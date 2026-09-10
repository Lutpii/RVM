<?php
// BackEnd/tests/Feature/RewardHistoryTest.php
namespace Tests\Feature;

use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RewardHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_returns_only_the_authenticated_users_own_redemptions(): void
    {
        $me = User::create([
            'name' => 'Me', 'email' => 'me@test.local', 'phone' => '0111111111',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
        $someoneElse = User::create([
            'name' => 'Someone Else', 'email' => 'else@test.local', 'phone' => '0222222222',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
        $item = RewardItem::create(['name' => 'Item', 'points_cost' => 10, 'is_active' => true]);

        RewardRedemption::create(['user_id' => $me->id, 'reward_item_id' => $item->id, 'reward_name' => 'Item', 'points_spent' => 10]);
        RewardRedemption::create(['user_id' => $someoneElse->id, 'reward_item_id' => $item->id, 'reward_name' => 'Item', 'points_spent' => 10]);

        Sanctum::actingAs($me, ['*']);
        $res = $this->getJson('/api/user/redemptions')->assertOk();

        $rows = $res->json('redemptions.data');
        $this->assertCount(1, $rows);
        $this->assertEquals($me->id, $rows[0]['user_id']);
    }
}
