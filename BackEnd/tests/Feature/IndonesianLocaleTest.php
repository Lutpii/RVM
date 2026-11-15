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

    public function test_validation_errors_follow_the_language(): void
    {
        $id = $this->withHeaders(['X-App-Locale' => 'id'])->postJson('/api/auth/login', [])->assertStatus(422);
        $this->assertStringContainsString('wajib diisi', $id->json('errors.password.0'));

        $my = $this->withHeaders(['X-App-Locale' => 'my'])->postJson('/api/auth/login', [])->assertStatus(422);
        $this->assertStringContainsString('diperlukan', $my->json('errors.password.0'));
    }

    // Rejected items earn 0 points and nothing is deducted (since 10 Nov).
    public function test_reject_message_never_says_points_were_deducted(): void
    {
        foreach (['en' => 'Points deducted', 'my' => 'Mata telah ditolak', 'id' => 'Poin dikurangi'] as $lang => $bad) {
            $text = (require lang_path("$lang/messages.php"))['item_rejected'];
            $this->assertStringNotContainsString($bad, $text, $lang);
            $this->assertStringContainsString('0', $text, $lang);
        }
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
