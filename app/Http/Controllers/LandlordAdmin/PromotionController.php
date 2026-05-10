<?php

namespace App\Http\Controllers\LandlordAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'actives'); // 'actives' | 'archives'

        $promotions = Promotion::with('plan')
            ->when($tab === 'archives', fn($q) => $q->archived(),    fn($q) => $q->notArchived())
            ->latest()
            ->get();

        $countActives  = Promotion::notArchived()->count();
        $countArchives = Promotion::archived()->count();

        return view('landlord-admin.promotions.index', compact(
            'promotions', 'tab', 'countActives', 'countArchives'
        ));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('landlord-admin.promotions.form', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request);
        Promotion::create($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion créée avec succès.');
    }

    public function edit(Promotion $promotion)
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('landlord-admin.promotions.form', compact('promotion', 'plans'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validate($request);
        $promotion->update($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion mise à jour.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return back()->with('success', 'Promotion supprimée.');
    }

    public function toggleActive(Promotion $promotion)
    {
        $promotion->update(['is_active' => !$promotion->is_active]);
        return back()->with('success', 'Statut de la promotion mis à jour.');
    }

    public function archive(Promotion $promotion)
    {
        $promotion->archive();
        return redirect()->route('admin.promotions.index')
            ->with('success', "Promotion « {$promotion->label} » archivée.");
    }

    public function unarchive(Promotion $promotion)
    {
        $promotion->unarchive();
        return redirect()->route('admin.promotions.index', ['tab' => 'archives'])
            ->with('success', "Promotion « {$promotion->label} » restaurée.");
    }

    private function validate(Request $request): array
    {
        $rules = $request->validate([
            'label'          => 'required|string|max:120',
            'badge_text'     => 'nullable|string|max:40',
            'description'    => 'nullable|string|max:300',
            'discount_type'  => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'plan_id'        => 'nullable|exists:landlord.plans,id',
            'billing_period' => 'required|in:all,monthly,yearly',
            'starts_at'      => 'nullable|date',
            'ends_at'        => 'nullable|date|after_or_equal:starts_at',
            'is_active'      => 'boolean',
        ], [
            'label.required'          => 'Le libellé est obligatoire.',
            'discount_value.required' => 'La valeur de la remise est obligatoire.',
            'ends_at.after_or_equal'  => 'La date de fin doit être après la date de début.',
        ]);

        $rules['is_active'] = $request->boolean('is_active');
        return $rules;
    }
}
