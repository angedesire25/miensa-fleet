<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Infraction;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\InfractionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfractionController extends Controller
{
    public function __construct(private readonly InfractionService $infractionService) {}

    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $showArchived = $request->boolean('archived');

        $query = $showArchived
            ? Infraction::onlyTrashed()->with(['vehicle', 'driver', 'user', 'createdBy'])
            : Infraction::with(['vehicle', 'driver', 'user', 'createdBy']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('location', 'like', "%{$q}%")
                   ->orWhere('pv_reference', 'like', "%{$q}%")
                   ->orWhereHas('vehicle', fn($vq) => $vq->where('plate', 'like', "%{$q}%"))
                   ->orWhereHas('driver', fn($dq) => $dq->where('full_name', 'like', "%{$q}%"));
            });
        }

        if (!$showArchived && $request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if (!$showArchived && $request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if (!$showArchived && $request->filled('payment_status') && $request->payment_status !== 'all') {
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
            'archived'     => Infraction::onlyTrashed()->count(),
        ];

        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get();

        return view('infractions.index', compact('infractions', 'stats', 'vehicles', 'showArchived'));
    }

    // ── Identification temps-réel (AJAX) ───────────────────────────────────

    /**
     * Identifie en temps réel qui utilisait le véhicule à la date/heure donnée.
     * Appelé par le formulaire d'infraction via fetch() pour pré-remplir le conducteur.
     *
     * Retourne JSON :
     *   { type: 'driver', id, name, ref, source }   si un chauffeur est trouvé
     *   { type: 'user',   id, name, ref, source }   si un collaborateur (vehicle_request)
     *   { type: 'unknown' }                          si aucune utilisation trouvée
     */
    public function identifyOccupant(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id'        => ['required', 'exists:vehicles,id'],
            'datetime_occurred' => ['required', 'date'],
        ]);

        $occurred   = Carbon::parse($request->datetime_occurred);
        $identified = $this->infractionService->identifyDriver((int) $request->vehicle_id, $occurred);

        // ── Affectation ponctuelle : chauffeur professionnel ───────────────
        if ($identified['source'] === 'assignment' && $identified['driver_id']) {
            $driver = Driver::find($identified['driver_id'], ['id', 'full_name', 'matricule']);
            return response()->json([
                'type'         => 'driver',
                'id'           => $driver->id,
                'name'         => $driver->full_name,
                'ref'          => $driver->matricule ?? '',
                'source'       => 'Affectation chauffeur',
                'is_permanent' => false,
            ]);
        }

        // ── Affectation ponctuelle : collaborateur ─────────────────────────
        if ($identified['source'] === 'assignment' && $identified['user_id']) {
            $user = User::find($identified['user_id'], ['id', 'name', 'email', 'department']);
            return response()->json([
                'type'         => 'user',
                'id'           => $user->id,
                'name'         => $user->name,
                'ref'          => $user->department ?? $user->email ?? '',
                'source'       => 'Affectation (collaborateur)',
                'is_permanent' => false,
            ]);
        }

        // ── Demande de véhicule ────────────────────────────────────────────
        if ($identified['source'] === 'request' && $identified['user_id']) {
            $user = User::find($identified['user_id'], ['id', 'name', 'email', 'department']);
            return response()->json([
                'type'         => 'user',
                'id'           => $user->id,
                'name'         => $user->name,
                'ref'          => $user->department ?? $user->email ?? '',
                'source'       => 'Demande de véhicule',
                'is_permanent' => false,
            ]);
        }

        // ── Affectation permanente (conducteur attitré) ────────────────────
        if ($identified['source'] === 'permanent') {
            if ($identified['driver_id']) {
                $driver = Driver::find($identified['driver_id'], ['id', 'full_name', 'matricule']);
                return response()->json([
                    'type'         => 'driver',
                    'id'           => $driver->id,
                    'name'         => $driver->full_name,
                    'ref'          => $driver->matricule ?? '',
                    'source'       => 'Affectation permanente',
                    'is_permanent' => true,
                ]);
            }
            if ($identified['user_id']) {
                $user = User::find($identified['user_id'], ['id', 'name', 'email', 'department']);
                return response()->json([
                    'type'         => 'user',
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'ref'          => $user->department ?? $user->email ?? '',
                    'source'       => 'Affectation permanente',
                    'is_permanent' => true,
                ]);
            }
        }

        return response()->json(['type' => 'unknown', 'is_permanent' => false]);
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(): View
    {
        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get();
        $drivers       = Driver::where('status', 'active')->orderBy('full_name')->get();
        $collaborators = User::role('collaborator')->orderBy('name')->get();

        return view('infractions.create', compact('vehicles', 'drivers', 'collaborators'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id'        => ['required', 'exists:vehicles,id'],
            'driver_id'         => ['nullable', 'exists:drivers,id'],
            'user_id'           => ['nullable', 'exists:users,id'],
            'datetime_occurred' => ['required', 'date', 'before_or_equal:now'],
            'location'          => ['nullable', 'string', 'max:255'],
            'type'              => ['required', 'in:speeding,red_light,illegal_parking,drunk_driving,phone_use,accident,seatbelt,overloading,other'],
            'description'       => ['nullable', 'string', 'max:1000'],
            'source'            => ['required', 'in:police_report,speed_camera,internal_report,joint_report,other'],
            'pv_reference'      => ['nullable', 'string', 'max:100'],
            'fine_amount'       => ['nullable', 'numeric', 'min:0'],
            'imputation'        => ['nullable', 'in:company,driver'],
        ]);

        $data['created_by'] = auth()->id();
        $data['status']     = 'open';
        $data['payment_status'] = $data['fine_amount'] ? 'unpaid' : null;

        // Si aucun conducteur ni collaborateur renseigné → identification automatique
        if (empty($data['driver_id']) && empty($data['user_id'])) {
            $occurred  = Carbon::parse($data['datetime_occurred']);
            $identified = $this->infractionService->identifyDriver((int) $data['vehicle_id'], $occurred);

            $data['driver_id']       = $identified['driver_id'];
            $data['user_id']         = $identified['user_id'];
            $data['auto_identified'] = $identified['source'] !== 'unknown';
        } elseif (!empty($data['driver_id']) || !empty($data['user_id'])) {
            // Pré-rempli depuis le formulaire (via AJAX) → marquer comme identifié
            $data['auto_identified'] = true;
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
            'type'              => ['required', 'in:speeding,red_light,illegal_parking,drunk_driving,phone_use,accident,seatbelt,overloading,other'],
            'description'       => ['nullable', 'string', 'max:1000'],
            'source'            => ['required', 'in:police_report,speed_camera,internal_report,joint_report,other'],
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
        $infraction->update(['status' => 'processed']);

        return redirect()->route('infractions.show', $infraction)
                         ->with('swal_success', 'Infraction clôturée.');
    }

    // ── Suppression (soft) ─────────────────────────────────────────────────

    public function destroy(Infraction $infraction): RedirectResponse
    {
        $id = $infraction->id;
        $infraction->delete();

        return redirect()->route('infractions.index')
                         ->with('swal_success', "Infraction #{$id} archivée.");
    }

    public function restore(int $id): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);
        $infraction = Infraction::onlyTrashed()->findOrFail($id);
        $infraction->restore();
        return redirect()->route('infractions.index')
                         ->with('swal_success', "Infraction #{$id} restaurée.");
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);
        $infraction = Infraction::onlyTrashed()->findOrFail($id);
        $infraction->forceDelete();
        return redirect()->route('infractions.index', ['archived' => 1])
                         ->with('swal_success', "Infraction #{$id} supprimée définitivement.");
    }
}
