<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripLog extends Model
{
    use HasFactory;

    /**
     * km_total est une colonne virtuelle MySQL :
     *   IF(km_end IS NOT NULL, km_end - km_start, NULL)
     * Exclue de $fillable.
     */
    protected $fillable = [
        'assignment_id',
        'request_id',
        'driver_id',
        'user_id',
        'vehicle_id',
        'datetime_start',
        'datetime_end',
        'location_start',
        'location_end',
        'lat_start',
        'lng_start',
        'lat_end',
        'lng_end',
        'km_start',
        'km_end',
        'purpose',
        'passengers',
        'fuel_added_liters',
        'fuel_cost',
        'has_incident',
        'incident_description',
        'incident_photos',
        'observations',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'datetime_start'     => 'datetime',
            'datetime_end'       => 'datetime',
            'lat_start'          => 'decimal:7',
            'lng_start'          => 'decimal:7',
            'lat_end'            => 'decimal:7',
            'lng_end'            => 'decimal:7',
            'km_start'           => 'integer',
            'km_end'             => 'integer',
            'km_total'           => 'integer',
            'fuel_added_liters'  => 'decimal:2',
            'fuel_cost'          => 'decimal:2',
            'has_incident'       => 'boolean',
            'incident_photos'    => 'array',
        ];
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /**
     * Affectation chauffeur dans le cadre de laquelle ce trajet a été effectué.
     * Null si le trajet est lié à une demande de véhicule.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Demande de véhicule dans le cadre de laquelle ce trajet a été effectué.
     * Null si le trajet est lié à une affectation chauffeur.
     */
    public function vehicleRequest(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class, 'request_id');
    }

    /** Chauffeur professionnel au volant (si affectation) */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** Collaborateur au volant (si demande de véhicule) */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Véhicule utilisé */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Utilisateur qui a enregistré le trajet */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Trajets ayant signalé un incident */
    public function scopeWithIncident(Builder $query): Builder
    {
        return $query->where('has_incident', true);
    }

    /** Trajets du véhicule donné, triés chronologiquement */
    public function scopeForVehicle(Builder $query, int $vehicleId): Builder
    {
        return $query->where('vehicle_id', $vehicleId)->orderBy('datetime_start');
    }
}
