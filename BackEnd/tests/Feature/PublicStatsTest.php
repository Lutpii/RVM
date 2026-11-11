<?php
namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicStatsTest extends TestCase
{
    use RefreshDatabase;

    private function tx(string $material, bool $valid = true, $at = null): void
    {
        $u = User::first() ?? User::create([
            'name' => 'A', 'email' => 'a@test.local', 'phone' => '0123456789',
            'password_hash' => bcrypt('x'), 'role' => 'user', 'is_verified' => 1, 'total_points' => 0,
        ]);
        $m = RvmMachine::first();
        $s = RecyclingSession::create(['session_code' => 'S-' . uniqid(), 'user_id' => $u->id, 'machine_id' => $m->id, 'status' => 'completed', 'start_points' => 0]);
        $t = Transaction::create([
            'session_id' => $s->id, 'user_id' => $u->id, 'machine_id' => $m->id,
            'material_selected' => $material, 'ai_detected_type' => $material,
            'is_valid' => $valid ? 1 : 0, 'weight_grams' => 0, 'points_earned' => $valid ? 10 : 0, 'points_deducted' => 0,
        ]);
        if ($at) $t->forceFill(['created_at' => $at])->save();
    }

    public function test_public_stats_need_no_login_and_count_only_accepted_items(): void
    {
        Cache::flush();
        $this->tx('plastic');
        $this->tx('aluminum');
        $this->tx('paper');
        $this->tx('unknown', false);
        $this->tx('plastic', true, now()->subMonthNoOverflow()->startOfMonth()); // last month

        $res = $this->getJson('/api/stats/public')->assertOk();

        $res->assertJsonPath('stats.items_recycled', 4)
            ->assertJsonPath('stats.month.items_recycled', 3)
            ->assertJsonPath('stats.machines', RvmMachine::count());
        // 0.038 + 0.226 + 0.04 this month
        $this->assertEqualsWithDelta(0.30, $res->json('stats.month.carbon_saved_kg'), 0.01);
        $this->assertEqualsWithDelta(0.23, $res->json('stats.month.carbon_by_material_kg.aluminum'), 0.01);
        $this->assertSame((int) now()->month, $res->json('stats.month.month'));
        $this->assertArrayNotHasKey('users', $res->json('stats'));
    }
}
