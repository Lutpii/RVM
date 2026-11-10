<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\MaintenanceRecord;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaintenanceRecordsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private RvmMachine $machine;
    private static int $seq = 0;

    private function makeUser(string $role): User
    {
        self::$seq++;
        return User::create([
            'name' => ucfirst($role) . self::$seq, 'email' => "{$role}" . self::$seq . '@test.local',
            'phone' => '01' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => $role, 'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    private function makeMachine(string $code): RvmMachine
    {
        return RvmMachine::create(['machine_code' => $code, 'name' => "Machine {$code}", 'location_name' => 'Lab', 'status' => 'active']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->makeUser('admin');
        $this->machine = $this->makeMachine('RVM-M1');
        Sanctum::actingAs($this->admin, ['*']);
    }

    private function open(array $o = [])
    {
        return $this->postJson("/api/admin/machines/{$this->machine->id}/maintenance-records", array_merge([
            'technician_name' => 'Ali', 'issue' => 'Flap jammed on plastic side',
        ], $o));
    }

    public function test_admin_opens_and_closes_a_record(): void
    {
        $id = $this->open()->assertStatus(201)->assertJsonPath('record.status', 'open')->json('record.id');

        $this->patchJson("/api/admin/maintenance-records/{$id}/close", ['action_taken' => 'Cleared jam, tested 5 deposits'])
            ->assertOk()
            ->assertJsonPath('record.status', 'closed');

        $r = MaintenanceRecord::find($id);
        $this->assertSame('closed', $r->status);
        $this->assertSame($this->admin->id, $r->opened_by);
        $this->assertSame($this->admin->id, $r->closed_by);
        $this->assertNotNull($r->closed_at);
        $this->assertSame('Cleared jam, tested 5 deposits', $r->action_taken);
    }

    public function test_open_validates_input(): void
    {
        $this->open(['technician_name' => ''])->assertStatus(422);
        $this->open(['issue' => ''])->assertStatus(422);
        $this->open(['technician_name' => str_repeat('a', 101)])->assertStatus(422);
        $this->postJson('/api/admin/machines/999999/maintenance-records', ['technician_name' => 'A', 'issue' => 'B'])->assertStatus(404);
    }

    public function test_close_needs_action_taken_and_only_once(): void
    {
        $id = $this->open()->json('record.id');
        $this->patchJson("/api/admin/maintenance-records/{$id}/close", ['action_taken' => ''])->assertStatus(422);
        $this->patchJson("/api/admin/maintenance-records/{$id}/close", ['action_taken' => 'Fixed'])->assertOk();
        $this->patchJson("/api/admin/maintenance-records/{$id}/close", ['action_taken' => 'Again'])->assertStatus(409);
        $this->patchJson('/api/admin/maintenance-records/999999/close', ['action_taken' => 'x'])->assertStatus(404);
    }

    public function test_records_are_listed_newest_first_for_that_machine_only(): void
    {
        $other = $this->makeMachine('RVM-M2');
        $this->open(['issue' => 'first']);
        $this->travel(1)->minutes();
        $this->open(['issue' => 'second']);
        $this->postJson("/api/admin/machines/{$other->id}/maintenance-records", ['technician_name' => 'B', 'issue' => 'other machine']);

        $res = $this->getJson("/api/admin/machines/{$this->machine->id}/maintenance-records")->assertOk();
        $this->assertSame(['second', 'first'], array_column($res->json('records'), 'issue'));
        $res->assertJsonPath('records.0.opened_by_name', $this->admin->name);
    }

    public function test_recycler_gets_403_on_all_routes(): void
    {
        $id = $this->open()->json('record.id');
        Sanctum::actingAs($this->makeUser('user'), ['*']);

        $this->getJson("/api/admin/machines/{$this->machine->id}/maintenance-records")->assertStatus(403);
        $this->open()->assertStatus(403);
        $this->patchJson("/api/admin/maintenance-records/{$id}/close", ['action_taken' => 'x'])->assertStatus(403);
    }

    public function test_open_and_close_write_admin_logs(): void
    {
        $id = $this->open()->json('record.id');
        $this->patchJson("/api/admin/maintenance-records/{$id}/close", ['action_taken' => 'Fixed']);

        $this->assertSame(1, AdminLog::where('action', 'maintenance_open')->where('target_id', $this->machine->id)->count());
        $this->assertSame(1, AdminLog::where('action', 'maintenance_close')->where('target_id', $this->machine->id)->count());
    }
}
