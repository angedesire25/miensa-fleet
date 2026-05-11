<?php

use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes API — Synchronisation hors ligne (PWA)
|
| Ces routes utilisent la session web (cookies) via le middleware 'web'
| enregistré dans bootstrap/app.php. Le SW envoie toujours le cookie
| de session + l'en-tête X-CSRF-TOKEN (credentials: 'include').
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'needs.tenant'])->group(function () {
    Route::post('/sync/inspections', [SyncController::class, 'syncInspections'])
        ->name('api.sync.inspections');

    Route::post('/sync/trips', [SyncController::class, 'syncTripLogs'])
        ->name('api.sync.trips');
});
