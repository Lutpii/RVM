<?php

namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCarbonStatsTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function makeUser(array $overrides = []): User
    {
        self::$seq++;
        return User::create(array_merge([
            'name' => 'User ' . self::$seq,
            'email' => 'user' . self::$seq . '@test.local',
            'phone' => '01' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'),
            'role' => 'user',
            'is_verified' => 1,
            'total_points' => 0,
        ], $overrides));
    }

    private function actingAsAdmin(): User
    {
        $admin = $this->makeUser(['name' => 'Admin', 'role' => 'admin']);
        Sanctum::actingAs($admin, ['*']);
        return $admin;
    }

    private function makeMachine(): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(),
            'name' => 'Test Machine',
            'location_name' => 'Test Lobby',
            'status' => 'active',
        ]);
    }

    private function makeSession(User $user, RvmMachine $machine): RecyclingSession
    {
        return RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(),
            'user_id' => $user->id,
            'machine_id' => $machine->id,
            'status' => 'active',
            'start_points' => 0,
        ]);
    }

    public function test_dashboard_stats_reports_carbon_saved_instead_of_weight(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser();
        $machine = $this->makeMachine();
        $session = $this->makeSession($user, $machine);

        // Two valid aluminum cans: 2 x 0.226 kg CO2e = 0.452 kg.
        Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum',
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0,
        ]);
        Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum',
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0,
        ]);
        // One rejected plastic item — must not count.
        Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => 'plastic', 'ai_detected_type' => 'glass',
            'is_valid' => 0, 'weight_grams' => 0, 'points_earned' => 0, 'points_deducted' => 10,
        ]);

        $response = $this->getJson('/api/admin/stats')->assertOk();

        $stats = $response->json('stats');
        $this->assertSame(0.45, $stats['total_carbon_saved_kg']);
        $this->assertArrayNotHasKey('total_weight_kg', $stats);

        $aluminumRow = collect($stats['material_stats'])->firstWhere('material_selected', 'aluminum');
        $this->assertSame(0.452, round($aluminumRow['total_carbon_kg'], 3));
        $this->assertArrayNotHasKey('total_weight', $aluminumRow);
    }
}
