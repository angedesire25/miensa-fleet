<?php

namespace App\Observers;

use App\Models\PartReplacement;
use App\Services\AlertService;
use Illuminate\Support\Facades\DB;

class PartReplacementObserver
{
    public function __construct(
        private readonly AlertService $alertService,
    ) {}

    /**
     * Réagit à chaque sauvegarde d'une pièce remplacée.
     *
     * Si `failed_at` vient d'être renseigné pour la première fois :
     *   1. Calcule days_until_failure = failed_at - replaced_at
     *   2. Calcule under_warranty_at_failure = failed_at <= warranty_expiry
     *   3. Passe status → failed
     *   4. Si pièce sous garantie → alerte vehicle_anomaly (severity critical)
     *
     * Utilise DB::table() pour les mises à jour afin d'éviter une boucle
     * d'événements Eloquent (le saved() ne doit pas se re-déclencher).
     */
    public function saved(PartReplacement $part): void
    {
        // Déclenche uniquement quand failed_at passe de null à une date réelle
        if (! $part->wasChanged('failed_at') || $part->failed_at === null) {
            return;
        }

        // ── Calculs automatiques ────────────────────────────────────────────
        $daysUntilFailure = (int) $part->replaced_at->diffInDays($part->failed_at);

        $underWarranty = $part->warranty_expiry !== null
            && $part->failed_at->lte($part->warranty_expiry);

        // ── Persistance sans observer loop ──────────────────────────────────
        DB::table('parts_replacements')
            ->where('id', $part->id)
            ->update([
                'days_until_failure'        => $daysUntilFailure,
                'under_warranty_at_failure' => $underWarranty,
                'status'                    => 'failed',
            ]);

        // ── Alerte garantie : pièce défaillante encore sous garantie ────────
        if (! $underWarranty) {
            return;
        }

        $vehicle = $part->vehicle;
        $garage  = $part->replacedByGarage;
        $plate   = $vehicle?->plate ?? "véhicule #{$part->vehicle_id}";

        $this->alertService->createAlert('vehicle_anomaly', [
            'vehicle_id' => $part->vehicle_id,
            'title'      => "Pièce sous garantie défaillante : {$part->part_name} — {$plate}",
            'message'    => "Pièce sous garantie défaillante : {$part->part_name}"
                          . " sur véhicule {$plate}."
                          . " Changée le {$part->replaced_at->format('d/m/Y')}"
                          . " ({$daysUntilFailure} jour(s)),"
                          . " garantie jusqu'au {$part->warranty_expiry->format('d/m/Y')}."
                          . ($garage ? " Contacter le garage {$garage->name}." : ''),
            'severity'   => 'critical',
            'channels'   => ['in_app', 'email'],
        ]);
    }
}
