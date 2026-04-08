<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\DriverDocument;
use App\Models\Inspection;
use App\Models\PartReplacement;
use App\Models\Repair;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Models\VehicleRequest;
use App\Jobs\SendAlertNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Seuils de préavis configurables.
     * Tous les documents expirant dans ces fenêtres déclenchent une alerte.
     */
    private const EXPIRY_WARNING_DAYS  = 30; // Alerte "expiring_soon"
    private const EXPIRY_CRITICAL_DAYS = 7;  // Alerte critique si < 7 jours

    /**
     * Délai (en heures) au-delà duquel une demande sans réponse déclenche
     * une alerte `request_pending_timeout`.
     */
    private const PENDING_TIMEOUT_HOURS = 4;

    // ──────────────────────────────────────────────────────────────────────
    // Vérifications planifiées (appelées par le scheduler)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Parcourt tous les vehicle_documents où expiry_date IS NOT NULL.
     *
     * Pour chaque document dont le type est 'insurance' ou 'technical_control' :
     *   - expiry_date < aujourd'hui        → insurance_expired / technical_control_expired
     *   - expiry_date entre J et J+30      → insurance_expiring / technical_control_expiring
     *   - expiry_date au-delà de J+30      → ignoré (pas encore à l'horizon d'alerte)
     *
     * Déduplique : ne crée PAS d'alerte si une alerte du même type
     * pour le même vehicle_id avec status IN (new, seen) existe déjà.
     */
    public function checkVehicleDocuments(): void
    {
        $typeMap = [
            'insurance'         => ['expiring' => 'insurance_expiring',         'expired' => 'insurance_expired'],
            'technical_control' => ['expiring' => 'technical_control_expiring', 'expired' => 'technical_control_expired'],
        ];

        VehicleDocument::whereNotNull('expiry_date')
            ->whereIn('type', array_keys($typeMap))
            ->with('vehicle')
            ->each(function (VehicleDocument $doc) use ($typeMap) {
                $vehicle = $doc->vehicle;
                if (! $vehicle) {
                    return;
                }

                $today     = now()->startOfDay();
                $expiryDay = $doc->expiry_date->copy()->startOfDay();

                // Détermination du type d'alerte selon la position de expiry_date
                if ($expiryDay->lt($today)) {
                    $alertType = $typeMap[$doc->type]['expired'];
                } elseif ($expiryDay->lte($today->copy()->addDays(self::EXPIRY_WARNING_DAYS))) {
                    $alertType = $typeMap[$doc->type]['expiring'];
                } else {
                    return; // Pas encore à l'horizon des 30 jours → rien à faire
                }

                // Déduplication : même type + même vehicle_id + statut non-traité
                $alreadyExists = Alert::where('type', $alertType)
                    ->where('vehicle_id', $vehicle->id)
                    ->whereIn('status', ['new', 'seen'])
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $days     = $doc->daysUntilExpiry(); // Négatif si déjà expiré
                $expired  = $expiryDay->lt($today);
                $severity = ($expired || ($days !== null && $days <= self::EXPIRY_CRITICAL_DAYS))
                    ? 'critical'
                    : 'warning';

                if ($expired) {
                    $title   = "Document expiré : {$doc->type} — {$vehicle->plate}";
                    $message = "Le document \"{$doc->document_number}\" ({$doc->type}) du véhicule"
                             . " {$vehicle->brand} {$vehicle->model} ({$vehicle->plate})"
                             . " a expiré le {$doc->expiry_date->format('d/m/Y')}.";
                } else {
                    $title   = "Expiration imminente : {$doc->type} — {$vehicle->plate}";
                    $message = "Le document \"{$doc->document_number}\" ({$doc->type}) du véhicule"
                             . " {$vehicle->brand} {$vehicle->model} ({$vehicle->plate})"
                             . " expire le {$doc->expiry_date->format('d/m/Y')}"
                             . ($days !== null ? " (dans {$days} jour(s))." : '.');
                }

                $this->createAlert($alertType, [
                    'vehicle_id'     => $vehicle->id,
                    'title'          => $title,
                    'message'        => $message,
                    'due_date'       => $doc->expiry_date,
                    'days_remaining' => $days,
                    'severity'       => $severity,
                    'channels'       => ['in_app', 'email'],
                ]);
            });
    }

    /**
     * Parcourt tous les driver_documents où expiry_date IS NOT NULL.
     *
     * Horizons d'alerte :
     *   - license, employment_contract : J-60
     *   - tous les autres types        : J-30
     *
     * Types d'alertes produits :
     *   - license_expiring         → permis expirant dans l'horizon
     *   - license_expired          → permis déjà expiré
     *   - medical_fitness_due      → visite médicale expirée ou dans l'horizon
     *   - driver_document_missing  → document obligatoire absent/manquant (passe séparée)
     *
     * Déduplique : ne crée PAS d'alerte si une alerte du même type
     * pour le même driver_id avec status IN (new, seen) existe déjà.
     */
    public function checkDriverDocuments(): void
    {
        // Horizon (jours) par type de document
        $horizons = [
            'license'             => 60,
            'employment_contract' => 60,
            // Tous les autres types → EXPIRY_WARNING_DAYS (30 j)
        ];

        // Mapping type de document → [expiring, expired]
        // null = pas de type d'alerte dédié pour ce document → ignoré
        $typeMap = [
            'license'         => ['expiring' => 'license_expiring',    'expired' => 'license_expired'],
            'medical_fitness' => ['expiring' => 'medical_fitness_due', 'expired' => 'medical_fitness_due'],
            // 'employment_contract' → 'contract_ending' (à brancher quand le type sera utilisé)
        ];

        // ── Boucle principale : expiry_date IS NOT NULL ─────────────────────
        DriverDocument::whereNotNull('expiry_date')
            ->whereIn('type', array_keys($typeMap))
            ->with('driver')
            ->each(function (DriverDocument $doc) use ($horizons, $typeMap) {
                $driver = $doc->driver;
                if (! $driver) {
                    return;
                }

                $horizon   = $horizons[$doc->type] ?? self::EXPIRY_WARNING_DAYS;
                $today     = now()->startOfDay();
                $expiryDay = $doc->expiry_date->copy()->startOfDay();

                // Détermination du type d'alerte selon la position de expiry_date
                if ($expiryDay->lt($today)) {
                    $alertType = $typeMap[$doc->type]['expired'];
                } elseif ($expiryDay->lte($today->copy()->addDays($horizon))) {
                    $alertType = $typeMap[$doc->type]['expiring'];
                } else {
                    return; // Pas encore dans l'horizon → rien à faire
                }

                // Déduplication : même type + même driver_id + statut non-traité
                $alreadyExists = Alert::where('type', $alertType)
                    ->where('driver_id', $driver->id)
                    ->whereIn('status', ['new', 'seen'])
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $days     = $doc->daysUntilExpiry(); // Négatif si déjà expiré
                $expired  = $expiryDay->lt($today);
                $severity = ($expired || ($days !== null && $days <= self::EXPIRY_CRITICAL_DAYS))
                    ? 'critical'
                    : 'warning';

                if ($expired) {
                    $title   = "Document expiré : {$doc->type} — {$driver->full_name}";
                    $message = "Le document « {$doc->type} »"
                             . ($doc->document_number ? " (n° {$doc->document_number})" : '')
                             . " du chauffeur {$driver->full_name}"
                             . " a expiré le {$doc->expiry_date->format('d/m/Y')}.";
                } else {
                    $title   = "Expiration imminente : {$doc->type} — {$driver->full_name}";
                    $message = "Le document « {$doc->type} »"
                             . ($doc->document_number ? " (n° {$doc->document_number})" : '')
                             . " du chauffeur {$driver->full_name}"
                             . " expire le {$doc->expiry_date->format('d/m/Y')}"
                             . ($days !== null ? " (dans {$days} jour(s))." : '.');
                }

                $this->createAlert($alertType, [
                    'driver_id'      => $driver->id,
                    'title'          => $title,
                    'message'        => $message,
                    'due_date'       => $doc->expiry_date,
                    'days_remaining' => $days,
                    'severity'       => $severity,
                    'channels'       => ['in_app', 'email'],
                ]);
            });

        // ── Documents obligatoires manquants (driver_document_missing) ───────
        // Pour chaque chauffeur actif, collecte tous les types obligatoires
        // absents ou marqués 'missing', puis crée une seule alerte les listant.
        $mandatoryTypes = ['license', 'medical_fitness'];

        Driver::active()
            ->each(function (Driver $driver) use ($mandatoryTypes) {
                $missing = [];

                foreach ($mandatoryTypes as $docType) {
                    $present = $driver->documents()
                        ->where('type', $docType)
                        ->where('status', '!=', 'missing')
                        ->exists();

                    if (! $present) {
                        $missing[] = $docType;
                    }
                }

                if (empty($missing)) {
                    return;
                }

                // Déduplication : même type + même driver_id + statut non-traité
                $alreadyExists = Alert::where('type', 'driver_document_missing')
                    ->where('driver_id', $driver->id)
                    ->whereIn('status', ['new', 'seen'])
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $list = implode(', ', $missing);

                $this->createAlert('driver_document_missing', [
                    'driver_id' => $driver->id,
                    'title'     => "Document(s) obligatoire(s) manquant(s) — {$driver->full_name}",
                    'message'   => "Le(s) document(s) suivant(s) sont absents ou marqués manquants"
                                 . " pour {$driver->full_name} : {$list}.",
                    'severity'  => 'warning',
                    'channels'  => ['in_app'],
                ]);
            });
    }

    /**
     * Vérifie tous les véhicules actifs dont km_current >= km_next_service
     * et crée une alerte `oil_change_due` s'il n'en existe pas déjà une non-traitée.
     */
    public function checkOilChangeDue(): void
    {
        Vehicle::active()
            ->whereNotNull('km_next_service')
            ->whereColumn('km_current', '>=', 'km_next_service')
            ->whereDoesntHave('alerts', function ($q) {
                $q->where('type', 'oil_change_due')->whereIn('status', ['new', 'seen']);
            })
            ->each(function (Vehicle $vehicle) {
                $km_over = $vehicle->km_current - $vehicle->km_next_service;

                $this->createAlert('oil_change_due', [
                    'vehicle_id' => $vehicle->id,
                    'title'      => "Vidange requise — {$vehicle->plate}",
                    'message'    => "Le véhicule {$vehicle->brand} {$vehicle->model} ({$vehicle->plate})"
                                 . " a atteint {$vehicle->km_current} km"
                                 . " (seuil de vidange : {$vehicle->km_next_service} km,"
                                 . " dépassement : +{$km_over} km).",
                    'severity'   => 'warning',
                    'channels'   => ['in_app', 'email'],
                ]);
            });
    }

    /**
     * Cherche les assignments et vehicle_requests en statut `in_progress`
     * dont datetime_end_planned est dépassée et crée une alerte
     * `vehicle_return_overdue` (severity = critical) par entrée en retard.
     *
     * Le message inclut : nom du chauffeur/demandeur, véhicule,
     * heure prévue de retour et retard en minutes.
     *
     * Déduplique : même type + même vehicle_id + status IN (new, seen).
     */
    public function checkOverdueReturns(): void
    {
        $now = now();

        // ── Affectations chauffeur en retard ────────────────────────────────
        Assignment::with(['vehicle', 'driver'])
            ->where('status', 'in_progress')
            ->where('datetime_end_planned', '<', $now)
            ->each(function (Assignment $assignment) use ($now) {
                $vehicle = $assignment->vehicle;
                $driver  = $assignment->driver;

                if (! $vehicle) {
                    return;
                }

                // Déduplication : même type + même vehicle_id + statut non-traité
                $alreadyExists = Alert::where('type', 'vehicle_return_overdue')
                    ->where('vehicle_id', $vehicle->id)
                    ->whereIn('status', ['new', 'seen'])
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $delayMinutes = (int) $now->diffInMinutes($assignment->datetime_end_planned);
                $plannedAt    = $assignment->datetime_end_planned->format('d/m/Y à H:i');

                $this->createAlert('vehicle_return_overdue', [
                    'vehicle_id' => $vehicle->id,
                    'driver_id'  => $driver?->id,
                    'title'      => "Retour en retard — {$vehicle->plate}",
                    'message'    => "Affectation #{$assignment->id} :"
                                 . " {$vehicle->brand} {$vehicle->model} ({$vehicle->plate})"
                                 . ($driver ? ", chauffeur {$driver->full_name}" : '')
                                 . ". Retour prévu le {$plannedAt}."
                                 . " Retard : {$delayMinutes} min.",
                    'severity'   => 'critical',
                    'channels'   => ['in_app', 'email', 'sms'],
                ]);
            });

        // ── Demandes de véhicule en retard ──────────────────────────────────
        VehicleRequest::with(['vehicle', 'user'])
            ->where('status', 'in_progress')
            ->where('datetime_end_planned', '<', $now)
            ->each(function (VehicleRequest $request) use ($now) {
                $vehicle = $request->vehicle;
                $user    = $request->user;

                if (! $vehicle) {
                    return;
                }

                // Déduplication : même type + même vehicle_id + statut non-traité
                $alreadyExists = Alert::where('type', 'vehicle_return_overdue')
                    ->where('vehicle_id', $vehicle->id)
                    ->whereIn('status', ['new', 'seen'])
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $delayMinutes = (int) $now->diffInMinutes($request->datetime_end_planned);
                $plannedAt    = $request->datetime_end_planned->format('d/m/Y à H:i');

                $this->createAlert('vehicle_return_overdue', [
                    'vehicle_id' => $vehicle->id,
                    'user_id'    => $user?->id,
                    'request_id' => $request->id,
                    'title'      => "Retour en retard — {$vehicle->plate}",
                    'message'    => "Demande #{$request->id} :"
                                 . " {$vehicle->brand} {$vehicle->model} ({$vehicle->plate})"
                                 . ($user ? ", demandeur {$user->name}" : '')
                                 . ". Retour prévu le {$plannedAt}."
                                 . " Retard : {$delayMinutes} min.",
                    'severity'   => 'critical',
                    'channels'   => ['in_app', 'email', 'sms'],
                ]);
            });
    }

    /**
     * Cherche les vehicle_requests en statut `pending` dont created_at
     * est antérieur à now() - fleet.pending_timeout_hours (défaut : 4 h).
     *
     * Crée une alerte `request_pending_timeout` (severity = warning)
     * si aucune alerte active du même type pour la même demande n'existe.
     *
     * Déduplique : même type + même request_id + status IN (new, seen).
     */
    public function checkPendingRequestsTimeout(): void
    {
        $timeoutHours = (int) config('fleet.pending_timeout_hours', self::PENDING_TIMEOUT_HOURS);
        $cutoff       = now()->subHours($timeoutHours);

        VehicleRequest::with(['user'])
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->each(function (VehicleRequest $request) use ($timeoutHours) {
                // Déduplication : même type + même request_id + statut non-traité
                $alreadyExists = Alert::where('type', 'request_pending_timeout')
                    ->where('request_id', $request->id)
                    ->whereIn('status', ['new', 'seen'])
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $user         = $request->user;
                $waitMinutes  = (int) now()->diffInMinutes($request->created_at);
                $submittedAt  = $request->created_at->format('d/m/Y à H:i');

                $this->createAlert('request_pending_timeout', [
                    'user_id'    => $user?->id,
                    'request_id' => $request->id,
                    'title'      => "Demande #{$request->id} sans réponse depuis {$waitMinutes} min",
                    'message'    => "La demande #{$request->id}"
                                 . ($user ? " de {$user->name}" : '')
                                 . ", soumise le {$submittedAt},"
                                 . " est toujours en attente après {$waitMinutes} min"
                                 . " (seuil : {$timeoutHours} h).",
                    'severity'   => 'warning',
                    'channels'   => ['in_app', 'email'],
                ]);
            });
    }

    /**
     * Cherche les pièces actives dont la garantie expire dans les 30 prochains jours.
     *
     * Critères : status = active AND warranty_expiry IS NOT NULL
     *            AND warranty_expiry BETWEEN aujourd'hui ET aujourd'hui + 30 jours
     *
     * Crée une alerte 'part_warranty_expiring' (severity = warning) par pièce concernée.
     * Déduplique : même type + même vehicle_id + statut non-traité.
     */
    public function checkPartsUnderWarranty(): void
    {
        $today   = now()->startOfDay();
        $horizon = $today->copy()->addDays(30);

        PartReplacement::with(['vehicle', 'replacedByGarage'])
            ->where('status', 'active')
            ->whereNotNull('warranty_expiry')
            ->whereBetween('warranty_expiry', [$today->toDateString(), $horizon->toDateString()])
            ->each(function (PartReplacement $part) {
                $vehicle = $part->vehicle;
                if (! $vehicle) {
                    return;
                }

                // Déduplication : même type + même vehicle_id + statut non-traité
                $alreadyExists = Alert::where('type', 'part_warranty_expiring')
                    ->where('vehicle_id', $vehicle->id)
                    ->whereIn('status', ['new', 'seen'])
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $plate = $vehicle->plate;

                $this->createAlert('part_warranty_expiring', [
                    'vehicle_id' => $vehicle->id,
                    'title'      => "Garantie pièce expirante : {$part->part_name} sur {$plate}",
                    'message'    => "Garantie pièce expirante : {$part->part_name} sur {$plate},"
                                  . " garantie jusqu'au {$part->warranty_expiry->format('d/m/Y')}.",
                    'due_date'   => $part->warranty_expiry,
                    'severity'   => 'warning',
                    'channels'   => ['in_app', 'email'],
                ]);
            });
    }

    /**
     * Cherche les réparations en cours dont l'envoi au garage remonte à plus de N jours
     * sans qu'aucun retour n'ait été enregistré.
     *
     * Statuts surveillés : sent, diagnosing, repairing, waiting_parts
     * Seuil configurable : config('miensafleet.repair_overdue_days', 7)
     *
     * Crée une alerte 'repair_overdue' (severity = warning) par réparation en retard.
     * Déduplique : même type + même vehicle_id + statut non-traité.
     */
    public function checkRepairsOverdue(): void
    {
        $overdueDays = (int) config('miensafleet.repair_overdue_days', 7);
        $cutoff      = now()->subDays($overdueDays);

        Repair::with(['vehicle', 'garage'])
            ->whereIn('status', ['sent', 'diagnosing', 'repairing', 'waiting_parts'])
            ->where('datetime_sent', '<', $cutoff)
            ->each(function (Repair $repair) use ($overdueDays) {
                $vehicle = $repair->vehicle;
                if (! $vehicle) {
                    return;
                }

                // Déduplication : même type + même vehicle_id + statut non-traité
                $alreadyExists = Alert::where('type', 'repair_overdue')
                    ->where('vehicle_id', $vehicle->id)
                    ->whereIn('status', ['new', 'seen'])
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                $plate  = $vehicle->plate;
                $garage = $repair->garage;
                $garageLabel = $garage?->name ?? 'garage inconnu';
                $days   = (int) now()->diffInDays($repair->datetime_sent);

                $this->createAlert('repair_overdue', [
                    'vehicle_id' => $vehicle->id,
                    'title'      => "Réparation en retard — {$plate}",
                    'message'    => "Véhicule {$plate} au garage {$garageLabel}"
                                  . " depuis {$days} jours sans retour"
                                  . " (seuil : {$overdueDays} jours).",
                    'severity'   => 'warning',
                    'channels'   => ['in_app', 'email'],
                ]);
            });
    }

    // ──────────────────────────────────────────────────────────────────────
    // Vidange
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Vérifie les prochaines dates de vidange saisies dans les fiches de contrôle.
     *
     * Pour chaque véhicule, on prend la fiche de contrôle la plus récente
     * ayant un `oil_change_next_date` renseigné.
     *
     * Seuils d'alerte :
     *   - oil_change_next_date dans [0, 15] jours → warning  (oil_change_due)
     *   - oil_change_next_date < aujourd'hui       → critical (oil_change_overdue)
     *
     * Anti-doublon : ne recrée pas si une alerte oil_change_due|overdue
     * non traitée existe déjà pour ce vehicle_id.
     */
    public function checkOilChangesDue(): void
    {
        $today       = now()->startOfDay();
        $warnHorizon = $today->copy()->addDays(15);

        // Sous-requête : ID de la fiche la plus récente par véhicule
        // ayant oil_change_next_date renseigné
        $latestIds = Inspection::whereNotNull('oil_change_next_date')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('vehicle_id')
            ->pluck('id');

        Inspection::whereIn('id', $latestIds)
            ->with('vehicle')
            ->each(function (Inspection $inspection) use ($today, $warnHorizon) {
                $vehicle   = $inspection->vehicle;
                $nextDate  = $inspection->oil_change_next_date;

                if (! $vehicle || ! $nextDate) {
                    return;
                }

                $nextDay  = $nextDate->copy()->startOfDay();
                $overdue  = $nextDay->lt($today);
                $dueSoon  = ! $overdue && $nextDay->lte($warnHorizon);

                if (! $overdue && ! $dueSoon) {
                    return; // Pas encore dans l'horizon d'alerte
                }

                $alertType = $overdue ? 'oil_change_overdue' : 'oil_change_due';
                $severity  = $overdue ? 'critical' : 'warning';

                // Anti-doublon : même type + même vehicle_id + non traité
                $exists = Alert::whereIn('type', ['oil_change_due', 'oil_change_overdue'])
                    ->where('vehicle_id', $vehicle->id)
                    ->whereIn('status', ['new', 'seen'])
                    ->exists();

                if ($exists) {
                    return;
                }

                $plate     = $vehicle->plate;
                $brand     = "{$vehicle->brand} {$vehicle->model}";
                $dateLabel = $nextDate->format('d/m/Y');
                $daysLeft  = (int) $today->diffInDays($nextDay, false);

                if ($overdue) {
                    $title   = "Vidange dépassée — {$plate}";
                    $message = "La vidange du véhicule {$brand} ({$plate}) était prévue le {$dateLabel}"
                             . " — dépassée de " . abs($daysLeft) . " jour(s).";
                } else {
                    $title   = "Vidange à prévoir — {$plate}";
                    $message = "La vidange du véhicule {$brand} ({$plate}) est prévue le {$dateLabel}"
                             . " (dans {$daysLeft} jour(s)).";
                    if ($inspection->oil_change_next_km) {
                        $message .= " Seuil km : " . number_format($inspection->oil_change_next_km, 0, ',', ' ') . " km.";
                    }
                }

                $this->createAlert($alertType, [
                    'vehicle_id'     => $vehicle->id,
                    'title'          => $title,
                    'message'        => $message,
                    'due_date'       => $nextDate,
                    'days_remaining' => $daysLeft,
                    'severity'       => $severity,
                    'channels'       => ['in_app', 'email'],
                ]);
            });
    }

    // ──────────────────────────────────────────────────────────────────────
    // Création et envoi
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Vérifie l'anti-doublon, puis crée et persiste une alerte.
     *
     * Si une alerte du même type avec les mêmes clés FK et un statut
     * non-traité (new, seen) existe déjà, retourne l'alerte existante
     * sans créer de doublon ni re-dispatcher.
     *
     * @param string $type  Type d'alerte (doit correspondre à l'enum alerts.type)
     * @param array{
     *   vehicle_id?:     int,
     *   driver_id?:      int,
     *   user_id?:        int,
     *   request_id?:     int,
     *   infraction_id?:  int,
     *   title:           string,
     *   message:         string,
     *   due_date?:       \Carbon\Carbon|\DateTimeInterface|string|null,
     *   days_remaining?: int|null,
     *   severity:        string,
     *   channels:        string[],
     * } $data
     */
    public function createAlert(string $type, array $data): Alert
    {
        // ── Anti-doublon ────────────────────────────────────────────────────
        // Construit le critère sur toutes les FKs présentes dans $data.
        $query = Alert::where('type', $type)->whereIn('status', ['new', 'seen']);

        foreach (['vehicle_id', 'driver_id', 'user_id', 'request_id', 'infraction_id'] as $fk) {
            if (isset($data[$fk])) {
                $query->where($fk, $data[$fk]);
            }
        }

        $existing = $query->first();

        if ($existing !== null) {
            return $existing;
        }

        // ── Création ────────────────────────────────────────────────────────
        $alert = Alert::create(array_merge([
            'type'           => $type,
            'severity'       => 'warning',
            'status'         => 'new',
            'send_failed'    => false,
            'channels'       => ['in_app'],
            'days_remaining' => null,
            'due_date'       => null,
        ], $data));

        $this->dispatchAlert($alert);

        return $alert;
    }

    /**
     * Dispatche SendAlertNotificationJob pour l'alerte donnée.
     *
     * Le job reçoit l'alerte complète avec ses canaux (`$alert->channels`)
     * et se charge de l'envoi effectif (email, sms, in_app…).
     *
     * En cas d'échec du dispatch (queue indisponible, sérialisation…) :
     *   - `send_failed` passe à true via DB::table() (sans déclencher d'observer)
     *   - L'erreur est loggée dans le canal `daily`
     *
     * En cas de succès : loggué via Spatie Activitylog dans le log 'alerts'.
     */
    public function dispatchAlert(Alert $alert): void
    {
        try {
            SendAlertNotificationJob::dispatch($alert);

            activity('alerts')->log('Alert dispatched: ' . $alert->type);
        } catch (\Throwable $e) {
            Log::error("AlertService: échec du dispatch de l'alerte #{$alert->id}.", [
                'type'  => $alert->type,
                'error' => $e->getMessage(),
            ]);

            // Sans Eloquent pour éviter tout observer loop
            DB::table('alerts')
                ->where('id', $alert->id)
                ->update(['send_failed' => true]);
        }
    }
}
