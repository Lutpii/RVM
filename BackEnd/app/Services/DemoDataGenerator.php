<?php
// BackEnd/app/Services/DemoDataGenerator.php
namespace App\Services;

use App\Models\AdminLog;
use App\Models\MaintenanceRecord;
use App\Models\PointsHistory;
use App\Models\RecyclingSession;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Generates realistic, purgeable demo data (users, sessions, transactions,
 * redemptions, admin logs) through the same writes as the real kiosk flow.
 * Everything it creates is marked: user email @demo.rvm, session code DEMO-,
 * admin log details {"demo": true}, machine RVM-DSME.
 */
class DemoDataGenerator
{
    private const TZ = 'Asia/Kuala_Lumpur';
    private const EMAIL_SUFFIX = '@demo.rvm';
    private const DAY_START = 8 * 3600;
    private const DAY_END = 20 * 3600;
    private const REJECT_PERCENT = 7;
    private const REDEEM_PERCENT = 15;
    private const DSME_PERCENT = 60;

    // [name, activity weight 1-5]
    private const USERS = [
        ['Nurul Aina', 5], ['Muhammad Haziq', 4], ['Tan Wei Jie', 3], ['Siti Nur Aisyah', 3],
        ['Priya Devi', 2], ['Lim Mei Ling', 2], ['Ahmad Danial', 1], ['Rajesh Kumar', 1], ['Farah Hanis', 1],
    ];

    /** @var array<string, array> */
    private array $users = [];
    /** @var array{dsme: RvmMachine, others: RvmMachine[]} */
    private array $machines;
    private array $rewardItems = [];
    private ?User $admin = null;
    private array $rates;
    private ?Carbon $windowStart = null;

    public function __construct(private RewardConfigService $rewards)
    {
    }

    public static function isDemoEmail(string $email): bool
    {
        return str_ends_with($email, self::EMAIL_SUFFIX);
    }

    public function generate(int $days, CarbonInterface $now, ?int $seed = null): array
    {
        if ($seed !== null) mt_srand($seed);

        $now = Carbon::instance($now)->setTimezone(self::TZ);
        $this->rates = $this->rewards->load();

        $result = ['days_filled' => 0, 'transactions' => 0, 'admin_logs' => 0, 'today' => 'skipped'];
        $windowStart = $now->copy()->startOfDay()->subDays($days - 1);
        $this->windowStart = $windowStart;
        $this->prepare();

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = $now->copy()->startOfDay()->subDays($i);
            $isToday = $i === 0;
            // Deterministic per-date quota, independent of what already exists.
            $quota = $this->quotaFor($day);
            $existing = $this->demoItemCount($day);

            if (!$isToday) {
                if ($this->dayFilled($day)) {
                    // Complete a day that was first generated as a partial "today".
                    $missing = $quota - $existing;
                    if ($missing > 0) {
                        $from = $this->lastDemoItemTime($day) ?? $day->copy()->addSeconds(self::DAY_START);
                        $until = $day->copy()->addSeconds(self::DAY_END);
                        if ($from->gt($until)) $from = $until->copy();
                        $count = DB::transaction(fn () => $this->generateItems($day, $missing, $from, $until));
                        $result['transactions'] += $count;
                    }
                    continue;
                }
                $n = DB::transaction(function () use ($day, $quota) {
                    $count = $this->generateItems($day, $quota, $day->copy()->addSeconds(self::DAY_START), $day->copy()->addSeconds(self::DAY_END));
                    return [$count, $this->generateAdminLogs($day, null)];
                });
                $result['transactions'] += $n[0];
                $result['admin_logs'] += $n[1];
                $result['days_filled']++;
                continue;
            }

            // Today: proportional share of the quota, only the missing difference is added.
            $open = $day->copy()->addSeconds(self::DAY_START);
            if ($now->lt($day->copy()->setTime(9, 0))) {
                $result['today'] = 'skipped';
                continue;
            }
            $frac = min(1, max(0, $open->diffInSeconds($now) / (self::DAY_END - self::DAY_START)));
            $target = (int) round($quota * $frac);
            if ($quota > 0) $target = max(1, $target);
            $missing = $target - $existing;
            if ($missing <= 0) {
                $result['today'] = 'skipped';
                continue;
            }
            $hadSessions = $this->dayFilled($day);
            $from = $this->lastDemoItemTime($day) ?? $open;
            $added = DB::transaction(function () use ($day, $missing, $from, $now, $hadSessions) {
                $count = $this->generateItems($day, $missing, $from, $now);
                return [$count, $hadSessions ? 0 : $this->generateAdminLogs($day, $now)];
            });
            $result['transactions'] += $added[0];
            $result['admin_logs'] += $added[1];
            $result['today'] = $hadSessions ? 'topped_up' : 'filled';
            if (!$hadSessions) $result['days_filled']++;
        }

