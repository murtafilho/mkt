<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Get a setting value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Cache settings for 1 hour
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            // Cast based on type
            return match ($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $description = null): Setting
    {
        // Convert value to string for storage
        $stringValue = match ($type) {
            'json' => is_string($value) ? $value : json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        // Clear cache
        Cache::forget("setting.{$key}");

        return $setting;
    }

    /**
     * Get all settings in a group.
     */
    public function getGroup(string $group): array
    {
        $settings = Setting::where('group', $group)->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = match ($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        }

        return $result;
    }

    /**
     * Delete a setting.
     */
    public function delete(string $key): bool
    {
        $deleted = Setting::where('key', $key)->delete();

        Cache::forget("setting.{$key}");

        return $deleted > 0;
    }

    /**
     * Get all settings organized by group.
     */
    public function all(): array
    {
        $settings = Setting::all();

        $organized = [];

        foreach ($settings as $setting) {
            $group = $setting->group ?? 'general';

            if (! isset($organized[$group])) {
                $organized[$group] = [];
            }

            $organized[$group][$setting->key] = match ($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        }

        return $organized;
    }

    /**
     * Flush settings cache.
     */
    public function clearCache(): void
    {
        $keys = Setting::pluck('key');

        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }
}
