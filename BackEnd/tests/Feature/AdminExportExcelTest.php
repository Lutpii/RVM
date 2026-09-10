<?php

namespace Tests\Feature;

use App\Models\RecyclingSession;
use App\Models\RvmMachine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AdminExportExcelTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function makeUser(array $overrides = []): User
    {
        self::$seq++;
        return User::create(array_merge([
            'name' => 'User ' . self::$seq,
            'email' => 'user' . self::$seq . '@test.local',
            'phone' => '01' . str_pad((string) self::$seq, 9, '0', STR_PAD_LEFT),
            'password_hash' => bcrypt('password'),
            'role' => 'user',
            'is_verified' => 1,
            'total_points' => 0,
        ], $overrides));
    }

    private function actingAsAdmin(): User
    {
        $admin = $this->makeUser(['name' => 'Admin', 'role' => 'admin']);
        Sanctum::actingAs($admin, ['*']);
        return $admin;
    }

    public function test_export_excel_produces_a_readable_xlsx_with_borders_and_autosize_columns(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser();
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(),
            'name' => 'Test Machine',
            'location_name' => 'Test Lobby',
            'status' => 'active',
        ]);
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(),
            'user_id' => $user->id,
            'machine_id' => $machine->id,
            'status' => 'active',
            'start_points' => 0,
        ]);
        Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum',
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0,
        ]);

        $response = $this->get('/api/admin/export-excel')->assertOk();
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $tmpFile = tempnam(sys_get_temp_dir(), 'rvm_export_test_') . '.xlsx';
        file_put_contents($tmpFile, $response->streamedContent());

        $spreadsheet = IOFactory::load($tmpFile);
        $sheet = $spreadsheet->getActiveSheet();

        // The report now leads with a header + summary section (see
        // FormalReportService) before the transaction detail table, so its
        // 'ID' header is no longer at a fixed row — found by scanning rather
        // than assuming A1, the same way FormalReportServiceTest does.
        $detailHeaderRow = null;
        for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
            if ($sheet->getCell("A{$r}")->getValue() === 'ID') { $detailHeaderRow = $r; break; }
        }
        $this->assertNotNull($detailHeaderRow, 'Expected a transaction-details "ID" header row');

        $this->assertSame('RVM Recycling Report', $sheet->getCell('A1')->getValue());
        $this->assertSame('Carbon Saved (kg)', $sheet->getCell("I{$detailHeaderRow}")->getValue());
        $this->assertSame(0.226, $sheet->getCell('I' . ($detailHeaderRow + 1))->getValue());

        $borderStyle = $sheet->getStyle("A{$detailHeaderRow}")->getBorders()->getBottom()->getBorderStyle();
        $this->assertNotSame(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE, $borderStyle);

        // The reader doesn't reconstruct the AutoSize flag itself (bestFit is
        // a hint, not a re-computable property) — the real evidence that
        // autosizing took effect is a concrete, calculated column width.
        $this->assertGreaterThan(0, $sheet->getColumnDimension('A')->getWidth());

        $this->assertSame(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            $sheet->getStyle("A{$detailHeaderRow}")->getAlignment()->getHorizontal()
        );
        $this->assertSame(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            $sheet->getStyle('A' . ($detailHeaderRow + 1))->getAlignment()->getHorizontal()
        );

        unlink($tmpFile);
    }

    public function test_export_excel_filters_by_date_range(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser();
        $machine = RvmMachine::create([
            'machine_code' => 'RVM-TEST-' . uniqid(), 'name' => 'Test Machine',
            'location_name' => 'Test Lobby', 'status' => 'active',
        ]);
        $session = RecyclingSession::create([
            'session_code' => 'SESS-' . uniqid(), 'user_id' => $user->id,
            'machine_id' => $machine->id, 'status' => 'active', 'start_points' => 0,
        ]);
        $inRange = Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => 'aluminum', 'ai_detected_type' => 'aluminum',
            'is_valid' => 1, 'weight_grams' => 15, 'points_earned' => 15, 'points_deducted' => 0,
        ]);
        $inRange->created_at = '2026-06-15 10:00:00';
        $inRange->save();

        $outOfRange = Transaction::create([
            'session_id' => $session->id, 'user_id' => $user->id, 'machine_id' => $machine->id,
            'material_selected' => 'plastic', 'ai_detected_type' => 'plastic',
            'is_valid' => 1, 'weight_grams' => 20, 'points_earned' => 10, 'points_deducted' => 0,
        ]);
        $outOfRange->created_at = '2026-01-01 10:00:00';
        $outOfRange->save();

        $response = $this->get('/api/admin/export-excel?date_from=2026-06-01&date_to=2026-06-30')->assertOk();

        $tmpFile = tempnam(sys_get_temp_dir(), 'rvm_export_test_') . '.xlsx';
        file_put_contents($tmpFile, $response->streamedContent());
        $spreadsheet = IOFactory::load($tmpFile);
        $sheet = $spreadsheet->getActiveSheet();

        $detailHeaderRow = null;
        for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
            if ($sheet->getCell("A{$r}")->getValue() === 'ID') { $detailHeaderRow = $r; break; }
        }
        $this->assertSame($inRange->id, $sheet->getCell('A' . ($detailHeaderRow + 1))->getValue());
        $this->assertSame($detailHeaderRow + 1, $sheet->getHighestRow(), 'Only the in-range transaction should appear');

        unlink($tmpFile);
    }
}
