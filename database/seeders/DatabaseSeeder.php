<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Ordre d'exécution respectant les dépendances FK :
     *   1. RoleAndPermissionSeeder  → rôles + permissions (aucune dépendance)
     *   2. UserSeeder               → dépend des rôles
     *   3. GarageSeeder             → dépend des users (created_by)
     *   4. VehicleSeeder            → dépend des users (created_by)
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            GarageSeeder::class,
            VehicleSeeder::class,
        ]);
    }
}
