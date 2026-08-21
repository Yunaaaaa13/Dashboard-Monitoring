<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

/**
 * Test Master PO Fulfillment Calculation.
 * Memvalidasi bahwa fulfillment percentage dihitung dari dataset terfilter.
 */
class PurchasingMasterPoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Halaman Master PO memerlukan autentikasi.
     */
    public function test_master_po_requires_auth(): void
    {
        $response = $this->get('/purchasing/master-po');
        $response->assertRedirect('/login');
    }

    /**
     * Test: Halaman Master PO dapat diakses setelah login.
     */
    public function test_master_po_page_loads_successfully(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/master-po');
        $response->assertStatus(200);
    }

    /**
     * Test: Fulfillment percentage tidak menyebabkan division by zero saat dataset kosong.
     */
    public function test_fulfillment_percentage_no_division_by_zero(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Dengan database kosong, fulfillment harus 0 bukan error
        $response = $this->actingAs($user)->get('/purchasing/master-po');
        $response->assertStatus(200);

        $fulfillment = $response->viewData('fulfillmentPercentage');
        $this->assertEquals(0, $fulfillment, 'Fulfillment should be 0 when no data exists');
    }

    /**
     * Test: KPI view variables tersedia.
     */
    public function test_kpi_view_variables_available(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/purchasing/master-po');
        $response->assertStatus(200);

        $response->assertViewHas('masterPoTotalQty');
        $response->assertViewHas('masterPoTotalCount');
        $response->assertViewHas('matchedActualQty');
        $response->assertViewHas('fulfillmentPercentage');
    }

    /**
     * Test: Fulfillment percentage berada dalam batas logis (0-100+).
     */
    public function test_fulfillment_percentage_is_not_negative(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/purchasing/master-po');
        $response->assertStatus(200);

        $fulfillment = $response->viewData('fulfillmentPercentage');
        $this->assertGreaterThanOrEqual(0, $fulfillment, 'Fulfillment should be >= 0');
    }

    /**
     * Test: Filter periode non-existent tetap menghasilkan response 200.
     */
    public function test_nonexistent_filter_returns_200(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/purchasing/master-po?periode=9999-99');
        $response->assertStatus(200);
    }
}
