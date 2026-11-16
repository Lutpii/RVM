<?php

namespace App\Http\Controllers;

use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Services\CarbonService;
use App\Services\PlatformTelemetry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PlatformController extends Controller
{
    public function locations(PlatformTelemetry $telemetry): JsonResponse
    {
        $now = now();
        $groups = RvmMachine::query()->where('status', 'active')->whereNotNull('location_id')
            ->with('location')->orderByRaw('display_order IS NULL')->orderBy('display_order')->orderBy('id')
            // One card per station: machines with the same name at the same location
            // (an Add Machine form with several types creates exactly that).
            ->get()->groupBy(fn (RvmMachine $machine) => $machine->location_id . ':' . $machine->name);

        $locations = $groups->map(function ($group, $key) use ($telemetry, $now) {
            $location = $group->first()->location;

            return [
                'id' => $key,
                'name' => $group->first()->name,
                'location' => $location->name,
                // The station's own machine coordinates (exact pin, follows Edit Machine);
                // the shared Location row is only a fallback, since its name can repeat
                // across campuses (e.g. "Main Lobby").
                ...$this->stationCoordinates($group, $location),
                'machines' => $group->map(function (RvmMachine $machine) use ($telemetry, $now) {
                    $row = [
                        'id' => $machine->id,
                        'machine_code' => $machine->machine_code,
                        'name' => $machine->name,
                        'machine_type' => $machine->machine_type,
                        'status' => $telemetry->status($machine, $now),
                        'fill_level' => $telemetry->fillLevel($machine, $now),
                        'is_demo' => $machine->is_demo,
                        'last_seen_at' => $machine->last_seen_at,
                        'fault_active' => $machine->fault_active,
                    ];

                    if ($machine->machine_type === 'rvm') {
                        $row['bins'] = [
                            'aluminum' => (int) $machine->aluminum_level,
                            'plastic' => (int) $machine->plastic_level,
                            'paper' => (int) $machine->paper_level,
                        ];
                    }

                    return $row;
                })->values(),
            ];
        })->values();

        return response()->json(['success' => true, 'locations' => $locations]);
    }

    /** @return array{latitude: ?float, longitude: ?float} */
    private function stationCoordinates($machines, $location): array
    {
        $pinned = $machines->first(fn (RvmMachine $m) => $m->latitude !== null && $m->longitude !== null);
        $source = $pinned ?? $location;

        return [
            'latitude' => $source->latitude === null ? null : (float) $source->latitude,
            'longitude' => $source->longitude === null ? null : (float) $source->longitude,
        ];
    }

    public function stats(): JsonResponse
    {
        $stats = Cache::remember('platform:public_stats', now()->addMinutes(5), function () {
            $counts = RvmMachine::query()->selectRaw('machine_type, COUNT(*) AS total')
                ->groupBy('machine_type')->pluck('total', 'machine_type');
            $machines = [];
            foreach (RvmMachine::TYPES as $type) {
                $machines[$type] = (int) ($counts[$type] ?? 0);
            }

            $month = $this->carbonByType(now()->startOfMonth());

            return [
                'carbon_saved_kg' => round(array_sum($this->carbonByType()), 3),
                'machines' => $machines,
                'month' => [
                    'carbon_saved_kg' => round(array_sum($month), 3),
                    'carbon_by_machine_kg' => $month,
                ],
            ];
        });

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    private function carbonByType(?Carbon $since = null): array
    {
        $rvm = CarbonService::forCounts(
            Transaction::where('is_valid', true)->whereIn('material_selected', ['aluminum', 'plastic', 'paper'])
                ->whereHas('machine', fn ($machine) => $machine->rvmOnly())
                ->when($since, fn ($query) => $query->where('created_at', '>=', $since))
                ->selectRaw('material_selected, COUNT(*) AS total')->groupBy('material_selected')
                ->pluck('total', 'material_selected')
        );

        return [
            'rvm' => round($rvm, 3),
            'compactor' => (float) CompactorBatch::query()->when($since, fn ($query) => $query->where('created_at', '>=', $since))->sum('carbon_kg'),
            'composter' => (float) ComposterDeposit::query()->when($since, fn ($query) => $query->where('created_at', '>=', $since))->sum('carbon_kg'),
        ];
    }
}
