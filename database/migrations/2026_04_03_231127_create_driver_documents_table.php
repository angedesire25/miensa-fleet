<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('driver_id')
                  ->constrained('drivers')
                  ->cascadeOnDelete();

            $table->enum('type', [
                'license',            // Permis de conduire
                'national_id',        // Carte nationale d'identité
                'medical_fitness',    // Visite médicale d'aptitude
                'safety_training',    // Formation sécurité routière
                'special_habilitation', // Habilitation spéciale
                'employment_contract',  // Contrat de travail
                'criminal_record',    // Casier judiciaire
                'other',
            ]);

            $table->string('document_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Pour visite médicale
            $table->enum('medical_result', ['fit', 'fit_with_restrictions', 'unfit'])
                  ->nullable();
            $table->date('next_check_date')->nullable();  // Prochaine visite

            // Pour formation
            $table->string('training_organization')->nullable();
            $table->date('renewal_date')->nullable();

            $table->string('file_path')->nullable();      // Scan PDF/image

            $table->enum('status', ['valid', 'expiring_soon', 'expired', 'missing'])
                  ->default('valid');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            // Index pour alertes
            $table->index(['driver_id', 'type']);
            $table->index(['expiry_date', 'status']);
            $table->index('next_check_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_documents');
    }
};