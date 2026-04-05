<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Inspection;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InspectionController extends Controller
{
    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user  = Auth::user();
        $query = Inspection::with(['vehicle.profilePhoto', 'inspector', 'driver']);

        // Chauffeur & collaborateur : uniquement leurs fiches
        if ($user->hasAnyRole(['driver_user', 'collaborator'])) {
            $query->where('inspector_id', $user->id);
        }

        // Par défaut, masquer les fiches archivées (sauf si filtre actif)
        if ($request->boolean('show_archived')) {
            $query->archived();
        } else {
            $query->active();
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->whereHas('vehicle', fn($v) => $v->where('plate', 'like', "%{$q}%")->orWhere('brand', 'like', "%{$q}%"))
                   ->orWhere('location', 'like', "%{$q}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('inspection_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('critical_only')) {
            $query->where('has_critical_issue', true);
        }

        if ($request->filled('date_from')) {
            $query->where('inspected_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('inspected_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $inspections = $query->latest('inspected_at')->paginate(20)->withQueryString();

        // Statistiques globales (sur les fiches actives uniquement)
        $baseQuery = $user->hasAnyRole(['driver_user', 'collaborator'])
            ? Inspection::active()->where('inspector_id', $user->id)
            : Inspection::active();

        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'today'      => (clone $baseQuery)->whereDate('inspected_at', today())->count(),
            'critical'   => (clone $baseQuery)->where('has_critical_issue', true)
                                ->where('inspected_at', '>=', now()->subDays(7))->count(),
            'submitted'  => (clone $baseQuery)->where('status', 'submitted')->count(),
            'archived'   => $user->hasAnyRole(['driver_user', 'collaborator'])
                                ? Inspection::archived()->where('inspector_id', $user->id)->count()
                                : Inspection::archived()->count(),
        ];

        $vehicles = Vehicle::active()->orderBy('brand')->get(['id', 'plate', 'brand', 'model']);

        return view('inspections.index', compact('inspections', 'stats', 'vehicles'));
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Inspection $inspection): View
    {
        $inspection->load(['vehicle.profilePhoto', 'inspector', 'driver', 'assignment', 'vehicleRequest', 'validatedBy']);
        return view('inspections.show', compact('inspection'));
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $vehicles = Vehicle::active()->with('profilePhoto')->orderBy('brand')->orderBy('model')->get();
        $drivers  = Driver::active()->orderBy('full_name')->get();

        $preVehicle = $request->filled('vehicle_id') ? Vehicle::find($request->vehicle_id) : null;
        $preDriver  = $request->filled('driver_id')  ? Driver::find($request->driver_id)  : null;

        return view('inspections.create', compact('vehicles', 'drivers', 'preVehicle', 'preDriver'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateInspection($request);

        $data['inspector_id'] = Auth::id();
        $data['status']       = $request->input('action') === 'draft' ? 'draft' : 'submitted';

        // Gestion des photos carrosserie uploadées
        $data['body_photos'] = $this->handlePhotoUploads($request, null);

        $inspection = Inspection::create($data);

        $msg = $data['status'] === 'draft'
            ? 'Fiche enregistrée en brouillon.'
            : 'Fiche de contrôle soumise avec succès.';

        if ($inspection->has_critical_issue) {
            return redirect()->route('inspections.show', $inspection)
                ->with('swal_warning', $msg . ' ⚠️ Anomalie critique détectée !');
        }

        return redirect()->route('inspections.show', $inspection)
            ->with('swal_success', $msg);
    }

    // ── Modification ───────────────────────────────────────────────────────

    public function edit(Inspection $inspection): View
    {
        // La fiche doit être en brouillon ou à corriger (et non archivée)
        abort_if(!$inspection->canEdit(), 403, 'Cette fiche ne peut plus être modifiée.');

        // Chauffeur et collaborateur ne peuvent modifier que leurs propres fiches
        $this->abortIfNotOwner($inspection);

        $vehicles = Vehicle::active()->with('profilePhoto')->orderBy('brand')->get();
        $drivers  = Driver::active()->orderBy('full_name')->get();

        return view('inspections.edit', compact('inspection', 'vehicles', 'drivers'));
    }

    public function update(Request $request, Inspection $inspection): RedirectResponse
    {
        abort_if(!$inspection->canEdit(), 403);

        // Chauffeur et collaborateur ne peuvent modifier que leurs propres fiches
        $this->abortIfNotOwner($inspection);

        $data = $this->validateInspection($request, $inspection);
        $data['status'] = $request->input('action') === 'draft' ? 'draft' : 'submitted';

        // Gestion photos : garder les existantes + ajouter les nouvelles
        $data['body_photos'] = $this->handlePhotoUploads($request, $inspection);

        $inspection->update($data);

        return redirect()->route('inspections.show', $inspection)
            ->with('swal_success', 'Fiche de contrôle mise à jour.');
    }

    // ── Validation / Rejet ─────────────────────────────────────────────────

    /** submitted → validated */
    public function validate(Request $request, Inspection $inspection): RedirectResponse
    {
        abort_if($inspection->status !== 'submitted', 403);

        $inspection->update([
            'status'           => 'validated',
            'validated_by'     => Auth::id(),
            'validated_at'     => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('swal_success', 'Fiche de contrôle validée.');
    }

    /** submitted|validated → rejected (renvoi pour correction) */
    public function reject(Request $request, Inspection $inspection): RedirectResponse
    {
        abort_if(!in_array($inspection->status, ['submitted', 'validated']), 403);

        $request->validate(['rejection_reason' => 'nullable|string|max:500']);

        $inspection->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'validated_by'     => null,
            'validated_at'     => null,
        ]);

        return back()->with('swal_warning', 'Fiche renvoyée pour correction.');
    }

    // ── Archivage ─────────────────────────────────────────────────────────

    /** Archiver une fiche (masquer des listes) — réservé aux fiches validées */
    public function archive(Inspection $inspection): RedirectResponse
    {
        abort_if($inspection->isArchived(), 403, 'Cette fiche est déjà archivée.');

        $inspection->update(['archived_at' => now()]);

        return back()->with('swal_success', 'Fiche archivée.');
    }

    /** Désarchiver une fiche */
    public function unarchive(Inspection $inspection): RedirectResponse
    {
        abort_if(!$inspection->isArchived(), 403, 'Cette fiche n\'est pas archivée.');

        $inspection->update(['archived_at' => null]);

        return back()->with('swal_success', 'Fiche désarchivée.');
    }

    /** Supprimer une photo carrosserie existante */
    public function deletePhoto(Request $request, Inspection $inspection): RedirectResponse
    {
        $request->validate(['photo' => 'required|string']);

        $photos = $inspection->body_photos ?? [];

        // Supprimer le fichier du disque
        if (Storage::disk('public')->exists($request->photo)) {
            Storage::disk('public')->delete($request->photo);
        }

        // Retirer du tableau JSON
        $photos = array_values(array_filter($photos, fn($p) => $p !== $request->photo));
        $inspection->update(['body_photos' => $photos]);

        return back()->with('swal_success', 'Photo supprimée.');
    }

    // ── Suppression ────────────────────────────────────────────────────────

    public function destroy(Inspection $inspection): RedirectResponse
    {
        abort_if($inspection->status === 'validated', 403, 'Impossible de supprimer une fiche validée.');

        // Supprimer les photos associées du stockage
        foreach ($inspection->body_photos ?? [] as $photo) {
            if (Storage::disk('public')->exists($photo)) {
                Storage::disk('public')->delete($photo);
            }
        }

        $inspection->delete();

        return redirect()->route('inspections.index')
            ->with('swal_success', 'Fiche supprimée.');
    }

    // ── Gestion des photos (upload) ────────────────────────────────────────

    /**
     * Gère l'upload des nouvelles photos et la suppression des photos cochées.
     * Retourne le tableau JSON final des chemins.
     */
    private function handlePhotoUploads(Request $request, ?Inspection $inspection): array
    {
        // Photos existantes (mode édition)
        $existing = $inspection?->body_photos ?? [];

        // Supprimer les photos sélectionnées pour suppression
        $toDelete = $request->input('delete_photos', []);
        foreach ($toDelete as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $existing = array_values(array_filter($existing, fn($p) => !in_array($p, $toDelete)));

        // Ajouter les nouvelles photos uploadées
        if ($request->hasFile('body_photos_upload')) {
            foreach ($request->file('body_photos_upload') as $file) {
                $path = $file->store('inspections/photos', 'public');
                $existing[] = $path;
            }
        }

        return $existing;
    }

    // ── Contrôle de propriété (chauffeur / collaborateur) ─────────────────

    /**
     * Les rôles driver_user et collaborator ne peuvent accéder
     * qu'aux fiches qu'ils ont eux-mêmes créées (inspector_id = auth id).
     * Les gestionnaires et admins n'ont aucune restriction.
     */
    private function abortIfNotOwner(Inspection $inspection): void
    {
        $user = Auth::user();
        if ($user->hasAnyRole(['driver_user', 'collaborator'])
            && $inspection->inspector_id !== $user->id) {
            abort(403, 'Vous ne pouvez modifier que vos propres fiches.');
        }
    }

    // ── Validation des données du formulaire ───────────────────────────────

    private function validateInspection(Request $request, ?Inspection $inspection = null): array
    {
        return $request->validate([
            'vehicle_id'                => 'required|exists:vehicles,id',
            'driver_id'                 => 'nullable|exists:drivers,id',
            'assignment_id'             => 'nullable|exists:assignments,id',
            'request_id'                => 'nullable|exists:vehicle_requests,id',
            'inspected_at'              => 'required|date',
            'location'                  => 'nullable|string|max:150',
            'inspection_type'           => 'required|in:departure,return,routine',
            // 14 points de contrôle
            'km'                        => 'nullable|integer|min:0',
            'oil_level'                 => 'nullable|in:low,medium,high',
            'oil_notes'                 => 'nullable|string|max:255',
            'coolant_level'             => 'nullable|in:low,medium,high',
            'brake_fluid_level'         => 'nullable|in:low,medium,high',
            'tire_pressure'             => 'nullable|in:low,medium,ok',
            'tire_notes'                => 'nullable|string|max:255',
            'fuel_level_pct'            => 'nullable|integer|min:0|max:100',
            'insurance_status'          => 'nullable|in:present,absent,expired',
            'insurance_expiry'          => 'nullable|date',
            'technical_control_status'  => 'nullable|in:present,absent,expired',
            'technical_control_expiry'  => 'nullable|date',
            'registration_present'      => 'boolean',
            'oil_change_status'         => 'nullable|in:ok,due_soon,overdue',
            'oil_change_date'           => 'nullable|date',
            'body_notes'                => 'nullable|string|max:1000',
            // Photos carrosserie : tableaux de fichiers image (max 5 Mo chacune, 10 max)
            'body_photos_upload'        => 'nullable|array|max:10',
            'body_photos_upload.*'      => 'image|mimes:jpeg,png,webp|max:5120',
            'delete_photos'             => 'nullable|array',
            'delete_photos.*'           => 'string',
            'lights_status'             => 'nullable|in:ok,minor_issue,critical',
            'lights_notes'              => 'nullable|string|max:500',
            'brakes_status'             => 'nullable|in:ok,minor_issue,critical',
            'brakes_notes'              => 'nullable|string|max:500',
            'general_observations'      => 'nullable|string|max:2000',
        ]);
    }
}
