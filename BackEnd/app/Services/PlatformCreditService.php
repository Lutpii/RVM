<?php

namespace App\Services;

use App\Http\Controllers\TransactionController;
use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\PointsHistory;
use App\Models\RvmMachine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlatformCreditService
{
    public function __construct(private PlatformSettingsService $settings)
    {
    }

    public function creditCompactorBatch(
        User $user, RvmMachine $machine, int $plastic, int $aluminum, int $nonTarget, ?Carbon $at = null
    ): CompactorBatch {
        $this->requireMachineType($machine, 'compactor');
        if (min($plastic, $aluminum, $nonTarget) < 0) {
            throw new InvalidArgumentException('Item counts must be nonnegative.');
        }

        $points = 0;
        $count = $plastic + $aluminum;
        for ($item = 0; $item < $count; $item++) {
            $points += rand(TransactionController::POINTS_MIN, TransactionController::POINTS_MAX);
        }
        $carbon = $plastic * CarbonService::forMaterial('plastic') + $aluminum * CarbonService::forMaterial('aluminum');

        return DB::transaction(function () use ($user, $machine, $plastic, $aluminum, $nonTarget, $points, $carbon, $at) {
            $batch = CompactorBatch::create([
                'user_id' => $user->id,
                'machine_id' => $machine->id,
                'plastic_count' => $plastic,
                'aluminum_count' => $aluminum,
                'non_target_count' => $nonTarget,
                'points_earned' => $points,
                'carbon_kg' => round($carbon, 3),
                'is_demo' => true,
                'created_at' => $at ?? now(),
            ]);
            $this->award($user, 'compactor', $batch->id, $points, $batch->created_at);

            return $batch;
        }, 3);
    }

    public function creditComposterDeposit(User $user, RvmMachine $machine, float $kg, ?Carbon $at = null): ComposterDeposit
    {
        $this->requireMachineType($machine, 'composter');
        $kg = round($kg, 2);
        if (!is_finite($kg) || $kg <= 0 || $kg > 999999.99) {
            throw new InvalidArgumentException('Composter weight is out of range.');
        }

        $settings = $this->settings->load();
        $points = (int) round($kg * $settings['composter_points_per_kg']);
        $carbon = round($kg * $settings['composter_kg_co2_per_kg'], 3);

        return DB::transaction(function () use ($user, $machine, $kg, $points, $carbon, $at) {
            $deposit = ComposterDeposit::create([
                'user_id' => $user->id,
                'machine_id' => $machine->id,
                'weight_kg' => $kg,
                'points_earned' => $points,
                'carbon_kg' => $carbon,
                'is_demo' => true,
                'created_at' => $at ?? now(),
            ]);
            $this->award($user, 'composter', $deposit->id, $points, $deposit->created_at);

            return $deposit;
        }, 3);
    }

    private function requireMachineType(RvmMachine $machine, string $type): void
    {
        if ($machine->machine_type !== $type) {
            throw new InvalidArgumentException("A {$type} machine is required.");
        }
    }

    private function award(User $user, string $source, int $sourceId, int $points, Carbon $at): void
    {
        $balance = User::query()->lockForUpdate()->findOrFail($user->id);
        $balance->increment('total_points', $points);

        $entry = new PointsHistory([
            'user_id' => $user->id,
            'points_change' => $points,
            'balance_after' => $balance->total_points,
            'type' => 'earned',
            'source' => $source,
            'source_id' => $sourceId,
        ]);
        $entry->created_at = $at;
        $entry->save();
    }
}
