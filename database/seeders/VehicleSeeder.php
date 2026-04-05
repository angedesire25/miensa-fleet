<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Database\Seeder;

/**
 * Crée 10 véhicules représentatifs d'un parc automobile ivoirien,
 * puis ajoute une photo de profil fictive pour chacun.
 *
 * Les plaques suivent le format ivoirien courant : AB 1234 CI
 * Les chemins photos sont fictifs (pas de vrais fichiers sur disque) ;
 * ils suivent la convention : vehicles/{id}/vehicle_profile/photo_principale.jpg
 */
class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $createdBy = User::role('fleet_manager')->first()?->id
                  ?? User::first()?->id;

        $vehicles = [
            // ── 1. SUV direction ─────────────────────────────────────────────
            [
                'brand'                  => 'Toyota',
                'model'                  => 'Land Cruiser 200',
                'plate'                  => 'AB 1234 CI',
                'year'                   => 2019,
                'color'                  => 'Blanc',
                'vin'                    => 'JTMHX05J904012345',
                'fuel_type'              => 'diesel',
                'vehicle_type'           => 'suv',
                'license_category'       => 'B',
                'seats'                  => 7,
                'payload_kg'             => null,
                'km_current'             => 112_450,
                'km_next_service'        => 120_000,
                'km_last_oil_change'     => 108_000,
                'date_last_oil_change'   => '2025-10-15',
                'status'                 => 'available',
                'purchase_price'         => 38_500_000.00,
                'purchase_date'          => '2019-06-01',
                'insurance_company'      => 'SUNU Assurances CI',
                'insurance_policy_number'=> 'POL-2019-00412',
                'notes'                  => 'Véhicule de direction. Priorité d\'affectation cadres.',
            ],

            // ── 2. Pickup mission terrain ────────────────────────────────────
            [
                'brand'                  => 'Toyota',
                'model'                  => 'Hilux 2.8 GD6',
                'plate'                  => 'CD 5678 CI',
                'year'                   => 2021,
                'color'                  => 'Blanc',
                'vin'                    => 'MR0FX29G802056789',
                'fuel_type'              => 'diesel',
                'vehicle_type'           => 'pickup',
                'license_category'       => 'B',
                'seats'                  => 5,
                'payload_kg'             => 1_000,
                'km_current'             => 67_200,
                'km_next_service'        => 70_000,
                'km_last_oil_change'     => 62_000,
                'date_last_oil_change'   => '2025-12-20',
                'status'                 => 'available',
                'purchase_price'         => 28_900_000.00,
                'purchase_date'          => '2021-03-15',
                'insurance_company'      => 'SUNU Assurances CI',
                'insurance_policy_number'=> 'POL-2021-00891',
                'notes'                  => 'Missions terrain et régions reculées.',
            ],

            // ── 3. Minibus transport équipe ──────────────────────────────────
            [
                'brand'                  => 'Toyota',
                'model'                  => 'HiAce GL',
                'plate'                  => 'EF 9012 CI',
                'year'                   => 2018,
                'color'                  => 'Blanc',
                'vin'                    => 'JTFSS22P002089012',
                'fuel_type'              => 'diesel',
                'vehicle_type'           => 'van',
                'license_category'       => 'B',
                'seats'                  => 15,
                'payload_kg'             => null,
                'km_current'             => 178_600,
                'km_next_service'        => 180_000,
                'km_last_oil_change'     => 173_000,
                'date_last_oil_change'   => '2025-09-05',
                'status'                 => 'available',
                'purchase_price'         => 21_500_000.00,
                'purchase_date'          => '2018-01-10',
                'insurance_company'      => 'Allianz Côte d\'Ivoire',
                'insurance_policy_number'=> 'POL-2018-00120',
                'notes'                  => 'Transport collectif équipes. Révision à prévoir bientôt.',
            ],

            // ── 4. Berline liaison courte distance ───────────────────────────
            [
                'brand'                  => 'Toyota',
                'model'                  => 'Corolla 1.8',
                'plate'                  => 'GH 3456 CI',
                'year'                   => 2020,
                'color'                  => 'Gris Métallisé',
                'vin'                    => 'SB1ZF3BE50E234567',
                'fuel_type'              => 'gasoline',
                'vehicle_type'           => 'sedan',
                'license_category'       => 'B',
                'seats'                  => 5,
                'payload_kg'             => null,
                'km_current'             => 54_800,
                'km_next_service'        => 60_000,
                'km_last_oil_change'     => 50_000,
                'date_last_oil_change'   => '2025-11-30',
                'status'                 => 'available',
                'purchase_price'         => 16_200_000.00,
                'purchase_date'          => '2020-07-22',
                'insurance_company'      => 'SUNU Assurances CI',
                'insurance_policy_number'=> 'POL-2020-00554',
                'notes'                  => 'Liaisons courtes Abidjan et banlieue.',
            ],

            // ── 5. SUV Prado ─────────────────────────────────────────────────
            [
                'brand'                  => 'Toyota',
                'model'                  => 'Prado 150',
                'plate'                  => 'IJ 7890 CI',
                'year'                   => 2020,
                'color'                  => 'Argent',
                'vin'                    => 'JTEBX9FJ102078901',
                'fuel_type'              => 'diesel',
                'vehicle_type'           => 'suv',
                'license_category'       => 'B',
                'seats'                  => 7,
                'payload_kg'             => null,
                'km_current'             => 88_300,
                'km_next_service'        => 90_000,
                'km_last_oil_change'     => 83_000,
                'date_last_oil_change'   => '2025-11-10',
                'status'                 => 'available',
                'purchase_price'         => 34_000_000.00,
                'purchase_date'          => '2020-02-14',
                'insurance_company'      => 'SUNU Assurances CI',
                'insurance_policy_number'=> 'POL-2020-00212',
                'notes'                  => 'Véhicule polyvalent direction et terrain.',
            ],

            // ── 6. Pickup double cabine ──────────────────────────────────────
            [
                'brand'                  => 'Mitsubishi',
                'model'                  => 'L200 2.4D',
                'plate'                  => 'KL 2345 CI',
                'year'                   => 2022,
                'color'                  => 'Noir',
                'vin'                    => 'MMBJNKB40NH234561',
                'fuel_type'              => 'diesel',
                'vehicle_type'           => 'pickup',
                'license_category'       => 'B',
                'seats'                  => 5,
                'payload_kg'             => 1_050,
                'km_current'             => 45_100,
                'km_next_service'        => 50_000,
                'km_last_oil_change'     => 40_000,
                'date_last_oil_change'   => '2026-01-08',
                'status'                 => 'available',
                'purchase_price'         => 27_500_000.00,
                'purchase_date'          => '2022-09-01',
                'insurance_company'      => 'NSIA Assurances',
                'insurance_policy_number'=> 'POL-2022-01102',
                'notes'                  => 'Missions Bouaké et zones Centre.',
            ],

            // ── 7. Van cargo livraisons ──────────────────────────────────────
            [
                'brand'                  => 'Hyundai',
                'model'                  => 'H350',
                'plate'                  => 'MN 6789 CI',
                'year'                   => 2019,
                'color'                  => 'Blanc',
                'vin'                    => 'KMHWN81KAKJ067891',
                'fuel_type'              => 'diesel',
                'vehicle_type'           => 'van',
                'license_category'       => 'B',
                'seats'                  => 3,
                'payload_kg'             => 1_200,
                'km_current'             => 142_700,
                'km_next_service'        => 145_000,
                'km_last_oil_change'     => 138_000,
                'date_last_oil_change'   => '2025-10-22',
                'status'                 => 'available',
                'purchase_price'         => 19_800_000.00,
                'purchase_date'          => '2019-11-05',
                'insurance_company'      => 'Allianz Côte d\'Ivoire',
                'insurance_policy_number'=> 'POL-2019-01567',
                'notes'                  => 'Livraisons et transport de matériel.',
            ],

            // ── 8. Pickup Ford ───────────────────────────────────────────────
            [
                'brand'                  => 'Ford',
                'model'                  => 'Ranger XLT 3.2',
                'plate'                  => 'OP 0123 CI',
                'year'                   => 2021,
                'color'                  => 'Bleu Nuit',
                'vin'                    => 'WF0XXXTTGXKB01234',
                'fuel_type'              => 'diesel',
                'vehicle_type'           => 'pickup',
                'license_category'       => 'B',
                'seats'                  => 5,
                'payload_kg'             => 930,
                'km_current'             => 73_500,
                'km_next_service'        => 80_000,
                'km_last_oil_change'     => 68_000,
                'date_last_oil_change'   => '2025-12-01',
                'status'                 => 'available',
                'purchase_price'         => 26_000_000.00,
                'purchase_date'          => '2021-06-30',
                'insurance_company'      => 'NSIA Assurances',
                'insurance_policy_number'=> 'POL-2021-01234',
                'notes'                  => 'Affecté équipe technique mobile.',
            ],

            // ── 9. SUV polyvalent ────────────────────────────────────────────
            [
                'brand'                  => 'Toyota',
                'model'                  => 'RAV4 2.0',
                'plate'                  => 'QR 4567 CI',
                'year'                   => 2019,
                'color'                  => 'Blanc Nacré',
                'vin'                    => 'JTMRFREV4KD456789',
                'fuel_type'              => 'gasoline',
                'vehicle_type'           => 'suv',
                'license_category'       => 'B',
                'seats'                  => 5,
                'payload_kg'             => null,
                'km_current'             => 98_200,
                'km_next_service'        => 100_000,
                'km_last_oil_change'     => 93_000,
                'date_last_oil_change'   => '2025-10-05',
                'status'                 => 'available',
                'purchase_price'         => 22_500_000.00,
                'purchase_date'          => '2019-08-18',
                'insurance_company'      => 'SUNU Assurances CI',
                'insurance_policy_number'=> 'POL-2019-00876',
                'notes'                  => null,
            ],

            // ── 10. Utilitaire Renault ───────────────────────────────────────
            [
                'brand'                  => 'Renault',
                'model'                  => 'Kangoo Express 1.5 dCi',
                'plate'                  => 'ST 8901 CI',
                'year'                   => 2018,
                'color'                  => 'Gris Clair',
                'vin'                    => 'VF1FW17B550890123',
                'fuel_type'              => 'diesel',
                'vehicle_type'           => 'van',
                'license_category'       => 'B',
                'seats'                  => 2,
                'payload_kg'             => 650,
                'km_current'             => 164_300,
                'km_next_service'        => 165_000,
                'km_last_oil_change'     => 160_000,
                'date_last_oil_change'   => '2025-09-15',
                'status'                 => 'available',
                'purchase_price'         => 12_400_000.00,
                'purchase_date'          => '2018-04-25',
                'insurance_company'      => 'Allianz Côte d\'Ivoire',
                'insurance_policy_number'=> 'POL-2018-00312',
                'notes'                  => 'Coursier et petits transports internes. À surveiller (km élevé).',
            ],
        ];

        foreach ($vehicles as $data) {
            $vehicle = Vehicle::firstOrCreate(
                ['plate' => $data['plate']],
                array_merge($data, ['created_by' => $createdBy]),
            );

            // ── Photo de profil fictive ────────────────────────────────────────
            // Pas de fichier réel sur disque : enregistrement BDD uniquement.
            // Le chemin suit la convention du PhotoService.
            $photoPath = "vehicles/{$vehicle->id}/vehicle_profile/photo_principale.jpg";

            VehiclePhoto::firstOrCreate(
                [
                    'vehicle_id' => $vehicle->id,
                    'context'    => 'vehicle_profile',
                ],
                [
                    'photoable_type' => null,
                    'photoable_id'   => null,
                    'file_path'      => $photoPath,
                    'original_name'  => 'photo_principale.jpg',
                    'mime_type'      => 'image/jpeg',
                    'size_kb'        => 210,
                    'caption'        => "{$vehicle->brand} {$vehicle->model} — {$vehicle->plate}",
                    'taken_at'       => $vehicle->purchase_date ?? now(),
                    'uploaded_by'    => $createdBy,
                ],
            );
        }
    }
}
