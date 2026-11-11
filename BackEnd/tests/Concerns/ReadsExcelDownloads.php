<?php
namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait ReadsExcelDownloads
{
    private function excelSheet(TestResponse $response): Worksheet
    {
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());
        try {
            return IOFactory::load($path)->getActiveSheet();
        } finally {
            @unlink($path);
        }
    }

    /**
     * Rows of the downloaded .xlsx below the title block: [0] = header row,
     * then one array per data row.
     */
    protected function excelTable(TestResponse $response): array
    {
        $rows = $this->excelSheet($response)->toArray(null, false, false, false);

        // Title, exported-at and filters lines, then a blank row, then the header.
        return array_slice($rows, 4);
    }

    protected function excelFiltersLine(TestResponse $response): string
    {
        return (string) $this->excelSheet($response)->getCell('A3')->getValue();
    }
}
