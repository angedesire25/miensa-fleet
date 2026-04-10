<?php

namespace App\Http\Controllers;

use App\Exceptions\AssignmentConflictException;
use App\Models\Assignment;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Services\AssignmentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function __construct(private readonly AssignmentService $service) {}

    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user         = Auth::user();
        $showArchived = $request->boolean('archived');

        $query = $showArchived
            ? Assignment::onlyTrashed()->with(['driver', 'collaborator', 'vehicle.profilePhoto', 'validatedBy'])
            : Assignment::with(['driver', 'collaborator', 'vehicle.profilePhoto', 'validatedBy']);

        // Chauffeur : ne voir que ses propres affectations via son profil driver
        // (liaison User → driver_id → Assignment.driver_id)
        if ($user->hasRole('driver_user')) {
            $query->where('driver_id', $user->driver_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->whereHas('driver', fn($d) => $d->where('full_name', 'like', "%{$q}%")->orWhere('matricule', 'like', "%{$q}%"))
                   ->orWhereHas('collaborator', fn($u) => $u->where('name', 'like', "%{$q}%"))
                   ->orWhereHas('vehicle', fn($v) => $v->where('plate', 'like', "%{$q}%")->orWhere('brand', 'like', "%{$q}%"))
                   ->orWhere('destination', 'like', "%{$q}%")
                   ->orWhere('mission', 'like', "%{$q}%");
            });
        }

        if (!$showArchived && $request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->where('datetime_start', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('datetime_start', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $assignments = $query->latest('datetime_start')->paginate(15)->withQueryString();

        // Statistiques : restreintes au chauffeur connecté si rôle driver_user
        $statsQuery = fn() => $user->hasRole('driver_user')
            ? Assignment::where('driver_id', $user->driver_id)
            : new \App\Models\Assignment();

        $stats = [
            'total'       => $statsQuery()->count(),
            'planned'     => $statsQuery()->whereIn('status', ['planned', 'confirmed'])->count(),
            'in_progress' => $statsQuery()->where('status', 'in_progress')->count(),
            'completed'   => $statsQuery()->where('status', 'completed')->count(),
            'cancelled'   => $statsQuery()->where('status', 'cancelled')->count(),
            'archived'    => $statsQuery()->onlyTrashed()->count(),
        ];

        return view('assignments.index', compact('assignments', 'stats', 'showArchived'));
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Assignment $assignment): View
    {
        $assignment->load(['driver', 'vehicle.profilePhoto', 'validatedBy', 'createdBy',
                           'departurePhotos', 'returnPhotos']);
        return view('assignments.show', compact('assignment'));
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        // Pré-remplissage si on arrive depuis fiche véhicule ou chauffeur
        $preVehicle = $request->filled('vehicle_id') ? Vehicle::find($request->vehicle_id) : null;
        $preDriver  = $request->filled('driver_id')  ? Driver::find($request->driver_id)   : null;

        $vehicles      = Vehicle::active()->with('profilePhoto')->orderBy('brand')->orderBy('model')->get();
        $drivers       = Driver::active()->orderBy('full_name')->get();
        $collaborators = User::role('collaborator')->orderBy('name')->get();

        return view('assignments.create', compact('vehicles', 'drivers', 'collaborators', 'preVehicle', 'preDriver'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'driver_id'            => 'nullable|exists:drivers,id',
            'user_id'              => 'nullable|exists:users,id',
            'vehicle_id'           => 'required|exists:vehicles,id',
            'type'                 => 'required|in:mission,daily,courses,permanent,replacement,trial',
            'datetime_start'       => 'required|date|after_or_equal:now',
            'datetime_end_planned' => 'required|date|after:datetime_start',
            'mission'              => 'nullable|string|max:255',
            'destination'          => 'nullable|string|max:255',
            'km_start'             => 'nullable|integer|min:0',
            'condition_start'      => 'nullable|in:good,fair,poor',
            'condition_start_notes'=> 'nullable|string|max:500',
        ]);

        if (empty($data['driver_id']) && empty($data['user_id'])) {
            return back()->withInput()->withErrors(['driver_id' => 'Veuillez sélectionner un chauffeur ou un collaborateur.']);
        }

        $data['created_by'] = Auth::id();

        try {
            $assignment = $this->service->createAssignment($data);
            return redirect()->route('assignments.show', $assignment)
                ->with('swal_success', 'Affectation créée avec succès.');
        } catch (AssignmentConflictException $e) {
            return back()->withInput()
                ->with('swal_error', implode(' ', $e->getReasons()));
        }
    }

    // ── Modification ───────────────────────────────────────────────────────

    public function edit(Assignment $assignment): View
    {
        abort_if(
            in_array($assignment->status, ['completed', 'cancelled']),
            403,
            'Impossible de modifier une affectation terminée ou annulée.'
        );

        $vehicles      = Vehicle::active()->with('profilePhoto')->orderBy('brand')->get();
        $drivers       = Driver::active()->orderBy('full_name')->get();
        $collaborators = User::role('collaborator')->orderBy('name')->get();

        return view('assignments.edit', compact('assignment', 'vehicles', 'drivers', 'collaborators'));
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_if(in_array($assignment->status, ['completed', 'cancelled']), 403);

        $data = $request->validate([
            'mission'     => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'type'        => 'required|in:mission,daily,courses,permanent,replacement,trial',
            'datetime_end_planned' => 'required|date|after:datetime_start',
        ]);

        $assignment->update($data);

        return redirect()->route('assignments.show', $assignment)
            ->with('swal_success', 'Affectation mise à jour.');
    }

    // ── Transitions de statut ──────────────────────────────────────────────

    /** planned → confirmed */
    public function confirm(Assignment $assignment): RedirectResponse
    {
        abort_if($assignment->status !== 'planned', 403, "Statut actuel : {$assignment->status}");

        $assignment->update([
            'status'       => 'confirmed',
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return back()->with('swal_success', 'Affectation confirmée — bon de sortie prêt.');
    }

    /** confirmed/planned → in_progress (départ effectif, saisie km_start + photos) */
    public function start(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_if(!in_array($assignment->status, ['planned', 'confirmed']), 403);

        $data = $request->validate([
            'km_start'              => 'required|integer|min:0',
            'condition_start'       => 'required|in:good,fair,poor',
            'condition_start_notes' => 'nullable|string|max:500',
            'photos_departure.*'    => 'nullable|image|mimes:jpeg,jpg,png,webp|max:8192',
        ]);

        $assignment->update([
            'km_start'              => $data['km_start'],
            'condition_start'       => $data['condition_start'],
            'condition_start_notes' => $data['condition_start_notes'] ?? null,
            'status'                => 'in_progress',
        ]);

        // Sauvegarder les photos de départ (4 faces)
        $faces = ['avant', 'arriere', 'gauche', 'droite'];
        foreach ($faces as $face) {
            $key = "photos_departure_{$face}";
            if ($request->hasFile($key)) {
                $path = $request->file($key)->store("assignments/{$assignment->id}/departure", 'public');
                VehiclePhoto::create([
                    'vehicle_id'     => $assignment->vehicle_id,
                    'photoable_type' => Assignment::class,
                    'photoable_id'   => $assignment->id,
                    'context'        => 'departure',
                    'caption'        => $face,
                    'file_path'      => $path,
                    'uploaded_by'    => Auth::id(),
                ]);
            }
        }

        // Mettre le véhicule en mission
        $assignment->vehicle->update(['status' => 'on_mission', 'current_driver_id' => $assignment->driver_id]);

        return back()->with('swal_success', 'Départ enregistré — affectation en cours.');
    }

    /** in_progress → completed (retour, saisie km_end + photos) */
    public function complete(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_if($assignment->status !== 'in_progress', 403);

        $data = $request->validate([
            'km_end'              => 'required|integer|min:' . ($assignment->km_start ?? 0),
            'condition_end'       => 'required|in:good,fair,poor',
            'condition_end_notes' => 'nullable|string|max:500',
            'photos_return.*'     => 'nullable|image|mimes:jpeg,jpg,png,webp|max:8192',
        ]);

        // Sauvegarder les photos de retour (4 faces) avant de clôturer
        $faces = ['avant', 'arriere', 'gauche', 'droite'];
        foreach ($faces as $face) {
            $key = "photos_return_{$face}";
            if ($request->hasFile($key)) {
                $path = $request->file($key)->store("assignments/{$assignment->id}/return", 'public');
                VehiclePhoto::create([
                    'vehicle_id'     => $assignment->vehicle_id,
                    'photoable_type' => Assignment::class,
                    'photoable_id'   => $assignment->id,
                    'context'        => 'return',
                    'caption'        => $face,
                    'file_path'      => $path,
                    'uploaded_by'    => Auth::id(),
                ]);
            }
        }

        try {
            $this->service->closeAssignment(
                $assignment,
                $data['km_end'],
                $data['condition_end'],
                $data['condition_end_notes'] ?? null
            );

            // Libérer le véhicule
            $assignment->vehicle->update(['status' => 'available', 'current_driver_id' => null]);

            return redirect()->route('assignments.show', $assignment)
                ->with('swal_success', "Retour enregistré — " . number_format($assignment->fresh()->km_total) . " km parcourus.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('swal_error', $e->getMessage());
        }
    }

    /** Annuler une affectation */
    public function cancel(Request $request, Assignment $assignment): RedirectResponse
    {
        abort_if(in_array($assignment->status, ['completed', 'cancelled']), 403);

        $request->validate(['cancellation_reason' => 'nullable|string|max:500']);

        // Si en cours, libérer le véhicule
        if ($assignment->status === 'in_progress') {
            $assignment->vehicle->update(['status' => 'available', 'current_driver_id' => null]);
        }

        $assignment->update([
            'status'               => 'cancelled',
            'cancellation_reason'  => $request->cancellation_reason,
        ]);

        return redirect()->route('assignments.index')
            ->with('swal_warning', 'Affectation annulée.');
    }

    /** Suppression (soft delete) */
    public function destroy(Assignment $assignment): RedirectResponse
    {
        abort_if($assignment->status === 'in_progress', 403, 'Impossible de supprimer une affectation en cours.');
        $assignment->delete();
        return redirect()->route('assignments.index')
            ->with('swal_success', 'Affectation archivée.');
    }

    /** Restauration depuis les archives */
    public function restore(int $id): RedirectResponse
    {
        $assignment = Assignment::onlyTrashed()->findOrFail($id);
        $assignment->restore();
        return redirect()->route('assignments.index')
            ->with('swal_success', "Affectation #{$id} restaurée.");
    }

    /** Suppression définitive (irréversible) — réservée aux admins */
    public function forceDestroy(int $id): RedirectResponse
    {
        $assignment = Assignment::onlyTrashed()->findOrFail($id);
        $assignment->forceDelete();
        return redirect()->route('assignments.index', ['archived' => 1])
            ->with('swal_success', "Affectation #{$id} supprimée définitivement.");
    }
}
