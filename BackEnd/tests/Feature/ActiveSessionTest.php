<?php
namespace Tests\Feature;

use App\Models\PointsHistory;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CarbonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ActiveSessionTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function makeUser(): User
    {
        self::$seq++;
        return User::create([
            'name' => 'U' . self::$seq, 'email' => 'as' . self::$seq . '@test.local',
            'phone' => '02' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    private function makeMachine(bool $demo): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'RVM-' . uniqid(), 'name' => $demo ? 'Demo' : 'Real',
            'location_name' => 'Lobby', 'status' => 'active', 'is_demo' => $demo,
        ]);
    }

    private function makeSession(User $u, RvmMachine $m, string $status = 'active'): RecyclingSession
    {
        return RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $u->id, 'machine_id' => $m->id,
            'status' => $status, 'start_points' => 0, 'started_at' => now(),
        ]);
    }

    private function makeTx(RecyclingSession $s, array $o = []): Transaction
    {
        return Transaction::create(array_merge([
            'session_id' => $s->id, 'user_id' => $s->user_id, 'machine_id' => $s->machine_id,
            'material_selected' => 'plastic', 'ai_detected_type' => 'plastic',
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 10, 'points_deducted' => 0,
        ], $o));
    }

    public function test_returns_null_without_active_session(): void
    {
        Sanctum::actingAs($this->makeUser(), ['*']);
        $this->getJson('/api/user/active-session')->assertOk()
            ->assertJson(['success' => true, 'session' => null]);
    }

    public function test_returns_active_demo_session_with_items_and_carbon(): void
    {
        $u = $this->makeUser();
        $s = $this->makeSession($u, $this->makeMachine(true));
        $this->makeTx($s);
        $this->makeTx($s, ['material_selected' => 'reject', 'ai_detected_type' => 'glass',
            'is_valid' => 0, 'points_earned' => 0]);
        Sanctum::actingAs($u, ['*']);

        $res = $this->getJson('/api/user/active-session')->assertOk();
        $this->assertTrue($res->json('session.is_demo'));
        $this->assertTrue($res->json('session.machine.is_demo'));
        $this->assertSame('Lobby', $res->json('session.machine.location'));
        $this->assertCount(2, $res->json('session.items'));
        $this->assertSame('plastic', $res->json('session.items.0.material_selected'));
        $this->assertSame(0, $res->json('session.items.1.points_earned'));
        $this->assertEquals(CarbonService::forMaterial('plastic'), $res->json('session.carbon_saved'));
        $this->assertEquals(0, $res->json('session.items.1.carbon_saved'));
    }

    public function test_ended_session_is_not_returned(): void
    {
        $u = $this->makeUser();
        $this->makeSession($u, $this->makeMachine(false), 'completed');
        Sanctum::actingAs($u, ['*']);
        $this->getJson('/api/user/active-session')->assertOk()->assertJson(['session' => null]);
    }

    public function test_history_and_sessions_flag_demo(): void
    {
        $u = $this->makeUser();
        $demo = $this->makeSession($u, $this->makeMachine(true), 'completed');
        $real = $this->makeSession($u, $this->makeMachine(false), 'completed');
        foreach ([$demo, $real] as $s) {
            $t = $this->makeTx($s);
            PointsHistory::create(['user_id' => $u->id, 'transaction_id' => $t->id, 'session_id' => $s->id,
                'points_change' => 10, 'balance_after' => 10, 'type' => 'earned', 'description' => 'x']);
        }
        PointsHistory::create(['user_id' => $u->id, 'points_change' => -5, 'balance_after' => 5,
            'type' => 'redeemed', 'description' => 'no session']);
        Sanctum::actingAs($u, ['*']);

        $sessions = collect($this->getJson('/api/user/sessions')->assertOk()->json('sessions.data'))
            ->pluck('is_demo', 'id');
        $this->assertTrue($sessions[$demo->id]);
        $this->assertFalse($sessions[$real->id]);

        $rows = collect($this->getJson('/api/user/points-history')->assertOk()->json('history.data'));
        $this->assertTrue($rows->firstWhere('session_id', $demo->id)['is_demo']);
        $this->assertFalse($rows->firstWhere('session_id', $real->id)['is_demo']);
        $this->assertFalse($rows->firstWhere('session_id', null)['is_demo']);
    }
}
