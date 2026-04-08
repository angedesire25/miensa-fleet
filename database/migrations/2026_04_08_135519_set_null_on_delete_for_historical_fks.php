<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Change les contraintes RESTRICT → SET NULL sur les colonnes d'audit/historique.
 *
 * Objectif : permettre la suppression définitive d'un chauffeur ou d'un utilisateur
 * sans perdre les données historiques (affectations, fiches, demandes).
 * Les enregistrements sont conservés avec la colonne FK mise à NULL.
 *
 * Colonnes concernées :
 *   - assignments.driver_id      (chauffeur affecté)
 *   - inspections.inspector_id   (utilisateur auteur de la fiche)
 *   - vehicle_requests.requester_id (utilisateur demandeur)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── assignments.driver_id ──────────────────────────────────────────
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->foreignId('driver_id')->nullable()->change();
            $table->foreign('driver_id')
                  ->references('id')->on('drivers')
                  ->nullOnDelete();
        });

        // ── inspections.inspector_id ───────────────────────────────────────
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['inspector_id']);
            $table->unsignedBigInteger('inspector_id')->nullable()->change();
            $table->foreign('inspector_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });

        // ── vehicle_requests.requester_id ──────────────────────────────────
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->dropForeign(['requester_id']);
            $table->unsignedBigInteger('requester_id')->nullable()->change();
            $table->foreign('requester_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->foreignId('driver_id')->nullable(false)->change();
            $table->foreign('driver_id')
                  ->references('id')->on('drivers')
                  ->restrictOnDelete();
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['inspector_id']);
            $table->unsignedBigInteger('inspector_id')->nullable(false)->change();
            $table->foreign('inspector_id')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->dropForeign(['requester_id']);
            $table->unsignedBigInteger('requester_id')->nullable(false)->change();
            $table->foreign('requester_id')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });
    }
};
