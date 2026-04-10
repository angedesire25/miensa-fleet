<?php

namespace App\Services;

use App\Exceptions\AssignmentConflictException;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssignmentService
{
    /**
     * Types de documents obligatoires que tout chauffeur doit posséder
     * (non-expiré, non-manquant) pour être éligible à une affectation.
     */
    private const MANDATORY_DOCUMENT_TYPES = [
        'license',         // Permis de conduire (scan)
        'medical_fitness', // Visite médicale d'aptitude
    ];

    // ──────────────────────────────────────────────────────────────────────
    // Vérifications de conflits
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Vérifie si un véhicule est déjà affecté sur la plage donnée.
     *
     * Implémente la règle métier #2 :
     * "Un véhicule ne peut pas être dans 2 affectations simultanées."
     *
     * @param int      $vehicleId  Identifiant du véhicule à tester
     * @param Carbon   $start      Début du créneau souhaité
     * @param Carbon   $end        Fin du créneau souhaité (datetime_end_planned)
     * @param int|null $excludeId  Exclure une affectation existante (utile pour modification)
     */
    public function checkVehicleConflict(
        int     $vehicleId,
        Carbon  $start,
        Carbon  $end,
        ?int    $excludeId = null
    ): bool {
        return Assignment::conflicting($vehicleId, null, $start, $end, $excludeId)->exists();
    }

    /**
     * Vérifie si un chauffeur a déjà une affectation qui chevauche la plage donnée.
     *
     * Implémente la règle métier #3 :
     * "Un chauffeur ne peut pas avoir 2 affectations qui se chevauchent."
     *
     * @param int      $driverId   Identifiant du chauffeur à tester
     * @param Carbon   $start      Début du créneau souhaité
     * @param Carbon   $end        Fin du créneau souhaité
     * @param int|null $excludeId  Exclure une affectation existante (utile pour modification)
     */
    public function checkDriverConflict(
        int     $driverId,
        Carbon  $start,
        Carbon  $end,
        ?int    $excludeId = null
    ): bool {
        return Assignment::conflicting(null, $driverId, $start, $end, $excludeId)->exists();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Éligibilité du chauffeur
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Vérifie l'éligibilité complète d'un chauffeur à conduire un véhicule donné.
     *
     * Implémente la règle métier #4 :
     * "Un chauffeur bloqué (permis expiré, visite médicale expirée, statut
     * suspended/terminated) ne peut PAS être affecté."
     *
     * Cinq conditions sont vérifiées indépendamment :
     *   1. Statut du chauffeur = active
     *   2. Permis de conduire non expiré (license_expiry_date)
     *   3. Catégorie du permis compatible avec la catégorie requise par le véhicule
     *   4. Visite médicale d'aptitude valide (non expirée, résultat ≠ unfit)
     *   5. Documents obligatoires présents (non-manquants)
     *
     * @param int $driverId   Identifiant du chauffeur
     * @param int $vehicleId  Identifiant du véhicule ciblé
     *
     * @return array{eligible: bool, reasons: string[]}
     */
    public function checkDriverEligibility(int $driverId, int $vehicleId): array
    {
        $driver  = Driver::with('documents')->findOrFail($driverId);
        $vehicle = Vehicle::findOrFail($vehicleId);

        $eligible = true;
        $reasons  = [];

        // ── 1. Statut du chauffeur ─────────────────────────────────────────
        if ($driver->status !== 'active') {
            $eligible  = false;
            $reasons[] = match ($driver->status) {
                'suspended'  => 'Chauffeur suspendu' . ($driver->suspension_reason ? " : {$driver->suspension_reason}" : '.'),
                'on_leave'   => 'Chauffeur actuellement en congé.',
                'terminated' => 'Chauffeur licencié.',
                default      => "Statut du chauffeur invalide ({$driver->status}).",
            };
        }

        // ── 2. Validité du permis de conduire ──────────────────────────────
        if ($driver->license_expiry_date === null || $driver->license_expiry_date->isPast()) {
            $eligible  = false;
            $expiry    = $driver->license_expiry_date?->format('d/m/Y') ?? 'non renseignée';
            $reasons[] = "Permis de conduire expiré (expiration : {$expiry}).";
        }

        // ── 3. Catégorie du permis vs catégorie requise par le véhicule ────
        $requiredCategory = $vehicle->license_category;
        $driverCategories = $driver->license_categories ?? [];

        if (! in_array($requiredCategory, $driverCategories, true)) {
            $eligible  = false;
            $held      = empty($driverCategories) ? 'aucune' : implode(', ', $driverCategories);
            $reasons[] = "Catégorie de permis insuffisante : le véhicule exige '{$requiredCategory}'"
                       . " (chauffeur possède : {$held}).";
        }

        // ── 4. Visite médicale d'aptitude valide ───────────────────────────
        $medicalDoc = $driver->documents()
            ->where('type', 'medical_fitness')
            ->whereNotIn('status', ['expired', 'missing'])
            ->orderByDesc('issue_date')
            ->first();

        if ($medicalDoc === null) {
            $eligible  = false;
            $reasons[] = "Aucune visite médicale d'aptitude valide enregistrée.";
        } elseif ($medicalDoc->medical_result === 'unfit') {
            $eligible  = false;
            $reasons[] = "Le chauffeur a été déclaré inapte lors de sa dernière visite médicale.";
        }

        // ── 5. Documents obligatoires présents ─────────────────────────────
        $missingDocs = [];

        foreach (self::MANDATORY_DOCUMENT_TYPES as $docType) {
            $present = $driver->documents()
                ->where('type', $docType)
                ->where('status', '!=', 'missing')
                ->exists();

            if (! $present) {
                $missingDocs[] = $docType;
            }
        }

        if (! empty($missingDocs)) {
            $docLabels = [
                'license'        => 'Permis de conduire',
                'medical_fitness'=> 'Visite médicale d\'aptitude',
            ];
            $translated = array_map(fn($t) => $docLabels[$t] ?? $t, $missingDocs);
            $eligible   = false;
            $reasons[]  = 'Documents obligatoires manquants : ' . implode(', ', $translated) . '.';
        }

        return ['eligible' => $eligible, 'reasons' => $reasons];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Création et clôture d'affectation
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Crée une affectation après validation complète des règles métier.
     *
     * Exécute les 3 vérifications dans une transaction DB avec verrou
     * pessimiste sur le véhicule pour éviter les doublons en cas de
     * requêtes concurrentes.
     *
     * @param array{
     *   driver_id:            int,
     *   vehicle_id:           int,
     *   type:                 string,
     *   datetime_start:       Carbon|string,
     *   datetime_end_planned: Carbon|string,
     *   mission?:             string,
     *   destination?:         string,
     *   km_start?:            int,
     *   created_by?:          int,
     * } $data
     *
     * @throws AssignmentConflictException  Si l'une des trois vérifications échoue
     */
    public function createAssignment(array $data): Assignment
    {
        return DB::transaction(function () use ($data) {
            $start = Carbon::parse($data['datetime_start']);
            $end   = Carbon::parse($data['datetime_end_planned']);

            // Verrou pessimiste sur la ligne véhicule pour éviter les race conditions
            // entre la vérification de conflit et l'insertion.
            $vehicle = Vehicle::lockForUpdate()->findOrFail($data['vehicle_id']);

            // ── Conflit véhicule ────────────────────────────────────────────
            if ($this->checkVehicleConflict($vehicle->id, $start, $end)) {
                throw new AssignmentConflictException(
                    'vehicle',
                    ["Le véhicule {$vehicle->plate} est déjà affecté sur ce créneau."]
                );
            }

            // ── Conflit chauffeur (uniquement si driver_id renseigné) ──────
            if (!empty($data['driver_id'])) {
                if ($this->checkDriverConflict($data['driver_id'], $start, $end)) {
                    $driver = Driver::find($data['driver_id']);
                    $name   = $driver?->full_name ?? "ID {$data['driver_id']}";
                    throw new AssignmentConflictException(
                        'driver',
                        ["Le chauffeur {$name} a déjà une affectation qui chevauche ce créneau."]
                    );
                }

                // ── Éligibilité du chauffeur ────────────────────────────────
                $eligibility = $this->checkDriverEligibility($data['driver_id'], $vehicle->id);

                if (! $eligibility['eligible']) {
                    throw new AssignmentConflictException('eligibility', $eligibility['reasons']);
                }
            }

            return Assignment::create($data);
        });
    }

    /**
     * Clôture une affectation en cours ou confirmée.
     *
     * Enregistre le kilométrage de retour, l'état du véhicule et passe
     * le statut à 'completed'. L'AssignmentObserver prend alors le relais
     * pour mettre à jour le véhicule et les statistiques du chauffeur.
     *
     * Règle métier #5 : km_start doit avoir été saisi avant cette étape
     * (enforced lors du passage en in_progress, pas ici).
     *
     * @param string $conditionEnd  'good' | 'fair' | 'poor'
     *
     * @throws InvalidArgumentException  Si le statut ou le km_end est invalide
     */
    public function closeAssignment(
        Assignment $assignment,
        int        $kmEnd,
        string     $conditionEnd,
        ?string    $notes = null
    ): Assignment {
        if (! in_array($assignment->status, ['in_progress', 'confirmed'], true)) {
            throw new InvalidArgumentException(
                "Impossible de clôturer l'affectation #{$assignment->id}"
                . " dont le statut est '{$assignment->status}'."
                . " Statuts acceptés : in_progress, confirmed."
            );
        }

        if ($assignment->km_start !== null && $kmEnd < $assignment->km_start) {
            throw new InvalidArgumentException(
                "Le kilométrage de retour ({$kmEnd} km) ne peut pas être"
                . " inférieur au kilométrage de départ ({$assignment->km_start} km)."
            );
        }

        $assignment->update([
            'status'              => 'completed',
            'km_end'              => $kmEnd,
            'condition_end'       => $conditionEnd,
            'condition_end_notes' => $notes,
            'datetime_end_actual' => now(),
        ]);

        // Recharge le modèle pour récupérer km_total (colonne virtuelle MySQL)
        return $assignment->fresh();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Recherche de disponibilités
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Retourne les véhicules disponibles pour une plage horaire donnée.
     *
     * Un véhicule est disponible si :
     *   - Son statut n'est pas 'sold' ni 'retired' (scopeActive)
     *   - Il n'a aucune affectation active (planned/confirmed/in_progress) qui chevauche la plage
     *   - Il n'a aucune demande de véhicule active (approved/confirmed/in_progress) qui chevauche la plage
     *
     * @param Carbon      $start  Début du créneau souhaité
     * @param Carbon      $end    Fin du créneau souhaité
     * @param string|null $type   Filtrer par type de véhicule (city, sedan, suv…)
     */
    public function getAvailableVehicles(Carbon $start, Carbon $end, ?string $type = null): Collection
    {
        return Vehicle::active()
            ->when($type, fn($q) => $q->where('vehicle_type', $type))
            // Exclure les véhicules avec une affectation conflictuelle
            ->whereDoesntHave('assignments', function ($q) use ($start, $end) {
                $q->whereIn('status', ['planned', 'confirmed', 'in_progress'])
                  ->where('datetime_start', '<', $end)
                  ->where('datetime_end_planned', '>', $start);
            })
            // Exclure les véhicules réservés par une demande conflictuelle
            ->whereDoesntHave('vehicleRequests', function ($q) use ($start, $end) {
                $q->whereIn('status', ['approved', 'confirmed', 'in_progress'])
                  ->where('datetime_start', '<', $end)
                  ->where('datetime_end_planned', '>', $start);
            })
            ->with('currentDriver')
            ->orderBy('vehicle_type')
            ->orderBy('brand')
            ->get();
    }

    /**
     * Retourne les chauffeurs disponibles pour une plage horaire donnée.
     *
     * Un chauffeur est disponible si :
     *   - Son statut est 'active'
     *   - Son permis n'est pas expiré
     *   - Il n'a aucune affectation active (planned/confirmed/in_progress) qui chevauche la plage
     *
     * Note : la vérification complète d'éligibilité (visite médicale, catégorie
     * de permis) est faite dans checkDriverEligibility() au moment de la création.
     * Cette méthode est destinée à peupler un sélecteur de chauffeur dans l'UI.
     *
     * @param Carbon $start  Début du créneau souhaité
     * @param Carbon $end    Fin du créneau souhaité
     */
    public function getAvailableDrivers(Carbon $start, Carbon $end): Collection
    {
        return Driver::available($start, $end)
            ->with('preferredVehicle')
            ->orderBy('full_name')
            ->get();
    }
}
