<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FuelStation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'brand',
        'address',
        'city',
        'phone',
        'contact_person',
        'fuel_types',
        'is_active',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fuel_types' => 'array',
            'is_active'  => 'boolean',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('fuel_stations')
            ->logOnly(['name', 'brand', 'address', 'city', 'is_active', 'fuel_types'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Demandes de carburant liées à cette station */
    public function fuelRequests(): HasMany
    {
        return $this->hasMany(FuelRequest::class);
    }

    /** Transactions enregistrées dans cette station */
    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    /** Utilisateur qui a créé la station */
    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Stations actives uniquement */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Stations proposant un type de carburant donné */
    public function scopeWithFuelType(Builder $query, string $type): Builder
    {
        return $query->whereJsonContains('fuel_types', $type);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Libellé affiché : marque + nom ou nom seul */
    public function getDisplayNameAttribute(): string
    {
        return $this->brand ? "{$this->brand} — {$this->name}" : $this->name;
    }
}
