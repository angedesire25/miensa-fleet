<?php

namespace App\Http\Controllers\LandlordAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Notifications\TenantAccessResetNotification;
use App\Notifications\TenantSuspendedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tenant::on('landlord')->with('plan');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $tenants = $query->latest()->paginate(20)->withQueryString();
        $plans   = Plan::on('landlord')->orderBy('sort_order')->get();

        return view('landlord-admin.tenants.index', compact('tenants', 'plans'));
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load('plan', 'subscriptions');
        return view('landlord-admin.tenants.show', compact('tenant'));
    }

    public function create(): View
    {
        $plans = Plan::on('landlord')->where('is_active', true)->orderBy('sort_order')->get();
        return view('landlord-admin.tenants.create', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'slug'          => ['required', 'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', 'max:30',
                                'unique:landlord.tenants,slug'],
            'contact_name'  => 'required|string|max:100',
            'contact_email' => 'required|email|max:150',
            'contact_phone' => 'nullable|string|max:30',
            'plan_id'       => 'required|exists:landlord.plans,id',
            'country'       => 'nullable|string|max:100',
            'timezone'      => 'nullable|string|max:60',
        ], [
            'slug.regex' => 'Le sous-domaine ne doit contenir que des lettres minuscules, chiffres et tirets (pas en début/fin).',
            'slug.unique' => 'Ce sous-domaine est déjà utilisé.',
        ]);

        $plan   = Plan::on('landlord')->findOrFail($validated['plan_id']);
        $slug   = $validated['slug'];
        $dbName = Tenant::databaseNameForSlug($slug);
        $domain = $slug . '.' . config('multitenancy.landlord_domain');

        // 1. Créer le tenant dans la base centrale
        $tenant = Tenant::create([
            'name'          => $validated['name'],
            'slug'          => $slug,
            'domain'        => $domain,
            'database'      => $dbName,
            'plan_id'       => $plan->id,
            'status'        => $plan->trial_days > 0 ? 'trial' : 'active',
            'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
            'contact_name'  => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'country'       => $validated['country'] ?? null,
            'timezone'      => $validated['timezone'] ?? 'Africa/Abidjan',
            'max_vehicles'  => $plan->max_vehicles,
            'max_users'     => $plan->max_users,
        ]);

        $originalDefault = DB::getDefaultConnection();

        try {
            // 2. Créer la base MySQL du tenant
            DB::connection('landlord')->statement(
                "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );

            // 3. Pointer la connexion 'tenant' vers la nouvelle BDD
            config(['database.connections.tenant.database' => $dbName]);
            DB::purge('tenant');
            DB::reconnect('tenant');
            DB::setDefaultConnection('tenant');

            // 4. Migrations tenant (database/migrations/ uniquement, pas le sous-dossier landlord/)
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path'     => 'database/migrations',
                '--force'    => true,
            ]);

            // 5. Rôles et permissions
            Artisan::call('db:seed', [
                '--class' => 'RoleAndPermissionSeeder',
                '--force' => true,
            ]);

            // 6. Compte administrateur du tenant
            $password  = Str::password(12, symbols: false);
            $adminUser = \App\Models\User::create([
                'name'                => $validated['contact_name'],
                'email'               => $validated['contact_email'],
                'password'            => Hash::make($password),
                'email_verified_at'   => now(),
                'password_changed_at' => now(),
                'status'              => 'active',
            ]);
            $adminUser->assignRole('admin');

        } catch (\Throwable $e) {
            // Rollback : supprimer l'enregistrement tenant
            $tenant->forceDelete();
            DB::setDefaultConnection($originalDefault);

            return back()->withInput()
                ->with('error', "Erreur lors de la création : " . $e->getMessage());
        }

        // 7. Rétablir la connexion par défaut
        DB::setDefaultConnection($originalDefault);
        DB::purge('tenant');

        $successMsg = "Société « {$tenant->name} » créée avec succès !"
            . " Panel : http://{$tenant->domain}"
            . " — Email : {$validated['contact_email']}"
            . " — Mot de passe temporaire : {$password}";

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', $successMsg)
            ->with('tenant_password', $password);
    }

    public function suspend(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Le motif de suspension est obligatoire.',
        ]);

        $tenant->update([
            'status'           => 'suspended',
            'suspended_at'     => now(),
            'suspension_reason'=> $request->reason,
        ]);

        // Notifier l'administrateur du tenant via son email de contact (landlord DB)
        try {
            $tenant->makeCurrent();

            $superAdmin = \App\Models\User::whereHas(
                'roles', fn ($q) => $q->where('name', 'super_admin')
            )->first();

            if ($superAdmin) {
                $superAdmin->notify(new TenantSuspendedNotification($tenant, $request->reason));
            }

            Tenant::forgetCurrent();
        } catch (\Throwable $e) {
            Log::warning("Notification suspend failed for tenant {$tenant->slug}: " . $e->getMessage());
            Tenant::forgetCurrent();
        }

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', "« {$tenant->name} » suspendu. Leurs utilisateurs ne peuvent plus se connecter.");
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        $wasTrial = $tenant->status === 'trial';

        $tenant->update([
            'status'           => 'active',
            'suspended_at'     => null,
            'suspension_reason'=> null,
            'trial_ends_at'    => null,
            'subscribed_at'    => $wasTrial ? now() : $tenant->subscribed_at,
        ]);

        $msg = $wasTrial
            ? "Abonnement de « {$tenant->name} » validé — trial converti en actif."
            : "« {$tenant->name} » réactivé avec succès.";

        return back()->with('success', $msg);
    }

    public function destroy(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'confirm_slug' => ['required', 'string', function ($attr, $value, $fail) use ($tenant) {
                if ($value !== $tenant->slug) {
                    $fail("Le sous-domaine saisi ne correspond pas. Suppression annulée.");
                }
            }],
        ]);

        $name     = $tenant->name;
        $dbName   = $tenant->database;
        $dropDb   = $request->boolean('drop_database');

        // Soft-delete du tenant (status cancelled)
        $tenant->update(['status' => 'cancelled']);
        $tenant->delete();

        // Suppression optionnelle de la base MySQL
        if ($dropDb && $dbName) {
            try {
                DB::connection('landlord')->statement("DROP DATABASE IF EXISTS `{$dbName}`");
            } catch (\Throwable $e) {
                Log::error("Impossible de supprimer la base {$dbName} : " . $e->getMessage());
                return redirect()->route('admin.tenants.index')
                    ->with('success', "« {$name} » supprimé.")
                    ->with('error', "La base de données n'a pas pu être supprimée : " . $e->getMessage());
            }
        }

        $msg = $dropDb
            ? "« {$name} » supprimé et base de données `{$dbName}` effacée définitivement."
            : "« {$name} » supprimé. La base de données `{$dbName}` est conservée.";

        return redirect()->route('admin.tenants.index')->with('success', $msg);
    }

    public function changePlan(Tenant $tenant, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:landlord.plans,id',
        ]);

        $plan = Plan::on('landlord')->findOrFail($validated['plan_id']);

        $tenant->update([
            'plan_id'      => $plan->id,
            'max_vehicles' => $plan->max_vehicles,
            'max_users'    => $plan->max_users,
        ]);

        return back()->with('success', "Plan mis à jour : {$plan->name}");
    }

    public function resetAccess(Tenant $tenant): RedirectResponse
    {
        $originalDefault = DB::getDefaultConnection();

        // Basculer sur la BDD du tenant
        config(['database.connections.tenant.database' => $tenant->database]);
        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');

        try {
            // Trouver le compte admin principal (super_admin en priorité, sinon admin)
            $adminUser = \App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first()
                      ?? \App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();

            if (! $adminUser) {
                DB::setDefaultConnection($originalDefault);
                DB::purge('tenant');
                return back()->with('error', "Aucun compte admin trouvé dans la base de {$tenant->name}.");
            }

            $newPassword = Str::password(12, symbols: false);

            $adminUser->update([
                'password'            => Hash::make($newPassword),
                'password_changed_at' => null, // Force le changement à la prochaine connexion
            ]);

            // Notifier l'utilisateur par email (tentative silencieuse)
            try {
                $adminUser->notify(new TenantAccessResetNotification($tenant, $newPassword));
            } catch (\Throwable $e) {
                Log::warning("Notification reset access failed for tenant {$tenant->slug}: " . $e->getMessage());
            }

            DB::setDefaultConnection($originalDefault);
            DB::purge('tenant');

            return back()
                ->with('access_reset', [
                    'email'    => $adminUser->email,
                    'password' => $newPassword,
                    'tenant'   => $tenant->name,
                    'domain'   => $tenant->domain,
                ]);

        } catch (\Throwable $e) {
            DB::setDefaultConnection($originalDefault);
            DB::purge('tenant');

            return back()->with('error', "Erreur lors de la réinitialisation : " . $e->getMessage());
        }
    }

    public function impersonate(Tenant $tenant): RedirectResponse
    {
        return redirect("http://{$tenant->domain}");
    }
}
