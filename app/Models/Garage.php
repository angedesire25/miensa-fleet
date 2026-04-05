<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Garage extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'address',
        'city',
        'phone',
        'email',
        'contact_person',
        'specializations',
        'rating',
        'is_approved',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'is_approved'     => 'boolean',
            'rating'          => 'integer',
        ];
    }

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('garages')
            ->logOnly([
                'name', 'type', 'city',
                'is_approved', 'rating', 'specializations',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Réparations effectuées par ce garage */
    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    /** Utilisateur qui a créé la fiche garage */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Garages approuvés par la société */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    /** Garages situés dans une ville donnée */
    public function scopeByCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }

    /**
     * Garages ayant une spécialisation donnée.
     * Ex : Garage::specialized('body') → garages capables de carrosserie
     */
    public function scopeSpecialized(Builder $query, string $type): Builder
    {
        return $query->whereJsonContains('specializations', $type);
    }
}
