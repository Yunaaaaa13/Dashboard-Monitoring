<?php

namespace App\Http\Controllers;

use App\Models\PurchasingOutstanding;
use App\Models\PurchasingLog;
use App\Models\PurchasingCategory;
use App\Models\Forecasting;
use App\Models\Outstanding;
use App\Models\Actual;
use App\Models\ForecastActual;
use App\Models\ComparisonMaster;
use Database\Seeders\PurchasingOutstandingSeeder;
use Illuminate\Http\Request;

class PurchasingOutstandingController extends Controller
{
    /**
     * Tampilkan halaman monitoring data Outstanding Order Material
     */
    public function index(Request $request)
    {
        PurchasingOutstanding::clearCalcCaches();
        \App\Models\Forecasting::clearLookupsCache();
        $statusFilter = $request->get('status', 'All');
        $supplierFilter = $request->get('supplier', 'All');
        $searchQuery  = $request->get('search');

        $suppliers = PurchasingOutstanding::whereNotNull('supplier_name')->where('supplier_name', '!=', '')
            ->distinct()->pluck('supplier_name')
            ->map(fn($s) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($s))
            ->filter()->unique()->sort()->values();

        $query = PurchasingOutstanding::with('category');

        if ($statusFilter && $statusFilter !== 'All') {
            $query->where('status', $statusFilter);
        }

        if ($supplierFilter && $supplierFilter !== 'All') {
            $variations = \App\Services\DataValidation\InputNormalizer::getSupplierVariations($supplierFilter);
            $query->whereIn('supplier_name', $variations);
        }

        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('po_number', 'like', "%{$searchQuery}%")
                  ->orWhere('part_number', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%")
                  ->orWhere('drawing', 'like', "%{$searchQuery}%")
                  ->orWhere('supplier_name', 'like', "%{$searchQuery}%");
            });
        }

        $perPageParam = $request->get('per_page', '50');
        $perPage = ($perPageParam === 'ALL' || $perPageParam === 'all') ? null : max(10, (int)$perPageParam);

        if ($perPage) {
            $items = $query->orderBy('part_number', 'asc')->paginate($perPage)->withQueryString();
        } else {
            $items = $query->orderBy('part_number', 'asc')->get();
        }

        // Fast SQL Aggregate KPIs Keseluruhan
        $totalItems        = PurchasingOutstanding::count();
        $totalOrderQty     = (int) PurchasingOutstanding::sum('order_qty');
        $totalCompleteQty  = (int) PurchasingOutstanding::sum('complete');
        $totalPendingQty   = max(0, $totalOrderQty - $totalCompleteQty);
        $totalAmount       = (float) PurchasingOutstanding::sum('amount');
        $overallProgress   = $totalOrderQty > 0 ? round(($totalCompleteQty / $totalOrderQty) * 100, 1) : 0;

        if ($request->has('duration')) {
            $duration = max(1, min(36, (int)$request->get('duration')));
            session(['monitor_duration' => $duration]);
        } else {
            $duration = (int) session('monitor_duration', 12);
        }

        // Auto-detect dataset's fixed Pre-Month from DB
        // Note: Forecasting stores running months starting from Month 1 (e.g. 2026-07 = JUL).
        // Therefore, the dataset's fixed Pre-Month (Month 0) is 1 month prior to $firstPeriod (e.g. 2026-06 = JUN).
        $firstPeriod = \App\Models\Forecasting::orderBy('periode', 'asc')->value('periode');
        if ($firstPeriod && preg_match('/^(\d{4})-(\d{2})$/', $firstPeriod, $pMatches)) {
            $preTimestamp = strtotime("{$pMatches[1]}-{$pMatches[2]}-01 -1 month");
            $dbStartMonth = strtoupper(date('M', $preTimestamp));
            $dbStartYear  = (int) date('Y', $preTimestamp);
        } else {
            $dbStartMonth = 'JUN';
            $dbStartYear  = 2026;
        }

        // Pre-month is fixed to the uploaded dataset's starting month and cannot be altered by query parameter
        $startMonth = session('monitor_start_month', $dbStartMonth);
        if (!$startMonth) {
            $startMonth = $dbStartMonth;
        }
        session(['monitor_start_month' => $startMonth]);

        if ($request->has('start_year')) {
            $startYear = (int) $request->get('start_year');
            session(['monitor_start_year' => $startYear]);
        } else {
            $startYear = (int) session('monitor_start_year', $dbStartYear);
        }

        $allMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $startIndex = array_search($startMonth, $allMonths);
        if ($startIndex === false) {
            $startIndex = 5; // JUN default
            $startMonth = 'JUN';
        }

        $months = [];
        for ($i = 0; $i <= 36; $i++) {
            $months[$i] = $allMonths[($startIndex + $i) % 12];
        }
        session([
            'monitor_duration'    => $duration,
            'monitor_start_month' => $startMonth,
            'monitor_start_year'  => $startYear,
            'monitor_months'      => $months,
        ]);

        // --- Forecast vs Actual Aggregation ---
        $forecastYear = $request->get('forecast_year', date('Y'));

        $monthsList = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
        ];

        // Ambil semua log untuk tahun yang dipilih
        $forecastLogs = PurchasingLog::with(['category', 'user'])
            ->where('period_month', 'like', $forecastYear . '-%')
            ->orderBy('period_month', 'asc')
            ->get();

        // Helper untuk menghitung metrik target & aktual ter-deduplikasi per PO + Item Code
        $calcDeduplicatedMetrics = function($logCollection) {
            $poGroups = [];
            foreach ($logCollection as $log) {
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

            return [
                'target'   => $target,
                'received' => $received,
                'pending'  => $pending,
            ];
        };

        // Agregasi per bulan
        $forecastByMonth = [];
        foreach ($monthsList as $num => $label) {
            $periodStr = $forecastYear . '-' . $num;
            $monthLogs = $forecastLogs->where('period_month', $periodStr);
            $monthMetrics = $calcDeduplicatedMetrics($monthLogs);
            $target    = $monthMetrics['target'];
            $actual    = $monthMetrics['received'];
            $prod      = (int) $monthLogs->sum('production_qty');
            $pending   = $monthMetrics['pending'];
            $gap       = $actual - $target;               // positif = over, negatif = under
            $fulfPct   = $target > 0 ? round(($actual / $target) * 100, 1) : null;

            $forecastByMonth[$num] = [
                'label'       => $label,
                'period'      => $periodStr,
                'target'      => $target,
                'actual'      => $actual,
                'production'  => $prod,
                'pending'     => $pending,
                'gap'         => $gap,
                'fulfillment' => $fulfPct,
                'has_data'    => $target > 0 || $actual > 0,
            ];
        }

        // Agregasi per kategori
        $allCategories = PurchasingCategory::with('buyer')->get();
        $forecastByCategory = [];
        foreach ($allCategories as $cat) {
            $catLogs = $forecastLogs->where('purchasing_category_id', $cat->id);
            $catMetrics = $calcDeduplicatedMetrics($catLogs);
            $target  = $catMetrics['target'];
            $actual  = $catMetrics['received'];
            $gap     = $actual - $target;
            $fulfPct = $target > 0 ? round(($actual / $target) * 100, 1) : null;

            $forecastByCategory[] = [
                'code'        => $cat->category_code,
                'name'        => $cat->category_name,
                'target'      => $target,
                'actual'      => $actual,
                'production'  => (int) $catLogs->sum('production_qty'),
                'pending'     => $catMetrics['pending'],
                'gap'         => $gap,
                'fulfillment' => $fulfPct,
            ];
        }

        // KPI Totals
        $overallForecastMetrics = $calcDeduplicatedMetrics($forecastLogs);
        $forecastTotalTarget  = $overallForecastMetrics['target'];
        $forecastTotalActual  = $overallForecastMetrics['received'];
        $forecastTotalProd    = (int) $forecastLogs->sum('production_qty');
        $forecastTotalPending = $overallForecastMetrics['pending'];
        $forecastTotalGap     = $forecastTotalActual - $forecastTotalTarget;
        $forecastFulfillPct   = $forecastTotalTarget > 0
            ? round(($forecastTotalActual / $forecastTotalTarget) * 100, 1)
            : 0;

        // Chart data arrays (12 bulan)
        $chartLabels   = array_values($monthsList);
        $chartTarget   = array_column($forecastByMonth, 'target');
        $chartActual   = array_column($forecastByMonth, 'actual');
        $chartGap      = array_map(fn($t, $a) => $a - $t, $chartTarget, $chartActual);

        // Available years for filter
        $availableYears = PurchasingLog::selectRaw("SUBSTRING(period_month, 1, 4) as yr")
            ->distinct()
            ->orderBy('yr', 'desc')
            ->pluck('yr')
            ->toArray();
        if (!in_array($forecastYear, $availableYears)) {
            array_unshift($availableYears, $forecastYear);
        }

        // Komparasi langsung Outstanding vs Forecast Actual (Sesuai Proses Bisnis PT Kawai: Forecast Actual = PO - Outstanding)
        $comparisonPeriod = $request->get('comparison_period', now()->format('Y-m'));
        $comparisonData = $this->buildComparisonData($comparisonPeriod);

        // ─── 3 Master Data Terpisah (Forecast, Actual, Outstanding) ───
        $periode = $request->get('periode');

        $forecastQuery = Forecasting::query();
        $actualQuery = Actual::query();
        $outstandingQuery = Outstanding::query();

        if ($periode && $periode !== 'All') {
            $forecastQuery->where('periode', $periode);
            $actualQuery->where('periode', $periode);
            $outstandingQuery->where('periode', $periode);
        }

        if ($searchQuery) {
            $forecastQuery->where(function($q) use ($searchQuery) {
                $q->where('part_number', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%");
            });
            $actualQuery->where(function($q) use ($searchQuery) {
                $q->where('part_number', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%");
            });
            $outstandingQuery->where(function($q) use ($searchQuery) {
                $q->where('part_number', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%")
                  ->orWhere('po', 'like', "%{$searchQuery}%");
            });
        }

        // KPI Totals 3 Master
        $masterForecastCount = (clone $forecastQuery)->count();
        $masterForecastTotalQty = (int) (clone $forecastQuery)->sum('forecast_qty');

        $masterActualCount = (clone $actualQuery)->count();
        $masterActualTotalQty = (int) (clone $actualQuery)->sum('actual_qty');

        $masterOutstandingCount = (clone $outstandingQuery)->count();
        $masterOutstandingTotalQty = (int) (clone $outstandingQuery)->sum('outstanding_qty');

        // Kumpulkan semua periode unik dari ke-3 tabel untuk filter dropdown, dinormalisasi
        $periodes = collect()
            ->concat(Forecasting::distinct()->whereNotNull('periode')->pluck('periode'))
            ->concat(Actual::distinct()->whereNotNull('periode')->pluck('periode'))
            ->concat(Outstanding::distinct()->whereNotNull('periode')->pluck('periode'))
            ->filter()
            ->map(fn ($p) => $this->normalizePeriodString((string) $p))
            ->unique()
            ->sortDesc()
            ->values();

        $forecastList = $forecastQuery->orderBy('periode', 'desc')->orderBy('part_number', 'asc')->paginate(50, ['*'], 'forecast_page')->withQueryString();
        $actualList = $actualQuery->orderBy('periode', 'desc')->orderBy('part_number', 'asc')->paginate(50, ['*'], 'actual_page')->withQueryString();
        $outstandingList = $outstandingQuery->orderBy('periode', 'desc')->orderBy('part_number', 'asc')->paginate(50, ['*'], 'outstanding_page')->withQueryString();

        // Data Monitoring Status Pemenuhan Material (Revisi Legacy)
        $fulfillmentMonitoringList = $this->buildFulfillmentMonitoringData($periode, $searchQuery);
        $monitoringTotalForecast = $fulfillmentMonitoringList->sum('forecast_qty');
        $monitoringTotalActual = $fulfillmentMonitoringList->sum('actual_qty');
        $monitoringTotalOutstanding = $fulfillmentMonitoringList->sum('outstanding_qty');
        $monitoringFulfillmentPct = $monitoringTotalForecast > 0 ? round(($monitoringTotalActual / $monitoringTotalForecast) * 100, 1) : 0;
        $monitoringChartLabels = $fulfillmentMonitoringList->map(fn($r) => $r->part_number . ($periode && $periode !== 'All' ? '' : ' (' . $r->periode . ')') )->values();
        $monitoringChartForecast = $fulfillmentMonitoringList->pluck('forecast_qty')->values();
        $monitoringChartActual = $fulfillmentMonitoringList->pluck('actual_qty')->values();
        $monitoringChartOutstanding = $fulfillmentMonitoringList->pluck('outstanding_qty')->values();

        $importDuplicatesFound = session()->pull('import_duplicates_found');

        $viewData = array_merge([
            'importDuplicatesFound'    => $importDuplicatesFound,
            'periode'                  => $periode,
            'periodes'                 => $periodes,
            'forecastList'             => $forecastList,
            'actualList'               => $actualList,
            'outstandingList'          => $outstandingList,
            'masterForecastCount'      => $masterForecastCount,
            'masterForecastTotalQty'   => $masterForecastTotalQty,
            'masterActualCount'        => $masterActualCount,
            'masterActualTotalQty'     => $masterActualTotalQty,
            'masterOutstandingCount'   => $masterOutstandingCount,
            'masterOutstandingTotalQty'=> $masterOutstandingTotalQty,
            'fulfillmentMonitoringList'  => $fulfillmentMonitoringList,
            'monitoringTotalForecast'    => $monitoringTotalForecast,
            'monitoringTotalActual'      => $monitoringTotalActual,
            'monitoringTotalOutstanding' => $monitoringTotalOutstanding,
            'monitoringFulfillmentPct'   => $monitoringFulfillmentPct,
            'monitoringChartLabels'      => $monitoringChartLabels,
            'monitoringChartForecast'    => $monitoringChartForecast,
            'monitoringChartActual'      => $monitoringChartActual,
            'monitoringChartOutstanding' => $monitoringChartOutstanding,
            'items'            => $items,
            'perPageParam'     => $perPageParam,
            'categories'       => $allCategories,
            'buyers'           => \App\Models\User::orderBy('name')->get(),
            'suppliers'        => $suppliers,
            'supplierFilter'   => $supplierFilter,
            'statusFilter'     => $statusFilter,
            'searchQuery'      => $searchQuery,
            'totalItems'       => $totalItems,
            'totalOrderQty'    => $totalOrderQty,
            'totalCompleteQty' => $totalCompleteQty,
            'totalPendingQty'  => $totalPendingQty,
            'totalAmount'      => $totalAmount,
            'overallProgress'  => $overallProgress,
            'duration'         => $duration,
            'startMonth'       => $startMonth,
            'startYear'        => $startYear,
            'months'           => $months,
            'm0Name'           => $months[0] ?? 'JULY',
            'm1Name'           => $months[1] ?? 'AUG',
            'm2Name'           => $months[2] ?? 'SEP',
            'm3Name'           => $months[3] ?? 'OCT',
            'forecastYear'         => $forecastYear,
            'forecastByMonth'      => $forecastByMonth,
            'forecastByCategory'   => $forecastByCategory,
            'forecastTotalTarget'  => $forecastTotalTarget,
            'forecastTotalActual'  => $forecastTotalActual,
            'forecastTotalProd'    => $forecastTotalProd,
            'forecastTotalPending' => $forecastTotalPending,
            'forecastTotalGap'     => $forecastTotalGap,
            'forecastFulfillPct'   => $forecastFulfillPct,
            'chartLabels'          => $chartLabels,
            'chartTarget'          => $chartTarget,
            'chartActual'          => $chartActual,
            'chartGap'             => $chartGap,
            'availableYears'       => $availableYears,
        ], $comparisonData);

        return view('purchasing.outstanding', $viewData);
    }

    /**
     * Membangun data Monitoring Status Pemenuhan Material dari 3 Master (Forecast, Actual, Outstanding)
     * dengan Part Number & Periode sebagai key.
     */
    private function buildFulfillmentMonitoringData(?string $periode, ?string $searchQuery = null): \Illuminate\Support\Collection
    {
        $forecastQuery = Forecasting::query();
        $actualQuery = Actual::query();
        $outstandingQuery = Outstanding::query();

        if ($periode && $periode !== 'All') {
            $variants = $this->getPeriodVariantsString($periode);
            $forecastQuery->where(function($q) use ($variants) {
                $q->whereIn('periode', $variants)->orWhereIn('period_month', $variants);
            });
            $actualQuery->where(function($q) use ($variants) {
                $q->whereIn('periode', $variants)->orWhereIn('period_month', $variants);
            });
            $outstandingQuery->where(function($q) use ($variants) {
                $q->whereIn('periode', $variants)->orWhereIn('period_month', $variants);
            });
        }

        if ($searchQuery) {
            $forecastQuery->where(function($q) use ($searchQuery) {
                $q->where('part_number', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%");
            });
            $actualQuery->where(function($q) use ($searchQuery) {
                $q->where('part_number', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%");
            });
            $outstandingQuery->where(function($q) use ($searchQuery) {
                $q->where('part_number', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%")
                  ->orWhere('po', 'like', "%{$searchQuery}%");
            });
        }

        $fList = $forecastQuery->get();
        $aList = $actualQuery->get();
        $oList = $outstandingQuery->get();

        $map = [];

        foreach ($fList as $item) {
            $part = trim($item->part_number);
            $p = $this->normalizePeriodString($item->periode ?? $item->period_month ?? '');
            $key = $part . '___' . $p;
            if (!isset($map[$key])) {
                $map[$key] = [
                    'part_number'     => $part,
                    'periode'         => $p,
                    'description'     => $item->description ?? '-',
                    'forecast_qty'    => 0,
                    'actual_qty'      => 0,
                    'outstanding_qty' => 0,
                    'has_forecast'    => false,
                ];
            }
            $map[$key]['forecast_qty'] += (int) $item->forecast_qty;
            $map[$key]['has_forecast'] = true;
            if ($map[$key]['description'] === '-' && !empty($item->description)) {
                $map[$key]['description'] = $item->description;
            }
        }

        foreach ($aList as $item) {
            $part = trim($item->part_number);
            $p = $this->normalizePeriodString($item->periode ?? $item->period_month ?? '');
            $key = $part . '___' . $p;
            if (!isset($map[$key])) {
                $map[$key] = [
                    'part_number'     => $part,
                    'periode'         => $p,
                    'description'     => $item->description ?? '-',
                    'forecast_qty'    => 0,
                    'actual_qty'      => 0,
                    'outstanding_qty' => 0,
                    'has_forecast'    => false,
                ];
            }
            $map[$key]['actual_qty'] += (int) $item->actual_qty;
            if ($map[$key]['description'] === '-' && !empty($item->description)) {
                $map[$key]['description'] = $item->description;
            }
        }

        foreach ($oList as $item) {
            $part = trim($item->part_number);
            $p = $this->normalizePeriodString($item->periode ?? $item->period_month ?? '');
            $key = $part . '___' . $p;
            if (!isset($map[$key])) {
                $map[$key] = [
                    'part_number'     => $part,
                    'periode'         => $p,
                    'description'     => $item->description ?? '-',
                    'forecast_qty'    => 0,
                    'actual_qty'      => 0,
                    'outstanding_qty' => 0,
                    'has_forecast'    => false,
                ];
            }
            $map[$key]['outstanding_qty'] += (int) $item->outstanding_qty;
            if ($map[$key]['description'] === '-' && !empty($item->description)) {
                $map[$key]['description'] = $item->description;
            }
        }

        usort($map, function($a, $b) {
            if ($a['periode'] === $b['periode']) {
                return strcmp($a['part_number'], $b['part_number']);
            }
            return strcmp($b['periode'], $a['periode']);
        });

        $results = [];
        foreach ($map as $row) {
            $fQty = $row['forecast_qty'];
            $aQty = $row['actual_qty'];
            $oQty = $row['outstanding_qty'];

            $fulfPct = $fQty > 0 ? round(($aQty / $fQty) * 100, 1) : null;

            if ($fQty > 0) {
                if ($aQty >= $fQty && $oQty == 0) {
                    $status = 'Complete';
                    $statusBadge = 'badge bg-success bg-opacity-25 text-success border border-success px-3 py-1';
                    $recommendation = 'Material Selesai';
                    $recBadge = 'badge bg-success text-white px-3 py-1 rounded-pill fw-semibold';
                } elseif (($aQty + $oQty) > $fQty || $aQty > $fQty) {
                    $status = 'Overstock';
                    $statusBadge = 'badge bg-danger bg-opacity-25 text-danger border border-danger px-3 py-1';
                    $recommendation = 'Evaluasi Forecast';
                    $recBadge = 'badge bg-danger text-white px-3 py-1 rounded-pill fw-semibold';
                } elseif (($aQty + $oQty) == $fQty && $oQty > 0) {
                    $status = 'Material Cukup';
                    $statusBadge = 'badge bg-info bg-opacity-25 text-info border border-info px-3 py-1';
                    $recommendation = 'Monitoring Supplier';
                    $recBadge = 'badge bg-info text-dark px-3 py-1 rounded-pill fw-semibold';
                } else {
                    $status = 'Follow Up Supplier';
                    $statusBadge = 'badge bg-warning bg-opacity-25 text-warning border border-warning px-3 py-1';
                    $recommendation = 'Tambah Purchase Order';
                    $recBadge = 'badge bg-warning text-dark px-3 py-1 rounded-pill fw-semibold';
                }
            } else {
                if ($oQty == 0) {
                    $status = 'Complete';
                    $statusBadge = 'badge bg-success bg-opacity-25 text-success border border-success px-3 py-1';
                    $recommendation = 'Material Selesai';
                    $recBadge = 'badge bg-success text-white px-3 py-1 rounded-pill fw-semibold';
                } else {
                    $status = 'Overstock';
                    $statusBadge = 'badge bg-danger bg-opacity-25 text-danger border border-danger px-3 py-1';
                    $recommendation = 'Evaluasi Forecast';
                    $recBadge = 'badge bg-danger text-white px-3 py-1 rounded-pill fw-semibold';
                }
            }

            $row['fulfillment_pct'] = $fulfPct;
            $row['status'] = $status;
            $row['status_badge'] = $statusBadge;
            $row['recommendation'] = $recommendation;
            $row['recommendation_badge'] = $recBadge;

            $results[] = (object) $row;
        }

        return collect($results)->reject(function ($item) {
            return $item->forecast_qty === 0 && $item->actual_qty === 0 && $item->outstanding_qty === 0 && !($item->has_forecast ?? false);
        })->values();
    }

    /**
     * Endpoint JSON untuk Auto-Update Live Tanpa Refresh Halaman
     */
    public function comparisonJson(Request $request)
    {
        $comparisonPeriod = $request->get('comparison_period', now()->format('Y-m'));
        $data = $this->buildComparisonData($comparisonPeriod);
        return response()->json([
            'status' => 'success',
            'period' => $comparisonPeriod,
            'data'   => $data,
        ], 200);
    }

    /**
     * Helper untuk menghitung data komparasi PT Kawai (Outstanding vs Forecast Actual)
     * Membaca dari tabel purchasing_comparison_master sebagai single source of truth.
     */
    private function buildComparisonData(string $comparisonPeriod): array
    {
        // Pastikan tabel sudah ada sebelum query
        if (!\Illuminate\Support\Facades\Schema::hasTable('purchasing_comparison_master')) {
            // Fallback ke join real-time jika tabel belum ada (migrasi belum dijalankan)
            return $this->buildComparisonDataFallback($comparisonPeriod);
        }

        // Sinkronisasi data hanya jika tabel komparasi untuk periode ini belum terisi
        $hasComparison = ComparisonMaster::where('periode', $comparisonPeriod)->exists();
        if (!$hasComparison) {
            $outstandingParts = Outstanding::where('periode', $comparisonPeriod)
                ->orWhere('period_month', $comparisonPeriod)
                ->pluck('part_number');
            $actualParts = Actual::where('periode', $comparisonPeriod)
                ->orWhere('period_month', $comparisonPeriod)
                ->pluck('part_number');
            $faParts = ForecastActual::where('periode', $comparisonPeriod)
                ->pluck('part_number');
            $compParts = ComparisonMaster::where('periode', $comparisonPeriod)
                ->pluck('part_number');

            $allParts = $outstandingParts->merge($actualParts)->merge($faParts)->merge($compParts)->unique();
            foreach ($allParts as $pn) {
                ComparisonMaster::sync($pn, $comparisonPeriod);
            }
        }

        // Baca dari tabel komparasi tersimpan
        $comparisonRows = ComparisonMaster::where('periode', $comparisonPeriod)
            ->orderBy('part_number')
            ->get()
            ->map(function ($row) {
                // Mapping ke format standar yang digunakan view
                return (object) [
                    'part_number'          => $row->part_number,
                    'description'          => $row->description ?? '-',
                    'period'               => $row->periode,
                    'periode'              => $row->periode,
                    'po'                   => (int) ($row->outstanding_qty > 0 ? $row->outstanding_qty : ($row->forecast_actual ?? 0)),
                    'outstanding'          => (int) $row->outstanding_qty,
                    'outstanding_qty'      => (int) $row->outstanding_qty,
                    'forecast_actual'      => (int) $row->forecast_actual,
                    'actual_po'            => (int) $row->actual_po,
                    'actual_production'    => (int) $row->actual_production,
                    'actual_stock'         => (int) $row->actual_po,
                    'selisih'              => (int) $row->selisih,
                    'coverage'             => $row->coverage,
                    'outstanding_pct'      => $row->coverage !== null ? round(100 - min(100, (float) $row->coverage), 1) : 0,
                    'forecast_pct'         => $row->coverage !== null ? min(100, round((float) $row->coverage, 1)) : 0,
                    'status'               => $row->status,
                    'status_badge'         => $row->status_badge,
                    'has_outstanding'      => (bool) $row->has_outstanding,
                    'has_actual'           => (bool) $row->has_forecast,
                    'has_forecast'         => (bool) $row->has_forecast,
                    'updated_at'           => $row->synced_at ?? $row->updated_at,
                    'actual_updated_at'    => $row->synced_at ?? $row->updated_at,
                    'outstanding_updated_at' => $row->synced_at ?? $row->updated_at,
                ];
            });

        // KPI
        $totalPo              = (int) $comparisonRows->sum('po');
        $totalOutstanding     = (int) $comparisonRows->sum('outstanding');
        $totalForecastActual  = (int) $comparisonRows->sum('forecast_actual');
        $pctOutstanding       = $totalPo > 0 ? round(($totalOutstanding / $totalPo) * 100, 1) : 0;
        $pctForecastActual    = $totalPo > 0 ? round(($totalForecastActual / $totalPo) * 100, 1) : 0;
        $countMaterialAman    = $comparisonRows->where('status', 'Material Aman')->count();
        $countPerluMonitoring = $comparisonRows->where('status', 'Perlu Monitoring')->count();
        $countKurangMaterial  = $comparisonRows->where('status', 'Kurang Material')->count();
        $countMenungguData    = $comparisonRows->where('status', 'Menunggu Data')->count();
        $countOutstandingParts = $comparisonRows->filter(fn($r) => (int)$r->outstanding > 0)->count();
        $countCompleteParts    = $comparisonRows->filter(fn($r) => (int)$r->outstanding === 0)->count();
        $validCoverages        = $comparisonRows->filter(fn($r) => $r->coverage !== null)->pluck('coverage');
        $averageCoverage       = $validCoverages->count() > 0 ? round($validCoverages->avg(), 1) : null;

        // Chart arrays
        $comparisonChartLabels         = $comparisonRows->map(fn($r) => $r->part_number)->values()->toArray();
        $comparisonChartOutstanding    = $comparisonRows->map(fn($r) => $r->outstanding)->values()->toArray();
        $comparisonChartForecastActual = $comparisonRows->map(fn($r) => $r->forecast_actual)->values()->toArray();
        $comparisonChartPo             = $comparisonRows->map(fn($r) => $r->po)->values()->toArray();

        // Periode selector
        $comparisonPeriods = ComparisonMaster::pluck('periode')
            ->merge(Outstanding::pluck('periode'))
            ->merge(Outstanding::pluck('period_month'))
            ->merge(Actual::pluck('periode'))
            ->merge(Actual::pluck('period_month'))
            ->unique()->filter()->sortDesc()->values();
        if (!$comparisonPeriods->contains($comparisonPeriod)) {
            $comparisonPeriods->prepend($comparisonPeriod);
        }

        return [
            'comparisonPeriod'              => $comparisonPeriod,
            'comparisonPeriods'             => $comparisonPeriods,
            'comparisonRows'                => $comparisonRows,
            'comparisonTotalPo'             => $totalPo,
            'comparisonTotalOutstanding'    => $totalOutstanding,
            'comparisonTotalForecastActual' => $totalForecastActual,
            'comparisonPctOutstanding'      => $pctOutstanding,
            'comparisonPctForecastActual'   => $pctForecastActual,
            'comparisonCountOutstandingParts' => $countOutstandingParts,
            'comparisonCountCompleteParts'  => $countCompleteParts,
            'comparisonCountMaterialAman'   => $countMaterialAman,
            'comparisonCountPerluMonitoring'=> $countPerluMonitoring,
            'comparisonCountKurangMaterial' => $countKurangMaterial,
            'comparisonCountMenungguData'   => $countMenungguData,
            'comparisonAverageCoverage'     => $averageCoverage,
            'comparisonChartLabels'         => $comparisonChartLabels,
            'comparisonChartOutstanding'    => $comparisonChartOutstanding,
            'comparisonChartForecastActual' => $comparisonChartForecastActual,
            'comparisonChartPo'             => $comparisonChartPo,
            // Backward compat
            'comparisonForecastCount'       => $comparisonRows->filter(fn($r) => $r->forecast_actual > 0)->count(),
            'comparisonOutstandingCount'    => $countOutstandingParts,
            'comparisonActualCount'         => $comparisonRows->filter(fn($r) => $r->forecast_actual > 0)->count(),
            'forecastVsActualRows'          => $comparisonRows,
            'comparisonSafeCount'           => $countMaterialAman,
            'comparisonWarningCount'        => $countPerluMonitoring,
            'comparisonShortageCount'       => $countKurangMaterial,
        ];
    }

    /**
     * Fallback: JOIN real-time (digunakan jika tabel purchasing_comparison_master belum tersedia)
     */
    private function buildComparisonDataFallback(string $comparisonPeriod): array
    {
        $comparisonOutstandings = Outstanding::where('periode', $comparisonPeriod)
            ->orWhere('period_month', $comparisonPeriod)
            ->get()->keyBy('part_number');
        $comparisonForecastActuals = ForecastActual::where('periode', $comparisonPeriod)->get()->keyBy('part_number');
        $masterOutstandings = PurchasingOutstanding::all()->keyBy('part_number');
        $comparisonActuals = Actual::where('periode', $comparisonPeriod)->orWhere('period_month', $comparisonPeriod)->get()->keyBy('part_number');

        $comparisonPartNumbers = $comparisonOutstandings->keys()
            ->merge($masterOutstandings->keys())
            ->merge($comparisonForecastActuals->keys())
            ->merge($comparisonActuals->keys())
            ->unique()->sort()->values();

        $comparisonRows = $comparisonPartNumbers->map(function ($partNumber) use ($comparisonOutstandings, $masterOutstandings, $comparisonForecastActuals, $comparisonActuals, $comparisonPeriod) {
            $outstandingRecord = $comparisonOutstandings->get($partNumber);
            $master            = $masterOutstandings->get($partNumber);
            $faRecord          = $comparisonForecastActuals->get($partNumber);
            $actualRecord      = $comparisonActuals->get($partNumber);

            $po             = (int) ($faRecord?->po ?? $master?->order_qty ?? $outstandingRecord?->outstanding_qty ?? 0);
            $outstandingQty = $outstandingRecord ? (int) $outstandingRecord->outstanding_qty : $po;
            
            $forecastActual = 0;
            if ($faRecord && $faRecord->forecast_actual !== null && $faRecord->forecast_actual > 0) {
                $forecastActual = (int) $faRecord->forecast_actual;
            } elseif ($actualRecord && $actualRecord->actual_po !== null && $actualRecord->actual_po > 0) {
                $forecastActual = (int) $actualRecord->actual_po;
            } elseif ($po > 0 && $outstandingQty < $po) {
                $forecastActual = max(0, $po - $outstandingQty);
            }

            $description    = $master?->description ?? ($faRecord?->description ?? '-');

            $outstandingPct = $po > 0 ? round(($outstandingQty / $po) * 100, 1) : 0;
            $forecastPct    = $po > 0 ? round(($forecastActual / $po) * 100, 1) : 0;
            
            $coverage = null;
            if ($outstandingQty > 0) {
                $coverage = round(($forecastActual / $outstandingQty) * 100, 1);
            } elseif ($outstandingQty === 0 && $po > 0) {
                $coverage = round(($forecastActual / $po) * 100, 1);
            } elseif ($outstandingQty === 0 && $forecastActual > 0) {
                $coverage = 100.0;
            }

            if (!$outstandingRecord && !$actualRecord && !$faRecord && $coverage === null) {
                $status = 'Menunggu Data'; $statusBadge = 'bg-secondary text-white';
            } elseif ($coverage === null) {
                $status = 'Menunggu Data'; $statusBadge = 'bg-secondary text-white';
            } elseif ($coverage > 100) {
                $status = 'Material Aman'; $statusBadge = 'bg-success text-white';
            } elseif ($coverage >= 90) {
                $status = 'Perlu Monitoring'; $statusBadge = 'bg-warning text-dark';
            } else {
                $status = 'Kurang Material'; $statusBadge = 'bg-danger text-white';
            }

            return (object) [
                'part_number'          => $partNumber,
                'description'          => $description,
                'period'               => $comparisonPeriod,
                'periode'              => $comparisonPeriod,
                'po'                   => $po,
                'outstanding'          => $outstandingQty,
                'outstanding_qty'      => $outstandingQty,
                'forecast_actual'      => $forecastActual,
                'actual_po'            => (int) ($actualRecord?->actual_po ?? 0),
                'actual_production'    => (int) ($actualRecord?->actual_production ?? 0),
                'actual_stock'         => (int) ($actualRecord?->actual_stock ?? 0),
                'selisih'              => $forecastActual - $outstandingQty,
                'outstanding_pct'      => $outstandingPct,
                'forecast_pct'         => $forecastPct,
                'coverage'             => $coverage,
                'status'               => $status,
                'status_badge'         => $statusBadge,
                'has_outstanding'      => (bool) ($outstandingRecord || $master),
                'has_actual'           => (bool) ($faRecord || $actualRecord),
                'has_forecast'         => (bool) ($faRecord || $actualRecord),
                'updated_at'           => $outstandingRecord?->updated_at ?? now(),
                'actual_updated_at'    => $actualRecord?->updated_at ?? null,
                'outstanding_updated_at' => $outstandingRecord?->updated_at ?? null,
            ];
        });

        $totalPo             = (int) $comparisonRows->sum('po');
        $totalOutstanding    = (int) $comparisonRows->sum('outstanding');
        $totalForecastActual = (int) $comparisonRows->sum('forecast_actual');
        $pctOutstanding      = $totalPo > 0 ? round(($totalOutstanding / $totalPo) * 100, 1) : 0;
        $pctForecastActual   = $totalPo > 0 ? round(($totalForecastActual / $totalPo) * 100, 1) : 0;
        $countOutstandingParts = $comparisonRows->filter(fn($r) => (int)$r->outstanding > 0)->count();
        $countCompleteParts    = $comparisonRows->filter(fn($r) => (int)$r->outstanding === 0)->count();

        $comparisonChartLabels         = $comparisonRows->map(fn($r) => $r->part_number)->values()->toArray();
        $comparisonChartOutstanding    = $comparisonRows->map(fn($r) => $r->outstanding)->values()->toArray();
        $comparisonChartForecastActual = $comparisonRows->map(fn($r) => $r->forecast_actual)->values()->toArray();
        $comparisonChartPo             = $comparisonRows->map(fn($r) => $r->po)->values()->toArray();

        $comparisonPeriods = Forecasting::pluck('periode')
            ->merge(Outstanding::pluck('periode'))
            ->merge(ForecastActual::pluck('periode'))
            ->merge(Actual::pluck('periode'))
            ->unique()->filter()->sortDesc()->values();
        if (!$comparisonPeriods->contains($comparisonPeriod)) $comparisonPeriods->prepend($comparisonPeriod);

        return [
            'comparisonPeriod'              => $comparisonPeriod,
            'comparisonPeriods'             => $comparisonPeriods,
            'comparisonRows'                => $comparisonRows,
            'comparisonTotalPo'             => $totalPo,
            'comparisonTotalOutstanding'    => $totalOutstanding,
            'comparisonTotalForecastActual' => $totalForecastActual,
            'comparisonPctOutstanding'      => $pctOutstanding,
            'comparisonPctForecastActual'   => $pctForecastActual,
            'comparisonCountOutstandingParts' => $countOutstandingParts,
            'comparisonCountCompleteParts'  => $countCompleteParts,
            'comparisonCountMaterialAman'   => $comparisonRows->where('status', 'Material Aman')->count(),
            'comparisonCountPerluMonitoring'=> $comparisonRows->where('status', 'Perlu Monitoring')->count(),
            'comparisonCountKurangMaterial' => $comparisonRows->where('status', 'Kurang Material')->count(),
            'comparisonCountMenungguData'   => $comparisonRows->where('status', 'Menunggu Data')->count(),
            'comparisonAverageCoverage'     => null,
            'comparisonChartLabels'         => $comparisonChartLabels,
            'comparisonChartOutstanding'    => $comparisonChartOutstanding,
            'comparisonChartForecastActual' => $comparisonChartForecastActual,
            'comparisonChartPo'             => $comparisonChartPo,
            'comparisonForecastCount'       => $comparisonRows->filter(fn($r) => $r->forecast_actual > 0)->count(),
            'comparisonOutstandingCount'    => $countOutstandingParts,
            'comparisonActualCount'         => $comparisonRows->filter(fn($r) => $r->forecast_actual > 0)->count(),
            'forecastVsActualRows'          => $comparisonRows,
            'comparisonSafeCount'           => $comparisonRows->where('status', 'Material Aman')->count(),
            'comparisonWarningCount'        => $comparisonRows->where('status', 'Perlu Monitoring')->count(),
            'comparisonShortageCount'       => $comparisonRows->where('status', 'Kurang Material')->count(),
        ];
    }

    /**
     * Simpan data Outstanding Order baru
     */
    public function store(Request $request)
    {
        $rules = [
            'po_number'     => 'nullable|string',
            'po_date'       => 'nullable|date',
            'part_number'   => 'required|string',
            'description'   => 'required|string',
            'category_id'   => 'nullable|exists:purchasing_categories,id',
            'order_qty'     => 'nullable|integer|min:0',
            'drawing'       => 'nullable|string',
            'price'         => 'nullable',
            'price_deviation_reason' => 'nullable|string',
            'complete'      => 'nullable|integer|min:0',
            'supplier_name' => 'nullable|string',
            'eta_date'      => 'nullable|date',
            'plan_stock'    => 'nullable|integer',
            'plan_outstand' => 'nullable|integer',
            'actual_po'         => 'nullable|integer|min:0',
            'actual_production' => 'nullable|integer|min:0',
        ];
        for ($i = 0; $i <= 36; $i++) {
            $rules["m{$i}_po"]   = 'nullable|integer';
            $rules["m{$i}_prod"] = 'nullable|integer';
        }
        $validated = $request->validate($rules);

        // Do not default to 1000 to avoid accidental outstanding creation.
        // Treat missing order_qty as 0 for the PO master, but only sync to Outstanding
        // if the request explicitly provided `order_qty` or set `create_outstanding`.
        $orderQty = (int) ($validated['order_qty'] ?? 0);
        $rawPrice = $request->input('price');
        $price    = ($rawPrice !== null && $rawPrice !== '') ? (float) str_replace(',', '.', (string) $rawPrice) : 0.0;
        $drawingClean = strtoupper(trim($validated['drawing'] ?? '-'));
        $partNumberClean = strtoupper(trim($validated['part_number']));
        if ($price <= 0) {
            $fc = \App\Models\Forecasting::where('part_number', $partNumberClean)->where('price', '>', 0)->first();
            if ($fc) {
                $price = (float) $fc->price;
            }
        }
        $complete = (int) ($validated['complete'] ?? ($validated['actual_po'] ?? 0));
        $amount   = $orderQty * $price;
        $status = 'Pending';
        if ($complete >= $orderQty && $orderQty > 0) {
            $status = 'Complete';
        } elseif ($complete > 0) {
            $status = 'On Progress';
        }
        $poNumber = strtoupper($validated['po_number'] ?? $validated['part_number'] ?? ('PO-KI-' . date('Ym') . '-' . rand(100, 999)));

        $createData = [
            'po_number'      => $poNumber,
            'po_date'        => $validated['po_date'] ?? date('Y-m-d'),
            'part_number'    => $partNumberClean,
            'factory_code'   => strtoupper(trim($request->input('factory_code', 'KIP 1'))),
            'description'    => $validated['description'],
            'category_id'    => $validated['category_id'] ?? null,
            'order_qty'      => $orderQty,
            'drawing'        => $drawingClean,
            'price'          => $price,
            'price_deviation_reason' => $request->input('price_deviation_reason'),
            'amount'         => $amount,
            'complete'       => $complete,
            'status'         => $status,
            'workflow_stage' => 'waiting_manager',
            'approval_notes' => 'Draft PO Baru Dibuat - Menunggu Approval Manager Purchasing',
            'supplier_name'  => !empty($validated['supplier_name']) ? $validated['supplier_name'] : null,
            'eta_date'       => $validated['eta_date'] ?? null,
            'plan_stock'     => (int) ($validated['plan_stock'] ?? 0),
            'plan_outstand'  => (int) ($validated['plan_outstand'] ?? 0),
        ];
        for ($i = 0; $i <= 12; $i++) {
            $createData["m{$i}_po"]   = (int) ($validated["m{$i}_po"] ?? 0);
            $createData["m{$i}_prod"] = (int) ($validated["m{$i}_prod"] ?? 0);
        }

        // Cari berdasarkan Item Code (drawing - Primary Key) & No. PO (part_number - Secondary Key)
        $queryKey = [];
        if ($drawingClean !== '-' && !empty($drawingClean)) {
            $queryKey['drawing'] = $drawingClean;
        }
        $queryKey['part_number'] = $partNumberClean;

        if ($request->filled('start_month')) {
            session(['monitor_start_month' => strtoupper(trim($request->input('start_month')))]);
        }
        if ($request->filled('start_year')) {
            session(['monitor_start_year' => (int) $request->input('start_year')]);
        }
        if ($request->filled('duration')) {
            session(['monitor_duration' => (int) $request->input('duration')]);
        }

        $item = PurchasingOutstanding::updateOrCreate($queryKey, $createData);



        if ($request->filled('actual_po') || $request->filled('actual_production') || $complete > 0) {
            $actualPo   = (int) ($request->get('actual_po', $complete));
            $actualProd = (int) ($request->get('actual_production', 0));
            
            $itemCodeKey = ($drawingClean && $drawingClean !== '-') ? strtoupper($drawingClean) : strtoupper($partNumberClean);
            $currentPeriod = session('monitor_m0', now()->format('Y-m'));

            $prevForecast = \App\Models\Forecasting::where('part_number', $itemCodeKey)->orderBy('id', 'desc')->first();
            $prevStock    = $prevForecast ? (int) $prevForecast->stock : 0;
            $actualStock  = $prevStock + $actualPo - $actualProd;

            \App\Models\Actual::updateOrCreate(
                [
                    'part_number' => $itemCodeKey,
                    'periode'     => $currentPeriod,
                ],
                [
                    'actual_qty'        => $actualProd,
                    'actual_po'         => $actualPo,
                    'actual_production' => $actualProd,
                    'actual_stock'      => $actualStock,
                    'period_month'      => now()->format('Y-m'),
                ]
            );
        }

        if ($complete < $orderQty) {
            $diff = number_format($orderQty - $complete, 0, ',', '.');
            session()->flash('warning', "⚠️ <strong>Peringatan Outstanding (Pending Qty):</strong> Unit Diterima (" . number_format($complete, 0, ',', '.') . " unit) <strong>belum memenuhi Target Order PO</strong> (" . number_format($orderQty, 0, ',', '.') . " unit). Terdapat sisa kekurangan <strong>$diff unit</strong> yang masuk ke daftar monitoring On Progress.");
        }

        return redirect()->route('purchasing.outstanding')
            ->with('success', 'Data Realisasi & Ratio part ' . strtoupper($validated['part_number']) . ' berhasil disimpan/diperbarui.');
    }

    /**
     * Update progress Qty Selesai atau informasi part
     */
    public function update(Request $request, $id)
    {
        $item = PurchasingOutstanding::findOrFail($id);

        $rules = [
            'description' => 'nullable|string',
            'supplier_name' => 'nullable|string',
            'drawing'    => 'nullable|string',
            'category_id' => 'nullable|exists:purchasing_categories,id',
            'order_qty'  => 'nullable|integer|min:0',
            'price'      => 'nullable',
            'price_deviation_reason' => 'nullable|string',
            'complete'   => 'nullable|integer|min:0',
            'plan_stock' => 'nullable|integer',
            'actual_po'         => 'nullable|integer|min:0',
            'actual_production' => 'nullable|integer|min:0',
        ];
        for ($i = 0; $i <= 36; $i++) {
            $rules["m{$i}_po"]   = 'nullable|integer';
            $rules["m{$i}_prod"] = 'nullable|integer';
        }
        $rules['user_id']   = 'nullable|exists:users,id';
        $rules['pic_buyer'] = 'nullable|string';
        $validated = $request->validate($rules);

        // Avoid defaulting to 1000 which may create unintended Outstanding values.
        $orderQty = (int) ($validated['order_qty'] ?? $item->order_qty ?? 0);
        $rawPrice = $request->input('price');
        if ($rawPrice !== null && $rawPrice !== '') {
            $price = (float) str_replace(',', '.', (string) $rawPrice);
        } else {
            $price = (float) ($item->price ?? 0.0);
        }
        if ($price <= 0) {
            $itemCodeKey = $item->part_number ?: $item->drawing;
            $fc = \App\Models\Forecasting::where('part_number', $itemCodeKey)->where('price', '>', 0)->first();
            if ($fc) {
                $price = (float) $fc->price;
            }
        }
        $complete = min($orderQty, (int) ($validated['complete'] ?? ($validated['actual_po'] ?? $item->complete ?? 0)));
        $amount   = $orderQty * $price;

        $status = 'Pending';
        if ($complete >= $orderQty && $orderQty > 0) {
            $status = 'Complete';
        } elseif ($complete > 0) {
            $status = 'On Progress';
        }

        $updateData = [
            'category_id' => $validated['category_id'] ?? $item->category_id,
            'order_qty'  => $orderQty,
            'price'      => $price,
            'amount'     => $amount,
            'complete'   => $complete,
            'status'     => $status,
            'plan_stock' => (int) ($validated['plan_stock'] ?? 0),
        ];
        if (isset($validated['description'])) {
            $updateData['description'] = $validated['description'];
        }
        if (isset($validated['supplier_name'])) {
            $updateData['supplier_name'] = $validated['supplier_name'];
        }
        if (isset($validated['drawing'])) {
            $updateData['drawing'] = strtoupper($validated['drawing']);
        }
        if ($request->has('user_id')) {
            $updateData['user_id'] = $request->user_id ?: null;
        }
        if ($request->has('pic_buyer')) {
            $updateData['pic_buyer'] = $request->pic_buyer ?: null;
        }
        if ($request->has('price_deviation_reason')) {
            $updateData['price_deviation_reason'] = $request->price_deviation_reason;
        }

        // Auto-lookup PIC Buyer name from User model if user_id is set but pic_buyer is empty
        if (!empty($updateData['user_id']) && empty($updateData['pic_buyer'])) {
            $u = \App\Models\User::find($updateData['user_id']);
            if ($u) {
                $updateData['pic_buyer'] = $u->name;
            }
        }

        // Fallback from Category if still empty
        $catId = $updateData['category_id'] ?? $item->category_id;
        if ($catId && empty($updateData['pic_buyer'])) {
            $cat = \App\Models\PurchasingCategory::with('buyer')->find($catId);
            if ($cat) {
                if ($cat->pic_buyer) {
                    $updateData['pic_buyer'] = $cat->pic_buyer;
                } elseif ($cat->buyer) {
                    $updateData['pic_buyer'] = $cat->buyer->name;
                    $updateData['user_id'] = $updateData['user_id'] ?? $cat->buyer_user_id;
                }
            }
        }

        for ($i = 0; $i <= 12; $i++) {
            $updateData["m{$i}_po"]   = (int) ($validated["m{$i}_po"] ?? 0);
            $updateData["m{$i}_prod"] = (int) ($validated["m{$i}_prod"] ?? 0);
        }

        if (in_array($item->workflow_stage, ['revision_manager', 'iad_rejected'], true)) {
            $updateData['workflow_stage'] = 'waiting_manager';
            $updateData['approval_notes'] = '✅ PO telah direvisi format & datanya oleh Staff - Menunggu Approval Ulang Manager';
        }

        if ($request->filled('start_month')) {
            session(['monitor_start_month' => strtoupper(trim($request->input('start_month')))]);
        }
        if ($request->filled('start_year')) {
            session(['monitor_start_year' => (int) $request->input('start_year')]);
        }
        if ($request->filled('duration')) {
            session(['monitor_duration' => (int) $request->input('duration')]);
        }

        $item->update($updateData);

        if ($request->filled('start_month') && $request->filled('duration')) {
            $duration   = (int) $request->duration;
            $startMonth = strtoupper(trim($request->start_month));
            $allMonths  = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            $startIndex = array_search($startMonth, $allMonths);
            if ($startIndex === false) { $startIndex = 6; }
            $finalMonths = [];
            for ($m = 0; $m <= 12; $m++) {
                $finalMonths[$m] = $allMonths[($startIndex + $m) % 12];
            }
            session([
                'monitor_duration'    => $duration,
                'monitor_start_month' => $startMonth,
                'monitor_months'      => $finalMonths,
                'monitor_m0'          => $finalMonths[0] ?? 'JULY',
                'monitor_m1'          => $finalMonths[1] ?? 'AUG',
                'monitor_m2'          => $finalMonths[2] ?? 'SEP',
                'monitor_m3'          => $finalMonths[3] ?? 'OCT',
            ]);
        } elseif ($request->filled('custom_month')) {
            session(['monitor_m0' => strtoupper(trim($request->custom_month))]);
        }

        $periodStr = session('monitor_m0', now()->format('Y-m'));
        $yearMonthStr = now()->format('Y-m');

        $itemCodeKey = ($item->drawing && $item->drawing !== '-') ? strtoupper($item->drawing) : strtoupper($item->part_number);

        if ($request->filled('actual_po') || $request->filled('actual_production') || $complete > 0) {
            $actualPo   = (int) ($request->get('actual_po', $complete));
            $actualProd = (int) ($request->get('actual_production', 0));
            
            $prevForecast = \App\Models\Forecasting::where('part_number', $itemCodeKey)->orderBy('id', 'desc')->first();
            $prevStock    = $prevForecast ? (int) $prevForecast->stock : 0;
            $actualStock  = $prevStock + $actualPo - $actualProd;

            \App\Models\Actual::updateOrCreate(
                [
                    'part_number' => $itemCodeKey,
                    'periode'     => $periodStr,
                ],
                [
                    'actual_qty'        => $actualProd,
                    'actual_po'         => $actualPo,
                    'actual_production' => $actualProd,
                    'actual_stock'      => $actualStock,
                    'period_month'      => $yearMonthStr,
                ]
            );
            \App\Models\Actual::updateOrCreate(
                [
                    'part_number' => $itemCodeKey,
                    'periode'     => $yearMonthStr,
                ],
                [
                    'actual_qty'        => $actualProd,
                    'actual_po'         => $actualPo,
                    'actual_production' => $actualProd,
                    'actual_stock'      => $actualStock,
                    'period_month'      => $periodStr,
                ]
            );
        }

        if ($complete < $orderQty) {
            $diff = number_format($orderQty - $complete, 0, ',', '.');
            session()->flash('warning', "⚠️ <strong>Peringatan Outstanding (Pending Qty):</strong> Unit Diterima (" . number_format($complete, 0, ',', '.') . " unit) <strong>belum memenuhi Target Order PO</strong> (" . number_format($orderQty, 0, ',', '.') . " unit). Terdapat sisa kekurangan <strong>$diff unit</strong> yang masih dalam status On Progress / Pending.");
        }

        return redirect()->route('purchasing.outstanding')
            ->with('success', 'Progress outstanding part ' . $item->part_number . ' diperbarui menjadi ' . $complete . ' / ' . $orderQty . ' unit (' . $item->progress_percentage . '%).');
    }

    /**
     * Hapus data outstanding
     */
    public function destroy($id)
    {
        $item = PurchasingOutstanding::findOrFail($id);
        $partNo = $item->part_number;
        $item->delete();

        session()->forget('import_duplicates_found');

        return redirect()->route('purchasing.outstanding')
            ->with('success', 'Data Outstanding part ' . $partNo . ' berhasil dihapus.');
    }

    /**
     * Hapus data komparasi bulanan (Target Outstanding & Realisasi Actual)
     */
    public function destroyComparison(Request $request)
    {
        $partNumber = strtoupper(trim($request->input('part_number', '')));
        $periode    = trim($request->input('periode', ''));

        if (!empty($partNumber) && !empty($periode)) {
            \App\Models\Outstanding::where('part_number', $partNumber)
                ->where(function($q) use ($periode) {
                    $q->where('periode', $periode)->orWhere('period_month', $periode);
                })->delete();

            \App\Models\Actual::where('part_number', $partNumber)
                ->where(function($q) use ($periode) {
                    $q->where('periode', $periode)->orWhere('period_month', $periode);
                })->delete();

            if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_forecast_actuals')) {
                \App\Models\ForecastActual::where('part_number', $partNumber)
                    ->where('periode', $periode)
                    ->delete();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_comparison_master')) {
                \App\Models\ComparisonMaster::where('part_number', $partNumber)
                    ->where('periode', $periode)
                    ->delete();
            }
        }

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => "Data komparasi (Target Outstanding & Forecast Actual) untuk part {$partNumber} periode {$periode} berhasil dihapus.",
                'period'  => $periode,
                'periode' => $periode,
            ], 200);
        }

        return redirect()->route('purchasing.outstanding', ['comparison_period' => $periode])
            ->with('success', "Data komparasi (Target Outstanding & Realisasi Actual) untuk part {$partNumber} periode {$periode} berhasil dihapus.");
    }

    /**
     * Reset / Seed Default Data Outstanding PT Kawai Indonesia
     */
    public function seedDefault()
    {
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'PurchasingOutstandingSeeder'
        ]);

        return redirect()->route('purchasing.outstanding')
            ->with('success', 'Berhasil memuat 6 data standar KAWAI ke monitoring Outstanding Order.');
    }

    /**
     * Kelola transisi alur kerja PO (Approval Manager -> Konfirmasi Supplier -> Check Delivery IAD)
     */
    public function updateWorkflow(Request $request, $id)
    {
        $item = PurchasingOutstanding::findOrFail($id);
        $action = $request->input('action');
        $notes  = $request->input('notes');

        $rules = [
            'approve_manager' => [['leader', 'supervisor'], ['waiting_manager', 'revision_manager']],
            'reject_manager' => [['leader', 'supervisor'], ['waiting_manager', 'revision_manager']],
            'send_to_supplier' => [['leader', 'supervisor'], ['approved_manager']],
            'confirm_supplier' => [['leader', 'supervisor'], ['approved_manager', 'waiting_supplier']],
            'ship_material' => [['leader', 'supervisor'], ['approved_manager', 'waiting_supplier']],
            'reject_supplier' => [['leader', 'supervisor'], ['waiting_supplier']],
            'arrive_for_iad' => [['staff', 'leader', 'supervisor'], ['waiting_supplier', 'material_shipped']],
            'iad_passed' => [['leader', 'supervisor'], ['material_shipped', 'iad_check', 'iad_rejected']],
            'iad_pass' => [['leader', 'supervisor'], ['material_shipped', 'iad_check', 'iad_rejected']],
            'iad_rejected' => [['leader', 'supervisor'], ['material_shipped', 'iad_check', 'iad_rejected']],
            'iad_reject' => [['leader', 'supervisor'], ['material_shipped', 'iad_check', 'iad_rejected']],
        ];

        if (!isset($rules[$action])) {
            return redirect()->back()->with('error', 'Aksi alur kerja tidak dikenal.');
        }

        [$roles, $stages] = $rules[$action];
        if (!in_array(auth()->user()->role, $roles, true) || !in_array($item->workflow_stage, $stages, true)) {
            abort(403, 'Aksi ini tidak diizinkan untuk peran atau tahap PO saat ini.');
        }

        switch ($action) {
            case 'approve_manager':
                $item->update([
                    'workflow_stage' => 'approved_manager',
                    'approval_notes' => 'PO Disetujui Manager Purchasing - Siap dikirim ke Supplier',
                ]);
                return redirect()->back()->with('success', 'PO ' . ($item->po_number ?: $item->part_number) . ' berhasil Di-approve oleh Manager Purchasing.');

            case 'reject_manager':
                $item->update([
                    'workflow_stage' => 'revision_manager',
                    'approval_notes' => $notes ?: 'Revisi format/isi PO diminta oleh Supervisor/Leader',
                ]);
                return redirect()->back()->with('error', 'PO ' . ($item->po_number ?: $item->part_number) . ' dikembalikan ke Staff untuk Revisi.');

            case 'send_to_supplier':
            case 'confirm_supplier':
                if ($item->workflow_stage === 'approved_manager') {
                    $item->update([
                        'workflow_stage' => 'waiting_supplier',
                        'status'         => 'On Progress',
                        'approval_notes' => 'PO dikirim ke Supplier - Menunggu konfirmasi pengiriman material',
                    ]);
                    return redirect()->back()->with('success', 'PO ' . ($item->po_number ?: $item->part_number) . ' berhasil dikirim ke Supplier.');
                }
            case 'ship_material':
                $item->update([
                    'workflow_stage' => 'material_shipped',
                    'status'         => 'On Progress',
                    'approval_notes' => 'Supplier mengonfirmasi kesanggupan & material dalam pengiriman',
                ]);
                return redirect()->back()->with('success', 'Supplier mengonfirmasi PO ' . ($item->po_number ?: $item->part_number) . '. Material sedang dikirim.');

            case 'reject_supplier':
                $item->update([
                    'workflow_stage' => 'revision_supplier',
                    'approval_notes' => $notes ?: 'Supplier meminta revisi jadwal / kuantitas PO',
                ]);
                return redirect()->back()->with('error', 'Supplier meminta Revisi pada PO ' . ($item->po_number ?: $item->part_number) . '.');

            case 'arrive_for_iad':
                $item->update([
                    'workflow_stage' => 'iad_check',
                    'status'         => 'On Progress',
                    'approval_notes' => 'Material tiba di KIIC Karawang - Sedang Check Delivery & Pelaksanaan IAD',
                ]);
                return redirect()->back()->with('success', 'Material PO ' . ($item->po_number ?: $item->part_number) . ' tiba di pabrik. Masuk tahap Check IAD.');

            case 'iad_passed':
            case 'iad_pass':
                $item->update([
                    'workflow_stage' => 'completed',
                    'status'         => 'Complete',
                    'complete'       => $item->order_qty,
                    'approval_notes' => 'Lolos IAD (Incoming Acceptance Decision) - Material Diterima & Masuk Realisasi Gudang',
                ]);

                // Catat otomatis sekali saja ke log penerimaan bulanan.
                $alreadyLogged = \App\Models\PurchasingLog::where('po_reference', $item->po_number ?: ('PO-' . $item->part_number))->exists();
                if (!$alreadyLogged) {
                    \App\Models\PurchasingLog::create([
                        'purchasing_category_id' => 1,
                        'user_id'                => auth()->id(),
                        'po_reference'           => $item->po_number ?: ('PO-' . $item->part_number),
                        'period_month'           => date('Y-m', strtotime($item->po_date ?: now())),
                        'target_order'           => $item->order_qty,
                        'actual_received'        => $item->order_qty,
                        'pending_order'          => 0,
                        'status_note'            => '✅ Disetujui Diterima (Supervisor: ' . (auth()->user() ? auth()->user()->name : 'Budi Santoso') . ') - Lolos IAD (' . $item->part_number . ') - Diterima Gudang KIIC',
                    ]);
                }

                return redirect()->back()->with('success', 'YES - Material PO ' . ($item->po_number ?: $item->part_number) . ' LOLOS IAD 100%! Material berhasil masuk ke stok realisasi gudang.');

            case 'iad_rejected':
            case 'iad_reject':
                $item->update([
                    'workflow_stage' => 'iad_rejected',
                    'approval_notes' => $notes ?: 'GAGAL IAD (Kualitas/Defect tidak memenuhi standar) - Harap Kirim Ulang Material!',
                ]);
                return redirect()->back()->with('error', 'NO (GAGAL IAD) - Material PO ' . ($item->po_number ?: $item->part_number) . ' DITOLAK pada pemeriksaan IAD! Harus mengirim ulang material.');

            default:
                return redirect()->back();
        }
    }

    /**
     * Sesuaikan / Atur Nama Bulan Monitoring
     */
    public function updateMonths(Request $request)
    {
        $validated = $request->validate([
            'duration'    => 'required|integer|min:1|max:36',
            'start_month' => 'required|string|max:20',
            'start_year'  => 'nullable|integer|min:2020|max:2035',
            'months'      => 'nullable|array',
            'months.*'    => 'nullable|string|max:20',
        ]);

        $duration   = (int) $validated['duration'];
        $startMonth = strtoupper(trim($validated['start_month']));
        $startYear  = (int) ($validated['start_year'] ?? session('monitor_start_year', (int) date('Y')));
        $monthsList = $validated['months'] ?? [];

        $allMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $startIndex = array_search($startMonth, $allMonths);
        if ($startIndex === false) {
            $startIndex = 0; // JAN default
            $startMonth = 'JAN';
        }

        $finalMonths = [];
        for ($i = 0; $i <= 36; $i++) {
            if (!empty($monthsList[$i])) {
                $finalMonths[$i] = strtoupper(trim($monthsList[$i]));
            } else {
                $monthIdx = ($startIndex + $i) % 12;
                $finalMonths[$i] = $allMonths[$monthIdx];
            }
        }

        session([
            'monitor_duration'    => $duration,
            'monitor_start_month' => $startMonth,
            'monitor_start_year'  => $startYear,
            'monitor_months'      => $finalMonths,
            'monitor_m0'          => $finalMonths[0] ?? 'JAN',
            'monitor_m1'          => $finalMonths[1] ?? 'FEB',
            'monitor_m2'          => $finalMonths[2] ?? 'MAR',
            'monitor_m3'          => $finalMonths[3] ?? 'APR',
        ]);

        return redirect()->back()->with('success', "Periode bulan monitoring berhasil diatur menjadi {$duration} bulan, dimulai dari {$startMonth}.");
    }



    /**
     * Hapus banyak data Purchasing Outstanding sekaligus (Bulk Delete).
     */
    public function destroyBulk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $deleteAll = $request->boolean('delete_all', false) || $request->input('all') == 1;

            if (empty($ids) && !$deleteAll) {
                return redirect()->back()->with('error', 'Tidak ada data terpilih untuk dihapus.');
            }

            \Illuminate\Support\Facades\DB::transaction(function() use ($ids, $deleteAll) {
                $totalCount = PurchasingOutstanding::count();
                if ($deleteAll || (!empty($ids) && count($ids) >= $totalCount)) {
                    PurchasingOutstanding::query()->delete();
                    \App\Models\Forecasting::query()->delete();
                    \App\Models\Outstanding::query()->delete();
                    \App\Models\Actual::query()->delete();
                    if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_forecast_actuals')) {
                        \App\Models\ForecastActual::query()->delete();
                    }
                    if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_comparison_master')) {
                        \App\Models\ComparisonMaster::query()->delete();
                    }
                } else {
                    $items = PurchasingOutstanding::whereIn('id', $ids)->get(['id', 'part_number', 'drawing']);
                    $partNumbers = $items->pluck('part_number')->filter()->toArray();
                    $drawings = $items->pluck('drawing')->filter()->toArray();
                    $allKeys = array_unique(array_merge($partNumbers, $drawings));

                    foreach (array_chunk($ids, 500) as $chunkIds) {
                        PurchasingOutstanding::whereIn('id', $chunkIds)->delete();
                    }

                    if (!empty($allKeys)) {
                        foreach (array_chunk($allKeys, 500) as $chunkKeys) {
                            \App\Models\Forecasting::whereIn('part_number', $chunkKeys)->delete();
                            \App\Models\Outstanding::whereIn('part_number', $chunkKeys)->delete();
                            \App\Models\Actual::whereIn('part_number', $chunkKeys)->delete();
                            if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_forecast_actuals')) {
                                \App\Models\ForecastActual::whereIn('part_number', $chunkKeys)->delete();
                            }
                            if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_comparison_master')) {
                                \App\Models\ComparisonMaster::whereIn('part_number', $chunkKeys)->delete();
                            }
                        }
                    }
                }
            });

            session()->forget('import_duplicates_found');

            $msg = $deleteAll ? 'Seluruh data berhasil dibersihkan dari sistem.' : 'Data terpilih berhasil dihapus massal.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus seluruh data Purchasing Outstanding & Master Terkait (Reset Total).
     */
    public function destroyAll(Request $request)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('purchasing.outstanding');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function() {
                PurchasingOutstanding::query()->delete();
                \App\Models\Forecasting::query()->delete();
                \App\Models\Outstanding::query()->delete();
                \App\Models\Actual::query()->delete();
                if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_forecast_actuals')) {
                    \App\Models\ForecastActual::query()->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_comparison_master')) {
                    \App\Models\ComparisonMaster::query()->delete();
                }
            });

            session()->forget('import_duplicates_found');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Seluruh data Purchasing Outstanding (Step 1) berhasil dikosongkan.']);
            }
            return redirect()->route('purchasing.outstanding')->with('success', 'Seluruh data Purchasing Outstanding (Step 1) berhasil dikosongkan.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal mengosongkan data: ' . $e->getMessage());
        }
    }

    /**
     * Helper function untuk membersihkan angka dari format Excel
     * Contoh: "$ 200,32" -> 200.32, "(3.069,80)" -> -3069.80, "2756%" -> 2756
     */
    private function parseCleanNumber($val): float
    {
        if ($val === null || $val === '' || $val === '-') return 0.0;
        $str = trim((string)$val);
        
        // Tangani formula error Excel (#VALUE!, #REF!, dll) atau string formula mentah (=SUMIF, dll)
        if (str_starts_with($str, '#') || str_starts_with($str, '=')) {
            // Jika formula sederhana bernilai konstan, misal: "=150" atau "=+50.5"
            if (preg_match('/^=[+]?(-?\d+(?:\.\d+)?)$/', $str, $simpleMatch)) {
                return (float) $simpleMatch[1];
            }
            return 0.0;
        }
        
        $isNegative = false;
        if (preg_match('/^\((.*)\)$/', $str, $matches)) {
            $isNegative = true;
            $str = $matches[1];
        }
        
        $str = preg_replace('/[^\d.,-]/', '', $str);
        if (empty($str)) return 0.0;
        
        if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
            // Kedua separator ada — tentukan mana desimal dan mana ribuan
            if (strrpos($str, ',') > strrpos($str, '.')) {
                // Format Eropa: 1.234,56 → koma adalah desimal
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                // Format US: 1,234.56 → titik adalah desimal
                $str = str_replace(',', '', $str);
            }
        } elseif (strpos($str, ',') !== false) {
            // Hanya ada koma — deteksi apakah ini separator ribuan atau desimal
            $commaPos = strrpos($str, ',');
            $afterComma = substr($str, $commaPos + 1);
            // Jika tepat 3 digit setelah koma, kemungkinan besar separator ribuan (cth: 100,000)
            if (strlen($afterComma) === 3 && ctype_digit($afterComma)) {
                $str = str_replace(',', '', $str); // 100,000 → 100000
            } else {
                $str = str_replace(',', '.', $str); // 76,75 → 76.75
            }
        }

        $num = (float) $str;

        // Lindungi dari integer overflow di MySQL (SIGNED INT batas 2.147.483.647)
        if ($num > 2147483647) {
            $num = 2147483647;
        } elseif ($num < -2147483648) {
            $num = -2147483648;
        }

        return $isNegative ? -$num : $num;
    }

    /**
     * Import data KAWAI dari Excel / CSV (Flexible Fuzzy Mapper untuk LUTFI.xlsx & Format Lain up to 2000+ baris)
     * v2: Enhanced multi-row header parsing, AMOUNT column detection, Forecasting sync, auto-date sync
     */
    public function importExcel(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        \App\Services\DataValidation\DatabaseSchemaManager::ensureAllTablesIntegrity();

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        
        try {
            $realPath = $file->getRealPath();
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($realPath);
                if (method_exists($reader, 'setReadDataOnly')) {
                    $reader->setReadDataOnly(true);
                }
                $spreadsheet = $reader->load($realPath);
            } catch (\Throwable $e) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($realPath);
            }

            $allSheetNames = $spreadsheet->getSheetNames();
            $bestSheet = null;
            $bestSheetScore = -1;
            $bestRows = [];

            $itemKeywords = [
                'ITEM CODE', 'ITEM_CODE', 'ITEM', 'PART NUMBER', 'PART_NUMBER', 'PART NO', 'PN',
                'DRAWING', 'NO. BARANG', 'ITEM CODE (PK)', 'MATERIAL CODE', 'MATERIAL_CODE', 'MATERIAL',
                'KODE BARANG', 'KODE MATERIAL', 'KODE ITEM', 'KODE PART', 'KOMPONEN', 'SKU', 'CODE',
                // Header kolom RM (Raw Material) spesifik
                'KODE RM', 'NO RM', 'NO. RM', 'RM CODE', 'RM NUMBER', 'KODE RAW MATERIAL',
                'NO BARANG', 'NOMOR BARANG', 'KODE KOMPONEN',
            ];

            // Helper internal untuk mengurai nilai sel menjadi info bulan (Mendukung Serial Date Excel 46174 & String "JUN-26")
            $parseCellMonth = function($val) {
                if (empty($val)) return null;
                $valStr = trim((string)$val);
                
                // 1. Jika angka serial Excel (40000 - 55000 = tahun 2009 - 2050)
                if (is_numeric($valStr) && (float)$valStr >= 40000 && (float)$valStr <= 55000) {
                    $unix = ((float)$valStr - 25569) * 86400;
                    $mShort = strtoupper(date('M', (int)$unix));
                    $yr = date('Y', (int)$unix);
                    return [
                        'short' => $mShort,
                        'year'  => (int)$yr,
                        'code'  => $mShort . '-' . substr($yr, -2)
                    ];
                }

                $mNames = [1=>'JAN',2=>'FEB',3=>'MAR',4=>'APR',5=>'MAY',6=>'JUN',7=>'JUL',8=>'AUG',9=>'SEP',10=>'OCT',11=>'NOV',12=>'DEC'];
                $mIndoMap = ['MEI'=>'MAY','AGS'=>'AUG','OKT'=>'OCT','DES'=>'DEC','AGU'=>'AUG'];

                // 2. Format ISO YYYY-MM-DD atau YYYY-MM
                if (preg_match('/^(\d{4})[-\/](\d{1,2})([-\/]\d{1,2})?/', $valStr, $isoMatch)) {
                    $y = (int) $isoMatch[1];
                    $m = (int) $isoMatch[2];
                    if ($y >= 2020 && $y <= 2040 && $m >= 1 && $m <= 12) {
                        $mShort = $mNames[$m];
                        return [
                            'short' => $mShort,
                            'year'  => $y,
                            'code'  => $mShort . '-' . substr((string)$y, -2)
                        ];
                    }
                }

                // 3. Format DD/MM/YYYY atau DD-MM-YYYY
                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})/', $valStr, $dmyMatch)) {
                    $m = (int) $dmyMatch[2];
                    $y = (int) $dmyMatch[3];
                    if ($y >= 2020 && $y <= 2040 && $m >= 1 && $m <= 12) {
                        $mShort = $mNames[$m];
                        return [
                            'short' => $mShort,
                            'year'  => $y,
                            'code'  => $mShort . '-' . substr((string)$y, -2)
                        ];
                    }
                }

                // 4. Cari apakah ada 4 digit tahun (2020-2040)
                $foundYear = null;
                if (preg_match('/\b(20[23]\d)\b/', $valStr, $yMatch)) {
                    $foundYear = (int) $yMatch[1];
                }

                // Cari nama bulan (English & Indonesia)
                $monthPattern = '/(JANUARI|FEBRUARI|MARET|APRIL|MEI|JUNI|JULI|AGUSTUS|SEPTEMBER|OKTOBER|NOVEMBER|DESEMBER|JAN|FEB|MAR|APR|MAY|MEI|JUN|JUL|AUG|AGS|AGU|SEP|OCT|OKT|NOV|DEC|DES)/i';
                if (preg_match($monthPattern, strtoupper($valStr), $mMatch)) {
                    $mRaw = strtoupper(substr($mMatch[1], 0, 3));
                    if (isset($mIndoMap[$mRaw])) $mRaw = $mIndoMap[$mRaw];

                    if ($foundYear !== null) {
                        return [
                            'short' => $mRaw,
                            'year'  => $foundYear,
                            'code'  => $mRaw . '-' . substr((string)$foundYear, -2)
                        ];
                    }

                    // Jika tidak ada 4 digit tahun, cari 2 digit tahun yang masuk akal (20..39)
                    if (preg_match('/' . preg_quote($mMatch[1], '/') . '[\s\-]?(2\d|3\d)\b/i', $valStr, $y2Match)) {
                        $y = (int) ('20' . $y2Match[1]);
                        return [
                            'short' => $mRaw,
                            'year'  => $y,
                            'code'  => $mRaw . '-' . substr((string)$y, -2)
                        ];
                    }
                    if (preg_match('/\b(2\d|3\d)[\s\-]?' . preg_quote($mMatch[1], '/') . '/i', $valStr, $y2Match)) {
                        $y = (int) ('20' . $y2Match[1]);
                        return [
                            'short' => $mRaw,
                            'year'  => $y,
                            'code'  => $mRaw . '-' . substr((string)$y, -2)
                        ];
                    }
                }
                
                return null;
            };

            // ---------------------------------------------------------------
            // Sheet inspection: cari sheet terbaik dengan skor data material tertinggi.
            // Sheet cover/blank dilewati. Kategori dapat di-inherit jika nama sheet
            // mengindikasikan kategori (misal: "RM SYAHRUL" -> PUR-01).
            // ---------------------------------------------------------------
            foreach ($allSheetNames as $sheetName) {
                $candidateSheet = $spreadsheet->getSheetByName($sheetName);
                if (!$candidateSheet) continue;
                $candRows = $candidateSheet->toArray(null, false, true, true);
                if (empty($candRows)) continue;

                $sheetScore = 0;
                $dataRowsCount = 0;
                foreach ($candRows as $rIdx => $cRow) {
                    if ($rIdx > 35) {
                        $dataRowsCount++;
                        continue;
                    }
                    foreach ($cRow as $cVal) {
                        $cleanVal = strtoupper(trim((string)($cVal ?? '')));
                        if (!$cleanVal) continue;
                        foreach ($itemKeywords as $ikw) {
                            if ($cleanVal === $ikw || str_starts_with($cleanVal, $ikw) || str_contains($cleanVal, $ikw)) {
                                $sheetScore += 10;
                                break;
                            }
                        }
                        if (str_contains($cleanVal, 'SUPPLIER') || str_contains($cleanVal, 'MATERIAL') || str_contains($cleanVal, 'PLANT') || str_contains($cleanVal, 'KATEGORI') || str_contains($cleanVal, 'PUR-')) {
                            $sheetScore += 4;
                        }
                        if (str_contains($cleanVal, 'OUTSTANDING') || str_contains($cleanVal, 'STOCK') || str_contains($cleanVal, 'PO') || str_contains($cleanVal, 'PROD')) {
                            $sheetScore += 3;
                        }
                        if ($parseCellMonth($cVal) !== null) {
                            $sheetScore += 5;
                        }
                    }
                }
                $sheetScore += min($dataRowsCount, 50);

                if ($sheetScore > $bestSheetScore) {
                    $bestSheetScore = $sheetScore;
                    $bestSheet = $candidateSheet;
                }
            }

            // Helper: ambil rows dari sheet dengan fallback cached formula value
            $extractSheetRows = function(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws) use ($spreadsheet): array {
                // Coba hitung formula terlebih dahulu
                try {
                    $calcRows = $ws->toArray(null, true, false, true);
                    if (!empty($calcRows)) {
                        // Gantikan sel yang masih berupa formula string (=...) dengan OldCalculatedValue
                        foreach ($calcRows as $rIdx => $row) {
                            foreach ($row as $col => $cellVal) {
                                if (is_string($cellVal) && str_starts_with(trim($cellVal), '=')) {
                                    try {
                                        $cell = $ws->getCell($col . $rIdx);
                                        $old  = $cell->getOldCalculatedValue();
                                        if ($old !== null && !str_starts_with(trim((string)$old), '=')) {
                                            $calcRows[$rIdx][$col] = $old;
                                        }
                                    } catch (\Throwable $_) {}
                                }
                            }
                        }
                        return $calcRows;
                    }
                } catch (\Throwable $calcErr) {}

                // Fallback: baca tanpa kalkulasi, lalu ekstrak OldCalculatedValue untuk sel formula
                $rawRows = $ws->toArray(null, false, true, true);
                foreach ($rawRows as $rIdx => $row) {
                    foreach ($row as $col => $cellVal) {
                        if (is_string($cellVal) && str_starts_with(trim((string)$cellVal), '=')) {
                            try {
                                $cell = $ws->getCell($col . $rIdx);
                                $old  = $cell->getOldCalculatedValue();
                                if ($old !== null && !str_starts_with(trim((string)$old), '=')) {
                                    $rawRows[$rIdx][$col] = $old;
                                }
                            } catch (\Throwable $_) {}
                        }
                    }
                }
                return $rawRows;
            };

            // Selalu gunakan single best sheet dengan skor relevansi tertinggi ($bestSheet).
            // Dilarang menggabungkan multi-sheet secara buta karena sheet duplikat/revisi
            // (misal Sheet2 (2) vs Sheet2 (3)) memiliki struktur kolom berbeda yang menyebabkan
            // pergeseran kolom (deskripsi terbaca sebagai kode barang) dan duplikasi ratusan baris.
            $bestRows = [];
            $sheetDefaultCategory = null;
            if ($bestSheet) {
                $sheetTitle = $bestSheet->getTitle();
                $normalizedSheetCat = \App\Services\DataValidation\InputNormalizer::normalizeCategoryCode($sheetTitle);
                // Jika nama sheet mengindikasikan kategori (misal: RM, RM SYAHRUL, PUR-01), simpan sebagai fallback
                if (!empty($normalizedSheetCat) && !preg_match('/^SHEET\d*/i', trim($sheetTitle))) {
                    $sheetDefaultCategory = $normalizedSheetCat;
                }
                $bestRows = $extractSheetRows($bestSheet);
            }

            if (empty($bestRows)) {
                $bestSheet = $spreadsheet->getActiveSheet();
                $bestRows = $bestSheet ? $bestSheet->toArray(null, false, true, true) : [];
            }
            $rows = $bestRows;
            
            if (empty($rows)) {
                return redirect()->back()->with('error', 'File Excel kosong atau tidak terbaca.');
            }

            // ==============================================================
            // 1. DETEKSI BARIS HEADER UTAMA
            //    Cari baris yang memiliki 'ITEM CODE', 'PART NUMBER', 'MATERIAL CODE', dsb.
            //    Ini menandai awal dari blok multi-baris header.
            // ==============================================================
            $headerRowIdx = null;
            $bestScore = 0;

            foreach ($rows as $rIdx => $row) {
                if ($rIdx > 35) break;
                $rowScore = 0;
                foreach ($row as $col => $val) {
                    $cleanVal = strtoupper(trim((string)($val ?? '')));
                    if (!$cleanVal) continue;
                    foreach ($itemKeywords as $ikw) {
                        if ($cleanVal === $ikw || str_starts_with($cleanVal, $ikw) || str_contains($cleanVal, $ikw)) {
                            $rowScore += 4;
                            break;
                        }
                    }
                    if (str_contains($cleanVal, 'SUPPLIER') || str_contains($cleanVal, 'VENDOR') || str_contains($cleanVal, 'DESCRIPTION') || str_contains($cleanVal, 'PRICE') || str_contains($cleanVal, 'PO') || str_contains($cleanVal, 'STOCK') || str_contains($cleanVal, 'PLANT') || str_contains($cleanVal, 'KATEGORI')) {
                        $rowScore += 1;
                    }
                }
                if ($rowScore > $bestScore) {
                    $bestScore = $rowScore;
                    $headerRowIdx = $rIdx;
                }
            }

            if ($headerRowIdx === null) {
                $headerRowIdx = 1; // Fallback ke baris 1
            }

            // ==============================================================
            // 2. DETEKSI BLOK HEADER MULTI-BARIS (termasuk sub-header bulan)
            //    Enhanced: Mendukung template 3-4 baris header (baris bulan,
            //    baris group OUTSTANDING/STOCK/PO, baris QTY/AMOUNT).
            //    dataStartRowIdx = baris pertama data nyata (setelah header).
            // ==============================================================
            $dataStartRowIdx = $headerRowIdx + 1;
            $subHeaderKeywords = [
                'PO', 'PROD', 'PRODUKSI', 'STOCK', 'STOK', 'OUTSTANDING',
                'INVENTORY', 'INVENTORI', 'INV',
                'QTY', 'BQTY', 'B.QTY', 'B QTY',
                'FORECAST', 'TARGET', 'DELIVERY', 'RATIO',
                'NOTE', 'KETERANGAN', '%',
                'AMOUNT', 'AMT', 'JUMLAH',
                'DELIVERY DATE', 'CURRENCY', 'MATA UANG',
                'NO', 'PRICE', 'HARGA',
            ];
            
            for ($scanRow = $headerRowIdx + 1; $scanRow <= $headerRowIdx + 10; $scanRow++) {
                if (!isset($rows[$scanRow])) break;
                $rowData = $rows[$scanRow];
                
                $firstCells = array_values(array_filter(array_map('trim', $rowData)));
                if (empty($firstCells)) {
                    continue; // Skip blank lines between headers
                }
                
                $firstCellVal = strtoupper($firstCells[0]);
                
                // Hitung berapa sel pada baris ini yang cocok dengan sub-header keywords
                $matchCount = 0;
                $nonEmptyCount = 0;
                $textualMonthMatch = 0;
                $textMonthPattern = '/\b(JAN|FEB|MAR|APR|MAY|MEI|JUN|JUL|AUG|AGS|SEP|OCT|OKT|NOV|DEC|DES)[\s\-]?\d{2,4}\b/i';
                
                foreach ($rowData as $col => $val) {
                    $clean = strtoupper(trim((string)($val ?? '')));
                    if (!empty($clean)) {
                        $nonEmptyCount++;
                        if (in_array($clean, $subHeaderKeywords)) {
                            $matchCount++;
                        }
                        if (preg_match($textMonthPattern, $clean)) {
                            $textualMonthMatch++;
                        }
                    }
                }
                
                // Jika baris ini dominan subheader keyword atau baris header bulan lanjutan
                if ($textualMonthMatch > 0 || in_array($firstCellVal, $subHeaderKeywords) || ($nonEmptyCount > 0 && ($matchCount / $nonEmptyCount) >= 0.4)) {
                    $dataStartRowIdx = $scanRow + 1;
                    continue;
                }
                
                // Jika sudah menemukan baris data nyata, stop
                break;
            }

            // ==============================================================
            // 3. BANGUN COMBINED HEADERS dari SEMUA baris header block (mulai dari baris 1)
            // ==============================================================
            $combinedHeaders = [];
            $allHeaderCols = [];
            for ($hRow = 1; $hRow < $dataStartRowIdx; $hRow++) {
                if (isset($rows[$hRow])) {
                    $allHeaderCols = array_unique(array_merge($allHeaderCols, array_keys($rows[$hRow])));
                }
            }
            
            // Build per-row header texts for fine-grained QTY/AMOUNT detection
            $perRowHeaders = [];
            for ($hRow = 1; $hRow < $dataStartRowIdx; $hRow++) {
                if (isset($rows[$hRow])) {
                    foreach ($rows[$hRow] as $col => $val) {
                        $v = strtoupper(trim((string)($val ?? '')));
                        if (!empty($v)) {
                            $perRowHeaders[$hRow][$col] = $v;
                        }
                    }
                }
            }
            
            foreach ($allHeaderCols as $col) {
                $parts = [];
                for ($hRow = 1; $hRow < $dataStartRowIdx; $hRow++) {
                    if (isset($rows[$hRow][$col])) {
                        $v = strtoupper(trim($rows[$hRow][$col]));
                        if ($v && !in_array($v, $parts)) {
                            $parts[] = $v;
                        }
                    }
                }
                $combinedHeaders[$col] = implode(' ', $parts);
            }

            // ==============================================================
            // 4. DETEKSI BULAN LANGSUNG DARI SEMUA BARIS HEADER (Baris 1 s/d dataStartRowIdx-1)
            // ==============================================================
            $monthBlocks = [];
            $currentMonthStr = null;
            $currentMShort   = null;
            $currentYrDigits = null;
            $monthIndexCount = -1;
            $monthColsScanned = []; // col -> monthIndex

            for ($hRow = 1; $hRow < $dataStartRowIdx; $hRow++) {
                if (!isset($rows[$hRow])) continue;
                foreach ($rows[$hRow] as $col => $val) {
                    $mInfo = $parseCellMonth($val);
                    if ($mInfo) {
                        if ($mInfo['code'] !== $currentMonthStr) {
                            $currentMonthStr = $mInfo['code'];
                            $currentMShort   = $mInfo['short'];
                            $currentYrDigits = $mInfo['year'];
                            $monthIndexCount++;
                            
                            if (!isset($monthBlocks[$monthIndexCount])) {
                                $monthNumMap = ['JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MAY'=>5,'JUN'=>6,'JUL'=>7,'AUG'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12];
                                $mNum = $monthNumMap[$currentMShort] ?? 1;
                                $periodYYYYMM = $currentYrDigits . '-' . str_pad($mNum, 2, '0', STR_PAD_LEFT);
                                
                                $monthBlocks[$monthIndexCount] = [
                                    'name'               => $currentMonthStr,
                                    'year'               => (int) $currentYrDigits,
                                    'shortMonth'         => $currentMShort,
                                    'periodYYYYMM'       => $periodYYYYMM,
                                    'startCol'           => $col,
                                    'poCol'              => null,
                                    'poAmountCol'        => null,
                                    'prodCol'            => null,
                                    'stockCol'           => null,
                                    'stockAmountCol'     => null,
                                    'inventoryCol'       => null,
                                    'inventoryAmountCol' => null,
                                    'outstandCol'        => null,
                                    'outstandAmountCol'  => null,
                                    'forecastCol'        => null,
                                    'forecastAmountCol'  => null,
                                    'deliveryCol'        => null,
                                    'deliveryAmountCol'  => null,
                                ];
                            }
                        }
                        
                        if ($monthIndexCount >= 0 && !isset($monthColsScanned[$col])) {
                            $monthColsScanned[$col] = $monthIndexCount;
                        }
                    }
                }
            }
            
            $colToNum = fn($c) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($c);
            $numToCol = fn($i) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);

            $monthStarts = [];
            foreach ($monthBlocks as $mIdx => $mBlock) {
                $monthStarts[$mIdx] = $mBlock['startCol'];
            }
            
            // Helper: Tentukan month index untuk kolom tertentu
            $getMonthIdxForCol = function($col) use ($monthColsScanned, $monthStarts, $colToNum) {
                if (isset($monthColsScanned[$col])) {
                    return $monthColsScanned[$col];
                }
                $bestMonthIdx = null;
                $bestMonthColNum = null;
                $cNum = $colToNum($col);
                foreach ($monthStarts as $mIdx => $mStartCol) {
                    $mStartNum = $colToNum($mStartCol);
                    if ($mStartNum <= $cNum) {
                        if ($bestMonthColNum === null || $mStartNum > $bestMonthColNum) {
                            $bestMonthColNum = $mStartNum;
                            $bestMonthIdx = $mIdx;
                        }
                    }
                }
                return $bestMonthIdx;
            };
            
            // Pass 1: Detect group headers (OUTSTANDING, STOCK, INVENTORY, PO, FORECAST, DELIVERY, PROD)
            $colGroupMap = [];
            $lastGroupName = null;
            
            for ($hRow = 1; $hRow < $dataStartRowIdx; $hRow++) {
                if (!isset($rows[$hRow])) continue;
                foreach ($rows[$hRow] as $col => $val) {
                    $cellVal = strtoupper(trim((string)($val ?? '')));
                    if (empty($cellVal)) continue;
                    
                    $assignedMonthIdx = $getMonthIdxForCol($col);
                    if ($assignedMonthIdx === null || !isset($monthBlocks[$assignedMonthIdx])) continue;
                    
                    if (preg_match('/^INVENTORY$|\bINVENTORY\b|\bINVENTORI\b|\bINV\b/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['inventoryCol']) $monthBlocks[$assignedMonthIdx]['inventoryCol'] = $col;
                        $colGroupMap[$col] = 'INVENTORY';
                    } elseif (preg_match('/^PO$|\bPO\b/i', $cellVal) && !preg_match('/OUTSTANDING|STOCK|FORECAST|INVENTORY/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['poCol']) $monthBlocks[$assignedMonthIdx]['poCol'] = $col;
                        $colGroupMap[$col] = 'PO';
                    } elseif (preg_match('/^PROD$|\bPROD\b|PRODUKSI/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['prodCol']) $monthBlocks[$assignedMonthIdx]['prodCol'] = $col;
                        $colGroupMap[$col] = 'PROD';
                    } elseif (preg_match('/^STOCK$|\bSTOCK\b|\bSTOK\b/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['stockCol']) $monthBlocks[$assignedMonthIdx]['stockCol'] = $col;
                        $colGroupMap[$col] = 'STOCK';
                    } elseif (preg_match('/OUTSTANDING/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['outstandCol']) $monthBlocks[$assignedMonthIdx]['outstandCol'] = $col;
                        $colGroupMap[$col] = 'OUTSTANDING';
                    } elseif (preg_match('/FORECAST|TARGET|\bFC\b|F\/C|\bFCST\b|\bPLAN\b/i', $cellVal) && !preg_match('/FACTORY|PABRIK|PLANT/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['forecastCol']) $monthBlocks[$assignedMonthIdx]['forecastCol'] = $col;
                        $colGroupMap[$col] = 'FORECAST';
                    } elseif (preg_match('/DELIVERY|DELIVERI/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['deliveryCol']) $monthBlocks[$assignedMonthIdx]['deliveryCol'] = $col;
                        $colGroupMap[$col] = 'DELIVERY';
                    }
                }
            }
            
            // Pass 2: Detect QTY and AMOUNT sub-columns
            for ($hRow = 1; $hRow < $dataStartRowIdx; $hRow++) {
                if (!isset($rows[$hRow])) continue;
                foreach ($rows[$hRow] as $col => $val) {
                    $cellVal = strtoupper(trim((string)($val ?? '')));
                    if (empty($cellVal)) continue;
                    
                    $isQty = in_array($cellVal, ['QTY', 'BQTY', 'B.QTY', 'B QTY', 'UNIT', 'JML', 'JUMLAH QTY']);
                    $isAmount = in_array($cellVal, ['AMOUNT', 'AMT', 'JUMLAH', 'TOTAL', 'NILAI']);
                    
                    if (!$isQty && !$isAmount) continue;
                    
                    $assignedMonthIdx = $getMonthIdxForCol($col);
                    if ($assignedMonthIdx === null || !isset($monthBlocks[$assignedMonthIdx])) continue;
                    
                    $parentGroup = null;
                    
                    // Check same column in previous rows
                    for ($checkRow = $hRow - 1; $checkRow >= 1; $checkRow--) {
                        if (isset($colGroupMap[$col])) {
                            $parentGroup = $colGroupMap[$col];
                            break;
                        }
                        if (isset($perRowHeaders[$checkRow][$col])) {
                            $aboveVal = $perRowHeaders[$checkRow][$col];
                            if (preg_match('/INVENTORY|INVENTORI|\bINV\b/i', $aboveVal)) { $parentGroup = 'INVENTORY'; break; }
                            if (preg_match('/OUTSTANDING/i', $aboveVal)) { $parentGroup = 'OUTSTANDING'; break; }
                            if (preg_match('/^STOCK$|\bSTOCK\b|\bSTOK\b/i', $aboveVal)) { $parentGroup = 'STOCK'; break; }
                            if (preg_match('/^PO$|\bPO\b/i', $aboveVal) && !preg_match('/OUTSTANDING|STOCK|FORECAST|INVENTORY/i', $aboveVal)) { $parentGroup = 'PO'; break; }
                            if (preg_match('/FORECAST|TARGET|\bFC\b|F\/C|\bFCST\b|\bPLAN\b/i', $aboveVal) && !preg_match('/FACTORY|PABRIK|PLANT/i', $aboveVal)) { $parentGroup = 'FORECAST'; break; }
                            if (preg_match('/DELIVERY|DELIVERI/i', $aboveVal)) { $parentGroup = 'DELIVERY'; break; }
                            if (preg_match('/^PROD$|\bPROD\b|PRODUKSI/i', $aboveVal)) { $parentGroup = 'PROD'; break; }
                        }
                    }
                    
                    // Search nearest group to the left in same month
                    if (!$parentGroup) {
                        $nearestGroupCol = null;
                        $nearestGroupName = null;
                        foreach ($colGroupMap as $gCol => $gName) {
                            $gMonthIdx = $getMonthIdxForCol($gCol);
                            if ($gMonthIdx === $assignedMonthIdx && $colToNum($gCol) <= $colToNum($col)) {
                                if ($nearestGroupCol === null || $colToNum($gCol) > $colToNum($nearestGroupCol)) {
                                    $nearestGroupCol = $gCol;
                                    $nearestGroupName = $gName;
                                }
                            }
                        }
                        $parentGroup = $nearestGroupName;
                    }
                    
                    if (!$parentGroup) continue;
                    
                    $mb = &$monthBlocks[$assignedMonthIdx];
                    
                    if ($isQty) {
                        match ($parentGroup) {
                            'INVENTORY'   => $mb['inventoryCol'] = $mb['inventoryCol'] ?? $col,
                            'PO'          => $mb['poCol'] = $mb['poCol'] ?? $col,
                            'OUTSTANDING' => $mb['outstandCol'] = $mb['outstandCol'] ?? $col,
                            'STOCK'       => $mb['stockCol'] = $mb['stockCol'] ?? $col,
                            'FORECAST'    => $mb['forecastCol'] = $mb['forecastCol'] ?? $col,
                            'DELIVERY'    => $mb['deliveryCol'] = $mb['deliveryCol'] ?? $col,
                            'PROD'        => $mb['prodCol'] = $mb['prodCol'] ?? $col,
                            default       => null,
                        };
                    } elseif ($isAmount) {
                        match ($parentGroup) {
                            'INVENTORY'   => $mb['inventoryAmountCol'] = $mb['inventoryAmountCol'] ?? $col,
                            'PO'          => $mb['poAmountCol'] = $mb['poAmountCol'] ?? $col,
                            'OUTSTANDING' => $mb['outstandAmountCol'] = $mb['outstandAmountCol'] ?? $col,
                            'STOCK'       => $mb['stockAmountCol'] = $mb['stockAmountCol'] ?? $col,
                            'FORECAST'    => $mb['forecastAmountCol'] = $mb['forecastAmountCol'] ?? $col,
                            'DELIVERY'    => $mb['deliveryAmountCol'] = $mb['deliveryAmountCol'] ?? $col,
                            default       => null,
                        };
                    }
                    
                    unset($mb);
                }
            }
            
            // Pass 3: Check next column for AMOUNT
            foreach ($monthBlocks as $mIdx => &$mb) {
                $groups = [
                    'po'        => 'poCol',
                    'outstand'  => 'outstandCol',
                    'stock'     => 'stockCol',
                    'inventory' => 'inventoryCol',
                    'forecast'  => 'forecastCol',
                    'delivery'  => 'deliveryCol',
                ];
                foreach ($groups as $prefix => $qtyKey) {
                    $amtKey = $prefix . 'AmountCol';
                    if ($mb[$qtyKey] && !$mb[$amtKey]) {
                        $nextCol = $numToCol($colToNum($mb[$qtyKey]) + 1);
                        if (isset($combinedHeaders[$nextCol])) {
                            $nextHt = strtoupper($combinedHeaders[$nextCol]);
                            if (preg_match('/\bAMOUNT\b|\bAMT\b|\bJUMLAH\b|\bNILAI\b/i', $nextHt)) {
                                $mb[$amtKey] = $nextCol;
                            }
                        }
                    }
                }
            }
            unset($mb);

            // ==============================================================
            // 5. DETEKSI KOLOM FIELD (ITEM CODE, DESC, SUPPLIER, PRICE, dll.)
            // ==============================================================
            $itemCodeCol = null;
            $factoryCodeCol = null;
            $categoryCol = null;
            $descCol = null;
            $suppCol = null;
            $suppCodeCol = null;
            $typeCol = null;
            $priceCol = null;
            $currencyCol = null;
            $outstandCol = null;
            $stockCol = null;
            $poCol = null;
            $forecastCol = null;
            $prodCol = null;
            $outstandQtyCol = null;
            $stockQtyCol    = null;
            $poQtyCol       = null;
            $forecastQtyCol = null;

            foreach ($combinedHeaders as $col => $headerText) {
                $ht = strtoupper(trim($headerText));

                $leftmostMonthCol = !empty($monthStarts) ? $numToCol(min(array_map($colToNum, $monthStarts))) : 'XFD';
                $isFieldCol = empty($monthBlocks) || empty($monthStarts) || ($colToNum($col) < $colToNum($leftmostMonthCol));

                if ($isFieldCol) {
                    if (!$suppCodeCol && preg_match('/(SUPPLIER[\s_]?CODE|KODE[\s_]?SUPPLIER|VENDOR[\s_]?CODE|KODE[\s_]?VENDOR|VEND[\s_]?CODE|SUPP[\s_]?CODE|KD[\s_\.]?SUPP|KD[\s_\.]?VENDOR|KD[\s_\.]?SUPPLIER)/i', $ht)) {
                        $suppCodeCol = $col;
                    } elseif (!$suppCol && preg_match('/(SUPPLIER[\s_]?NAME|NAMA[\s_]?SUPPLIER|NAMA[\s_]?VENDOR|VENDOR[\s_]?NAME|\bSUPPLIER\b|\bVENDOR\b|TRADE[\s_]?NAME|\bSUPP\b)/i', $ht) && !preg_match('/(CODE|KODE|VEND_CODE|SUPP_CODE)/i', $ht)) {
                        $suppCol = $col;
                    } elseif (!$factoryCodeCol && preg_match('/(FACTORY[\s_]?CODE|KODE[\s_]?PABRIK|\bFACTORY\b|\bPLANT\b|\bPABRIK\b)/i', $ht)) {
                        $factoryCodeCol = $col;
                    } elseif (!$categoryCol && preg_match('/(KATEGORI|CATEGORY|PURCHASING[\s_]?CAT|KODE[\s_]?KATEGORI|\bKAT\b|JENIS[\s_]?MATERIAL|\bPUR[\s\-_]?\d{2}\b)/i', $ht)) {
                        $categoryCol = $col;
                    } elseif (!$itemCodeCol && !preg_match('/(SUPPLIER|VENDOR|FACTORY|PLANT|PABRIK|KATEGORI|CATEGORY|PUR[\s\-_]?\d|DESC|DESCRIP|NAMA|NAME|KET|KETERANGAN)/i', $ht) && preg_match('/(ITEM[\s_]?CODE|MATERIAL[\s_]?CODE|PART[\s_]?NUMBER|PART[\s_]?NO|ITEM[\s_]?NO|NO[\s_\.]?PART|NO[\s_\.]?ITEM|NO[\s_\.]?BARANG|NO[\s_\.]?MATERIAL|KODE[\s_]?BARANG|KODE[\s_]?MATERIAL|KODE[\s_]?ITEM|KODE[\s_]?PART|KODE[\s_]?RM|NO[\s_\.]?RM|RM[\s_]?CODE|RM[\s_]?NUMBER|RAW[\s_]?MATERIAL[\s_]?CODE|\bPN\b|\bP\/N\b|\bDRAWING\b|\bDWG\b|\bKOMPONEN\b|\bSKU\b|PART[\s_]?#|ITEM[\s_]?#|MAT[\s_]?#|MAT[\s_]?CODE|\bITEM\b|\bPART\b|\bPARTS\b|\bMATERIAL\b)/i', $ht)) {
                        $itemCodeCol = $col;
                    } elseif (!$descCol && !preg_match('/(KATEGORI|CATEGORY|PUR[\s\-_]?\d|SUPPLIER|VENDOR|FACTORY|PLANT|PABRIK|ITEM[\s_]?CODE|MATERIAL[\s_]?CODE|PART[\s_]?NUMBER|PART[\s_]?NO|KODE[\s_]?BARANG|KODE[\s_]?MATERIAL|KODE[\s_]?PART|KODE[\s_]?RM|\bPN\b|\bP\/N\b)/i', $ht) && preg_match('/(DECRIPTION|DESCRIPTION|DESKRIPSI|NAMA[\s_]?BARANG|ITEM[\s_]?NAME|MATERIAL[\s_]?NAME|PART[\s_]?NAME|PRODUCT[\s_]?NAME|NAMA[\s_]?PRODUK|NAMA[\s_]?ITEM|NAMA[\s_]?PART|NAMA[\s_]?MATERIAL|ITEM[\s_]?DESCRIPTION|MATERIAL[\s_]?DESCRIPTION|PART[\s_]?DESCRIPTION|\bDESC\b|\bDESCR\b|KETERANGAN|\bKET\b|SPESIFIKASI|SPECIFICATION|\bSPEC\b|\bSPECS\b|UKURAN|\bSIZE\b)/i', $ht)) {
                        $descCol = $col;
                    } elseif (!$typeCol && preg_match('/\bTYPE\b|\bTIPE\b|\bMODEL\b|\bSPEC\b/i', $ht)) {
                        $typeCol = $col;
                    } elseif (!$priceCol && preg_match('/(UNIT[\s_]?PRICE|HARGA[\s_]?SATUAN|\bPRICE\b|\bHARGA\b)/i', $ht)) {
                        $priceCol = $col;
                    } elseif (!$currencyCol && preg_match('/(CURRENCY|MATA[\s_]?UANG|\bKURS\b|\bKU\b)/i', $ht)) {
                        $currencyCol = $col;
                    } elseif (preg_match('/OUTSTANDING/i', $ht)) {
                        if (preg_match('/\bQTY\b|\bUNIT\b|\bJML\b/i', $ht)) {
                            if (!$outstandQtyCol) $outstandQtyCol = $col;
                        }
                        if (!$outstandCol) $outstandCol = $col;
                    } elseif (preg_match('/\bSTOCK\b|\bSTOK\b/i', $ht)) {
                        if (preg_match('/\bQTY\b|\bUNIT\b|\bJML\b/i', $ht)) {
                            if (!$stockQtyCol) $stockQtyCol = $col;
                        }
                        if (!$stockCol) $stockCol = $col;
                    } elseif (preg_match('/\bFORECAST\b|\bTARGET\b/i', $ht)) {
                        if (preg_match('/\bQTY\b|\bUNIT\b|\bJML\b/i', $ht)) {
                            if (!$forecastQtyCol) $forecastQtyCol = $col;
                        }
                        if (!$forecastCol) $forecastCol = $col;
                    } elseif (preg_match('/\bPO\b|P\.O\.|\bPURCHASE[\s_]ORDER\b|\bORDER\b/i', $ht) && !preg_match('/OUTSTANDING|STOCK|FORECAST/i', $ht)) {
                        if (preg_match('/\bQTY\b|\bUNIT\b|\bJML\b/i', $ht)) {
                            if (!$poQtyCol) $poQtyCol = $col;
                        }
                        if (!$poCol) $poCol = $col;
                    } elseif (!$prodCol && preg_match('/\bPROD\b|PRODUKSI/i', $ht)) {
                        $prodCol = $col;
                    }
                }
            }

            if ($outstandQtyCol) $outstandCol = $outstandQtyCol;
            if ($stockQtyCol)    $stockCol    = $stockQtyCol;
            if ($forecastQtyCol) $forecastCol = $forecastQtyCol;
            if ($poQtyCol)       $poCol       = $poQtyCol;

            // Safe positional fallbacks that NEVER collide with suppCodeCol, suppCol, factoryCodeCol, or categoryCol
            $excludedCols = array_filter([$suppCodeCol, $suppCol, $factoryCodeCol, $categoryCol, $priceCol, $currencyCol]);
            if ($descCol) {
                $excludedCols[] = $descCol;
            }

            if (!$itemCodeCol) {
                foreach ($allHeaderCols as $c) {
                    if ($colToNum($c) < $colToNum($leftmostMonthCol) && !in_array($c, $excludedCols, true)) {
                        $itemCodeCol = $c;
                        $excludedCols[] = $c;
                        break;
                    }
                }
                if (!$itemCodeCol) {
                    foreach (['E', 'F', 'B', 'C', 'D'] as $c) {
                        if (!in_array($c, $excludedCols, true)) {
                            $itemCodeCol = $c;
                            $excludedCols[] = $c;
                            break;
                        }
                    }
                }
            }

            if (!$descCol) {
                foreach ($allHeaderCols as $c) {
                    if ($colToNum($c) < $colToNum($leftmostMonthCol) && !in_array($c, $excludedCols, true) && $c !== $itemCodeCol) {
                        $descCol = $c;
                        $excludedCols[] = $c;
                        break;
                    }
                }
                if (!$descCol) {
                    foreach (['F', 'E', 'D', 'C'] as $c) {
                        if (!in_array($c, $excludedCols, true) && $c !== $itemCodeCol) {
                            $descCol = $c;
                            break;
                        }
                    }
                }
            }

            if (!$factoryCodeCol) $factoryCodeCol = 'D';
            if (!$priceCol)       $priceCol       = 'F';
            if (!$currencyCol)    $currencyCol    = 'G';

            // ==============================================================
            // 6. PROSES DATA ROWS
            // ==============================================================
            $importCount = 0;
            $updateCount = 0;
            $forecastSyncCount = 0;
            $dataRows = array_filter($rows, fn($rIdx) => $rIdx >= $dataStartRowIdx, ARRAY_FILTER_USE_KEY);
            $allCategories = \App\Models\PurchasingCategory::all();
            $firstCategory = $allCategories->where('status', 'Active')->first() ?? $allCategories->first();
            $defaultCatId = $firstCategory ? $firstCategory->id : 1;

            \Illuminate\Support\Facades\DB::transaction(function() use ($request, $dataRows, $dataStartRowIdx, $allHeaderCols, $colToNum, $leftmostMonthCol, $itemCodeCol, $factoryCodeCol, $categoryCol, $descCol, $suppCol, $suppCodeCol, $typeCol, $priceCol, $currencyCol, $outstandCol, $stockCol, $poCol, $forecastCol, $prodCol, $monthBlocks, &$allCategories, $defaultCatId, $parseCellMonth, &$importCount, &$updateCount, &$forecastSyncCount, $sheetDefaultCategory) {
                $skipKeywords = ['ITEM CODE', 'ITEM', 'PN', 'PART NUMBER', 'PART NO', 'TOTAL', 'GRAND TOTAL', 'NO', 'ITEM CODE (PK)', 'KATEGORI', 'DESCRIPTION & SUPPLIER', 'DESCRIPTION', 'DESKRIPSI', 'KETERANGAN', 'NOTE', 'SUB TOTAL', 'SUBTOTAL'];
                $isCompanyName = function(?string $text): bool {
                    if (empty($text)) return false;
                    $clean = strtoupper(trim($text));
                    if (preg_match('/^(PT\b|PT\.|CV\b|CV\.|UD\b|UD\.|FA\b|FA\.|TBK\b|INC\b|LTD\b|CORP\b)/i', $clean)) return true;
                    $keywords = ['SEJAHTERA', 'INDONESIA', 'NIAGA', 'BIMASAKTI', 'ANEKA', 'SUMBER', 'AGUNG', 'JAYA', 'ABADI', 'SUKSES', 'PERSERO', 'MAKMUR', 'SENTOSA', 'UTAMA', 'KARYA', 'MANDIRI'];
                    foreach ($keywords as $kw) { if (str_contains($clean, $kw)) return true; }
                    return false;
                };

                $isVendorCode = function(?string $text): bool {
                    if (empty($text)) return false;
                    return (bool) preg_match('/^C\d{3,4}$/i', trim($text));
                };

                $isCategoryCode = function(?string $text): bool {
                    if (empty($text)) return false;
                    $t = strtoupper(trim($text));
                    // Cocokkan PUR-01 .. PUR-99
                    if (preg_match('/^PUR[\s\-_]?0?[1-9]\d*$/i', $t)) return true;
                    // Cocokkan nama/alias kategori RM yang umum digunakan di PT Kawai
                    $rmAliases = [
                        'RM', 'RM KAYU', 'RM-KAYU', 'RM LOGAM', 'RM-LOGAM',
                        'RM BESI', 'RM BAJA', 'RM STEEL', 'RM SYAHRUL',
                        'RAW MATERIAL', 'RAW MATERIALS', 'BAHAN BAKU',
                        'PACKING', 'PACKAGING', 'KOMPONEN PACKING',
                        'CONSUMABLE', 'CONSUMABLE TOOL', 'TOOL', 'TOOLS',
                        'LOGAM', 'KAYU', 'WOOD', 'METAL', 'BESI', 'BAJA', 'STEEL',
                    ];
                    return in_array($t, $rmAliases, true);
                };

                $isPlantCode = function(?string $text): bool {
                    if (empty($text)) return false;
                    return (bool) preg_match('/^(KIP|PLANT|PABRIK)\s*[1-4]?$/i', trim($text));
                };

                $codeOccurrences = [];
                $forecastingBatch = [];

                foreach ($dataRows as $rIdx => $row) {
                    $rawCode = trim((string)($row[$itemCodeCol] ?? ''));
                    $rawDesc = trim((string)($row[$descCol] ?? ''));
                    $rawSupp = trim((string)($row[$suppCol] ?? ''));
                    $rawSuppCode = $suppCodeCol ? trim((string)($row[$suppCodeCol] ?? '')) : '';
                    $rawCategory = $categoryCol ? trim((string)($row[$categoryCol] ?? '')) : '';
                    // Jika tidak ada kolom kategori eksplisit dan nama sheet mengindikasikan kategori (misal RM SYAHRUL -> PUR-01), gunakan fallback
                    if (empty($rawCategory) && !empty($sheetDefaultCategory)) {
                        $rawCategory = $sheetDefaultCategory;
                    }
                    $rawFactory = ($factoryCodeCol && !empty(trim((string)($row[$factoryCodeCol] ?? '')))) ? strtoupper(trim((string)$row[$factoryCodeCol])) : '';

                    // Step A: Supplier Disambiguation
                    if ($isCompanyName($rawCode)) {
                        $suppVal = $rawCode;
                        $rawCode = '';
                    } elseif ($isCompanyName($rawSupp)) {
                        $suppVal = $rawSupp;
                    } else {
                        $suppVal = $rawSupp ?: $rawSuppCode;
                    }

                    if ($isVendorCode($rawSupp) && empty($rawSuppCode)) {
                        $rawSuppCode = $rawSupp;
                        if (!$isCompanyName($suppVal)) $suppVal = '';
                    }

                    // Step B: Content-Aware Relocation
                    // If rawCode was matched to a vendor code (C017) or category code (PUR-03)
                    if ($isVendorCode($rawCode)) {
                        if (empty($rawSuppCode)) $rawSuppCode = $rawCode;
                        $rawCode = '';
                    } elseif ($isCategoryCode($rawCode)) {
                        if (empty($rawCategory)) $rawCategory = $rawCode;
                        $rawCode = '';
                    }

                    // If rawDesc was matched to a category code (PUR-03) or vendor code (C017) or plant
                    if ($isCategoryCode($rawDesc)) {
                        if (empty($rawCategory)) $rawCategory = $rawDesc;
                        $rawDesc = '';
                    } elseif ($isVendorCode($rawDesc)) {
                        if (empty($rawSuppCode)) $rawSuppCode = $rawDesc;
                        $rawDesc = '';
                    } elseif ($isPlantCode($rawDesc)) {
                        if (empty($rawFactory)) $rawFactory = $rawDesc;
                        $rawDesc = '';
                    }

                    // Step C: Scan row cells if rawCode or rawDesc are missing
                    if (empty($rawCode) || empty($rawDesc)) {
                        foreach ($allHeaderCols as $cKey) {
                            if ($colToNum($cKey) >= $colToNum($leftmostMonthCol)) continue;
                            $strVal = trim((string)($row[$cKey] ?? ''));
                            if ($strVal === '' || in_array(strtoupper($strVal), $skipKeywords, true)) continue;

                            if ($isCompanyName($strVal) && empty($suppVal)) {
                                $suppVal = $strVal;
                                continue;
                            }
                            if ($isVendorCode($strVal) && empty($rawSuppCode)) {
                                $rawSuppCode = $strVal;
                                continue;
                            }
                            if ($isCategoryCode($strVal) && empty($rawCategory)) {
                                $rawCategory = $strVal;
                                continue;
                            }
                            if ($isPlantCode($strVal) && empty($rawFactory)) {
                                $rawFactory = $strVal;
                                continue;
                            }

                            // Candidate for Item Code
                            // RM item codes (misal: WOOD-SPRUCE-A, BALOK-PINE-2X4, M50601) boleh tidak mengandung digit tapi berstruktur kode
                            if (empty($rawCode) && !$isCompanyName($strVal) && !$isVendorCode($strVal) && !$isCategoryCode($strVal) && !$isPlantCode($strVal)) {
                                $isNumericCode = is_numeric($strVal) && strlen($strVal) >= 4 && (int)$strVal > 1000 && !str_contains($strVal, '.');
                                $hasCodeStructure = preg_match('/[0-9]/', $strVal) || preg_match('/[\-\_\/]/', $strVal) || !str_contains($strVal, ' ');
                                $isAlphanumCode = strlen($strVal) <= 40 && !str_contains($strVal, '  ') && !is_numeric($strVal)
                                    && preg_match('/^[A-Z0-9][A-Z0-9\-\_\/\.\s]{1,39}$/i', $strVal)
                                    && !preg_match('/^(TOTAL|SUBTOTAL|GRAND|NOTE|KETERANGAN|DESCRIPTION|DESKRIPSI)\b/i', $strVal)
                                    && $hasCodeStructure
                                    && substr_count($strVal, ' ') <= 1;
                                if ($isNumericCode || $isAlphanumCode) {
                                    $rawCode = $strVal;
                                    continue;
                                }
                            }

                            // Candidate for Description
                            if (empty($rawDesc) && $strVal !== $rawCode && !$isCompanyName($strVal) && !$isVendorCode($strVal) && !$isCategoryCode($strVal) && !$isPlantCode($strVal)) {
                                if (preg_match('/[a-zA-Z]/', $strVal) && !is_numeric($strVal)) {
                                    $rawDesc = $strVal;
                                    continue;
                                }
                            }
                        }
                    }

                    $itemCodeVal = !empty($rawCode) ? $rawCode : (!empty($rawSuppCode) && !$isVendorCode($rawSuppCode) ? $rawSuppCode : $rawDesc);
                    if (empty($itemCodeVal) && !empty($rawSuppCode)) {
                        $itemCodeVal = $rawSuppCode;
                    }
                    if (empty($itemCodeVal)) continue;

                    $itemCodeUpper = strtoupper($itemCodeVal);
                    if (in_array($itemCodeUpper, $skipKeywords) || $parseCellMonth($itemCodeVal) !== null) continue;
                    
                    $itemCodeClean = strtoupper($itemCodeVal);

                    $factoryVal = 'KIP 1';
                    if (in_array($rawFactory, ['KIP 1', 'KIP1', 'KIP', 'PLANT 1', 'PLANT1', 'PABRIK 1', 'P1'])) {
                        $factoryVal = 'KIP 1';
                    } elseif (in_array($rawFactory, ['KIP 2', 'KIP2', 'KIK', 'PLANT 2', 'PLANT2', 'PABRIK 2', 'P2'])) {
                        $factoryVal = 'KIP 2';
                    } elseif (in_array($rawFactory, ['KIP 3', 'KIP3', 'PLANT 3', 'PLANT3', 'PABRIK 3', 'P3'])) {
                        $factoryVal = 'KIP 3';
                    } elseif (in_array($rawFactory, ['KIP 4', 'KIP4', 'PLANT 4', 'PLANT4', 'PABRIK 4', 'P4'])) {
                        $factoryVal = 'KIP 4';
                    } else {
                        if (!empty($rawFactory) && $isCompanyName($rawFactory)) $suppVal = trim($row[$factoryCodeCol]);
                        $factoryVal = 'KIP 1';
                    }
                    
                    $descVal = !empty($rawDesc) ? $rawDesc : ($itemCodeClean !== $rawCode && !empty($rawCode) ? $rawCode : $itemCodeClean);
                    $typeVal = $typeCol ? trim((string)($row[$typeCol] ?? '')) : '';
                    $fullDesc = $typeVal ? ($descVal . ' (' . $typeVal . ')') : $descVal;
                    $suppClean = strtoupper(trim($suppVal ?? ''));
                    $compositeKey = $itemCodeClean . '___' . $factoryVal . '___' . $suppClean;
                    $codeOccurrences[$compositeKey][] = ['row' => $rIdx, 'desc' => $fullDesc];

                    $outstandVal = ($outstandCol && isset($row[$outstandCol])) ? (int) $this->parseCleanNumber($row[$outstandCol]) : 0;
                    $stockVal = ($stockCol && isset($row[$stockCol])) ? (int) $this->parseCleanNumber($row[$stockCol]) : 0;

                    // Fallback to Month 0 (Pre-Month) columns if standalone columns were 0 or missing
                    if ($outstandVal === 0 && isset($monthBlocks[0]['outstandCol']) && isset($row[$monthBlocks[0]['outstandCol']])) {
                        $outstandVal = (int) $this->parseCleanNumber($row[$monthBlocks[0]['outstandCol']]);
                    }
                    if ($stockVal === 0 && isset($monthBlocks[0]['stockCol']) && isset($row[$monthBlocks[0]['stockCol']])) {
                        $stockVal = (int) $this->parseCleanNumber($row[$monthBlocks[0]['stockCol']]);
                    }

                    $priceVal = $priceCol ? (float) $this->parseCleanNumber($row[$priceCol] ?? 0) : 0.0;
                    $poVal = $poCol ? (int) $this->parseCleanNumber($row[$poCol] ?? 0) : 0;
                    $prodVal = $prodCol ? (int) $this->parseCleanNumber($row[$prodCol] ?? 0) : 0;
                    if ($prodVal === 0 && ($forecastCol && isset($row[$forecastCol]))) $prodVal = (int) $this->parseCleanNumber($row[$forecastCol]);

                    $defaultCurrency = strtoupper(trim($request->input('import_currency', 'IDR')));
                    $currencyVal = null;
                    if ($currencyCol && isset($row[$currencyCol])) {
                        $rawC = strtoupper(trim((string)$row[$currencyCol]));
                        if (str_contains($rawC, 'IDR') || str_contains($rawC, 'RP')) $currencyVal = 'IDR';
                        elseif (str_contains($rawC, 'USD') || str_contains($rawC, '$')) $currencyVal = 'USD';
                    }
                    $currencyVal = $currencyVal ?: $defaultCurrency;

                    $resolvedCategoryId = $defaultCatId;
                    $effectiveCategory = !empty($rawCategory) ? $rawCategory : ($categoryCol && !empty($row[$categoryCol]) ? trim((string)$row[$categoryCol]) : '');
                    if (!empty($effectiveCategory)) {
                        $normCatCode = \App\Services\DataValidation\InputNormalizer::normalizeCategoryCode($effectiveCategory);
                        $matchedCategory = $allCategories->first(fn($c) => strtoupper(trim($c->category_code)) === $normCatCode);
                        if (!$matchedCategory) {
                            $catNameMap = ['PUR-01' => 'Raw Material Kayu', 'PUR-02' => 'Raw Material Logam', 'PUR-03' => 'Consumable & Tools', 'PUR-04' => 'Komponen Packing'];
                            $catName = $catNameMap[$normCatCode] ?? ($effectiveCategory ?: $normCatCode);
                            $matchedCategory = \App\Models\PurchasingCategory::firstOrCreate(
                                ['category_code' => $normCatCode],
                                [
                                    'category_name' => $catName,
                                    'pic_buyer' => 'Procurement KI',
                                    'monthly_target_units' => 5000,
                                    'status' => 'Active',
                                ]
                            );
                            $allCategories = \App\Models\PurchasingCategory::all();
                        }
                        if ($matchedCategory) $resolvedCategoryId = $matchedCategory->id;
                    }

                    $monthlyPoValues = [];
                    $createData = [
                        'po_number' => 'PO-' . $itemCodeClean, 'po_date' => date('Y-m-d'), 'part_number' => $itemCodeClean,
                        'factory_code' => $factoryVal, 'description' => $fullDesc, 'category_id' => $resolvedCategoryId,
                        'drawing' => $typeVal ?: $itemCodeClean, 'price' => $priceVal, 'currency' => $currencyVal,
                        'complete' => 0, 'status' => 'Pending', 'workflow_stage' => 'waiting_manager',
                        'approval_notes' => 'Di-import dari Excel', 'supplier_name' => $suppVal,
                        'plan_stock' => $stockVal, 'plan_outstand' => $outstandVal
                    ];

                    for ($i = 0; $i <= 36; $i++) {
                        $mPo = 0; $mProd = 0; $mInv = 0;
                        if (isset($monthBlocks[$i])) {
                            if ($monthBlocks[$i]['poCol'] && isset($row[$monthBlocks[$i]['poCol']])) {
                                $mPo = (int) min(2147483647, max(-2147483648, $this->parseCleanNumber($row[$monthBlocks[$i]['poCol']])));
                            }
                            if ($monthBlocks[$i]['prodCol'] && isset($row[$monthBlocks[$i]['prodCol']])) {
                                $mProd = (int) min(2147483647, max(-2147483648, $this->parseCleanNumber($row[$monthBlocks[$i]['prodCol']])));
                            }
                            $stkColToUse = $monthBlocks[$i]['stockCol'] ?: ($monthBlocks[$i]['inventoryCol'] ?? null);
                            if ($stkColToUse && isset($row[$stkColToUse])) {
                                $mInv = (int) min(2147483647, max(-2147483648, $this->parseCleanNumber($row[$stkColToUse])));
                            }
                        }
                        if ($i === 0 && $mInv === 0 && $stockVal > 0) {
                            $mInv = (int) min(2147483647, max(-2147483648, $stockVal));
                        }
                        $createData["m{$i}_po"] = $mPo;
                        $createData["m{$i}_prod"] = $mProd;
                        $createData["m{$i}_inventory"] = $mInv;
                        if ($mPo > 0) $monthlyPoValues[] = $mPo;
                    }
                    if (!isset($createData['m0_inventory']) || $createData['m0_inventory'] === 0) {
                        $createData['m0_inventory'] = (int) min(2147483647, max(-2147483648, $stockVal));
                    }

                    $totalMonthPo = array_sum($monthlyPoValues);
                    $rawOrderQty = $poVal > 0 ? $poVal : ($totalMonthPo > 0 ? $totalMonthPo : $outstandVal);
                    $createData['order_qty'] = (int) min(2147483647, max(0, $rawOrderQty));
                    $createData['amount'] = (float) ($createData['order_qty'] * $priceVal);

                    // Robust Business Key Lookup: part_number + factory_code
                    PurchasingOutstanding::withoutEvents(function() use ($itemCodeClean, $factoryVal, $createData, &$updateCount, &$importCount) {
                        $existingItem = PurchasingOutstanding::where('part_number', $itemCodeClean)
                            ->where('factory_code', $factoryVal)
                            ->first();

                        if ($existingItem) {
                            $existingItem->update($createData);
                            $updateCount++;
                        } else {
                            PurchasingOutstanding::create($createData);
                            $importCount++;
                        }
                    });

                    $runningOutstand = $outstandVal; $runningStock = $stockVal;
                    foreach ($monthBlocks as $mIdx => $mBlock) {
                        $periode = $mBlock['periodYYYYMM'] ?? null;
                        if (!$periode) continue;
                        $mPoQty = ($mBlock['poCol'] && isset($row[$mBlock['poCol']])) ? (int) min(2147483647, max(-2147483648, $this->parseCleanNumber($row[$mBlock['poCol']]))) : 0;
                        $mProdQty = ($mBlock['prodCol'] && isset($row[$mBlock['prodCol']])) ? (int) min(2147483647, max(-2147483648, $this->parseCleanNumber($row[$mBlock['prodCol']]))) : 0;
                        $mForecastQty = ($mBlock['forecastCol'] && isset($row[$mBlock['forecastCol']])) ? (int) min(2147483647, max(0, $this->parseCleanNumber($row[$mBlock['forecastCol']]))) : 0;
                        $mDeliveryQty = ($mBlock['deliveryCol'] && isset($row[$mBlock['deliveryCol']])) ? (int) min(2147483647, max(-2147483648, $this->parseCleanNumber($row[$mBlock['deliveryCol']]))) : 0;
                        
                        // Ekstraksi nilai stock eksplisit jika ada kolom stock pada bulan ini di file Excel
                        $stkCol = $mBlock['stockCol'] ?: ($mBlock['inventoryCol'] ?? null);
                        $excelStockVal = ($stkCol && isset($row[$stkCol]) && trim((string)$row[$stkCol]) !== '') ? (int) min(2147483647, max(-2147483648, $this->parseCleanNumber($row[$stkCol]))) : null;

                        $effDelivery = $mDeliveryQty > 0 ? $mDeliveryQty : $mPoQty;
                        $calcOutstanding = ($mIdx === 0 && $outstandVal > 0) ? $outstandVal : ($runningOutstand + $mPoQty - $effDelivery);
                        
                        if ($excelStockVal !== null && ($excelStockVal !== 0 || $mIdx === 0)) {
                            $calcStock = $excelStockVal;
                        } else {
                            $calcStock = ($mIdx === 0) ? $runningStock : ($runningStock + $effDelivery - $mProdQty);
                        }
                        
                        $forecastingBatch[] = [
                            'part_number' => $itemCodeClean, 'factory_code' => $factoryVal, 'supplier_name' => $suppVal,
                            'periode' => $periode, 'period_month' => $periode, 'description' => $fullDesc,
                            'price' => $priceVal, 'currency' => $currencyVal,
                            'outstanding_pre' => (int) min(2147483647, max(-2147483648, $runningOutstand)),
                            'stock_pre'       => (int) min(2147483647, max(-2147483648, $runningStock)),
                            'po'              => (int) min(2147483647, max(-2147483648, $mPoQty)),
                            'po_qty'          => (int) min(2147483647, max(-2147483648, $mPoQty)),
                            'production'      => (int) min(2147483647, max(-2147483648, $mProdQty)),
                            'production_qty'  => (int) min(2147483647, max(-2147483648, $mProdQty)),
                            'delivery'        => (int) min(2147483647, max(-2147483648, $effDelivery)),
                            'forecast_qty'    => (int) min(2147483647, max(0, $mForecastQty ?: ($mPoQty > 0 ? $mPoQty : $mProdQty))),
                            'outstanding'     => (int) min(2147483647, max(-2147483648, $calcOutstanding)),
                            'stock'           => (int) min(2147483647, max(-2147483648, $calcStock)),
                            'stock_qty'       => (int) min(2147483647, max(-2147483648, $calcStock)),
                            'delivery_category_code' => 'LOC'
                        ];
                        $runningOutstand = $calcOutstanding; $runningStock = $calcStock;
                    }
                }

                // Update or Insert forecast records without nuking existing categories
                $userId = \Illuminate\Support\Facades\Auth::id(); $now = now();
                foreach ($forecastingBatch as $fcData) {
                    \Illuminate\Support\Facades\DB::table('forecastings')->updateOrInsert(
                        [
                            'part_number'  => $fcData['part_number'],
                            'factory_code' => $fcData['factory_code'],
                            'period_month' => $fcData['period_month'],
                        ],
                        array_merge($fcData, ['user_id' => $userId, 'created_at' => $now, 'updated_at' => $now])
                    );
                    $forecastSyncCount++;
                }

                // Cek duplikasi baris: hanya peringatkan jika Part Number + Factory Code + Supplier persis sama muncul > 1 kali
                $duplicatesFound = [];
                foreach ($codeOccurrences as $cKey => $occs) {
                    if (count($occs) > 1) {
                        $parts = explode('___', $cKey);
                        $cCode = $parts[0] ?? '';
                        $cFactory = $parts[1] ?? 'KIP 1';
                        $cSupp = $parts[2] ?? '';
                        $duplicatesFound[] = [
                            'code'         => $cCode,
                            'part_number'  => $cCode,
                            'factory_code' => $cFactory,
                            'supplier'     => $cSupp,
                            'count'        => count($occs),
                            'rows'         => array_column($occs, 'row'),
                            'descriptions' => array_values(array_unique(array_column($occs, 'desc'))),
                        ];
                    }
                }
                if (!empty($duplicatesFound)) {
                    session(['import_duplicates_found' => $duplicatesFound]);
                } else {
                    session()->forget('import_duplicates_found');
                }
            });

            PurchasingOutstanding::clearCalcCaches();
            //    Month 0 = Premonth (JUN), Month 1 = Start (JUL)
            //    Dropdown "Mulai" dan "Tahun" otomatis mengikuti Excel
            // ==============================================================
            $preM    = 'JUN';
            $startM  = 'JUL';
            $preYr   = (int) date('Y');
            $startYr = (int) date('Y');

            if (count($monthBlocks) > 0) {
                $durCount = max(1, count($monthBlocks) - 1);
                // monthBlocks[0] SELALU Premonth (misal: Jun-26)
                $preM    = $monthBlocks[0]['shortMonth'] ?? 'JUN';
                $preYr   = $monthBlocks[0]['year'] ?? date('Y');
                // monthBlocks[1] SELALU Start Month (misal: Jul-26)
                $startM  = isset($monthBlocks[1]) ? $monthBlocks[1]['shortMonth'] : $preM;
                $startYr = isset($monthBlocks[1]) ? $monthBlocks[1]['year'] : $preYr;

                $finalMonths = [];
                foreach ($monthBlocks as $idx => $mBlock) {
                    $finalMonths[$idx] = $mBlock['shortMonth'];
                }
                session([
                    'monitor_duration'    => $durCount,
                    'monitor_start_month' => $preM,
                    'monitor_start_year'  => $preYr,
                    'monitor_pre_month'   => $preM,
                    'monitor_months'      => $finalMonths,
                ]);
            }

            $fcMsg = $forecastSyncCount > 0 ? " | Forecast Sync: <strong>{$forecastSyncCount}</strong> record" : '';
            
            return redirect()->route('purchasing.outstanding', [
                'start_month' => $preM,
                'start_year'  => $preYr,
                'duration'    => isset($durCount) ? $durCount : 12,
            ])->with('success', "⚡ <strong>Smart Excel Multi-Bulan Import Berhasil!</strong> Berhasil memproses <strong>{$importCount}</strong> data baru dan memperbarui <strong>{$updateCount}</strong> data material. Premonth: <strong>{$preM}</strong> | Bulan Berjalan: <strong>{$startM} {$startYr}</strong>{$fcMsg}.");

        } catch (\Throwable $e) {
            return redirect()->route('purchasing.outstanding')->with('error', 'Terjadi kesalahan saat memproses Excel: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
        }
    }
}

