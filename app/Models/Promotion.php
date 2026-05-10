<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    protected $connection = 'landlord';

    protected $fillable = [
        'label', 'badge_text', 'description',
        'discount_type', 'discount_value',
        'plan_id', 'billing_period',
        'starts_at', 'ends_at',
        'is_active', 'archived_at',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'archived_at'    => 'datetime',
        'discount_value' => 'float',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /** Calcule le prix après remise. */
    public function applyTo(float $price): float
    {
        if ($this->discount_type === 'percent') {
            return round($price * (1 - $this->discount_value / 100));
        }
        return max(0, round($price - $this->discount_value));
    }

    /** Retourne le libellé formaté de la remise. */
    public function discountLabel(): string
    {
        if ($this->discount_type === 'percent') {
            return '-' . $this->discount_value . '%';
        }
        return '-' . number_format($this->discount_value, 0, ',', ' ') . ' FCFA';
    }

    /** Scope : promotions actuellement actives et dans la période de validité (non archivées). */
    public function scopeActive($query)
    {
        $now = now();
        return $query
            ->whereNull('archived_at')
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    /** Scope : promotions non archivées. */
    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    /** Scope : promotions archivées. */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function archive(): void
    {
        $this->update(['archived_at' => now(), 'is_active' => false]);
    }

    public function unarchive(): void
    {
        $this->update(['archived_at' => null]);
    }
}
