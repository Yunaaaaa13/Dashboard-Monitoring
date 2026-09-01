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

class MultiPlantItemCodeImportTest extends TestCase
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

    protected function createMultiPlantExcel(): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet2');

        // Row 1: Months
        $sheet->setCellValue('I1', 'DEC-25');
        $sheet->setCellValue('M1', 'JAN-26');

        // Row 2: Sub-headers
        $sheet->setCellValue('I2', 'OUTSTANDING');
        $sheet->setCellValue('J2', 'STOCK');
        $sheet->setCellValue('K2', 'PO');
        $sheet->setCellValue('L2', 'PROD');

        $sheet->setCellValue('M2', 'OUTSTANDING');
        $sheet->setCellValue('N2', 'STOCK');
        $sheet->setCellValue('O2', 'PO');
        $sheet->setCellValue('P2', 'PROD');

        // Row 4: Column Headers
        $sheet->setCellValue('A4', 'Supplier Code');
        $sheet->setCellValue('B4', 'Supplier Name');
        $sheet->setCellValue('C4', 'Plant');
        $sheet->setCellValue('D4', 'Kategori');
        $sheet->setCellValue('E4', 'Material Code');
        $sheet->setCellValue('F4', 'Description');
        $sheet->setCellValue('G4', 'Unit price');
        $sheet->setCellValue('H4', 'Currency');

        // Row 8: 1311023 (KIP1)
        $sheet->setCellValue('A8', 'C146');
        $sheet->setCellValue('B8', 'PT. SUMBER AGUNG SEJAHTERA ABADI');
        $sheet->setCellValue('C8', 'KIP1');
        $sheet->setCellValue('D8', 'PUR-04');
        $sheet->setCellValue('E8', '1311023');
        $sheet->setCellValue('F8', 'PLASTIK SINGLE SHEET 160CMX0.07');
        $sheet->setCellValue('G8', '41000');
        $sheet->setCellValue('H8', 'IDR');
        $sheet->setCellValue('O8', 100);

        // Row 9: 1311025 (KIP1)
        $sheet->setCellValue('A9', 'C146');
        $sheet->setCellValue('B9', 'PT. SUMBER AGUNG SEJAHTERA ABADI');
        $sheet->setCellValue('C9', 'KIP1');
        $sheet->setCellValue('D9', 'PUR-04');
        $sheet->setCellValue('E9', '1311025');
        $sheet->setCellValue('F9', 'PLASTIK SINGLE SHEET 220CMX0.06');
        $sheet->setCellValue('G9', '41000');
        $sheet->setCellValue('H9', 'IDR');
        $sheet->setCellValue('O9', 200);

        // Row 14: 1311004 (KIP1)
        $sheet->setCellValue('A14', 'C146');
        $sheet->setCellValue('B14', 'PT. SUMBER AGUNG SEJAHTERA ABADI');
        $sheet->setCellValue('C14', 'KIP1');
        $sheet->setCellValue('D14', 'PUR-04');
        $sheet->setCellValue('E14', '1311004');
        $sheet->setCellValue('F14', 'STRETCH FILM 17 mcr 500 mm*170 m');
        $sheet->setCellValue('G14', '113100');
        $sheet->setCellValue('H14', 'IDR');
        $sheet->setCellValue('O14', 300);

        // Row 15: 1311025 (KIP4) -> Same item code as row 9, but DIFFERENT PLANT (KIP4)
        $sheet->setCellValue('A15', 'C146');
        $sheet->setCellValue('B15', 'PT. SUMBER AGUNG SEJAHTERA ABADI');
        $sheet->setCellValue('C15', 'KIP4');
        $sheet->setCellValue('D15', 'PUR-04');
        $sheet->setCellValue('E15', '1311025');
        $sheet->setCellValue('F15', 'PLASTIK SINGLE SHEET 220CMX0.06');
        $sheet->setCellValue('G15', '41000');
        $sheet->setCellValue('H15', 'IDR');
        $sheet->setCellValue('O15', 250);

        // Row 16: 1311004 (KIP2) -> Same item code as row 14, but DIFFERENT PLANT (KIP2)
        $sheet->setCellValue('A16', 'C146');
        $sheet->setCellValue('B16', 'PT. SUMBER AGUNG SEJAHTERA ABADI');
        $sheet->setCellValue('C16', 'KIP2');
        $sheet->setCellValue('D16', 'PUR-04');
        $sheet->setCellValue('E16', '1311004');
        $sheet->setCellValue('F16', 'STRETCH FILM 17 mcr 500 mm*170 m');
        $sheet->setCellValue('G16', '113100');
        $sheet->setCellValue('H16', 'IDR');
        $sheet->setCellValue('O16', 350);

        // Row 17: 1311004 (KIP4) -> Same item code as row 14 & 16, but DIFFERENT PLANT (KIP4)
        $sheet->setCellValue('A17', 'C146');
        $sheet->setCellValue('B17', 'PT. SUMBER AGUNG SEJAHTERA ABADI');
        $sheet->setCellValue('C17', 'KIP4');
        $sheet->setCellValue('D17', 'PUR-04');
        $sheet->setCellValue('E17', '1311004');
        $sheet->setCellValue('F17', 'STRETCH FILM 17 mcr 500 mm*170 m');
        $sheet->setCellValue('G17', '113100');
        $sheet->setCellValue('H17', 'IDR');
        $sheet->setCellValue('O17', 400);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_multiplant_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile($tempPath, 'template all for luthfi start stock dec 2025 packing pallet.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_same_item_code_in_different_plants_is_not_flagged_as_duplicate()
    {
        $file = $this->createMultiPlantExcel();

        $response = $this->actingAs($this->user)->post(route('purchasing.outstanding.import'), [
            'file' => $file,
            'import_currency' => 'IDR',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $response->assertSessionMissing('import_duplicates_found');

        // Verify 6 records created in PurchasingOutstanding
        $this->assertEquals(6, PurchasingOutstanding::count());

        // Check 1311004 exists across 3 separate plants
        $po1004_kip1 = PurchasingOutstanding::where('part_number', '1311004')->where('factory_code', 'KIP 1')->first();
        $this->assertNotNull($po1004_kip1);
        $this->assertEquals(300, $po1004_kip1->order_qty);

        $po1004_kip2 = PurchasingOutstanding::where('part_number', '1311004')->where('factory_code', 'KIP 2')->first();
        $this->assertNotNull($po1004_kip2);
        $this->assertEquals(350, $po1004_kip2->order_qty);

        $po1004_kip4 = PurchasingOutstanding::where('part_number', '1311004')->where('factory_code', 'KIP 4')->first();
        $this->assertNotNull($po1004_kip4);
        $this->assertEquals(400, $po1004_kip4->order_qty);

        // Check 1311025 exists across 2 separate plants
        $po1025_kip1 = PurchasingOutstanding::where('part_number', '1311025')->where('factory_code', 'KIP 1')->first();
        $this->assertNotNull($po1025_kip1);
        $this->assertEquals(200, $po1025_kip1->order_qty);

        $po1025_kip4 = PurchasingOutstanding::where('part_number', '1311025')->where('factory_code', 'KIP 4')->first();
        $this->assertNotNull($po1025_kip4);
        $this->assertEquals(250, $po1025_kip4->order_qty);

        // Check Forecasting table has all distinct plant records and did NOT overwrite each other
        $fc1004_kip1 = Forecasting::where('part_number', '1311004')->where('factory_code', 'KIP 1')->where('periode', '2026-01')->first();
        $this->assertNotNull($fc1004_kip1);
        $this->assertEquals(300, $fc1004_kip1->po_qty);

        $fc1004_kip2 = Forecasting::where('part_number', '1311004')->where('factory_code', 'KIP 2')->where('periode', '2026-01')->first();
        $this->assertNotNull($fc1004_kip2);
        $this->assertEquals(350, $fc1004_kip2->po_qty);

        $fc1004_kip4 = Forecasting::where('part_number', '1311004')->where('factory_code', 'KIP 4')->where('periode', '2026-01')->first();
        $this->assertNotNull($fc1004_kip4);
        $this->assertEquals(400, $fc1004_kip4->po_qty);

        // Check Outstanding page does not show duplicate popup
        $pageResponse = $this->actingAs($this->user)->get(route('purchasing.outstanding'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertDontSee('modalDuplicatesNotification');
    }

    public function test_genuine_duplicate_on_same_plant_triggers_warning_modal()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        $sheet->setCellValue('I1', 'DEC-25');
        $sheet->setCellValue('M1', 'JAN-26');

        $sheet->setCellValue('A4', 'Supplier Code');
        $sheet->setCellValue('B4', 'Supplier Name');
        $sheet->setCellValue('C4', 'Plant');
        $sheet->setCellValue('D4', 'Kategori');
        $sheet->setCellValue('E4', 'Material Code');
        $sheet->setCellValue('F4', 'Description');
        $sheet->setCellValue('G4', 'Unit price');
        $sheet->setCellValue('H4', 'Currency');

        // Row 5: 1311004 (KIP 1)
        $sheet->setCellValue('A5', 'C146');
        $sheet->setCellValue('B5', 'PT. SUMBER AGUNG SEJAHTERA ABADI');
        $sheet->setCellValue('C5', 'KIP1');
        $sheet->setCellValue('D5', 'PUR-04');
        $sheet->setCellValue('E5', '1311004');
        $sheet->setCellValue('F5', 'STRETCH FILM 17 mcr');
        $sheet->setCellValue('G5', '113100');
        $sheet->setCellValue('H5', 'IDR');
        $sheet->setCellValue('O5', 300);

        // Row 6: Exact duplicate: 1311004 on SAME plant (KIP 1) and SAME supplier
        $sheet->setCellValue('A6', 'C146');
        $sheet->setCellValue('B6', 'PT. SUMBER AGUNG SEJAHTERA ABADI');
        $sheet->setCellValue('C6', 'KIP1');
        $sheet->setCellValue('D6', 'PUR-04');
        $sheet->setCellValue('E6', '1311004');
        $sheet->setCellValue('F6', 'STRETCH FILM 17 mcr (DUPLIKAT)');
        $sheet->setCellValue('G6', '113100');
        $sheet->setCellValue('H6', 'IDR');
        $sheet->setCellValue('O6', 150);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_genuinedup_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile($tempPath, 'test_dup.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->user)->post(route('purchasing.outstanding.import'), [
            'file' => $file,
            'import_currency' => 'IDR',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('import_duplicates_found');

        // Page should render duplicate modal with plant and part info
        $pageResponse = $this->actingAs($this->user)->get(route('purchasing.outstanding'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('modalDuplicatesNotification');
        $pageResponse->assertSee('1311004');
        $pageResponse->assertSee('KIP 1');
    }
}
