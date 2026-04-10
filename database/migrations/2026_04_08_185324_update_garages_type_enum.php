<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Met à jour l'ENUM `garages.type` pour correspondre aux valeurs du formulaire.
 * Anciennes valeurs : dealer, independent, official_service, roadside
 * Nouvelles valeurs : general, body_repair, electrical, tires, painting, glass, specialized
 *
 * Les lignes existantes avec une ancienne valeur sont migrées vers 'general'.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Élargir l'ENUM pour accepter les deux jeux de valeurs simultanément
        DB::statement("ALTER TABLE garages MODIFY COLUMN type ENUM('dealer','independent','official_service','roadside','general','body_repair','electrical','tires','painting','glass','specialized') NOT NULL DEFAULT 'general'");

        // 2. Migrer les anciennes valeurs vers 'general'
        DB::statement("UPDATE garages SET type = 'general' WHERE type IN ('dealer','independent','official_service','roadside')");

        // 3. Réduire au nouveau jeu de valeurs uniquement
        DB::statement("ALTER TABLE garages MODIFY COLUMN type ENUM('general','body_repair','electrical','tires','painting','glass','specialized') NOT NULL DEFAULT 'general'");
    }

    public function down(): void
    {
        DB::statement("UPDATE garages SET type = 'independent' WHERE type NOT IN ('dealer','independent','official_service','roadside')");
        DB::statement("ALTER TABLE garages MODIFY COLUMN type ENUM('dealer','independent','official_service','roadside') NOT NULL");
    }
};
