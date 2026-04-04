<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `alerts` : centre de notifications et d'alertes centralisé.
     *
     * Les alertes sont générées par deux mécanismes :
     *   1. Scheduler quotidien (app/Console/Commands) → expiration de documents,
     *      visites médicales dues, retards de retour, amendes impayées…
     *   2. En temps réel lors d'événements critiques → anomalie sur fiche de contrôle,
     *      nouvelle infraction, permis expiré lors d'une tentative d'affectation.
     *
     * Chaque alerte cible un objet polymorphique (véhicule, chauffeur, utilisateur
     * ou demande) et peut être envoyée sur plusieurs canaux (email, SMS, in-app).
     *
     * Workflow : new → seen → processed / ignored
     */
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            $table->enum('type', [
                // Véhicule
                'insurance_expiring',
                'insurance_expired',
                'technical_control_expiring',
                'technical_control_expired',
                'oil_change_due',
                'vehicle_anomaly',        // Anomalie critique sur fiche de contrôle
                'vehicle_not_inspected',  // Pas de fiche depuis X jours

                // Chauffeur
                'license_expiring',
                'license_expired',
                'medical_fitness_due',
                'driver_document_missing',
                'contract_ending',        // Fin de CDD imminente

                // Demande de véhicule
                'request_pending_timeout',  // Demande sans réponse depuis X heures
                'vehicle_return_overdue',   // Retour en retard

                // Infraction
                'new_infraction',
                'fine_unpaid',
            ]);

            // Cible polymorphique : véhicule, chauffeur, utilisateur ou demande
            $table->foreignId('vehicle_id')
                  ->nullable()
                  ->constrained('vehicles')
                  ->cascadeOnDelete();
            $table->foreignId('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('request_id')
                  ->nullable()
                  ->constrained('vehicle_requests')
                  ->cascadeOnDelete();
            $table->foreignId('infraction_id')
                  ->nullable()
                  ->constrained('infractions')
                  ->cascadeOnDelete();

            // Contenu
            $table->string('title');
            $table->text('message');
            $table->date('due_date')->nullable();        // Date d'échéance concernée
            $table->integer('days_remaining')->nullable(); // Jours restants au moment de création

            $table->enum('severity', ['info', 'warning', 'critical'])
                  ->default('warning');

            // Canaux d'envoi
            $table->json('channels')->nullable();        // ["email","sms","in_app"]
            $table->timestamp('sent_at')->nullable();
            $table->boolean('send_failed')->default(false);

            // Statut de traitement
            $table->enum('status', ['new', 'seen', 'processed', 'ignored'])
                  ->default('new');
            $table->foreignId('processed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('process_notes')->nullable();

            $table->timestamps();

            // Index
            $table->index(['status', 'severity']);
            $table->index('type');
            $table->index('due_date');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};