<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Cached, typed access to platform settings stored in the database.
 */
class SettingsService
{
    private ?array $cache = null;

    public function all(): array
    {
        return $this->cache ??= Cache::rememberForever(
            Setting::CACHE_KEY,
            fn () => Setting::query()->get()->mapWithKeys(fn (Setting $s) => [
                $s->key => $s->typed_value,
            ])->all()
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function group(string $group): array
    {
        return Setting::query()->where('group', $group)->get()
            ->mapWithKeys(fn (Setting $s) => [$s->key => $s->typed_value])->all();
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'string', bool $encrypted = false): Setting
    {
        $stored = match ($type) {
            'json' => json_encode($value, JSON_UNESCAPED_SLASHES),
            'bool' => $value ? '1' : '0',
            default => (string) $value,
        };

        if ($encrypted && filled($stored)) {
            $stored = Crypt::encryptString($stored);
        }

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'group' => $group, 'type' => $type, 'is_encrypted' => $encrypted],
        );

        $this->flush();

        return $setting;
    }

    public function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'bool',
                is_int($value) => 'int',
                is_array($value) => 'json',
                default => 'string',
            };

            $this->set($key, $value, $group, $type);
        }
    }

    public function flush(): void
    {
        $this->cache = null;
        Cache::forget(Setting::CACHE_KEY);
    }

    public function isMaintenanceMode(): bool
    {
        return (bool) $this->get('maintenance_mode', false);
    }

    public function brand(): array
    {
        return [
            'name' => $this->get('platform_name', config('platform.name')),
            'tagline' => $this->get('platform_tagline', config('platform.tagline')),
            'logo' => $this->get('platform_logo'),
            'favicon' => $this->get('platform_favicon'),
            'primary_color' => $this->get('brand_primary', '#6366f1'),
            'accent_color' => $this->get('brand_accent', '#22d3ee'),
            'support_email' => $this->get('support_email', config('platform.support_email')),
        ];
    }
}
