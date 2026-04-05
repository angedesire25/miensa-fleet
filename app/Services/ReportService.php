<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\DriverDocument;
use App\Models\Infraction;
use App\Models\Inspection;
use App\Models\TripLog;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Models\VehicleRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // ──────────────────────────────────────────────────────────────────────
    // Rapport véhicule
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Génère le rapport d'activité d'un véhicule sur une période donnée.
     *
     * Toutes les métriques de période utilisent datetime_start (assignments,
     * requests, trip_logs) ou datetime_occurred (infractions) ou inspected_at
     * comme point d'ancrage dans la fenêtre [$from, $to].
     *
     * Les documents et les données du véhicule sont retournés sans filtre
     * de période (état actuel du parc documentaire).
     *
     * @param int    $vehicleId  Identifiant du véhicule
     * @param Carbon $from       Début de la période (inclus)
     * @param Carbon $to         Fin de la période (inclus)
     *
     * @return array{
     *   vehicle:           Vehicle,
     *   period:            array{from: Carbon, to: Carbon},
     *   total_assignments: int,
     *   total_km:          int,
     *   total_trips:       int,
     *   total_infractions: array{count: int, total_amount: float},
     *   inspections:       \Illuminate\Database\Eloquent\Collection,
     *   documents:         \Illuminate\Database\Eloquent\Collection,
     *   requests:          \Illuminate\Database\Eloquent\Collection,
     * }
     */
    public function vehicleReport(int $vehicleId, Carbon $from, Carbon $to): array
    {
        // Chargement du véhicule avec ses relations de base
        $vehicle = Vehicle::with(['documents', 'currentDriver'])->findOrFail($vehicleId);

        // ── Nombre total d'affectations sur la période ──────────────────────
        // Toutes les affectations dont le départ se situe dans la fenêtre,
        // quel que soit leur statut.
        $totalAssignments = Assignment::where('vehicle_id', $vehicleId)
            ->whereBetween('datetime_start', [$from, $to])
            ->count();

        // ── Kilométrage total parcouru sur la période ───────────────────────
        // Somme des km_total des affectations complétées (colonne virtuelle MySQL).
        $kmAssignments = Assignment::where('vehicle_id', $vehicleId)
            ->where('status', 'completed')
            ->whereBetween('datetime_start', [$from, $to])
            ->sum('km_total');

        // Idem pour les demandes de véhicule (collaborateurs non-chauffeurs).
        $kmRequests = VehicleRequest::where('vehicle_id', $vehicleId)
            ->where('status', 'completed')
            ->whereBetween('datetime_start', [$from, $to])
            ->sum('km_total');

        $totalKm = (int) ($kmAssignments + $kmRequests);

        // ── Nombre de trajets dans le carnet de bord ───────────────────────
        $totalTrips = TripLog::where('vehicle_id', $vehicleId)
            ->whereBetween('datetime_start', [$from, $to])
            ->count();

        // ── Infractions : nombre et montant total ───────────────────────────
        $infractionsBase = Infraction::where('vehicle_id', $vehicleId)
            ->whereBetween('datetime_occurred', [$from, $to]);

        $totalInfractions = [
            'count'        => $infractionsBase->count(),
            'total_amount' => (float) (clone $infractionsBase)->sum('fine_amount'),
        ];

        // ── Fiches de contrôle sur la période (avec signalement anomalies) ──
        // Inclut : type de fiche, kilométrage au contrôle, présence d'anomalie
        // critique, observations générales. Triées chronologiquement.
        $inspections = Inspection::where('vehicle_id', $vehicleId)
            ->whereBetween('inspected_at', [$from, $to])
            ->with('inspector')
            ->orderBy('inspected_at')
            ->get([
                'id',
                'inspected_at',
                'inspection_type',
                'km',
                'has_critical_issue',
                'oil_level',
                'brakes_status',
                'lights_status',
                'general_observations',
                'inspector_id',
            ]);

        // ── Documents du véhicule (état actuel, sans filtre de période) ─────
        // Retourne tous les documents administratifs avec leur statut
        // et leur date d'expiration pour permettre un diagnostic documentaire.
        $documents = $vehicle->documents()
            ->orderBy('type')
            ->get([
                'id',
                'type',
                'document_number',
                'issue_date',
                'expiry_date',
                'status',
            ]);

        // ── Demandes de véhicule sur la période ─────────────────────────────
        $requests = VehicleRequest::where('vehicle_id', $vehicleId)
            ->whereBetween('datetime_start', [$from, $to])
            ->with('requester')
            ->orderBy('datetime_start')
            ->get([
                'id',
                'requester_id',
                'datetime_start',
                'datetime_end_planned',
                'datetime_end_actual',
                'destination',
                'purpose',
                'status',
                'km_start',
                'km_end',
                'km_total',
            ]);

        return [
            'vehicle'           => $vehicle,
            'period'            => ['from' => $from, 'to' => $to],
            'total_assignments' => $totalAssignments,
            'total_km'          => $totalKm,
            'total_trips'       => $totalTrips,
            'total_infractions' => $totalInfractions,
            'inspections'       => $inspections,
            'documents'         => $documents,
            'requests'          => $requests,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Rapport chauffeur
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Génère le rapport d'activité d'un chauffeur sur une période donnée.
     *
     * Le kilométrage total n'inclut que les affectations complétées
     * (km_total NULL tant que km_end n'est pas saisi).
     *
     * Les documents sont retournés sans filtre de période (état actuel),
     * avec le statut et les alertes d'expiration.
     *
     * @param int    $driverId  Identifiant du chauffeur
     * @param Carbon $from      Début de la période (inclus)
     * @param Carbon $to        Fin de la période (inclus)
     *
     * @return array{
     *   driver:             Driver,
     *   period:             array{from: Carbon, to: Carbon},
     *   total_assignments:  int,
     *   total_km:           int,
     *   total_trips:        int,
     *   infractions:        \Illuminate\Database\Eloquent\Collection,
     *   documents:          \Illuminate\Database\Eloquent\Collection,
     *   assignments_detail: \Illuminate\Database\Eloquent\Collection,
     * }
     */
    public function driverReport(int $driverId, Carbon $from, Carbon $to): array
    {
        // Chargement du chauffeur avec ses documents
        $driver = Driver::with('documents')->findOrFail($driverId);

        // ── Nombre total d'affectations sur la période ──────────────────────
        $totalAssignments = Assignment::where('driver_id', $driverId)
            ->whereBetween('datetime_start', [$from, $to])
            ->count();

        // ── Kilométrage parcouru : uniquement les affectations complétées ───
        // Les affectations in_progress ou planned n'ont pas encore km_end saisi.
        $totalKm = (int) Assignment::where('driver_id', $driverId)
            ->where('status', 'completed')
            ->whereBetween('datetime_start', [$from, $to])
            ->sum('km_total');

        // ── Nombre de trajets dans le carnet de bord ───────────────────────
        $totalTrips = TripLog::where('driver_id', $driverId)
            ->whereBetween('datetime_start', [$from, $to])
            ->count();

        // ── Infractions sur la période avec montants et statut paiement ─────
        // Triées chronologiquement pour faciliter la lecture du rapport.
        $infractions = Infraction::where('driver_id', $driverId)
            ->whereBetween('datetime_occurred', [$from, $to])
            ->with('vehicle')
            ->orderBy('datetime_occurred')
            ->get([
                'id',
                'vehicle_id',
                'datetime_occurred',
                'type',
                'location',
                'fine_amount',
                'payment_status',
                'imputation',
                'status',
            ]);

        // ── Documents du chauffeur (état actuel) avec alertes d'expiration ──
        // Inclut le résultat médical pour identifier un éventuel statut inapte.
        $documents = $driver->documents()
            ->orderBy('type')
            ->get([
                'id',
                'type',
                'document_number',
                'issue_date',
                'expiry_date',
                'status',
                'medical_result',
                'next_check_date',
            ]);

        // ── Liste chronologique complète des affectations ───────────────────
        // Détail complet pour permettre la traçabilité mission par mission.
        $assignmentsDetail = Assignment::where('driver_id', $driverId)
            ->whereBetween('datetime_start', [$from, $to])
            ->with('vehicle')
            ->orderBy('datetime_start')
            ->get([
                'id',
                'vehicle_id',
                'type',
                'datetime_start',
                'datetime_end_planned',
                'datetime_end_actual',
                'mission',
                'destination',
                'km_start',
                'km_end',
                'km_total',
                'status',
                'condition_end',
            ]);

        return [
            'driver'             => $driver,
            'period'             => ['from' => $from, 'to' => $to],
            'total_assignments'  => $totalAssignments,
            'total_km'           => $totalKm,
            'total_trips'        => $totalTrips,
            'infractions'        => $infractions,
            'documents'          => $documents,
            'assignments_detail' => $assignmentsDetail,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Tableau de bord global de la flotte
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Synthèse globale de la flotte sur une période donnée.
     *
     * Aggrège les métriques clés en un seul appel pour alimenter
     * le tableau de bord principal (dashboard).
     *
     * @param Carbon $from  Début de la période (inclus)
     * @param Carbon $to    Fin de la période (inclus)
     *
     * @return array{
     *   period:           array{from: Carbon, to: Carbon},
     *   total_vehicles:   array<string, int>,
     *   total_drivers:    array<string, int>,
     *   total_assignments: array{completed: int, in_progress: int, planned: int},
     *   total_km_fleet:   int,
     *   total_infractions: array{count: int, total_amount: float},
     *   total_requests:   array<string, int>,
     *   alerts_summary:   array,
     *   top_drivers_km:   \Illuminate\Support\Collection,
     *   top_vehicles_used: \Illuminate\Support\Collection,
     * }
     */
    public function fleetSummary(Carbon $from, Carbon $to): array
    {
        // ── Répartition des véhicules par statut ────────────────────────────
        // Récupère uniquement les véhicules non supprimés (soft delete exclu).
        $vehiclesByStatus = Vehicle::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        // ── Répartition des chauffeurs par statut ───────────────────────────
        $driversByStatus = Driver::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        // ── Affectations sur la période, ventilées par statut cible ─────────
        $assignmentCounts = Assignment::whereBetween('datetime_start', [$from, $to])
            ->whereIn('status', ['completed', 'in_progress', 'planned'])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalAssignments = [
            'completed'   => (int) ($assignmentCounts['completed']   ?? 0),
            'in_progress' => (int) ($assignmentCounts['in_progress'] ?? 0),
            'planned'     => (int) ($assignmentCounts['planned']     ?? 0),
        ];

        // ── Kilométrage total de la flotte sur la période ───────────────────
        // Somme des km_total des affectations complétées (colonne virtuelle MySQL)
        // augmentée de ceux des demandes de véhicule complétées.
        $kmAssignments = Assignment::where('status', 'completed')
            ->whereBetween('datetime_start', [$from, $to])
            ->sum('km_total');

        $kmRequests = VehicleRequest::where('status', 'completed')
            ->whereBetween('datetime_start', [$from, $to])
            ->sum('km_total');

        $totalKmFleet = (int) ($kmAssignments + $kmRequests);

        // ── Infractions sur la période ──────────────────────────────────────
        $infractionsBase  = Infraction::whereBetween('datetime_occurred', [$from, $to]);
        $totalInfractions = [
            'count'        => $infractionsBase->count(),
            'total_amount' => (float) (clone $infractionsBase)->sum('fine_amount'),
        ];

        // ── Demandes de véhicule par statut ─────────────────────────────────
        $totalRequests = VehicleRequest::whereBetween('datetime_start', [$from, $to])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        // ── Synthèse des alertes par type et par sévérité ──────────────────
        // Comptage des alertes non-archivées, toutes périodes confondues,
        // pour refléter l'état actuel des alertes en cours.
        $alertsByType = Alert::select('type', DB::raw('COUNT(*) as total'))
            ->whereIn('status', ['new', 'seen'])
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();

        $alertsBySeverity = Alert::select('severity', DB::raw('COUNT(*) as total'))
            ->whereIn('status', ['new', 'seen'])
            ->groupBy('severity')
            ->pluck('total', 'severity')
            ->all();

        $alertsSummary = [
            'by_type'     => $alertsByType,
            'by_severity' => $alertsBySeverity,
            'total'       => array_sum($alertsByType),
        ];

        // ── Top 5 chauffeurs par kilométrage sur la période ─────────────────
        // Jointure sur assignments complétés pour sommer km_total par chauffeur.
        $topDriversKm = Driver::select(
                'drivers.id',
                'drivers.full_name',
                'drivers.matricule',
                DB::raw('COALESCE(SUM(a.km_total), 0) AS total_km'),
                DB::raw('COUNT(a.id) AS nb_assignments')
            )
            ->join('assignments AS a', function ($join) use ($from, $to) {
                $join->on('a.driver_id', '=', 'drivers.id')
                     ->where('a.status', 'completed')
                     ->whereBetween('a.datetime_start', [$from, $to])
                     ->whereNull('a.deleted_at');
            })
            ->groupBy('drivers.id', 'drivers.full_name', 'drivers.matricule')
            ->orderByDesc('total_km')
            ->limit(5)
            ->get();

        // ── Top 5 véhicules les plus utilisés sur la période ────────────────
        // Utilisation = nombre d'affectations complétées ou terminées,
        // km_total cumulé et nombre de missions.
        $topVehiclesUsed = Vehicle::select(
                'vehicles.id',
                'vehicles.brand',
                'vehicles.model',
                'vehicles.plate',
                DB::raw('COALESCE(SUM(a.km_total), 0) AS total_km'),
                DB::raw('COUNT(a.id) AS nb_assignments')
            )
            ->join('assignments AS a', function ($join) use ($from, $to) {
                $join->on('a.vehicle_id', '=', 'vehicles.id')
                     ->where('a.status', 'completed')
                     ->whereBetween('a.datetime_start', [$from, $to])
                     ->whereNull('a.deleted_at');
            })
            ->groupBy('vehicles.id', 'vehicles.brand', 'vehicles.model', 'vehicles.plate')
            ->orderByDesc('nb_assignments')
            ->limit(5)
            ->get();

        return [
            'period'            => ['from' => $from, 'to' => $to],
            'total_vehicles'    => $vehiclesByStatus,
            'total_drivers'     => $driversByStatus,
            'total_assignments' => $totalAssignments,
            'total_km_fleet'    => $totalKmFleet,
            'total_infractions' => $totalInfractions,
            'total_requests'    => $totalRequests,
            'alerts_summary'    => $alertsSummary,
            'top_drivers_km'    => $topDriversKm,
            'top_vehicles_used' => $topVehiclesUsed,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Documents expirant prochainement
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Liste tous les documents (véhicules et chauffeurs) dont la date
     * d'expiration tombe dans les $days prochains jours.
     *
     * Résultat groupé par véhicule / chauffeur pour faciliter l'affichage
     * dans les vues de gestion documentaire et les exports.
     *
     * @param int $days  Horizon en jours (défaut : 30)
     *
     * @return array{
     *   vehicle_documents: \Illuminate\Support\Collection,
     *   driver_documents:  \Illuminate\Support\Collection,
     * }
     */
    public function documentsExpiring(int $days = 30): array
    {
        $horizon = now()->addDays($days)->endOfDay();
        $today   = now()->startOfDay();

        // ── Documents véhicules expirant dans $days jours ───────────────────
        // Groupés par véhicule ; chaque entrée contient les métadonnées
        // nécessaires à une action corrective (renouvellement, mise en demeure…).
        $vehicleDocuments = VehicleDocument::with('vehicle:id,brand,model,plate')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$today, $horizon])
            ->orderBy('expiry_date')
            ->get([
                'id',
                'vehicle_id',
                'type',
                'document_number',
                'expiry_date',
                'status',
            ])
            ->map(function (VehicleDocument $doc) {
                return [
                    'vehicle'       => $doc->vehicle,
                    'type'          => $doc->type,
                    'document_number' => $doc->document_number,
                    'expiry_date'   => $doc->expiry_date,
                    'days_remaining' => $doc->daysUntilExpiry(),
                    'status'        => $doc->status,
                ];
            })
            // Regroupement par identifiant de véhicule pour la vue
            ->groupBy('vehicle.id');

        // ── Documents chauffeurs expirant dans $days jours ──────────────────
        $driverDocuments = DriverDocument::with('driver:id,matricule,full_name')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$today, $horizon])
            ->orderBy('expiry_date')
            ->get([
                'id',
                'driver_id',
                'type',
                'document_number',
                'expiry_date',
                'status',
                'medical_result',
            ])
            ->map(function (DriverDocument $doc) {
                return [
                    'driver'         => $doc->driver,
                    'type'           => $doc->type,
                    'document_number' => $doc->document_number,
                    'expiry_date'    => $doc->expiry_date,
                    'days_remaining' => $doc->daysUntilExpiry(),
                    'status'         => $doc->status,
                    'medical_result' => $doc->medical_result,
                ];
            })
            ->groupBy('driver.id');

        return [
            'vehicle_documents' => $vehicleDocuments,
            'driver_documents'  => $driverDocuments,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Rapport infractions
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Rapport analytique des infractions sur une période donnée.
     *
     * Produit les indicateurs clés pour le suivi financier et disciplinaire :
     * répartition par type, conducteurs les plus impliqués, véhicules,
     * statut de paiement et imputation financière.
     *
     * @param Carbon $from  Début de la période (inclus)
     * @param Carbon $to    Fin de la période (inclus)
     *
     * @return array{
     *   period:                 array{from: Carbon, to: Carbon},
     *   total:                  int,
     *   total_amount:           float,
     *   by_type:                array<string, int>,
     *   by_driver:              \Illuminate\Support\Collection,
     *   by_vehicle:             \Illuminate\Support\Collection,
     *   payment_status_summary: array<string, int>,
     *   imputation_summary:     array<string, int>,
     * }
     */
    public function infractionReport(Carbon $from, Carbon $to): array
    {
        // Base de la période, réutilisée pour tous les sous-calculs
        $base = Infraction::whereBetween('datetime_occurred', [$from, $to]);

        // ── Totaux globaux ──────────────────────────────────────────────────
        $total       = $base->count();
        $totalAmount = (float) (clone $base)->sum('fine_amount');

        // ── Répartition par type d'infraction ──────────────────────────────
        // Permet d'identifier les comportements à risque les plus fréquents.
        $byType = (clone $base)
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->pluck('total', 'type')
            ->all();

        // ── Top conducteurs impliqués (chauffeurs professionnels) ───────────
        // Jointure sur drivers pour récupérer le nom ; les infractions sans
        // driver_id (conducteur = collaborateur ou inconnu) sont exclues.
        $byDriver = (clone $base)
            ->select(
                'infractions.driver_id',
                DB::raw('COUNT(infractions.id) AS nb_infractions'),
                DB::raw('COALESCE(SUM(infractions.fine_amount), 0) AS total_amount'),
                'drivers.full_name',
                'drivers.matricule'
            )
            ->join('drivers', 'drivers.id', '=', 'infractions.driver_id')
            ->whereNotNull('infractions.driver_id')
            ->groupBy('infractions.driver_id', 'drivers.full_name', 'drivers.matricule')
            ->orderByDesc('nb_infractions')
            ->limit(10)
            ->get();

        // ── Top véhicules impliqués ─────────────────────────────────────────
        $byVehicle = (clone $base)
            ->select(
                'infractions.vehicle_id',
                DB::raw('COUNT(infractions.id) AS nb_infractions'),
                DB::raw('COALESCE(SUM(infractions.fine_amount), 0) AS total_amount'),
                'vehicles.brand',
                'vehicles.model',
                'vehicles.plate'
            )
            ->join('vehicles', 'vehicles.id', '=', 'infractions.vehicle_id')
            ->groupBy('infractions.vehicle_id', 'vehicles.brand', 'vehicles.model', 'vehicles.plate')
            ->orderByDesc('nb_infractions')
            ->limit(10)
            ->get();

        // ── Répartition par statut de paiement ──────────────────────────────
        // Utile pour le suivi comptable et le recouvrement des amendes.
        $paymentStatusSummary = (clone $base)
            ->select('payment_status', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status')
            ->all();

        // ── Répartition par imputation financière ───────────────────────────
        // Permet de mesurer la part supportée par la société vs les conducteurs.
        $imputationSummary = (clone $base)
            ->select('imputation', DB::raw('COUNT(*) as total'))
            ->groupBy('imputation')
            ->pluck('total', 'imputation')
            ->all();

        return [
            'period'                 => ['from' => $from, 'to' => $to],
            'total'                  => $total,
            'total_amount'           => $totalAmount,
            'by_type'                => $byType,
            'by_driver'              => $byDriver,
            'by_vehicle'             => $byVehicle,
            'payment_status_summary' => $paymentStatusSummary,
            'imputation_summary'     => $imputationSummary,
        ];
    }
}
