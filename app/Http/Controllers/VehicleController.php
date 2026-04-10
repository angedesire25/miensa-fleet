<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleController extends Controller
{
    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $showArchived = $request->boolean('archived');

        $query = $showArchived
            ? Vehicle::onlyTrashed()->with(['currentDriver', 'profilePhoto', 'permanentAssignment'])
            : Vehicle::with(['currentDriver', 'profilePhoto', 'permanentAssignment']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('brand', 'like', "%{$q}%")
                   ->orWhere('model', 'like', "%{$q}%")
                   ->orWhere('plate', 'like', "%{$q}%")
                   ->orWhere('vin', 'like', "%{$q}%");
            });
        }

        if (!$showArchived && $request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('vehicle_type', $request->type);
        }

        if ($request->filled('fuel')) {
            $query->where('fuel_type', $request->fuel);
        }

        $vehicles = $query->orderBy('brand')->orderBy('model')->paginate(15)->withQueryString();

        $stats = [
            'total'       => Vehicle::count(),
            'available'   => Vehicle::where('status', 'available')->count(),
            'on_mission'  => Vehicle::where('status', 'on_mission')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            'breakdown'   => Vehicle::where('status', 'breakdown')->count(),
            'archived'    => Vehicle::onlyTrashed()->count(),
        ];

        return view('vehicles.index', compact('vehicles', 'stats', 'showArchived'));
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load([
            'currentDriver',
            'profilePhoto',
            'photos',
            'documents',
            'activeIncident',
            'currentRepair.garage',
            'incidents' => fn($q) => $q->latest()->limit(5),
            'repairs'   => fn($q) => $q->with('garage')->latest()->limit(5),
            'assignments' => fn($q) => $q->with('driver')->latest('datetime_start')->limit(10),
        ]);

        return view('vehicles.show', compact('vehicle'));
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(): View
    {
        $drivers = Driver::active()->orderBy('full_name')->get();
        return view('vehicles.create', compact('drivers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'brand'                    => 'required|string|max:60',
            'model'                    => 'required|string|max:60',
            'plate'                    => 'required|string|max:20|unique:vehicles,plate',
            'year'                     => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'color'                    => 'nullable|string|max:40',
            'vin'                      => 'nullable|string|max:17|unique:vehicles,vin',
            'fuel_type'                => 'required|in:diesel,gasoline,hybrid,electric,lpg',
            'vehicle_type'             => 'required|in:city,sedan,suv,pickup,van,truck,motorcycle',
            'license_category'         => 'required|in:A,B,C,D,E,BE,CE',
            'seats'                    => 'required|integer|min:1|max:60',
            'payload_kg'               => 'nullable|integer|min:0',
            'km_current'               => 'required|integer|min:0',
            'km_next_service'          => 'nullable|integer|min:0',
            'purchase_price'           => 'nullable|numeric|min:0',
            'purchase_date'            => 'nullable|date',
            'insurance_company'        => 'nullable|string|max:100',
            'insurance_policy_number'  => 'nullable|string|max:100',
            'notes'                    => 'nullable|string|max:2000',
            'photo'                    => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $data['created_by'] = Auth::id();
        $data['status']     = 'available';
        unset($data['photo']);

        $vehicle = Vehicle::create($data);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $path = $file->store("vehicles/{$vehicle->id}/profile", 'public');
            VehiclePhoto::create([
                'vehicle_id'     => $vehicle->id,
                'context'        => 'vehicle_profile',
                'file_path'      => $path,
                'original_name'  => $file->getClientOriginalName(),
                'mime_type'      => $file->getMimeType(),
                'size_kb'        => (int) ($file->getSize() / 1024),
                'uploaded_by'    => Auth::id(),
            ]);
        }

        return redirect()->route('vehicles.show', $vehicle)
                         ->with('swal_success', 'Véhicule ajouté avec succès.');
    }

    // ── Modification ───────────────────────────────────────────────────────

    public function edit(Vehicle $vehicle): View
    {
        $drivers = Driver::active()->orderBy('full_name')->get();
        $vehicle->load('profilePhoto');
        return view('vehicles.edit', compact('vehicle', 'drivers'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'brand'                    => 'required|string|max:60',
            'model'                    => 'required|string|max:60',
            'plate'                    => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate')->ignore($vehicle->id)],
            'year'                     => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'color'                    => 'nullable|string|max:40',
            'vin'                      => ['nullable', 'string', 'max:17', Rule::unique('vehicles', 'vin')->ignore($vehicle->id)],
            'fuel_type'                => 'required|in:diesel,gasoline,hybrid,electric,lpg',
            'vehicle_type'             => 'required|in:city,sedan,suv,pickup,van,truck,motorcycle',
            'license_category'         => 'required|in:A,B,C,D,E,BE,CE',
            'seats'                    => 'required|integer|min:1|max:60',
            'payload_kg'               => 'nullable|integer|min:0',
            'km_current'               => 'required|integer|min:0',
            'km_next_service'          => 'nullable|integer|min:0',
            'purchase_price'           => 'nullable|numeric|min:0',
            'purchase_date'            => 'nullable|date',
            'insurance_company'        => 'nullable|string|max:100',
            'insurance_policy_number'  => 'nullable|string|max:100',
            'notes'                    => 'nullable|string|max:2000',
            'photo'                    => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'status'                   => 'sometimes|in:available,on_mission,maintenance,breakdown,sold,retired',
        ]);

        unset($data['photo']);
        $vehicle->update($data);

        if ($request->hasFile('photo')) {
            $vehicle->load('profilePhoto');
            if ($vehicle->profilePhoto) {
                Storage::disk('public')->delete($vehicle->profilePhoto->file_path);
                $vehicle->profilePhoto->delete();
            }
            $file = $request->file('photo');
            $path = $file->store("vehicles/{$vehicle->id}/profile", 'public');
            VehiclePhoto::create([
                'vehicle_id'    => $vehicle->id,
                'context'       => 'vehicle_profile',
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size_kb'       => (int) ($file->getSize() / 1024),
                'uploaded_by'   => Auth::id(),
            ]);
        }

        return redirect()->route('vehicles.show', $vehicle)
                         ->with('swal_success', 'Véhicule mis à jour.');
    }

    // ── Archivage / Restauration ───────────────────────────────────────────

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')
                         ->with('swal_success', "Véhicule {$vehicle->plate} archivé.");
    }

    public function restore(int $id): RedirectResponse
    {
        $vehicle = Vehicle::onlyTrashed()->findOrFail($id);
        $vehicle->restore();
        return redirect()->route('vehicles.show', $vehicle)
                         ->with('swal_success', 'Véhicule restauré.');
    }

    /**
     * Suppression définitive (irréversible) — réservée aux admins.
     * Vérifie d'abord qu'aucune donnée liée ne bloque la suppression.
     * Si des données existent, liste les sections à vider en premier.
     */
    public function forceDestroy(int $id): RedirectResponse
    {
        $vehicle = Vehicle::withTrashed()->findOrFail($id);
        $plate   = $vehicle->plate;

        // Vérifier chaque relation susceptible de bloquer la suppression MySQL.
        // Pour chaque relation avec SoftDeletes, on distingue actifs vs archivés
        // afin de donner un message précis à l'utilisateur.
        $blocking = [];

        $this->checkRelation($vehicle->assignments(), $blocking, 'affectation(s)');
        $this->checkRelation($vehicle->inspections(), $blocking, 'fiche(s) de contrôle');
        $this->checkRelation($vehicle->infractions(), $blocking, 'infraction(s)');
        $this->checkRelation($vehicle->incidents(),   $blocking, 'sinistre(s)');
        $this->checkRelation($vehicle->repairs(),     $blocking, 'réparation(s)');
        $this->checkRelation($vehicle->cleanings(),   $blocking, 'nettoyage(s)');

        // Relations sans SoftDeletes
        $n = $vehicle->vehicleRequests()->count();
        if ($n) $blocking[] = "{$n} demande(s) de véhicule";

        $n = $vehicle->alerts()->count();
        if ($n) $blocking[] = "{$n} alerte(s)";

        // Si des données liées existent → informer sans rien supprimer
        if (!empty($blocking)) {
            $lines = implode("\n• ", $blocking);
            return redirect()->back()
                ->with('swal_error', "Impossible de supprimer définitivement « {$plate} ».\n\nEnregistrements encore liés :\n• {$lines}\n\nConsultez les archives si aucun enregistrement n'est visible dans les listes.");
        }

        // Aucune donnée liée bloquante : supprimer les fichiers puis le véhicule
        foreach ($vehicle->photos()->get() as $photo) {
            Storage::disk('public')->delete($photo->file_path);
        }
        foreach ($vehicle->documents()->get() as $doc) {
            if ($doc->file_path) Storage::disk('public')->delete($doc->file_path);
        }

        $vehicle->photos()->delete();
        $vehicle->documents()->delete();
        $vehicle->forceDelete();

        return redirect()->route('vehicles.index')
                         ->with('swal_success', "Véhicule {$plate} supprimé définitivement.");
    }

    // ── Statut ─────────────────────────────────────────────────────────────

    public function toggleStatus(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:available,on_mission,maintenance,breakdown,sold,retired',
        ]);

        $vehicle->update(['status' => $request->status]);

        return back()->with('swal_success', 'Statut du véhicule mis à jour.');
    }

    // ── Utilitaires ────────────────────────────────────────────────────────

    /**
     * Compte les enregistrements liés (actifs + archivés séparément)
     * et pousse une entrée lisible dans $blocking si au moins un existe.
     */
    private function checkRelation($relation, array &$blocking, string $label): void
    {
        $total  = $relation->withoutGlobalScopes()->count();
        if ($total === 0) return;

        $active   = $relation->count();
        $archived = $total - $active;

        $detail = [];
        if ($active   > 0) $detail[] = "{$active} actif(s)";
        if ($archived > 0) $detail[] = "{$archived} archivé(s) — vérifiez les archives";

        $blocking[] = "{$total} {$label} (" . implode(', ', $detail) . ")";
    }
}
