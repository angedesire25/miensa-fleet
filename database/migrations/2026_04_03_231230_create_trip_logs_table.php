<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Carnet de bord numérique.
     * Un trajet est une sortie unique dans le cadre d'une affectation
     * ou d'une demande de véhicule.
     * Une affectation peut contenir plusieurs trajets.
     */
    public function up(): void
    {
        Schema::create('trip_logs', function (Blueprint $table) {
            $table->id();

            // Liens — une des deux FK doit être renseignée
            $table->foreignId('assignment_id')
                  ->nullable()
                  ->constrained('assignments')
                  ->cascadeOnDelete();
            $table->foreignId('request_id')
                  ->nullable()
                  ->constrained('vehicle_requests')
                  ->cascadeOnDelete();

            // Acteurs (dénormalisés pour requêtes rapides)
            $table->foreignId('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();
            $table->foreignId('user_id')   // Si collaborateur (non-chauffeur)
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            // Horaires
            $table->dateTime('datetime_start');
            $table->dateTime('datetime_end')->nullable();

            // Lieux
            $table->string('location_start')->nullable();
            $table->string('location_end')->nullable();
            $table->decimal('lat_start', 10, 7)->nullable(); // GPS optionnel
            $table->decimal('lng_start', 10, 7)->nullable();
            $table->decimal('lat_end', 10, 7)->nullable();
            $table->decimal('lng_end', 10, 7)->nullable();

            // Kilométrage
            $table->unsignedInteger('km_start');
            $table->unsignedInteger('km_end')->nullable();
            $table->unsignedInteger('km_total')
                  ->virtualAs('IF(km_end IS NOT NULL, km_end - km_start, NULL)');

            // Détails du trajet
            $table->string('purpose');                          // Motif
            $table->string('passengers')->nullable();           // Personnes transportées
            $table->decimal('fuel_added_liters', 5, 2)
                  ->nullable();                                  // Carburant rechargé
            $table->decimal('fuel_cost', 10, 2)->nullable();    // Coût si connu

            // Incidents pendant le trajet
            $table->boolean('has_incident')->default(false);
            $table->text('incident_description')->nullable();
            $table->json('incident_photos')->nullable();

            $table->text('observations')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            // Index
            $table->index(['vehicle_id', 'datetime_start']);
            $table->index(['driver_id', 'datetime_start']);
            $table->index(['assignment_id']);
            $table->index('has_incident');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_logs');
    }
};