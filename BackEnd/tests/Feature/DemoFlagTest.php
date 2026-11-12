<?php
namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DemoFlagTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function makeUser(string $name, string $role = 'user'): User
    {
        self::$seq++;
        return User::create([
            'name' => $name, 'email' => 'd' . self::$seq . '@test.local',
            'phone' => '02' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => $role, 'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    private function makeMachine(bool $demo = false): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'RVM-' . uniqid(), 'name' => 'M',
            'location_name' => 'Lobby', 'status' => 'active', 'is_demo' => $demo,
        ]);
    }

    private function makeSession(RvmMachine $m, User $u): RecyclingSession
    {
        return RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $u->id,
            'machine_id' => $m->id, 'status' => 'active', 'start_points' => 0,
        ]);
    }

    private function makeTx(RvmMachine $m, User $u): Transaction
    {
        $s = $this->makeSession($m, $u);
        return Transaction::create([
            'session_id' => $s->id, 'user_id' => $u->id, 'machine_id' => $m->id,
            'material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum',
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0,
        ]);
    }

    public function test_session_and_transaction_copy_is_demo_from_machine(): void
    {
        $u = $this->makeUser('U');
        $demo = $this->makeMachine(true);
        $real = $this->makeMachine(false);

        $tx = $this->makeTx($demo, $u);
        $this->assertTrue($tx->fresh()->is_demo);
        $this->assertTrue($tx->session->fresh()->is_demo);

        $tx2 = $this->makeTx($real, $u);
        $this->assertFalse($tx2->fresh()->is_demo);
        $this->assertFalse($tx2->session->fresh()->is_demo);
    }

    public function test_data_scope_filters(): void
    {
        $u = $this->makeUser('U');
        $this->makeTx($this->makeMachine(false), $u);
        $demo = $this->makeMachine(true);
        $this->makeTx($demo, $u);
        $this->makeTx($demo, $u);

        $this->assertSame(1, Transaction::dataScope('real')->count());
        $this->assertSame(2, Transaction::dataScope('demo')->count());
        $this->assertSame(3, Transaction::dataScope('all')->count());
        $this->assertSame(3, Transaction::dataScope('foo')->count());
        $this->assertSame(1, RecyclingSession::dataScope('real')->count());
        $this->assertSame(2, RecyclingSession::dataScope('demo')->count());
    }

    public function test_admin_can_create_and_toggle_demo_machine(): void
    {
        Sanctum::actingAs($this->makeUser('Admin', 'admin'), ['*']);

        $res = $this->postJson('/api/admin/machines', [
            'machine_code' => 'DEMO-1', 'name' => 'Demo', 'is_demo' => true,
        ])->assertStatus(201);
        $id = $res->json('machine.id');
        $this->assertDatabaseHas('rvm_machines', ['id' => $id, 'is_demo' => 1]);

        $this->putJson('/api/admin/machines/' . $id, ['is_demo' => false])->assertOk();
        $this->assertDatabaseHas('rvm_machines', ['id' => $id, 'is_demo' => 0]);

        $this->getJson('/api/admin/machines')->assertOk()->assertJsonPath('machines.0.is_demo', false);
    }

    public function test_public_machines_list_includes_is_demo(): void
    {
        $this->makeMachine(true);
        $res = $this->getJson('/api/machines')->assertOk();
        $this->assertArrayHasKey('is_demo', $res->json('machines.0'));
    }

    public function test_logged_in_reject_stores_named_reject(): void
    {
        $this->mock(AiService::class, fn ($m) => $m->shouldReceive('sort')->andReturnNull());
        $u = $this->makeUser('U');
        $s = $this->makeSession($this->makeMachine(true), $u);
        Sanctum::actingAs($u, ['*']);

        $this->postJson('/api/transactions/reject', [
            'session_code' => $s->session_code, 'material_selected' => 'reject',
            'ai_detected_type' => 'glass', 'ai_confidence' => 0.912,
        ])->assertOk();

        $this->assertDatabaseHas('transactions', [
            'session_id' => $s->id, 'material_selected' => 'reject', 'ai_detected_type' => 'glass',
            'is_valid' => 0, 'points_earned' => 0, 'is_demo' => 1,
        ]);
    }

    public function test_guest_complete_records_rejected_glass(): void
    {
        $guest = User::guest();
        $s = $this->makeSession($this->makeMachine(true), $guest);

        $res = $this->postJson('/api/hardware/session/complete', [
            'session_code' => $s->session_code, 'ai_detected_type' => 'glass',
            'material_selected' => 'reject', 'ai_confidence' => 0.9,
        ])->assertOk();

        $this->assertEquals(0, $res->json('carbon_saved'));
        $this->assertDatabaseHas('transactions', [
            'session_id' => $s->id, 'material_selected' => 'reject', 'ai_detected_type' => 'glass',
            'is_valid' => 0, 'points_earned' => 0, 'is_demo' => 1,
        ]);

        $res2 = $this->postJson('/api/hardware/session/complete', [
            'session_code' => $s->session_code, 'ai_detected_type' => 'plastic', 'ai_confidence' => 0.9,
        ])->assertOk();
        $this->assertGreaterThan(0, $res2->json('carbon_saved'));
        $this->assertDatabaseHas('transactions', ['session_id' => $s->id, 'ai_detected_type' => 'plastic', 'is_valid' => 1]);
    }
}
