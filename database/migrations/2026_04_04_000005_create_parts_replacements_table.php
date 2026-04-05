<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `parts_replacements` : historique détaillé de chaque pièce remplacée.
     *
     * ⚠️ CLÉ MÉTIER — durée de vie réelle des pièces :
     *   Cette table permet de répondre à la question :
     *   "La batterie changée le 01/01 a-t-elle lâché 2 jours, 3 semaines ou 2 ans après ?"
     *
     * Relation avec `repairs.parts_replaced` (JSON) :
     *   Le champ JSON sur `repairs` est le résumé humain saisi par le garage.
     *   Cette table est sa version structurée, une ligne par pièce, permettant
     *   des requêtes analytiques précises sur les durées de vie, les marques
     *   défaillantes et le suivi des garanties.
     *
     * Cycle de vie d'une pièce : active → failed → replaced / removed
     *
     * Calculs automatiques (côté application, au moment de la saisie) :
     *   - `days_until_failure`       = failed_at - replaced_at
     *   - `under_warranty_at_failure` = (failed_at <= warranty_expiry)
     *   - `warranty_expiry`          = replaced_at + warranty_months (mois)
     */
    public function up(): void
    {
        Schema::create('parts_replacements', function (Blueprint $table) {
            $table->id();

            // ── Liens contextuels ────────────────────────────────────────────

            // Véhicule sur lequel la pièce a été montée — toujours renseigné
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            // Réparation lors de laquelle la pièce a été posée (null = pose isolée)
            $table->foreignId('repair_id')
                  ->nullable()
                  ->constrained('repairs')
                  ->nullOnDelete();

            // Sinistre associé à ce remplacement de pièce (null si entretien préventif)
            $table->foreignId('incident_id')
                  ->nullable()
                  ->constrained('incidents')
                  ->nullOnDelete();

            // ── Identification de la pièce ───────────────────────────────────

            $table->enum('part_category', [
                'battery',       // Batterie
                'tire',          // Pneu (préciser la position dans notes : AV gauche, AR droit…)
                'brake_pad',     // Plaquettes de frein
                'brake_disc',    // Disque de frein
                'oil_filter',    // Filtre à huile
                'air_filter',    // Filtre à air
                'fuel_filter',   // Filtre à carburant
                'spark_plug',    // Bougie d'allumage
                'timing_belt',   // Courroie de distribution
                'alternator',    // Alternateur
                'starter',       // Démarreur
                'shock_absorber',// Amortisseur
                'water_pump',    // Pompe à eau
                'radiator',      // Radiateur
                'clutch',        // Embrayage (kit complet)
                'gearbox',       // Boîte de vitesses
                'engine_part',   // Pièce moteur générique
                'electrical',    // Pièce électrique (capteur, faisceau, relais…)
                'body_part',     // Pièce carrosserie (aile, pare-chocs, vitre…)
                'other',         // Toute pièce non catégorisée
            ]);

            $table->string('part_name');                             // Nom précis : "Batterie 12V 70Ah Bosch S4"
            $table->string('part_reference')->nullable();            // Référence constructeur / OEM
            $table->string('part_brand')->nullable();                // Marque : Bosch, Michelin, NGK…

            // ── Quantité et coût ─────────────────────────────────────────────

            $table->tinyInteger('quantity')->unsigned()->default(1); // Nombre de pièces posées
            $table->decimal('unit_cost', 10, 2)->nullable();        // Coût unitaire HT
            $table->decimal('total_cost', 10, 2)->nullable();       // quantity × unit_cost (calculé en app)

            // ── Pose ─────────────────────────────────────────────────────────

            $table->date('replaced_at');                             // ⚠️ Date de remplacement — CRITIQUE pour calcul durée de vie
            $table->unsignedInteger('km_at_replacement')->nullable();// Kilométrage au moment de la pose

            // Garage ayant effectué la pose
            $table->foreignId('replaced_by_garage')
                  ->nullable()
                  ->constrained('garages')
                  ->nullOnDelete();

            // Garantie sur la pièce et la main d'œuvre
            $table->tinyInteger('warranty_months')->unsigned()->nullable();  // Durée de la garantie en mois
            $table->date('warranty_expiry')->nullable();                     // replaced_at + warranty_months (calculé en app)

            // ── Suivi de durée de vie ⚠️ CLÉ MÉTIER ─────────────────────────

            // Date à laquelle la défaillance a été constatée
            // (null = pièce encore en service ou retirée sans panne)
            $table->date('failed_at')->nullable();

            // Réparation lors de laquelle cette pièce a été déclarée défaillante
            // Permet de tracer : pose → défaillance → nouvelle réparation
            $table->foreignId('failure_reported_in_repair_id')
                  ->nullable()
                  ->constrained('repairs')
                  ->nullOnDelete();

            // Nombre de jours entre la pose et la défaillance
            // Calculé automatiquement en app : failed_at - replaced_at
            $table->unsignedSmallInteger('days_until_failure')->nullable();

            $table->text('failure_reason')->nullable();             // Description de la cause de défaillance

            // Vrai si la défaillance est survenue dans la période de garantie
            // Calculé automatiquement en app : failed_at <= warranty_expiry
            $table->boolean('under_warranty_at_failure')->nullable();

            // ── Statut de la pièce ───────────────────────────────────────────

            $table->enum('status', [
                'active',   // Pièce montée, en service
                'failed',   // Pièce défaillante (failed_at renseigné)
                'replaced', // Remplacée par une nouvelle pièce
                'removed',  // Retirée sans remplacement (véhicule vendu, hors service…)
            ])->default('active');

            // ── Métadonnées ──────────────────────────────────────────────────

            $table->text('notes')->nullable();                      // Position du pneu, observations mécanicien…

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            // Pas de softDeletes : l'historique des pièces est une donnée comptable
            // et de traçabilité — il ne doit pas être masqué par soft delete.

            // ── Index ────────────────────────────────────────────────────────

            // Recherche de l'historique pièces d'un véhicule par catégorie
            $table->index(['vehicle_id', 'part_category']);

            // Requêtes chronologiques sur les poses et les défaillances
            $table->index('replaced_at');
            $table->index('failed_at');

            // File de gestion (pièces actives, défaillantes, sous garantie…)
            $table->index('status');
            $table->index('under_warranty_at_failure');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts_replacements');
    }
};
