<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Forecasting;
use App\Models\PurchasingLog;
use App\Models\ActualProduction;
use App\Models\Inventory;
use App\Models\PurchasingOutstanding;
use App\Models\TaxBudgetForecastRate;
use App\Models\TaxExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastActualComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'supervisor']);

        // Seed exchange rate for 2026-07
        TaxBudgetForecastRate::create([
            'exch_year' => 2026,
            'exch_month' => 7,
            'currency_type' => 2, // USD
            'budget_rate' => 16500,
        ]);
        TaxExchangeRate::create([
            'exch_year' => 2026,
            'exch_month' => 7,
            'week_code' => 1,
            'currency_type' => 2,
            'tax_exchange_rate' => 16500,
        ]);
    }

    /**
     * Test Acceptance Criteria 1: Item 1312006 Juli 2026
     * Forecast: Plan Stock = 64, Forecast Qty = 210, Price = $0.51, Forecast Amount = $107.10
     * Actual: Incoming = 210, Prod = 0, Ending Inventory = 274 (64 + 210 - 0), Price = $0.47
     * Inventory Amount = 274 * $0.47 = $128.78 (bukan $99.31)
     * Achievement = 100%, Qty Variance = 0, Status = Sesuai.
     */
    public function test_item_1312006_jul_2026_forecast_actual_comparison_and_valuation(): void
    {
        // 1. Setup Master Forecast / Outstanding Model (Item 1312006)
        $item = PurchasingOutstanding::create([
            'part_number'            => '1312006',
            'drawing'                => 'DWG-1312006',
            'description'            => 'BACK COVER FELT',
            'supplier_name'          => 'PT SURYARAYA NUSATAMA',
            'plan_stock'             => 64,
            'plan_outstand'          => 0,
            'price'                  => 0.47,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 210,
            'm1_forecast'            => 210,
            'm1_delivery'            => 210,
            'm1_prod'                => 0,
            'm2_prod'                => 200,
        ]);

        // 2. Setup Forecasting Table (Forecast Price $0.51)
        Forecasting::create([
            'part_number'   => '1312006',
            'forecast_qty'  => 210,
            'price'         => 0.51,
            'currency'      => 'USD',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);

        // 3. Setup Actual Incoming Receipt (Step 3: 210 PCS @ $0.47)
        PurchasingLog::create([
            'item_code'       => '1312006',
            'item_name'       => 'BACK COVER FELT',
            'po_reference'    => 'PO-2026-001',
            'receipt_date'    => '2026-07-15',
            'period_month'    => '2026-07',
            'target_order'    => 210,
            'actual_received' => 210,
            'price'           => 0.47,
            'currency'        => 'USD',
            'supplier_name'   => 'PT SURYARAYA NUSATAMA',
            'user_id'         => $this->user->id,
        ]);

        // 4. Setup Actual Production (Step 5: 0 PCS on 2026-07)
        ActualProduction::create([
            'tanggal_produksi' => '2026-07-20',
            'item_code'        => '1312006',
            'factory_code'     => 'KIP 1',
            'qty'              => 0,
        ]);

        // Request Analysis Page starting July 2026 (index 1 = JUL)
        $response = $this->actingAs($this->user)->get('/purchasing/analysis?item_code=1312006&start_month=JUN&year=2026&duration=12');
        $response->assertStatus(200);

        $displayGrid = $response->viewData('displayGrid');
        $this->assertNotEmpty($displayGrid);

        $gridItem = $displayGrid->firstWhere('item_code', '1312006');
        $this->assertNotNull($gridItem);

        // Month 1 (JULY) Forecast & Actual rows
        $fJuly = $gridItem->forecast_grid[1];
        $aJuly = $gridItem->actual_grid[1];

        // Verifikasi Forecast Target & Amount ($210 * $0.51 = $107.10)
        $this->assertEquals(210, $fJuly->forecast);
        $this->assertEquals(0.51, $fJuly->price_usd);
        $this->assertEquals(210 * 0.51, $fJuly->forecast_amount_usd);

        // Verifikasi Actual Incoming & Ending Stock (64 init + 210 received - 0 prod = 274)
        $this->assertEquals(210, $aJuly->delivery);
        $this->assertEquals(0, $aJuly->prod);
        $this->assertEquals(274, $aJuly->stock);

        // Verifikasi Inventory Price ($0.47)
        $this->assertEquals(0.47, $aJuly->price_usd);

        // Verifikasi Actual Inventory Amount (274 * $0.47 = $128.78) - BUKAN $99.31 (210 * $0.47)!
        $this->assertEquals(274 * 0.47, $aJuly->inventory_amount_usd);
        $this->assertEquals(210 * 0.47, $aJuly->incoming_amount_usd);

        // Verifikasi Achievement % (210 / 210 = 100%)
        $this->assertEquals('100%', $aJuly->achievement_pct);
        $this->assertEquals(0, $aJuly->variance_qty);
        $this->assertEquals('Sesuai', $aJuly->status);
    }

    /**
     * Test Division by Zero Prevention: Forecast Qty = 0 tidak boleh menghasilkan #DIV/0!
     */
    public function test_zero_forecast_division_by_zero_prevention(): void
    {
        $item = PurchasingOutstanding::create([
            'part_number'            => 'TEST-ZERO-DIV',
            'drawing'                => 'DWG-ZERO',
            'description'            => 'ZERO DEMAND ITEM',
            'plan_stock'             => 0,
            'price'                  => 1.00,
            'currency'               => 'USD',
            'm1_forecast'            => 0,
            'm1_po'                  => 0,
            'm1_delivery'            => 0,
            'm1_prod'                => 0,
            'm2_prod'                => 0,
        ]);

        $response = $this->actingAs($this->user)->get('/purchasing/analysis?item_code=TEST-ZERO-DIV&start_month=JUN&year=2026&duration=1');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString('#DIV/0!', $content);

        $gridItem = $response->viewData('displayGrid')->firstWhere('item_code', 'TEST-ZERO-DIV');
        $aJuly = $gridItem->actual_grid[1];

        $this->assertEquals('-', $aJuly->achievement_pct);
        $this->assertEquals('No Demand', $aJuly->status);
    }

    /**
     * Test Unplanned Actual: Forecast Qty = 0 tetapi Actual Incoming > 0
     */
    public function test_unplanned_actual_without_forecast(): void
    {
        PurchasingOutstanding::create([
            'part_number'            => 'UNPLANNED-MAT',
            'description'            => 'UNPLANNED SHIPMENT',
            'plan_stock'             => 0,
            'price'                  => 2.50,
            'currency'               => 'USD',
            'm1_forecast'            => 0,
            'm1_po'                  => 0,
        ]);

        PurchasingLog::create([
            'item_code'       => 'UNPLANNED-MAT',
            'item_name'       => 'UNPLANNED SHIPMENT',
            'receipt_date'    => '2026-07-10',
            'period_month'    => '2026-07',
            'target_order'    => 50,
            'actual_received' => 50,
            'price'           => 2.50,
            'currency'        => 'USD',
            'user_id'         => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/purchasing/analysis?item_code=UNPLANNED-MAT&start_month=JUN&year=2026&duration=1');
        $response->assertStatus(200);

        $gridItem = $response->viewData('displayGrid')->firstWhere('item_code', 'UNPLANNED-MAT');
        $aJuly = $gridItem->actual_grid[1];

        $this->assertEquals('Unplanned', $aJuly->achievement_pct);
        $this->assertEquals('Unplanned', $aJuly->status);
        $this->assertEquals(50, $aJuly->delivery);
        $this->assertEquals(50 * 2.50, $aJuly->inventory_amount_usd);
    }

    /**
     * Test Under and Over Forecast Variance & Statuses
     */
    public function test_under_and_over_forecast_variance_calculations(): void
    {
        // Item Under Forecast: Forecast = 200, Actual = 160 (80%) -> Under Forecast
        PurchasingOutstanding::create([
            'part_number' => 'MAT-UNDER',
            'description' => 'UNDER FORECAST ITEM',
            'plan_stock'  => 10,
            'price'       => 1.0,
            'm1_forecast' => 200,
            'm1_po'       => 200,
        ]);
        PurchasingLog::create([
            'item_code'       => 'MAT-UNDER',
            'period_month'    => '2026-07',
            'receipt_date'    => '2026-07-05',
            'actual_received' => 160,
            'price'           => 1.0,
            'user_id'         => $this->user->id,
        ]);

        // Item Over Forecast: Forecast = 100, Actual = 150 (150%) -> Over Forecast
        PurchasingOutstanding::create([
            'part_number' => 'MAT-OVER',
            'description' => 'OVER FORECAST ITEM',
            'plan_stock'  => 10,
            'price'       => 1.0,
            'm1_forecast' => 100,
            'm1_po'       => 100,
        ]);
        PurchasingLog::create([
            'item_code'       => 'MAT-OVER',
            'period_month'    => '2026-07',
            'receipt_date'    => '2026-07-05',
            'actual_received' => 150,
            'price'           => 1.0,
            'user_id'         => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/purchasing/analysis?start_month=JUN&year=2026&duration=1');
        $response->assertStatus(200);

        $grid = $response->viewData('displayGrid');

        $underItem = $grid->firstWhere('item_code', 'MAT-UNDER');
        $this->assertEquals('80%', $underItem->actual_grid[1]->achievement_pct);
        $this->assertEquals(-40, $underItem->actual_grid[1]->variance_qty);
        $this->assertEquals('Under Forecast', $underItem->actual_grid[1]->status);

        $overItem = $grid->firstWhere('item_code', 'MAT-OVER');
        $this->assertEquals('150%', $overItem->actual_grid[1]->achievement_pct);
        $this->assertEquals(50, $overItem->actual_grid[1]->variance_qty);
        $this->assertEquals('Over Forecast', $overItem->actual_grid[1]->status);
    }
}
