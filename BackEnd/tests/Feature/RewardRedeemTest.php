<?php
// BackEnd/tests/Feature/RewardRedeemTest.php
namespace Tests\Feature;

use App\Models\PointsHistory;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RewardRedeemTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        static $seq = 0;
        $seq++;
        return User::create(array_merge([
            'name' => 'User ' . $seq, 'email' => "user{$seq}@test.local", 'phone' => '0123456' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 100,
        ], $overrides));
    }

    public function test_redeem_succeeds_decrements_points_and_stock_and_records_history(): void
    {
        $user = $this->makeUser(['total_points' => 100]);
        Sanctum::actingAs($user, ['*']);
        $item = RewardItem::create([
            'name' => 'Coffee Voucher', 'points_cost' => 30, 'stock' => 5, 'is_active' => true,
        ]);

        $this->postJson("/api/user/reward-items/{$item->id}/redeem")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_points', 70);

        $this->assertEquals(70, $user->fresh()->total_points);
        $this->assertEquals(4, $item->fresh()->stock);

        $redemption = RewardRedemption::first();
        $this->assertNotNull($redemption);
        $this->assertEquals($user->id, $redemption->user_id);
        $this->assertEquals($item->id, $redemption->reward_item_id);
        $this->assertEquals('Coffee Voucher', $redemption->reward_name);
        $this->assertEquals(30, $redemption->points_spent);

        $history = PointsHistory::where('user_id', $user->id)->where('type', 'redeemed')->first();
        $this->assertNotNull($history);
        $this->assertEquals(-30, $history->points_change);
        $this->assertEquals(70, $history->balance_after);
    }

    public function test_redeem_with_unlimited_stock_does_not_decrement_stock(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user, ['*']);
        $item = RewardItem::create(['name' => 'Unlimited Item', 'points_cost' => 10, 'stock' => null, 'is_active' => true]);

        $this->postJson("/api/user/reward-items/{$item->id}/redeem")->assertOk();

        $this->assertNull($item->fresh()->stock);
    }

    public function test_redeem_fails_with_404_for_unknown_item(): void
    {
        $this->makeUser();
        Sanctum::actingAs(User::first(), ['*']);

        $this->postJson('/api/user/reward-items/99999/redeem')->assertStatus(404);
    }

    public function test_redeem_fails_with_422_for_insufficient_points(): void
    {
        $user = $this->makeUser(['total_points' => 5]);
        Sanctum::actingAs($user, ['*']);
        $item = RewardItem::create(['name' => 'Expensive Item', 'points_cost' => 30, 'is_active' => true]);

        $this->postJson("/api/user/reward-items/{$item->id}/redeem")->assertStatus(422);
        $this->assertEquals(5, $user->fresh()->total_points);
    }

    public function test_redeem_fails_with_422_for_an_inactive_item(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user, ['*']);
        $item = RewardItem::create(['name' => 'Disabled Item', 'points_cost' => 10, 'is_active' => false]);

        $this->postJson("/api/user/reward-items/{$item->id}/redeem")->assertStatus(422);
    }

    public function test_redeem_fails_with_422_outside_the_date_window(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user, ['*']);
        $item = RewardItem::create([
            'name' => 'Not Yet Item', 'points_cost' => 10, 'is_active' => true,
            'valid_from' => now()->addDay(),
        ]);

        $this->postJson("/api/user/reward-items/{$item->id}/redeem")->assertStatus(422);
    }

    public function test_redeem_fails_with_422_when_stock_is_already_zero(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user, ['*']);
        $item = RewardItem::create(['name' => 'Sold Out', 'points_cost' => 10, 'stock' => 0, 'is_active' => true]);

        $this->postJson("/api/user/reward-items/{$item->id}/redeem")->assertStatus(422);
    }

    /**
     * See this plan's Global Constraints for why this isn't a literal two-process
     * race: it instead proves (a) the redeem query actually acquires a row lock,
     * and (b) the boundary that lock protects — the last unit of stock — is
     * enforced correctly once reached.
     */
    public function test_redeem_query_uses_row_level_locking_and_the_last_unit_of_stock_is_protected(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user, ['*']);
        $item = RewardItem::create(['name' => 'Last Unit', 'points_cost' => 10, 'stock' => 1, 'is_active' => true]);

        DB::enableQueryLog();
        $first = $this->postJson("/api/user/reward-items/{$item->id}/redeem");
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $first->assertOk();
        $usedRowLock = collect($queries)->contains(
            fn ($q) => str_contains(strtolower($q['query']), 'for update')
        );
        $this->assertTrue($usedRowLock, 'Expected the redeem flow to run a SELECT ... FOR UPDATE query.');

        $this->assertEquals(0, $item->fresh()->stock);

        $second = $this->postJson("/api/user/reward-items/{$item->id}/redeem");
        $second->assertStatus(422);
    }

    public function test_redeem_is_blocked_when_authenticated_via_kiosk_token(): void
    {
        $user = $this->makeUser(['total_points' => 100]);
        $item = RewardItem::create(['name' => 'Coffee Voucher', 'points_cost' => 30, 'stock' => 5, 'is_active' => true]);

        $machine = \App\Models\RvmMachine::create([
            'machine_code' => 'RVM-KIOSK-' . uniqid(), 'name' => 'Kiosk Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $kioskToken = 'kiosk-' . uniqid();
        \App\Models\QrSession::create([
            'machine_id' => $machine->id, 'qr_token' => 'qr-' . uniqid(),
            'kiosk_token' => $kioskToken, 'status' => 'scanned',
            'scanned_by' => $user->id, 'expires_at' => now()->addMinutes(5),
        ]);

        $this->withHeaders(['X-Kiosk-Token' => $kioskToken])
            ->postJson("/api/user/reward-items/{$item->id}/redeem")
            ->assertStatus(403);

        $this->assertEquals(100, $user->fresh()->total_points);
        $this->assertEquals(5, $item->fresh()->stock);
        $this->assertEquals(0, RewardRedemption::count());
    }

    public function test_deleting_a_reward_item_leaves_its_redemption_history_intact(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user, ['*']);
        $item = RewardItem::create(['name' => 'Doomed Item', 'points_cost' => 10, 'is_active' => true]);

        $this->postJson("/api/user/reward-items/{$item->id}/redeem")->assertOk();
        $redemption = RewardRedemption::first();

        $item->delete();
        $redemption->refresh();

        $this->assertNull($redemption->reward_item_id);
        $this->assertEquals('Doomed Item', $redemption->reward_name);
        $this->assertEquals(10, $redemption->points_spent);
    }
}
