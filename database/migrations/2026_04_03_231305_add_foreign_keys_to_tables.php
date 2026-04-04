<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration de résolution des dépendances circulaires entre tables.
     *
     * Certaines FK n'ont pas pu être créées dans leur migration d'origine
     * car la table référencée n'existait pas encore au moment de la création.
     * Cette migration est exécutée après toutes les tables et ajoute :
     *
     * Résolution de la référence circulaire :
     *   users.driver_id → drivers   (ajout après création de drivers)
     *   vehicles.current_driver_id → drivers  (déjà défini, mais nécessite drivers)
     *
     * On ajoute ici les FK différées qui ne pouvaient pas être créées
     * au moment de la migration initiale (ordre de création des tables).
     *
     * Note: La migration users a été créée SANS la FK driver_id
     * pour éviter la dépendance circulaire. On l'ajoute ici.
     */
    public function up(): void
    {
        // Ajouter FK driver_id sur users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('driver_id')
                  ->nullable()
                  ->after('avatar')
                  ->constrained('drivers')
                  ->nullOnDelete();
        });

        // Ajouter FK current_driver_id sur vehicles (dépendance circulaire)
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreign('current_driver_id')
                  ->references('id')->on('drivers')
                  ->nullOnDelete();
        });

        // Ajouter FK inspection_start_id et inspection_end_id sur assignments (dépendance circulaire)
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('inspection_start_id')
                  ->references('id')->on('inspections')
                  ->nullOnDelete();
            $table->foreign('inspection_end_id')
                  ->references('id')->on('inspections')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['inspection_start_id']);
            $table->dropForeign(['inspection_end_id']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['current_driver_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropColumn('driver_id');
        });
    }
};