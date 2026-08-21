<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\Actual;
use App\Models\ComparisonMaster;
use App\Services\Import\IntegratedPoIncomingImporter;
use App\Services\DataValidation\InputNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\UploadedFile;

class CrossDashboardDataTracingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        \App\Models\PurchasingCategory::create([
            'category_code' => 'RAW',
            'category_name' => 'Raw Material',
            'pic_buyer'     => 'Staff Buyer',
            'status'        => 'active',
        ]);
    }

    protected function createExcelFile(array $rows, string $fileName = 'test_import.xlsx'): UploadedFile
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

        $tmpPath = tempnam(sys_get_temp_dir(), 'e2e_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return new UploadedFile($tmpPath, $fileName, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /**
     * Test A: PO Only (Plan > 0, Result = 0)
     */
    public function test_a_po_only_trace()
    {
        $rows = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C146', 'PT. SUMBER AGUNG', '2026-08-10', '1311010', 'SCREW B 4X12', 'KI-SAS-0006', 'USD', 2.50, 'KIP 2', 600, 1500, 0, 0],
        ];

        $file = $this->createExcelFile($rows);

        $response = $this->actingAs($this->adminUser)
            ->post(route('purchasing.integrated-import.execute'), [
                'file' => $file,
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('master_pos', [
            'item_code' => '1311010',
            'qty'       => 600,
        ]);
        $this->assertEquals(0, PurchasingLog::count(), 'Incoming should not have zero transaction records');

        // Master PO dashboard must show 600
        $viewResponse = $this->actingAs($this->adminUser)->get(route('purchasing.master-po'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('1311010');
        $viewResponse->assertSee('600');

        // Outstanding dashboard must show Outstanding = 600
        $outResponse = $this->actingAs($this->adminUser)->get(route('purchasing.outstanding-po'));
        $outResponse->assertStatus(200);
        $outResponse->assertSee('1311010');
    }

    /**
     * Test B: Incoming Only / Unplanned (Plan = 0, Result > 0)
     */
    public function test_b_incoming_only_trace()
    {
        $rows = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C096', 'CV. BIMASAKTI ANEKA', '2026-08-15', '1314002', 'GESPER STRAPPING', 'KI-BSA-0012', 'IDR', 95000, 'KIP 1', 0, 0, 120, 11400000],
        ];

        $file = $this->createExcelFile($rows);

        $response = $this->actingAs($this->adminUser)
            ->post(route('purchasing.integrated-import.execute'), [
                'file' => $file,
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals(0, MasterPo::count());
        $this->assertDatabaseHas('purchasing_logs', [
            'item_code'       => '1314002',
            'actual_received' => 120,
            'status_note'     => 'Unplanned Incoming',
        ]);
    }

    /**
     * Test C: Complete & Partial Multi-Item Trace
     */
    public function test_c_complete_and_partial_multi_item_trace()
    {
        $rows = [
            ['Supplier Code', 'Supplier Name', 'Delivery Date', 'Material Code', 'Description', 'PO No.', 'Currency', 'Price', 'Plant', 'Plan', 'Plan Amount', 'Result', 'Result Amount'],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-08-05', '1312006', 'MAIN BOARD A', 'KI-TJT-0023', 'IDR', 8470, 'KIP 1', 210, 1778700, 210, 1778700],
            ['C102', 'PT. TRI JAYA TEKNIK', '2026-08-20', '1312007', 'MAIN BOARD B', 'KI-TJT-0027', 'IDR', 9500, 'KIP 1', 200, 1900000, 126, 1197000],
        ];

        $file = $this->createExcelFile($rows);

        $this->actingAs($this->adminUser)
            ->post(route('purchasing.integrated-import.execute'), [
                'file' => $file,
            ]);

        $this->assertEquals(2, MasterPo::count());
        $this->assertEquals(2, PurchasingLog::count());

        $outResponse = $this->actingAs($this->adminUser)->get(route('purchasing.outstanding-po'));
        $outResponse->assertStatus(200);
        $outResponse->assertSee('1312006');
        $outResponse->assertSee('1312007');
    }

    /**
     * Test D: Suffix Tolerance in PO Matching (KI-TJT-0023 vs KI-TJT-0023/2026)
     */
    public function test_d_suffix_tolerance_po_matching()
    {
        // 1. Master PO inserted with base PO
        MasterPo::create([
            'tanggal'        => '2026-08-01',
            'supplier'       => 'PT. TRI JAYA TEKNIK',
            'po'             => 'KI-TJT-0023',
            'item_code'      => '1312006',
            'factory_code'   => 'Plant 1',
            'name'           => 'MAIN BOARD A',
            'qty'            => 210,
            'price'          => 8470,
            'currency'       => 'IDR',
            'user_id'        => $this->adminUser->id,
            'created_by'     => $this->adminUser->id,
        ]);

        // 2. Incoming log inserted with year suffix /2026
        PurchasingLog::create([
            'purchasing_category_id' => 1,
            'user_id'                => $this->adminUser->id,
            'receipt_date'           => '2026-08-02',
            'item_code'              => '1312006',
            'factory_code'           => 'Plant 1',
            'item_name'              => 'MAIN BOARD A',
            'supplier_name'          => 'PT. TRI JAYA TEKNIK',
            'po_reference'           => 'KI-TJT-0023/2026',
            'period_month'           => '2026-08',
            'target_order'           => 210,
            'actual_received'        => 210,
            'pending_order'          => 0,
            'price'                  => 8470,
            'currency'               => 'IDR',
            'amount'                 => 1778700,
            'status_note'            => 'Complete',
        ]);

        // 3. Outstanding dashboard must resolve base PO match
        $outResponse = $this->actingAs($this->adminUser)->get(route('purchasing.outstanding-po'));
        $outResponse->assertStatus(200);
        $outData = $outResponse->viewData('outstandingData');
        $this->assertNotEmpty($outData);

        $item = $outData->firstWhere('item_code', '1312006');
        $this->assertNotNull($item);
        $this->assertEquals(210, $item->qty_po);
        $this->assertEquals(210, $item->qty_receipt);
        $this->assertEquals(0, $item->outstanding_qty);
    }

    /**
     * Test E: Data Integration Health & Matrix Endpoint
     */
    public function test_e_data_health_endpoint()
    {
        $response = $this->actingAs($this->adminUser)->get(route('system.data-health'));
        $response->assertStatus(200);
        $response->assertSee('Data Integration Health');
        $response->assertSee('Overall Health Score');

        $apiResponse = $this->actingAs($this->adminUser)->get(route('api.system.data-health'));
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonStructure([
            'timestamp',
            'health_score',
            'modules',
            'reconciliation',
        ]);
    }
}
