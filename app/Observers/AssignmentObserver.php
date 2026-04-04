<?php

namespace App\Observers;

use App\Models\Assignment;
use Illuminate\Support\Facades\DB;

class AssignmentObserver
{
    /**
     * Cas rare : affectation créée directement en in_progress (import, seeder…).
     * On synchronise immédiatement le véhicule et le chauffeur.
     */
    public function created(Assignment $assignment): void
    {
        if ($assignment->status === 'in_progress') {
            $this->syncVehicleStarted($assignment);
        }
    }

    /**
     * Point central : chaque changement de statut déclenche des effets de bord
     * sur le véhicule (status, current_driver_id) et les statistiques chauffeur.
     */
    public function updated(Assignment $assignment): void
    {
        if (! $assignment->wasChanged('status')) {
            return;
        }

        $oldStatus = $assignment->getOriginal('status');
        $newStatus = $assignment->status;

        match ($newStatus) {
            'in_progress' => $this->syncVehicleStarted($assignment),
            'completed'   => $this->syncVehicleCompleted($assignment),
            'cancelled'   => $this->syncVehicleCancelled($assignment, $oldStatus),
            default       => null,
        };
    }

    // ── Méthodes privées ───────────────────────────────────────────────────

    /**
     * Statut → in_progress.
     * Le véhicule est parti : on le marque "en mission" et on enregistre
     * le chauffeur comme conducteur courant (dénormalisation).
     */
    private function syncVehicleStarted(Assignment $assignment): void
    {
        DB::table('vehicles')
            ->where('id', $assignment->vehicle_id)
            ->update([
                'status'            => 'on_mission',
                'current_driver_id' => $assignment->driver_id,
            ]);
    }

    /**
     * Statut → completed.
     *
     * 1. Remet le véhicule en disponible s'il n'y a pas d'autre affectation
     *    active en cours pour ce même véhicule (sécurité anti-désync).
     * 2. Incrémente les statistiques dénormalisées du chauffeur :
     *    - total_km          += km_end - km_start
     *    - total_assignments += 1
     *
     * Utilise DB::table() pour éviter de déclencher d'autres événements Eloquent.
     */
    private function syncVehicleCompleted(Assignment $assignment): void
    {
        $hasOtherInProgress = Assignment::where('vehicle_id', $assignment->vehicle_id)
            ->where('id', '!=', $assignment->id)
            ->where('status', 'in_progress')
            ->exists();

        if (! $hasOtherInProgress) {
            DB::table('vehicles')
                ->where('id', $assignment->vehicle_id)
                ->update([
                    'status'            => 'available',
                    'current_driver_id' => null,
                ]);
        }

        // Calcul PHP du km_total (km_total est une colonne virtuelle MySQL,
        // non disponible via $assignment->km_total sans rechargement).
        $kmDone = ($assignment->km_end !== null && $assignment->km_start !== null)
            ? max(0, $assignment->km_end - $assignment->km_start)
            : 0;

        DB::table('drivers')
            ->where('id', $assignment->driver_id)
            ->update([
                'total_km'          => DB::raw("total_km + {$kmDone}"),
                'total_assignments' => DB::raw('total_assignments + 1'),
            ]);
    }

    /**
     * Statut → cancelled.
     *
     * Si l'affectation était in_progress au moment de l'annulation,
     * le véhicule était physiquement sorti. On le remet disponible
     * et on libère le chauffeur courant, sauf si une autre affectation
     * est toujours en cours sur ce véhicule.
     *
     * Les affectations simplement planifiées ou confirmées n'ont pas
     * encore impacté le statut du véhicule : pas d'action nécessaire.
     */
    private function syncVehicleCancelled(Assignment $assignment, string $oldStatus): void
    {
        if ($oldStatus !== 'in_progress') {
            return;
        }

        $hasOtherInProgress = Assignment::where('vehicle_id', $assignment->vehicle_id)
            ->where('id', '!=', $assignment->id)
            ->where('status', 'in_progress')
            ->exists();

        if (! $hasOtherInProgress) {
            DB::table('vehicles')
                ->where('id', $assignment->vehicle_id)
                ->update([
                    'status'            => 'available',
                    'current_driver_id' => null,
                ]);
        }
    }
}
