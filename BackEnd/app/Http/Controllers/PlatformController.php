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
            ->get()->groupBy('location_id');

        $locations = $groups->map(function ($group) use ($telemetry, $now) {
            $location = $group->first()->location;

            return [
                'id' => $location->id,
                'name' => $location->name,
                'latitude' => $location->latitude === null ? null : (float) $location->latitude,
                'longitude' => $location->longitude === null ? null : (float) $location->longitude,
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
