<?php

namespace App\Services;

use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use App\Models\Inventory;
use App\Models\ActualProduction;
use App\Models\TaxBudgetForecastRate;
use App\Models\TaxExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * PurchasingCalculationService
 * 
 * Single Source of Truth untuk seluruh formula dan kalkulasi bisnis
 * pada ekosistem Purchasing & Supply Chain PT Kawai Indonesia.
 */
class PurchasingCalculationService
{
    /**
     * RUMUS 1: Kalkulasi Outstanding PO (Sisa Pesanan Tertunda)
     * 
     * Outstanding = max(PO Qty - Receipt Qty, 0)
     * Over Delivery = max(Receipt Qty - PO Qty, 0)
     * 
     * @param float|int $poQty Kuantitas yang dipesan ke vendor
     * @param float|int $receivedQty Kuantitas aktual yang sudah diterima
     * @return array [ 'outstanding' => int, 'over_delivery' => int, 'fulfillment_pct' => float, 'is_complete' => bool ]
     */
    public static function calculateOutstanding($poQty, $receivedQty): array
    {
        $po = max(0, (float) $poQty);
        $rec = max(0, (float) $receivedQty);

        $outstanding = max(0, (int) round($po - $rec));
        $overDelivery = max(0, (int) round($rec - $po));
        $fulfillmentPct = $po > 0 ? min(100.0, round(($rec / $po) * 100, 1)) : ($rec > 0 ? 100.0 : 0.0);
        $isComplete = ($outstanding === 0 && $po > 0);

        return [
            'po_qty' => (int) $po,
            'received_qty' => (int) $rec,
            'outstanding' => $outstanding,
            'over_delivery' => $overDelivery,
            'fulfillment_pct' => $fulfillmentPct,
            'is_complete' => $isComplete,
        ];
    }

    /**
     * RUMUS 2: Kalkulasi Saldo Stok Roll-Forward Berantai Bulanan
     * 
     * Stock Bulan ke-i = Stock Bulan Sebelumnya + PO Bulan Ini - PROD Bulan Ini
     * 
     * @param float|int $prevStock Saldo stok akhir bulan lalu (M-1)
     * @param float|int $poQty Kuantitas order PO bulan ini
     * @param float|int $prodQty Kuantitas pemakaian produksi bulan ini
     * @return int Saldo stok akhir bulan berjalan
     */
    public static function calculateRollForwardStock($prevStock, $poQty, $prodQty): int
    {
        $prev = (float) $prevStock;
        $po   = (float) $poQty;
        $prod = (float) $prodQty;

        return (int) round($prev + $po - $prod);
    }

    /**
     * RUMUS 3: Kalkulasi Live Ratio (%) Kecukupan Stok Terhadap Kebutuhan Bulan Depan
     * 
     * Ratio (%) = ( Stock Bulan Ini / PROD Bulan Depan [M+1] ) × 100%
     * 
     * @param float|int $currentStock Saldo stok akhir bulan berjalan
     * @param float|int $nextMonthProd Kebutuhan pemakaian produksi bulan depan (M+1)
     * @return array [ 'ratio_pct' => ?float, 'status' => string, 'badge_class' => string, 'label' => string ]
     */
    public static function calculateLiveRatio($currentStock, $nextMonthProd): array
    {
        $stock = (float) $currentStock;
        $nextDemand = (float) $nextMonthProd;

        if ($nextDemand <= 0) {
            return [
                'ratio_pct' => null,
                'status' => 'NO_DEMAND',
                'badge_class' => 'badge-neutral',
                'label' => 'No Demand (PROD M+1 Kosong)'
            ];
        }

        $ratio = round(($stock / $nextDemand) * 100, 1);

        if ($ratio < 100.0) {
            $status = 'CRITICAL';
            $badgeClass = 'badge-danger';
            $label = 'Kritis (< 100%)';
        } elseif ($ratio <= 200.0) {
            $status = 'IDEAL';
            $badgeClass = 'badge-warning';
            $label = 'Normal & Ideal (100% - 200%)';
        } else {
            $status = 'OVERSTOCK';
            $badgeClass = 'badge-success';
            $label = 'Overstock (> 200%)';
        }

        return [
            'ratio_pct' => $ratio,
            'status' => $status,
            'badge_class' => $badgeClass,
            'label' => $label
        ];
    }

    /**
     * RUMUS 4: Konversi Rupiah Forecast (Kurs Budget Tahunan Bulanan)
     * 
     * Forecast Amount (Rp) = Forecast Amount ($) × Kurs Budget Bulanan
     */
    public static function calculateForecastAmount($qty, $priceUsd, $budgetRate): array
    {
        $q = max(0, (float) $qty);
        $p = max(0, (float) $priceUsd);
        $r = max(1, (float) $budgetRate);

        $amountUsd = $q * $p;
        $amountIdr = $amountUsd * $r;

        return [
            'qty' => $q,
            'price_usd' => $p,
            'budget_rate' => $r,
            'amount_usd' => round($amountUsd, 2),
            'amount_idr' => round($amountIdr, 2),
        ];
    }

