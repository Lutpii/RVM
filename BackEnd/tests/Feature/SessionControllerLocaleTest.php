<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SessionControllerLocaleTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    public function test_session_not_found_message_is_localized(): void
    {
        Sanctum::actingAs($this->makeUser(), ['*']);

        $this->withHeaders(['X-App-Locale' => 'en'])
            ->getJson('/api/sessions/NON-EXISTENT-CODE')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Session not found.');

        $this->withHeaders(['X-App-Locale' => 'my'])
            ->getJson('/api/sessions/NON-EXISTENT-CODE')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Sesi tidak dijumpai.');
    }
}
