<?php

namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SessionEndBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_redemption_during_session_is_not_undone_by_end(): void
    {
        $user = User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 500,
        ]);
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-' . uniqid(), 'name' => 'M1',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 500,
        ]);
        Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum',
            'is_valid' => 1, 'weight_grams' => 5, 'points_earned' => 5, 'points_deducted' => 0,
        ]);
        // 5 pts applied per-item (505), then a 200-pt redemption from the phone.
        $user->update(['total_points' => 305]);

        Sanctum::actingAs($user, ['*']);
        $this->postJson("/api/sessions/{$session->session_code}/end")->assertOk();

        $this->assertSame(305, (int) $user->fresh()->total_points);
        $session->refresh();
        $this->assertSame(305, (int) $session->end_points);
        $this->assertSame(5, (int) $session->points_earned);
    }
}
