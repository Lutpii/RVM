<?php
// BackEnd/tests/Feature/DemoDataGeneratorTest.php
namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\MaintenanceRecord;
use App\Models\PointsHistory;
use App\Models\RecyclingSession;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DemoDataGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoDataGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private const TZ = 'Asia/Kuala_Lumpur';

    private User $admin;
    private User $realUser;
    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('password'), 'role' => 'admin', 'is_verified' => 1, 'total_points' => 40,
        ]);
        $this->realUser = User::create([
            'name' => 'Real', 'email' => 'real@test.local', 'phone' => '0111111111',
            'password_hash' => bcrypt('password'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 25,
        ]);
        RvmMachine::firstOrCreate(['machine_code' => 'RVM-001'], ['name' => 'RVM One', 'location_name' => 'Hall', 'status' => 'active']);
        RewardItem::create(['name' => 'Small', 'points_cost' => 200, 'is_active' => true]);
        RewardItem::create(['name' => 'Big', 'points_cost' => 500, 'is_active' => true]);
        AdminLog::create([
            'admin_id' => $this->admin->id, 'action' => 'update_user', 'target_type' => 'user', 'target_id' => 1,
            'details' => json_encode(['message' => 'real log']),
        ]);

        $this->now = Carbon::parse('2026-11-10 15:00', self::TZ);
    }

    private function gen(?int $seed = 42): array
    {
        return app(DemoDataGenerator::class)->generate(30, $this->now, $seed);
    }

    private function demoUserIds(): array
    {
        return User::where('email', 'like', '%@demo.rvm')->pluck('id')->all();
    }

    private function dailyCounts(): array
    {
        $counts = [];
        foreach (Transaction::whereIn('user_id', $this->demoUserIds())->get() as $t) {
            $d = $t->created_at->copy()->setTimezone(self::TZ)->toDateString();
            $counts[$d] = ($counts[$d] ?? 0) + 1;
        }
        return $counts;
    }

    public function test_every_day_in_window_has_valid_transactions(): void
    {
        $this->gen();
        for ($i = 0; $i < 30; $i++) {
            $d = $this->now->copy()->subDays($i);
            $n = Transaction::whereIn('user_id', $this->demoUserIds())
                ->where('is_valid', 1)
                ->whereBetween('created_at', [$d->copy()->startOfDay(), $d->copy()->endOfDay()])
                ->count();
            $this->assertGreaterThanOrEqual(1, $n, 'no valid transactions on ' . $d->toDateString());
        }
    }

    public function test_rejected_items_follow_the_kiosk_rule_and_cost_no_points(): void
    {
        $this->gen();
        $rejected = Transaction::whereIn('user_id', $this->demoUserIds())->where('is_valid', 0)->get();
        $this->assertGreaterThan(0, $rejected->count());

        foreach ($rejected as $t) {
            $this->assertSame(0, (int) $t->points_deducted);
            $this->assertSame(0, (int) $t->points_earned);
            $this->assertContains($t->ai_detected_type, ['unknown', 'reject']);
            if ($t->ai_detected_type === 'unknown') {
                $this->assertLessThan(0.6, (float) $t->ai_confidence);
            }
            $this->assertFalse(PointsHistory::where('transaction_id', $t->id)->exists());
        }
        $this->assertFalse(PointsHistory::whereIn('user_id', $this->demoUserIds())->where('type', 'deducted')->exists());
    }

    public function test_accepted_items_are_plastic_aluminium_and_paper_in_similar_numbers(): void
    {
        mt_srand(42);
        $this->gen();
        $counts = Transaction::whereIn('user_id', $this->demoUserIds())->where('is_valid', 1)
            ->selectRaw('material_selected, COUNT(*) AS n')->groupBy('material_selected')
            ->pluck('n', 'material_selected');

        $this->assertEqualsCanonicalizing(['plastic', 'aluminum', 'paper'], $counts->keys()->all(), 'no glass in demo data');
        $total = $counts->sum();
        foreach ($counts as $material => $n) {
            $share = $n / $total;
            $this->assertGreaterThan(0.22, $share, "$material share too small");
            $this->assertLessThan(0.45, $share, "$material share too large");
        }
    }

    public function test_daily_volume_is_not_lopsided(): void
    {
        $this->gen();
        $counts = $this->dailyCounts();
        unset($counts[$this->now->toDateString()]);
        $this->assertCount(29, $counts);
        $this->assertLessThanOrEqual(3, max($counts) / min($counts));

        $wd = $we = [];
        foreach ($counts as $date => $c) {
            if (Carbon::parse($date, self::TZ)->isWeekend()) $we[] = $c; else $wd[] = $c;
        }
        $this->assertGreaterThan(array_sum($we) / count($we), array_sum($wd) / count($wd));
    }

    public function test_each_demo_user_balance_matches_ledger(): void
    {
        $this->gen();
        $sawRedeemed = false;
        foreach (User::where('email', 'like', '%@demo.rvm')->get() as $u) {
            $rows = PointsHistory::where('user_id', $u->id)->orderBy('id')->get();
            $this->assertSame((int) $rows->sum('points_change'), (int) $u->total_points);
            if ($rows->isNotEmpty()) {
                $this->assertSame((int) $u->total_points, (int) $rows->last()->balance_after);
            }
            $this->assertGreaterThanOrEqual(0, $u->total_points);
            if ($rows->where('type', 'redeemed')->isNotEmpty()) $sawRedeemed = true;
        }
        $this->assertTrue($sawRedeemed);
    }

    public function test_no_timestamps_in_future_and_today_skipped_before_nine(): void
    {
        $this->gen();
        $this->assertSame(0, Transaction::whereIn('user_id', $this->demoUserIds())->where('created_at', '>', $this->now)->count());

        $early = Carbon::parse('2026-11-11 08:30', self::TZ);
        $res = app(DemoDataGenerator::class)->generate(30, $early, 42);
        $this->assertSame('skipped', $res['today']);
        $this->assertSame(0, Transaction::whereIn('user_id', $this->demoUserIds())
            ->whereBetween('created_at', [Carbon::parse('2026-11-11 00:00', self::TZ), Carbon::parse('2026-11-11 23:59:59', self::TZ)])
            ->count());
    }

    public function test_rerun_same_day_does_not_duplicate(): void
    {
        $this->gen();
        $before = $this->dailyCounts();
        $res = app(DemoDataGenerator::class)->generate(30, $this->now, 43);
        $after = $this->dailyCounts();

        $today = $this->now->toDateString();
        foreach ($before as $date => $c) {
            if ($date === $today) continue;
            $this->assertSame($c, $after[$date] ?? 0, "past day $date changed");
        }
        $this->assertSame(0, $res['days_filled']);
    }

    public function test_admin_logs_are_demo_flagged_and_consistent(): void
    {
        $this->gen();
        $logs = AdminLog::where('details->demo', true)->get();
        $this->assertGreaterThan(0, $logs->count());
        foreach ($logs as $log) {
            $this->assertTrue(json_decode($log->details, true)['demo'] === true);
            if ($log->action === 'use_voucher') {
                $r = RewardRedemption::find($log->target_id);
                $this->assertNotNull($r);
                $this->assertNotNull($r->used_at);
                $this->assertStringContainsString($r->voucher_code, json_decode($log->details, true)['message']);
            }
        }
        $perDay = $logs->groupBy(fn ($l) => $l->created_at->copy()->setTimezone(self::TZ)->toDateString());
        $this->assertGreaterThanOrEqual(29, $perDay->count());
        foreach ($perDay as $date => $dayLogs) {
            $this->assertGreaterThanOrEqual(1, $dayLogs->count(), $date);
            $this->assertLessThanOrEqual(4, $dayLogs->count(), $date);
        }
        $this->assertSame(1, AdminLog::where('action', 'update_user')->count());
        $this->assertSame(1, AdminLog::whereNull('details->demo')->count());
        $this->assertGreaterThanOrEqual(2, $logs->pluck('action')->unique()->count());
        $this->assertLessThanOrEqual(0.6 * $perDay->count(), $logs->where('action', 'export_csv')->count());
        foreach ($logs as $log) {
            $h = (int) $log->created_at->copy()->setTimezone(self::TZ)->format('G');
            $this->assertGreaterThanOrEqual(8, $h);
            $this->assertLessThanOrEqual(20, $h);
        }
    }

    public function test_morning_run_sequence_fills_every_day(): void
    {
        $gen = app(DemoDataGenerator::class);
        $first = Carbon::parse('2026-11-09 21:00', self::TZ);
        $gen->generate(30, $first, null);
        $gen->generate(30, Carbon::parse('2026-11-10 09:30', self::TZ), null);
        $gen->generate(30, Carbon::parse('2026-11-11 09:30', self::TZ), null);
        $afterMorning = $this->dailyCounts();
        $this->assertGreaterThanOrEqual(1, $afterMorning['2026-11-11'] ?? 0);
        $gen->generate(30, Carbon::parse('2026-11-11 10:00', self::TZ), null);

        $counts = $this->dailyCounts();
        $this->assertGreaterThanOrEqual(1, $counts['2026-11-11'] ?? 0);
        unset($counts['2026-11-11']);
        $this->assertCount(31, $counts); // sliding window: 11 Oct .. 10 Nov
        foreach ($counts as $date => $c) {
            $this->assertGreaterThanOrEqual(6, $c, "day $date too small");
        }
        $this->assertLessThanOrEqual(3, max($counts) / min($counts));

        // Deterministic quota: 10 Nov is complete and identical to a single full-day run.
        $this->assertGreaterThanOrEqual(10, $counts['2026-11-10']);
    }

    public function test_dsme_bins_grow_by_at_least_one_per_valid_item(): void
    {
        $this->gen();
        $dsme = RvmMachine::where('machine_code', 'RVM-DSME')->first();
        $valid = Transaction::where('machine_id', $dsme->id)->where('is_valid', 1)->count();
        $this->assertGreaterThan(0, $valid);
        $this->assertGreaterThan(0, $dsme->plastic_level + $dsme->aluminum_level
            + AdminLog::where('action', 'update_bin_levels')->count() * 90);
    }

    public function test_demo_users_have_join_dates_before_window_and_are_not_moved(): void
    {
        $this->gen();
        $windowStart = Carbon::parse('2026-10-12 00:00', self::TZ);
        $dates = [];
        foreach (User::where('email', 'like', '%@demo.rvm')->get() as $u) {
            $this->assertTrue($u->created_at->lt($windowStart->copy()->subDays(2)), $u->email);
            $this->assertTrue($u->created_at->gte($windowStart->copy()->subDays(21)), $u->email);
            $dates[$u->id] = $u->created_at->timestamp;
        }
        app(DemoDataGenerator::class)->generate(30, $this->now->copy()->addDay(), 1);
        foreach (User::where('email', 'like', '%@demo.rvm')->get() as $u) {
            $this->assertSame($dates[$u->id], $u->created_at->timestamp);
        }
    }

    public function test_demo_users_cannot_log_in(): void
    {
        $this->gen();
        $this->assertNotSame(200, $this->postJson('/api/auth/login', [
            'email' => 'nurul.aina@demo.rvm', 'password' => 'password',
        ])->getStatusCode());
    }

    public function test_purge_removes_only_demo_rows(): void
    {
        $this->gen();
        $ids = $this->demoUserIds();
        $res = app(DemoDataGenerator::class)->purge();
        $this->assertSame(9, $res['users']);
        $this->assertSame(0, Transaction::whereIn('user_id', $ids)->count());
        $this->assertSame(0, PointsHistory::whereIn('user_id', $ids)->count());
        $this->assertSame(0, RewardRedemption::whereIn('user_id', $ids)->count());
        $this->assertSame(0, RecyclingSession::whereIn('user_id', $ids)->count());

        $this->assertSame(0, User::where('email', 'like', '%@demo.rvm')->count());
        $this->assertSame(0, RecyclingSession::where('session_code', 'like', 'DEMO-%')->count());
        $this->assertSame(0, AdminLog::where('details->demo', true)->count());
        $this->assertNotNull(User::find($this->realUser->id));
        $this->assertSame(1, AdminLog::where('action', 'update_user')->count());
        $this->assertFalse(RvmMachine::where('machine_code', 'RVM-DSME')->exists());
    }

    public function test_demo_maintenance_records_are_closed_labelled_and_not_duplicated(): void
    {
        $res = $this->gen();
        $dsme = RvmMachine::where('machine_code', 'RVM-DSME')->first();
        $records = MaintenanceRecord::where('machine_id', $dsme->id)->get();

        $this->assertSame(3, $res['maintenance_records']);
        $this->assertCount(3, $records);
        foreach ($records as $r) {
            $this->assertStringStartsWith('Demo Technician', $r->technician_name);
            $this->assertSame('closed', $r->status);
            $this->assertNotEmpty($r->action_taken);
            $this->assertTrue($r->created_at->lt($r->closed_at));
            $this->assertTrue($r->closed_at->lte($this->now));
            $this->assertTrue($r->created_at->gte($this->now->copy()->startOfDay()->subDays(29)));
        }

        // A second run the same day adds none.
        $again = app(DemoDataGenerator::class)->generate(30, $this->now, 7);
        $this->assertSame(0, $again['maintenance_records']);
        $this->assertSame(3, MaintenanceRecord::count());
    }

    public function test_purge_removes_demo_maintenance_records_only(): void
    {
        $this->gen();
        $real = RvmMachine::create(['machine_code' => 'RVM-REAL', 'name' => 'Real', 'location_name' => 'X', 'status' => 'active']);
        MaintenanceRecord::create(['machine_id' => $real->id, 'technician_name' => 'Ali', 'issue' => 'Real issue', 'status' => 'open']);

        $res = app(DemoDataGenerator::class)->purge();

        $this->assertSame(3, $res['maintenance_records']);
        $this->assertSame(0, MaintenanceRecord::where('technician_name', 'like', 'Demo Technician%')->count());
        $this->assertSame(1, MaintenanceRecord::where('machine_id', $real->id)->count());
    }

    public function test_purge_keeps_dsme_machine_with_real_sessions(): void
    {
        $this->gen();
        $dsme = RvmMachine::where('machine_code', 'RVM-DSME')->first();
        RecyclingSession::create([
            'session_code' => 'REAL-1', 'user_id' => $this->realUser->id, 'machine_id' => $dsme->id,
            'status' => 'completed', 'start_points' => 0, 'end_points' => 0, 'points_earned' => 0, 'total_items' => 0,
            'started_at' => $this->now, 'ended_at' => $this->now,
        ]);

        $this->assertFalse(app(DemoDataGenerator::class)->purge()['machine_deleted']);
        $this->assertTrue(RvmMachine::where('machine_code', 'RVM-DSME')->exists());
    }

    public function test_real_accounts_untouched(): void
    {
        PointsHistory::create(['user_id' => $this->realUser->id, 'points_change' => 25, 'balance_after' => 25, 'type' => 'earned']);
        $this->gen();

        $this->assertSame(40, (int) $this->admin->fresh()->total_points);
        $this->assertSame(25, (int) $this->realUser->fresh()->total_points);
        $this->assertSame(0, PointsHistory::where('user_id', $this->admin->id)->count());
        $this->assertSame(1, PointsHistory::where('user_id', $this->realUser->id)->count());
    }

    public function test_real_machine_rows_are_never_modified(): void
    {
        $m = RvmMachine::where('machine_code', 'RVM-001')->first();
        $m->forceFill(['glass_level' => 95, 'plastic_level' => 93])->saveQuietly();
        $before = DB::table('rvm_machines')->where('machine_code', 'RVM-001')->first();

        $this->gen();
        $this->assertEquals($before, DB::table('rvm_machines')->where('machine_code', 'RVM-001')->first());
        $this->assertGreaterThan(0, Transaction::where('machine_id', $m->id)->count());

        app(DemoDataGenerator::class)->purge();
        $this->assertEquals($before, DB::table('rvm_machines')->where('machine_code', 'RVM-001')->first());
    }
}
