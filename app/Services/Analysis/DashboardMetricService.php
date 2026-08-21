<?php

namespace App\Services\Analysis;

use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\Forecasting;
use App\Models\ActualProduction;
use App\Models\Inventory;
use App\Models\Document;
use App\Models\TaxBudgetForecastRate;
use App\Services\Matching\ReconciliationService;
use App\Services\Normalization\CurrencyNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * DashboardMetricService
 * 
 * Menyediakan Data Availability Matrix dan metrik dinamis untuk dashboard analisis.
 * Tidak bergantung pada jumlah row, periode, atau format tertentu.
 */
class DashboardMetricService
{
    /**
     * Check data availability across all dataset types.
     * Returns which datasets are available and their basic stats.
     *
     * @return array Data availability matrix
     */
    public static function getDataAvailability(): array
    {
        // Check each data source for existence and count
        $masterPoCount = MasterPo::count();
        $incomingCount = PurchasingLog::count();
        $forecastCount = Forecasting::count();
        $productionCount = class_exists(ActualProduction::class) ? ActualProduction::count() : 0;
        $inventoryCount = class_exists(Inventory::class) ? Inventory::count() : 0;

        return [
            'master_po' => [
                'available' => $masterPoCount > 0,
                'count' => $masterPoCount,
                'label' => 'Master PO',
            ],
            'incoming' => [
                'available' => $incomingCount > 0,
                'count' => $incomingCount,
                'label' => 'Incoming / Realisasi',
            ],
            'forecast' => [
                'available' => $forecastCount > 0,
                'count' => $forecastCount,
                'label' => 'Forecast',
            ],
            'actual_production' => [
                'available' => $productionCount > 0,
                'count' => $productionCount,
                'label' => 'Actual Production',
            ],
            'actual_stock' => [
                'available' => $inventoryCount > 0,
                'count' => $inventoryCount,
                'label' => 'Actual Stock / Inventory',
            ],
        ];
    }

    /**
     * Detect the dynamic analysis horizon from actual data.
     * Does NOT assume any fixed period like June-October.
     *
     * @return array Dynamic horizon info
     */
    public static function detectDynamicHorizon(): array
    {
        // Get min/max periods from Master PO (tanggal -> YYYY-MM)
        $poMinDate = MasterPo::min('tanggal');
        $poMaxDate = MasterPo::max('tanggal');
        $poMin = $poMinDate ? substr($poMinDate, 0, 7) : null;
        $poMax = $poMaxDate ? substr($poMaxDate, 0, 7) : null;

        // Get min/max periods from Incoming (period_month is already YYYY-MM)
        $incMin = PurchasingLog::min('period_month');
        $incMax = PurchasingLog::max('period_month');

        // Get min/max from Forecasting
        $fcMin = Forecasting::min('periode');
        $fcMax = Forecasting::max('periode');

        // Compute overall range
        $allMins = array_filter([$poMin, $incMin, $fcMin]);
        $allMaxs = array_filter([$poMax, $incMax, $fcMax]);

        $overallMin = !empty($allMins) ? min($allMins) : date('Y-m');
        $overallMax = !empty($allMaxs) ? max($allMaxs) : date('Y-m');

        // Generate ordered month list
        $months = [];
        $current = $overallMin;
        $maxIterations = 120; // safety: max 10 years
        while ($current <= $overallMax && $maxIterations-- > 0) {
            $months[] = $current;
            // Next month
            $year = (int) substr($current, 0, 4);
            $month = (int) substr($current, 5, 2);
            $month++;
            if ($month > 12) { $month = 1; $year++; }
            $current = sprintf('%04d-%02d', $year, $month);
        }

        // Which months have actual incoming data?
        $monthsWithIncoming = PurchasingLog::select('period_month')
            ->distinct()
            ->pluck('period_month')
            ->filter()
            ->map(fn($p) => substr($p, 0, 7))
            ->unique()
            ->toArray();

        // Which months have PO data?
        $monthsWithPo = MasterPo::select(DB::raw("SUBSTRING(tanggal, 1, 7) as period"))
            ->distinct()
            ->pluck('period')
            ->filter()
            ->toArray();

        return [
            'min' => $overallMin,
            'max' => $overallMax,
            'months' => $months,
            'duration' => count($months),
            'po_range' => ['min' => $poMin, 'max' => $poMax],
            'incoming_range' => ['min' => $incMin, 'max' => $incMax],
            'forecast_range' => ['min' => $fcMin, 'max' => $fcMax],
            'months_with_incoming' => $monthsWithIncoming,
            'months_with_po' => $monthsWithPo,
            'last_incoming_period' => !empty($monthsWithIncoming) ? max($monthsWithIncoming) : null,
        ];
    }

