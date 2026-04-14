<?php

namespace App\Http\Controllers\LandlordAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['status' => 'suspended', 'suspended_at' => now()]);
        return back()->with('success', "Tenant « {$tenant->name} » suspendu.");
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        $wasTrial = $tenant->status === 'trial';

        $tenant->update([
            'status'        => 'active',
            'suspended_at'  => null,
            'trial_ends_at' => null,
            // Marque la date de début d'abonnement payant si c'était un trial
            'subscribed_at' => $wasTrial ? now() : $tenant->subscribed_at,
        ]);

        $msg = $wasTrial
            ? "Abonnement de « {$tenant->name} » validé — trial converti en actif."
            : "Tenant « {$tenant->name} » réactivé.";

        return back()->with('success', $msg);
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

    public function impersonate(Tenant $tenant): RedirectResponse
    {
        return redirect("http://{$tenant->domain}");
    }
}
