<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `activity_log` : journal d'audit complet (Spatie Laravel Activitylog v4).
     *
     * Enregistre toutes les actions sensibles sur les modèles configurés avec LogsActivity :
     *   - Qui a fait quoi  → causer_type / causer_id (polymorphique, généralement User)
     *   - Sur quel objet   → subject_type / subject_id (polymorphique : Vehicle, Driver…)
     *   - Quelles données  → properties.old / properties.attributes (avant/après)
     *   - Quel contexte    → log_name (par domaine : 'vehicles', 'drivers', 'users'…)
     *
     * Modèles audités : User, Vehicle, VehicleDocument, Driver, DriverDocument,
     *                   Assignment, VehicleRequest, Infraction.
     *
     * Note : `description` est en VARCHAR(255) au lieu de TEXT pour permettre
     * l'indexation MySQL. Les descriptions courtes ('created', 'updated', 'deleted')
     * ne dépassent jamais cette limite.
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