<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Levée par VehicleRequestService::approve() quand le véhicule demandé
 * est déjà occupé sur le créneau de la demande.
 *
 * Peut indiquer un conflit avec :
 *   - une affectation chauffeur (Assignment)
 *   - une autre demande de véhicule (VehicleRequest)
 */
class VehicleAvailabilityException extends RuntimeException
{
    public function __construct(
        string      $message  = 'Le véhicule n\'est pas disponible sur ce créneau.',
        int         $code     = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
