<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Accorde inspections.edit aux rôles driver_user et collaborator.
 *
 * Contexte : quand une fiche est renvoyée "À corriger" (status=rejected),
 * son auteur (chauffeur ou collaborateur) doit pouvoir la modifier et la
 * re-soumettre. Le contrôleur vérifie que l'utilisateur ne peut modifier
 * que SES PROPRES fiches (inspector_id = auth()->id()).
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $editPerm = Permission::firstOrCreate(
            ['name' => 'inspections.edit', 'guard_name' => 'web'],
            ['group' => 'inspections']
        );

        foreach (['driver_user', 'collaborator'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($editPerm);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $editPerm = Permission::where('name', 'inspections.edit')->first();
        if (!$editPerm) return;

        foreach (['driver_user', 'collaborator'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->revokePermissionTo($editPerm);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
