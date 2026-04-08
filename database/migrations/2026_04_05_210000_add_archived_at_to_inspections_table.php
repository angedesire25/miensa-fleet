<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute la colonne `archived_at` à la table inspections.
 *
 * L'archivage est une action douce (soft-archive) : la fiche reste en base
 * mais est exclue des listes par défaut. Un filtre "Afficher les archives"
 * permet de les retrouver.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Timestamp de l'archivage (null = non archivée)
            $table->timestamp('archived_at')->nullable()->after('has_critical_issue');
            // Index pour filtrer rapidement les fiches actives
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropIndex(['archived_at']);
            $table->dropColumn('archived_at');
        });
    }
};
 