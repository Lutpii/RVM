<?php
// BackEnd/tests/Feature/AdminKioskMaintenanceTest.php
namespace Tests\Feature;

use App\Models\QrSession;
use App\Models\RvmMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminKioskMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin_' . uniqid() . '@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'admin',
            'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    private function makeMachine(): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(), 'name' => 'Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
    }

    private function makePendingQr(RvmMachine $machine, string $token = null): QrSession
    {
        return QrSession::create([
            'machine_id' => $machine->id,
            'qr_token'   => $token ?? strtoupper(uniqid()),
            'status'     => 'pending',
            'expires_at' => Carbon::now()->addSeconds(60),
        ]);
    }

    public function test_admin_scanning_the_live_kiosk_qr_triggers_the_kiosk_control_service(): void
    {
        Http::fake(['127.0.0.1:8765/*' => Http::response(['success' => true], 200)]);

        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);
        $machine = $this->makeMachine();
        $qr = $this->makePendingQr($machine);

        $this->postJson("/api/admin/machines/{$machine->id}/maintenance", ['qr_token' => $qr->qr_token])
            ->assertOk()
            ->assertJsonPath('success', true);

        Http::assertSent(fn ($request) => str_contains($request->url(), '127.0.0.1:8765'));

        $this->assertDatabaseHas('qr_sessions', ['id' => $qr->id, 'status' => 'expired']);
        $this->assertDatabaseHas('admin_logs', [
            'admin_id' => $admin->id, 'action' => 'kiosk_maintenance',
            'target_type' => 'machine', 'target_id' => $machine->id,
        ]);
    }

    public function test_rejects_a_qr_token_that_belongs_to_a_different_machine(): void
    {
        Http::fake();
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);
        $machine = $this->makeMachine();
        $otherMachine = $this->makeMachine();
        $qr = $this->makePendingQr($otherMachine);

        $this->postJson("/api/admin/machines/{$machine->id}/maintenance", ['qr_token' => $qr->qr_token])
            ->assertStatus(400)
            ->assertJsonPath('success', false);

        Http::assertNothingSent();
    }

    public function test_rejects_an_expired_qr_token(): void
    {
        Http::fake();
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);
        $machine = $this->makeMachine();
        $qr = $this->makePendingQr($machine);
        $qr->update(['expires_at' => Carbon::now()->subSeconds(5)]);

        $this->postJson("/api/admin/machines/{$machine->id}/maintenance", ['qr_token' => $qr->qr_token])
            ->assertStatus(400)
            ->assertJsonPath('success', false);

        Http::assertNothingSent();
    }

    public function test_rejects_a_qr_token_already_consumed_by_a_real_scan(): void
    {
        Http::fake();
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);
        $machine = $this->makeMachine();
        $qr = $this->makePendingQr($machine);
        $qr->update(['status' => 'scanned']);

        $this->postJson("/api/admin/machines/{$machine->id}/maintenance", ['qr_token' => $qr->qr_token])
            ->assertStatus(400)
            ->assertJsonPath('success', false);

        Http::assertNothingSent();
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        Http::fake();
        $user = User::create([
            'name' => 'User', 'email' => 'user_' . uniqid() . '@test.local', 'phone' => '0123456780',
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ]);
        Sanctum::actingAs($user, ['*']);
        $machine = $this->makeMachine();
        $qr = $this->makePendingQr($machine);

        $this->postJson("/api/admin/machines/{$machine->id}/maintenance", ['qr_token' => $qr->qr_token])
            ->assertStatus(403);

        Http::assertNothingSent();
    }

    public function test_returns_an_error_when_the_kiosk_control_service_is_unreachable(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('refused'));

        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin, ['*']);
        $machine = $this->makeMachine();
        $qr = $this->makePendingQr($machine);

        $this->postJson("/api/admin/machines/{$machine->id}/maintenance", ['qr_token' => $qr->qr_token])
            ->assertStatus(502)
            ->assertJsonPath('success', false);

        // Still recorded, and the QR is still consumed — the admin already proved
        // presence; a dead control service is an infra problem, not a fraudulent attempt.
        $this->assertDatabaseHas('qr_sessions', ['id' => $qr->id, 'status' => 'expired']);
    }
}
