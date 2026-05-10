<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Repair extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'incident_id',
        'vehicle_id',
        'garage_id',
        'repair_type',
        // ── Champs DI ──────────────────────────────────────────
        'di_number',
        'or_initial_reference',
        'vehicle_type_body',
        'availability_date_requested',
        'actual_exit_date',
        // immobilization_days : colonne virtuelle MySQL, non fillable
        'signature_company_path',
        'signature_garage_path',
        'signature_company_exit_path',
        'signature_garage_exit_path',
        // ── Champs existants ────────────────────────────────────
        'datetime_sent',
        'km_at_departure',
        'condition_at_departure',
        'sent_by',
        'diagnosis',
        'parts_to_replace',
        'datetime_returned',
        'km_at_return',
        'condition_at_return',
        'received_by',
        'work_performed',
        'parts_replaced',
        'same_issue_recurrence',
        'previous_repair_id',
        'recurrence_delay_days',
        'status',
        'quote_amount',
        'invoice_amount',
        'invoice_number',
        'invoice_path',
        'payment_status',
        'payment_date',
        'warranty_months',
        'warranty_expiry',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'datetime_sent'              => 'datetime',
            'datetime_returned'          => 'datetime',
            'parts_to_replace'           => 'array',
            'parts_replaced'             => 'array',
            'same_issue_recurrence'      => 'boolean',
            'quote_amount'               => 'decimal:2',
            'invoice_amount'             => 'decimal:2',
            'warranty_expiry'            => 'date',
            'payment_date'               => 'date',
            'km_at_departure'            => 'integer',
            'km_at_return'               => 'integer',
            'recurrence_delay_days'      => 'integer',
            'warranty_months'            => 'integer',
            // ── Champs DI ─────────────────────────────────────────
            'availability_date_requested' => 'date',
            'actual_exit_date'            => 'date',
            // immobilization_days → accesseur PHP (voir ci-dessous)
        ];
    }

    protected $appends = ['duration_days', 'is_overdue', 'immobilization_days', 'di_number_formatted'];

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('repairs')
            ->logOnly([
                'vehicle_id', 'incident_id', 'garage_id',
                'repair_type', 'status',
                'datetime_sent', 'datetime_returned',
                'same_issue_recurrence', 'previous_repair_id',
                'quote_amount', 'invoice_amount', 'payment_status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Sinistre à l'origine de cette réparation (null = entretien préventif) */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /** Véhicule réparé */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Garage prestataire (null si non encore sélectionné) */
    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class);
    }

    /** Responsable de l'envoi du véhicule au garage */
    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /** Responsable de la réception du véhicule à son retour */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Réparation précédente pour la même panne.
     * Renseignée quand same_issue_recurrence = true.
     */
    public function previousRepair(): BelongsTo
    {
        return $this->belongsTo(Repair::class, 'previous_repair_id');
    }

    /**
     * Réparations ultérieures qui pointent vers celle-ci comme récurrence.
     * Permet de constituer la chaîne complète des retours pour la même panne.
     */
    public function recurrences(): HasMany
    {
        return $this->hasMany(Repair::class, 'previous_repair_id');
    }

    /** Codes de panne résolus */
    public function resolvedFaults(): HasMany
    {
        return $this->hasMany(RepairFaultCode::class)->where('resolution_status', 'resolved');
    }

    /** Codes de panne en attente */
    public function pendingFaults(): HasMany
    {
        return $this->hasMany(RepairFaultCode::class)->where('resolution_status', 'pending');
    }

    /** Pièces remplacées lors de cette réparation (version structurée) */
    public function partsReplaced(): HasMany
    {
        return $this->hasMany(PartReplacement::class);
    }

    /**
     * Anomalies / codes de panne déclarés sur la DI.
     * Remplace le champ texte libre parts_to_replace pour les DI Geomatos.
     * Triés par sort_order puis par code.
     */
    public function faultCodes(): HasMany
    {
        return $this->hasMany(RepairFaultCode::class)->orderBy('sort_order')->orderBy('code');
    }

    /** Utilisateur qui a créé le bon de réparation */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Photos liées à cette réparation via la relation polymorphique.
     * Contextes attendus : repair_in_progress, repair_after.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class, 'photoable_id')
                    ->where('photoable_type', self::class);
    }

    // ── Boot ───────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $repair) {
            if (empty($repair->di_number)) {
                $repair->di_number = static::generateDiNumber($repair->vehicle_id);
            }
        });
    }

    // ── Génération du numéro DI ───────────────────────────────────────────

    /**
     * Génère le prochain numéro DI pour un véhicule sur le mois courant.
     *
     * Format : DI_{IMMAT_SANS_TIRETS}{MM}{YYYY}_{séquence 3 chiffres}
     * Exemples : DI_1548JB012026_000, DI_1548JB012026_001
     *
     * La séquence est propre à chaque couple (vehicle × mois) et commence à 000.
     */
    public static function generateDiNumber(int $vehicleId): string
    {
        $plate      = \App\Models\Vehicle::where('id', $vehicleId)->value('plate') ?? 'VH';
        $plateClean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $plate));

        $mm   = now()->format('m');
        $yyyy = now()->format('Y');

        $prefix = "DI_{$plateClean}{$mm}{$yyyy}";

        $count = static::withTrashed()
            ->where('vehicle_id', $vehicleId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return $prefix . '_' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Réparations en cours (véhicule toujours au garage) */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->whereIn('status', ['sent', 'diagnosing', 'repairing', 'waiting_parts']);
    }

    /** Réparations terminées (véhicule restitué avec ou sans réserve) */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', ['completed', 'returned']);
    }

    /** Réparations signalées comme récurrence d'une même panne */
    public function scopeRecurrences(Builder $query): Builder
    {
        return $query->where('same_issue_recurrence', true);
    }

    /** Réparations effectuées par un garage donné */
    public function scopeByGarage(Builder $query, int $garageId): Builder
    {
        return $query->where('garage_id', $garageId);
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    /**
     * Durée de la réparation en jours entiers.
     *   - Véhicule retourné : diff(datetime_sent, datetime_returned)
     *   - Véhicule encore au garage : diff(datetime_sent, now())
     */
    protected function durationDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->datetime_sent === null) {
                    return null;
                }

                $end = $this->datetime_returned ?? now();

                return (int) $this->datetime_sent->diffInDays($end);
            },
        );
    }

    /**
     * Vrai si la date de disponibilité demandée est dépassée et que le véhicule
     * n'est pas encore sorti (actual_exit_date IS NULL).
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->availability_date_requested !== null
                    && $this->availability_date_requested->lt(now()->startOfDay())
                    && $this->actual_exit_date === null;
            },
        );
    }

    /** Numéro DI formaté pour l'affichage (ex : DI_042026_001) */
    protected function diNumberFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->di_number ?? '—',
        );
    }

    /**
     * Durée d'immobilisation en jours entiers (équivalent de la formule
     * virtualAs demandée, recalculée en PHP car MySQL interdit CURDATE()
     * dans les colonnes générées).
     *
     *   - véhicule sorti  : actual_exit_date  − DATE(datetime_sent)
     *   - encore au garage : today()           − DATE(datetime_sent)
     *   - pas encore envoyé : null
     */
    protected function immobilizationDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->datetime_sent === null) {
                    return null;
                }

                $start = $this->datetime_sent->startOfDay();
                $end   = $this->actual_exit_date
                    ? $this->actual_exit_date->startOfDay()
                    : now()->startOfDay();

                return (int) $start->diffInDays($end);
            },
        );
    }
}
