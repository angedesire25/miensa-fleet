<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Chauffeur concerné par l'inspection (différent de l'inspector_id = auteur de la fiche)
            $table->foreignId('driver_id')
                  ->nullable()
                  ->after('inspector_id')
                  ->constrained('drivers')
                  ->nullOnDelete();

            // Workflow de validation
            $table->enum('status', ['draft', 'submitted', 'validated', 'rejected'])
                  ->default('submitted')
                  ->after('inspection_type')
                  ->comment('draft=brouillon, submitted=soumis, validated=validé, rejected=à corriger');

            $table->foreignId('validated_by')
                  ->nullable()
                  ->after('has_critical_issue')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('validated_at')->nullable()->after('validated_by');

            $table->text('rejection_reason')->nullable()->after('validated_at');

            // Index utiles
            $table->index(['vehicle_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Driver::class);
            $table->dropColumn(['driver_id', 'status', 'validated_by', 'validated_at', 'rejection_reason']);
        });
    }
};
