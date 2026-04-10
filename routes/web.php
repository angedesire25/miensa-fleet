<?php

use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CleaningController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\InfractionController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleDocumentController;
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
    // Suppression définitive (admin + super_admin uniquement)
    Route::middleware('role:super_admin|admin')->delete('vehicules/{id}/supprimer-definitivement', [VehicleController::class, 'forceDestroy'])->name('vehicles.force-destroy');
    // Documents administratifs du véhicule
    Route::middleware('permission:vehicles.edit')->post('vehicules/{vehicle}/documents', [VehicleDocumentController::class, 'store'])->name('vehicle-documents.store');
    Route::middleware('permission:vehicles.edit')->delete('vehicules/{vehicle}/documents/{document}', [VehicleDocumentController::class, 'destroy'])->name('vehicle-documents.destroy');

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
    Route::middleware('role:super_admin|admin')->delete('chauffeurs/{id}/supprimer-definitivement', [DriverController::class, 'forceDestroy'])->name('drivers.force-destroy');
    Route::middleware('role:super_admin|admin')->post('chauffeurs/{driver}/creer-compte', [DriverController::class, 'createAccount'])->name('drivers.create-account');

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
    Route::middleware('permission:assignments.delete')->post('affectations/{id}/restaurer', [AssignmentController::class, 'restore'])->name('assignments.restore');
    Route::middleware('permission:assignments.delete')->delete('affectations/{id}/supprimer-definitif', [AssignmentController::class, 'forceDestroy'])->name('assignments.force-destroy');

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
    Route::delete('demandes/{vehicleRequest}/archiver', [VehicleRequestController::class, 'destroy'])->name('requests.destroy')->middleware('auth');
    Route::post('demandes/{id}/restaurer', [VehicleRequestController::class, 'restore'])->name('requests.restore')->middleware('auth');
    Route::delete('demandes/{id}/supprimer-definitif', [VehicleRequestController::class, 'forceDestroy'])->name('requests.force-destroy')->middleware('auth');

    // ── Sinistres ──────────────────────────────────────────────────────────
    Route::middleware('permission:incidents.view')->get('sinistres', [IncidentController::class, 'index'])->name('incidents.index');
    Route::middleware('permission:incidents.create')->get('sinistres/nouveau', [IncidentController::class, 'create'])->name('incidents.create');
    Route::middleware('permission:incidents.create')->post('sinistres', [IncidentController::class, 'store'])->name('incidents.store');
    Route::middleware('permission:incidents.view')->get('sinistres/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
    Route::middleware('permission:incidents.edit')->get('sinistres/{incident}/modifier', [IncidentController::class, 'edit'])->name('incidents.edit');
    Route::middleware('permission:incidents.edit')->put('sinistres/{incident}', [IncidentController::class, 'update'])->name('incidents.update');
    Route::middleware('permission:incidents.edit')->post('sinistres/{incident}/envoyer-garage', [IncidentController::class, 'sendToGarage'])->name('incidents.send-to-garage');
    Route::middleware('permission:incidents.edit')->post('sinistres/{incident}/cloturer', [IncidentController::class, 'close'])->name('incidents.close');
    Route::middleware('permission:incidents.edit')->delete('sinistres/{incident}/photo', [IncidentController::class, 'deletePhoto'])->name('incidents.delete-photo');
    Route::middleware('permission:incidents.edit')->delete('sinistres/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');
    Route::middleware('permission:incidents.edit')->post('sinistres/{id}/restaurer', [IncidentController::class, 'restore'])->name('incidents.restore');
    Route::middleware('role:super_admin|admin')->delete('sinistres/{id}/supprimer-definitivement', [IncidentController::class, 'forceDestroy'])->name('incidents.force-destroy');

    // ── Réparations ────────────────────────────────────────────────────────
    Route::middleware('permission:repairs.view')->get('reparations', [RepairController::class, 'index'])->name('repairs.index');
    Route::middleware('permission:repairs.create')->get('reparations/nouvelle', [RepairController::class, 'create'])->name('repairs.create');
    Route::middleware('permission:repairs.create')->post('reparations', [RepairController::class, 'store'])->name('repairs.store');
    Route::middleware('permission:repairs.view')->get('reparations/{repair}', [RepairController::class, 'show'])->name('repairs.show');
    Route::middleware('permission:repairs.edit')->post('reparations/{repair}/statut', [RepairController::class, 'updateStatus'])->name('repairs.update-status');
    Route::middleware('permission:repairs.edit')->post('reparations/{repair}/retour-garage', [RepairController::class, 'returnFromGarage'])->name('repairs.return-from-garage');
    Route::middleware('permission:repairs.edit')->delete('reparations/{repair}/photo', [RepairController::class, 'deletePhoto'])->name('repairs.delete-photo');
    Route::middleware('permission:repairs.delete')->delete('reparations/{repair}', [RepairController::class, 'destroy'])->name('repairs.destroy');

    // ── Garages ────────────────────────────────────────────────────────────
    Route::middleware('permission:garages.view')->get('garages', [GarageController::class, 'index'])->name('garages.index');
    Route::middleware('permission:garages.create')->get('garages/nouveau', [GarageController::class, 'create'])->name('garages.create');
    Route::middleware('permission:garages.create')->post('garages', [GarageController::class, 'store'])->name('garages.store');
    Route::middleware('permission:garages.view')->get('garages/{garage}', [GarageController::class, 'show'])->name('garages.show');
    Route::middleware('permission:garages.edit')->get('garages/{garage}/modifier', [GarageController::class, 'edit'])->name('garages.edit');
    Route::middleware('permission:garages.edit')->put('garages/{garage}', [GarageController::class, 'update'])->name('garages.update');
    Route::middleware('permission:garages.edit')->post('garages/{garage}/approuver', [GarageController::class, 'toggleApproved'])->name('garages.toggle-approved');
    Route::middleware('permission:garages.delete')->delete('garages/{garage}', [GarageController::class, 'destroy'])->name('garages.destroy');
    Route::middleware('role:super_admin|admin')->post('garages/{id}/restaurer', [GarageController::class, 'restore'])->name('garages.restore');
    Route::middleware('role:super_admin|admin')->delete('garages/{id}/supprimer-definitivement', [GarageController::class, 'forceDestroy'])->name('garages.force-destroy');

    // ── Alertes ────────────────────────────────────────────────────────────
    Route::middleware('permission:alerts.view')->get('alertes', [AlertController::class, 'index'])->name('alerts.index');
    Route::middleware('permission:alerts.view')->get('alertes/{alert}', [AlertController::class, 'show'])->name('alerts.show');
    Route::middleware('permission:alerts.manage')->post('alertes/{alert}/traiter', [AlertController::class, 'process'])->name('alerts.process');
    Route::middleware('permission:alerts.manage')->post('alertes/traiter-tout', [AlertController::class, 'bulkProcess'])->name('alerts.bulk-process');

    // ── Infractions ────────────────────────────────────────────────────────
    Route::middleware('permission:infractions.view')->get('infractions', [InfractionController::class, 'index'])->name('infractions.index');
    Route::middleware('permission:infractions.create')->get('infractions/nouvelle', [InfractionController::class, 'create'])->name('infractions.create');
    Route::middleware('permission:infractions.create')->post('infractions', [InfractionController::class, 'store'])->name('infractions.store');
    Route::middleware('permission:infractions.view')->get('infractions/identifier-occupant', [InfractionController::class, 'identifyOccupant'])->name('infractions.identify-occupant');
    Route::middleware('permission:infractions.view')->get('infractions/{infraction}', [InfractionController::class, 'show'])->name('infractions.show');
    Route::middleware('permission:infractions.edit')->get('infractions/{infraction}/modifier', [InfractionController::class, 'edit'])->name('infractions.edit');
    Route::middleware('permission:infractions.edit')->put('infractions/{infraction}', [InfractionController::class, 'update'])->name('infractions.update');
    Route::middleware('permission:infractions.impute')->post('infractions/{infraction}/imputer', [InfractionController::class, 'impute'])->name('infractions.impute');
    Route::middleware('permission:infractions.edit')->post('infractions/{infraction}/paiement', [InfractionController::class, 'recordPayment'])->name('infractions.record-payment');
    Route::middleware('permission:infractions.edit')->post('infractions/{infraction}/cloturer', [InfractionController::class, 'close'])->name('infractions.close');
    Route::middleware('permission:infractions.edit')->delete('infractions/{infraction}', [InfractionController::class, 'destroy'])->name('infractions.destroy');
    Route::middleware('role:super_admin|admin')->post('infractions/{id}/restaurer', [InfractionController::class, 'restore'])->name('infractions.restore');
    Route::middleware('role:super_admin|admin')->delete('infractions/{id}/supprimer-definitivement', [InfractionController::class, 'forceDestroy'])->name('infractions.force-destroy');

    // ── Nettoyage des véhicules ────────────────────────────────────────────
    Route::middleware('permission:cleanings.view')->get('nettoyages', [CleaningController::class, 'index'])->name('cleanings.index');
    Route::middleware('permission:cleanings.create')->get('nettoyages/nouveau', [CleaningController::class, 'create'])->name('cleanings.create');
    Route::middleware('permission:cleanings.create')->post('nettoyages', [CleaningController::class, 'store'])->name('cleanings.store');
    Route::middleware('permission:cleanings.view')->get('nettoyages/{cleaning}', [CleaningController::class, 'show'])->name('cleanings.show');
    Route::middleware('permission:cleanings.edit')->get('nettoyages/{cleaning}/modifier', [CleaningController::class, 'edit'])->name('cleanings.edit');
    Route::middleware('permission:cleanings.edit')->put('nettoyages/{cleaning}', [CleaningController::class, 'update'])->name('cleanings.update');
    Route::middleware('permission:cleanings.confirm')->post('nettoyages/{cleaning}/confirmer', [CleaningController::class, 'confirm'])->name('cleanings.confirm');
    Route::middleware('permission:cleanings.edit')->post('nettoyages/{cleaning}/completer', [CleaningController::class, 'complete'])->name('cleanings.complete');
    Route::middleware('permission:cleanings.edit')->post('nettoyages/{cleaning}/manque', [CleaningController::class, 'markMissed'])->name('cleanings.missed');
    Route::middleware('permission:cleanings.edit')->post('nettoyages/{cleaning}/annuler', [CleaningController::class, 'cancel'])->name('cleanings.cancel');
    Route::middleware('permission:cleanings.delete')->delete('nettoyages/{cleaning}', [CleaningController::class, 'destroy'])->name('cleanings.destroy');
    Route::middleware('permission:cleanings.delete')->post('nettoyages/{id}/restaurer', [CleaningController::class, 'restore'])->name('cleanings.restore');

    // ── Notifications in-app ──────────────────────────────────────────────
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('notifications/{id}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');

    // ── Rapports ───────────────────────────────────────────────────────────
    Route::middleware('permission:reports.view')->get('rapports', [ReportController::class, 'index'])->name('reports.index');
    Route::middleware('permission:reports.view')->get('rapports/vehicule', [ReportController::class, 'vehicle'])->name('reports.vehicle');
    Route::middleware('permission:reports.view')->get('rapports/chauffeur', [ReportController::class, 'driver'])->name('reports.driver');
    Route::middleware('permission:reports.view')->get('rapports/infractions', [ReportController::class, 'infractions'])->name('reports.infractions');
    Route::middleware('permission:reports.view')->get('rapports/documents', [ReportController::class, 'documentsExpiring'])->name('reports.documents');

    // ── Administration (super_admin + admin uniquement) ─────────────────────
    Route::middleware('role:super_admin|admin')->prefix('admin')->name('admin.')->group(function () {

        // Paramètres de l'application (super_admin uniquement)
        Route::middleware('role:super_admin')->group(function () {
            Route::get('parametres', [SettingController::class, 'edit'])->name('settings.edit');
            Route::post('parametres', [SettingController::class, 'update'])->name('settings.update');
        });

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
        Route::delete('utilisateurs/{id}/supprimer-definitivement', [UserController::class, 'forceDestroy'])->name('users.force-destroy');
    });
});
