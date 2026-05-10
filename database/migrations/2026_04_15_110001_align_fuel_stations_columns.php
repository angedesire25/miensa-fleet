<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aligne `fuel_stations` sur le modèle FuelStation.
 *
 * La migration initiale (092604) utilisait des noms différents :
 *   contact_name → contact_person
 *
 * Colonnes ajoutées :
 *   fuel_types   JSON  (types carburant disponibles)
 *   created_by   FK → users
 *   deleted_at   (soft deletes)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_stations', function (Blueprint $table) {
            // Renommage
            $table->renameColumn('contact_name', 'contact_person');
        });

        Schema::table('fuel_stations', function (Blueprint $table) {
            // Ajout des colonnes manquantes
            $table->json('fuel_types')->nullable()->after('is_active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('fuel_types');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('fuel_stations', function (Blueprint $table) {
            $table->renameColumn('contact_person', 'contact_name');
        });

        Schema::table('fuel_stations', function (Blueprint $table) {
            $table->dropColumn(['fuel_types', 'deleted_at']);
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
