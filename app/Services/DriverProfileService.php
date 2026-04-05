<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service de synchronisation entre un compte utilisateur (rôle driver_user)
 * et un profil chauffeur dans la table `drivers`.
 *
 * Règles métier :
 *  - Quand un utilisateur reçoit le rôle `driver_user`, un profil chauffeur
 *    minimal est créé automatiquement dans `drivers` et lié via `users.driver_id`.
 *  - Si l'utilisateur a déjà un profil chauffeur lié, on ne fait rien.
 *  - Si un profil existe avec la même adresse e-mail (ex: chauffeur créé manuellement
 *    avant la création du compte), on lie les deux sans dupliquer.
 *  - Le changement de rôle vers autre chose ne supprime PAS le profil chauffeur
 *    (données historiques : affectations, infractions, inspections).
 */
class DriverProfileService
{
    /**
     * Crée ou lie un profil chauffeur pour cet utilisateur, si nécessaire.
     *
     * @param  array $extra  Champs supplémentaires saisis dans le formulaire utilisateur
     *                       (license_number, license_categories, license_expiry_date, etc.)
     */
    public function ensureDriverProfile(User $user, array $extra = []): ?Driver
    {
        // L'utilisateur a déjà un profil chauffeur lié → mettre à jour si des données sont fournies
        if ($user->driver_id !== null) {
            if (!empty($extra)) {
                $user->driver->update(array_filter($extra));
            }
            return $user->driver->fresh();
        }

        return DB::transaction(function () use ($user, $extra) {
            // Chercher un profil existant par e-mail (chauffeur créé manuellement avant le compte)
            $driver = Driver::where('email', $user->email)->first();

            if (!$driver) {
                // Construire le profil à partir des infos utilisateur + données extra du formulaire
                $driverData = array_merge([
                    'matricule'     => $this->generateMatricule($user),
                    'full_name'     => $user->name,
                    'email'         => $user->email,
                    'phone'         => $user->phone ?: null,
                    'hire_date'     => now()->toDateString(),
                    'contract_type' => 'permanent',
                    'status'        => 'active',
                    'created_by'    => auth()->id(),
                ], array_filter($extra)); // Les données extra écrasent les valeurs par défaut

                $driver = Driver::create($driverData);
            } elseif (!empty($extra)) {
                // Profil trouvé par email → compléter avec les données extra
                $driver->update(array_filter($extra));
            }

            // Lier le compte utilisateur au profil chauffeur
            $user->update(['driver_id' => $driver->id]);

            return $driver->fresh();
        });
    }

    /**
     * Génère un matricule unique au format CHF-YYYY-NNNN.
     * Incrémente jusqu'à trouver un matricule libre.
     */
    private function generateMatricule(User $user): string
    {
        $year    = now()->year;
        $baseNum = $user->id;

        // Essai n°0 : CHF-2026-007, puis CHF-2026-007-2, etc.
        $candidate = 'CHF-' . $year . '-' . str_pad($baseNum, 3, '0', STR_PAD_LEFT);

        $suffix = 1;
        while (Driver::where('matricule', $candidate)->exists()) {
            $suffix++;
            $candidate = 'CHF-' . $year . '-' . str_pad($baseNum, 3, '0', STR_PAD_LEFT) . '-' . $suffix;
        }

        return $candidate;
    }
}
