<?php

namespace Tests\Feature;

use App\Services\ProfessionalSpreadsheetExportService;
use Tests\TestCase;
use ZipArchive;

class ProfessionalSpreadsheetExportTest extends TestCase
{
    public function test_it_generates_a_styled_safe_and_filterable_excel_workbook(): void
    {
        $response = app(ProfessionalSpreadsheetExportService::class)->download(
            'sample-report.xlsx',
            'VMC Auto Parts & Accessories',
            'Inventory Stock Report',
            [
                ['label' => 'Filters', 'value' => 'All inventory records'],
                ['label' => 'Generated', 'value' => 'October 30, 2026 10:00 AM'],
                ['label' => 'Records', 'value' => 1],
            ],
            [
                ['key' => 'code', 'label' => 'Variant Code', 'type' => 'text', 'width' => 18],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'width' => 28],
                ['key' => 'date', 'label' => 'Date', 'type' => 'date', 'width' => 14],
                ['key' => 'quantity', 'label' => 'Quantity', 'type' => 'integer', 'width' => 12],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'currency', 'width' => 16],
            ],
            [[
                'code' => '00123',
                'description' => '=2+2',
                'date' => '2026-10-30',
                'quantity' => 5,
                'amount' => 1234.50,
            ]],
            'Inventory'
        );

        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );
        $this->assertStringContainsString('sample-report.xlsx', $response->headers->get('content-disposition'));

        $temporaryFile = tempnam(sys_get_temp_dir(), 'acrylink-xlsx-');
        file_put_contents($temporaryFile, $response->getContent());
        $zip = new ZipArchive;

        try {
            $this->assertTrue($zip->open($temporaryFile) === true);
            $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
            $styles = $zip->getFromName('xl/styles.xml');
            $sharedStrings = $zip->getFromName('xl/sharedStrings.xml') ?: '';
            $workbook = $zip->getFromName('xl/workbook.xml');

            $this->assertIsString($worksheet);
            $this->assertIsString($styles);
            $this->assertIsString($workbook);
            $this->assertStringContainsString('<pane', $worksheet);
            $this->assertStringContainsString('topLeftCell="A8"', $worksheet);
            $this->assertStringContainsString('<autoFilter ref="A7:E8"', $worksheet);
            $this->assertStringContainsString('<mergeCell ref="A1:E1"', $worksheet);
            $this->assertStringContainsString('<mergeCell ref="A2:E2"', $worksheet);
            $this->assertStringContainsString('FF1D2D4F', $styles);
            $this->assertStringContainsString('FFE8470B', $styles);
            $this->assertStringContainsString('₱#,##0.00', html_entity_decode($styles));
            $this->assertStringContainsString('00123', $sharedStrings.$worksheet);
            $this->assertStringContainsString("'=2+2", $sharedStrings.$worksheet);
            $this->assertStringNotContainsString('<f>', $worksheet);
            $this->assertStringNotContainsString('<f ', $worksheet);
            $this->assertStringContainsString('Inventory Stock Report', $sharedStrings.$worksheet);
        } finally {
            $zip->close();
            @unlink($temporaryFile);
        }
    }

    public function test_it_generates_a_valid_workbook_when_filters_return_no_records(): void
    {
        $response = app(ProfessionalSpreadsheetExportService::class)->download(
            'empty.xlsx',
            'VMC Auto Parts & Accessories',
            'Sales Report',
            [['label' => 'Report period', 'value' => 'October 2026']],
            [
                ['key' => 'receipt', 'label' => 'Receipt', 'type' => 'text'],
                ['key' => 'total', 'label' => 'Total', 'type' => 'currency'],
            ],
            [],
            'Sales Report'
        );

        $this->assertStringStartsWith('PK', $response->getContent());
        $this->assertGreaterThan(1000, strlen($response->getContent()));
    }
}
