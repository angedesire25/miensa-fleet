<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Infractions routières.
     * Le conducteur peut être un chauffeur (driver_id) OU un collaborateur (user_id).
     * L'identification auto se fait via l'affectation ou la demande active
     * au moment de l'infraction (datetime_occurred).
     */
    public function up(): void
    {
        Schema::create('infractions', function (Blueprint $table) {
            $table->id();

            // Véhicule impliqué
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            // Conducteur identifié — l'un ou l'autre (ou les deux null si inconnu)
            $table->foreignId('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();
            $table->foreignId('user_id')    // Collaborateur non-chauffeur
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Contexte de l'affectation au moment de l'infraction
            $table->foreignId('assignment_id')
                  ->nullable()
                  ->constrained('assignments')
                  ->nullOnDelete();
            $table->foreignId('request_id')
                  ->nullable()
                  ->constrained('vehicle_requests')
                  ->nullOnDelete();

            // Détails de l'infraction
            $table->dateTime('datetime_occurred');          // Date et heure exactes
            $table->string('location');                     // Lieu

            $table->enum('type', [
                'speeding',           // Excès de vitesse
                'red_light',          // Feu rouge grillé
                'illegal_parking',    // Stationnement illicite
                'drunk_driving',      // Conduite en état d'ivresse
                'phone_use',          // Usage téléphone au volant
                'accident',           // Accident
                'seatbelt',           // Non port de ceinture
                'overloading',        // Surcharge
                'other',
            ]);

            $table->text('description')->nullable();

            $table->enum('source', [
                'police_report',      // Procès-verbal police
                'speed_camera',       // Radar automatique
                'internal_report',    // Signalement interne
                'joint_report',       // Constat amiable
                'other',
            ]);

            $table->string('pv_reference')->nullable();     // Numéro PV officiel
            $table->json('documents')->nullable();           // Chemins PV, photos, constat

            // Financier
            $table->decimal('fine_amount', 10, 2)->nullable();

            $table->enum('payment_status', [
                'unpaid',             // Non payée
                'paid_by_company',    // Payée par la société
                'charged_to_driver',  // Imputée au conducteur (retenue)
                'contested',          // Contestée
                'waived',             // Remise
            ])->default('unpaid');

            $table->date('payment_date')->nullable();
            $table->text('payment_notes')->nullable();

            // Imputation
            $table->enum('imputation', ['company', 'driver', 'pending'])
                  ->default('pending');

            // Sanction interne
            $table->text('internal_sanction')->nullable();  // Avertissement, mise à pied...
            $table->foreignId('sanction_decided_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Statut global
            $table->enum('status', [
                'open',       // En cours de traitement
                'processed',  // Traitée
                'contested',  // Contestée
                'archived',   // Archivée
            ])->default('open');

            // Saisie
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->boolean('auto_identified')->default(false); // Conducteur identifié auto
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['vehicle_id', 'datetime_occurred']);
            $table->index('driver_id');
            $table->index('user_id');
            $table->index(['status', 'payment_status']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infractions');
    }
};