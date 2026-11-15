<?php
namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\ReadsExcelDownloads;
use Tests\TestCase;

// Test-only user whose first number guess collides, as when two people
// register at the same moment and both read the same max(display_no).
class CollidingUser extends User
{
    protected $table = 'users';

    protected static function displayNoCandidate(int $attempt): int
    {
        return $attempt === 0 ? 1 : parent::displayNoCandidate($attempt);
    }
}

class UserDisplayNumberTest extends TestCase
{
    use RefreshDatabase, ReadsExcelDownloads;

    private static int $seq = 0;

    private function attrs(string $name, string $role = 'user'): array
    {
        self::$seq++;
        return [
            'name' => $name, 'email' => 'dn' . self::$seq . '@test.local',
            'phone' => '01' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => $role, 'is_verified' => 1, 'total_points' => 0,
        ];
    }

    private function makeUser(string $name, string $role = 'user'): User
    {
        return User::create($this->attrs($name, $role));
    }

    private function asAdmin(): void
    {
        Sanctum::actingAs($this->makeUser('Admin', 'admin'), ['*']);
    }

    public function test_new_users_get_sequential_numbers(): void
    {
        $a = $this->makeUser('A'); $b = $this->makeUser('B'); $c = $this->makeUser('C');

        $this->assertSame([1, 2, 3], [$a->display_no, $b->display_no, $c->display_no]);
        $this->assertSame(3, $c->fresh()->display_no);
    }

    public function test_number_is_kept_after_another_user_is_deleted(): void
    {
        $a = $this->makeUser('A'); $b = $this->makeUser('B'); $c = $this->makeUser('C');
        $b->delete();
        $d = $this->makeUser('D');

        $this->assertSame(1, $a->fresh()->display_no);
        $this->assertSame(3, $c->fresh()->display_no);
        $this->assertSame(4, $d->fresh()->display_no);
    }

    public function test_backfill_orders_by_created_at_not_id(): void
    {
        // Higher id, older registration (TiDB hands out ids in batches per node).
        DB::table('users')->insert([
            array_merge($this->attrs('Newer'), ['id' => 100, 'created_at' => '2026-10-02 10:00:00', 'updated_at' => now()]),
            array_merge($this->attrs('Older'), ['id' => 900, 'created_at' => '2026-10-01 10:00:00', 'updated_at' => now()]),
        ]);

        $migration = require database_path('migrations/2026_11_15_000001_add_display_no_to_users_table.php');
        $migration->backfill();

        $this->assertSame(1, (int) DB::table('users')->where('id', 900)->value('display_no'));
        $this->assertSame(2, (int) DB::table('users')->where('id', 100)->value('display_no'));
    }

    public function test_collision_retries_with_next_number(): void
    {
        $first = $this->makeUser('First');
        $second = CollidingUser::create($this->attrs('Second'));

        $this->assertSame(1, $first->fresh()->display_no);
        $this->assertSame(2, $second->display_no);
        $this->assertSame(2, (int) DB::table('users')->where('id', $second->id)->value('display_no'));
    }

    public function test_admin_transactions_search_matches_display_number(): void
    {
        $u = $this->makeUser('Zed');
        DB::table('users')->where('id', $u->id)->update(['display_no' => 42]);
        $m = RvmMachine::create(['machine_code' => 'RVM-DN', 'name' => 'M', 'location_name' => 'Lobby', 'status' => 'active']);
        $s = RecyclingSession::create(['session_code' => 'SESS-DN', 'user_id' => $u->id, 'machine_id' => $m->id, 'status' => 'active', 'start_points' => 0]);
        Transaction::create([
            'session_id' => $s->id, 'user_id' => $u->id, 'machine_id' => $m->id,
            'material_selected' => 'plastic', 'ai_detected_type' => 'plastic',
            'is_valid' => 1, 'weight_grams' => 0, 'points_earned' => 5, 'points_deducted' => 0,
        ]);
        $this->asAdmin();

        foreach (['USR-0042', '42'] as $q) {
            $rows = $this->getJson('/api/admin/transactions?search=' . $q)->assertOk()->json('transactions.data');
            $this->assertCount(1, $rows, "search $q");
            $this->assertSame(42, $rows[0]['user']['display_no']);
        }
    }

    public function test_admin_users_search_and_export_use_display_number(): void
    {
        $u = $this->makeUser('Zed');
        $this->makeUser('Other');
        DB::table('users')->where('id', $u->id)->update(['display_no' => 42]);
        $this->asAdmin();

        $rows = $this->getJson('/api/admin/users?search=USR-0042')->assertOk()->json('users.data');
        $this->assertSame(['Zed'], array_column($rows, 'name'));

        $table = $this->excelTable($this->get('/api/admin/users/export?search=Zed'));
        $this->assertSame('USR-0042', $table[1][0]);
    }
}
