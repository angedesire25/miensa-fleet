<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $connection = 'landlord';

    protected $fillable = [
        'tenant_id', 'subscription_id', 'invoice_number',
        'amount_ht', 'tax_rate', 'amount_ttc', 'currency',
        'status', 'issued_at', 'due_at', 'paid_at',
        'payment_reference', 'payment_provider', 'notes', 'pdf_path',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'due_at'    => 'date',
        'paid_at'   => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
