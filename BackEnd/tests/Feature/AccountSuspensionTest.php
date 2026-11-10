<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountSuspensionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.local', 'phone' => '0100000001',
            'password_hash' => bcrypt('password'), 'role' => 'admin', 'is_verified' => 1, 'total_points' => 0,
        ]);
        $this->user = User::create([
            'name' => 'Recycler', 'email' => 'user@test.local', 'phone' => '0100000002',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    private function suspend(User $target, string $status = 'suspended')
    {
        Sanctum::actingAs($this->admin, ['*']);
        return $this->putJson("/api/admin/users/{$target->id}", ['account_status' => $status]);
    }

    public function test_new_users_are_active(): void
    {
        $this->assertSame('active', $this->user->fresh()->account_status);
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        $this->suspend($this->user)->assertOk();
        $this->app['auth']->forgetGuards();

        $this->postJson('/api/auth/login', ['email' => 'user@test.local', 'password' => 'password'])
            ->assertStatus(403)
            ->assertJsonPath('account_suspended', true)
            ->assertJsonMissingPath('token');
    }

    public function test_wrong_password_on_suspended_account_is_still_401(): void
    {
        $this->suspend($this->user)->assertOk();
        $this->app['auth']->forgetGuards();

        $this->postJson('/api/auth/login', ['email' => 'user@test.local', 'password' => 'nope'])->assertStatus(401);
    }

    public function test_suspending_revokes_existing_tokens(): void
    {
        $token = $this->user->createToken('rvm_token')->plainTextToken;
        $this->suspend($this->user)->assertOk();
        $this->assertSame(0, $this->user->tokens()->count());

        $this->app['auth']->forgetGuards();
        $this->getJson('/api/auth/me', ['Authorization' => "Bearer {$token}"])->assertStatus(401);
    }

    public function test_suspended_user_is_refused_on_protected_routes_even_with_a_session(): void
    {
        $this->user->forceFill(['account_status' => 'suspended'])->save();
        Sanctum::actingAs($this->user->fresh(), ['*']);

        $this->getJson('/api/user/profile')->assertStatus(403)->assertJsonPath('account_suspended', true);
    }

    public function test_admin_cannot_suspend_self(): void
    {
        $this->suspend($this->admin)->assertStatus(422);
        $this->assertSame('active', $this->admin->fresh()->account_status);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->suspend($this->user, 'banned')->assertStatus(422);
    }

    public function test_reactivated_user_can_log_in_again(): void
    {
        $this->suspend($this->user)->assertOk();
        $this->suspend($this->user, 'active')->assertOk();
        $this->app['auth']->forgetGuards();

        $this->postJson('/api/auth/login', ['email' => 'user@test.local', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token']);
    }

    public function test_suspension_is_in_admin_log_with_before_after(): void
    {
        $this->suspend($this->user)->assertOk();

        $log = AdminLog::where('action', 'update_user')->where('target_id', $this->user->id)->latest('id')->first();
        $this->assertNotNull($log);
        $details = json_decode($log->details, true);
        $this->assertSame(['active', 'suspended'], $details['changes']['account_status']);
    }
}
