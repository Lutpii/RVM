<?php
// BackEnd/tests/Feature/ExpireStaleSessionsTest.php
namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpireStaleSessionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeSession(string $emailSuffix, string $updatedAgo): RecyclingSession
    {
        $user = User::create([
            'name' => 'User', 'email' => "user{$emailSuffix}@test.local", 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(), 'name' => 'Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 0,
        ]);
        // Eloquent stamps updated_at on create — backdate it directly so the
        // command sees this row as idle without waiting real time out.
        DB::table('recycling_sessions')->where('id', $session->id)
            ->update(['updated_at' => Carbon::parse($updatedAgo)]);

        return $session->fresh();
    }

    public function test_expires_a_session_idle_for_more_than_15_minutes(): void
    {
        $session = $this->makeSession('_stale', '-20 minutes');

        Artisan::call('sessions:expire-stale');

        $session->refresh();
        $this->assertSame('expired', $session->status);
        $this->assertNotNull($session->ended_at);
    }

    public function test_leaves_a_recently_active_session_untouched(): void
    {
        $session = $this->makeSession('_fresh', '-5 minutes');

        Artisan::call('sessions:expire-stale');

        $session->refresh();
        $this->assertSame('active', $session->status);
        $this->assertNull($session->ended_at);
    }

    public function test_user_can_start_a_new_session_after_their_stale_one_expires(): void
    {
        $session = $this->makeSession('_blocked', '-20 minutes');
        Artisan::call('sessions:expire-stale');

        $user = $session->user;
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(), 'name' => 'Second Machine',
            'location_name' => 'Test Lobby 2', 'status' => 'active',
        ]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/sessions/start', [
            'machine_id' => $machine->id,
            'qr_token'   => 'test-token',
        ])->assertOk()->assertJsonPath('success', true);
    }
}
