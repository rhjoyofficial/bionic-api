<?php

namespace App\Domains\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'type', 'value', 'label', 'description', 'is_readonly', 'sort_order'];

    protected $casts = [
        'is_readonly' => 'boolean',
        'sort_order'  => 'integer',
    ];

    protected const CACHE_KEY = 'admin_settings_all';
    protected const CACHE_TTL = 3600; // 1 hour

    // ── Static Helpers ────────────────────────────────────────────────────────

    /**
     * Get a single setting value, cast to its declared type.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::fromCache()->firstWhere('key', $key);

        if (! $setting) {
            return $default;
        }

        return static::cast($setting->value, $setting->type);
    }

    /**
     * Update a setting by key and bust the cache.
     */
    public static function set(string $key, mixed $value): bool
    {
        $rows = static::where('key', $key)->where('is_readonly', false)->update([
            'value'      => (string) $value,
            'updated_at' => now(),
        ]);

        static::bustCache();

        return $rows > 0;
    }

    /**
     * Get all settings for a given group, keyed by key.
     */
    public static function group(string $group): Collection
    {
        return static::fromCache()
            ->where('group', $group)
            ->sortBy('sort_order')
            ->values();
    }

    /**
     * Get all settings as a flat key → value map.
     */
    public static function all_values(): array
    {
        return static::fromCache()
            ->mapWithKeys(fn($s) => [$s->key => static::cast($s->value, $s->type)])
            ->all();
    }

    public static function bustCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private static function fromCache(): Collection
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, fn() => static::orderBy('sort_order')->get());
    }

    private static function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            default   => $value,
        };
    }
}
