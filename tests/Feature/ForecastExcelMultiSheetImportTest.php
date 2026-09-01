<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PurchasingCategory;
use App\Models\PurchasingOutstanding;
use App\Models\Forecasting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ForecastExcelMultiSheetImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'supervisor',
            'email' => 'supervisor@kawai.co.id',
        ]);
    }

    protected function createMultiSheetExcel(): UploadedFile
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Empty or Cover Sheet
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Sheet1');
        $sheet1->setCellValue('A1', 'COVER TEMPLATE');

        // Sheet 2: Data Sheet named Sheet2 (3)
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Sheet2 (3)');

        // Row 1: Month Merged Headers
        $sheet2->setCellValue('I1', 'DEC-25');
        $sheet2->setCellValue('M1', 'JAN-26');
        $sheet2->setCellValue('Q1', 'FEB-26');

        // Row 2: Sub-headers
        $sheet2->setCellValue('I2', 'OUTSTANDING');
        $sheet2->setCellValue('J2', 'STOCK');
        $sheet2->setCellValue('K2', 'PO');
        $sheet2->setCellValue('L2', 'PROD');

        $sheet2->setCellValue('M2', 'OUTSTANDING');
        $sheet2->setCellValue('N2', 'STOCK');
        $sheet2->setCellValue('O2', 'PO');
        $sheet2->setCellValue('P2', 'PROD');

        $sheet2->setCellValue('Q2', 'OUTSTANDING');
        $sheet2->setCellValue('R2', 'STOCK');
        $sheet2->setCellValue('S2', 'PO');
        $sheet2->setCellValue('T2', 'PROD');

        // Row 4: Column Field Headers
        $sheet2->setCellValue('A4', 'Supplier Code');
        $sheet2->setCellValue('B4', 'Supplier Name');
        $sheet2->setCellValue('C4', 'Plant');
        $sheet2->setCellValue('D4', 'kategori');
        $sheet2->setCellValue('E4', 'Material Code');
        $sheet2->setCellValue('F4', 'Description');
        $sheet2->setCellValue('G4', 'Unit price');
        $sheet2->setCellValue('H4', 'Currency');

        // Row 5: Data Row 1 (PUR-01)
        $sheet2->setCellValue('A5', 'C008');
        $sheet2->setCellValue('B5', 'PT. SURYARAYA NUSATAMA');
        $sheet2->setCellValue('C5', 'KIP1');
        $sheet2->setCellValue('D5', 'PUR-01');
        $sheet2->setCellValue('E5', '022365B');
        $sheet2->setCellValue('F5', 'Post 2 DSO');
        $sheet2->setCellValue('G5', '43574');
        $sheet2->setCellValue('H5', 'IDR');
        $sheet2->setCellValue('I5', 50);
        $sheet2->setCellValue('J5', 100);
        $sheet2->setCellValue('O5', 200);
        $sheet2->setCellValue('P5', 150);
        $sheet2->setCellValue('S5', 300);
        $sheet2->setCellValue('T5', 250);

        // Row 6: Data Row 2 (PUR-04)
        $sheet2->setCellValue('A6', 'C146');
        $sheet2->setCellValue('B6', 'PT. SUMBER AGUNG SEJAHTERA ABADI');
        $sheet2->setCellValue('C6', 'KIP4');
        $sheet2->setCellValue('D6', 'PUR-04');
        $sheet2->setCellValue('E6', '1311023');
        $sheet2->setCellValue('F6', 'PLASTIK SINGLE SHEET');
        $sheet2->setCellValue('G6', '41000');
        $sheet2->setCellValue('H6', 'IDR');
        $sheet2->setCellValue('I6', 10);
        $sheet2->setCellValue('J6', 20);
        $sheet2->setCellValue('O6', 500);
        $sheet2->setCellValue('P6', 400);
        $sheet2->setCellValue('S6', 600);
        $sheet2->setCellValue('T6', 500);

        // Set active sheet back to Sheet1
        $spreadsheet->setActiveSheetIndex(0);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_multisheet_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile($tempPath, 'Template all for luthfi start stok dec 2025 RM syahrul (003)310826.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_multisheet_excel_import_extracts_data_and_creates_forecasts()
    {
        $file = $this->createMultiSheetExcel();

        $response = $this->actingAs($this->user)->post(route('purchasing.outstanding.import'), [
            'file' => $file,
            'import_currency' => 'IDR',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Check PurchasingOutstanding records
        $this->assertEquals(2, PurchasingOutstanding::count());

        $po1 = PurchasingOutstanding::where('part_number', '022365B')->first();
        $this->assertNotNull($po1);
        $this->assertEquals('KIP 1', $po1->factory_code);
        $this->assertEquals('PT. SURYARAYA NUSATAMA', $po1->supplier_name);
        $this->assertEquals(43574, $po1->price);
        $this->assertEquals(500, $po1->order_qty);

        $po2 = PurchasingOutstanding::where('part_number', '1311023')->first();
        $this->assertNotNull($po2);
        $this->assertEquals('KIP 4', $po2->factory_code);
        $this->assertEquals('PT. SUMBER AGUNG SEJAHTERA ABADI', $po2->supplier_name);
        $this->assertEquals(41000, $po2->price);
        $this->assertEquals(1100, $po2->order_qty);

        // Check Categories Created & Mapped
        $catPur01 = PurchasingCategory::where('category_code', 'PUR-01')->first();
        $this->assertNotNull($catPur01);
        $this->assertEquals($catPur01->id, $po1->category_id);

        $catPur04 = PurchasingCategory::where('category_code', 'PUR-04')->first();
        $this->assertNotNull($catPur04);
        $this->assertEquals($catPur04->id, $po2->category_id);

        // Check Forecasting Records Created
        $forecastCount = Forecasting::count();
        $this->assertGreaterThanOrEqual(4, $forecastCount);

        $fcJan1 = Forecasting::where('part_number', '022365B')->where('periode', '2026-01')->first();
        $this->assertNotNull($fcJan1);
        $this->assertEquals(200, $fcJan1->po_qty);
        $this->assertEquals(150, $fcJan1->production_qty);
        $this->assertEquals(200, $fcJan1->forecast_qty);

        $fcJan2 = Forecasting::where('part_number', '1311023')->where('periode', '2026-01')->first();
        $this->assertNotNull($fcJan2);
        $this->assertEquals(500, $fcJan2->po_qty);
        $this->assertEquals(400, $fcJan2->production_qty);
        $this->assertEquals(500, $fcJan2->forecast_qty);

        // Check Outstanding Page rendering
        $outResponse = $this->actingAs($this->user)->get(route('purchasing.outstanding'));
        $outResponse->assertStatus(200);
        $outResponse->assertSee('022365B');
        $outResponse->assertSee('1311023');
        $outResponse->assertSee('MASTER FORECAST');
        $outResponse->assertSee('1,600');

        // Check Dashboard Page rendering
        $dashResponse = $this->actingAs($this->user)->get(route('dashboard.overview'));
        $dashResponse->assertStatus(200);
    }
}
