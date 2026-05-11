<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Provisionne un nouveau client (tenant) :
 *   1. Vérifie que la base de données existe
 *   2. Crée l'enregistrement dans la table landlord `tenants`
 *   3. Exécute les migrations sur la nouvelle base
 *   4. Exécute le seeder de rôles/permissions
 *   5. Crée un abonnement en période d'essai
 *
 * Usage :
 *   php artisan tenant:create
 *   php artisan tenant:create --name="Geomatos SARL" --slug=geomatos --plan=essential
 *   php artisan tenant:create --slug=geomatos --db-host=mysql.host.com --db-username=user --db-password=secret
 */
class CreateTenantCommand extends Command
{
    protected $signature = 'tenant:create
        {--name=        : Nom complet de la société}
        {--slug=        : Identifiant unique (ex: geomatos) — utilisé pour le sous-domaine}
        {--plan=        : Plan tarifaire : free|essential|pro (défaut : free)}
        {--email=       : Email du contact principal}
        {--phone=       : Téléphone du contact}
        {--contact=     : Nom du contact principal}
        {--domain=      : Domaine personnalisé (optionnel, défaut : {slug}.miensafleet.ci)}
        {--database=    : Nom exact de la base de données (défaut : généré depuis le slug)}
        {--db-host=     : Hôte de la base de données (défaut : valeur env)}
        {--db-port=     : Port de la base de données (défaut : 3306)}
        {--db-username= : Identifiant de connexion (défaut : valeur env)}
        {--db-password= : Mot de passe de connexion (défaut : valeur env)}';

    protected $description = 'Crée et provisionne un nouveau tenant (client MiensaFleet)';

