<?php

use App\Http\Controllers\LandlordAdmin\AuthController;
use App\Http\Controllers\LandlordAdmin\DashboardController;
use App\Http\Controllers\LandlordAdmin\TenantAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Landlord Admin — admin.miensafleet.ci / admin.miensa-fleet.test
| Chargées avec une contrainte de domaine dans bootstrap/app.php.
| Guard : 'landlord' (base centrale, table landlord_users)
|--------------------------------------------------------------------------
*/

// ── Authentification ──────────────────────────────────────────────────────────
Route::middleware('guest:landlord')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:landlord')
    ->name('admin.logout');

// ── Panel (protégé) ───────────────────────────────────────────────────────────
Route::middleware('auth:landlord')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Tenants
    Route::get('/tenants',                    [TenantAdminController::class, 'index'])->name('admin.tenants.index');
    Route::get('/tenants/creer',              [TenantAdminController::class, 'create'])->name('admin.tenants.create');
    Route::post('/tenants',                   [TenantAdminController::class, 'store'])->name('admin.tenants.store');
    Route::get('/tenants/{tenant}',           [TenantAdminController::class, 'show'])->name('admin.tenants.show');
    Route::post('/tenants/{tenant}/suspendre',[TenantAdminController::class, 'suspend'])->name('admin.tenants.suspend');
    Route::post('/tenants/{tenant}/activer',  [TenantAdminController::class, 'activate'])->name('admin.tenants.activate');
    Route::post('/tenants/{tenant}/plan',     [TenantAdminController::class, 'changePlan'])->name('admin.tenants.changePlan');
    Route::get('/tenants/{tenant}/acceder',   [TenantAdminController::class, 'impersonate'])->name('admin.tenants.impersonate');
});
