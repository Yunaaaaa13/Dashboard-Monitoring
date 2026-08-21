<?php

namespace Tests\Feature;

use App\Models\ImportBatch;
use App\Models\ImportAuditLog;
use App\Models\User;
use App\Services\DataValidation\DataValidator;
use App\Services\DataValidation\ImportBatchManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportBatchTransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Atomic Batch Import with Zero Silent Loss
     */
    public function test_atomic_import_batch_execution(): void
    {
        $validator = new DataValidator();

        // Dataset Uji: 3 baris valid, 1 baris invalid (Qty negatif)
        $rawRows = [
            [
                'material_code' => '1312006',
                'production_qty' => '100',
                'plant' => 'Plant 3',
                'supplier_name' => 'PT. SERBAGUNA PRIMA',
                'production_date' => '2026-08-19'
            ],
            [
                'material_code' => '1312007',
                'production_qty' => '200',
                'plant' => 'Plant 3',
                'supplier_name' => 'PT. SERBAGUNA PRIMA',
                'production_date' => '2026-08-19'
            ],
            [
                'material_code' => '1312008',
                'production_qty' => '300',
                'plant' => 'Plant 3',
                'supplier_name' => 'PT. SERBAGUNA PRIMA',
                'production_date' => '2026-08-19'
            ],
            [
                'material_code' => '1312009',
                'production_qty' => '-50', // Invalid negative qty
                'plant' => 'Plant 3',
                'supplier_name' => 'PT. SERBAGUNA PRIMA',
                'production_date' => '2026-08-19'
            ]
        ];

        $batchValidation = $validator->validateBatch('actual_production', $rawRows);

        $this->assertEquals(4, $batchValidation['total_rows']);
        $this->assertEquals(3, $batchValidation['valid_count']);
        $this->assertEquals(1, $batchValidation['invalid_count']);

        // Eksekusi Transactional Import
        $fileHash = md5('test_actual_prod_file');
        $persisterCalled = 0;

        $res = ImportBatchManager::executeTransactionalImport(
            'actual_production',
            'test_production_august.xlsx',
            $fileHash,
            $batchValidation,
            function($validRows, $batchId) use (&$persisterCalled) {
                $persisterCalled = count($validRows);
                return [
                    'count' => count($validRows),
                    'total_qty' => 600.0 // 100 + 200 + 300
                ];
            },
            ['template_version' => '1.1']
        );

        $this->assertTrue($res['success']);
        $this->assertEquals(3, $persisterCalled);
        $this->assertEquals(1, $res['rejected_count']);

        // Verifikasi di Database
        $batch = ImportBatch::where('batch_id', $res['batch_id'])->first();
        $this->assertNotNull($batch);
        $this->assertEquals('COMMITTED', $batch->status);
        $this->assertEquals(3, $batch->valid_rows);
        $this->assertEquals(1, $batch->rejected_rows);
        $this->assertEquals('SUCCESS', $batch->reconciliation_status);

        // Verifikasi Row-Level Audit Log untuk 1 baris yang ditolak
        $auditLog = ImportAuditLog::where('batch_id', $res['batch_id'])->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals(4, $auditLog->row_number);
        $this->assertEquals('ERROR', $auditLog->severity);
    }
}
