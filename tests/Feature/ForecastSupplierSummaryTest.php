<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Forecasting;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use App\Models\TaxBudgetForecastRate;
use App\Models\TaxExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastSupplierSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'supervisor']);

        // Seed exchange rate for 2026-07 and 2026-08 (Budget Rate: 16500)
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

        TaxBudgetForecastRate::create([
            'exch_year' => 2026,
            'exch_month' => 8,
            'currency_type' => 2,
            'budget_rate' => 16500,
        ]);
        TaxExchangeRate::create([
            'exch_year' => 2026,
            'exch_month' => 8,
            'week_code' => 1,
            'currency_type' => 2,
            'tax_exchange_rate' => 16500,
        ]);
    }

    /**
     * Test bahwa halaman analysis menyertakan seluruh variabel Supplier Summary, Multi-Currency & MoM Analytics.
     */
    public function test_analysis_provides_all_supplier_summary_variables(): void
    {
        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-SUP-01',
            'drawing'                => 'DWG-SUP-01',
            'description'            => 'TEST ITEM A',
            'supplier_name'          => 'PT MITRA AMANDA',
            'price'                  => 1.50,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 100,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.analysis'));

        $response->assertStatus(200);
        $response->assertViewHas([
            'supplierMonthlySummary',
            'supplierTotals',
            'supplierRanking',
            'chartSupplierLabels',
            'chartSupplierForecastAmount',
            'chartSupplierActualAmount',
            'chartSupplierForecastAmountUsd',
            'chartSupplierActualAmountUsd',
            'chartSupplierForecastAmountIdr',
            'chartSupplierActualAmountIdr',
            'chartSupplierForecastQty',
            'chartSupplierActualQty',
            'supplierMoMAnalytics',
            'supplierReconciliationPassed',
        ]);
    }

    /**
     * Test agregasi multi-supplier, perankingan, dan perhitungan kontribusi persentase.
     */
    public function test_supplier_ranking_and_monthly_aggregation_multi_supplier(): void
    {
        // Supplier A: 2 Items
        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-A1',
            'drawing'                => 'DWG-A1',
            'description'            => 'ITEM A1',
            'supplier_name'          => 'PT MITRA AMANDA',
            'price'                  => 2.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 500,
        ]);
        Forecasting::create([
            'part_number'   => 'ITEM-A1',
            'forecast_qty'  => 500,
            'price'         => 2.00,
            'currency'      => 'USD',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);
        PurchasingLog::create([
            'item_code'       => 'ITEM-A1',
            'item_name'       => 'ITEM A1',
            'po_reference'    => 'PO-A1',
            'receipt_date'    => '2026-07-10',
            'period_month'    => '2026-07',
            'target_order'    => 500,
            'actual_received' => 400,
            'price'           => 2.00,
            'currency'        => 'USD',
            'supplier_name'   => 'PT MITRA AMANDA',
            'user_id'         => $this->user->id,
        ]);

        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-A2',
            'drawing'                => 'DWG-A2',
            'description'            => 'ITEM A2',
            'supplier_name'          => 'PT MITRA AMANDA',
            'price'                  => 1.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 1000,
        ]);
        Forecasting::create([
            'part_number'   => 'ITEM-A2',
            'forecast_qty'  => 1000,
            'price'         => 1.00,
            'currency'      => 'USD',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);
        PurchasingLog::create([
            'item_code'       => 'ITEM-A2',
            'item_name'       => 'ITEM A2',
            'po_reference'    => 'PO-A2',
            'receipt_date'    => '2026-07-11',
            'period_month'    => '2026-07',
            'target_order'    => 1000,
            'actual_received' => 1000,
            'price'           => 1.00,
            'currency'        => 'USD',
            'supplier_name'   => 'PT MITRA AMANDA',
            'user_id'         => $this->user->id,
        ]);

        // Supplier B: 1 Item
        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-B1',
            'drawing'                => 'DWG-B1',
            'description'            => 'ITEM B1',
            'supplier_name'          => 'PT SURYARAYA NUSATAMA',
            'price'                  => 5.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 100,
        ]);
        Forecasting::create([
            'part_number'   => 'ITEM-B1',
            'forecast_qty'  => 100,
            'price'         => 5.00,
            'currency'      => 'USD',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);
        PurchasingLog::create([
            'item_code'       => 'ITEM-B1',
            'item_name'       => 'ITEM B1',
            'po_reference'    => 'PO-B1',
            'receipt_date'    => '2026-07-12',
            'period_month'    => '2026-07',
            'target_order'    => 100,
            'actual_received' => 120,
            'price'           => 5.00,
            'currency'        => 'USD',
            'supplier_name'   => 'PT SURYARAYA NUSATAMA',
            'user_id'         => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'year' => 2026,
            'start_month' => 'JUN', // Month 1 = JULY (2026-07)
            'duration' => 1,
        ]));

        $response->assertStatus(200);

        $supplierRanking = $response->viewData('supplierRanking');
        $monthlySummary  = $response->viewData('supplierMonthlySummary');

        // Verify Ranking Order (PT MITRA AMANDA: $2,000 vs PT SURYARAYA: $500)
        $this->assertCount(2, $supplierRanking);
        $this->assertEquals('PT MITRA AMANDA', $supplierRanking[0]->supplier);
        $this->assertEquals('PT SURYARAYA NUSATAMA', $supplierRanking[1]->supplier);

        // Verify PT MITRA AMANDA totals (2 items, 1500 fc qty, 1400 act qty)
        $this->assertEquals(2, $supplierRanking[0]->item_count);
        $this->assertEquals(1500, $supplierRanking[0]->total_forecast_qty);
        $this->assertEquals(1400, $supplierRanking[0]->total_actual_qty);
        $this->assertEquals(2000.0, $supplierRanking[0]->total_forecast_amount_usd);
        $this->assertEquals(1800.0, $supplierRanking[0]->total_incoming_amount_usd);
        $this->assertEquals(-200.0, $supplierRanking[0]->variance_amount_usd);
        $this->assertEquals('93.3%', $supplierRanking[0]->achievement_pct);

        // Verify Contribution % (Total Forecast = $2,500. MITRA = 80.0%, SURYARAYA = 20.0%)
        $this->assertEquals(80.0, $supplierRanking[0]->contribution_pct);
        $this->assertEquals(20.0, $supplierRanking[1]->contribution_pct);

        // Verify Monthly Summary structure for Month 1 (JULY)
        $this->assertArrayHasKey('PT MITRA AMANDA', $monthlySummary);
        $mitraM1 = $monthlySummary['PT MITRA AMANDA'][1];
        $this->assertEquals(1500, $mitraM1['forecast_qty']);
        $this->assertEquals(1400, $mitraM1['actual_qty']);
        $this->assertEquals(-100, $mitraM1['variance_qty']);
        $this->assertEquals('Under Forecast', $mitraM1['status']);
    }

    /**
     * Test Normalisasi Multi-Currency Supplier (Item USD + Item IDR digabungkan akurat pada USD & IDR).
     */
    public function test_multi_currency_supplier_normalization_usd_and_idr(): void
    {
        // Supplier X: Item 1 in USD ($10.00)
        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-USD',
            'drawing'                => 'DWG-USD',
            'description'            => 'USD PART',
            'supplier_name'          => 'PT MULTI CURRENCY',
            'price'                  => 10.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'IMP',
            'm1_po'                  => 100, // 100 PCS @ $10 = $1,000 = Rp 16,500,000
        ]);
        Forecasting::create([
            'part_number'   => 'ITEM-USD',
            'forecast_qty'  => 100,
            'price'         => 10.00,
            'currency'      => 'USD',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);
        PurchasingLog::create([
            'item_code'       => 'ITEM-USD',
            'item_name'       => 'USD PART',
            'po_reference'    => 'PO-USD-1',
            'receipt_date'    => '2026-07-05',
            'period_month'    => '2026-07',
            'target_order'    => 100,
            'actual_received' => 100,
            'price'           => 10.00,
            'currency'        => 'USD',
            'supplier_name'   => 'PT MULTI CURRENCY',
            'user_id'         => $this->user->id,
        ]);

        // Supplier X: Item 2 in IDR (Rp 165,000 per unit -> @ 16500 = $10.00)
        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-IDR',
            'drawing'                => 'DWG-IDR',
            'description'            => 'IDR PART',
            'supplier_name'          => 'PT MULTI CURRENCY',
            'price'                  => 165000,
            'currency'               => 'IDR',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 100, // 100 PCS @ Rp 165,000 = Rp 16,500,000 = $1,000
        ]);
        Forecasting::create([
            'part_number'   => 'ITEM-IDR',
            'forecast_qty'  => 100,
            'price'         => 165000,
            'currency'      => 'IDR',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);
        PurchasingLog::create([
            'item_code'       => 'ITEM-IDR',
            'item_name'       => 'IDR PART',
            'po_reference'    => 'PO-IDR-1',
            'receipt_date'    => '2026-07-06',
            'period_month'    => '2026-07',
            'target_order'    => 100,
            'actual_received' => 100,
            'price'           => 165000,
            'currency'        => 'IDR',
            'supplier_name'   => 'PT MULTI CURRENCY',
            'user_id'         => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'vendor' => 'PT MULTI CURRENCY',
            'year' => 2026,
            'start_month' => 'JUN', // Month 1 = JULY (2026-07)
            'duration' => 1,
        ]));

        $response->assertStatus(200);

        $supplierRanking = $response->viewData('supplierRanking');
        $this->assertCount(1, $supplierRanking);

        // USD Total should be $1,000 (Item 1) + $1,000 (Item 2) = $2,000
        $this->assertEquals(2000.0, $supplierRanking[0]->total_forecast_amount_usd);
        $this->assertEquals(2000.0, $supplierRanking[0]->total_incoming_amount_usd);

        // IDR Total should be Rp 16,500,000 + Rp 16,500,000 = Rp 33,000,000
        $this->assertEquals(33000000.0, $supplierRanking[0]->total_forecast_amount_idr);
        $this->assertEquals(33000000.0, $supplierRanking[0]->total_incoming_amount_idr);
    }

    /**
     * Test Rekonsiliasi Matematis: SUM(Item Code) = SUM(Supplier) = Global Monthly Total (Anti Double-Counting).
     */
    public function test_mathematical_reconciliation_sum_items_equals_sum_suppliers(): void
    {
        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-1',
            'drawing'                => 'DWG-1',
            'description'            => 'ITEM 1',
            'supplier_name'          => 'SUPPLIER A',
            'price'                  => 10.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 300,
        ]);
        Forecasting::create([
            'part_number'   => 'ITEM-1',
            'forecast_qty'  => 300,
            'price'         => 10.00,
            'currency'      => 'USD',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);

        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-2',
            'drawing'                => 'DWG-2',
            'description'            => 'ITEM 2',
            'supplier_name'          => 'SUPPLIER B',
            'price'                  => 20.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 700,
        ]);
        Forecasting::create([
            'part_number'   => 'ITEM-2',
            'forecast_qty'  => 700,
            'price'         => 20.00,
            'currency'      => 'USD',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'year' => 2026,
            'start_month' => 'JUN', // Month 1 = JULY (2026-07)
            'duration' => 1,
        ]));

        $response->assertStatus(200);

        $displayGrid = $response->viewData('displayGrid');
        $supplierTotals = $response->viewData('supplierTotals');
        $chartSupplierForecastQty = $response->viewData('chartSupplierForecastQty');
        $reconciliationPassed = $response->viewData('supplierReconciliationPassed');

        // Sum from items = 300 + 700 = 1000
        $sumItems = $displayGrid->sum(fn($g) => $g->forecast_grid[1]->forecast ?? 0);
        $this->assertEquals(1000, $sumItems);

        // Sum from suppliers = 300 + 700 = 1000
        $sumSuppliers = array_sum(array_column($supplierTotals, 'total_forecast_qty'));
        $this->assertEquals(1000, $sumSuppliers);

        // Global Chart Quantity for M1 = 1000
        $this->assertEquals(1000, $chartSupplierForecastQty[0]);

        // Reconciliation flag must pass
        $this->assertTrue($reconciliationPassed);
    }

    /**
     * Test Month-over-Month (MoM) Quantity & Material Contribution Analysis.
     */
    public function test_month_over_month_quantity_change_and_contributor_breakdown(): void
    {
        // Item 1: July 1,000 PCS -> August 1,500 PCS (+500 PCS)
        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-MOM-1',
            'drawing'                => 'DWG-MOM-1',
            'description'            => 'MOM PART A',
            'supplier_name'          => 'PT MOM SUPPLIER',
            'price'                  => 5.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 1000,
            'm2_po'                  => 1500,
        ]);
        Forecasting::updateOrCreate(
            ['part_number' => 'ITEM-MOM-1', 'period_month' => '2026-07'],
            [
                'forecast_qty' => 1000,
                'price'        => 5.00,
                'currency'     => 'USD',
                'periode'      => '2026-07',
            ]
        );
        Forecasting::updateOrCreate(
            ['part_number' => 'ITEM-MOM-1', 'period_month' => '2026-08'],
            [
                'forecast_qty' => 1500,
                'price'        => 5.00,
                'currency'     => 'USD',
                'periode'      => '2026-08',
            ]
        );

        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'year' => 2026,
            'start_month' => 'JUN', // Month 1 = JULY (2026-07), Month 2 = AUGUST (2026-08)
            'duration' => 2,
        ]));

        $response->assertStatus(200);

        $momAnalytics = $response->viewData('supplierMoMAnalytics');
        $this->assertArrayHasKey(2, $momAnalytics);

        $m2 = $momAnalytics[2];
        $this->assertEquals(1000, $m2->prev_forecast_qty);
        $this->assertEquals(1500, $m2->curr_forecast_qty);
        $this->assertEquals(500, $m2->diff_forecast_qty);
        $this->assertEquals(50.0, $m2->diff_forecast_pct);

        // Top supplier driver
        $this->assertNotEmpty($m2->top_supplier_drivers);
        $this->assertEquals('PT MOM SUPPLIER', $m2->top_supplier_drivers[0]['supplier']);
        $this->assertEquals(500, $m2->top_supplier_drivers[0]['delta_qty']);

        // Top item driver
        $this->assertNotEmpty($m2->top_item_drivers);
        $this->assertEquals('ITEM-MOM-1', $m2->top_item_drivers[0]['item_code']);
        $this->assertEquals(500, $m2->top_item_drivers[0]['delta_qty']);
    }

    /**
     * Test filter global vendor: membatasi seluruh visual dan data chart ke vendor yang dipilih.
     */
    public function test_supplier_filter_applies_globally_and_focuses_summary(): void
    {
        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-A',
            'drawing'                => 'DWG-A',
            'description'            => 'ITEM A',
            'supplier_name'          => 'PT MITRA AMANDA',
            'price'                  => 10.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 200,
        ]);
        Forecasting::create([
            'part_number'   => 'ITEM-A',
            'forecast_qty'  => 200,
            'price'         => 10.00,
            'currency'      => 'USD',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);
        PurchasingLog::create([
            'item_code'       => 'ITEM-A',
            'item_name'       => 'ITEM A',
            'po_reference'    => 'PO-A',
            'receipt_date'    => '2026-07-10',
            'period_month'    => '2026-07',
            'target_order'    => 200,
            'actual_received' => 200,
            'price'           => 10.00,
            'currency'        => 'USD',
            'supplier_name'   => 'PT MITRA AMANDA',
            'user_id'         => $this->user->id,
        ]);

        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-B',
            'drawing'                => 'DWG-B',
            'description'            => 'ITEM B',
            'supplier_name'          => 'PT SURYARAYA NUSATAMA',
            'price'                  => 5.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 100,
        ]);
        Forecasting::create([
            'part_number'   => 'ITEM-B',
            'forecast_qty'  => 100,
            'price'         => 5.00,
            'currency'      => 'USD',
            'periode'       => '2026-07',
            'period_month'  => '2026-07',
        ]);
        PurchasingLog::create([
            'item_code'       => 'ITEM-B',
            'item_name'       => 'ITEM B',
            'po_reference'    => 'PO-B',
            'receipt_date'    => '2026-07-12',
            'period_month'    => '2026-07',
            'target_order'    => 100,
            'actual_received' => 100,
            'price'           => 5.00,
            'currency'        => 'USD',
            'supplier_name'   => 'PT SURYARAYA NUSATAMA',
            'user_id'         => $this->user->id,
        ]);

        // Request with vendor filter = PT MITRA AMANDA
        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'vendor' => 'PT MITRA AMANDA',
            'year' => 2026,
            'start_month' => 'JUN', // Month 1 = JULY (2026-07)
            'duration' => 1,
        ]));

        $response->assertStatus(200);

        $displayGrid = $response->viewData('displayGrid');
        $this->assertCount(1, $displayGrid);
        $this->assertEquals('ITEM-A', $displayGrid->first()->item_code);

        // Chart Forecast Qty for M1 should be 200 (only Mitra Amanda, ignoring Suryaraya's 100)
        $chartSupplierForecastQty = $response->viewData('chartSupplierForecastQty');
        $this->assertEquals(200, $chartSupplierForecastQty[0]);

        $chartSupplierForecastAmount = $response->viewData('chartSupplierForecastAmount');
        $this->assertEquals(2000.0, $chartSupplierForecastAmount[0]);
    }

    /**
     * Test perlindungan division-by-zero dan penanganan unplanned actuals pada supplier.
     */
    public function test_supplier_summary_handles_zero_and_unplanned_data_gracefully(): void
    {
        // Unplanned delivery without forecast (m1_po = 0)
        PurchasingOutstanding::create([
            'part_number'            => 'ITEM-UNPLANNED',
            'drawing'                => 'DWG-UNP',
            'description'            => 'UNPLANNED MATERIAL',
            'supplier_name'          => 'PT SPECIALIST METAL',
            'price'                  => 3.00,
            'currency'               => 'USD',
            'delivery_category_code' => 'LOC',
            'm1_po'                  => 0,
        ]);
        PurchasingLog::create([
            'item_code'       => 'ITEM-UNPLANNED',
            'item_name'       => 'UNPLANNED MATERIAL',
            'po_reference'    => 'PO-UNP',
            'receipt_date'    => '2026-07-15',
            'period_month'    => '2026-07',
            'target_order'    => 0,
            'actual_received' => 50,
            'price'           => 3.00,
            'currency'        => 'USD',
            'supplier_name'   => 'PT SPECIALIST METAL',
            'user_id'         => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'year' => 2026,
            'start_month' => 'JUN', // Month 1 = JULY (2026-07)
            'duration' => 1,
        ]));

        $response->assertStatus(200);

        $supplierRanking = $response->viewData('supplierRanking');
        $monthlySummary  = $response->viewData('supplierMonthlySummary');

        $this->assertCount(1, $supplierRanking);
        $this->assertEquals('Unplanned', $supplierRanking[0]->achievement_pct);
        $this->assertEquals(50, $supplierRanking[0]->total_actual_qty);

        $m1 = $monthlySummary['PT SPECIALIST METAL'][1];
        $this->assertEquals('Unplanned', $m1['achievement_pct']);
        $this->assertEquals('Unplanned', $m1['status']);
        $this->assertEquals(50, $m1['variance_qty']);
    }
}
