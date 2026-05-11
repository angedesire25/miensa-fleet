<?php

namespace App\Multitenancy;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * Commute la connexion 'tenant' vers la base de données du tenant courant.
 *
 * Chaque tenant peut avoir ses propres credentials (host, port, username, password).
 * Si un champ est absent, on retombe sur les valeurs TENANT_DB_* de l'environnement.
 */
class SwitchTenantDatabaseTask implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        $overrides = ['database' => $tenant->database];

        if (filled($tenant->db_host)) {
            $overrides['host'] = $tenant->db_host;
        }
        if (filled($tenant->db_port)) {
            $overrides['port'] = (int) $tenant->db_port;
        }
        if (filled($tenant->db_username)) {
            $overrides['username'] = $tenant->db_username;
        }
        if (filled($tenant->db_password)) {
            try {
                $overrides['password'] = Crypt::decryptString($tenant->db_password);
            } catch (\Exception) {
                // Valeur non chiffrée (migration legacy) — utiliser telle quelle
                $overrides['password'] = $tenant->db_password;
            }
        }

        foreach ($overrides as $key => $value) {
            config(["database.connections.tenant.{$key}" => $value]);
        }

        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');
    }

    public function forgetCurrent(): void
    {
        // Remettre les valeurs par défaut de l'env
        config([
            'database.connections.tenant.database' => null,
            'database.connections.tenant.host'     => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'database.connections.tenant.port'     => env('TENANT_DB_PORT', env('DB_PORT', '3306')),
            'database.connections.tenant.username' => env('TENANT_DB_USERNAME', env('DB_USERNAME', 'root')),
            'database.connections.tenant.password' => env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
        ]);

        DB::purge('tenant');
    }
}
