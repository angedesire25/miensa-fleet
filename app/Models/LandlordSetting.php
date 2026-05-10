<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LandlordSetting extends Model
{
    protected $connection  = 'landlord';
    protected $table       = 'landlord_settings';
    protected $primaryKey  = 'key';
    protected $keyType     = 'string';
    public    $incrementing = false;

    protected $fillable = ['key', 'value'];

    /** Récupère un paramètre (avec cache 1h). */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("landlord_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::find($key);
            return $setting?->value ?? $default;
        });
    }

    /** Enregistre ou met à jour un paramètre, vide le cache. */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("landlord_setting_{$key}");
    }

    /** Supprime la valeur d'un paramètre. */
    public static function remove(string $key): void
    {
        static::where('key', $key)->delete();
        Cache::forget("landlord_setting_{$key}");
    }

    /** Récupère plusieurs paramètres en une fois. */
    public static function getMany(array $keys, array $defaults = []): array
    {
        return array_combine($keys, array_map(
            fn($k) => static::get($k, $defaults[$k] ?? null),
            $keys
        ));
    }
}
