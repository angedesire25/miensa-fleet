<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Garage;
use App\Models\Incident;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Services\IncidentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function __construct(private readonly IncidentService $incidentService) {}

    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Incident::with(['vehicle', 'driver', 'user', 'latestRepair.garage']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('location', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%")
                   ->orWhereHas('vehicle', fn($vq) => $vq->where('plate', 'like', "%{$q}%")
                       ->orWhere('brand', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity') && $request->severity !== 'all') {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $incidents = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'     => Incident::count(),
            'ouverts'   => Incident::whereIn('status', ['open', 'at_garage'])->count(),
            'au_garage' => Incident::where('status', 'at_garage')->count(),
            'repares'   => Incident::where('status', 'repaired')->count(),
            'graves'    => Incident::whereIn('severity', ['major', 'total_loss'])->count(),
        ];

        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get();

        return view('incidents.index', compact('incidents', 'stats', 'vehicles'));
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(): View
    {
        $vehicles = Vehicle::whereIn('status', ['available', 'on_mission', 'breakdown'])
                           ->orderBy('brand')->orderBy('model')->get();
        $drivers  = Driver::where('status', 'active')->orderBy('full_name')->get();

        return view('incidents.create', compact('vehicles', 'drivers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id'               => ['required', 'exists:vehicles,id'],
            'driver_id'                => ['nullable', 'exists:drivers,id'],
            'assignment_id'            => ['nullable', 'exists:assignments,id'],
            'type'                     => ['required', 'in:accident,breakdown,flat_tire,electrical_fault,body_damage,theft_attempt,theft,flood_damage,fire,vandalism,other'],
            'severity'                 => ['required', 'in:minor,moderate,major,total_loss'],
            'datetime_occurred'        => ['required', 'date', 'before_or_equal:now'],
            'location'                 => ['nullable', 'string', 'max:255'],
            'description'              => ['required', 'string', 'max:2000'],
            'vehicle_immobilized'      => ['boolean'],
            'third_party_involved'     => ['boolean'],
            'third_party_name'         => ['nullable', 'string', 'max:150'],
            'third_party_plate'        => ['nullable', 'string', 'max:30'],
            'third_party_insurance'    => ['nullable', 'string', 'max:150'],
            'police_report_number'     => ['nullable', 'string', 'max:100'],
            'insurance_declared'       => ['boolean'],
            'insurance_claim_number'   => ['nullable', 'string', 'max:100'],
            'estimated_repair_cost'    => ['nullable', 'numeric', 'min:0'],
            // Photos
            'photos'                   => ['nullable', 'array', 'max:10'],
            'photos.*'                 => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'photo_contexts'           => ['nullable', 'array'],
            'photo_contexts.*'         => ['in:incident_before,incident_damage'],
        ]);

        $data['created_by']          = auth()->id();
        $data['user_id']             = $data['driver_id'] ? null : auth()->id();
        $data['vehicle_immobilized'] = $request->boolean('vehicle_immobilized');
        $data['third_party_involved']= $request->boolean('third_party_involved');
        $data['insurance_declared']  = $request->boolean('insurance_declared');
        $data['status']              = 'open';

        $incident = $this->incidentService->createIncident($data);

        // Enregistrer les photos après création
        $this->savePhotos($request, $incident);

        return redirect()->route('incidents.show', $incident)
                         ->with('swal_success', "Sinistre enregistré avec succès (#{$incident->id}).");
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Incident $incident): View
    {
        $incident->load(['vehicle', 'driver', 'user', 'assignment', 'repairs.garage', 'createdBy', 'photos']);

        return view('incidents.show', compact('incident'));
    }

    // ── Édition ────────────────────────────────────────────────────────────

    public function edit(Incident $incident): View
    {
        $incident->load('photos');
        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get();
        $drivers  = Driver::orderBy('full_name')->get();

        return view('incidents.edit', compact('incident', 'vehicles', 'drivers'));
    }

    public function update(Request $request, Incident $incident): RedirectResponse
    {
        if (in_array($incident->status, ['closed', 'total_loss'], true)) {
            return back()->withErrors(['status' => 'Impossible de modifier un sinistre clôturé ou en perte totale.']);
        }

        $data = $request->validate([
            'type'                      => ['required', 'in:accident,breakdown,flat_tire,electrical_fault,body_damage,theft_attempt,theft,flood_damage,fire,vandalism,other'],
            'severity'                  => ['required', 'in:minor,moderate,major,total_loss'],
            'datetime_occurred'         => ['required', 'date', 'before_or_equal:now'],
            'location'                  => ['nullable', 'string', 'max:255'],
            'description'               => ['required', 'string', 'max:2000'],
            'vehicle_immobilized'       => ['boolean'],
            'third_party_involved'      => ['boolean'],
            'third_party_name'          => ['nullable', 'string', 'max:150'],
            'third_party_plate'         => ['nullable', 'string', 'max:30'],
            'third_party_insurance'     => ['nullable', 'string', 'max:150'],
            'police_report_number'      => ['nullable', 'string', 'max:100'],
            'insurance_declared'        => ['boolean'],
            'insurance_claim_number'    => ['nullable', 'string', 'max:100'],
            'insurance_declaration_date'=> ['nullable', 'date'],
            'insurance_amount_claimed'  => ['nullable', 'numeric', 'min:0'],
            'insurance_amount_received' => ['nullable', 'numeric', 'min:0'],
            'insurance_status'          => ['nullable', 'in:not_declared,pending,accepted,rejected,partial'],
            'estimated_repair_cost'     => ['nullable', 'numeric', 'min:0'],
            'actual_repair_cost'        => ['nullable', 'numeric', 'min:0'],
            // Photos
            'photos'                    => ['nullable', 'array', 'max:10'],
            'photos.*'                  => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'photo_contexts'            => ['nullable', 'array'],
            'photo_contexts.*'          => ['in:incident_before,incident_damage'],
            'delete_photos'             => ['nullable', 'array'],
            'delete_photos.*'           => ['integer', 'exists:vehicle_photos,id'],
        ]);

        $data['vehicle_immobilized']  = $request->boolean('vehicle_immobilized');
        $data['third_party_involved'] = $request->boolean('third_party_involved');
        $data['insurance_declared']   = $request->boolean('insurance_declared');

        $incident->update($data);

        // Supprimer les photos cochées
        if (! empty($data['delete_photos'])) {
            $this->deletePhotos($data['delete_photos']);
        }

        // Ajouter les nouvelles photos
        $this->savePhotos($request, $incident);

        return redirect()->route('incidents.show', $incident)
                         ->with('swal_success', 'Sinistre mis à jour.');
    }

    // ── Suppression d'une photo ────────────────────────────────────────────

    public function deletePhoto(Request $request, Incident $incident): RedirectResponse
    {
        $request->validate(['photo_id' => ['required', 'integer', 'exists:vehicle_photos,id']]);

        $photo = VehiclePhoto::where('id', $request->photo_id)
                             ->where('photoable_type', Incident::class)
                             ->where('photoable_id', $incident->id)
                             ->firstOrFail();

        if (Storage::disk('public')->exists($photo->file_path)) {
            Storage::disk('public')->delete($photo->file_path);
        }

        $photo->delete();

        return back()->with('swal_success', 'Photo supprimée.');
    }

    // ── Envoi au garage ────────────────────────────────────────────────────

    public function sendToGarage(Request $request, Incident $incident): RedirectResponse
    {
        if (! in_array($incident->status, ['open'], true)) {
            return back()->withErrors(['garage' => 'Le sinistre doit être ouvert pour envoyer le véhicule au garage.']);
        }

        $data = $request->validate([
            'garage_id'             => ['required', 'exists:garages,id'],
            'repair_type'           => ['required', 'in:body_repair,mechanical,electrical,tire,painting,glass,full_service,other'],
            'datetime_sent'         => ['required', 'date'],
            'km_at_departure'       => ['nullable', 'integer', 'min:0'],
            'condition_at_departure'=> ['nullable', 'string', 'max:500'],
        ]);

        $data['sent_by']    = auth()->id();
        $data['created_by'] = auth()->id();

        $repair = $this->incidentService->sendToGarage($incident, $data['garage_id'], $data);

        return redirect()->route('incidents.show', $incident)
                         ->with('swal_success', "Véhicule envoyé au garage. Réparation #{$repair->id} créée.");
    }

    // ── Clôture ────────────────────────────────────────────────────────────

    public function close(Incident $incident): RedirectResponse
    {
        if ($incident->status === 'at_garage') {
            return back()->withErrors(['status' => 'Impossible de clôturer : le véhicule est encore au garage.']);
        }

        $incident->update(['status' => 'closed']);

        return redirect()->route('incidents.show', $incident)
                         ->with('swal_success', 'Sinistre clôturé.');
    }

    // ── Suppression (soft) ─────────────────────────────────────────────────

    public function destroy(Incident $incident): RedirectResponse
    {
        $id = $incident->id;
        $incident->delete();

        return redirect()->route('incidents.index')
                         ->with('swal_success', "Sinistre #{$id} supprimé.");
    }

    // ── Helpers privés ─────────────────────────────────────────────────────

    /**
     * Enregistre les photos uploadées et crée les entrées VehiclePhoto.
     * Chaque fichier peut avoir un contexte (incident_before / incident_damage).
     */
    private function savePhotos(Request $request, Incident $incident): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        $contexts = $request->input('photo_contexts', []);

        foreach ($request->file('photos') as $index => $file) {
            $context  = $contexts[$index] ?? 'incident_damage';
            $path     = $file->store('incidents/photos', 'public');

            VehiclePhoto::create([
                'vehicle_id'     => $incident->vehicle_id,
                'photoable_type' => Incident::class,
                'photoable_id'   => $incident->id,
                'context'        => $context,
                'file_path'      => $path,
                'original_name'  => $file->getClientOriginalName(),
                'mime_type'      => $file->getMimeType(),
                'size_kb'        => (int) round($file->getSize() / 1024),
                'uploaded_by'    => auth()->id(),
            ]);
        }
    }

    /**
     * Supprime physiquement les photos par leurs IDs.
     */
    private function deletePhotos(array $photoIds): void
    {
        VehiclePhoto::whereIn('id', $photoIds)->each(function (VehiclePhoto $photo) {
            if (Storage::disk('public')->exists($photo->file_path)) {
                Storage::disk('public')->delete($photo->file_path);
            }
            $photo->delete();
        });
    }
}
