<?php

namespace App\Models;

use App\Observers\DriverObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(DriverObserver::class)]
class Driver extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'matricule',
        'full_name',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'avatar',
        'hire_date',
        'contract_type',
        'contract_end_date',
        'license_number',
        'license_categories',
        'license_expiry_date',
        'license_issuing_authority',
        'habilitations',
        'status',
        'suspension_reason',
        'preferred_vehicle_id',
        'total_km',
        'total_assignments',
        'total_infractions',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'        => 'date',
            'hire_date'            => 'date',
            'contract_end_date'    => 'date',
            'license_expiry_date'  => 'date',
            'license_categories'   => 'array',
            'habilitations'        => 'array',
            'total_km'             => 'integer',
            'total_assignments'    => 'integer',
            'total_infractions'    => 'integer',
        ];
    }

    protected $appends = ['is_blocked', 'is_license_expired'];

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('drivers')
            ->logOnly([
                'matricule', 'full_name', 'phone', 'email',
                'status', 'suspension_reason',
                'license_number', 'license_categories', 'license_expiry_date',
                'contract_type', 'contract_end_date',
                'preferred_vehicle_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Véhicule préférentiel (non exclusif) */
    public function preferredVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'preferred_vehicle_id');
    }

    /** Utilisateur qui a créé ce profil */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Compte utilisateur lié à ce chauffeur */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'driver_id');
    }

    /** Toutes les affectations de ce chauffeur */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /** Affectation en cours (une seule à la fois, si in_progress) */
    public function activeAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)
                    ->where('status', 'in_progress')
                    ->latest('datetime_start');
    }

    /** Documents administratifs du chauffeur */
    public function documents(): HasMany
    {
        return $this->hasMany(DriverDocument::class);
    }

    /** Carnet de bord (trajets effectués) */
    public function tripLogs(): HasMany
    {
        return $this->hasMany(TripLog::class);
    }

    /** Infractions imputées à ce chauffeur */
    public function infractions(): HasMany
    {
        return $this->hasMany(Infraction::class);
    }

    /** Alertes ciblant ce chauffeur */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Chauffeurs actifs (peuvent être affectés) */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Chauffeurs disponibles : actifs + permis valide + pas d'affectation
     * en cours sur le créneau donné.
     * Implémente la règle métier #4.
     */
    public function scopeAvailable(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query
            ->where('status', 'active')
            ->where('license_expiry_date', '>', now())
            ->whereDoesntHave('assignments', function (Builder $q) use ($start, $end) {
                $q->whereIn('status', ['planned', 'confirmed', 'in_progress'])
                  ->where('datetime_start', '<', $end)
                  ->where('datetime_end_planned', '>', $start);
            });
    }

    /**
     * Chauffeurs bloqués : suspendus, licenciés ou permis expiré.
     * Implémente la règle métier #4.
     */
    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereIn('status', ['suspended', 'terminated'])
              ->orWhere('license_expiry_date', '<=', now());
        });
    }

    /**
     * Chauffeurs dont le permis expire dans $days jours.
     * Utilisé par le scheduler d'alertes.
     */
    public function scopeExpiring(Builder $query, int $days = 30): Builder
    {
        return $query->where('license_expiry_date', '<=', now()->addDays($days))
                     ->where('license_expiry_date', '>=', now());
    }

    /**
     * Chauffeurs ayant une affectation conflictuelle sur la plage donnée.
     * Implémente la règle métier #3.
     */
    public function scopeWithConflict(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereHas('assignments', function (Builder $q) use ($start, $end) {
            $q->whereIn('status', ['planned', 'confirmed', 'in_progress'])
              ->where('datetime_start', '<', $end)
              ->where('datetime_end_planned', '>', $start);
        });
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    /**
     * Vrai si le chauffeur est bloqué et ne peut pas être affecté.
     * Règle métier #4.
     */
    protected function isBlocked(): Attribute
    {
        return Attribute::make(
            get: fn() => in_array($this->status, ['suspended', 'terminated'], true)
                      || $this->license_expiry_date?->isPast(),
        );
    }

    /** Vrai si le permis de conduire est encore valide */
    protected function isLicenseExpired(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_expiry_date?->isPast() ?? false,
        );
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Vérifie si le chauffeur possède une catégorie de permis donnée */
    public function hasLicenseCategory(string $category): bool
    {
        return in_array($category, $this->license_categories ?? [], true);
    }

    /** Jours restants avant expiration du permis (négatif si expiré) */
    public function daysUntilLicenseExpiry(): int
    {
        return (int) now()->diffInDays($this->license_expiry_date, false);
    }
}
