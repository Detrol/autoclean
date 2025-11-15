<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected const CACHE_PREFIX = 'setting.';

    protected const CACHE_TTL = 86400; // 24 hours

    /**
     * Get a setting value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX.$key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public function set(string $key, mixed $value): bool
    {
        $setting = Setting::where('key', $key)->first();

        if (! $setting) {
            return false;
        }

        $setting->value = $value;
        $setting->save();

        // Clear cache
        Cache::forget(self::CACHE_PREFIX.$key);

        return true;
    }

    /**
     * Get all settings, optionally filtered by group
     */
    public function all(?string $group = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Setting::query();

        if ($group) {
            $query->byGroup($group);
        }

        return $query->orderBy('group')->orderBy('label')->get();
    }

    /**
     * Get all settings grouped by group
     */
    public function allGrouped(): array
    {
        return Setting::all()
            ->groupBy('group')
            ->map(fn ($items) => $items->sortBy('label'))
            ->toArray();
    }

    /**
     * Clear all settings cache
     */
    public function clearCache(): void
    {
        $settings = Setting::all();

        foreach ($settings as $setting) {
            Cache::forget(self::CACHE_PREFIX.$setting->key);
        }
    }
}
