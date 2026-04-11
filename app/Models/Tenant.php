<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

/**
 * Société cliente — stockée dans la base centrale (landlord).
 *
 * @property int         $id
 * @property string      $name
 * @property string      $slug
 * @property string      $domain
 * @property string      $database
 * @property int|null    $plan_id
 * @property string      $status     trial|active|suspended|cancelled
 * @property int         $max_vehicles
 * @property int         $max_users
 */
class Tenant extends BaseTenant
{
    use SoftDeletes;

    protected $connection = 'landlord';

    protected $fillable = [
        'name', 'slug', 'domain', 'database', 'plan_id',
        'status', 'trial_ends_at', 'subscribed_at', 'suspended_at',
        'contact_name', 'contact_email', 'contact_phone',
        'country', 'timezone', 'max_vehicles', 'max_users',
    ];

    protected $casts = [
        'trial_ends_at'  => 'datetime',
        'subscribed_at'  => 'datetime',
        'suspended_at'   => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): \Illuminate\Database\Eloquent\Builder|HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /** Retourne le nom de la BDD à partir du slug si non défini */
    public static function databaseNameForSlug(string $slug): string
    {
        return 'miensafleet_' . str_replace('-', '_', $slug);
    }
}
