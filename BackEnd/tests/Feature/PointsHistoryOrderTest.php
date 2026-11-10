<?php

namespace Tests\Feature;

use App\Models\PointsHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// BUG-001 (DSME test report 2026-11-10): rows written in the same second
// must still come back newest first, so the top row carries the latest
// balance_after.
class PointsHistoryOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_rows_with_the_same_timestamp_are_ordered_newest_id_first(): void
    {
        $user = User::create([
            'name' => 'User', 'email' => 'order@test.local', 'phone' => '0123456780',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 12,
        ]);
        $sameSecond = Carbon::parse('2026-11-10 09:00:00');

        foreach ([[6, 6], [8, 14], [-2, 12]] as [$change, $balance]) {
            $row = PointsHistory::create([
                'user_id' => $user->id, 'points_change' => $change, 'balance_after' => $balance,
                'type' => $change > 0 ? 'earned' : 'deducted', 'description' => 'test',
            ]);
            $row->forceFill(['created_at' => $sameSecond])->saveQuietly();
        }

        Sanctum::actingAs($user, ['*']);
        $this->getJson('/api/user/points-history')
            ->assertOk()
            ->assertJsonPath('history.data.0.balance_after', 12)
            ->assertJsonPath('history.data.1.balance_after', 14)
            ->assertJsonPath('history.data.2.balance_after', 6);
    }
}
