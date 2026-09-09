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

class PointsHistoryLocaleTest extends TestCase
{
    use RefreshDatabase;

    private function makeFixture(): array
    {
        $user = User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 100,
        ]);
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(), 'name' => 'Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 0,
        ]);
        $transaction = Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => 'plastic', 'ai_detected_type' => 'plastic',
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0,
        ]);
        PointsHistory::create([
            'user_id' => $user->id, 'transaction_id' => $transaction->id, 'session_id' => $session->id,
            'points_change' => 15, 'balance_after' => 100, 'type' => 'earned',
            'description' => 'Recycled 15g of plastic',
        ]);

        Sanctum::actingAs($user, ['*']);
        return [$user, $transaction];
    }

    public function test_points_history_description_is_localized_to_malay_when_requested(): void
    {
        $this->makeFixture();

        $response = $this->withHeaders(['X-App-Locale' => 'my'])
            ->getJson('/api/user/points-history')
            ->assertOk();

        $response->assertJsonPath('history.data.0.description', 'Mengitar semula 15 g plastik');
    }

    public function test_points_history_description_defaults_to_english(): void
    {
        $this->makeFixture();

        $response = $this->getJson('/api/user/points-history')->assertOk();

        $response->assertJsonPath('history.data.0.description', 'Recycled 15 g of plastic');
    }

    public function test_points_history_description_reflects_current_locale_not_the_one_stored_at(): void
    {
        // Simulates a user who recycled while the UI was in English, then
        // switched to Malay before viewing their history — the description
        // must follow the CURRENT request's locale, not whatever was active
        // when the row was written.
        $this->makeFixture();

        $this->withHeaders(['X-App-Locale' => 'en'])->getJson('/api/user/points-history')
            ->assertJsonPath('history.data.0.description', 'Recycled 15 g of plastic');

        $this->withHeaders(['X-App-Locale' => 'my'])->getJson('/api/user/points-history')
            ->assertJsonPath('history.data.0.description', 'Mengitar semula 15 g plastik');
    }
}
