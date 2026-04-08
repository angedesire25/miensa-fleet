<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    // ── Tableau de bord rapports ────────────────────────────────────────────

    /**
     * Page d'accueil des rapports : sélection du type + période.
     * Affiche aussi la synthèse globale de la flotte sur la période.
     */
    public function index(Request $request): View
    {
        [$from, $to] = $this->parsePeriod($request);

        $summary  = $this->reportService->fleetSummary($from, $to);
        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get(['id', 'brand', 'model', 'plate']);
        $drivers  = Driver::orderBy('full_name')->get(['id', 'full_name', 'matricule']);

        return view('reports.index', compact('summary', 'vehicles', 'drivers', 'from', 'to'));
    }

    // ── Rapport véhicule ───────────────────────────────────────────────────

    public function vehicle(Request $request): View
    {
        [$from, $to] = $this->parsePeriod($request);
        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get(['id', 'brand', 'model', 'plate']);
        $drivers  = Driver::orderBy('full_name')->get(['id', 'full_name', 'matricule']);
        $report   = null;

        if ($request->filled('vehicle_id')) {
            $request->validate(['vehicle_id' => ['required', 'exists:vehicles,id']]);
            $report = $this->reportService->vehicleReport((int) $request->vehicle_id, $from, $to);
        }

        return view('reports.vehicle', compact('report', 'vehicles', 'drivers', 'from', 'to'));
    }

    // ── Rapport chauffeur ──────────────────────────────────────────────────

    public function driver(Request $request): View
    {
        [$from, $to] = $this->parsePeriod($request);
        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get(['id', 'brand', 'model', 'plate']);
        $drivers  = Driver::orderBy('full_name')->get(['id', 'full_name', 'matricule']);
        $report   = null;

        if ($request->filled('driver_id')) {
            $request->validate(['driver_id' => ['required', 'exists:drivers,id']]);
            $report = $this->reportService->driverReport((int) $request->driver_id, $from, $to);
        }

        return view('reports.driver', compact('report', 'vehicles', 'drivers', 'from', 'to'));
    }

    // ── Rapport infractions ────────────────────────────────────────────────

    public function infractions(Request $request): View
    {
        [$from, $to] = $this->parsePeriod($request);

        $report   = $this->reportService->infractionReport($from, $to);
        $vehicles = Vehicle::orderBy('brand')->orderBy('model')->get(['id', 'brand', 'model', 'plate']);
        $drivers  = Driver::orderBy('full_name')->get(['id', 'full_name', 'matricule']);

        return view('reports.infractions', compact('report', 'vehicles', 'drivers', 'from', 'to'));
    }

    // ── Documents expirants ────────────────────────────────────────────────

    public function documentsExpiring(Request $request): View
    {
        $days   = (int) $request->input('days', 30);
        $report = $this->reportService->documentsExpiring($days);

        return view('reports.documents', compact('report', 'days'));
    }

    // ── Helper ─────────────────────────────────────────────────────────────

    /**
     * Retourne [Carbon $from, Carbon $to] depuis la requête.
     * Par défaut : mois en cours.
     */
    private function parsePeriod(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        // Sécurité : from ne peut pas être après to
        if ($from->gt($to)) {
            $from = $to->copy()->subMonth();
        }

        return [$from, $to];
    }
}
