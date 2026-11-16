<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\Location;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Services\CarbonService;
use App\Services\PlatformTelemetry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformAdminController extends Controller
{
    public function __construct(private PlatformTelemetry $telemetry)
    {
    }

    public function overview(Request $request): JsonResponse
    {
        $scope = $request->validate(['scope' => 'sometimes|in:all,real,demo'])['scope'] ?? 'all';
        $now = now();
        $since = $now->copy()->startOfDay()->subDays(6);
        $machines = RvmMachine::query()->when($scope !== 'all', fn ($query) => $query->where('is_demo', $scope === 'demo'))->get();

        $active = $totals = array_fill_keys(RvmMachine::TYPES, 0);
        $alerts = [];
        foreach ($machines as $machine) {
            $type = $machine->machine_type;
            $totals[$type]++;
            if ($machine->status === 'active') {
                $active[$type]++;
            }
            foreach ($this->telemetry->alerts($machine, $now) as $alert) {
                $alerts[] = ['machine_id' => $machine->id, 'machine_name' => $machine->name, 'machine_type' => $type] + $alert;
            }
        }

        $rvm = Transaction::dataScope($scope)->where('is_valid', true)
            ->whereHas('machine', fn ($query) => $query->rvmOnly());
        $carbon = (clone $rvm)->selectRaw('material_selected, COUNT(*) AS total')
            ->groupBy('material_selected')->pluck('total', 'material_selected')
            ->map(fn ($count, $material) => $count * CarbonService::forMaterial($material))->sum();
        $points = $rvm->sum('points_earned');
        $byDate = [];
        $rvmDays = $rvm->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) AS day, material_selected, COUNT(*) AS total')
            ->groupByRaw('DATE(created_at), material_selected')->get();
        foreach ($rvmDays as $row) {
            $byDate['rvm'][$row->day] = ($byDate['rvm'][$row->day] ?? 0)
                + $row->total * CarbonService::forMaterial($row->material_selected);
        }

        if ($scope !== 'real') {
            foreach (['compactor', 'composter'] as $type) {
                $activity = $this->activityQuery($type);
                $carbon += (clone $activity)->sum('carbon_kg');
                $points += (clone $activity)->sum('points_earned');
                $byDate[$type] = $activity->where('created_at', '>=', $since)
                    ->selectRaw('DATE(created_at) AS day, SUM(carbon_kg) AS total')
                    ->groupByRaw('DATE(created_at)')->pluck('total', 'day')->all();
            }
        }

        $chart = [];
        for ($offset = 0; $offset < 7; $offset++) {
            $date = $since->copy()->addDays($offset)->toDateString();
            $day = ['date' => $date];
            foreach (RvmMachine::TYPES as $type) {
                $day[$type] = round((float) ($byDate[$type][$date] ?? 0), 3);
            }
            $chart[] = $day;
        }

        return response()->json([
            'kpis' => [
                'points_issued' => (int) $points,
                'carbon_saved_kg' => round($carbon, 3),
                'machines_active' => $active,
                'machines_total' => $totals,
                'alerts' => count($alerts),
            ],
            'carbon_7d' => $chart,
            'alerts' => $alerts,
        ]);
    }

    public function locations(): JsonResponse
    {
        return response()->json(['locations' => Location::orderBy('name')->get(['id', 'name'])]);
    }

    public function machines(Request $request): JsonResponse
    {
        $type = $request->validate(['type' => 'required|in:compactor,composter'])['type'];
        $now = now();
        $machines = RvmMachine::ofType($type)->with('location:id,name')->orderBy('name')->get()
            ->map(fn (RvmMachine $machine) => $this->machineRow($machine, $now) + ['telemetry' => $this->reading($machine, $now)]);

        return response()->json(['machines' => $machines]);
    }

    public function machine(int $id): JsonResponse
    {
        $machine = RvmMachine::whereIn('machine_type', ['compactor', 'composter'])->with('location:id,name')->findOrFail($id);
        $now = now();
        $recent = $this->recentActivity($machine->machine_type)->where('machine_id', $id)->limit(10)->get()
            ->map(fn ($activity) => $this->activityRow($activity));

        return response()->json([
            'machine' => $this->machineRow($machine, $now),
            'telemetry' => $this->reading($machine, $now),
            'alerts' => $this->telemetry->alerts($machine, $now),
            'recent' => $recent,
        ]);
    }

    public function activity(Request $request): JsonResponse
    {
        $type = $request->validate(['type' => 'required|in:compactor,composter'])['type'];
        $activity = $this->recentActivity($type)->paginate(20)->appends(['type' => $type])
            ->through(fn ($row) => $this->activityRow($row));

        return response()->json(['activity' => $activity]);
    }

    private function activityQuery(string $type): Builder
    {
        return $type === 'compactor' ? CompactorBatch::query() : ComposterDeposit::query();
    }

    private function recentActivity(string $type): Builder
    {
        return $this->activityQuery($type)->with(['user:id,display_no', 'machine:id,name'])->latest()->orderByDesc('id');
    }

    private function activityRow(CompactorBatch|ComposterDeposit $activity): array
    {
        return [
            'id' => $activity->id,
            'user_display_no' => $activity->user?->display_no,
            'machine_name' => $activity->machine?->name,
            'points_earned' => (int) $activity->points_earned,
            'carbon_kg' => (float) $activity->carbon_kg,
            'is_demo' => $activity->is_demo,
            'created_at' => $activity->created_at,
        ] + ($activity instanceof CompactorBatch ? [
            'plastic_count' => (int) $activity->plastic_count,
            'aluminum_count' => (int) $activity->aluminum_count,
            'non_target_count' => (int) $activity->non_target_count,
        ] : ['weight_kg' => (float) $activity->weight_kg]);
    }

    private function machineRow(RvmMachine $machine, Carbon $now): array
    {
        return [
            'id' => $machine->id,
            'name' => $machine->name,
            'location' => $machine->location?->name ?? $machine->location_name,
            'machine_type' => $machine->machine_type,
            'status' => $this->telemetry->status($machine, $now),
            'is_demo' => $machine->is_demo,
        ];
    }

    private function reading(RvmMachine $machine, Carbon $now): array
    {
        return $machine->machine_type === 'compactor'
            ? $this->telemetry->compactor($machine, $now)
            : $this->telemetry->composter($machine, $now);
    }
}
