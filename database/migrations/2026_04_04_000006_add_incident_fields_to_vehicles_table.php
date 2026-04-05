<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute sur `vehicles` quatre colonnes dénormalisées liées aux sinistres
     * et aux réparations.
     *
     * Ces colonnes sont maintenues à jour par les observers/services
     * (IncidentObserver, RepairObserver) afin d'éviter des agrégats coûteux
     * à chaque affichage de la liste des véhicules ou du tableau de bord.
     *
     *   total_incidents         → COUNT sur incidents (toutes sévérités)
     *   total_repair_cost       → SUM des repairs.invoice_amount (réparations facturées)
     *   last_incident_at        → datetime_occurred du dernier incident
     *   last_repair_returned_at → datetime_returned de la dernière réparation restituée
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Nombre total de sinistres déclarés pour ce véhicule (dénormalisé)
            $table->unsignedSmallInteger('total_incidents')
                  ->default(0)
                  ->after('notes');

            // Coût cumulé de toutes les réparations facturées (dénormalisé)
            $table->decimal('total_repair_cost', 12, 2)
                  ->default(0)
                  ->after('total_incidents');

            // Date/heure du dernier incident constaté
            $table->timestamp('last_incident_at')
                  ->nullable()
                  ->after('total_repair_cost');

            // Date/heure du dernier retour de garage (réparation restituée)
            $table->timestamp('last_repair_returned_at')
                  ->nullable()
                  ->after('last_incident_at');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'total_incidents',
                'total_repair_cost',
                'last_incident_at',
                'last_repair_returned_at',
            ]);
        });
    }
};
