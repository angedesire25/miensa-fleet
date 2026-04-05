<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DriverController extends Controller
{
    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Driver::with(['activeAssignment.vehicle', 'preferredVehicle']);

        if ($request->boolean('avec_archives')) {
            $query = Driver::withTrashed()->with(['activeAssignment.vehicle', 'preferredVehicle']);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('full_name', 'like', "%{$q}%")
                   ->orWhere('matricule', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('license')) {
            $query->whereJsonContains('license_categories', $request->license);
        }

        if ($request->filled('contract')) {
            $query->where('contract_type', $request->contract);
        }

        $drivers = $query->orderBy('full_name')->paginate(15)->withQueryString();

        $stats = [
            'total'      => Driver::count(),
            'active'     => Driver::where('status', 'active')->count(),
            'suspended'  => Driver::where('status', 'suspended')->count(),
            'on_leave'   => Driver::where('status', 'on_leave')->count(),
            'terminated' => Driver::where('status', 'terminated')->count(),
            'archived'   => Driver::onlyTrashed()->count(),
            'license_expiring' => Driver::where('status', 'active')
                ->where('license_expiry_date', '<=', now()->addDays(30))
                ->where('license_expiry_date', '>=', now())
                ->count(),
        ];

        return view('drivers.index', compact('drivers', 'stats'));
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Driver $driver): View
    {
        $driver->load([
            'preferredVehicle',
            'documents',
            'activeAssignment.vehicle',
            'assignments' => fn($q) => $q->with('vehicle')->latest('datetime_start')->limit(10),
            'infractions'  => fn($q) => $q->latest()->limit(5),
        ]);

        return view('drivers.show', compact('driver'));
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(): View
    {
        $vehicles = Vehicle::available()->orderBy('brand')->orderBy('model')->get();
        return view('drivers.create', compact('vehicles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'matricule'                => 'required|string|max:30|unique:drivers,matricule',
            'full_name'                => 'required|string|max:100',
            'date_of_birth'            => 'nullable|date|before:today',
            'phone'                    => 'required|string|max:30',
            'email'                    => 'nullable|email|max:100|unique:drivers,email',
            'address'                  => 'nullable|string|max:200',
            'hire_date'                => 'nullable|date',
            'contract_type'            => 'required|in:permanent,fixed_term,interim,contractor',
            'contract_end_date'        => 'nullable|date|after:hire_date',
            // Permis : optionnel à la création (profil auto-créé) — à compléter ultérieurement
            'license_number'           => 'nullable|string|max:50|unique:drivers,license_number',
            'license_categories'       => 'nullable|array',
            'license_categories.*'     => 'in:A,B,C,D,E,BE,CE',
            'license_expiry_date'      => 'nullable|date',
            'license_issuing_authority'=> 'nullable|string|max:100',
            'preferred_vehicle_id'     => 'nullable|exists:vehicles,id',
            'notes'                    => 'nullable|string|max:2000',
            'avatar'                   => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
        ]);

        $data['created_by'] = Auth::id();
        $data['status']     = 'active';
        unset($data['avatar']);

        $driver = Driver::create($data);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store("drivers/{$driver->id}/avatar", 'public');
            $driver->update(['avatar' => $path]);
        }

        return redirect()->route('drivers.show', $driver)
                         ->with('swal_success', 'Chauffeur créé avec succès.');
    }

    // ── Modification ───────────────────────────────────────────────────────

    public function edit(Driver $driver): View
    {
        $vehicles = Vehicle::available()->orderBy('brand')->orderBy('model')->get();
        return view('drivers.edit', compact('driver', 'vehicles'));
    }

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $data = $request->validate([
            'matricule'                => ['required', 'string', 'max:30', Rule::unique('drivers', 'matricule')->ignore($driver->id)],
            'full_name'                => 'required|string|max:100',
            'date_of_birth'            => 'nullable|date|before:today',
            'phone'                    => 'required|string|max:30',
            'email'                    => ['nullable', 'email', 'max:100', Rule::unique('drivers', 'email')->ignore($driver->id)],
            'address'                  => 'nullable|string|max:200',
            'hire_date'                => 'nullable|date',
            'contract_type'            => 'required|in:permanent,fixed_term,interim,contractor',
            'contract_end_date'        => 'nullable|date',
            // Permis : optionnel (peut être complété après la création du profil auto)
            'license_number'           => ['nullable', 'string', 'max:50', Rule::unique('drivers', 'license_number')->ignore($driver->id)],
            'license_categories'       => 'nullable|array',
            'license_categories.*'     => 'in:A,B,C,D,E,BE,CE',
            'license_expiry_date'      => 'nullable|date',
            'license_issuing_authority'=> 'nullable|string|max:100',
            'preferred_vehicle_id'     => 'nullable|exists:vehicles,id',
            'status'                   => 'sometimes|in:active,suspended,on_leave,terminated',
            'suspension_reason'        => 'nullable|string|max:255',
            'notes'                    => 'nullable|string|max:2000',
            'avatar'                   => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
        ]);

        unset($data['avatar']);
        $driver->update($data);

        if ($request->hasFile('avatar')) {
            if ($driver->avatar) {
                Storage::disk('public')->delete($driver->avatar);
            }
            $path = $request->file('avatar')->store("drivers/{$driver->id}/avatar", 'public');
            $driver->update(['avatar' => $path]);
        }

        return redirect()->route('drivers.show', $driver)
                         ->with('swal_success', 'Chauffeur mis à jour.');
    }

    // ── Statut ─────────────────────────────────────────────────────────────

    public function toggleStatus(Request $request, Driver $driver): RedirectResponse
    {
        $request->validate([
            'status'           => 'required|in:active,suspended,on_leave,terminated',
            'suspension_reason'=> 'nullable|string|max:255',
        ]);

        $driver->update([
            'status'            => $request->status,
            'suspension_reason' => $request->status === 'active' ? null : $request->suspension_reason,
        ]);

        return back()->with('swal_success', 'Statut du chauffeur mis à jour.');
    }

    // ── Archivage / Restauration ───────────────────────────────────────────

    public function destroy(Driver $driver): RedirectResponse
    {
        $driver->delete();
        return redirect()->route('drivers.index')
                         ->with('swal_success', "Chauffeur {$driver->full_name} archivé.");
    }

    public function restore(int $id): RedirectResponse
    {
        $driver = Driver::onlyTrashed()->findOrFail($id);
        $driver->restore();
        return redirect()->route('drivers.show', $driver)
                         ->with('swal_success', 'Chauffeur restauré.');
    }
}
