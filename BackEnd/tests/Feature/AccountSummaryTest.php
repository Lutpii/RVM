<?php
namespace Tests\Feature;

use App\Models\PointsHistory;
use App\Models\RecyclingSession;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'a@test.local'): User
    {
        return User::create([
            'name' => 'Alice', 'email' => $email, 'phone' => '01' . random_int(100000000, 999999999),
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 120,
        ]);
    }

    private function tx(User $u, array $o = []): Transaction
    {
        $m = RvmMachine::first() ?? RvmMachine::create(['machine_code' => 'RVM-T', 'name' => 'T', 'status' => 'active']);
        $s = RecyclingSession::create(['session_code' => 'S-' . uniqid(), 'user_id' => $u->id, 'machine_id' => $m->id, 'status' => 'completed', 'start_points' => 0]);
        return Transaction::create(array_merge([
            'session_id' => $s->id, 'user_id' => $u->id, 'machine_id' => $m->id,
            'material_selected' => 'plastic', 'ai_detected_type' => 'plastic',
            'is_valid' => 1, 'weight_grams' => 0, 'points_earned' => 15, 'points_deducted' => 0,
        ], $o));
    }

    public function test_summary_counts_items_month_points_and_open_vouchers(): void
    {
        $u = $this->makeUser();
        $other = $this->makeUser('b@test.local');
        $this->tx($u);
        $this->tx($u, ['material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum']);
        $this->tx($u, ['material_selected' => 'unknown', 'ai_detected_type' => 'unknown', 'is_valid' => 0, 'points_earned' => 0]);
        $this->tx($other);

        PointsHistory::create(['user_id' => $u->id, 'points_change' => 30, 'balance_after' => 30, 'type' => 'earned', 'description' => 'x']);
        PointsHistory::create(['user_id' => $u->id, 'points_change' => -10, 'balance_after' => 20, 'type' => 'redeemed', 'description' => 'x']);
        $old = PointsHistory::create(['user_id' => $u->id, 'points_change' => 99, 'balance_after' => 119, 'type' => 'earned', 'description' => 'last month']);
        $old->forceFill(['created_at' => now()->subMonthNoOverflow()->startOfMonth()])->save();

        $item = RewardItem::create(['name' => 'Coffee', 'points_cost' => 10, 'is_active' => true]);
        RewardRedemption::create(['user_id' => $u->id, 'reward_item_id' => $item->id, 'reward_name' => 'Coffee', 'points_spent' => 10, 'voucher_code' => 'OPEN1']);
        RewardRedemption::create(['user_id' => $u->id, 'reward_item_id' => $item->id, 'reward_name' => 'Coffee', 'points_spent' => 10, 'voucher_code' => 'USED1', 'used_at' => now()]);

        Sanctum::actingAs($u, ['*']);
        $this->getJson('/api/user/account-summary')
            ->assertOk()
            ->assertJsonPath('summary.total_points', 120)
            ->assertJsonPath('summary.items_recycled', 2)
            ->assertJsonPath('summary.earned_this_month', 30)
            ->assertJsonPath('summary.redeemed_this_month', 10)
            ->assertJsonPath('summary.active_vouchers', 1);
    }

    public function test_user_can_pick_and_clear_an_avatar_icon(): void
    {
        $u = $this->makeUser();
        Sanctum::actingAs($u, ['*']);

        $this->putJson('/api/user/profile', ['avatar_icon' => 'leaf:green'])->assertOk();
        $this->assertSame('leaf:green', $u->fresh()->avatar_icon);
        $this->getJson('/api/user/profile')->assertJsonPath('user.avatar_icon', 'leaf:green');

        $this->putJson('/api/user/profile', ['avatar_icon' => null])->assertOk();
        $this->assertNull($u->fresh()->avatar_icon);
    }

    public function test_avatar_icon_must_be_one_of_the_offered_choices(): void
    {
        $u = $this->makeUser();
        Sanctum::actingAs($u, ['*']);

        foreach (['dragon:green', 'leaf:black', 'https://evil.example/x.png', 'leaf'] as $bad) {
            $this->putJson('/api/user/profile', ['avatar_icon' => $bad])->assertStatus(422);
        }
        $this->assertNull($u->fresh()->avatar_icon);
    }
}
