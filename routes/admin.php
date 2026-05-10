<?php

use App\Http\Controllers\LandlordAdmin\AuthController;
use App\Http\Controllers\LandlordAdmin\DashboardController;
use App\Http\Controllers\LandlordAdmin\PlanController;
use App\Http\Controllers\LandlordAdmin\ProfileController;
use App\Http\Controllers\LandlordAdmin\PromotionController;
use App\Http\Controllers\LandlordAdmin\SiteSettingController;
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
    Route::post('/tenants/{tenant}/suspendre', [TenantAdminController::class, 'suspend'])->name('admin.tenants.suspend');
    Route::post('/tenants/{tenant}/activer',   [TenantAdminController::class, 'activate'])->name('admin.tenants.activate');
    Route::post('/tenants/{tenant}/plan',      [TenantAdminController::class, 'changePlan'])->name('admin.tenants.changePlan');
    Route::delete('/tenants/{tenant}',                    [TenantAdminController::class, 'destroy'])->name('admin.tenants.destroy');
    Route::post('/tenants/{tenant}/reinitialiser-acces', [TenantAdminController::class, 'resetAccess'])->name('admin.tenants.resetAccess');
    Route::get('/tenants/{tenant}/acceder',              [TenantAdminController::class, 'impersonate'])->name('admin.tenants.impersonate');

    // Profil super admin
    Route::get('/profil',              [ProfileController::class, 'index'])->name('admin.profile');
    Route::put('/profil/infos',        [ProfileController::class, 'updateInfo'])->name('admin.profile.info');
    Route::put('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');

    // Paramètres du site
    Route::get('/parametres',  [SiteSettingController::class, 'index'])->name('admin.settings.index');
    Route::put('/parametres',  [SiteSettingController::class, 'update'])->name('admin.settings.update');

    // Plans & Tarifs
    Route::get('/plans',                      [PlanController::class, 'index'])->name('admin.plans.index');
    Route::get('/plans/{plan}/modifier',      [PlanController::class, 'edit'])->name('admin.plans.edit');
    Route::put('/plans/{plan}',               [PlanController::class, 'update'])->name('admin.plans.update');
    Route::patch('/plans/{plan}/toggle',      [PlanController::class, 'toggleActive'])->name('admin.plans.toggle');

    // Promotions
    Route::get('/promotions',                 [PromotionController::class, 'index'])->name('admin.promotions.index');
    Route::get('/promotions/nouvelle',        [PromotionController::class, 'create'])->name('admin.promotions.create');
    Route::post('/promotions',                [PromotionController::class, 'store'])->name('admin.promotions.store');
    Route::get('/promotions/{promotion}/modifier', [PromotionController::class, 'edit'])->name('admin.promotions.edit');
    Route::put('/promotions/{promotion}',     [PromotionController::class, 'update'])->name('admin.promotions.update');
    Route::delete('/promotions/{promotion}',  [PromotionController::class, 'destroy'])->name('admin.promotions.destroy');
    Route::patch('/promotions/{promotion}/toggle',    [PromotionController::class, 'toggleActive'])->name('admin.promotions.toggle');
    Route::patch('/promotions/{promotion}/archiver',  [PromotionController::class, 'archive'])->name('admin.promotions.archive');
    Route::patch('/promotions/{promotion}/restaurer', [PromotionController::class, 'unarchive'])->name('admin.promotions.unarchive');
});
