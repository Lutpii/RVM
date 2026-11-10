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

    public function test_retry_with_same_key_returns_original_and_credits_once(): void
    {
        $first = $this->postJson('/api/transactions/complete', $this->payload(['idempotency_key' => 'k1']))->assertOk();
        // The pending item is gone now; a retry with the same key replays the first result.
        $again = $this->postJson('/api/transactions/complete', $this->payload(['idempotency_key' => 'k1']))->assertOk();

        $again->assertJsonPath('replayed', true);
        $this->assertSame($first->json('transaction_id'), $again->json('transaction_id'));
        $this->assertSame(5, $again->json('points_earned'));
        $this->assertSame(5, (int) $this->user->fresh()->total_points);
        $this->assertSame(1, Transaction::count());
        $this->assertSame(1, PointsHistory::count());
    }

    public function test_same_key_in_another_users_session_does_not_replay(): void
    {
        $this->postJson('/api/transactions/complete', $this->payload(['idempotency_key' => 'k1']))->assertOk();
        $aTx = Transaction::first();

        $userB = User::create([
            'name' => 'B', 'email' => 'b@test.local', 'phone' => '0199999999',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
        $sessionB = RecyclingSession::create([
            'session_code' => 'SESS-B-' . uniqid(), 'user_id' => $userB->id,
            'machine_id' => $this->session->machine_id, 'status' => 'active', 'start_points' => 0,
        ]);
        Cache::put("rvm:pending_txn:{$sessionB->id}", ['weight_grams' => 30, 'points_earned' => 7]);
        Sanctum::actingAs($userB, ['*']);

        $res = $this->postJson('/api/transactions/complete', [
            'session_code' => $sessionB->session_code, 'ai_detected_type' => 'aluminum', 'idempotency_key' => 'k1',
        ])->assertOk();

        $res->assertJsonMissingPath('replayed');
        $this->assertNotSame($aTx->id, $res->json('transaction_id'));
        $this->assertSame(7, (int) $userB->fresh()->total_points);
        $this->assertSame(5, (int) $this->user->fresh()->total_points);
        $this->assertSame(2, Transaction::count());
    }

    public function test_complete_without_key_behaves_as_before(): void
    {
        $this->postJson('/api/transactions/complete', $this->payload())->assertOk()->assertJsonMissingPath('replayed');
        $this->postJson('/api/transactions/complete', $this->payload())->assertStatus(400);
    }

    public function test_idempotency_key_is_length_limited(): void
    {
        $this->postJson('/api/transactions/complete', $this->payload(['idempotency_key' => str_repeat('x', 65)]))->assertStatus(422);
    }
}
