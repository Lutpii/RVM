<?php

namespace App\Http\Controllers;

use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Services\CarbonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Numbers for the public landing page: lifetime items and CO2 across all
 * machines, and this month's CO2 split by material. No personal data, so no
 * login; cached briefly because every visitor of the landing page asks.
 */
class PublicStatsController extends Controller
{
    private const MATERIALS = ['plastic', 'aluminum', 'paper', 'glass'];

    public function show(): JsonResponse
    {
        $stats = Cache::remember('rvm:public_stats', now()->addMinutes(5), function () {
            $allTime = $this->countsByMaterial(null);
            $month = $this->countsByMaterial(now()->startOfMonth());

            $monthByMaterial = [];
            $monthItems = [];
            foreach (self::MATERIALS as $m) {
                $monthByMaterial[$m] = round(($month[$m] ?? 0) * CarbonService::forMaterial($m), 2);
                $monthItems[$m] = (int) ($month[$m] ?? 0);
            }

            return [
                'items_recycled'  => array_sum($allTime),
                'carbon_saved_kg' => $this->carbon($allTime),
                'machines'        => RvmMachine::count(),
                'month' => [
                    'year'            => (int) now()->year,
                    'month'           => (int) now()->month,
                    'items_recycled'  => array_sum($month),
                    'carbon_saved_kg' => $this->carbon($month),
                    'carbon_by_material_kg' => $monthByMaterial,
                    // The landing page bar shows items, not CO2: per item an
                    // aluminium can saves ~6x the CO2 of a bottle or paper,
                    // so a CO2 bar is always mostly aluminium.
                    'items_by_material'     => $monthItems,
                ],
            ];
        });

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /** @return array<string, int> accepted items per material */
    private function countsByMaterial($since): array
    {
        $q = Transaction::where('is_valid', 1);
        if ($since) $q->where('created_at', '>=', $since);

        return $q->selectRaw('material_selected, COUNT(*) AS n')
            ->groupBy('material_selected')
            ->pluck('n', 'material_selected')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    private function carbon(array $counts): float
    {
        $kg = 0.0;
        foreach ($counts as $material => $n) {
            $kg += $n * CarbonService::forMaterial($material);
        }
        return round($kg, 2);
    }
}
