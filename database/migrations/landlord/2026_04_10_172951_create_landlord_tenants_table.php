<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table landlord : tenants (sociétés clientes).
 *
 * Connexion : 'landlord' — jamais commutée vers la base tenant.
 * Chaque tenant dispose d'une base MySQL dédiée : miensafleet_{slug}.
 */
return new class extends Migration
{
    public function getConnection(): string { return 'landlord'; }

    public function up(): void
    {
        Schema::connection('landlord')->create('tenants', function (Blueprint $table) {
            $table->id();

            // Identité
            $table->string('name');                         // "Geomatos SARL"
            $table->string('slug')->unique();               // "geomatos"
            $table->string('domain')->unique();             // "geomatos.miensafleet.ci"

            // Base de données dédiée
            $table->string('database')->unique();           // "miensafleet_geomatos"

            // Plan souscrit (FK ajoutée après la table plans)
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();

            // Statut du compte
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled'])
                  ->default('trial');

            // Dates clés
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('suspended_at')->nullable();

            // Contact principal
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            // Localisation
            $table->string('country')->default('CI');
            $table->string('timezone')->default('Africa/Abidjan');

            // Limites du plan (dénormalisées pour perf)
            $table->unsignedInteger('max_vehicles')->default(5);
            $table->unsignedInteger('max_users')->default(3);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('tenants');
    }
};
