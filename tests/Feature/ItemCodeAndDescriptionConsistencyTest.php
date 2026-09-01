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

class ItemCodeAndDescriptionConsistencyTest extends TestCase
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

    protected function createEkadharmaExcel(): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PUR-03 Consumable');

        // Row 1: Month Merged Headers
        $sheet->setCellValue('I1', 'DEC-25');
        $sheet->setCellValue('M1', 'JAN-26');
        $sheet->setCellValue('Q1', 'FEB-26');

        // Row 2: Sub-headers (Month 0 / DEC-25 has OUTSTANDING & STOCK)
        $sheet->setCellValue('I2', 'OUTSTANDING');
        $sheet->setCellValue('J2', 'STOCK');
        $sheet->setCellValue('K2', 'PO');
        $sheet->setCellValue('L2', 'PROD');

        $sheet->setCellValue('M2', 'OUTSTANDING');
        $sheet->setCellValue('N2', 'STOCK');
        $sheet->setCellValue('O2', 'PO');
        $sheet->setCellValue('P2', 'PROD');

        $sheet->setCellValue('Q2', 'OUTSTANDING');
        $sheet->setCellValue('R2', 'STOCK');
        $sheet->setCellValue('S2', 'PO');
        $sheet->setCellValue('T2', 'PROD');

        // Row 4: Column Field Headers
        $sheet->setCellValue('A4', 'Supplier Code');
        $sheet->setCellValue('B4', 'Supplier Name');
        $sheet->setCellValue('C4', 'Plant');
        $sheet->setCellValue('D4', 'kategori');
        $sheet->setCellValue('E4', 'Material Code');
        $sheet->setCellValue('F4', 'Description');
        $sheet->setCellValue('G4', 'Unit Price');
        $sheet->setCellValue('H4', 'Currency');

        // Row 5: Data Row 1 (Item OPP Tape)
        $sheet->setCellValue('A5', 'C017');
        $sheet->setCellValue('B5', 'PT. EKADHARMA INTERNASIONAL TBK');
        $sheet->setCellValue('C5', 'KIP 1');
        $sheet->setCellValue('D5', 'PUR-03');
        $sheet->setCellValue('E5', '1312006');
        $sheet->setCellValue('F5', 'LAKBAN BENING DAIMARU 48MM X 100M');
        $sheet->setCellValue('G5', '12420');
        $sheet->setCellValue('H5', 'IDR');
        $sheet->setCellValue('I5', 500); // Month 0 Outstanding
        $sheet->setCellValue('J5', 250); // Month 0 Stock
        $sheet->setCellValue('O5', 1000);
        $sheet->setCellValue('P5', 800);
        $sheet->setCellValue('S5', 1200);
        $sheet->setCellValue('T5', 1100);

        // Row 6: Data Row 2 (Masking Tape)
        $sheet->setCellValue('A6', 'C017');
        $sheet->setCellValue('B6', 'PT. EKADHARMA INTERNASIONAL TBK');
        $sheet->setCellValue('C6', 'KIP 1');
        $sheet->setCellValue('D6', 'PUR-03');
        $sheet->setCellValue('E6', '1312007');
        $sheet->setCellValue('F6', 'MASKING TAPE DAIMARU 24MM');
        $sheet->setCellValue('G6', '24820');
        $sheet->setCellValue('H6', 'IDR');
        $sheet->setCellValue('I6', 300);
        $sheet->setCellValue('J6', 150);
        $sheet->setCellValue('O6', 500);
        $sheet->setCellValue('P6', 400);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_ekadharma_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile($tempPath, 'Forecast_Ekadharma.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    protected function createCategoryExcel(string $categoryCode, string $catName, string $partNo, string $desc, string $suppCode, string $suppName): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($categoryCode);

        $sheet->setCellValue('I1', 'DEC-25');
        $sheet->setCellValue('M1', 'JAN-26');

        $sheet->setCellValue('I2', 'OUTSTANDING');
        $sheet->setCellValue('J2', 'STOCK');
        $sheet->setCellValue('K2', 'PO');
        $sheet->setCellValue('L2', 'PROD');

        $sheet->setCellValue('M2', 'OUTSTANDING');
        $sheet->setCellValue('N2', 'STOCK');
        $sheet->setCellValue('O2', 'PO');
        $sheet->setCellValue('P2', 'PROD');

        $sheet->setCellValue('A4', 'Supplier Code');
        $sheet->setCellValue('B4', 'Supplier Name');
        $sheet->setCellValue('C4', 'Plant');
        $sheet->setCellValue('D4', 'kategori');
        $sheet->setCellValue('E4', 'Material Code');
        $sheet->setCellValue('F4', 'Description');
        $sheet->setCellValue('G4', 'Unit Price');
        $sheet->setCellValue('H4', 'Currency');

        $sheet->setCellValue('A5', $suppCode);
        $sheet->setCellValue('B5', $suppName);
        $sheet->setCellValue('C5', 'KIP 1');
        $sheet->setCellValue('D5', $categoryCode);
        $sheet->setCellValue('E5', $partNo);
        $sheet->setCellValue('F5', $desc);
        $sheet->setCellValue('G5', '35000');
        $sheet->setCellValue('H5', 'IDR');
        $sheet->setCellValue('I5', 400);
        $sheet->setCellValue('J5', 200);
        $sheet->setCellValue('O5', 600);
        $sheet->setCellValue('P5', 500);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_' . strtolower($categoryCode) . '_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile($tempPath, "Forecast_{$categoryCode}.xlsx", 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_item_code_and_description_consistency_and_not_confused_with_vendor_or_category()
    {
        $file = $this->createEkadharmaExcel();

        $response = $this->actingAs($this->user)
            ->post(route('purchasing.outstanding.import'), [
                'file' => $file,
                'import_currency' => 'IDR',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify row 1
        $item1 = PurchasingOutstanding::where('part_number', '1312006')->first();
        $this->assertNotNull($item1, 'Part number 1312006 must exist and NOT be named C017');
        $this->assertNotEquals('C017', $item1->part_number);
        $this->assertNotEquals('PUR-03', $item1->description);
        $this->assertEquals('LAKBAN BENING DAIMARU 48MM X 100M', $item1->description);
        $this->assertEquals('PT. EKADHARMA INTERNASIONAL TBK', $item1->supplier_name);
        $this->assertEquals(12420.0, (float)$item1->price);
        $this->assertEquals('KIP 1', $item1->factory_code);

        // Verify Month 0 Pre-Month values were extracted
        $this->assertEquals(500, (int)$item1->plan_outstand);
        $this->assertEquals(250, (int)$item1->plan_stock);

        // Verify row 2
        $item2 = PurchasingOutstanding::where('part_number', '1312007')->first();
        $this->assertNotNull($item2);
        $this->assertEquals('MASKING TAPE DAIMARU 24MM', $item2->description);
        $this->assertEquals(24820.0, (float)$item2->price);

        // C017 must NEVER exist as a part_number!
        $c017Item = PurchasingOutstanding::where('part_number', 'C017')->first();
        $this->assertNull($c017Item, 'Supplier code C017 must NEVER be saved as a part_number');

        // Category must be PUR-03
        $category = PurchasingCategory::find($item1->category_id);
        $this->assertNotNull($category);
        $this->assertEquals('PUR-03', $category->category_code);
    }

    public function test_uploading_multiple_category_files_preserves_both_categories_without_overwriting()
    {
        // 1. Upload PUR-01
        $file1 = $this->createCategoryExcel('PUR-01', 'Raw Material Kayu', '022365B', 'Post 2 DSO', 'C008', 'PT. SURYARAYA NUSATAMA');
        $this->actingAs($this->user)->post(route('purchasing.outstanding.import'), ['file' => $file1]);

        $this->assertDatabaseHas('purchasing_outstandings', [
            'part_number' => '022365B',
        ]);
        $this->assertDatabaseHas('forecastings', [
            'part_number' => '022365B',
        ]);

        // 2. Upload PUR-04
        $file2 = $this->createCategoryExcel('PUR-04', 'Komponen Packing', '1311023', 'PLASTIK SINGLE SHEET', 'C146', 'PT. SUMBER AGUNG');
        $this->actingAs($this->user)->post(route('purchasing.outstanding.import'), ['file' => $file2]);

        // BOTH must exist in purchasing_outstandings! PUR-01 was NOT wiped or overwritten!
        $this->assertDatabaseHas('purchasing_outstandings', [
            'part_number' => '022365B',
        ]);
        $this->assertDatabaseHas('purchasing_outstandings', [
            'part_number' => '1311023',
        ]);

        // BOTH must exist in forecastings!
        $this->assertDatabaseHas('forecastings', [
            'part_number' => '022365B',
        ]);
        $this->assertDatabaseHas('forecastings', [
            'part_number' => '1311023',
        ]);
    }

    public function test_home_dashboard_displays_non_zero_targets_after_step1_upload()
    {
        $file = $this->createEkadharmaExcel();
        $this->actingAs($this->user)->post(route('purchasing.outstanding.import'), ['file' => $file]);

        $response = $this->actingAs($this->user)->get(route('dashboard.overview'));
        $response->assertStatus(200);

        // View variables should have valid non-zero targets
        $targetOrder = $response->viewData('targetOrder');
        $this->assertGreaterThan(0, $targetOrder, 'Home Target Order must be greater than 0');

        $categoryPerformances = $response->viewData('categoryPerformances');
        $this->assertNotEmpty($categoryPerformances);

        // Find PUR-03 in category performances
        $pur03Perf = collect($categoryPerformances)->firstWhere('code', 'PUR-03');
        $this->assertNotNull($pur03Perf, 'PUR-03 must appear in Category Performances');
        $this->assertGreaterThan(0, $pur03Perf['target'], 'PUR-03 target must be greater than 0');
    }
}
