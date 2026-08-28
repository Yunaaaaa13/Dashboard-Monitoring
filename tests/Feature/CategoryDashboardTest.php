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
        $response->assertSee('TARGET BULANAN (USD)');
        $response->assertSee('ACTUAL TERCAPAI (USD)');
        $response->assertSee('50,000.00');
        $response->assertSee('20,000.00'); // Total USD actual (10,000 + 10,000)
        $response->assertSee('40%'); // 20,000 / 50,000 = 40%
    }

    public function test_store_and_update_category_with_usd_target()
    {
        $storeResponse = $this->actingAs($this->adminUser)->post(route('purchasing.categories.store'), [
            'category_code' => 'PUR-WOOD',
            'category_name' => 'Wood & Timber',
            'buyer_user_id' => $this->adminUser->id,
            'monthly_target_units' => 60000.00,
            'status' => 'Active',
        ]);

        $storeResponse->assertRedirect(route('purchasing.categories'));
        $this->assertDatabaseHas('purchasing_categories', [
            'category_code' => 'PUR-WOOD',
            'category_name' => 'Wood & Timber',
            'monthly_target_units' => 60000.00,
        ]);

        $category = PurchasingCategory::where('category_code', 'PUR-WOOD')->first();

        $updateResponse = $this->actingAs($this->adminUser)->put(route('purchasing.categories.update', $category->id), [
            'category_code' => 'PUR-WOOD',
            'category_name' => 'Wood & Soundboard Timber',
            'buyer_user_id' => $this->adminUser->id,
            'monthly_target_units' => 85000.00,
            'status' => 'Active',
        ]);

        $updateResponse->assertRedirect(route('purchasing.categories'));
        $this->assertDatabaseHas('purchasing_categories', [
            'id' => $category->id,
            'category_name' => 'Wood & Soundboard Timber',
            'monthly_target_units' => 85000.00,
        ]);
    }
}
