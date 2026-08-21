<?php

namespace App\Http\Controllers;

use App\Models\PurchasingOutstanding;
use App\Models\PurchasingLog;
use App\Models\PurchasingCategory;
use App\Models\Forecasting;
use App\Models\Outstanding;
use App\Models\OutstandingRecord;
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
        $statusFilter = $request->get('status', 'All');
        $searchQuery  = $request->get('search');

        $query = PurchasingOutstanding::with('category');

        if ($statusFilter && $statusFilter !== 'All') {
            $query->where('status', $statusFilter);
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

        $items = $query->orderBy('part_number', 'asc')->get();

        // KPI Keseluruhan (dari seluruh data, bukan hanya hasil filter)
        $allItems = PurchasingOutstanding::all();
        $totalItems        = $allItems->count();
        $totalOrderQty     = $allItems->sum('order_qty');
        $totalCompleteQty  = $allItems->sum('complete');
        $totalPendingQty   = max(0, $totalOrderQty - $totalCompleteQty);
        $totalAmount       = $allItems->sum('amount');
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

        // Agregasi per bulan
        $forecastByMonth = [];
        foreach ($monthsList as $num => $label) {
            $periodStr = $forecastYear . '-' . $num;
            $monthLogs = $forecastLogs->where('period_month', $periodStr);
            $target    = $monthLogs->sum('target_order');
            $actual    = $monthLogs->sum('actual_received');
            $prod      = $monthLogs->sum('production_qty');
            $pending   = $monthLogs->sum('pending_order');
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
            $target  = $catLogs->sum('target_order');
            $actual  = $catLogs->sum('actual_received');
            $gap     = $actual - $target;
            $fulfPct = $target > 0 ? round(($actual / $target) * 100, 1) : null;

            $forecastByCategory[] = [
                'code'        => $cat->category_code,
                'name'        => $cat->category_name,
                'target'      => $target,
                'actual'      => $actual,
                'production'  => $catLogs->sum('production_qty'),
                'pending'     => $catLogs->sum('pending_order'),
                'gap'         => $gap,
                'fulfillment' => $fulfPct,
            ];
        }

        // KPI Totals
        $forecastTotalTarget  = $forecastLogs->sum('target_order');
        $forecastTotalActual  = $forecastLogs->sum('actual_received');
        $forecastTotalProd    = $forecastLogs->sum('production_qty');
        $forecastTotalPending = $forecastLogs->sum('pending_order');
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

        $forecastList = $forecastQuery->orderBy('periode', 'desc')->orderBy('part_number', 'asc')->get();
        $actualList = $actualQuery->orderBy('periode', 'desc')->orderBy('part_number', 'asc')->get();
        $outstandingList = $outstandingQuery->orderBy('periode', 'desc')->orderBy('part_number', 'asc')->get();

        // Kumpulkan semua periode unik dari ke-3 tabel untuk filter dropdown, dinormalisasi
        $periodes = collect()
            ->concat(Forecasting::pluck('periode'))
            ->concat(Forecasting::pluck('period_month'))
            ->concat(Actual::pluck('periode'))
            ->concat(Actual::pluck('period_month'))
            ->concat(Outstanding::pluck('periode'))
            ->concat(Outstanding::pluck('period_month'))
            ->filter()
            ->map(fn ($p) => $this->normalizePeriodString((string) $p))
            ->unique()
            ->sortDesc()
            ->values();

        // KPI Totals 3 Master
        $masterForecastCount = $forecastList->count();
        $masterForecastTotalQty = $forecastList->sum('forecast_qty');

        $masterActualCount = $actualList->count();
        $masterActualTotalQty = $actualList->sum('actual_qty');

        $masterOutstandingCount = $outstandingList->count();
        $masterOutstandingTotalQty = $outstandingList->sum('outstanding_qty');

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
            'categories'       => $allCategories,
            'buyers'           => \App\Models\User::orderBy('name')->get(),
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

        // Sinkronisasi live data untuk periode yang sedang dilihat agar hasil komparasi selalu 100% akurat
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

            $sheet = $spreadsheet->getActiveSheet();
            // Disabling $calculateFormulas (parameter 2 = false) prevents PHP formula recalculation loops on #DIV/0! and complex formulas
            $rows = $sheet->toArray(null, false, true, true);
            
            if (empty($rows)) {
                return redirect()->back()->with('error', 'File Excel kosong atau tidak terbaca.');
            }

            // ==============================================================
            // 1. DETEKSI BARIS HEADER UTAMA
            //    Cari baris yang memiliki 'ITEM CODE', 'PART NUMBER', dsb.
            //    Ini menandai awal dari blok multi-baris header.
            // ==============================================================
            $headerRowIdx = null;
            $bestScore = 0;
            $itemKeywords = [
                'ITEM CODE', 'ITEM_CODE', 'ITEM', 'PART NUMBER', 'PART_NUMBER', 'PART NO', 'PN', 
                'DRAWING', 'NO. BARANG', 'ITEM CODE (PK)', 'MATERIAL CODE', 'MATERIAL_CODE', 'MATERIAL',
                'KODE BARANG', 'KODE MATERIAL', 'KODE ITEM', 'KODE PART', 'KOMPONEN', 'SKU', 'CODE'
            ];

            foreach ($rows as $rIdx => $row) {
                if ($rIdx > 30) break;
                $rowScore = 0;
                foreach ($row as $col => $val) {
                    $cleanVal = strtoupper(trim((string)($val ?? '')));
                    if (!$cleanVal) continue;
                    foreach ($itemKeywords as $ikw) {
                        if ($cleanVal === $ikw || str_starts_with($cleanVal, $ikw) || str_contains($cleanVal, $ikw)) {
                            $rowScore += 3;
                            break;
                        }
                    }
                    if (str_contains($cleanVal, 'SUPPLIER') || str_contains($cleanVal, 'VENDOR') || str_contains($cleanVal, 'DESCRIPTION') || str_contains($cleanVal, 'PRICE') || str_contains($cleanVal, 'PO') || str_contains($cleanVal, 'STOCK')) {
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

            // Helper internal untuk mengurai nilai sel menjadi info bulan (Mendukung Serial Date Excel 46174 & String "JUN-26")
            $parseCellMonth = function($val) {
                if (empty($val)) return null;
                $valStr = trim((string)$val);
                
                // 1. Jika angka serial Excel (40000 - 50000 = tahun 2009 - 2036)
                if (is_numeric($valStr) && (float)$valStr >= 40000 && (float)$valStr <= 50000) {
                    $unix = ((float)$valStr - 25569) * 86400;
                    $mShort = strtoupper(date('M', (int)$unix));
                    $yr = date('Y', (int)$unix);
                    return [
                        'short' => $mShort,
                        'year'  => (int)$yr,
                        'code'  => $mShort . '-' . substr($yr, -2)
                    ];
                }
                
                // 2. Jika string tekstual (e.g. JUN-26, Jun 2026, JUL-26)
                $monthPattern = '/(JAN|FEB|MAR|APR|MAY|MEI|JUN|JUL|AUG|AGS|SEP|OCT|OKT|NOV|DEC|DES)[\s\-]?(\d{2,4})/i';
                if (preg_match($monthPattern, strtoupper($valStr), $mMatch)) {
                    $mShort = strtoupper($mMatch[1]);
                    if ($mShort === 'MEI') $mShort = 'MAY';
                    if ($mShort === 'AGS') $mShort = 'AUG';
                    if ($mShort === 'DES') $mShort = 'DEC';
                    if ($mShort === 'OKT') $mShort = 'OCT';
                    $yrDigits = strlen($mMatch[2]) === 2 ? ('20' . $mMatch[2]) : $mMatch[2];
                    return [
                        'short' => $mShort,
                        'year'  => (int)$yrDigits,
                        'code'  => $mShort . '-' . substr($yrDigits, -2)
                    ];
                }
                
                return null;
            };

            // ==============================================================
            // 2. DETEKSI BLOK HEADER MULTI-BARIS (termasuk sub-header bulan)
            //    Enhanced: Mendukung template 3-baris header (baris bulan,
            //    baris group OUTSTANDING/STOCK/PO, baris QTY/AMOUNT).
            //    dataStartRowIdx = baris pertama data nyata (setelah header).
            // ==============================================================
            $dataStartRowIdx = $headerRowIdx + 1;
            // Extended keyword list untuk mendeteksi sub-header rows
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
                
                // Cek apakah baris ini adalah baris sub-header (mengandung nama bulan)
                $hasMonthName = false;
                foreach ($rowData as $col => $val) {
                    if ($parseCellMonth($val) !== null) {
                        $hasMonthName = true;
                        break;
                    }
                }
                
                // Jika baris mengandung bulan → pasti sub-header
                if ($hasMonthName) {
                    $dataStartRowIdx = $scanRow + 1;
                    continue;
                }
                
                // Cek kata kunci sub-header di semua sel baris
                $firstCellKey = array_key_first($rowData);
                $firstCellVal = strtoupper(trim((string)($rowData[$firstCellKey] ?? '')));
                
                // Jika sel pertama berisi teks sub-header umum (bukan item code nyata)
                if (!empty($firstCellVal) && in_array($firstCellVal, $subHeaderKeywords)) {
                    $dataStartRowIdx = $scanRow + 1;
                    continue;
                }
                
                // Enhanced: Hitung berapa banyak sel berisi keyword sub-header vs total sel non-kosong
                $subHeaderWordCount = 0;
                $totalTextCells = 0;
                $totalNonEmptyCells = 0;
                foreach ($rowData as $val) {
                    $vClean = strtoupper(trim((string)$val));
                    if (!empty($vClean)) {
                        $totalNonEmptyCells++;
                        if (!is_numeric($vClean)) {
                            $totalTextCells++;
                            if (in_array($vClean, $subHeaderKeywords) || $vClean === '%') {
                                $subHeaderWordCount++;
                            }
                        }
                    }
                }
                
                // Baris kosong di kolom A/B/C tapi punya banyak keyword → sub-header
                if (empty($firstCellVal)) {
                    // Jika ada 2+ kata kunci sub-header di baris ini → baris sub-header
                    if ($subHeaderWordCount >= 2) {
                        $dataStartRowIdx = $scanRow + 1;
                        continue;
                    }
                    // Jika semua sel teks adalah kata kunci sub-header (100%) → sub-header juga
                    if ($totalTextCells > 0 && ($subHeaderWordCount / $totalTextCells) >= 0.8) {
                        $dataStartRowIdx = $scanRow + 1;
                        continue;
                    }
                    // Jika baris hanya berisi QTY dan AMOUNT (row ke-3 dari multi-row header)
                    $qtyAmountCount = 0;
                    foreach ($rowData as $val) {
                        $vClean = strtoupper(trim((string)$val));
                        if (in_array($vClean, ['QTY', 'BQTY', 'B.QTY', 'B QTY', 'AMOUNT', 'AMT', 'JUMLAH', '%'])) {
                            $qtyAmountCount++;
                        }
                    }
                    if ($qtyAmountCount >= 2) {
                        $dataStartRowIdx = $scanRow + 1;
                        continue;
                    }
                }
                
                // Enhanced: Jika > 50% sel non-kosong berisi keyword → sub-header
                if ($totalNonEmptyCells > 0 && $subHeaderWordCount > 0 && ($subHeaderWordCount / $totalNonEmptyCells) >= 0.5) {
                    $dataStartRowIdx = $scanRow + 1;
                    continue;
                }
                
                // Jika sel pertama non-kosong dan bukan kata kunci → ini baris data pertama
                $nonEmptyCells = array_filter($rowData, fn($v) => !empty(trim((string)$v)));
                $nonEmptyCount = count($nonEmptyCells);
                if ($nonEmptyCount > 0 && !empty($firstCellVal) && !in_array($firstCellVal, $subHeaderKeywords)) {
                    // Extra check: Jika sel pertama murni angka kecil (nomor urut) DAN banyak keyword di baris → sub-header
                    if (is_numeric($firstCellVal) && (int)$firstCellVal <= 10 && $subHeaderWordCount >= 2) {
                        $dataStartRowIdx = $scanRow + 1;
                        continue;
                    }
                    $dataStartRowIdx = $scanRow;
                    break;
                }
                
                // Jika semua sel kosong → mungkin baris kosong antara, skip saja
                if ($nonEmptyCount === 0) {
                    continue;
                }
            }


            // ==============================================================
            // 3. BANGUN COMBINED HEADERS dari semua baris header block
            //    (baris headerRowIdx sampai dataStartRowIdx-1, TIDAK melebihi data)
            // ==============================================================
            $combinedHeaders = [];
            $allHeaderCols = [];
            for ($hRow = $headerRowIdx; $hRow < $dataStartRowIdx; $hRow++) {
                if (isset($rows[$hRow])) {
                    $allHeaderCols = array_unique(array_merge($allHeaderCols, array_keys($rows[$hRow])));
                }
            }
            // Juga gabungkan kolom dari 2 baris di atas headerRowIdx (untuk parent merged header)
            for ($hRow = max(1, $headerRowIdx - 2); $hRow < $headerRowIdx; $hRow++) {
                if (isset($rows[$hRow])) {
                    $allHeaderCols = array_unique(array_merge($allHeaderCols, array_keys($rows[$hRow])));
                }
            }
            
            // Build per-row header texts for fine-grained QTY/AMOUNT detection
            $perRowHeaders = [];
            for ($hRow = max(1, $headerRowIdx - 2); $hRow < $dataStartRowIdx; $hRow++) {
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
                for ($hRow = max(1, $headerRowIdx - 2); $hRow < $dataStartRowIdx; $hRow++) {
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
            // 4. DETEKSI BULAN LANGSUNG DARI BARIS HEADER (Mendukung Date Serial & String)
            //    Enhanced: Deteksi QTY vs AMOUNT kolom per bulan
            // ==============================================================
            $monthBlocks = [];
            $currentMonthStr = null;
            $currentMShort   = null;
            $currentYrDigits = null;
            $monthIndexCount = -1;
            $monthColsScanned = []; // col -> monthIndex

            for ($hRow = max(1, $headerRowIdx - 2); $hRow < $dataStartRowIdx; $hRow++) {
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
                                // Map month short name to numeric month
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

            // Petakan sub-header PO/PROD/STOCK/INVENTORY/OUTSTANDING/FORECAST ke bulan yang benar
            $monthStarts = [];
            foreach ($monthBlocks as $mIdx => $mBlock) {
                $monthStarts[$mIdx] = $mBlock['startCol'];
            }
            
            // Helper: Tentukan month index untuk kolom tertentu (menggunakan perbandingan indeks numerik kolom)
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
            // These are in row 2 of the multi-row header (the middle row)
            $colGroupMap = []; // col => 'PO'|'OUTSTANDING'|'STOCK'|'INVENTORY'|'FORECAST'|'DELIVERY'|'PROD'
            $lastGroupName = null;
            
            for ($hRow = max(1, $headerRowIdx - 2); $hRow < $dataStartRowIdx; $hRow++) {
                if (!isset($rows[$hRow])) continue;
                foreach ($rows[$hRow] as $col => $val) {
                    $cellVal = strtoupper(trim((string)($val ?? '')));
                    if (empty($cellVal)) continue;
                    
                    $assignedMonthIdx = $getMonthIdxForCol($col);
                    if ($assignedMonthIdx === null || !isset($monthBlocks[$assignedMonthIdx])) continue;
                    
                    // Detect group name
                    if (preg_match('/^INVENTORY$|\bINVENTORY\b|\bINVENTORI\b|\bINV\b/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['inventoryCol']) $monthBlocks[$assignedMonthIdx]['inventoryCol'] = $col;
                        $colGroupMap[$col] = 'INVENTORY';
                        $lastGroupName = 'INVENTORY';
                    } elseif (preg_match('/^PO$|\bPO\b/i', $cellVal) && !preg_match('/OUTSTANDING|STOCK|FORECAST|INVENTORY/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['poCol']) $monthBlocks[$assignedMonthIdx]['poCol'] = $col;
                        $colGroupMap[$col] = 'PO';
                        $lastGroupName = 'PO';
                    } elseif (preg_match('/^PROD$|\bPROD\b|PRODUKSI/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['prodCol']) $monthBlocks[$assignedMonthIdx]['prodCol'] = $col;
                        $colGroupMap[$col] = 'PROD';
                        $lastGroupName = 'PROD';
                    } elseif (preg_match('/^STOCK$|\bSTOCK\b|\bSTOK\b/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['stockCol']) $monthBlocks[$assignedMonthIdx]['stockCol'] = $col;
                        $colGroupMap[$col] = 'STOCK';
                        $lastGroupName = 'STOCK';
                    } elseif (preg_match('/OUTSTANDING/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['outstandCol']) $monthBlocks[$assignedMonthIdx]['outstandCol'] = $col;
                        $colGroupMap[$col] = 'OUTSTANDING';
                        $lastGroupName = 'OUTSTANDING';
                    } elseif (preg_match('/FORECAST|TARGET/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['forecastCol']) $monthBlocks[$assignedMonthIdx]['forecastCol'] = $col;
                        $colGroupMap[$col] = 'FORECAST';
                        $lastGroupName = 'FORECAST';
                    } elseif (preg_match('/DELIVERY|DELIVERI/i', $cellVal)) {
                        if (!$monthBlocks[$assignedMonthIdx]['deliveryCol']) $monthBlocks[$assignedMonthIdx]['deliveryCol'] = $col;
                        $colGroupMap[$col] = 'DELIVERY';
                        $lastGroupName = 'DELIVERY';
                    }
                }
            }
            
            // Pass 2: Detect QTY and AMOUNT sub-columns (row 3 of multi-row header)
            // For each QTY/AMOUNT cell, find which group it belongs to by looking at:
            //   1. The group header directly above it (same column in row above)
            //   2. The nearest group header to the left (merged cell scenario)
            for ($hRow = max(1, $headerRowIdx - 2); $hRow < $dataStartRowIdx; $hRow++) {
                if (!isset($rows[$hRow])) continue;
                foreach ($rows[$hRow] as $col => $val) {
                    $cellVal = strtoupper(trim((string)($val ?? '')));
                    if (empty($cellVal)) continue;
                    
                    $isQty = in_array($cellVal, ['QTY', 'BQTY', 'B.QTY', 'B QTY', 'UNIT', 'JML', 'JUMLAH QTY']);
                    $isAmount = in_array($cellVal, ['AMOUNT', 'AMT', 'JUMLAH', 'TOTAL', 'NILAI']);
                    
                    if (!$isQty && !$isAmount) continue;
                    
                    $assignedMonthIdx = $getMonthIdxForCol($col);
                    if ($assignedMonthIdx === null || !isset($monthBlocks[$assignedMonthIdx])) continue;
                    
                    // Find the parent group: check column directly above, or the nearest group col to the left
                    $parentGroup = null;
                    
                    // Check same column in previous rows
                    for ($checkRow = $hRow - 1; $checkRow >= max(1, $headerRowIdx - 2); $checkRow--) {
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
                            if (preg_match('/FORECAST|TARGET/i', $aboveVal)) { $parentGroup = 'FORECAST'; break; }
                            if (preg_match('/DELIVERY|DELIVERI/i', $aboveVal)) { $parentGroup = 'DELIVERY'; break; }
                            if (preg_match('/^PROD$|\bPROD\b|PRODUKSI/i', $aboveVal)) { $parentGroup = 'PROD'; break; }
                        }
                    }
                    
                    // If no parent found directly above, search nearest group to the left in same month
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
                        // Assign QTY col (prefer first found, don't overwrite)
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
            
            // Pass 3: For groups with a column but no explicit QTY/AMOUNT sub-columns,
            // check if the next column is AMOUNT by position (common pattern: QTY then AMOUNT)
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
                        // Check if next column after QTY col has AMOUNT in combined header
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
            // 5. DETEKSI KOLOM FIELD (ITEM CODE, DESC, SUPPLIER, PRICE, CURRENCY, dll.)
            // ==============================================================
            $itemCodeCol = null;
            $factoryCodeCol = null;
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
                $ht = strtoupper($headerText);

                $leftmostMonthCol = !empty($monthStarts) ? $numToCol(min(array_map($colToNum, $monthStarts))) : 'ZZZZZ';
                $isFieldCol = ($colToNum($col) < $colToNum($leftmostMonthCol)) || empty($monthBlocks);

                if ($isFieldCol) {
                    if (!$suppCodeCol && preg_match('/(SUPPLIER[\s_]?CODE|KODE[\s_]?SUPPLIER|VENDOR[\s_]?CODE|KODE[\s_]?VENDOR|VEND_CODE|SUPP_CODE)/i', $ht)) {
                        $suppCodeCol = $col;
                    } elseif (!$itemCodeCol && preg_match('/(ITEM[\s_]?CODE|MATERIAL[\s_]?CODE|PART[\s_]?NUMBER|PART[\s_]?NO|\bPN\b|\bDRAWING\b|KODE[\s_]?BARANG)/i', $ht)) {
                        $itemCodeCol = $col;
                    } elseif (!$factoryCodeCol && preg_match('/(FACTORY[\s_]?CODE|KODE[\s_]?PABRIK|\bFACTORY\b|\bPLANT\b)/i', $ht)) {
                        $factoryCodeCol = $col;
                    } elseif (!$descCol && preg_match('/(DECRIPTION|DESCRIPTION|DESKRIPSI|NAMA[\s_]?BARANG|ITEM[\s_]?NAME)/i', $ht)) {
                        $descCol = $col;
                    } elseif (!$suppCol && preg_match('/(SUPPLIER|VENDOR|TRADE[\s_]?NAME|\bSUPP\b)/i', $ht)) {
                        $suppCol = $col;
                    } elseif (!$typeCol && preg_match('/\bTYPE\b|\bTIPE\b|\bMODEL\b|\bSPEC\b/i', $ht)) {
                        $typeCol = $col;
                    } elseif (!$priceCol && preg_match('/\bPRICE\b|\bHARGA\b|UNIT PRICE/i', $ht)) {
                        $priceCol = $col;
                    } elseif (!$currencyCol && preg_match('/CURRENCY|MATA[\s_]?UANG|KURS/i', $ht)) {
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
                    } elseif (
                        preg_match('/\bPO\b|P\.O\.|\bPURCHASE[\s_]ORDER\b|\bORDER\b/i', $ht) &&
                        !preg_match('/OUTSTANDING|STOCK|FORECAST/i', $ht)
                    ) {
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

            // Strict & Standard Positional Fallbacks
            if (!$itemCodeCol && !$suppCol) {
                $itemCodeCol = 'B';
                $suppCol = 'E';
            } elseif (!$itemCodeCol && $suppCol) {
                // If suppCol is 'B', item code might be in 'F' or 'E' or another field
                $itemCodeCol = $suppCodeCol ?: ($suppCol === 'B' ? 'F' : 'B');
            } elseif ($itemCodeCol && !$suppCol) {
                $suppCol = $suppCodeCol ?: ($itemCodeCol === 'B' ? 'E' : 'B');
            }
            if (!$descCol)        $descCol        = 'C';
            if (!$factoryCodeCol) $factoryCodeCol = 'D';
            if (!$priceCol)       $priceCol       = 'F';
            if (!$currencyCol)    $currencyCol    = 'G';

            // ==============================================================
            // 6. PROSES DATA ROWS — Mulai dari $dataStartRowIdx
            // ==============================================================
            $importCount = 0;
            $updateCount = 0;
            $forecastSyncCount = 0;
            $dataRows = array_filter($rows, fn($rIdx) => $rIdx >= $dataStartRowIdx, ARRAY_FILTER_USE_KEY);
            $firstCategory = \App\Models\PurchasingCategory::first();
            $defaultCatId = $firstCategory ? $firstCategory->id : 1;

            \Illuminate\Support\Facades\DB::transaction(function() use ($request, $dataRows, $itemCodeCol, $factoryCodeCol, $descCol, $suppCol, $suppCodeCol, $typeCol, $priceCol, $currencyCol, $outstandCol, $stockCol, $poCol, $forecastCol, $prodCol, $monthBlocks, $defaultCatId, $parseCellMonth, &$importCount, &$updateCount, &$forecastSyncCount) {
                // Fetch existing records ordered by ID for 1-to-1 row index matching with Excel
                $existingRows = PurchasingOutstanding::orderBy('id', 'asc')->get();
                $existingCount = $existingRows->count();
                $rowIndex = 0;

                $skipKeywords = ['ITEM CODE', 'ITEM', 'PN', 'PART NUMBER', 'PART NO', 'TOTAL', 'GRAND TOTAL', 'NO', 'ITEM CODE (PK)', 'KATEGORI', 'DESCRIPTION & SUPPLIER', 'DESCRIPTION', 'DESKRIPSI', 'KETERANGAN', 'NOTE', 'SUB TOTAL', 'SUBTOTAL'];

                // Helper deteksi nama perusahaan / supplier
                $isCompanyName = function(?string $text): bool {
                    if (empty($text)) return false;
                    $clean = strtoupper(trim($text));
                    if (preg_match('/^(PT\b|PT\.|CV\b|CV\.|UD\b|UD\.|FA\b|FA\.|TBK\b|INC\b|LTD\b|CORP\b)/i', $clean)) {
                        return true;
                    }
                    $keywords = ['SEJAHTERA', 'INDONESIA', 'NIAGA', 'BIMASAKTI', 'ANEKA', 'SUMBER', 'AGUNG', 'JAYA', 'ABADI', 'SUKSES', 'PERSERO', 'MAKMUR', 'SENTOSA', 'UTAMA', 'KARYA', 'MANDIRI'];
                    foreach ($keywords as $kw) {
                        if (str_contains($clean, $kw)) return true;
                    }
                    return false;
                };

                // Track item code occurrences to detect duplicates in Excel
                $codeOccurrences = [];

                foreach ($dataRows as $rIdx => $row) {
                    $rawCode = trim($row[$itemCodeCol] ?? '');
                    $rawDesc = trim($row[$descCol] ?? '');
                    $rawSupp = trim($row[$suppCol] ?? '');
                    $rawSuppCode = $suppCodeCol ? trim($row[$suppCodeCol] ?? '') : '';

                    // Evaluasi Disambiguasi: Jika $rawCode adalah Nama Perusahaan (CV./PT.)
                    if ($isCompanyName($rawCode)) {
                        $suppVal = $rawCode;
                        if (!empty($rawSupp) && !$isCompanyName($rawSupp)) {
                            $itemCodeVal = $rawSupp;
                        } elseif (!empty($rawSuppCode) && !$isCompanyName($rawSuppCode)) {
                            $itemCodeVal = $rawSuppCode;
                        } else {
                            $itemCodeVal = !empty($rawDesc) ? $rawDesc : $rawCode;
                        }
                    } elseif ($isCompanyName($rawSupp)) {
                        $suppVal = $rawSupp;
                        $itemCodeVal = !empty($rawCode) ? $rawCode : ($rawSuppCode ?: $rawDesc);
                    } else {
                        $itemCodeVal = !empty($rawCode) ? $rawCode : ($rawDesc ?: $rawSupp);
                        $suppVal = $rawSupp ?: $rawSuppCode;
                    }

                    if (empty($itemCodeVal)) continue;
                    
                    $itemCodeUpper = strtoupper($itemCodeVal);
                    if (in_array($itemCodeUpper, $skipKeywords)) continue;
                    if ($parseCellMonth($itemCodeVal) !== null) continue;
                    if (preg_match('/^(PO|PROD|PRODUKSI|STOCK|STOK|OUTSTANDING|QTY|BQTY|FORECAST|TARGET|DELIVERY|RATIO|KETERANGAN|NOTE|%|AMOUNT|AMT)$/i', $itemCodeVal)) continue;
                    
                    $itemCodeClean = strtoupper($itemCodeVal);
                    $rawFactory = ($factoryCodeCol && !empty(trim($row[$factoryCodeCol] ?? ''))) ? strtoupper(trim($row[$factoryCodeCol])) : '';

                    // Strict factory code matching (max 4-5 chars: KIP 1, KIP 2, KIP 3, KIP 4)
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
                        if (!empty($rawFactory) && $isCompanyName($rawFactory)) {
                            if (empty($suppVal)) {
                                $suppVal = trim($row[$factoryCodeCol]);
                            }
                        }
                        $factoryVal = 'KIP 1';
                    }
                    
                    // Description
                    $descVal = !empty($rawDesc) ? $rawDesc : ($itemCodeClean !== $rawCode ? $rawCode : $itemCodeClean);
                    $typeVal = $typeCol ? trim($row[$typeCol] ?? '') : '';
                    $fullDesc = $typeVal ? ($descVal . ' (' . $typeVal . ')') : $descVal;

                    $suppClean = strtoupper(trim($suppVal ?? ''));

                    // Catat kemunculan (item_code + factory_code + supplier) & nomor baris Excel untuk notifikasi duplikat
                    $compositeKey = $itemCodeClean . '___' . $factoryVal . '___' . $suppClean;
                    $codeOccurrences[$compositeKey][] = [
                        'row'  => $rIdx,
                        'desc' => $fullDesc . ' [' . $factoryVal . ($suppVal ? ' - ' . $suppVal : '') . ']',
                    ];

                    // Baseline Outstanding & Stock dari Month 0 (Premonth = Jun)
                    $outstandVal  = 0;
                    $stockVal     = 0;

                    if (isset($monthBlocks[0])) {
                        $oCol = $monthBlocks[0]['outstandCol'];
                        $sCol = $monthBlocks[0]['stockCol'] ?? ($monthBlocks[0]['inventoryCol'] ?? null);
                        if ($oCol && isset($row[$oCol])) $outstandVal = (int) $this->parseCleanNumber($row[$oCol]);
                        if ($sCol && isset($row[$sCol])) $stockVal    = (int) $this->parseCleanNumber($row[$sCol]);
                    }

                    if ($outstandVal === 0 && $outstandCol && isset($row[$outstandCol])) {
                        $outstandVal = (int) $this->parseCleanNumber($row[$outstandCol]);
                    }
                    if ($stockVal === 0 && $stockCol && isset($row[$stockCol])) {
                        $stockVal = (int) $this->parseCleanNumber($row[$stockCol]);
                    }

                    // Price
                    $priceVal    = $priceCol ? (float) $this->parseCleanNumber($row[$priceCol] ?? 0) : 0.0;
                    $poVal       = $poCol ? (int) $this->parseCleanNumber($row[$poCol] ?? 0) : 0;
                    $prodVal     = $prodCol ? (int) $this->parseCleanNumber($row[$prodCol] ?? 0) : 0;
                    $forecastVal = $forecastCol ? (int) $this->parseCleanNumber($row[$forecastCol] ?? 0) : 0;
                    if ($prodVal === 0 && $forecastVal > 0) {
                        $prodVal = $forecastVal;
                    }

                    // Currency
                    $defaultCurrency = strtoupper(trim($request->input('import_currency', $request->input('currency', 'IDR'))));
                    if (!in_array($defaultCurrency, ['USD', 'IDR', 'ALL'])) {
                        $defaultCurrency = 'IDR';
                    }
                    if ($defaultCurrency === 'ALL') $defaultCurrency = 'IDR';

                    $currencyVal = null;
                    
                    if ($currencyCol && isset($row[$currencyCol]) && !empty(trim((string)$row[$currencyCol]))) {
                        $rawC = strtoupper(trim((string)$row[$currencyCol]));
                        if (str_contains($rawC, 'IDR') || str_contains($rawC, 'RUPIAH') || str_contains($rawC, 'RP')) {
                            $currencyVal = 'IDR';
                        } elseif (str_contains($rawC, 'USD') || str_contains($rawC, 'DOLLAR') || str_contains($rawC, '$')) {
                            $currencyVal = 'USD';
                        }
                    }
                    
                    if (!$currencyVal && $priceCol) {
                        $nextColChar = chr(ord($priceCol) + 1);
                        if (isset($row[$nextColChar]) && !empty(trim((string)$row[$nextColChar]))) {
                            $rawNext = strtoupper(trim((string)$row[$nextColChar]));
                            if (str_contains($rawNext, 'IDR') || str_contains($rawNext, 'RUPIAH') || str_contains($rawNext, 'RP')) {
                                $currencyVal = 'IDR';
                            } elseif (str_contains($rawNext, 'USD') || str_contains($rawNext, 'DOLLAR') || str_contains($rawNext, '$')) {
                                $currencyVal = 'USD';
                            }
                        }
                    }
                    
                    if (!$currencyVal) {
                        foreach ($row as $cellVal) {
                            $cvClean = strtoupper(trim((string)$cellVal));
                            if (in_array($cvClean, ['IDR', 'RUPIAH', 'RP'])) {
                                $currencyVal = 'IDR';
                                break;
                            } elseif (in_array($cvClean, ['USD', 'DOLLAR', '$'])) {
                                $currencyVal = 'USD';
                                break;
                            }
                        }
                    }
                    
                    if (!$currencyVal) {
                        $currencyVal = $defaultCurrency;
                    }

                    $createData = [
                        'po_number'      => 'PO-' . $itemCodeClean,
                        'po_date'        => date('Y-m-d'),
                        'part_number'    => $itemCodeClean,
                        'factory_code'   => $factoryVal,
                        'description'    => $fullDesc,
                        'category_id'    => $defaultCatId,
                        'order_qty'      => $poVal,
                        'drawing'        => $typeVal ?: $itemCodeClean,
                        'price'          => $priceVal,
                        'currency'       => $currencyVal,
                        'amount'         => $poVal * $priceVal,
                        'complete'       => 0,
                        'status'         => 'Pending',
                        'workflow_stage' => 'waiting_manager',
                        'approval_notes' => 'Di-import dari Excel (Smart Mapping Multi-Bulan) - Menunggu Manager',
                        'supplier_name'  => $suppVal,
                        'plan_stock'     => $stockVal,
                        'plan_outstand'  => $outstandVal,
                    ];

                    for ($i = 1; $i <= 36; $i++) {
                        $mPo   = 0;
                        $mProd = 0;
                        $mDel  = 0;

                        if (isset($monthBlocks[$i])) {
                            $poC   = $monthBlocks[$i]['poCol'];
                            $prodC = $monthBlocks[$i]['prodCol'];
                            $delC  = $monthBlocks[$i]['deliveryCol'];

                            if ($poC && isset($row[$poC])) {
                                $mPo = (int) $this->parseCleanNumber($row[$poC]);
                            }
                            if ($prodC && isset($row[$prodC])) {
                                $mProd = (int) $this->parseCleanNumber($row[$prodC]);
                            }
                            if ($delC && isset($row[$delC])) {
                                $mDel = (int) $this->parseCleanNumber($row[$delC]);
                            }
                        } else {
                            if ($i === 1) {
                                $mPo   = $poVal;
                                $mProd = $prodVal;
                            }
                        }

                        $createData["m{$i}_po"]   = $mPo;
                        $createData["m{$i}_prod"] = $mProd;
                    }

                    // 1-to-1 Row Index Matching: Setiap baris Excel memetakan tepat 1 record DB
                    if ($rowIndex < $existingCount) {
                        $existingRows[$rowIndex]->update($createData);
                        $updateCount++;
                    } else {
                        PurchasingOutstanding::create($createData);
                        $importCount++;
                    }
                    $rowIndex++;

                    // ──────────────────────────────────────────────────────────
                    // FORECASTING SYNC: Buat/update record di tabel forecastings
                    // ──────────────────────────────────────────────────────────
                    foreach ($monthBlocks as $mIdx => $mBlock) {
                        if ($mIdx === 0) continue;
                        
                        $periode = $mBlock['periodYYYYMM'] ?? null;
                        if (!$periode) continue;
                        
                        $mPoQty = 0;
                        $mProdQty = 0;
                        $mForecastQty = 0;
                        $mDeliveryQty = 0;
                        
                        if ($mBlock['poCol'] && isset($row[$mBlock['poCol']])) {
                            $mPoQty = (int) $this->parseCleanNumber($row[$mBlock['poCol']]);
                        }
                        if ($mBlock['prodCol'] && isset($row[$mBlock['prodCol']])) {
                            $mProdQty = (int) $this->parseCleanNumber($row[$mBlock['prodCol']]);
                        }
                        if ($mBlock['forecastCol'] && isset($row[$mBlock['forecastCol']])) {
                            $mForecastQty = (int) $this->parseCleanNumber($row[$mBlock['forecastCol']]);
                        }
                        if ($mBlock['deliveryCol'] && isset($row[$mBlock['deliveryCol']])) {
                            $mDeliveryQty = (int) $this->parseCleanNumber($row[$mBlock['deliveryCol']]);
                        }
                        
                        $calcOutstanding = $outstandVal + $mPoQty - $mDeliveryQty;
                        $calcStock = $stockVal + $mDeliveryQty - $mProdQty;
                        
                        $forecastingBatch[] = [
                            'part_number'    => $itemCodeClean,
                            'factory_code'   => $factoryVal,
                            'supplier_name'  => $suppVal,
                            'periode'        => $periode,
                            'period_month'   => $periode,
                            'description'    => $fullDesc,
                            'price'          => $priceVal,
                            'currency'       => $currencyVal,
                            'outstanding_pre'=> $outstandVal,
                            'stock_pre'      => $stockVal,
                            'po'             => $mPoQty,
                            'po_qty'         => $mPoQty,
                            'production'     => $mProdQty,
                            'production_qty' => $mProdQty,
                            'delivery'       => $mDeliveryQty > 0 ? $mDeliveryQty : $mPoQty,
                            'forecast_qty'   => $mForecastQty > 0 ? $mForecastQty : max(0, $mPoQty - $outstandVal),
                            'outstanding'    => $calcOutstanding,
                            'stock'          => $calcStock,
                            'stock_qty'      => $calcStock,
                            'delivery_category_code' => $request->input('delivery_category_code', 'LOC'),
                        ];
                        
                        $outstandVal = $calcOutstanding;
                        $stockVal = $calcStock;
                    }
                }

                // Hapus baris sisa di DB jika file Excel baru memiliki jumlah baris lebih sedikit
                if ($rowIndex < $existingCount) {
                    for ($k = $rowIndex; $k < $existingCount; $k++) {
                        $existingRows[$k]->delete();
                    }
                }

                // Olah data duplikat untuk disimpan di session agar dapat ditampilkan notifikasi pop-up
                $duplicateItemCodes = [];
                foreach ($codeOccurrences as $compKey => $occurrences) {
                    if (count($occurrences) > 1) {
                        $compParts = explode('___', $compKey);
                        $itemCodeDisp = $compParts[0];
                        $factoryDisp  = $compParts[1];
                        $suppDisp     = $compParts[2] ?? '';
                        $dispCode     = $itemCodeDisp . ' [' . $factoryDisp . ($suppDisp !== '' ? ' - ' . $suppDisp : '') . ']';
                        $duplicateItemCodes[] = [
                            'code'         => $dispCode,
                            'count'        => count($occurrences),
                            'rows'         => array_column($occurrences, 'row'),
                            'descriptions' => array_values(array_unique(array_column($occurrences, 'desc'))),
                        ];
                    }
                }

                if (!empty($duplicateItemCodes)) {
                    session()->flash('import_duplicates_found', $duplicateItemCodes);
                } else {
                    session()->forget('import_duplicates_found');
                }

                // Hapus data yatim di tabel Forecasting
                $validPartNumbers = PurchasingOutstanding::pluck('part_number')->map(fn($x) => strtoupper(trim($x)))->filter()->toArray();
                if (!empty($validPartNumbers)) {
                    \App\Models\Forecasting::whereNotIn('part_number', $validPartNumbers)->delete();
                }
                
                // ──────────────────────────────────────────────────────────
                // BATCH SYNC: Simpan semua forecast data ke tabel forecastings
                // ──────────────────────────────────────────────────────────
                \Illuminate\Support\Facades\DB::table('forecastings')->delete();

                $aggregatedForecasts = [];
                foreach ($forecastingBatch as $fcData) {
                    $key = $fcData['part_number'] . '___' . ($fcData['factory_code'] ?? 'KIP 1') . '___' . strtoupper(trim($fcData['supplier_name'] ?? '')) . '___' . $fcData['periode'];
                    if (!isset($aggregatedForecasts[$key])) {
                        $aggregatedForecasts[$key] = $fcData;
                    } else {
                        $aggregatedForecasts[$key]['po'] += $fcData['po'];
                        $aggregatedForecasts[$key]['po_qty'] += $fcData['po_qty'];
                        $aggregatedForecasts[$key]['production'] += $fcData['production'];
                        $aggregatedForecasts[$key]['production_qty'] += $fcData['production_qty'];
                        $aggregatedForecasts[$key]['delivery'] += $fcData['delivery'];
                        $aggregatedForecasts[$key]['forecast_qty'] += $fcData['forecast_qty'];
                        $aggregatedForecasts[$key]['outstanding'] += $fcData['outstanding'];
                        $aggregatedForecasts[$key]['stock'] += $fcData['stock'];
                        $aggregatedForecasts[$key]['stock_qty'] += $fcData['stock_qty'];
                    }
                }

                $userId = \Illuminate\Support\Facades\Auth::id();
                $now = now();
                foreach ($aggregatedForecasts as $fcData) {
                    \Illuminate\Support\Facades\DB::table('forecastings')->updateOrInsert(
                        [
                            'part_number'  => $fcData['part_number'],
                            'period_month' => $fcData['period_month'],
                        ],
                        array_merge($fcData, [
                            'user_id'    => $userId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])
                    );
                    $forecastSyncCount++;
                }
            });

            PurchasingOutstanding::clearCalcCaches();

            // ==============================================================
            // 7. SET SESSION MONITOR — Auto-sync bulan/tahun dari Excel
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

