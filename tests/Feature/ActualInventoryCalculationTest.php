<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Inventory;
use App\Models\PurchasingOutstanding;
use App\Helpers\PurchasingReconciliationHelper;

/**
 * Test Aktual Inventory Calculation and Supply Integration.
 * Memvalidasi integrasi stok fisik aktual terhadap forecast, outstanding PO, dan supply coverage.
 */
class ActualInventoryCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Halaman Aktual Inventory memerlukan autentikasi.
     */
    public function test_actual_inventory_requires_auth(): void
    {
        $response = $this->get('/purchasing/actual-inventory');
        $response->assertRedirect('/login');
    }

    /**
     * Test: Halaman Aktual Inventory dapat diakses oleh user terautentikasi.
     */
    public function test_actual_inventory_page_loads_successfully(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/actual-inventory');
        $response->assertStatus(200);
    }

    /**
     * Test: Seluruh variabel KPI, matriks, dan chart tersedia pada view.
     */
    public function test_inventory_view_variables_available(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/actual-inventory');
        $response->assertStatus(200);

        $response->assertViewHas('kpiTotalInventoryQty');
        $response->assertViewHas('kpiTotalInventoryValUsd');
        $response->assertViewHas('kpiTotalInventoryValIdr');
        $response->assertViewHas('kpiTotalInventoryDemand');
        $response->assertViewHas('kpiTotalForecastDemand');
        $response->assertViewHas('kpiTotalDemandValUsd');
        $response->assertViewHas('kpiTotalOutstandingPo');
        $response->assertViewHas('kpiTotalPotentialSupply');
        $response->assertViewHas('kpiSurplusCount');
        $response->assertViewHas('kpiCoveredByPoCount');
        $response->assertViewHas('kpiCriticalDeficitCount');
        $response->assertViewHas('kpiCoveragePercentage');
        $response->assertViewHas('filteredMatrix');
        $response->assertViewHas('chartLabels');
        $response->assertViewHas('chartInventoryDemand');
        $response->assertViewHas('chartForecastStock');
        $response->assertViewHas('chartActualInventory');
        $response->assertViewHas('chartOutstandingPo');
        $response->assertViewHas('chartPotentialSupply');
        $response->assertViewHas('chartStatusDistribution');
        $response->assertViewHas('vendorOverviewList');
        $response->assertViewHas('vendorChartData');
    }

    /**
     * Test: Validasi struktur data diagnostik vendor dan kesiapan dataset diagram area.
     */
    public function test_vendor_diagnostic_and_area_chart_data_structure(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Vendor A: Critical (Demand = 100, Stock = 10, PO = 0)
        PurchasingOutstanding::create([
            'supplier_name' => 'PT. VENDOR CRITICAL',
            'supplier_code' => 'C001',
            'part_number'   => 'PART-CRIT-01',
            'description'   => 'Critical Item',
            'factory_code'  => 'KIP1',
            'order_qty'     => 100,
        ]);
        Inventory::create([
            'supplier_name' => 'PT. VENDOR CRITICAL',
            'supplier_code' => 'C001',
            'part_number'   => 'PART-CRIT-01',
            'factory_code'  => 'KIP1',
            'current_stock' => 10,
            'tanggal_inventory' => '2026-08-20',
        ]);

        // Vendor B: Healthy (Demand = 50, Stock = 80, PO = 0)
        PurchasingOutstanding::create([
            'supplier_name' => 'PT. VENDOR HEALTHY',
            'supplier_code' => 'C002',
            'part_number'   => 'PART-HLTH-01',
            'description'   => 'Healthy Item',
            'factory_code'  => 'KIP1',
            'order_qty'     => 50,
        ]);
        Inventory::create([
            'supplier_name' => 'PT. VENDOR HEALTHY',
            'supplier_code' => 'C002',
            'part_number'   => 'PART-HLTH-01',
            'factory_code'  => 'KIP1',
            'current_stock' => 80,
            'tanggal_inventory' => '2026-08-20',
        ]);

        $response = $this->actingAs($user)->get('/purchasing/actual-inventory');
        $response->assertStatus(200);

        $vendorList = $response->viewData('vendorOverviewList');
        $this->assertNotNull($vendorList);

        $vCrit = $vendorList->firstWhere('supplier_name', 'PT. VENDOR CRITICAL');
        $this->assertNotNull($vCrit);
        $this->assertEquals('Critical', $vCrit['status']);
        $this->assertEquals(1, $vCrit['critical_items_count']);
        $this->assertEquals(90, $vCrit['total_additional_req']);
        $this->assertNotEmpty($vCrit['status_reason']);

        $vHlth = $vendorList->firstWhere('supplier_name', 'PT. VENDOR HEALTHY');
        $this->assertNotNull($vHlth);
        $this->assertEquals('Healthy', $vHlth['status']);
        $this->assertEquals(1, $vHlth['healthy_items_count']);
        $this->assertEquals(0, $vHlth['total_additional_req']);
        $this->assertEquals(100.0, $vHlth['health_score_pct']);

        // Validasi struktur vendorChartData untuk Chart.js Area Chart
        $chartData = $response->viewData('vendorChartData');
        $this->assertIsArray($chartData);
        $this->assertArrayHasKey('labels', $chartData);
        $this->assertArrayHasKey('in_demand', $chartData);
        $this->assertArrayHasKey('actual_inventory', $chartData);
        $this->assertArrayHasKey('outstanding_po', $chartData);
        $this->assertArrayHasKey('statuses', $chartData);
        $this->assertCount(2, $chartData['labels']);
    }

    /**
     * Test: Filter item code dan filter status tidak menghasilkan error.
     */
    public function test_inventory_filters_work_without_error(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Test filter status SURPLUS
        $responseSurplus = $this->actingAs($user)->get('/purchasing/actual-inventory?status_filter=SURPLUS');
        $responseSurplus->assertStatus(200);

        // Test filter status CRITICAL_DEFICIT
        $responseDeficit = $this->actingAs($user)->get('/purchasing/actual-inventory?status_filter=CRITICAL_DEFICIT');
        $responseDeficit->assertStatus(200);

        // Test filter item non-existent
        $responseItem = $this->actingAs($user)->get('/purchasing/actual-inventory?item_code=NON_EXISTENT_SKU_123');
        $responseItem->assertStatus(200);
    }

    /**
     * Test: Reconcile Inventory Helper berjalan lancar dan menghasilkan metrics yang konsisten.
     */
    public function test_reconcile_inventory_helper(): void
    {
        $reconciliation = PurchasingReconciliationHelper::reconcileInventory();

        $this->assertIsObject($reconciliation);
        $this->assertObjectHasProperty('total_actual_inventory', $reconciliation);
        $this->assertObjectHasProperty('total_forecast_demand', $reconciliation);
        $this->assertObjectHasProperty('total_outstanding_po', $reconciliation);
        $this->assertObjectHasProperty('total_potential_supply', $reconciliation);
        $this->assertObjectHasProperty('net_supply_gap', $reconciliation);
        $this->assertObjectHasProperty('coverage_rate_pct', $reconciliation);
        $this->assertGreaterThanOrEqual(0, $reconciliation->coverage_rate_pct);
    }

    /**
     * Test: Store log inventory baru dan memverifikasi sinkronisasi dengan PurchasingOutstanding.
     */
    public function test_store_inventory_log_syncs_with_purchasing_outstanding(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Buat dummy item di PurchasingOutstanding
        $os = PurchasingOutstanding::create([
            'part_number'  => 'TEST-PART-999',
            'description'  => 'Test Material Description',
            'm0_inventory' => 100,
            'm1_forecast'  => 500,
            'price'        => 10.5,
            'currency'     => 'USD',
        ]);

        $response = $this->actingAs($user)->post('/purchasing/actual-inventory/store', [
            'tanggal_inventory' => '2026-08-18',
            'part_number'       => 'TEST-PART-999',
            'description'       => 'Test Material Description',
            'current_stock'     => 750,
        ]);

        $response->assertSessionHas('success');

        // Verifikasi log tersimpan di database
        $this->assertDatabaseHas('inventories', [
            'part_number'   => 'TEST-PART-999',
            'current_stock' => 750,
        ]);

        // Verifikasi sinkronisasi ke m0_inventory di PurchasingOutstanding
        $os->refresh();
        $this->assertEquals(750, $os->m0_inventory, 'm0_inventory in PurchasingOutstanding should be updated to 750');
    }

    /**
     * Test: Download template CSV standar Actual Inventory.
     */
    public function test_download_actual_inventory_template_returns_csv(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $response = $this->actingAs($user)->get('/purchasing/actual-inventory/template');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /**
     * Test: Import Excel dengan kunci logis Plant + Material Code (preservasi leading zero & anti-double counting).
     */
    public function test_excel_import_with_plant_material_code_unique_key(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        // Data sampel: kode dengan leading zero '001234' pada 2 plant berbeda ('KIP1' dan 'KIP4')
        $importRows = [
            [
                'supplier_code'    => 'C102',
                'supplier_name'    => 'PT. TRI JAYA TEKNIK KARAWANG',
                'plant'            => 'KIP1',
                'material_code'    => '001234',
                'description'      => 'BRACKET TEST 1',
                'actual_inventory' => 120,
                'snapshot_date'    => '2026-08-18'
            ],
            [
                'supplier_code'    => 'C102',
                'supplier_name'    => 'PT. TRI JAYA TEKNIK KARAWANG',
                'plant'            => 'KIP4',
                'material_code'    => '001234',
                'description'      => 'BRACKET TEST 1',
                'actual_inventory' => 80,
                'snapshot_date'    => '2026-08-18'
            ],
            [
                'supplier_code'    => 'C146',
                'supplier_name'    => 'PT. SUMBER AGUNG SEJAHTERA',
                'plant'            => 'KIP1',
                'material_code'    => '817750',
                'description'      => 'PLASTIC BAG',
                'actual_inventory' => 500,
                'snapshot_date'    => '2026-08-18'
            ]
        ];

        $response = $this->actingAs($user)->postJson('/purchasing/actual-inventory/import', [
            'rows' => $importRows
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verifikasi kode dengan leading zero '001234' tersimpan presisi di kedua plant
        $this->assertDatabaseHas('inventories', [
            'part_number'   => '001234',
            'factory_code'  => 'KIP1',
            'current_stock' => 120
        ]);

        $this->assertDatabaseHas('inventories', [
            'part_number'   => '001234',
            'factory_code'  => 'KIP4',
            'current_stock' => 80
        ]);

        $this->assertDatabaseHas('inventories', [
            'part_number'   => '817750',
            'factory_code'  => 'KIP1',
            'current_stock' => 500
        ]);
    }

    /**
     * Test: Potensi pasokan (Potential Supply = Inventory + Outstanding) dan 4 status keputusan purchasing.
     */
    public function test_potential_supply_and_status_classifications(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Item 1: Surplus (Inv 500 >= Forecast 300)
        PurchasingOutstanding::create([
            'part_number'   => 'MAT-SURPLUS',
            'description'   => 'Surplus Material Item',
            'factory_code'  => 'KIP1',
            'm0_inventory'  => 500,
            'order_qty'     => 300,
        ]);
        Inventory::create([
            'part_number'   => 'MAT-SURPLUS',
            'factory_code'  => 'KIP1',
            'current_stock' => 500,
            'tanggal_inventory' => '2026-08-18'
        ]);

        // Item 2: Covered by PO (Inv 100 < Forecast 400, but Inv 100 + PO 350 = 450 >= 400)
        PurchasingOutstanding::create([
            'part_number'   => 'MAT-COVERED',
            'description'   => 'Covered Material Item',
            'factory_code'  => 'KIP1',
            'm0_inventory'  => 100,
            'order_qty'     => 400,
        ]);
        Inventory::create([
            'part_number'   => 'MAT-COVERED',
            'factory_code'  => 'KIP1',
            'current_stock' => 100,
            'tanggal_inventory' => '2026-08-18'
        ]);
        \App\Models\MasterPo::create([
            'po'        => 'PO-COVERED-01',
            'item_code' => 'MAT-COVERED',
            'qty'       => 350,
            'tanggal'   => '2026-08-01'
        ]);

        // Item 3: Defisit / Perlu PO (Inv 50 + PO 100 = 150 < Forecast 500)
        PurchasingOutstanding::create([
            'part_number'   => 'MAT-DEFICIT',
            'description'   => 'Deficit Material Item',
            'factory_code'  => 'KIP1',
            'm0_inventory'  => 50,
            'order_qty'     => 500,
        ]);
        Inventory::create([
            'part_number'   => 'MAT-DEFICIT',
            'factory_code'  => 'KIP1',
            'current_stock' => 50,
            'tanggal_inventory' => '2026-08-18'
        ]);
        \App\Models\MasterPo::create([
            'po'        => 'PO-DEFICIT-01',
            'item_code' => 'MAT-DEFICIT',
            'qty'       => 100,
            'tanggal'   => '2026-08-01'
        ]);

        $response = $this->actingAs($user)->get('/purchasing/actual-inventory');
        $response->assertStatus(200);

        $matrix = $response->viewData('filteredMatrix');
        $this->assertNotNull($matrix);

        $itemSurplus = $matrix->firstWhere('part_number', 'MAT-SURPLUS');
        $this->assertNotNull($itemSurplus);
        $this->assertEquals('SURPLUS', $itemSurplus->status);

        $itemCovered = $matrix->firstWhere('part_number', 'MAT-COVERED');
        $this->assertNotNull($itemCovered);
        $this->assertEquals('COVERED_BY_PO', $itemCovered->status);
        $this->assertEquals(450, $itemCovered->potential_supply);

        $itemDeficit = $matrix->firstWhere('part_number', 'MAT-DEFICIT');
        $this->assertNotNull($itemDeficit);
        $this->assertEquals('CRITICAL_DEFICIT', $itemDeficit->status);
        $this->assertEquals(150, $itemDeficit->potential_supply);
        $this->assertEquals(-350, $itemDeficit->net_supply_gap);
    }

    /**
     * Test: Fitur Delete Selection (Hapus Terpilih) menghapus item terpilih dan mereset m0_inventory.
     */
    public function test_delete_selection_removes_selected_records_and_syncs_outstanding(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $inv1 = Inventory::create([
            'part_number'       => 'DEL-SEL-01',
            'factory_code'      => 'KIP1',
            'current_stock'     => 150,
            'tanggal_inventory' => '2026-08-18'
        ]);

        $inv2 = Inventory::create([
            'part_number'       => 'DEL-SEL-02',
            'factory_code'      => 'KIP1',
            'current_stock'     => 250,
            'tanggal_inventory' => '2026-08-18'
        ]);

        PurchasingOutstanding::create([
            'part_number'   => 'DEL-SEL-01',
            'description'   => 'Item 1',
            'm0_inventory'  => 150
        ]);

        PurchasingOutstanding::create([
            'part_number'   => 'DEL-SEL-02',
            'description'   => 'Item 2',
            'm0_inventory'  => 250
        ]);

        // Hapus hanya inv1
        $response = $this->actingAs($user)->postJson('/purchasing/actual-inventory/destroy-bulk', [
            'ids' => [$inv1->id],
            'part_numbers' => ['DEL-SEL-01']
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseMissing('inventories', ['id' => $inv1->id]);
        $this->assertDatabaseHas('inventories', ['id' => $inv2->id]);

        $this->assertDatabaseHas('purchasing_outstandings', [
            'part_number'  => 'DEL-SEL-01',
            'm0_inventory' => 0
        ]);
        $this->assertDatabaseHas('purchasing_outstandings', [
            'part_number'  => 'DEL-SEL-02',
            'm0_inventory' => 250
        ]);
    }

    /**
     * Test: Fitur Delete Massal (Hapus Semua) menghapus seluruh records dan mereset m0_inventory ke 0.
     */
    public function test_delete_massal_removes_all_records_and_resets_outstanding(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        Inventory::create([
            'part_number'       => 'DEL-ALL-01',
            'factory_code'      => 'KIP1',
            'current_stock'     => 300,
            'tanggal_inventory' => '2026-08-18'
        ]);

        Inventory::create([
            'part_number'       => 'DEL-ALL-02',
            'factory_code'      => 'KIP1',
            'current_stock'     => 400,
            'tanggal_inventory' => '2026-08-18'
        ]);

        PurchasingOutstanding::create([
            'part_number'   => 'DEL-ALL-01',
            'description'   => 'All Item 1',
            'm0_inventory'  => 300
        ]);

        $response = $this->actingAs($user)->post('/purchasing/actual-inventory/destroy-all');
        $response->assertRedirect('/purchasing/actual-inventory');

        $this->assertEquals(0, Inventory::count());
        $this->assertDatabaseHas('purchasing_outstandings', [
            'part_number'  => 'DEL-ALL-01',
            'm0_inventory' => 0
        ]);
    }

    /**
     * Test: End-to-end tracing Material 1312004 -> Excel (44 PCS, flexible header) -> DB -> Controller -> View (44).
     */
    public function test_end_to_end_material_tracing_and_flexible_header_mapping(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Master forecast: Forecast Demand = 50, Master PO Outstanding = 20
        PurchasingOutstanding::create([
            'part_number'   => '1312004',
            'description'   => 'GP BRACKET COMPOU',
            'factory_code'  => 'KIP1',
            'order_qty'     => 50,
            'price'         => 2.5
        ]);

        // Simulasi import dengan header bervariasi dan numeric '44 PCS' & '1.100'
        $importPayload = [
            [
                'vendor_code'      => 'C102',
                'vendor_name'      => 'PT. TRI JAYA TEKNIK KARAWANG',
                'lokasi'           => 'KIP1',
                'kode_material'    => '1312004',
                'deskripsi'        => 'GP BRACKET COMPOU',
                'aktual_inventory' => '44 PCS',
                'tanggal'          => '18/08/2026'
            ],
            [
                'vendor_code'      => 'C084',
                'vendor_name'      => 'PT. CRESTEC',
                'plant'            => 'KIP1',
                'material_code'    => '817750',
                'description'      => 'ZIPPER BAG',
                'actual_stock'     => '1.100',
                'snapshot_date'    => '2026-08-18'
            ]
        ];

        $responseImport = $this->actingAs($user)->postJson('/purchasing/actual-inventory/import', [
            'rows' => $importPayload
        ]);

        $responseImport->assertStatus(200)->assertJson(['success' => true]);

        // Verifikasi DB tersimpan nilai numeric 44 dan 1100
        $this->assertDatabaseHas('inventories', [
            'part_number'   => '1312004',
            'current_stock' => 44,
            'factory_code'  => 'KIP1'
        ]);

        $this->assertDatabaseHas('inventories', [
            'part_number'   => '817750',
            'current_stock' => 1100,
            'factory_code'  => 'KIP1'
        ]);

        // Verifikasi Controller & View
        $responseView = $this->actingAs($user)->get('/purchasing/actual-inventory');
        $responseView->assertStatus(200);

        $matrix = $responseView->viewData('filteredMatrix');
        $item1312004 = $matrix->firstWhere('part_number', '1312004');

        $this->assertNotNull($item1312004, 'Material 1312004 must be present in filteredMatrix');
        $this->assertEquals(44, $item1312004->actual_stock, 'Actual stock in matrix must be 44');
        $this->assertEquals(50, $item1312004->forecast_demand, 'Forecast demand must be 50');
        $this->assertEquals(44, $item1312004->potential_supply, 'Potential supply must be actual + outstanding');
        $this->assertEquals(-6, $item1312004->net_supply_gap, 'Supply gap must be 44 - 50 = -6');
        $this->assertEquals('CRITICAL_DEFICIT', $item1312004->status);

        // Verifikasi Data Quality Indicator
        $responseView->assertViewHas('latestSnapshotDate');
        $responseView->assertViewHas('matchedMaterialsCount');
        $responseView->assertViewHas('matchPercentage');
    }
}

