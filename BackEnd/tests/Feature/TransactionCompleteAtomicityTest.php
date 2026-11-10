<?php

namespace Tests\Feature;

use App\Models\PointsHistory;
use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionCompleteAtomicityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private RecyclingSession $session;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(); // the AI service sort call must not leave the test

        $this->user = User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-' . uniqid(), 'name' => 'M1',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $this->session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $this->user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 0,
        ]);
        Cache::put("rvm:pending_txn:{$this->session->id}", ['weight_grams' => 20, 'points_earned' => 5]);
        Sanctum::actingAs($this->user, ['*']);
    }

    private function payload(array $o = []): array
    {
        return array_merge([
            'session_code' => $this->session->session_code,
            'ai_detected_type' => 'plastic',
            'ai_confidence' => 0.9,
        ], $o);
    }

    public function test_unknown_material_is_rejected_before_any_write(): void
    {
        $this->postJson('/api/transactions/complete', $this->payload(['ai_detected_type' => 'banana']))
            ->assertStatus(422);

        $this->assertSame(0, Transaction::count());
        $this->assertSame(0, (int) $this->user->fresh()->total_points);
    }

    public function test_db_failure_mid_complete_leaves_no_partial_rows(): void
    {
        PointsHistory::creating(function () {
            throw new \RuntimeException('simulated failure');
        });

        $this->postJson('/api/transactions/complete', $this->payload())->assertStatus(500);

        $this->assertSame(0, Transaction::count());
        $this->assertSame(0, (int) $this->user->fresh()->total_points);
    }

    public function test_valid_complete_credits_once_and_second_call_is_400(): void
    {
        $this->postJson('/api/transactions/complete', $this->payload())
            ->assertOk()
            ->assertJsonPath('points_earned', 5);
        $this->postJson('/api/transactions/complete', $this->payload())->assertStatus(400);

        $this->assertSame(5, (int) $this->user->fresh()->total_points);
        $this->assertSame(1, PointsHistory::count());
        $this->assertSame(1, Transaction::count());
    }

    public function test_db_failure_mid_reject_leaves_no_partial_rows(): void
    {
        $this->user->update(['total_points' => 50]);
        PointsHistory::creating(function () {
            throw new \RuntimeException('simulated failure');
        });

        $this->postJson('/api/transactions/reject', $this->payload([
            'material_selected' => 'plastic', 'ai_detected_type' => 'glass',
        ]))->assertStatus(500);

        $this->assertSame(0, Transaction::count());
        $this->assertSame(50, (int) $this->user->fresh()->total_points);
    }
}
