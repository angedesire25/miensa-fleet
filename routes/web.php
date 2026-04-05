<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleRequestController;
use Illuminate\Support\Facades\Route;

// ── Authentification ────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Application (protégé) ──────────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Contrôles (fiches de contrôle journalières) ────────────────────────
    Route::middleware('permission:inspections.view')->get('controles', [InspectionController::class, 'index'])->name('inspections.index');
    Route::middleware('permission:inspections.create')->get('controles/nouveau', [InspectionController::class, 'create'])->name('inspections.create');
    Route::middleware('permission:inspections.create')->post('controles', [InspectionController::class, 'store'])->name('inspections.store');
    Route::middleware('permission:inspections.view')->get('controles/{inspection}', [InspectionController::class, 'show'])->name('inspections.show');
    Route::middleware('permission:inspections.edit')->get('controles/{inspection}/modifier', [InspectionController::class, 'edit'])->name('inspections.edit');
    Route::middleware('permission:inspections.edit')->put('controles/{inspection}', [InspectionController::class, 'update'])->name('inspections.update');
    // Validation / rejet : réservés au gestionnaire, contrôleur et admins
    Route::middleware('permission:inspections.validate')->post('controles/{inspection}/valider', [InspectionController::class, 'validate'])->name('inspections.validate');
    Route::middleware('permission:inspections.validate')->post('controles/{inspection}/rejeter', [InspectionController::class, 'reject'])->name('inspections.reject');
    // Archivage : masquer une fiche sans la supprimer
    Route::middleware('permission:inspections.edit')->post('controles/{inspection}/archiver', [InspectionController::class, 'archive'])->name('inspections.archive');
    Route::middleware('permission:inspections.edit')->post('controles/{inspection}/desarchiver', [InspectionController::class, 'unarchive'])->name('inspections.unarchive');
    // Suppression d'une photo carrosserie
    Route::middleware('permission:inspections.edit')->delete('controles/{inspection}/photo', [InspectionController::class, 'deletePhoto'])->name('inspections.delete-photo');
    Route::middleware('permission:inspections.edit')->delete('controles/{inspection}', [InspectionController::class, 'destroy'])->name('inspections.destroy');

    // Profil utilisateur
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ── Véhicules ──────────────────────────────────────────────────────────
    Route::middleware('permission:vehicles.view')->get('vehicules', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::middleware('permission:vehicles.create')->get('vehicules/nouveau', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::middleware('permission:vehicles.create')->post('vehicules', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::middleware('permission:vehicles.delete')->post('vehicules/{id}/restaurer', [VehicleController::class, 'restore'])->name('vehicles.restore');
    Route::middleware('permission:vehicles.view')->get('vehicules/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::middleware('permission:vehicles.edit')->get('vehicules/{vehicle}/modifier', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::middleware('permission:vehicles.edit')->put('vehicules/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::middleware('permission:vehicles.edit')->post('vehicules/{vehicle}/statut', [VehicleController::class, 'toggleStatus'])->name('vehicles.toggle-status');
    Route::middleware('permission:vehicles.delete')->delete('vehicules/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    // ── Chauffeurs ─────────────────────────────────────────────────────────
    Route::middleware('permission:drivers.view')->get('chauffeurs', [DriverController::class, 'index'])->name('drivers.index');
    Route::middleware('permission:drivers.create')->get('chauffeurs/nouveau', [DriverController::class, 'create'])->name('drivers.create');
    Route::middleware('permission:drivers.create')->post('chauffeurs', [DriverController::class, 'store'])->name('drivers.store');
    Route::middleware('permission:drivers.delete')->post('chauffeurs/{id}/restaurer', [DriverController::class, 'restore'])->name('drivers.restore');
    Route::middleware('permission:drivers.view')->get('chauffeurs/{driver}', [DriverController::class, 'show'])->name('drivers.show');
    Route::middleware('permission:drivers.edit')->get('chauffeurs/{driver}/modifier', [DriverController::class, 'edit'])->name('drivers.edit');
    Route::middleware('permission:drivers.edit')->put('chauffeurs/{driver}', [DriverController::class, 'update'])->name('drivers.update');
    Route::middleware('permission:drivers.edit')->post('chauffeurs/{driver}/statut', [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status');
    Route::middleware('permission:drivers.delete')->delete('chauffeurs/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy');

    // ── Affectations ───────────────────────────────────────────────────────
    Route::middleware('permission:assignments.view')->get('affectations', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::middleware('permission:assignments.create')->get('affectations/nouvelle', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::middleware('permission:assignments.create')->post('affectations', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::middleware('permission:assignments.view')->get('affectations/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::middleware('permission:assignments.edit')->get('affectations/{assignment}/modifier', [AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::middleware('permission:assignments.edit')->put('affectations/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::middleware('permission:assignments.edit')->post('affectations/{assignment}/confirmer', [AssignmentController::class, 'confirm'])->name('assignments.confirm');
    Route::middleware('permission:assignments.edit')->post('affectations/{assignment}/demarrer', [AssignmentController::class, 'start'])->name('assignments.start');
    Route::middleware('permission:assignments.edit')->post('affectations/{assignment}/terminer', [AssignmentController::class, 'complete'])->name('assignments.complete');
    Route::middleware('permission:assignments.edit')->post('affectations/{assignment}/annuler', [AssignmentController::class, 'cancel'])->name('assignments.cancel');
    Route::middleware('permission:assignments.delete')->delete('affectations/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

    // ── Demandes de véhicule ────────────────────────────────────────────────
    Route::middleware('permission:vehicle_requests.view')->get('demandes', [VehicleRequestController::class, 'index'])->name('requests.index');
    Route::middleware('permission:vehicle_requests.create')->get('demandes/nouvelle', [VehicleRequestController::class, 'create'])->name('requests.create');
    Route::middleware('permission:vehicle_requests.create')->post('demandes', [VehicleRequestController::class, 'store'])->name('requests.store');
    Route::middleware('permission:vehicle_requests.view')->get('demandes/{vehicleRequest}', [VehicleRequestController::class, 'show'])->name('requests.show');
    Route::middleware('permission:vehicle_requests.edit')->get('demandes/{vehicleRequest}/modifier', [VehicleRequestController::class, 'edit'])->name('requests.edit');
    Route::middleware('permission:vehicle_requests.edit')->put('demandes/{vehicleRequest}', [VehicleRequestController::class, 'update'])->name('requests.update');
    Route::middleware('permission:vehicle_requests.approve')->post('demandes/{vehicleRequest}/approuver', [VehicleRequestController::class, 'approve'])->name('requests.approve');
    Route::middleware('permission:vehicle_requests.approve')->post('demandes/{vehicleRequest}/rejeter', [VehicleRequestController::class, 'reject'])->name('requests.reject');
    Route::middleware('permission:vehicle_requests.edit')->post('demandes/{vehicleRequest}/demarrer', [VehicleRequestController::class, 'start'])->name('requests.start');
    Route::middleware('permission:vehicle_requests.edit')->post('demandes/{vehicleRequest}/terminer', [VehicleRequestController::class, 'complete'])->name('requests.complete');
    Route::middleware('permission:vehicle_requests.edit')->post('demandes/{vehicleRequest}/annuler', [VehicleRequestController::class, 'cancel'])->name('requests.cancel');

    // ── Administration (super_admin + admin uniquement) ─────────────────────
    Route::middleware('role:super_admin|admin')->prefix('admin')->name('admin.')->group(function () {

        // Gestion des utilisateurs
        Route::get('utilisateurs', [UserController::class, 'index'])->name('users.index');
        Route::get('utilisateurs/nouveau', [UserController::class, 'create'])->name('users.create');
        Route::post('utilisateurs', [UserController::class, 'store'])->name('users.store');
        Route::get('utilisateurs/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('utilisateurs/{user}/modifier', [UserController::class, 'edit'])->name('users.edit');
        Route::put('utilisateurs/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('utilisateurs/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('utilisateurs/{id}/restaurer', [UserController::class, 'restore'])->name('users.restore');
        Route::post('utilisateurs/{user}/statut', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('utilisateurs/{user}/reinitialiser-mdp', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });
});
