<?php

namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SessionSummaryReceiptTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email, string $phone): User
    {
        return User::create([
            'name' => 'U ' . $email, 'email' => $email, 'phone' => $phone,
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    public function test_summary_lists_each_item_with_its_id_and_time(): void
    {
        $user = $this->makeUser('a@test.local', '0100000001');
        $machine = RvmMachine::create(['machine_code' => 'RVM-R1', 'name' => 'M', 'location_name' => 'L', 'status' => 'active']);
        $session = RecyclingSession::create([
            'session_code' => 'SESS-R1', 'user_id' => $user->id, 'machine_id' => $machine->id,
            'status' => 'completed', 'start_points' => 0,
        ]);
        $tx = fn (string $m, int $valid) => Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => $m, 'ai_detected_type' => $m, 'is_valid' => $valid,
            'weight_grams' => 10, 'points_earned' => $valid ? 5 : 0, 'points_deducted' => $valid ? 0 : 2,
        ]);
        $t1 = $tx('plastic', 1);
        $t2 = $tx('glass', 0);

        Sanctum::actingAs($user, ['*']);
        $res = $this->getJson('/api/sessions/SESS-R1/summary')->assertOk();
        $res->assertJsonPath('summary.transactions.0.id', $t1->id);
        $res->assertJsonPath('summary.transactions.1.id', $t2->id);
        $this->assertNotNull($res->json('summary.transactions.0.created_at'));
        $this->assertNotNull($res->json('summary.transactions.1.created_at'));

        // Someone else's session code is not theirs to read.
        Sanctum::actingAs($this->makeUser('b@test.local', '0100000002'), ['*']);
        $this->getJson('/api/sessions/SESS-R1/summary')->assertStatus(404);
    }
}
