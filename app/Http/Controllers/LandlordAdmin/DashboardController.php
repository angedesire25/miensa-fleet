<?php

namespace App\Http\Controllers\LandlordAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'tenants_total'  => Tenant::on('landlord')->count(),
            'tenants_active' => Tenant::on('landlord')->where('status', 'active')->count(),
            'tenants_trial'  => Tenant::on('landlord')->where('status', 'trial')->count(),
            'tenants_suspended' => Tenant::on('landlord')->where('status', 'suspended')->count(),
            'mrr' => Subscription::on('landlord')
                ->whereIn('status', ['active', 'trial'])
                ->where('billing_cycle', 'monthly')
                ->sum('amount'),
        ];

        $recentTenants = Tenant::on('landlord')
            ->with('plan')
            ->latest()
            ->limit(8)
            ->get();

        $plans = Plan::on('landlord')
            ->withCount(['tenants as tenants_count'])
            ->orderBy('sort_order')
            ->get();

        return view('landlord-admin.dashboard', compact('stats', 'recentTenants', 'plans'));
    }
}
