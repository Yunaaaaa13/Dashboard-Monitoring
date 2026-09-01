<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PurchasingCategory;
use App\Models\PurchasingOutstanding;
use App\Models\MasterPo;
use App\Models\PurchasingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MasterPoAndIncomingDatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PurchasingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'supervisor',
            'email' => 'supervisor@kawai.co.id',
        ]);

        $this->category = PurchasingCategory::create([
            'category_code' => 'PUR-04',
            'category_name' => 'Komponen Packing',
            'pic_buyer'     => 'Staff Purchasing',
            'status'        => 'Active',
        ]);
    }

    public function test_master_po_store_saves_successfully_with_category_and_factory()
    {
        $response = $this->actingAs($this->user)->post(route('purchasing.master-po.store'), [
            'tanggal'      => '2026-02-02',
            'supplier'     => 'PT. TRI JAYA TEKNIK KARAWANG',
            'po'           => 'KI-TJT-0001/2026',
            'item_code'    => '1312006',
            'factory_code' => 'Plant 3',
            'category_id'  => $this->category->id,
            'name'         => 'Bracket Compou',
            'qty'          => 230,
            'price'        => 8470,
            'currency'     => 'IDR',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('master_pos', [
            'po'           => 'KI-TJT-0001/2026',
            'item_code'    => '1312006',
            'factory_code' => 'Plant 3',
            'category_id'  => $this->category->id,
            'qty'          => 230,
            'price'        => 8470,
            'currency'     => 'IDR',
        ]);
    }

    public function test_master_po_model_safely_filters_non_existent_attributes_without_crashing()
    {
        $po = MasterPo::create([
            'tanggal'        => '2026-01-13',
            'supplier'       => 'PT. SUMBER GRAHA SEJAHTERA',
            'po'             => 'KI-SGS-0001/2026',
            'item_code'      => 'K050602',
            'factory_code'   => 'Plant 3',
            'category_id'    => $this->category->id,
            'name'           => 'Box (1) Dso (37.5 X 130 X 950) 506',
            'qty'            => 2,
            'price'          => 119721,
            'currency'       => 'IDR',
            'non_existent_col' => 'safe_value', // should be safely stripped
        ]);

        $this->assertNotNull($po->id);
        $this->assertEquals('K050602', $po->item_code);
        $this->assertEquals($this->category->id, $po->category_id);
    }

    public function test_incoming_log_creation_and_po_correlation()
    {
        // 1. Create Master PO
        $masterPo = MasterPo::create([
            'tanggal'        => '2026-02-02',
            'supplier'       => 'PT. TRI JAYA TEKNIK KARAWANG',
            'po'             => 'KI-TJT-0001/2026',
            'item_code'      => '1312006',
            'factory_code'   => 'Plant 3',
            'category_id'    => $this->category->id,
            'name'           => 'Bracket Compou',
            'qty'            => 230,
            'price'          => 8470,
            'currency'       => 'IDR',
        ]);

        // 2. Post Incoming Log
        $response = $this->actingAs($this->user)->post(route('purchasing.store'), [
            'receipt_date'           => '2026-02-05',
            'period_month'           => '2026-02',
            'item_code'              => '1312006',
            'po_reference'           => 'KI-TJT-0001/2026',
            'factory_code'           => 'Plant 3',
            'purchasing_category_id' => $this->category->id,
            'actual_received'        => 230,
            'price'                  => 8470,
            'currency'               => 'IDR',
            'status_note'            => 'Barang tiba lengkap',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchasing_logs', [
            'item_code'              => '1312006',
            'po_reference'           => 'KI-TJT-0001/2026',
            'factory_code'           => 'Plant 3',
            'purchasing_category_id' => $this->category->id,
            'actual_received'        => 230,
            'price'                  => 8470,
            'currency'               => 'IDR',
        ]);
    }

    public function test_master_po_update_preserves_factory_code_and_price()
    {
        $po = MasterPo::create([
            'tanggal'      => '2026-02-02',
            'supplier'     => 'PT. TRI JAYA TEKNIK KARAWANG',
            'po'           => 'KI-TJT-0001/2026',
            'item_code'    => '1312006',
            'factory_code' => 'Plant 3',
            'category_id'  => $this->category->id,
            'name'         => 'Bracket Compou',
            'qty'          => 230,
            'price'        => 8470,
            'currency'     => 'IDR',
        ]);

        $response = $this->actingAs($this->user)->put(route('purchasing.master-po.update', $po->id), [
            'tanggal'      => '2026-02-03',
            'supplier'     => 'PT. TRI JAYA TEKNIK KARAWANG',
            'po'           => 'KI-TJT-0001/2026',
            'item_code'    => '1312006',
            'factory_code' => 'KIP 1',
            'category_id'  => $this->category->id,
            'name'         => 'Bracket Compound Updated',
            'qty'          => 300,
            'price'        => 9000,
            'currency'     => 'IDR',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('master_pos', [
            'id'           => $po->id,
            'factory_code' => 'KIP 1',
            'qty'          => 300,
            'price'        => 9000,
        ]);
    }

    public function test_master_po_store_auto_fills_details_from_step1_when_omitted()
    {
        // 1. Create Step 1 record
        PurchasingOutstanding::create([
            'part_number'   => 'SKU-AUTO-01',
            'description'   => 'Auto Desc Material',
            'supplier_name' => 'PT Auto Vendor',
            'price'         => 12500,
            'currency'      => 'IDR',
            'category_id'   => $this->category->id,
            'factory_code'  => 'Plant 2',
            'delivery_category_code' => 'LOC',
        ]);

        // 2. Submit Master PO store with only minimal fields
        $response = $this->actingAs($this->user)->post(route('purchasing.master-po.store'), [
            'tanggal'   => '2026-02-10',
            'po'        => 'PO-AUTO-001',
            'item_code' => 'SKU-AUTO-01',
            'qty'       => 500,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('master_pos', [
            'po'           => 'PO-AUTO-001',
            'item_code'    => 'SKU-AUTO-01',
            'name'         => 'Auto Desc Material',
            'supplier'     => 'PT Auto Vendor',
            'price'        => 12500,
            'currency'     => 'IDR',
            'category_id'  => $this->category->id,
            'factory_code' => 'Plant 2',
            'qty'          => 500,
        ]);
    }

    public function test_master_po_store_ajax_returns_json_and_auto_registers_step1()
    {
        $response = $this->actingAs($this->user)->postJson(route('purchasing.master-po.store'), [
            'tanggal'      => '2026-03-01',
            'po'           => 'PO-AJAX-001',
            'item_code'    => 'NEW-SKU-99',
            'name'         => 'Brand New Component',
            'supplier'     => 'PT Supplier Baru',
            'qty'          => 150,
            'price'        => 2.50,
            'currency'     => 'USD',
            'factory_code' => 'Plant 4',
            'category_id'  => $this->category->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('master_pos', [
            'po'        => 'PO-AJAX-001',
            'item_code' => 'NEW-SKU-99',
            'price'     => 2.50,
            'currency'  => 'USD',
        ]);

        $this->assertDatabaseHas('purchasing_outstandings', [
            'part_number' => 'NEW-SKU-99',
            'description' => 'Brand New Component',
        ]);
    }

    public function test_master_po_index_supplies_registered_items_for_autocomplete()
    {
        PurchasingOutstanding::create([
            'part_number' => 'SKU-LIST-01',
            'description' => 'List Item Description',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.master-po'));
        $response->assertStatus(200);
        $response->assertViewHas('registeredItems');
        $response->assertViewHas('registeredItemsMapJson');

        $registered = $response->viewData('registeredItems');
        $this->assertTrue(collect($registered)->contains('item_code', 'SKU-LIST-01'));
    }

    public function test_integrated_importer_execute_import_saves_master_po_and_incoming_safely()
    {
        $importer = app(\App\Services\Import\IntegratedPoIncomingImporter::class);

        $analysisData = [
            'batch_id' => 'IMP-TEST-001',
            'file_name' => 'test_master_po.xlsx',
            'master_po_rows' => [
                [
                    'tanggal'      => '2026-02-02',
                    'supplier'     => 'PT. TRI JAYA TEKNIK KARAWANG',
                    'po'           => 'KI-TJT-0001/2026',
                    'item_code'    => '1312006',
                    'factory_code' => 'Plant 3',
                    'category_id'  => $this->category->id,
                    'name'         => 'Bracket Compou',
                    'qty'          => 230,
                    'price'        => 8470,
                    'currency'     => 'IDR',
                ],
            ],
            'incoming_rows' => [
                [
                    'receipt_date'    => '2026-02-05',
                    'item_code'       => '1312006',
                    'factory_code'    => 'Plant 3',
                    'category_id'     => $this->category->id,
                    'item_name'       => 'Bracket Compou',
                    'supplier_name'   => 'PT. TRI JAYA TEKNIK KARAWANG',
                    'po_reference'    => 'KI-TJT-0001/2026',
                    'period_month'    => '2026-02',
                    'target_order'    => 230,
                    'actual_received' => 230,
                    'pending_order'   => 0,
                    'price'           => 8470,
                    'currency'        => 'IDR',
                    'amount'          => 1948100,
                    'status_note'     => 'MATCH_EXACT',
                ],
            ],
            'reconciliation' => [
                'total_excel_rows' => 2,
            ],
        ];

        $result = $importer->executeImport($analysisData, $this->user->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['inserted_master_po']);
        $this->assertEquals(1, $result['inserted_incoming']);

        $this->assertDatabaseHas('master_pos', [
            'po'           => 'KI-TJT-0001/2026',
            'item_code'    => '1312006',
            'factory_code' => 'Plant 3',
            'category_id'  => $this->category->id,
            'qty'          => 230,
            'price'        => 8470,
            'currency'     => 'IDR',
        ]);

        $this->assertDatabaseHas('purchasing_logs', [
            'po_reference'           => 'KI-TJT-0001/2026',
            'item_code'              => '1312006',
            'factory_code'           => 'Plant 3',
            'purchasing_category_id' => $this->category->id,
            'actual_received'        => 230,
            'price'                  => 8470,
            'currency'               => 'IDR',
        ]);
    }

    public function test_database_schema_manager_ensures_all_tables_integrity()
    {
        \App\Services\DataValidation\DatabaseSchemaManager::ensureAllTablesIntegrity(true);

        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('master_pos'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('master_pos', 'category_id'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('master_pos', 'factory_code'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('master_pos', 'price'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('master_pos', 'currency'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('master_pos', 'delivery_category_code'));

        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('purchasing_logs'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('purchasing_logs', 'purchasing_category_id'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('purchasing_logs', 'price'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('purchasing_logs', 'currency'));
    }
}
