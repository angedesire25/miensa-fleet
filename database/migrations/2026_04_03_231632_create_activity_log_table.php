<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal d'audit complet — Spatie Laravel Activitylog.
     * Enregistre TOUTES les actions sensibles avec :
     *   - Qui a fait quoi (causer)
     *   - Sur quel objet (subject)
     *   - Quelles données ont changé (properties: old/attributes)
     *   - Depuis quelle IP
     */
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('log_name')->nullable()->index();
            // Ex: 'vehicles', 'assignments', 'users', 'infractions', 'auth'

            $table->string('description');
            // Ex: 'created', 'updated', 'deleted', 'login', 'impersonated'

            // Objet concerné (polymorphique)
            $table->nullableMorphs('subject', 'subject');
            // → subject_type = 'App\Models\Vehicle', subject_id = 42

            // Qui a effectué l'action (polymorphique)
            $table->nullableMorphs('causer', 'causer');
            // → causer_type = 'App\Models\User', causer_id = 7

            // Données avant/après modification
            $table->json('properties')->nullable();
            // {
            //   "old": { "status": "available" },
            //   "attributes": { "status": "on_mission" },
            //   "ip": "192.168.1.10",
            //   "user_agent": "Mozilla/5.0..."
            // }

            $table->uuid('batch_uuid')->nullable()->index();

            $table->timestamp('created_at')->nullable()->index();
            $table->timestamp('updated_at')->nullable();

            // Index pour l'interface d'audit
            $table->index(['log_name', 'created_at']);
            $table->index('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};