<?php

namespace App\Services;

use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\User;

class UserImpactService
{
    public function carbonFor(User $user, bool $platform = false): float
    {
        return $this->summaryFor($user, $platform)['carbon'];
    }

    /** @return array{items: int, carbon: float} */
    public function summaryFor(User $user, bool $platform = false): array
    {
        $counts = $user->transactions()->where('is_valid', true)
            ->whereHas('machine', fn ($machine) => $machine->rvmOnly())
            ->selectRaw('material_selected, COUNT(*) AS total')
            ->groupBy('material_selected')->pluck('total', 'material_selected');

        $carbon = $counts->map(fn ($count, $material) => $count * CarbonService::forMaterial($material))->sum();
        if ($platform && config('platform.enabled')) {
            $carbon += CompactorBatch::where('user_id', $user->id)->sum('carbon_kg');
            $carbon += ComposterDeposit::where('user_id', $user->id)->sum('carbon_kg');
        }

        return ['items' => (int) $counts->sum(), 'carbon' => round($carbon, 3)];
    }
}
