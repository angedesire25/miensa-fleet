<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aligne les colonnes de `fuel_requests` sur les noms utilisés par le modèle.
 *
 * La migration initiale (092605) utilisait des noms différents :
 *   requested_by       → requester_id
 *   requested_liters   → liters_requested
 *   requested_amount   → estimated_amount
 *   purpose            → reason
 *   approved_by        → reviewed_by   (colonne unique de décision)
 *   approved_at        → reviewed_at
 *   rejection_reason   → review_notes
 *
 * Colonnes supprimées (fusionnées dans reviewed_*) :
 *   rejected_by, rejected_at
 *
 * Colonnes ajoutées :
 *   is_urgent     BOOLEAN default false
 *   requested_at  TIMESTAMP nullable
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_requests', function (Blueprint $table) {

            // ── Renommages ────────────────────────────────────────────────────
            // MySQL 8.0.4+ supporte RENAME COLUMN nativement (Laravel 10+)
            $table->renameColumn('requested_by',     'requester_id');
            $table->renameColumn('requested_liters', 'liters_requested');
            $table->renameColumn('requested_amount', 'estimated_amount');
            $table->renameColumn('purpose',          'reason');
            $table->renameColumn('approved_by',      'reviewed_by');
            $table->renameColumn('approved_at',      'reviewed_at');
            $table->renameColumn('rejection_reason', 'review_notes');
        });

        Schema::table('fuel_requests', function (Blueprint $table) {

            // ── Suppression des colonnes fusionnées ───────────────────────────
            // rejected_by et rejected_at sont maintenant couverts par
            // reviewed_by / reviewed_at + status = 'rejected'
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['rejected_by', 'rejected_at']);

            // ── Nouvelles colonnes ────────────────────────────────────────────
            $table->boolean('is_urgent')->default(false)->after('notes');
            $table->timestamp('requested_at')->nullable()->after('is_urgent');

            // Renommer needed_by → (garder tel quel, non utilisé dans le modèle)
            // On le supprime pour alléger le schéma
            $table->dropColumn('needed_by');
        });
    }

    public function down(): void
    {
        Schema::table('fuel_requests', function (Blueprint $table) {
            $table->renameColumn('requester_id',    'requested_by');
            $table->renameColumn('liters_requested','requested_liters');
            $table->renameColumn('estimated_amount','requested_amount');
            $table->renameColumn('reason',          'purpose');
            $table->renameColumn('reviewed_by',     'approved_by');
            $table->renameColumn('reviewed_at',     'approved_at');
            $table->renameColumn('review_notes',    'rejection_reason');
        });

        Schema::table('fuel_requests', function (Blueprint $table) {
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->date('needed_by')->nullable();
            $table->dropColumn(['is_urgent', 'requested_at']);
        });
    }
};
