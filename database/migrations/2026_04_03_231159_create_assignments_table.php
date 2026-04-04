<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `assignments` : affectations de véhicules aux chauffeurs professionnels.
     *
     * Représente le contrat opérationnel "ce chauffeur utilise ce véhicule
     * du datetime_start au datetime_end_planned".
     *
     * RÈGLE MÉTIER CRITIQUE :
     * Un chauffeur peut avoir PLUSIEURS affectations dans la même journée.
     * Ex: Véhicule A de 08h00 à 08h30, puis Véhicule B de 09h00 à 17h30.
     *
     * La détection de conflit se fait côté applicatif (AssignmentService)
     * sur les plages DATETIME avec précision à la minute.
     *
     * Contraintes :
     *   - Un véhicule ne peut pas être dans 2 affectations simultanées
     *   - Un chauffeur ne peut pas avoir 2 affectations qui se chevauchent
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();

            // Acteurs
            $table->foreignId('driver_id')
                  ->constrained('drivers')
                  ->restrictOnDelete();
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            // Type d'affectation
            $table->enum('type', [
                'mission',      // Mission ponctuelle (aller-retour tracé)
                'daily',        // Affectation journée complète
                'permanent',    // Affectation longue durée (véhicule dédié)
                'replacement',  // Remplacement temporaire d'un titulaire
                'trial',        // Période d'essai chauffeur
            ])->default('mission');

            // ── Plages horaires (DATETIME précis à la minute) ──
            $table->dateTime('datetime_start');
            $table->dateTime('datetime_end_planned');
            $table->dateTime('datetime_end_actual')->nullable(); // Saisi au retour

            // Mission
            $table->string('mission')->nullable();          // Motif / description
            $table->string('destination')->nullable();

            // Kilométrage
            $table->unsignedInteger('km_start')->nullable();
            $table->unsignedInteger('km_end')->nullable();
            $table->unsignedInteger('km_total')             // Calculé automatiquement
                  ->virtualAs('IF(km_end IS NOT NULL AND km_start IS NOT NULL, km_end - km_start, NULL)');

            // État du véhicule
            $table->enum('condition_start', ['good', 'fair', 'poor'])->nullable();
            $table->text('condition_start_notes')->nullable();
            $table->json('photos_start')->nullable();       // Photos au départ

            $table->enum('condition_end', ['good', 'fair', 'poor'])->nullable();
            $table->text('condition_end_notes')->nullable();
            $table->json('photos_end')->nullable();         // Photos au retour

            // Statut (machine à états)
            $table->enum('status', [
                'planned',      // Planifiée — pas encore commencée
                'confirmed',    // Validée par gestionnaire, bon de sortie généré
                'in_progress',  // En cours (km départ saisi)
                'completed',    // Terminée (km retour saisi)
                'cancelled',    // Annulée
            ])->default('planned');

            $table->text('cancellation_reason')->nullable();

            // Liens vers fiches de contrôle associées
            // FK vers inspections ajoutées dans add_foreign_keys_to_tables (dépendance circulaire)
            $table->unsignedBigInteger('inspection_start_id')->nullable();
            $table->unsignedBigInteger('inspection_end_id')->nullable();

            // Validation
            $table->foreignId('validated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('validated_at')->nullable();

            // Bon de sortie PDF généré
            $table->string('bon_sortie_path')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // ── Index critiques pour détection de conflits ──
            // Requête type : vehicle_id = ? AND status IN ('planned','confirmed','in_progress')
            //                AND datetime_start < ? AND datetime_end_planned > ?
            $table->index(['vehicle_id', 'status', 'datetime_start', 'datetime_end_planned'],
                          'idx_assignments_vehicle_conflict');
            $table->index(['driver_id', 'status', 'datetime_start', 'datetime_end_planned'],
                          'idx_assignments_driver_conflict');
            $table->index('status');
            $table->index('datetime_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};