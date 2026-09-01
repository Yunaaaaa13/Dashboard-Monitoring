<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\PurchasingOutstanding;
use App\Models\MasterPo;
use App\Models\PurchasingLog;
use App\Models\TaxBudgetForecastRate;
use App\Models\Forecasting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display Step 6: Aktual Inventory Dashboard with full integration
     * (Inventory Demand / Forecast, Actual Inventory, Outstanding PO, and Supply Coverage Analysis).
     */
    public function index(Request $request)
    {
        $search         = $request->get('search');
        $itemCode       = $request->get('item_code', 'ALL');
        $plantFilter    = $request->get('plant', 'ALL');
        $supplierFilter = $request->get('supplier', 'ALL');
        $statusFilter   = $request->get('status_filter', 'ALL');

        // ── 1. PRE-FETCH DATA INTEGRASI (PO & INCOMING LOGS KESELURUHAN & PER PERIODE) ──
        $poSummaries = MasterPo::select('item_code', DB::raw('SUM(qty) as total_po_qty'), DB::raw('AVG(price) as avg_po_price'), DB::raw('MAX(currency) as currency'))
            ->whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->groupBy('item_code')
            ->get()
            ->keyBy(fn($item) => strtoupper(trim((string)$item->item_code)));

        $receiptSummaries = PurchasingLog::select('item_code', DB::raw('SUM(actual_received) as total_receipt_qty'), DB::raw('AVG(price) as avg_actual_price'), DB::raw('MAX(currency) as currency'))
            ->whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->groupBy('item_code')
            ->get()
            ->keyBy(fn($item) => strtoupper(trim((string)$item->item_code)));

        // Pre-fetch PO dan Incoming per periode (YYYY-MM)
        $poByPeriod = MasterPo::selectRaw("item_code, SUBSTRING(tanggal, 1, 7) as ym, SUM(qty) as sum_qty, AVG(price) as avg_price, MAX(currency) as currency")
            ->whereNotNull('item_code')
            ->groupBy('item_code', 'ym')
            ->get()
            ->groupBy(fn($x) => strtoupper(trim((string)$x->item_code)));

        $receiptByPeriod = PurchasingLog::selectRaw("item_code, period_month as ym, SUM(actual_received) as sum_received, AVG(price) as avg_price, MAX(currency) as currency")
            ->whereNotNull('item_code')
            ->groupBy('item_code', 'period_month')
            ->get()
            ->groupBy(fn($x) => strtoupper(trim((string)$x->item_code)));

        // Kurs Budget terkini
        $latestBudgetRateRecord = TaxBudgetForecastRate::orderBy('exch_year', 'desc')->orderBy('exch_month', 'desc')->first();
        $budgetExchangeRate = $latestBudgetRateRecord ? (int) $latestBudgetRateRecord->budget_rate : 16600;

        // ── 2. SUMBER UTAMA TABEL: DATA DARI TABEL INVENTORIES DILENGKAPI DATA MASTER PART ──
        $rawInventoryRecords = Inventory::orderBy('tanggal_inventory', 'desc')->orderBy('id', 'desc')->get();
        $integratedMatrix = collect();

        // Master outstandings untuk referensi harga & fallback deskripsi
        $allOutstandingsByPart = PurchasingOutstanding::all()->keyBy(fn($x) => strtoupper(trim((string)$x->part_number)));
        $allOutstandingsByDrawing = PurchasingOutstanding::whereNotNull('drawing')->where('drawing', '!=', '')->get()->keyBy(fn($x) => strtoupper(trim((string)$x->drawing)));
        $allOutstandings = $allOutstandingsByPart->merge($allOutstandingsByDrawing);
        $allForecasting = Forecasting::all()->groupBy(fn($x) => strtoupper(trim((string)$x->part_number)));

        $latestSnapshotDateRaw = $rawInventoryRecords->max('tanggal_inventory');
        $latestSnapshotDate = $latestSnapshotDateRaw ? Carbon::parse($latestSnapshotDateRaw)->format('d M Y') : date('d M Y');
        $snapshotCanonicalPeriod = $latestSnapshotDateRaw ? date('Y-m', strtotime($latestSnapshotDateRaw)) : '2026-07';

        // Tentukan periode default (mengikuti snapshot fisik terkini, misal 2026-07)
        $periodFilter = $request->get('period', $request->get('periode', $snapshotCanonicalPeriod));

        // Jika mode ALL atau periode spesifik dipilih:
        $inventoryRecords = collect();
        if ($periodFilter !== 'ALL') {
            $matchingInventories = $rawInventoryRecords->filter(fn($x) => str_starts_with((string)$x->tanggal_inventory, $periodFilter))->unique(fn($x) => strtoupper(trim((string)$x->part_number)) . '|' . strtoupper(trim((string)($x->factory_code ?? ''))))->values();
            if ($matchingInventories->isNotEmpty()) {
                $inventoryRecords = $matchingInventories;
            } else {
                // Generate data dari 307 master part untuk periode yang dipilih
                $pDate = $periodFilter . '-01';
                foreach ($allOutstandingsByPart as $code => $os) {
                    $inventoryRecords->push((object)[
                        'id'                => null,
                        'part_number'       => $code,
                        'description'       => $os->description ?: 'Material Item',
                        'supplier_name'     => $os->supplier_name ?: '-',
                        'supplier_code'     => $os->supplier_code ?? '-',
                        'factory_code'      => $os->factory_code ?: 'KIP1',
                        'current_stock'     => 0,
                        'tanggal_inventory' => $pDate,
                    ]);
                }
            }
        } else {
            // Saat mode ALL, ambil snapshot fisik terbaru per Part Number + Factory Code (307 part posisi)
            $inventoryRecords = $rawInventoryRecords->unique(fn($x) => strtoupper(trim((string)$x->part_number)) . '|' . strtoupper(trim((string)($x->factory_code ?? ''))))->values();
        }

        foreach ($inventoryRecords as $inv) {
            $code = strtoupper(trim((string)$inv->part_number));
            if (!$code) continue;

            $os = $allOutstandingsByPart->get($code) ?? $allOutstandingsByDrawing->get($code);
            $desc = $inv->description ?: ($os?->description ?: 'Material Item');
            $supplier = $inv->supplier_name ?: ($os?->supplier_name ?: '-');
            $supplierCode = $inv->supplier_code ?: ($os?->supplier_code ?? '-');
            $factory = $inv->factory_code ?: ($os?->factory_code ?: 'KIP1');
            $actualStock = (int) $inv->current_stock;
            $lastStockDate = $inv->tanggal_inventory ? Carbon::parse($inv->tanggal_inventory)->format('d/m/Y') : '-';
            $periodCarbon = $inv->tanggal_inventory ? Carbon::parse($inv->tanggal_inventory) : null;
            $periodKey = $periodCarbon ? $periodCarbon->format('Y-m') : '';
            $periodLabel = $periodCarbon ? $periodCarbon->translatedFormat('F Y') : '-';

            // 1. Inventory Demand untuk item ini (Target Kebutuhan dari Forecast Monitoring Ratio & Production Plan)
            $inventoryDemand = 0;
            $fcRecords = $allForecasting->get($code);
            $recordPeriod = $periodCarbon ? $periodCarbon->format('Y-m') : ($periodFilter !== 'ALL' ? $periodFilter : $snapshotCanonicalPeriod);

            // Prioritas 1: Cek tabel detail Forecasting untuk periode yang sesuai dengan record input ini
            if ($fcRecords && $fcRecords->isNotEmpty()) {
                $fcMatchingPeriod = $fcRecords->firstWhere('period_month', $recordPeriod) 
                    ?: ($fcRecords->firstWhere('periode', $recordPeriod)
                    ?: $fcRecords->firstWhere('period_month', $snapshotCanonicalPeriod));
                
                if ($fcMatchingPeriod) {
                    $inventoryDemand = (int) ($fcMatchingPeriod->production_qty ?: $fcMatchingPeriod->po_qty);
                }
            }

            // Prioritas 2: Jika belum ada di forecasting detail, ambil dari PurchasingOutstanding
            if ($inventoryDemand === 0 && $os) {
                // Ambil target kebutuhan langsung dari Forecast Monitoring Ratio (Step 1): PROD -> PO -> Order Qty
                $inventoryDemand = (int) ($os->m1_prod ?: ($os->m1_po ?: ($os->m0_prod ?: ($os->m2_prod ?: ($os->order_qty ?: 0)))));
            }

            // Prioritas 3: Fallback ke record forecasting manapun yang memiliki demand > 0
            if ($inventoryDemand === 0 && $fcRecords && $fcRecords->isNotEmpty()) {
                $fcWithQty = $fcRecords->first(fn($f) => ($f->production_qty > 0 || $f->po_qty > 0));
                if ($fcWithQty) {
                    $inventoryDemand = (int) ($fcWithQty->production_qty ?: $fcWithQty->po_qty);
                }
            }

            $isMatched = ($os !== null || ($fcRecords && $fcRecords->isNotEmpty()));

            // 2. PO & Receipt & Outstanding (Spesifik per periode atau akumulatif jika ALL)
            $poData = $poSummaries->get($code);
            $rcptData = $receiptSummaries->get($code);

            if ($periodFilter !== 'ALL') {
                $poRec = $poByPeriod->get($code)?->firstWhere('ym', $recordPeriod);
                $poQty = $poRec ? (int)$poRec->sum_qty : ($poData ? (int) $poData->total_po_qty : 0);

                $rcptRec = $receiptByPeriod->get($code)?->firstWhere('ym', $recordPeriod);
                $rcptQty = $rcptRec ? (int)$rcptRec->sum_received : 0;
            } else {
                $poQty = $poData ? (int) $poData->total_po_qty : 0;
                $rcptQty = $rcptData ? (int) $rcptData->total_receipt_qty : 0;
            }

            $outstandingPoQty = max(0, $poQty - $rcptQty);
            $overDeliveryQty  = max(0, $rcptQty - $poQty);

            // 3. Potential Supply = Actual Inventory + Outstanding PO
            $potentialSupply = $actualStock + $outstandingPoQty;

            // 4. Gap Calculations, Additional Requirement & Coverage
            $inventoryGap = $actualStock - $inventoryDemand;        // Gap stok fisik vs kebutuhan
            $netSupplyGap = $potentialSupply - $inventoryDemand;    // Gap supply total vs kebutuhan
            $additionalRequirement = max(0, $inventoryDemand - $potentialSupply);
            $itemCoverage = $inventoryDemand > 0 ? round(($potentialSupply / $inventoryDemand) * 100, 1) : 100.0;

            // 5. 4 Status Penentuan Kebutuhan Tindak Lanjut Purchasing (Standar Baku)
            if ($inventoryDemand > 0) {
                if ($actualStock >= $inventoryDemand) {
                    $status = 'SURPLUS';
                    $statusLabel = 'Surplus / Stok Cukup';
                    $statusBadge = 'badge bg-success bg-opacity-25 text-success border border-success border-opacity-50';
                    $actionNote = 'Stok fisik mencukupi rencana produksi (+' . number_format($inventoryGap) . ' unit).';
                } elseif ($potentialSupply >= $inventoryDemand) {
                    $status = 'COVERED_BY_PO';
                    $statusLabel = 'Terpenuhi via PO';
                    $statusBadge = 'badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50';
                    $actionNote = 'Stok fisik kurang, namun aman tercover PO aktif (' . number_format($outstandingPoQty) . ' unit).';
                } else {
                    $status = 'CRITICAL_DEFICIT';
                    $statusLabel = 'Defisit / Perlu PO';
                    $statusBadge = 'badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50';
                    $actionNote = 'Defisit pasokan: Butuh pengadaan tambahan ' . number_format($additionalRequirement) . ' unit (Coverage ' . $itemCoverage . '%).';
                }
            } else {
                $status = 'OPTIMAL';
                $statusLabel = $actualStock > 0 ? 'Stok Tersedia (No Demand)' : 'Optimal / No Demand';
                $statusBadge = 'badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-50';
                $actionNote = $actualStock > 0 ? 'Stok fisik tersedia di gudang tanpa rencana forecast aktif.' : 'Stok 0 dan kebutuhan 0.';
            }

            // 6. Normalisasi Valuasi Multi-Currency ($ USD & Rp IDR)
            $price = 0.0;
            $currency = 'USD';
            $priceSource = 'Estimasi';

            if ($os && $os->price > 0) {
                $price = (float) $os->price;
                $currency = strtoupper(trim($os->currency ?: ($price > 500 ? 'IDR' : 'USD')));
                $priceSource = 'Master Outstanding';
            } elseif ($poData && $poData->avg_po_price > 0) {
                $price = (float) $poData->avg_po_price;
                $currency = strtoupper(trim($poData->currency ?: ($price > 500 ? 'IDR' : 'USD')));
                $priceSource = 'Master PO';
            } elseif ($rcptData && $rcptData->avg_actual_price > 0) {
                $price = (float) $rcptData->avg_actual_price;
                $currency = strtoupper(trim($rcptData->currency ?: ($price > 500 ? 'IDR' : 'USD')));
                $priceSource = 'Incoming Log';
            }

            if ($currency === 'IDR' || $price > 500) {
                $unitPriceIdr = $price;
                $unitPriceUsd = round($price / $budgetExchangeRate, 4);
                $inventoryValIdr = (int) round($actualStock * $unitPriceIdr);
                $inventoryValUsd = round($inventoryValIdr / $budgetExchangeRate, 2);
                $demandValIdr    = (int) round($inventoryDemand * $unitPriceIdr);
                $demandValUsd    = round($demandValIdr / $budgetExchangeRate, 2);
            } else {
                $unitPriceUsd = $price;
                $unitPriceIdr = (int) round($price * $budgetExchangeRate);
                $inventoryValUsd = round($actualStock * $unitPriceUsd, 2);
                $inventoryValIdr = (int) round($inventoryValUsd * $budgetExchangeRate);
                $demandValUsd    = round($inventoryDemand * $unitPriceUsd, 2);
                $demandValIdr    = (int) round($demandValUsd * $budgetExchangeRate);
            }

            $integratedMatrix->push((object)[
                'id'                     => $inv->id,
                'inventory_id'           => $inv->id,
                'part_number'            => $code,
                'description'            => $desc,
                'supplier_code'          => $supplierCode,
                'supplier_name'          => $supplier,
                'factory_code'           => $factory,
                'actual_stock'           => $actualStock,
                'tanggal_inventory'      => $inv->tanggal_inventory,
                'last_stock_date'        => $lastStockDate,
                'period_key'             => $periodKey,
                'period_label'           => $periodLabel,
                'inventory_demand'       => $inventoryDemand,
                'forecast_demand'        => $inventoryDemand,
                'po_qty'                 => $poQty,
                'receipt_qty'            => $rcptQty,
                'outstanding_po_qty'     => $outstandingPoQty,
                'over_delivery_qty'      => $overDeliveryQty,
                'potential_supply'       => $potentialSupply,
                'inventory_gap'          => $inventoryGap,
                'net_supply_gap'         => $netSupplyGap,
                'additional_requirement' => $additionalRequirement,
                'coverage_pct'           => $itemCoverage,
                'status'                 => $status,
                'status_label'           => $statusLabel,
                'status_badge'           => $statusBadge,
                'action_note'            => $actionNote,
                'is_matched'             => $isMatched,
                'currency'               => $currency,
                'price_source'           => $priceSource,
                'unit_price_usd'         => $unitPriceUsd,
                'unit_price_idr'         => $unitPriceIdr,
                'inventory_val_usd'      => $inventoryValUsd,
                'inventory_val_idr'      => $inventoryValIdr,
                'demand_val_usd'         => $demandValUsd,
                'demand_val_idr'         => $demandValIdr,
            ]);
        }

        // ── 3. FILTERING PADA TABEL AKTUAL INVENTORY ──
        $filteredMatrix = $integratedMatrix;

        if ($search) {
            $sLower = strtolower($search);
            $filteredMatrix = $filteredMatrix->filter(function($item) use ($sLower) {
                return str_contains(strtolower($item->part_number), $sLower)
                    || str_contains(strtolower($item->description), $sLower)
                    || str_contains(strtolower($item->supplier_name), $sLower)
                    || str_contains(strtolower($item->supplier_code), $sLower)
                    || str_contains(strtolower($item->factory_code), $sLower);
            });
        }

        if ($itemCode && $itemCode !== 'ALL') {
            $filteredMatrix = $filteredMatrix->filter(fn($x) => $x->part_number === $itemCode);
        }

        if ($plantFilter && $plantFilter !== 'ALL') {
            $filteredMatrix = $filteredMatrix->filter(fn($x) => strtoupper($x->factory_code) === strtoupper($plantFilter));
        }

        if ($supplierFilter && $supplierFilter !== 'ALL') {
            $normSup = \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($supplierFilter);
            $filteredMatrix = $filteredMatrix->filter(fn($x) => strtoupper($x->supplier_code) === strtoupper($supplierFilter) || \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($x->supplier_name) === $normSup);
        }

        if ($periodFilter && $periodFilter !== 'ALL') {
            $filteredMatrix = $filteredMatrix->filter(function($item) use ($periodFilter) {
                return $item->period_key === $periodFilter || str_starts_with((string)$item->tanggal_inventory, $periodFilter);
            });
        }

        if ($statusFilter && $statusFilter !== 'ALL') {
            $filteredMatrix = $filteredMatrix->filter(fn($x) => $x->status === $statusFilter);
        }

        // ── 4. PERHITUNGAN KPI TERFILTER & STANDARISASI DATA GRAIN ──
        $kpiTotalInventoryQty        = $filteredMatrix->sum('actual_stock');
        $kpiTotalInventoryValUsd     = round($filteredMatrix->sum('inventory_val_usd'), 2);
        $kpiTotalInventoryValIdr     = (int) $filteredMatrix->sum('inventory_val_idr');
        $kpiTotalInventoryDemand     = $filteredMatrix->sum('inventory_demand');
        $kpiTotalForecastDemand      = $kpiTotalInventoryDemand; // alias
        $kpiTotalDemandValUsd        = round($filteredMatrix->sum('demand_val_usd'), 2);
        $kpiTotalDemandValIdr        = (int) $filteredMatrix->sum('demand_val_idr');
        $kpiTotalOutstandingPo       = $filteredMatrix->sum('outstanding_po_qty');
        $kpiTotalPotentialSupply     = $filteredMatrix->sum('potential_supply');
        $kpiTotalPositions           = $filteredMatrix->count();
        $kpiUniqueMaterialsCount     = $filteredMatrix->pluck('part_number')->unique()->count();
        $kpiTotalUniqueItems         = $kpiTotalPositions; // backward compatibility
        $kpiNetSupplyGap             = $kpiTotalPotentialSupply - $kpiTotalInventoryDemand;
        $kpiAdditionalRequirement    = max(0, $kpiTotalInventoryDemand - $kpiTotalPotentialSupply);

        $kpiSurplusCount             = $filteredMatrix->where('status', 'SURPLUS')->count();
        $kpiCoveredByPoCount         = $filteredMatrix->where('status', 'COVERED_BY_PO')->count();
        $kpiCriticalDeficitCount     = $filteredMatrix->where('status', 'CRITICAL_DEFICIT')->count();
        $kpiOptimalCount             = $filteredMatrix->where('status', 'OPTIMAL')->count();

        $kpiCoveragePercentage       = $kpiTotalInventoryDemand > 0
            ? round(($kpiTotalPotentialSupply / $kpiTotalInventoryDemand) * 100, 1)
            : 100.0;

        // Data Quality & Integration Metrics (Pemisahan grain: SKU Unik vs Posisi Fisik)
        $totalForecastMaterials      = $allOutstandings->count() ?: $kpiUniqueMaterialsCount;
        $matchedUniqueMaterialsCount = $filteredMatrix->filter(fn($x) => $x->is_matched)->pluck('part_number')->unique()->count();
        $matchedMaterialsCount       = $matchedUniqueMaterialsCount;
        $matchPercentage             = $kpiUniqueMaterialsCount > 0 ? round(($matchedUniqueMaterialsCount / $kpiUniqueMaterialsCount) * 100, 1) : 100.0;

        // Scorecard
        $dataQualityScorecard = [
            'total_positions'        => $kpiTotalPositions,
            'unique_materials'       => $kpiUniqueMaterialsCount,
            'matched_materials'      => $matchedUniqueMaterialsCount,
            'match_rate_pct'         => $matchPercentage,
            'negative_stock_records' => $filteredMatrix->where('actual_stock', '<', 0)->count(),
            'price_coverage_pct'     => $kpiTotalPositions > 0 ? round(($filteredMatrix->where('unit_price_usd', '>', 0)->count() / $kpiTotalPositions) * 100, 1) : 100.0,
            'snapshot_date'          => $latestSnapshotDate,
            'planning_horizon'       => Carbon::parse($latestSnapshotDateRaw ?: date('Y-m-d'))->translatedFormat('F Y'),
            'overall_status'         => ($matchPercentage >= 95 && $filteredMatrix->where('actual_stock', '<', 0)->count() === 0) ? 'GOOD' : 'REVIEW_REQUIRED',
        ];

        // ── 5. CHART DATASETS: FULL SET FOR EXECUTIVE COMPARISON (Dynamic Limit: Top 10, 25, 50, All & Period Summary) ──
        $allChartItems = $filteredMatrix
            ->filter(fn($x) => ($x->inventory_demand > 0 || $x->actual_stock > 0 || $x->outstanding_po_qty > 0))
            ->sortByDesc(fn($x) => max($x->inventory_demand, $x->potential_supply, $x->actual_stock))
            ->map(function($x) use ($filteredMatrix) {
                $hasDuplicates = $filteredMatrix->where('part_number', $x->part_number)->count() > 1;
                $label = $x->part_number;
                if ($hasDuplicates && !empty($x->period_label) && $x->period_label !== '-') {
                    $label .= ' (' . $x->period_label . ')';
                }
                return [
                    'part_number'      => $x->part_number,
                    'chart_label'      => $label,
                    'description'      => $x->description,
                    'supplier_name'    => $x->supplier_name,
                    'factory_code'     => $x->factory_code,
                    'period_label'     => $x->period_label,
                    'last_stock_date'  => $x->last_stock_date,
                    'inventory_demand' => (int) $x->inventory_demand,
                    'actual_stock'     => (int) $x->actual_stock,
                    'outstanding_po'   => (int) $x->outstanding_po_qty,
                    'potential_supply' => (int) $x->potential_supply,
                    'net_supply_gap'   => (int) $x->net_supply_gap,
                    'status'           => $x->status,
                ];
            })
            ->values();

        // Ringkasan per Periode Bulan untuk Diagram Area Tren (Per Period Area Analysis)
        $periodSummaryData = $filteredMatrix
            ->filter(fn($x) => !empty($x->period_label) && $x->period_label !== '-')
            ->groupBy('period_label')
            ->map(function($rows, $label) {
                return [
                    'period_label'     => $label,
                    'period_key'       => $rows->first()->period_key ?? '',
                    'inventory_demand' => (int) $rows->sum('inventory_demand'),
                    'actual_stock'     => (int) $rows->sum('actual_stock'),
                    'outstanding_po'   => (int) $rows->sum('outstanding_po_qty'),
                    'potential_supply' => (int) $rows->sum('potential_supply'),
                    'net_supply_gap'   => (int) $rows->sum('net_supply_gap'),
                    'items_count'      => $rows->count(),
                ];
            })
            ->values();

        // ── 5B. STRUKTUR POHON HIERARKIS: VENDOR -> ITEM CODE -> 3D SUPPLY (AREA & BULLET CHART) ──
        // Single Validated Calculation Dataset pipeline:
        // Key: Vendor + Plant + Item Code + Period
        $vendorOverviewList = $filteredMatrix
            ->groupBy(fn($x) => $x->supplier_name ?: 'Tanpa Supplier')
            ->map(function($items, $supplierName) {
                $itemsGrouped = $items->groupBy('part_number')->map(function($rows, $partNo) {
                    $first = $rows->first();
                    $totActual = (int) $rows->sum('actual_stock');
                    $totDemand = (int) $rows->sum('inventory_demand');
                    $totPo     = (int) $rows->sum('outstanding_po_qty');
                    $totPot    = $totActual + $totPo;
                    $totGap    = $totDemand - $totActual; // Standard Inventory Gap: In Demand - Actual Inventory
                    $netSupplyGap = $totPot - $totDemand;
                    $additionalReq = max(0, $totDemand - $totPot);
                    $covPct    = $totDemand > 0 ? round(($totPot / $totDemand) * 100, 1) : 100.0;
                    
                    // Centralized Status Logic:
                    if ($totDemand > 0) {
                        if ($totActual >= $totDemand) {
                            $stdStatus = 'Healthy';
                            $statusBadge = 'badge bg-success bg-opacity-25 text-success border border-success border-opacity-50';
                            $issueReason = 'Stok fisik mencukupi target rencana kebutuhan produksi (Surplus +' . number_format(abs($totGap)) . ' PCS).';
                        } elseif ($totActual < $totDemand && $totPo > 0) {
                            $stdStatus = ($totPot >= $totDemand) ? 'Attention' : 'Critical';
                            $statusBadge = ($stdStatus === 'Attention') 
                                ? 'badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50'
                                : 'badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50';
                            $issueReason = ($stdStatus === 'Attention')
                                ? 'Stok fisik kurang (' . number_format($totActual) . ' PCS), namun aman tercover PO aktif (' . number_format($totPo) . ' PCS).'
                                : 'Defisit Pasokan: Stok fisik (' . number_format($totActual) . ' PCS) + PO (' . number_format($totPo) . ' PCS) belum cukup. Kurang ' . number_format($additionalReq) . ' PCS (Coverage ' . $covPct . '%).';
                        } elseif ($totPo > 0) {
                            $stdStatus = 'Attention';
                            $statusBadge = 'badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50';
                            $issueReason = 'Stok fisik 0, namun ada PO berjalan ' . number_format($totPo) . ' PCS.';
                        } else {
                            $stdStatus = 'Critical';
                            $statusBadge = 'badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50';
                            $issueReason = 'Defisit Kritis: Butuh ' . number_format($totDemand) . ' PCS, stok fisik ' . number_format($totActual) . ' PCS dan belum ada PO aktif (Kurang ' . number_format($additionalReq) . ' PCS).';
                        }
                    } else {
                        $stdStatus = ($totActual > 0) ? 'Healthy' : 'Check Data';
                        $statusBadge = ($totActual > 0) 
                            ? 'badge bg-success bg-opacity-25 text-success border border-success border-opacity-50'
                            : 'badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-50';
                        $issueReason = ($totActual > 0)
                            ? 'Stok fisik tersedia di gudang (' . number_format($totActual) . ' PCS) tanpa rencana kebutuhan forecast aktif.'
                            : 'Stok fisik 0 dan rencana kebutuhan 0 (No Demand).';
                    }

                    // Format sorted periods timeline
                    $sortedPeriods = $rows->sortBy('period_key')->map(fn($r) => [
                        'period_label'     => $r->period_label,
                        'period_key'       => $r->period_key,
                        'last_stock_date'  => $r->last_stock_date,
                        'in_demand'        => (int) $r->inventory_demand,
                        'actual_inventory' => (int) $r->actual_stock,
                        'outstanding'      => (int) $r->outstanding_po_qty,
                        'inventory_gap'    => (int) ($r->inventory_demand - $r->actual_stock),
                        'net_supply_gap'   => (int) $r->net_supply_gap,
                        'additional_req'   => (int) $r->additional_requirement,
                        'coverage_pct'     => (float) $r->coverage_pct,
                        'status'           => $stdStatus,
                    ])->values()->all();

                    return [
                        'part_number'      => $partNo,
                        'description'      => $first->description,
                        'factory_code'     => $first->factory_code,
                        'supplier_name'    => $first->supplier_name,
                        'supplier_code'    => $first->supplier_code,
                        'unit_price_usd'   => (float) $first->unit_price_usd,
                        'in_demand'        => $totDemand,
                        'actual_inventory' => $totActual,
                        'outstanding'      => $totPo,
                        'potential_supply' => $totPot,
                        'inventory_gap'    => $totGap,
                        'net_supply_gap'   => $netSupplyGap,
                        'additional_req'   => $additionalReq,
                        'coverage_pct'     => $covPct,
                        'status'           => $stdStatus,
                        'status_badge'     => $statusBadge,
                        'issue_reason'     => $issueReason,
                        'action_note'      => $first->action_note,
                        'val_usd'          => (float) $rows->sum('inventory_val_usd'),
                        'demand_val_usd'   => (float) $rows->sum('demand_val_usd'),
                        'periods'          => $sortedPeriods,
                    ];
                })->values();

                $vActual = (int) $items->sum('actual_stock');
                $vDemand = (int) $items->sum('inventory_demand');
                $vPo     = (int) $items->sum('outstanding_po_qty');
                $vGap    = $vDemand - $vActual;
                $vPot    = $vActual + $vPo;
                
                $criticalCount = $itemsGrouped->where('status', 'Critical')->count();
                $attentionCount = $itemsGrouped->where('status', 'Attention')->count();
                $healthyCount = $itemsGrouped->where('status', 'Healthy')->count();
                $checkDataCount = $itemsGrouped->where('status', 'Check Data')->count();
                $totalAddReq = (int) $itemsGrouped->sum('additional_req');
                $healthScore = $itemsGrouped->count() > 0 
                    ? round((($healthyCount + ($attentionCount * 0.5)) / $itemsGrouped->count()) * 100, 1) 
                    : 100.0;

                if ($vDemand > 0) {
                    if ($criticalCount > 0 || $vPot < $vDemand) {
                        $vStatus = 'Critical';
                        $statusReason = 'Terdapat ' . $criticalCount . ' item code berstatus defisit kritis (Total kekurangan pasokan: ' . number_format($totalAddReq) . ' PCS). Perlu PO pengadaan tambahan.';
                    } elseif ($attentionCount > 0) {
                        $vStatus = 'Attention';
                        $statusReason = 'Semua kebutuhan terpenuhi namun ' . $attentionCount . ' item code bergantung pada PO berjalan. Pantau ketepatan kirim.';
                    } else {
                        $vStatus = 'Healthy';
                        $statusReason = 'Stok fisik seluruh item code mencukupi rencana kebutuhan produksi secara mandiri (Surplus).';
                    }
                } else {
                    $vStatus = ($vActual > 0) ? 'Healthy' : 'Check Data';
                    $statusReason = ($vActual > 0) ? 'Stok fisik tersedia tanpa rencana kebutuhan forecast aktif.' : 'Tidak ada kebutuhan dan stok 0.';
                }

                return [
                    'supplier_name'           => $supplierName,
                    'supplier_code'           => $items->first()->supplier_code ?? '-',
                    'total_item_codes'        => $itemsGrouped->count(),
                    'critical_items_count'    => $criticalCount,
                    'attention_items_count'   => $attentionCount,
                    'healthy_items_count'     => $healthyCount,
                    'check_data_items_count'  => $checkDataCount,
                    'health_score_pct'        => $healthScore,
                    'total_additional_req'    => $totalAddReq,
                    'total_in_demand'         => $vDemand,
                    'total_actual_inventory'  => $vActual,
                    'total_outstanding'       => $vPo,
                    'total_potential_supply'  => $vPot,
                    'total_inventory_gap'     => $vGap,
                    'total_val_usd'           => (float) $items->sum('inventory_val_usd'),
                    'status'                  => $vStatus,
                    'status_reason'           => $statusReason,
                    'items'                   => $itemsGrouped->all(),
                ];
            })
            ->sortBy('supplier_name')
            ->values();

        $vendorTreeData = $vendorOverviewList; // Alias for backward compatibility

        // ── 5C. DATASET UNTUK DIAGRAM AREA VENDOR (CHART.JS) ──
        $vendorChartData = [
            'labels'             => $vendorOverviewList->pluck('supplier_name')->map(function($name) {
                // Shorten name if too long for clean chart presentation
                return strlen($name) > 24 ? substr($name, 0, 22) . '...' : $name;
            })->toArray(),
            'full_names'         => $vendorOverviewList->pluck('supplier_name')->toArray(),
            'supplier_codes'     => $vendorOverviewList->pluck('supplier_code')->toArray(),
            'in_demand'          => $vendorOverviewList->pluck('total_in_demand')->toArray(),
            'actual_inventory'   => $vendorOverviewList->pluck('total_actual_inventory')->toArray(),
            'outstanding_po'     => $vendorOverviewList->pluck('total_outstanding')->toArray(),
            'potential_supply'   => $vendorOverviewList->pluck('total_potential_supply')->toArray(),
            'statuses'           => $vendorOverviewList->pluck('status')->toArray(),
            'critical_counts'    => $vendorOverviewList->pluck('critical_items_count')->toArray(),
            'healthy_counts'     => $vendorOverviewList->pluck('healthy_items_count')->toArray(),
            'attention_counts'   => $vendorOverviewList->pluck('attention_items_count')->toArray(),
            'health_scores'      => $vendorOverviewList->pluck('health_score_pct')->toArray(),
        ];
        
        // Pre-Render Completeness & Reconciliation Verification:
        $dbVendorCount = $inventoryRecords->pluck('supplier_name')->unique()->filter()->count();
        $calcVendorCount = $vendorOverviewList->count();
        $reconciliationValidation = [
            'db_vendors'      => $dbVendorCount,
            'calc_vendors'    => $calcVendorCount,
            'is_consistent'   => ($calcVendorCount >= $dbVendorCount || $supplierFilter !== 'ALL'),
            'total_items'     => $filteredMatrix->count(),
            'distinct_items'  => $filteredMatrix->pluck('part_number')->unique()->count(),
        ];

        $topComparisonItems = $allChartItems->values();
        $chartLabels          = $topComparisonItems->pluck('chart_label')->toArray();
        $chartInventoryDemand = $topComparisonItems->pluck('inventory_demand')->toArray();
        $chartForecastStock   = $chartInventoryDemand; // alias for compatibility
        $chartActualInventory = $topComparisonItems->pluck('actual_stock')->toArray();
        $chartOutstandingPo   = $topComparisonItems->pluck('outstanding_po')->toArray();
        $chartPotentialSupply = $topComparisonItems->pluck('potential_supply')->toArray();

        $chartStatusDistribution = [
            'surplus'          => $kpiSurplusCount,
            'covered_by_po'    => $kpiCoveredByPoCount,
            'critical_deficit' => $kpiCriticalDeficitCount,
            'optimal'          => $kpiOptimalCount,
        ];

        // ── 6. LIST DROPDOWN OPTIONS UNTUK FILTER & MODAL INPUT ──
        $availablePeriods = collect()
            ->concat(PurchasingLog::distinct()->whereNotNull('period_month')->pluck('period_month'))
            ->concat(MasterPo::selectRaw("SUBSTRING(tanggal, 1, 7) as ym")->distinct()->pluck('ym'))
            ->concat(Inventory::selectRaw("SUBSTRING(tanggal_inventory, 1, 7) as ym")->distinct()->pluck('ym'))
            ->concat(Forecasting::distinct()->whereNotNull('period_month')->pluck('period_month'))
            ->filter(fn($p) => preg_match('/^\d{4}-\d{2}$/', (string)$p))
            ->unique()
            ->sortDesc()
            ->map(function($ym) {
                $carbon = Carbon::parse($ym . '-01');
                return (object)[
                    'key'      => $ym,
                    'label'    => $carbon->translatedFormat('F Y'),
                    'raw_date' => $ym . '-01',
                ];
            })
            ->values();

        $availablePlants    = collect(['KIP1', 'KIP2', 'KIP3', 'KIP4', 'Plant 3'])->merge($inventoryRecords->pluck('factory_code'))->unique()->filter()->sort()->values();
        $availableSuppliers = $allOutstandings->pluck('supplier_name')->merge($inventoryRecords->pluck('supplier_name'))
            ->map(fn($s) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($s))
            ->unique()->filter()->sort()->values();

        // Reactive Item Codes: Prioritaskan item code milik supplier yang sedang dipilih
        $normSupFilter = ($supplierFilter !== 'ALL') ? \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($supplierFilter) : '';
        $supplierItems = ($supplierFilter !== 'ALL') 
            ? $integratedMatrix->filter(fn($x) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($x->supplier_name) === $normSupFilter || strtoupper($x->supplier_code) === strtoupper($supplierFilter))->pluck('part_number')->unique()->sort()->values()
            : collect();

        $availableItemCodes = $supplierItems->isNotEmpty() 
            ? $supplierItems 
            : $allOutstandings->keys()->merge($inventoryRecords->pluck('part_number')->map(fn($v)=>strtoupper(trim($v))))->unique()->sort()->values();

        $itemsWithDetails = collect();
        foreach ($allOutstandings as $code => $os) {
            $itemsWithDetails->put($code, [
                'item_code'     => $code,
                'part_number'   => $code,
                'description'   => $os->description ?: '',
                'supplier_code' => $os->supplier_code ?? '',
                'supplier_name' => $os->supplier_name ?: '',
                'factory'       => $os->factory_code ?: 'KIP1',
                'unit_price'    => (float) $os->price,
            ]);
        }

        // ── 7. PAGINASI DATA TABEL UNTUK DATASET BESAR (600+ BARIS) ──
        $perPageParam = $request->get('per_page', '50');
        $perPage = ($perPageParam === 'ALL' || $perPageParam === 'all') ? max(1, $filteredMatrix->count()) : max(10, (int)$perPageParam);
        $currentPage = (int) $request->get('page', 1);
        $totalRowsCount = $filteredMatrix->count();

        if ($perPage > 0 && $totalRowsCount > $perPage) {
            $slice = $filteredMatrix->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $paginatedMatrix = new \Illuminate\Pagination\LengthAwarePaginator(
                $slice,
                $totalRowsCount,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $paginatedMatrix = new \Illuminate\Pagination\LengthAwarePaginator(
                $filteredMatrix,
                $totalRowsCount,
                max(1, $totalRowsCount),
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('purchasing.inventory', compact(
            'search',
            'itemCode',
            'plantFilter',
            'supplierFilter',
            'periodFilter',
            'statusFilter',
            'perPageParam',
            'filteredMatrix',
            'paginatedMatrix',
            'kpiTotalInventoryQty',
            'kpiTotalInventoryValUsd',
            'kpiTotalInventoryValIdr',
            'kpiTotalInventoryDemand',
            'kpiTotalForecastDemand',
            'kpiTotalDemandValUsd',
            'kpiTotalDemandValIdr',
            'kpiTotalOutstandingPo',
            'kpiTotalPotentialSupply',
            'kpiTotalPositions',
            'kpiUniqueMaterialsCount',
            'kpiTotalUniqueItems',
            'kpiNetSupplyGap',
            'kpiAdditionalRequirement',
            'kpiSurplusCount',
            'kpiCoveredByPoCount',
            'kpiCriticalDeficitCount',
            'kpiOptimalCount',
            'kpiCoveragePercentage',
            'latestSnapshotDate',
            'totalForecastMaterials',
            'matchedMaterialsCount',
            'matchPercentage',
            'dataQualityScorecard',
            'topComparisonItems',
            'allChartItems',
            'periodSummaryData',
            'vendorTreeData',
            'vendorOverviewList',
            'reconciliationValidation',
            'chartLabels',
            'chartInventoryDemand',
            'chartForecastStock',
            'chartActualInventory',
            'chartOutstandingPo',
            'chartPotentialSupply',
            'chartStatusDistribution',
            'availablePeriods',
            'availableItemCodes',
            'availablePlants',
            'availableSuppliers',
            'itemsWithDetails',
            'budgetExchangeRate',
            'vendorChartData'
        ));
    }

    /**
     * Download Standard Excel/CSV Template for Actual Inventory
     */
    public function downloadTemplate()
    {
        $filename = "Template_Actual_Inventory_" . date('Ymd') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Supplier Code', 'Supplier Name', 'Plant', 'Material Code', 'Description', 'Actual Inventory', 'Snapshot Date']);
            
            fputcsv($file, ['C102', 'PT. TRI JAYA TEKNIK KARAWANG', 'KIP1', '1312004', 'GP BRACKET COMPOU', 44, date('Y-m-d')]);
            fputcsv($file, ['C146', 'PT. SUMBER AGUNG SEJAHTERA ABADI', 'KIP1', '1311010', 'PLASTIC LDPE BAG', 90, date('Y-m-d')]);
            fputcsv($file, ['C084', 'PT. CRESTEC', 'KIP1', '817750', 'ZIPPER PLASTIC BAG', 1100, date('Y-m-d')]);
            fputcsv($file, ['C146', 'PT. SUMBER AGUNG SEJAHTERA ABADI', 'KIP4', '1311010', 'PLASTIC LDPE BAG', 50, date('Y-m-d')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Resolve a field from row with flexible key matching (stripping non-alphanumeric, case-insensitive)
     */
    private function resolveField(array $row, array $candidateKeys, $default = null)
    {
        $normalizedRow = [];
        foreach ($row as $k => $v) {
            $cleanK = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$k));
            $normalizedRow[$cleanK] = $v;
        }

        foreach ($candidateKeys as $cand) {
            $cleanCand = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$cand));
            if (isset($normalizedRow[$cleanCand]) && $normalizedRow[$cleanCand] !== '' && $normalizedRow[$cleanCand] !== null) {
                return $normalizedRow[$cleanCand];
            }
        }
        return $default;
    }

    /**
     * Clean and parse numeric inventory value supporting Indonesian/US formats, commas, dots, and unit suffixes.
     */
    private function parseStockNumeric($val): int
    {
        if ($val === null || $val === '') return 0;
        if (is_int($val)) return $val;

        $s = trim((string)$val);
        $s = preg_replace('/[a-zA-Z$]/', '', $s);
        $s = trim($s);

        if ($s === '' || $s === '-') return 0;

        // Indonesian thousands separator with dot e.g. "1.100" or "12.500" or "1.000.000"
        if (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $s)) {
            $s = str_replace('.', '', $s);
            return (int) $s;
        }

        // US thousands separator with comma e.g. "1,100" or "12,500"
        if (preg_match('/^-?\d{1,3}(,\d{3})+$/', $s)) {
            $s = str_replace(',', '', $s);
            return (int) $s;
        }

        // Mixed with decimal dot or comma e.g. "1,100.00"
        if (str_contains($s, ',') && str_contains($s, '.')) {
            $s = str_replace(',', '', $s);
        } elseif (str_contains($s, ',') && !str_contains($s, '.')) {
            $s = str_replace(',', '.', $s);
        }

        return is_numeric($s) ? (int) round((float)$s) : 0;
    }

    /**
     * Import Actual Inventory Data from Excel / JSON
     */
    public function importExcel(Request $request)
    {
        $rows = [];

        if ($request->has('rows') && is_array($request->input('rows'))) {
            $rows = $request->input('rows');
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->getRealPath();
            $data = array_map('str_getcsv', file($path));
            if (count($data) > 1) {
                $header = array_map(fn($h) => strtolower(trim((string)$h)), $data[0]);
                for ($i = 1; $i < count($data); $i++) {
                    if (empty(array_filter($data[$i]))) continue;
                    $rowAssoc = [];
                    foreach ($header as $colIdx => $colName) {
                        $rowAssoc[$colName] = $data[$i][$colIdx] ?? null;
                    }
                    $rows[] = $rowAssoc;
                }
            }
        }

        if (empty($rows)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data valid yang ditemukan untuk di-import.'], 422);
            }
            return redirect()->back()->with('error', 'Tidak ada data valid yang ditemukan untuk di-import.');
        }

        $importedCount = 0;
        $updatedCount = 0;
        $today = date('Y-m-d');
        $authId = auth()->id();

        $aggregatedRows = [];

        foreach ($rows as $r) {
            $rawSupplierCode = $this->resolveField($r, ['supplier_code', 'suppliercode', 'vendor_code', 'vendorcode', 'kode_supplier', 'kode_vendor']);
            $rawSupplierName = $this->resolveField($r, ['supplier_name', 'suppliername', 'supplier', 'vendor_name', 'vendorname', 'vendor', 'nama_supplier', 'nama_vendor']);
            $rawPlant        = $this->resolveField($r, ['plant', 'factory_code', 'factorycode', 'factory', 'lokasi', 'site', 'plant_code'], 'KIP1');
            $rawMaterialCode = $this->resolveField($r, ['material_code', 'materialcode', 'part_number', 'partnumber', 'part_no', 'partno', 'item_code', 'itemcode', 'kode_material', 'kode_barang', 'material', 'part']);
            $rawDescription  = $this->resolveField($r, ['description', 'deskripsi', 'nama_barang', 'item_description', 'material_name', 'keterangan']);
            $rawInventory    = $this->resolveField($r, ['actual_inventory', 'actualinventory', 'aktual_inventory', 'aktualinventory', 'actual_stock', 'actualstock', 'aktual_stok', 'aktualstok', 'current_stock', 'currentstock', 'stok_fisik', 'stokfisik', 'physical_stock', 'physicalstock', 'ending_stock', 'endingstock', 'saldo', 'saldo_akhir', 'qty', 'quantity', 'jumlah', 'inventory', 'stock', 'stok', 'm0_inventory', 'm0_stock', 'm0']);
            $rawSnapshotDate = $this->resolveField($r, ['snapshot_date', 'snapshotdate', 'tanggal_inventory', 'tanggalinventory', 'tanggal', 'date', 'periode', 'period', 'tgl'], $today);

            $matCodeStr = strtoupper(trim((string)$rawMaterialCode));
            if ($rawMaterialCode === null || $matCodeStr === '' || $matCodeStr === 'ITEM CODE' || $matCodeStr === 'MATERIAL CODE' || $matCodeStr === 'PART NUMBER' || str_starts_with($matCodeStr, 'TOTAL')) continue;

            $materialCode = trim((string)$rawMaterialCode);
            $plant = strtoupper(trim((string)$rawPlant ?: 'KIP1'));
            $supplierCode = $rawSupplierCode ? trim((string)$rawSupplierCode) : null;
            $supplierName = $rawSupplierName ? trim((string)$rawSupplierName) : null;
            $description = $rawDescription ? trim((string)$rawDescription) : null;
            
            $actualInventory = $this->parseStockNumeric($rawInventory);

            try {
                if (is_numeric($rawSnapshotDate) && (int)$rawSnapshotDate > 30000 && (int)$rawSnapshotDate < 60000) {
                    $snapshotDate = Carbon::create(1899, 12, 30)->addDays((int)$rawSnapshotDate)->format('Y-m-d');
                } else {
                    $snapshotDate = Carbon::parse($rawSnapshotDate)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $snapshotDate = $today;
            }

            $key = $plant . '|' . $materialCode . '|' . $snapshotDate;

            $aggregatedRows[$key] = [
                'supplier_code'     => $supplierCode,
                'supplier_name'     => $supplierName,
                'plant'             => $plant,
                'material_code'     => $materialCode,
                'description'       => $description,
                'actual_inventory'  => $actualInventory,
                'snapshot_date'     => $snapshotDate,
            ];
        }

        DB::beginTransaction();
        try {
            foreach ($aggregatedRows as $item) {
                $materialCode = $item['material_code'];
                $plant = $item['plant'];
                $snapshotDate = $item['snapshot_date'];
                $actualInventory = $item['actual_inventory'];
                $status = $actualInventory < 0 ? 'DEFICIT' : 'OPTIMAL';

                $desc = $item['description'];
                if (empty($desc)) {
                    $os = PurchasingOutstanding::where('part_number', $materialCode)->orWhere('drawing', $materialCode)->first();
                    $desc = $os?->description ?: 'Material Inventory Item';
                }

                $invRecord = Inventory::where('part_number', $materialCode)
                    ->where('factory_code', $plant)
                    ->where('tanggal_inventory', $snapshotDate)
                    ->first();

                if ($invRecord) {
                    $invRecord->update([
                        'supplier_code'     => $item['supplier_code'] ?: $invRecord->supplier_code,
                        'supplier_name'     => $item['supplier_name'] ?: $invRecord->supplier_name,
                        'description'       => $desc,
                        'current_stock'     => $actualInventory,
                        'm0_inventory'      => $actualInventory,
                        'status'            => $status,
                        'user_id'           => $authId,
                    ]);
                    $updatedCount++;
                } else {
                    Inventory::create([
                        'tanggal_inventory' => $snapshotDate,
                        'part_number'       => $materialCode,
                        'description'       => $desc,
                        'supplier_code'     => $item['supplier_code'],
                        'supplier_name'     => $item['supplier_name'],
                        'factory_code'      => $plant,
                        'current_stock'     => $actualInventory,
                        'm0_inventory'      => $actualInventory,
                        'unit_measure'      => 'PCS',
                        'status'            => $status,
                        'user_id'           => $authId,
                    ]);
                    $importedCount++;
                }

                PurchasingOutstanding::where('part_number', $materialCode)
                    ->orWhere('drawing', $materialCode)
                    ->update(['m0_inventory' => $actualInventory]);
            }

            DB::commit();

            $totalProcessed = $importedCount + $updatedCount;
            $msg = "Import Sukses! Berhasil memproses <strong>{$totalProcessed} data inventory</strong> ({$importedCount} baru, {$updatedCount} diperbarui).";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'imported' => $importedCount,
                    'updated' => $updatedCount,
                    'total' => $totalProcessed,
                ]);
            }

            return redirect()->route('purchasing.actual-inventory')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal mengimport data: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    /**
     * Store new single actual inventory log entry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_inventory' => 'required|date',
            'part_number'       => 'required|string',
            'factory_code'      => 'nullable|string',
            'supplier_code'     => 'nullable|string',
            'supplier_name'     => 'nullable|string',
            'description'       => 'nullable|string',
            'current_stock'     => 'required|numeric',
        ]);

        $itemCodeClean = strtoupper(trim($validated['part_number']));
        $plant = strtoupper(trim($validated['factory_code'] ?? 'KIP1'));
        $stock = (int) $validated['current_stock'];
        $status = $stock < 0 ? 'DEFICIT' : 'OPTIMAL';

        $description = trim($validated['description'] ?? '');
        $suppName = $validated['supplier_name'] ?? null;
        $suppCode = $validated['supplier_code'] ?? null;

        $os = PurchasingOutstanding::where('part_number', $itemCodeClean)
            ->orWhere('drawing', $itemCodeClean)
            ->first();

        if (empty($description)) {
            $description = $os?->description ?: 'Material Inventory Item';
        }
        if (empty($suppName) && $os) {
            $suppName = $os->supplier_name ?: null;
        }
        if (empty($suppCode) && $os) {
            $suppCode = $os->supplier_code ?: null;
        }
        if (empty($suppName)) {
            $mp = MasterPo::where('item_code', $itemCodeClean)->first();
            if ($mp) {
                $suppName = $mp->supplier ?: null;
                if (empty($description) || $description === 'Material Inventory Item') {
                    $description = $mp->name ?: 'Material Inventory Item';
                }
            }
        }

        Inventory::create([
            'tanggal_inventory' => $validated['tanggal_inventory'],
            'part_number'       => $itemCodeClean,
            'supplier_code'     => $suppCode,
            'supplier_name'     => $suppName,
            'factory_code'      => $plant,
            'description'       => $description,
            'current_stock'     => $stock,
            'm0_inventory'      => $stock,
            'unit_measure'      => 'PCS',
            'status'            => $status,
            'user_id'           => auth()->id(),
        ]);

        PurchasingOutstanding::where('part_number', $itemCodeClean)
            ->orWhere('drawing', $itemCodeClean)
            ->update(['m0_inventory' => $stock]);

        return redirect()->back()->with('success', "Data Aktual Inventory untuk <strong>{$itemCodeClean}</strong> ({$plant}) sebesar <strong>" . number_format($stock) . " PCS</strong> berhasil disimpan.");
    }

    /**
     * Update existing actual inventory log
     */
    public function update(Request $request, $id)
    {
        $log = Inventory::findOrFail($id);

        $validated = $request->validate([
            'tanggal_inventory' => 'required|date',
            'part_number'       => 'required|string',
            'factory_code'      => 'nullable|string',
            'supplier_code'     => 'nullable|string',
            'supplier_name'     => 'nullable|string',
            'description'       => 'nullable|string',
            'current_stock'     => 'required|numeric',
        ]);

        $itemCodeClean = strtoupper(trim($validated['part_number']));
        $plant = strtoupper(trim($validated['factory_code'] ?? $log->factory_code ?? 'KIP1'));
        $stock = (int) $validated['current_stock'];
        $status = $stock < 0 ? 'DEFICIT' : 'OPTIMAL';

        $log->update([
            'tanggal_inventory' => $validated['tanggal_inventory'],
            'part_number'       => $itemCodeClean,
            'supplier_code'     => $validated['supplier_code'] ?? $log->supplier_code,
            'supplier_name'     => $validated['supplier_name'] ?? $log->supplier_name,
            'factory_code'      => $plant,
            'description'       => $validated['description'] ?: $log->description,
            'current_stock'     => $stock,
            'm0_inventory'      => $stock,
            'status'            => $status,
        ]);

        PurchasingOutstanding::where('part_number', $itemCodeClean)
            ->orWhere('drawing', $itemCodeClean)
            ->update(['m0_inventory' => $stock]);

        return redirect()->back()->with('success', "Data Aktual Inventory #{$id} berhasil diperbarui.");
    }

    /**
     * Destroy single inventory record or reset stock
     */
    public function destroy(Request $request, $id)
    {
        $log = is_numeric($id) ? Inventory::find($id) : null;
        $itemCode = null;

        if ($log) {
            $itemCode = $log->part_number;
            $log->delete();
        } else {
            $itemCode = (string) $id;
            Inventory::where('part_number', $itemCode)->delete();
        }

        if ($itemCode) {
            PurchasingOutstanding::where('part_number', $itemCode)
                ->orWhere('drawing', $itemCode)
                ->update(['m0_inventory' => 0]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data inventory {$itemCode} berhasil dihapus."
            ]);
        }

        return redirect()->back()->with('success', "Data Aktual Inventory untuk <strong>{$itemCode}</strong> berhasil dihapus.");
    }

    /**
     * Bulk destroy selected inventory items (Delete Selection)
     */
    public function destroyBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $partNumbers = $request->input('part_numbers', []);

        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?: [];
        }
        if (is_string($partNumbers)) {
            $partNumbers = json_decode($partNumbers, true) ?: [];
        }

        $deletedCount = 0;

        DB::beginTransaction();
        try {
            if (!empty($ids)) {
                $partsToSync = Inventory::whereIn('id', $ids)->pluck('part_number')->unique()->toArray();
                $deletedCount = Inventory::whereIn('id', $ids)->delete();

                if (!empty($partsToSync)) {
                    PurchasingOutstanding::whereIn('part_number', $partsToSync)
                        ->orWhereIn('drawing', $partsToSync)
                        ->update(['m0_inventory' => 0]);
                }
            }

            if (!empty($partNumbers)) {
                $deletedParts = Inventory::whereIn('part_number', $partNumbers)->delete();
                $deletedCount = max($deletedCount, $deletedParts);

                PurchasingOutstanding::whereIn('part_number', $partNumbers)
                    ->orWhereIn('drawing', $partNumbers)
                    ->update(['m0_inventory' => 0]);
            }

            DB::commit();

            if ($deletedCount === 0 && empty($ids) && empty($partNumbers)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Pilih minimal satu data untuk dihapus.'], 422);
                }
                return redirect()->back()->with('error', 'Pilih minimal satu data untuk dihapus.');
            }

            $msg = "Berhasil menghapus {$deletedCount} data terpilih dari Aktual Inventory.";
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'deleted' => $deletedCount]);
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus data terpilih: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Mass destroy all inventory records (Delete Massal / Reset All)
     */
    public function destroyAll(Request $request)
    {
        DB::beginTransaction();
        try {
            $totalDeleted = Inventory::count();
            Inventory::query()->delete();

            // Reset all m0_inventory on purchasing outstandings
            PurchasingOutstanding::query()->update(['m0_inventory' => 0]);

            DB::commit();

            $msg = "Seluruh data Aktual Inventory ({$totalDeleted} records) berhasil dihapus massal dan direset ke 0.";

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $msg, 'deleted' => $totalDeleted]);
            }

            return redirect()->route('purchasing.actual-inventory')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal melakukan delete massal: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal melakukan delete massal: ' . $e->getMessage());
        }
    }
}
