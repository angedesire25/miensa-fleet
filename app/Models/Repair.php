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
            'datetime_sent'       => 'datetime',
            'datetime_returned'   => 'datetime',
            'parts_to_replace'    => 'array',
            'parts_replaced'      => 'array',
            'same_issue_recurrence' => 'boolean',
            'quote_amount'        => 'decimal:2',
            'invoice_amount'      => 'decimal:2',
            'warranty_expiry'     => 'date',
            'payment_date'        => 'date',
            'km_at_departure'     => 'integer',
            'km_at_return'        => 'integer',
            'recurrence_delay_days' => 'integer',
            'warranty_months'     => 'integer',
        ];
    }

    protected $appends = ['duration_days', 'is_overdue'];

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

    /** Pièces remplacées lors de cette réparation (version structurée) */
    public function partsReplaced(): HasMany
    {
        return $this->hasMany(PartReplacement::class);
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
     * Vrai si la réparation est en cours depuis plus de N jours (défaut : 7).
     * Seuil configurable via config('fleet.repair_overdue_days').
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! in_array($this->status, ['sent', 'diagnosing', 'repairing', 'waiting_parts'], true)) {
                    return false;
                }

                $maxDays = (int) config('fleet.repair_overdue_days', 7);

                return $this->datetime_sent !== null
                    && $this->datetime_sent->lt(now()->subDays($maxDays));
            },
        );
    }
}
