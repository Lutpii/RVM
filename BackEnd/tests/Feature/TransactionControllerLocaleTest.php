<?php

namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionControllerLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_bin_full_message_is_localized_including_material_name(): void
    {
        $user = User::create([
            'name' => 'User', 'email' => 'user@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(), 'name' => 'Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
            'plastic_level' => 95,
        ]);
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 0,
        ]);
        Sanctum::actingAs($user, ['*']);

        $this->withHeaders(['X-App-Locale' => 'en'])
            ->postJson('/api/transactions/check-bin', [
                'session_code' => $session->session_code,
                'material_selected' => 'plastic',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'The plastic bin is full. Please choose a different material.');

        $this->withHeaders(['X-App-Locale' => 'my'])
            ->postJson('/api/transactions/check-bin', [
                'session_code' => $session->session_code,
                'material_selected' => 'plastic',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Tong plastik sudah penuh. Sila pilih bahan lain.');
    }

    public function test_active_session_not_found_message_is_localized(): void
    {
        $user = User::create([
            'name' => 'User', 'email' => 'user2@test.local', 'phone' => '0123456780',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($user, ['*']);

        $this->withHeaders(['X-App-Locale' => 'my'])
            ->postJson('/api/transactions/open-lid', ['session_code' => 'NON-EXISTENT'])
            ->assertStatus(404)
            ->assertJsonPath('message', 'Sesi aktif tidak dijumpai.');
    }
}
