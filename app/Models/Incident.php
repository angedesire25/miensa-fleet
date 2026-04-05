<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Incident extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'user_id',
        'assignment_id',
        'request_id',
        'type',
        'severity',
        'datetime_occurred',
        'location',
        'description',
        'third_party_involved',
        'third_party_name',
        'third_party_plate',
        'third_party_insurance',
        'police_report_number',
        'police_report_path',
        'insurance_declared',
        'insurance_claim_number',
        'insurance_declaration_date',
        'insurance_amount_claimed',
        'insurance_amount_received',
        'insurance_status',
        'status',
        'vehicle_immobilized',
        'estimated_repair_cost',
        'actual_repair_cost',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'datetime_occurred'          => 'datetime',
            'insurance_declaration_date' => 'date',
            'third_party_involved'       => 'boolean',
            'insurance_declared'         => 'boolean',
            'vehicle_immobilized'        => 'boolean',
            'estimated_repair_cost'      => 'decimal:2',
            'actual_repair_cost'         => 'decimal:2',
            'insurance_amount_claimed'   => 'decimal:2',
            'insurance_amount_received'  => 'decimal:2',
        ];
    }

    protected $appends = ['is_active', 'duration_days'];

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('incidents')
            ->logOnly([
                'vehicle_id', 'driver_id', 'user_id',
                'type', 'severity', 'status',
                'datetime_occurred', 'location',
                'insurance_declared', 'insurance_status',
                'vehicle_immobilized',
                'estimated_repair_cost', 'actual_repair_cost',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Véhicule impliqué dans le sinistre */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Chauffeur professionnel au volant lors du sinistre (nullable) */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** Collaborateur non-chauffeur au volant lors du sinistre (nullable) */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Affectation en cours lors du sinistre (nullable) */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /** Demande de véhicule en cours lors du sinistre (nullable) */
    public function request(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class, 'request_id');
    }

    /** Toutes les réparations issues de ce sinistre */
    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    /**
     * Toutes les pièces remplacées au cours des réparations de ce sinistre.
     * Traversée : Incident → Repair → PartReplacement
     */
    public function parts(): HasManyThrough
    {
        return $this->hasManyThrough(PartReplacement::class, Repair::class);
    }

    /**
     * Photos attachées à ce sinistre via la relation polymorphique.
     * Contextes attendus : incident_before, incident_damage.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class, 'photoable_id')
                    ->where('photoable_type', self::class);
    }

    /** Utilisateur qui a saisi le sinistre */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Réparation la plus récente liée à ce sinistre */
    public function latestRepair(): HasOne
    {
        return $this->hasOne(Repair::class)->latest();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Sinistres en cours de traitement */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /** Sinistres dont le véhicule est actuellement au garage */
    public function scopeAtGarage(Builder $query): Builder
    {
        return $query->where('status', 'at_garage');
    }

    /** Sinistres d'un véhicule donné */
    public function scopeByVehicle(Builder $query, int $vehicleId): Builder
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    /** Sinistres graves nécessitant une attention prioritaire */
    public function scopeSevere(Builder $query): Builder
    {
        return $query->whereIn('severity', ['major', 'total_loss']);
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    /**
     * Vrai si le sinistre est encore en cours de traitement.
     * Faux dès que le véhicule est réparé, la fiche clôturée ou déclaré épave.
     */
    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn() => ! in_array($this->status, ['repaired', 'closed', 'total_loss'], true),
        );
    }

    /**
     * Durée du sinistre en jours entiers.
     *
     * Si le sinistre est terminé (repaired / closed / total_loss) :
     *   diff entre datetime_occurred et updated_at (proxy de la date de clôture).
     * Si le sinistre est encore actif :
     *   diff entre datetime_occurred et maintenant.
     */
    protected function durationDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->datetime_occurred === null) {
                    return null;
                }

                $end = in_array($this->status, ['repaired', 'closed', 'total_loss'], true)
                    ? $this->updated_at
                    : now();

                return (int) $this->datetime_occurred->diffInDays($end);
            },
        );
    }
}
