<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Turns one admin table (the rows the admin is looking at, with the same
 * filters) into a plain .xlsx: a title line, the filters that were applied,
 * then a bold header row with autofilter and frozen panes.
 */
class AdminTableExport
{
    /**
     * @param array<string, string>      $filters label => value, only the active ones
     * @param array<int, string>         $headers
     * @param iterable<array<int, mixed>> $rows
     */
    public function download(string $title, string $filenamePrefix, array $filters, array $headers, iterable $rows): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title, 0, 31));

        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'Exported: ' . now()->format('d M Y H:i'));

        $line = 3;
        $filterText = [];
        foreach ($filters as $label => $value) {
            if ($value !== null && $value !== '') $filterText[] = "{$label}: {$value}";
        }
        $sheet->setCellValue("A{$line}", 'Filters: ' . ($filterText ? implode(' | ', $filterText) : 'none (all data)'));
        $sheet->getStyle('A2:A3')->getFont()->getColor()->setRGB('555555');

        $headerRow = $line + 2;
        $lastCol = Coordinate::stringFromColumnIndex(max(2, count($headers)));
        // Merged so the long title lines don't stretch column A when the
        // columns are auto-sized (merged cells are left out of that).
        foreach ([1, 2, 3] as $r) $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
        foreach (array_values($headers) as $i => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . $headerRow, $header);
        }
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');

        // setCellValue per cell: fromArray() would turn a genuine 0 into a blank.
        $row = $headerRow;
        foreach ($rows as $values) {
            $row++;
            foreach (array_values($values) as $i => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . $row, $value);
            }
        }

        $sheet->setAutoFilter("A{$headerRow}:{$lastCol}" . max($row, $headerRow));
        $sheet->freezePane('A' . ($headerRow + 1));
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $filename = $filenamePrefix . '_' . now()->format('Y-m-d') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
