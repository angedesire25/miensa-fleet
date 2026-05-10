<?php

namespace App\Models;

use App\Observers\FuelTransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Ravitaillement effectivement réalisé.
 *
 * Peut être issu d'une demande approuvée (fuel_request_id renseigné)
 * ou saisi directement par un gestionnaire (fuel_request_id NULL).
 *
 * Lors de l'enregistrement, l'observer met à jour les statistiques
 * carburant du véhicule (total_liters_consumed, total_fuel_cost,
 * consumption_real, km_last_fill, date_last_fill).
 */
#[ObservedBy(FuelTransactionObserver::class)]
class FuelTransaction extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'reference',
        'fuel_request_id',
        'vehicle_id',
        'driver_id',
        'fuel_station_id',
        'station_name_free',
        'fuel_type',
        'liters',
        'unit_price',
        'total_amount',
        'odometer_km',
        'km_since_last_fill',
        'consumption_per_100km',
        'fuel_card_used',
        'fuel_card_number',
        'receipt_number',
        'receipt_photo',
        'recorded_by',
        'fueled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'liters'                 => 'decimal:2',
            'unit_price'             => 'decimal:2',
            'total_amount'           => 'decimal:2',
            'odometer_km'            => 'integer',
            'km_since_last_fill'     => 'integer',
            'consumption_per_100km'  => 'decimal:2',
            'fuel_card_used'         => 'boolean',
            'fueled_at'              => 'date',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('fuel_transactions')
            ->logOnly([
                'reference', 'vehicle_id', 'driver_id',
                'fuel_type', 'liters', 'unit_price', 'total_amount',
                'odometer_km', 'fuel_card_used', 'fueled_at',
                'recorded_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Demande à l'origine de ce ravitaillement (nullable) */
    public function fuelRequest(): BelongsTo
    {
        return $this->belongsTo(FuelRequest::class);
    }

    /** Véhicule ravitaillé */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Chauffeur présent au plein (nullable) */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** Station référencée (nullable si station libre) */
    public function fuelStation(): BelongsTo
    {
        return $this->belongsTo(FuelStation::class);
    }

    /** Utilisateur qui a saisi la transaction */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Transactions avec carte carburant */
    public function scopeWithCard(Builder $query): Builder
    {
        return $query->where('fuel_card_used', true);
    }

    /** Transactions sur une période donnée */
    public function scopePeriod(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('fueled_at', [$from, $to]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Nom affiché de la station (référencée ou libre) */
    public function getStationLabelAttribute(): string
    {
        return $this->fuelStation?->name ?? $this->station_name_free ?? 'Station inconnue';
    }
}
