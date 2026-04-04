<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Assignment extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * km_total est une colonne virtuelle calculée par MySQL :
     *   IF(km_end IS NOT NULL AND km_start IS NOT NULL, km_end - km_start, NULL)
     * Elle ne doit jamais figurer dans $fillable.
     */
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'type',
        'datetime_start',
        'datetime_end_planned',
        'datetime_end_actual',
        'mission',
        'destination',
        'km_start',
        'km_end',
        'condition_start',
        'condition_start_notes',
        'photos_start',
        'condition_end',
        'condition_end_notes',
        'photos_end',
        'status',
        'cancellation_reason',
        'inspection_start_id',
        'inspection_end_id',
        'validated_by',
        'validated_at',
        'bon_sortie_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'datetime_start'       => 'datetime',
            'datetime_end_planned' => 'datetime',
            'datetime_end_actual'  => 'datetime',
            'validated_at'         => 'datetime',
            'km_start'             => 'integer',
            'km_end'               => 'integer',
            'km_total'             => 'integer',
            'photos_start'         => 'array',
            'photos_end'           => 'array',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('assignments')
            ->logOnly([
                'driver_id', 'vehicle_id', 'type', 'status',
                'datetime_start', 'datetime_end_planned', 'datetime_end_actual',
                'mission', 'destination', 'km_start', 'km_end',
                'validated_by', 'validated_at', 'cancellation_reason',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Utilisateur gestionnaire qui a validé l'affectation */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Fiche de contrôle au départ */
    public function inspectionStart(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_start_id');
    }

    /** Fiche de contrôle au retour */
    public function inspectionEnd(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_end_id');
    }

    /** Trajets enregistrés dans le carnet de bord pour cette affectation */
    public function tripLogs(): HasMany
    {
        return $this->hasMany(TripLog::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Affectations actives (pas terminées ni annulées) */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['planned', 'confirmed', 'in_progress']);
    }

    /** Affectations en cours d'exécution (chauffeur parti, km de départ saisi) */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Détecte les conflits d'affectation pour une plage horaire donnée.
     *
     * Implémente les règles métier :
     *   #2 — Un véhicule ne peut pas être dans 2 affectations simultanées
     *   #3 — Un chauffeur ne peut pas avoir 2 affectations qui se chevauchent
     *
     * Exemple d'utilisation (vérification d'un conflit avant création) :
     *   Assignment::conflicting($vehicleId, $driverId, $start, $end)->exists()
     *
     * @param int|null $vehicleId  Identifiant du véhicule à tester (null = ignorer)
     * @param int|null $driverId   Identifiant du chauffeur à tester (null = ignorer)
     * @param Carbon   $start      Début de la plage à tester
     * @param Carbon   $end        Fin de la plage à tester (datetime_end_planned)
     * @param int|null $excludeId  ID de l'affectation à exclure (utile lors d'une mise à jour)
     */
    public function scopeConflicting(
        Builder $query,
        ?int    $vehicleId,
        ?int    $driverId,
        Carbon  $start,
        Carbon  $end,
        ?int    $excludeId = null
    ): Builder {
        $query
            ->whereIn('status', ['planned', 'confirmed', 'in_progress'])
            ->where('datetime_start', '<', $end)
            ->where('datetime_end_planned', '>', $start)
            ->when($excludeId, fn(Builder $q) => $q->where('id', '!=', $excludeId));

        if ($vehicleId && $driverId) {
            // Conflit si le véhicule OU le chauffeur est déjà occupé
            $query->where(function (Builder $q) use ($vehicleId, $driverId) {
                $q->where('vehicle_id', $vehicleId)
                  ->orWhere('driver_id', $driverId);
            });
        } elseif ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        } elseif ($driverId) {
            $query->where('driver_id', $driverId);
        }

        return $query;
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['planned', 'confirmed', 'in_progress'], true);
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /** Règle métier #5 — km_start obligatoire avant passage en in_progress */
    public function canStart(): bool
    {
        return $this->km_start !== null
            && in_array($this->status, ['planned', 'confirmed'], true);
    }
}
