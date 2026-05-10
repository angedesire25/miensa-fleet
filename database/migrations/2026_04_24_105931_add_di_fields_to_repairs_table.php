<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chaque ajout est conditionnel pour rendre la migration idempotente
        // (la première tentative avait échoué après avoir partiellement ajouté des colonnes)

        Schema::table('repairs', function (Blueprint $table) {

            // ── Identité DI ───────────────────────────────────────────────
            if (! Schema::hasColumn('repairs', 'di_number')) {
                $table->string('di_number', 30)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('repairs', 'or_initial_reference')) {
                $table->string('or_initial_reference', 100)->nullable()->after('di_number');
            }

            // ── Informations véhicule complémentaires ─────────────────────
            if (! Schema::hasColumn('repairs', 'vehicle_type_body')) {
                $table->string('vehicle_type_body', 60)->nullable()->after('or_initial_reference');
            }

            // ── Dates de suivi ────────────────────────────────────────────
            if (! Schema::hasColumn('repairs', 'availability_date_requested')) {
                $table->date('availability_date_requested')->nullable()->after('vehicle_type_body');
            }
            if (! Schema::hasColumn('repairs', 'actual_exit_date')) {
                $table->date('actual_exit_date')->nullable()->after('availability_date_requested');
            }

            // NOTE : immobilization_days n'est PAS une colonne DB.
            // MySQL interdit CURDATE() dans les colonnes générées (fonction non-déterministe).
            // Le calcul est réalisé via l'accesseur PHP `immobilization_days` du modèle Repair.

            // ── Signatures ────────────────────────────────────────────────
            if (! Schema::hasColumn('repairs', 'signature_company_path')) {
                $table->string('signature_company_path')->nullable()->after('actual_exit_date');
            }
            if (! Schema::hasColumn('repairs', 'signature_garage_path')) {
                $table->string('signature_garage_path')->nullable()->after('signature_company_path');
            }
            if (! Schema::hasColumn('repairs', 'signature_company_exit_path')) {
                $table->string('signature_company_exit_path')->nullable()->after('signature_garage_path');
            }
            if (! Schema::hasColumn('repairs', 'signature_garage_exit_path')) {
                $table->string('signature_garage_exit_path')->nullable()->after('signature_company_exit_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $columns = [
                'di_number', 'or_initial_reference', 'vehicle_type_body',
                'availability_date_requested', 'actual_exit_date',
                'signature_company_path', 'signature_garage_path',
                'signature_company_exit_path', 'signature_garage_exit_path',
            ];

            $existing = array_filter($columns, fn($c) => Schema::hasColumn('repairs', $c));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
