<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchasingVendorFilterTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'supervisor']);
    }

    public function test_dashboard_overview_loads_with_vendor_filter()
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.overview', ['supplier' => 'PT. TEST VENDOR']));
        $response->assertStatus(200);
        $response->assertViewHas('suppliers');
        $response->assertViewHas('selectedSupplier', 'PT. TEST VENDOR');
    }

    public function test_purchasing_outstanding_loads_with_vendor_filter()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.outstanding', ['supplier' => 'PT. TEST VENDOR']));
        $response->assertStatus(200);
        $response->assertViewHas('suppliers');
        $response->assertViewHas('supplierFilter', 'PT. TEST VENDOR');
    }

    public function test_master_po_loads_with_vendor_filter()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.master-po', ['supplier' => 'PT. TEST VENDOR']));
        $response->assertStatus(200);
        $response->assertViewHas('suppliers');
        $response->assertViewHas('selectedSupplier', 'PT. TEST VENDOR');
    }

    public function test_purchasing_input_loads_with_vendor_filter()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.input', ['supplier' => 'PT. TEST VENDOR']));
        $response->assertStatus(200);
        $response->assertViewHas('suppliers');
        $response->assertViewHas('selectedSupplier', 'PT. TEST VENDOR');
    }

    public function test_purchasing_history_loads_with_vendor_filter()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.history', ['supplier' => 'PT. TEST VENDOR']));
        $response->assertStatus(200);
        $response->assertViewHas('suppliers');
        $response->assertViewHas('selectedSupplier', 'PT. TEST VENDOR');
    }

    public function test_actual_inventory_loads_with_vendor_filter()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.actual-inventory', ['supplier' => 'PT. TEST VENDOR']));
        $response->assertStatus(200);
        $response->assertViewHas('availableSuppliers');
        $response->assertViewHas('supplierFilter', 'PT. TEST VENDOR');
    }

    public function test_outstanding_po_loads_with_vendor_filter()
    {
        $response = $this->actingAs($this->user)->get(route('purchasing.outstanding-po', ['vendor' => 'PT. TEST VENDOR']));
        $response->assertStatus(200);
        $response->assertViewHas('availableVendors');
        $response->assertViewHas('selectedVendor', 'PT. TEST VENDOR');
    }
}