    /**
     * Get aggregated KPI metrics for the dashboard.
     * All values dynamically computed from actual data.
     *
     * @param string|null $periodMin Optional minimum period filter YYYY-MM
     * @param string|null $periodMax Optional maximum period filter YYYY-MM
     * @return array KPI metrics
     */
    public static function getKpiMetrics(?string $periodMin = null, ?string $periodMax = null): array
    {
        $horizon = static::detectDynamicHorizon();
        $pMin = $periodMin ?: $horizon['min'];
        $pMax = $periodMax ?: $horizon['max'];

        // Total PO qty within period
        $totalPoQty = MasterPo::where('tanggal', '>=', $pMin . '-01')
            ->where('tanggal', '<=', $pMax . '-31')
            ->sum('qty');

        // Total incoming qty within period
        $totalIncomingQty = PurchasingLog::where(function ($q) use ($pMin, $pMax) {
                $q->where('period_month', '>=', $pMin)
                  ->where('period_month', '<=', $pMax);
            })->sum('actual_received');

        // Unique items and suppliers
        $uniqueItems = MasterPo::where('tanggal', '>=', $pMin . '-01')
            ->where('tanggal', '<=', $pMax . '-31')
            ->distinct('item_code')->count('item_code');

        $uniqueSuppliers = MasterPo::where('tanggal', '>=', $pMin . '-01')
            ->where('tanggal', '<=', $pMax . '-31')
            ->distinct('supplier')->count('supplier');

        // Outstanding
        $outstandingQty = max(0, $totalPoQty - $totalIncomingQty);
        $fulfillmentPct = $totalPoQty > 0 ? round(($totalIncomingQty / $totalPoQty) * 100, 1) : 0;

        // Currency distribution
        $poCurrencies = MasterPo::select('currency', DB::raw('COUNT(*) as cnt'))
            ->where('tanggal', '>=', $pMin . '-01')
            ->where('tanggal', '<=', $pMax . '-31')
            ->groupBy('currency')
            ->pluck('cnt', 'currency')
            ->toArray();

        $totalCurrRows = array_sum($poCurrencies);
        $currencyDist = [];
        foreach ($poCurrencies as $cur => $cnt) {
            $currencyDist[strtoupper($cur ?: 'USD')] = $totalCurrRows > 0 ? round(($cnt / $totalCurrRows) * 100, 1) : 0;
        }

        // Compute total amounts with proper currency conversion
        // Using CurrencyNormalizer if available, otherwise fallback
        $poAmountUsd = 0.0;
        MasterPo::where('tanggal', '>=', $pMin . '-01')
            ->where('tanggal', '<=', $pMax . '-31')
            ->select('qty', 'price', 'currency')
            ->chunk(500, function ($rows) use (&$poAmountUsd) {
                foreach ($rows as $row) {
                    $amt = (float) $row->qty * (float) $row->price;
                    $cur = strtoupper(trim($row->currency ?: 'USD'));
                    if ($cur === 'IDR' || (float) $row->price > 300) {
                        $poAmountUsd += CurrencyNormalizer::convertToUsd($amt, 'IDR');
                    } else {
                        $poAmountUsd += $amt;
                    }
                }
            });

        $incomingAmountUsd = 0.0;
        PurchasingLog::where(function ($q) use ($pMin, $pMax) {
                $q->where('period_month', '>=', $pMin)
                  ->where('period_month', '<=', $pMax);
            })
            ->select('actual_received', 'price', 'currency')
            ->chunk(500, function ($rows) use (&$incomingAmountUsd) {
                foreach ($rows as $row) {
                    $amt = (float) $row->actual_received * (float) $row->price;
                    $cur = strtoupper(trim($row->currency ?: 'USD'));
                    if ($cur === 'IDR' || (float) $row->price > 300) {
                        $incomingAmountUsd += CurrencyNormalizer::convertToUsd($amt, 'IDR');
                    } else {
                        $incomingAmountUsd += $amt;
                    }
                }
            });

        return [
            'period_min' => $pMin,
            'period_max' => $pMax,
            'total_po_qty' => (int) $totalPoQty,
            'total_incoming_qty' => (int) $totalIncomingQty,
            'outstanding_qty' => $outstandingQty,
            'fulfillment_pct' => $fulfillmentPct,
            'unique_items' => $uniqueItems,
            'unique_suppliers' => $uniqueSuppliers,
            'total_po_amount_usd' => round($poAmountUsd, 2),
            'total_incoming_amount_usd' => round($incomingAmountUsd, 2),
            'currency_distribution' => $currencyDist,
        ];
    }