    public function handle(): int
    {
        $this->info('');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('   MiensaFleet — Provisioning tenant');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // ── 1. Collecter les informations ─────────────────────────────────
        $name = $this->option('name') ?: $this->ask('Nom de la société');
        $slug = $this->option('slug') ?: Str::slug($this->ask('Slug (sous-domaine)', Str::slug($name)));
        $slug = Str::slug($slug);

        if (Tenant::where('slug', $slug)->exists()) {
            $this->error("Un tenant avec le slug « {$slug} » existe déjà.");
            return self::FAILURE;
        }

        $planSlug  = $this->option('plan')    ?: $this->choice('Plan', ['free', 'essential', 'pro'], 'free');
        $email     = $this->option('email')   ?: $this->ask('Email du contact', null);
        $phone     = $this->option('phone')   ?: $this->ask('Téléphone', null);
        $contact   = $this->option('contact') ?: $this->ask('Nom du contact', null);

        $plan = Plan::where('slug', $planSlug)->first();
        if (!$plan) {
            $this->error("Plan « {$planSlug} » introuvable. Exécutez d'abord : php artisan db:seed --class=PlanSeeder");
            return self::FAILURE;
        }

        $landlordDomain = config('multitenancy.landlord_domain', 'miensafleet.ci');
        $domain   = $this->option('domain')   ?: "{$slug}.{$landlordDomain}";
        $database = $this->option('database') ?: Tenant::databaseNameForSlug($slug);
        $dbHost   = $this->option('db-host')     ?: null;
        $dbPort   = $this->option('db-port')     ? (int) $this->option('db-port') : null;
        $dbUser   = $this->option('db-username') ?: null;
        $dbPass   = $this->option('db-password') ?: null;

        // ── Récapitulatif ─────────────────────────────────────────────────
        $this->newLine();
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Société',         $name],
                ['Slug',            $slug],
                ['Domaine',         $domain],
                ['Base de données', $database],
                ['Hôte DB',         $dbHost ?? '(env par défaut)'],
                ['Identifiant DB',  $dbUser ?? '(env par défaut)'],
                ['Plan',            $plan->name . ' (' . number_format($plan->price_monthly, 0, ',', ' ') . ' FCFA/mois)'],
                ['Contact',         $contact ?? '—'],
                ['Email',           $email   ?? '—'],
                ['Téléphone',       $phone   ?? '—'],
            ]
        );

        if (!$this->confirm('Confirmer la création ?', true)) {
            $this->info('Annulé.');
            return self::SUCCESS;
        }

        // ── 2. Vérifier que la base de données existe ────────────────────
        $this->info("→ Vérification de la base de données « {$database} »…");
        $connOverrides = ['database' => $database];
        if ($dbHost)   $connOverrides['host']     = $dbHost;
        if ($dbPort)   $connOverrides['port']      = $dbPort;
        if ($dbUser)   $connOverrides['username']  = $dbUser;
        if ($dbPass)   $connOverrides['password']  = $dbPass;

        foreach ($connOverrides as $k => $v) {
            config(["database.connections.tenant.{$k}" => $v]);
        }
        DB::purge('tenant');
        try {
            DB::connection('tenant')->getPdo();
        } catch (\Exception $e) {
            $this->error("La base « {$database} » n'existe pas ou n'est pas accessible.");
            $this->line("  Vérifiez que la base existe sur votre hébergeur et que les identifiants sont corrects.");
            $this->line("  Puis relancez : <comment>php artisan tenant:create --slug={$slug}</comment>");
            return self::FAILURE;
        }
        $this->line("  <info>✓</info> Base accessible.");

        // ── 3. Créer l'enregistrement tenant ─────────────────────────────
        $this->info("→ Enregistrement du tenant dans la base landlord…");
        $trialEndsAt = $plan->trial_days > 0
            ? Carbon::now()->addDays($plan->trial_days)
            : null;

        $tenant = Tenant::create([
            'name'          => $name,
            'slug'          => $slug,
            'domain'        => $domain,
            'database'      => $database,
            'db_host'       => $dbHost,
            'db_port'       => $dbPort ?? 3306,
            'db_username'   => $dbUser,
            'db_password'   => $dbPass ? \Illuminate\Support\Facades\Crypt::encryptString($dbPass) : null,
            'plan_id'       => $plan->id,
            'status'        => $plan->trial_days > 0 ? 'trial' : 'active',
            'trial_ends_at' => $trialEndsAt,
            'contact_name'  => $contact,
            'contact_email' => $email,
            'contact_phone' => $phone,
            'max_vehicles'  => $plan->max_vehicles,
            'max_users'     => $plan->max_users,
        ]);
        $this->line("  <info>✓</info> Tenant #{$tenant->id} enregistré.");

        // ── 4. Migrer la base tenant ──────────────────────────────────────
        $this->info("→ Exécution des migrations sur « {$database} »…");
        config(['database.connections.tenant.database' => $database]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations',
            '--force'    => true,
        ]);
        $this->line(Artisan::output());
        $this->line("  <info>✓</info> Migrations exécutées.");

        // ── 5. Seeder rôles/permissions ───────────────────────────────────
        $this->info("→ Création des rôles et permissions…");
        Artisan::call('db:seed', [
            '--class'    => 'RoleAndPermissionSeeder',
            '--database' => 'tenant',
            '--force'    => true,
        ]);
        $this->line("  <info>✓</info> Rôles et permissions créés.");

        // ── 6. Abonnement trial ───────────────────────────────────────────
        if ($trialEndsAt) {
            Subscription::create([
                'tenant_id'    => $tenant->id,
                'plan_id'      => $plan->id,
                'billing_cycle'=> 'monthly',
                'amount'       => $plan->price_monthly,
                'currency'     => 'XOF',
                'status'       => 'trial',
                'starts_at'    => now(),
                'ends_at'      => $trialEndsAt,
            ]);
            $this->line("  <info>✓</info> Période d'essai de {$plan->trial_days} jours créée.");
        }

        // ── 7. Entrée hosts locale (dev uniquement) ───────────────────────
        if (app()->isLocal()) {
            $this->newLine();
            $hostsFile = PHP_OS_FAMILY === 'Windows'
                ? 'C:\\Windows\\System32\\drivers\\etc\\hosts'
                : '/etc/hosts';

            $hostsContent = @file_get_contents($hostsFile) ?? '';
            if (!str_contains($hostsContent, $domain)) {
                $added = @file_put_contents(
                    $hostsFile,
                    PHP_EOL . "127.0.0.1      {$domain}    #laragon magic!" . PHP_EOL,
                    FILE_APPEND
                );
                if ($added !== false) {
                    $this->line("  <info>✓</info> Entrée hosts ajoutée : {$domain}");
                } else {
                    $this->warn("  ⚠  Impossible d'écrire dans {$hostsFile}.");
                    $this->line("     Ajoutez manuellement : 127.0.0.1      {$domain}");
                    $this->line("     (ou lancez : <comment>add-hosts.ps1</comment> en tant qu'administrateur)");
                }
            } else {
                $this->line("  <info>✓</info> Hosts : {$domain} déjà présent.");
            }
        }

        // ── Succès ────────────────────────────────────────────────────────
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("   ✅  Tenant « {$name} » provisionné avec succès !");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->table(['Info', 'Valeur'], [
            ['URL',        "http://{$domain}"],
            ['Base',       $database],
            ['Plan',       $plan->name],
            ['Essai jusqu\'au', $trialEndsAt?->isoFormat('D MMM YYYY') ?? 'Aucun'],
        ]);

        return self::SUCCESS;
    }
}
