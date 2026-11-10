<?php

namespace Tests\Feature;

use App\Models\PointsHistory;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// A rejected item is returned to the user with 0 points: nothing is deducted.
class TransactionRejectNoDeductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reject_records_the_item_without_touching_the_balance(): void
    {
        $user = User::create([
            'name' => 'User', 'email' => 'rej@test.local', 'phone' => '0123456781',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 50,
        ]);
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-REJ', 'name' => 'Test Machine',
            'location_name' => 'Lobby', 'status' => 'active',
        ]);
        RecyclingSession::create([
            'session_code' => 'SESS-REJ', 'user_id' => $user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 50,
        ]);
        Sanctum::actingAs($user, ['*']);

        $this->postJson('/api/transactions/reject', [
            'session_code' => 'SESS-REJ', 'material_selected' => 'plastic',
            'ai_detected_type' => 'unknown', 'ai_confidence' => 0.42,
        ])->assertOk()->assertJsonPath('points_deducted', 0)->assertJsonPath('total_points', 50);

        $this->assertSame(50, (int) $user->fresh()->total_points);
        $tx = Transaction::firstOrFail();
        $this->assertSame(0, (int) $tx->is_valid);
        $this->assertSame(0, (int) $tx->points_deducted);
        $this->assertSame(0, PointsHistory::count());
    }
}
