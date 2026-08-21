<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ActualProduction;
use App\Models\PurchasingOutstanding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ActualProductionImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed default exchange rate or user
    }

    /**
     * Test: Halaman Actual Production dapat diakses dan variabel KPI tersedia.
     */
    public function test_actual_production_page_loads_with_kpis(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        ActualProduction::create([
            'tanggal_produksi' => '2026-08-01',
            'item_code'        => '1312006',
            'factory_code'     => 'KIP 1',
            'qty'              => 216,
        ]);
        ActualProduction::create([
            'tanggal_produksi' => '2026-08-01',
            'item_code'        => '1311023',
            'factory_code'     => 'KIP 4',
            'qty'              => 0,
        ]);

        $response = $this->actingAs($user)->get('/purchasing/actual-production');
        $response->assertStatus(200);

        $response->assertViewHas('totalLogsCount', 2);
        $response->assertViewHas('totalUniqueItemsCount', 2);
        $response->assertViewHas('totalProductionQty', 216);
        $response->assertViewHas('totalZeroProductionCount', 1);
    }

    /**
     * Test: Import JSON payload dari SheetJS dengan 170 baris termasuk 78 baris zero production.
     */
    public function test_import_client_payload_with_zero_production_and_duplicate_materials(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $rows = [];
        // Buat 92 baris dengan Qty > 0
        for ($i = 1; $i <= 92; $i++) {
            $rows[] = [
                'excel_row_number' => $i + 1,
                'plant'            => ($i % 2 === 0) ? 'KIP 1' : 'KIP 4',
                'supplier_code'    => 'V001',
                'supplier_name'    => 'PT SUPPLIER UTAMA',
                'material_code'    => 'MAT-' . sprintf('%04d', $i),
                'description'      => 'Material Desc ' . $i,
                'production_qty'   => $i * 10,
                'tanggal_produksi' => '2026-08-01',
            ];
        }

        // Buat 78 baris dengan Qty = 0 (Zero Production)
        for ($j = 93; $j <= 170; $j++) {
            // Sebagian menggunakan material code yang sama untuk menguji duplikasi transaksi
            $matCode = ($j <= 120) ? 'MAT-' . sprintf('%04d', $j - 50) : 'MAT-' . sprintf('%04d', $j);
            $rows[] = [
                'excel_row_number' => $j + 1,
                'plant'            => 'KIP 1',
                'supplier_code'    => 'V002',
                'supplier_name'    => 'PT METAL PERSADA',
                'material_code'    => $matCode,
                'description'      => 'Material Zero ' . $j,
                'production_qty'   => 0, // ZERO PRODUCTION
                'tanggal_produksi' => '2026-08-01',
            ];
        }

        $this->assertCount(170, $rows);

        $response = $this->actingAs($user)->postJson('/purchasing/actual-production/import', [
            'rows' => $rows
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'        => true,
            'total_inserted' => 170,
            'zero_count'     => 78,
        ]);

        // Verifikasi database memiliki tepat 170 log produksi
        $this->assertEquals(170, ActualProduction::count());
        $this->assertEquals(78, ActualProduction::where('qty', 0)->count());
        $this->assertEquals(92, ActualProduction::where('qty', '>', 0)->count());

        // Verifikasi batch_id tersimpan
        $first = ActualProduction::first();
        $this->assertNotNull($first->import_batch_id);
        $this->assertStringStartsWith('BATCH-', $first->import_batch_id);
    }

    /**
     * Test: Download template CSV mengembalikan format header yang sesuai.
     */
    public function test_download_template_returns_correct_csv(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $response = $this->actingAs($user)->get('/purchasing/actual-production/template/download');

        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    /**
     * Test: Hapus terpilih (Bulk Delete).
     */
    public function test_destroy_bulk_removes_selected_records(): void
    {
        $user = User::factory()->create(['role' => 'supervisor']);

        $p1 = ActualProduction::create(['tanggal_produksi' => '2026-08-01', 'item_code' => 'P001', 'qty' => 10]);
        $p2 = ActualProduction::create(['tanggal_produksi' => '2026-08-01', 'item_code' => 'P002', 'qty' => 20]);
        $p3 = ActualProduction::create(['tanggal_produksi' => '2026-08-01', 'item_code' => 'P003', 'qty' => 30]);

        $this->assertEquals(3, ActualProduction::count());

        $response = $this->actingAs($user)->postJson('/purchasing/actual-production/destroy-bulk', [
            'ids' => [$p1->id, $p2->id]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(1, ActualProduction::count());
        $this->assertTrue(ActualProduction::where('id', $p3->id)->exists());
    }

    /**
     * Test: Hapus massal (Destroy All).
     */
    public function test_destroy_all_empties_actual_production_table(): void
    {
        $user = User::factory()->create(['role' => 'supervisor']);

        ActualProduction::create(['tanggal_produksi' => '2026-08-01', 'item_code' => 'P001', 'qty' => 10]);
        ActualProduction::create(['tanggal_produksi' => '2026-08-01', 'item_code' => 'P002', 'qty' => 0]);

        $this->assertEquals(2, ActualProduction::count());

        $response = $this->actingAs($user)->post('/purchasing/actual-production/destroy-all');
        $response->assertStatus(302);

        $this->assertEquals(0, ActualProduction::count());
    }
}
