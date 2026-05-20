<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    /**
     * Get a setting value by key, with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("site_setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key, clearing cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget("site_setting.{$key}");
    }

    /**
     * Get all settings as key-value pairs, optionally filtered by group.
     */
    public static function allGrouped(): array
    {
        return static::all()->groupBy('group')->map(function ($items) {
            return $items->pluck('value', 'key');
        })->toArray();
    }

    /**
     * Clear all setting caches.
     */
    public static function clearCache(): void
    {
        static::all()->each(function ($setting) {
            Cache::forget("site_setting.{$setting->key}");
        });
    }
}
