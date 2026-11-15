<?php

namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// Deleting a machine also deletes its sessions and transactions (FK cascade).
// On 13 Nov 2026 that wiped ~200 live transactions with RVM-DSME, so a machine
// with any history can no longer be deleted: set it to Inactive instead.
class MachineDeleteGuardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'guard-admin@test.local', 'phone' => '0123499100',
            'password_hash' => bcrypt('password'), 'role' => 'admin', 'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($admin, ['*']);
        return $admin;
    }

    private function machine(string $code): RvmMachine
    {
        return RvmMachine::create(['machine_code' => $code, 'name' => $code, 'location_name' => 'Lobby', 'status' => 'active']);
    }

    public function test_a_machine_with_sessions_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $machine = $this->machine('RVM-HIST');
        RecyclingSession::create([
            'session_code' => 'SESS-HIST', 'user_id' => $admin->id, 'machine_id' => $machine->id,
            'status' => 'completed', 'start_points' => 0,
        ]);

        $res = $this->withHeaders(['X-App-Locale' => 'id'])->deleteJson('/api/admin/machines/' . $machine->id);

        $res->assertStatus(409)->assertJsonPath('success', false);
        $this->assertStringContainsString('Tidak Aktif', $res->json('message'));
        $this->assertTrue(RvmMachine::whereKey($machine->id)->exists());
        $this->assertTrue(RecyclingSession::where('session_code', 'SESS-HIST')->exists());
    }

    public function test_a_machine_without_history_can_still_be_deleted(): void
    {
        $this->admin();
        $machine = $this->machine('RVM-NEW');

        $this->deleteJson('/api/admin/machines/' . $machine->id)->assertOk();

        $this->assertFalse(RvmMachine::whereKey($machine->id)->exists());
    }
}
