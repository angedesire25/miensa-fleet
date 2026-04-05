<?php

namespace App\Models;

use Carbon\Carbon;
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

class Vehicle extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'brand',
        'model',
        'plate',
        'year',
        'color',
        'vin',
        'fuel_type',
        'vehicle_type',
        'license_category',
        'seats',
        'payload_kg',
        'km_current',
        'km_next_service',
        'km_last_oil_change',
        'date_last_oil_change',
        'status',
        'current_driver_id',
        'purchase_price',
        'purchase_date',
        'insurance_company',
        'insurance_policy_number',
        'photos',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'year'                 => 'integer',
            'seats'                => 'integer',
            'payload_kg'           => 'integer',
            'km_current'           => 'integer',
            'km_next_service'      => 'integer',
            'km_last_oil_change'   => 'integer',
            'date_last_oil_change' => 'date',
            'purchase_price'       => 'decimal:2',
            'purchase_date'        => 'date',
            'photos'               => 'array',
        ];
    }

    protected $appends = ['needs_service', 'main_photo_url', 'current_battery'];

    // ── Spatie Activitylog ──────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('vehicles')
            ->logOnly([
                'brand', 'model', 'plate', 'year', 'color', 'vin',
                'fuel_type', 'vehicle_type', 'license_category',
                'km_current', 'km_next_service', 'status',
                'current_driver_id', 'insurance_company', 'insurance_policy_number',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ── Relations ──────────────────────────────────────────────────────────

    /** Chauffeur actuellement au volant de ce véhicule */
    public function currentDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'current_driver_id');
    }

    /** Utilisateur qui a créé la fiche véhicule */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Toutes les affectations de ce véhicule */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /** Affectation actuellement en cours (en_progress) */
    public function currentAssignment(): HasOne
    {
        return $this->hasOne(Assignment::class)
                    ->where('status', 'in_progress')
                    ->latest('datetime_start');
    }

    /** Demandes de véhicule */
    public function vehicleRequests(): HasMany
    {
        return $this->hasMany(VehicleRequest::class);
    }

    /** Fiches de contrôle */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    /** Documents administratifs (assurance, CT, carte grise…) */
    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    /** Infractions commises avec ce véhicule */
    public function infractions(): HasMany
    {
        return $this->hasMany(Infraction::class);
    }

    /** Alertes liées à ce véhicule */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /** Carnet de bord */
    public function tripLogs(): HasMany
    {
        return $this->hasMany(TripLog::class);
    }

    /** Toutes les photos de ce véhicule (tous contextes confondus) */
    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class);
    }

    /**
     * Photo de profil principale du véhicule (contexte = vehicle_profile).
     * Retourne la plus récente si plusieurs photos de profil existent.
     */
    public function profilePhoto(): HasOne
    {
        return $this->hasOne(VehiclePhoto::class)
                    ->where('context', 'vehicle_profile')
                    ->latest();
    }

    /** Tous les sinistres déclarés pour ce véhicule */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Sinistre en cours : véhicule actuellement au garage suite à un sinistre.
     * Un seul à la fois (status = at_garage) ; retourne le plus récent.
     */
    public function activeIncident(): HasOne
    {
        return $this->hasOne(Incident::class)
                    ->where('status', 'at_garage')
                    ->latest();
    }

    /** Toutes les réparations de ce véhicule (préventif + curatif) */
    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    /**
     * Réparation en cours : véhicule actuellement chez un prestataire.
     * Couvre les statuts actifs de l'atelier (envoyé jusqu'en réparation).
     */
    public function currentRepair(): HasOne
    {
        return $this->hasOne(Repair::class)
                    ->whereIn('status', ['sent', 'diagnosing', 'repairing'])
                    ->latest();
    }

    /** Historique complet des pièces remplacées sur ce véhicule */
    public function partsHistory(): HasMany
    {
        return $this->hasMany(PartReplacement::class);
    }

    /**
     * Pièces actives d'une catégorie donnée montées sur ce véhicule.
     * Exemple : $vehicle->activeParts('battery') → batteries actuellement en service.
     *
     * Note : retourne une instance HasMany (pas une Collection) pour permettre
     * des appels chaînés (->latest('replaced_at')->first(), ->count()…).
     */
    public function activeParts(string $category): HasMany
    {
        return $this->hasMany(PartReplacement::class)
                    ->where('part_category', $category)
                    ->where('status', 'active');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Véhicules disponibles à l'affectation */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    /** Véhicules en cours de maintenance */
    public function scopeInMaintenance(Builder $query): Builder
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Véhicules actuellement au garage suite à un sinistre.
     * Alias sémantique de scopeInMaintenance pour les contextes sinistre.
     */
    public function scopeAtGarage(Builder $query): Builder
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Véhicules ayant un sinistre en cours (status = at_garage).
     * Utile pour les tableaux de bord et les filtres de flotte.
     */
    public function scopeWithActiveIncident(Builder $query): Builder
    {
        return $query->whereHas('incidents', function (Builder $q) {
            $q->where('status', 'at_garage');
        });
    }

    /** Véhicules encore en service (exclu vendus/retirés) */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['sold', 'retired']);
    }

    /**
     * Véhicules dont un document expire dans $days jours.
     * Utile pour le scheduler d'alertes.
     */
    public function scopeWithExpiringDocuments(Builder $query, int $days = 30): Builder
    {
        return $query->whereHas('documents', function (Builder $q) use ($days) {
            $q->where('expiry_date', '<=', now()->addDays($days))
              ->where('expiry_date', '>=', now())
              ->whereIn('status', ['valid', 'expiring_soon']);
        });
    }

    /**
     * Véhicules ayant une affectation conflictuelle sur la plage donnée.
     * Implémente la règle métier #2 : un véhicule ne peut pas avoir
     * deux affectations simultanées.
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

    /** Vrai si le kilométrage a atteint l'échéance du prochain entretien */
    protected function needsService(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->km_next_service !== null
                      && $this->km_current >= $this->km_next_service,
        );
    }

    /**
     * URL de la photo de profil principale du véhicule.
     * Retourne le chemin de la photo de profil si elle existe,
     * sinon l'image placeholder par défaut.
     */
    protected function mainPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->profilePhoto?->file_path ?? 'images/vehicle-placeholder.png',
        );
    }

    /**
     * Batterie actuellement montée sur ce véhicule.
     * Retourne le PartReplacement le plus récent de catégorie 'battery'
     * dont le statut est 'active', ou null si aucune batterie enregistrée.
     */
    protected function currentBattery(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->partsHistory()
                ->where('part_category', 'battery')
                ->where('status', 'active')
                ->latest('replaced_at')
                ->first(),
        );
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isCompatibleWithLicense(string $licenseCategory): bool
    {
        $hierarchy = ['A', 'B', 'C', 'D', 'E', 'BE', 'CE'];
        $required  = array_search($this->license_category, $hierarchy, true);
        $held      = array_search($licenseCategory, $hierarchy, true);

        return $held !== false && $held >= $required;
    }
}
