<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Promotion;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Promotions actuellement en cours (actives + dans la période)
        $promotions = Promotion::active()->with('plan')->get();

        return view('landlord.pricing', compact('plans', 'promotions'));
    }

    public function contact(): View
    {
        return view('landlord.contact');
    }
}
