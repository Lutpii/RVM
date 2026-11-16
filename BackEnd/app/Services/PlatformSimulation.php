<?php

namespace App\Services;

use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\Location;
use App\Models\PointsHistory;
use App\Models\RvmMachine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PlatformSimulation
{
    private const LOCK = 'platform:simulation';
    public const LAST_TICK = 'platform:last_tick';
    // The only machines generate() creates; purge deletes these and nothing else, so
    // a compactor/composter an admin added by hand survives `rvm:demo-data --purge`.
    public const GENERATED_CODES = ['RVM-PEKAN', 'CMP-PEKAN', 'CPS-PEKAN', 'CMP-DSME'];

    public function __construct(private PlatformCreditService $credits)
    {
    }

    public function generate(Carbon $start, Carbon $end, RvmMachine $dsme): array
    {
        return $this->locked(function () use ($start, $end, $dsme) {
            $pekan = Location::firstOrCreate(['name' => 'UMPSA Pekan Hub']);
            $puchong = $dsme->location ?? Location::firstOrCreate(['name' => 'DSME Puchong'], [
                'latitude' => $dsme->latitude, 'longitude' => $dsme->longitude,
            ]);
            if (!$dsme->location_id) {
                $dsme->update(['location_id' => $puchong->id]);
            }

            // Machines of one station share its name, so the home page shows one card each.
            $this->demoMachine('RVM-PEKAN', 'RVM UMPSA Pekan', 'rvm', $pekan);
            $this->demoMachine('CMP-PEKAN', 'RVM UMPSA Pekan', 'compactor', $pekan);
            $this->demoMachine('CPS-PEKAN', 'RVM UMPSA Pekan', 'composter', $pekan);
            $this->demoMachine('CMP-DSME', $dsme->name, 'compactor', $puchong);
            Cache::forget('rvm:public_stats');
            Cache::forget('platform:public_stats');

            return $this->fill($start, $end);
        });
    }

    public function tick(Carbon $now): array
    {
        return $this->locked(function () use ($now) {
            $lastTick = Cache::get(self::LAST_TICK);
            $from = $lastTick ? Carbon::parse($lastTick)->setTimezone($now->timezone) : $now->copy()->subMinutes(10);
            if ($from->lt($now->copy()->subDay())) {
                $from = $now->copy()->subDay();
            }
            if ($from->gte($now)) {
                return ['compactor_batches' => 0, 'composter_deposits' => 0];
            }

            $counts = $this->fill($from, $now);
            Cache::forever(self::LAST_TICK, $now->toIso8601String());
            if (array_sum($counts) > 0) {
                Cache::forget('platform:public_stats');
            }

            return $counts;
        });
    }

    public function locked(callable $callback): mixed
    {
        return Cache::lock(self::LOCK, 300)->block(10, $callback);
    }

    // Called inside the generator's locked purge transaction.
    public function purgeRows(): array
    {
        $earned = PointsHistory::whereIn('source', ['compactor', 'composter'])
            ->selectRaw('user_id, SUM(points_change) AS points')->groupBy('user_id')->pluck('points', 'user_id');
        foreach ($earned as $userId => $points) {
            $user = User::query()->lockForUpdate()->find($userId);
            if ($user && !DemoDataGenerator::isDemoEmail($user->email)) {
                // Preserve completed redemptions. Only remove points still available.
                $user->decrement('total_points', min((int) $points, $user->total_points));
            }
        }

        PointsHistory::whereIn('source', ['compactor', 'composter'])->delete();
        $counts = [
            'compactor_batches' => CompactorBatch::where('is_demo', true)->delete(),
            'composter_deposits' => ComposterDeposit::where('is_demo', true)->delete(),
        ];
        $machines = RvmMachine::where('is_demo', true)->whereIn('machine_code', self::GENERATED_CODES)
            ->whereDoesntHave('sessions')->whereDoesntHave('transactions');
        $locations = (clone $machines)->whereNotNull('location_id')->pluck('location_id');
        $counts['platform_machines'] = $machines->delete();
        $counts['platform_locations'] = Location::whereKey($locations->all())->whereDoesntHave('machines')->delete();

        return $counts;
    }

    private function demoMachine(string $code, string $name, string $type, Location $location): void
    {
        RvmMachine::firstOrCreate(['machine_code' => $code], [
            'name' => $name,
            'machine_type' => $type,
            'location_id' => $location->id,
            'location_name' => $location->name,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'status' => 'active',
            'is_demo' => true,
        ]);
    }

    private function fill(Carbon $from, Carbon $through): array
    {
        $email = config('platform.booth_email');
        $booth = $email ? User::where('email', $email)->where('role', 'user')->first() : null;
        $users = User::where('email', 'like', '%' . DemoDataGenerator::EMAIL_SUFFIX)->where('role', 'user')->orderBy('id')
            ->when($booth, fn ($query) => $query->whereKeyNot($booth->id))->get()->values();
        $counts = ['compactor_batches' => 0, 'composter_deposits' => 0];
        if (!$booth && $users->isEmpty()) {
            return $counts;
        }

        $machines = RvmMachine::whereIn('machine_type', ['compactor', 'composter'])
            ->where('is_demo', true)->where('status', 'active')->orderBy('id')->get();
        foreach ($machines as $machine) {
            $isCompactor = $machine->machine_type === 'compactor';
            $activity = $isCompactor ? CompactorBatch::query() : ComposterDeposit::query();
            $existing = $activity->where('machine_id', $machine->id)->whereBetween('created_at', [$from, $through])
                ->pluck('created_at')->mapWithKeys(fn ($at) => [$at->toDateTimeString() => true]);
            $key = $isCompactor ? 'compactor_batches' : 'composter_deposits';

            for ($day = $from->copy()->startOfDay(); $day->lt($through); $day->addDay()) {
                $dayKey = "{$machine->machine_code}:{$day->toDateString()}";
                $quota = $isCompactor ? 3 + crc32($dayKey) % 10 : 2 + crc32($dayKey) % 5;
                for ($slot = 0; $slot < $quota; $slot++) {
                    $at = $day->copy()->addSeconds(intdiv(($slot * 2 + 1) * 86400, $quota * 2));
                    if ($at->lte($from) || $at->gt($through) || $existing->has($at->toDateTimeString())) {
                        continue;
                    }
                    $seed = "{$dayKey}:{$slot}";
                    $user = $booth && ($users->isEmpty() || crc32("booth:{$seed}") % 5 === 0)
                        ? $booth
                        : $users[crc32("user:{$seed}") % $users->count()];

                    if ($isCompactor) {
                        $items = 20 + crc32("items:{$seed}") % 41;
                        $plastic = (int) round($items * 0.7);
                        $this->credits->creditCompactorBatch($user, $machine, $plastic, $items - $plastic, crc32("reject:{$seed}") % 4, $at);
                    } else {
                        $kg = (50 + crc32("weight:{$seed}") % 451) / 100;
                        $this->credits->creditComposterDeposit($user, $machine, $kg, $at);
                    }
                    $counts[$key]++;
                }
            }
        }

        return $counts;
    }
}
