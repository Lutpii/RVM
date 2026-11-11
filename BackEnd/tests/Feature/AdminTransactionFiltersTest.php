<?php
namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTransactionFiltersTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function makeUser(string $name, string $role = 'user'): User
    {
        self::$seq++;
        return User::create([
            'name' => $name, 'email' => 'u' . self::$seq . '@test.local',
            'phone' => '01' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => $role, 'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    private function makeMachine(string $name): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'RVM-' . uniqid(), 'name' => $name,
            'location_name' => 'Lobby', 'status' => 'active',
        ]);
    }

    private function makeTx(RvmMachine $m, User $u, array $o = []): Transaction
    {
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $u->id,
            'machine_id' => $m->id, 'status' => 'active', 'start_points' => 0,
        ]);
        $createdAt = $o['created_at'] ?? null;
        unset($o['created_at']);
        $tx = Transaction::create(array_merge([
            'session_id' => $session->id, 'user_id' => $u->id, 'machine_id' => $m->id,
            'material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum',
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0,
        ], $o));
        if ($createdAt) {
            $tx->forceFill(['created_at' => $createdAt])->save();
        }
        return $tx;
    }

    private function asAdmin(): void
    {
        Sanctum::actingAs($this->makeUser('Admin', 'admin'), ['*']);
    }

    public function test_filters_by_machine(): void
    {
        $a = $this->makeMachine('A'); $b = $this->makeMachine('B'); $u = $this->makeUser('Zed');
        $this->makeTx($a, $u); $this->makeTx($a, $u); $this->makeTx($b, $u);
        $this->asAdmin();

        $res = $this->getJson('/api/admin/transactions?machine_id=' . $b->id)->assertOk();
        $this->assertCount(1, $res->json('transactions.data'));
    }

    public function test_filters_by_date_range(): void
    {
        $m = $this->makeMachine('A'); $u = $this->makeUser('Zed');
        $this->makeTx($m, $u, ['created_at' => '2026-11-01 10:00:00']);
        $five = $this->makeTx($m, $u, ['created_at' => '2026-11-05 23:30:00']);
        $this->makeTx($m, $u, ['created_at' => '2026-11-09 10:00:00']);
        $this->asAdmin();

        $res = $this->getJson('/api/admin/transactions?date_from=2026-11-04&date_to=2026-11-06')->assertOk();
        $ids = collect($res->json('transactions.data'))->pluck('id')->all();
        $this->assertSame([$five->id], $ids);

        // Inclusive bounds: a single-day window on the 5th still matches.
        $res = $this->getJson('/api/admin/transactions?date_from=2026-11-05&date_to=2026-11-05')->assertOk();
        $this->assertCount(1, $res->json('transactions.data'));
    }

    public function test_filters_combine_with_status_and_search(): void
    {
        $a = $this->makeMachine('A'); $b = $this->makeMachine('B');
        $budi = $this->makeUser('Budi'); $sari = $this->makeUser('Sari');
        $target = $this->makeTx($a, $budi, ['is_valid' => 0]);
        $this->makeTx($a, $budi, ['is_valid' => 1]);
        $this->makeTx($a, $sari, ['is_valid' => 0]);
        $this->makeTx($b, $budi, ['is_valid' => 0]);
        $this->asAdmin();

        $res = $this->getJson("/api/admin/transactions?machine_id={$a->id}&status=rejected&search=Budi")->assertOk();
        $ids = collect($res->json('transactions.data'))->pluck('id')->all();
        $this->assertSame([$target->id], $ids);
    }

    public function test_invalid_filters_rejected(): void
    {
        $this->asAdmin();
        $this->getJson('/api/admin/transactions?date_from=notadate')->assertStatus(422);
        $this->getJson('/api/admin/transactions?machine_id=abc')->assertStatus(422);
        $this->getJson('/api/admin/transactions?date_from=2026-11-06&date_to=2026-11-04')->assertStatus(422);
    }

    public function test_summary_counts_everything_the_filters_match(): void
    {
        $m = $this->makeMachine('M1');
        $u = $this->makeUser('Alice');
        $this->makeTx($m, $u, ['points_earned' => 10, 'weight_grams' => 20]);
        $this->makeTx($m, $u, ['points_earned' => 5, 'weight_grams' => 30]);
        $this->makeTx($m, $u, ['material_selected' => 'unknown', 'ai_detected_type' => 'unknown',
            'is_valid' => 0, 'points_earned' => 0, 'weight_grams' => 0]);
        $this->asAdmin();

        $this->getJson('/api/admin/transactions?per_page=1')
            ->assertOk()
            ->assertJsonPath('summary.items', 3)
            ->assertJsonPath('summary.accepted', 2)
            ->assertJsonPath('summary.rejected', 1)
            ->assertJsonPath('summary.points', 15)
            ->assertJsonMissingPath('summary.grams');

        $this->getJson('/api/admin/transactions?status=rejected')
            ->assertJsonPath('summary.items', 1)
            ->assertJsonPath('summary.accepted', 0);
    }

    public function test_search_finds_a_user_by_the_id_shown_in_the_table(): void
    {
        $m = $this->makeMachine('M1');
        $alice = $this->makeUser('Alice');
        $bob = $this->makeUser('Bob');
        $this->makeTx($m, $alice);
        $this->makeTx($m, $bob);
        $this->asAdmin();

        $this->getJson('/api/admin/transactions?search=USR-' . str_pad((string) $bob->id, 4, '0', STR_PAD_LEFT))
            ->assertOk()
            ->assertJsonCount(1, 'transactions.data')
            ->assertJsonPath('transactions.data.0.user_id', $bob->id);

        $this->getJson('/api/admin/transactions?search=' . urlencode($alice->email))
            ->assertJsonCount(1, 'transactions.data')
            ->assertJsonPath('transactions.data.0.user_id', $alice->id);
    }
}
