<?php

namespace Tests\Feature;

use App\Models\PointsHistory;
use App\Models\RvmMachine;
use App\Models\User;
use App\Services\PlatformCreditService;
use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class PlatformCreditTest extends TestCase
{
    use RefreshDatabase;

    public function test_compactor_batch_uses_ultra_rvm_reward_config_per_item(): void
    {
        app(\App\Services\RewardConfigService::class)->save(['plastic' => 5, 'aluminum' => 8, 'glass' => 5, 'paper' => 3]);
        $user = $this->user();
        $machine = $this->machine('compactor');
        $at = now()->subDay()->startOfSecond();

        $batch = app(PlatformCreditService::class)->creditCompactorBatch($user, $machine, 6, 4, 3, $at);

        // 6 plastic x 5 + 4 aluminium x 8, the same per-item amounts Ultra RVM awards
        $this->assertSame(62, $batch->points_earned);
        $this->assertSame(100 + $batch->points_earned, $user->fresh()->total_points);
        $this->assertSame('0.976', $batch->carbon_kg);
        $this->assertTrue($batch->is_demo);
        $history = PointsHistory::where('user_id', $user->id)->sole();
        $this->assertSame('earned', $history->type);
        $this->assertSame('compactor', $history->source);
        $this->assertSame($batch->id, $history->source_id);
        $this->assertSame(100 + $batch->points_earned, $history->balance_after);
        $this->assertTrue($history->created_at->equalTo($at));
    }

    public function test_composter_deposit_uses_settings_and_an_up_to_date_balance(): void
    {
        Storage::fake('local');
        app(PlatformSettingsService::class)->save([
            'composter_points_per_kg' => 10,
            'composter_kg_co2_per_kg' => 0.44,
        ]);
        $user = $this->user();
        $machine = $this->machine('composter');

        $user->fresh()->increment('total_points', 10);
        $deposit = app(PlatformCreditService::class)->creditComposterDeposit($user, $machine, 2.5);

        $this->assertSame(25, $deposit->points_earned);
        $this->assertSame('1.100', $deposit->carbon_kg);
        $this->assertTrue($deposit->is_demo);
        $this->assertSame(135, $user->fresh()->total_points);
        $this->assertDatabaseHas('points_history', [
            'user_id' => $user->id,
            'source' => 'composter',
            'source_id' => $deposit->id,
            'points_change' => 25,
            'balance_after' => 135,
        ]);
    }

    public function test_wrong_machine_type_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(PlatformCreditService::class)->creditCompactorBatch($this->user(), $this->machine('composter'), 1, 1, 0);
    }

    private function user(): User
    {
        return User::create([
            'name' => 'Credit Test User',
            'email' => 'platform-credit@example.test',
            'role' => 'user',
            'is_verified' => true,
            'total_points' => 100,
        ]);
    }

    private function machine(string $type): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'CREDIT-' . $type,
            'name' => ucfirst($type),
            'machine_type' => $type,
            'status' => 'active',
            'is_demo' => true,
        ]);
    }
}
