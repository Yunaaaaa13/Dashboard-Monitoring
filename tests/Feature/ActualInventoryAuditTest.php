<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Inventory;
use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use App\Models\Forecasting;
use App\Models\TaxBudgetForecastRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActualInventoryAuditTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'staff']);
    }

    /**
     * Test 1: Financial valuation multi-currency normalization prevents astronomical trillions glitch.
     */
    public function test_multi_currency_valuation_normalization()
    {
        // 1 IDR item with price 41000 IDR, 1 USD item with price 2.50 USD
        TaxBudgetForecastRate::create([
            'exch_year' => 2026,
            'exch_month' => 8,
            'budget_rate' => 16600,
            'forecast_rate' => 16600,
        ]);

        PurchasingOutstanding::create([
            'part_number' => 'PART-IDR',
            'description' => 'Item in IDR',
            'price' => 41000,
            'currency' => 'IDR',
            'factory_code' => 'KIP1',
            'm1_po' => 100,
        ]);

        PurchasingOutstanding::create([
            'part_number' => 'PART-USD',
            'description' => 'Item in USD',
            'price' => 2.50,
            'currency' => 'USD',
            'factory_code' => 'KIP1',
            'm1_po' => 50,
        ]);

        Inventory::create([
            'part_number' => 'PART-IDR',
            'description' => 'Item in IDR',
            'factory_code' => 'KIP1',
            'current_stock' => 10, // 10 * 41000 = 410,000 IDR ~= 24.70 USD
            'tanggal_inventory' => '2026-08-19',
        ]);

        Inventory::create([
            'part_number' => 'PART-USD',
            'description' => 'Item in USD',
            'factory_code' => 'KIP1',
            'current_stock' => 20, // 20 * 2.50 = 50.00 USD = 830,000 IDR
            'tanggal_inventory' => '2026-08-19',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.actual-inventory'));
        $response->assertStatus(200);

        // Check values passed to view
        $valUsd = $response->viewData('kpiTotalInventoryValUsd');
        $valIdr = $response->viewData('kpiTotalInventoryValIdr');

        // Total IDR should be around 410,000 + 830,000 = 1,240,000 IDR
        // Total USD should be around 24.70 + 50.00 = 74.70 USD
        $this->assertGreaterThan(70, $valUsd);
        $this->assertLessThan(80, $valUsd); // Definitely NOT millions!
        $this->assertGreaterThan(1200000, $valIdr);
        $this->assertLessThan(1300000, $valIdr); // Definitely NOT trillions!
    }

    /**
     * Test 2: Grain Separation: Physical Positions vs Unique SKU Materials & 100% Match Rate.
     */
    public function test_grain_separation_and_match_rate()
    {
        // 1 SKU stored in 2 different plants (2 physical positions)
        PurchasingOutstanding::create([
            'part_number' => 'SKU-001',
            'description' => 'Test Multi-Plant Part',
            'price' => 10000,
            'currency' => 'IDR',
            'factory_code' => 'KIP1',
            'm1_po' => 100,
        ]);

        Inventory::create([
            'part_number' => 'SKU-001',
            'factory_code' => 'KIP1',
            'current_stock' => 50,
            'tanggal_inventory' => '2026-08-19',
        ]);

        Inventory::create([
            'part_number' => 'SKU-001',
            'factory_code' => 'KIP2',
            'current_stock' => 30,
            'tanggal_inventory' => '2026-08-19',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.actual-inventory'));
        $response->assertStatus(200);

        $this->assertEquals(2, $response->viewData('kpiTotalPositions'));
        $this->assertEquals(1, $response->viewData('kpiUniqueMaterialsCount'));
        $this->assertEquals(1, $response->viewData('matchedMaterialsCount'));
        $this->assertEquals(100.0, $response->viewData('matchPercentage'));
        $this->assertEquals(80, $response->viewData('kpiTotalInventoryQty'));
    }

    /**
     * Test 3: Potential Supply, Gap, Additional Requirement & Coverage Mathematics.
     */
    public function test_supply_gap_additional_requirement_and_coverage_math()
    {
        // Item 1: Deficit case (Demand = 100, Actual = 20, PO = 30, Potential Supply = 50, Gap = -50, AddReq = 50)
        PurchasingOutstanding::create([
            'part_number' => 'DEFICIT-01',
            'description' => 'Deficit Item',
            'price' => 10,
            'currency' => 'USD',
            'factory_code' => 'KIP1',
            'm1_po' => 100,
        ]);

        Inventory::create([
            'part_number' => 'DEFICIT-01',
            'factory_code' => 'KIP1',
            'current_stock' => 20,
            'tanggal_inventory' => '2026-08-19',
        ]);

        MasterPo::create([
            'item_code' => 'DEFICIT-01',
            'qty' => 30,
            'price' => 10,
            'currency' => 'USD',
        ]);

        // Item 2: Surplus case (Demand = 50, Actual = 60, PO = 0, Potential Supply = 60, Gap = +10, AddReq = 0)
        PurchasingOutstanding::create([
            'part_number' => 'SURPLUS-01',
            'description' => 'Surplus Item',
            'price' => 5,
            'currency' => 'USD',
            'factory_code' => 'KIP1',
            'm1_po' => 50,
        ]);

        Inventory::create([
            'part_number' => 'SURPLUS-01',
            'factory_code' => 'KIP1',
            'current_stock' => 60,
            'tanggal_inventory' => '2026-08-19',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.actual-inventory'));
        $response->assertStatus(200);

        // Demand: 100 + 50 = 150
        // Actual Stock: 20 + 60 = 80
        // Outstanding PO: 30 + 0 = 30
        // Potential Supply: 80 + 30 = 110
        // Net Supply Gap: 110 - 150 = -40
        // Additional Req: 150 - 110 = 40
        // Coverage %: (110 / 150) * 100 = 73.3%

        $this->assertEquals(150, $response->viewData('kpiTotalInventoryDemand'));
        $this->assertEquals(80, $response->viewData('kpiTotalInventoryQty'));
        $this->assertEquals(30, $response->viewData('kpiTotalOutstandingPo'));
        $this->assertEquals(110, $response->viewData('kpiTotalPotentialSupply'));
        $this->assertEquals(-40, $response->viewData('kpiNetSupplyGap'));
        $this->assertEquals(40, $response->viewData('kpiAdditionalRequirement'));
        $this->assertEquals(73.3, $response->viewData('kpiCoveragePercentage'));
    }

    /**
     * Test 4: Detail Table sum reconciles 100% with Dashboard KPI values.
     */
    public function test_detail_table_sum_reconciles_with_kpi_cards()
    {
        PurchasingOutstanding::create([
            'part_number' => 'ITEM-A',
            'description' => 'Item A',
            'price' => 10,
            'currency' => 'USD',
            'm1_po' => 200,
        ]);

        Inventory::create([
            'part_number' => 'ITEM-A',
            'current_stock' => 150,
            'tanggal_inventory' => '2026-08-19',
        ]);

        MasterPo::create([
            'item_code' => 'ITEM-A',
            'qty' => 100,
            'price' => 10,
            'currency' => 'USD',
        ]);

        PurchasingLog::create([
            'item_code' => 'ITEM-A',
            'actual_received' => 40,
            'price' => 10,
            'currency' => 'USD',
            'period_month' => '2026-08',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.actual-inventory'));
        $response->assertStatus(200);

        $matrix = $response->viewData('filteredMatrix');
        $sumActualStock = $matrix->sum('actual_stock');
        $sumDemand = $matrix->sum('inventory_demand');
        $sumOutstanding = $matrix->sum('outstanding_po_qty');
        $sumPotentialSupply = $matrix->sum('potential_supply');

        $this->assertEquals($sumActualStock, $response->viewData('kpiTotalInventoryQty'));
        $this->assertEquals($sumDemand, $response->viewData('kpiTotalInventoryDemand'));
        $this->assertEquals($sumOutstanding, $response->viewData('kpiTotalOutstandingPo'));
        $this->assertEquals($sumPotentialSupply, $response->viewData('kpiTotalPotentialSupply'));
    }

    /**
     * Test 5: Scorecard integrity & zero demand protection.
     */
    public function test_zero_demand_coverage_and_scorecard()
    {
        Inventory::create([
            'part_number' => 'ZERO-DEMAND',
            'current_stock' => 100,
            'tanggal_inventory' => '2026-08-19',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.actual-inventory'));
        $response->assertStatus(200);

        // Demand is 0 -> coverage should default cleanly to 100.0 without division by zero error
        $this->assertEquals(0, $response->viewData('kpiTotalInventoryDemand'));
        $this->assertEquals(100.0, $response->viewData('kpiCoveragePercentage'));
        $scorecard = $response->viewData('dataQualityScorecard');
        $this->assertIsArray($scorecard);
        $this->assertEquals(1, $scorecard['total_positions']);
        $this->assertEquals(0, $scorecard['negative_stock_records']);
    }
}
