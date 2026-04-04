<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `vehicle_requests` : demandes de véhicule par les collaborateurs non-chauffeurs.
     *
     * Permet à tout employé disposant du rôle `collaborator` de réserver un véhicule
     * pour un déplacement professionnel. Le gestionnaire de flotte approuve ou refuse.
     *
     * Workflow : pending → approved/rejected → confirmed → in_progress → completed
     *                                        ↳ cancelled (annulation demandeur)
     */
    public function up(): void
    {
        Schema::create('vehicle_requests', function (Blueprint $table) {
            $table->id();

            // Demandeur (collaborateur)
            $table->foreignId('requester_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Véhicule attribué par le gestionnaire (nullable jusqu'à approbation)
            $table->foreignId('vehicle_id')
                  ->nullable()
                  ->constrained('vehicles')
                  ->nullOnDelete();

            // Préférences de la demande
            $table->enum('vehicle_type_preferred', [
                'any', 'city', 'sedan', 'suv', 'pickup', 'van', 'truck'
            ])->default('any');

            // Créneau demandé
            $table->dateTime('datetime_start');
            $table->dateTime('datetime_end_planned');
            $table->dateTime('datetime_end_actual')->nullable();

            // Détails de la mission
            $table->string('destination');
            $table->string('purpose');                      // Motif professionnel
            $table->unsignedTinyInteger('passengers')->default(1);
            $table->boolean('is_urgent')->default(false);
            $table->text('requester_notes')->nullable();
            $table->string('attachment_path')->nullable();  // Ordre de mission, bon

            // Traitement par le gestionnaire
            $table->enum('status', [
                'pending',      // En attente de traitement
                'approved',     // Approuvée, véhicule attribué
                'rejected',     // Rejetée
                'confirmed',    // Bon de sortie généré
                'in_progress',  // Véhicule pris en charge
                'completed',    // Véhicule rendu
                'cancelled',    // Annulée par le demandeur
            ])->default('pending');

            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();       // Motif rejet ou conditions

            // Conditions d'utilisation spécifiques
            $table->unsignedInteger('km_limit')->nullable(); // Plafond km autorisé
            $table->string('geographic_limit')->nullable();  // Zone géographique

            // Kilométrage (saisi par le collaborateur ou contrôleur)
            $table->unsignedInteger('km_start')->nullable();
            $table->unsignedInteger('km_end')->nullable();
            $table->unsignedInteger('km_total')
                  ->virtualAs('IF(km_end IS NOT NULL AND km_start IS NOT NULL, km_end - km_start, NULL)');

            // État véhicule
            $table->enum('condition_start', ['good', 'fair', 'poor'])->nullable();
            $table->text('condition_start_notes')->nullable();
            $table->enum('condition_end', ['good', 'fair', 'poor'])->nullable();
            $table->text('condition_end_notes')->nullable();

            // Bon de sortie
            $table->string('bon_sortie_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['requester_id', 'status']);
            $table->index(['vehicle_id', 'status', 'datetime_start', 'datetime_end_planned'],
                          'idx_requests_vehicle_conflict');
            $table->index('status');
            $table->index('datetime_start');
            $table->index('is_urgent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_requests');
    }
};