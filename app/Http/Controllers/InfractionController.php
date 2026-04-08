<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Infraction;
use App\Models\Vehicle;
use App\Services\InfractionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfractionController extends Controller
{
    public function __construct(private readonly InfractionService $infractionService) {}

    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Infraction::with(['vehicle', 'driver', 'user', 'createdBy']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('location', 'like', "%{$q}%")
                   ->orWhere('pv_reference', 'like', "%{$q}%")
                   ->orWhereHas('vehicle', fn($vq) => $vq->where('plate', 'like', "%{$q}%"))
                   ->orWhereHas('driver', fn($dq) => $dq->where('full_name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $infractions = $query->latest('datetime_occurred')->paginate(15)->withQueryString();

        $stats = [
            'total'        => Infraction::count(),
            'ouvertes'     => Infraction::where('status', 'open')->count(),
            'non_payees'   => Infraction::unpaid()->count(),
            'total_amendes'=> (float) Infraction::sum('fine_amount'),
        ];

        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get();

        return view('infractions.index', compact('infractions', 'stats', 'vehicles'));
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(): View
    {
        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get();
        $drivers  = Driver::where('status', 'active')->orderBy('full_name')->get();

        return view('infractions.create', compact('vehicles', 'drivers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id'        => ['required', 'exists:vehicles,id'],
            'driver_id'         => ['nullable', 'exists:drivers,id'],
            'datetime_occurred' => ['required', 'date', 'before_or_equal:now'],
            'location'          => ['nullable', 'string', 'max:255'],
            'type'              => ['required', 'in:speeding,red_light,parking,phone_use,seatbelt,alcohol,dangerous_driving,overload,invalid_documents,other'],
            'description'       => ['nullable', 'string', 'max:1000'],
            'source'            => ['required', 'in:police,radar,internal,reported_by_driver,third_party'],
            'pv_reference'      => ['nullable', 'string', 'max:100'],
            'fine_amount'       => ['nullable', 'numeric', 'min:0'],
            'imputation'        => ['nullable', 'in:company,driver'],
        ]);

        $data['created_by'] = auth()->id();
        $data['status']     = 'open';
        $data['payment_status'] = $data['fine_amount'] ? 'unpaid' : null;

        // Identification automatique du conducteur si non fourni
        if (empty($data['driver_id'])) {
            $occurred  = Carbon::parse($data['datetime_occurred']);
            $identified = $this->infractionService->identifyDriver((int) $data['vehicle_id'], $occurred);

            $data['driver_id']       = $identified['driver_id'];
            $data['user_id']         = $identified['user_id'];
            $data['auto_identified'] = $identified['source'] !== 'unknown';
        }

        $infraction = Infraction::create($data);

        $msg = "Infraction #{$infraction->id} enregistrée.";
        if ($infraction->auto_identified && $infraction->driver) {
            $msg .= " Conducteur identifié automatiquement : {$infraction->driver->full_name}.";
        }

        return redirect()->route('infractions.show', $infraction)
                         ->with('swal_success', $msg);
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Infraction $infraction): View
    {
        $infraction->load(['vehicle', 'driver', 'user', 'assignment', 'sanctionDecidedBy', 'createdBy']);

        return view('infractions.show', compact('infraction'));
    }

    // ── Édition ────────────────────────────────────────────────────────────

    public function edit(Infraction $infraction): View
    {
        abort_if($infraction->status === 'closed', 403, 'Impossible de modifier une infraction clôturée.');

        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get();
        $drivers  = Driver::orderBy('full_name')->get();

        return view('infractions.edit', compact('infraction', 'vehicles', 'drivers'));
    }

    public function update(Request $request, Infraction $infraction): RedirectResponse
    {
        abort_if($infraction->status === 'closed', 403);

        $data = $request->validate([
            'vehicle_id'        => ['required', 'exists:vehicles,id'],
            'driver_id'         => ['nullable', 'exists:drivers,id'],
            'datetime_occurred' => ['required', 'date', 'before_or_equal:now'],
            'location'          => ['nullable', 'string', 'max:255'],
            'type'              => ['required', 'in:speeding,red_light,parking,phone_use,seatbelt,alcohol,dangerous_driving,overload,invalid_documents,other'],
            'description'       => ['nullable', 'string', 'max:1000'],
            'source'            => ['required', 'in:police,radar,internal,reported_by_driver,third_party'],
            'pv_reference'      => ['nullable', 'string', 'max:100'],
            'fine_amount'       => ['nullable', 'numeric', 'min:0'],
            'internal_sanction' => ['nullable', 'string', 'max:500'],
        ]);

        $infraction->update($data);

        return redirect()->route('infractions.show', $infraction)
                         ->with('swal_success', 'Infraction mise à jour.');
    }

    // ── Imputation financière ──────────────────────────────────────────────

    /**
     * Décide qui supporte l'amende (société ou chauffeur).
     * Utilise `InfractionService::impute()` qui gère la notification.
     */
    public function impute(Request $request, Infraction $infraction): RedirectResponse
    {
        $data = $request->validate([
            'imputation'        => ['required', 'in:company,driver'],
            'internal_sanction' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['internal_sanction'] ?? null) {
            $infraction->update(['internal_sanction' => $data['internal_sanction']]);
        }

        $this->infractionService->impute($infraction, $data['imputation'], auth()->user());

        $label = $data['imputation'] === 'driver' ? 'au chauffeur' : 'à la société';

        return back()->with('swal_success', "Amende imputée {$label}.");
    }

    // ── Enregistrement du paiement ─────────────────────────────────────────

    public function recordPayment(Request $request, Infraction $infraction): RedirectResponse
    {
        $data = $request->validate([
            'fine_amount'    => ['required', 'numeric', 'min:0'],
            'payment_date'   => ['required', 'date'],
            'payment_notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $this->infractionService->recordPayment(
            $infraction,
            (float) $data['fine_amount'],
            Carbon::parse($data['payment_date']),
            $data['payment_notes'] ?? ''
        );

        return back()->with('swal_success', 'Paiement enregistré.');
    }

    // ── Clôture ────────────────────────────────────────────────────────────

    public function close(Infraction $infraction): RedirectResponse
    {
        $infraction->update(['status' => 'closed']);

        return redirect()->route('infractions.show', $infraction)
                         ->with('swal_success', 'Infraction clôturée.');
    }

    // ── Suppression (soft) ─────────────────────────────────────────────────

    public function destroy(Infraction $infraction): RedirectResponse
    {
        $id = $infraction->id;
        $infraction->delete();

        return redirect()->route('infractions.index')
                         ->with('swal_success', "Infraction #{$id} supprimée.");
    }
}
