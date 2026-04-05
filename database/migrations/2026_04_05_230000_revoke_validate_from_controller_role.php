<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Correction des droits de validation des fiches de contrôle.
 *
 * Le rôle `controller` peut remplir et modifier les fiches mais
 * pas les valider. Seuls les rôles fleet_manager, admin et super_admin
 * ont le droit de valider / rejeter une fiche.
 *
 * Hiérarchie résultante :
 *   controller    → inspections.view + create + edit   (terrain)
 *   fleet_manager → + inspections.validate             (validation)
 *   admin         → toutes les permissions
 *   super_admin   → toutes les permissions
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $validatePerm = Permission::where('name', 'inspections.validate')->first();

        if ($validatePerm) {
            // Retirer le droit de validation au contrôleur
            $controller = Role::where('name', 'controller')->first();
            if ($controller) {
                $controller->revokePermissionTo($validatePerm);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $validatePerm = Permission::where('name', 'inspections.validate')->first();
        if (!$validatePerm) return;

        $controller = Role::where('name', 'controller')->first();
        if ($controller) {
            $controller->givePermissionTo($validatePerm);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
