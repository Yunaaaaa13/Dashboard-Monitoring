<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ComparisonAnalysisService;
use App\Models\User;
use App\Models\TaxBudgetForecastRate;
use App\Models\TaxExchangeRate;
use App\Models\PurchasingOutstanding;
use App\Models\PurchasingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComparisonAndSlide1AuditTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'staff']);
        $this->seedTestData();
    }

    private function seedTestData()
    {
        $cat = \App\Models\PurchasingCategory::create([
            'category_code' => 'PUR-01',
            'category_name' => 'General Materials',
            'pic_buyer'     => 'Staff Buyer',
            'status'        => 'active',
        ]);

        // 1. Budget exchange rate
        for ($m = 1; $m <= 12; $m++) {
            TaxBudgetForecastRate::create([
                'exch_year' => 2026,
                'exch_month' => $m,
                'budget_rate' => 16600,
                'forecast_rate' => 16600,
            ]);
        }

        // 2. Actual exchange rate (July & August 2026)
        TaxExchangeRate::create([
            'exch_year' => 2026,
            'exch_month' => 7,
            'period_week' => 'W1',
            'tax_exchange_rate' => 16300,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-07',
        ]);
        TaxExchangeRate::create([
            'exch_year' => 2026,
            'exch_month' => 8,
            'period_week' => 'W1',
            'tax_exchange_rate' => 16400,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-07',
        ]);

        // 3. Purchasing Outstanding Forecast:
        // Item A: 1000 Qty @ $1.50 in July (M1), 2000 Qty @ $1.50 in August (M2), 500 Qty @ $3.00 in Sept (M3)
        PurchasingOutstanding::create([
            'part_number' => 'MAT-001',
            'description' => 'Raw Material A',
            'supplier_name' => 'PT. SUPPLIER UTAMA',
            'category_id' => $cat->id,
            'price' => 1.50,
            'currency' => 'USD',
            'factory_code' => 'KIP1',
            'm1_po' => 1000,
            'm2_po' => 2000,
            'm3_po' => 500,
        ]);

        // Item B: 500 Qty @ 33,200 IDR ($2.00) in July (M1), 500 Qty in August (M2)
        PurchasingOutstanding::create([
            'part_number' => 'MAT-002',
            'description' => 'Raw Material B',
            'supplier_name' => 'PT. MITRA JAYA',
            'category_id' => $cat->id,
            'price' => 33200,
            'currency' => 'IDR',
            'factory_code' => 'KIP1',
            'm1_po' => 500,
            'm2_po' => 500,
            'm3_po' => 0,
        ]);

        // 4. Purchasing Log (Incoming):
        // July: 800 Qty of MAT-001 @ $1.40 + 400 Qty of MAT-002 @ 32,600 IDR ($2.00)
        PurchasingLog::create([
            'purchasing_category_id' => $cat->id,
            'po_reference' => 'PO-2026-001',
            'item_code' => 'MAT-001',
            'item_name' => 'Raw Material A',
            'supplier_name' => 'PT. SUPPLIER UTAMA',
            'actual_received' => 800,
            'price' => 1.40,
            'currency' => 'USD',
            'receipt_date' => '2026-07-15',
            'period_month' => '2026-07',
        ]);
        PurchasingLog::create([
            'purchasing_category_id' => $cat->id,
            'po_reference' => 'PO-2026-002',
            'item_code' => 'MAT-002',
            'item_name' => 'Raw Material B',
            'supplier_name' => 'PT. MITRA JAYA',
            'actual_received' => 400,
            'price' => 32600,
            'currency' => 'IDR',
            'receipt_date' => '2026-07-20',
            'period_month' => '2026-07',
        ]);

        // August: 1800 Qty of MAT-001 @ $1.45
        PurchasingLog::create([
            'purchasing_category_id' => $cat->id,
            'po_reference' => 'PO-2026-003',
            'item_code' => 'MAT-001',
            'item_name' => 'Raw Material A',
            'supplier_name' => 'PT. SUPPLIER UTAMA',
            'actual_received' => 1800,
            'price' => 1.45,
            'currency' => 'USD',
            'receipt_date' => '2026-08-10',
            'period_month' => '2026-08',
        ]);
        // September: NO INCOMING TRANSACTIONS (Simulating Future Period)
    }

    /**
     * Test A: July 2026 Complete Validation (Incoming & Forecast present)
     */
    public function test_july_2026_complete_validation()
    {
        $dataset = ComparisonAnalysisService::buildComparisonDataset([
            'year' => 2026,
            'start_month' => 7,
            'duration' => 12
        ]);

        $this->assertNotEmpty($dataset);
        $jul = $dataset->firstWhere('month', 1);

        $this->assertNotNull($jul);
        $this->assertEquals('COMPLETE', $jul->data_status);
        $this->assertEquals(1500, $jul->forecast_qty); // 1000 + 500
        $this->assertEquals(2500.00, $jul->forecast_amount_usd); // (1000 * 1.50) + (500 * (33200/16600)) = 1500 + 1000 = 2500
        $this->assertEquals(1.67, $jul->forecast_price_usd); // 2500 / 1500 = 1.6667 -> 1.67
        
        $this->assertEquals(1200, $jul->incoming_qty); // 800 + 400
        $this->assertNotNull($jul->incoming_amount_usd);
        $this->assertNotNull($jul->incoming_price_usd);
        $this->assertTrue($jul->is_first_month);
    }

    /**
     * Test B: August 2026 Complete Validation & MoM calculations
     */
    public function test_august_2026_complete_and_mom_calculations()
    {
        $dataset = ComparisonAnalysisService::buildComparisonDataset([
            'year' => 2026,
            'start_month' => 7,
            'duration' => 12
        ]);

        $aug = $dataset->firstWhere('month', 2);

        $this->assertNotNull($aug);
        $this->assertEquals('COMPLETE', $aug->data_status);
        $this->assertFalse($aug->is_first_month);
        $this->assertEquals(2500, $aug->forecast_qty); // 2000 + 500
        $this->assertEquals(4000.00, $aug->forecast_amount_usd); // (2000 * 1.50) + (500 * 2.00) = 3000 + 1000 = 4000
        $this->assertEquals(1.60, $aug->forecast_price_usd); // 4000 / 2500 = 1.60
        $this->assertEquals(1800, $aug->incoming_qty);

        // MoM Forecast calculations should be mathematically exact:
        // July FC Price = 2500/1500 = 1.6667, August FC Price = 4000/2500 = 1.60 -> Delta: -4.00%
        $jul = $dataset->firstWhere('month', 1);
        $this->assertEquals(-4.00, $aug->mom_fc_price_pct);

        $expectedAmtMoM = round((($aug->forecast_amount_usd - $jul->forecast_amount_usd) / $jul->forecast_amount_usd) * 100, 2);
        $this->assertEquals($expectedAmtMoM, $aug->mom_fc_amount_pct);
    }

    /**
     * Test C: September 2026+ Missing Incoming Data must be null (not 0.00)
     */
    public function test_september_and_future_months_render_null_for_incoming()
    {
        $dataset = ComparisonAnalysisService::buildComparisonDataset([
            'year' => 2026,
            'start_month' => 7,
            'duration' => 12
        ]);

        $sep = $dataset->firstWhere('month', 3);

        $this->assertNotNull($sep);
        $this->assertEquals('FORECAST_ONLY', $sep->data_status);
        $this->assertEquals(500, $sep->forecast_qty);
        $this->assertEquals(750.00, $sep->forecast_amount_usd); // 500 * 1.50
        $this->assertEquals(1.50, $sep->forecast_price_usd);

        // Incoming MUST BE NULL
        $this->assertNull($sep->incoming_qty);
        $this->assertNull($sep->incoming_amount_usd);
        $this->assertNull($sep->incoming_price_usd);
        $this->assertNull($sep->incoming_amount_idr);
        $this->assertNull($sep->incoming_price_idr);
        $this->assertNull($sep->variance_amount_usd);
    }

    /**
     * Test D: Weighted Average Price is Amount / Qty (not simple average)
     */
    public function test_weighted_average_price_logic()
    {
        $dataset = ComparisonAnalysisService::buildComparisonDataset([
            'year' => 2026,
            'start_month' => 7,
            'duration' => 12
        ]);

        foreach ($dataset as $row) {
            if ($row->forecast_qty > 0 && $row->forecast_amount_usd > 0) {
                $calculatedPrice = round($row->forecast_amount_usd / $row->forecast_qty, 2);
                $this->assertEquals($calculatedPrice, $row->forecast_price_usd);
            }
            if ($row->incoming_qty > 0 && $row->incoming_amount_usd > 0) {
                $calculatedIncomingPrice = round($row->incoming_amount_usd / $row->incoming_qty, 2);
                $this->assertEquals($calculatedIncomingPrice, $row->incoming_price_usd);
            }
        }
    }

    /**
     * Test E: Slide 1 Executive Summary matches Dataset Aggregates
     */
    public function test_slide1_executive_summary_reconciled()
    {
        $dataset = ComparisonAnalysisService::buildComparisonDataset([
            'year' => 2026,
            'start_month' => 7,
            'duration' => 12
        ]);

        $summary = ComparisonAnalysisService::getSlide1ExecutiveSummary($dataset);

        $this->assertNotNull($summary);
        $this->assertEquals(round($dataset->sum('forecast_amount_usd'), 2), $summary->total_forecast_amount_usd);
        $this->assertEquals(round($dataset->whereNotNull('incoming_amount_usd')->sum('incoming_amount_usd'), 2), $summary->total_incoming_amount_usd);
        $this->assertEquals(2, $summary->validated_months_count);
        $this->assertGreaterThan(0, $summary->completion_pct);
    }

    /**
     * Test F: Supplier / Vendor Filtering
     */
    public function test_vendor_filtering()
    {
        $allDataset = ComparisonAnalysisService::buildComparisonDataset([
            'year' => 2026,
            'start_month' => 7,
            'duration' => 12
        ]);

        $filteredDataset = ComparisonAnalysisService::buildComparisonDataset([
            'year' => 2026,
            'start_month' => 7,
            'duration' => 12,
            'vendor' => 'PT. MITRA JAYA'
        ]);

        // Filtered should only have MAT-002 (500 in Jul, 500 in Aug, 0 in Sep)
        $this->assertEquals(1000, $filteredDataset->sum('forecast_qty'));
        $this->assertEquals(2000.00, $filteredDataset->sum('forecast_amount_usd'));
        $this->assertEquals(400, $filteredDataset->whereNotNull('incoming_qty')->sum('incoming_qty'));
    }

    /**
     * Test G: HTTP Feature test accessing /purchasing/analysis
     */
    public function test_purchasing_analysis_page_loads_successfully()
    {
        $response = $this->actingAs($this->user)->get('/purchasing/analysis');

        $response->assertStatus(200);
        $response->assertSee('EXECUTIVE SUMMARY MATRIX');
        $response->assertSee('tableFxComparison');
    }

    /**
     * Test H: Business Period Metadata & QC Audit
     */
    public function test_period_metadata_and_qc_audit()
    {
        $dataset = ComparisonAnalysisService::buildComparisonDataset([
            'year' => 2026,
            'start_month' => 7,
            'duration' => 12
        ]);

        $metadata = ComparisonAnalysisService::getAnalysisPeriodMetadata($dataset);

        $this->assertNotNull($metadata);
        $this->assertStringContainsString('Juli 2026', $metadata->forecast_period);
        $this->assertStringContainsString('Juli 2026 – Agustus 2026', $metadata->actual_period);
        $this->assertStringContainsString('Juli 2026', $metadata->current_period);
        $this->assertEquals(2, $metadata->validated_months_count);
        $this->assertTrue($metadata->qc_audit->is_period_aligned);
        $this->assertTrue($metadata->qc_audit->sanitized_div_zero);
        $this->assertFalse($metadata->qc_audit->has_div_zero_risk);
    }

    /**
     * Test I: Multi-Metric Matrix Aggregations (SUM/AVG Amount & Weighted/SUM Price)
     */
    public function test_multi_metric_matrix_calculations()
    {
        $dataset = ComparisonAnalysisService::buildComparisonDataset([
            'year' => 2026,
            'start_month' => 7,
            'duration' => 12
        ]);

        $jul = $dataset->firstWhere('month', 1);
        $this->assertNotNull($jul);

        // July has 2 items: MAT-001 ($1500 amount, $1.50 price) and MAT-002 ($1000 amount, $2.00 price)
        // SUM Amount = $2500
        $this->assertEquals(2500.00, $jul->sum_forecast_amount_usd);
        // AVG Amount = $2500 / 2 = $1250
        $this->assertEquals(1250.00, $jul->avg_forecast_amount_usd);
        // Weighted AVG Price = $2500 / 1500 = $1.67
        $this->assertEquals(1.67, $jul->weighted_avg_forecast_price_usd);
        // SUM Price = $1.50 + $2.00 = $3.50
        $this->assertEquals(3.50, $jul->sum_forecast_price_usd);

        // September (month 3) has no incoming -> Incoming multi-metrics must be null
        $sep = $dataset->firstWhere('month', 3);
        $this->assertNull($sep->sum_incoming_amount_usd);
        $this->assertNull($sep->avg_incoming_amount_usd);
        $this->assertNull($sep->weighted_avg_incoming_price_usd);
        $this->assertNull($sep->sum_incoming_price_usd);
    }
}
