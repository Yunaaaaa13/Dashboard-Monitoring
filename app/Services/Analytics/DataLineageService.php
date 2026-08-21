<?php

namespace App\Services\Analytics;

use App\Models\ImportBatch;
use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;

/**
 * DataLineageService
 * 
 * Mesin Penelusuran Asal-Usul Data (Data Lineage & Audit Trail).
 * Memungkinkan pelacakan angka metrik dashboard hingga ke nomor baris file Excel asalnya.
 */
class DataLineageService
{
    /**
     * Telusuri Sumber Data Angka Penerimaan PO
     * 
     * @param string $itemCode
     * @param string|null $periodMonth
     * @return array
     */
    public static function traceReceiptLineage(string $itemCode, ?string $periodMonth = null): array
    {
        $query = PurchasingLog::where('item_code', $itemCode);
        if ($periodMonth) {
            $query->where('period_month', $periodMonth);
        }

        $logs = $query->get();
        $lineageRecords = [];

        foreach ($logs as $log) {
            $batch = null;
            if ($log->batch_id ?? null) {
                $batch = ImportBatch::where('batch_id', $log->batch_id)->first();
            }

            $lineageRecords[] = [
                'log_id' => $log->id,
                'po_reference' => $log->po_reference,
                'item_code' => $log->item_code,
                'actual_received' => $log->actual_received,
                'price' => $log->price,
                'period_month' => $log->period_month,
                'user_name' => $log->user->name ?? ($log->user_name ?? 'System'),
                'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
                'batch' => $batch ? [
                    'batch_id' => $batch->batch_id,
                    'file_name' => $batch->file_name,
                    'uploaded_by' => $batch->uploaded_by,
                    'uploaded_at' => $batch->created_at ? $batch->created_at->format('Y-m-d H:i:s') : '-'
                ] : null,
            ];
        }

        return [
            'item_code' => $itemCode,
            'period_month' => $periodMonth,
            'total_logs' => count($lineageRecords),
            'total_received_sum' => $logs->sum('actual_received'),
            'records' => $lineageRecords,
        ];
    }
}
