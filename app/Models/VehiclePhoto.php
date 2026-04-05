<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VehiclePhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'photoable_type',
        'photoable_id',
        'context',
        'file_path',
        'original_name',
        'mime_type',
        'size_kb',
        'caption',
        'taken_at',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
            'size_kb'  => 'integer',
        ];
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Véhicule auquel appartient la photo (ancre obligatoire) */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Utilisateur qui a uploadé la photo */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Modèle polymorphique lié à la photo.
     *
     * Peut pointer vers : Vehicle, Incident, Repair, Assignment, VehicleRequest.
     * Null si la photo est une vue générale du véhicule (profil, galerie).
     */
    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Filtre par contexte sémantique exact */
    public function scopeContext(Builder $query, string $context): Builder
    {
        return $query->where('context', $context);
    }

    /** Photos de profil du véhicule (photo principale de la fiche) */
    public function scopeProfile(Builder $query): Builder
    {
        return $query->where('context', 'vehicle_profile');
    }

    /** Photos liées à un sinistre (état avant et dégâts constatés) */
    public function scopeIncident(Builder $query): Builder
    {
        return $query->whereIn('context', ['incident_before', 'incident_damage']);
    }

    /** Photos liées à une réparation (en cours et après) */
    public function scopeRepair(Builder $query): Builder
    {
        return $query->whereIn('context', ['repair_in_progress', 'repair_after']);
    }
}
