<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_failure_message_is_localized(): void
    {
        User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);

        $this->withHeaders(['X-App-Locale' => 'en'])
            ->postJson('/api/auth/login', ['email' => 'user@test.local', 'password' => 'wrong-password'])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials.');

        $this->withHeaders(['X-App-Locale' => 'my'])
            ->postJson('/api/auth/login', ['email' => 'user@test.local', 'password' => 'wrong-password'])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Kelayakan tidak sah.');
    }

    public function test_logout_message_is_localized(): void
    {
        $user = User::create([
            'name' => 'User', 'email' => 'user2@test.local', 'phone' => '0123456780',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($user, ['*']);

        $this->withHeaders(['X-App-Locale' => 'my'])
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Log keluar berjaya.');
    }
}
