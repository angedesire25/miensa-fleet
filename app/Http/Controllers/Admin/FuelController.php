<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\FuelRequest;
use App\Models\FuelStation;
use App\Models\FuelTransaction;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Espace administration du module carburant.
 *
 * Accessible aux rôles : super_admin, admin, fleet_manager, controller (limité).
 * Routes préfixées par /fuel (sans préfixe "admin" dans l'URL pour garder cohérence).
 */
class FuelController extends Controller
{
    // ── Tableau de bord carburant ──────────────────────────────────────────

    public function dashboard(): View
    {


        $period = request('period', 'month'); // month | quarter | year
        [$from, $to] = $this->periodDates($period);

        $stats = [
            'pending_requests'   => FuelRequest::where('status', 'pending')->count(),
            'approved_requests'  => FuelRequest::where('status', 'approved')->count(),
            'transactions_count' => FuelTransaction::whereBetween('fueled_at', [$from, $to])->count(),
            'total_liters'       => FuelTransaction::whereBetween('fueled_at', [$from, $to])->sum('liters'),
            'total_cost'         => FuelTransaction::whereBetween('fueled_at', [$from, $to])->sum('total_amount'),
            'card_cost'          => FuelTransaction::whereBetween('fueled_at', [$from, $to])->where('fuel_card_used', true)->sum('total_amount'),
        ];

        // Top 5 véhicules consommateurs sur la période
        $topVehicles = FuelTransaction::whereBetween('fueled_at', [$from, $to])
            ->with('vehicle')
            ->selectRaw('vehicle_id, SUM(liters) as total_liters, SUM(total_amount) as total_cost, COUNT(*) as fills')
            ->groupBy('vehicle_id')
            ->orderByDesc('total_liters')
            ->limit(5)
            ->get();

        // Dernières transactions
        $recentTransactions = FuelTransaction::with(['vehicle', 'driver', 'recordedBy'])
            ->latest('fueled_at')
            ->limit(8)
            ->get();

        // Demandes urgentes en attente
        $urgentRequests = FuelRequest::with(['vehicle', 'requester'])
            ->where('status', 'pending')
            ->where('is_urgent', true)
            ->latest('requested_at')
            ->get();

        return view('fuel.admin.dashboard', compact(
            'stats', 'topVehicles', 'recentTransactions', 'urgentRequests', 'period', 'from', 'to'
        ));
    }

    // ── Liste de toutes les demandes ───────────────────────────────────────

    public function requests(Request $request): View
    {


        $query = FuelRequest::with(['vehicle', 'driver', 'requester', 'fuelStation', 'reviewedBy']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('reference', 'like', "%{$q}%")
                   ->orWhereHas('vehicle', fn($v) => $v->where('plate', 'like', "%{$q}%"))
                   ->orWhereHas('requester', fn($r) => $r->where('name', 'like', "%{$q}%"));
            });
        }

        if ($request->boolean('urgent_only')) {
            $query->where('is_urgent', true);
        }

        $requests = $query->latest('requested_at')->paginate(20)->withQueryString();

        $stats = [
            'pending'   => FuelRequest::where('status', 'pending')->count(),
            'approved'  => FuelRequest::where('status', 'approved')->count(),
            'fulfilled' => FuelRequest::where('status', 'fulfilled')->count(),
            'urgent'    => FuelRequest::where('status', 'pending')->where('is_urgent', true)->count(),
        ];

        $vehicles = Vehicle::active()->orderBy('brand')->get();

        return view('fuel.admin.requests', compact('requests', 'stats', 'vehicles'));
    }

    // ── Détail d'une demande (approbation / rejet) ─────────────────────────

    public function showRequest(FuelRequest $fuelRequest): View
    {


        $fuelRequest->load(['vehicle', 'driver', 'requester', 'fuelStation', 'reviewedBy', 'fuelTransactions.recordedBy']);

        $stations = FuelStation::active()->orderBy('name')->get();
        $drivers  = Driver::active()->orderBy('full_name')->get();

        return view('fuel.admin.request-show', compact('fuelRequest', 'stations', 'drivers'));
    }

    // ── Approuver une demande ──────────────────────────────────────────────

    public function approveRequest(Request $request, FuelRequest $fuelRequest): RedirectResponse
    {


        if ($fuelRequest->status !== 'pending') {
            return back()->with('error', 'Cette demande n\'est plus en attente.');
        }

        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $fuelRequest->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes'=> $data['review_notes'] ?? null,
        ]);

        return back()->with('success', "Demande {$fuelRequest->reference} approuvée.");
    }

    // ── Rejeter une demande ────────────────────────────────────────────────

    public function rejectRequest(Request $request, FuelRequest $fuelRequest): RedirectResponse
    {


        if ($fuelRequest->status !== 'pending') {
            return back()->with('error', 'Cette demande n\'est plus en attente.');
        }

        $data = $request->validate([
            'review_notes' => ['required', 'string', 'max:500'],
        ]);

        $fuelRequest->update([
            'status'       => 'rejected',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'review_notes' => $data['review_notes'],
        ]);

        return back()->with('success', "Demande {$fuelRequest->reference} rejetée.");
    }

    // ── Liste des transactions ─────────────────────────────────────────────

    public function transactions(Request $request): View
    {


        $query = FuelTransaction::with(['vehicle', 'driver', 'fuelStation', 'recordedBy', 'fuelRequest']);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->filled('date_from')) {
            $query->where('fueled_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('fueled_at', '<=', $request->date_to);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('reference', 'like', "%{$q}%")
                   ->orWhere('receipt_number', 'like', "%{$q}%")
                   ->orWhereHas('vehicle', fn($v) => $v->where('plate', 'like', "%{$q}%"));
            });
        }

        $transactions = $query->latest('fueled_at')->paginate(20)->withQueryString();

        $totals = [
            'liters' => FuelTransaction::sum('liters'),
            'cost'   => FuelTransaction::sum('total_amount'),
        ];

        $vehicles = Vehicle::active()->orderBy('brand')->get();

        return view('fuel.admin.transactions', compact('transactions', 'totals', 'vehicles'));
    }

    // ── Enregistrer une transaction directe ───────────────────────────────

    public function createTransaction(Request $request): View
    {


        $vehicles  = Vehicle::active()->orderBy('brand')->get();
        $drivers   = Driver::active()->orderBy('full_name')->get();
        $stations  = FuelStation::active()->orderBy('name')->get();
        $fuelTypes = $this->fuelTypeLabels();

        // Optionnel : pré-remplir depuis une demande approuvée
        $fuelRequest = null;
        if ($request->filled('request_id')) {
            $fuelRequest = FuelRequest::with(['vehicle', 'driver', 'fuelStation'])
                ->where('status', 'approved')
                ->findOrFail($request->request_id);
        }

        return view('fuel.admin.transaction-create', compact('vehicles', 'drivers', 'stations', 'fuelTypes', 'fuelRequest'));
    }

    public function storeTransaction(Request $request): RedirectResponse
    {


        $data = $request->validate([
            'vehicle_id'        => ['required', 'exists:vehicles,id'],
            'driver_id'         => ['nullable', 'exists:drivers,id'],
            'fuel_request_id'   => ['nullable', 'exists:fuel_requests,id'],
            'fuel_station_id'   => ['nullable', 'exists:fuel_stations,id'],
            'station_name_free' => ['nullable', 'string', 'max:150'],
            'fuel_type'         => ['required', 'in:diesel,gasoline,hybrid,electric,lpg'],
            'liters'            => ['required', 'numeric', 'min:0.1', 'max:5000'],
            'unit_price'        => ['required', 'numeric', 'min:0'],
            'total_amount'      => ['required', 'numeric', 'min:0'],
            'odometer_km'       => ['required', 'integer', 'min:0'],
            'fuel_card_used'    => ['boolean'],
            'fuel_card_number'  => ['nullable', 'string', 'max:30'],
            'receipt_number'    => ['nullable', 'string', 'max:60'],
            'receipt_photo'     => ['nullable', 'image', 'max:4096'],
            'fueled_at'         => ['required', 'date'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        // Upload ticket photo
        if ($request->hasFile('receipt_photo')) {
            $data['receipt_photo'] = $request->file('receipt_photo')
                ->store('fuel/receipts', 'public');
        }

        // Calculer km parcourus + consommation depuis le dernier plein du véhicule
        $vehicle = Vehicle::find($data['vehicle_id']);
        if ($vehicle->km_last_fill && $data['odometer_km'] > $vehicle->km_last_fill) {
            $kmSince = $data['odometer_km'] - $vehicle->km_last_fill;
            $data['km_since_last_fill']    = $kmSince;
            $data['consumption_per_100km'] = $kmSince > 0
                ? round(($data['liters'] / $kmSince) * 100, 2)
                : null;
        }

        FuelTransaction::create([
            ...$data,
            'recorded_by'    => Auth::id(),
            'fuel_card_used' => $request->boolean('fuel_card_used'),
        ]);

        return redirect()->route('fuel.admin.transactions')
            ->with('success', 'Transaction enregistrée avec succès.');
    }

    // ── Gestion des stations ───────────────────────────────────────────────

    public function stations(Request $request): View
    {


        $query = FuelStation::withCount(['fuelTransactions']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('city', 'like', "%{$q}%")
                   ->orWhere('brand', 'like', "%{$q}%");
            });
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $stations = $query->latest()->paginate(20)->withQueryString();

        return view('fuel.admin.stations', compact('stations'));
    }

    public function createStation(): View
    {


        $fuelTypes = $this->fuelTypeLabels();

        return view('fuel.admin.station-create', compact('fuelTypes'));
    }

    public function storeStation(Request $request): RedirectResponse
    {


        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'brand'          => ['nullable', 'string', 'max:60'],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:80'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'fuel_types'     => ['required', 'array', 'min:1'],
            'fuel_types.*'   => ['in:diesel,gasoline,hybrid,electric,lpg'],
            'is_active'      => ['boolean'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        FuelStation::create([
            ...$data,
            'created_by' => Auth::id(),
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('fuel.admin.stations')
            ->with('success', 'Station carburant ajoutée.');
    }

    public function editStation(FuelStation $fuelStation): View
    {


        $fuelTypes = $this->fuelTypeLabels();

        return view('fuel.admin.station-edit', compact('fuelStation', 'fuelTypes'));
    }

    public function updateStation(Request $request, FuelStation $fuelStation): RedirectResponse
    {


        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'brand'          => ['nullable', 'string', 'max:60'],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:80'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'fuel_types'     => ['required', 'array', 'min:1'],
            'fuel_types.*'   => ['in:diesel,gasoline,hybrid,electric,lpg'],
            'is_active'      => ['boolean'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $fuelStation->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('fuel.admin.stations')
            ->with('success', 'Station mise à jour.');
    }

    public function destroyStation(FuelStation $fuelStation): RedirectResponse
    {


        $fuelStation->delete();

        return redirect()->route('fuel.admin.stations')
            ->with('success', 'Station supprimée.');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function fuelTypeLabels(): array
    {
        return [
            'diesel'   => 'Diesel',
            'gasoline' => 'Essence',
            'hybrid'   => 'Hybride',
            'electric' => 'Électrique',
            'lpg'      => 'GPL',
        ];
    }

    private function periodDates(string $period): array
    {
        return match ($period) {
            'quarter' => [now()->startOfQuarter()->toDateString(), now()->endOfQuarter()->toDateString()],
            'year'    => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            default   => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        };
    }
}
