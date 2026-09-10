<?php
// BackEnd/tests/Feature/PointsHistoryRedeemedTypeTest.php
namespace Tests\Feature;

use App\Models\PointsHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsHistoryRedeemedTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_points_history_accepts_redeemed_as_a_type(): void
    {
        $user = User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);

        $entry = PointsHistory::create([
            'user_id' => $user->id,
            'points_change' => -10,
            'balance_after' => 0,
            'type' => 'redeemed',
            'description' => 'Redeemed: Test Reward',
        ]);

        $this->assertEquals('redeemed', $entry->fresh()->type);
    }
}
