<?php
// BackEnd/tests/Feature/AdminRedemptionsListTest.php
namespace Tests\Feature;

use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminRedemptionsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_redemptions_across_all_users(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'admin', 'is_verified' => 1, 'total_points' => 0,
        ]);
        $userA = User::create([
            'name' => 'A', 'email' => 'a@test.local', 'phone' => '0111111111',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
        $userB = User::create([
            'name' => 'B', 'email' => 'b@test.local', 'phone' => '0222222222',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
        $item = RewardItem::create(['name' => 'Item', 'points_cost' => 10, 'is_active' => true]);
        RewardRedemption::create(['user_id' => $userA->id, 'reward_item_id' => $item->id, 'reward_name' => 'Item', 'points_spent' => 10]);
        RewardRedemption::create(['user_id' => $userB->id, 'reward_item_id' => $item->id, 'reward_name' => 'Item', 'points_spent' => 10]);

        Sanctum::actingAs($admin, ['*']);
        $res = $this->getJson('/api/admin/redemptions')->assertOk();

        $this->assertCount(2, $res->json('redemptions.data'));
    }
}
