<?php

namespace App\Http\Controllers\LandlordAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('landlord-admin.plans.index', compact('plans'));
    }

    public function edit(Plan $plan)
    {
        return view('landlord-admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:80',
            'description'      => 'nullable|string|max:500',
            'price_monthly'    => 'required|numeric|min:0',
            'price_yearly'     => 'required|numeric|min:0',
            'max_vehicles'     => 'required|integer|min:1|max:9999',
            'max_users'        => 'required|integer|min:1|max:9999',
            'max_drivers'      => 'required|integer|min:1|max:9999',
            'trial_days'       => 'required|integer|min:0|max:365',
            'sort_order'       => 'required|integer|min:0|max:100',
        ], [
            'name.required'          => 'Le nom du plan est obligatoire.',
            'price_monthly.required' => 'Le prix mensuel est obligatoire.',
            'price_yearly.required'  => 'Le prix annuel est obligatoire.',
        ]);

        $validated['has_repairs']     = $request->boolean('has_repairs');
        $validated['has_infractions'] = $request->boolean('has_infractions');
        $validated['has_incidents']   = $request->boolean('has_incidents');
        $validated['has_inspections'] = $request->boolean('has_inspections');
        $validated['has_reports']     = $request->boolean('has_reports');
        $validated['has_api']         = $request->boolean('has_api');
        $validated['is_active']       = $request->boolean('is_active');
        $validated['is_featured']     = $request->boolean('is_featured');

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$plan->name}\" mis à jour avec succès.");
    }

    /** Active ou désactive rapidement un plan (appel Ajax/direct). */
    public function toggleActive(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        return back()->with('success', "Visibilité du plan mise à jour.");
    }
}
