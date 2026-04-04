<?php

namespace App\Observers;

use App\Models\Infraction;
use Illuminate\Support\Facades\DB;

class InfractionObserver
{
    /**
     * À la création d'une infraction :
     *
     *   1. Identification automatique du conducteur (règle métier #6)
     *      Si ni driver_id ni user_id ne sont renseignés, on cherche
     *      l'affectation ou la demande de véhicule active au moment
     *      de l'infraction. Si trouvé, on persiste silencieusement.
     *
     *   2. Incrémentation de drivers.total_infractions
     *      Dès qu'un driver_id est connu (fourni à la création ou
     *      découvert par identifyDriverAuto()), le compteur est mis à jour.
     */
    public function created(Infraction $infraction): void
    {
        // ── Étape 1 : identification automatique ────────────────────────────
        if ($infraction->driver_id === null && $infraction->user_id === null) {
            $identified = $infraction->identifyDriverAuto();

            if ($identified) {
                // Sauvegarde silencieuse : n'émet pas d'événements Eloquent,
                // évite une récursion sur cet observer.
                $infraction->saveQuietly();
            }
        }

        // ── Étape 2 : incrément total_infractions ───────────────────────────
        // Relire driver_id après l'éventuelle identification automatique.
        if ($infraction->driver_id !== null) {
            DB::table('drivers')
                ->where('id', $infraction->driver_id)
                ->update([
                    'total_infractions' => DB::raw('total_infractions + 1'),
                ]);
        }
    }
}