        $result['maintenance_records'] = $this->generateMaintenanceRecords($windowStart, $now);

        return $result;
    }

    public function purge(): array
    {
        // Explicit deletes: do not rely on FK cascades (TiDB may not enforce them).
        return DB::transaction(function () {
            $ids = User::where('email', 'like', '%' . self::EMAIL_SUFFIX)->pluck('id')->all();
            foreach (['points_history', 'transactions', 'reward_redemptions', 'recycling_sessions'] as $table) {
                if ($ids) DB::table($table)->whereIn('user_id', $ids)->delete();
            }
            $users = $ids ? User::whereIn('id', $ids)->delete() : 0;
            $logs = AdminLog::where('details->demo', true)->delete();
            $maintenance = MaintenanceRecord::where('technician_name', 'like', self::DEMO_TECHNICIAN . '%')->delete();

            $deleted = false;
            $dsme = RvmMachine::where('machine_code', 'RVM-DSME')->first();
            if ($dsme && !RecyclingSession::where('machine_id', $dsme->id)->exists()) {
                $dsme->delete();
                $deleted = true;
            }

            return ['users' => $users, 'admin_logs' => $logs, 'maintenance_records' => $maintenance, 'machine_deleted' => $deleted];
        });
    }

    // ---- maintenance records (A06) ----------------------------------------

    /** Demo service history is recognisable by this technician-name prefix. */
    private const DEMO_TECHNICIAN = 'Demo Technician';

    /** [days ago opened, hours until closed, technician, issue, action taken] */
    private const MAINTENANCE_DEMO = [
        [24, 3, 'Demo Technician A', 'Plastic flap did not open fully during a deposit.', 'Cleaned the flap guide and re-tested 5 deposits.'],
        [13, 2, 'Demo Technician B', 'Camera image too dark in the early morning.', 'Adjusted the LED strip angle and checked detection on 10 items.'],
        [4, 1, 'Demo Technician A', 'Aluminium bin level read full while half empty.', 'Re-seated the level sensor cable and recalibrated the reading.'],
    ];

    /**
     * A few closed service records on the demo machine, so the Service History
     * panel has something to show. Created once; reruns add nothing.
     */
    private function generateMaintenanceRecords(Carbon $windowStart, Carbon $now): int
    {
        $dsme = $this->machines['dsme'];
        $exists = MaintenanceRecord::where('machine_id', $dsme->id)
            ->where('technician_name', 'like', self::DEMO_TECHNICIAN . '%')->exists();
        if ($exists) return 0;

        $written = 0;
        foreach (self::MAINTENANCE_DEMO as [$daysAgo, $hours, $technician, $issue, $action]) {
            $opened = $now->copy()->startOfDay()->subDays($daysAgo)->setTime(10, 15);
            $closed = $opened->copy()->addHours($hours);
            if ($opened->lt($windowStart) || $closed->gt($now)) continue;

            $record = new MaintenanceRecord([
                'machine_id'      => $dsme->id,
                'technician_name' => $technician,
                'issue'           => $issue,
                'action_taken'    => $action,
                'status'          => 'closed',
                'opened_by'       => $this->admin?->id,
                'closed_by'       => $this->admin?->id,
                'closed_at'       => $closed,
            ]);
            $record->created_at = $opened;
            $record->updated_at = $closed;
            $record->save();
            $written++;
        }
        return $written;
    }

    // ---- setup -----------------------------------------------------------

    private function prepare(): void
    {
        $this->users = [];
        foreach (self::USERS as $i => [$name, $weight]) {
            $email = Str::slug($name, '.') . self::EMAIL_SUFFIX;
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => '01990000' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'password_hash' => bcrypt(Str::random(64)),
                'role' => 'user',
                'is_verified' => 1,
                'total_points' => 0,
            ]);
            if ($user->wasRecentlyCreated && $this->windowStart) {
                // Join date 3-20 days before the window, deterministic per user; never moved on re-runs.
                $joined = $this->windowStart->copy()->subDays(3 + crc32($email) % 18)->setTime(10, 0);
                $user->forceFill(['created_at' => $joined, 'updated_at' => $joined])->saveQuietly();
            }
            $this->users[$email] = ['model' => $user, 'weight' => $weight];
        }

        $dsme = RvmMachine::firstOrCreate(
            ['machine_code' => 'RVM-DSME'],
            ['name' => 'RVM DSME', 'location_name' => 'DSME Engineering', 'status' => 'active']
        );
        $others = RvmMachine::whereIn('machine_code', ['RVM-001', 'RVM-002'])->orderBy('machine_code')->get()->all();
        $this->machines = ['dsme' => $dsme, 'others' => $others];

        $this->rewardItems = RewardItem::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '>', 0))
            ->orderBy('points_cost')->get()->all();
        $this->admin = User::where('role', 'admin')->orderBy('id')->first();
    }

    // ---- per-day volume --------------------------------------------------

    /** Raw (unclamped) quota: a pure function of the date. */
    private function rawQuota(Carbon $day): int
    {
        $h = crc32($day->toDateString());
        [$lo, $hi] = $day->isWeekend() ? [6, 11] : [10, 18];
        return $lo + $h % ($hi - $lo + 1);
    }

    /** Deterministic per-date quota, clamped to +-30% of the previous date's raw quota. */
    private function quotaFor(Carbon $day): int
    {
        $quota = $this->rawQuota($day);
        $prev = $this->rawQuota($day->copy()->subDay());
        return max((int) ceil($prev * 0.7), min((int) floor($prev * 1.3), $quota));
    }

    private function dayBounds(Carbon $day): array
    {
        return [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
    }

    private function dayFilled(Carbon $day): bool
    {
        return RecyclingSession::where('session_code', 'like', 'DEMO-%')
            ->whereBetween('started_at', $this->dayBounds($day))->exists();
    }

    private function demoItemCount(Carbon $day): int
    {
        return Transaction::whereBetween('created_at', $this->dayBounds($day))
            ->whereIn('session_id', RecyclingSession::where('session_code', 'like', 'DEMO-%')->select('id'))
            ->count();
    }

    private function lastDemoItemTime(Carbon $day): ?Carbon
    {
        $max = Transaction::whereBetween('created_at', $this->dayBounds($day))
            ->whereIn('session_id', RecyclingSession::where('session_code', 'like', 'DEMO-%')->select('id'))
            ->max('created_at');
        return $max ? Carbon::parse($max, self::TZ)->addSecond() : null;
    }

    // ---- sessions / items ------------------------------------------------

    /** Generates $items items on $day between $from and $until; returns how many were written. */
    private function generateItems(Carbon $day, int $items, Carbon $from, Carbon $until): int
    {
        $span = max(0, $from->diffInSeconds($until) - 60);
        $sessions = [];
        for ($left = $items; $left > 0;) {
            $n = min(mt_rand(1, 4), $left);
            $sessions[] = ['start' => $from->copy()->addSeconds($span > 0 ? mt_rand(0, $span) : 0), 'items' => $n];
            $left -= $n;
        }
        usort($sessions, fn ($a, $b) => $a['start']->timestamp <=> $b['start']->timestamp);

        foreach ($sessions as $s) {
            $this->runSession($s['start'], $s['items'], $until);
        }
        return $items;
    }

    private function pickUser(): User
    {
        $total = array_sum(array_column($this->users, 'weight'));
        $roll = mt_rand(1, $total);
        foreach ($this->users as $u) {
            $roll -= $u['weight'];
            if ($roll <= 0) return $u['model'];
        }
        return end($this->users)['model'];
    }

    private function runSession(Carbon $start, int $items, Carbon $until): void
    {
        $user = $this->pickUser()->fresh();
        $useDsme = !$this->machines['others'] || mt_rand(1, 100) <= self::DSME_PERCENT;
        $machine = $useDsme
            ? $this->machines['dsme']
            : $this->machines['others'][mt_rand(0, count($this->machines['others']) - 1)];
        $machine = $machine->fresh();
        // Accepted materials are plastic, aluminium and paper (no glass), and
        // the demo should show them in roughly equal numbers. The DSME machine
        // only takes plastic and aluminium (2-bin), so the other machines lean
        // on paper: with DSME_PERCENT = 60 this works out to about a third each.
        $weights = $useDsme
            ? ['plastic' => 1, 'aluminum' => 1]
            : ['plastic' => 1, 'aluminum' => 1, 'paper' => 10];

        $session = $this->stamp(RecyclingSession::create([
            'session_code' => 'DEMO-' . strtoupper(Str::random(10)),
            'user_id' => $user->id,
            'machine_id' => $machine->id,
            'status' => 'active',
            'start_points' => $user->total_points,
            'end_points' => $user->total_points,
            'points_earned' => 0,
            'total_items' => 0,
            'started_at' => $start,
        ]), $start);

        $at = $start->copy();
        $earnedTotal = 0;
        $balance = (int) $user->total_points;

        for ($k = 0; $k < $items; $k++) {
            if ($at->gt($until)) $at = $until->copy();
            $material = $this->pickWeighted($weights);
            $rejected = mt_rand(1, 100) <= self::REJECT_PERCENT;

            if ($rejected) {
                // Same rule as the kiosk: the AI service turns anything below
                // 60 % confidence into 'unknown', and a recognised material with
                // no slot on this machine (metal, wood, bricks) into 'reject'.
                // Either way the item is returned with 0 points and no deduction,
                // so no points-history row is written.
                $unknown = mt_rand(0, 1) === 1;
                $detected = $unknown ? 'unknown' : 'reject';
                $this->stamp(Transaction::create([
                    'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
                    'material_selected' => $detected, 'ai_detected_type' => $detected,
                    'ai_confidence' => $unknown ? mt_rand(2000, 5900) / 10000 : mt_rand(6500, 9500) / 10000,
                    'is_valid' => 0, 'weight_grams' => 0, 'points_earned' => 0, 'points_deducted' => 0,
                ]), $at);
            } else {
                $weight = in_array($material, ['aluminum', 'plastic'], true) ? mt_rand(9, 49) : mt_rand(50, 500);
                $points = (int) ($this->rates[$material] ?? 0);
                $tx = $this->stamp(Transaction::create([
                    'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
                    'material_selected' => $material, 'ai_detected_type' => $material,
                    'ai_confidence' => mt_rand(8200, 9900) / 10000,
                    'is_valid' => 1, 'weight_grams' => $weight, 'points_earned' => $points, 'points_deducted' => 0,
                ]), $at);
                $balance += $points;
                $earnedTotal += $points;
                $this->stamp(PointsHistory::create([
                    'user_id' => $user->id, 'transaction_id' => $tx->id, 'session_id' => $session->id,
                    'points_change' => $points, 'balance_after' => $balance, 'type' => 'earned',
                    'description' => "Recycled {$weight}g of {$material}",
                ]), $at);
                // Bin levels are only ever written on the demo machine; real machines stay untouched.
                if ($machine->id === $this->machines['dsme']->id) {
                    $field = $material . '_level';
                    $machine->$field = min(100, $machine->$field + max(1, (int) ceil($weight / 50)));
                }
            }
            $at = $at->copy()->addSeconds(mt_rand(45, 150));
        }
        $endAt = $at->gt($until) ? $until->copy() : $at->copy();

        if ($machine->isDirty()) $machine->save();
        $user->update(['total_points' => $balance]);
        $session->forceFill([
            'status' => 'completed', 'total_items' => $items, 'points_earned' => $earnedTotal,
            'end_points' => $balance, 'ended_at' => $endAt, 'updated_at' => $endAt,
        ])->saveQuietly();

        $this->maybeRedeem($user, $balance, $endAt, $until);
    }

    /** @param array<string, int> $weights */
    private function pickWeighted(array $weights): string
    {
        $roll = mt_rand(1, array_sum($weights));
        foreach ($weights as $key => $w) {
            $roll -= $w;
            if ($roll <= 0) return $key;
        }
        return array_key_last($weights);
    }

    private function maybeRedeem(User $user, int $balance, Carbon $at, Carbon $until): void
    {
        if (!$this->rewardItems || $balance < $this->rewardItems[0]->points_cost) return;
        if (mt_rand(1, 100) > self::REDEEM_PERCENT) return;

        $affordable = array_values(array_filter($this->rewardItems, fn ($i) => $i->points_cost <= $balance));
        $item = $affordable[mt_rand(0, count($affordable) - 1)];
        $time = $at->copy()->addSeconds(60);
        if ($time->gt($until)) $time = $until->copy();
        $balance -= $item->points_cost;

        $this->stamp(RewardRedemption::create([
            'user_id' => $user->id, 'reward_item_id' => $item->id, 'reward_name' => $item->name,
            'points_spent' => $item->points_cost, 'voucher_code' => $this->newVoucherCode(),
        ]), $time);
        $this->stamp(PointsHistory::create([
            'user_id' => $user->id, 'points_change' => -$item->points_cost, 'balance_after' => $balance,
            'type' => 'redeemed', 'description' => "Redeemed: {$item->name}",
        ]), $time);
        $user->update(['total_points' => $balance]);
    }

    // Same format as RewardController::newVoucherCode() (private there).
    private function newVoucherCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $chars = '';
            for ($i = 0; $i < 8; $i++) {
                $chars .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
            }
            $code = 'RVM-' . substr($chars, 0, 4) . '-' . substr($chars, 4);
        } while (RewardRedemption::where('voucher_code', $code)->exists());

        return $code;
    }

    // ---- admin logs ------------------------------------------------------

    /** $now null = a complete past day. Returns the number of logs written (1-4). */
    private function generateAdminLogs(Carbon $day, ?Carbon $now): int
    {
        if (!$this->admin) return 0;

        $open = $day->copy()->addSeconds(self::DAY_START);
        $close = $now ? $now->copy() : $day->copy()->addSeconds(self::DAY_END);
        $at = fn () => $open->copy()->addSeconds($close->gt($open) ? mt_rand(0, $open->diffInSeconds($close)) : 0);
        $written = 0;
        $target = mt_rand(1, 4);

        // Full DSME bin -> collection request, then the bins are reset (counts as 2 logs).
        // Only the demo machine is ever touched; real machines are never written.
        $m = RvmMachine::where('machine_code', 'RVM-DSME')
            ->where(fn ($q) => $q->where('aluminum_level', '>=', 90)->orWhere('plastic_level', '>=', 90)
                ->orWhere('glass_level', '>=', 90)->orWhere('paper_level', '>=', 90))
            ->first();
        if ($m) {
            $t = $at();
            $this->log('request_bin_collection', 'system', 0, "Collection requested for 1 machine(s)", $t);
            $t = $t->copy()->addMinutes(mt_rand(5, 30));
            if ($t->gt($close)) $t = $close->copy();
            $m->forceFill(['aluminum_level' => 0, 'plastic_level' => 0, 'glass_level' => 0, 'paper_level' => 0])->saveQuietly();
            $this->log('update_bin_levels', 'machine', $m->id, "Bin levels updated for {$m->name}", $t);
            $written += 2;
        }

        // Mark older, still-unused demo vouchers as used.
        $latest = $close->copy()->subDay();
        $vouchers = RewardRedemption::whereIn('user_id', array_map(fn ($u) => $u['model']->id, $this->users))
            ->whereNull('used_at')->whereNotNull('voucher_code')->where('created_at', '<=', $latest)
            ->inRandomOrder(mt_rand())->limit(mt_rand(1, 2))->get();
        foreach ($vouchers as $r) {
            if ($written >= $target) break;
            $t = $at();
            if ($t->lt($r->created_at->copy()->addDay())) $t = $r->created_at->copy()->addDay();
            if ($t->gt($close)) $t = $close->copy();
            $r->forceFill(['used_at' => $t, 'used_by' => $this->admin->id, 'updated_at' => $t])->saveQuietly();
            $this->log('use_voucher', 'reward_redemption', $r->id, "Marked voucher {$r->voucher_code} as used", $t);
            $written++;
        }

        // Machine edit entry (message only; the machine row is not changed), at most every few days.
        if ($written < $target && $written < 4 && !$this->recentDemoLog('update_machine', $day, 1)) {
            $this->log('update_machine', 'machine', $this->machines['dsme']->id, 'Updated machine: RVM DSME', $at());
            $written++;
        }

        // Report export with a varied range, on roughly half of the days.
        if ($written === 0 ? mt_rand(1, 100) <= 90 : ($written < 4 && mt_rand(1, 100) <= 10)) {
            $to = $day->toDateString();
            $from = match (mt_rand(0, 2)) {
                0 => $day->copy()->subDays(6)->toDateString(),
                1 => $day->copy()->startOfMonth()->toDateString(),
                default => $to,
            };
            $this->log('export_csv', 'transactions', 0, "Exported formal report ({$from} to {$to})", $at());
            $written++;
        }

        if ($written === 0) {
            $to = $day->toDateString();
            $from = $day->copy()->subDays(6)->toDateString();
            $this->log('export_csv', 'transactions', 0, "Exported formal report ({$from} to {$to})", $at());
            $written++;
        }

        return $written;
    }

    private function recentDemoLog(string $action, Carbon $day, int $days): bool
    {
        return AdminLog::where('details->demo', true)->where('action', $action)
            ->whereBetween('created_at', [$day->copy()->subDays($days)->startOfDay(), $day->copy()->endOfDay()])
            ->exists();
    }

    private function log(string $action, string $targetType, int $targetId, string $message, Carbon $at): void
    {
        $this->stamp(AdminLog::create([
            'admin_id' => $this->admin->id, 'action' => $action, 'target_type' => $targetType, 'target_id' => $targetId,
            'details' => json_encode(['message' => $message, 'demo' => true]),
        ]), $at);
    }

    /** Overwrites created_at/updated_at with the generated time (stored in the app timezone). */
    private function stamp($model, Carbon $at)
    {
        $model->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();
        return $model;
    }
}
