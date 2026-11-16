<?php

namespace Tests\Feature;

use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\Location;
use App\Models\RvmMachine;
use App\Models\RecyclingSession;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PlatformPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_locations_group_active_machines_in_display_order_without_item_counts(): void
    {
        config(['platform.enabled' => true]);
        RvmMachine::query()->update(['status' => 'inactive']);
        $hub = Location::create(['name' => 'Pekan Hub', 'latitude' => 3.54, 'longitude' => 103.43]);
        $campus = Location::create(['name' => 'Campus']);
        Location::create(['name' => 'Empty']);

        $compactor = $this->machine('compactor', $hub, 2);
        $rvm = $this->machine('rvm', $hub, 3);
        $this->machine('composter', $hub, 4);
        $this->machine('rvm', $campus, 1);

        $response = $this->getJson('/api/platform/locations')->assertOk()
            ->assertJsonCount(2, 'locations')
            ->assertJsonPath('locations.0.id', $campus->id)
            ->assertJsonPath('locations.1.id', $hub->id)
            ->assertJsonCount(3, 'locations.1.machines')
            ->assertJsonPath('locations.1.machines.0.id', $compactor->id)
            ->assertJsonPath('locations.1.machines.0.is_demo', true)
            ->assertJsonMissingPath('locations.1.machines.0.bins')
            ->assertJsonPath('locations.1.machines.1.id', $rvm->id)
            ->assertJsonPath('locations.1.machines.1.bins.plastic', 0);
        $this->assertNoItemKeys($response->json());
    }

    public function test_stats_show_carbon_by_machine_without_item_counts(): void
    {
        config(['platform.enabled' => true]);
        $location = Location::create(['name' => 'Test location']);
        $compactor = $this->machine('compactor', $location, 1);
        $composter = $this->machine('composter', $location, 2);
        $rvm = $this->machine('rvm', $location, 3);

        $user = User::create(['name' => 'Public Stats User', 'email' => 'platform-stats@example.test']);
        $session = RecyclingSession::create([
            'session_code' => 'PLATFORM-PUBLIC', 'user_id' => $user->id, 'machine_id' => $rvm->id, 'status' => 'completed',
        ]);
        Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $rvm->id,
            'material_selected' => 'aluminum', 'is_valid' => true,
        ]);
        CompactorBatch::create([
            'machine_id' => $compactor->id, 'plastic_count' => 15, 'aluminum_count' => 10,
            'points_earned' => 400, 'carbon_kg' => 2.44,
        ]);
        ComposterDeposit::create(['machine_id' => $composter->id, 'weight_kg' => 5, 'points_earned' => 50, 'carbon_kg' => 2.2]);
        ComposterDeposit::create([
            'machine_id' => $composter->id, 'weight_kg' => 2.5, 'points_earned' => 25, 'carbon_kg' => 1.1,
            'created_at' => now()->startOfMonth()->subDay(),
        ]);

        $response = $this->getJson('/api/platform/stats')->assertOk()
            ->assertJsonPath('stats.machines.compactor', 1)
            ->assertJsonPath('stats.machines.composter', 1)
            ->assertJsonPath('stats.carbon_saved_kg', 5.966)
            ->assertJsonPath('stats.month.carbon_saved_kg', 4.866)
            ->assertJsonPath('stats.month.carbon_by_machine_kg.rvm', 0.226)
            ->assertJsonPath('stats.month.carbon_by_machine_kg.compactor', 2.44)
            ->assertJsonPath('stats.month.carbon_by_machine_kg.composter', 2.2);
        $this->assertNoItemKeys($response->json());
    }

    public function test_both_endpoints_return_404_when_disabled(): void
    {
        config(['platform.enabled' => false]);

        $this->getJson('/api/platform/stats')->assertNotFound();
        $this->getJson('/api/platform/locations')->assertNotFound();
    }

    public function test_old_public_stats_do_not_change_when_enabled(): void
    {
        config(['platform.enabled' => false]);
        Cache::forget('rvm:public_stats');
        $before = $this->getJson('/api/stats/public')->assertOk()->json();

        config(['platform.enabled' => true]);
        $location = Location::create(['name' => 'Test']);
        $this->machine('compactor', $location, 1);
        Cache::forget('rvm:public_stats');
        $this->getJson('/api/stats/public')->assertOk()->assertExactJson($before);
    }

    private function machine(string $type, Location $location, int $order): RvmMachine
    {
        return RvmMachine::forceCreate([
            'machine_code' => 'PUBLIC-' . $order, 'name' => $type, 'location_id' => $location->id,
            'machine_type' => $type, 'display_order' => $order, 'is_demo' => $type !== 'rvm',
        ]);
    }

    private function assertNoItemKeys(array $json): void
    {
        foreach ($json as $key => $value) {
            $this->assertStringNotContainsString('items', (string) $key);
            if (is_array($value)) {
                $this->assertNoItemKeys($value);
            }
        }
    }
}
