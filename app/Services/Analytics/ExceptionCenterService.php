<?php

namespace App\Services\Analytics;

use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use App\Models\Inventory;
use App\Models\ImportBatch;
use App\Models\ActualProduction;
use Illuminate\Support\Facades\DB;

/**
 * ExceptionCenterService
 * 
 * Pusat Diagnostik Kualitas Data & Pengecualian (Data Quality & Exception Center).
 * Mendeteksi anomali data, item tanpa harga/supplier, defisit pasokan, dan
 * batch rekonsiliasi yang membutuhkan review.
 */
class ExceptionCenterService
{
    /**
     * Dapatkan Diagnostik Kesehatan Data Menyeluruh
     */
    public static function getHealthDiagnostics(): array
    {
        $exceptions = [];

        // 1. Material tanpa Supplier di Master Data
        try {
            $missingSupplierCount = PurchasingOutstanding::where(function($q) {
                $q->whereNull('supplier_name')->orWhere('supplier_name', '');
            })->count();

            if ($missingSupplierCount > 0) {
                $exceptions[] = [
                    'category' => 'MASTER_DATA',
                    'severity' => 'WARNING',
                    'count' => $missingSupplierCount,
                    'title' => "{$missingSupplierCount} Part Number Tanpa Supplier",
                    'description' => "Ditemukan part number di Master Data yang belum memiliki nama vendor resmi.",
                    'action_url' => route('purchasing.outstanding'),
                    'action_label' => 'Periksa Step 1 Master Data'
                ];
            }
        } catch (\Throwable $e) {}

        // 2. Material dengan Harga Nol atau Belum Diisi
        try {
            $missingPriceCount = PurchasingOutstanding::where(function($q) {
                $q->whereNull('price')->orWhere('price', '<=', 0);
            })->count();

            if ($missingPriceCount > 0) {
                $exceptions[] = [
                    'category' => 'FINANCIAL',
                    'severity' => 'WARNING',
                    'count' => $missingPriceCount,
                    'title' => "{$missingPriceCount} Item Belum Memiliki Harga Satuan ($)",
                    'description' => "Harga satuan material masih $0.00 sehingga valuasi finansial belum terhitung akurat.",
                    'action_url' => route('purchasing.outstanding'),
                    'action_label' => 'Lengkapi Harga Material'
                ];
            }
        } catch (\Throwable $e) {}

        // 3. Batch Impor dengan Status Rekonsiliasi Perlu Perhatian
        try {
            $unreconciledBatches = ImportBatch::where('reconciliation_status', '!=', 'SUCCESS')
                ->where('status', 'COMMITTED')
                ->count();

            if ($unreconciledBatches > 0) {
                $exceptions[] = [
                    'category' => 'RECONCILIATION',
                    'severity' => 'DANGER',
                    'count' => $unreconciledBatches,
                    'title' => "{$unreconciledBatches} Batch Impor Memerlukan Rekonsiliasi Ulang",
                    'description' => "Terdapat perbedaan antara kuantitas pada file sumber dengan baris yang berhasil diimpor.",
                    'action_url' => route('system.data-health'),
                    'action_label' => 'Buka Data Integration Health'
                ];
            }
        } catch (\Throwable $e) {}

        // 4. Over-Delivery PO (Penerimaan Melebihi Kuantitas Pesanan)
        try {
            $poGroupLogs = PurchasingLog::select('po_reference', 'item_code', DB::raw('SUM(actual_received) as total_received'))
                ->whereNotNull('po_reference')
                ->where('po_reference', '!=', '')
                ->groupBy('po_reference', 'item_code')
                ->get();

            $overDeliveryCount = 0;
            foreach ($poGroupLogs as $log) {
                $masterPo = MasterPo::where('po', $log->po_reference)
                    ->where('item_code', $log->item_code)
                    ->first();

                if ($masterPo && $log->total_received > $masterPo->qty) {
                    $overDeliveryCount++;
                }
            }

            if ($overDeliveryCount > 0) {
                $exceptions[] = [
                    'category' => 'SUPPLY_CHAIN',
                    'severity' => 'INFO',
                    'count' => $overDeliveryCount,
                    'title' => "{$overDeliveryCount} Transaksi Mengalami Over-Delivery",
                    'description' => "Kuantitas fisik yang diterima dari vendor melebihi jumlah PO yang terdaftar.",
                    'action_url' => route('purchasing.outstanding-po'),
                    'action_label' => 'Buka Step 4 Outstanding'
                ];
            }
        } catch (\Throwable $e) {}

        // 5. Hitung Skor Kualitas Data Ekosistem (Data Quality KPI)
        $totalExceptions = count($exceptions);
        if ($totalExceptions === 0) {
            $qualityScore = 100.0;
            $healthStatus = 'EXCELLENT';
            $healthBadge = 'bg-success';
        } elseif ($totalExceptions <= 2) {
            $qualityScore = 95.0;
            $healthStatus = 'GOOD';
            $healthBadge = 'bg-primary';
        } else {
            $qualityScore = max(70.0, 100 - ($totalExceptions * 8));
            $healthStatus = 'ATTENTION_REQUIRED';
            $healthBadge = 'bg-warning text-dark';
        }

        return [
            'quality_score' => $qualityScore,
            'health_status' => $healthStatus,
            'health_badge' => $healthBadge,
            'total_anomalies' => $totalExceptions,
            'exceptions' => $exceptions,
        ];
    }
}
