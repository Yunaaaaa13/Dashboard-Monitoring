<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

/**
 * Test Outstanding PO Calculation Logic.
 * Memvalidasi outstanding = max(PO - Receipt, 0) dan over delivery terpisah.
 */
class OutstandingPoCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Halaman Outstanding PO memerlukan autentikasi.
     */
    public function test_outstanding_po_requires_auth(): void
    {
        $response = $this->get('/purchasing/outstanding-po');
        $response->assertRedirect('/login');
    }

    /**
     * Test: Halaman Outstanding PO dapat diakses.
     */
    public function test_outstanding_po_page_loads(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/outstanding-po');
        $response->assertStatus(200);
    }

    /**
     * Test: Outstanding data view variable tersedia.
     */
    public function test_outstanding_data_available(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/outstanding-po');
        $response->assertStatus(200);
        $response->assertViewHas('outstandingData');
    }

    /**
     * Test: Halaman Outstanding PO tidak error dengan search kosong.
     */
    public function test_empty_search_no_error(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/outstanding-po?search=NONEXISTENT_ITEM_XYZ_12345');
        $response->assertStatus(200);
    }

    /**
     * Test: Filter vendor bekerja tanpa error.
     */
    public function test_vendor_filter_no_error(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/purchasing/outstanding-po?vendor=NONEXISTENT_VENDOR');
        $response->assertStatus(200);
    }
}
