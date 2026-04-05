<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `incidents` : sinistres, pannes et événements négatifs affectant un véhicule.
     *
     * Distinction avec `infractions` :
     *   - Une infraction concerne le CONDUCTEUR (comportement au volant)
     *   - Un incident concerne le VÉHICULE (état mécanique, sinistre, vol…)
     *
     * Un incident peut impliquer un tiers (accident de la route), déclencher
     * une déclaration de sinistre auprès de l'assurance et générer un ordre
     * de réparation vers un garage (table `repairs`).
     *
     * Cycle de vie : open → at_garage → repaired / total_loss / closed
     */
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            // ── Véhicule et conducteur impliqués ────────────────────────────

            // Véhicule concerné — toujours renseigné ; restrict pour préserver
            // l'historique des sinistres si le véhicule est archivé
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            // Conducteur professionnel au moment du sinistre (null si inconnu)
            $table->foreignId('driver_id')
                  ->nullable()
                  ->constrained('drivers')
                  ->nullOnDelete();

            // Collaborateur non-chauffeur au volant (si demande de véhicule)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Contexte opérationnel lors de l'incident
            $table->foreignId('assignment_id')
                  ->nullable()
                  ->constrained('assignments')
                  ->nullOnDelete();

            $table->foreignId('request_id')
                  ->nullable()
                  ->constrained('vehicle_requests')
                  ->nullOnDelete();

            // ── Classification de l'incident ────────────────────────────────

            $table->enum('type', [
                'accident',         // Accident de la route (collision, renversement…)
                'breakdown',        // Panne mécanique (moteur, boîte de vitesses…)
                'flat_tire',        // Crevaison ou pneu endommagé
                'electrical_fault', // Panne électrique (batterie, alternateur…)
                'body_damage',      // Dommage carrosserie sans accident tiers
                'theft_attempt',    // Tentative de vol (effraction, vitre brisée…)
                'theft',            // Vol du véhicule ou de ses équipements
                'flood_damage',     // Dommage lié à une inondation
                'fire',             // Incendie (partiel ou total)
                'vandalism',        // Acte de vandalisme (tags, rétroviseurs…)
                'other',            // Tout autre événement non catégorisé
            ]);

            // Gravité du sinistre — impacte les délais et coûts de remise en état
            $table->enum('severity', [
                'minor',      // Dommage léger, véhicule utilisable
                'moderate',   // Réparation nécessaire, courte immobilisation
                'major',      // Réparation lourde, longue immobilisation
                'total_loss', // Épave — véhicule irrécupérable
            ]);

            // ── Détails de l'incident ────────────────────────────────────────

            $table->dateTime('datetime_occurred');              // Date et heure exactes
            $table->string('location')->nullable();             // Lieu (adresse, ville, route…)
            $table->text('description');                        // Récit détaillé des circonstances

            // ── Tiers impliqués (accident de la route) ──────────────────────

            $table->boolean('third_party_involved')->default(false); // Présence d'un tiers
            $table->string('third_party_name')->nullable();          // Nom / raison sociale
            $table->string('third_party_plate')->nullable();         // Plaque du tiers
            $table->string('third_party_insurance')->nullable();     // Assurance du tiers

            // Procès-verbal de police
            $table->string('police_report_number')->nullable();      // Numéro du PV officiel
            $table->string('police_report_path')->nullable();        // Chemin du scan du PV

            // ── Déclaration de sinistre (assurance) ─────────────────────────

            $table->boolean('insurance_declared')->default(false);   // Sinistre déclaré à l'assurance
            $table->string('insurance_claim_number')->nullable();     // Numéro de dossier assurance
            $table->date('insurance_declaration_date')->nullable();   // Date de déclaration
            $table->decimal('insurance_amount_claimed', 12, 2)->nullable();  // Montant réclamé
            $table->decimal('insurance_amount_received', 12, 2)->nullable(); // Montant effectivement versé

            $table->enum('insurance_status', [
                'not_declared', // Sinistre non déclaré (incident mineur ou pris en charge en interne)
                'declared',     // Déclaré, en attente de traitement par l'assureur
                'processing',   // En cours d'expertise ou d'instruction
                'settled',      // Indemnisation versée — dossier clôturé
                'rejected',     // Indemnisation refusée par l'assureur
            ])->nullable();     // Null tant que insurance_declared = false

            // ── Statut global du sinistre ────────────────────────────────────

            $table->enum('status', [
                'open',       // Signalé, en cours de traitement
                'at_garage',  // Véhicule déposé au garage
                'repaired',   // Réparé et retourné
                'total_loss', // Perte totale — véhicule déclaré épave
                'closed',     // Clôturé sans réparation (sinistre mineur ou prise en charge refusée)
            ])->default('open');

            // ── Impact sur le véhicule ───────────────────────────────────────

            $table->boolean('vehicle_immobilized')->default(false);        // Véhicule hors service
            $table->decimal('estimated_repair_cost', 12, 2)->nullable();   // Estimation avant réparation
            $table->decimal('actual_repair_cost', 12, 2)->nullable();      // Coût réel après réparation

            // ── Traçabilité ─────────────────────────────────────────────────

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ── Index ────────────────────────────────────────────────────────

            $table->index(['vehicle_id', 'datetime_occurred']); // Historique sinistres par véhicule
            $table->index('status');                            // File de traitement
            $table->index('type');                              // Filtrage par type de sinistre
            $table->index('driver_id');                         // Sinistres par chauffeur
            $table->index('insurance_status');                  // Suivi des déclarations assurance
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
