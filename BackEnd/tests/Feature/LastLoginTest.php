<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\Concerns\ReadsExcelDownloads;
use Tests\TestCase;

// users.last_login_at: when the account last signed in (password, OTP or
// Google). Only the time is kept, for admins to spot inactive accounts.
class LastLoginTest extends TestCase
{
    use RefreshDatabase, ReadsExcelDownloads;

    private function makeUser(array $o = []): User
    {
        return User::create(array_merge([
            'name' => 'Lia', 'email' => 'lia@test.local', 'phone' => '0123400001',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ], $o));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_new_account_has_no_last_login(): void
    {
        $this->assertNull($this->makeUser()->fresh()->last_login_at);
    }

    public function test_password_login_records_the_time(): void
    {
        $user = $this->makeUser();
        Carbon::setTestNow('2026-11-15 10:30:00');

        $this->postJson('/api/auth/login', ['email' => 'lia@test.local', 'password' => 'password'])->assertOk();

        $this->assertSame('2026-11-15 10:30:00', $user->fresh()->last_login_at->format('Y-m-d H:i:s'));
    }

    public function test_a_failed_login_does_not_change_it(): void
    {
        $user = $this->makeUser();

        $this->postJson('/api/auth/login', ['email' => 'lia@test.local', 'password' => 'wrong-password'])->assertStatus(401);

        $this->assertNull($user->fresh()->last_login_at);
    }

    public function test_google_login_records_the_time(): void
    {
        $user = $this->makeUser(['email' => 'g@test.local', 'phone' => null]);
        $googleUser = (new SocialiteUser())->map(['id' => 'g-1', 'name' => 'Lia', 'email' => 'g@test.local', 'avatar' => null]);
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('setHttpClient')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        Cache::put('oauth_state:s1', ['nonce' => 'n1', 'frontend' => 'https://rvm.test'], now()->addMinutes(10));
        Carbon::setTestNow('2026-11-15 11:00:00');

        $this->withCookie('oauth_nonce', 'n1')->get('/auth/google/callback?state=s1&code=c');

        $this->assertSame('2026-11-15 11:00:00', $user->fresh()->last_login_at->format('Y-m-d H:i:s'));
    }

    public function test_refreshing_the_token_is_not_a_new_login(): void
    {
        $user = $this->makeUser();
        $user->forceFill(['last_login_at' => '2026-11-01 08:00:00'])->save();
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/auth/refresh')->assertOk();

        $this->assertSame('2026-11-01 08:00:00', $user->fresh()->last_login_at->format('Y-m-d H:i:s'));
    }

    public function test_admin_users_list_and_export_show_it(): void
    {
        $user = $this->makeUser();
        $user->forceFill(['last_login_at' => '2026-11-14 09:15:00'])->save();
        Sanctum::actingAs($this->makeUser(['name' => 'Admin', 'email' => 'a@test.local', 'phone' => '0123400002', 'role' => 'admin']), ['*']);

        $rows = $this->getJson('/api/admin/users?search=Lia')->assertOk()->json('users.data');
        $this->assertStringStartsWith('2026-11-14', $rows[0]['last_login_at']);

        $table = $this->excelTable($this->get('/api/admin/users/export?search=Lia'));
        $col = array_search('Last login', $table[0], true);
        $this->assertNotFalse($col);
        $this->assertSame('2026-11-14 09:15', $table[1][$col]);
    }
}
