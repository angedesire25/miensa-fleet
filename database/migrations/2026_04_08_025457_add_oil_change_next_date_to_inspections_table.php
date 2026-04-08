<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Colonnes ajoutées manuellement car la migration était vide lors du premier `migrate`
        Schema::table('inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('inspections', 'oil_change_km')) {
                $table->unsignedInteger('oil_change_km')->nullable()->after('oil_change_date')
                      ->comment('Kilométrage au moment de la dernière vidange');
            }
            if (!Schema::hasColumn('inspections', 'oil_change_next_date')) {
                $table->date('oil_change_next_date')->nullable()->after('oil_change_km')
                      ->index()
                      ->comment('Date prévue de la prochaine vidange');
            }
            if (!Schema::hasColumn('inspections', 'oil_change_next_km')) {
                $table->unsignedInteger('oil_change_next_km')->nullable()->after('oil_change_next_date')
                      ->comment('Kilométrage seuil de la prochaine vidange');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['oil_change_km', 'oil_change_next_date', 'oil_change_next_km'],
                fn($col) => Schema::hasColumn('inspections', $col)
            ));
        });
    }
};
