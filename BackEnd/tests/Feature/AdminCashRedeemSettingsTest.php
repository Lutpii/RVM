<?php
// BackEnd/tests/Feature/AdminCashRedeemSettingsTest.php
namespace Tests\Feature;

use App\Models\User;
use App\Services\CashRedeemSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCashRedeemSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Isolated testing settings file (CASH_REDEEM_SETTINGS_FILENAME in
        // phpunit.xml) — clean it up so tests start fresh, never touching
        // the real admin-configured file.
        $path = app(CashRedeemSettingsService::class)->path();
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'cashadmin@test.local', 'phone' => '0129876543',
            'password_hash' => bcrypt('password'), 'role' => 'admin',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($admin, ['*']);
        return $admin;
    }

    public function test_get_cash_redeem_settings_returns_defaults_when_nothing_saved_yet(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/admin/cash-redeem-settings')
            ->assertOk()
            ->assertJson(['success' => true, 'settings' => [
                'points_per_unit' => 100, 'rm_per_unit' => 0.10, 'min_points' => 500,
            ]]);
    }

    public function test_update_cash_redeem_settings_persists_and_is_read_back(): void
    {
        $this->actingAsAdmin();

        $this->putJson('/api/admin/cash-redeem-settings', [
            'points_per_unit' => 200, 'rm_per_unit' => 0.25, 'min_points' => 1000,
        ])->assertOk();

        $this->getJson('/api/admin/cash-redeem-settings')
            ->assertOk()
            ->assertJson(['success' => true, 'settings' => [
                'points_per_unit' => 200, 'rm_per_unit' => 0.25, 'min_points' => 1000,
            ]]);
    }

    public function test_update_cash_redeem_settings_writes_an_admin_log(): void
    {
        $admin = $this->actingAsAdmin();

        $this->putJson('/api/admin/cash-redeem-settings', [
            'points_per_unit' => 200, 'rm_per_unit' => 0.25, 'min_points' => 1000,
        ])->assertOk();

        $this->assertDatabaseHas('admin_logs', [
            'admin_id' => $admin->id, 'action' => 'update_cash_redeem_settings',
        ]);
    }
}
