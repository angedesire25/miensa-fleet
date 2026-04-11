<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeder landlord — à exécuter sur la connexion 'landlord'.
 * php artisan db:seed --class=PlanSeeder --database=landlord
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Gratuit',
                'slug'          => 'free',
                'description'   => 'Démarrez gratuitement, sans engagement.',
                'price_monthly' => 0,
                'price_yearly'  => 0,
                'max_vehicles'  => 3,
                'max_users'     => 2,
                'max_drivers'   => 5,
                'has_repairs'       => false,
                'has_infractions'   => false,
                'has_incidents'     => false,
                'has_inspections'   => true,
                'has_reports'       => false,
                'has_api'           => false,
                'trial_days'    => 0,
                'sort_order'    => 1,
                'is_active'     => true,
                'is_featured'   => false,
            ],
            [
                'name'          => 'Essentiel',
                'slug'          => 'essential',
                'description'   => 'Pour les PME qui veulent gérer leur flotte efficacement.',
                'price_monthly' => 25000,   // 25 000 FCFA / mois
                'price_yearly'  => 250000,  // 250 000 FCFA / an (≈ 2 mois offerts)
                'max_vehicles'  => 15,
                'max_users'     => 5,
                'max_drivers'   => 20,
                'has_repairs'       => true,
                'has_infractions'   => true,
                'has_incidents'     => true,
                'has_inspections'   => true,
                'has_reports'       => false,
                'has_api'           => false,
                'trial_days'    => 14,
                'sort_order'    => 2,
                'is_active'     => true,
                'is_featured'   => false,
            ],
            [
                'name'          => 'Pro',
                'slug'          => 'pro',
                'description'   => 'Toutes les fonctionnalités + rapports avancés et API.',
                'price_monthly' => 60000,   // 60 000 FCFA / mois
                'price_yearly'  => 600000,  // 600 000 FCFA / an
                'max_vehicles'  => 999,
                'max_users'     => 999,
                'max_drivers'   => 999,
                'has_repairs'       => true,
                'has_infractions'   => true,
                'has_incidents'     => true,
                'has_inspections'   => true,
                'has_reports'       => true,
                'has_api'           => true,
                'trial_days'    => 14,
                'sort_order'    => 3,
                'is_active'     => true,
                'is_featured'   => true,
            ],
        ];

        foreach ($plans as $data) {
            Plan::updateOrCreate(['slug' => $data['slug']], $data);
        }

        $this->command->info('✓ 3 plans créés : Gratuit, Essentiel, Pro');
    }
}
