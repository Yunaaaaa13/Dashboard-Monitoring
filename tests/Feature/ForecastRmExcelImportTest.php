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

/**
 * Tes import Excel untuk bagian RM (Raw Material) di Forecast.
 * Memvalidasi:
 * 1. Multi-sheet workbook: sheet RM terpisah berhasil di-import.
 * 2. Part number tanpa digit (misal WOOD-SPRUCE) diterima sebagai item code.
 * 3. Kategori RM alias (RM, RM KAYU, RAW MATERIAL) ter-resolve ke PUR-01.
 * 4. Nilai forecast muncul di tabel monitoring (getForecastForMonth > 0).
 */
class ForecastRmExcelImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role'  => 'supervisor',
            'email' => 'supervisor@kawai.co.id',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: build Excel dengan sheet Cover + sheet "RM SYAHRUL"
    // ─────────────────────────────────────────────────────────────────────────
    protected function createRmMultiSheetExcel(): UploadedFile
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Cover (sedikit data, skor rendah)
        $cover = $spreadsheet->getActiveSheet();
        $cover->setTitle('COVER');
        $cover->setCellValue('A1', 'TEMPLATE MONITORING PURCHASING');
        $cover->setCellValue('A2', 'PT KAWAI INDONESIA');

        // Sheet 2: RM SYAHRUL (data RM kayu)
        $rm = $spreadsheet->createSheet();
        $rm->setTitle('RM SYAHRUL');

        // Baris 1: header bulan
        $rm->setCellValue('I1', 'JUL-26');
        $rm->setCellValue('M1', 'AUG-26');

        // Baris 2: sub-header
        $rm->setCellValue('I2', 'OUTSTANDING');
        $rm->setCellValue('J2', 'STOCK');
        $rm->setCellValue('K2', 'PO');
        $rm->setCellValue('L2', 'PROD');
        $rm->setCellValue('M2', 'OUTSTANDING');
        $rm->setCellValue('N2', 'STOCK');
        $rm->setCellValue('O2', 'PO');
        $rm->setCellValue('P2', 'PROD');

        // Baris 4: header kolom field
        $rm->setCellValue('A4', 'Supplier Code');
        $rm->setCellValue('B4', 'Supplier Name');
        $rm->setCellValue('C4', 'Plant');
        $rm->setCellValue('D4', 'kategori');
        $rm->setCellValue('E4', 'Kode RM');       // header RM-spesifik
        $rm->setCellValue('F4', 'Description');
        $rm->setCellValue('G4', 'Unit price');
        $rm->setCellValue('H4', 'Currency');

        // Baris 5: data item RM dengan part number tanpa digit
        $rm->setCellValue('A5', 'C008');
        $rm->setCellValue('B5', 'PT. SURYARAYA NUSATAMA');
        $rm->setCellValue('C5', 'KIP1');
        $rm->setCellValue('D5', 'RM KAYU');           // alias -> PUR-01
        $rm->setCellValue('E5', 'WOOD-SPRUCE-A');     // kode RM tanpa digit
        $rm->setCellValue('F5', 'Spruce Top Grade A');
        $rm->setCellValue('G5', 50000);
        $rm->setCellValue('H5', 'IDR');
        $rm->setCellValue('I5', 20);    // outstanding Jul
        $rm->setCellValue('J5', 80);    // stock Jul
        $rm->setCellValue('K5', 100);   // PO Jul
        $rm->setCellValue('L5', 90);    // prod Jul
        $rm->setCellValue('O5', 200);   // PO Aug
        $rm->setCellValue('P5', 180);   // prod Aug

        // Baris 6: data item RM dengan kategori 'RM' saja
        $rm->setCellValue('A6', 'C009');
        $rm->setCellValue('B6', 'PT. KAYU JAYA MAKMUR');
        $rm->setCellValue('C6', 'KIP1');
        $rm->setCellValue('D6', 'RM');               // alias umum -> PUR-01
        $rm->setCellValue('E6', 'BALOK-PINE-2X4');   // kode RM tanpa digit
        $rm->setCellValue('F6', 'Pine Wood 2x4');
        $rm->setCellValue('G6', 35000);
        $rm->setCellValue('H6', 'IDR');
        $rm->setCellValue('I6', 5);
        $rm->setCellValue('J6', 40);
        $rm->setCellValue('K6', 60);
        $rm->setCellValue('L6', 55);
        $rm->setCellValue('O6', 120);
        $rm->setCellValue('P6', 110);

        $spreadsheet->setActiveSheetIndex(0);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_rm_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return new UploadedFile(
            $tempPath,
            'Template RM SYAHRUL forecast 2026.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: single sheet dengan kolom kategori 'RAW MATERIAL'
    // ─────────────────────────────────────────────────────────────────────────
    protected function createRmSingleSheetExcel(): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

        $sheet->setCellValue('I1', 'JUL-26');
        $sheet->setCellValue('M1', 'AUG-26');
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
        $sheet->setCellValue('G4', 'Unit price');
        $sheet->setCellValue('H4', 'Currency');

        $sheet->setCellValue('A5', 'C010');
        $sheet->setCellValue('B5', 'PT. AKUSTIK SUPPLIER');
        $sheet->setCellValue('C5', 'KIP1');
        $sheet->setCellValue('D5', 'RAW MATERIAL');   // alias -> PUR-01
        $sheet->setCellValue('E5', '022365B');
        $sheet->setCellValue('F5', 'Guitar Top Spruce');
        $sheet->setCellValue('G5', 75000);
        $sheet->setCellValue('H5', 'IDR');
        $sheet->setCellValue('K5', 300);
        $sheet->setCellValue('L5', 250);
        $sheet->setCellValue('O5', 400);
        $sheet->setCellValue('P5', 350);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_rm_single_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return new UploadedFile(
            $tempPath,
            'RM_Single_Sheet_Test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 1: Multi-sheet workbook dengan sheet "RM SYAHRUL"
    // ─────────────────────────────────────────────────────────────────────────
    public function test_rm_multisheet_import_creates_records_with_correct_category()
    {
        $file = $this->createRmMultiSheetExcel();

        $response = $this->actingAs($this->user)->post(route('purchasing.outstanding.import'), [
            'file'            => $file,
            'import_currency' => 'IDR',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertGreaterThanOrEqual(2, PurchasingOutstanding::count(), 'Minimal 2 item RM harus ter-import');

        $item1 = PurchasingOutstanding::where('part_number', 'WOOD-SPRUCE-A')->first();
        $this->assertNotNull($item1, 'Part number WOOD-SPRUCE-A harus ter-import');
        $this->assertEquals('PT. SURYARAYA NUSATAMA', $item1->supplier_name);

        $cat = PurchasingCategory::where('category_code', 'PUR-01')->first();
        $this->assertNotNull($cat, 'Kategori PUR-01 harus ada');
        $this->assertEquals($cat->id, $item1->category_id, 'WOOD-SPRUCE-A harus berkategori PUR-01');

        $item2 = PurchasingOutstanding::where('part_number', 'BALOK-PINE-2X4')->first();
        $this->assertNotNull($item2, 'Part number BALOK-PINE-2X4 harus ter-import');
        $this->assertEquals($cat->id, $item2->category_id, 'BALOK-PINE-2X4 harus berkategori PUR-01');

        $this->assertGreaterThanOrEqual(2, Forecasting::count(), 'Minimal 2 forecast record harus ada');

        PurchasingOutstanding::clearCalcCaches();
        $item1Fresh = PurchasingOutstanding::where('part_number', 'WOOD-SPRUCE-A')->first();
        $fcMonth1   = $item1Fresh->getForecastForMonth(1);
        $this->assertGreaterThan(0, $fcMonth1, 'getForecastForMonth(1) harus > 0 untuk item RM yang di-import');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 2: Single-sheet dengan kolom kategori 'RAW MATERIAL'
    // ─────────────────────────────────────────────────────────────────────────
    public function test_rm_single_sheet_raw_material_alias_resolves_to_pur01()
    {
        $file = $this->createRmSingleSheetExcel();

        $response = $this->actingAs($this->user)->post(route('purchasing.outstanding.import'), [
            'file'            => $file,
            'import_currency' => 'IDR',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $item = PurchasingOutstanding::where('part_number', '022365B')->first();
        $this->assertNotNull($item, 'Part number 022365B harus ter-import');

        $cat = PurchasingCategory::where('category_code', 'PUR-01')->first();
        $this->assertNotNull($cat);
        $this->assertEquals($cat->id, $item->category_id, 'RAW MATERIAL alias harus resolve ke PUR-01');

        $fc = Forecasting::where('part_number', '022365B')->first();
        $this->assertNotNull($fc, 'Forecast record untuk 022365B harus ada');
        $this->assertGreaterThan(0, $fc->forecast_qty, 'forecast_qty harus > 0');

        PurchasingOutstanding::clearCalcCaches();
        $itemFresh = PurchasingOutstanding::where('part_number', '022365B')->first();
        $fcMonth1  = $itemFresh->getForecastForMonth(1);
        $this->assertGreaterThan(0, $fcMonth1, 'getForecastForMonth harus > 0 setelah import Excel RM');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 3: normalizeCategoryCode - validasi alias RM
    // ─────────────────────────────────────────────────────────────────────────
    public function test_normalize_category_code_rm_aliases()
    {
        $aliases = [
            'RM'                 => 'PUR-01',
            'RM KAYU'            => 'PUR-01',
            'RM-KAYU'            => 'PUR-01',
            'RAW MATERIAL'       => 'PUR-01',
            'RAW MATERIALS'      => 'PUR-01',
            'BAHAN BAKU'         => 'PUR-01',
            'RM SYAHRUL'         => 'PUR-01',
            'RAW MATERIAL KAYU'  => 'PUR-01',
            'RM LOGAM'           => 'PUR-02',
            'RM-LOGAM'           => 'PUR-02',
            'RM BESI'            => 'PUR-02',
            'RM BAJA'            => 'PUR-02',
            'PACKING'            => 'PUR-04',
            'PUR-01'             => 'PUR-01',
            'PUR 1'              => 'PUR-01',
            'PUR-04'             => 'PUR-04',
        ];

        foreach ($aliases as $input => $expected) {
            $result = \App\Services\DataValidation\InputNormalizer::normalizeCategoryCode($input);
            $this->assertEquals(
                $expected,
                $result,
                "normalizeCategoryCode('$input') seharusnya '$expected', got '$result'"
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Test 4: halaman monitoring tetap bisa load setelah import
    // ─────────────────────────────────────────────────────────────────────────
    public function test_outstanding_page_loads_after_rm_import()
    {
        PurchasingCategory::firstOrCreate(
            ['category_code' => 'PUR-01'],
            ['category_name' => 'Raw Material Kayu', 'pic_buyer' => 'Luthfi', 'monthly_target_units' => 5000, 'status' => 'Active']
        );

        $response = $this->actingAs($this->user)->get(route('purchasing.outstanding'));
        $response->assertStatus(200);
        $response->assertSee('MASTER FORECAST');
    }
}
