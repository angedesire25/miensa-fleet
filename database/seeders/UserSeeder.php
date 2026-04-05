<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crée les comptes utilisateurs de démonstration.
 *
 * Un compte par rôle pour couvrir tous les profils de l'application.
 * Tous les comptes ont le mot de passe : Password@123
 */
class UserSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'Password@123';

    public function run(): void
    {
        $users = [
            // ── Super Admin ──────────────────────────────────────────────────
            [
                'name'       => 'Moussa Coulibaly',
                'email'      => 'superadmin@miensafleet.ci',
                'phone'      => '+225 07 00 11 22 33',
                'department' => 'Direction',
                'job_title'  => 'Administrateur Système',
                'status'     => 'active',
                'role'       => 'super_admin',
            ],

            // ── Admin ────────────────────────────────────────────────────────
            [
                'name'       => 'Bintou Koné',
                'email'      => 'admin@miensafleet.ci',
                'phone'      => '+225 07 00 44 55 66',
                'department' => 'Direction',
                'job_title'  => 'Administratrice',
                'status'     => 'active',
                'role'       => 'admin',
            ],

            // ── Fleet Manager #1 ─────────────────────────────────────────────
            [
                'name'       => 'Kofi Asante',
                'email'      => 'kofi.asante@miensafleet.ci',
                'phone'      => '+225 07 10 20 30 40',
                'department' => 'Logistique',
                'job_title'  => 'Responsable de Flotte',
                'status'     => 'active',
                'role'       => 'fleet_manager',
            ],

            // ── Fleet Manager #2 ─────────────────────────────────────────────
            [
                'name'       => 'Amina Diallo',
                'email'      => 'amina.diallo@miensafleet.ci',
                'phone'      => '+225 07 50 60 70 80',
                'department' => 'Logistique',
                'job_title'  => 'Responsable de Flotte Adjointe',
                'status'     => 'active',
                'role'       => 'fleet_manager',
            ],

            // ── Contrôleur ───────────────────────────────────────────────────
            [
                'name'       => 'Djibril Traoré',
                'email'      => 'djibril.traore@miensafleet.ci',
                'phone'      => '+225 05 11 22 33 44',
                'department' => 'Logistique',
                'job_title'  => 'Contrôleur de Parc',
                'status'     => 'active',
                'role'       => 'controller',
            ],

            // ── Directeur ────────────────────────────────────────────────────
            [
                'name'       => 'Fatou Sidibé',
                'email'      => 'fatou.sidibe@miensafleet.ci',
                'phone'      => '+225 01 99 88 77 66',
                'department' => 'Direction Générale',
                'job_title'  => 'Directrice Générale',
                'status'     => 'active',
                'role'       => 'director',
            ],

            // ── Collaborateur ────────────────────────────────────────────────
            [
                'name'       => 'Jean-Baptiste Yao',
                'email'      => 'jb.yao@miensafleet.ci',
                'phone'      => '+225 07 33 44 55 66',
                'department' => 'Commercial',
                'job_title'  => 'Chargé de Mission',
                'status'     => 'active',
                'role'       => 'collaborator',
            ],

            // ── Driver User ──────────────────────────────────────────────────
            [
                'name'       => 'Sékou Ouattara',
                'email'      => 'sekou.ouattara@miensafleet.ci',
                'phone'      => '+225 05 77 88 99 00',
                'department' => 'Transport',
                'job_title'  => 'Chauffeur',
                'status'     => 'active',
                'role'       => 'driver_user',
            ],
        ];

        $adminId = null;

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password'            => Hash::make(self::DEFAULT_PASSWORD),
                    'email_verified_at'   => now(),
                    'password_changed_at' => now(),
                    'created_by'          => $adminId,
                ]),
            );

            $user->syncRoles([$role]);

            // L'admin (index 1) devient le created_by des comptes suivants
            if ($adminId === null) {
                $adminId = $user->id;
            }
        }
    }
}
