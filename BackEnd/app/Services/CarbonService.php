<?php

namespace App\Services;

class CarbonService
{
    // Item masses are assumptions. Kiosk weight_grams may be simulated.
    private const SOURCES = [
        'aluminum' => [
            'mass_g' => 15,
            'kg_co2e_per_kg' => 15.1,
            'kg_co2e_per_item' => 0.226,
            'reference' => 'Peng et al. 2022, Processes',
            'derived' => false,
        ],
        'plastic' => [
            'mass_g' => 20,
            // Demo estimate derived from the abstract: 1,600 / (1 - 0.272) - 1,600
            // is about 598 kg CO2e/t avoided. Confirm against the full PDF before reporting.
            'kg_co2e_per_kg' => 0.6,
            'kg_co2e_per_item' => 0.012,
            'reference' => 'Lele et al. 2026, J. Environ. Educ. Res. Sustain. Dev. (derived from abstract)',
            'derived' => true,
        ],
        'paper' => [
            'mass_g' => 100,
            'kg_co2e_per_kg' => 0.1,
            'kg_co2e_per_item' => 0.01,
            'reference' => 'Varling et al. 2026, Sci. Total Environ.',
            'derived' => false,
        ],
        'glass' => [
            'mass_g' => 300,
            'kg_co2e_per_kg' => 0.5,
            'kg_co2e_per_item' => 0.15,
            'reference' => 'Larsen et al. 2009, Waste Manag. Res.',
            'derived' => false,
        ],
        'food_waste' => [
            'mass_g' => null,
            'kg_co2e_per_kg' => 0.44,
            'kg_co2e_per_item' => null,
            'reference' => 'Nordahl et al. 2020, Environ. Sci. Technol.',
            'derived' => false,
        ],
    ];

    // Total kg CO2e for a map of material => item count (unrounded). The one place
    // every aggregate (user totals, admin, landing, platform) does this sum.
    public static function forCounts(iterable $countsByMaterial): float
    {
        $kg = 0.0;
        foreach ($countsByMaterial as $material => $count) {
            $kg += (int) $count * self::forMaterial($material);
        }
        return $kg;
    }

    public static function forMaterial(?string $material): float
    {
        return self::SOURCES[$material]['kg_co2e_per_item'] ?? 0.0;
    }

    /** @return array<string, array{mass_g: ?int, kg_co2e_per_kg: float, kg_co2e_per_item: ?float, reference: string, derived: bool}> */
    public static function sources(): array
    {
        $sources = self::SOURCES;
        $sources['food_waste']['kg_co2e_per_kg'] = app(PlatformSettingsService::class)->load()['composter_kg_co2_per_kg'];

        return $sources;
    }
}
