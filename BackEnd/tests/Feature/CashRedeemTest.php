<?php
// BackEnd/tests/Feature/CashRedeemTest.php
namespace Tests\Feature;

use App\Models\User;
use App\Services\CashRedeemSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CashRedeemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $path = app(CashRedeemSettingsService::class)->path();
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function makeUser(array $overrides = []): User
    {
        static $seq = 0;
        $seq++;
        return User::create(array_merge([
            'name' => 'User ' . $seq, 'email' => "cashuser{$seq}@test.local", 'phone' => '0198765' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 1000,
        ], $overrides));
    }

    public function test_reward_rate_returns_defaults_when_nothing_configured(): void
    {
        $this->makeUser();
        Sanctum::actingAs(User::first(), ['*']);

        $this->getJson('/api/user/reward-rate')
            ->assertOk()
            ->assertJson(['success' => true, 'rate' => ['points' => 100, 'rm' => 0.10], 'min_points' => 500]);
    }
}
