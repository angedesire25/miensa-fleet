<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DriverProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly DriverProfileService $driverProfileService) {}

    /**
     * Liste paginée avec filtres : recherche, rôle, statut.
     */
    public function index(Request $request): View
    {
        $query = User::with('roles')->withTrashed($request->boolean('avec_supprimes'));

        // ── Filtres ──────────────────────────────────────────────────────────
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->role($role);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        // ── Statistiques rapides ─────────────────────────────────────────────
        $stats = [
            'total'     => User::count(),
            'actifs'    => User::where('status', 'active')->count(),
            'suspendus' => User::where('status', 'suspended')->count(),
            'supprimes' => User::onlyTrashed()->count(),
        ];

        $roles = Role::orderBy('level')->get();

        return view('admin.users.index', compact('users', 'stats', 'roles'));
    }

    /**
     * Formulaire de création.
     */
    public function create(): View
    {
        $roles = Role::orderBy('level')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Enregistre un nouvel utilisateur.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'job_title'  => ['nullable', 'string', 'max:100'],
            'status'     => ['required', 'in:active,suspended'],
            'role'       => ['required', 'exists:roles,name'],
            'password'   => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            // Champs spécifiques chauffeur (requis si rôle = driver_user)
            'driver_license_number'           => ['nullable', 'string', 'max:50', 'unique:drivers,license_number',
                                                   $request->input('role') === 'driver_user' ? 'required' : 'nullable'],
            'driver_license_expiry_date'      => ['nullable', 'date', 'after:today',
                                                   $request->input('role') === 'driver_user' ? 'required' : 'nullable'],
            'driver_license_categories'       => ['nullable', 'array',
                                                   $request->input('role') === 'driver_user' ? 'required|min:1' : 'nullable'],
            'driver_license_categories.*'     => ['in:A,B,C,D,E,BE,CE'],
            'driver_license_issuing_authority'=> ['nullable', 'string', 'max:100'],
            'driver_date_of_birth'            => ['nullable', 'date', 'before:today'],
            'driver_hire_date'                => ['nullable', 'date'],
            'driver_contract_type'            => ['nullable', 'in:permanent,fixed_term,interim,contractor'],
        ]);

        // Empêche un admin de créer un super_admin
        if ($data['role'] === 'super_admin' && ! auth()->user()->hasRole('super_admin')) {
            return back()->withErrors(['role' => 'Seul un super administrateur peut créer ce type de compte.']);
        }

        $user = User::create([
            'name'                => $data['name'],
            'email'               => $data['email'],
            'phone'               => $data['phone'] ?? null,
            'department'          => $data['department'] ?? null,
            'job_title'           => $data['job_title'] ?? null,
            'status'              => $data['status'],
            'password'            => Hash::make($data['password']),
            'email_verified_at'   => now(),
            'password_changed_at' => now(),
            'created_by'          => auth()->id(),
        ]);

        $user->syncRoles([$data['role']]);

        // Création automatique du profil chauffeur si le rôle est driver_user
        $driverCreated = null;
        if ($data['role'] === 'driver_user') {
            // Rassembler les informations chauffeur saisies dans le formulaire
            $driverExtra = array_filter([
                'license_number'            => $data['driver_license_number'] ?? null,
                'license_expiry_date'       => $data['driver_license_expiry_date'] ?? null,
                'license_categories'        => $data['driver_license_categories'] ?? null,
                'license_issuing_authority' => $data['driver_license_issuing_authority'] ?? null,
                'date_of_birth'             => $data['driver_date_of_birth'] ?? null,
                'hire_date'                 => $data['driver_hire_date'] ?? null,
                'contract_type'             => $data['driver_contract_type'] ?? 'permanent',
            ]);
            $driverCreated = $this->driverProfileService->ensureDriverProfile($user, $driverExtra);
        }

        $msg = "Compte de {$user->name} créé avec succès.";
        if ($driverCreated) {
            $msg .= " Un profil chauffeur a été créé automatiquement ({$driverCreated->matricule}) — pensez à compléter les informations du permis.";
        }

        return redirect()->route('admin.users.show', $user)
                         ->with('swal_success', $msg);
    }

    /**
     * Fiche détail d'un utilisateur.
     */
    public function show(User $user): View
    {
        $user->load('roles');
        $activities = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.users.show', compact('user', 'activities'));
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(User $user): View
    {
        $roles = Role::orderBy('level')->get();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Met à jour un utilisateur (infos + rôle).
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone'      => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'job_title'  => ['nullable', 'string', 'max:100'],
            'status'     => ['required', 'in:active,suspended'],
            'role'       => ['required', 'exists:roles,name'],
            'suspension_reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Empêche de modifier le rôle d'un super_admin si on n'est pas super_admin
        if ($user->hasRole('super_admin') && ! auth()->user()->hasRole('super_admin')) {
            return back()->withErrors(['role' => 'Vous ne pouvez pas modifier le rôle d\'un super administrateur.']);
        }

        // Empêche de s'auto-dégrader si on est le seul super_admin
        if (auth()->id() === $user->id && $data['role'] !== 'super_admin' && $user->hasRole('super_admin')) {
            $otherSuperAdmins = User::role('super_admin')->where('id', '!=', $user->id)->count();
            if ($otherSuperAdmins === 0) {
                return back()->withErrors(['role' => 'Impossible de retirer le rôle super_admin : vous êtes le seul.']);
            }
        }

        $user->update([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'phone'              => $data['phone'],
            'department'         => $data['department'],
            'job_title'          => $data['job_title'],
            'status'             => $data['status'],
            'suspension_reason'  => $data['status'] === 'suspended' ? ($data['suspension_reason'] ?? null) : null,
        ]);

        $user->syncRoles([$data['role']]);

        // Si le rôle devient driver_user, s'assurer qu'un profil chauffeur existe
        $driverCreated = null;
        if ($data['role'] === 'driver_user') {
            $user->refresh(); // Recharger après syncRoles
            $driverCreated = $this->driverProfileService->ensureDriverProfile($user);
        }

        $msg = "Compte de {$user->name} mis à jour.";
        if ($driverCreated && $user->wasRecentlyCreated === false) {
            $msg .= " Profil chauffeur lié ({$driverCreated->matricule}).";
        }

        return redirect()->route('admin.users.show', $user)
                         ->with('swal_success', $msg);
    }

    /**
     * Suppression douce (soft delete).
     * Un super_admin ne peut pas être supprimé par un admin.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['delete' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        if ($user->hasRole('super_admin') && ! auth()->user()->hasRole('super_admin')) {
            return back()->withErrors(['delete' => 'Seul un super administrateur peut supprimer ce compte.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('swal_success', "Compte de {$name} archivé.");
    }

    /**
     * Restaure un compte archivé (soft-deleted).
     */
    public function restore(int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.show', $user)
                         ->with('swal_success', "Compte de {$user->name} restauré.");
    }

    /**
     * Bascule le statut actif/suspendu d'un utilisateur.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['status' => 'Vous ne pouvez pas modifier votre propre statut.']);
        }

        if ($user->status === 'active') {
            $request->validate(['suspension_reason' => ['nullable', 'string', 'max:500']]);
            $user->update([
                'status'             => 'suspended',
                'suspension_reason'  => $request->input('suspension_reason'),
            ]);
            $message = "Compte de {$user->name} suspendu.";
        } else {
            $user->update(['status' => 'active', 'suspension_reason' => null]);
            $message = "Compte de {$user->name} réactivé.";
        }

        return back()->with('swal_success', $message);
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur.
     * Génère un mot de passe aléatoire affiché une seule fois.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $newPassword = 'Fleet@' . rand(1000, 9999);

        $user->update([
            'password'            => Hash::make($newPassword),
            'password_changed_at' => null, // Force le changement à la prochaine connexion
        ]);

        return back()->with('new_password', $newPassword)
                     ->with('new_password_user', $user->name);
    }
}
