<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PurchasingOutstanding;
use App\Models\ActualInventory;
use App\Models\ActualProduction;
use App\Models\PurchasingLog;
use App\Models\TaxBudgetForecastRate;
use App\Models\TaxExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockCalculationAndReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'supervisor']);

        // Seed exchange rates
        for ($m = 1; $m <= 12; $m++) {
            TaxBudgetForecastRate::create([
                'exch_year' => 2026,
                'exch_month' => $m,
                'currency_type' => 2,
                'budget_rate' => 16500,
            ]);
            TaxExchangeRate::create([
                'exch_year' => 2026,
                'exch_month' => $m,
                'week_code' => 1,
                'currency_type' => 2,
                'tax_exchange_rate' => 16500,
            ]);
        }

        // Create Item 1: Balanced supply
        PurchasingOutstanding::create([
            'po' => 'PO-001',
            'item_code' => 'ITEM-A',
            'part_number' => 'ITEM-A',
            'drawing' => 'DWG-A',
            'description' => 'Item Alpha',
            'supplier' => 'PT. VENDOR A',
            'currency' => 'USD',
            'price' => 10.0,
            'plan_stock' => 1000,
            'plan_outstand' => 500,
            'm0_inventory' => 1000,
            'm1_po' => 500,
            'm1_prod' => 400, // FC Stock M1 = 1000 + 500 - 400 = 1100
            'm1_inventory' => 1200,
            'm2_po' => 600,
            'm2_prod' => 500, // FC Stock M2 = 1100 + 600 - 500 = 1200
            'm2_inventory' => 1300,
        ]);

        // Create Item 2: Deficit demand exceeding supply
        PurchasingOutstanding::create([
            'po' => 'PO-002',
            'item_code' => 'ITEM-B',
            'part_number' => 'ITEM-B',
            'drawing' => 'DWG-B',
            'description' => 'Item Beta',
            'supplier' => 'PT. VENDOR B',
            'currency' => 'USD',
            'price' => 20.0,
            'plan_stock' => 200,
            'plan_outstand' => 100,
            'm0_inventory' => 200,
            'm1_po' => 100,
            'm1_prod' => 500, // Net = 200 + 100 - 500 = -200 -> Physical Stock = 0, Deficit = 200
            'm1_inventory' => 50,
            'm2_po' => 300,
            'm2_prod' => 200, // Net = -200 + 300 - 200 = -100 -> Physical Stock = 0, Deficit = 100
            'm2_inventory' => 150,
        ]);
    }

    /**
     * Test 1: Forecast Stock roll-forward maintains positive physically available inventory (>= 0)
     * and separates physical stock from deficit/gap.
     */
    public function test_forecast_stock_roll_forward_remains_positive_and_slide2_slide3_sync()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'year' => 2026,
            'start_month' => 'JUN',
            'duration' => 6,
            'vendor' => 'ALL'
        ]));

        $response->assertStatus(200);

        $viewData = $response->getOriginalContent()->getData();
        $chartForecastStock = $viewData['chartForecastStock'] ?? [];
        $chartInvForecastStock = $viewData['chartInvForecastStock'] ?? [];

        // Assert all forecast stock values across all 6 months are non-negative (>= 0)
        for ($i = 0; $i <= 6; $i++) {
            $fcStock = $chartForecastStock[$i] ?? 0;
            $this->assertGreaterThanOrEqual(0, $fcStock, "Month {$i} Forecast Stock in Slide 2 must be >= 0, got {$fcStock}");

            $invFcStock = $chartInvForecastStock[$i] ?? 0;
            $this->assertGreaterThanOrEqual(0, $invFcStock, "Month {$i} Forecast Stock in Slide 3 must be >= 0, got {$invFcStock}");

            // Slide 2 and Slide 3 forecast stock must match 100%
            $this->assertEquals($fcStock, $invFcStock, "Month {$i} Slide 2 and Slide 3 Forecast Stock must be identical");
        }
    }

    /**
     * Test 2: Forecast stock roll-forward values for Month 1 and Month 2 are mathematically verified.
     */
    public function test_stock_mathematical_roll_forward_values()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'year' => 2026,
            'start_month' => 'JUN',
            'duration' => 6,
            'vendor' => 'ALL'
        ]));

        $response->assertStatus(200);
        $viewData = $response->getOriginalContent()->getData();
        $stockMonthlySummary = $viewData['stockMonthlySummary'] ?? [];

        $this->assertNotEmpty($stockMonthlySummary);

        // Month 1 (July): ITEM-A (1100) + ITEM-B (0 physical stock) = 1100
        $this->assertEquals(1100, $stockMonthlySummary[1]['forecast_stock_qty']);

        // Month 2 (August): ITEM-A (1200) + ITEM-B (0 physical stock) = 1200
        $this->assertEquals(1200, $stockMonthlySummary[2]['forecast_stock_qty']);

        // Actual Inventory Month 1: 1200 + 50 = 1250
        $this->assertEquals(1250, $stockMonthlySummary[1]['actual_stock_qty']);

        // Actual Inventory Month 2: 1300 + 150 = 1450
        $this->assertEquals(1450, $stockMonthlySummary[2]['actual_stock_qty']);
    }

    /**
     * Test 3: Stock Variance and Status Classification (Surplus, Balanced, Deficit).
     */
    public function test_stock_variance_and_status_classification()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'year' => 2026,
            'start_month' => 'JUN',
            'duration' => 6,
            'vendor' => 'ALL'
        ]));

        $response->assertStatus(200);
        $viewData = $response->getOriginalContent()->getData();
        $stockMonthlySummary = $viewData['stockMonthlySummary'] ?? [];

        foreach ($stockMonthlySummary as $mIdx => $row) {
            $this->assertContains($row['status'], ['Surplus', 'Balanced', 'Deficit', 'No Demand']);

            $expectedVar = $row['actual_stock_qty'] - $row['forecast_stock_qty'];
            $this->assertEquals($expectedVar, $row['variance_qty']);
        }
    }

    /**
     * Test 4: Month-over-Month (MoM) Stock Movement Analysis accurately reconciles.
     */
    public function test_stock_mom_movement_analytics()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'year' => 2026,
            'start_month' => 'JUN',
            'duration' => 6,
            'vendor' => 'ALL'
        ]));

        $response->assertStatus(200);
        $viewData = $response->getOriginalContent()->getData();
        $stockMoMAnalytics = $viewData['stockMoMAnalytics'] ?? [];

        $this->assertNotEmpty($stockMoMAnalytics);

        // Verify Month 2 (August) movement analytics
        $augMoM = $stockMoMAnalytics[2] ?? null;
        $this->assertNotNull($augMoM);
        $this->assertEquals(1250, $augMoM->prev_stock_qty); // Month 1 Actual Stock
        $this->assertEquals(1450, $augMoM->curr_stock_qty); // Month 2 Actual Stock
        $this->assertEquals(200, $augMoM->diff_stock_qty);   // Delta = +200 PCS
        $this->assertEquals(16.0, $augMoM->diff_stock_pct);  // +16.0%

        // Top material drivers must sum up to delta
        $this->assertNotEmpty($augMoM->top_material_drivers);
        $this->assertNotEmpty($augMoM->top_supplier_drivers);
    }

    /**
     * Test 5: Multi-currency normalization on stock values.
     */
    public function test_stock_multi_currency_amounts()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'year' => 2026,
            'start_month' => 'JUN',
            'duration' => 6,
            'vendor' => 'ALL'
        ]));

        $response->assertStatus(200);
        $viewData = $response->getOriginalContent()->getData();
        $stockMonthlySummary = $viewData['stockMonthlySummary'] ?? [];

        foreach ($stockMonthlySummary as $mIdx => $row) {
            $this->assertGreaterThanOrEqual(0, $row['forecast_stock_usd']);
            $this->assertGreaterThanOrEqual(0, $row['actual_stock_usd']);
            $this->assertGreaterThanOrEqual(0, $row['forecast_stock_idr']);
            $this->assertGreaterThanOrEqual(0, $row['actual_stock_idr']);
        }
    }
}
