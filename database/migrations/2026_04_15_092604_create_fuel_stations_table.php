<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `fuel_stations` — Stations-service référencées dans la flotte.
 *
 * Centralise les stations partenaires pour le suivi des ravitaillements.
 * Un ravitaillement peut référencer une station ou rester libre (station non référencée).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_stations', function (Blueprint $table) {
            $table->id();

            // ── Identité ─────────────────────────────────────────────────────
            $table->string('name');                             // "Station Total Plateau"
            $table->string('brand', 60)->nullable();           // "Total", "Shell", "Vivo Energy"…
            $table->string('code', 20)->nullable()->unique();  // Code interne de référence

            // ── Localisation ─────────────────────────────────────────────────
            $table->string('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('country', 60)->default('CI');
            $table->decimal('latitude',  10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // ── Contact ──────────────────────────────────────────────────────
            $table->string('phone', 30)->nullable();
            $table->string('contact_name')->nullable();

            // ── Tarification & disponibilité ─────────────────────────────────
            // Prix constatés par litre (indicatifs, mis à jour manuellement)
            $table->decimal('price_diesel',   8, 2)->nullable();  // FCFA/litre
            $table->decimal('price_gasoline', 8, 2)->nullable();
            $table->boolean('accepts_fuel_card')->default(false);  // Accepte les cartes carburant

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('city');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_stations');
    }
};
