<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `users` : comptes utilisateurs de l'application.
     *
     * Regroupe tous les types d'acteurs :
     *   - Administrateurs / gestionnaires de flotte / contrôleurs
     *   - Collaborateurs (non-chauffeurs) pouvant faire des demandes de véhicule
     *   - Chauffeurs professionnels (liés à un profil Driver via driver_id)
     *
     * Les rôles fins sont gérés par Spatie Permission (table roles / model_has_roles).
     * La colonne `driver_id` est ajoutée dans add_foreign_keys_to_tables pour
     * éviter la dépendance circulaire avec la table `drivers`.
     *
     * Tables connexes créées dans cette même migration :
     *   - `password_reset_tokens` : tokens de réinitialisation de mot de passe
     *   - `sessions`              : sessions utilisateur (driver = database)
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // ── Identité ────────────────────────────────────────────────────
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->string('department')->nullable();   // Service / Département
            $table->string('job_title')->nullable();   // Poste / Fonction
            $table->string('avatar')->nullable();      // Chemin photo de profil

            // Statut
            $table->enum('status', ['active', 'suspended', 'pending', 'archived'])
                  ->default('pending');
            $table->string('suspension_reason')->nullable();

            // Sécurité
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Laravel standard
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // archivage sans suppression physique

            // Index
            $table->index('status');
            $table->index('department');
        });

        // Table de réinitialisation des mots de passe (Laravel standard)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions (si driver = database)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};