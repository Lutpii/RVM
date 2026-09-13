<?php
// BackEnd/tests/Feature/EnforceIdleTimeoutTest.php
namespace Tests\Feature;

use App\Models\QrSession;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EnforceIdleTimeoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'name' => 'User', 'email' => 'idle' . uniqid() . '@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    public function test_a_token_idle_for_more_than_30_minutes_is_rejected_and_deleted(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;
        DB::table('personal_access_tokens')->where('tokenable_id', $user->id)
            ->update(['last_used_at' => Carbon::now()->subMinutes(31)]);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('message', __('messages.session_expired_idle'));

        $this->assertSame(0, DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->count());
    }

    public function test_a_token_used_within_30_minutes_is_accepted(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;
        DB::table('personal_access_tokens')->where('tokenable_id', $user->id)
            ->update(['last_used_at' => Carbon::now()->subMinutes(10)]);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/auth/me')
            ->assertOk();
    }

    public function test_a_never_used_token_falls_back_to_created_at(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;
        DB::table('personal_access_tokens')->where('tokenable_id', $user->id)
            ->update(['last_used_at' => null, 'created_at' => Carbon::now()->subMinutes(31)]);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/auth/me')
            ->assertStatus(401);
    }

    public function test_a_kiosk_token_request_bypasses_the_idle_check(): void
    {
        $user = $this->makeUser();
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(), 'name' => 'Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $kioskToken = 'kiosk-' . uniqid();
        QrSession::create([
            'machine_id' => $machine->id, 'qr_token' => 'qr-' . uniqid(),
            'kiosk_token' => $kioskToken, 'status' => 'scanned',
            'scanned_by' => $user->id, 'expires_at' => now()->addMinutes(5),
        ]);

        $this->withHeaders(['X-Kiosk-Token' => $kioskToken])
            ->getJson('/api/auth/me')
            ->assertOk();
    }
}
