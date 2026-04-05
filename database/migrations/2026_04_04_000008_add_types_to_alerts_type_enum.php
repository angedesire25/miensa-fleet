<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Étend l'enum alerts.type avec les types liés aux pièces et aux réparations.
 *
 * Nouveaux types ajoutés :
 *   part_warranty_expiring       → garantie pièce active expirant sous 30 jours
 *   part_failure_under_warranty  → pièce tombée en panne encore sous garantie
 *   repair_recurrence            → même panne détectée après une réparation récente
 *   repair_overdue               → véhicule au garage depuis plus de N jours sans retour
 *
 * MySQL ne supporte pas l'ajout incrémental de valeurs d'enum :
 * l'ALTER TABLE doit lister TOUTES les valeurs (existantes + nouvelles).
 */
return new class extends Migration
{
    /** Liste complète des valeurs après migration. */
    private const ALL_TYPES = [
        // Véhicule — documents
        'insurance_expiring',
        'insurance_expired',
        'technical_control_expiring',
        'technical_control_expired',
        'oil_change_due',
        'vehicle_anomaly',
        'vehicle_not_inspected',

        // Chauffeur
        'license_expiring',
        'license_expired',
        'medical_fitness_due',
        'driver_document_missing',
        'contract_ending',

        // Demandes / retours
        'request_pending_timeout',
        'vehicle_return_overdue',

        // Infractions
        'new_infraction',
        'fine_unpaid',

        // Pièces (nouveaux)
        'part_warranty_expiring',
        'part_failure_under_warranty',

        // Réparations (nouveaux)
        'repair_recurrence',
        'repair_overdue',
    ];

    /** Valeurs présentes avant cette migration (pour le rollback). */
    private const ORIGINAL_TYPES = [
        'insurance_expiring',
        'insurance_expired',
        'technical_control_expiring',
        'technical_control_expired',
        'oil_change_due',
        'vehicle_anomaly',
        'vehicle_not_inspected',
        'license_expiring',
        'license_expired',
        'medical_fitness_due',
        'driver_document_missing',
        'contract_ending',
        'request_pending_timeout',
        'vehicle_return_overdue',
        'new_infraction',
        'fine_unpaid',
    ];

    public function up(): void
    {
        $values = implode(', ', array_map(fn(string $v) => "'{$v}'", self::ALL_TYPES));

        DB::statement("ALTER TABLE alerts MODIFY COLUMN type ENUM({$values}) NOT NULL");
    }

    public function down(): void
    {
        $values = implode(', ', array_map(fn(string $v) => "'{$v}'", self::ORIGINAL_TYPES));

        DB::statement("ALTER TABLE alerts MODIFY COLUMN type ENUM({$values}) NOT NULL");
    }
};
