<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crée les rôles et permissions de l'application Miensa Fleet.
 *
 * Hiérarchie (level = priorité, 1 = plus élevée) :
 *   super_admin  (1) → Accès absolu, gestion technique
 *   admin        (2) → Gestion complète de la flotte et des utilisateurs
 *   fleet_manager(3) → Opérations quotidiennes (affectations, demandes, incidents)
 *   controller   (4) → Terrain : fiches de contrôle, km, infractions
 *   director     (5) → Lecture seule + rapports
 *   collaborator (6) → Demandes de véhicule uniquement
 *   driver_user  (7) → Portail chauffeur : ses propres données
 */
class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Vide le cache Spatie avant de créer les permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ── Définition des permissions par groupe ──────────────────────────
        $permissions = [
            // Véhicules
            'vehicles' => [
                'vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete',
            ],
            // Chauffeurs
            'drivers' => [
                'drivers.view', 'drivers.create', 'drivers.edit', 'drivers.delete',
            ],
            // Affectations
            'assignments' => [
                'assignments.view', 'assignments.create', 'assignments.edit',
                'assignments.delete', 'assignments.validate',
            ],
            // Demandes de véhicule
            'vehicle_requests' => [
                'vehicle_requests.view', 'vehicle_requests.create',
                'vehicle_requests.edit', 'vehicle_requests.approve',
            ],
            // Fiches de contrôle
            'inspections' => [
                'inspections.view', 'inspections.create', 'inspections.edit',
                'inspections.validate', // Approbation / rejet d'une fiche (gestionnaire+)
            ],
            // Documents administratifs
            'documents' => [
                'documents.view', 'documents.create', 'documents.edit', 'documents.delete',
            ],
            // Infractions
            'infractions' => [
                'infractions.view', 'infractions.create', 'infractions.edit', 'infractions.impute',
            ],
            // Alertes
            'alerts' => [
                'alerts.view', 'alerts.manage',
            ],
            // Rapports
            'reports' => [
                'reports.view', 'reports.export',
            ],
            // Garages
            'garages' => [
                'garages.view', 'garages.create', 'garages.edit', 'garages.delete',
            ],
            // Sinistres
            'incidents' => [
                'incidents.view', 'incidents.create', 'incidents.edit',
            ],
            // Réparations
            'repairs' => [
                'repairs.view', 'repairs.create', 'repairs.edit', 'repairs.delete',
            ],
            // Pièces
            'parts' => [
                'parts.view', 'parts.create', 'parts.edit',
            ],
            // Utilisateurs
            'users' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
            ],
            // Nettoyage des véhicules
            'cleanings' => [
                'cleanings.view', 'cleanings.create', 'cleanings.edit',
                'cleanings.delete', 'cleanings.confirm',
            ],
        ];

        foreach ($permissions as $group => $names) {
            foreach ($names as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'], [
                    'group' => $group,
                ]);
            }
        }

        $all = Permission::all();

        // ── Rôles ──────────────────────────────────────────────────────────
        $roleDefinitions = [
            [
                'name'        => 'super_admin',
                'level'       => 1,
                'description' => 'Accès technique absolu',
                'color'       => '#B91C1C',
                'permissions' => $all,
            ],
            [
                'name'        => 'admin',
                'level'       => 2,
                'description' => 'Gestion complète de la flotte et des utilisateurs',
                'color'       => '#1D4ED8',
                'permissions' => $all,
            ],
            [
                'name'        => 'fleet_manager',
                'level'       => 3,
                'description' => 'Opérations quotidiennes : affectations, demandes, incidents',
                'color'       => '#047857',
                'permissions' => Permission::whereNotIn('name', [
                    'users.delete',
                ])->get(),
            ],
            [
                'name'        => 'controller',
                'level'       => 4,
                'description' => 'Terrain : fiches de contrôle, km, infractions',
                'color'       => '#D97706',
                'permissions' => Permission::whereIn('name', [
                    'vehicles.view',
                    'drivers.view',
                    'assignments.view', 'assignments.create', 'assignments.edit',
                    'vehicle_requests.view',
                    // Le contrôleur remplit et corrige les fiches, mais ne les valide PAS
                    // (validation réservée au fleet_manager et aux admins)
                    'inspections.view', 'inspections.create', 'inspections.edit',
                    'documents.view',
                    'infractions.view', 'infractions.create', 'infractions.edit',
                    'alerts.view',
                    'garages.view',
                    'incidents.view',
                    'repairs.view', 'repairs.create', 'repairs.edit',
                    'parts.view', 'parts.create',
                    'cleanings.view', 'cleanings.create', 'cleanings.edit', 'cleanings.confirm',
                ])->get(),
            ],
            [
                'name'        => 'director',
                'level'       => 5,
                'description' => 'Lecture seule + accès aux rapports',
                'color'       => '#7C3AED',
                'permissions' => Permission::where('name', 'like', '%.view')
                    ->orWhereIn('name', ['reports.export'])
                    ->get(),
            ],
            [
                'name'        => 'collaborator',
                'level'       => 6,
                'description' => 'Demandes de véhicule et fiches de contrôle',
                'color'       => '#0891B2',
                'permissions' => Permission::whereIn('name', [
                    'vehicles.view',
                    'vehicle_requests.view', 'vehicle_requests.create',
                    'inspections.view', 'inspections.create', 'inspections.edit',
                    'cleanings.view', 'cleanings.confirm',
                ])->get(),
            ],
            [
                'name'        => 'driver_user',
                'level'       => 7,
                'description' => 'Portail chauffeur : ses données + fiches de contrôle',
                'color'       => '#64748B',
                'permissions' => Permission::whereIn('name', [
                    'vehicles.view',
                    'vehicle_requests.view', 'vehicle_requests.create',
                    'assignments.view',
                    'inspections.view', 'inspections.create', 'inspections.edit',
                    'cleanings.view', 'cleanings.confirm',
                ])->get(),
            ],
        ];

        foreach ($roleDefinitions as $def) {
            $role = Role::firstOrCreate(
                ['name' => $def['name'], 'guard_name' => 'web'],
                [
                    'level'       => $def['level'],
                    'description' => $def['description'],
                    'color'       => $def['color'],
                ],
            );

            $role->syncPermissions($def['permissions']);
        }
    }
}
