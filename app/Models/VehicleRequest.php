<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VehicleRequest extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * km_total est une colonne virtuelle MySQL, exclue de $fillable.
     */
    protected $fillable = [
        'requester_id',
        'vehicle_id',
        'vehicle_type_preferred',
        'datetime_start',
        'datetime_end_planned',
        'datetime_end_actual',
        'destination',
        'purpose',
        'passengers',
        'is_urgent',
        'requester_notes',
        'attachment_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'km_limit',
        'geographic_limit',
        'km_start',
        'km_end',
        'condition_start',
        'condition_start_notes',
        'condition_end',
        'condition_end_notes',
        'bon_sortie_path',
    ];

    protected function casts(): array
    {
        return [
            'datetime_start'       => 'datetime',
            'datetime_end_planned' => 'datetime',
            'datetime_end_actual'  => 'datetime',
            'reviewed_at'          => 'datetime',
            'is_urgent'            => 'boolean',
            'passengers'           => 'integer',
            'km_limit'             => 'integer',
            'km_start'             => 'integer',
            'km_end'               => 'integer',
            'km_total'             => 'integer',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('vehicle_requests')
            ->logOnly([
                'requester_id', 'vehicle_id', 'status',
                'datetime_start', 'datetime_end_planned',
                'destination', 'purpose', 'is_urgent',
                'reviewed_by', 'reviewed_at', 'review_notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Collaborateur qui a soumis la demande */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /** Véhicule attribué par le gestionnaire */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Gestionnaire qui a traité la demande (approbation ou refus) */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Trajets enregistrés dans le carnet de bord liés à cette demande */
    public function tripLogs(): HasMany
    {
        return $this->hasMany(TripLog::class, 'request_id');
    }

    /** Infractions constatées pendant l'utilisation du véhicule */
    public function infractions(): HasMany
    {
        return $this->hasMany(Infraction::class, 'request_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Demandes en attente de traitement par le gestionnaire */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /** Demandes actives (approuvées, en cours ou confirmées — pas terminées ni rejetées) */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['approved', 'confirmed', 'in_progress']);
    }

    /** Demandes urgentes non encore traitées */
    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('is_urgent', true)->where('status', 'pending');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'confirmed', 'in_progress'], true);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
