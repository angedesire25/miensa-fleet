<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\Repair;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── Détail flotte par statut ───────────────────────────────────────
        // IDs véhicules avec une affectation permanente active (confirmed ou in_progress)
        $permanentVehicleIds = Assignment::whereIn('status', ['confirmed', 'in_progress'])
            ->where('type', 'permanent')
            ->pluck('vehicle_id')
            ->unique();

        // IDs véhicules avec une mission ponctuelle en cours (hors permanente)
        $missionPonctuelIds = Assignment::whereIn('status', ['confirmed', 'in_progress'])
            ->whereIn('type', ['mission', 'daily', 'replacement', 'trial'])
            ->pluck('vehicle_id')
            ->unique();

        $fleetStats = [
            // Vraiment libre : status=available ET pas d'affectation permanente
            'available'          => Vehicle::where('status', 'available')
                                        ->whereNotIn('id', $permanentVehicleIds)->count(),
            // Disponible physiquement mais déjà attribué en permanence
            'permanent_assigned' => Vehicle::whereIn('id', $permanentVehicleIds)->count(),
            // En déplacement ponctuel (on_mission, hors permanent)
            'mission_ponctuel'   => Vehicle::where('status', 'on_mission')
                                        ->whereNotIn('id', $permanentVehicleIds)->count(),
            'maintenance'        => Vehicle::where('status', 'maintenance')->count(),
            'breakdown'          => Vehicle::where('status', 'breakdown')->count(),
            'total'              => Vehicle::active()->count(),
        ];

        // ── Statistiques principales ───────────────────────────────────────
        $stats = [
            'vehicles_total'       => $fleetStats['total'],
            'vehicles_available'   => $fleetStats['available'],
            'vehicles_permanent'   => $fleetStats['permanent_assigned'],
            'vehicles_mission'     => $fleetStats['mission_ponctuel'],
            'vehicles_maintenance' => $fleetStats['maintenance'] + $fleetStats['breakdown'],

            'drivers_active'     => Driver::where('status', 'active')->count(),
            'assignments_active' => Assignment::whereIn('status', ['confirmed', 'in_progress'])->count(),

            'alerts_new'         => Alert::where('status', 'new')->count(),
            'alerts_critical'    => Alert::where('status', 'new')->where('severity', 'critical')->count(),

            'incidents_open'     => Incident::whereIn('status', ['open', 'at_garage'])->count(),
            'repairs_in_progress'=> Repair::whereIn('status', ['sent', 'diagnosing', 'repairing', 'waiting_parts'])->count(),
        ];

        // ── Alertes récentes ───────────────────────────────────────────────
        $recentAlerts = Alert::with('vehicle', 'driver')
            ->whereIn('status', ['new', 'seen'])
            ->orderByRaw("FIELD(severity,'critical','warning','info')")
            ->latest()
            ->limit(8)
            ->get();

        // ── Affectations en cours ──────────────────────────────────────────
        $activeAssignments = Assignment::with(['vehicle', 'driver', 'collaborator'])
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->orderBy('datetime_end_planned')
            ->limit(6)
            ->get();

        // ── Réparations en cours ───────────────────────────────────────────
        $ongoingRepairs = Repair::with(['vehicle', 'garage'])
            ->whereIn('status', ['sent', 'diagnosing', 'repairing', 'waiting_parts'])
            ->latest('datetime_sent')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'fleetStats', 'recentAlerts', 'activeAssignments', 'ongoingRepairs'
        ));
    }
}
