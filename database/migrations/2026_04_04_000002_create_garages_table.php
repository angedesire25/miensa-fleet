<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `garages` : répertoire des garages et prestataires de réparation.
     *
     * Utilisée pour lier les incidents et les ordres de réparation à un prestataire
     * connu et approuvé par la société. Les garages peuvent être :
     *   - dealer           → concessionnaire de la marque
     *   - independent      → garage indépendant
     *   - official_service → service officiel agréé constructeur
     *   - roadside         → assistance routière / dépannage
     *
     * Le champ `specializations` (JSON) permet un filtrage rapide lors du choix
     * d'un prestataire selon le type de panne ou de dommage constaté.
     */
    public function up(): void
    {
        Schema::create('garages', function (Blueprint $table) {
            $table->id();

            // Identité du garage
            $table->string('name');                            // Raison sociale ou nom commercial

            $table->enum('type', [
                'dealer',           // Concessionnaire de la marque du véhicule
                'independent',      // Garage indépendant
                'official_service', // Service officiel agréé constructeur
                'roadside',         // Assistance routière / dépannage d'urgence
            ]);

            // Coordonnées
            $table->string('address')->nullable();             // Adresse postale complète
            $table->string('city')->nullable();                // Ville (utilisée pour l'index)
            $table->string('phone')->nullable();               // Numéro de téléphone principal
            $table->string('email')->nullable();               // Email de contact
            $table->string('contact_person')->nullable();      // Nom du contact privilégié

            // Compétences et évaluation interne
            $table->json('specializations')->nullable();       // Ex. ["body","engine","electrical","tires"]
            $table->tinyInteger('rating')->unsigned()->nullable(); // Note interne 1 à 5

            // Statut et notes
            $table->boolean('is_approved')->default(true);     // Garage approuvé par la société
            $table->text('notes')->nullable();                 // Observations internes (délais, tarifs…)

            // Traçabilité de la création
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ── Index ───────────────────────────────────────────────────────
            $table->index('city');         // Recherche géographique
            $table->index('is_approved'); // Filtrage des prestataires validés
            $table->index('type');        // Filtrage par catégorie
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garages');
    }
};
