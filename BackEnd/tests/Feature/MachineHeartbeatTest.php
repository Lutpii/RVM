<?php

namespace Tests\Feature;

use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MachineHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    private RvmMachine $machine;
    private string $key = 'test-machine-key-0123456789abcdef';

    protected function setUp(): void
    {
        parent::setUp();
        // The AI service answers 404 on /state = "legacy" profile, a plain 200 from us.
        Http::fake(['*' => Http::response(['error' => 'not found'], 404)]);

        $this->machine = RvmMachine::create([
            'machine_code' => 'RVM-HB', 'name' => 'Heartbeat Machine',
            'location_name' => 'Lab', 'status' => 'active',
        ]);
        $this->machine->forceFill(['api_key_hash' => hash('sha256', $this->key)])->save();
    }

    private function heartbeat(array $body = ['fault' => false], ?string $key = null)
    {
        $headers = $key === null ? [] : ['X-Machine-Key' => $key];
        return $this->postJson('/api/hardware/heartbeat', $body, $headers);
    }

    public function test_heartbeat_with_valid_key_updates_last_seen(): void
    {
        $this->heartbeat(['fault' => false], $this->key)->assertOk()->assertJsonPath('success', true);
        $this->assertNotNull($this->machine->fresh()->last_seen_at);
    }

    public function test_heartbeat_without_or_with_wrong_key_is_401_even_when_not_enforced(): void
    {
        config(['rvm.machine_auth_enforce' => false]);
        $this->heartbeat(['fault' => false])->assertStatus(401);
        $this->heartbeat(['fault' => false], 'wrong-key')->assertStatus(401);
        $this->assertNull($this->machine->fresh()->last_seen_at);
    }

    public function test_fault_transition_sets_and_clears_fault_since(): void
    {
        $this->heartbeat(['fault' => true, 'fault_reason' => 'flap_jam'], $this->key)->assertOk();
        $m = $this->machine->fresh();
        $this->assertTrue($m->fault_active);
        $this->assertSame('flap_jam', $m->fault_reason);
        $this->assertNotNull($m->fault_since);
        $firstSince = $m->fault_since->toIso8601String();

        // A repeated fault heartbeat keeps the original start time.
        $this->travel(2)->minutes();
        $this->heartbeat(['fault' => true, 'fault_reason' => 'flap_jam'], $this->key)->assertOk();
        $this->assertSame($firstSince, $this->machine->fresh()->fault_since->toIso8601String());

        $this->heartbeat(['fault' => false], $this->key)->assertOk();
        $m = $this->machine->fresh();
        $this->assertFalse($m->fault_active);
        $this->assertNull($m->fault_reason);
        $this->assertNull($m->fault_since);
    }

    public function test_heartbeat_validates_body(): void
    {
        $this->heartbeat(['fault' => 'maybe'], $this->key)->assertStatus(422);
        $this->heartbeat(['fault' => true, 'fault_reason' => str_repeat('x', 256)], $this->key)->assertStatus(422);
    }

    public function test_hardware_route_without_key_passes_when_not_enforced(): void
    {
        config(['rvm.machine_auth_enforce' => false]);
        $this->getJson('/api/hardware/state')->assertOk();
    }

    public function test_hardware_route_without_key_is_401_when_enforced(): void
    {
        config(['rvm.machine_auth_enforce' => true]);
        $this->getJson('/api/hardware/state')->assertStatus(401);
        $this->getJson('/api/hardware/state', ['X-Machine-Key' => 'wrong'])->assertStatus(401);
    }

    public function test_hardware_route_with_key_counts_as_heartbeat(): void
    {
        config(['rvm.machine_auth_enforce' => true]);
        $this->getJson('/api/hardware/state', ['X-Machine-Key' => $this->key])->assertOk();
        $this->assertNotNull($this->machine->fresh()->last_seen_at);
    }

    public function test_api_key_hash_is_never_serialised(): void
    {
        $this->getJson('/api/machines')->assertOk()->assertDontSee('api_key_hash');
        $this->getJson("/api/machines/{$this->machine->id}")->assertOk()->assertDontSee('api_key_hash');

        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.local', 'phone' => '0100000000',
            'password_hash' => bcrypt('password'), 'role' => 'admin', 'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($admin, ['*']);
        $this->getJson('/api/admin/machines')->assertOk()->assertDontSee('api_key_hash')->assertDontSee(hash('sha256', $this->key));
    }

    public function test_machine_key_command_prints_a_key_that_authenticates(): void
    {
        $this->assertSame(0, Artisan::call('rvm:machine-key', ['machine_code' => 'RVM-HB']));
        preg_match('/Key:\s*([0-9a-f]{64})/', Artisan::output(), $m);
        $this->assertNotEmpty($m[1] ?? null, 'command should print "Key: <64 hex chars>"');

        $this->heartbeat(['fault' => false], $m[1])->assertOk();
        $this->heartbeat(['fault' => false], $this->key)->assertStatus(401); // old key replaced
    }

    public function test_machine_key_command_fails_for_unknown_machine(): void
    {
        $this->assertSame(1, Artisan::call('rvm:machine-key', ['machine_code' => 'NOPE']));
    }
}
