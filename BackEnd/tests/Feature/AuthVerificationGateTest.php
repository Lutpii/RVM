<?php
// BackEnd/tests/Feature/AuthVerificationGateTest.php
namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthVerificationGateTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(bool $verified, string $password = 'password'): User
    {
        static $seq = 0;
        $seq++;
        return User::create([
            'name' => 'User ' . $seq, 'email' => "user{$seq}@test.local", 'phone' => null,
            'password_hash' => bcrypt($password), 'role' => 'user',
            'is_verified' => $verified ? 1 : 0, 'total_points' => 0,
        ]);
    }

    public function test_register_does_not_grant_an_auth_token(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/register', [
            'name' => 'New User', 'email' => 'newuser_' . uniqid() . '@test.local',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('token');
    }

    public function test_login_with_correct_password_but_unverified_account_is_rejected(): void
    {
        Mail::fake();
        $user = $this->makeUser(verified: false);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('needs_verification', true)
            ->assertJsonMissingPath('token');
    }

    public function test_login_with_unverified_account_sends_a_fresh_otp(): void
    {
        Mail::fake();
        $user = $this->makeUser(verified: false);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password']);

        Mail::assertQueued(OtpMail::class);
        $this->assertNotNull($user->fresh()->otp_code);
    }

    public function test_login_with_verified_account_still_succeeds_and_returns_a_token(): void
    {
        $user = $this->makeUser(verified: true);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['token']);
    }

    public function test_repeated_unverified_login_attempts_do_not_spam_otp_beyond_the_resend_cap(): void
    {
        Mail::fake();
        $user = $this->makeUser(verified: false);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
                ->assertStatus(403)
                ->assertJsonPath('needs_verification', true);
        }

        // Same cap as sendOtp()'s own RateLimiter::hit($sendKey, 600) with 3 attempts.
        Mail::assertQueued(OtpMail::class, 3);
    }
}
