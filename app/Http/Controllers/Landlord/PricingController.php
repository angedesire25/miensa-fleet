<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('landlord.pricing', compact('plans'));
    }

    public function contact(): View
    {
        return view('landlord.contact');
    }
}
