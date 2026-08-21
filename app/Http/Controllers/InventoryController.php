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
        $itemCode       = $request->get('item_code');
        $plantFilter    = $request->get('plant', 'ALL');
        $supplierFilter = $request->get('supplier', 'ALL');
        $statusFilter   = $request->get('status_filter', 'ALL');

        // ── 1. PRE-FETCH DATA INTEGRASI (PO & INCOMING LOGS) ──
        $poSummaries = MasterPo::select('item_code', 'currency', DB::raw('SUM(qty) as total_po_qty'), DB::raw('AVG(price) as avg_po_price'))
            ->whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->groupBy('item_code', 'currency')
            ->get()
            ->keyBy(fn($item) => strtoupper(trim((string)$item->item_code)));

        $receiptSummaries = PurchasingLog::select('item_code', 'currency', DB::raw('SUM(actual_received) as total_receipt_qty'), DB::raw('AVG(price) as avg_actual_price'))
            ->whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->groupBy('item_code', 'currency')
            ->get()
            ->keyBy(fn($item) => strtoupper(trim((string)$item->item_code)));

        // Kurs Budget terkini
        $latestBudgetRateRecord = TaxBudgetForecastRate::orderBy('exch_year', 'desc')->orderBy('exch_month', 'desc')->first();
        $budgetExchangeRate = $latestBudgetRateRecord ? (int) $latestBudgetRateRecord->budget_rate : 16600;

        // ── 2. SUMBER UTAMA TABEL: DATA DARI TABEL INVENTORIES (LOGS FISIK TERUNGGAH) ──
        $inventoryRecords = Inventory::orderBy('tanggal_inventory', 'desc')->orderBy('id', 'desc')->get();
        $integratedMatrix = collect();

        // Master outstandings untuk referensi harga & fallback deskripsi
        $allOutstandings = PurchasingOutstanding::all()->keyBy(fn($x) => strtoupper(trim((string)($x->part_number ?: $x->drawing))));
        $allForecasting = Forecasting::all()->groupBy(fn($x) => strtoupper(trim((string)$x->part_number)));

        $latestSnapshotDateRaw = $inventoryRecords->max('tanggal_inventory');
        $latestSnapshotDate = $latestSnapshotDateRaw ? Carbon::parse($latestSnapshotDateRaw)->format('d M Y') : date('d M Y');
        $snapshotCanonicalPeriod = $latestSnapshotDateRaw ? date('Y-m', strtotime($latestSnapshotDateRaw)) : date('Y-m');

        foreach ($inventoryRecords as $inv) {
            $code = strtoupper(trim((string)$inv->part_number));
            if (!$code) continue;

            $os = $allOutstandings->get($code);
            $desc = $inv->description ?: ($os?->description ?: 'Material Item');
            $supplier = $inv->supplier_name ?: ($os?->supplier_name ?: '-');
            $supplierCode = $inv->supplier_code ?: ($os?->supplier_code ?? '-');
            $factory = $inv->factory_code ?: ($os?->factory_code ?: 'KIP1');
            $actualStock = (int) $inv->current_stock;
            $lastStockDate = $inv->tanggal_inventory ? Carbon::parse($inv->tanggal_inventory)->format('d/m/Y') : '-';

            // 1. Inventory Demand untuk item ini (Target Kebutuhan dari Forecast)
            $inventoryDemand = 0;
            $fcRecords = $allForecasting->get($code);
            if ($fcRecords && $fcRecords->isNotEmpty()) {
                // Cari forecast periode yang sesuai dengan snapshot jika ada
                $fcMatchingPeriod = $fcRecords->firstWhere('period_month', $snapshotCanonicalPeriod) ?: $fcRecords->firstWhere('periode', $snapshotCanonicalPeriod);
                $fcTarget = $fcMatchingPeriod ?: $fcRecords->first();
                $inventoryDemand = (int) ($fcTarget->forecast_qty ?: ($fcTarget->po_qty ?: $fcTarget->production_qty));
            }
            if ($inventoryDemand === 0 && $os) {
                $inventoryDemand = (int) ($os->m1_po ?: ($os->order_qty ?: ($os->m0_po ?: ($os->m1_prod ?: 0))));
            }

            $isMatched = ($os !== null || ($fcRecords && $fcRecords->isNotEmpty()));

            // 2. PO & Receipt & Outstanding
            $poData = $poSummaries->get($code);
            $rcptData = $receiptSummaries->get($code);

            $poQty = $poData ? (int) $poData->total_po_qty : 0;
            $rcptQty = $rcptData ? (int) $rcptData->total_receipt_qty : 0;
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
            $filteredMatrix = $filteredMatrix->filter(fn($x) => strtoupper($x->supplier_code) === strtoupper($supplierFilter) || strtoupper($x->supplier_name) === strtoupper($supplierFilter));
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

        // ── 5. CHART DATASETS: TOP 10 EXECUTIVE COMPARISON (Inventory Demand vs Inv vs PO vs Potential Supply) ──
        $topComparisonItems = $filteredMatrix
            ->filter(fn($x) => ($x->inventory_demand > 0 || $x->actual_stock > 0 || $x->outstanding_po_qty > 0))
            ->sortByDesc(fn($x) => max($x->inventory_demand, $x->potential_supply, $x->actual_stock))
            ->take(10)
            ->values();

        $chartLabels          = $topComparisonItems->pluck('part_number')->toArray();
        $chartInventoryDemand = $topComparisonItems->pluck('inventory_demand')->toArray();
        $chartForecastStock   = $chartInventoryDemand; // alias for compatibility
        $chartActualInventory = $topComparisonItems->pluck('actual_stock')->toArray();
        $chartOutstandingPo   = $topComparisonItems->pluck('outstanding_po_qty')->toArray();
        $chartPotentialSupply = $topComparisonItems->pluck('potential_supply')->toArray();

        $chartStatusDistribution = [
            'surplus'          => $kpiSurplusCount,
            'covered_by_po'    => $kpiCoveredByPoCount,
            'critical_deficit' => $kpiCriticalDeficitCount,
            'optimal'          => $kpiOptimalCount,
        ];

        // ── 6. LIST DROPDOWN OPTIONS UNTUK FILTER & MODAL INPUT ──
        $availableItemCodes = $allOutstandings->keys()->merge($inventoryRecords->pluck('part_number')->map(fn($v)=>strtoupper(trim($v))))->unique()->sort()->values();
        $availablePlants    = collect(['KIP1', 'KIP2', 'KIP3', 'KIP4', 'Plant 3'])->merge($inventoryRecords->pluck('factory_code'))->unique()->filter()->sort()->values();
        $availableSuppliers = $allOutstandings->pluck('supplier_name')->merge($inventoryRecords->pluck('supplier_name'))->unique()->filter()->sort()->values();

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

        return view('purchasing.inventory', compact(
            'search',
            'itemCode',
            'plantFilter',
            'supplierFilter',
            'statusFilter',
            'filteredMatrix',
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
            'chartLabels',
            'chartInventoryDemand',
            'chartForecastStock',
            'chartActualInventory',
            'chartOutstandingPo',
            'chartPotentialSupply',
            'chartStatusDistribution',
            'availableItemCodes',
            'availablePlants',
            'availableSuppliers',
            'itemsWithDetails',
            'budgetExchangeRate'
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

            if ($rawMaterialCode === null || trim((string)$rawMaterialCode) === '') continue;

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
        if (empty($description)) {
            $os = PurchasingOutstanding::where('part_number', $itemCodeClean)
                ->orWhere('drawing', $itemCodeClean)
                ->first();
            $description = $os?->description ?: 'Material Inventory Item';
        }

        Inventory::create([
            'tanggal_inventory' => $validated['tanggal_inventory'],
            'part_number'       => $itemCodeClean,
            'supplier_code'     => $validated['supplier_code'] ?? null,
            'supplier_name'     => $validated['supplier_name'] ?? null,
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
