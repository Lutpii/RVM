<?php
// BackEnd/tests/Unit/FormalReportServiceTest.php
namespace Tests\Unit;

use App\Models\RecyclingSession;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FormalReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormalReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function makeUser(array $overrides = []): User
    {
        self::$seq++;
        return User::create(array_merge([
            'name' => 'User ' . self::$seq, 'email' => 'user' . self::$seq . '@test.local',
            'phone' => '01' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'), 'role' => 'user',
            'is_verified' => 1, 'total_points' => 0,
        ], $overrides));
    }

    private function makeMachine(string $name): RvmMachine
    {
        return RvmMachine::create([
            'machine_code' => 'RVM-' . uniqid(), 'name' => $name,
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
    }

    private function makeTransaction(RvmMachine $machine, User $user, array $overrides = []): Transaction
    {
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 0,
        ]);
        return Transaction::create(array_merge([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum',
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0,
        ], $overrides));
    }

    /** Scans column A for the row whose value matches $needle, so tests don't hardcode row numbers. */
    private function findRow($sheet, string $needle): ?int
    {
        $highestRow = $sheet->getHighestRow();
        for ($r = 1; $r <= $highestRow; $r++) {
            if ($sheet->getCell("A{$r}")->getValue() === $needle) return $r;
        }
        return null;
    }

    public function test_header_includes_title_period_and_machine_names(): void
    {
        $machine = $this->makeMachine('RVM UMPSA Gambang');
        $spreadsheet = (new FormalReportService())->build('2026-09-01', '2026-09-30');
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('RVM Recycling Report', $sheet->getCell('A1')->getValue());
        $this->assertStringContainsString('2026-09-01', $sheet->getCell('A3')->getValue());
        $this->assertStringContainsString('2026-09-30', $sheet->getCell('A3')->getValue());
        $this->assertStringContainsString('RVM UMPSA Gambang', $sheet->getCell('A4')->getValue());
    }

    public function test_summary_row_totals_are_correct_per_machine_and_grand_total(): void
    {
        $gambang = $this->makeMachine('RVM UMPSA Gambang');
        $pekan = $this->makeMachine('RVM UMPSA Pekan');
        $user1 = $this->makeUser();
        $user2 = $this->makeUser();

        $this->makeTransaction($gambang, $user1, ['material_selected' => 'aluminum', 'is_valid' => 1, 'points_earned' => 15]);
        $this->makeTransaction($gambang, $user1, ['material_selected' => 'plastic', 'is_valid' => 1, 'points_earned' => 10]);
        $this->makeTransaction($gambang, $user2, ['material_selected' => 'plastic', 'is_valid' => 0, 'points_earned' => 0]);
        $this->makeTransaction($pekan, $user2, ['material_selected' => 'paper', 'is_valid' => 1, 'points_earned' => 8]);

        $spreadsheet = (new FormalReportService())->build(null, null);
        $sheet = $spreadsheet->getActiveSheet();

        $summaryHeaderRow = $this->findRow($sheet, 'Machine');
        $this->assertNotNull($summaryHeaderRow, 'Expected a "Machine" summary header row');

        // Rows are found by name, not by fixed offset — the DB migration seeds
        // a permanent default "RVM Machine 1" row (see 2026_05_07_000002_...),
        // so the summary always has at least that extra machine alongside
        // whatever this test creates, and 'name' ordering interleaves it.
        $gambangRow = $this->findRow($sheet, 'RVM UMPSA Gambang');
        $this->assertNotNull($gambangRow);
        $this->assertSame(1, $sheet->getCell("B{$gambangRow}")->getValue()); // Plastic (valid only)
        $this->assertSame(1, $sheet->getCell("C{$gambangRow}")->getValue()); // Aluminum
        $this->assertEqualsWithDelta(33.3, $sheet->getCell("G{$gambangRow}")->getValue(), 0.1); // 1/3 rejected
        $this->assertSame(25, $sheet->getCell("H{$gambangRow}")->getValue()); // points earned (15+10)
        $this->assertSame(2, $sheet->getCell("I{$gambangRow}")->getValue()); // unique users

        $pekanRow = $this->findRow($sheet, 'RVM UMPSA Pekan');
        $this->assertNotNull($pekanRow);
        $this->assertSame(1, $sheet->getCell("D{$pekanRow}")->getValue()); // Paper
        $this->assertSame(0.0, $sheet->getCell("G{$pekanRow}")->getValue()); // no rejects

        $totalRow = $this->findRow($sheet, 'TOTAL');
        $this->assertNotNull($totalRow);
        $this->assertSame(1, $sheet->getCell("C{$totalRow}")->getValue()); // aluminum total (gambang only)
        $this->assertSame(1, $sheet->getCell("B{$totalRow}")->getValue()); // plastic total (only gambang's valid one counts)
        $this->assertSame(33, $sheet->getCell("H{$totalRow}")->getValue()); // 25 + 8
        $this->assertSame(2, $sheet->getCell("I{$totalRow}")->getValue()); // unique users overall (user1, user2)
    }

    public function test_total_points_redeemed_line_reflects_reward_redemption_rows_in_range(): void
    {
        $machine = $this->makeMachine('RVM UMPSA Gambang');
        $user = $this->makeUser(['total_points' => 100]);
        $item = RewardItem::create(['name' => 'Coffee Voucher', 'points_cost' => 30, 'is_active' => true]);
        RewardRedemption::create(['user_id' => $user->id, 'reward_item_id' => $item->id, 'reward_name' => $item->name, 'points_spent' => 30]);
        RewardRedemption::create(['user_id' => $user->id, 'reward_item_id' => $item->id, 'reward_name' => $item->name, 'points_spent' => 12]);

        $spreadsheet = (new FormalReportService())->build(null, null);
        $sheet = $spreadsheet->getActiveSheet();

        $row = $this->findRow($sheet, 'Total Points Redeemed (all machines): 42');
        $this->assertNotNull($row, 'Expected the points-redeemed summary line to show 42 (30+12)');
    }

    public function test_date_filter_excludes_transactions_outside_the_range(): void
    {
        $machine = $this->makeMachine('RVM UMPSA Gambang');
        $user = $this->makeUser();

        $inRange = $this->makeTransaction($machine, $user);
        $inRange->created_at = '2026-09-15 10:00:00';
        $inRange->save();

        $outOfRange = $this->makeTransaction($machine, $user);
        $outOfRange->created_at = '2026-08-01 10:00:00';
        $outOfRange->save();

        $spreadsheet = (new FormalReportService())->build('2026-09-01', '2026-09-30');
        $sheet = $spreadsheet->getActiveSheet();

        $detailHeaderRow = $this->findRow($sheet, 'ID');
        $this->assertNotNull($detailHeaderRow);
        $this->assertSame($inRange->id, $sheet->getCell('A' . ($detailHeaderRow + 1))->getValue());
        $this->assertSame($detailHeaderRow + 1, $sheet->getHighestRow(), 'Only the in-range transaction should appear');
    }

    public function test_details_table_has_borders_and_bold_header(): void
    {
        $machine = $this->makeMachine('RVM UMPSA Gambang');
        $user = $this->makeUser();
        $this->makeTransaction($machine, $user, ['material_selected' => 'aluminum', 'is_valid' => 1]);

        $spreadsheet = (new FormalReportService())->build(null, null);
        $sheet = $spreadsheet->getActiveSheet();

        $detailHeaderRow = $this->findRow($sheet, 'ID');
        $this->assertNotNull($detailHeaderRow);
        $this->assertSame('Carbon Saved (kg)', $sheet->getCell('I' . $detailHeaderRow)->getValue());
        $this->assertSame(0.226, $sheet->getCell('I' . ($detailHeaderRow + 1))->getValue());

        $borderStyle = $sheet->getStyle("A{$detailHeaderRow}")->getBorders()->getBottom()->getBorderStyle();
        $this->assertNotSame(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE, $borderStyle);
        $this->assertTrue($sheet->getStyle("A{$detailHeaderRow}")->getFont()->getBold());
        // Column autosize only computes a concrete width once the file is
        // actually written and re-read (setAutoSize just sets a flag on this
        // in-memory object) — the Feature test that round-trips through a
        // real HTTP download is where that gets verified.
    }
}
