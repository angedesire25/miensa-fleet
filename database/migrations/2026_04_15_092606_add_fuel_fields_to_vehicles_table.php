<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute les champs carburant à la table `vehicles`.
 *
 * Carte carburant : certains véhicules disposent d'une carte dédiée
 * (Total GR, Shell Fleet, etc.) permettant des paiements sans espèces
 * et un suivi automatique. Les ravitaillements sans carte restent possibles.
 *
 * Consommation : les valeurs sont mises à jour automatiquement
 * lors de l'enregistrement d'une fuel_transaction.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            // ── Carte carburant ──────────────────────────────────────────────
            $table->boolean('has_fuel_card')->default(false)->after('fuel_type');
            $table->string('fuel_card_number', 30)->nullable()->after('has_fuel_card');
            $table->string('fuel_card_provider', 60)->nullable()->after('fuel_card_number');
            // Plafond journalier autorisé sur la carte (FCFA), null = pas de limite définie
            $table->decimal('fuel_card_daily_limit', 10, 2)->nullable()->after('fuel_card_provider');
            // Date d'expiration de la carte carburant
            $table->date('fuel_card_expires_at')->nullable()->after('fuel_card_daily_limit');

            // ── Consommation & suivi carburant ───────────────────────────────
            // Consommation théorique constructeur (L/100km) — saisie manuelle
            $table->decimal('consumption_norm', 5, 2)->nullable()->after('fuel_card_expires_at');
            // Consommation réelle moyenne calculée depuis les transactions (L/100km)
            $table->decimal('consumption_real', 5, 2)->nullable()->after('consumption_norm');
            // Kilométrage lors du dernier plein enregistré
            $table->unsignedInteger('km_last_fill')->nullable()->after('consumption_real');
            // Date du dernier plein
            $table->date('date_last_fill')->nullable()->after('km_last_fill');
            // Litres cumulés depuis le début (historique)
            $table->decimal('total_liters_consumed', 10, 2)->default(0)->after('date_last_fill');
            // Montant total carburant dépensé (FCFA, cumulé)
            $table->decimal('total_fuel_cost', 12, 2)->default(0)->after('total_liters_consumed');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'has_fuel_card',
                'fuel_card_number',
                'fuel_card_provider',
                'fuel_card_daily_limit',
                'fuel_card_expires_at',
                'consumption_norm',
                'consumption_real',
                'km_last_fill',
                'date_last_fill',
                'total_liters_consumed',
                'total_fuel_cost',
            ]);
        });
    }
};
