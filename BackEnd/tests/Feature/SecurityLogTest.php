<?php

namespace Tests\Feature;

use App\Models\RvmMachine;
use App\Models\User;
use App\Support\SecurityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SecurityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'Recycler', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    public function test_mask_hides_most_of_an_email_or_phone(): void
    {
        $this->assertSame('us***@test.local', SecurityLog::mask('user@test.local'));
        $this->assertSame('*******789', SecurityLog::mask('0123456789'));
        $this->assertSame('a***@x.io', SecurityLog::mask('a@x.io'));
    }

    public function test_successful_login_is_logged_with_user_id(): void
    {
        Log::spy();
        $this->postJson('/api/auth/login', ['email' => 'user@test.local', 'password' => 'password'])->assertOk();

        Log::shouldHaveReceived('info')->withArgs(fn ($msg, $ctx = []) =>
            $msg === '[Auth] login_success' && ($ctx['user_id'] ?? null) === $this->user->id)->once();
    }

    public function test_failed_login_is_logged_without_password_or_full_email(): void
    {
        Log::spy();
        $this->postJson('/api/auth/login', ['email' => 'user@test.local', 'password' => 'wrong-pass'])->assertStatus(401);

        Log::shouldHaveReceived('info')->withArgs(function ($msg, $ctx = []) {
            $flat = json_encode($ctx);
            return $msg === '[Auth] login_failed'
                && ($ctx['identifier'] ?? null) === 'us***@test.local'
                && !array_key_exists('password', $ctx)
                && !str_contains($flat, 'wrong-pass')
                && !str_contains($flat, 'user@test.local');
        })->once();
    }

    public function test_lockout_and_suspended_attempts_are_logged(): void
    {
        Log::spy();
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/auth/login', ['email' => 'user@test.local', 'password' => 'nope']);
        }
        Log::shouldHaveReceived('info')->withArgs(fn ($msg) => $msg === '[Auth] login_locked')->atLeast()->once();

        \Illuminate\Support\Facades\RateLimiter::clear('login:user@test.local');
        $this->user->forceFill(['account_status' => 'suspended'])->save();
        $this->postJson('/api/auth/login', ['email' => 'user@test.local', 'password' => 'password'])->assertStatus(403);
        Log::shouldHaveReceived('info')->withArgs(fn ($msg, $ctx = []) =>
            $msg === '[Auth] login_suspended' && ($ctx['user_id'] ?? null) === $this->user->id)->once();
    }

    public function test_machine_fault_is_logged_once_per_change(): void
    {
        Http::fake();
        $key = 'fault-log-key-0123456789';
        $m = RvmMachine::create(['machine_code' => 'RVM-FL', 'name' => 'F', 'location_name' => 'L', 'status' => 'active']);
        $m->forceFill(['api_key_hash' => hash('sha256', $key)])->save();
        Log::spy();

        $hb = fn (array $b) => $this->postJson('/api/hardware/heartbeat', $b, ['X-Machine-Key' => $key])->assertOk();
        $hb(['fault' => true, 'fault_reason' => 'flap_jam']);
        $hb(['fault' => true, 'fault_reason' => 'flap_jam']);
        $hb(['fault' => false]);
        $hb(['fault' => false]);

        Log::shouldHaveReceived('warning')->withArgs(fn ($msg, $ctx = []) =>
            $msg === '[Machine] fault_raised' && ($ctx['machine_code'] ?? null) === 'RVM-FL' && ($ctx['reason'] ?? null) === 'flap_jam')->once();
        Log::shouldHaveReceived('warning')->withArgs(fn ($msg, $ctx = []) =>
            $msg === '[Machine] fault_cleared' && ($ctx['machine_code'] ?? null) === 'RVM-FL')->once();
    }
}
