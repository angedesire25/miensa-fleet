<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rend la colonne `phone` nullable dans `drivers`.
 *
 * Raison : lorsqu'un compte utilisateur avec le rôle `driver_user`
 * est créé sans numéro de téléphone, le service DriverProfileService
 * tente de créer automatiquement un profil chauffeur minimal.
 * Le champ `phone` peut être vide à ce stade et doit être complété
 * ultérieurement via la fiche chauffeur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Attention : les lignes sans téléphone doivent être corrigées avant de rejouer ce rollback
            $table->string('phone', 30)->nullable(false)->change();
        });
    }
};
