<?php
// BackEnd/tests/Feature/GoogleCallbackAccountLinkTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleCallbackAccountLinkTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogle(string $id, string $email, string $name = 'Adi Google'): void
    {
        $googleUser = (new SocialiteUser())->map([
            'id' => $id, 'name' => $name, 'email' => $email, 'avatar' => 'https://example.com/a.png',
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('setHttpClient')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    private function hitCallback()
    {
        Cache::put('oauth_state:test-state', ['nonce' => 'test-nonce', 'frontend' => 'https://rvm.test'], now()->addMinutes(10));

        return $this->withCookie('oauth_nonce', 'test-nonce')
            ->get('/auth/google/callback?state=test-state&code=google-code');
    }

    public function test_google_login_links_an_existing_email_account_instead_of_failing(): void
    {
        $existing = User::create([
            'name' => 'Adi Email', 'email' => 'adi@example.com', 'password_hash' => bcrypt('password'),
            'role' => 'admin', 'is_verified' => 1, 'total_points' => 750,
        ]);
        $this->fakeGoogle('google-123', 'adi@example.com');

        $response = $this->hitCallback();

        $response->assertRedirectContains('https://rvm.test/#/auth/callback?code=');
        $this->assertEquals(1, User::count());
        $existing->refresh();
        $this->assertEquals('google-123', $existing->google_id);
        $this->assertEquals('admin', $existing->role);
        $this->assertEquals(750, $existing->total_points);
    }

    public function test_google_login_creates_a_new_user_when_the_email_is_unknown(): void
    {
        $this->fakeGoogle('google-456', 'new@example.com');

        $this->hitCallback()->assertRedirectContains('https://rvm.test/#/auth/callback?code=');

        $user = User::where('email', 'new@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google-456', $user->google_id);
        $this->assertEquals('user', $user->role);
    }

    public function test_returning_google_user_is_matched_by_google_id(): void
    {
        User::create([
            'name' => 'Old Name', 'email' => 'old@example.com', 'google_id' => 'google-789',
            'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
        // Same Google account, but its email changed on Google's side.
        $this->fakeGoogle('google-789', 'changed@example.com', 'New Name');

        $this->hitCallback()->assertRedirectContains('/#/auth/callback?code=');

        $this->assertEquals(1, User::count());
        $this->assertEquals('changed@example.com', User::first()->email);
    }
}
