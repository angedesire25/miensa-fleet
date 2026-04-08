<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inspection extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'inspector_id',
        'driver_id',
        'assignment_id',
        'request_id',
        'inspected_at',
        'location',
        'inspection_type',
        'status',
        // 14 points de contrôle
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
        'oil_change_km',
        'oil_change_next_date',
        'oil_change_next_km',
        'body_notes',
        'body_photos',
        'lights_status',
        'lights_notes',
        'brakes_status',
        'brakes_notes',
        'general_observations',
        'signature_path',
        'has_critical_issue',
        // Archivage
        'archived_at',
        // Validation
        'validated_by',
        'validated_at',
        'rejection_reason',
    ];

    protected $appends = ['is_critical'];

    protected function casts(): array
    {
        return [
            'inspected_at'               => 'datetime',
            'insurance_expiry'           => 'date',
            'technical_control_expiry'   => 'date',
            'oil_change_date'            => 'date',
            'oil_change_next_date'       => 'date',
            'oil_change_km'              => 'integer',
            'oil_change_next_km'         => 'integer',
            'validated_at'               => 'datetime',
            'archived_at'                => 'datetime',
            'km'                         => 'integer',
            'fuel_level_pct'             => 'integer',
            'registration_present'       => 'boolean',
            'has_critical_issue'         => 'boolean',
            'body_photos'                => 'array',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('inspections')
            ->logOnly(['vehicle_id', 'inspector_id', 'status', 'inspection_type', 'inspected_at', 'has_critical_issue'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Auto-synchronisation de has_critical_issue ─────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Inspection $inspection): void {
            $inspection->has_critical_issue =
                $inspection->oil_level        === 'low'
                || $inspection->brakes_status === 'critical'
                || $inspection->lights_status === 'critical';
        });
    }

    // ── Relations ──────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Utilisateur (contrôleur/agent) qui a rempli la fiche */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /** Chauffeur concerné par l'inspection */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function vehicleRequest(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class, 'request_id');
    }

    /** Gestionnaire qui a validé la fiche */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('has_critical_issue', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('inspection_type', $type);
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('inspected_at', today());
    }

    /** Fiches non archivées (filtre par défaut) */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /** Fiches archivées uniquement */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    protected function isCritical(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->oil_level        === 'low'
                      || $this->brakes_status    === 'critical'
                      || $this->lights_status    === 'critical',
        );
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function hasValidDocuments(): bool
    {
        return $this->insurance_status         === 'present'
            && $this->technical_control_status === 'present'
            && $this->registration_present     === true;
    }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isValidated(): bool { return $this->status === 'validated'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }

    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }

    /** Une fiche peut être modifiée si elle est en brouillon ou renvoyée pour correction, et non archivée */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'rejected']) && !$this->isArchived();
    }

    /** Score de complétion (0–100) basé sur les 14 points renseignés */
    public function completionScore(): int
    {
        $fields = [
            'km', 'oil_level', 'coolant_level', 'brake_fluid_level',
            'tire_pressure', 'fuel_level_pct',
            'insurance_status', 'technical_control_status', 'registration_present',
            'oil_change_status', 'lights_status', 'brakes_status',
        ];
        $filled = collect($fields)->filter(fn($f) => !is_null($this->$f))->count();
        return (int) round($filled / count($fields) * 100);
    }
}
