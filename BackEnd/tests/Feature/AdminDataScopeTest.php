<?php
namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FormalReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\ReadsExcelDownloads;
use Tests\TestCase;

class AdminDataScopeTest extends TestCase
{
    use RefreshDatabase, ReadsExcelDownloads;

    private static int $seq = 0;

    private function makeUser(string $role = 'user'): User
    {
        self::$seq++;
        return User::create([
            'name' => 'U' . self::$seq, 'email' => 'ds' . self::$seq . '@test.local',
            'phone' => '02' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => $role, 'is_verified' => 1, 'total_points' => 0,
        ]);
    }

    private function seedData(): void
    {
        Cache::flush();
        $user = $this->makeUser();
        $real = RvmMachine::create(['machine_code' => 'RVM-' . uniqid(), 'name' => 'Real', 'location_name' => 'L', 'status' => 'active']);
        $demo = RvmMachine::create(['machine_code' => 'RVM-' . uniqid(), 'name' => 'Demo', 'location_name' => 'L', 'status' => 'active', 'is_demo' => true]);
        foreach ([[$real, 'plastic'], [$real, 'plastic'], [$demo, 'glass']] as [$m, $mat]) {
            $s = RecyclingSession::create([
                'session_code' => 'SESS-' . uniqid(), 'user_id' => $user->id, 'machine_id' => $m->id,
                'status' => 'completed', 'start_points' => 0, 'total_items' => 1, 'points_earned' => 10,
            ]);
            Transaction::create([
                'session_id' => $s->id, 'user_id' => $user->id, 'machine_id' => $m->id,
                'material_selected' => $mat, 'ai_detected_type' => $mat, 'ai_confidence' => 0.9,
                'is_valid' => 1, 'weight_grams' => 10, 'points_earned' => 10, 'points_deducted' => 0,
            ]);
        }
        Sanctum::actingAs($this->makeUser('admin'), ['*']);
    }

    public function test_transactions_list_scopes(): void
    {
        $this->seedData();
        $this->assertCount(2, $this->getJson('/api/admin/transactions?data=real')->json('transactions.data'));
        $demo = $this->getJson('/api/admin/transactions?data=demo');
        $this->assertCount(1, $demo->json('transactions.data'));
        $this->assertTrue($demo->json('transactions.data.0.is_demo'));
        $this->assertSame(1, $demo->json('summary.items'));
        $this->assertCount(3, $this->getJson('/api/admin/transactions')->json('transactions.data'));
        $this->assertCount(3, $this->getJson('/api/admin/transactions?data=foo')->json('transactions.data'));
        $this->assertCount(1, $this->getJson('/api/admin/sessions?data=demo')->json('sessions.data'));
    }

    public function test_dashboard_stats_scope_and_cache_are_separate(): void
    {
        $this->seedData();
        $this->assertSame(1, $this->getJson('/api/admin/stats?data=demo')->json('stats.total_transactions'));
        $this->assertSame(2, $this->getJson('/api/admin/stats?data=real')->json('stats.total_transactions'));
        $this->assertSame(3, $this->getJson('/api/admin/stats')->json('stats.total_transactions'));
    }

    public function test_chart_data_scope(): void
    {
        $this->seedData();
        $demo = $this->getJson('/api/admin/chart-data?data=demo')->json('breakdown');
        $this->assertGreaterThan(0, $demo['glass']);
        $this->assertEquals(0, $demo['plastic']);
        $real = $this->getJson('/api/admin/chart-data?data=real')->json('breakdown');
        $this->assertEquals(0, $real['glass']);
        $this->assertGreaterThan(0, $real['plastic']);
    }

    public function test_transactions_export_lists_scope(): void
    {
        $this->seedData();
        $r = $this->get('/api/admin/transactions/export?data=demo');
        $this->assertStringContainsString('Data: Demo only', $this->excelFiltersLine($r));
        $this->assertCount(2, $this->excelTable($r));
    }

    public function test_formal_report_scope(): void
    {
        $this->seedData();
        $sheet = (new FormalReportService())->build(null, null, 'real')->getActiveSheet();
        $glass = 0;
        foreach ($sheet->toArray(null, false, false, false) as $row) {
            if (in_array('Demo', $row, true)) $glass++;
        }
        $this->assertSame(1, $glass, 'only the per-machine summary row, no detail row');
        $all = (new FormalReportService())->build(null, null)->getActiveSheet();
        $count = 0;
        foreach ($all->toArray(null, false, false, false) as $row) {
            if (in_array('Demo', $row, true)) $count++;
        }
        $this->assertSame(2, $count, 'summary row + the demo detail row');
    }
}
