<?php
// BackEnd/tests/Feature/AdminCompactorSettingsTest.php
namespace Tests\Feature;

use App\Models\User;
use App\Services\AiService;
use App\Services\CompactorSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCompactorSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Isolated testing file (COMPACTOR_SETTINGS_FILENAME in phpunit.xml),
        // removed so every test starts from the defaults.
        $path = app(CompactorSettingsService::class)->path();
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function actingAsRole(string $role): User
    {
        $user = User::create([
            'name' => ucfirst($role), 'email' => "compactor-{$role}@test.local", 'phone' => '0121112233',
            'password_hash' => bcrypt('password'), 'role' => $role,
            'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($user, ['*']);
        return $user;
    }

    public function test_defaults_are_three_of_each(): void
    {
        $this->actingAsRole('admin');

        $this->getJson('/api/admin/compactor-settings')
            ->assertOk()
            ->assertExactJson(['success' => true, 'settings' => ['plastic' => 3, 'aluminum' => 3]]);
    }

    public function test_update_persists_and_is_read_back(): void
    {
        $this->actingAsRole('admin');

        $this->putJson('/api/admin/compactor-settings', ['plastic' => 4, 'aluminum' => 6])
            ->assertOk()
            ->assertJson(['success' => true, 'settings' => ['plastic' => 4, 'aluminum' => 6]]);

        $this->getJson('/api/admin/compactor-settings')
            ->assertOk()
            ->assertExactJson(['success' => true, 'settings' => ['plastic' => 4, 'aluminum' => 6]]);
    }

    public function test_values_must_be_whole_numbers_from_1_to_10(): void
    {
        $this->actingAsRole('admin');

        foreach ([[0, 3], [3, 11], ['x', 3], [2.5, 3], [null, 3]] as [$plastic, $aluminum]) {
            $this->putJson('/api/admin/compactor-settings', ['plastic' => $plastic, 'aluminum' => $aluminum])
                ->assertStatus(422);
        }
        $this->getJson('/api/admin/compactor-settings')
            ->assertJson(['settings' => ['plastic' => 3, 'aluminum' => 3]]);
    }

    public function test_only_admins_can_change_it(): void
    {
        $this->actingAsRole('user');

        $this->putJson('/api/admin/compactor-settings', ['plastic' => 5, 'aluminum' => 5])->assertStatus(403);
    }

    public function test_deposit_sends_the_admin_maximum_for_that_material(): void
    {
        app(CompactorSettingsService::class)->save(['plastic' => 5, 'aluminum' => 2]);
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('deposit')->once()->with('plastic', false, 5)->andReturn([
                'status' => 200, 'body' => ['accepted' => true, 'job_id' => 'j1', 'will_flush' => false, 'eta_seconds' => 10],
            ]);
            $mock->shouldReceive('deposit')->once()->with('aluminum', true, 2)->andReturn([
                'status' => 200, 'body' => ['accepted' => true, 'job_id' => 'j2', 'will_flush' => true, 'eta_seconds' => 34],
            ]);
        });

        $this->postJson('/api/hardware/deposit', ['material' => 'plastic', 'allow_flush' => false])->assertOk();
        $this->postJson('/api/hardware/deposit', ['material' => 'aluminum', 'allow_flush' => true])->assertOk();
    }

    public function test_state_tells_the_kiosk_the_limits(): void
    {
        app(CompactorSettingsService::class)->save(['plastic' => 4, 'aluminum' => 7]);
        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('state')->once()->andReturn([
                'status' => 200, 'body' => ['profile' => '2bin', 'chamber_material' => null, 'chamber_count' => 0],
            ]);
        });

        $this->getJson('/api/hardware/state')
            ->assertOk()
            ->assertJson(['profile' => '2bin', 'limits' => ['plastic' => 4, 'aluminum' => 7]]);
    }
}
