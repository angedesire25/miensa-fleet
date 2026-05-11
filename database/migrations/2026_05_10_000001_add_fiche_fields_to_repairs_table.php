<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            // Durée d'immobilisation saisie manuellement (ex: "2 jours", "N/A")
            // Complète l'accesseur computed `immobilization_days` (entier calculé)
            if (! Schema::hasColumn('repairs', 'duree_immobilisation')) {
                $table->string('duree_immobilisation', 60)->nullable()->after('actual_exit_date');
            }

            // Chemin de stockage de la fiche DI générée (S3 ou local)
            if (! Schema::hasColumn('repairs', 'fiche_di_path')) {
                $table->string('fiche_di_path')->nullable()->after('duree_immobilisation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            foreach (['duree_immobilisation', 'fiche_di_path'] as $col) {
                if (Schema::hasColumn('repairs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
