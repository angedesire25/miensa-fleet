<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SignupController extends Controller
{
    public function create(Request $request): View
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $selectedPlan = $request->query('plan', 'essential');

        return view('landlord.signup', compact('plans', 'selectedPlan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name'  => ['required', 'string', 'max:255'],
            'slug'          => ['required', 'string', 'max:63', 'regex:/^[a-z0-9\-]+$/', 'unique:App\Models\Tenant,slug'],
            'plan'          => ['required', 'string', 'exists:App\Models\Plan,slug'],
            'contact_name'  => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
        ], [
            'company_name.required'  => 'Le nom de la société est obligatoire.',
            'slug.required'          => "L'identifiant est obligatoire.",
            'slug.regex'             => "L'identifiant ne peut contenir que des lettres minuscules, chiffres et tirets.",
            'slug.unique'            => 'Cet identifiant est déjà utilisé. Choisissez-en un autre.',
            'plan.required'          => 'Veuillez choisir un plan.',
            'plan.exists'            => 'Plan invalide.',
            'contact_name.required'  => 'Le nom du contact est obligatoire.',
            'contact_email.required' => "L'email est obligatoire.",
            'contact_email.email'    => "L'adresse email n'est pas valide.",
        ]);

        $slug = Str::slug($validated['slug']);
        $plan = Plan::where('slug', $validated['plan'])->firstOrFail();

        $landlordDomain = config('multitenancy.landlord_domain', 'miensafleet.ci');
        $domain   = "{$slug}.{$landlordDomain}";
        $database = Tenant::databaseNameForSlug($slug);

        // ── 1. Créer la base de données ───────────────────────────────────
        DB::connection('landlord')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        // ── 2. Enregistrer le tenant ──────────────────────────────────────
        $trialEndsAt = $plan->trial_days > 0
            ? Carbon::now()->addDays($plan->trial_days)
            : null;

        $tenant = Tenant::create([
            'name'          => $validated['company_name'],
            'slug'          => $slug,
            'domain'        => $domain,
            'database'      => $database,
            'plan_id'       => $plan->id,
            'status'        => $plan->trial_days > 0 ? 'trial' : 'active',
            'trial_ends_at' => $trialEndsAt,
            'contact_name'  => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'max_vehicles'  => $plan->max_vehicles,
            'max_users'     => $plan->max_users,
        ]);

        // ── 3. Migrer la base tenant ──────────────────────────────────────
        config(['database.connections.tenant.database' => $database]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations',
            '--force'    => true,
        ]);

        // ── 4. Seeder rôles/permissions ───────────────────────────────────
        Artisan::call('db:seed', [
            '--class'    => 'RoleAndPermissionSeeder',
            '--database' => 'tenant',
            '--force'    => true,
        ]);

        // ── 5. Abonnement trial ───────────────────────────────────────────
        if ($trialEndsAt) {
            Subscription::create([
                'tenant_id'     => $tenant->id,
                'plan_id'       => $plan->id,
                'billing_cycle' => 'monthly',
                'amount'        => $plan->price_monthly,
                'currency'      => 'XOF',
                'status'        => 'trial',
                'starts_at'     => now(),
                'ends_at'       => $trialEndsAt,
            ]);
        }

        return redirect()->route('landlord.signup.success')
            ->with('tenant_domain', $domain)
            ->with('tenant_name', $validated['company_name'])
            ->with('trial_ends_at', $trialEndsAt?->isoFormat('D MMMM YYYY'))
            ->with('plan_name', $plan->name);
    }

    public function success(): View
    {
        if (!session()->has('tenant_domain')) {
            return redirect()->route('landlord.home');
        }

        return view('landlord.signup-success', [
            'domain'       => session('tenant_domain'),
            'tenantName'   => session('tenant_name'),
            'trialEndsAt'  => session('trial_ends_at'),
            'planName'     => session('plan_name'),
        ]);
    }
}
