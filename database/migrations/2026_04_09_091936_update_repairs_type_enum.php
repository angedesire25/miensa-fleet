<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Étape 1 : étendre l'ENUM pour inclure les deux ensembles de valeurs
        DB::statement("ALTER TABLE repairs MODIFY COLUMN repair_type ENUM(
            'corrective','preventive','warranty','recall',
            'body_repair','mechanical','electrical','tire','painting','glass','full_service','other'
        ) NOT NULL");

        // Étape 2 : migrer les anciennes valeurs vers les nouvelles
        DB::statement("UPDATE repairs SET repair_type = 'mechanical'  WHERE repair_type = 'corrective'");
        DB::statement("UPDATE repairs SET repair_type = 'full_service' WHERE repair_type = 'preventive'");
        DB::statement("UPDATE repairs SET repair_type = 'other'        WHERE repair_type IN ('warranty','recall')");

        // Étape 3 : restreindre aux nouvelles valeurs uniquement
        DB::statement("ALTER TABLE repairs MODIFY COLUMN repair_type ENUM(
            'body_repair','mechanical','electrical','tire','painting','glass','full_service','other'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE repairs MODIFY COLUMN repair_type ENUM(
            'corrective','preventive','warranty','recall'
        ) NOT NULL");
    }
};
