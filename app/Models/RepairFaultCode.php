<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairFaultCode extends Model
{
    protected $fillable = [
        'repair_id',
        'code',
        'category',
        'label',
        'garage_diagnosis',
        'work_performed',
        'resolution_status',
        'fault_cost',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'fault_cost'  => 'decimal:2',
            'sort_order'  => 'integer',
        ];
    }

    // ── Préfixes de codes par catégorie ─────────────────────────────────
    public const CATEGORY_PREFIXES = [
        'anomaly'   => 'AN',
        'breakdown' => 'PN',
        'wear'      => 'US',
        'accident'  => 'AC',
        'other'     => 'AU',
    ];

    public const CATEGORY_LABELS = [
        'anomaly'   => 'Anomalie',
        'breakdown' => 'Panne',
        'wear'      => 'Usure',
        'accident'  => 'Accident',
        'other'     => 'Autre',
    ];

    public const RESOLUTION_LABELS = [
        'pending'     => 'En attente',
        'resolved'    => 'Résolu',
        'partial'     => 'Partiellement résolu',
        'deferred'    => 'Reporté',
        'not_covered' => 'Non pris en charge',
    ];

    // ── Boot ─────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $faultCode) {
            if (empty($faultCode->code)) {
                $faultCode->code = static::generateCode($faultCode->repair_id, $faultCode->category ?? 'other');
            }
        });
    }

    // ── Relation ─────────────────────────────────────────────────────────

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    // ── Génération automatique du code ───────────────────────────────────

    /**
     * Génère le prochain code pour une catégorie donnée sur une réparation.
     *
     * Format : {PREFIX}{seq 2 chiffres}  ex: PN01, PN02, AN01
     * La séquence est propre à chaque couple (repair_id × category).
     */
    public static function generateCode(int $repairId, string $category): string
    {
        $prefix = self::CATEGORY_PREFIXES[$category] ?? 'AU';

        $count = static::where('repair_id', $repairId)
            ->where('category', $category)
            ->count();

        return $prefix . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
    }

    // ── Accesseurs utilitaires ───────────────────────────────────────────

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? $this->category;
    }

    public function getResolutionLabelAttribute(): string
    {
        return self::RESOLUTION_LABELS[$this->resolution_status] ?? $this->resolution_status;
    }

    public function isResolved(): bool
    {
        return $this->resolution_status === 'resolved';
    }

    public function isPending(): bool
    {
        return $this->resolution_status === 'pending';
    }
}
