<?php

namespace App\Services;

use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\RvmMachine;
use Carbon\Carbon;

class PlatformTelemetry
{
    public function status(RvmMachine $machine, Carbon $now): string
    {
        if ($machine->status !== 'active') {
            return 'maintenance';
        }
        if ($machine->fault_active) {
            return 'fault';
        }
        if ($this->isRvm($machine) && $machine->last_seen_at?->lt($now->copy()->subMinutes(3))) {
            return 'offline';
        }

        $fill = $this->fillLevel($machine, $now);
        return match (true) {
            $fill >= 90 => 'full',
            $fill >= 80 => 'almost_full',
            default => 'available',
        };
    }

    public function fillLevel(RvmMachine $machine, Carbon $now): int
    {
        if ($this->isRvm($machine)) {
            return max(0, min(100, max((int) $machine->aluminum_level, (int) $machine->plastic_level, (int) $machine->paper_level)));
        }

        $minutesSinceEmpty = $this->lastEmptied($machine, $now)->diffInMinutes($now);

        return min(100, 6 + (int) floor($minutesSinceEmpty / 14));
    }

    public function compactor(RvmMachine $machine, Carbon $now): array
    {
        $counts = CompactorBatch::query()->where('machine_id', $machine->id)
            ->whereBetween('created_at', [$now->copy()->startOfDay(), $now])
            ->selectRaw('COUNT(*) AS batches, SUM(plastic_count + aluminum_count) AS items, SUM(non_target_count) AS non_targets')
            ->firstOrFail();
        $items = (int) $counts->items;
        $day = $now->toDateString();

        return [
            'fill_level' => $this->fillLevel($machine, $now),
            'cycles_today' => (int) ceil($items / 25),
            'batches_today' => (int) $counts->batches,
            'items_today' => $items,
            'non_target_today' => (int) $counts->non_targets,
            'last_emptied_at' => $this->lastEmptied($machine, $now)->toIso8601String(),
            'safety_ok' => $this->seed($machine, "safety:{$day}") % 37 !== 0,
            'smoke_ok' => $this->seed($machine, "smoke:{$day}") % 71 !== 0,
            'online' => $machine->status === 'active',
        ];
    }

    public function composter(RvmMachine $machine, Carbon $now): array
    {
        $day = $now->toDateString();
        $hour = $now->format('Y-m-d H');
        $temperature = 55 + $this->seed($machine, "temp:{$hour}") % 51 / 10;
        $moisture = 50 + $this->seed($machine, "moisture:{$hour}") % 101 / 10;
        $ph = 6.5 + $this->seed($machine, "ph:{$hour}") % 16 / 10;

        if ($now->hour === $this->seed($machine, "drift-hour:{$day}") % 24) {
            switch ($this->seed($machine, "drift-variable:{$day}") % 3) {
                case 0: $temperature = 53.5; break;
                case 1: $moisture = 62.0; break;
                case 2: $ph = 6.2; break;
            }
        }

        $age = ($now->dayOfYear + $this->seed($machine, 'age')) % 21;
        $phase = match (true) {
            $age < 3 => 'mesophilic',
            $age < 12 => 'thermophilic',
            $age < 17 => 'cooling',
            default => 'maturing',
        };
        $kg = ComposterDeposit::query()->where('machine_id', $machine->id)
            ->whereBetween('created_at', [$now->copy()->startOfDay(), $now])
            ->sum('weight_kg');

        return [
            'fill_level' => $this->fillLevel($machine, $now),
            'temperature_c' => (float) $temperature,
            'moisture_pct' => (float) $moisture,
            'ph' => $ph,
            'co2_level' => $temperature >= 59.5 ? 'high' : 'normal',
            'nh3_level' => $ph > 7.8 ? 'high' : ($ph < 6.6 ? 'low' : 'normal'),
            'drum_rotations_today' => intdiv($now->hour * 60 + $now->minute, 15),
            'kg_today' => (float) $kg,
            'phase' => $phase,
            'days_to_maturity' => 21 - $age,
            'in_ideal_range' => $temperature >= 55 && $temperature <= 60 &&
                $moisture >= 50 && $moisture <= 60 && $ph >= 6.5 && $ph <= 8,
        ];
    }

    /** @return list<array{level: string, code: string}> */
    public function alerts(RvmMachine $machine, Carbon $now): array
    {
        $status = $this->status($machine, $now);
        $alerts = match ($status) {
            'full' => [['level' => 'critical', 'code' => 'full']],
            'almost_full' => [['level' => 'warning', 'code' => 'almost_full']],
            default => [],
        };

        if ($machine->machine_type === 'compactor' && $this->compactor($machine, $now)['non_target_today'] > 0) {
            $alerts[] = ['level' => 'warning', 'code' => 'non_target_paused'];
        }
        if ($machine->machine_type === 'composter') {
            $reading = $this->composter($machine, $now);
            if ($reading['temperature_c'] < 55) {
                $alerts[] = ['level' => 'warning', 'code' => 'temp_low'];
            }
            if ($reading['moisture_pct'] < 50 || $reading['moisture_pct'] > 60) {
                $alerts[] = ['level' => 'warning', 'code' => 'moisture_out'];
            }
            if ($reading['ph'] < 6.5 || $reading['ph'] > 8) {
                $alerts[] = ['level' => 'warning', 'code' => 'ph_out'];
            }
        }

        return $alerts;
    }

    private function isRvm(RvmMachine $machine): bool
    {
        return ($machine->machine_type ?? 'rvm') === 'rvm';
    }

    private function seed(RvmMachine $machine, string $key): int
    {
        return crc32("{$machine->id}:{$key}");
    }

    private function lastEmptied(RvmMachine $machine, Carbon $now): Carbon
    {
        $minute = $this->seed($machine, 'empty') % 1440;
        $last = $now->copy()->startOfDay()->addMinutes($minute);

        return $last->gt($now) ? $last->subDay() : $last;
    }
}