    /**
     * Get per-period breakdown for charts.
     * Builds dynamic arrays keyed by YYYY-MM, not hardcoded month names.
     *
     * @return array Per-period data for charts
     */
    public static function getPerPeriodBreakdown(): array
    {
        $horizon = static::detectDynamicHorizon();
        $monthNames = [
            '01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR',
            '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG',
            '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC',
        ];

        // Aggregate PO qty per period
        $poByPeriod = MasterPo::select(
                DB::raw("SUBSTRING(tanggal, 1, 7) as period"),
                DB::raw("SUM(qty) as total_qty"),
                DB::raw("COUNT(*) as row_count")
            )
            ->groupBy(DB::raw("SUBSTRING(tanggal, 1, 7)"))
            ->pluck('total_qty', 'period')
            ->toArray();

        // Aggregate Incoming qty per period
        $incByPeriod = PurchasingLog::select(
                'period_month as period',
                DB::raw("SUM(actual_received) as total_qty")
            )
            ->groupBy('period_month')
            ->pluck('total_qty', 'period')
            ->toArray();

        // Build arrays aligned to dynamic horizon
        $labels = [];
        $poQtyArr = [];
        $incQtyArr = [];
        $outstandingArr = [];
        $fulfillmentArr = [];

        foreach ($horizon['months'] as $period) {
            $mm = substr($period, 5, 2);
            $labels[] = $monthNames[$mm] ?? $mm;

            $po = (int) ($poByPeriod[$period] ?? 0);
            $inc = (int) ($incByPeriod[$period] ?? 0);

            $poQtyArr[] = $po;
            // For months without incoming data, use null (not 0) so chart line stops cleanly
            $incQtyArr[] = in_array($period, $horizon['months_with_incoming']) ? $inc : null;
            $outstandingArr[] = max(0, $po - $inc);
            $fulfillmentArr[] = $po > 0 ? round(($inc / $po) * 100, 1) : null;
        }

        return [
            'labels' => $labels,
            'periods' => $horizon['months'],
            'po_qty' => $poQtyArr,
            'incoming_qty' => $incQtyArr,
            'outstanding_qty' => $outstandingArr,
            'fulfillment_pct' => $fulfillmentArr,
            'last_incoming_period' => $horizon['last_incoming_period'],
        ];
    }

