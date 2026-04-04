<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `inspections` : fiches de contrôle véhicule — digitalisation de la fiche papier.
     *
     * Couvre 14 points de contrôle (huile, freins, feux, pneus, documents légaux…).
     * Une fiche peut être liée à une affectation (inspection au départ/retour chauffeur)
     * ou à une demande de véhicule, ou effectuée de manière autonome (routine).
     *
     * Le booléen `has_critical_issue` est calculé automatiquement dans le modèle
     * Inspection (boot→saving) depuis oil_level=low, brakes_status=critical ou
     * lights_status=critical. Il est indexé pour permettre les alertes immédiates.
     */
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();

            // Contexte
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();
            $table->foreignId('inspector_id')     // Contrôleur qui effectue la fiche
                  ->constrained('users')
                  ->restrictOnDelete();

            // Liens optionnels
            $table->foreignId('assignment_id')
                  ->nullable()
                  ->constrained('assignments')
                  ->nullOnDelete();
            $table->foreignId('request_id')
                  ->nullable()
                  ->constrained('vehicle_requests')
                  ->nullOnDelete();

            // Métadonnées
            $table->dateTime('inspected_at');             // Date et heure du contrôle
            $table->string('location')->nullable();       // Lieu du contrôle
            $table->enum('inspection_type', ['departure', 'return', 'routine'])
                  ->default('routine');

            // ── 14 Points de contrôle ──

            // 1. Kilométrage
            $table->unsignedInteger('km')->nullable();

            // 2. Huile moteur
            $table->enum('oil_level', ['low', 'medium', 'high'])->nullable();
            $table->string('oil_notes')->nullable();

            // 3. Liquide de refroidissement
            $table->enum('coolant_level', ['low', 'medium', 'high'])->nullable();

            // 4. Huile à frein
            $table->enum('brake_fluid_level', ['low', 'medium', 'high'])->nullable();

            // 5. Pression des pneus
            $table->enum('tire_pressure', ['low', 'medium', 'ok'])->nullable();
            $table->string('tire_notes')->nullable();

            // 6. Niveau de carburant (0-100%)
            $table->unsignedTinyInteger('fuel_level_pct')->nullable();

            // 7. Assurance
            $table->enum('insurance_status', ['present', 'absent', 'expired'])->nullable();
            $table->date('insurance_expiry')->nullable();

            // 8. Visite technique
            $table->enum('technical_control_status', ['present', 'absent', 'expired'])->nullable();
            $table->date('technical_control_expiry')->nullable();

            // 9. Carte grise
            $table->boolean('registration_present')->nullable();

            // 10. Vidange
            $table->enum('oil_change_status', ['ok', 'due_soon', 'overdue'])->nullable();
            $table->date('oil_change_date')->nullable();

            // 11. Carrosserie
            $table->text('body_notes')->nullable();
            $table->json('body_photos')->nullable();       // Photos des dégâts

            // 12. Feux
            $table->enum('lights_status', ['ok', 'minor_issue', 'critical'])->nullable();
            $table->text('lights_notes')->nullable();

            // 13. Freins
            $table->enum('brakes_status', ['ok', 'minor_issue', 'critical'])->nullable();
            $table->text('brakes_notes')->nullable();

            // 14. Observations générales & signature
            $table->text('general_observations')->nullable();
            $table->string('signature_path')->nullable();  // Signature numérique

            // Anomalie critique détectée (pour déclencher alerte immédiate)
            $table->boolean('has_critical_issue')->default(false);

            $table->timestamps();

            // Index
            $table->index(['vehicle_id', 'inspected_at']);
            $table->index('inspector_id');
            $table->index('has_critical_issue');
            $table->index('inspection_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};