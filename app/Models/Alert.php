<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'vehicle_id',
        'driver_id',
        'user_id',
        'request_id',
        'infraction_id',
        'title',
        'message',
        'due_date',
        'days_remaining',
        'severity',
        'channels',
        'sent_at',
        'send_failed',
        'status',
        'processed_by',
        'processed_at',
        'process_notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date'       => 'date',
            'sent_at'        => 'datetime',
            'processed_at'   => 'datetime',
            'days_remaining' => 'integer',
            'channels'       => 'array',
            'send_failed'    => 'boolean',
        ];
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Véhicule concerné par l'alerte (nullable) */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Chauffeur concerné par l'alerte (nullable) */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** Utilisateur concerné par l'alerte (nullable) */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Demande de véhicule en retard ou sans réponse (nullable) */
    public function vehicleRequest(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class, 'request_id');
    }

    /** Infraction à l'origine de l'alerte (nullable) */
    public function infraction(): BelongsTo
    {
        return $this->belongsTo(Infraction::class);
    }

    /** Gestionnaire qui a traité l'alerte */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Alertes non encore traitées (new ou seen) */
    public function scopeUnprocessed(Builder $query): Builder
    {
        return $query->whereIn('status', ['new', 'seen']);
    }

    /** Alertes critiques nécessitant une action immédiate */
    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('severity', 'critical');
    }

    /** Alertes non encore envoyées (sans date d'envoi et sans échec) */
    public function scopePendingSend(Builder $query): Builder
    {
        return $query->whereNull('sent_at')->where('send_failed', false);
    }

    /** Alertes dont la date d'échéance est dépassée */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
                     ->where('due_date', '<', now())
                     ->whereIn('status', ['new', 'seen']);
    }

    /** Filtrer par type d'alerte */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    public function markAsSeen(): void
    {
        if ($this->status === 'new') {
            $this->update(['status' => 'seen']);
        }
    }

    public function markAsProcessed(int $userId, ?string $notes = null): void
    {
        $this->update([
            'status'       => 'processed',
            'processed_by' => $userId,
            'processed_at' => now(),
            'process_notes' => $notes,
        ]);
    }
}
