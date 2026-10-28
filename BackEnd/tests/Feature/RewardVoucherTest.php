<?php
// BackEnd/tests/Feature/RewardVoucherTest.php
namespace Tests\Feature;

use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RewardVoucherTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        static $seq = 0;
        $seq++;
        return User::create(array_merge([
            'name' => 'Adi Luthfi ' . $seq, 'email' => "voucher{$seq}@test.local", 'phone' => '0129876' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 1000,
        ], $overrides));
    }

    private function redeemOne(User $user): string
    {
        Sanctum::actingAs($user, ['*']);
        $item = RewardItem::create(['name' => 'GrabFood RM5', 'points_cost' => 500, 'is_active' => true]);

        return $this->postJson("/api/user/reward-items/{$item->id}/redeem")
            ->assertOk()
            ->json('redemption.voucher_code');
    }

    public function test_redeem_returns_a_unique_readable_voucher_code(): void
    {
        $user = $this->makeUser();
        $first = $this->redeemOne($user);
        $second = $this->redeemOne($user);

        $this->assertMatchesRegularExpression('/^RVM-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$/', $first);
        $this->assertNotEquals($first, $second);
        $this->assertEquals($first, RewardRedemption::first()->voucher_code);
    }

    public function test_voucher_check_is_public_and_masks_the_holder_name(): void
    {
        $code = $this->redeemOne($this->makeUser(['name' => 'Adi Luthfi']));
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/vouchers/' . strtolower($code))
            ->assertOk()
            ->assertJsonPath('voucher.code', $code)
            ->assertJsonPath('voucher.reward_name', 'GrabFood RM5')
            ->assertJsonPath('voucher.holder', 'Adi L***')
            ->assertJsonPath('voucher.status', 'valid')
            ->assertJsonMissingPath('voucher.email');
    }

    public function test_unknown_voucher_is_404(): void
    {
        $this->getJson('/api/vouchers/RVM-AAAA-BBBB')->assertNotFound();
    }

    public function test_admin_can_mark_a_voucher_used_only_once(): void
    {
        $code = $this->redeemOne($this->makeUser());
        $admin = $this->makeUser(['role' => 'admin']);
        Sanctum::actingAs($admin, ['*']);

        $this->postJson("/api/admin/vouchers/{$code}/use")
            ->assertOk()
            ->assertJsonPath('voucher.status', 'used');

        $redemption = RewardRedemption::where('voucher_code', $code)->first();
        $this->assertNotNull($redemption->used_at);
        $this->assertEquals($admin->id, $redemption->used_by);

        $this->postJson("/api/admin/vouchers/{$code}/use")
            ->assertStatus(409)
            ->assertJsonPath('voucher.status', 'used');
    }

    public function test_regular_user_cannot_mark_a_voucher_used(): void
    {
        $user = $this->makeUser();
        $code = $this->redeemOne($user);

        $this->postJson("/api/admin/vouchers/{$code}/use")->assertForbidden();
        $this->assertNull(RewardRedemption::where('voucher_code', $code)->first()->used_at);
    }
}
