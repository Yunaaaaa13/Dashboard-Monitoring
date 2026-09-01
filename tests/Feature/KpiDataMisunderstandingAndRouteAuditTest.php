<?php

namespace Tests\Feature;

use App\Models\MasterPo;
use App\Models\PurchasingCategory;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use App\Models\User;
use App\Models\ActualProduction;
use App\Models\Inventory;
use App\Services\Analytics\ExceptionCenterService;
use App\Services\PurchasingCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiDataMisunderstandingAndRouteAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PurchasingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff@kawai.co.id',
        ]);

        $this->category = PurchasingCategory::create([
            'category_code' => 'RAW',
            'category_name' => 'Raw Materials',
            'pic_buyer'     => 'Test Buyer',
        ]);
    }

    /** @test */
    public function kurs_banner_links_to_valid_exchange_rate_index_route()
    {
        $this->actingAs($this->user);

        // Access dashboard or Step 1
        $response = $this->get(route('purchasing.outstanding'));
        $response->assertStatus(200);

        // Banner should render link to exchange-rate.index (URL: /exchange-rate)
        $response->assertSee(route('exchange-rate.index'));
    }

    /** @test */
    public function exception_center_links_reconciliation_to_data_health_route()
    {
        $this->actingAs($this->user);

        \App\Models\ImportBatch::create([
            'batch_id' => 'BATCH-TEST-001',
            'file_name' => 'test.xlsx',
            'template_type' => 'STEP1',
            'file_hash' => md5('test.xlsx'),
            'status' => 'COMMITTED',
            'reconciliation_status' => 'DISCREPANCY',
            'uploaded_by' => $this->user->id,
        ]);

        $diagnostics = ExceptionCenterService::getHealthDiagnostics();
        $reconciliationException = collect($diagnostics['exceptions'])->firstWhere('category', 'RECONCILIATION');

        $this->assertNotNull($reconciliationException);
        $this->assertEquals(route('system.data-health'), $reconciliationException['action_url']);
    }

    /** @test */
    public function history_controller_accepts_and_preserves_decimal_prices_without_truncation()
    {
        $this->actingAs($this->user);

        $item = PurchasingOutstanding::create([
            'part_number' => 'TEST-PART-001',
            'description' => 'Test Item Precision',
            'order_qty' => 500,
            'price' => 1.50,
            'complete' => 0,
            'status' => 'Pending',
        ]);

        // Submit update with a decimal price (e.g. $2.75)
        $response = $this->put(route('purchasing.history.outstanding.update', $item->id), [
            'part_number' => 'TEST-PART-001',
            'description' => 'Test Item Precision Updated',
            'order_qty' => 500,
            'price' => 2.75,
            'complete' => 250,
        ]);

        $response->assertRedirect(route('purchasing.history', ['tab' => 'outstanding']));

        $item->refresh();
        $this->assertEquals(2.75, (float) $item->price);
        $this->assertEquals(1375.0, (float) $item->amount); // 500 * 2.75
        $this->assertEquals(250, $item->complete);
        $this->assertEquals('On Progress', $item->status);
    }

    /** @test */
    public function multi_shipment_partial_deliveries_do_not_multiply_target_order()
    {
        $this->actingAs($this->user);

        // Create PO with target 1000 units delivered in 2 shipments (400 and 600)
        PurchasingLog::create([
            'po_reference' => 'PO-2026-001',
            'item_code' => 'MAT-A',
            'period_month' => '2026-06',
            'target_order' => 1000,
            'actual_received' => 400,
            'purchasing_category_id' => $this->category->id,
            'price' => 10.0,
            'currency' => 'USD',
        ]);

        PurchasingLog::create([
            'po_reference' => 'PO-2026-001',
            'item_code' => 'MAT-A',
            'period_month' => '2026-06',
            'target_order' => 1000,
            'actual_received' => 600,
            'purchasing_category_id' => $this->category->id,
            'price' => 10.0,
            'currency' => 'USD',
        ]);

        // 1. Check PurchasingCalculationService consolidated metrics
        $metrics = PurchasingCalculationService::getConsolidatedDashboardMetrics('2026', (string)$this->category->id);
        $this->assertEquals(1000, $metrics['total_target'], 'Target must be deduplicated to 1000, not 2000');
        $this->assertEquals(1000, $metrics['total_received']);
        $this->assertEquals(0, $metrics['total_pending']);
        $this->assertEquals(100.0, $metrics['fulfillment_pct'], 'Fulfillment must be 100%');

        // 2. Check Step 1 Forecast dashboard page data
        $response = $this->get(route('purchasing.outstanding', ['forecast_year' => 2026]));
        $response->assertStatus(200);
        $forecastTotalTarget = $response->viewData('forecastTotalTarget');
        $forecastTotalActual = $response->viewData('forecastTotalActual');
        $forecastFulfillPct  = $response->viewData('forecastFulfillPct');

        $this->assertEquals(1000, $forecastTotalTarget, 'Step 1 forecastTotalTarget must be deduplicated to 1000');
        $this->assertEquals(1000, $forecastTotalActual);
        $this->assertEquals(100.0, $forecastFulfillPct);
    }

    /** @test */
    public function data_trace_health_matrix_includes_all_seven_workflow_steps()
    {
        $this->actingAs($this->user);

        // Seed sample data for all steps
        PurchasingOutstanding::create([
            'part_number' => 'SKU-001',
            'description' => 'SKU 001 Description',
            'order_qty' => 100,
            'price' => 10,
        ]);

        MasterPo::create([
            'po' => 'PO-001',
            'item_code' => 'SKU-001',
            'qty' => 100,
            'tanggal' => '2026-06-01',
        ]);

        PurchasingLog::create([
            'po_reference' => 'PO-001',
            'item_code' => 'SKU-001',
            'period_month' => '2026-06',
            'actual_received' => 100,
            'target_order' => 100,
        ]);

        ActualProduction::create([
            'tanggal_produksi' => '2026-06-01',
            'item_code' => 'SKU-001',
            'qty' => 50,
            'factory_code' => 'P1',
        ]);

        Inventory::create([
            'tanggal_inventory' => '2026-06-01',
            'part_number' => 'SKU-001',
            'current_stock' => 50,
            'factory_code' => 'P1',
        ]);

        $controller = new \App\Http\Controllers\DataTraceController();
        $healthData = $controller->calculateHealthMatrix();

        $this->assertArrayHasKey('forecast', $healthData['modules']);
        $this->assertArrayHasKey('master_po', $healthData['modules']);
        $this->assertArrayHasKey('incoming', $healthData['modules']);
        $this->assertArrayHasKey('outstanding', $healthData['modules']);
        $this->assertArrayHasKey('actual_production', $healthData['modules']);
        $this->assertArrayHasKey('inventory', $healthData['modules']);
        $this->assertArrayHasKey('analysis', $healthData['modules']);

        $this->assertGreaterThan(0, $healthData['health_score']);
        $this->assertEquals(7, count($healthData['modules']));
    }
}
