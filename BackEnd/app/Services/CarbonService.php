<?php

namespace App\Services;

class CarbonService
{
    // kg CO2-equivalent avoided per recycled item, by material. Draft values
    // pending supervisor review — each is (assumed average item weight) x
    // (kg CO2e per kg from published LCA literature). See
    // docs/superpowers/specs/2026-09-02-carbon-saved-design.md §4 for sources.
    // Fixed per item (not derived from weight_grams, which is simulated —
    // see TransactionController::weigh()) for the same reason the points
    // system moved to a fixed amount per material.
    private const KG_CO2_PER_ITEM = [
        'aluminum' => 0.226, // 15g/can x 15.1 kg CO2e/kg (Peng et al. 2022, Processes)
        'plastic'  => 0.038, // 20g/bottle x 1.88 kg CO2e/kg (Muangmeesri et al. 2024, ACS Sust. Chem. Eng.)
        'glass'    => 0.15,  // 300g/bottle x 0.5 kg CO2e/kg (Larsen et al. 2009, Waste Manag. & Research)
        'paper'    => 0.04,  // 100g/item x 0.4 kg CO2e/kg (conservative mid-estimate; Sun et al. 2018 vs Merrild et al. 2009 range)
    ];

    public static function forMaterial(?string $material): float
    {
        return self::KG_CO2_PER_ITEM[$material] ?? 0.0;
    }
}
