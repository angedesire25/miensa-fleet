<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Initialise un tenant existant dans la BDD landlord sur une base déjà créée.
 *
 * La base doit exister et être accessible avant d'appeler cette commande.
 *
 * Usage :
 *   php artisan tenant:init geomatos
 *   php artisan tenant:init geomatos --email=admin@geomatos.com --name="Administrateur"
 */
class InitTenantCommand extends Command
{
    protected $signature = 'tenant:init
        {slug             : Slug du tenant (ex: geomatos)}
        {--email=         : Email du super-admin à créer (défaut: admin@{slug}.com)}
        {--name=          : Nom du super-admin (défaut: Administrateur)}
        {--password=      : Mot de passe temporaire (généré si omis)}
        {--force          : Ne pas demander de confirmation}';

    protected $description = 'Initialise un tenant existant : migrations + rôles + super-admin';

    public function handle(): int
    {
        $slug = $this->argument('slug');

        $this->line('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("  MiensaFleet — Init tenant : <info>{$slug}</info>");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // ── 1. Vérifier que le tenant existe dans la DB landlord ──────────
        $tenant = Tenant::where('slug', $slug)->first();
        if (! $tenant) {
            $this->error("Aucun tenant avec le slug « {$slug} » trouvé dans la base landlord.");
            $this->line("  Créez d'abord la société depuis le panel admin.");
            return self::FAILURE;
        }

        $dbName = $tenant->database;
        $this->line("  Tenant    : <info>{$tenant->name}</info>");
        $this->line("  Base      : <info>{$dbName}</info>");
        $this->line("  Domaine   : <info>{$tenant->domain}</info>");

        // ── 2. Vérifier que la base MySQL existe et est accessible ────────
        $this->line('');
        $this->info('→ Vérification de la base de données…');
        try {
            // Appliquer les credentials spécifiques au tenant s'ils existent
            $overrides = ['database' => $dbName];
            if (filled($tenant->db_host))     $overrides['host']     = $tenant->db_host;
            if (filled($tenant->db_port))     $overrides['port']     = (int) $tenant->db_port;
            if (filled($tenant->db_username)) $overrides['username'] = $tenant->db_username;
            if (filled($tenant->db_password)) {
                try {
                    $overrides['password'] = \Illuminate\Support\Facades\Crypt::decryptString($tenant->db_password);
                } catch (\Exception) {
                    $overrides['password'] = $tenant->db_password;
                }
            }
            foreach ($overrides as $k => $v) {
                config(["database.connections.tenant.{$k}" => $v]);
            }
            DB::purge('tenant');
            DB::connection('tenant')->getPdo();
            $this->line("  <info>✓</info> Base « {$dbName} » accessible.");
        } catch (\Exception $e) {
            $this->error("La base « {$dbName} » n'existe pas ou n'est pas accessible.");
            $this->line("  Vérifiez que la base existe sur votre hébergeur et que les identifiants sont corrects.");
            $this->line("  Puis relancez : <comment>php artisan tenant:init {$slug}</comment>");
            return self::FAILURE;
        }

        // ── 3. Confirmation ───────────────────────────────────────────────
        if (! $this->option('force') && ! $this->confirm("Initialiser « {$tenant->name} » (migrations + rôles + super-admin) ?", true)) {
            $this->line('Annulé.');
            return self::SUCCESS;
        }

        DB::setDefaultConnection('tenant');

        // ── 4. Migrations manquantes ──────────────────────────────────────
        $this->info('→ Exécution des migrations…');
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations',
            '--force'    => true,
        ]);
        $output = trim(Artisan::output());
        if ($output) {
            foreach (explode("\n", $output) as $line) {
                $this->line("  " . $line);
            }
        }
        $total = DB::table('migrations')->count();
        $this->line("  <info>✓</info> {$total} migrations en place.");

        // ── 5. Rôles et permissions ───────────────────────────────────────
        $this->info('→ Rôles et permissions…');
        Artisan::call('db:seed', [
            '--class' => 'RoleAndPermissionSeeder',
            '--force' => true,
        ]);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $roles = DB::table('roles')->count();
        $perms = DB::table('permissions')->count();
        $this->line("  <info>✓</info> {$roles} rôles, {$perms} permissions.");

        // ── 6. Super-admin ────────────────────────────────────────────────
        $this->info('→ Compte super-admin…');

        $existingAdmin = \App\Models\User::whereHas('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))->first();

        if ($existingAdmin && ! $this->option('force')) {
            $this->line("  <comment>!</comment> Un compte admin existe déjà : <info>{$existingAdmin->email}</info>");
            if (! $this->confirm('Réinitialiser son mot de passe ?', false)) {
                $this->line("  Ignoré.");
                $this->printSummary($tenant, $existingAdmin->email, '(inchangé)');
                return self::SUCCESS;
            }
            $adminUser = $existingAdmin;
        } else {
            $email = $this->option('email') ?: "admin@{$slug}.com";
            $uname = $this->option('name')  ?: ($tenant->contact_name ?? 'Administrateur');

            $adminUser = \App\Models\User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => $uname,
                    'email_verified_at' => now(),
                    'status'            => 'active',
                    'password'          => Hash::make(Str::random(32)),
                ]
            );
            $adminUser->update(['status' => 'active', 'email_verified_at' => now()]);
            $adminUser->syncRoles(['super_admin']);
        }

        $password = $this->option('password') ?: Str::password(12, symbols: false);
        $adminUser->update([
            'password'            => Hash::make($password),
            'password_changed_at' => null,
        ]);
        $this->line("  <info>✓</info> Super-admin prêt : <info>{$adminUser->email}</info>");

        // ── Restaurer connexion ───────────────────────────────────────────
        DB::setDefaultConnection('landlord');
        DB::purge('tenant');

        $this->printSummary($tenant, $adminUser->email, $password);
        return self::SUCCESS;
    }

    private function printSummary(Tenant $tenant, string $email, string $password): void
    {
        $this->line('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("  <info>✅  Tenant « {$tenant->name} » initialisé !</info>");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');
        $this->table(['Champ', 'Valeur'], [
            ['URL panel',          "http://{$tenant->domain}"],
            ['Email admin',        $email],
            ['Mot de passe',       $password],
            ['Base de données',    $tenant->database],
        ]);
        $this->line('  <comment>⚠  Transmettez ces identifiants de façon sécurisée.</comment>');
        $this->line('     Le mot de passe devra être changé à la première connexion.');
        $this->line('');
    }
}
