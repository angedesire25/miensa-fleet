<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\Repair;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── Statistiques principales ───────────────────────────────────────
        $stats = [
            'vehicles_total'     => Vehicle::active()->count(),
            'vehicles_available' => Vehicle::where('status', 'available')->count(),
            'vehicles_mission'   => Vehicle::where('status', 'on_mission')->count(),
            'vehicles_maintenance' => Vehicle::whereIn('status', ['maintenance', 'breakdown'])->count(),

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
        $activeAssignments = Assignment::with(['vehicle', 'driver'])
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
            'stats', 'recentAlerts', 'activeAssignments', 'ongoingRepairs'
        ));
    }
}
