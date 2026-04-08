<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GarageController extends Controller
{
    // ── Liste ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Garage::withCount('repairs');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('city', 'like', "%{$q}%")
                   ->orWhere('contact_person', 'like', "%{$q}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('approved')) {
            $query->where('is_approved', $request->boolean('approved'));
        }

        $garages = $query->orderBy('name')->paginate(15)->withQueryString();

        $stats = [
            'total'    => Garage::count(),
            'approuves'=> Garage::where('is_approved', true)->count(),
            'en_attente'=> Garage::where('is_approved', false)->count(),
        ];

        return view('garages.index', compact('garages', 'stats'));
    }

    // ── Création ───────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('garages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150', 'unique:garages,name'],
            'type'            => ['required', 'in:general,body_repair,electrical,tires,painting,glass,specialized'],
            'address'         => ['nullable', 'string', 'max:255'],
            'city'            => ['nullable', 'string', 'max:100'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:150'],
            'contact_person'  => ['nullable', 'string', 'max:100'],
            'specializations' => ['nullable', 'array'],
            'specializations.*'=> ['string', 'max:50'],
            'rating'          => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_approved'     => ['boolean'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $data['is_approved'] = $request->boolean('is_approved');
        $data['created_by']  = auth()->id();

        $garage = Garage::create($data);

        return redirect()->route('garages.show', $garage)
                         ->with('swal_success', "Garage \"{$garage->name}\" ajouté.");
    }

    // ── Détail ─────────────────────────────────────────────────────────────

    public function show(Garage $garage): View
    {
        $garage->load(['repairs' => fn($q) => $q->with('vehicle', 'incident')->latest()->limit(10)]);
        $repairStats = [
            'total'    => $garage->repairs()->count(),
            'en_cours' => $garage->repairs()->inProgress()->count(),
            'terminees'=> $garage->repairs()->completed()->count(),
        ];

        return view('garages.show', compact('garage', 'repairStats'));
    }

    // ── Édition ────────────────────────────────────────────────────────────

    public function edit(Garage $garage): View
    {
        return view('garages.edit', compact('garage'));
    }

    public function update(Request $request, Garage $garage): RedirectResponse
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150', 'unique:garages,name,' . $garage->id],
            'type'            => ['required', 'in:general,body_repair,electrical,tires,painting,glass,specialized'],
            'address'         => ['nullable', 'string', 'max:255'],
            'city'            => ['nullable', 'string', 'max:100'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:150'],
            'contact_person'  => ['nullable', 'string', 'max:100'],
            'specializations' => ['nullable', 'array'],
            'specializations.*'=> ['string', 'max:50'],
            'rating'          => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_approved'     => ['boolean'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $data['is_approved'] = $request->boolean('is_approved');
        $garage->update($data);

        return redirect()->route('garages.show', $garage)
                         ->with('swal_success', "Garage \"{$garage->name}\" mis à jour.");
    }

    // ── Approbation rapide ─────────────────────────────────────────────────

    public function toggleApproved(Garage $garage): RedirectResponse
    {
        $garage->update(['is_approved' => ! $garage->is_approved]);

        $msg = $garage->is_approved
            ? "Garage \"{$garage->name}\" approuvé."
            : "Approbation de \"{$garage->name}\" retirée.";

        return back()->with('swal_success', $msg);
    }

    // ── Suppression (soft) ─────────────────────────────────────────────────

    public function destroy(Garage $garage): RedirectResponse
    {
        $name = $garage->name;
        $garage->delete();

        return redirect()->route('garages.index')
                         ->with('swal_success', "Garage \"{$name}\" supprimé.");
    }
}
