<?php
// BackEnd/tests/Feature/AdminRewardItemsCrudTest.php
namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\RewardItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminRewardItemsCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'admin',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($admin, ['*']);
        return $admin;
    }

    public function test_lists_all_reward_items(): void
    {
        $this->actingAsAdmin();
        RewardItem::create(['name' => 'Item A', 'points_cost' => 10, 'is_active' => true]);
        RewardItem::create(['name' => 'Item B', 'points_cost' => 20, 'is_active' => false]);

        $res = $this->getJson('/api/admin/reward-items')->assertOk();
        $this->assertCount(2, $res->json('reward_items'));
    }

    public function test_creates_a_reward_item_with_an_image_and_logs_it(): void
    {
        $this->actingAsAdmin();
        \Illuminate\Support\Facades\Storage::fake('public');

        $res = $this->postJson('/api/admin/reward-items', [
            'name' => 'Coffee Voucher', 'points_cost' => 30, 'stock' => 5, 'category' => 'Food',
            'image' => UploadedFile::fake()->image('voucher.jpg'),
        ])->assertStatus(201);

        $item = RewardItem::first();
        $this->assertEquals('Coffee Voucher', $item->name);
        $this->assertNotNull($item->image_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($item->image_path);
        $this->assertEquals(1, AdminLog::where('action', 'create_reward_item')->count());
    }

    public function test_creates_a_reward_item_without_an_image(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/admin/reward-items', ['name' => 'No Image Item', 'points_cost' => 15])
            ->assertStatus(201);

        $this->assertNull(RewardItem::first()->image_path);
    }

    public function test_updates_a_reward_item_and_logs_it(): void
    {
        $this->actingAsAdmin();
        $item = RewardItem::create(['name' => 'Old Name', 'points_cost' => 10, 'is_active' => true]);

        $this->putJson("/api/admin/reward-items/{$item->id}", [
            'name' => 'New Name', 'points_cost' => 25, 'is_active' => false,
        ])->assertOk();

        $item->refresh();
        $this->assertEquals('New Name', $item->name);
        $this->assertEquals(25, $item->points_cost);
        $this->assertFalse($item->is_active);
        $this->assertEquals(1, AdminLog::where('action', 'update_reward_item')->count());
    }

    public function test_deletes_a_reward_item_and_logs_it(): void
    {
        $this->actingAsAdmin();
        $item = RewardItem::create(['name' => 'Doomed', 'points_cost' => 10, 'is_active' => true]);

        $this->deleteJson("/api/admin/reward-items/{$item->id}")->assertOk();

        $this->assertNull(RewardItem::find($item->id));
        $this->assertEquals(1, AdminLog::where('action', 'delete_reward_item')->count());
    }

    public function test_non_admin_cannot_access_reward_items_admin_endpoints(): void
    {
        $user = User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0198765432',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/admin/reward-items')->assertStatus(403);
    }
}
