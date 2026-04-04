<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Infraction extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'user_id',
        'assignment_id',
        'request_id',
        'datetime_occurred',
        'location',
        'type',
        'description',
        'source',
        'pv_reference',
        'documents',
        'fine_amount',
        'payment_status',
        'payment_date',
        'payment_notes',
        'imputation',
        'internal_sanction',
        'sanction_decided_by',
        'status',
        'created_by',
        'auto_identified',
    ];

    protected function casts(): array
    {
        return [
            'datetime_occurred'  => 'datetime',
            'payment_date'       => 'date',
            'fine_amount'        => 'decimal:2',
            'documents'          => 'array',
            'auto_identified'    => 'boolean',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('infractions')
            ->logOnly([
                'vehicle_id', 'driver_id', 'user_id',
                'type', 'status', 'datetime_occurred', 'location',
                'fine_amount', 'payment_status', 'imputation',
                'internal_sanction', 'sanction_decided_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Véhicule impliqué dans l'infraction */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Chauffeur professionnel identifié (null si conducteur = collaborateur) */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** Collaborateur identifié comme conducteur (null si chauffeur) */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Affectation active au moment de l'infraction */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /** Demande de véhicule active au moment de l'infraction */
    public function vehicleRequest(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class, 'request_id');
    }

    /** Gestionnaire qui a décidé de la sanction interne */
    public function sanctionDecidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sanction_decided_by');
    }

    /** Utilisateur qui a saisi l'infraction */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Infractions avec amendes non encore réglées */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('payment_status', ['unpaid', 'contested'])
                     ->whereNotNull('fine_amount');
    }

    /** Infractions en cours de traitement */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    // ── Méthodes métier ────────────────────────────────────────────────────

    /**
     * Identifie automatiquement le conducteur responsable de l'infraction.
     *
     * Implémente la règle métier #6 :
     * "Le conducteur d'une infraction est identifié automatiquement via
     * l'assignment ou vehicle_request actif au datetime_occurred."
     *
     * Algorithme :
     *   1. Cherche une affectation chauffeur active au moment de l'infraction
     *      → remplit driver_id et assignment_id
     *   2. Sinon, cherche une demande de véhicule active au même moment
     *      → remplit user_id et request_id
     *   3. Dans les deux cas, marque auto_identified = true
     *
     * Note : Cette méthode ne persiste pas. Appeler save() après.
     *
     * @return bool  true si un conducteur a été identifié
     */
    public function identifyDriverAuto(): bool
    {
        if ($this->datetime_occurred === null || $this->vehicle_id === null) {
            return false;
        }

        $occurred = $this->datetime_occurred;

        // 1. Cherche une affectation chauffeur active au moment de l'infraction
        $assignment = Assignment::where('vehicle_id', $this->vehicle_id)
            ->whereIn('status', ['in_progress', 'completed'])
            ->where('datetime_start', '<=', $occurred)
            ->where(function (Builder $q) use ($occurred) {
                // Fin réelle si saisie, sinon fin planifiée
                $q->where(function (Builder $inner) use ($occurred) {
                    $inner->whereNotNull('datetime_end_actual')
                          ->where('datetime_end_actual', '>=', $occurred);
                })->orWhere(function (Builder $inner) use ($occurred) {
                    $inner->whereNull('datetime_end_actual')
                          ->where('datetime_end_planned', '>=', $occurred);
                });
            })
            ->first();

        if ($assignment) {
            $this->driver_id      = $assignment->driver_id;
            $this->assignment_id  = $assignment->id;
            $this->auto_identified = true;

            return true;
        }

        // 2. Cherche une demande de véhicule active au moment de l'infraction
        $request = VehicleRequest::where('vehicle_id', $this->vehicle_id)
            ->whereIn('status', ['in_progress', 'completed'])
            ->where('datetime_start', '<=', $occurred)
            ->where(function (Builder $q) use ($occurred) {
                $q->where(function (Builder $inner) use ($occurred) {
                    $inner->whereNotNull('datetime_end_actual')
                          ->where('datetime_end_actual', '>=', $occurred);
                })->orWhere(function (Builder $inner) use ($occurred) {
                    $inner->whereNull('datetime_end_actual')
                          ->where('datetime_end_planned', '>=', $occurred);
                });
            })
            ->first();

        if ($request) {
            $this->user_id        = $request->requester_id;
            $this->request_id     = $request->id;
            $this->auto_identified = true;

            return true;
        }

        return false;
    }
}
