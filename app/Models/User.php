<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'department',
        'job_title',
        'avatar',
        'status',
        'suspension_reason',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'last_login_ip',
        'last_login_at',
        'password_changed_at',
        'password',
        'created_by',
        'driver_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'           => 'datetime',
            'last_login_at'               => 'datetime',
            'password_changed_at'         => 'datetime',
            'two_factor_enabled'          => 'boolean',
            'password'                    => 'hashed',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('users')
            ->logOnly([
                'name', 'email', 'phone', 'department', 'job_title',
                'status', 'suspension_reason', 'driver_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Utilisateur qui a créé ce compte */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Profil chauffeur lié à ce compte (si l'utilisateur est chauffeur) */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /** Demandes de véhicule soumises par cet utilisateur */
    public function vehicleRequests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class, 'requester_id');
    }

    /** Demandes de véhicule traitées par cet utilisateur */
    public function reviewedRequests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class, 'reviewed_by');
    }

    /** Affectations validées par cet utilisateur */
    public function validatedAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'validated_by');
    }

    /** Infractions où cet utilisateur (non-chauffeur) était au volant */
    public function infractions(): HasMany
    {
        return $this->hasMany(Infraction::class, 'user_id');
    }

    /** Alertes ciblant cet utilisateur */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'user_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', 'suspended');
    }

    public function scopeInDepartment(Builder $query, string $department): Builder
    {
        return $query->where('department', $department);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDriver(): bool
    {
        return $this->driver_id !== null;
    }
}
