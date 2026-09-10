<?php
// BackEnd/tests/Feature/RewardCatalogTest.php
namespace Tests\Feature;

use App\Models\RewardItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RewardCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($user, ['*']);
        return $user;
    }

    public function test_catalog_lists_active_items_and_excludes_inactive_ones(): void
    {
        $this->actingAsUser();
        RewardItem::create(['name' => 'Active Item', 'points_cost' => 10, 'is_active' => true]);
        RewardItem::create(['name' => 'Inactive Item', 'points_cost' => 10, 'is_active' => false]);

        $res = $this->getJson('/api/user/reward-items')->assertOk();
        $names = collect($res->json('reward_items'))->pluck('name');

        $this->assertTrue($names->contains('Active Item'));
        $this->assertFalse($names->contains('Inactive Item'));
    }

    public function test_catalog_excludes_items_past_their_valid_until_date(): void
    {
        $this->actingAsUser();
        RewardItem::create([
            'name' => 'Expired Item', 'points_cost' => 10, 'is_active' => true,
            'valid_until' => now()->subDay(),
        ]);

        $res = $this->getJson('/api/user/reward-items')->assertOk();
        $names = collect($res->json('reward_items'))->pluck('name');

        $this->assertFalse($names->contains('Expired Item'));
    }

    public function test_catalog_includes_a_sold_out_item_marked_unavailable_rather_than_hiding_it(): void
    {
        $this->actingAsUser();
        RewardItem::create(['name' => 'Sold Out Item', 'points_cost' => 10, 'is_active' => true, 'stock' => 0]);

        $res = $this->getJson('/api/user/reward-items')->assertOk();
        $item = collect($res->json('reward_items'))->firstWhere('name', 'Sold Out Item');

        $this->assertNotNull($item);
        $this->assertFalse($item['is_available']);
    }
}
