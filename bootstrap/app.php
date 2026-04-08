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
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
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
        //
    })->create();
