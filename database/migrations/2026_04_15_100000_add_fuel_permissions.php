<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Ajoute le groupe de permissions "fuel" pour le module carburant.
 *
 * Permissions créées :
 *   fuel.view           — voir les demandes et transactions
 *   fuel.request        — soumettre une demande de carburant
 *   fuel.approve        — approuver / rejeter une demande
 *   fuel.record         — enregistrer une transaction (ravitaillement)
 *   fuel.manage_stations— créer / modifier / supprimer des stations
 *   fuel.export         — exporter les données carburant
 *
 * Attribution par rôle :
 *   super_admin / admin  → toutes
 *   fleet_manager        → toutes sauf manage_stations (droit admin)
 *   controller           → view + record
 *   director             → view + export
 *   collaborator         → view + request
 *   driver_user          → view + request
 */
return new class extends Migration
{
    /** Les permissions du module carburant. */
    private array $fuelPermissions = [
        'fuel.view',
        'fuel.request',
        'fuel.approve',
        'fuel.record',
        'fuel.manage_stations',
        'fuel.export',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Création des permissions
        foreach ($this->fuelPermissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['group' => 'fuel'],
            );
        }

        // Attribution aux rôles existants
        $this->syncRoles();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Retirer les permissions des rôles avant suppression
        foreach (Role::all() as $role) {
            $role->revokePermissionTo($this->fuelPermissions);
        }

        Permission::whereIn('name', $this->fuelPermissions)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // ── Privé ──────────────────────────────────────────────────────────────

    private function syncRoles(): void
    {
        $map = [
            'super_admin'  => ['fuel.view', 'fuel.request', 'fuel.approve', 'fuel.record', 'fuel.manage_stations', 'fuel.export'],
            'admin'        => ['fuel.view', 'fuel.request', 'fuel.approve', 'fuel.record', 'fuel.manage_stations', 'fuel.export'],
            'fleet_manager'=> ['fuel.view', 'fuel.request', 'fuel.approve', 'fuel.record', 'fuel.export'],
            'controller'   => ['fuel.view', 'fuel.record'],
            'director'     => ['fuel.view', 'fuel.export'],
            'collaborator' => ['fuel.view', 'fuel.request'],
            'driver_user'  => ['fuel.view', 'fuel.request'],
        ];

        foreach ($map as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }
    }
};
