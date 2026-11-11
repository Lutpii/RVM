<?php
namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\RecyclingSession;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\ReadsExcelDownloads;
use Tests\TestCase;

class AdminTableExportTest extends TestCase
{
    use RefreshDatabase, ReadsExcelDownloads;

    private static int $seq = 0;

    private function makeUser(string $name, string $role = 'user', int $points = 0): User
    {
        self::$seq++;
        return User::create([
            'name' => $name, 'email' => 'x' . self::$seq . '@test.local',
            'phone' => '01' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => $role, 'is_verified' => 1, 'total_points' => $points,
        ]);
    }

    private function makeMachine(string $name): RvmMachine
    {
        return RvmMachine::create(['machine_code' => 'RVM-' . uniqid(), 'name' => $name, 'location_name' => 'Lobby', 'status' => 'active']);
    }

    private function makeSession(RvmMachine $m, User $u): RecyclingSession
    {
        return RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $u->id, 'machine_id' => $m->id,
            'status' => 'completed', 'start_points' => 0, 'total_items' => 1, 'points_earned' => 15,
        ]);
    }

    private function makeTx(RvmMachine $m, User $u, array $o = []): Transaction
    {
        return Transaction::create(array_merge([
            'session_id' => $this->makeSession($m, $u)->id, 'user_id' => $u->id, 'machine_id' => $m->id,
            'material_selected' => 'plastic', 'ai_detected_type' => 'plastic', 'ai_confidence' => 0.91,
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0,
        ], $o));
    }

    private function asAdmin(): User
    {
        $admin = $this->makeUser('Admin', 'admin');
        Sanctum::actingAs($admin, ['*']);
        return $admin;
    }

    public function test_transactions_export_follows_the_filters_and_has_no_weight(): void
    {
        $m = $this->makeMachine('RVM DSME');
        $u = $this->makeUser('Alice');
        $this->makeTx($m, $u);
        $this->makeTx($m, $u, ['material_selected' => 'unknown', 'ai_detected_type' => 'unknown',
            'ai_confidence' => 0.42, 'is_valid' => 0, 'weight_grams' => 0, 'points_earned' => 0]);
        $this->asAdmin();

        $response = $this->get('/api/admin/transactions/export?status=rejected');
        $table = $this->excelTable($response);

        $this->assertSame(['Date', 'Time', 'User', 'User ID', 'Machine', 'Location', 'Item', 'Result', 'Reason', 'AI confidence %', 'Points'], $table[0]);
        $this->assertCount(2, $table, 'header + the one rejected row');
        $this->assertSame(
            ['Alice', 'RVM DSME', 'Not recognised', 'Rejected', 'AI was not sure what it was (below 60%)', 42, 0],
            [$table[1][2], $table[1][4], $table[1][6], $table[1][7], $table[1][8], $table[1][9], $table[1][10]]
        );
        $this->assertStringContainsString('Show: Rejected', $this->excelFiltersLine($response));
        $this->assertNotContains('Weight', $table[0]);
    }

    public function test_users_export_uses_the_search_and_hides_the_guest_account(): void
    {
        $this->makeUser('Alice', 'user', 120);
        $this->makeUser('Bob');
        User::guest();
        $this->asAdmin();

        $table = $this->excelTable($this->get('/api/admin/users/export?search=Ali'));

        $this->assertCount(2, $table);
        $this->assertSame('Alice', $table[1][1]);
        $this->assertSame(120, $table[1][4]);
    }

    public function test_sessions_export_lists_sessions(): void
    {
        $m = $this->makeMachine('RVM DSME');
        $this->makeSession($m, $this->makeUser('Alice'));
        $this->asAdmin();

        $table = $this->excelTable($this->get('/api/admin/sessions/export'));

        $this->assertSame('Session code', $table[0][0]);
        $this->assertCount(2, $table);
        $this->assertSame(['Alice', 'RVM DSME', 'completed', 1, 15], [$table[1][1], $table[1][3], $table[1][4], $table[1][5], $table[1][6]]);
    }

    public function test_machines_export_lists_bin_levels(): void
    {
        $m = $this->makeMachine('RVM DSME');
        $m->forceFill(['plastic_level' => 40])->save();
        $this->asAdmin();

        $table = $this->excelTable($this->get('/api/admin/machines/export'));

        // The migration seeds one machine of its own, so look ours up by name.
        $this->assertCount(RvmMachine::count() + 1, $table);
        $row = collect($table)->firstWhere(1, 'RVM DSME');
        $this->assertSame(40, $row[6]);
    }

    public function test_audit_log_export_follows_the_action_filter_and_logs_itself_afterwards(): void
    {
        $admin = $this->asAdmin();
        AdminLog::create(['admin_id' => $admin->id, 'action' => 'use_voucher', 'target_type' => 'voucher', 'target_id' => 1, 'details' => json_encode(['message' => 'Voucher ABC used'])]);
        AdminLog::create(['admin_id' => $admin->id, 'action' => 'update_user', 'target_type' => 'user', 'target_id' => 2, 'details' => json_encode(['message' => 'changed'])]);

        $table = $this->excelTable($this->get('/api/admin/logs/export?action=use_voucher'));

        $this->assertCount(2, $table);
        $this->assertSame(['Admin', 'use_voucher', 'voucher #1', 'Voucher ABC used'], array_slice($table[1], 1));
        $this->assertSame(1, AdminLog::where('target_type', 'admin_logs')->count());
    }

    public function test_reward_catalog_and_redemptions_export(): void
    {
        $item = RewardItem::create(['name' => 'Coffee', 'points_cost' => 100, 'stock' => null, 'is_active' => true]);
        $u = $this->makeUser('Alice');
        RewardRedemption::create(['user_id' => $u->id, 'reward_item_id' => $item->id, 'reward_name' => 'Coffee', 'points_spent' => 100, 'voucher_code' => 'ABC123']);
        $this->asAdmin();

        $catalog = $this->excelTable($this->get('/api/admin/reward-items/export'));
        $this->assertSame(['Coffee', 100, 'Unlimited', 'Active'], [$catalog[1][0], $catalog[1][3], $catalog[1][4], $catalog[1][5]]);

        $history = $this->excelTable($this->get('/api/admin/redemptions/export'));
        $this->assertSame(['Alice', 'Coffee', 100, 'ABC123', 'Not yet'], [$history[1][1], $history[1][3], $history[1][4], $history[1][8], $history[1][9]]);
    }

    public function test_non_admins_cannot_export(): void
    {
        Sanctum::actingAs($this->makeUser('Alice'), ['*']);

        foreach (['users', 'machines', 'sessions', 'transactions', 'logs', 'reward-items', 'redemptions', 'detection-logs'] as $table) {
            $this->getJson("/api/admin/{$table}/export")->assertStatus(403);
        }
    }
}
