<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverDocument;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DriverController extends Controller
{
    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $showArchived = $request->boolean('archived');

        $query = $showArchived
            ? Driver::onlyTrashed()->with(['activeAssignment.vehicle', 'preferredVehicle'])
            : Driver::with(['activeAssignment.vehicle', 'preferredVehicle']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('full_name', 'like', "%{$q}%")
                   ->orWhere('matricule', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if (!$showArchived && $request->filled('status') && $request->status !== 'all') {
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

        return view('drivers.index', compact('drivers', 'stats', 'showArchived'));
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Driver $driver): View
    {
        $driver->load([
            'preferredVehicle',
            'documents',
            'activeAssignment.vehicle',
            'assignments' => fn($q) => $q->with('vehicle')->latest('datetime_start')->limit(10),
            'infractions'  => fn($q) => $q->with('vehicle')->latest()->limit(10),
            'incidents'    => fn($q) => $q->with('vehicle')->latest('datetime_occurred')->limit(10),
        ]);

        // Compte utilisateur lié (cherche par driver_id ou par email)
        $linkedUser = User::with('roles')->where('driver_id', $driver->id)->first()
            ?? User::with('roles')->where('email', $driver->email)->whereNotNull('email')->first();

        return view('drivers.show', compact('driver', 'linkedUser'));
    }

    /**
     * Crée un compte utilisateur driver_user pour un profil chauffeur existant.
     */
    public function createAccount(Request $request, Driver $driver): RedirectResponse
    {
        $request->validate([
            'account_email'    => 'required|email|max:100|unique:users,email',
            'account_password' => 'required|string|min:6',
        ]);

        if (User::where('driver_id', $driver->id)->exists()) {
            return back()->with('swal_error', 'Ce chauffeur possède déjà un compte utilisateur.');
        }

        $user = User::create([
            'name'                => $driver->full_name,
            'email'               => $request->account_email,
            'phone'               => $driver->phone,
            'password'            => Hash::make($request->account_password),
            'status'              => 'active',
            'driver_id'           => $driver->id,
            'email_verified_at'   => now(),
            'password_changed_at' => null,
            'created_by'          => Auth::id(),
        ]);
        $user->syncRoles(['driver_user']);

        return redirect()->route('drivers.show', $driver)
            ->with('swal_success', "Compte créé pour {$driver->full_name}.")
            ->with('new_password', $request->account_password)
            ->with('new_password_user', $driver->full_name);
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
            'license_number'           => 'nullable|string|max:50|unique:drivers,license_number',
            'license_categories'       => 'nullable|array',
            'license_categories.*'     => 'in:A,B,C,D,E,BE,CE',
            'license_expiry_date'      => 'nullable|date',
            'license_issuing_authority'=> 'nullable|string|max:100',
            'preferred_vehicle_id'     => 'nullable|exists:vehicles,id',
            'notes'                    => 'nullable|string|max:2000',
            'avatar'                   => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
            // Documents d'identité
            'license_file'             => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:5120',
            'license_issue_date'       => 'nullable|date',
            'national_id_number'       => 'nullable|string|max:50',
            'national_id_issue_date'   => 'nullable|date',
            'national_id_expiry_date'  => 'nullable|date',
            'national_id_file'         => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:5120',
            // Visite médicale
            'medical_issue_date'       => 'nullable|date',
            'medical_expiry_date'      => 'nullable|date|after_or_equal:medical_issue_date',
            'medical_result'           => 'nullable|in:fit,fit_with_restrictions,unfit',
            'medical_file'             => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:5120',
            // Compte d'accès optionnel
            'create_account'           => 'nullable|boolean',
            'account_email'            => 'nullable|required_if:create_account,1|email|max:100|unique:users,email',
            'account_password'         => 'nullable|required_if:create_account,1|string|min:6',
        ]);

        $data['created_by'] = Auth::id();
        $data['status']     = 'active';

        $createAccount   = (bool) ($data['create_account'] ?? false);
        $accountEmail    = $data['account_email'] ?? null;
        $accountPassword = $data['account_password'] ?? null;

        unset($data['avatar'], $data['license_file'], $data['license_issue_date'],
              $data['national_id_number'], $data['national_id_issue_date'],
              $data['national_id_expiry_date'], $data['national_id_file'],
              $data['medical_issue_date'], $data['medical_expiry_date'],
              $data['medical_result'], $data['medical_file'],
              $data['create_account'], $data['account_email'], $data['account_password']);

        $driver = Driver::create($data);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store("drivers/{$driver->id}/avatar", 'public');
            $driver->update(['avatar' => $path]);
        }

        // Scan du permis de conduire
        if ($request->hasFile('license_file') || $request->filled('license_issue_date')) {
            $licPath = $request->hasFile('license_file')
                ? $request->file('license_file')->store("drivers/{$driver->id}/documents", 'public')
                : null;
            DriverDocument::create([
                'driver_id'       => $driver->id,
                'type'            => 'license',
                'document_number' => $driver->license_number,
                'issue_date'      => $request->license_issue_date ?: null,
                'expiry_date'     => $driver->license_expiry_date?->format('Y-m-d'),
                'file_path'       => $licPath,
                'status'          => 'valid',
                'created_by'      => Auth::id(),
            ]);
        }

        // CNI
        if ($request->hasFile('national_id_file') || $request->filled('national_id_number')) {
            $cniPath = $request->hasFile('national_id_file')
                ? $request->file('national_id_file')->store("drivers/{$driver->id}/documents", 'public')
                : null;
            DriverDocument::create([
                'driver_id'       => $driver->id,
                'type'            => 'national_id',
                'document_number' => $request->national_id_number ?: null,
                'issue_date'      => $request->national_id_issue_date ?: null,
                'expiry_date'     => $request->national_id_expiry_date ?: null,
                'file_path'       => $cniPath,
                'status'          => 'valid',
                'created_by'      => Auth::id(),
            ]);
        }

        // Visite médicale
        if ($request->filled('medical_issue_date') || $request->filled('medical_expiry_date') || $request->hasFile('medical_file')) {
            $medPath = $request->hasFile('medical_file')
                ? $request->file('medical_file')->store("drivers/{$driver->id}/documents", 'public')
                : null;
            DriverDocument::create([
                'driver_id'      => $driver->id,
                'type'           => 'medical_fitness',
                'issue_date'     => $request->medical_issue_date ?: null,
                'expiry_date'    => $request->medical_expiry_date ?: null,
                'medical_result' => $request->medical_result ?? 'fit',
                'file_path'      => $medPath,
                'status'         => 'valid',
                'created_by'     => Auth::id(),
            ]);
        }

        // Créer le compte utilisateur lié si demandé
        $newUserPassword = null;
        if ($createAccount && $accountEmail && $accountPassword) {
            $user = User::create([
                'name'                => $driver->full_name,
                'email'               => $accountEmail,
                'phone'               => $driver->phone,
                'password'            => Hash::make($accountPassword),
                'status'              => 'active',
                'driver_id'           => $driver->id,
                'email_verified_at'   => now(),
                'password_changed_at' => null, // Force le changement à la prochaine connexion
                'created_by'          => Auth::id(),
            ]);
            $user->syncRoles(['driver_user']);
            $newUserPassword = $accountPassword;
        }

        $msg = 'Chauffeur créé avec succès.';
        if ($newUserPassword) {
            $msg .= " Un compte d'accès a été créé (email : {$accountEmail}).";
        }

        $redirect = redirect()->route('drivers.show', $driver)->with('swal_success', $msg);

        if ($newUserPassword) {
            $redirect = $redirect->with('new_password', $newUserPassword)
                                 ->with('new_password_user', $driver->full_name);
        }

        return $redirect;
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
            // Documents d'identité
            'license_file'             => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:5120',
            'license_issue_date'       => 'nullable|date',
            'national_id_number'       => 'nullable|string|max:50',
            'national_id_issue_date'   => 'nullable|date',
            'national_id_expiry_date'  => 'nullable|date',
            'national_id_file'         => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:5120',
            // Visite médicale
            'medical_issue_date'       => 'nullable|date',
            'medical_expiry_date'      => 'nullable|date|after_or_equal:medical_issue_date',
            'medical_result'           => 'nullable|in:fit,fit_with_restrictions,unfit',
            'medical_file'             => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:5120',
        ]);

        unset($data['avatar'], $data['license_file'], $data['license_issue_date'],
              $data['national_id_number'], $data['national_id_issue_date'],
              $data['national_id_expiry_date'], $data['national_id_file'],
              $data['medical_issue_date'], $data['medical_expiry_date'],
              $data['medical_result'], $data['medical_file']);
        $driver->update($data);

        if ($request->hasFile('avatar')) {
            if ($driver->avatar) {
                Storage::disk('public')->delete($driver->avatar);
            }
            $path = $request->file('avatar')->store("drivers/{$driver->id}/avatar", 'public');
            $driver->update(['avatar' => $path]);
        }

        // Mise à jour / création du document permis
        if ($request->hasFile('license_file') || $request->filled('license_issue_date')) {
            $licDoc = $driver->documents()->where('type', 'license')->first();
            $licPath = $licDoc?->file_path;
            if ($request->hasFile('license_file')) {
                if ($licPath) Storage::disk('public')->delete($licPath);
                $licPath = $request->file('license_file')->store("drivers/{$driver->id}/documents", 'public');
            }
            $docData = [
                'document_number' => $driver->license_number,
                'issue_date'      => $request->license_issue_date ?: ($licDoc?->issue_date?->format('Y-m-d')),
                'expiry_date'     => $driver->license_expiry_date?->format('Y-m-d'),
                'file_path'       => $licPath,
                'status'          => 'valid',
                'created_by'      => Auth::id(),
            ];
            if ($licDoc) {
                $licDoc->update($docData);
            } else {
                $driver->documents()->create(['type' => 'license'] + $docData);
            }
        }

        // Mise à jour / création du document CNI
        if ($request->hasFile('national_id_file') || $request->filled('national_id_number')) {
            $cniDoc  = $driver->documents()->where('type', 'national_id')->first();
            $cniPath = $cniDoc?->file_path;
            if ($request->hasFile('national_id_file')) {
                if ($cniPath) Storage::disk('public')->delete($cniPath);
                $cniPath = $request->file('national_id_file')->store("drivers/{$driver->id}/documents", 'public');
            }
            $cniData = [
                'document_number' => $request->national_id_number ?: ($cniDoc?->document_number),
                'issue_date'      => $request->national_id_issue_date ?: ($cniDoc?->issue_date?->format('Y-m-d')),
                'expiry_date'     => $request->national_id_expiry_date ?: ($cniDoc?->expiry_date?->format('Y-m-d')),
                'file_path'       => $cniPath,
                'status'          => 'valid',
                'created_by'      => Auth::id(),
            ];
            if ($cniDoc) {
                $cniDoc->update($cniData);
            } else {
                $driver->documents()->create(['type' => 'national_id'] + $cniData);
            }
        }

        // Mise à jour / création du document visite médicale
        if ($request->hasFile('medical_file') || $request->filled('medical_issue_date') || $request->filled('medical_expiry_date')) {
            $medDoc  = $driver->documents()->where('type', 'medical_fitness')->first();
            $medPath = $medDoc?->file_path;
            if ($request->hasFile('medical_file')) {
                if ($medPath) Storage::disk('public')->delete($medPath);
                $medPath = $request->file('medical_file')->store("drivers/{$driver->id}/documents", 'public');
            }
            $medData = [
                'issue_date'     => $request->medical_issue_date ?: ($medDoc?->issue_date?->format('Y-m-d')),
                'expiry_date'    => $request->medical_expiry_date ?: ($medDoc?->expiry_date?->format('Y-m-d')),
                'medical_result' => $request->medical_result ?? ($medDoc?->medical_result ?? 'fit'),
                'file_path'      => $medPath,
                'status'         => 'valid',
                'created_by'     => Auth::id(),
            ];
            if ($medDoc) {
                $medDoc->update($medData);
            } else {
                $driver->documents()->create(['type' => 'medical_fitness'] + $medData);
            }
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

    /**
     * Suppression définitive (irréversible) — réservée aux admins.
     * Les données historiques (affectations, infractions, fiches…) sont conservées
     * avec les FK mises à NULL (SET NULL en base). Seuls les documents du profil
     * sont supprimés. Le compte utilisateur lié est archivé (soft delete).
     */
    public function forceDestroy(int $id): RedirectResponse
    {
        $driver = Driver::withTrashed()->findOrFail($id);
        $name   = $driver->full_name;

        // Supprimer les documents du profil (fichiers + enregistrements)
        foreach ($driver->documents()->get() as $doc) {
            if ($doc->file_path) Storage::disk('public')->delete($doc->file_path);
        }
        $driver->documents()->delete();

        // Archiver le compte utilisateur lié pour qu'il n'apparaisse plus dans les listes
        $linkedUser = \App\Models\User::where('driver_id', $driver->id)->first();
        if ($linkedUser && ! $linkedUser->trashed()) {
            $linkedUser->update(['driver_id' => null]);
            $linkedUser->delete(); // soft delete
        } elseif ($linkedUser) {
            $linkedUser->update(['driver_id' => null]);
        }

        $driver->forceDelete();

        return redirect()->route('drivers.index')
                         ->with('swal_success', "Chauffeur « {$name} » supprimé définitivement. Le compte utilisateur associé a été archivé.");
    }
}
