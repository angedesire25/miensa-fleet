<?php

use App\Http\Controllers\Landlord\PricingController;
use App\Http\Controllers\Landlord\SignupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Landlord (domaine principal : miensafleet.ci)
|--------------------------------------------------------------------------
| Ces routes sont chargées SANS le middleware needs.tenant.
| Elles sont accessibles depuis miensafleet.ci (ou miensafleet.test en dev).
|--------------------------------------------------------------------------
*/

// ── Page d'accueil / Pricing ──────────────────────────────────────────────
Route::get('/', [PricingController::class, 'index'])->name('landlord.home');
Route::get('/tarifs', [PricingController::class, 'index'])->name('landlord.pricing');

// ── Inscription (création de compte tenant) ───────────────────────────────
Route::get('/inscription', [SignupController::class, 'create'])->name('landlord.signup');
Route::post('/inscription', [SignupController::class, 'store'])->name('landlord.signup.store');
Route::get('/inscription/succes', [SignupController::class, 'success'])->name('landlord.signup.success');

// ── Contact ───────────────────────────────────────────────────────────────
Route::get('/contact', [PricingController::class, 'contact'])->name('landlord.contact');
