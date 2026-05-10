<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `fuel_transactions` — Ravitaillements réellement effectués.
 *
 * Deux origines possibles :
 *   1. Issue d'une demande approuvée (fuel_request_id renseigné)
 *   2. Saisie directe par un gestionnaire sans demande préalable (fuel_request_id NULL)
 *
 * Permet de calculer :
 *   - Consommation réelle par véhicule (L/100 km)
 *   - Coût total carburant par période, véhicule, chauffeur, station
 *   - Détecter les anomalies (consommation anormale, pleins non justifiés)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();

            // ── Référence unique ─────────────────────────────────────────────
            // Format : FT-YYYYMM-XXXXX (ex: FT-202604-00017)
            $table->string('reference', 20)->unique();

            // ── Lien avec la demande (optionnel) ────────────────────────────
            $table->foreignId('fuel_request_id')
                  ->nullable()
                  ->constrained('fuel_requests')
                  ->nullOnDelete();

            // ── Véhicule et conducteur ───────────────────────────────────────
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            $table->foreignId('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();

            // ── Station ──────────────────────────────────────────────────────
            $table->foreignId('fuel_station_id')
                  ->nullable()
                  ->constrained('fuel_stations')
                  ->nullOnDelete();

            // Station libre (si non référencée) — ex: "Pompiste bord de route Daloa"
            $table->string('station_name_free', 150)->nullable();

            // ── Détails du ravitaillement ────────────────────────────────────
            $table->enum('fuel_type', ['diesel', 'gasoline', 'hybrid', 'electric', 'lpg'])
                  ->default('diesel');

            $table->decimal('liters', 8, 2);                // Litres réellement distribués
            $table->decimal('unit_price', 8, 2);            // Prix unitaire au litre (FCFA)
            $table->decimal('total_amount', 10, 2);         // Montant total (FCFA)

            // ── Kilométrage au moment du plein ───────────────────────────────
            $table->unsignedInteger('odometer_km');         // Relevé compteur au plein
            // Calculé automatiquement à partir du plein précédent (nullable en 1er plein)
            $table->unsignedInteger('km_since_last_fill')->nullable();
            // L/100km calculé (null si pas de plein précédent connu)
            $table->decimal('consumption_per_100km', 5, 2)->nullable();

            // ── Carte carburant ──────────────────────────────────────────────
            $table->boolean('fuel_card_used')->default(false);
            // Numéro de carte utilisé (peut différer de la carte du véhicule si prêt)
            $table->string('fuel_card_number', 30)->nullable();

            // ── Justificatif ─────────────────────────────────────────────────
            $table->string('receipt_number', 60)->nullable();     // N° ticket/facture
            $table->string('receipt_photo')->nullable();          // Chemin vers la photo du ticket

            // ── Qui saisit / quand ───────────────────────────────────────────
            $table->foreignId('recorded_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Date réelle du ravitaillement (peut différer de created_at si saisie décalée)
            $table->date('fueled_at');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Index ────────────────────────────────────────────────────────
            $table->index('vehicle_id');
            $table->index('driver_id');
            $table->index('fueled_at');
            $table->index('fuel_station_id');
            $table->index('fuel_card_used');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_transactions');
    }
};
