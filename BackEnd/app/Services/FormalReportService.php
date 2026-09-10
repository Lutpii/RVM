<?php
// BackEnd/app/Services/FormalReportService.php
namespace App\Services;

use App\Models\RewardRedemption;
use App\Models\RvmMachine;
use App\Models\Transaction;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FormalReportService
{
    private const MATERIALS = ['plastic', 'aluminum', 'paper', 'glass'];

    // Worksheet::fromArray() treats any value loosely equal to its $nullValue
    // marker (default PHP null) as "leave this cell blank" — and in PHP,
    // 0 == null and 0.0 == null are both true, so a genuine zero count or a
    // 0.0 reject rate silently vanished into an empty cell instead of a 0.
    // setCellValue() has no such gotcha; writing row-by-row through it here
    // sidesteps the whole class of bug rather than working around one call.
    private function writeRow(Worksheet $sheet, int $row, array $values): void
    {
        $col = 'A';
        foreach ($values as $value) {
            $sheet->setCellValue("{$col}{$row}", $value);
            $col++;
        }
    }

    // Builds a formal report — header, a per-machine summary (with a grand
    // total row), a standalone points-redeemed line, then the transaction
    // detail table — as a single Spreadsheet. Shared by the download and
    // email-delivery endpoints so both always produce identical reports.
    public function build(?string $dateFrom, ?string $dateTo): Spreadsheet
    {
        $query = Transaction::with(['user', 'machine'])->latest();
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        $transactions = $query->get();

        $machines = RvmMachine::orderBy('name')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;

        $row = $this->writeHeader($sheet, $row, $dateFrom, $dateTo, $machines);
        $row++; // spacer

        $row = $this->writeSummary($sheet, $row, $machines, $transactions);
        $row++; // spacer

        $row = $this->writePointsRedeemedLine($sheet, $row, $dateFrom, $dateTo);
        $row++; // spacer

        $this->writeDetails($sheet, $row, $transactions);

        return $spreadsheet;
    }

    private function writeHeader(Worksheet $sheet, int $row, ?string $dateFrom, ?string $dateTo, $machines): int
    {
        $sheet->setCellValue("A{$row}", 'RVM Recycling Report');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $row++;

        $sheet->setCellValue("A{$row}", 'Universiti Malaysia Pahang Al-Sultan Abdullah (UMPSA)');
        $row++;

        $period = 'Period: ' . ($dateFrom ?: 'earliest') . ' to ' . ($dateTo ?: 'latest');
        $sheet->setCellValue("A{$row}", $period);
        $row++;

        $machineNames = $machines->pluck('name')->implode(', ') ?: 'None';
        $sheet->setCellValue("A{$row}", "Machines: {$machineNames}");
        $row++;

        return $row;
    }

    private function writeSummary(Worksheet $sheet, int $row, $machines, $transactions): int
    {
        $sheet->setCellValue("A{$row}", 'SUMMARY');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $headers = ['Machine', 'Plastic', 'Aluminum', 'Paper', 'Glass', 'Carbon Saved (kg)', 'Reject Rate (%)', 'Points Earned', 'Unique Users'];
        $this->writeRow($sheet, $row, $headers);
        $headerRow = $row;
        $row++;

        $grand = ['plastic' => 0, 'aluminum' => 0, 'paper' => 0, 'glass' => 0, 'carbon' => 0.0, 'valid' => 0, 'invalid' => 0, 'points' => 0];
        $allUserIds = collect();

        foreach ($machines as $machine) {
            $machineTx = $transactions->where('machine_id', $machine->id);

            $counts = [];
            $carbon = 0.0;
            foreach (self::MATERIALS as $material) {
                $count = $machineTx->where('material_selected', $material)->where('is_valid', true)->count();
                $counts[$material] = $count;
                $carbon += $count * CarbonService::forMaterial($material);
                $grand[$material] += $count;
            }

            $total = $machineTx->count();
            $invalid = $machineTx->where('is_valid', false)->count();
            $rejectRate = $total > 0 ? round(($invalid / $total) * 100, 1) : 0.0;
            $pointsEarned = (int) $machineTx->where('is_valid', true)->sum('points_earned');
            $uniqueUsers = $machineTx->pluck('user_id')->unique()->count();

            $this->writeRow($sheet, $row, [
                $machine->name, $counts['plastic'], $counts['aluminum'], $counts['paper'], $counts['glass'],
                round($carbon, 3), $rejectRate, $pointsEarned, $uniqueUsers,
            ]);
            $row++;

            $grand['carbon'] += $carbon;
            $grand['valid'] += $total - $invalid;
            $grand['invalid'] += $invalid;
            $grand['points'] += $pointsEarned;
            $allUserIds = $allUserIds->merge($machineTx->pluck('user_id'));
        }

        $grandTotal = $grand['valid'] + $grand['invalid'];
        $grandRejectRate = $grandTotal > 0 ? round(($grand['invalid'] / $grandTotal) * 100, 1) : 0.0;
        $this->writeRow($sheet, $row, [
            'TOTAL', $grand['plastic'], $grand['aluminum'], $grand['paper'], $grand['glass'],
            round($grand['carbon'], 3), $grandRejectRate, $grand['points'], $allUserIds->unique()->count(),
        ]);
        $totalRow = $row;
        $sheet->getStyle("A{$totalRow}:I{$totalRow}")->getFont()->setBold(true);
        $row++;

        $lastCol = 'I';
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFont()->setBold(true);

        return $row;
    }

    private function writePointsRedeemedLine(Worksheet $sheet, int $row, ?string $dateFrom, ?string $dateTo): int
    {
        $query = RewardRedemption::query();
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        $pointsRedeemed = (int) $query->sum('points_spent');

        $sheet->setCellValue("A{$row}", "Total Points Redeemed (all machines): {$pointsRedeemed}");
        $row++;

        return $row;
    }

    private function writeDetails(Worksheet $sheet, int $row, $transactions): int
    {
        $sheet->setCellValue("A{$row}", 'TRANSACTION DETAILS');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $headers = ['ID', 'Time', 'User', 'Machine', 'Material', 'AI Detected', 'AI Confidence %', 'Valid', 'Carbon Saved (kg)', 'Points Earned', 'Status'];
        $this->writeRow($sheet, $row, $headers);
        $headerRow = $row;
        $row++;

        foreach ($transactions as $t) {
            $this->writeRow($sheet, $row, [
                $t->id,
                $t->created_at?->toDateTimeString(),
                $t->user?->name ?? 'Guest',
                $t->machine?->name ?? '—',
                $t->material_selected,
                $t->ai_detected_type ?? '—',
                round(($t->ai_confidence ?? 0) * 100),
                $t->is_valid ? 'Valid' : 'Rejected',
                $t->is_valid ? CarbonService::forMaterial($t->material_selected) : 0.0,
                $t->points_earned,
                $t->is_valid ? 'OK' : 'REJECTED',
            ]);
            $row++;
        }

        $lastRow = max($row - 1, $headerRow);
        $lastCol = chr(ord('A') + count($headers) - 1); // 'K'
        $range = "A{$headerRow}:{$lastCol}{$lastRow}";

        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if ($lastRow > $headerRow) {
            $sheet->getStyle('A' . ($headerRow + 1) . ":{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $row;
    }
}
