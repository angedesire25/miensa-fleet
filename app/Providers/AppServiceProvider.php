<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\DriverDocument;
use App\Models\Infraction;
use App\Models\VehicleDocument;
use App\Models\VehicleRequest;
use App\Observers\AssignmentObserver;
use App\Observers\DriverDocumentObserver;
use App\Observers\InfractionObserver;
use App\Observers\VehicleDocumentObserver;
use App\Observers\VehicleRequestObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistrement des services applicatifs.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap des services applicatifs.
     *
     * Tous les observers sont centralisés ici pour :
     *   - Avoir une vue d'ensemble des effets de bord en un seul endroit
     *   - Permettre leur désactivation globale en tests via Model::withoutObservers()
     *
     * Exception : Driver utilise #[ObservedBy(DriverObserver::class)] directement
     * sur la classe car son observer a été créé avant cette centralisation.
     *
     * Observers enregistrés :
     *   Assignment           → statut véhicule, current_driver_id, stats chauffeur
     *   VehicleRequest       → statut véhicule selon cycle de vie de la demande
     *   Infraction           → auto-identification conducteur, total_infractions
     *   DriverDocument       → recalcul automatique du statut selon expiry_date
     *   VehicleDocument      → recalcul automatique du statut selon expiry_date
     */
    public function boot(): void
    {
        Assignment::observe(AssignmentObserver::class);
        VehicleRequest::observe(VehicleRequestObserver::class);
        Infraction::observe(InfractionObserver::class);
        DriverDocument::observe(DriverDocumentObserver::class);
        VehicleDocument::observe(VehicleDocumentObserver::class);
    }
}
