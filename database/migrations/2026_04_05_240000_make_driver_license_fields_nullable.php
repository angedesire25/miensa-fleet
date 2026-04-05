<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rend les champs permis et contrat de la table `drivers` nullables.
 *
 * Contexte : quand un compte utilisateur reçoit le rôle `driver_user`,
 * un profil chauffeur est créé automatiquement avec les infos de base
 * (nom, email, téléphone). Les informations du permis de conduire
 * sont complétées ultérieurement par l'administrateur dans la fiche chauffeur.
 *
 * Champs rendus nullables :
 *   - license_number      : numéro de permis (unique, rempli plus tard)
 *   - license_categories  : catégories (B, C, D…)
 *   - license_expiry_date : date d'expiration du permis
 *   - hire_date           : date d'embauche (peut être inconnue à la création)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Utiliser DB::statement pour éviter la re-création des index existants
        // (->change() avec ->unique() tente d'ajouter un index déjà présent)
        \DB::statement('ALTER TABLE drivers MODIFY license_number VARCHAR(50) NULL');
        \DB::statement('ALTER TABLE drivers MODIFY license_categories JSON NULL');
        \DB::statement('ALTER TABLE drivers MODIFY license_expiry_date DATE NULL');
        \DB::statement('ALTER TABLE drivers MODIFY hire_date DATE NULL');
    }

    public function down(): void
    {
        \DB::statement('ALTER TABLE drivers MODIFY license_number VARCHAR(50) NOT NULL');
        \DB::statement('ALTER TABLE drivers MODIFY license_categories JSON NOT NULL');
        \DB::statement('ALTER TABLE drivers MODIFY license_expiry_date DATE NOT NULL');
        \DB::statement('ALTER TABLE drivers MODIFY hire_date DATE NOT NULL');
    }
};
