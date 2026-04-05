<?php

namespace App\Observers;

use App\Models\Repair;
use App\Services\AlertService;
use Illuminate\Support\Facades\DB;

class RepairObserver
{
    public function __construct(
        private readonly AlertService $alertService,
    ) {}

    /**
     * Réagit aux changements de statut et aux récurrences de pannes.
     *
     * Événements traités :
     *   → sent                                  : véhicule mis en maintenance
     *   → returned / returned_with_issue         : véhicule remis en service
     *   → same_issue_recurrence = true           : calcul du délai et alerte critique
     */
    public function updated(Repair $repair): void
    {
        // ── Changement de statut ─────────────────────────────────────────────
        if ($repair->wasChanged('status')) {
            $newStatus = $repair->status;

            if ($newStatus === 'sent') {
                $this->syncVehicleSent($repair);
            }

            if (in_array($newStatus, ['returned', 'returned_with_issue'], true)) {
                $this->syncVehicleReturned($repair);
            }
        }

        // ── Récurrence de panne détectée ────────────────────────────────────
        // Déclenché quand same_issue_recurrence passe à true et qu'une
        // réparation précédente est liée.
        if (
            $repair->wasChanged('same_issue_recurrence')
            && $repair->same_issue_recurrence
            && $repair->previous_repair_id !== null
        ) {
            $this->handleRecurrence($repair);
        }
    }

    // ── Méthodes privées ───────────────────────────────────────────────────

    /**
     * Passe le véhicule en statut `maintenance` quand il est envoyé au garage.
     * Utilise DB::table() pour éviter de déclencher les observers du Vehicle.
     */
    private function syncVehicleSent(Repair $repair): void
    {
        DB::table('vehicles')
            ->where('id', $repair->vehicle_id)
            ->update(['status' => 'maintenance']);
    }

    /**
     * Remet le véhicule en service après retour du garage.
     *
     *   - vehicle.status                 → available
     *   - vehicle.last_repair_returned_at → now()
     *
     * Si la réparation a une garantie (warranty_months renseigné),
     * calcule et persiste warranty_expiry sur la réparation elle-même.
     */
    private function syncVehicleReturned(Repair $repair): void
    {
        // Mise à jour du véhicule sans observer loop
        DB::table('vehicles')
            ->where('id', $repair->vehicle_id)
            ->update([
                'status'                  => 'available',
                'last_repair_returned_at' => now(),
            ]);

        // Calcul de la date d'expiration de la garantie pièce/main d'œuvre
        if ($repair->warranty_months !== null && $repair->datetime_returned !== null) {
            $warrantyExpiry = $repair->datetime_returned
                ->addMonths($repair->warranty_months)
                ->toDateString();

            DB::table('repairs')
                ->where('id', $repair->id)
                ->update(['warranty_expiry' => $warrantyExpiry]);
        }
    }

    /**
     * Gère la détection d'une récurrence de panne.
     *
     *   1. Calcule recurrence_delay_days = datetime_sent - previous_repair.datetime_returned
     *   2. Crée une alerte vehicle_anomaly (severity critical) listant :
     *      le véhicule, le délai de récurrence et le garage concerné.
     */
    private function handleRecurrence(Repair $repair): void
    {
        // ── Calcul du délai de récurrence ────────────────────────────────────
        $previousRepair = Repair::withTrashed()->find($repair->previous_repair_id);

        if ($previousRepair?->datetime_returned !== null && $repair->datetime_sent !== null) {
            $delayDays = (int) $previousRepair->datetime_returned->diffInDays($repair->datetime_sent);

            DB::table('repairs')
                ->where('id', $repair->id)
                ->update(['recurrence_delay_days' => $delayDays]);
        } else {
            $delayDays = null;
        }

        // ── Alerte critique : récurrence de panne ────────────────────────────
        $vehicle     = $repair->vehicle;
        $garage      = $repair->garage;
        $garageLabel = $garage?->name ?? 'garage inconnu';
        $plate       = $vehicle?->plate ?? "véhicule #{$repair->vehicle_id}";
        $delayLabel  = $delayDays !== null ? "{$delayDays} jour(s)" : 'délai inconnu';

        $this->alertService->createAlert('vehicle_anomaly', [
            'vehicle_id' => $repair->vehicle_id,
            'title'      => "Récurrence de panne — {$plate}",
            'message'    => "Le véhicule {$plate} est revenu pour la même panne"
                          . " {$delayLabel} après sa dernière réparation au {$garageLabel}.",
            'severity'   => 'critical',
            'channels'   => ['in_app', 'email'],
        ]);
    }
}
