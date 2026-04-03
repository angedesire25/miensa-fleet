<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VehicleDocument extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'type',
        'document_number',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'file_path',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'  => 'date',
            'expiry_date' => 'date',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('vehicle_documents')
            ->logOnly([
                'vehicle_id', 'type', 'document_number',
                'issue_date', 'expiry_date', 'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Documents encore valides */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status', 'valid');
    }

    /** Documents expirés */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')
                     ->orWhere('expiry_date', '<', now());
    }

    /**
     * Documents qui expirent dans $days jours (pour alertes préventives).
     * Par défaut : 30 jours.
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>=', now())
                     ->whereIn('status', ['valid', 'expiring_soon']);
    }

    /** Filtrer par type de document */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->expiry_date !== null && $this->expiry_date->isPast());
    }

    public function daysUntilExpiry(): ?int
    {
        if ($this->expiry_date === null) {
            return null;
        }

        return (int) now()->diffInDays($this->expiry_date, false);
    }
}
