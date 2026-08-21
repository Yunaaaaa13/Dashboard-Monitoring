<?php

namespace Tests\Feature;

use App\Models\ActualProduction;
use App\Models\Inventory;
use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HighScaleDataPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role'     => 'admin',
            'username' => 'admin_perf',
        ]);
    }

    /**
     * Test application performance and memory usage with a high-volume dataset.
     */
    public function test_high_volume_dataset_performance_and_memory_efficiency(): void
    {
        // 1. Seed 200 records in purchasing_outstandings
        for ($i = 1; $i <= 200; $i++) {
            PurchasingOutstanding::create([
                'part_number'    => 'PART-PERF-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'description'    => 'Performance Test Material #' . $i,
                'factory_code'   => ($i % 2 === 0) ? 'KIP 1' : 'KIP 3',
                'order_qty'      => 100 * $i,
                'drawing'        => 'DWG-' . $i,
                'price'          => 50.00 + $i,
                'currency'       => 'USD',
                'amount'         => (100 * $i) * (50.00 + $i),
                'status'         => 'Pending',
                'supplier_name'  => 'PT KAWAII SUPPLIER ' . ($i % 5),
            ]);
        }

        // 2. Seed 300 records in purchasing_logs
        for ($j = 1; $j <= 300; $j++) {
            $partIdx = ($j % 200) + 1;
            PurchasingLog::create([
                'po_reference'    => 'PO-PERF-' . str_pad($partIdx, 4, '0', STR_PAD_LEFT),
                'item_code'       => 'PART-PERF-' . str_pad($partIdx, 4, '0', STR_PAD_LEFT),
                'item_name'       => 'Performance Test Material #' . $partIdx,
                'receipt_date'    => '2026-06-15',
                'period_month'    => '2026-06',
                'target_order'    => 100,
                'actual_received' => 95,
                'price'           => 52.00,
                'currency'        => 'USD',
                'supplier_name'   => 'PT KAWAII SUPPLIER 1',
                'user_id'         => $this->adminUser->id,
            ]);
        }

        // 3. Seed 200 records in actual_productions
        for ($k = 1; $k <= 200; $k++) {
            $partIdx = ($k % 200) + 1;
            ActualProduction::create([
                'tanggal_produksi' => '2026-06-20',
                'item_code'        => 'PART-PERF-' . str_pad($partIdx, 4, '0', STR_PAD_LEFT),
                'qty'              => 80,
            ]);
        }

        // Measure memory and load time for Analysis Dashboard
        $memBefore = memory_get_usage(true);
        $startTime = microtime(true);

        $response = $this->actingAs($this->adminUser)->get(route('purchasing.analysis'));

        $endTime = microtime(true);
        $memAfter = memory_get_usage(true);

        $durationSec = round($endTime - $startTime, 3);
        $memUsedMb = round(($memAfter - $memBefore) / (1024 * 1024), 2);

        $response->assertStatus(200);

        // Assert response time is under 10 seconds for 700 seeded records
        $this::assertLessThan(10.0, $durationSec, "Analysis dashboard page load took {$durationSec}s, which exceeds 10.0s limit.");

        // Assert memory increase is under 64MB
        $this::assertLessThan(64.0, $memUsedMb, "Analysis dashboard consumed {$memUsedMb}MB RAM, which exceeds 64MB limit.");
    }
}
