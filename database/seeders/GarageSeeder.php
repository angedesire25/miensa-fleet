<?php

namespace Database\Seeders;

use App\Models\Garage;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Crée 5 garages partenaires en contexte ivoirien.
 *
 * Couvre les 4 types métier : dealer, independent, official_service, roadside.
 * Répartis sur 3 villes : Abidjan, Bouaké, Yamoussoukro.
 */
class GarageSeeder extends Seeder
{
    public function run(): void
    {
        // Le fleet_manager crée les garages (premier compte avec ce rôle)
        $createdBy = User::role('fleet_manager')->first()?->id
                  ?? User::first()?->id;

        $garages = [
            // ── 1. Dealer officiel Abidjan ───────────────────────────────────
            [
                'name'            => 'Garage Central Abidjan',
                'type'            => 'dealer',
                'address'         => 'Zone Industrielle de Yopougon, Lot 42',
                'city'            => 'Abidjan',
                'phone'           => '+225 27 23 45 67 89',
                'email'           => 'contact@garagecentral-abidjan.ci',
                'contact_person'  => 'Léon Akré',
                'specializations' => ['engine', 'electrical', 'body'],
                'rating'          => 4,
                'is_approved'     => true,
                'notes'           => 'Concessionnaire agréé multi-marques. Disponible 6j/7.',
            ],

            // ── 2. Indépendant carrosserie / pneus Abidjan ───────────────────
            [
                'name'            => 'Auto Services Plateau',
                'type'            => 'independent',
                'address'         => 'Rue du Commerce, Plateau',
                'city'            => 'Abidjan',
                'phone'           => '+225 07 58 12 34 56',
                'email'           => 'autoservices.plateau@gmail.com',
                'contact_person'  => 'Honoré Kouamé',
                'specializations' => ['body', 'tires'],
                'rating'          => 3,
                'is_approved'     => true,
                'notes'           => 'Spécialisé carrosserie et pneumatiques. Bon rapport qualité/prix.',
            ],

            // ── 3. Service officiel Toyota ───────────────────────────────────
            [
                'name'            => 'Toyota CI Service Officiel',
                'type'            => 'official_service',
                'address'         => 'Boulevard de Marseille, Marcory',
                'city'            => 'Abidjan',
                'phone'           => '+225 27 22 40 00 00',
                'email'           => 'service@toyota-ci.com',
                'contact_person'  => 'Marc-Antoine Gnane',
                'specializations' => ['engine', 'electrical', 'warranty'],
                'rating'          => 5,
                'is_approved'     => true,
                'notes'           => 'Centre agréé Toyota. Gestion garantie constructeur. Prise de RDV obligatoire.',
            ],

            // ── 4. Indépendant moteur / pneus Bouaké ────────────────────────
            [
                'name'            => 'Méca Express Bouaké',
                'type'            => 'independent',
                'address'         => 'Quartier Commerce, Avenue Houphouët-Boigny',
                'city'            => 'Bouaké',
                'phone'           => '+225 07 99 45 12 78',
                'email'           => 'mecaexpress.bouake@gmail.com',
                'contact_person'  => 'Ibrahima Coulibaly',
                'specializations' => ['engine', 'tires'],
                'rating'          => 3,
                'is_approved'     => true,
                'notes'           => 'Prestataire de proximité pour les missions Centre-Nord.',
            ],

            // ── 5. Dépannage rapide Yamoussoukro ────────────────────────────
            [
                'name'            => 'Dépannage Rapide Yamoussoukro',
                'type'            => 'roadside',
                'address'         => 'Carrefour Habitat, Route de Toumodi',
                'city'            => 'Yamoussoukro',
                'phone'           => '+225 05 44 33 22 11',
                'email'           => null,
                'contact_person'  => 'Noël Bamba',
                'specializations' => ['tires', 'electrical'],
                'rating'          => 2,
                'is_approved'     => true,
                'notes'           => 'Intervention en bord de route uniquement. Disponible 24h/24.',
            ],
        ];

        foreach ($garages as $data) {
            Garage::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['created_by' => $createdBy]),
            );
        }
    }
}
