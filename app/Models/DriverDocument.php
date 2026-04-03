<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DriverDocument extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'driver_id',
        'type',
        'document_number',
        'issue_date',
        'expiry_date',
        'medical_result',
        'next_check_date',
        'training_organization',
        'renewal_date',
        'file_path',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'      => 'date',
            'expiry_date'     => 'date',
            'next_check_date' => 'date',
            'renewal_date'    => 'date',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('driver_documents')
            ->logOnly([
                'driver_id', 'type', 'document_number',
                'issue_date', 'expiry_date', 'medical_result',
                'next_check_date', 'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Documents encore valides */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status', 'valid');
    }

    /** Documents expirés */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')
                     ->orWhere('expiry_date', '<', now());
    }

    /**
     * Documents qui expirent dans $days jours.
     * Utilisé par le scheduler d'alertes (visite médicale, permis…).
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>=', now())
                     ->whereIn('status', ['valid', 'expiring_soon']);
    }

    /** Filtrer par type de document */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /** Uniquement les visites médicales */
    public function scopeMedical(Builder $query): Builder
    {
        return $query->where('type', 'medical_fitness');
    }

    /**
     * Visites médicales dont la prochaine échéance approche.
     * Utile pour alerter avant la date de next_check_date.
     */
    public function scopeMedicalCheckDue(Builder $query, int $days = 30): Builder
    {
        return $query->where('type', 'medical_fitness')
                     ->where('next_check_date', '<=', now()->addDays($days))
                     ->where('next_check_date', '>=', now());
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->expiry_date !== null && $this->expiry_date->isPast());
    }

    /** Un médecin a déclaré le chauffeur inapte → le chauffeur est bloqué */
    public function isDriverUnfit(): bool
    {
        return $this->type === 'medical_fitness'
            && $this->medical_result === 'unfit';
    }

    public function daysUntilExpiry(): ?int
    {
        if ($this->expiry_date === null) {
            return null;
        }

        return (int) now()->diffInDays($this->expiry_date, false);
    }
}
