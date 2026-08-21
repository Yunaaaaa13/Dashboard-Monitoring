<?php

namespace App\Services\DataValidation;

use App\Models\ImportBatch;
use App\Models\ImportAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ImportBatchManager
 * 
 * Pengelola Siklus Batch Impor, Transaksi Atomik, dan Audit Terpadu.
 * Memastikan Zero Silent Loss: setiap baris data tercatat status dan perjalanannya.
 */
class ImportBatchManager
{
    /**
     * Buat Batch ID Baru (misal: IMP-20260819-0001)
     */
    public static function generateBatchId(): string
    {
        $prefix = 'IMP-' . date('Ymd') . '-';
        $latestBatch = ImportBatch::where('batch_id', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestBatch && preg_match('/-(\d{4})$/', $latestBatch->batch_id, $matches)) {
            $nextSeq = str_pad((int) $matches[1] + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }

    /**
     * Periksa Apakah File Pernah Diunggah Sebelumnya (Idempotency Check)
     */
    public static function checkDuplicateUpload(string $fileHash, string $templateType): ?ImportBatch
    {
        return ImportBatch::where('file_hash', $fileHash)
            ->where('template_type', $templateType)
            ->where('status', 'COMMITTED')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Eksekusi Impor Batch Secara Transaksional dan Atomik
     * 
     * @param string $templateType
     * @param string $fileName
     * @param string $fileHash
     * @param array $validatedBatch Hasil dari DataValidator::validateBatch()
     * @param callable $persister Callback untuk melakukan insert ke tabel model database
     * @param array $extraMetadata
     * @return array [ 'success' => bool, 'batch_id' => string, 'imported_count' => int, 'reconciliation' => array, 'message' => string ]
     */
    public static function executeTransactionalImport(
        string $templateType,
        string $fileName,
        string $fileHash,
        array $validatedBatch,
        callable $persister,
        array $extraMetadata = []
    ): array {
        $batchId = self::generateBatchId();
        $userName = Auth::user()->name ?? (Auth::user()->username ?? 'System / Guest');

        $totalRows = $validatedBatch['total_rows'] ?? count($validatedBatch['valid_rows'] ?? []);
        $validRows = $validatedBatch['valid_rows'] ?? [];
        $invalidRows = $validatedBatch['invalid_rows'] ?? [];
        $warningRows = $validatedBatch['warning_rows'] ?? [];
        $sourceTotalQty = (float) ($validatedBatch['total_qty_sum'] ?? 0);

        // Jika ada baris yang fatal invalid dan tidak ada baris valid
        if (count($validRows) === 0 && count($invalidRows) > 0) {
            // Catat batch sebagai FAILED
            $batch = ImportBatch::create([
                'batch_id' => $batchId,
                'template_type' => $templateType,
                'template_version' => $extraMetadata['template_version'] ?? '1.0',
                'file_name' => $fileName,
                'file_hash' => $fileHash,
                'uploaded_by' => $userName,
                'total_rows' => $totalRows,
                'valid_rows' => 0,
                'warning_rows' => count($warningRows),
                'rejected_rows' => count($invalidRows),
                'status' => 'FAILED',
                'reconciliation_status' => 'FAILED',
                'metadata' => $extraMetadata
            ]);

            self::logInvalidRows($batchId, $invalidRows);

            return [
                'success' => false,
                'batch_id' => $batchId,
                'imported_count' => 0,
                'rejected_count' => count($invalidRows),
                'message' => "Impor dibatalkan: Seluruh data ({$totalRows} baris) tidak memenuhi kriteria validasi."
            ];
        }

        // Jalankan Database Transaction Atomik
        DB::beginTransaction();
        try {
            // 1. Buat Record Batch Awal
            $batch = ImportBatch::create([
                'batch_id' => $batchId,
                'template_type' => $templateType,
                'template_version' => $extraMetadata['template_version'] ?? '1.0',
                'file_name' => $fileName,
                'file_hash' => $fileHash,
                'uploaded_by' => $userName,
                'total_rows' => $totalRows,
                'valid_rows' => count($validRows),
                'warning_rows' => count($warningRows),
                'rejected_rows' => count($invalidRows),
                'total_qty_source' => $sourceTotalQty,
                'status' => 'PROCESSING',
                'metadata' => $extraMetadata
            ]);

            // 2. Eksekusi Penyimpanan Record melalui Callback Persister
            $persistResult = $persister($validRows, $batchId);
            $importedCount = is_int($persistResult) ? $persistResult : (is_array($persistResult) ? ($persistResult['count'] ?? count($validRows)) : count($validRows));
            $importedTotalQty = is_array($persistResult) && isset($persistResult['total_qty']) ? (float) $persistResult['total_qty'] : $sourceTotalQty;

            // 3. Rekonsiliasi Otomatis
            $reconcileDiff = abs($sourceTotalQty - $importedTotalQty);
            $reconcileStatus = ($reconcileDiff < 0.0001 && $importedCount === count($validRows)) ? 'SUCCESS' : ($reconcileDiff > 0 ? 'WARNING' : 'SUCCESS');

            // 4. Catat Audit Log untuk Baris Error dan Warning
            self::logInvalidRows($batchId, $invalidRows);
            self::logWarningRows($batchId, $warningRows);

            // 5. Update Status Batch Menjadi COMMITTED
            $batch->update([
                'valid_rows' => $importedCount,
                'total_qty_imported' => $importedTotalQty,
                'reconciliation_diff' => $reconcileDiff,
                'reconciliation_status' => $reconcileStatus,
                'status' => 'COMMITTED'
            ]);

            DB::commit();

            return [
                'success' => true,
                'batch_id' => $batchId,
                'imported_count' => $importedCount,
                'rejected_count' => count($invalidRows),
                'warning_count' => count($warningRows),
                'reconciliation' => [
                    'source_qty' => $sourceTotalQty,
                    'imported_qty' => $importedTotalQty,
                    'diff' => $reconcileDiff,
                    'status' => $reconcileStatus
                ],
                'message' => "Impor batch {$batchId} berhasil! {$importedCount} baris data tersimpan secara atomik." . (count($invalidRows) > 0 ? " (" . count($invalidRows) . " baris ditolak dan dicatat di audit log)." : "")
            ];

        } catch (\Throwable $e) {
            DB::rollBack();

            // Catat kegagalan batch
            ImportBatch::create([
                'batch_id' => $batchId,
                'template_type' => $templateType,
                'file_name' => $fileName,
                'file_hash' => $fileHash,
                'uploaded_by' => $userName,
                'total_rows' => $totalRows,
                'status' => 'ROLLED_BACK',
                'reconciliation_status' => 'FAILED',
                'metadata' => array_merge($extraMetadata, ['exception' => $e->getMessage()])
            ]);

            return [
                'success' => false,
                'batch_id' => $batchId,
                'imported_count' => 0,
                'error' => $e->getMessage(),
                'message' => "Terjadi kesalahan sistem saat menyimpan batch. Seluruh transaksi telah di-rollback: " . $e->getMessage()
            ];
        }
    }

    /**
     * Catat Baris Gagal ke Tabel ImportAuditLog
     */
    protected static function logInvalidRows(string $batchId, array $invalidRows): void
    {
        foreach ($invalidRows as $inv) {
            $rowNum = $inv['row_number'] ?? 0;
            $errors = $inv['errors'] ?? [];
            $suggestions = $inv['suggestions'] ?? [];
            $raw = $inv['raw'] ?? [];

            foreach ($errors as $err) {
                ImportAuditLog::create([
                    'batch_id' => $batchId,
                    'row_number' => $rowNum,
                    'field' => 'ROW',
                    'input_value' => json_encode($raw),
                    'error_type' => 'VALIDATION_REJECTED',
                    'severity' => 'ERROR',
                    'error_message' => $err,
                    'suggestion' => !empty($suggestions) ? json_encode($suggestions) : null,
                    'is_resolved' => false
                ]);
            }
        }
    }

    /**
     * Catat Baris Peringatan ke Tabel ImportAuditLog
     */
    protected static function logWarningRows(string $batchId, array $warningRows): void
    {
        foreach ($warningRows as $w) {
            $rowNum = $w['row_number'] ?? 0;
            $warnings = $w['warnings'] ?? [];
            $raw = $w['raw'] ?? [];

            foreach ($warnings as $warn) {
                ImportAuditLog::create([
                    'batch_id' => $batchId,
                    'row_number' => $rowNum,
                    'field' => 'ROW',
                    'input_value' => json_encode($raw),
                    'error_type' => 'WARNING',
                    'severity' => 'WARNING',
                    'error_message' => $warn,
                    'is_resolved' => true
                ]);
            }
        }
    }
}
