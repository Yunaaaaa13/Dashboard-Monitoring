<?php

namespace App\Http\Controllers;

use App\Models\Forecasting;
use App\Models\Actual;
use App\Models\Outstanding;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    /**
     * Halaman utama Modul Analisis Purchasing.
     */
    public function index(Request $request)
    {
        @set_time_limit(300);
        \App\Models\PurchasingOutstanding::clearCalcCaches();

        // ═══════════════════════════════════════════════════════════════════
        // INDEPENDENT FILTER STATE MANAGEMENT PER SLIDE
        // ═══════════════════════════════════════════════════════════════════
        $resetSlide = $request->get('reset_slide');
        if ($resetSlide === 'slide1') {
            session()->forget(['s1_item_code', 's1_vendor', 's1_pic', 's1_po', 's1_delivery_category', 's1_year', 's1_duration']);
        } elseif ($resetSlide === 'slide2') {
            session()->forget(['s2_item_code', 's2_vendor', 's2_pic', 's2_po', 's2_delivery_category', 's2_year', 's2_duration']);
        } elseif ($resetSlide === 'slide3') {
            session()->forget(['s3_item_code', 's3_vendor', 's3_pic', 's3_po', 's3_delivery_category', 's3_year', 's3_duration']);
        }

        $activeSlide = $request->get('active_slide', session('analysis_active_slide', 'slide1'));
        if (!in_array($activeSlide, ['slide1', 'slide2', 'slide3'], true)) {
            $activeSlide = 'slide1';
        }
        session(['analysis_active_slide' => $activeSlide]);

        $availableYears = [2025, 2026, 2027, 2028];

        // Auto-detect default duration based on active forecast horizon in DB
        $maxForecastPeriods = \App\Models\Forecasting::distinct()->count('periode');
        $defaultDuration = ($maxForecastPeriods > 0 && $maxForecastPeriods <= 36) ? $maxForecastPeriods : 8;

        // Slide 1 Filters
        $s1_item_code = ($resetSlide === 'slide1') ? 'ALL' : ($request->has('s1_item_code') ? strtoupper(trim((string)$request->get('s1_item_code'))) : ($request->has('item_code') ? strtoupper(trim((string)$request->get('item_code'))) : 'ALL'));
        $s1_vendor = ($resetSlide === 'slide1') ? 'ALL' : ($request->has('s1_vendor') ? trim((string)$request->get('s1_vendor')) : ($request->has('vendor') ? trim((string)$request->get('vendor')) : 'ALL'));
        $s1_pic = ($resetSlide === 'slide1') ? 'ALL' : ($request->has('s1_pic') ? trim((string)$request->get('s1_pic')) : ($request->has('pic') ? trim((string)$request->get('pic')) : 'ALL'));
        $s1_po = ($resetSlide === 'slide1') ? 'ALL' : ($request->has('s1_po') ? strtoupper(trim((string)$request->get('s1_po'))) : ($request->has('po') ? strtoupper(trim((string)$request->get('po'))) : 'ALL'));
        $s1_delivery_category = ($resetSlide === 'slide1') ? 'ALL' : ($request->has('s1_delivery_category') ? strtoupper(trim((string)$request->get('s1_delivery_category'))) : ($request->has('delivery_category') ? strtoupper(trim((string)$request->get('delivery_category'))) : 'ALL'));
        if (!in_array($s1_delivery_category, ['ALL', 'LOC', 'IMP', 'CON'], true)) $s1_delivery_category = 'ALL';
        $s1_year = ($resetSlide === 'slide1') ? '2026' : ($request->has('s1_year') ? $request->get('s1_year') : ($request->has('year') ? $request->get('year') : '2026'));
        $s1_duration = ($resetSlide === 'slide1') ? $defaultDuration : max(1, min(36, (int)($request->has('s1_duration') ? $request->get('s1_duration') : ($request->has('duration') ? $request->get('duration') : $defaultDuration))));

        // Slide 2 Filters
        $s2_item_code = ($resetSlide === 'slide2') ? 'ALL' : ($request->has('s2_item_code') ? strtoupper(trim((string)$request->get('s2_item_code'))) : ($request->has('item_code') ? strtoupper(trim((string)$request->get('item_code'))) : 'ALL'));
        $s2_vendor = ($resetSlide === 'slide2') ? 'ALL' : ($request->has('s2_vendor') ? trim((string)$request->get('s2_vendor')) : ($request->has('vendor') ? trim((string)$request->get('vendor')) : 'ALL'));
        $s2_pic = ($resetSlide === 'slide2') ? 'ALL' : ($request->has('s2_pic') ? trim((string)$request->get('s2_pic')) : ($request->has('pic') ? trim((string)$request->get('pic')) : 'ALL'));
        $s2_po = ($resetSlide === 'slide2') ? 'ALL' : ($request->has('s2_po') ? strtoupper(trim((string)$request->get('s2_po'))) : ($request->has('po') ? strtoupper(trim((string)$request->get('po'))) : 'ALL'));
        $s2_delivery_category = ($resetSlide === 'slide2') ? 'ALL' : ($request->has('s2_delivery_category') ? strtoupper(trim((string)$request->get('s2_delivery_category'))) : ($request->has('delivery_category') ? strtoupper(trim((string)$request->get('delivery_category'))) : 'ALL'));
        if (!in_array($s2_delivery_category, ['ALL', 'LOC', 'IMP', 'CON'], true)) $s2_delivery_category = 'ALL';
        $s2_year = ($resetSlide === 'slide2') ? '2026' : ($request->has('s2_year') ? $request->get('s2_year') : ($request->has('year') ? $request->get('year') : '2026'));
        $s2_duration = ($resetSlide === 'slide2') ? $defaultDuration : max(1, min(36, (int)($request->has('s2_duration') ? $request->get('s2_duration') : ($request->has('duration') ? $request->get('duration') : $defaultDuration))));

        // Slide 3 Filters (Strictly Independent - Defaults to ALL / Default Horizon)
        $s3_item_code = ($resetSlide === 'slide3') ? 'ALL' : ($request->has('s3_item_code') ? strtoupper(trim((string)$request->get('s3_item_code'))) : ($request->has('item_code') ? strtoupper(trim((string)$request->get('item_code'))) : 'ALL'));
        $s3_vendor = ($resetSlide === 'slide3') ? 'ALL' : ($request->has('s3_vendor') ? trim((string)$request->get('s3_vendor')) : ($request->has('vendor') ? trim((string)$request->get('vendor')) : 'ALL'));
        $s3_pic = ($resetSlide === 'slide3') ? 'ALL' : ($request->has('s3_pic') ? trim((string)$request->get('s3_pic')) : ($request->has('pic') ? trim((string)$request->get('pic')) : 'ALL'));
        $s3_po = ($resetSlide === 'slide3') ? 'ALL' : ($request->has('s3_po') ? strtoupper(trim((string)$request->get('s3_po'))) : ($request->has('po') ? strtoupper(trim((string)$request->get('po'))) : 'ALL'));
        $s3_delivery_category = ($resetSlide === 'slide3') ? 'ALL' : ($request->has('s3_delivery_category') ? strtoupper(trim((string)$request->get('s3_delivery_category'))) : ($request->has('delivery_category') ? strtoupper(trim((string)$request->get('delivery_category'))) : 'ALL'));
        if (!in_array($s3_delivery_category, ['ALL', 'LOC', 'IMP', 'CON'], true)) $s3_delivery_category = 'ALL';
        $s3_year = ($resetSlide === 'slide3') ? '2026' : ($request->has('s3_year') ? $request->get('s3_year') : ($request->has('year') ? $request->get('year') : '2026'));
        $s3_duration = ($resetSlide === 'slide3') ? $defaultDuration : max(1, min(36, (int)($request->has('s3_duration') ? $request->get('s3_duration') : ($request->has('duration') ? $request->get('duration') : $defaultDuration))));

        // Global / Primary Fallbacks
        $selectedItemCode = $s1_item_code;
        $selectedVendor   = $s1_vendor;
        $selectedPic      = $s1_pic;
        $selectedPo       = $s1_po;
        $selectedDeliveryCategory = $s1_delivery_category;
        $selectedYear     = $s1_year;
        $duration         = $s1_duration;

        $calcYearBase = ($selectedYear === 'ALL') ? 2026 : (int)$selectedYear;

        $detectedFirstPeriod = \App\Models\Forecasting::where('forecast_qty', '>', 0)->orderBy('periode', 'asc')->value('periode')
            ?: \App\Models\PurchasingLog::orderBy('period_month', 'asc')->value('period_month');

        if (!$detectedFirstPeriod) {
            $prodDate = \App\Models\ActualProduction::orderBy('tanggal_produksi', 'asc')->value('tanggal_produksi');
            if ($prodDate instanceof \DateTimeInterface) {
                $detectedFirstPeriod = $prodDate->format('Y-m');
            } elseif (is_string($prodDate) && !empty($prodDate)) {
                $detectedFirstPeriod = substr(trim($prodDate), 0, 7);
            }
        }

        if (!$detectedFirstPeriod) {
            $detectedFirstPeriod = \App\Models\Forecasting::orderBy('periode', 'asc')->value('periode');
        }

        $allMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $mMap = ['JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12];

        if ($detectedFirstPeriod && preg_match('/^(\d{4})-(\d{2})$/', $detectedFirstPeriod, $mMatches)) {
            $detectedFirstMonth = (int)$mMatches[2]; // e.g. 6 (JUN)
            $detectedBaseMonthNum = ($detectedFirstMonth === 1) ? 12 : ($detectedFirstMonth - 1); // 5 (MAY)
            $defaultStartMonth = $allMonths[$detectedBaseMonthNum - 1]; // 'MAY'
        } else {
            $defaultStartMonth = 'MAY';
        }

        $rawStartMonth = $request->get('start_month', $defaultStartMonth);
        if ($rawStartMonth === 'ALL' || empty($rawStartMonth)) $rawStartMonth = $defaultStartMonth;
        if ($rawStartMonth === 'JULY') $rawStartMonth = 'JUL';
        session(['monitor_start_month' => $rawStartMonth]);

        $startMonthNum = $mMap[$rawStartMonth] ?? 5; // Default to MAY (5)
        $startMonth = $allMonths[$startMonthNum - 1];
        $startIndex = $startMonthNum - 1; // Month 0 index (Base month, e.g. 4 for MAY)

        $months = [];
        for ($i = 0; $i <= 36; $i++) {
            $months[$i] = $allMonths[($startIndex + $i) % 12];
        }

        // Always load all items to build the complete master grid, allowing independent filtering per slide
        $allItems = \App\Models\PurchasingOutstanding::with(['category.buyer', 'user'])->get();

        // For focused analysis, only load related auxiliary rows. Keeping this
        // database-side prevents a single-item drill-down from hydrating an
        // entire history of receipts, stock, and forecast records.
        $relatedItemKeys = $allItems->flatMap(fn ($item) => [
            strtoupper(trim((string) $item->part_number)),
            strtoupper(trim((string) $item->drawing)),
            strtoupper(trim((string) $item->po_number)),
        ])->filter(fn ($key) => $key !== '' && $key !== '-')->unique()->values();
        $canScopeRelatedData = $relatedItemKeys->isNotEmpty() && $relatedItemKeys->count() <= 1000;

        // Load actual receipts & target orders (Step 3) with selected columns for memory efficiency
        $allLogsQuery = \App\Models\PurchasingLog::select([
            'id', 'item_code', 'po_reference', 'receipt_date', 'period_month', 
            'target_order', 'actual_received', 'price', 'currency', 'supplier_name', 'user_id'
        ])->with('user:id,name');
        if ($canScopeRelatedData) {
            $allLogsQuery->where(function ($query) use ($relatedItemKeys) {
                $query->whereIn('item_code', $relatedItemKeys)
                    ->orWhereIn('po_reference', $relatedItemKeys);
            });
        }
        $allLogs = $allLogsQuery->get();
        $actualLogs = [];
        $actualTargetOrders = [];
        $uniquePoTargets = [];

        foreach ($allLogs as $log) {
            $itemCode = strtoupper(trim((string)$log->item_code));
            $poRef    = strtoupper(trim((string)$log->po_reference));

            $ym = $this->parseYearMonth($log->receipt_date, $log->period_month);
            $keysToPut = array_filter([$itemCode, $poRef]);
            $periodsToPut = array_filter([$ym]);

            foreach ($keysToPut as $k) {
                foreach ($periodsToPut as $p) {
                    $keyStr = $k . '___' . $p;
                    $actualLogs[$keyStr] = ($actualLogs[$keyStr] ?? 0) + (int)$log->actual_received;
                }
            }

            // Deduplicate target_order by PO reference & primary period to prevent partial shipment multiplication
            $poKey = $poRef ?: $itemCode;
            if ($poKey && !empty($periodsToPut)) {
                $uKey = $poKey . '___' . reset($periodsToPut);
                if (!isset($uniquePoTargets[$uKey])) {
                    $uniquePoTargets[$uKey] = [
                        'item_code'    => $itemCode,
                        'po_ref'       => $poRef,
                        'periods'      => $periodsToPut,
                        'target_order' => (int)$log->target_order,
                    ];
                } else {
                    $uniquePoTargets[$uKey]['target_order'] = max($uniquePoTargets[$uKey]['target_order'], (int)$log->target_order);
                }
            }
        }

        // Aggregate unique PO targets into actualTargetOrders
        foreach ($uniquePoTargets as $uPo) {
            $keysToPut = array_unique(array_filter([$uPo['item_code'], $uPo['po_ref']]));
            foreach ($keysToPut as $k) {
                foreach ($uPo['periods'] as $p) {
                    $keyStr = $k . '___' . $p;
                    $actualTargetOrders[$keyStr] = ($actualTargetOrders[$keyStr] ?? 0) + $uPo['target_order'];
                }
            }
        }

        // ── Integrate MasterPo records into actualTargetOrders ──
        // MasterPo is the source-of-truth for PO quantities per item per period.
        // Group by item_code + period to avoid double-counting within MasterPo itself.
        $masterPoTargets = [];
        $allMasterPoForTargets = \App\Models\MasterPo::select(['item_code', 'po', 'tanggal', 'qty'])->get();
        foreach ($allMasterPoForTargets as $mpo) {
            $mpoItemCode = strtoupper(trim((string) $mpo->item_code));
            $mpoPoRef = strtoupper(trim((string) $mpo->po));
            $mpoYm = !empty($mpo->tanggal) ? substr($mpo->tanggal, 0, 7) : null;
            if (!$mpoItemCode || !$mpoYm) continue;

            $mpoQty = (int) $mpo->qty;
            // Build aggregated PO qty per unique PO line (po+item+period)
            $lineKey = ($mpoPoRef ?: $mpoItemCode) . '|' . $mpoItemCode . '|' . $mpoYm;
            $masterPoTargets[$lineKey] = ($masterPoTargets[$lineKey] ?? 0) + $mpoQty;
        }

        // Merge MasterPo targets into actualTargetOrders
        foreach ($masterPoTargets as $lineKey => $mpoQty) {
            $parts = explode('|', $lineKey);
            $mpoPoRef = $parts[0];
            $mpoItemCode = $parts[1];
            $mpoYm = $parts[2];
            
            $keysToPut = array_unique(array_filter([$mpoItemCode, $mpoPoRef]));
            foreach ($keysToPut as $k) {
                $keyStr = $k . '___' . $mpoYm;
                // Only override if MasterPo has higher qty (PO is source of truth)
                $actualTargetOrders[$keyStr] = max($actualTargetOrders[$keyStr] ?? 0, $mpoQty);
            }
        }

        // Load actual production (Step 5) with selected columns
        $allProdsQuery = \App\Models\ActualProduction::select(['item_code', 'tanggal_produksi', 'qty']);
        if ($canScopeRelatedData) {
            $allProdsQuery->whereIn('item_code', $relatedItemKeys);
        }
        $allProds = $allProdsQuery->get();
        $actualProds = [];
        foreach ($allProds as $prod) {
            $itemCode = strtoupper(trim((string)$prod->item_code));
            if (!$itemCode) continue;

            $ym = $this->parseYearMonth($prod->tanggal_produksi);

            $periodsToPut = array_filter([$ym]);
            if (!empty($ym)) {
                $monthNum = date('m', strtotime($ym . '-01'));
                $periodsToPut[] = $monthNum;
            }
            $periodsToPut = array_unique($periodsToPut);

            foreach ($periodsToPut as $p) {
                $keyStr = $itemCode . '___' . $p;
                $actualProds[$keyStr] = ($actualProds[$keyStr] ?? 0) + (int)$prod->qty;
            }
        }

        // Build lookup maps for supplier & user PIC from PurchasingLog & MasterPo
        $supplierMap = [];
        $userMap = [];

        foreach ($allLogs as $log) {
            $code = strtoupper(trim((string)$log->item_code));
            $ref  = strtoupper(trim((string)$log->po_reference));

            if (!empty($log->supplier_name)) {
                if ($code) $supplierMap[$code] = trim($log->supplier_name);
                if ($ref)  $supplierMap[$ref]  = trim($log->supplier_name);
            }
            if ($log->user) {
                if ($code) $userMap[$code] = $log->user->name;
                if ($ref)  $userMap[$ref]  = $log->user->name;
            }
        }

        $allMasterPosQuery = \App\Models\MasterPo::select(['item_code', 'po', 'supplier']);
        if ($canScopeRelatedData) {
            $allMasterPosQuery->where(function ($query) use ($relatedItemKeys) {
                $query->whereIn('item_code', $relatedItemKeys)
                    ->orWhereIn('po', $relatedItemKeys);
            });
        }
        $allMasterPos = $allMasterPosQuery->get();
        foreach ($allMasterPos as $mpo) {
            $code = strtoupper(trim((string)$mpo->item_code));
            $po   = strtoupper(trim((string)$mpo->po));

            $supName = trim((string)($mpo->supplier ?? ''));
            if (!empty($supName)) {
                if ($code) $supplierMap[$code] = $supName;
                if ($po)   $supplierMap[$po]   = $supName;
            }
        }

        // Base year dynamically follows user selected year
        $baseYear = $selectedYear;

        // Fetch exchange rates prior to comparison grid processing
        // Kurs harus mengikuti periode analisis, termasuk bila horizon melewati tahun berikutnya.
        $selectedYearExchangeRate = $selectedYear;
        $selectedCurrencyExchangeRate = 2; // USD/IDR default

        $rateYears = range($selectedYearExchangeRate, $selectedYearExchangeRate + 3);
        $rawBudgetForecasts = \App\Models\TaxBudgetForecastRate::whereIn('exch_year', $rateYears)
            ->where(function($q) use ($selectedCurrencyExchangeRate) {
                $q->where('currency_code', $selectedCurrencyExchangeRate)
                  ->orWhereNull('currency_code');
            })
            ->get()
            ->keyBy(fn ($rate) => sprintf('%04d-%02d', $rate->exch_year ?: 2026, $rate->exch_month ?: 1));

        $weeklyExchangeRates = \App\Models\TaxExchangeRate::whereIn('exch_year', $rateYears)
            ->where(function($q) use ($selectedCurrencyExchangeRate) {
                $q->where('currency_code', $selectedCurrencyExchangeRate)
                  ->orWhereNull('currency_code');
            })
            ->orderBy('exch_month')
            ->orderBy('week_code')
            ->get()
            ->groupBy(fn ($rate) => sprintf('%04d-%02d', $rate->exch_year ?: 2026, $rate->exch_month ?: 1));

        // High-performance pre-calculation of budget & weekly exchange rates
        $precomputedBudgetRates = [];
        foreach ($rawBudgetForecasts as $ym => $r) {
            $precomputedBudgetRates[$ym] = (float)($r->budget_rate ?: 16600);
        }

        $precomputedAvgRates = [];
        $precomputedWeeklyRates = [];
        foreach ($weeklyExchangeRates as $ym => $coll) {
            $bRate = $precomputedBudgetRates[$ym] ?? 16600;
            $precomputedAvgRates[$ym] = (float)($coll->avg('tax_exchange_rate') ?: $bRate);
            foreach ($coll as $w) {
                if ($w->tax_exchange_rate > 0) {
                    $precomputedWeeklyRates[$ym][(int)$w->week_code] = (float)$w->tax_exchange_rate;
                }
            }
        }

        // Precompute calendar months mapping for 0..36
        $precomputedCalendarMonths = [];
        for ($i = 0; $i <= 37; $i++) {
            $precomputedCalendarMonths[$i] = $this->getCalendarMonthForIndex($i, $startMonth, $baseYear);
        }

        // Fetch forecasting prices for fallback matching
        $forecastingQuery = \App\Models\Forecasting::where('price', '>', 0);
        if ($canScopeRelatedData) {
            $forecastingQuery->whereIn('part_number', $relatedItemKeys);
        }
        $forecastingMap = $forecastingQuery->get()->keyBy(fn($f) => strtoupper(trim($f->part_number)));

        // High-performance pre-indexing of physical delivery logs to prevent N*M*K nested loop slowdown
        $logsByCodeAndPeriod = [];
        $allLogsByCode = [];
        foreach ($allLogs as $l) {
            $cCode = strtoupper(trim((string)$l->item_code));
            $cPo   = strtoupper(trim((string)$l->po_reference));

            $ymLog = $this->parseYearMonth($l->receipt_date, $l->period_month);
            $mNameLog = !empty($l->period_month) ? strtoupper(trim($l->period_month)) : null;
            $logMonthNum = !empty($ymLog) ? date('m', strtotime($ymLog . '-01')) : null;

            $logData = [
                'id'              => $l->id,
                'receipt_date'    => $l->receipt_date ? date('d/m/Y', strtotime($l->receipt_date)) : $l->period_month,
                'raw_receipt_date'=> $l->receipt_date,
                'currency'        => $l->currency ?: 'USD',
                'po_reference'    => $l->po_reference ?: '-',
                'supplier_name'   => $l->supplier_name ?: 'PT SURYARAYA NUSATAMA',
                'target_order'    => (int) $l->target_order,
                'actual_received' => (int) $l->actual_received,
                'price'           => (float) $l->price,
                'amount'          => (float) ($l->actual_received * $l->price),
                'user_name'       => $l->user->name ?? 'System',
            ];

            $keys = array_unique(array_filter([$cCode, $cPo]));
            foreach ($keys as $k) {
                if ($ymLog)       $logsByCodeAndPeriod[$k . '___' . $ymLog][] = $logData;
                if ($mNameLog)    $logsByCodeAndPeriod[$k . '___' . $mNameLog][] = $logData;
                if ($logMonthNum) $logsByCodeAndPeriod[$k . '___' . $logMonthNum][] = $logData;
                $allLogsByCode[$k][$l->id] = $logData;
            }
        }

        // Load Step 6 Actual Inventories with selected columns
        $allInventoriesQuery = \App\Models\Inventory::select(['id', 'part_number', 'drawing', 'description', 'supplier_name', 'category_id', 'factory_code', 'm0_inventory', 'current_stock', 'unit_price', 'currency']);
        if ($canScopeRelatedData) {
            $allInventoriesQuery->where(function ($query) use ($relatedItemKeys) {
                $query->whereIn('part_number', $relatedItemKeys)
                    ->orWhereIn('drawing', $relatedItemKeys);
            });
        }
        $allInventories = $allInventoriesQuery->get();
        $inventoriesByCode = [];
        foreach ($allInventories as $inv) {
            $code = strtoupper(trim((string)$inv->part_number));
            if ($code) {
                $inventoriesByCode[$code] = $inv;
            }
        }

        // Compile forecast and actual multi-month grids
        $comparisonGrid = $allItems->map(function($item) use ($startIndex, $startMonth, $selectedYear, $baseYear, $duration, $actualLogs, $actualTargetOrders, $actualProds, $supplierMap, $userMap, $forecastingMap, $logsByCodeAndPeriod, $allLogsByCode, $precomputedBudgetRates, $precomputedAvgRates, $precomputedWeeklyRates, $precomputedCalendarMonths, $inventoriesByCode) {
            $rawAttrs = $item->getAttributes();
            $partNoClean  = strtoupper(trim((string)($rawAttrs['part_number'] ?? '')));
            $drawingClean = strtoupper(trim((string)($rawAttrs['drawing'] ?? '')));
            $poNumClean   = strtoupper(trim((string)($rawAttrs['po_number'] ?? '')));
            $descClean    = strtoupper(trim((string)($rawAttrs['description'] ?? '')));

            // Primary Item Code is part_number
            $itemCode = (!empty($partNoClean) && $partNoClean !== '-') ? $partNoClean : $drawingClean;
            
            // Determine Forecast & Actual Prices (Unit price in item's native currency)
            $itemPrice = (float) ($rawAttrs['price'] ?? 0.0);
            $forecastPrice = (isset($forecastingMap[$itemCode]) && (float)$forecastingMap[$itemCode]->price > 0)
                ? (float) $forecastingMap[$itemCode]->price
                : $itemPrice;
            $actualPrice   = $itemPrice > 0 ? $itemPrice : $forecastPrice;
            $priceDeviationReason = $rawAttrs['price_deviation_reason'] ?? null;

            // Auto-detect realistic currency based on unit price magnitude:
            // Unit prices > 300 are 100% IDR (Rupiah) and must be converted to USD using budget rate.
            $rawCurrency = strtoupper($forecastingMap[$itemCode]->currency ?? $rawAttrs['currency'] ?? 'USD');
            if ($forecastPrice > 300) {
                $forecastCurrency = 'IDR';
            } else {
                $forecastCurrency = in_array($rawCurrency, ['USD', 'IDR'], true) ? $rawCurrency : 'USD';
            }
            if ($actualPrice > 300) {
                $itemCurrency = 'IDR';
            } else {
                $itemCurrency = in_array(strtoupper($rawAttrs['currency'] ?? ''), ['USD', 'IDR'], true) ? strtoupper($rawAttrs['currency']) : $forecastCurrency;
            }

            $m0MonthNum = (($startIndex + 0) % 12) + 1;
            $m0Period = $precomputedCalendarMonths[0] ?? $this->getCalendarMonthForIndex(0, $startMonth, $baseYear);
            $m0BudgetRate = $precomputedBudgetRates[$m0Period] ?? 16600.0;
            $m0ActualAvgRate = $precomputedAvgRates[$m0Period] ?? $m0BudgetRate;

            if ($forecastCurrency === 'IDR') {
                $fPrcIdr0 = $forecastPrice;
                $fPrcUsd0 = $m0BudgetRate > 0 ? ($forecastPrice / $m0BudgetRate) : 0.0;
                $aPrcIdr0 = $actualPrice;
                $aPrcUsd0 = $m0ActualAvgRate > 0 ? ($actualPrice / $m0ActualAvgRate) : 0.0;
            } else {
                $fPrcUsd0 = $forecastPrice;
                $fPrcIdr0 = $forecastPrice * $m0BudgetRate;
                $aPrcUsd0 = $actualPrice;
                $aPrcIdr0 = $actualPrice * $m0ActualAvgRate;
            }

            // Pre-filter non-empty lookup keys once per item
            $lookupKeys = array_unique(array_filter([$partNoClean, $drawingClean, $poNumClean, $descClean], fn($k) => !empty($k) && $k !== '-'));

            // Forecast states (Month 0 / Pre-month)
            $forecastRows = [];
            $f0Stock = (int)($rawAttrs['plan_stock'] ?? 0);
            $f0Outstand = (int)($rawAttrs['plan_outstand'] ?? 0);
            $f0NextProd = (int)($rawAttrs['m1_prod'] ?? 0);
            $f0Ratio = $f0NextProd > 0 ? round(($f0Stock / $f0NextProd) * 100) . '%' : '-';

            $forecastRows[0] = (object)[
                'month_name'             => $startMonth,
                'po'                     => 0,
                'forecast'               => 0,
                'delivery'               => 0,
                'outstanding'            => $f0Outstand,
                'prod'                   => 0,
                'stock'                  => $f0Stock,
                'raw_stock'              => $f0Stock,
                'deficit'                => 0,
                'stock_gap'              => $f0Stock,
                'ratio'                  => $f0Ratio,
                'coverage_ratio'         => $f0Ratio,
                'achievement_pct'        => '-',
                'price'                  => $fPrcUsd0,
                'price_usd'              => $fPrcUsd0,
                'price_idr'              => $fPrcIdr0,
                'po_amount'              => 0,
                'po_amount_usd'          => 0,
                'po_amount_idr'          => 0,
                'forecast_amount'        => 0,
                'forecast_amount_usd'    => 0,
                'forecast_amount_idr'    => 0,
                'delivery_amount'        => 0,
                'delivery_amount_usd'    => 0,
                'delivery_amount_idr'    => 0,
                'stock_amount'           => $f0Stock * $fPrcUsd0,
                'stock_amount_usd'       => $f0Stock * $fPrcUsd0,
                'stock_amount_idr'       => $f0Stock * $fPrcIdr0,
                'inventory_amount_usd'   => $f0Stock * $fPrcUsd0,
                'inventory_amount_idr'   => $f0Stock * $fPrcIdr0,
                'outstanding_amount'     => $f0Outstand * $fPrcUsd0,
                'outstanding_amount_usd' => $f0Outstand * $fPrcUsd0,
                'outstanding_amount_idr' => $f0Outstand * $fPrcIdr0,
            ];
            
            $calendarMonth0 = $precomputedCalendarMonths[0] ?? $this->getCalendarMonthForIndex(0, $startMonth, $baseYear);
            $periodKeys0    = array_unique([$calendarMonth0, $startMonth, 'm0']);
            
            $aTargetPoBase = 0;
            $aDelBase      = 0;
            $aProdBase     = 0;

            foreach ($lookupKeys as $lk) {
                foreach ($periodKeys0 as $pk) {
                    $k0 = $lk . '___' . $pk;
                    $aTargetPoBase += (int) ($actualTargetOrders[$k0] ?? 0);
                    $aDelBase      += (int) ($actualLogs[$k0] ?? 0);
                    $aProdBase     += (int) ($actualProds[$k0] ?? 0);
                }
            }

            $initStock = (int)($rawAttrs['plan_stock'] ?? 0);
            $a0NextProd = (int)($rawAttrs['m1_prod'] ?? 0);
            $a0Ratio = $a0NextProd > 0 ? round(($initStock / $a0NextProd) * 100) . '%' : '-';

            $actualRows = [];
            $actualRows[0] = (object)[
                'month_name'             => $startMonth,
                'po'                     => $aTargetPoBase,
                'forecast'               => 0,
                'delivery'               => $aDelBase,
                'delivery_details'       => [],
                'outstanding'            => (int)($rawAttrs['plan_outstand'] ?? 0),
                'prod'                   => $aProdBase,
                'stock'                  => $initStock,
                'raw_stock'              => $initStock,
                'deficit'                => 0,
                'stock_gap'              => $initStock - $a0NextProd,
                'ratio'                  => $a0Ratio,
                'coverage_ratio'         => $a0Ratio,
                'achievement_pct'        => '-',
                'achievement_val'        => null,
                'price'                  => $aPrcUsd0,
                'price_usd'              => $aPrcUsd0,
                'price_idr'              => $aPrcIdr0,
                'po_amount'              => $aTargetPoBase * $aPrcUsd0,
                'po_amount_usd'          => $aTargetPoBase * $aPrcUsd0,
                'po_amount_idr'          => $aTargetPoBase * $aPrcIdr0,
                'forecast_amount'        => 0,
                'forecast_amount_usd'    => 0,
                'forecast_amount_idr'    => 0,
                'delivery_amount'        => $aDelBase * $aPrcUsd0,
                'delivery_amount_usd'    => $aDelBase * $aPrcUsd0,
                'delivery_amount_idr'    => $aDelBase * $aPrcIdr0,
                'incoming_amount_usd'    => $aDelBase * $aPrcUsd0,
                'incoming_amount_idr'    => $aDelBase * $aPrcIdr0,
                'stock_amount'           => $initStock * $aPrcUsd0,
                'stock_amount_usd'       => $initStock * $aPrcUsd0,
                'stock_amount_idr'       => $initStock * $aPrcIdr0,
                'inventory_amount_usd'   => $initStock * $aPrcUsd0,
                'inventory_amount_idr'   => $initStock * $aPrcIdr0,
                'outstanding_amount'     => (int)($rawAttrs['plan_outstand'] ?? 0) * $aPrcUsd0,
                'outstanding_amount_usd' => (int)($rawAttrs['plan_outstand'] ?? 0) * $aPrcUsd0,
                'outstanding_amount_idr' => (int)($rawAttrs['plan_outstand'] ?? 0) * $aPrcIdr0,
                'variance_qty'           => $aDelBase,
                'variance_stock'         => 0,
                'variance_amount_usd'    => 0,
                'variance_amount_idr'    => 0,
                'variance_pct'           => 0,
                'status'                 => 'Sesuai',
            ];

            // Forecast & Actual roll-forward loop (all 36 months using linear O(1) calculations)
            for ($i = 1; $i <= 36; $i++) {
                $calendarMonth = $precomputedCalendarMonths[$i] ?? $this->getCalendarMonthForIndex($i, $startMonth, $baseYear);
                $calcYear = (int) substr($calendarMonth, 0, 4);
                $calcMonthNum = (int) substr($calendarMonth, 5, 2);
                $calcPeriodStr = $calendarMonth;

                $allMonthsList = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                $mName = $allMonthsList[$calcMonthNum - 1];

                $mMonthNum = $calcMonthNum;
                $mBudgetRate = $precomputedBudgetRates[$calendarMonth] ?? 16600.0;
                $mActualAvgRate = $precomputedAvgRates[$calendarMonth] ?? $mBudgetRate;

                // Forecast values directly from imported attributes
                $fForecast  = (int) ($rawAttrs["m{$i}_forecast"] ?? ($rawAttrs["m{$i}_po"] ?? ($rawAttrs["m{$i}_order_qty"] ?? 0)));
                $fPo        = $fForecast;
                $fDelivery  = (int) ($rawAttrs["m{$i}_delivery"] ?? 0);
                $fProd      = (int) ($rawAttrs["m{$i}_prod"] ?? 0);

                // Outstanding (Prev Outstanding + Forecast PO - Delivery)
                $fOutstand  = $forecastRows[$i - 1]->outstanding + $fPo - $fDelivery;

                // Forecast Ending Stock Roll-Forward = Previous Ending Stock + Planned Incoming PO - Planned Production
                $prevFStockRaw = $forecastRows[$i - 1]->raw_stock ?? $forecastRows[$i - 1]->stock;
                $fNetStock     = $prevFStockRaw + $fPo - $fProd;
                $fPhysicalStock = max(0, $fNetStock);
                $fDeficit      = max(0, -$fNetStock);
                $fStockGap     = $fPhysicalStock - $fProd;
                $nextFProd     = (int) ($rawAttrs["m" . ($i + 1) . "_prod"] ?? 0);
                $fRatio        = $nextFProd > 0 ? round(($fPhysicalStock / $nextFProd) * 100) . '%' : '-';

                if ($forecastCurrency === 'IDR') {
                    $fPrcIdr = $forecastPrice;
                    $fPrcUsd = $mBudgetRate > 0 ? ($forecastPrice / $mBudgetRate) : 0.0;
                } else {
                    $fPrcUsd = $forecastPrice;
                    $fPrcIdr = $forecastPrice * $mBudgetRate;
                }

                $forecastRows[$i] = (object)[
                    'month_name'             => $mName,
                    'po'                     => $fPo,
                    'forecast'               => $fForecast,
                    'delivery'               => $fDelivery,
                    'outstanding'            => $fOutstand,
                    'prod'                   => $fProd,
                    'stock'                  => $fPhysicalStock,
                    'raw_stock'              => $fNetStock,
                    'deficit'                => $fDeficit,
                    'stock_gap'              => $fStockGap,
                    'ratio'                  => $fRatio,
                    'coverage_ratio'         => $fRatio,
                    'achievement_pct'        => '100%',
                    'price'                  => $fPrcUsd,
                    'price_usd'              => $fPrcUsd,
                    'price_idr'              => $fPrcIdr,
                    'po_amount'              => $fPo * $fPrcUsd,
                    'po_amount_usd'          => $fPo * $fPrcUsd,
                    'po_amount_idr'          => $fPo * $fPrcIdr,
                    'forecast_amount'        => $fForecast * $fPrcUsd,
                    'forecast_amount_usd'    => $fForecast * $fPrcUsd,
                    'forecast_amount_idr'    => $fForecast * $fPrcIdr,
                    'delivery_amount'        => $fDelivery * $fPrcUsd,
                    'delivery_amount_usd'    => $fDelivery * $fPrcUsd,
                    'delivery_amount_idr'    => $fDelivery * $fPrcIdr,
                    'stock_amount'           => $fPhysicalStock * $fPrcUsd,
                    'stock_amount_usd'       => $fPhysicalStock * $fPrcUsd,
                    'stock_amount_idr'       => $fPhysicalStock * $fPrcIdr,
                    'inventory_amount_usd'   => $fPhysicalStock * $fPrcUsd,
                    'inventory_amount_idr'   => $fPhysicalStock * $fPrcIdr,
                    'outstanding_amount'     => $fOutstand * $fPrcUsd,
                    'outstanding_amount_usd' => $fOutstand * $fPrcUsd,
                    'outstanding_amount_idr' => $fOutstand * $fPrcIdr,
                ];

                // Actual values from Step 3 Realisasi Logs & Step 5 Production (strictly matched by YYYY-MM)
                $periodKeys = [$calendarMonth];

                $aPo       = 0;
                $aDelivery = 0;
                $aProd     = 0;

                $matchedPoVals   = [];
                $matchedDelVals  = [];
                $matchedProdVals = [];

                foreach ($lookupKeys as $lk) {
                    foreach ($periodKeys as $pk) {
                        $kCur = $lk . '___' . $pk;
                        if (isset($actualTargetOrders[$kCur])) {
                            $matchedPoVals[] = (int) $actualTargetOrders[$kCur];
                        }
                        if (isset($actualLogs[$kCur])) {
                            $matchedDelVals[] = (int) $actualLogs[$kCur];
                        }
                        if (isset($actualProds[$kCur])) {
                            $matchedProdVals[] = (int) $actualProds[$kCur];
                        }
                    }
                }

                $aPo       = !empty($matchedPoVals) ? max($matchedPoVals) : 0;
                $aDelivery = !empty($matchedDelVals) ? max($matchedDelVals) : 0;
                $aProd     = !empty($matchedProdVals) ? max($matchedProdVals) : 0;

                // Outstanding (Prev Outstanding + Target PO - Delivery Received)
                $aOutstand = $actualRows[$i - 1]->outstanding + $aPo - $aDelivery;

                // Forecast Target from Master Forecast
                $aForecast = $fForecast;

                // Actual Ending Stock Roll-Forward = Previous Ending Stock + Delivery Received - Actual Production
                $itemDirectInv = (int) ($rawAttrs["m{$i}_inventory"] ?? 0);
                $prevAStockRaw = $actualRows[$i - 1]->raw_stock ?? $actualRows[$i - 1]->stock;
                $aNetStock     = ($itemDirectInv > 0) ? $itemDirectInv : ($prevAStockRaw + $aDelivery - $aProd);
                $aPhysicalStock = max(0, $aNetStock);
                $aDeficit      = max(0, -$aNetStock);
                $aStockGap     = $aPhysicalStock - $aProd;
                $aStock        = $aPhysicalStock;

                // Actual Stock Coverage Ratio = stock / next month's actual prod
                $nextCalendarMonth = $precomputedCalendarMonths[$i + 1] ?? $this->getCalendarMonthForIndex($i + 1, $startMonth, $baseYear);
                $nextPeriodKeys    = [$nextCalendarMonth];

                $nextAProd = 0;
                foreach ($lookupKeys as $lk) {
                    foreach ($nextPeriodKeys as $pk) {
                        $kNext = $lk . '___' . $pk;
                        $nextAProd += (int) ($actualProds[$kNext] ?? 0);
                    }
                }
                if ($nextAProd <= 0) {
                    $nextAProd = (int) ($rawAttrs["m" . ($i + 1) . "_prod"] ?? 0);
                }
                $aCoverage = $nextAProd > 0 ? round(($aStock / $nextAProd) * 100) . '%' : '-';

                // Achievement % (Realisasi Incoming vs Target Forecast Demand)
                if ($aForecast > 0) {
                    $aAchievementVal = round(($aDelivery / $aForecast) * 100, 1);
                    $aAchievementStr = $aAchievementVal . '%';
                } elseif ($aForecast == 0 && $aDelivery == 0) {
                    $aAchievementVal = null;
                    $aAchievementStr = '-';
                } else {
                    $aAchievementVal = null;
                    $aAchievementStr = 'Unplanned';
                }

                // Detailed breakdown of physical delivery receipts for this month using ultra-fast pre-indexed map
                $deliveryDetailsMap = [];
                foreach ($lookupKeys as $lk) {
                    $kStr = $lk . '___' . $calendarMonth;
                    if (isset($logsByCodeAndPeriod[$kStr])) {
                        foreach ($logsByCodeAndPeriod[$kStr] as $det) {
                            $deliveryDetailsMap[$det['id']] = $det;
                        }
                    }
                }
                $deliveryDetails = array_values($deliveryDetailsMap);

                $aDelAmtUsd = 0.0;
                $aDelAmtIdr = 0.0;

                if (!empty($deliveryDetails)) {
                    foreach ($deliveryDetails as $det) {
                        $dQty = (int)($det['actual_received'] ?? 0);
                        $dPrc = (float)($det['price'] ?? 0);
                        $dCurr = strtoupper(trim($det['currency'] ?? 'USD'));
                        $dDate = $det['raw_receipt_date'] ?? null;

                        // Check weekly rate match for specific receipt date using fast array lookup
                        $logRate = 0.0;
                        if (!empty($dDate)) {
                            $dayNum = (int) substr($dDate, 8, 2);
                            $wCode = (int) min(5, max(1, ceil($dayNum / 7)));
                            $logRate = $precomputedWeeklyRates[$calendarMonth][$wCode] ?? 0.0;
                        }
                        if ($logRate <= 0) {
                            $logRate = $mActualAvgRate > 0 ? $mActualAvgRate : $mBudgetRate;
                        }

                        if ($dCurr === 'IDR' || $dPrc > 300) {
                            $prcIdr = $dPrc;
                            $prcUsd = $logRate > 0 ? ($dPrc / $logRate) : ($dPrc / 16600);
                        } else {
                            $prcUsd = $dPrc;
                            $prcIdr = $dPrc * $logRate;
                        }

                        $aDelAmtUsd += $dQty * $prcUsd;
                        $aDelAmtIdr += $dQty * $prcIdr;
                    }
                    $aPrcUsd = $aDelivery > 0 ? ($aDelAmtUsd / $aDelivery) : $actualPrice;
                    $aPrcIdr = $aDelivery > 0 ? ($aDelAmtIdr / $aDelivery) : ($actualPrice * $mActualAvgRate);
                } else {
                    if ($itemCurrency === 'IDR' || $actualPrice > 300) {
                        $aPrcIdr = $actualPrice;
                        $aPrcUsd = $mActualAvgRate > 0 ? ($actualPrice / $mActualAvgRate) : 0.0;
                    } else {
                        $aPrcUsd = $actualPrice;
                        $aPrcIdr = $actualPrice * $mActualAvgRate;
                    }
                    $aDelAmtUsd = $aDelivery * $aPrcUsd;
                    $aDelAmtIdr = $aDelivery * $aPrcIdr;
                }

                // Inventory Amount = Actual Ending Inventory (Stock) * Inventory Price
                $actInvAmtUsd = $aStock * $aPrcUsd;
                $actInvAmtIdr = $aStock * $aPrcIdr;
                $fcAmtUsd     = $aForecast * $fPrcUsd;
                $fcAmtIdr     = $aForecast * $fPrcIdr;

                // Variance Calculations
                $qtyVar       = $aDelivery - $aForecast;
                $stockVar     = $aStock - $fPhysicalStock;
                $amtVarUsd    = $actInvAmtUsd - $fcAmtUsd;
                $amtVarIdr    = $actInvAmtIdr - $fcAmtIdr;

                if ($fcAmtUsd > 0) {
                    $varPct = round(($amtVarUsd / $fcAmtUsd) * 100, 1);
                } elseif ($aForecast > 0) {
                    $varPct = round(($qtyVar / $aForecast) * 100, 1);
                } else {
                    $varPct = 0.0;
                }

                // Status Monitoring
                if ($aForecast == 0 && $aDelivery == 0) {
                    $statusMon = 'No Demand';
                } elseif ($aForecast == 0 && $aDelivery > 0) {
                    $statusMon = 'Unplanned';
                } elseif (abs($aDelivery - $aForecast) <= max(1, 0.05 * $aForecast)) {
                    $statusMon = 'Sesuai';
                } elseif ($aDelivery < $aForecast) {
                    $statusMon = 'Under Forecast';
                } else {
                    $statusMon = 'Over Forecast';
                }

                $actualRows[$i] = (object)[
                    'month_name'             => $mName,
                    'po'                     => $aPo,
                    'forecast'               => $aForecast,
                    'delivery'               => $aDelivery,
                    'delivery_details'       => $deliveryDetails,
                    'outstanding'            => $aOutstand,
                    'prod'                   => $aProd,
                    'stock'                  => $aPhysicalStock,
                    'raw_stock'              => $aNetStock,
                    'deficit'                => $aDeficit,
                    'stock_gap'              => $aStockGap,
                    'ratio'                  => $aAchievementStr,
                    'achievement_pct'        => $aAchievementStr,
                    'achievement_val'        => $aAchievementVal,
                    'coverage_ratio'         => $aCoverage,
                    'price'                  => $aPrcUsd,
                    'price_usd'              => $aPrcUsd,
                    'price_idr'              => $aPrcIdr,
                    'po_amount'              => $aPo * $aPrcUsd,
                    'po_amount_usd'          => $aPo * $aPrcUsd,
                    'po_amount_idr'          => $aPo * $aPrcIdr,
                    'forecast_amount'        => $fcAmtUsd,
                    'forecast_amount_usd'    => $fcAmtUsd,
                    'forecast_amount_idr'    => $fcAmtIdr,
                    'delivery_amount'        => $aDelAmtUsd,
                    'delivery_amount_usd'    => $aDelAmtUsd,
                    'delivery_amount_idr'    => $aDelAmtIdr,
                    'incoming_amount_usd'    => $aDelAmtUsd,
                    'incoming_amount_idr'    => $aDelAmtIdr,
                    'stock_amount'           => $actInvAmtUsd,
                    'stock_amount_usd'       => $actInvAmtUsd,
                    'stock_amount_idr'       => $actInvAmtIdr,
                    'inventory_amount_usd'   => $actInvAmtUsd,
                    'inventory_amount_idr'   => $actInvAmtIdr,
                    'outstanding_amount'     => $aOutstand * $aPrcUsd,
                    'outstanding_amount_usd' => $aOutstand * $aPrcUsd,
                    'outstanding_amount_idr' => $aOutstand * $aPrcIdr,
                    'variance_qty'           => $qtyVar,
                    'variance_stock'         => $stockVar,
                    'variance_amount_usd'    => $amtVarUsd,
                    'variance_amount_idr'    => $amtVarIdr,
                    'variance_pct'           => $varPct,
                    'status'                 => $statusMon,
                ];
            }

            // Calculate actual ratio for month 0 using Month 1 actual production
            $m1AProd = $actualRows[1]->prod > 0 ? $actualRows[1]->prod : (int)($rawAttrs['m1_prod'] ?? 0);
            $actualRows[0]->coverage_ratio = $m1AProd > 0 ? round(($actualRows[0]->stock / $m1AProd) * 100) . '%' : '-';

            // Find category, supplier, and PIC Buyer with complete fallback chain
            $categoryId = $item->category_id;
            $categoryCode = $item->category ? $item->category->category_code : null;
            $categoryName = $item->category ? $item->category->category_name : 'Tanpa Kategori';

            $supplierName = $rawAttrs['supplier_name'] ?? null;
            if (empty($supplierName)) {
                $supplierName = $supplierMap[$partNoClean] ?? ($supplierMap[$drawingClean] ?? '-');
            }

            // Prioritas penentuan PIC Buyer yang konsisten dan akurat:
            // 1. User penanggung jawab item ($item->user->name)
            // 2. Kolom explicit pic_buyer pada item ($item->pic_buyer)
            // 3. PIC Buyer dari Master Kategori Material ($item->category->buyer->name ?: $item->category->pic_buyer)
            // 4. User penginput log realisasi transaksi ($userMap[$partNoClean] ?? $userMap[$drawingClean])
            // 5. Fallback jika belum teralokasi: '-'
            $picBuyer = $item->user?->name 
                ?: (($rawAttrs['pic_buyer'] ?? null) 
                ?: ($item->category?->buyer?->name 
                ?: ($item->category?->pic_buyer 
                ?: ($userMap[$partNoClean] ?? ($userMap[$drawingClean] ?? '-')))));

            $allItemDetailsMap = [];
            foreach ($lookupKeys as $lk) {
                if (isset($allLogsByCode[$lk])) {
                    foreach ($allLogsByCode[$lk] as $detId => $det) {
                        $allItemDetailsMap[$detId] = $det;
                    }
                }
            }
            $allItemDeliveryDetails = array_values($allItemDetailsMap);

            // Stock Grid for Slide 3 (Stock Forecast vs Actual Stock)
            $invModel = $inventoriesByCode[$partNoClean] ?? ($inventoriesByCode[$drawingClean] ?? null);
            $inventoryRows = [];

            // Month 0 (Pre-Month Baseline Stock)
            $planStockInit = (int) ($rawAttrs['plan_stock'] ?? 0);
            $inv0Qty = $planStockInit;

            $inventoryRows[0] = (object) [
                'month_name'             => $startMonth,
                'stock_qty'              => $inv0Qty,
                'stock_amount_usd'       => $inv0Qty * $fPrcUsd0,
                'stock_amount_idr'       => $inv0Qty * $fPrcIdr0,
                'inventory_qty'          => $inv0Qty,
                'inventory_amount_usd'   => $inv0Qty * $fPrcUsd0,
                'inventory_amount_idr'   => $inv0Qty * $fPrcIdr0,
                'forecast_stock_qty'     => $planStockInit,
                'forecast_stock_usd'     => $planStockInit * $fPrcUsd0,
                'forecast_stock_idr'     => $planStockInit * $fPrcIdr0,
                'variance_qty'           => 0,
                'variance_amount_usd'    => 0.0,
                'variance_amount_idr'    => 0.0,
            ];

            for ($i = 1; $i <= 36; $i++) {
                $fPrcUsdMonth = $forecastRows[$i]->price_usd;
                $fPrcIdrMonth = $forecastRows[$i]->price_idr;

                $fcStockQty = (int) $forecastRows[$i]->stock;
                $fcStockUsd = $fcStockQty * $fPrcUsdMonth;
                $fcStockIdr = $fcStockQty * $fPrcIdrMonth;

                // Check if month $i has actual delivery, production, or physical stock
                $itemDirectInv = (int) ($rawAttrs["m{$i}_inventory"] ?? 0);
                $hasMonthActualData = (($actualRows[$i]->delivery ?? 0) > 0)
                    || (($actualRows[$i]->prod ?? 0) > 0)
                    || ($itemDirectInv > 0);

                if ($hasMonthActualData) {
                    $invMonthQty = ($itemDirectInv > 0) ? $itemDirectInv : (int) $actualRows[$i]->stock;
                    $invMonthAmtUsd = $invMonthQty * $fPrcUsdMonth;
                    $invMonthAmtIdr = $invMonthQty * $fPrcIdrMonth;
                    $varStockQty = $invMonthQty - $fcStockQty;
                    $varStockUsd = $varStockQty * $fPrcUsdMonth;
                    $varStockIdr = $varStockQty * $fPrcIdrMonth;
                } else {
                    $invMonthQty = null;
                    $invMonthAmtUsd = null;
                    $invMonthAmtIdr = null;
                    $varStockQty = null;
                    $varStockUsd = null;
                    $varStockIdr = null;
                }

                $inventoryRows[$i] = (object) [
                    'month_name'             => $forecastRows[$i]->month_name,
                    'has_actual_data'        => $hasMonthActualData,
                    'stock_qty'              => $invMonthQty,
                    'stock_amount_usd'       => $invMonthAmtUsd,
                    'stock_amount_idr'       => $invMonthAmtIdr,
                    'inventory_qty'          => $invMonthQty,
                    'inventory_amount_usd'   => $invMonthAmtUsd,
                    'inventory_amount_idr'   => $invMonthAmtIdr,
                    'forecast_stock_qty'     => $fcStockQty,
                    'forecast_stock_usd'     => $fcStockUsd,
                    'forecast_stock_idr'     => $fcStockIdr,
                    'variance_qty'           => $varStockQty,
                    'variance_amount_usd'    => $varStockUsd,
                    'variance_amount_idr'    => $varStockIdr,
                ];
            }

            return (object)[
                'id'                     => $item->id,
                'item_code'              => $itemCode,
                'drawing'                => $drawingClean,
                'description'            => $item->description,
                'supplier'               => $supplierName,
                'pic_buyer'              => $picBuyer,
                'delivery_category_code' => strtoupper(trim((string) ($item->delivery_category_code ?: 'LOC'))),
                'category_id'            => $categoryId,
                'category_code'          => $categoryCode,
                'category_name'          => $categoryName,
                'forecast_price'         => $forecastPrice,
                'actual_price'           => $actualPrice,
                'price_deviation_reason' => $priceDeviationReason,
                'forecast_grid'          => $forecastRows,
                'actual_grid'            => $actualRows,
                'inventory_grid'         => $inventoryRows,
                'all_delivery_details'   => $allItemDeliveryDetails,
            ];
        });

        // Compute available filter lists
        $availableItemCodes = $comparisonGrid->pluck('item_code')->unique()->sort()->values();
        $availableVendors   = $comparisonGrid->pluck('supplier')
            ->map(fn($s) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($s))
            ->filter()->unique()->sort()->values();
        $availablePics      = $comparisonGrid->pluck('pic_buyer')->filter(fn($x) => $x !== '-')->unique()->sort()->values();
        $availablePoNumbers = \App\Models\MasterPo::pluck('po')->filter()->unique()->sort()->values();

        // ═══════════════════════════════════════════════════════════════════
        // INDEPENDENT DISPLAY GRIDS & ACTIVE FILTER CALCULATION PER SLIDE
        // ═══════════════════════════════════════════════════════════════════
        $filterSlideGrid = function($grid, $itemCode, $vendor, $pic, $po, $deliveryCat) {
            $res = $grid;
            if ($itemCode !== 'ALL') {
                $res = $res->filter(fn($x) => strtoupper($x->item_code) === strtoupper($itemCode) || strtoupper($x->drawing) === strtoupper($itemCode));
            }
            if ($vendor !== 'ALL') {
                $normVendor = \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($vendor);
                $res = $res->filter(fn($x) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($x->supplier) === $normVendor);
            }
            if ($pic !== 'ALL') {
                $res = $res->filter(fn($x) => strtoupper($x->pic_buyer) === strtoupper($pic));
            }
            if ($po !== 'ALL') {
                $poItemCodes = \App\Models\MasterPo::where('po', $po)->pluck('item_code')->map(fn($x) => strtoupper(trim((string)$x)))->toArray();
                $res = $res->filter(fn($x) => in_array(strtoupper($x->item_code), $poItemCodes, true));
            }
            if ($deliveryCat !== 'ALL') {
                $res = $res->filter(fn($x) => $x->delivery_category_code === $deliveryCat);
            }
            return $res->values();
        };

        $displayGridS1 = $filterSlideGrid($comparisonGrid, $s1_item_code, $s1_vendor, $s1_pic, $s1_po, $s1_delivery_category);
        $displayGridS2 = $filterSlideGrid($comparisonGrid, $s2_item_code, $s2_vendor, $s2_pic, $s2_po, $s2_delivery_category);
        $displayGridS3 = $filterSlideGrid($comparisonGrid, $s3_item_code, $s3_vendor, $s3_pic, $s3_po, $s3_delivery_category);
        $allDisplayGridS3 = $filterSlideGrid($comparisonGrid, $s3_item_code, 'ALL', $s3_pic, $s3_po, $s3_delivery_category);

        // Active filter tags & counts
        $getActiveFiltersList = function($itemCode, $vendor, $pic, $po, $deliveryCat, $year, $durationVal, $defDuration) {
            $tags = [];
            if ($itemCode !== 'ALL') $tags[] = ['key' => 'item_code', 'label' => 'Item: ' . $itemCode, 'val' => $itemCode];
            if ($vendor !== 'ALL') $tags[] = ['key' => 'vendor', 'label' => 'Vendor: ' . $vendor, 'val' => $vendor];
            if ($pic !== 'ALL') $tags[] = ['key' => 'pic', 'label' => 'PIC: ' . $pic, 'val' => $pic];
            if ($po !== 'ALL') $tags[] = ['key' => 'po', 'label' => 'PO: ' . $po, 'val' => $po];
            if ($deliveryCat !== 'ALL') $tags[] = ['key' => 'delivery_category', 'label' => 'Pengantaran: ' . $deliveryCat, 'val' => $deliveryCat];
            if ((string)$year !== '2026' && (string)$year !== 'ALL') $tags[] = ['key' => 'year', 'label' => 'Tahun: ' . $year, 'val' => $year];
            if ((int)$durationVal !== (int)$defDuration) $tags[] = ['key' => 'duration', 'label' => 'Durasi: ' . $durationVal . ' Bulan', 'val' => $durationVal];
            return $tags;
        };

        $activeFiltersListS1 = $getActiveFiltersList($s1_item_code, $s1_vendor, $s1_pic, $s1_po, $s1_delivery_category, $s1_year, $s1_duration, $defaultDuration);
        $activeFilterCountS1 = count($activeFiltersListS1);

        $activeFiltersListS2 = $getActiveFiltersList($s2_item_code, $s2_vendor, $s2_pic, $s2_po, $s2_delivery_category, $s2_year, $s2_duration, $defaultDuration);
        $activeFilterCountS2 = count($activeFiltersListS2);

        $activeFiltersListS3 = $getActiveFiltersList($s3_item_code, $s3_vendor, $s3_pic, $s3_po, $s3_delivery_category, $s3_year, $s3_duration, $defaultDuration);
        $activeFilterCountS3 = count($activeFiltersListS3);

        // Aliases for global/legacy compatibility
        $displayGrid = $displayGridS1;

        // Paginators per slide
        $detailPerPage = min(100, max(25, (int) $request->query('per_page', 50)));

        $currentPageS1 = max(1, (int) $request->query('s1_page', $request->query('page', 1)));
        $visibleDisplayGridS1 = new \Illuminate\Pagination\LengthAwarePaginator(
            $displayGridS1->forPage($currentPageS1, $detailPerPage)->values(),
            $displayGridS1->count(),
            $detailPerPage,
            $currentPageS1,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 's1_page']
        );

        $currentPageS2 = max(1, (int) $request->query('s2_page', 1));
        $visibleDisplayGridS2 = new \Illuminate\Pagination\LengthAwarePaginator(
            $displayGridS2->forPage($currentPageS2, $detailPerPage)->values(),
            $displayGridS2->count(),
            $detailPerPage,
            $currentPageS2,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 's2_page']
        );

        $currentPageS3 = max(1, (int) $request->query('s3_page', 1));
        $visibleDisplayGridS3 = new \Illuminate\Pagination\LengthAwarePaginator(
            $displayGridS3->forPage($currentPageS3, $detailPerPage)->values(),
            $displayGridS3->count(),
            $detailPerPage,
            $currentPageS3,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 's3_page']
        );

        $visibleDisplayGrid = $visibleDisplayGridS1;

        // Compute aggregate chart data for up to 36 months based on filtered displayGridS1
        $monthsLabels = [];
        for ($i = 0; $i <= 36; $i++) {
            $mIndex = ($startIndex + $i) % 12;
            $allMonthsList = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            $monthsLabels[] = $allMonthsList[$mIndex];
        }

        $chartForecastPo     = array_fill(0, 37, 0);
        $chartActualPo       = array_fill(0, 37, 0);
        $chartForecastStock  = array_fill(0, 37, 0);
        $chartActualStock    = array_fill(0, 37, 0);
        $chartForecastAmount = array_fill(0, 37, 0.0);
        $chartActualAmount   = array_fill(0, 37, 0.0);
        $chartForecastPrice  = array_fill(0, 37, 0.0);
        $chartActualPrice    = array_fill(0, 37, 0.0);

        $itemCountS1 = max(1, $displayGridS1->count());

        $chartPerItem = [];
        $chartPerPo   = [];

        foreach ($displayGridS1 as $gridItem) {
            $itemCodeKey = strtoupper(trim((string)$gridItem->item_code));

            $itemData = [
                'forecast_po'     => array_fill(0, 37, 0),
                'actual_po'       => array_fill(0, 37, 0),
                'forecast_stock'  => array_fill(0, 37, 0),
                'actual_stock'    => array_fill(0, 37, 0),
                'forecast_amount' => array_fill(0, 37, 0.0),
                'actual_amount'   => array_fill(0, 37, 0.0),
                'forecast_price'  => array_fill(0, 37, 0.0),
                'actual_price'    => array_fill(0, 37, 0.0),
            ];

            for ($i = 0; $i <= 36; $i++) {
                $fFc   = $gridItem->forecast_grid[$i]->forecast ?? 0;
                $fStk  = $gridItem->forecast_grid[$i]->stock ?? 0;
                $fAmt  = $gridItem->forecast_grid[$i]->forecast_amount ?? 0;
                $fPrc  = $gridItem->forecast_grid[$i]->price ?? 0;

                $aDel  = $gridItem->actual_grid[$i]->delivery ?? 0;
                $aStk  = $gridItem->actual_grid[$i]->stock ?? 0;
                $aAmt  = $gridItem->actual_grid[$i]->delivery_amount_usd ?? 0;
                $aPrc  = ($aDel > 0 && isset($gridItem->actual_grid[$i]->price)) 
                            ? (float)$gridItem->actual_grid[$i]->price 
                            : ($aDel > 0 ? (float)($gridItem->actual_price ?? $fPrc) : 0.0);

                // Accumulate totals
                $chartForecastPo[$i]     += $fFc;
                $chartForecastStock[$i]  += $fStk;
                $chartForecastAmount[$i] += $fAmt;

                $chartActualPo[$i]       += $aDel;
                $chartActualStock[$i]    += $aStk;
                $chartActualAmount[$i]   += $aAmt;

                // Per-item array
                $itemData['forecast_po'][$i]     = $fFc;
                $itemData['actual_po'][$i]       = $aDel;
                $itemData['forecast_stock'][$i]  = $fStk;
                $itemData['actual_stock'][$i]    = $aStk;
                $itemData['forecast_amount'][$i] = $fAmt;
                $itemData['actual_amount'][$i]   = $aAmt;
                $itemData['forecast_price'][$i]  = $fPrc;
                $itemData['actual_price'][$i]    = $aPrc;
            }

            if ($itemCodeKey) {
                $chartPerItem[$itemCodeKey] = $itemData;
            }
        }

        // Slide 3 Chart Aggregation: Forecast Stock vs Actual Stock (computed from displayGridS3)
        $chartInvForecastStock     = array_fill(0, 37, 0);
        $chartInvActualStock       = array_fill(0, 37, 0);
        $chartInvForecastAmountUsd = array_fill(0, 37, 0.0);
        $chartInvActualAmountUsd   = array_fill(0, 37, 0.0);
        $chartInvForecastAmountIdr = array_fill(0, 37, 0.0);
        $chartInvActualAmountIdr   = array_fill(0, 37, 0.0);

        // Identify which months have actual transaction data across items in Slide 3
        $monthsWithActualStock = array_fill(0, 37, false);
        foreach ($displayGridS3 as $gridItem) {
            for ($i = 0; $i <= 36; $i++) {
                if (!empty($gridItem->inventory_grid[$i]?->has_actual_data)) {
                    $monthsWithActualStock[$i] = true;
                }
            }
        }
        // Month 0 (Pre-Month Baseline) has initial stock baseline
        $monthsWithActualStock[0] = true;

        foreach ($displayGridS3 as $gridItem) {
            for ($i = 0; $i <= 36; $i++) {
                if (isset($gridItem->inventory_grid[$i])) {
                    $invRow = $gridItem->inventory_grid[$i];
                    $chartInvForecastStock[$i]     += (int) $invRow->forecast_stock_qty;
                    $chartInvForecastAmountUsd[$i] += (float) $invRow->forecast_stock_usd;
                    $chartInvForecastAmountIdr[$i] += (float) $invRow->forecast_stock_idr;

                    if ($monthsWithActualStock[$i]) {
                        $chartInvActualStock[$i]       += (int) ($invRow->stock_qty ?? 0);
                        $chartInvActualAmountUsd[$i]   += (float) ($invRow->stock_amount_usd ?? 0.0);
                        $chartInvActualAmountIdr[$i]   += (float) ($invRow->stock_amount_idr ?? 0.0);
                    }
                }
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // SUPPLIER MONTHLY SUMMARY & MULTI-CURRENCY NORMALIZATION LAYER
        // Aggregates all item codes per supplier per month for Forecast vs Actual comparison.
        // Performs distinct separate aggregation (Supplier + Item Code + Month) to prevent double counting.
        // Normalizes both USD ($) and IDR (Rp) using Budget Rate (Forecast) and Weekly Actual Rate (Incoming).
        // ═══════════════════════════════════════════════════════════════════
        $supplierMonthlySummary = [];  // [supplier_name => [month_index => {metrics}]]
        $supplierTotals         = [];  // [supplier_name => {cumulative totals}]
        $supplierItemMonthlyMap = [];  // [supplier_name => [month_index => [item_code => {item_metrics}]]]

        foreach ($displayGridS2 as $gridItem) {
            $sup = $gridItem->supplier ?: '-';
            $itemCodeKey = strtoupper(trim((string)$gridItem->item_code));
            $itemCurr    = strtoupper(trim((string)($gridItem->currency ?? 'USD')));

            if (!isset($supplierTotals[$sup])) {
                $supplierTotals[$sup] = [
                    'supplier'                  => $sup,
                    'item_count'                => 0,
                    'currencies'                => [],
                    'total_forecast_qty'        => 0,
                    'total_actual_qty'          => 0,
                    'total_forecast_amount_usd' => 0.0,
                    'total_forecast_amount_idr' => 0.0,
                    'total_actual_amount_usd'   => 0.0,
                    'total_actual_amount_idr'   => 0.0,
                    'total_incoming_amount_usd' => 0.0,
                    'total_incoming_amount_idr' => 0.0,
                ];
            }
            $supplierTotals[$sup]['item_count']++;
            if (!in_array($itemCurr, $supplierTotals[$sup]['currencies'], true)) {
                $supplierTotals[$sup]['currencies'][] = $itemCurr;
            }

            for ($i = 1; $i <= $s2_duration; $i++) {
                $fg = $gridItem->forecast_grid[$i] ?? null;
                $ag = $gridItem->actual_grid[$i] ?? null;

                $fQty    = (int) ($fg->forecast ?? 0);
                $aQty    = (int) ($ag->delivery ?? 0);

                // Multi-currency normalized amounts
                $fAmtUsd = (float) ($fg->forecast_amount_usd ?? ($fQty * ($fg->price_usd ?? 0.0)));
                $fAmtIdr = (float) ($fg->forecast_amount_idr ?? ($fQty * ($fg->price_idr ?? 0.0)));

                $aInvAmtUsd = (float) ($ag->inventory_amount_usd ?? (($ag->stock ?? 0) * ($ag->price_usd ?? 0.0)));
                $aInvAmtIdr = (float) ($ag->inventory_amount_idr ?? (($ag->stock ?? 0) * ($ag->price_idr ?? 0.0)));

                $aIncAmtUsd = (float) ($ag->incoming_amount_usd ?? ($aQty * ($ag->price_usd ?? 0.0)));
                $aIncAmtIdr = (float) ($ag->incoming_amount_idr ?? ($aQty * ($ag->price_idr ?? 0.0)));

                if (!isset($supplierMonthlySummary[$sup][$i])) {
                    $supplierMonthlySummary[$sup][$i] = [
                        'month_name'           => $months[$i] ?? ('M' . $i),
                        'month_index'          => $i,
                        'forecast_qty'         => 0,
                        'actual_qty'           => 0,
                        'forecast_amount_usd'  => 0.0,
                        'forecast_amount_idr'  => 0.0,
                        'actual_amount_usd'    => 0.0,
                        'actual_amount_idr'    => 0.0,
                        'incoming_amount_usd'  => 0.0,
                        'incoming_amount_idr'  => 0.0,
                        'item_count'           => 0,
                        'currencies'           => [],
                    ];
                }

                $supplierMonthlySummary[$sup][$i]['forecast_qty']        += $fQty;
                $supplierMonthlySummary[$sup][$i]['actual_qty']          += $aQty;
                $supplierMonthlySummary[$sup][$i]['forecast_amount_usd'] += $fAmtUsd;
                $supplierMonthlySummary[$sup][$i]['forecast_amount_idr'] += $fAmtIdr;
                $supplierMonthlySummary[$sup][$i]['actual_amount_usd']   += $aInvAmtUsd;
                $supplierMonthlySummary[$sup][$i]['actual_amount_idr']   += $aInvAmtIdr;
                $supplierMonthlySummary[$sup][$i]['incoming_amount_usd'] += $aIncAmtUsd;
                $supplierMonthlySummary[$sup][$i]['incoming_amount_idr'] += $aIncAmtIdr;
                $supplierMonthlySummary[$sup][$i]['item_count']++;
                if (!in_array($itemCurr, $supplierMonthlySummary[$sup][$i]['currencies'], true)) {
                    $supplierMonthlySummary[$sup][$i]['currencies'][] = $itemCurr;
                }

                $supplierTotals[$sup]['total_forecast_qty']        += $fQty;
                $supplierTotals[$sup]['total_actual_qty']          += $aQty;
                $supplierTotals[$sup]['total_forecast_amount_usd'] += $fAmtUsd;
                $supplierTotals[$sup]['total_forecast_amount_idr'] += $fAmtIdr;
                $supplierTotals[$sup]['total_actual_amount_usd']   += $aInvAmtUsd;
                $supplierTotals[$sup]['total_actual_amount_idr']   += $aInvAmtIdr;
                $supplierTotals[$sup]['total_incoming_amount_usd'] += $aIncAmtUsd;
                $supplierTotals[$sup]['total_incoming_amount_idr'] += $aIncAmtIdr;

                // Track item-level breakdown per supplier-month for drilldown and contribution analysis
                $supplierItemMonthlyMap[$sup][$i][$itemCodeKey] = [
                    'item_code'           => $gridItem->item_code,
                    'description'         => $gridItem->description ?: '-',
                    'supplier'            => $sup,
                    'currency'            => $itemCurr,
                    'forecast_qty'        => $fQty,
                    'actual_qty'          => $aQty,
                    'forecast_amount_usd' => $fAmtUsd,
                    'forecast_amount_idr' => $fAmtIdr,
                    'incoming_amount_usd' => $aIncAmtUsd,
                    'incoming_amount_idr' => $aIncAmtIdr,
                ];
            }
        }

        // Calculate variance, achievement, and status for each supplier-month (both USD & IDR)
        foreach ($supplierMonthlySummary as $sup => &$monthsData) {
            foreach ($monthsData as $i => &$row) {
                $row['variance_qty']        = $row['actual_qty'] - $row['forecast_qty'];
                $row['variance_amount_usd'] = $row['incoming_amount_usd'] - $row['forecast_amount_usd'];
                $row['variance_amount_idr'] = $row['incoming_amount_idr'] - $row['forecast_amount_idr'];

                if ($row['forecast_qty'] > 0) {
                    $row['achievement_pct'] = round(($row['actual_qty'] / $row['forecast_qty']) * 100, 1) . '%';
                } elseif ($row['actual_qty'] > 0) {
                    $row['achievement_pct'] = 'Unplanned';
                } else {
                    $row['achievement_pct'] = '-';
                }

                if ($row['forecast_qty'] == 0 && $row['actual_qty'] == 0) {
                    $row['status'] = 'No Demand';
                } elseif ($row['forecast_qty'] == 0 && $row['actual_qty'] > 0) {
                    $row['status'] = 'Unplanned';
                } elseif (abs($row['actual_qty'] - $row['forecast_qty']) <= max(1, 0.05 * $row['forecast_qty'])) {
                    $row['status'] = 'Sesuai';
                } elseif ($row['actual_qty'] < $row['forecast_qty']) {
                    $row['status'] = 'Under Forecast';
                } else {
                    $row['status'] = 'Over Forecast';
                }
            }
            unset($row);
        }
        unset($monthsData);

        // Build supplier ranking sorted by total forecast amount USD (descending)
        $grandTotalForecastAmtUsd = max(0.01, array_sum(array_column($supplierTotals, 'total_forecast_amount_usd')));
        $grandTotalForecastAmtIdr = max(1.0, array_sum(array_column($supplierTotals, 'total_forecast_amount_idr')));

        $supplierRanking = collect($supplierTotals)->map(function ($s) use ($grandTotalForecastAmtUsd, $grandTotalForecastAmtIdr) {
            $s['variance_qty']        = $s['total_actual_qty'] - $s['total_forecast_qty'];
            $s['variance_amount_usd'] = $s['total_incoming_amount_usd'] - $s['total_forecast_amount_usd'];
            $s['variance_amount_idr'] = $s['total_incoming_amount_idr'] - $s['total_forecast_amount_idr'];
            $s['achievement_pct']     = $s['total_forecast_qty'] > 0
                ? round(($s['total_actual_qty'] / $s['total_forecast_qty']) * 100, 1) . '%'
                : ($s['total_actual_qty'] > 0 ? 'Unplanned' : '-');
            $s['contribution_pct']    = round(($s['total_forecast_amount_usd'] / $grandTotalForecastAmtUsd) * 100, 1);
            $s['currency_badge']      = implode(', ', array_unique($s['currencies'] ?: ['USD']));
            return (object) $s;
        })->sortByDesc('total_forecast_amount_usd')->values();

        // ── Month-over-Month (MoM) Quantity & Material Contribution Analysis for Slide 2 ──
        $supplierMoMAnalytics = collect();
        for ($i = 1; $i <= $s2_duration; $i++) {
            $mName = $months[$i] ?? ('M' . $i);
            $prevMName = ($i > 1) ? ($months[$i - 1] ?? ('M' . ($i - 1))) : null;

            // Global monthly sums
            $curFcQtySum = 0; $curAcQtySum = 0; $curFcAmtUsd = 0.0; $curAcAmtUsd = 0.0;
            $prevFcQtySum = 0; $prevAcQtySum = 0;

            foreach ($supplierMonthlySummary as $supData) {
                if (isset($supData[$i])) {
                    $curFcQtySum  += $supData[$i]['forecast_qty'];
                    $curAcQtySum  += $supData[$i]['actual_qty'];
                    $curFcAmtUsd  += $supData[$i]['forecast_amount_usd'];
                    $curAcAmtUsd  += $supData[$i]['incoming_amount_usd'];
                }
                if ($i > 1 && isset($supData[$i - 1])) {
                    $prevFcQtySum += $supData[$i - 1]['forecast_qty'];
                    $prevAcQtySum += $supData[$i - 1]['actual_qty'];
                }
            }

            $diffFcQty = ($i > 1) ? ($curFcQtySum - $prevFcQtySum) : 0;
            $diffFcPct = ($i > 1 && $prevFcQtySum > 0) ? round(($diffFcQty / $prevFcQtySum) * 100, 2) : 0.0;

            // Calculate per-supplier delta for this month vs previous month
            $supplierDrivers = [];
            if ($i > 1) {
                foreach ($supplierMonthlySummary as $sName => $sMonths) {
                    $cQty = $sMonths[$i]['forecast_qty'] ?? 0;
                    $pQty = $sMonths[$i - 1]['forecast_qty'] ?? 0;
                    $delta = $cQty - $pQty;
                    if ($delta != 0) {
                        $supplierDrivers[] = [
                            'supplier'    => $sName,
                            'prev_qty'    => $pQty,
                            'curr_qty'    => $cQty,
                            'delta_qty'   => $delta,
                            'abs_delta'   => abs($delta),
                        ];
                    }
                }
                usort($supplierDrivers, fn($a, $b) => $b['abs_delta'] <=> $a['abs_delta']);
            }

            // Calculate per-item delta for this month vs previous month
            $itemDrivers = [];
            if ($i > 1) {
                foreach ($displayGridS2 as $gItem) {
                    $cItemQty = (int)($gItem->forecast_grid[$i]->forecast ?? 0);
                    $pItemQty = (int)($gItem->forecast_grid[$i - 1]->forecast ?? 0);
                    $iDelta = $cItemQty - $pItemQty;
                    if ($iDelta != 0) {
                        $itemDrivers[] = [
                            'item_code'   => $gItem->item_code,
                            'description' => $gItem->description ?: '-',
                            'supplier'    => $gItem->supplier ?: '-',
                            'prev_qty'    => $pItemQty,
                            'curr_qty'    => $cItemQty,
                            'delta_qty'   => $iDelta,
                            'abs_delta'   => abs($iDelta),
                        ];
                    }
                }
                usort($itemDrivers, fn($a, $b) => $b['abs_delta'] <=> $a['abs_delta']);
            }

            $supplierMoMAnalytics->put($i, (object)[
                'month_index'         => $i,
                'month_name'          => $mName,
                'prev_month_name'     => $prevMName,
                'curr_forecast_qty'   => $curFcQtySum,
                'prev_forecast_qty'   => $prevFcQtySum,
                'diff_forecast_qty'   => $diffFcQty,
                'diff_forecast_pct'   => $diffFcPct,
                'curr_actual_qty'     => $curAcQtySum,
                'prev_actual_qty'     => $prevAcQtySum,
                'top_supplier_drivers'=> array_slice($supplierDrivers, 0, 5),
                'top_item_drivers'    => array_slice($itemDrivers, 0, 10),
            ]);
        }

        // ── Data Integrity & Anti-Double Counting Reconciliation Report ──
        $supplierReconciliationPassed = true;
        foreach ($supplierMoMAnalytics as $mIdx => $mAudit) {
            $sumFromItems = (int) $displayGridS2->sum(fn($g) => $g->forecast_grid[$mIdx]->forecast ?? 0);
            if ($sumFromItems !== $mAudit->curr_forecast_qty) {
                $supplierReconciliationPassed = false;
            }
        }

        // Prepare chart data arrays for Supplier Amount Trend (Line: USD & IDR) & Qty Comparison (Bar)
        $chartSupplierLabels            = [];
        $chartSupplierForecastAmountUsd = [];
        $chartSupplierActualAmountUsd   = [];
        $chartSupplierForecastAmountIdr = [];
        $chartSupplierActualAmountIdr   = [];
        $chartSupplierForecastQty       = [];
        $chartSupplierActualQty         = [];

        // Legacy compatibility alias
        $chartSupplierForecastAmount    = &$chartSupplierForecastAmountUsd;
        $chartSupplierActualAmount      = &$chartSupplierActualAmountUsd;

        $activeSupplierKey = ($s2_vendor !== 'ALL') ? $s2_vendor : null;

        for ($i = 1; $i <= $s2_duration; $i++) {
            $chartSupplierLabels[] = $months[$i] ?? ('M' . $i);
            $fQtySum = 0; $aQtySum = 0;
            $fAmtUsdSum = 0.0; $aAmtUsdSum = 0.0;
            $fAmtIdrSum = 0.0; $aAmtIdrSum = 0.0;

            if ($activeSupplierKey && isset($supplierMonthlySummary[$activeSupplierKey][$i])) {
                $r = $supplierMonthlySummary[$activeSupplierKey][$i];
                $fQtySum    = $r['forecast_qty'];
                $aQtySum    = $r['actual_qty'];
                $fAmtUsdSum = $r['forecast_amount_usd'];
                $aAmtUsdSum = $r['incoming_amount_usd'];
                $fAmtIdrSum = $r['forecast_amount_idr'];
                $aAmtIdrSum = $r['incoming_amount_idr'];
            } else {
                foreach ($supplierMonthlySummary as $supData) {
                    if (isset($supData[$i])) {
                        $fQtySum    += $supData[$i]['forecast_qty'];
                        $aQtySum    += $supData[$i]['actual_qty'];
                        $fAmtUsdSum += $supData[$i]['forecast_amount_usd'];
                        $aAmtUsdSum += $supData[$i]['incoming_amount_usd'];
                        $fAmtIdrSum += $supData[$i]['forecast_amount_idr'];
                        $aAmtIdrSum += $supData[$i]['incoming_amount_idr'];
                    }
                }
            }

            $chartSupplierForecastAmountUsd[] = round($fAmtUsdSum, 2);
            $chartSupplierActualAmountUsd[]   = round($aAmtUsdSum, 2);
            $chartSupplierForecastAmountIdr[] = round($fAmtIdrSum, 0);
            $chartSupplierActualAmountIdr[]   = round($aAmtIdrSum, 0);
            $chartSupplierForecastQty[]       = $fQtySum;
            $chartSupplierActualQty[]         = $aQtySum;
        }

        // ═══════════════════════════════════════════════════════════════════
        // STOCK MONTHLY SUMMARY & MOVEMENT ANALYTICS (Slide 3)
        // Aggregates Forecast Ending Stock vs Actual Stock for Slide 3
        // ═══════════════════════════════════════════════════════════════════
        $stockMonthlySummary = []; // [month_index => {metrics}]
        $stockMoMAnalytics   = []; // [month_index => {delta, top_material_drivers, top_supplier_drivers}]

        for ($i = 1; $i <= $s3_duration; $i++) {
            $mLabel = $months[$i] ?? ('M' . $i);

            $fStockSum    = 0;
            $aStockSum    = 0;
            $fStockAmtUsd = 0.0;
            $aStockAmtUsd = 0.0;
            $fStockAmtIdr = 0.0;
            $aStockAmtIdr = 0.0;
            $hasActualDataMonth = false;

            foreach ($displayGridS3 as $gridItem) {
                $fg = $gridItem->forecast_grid[$i] ?? null;
                $ig = $gridItem->inventory_grid[$i] ?? null;

                $fStockSum    += (int)($ig->forecast_stock_qty ?? $fg->stock ?? 0);
                $fStockAmtUsd += (float)($ig->forecast_stock_usd ?? $fg->stock_amount_usd ?? 0.0);
                $fStockAmtIdr += (float)($ig->forecast_stock_idr ?? $fg->stock_amount_idr ?? 0.0);

                if (!empty($ig?->has_actual_data)) {
                    $hasActualDataMonth = true;
                    $aStockSum    += (int)($ig->stock_qty ?? $ig->inventory_qty ?? 0);
                    $aStockAmtUsd += (float)($ig->stock_amount_usd ?? $ig->inventory_amount_usd ?? 0.0);
                    $aStockAmtIdr += (float)($ig->stock_amount_idr ?? $ig->inventory_amount_idr ?? 0.0);
                }
            }

            $varQty    = $aStockSum - $fStockSum;
            $varAmtUsd = $aStockAmtUsd - $fStockAmtUsd;
            $varAmtIdr = $aStockAmtIdr - $fStockAmtIdr;
            $varPctVal = $fStockSum > 0 ? round(($varQty / $fStockSum) * 100, 1) : 0.0;

            if ($fStockSum == 0 && $aStockSum == 0) {
                $status = 'No Demand';
            } elseif ($fStockSum > 0 && abs($varQty) <= max(1, 0.05 * $fStockSum)) {
                $status = 'Balanced';
            } elseif ($aStockSum > $fStockSum) {
                $status = 'Surplus';
            } else {
                $status = 'Deficit';
            }
            $varPct = ($varQty >= 0 ? '+' : '') . $varPctVal . '%';

            $stockMonthlySummary[$i] = [
                'month_index'         => $i,
                'month_name'          => $mLabel,
                'has_actual_data'     => $hasActualDataMonth,
                'forecast_stock_qty'  => $fStockSum,
                'actual_stock_qty'    => $hasActualDataMonth ? $aStockSum : 0,
                'forecast_stock_usd'  => $fStockAmtUsd,
                'actual_stock_usd'    => $hasActualDataMonth ? $aStockAmtUsd : 0.0,
                'forecast_stock_idr'  => $fStockAmtIdr,
                'actual_stock_idr'    => $hasActualDataMonth ? $aStockAmtIdr : 0.0,
                'variance_qty'        => $varQty,
                'variance_pct'        => $varPct,
                'variance_amount_usd' => $varAmtUsd,
                'variance_amount_idr' => $varAmtIdr,
                'status'              => $status,
            ];

            // MoM Stock Movement & Material Drivers for Slide 3
            if ($i >= 2 && $hasActualDataMonth && !empty($stockMonthlySummary[$i - 1]['has_actual_data'])) {
                $prevM = $stockMonthlySummary[$i - 1];
                $prevStock = $prevM['actual_stock_qty'] ?? 0;
                $currStock = $aStockSum;
                $diffQty   = $currStock - $prevStock;
                $diffPct   = $prevStock > 0 ? round(($diffQty / $prevStock) * 100, 2) : 0.0;

                // Rank materials by stock movement
                $itemMovers = [];
                $supMovers  = [];

                foreach ($displayGridS3 as $gridItem) {
                    $itemCode = $gridItem->item_code;
                    $supName  = $gridItem->supplier ?: '-';

                    $prevItemStock = (int)($gridItem->inventory_grid[$i - 1]->stock_qty ?? $gridItem->inventory_grid[$i - 1]->inventory_qty ?? 0);
                    $currItemStock = (int)($gridItem->inventory_grid[$i]->stock_qty ?? $gridItem->inventory_grid[$i]->inventory_qty ?? 0);
                    $itemDiff = $currItemStock - $prevItemStock;

                    if ($itemDiff != 0) {
                        $itemMovers[] = [
                            'item_code'   => $itemCode,
                            'description' => $gridItem->description ?: '-',
                            'supplier'    => $supName,
                            'prev_qty'    => $prevItemStock,
                            'curr_qty'    => $currItemStock,
                            'delta_qty'   => $itemDiff,
                            'delta_pct'   => $prevItemStock > 0 ? round(($itemDiff / $prevItemStock) * 100, 1) : 0.0,
                        ];

                        if (!isset($supMovers[$supName])) {
                            $supMovers[$supName] = ['supplier' => $supName, 'prev_qty' => 0, 'curr_qty' => 0, 'delta_qty' => 0];
                        }
                        $supMovers[$supName]['prev_qty']  += $prevItemStock;
                        $supMovers[$supName]['curr_qty']  += $currItemStock;
                        $supMovers[$supName]['delta_qty'] += $itemDiff;
                    }
                }

                // Sort item movers by largest absolute change
                usort($itemMovers, fn($a, $b) => abs($b['delta_qty']) <=> abs($a['delta_qty']));
                usort($supMovers, fn($a, $b) => abs($b['delta_qty']) <=> abs($a['delta_qty']));

                $stockMoMAnalytics[$i] = (object)[
                    'month_index'          => $i,
                    'prev_month_name'      => $months[$i - 1] ?? ('M' . ($i - 1)),
                    'curr_month_name'      => $mLabel,
                    'prev_stock_qty'       => $prevStock,
                    'curr_stock_qty'       => $currStock,
                    'diff_stock_qty'       => $diffQty,
                    'diff_stock_pct'       => $diffPct,
                    'top_material_drivers' => array_slice($itemMovers, 0, 10),
                    'top_supplier_drivers' => array_slice(array_values($supMovers), 0, 5),
                ];
            } else {
                $stockMoMAnalytics[$i] = null;
            }
        }

        // Map PO numbers to corresponding item datasets
        foreach ($allMasterPos as $mpo) {
            $poKey   = strtoupper(trim((string)$mpo->po));
            $codeKey = strtoupper(trim((string)$mpo->item_code));
            if ($poKey && isset($chartPerItem[$codeKey])) {
                $chartPerPo[$poKey] = $chartPerItem[$codeKey];
            }
        }

        // Calculate weighted average price for global chart dataset (Slide 1)
        for ($i = 0; $i <= 36; $i++) {
            $chartForecastPrice[$i] = $chartForecastPo[$i] > 0 
                ? ($chartForecastAmount[$i] / $chartForecastPo[$i]) 
                : ($itemCountS1 > 0 ? array_sum(array_column(array_column($chartPerItem, 'forecast_price'), $i)) / $itemCountS1 : 0.0);

            $chartActualPrice[$i] = $chartActualPo[$i] > 0 
                ? ($chartActualAmount[$i] / $chartActualPo[$i]) 
                : 0.0;
        }

        // Prepare Outstanding PO Qty vs Receipt Qty comparison data for Slide 2
        $filteredItemCodesS2 = $displayGridS2->pluck('item_code')->concat($displayGridS2->pluck('drawing'))->filter()->unique()->toArray();
        $masterPosQuery = \App\Models\MasterPo::orderBy('tanggal', 'desc');
        if ($s2_po !== 'ALL') {
            $masterPosQuery->where('po', $s2_po);
        } elseif (!empty($filteredItemCodesS2)) {
            $masterPosQuery->where(function($q) use ($filteredItemCodesS2) {
                $q->whereIn('item_code', $filteredItemCodesS2)
                  ->orWhereIn('po', $filteredItemCodesS2);
            });
        }
        $masterPos = $masterPosQuery->take(10)->get();

        if ($masterPos->isEmpty()) {
            $masterPos = \App\Models\MasterPo::orderBy('tanggal', 'desc')->take(10)->get();
        }

        $receiptsByPoItem = \App\Models\PurchasingLog::selectRaw('po_reference, item_code, SUM(actual_received) as total_received')
            ->groupBy('po_reference', 'item_code')
            ->get()
            ->groupBy(fn($row) => strtoupper(trim((string)$row->po_reference)) . '___' . strtoupper(trim((string)$row->item_code)));

        $outstandingPoChartData = $masterPos->map(function($mp) use ($receiptsByPoItem) {
            $po = strtoupper(trim((string)$mp->po));
            $code = strtoupper(trim((string)$mp->item_code));
            
            $poKey = $po . '___' . $code;
            $qtyReceipt = 0;
            if ($receiptsByPoItem->has($poKey)) {
                $qtyReceipt = (int)($receiptsByPoItem->get($poKey)?->first()?->total_received ?? 0);
            } else {
                $qtyReceipt = (int)\App\Models\PurchasingLog::where('po_reference', $mp->po)->sum('actual_received');
            }

            return [
                'po' => $mp->po,
                'item_code' => $mp->item_code,
                'qty_po' => (int)$mp->qty,
                'qty_receipt' => $qtyReceipt,
            ];
        })->values();

        // Build Infographic Monthly Trend Insights for each month for Slide 2
        $monthlyInsights = [];
        for ($i = 1; $i <= $s2_duration; $i++) {
            $mName = $months[$i] ?? ('Bulan ' . $i);
            $itemsUp = [];
            $itemsDown = [];
            $itemsStable = [];

            foreach ($displayGridS2 as $gridItem) {
                $fPo     = $gridItem->forecast_grid[$i]->po ?? 0;
                $fFc     = $gridItem->forecast_grid[$i]->forecast ?? 0;
                $aPo     = $gridItem->actual_grid[$i]->po ?? 0;
                $aDel    = $gridItem->actual_grid[$i]->delivery ?? 0;
                $aProd   = $gridItem->actual_grid[$i]->prod ?? 0;
                $fStock  = $gridItem->forecast_grid[$i]->stock ?? 0;
                $aStock  = $gridItem->actual_grid[$i]->stock ?? 0;

                $fTarget = $fFc > 0 ? $fFc : $fPo;
                $diffDel = $aDel > 0 ? ($aDel - $fTarget) : ($aPo > 0 ? ($aPo - $fTarget) : 0);
                $diffStock = $aStock - $fStock;

                $itemInfo = [
                    'item_code'      => $gridItem->item_code,
                    'description'    => $gridItem->description,
                    'supplier'       => $gridItem->supplier,
                    'pic_buyer'      => $gridItem->pic_buyer,
                    'category_name'  => $gridItem->category_name,
                    'actual_po'      => $aPo,
                    'forecast_po'    => $fTarget,
                    'actual_del'     => $aDel,
                    'forecast_del'   => $fTarget,
                    'diff_po'        => $diffDel,
                    'actual_stock'   => $aStock,
                    'forecast_stock' => $fStock,
                    'diff_stock'     => $diffStock,
                ];

                if ($aDel == 0 && $aPo == 0 && $aProd == 0) {
                    $itemsStable[] = $itemInfo;
                } elseif ($diffDel > 0) {
                    $itemsUp[] = $itemInfo;
                } elseif ($diffDel < 0) {
                    $itemsDown[] = $itemInfo;
                } elseif ($diffStock > 0 && $aProd > 0) {
                    $itemsUp[] = $itemInfo;
                } elseif ($diffStock < 0 && $aProd > 0) {
                    $itemsDown[] = $itemInfo;
                } else {
                    $itemsStable[] = $itemInfo;
                }
            }

            $monthlyInsights[$mName] = [
                'month_name'    => $mName,
                'items_up'      => $itemsUp,
                'items_down'    => $itemsDown,
                'items_stable'  => $itemsStable,
                'total_up'      => count($itemsUp),
                'total_down'    => count($itemsDown),
                'total_stable'  => count($itemsStable),
            ];
        }

        // Compile Top 10 ranking dataset for Slide 2 (Deduplicated by item_code with full multi-currency normalization)
        $rawTop10Data = $displayGridS2->map(function($g) use ($s2_duration) {
            $sumPoQty = 0;
            $sumActualQty = 0;
            $sumPoAmountUsd = 0.0;
            $sumPoAmountIdr = 0.0;
            $sumActualAmountUsd = 0.0;
            $sumActualAmountIdr = 0.0;
            $lastActualPriceUsd = 0.0;
            $lastActualPriceIdr = 0.0;
            
            for ($i = 1; $i <= $s2_duration; $i++) {
                $sumPoQty += $g->forecast_grid[$i]->po ?? 0;
                $sumActualQty += $g->actual_grid[$i]->delivery ?? 0;
                $sumPoAmountUsd += (float)($g->forecast_grid[$i]->po_amount_usd ?? ($g->forecast_grid[$i]->po_amount ?? 0));
                $sumPoAmountIdr += (float)($g->forecast_grid[$i]->po_amount_idr ?? (($g->forecast_grid[$i]->po ?? 0) * ($g->forecast_grid[$i]->price_idr ?? 0)));
                $sumActualAmountUsd += (float)($g->actual_grid[$i]->delivery_amount_usd ?? 0);
                $sumActualAmountIdr += (float)($g->actual_grid[$i]->delivery_amount_idr ?? (($g->actual_grid[$i]->delivery ?? 0) * ($g->actual_grid[$i]->price_idr ?? ($g->forecast_grid[$i]->price_idr ?? 0))));

                if (($g->actual_grid[$i]->delivery ?? 0) > 0) {
                    if (isset($g->actual_grid[$i]->price_usd) && $g->actual_grid[$i]->price_usd > 0) {
                        $lastActualPriceUsd = (float) $g->actual_grid[$i]->price_usd;
                    }
                    if (isset($g->actual_grid[$i]->price_idr) && $g->actual_grid[$i]->price_idr > 0) {
                        $lastActualPriceIdr = (float) $g->actual_grid[$i]->price_idr;
                    }
                }
            }
            
            $firstMonth = $g->forecast_grid[1] ?? null;
            $fallbackPriceUsd = (float)($firstMonth->price_usd ?? ($g->actual_price > 0 ? $g->actual_price : ($g->forecast_price ?? 0)));
            $fallbackPriceIdr = (float)($firstMonth->price_idr ?? ($fallbackPriceUsd * 16600));

            $priceUsd = $lastActualPriceUsd > 0 ? $lastActualPriceUsd : $fallbackPriceUsd;
            $priceIdr = $lastActualPriceIdr > 0 ? $lastActualPriceIdr : $fallbackPriceIdr;

            // Raw price in its native currency (e.g. IDR 1,719,374 or USD 58.96)
            $nativeCurrency = strtoupper(trim((string)($g->currency ?? ($g->delivery_category_code === 'IMP' ? 'USD' : ($priceIdr > 10000 ? 'IDR' : 'USD')))));
            $nativePrice = $nativeCurrency === 'IDR' ? $priceIdr : $priceUsd;

            $totalAmtUsd = $sumActualAmountUsd > 0 ? $sumActualAmountUsd 
                : ($sumActualQty > 0 ? $sumActualQty * $priceUsd : $sumPoAmountUsd);
            $totalAmtIdr = $sumActualAmountIdr > 0 ? $sumActualAmountIdr 
                : ($sumActualQty > 0 ? $sumActualQty * $priceIdr : $sumPoAmountIdr);
            
            return [
                'id'               => $g->id,
                'item_code'        => strtoupper(trim($g->item_code)),
                'description'      => $g->description ?: '-',
                'supplier'         => $g->supplier ?: '-',
                'pic_buyer'        => $g->pic_buyer ?: '-',
                'category_name'    => $g->category_name ?: '-',
                'currency'         => $nativeCurrency,
                'native_price'     => $nativePrice,
                'price_usd'        => $priceUsd,
                'price_idr'        => $priceIdr,
                'price'            => $priceUsd, // backward compatibility
                'sum_po_qty'       => $sumPoQty,
                'sum_actual_qty'   => $sumActualQty,
                'total_amount_usd' => $totalAmtUsd,
                'total_amount_idr' => $totalAmtIdr,
                'total_amount'     => $totalAmtUsd, // backward compatibility
            ];
        })->values();

        // Group by item_code to deduplicate — each item_code appears exactly once
        $top10ItemsData = collect($rawTop10Data)->groupBy('item_code')->map(function($group) {
            $first = $group->first();
            return [
                'id'               => $first['id'],
                'item_code'        => $first['item_code'],
                'description'      => $first['description'],
                'supplier'         => $first['supplier'],
                'pic_buyer'        => $first['pic_buyer'],
                'category_name'    => $first['category_name'],
                'currency'         => $first['currency'],
                'native_price'     => $first['native_price'],
                'price_usd'        => $group->max('price_usd'),
                'price_idr'        => $group->max('price_idr'),
                'price'            => $group->max('price_usd'),
                'sum_po_qty'       => $group->sum('sum_po_qty'),
                'sum_actual_qty'   => $group->sum('sum_actual_qty'),
                'total_amount_usd' => $group->sum('total_amount_usd'),
                'total_amount_idr' => $group->sum('total_amount_idr'),
                'total_amount'     => $group->sum('total_amount_usd'),
            ];
        })->values();

        // ── Single Calculation Engine: ComparisonAnalysisService (Slide 1) ──
        $comparisonDataset = \App\Services\ComparisonAnalysisService::buildComparisonDataset([
            'item_code'         => $s1_item_code,
            'vendor'            => $s1_vendor,
            'pic'               => $s1_pic,
            'po'                => $s1_po,
            'delivery_category' => $s1_delivery_category,
            'year'              => $s1_year,
            'start_month'       => $startMonth,
            'duration'          => $s1_duration,
        ]);

        $exchangeRateComparisonGrid = $comparisonDataset;
        $comparisonMonthlyInsights  = $comparisonDataset;

        $chartFxLabels            = $comparisonDataset->pluck('short_label')->toArray();
        $chartFxForecastAmountUsd = $comparisonDataset->map(fn($r) => (float)($r->forecast_amount_usd ?? 0.0))->toArray();
        $chartFxActualAmountUsd   = $comparisonDataset->map(fn($r) => (float)($r->incoming_amount_usd ?? 0.0))->toArray();
        $chartFxForecastPriceUsd  = $comparisonDataset->map(fn($r) => (float)($r->forecast_price_usd ?? 0.0))->toArray();
        $chartFxActualPriceUsd    = $comparisonDataset->map(fn($r) => (float)($r->incoming_price_usd ?? 0.0))->toArray();
        $chartFxForecastAmountIdr = $comparisonDataset->map(fn($r) => (float)($r->forecast_amount_idr ?? 0.0))->toArray();
        $chartFxActualAmountIdr   = $comparisonDataset->map(fn($r) => (float)($r->incoming_amount_idr ?? 0.0))->toArray();
        $chartFxForecastPriceIdr  = $comparisonDataset->map(fn($r) => (float)($r->forecast_price_idr ?? 0.0))->toArray();
        $chartFxActualPriceIdr    = $comparisonDataset->map(fn($r) => (float)($r->incoming_price_idr ?? 0.0))->toArray();
        
        // Multi-Metric Matrix Arrays for Dynamic Switcher (Slide 1):
        $chartFxAvgForecastAmountUsd = $comparisonDataset->map(fn($r) => (float)($r->avg_forecast_amount_usd ?? 0.0))->toArray();
        $chartFxAvgActualAmountUsd   = $comparisonDataset->map(fn($r) => (float)($r->avg_incoming_amount_usd ?? 0.0))->toArray();
        $chartFxAvgForecastAmountIdr = $comparisonDataset->map(fn($r) => (float)($r->avg_forecast_amount_idr ?? 0.0))->toArray();
        $chartFxAvgActualAmountIdr   = $comparisonDataset->map(fn($r) => (float)($r->avg_incoming_amount_idr ?? 0.0))->toArray();

        $chartFxSumForecastPriceUsd  = $comparisonDataset->map(fn($r) => (float)($r->sum_forecast_price_usd ?? 0.0))->toArray();
        $chartFxSumActualPriceUsd    = $comparisonDataset->map(fn($r) => (float)($r->sum_incoming_price_usd ?? 0.0))->toArray();
        $chartFxSumForecastPriceIdr  = $comparisonDataset->map(fn($r) => (float)($r->sum_forecast_price_idr ?? 0.0))->toArray();
        $chartFxSumActualPriceIdr    = $comparisonDataset->map(fn($r) => (float)($r->sum_incoming_price_idr ?? 0.0))->toArray();

        $chartFxIncomingStatus    = $comparisonDataset->map(fn($r) => (bool)$r->has_incoming)->toArray();
        $chartFxBudgetRates       = $comparisonDataset->pluck('budget_rate')->toArray();
        $chartFxActualRates       = $comparisonDataset->pluck('actual_avg_rate')->toArray();

        $slide1ExecutiveSummary   = \App\Services\ComparisonAnalysisService::getSlide1ExecutiveSummary($comparisonDataset);
        $analysisPeriodMetadata   = \App\Services\ComparisonAnalysisService::getAnalysisPeriodMetadata($comparisonDataset, $allItems, $allLogs);

        // Pre-fetch latest price per item_code for Slide 1
        $gridItemCodesS1 = $displayGridS1->pluck('item_code')->filter()->unique();
        $latestLogPrices = \App\Models\PurchasingLog::whereIn('item_code', $gridItemCodesS1)
            ->where('price', '>', 0)
            ->select('item_code', \Illuminate\Support\Facades\DB::raw('MAX(price) as latest_price'))
            ->groupBy('item_code')
            ->pluck('latest_price', 'item_code');

        // Item Price Variance Analysis for Slide 1
        $itemPriceVariances = $displayGridS1->map(function($g) use ($latestLogPrices, $s1_duration) {
            $fPrcUsd = (float) ($g->forecast_price ?? $g->price ?? 0.0);
            $aPrcUsd = (float) ($g->actual_price > 0 ? $g->actual_price : $fPrcUsd);

            if (isset($latestLogPrices[$g->item_code]) && $latestLogPrices[$g->item_code] > 0) {
                $aPrcUsd = (float) $latestLogPrices[$g->item_code];
            }

            $diffPrcUsd = $aPrcUsd - $fPrcUsd;
            $diffPrcPct = $fPrcUsd > 0 ? round(($diffPrcUsd / $fPrcUsd) * 100, 2) : 0;

            $avgBudgetRate = 16600;
            $fPrcIdr = (int) round($fPrcUsd * $avgBudgetRate);
            $aPrcIdr = (int) round($aPrcUsd * $avgBudgetRate);
            $diffPrcIdr = $aPrcIdr - $fPrcIdr;

            // Total Amounts over duration for this item
            $fAmtUsd = 0.0;
            $aAmtUsd = 0.0;
            for ($m = 1; $m <= $s1_duration; $m++) {
                $fCell = $g->forecast_grid[$m] ?? null;
                $aCell = $g->actual_grid[$m] ?? null;
                $fAmtUsd += (float) ($fCell?->po_amount ?? 0.0);
                $aAmtUsd += (float) ($aCell?->delivery_amount ?? 0.0);
            }

            $diffAmtUsd = $aAmtUsd - $fAmtUsd;
            $diffAmtPct = $fAmtUsd > 0 ? round(($diffAmtUsd / $fAmtUsd) * 100, 2) : 0;

            $fAmtIdr = (int) round($fAmtUsd * $avgBudgetRate);
            $aAmtIdr = (int) round($aAmtUsd * $avgBudgetRate);
            $diffAmtIdr = $aAmtIdr - $fAmtIdr;

            return (object)[
                'item_code'          => $g->item_code,
                'description'        => $g->description ?: '-',
                'supplier'           => $g->supplier ?: '-',
                'category_name'      => $g->category_name ?: '-',
                'pic_buyer'          => $g->pic_buyer ?: '-',
                
                // Price fields
                'forecast_price_usd' => $fPrcUsd,
                'actual_price_usd'   => $aPrcUsd,
                'diff_price_usd'     => $diffPrcUsd,
                'diff_price_pct'     => $diffPrcPct,
                'forecast_price_idr' => $fPrcIdr,
                'actual_price_idr'   => $aPrcIdr,
                'diff_price_idr'     => $diffPrcIdr,
                
                // Amount fields
                'forecast_amount_usd'=> $fAmtUsd,
                'actual_amount_usd'  => $aAmtUsd,
                'diff_amount_usd'    => $diffAmtUsd,
                'diff_amount_pct'    => $diffAmtPct,
                'forecast_amount_idr'=> $fAmtIdr,
                'actual_amount_idr'  => $aAmtIdr,
                'diff_amount_idr'    => $diffAmtIdr,

                'is_increase'        => ($diffPrcUsd > 0.01 || $diffAmtUsd > 0.01),
                'is_decrease'        => ($diffPrcUsd < -0.01 || $diffAmtUsd < -0.01),
                'is_stable'          => (abs($diffPrcUsd) <= 0.01 && abs($diffAmtUsd) <= 0.01),
            ];
        })->values();

        $visibleItemPriceVariances = $itemPriceVariances->take(500)->values();

        $inventoryVarianceReasons = \App\Models\InventoryVarianceReason::all()
            ->keyBy(fn($item) => strtoupper(trim((string)$item->part_number)));

        // ═══════════════════════════════════════════════════════════════════
        // SLIDE 3 KPI SUMMARY & TOP PART NUMBER VARIANCE CONTRIBUTORS
        // ═══════════════════════════════════════════════════════════════════
        $slide3TotalForecastStockQty = 0;
        $slide3TotalActualStockQty   = 0;
        $slide3TotalForecastStockUsd = 0.0;
        $slide3TotalActualStockUsd   = 0.0;
        $slide3TotalForecastStockIdr = 0.0;
        $slide3TotalActualStockIdr   = 0.0;
        $slide3AvailablePeriodsCount = 0;
        $slide3TotalPeriodsCount     = $s3_duration;

        for ($i = 1; $i <= $s3_duration; $i++) {
            $fQty = (int) ($chartInvForecastStock[$i] ?? 0);
            $slide3TotalForecastStockQty += $fQty;
            $slide3TotalForecastStockUsd += (float) ($chartInvForecastAmountUsd[$i] ?? 0.0);
            $slide3TotalForecastStockIdr += (float) ($chartInvForecastAmountIdr[$i] ?? 0.0);

            if (!empty($monthsWithActualStock[$i])) {
                $slide3AvailablePeriodsCount++;
                $slide3TotalActualStockQty += (int) $chartInvActualStock[$i];
                $slide3TotalActualStockUsd += (float) ($chartInvActualAmountUsd[$i] ?? 0.0);
                $slide3TotalActualStockIdr += (float) ($chartInvActualAmountIdr[$i] ?? 0.0);
            }
        }

        // Top Variance Part Numbers ranking for Slide 3
        $slide3TopVarianceItems = [];
        foreach ($displayGridS3 as $gridItem) {
            $itemTotFStock = 0;
            $itemTotAStock = 0;
            $itemHasActual = false;

            for ($i = 1; $i <= $s3_duration; $i++) {
                $ig = $gridItem->inventory_grid[$i] ?? null;
                if ($ig && $ig->has_actual_data) {
                    $itemHasActual = true;
                    $itemTotFStock += (int) $ig->forecast_stock_qty;
                    $itemTotAStock += (int) $ig->stock_qty;
                }
            }

            if ($itemHasActual) {
                $diff = $itemTotAStock - $itemTotFStock;
                $pct = $itemTotFStock > 0 ? round(($diff / $itemTotFStock) * 100, 1) : 0.0;
                $priceUsd = (float) ($gridItem->forecast_price ?? 0.0);
                $slide3TopVarianceItems[] = [
                    'item_code'      => $gridItem->item_code,
                    'drawing'        => $gridItem->drawing,
                    'description'    => $gridItem->description,
                    'supplier'       => $gridItem->supplier,
                    'forecast_stock' => $itemTotFStock,
                    'actual_stock'   => $itemTotAStock,
                    'variance_qty'   => $diff,
                    'variance_pct'   => $pct,
                    'variance_usd'   => $diff * $priceUsd,
                ];
            }
        }
        usort($slide3TopVarianceItems, fn($a, $b) => abs($b['variance_qty']) <=> abs($a['variance_qty']));
        $slide3TopVarianceItems = array_slice($slide3TopVarianceItems, 0, 10);

        // ═══════════════════════════════════════════════════════════════════
        // SLIDE 3 VENDOR STOCK SUMMARY (Grouped Hierarchy by Vendor)
        // ═══════════════════════════════════════════════════════════════════
        $gridForSlide3Vendors = $filterSlideGrid($comparisonGrid, $s3_item_code, 'ALL', $s3_pic, $s3_po, $s3_delivery_category);
        $slide3VendorSummariesMap = [];

        foreach ($gridForSlide3Vendors as $gridItem) {
            $supName = $gridItem->supplier ?: 'Tanpa Vendor';
            if (!isset($slide3VendorSummariesMap[$supName])) {
                $slide3VendorSummariesMap[$supName] = [
                    'supplier'            => $supName,
                    'item_count'          => 0,
                    'delivery_categories' => [],
                    'pics'                => [],
                    'item_codes'          => [],
                    'm0'                  => [
                        'forecast_stock_qty'  => 0,
                        'forecast_stock_usd'  => 0.0,
                        'forecast_stock_idr'  => 0.0,
                        'actual_stock_qty'    => 0,
                        'actual_stock_usd'    => 0.0,
                        'actual_stock_idr'    => 0.0,
                        'variance_qty'        => 0,
                        'variance_amount_usd' => 0.0,
                        'variance_amount_idr' => 0.0,
                    ],
                    'monthly'             => [],
                    'total_forecast_qty'  => 0,
                    'total_actual_qty'    => 0,
                    'total_variance_qty'  => 0,
                    'total_forecast_usd'  => 0.0,
                    'total_actual_usd'    => 0.0,
                    'total_variance_usd'  => 0.0,
                    'total_forecast_idr'  => 0.0,
                    'total_actual_idr'    => 0.0,
                    'total_variance_idr'  => 0.0,
                    'surplus_items_count' => 0,
                    'deficit_items_count' => 0,
                    'optimal_items_count' => 0,
                    'status'              => 'Optimal',
                ];
                for ($i = 1; $i <= $s3_duration; $i++) {
                    $slide3VendorSummariesMap[$supName]['monthly'][$i] = [
                        'month_index'         => $i,
                        'month_name'          => $monthsLabels[$i] ?? ('M' . $i),
                        'forecast_stock_qty'  => 0,
                        'forecast_stock_usd'  => 0.0,
                        'forecast_stock_idr'  => 0.0,
                        'actual_stock_qty'    => 0,
                        'actual_stock_usd'    => 0.0,
                        'actual_stock_idr'    => 0.0,
                        'has_actual_data'     => false,
                        'variance_qty'        => 0,
                        'variance_amount_usd' => 0.0,
                        'variance_amount_idr' => 0.0,
                    ];
                }
            }

            $slide3VendorSummariesMap[$supName]['item_count']++;
            if (!empty($gridItem->item_code) && !in_array($gridItem->item_code, $slide3VendorSummariesMap[$supName]['item_codes'], true)) {
                $slide3VendorSummariesMap[$supName]['item_codes'][] = $gridItem->item_code;
            }
            if (!empty($gridItem->delivery_category_code) && !in_array($gridItem->delivery_category_code, $slide3VendorSummariesMap[$supName]['delivery_categories'], true)) {
                $slide3VendorSummariesMap[$supName]['delivery_categories'][] = $gridItem->delivery_category_code;
            }
            if (!empty($gridItem->pic_buyer) && $gridItem->pic_buyer !== '-' && !in_array($gridItem->pic_buyer, $slide3VendorSummariesMap[$supName]['pics'], true)) {
                $slide3VendorSummariesMap[$supName]['pics'][] = $gridItem->pic_buyer;
            }

            // M0 (Pre-Month)
            $inv0 = $gridItem->inventory_grid[0] ?? null;
            if ($inv0) {
                $slide3VendorSummariesMap[$supName]['m0']['forecast_stock_qty']  += (int) ($inv0->forecast_stock_qty ?? 0);
                $slide3VendorSummariesMap[$supName]['m0']['forecast_stock_usd']  += (float) ($inv0->forecast_stock_usd ?? 0.0);
                $slide3VendorSummariesMap[$supName]['m0']['forecast_stock_idr']  += (float) ($inv0->forecast_stock_idr ?? 0.0);
                $slide3VendorSummariesMap[$supName]['m0']['actual_stock_qty']    += (int) ($inv0->stock_qty ?? 0);
                $slide3VendorSummariesMap[$supName]['m0']['actual_stock_usd']    += (float) ($inv0->stock_amount_usd ?? 0.0);
                $slide3VendorSummariesMap[$supName]['m0']['actual_stock_idr']    += (float) ($inv0->stock_amount_idr ?? 0.0);
                $slide3VendorSummariesMap[$supName]['m0']['variance_qty']        += (int) ($inv0->variance_qty ?? 0);
                $slide3VendorSummariesMap[$supName]['m0']['variance_amount_usd'] += (float) ($inv0->variance_amount_usd ?? 0.0);
                $slide3VendorSummariesMap[$supName]['m0']['variance_amount_idr'] += (float) ($inv0->variance_amount_idr ?? 0.0);
            }

            $itemVarQty0 = $inv0->variance_qty ?? 0;
            if ($itemVarQty0 > 0) $slide3VendorSummariesMap[$supName]['surplus_items_count']++;
            elseif ($itemVarQty0 < 0) $slide3VendorSummariesMap[$supName]['deficit_items_count']++;
            else $slide3VendorSummariesMap[$supName]['optimal_items_count']++;

            // Months 1..$s3_duration
            for ($i = 1; $i <= $s3_duration; $i++) {
                $invRow = $gridItem->inventory_grid[$i] ?? null;
                if ($invRow) {
                    $slide3VendorSummariesMap[$supName]['monthly'][$i]['forecast_stock_qty'] += (int) ($invRow->forecast_stock_qty ?? 0);
                    $slide3VendorSummariesMap[$supName]['monthly'][$i]['forecast_stock_usd'] += (float) ($invRow->forecast_stock_usd ?? 0.0);
                    $slide3VendorSummariesMap[$supName]['monthly'][$i]['forecast_stock_idr'] += (float) ($invRow->forecast_stock_idr ?? 0.0);

                    if (!empty($invRow->has_actual_data) && $invRow->stock_qty !== null) {
                        $slide3VendorSummariesMap[$supName]['monthly'][$i]['has_actual_data'] = true;
                        $slide3VendorSummariesMap[$supName]['monthly'][$i]['actual_stock_qty']   += (int) $invRow->stock_qty;
                        $slide3VendorSummariesMap[$supName]['monthly'][$i]['actual_stock_usd']   += (float) $invRow->stock_amount_usd;
                        $slide3VendorSummariesMap[$supName]['monthly'][$i]['actual_stock_idr']   += (float) $invRow->stock_amount_idr;
                        $slide3VendorSummariesMap[$supName]['monthly'][$i]['variance_qty']       += (int) $invRow->variance_qty;
                        $slide3VendorSummariesMap[$supName]['monthly'][$i]['variance_amount_usd']+= (float) $invRow->variance_amount_usd;
                        $slide3VendorSummariesMap[$supName]['monthly'][$i]['variance_amount_idr']+= (float) $invRow->variance_amount_idr;
                    }
                }
            }
        }

        // Finalize status and totals for each vendor
        $slide3VendorSummaries = collect($slide3VendorSummariesMap)->map(function($v) use ($s3_duration) {
            $totFcQty = 0; $totAcQty = 0; $totVarQty = 0;
            $totFcUsd = 0.0; $totAcUsd = 0.0; $totVarUsd = 0.0;
            $totFcIdr = 0.0; $totAcIdr = 0.0; $totVarIdr = 0.0;
            $hasAnyActual = false;

            for ($i = 1; $i <= $s3_duration; $i++) {
                $m = $v['monthly'][$i];
                $totFcQty += $m['forecast_stock_qty'];
                $totFcUsd += $m['forecast_stock_usd'];
                $totFcIdr += $m['forecast_stock_idr'];

                if ($m['has_actual_data']) {
                    $hasAnyActual = true;
                    $totAcQty += $m['actual_stock_qty'];
                    $totAcUsd += $m['actual_stock_usd'];
                    $totAcIdr += $m['actual_stock_idr'];
                    $totVarQty += $m['variance_qty'];
                    $totVarUsd += $m['variance_amount_usd'];
                    $totVarIdr += $m['variance_amount_idr'];
                }
            }

            $v['total_forecast_qty'] = $totFcQty;
            $v['total_actual_qty']   = $totAcQty;
            $v['total_variance_qty'] = $totVarQty;
            $v['total_forecast_usd'] = $totFcUsd;
            $v['total_actual_usd']   = $totAcUsd;
            $v['total_variance_usd'] = $totVarUsd;
            $v['total_forecast_idr'] = $totFcIdr;
            $v['total_actual_idr']   = $totAcIdr;
            $v['total_variance_idr'] = $totVarIdr;
            $v['has_actual_data']    = $hasAnyActual;

            if (!$hasAnyActual && ($v['m0']['variance_qty'] == 0)) {
                $v['status'] = 'Optimal';
            } elseif ($v['m0']['variance_qty'] > 0 || $totVarQty > 0) {
                $v['status'] = 'Surplus';
            } elseif ($v['m0']['variance_qty'] < 0 || $totVarQty < 0) {
                $v['status'] = 'Deficit';
            } else {
                $v['status'] = 'Optimal';
            }

            return (object)$v;
        })->sortByDesc('item_count')->values();

        // Sliced chart data for Slide 2 — starting from Month 1 (removing Month 0 baseline)
        $chartS2Labels = array_slice($monthsLabels, 1, $s2_duration);
        $chartS2ForecastPo = array_slice($chartForecastPo, 1, $s2_duration);
        $chartS2ActualPo = array_slice($chartActualPo, 1, $s2_duration);
        $chartS2ForecastAmount = array_slice($chartForecastAmount, 1, $s2_duration);
        $chartS2ActualAmount = array_slice($chartActualAmount, 1, $s2_duration);

        return view('purchasing.analysis', compact(
            'displayGrid',
            'visibleDisplayGrid',
            'chartS2Labels',
            'chartS2ForecastPo',
            'chartS2ActualPo',
            'chartS2ForecastAmount',
            'chartS2ActualAmount',
            'displayGridS1',
            'visibleDisplayGridS1',
            'displayGridS2',
            'visibleDisplayGridS2',
            'displayGridS3',
            'visibleDisplayGridS3',
            'activeSlide',
            'availableYears',
            'selectedYear',
            'availableItemCodes',
            'availableVendors',
            'availablePics',
            'availablePoNumbers',
            'selectedItemCode',
            'selectedVendor',
            'selectedPic',
            'selectedPo',
            'selectedDeliveryCategory',
            'duration',
            'maxForecastPeriods',
            's1_item_code',
            's1_vendor',
            's1_pic',
            's1_po',
            's1_delivery_category',
            's1_year',
            's1_duration',
            'activeFilterCountS1',
            'activeFiltersListS1',
            's2_item_code',
            's2_vendor',
            's2_pic',
            's2_po',
            's2_delivery_category',
            's2_year',
            's2_duration',
            'activeFilterCountS2',
            'activeFiltersListS2',
            's3_item_code',
            's3_vendor',
            's3_pic',
            's3_po',
            's3_delivery_category',
            's3_year',
            's3_duration',
            'activeFilterCountS3',
            'activeFiltersListS3',
            'startMonth',
            'monthsLabels',
            'chartForecastPo',
            'chartActualPo',
            'chartForecastStock',
            'chartActualStock',
            'chartForecastAmount',
            'chartActualAmount',
            'chartForecastPrice',
            'chartActualPrice',
            'months',
            'outstandingPoChartData',
            'monthlyInsights',
            'top10ItemsData',
            'chartPerItem',
            'chartPerPo',
            'exchangeRateComparisonGrid',
            'comparisonMonthlyInsights',
            'chartFxLabels',
            'chartFxForecastAmountIdr',
            'chartFxActualAmountIdr',
            'chartFxForecastPriceIdr',
            'chartFxActualPriceIdr',
            'chartFxForecastAmountUsd',
            'chartFxActualAmountUsd',
            'chartFxForecastPriceUsd',
            'chartFxActualPriceUsd',
            'chartFxAvgForecastAmountUsd',
            'chartFxAvgActualAmountUsd',
            'chartFxAvgForecastAmountIdr',
            'chartFxAvgActualAmountIdr',
            'chartFxSumForecastPriceUsd',
            'chartFxSumActualPriceUsd',
            'chartFxSumForecastPriceIdr',
            'chartFxSumActualPriceIdr',
            'chartFxBudgetRates',
            'chartFxActualRates',
            'chartFxIncomingStatus',
            'analysisPeriodMetadata',
            'itemPriceVariances',
            'visibleItemPriceVariances',
            'chartInvForecastStock',
            'chartInvActualStock',
            'chartInvForecastAmountUsd',
            'chartInvActualAmountUsd',
            'chartInvForecastAmountIdr',
            'chartInvActualAmountIdr',
            'inventoryVarianceReasons',
            'supplierMonthlySummary',
            'supplierTotals',
            'supplierRanking',
            'chartSupplierLabels',
            'chartSupplierForecastAmount',
            'chartSupplierActualAmount',
            'chartSupplierForecastAmountUsd',
            'chartSupplierActualAmountUsd',
            'chartSupplierForecastAmountIdr',
            'chartSupplierActualAmountIdr',
            'chartSupplierForecastQty',
            'chartSupplierActualQty',
            'supplierMoMAnalytics',
            'supplierReconciliationPassed',
            'supplierItemMonthlyMap',
            'stockMonthlySummary',
            'stockMoMAnalytics',
            'comparisonDataset',
            'slide1ExecutiveSummary',
            'slide3TotalForecastStockQty',
            'slide3TotalActualStockQty',
            'slide3TotalForecastStockUsd',
            'slide3TotalActualStockUsd',
            'slide3TotalForecastStockIdr',
            'slide3TotalActualStockIdr',
            'slide3AvailablePeriodsCount',
            'slide3TotalPeriodsCount',
            'slide3TopVarianceItems',
            'slide3VendorSummaries',
            'allDisplayGridS3'
        ));
    }

    /**
     * Simpan alasan variansi stok inventory (Surplus/Defisit).
     */
    public function storeInventoryReason(Request $request)
    {
        $request->validate([
            'part_number' => 'required|string',
            'reason_category' => 'required|string',
            'reason_notes' => 'nullable|string',
        ]);

        $partNumber = strtoupper(trim((string)$request->input('part_number')));

        $record = \App\Models\InventoryVarianceReason::updateOrCreate(
            ['part_number' => $partNumber],
            [
                'variance_type'   => $request->input('variance_type', 'DEFICIT'),
                'reason_category' => $request->input('reason_category'),
                'reason_notes'    => $request->input('reason_notes'),
                'user_id'         => auth()->id() ?: 1,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Alasan variansi stok berhasil disimpan.',
            'data'    => $record,
        ]);
    }

    /**
     * Endpoint JSON untuk filter AJAX.
     */
    public function data(Request $request)
    {
        $periodeInput = $request->get('periode', 'All');
        $periode = ($periodeInput === 'All' || strcasecmp($periodeInput, 'Semua Periode') === 0 || empty($periodeInput))
            ? 'All'
            : $this->normalizePeriodString($periodeInput);
        $allAnalysisData = $this->buildAnalysis($periode);

        $selectedCategoryIds = collect($request->input('categories', []))
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();

        $categoryScopedData = empty($selectedCategoryIds)
            ? $allAnalysisData
            : $allAnalysisData->filter(fn ($row) => in_array((string) ($row->category_id ?: 'uncategorized'), $selectedCategoryIds, true))->values();

        $availablePartNumbers = $categoryScopedData->pluck('part_number')->sort()->values();
        $selectedPartNumbers = collect($request->input('part_numbers', []))
            ->filter(fn ($part) => $availablePartNumbers->contains($part))
            ->values()
            ->all();

        $analysisData = empty($selectedPartNumbers)
            ? $categoryScopedData
            : $categoryScopedData->whereIn('part_number', $selectedPartNumbers)->values();

        $chartGroups = $this->buildChartGroups($analysisData);

        $akuratCount      = $analysisData->where('status', 'Akurat')->count();
        $evaluasiCount    = $analysisData->where('status', 'Perlu Evaluasi')->count();
        $tidakAkuratCount = $analysisData->where('status', 'Tidak Akurat')->count();
        $avgAccuracy      = $analysisData->count() > 0
            ? round($analysisData->whereNotNull('forecast_accuracy')->avg('forecast_accuracy'), 2)
            : null;

        return response()->json([
            'periode'       => $periode,
            'rows'          => $analysisData->values(),
            'kpi' => [
                'total'       => $analysisData->count(),
                'akurat'      => $akuratCount,
                'evaluasi'    => $evaluasiCount,
                'tidak_akurat' => $tidakAkuratCount,
                'avg_accuracy' => $avgAccuracy,
            ],
            'chart' => [
                'labels' => collect($chartGroups)->pluck('label')->values(),
                'forecast' => collect($chartGroups)->pluck('forecast_qty')->values(),
                'actual' => collect($chartGroups)->pluck('actual_qty')->values(),
                'outstanding' => collect($chartGroups)->pluck('outstanding_qty')->values(),
            ],
            'chart_groups' => $chartGroups,
        ]);
    }

    /**
     * Helper mapping varian periode (e.g. 2026-07 <-> JULY <-> 07)
     */
    private function getPeriodVariants(string $periode): array
    {
        return $this->getPeriodVariantsString($periode);
    }

    /**
     * Core: sinkronisasi & kalkulasi analisis berdasarkan periode.
     */
    private function buildAnalysis(string $periode): \Illuminate\Support\Collection
    {
        $forecastQuery = Forecasting::query();
        $actualQuery = Actual::query();
        $outstandingQuery = Outstanding::query();

        if ($periode && $periode !== 'All' && strcasecmp($periode, 'Semua Periode') !== 0) {
            $periodVariants = $this->getPeriodVariants($periode);
            $forecastQuery->where(function ($q) use ($periodVariants) {
                $q->whereIn('periode', $periodVariants)->orWhereIn('period_month', $periodVariants);
            });
            $actualQuery->where(function ($q) use ($periodVariants) {
                $q->whereIn('periode', $periodVariants)->orWhereIn('period_month', $periodVariants);
            });
            $outstandingQuery->where(function ($q) use ($periodVariants) {
                $q->whereIn('periode', $periodVariants)->orWhereIn('period_month', $periodVariants);
            });
        }

        $forecastsList = $forecastQuery->get();
        $actualsList = $actualQuery->get();
        $outstandingsList = $outstandingQuery->get();

        // Sumber aktual utama adalah log penerimaan Step 3. Dengan ini grafik
        // langsung menghitung surat jalan yang sudah ada, termasuk data lama
        // yang belum sempat tersinkron ke tabel actuals.
        $receiptsQuery = \App\Models\PurchasingLog::query()
            ->whereNotNull('item_code')
            ->where('item_code', '!=', '');
        if ($periode && $periode !== 'All' && strcasecmp($periode, 'Semua Periode') !== 0) {
            $receiptsQuery->whereIn('period_month', $this->getPeriodVariants($periode));
        }
        $receiptActuals = $receiptsQuery->get()
            ->groupBy(fn ($receipt) => strtoupper(trim((string) $receipt->item_code)) . '___' . $this->normalizePeriodString($receipt->period_month))
            ->map(function ($receipts, $key) {
                [$itemCode, $month] = explode('___', $key, 2);
                $first = $receipts->first();
                return new Actual([
                    'part_number' => $itemCode,
                    'periode' => $month,
                    'period_month' => $month,
                    'description' => $first->item_name,
                    'actual_qty' => (int) $receipts->sum('actual_received'),
                    'actual_po' => (int) $receipts->sum('actual_received'),
                ]);
            });
        $actualsByKey = $actualsList->keyBy(fn ($row) => strtoupper(trim((string) $row->part_number)) . '___' . $this->normalizePeriodString($row->periode ?? $row->period_month));
        $actualsList = $actualsByKey->merge($receiptActuals)->values();
        // Analisis selalu menggunakan item code sebagai identitas. Nomor PO
        // hanya referensi transaksi, sehingga tidak boleh muncul sebagai part.
        $masterPurchasing = \App\Models\MasterPo::orderBy('tanggal')->get()
            ->groupBy(fn ($row) => strtoupper(trim((string) $row->item_code)));
        $knownItemCodes = $forecastsList->pluck('part_number')
            ->merge($actualsList->pluck('part_number'))
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter()
            ->unique();
        $outstandingsList = $outstandingsList->filter(fn ($row) =>
            $knownItemCodes->contains(strtoupper(trim((string) $row->part_number)))
        )->values();

        $map = [];

        foreach ($forecastsList as $item) {
            $part = (string) trim($item->part_number);
            $p = $this->normalizePeriodString($item->periode ?? $item->period_month ?? '');
            if (empty($p) || $p === 'All') {
                $p = ($periode && $periode !== 'All') ? $periode : $this->normalizePeriodString(now()->format('Y-m'));
            }
            $key = $part . '___' . $p;
            if (!isset($map[$key])) {
                $map[$key] = [
                    'part_number' => $part,
                    'periode'     => $p,
                    'forecast'    => $item,
                    'actual'      => null,
                    'outstanding' => null,
                ];
            } else {
                $map[$key]['forecast'] = $item;
            }
        }

        foreach ($actualsList as $item) {
            $part = (string) trim($item->part_number);
            $p = $this->normalizePeriodString($item->periode ?? $item->period_month ?? '');
            if (empty($p) || $p === 'All') {
                $p = ($periode && $periode !== 'All') ? $periode : $this->normalizePeriodString(now()->format('Y-m'));
            }
            $key = $part . '___' . $p;
            if (!isset($map[$key])) {
                $map[$key] = [
                    'part_number' => $part,
                    'periode'     => $p,
                    'forecast'    => null,
                    'actual'      => $item,
                    'outstanding' => null,
                ];
            } else {
                $map[$key]['actual'] = $item;
            }
        }

        foreach ($outstandingsList as $item) {
            $part = (string) trim($item->part_number);
            $p = $this->normalizePeriodString($item->periode ?? $item->period_month ?? '');
            if (empty($p) || $p === 'All') {
                $p = ($periode && $periode !== 'All') ? $periode : $this->normalizePeriodString(now()->format('Y-m'));
            }
            $key = $part . '___' . $p;
            if (!isset($map[$key])) {
                $map[$key] = [
                    'part_number' => $part,
                    'periode'     => $p,
                    'forecast'    => null,
                    'actual'      => null,
                    'outstanding' => $item,
                ];
            } else {
                $map[$key]['outstanding'] = $item;
            }
        }

        usort($map, function ($a, $b) {
            if ($a['periode'] === $b['periode']) {
                return strcmp((string) $a['part_number'], (string) $b['part_number']);
            }
            return strcmp($b['periode'], $a['periode']);
        });

        return collect($map)->map(function ($entry) use ($masterPurchasing) {
            $partNumber  = (string) $entry['part_number'];
            $rowPeriode  = $entry['periode'];
            $forecast    = $entry['forecast'];
            $actual      = $entry['actual'];
            $outstanding = $entry['outstanding'];
            $masterRows = $masterPurchasing->get(strtoupper(trim($partNumber)), collect());
            // Gunakan PO yang dibuat di Step 2 pada periode yang sama. Bila
            // belum ada PO pada bulan itu, tampilkan PO terakhir sebelumnya.
            $masterPo = $masterRows->first(fn ($row) => substr((string) $row->tanggal, 0, 7) === $rowPeriode)
                ?? $masterRows->filter(fn ($row) => substr((string) $row->tanggal, 0, 7) <= $rowPeriode)->sortByDesc('tanggal')->first()
                ?? $masterRows->sortByDesc('tanggal')->first();

            // Qty dari masing-masing master dengan fallback inter-tabel yang akurat (menghormati nilai 0)
            $forecastQty = 0;
            if ($forecast !== null) {
                $forecastQty = (int) $forecast->forecast_qty;
            }

            $actualQty = 0;
            if ($actual !== null) {
                // Step 3 adalah realisasi penerimaan PO. Nilai produksi tidak
                // boleh menjadi fallback karena akan mengubah arti aktual dan
                // membuat komparasi forecast vs penerimaan menjadi keliru.
                $actualQty = (int) $actual->actual_qty;
            }

            $outstandingQty = 0;
            if ($masterPo) {
                // Outstanding yang ditampilkan berasal dari PO Step 2: qty PO
                // dikurangi realisasi pada periode tersebut, bukan data lama.
                $outstandingQty = max(0, (int) $masterPo->qty - $actualQty);
            } elseif ($outstanding !== null) {
                $outstandingQty = (int) $outstanding->outstanding_qty;
            }

            // Description: prioritas forecast → actual → outstanding → masterPo
            $description = $forecast?->description
                ?? $actual?->description
                ?? $outstanding?->description
                ?? $masterPo?->name
                ?? '-';

            // PO dari master outstanding atau masterPo
            $po = $masterPo?->po ?? $outstanding?->po ?? '-';

            // ─── Kalkulasi ───────────────────────────────────────────────────
            // Forecast Accuracy = (actual / forecast) × 100
            $forecastAccuracy = null;
            if ($forecastQty > 0) {
                $forecastAccuracy = round(($actualQty / $forecastQty) * 100, 2);
            }

            // Gap Forecast vs Actual
            $gapForecastActual = $forecastQty - $actualQty;

            // Gap Forecast vs Outstanding
            $gapForecastOutstanding = $forecastQty - $outstandingQty;

            // Gap Actual vs Outstanding
            $gapActualOutstanding = $actualQty - $outstandingQty;

            // Selisih Outstanding vs Actual (Rumus khusus Comparison dari Prompt 1/4/5)
            // Selisih = Outstanding Qty - Actual Qty
            $selisihOutActual = $outstandingQty - $actualQty;
            $stockQty         = 0;

            // Penentuan Status Pasokan (Complete / Overstock / Cukup / Kurang)
            if ($forecastQty > 0) {
                if ($actualQty >= $forecastQty && $outstandingQty == 0) {
                    $statusPasokan      = 'Complete';
                    $statusPasokanClass = 'badge bg-success bg-opacity-25 text-success border border-success px-2 py-1';
                    $statusPasokanColor = '#10b981';
                } elseif (($actualQty + $outstandingQty) > $forecastQty || $actualQty > $forecastQty) {
                    $statusPasokan      = 'Overstock';
                    $statusPasokanClass = 'badge bg-danger bg-opacity-25 text-danger border border-danger px-2 py-1';
                    $statusPasokanColor = '#ef4444';
                } elseif (($actualQty + $outstandingQty) == $forecastQty && $outstandingQty > 0) {
                    $statusPasokan      = 'Cukup';
                    $statusPasokanClass = 'badge bg-info bg-opacity-25 text-info border border-info px-2 py-1';
                    $statusPasokanColor = '#3b82f6';
                } else {
                    $statusPasokan      = 'Kurang';
                    $statusPasokanClass = 'badge bg-warning bg-opacity-25 text-warning border border-warning px-2 py-1';
                    $statusPasokanColor = '#f59e0b';
                }
            } else {
                if ($outstandingQty == 0) {
                    $statusPasokan      = 'Complete';
                    $statusPasokanClass = 'badge bg-success bg-opacity-25 text-success border border-success px-2 py-1';
                    $statusPasokanColor = '#10b981';
                } else {
                    $statusPasokan      = 'Overstock';
                    $statusPasokanClass = 'badge bg-danger bg-opacity-25 text-danger border border-danger px-2 py-1';
                    $statusPasokanColor = '#ef4444';
                }
            }

            // ─── Status Accuracy ─────────────────────────────────────────────
            if ($forecastAccuracy === null) {
                $status      = 'Tidak Ada Forecast';
                $statusClass = 'status-no-data';
            } elseif ($forecastAccuracy >= 95) {
                $status      = 'Akurat';
                $statusClass = 'status-akurat';
            } elseif ($forecastAccuracy >= 85) {
                $status      = 'Perlu Evaluasi';
                $statusClass = 'status-evaluasi';
            } else {
                $status      = 'Tidak Akurat';
                $statusClass = 'status-tidak-akurat';
            }

            return (object) [
                'part_number'            => $partNumber,
                'description'            => $description,
                'category_id'            => null,
                'category_code'          => null,
                'category_name'          => 'Tanpa Kategori',
                'periode'                => $rowPeriode,
                'po'                     => $po,
                'forecast_qty'           => $forecastQty,
                'actual_qty'             => $actualQty,
                'outstanding_qty'        => $outstandingQty,
                'stock_qty'              => $stockQty,
                'forecast_accuracy'      => $forecastAccuracy,
                'gap_forecast_actual'    => $gapForecastActual,
                'gap_forecast_outstanding' => $gapForecastOutstanding,
                'gap_actual_outstanding' => $gapActualOutstanding,
                'selisih_out_actual'     => $selisihOutActual,
                'status_pasokan'         => $statusPasokan,
                'status_pasokan_class'   => $statusPasokanClass,
                'status_pasokan_color'   => $statusPasokanColor,
                'status'                 => $status,
                'status_class'           => $statusClass,
                'has_forecast'           => (bool) $forecast,
                'has_actual'             => (bool) $actual,
                'has_outstanding'        => (bool) $outstanding,
            ];
        })->reject(function ($item) {
            // Filter out baris kosong/ghost (0 semua) yang otomatis terbentuk pada periode tanpa Forecast
            return $item->forecast_qty === 0 && $item->actual_qty === 0 && $item->outstanding_qty === 0 && !$item->has_forecast;
        })->values();
    }

    /**
     * Halaman Khusus Comparison Outstanding vs Actual (Sesuai Prompt 3 & 5)
     */
    public function comparison(Request $request)
    {
        $periodeInput = $request->get('periode', 'All');
        $periode = ($periodeInput === 'All' || strcasecmp($periodeInput, 'Semua Periode') === 0 || empty($periodeInput))
            ? 'All'
            : $this->normalizePeriodString($periodeInput);
        $analysisData = $this->buildAnalysis($periode);

        // KPI Summary untuk Comparison
        $totalOutstanding = $analysisData->sum('outstanding_qty');
        $totalActual      = $analysisData->sum('actual_qty');
        $totalSelisih     = $totalOutstanding - $totalActual;
        $totalKurang      = $analysisData->where('status_pasokan', 'Kurang')->count();
        $totalCukup       = $analysisData->where('status_pasokan', 'Cukup')->count();
        $totalPas         = $analysisData->where('status_pasokan', 'Pas')->count();

        // Ringkasan hanya memuat kategori. Data kode item disediakan pada
        // chartGroups untuk ditampilkan setelah pengguna memilih kategori.
        $chartGroups = $this->buildChartGroups($analysisData);
        $chartLabels = collect($chartGroups)->pluck('label')->values();
        $chartOutstanding = collect($chartGroups)->pluck('outstanding_qty')->values();
        $chartActual = collect($chartGroups)->pluck('actual_qty')->values();

        $availablePeriodes = $this->getAvailablePeriodes();

        return view('purchasing.comparison', compact(
            'periode',
            'analysisData',
            'totalOutstanding',
            'totalActual',
            'totalSelisih',
            'totalKurang',
            'totalCukup',
            'totalPas',
            'chartLabels',
            'chartOutstanding',
            'chartActual',
            'chartGroups',
            'availablePeriodes'
        ));
    }

    /**
     * Endpoint API JSON Comparison Outstanding vs Actual (Sesuai Prompt 5 Poin 6)
     */
    public function comparisonData(Request $request)
    {
        $periodeInput = $request->get('periode', 'All');
        $periode = ($periodeInput === 'All' || strcasecmp($periodeInput, 'Semua Periode') === 0 || empty($periodeInput))
            ? 'All'
            : $this->normalizePeriodString($periodeInput);
        $analysisData = $this->buildAnalysis($periode);

        $totalOutstanding = $analysisData->sum('outstanding_qty');
        $totalActual      = $analysisData->sum('actual_qty');
        $totalSelisih     = $totalOutstanding - $totalActual;
        $totalKurang      = $analysisData->where('status_pasokan', 'Kurang')->count();
        $totalCukup       = $analysisData->where('status_pasokan', 'Cukup')->count();
        $totalPas         = $analysisData->where('status_pasokan', 'Pas')->count();

        return response()->json([
            'status' => 'success',
            'meta' => [
                'module'       => 'Comparison Outstanding vs Actual',
                'periode'      => $periode,
                'timestamp'    => now()->toIso8601String(),
                'generated_by' => 'PT Kawai Indonesia Purchasing System',
            ],
            'kpi_summary' => [
                'total_outstanding_qty' => $totalOutstanding,
                'total_actual_qty'      => $totalActual,
                'total_selisih_qty'     => $totalSelisih,
                'total_material_kurang' => $totalKurang,
                'total_material_cukup'  => $totalCukup,
                'total_material_pas'    => $totalPas,
            ],
            'charts' => [
                'grouped_bar' => [
                    'labels'   => collect($this->buildChartGroups($analysisData))->pluck('label')->values(),
                    'datasets' => [
                        [
                            'label'           => 'Outstanding Qty',
                            'data'            => collect($this->buildChartGroups($analysisData))->pluck('outstanding_qty')->values(),
                            'backgroundColor' => '#fbbf24',
                        ],
                        [
                            'label'           => 'Actual Production Qty',
                            'data'            => collect($this->buildChartGroups($analysisData))->pluck('actual_qty')->values(),
                            'backgroundColor' => '#34d399',
                        ],
                    ],
                ],
                'donut_status' => [
                    'labels' => ['Cukup', 'Pas', 'Kurang'],
                    'data'   => [$totalCukup, $totalPas, $totalKurang],
                    'colors' => ['#10b981', '#f59e0b', '#ef4444'],
                ],
            ],
            'chart_groups' => $this->buildChartGroups($analysisData),
            'data_rows' => $analysisData->values()->map(function($r) {
                return [
                    'part_number'     => $r->part_number,
                    'description'     => $r->description,
                    'supplier'        => '-', // supplier info ditarik dari master outstanding jika ada
                    'po_number'       => $r->po,
                    'outstanding_qty' => $r->outstanding_qty,
                    'actual_qty'      => $r->actual_qty,
                    'stock'           => $r->stock_qty,
                    'selisih'         => $r->selisih_out_actual,
                    'status'          => $r->status_pasokan,
                    'status_color'    => $r->status_pasokan_color,
                ];
            }),
        ]);
    }

    /**
     * Siapkan dua tingkat data grafik: kategori sebagai ringkasan dan part
     * number sebagai detail. Kategori kosong tetap ditampilkan agar data lama
     * tidak hilang dari laporan.
     */
    private function buildChartGroups(\Illuminate\Support\Collection $analysisData): array
    {
        return $analysisData
            ->groupBy(function ($row) {
                return $row->category_id
                    ? 'category-' . $row->category_id
                    : 'uncategorized';
            })
            ->map(function ($rows, $key) {
                $first = $rows->first();
                $label = $first->category_id
                    ? trim(($first->category_code ? $first->category_code . ' - ' : '') . $first->category_name)
                    : 'Tanpa Kategori';

                return [
                    'key' => $key,
                    'label' => $label,
                    'item_count' => $rows->count(),
                    'forecast_qty' => (int) $rows->sum('forecast_qty'),
                    'actual_qty' => (int) $rows->sum('actual_qty'),
                    'outstanding_qty' => (int) $rows->sum('outstanding_qty'),
                    'items' => $rows->sortBy('part_number')->values()->map(fn ($row) => [
                        'part_number' => $row->part_number,
                        'forecast_qty' => (int) $row->forecast_qty,
                        'actual_qty' => (int) $row->actual_qty,
                        'outstanding_qty' => (int) $row->outstanding_qty,
                        'selisih' => (int) $row->selisih_out_actual,
                        'status' => $row->status_pasokan,
                    ])->all(),
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    /**
     * Kumpulkan daftar semua periode dari tiga master.
     */
    private function getAvailablePeriodes(): \Illuminate\Support\Collection
    {
        $fp = Forecasting::whereNotNull('periode')->pluck('periode');
        $fm = Forecasting::whereNotNull('period_month')->pluck('period_month');
        $ap = Actual::whereNotNull('periode')->pluck('periode');
        $am = Actual::whereNotNull('period_month')->pluck('period_month');
        $op = Outstanding::whereNotNull('periode')->pluck('periode');
        $om = Outstanding::whereNotNull('period_month')->pluck('period_month');

        return $fp->merge($fm)->merge($ap)->merge($am)->merge($op)->merge($om)
            ->filter()
            ->map(fn ($p) => $this->normalizePeriodString((string) $p))
            ->unique()
            ->sortDesc()
            ->values();
    }
    public function outstandingPo(Request $request)
    {
        $search = $request->get('search');
        $selectedVendor = $request->get('vendor', 'ALL');
        $selectedPic = $request->get('pic', 'ALL');
        $selectedDeliveryCategory = $request->get('delivery_category', 'ALL');

        $masterPoQuery = \App\Models\MasterPo::orderBy('tanggal', 'desc')->orderBy('id', 'desc');
        if ($selectedDeliveryCategory && $selectedDeliveryCategory !== 'ALL') {
            $masterPoQuery->where('delivery_category_code', $selectedDeliveryCategory);
        }
        if ($search) {
            $masterPoQuery->where(function($q) use ($search) {
                $q->where('po', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%");
            });
        }
        $masterPoList = $masterPoQuery->get();

        $allReceipts = \App\Models\PurchasingLog::all();
        $receiptsExactMap = [];
        $receiptsBaseMap  = [];

        foreach ($allReceipts as $rec) {
            $recPo = \App\Services\DataValidation\InputNormalizer::canonicalPoNumber($rec->po_reference);
            $recBasePo = \App\Services\DataValidation\InputNormalizer::basePoNumber($rec->po_reference);
            $recItem = \App\Services\DataValidation\InputNormalizer::cleanMaterialCode($rec->item_code);

            $exactKey = $recPo . '___' . $recItem;
            $baseKey  = $recBasePo . '___' . $recItem;

            $receiptsExactMap[$exactKey] = ($receiptsExactMap[$exactKey] ?? 0) + (int)$rec->actual_received;
            $receiptsBaseMap[$baseKey]   = ($receiptsBaseMap[$baseKey] ?? 0) + (int)$rec->actual_received;
        }

        // Load Step 1 outstandings for Vendor & PIC mapping
        $step1Items = \App\Models\PurchasingOutstanding::with('user')->get()->keyBy(fn($x) => \App\Services\DataValidation\InputNormalizer::cleanMaterialCode($x->drawing ?: $x->part_number));

        $outstandingData = $masterPoList->map(function($mp) use ($receiptsExactMap, $receiptsBaseMap, $step1Items) {
            $poExact = \App\Services\DataValidation\InputNormalizer::canonicalPoNumber($mp->po);
            $poBase  = \App\Services\DataValidation\InputNormalizer::basePoNumber($mp->po);
            $itemCodeKey = \App\Services\DataValidation\InputNormalizer::cleanMaterialCode($mp->item_code);

            $exactKey = $poExact . '___' . $itemCodeKey;
            $baseKey  = $poBase . '___' . $itemCodeKey;
            $step1 = $step1Items->get($itemCodeKey);

            $qtyReceipt = $receiptsExactMap[$exactKey] ?? ($receiptsBaseMap[$baseKey] ?? 0);
            $qtyPo = (int)$mp->qty;
            $outstandingQty  = max($qtyPo - $qtyReceipt, 0);
            $overDeliveryQty = max($qtyReceipt - $qtyPo, 0);

            $supplierName = $step1 ? ($step1->supplier_name ?: $mp->supplier) : ($mp->supplier ?: null);
            $supplierName = \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($supplierName);
            
            $picBuyer = '-';
            if ($step1 && $step1->user) {
                $picBuyer = $step1->user->name;
            } elseif ($step1 && !empty($step1->pic_buyer) && !str_contains($step1->pic_buyer, 'Ahmad Faisal')) {
                $picBuyer = $step1->pic_buyer;
            } elseif ($step1 && $step1->category && $step1->category->buyer) {
                $picBuyer = $step1->category->buyer->name;
            } elseif ($step1 && $step1->category && !empty($step1->category->pic_buyer)) {
                $picBuyer = $step1->category->pic_buyer;
            }

            return (object)[
                'po' => $mp->po,
                'item_code' => $mp->item_code,
                'description' => $mp->name,
                'qty_po' => $qtyPo,
                'qty_receipt' => $qtyReceipt,
                'outstanding_qty' => $outstandingQty,
                'diff' => $overDeliveryQty,
                'over_delivery_qty' => $overDeliveryQty,
                'supplier' => $supplierName,
                'pic_buyer' => $picBuyer,
                'tanggal' => $mp->tanggal,
                'delivery_category_code' => $mp->delivery_category_code ?? 'LOC',
                'delivery_category_badge' => $mp->delivery_category_badge,
            ];
        });

        $availableItemCodes = $outstandingData->pluck('item_code')->unique()->sort()->values();
        $availableVendors   = $outstandingData->pluck('supplier')
            ->map(fn($s) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($s))
            ->filter()->unique()->sort()->values();
        $availablePics      = $outstandingData->pluck('pic_buyer')->filter(fn($x) => $x !== '-')->unique()->sort()->values();
        $availablePoNumbers = $outstandingData->pluck('po')->filter()->unique()->sort()->values();

        $selectedVendor = $request->get('vendor', 'ALL');
        $selectedPic    = $request->get('pic', 'ALL');
        $selectedPo     = $request->get('po', 'ALL');
        $monthsRange    = $request->get('months_range', 'ALL');

        if ($selectedVendor !== 'ALL') {
            $normSelectedVendor = \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($selectedVendor);
            $outstandingData = $outstandingData->filter(fn($x) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($x->supplier) === $normSelectedVendor);
        }

        if ($selectedPic !== 'ALL') {
            $outstandingData = $outstandingData->filter(fn($x) => strtoupper($x->pic_buyer) === strtoupper($selectedPic));
        }

        if ($selectedPo !== 'ALL') {
            $outstandingData = $outstandingData->filter(fn($x) => strtoupper($x->po) === strtoupper($selectedPo));
        }

        if ($monthsRange !== 'ALL') {
            $limitMonths = (int)$monthsRange;
            if ($limitMonths > 0) {
                $cutoffDate = date('Y-m-d', strtotime("-{$limitMonths} months"));
                $outstandingData = $outstandingData->filter(function($x) use ($cutoffDate) {
                    return empty($x->tanggal) || $x->tanggal >= $cutoffDate;
                });
            }
        }

        // Re-index array so JSON output is always a JavaScript Array [...]
        $outstandingData = $outstandingData->values();

        return view('purchasing.outstanding_po', compact(
            'outstandingData',
            'availableItemCodes',
            'availableVendors',
            'availablePics',
            'availablePoNumbers',
            'selectedVendor',
            'selectedPic',
            'selectedPo',
            'selectedDeliveryCategory',
            'monthsRange',
            'search'
        ) + ['deliveryCategories' => \App\Models\DeliveryCategory::all()]);
    }
    private function getCalendarMonthForIndex(int $index, string $startMonth, int $baseYear = 2026): string
    {
        $allMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $sUpper = strtoupper(trim($startMonth));
        $startIndex = array_search($sUpper, $allMonths);
        if ($startIndex === false) {
            if (in_array($sUpper, ['DES', 'DECEMBER', 'DEC'], true)) {
                $startIndex = 11;
            } else {
                $startIndex = 6; // July default
            }
        }
        
        // Special handling for DEC baseline: DEC is Month 0 (baseYear - 1, e.g., 2025-12).
        // Month 1 is JAN of baseYear (2026-01).
        if ($startIndex === 11) { // DEC
            if ($index === 0) {
                return ($baseYear - 1) . '-12';
            }
            $targetMonthIndex = ($index - 1) % 12;
            $yearsToAdd = floor(($index - 1) / 12);
            $targetYear = $baseYear + (int)$yearsToAdd;
            return $targetYear . '-' . str_pad($targetMonthIndex + 1, 2, '0', STR_PAD_LEFT);
        }

        $totalMonths = $startIndex + $index;
        $targetMonthIndex = $totalMonths % 12;
        $yearsToAdd = floor($totalMonths / 12);
        $targetYear = $baseYear + (int)$yearsToAdd;

        return $targetYear . '-' . str_pad($targetMonthIndex + 1, 2, '0', STR_PAD_LEFT);
    }

    private function parseYearMonth($dateStr, ?string $periodStr = null, int $defaultYear = 2026): ?string
    {
        if ($dateStr instanceof \DateTimeInterface) {
            return $dateStr->format('Y-m');
        }
        if (!empty($dateStr)) {
            $dStr = trim((string)$dateStr);

            // 1. Format YYYY-MM-DD or YYYY/MM/DD
            if (preg_match('/^(\d{4})[\-\/](\d{1,2})/', $dStr, $m)) {
                return $m[1] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT);
            }

            // 2. Format DD/MM/YYYY or DD-MM-YYYY (e.g. 03/01/2026 -> 2026-01)
            if (preg_match('/^(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})/', $dStr, $m)) {
                return $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT);
            }

            // 3. Fallback: convert slashes to dashes for European/Indonesian date interpretation (DD-MM-YYYY)
            $cleanStr = str_replace('/', '-', $dStr);
            $time = strtotime($cleanStr);
            if ($time !== false && $time > 0) {
                return date('Y-m', $time);
            }
        }

        if (!empty($periodStr)) {
            $pm = trim($periodStr);
            if (preg_match('/^\d{4}-\d{2}$/', $pm)) {
                return $pm;
            }
            $monthMap = [
                'JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04','MAY'=>'05','MEI'=>'05',
                'JUN'=>'06','JULY'=>'07','JUL'=>'07','AUG'=>'08','AGS'=>'08','SEP'=>'09',
                'OCT'=>'10','OKT'=>'10','NOV'=>'11','DEC'=>'12','DES'=>'12'
            ];
            $mUpper = strtoupper($pm);
            if (isset($monthMap[$mUpper])) {
                return $defaultYear . '-' . $monthMap[$mUpper];
            }
        }

        return null;
    }
}
