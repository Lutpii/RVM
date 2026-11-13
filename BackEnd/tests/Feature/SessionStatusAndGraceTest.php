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

class SessionStatusAndGraceTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function makeUser(): User
    {
        self::$seq++;
        return User::create([
            'name' => 'Grace ' . self::$seq, 'email' => 'sg' . self::$seq . '@test.local',
            'phone' => '05' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 50,
        ]);
    }

    private function makeMachine(): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'RVM-' . uniqid(), 'name' => 'Grace Machine',
            'location_name' => 'Lobby', 'status' => 'active', 'is_demo' => false,
        ]);
    }

    private function makeSession(RvmMachine $m, User $u): RecyclingSession
    {
        return RecyclingSession::create([
            'session_code' => 'RVM-G' . strtoupper(uniqid()), 'user_id' => $u->id, 'machine_id' => $m->id,
            'status' => 'active', 'start_points' => 50, 'end_points' => 50,
            'points_earned' => 0, 'total_items' => 0, 'started_at' => Carbon::now(),
        ]);
    }

    private function weighAndEnd(User $u, RecyclingSession $s): void
    {
        Sanctum::actingAs($u, ['*']);
        $this->postJson('/api/transactions/weigh', ['session_code' => $s->session_code, 'ai_detected_type' => 'plastic'])
            ->assertOk();
        $this->postJson("/api/sessions/{$s->session_code}/end")->assertOk();
    }

    public function test_status_returns_active_then_completed(): void
    {
        $u = $this->makeUser();
        $s = $this->makeSession($this->makeMachine(), $u);
        Sanctum::actingAs($u, ['*']);

        $this->getJson("/api/sessions/{$s->session_code}/status")
            ->assertOk()->assertJson(['success' => true, 'status' => 'active', 'ended_at' => null]);

        $this->postJson("/api/sessions/{$s->session_code}/end")->assertOk();

        $res = $this->getJson("/api/sessions/{$s->session_code}/status")
            ->assertOk()->assertJson(['success' => true, 'status' => 'completed']);
        $this->assertNotNull($res->json('ended_at'));
    }

    public function test_status_404_for_another_users_session(): void
    {
        $owner = $this->makeUser();
        $s = $this->makeSession($this->makeMachine(), $owner);
        Sanctum::actingAs($this->makeUser(), ['*']);

        $this->getJson("/api/sessions/{$s->session_code}/status")->assertStatus(404);
    }

    public function test_complete_within_grace_after_phone_end_credits_points(): void
    {
        $u = $this->makeUser();
        $s = $this->makeSession($this->makeMachine(), $u);
        $this->weighAndEnd($u, $s);
        $before = $u->fresh()->total_points;

        $res = $this->postJson('/api/transactions/complete', [
            'session_code' => $s->session_code, 'ai_detected_type' => 'plastic',
        ])->assertOk();

        $earned = (int) $res->json('points_earned');
        $this->assertGreaterThan(0, $earned);
        $this->assertSame($before + $earned, $u->fresh()->total_points);
        $s = $s->fresh();
        $this->assertSame('completed', $s->status);
        $this->assertSame($earned, (int) $s->points_earned);
        $this->assertSame(1, (int) $s->total_items);
        $this->assertSame($before + $earned, (int) $s->end_points);
    }

    public function test_complete_after_grace_returns_404(): void
    {
        $u = $this->makeUser();
        $s = $this->makeSession($this->makeMachine(), $u);
        $this->weighAndEnd($u, $s);

        $this->travel(RecyclingSession::IN_FLIGHT_GRACE_SECONDS + 1)->seconds();

        $this->postJson('/api/transactions/complete', [
            'session_code' => $s->session_code, 'ai_detected_type' => 'plastic',
        ])->assertStatus(404);
    }

    public function test_kiosk_token_rejected_after_grace(): void
    {
        $u = $this->makeUser();
        $m = $this->makeMachine();
        $s = $this->makeSession($m, $u);
        QrSession::create([
            'machine_id' => $m->id, 'qr_token' => 'ABC123', 'kiosk_token' => 'kiosk-token-xyz',
            'status' => 'scanned', 'scanned_by' => $u->id, 'expires_at' => Carbon::now()->addHours(2),
        ]);

        Sanctum::actingAs($u, ['*']);
        $this->postJson("/api/sessions/{$s->session_code}/end")->assertOk();

        // still valid inside the grace window
        $this->app['auth']->forgetGuards();
        $this->withHeader('X-Kiosk-Token', 'kiosk-token-xyz')
            ->getJson("/api/sessions/{$s->session_code}/status")->assertOk();

        $this->travel(RecyclingSession::IN_FLIGHT_GRACE_SECONDS + 1)->seconds();

        $this->app['auth']->forgetGuards();
        $this->withHeader('X-Kiosk-Token', 'kiosk-token-xyz')
            ->getJson("/api/sessions/{$s->session_code}/status")->assertStatus(401);
    }

    public function test_expire_stale_also_expires_the_qr_token(): void
    {
        $u = $this->makeUser();
        $m = $this->makeMachine();
        $s = $this->makeSession($m, $u);
        $s->forceFill(['updated_at' => Carbon::now()->subMinutes(30)])->saveQuietly();
        $qr = QrSession::create([
            'machine_id' => $m->id, 'qr_token' => 'DEF456', 'kiosk_token' => 'kiosk-token-abc',
            'status' => 'scanned', 'scanned_by' => $u->id, 'expires_at' => Carbon::now()->addHours(2),
        ]);

        $this->artisan('sessions:expire-stale')->assertSuccessful();

        $this->assertSame('expired', $s->fresh()->status);
        $this->assertTrue($qr->fresh()->expires_at->lte(Carbon::now()->addSeconds(RecyclingSession::IN_FLIGHT_GRACE_SECONDS)));
    }

    public function test_item_start_endpoints_reject_an_ended_session_even_within_grace(): void
    {
        $u = $this->makeUser();
        $s = $this->makeSession($this->makeMachine(), $u);
        $this->weighAndEnd($u, $s);

        $this->postJson('/api/transactions/capture-image', ['session_code' => $s->session_code])->assertStatus(404);
        $this->postJson('/api/transactions/open-lid', ['session_code' => $s->session_code])->assertStatus(404);
        $this->postJson('/api/transactions/process-conveyor', ['session_code' => $s->session_code])->assertStatus(404);

        // the item weighed before the end can still finish
        $this->postJson('/api/transactions/complete', [
            'session_code' => $s->session_code, 'ai_detected_type' => 'plastic',
        ])->assertOk();
    }

    public function test_reject_within_grace_refreshes_session_totals(): void
    {
        $u = $this->makeUser();
        $s = $this->makeSession($this->makeMachine(), $u);
        Sanctum::actingAs($u, ['*']);
        $this->postJson("/api/sessions/{$s->session_code}/end")->assertOk();

        $this->postJson('/api/transactions/reject', [
            'session_code' => $s->session_code, 'material_selected' => 'reject', 'ai_detected_type' => 'unknown',
        ])->assertOk();

        $s = $s->fresh();
        $this->assertSame('completed', $s->status);
        $this->assertSame(0, (int) $s->points_earned);
        $this->assertSame(0, (int) $s->total_items);
        $this->assertSame(50, (int) $s->end_points);
        $this->assertSame(1, $s->transactions()->count());
    }

    public function test_replayed_idempotency_key_within_grace_credits_nothing(): void
    {
        $u = $this->makeUser();
        $s = $this->makeSession($this->makeMachine(), $u);
        $this->weighAndEnd($u, $s);
        $body = ['session_code' => $s->session_code, 'ai_detected_type' => 'plastic', 'idempotency_key' => 'k-1'];

        $this->postJson('/api/transactions/complete', $body)->assertOk();
        $after = $u->fresh()->total_points;

        $this->postJson('/api/transactions/complete', $body)->assertOk()->assertJson(['replayed' => true]);
        $this->assertSame($after, $u->fresh()->total_points);
        $this->assertSame(1, $s->transactions()->count());
    }

    public function test_end_kiosk_token_leaves_other_users_and_machines_untouched(): void
    {
        $u = $this->makeUser();
        $other = $this->makeUser();
        $m = $this->makeMachine();
        $m2 = $this->makeMachine();
        $s = $this->makeSession($m, $u);
        $far = Carbon::now()->addHours(2);
        $mk = fn ($machine, $user, $tok) => QrSession::create([
            'machine_id' => $machine->id, 'qr_token' => strtoupper(substr(uniqid(), -6)), 'kiosk_token' => $tok,
            'status' => 'scanned', 'scanned_by' => $user->id, 'expires_at' => $far,
        ]);
        $mine = $mk($m, $u, 'tok-mine');
        $otherUser = $mk($m, $other, 'tok-other-user');
        $otherMachine = $mk($m2, $u, 'tok-other-machine');

        Sanctum::actingAs($u, ['*']);
        $this->postJson("/api/sessions/{$s->session_code}/end")->assertOk();

        $this->assertTrue($mine->fresh()->expires_at->lt(Carbon::now()->addMinutes(5)));
        $this->assertTrue($otherUser->fresh()->expires_at->gt(Carbon::now()->addHour()));
        $this->assertTrue($otherMachine->fresh()->expires_at->gt(Carbon::now()->addHour()));
    }
}
