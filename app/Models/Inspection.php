<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'inspector_id',
        'assignment_id',
        'request_id',
        'inspected_at',
        'location',
        'inspection_type',
        'km',
        'oil_level',
        'oil_notes',
        'coolant_level',
        'brake_fluid_level',
        'tire_pressure',
        'tire_notes',
        'fuel_level_pct',
        'insurance_status',
        'insurance_expiry',
        'technical_control_status',
        'technical_control_expiry',
        'registration_present',
        'oil_change_status',
        'oil_change_date',
        'body_notes',
        'body_photos',
        'lights_status',
        'lights_notes',
        'brakes_status',
        'brakes_notes',
        'general_observations',
        'signature_path',
        'has_critical_issue',
    ];

    protected $appends = ['is_critical'];

    protected function casts(): array
    {
        return [
            'inspected_at'               => 'datetime',
            'insurance_expiry'           => 'date',
            'technical_control_expiry'   => 'date',
            'oil_change_date'            => 'date',
            'km'                         => 'integer',
            'fuel_level_pct'             => 'integer',
            'registration_present'       => 'boolean',
            'has_critical_issue'         => 'boolean',
            'body_photos'                => 'array',
        ];
    }

    // ── Auto-synchronisation de has_critical_issue ─────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Avant chaque enregistrement, recalcule has_critical_issue depuis les points
        // de contrôle individuels pour garantir la cohérence avec la colonne DB.
        static::saving(function (Inspection $inspection): void {
            $inspection->has_critical_issue =
                $inspection->oil_level         === 'low'
                || $inspection->brakes_status  === 'critical'
                || $inspection->lights_status  === 'critical';
        });
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Véhicule contrôlé */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Agent ou gestionnaire qui a effectué le contrôle */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /** Affectation dans le cadre de laquelle la fiche a été remplie (optionnel) */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /** Demande de véhicule liée à ce contrôle (optionnel) */
    public function vehicleRequest(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class, 'request_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Fiches révélant une anomalie critique */
    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('has_critical_issue', true);
    }

    /** Fiches d'un type donné (departure, return, routine) */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('inspection_type', $type);
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    /**
     * Calcul dynamique de l'anomalie critique depuis les 3 points de contrôle clés :
     *   - Niveau d'huile moteur à "low"
     *   - État des freins "critical"
     *   - État des feux "critical"
     *
     * La valeur de `has_critical_issue` en base est synchronisée automatiquement
     * dans `boot()->saving()` ci-dessus, ce qui permet de filtrer en SQL.
     * Cet accesseur est utile pour un calcul à la volée sans requête DB.
     */
    protected function isCritical(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->oil_level        === 'low'
                      || $this->brakes_status    === 'critical'
                      || $this->lights_status    === 'critical',
        );
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Vérifie si les documents légaux (assurance + CT) sont en ordre */
    public function hasValidDocuments(): bool
    {
        return $this->insurance_status         === 'present'
            && $this->technical_control_status === 'present'
            && $this->registration_present     === true;
    }
}
