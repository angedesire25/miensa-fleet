<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plans tarifaires : Gratuit / Essentiel / Pro
 */
return new class extends Migration
{
    public function getConnection(): string { return 'landlord'; }

    public function up(): void
    {
        Schema::connection('landlord')->create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');                         // "Pro"
            $table->string('slug')->unique();               // "pro"
            $table->text('description')->nullable();

            // Tarification mensuelle (FCFA)
            $table->decimal('price_monthly', 10, 2)->default(0);
            // Tarification annuelle (FCFA) — remise appliquée
            $table->decimal('price_yearly', 10, 2)->default(0);

            // Limites fonctionnelles
            $table->unsignedInteger('max_vehicles')->default(5);
            $table->unsignedInteger('max_users')->default(3);
            $table->unsignedInteger('max_drivers')->default(10);

            // Fonctionnalités activées (flags)
            $table->boolean('has_repairs')->default(true);
            $table->boolean('has_infractions')->default(true);
            $table->boolean('has_incidents')->default(true);
            $table->boolean('has_inspections')->default(true);
            $table->boolean('has_reports')->default(false);
            $table->boolean('has_api')->default(false);

            // Durée d'essai (jours)
            $table->unsignedInteger('trial_days')->default(14);

            // Ordre d'affichage sur la page pricing
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);   // Badge "Recommandé"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('plans');
    }
};
