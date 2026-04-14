<?php

use App\Services\AlertService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            $landlordDomain = env('LANDLORD_DOMAIN', 'miensafleet.ci');

            // Routes publiques landlord (pricing, signup) — domaine principal seulement
            \Illuminate\Support\Facades\Route::middleware('web')
                ->domain($landlordDomain)
                ->group(base_path('routes/landlord.php'));

            // Routes panel propriétaire — sous-domaine admin uniquement
            \Illuminate\Support\Facades\Route::middleware('web')
                ->domain('admin.' . $landlordDomain)
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ── Aliases de middleware ─────────────────────────────────────────
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            // Appliqué uniquement sur le groupe de routes tenant (routes/web.php)
            // Sur les routes landlord (pricing, signup), ce middleware N'EST PAS utilisé.
            'needs.tenant'       => \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // ── Vérifications d'alertes quotidiennes ─────────────────────────
        // Exécutées chaque matin à 07h00 pour peupler le module Alertes.

        // Documents véhicules (assurance, visite technique)
        $schedule->call(fn () => app(AlertService::class)->checkVehicleDocuments())
                 ->dailyAt('07:00')
                 ->name('alerts:vehicle-documents')
                 ->withoutOverlapping();

        // Documents chauffeurs (permis, visite médicale)
        $schedule->call(fn () => app(AlertService::class)->checkDriverDocuments())
                 ->dailyAt('07:05')
                 ->name('alerts:driver-documents')
                 ->withoutOverlapping();

        // Vidanges à prévoir ou dépassées (J-15 → warning, dépassée → critical)
        $schedule->call(fn () => app(AlertService::class)->checkOilChangesDue())
                 ->dailyAt('07:10')
                 ->name('alerts:oil-changes')
                 ->withoutOverlapping();

        // Pièces sous garantie expirant dans 30 jours
        $schedule->call(fn () => app(AlertService::class)->checkPartsUnderWarranty())
                 ->dailyAt('07:15')
                 ->name('alerts:parts-warranty')
                 ->withoutOverlapping();

        // Réparations en cours sans retour depuis N jours
        $schedule->call(fn () => app(AlertService::class)->checkRepairsOverdue())
                 ->dailyAt('07:20')
                 ->name('alerts:repairs-overdue')
                 ->withoutOverlapping();

        // ── Vérifications toutes les heures ──────────────────────────────

        // Retours en retard (affectations + demandes de véhicule)
        $schedule->call(fn () => app(AlertService::class)->checkOverdueReturns())
                 ->hourly()
                 ->name('alerts:overdue-returns')
                 ->withoutOverlapping();

        // Demandes sans réponse depuis > 4h
        $schedule->call(fn () => app(AlertService::class)->checkPendingRequestsTimeout())
                 ->everyThirtyMinutes()
                 ->name('alerts:pending-requests')
                 ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Quand une route tenant est accédée depuis le domaine landlord
        // (ex: miensa-fleet.test/login au lieu de dev.miensa-fleet.test/login),
        // rediriger vers la page d'accueil publique plutôt qu'afficher une erreur 500.
        $exceptions->render(function (
            \Spatie\Multitenancy\Exceptions\NoCurrentTenant $e,
            \Illuminate\Http\Request $request
        ) {
            return redirect()
                ->route('landlord.home')
                ->with('info', 'Veuillez accéder au panel via votre sous-domaine dédié.');
        });
    })->create();
