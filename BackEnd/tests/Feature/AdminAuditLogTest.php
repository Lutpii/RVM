<?php
// BackEnd/tests/Feature/AdminAuditLogTest.php
namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $name, string $role, string $phone): User
    {
        return User::create([
            'name' => $name, 'email' => strtolower($name) . '@test.local', 'phone' => $phone,
            'password_hash' => bcrypt('password'), 'role' => $role, 'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    private function makeLog(User $admin, string $action, string $message, string $date): AdminLog
    {
        $log = AdminLog::create([
            'admin_id' => $admin->id, 'action' => $action, 'target_type' => 'machine', 'target_id' => 1,
            'details' => json_encode(['message' => $message]),
        ]);
        $log->forceFill(['created_at' => $date . ' 12:00:00'])->save();
        return $log;
    }

    public function test_filters_by_action_date_and_search(): void
    {
        $alice = $this->makeUser('Alice', 'admin', '0123456789');
        $bob = $this->makeUser('Bob', 'admin', '0123456780');
        $this->makeLog($alice, 'use_voucher', 'Voucher redeemed at counter', '2026-11-01');
        $this->makeLog($bob, 'export_csv', 'Exported sessions report', '2026-11-05');
        $this->makeLog($alice, 'update_user', 'Changed phone number', '2026-11-08');
        Sanctum::actingAs($alice, ['*']);

        $res = $this->getJson('/api/admin/logs')->assertOk();
        $this->assertSame(['update_user', 'export_csv', 'use_voucher'], array_column($res->json('logs.data'), 'action'));
        $this->assertSame('Alice', $res->json('logs.data.0.admin.name'));

        $res = $this->getJson('/api/admin/logs?action=export_csv');
        $this->assertCount(1, $res->json('logs.data'));
        $this->assertSame('export_csv', $res->json('logs.data.0.action'));

        $res = $this->getJson('/api/admin/logs?date_from=2026-11-04&date_to=2026-11-06');
        $this->assertCount(1, $res->json('logs.data'));
        $this->assertSame('export_csv', $res->json('logs.data.0.action'));

        $res = $this->getJson('/api/admin/logs?date_from=2026-11-05');
        $this->assertCount(2, $res->json('logs.data'));

        $res = $this->getJson('/api/admin/logs?search=Bob');
        $this->assertCount(1, $res->json('logs.data'));
        $this->assertSame('export_csv', $res->json('logs.data.0.action'));

        $res = $this->getJson('/api/admin/logs?search=phone number');
        $this->assertCount(1, $res->json('logs.data'));
        $this->assertSame('update_user', $res->json('logs.data.0.action'));
    }

    public function test_recycler_gets_403(): void
    {
        $user = $this->makeUser('Regular', 'user', '0111111111');
        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/admin/logs')->assertStatus(403);
    }

    public function test_returns_distinct_actions(): void
    {
        $admin = $this->makeUser('Admin', 'admin', '0123456789');
        $this->makeLog($admin, 'use_voucher', 'a', '2026-11-01');
        $this->makeLog($admin, 'use_voucher', 'b', '2026-11-02');
        $this->makeLog($admin, 'export_csv', 'c', '2026-11-03');
        Sanctum::actingAs($admin, ['*']);

        $res = $this->getJson('/api/admin/logs')->assertOk();
        $this->assertSame(['export_csv', 'use_voucher'], $res->json('actions'));
    }
}
