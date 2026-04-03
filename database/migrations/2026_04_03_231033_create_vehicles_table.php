<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // Identité du véhicule
            $table->string('brand', 60);                  // Marque
            $table->string('model', 60);                  // Modèle
            $table->string('plate', 20)->unique();        // Immatriculation
            $table->year('year');                         // Année de mise en service
            $table->string('color', 40)->nullable();
            $table->string('vin', 17)->nullable()->unique(); // Numéro de châssis

            // Caractéristiques techniques
            $table->enum('fuel_type', ['diesel', 'gasoline', 'hybrid', 'electric', 'lpg'])
                  ->default('diesel');
            $table->enum('vehicle_type', ['city', 'sedan', 'suv', 'pickup', 'van', 'truck', 'motorcycle'])
                  ->default('sedan');
            $table->enum('license_category', ['A', 'B', 'C', 'D', 'E', 'BE', 'CE'])
                  ->default('B');                         // Catégorie permis requis
            $table->unsignedSmallInteger('seats')->default(5);
            $table->unsignedSmallInteger('payload_kg')->nullable(); // Charge utile (utilitaires)

            // Kilométrage
            $table->unsignedInteger('km_current')->default(0);
            $table->unsignedInteger('km_next_service')->nullable();  // Prochain entretien
            $table->unsignedInteger('km_last_oil_change')->nullable();
            $table->date('date_last_oil_change')->nullable();

            // Statut opérationnel
            $table->enum('status', [
                'available',    // Disponible
                'on_mission',   // En mission / affecté
                'maintenance',  // En entretien
                'breakdown',    // En panne
                'sold',         // Vendu
                'retired',      // Hors service
            ])->default('available');

            // Chauffeur actuellement affecté (dénormalisation pour perf)
            // FK vers drivers ajoutée dans add_foreign_keys_to_tables (dépendance circulaire)
            $table->unsignedBigInteger('current_driver_id')->nullable();

            // Données financières
            $table->decimal('purchase_price', 12, 2)->nullable();  // Valeur d'acquisition
            $table->date('purchase_date')->nullable();
            $table->string('insurance_company')->nullable();
            $table->string('insurance_policy_number')->nullable();

            // Photos & notes
            $table->json('photos')->nullable();            // Tableau de chemins
            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('status');
            $table->index('brand');
            $table->index('license_category');
            $table->index('vehicle_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};