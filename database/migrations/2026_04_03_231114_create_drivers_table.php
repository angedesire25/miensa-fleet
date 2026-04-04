<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `drivers` : profils des chauffeurs professionnels de la flotte.
     *
     * Distinct de la table `users` : un chauffeur peut ou non avoir un compte
     * utilisateur (lien via users.driver_id). Cette séparation permet de gérer
     * des chauffeurs sans accès à l'application.
     *
     * Les colonnes `total_km`, `total_assignments`, `total_infractions` sont des
     * statistiques dénormalisées maintenues à jour par l'observer DriverObserver
     * (app/Observers/DriverObserver.php) pour éviter les agrégations répétées.
     */
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();

            // Identité
            $table->string('matricule', 30)->unique();     // Ex: CHF-2024-007
            $table->string('full_name');
            $table->date('date_of_birth')->nullable();
            $table->string('phone', 30);
            $table->string('email', 100)->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('avatar')->nullable();          // Photo d'identité

            // Contrat
            $table->date('hire_date');
            $table->enum('contract_type', ['permanent', 'fixed_term', 'interim', 'contractor'])
                  ->default('permanent');
            $table->date('contract_end_date')->nullable(); // Si CDD

            // Permis de conduire
            $table->string('license_number', 50)->unique();
            $table->json('license_categories');            // ["B","C","D"]
            $table->date('license_expiry_date');
            $table->string('license_issuing_authority')->nullable();

            // Habilitations spéciales
            $table->json('habilitations')->nullable();
            // Ex: ["dangerous_goods","passenger_transport","long_distance"]

            // Statut
            $table->enum('status', [
                'active',      // Actif
                'suspended',   // Suspendu (temporaire)
                'on_leave',    // En congé
                'terminated',  // Licencié
            ])->default('active');
            $table->string('suspension_reason')->nullable();

            // Véhicule préférentiel (non bloquant)
            $table->foreignId('preferred_vehicle_id')
                  ->nullable()
                  ->constrained('vehicles')
                  ->nullOnDelete();

            // Statistiques dénormalisées (mise à jour par observer)
            $table->unsignedInteger('total_km')->default(0);
            $table->unsignedSmallInteger('total_assignments')->default(0);
            $table->unsignedSmallInteger('total_infractions')->default(0);

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('status');
            $table->index('license_expiry_date');
            $table->index('contract_end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};