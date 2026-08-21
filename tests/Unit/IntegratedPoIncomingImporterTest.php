<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Import\IntegratedPoIncomingImporter;
use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegratedPoIncomingImporterTest extends TestCase
{
    protected IntegratedPoIncomingImporter $importer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importer = new IntegratedPoIncomingImporter();
    }

    protected function createTempExcel(array $matrix): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($matrix as $rIdx => $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue($colLetter . ($rIdx + 1), $val);
                $colLetter++;
            }
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_po_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        return $tmpFile;
    }

    public function test_scenario_1_complete_receipt_creates_both_master_po_and_incoming()
    {
        $matrix = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-07-15', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 'KIP 1', 210, 1778700, 210, 1778700],
        ];

        $filePath = $this->createTempExcel($matrix);
        $result = $this->importer->parseAndAnalyze($filePath);
        @unlink($filePath);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['master_po_rows']);
        $this->assertCount(1, $result['incoming_rows']);

        $po = $result['master_po_rows'][0];
        $this->assertEquals(210, $po['qty']);
        $this->assertEquals(1778700, $po['amount']);
        $this->assertEquals('Complete', $po['status']);

        $inc = $result['incoming_rows'][0];
        $this->assertEquals(210, $inc['actual_received']);
        $this->assertEquals(210, $inc['target_order']);
        $this->assertEquals(0, $inc['pending_order']);
        $this->assertEquals('Complete', $inc['status_note']);
    }

    public function test_scenario_2_partial_receipt()
    {
        $matrix = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-07-20', '1312006', 'MAIN BOARD A', 'KI-TJT-0027', 'IDR', 8470, 'KIP 1', 200, 1694000, 126, 1067220],
        ];

        $filePath = $this->createTempExcel($matrix);
        $result = $this->importer->parseAndAnalyze($filePath);
        @unlink($filePath);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['master_po_rows']);
        $this->assertCount(1, $result['incoming_rows']);

        $po = $result['master_po_rows'][0];
        $this->assertEquals(200, $po['qty']);
        $this->assertEquals('Partial', $po['status']);

        $inc = $result['incoming_rows'][0];
        $this->assertEquals(126, $inc['actual_received']);
        $this->assertEquals(200, $inc['target_order']);
        $this->assertEquals(74, $inc['pending_order']);
        $this->assertEquals('Partial', $inc['status_note']);

        $preview = $result['preview_rows'][0];
        $this->assertEquals(74, $preview['outstanding_qty']);
    }

    public function test_scenario_3_not_received_creates_only_master_po()
    {
        $matrix = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C146', 'PT. SUMBER AGUNG', '2026-07-25', '1311010', 'SCREW B 4X12', 'KI-SAS-0006', 'USD', 2.50, 'KIP 2', 600, 1500, 0, 0],
        ];

        $filePath = $this->createTempExcel($matrix);
        $result = $this->importer->parseAndAnalyze($filePath);
        @unlink($filePath);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['master_po_rows']);
        $this->assertCount(0, $result['incoming_rows']); // Tidak membuat record incoming bernilai 0

        $po = $result['master_po_rows'][0];
        $this->assertEquals(600, $po['qty']);
        $this->assertEquals('Not Received', $po['status']);

        $preview = $result['preview_rows'][0];
        $this->assertTrue($preview['has_master_po']);
        $this->assertFalse($preview['has_incoming']);
        $this->assertEquals(600, $preview['outstanding_qty']);
    }

    public function test_scenario_4_unplanned_incoming_creates_incoming_with_warning()
    {
        $matrix = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C096', 'CV. BIMASAKTI ANEKA', '2026-07-28', '1314002', 'GESPER STRAPPING BAND', 'KI-BSA-0012', 'IDR', 95000, 'KIP 1', 0, 0, 120, 11400000],
        ];

        $filePath = $this->createTempExcel($matrix);
        $result = $this->importer->parseAndAnalyze($filePath);
        @unlink($filePath);

        $this->assertTrue($result['success']);
        $this->assertCount(0, $result['master_po_rows']); // Tidak ada plan
        $this->assertCount(1, $result['incoming_rows']);  // Dibuat sebagai unplanned incoming

        $inc = $result['incoming_rows'][0];
        $this->assertEquals(120, $inc['actual_received']);
        $this->assertEquals('Unplanned Incoming', $inc['status_note']);
        $this->assertEquals(1, $result['reconciliation']['unplanned_incoming']);
    }

    public function test_scenario_5_over_delivery()
    {
        $matrix = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-07-15', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 'KIP 1', 100, 847000, 120, 1016400],
        ];

        $filePath = $this->createTempExcel($matrix);
        $result = $this->importer->parseAndAnalyze($filePath);
        @unlink($filePath);

        $this->assertTrue($result['success']);
        $preview = $result['preview_rows'][0];
        $this->assertEquals('Over Delivery', $preview['status']);
        $this->assertEquals(0, $preview['outstanding_qty']);
    }

    public function test_reconciliation_sums_match_excel()
    {
        $matrix = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-07-15', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 'KIP 1', 210, 1778700, 210, 1778700],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-07-20', '1312006', 'MAIN BOARD A', 'KI-TJT-0027', 'IDR', 8470, 'KIP 1', 200, 1694000, 126, 1067220],
            ['C146', 'PT. SUMBER AGUNG', '2026-07-25', '1311010', 'SCREW B 4X12', 'KI-SAS-0006', 'IDR', 2500, 'KIP 2', 600, 1500000, 0, 0],
        ];

        $filePath = $this->createTempExcel($matrix);
        $result = $this->importer->parseAndAnalyze($filePath);
        @unlink($filePath);

        $this->assertTrue($result['success']);
        $rec = $result['reconciliation'];

        $this->assertEquals(3, $rec['total_excel_rows']);
        $this->assertEquals(3, $rec['master_po_count']);
        $this->assertEquals(2, $rec['incoming_count']);
        $this->assertEquals(1010, $rec['total_plan_qty']); // 210 + 200 + 600
        $this->assertEquals(336, $rec['total_result_qty']); // 210 + 126
        $this->assertEquals(674, $rec['total_outstanding_qty']); // 1010 - 336
    }
}
