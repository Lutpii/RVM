<?php
// BackEnd/tests/Feature/RewardRedemptionCashColumnsTest.php
namespace Tests\Feature;

use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardRedemptionCashColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reward_redemption_stores_cash_fields(): void
    {
        $user = User::create([
            'name' => 'Cash User', 'email' => 'cashcolumns@test.local', 'phone' => '0111222333',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);

        $redemption = RewardRedemption::create([
            'user_id' => $user->id,
            'reward_item_id' => null,
            'reward_name' => 'Cash Redemption',
            'points_spent' => 500,
            'cash_amount_rm' => 0.5,
            'ewallet_provider' => "Touch 'n Go eWallet",
            'ewallet_account' => '0123456789',
        ]);

        $fresh = $redemption->fresh();
        $this->assertSame(0.5, $fresh->cash_amount_rm);
        $this->assertSame("Touch 'n Go eWallet", $fresh->ewallet_provider);
        $this->assertSame('0123456789', $fresh->ewallet_account);
    }
}
