<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $connection = 'landlord';

    protected $fillable = [
        'tenant_id', 'plan_id', 'billing_cycle', 'amount', 'currency',
        'status', 'starts_at', 'ends_at', 'cancelled_at',
        'payment_reference', 'payment_provider', 'notes',
    ];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
