<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Alert::with(['vehicle', 'driver', 'user', 'processedBy']);

        // ── Filtres ──────────────────────────────────────────────────────────
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            // Par défaut : alertes non traitées uniquement
            if (! $request->filled('status')) {
                $query->whereIn('status', ['new', 'seen']);
            }
        }

        if ($request->filled('severity') && $request->severity !== 'all') {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $alerts = $query->orderByRaw("FIELD(severity,'critical','warning','info')")
                        ->orderBy('created_at', 'desc')
                        ->paginate(20)->withQueryString();

        // ── Statistiques ─────────────────────────────────────────────────────
        $stats = [
            'total'     => Alert::whereIn('status', ['new', 'seen'])->count(),
            'critiques' => Alert::where('severity', 'critical')->whereIn('status', ['new', 'seen'])->count(),
            'warnings'  => Alert::where('severity', 'warning')->whereIn('status', ['new', 'seen'])->count(),
            'traitees'  => Alert::where('status', 'processed')->count(),
        ];

        // Marquer automatiquement comme "vue" les alertes "new" de cette page
        $newIds = $alerts->where('status', 'new')->pluck('id');
        if ($newIds->isNotEmpty()) {
            Alert::whereIn('id', $newIds)->update(['status' => 'seen']);
        }

        return view('alerts.index', compact('alerts', 'stats'));
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Alert $alert): View
    {
        $alert->load(['vehicle', 'driver', 'user', 'infraction', 'vehicleRequest', 'processedBy']);
        $alert->markAsSeen();

        return view('alerts.show', compact('alert'));
    }

    // ── Traiter une alerte ─────────────────────────────────────────────────

    /**
     * Marque l'alerte comme traitée avec des notes optionnelles.
     * Réservé aux gestionnaires (permission alerts.manage).
     */
    public function process(Request $request, Alert $alert): RedirectResponse
    {
        $data = $request->validate([
            'process_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $alert->markAsProcessed(auth()->id(), $data['process_notes'] ?? null);

        return back()->with('swal_success', 'Alerte marquée comme traitée.');
    }

    // ── Traitement en masse ────────────────────────────────────────────────

    /**
     * Marque plusieurs alertes comme traitées d'un coup.
     */
    public function bulkProcess(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'alert_ids'   => ['required', 'array'],
            'alert_ids.*' => ['integer', 'exists:alerts,id'],
        ]);

        Alert::whereIn('id', $data['alert_ids'])
             ->whereIn('status', ['new', 'seen'])
             ->update([
                 'status'       => 'processed',
                 'processed_by' => auth()->id(),
                 'processed_at' => now(),
             ]);

        $count = count($data['alert_ids']);

        return back()->with('swal_success', "{$count} alerte(s) marquée(s) comme traitées.");
    }
}
