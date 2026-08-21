<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role'     => 'admin',
            'username' => 'admin_test',
        ]);

        $this->staffUser = User::factory()->create([
            'role'     => 'staff',
            'username' => 'staff_test',
        ]);
    }

    /**
     * Test overview dashboard accessible to authenticated user.
     */
    public function test_overview_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.overview'));
        $response->assertStatus(200);
    }

    /**
     * Test Master PO dashboard renders successfully.
     */
    public function test_master_po_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.master-po'));
        $response->assertStatus(200);
    }

    /**
     * Test Outstanding dashboard renders successfully.
     */
    public function test_outstanding_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.outstanding'));
        $response->assertStatus(200);
    }

    /**
     * Test Purchasing Buyer Input dashboard renders successfully.
     */
    public function test_purchasing_input_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.input'));
        $response->assertStatus(200);
    }

    /**
     * Test Purchasing History dashboard renders successfully.
     */
    public function test_purchasing_history_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.history'));
        $response->assertStatus(200);
    }

    /**
     * Test Purchasing History Outstanding tab renders with seeded records without ArgumentCountError.
     */
    public function test_purchasing_history_outstanding_tab_renders_with_records(): void
    {
        \App\Models\PurchasingOutstanding::create([
            'po_number'              => 'PO-HIST-TEST-01',
            'part_number'            => 'PART-HIST-01',
            'description'            => 'History Test Part',
            'price'                  => 12.5,
            'currency'               => 'USD',
            'plan_stock'             => 100,
            'plan_outstand'          => 50,
            'delivery_category_code' => 'LOC',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('purchasing.history', ['tab' => 'outstanding']));
        $response->assertStatus(200)
            ->assertSee('PO-HIST-TEST-01')
            ->assertSee('PART-HIST-01');
    }

    /**
     * Test Actual Production dashboard renders successfully.
     */
    public function test_actual_production_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.actual-production'));
        $response->assertStatus(200);
    }

    /**
     * Test Actual Inventory dashboard renders successfully.
     */
    public function test_actual_inventory_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.actual-inventory'));
        $response->assertStatus(200);
    }

    /**
     * Test Master Forecast dashboard renders successfully.
     */
    public function test_master_forecast_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.master.forecast'));
        $response->assertStatus(200);
    }

    /**
     * Test Master Actual dashboard renders successfully.
     */
    public function test_master_actual_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.master.actual'));
        $response->assertStatus(200);
    }

    /**
     * Test Master Outstanding dashboard renders successfully.
     */
    public function test_master_outstanding_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.master.outstanding'));
        $response->assertStatus(200);
    }

    /**
     * Test Analysis dashboard renders successfully.
     */
    public function test_analysis_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.analysis'));
        $response->assertStatus(200);
    }

    /**
     * Test Comparison dashboard renders successfully.
     */
    public function test_comparison_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('purchasing.comparison'));
        $response->assertStatus(200);
    }

    /**
     * Test Exchange Rate dashboard renders successfully.
     */
    public function test_exchange_rate_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('exchange-rate.index'));
        $response->assertStatus(200);
    }

    /**
     * Test User Management dashboard renders successfully for Admin.
     */
    public function test_user_management_dashboard_renders_successfully_for_admin(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('users.index'));
        $response->assertStatus(200);
    }

    /**
     * Test User Monitoring dashboard renders successfully.
     */
    public function test_user_monitoring_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('users.monitoring'));
        $response->assertStatus(200);
    }
}
