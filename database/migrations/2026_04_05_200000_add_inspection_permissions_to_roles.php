<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Migration de rattrapage : droits sur les fiches de contrôle.
 *
 * Problème : le seeder d'origine ne donnait pas les droits
 * inspections.view / inspections.create aux rôles collaborator
 * et driver_user, et la permission inspections.validate n'existait pas.
 *
 * Résultat attendu après migration :
 *   - collaborator  → inspections.view + inspections.create
 *   - driver_user   → inspections.view + inspections.create
 *   - controller    → + inspections.validate
 *   - fleet_manager → + inspections.validate (déjà tous les droits sauf delete)
 *   - admin / super_admin → déjà tout
 */
return new class extends Migration
{
    public function up(): void
    {
        // Vide le cache Spatie avant toute manipulation
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ── Créer la permission de validation si elle n'existe pas encore ──
        $validatePerm = Permission::firstOrCreate(
            ['name' => 'inspections.validate', 'guard_name' => 'web'],
            ['group' => 'inspections']
        );

        // ── Permissions de base : vue + création ──
        $viewPerm   = Permission::firstOrCreate(['name' => 'inspections.view',   'guard_name' => 'web'], ['group' => 'inspections']);
        $createPerm = Permission::firstOrCreate(['name' => 'inspections.create', 'guard_name' => 'web'], ['group' => 'inspections']);
        $editPerm   = Permission::firstOrCreate(['name' => 'inspections.edit',   'guard_name' => 'web'], ['group' => 'inspections']);

        // ── collaborator : peut voir ET soumettre ses propres fiches ──
        $collaborator = Role::where('name', 'collaborator')->first();
        if ($collaborator) {
            $collaborator->givePermissionTo([$viewPerm, $createPerm]);
        }

        // ── driver_user : peut voir ET soumettre ses propres fiches ──
        $driverUser = Role::where('name', 'driver_user')->first();
        if ($driverUser) {
            $driverUser->givePermissionTo([$viewPerm, $createPerm]);
        }

        // ── controller : ajouter le droit de validation ──
        $controller = Role::where('name', 'controller')->first();
        if ($controller) {
            $controller->givePermissionTo($validatePerm);
        }

        // ── fleet_manager : ajouter le droit de validation ──
        $fleetManager = Role::where('name', 'fleet_manager')->first();
        if ($fleetManager) {
            $fleetManager->givePermissionTo($validatePerm);
        }

        // ── admin et super_admin : s'assurer qu'ils ont tout ──
        foreach (['admin', 'super_admin'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo([$viewPerm, $createPerm, $editPerm, $validatePerm]);
            }
        }

        // Vide le cache après modifications
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Retirer les droits accordés aux rôles concernés
        foreach (['collaborator', 'driver_user'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->revokePermissionTo(['inspections.view', 'inspections.create']);
            }
        }

        // Supprimer la permission de validation
        Permission::where('name', 'inspections.validate')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
