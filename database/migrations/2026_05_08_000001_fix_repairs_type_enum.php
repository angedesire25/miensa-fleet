<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige l'enum `repair_type` de la table `repairs`.
 *
 * La migration 2026_04_09_091936 avait remplacé par erreur les valeurs
 * métier (corrective/preventive/warranty/recall) par des valeurs génériques.
 * Cette migration rétablit les valeurs correctes.
 *
 * Correspondances de conversion (aucune ligne existante ne sera perdue) :
 *   mechanical   → corrective
 *   full_service → preventive
 *   body_repair / electrical / tire / painting / glass / other → corrective
 *   (valeur générique → corrective = Retour Atelier, le cas le plus courant)
 */
return new class extends Migration
{
    private const CORRECT = "'corrective','preventive','warranty','recall'";

    private const WRONG   = "'body_repair','mechanical','electrical','tire','painting','glass','full_service','other'";

    public function up(): void
    {
        // Étape 1 — Élargir l'enum pour autoriser toutes les valeurs en transit
        DB::statement('ALTER TABLE repairs MODIFY COLUMN repair_type ENUM(' . self::CORRECT . ',' . self::WRONG . ') NOT NULL');

        // Étape 2 — Convertir les anciennes valeurs vers les nouvelles
        DB::statement("UPDATE repairs SET repair_type = 'preventive'  WHERE repair_type = 'full_service'");
        DB::statement("UPDATE repairs SET repair_type = 'corrective'  WHERE repair_type IN ('body_repair','mechanical','electrical','tire','painting','glass','other')");

        // Étape 3 — Restreindre aux valeurs correctes uniquement
        DB::statement('ALTER TABLE repairs MODIFY COLUMN repair_type ENUM(' . self::CORRECT . ') NOT NULL');
    }

    public function down(): void
    {
        // Restaurer les anciennes valeurs si nécessaire (rollback)
        DB::statement('ALTER TABLE repairs MODIFY COLUMN repair_type ENUM(' . self::CORRECT . ',' . self::WRONG . ') NOT NULL');

        DB::statement("UPDATE repairs SET repair_type = 'mechanical'   WHERE repair_type = 'corrective'");
        DB::statement("UPDATE repairs SET repair_type = 'full_service' WHERE repair_type = 'preventive'");
        DB::statement("UPDATE repairs SET repair_type = 'other'        WHERE repair_type IN ('warranty','recall')");

        DB::statement('ALTER TABLE repairs MODIFY COLUMN repair_type ENUM(' . self::WRONG . ') NOT NULL');
    }
};