    /**
     * Get Top N items by total amount (aggregated and deduplicated by item_code).
     * Each item_code appears exactly once.
     *
     * @param int $n Number of top items
     * @param string|null $periodMin Optional minimum period
     * @param string|null $periodMax Optional maximum period
     * @return array Top N items
     */
    public static function getTopItems(int $n = 10, ?string $periodMin = null, ?string $periodMax = null): array
    {
        $horizon = static::detectDynamicHorizon();
        $pMin = $periodMin ?: $horizon['min'];
        $pMax = $periodMax ?: $horizon['max'];

        // Aggregate PO data by item_code (deduplicated)
        $poData = MasterPo::where('tanggal', '>=', $pMin . '-01')
            ->where('tanggal', '<=', $pMax . '-31')
            ->select(
                'item_code',
                DB::raw('SUM(qty) as total_po_qty'),
                DB::raw('MAX(price) as unit_price'),
                DB::raw('MAX(currency) as currency'),
                DB::raw('MAX(supplier) as supplier'),
                DB::raw('MAX(name) as description')
            )
            ->groupBy('item_code')
            ->get()
            ->keyBy(fn($row) => strtoupper(trim($row->item_code)));

        // Aggregate Incoming data by item_code (deduplicated)
        $incData = PurchasingLog::where('period_month', '>=', $pMin)
            ->where('period_month', '<=', $pMax)
            ->select(
                'item_code',
                DB::raw('SUM(actual_received) as total_received_qty'),
                DB::raw('MAX(price) as unit_price'),
                DB::raw('MAX(currency) as currency'),
                DB::raw('MAX(supplier_name) as supplier')
            )
            ->groupBy('item_code')
            ->get()
            ->keyBy(fn($row) => strtoupper(trim($row->item_code)));

        // Merge and compute amounts
        $allItemCodes = $poData->keys()->merge($incData->keys())->unique();
        $items = [];

        foreach ($allItemCodes as $itemCode) {
            $po = $poData->get($itemCode);
            $inc = $incData->get($itemCode);

            $poQty = $po ? (int) $po->total_po_qty : 0;
            $incQty = $inc ? (int) $inc->total_received_qty : 0;
            $price = (float) ($po->unit_price ?? $inc->unit_price ?? 0);
            $currency = strtoupper(trim($po->currency ?? $inc->currency ?? 'USD'));
            $supplier = $po->supplier ?? $inc->supplier ?? '-';
            $description = $po->description ?? '-';

            // Convert to USD for ranking
            $amtRaw = max($poQty, $incQty) * $price;
            if ($currency === 'IDR' || $price > 300) {
                $amtUsd = CurrencyNormalizer::convertToUsd($amtRaw, 'IDR');
            } else {
                $amtUsd = $amtRaw;
            }

            $items[] = [
                'item_code' => $itemCode,
                'description' => $description,
                'supplier' => $supplier,
                'unit_price' => $price,
                'currency' => $currency,
                'total_po_qty' => $poQty,
                'total_received_qty' => $incQty,
                'outstanding_qty' => max(0, $poQty - $incQty),
                'total_amount_usd' => round($amtUsd, 2),
                'fulfillment_pct' => $poQty > 0 ? round(($incQty / $poQty) * 100, 1) : 0,
            ];
        }

        // Sort by total_amount_usd descending and take top N
        usort($items, fn($a, $b) => $b['total_amount_usd'] <=> $a['total_amount_usd']);

        return array_slice($items, 0, $n);
    }

    /**
     * Build chart data for the PO vs Incoming comparison chart (Slide 2).
     * Uses dynamic horizon — no Month 0 baseline, starts at Month 1.
     *
     * @return array Chart-ready data arrays
     */
    public static function getPoIncomingChartData(): array
    {
        $breakdown = static::getPerPeriodBreakdown();

        return [
            'labels' => $breakdown['labels'],
            'periods' => $breakdown['periods'],
            'forecast_po' => $breakdown['po_qty'],
            'actual_po' => $breakdown['incoming_qty'],
            'outstanding' => $breakdown['outstanding_qty'],
            'fulfillment' => $breakdown['fulfillment_pct'],
        ];
    }

    /**
     * Build chart data for Slide 1 FX comparison.
     * Lines stop cleanly at the last period with actual incoming data (no drop to 0).
     *
     * @return array Chart-ready data with null for future months
     */
    public static function getSlide1ChartData(): array
    {
        $horizon = static::detectDynamicHorizon();
        $lastIncoming = $horizon['last_incoming_period'];

        $breakdown = static::getPerPeriodBreakdown();

        // For incoming line: set values to null after the last period with data
        $incomingLine = [];
        $pastLastIncoming = false;
        foreach ($breakdown['periods'] as $idx => $period) {
            if ($pastLastIncoming) {
                $incomingLine[] = null;
            } else {
                $incomingLine[] = $breakdown['incoming_qty'][$idx];
                if ($period === $lastIncoming) {
                    $pastLastIncoming = true;
                }
            }
        }

        return [
            'labels' => $breakdown['labels'],
            'periods' => $breakdown['periods'],
            'po_qty' => $breakdown['po_qty'],
            'incoming_qty' => $incomingLine,
            'last_incoming_period' => $lastIncoming,
        ];
    }
}
