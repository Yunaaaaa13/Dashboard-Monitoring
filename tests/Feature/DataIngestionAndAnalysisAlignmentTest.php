<?php

namespace Tests\Feature;

use App\Helpers\PurchasingReconciliationHelper;
use App\Models\ActualProduction;
use App\Models\DeliveryCategory;
use App\Models\Forecasting;
use App\Models\Inventory;
use App\Models\MasterPo;
use App\Models\PurchasingCategory;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use App\Models\TaxBudgetForecastRate;
use App\Models\TaxExchangeRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataIngestionAndAnalysisAlignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PurchasingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'username' => 'align_admin',
        ]);

        $this->category = PurchasingCategory::create([
            'category_code' => 'RAW',
            'category_name' => 'Raw Materials',
            'pic_buyer'     => 'Buyer Alpha',
        ]);

        DeliveryCategory::firstOrCreate(
            ['code' => 'LOC'],
            ['name' => 'Local Supplier']
        );

        // Seed exchange rates
        TaxBudgetForecastRate::create([
            'exch_year'     => 2026,
            'exch_month'    => 7,
            'currency_code' => 2,
            'budget_rate'   => 16500,
        ]);

        TaxExchangeRate::create([
            'exch_year'         => 2026,
            'exch_month'        => 7,
            'week_code'         => 1,
            'currency_code'     => 2,
            'tax_exchange_rate' => 16500,
        ]);
    }

    /**
     * 1. Test Ingesting New Master PO & Receipts stays strictly reconciled.
     */
    public function test_new_master_po_and_receipt_data_stay_reconciled(): void
    {
        // Insert Master PO records
        MasterPo::create([
            'po'        => 'PO-2026-TEST-01',
            'item_code' => 'MAT-TEST-001',
            'qty'       => 500,
            'tanggal'   => '2026-07-01',
            'supplier'  => 'Vendor Alpha',
        ]);

        MasterPo::create([
            'po'        => 'PO-2026-TEST-02',
            'item_code' => 'MAT-TEST-002',
            'qty'       => 300,
            'tanggal'   => '2026-07-05',
            'supplier'  => 'Vendor Beta',
        ]);

        // Insert partial receipts
        PurchasingLog::create([
            'item_code'       => 'MAT-TEST-001',
            'po_reference'    => 'PO-2026-TEST-01',
            'receipt_date'    => '2026-07-10',
            'period_month'    => '2026-07',
            'actual_received' => 350,
            'price'           => 10.0,
            'currency'        => 'USD',
            'supplier_name'   => 'Vendor Alpha',
            'user_id'         => $this->admin->id,
        ]);

        // Over-delivery receipt
        PurchasingLog::create([
            'item_code'       => 'MAT-TEST-002',
            'po_reference'    => 'PO-2026-TEST-02',
            'receipt_date'    => '2026-07-12',
            'period_month'    => '2026-07',
            'actual_received' => 350, // 50 over PO
            'price'           => 15.0,
            'currency'        => 'USD',
            'supplier_name'   => 'Vendor Beta',
            'user_id'         => $this->admin->id,
        ]);

        $reconciliation = PurchasingReconciliationHelper::reconcileTotals();

        $this->assertEquals(800, $reconciliation->master_po_total_qty);
        $this->assertEquals(700, $reconciliation->matched_receipt_qty);
        $this->assertEquals(150, $reconciliation->total_outstanding); // 500 - 350 = 150
        $this->assertEquals(50, $reconciliation->total_over_delivery);  // max(350 - 300, 0) = 50
        $this->assertTrue($reconciliation->is_consistent);
    }

    /**
     * 2. Test Ingesting New Forecast aligns with Analysis Grid & Monthly Insights.
     */
    public function test_new_forecast_ingestion_updates_analysis_grid_and_insights(): void
    {
        // Seed PurchasingOutstanding & Forecasting
        $item = PurchasingOutstanding::create([
            'part_number'            => 'PART-ALIGNED-01',
            'description'            => 'Aligned Part 01',
            'price'                  => 25.0,
            'currency'               => 'USD',
            'plan_stock'             => 100,
            'plan_outstand'          => 50,
            'm1_po'                  => 400,
            'm1_prod'                => 150,
            'm2_po'                  => 200,
            'm2_prod'                => 100,
            'delivery_category_code' => 'LOC',
        ]);

        Forecasting::updateOrCreate(
            ['part_number' => 'PART-ALIGNED-01', 'periode' => '2026-07'],
            [
                'period_month' => '2026-07',
                'forecast_qty' => 400,
                'price'        => 25.0,
                'currency'     => 'USD',
            ]
        );

        Forecasting::updateOrCreate(
            ['part_number' => 'PART-ALIGNED-01', 'periode' => '2026-08'],
            [
                'period_month' => '2026-08',
                'forecast_qty' => 200,
                'price'        => 25.0,
                'currency'     => 'USD',
            ]
        );

        $response = $this->actingAs($this->admin)->get(route('purchasing.analysis'));
        $response->assertOk()
            ->assertViewHas('comparisonMonthlyInsights')
            ->assertViewHas('displayGrid');

        $insights = $response->viewData('comparisonMonthlyInsights');
        $this->assertNotEmpty($insights);

        // Month 1 (Jul 2026) check
        $m1 = $insights->firstWhere('month_num', 1);
        $this->assertNotNull($m1);
        $this->assertGreaterThan(0, $m1->forecast_amount_usd);

        // Month 2 (Aug 2026) check: should reflect 50% decrease from 400 to 200 units
        $m2 = $insights->firstWhere('month_num', 2);
        $this->assertNotNull($m2);
        $this->assertEquals('SIGNIFICANT_DECREASE', $m2->forecast_direction);
    }

    /**
     * 3. Test Ingesting Production Deducts Stock Consistently in Roll-Forward.
     */
    public function test_actual_production_ingestion_updates_roll_forward_stock(): void
    {
        $item = PurchasingOutstanding::create([
            'part_number'            => 'PART-PROD-01',
            'description'            => 'Production Test Part',
            'price'                  => 10.0,
            'currency'               => 'USD',
            'plan_stock'             => 500,
            'plan_outstand'          => 0,
            'delivery_category_code' => 'LOC',
        ]);

        ActualProduction::create([
            'item_code'        => 'PART-PROD-01',
            'tanggal_produksi' => '2026-07-15',
            'qty'              => 200,
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.analysis'));
        $response->assertOk();

        $displayGrid = $response->viewData('displayGrid');
        $matched = $displayGrid->firstWhere('item_code', 'PART-PROD-01');
        $this->assertNotNull($matched);

        // In Month 1, actual stock = initial stock (500) + delivery (0) - prod (200) = 300
        $this->assertEquals(300, $matched->actual_grid[1]->stock);
    }

    /**
     * 4. Test Ingesting Actual Inventory Updates Slide 3 and Reconciles Supply.
     */
    public function test_actual_inventory_ingestion_updates_slide_3_and_supply_matrix(): void
    {
        $item = PurchasingOutstanding::create([
            'part_number'            => 'PART-INV-01',
            'description'            => 'Inventory Test Part',
            'price'                  => 50.0,
            'currency'               => 'USD',
            'plan_stock'             => 100,
            'plan_outstand'          => 50,
            'm1_po'                  => 200,
            'm1_prod'                => 80,
            'delivery_category_code' => 'LOC',
        ]);

        Inventory::create([
            'part_number'       => 'PART-INV-01',
            'description'       => 'Inventory Test Part',
            'tanggal_inventory' => '2026-07-01',
            'm0_inventory'      => 120,
            'current_stock'     => 120,
            'unit_price'        => 50.0,
            'currency'          => 'USD',
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.analysis'));
        $response->assertOk()
            ->assertViewHas('chartInvActualStock')
            ->assertViewHas('chartInvForecastStock');

        $reconInventory = PurchasingReconciliationHelper::reconcileInventory();
        $this->assertGreaterThanOrEqual(120, $reconInventory->total_actual_inventory);
    }

    /**
     * 5. Test Multi-Month Mathematical Alignment Across All 12 Months.
     */
    public function test_multi_month_mathematical_alignment(): void
    {
        PurchasingOutstanding::create([
            'part_number'            => 'PART-MATH-01',
            'description'            => 'Math Alignment Test',
            'price'                  => 20.0,
            'currency'               => 'USD',
            'plan_stock'             => 1000,
            'plan_outstand'          => 200,
            'm1_po'                  => 500, 'm1_prod' => 300,
            'm2_po'                  => 600, 'm2_prod' => 400,
            'm3_po'                  => 700, 'm3_prod' => 500,
            'delivery_category_code' => 'LOC',
        ]);

        $response = $this->actingAs($this->admin)->get(route('purchasing.analysis'));
        $response->assertOk();

        $displayGrid = $response->viewData('displayGrid');
        $gridItem = $displayGrid->firstWhere('item_code', 'PART-MATH-01');
        $this->assertNotNull($gridItem);

        // Verify Forecast roll-forward formula for Month 1, 2, 3:
        // Month 1: stock = 1000 + 500 (m1_po) - 300 (m1_prod) = 1200
        $this->assertEquals(1200, $gridItem->forecast_grid[1]->stock);
        // Month 2: stock = 1200 + 600 (m2_po) - 400 (m2_prod) = 1400
        $this->assertEquals(1400, $gridItem->forecast_grid[2]->stock);
        // Month 3: stock = 1400 + 700 (m3_po) - 500 (m3_prod) = 1600
        $this->assertEquals(1600, $gridItem->forecast_grid[3]->stock);
    }
}
