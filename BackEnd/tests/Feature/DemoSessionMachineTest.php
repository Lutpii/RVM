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

// The demo kiosk must never continue a visitor's session on another machine:
// simulated items would otherwise be saved as real (non-demo) data there.
class DemoSessionMachineTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function makeUser(): User
    {
        self::$seq++;
        return User::create([
            'name' => 'Visitor ' . self::$seq, 'email' => 'dsm' . self::$seq . '@test.local',
            'phone' => '03' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 120,
        ]);
    }

    private function makeMachine(bool $demo): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'RVM-' . uniqid(), 'name' => $demo ? 'Demo' : 'Real',
            'location_name' => 'Lobby', 'status' => 'active', 'is_demo' => $demo,
        ]);
    }

    private function makeActiveSession(RvmMachine $m, User $u): RecyclingSession
    {
        return RecyclingSession::create([
            'session_code' => 'RVM-OLD' . uniqid(), 'user_id' => $u->id, 'machine_id' => $m->id,
            'status' => 'active', 'start_points' => 100, 'end_points' => 100,
            'points_earned' => 0, 'total_items' => 0, 'started_at' => Carbon::now()->subHour(),
        ]);
    }

    public function test_starting_on_a_demo_machine_ends_the_session_on_another_machine(): void
    {
        $u    = $this->makeUser();
        $real = $this->makeMachine(false);
        $demo = $this->makeMachine(true);
        $old  = $this->makeActiveSession($real, $u);

        Sanctum::actingAs($u, ['*']);
        $res = $this->postJson('/api/sessions/start', ['machine_id' => $demo->id, 'qr_token' => 'ABC123']);

        $res->assertOk()->assertJson(['success' => true]);
        $newCode = $res->json('session_code');
        $this->assertNotSame($old->session_code, $newCode);

        $old->refresh();
        $this->assertSame('completed', $old->status);
        $this->assertNotNull($old->ended_at);
        $this->assertSame(120, (int) $old->end_points);

        $new = RecyclingSession::where('session_code', $newCode)->firstOrFail();
        $this->assertSame($demo->id, $new->machine_id);
        $this->assertSame('active', $new->status);
        $this->assertTrue($new->is_demo);
        $this->assertSame(1, RecyclingSession::where('user_id', $u->id)->where('status', 'active')->count());
    }

    public function test_an_active_session_on_the_same_demo_machine_is_kept(): void
    {
        $u    = $this->makeUser();
        $demo = $this->makeMachine(true);
        $old  = $this->makeActiveSession($demo, $u);

        Sanctum::actingAs($u, ['*']);
        $this->postJson('/api/sessions/start', ['machine_id' => $demo->id, 'qr_token' => 'ABC123'])
            ->assertStatus(400)
            ->assertJson(['success' => false, 'session_code' => $old->session_code]);
        $this->assertSame('active', $old->fresh()->status);
    }

    public function test_starting_on_a_real_machine_keeps_todays_behaviour(): void
    {
        $u     = $this->makeUser();
        $realA = $this->makeMachine(false);
        $realB = $this->makeMachine(false);
        $old   = $this->makeActiveSession($realA, $u);

        Sanctum::actingAs($u, ['*']);
        $this->postJson('/api/sessions/start', ['machine_id' => $realB->id, 'qr_token' => 'ABC123'])
            ->assertStatus(400)
            ->assertJson(['success' => false, 'session_code' => $old->session_code]);

        $this->assertSame('active', $old->fresh()->status);
        $this->assertSame(1, RecyclingSession::where('user_id', $u->id)->count());
    }

    private function scannedQr(RvmMachine $m, User $u): QrSession
    {
        return QrSession::create([
            'machine_id' => $m->id, 'qr_token' => strtoupper(substr(uniqid(), -6)),
            'kiosk_token' => str_repeat('k', 64), 'status' => 'scanned',
            'scanned_by' => $u->id, 'expires_at' => Carbon::now()->addHour(),
        ]);
    }

    public function test_status_for_a_demo_qr_ignores_a_session_on_another_machine(): void
    {
        $u    = $this->makeUser();
        $real = $this->makeMachine(false);
        $demo = $this->makeMachine(true);
        $this->makeActiveSession($real, $u);
        $qr = $this->scannedQr($demo, $u);

        $this->getJson('/api/qr/status/' . $qr->qr_token)
            ->assertOk()
            ->assertJson(['status' => 'scanned', 'session' => null]);
    }

    public function test_status_for_a_demo_qr_returns_the_session_on_that_demo_machine(): void
    {
        $u    = $this->makeUser();
        $demo = $this->makeMachine(true);
        $own  = $this->makeActiveSession($demo, $u);
        $qr   = $this->scannedQr($demo, $u);

        $this->getJson('/api/qr/status/' . $qr->qr_token)
            ->assertOk()
            ->assertJsonPath('session.session_code', $own->session_code)
            ->assertJsonPath('session.machine.id', $demo->id);
    }

    public function test_status_for_a_real_qr_keeps_todays_behaviour(): void
    {
        $u     = $this->makeUser();
        $realA = $this->makeMachine(false);
        $realB = $this->makeMachine(false);
        $other = $this->makeActiveSession($realA, $u);
        $qr    = $this->scannedQr($realB, $u);

        $this->getJson('/api/qr/status/' . $qr->qr_token)
            ->assertOk()
            ->assertJsonPath('session.session_code', $other->session_code);
    }
}
