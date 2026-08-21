<?php

namespace App\Helpers;

use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

/**
 * Reconciliation & Validation Helper untuk Dashboard Purchasing & Inventory.
 * Memvalidasi konsistensi data antara Forecast, Master PO, Realisasi, Outstanding, dan Aktual Inventory.
 */
class PurchasingReconciliationHelper
{
    /**
     * Rekonsiliasi total antara Outstanding PO dan Master PO.
     * Memastikan kedua halaman menunjukkan angka yang konsisten.
     */
    public static function reconcileTotals(): object
    {
        // Master PO totals
        $masterPoTotalQty = MasterPo::sum('qty');
        $masterPoCount = MasterPo::count();

        // Receipt totals from PurchasingLog
        $totalReceipt = PurchasingLog::sum('actual_received');

        // Build receipt lookup by PO|ItemCode (same logic as controller)
        $receipts = PurchasingLog::select('po_reference', 'item_code', 'actual_received')->get();
        $receiptByPoItem = [];
        foreach ($receipts as $r) {
            $po = strtoupper(trim((string) $r->po_reference));
            $item = strtoupper(trim((string) $r->item_code));
            if ($po !== '' && $item !== '') {
                $key = $po . '|' . $item;
                $receiptByPoItem[$key] = ($receiptByPoItem[$key] ?? 0) + (int) $r->actual_received;
            }
        }

        // Calculate matched receipt per Master PO line
        $masterPos = MasterPo::all();
        $matchedReceiptQty = 0;
        $totalOutstanding = 0;
        $totalOverDelivery = 0;
        $linesFullyDelivered = 0;
        $linesPartiallyDelivered = 0;
        $linesNotDelivered = 0;
        $linesOverDelivered = 0;

        foreach ($masterPos as $mp) {
            $po = strtoupper(trim((string) $mp->po));
            $item = strtoupper(trim((string) $mp->item_code));
            $qtyPo = (int) $mp->qty;
            $key = $po . '|' . $item;
            $qtyReceipt = $receiptByPoItem[$key] ?? 0;

            $matchedReceiptQty += $qtyReceipt;
            $outstanding = max($qtyPo - $qtyReceipt, 0);
            $overDelivery = max($qtyReceipt - $qtyPo, 0);
            $totalOutstanding += $outstanding;
            $totalOverDelivery += $overDelivery;

            if ($qtyReceipt <= 0) $linesNotDelivered++;
            elseif ($qtyReceipt < $qtyPo) $linesPartiallyDelivered++;
            elseif ($qtyReceipt === $qtyPo) $linesFullyDelivered++;
            else $linesOverDelivered++;
        }

        $fulfillmentPct = $masterPoTotalQty > 0
            ? round(($matchedReceiptQty / $masterPoTotalQty) * 100, 1)
            : 0;

        return (object) [
            'master_po_total_qty' => $masterPoTotalQty,
            'master_po_count' => $masterPoCount,
            'matched_receipt_qty' => $matchedReceiptQty,
            'total_outstanding' => $totalOutstanding,
            'total_over_delivery' => $totalOverDelivery,
            'fulfillment_pct' => $fulfillmentPct,
            'lines_fully_delivered' => $linesFullyDelivered,
            'lines_partially_delivered' => $linesPartiallyDelivered,
            'lines_not_delivered' => $linesNotDelivered,
            'lines_over_delivered' => $linesOverDelivered,
            'is_consistent' => ($masterPoTotalQty - $matchedReceiptQty) === $totalOutstanding - $totalOverDelivery,
            // Cross-check: PO - Receipt should equal Outstanding (when no over-delivery clamp)
            'raw_outstanding_check' => ($masterPoTotalQty - $matchedReceiptQty),
            'clamped_outstanding' => $totalOutstanding,
        ];
    }