    /**
     * RUMUS 5: Konversi Rupiah Incoming (Kurs Pajak Mingguan KMK)
     * 
     * Actual Amount (Rp) = Actual Qty Received × Price ($) × Kurs Pajak Mingguan KMK
     */
    public static function calculateActualAmount($qty, $priceUsd, $kmkRate): array
    {
        $q = max(0, (float) $qty);
        $p = max(0, (float) $priceUsd);
        $r = max(1, (float) $kmkRate);

        $amountUsd = $q * $p;
        $amountIdr = $amountUsd * $r;

        return [
            'qty' => $q,
            'price_usd' => $p,
            'kmk_rate' => $r,
            'amount_usd' => round($amountUsd, 2),
            'amount_idr' => round($amountIdr, 2),
        ];
    }

    /**
     * RUMUS 6: Kalkulasi Potential Supply, Net Gap & Coverage Ratio (Step 6)
     * 
     * Potential Supply = Actual Stock Fisik + Outstanding PO Qty
     * Net Supply Gap   = Potential Supply - Demand
     * Coverage Ratio   = ( Potential Supply / Demand ) × 100%
     */
    public static function calculatePotentialSupplyAndGap($actualStock, $outstandingPo, $demand): array
    {
        $stock = (float) $actualStock;
        $po    = max(0, (float) $outstandingPo);
        $dem   = max(0, (float) $demand);

        $potentialSupply = $stock + $po;
        $netGap = $potentialSupply - $dem;
        $coveragePct = $dem > 0 ? round(($potentialSupply / $dem) * 100, 1) : 100.0;

        if ($netGap < 0) {
            $status = 'CRITICAL_DEFICIT';
            $statusLabel = 'CRITICAL DEFICIT';
            $badgeClass = 'bg-danger';
        } elseif ($stock >= $dem) {
            $status = 'SURPLUS';
            $statusLabel = 'SURPLUS';
            $badgeClass = 'bg-success';
        } else {
            $status = 'COVERED_VIA_PO';
            $statusLabel = 'COVERED VIA PO';
            $badgeClass = 'bg-primary';
        }

        return [
            'actual_stock' => (int) $stock,
            'outstanding_po' => (int) $po,
            'demand' => (int) $dem,
            'potential_supply' => (int) $potentialSupply,
            'net_gap' => (int) $netGap,
            'coverage_pct' => $coveragePct,
            'status' => $status,
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
        ];
    }

    /**
     * RUMUS 7: Kalkulasi Variance (Selisih Target vs Realisasi)
     */
    public static function calculateVariance($target, $actual): array
    {
        $t = (float) $target;
        $a = (float) $actual;

        $diff = $a - $t;
        $pct = $t > 0 ? round(($diff / $t) * 100, 2) : 0.0;

        return [
            'target' => $t,
            'actual' => $a,
            'difference' => $diff,
            'difference_pct' => $pct,
            'is_surplus' => $diff >= 0,
        ];
    }

    /**
     * Dataset Terkonsolidasi untuk Metrik Dashboard Utama (Anti Mismatch Query)
     * Menggabungkan Target, Diterima, dan Pending dari grain PO + Item yang konsisten.
     */
    public static function getConsolidatedDashboardMetrics(string $selectedYear, ?string $selectedCategoryId = null): array
    {
        $logsQuery = PurchasingLog::with(['category', 'user'])
            ->where('period_month', 'like', $selectedYear . '-%');

        if ($selectedCategoryId) {
            $logsQuery->where('purchasing_category_id', $selectedCategoryId);
        }

        $logs = $logsQuery->orderBy('period_month', 'asc')->get();

        // 1. Hitung Po Metrics secara terpadu
        $poGroups = [];
        foreach ($logs as $log) {
            $poKey   = !empty($log->po_reference) ? trim($log->po_reference) : (!empty($log->item_code) ? trim($log->item_code) : 'LOG-' . $log->id);
            $itemKey = !empty($log->item_code) ? trim($log->item_code) : 'ITEM-' . $log->id;
            $uniqueKey = $poKey . '___' . $itemKey;

            if (!isset($poGroups[$uniqueKey])) {
                $poGroups[$uniqueKey] = [
                    'target' => 0,
                    'received' => 0,
                ];
            }
            $poGroups[$uniqueKey]['target'] = max($poGroups[$uniqueKey]['target'], (int) $log->target_order);
            $poGroups[$uniqueKey]['received'] += (int) $log->actual_received;
        }

        $target = 0;
        $received = 0;
        $pending = 0;

        foreach ($poGroups as $group) {
            $target += $group['target'];
            $received += $group['received'];
            $pending += max(0, $group['target'] - $group['received']);
        }

        $fulfillmentPct = $target > 0 ? round(($received / $target) * 100, 1) : 0.0;

        // 2. Status Kesehatan Pengadaan
        if ($fulfillmentPct >= 85.0) {
            $healthStatus = 'AMAN';
            $healthBadge = 'badge-health-success';
            $healthLabel = 'Aman & Terpenuhi';
        } elseif ($fulfillmentPct >= 50.0) {
            $healthStatus = 'DALAM_PROSES';
            $healthBadge = 'badge-health-warning';
            $healthLabel = 'Dalam Proses Pengiriman';
        } else {
            $healthStatus = 'PERLU_PERHATIAN';
            $healthBadge = 'badge-health-danger';
            $healthLabel = 'Perlu Perhatian Khusus';
        }

        return [
            'total_target' => $target,
            'total_received' => $received,
            'total_pending' => $pending,
            'fulfillment_pct' => $fulfillmentPct,
            'health_status' => $healthStatus,
            'health_badge' => $healthBadge,
            'health_label' => $healthLabel,
            'logs_count' => $logs->count(),
            'unique_po_items' => count($poGroups),
        ];
    }
}
