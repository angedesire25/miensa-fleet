<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleCleaning extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'user_id',
        'scheduled_date',
        'scheduled_time',
        'cleaning_type',
        'status',
        'notes',
        'confirmed_at',
        'confirmed_by',
        'completed_at',
        'completion_proof',
        'completion_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'confirmed_at'   => 'datetime',
            'completed_at'   => 'datetime',
        ];
    }

    // ── Relations ──────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** Collaborateur ou utilisateur responsable */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeScheduled(Builder $q): Builder
    {
        return $q->where('status', 'scheduled');
    }

    public function scopeThisWeek(Builder $q): Builder
    {
        return $q->whereBetween('scheduled_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ]);
    }

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where('scheduled_date', '>=', today())
                 ->whereNotIn('status', ['completed', 'cancelled']);
    }

    // ── Accesseurs ─────────────────────────────────────────────────────────

    public function getTypeLabel(): string
    {
        return match($this->cleaning_type) {
            'exterior' => 'Extérieur',
            'interior' => 'Intérieur',
            'full'     => 'Complet',
            default    => ucfirst($this->cleaning_type),
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'scheduled' => 'Planifié',
            'confirmed' => 'Confirmé',
            'completed' => 'Effectué',
            'missed'    => 'Manqué',
            'cancelled' => 'Annulé',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusColor(): array
    {
        return match($this->status) {
            'scheduled' => ['#3b82f6', '#eff6ff'],
            'confirmed' => ['#f59e0b', '#fffbeb'],
            'completed' => ['#10b981', '#f0fdf4'],
            'missed'    => ['#ef4444', '#fef2f2'],
            'cancelled' => ['#64748b', '#f8fafc'],
            default     => ['#64748b', '#f8fafc'],
        };
    }

    /** Nom affiché du responsable (chauffeur ou collaborateur) */
    public function getResponsibleName(): string
    {
        if ($this->driver) {
            return $this->driver->full_name;
        }
        if ($this->responsible) {
            return $this->responsible->name;
        }
        return '—';
    }
}
