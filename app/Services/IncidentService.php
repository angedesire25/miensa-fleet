<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\PartReplacement;
use App\Models\Repair;
use App\Models\User;
use App\Notifications\PartUnderWarrantyFailedNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IncidentService
{
    public function __construct(
        private readonly AlertService $alertService,
    ) {}

    /**
     * Enregistre un nouveau sinistre.
     *
     * Si le véhicule est immobilisé (vehicle_immobilized = true), son statut
     * passe à 'breakdown' pour le retirer de la flotte disponible.
     * Une alerte critique est automatiquement créée pour les sinistres graves
     * (severity = major ou total_loss).
     */
    public function createIncident(array $data): Incident
    {
        $incident = Incident::create($data);

        // Immobilisation du véhicule si nécessaire
        if ($data['vehicle_immobilized'] ?? false) {
            DB::table('vehicles')
                ->where('id', $incident->vehicle_id)
                ->update(['status' => 'breakdown']);
        }

        activity('incidents')
            ->performedOn($incident)
            ->log("Sinistre créé : {$incident->type} — véhicule #{$incident->vehicle_id}");

        // Alerte immédiate pour les sinistres graves
        if (in_array($incident->severity, ['major', 'total_loss'], true)) {
            $vehicle = $incident->vehicle;
            $plate   = $vehicle?->plate ?? "véhicule #{$incident->vehicle_id}";

            $this->alertService->createAlert('vehicle_anomaly', [
                'vehicle_id' => $incident->vehicle_id,
                'title'      => "Sinistre grave — {$plate}",
                'message'    => "Sinistre de type {$incident->type} (sévérité : {$incident->severity})"
                              . " déclaré pour le véhicule {$plate}.",
                'severity'   => 'critical',
                'channels'   => ['in_app', 'email'],
            ]);
        }

        return $incident;
    }

    /**
     * Envoie le véhicule sinistré au garage pour réparation.
     *
     * Crée une réparation liée au sinistre, passe le véhicule en statut
     * 'maintenance' et l'incident en statut 'at_garage'.
     * Utilise DB::table() pour éviter de déclencher RepairObserver::updated()
     * sur un enregistrement en cours de création.
     */
    public function sendToGarage(Incident $incident, int $garageId, array $repairData): Repair
    {
        $repair = Repair::create(array_merge($repairData, [
            'incident_id' => $incident->id,
            'vehicle_id'  => $incident->vehicle_id,
            'garage_id'   => $garageId,
            'status'      => 'sent',
        ]));

        // Mise à jour véhicule et sinistre sans déclencher les observers
        DB::table('vehicles')
            ->where('id', $incident->vehicle_id)
            ->update(['status' => 'maintenance']);

        DB::table('incidents')
            ->where('id', $incident->id)
            ->update(['status' => 'at_garage']);

        $garage      = $repair->garage;
        $plate       = $incident->vehicle?->plate ?? "véhicule #{$incident->vehicle_id}";
        $garageLabel = $garage?->name ?? "garage #{$garageId}";

        activity('incidents')
            ->performedOn($incident)
            ->log("Véhicule {$plate} envoyé au garage {$garageLabel}");

        return $repair;
    }

    /**
     * Enregistre le retour du véhicule après réparation en garage.
     *
     * Met à jour les données de retour (état, travaux effectués, facturation),
     * remet le véhicule en service et clôt ou rouvre le sinistre selon
     * la présence d'un problème persistant.
     *
     * Si has_persistent_issue = true :
     *   → repair->status = returned_with_issue
     *   → incident->status = open (sinistre rouvert)
     *   → alerte critique créée
     *
     * Appelle checkRecurrence() pour détecter une éventuelle récurrence de panne.
     * Utilise DB::table() pour éviter une boucle avec RepairObserver::updated().
     */
    public function returnFromGarage(Repair $repair, array $returnData): Repair
    {
        $hasPersistentIssue = $returnData['has_persistent_issue'] ?? false;
        $repairStatus       = $hasPersistentIssue ? 'returned_with_issue' : 'returned';

        // Champs autorisés à être mis à jour lors du retour
        $updateData = array_intersect_key($returnData, array_flip([
            'datetime_returned', 'condition_at_return', 'work_performed',
            'parts_replaced', 'invoice_amount', 'received_by',
        ]));

        // Le champ JSON parts_replaced doit être encodé manuellement (DB::table ne caste pas)
        if (isset($updateData['parts_replaced']) && is_array($updateData['parts_replaced'])) {
            $updateData['parts_replaced'] = json_encode($updateData['parts_replaced']);
        }

        DB::table('repairs')
            ->where('id', $repair->id)
            ->update(array_merge($updateData, ['status' => $repairStatus]));

        // Retour du véhicule en service
        DB::table('vehicles')
            ->where('id', $repair->vehicle_id)
            ->update([
                'status'                  => 'available',
                'last_repair_returned_at' => now(),
            ]);

        // Mise à jour du sinistre lié
        if ($repair->incident_id !== null) {
            $incidentStatus = $hasPersistentIssue ? 'open' : 'repaired';
            DB::table('incidents')
                ->where('id', $repair->incident_id)
                ->update(['status' => $incidentStatus]);
        }

        // Alerte si problème persistant après retour
        if ($hasPersistentIssue) {
            $vehicle = $repair->vehicle;
            $plate   = $vehicle?->plate ?? "véhicule #{$repair->vehicle_id}";

            $this->alertService->createAlert('vehicle_anomaly', [
                'vehicle_id' => $repair->vehicle_id,
                'title'      => "Problème persistant après réparation — {$plate}",
                'message'    => "Le véhicule {$plate} est retourné avec un problème persistant"
                              . " après l'intervention #{$repair->id}.",
                'severity'   => 'critical',
                'channels'   => ['in_app', 'email'],
            ]);
        }

        // Rechargement du modèle pour que checkRecurrence dispose des données à jour
        $repair->refresh();
        $this->checkRecurrence($repair);

        return $repair->fresh();
    }

    /**
     * Détecte une récurrence de panne sur un véhicule.
     *
     * Logique clef : cherche dans l'historique une réparation précédente
     * sur le même véhicule, pour le même type de sinistre (incident->type),
     * dont le retour date de moins de 12 mois.
     *
     * Si une récurrence est trouvée :
     *   → repair.same_issue_recurrence  = true
     *   → repair.previous_repair_id     = id de la réparation précédente
     *   → repair.recurrence_delay_days  = jours entre l'ancien retour et le nouvel envoi
     *   → alerte critique créée
     *
     * Utilise DB::table() pour ne pas re-déclencher RepairObserver::updated()
     * (l'observer est le filet de sécurité pour les mises à jour directes).
     */
    public function checkRecurrence(Repair $repair): void
    {
        // Récurrence uniquement pertinente pour les réparations liées à un sinistre
        if ($repair->incident_id === null) {
            return;
        }

        $incident = $repair->incident;
        if ($incident === null) {
            return;
        }

        // Réparation précédente : même véhicule, même type de sinistre, terminée dans les 12 mois
        $previousRepair = Repair::query()
            ->where('vehicle_id', $repair->vehicle_id)
            ->where('id', '!=', $repair->id)
            ->whereIn('status', ['returned', 'returned_with_issue'])
            ->whereNotNull('datetime_returned')
            ->where('datetime_returned', '>', now()->subMonths(12))
            ->whereHas('incident', fn(Builder $q) => $q->where('type', $incident->type))
            ->latest('datetime_returned')
            ->first();

        if ($previousRepair === null) {
            return;
        }

        // Délai entre le retour précédent et le nouvel envoi au garage
        $delayDays = ($previousRepair->datetime_returned !== null && $repair->datetime_sent !== null)
            ? (int) $previousRepair->datetime_returned->diffInDays($repair->datetime_sent)
            : null;

        DB::table('repairs')
            ->where('id', $repair->id)
            ->update([
                'same_issue_recurrence' => true,
                'previous_repair_id'    => $previousRepair->id,
                'recurrence_delay_days' => $delayDays,
            ]);

        $vehicle     = $repair->vehicle;
        $garage      = $repair->garage;
        $plate       = $vehicle?->plate ?? "véhicule #{$repair->vehicle_id}";
        $garageLabel = $garage?->name ?? 'garage inconnu';
        $delayLabel  = $delayDays !== null ? "{$delayDays} jour(s)" : 'délai inconnu';

        $this->alertService->createAlert('vehicle_anomaly', [
            'vehicle_id' => $repair->vehicle_id,
            'title'      => "Récurrence de panne — {$plate}",
            'message'    => "Le véhicule {$plate} présente une récurrence de panne"
                          . " (type : {$incident->type}) {$delayLabel} après"
                          . " sa dernière réparation au {$garageLabel}.",
            'severity'   => 'critical',
            'channels'   => ['in_app', 'email'],
        ]);
    }

    /**
     * Enregistre les pièces remplacées lors d'une réparation.
     *
     * Pour chaque pièce, si warranty_months est renseigné, calcule automatiquement
     * warranty_expiry à partir de replaced_at. Toutes les pièces créées sont actives.
     *
     * Note : la création déclenche PartReplacementObserver::saved() mais celui-ci
     * ne réagit que si failed_at change — aucune boucle ici.
     */
    public function recordPartReplacement(Repair $repair, array $parts): Collection
    {
        $created = collect();

        foreach ($parts as $partData) {
            // Calcul automatique de la date d'expiration de garantie
            if (! empty($partData['warranty_months']) && ! empty($partData['replaced_at'])) {
                $replacedAt                 = Carbon::parse($partData['replaced_at']);
                $partData['warranty_expiry'] = $replacedAt
                    ->addMonths((int) $partData['warranty_months'])
                    ->toDateString();
            }

            $part = PartReplacement::create(array_merge($partData, [
                'vehicle_id' => $repair->vehicle_id,
                'repair_id'  => $repair->id,
                'status'     => 'active',
            ]));

            $created->push($part);
        }

        return $created;
    }

    /**
     * Déclare une pièce comme défaillante et enregistre les données associées.
     *
     * Logique clef :
     *   → calcule days_until_failure = failed_at - replaced_at
     *   → calcule under_warranty_at_failure = failed_at <= warranty_expiry
     *   → passe status → failed
     *   → si sous garantie : alerte critique + notification aux gestionnaires de flotte
     *
     * Utilise DB::table() pour éviter une boucle avec PartReplacementObserver::saved()
     * qui effectue les mêmes calculs (il sert de filet pour les mises à jour directes).
     */
    public function reportPartFailure(PartReplacement $part, array $data): PartReplacement
    {
        $failedAt   = Carbon::parse($data['failed_at']);
        $replacedAt = $part->replaced_at;

        $daysUntilFailure = $replacedAt !== null
            ? (int) $replacedAt->diffInDays($failedAt)
            : null;

        $underWarranty = $part->warranty_expiry !== null
            && $failedAt->lte($part->warranty_expiry);

        // Mise à jour sans déclencher l'observer (qui recalculerait les mêmes valeurs)
        DB::table('parts_replacements')
            ->where('id', $part->id)
            ->update([
                'failed_at'                      => $failedAt->toDateString(),
                'failure_reason'                 => $data['failure_reason'] ?? null,
                'failure_reported_in_repair_id'  => $data['failure_reported_in_repair_id'] ?? null,
                'days_until_failure'             => $daysUntilFailure,
                'under_warranty_at_failure'      => $underWarranty,
                'status'                         => 'failed',
            ]);

        // Alerte critique + notification si pièce encore sous garantie
        if ($underWarranty) {
            $vehicle = $part->vehicle;
            $garage  = $part->replacedByGarage;
            $plate   = $vehicle?->plate ?? "véhicule #{$part->vehicle_id}";

            $this->alertService->createAlert('vehicle_anomaly', [
                'vehicle_id' => $part->vehicle_id,
                'title'      => "Pièce sous garantie défaillante : {$part->part_name} — {$plate}",
                'message'    => "La pièce {$part->part_name} montée sur le véhicule {$plate}"
                              . " est tombée en panne après {$daysUntilFailure} jour(s),"
                              . " alors qu'elle est encore sous garantie"
                              . " jusqu'au {$part->warranty_expiry->format('d/m/Y')}."
                              . ($garage ? " Contacter le garage {$garage->name}." : ''),
                'severity'   => 'critical',
                'channels'   => ['in_app', 'email'],
            ]);

            // Notification directe aux gestionnaires de flotte
            $freshPart = $part->fresh();
            User::role('fleet_manager')->each(
                fn(User $manager) => $manager->notify(new PartUnderWarrantyFailedNotification($freshPart))
            );
        }

        $daysLabel = $daysUntilFailure !== null ? "{$daysUntilFailure} jour(s)" : 'durée inconnue';

        activity('parts_replacements')
            ->performedOn($part)
            ->log(
                "Pièce {$part->part_name} déclarée défaillante après {$daysLabel} de service"
                . ($underWarranty ? ' — encore sous garantie' : '')
            );

        return $part->fresh();
    }
}
