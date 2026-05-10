<?php

namespace App\Models;

use App\Observers\FuelRequestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Demande de carburant soumise par un collaborateur ou un chauffeur.
 *
 * Cycle de vie :
 *   pending → approved → fulfilled
 *           ↘ rejected
 *   (n'importe quel état non-fulfilled/rejected) → cancelled
 */
#[ObservedBy(FuelRequestObserver::class)]
class FuelRequest extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'reference',
        'vehicle_id',
        'driver_id',
        'requester_id',
        'fuel_station_id',
        'fuel_type',
        'liters_requested',
        'estimated_amount',
        'odometer_km',
        'reason',
        'is_urgent',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'requested_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'liters_requested' => 'decimal:2',
            'estimated_amount' => 'decimal:2',
            'odometer_km'      => 'integer',
            'is_urgent'        => 'boolean',
            'reviewed_at'      => 'datetime',
            'requested_at'     => 'datetime',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('fuel_requests')
            ->logOnly([
                'reference', 'vehicle_id', 'driver_id', 'requester_id',
                'fuel_type', 'liters_requested', 'status',
                'reviewed_by', 'reviewed_at', 'review_notes',
                'is_urgent',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Véhicule concerné */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Chauffeur lié (nullable si conducteur non-chauffeur) */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** Collaborateur / chauffeur-utilisateur qui a soumis la demande */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /** Station de carburant suggérée (nullable) */
    public function fuelStation(): BelongsTo
    {
        return $this->belongsTo(FuelStation::class);
    }

    /** Gestionnaire qui a traité la demande */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Transaction(s) issue(s) de cette demande (généralement une seule) */
    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('is_urgent', true)->where('status', 'pending');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isFulfilled(): bool { return $this->status === 'fulfilled'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true);
    }
}
