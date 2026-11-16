<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminPlatformSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['platform.enabled' => true]);
    }

    public function test_defaults_returned(): void
    {
        $this->actingAsRole('admin');
        $this->getJson('/api/admin/platform/settings')->assertOk()
            ->assertJsonPath('settings.composter_points_per_kg', 10)
            ->assertJsonPath('settings.composter_kg_co2_per_kg', 0.44);
    }

    public function test_update_persists_and_is_audited(): void
    {
        $admin = $this->actingAsRole('admin');
        $settings = ['composter_points_per_kg' => 12, 'composter_kg_co2_per_kg' => 0.5];

        $this->putJson('/api/admin/platform/settings', $settings)->assertOk();
        $this->getJson('/api/admin/platform/settings')->assertOk()->assertJsonPath('settings', $settings);
        $this->assertDatabaseHas('admin_logs', ['admin_id' => $admin->id, 'action' => 'update_platform_settings']);
    }

    public function test_rejects_out_of_range_values(): void
    {
        $this->actingAsRole('admin');

        foreach ([[0, 0.44], [-1, 0.44], [1001, 0.44], [10, 0], [10, 5.1]] as [$points, $carbon]) {
            $this->putJson('/api/admin/platform/settings', [
                'composter_points_per_kg' => $points,
                'composter_kg_co2_per_kg' => $carbon,
            ])->assertUnprocessable();
        }
    }

    public function test_non_admin_is_forbidden(): void
    {
        $this->actingAsRole('user');
        $this->getJson('/api/admin/platform/settings')->assertForbidden();
        $this->putJson('/api/admin/platform/settings', [])->assertForbidden();
    }

    public function test_settings_routes_return_404_when_disabled(): void
    {
        $this->actingAsRole('admin');
        config(['platform.enabled' => false]);
        $this->getJson('/api/admin/platform/settings')->assertNotFound();
        $this->putJson('/api/admin/platform/settings', [])->assertNotFound();
    }

    public function test_carbon_sources_reflect_the_admin_setting(): void
    {
        $this->actingAsRole('admin');
        $this->putJson('/api/admin/platform/settings', [
            'composter_points_per_kg' => 12,
            'composter_kg_co2_per_kg' => 0.5,
        ])->assertOk();

        $this->getJson('/api/carbon/sources')->assertOk()->assertJsonPath('sources.food_waste.kg_co2e_per_kg', 0.5);
    }

    private function actingAsRole(string $role): User
    {
        $user = User::create([
            'name' => 'Platform settings ' . $role,
            'email' => "platform-settings-{$role}@example.test",
            'role' => $role,
            'is_verified' => true,
        ]);
        Sanctum::actingAs($user);

        return $user;
    }
}
