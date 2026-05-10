<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\FuelRequest;
use App\Models\FuelStation;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FuelRequestController extends Controller
{
    // ── Liste des demandes de l'utilisateur connecté ───────────────────────

    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = FuelRequest::with(['vehicle', 'driver', 'fuelStation', 'reviewedBy'])
            ->where('requester_id', $user->id);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $requests = $query->latest('requested_at')->paginate(15)->withQueryString();

        $stats = [
            'total'     => FuelRequest::where('requester_id', $user->id)->count(),
            'pending'   => FuelRequest::where('requester_id', $user->id)->where('status', 'pending')->count(),
            'approved'  => FuelRequest::where('requester_id', $user->id)->where('status', 'approved')->count(),
            'fulfilled' => FuelRequest::where('requester_id', $user->id)->where('status', 'fulfilled')->count(),
        ];

        return view('fuel.requests.index', compact('requests', 'stats'));
    }

    // ── Formulaire de création ─────────────────────────────────────────────

    public function create(): View
    {
        $vehicles = Vehicle::active()->orderBy('brand')->get();
        $stations = FuelStation::active()->orderBy('name')->get();
        $fuelTypes = $this->fuelTypeLabels();

        // Si l'utilisateur est un chauffeur, pré-sélectionner son profil
        $driver = Auth::user()->driver ?? null;

        return view('fuel.requests.create', compact('vehicles', 'stations', 'fuelTypes', 'driver'));
    }

    // ── Enregistrement ─────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $user     = Auth::user();
        $isDriver = $user->hasRole('driver_user');

        $data = $request->validate([
            'vehicle_id'       => ['required', 'exists:vehicles,id'],
            'fuel_type'        => ['required', 'in:diesel,gasoline,hybrid,electric,lpg'],
            'estimated_amount' => ['required', 'numeric', 'min:1'],
            'liters_requested' => ['nullable', 'numeric', 'min:0.1', 'max:1000'],
            'odometer_km'      => $isDriver
                                    ? ['required', 'integer', 'min:0']
                                    : ['nullable', 'integer', 'min:0'],
            'fuel_station_id'  => ['nullable', 'exists:fuel_stations,id'],
            'reason'           => ['required', 'string', 'max:500'],
            'is_urgent'        => ['boolean'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ], [
            'estimated_amount.required' => 'Le montant est obligatoire.',
            'estimated_amount.min'      => 'Le montant doit être supérieur à 0 FCFA.',
            'odometer_km.required'      => 'Le kilométrage est obligatoire pour les chauffeurs professionnels.',
            'reason.required'           => 'Le motif de la demande est obligatoire.',
        ]);

        $driver = $user->driver;

        FuelRequest::create([
            ...$data,
            'requester_id' => $user->id,
            'driver_id'    => $driver?->id,
            'status'       => 'pending',
            'requested_at' => now(),
            'is_urgent'    => $request->boolean('is_urgent'),
        ]);

        return redirect()->route('fuel.requests.index')
            ->with('success', 'Votre demande de carburant a été soumise avec succès.');
    }

    // ── Détail d'une demande ───────────────────────────────────────────────

    public function show(FuelRequest $fuelRequest): View
    {
        // L'utilisateur ne peut voir que ses propres demandes (sauf gestionnaire)
        if (!Auth::user()->hasAnyRole(['super_admin', 'admin', 'fleet_manager', 'controller', 'director'])
            && $fuelRequest->requester_id !== Auth::id()) {
            abort(403);
        }

        $fuelRequest->load(['vehicle', 'driver', 'fuelStation', 'requester', 'reviewedBy', 'fuelTransactions.recordedBy']);

        return view('fuel.requests.show', compact('fuelRequest'));
    }

    // ── Annulation par le demandeur ────────────────────────────────────────

    public function cancel(FuelRequest $fuelRequest): RedirectResponse
    {
        // Seul le demandeur ou un gestionnaire peut annuler
        if (!Auth::user()->hasAnyRole(['super_admin', 'admin', 'fleet_manager'])
            && $fuelRequest->requester_id !== Auth::id()) {
            abort(403);
        }

        if (! $fuelRequest->canBeCancelled()) {
            return back()->with('error', 'Cette demande ne peut plus être annulée.');
        }

        $fuelRequest->update(['status' => 'cancelled']);

        return redirect()->route('fuel.requests.index')
            ->with('success', 'Demande annulée.');
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
}
