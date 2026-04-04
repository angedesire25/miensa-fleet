<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `jobs` : file d'attente des tâches asynchrones Laravel (queue worker).
     * Utilisée pour les envois d'e-mails, SMS, génération de PDF (bons de sortie),
     * calcul des alertes, mise à jour des statistiques chauffeur, etc.
     *
     * Table `job_batches` : lots de tâches groupées (Bus::batch).
     * Permet le suivi de l'avancement d'un groupe de jobs (ex: envoi massif d'alertes)
     * et leur annulation.
     *
     * Table `failed_jobs` : archive des tâches échouées après épuisement des tentatives.
     * Permet le ré-essai manuel via : php artisan queue:retry {uuid}
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();                 // Nom de la file (default, emails…)
            $table->longText('payload');                      // Tâche sérialisée (classe + données)
            $table->unsignedTinyInteger('attempts');          // Nombre de tentatives effectuées
            $table->unsignedInteger('reserved_at')->nullable(); // Timestamp de réservation par un worker
            $table->unsignedInteger('available_at');           // Timestamp de disponibilité (délai)
            $table->unsignedInteger('created_at');             // Timestamp de création
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();             // UUID du lot
            $table->string('name');                      // Nom descriptif du lot
            $table->integer('total_jobs');               // Nombre total de jobs dans le lot
            $table->integer('pending_jobs');             // Jobs encore en attente
            $table->integer('failed_jobs');              // Jobs échoués
            $table->longText('failed_job_ids');          // IDs des jobs échoués (JSON)
            $table->mediumText('options')->nullable();   // Options sérialisées (callbacks…)
            $table->integer('cancelled_at')->nullable(); // Timestamp d'annulation du lot
            $table->integer('created_at');               // Timestamp de création
            $table->integer('finished_at')->nullable();  // Timestamp de fin du lot
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();   // UUID unique pour identification lors du ré-essai
            $table->text('connection');         // Connexion de queue utilisée (database, redis…)
            $table->text('queue');              // Nom de la file
            $table->longText('payload');        // Tâche sérialisée originale
            $table->longText('exception');      // Stack trace de l'exception ayant causé l'échec
            $table->timestamp('failed_at')->useCurrent(); // Horodatage de l'échec
        });
    }

    /**
     * Supprime les tables de gestion des files d'attente.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
