<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndonesianLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_failure_message_is_in_indonesian(): void
    {
        User::create([
            'name' => 'User', 'email' => 'id@test.local', 'phone' => '0123456711',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);

        $this->withHeaders(['X-App-Locale' => 'id'])
            ->postJson('/api/auth/login', ['email' => 'id@test.local', 'password' => 'wrong-password'])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Email atau kata sandi salah.');
    }

    public function test_indonesian_messages_have_every_english_key(): void
    {
        $keys = function (array $a, string $prefix = '') use (&$keys): array {
            $out = [];
            foreach ($a as $k => $v) {
                $out = array_merge($out, is_array($v) ? $keys($v, "$prefix$k.") : ["$prefix$k"]);
            }
            sort($out);
            return $out;
        };

        $en = require lang_path('en/messages.php');
        $id = require lang_path('id/messages.php');

        $this->assertSame($keys($en), $keys($id));
        foreach ($id as $k => $v) {
            if (is_string($v)) $this->assertNotSame('', trim($v), $k);
        }
    }
}
