<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PurchasingOutstanding;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Slide3VendorGroupingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_slide3_provides_vendor_summaries_and_defaults_to_vendor_overview(): void
    {
        PurchasingOutstanding::create([
            'part_number' => 'PART-A1',
            'drawing'     => 'DRW-A1',
            'description' => 'Component A1',
            'supplier_name' => 'PT SERBAGUNA PRIMA',
            'plan_stock'  => 100,
            'm1_forecast' => 50,
            'm1_delivery' => 50,
            'm1_prod'     => 40,
            'price'       => 10.0,
            'currency'    => 'USD',
        ]);

        PurchasingOutstanding::create([
            'part_number' => 'PART-A2',
            'drawing'     => 'DRW-A2',
            'description' => 'Component A2',
            'supplier_name' => 'PT SERBAGUNA PRIMA',
            'plan_stock'  => 200,
            'm1_forecast' => 80,
            'm1_delivery' => 80,
            'm1_prod'     => 60,
            'price'       => 20.0,
            'currency'    => 'USD',
        ]);

        PurchasingOutstanding::create([
            'part_number' => 'PART-B1',
            'drawing'     => 'DRW-B1',
            'description' => 'Component B1',
            'supplier_name' => 'PT SURYARAYA NUSATAMA',
            'plan_stock'  => 300,
            'm1_forecast' => 120,
            'm1_delivery' => 120,
            'm1_prod'     => 100,
            'price'       => 15.0,
            'currency'    => 'USD',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'active_slide' => 'slide3',
            's3_vendor'    => 'ALL',
            'duration'     => 6,
        ]));

        $response->assertStatus(200);

        // Check view data
        $vendorSummaries = $response->viewData('slide3VendorSummaries');
        $this->assertNotNull($vendorSummaries);
        $this->assertCount(2, $vendorSummaries);

        // PT SERBAGUNA PRIMA should have 2 items
        $serbaguna = $vendorSummaries->firstWhere('supplier', 'PT SERBAGUNA PRIMA');
        $this->assertNotNull($serbaguna);
        $this->assertEquals(2, $serbaguna->item_count);
        $this->assertEquals(300, $serbaguna->m0['forecast_stock_qty']);

        // Check HTML renders Vendor Overview mode
        $response->assertSee('Tabel Ringkasan Komparasi Stock Forecast vs Stock Actual per Vendor');
        $response->assertSee('PT SERBAGUNA PRIMA');
        $response->assertSee('PT SURYARAYA NUSATAMA');
        $response->assertSee('Detail Item');
    }

    public function test_slide3_drilldown_to_specific_vendor_shows_detail_items(): void
    {
        PurchasingOutstanding::create([
            'part_number' => 'PART-A1',
            'drawing'     => 'DRW-A1',
            'description' => 'Component A1',
            'supplier_name' => 'PT SERBAGUNA PRIMA',
            'plan_stock'  => 100,
            'price'       => 10.0,
            'currency'    => 'USD',
        ]);

        PurchasingOutstanding::create([
            'part_number' => 'PART-B1',
            'drawing'     => 'DRW-B1',
            'description' => 'Component B1',
            'supplier_name' => 'PT SURYARAYA NUSATAMA',
            'plan_stock'  => 300,
            'price'       => 15.0,
            'currency'    => 'USD',
        ]);

        $response = $this->actingAs($this->user)->get(route('purchasing.analysis', [
            'active_slide' => 'slide3',
            's3_vendor'    => 'PT SERBAGUNA PRIMA',
        ]));

        $response->assertStatus(200);

        // Should display Vendor Focus Card & Detail Table
        $response->assertSee('Mode Detail Vendor');
        $response->assertSee('Kembali ke Ringkasan Semua Vendor');
        $response->assertSee('PART-A1');

        $displayGridS3 = $response->viewData('displayGridS3');
        $this->assertCount(1, $displayGridS3);
        $this->assertEquals('PART-A1', $displayGridS3->first()->item_code);
        $this->assertFalse($displayGridS3->contains('item_code', 'PART-B1'));
    }
}