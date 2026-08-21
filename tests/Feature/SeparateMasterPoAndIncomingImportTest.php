<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\PurchasingCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\UploadedFile;

class SeparateMasterPoAndIncomingImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        PurchasingCategory::create([
            'category_code' => 'RAW',
            'category_name' => 'Raw Material',
            'pic_buyer'     => 'Staff Buyer',
            'status'        => 'active',
        ]);
    }

    protected function createExcelFile(array $rows, string $fileName = 'test_ezrunner.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rIdx => $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue($colLetter . ($rIdx + 1), $val);
                $colLetter++;
            }
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'sep_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return new UploadedFile($tmpPath, $fileName, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /**
     * Scenario 1: Upload in Master PO (Step 2) creates ONLY Master PO, ignores Result
     */
    public function test_upload_master_po_creates_only_master_po()
    {
        $rows = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-08-05', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 'KIP 1', 210, 1778700, 210, 1778700],
        ];

        $file = $this->createExcelFile($rows);

        $response = $this->actingAs($this->adminUser)
            ->post(route('purchasing.master-po.import'), [
                'file' => $file,
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('master_pos', [
            'item_code' => '1312006',
            'po'        => 'KI-TJT-0023',
            'qty'       => 210,
        ]);
        // Critical: NO Incoming records should be created!
        $this->assertEquals(0, PurchasingLog::count(), 'Master PO import must not create Incoming records');
    }

    /**
     * Scenario 2: Upload in Incoming (Step 3) creates ONLY Incoming, ignores Plan
     */
    public function test_upload_incoming_creates_only_incoming()
    {
        $rows = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-08-05', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 'KIP 1', 210, 1778700, 126, 1067220],
        ];

        $file = $this->createExcelFile($rows);

        $response = $this->actingAs($this->adminUser)
            ->post(route('purchasing.input.import'), [
                'file' => $file,
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('purchasing_logs', [
            'item_code'       => '1312006',
            'po_reference'    => 'KI-TJT-0023',
            'actual_received' => 126,
        ]);
        // Critical: Master PO count must remain 0!
        $this->assertEquals(0, MasterPo::count(), 'Incoming import must not create Master PO records');
    }

    /**
     * Scenario 3: End-to-End Two Step Workflow with Auto Outstanding Correlation
     */
    public function test_two_step_workflow_and_outstanding_correlation()
    {
        $ezrunnerData = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-08-05', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 'KIP 1', 210, 1778700, 126, 1067220],
        ];

        // Step 2: Upload in Master PO
        $file1 = $this->createExcelFile($ezrunnerData, 'ezrunner_po.xlsx');
        $this->actingAs($this->adminUser)->post(route('purchasing.master-po.import'), ['file' => $file1]);

        $this->assertEquals(1, MasterPo::count());
        $this->assertEquals(0, PurchasingLog::count());

        // Step 3: User uploads arrival in Incoming
        $file2 = $this->createExcelFile($ezrunnerData, 'ezrunner_incoming.xlsx');
        $this->actingAs($this->adminUser)->post(route('purchasing.input.import'), ['file' => $file2]);

        $this->assertEquals(1, MasterPo::count());
        $this->assertEquals(1, PurchasingLog::count());

        // Step 4: Outstanding PO Dashboard resolves correlation automatically
        $outResponse = $this->actingAs($this->adminUser)->get(route('purchasing.outstanding-po'));
        $outResponse->assertStatus(200);

        $outData = $outResponse->viewData('outstandingData');
        $this->assertNotEmpty($outData);

        $item = $outData->firstWhere('item_code', '1312006');
        $this->assertNotNull($item);
        $this->assertEquals(210, $item->qty_po);
        $this->assertEquals(126, $item->qty_receipt);
        $this->assertEquals(84, $item->outstanding_qty);
    }

    /**
     * Scenario 4: Separate Template Downloads
     */
    public function test_separate_template_downloads()
    {
        $poTpl = $this->actingAs($this->adminUser)->get(route('purchasing.master-po.template'));
        $poTpl->assertStatus(200);
        $this->assertStringContainsString('template_master_po.xlsx', $poTpl->headers->get('content-disposition'));

        $incTpl = $this->actingAs($this->adminUser)->get(route('purchasing.input.template'));
        $incTpl->assertStatus(200);
        $this->assertStringContainsString('template_incoming.xlsx', $incTpl->headers->get('content-disposition'));
    }
}
