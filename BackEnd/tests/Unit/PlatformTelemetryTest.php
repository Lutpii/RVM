<?php

namespace Tests\Unit;

use App\Models\CompactorBatch;
use App\Models\RvmMachine;
use App\Services\PlatformTelemetry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformTelemetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_input_same_output(): void
    {
        $machine = $this->machine('compactor');
        $at = Carbon::parse('2026-11-24 12:00:00');
        $telemetry = app(PlatformTelemetry::class);

        $this->assertSame($telemetry->compactor($machine, $at), $telemetry->compactor($machine, $at->copy()));
    }

    public function test_status_precedence(): void
    {
        $now = now();
        $machine = new RvmMachine(['status' => 'maintenance', 'plastic_level' => 100]);
        $machine->fault_active = true;
        $telemetry = app(PlatformTelemetry::class);

        $this->assertSame('maintenance', $telemetry->status($machine, $now));
        $machine->status = 'active';
        $this->assertSame('fault', $telemetry->status($machine, $now));
        $machine->fault_active = false;
        $machine->last_seen_at = $now->copy()->subMinutes(4);
        $this->assertSame('offline', $telemetry->status($machine, $now));
    }

    public function test_fill_thresholds(): void
    {
        $telemetry = app(PlatformTelemetry::class);
        foreach ([79 => 'available', 80 => 'almost_full', 89 => 'almost_full', 90 => 'full'] as $fill => $status) {
            $machine = new RvmMachine(['status' => 'active', 'plastic_level' => $fill, 'paper_level' => 20]);
            $this->assertSame($status, $telemetry->status($machine, now()));
        }
    }

    public function test_fill_cycles_reach_all_fill_statuses(): void
    {
        $machine = $this->machine('compactor');
        $at = Carbon::parse('2026-11-16 00:00:00');
        $telemetry = app(PlatformTelemetry::class);
        $statuses = [];
        for ($hour = 0; $hour < 168; $hour++) {
            $statuses[] = $telemetry->status($machine, $at->copy()->addHours($hour));
        }

        $this->assertEqualsCanonicalizing(['available', 'almost_full', 'full'], array_values(array_unique($statuses)));
    }

    public function test_composter_values_are_mostly_in_ideal_range(): void
    {
        $machine = $this->machine('composter');
        $at = Carbon::parse('2026-11-16 00:00:00');
        $telemetry = app(PlatformTelemetry::class);
        $idealHours = 0;
        for ($hour = 0; $hour < 168; $hour++) {
            $idealHours += (int) $telemetry->composter($machine, $at->copy()->addHours($hour))['in_ideal_range'];
        }

        $this->assertGreaterThanOrEqual(152, $idealHours);
        $this->assertLessThan(168, $idealHours);
    }

    public function test_today_counters_exclude_previous_and_future_rows(): void
    {
        $machine = $this->machine('compactor');
        $at = Carbon::parse('2026-11-16 12:00:00');
        foreach ([$at->copy()->subDay(), $at->copy()->subHour(), $at->copy()->addHour()] as $timestamp) {
            CompactorBatch::create([
                'machine_id' => $machine->id, 'plastic_count' => 20, 'aluminum_count' => 6,
                'non_target_count' => 1, 'points_earned' => 410, 'carbon_kg' => 1.596,
                'created_at' => $timestamp,
            ]);
        }

        $reading = app(PlatformTelemetry::class)->compactor($machine, $at);
        $this->assertSame(1, $reading['batches_today']);
        $this->assertSame(26, $reading['items_today']);
        $this->assertSame(1, $reading['non_target_today']);
        $this->assertSame(2, $reading['cycles_today']);
    }

    private function machine(string $type): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'TELEMETRY-' . $type, 'name' => $type,
            'machine_type' => $type, 'status' => 'active', 'is_demo' => true,
        ]);
    }
}
