<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `vehicle_photos` : stockage centralisé de toutes les photos liées aux véhicules.
     *
     * Architecture polymorphique : chaque photo appartient toujours à un véhicule
     * (vehicle_id obligatoire) et peut être attachée en supplément à n'importe quel
     * autre modèle via le couple (photoable_type, photoable_id).
     *
     * Modèles pouvant être "photoable" :
     *   App\Models\Incident        → photos de sinistre / dégâts
     *   App\Models\Repair          → photos de réparation
     *   App\Models\Assignment      → photos départ / retour d'affectation
     *   App\Models\VehicleRequest  → photos départ / retour de demande
     *   null                       → photo de profil ou vue générale du véhicule
     *
     * Le champ `context` précise la nature de la photo indépendamment du modèle
     * lié, ce qui permet des requêtes ciblées (ex : toutes les photos 'return'
     * d'un véhicule sur une période) sans avoir à joindre tous les modèles.
     */
    public function up(): void
    {
        Schema::create('vehicle_photos', function (Blueprint $table) {
            $table->id();

            // Véhicule propriétaire de la photo — toujours renseigné
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();

            // Relation polymorphique optionnelle vers le contexte précis
            // (Incident, Repair, Assignment, VehicleRequest…)
            $table->string('photoable_type')->nullable(); // Classe du modèle lié
            $table->unsignedBigInteger('photoable_id')->nullable(); // ID du modèle lié

            // Contexte sémantique de la photo
            $table->enum('context', [
                'vehicle_profile',    // Photo principale du véhicule (fiche)
                'vehicle_exterior',   // Extérieur — 4 faces (avant, arrière, gauche, droite)
                'vehicle_interior',   // Intérieur (tableau de bord, sièges…)
                'incident_before',    // État du véhicule avant sinistre / à l'arrivée au garage
                'incident_damage',    // Dégâts constatés après sinistre
                'repair_in_progress', // Réparation en cours (intérieur garage)
                'repair_after',       // État du véhicule après réparation
                'departure',          // Photo prise avant départ (affectation ou demande)
                'return',             // Photo prise au retour du véhicule
            ]);

            // Fichier physique
            $table->string('file_path');                       // Chemin relatif dans storage/app/public/
            $table->string('original_name')->nullable();       // Nom du fichier tel qu'uploadé
            $table->string('mime_type')->nullable();           // image/jpeg, image/png…
            $table->unsignedInteger('size_kb')->nullable();    // Taille en kilo-octets

            // Métadonnées optionnelles
            $table->string('caption')->nullable();             // Légende libre
            $table->timestamp('taken_at')->nullable();         // Date/heure de prise de vue (EXIF ou manuelle)

            // Auteur de l'upload
            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // ── Index ───────────────────────────────────────────────────────

            // Recherche des photos d'un véhicule par contexte (galerie, rapports)
            $table->index(['vehicle_id', 'context']);

            // Recherche polymorphique — chargement des photos d'un modèle lié
            $table->index(['photoable_type', 'photoable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photos');
    }
};
