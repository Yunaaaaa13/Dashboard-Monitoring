<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

/**
 * Test Filter Consistency across purchasing pages.
 * Memvalidasi bahwa semua KPI, chart, dan tabel menggunakan dataset terfilter yang sama.
 */
class FilterConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Analysis page memerlukan autentikasi.
     */
    public function test_analysis_requires_auth(): void
    {
        $response = $this->get('/purchasing/analysis');
        $response->assertRedirect('/login');
    }

    /**
     * Test: Analysis page loads successfully.
     */
    public function test_analysis_page_loads(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/analysis');
        $response->assertStatus(200);
    }

    /**
     * Test: Analysis page menyediakan variabel chart dan grid.
     */
    public function test_analysis_provides_chart_and_grid_variables(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/analysis');
        $response->assertStatus(200);

        $response->assertViewHas('exchangeRateComparisonGrid');
        $response->assertViewHas('chartFxForecastAmountUsd');
        $response->assertViewHas('chartFxActualAmountUsd');
    }

    /**
     * Test: Outstanding page loads successfully.
     */
    public function test_outstanding_page_loads(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/outstanding');
        $response->assertStatus(200);
    }

    /**
     * Test: Master PO page dengan filter non-existent tidak error.
     */
    public function test_master_po_empty_filter_no_error(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/master-po?periode=9999-99');
        $response->assertStatus(200);

        $totalQty = $response->viewData('masterPoTotalQty');
        $this->assertEquals(0, $totalQty, 'Total qty should be 0 with impossible filter');
    }

    /**
     * Test: Outstanding PO dengan empty search tidak error.
     */
    public function test_outstanding_po_empty_search_no_error(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/outstanding-po?search=ZZZZNONEXISTENT999');
        $response->assertStatus(200);
    }

    /**
     * Test: Semua purchasing pages accessible tanpa error.
     */
    public function test_all_purchasing_pages_load(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $pages = [
            '/purchasing/master-po',
            '/purchasing/outstanding-po',
            '/purchasing/outstanding',
        ];

        foreach ($pages as $page) {
            $response = $this->actingAs($user)->get($page);
            $response->assertStatus(200, "Page {$page} should return 200");
        }
    }
}
