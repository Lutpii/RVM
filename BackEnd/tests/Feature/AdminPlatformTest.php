<?php

namespace Tests\Feature;

use App\Models\CompactorBatch;
use App\Models\ComposterDeposit;
use App\Models\Location;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['platform.enabled' => true]);
        $this->actingAsRole('admin');
    }

    public function test_update_with_blank_location_name_and_no_location_id_clears_location(): void
    {
        $location = \App\Models\Location::create(['name' => 'UMPSA Pekan Hub']);
        $machine = $this->machine('compactor');
        $machine->update(['location_id' => $location->id, 'location_name' => 'UMPSA Pekan Hub']);

        // What the Edit Machine form now sends after "No location" (fields cleared).
        $this->putJson('/api/admin/machines/' . $machine->id, [
            'name' => 'compactor', 'location_name' => null, 'status' => 'active', 'machine_type' => 'compactor',
        ])->assertOk();

        $this->assertNull($machine->fresh()->location_id);
    }

    public function test_bin_levels_and_maintenance_reject_platform_machines(): void
    {
        $composter = $this->machine('composter');

        $this->putJson("/api/admin/machines/{$composter->id}/bin-levels", ['plastic' => 0])->assertNotFound();
        $this->postJson("/api/admin/machines/{$composter->id}/maintenance", [])->assertNotFound();
        $this->getJson("/api/admin/machines/{$composter->id}/maintenance-records")->assertNotFound();
        $this->postJson("/api/admin/machines/{$composter->id}/maintenance-records", ['type' => 'inspection'])->assertNotFound();
    }

    public function test_overview_kpis_split_by_type_and_scope(): void
    {
        $this->batch($this->machine('compactor'));
        ComposterDeposit::create([
            'machine_id' => $this->machine('composter')->id, 'weight_kg' => 2.5,
            'points_earned' => 25, 'carbon_kg' => 1.1,
        ]);
        $this->getJson('/api/admin/platform/overview?scope=all')->assertOk()
            ->assertJsonPath('kpis.points_issued', 185)
            ->assertJsonPath('kpis.carbon_saved_kg', 2.076)
            ->assertJsonPath('kpis.machines_active.compactor', 1)
            ->assertJsonPath('kpis.machines_active.composter', 1)
            ->assertJsonCount(7, 'carbon_7d')
            ->assertJsonPath('carbon_7d.6.composter', 1.1);
        $this->getJson('/api/admin/platform/overview?scope=real')->assertOk()
            ->assertJsonPath('kpis.points_issued', 0)
            ->assertJsonPath('kpis.machines_active.compactor', 0);
    }

    public function test_machine_list_and_detail_include_telemetry_and_alerts(): void
    {
        $machine = $this->machine('compactor');
        $this->batch($machine);
        $this->machine('composter');
        $list = $this->getJson('/api/admin/platform/machines?type=compactor')->assertOk()
            ->assertJsonCount(1, 'machines')
            ->assertJsonPath('machines.0.id', $machine->id);
        $this->assertEqualsCanonicalizing([
            'fill_level', 'cycles_today', 'batches_today', 'items_today', 'non_target_today',
            'last_emptied_at', 'safety_ok', 'smoke_ok', 'online',
        ], array_keys($list->json('machines.0.telemetry')));

        $this->getJson("/api/admin/platform/machines/{$machine->id}")->assertOk()
            ->assertJsonPath('machine.id', $machine->id)
            ->assertJsonPath('telemetry.non_target_today', 1)
            ->assertJsonFragment(['code' => 'non_target_paused'])
            ->assertJsonCount(1, 'recent');
    }

    public function test_activity_paginates(): void
    {
        $machine = $this->machine('compactor');
        for ($i = 0; $i < 21; $i++) {
            $this->batch($machine);
        }

        $this->getJson('/api/admin/platform/activity?type=compactor&page=2')->assertOk()
            ->assertJsonPath('activity.total', 21)->assertJsonCount(1, 'activity.data')
            ->assertJsonPath('activity.data.0.machine_name', $machine->name);
    }

    public function test_create_uses_type_only_when_enabled(): void
    {
        $location = Location::create(['name' => 'Pekan']);
        $data = ['name' => 'New machine', 'machine_type' => 'compactor', 'location_id' => $location->id];
        $created = $this->postJson('/api/admin/machines', $data + ['machine_code' => 'NEW-TYPED'])->assertCreated();
        $this->assertDatabaseHas('rvm_machines', [
            'id' => $created->json('machine.id'), 'machine_type' => 'compactor',
            'location_id' => $location->id, 'location_name' => 'Pekan', 'is_demo' => true,
        ]);

        config(['platform.enabled' => false]);
        $created = $this->postJson('/api/admin/machines', $data + ['machine_code' => 'NEW-LEGACY'])->assertCreated();
        $this->assertDatabaseHas('rvm_machines', ['id' => $created->json('machine.id'), 'machine_type' => 'rvm', 'location_id' => null]);
    }

    public function test_delete_machine_with_activity_returns_conflict(): void
    {
        $machine = $this->machine('compactor');
        $batch = $this->batch($machine);
        $this->deleteJson("/api/admin/machines/{$machine->id}")->assertConflict();
        $this->assertModelExists($batch);
        $this->assertModelExists($machine);
    }

    public function test_platform_reads_require_the_flag_and_an_admin(): void
    {
        $machine = $this->machine('compactor');
        $paths = ['/overview', '/machines?type=compactor', "/machines/{$machine->id}", '/activity?type=compactor', '/locations'];
        config(['platform.enabled' => false]);
        foreach ($paths as $path) {
            $this->getJson('/api/admin/platform' . $path)->assertNotFound();
        }
        config(['platform.enabled' => true]);
        $this->actingAsRole('user');
        foreach ($paths as $path) {
            $this->getJson('/api/admin/platform' . $path)->assertForbidden();
        }
    }

    private function actingAsRole(string $role): void
    {
        Sanctum::actingAs(User::create([
            'name' => ucfirst($role), 'email' => "admin-platform-{$role}@example.test",
            'role' => $role, 'is_verified' => true,
        ]));
    }

    private function machine(string $type): RvmMachine
    {
        return RvmMachine::create(['machine_code' => 'ADMIN-' . $type, 'name' => $type, 'machine_type' => $type, 'is_demo' => true, 'status' => 'active']);
    }

    private function batch(RvmMachine $machine): CompactorBatch
    {
        return CompactorBatch::create([
            'machine_id' => $machine->id, 'plastic_count' => 6, 'aluminum_count' => 4,
            'non_target_count' => 1, 'points_earned' => 160, 'carbon_kg' => 0.976,
        ]);
    }
}
