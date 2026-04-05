<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table `repairs` : suivi des réparations et entretiens en garage.
     *
     * Un incident peut générer plusieurs réparations (ex : première intervention
     * partielle, puis retour garage pour persistance de la même panne).
     *
     * Cas particulier : `incident_id` est nullable pour permettre les envois
     * en entretien préventif (vidange, révision périodique…) sans sinistre associé.
     *
     * Récurrence de panne : si un véhicule revient pour la même panne après une
     * réparation précédente, `same_issue_recurrence = true`, `previous_repair_id`
     * pointe vers l'ancienne réparation et `recurrence_delay_days` est calculé
     * automatiquement (datetime_sent - previous_repair.datetime_returned).
     *
     * Cycle de vie : sent → diagnosing → repairing / waiting_parts
     *                    → completed → returned / returned_with_issue
     *
     * Lien avec migration 5 :
     *   Le champ JSON `parts_replaced` est le résumé humain des pièces remplacées.
     *   La table `parts_replacements` en est la version structurée avec quantités
     *   et coûts unitaires, alimentée à partir de cette même information.
     */
    public function up(): void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();

            // ── Liens contextuels ────────────────────────────────────────────

            // Sinistre à l'origine de la réparation (null = entretien préventif)
            $table->foreignId('incident_id')
                  ->nullable()
                  ->constrained('incidents')
                  ->nullOnDelete();

            // Véhicule concerné — toujours renseigné ; restrict pour préserver
            // l'historique si le véhicule est archivé
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            // Garage prestataire (null si prestataire non encore sélectionné)
            $table->foreignId('garage_id')
                  ->nullable()
                  ->constrained('garages')
                  ->nullOnDelete();

            // ── Classification de la réparation ─────────────────────────────

            $table->enum('repair_type', [
                'corrective', // Réparation curative suite à panne ou accident
                'preventive', // Entretien préventif (vidange, révision, filtre…)
                'warranty',   // Intervention sous garantie constructeur
                'recall',     // Rappel constructeur (campagne de mise à jour)
            ]);

            // ── Départ au garage ─────────────────────────────────────────────

            $table->dateTime('datetime_sent');                        // Date/heure de départ du véhicule
            $table->unsignedInteger('km_at_departure')->nullable();   // Kilométrage relevé au départ
            $table->text('condition_at_departure')->nullable();       // État du véhicule au départ

            // Responsable de l'envoi
            $table->foreignId('sent_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // ── Diagnostic ───────────────────────────────────────────────────

            $table->text('diagnosis')->nullable();                    // Diagnostic posé par le mécanicien
            $table->json('parts_to_replace')->nullable();             // Pièces identifiées à changer
                                                                      // Ex. : [{"ref":"HUB-4521","label":"Moyeu avant gauche"}]

            // ── Retour du garage ─────────────────────────────────────────────

            $table->dateTime('datetime_returned')->nullable();        // Date/heure de retour du véhicule
            $table->unsignedInteger('km_at_return')->nullable();      // Kilométrage relevé au retour
            $table->text('condition_at_return')->nullable();          // État du véhicule à la réception

            // Responsable de la réception
            $table->foreignId('received_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // ── Résultat de l'intervention ───────────────────────────────────

            $table->text('work_performed')->nullable();               // Description des travaux effectués
            $table->json('parts_replaced')->nullable();               // Pièces effectivement remplacées
                                                                      // ⚠️ Alimente la table `parts_replacements`
                                                                      // Ex. : [{"ref":"HUB-4521","qty":1,"unit_cost":85.00}]

            // ── ⚠️ Clé métier : récurrence de la même panne ─────────────────

            // Vrai si le véhicule revient pour exactement la même panne
            // qu'une réparation précédente → indicateur de qualité garage
            $table->boolean('same_issue_recurrence')->default(false);

            // Réparation précédente pour laquelle la même panne avait déjà été traitée
            $table->foreignId('previous_repair_id')
                  ->nullable()
                  ->constrained('repairs')
                  ->nullOnDelete();

            // Délai entre le retour du véhicule (réparation précédente) et ce renvoi
            // en garage — calculé automatiquement côté application :
            //   datetime_sent - previous_repair.datetime_returned
            $table->unsignedSmallInteger('recurrence_delay_days')->nullable();

            // ── Statut de la réparation ──────────────────────────────────────

            $table->enum('status', [
                'sent',                // Véhicule envoyé au garage, en attente de prise en charge
                'diagnosing',          // Diagnostic en cours chez le mécanicien
                'repairing',           // Réparation en cours
                'waiting_parts',       // En attente de pièces détachées
                'completed',           // Travaux terminés, véhicule prêt à être récupéré
                'returned',            // Véhicule restitué et remis en service
                'returned_with_issue', // Restitué mais problème persistant ⚠️ — nouveau suivi requis
            ])->default('sent');

            // ── Financier ────────────────────────────────────────────────────

            $table->decimal('quote_amount', 12, 2)->nullable();      // Montant du devis
            $table->decimal('invoice_amount', 12, 2)->nullable();    // Montant de la facture finale
            $table->string('invoice_number')->nullable();            // Numéro de facture
            $table->string('invoice_path')->nullable();              // Chemin du scan de la facture

            $table->enum('payment_status', [
                'unpaid',  // Facture non réglée
                'paid',    // Facture intégralement réglée
                'partial', // Acompte versé — solde en attente
            ])->nullable();                                          // Null tant qu'aucune facture

            $table->date('payment_date')->nullable();                // Date du règlement (total ou dernier acompte)

            // ── Garantie sur la réparation ───────────────────────────────────

            $table->tinyInteger('warranty_months')->unsigned()->nullable(); // Durée de la garantie (pièces + main d'œuvre)
            $table->date('warranty_expiry')->nullable();                    // Date d'expiration calculée auto
                                                                            // depuis datetime_returned + warranty_months

            // ── Métadonnées ──────────────────────────────────────────────────

            $table->text('notes')->nullable();                       // Observations libres (gestionnaire)

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ── Index ────────────────────────────────────────────────────────

            $table->index(['vehicle_id', 'status']);    // Réparations actives par véhicule
            $table->index('incident_id');               // Réparations issues d'un même sinistre
            $table->index('garage_id');                 // Activité par prestataire
            $table->index('same_issue_recurrence');     // Détection des récurrences de pannes
            $table->index('datetime_sent');             // Chronologie des envois
            $table->index('datetime_returned');         // Chronologie des retours
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
