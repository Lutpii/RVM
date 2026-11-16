<?php

namespace Tests\Feature;

use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MachineTypeIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['platform.enabled' => true]);
        Cache::flush();
    }

    public function test_public_machine_list_and_detail_remain_rvm_only(): void
    {
        $rvm = $this->machine('rvm');
        $compactor = $this->machine('compactor');
        $composter = $this->machine('composter');

        $this->getJson('/api/machines')->assertOk()
            ->assertJsonFragment(['id' => $rvm->id])
            ->assertJsonMissing(['id' => $compactor->id])
            ->assertJsonMissing(['id' => $composter->id]);
        $this->getJson("/api/machines/{$rvm->id}")->assertOk()
            ->assertJsonMissingPath('machine.machine_type')
            ->assertJsonMissingPath('machine.location_id');
        $this->getJson("/api/machines/{$compactor->id}")->assertNotFound();
    }

    public function test_legacy_stats_count_only_rvm_machines(): void
    {
        $count = RvmMachine::count();
        $this->machine('compactor', ['paper_level' => 100]);
        $this->machine('composter');

        $this->getJson('/api/stats/public')->assertOk()->assertJsonPath('stats.machines', $count);
        $this->admin();
        $this->getJson('/api/admin/dashboard')->assertOk()
            ->assertJsonPath('stats.total_machines', $count)
            ->assertJsonCount(0, 'stats.full_bins');
    }

    public function test_admin_list_exposes_all_types_only_when_requested_and_enabled(): void
    {
        $count = RvmMachine::count();
        $this->machine('compactor');
        $this->machine('composter');
        $this->admin();

        $this->getJson('/api/admin/machines')->assertOk()
            ->assertJsonCount($count, 'machines')
            ->assertJsonMissingPath('machines.0.machine_type');
        $this->getJson('/api/admin/machines?types=all')->assertOk()
            ->assertJsonCount($count + 2, 'machines')
            ->assertJsonFragment(['machine_type' => 'compactor'])
            ->assertJsonFragment(['machine_type' => 'composter']);
        config(['platform.enabled' => false]);
        $this->getJson('/api/admin/machines?types=all')->assertOk()->assertJsonCount($count, 'machines');
    }

    public function test_migration_links_existing_machines_to_their_location(): void
    {
        $machine = RvmMachine::where('machine_code', 'RVM-001')->firstOrFail();

        $this->assertSame('rvm', $machine->machine_type);
        $this->assertSame($machine->location_name, $machine->location->name);
        $this->assertTrue($machine->location->machines()->whereKey($machine->id)->exists());
    }

    private function machine(string $type, array $attributes = []): RvmMachine
    {
        return RvmMachine::forceCreate($attributes + [
            'machine_code' => 'ISOLATION-' . $type,
            'name' => $type,
            'machine_type' => $type,
            'status' => 'active',
        ]);
    }

    private function admin(): void
    {
        Sanctum::actingAs(User::create([
            'name' => 'Isolation Admin',
            'email' => 'isolation-admin@example.test',
            'role' => 'admin',
            'is_verified' => true,
        ]));
    }
}
