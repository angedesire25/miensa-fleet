<?php

namespace App\Observers;

use App\Models\Driver;

/**
 * Maintient les statistiques dénormalisées du chauffeur à jour.
 *
 * total_km          → recalculé depuis trip_logs
 * total_assignments → recalculé depuis assignments (status = completed)
 * total_infractions → recalculé depuis infractions
 *
 * Ces compteurs sont mis à jour par les observers de TripLog, Assignment
 * et Infraction (à créer). Ce classe sert d'accroche pour les événements
 * directs sur le modèle Driver lui-même.
 */
class DriverObserver
{
    /** Avant suppression douce : libérer le véhicule courant si nécessaire */
    public function deleting(Driver $driver): void
    {
        // Si le véhicule a ce chauffeur comme current_driver, on le libère
        if ($driver->preferredVehicle && $driver->preferredVehicle->current_driver_id === $driver->id) {
            $driver->preferredVehicle->update(['current_driver_id' => null]);
        }
    }
}
