<?php
// BackEnd/tests/Feature/AdminRewardConfigTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminRewardConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clean up persistent reward_config.json so tests start fresh
        $path = storage_path('app/reward_config.json');
        if (file_exists($path)) {
            unlink($path);
        }
    }

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

    public function test_get_reward_config_returns_defaults_when_nothing_saved_yet(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/admin/reward-config')
            ->assertOk()
            ->assertJson(['success' => true, 'config' => [
                'plastic' => 5, 'aluminum' => 8, 'glass' => 5, 'paper' => 3,
            ]]);
    }

    public function test_update_reward_config_persists_and_is_read_back(): void
    {
        $this->actingAsAdmin();

        $this->putJson('/api/admin/reward-config', [
            'plastic' => 10, 'aluminum' => 12, 'glass' => 7, 'paper' => 4,
        ])->assertOk();

        $this->getJson('/api/admin/reward-config')
            ->assertOk()
            ->assertJson(['success' => true, 'config' => [
                'plastic' => 10, 'aluminum' => 12, 'glass' => 7, 'paper' => 4,
            ]]);
    }
}
