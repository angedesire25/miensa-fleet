<?php

namespace App\Multitenancy;

use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * Commute la connexion 'tenant' vers la base de données du tenant courant.
 * Appelée automatiquement par le package à chaque résolution de tenant.
 */
class SwitchTenantDatabaseTask implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        $this->setTenantDatabase($tenant->database);
    }

    public function forgetCurrent(): void
    {
        $this->setTenantDatabase(null);
    }

    private function setTenantDatabase(?string $database): void
    {
        // Mettre à jour dynamiquement la config de la connexion 'tenant'
        config(['database.connections.tenant.database' => $database]);

        // Purger la connexion pour forcer la reconnexion avec la nouvelle BDD
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Définir 'tenant' comme connexion par défaut pour toutes les requêtes
        DB::setDefaultConnection('tenant');
    }
}
