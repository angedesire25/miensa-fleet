<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Models\VehicleRequest;
use Illuminate\Support\Facades\DB;

class VehicleRequestObserver
{
    /**
     * Chaque changement de statut d'une demande de véhicule
     * peut modifier le statut du véhicule associé.
     */
    public function updated(VehicleRequest $request): void
    {
        if (! $request->wasChanged('status')) {
            return;
        }

        $oldStatus = $request->getOriginal('status');
        $newStatus = $request->status;

        match ($newStatus) {
            'approved'    => $this->syncVehicleApproved($request),
            'in_progress' => $this->syncVehicleStarted($request),
            'completed'   => $this->syncVehicleCompleted($request),
            'cancelled'   => $this->syncVehicleCancelled($request, $oldStatus),
            'rejected'    => $this->syncVehicleRejected($request, $oldStatus),
            default       => null,
        };
    }

    // ── Méthodes privées ───────────────────────────────────────────────────

    /**
     * Statut → approved.
     *
     * La demande est acceptée et un véhicule attribué : le véhicule est
     * désormais engagé sur cette demande et ne doit plus apparaître comme
     * disponible pour d'autres réservations.
     */
    private function syncVehicleApproved(VehicleRequest $request): void
    {
        if ($request->vehicle_id === null) {
            return; // Approbation sans véhicule encore attribué — rien à faire
        }

        DB::table('vehicles')
            ->where('id', $request->vehicle_id)
            ->update(['status' => 'on_mission']);
    }

    /**
     * Statut → in_progress.
     *
     * Le collaborateur a pris le véhicule (km_start saisi — règle #5).
     * On s'assure que le statut est bien on_mission (idempotent si already set).
     */
    private function syncVehicleStarted(VehicleRequest $request): void
    {
        if ($request->vehicle_id === null) {
            return;
        }

        DB::table('vehicles')
            ->where('id', $request->vehicle_id)
            ->update(['status' => 'on_mission']);
    }

    /**
     * Statut → completed.
     *
     * Le véhicule est rendu : on le remet disponible, sauf si une affectation
     * chauffeur ou une autre demande est en cours sur ce même véhicule.
     *
     * Note : km_total sur vehicle_requests est une colonne virtuelle MySQL
     * (km_end - km_start), pas besoin de calcul applicatif ici.
     * Le total_km du chauffeur n'est pas mis à jour ici car c'est un
     * collaborateur non-chauffeur qui conduit (pas de profil Driver).
     */
    private function syncVehicleCompleted(VehicleRequest $request): void
    {
        if ($request->vehicle_id === null) {
            return;
        }

        if ($this->vehicleIsStillBusy($request)) {
            return;
        }

        DB::table('vehicles')
            ->where('id', $request->vehicle_id)
            ->update(['status' => 'available']);
    }

    /**
     * Statut → cancelled.
     *
     * On ne remet le véhicule disponible QUE si la demande était in_progress
     * (le véhicule avait physiquement quitté le parc).
     * Une demande simplement approved/confirmed n'a pas encore de véhicule sorti.
     */
    private function syncVehicleCancelled(VehicleRequest $request, string $oldStatus): void
    {
        if ($request->vehicle_id === null) {
            return;
        }

        if ($oldStatus !== 'in_progress') {
            return;
        }

        if ($this->vehicleIsStillBusy($request)) {
            return;
        }

        DB::table('vehicles')
            ->where('id', $request->vehicle_id)
            ->update(['status' => 'available']);
    }

    /**
     * Statut → rejected.
     *
     * Si le gestionnaire rejette une demande qui était déjà approved
     * (véhicule mis en on_mission), on libère le véhicule.
     */
    private function syncVehicleRejected(VehicleRequest $request, string $oldStatus): void
    {
        if ($request->vehicle_id === null) {
            return;
        }

        if (! in_array($oldStatus, ['approved', 'confirmed'], true)) {
            return;
        }

        if ($this->vehicleIsStillBusy($request)) {
            return;
        }

        DB::table('vehicles')
            ->where('id', $request->vehicle_id)
            ->update(['status' => 'available']);
    }

    /**
     * Vérifie si le véhicule est encore occupé par une autre affectation
     * ou une autre demande active.
     *
     * Evite de remettre un véhicule en "available" alors qu'il est utilisé
     * en parallèle (ne devrait pas arriver grâce aux règles de conflit,
     * mais cette garde protège contre les incohérences de données).
     */
    private function vehicleIsStillBusy(VehicleRequest $request): bool
    {
        $busyAssignment = Assignment::where('vehicle_id', $request->vehicle_id)
            ->where('status', 'in_progress')
            ->exists();

        if ($busyAssignment) {
            return true;
        }

        $busyRequest = VehicleRequest::where('vehicle_id', $request->vehicle_id)
            ->where('id', '!=', $request->id)
            ->where('status', 'in_progress')
            ->exists();

        return $busyRequest;
    }
}
