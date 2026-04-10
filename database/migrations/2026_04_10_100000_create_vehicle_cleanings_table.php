<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_cleanings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            // Responsable du nettoyage : chauffeur professionnel OU collaborateur
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('scheduled_date');            // toujours un samedi
            $table->time('scheduled_time')->default('08:00'); // heure prévue

            $table->enum('cleaning_type', ['exterior', 'interior', 'full'])->default('full');
            // exterior = Extérieur, interior = Intérieur, full = Complet

            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'missed', 'cancelled'])
                  ->default('scheduled');

            $table->text('notes')->nullable();          // instructions gestionnaire

            // Confirmation par le chauffeur/responsable
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();

            // Complétion
            $table->timestamp('completed_at')->nullable();
            $table->string('completion_proof')->nullable(); // photo preuve (chemin)
            $table->text('completion_notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_cleanings');
    }
};
