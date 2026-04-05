<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleController extends Controller
{
    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Vehicle::with(['currentDriver', 'profilePhoto']);

        if ($request->boolean('avec_archives')) {
            $query = Vehicle::withTrashed()->with(['currentDriver', 'profilePhoto']);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('brand', 'like', "%{$q}%")
                   ->orWhere('model', 'like', "%{$q}%")
                   ->orWhere('plate', 'like', "%{$q}%")
                   ->orWhere('vin', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
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

        return view('vehicles.index', compact('vehicles', 'stats'));
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

    // ── Statut ─────────────────────────────────────────────────────────────

    public function toggleStatus(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:available,on_mission,maintenance,breakdown,sold,retired',
        ]);

        $vehicle->update(['status' => $request->status]);

        return back()->with('swal_success', 'Statut du véhicule mis à jour.');
    }
}
