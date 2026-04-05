<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Infraction;
use App\Models\User;
use App\Models\VehicleRequest;
use App\Notifications\InfractionImputedNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

class InfractionService
{
    // ──────────────────────────────────────────────────────────────────────
    // Identification du conducteur
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Identifie le conducteur responsable d'un véhicule à un instant donné.
     *
     * Implémente la règle métier #6 (version service, indépendante d'un modèle
     * Infraction existant) : cherche d'abord dans les affectations chauffeur,
     * puis dans les demandes de véhicule.
     *
     * Conditions SQL :
     *   WHERE vehicle_id = $vehicleId
     *   AND   status IN (in_progress, completed)
     *   AND   datetime_start <= $datetime
     *   AND   (datetime_end_actual >= $datetime          -- si renseignée
     *          OR (datetime_end_actual IS NULL
     *              AND datetime_end_planned >= $datetime)) -- sinon
     *
     * @param int    $vehicleId  Identifiant du véhicule impliqué
     * @param Carbon $datetime   Instant de l'infraction
     *
     * @return array{driver_id: int|null, user_id: int|null, source: string}
     */
    public function identifyDriver(int $vehicleId, Carbon $datetime): array
    {
        // ── 1. Cherche dans les affectations ────────────────────────────────
        $assignment = Assignment::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['in_progress', 'completed'])
            ->where('datetime_start', '<=', $datetime)
            ->where(function (Builder $q) use ($datetime) {
                $q->where(function (Builder $inner) use ($datetime) {
                    $inner->whereNotNull('datetime_end_actual')
                          ->where('datetime_end_actual', '>=', $datetime);
                })->orWhere(function (Builder $inner) use ($datetime) {
                    $inner->whereNull('datetime_end_actual')
                          ->where('datetime_end_planned', '>=', $datetime);
                });
            })
            ->orderByDesc('datetime_start')
            ->first();

        if ($assignment) {
            return [
                'driver_id' => $assignment->driver_id,
                'user_id'   => null,
                'source'    => 'assignment',
            ];
        }

        // ── 2. Cherche dans les demandes de véhicule ────────────────────────
        $request = VehicleRequest::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['in_progress', 'completed'])
            ->where('datetime_start', '<=', $datetime)
            ->where(function (Builder $q) use ($datetime) {
                $q->where(function (Builder $inner) use ($datetime) {
                    $inner->whereNotNull('datetime_end_actual')
                          ->where('datetime_end_actual', '>=', $datetime);
                })->orWhere(function (Builder $inner) use ($datetime) {
                    $inner->whereNull('datetime_end_actual')
                          ->where('datetime_end_planned', '>=', $datetime);
                });
            })
            ->orderByDesc('datetime_start')
            ->first();

        if ($request) {
            return [
                'driver_id' => null,
                'user_id'   => $request->requester_id,
                'source'    => 'request',
            ];
        }

        return [
            'driver_id' => null,
            'user_id'   => null,
            'source'    => 'unknown',
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Enregistrement du paiement
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Enregistre le règlement d'une amende sur une infraction.
     *
     * Met à jour fine_amount, payment_date, payment_notes et déduit
     * payment_status depuis l'imputation déjà décidée :
     *   - imputation = 'company' → paid_by_company
     *   - imputation = 'driver'  → charged_to_driver
     *
     * Loggue l'activité via Spatie Activitylog.
     *
     * @param Infraction $infraction  L'infraction à solder
     * @param float      $amount      Montant réglé
     * @param Carbon     $date        Date du règlement
     * @param string     $notes       Justificatif, référence virement…
     */
    public function recordPayment(
        Infraction $infraction,
        float      $amount,
        Carbon     $date,
        string     $notes
    ): Infraction {
        $paymentStatus = match ($infraction->imputation) {
            'driver'  => 'charged_to_driver',
            default   => 'paid_by_company',
        };

        $infraction->update([
            'fine_amount'    => $amount,
            'payment_status' => $paymentStatus,
            'payment_date'   => $date,
            'payment_notes'  => $notes,
        ]);

        activity('infractions')
            ->performedOn($infraction)
            ->log('Infraction payment recorded');

        return $infraction->fresh();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Imputation financière
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Décide qui supporte financièrement l'amende d'une infraction.
     *
     * Met à jour :
     *   - imputation          → 'company' | 'driver'
     *   - sanction_decided_by → $decidedBy->id
     *
     * Si $target = 'driver' et qu'un driver_id est renseigné sur l'infraction,
     * envoie une InfractionImputedNotification par email au chauffeur concerné.
     *
     * Loggue l'activité via Spatie Activitylog.
     *
     * @param Infraction $infraction  L'infraction à imputer
     * @param string     $target      'company' ou 'driver'
     * @param User       $decidedBy   Utilisateur qui prend la décision
     */
    public function impute(
        Infraction $infraction,
        string     $target,
        User       $decidedBy
    ): Infraction {
        $infraction->update([
            'imputation'          => $target,
            'sanction_decided_by' => $decidedBy->id,
        ]);

        // ── Notification chauffeur si imputation = driver ───────────────────
        if ($target === 'driver' && $infraction->driver_id !== null) {
            $driver = $infraction->driver()->with('user')->first();

            if ($driver !== null) {
                if ($driver->user !== null) {
                    // Le chauffeur a un compte utilisateur → notification native Laravel
                    $driver->user->notify(new InfractionImputedNotification($infraction->fresh()));
                } elseif ($driver->email !== null) {
                    // Pas de compte utilisateur → envoi via route email directe
                    Notification::route('mail', $driver->email)
                        ->notify(new InfractionImputedNotification($infraction->fresh()));
                }
            }
        }

        activity('infractions')
            ->performedOn($infraction)
            ->causedBy($decidedBy)
            ->log('Infraction imputed to ' . $target);

        return $infraction->fresh();
    }
}
