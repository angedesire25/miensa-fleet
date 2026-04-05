<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'repair_id',
        'incident_id',
        'part_category',
        'part_name',
        'part_reference',
        'part_brand',
        'quantity',
        'unit_cost',
        'total_cost',
        'replaced_at',
        'km_at_replacement',
        'replaced_by_garage',
        'warranty_months',
        'warranty_expiry',
        'failed_at',
        'failure_reported_in_repair_id',
        'days_until_failure',
        'failure_reason',
        'under_warranty_at_failure',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'replaced_at'               => 'date',
            'failed_at'                 => 'date',
            'warranty_expiry'           => 'date',
            'under_warranty_at_failure' => 'boolean',
            'unit_cost'                 => 'decimal:2',
            'total_cost'                => 'decimal:2',
            'quantity'                  => 'integer',
            'km_at_replacement'         => 'integer',
            'days_until_failure'        => 'integer',
            'warranty_months'           => 'integer',
        ];
    }

    protected $appends = ['lifespan_days', 'is_under_warranty'];

    // ── Relations ──────────────────────────────────────────────────────────

    /** Véhicule sur lequel la pièce a été montée */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Réparation lors de laquelle la pièce a été posée (null = pose isolée) */
    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    /** Sinistre associé à ce remplacement (null = entretien préventif) */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /** Garage ayant effectué la pose */
    public function replacedByGarage(): BelongsTo
    {
        return $this->belongsTo(Garage::class, 'replaced_by_garage');
    }

    /**
     * Réparation lors de laquelle cette pièce a été déclarée défaillante.
     * Permet de tracer la chaîne : pose → défaillance → nouvelle intervention.
     */
    public function failureRepair(): BelongsTo
    {
        return $this->belongsTo(Repair::class, 'failure_reported_in_repair_id');
    }

    /** Utilisateur qui a saisi cet enregistrement */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Pièces encore en service */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** Pièces déclarées défaillantes */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /** Filtre par catégorie de pièce */
    public function scopeCategory(Builder $query, string $cat): Builder
    {
        return $query->where('part_category', $cat);
    }

    /** Pièces actives encore couvertes par la garantie */
    public function scopeUnderWarranty(Builder $query): Builder
    {
        return $query->where('status', 'active')
                     ->whereNotNull('warranty_expiry')
                     ->where('warranty_expiry', '>=', now()->toDateString());
    }

    /** Historique des pièces d'un véhicule donné */
    public function scopeByVehicle(Builder $query, int $vehicleId): Builder
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    /**
     * Durée de vie de la pièce en jours entiers.
     *   - Pièce défaillante : diff(replaced_at, failed_at)
     *   - Pièce encore en service : diff(replaced_at, aujourd'hui)
     */
    protected function lifespanDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->replaced_at === null) {
                    return null;
                }

                $end = $this->failed_at ?? now()->startOfDay();

                return (int) $this->replaced_at->diffInDays($end);
            },
        );
    }

    /**
     * Vrai si la pièce est active et couverte par sa garantie aujourd'hui.
     */
    protected function isUnderWarranty(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->warranty_expiry !== null
                      && now()->startOfDay()->lte($this->warranty_expiry)
                      && $this->status === 'active',
        );
    }
}
