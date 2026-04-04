<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `cache` : cache applicatif Laravel (driver = database).
     * Stocke les entrées du cache avec leur clé unique, valeur sérialisée
     * et timestamp Unix d'expiration.
     *
     * Table `cache_locks` : verrous distribués pour les opérations atomiques
     * (rate limiting, mutex). Chaque verrou possède un propriétaire et une
     * expiration Unix.
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();       // Clé unique du cache
            $table->mediumText('value');             // Valeur sérialisée (PHP serialize)
            $table->bigInteger('expiration')->index(); // Timestamp Unix d'expiration
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();         // Identifiant du verrou
            $table->string('owner');                  // Propriétaire du verrou (token aléatoire)
            $table->bigInteger('expiration')->index(); // Timestamp Unix d'expiration
        });
    }

    /**
     * Supprime les tables de cache.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
