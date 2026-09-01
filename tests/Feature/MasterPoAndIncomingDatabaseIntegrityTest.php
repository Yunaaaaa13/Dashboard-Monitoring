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
}
