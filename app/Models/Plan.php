<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $connection = 'landlord';

    protected $fillable = [
        'name', 'slug', 'description',
        'price_monthly', 'price_yearly',
        'max_vehicles', 'max_users', 'max_drivers',
        'has_repairs', 'has_infractions', 'has_incidents',
        'has_inspections', 'has_reports', 'has_api',
        'trial_days', 'sort_order', 'is_active', 'is_featured',
    ];

    protected $casts = [
        'has_repairs'     => 'boolean',
        'has_infractions' => 'boolean',
        'has_incidents'   => 'boolean',
        'has_inspections' => 'boolean',
        'has_reports'     => 'boolean',
        'has_api'         => 'boolean',
        'is_active'       => 'boolean',
        'is_featured'     => 'boolean',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
