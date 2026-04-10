<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCleaning;
use App\Notifications\CleaningScheduledNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CleaningController extends Controller
{
    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $showArchived = $request->boolean('archived');

        $query = $showArchived
            ? VehicleCleaning::onlyTrashed()->with(['vehicle.profilePhoto', 'driver', 'responsible'])
            : VehicleCleaning::with(['vehicle.profilePhoto', 'driver', 'responsible']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->whereHas('vehicle', fn($vq) =>
                    $vq->where('plate', 'like', "%{$q}%")
                       ->orWhere('brand', 'like', "%{$q}%")
                       ->orWhere('model', 'like', "%{$q}%")
                );
            });
        }

        if (!$showArchived && $request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('cleaning_type', $request->type);
        }

        if ($request->filled('week')) {
            $date = Carbon::parse($request->week);
            $query->whereBetween('scheduled_date', [
                $date->startOfWeek()->toDateString(),
                $date->endOfWeek()->toDateString(),
            ]);
        }

        $cleanings = $query->orderByDesc('scheduled_date')->paginate(15)->withQueryString();

        $stats = [
            'total'     => VehicleCleaning::count(),
            'scheduled' => VehicleCleaning::where('status', 'scheduled')->count(),
            'confirmed' => VehicleCleaning::where('status', 'confirmed')->count(),
            'completed' => VehicleCleaning::where('status', 'completed')->count(),
            'missed'    => VehicleCleaning::where('status', 'missed')->count(),
            'archived'  => VehicleCleaning::onlyTrashed()->count(),
        ];

        return view('cleanings.index', compact('cleanings', 'stats', 'showArchived'));
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(): View
    {
        $vehicles    = Vehicle::active()->with('profilePhoto')->orderBy('brand')->get();
        $drivers     = Driver::active()->orderBy('full_name')->get();
        $collaborators = User::role('collaborator')->orderBy('name')->get();

        // Prochain samedi
        $nextSaturday = Carbon::now()->next(Carbon::SATURDAY)->format('Y-m-d');

        return view('cleanings.create', compact('vehicles', 'drivers', 'collaborators', 'nextSaturday'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id'    => ['required', 'exists:vehicles,id'],
            'driver_id'     => ['nullable', 'exists:drivers,id'],
            'user_id'       => ['nullable', 'exists:users,id'],
            'scheduled_date'=> ['required', 'date', 'after_or_equal:today'],
            'scheduled_time'=> ['required', 'date_format:H:i'],
            'cleaning_type' => ['required', 'in:exterior,interior,full'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        if (empty($data['driver_id']) && empty($data['user_id'])) {
            return back()->withErrors(['driver_id' => 'Sélectionnez un chauffeur ou un collaborateur.'])->withInput();
        }

        $data['created_by'] = Auth::id();
        $data['status']     = 'scheduled';

        $cleaning = VehicleCleaning::create($data);

        // Notifier le responsable
        $this->notifyResponsible($cleaning);

        return redirect()->route('cleanings.show', $cleaning)
            ->with('swal_success', 'Nettoyage planifié avec succès.');
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(VehicleCleaning $cleaning): View
    {
        $cleaning->load(['vehicle.profilePhoto', 'driver', 'responsible', 'createdBy', 'confirmedBy']);
        return view('cleanings.show', compact('cleaning'));
    }

    // ── Édition ────────────────────────────────────────────────────────────

    public function edit(VehicleCleaning $cleaning): View
    {
        $vehicles      = Vehicle::active()->with('profilePhoto')->orderBy('brand')->get();
        $drivers       = Driver::active()->orderBy('full_name')->get();
        $collaborators = User::role('collaborator')->orderBy('name')->get();

        return view('cleanings.edit', compact('cleaning', 'vehicles', 'drivers', 'collaborators'));
    }

    public function update(Request $request, VehicleCleaning $cleaning): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id'    => ['required', 'exists:vehicles,id'],
            'driver_id'     => ['nullable', 'exists:drivers,id'],
            'user_id'       => ['nullable', 'exists:users,id'],
            'scheduled_date'=> ['required', 'date'],
            'scheduled_time'=> ['required', 'date_format:H:i'],
            'cleaning_type' => ['required', 'in:exterior,interior,full'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        if (empty($data['driver_id']) && empty($data['user_id'])) {
            return back()->withErrors(['driver_id' => 'Sélectionnez un chauffeur ou un collaborateur.'])->withInput();
        }

        $cleaning->update($data);

        // Re-notifier si la date ou l'heure a changé
        if ($cleaning->wasChanged(['scheduled_date', 'scheduled_time', 'vehicle_id'])) {
            $this->notifyResponsible($cleaning);
        }

        return redirect()->route('cleanings.show', $cleaning)
            ->with('swal_success', 'Nettoyage mis à jour.');
    }

    // ── Confirmation par le chauffeur / responsable ────────────────────────

    public function confirm(VehicleCleaning $cleaning): RedirectResponse
    {
        if (!in_array($cleaning->status, ['scheduled'])) {
            return back()->with('swal_error', 'Ce nettoyage ne peut pas être confirmé dans son état actuel.');
        }

        $cleaning->update([
            'status'       => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => Auth::id(),
        ]);

        return back()->with('swal_success', 'Nettoyage confirmé. Merci !');
    }

    // ── Marquer comme effectué (avec preuve photo optionnelle) ─────────────

    public function complete(Request $request, VehicleCleaning $cleaning): RedirectResponse
    {
        if (!in_array($cleaning->status, ['scheduled', 'confirmed'])) {
            return back()->with('swal_error', 'Ce nettoyage ne peut pas être complété dans son état actuel.');
        }

        $data = $request->validate([
            'completion_notes' => ['nullable', 'string', 'max:1000'],
            'completion_proof' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('completion_proof')) {
            $data['completion_proof'] = $request->file('completion_proof')
                ->store("cleanings/{$cleaning->id}", 'public');
        }

        $cleaning->update(array_merge($data, [
            'status'       => 'completed',
            'completed_at' => now(),
        ]));

        return back()->with('swal_success', 'Nettoyage marqué comme effectué.');
    }

    // ── Annulation ─────────────────────────────────────────────────────────

    public function cancel(Request $request, VehicleCleaning $cleaning): RedirectResponse
    {
        if (in_array($cleaning->status, ['completed', 'cancelled'])) {
            return back()->with('swal_error', 'Ce nettoyage ne peut pas être annulé.');
        }

        $cleaning->update(['status' => 'cancelled']);

        return back()->with('swal_success', 'Nettoyage annulé.');
    }

    // ── Marquer comme manqué ───────────────────────────────────────────────

    public function markMissed(VehicleCleaning $cleaning): RedirectResponse
    {
        if (!in_array($cleaning->status, ['scheduled', 'confirmed'])) {
            return back()->with('swal_error', 'Action non disponible.');
        }

        $cleaning->update(['status' => 'missed']);

        return back()->with('swal_success', 'Nettoyage marqué comme manqué.');
    }

    // ── Archivage (soft delete) ────────────────────────────────────────────

    public function destroy(VehicleCleaning $cleaning): RedirectResponse
    {
        $cleaning->delete();
        return redirect()->route('cleanings.index')
            ->with('swal_success', "Nettoyage #{$cleaning->id} archivé.");
    }

    // ── Restauration ───────────────────────────────────────────────────────

    public function restore(int $id): RedirectResponse
    {
        $cleaning = VehicleCleaning::onlyTrashed()->findOrFail($id);
        $cleaning->restore();
        return redirect()->route('cleanings.index')
            ->with('swal_success', "Nettoyage #{$id} restauré.");
    }

    // ── Utilitaires ────────────────────────────────────────────────────────

    private function notifyResponsible(VehicleCleaning $cleaning): void
    {
        $cleaning->load(['driver.user', 'responsible']);

        // Notifier le chauffeur professionnel (via son compte utilisateur)
        if ($cleaning->driver && $cleaning->driver->user) {
            $cleaning->driver->user->notify(new CleaningScheduledNotification($cleaning));
        }

        // Notifier le collaborateur
        if ($cleaning->responsible) {
            $cleaning->responsible->notify(new CleaningScheduledNotification($cleaning));
        }
    }
}
