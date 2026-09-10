<?php
// BackEnd/tests/Feature/WeighAwardsConfiguredPointsTest.php
namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\User;
use App\Services\RewardConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WeighAwardsConfiguredPointsTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveSession(): RecyclingSession
    {
        $user = User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(), 'name' => 'Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 0,
        ]);
        Sanctum::actingAs($user, ['*']);
        return $session;
    }

    public function test_weigh_awards_the_configured_amount_for_the_detected_material(): void
    {
        app(RewardConfigService::class)->save([
            'plastic' => 10, 'aluminum' => 12, 'glass' => 7, 'paper' => 4,
        ]);
        $session = $this->makeActiveSession();

        $this->postJson('/api/transactions/weigh', [
            'session_code' => $session->session_code,
            'ai_detected_type' => 'aluminum',
        ])
            ->assertOk()
            ->assertJsonPath('points_earned', 12);
    }

    public function test_weigh_falls_back_to_the_random_range_for_an_unconfigured_material(): void
    {
        app(RewardConfigService::class)->save([
            'plastic' => 10, 'aluminum' => 12, 'glass' => 7, 'paper' => 4,
        ]);
        $session = $this->makeActiveSession();

        $res = $this->postJson('/api/transactions/weigh', [
            'session_code' => $session->session_code,
            'ai_detected_type' => 'unknown',
        ])->assertOk();

        $this->assertGreaterThanOrEqual(15, $res->json('points_earned'));
        $this->assertLessThanOrEqual(20, $res->json('points_earned'));
    }
}
