<?php
// BackEnd/tests/Feature/DemoDataGeneratorTest.php
namespace Tests\Feature;

use App\Models\AdminLog;
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
        $this->assertSame(1, AdminLog::where('action', 'update_user')->count());
        $this->assertSame(1, AdminLog::whereNull('details->demo')->count());
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
        $res = app(DemoDataGenerator::class)->purge();
        $this->assertSame(9, $res['users']);

        $this->assertSame(0, User::where('email', 'like', '%@demo.rvm')->count());
        $this->assertSame(0, RecyclingSession::where('session_code', 'like', 'DEMO-%')->count());
        $this->assertSame(0, AdminLog::where('details->demo', true)->count());
        $this->assertNotNull(User::find($this->realUser->id));
        $this->assertSame(1, AdminLog::where('action', 'update_user')->count());
        $this->assertFalse(RvmMachine::where('machine_code', 'RVM-DSME')->exists());
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
}
