<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use App\Models\Incident;
use App\Models\Repair;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Services\IncidentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RepairController extends Controller
{
    public function __construct(private readonly IncidentService $incidentService) {}

    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Repair::with(['vehicle', 'garage', 'incident', 'sentBy']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('diagnosis', 'like', "%{$q}%")
                   ->orWhere('work_performed', 'like', "%{$q}%")
                   ->orWhereHas('vehicle', fn($vq) => $vq->where('plate', 'like', "%{$q}%"))
                   ->orWhereHas('garage', fn($gq) => $gq->where('name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('garage_id')) {
            $query->where('garage_id', $request->garage_id);
        }

        if ($request->filled('overdue') && $request->overdue === '1') {
            $maxDays = config('fleet.repair_overdue_days', 7);
            $query->inProgress()->where('datetime_sent', '<', now()->subDays($maxDays));
        }

        $repairs = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'       => Repair::count(),
            'en_cours'    => Repair::inProgress()->count(),
            'terminees'   => Repair::completed()->count(),
            'recurrences' => Repair::where('same_issue_recurrence', true)->count(),
        ];

        $garages = Garage::orderBy('name')->get();

        return view('repairs.index', compact('repairs', 'stats', 'garages'));
    }

    // ── Création directe (entretien préventif ou hors sinistre) ───────────

    public function create(): View
    {
        $vehicles  = Vehicle::whereNotIn('status', ['archived'])->orderBy('brand')->orderBy('model')->get();
        $garages   = Garage::approved()->orderBy('name')->get();
        $incidents = Incident::whereIn('status', ['open'])->with('vehicle')->latest()->get();

        return view('repairs.create', compact('vehicles', 'garages', 'incidents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id'             => ['required', 'exists:vehicles,id'],
            'garage_id'              => ['required', 'exists:garages,id'],
            'incident_id'            => ['nullable', 'exists:incidents,id'],
            'repair_type'            => ['required', 'in:body_repair,mechanical,electrical,tire,painting,glass,full_service,other'],
            'datetime_sent'          => ['required', 'date'],
            'km_at_departure'        => ['nullable', 'integer', 'min:0'],
            'condition_at_departure' => ['nullable', 'string', 'max:500'],
            'quote_amount'           => ['nullable', 'numeric', 'min:0'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
            // Photos
            'photos'                 => ['nullable', 'array', 'max:10'],
            'photos.*'               => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'photo_contexts'         => ['nullable', 'array'],
            'photo_contexts.*'       => ['in:repair_in_progress,repair_after'],
        ]);

        $data['sent_by']    = auth()->id();
        $data['created_by'] = auth()->id();
        $data['status']     = 'sent';

        if (! empty($data['incident_id'])) {
            $incident = Incident::findOrFail($data['incident_id']);
            $repair   = $this->incidentService->sendToGarage($incident, $data['garage_id'], $data);
        } else {
            $repair = Repair::create($data);
            DB::table('vehicles')
                ->where('id', $data['vehicle_id'])
                ->update(['status' => 'maintenance']);
        }

        $this->savePhotos($request, $repair);

        return redirect()->route('repairs.show', $repair)
                         ->with('swal_success', "Bon de réparation #{$repair->id} créé.");
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Repair $repair): View
    {
        $repair->load(['vehicle', 'garage', 'incident', 'sentBy', 'receivedBy', 'partsReplaced', 'previousRepair', 'photos']);

        return view('repairs.show', compact('repair'));
    }

    // ── Mise à jour du statut + ajout de photos ────────────────────────────

    public function updateStatus(Request $request, Repair $repair): RedirectResponse
    {
        $data = $request->validate([
            'status'         => ['required', 'in:sent,diagnosing,repairing,waiting_parts,completed'],
            'diagnosis'      => ['nullable', 'string', 'max:1000'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            // Photos
            'photos'         => ['nullable', 'array', 'max:10'],
            'photos.*'       => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'photo_contexts' => ['nullable', 'array'],
            'photo_contexts.*'=> ['in:repair_in_progress,repair_after'],
        ]);

        $repair->update($data);

        $this->savePhotos($request, $repair);

        return back()->with('swal_success', 'Statut de la réparation mis à jour.');
    }

    // ── Retour du garage ───────────────────────────────────────────────────

    public function returnFromGarage(Request $request, Repair $repair): RedirectResponse
    {
        if (! in_array($repair->status, ['sent', 'diagnosing', 'repairing', 'waiting_parts', 'completed'], true)) {
            return back()->withErrors(['status' => 'Ce bon de réparation n\'est pas en attente de retour.']);
        }

        $data = $request->validate([
            'datetime_returned'    => ['required', 'date', 'after_or_equal:' . $repair->datetime_sent->toDateString()],
            'km_at_return'         => ['nullable', 'integer', 'min:0'],
            'condition_at_return'  => ['nullable', 'string', 'max:500'],
            'work_performed'       => ['required', 'string', 'max:2000'],
            'invoice_amount'       => ['nullable', 'numeric', 'min:0'],
            'invoice_number'       => ['nullable', 'string', 'max:100'],
            'warranty_months'      => ['nullable', 'integer', 'min:0', 'max:120'],
            'has_persistent_issue' => ['boolean'],
            'notes'                => ['nullable', 'string', 'max:1000'],
            // Photos
            'photos'               => ['nullable', 'array', 'max:10'],
            'photos.*'             => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'photo_contexts'       => ['nullable', 'array'],
            'photo_contexts.*'     => ['in:repair_in_progress,repair_after'],
        ]);

        $data['received_by']          = auth()->id();
        $data['has_persistent_issue'] = $request->boolean('has_persistent_issue');

        if (! empty($data['warranty_months'])) {
            $repair->update(['warranty_months' => $data['warranty_months']]);
        }

        // Sauvegarder les photos avant que returnFromGarage ne rafraîchisse le modèle
        $this->savePhotos($request, $repair);

        $repair = $this->incidentService->returnFromGarage($repair, $data);

        $msg = 'Retour du véhicule enregistré.';
        if ($data['has_persistent_issue']) {
            $msg .= ' ⚠ Problème persistant signalé — le sinistre a été rouvert.';
        }

        return redirect()->route('repairs.show', $repair)
                         ->with($data['has_persistent_issue'] ? 'swal_warning' : 'swal_success', $msg);
    }

    // ── Suppression d'une photo ────────────────────────────────────────────

    public function deletePhoto(Request $request, Repair $repair): RedirectResponse
    {
        $request->validate(['photo_id' => ['required', 'integer', 'exists:vehicle_photos,id']]);

        $photo = VehiclePhoto::where('id', $request->photo_id)
                             ->where('photoable_type', Repair::class)
                             ->where('photoable_id', $repair->id)
                             ->firstOrFail();

        if (Storage::disk('public')->exists($photo->file_path)) {
            Storage::disk('public')->delete($photo->file_path);
        }

        $photo->delete();

        return back()->with('swal_success', 'Photo supprimée.');
    }

    // ── Helpers privés ─────────────────────────────────────────────────────

    /**
     * Enregistre les photos uploadées et crée les entrées VehiclePhoto.
     * Contextes : repair_in_progress (en cours) ou repair_after (après).
     */
    private function savePhotos(Request $request, Repair $repair): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        $contexts = $request->input('photo_contexts', []);

        foreach ($request->file('photos') as $index => $file) {
            $context = $contexts[$index] ?? 'repair_in_progress';
            $path    = $file->store('repairs/photos', 'public');

            VehiclePhoto::create([
                'vehicle_id'     => $repair->vehicle_id,
                'photoable_type' => Repair::class,
                'photoable_id'   => $repair->id,
                'context'        => $context,
                'file_path'      => $path,
                'original_name'  => $file->getClientOriginalName(),
                'mime_type'      => $file->getMimeType(),
                'size_kb'        => (int) round($file->getSize() / 1024),
                'uploaded_by'    => auth()->id(),
            ]);
        }
    }
}
