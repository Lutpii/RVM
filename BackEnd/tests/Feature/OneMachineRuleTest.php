<?php
namespace Tests\Feature;

use App\Models\QrSession;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// A phone user has one active session at a time across every machine.
class OneMachineRuleTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function makeUser(): User
    {
        self::$seq++;
        return User::create([
            'name' => 'Rule ' . self::$seq, 'email' => 'omr' . self::$seq . '@test.local',
            'phone' => '04' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 50,
        ]);
    }

    private function makeMachine(bool $demo, string $name): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'RVM-' . uniqid(), 'name' => $name,
            'location_name' => 'Lobby', 'status' => 'active', 'is_demo' => $demo,
        ]);
    }

    private function makeSession(RvmMachine $m, User $u, string $status = 'active'): RecyclingSession
    {
        return RecyclingSession::create([
            'session_code' => 'RVM-OLD' . uniqid(), 'user_id' => $u->id, 'machine_id' => $m->id,
            'status' => $status, 'start_points' => 50, 'end_points' => 50,
            'points_earned' => 0, 'total_items' => 0, 'started_at' => Carbon::now()->subHour(),
        ]);
    }

    private function pendingQr(RvmMachine $m): QrSession
    {
        return QrSession::create([
            'machine_id' => $m->id, 'qr_token' => strtoupper(substr(uniqid(), -6)),
            'status' => 'pending', 'expires_at' => Carbon::now()->addMinutes(5),
        ]);
    }

    public function test_scan_rejects_when_user_has_active_session_on_another_machine(): void
    {
        $u = $this->makeUser();
        $a = $this->makeMachine(false, 'Machine A');
        $b = $this->makeMachine(false, 'Machine B');
        $old = $this->makeSession($a, $u);
        $qr = $this->pendingQr($b);

        Sanctum::actingAs($u, ['*']);
        $this->postJson('/api/qr/scan', ['token' => $qr->qr_token])
            ->assertStatus(409)
            ->assertJson([
                'success' => false, 'code' => 'active_session_elsewhere',
                'session_code' => $old->session_code, 'machine_name' => 'Machine A',
            ]);
        $this->assertSame('pending', $qr->fresh()->status);
    }

    public function test_scan_on_same_machine_with_active_session_continues(): void
    {
        $u = $this->makeUser();
        $a = $this->makeMachine(false, 'Machine A');
        $this->makeSession($a, $u);
        $qr = $this->pendingQr($a);

        Sanctum::actingAs($u, ['*']);
        $this->postJson('/api/qr/scan', ['token' => $qr->qr_token])->assertOk();
        $this->assertSame('scanned', $qr->fresh()->status);
    }

    public function test_start_rejects_active_session_elsewhere_on_a_demo_machine(): void
    {
        $u = $this->makeUser();
        $real = $this->makeMachine(false, 'Real');
        $demo = $this->makeMachine(true, 'Demo');
        $old = $this->makeSession($real, $u);

        Sanctum::actingAs($u, ['*']);
        $this->postJson('/api/sessions/start', ['machine_id' => $demo->id, 'qr_token' => 'ABC123'])
            ->assertStatus(409)
            ->assertJson(['success' => false, 'code' => 'active_session_elsewhere']);
        $this->assertSame('active', $old->fresh()->status);
    }

    public function test_start_rejects_active_session_elsewhere_on_a_real_machine(): void
    {
        $u = $this->makeUser();
        $a = $this->makeMachine(false, 'Machine A');
        $b = $this->makeMachine(false, 'Machine B');
        $old = $this->makeSession($a, $u);

        Sanctum::actingAs($u, ['*']);
        $this->postJson('/api/sessions/start', ['machine_id' => $b->id, 'qr_token' => 'ABC123'])
            ->assertStatus(409)
            ->assertJson([
                'success' => false, 'code' => 'active_session_elsewhere',
                'session_code' => $old->session_code, 'machine_name' => 'Machine A',
            ]);
        $this->assertSame(1, RecyclingSession::where('user_id', $u->id)->count());
    }

    public function test_status_for_any_qr_ignores_a_session_on_another_machine(): void
    {
        $u = $this->makeUser();
        $a = $this->makeMachine(false, 'Machine A');
        $b = $this->makeMachine(false, 'Machine B');
        $this->makeSession($a, $u);
        $qr = QrSession::create([
            'machine_id' => $b->id, 'qr_token' => strtoupper(substr(uniqid(), -6)),
            'kiosk_token' => str_repeat('k', 64), 'status' => 'scanned',
            'scanned_by' => $u->id, 'expires_at' => Carbon::now()->addHour(),
        ]);

        $this->getJson('/api/qr/status/' . $qr->qr_token)
            ->assertOk()
            ->assertJson(['status' => 'scanned', 'session' => null]);
    }

    public function test_expired_session_elsewhere_does_not_block(): void
    {
        $u = $this->makeUser();
        $a = $this->makeMachine(false, 'Machine A');
        $b = $this->makeMachine(false, 'Machine B');
        $this->makeSession($a, $u, 'expired');
        $qr = $this->pendingQr($b);

        Sanctum::actingAs($u, ['*']);
        $this->postJson('/api/qr/scan', ['token' => $qr->qr_token])->assertOk();
    }
}
