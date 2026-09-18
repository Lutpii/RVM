<?php
// BackEnd/tests/Feature/CashRedeemTest.php
namespace Tests\Feature;

use App\Models\User;
use App\Services\CashRedeemSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CashRedeemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $path = app(CashRedeemSettingsService::class)->path();
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function makeUser(array $overrides = []): User
    {
        static $seq = 0;
        $seq++;
        return User::create(array_merge([
            'name' => 'User ' . $seq, 'email' => "cashuser{$seq}@test.local", 'phone' => '0198765' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 1000,
        ], $overrides));
    }

    public function test_reward_rate_returns_defaults_when_nothing_configured(): void
    {
        $this->makeUser();
        Sanctum::actingAs(User::first(), ['*']);

        $this->getJson('/api/user/reward-rate')
            ->assertOk()
            ->assertJson(['success' => true, 'rate' => ['points' => 100, 'rm' => 0.10], 'min_points' => 500]);
    }

    public function test_redeem_succeeds_and_decrements_points_and_records_history(): void
    {
        $user = $this->makeUser(['total_points' => 1000]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/user/redeem', [
            'points' => 500, 'ewallet_provider' => "Touch 'n Go eWallet", 'ewallet_account' => '0123456789',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_points', 500)
            ->assertJsonPath('cash_amount_rm', 0.5);

        $this->assertEquals(500, $user->fresh()->total_points);

        $redemption = \App\Models\RewardRedemption::first();
        $this->assertNotNull($redemption);
        $this->assertNull($redemption->reward_item_id);
        $this->assertEquals('Cash Redemption', $redemption->reward_name);
        $this->assertEquals(500, $redemption->points_spent);
        $this->assertEquals(0.5, $redemption->cash_amount_rm);
        $this->assertEquals("Touch 'n Go eWallet", $redemption->ewallet_provider);
        $this->assertEquals('0123456789', $redemption->ewallet_account);

        $history = \App\Models\PointsHistory::where('user_id', $user->id)->where('type', 'redeemed')->first();
        $this->assertNotNull($history);
        $this->assertEquals(-500, $history->points_change);
        $this->assertEquals(500, $history->balance_after);
    }

    public function test_redeem_fails_below_minimum_points(): void
    {
        $user = $this->makeUser(['total_points' => 1000]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/user/redeem', [
            'points' => 100, 'ewallet_provider' => 'GrabPay', 'ewallet_account' => '0123456789',
        ])->assertStatus(422);

        $this->assertEquals(1000, $user->fresh()->total_points);
    }

    public function test_redeem_fails_when_points_not_a_multiple_of_the_conversion_unit(): void
    {
        $user = $this->makeUser(['total_points' => 1000]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/user/redeem', [
            'points' => 550, 'ewallet_provider' => 'GrabPay', 'ewallet_account' => '0123456789',
        ])->assertStatus(422);

        $this->assertEquals(1000, $user->fresh()->total_points);
    }

    public function test_redeem_fails_with_422_for_insufficient_points(): void
    {
        $user = $this->makeUser(['total_points' => 100]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/user/redeem', [
            'points' => 500, 'ewallet_provider' => 'GrabPay', 'ewallet_account' => '0123456789',
        ])->assertStatus(422);

        $this->assertEquals(100, $user->fresh()->total_points);
    }

    public function test_redeem_fails_for_an_unsupported_ewallet_provider(): void
    {
        $user = $this->makeUser(['total_points' => 1000]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/user/redeem', [
            'points' => 500, 'ewallet_provider' => 'RandomWallet', 'ewallet_account' => '0123456789',
        ])->assertStatus(422);
    }

    public function test_redeem_is_blocked_when_authenticated_via_kiosk_token(): void
    {
        $user = $this->makeUser(['total_points' => 1000]);

        $machine = \App\Models\RvmMachine::create([
            'machine_code' => 'RVM-CASH-KIOSK-' . uniqid(), 'name' => 'Kiosk Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $kioskToken = 'kiosk-' . uniqid();
        \App\Models\QrSession::create([
            'machine_id' => $machine->id, 'qr_token' => 'qr-' . uniqid(),
            'kiosk_token' => $kioskToken, 'status' => 'scanned',
            'scanned_by' => $user->id, 'expires_at' => now()->addMinutes(5),
        ]);

        $this->withHeaders(['X-Kiosk-Token' => $kioskToken])
            ->postJson('/api/user/redeem', [
                'points' => 500, 'ewallet_provider' => 'GrabPay', 'ewallet_account' => '0123456789',
            ])
            ->assertStatus(403);

        $this->assertEquals(1000, $user->fresh()->total_points);
        $this->assertEquals(0, \App\Models\RewardRedemption::count());
    }
}
