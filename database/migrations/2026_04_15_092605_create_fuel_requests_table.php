<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `fuel_requests` — Demandes de carburant.
 *
 * Flux : collaborateur/chauffeur soumet → gestionnaire approuve ou rejette
 *        → si approuvée, une `fuel_transaction` est créée à l'exécution.
 *
 * Une demande peut cibler un véhicule précis ou rester générique (rare).
 * La quantité est estimée ; la transaction enregistre les quantités réelles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_requests', function (Blueprint $table) {
            $table->id();

            // ── Référence unique ─────────────────────────────────────────────
            // Format : FR-YYYYMM-XXXXX (ex: FR-202604-00042)
            $table->string('reference', 20)->unique();

            // ── Qui demande, pour quel véhicule ─────────────────────────────
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            // Conducteur concerné par le ravitaillement (peut différer du demandeur)
            $table->foreignId('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();

            // Utilisateur qui a soumis la demande
            $table->foreignId('requested_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            // ── Station souhaitée ────────────────────────────────────────────
            // Nullable : le demandeur peut ne pas spécifier de station
            $table->foreignId('fuel_station_id')
                  ->nullable()
                  ->constrained('fuel_stations')
                  ->nullOnDelete();

            // ── Besoin exprimé ───────────────────────────────────────────────
            $table->enum('fuel_type', ['diesel', 'gasoline', 'hybrid', 'electric', 'lpg'])
                  ->default('diesel');
            $table->decimal('requested_liters', 8, 2)->nullable();   // Quantité estimée (L)
            $table->decimal('requested_amount', 10, 2)->nullable();  // Montant estimé (FCFA)

            // Kilométrage déclaré par le demandeur au moment de la demande
            $table->unsignedInteger('odometer_km')->nullable();

            // Motif de la demande
            $table->string('purpose')->nullable();  // "Mission Yamoussoukro", "Tournée clientèle"…
            $table->text('notes')->nullable();

            // ── Statut du cycle de vie ───────────────────────────────────────
            $table->enum('status', [
                'pending',    // En attente de validation
                'approved',   // Approuvée — en attente d'exécution
                'rejected',   // Refusée
                'fulfilled',  // Exécutée (transaction créée)
                'cancelled',  // Annulée par le demandeur
            ])->default('pending');

            // ── Validation ───────────────────────────────────────────────────
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->foreignId('rejected_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // ── Date souhaitée ───────────────────────────────────────────────
            $table->date('needed_by')->nullable();  // Date à laquelle le carburant est nécessaire

            $table->timestamps();
            $table->softDeletes();

            // ── Index ────────────────────────────────────────────────────────
            $table->index('status');
            $table->index('vehicle_id');
            $table->index('requested_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_requests');
    }
};
