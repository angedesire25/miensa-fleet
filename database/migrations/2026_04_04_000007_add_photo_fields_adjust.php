<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Migration de compatibilité — pas de DDL exécuté.
     *
     * Contexte :
     *   La colonne `vehicles.photos` (JSON) a été créée lors de la migration
     *   initiale `create_vehicles_table` pour stocker un tableau de chemins de
     *   photos attachées au véhicule (photos de profil, galerie rapide…).
     *
     * Évolution :
     *   La migration `create_vehicle_photos_table` introduit une table dédiée
     *   (`vehicle_photos`) avec un modèle polymorphique, des contextes sémantiques
     *   (vehicle_profile, departure, return, incident_damage…), des métadonnées
     *   (mime_type, size_kb, taken_at) et une traçabilité de l'upload.
     *
     * Décision :
     *   - `vehicles.photos` (JSON) est CONSERVÉ pour compatibilité descendante.
     *     Les éventuelles données existantes ne sont pas migrées automatiquement.
     *   - La SOURCE DE VÉRITÉ devient désormais la table `vehicle_photos`.
     *   - Le champ JSON ne doit plus être alimenté par de nouvelles saisies.
     *     Il sera supprimé dans une migration future après vérification que
     *     toutes les données ont été migrées vers `vehicle_photos`.
     *
     * Action requise côté application :
     *   - Vehicle::$fillable : retirer 'photos'
     *   - Vehicle::$casts    : retirer 'photos' => 'array'
     *   - Toute écriture sur Vehicle::photos doit être remplacée par
     *     une création dans VehiclePhoto
     */
    public function up(): void
    {
        // Aucune modification DDL intentionnelle dans cette migration.
        // La colonne `vehicles.photos` est conservée telle quelle.
    }

    public function down(): void
    {
        // Rien à annuler.
    }
};
