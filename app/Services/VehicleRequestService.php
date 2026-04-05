<?php

namespace App\Services;

use App\Exceptions\VehicleAvailabilityException;
use App\Models\Assignment;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VehicleRequestService
{
    // ──────────────────────────────────────────────────────────────────────
    // Vérification de disponibilité du véhicule
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Vérifie si un véhicule est disponible pour une plage horaire donnée.
     *
     * Consulte simultanément :
     *   - Les affectations actives (planned / confirmed / in_progress)
     *   - Les demandes de véhicule actives (approved / confirmed / in_progress)
     *
     * @param int      $vehicleId  Identifiant du véhicule à tester
     * @param Carbon   $start      Début du créneau souhaité
     * @param Carbon   $end        Fin du créneau souhaité
     * @param int|null $excludeId  Exclure une demande existante (utile lors de la modification)
     *
     * @return bool  true si le véhicule est libre, false s'il y a un conflit
     */
    public function checkVehicleAvailability(
        int    $vehicleId,
        Carbon $start,
        Carbon $end,
        ?int   $excludeId = null
    ): bool {
        // ── Conflit avec une affectation ────────────────────────────────────
        $assignmentConflict = Assignment::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['planned', 'confirmed', 'in_progress'])
            ->where('datetime_start', '<', $end)
            ->where('datetime_end_planned', '>', $start)
            ->exists();

        if ($assignmentConflict) {
            return false;
        }

        // ── Conflit avec une autre demande de véhicule ──────────────────────
        $requestConflict = VehicleRequest::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['approved', 'confirmed', 'in_progress'])
            ->where('datetime_start', '<', $end)
            ->where('datetime_end_planned', '>', $start)
            ->when($excludeId !== null, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        return ! $requestConflict;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Approbation d'une demande
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Approuve une demande de véhicule en lui affectant un véhicule.
     *
     * Exécute la vérification de disponibilité dans une transaction DB avec
     * verrou pessimiste sur le véhicule pour éviter les doublons en cas de
     * requêtes concurrentes.
     *
     * @param VehicleRequest $request     La demande à approuver (doit être en statut 'pending')
     * @param int            $vehicleId   Identifiant du véhicule à affecter
     * @param int            $reviewedBy  Identifiant de l'utilisateur qui approuve
     * @param string|null    $notes       Notes optionnelles de la revue
     *
     * @throws InvalidArgumentException      Si la demande n'est pas en statut 'pending'
     * @throws VehicleAvailabilityException  Si le véhicule est déjà occupé sur ce créneau
     */
    /**
     * @param int|null $driverId   Chauffeur professionnel à affecter (null si auto-conduite ou sans chauffeur)
     * @param bool     $selfDriving Le demandeur conduit lui-même
     */
    public function approve(
        VehicleRequest $request,
        int            $vehicleId,
        int            $reviewedBy,
        ?string        $notes      = null,
        ?int           $driverId   = null,
        bool           $selfDriving = false
    ): VehicleRequest {
        if ($request->status !== 'pending') {
            throw new InvalidArgumentException(
                "Impossible d'approuver la demande #{$request->id}"
                . " dont le statut est '{$request->status}'."
                . " Seules les demandes en statut 'pending' peuvent être approuvées."
            );
        }

        return DB::transaction(function () use ($request, $vehicleId, $reviewedBy, $notes, $driverId, $selfDriving) {
            // Verrou pessimiste sur la ligne véhicule pour éviter les race conditions
            // entre la vérification de disponibilité et la mise à jour.
            Vehicle::lockForUpdate()->findOrFail($vehicleId);

            $start = Carbon::parse($request->datetime_start);
            $end   = Carbon::parse($request->datetime_end_planned);

            if (! $this->checkVehicleAvailability($vehicleId, $start, $end, $request->id)) {
                $vehicle = Vehicle::find($vehicleId);
                $plate   = $vehicle?->plate ?? "ID {$vehicleId}";

                throw new VehicleAvailabilityException(
                    "Le véhicule {$plate} n'est pas disponible sur le créneau demandé"
                    . " ({$start->format('d/m/Y H:i')} – {$end->format('d/m/Y H:i')})."
                );
            }

            $request->update([
                'status'       => 'approved',
                'vehicle_id'   => $vehicleId,
                'driver_id'    => $selfDriving ? null : $driverId,
                'self_driving' => $selfDriving,
                'reviewed_by'  => $reviewedBy,
                'reviewed_at'  => now(),
                'review_notes' => $notes,
            ]);

            return $request->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // Rejet d'une demande
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Rejette une demande de véhicule.
     *
     * @param VehicleRequest $request     La demande à rejeter (doit être en statut 'pending')
     * @param int            $reviewedBy  Identifiant de l'utilisateur qui rejette
     * @param string|null    $reason      Motif du rejet (stocké dans review_notes)
     *
     * @throws InvalidArgumentException  Si la demande n'est pas en statut 'pending'
     */
    public function reject(
        VehicleRequest $request,
        int            $reviewedBy,
        ?string        $reason = null
    ): VehicleRequest {
        if ($request->status !== 'pending') {
            throw new InvalidArgumentException(
                "Impossible de rejeter la demande #{$request->id}"
                . " dont le statut est '{$request->status}'."
                . " Seules les demandes en statut 'pending' peuvent être rejetées."
            );
        }

        $request->update([
            'status'       => 'rejected',
            'reviewed_by'  => $reviewedBy,
            'reviewed_at'  => now(),
            'review_notes' => $reason,
        ]);

        return $request->fresh();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Clôture d'une demande
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Clôture une demande de véhicule en cours.
     *
     * Enregistre le kilométrage de retour, l'état du véhicule et passe le
     * statut à 'completed'. Le VehicleRequestObserver prend alors le relais
     * pour remettre le véhicule en statut 'available'.
     *
     * @param VehicleRequest $request       La demande à clôturer (doit être en statut 'in_progress')
     * @param int            $kmEnd         Kilométrage relevé au retour du véhicule
     * @param string         $conditionEnd  État du véhicule à la restitution ('good' | 'fair' | 'poor')
     * @param string|null    $notes         Notes optionnelles sur l'état du véhicule
     *
     * @throws InvalidArgumentException  Si le statut est invalide ou si km_end < km_start
     */
    public function close(
        VehicleRequest $request,
        int            $kmEnd,
        string         $conditionEnd,
        ?string        $notes = null
    ): VehicleRequest {
        if ($request->status !== 'in_progress') {
            throw new InvalidArgumentException(
                "Impossible de clôturer la demande #{$request->id}"
                . " dont le statut est '{$request->status}'."
                . " Seul le statut 'in_progress' permet la clôture."
            );
        }

        if ($request->km_start !== null && $kmEnd < $request->km_start) {
            throw new InvalidArgumentException(
                "Le kilométrage de retour ({$kmEnd} km) ne peut pas être"
                . " inférieur au kilométrage de départ ({$request->km_start} km)."
            );
        }

        $request->update([
            'status'              => 'completed',
            'km_end'              => $kmEnd,
            'condition_end'       => $conditionEnd,
            'condition_end_notes' => $notes,
            'datetime_end_actual' => now(),
        ]);

        // Recharge le modèle pour récupérer km_total (colonne virtuelle MySQL)
        return $request->fresh();
    }
}
