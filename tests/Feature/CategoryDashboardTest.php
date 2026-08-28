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
            'role' => 'admin',
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
}
