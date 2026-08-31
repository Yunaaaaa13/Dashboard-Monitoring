<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PurchasingCategory;
use App\Models\PurchasingLog;
use App\Models\TaxBudgetForecastRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create([
            'role' => 'supervisor',
            'email' => 'admin_cat_test@kawai.co.id'
        ]);

        // Seed exchange rate for USD & IDR
        TaxBudgetForecastRate::create([
            'exch_year' => 2026,
            'exch_month' => 6,
            'currency_code' => 2, // USD/IDR
            'budget_rate' => 16500,
            'forecast_rate' => 16500,
        ]);
    }

    public function test_categories_page_renders_with_usd_metrics()
    {
        $category = PurchasingCategory::create([
            'category_code' => 'PUR-METAL',
            'category_name' => 'Metal & Screws',
            'pic_buyer' => $this->adminUser->name,
            'buyer_user_id' => $this->adminUser->id,
            'monthly_target_units' => 50000.00,
            'status' => 'Active',
        ]);

        // Log in USD
        PurchasingLog::create([
            'purchasing_category_id' => $category->id,
            'user_id' => $this->adminUser->id,
            'receipt_date' => '2026-06-15',
            'period_month' => '2026-06',
            'item_code' => 'SCR-001',
            'item_name' => 'Screw M4',
            'target_order' => 1000,
            'actual_received' => 1000,
            'price' => 10.00,
            'currency' => 'USD',
            'amount' => 10000.00,
        ]);

        // Log in IDR (165,000,000 IDR = 10,000 USD at 16,500 rate)
        PurchasingLog::create([
            'purchasing_category_id' => $category->id,
            'user_id' => $this->adminUser->id,
            'receipt_date' => '2026-06-20',
            'period_month' => '2026-06',
            'item_code' => 'PLT-001',
            'item_name' => 'Metal Plate',
            'target_order' => 500,
            'actual_received' => 500,
            'price' => 330000.00,
            'currency' => 'IDR',
            'amount' => 165000000.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('purchasing.categories'));
        $response->assertStatus(200);
        $response->assertSee('TARGET PENGADAAN (PLAN)');
        $response->assertSee('ACTUAL TERCAPAI (USD)');
        $response->assertSee('20,000.00'); // Total USD actual (10,000 + 10,000)
        $response->assertSee('100%'); // 20,000 / 20,000 = 100%
        $response->assertSee('1,500'); // 1,000 + 500 units
        $response->assertSee('2 baris'); // 2 transaction rows
    }

    public function test_categories_page_with_period_filter()
    {
        $category = PurchasingCategory::create([
            'category_code' => 'PUR-METAL2',
            'category_name' => 'Metal & Screws 2',
            'pic_buyer' => $this->adminUser->name,
            'buyer_user_id' => $this->adminUser->id,
            'monthly_target_units' => 10000.00,
            'status' => 'Active',
        ]);

        // June log
        PurchasingLog::create([
            'purchasing_category_id' => $category->id,
            'user_id' => $this->adminUser->id,
            'receipt_date' => '2026-06-15',
            'period_month' => '2026-06',
            'item_code' => 'SCR-001',
            'item_name' => 'Screw M4',
            'target_order' => 100,
            'actual_received' => 100,
            'price' => 10.00,
            'currency' => 'USD',
            'amount' => 1000.00,
        ]);

        // July log
        PurchasingLog::create([
            'purchasing_category_id' => $category->id,
            'user_id' => $this->adminUser->id,
            'receipt_date' => '2026-07-20',
            'period_month' => '2026-07',
            'item_code' => 'SCR-002',
            'item_name' => 'Screw M5',
            'target_order' => 200,
            'actual_received' => 200,
            'price' => 10.00,
            'currency' => 'USD',
            'amount' => 2000.00,
        ]);

        // Test filtering by period 2026-07
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.categories', ['period' => '2026-07']));
        $response->assertStatus(200);
        $response->assertSee('2,000.00'); // July actual USD
        $response->assertSee('200'); // July units
        $response->assertSee('1 baris'); // July row
    }

    public function test_store_and_update_category_with_usd_target()
    {
        $storeResponse = $this->actingAs($this->adminUser)->post(route('purchasing.categories.store'), [
            'category_code' => 'PUR-WOOD',
            'category_name' => 'Wood & Timber',
            'buyer_user_id' => $this->adminUser->id,
            'target_qty' => 15000,
            'monthly_target_units' => 60000.00,
            'status' => 'Active',
        ]);

        $storeResponse->assertRedirect(route('purchasing.categories'));
        $this->assertDatabaseHas('purchasing_categories', [
            'category_code' => 'PUR-WOOD',
            'category_name' => 'Wood & Timber',
            'target_qty' => 15000,
            'monthly_target_units' => 60000.00,
        ]);

        $category = PurchasingCategory::where('category_code', 'PUR-WOOD')->first();

        $updateResponse = $this->actingAs($this->adminUser)->put(route('purchasing.categories.update', $category->id), [
            'category_code' => 'PUR-WOOD',
            'category_name' => 'Wood & Soundboard Timber',
            'buyer_user_id' => $this->adminUser->id,
            'target_qty' => 17000,
            'monthly_target_units' => 85000.00,
            'status' => 'Active',
        ]);

        $updateResponse->assertRedirect(route('purchasing.categories'));
        $this->assertDatabaseHas('purchasing_categories', [
            'id' => $category->id,
            'category_name' => 'Wood & Soundboard Timber',
            'target_qty' => 17000,
            'monthly_target_units' => 85000.00,
        ]);
    }

    public function test_download_category_template()
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.categories.template'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_import_categories_from_csv()
    {
        $csvContent = "No,Kode Kategori,Nama Kategori Material,PIC Procurement,Target Kuantitas (Unit),Target Bulanan (USD),Status\n" .
                      "1,PUR-FABRIC,Fabric & Felt Parts,{$this->adminUser->name},12000,45000.00,Active\n" .
                      "2,PUR-CHEM,Chemicals & Paint,{$this->adminUser->name},8000,30000.00,Review\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('categories_test.csv', $csvContent);

        $response = $this->actingAs($this->adminUser)->post(route('purchasing.categories.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('purchasing.categories'));
        $this->assertDatabaseHas('purchasing_categories', [
            'category_code' => 'PUR-FABRIC',
            'category_name' => 'Fabric & Felt Parts',
            'target_qty' => 12000,
            'monthly_target_units' => 45000.00,
            'status' => 'Active',
        ]);
        $this->assertDatabaseHas('purchasing_categories', [
            'category_code' => 'PUR-CHEM',
            'category_name' => 'Chemicals & Paint',
            'target_qty' => 8000,
            'monthly_target_units' => 30000.00,
            'status' => 'Review',
        ]);
    }

    public function test_forecast_excel_import_resolves_and_syncs_category_column()
    {
        $cat1 = PurchasingCategory::create([
            'category_code' => 'PUR-01',
            'category_name' => 'Kayu Akustik & Soundboard Spruce',
            'pic_buyer'     => 'Staff Buyer',
            'status'        => 'active',
        ]);
        $cat4 = PurchasingCategory::create([
            'category_code' => 'PUR-04',
            'category_name' => 'Finishing Polyester Resin & Chemical',
            'pic_buyer'     => 'Staff Buyer',
            'status'        => 'active',
        ]);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Row 1: Header (Matching User's Excel)
        $sheet->setCellValue('A1', 'Supplier Code');
        $sheet->setCellValue('B1', 'Supplier Name');
        $sheet->setCellValue('C1', 'Plant');
        $sheet->setCellValue('D1', 'Kategori');
        $sheet->setCellValue('E1', 'Material Code');
        $sheet->setCellValue('F1', 'Description');
        $sheet->setCellValue('G1', 'Unit price');
        $sheet->setCellValue('H1', 'kurs');
        $sheet->setCellValue('I1', 'Plan Stock');
        $sheet->setCellValue('J1', 'Plan Outstand');

        // Row 2: Data with PUR-04
        $sheet->setCellValue('A2', 'C102');
        $sheet->setCellValue('B2', 'PT. TRI JAYA TEKNIK KARAWANG');
        $sheet->setCellValue('C2', 'KIP1');
        $sheet->setCellValue('D2', 'PUR-04');
        $sheet->setCellValue('E2', '1312006');
        $sheet->setCellValue('F2', 'GP BRACKET KOMPOU');
        $sheet->setCellValue('G2', '8470');
        $sheet->setCellValue('H2', 'USD');
        $sheet->setCellValue('I2', '100');
        $sheet->setCellValue('J2', '500');

        $tmpFile = tempnam(sys_get_temp_dir(), 'cat_test_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmpFile);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tmpFile,
            'forecast_category_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->adminUser)->post(route('purchasing.outstanding.import'), [
            'file' => $uploadedFile,
        ]);

        @unlink($tmpFile);

        $response->assertRedirect();
        
        // Ensure the record in purchasing_outstandings has category_id == $cat4->id (PUR-04), NOT defaulted to $cat1->id
        $this->assertDatabaseHas('purchasing_outstandings', [
            'part_number' => '1312006',
            'category_id' => $cat4->id,
            'factory_code' => 'KIP 1',
        ]);
    }

    public function test_integrated_importer_resolves_and_assigns_category_id_to_master_po_and_incoming()
    {
        $cat4 = PurchasingCategory::create([
            'category_code' => 'PUR-04',
            'category_name' => 'Finishing Polyester Resin & Chemical',
            'pic_buyer'     => 'Staff Buyer',
            'status'        => 'active',
        ]);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Supplier Code');
        $sheet->setCellValue('B1', 'Supplier Name');
        $sheet->setCellValue('C1', 'Delivery Date');
        $sheet->setCellValue('D1', 'Plant');
        $sheet->setCellValue('E1', 'Kategori');
        $sheet->setCellValue('F1', 'Material Code');
        $sheet->setCellValue('G1', 'Description');
        $sheet->setCellValue('H1', 'PO No.');
        $sheet->setCellValue('I1', 'Currency');
        $sheet->setCellValue('J1', 'Price');
        $sheet->setCellValue('K1', 'Plan');
        $sheet->setCellValue('L1', 'Result');

        $sheet->setCellValue('A2', 'C102');
        $sheet->setCellValue('B2', 'PT. TRI JAYA TEKNIK KARAWANG');
        $sheet->setCellValue('C2', '2026-07-15');
        $sheet->setCellValue('D2', 'KIP 1');
        $sheet->setCellValue('E2', 'PUR-04');
        $sheet->setCellValue('F2', '1312006');
        $sheet->setCellValue('G2', 'MAIN BOARD A');
        $sheet->setCellValue('H2', 'KI-TJT-0023');
        $sheet->setCellValue('I2', 'IDR');
        $sheet->setCellValue('J2', 8470);
        $sheet->setCellValue('K2', 210);
        $sheet->setCellValue('L2', 210);

        $tmpFile = tempnam(sys_get_temp_dir(), 'imp_test_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmpFile);

        $importer = app(\App\Services\Import\IntegratedPoIncomingImporter::class);
        $analysis = $importer->parseAndAnalyze($tmpFile);
        @unlink($tmpFile);

        $this->assertTrue($analysis['success']);
        $this->assertEquals($cat4->id, $analysis['master_po_rows'][0]['category_id']);
        $this->assertEquals('PUR-04', $analysis['master_po_rows'][0]['category_code']);
        $this->assertEquals($cat4->id, $analysis['incoming_rows'][0]['category_id']);

        $execResult = $importer->executeImport($analysis, $this->adminUser->id);
        $this->assertTrue($execResult['success']);

        $this->assertDatabaseHas('master_pos', [
            'item_code' => '1312006',
            'po' => 'KI-TJT-0023',
            'category_id' => $cat4->id,
        ]);

        $this->assertDatabaseHas('purchasing_logs', [
            'item_code' => '1312006',
            'po_reference' => 'KI-TJT-0023',
            'purchasing_category_id' => $cat4->id,
        ]);
    }

    public function test_ocr_category_normalizer_and_matcher()
    {
        $cat4 = PurchasingCategory::create([
            'category_code' => 'PUR-04',
            'category_name' => 'Finishing Polyester Resin & Chemical',
            'pic_buyer'     => 'Staff Buyer',
            'status'        => 'active',
        ]);

        // OCR Noise repair (Letter 'O' instead of digit '0', lower case, space variations)
        $this->assertEquals('PUR-04', \App\Services\DataValidation\InputNormalizer::normalizeCategoryCode('PUR-O4'));
        $this->assertEquals('PUR-04', \App\Services\DataValidation\InputNormalizer::normalizeCategoryCode('pur 04'));
        $this->assertEquals('PUR-04', \App\Services\DataValidation\InputNormalizer::normalizeCategoryCode('PUR04'));

        $matcher = app(\App\Services\Ocr\MasterDictionaryMatcher::class);
        $matched = $matcher->matchCategory('PUR-O4');
        $this->assertEquals($cat4->id, $matched['category_id']);
        $this->assertEquals('PUR-04', $matched['category_code']);

        $matchedByName = $matcher->matchCategory('Finishing Polyester');
        $this->assertEquals($cat4->id, $matchedByName['category_id']);
    }

    public function test_rm_kayu_and_industry_aliases_matching()
    {
        $cat1 = PurchasingCategory::create([
            'category_code' => 'PUR-01',
            'category_name' => 'material berbahan dasar kayu',
            'pic_buyer'     => 'Buyer Wood',
            'status'        => 'Active',
        ]);
        $cat2 = PurchasingCategory::create([
            'category_code' => 'PUR-02',
            'category_name' => 'material berbahan dasar logam',
            'pic_buyer'     => 'Buyer Metal',
            'status'        => 'Active',
        ]);
        $cat3 = PurchasingCategory::create([
            'category_code' => 'PUR-03',
            'category_name' => 'consumable tool',
            'pic_buyer'     => 'Buyer Tool',
            'status'        => 'Active',
        ]);
        $cat4 = PurchasingCategory::create([
            'category_code' => 'PUR-04',
            'category_name' => 'komponen packing',
            'pic_buyer'     => 'Buyer Packing',
            'status'        => 'Active',
        ]);

        $matcher = app(\App\Services\Ocr\MasterDictionaryMatcher::class);

        // Test RM Kayu and variations -> PUR-01
        $matchKayu1 = $matcher->matchCategory('RM KAYU');
        $this->assertEquals($cat1->id, $matchKayu1['category_id'], 'RM KAYU should match PUR-01');
        $this->assertEquals('PUR-01', $matchKayu1['category_code']);

        $matchKayu2 = $matcher->matchCategory('RM-KAYU');
        $this->assertEquals($cat1->id, $matchKayu2['category_id'], 'RM-KAYU should match PUR-01');

        $matchKayu3 = $matcher->matchCategory('RAW MATERIAL KAYU');
        $this->assertEquals($cat1->id, $matchKayu3['category_id'], 'RAW MATERIAL KAYU should match PUR-01');

        $matchKayu4 = $matcher->matchCategory('KAYU');
        $this->assertEquals($cat1->id, $matchKayu4['category_id'], 'KAYU should match PUR-01');

        $matchKayu5 = $matcher->matchCategory('WOOD');
        $this->assertEquals($cat1->id, $matchKayu5['category_id'], 'WOOD should match PUR-01');

        // Test RM Logam -> PUR-02
        $matchLogam = $matcher->matchCategory('RM LOGAM');
        $this->assertEquals($cat2->id, $matchLogam['category_id'], 'RM LOGAM should match PUR-02');

        // Test Tool -> PUR-03
        $matchTool = $matcher->matchCategory('TOOL');
        $this->assertEquals($cat3->id, $matchTool['category_id'], 'TOOL should match PUR-03');

        // Test Packing -> PUR-04
        $matchPacking = $matcher->matchCategory('PACKING');
        $this->assertEquals($cat4->id, $matchPacking['category_id'], 'PACKING should match PUR-04');
    }

    public function test_excel_import_with_rm_kayu_category_resolves_correctly()
    {
        $cat1 = PurchasingCategory::create([
            'category_code' => 'PUR-01',
            'category_name' => 'material berbahan dasar kayu',
            'pic_buyer'     => 'Buyer Wood',
            'status'        => 'Active',
        ]);
        $cat4 = PurchasingCategory::create([
            'category_code' => 'PUR-04',
            'category_name' => 'komponen packing',
            'pic_buyer'     => 'Buyer Packing',
            'status'        => 'Active',
        ]);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Supplier Code');
        $sheet->setCellValue('B1', 'Supplier Name');
        $sheet->setCellValue('C1', 'Plant');
        $sheet->setCellValue('D1', 'Kategori');
        $sheet->setCellValue('E1', 'Material Code');
        $sheet->setCellValue('F1', 'Description');
        $sheet->setCellValue('G1', 'Unit price');
        $sheet->setCellValue('H1', 'kurs');
        $sheet->setCellValue('I1', 'Plan Stock');
        $sheet->setCellValue('J1', 'Plan Outstand');

        // Row 2: Data with 'RM KAYU' (which previously defaulted incorrectly)
        $sheet->setCellValue('A2', 'K101');
        $sheet->setCellValue('B2', 'PT. KAYU NUSANTARA INDAH');
        $sheet->setCellValue('C2', 'KIP 1');
        $sheet->setCellValue('D2', 'RM KAYU');
        $sheet->setCellValue('E2', 'WOOD-SPRUCE-01');
        $sheet->setCellValue('F2', 'SPRUCE SOUNDBOARD LOG');
        $sheet->setCellValue('G2', '1250000');
        $sheet->setCellValue('H2', 'IDR');
        $sheet->setCellValue('I2', '50');
        $sheet->setCellValue('J2', '200');

        $tmpFile = tempnam(sys_get_temp_dir(), 'rm_kayu_test_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmpFile);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tmpFile,
            'forecast_rm_kayu_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->adminUser)->post(route('purchasing.outstanding.import'), [
            'file' => $uploadedFile,
        ]);

        @unlink($tmpFile);

        $response->assertRedirect();
        
        // Ensure the record in purchasing_outstandings has category_id == $cat1->id (PUR-01 / material berbahan dasar kayu)
        $this->assertDatabaseHas('purchasing_outstandings', [
            'part_number' => 'WOOD-SPRUCE-01',
            'category_id' => $cat1->id,
            'factory_code' => 'KIP 1',
        ]);
    }

    public function test_dashboard_displays_standby_status_when_category_has_zero_activity()
    {
        $cat1 = PurchasingCategory::create([
            'category_code' => 'PUR-01',
            'category_name' => 'material berbahan dasar kayu',
            'pic_buyer'     => 'Administrator System',
            'status'        => 'Active',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('dashboard.overview'));
        $response->assertStatus(200);
        $response->assertSee('PUR-01');
        $response->assertSee('Standby');
        $response->assertSee('Tidak ada sisa pending');
    }
}
