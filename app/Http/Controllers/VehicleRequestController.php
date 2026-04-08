<?php

namespace App\Http\Controllers;

use App\Exceptions\VehicleAvailabilityException;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleRequest;
use App\Notifications\VehicleRequestSubmittedNotification;
use App\Services\VehicleRequestService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VehicleRequestController extends Controller
{
    public function __construct(private readonly VehicleRequestService $service) {}

    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user  = Auth::user();
        $query = VehicleRequest::with(['requester', 'vehicle.profilePhoto', 'reviewedBy']);

        // Collaborateur et chauffeur ne voient que leurs propres demandes
        // Seuls les admins / responsables / contrôleurs / directeurs voient tout
        if (!$user->hasAnyRole(['super_admin', 'admin', 'fleet_manager', 'controller', 'director'])) {
            $query->where('requester_id', $user->id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('destination', 'like', "%{$q}%")
                   ->orWhere('purpose', 'like', "%{$q}%")
                   ->orWhereHas('requester', fn($r) => $r->where('name', 'like', "%{$q}%"))
                   ->orWhereHas('vehicle', fn($v) => $v->where('plate', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->boolean('urgent_only')) {
            $query->where('is_urgent', true);
        }

        if ($request->filled('date_from')) {
            $query->where('datetime_start', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        // Portée statistique : restreinte pour les non-gestionnaires
        $isRestricted = !$user->hasAnyRole(['super_admin', 'admin', 'fleet_manager', 'controller', 'director']);

        $stats = [
            'total'       => VehicleRequest::when($isRestricted, fn($q) => $q->where('requester_id', $user->id))->count(),
            'pending'     => VehicleRequest::where('status', 'pending')
                ->when($isRestricted, fn($q) => $q->where('requester_id', $user->id))->count(),
            'active'      => VehicleRequest::whereIn('status', ['approved', 'confirmed', 'in_progress'])
                ->when($isRestricted, fn($q) => $q->where('requester_id', $user->id))->count(),
            'completed'   => VehicleRequest::where('status', 'completed')
                ->when($isRestricted, fn($q) => $q->where('requester_id', $user->id))->count(),
            // Urgentes : visibles seulement par les gestionnaires
            'urgent'      => $isRestricted ? 0 : VehicleRequest::where('status', 'pending')->where('is_urgent', true)->count(),
        ];

        return view('requests.index', compact('requests', 'stats'));
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(VehicleRequest $vehicleRequest): View
    {
        $vehicleRequest->load(['requester', 'vehicle.profilePhoto', 'driver', 'reviewedBy']);

        // Collaborateur et chauffeur ne peuvent voir que leurs propres demandes
        if (!Auth::user()->hasAnyRole(['super_admin', 'admin', 'fleet_manager', 'controller', 'director'])
            && $vehicleRequest->requester_id !== Auth::id()) {
            abort(403);
        }

        $availableVehicles = collect();
        $availableDrivers  = collect();

        if ($vehicleRequest->status === 'pending' && Auth::user()->canAny(['vehicle_requests.approve'])) {
            $start = Carbon::parse($vehicleRequest->datetime_start);
            $end   = Carbon::parse($vehicleRequest->datetime_end_planned);

            $availableVehicles = Vehicle::active()
                ->whereDoesntHave('assignments', fn($q) => $q->whereIn('status', ['planned', 'confirmed', 'in_progress'])
                    ->where('datetime_start', '<', $end)->where('datetime_end_planned', '>', $start))
                ->whereDoesntHave('vehicleRequests', fn($q) => $q->whereIn('status', ['approved', 'confirmed', 'in_progress'])
                    ->where('id', '!=', $vehicleRequest->id)
                    ->where('datetime_start', '<', $end)->where('datetime_end_planned', '>', $start))
                ->with('profilePhoto')->orderBy('brand')->get();

            // Chauffeurs actifs non occupés sur ce créneau
            $availableDrivers = Driver::active()
                ->whereDoesntHave('assignments', fn($q) => $q->whereIn('status', ['planned', 'confirmed', 'in_progress'])
                    ->where('datetime_start', '<', $end)->where('datetime_end_planned', '>', $start))
                ->orderBy('full_name')->get();
        }

        return view('requests.show', compact('vehicleRequest', 'availableVehicles', 'availableDrivers'));
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('requests.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'datetime_start'         => 'required|date|after_or_equal:now',
            'datetime_end_planned'   => 'required|date|after:datetime_start',
            'destination'            => 'required|string|max:255',
            'purpose'                => 'required|string|max:255',
            'passengers'             => 'required|integer|min:1|max:50',
            'vehicle_type_preferred' => 'required|in:any,city,sedan,suv,pickup,van,truck',
            'is_urgent'              => 'boolean',
            'requester_notes'        => 'nullable|string|max:1000',
        ]);

        $data['requester_id'] = Auth::id();
        $data['status']       = 'pending';
        $data['is_urgent']    = $request->boolean('is_urgent');

        $vr = VehicleRequest::create($data);

        // Notifier tous les approbateurs de la nouvelle demande
        $vr->load('requester');
        $approvers = User::permission('vehicle_requests.approve')
            ->where('status', 'active')
            ->where('id', '!=', Auth::id()) // ne pas notifier soi-même si on a la permission
            ->get();

        \Illuminate\Support\Facades\Notification::send(
            $approvers,
            new VehicleRequestSubmittedNotification($vr)
        );

        return redirect()->route('requests.show', $vr)
            ->with('swal_success', 'Demande soumise avec succès. En attente de validation.');
    }

    // ── Modification (seulement si pending) ───────────────────────────────

    public function edit(VehicleRequest $vehicleRequest): View
    {
        abort_if($vehicleRequest->status !== 'pending', 403, 'Cette demande ne peut plus être modifiée.');
        abort_if(
            !Auth::user()->hasAnyRole(['super_admin', 'admin', 'fleet_manager', 'controller', 'director'])
            && $vehicleRequest->requester_id !== Auth::id(),
            403
        );

        return view('requests.edit', compact('vehicleRequest'));
    }

    public function update(Request $request, VehicleRequest $vehicleRequest): RedirectResponse
    {
        abort_if($vehicleRequest->status !== 'pending', 403);
        abort_if(
            !Auth::user()->hasAnyRole(['super_admin', 'admin', 'fleet_manager', 'controller', 'director'])
            && $vehicleRequest->requester_id !== Auth::id(),
            403
        );

        $data = $request->validate([
            'datetime_start'         => 'required|date',
            'datetime_end_planned'   => 'required|date|after:datetime_start',
            'destination'            => 'required|string|max:255',
            'purpose'                => 'required|string|max:255',
            'passengers'             => 'required|integer|min:1|max:50',
            'vehicle_type_preferred' => 'required|in:any,city,sedan,suv,pickup,van,truck',
            'is_urgent'              => 'boolean',
            'requester_notes'        => 'nullable|string|max:1000',
        ]);
        $data['is_urgent'] = $request->boolean('is_urgent');

        $vehicleRequest->update($data);

        return redirect()->route('requests.show', $vehicleRequest)
            ->with('swal_success', 'Demande mise à jour.');
    }

    // ── Actions gestionnaire ───────────────────────────────────────────────

    /** pending → approved (avec véhicule attribué) */
    public function approve(Request $request, VehicleRequest $vehicleRequest): RedirectResponse
    {
        $request->validate([
            'vehicle_id'   => 'required|exists:vehicles,id',
            'driver_mode'  => 'required|in:assign,self,none',
            'driver_id'    => 'nullable|exists:drivers,id|required_if:driver_mode,assign',
            'review_notes' => 'nullable|string|max:500',
        ]);

        $selfDriving = $request->driver_mode === 'self';
        $driverId    = $request->driver_mode === 'assign' ? $request->driver_id : null;

        try {
            $this->service->approve(
                $vehicleRequest,
                $request->vehicle_id,
                Auth::id(),
                $request->review_notes,
                $driverId,
                $selfDriving
            );

            $msg = 'Demande approuvée — véhicule attribué';
            if ($selfDriving) {
                $msg .= ' (auto-conduite).';
            } elseif ($driverId) {
                $msg .= ' avec chauffeur.';
            } else {
                $msg .= '.';
            }

            return back()->with('swal_success', $msg);
        } catch (VehicleAvailabilityException $e) {
            return back()->with('swal_error', $e->getMessage());
        }
    }

    /** pending → rejected */
    public function reject(Request $request, VehicleRequest $vehicleRequest): RedirectResponse
    {
        $request->validate(['review_notes' => 'nullable|string|max:500']);

        try {
            $this->service->reject($vehicleRequest, Auth::id(), $request->review_notes);
            return back()->with('swal_warning', 'Demande rejetée.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('swal_error', $e->getMessage());
        }
    }

    /** approved → in_progress (prise en charge du véhicule, saisie km_start) */
    public function start(Request $request, VehicleRequest $vehicleRequest): RedirectResponse
    {
        abort_if($vehicleRequest->status !== 'approved', 403);

        $data = $request->validate([
            'km_start'             => 'required|integer|min:0',
            'condition_start'      => 'required|in:good,fair,poor',
            'condition_start_notes'=> 'nullable|string|max:500',
        ]);

        $vehicleRequest->update(array_merge($data, ['status' => 'in_progress']));

        if ($vehicleRequest->vehicle) {
            $vehicleRequest->vehicle->update(['status' => 'on_mission']);
        }

        return back()->with('swal_success', 'Départ enregistré.');
    }

    /** in_progress → completed (restitution du véhicule) */
    public function complete(Request $request, VehicleRequest $vehicleRequest): RedirectResponse
    {
        abort_if($vehicleRequest->status !== 'in_progress', 403);

        $data = $request->validate([
            'km_end'             => 'required|integer|min:' . ($vehicleRequest->km_start ?? 0),
            'condition_end'      => 'required|in:good,fair,poor',
            'condition_end_notes'=> 'nullable|string|max:500',
        ]);

        try {
            $result = $this->service->close($vehicleRequest, $data['km_end'], $data['condition_end'], $data['condition_end_notes'] ?? null);

            if ($vehicleRequest->vehicle) {
                $vehicleRequest->vehicle->update(['status' => 'available']);
            }

            return redirect()->route('requests.show', $vehicleRequest)
                ->with('swal_success', "Retour enregistré — " . number_format($result->km_total ?? 0) . " km parcourus.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('swal_error', $e->getMessage());
        }
    }

    /** Annuler une demande */
    public function cancel(Request $request, VehicleRequest $vehicleRequest): RedirectResponse
    {
        abort_if(in_array($vehicleRequest->status, ['completed', 'rejected', 'cancelled']), 403);

        // Collaborateur et chauffeur ne peuvent annuler que leurs propres demandes
        if (!Auth::user()->hasAnyRole(['super_admin', 'admin', 'fleet_manager', 'controller', 'director'])) {
            abort_if($vehicleRequest->requester_id !== Auth::id(), 403);
        }

        if ($vehicleRequest->status === 'in_progress' && $vehicleRequest->vehicle) {
            $vehicleRequest->vehicle->update(['status' => 'available']);
        }

        $vehicleRequest->update(['status' => 'cancelled']);

        return redirect()->route('requests.index')
            ->with('swal_warning', 'Demande annulée.');
    }
}
