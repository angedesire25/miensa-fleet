<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_fault_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('repair_id')
                  ->constrained('repairs')
                  ->cascadeOnDelete();

            // Code alphanumérique de l'anomalie.
            // Préfixe issu de la catégorie : AN01, PN02, US01, AC01, AU01…
            // Généré automatiquement ou saisi manuellement.
            $table->string('code', 10);

            $table->enum('category', [
                'anomaly',      // AN — Anomalie générale
                'breakdown',    // PN — Panne
                'wear',         // US — Usure
                'accident',     // AC — Accident
                'other',        // AU — Autre
            ])->default('breakdown');

            // Libellé court de l'anomalie, ex: "Climatisation inopérante"
            $table->string('label');

            // Ce que le garage a diagnostiqué sur cette panne précise
            $table->text('garage_diagnosis')->nullable();

            // Travaux réellement effectués pour résoudre cette panne
            $table->text('work_performed')->nullable();

            $table->enum('resolution_status', [
                'pending',      // En attente de traitement
                'resolved',     // Résolu
                'partial',      // Partiellement résolu
                'deferred',     // Reporté (pièce non disponible)
                'not_covered',  // Non pris en charge
            ])->default('pending');

            // Coût de réparation imputable à cette panne spécifique
            $table->decimal('fault_cost', 10, 2)->nullable();

            // Ordre d'affichage (AN avant PN, etc.)
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['repair_id', 'sort_order']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_fault_codes');
    }
};
