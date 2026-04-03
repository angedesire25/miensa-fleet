<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            $table->enum('type', [
                'insurance',          // Assurance
                'technical_control',  // Visite technique
                'registration',       // Carte grise
                'transport_permit',   // Autorisation de transport
                'other',
            ]);

            $table->string('document_number')->nullable();   // Numéro de pièce
            $table->date('issue_date')->nullable();          // Date d'émission
            $table->date('expiry_date')->nullable();         // Date d'expiration
            $table->string('issuing_authority')->nullable(); // Organisme émetteur
            $table->string('file_path')->nullable();         // Chemin du scan PDF/image

            $table->enum('status', ['valid', 'expiring_soon', 'expired', 'missing'])
                  ->default('valid');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            // Index pour les requêtes d'alertes
            $table->index(['vehicle_id', 'type']);
            $table->index(['expiry_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_documents');
    }
};