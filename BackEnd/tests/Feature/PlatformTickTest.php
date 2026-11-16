<?php

namespace Tests\Feature;

use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PlatformTickTest extends TestCase
{
    use RefreshDatabase;

    public function test_tick_catches_up_over_a_capped_window_and_does_not_duplicate(): void
    {
        config(['platform.enabled' => true]);
        $now = now()->startOfDay();
        $this->travelTo($now);
        $this->fixtures();
        Cache::put('platform:last_tick', $now->copy()->subHours(30)->toIso8601String());

        $this->artisan('platform:tick')->assertSuccessful();

        $batches = CompactorBatch::orderBy('created_at')->get();
        $deposits = ComposterDeposit::orderBy('created_at')->get();
        $this->assertGreaterThanOrEqual(3, $batches->count());
        $this->assertLessThanOrEqual(12, $batches->count());
        $this->assertGreaterThanOrEqual(2, $deposits->count());
        $this->assertLessThanOrEqual(6, $deposits->count());
        foreach ($batches->concat($deposits) as $row) {
            $this->assertTrue($row->created_at->betweenIncluded($now->copy()->subDay(), $now));
        }
        $this->assertGreaterThan(1, $batches->pluck('created_at')->unique()->count());

        // Simulate a retry after rows committed and before the cursor was recorded.
        Cache::put('platform:last_tick', $now->copy()->subHours(30)->toIso8601String());
        $this->artisan('platform:tick')->assertSuccessful();
        $this->assertDatabaseCount('compactor_batches', $batches->count());
        $this->assertDatabaseCount('composter_deposits', $deposits->count());
    }

    public function test_tick_is_a_noop_when_disabled(): void
    {
        config(['platform.enabled' => false]);
        $this->fixtures();
        Cache::put('platform:last_tick', now()->subDay()->toIso8601String());

        $this->artisan('platform:tick')->assertSuccessful();
        $this->assertDatabaseCount('compactor_batches', 0);
        $this->assertDatabaseCount('composter_deposits', 0);
    }

    private function fixtures(): void
    {
        User::create(['name' => 'Demo user', 'email' => 'tick@demo.rvm', 'role' => 'user']);
        foreach (['compactor', 'composter'] as $type) {
            RvmMachine::create(['machine_code' => 'TICK-' . $type, 'name' => $type, 'machine_type' => $type, 'is_demo' => true, 'status' => 'active']);
        }
    }
}