    /**
     * Rekonsiliasi Aktual Inventory dengan Forecast dan Outstanding PO.
     * Menguji tingkat ketercukupan supply (Potential Supply = Actual Inventory + Outstanding PO vs Forecast).
     */
    public static function reconcileInventory(): object
    {
        $allOutstandings = PurchasingOutstanding::all();

        $poSummaries = MasterPo::select('item_code', DB::raw('SUM(qty) as total_po_qty'))
            ->whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->groupBy('item_code')
            ->get()
            ->keyBy(fn($item) => strtoupper(trim((string)$item->item_code)));

        $receiptSummaries = PurchasingLog::select('item_code', DB::raw('SUM(actual_received) as total_receipt_qty'))
            ->whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->groupBy('item_code')
            ->get()
            ->keyBy(fn($item) => strtoupper(trim((string)$item->item_code)));

        $latestInventoryLogs = Inventory::select('part_number', 'current_stock')
            ->orderBy('tanggal_inventory', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->unique(fn($item) => strtoupper(trim((string)$item->part_number)))
            ->keyBy(fn($item) => strtoupper(trim((string)$item->part_number)));

        $totalActualInventory = 0;
        $totalForecastDemand = 0;
        $totalOutstandingPo = 0;
        $totalPotentialSupply = 0;

        $surplusCount = 0;
        $coveredByPoCount = 0;
        $criticalDeficitCount = 0;
        $optimalCount = 0;

        foreach ($allOutstandings as $os) {
            $code = strtoupper(trim((string)($os->part_number ?: $os->drawing)));
            if (!$code) continue;

            $invLog = $latestInventoryLogs->get($code);
            $actualStock = $invLog ? (int) $invLog->current_stock : (int) ($os->plan_stock ?? 0);
            $forecastDemand = (int) ($os->m1_forecast ?? ($os->m0_stock ?? 0));

            $poQty = (int) ($poSummaries->get($code)->total_po_qty ?? 0);
            $rcptQty = (int) ($receiptSummaries->get($code)->total_receipt_qty ?? 0);
            $outstandingPoQty = max(0, $poQty - $rcptQty);

            $potentialSupply = $actualStock + $outstandingPoQty;

            $totalActualInventory += $actualStock;
            $totalForecastDemand  += $forecastDemand;
            $totalOutstandingPo   += $outstandingPoQty;
            $totalPotentialSupply += $potentialSupply;

            if ($forecastDemand > 0) {
                if ($actualStock >= $forecastDemand) {
                    $surplusCount++;
                } elseif ($potentialSupply >= $forecastDemand) {
                    $coveredByPoCount++;
                } else {
                    $criticalDeficitCount++;
                }
            } else {
                $optimalCount++;
            }
        }

        $totalItems = $allOutstandings->count();
        $coverageRate = $totalItems > 0
            ? round((($surplusCount + $coveredByPoCount + $optimalCount) / $totalItems) * 100, 1)
            : 100.0;

        return (object) [
            'total_items'             => $totalItems,
            'total_actual_inventory'  => $totalActualInventory,
            'total_forecast_demand'   => $totalForecastDemand,
            'total_outstanding_po'    => $totalOutstandingPo,
            'total_potential_supply'  => $totalPotentialSupply,
            'net_supply_gap'          => $totalPotentialSupply - $totalForecastDemand,
            'surplus_count'           => $surplusCount,
            'covered_by_po_count'     => $coveredByPoCount,
            'critical_deficit_count'  => $criticalDeficitCount,
            'optimal_count'           => $optimalCount,
            'coverage_rate_pct'       => $coverageRate,
            'is_supply_sufficient'    => ($totalPotentialSupply >= $totalForecastDemand),
        ];
    }

    /**
     * Identifikasi orphaned records — data tanpa pasangan.
     */
    public static function findOrphanedRecords(): object
    {
        // Part numbers in Forecast (PurchasingOutstanding)
        $forecastParts = PurchasingOutstanding::pluck('part_number')
            ->filter()
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->values();

        // Part numbers in Master PO
        $masterPoParts = MasterPo::pluck('item_code')
            ->filter()
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->values();

        // Part numbers in Realisasi (PurchasingLog)
        $realisasiParts = PurchasingLog::pluck('item_code')
            ->filter()
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->values();

        // Forecast tanpa Master PO
        $forecastWithoutPo = $forecastParts->diff($masterPoParts)->values();

        // Forecast tanpa Realisasi
        $forecastWithoutActual = $forecastParts->diff($realisasiParts)->values();

        // Realisasi tanpa Forecast
        $actualWithoutForecast = $realisasiParts->diff($forecastParts)->values();

        // Master PO tanpa Realisasi
        $poWithoutActual = $masterPoParts->diff($realisasiParts)->values();

        return (object) [
            'total_forecast_parts' => $forecastParts->count(),
            'total_master_po_parts' => $masterPoParts->count(),
            'total_realisasi_parts' => $realisasiParts->count(),
            'forecast_without_po' => $forecastWithoutPo,
            'forecast_without_actual' => $forecastWithoutActual,
            'actual_without_forecast' => $actualWithoutForecast,
            'po_without_actual' => $poWithoutActual,
            'forecast_without_po_count' => $forecastWithoutPo->count(),
            'forecast_without_actual_count' => $forecastWithoutActual->count(),
            'actual_without_forecast_count' => $actualWithoutForecast->count(),
            'po_without_actual_count' => $poWithoutActual->count(),
        ];
    }

    /**
     * Validasi edge cases dalam dataset.
     */
    public static function validateEdgeCases(): object
    {
        $issues = [];

        // 1. Master PO dengan qty = 0 atau null
        $zeroQtyPo = MasterPo::where('qty', '<=', 0)->orWhereNull('qty')->count();
        if ($zeroQtyPo > 0) {
            $issues[] = "Found {$zeroQtyPo} Master PO records with qty <= 0 or null";
        }

        // 2. PurchasingLog dengan actual_received = 0 atau null
        $zeroReceipt = PurchasingLog::where('actual_received', '<=', 0)->orWhereNull('actual_received')->count();
        if ($zeroReceipt > 0) {
            $issues[] = "Found {$zeroReceipt} PurchasingLog records with actual_received <= 0 or null";
        }

        // 3. Blank item_code in Master PO
        $blankItemCode = MasterPo::where('item_code', '')->orWhereNull('item_code')->count();
        if ($blankItemCode > 0) {
            $issues[] = "Found {$blankItemCode} Master PO records with blank/null item_code";
        }

        // 4. Blank po_reference in PurchasingLog
        $blankPoRef = PurchasingLog::where('po_reference', '')->orWhereNull('po_reference')->count();
        if ($blankPoRef > 0) {
            $issues[] = "Found {$blankPoRef} PurchasingLog records with blank/null po_reference";
        }

        // 5. Duplicate PO + item_code in Master PO
        $duplicates = MasterPo::selectRaw('po, item_code, COUNT(*) as cnt')
            ->groupBy('po', 'item_code')
            ->having('cnt', '>', 1)
            ->count();
        if ($duplicates > 0) {
            $issues[] = "Found {$duplicates} duplicate PO + item_code combinations in Master PO";
        }

        return (object) [
            'has_issues' => count($issues) > 0,
            'issue_count' => count($issues),
            'issues' => $issues,
        ];
    }
}
