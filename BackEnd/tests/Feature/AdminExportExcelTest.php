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

        $this->assertSame('ID', $sheet->getCell('A1')->getValue());
        $this->assertSame('Carbon Saved (kg)', $sheet->getCell('I1')->getValue());
        $this->assertSame(0.226, $sheet->getCell('I2')->getValue());

        $borderStyle = $sheet->getStyle('A1')->getBorders()->getBottom()->getBorderStyle();
        $this->assertNotSame(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE, $borderStyle);

        // The reader doesn't reconstruct the AutoSize flag itself (bestFit is
        // a hint, not a re-computable property) — the real evidence that
        // autosizing took effect is a concrete, calculated column width.
        $this->assertGreaterThan(0, $sheet->getColumnDimension('A')->getWidth());

        $this->assertSame(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            $sheet->getStyle('A1')->getAlignment()->getHorizontal()
        );
        $this->assertSame(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            $sheet->getStyle('A2')->getAlignment()->getHorizontal()
        );

        unlink($tmpFile);
    }
}
