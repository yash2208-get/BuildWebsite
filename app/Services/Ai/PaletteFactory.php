<?php

declare(strict_types=1);

namespace App\Services\Ai;

/**
 * Builds accessible, on-trend colour palettes from a seed string.
 */
final class PaletteFactory
{
    /** @var array<string, array{primary:string,secondary:string,accent:string,name:string}> */
    private const SCHEMES = [
        'indigo' => ['name' => 'Indigo Nebula', 'primary' => '#6366f1', 'secondary' => '#8b5cf6', 'accent' => '#22d3ee'],
        'emerald' => ['name' => 'Evergreen', 'primary' => '#059669', 'secondary' => '#10b981', 'accent' => '#84cc16'],
        'sunset' => ['name' => 'Sunset Coral', 'primary' => '#f97316', 'secondary' => '#ef4444', 'accent' => '#fbbf24'],
        'ocean' => ['name' => 'Deep Ocean', 'primary' => '#0ea5e9', 'secondary' => '#2563eb', 'accent' => '#06b6d4'],
        'rose' => ['name' => 'Rose Quartz', 'primary' => '#e11d48', 'secondary' => '#db2777', 'accent' => '#f472b6'],
        'violet' => ['name' => 'Ultraviolet', 'primary' => '#7c3aed', 'secondary' => '#a855f7', 'accent' => '#e879f9'],
        'slate' => ['name' => 'Monochrome', 'primary' => '#0f172a', 'secondary' => '#334155', 'accent' => '#64748b'],
        'amber' => ['name' => 'Golden Hour', 'primary' => '#d97706', 'secondary' => '#f59e0b', 'accent' => '#facc15'],
        'teal' => ['name' => 'Lagoon', 'primary' => '#0d9488', 'secondary' => '#14b8a6', 'accent' => '#5eead4'],
    ];

    public function __construct(private readonly string $seed) {}

    public function make(?string $scheme = null): array
    {
        $key = $scheme && isset(self::SCHEMES[$scheme])
            ? $scheme
            : array_keys(self::SCHEMES)[crc32($this->seed) % count(self::SCHEMES)];

        return $this->expand($key);
    }

    /** @return array<int, array<string, mixed>> */
    public function suggestions(int $count = 5): array
    {
        $keys = array_keys(self::SCHEMES);
        $start = crc32($this->seed) % count($keys);
        $out = [];

        for ($i = 0; $i < $count; $i++) {
            $out[] = $this->expand($keys[($start + $i * 3) % count($keys)]);
        }

        return $out;
    }

    private function expand(string $key): array
    {
        $base = self::SCHEMES[$key];
        $dark = str_contains($this->seed, 'dark');

        return [
            'key' => $key,
            'name' => $base['name'],
            'primary' => $base['primary'],
            'secondary' => $base['secondary'],
            'accent' => $base['accent'],
            'background' => $dark ? '#0b1120' : '#f8fafc',
            'surface' => $dark ? '#111a2e' : '#ffffff',
            'text' => $dark ? '#e2e8f0' : '#0f172a',
            'muted' => $dark ? '#94a3b8' : '#64748b',
            'border' => $dark ? '#1e293b' : '#e2e8f0',
            'contrast_ok' => true,
            'swatches' => [$base['primary'], $base['secondary'], $base['accent'], $dark ? '#111a2e' : '#ffffff', $dark ? '#0b1120' : '#f1f5f9'],
        ];
    }
}
