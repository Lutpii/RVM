<?php

namespace App\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class PlatformSettingsService
{
    private const FILE = 'platform_settings.json';

    private const DEFAULTS = [
        'composter_points_per_kg' => 10,
        'composter_kg_co2_per_kg' => 0.44,
    ];

    /** @return array{composter_points_per_kg: int, composter_kg_co2_per_kg: float} */
    public function load(): array
    {
        $disk = Storage::disk('local');
        if (!$disk->exists(self::FILE)) {
            return self::DEFAULTS;
        }

        $stored = json_decode($disk->get(self::FILE) ?? '', true);
        $points = $stored['composter_points_per_kg'] ?? null;
        $carbon = $stored['composter_kg_co2_per_kg'] ?? null;

        return [
            'composter_points_per_kg' => is_int($points) && $points >= 1 && $points <= 1000
                ? $points
                : self::DEFAULTS['composter_points_per_kg'],
            'composter_kg_co2_per_kg' => is_numeric($carbon) && $carbon >= 0.01 && $carbon <= 5
                ? (float) $carbon
                : self::DEFAULTS['composter_kg_co2_per_kg'],
        ];
    }

    public function save(array $settings): void
    {
        $path = Storage::disk('local')->path(self::FILE);
        $files = new Filesystem();
        $files->ensureDirectoryExists(dirname($path));
        $files->replace($path, json_encode([
            'composter_points_per_kg' => (int) $settings['composter_points_per_kg'],
            'composter_kg_co2_per_kg' => (float) $settings['composter_kg_co2_per_kg'],
        ], JSON_THROW_ON_ERROR));
    }
}
