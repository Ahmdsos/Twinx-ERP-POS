<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Setting Model
 * For storing and retrieving application settings
 */
class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // Cache key
    const CACHE_KEY = 'app_settings';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $settings = self::getAllCached();

        if (!isset($settings[$key])) {
            return $default;
        }

        return self::castValue($settings[$key]['value'], $settings[$key]['type']);
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value, string $group = 'general'): void
    {
        $type = self::detectType($value);

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );

        self::clearCache();
    }

    /**
     * Get all settings cached
     */
    public static function getAllCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::all()->keyBy('key')->map(fn($s) => [
                'value' => $s->value,
                'type' => $s->type,
                'group' => $s->group,
            ])->toArray();
        });
    }

    /**
     * Get settings by group
     */
    public static function getGroup(string $group): array
    {
        return self::where('group', $group)->get()->keyBy('key')->map(
            fn($s) =>
            self::castValue($s->value, $s->type)
        )->toArray();
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'integer', 'int' => (int) $value,
            'float', 'decimal' => (float) $value,
            'boolean', 'bool' => in_array(strtolower($value), ['1', 'true', 'yes']),
            'array', 'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Detect value type
     */
    protected static function detectType($value): string
    {
        if (is_bool($value))
            return 'boolean';
        if (is_int($value))
            return 'integer';
        if (is_float($value))
            return 'float';
        if (is_array($value) || is_object($value))
            return 'json';
        return 'string';
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
