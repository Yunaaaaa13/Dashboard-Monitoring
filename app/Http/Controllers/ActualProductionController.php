<?php

namespace App\Http\Controllers;

use App\Models\ActualProduction;
use App\Models\PurchasingOutstanding;
use App\Models\MasterPo;
use App\Models\Forecasting;
use App\Models\DeliveryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActualProductionController extends Controller
{
    /**
     * Tampilkan halaman utama input actual produksi (Step 5).
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $itemCode = $request->get('item_code');
        $selectedDeliveryCategory = $request->get('delivery_category');
        $selectedPlant = $request->get('plant');

        $query = ActualProduction::orderBy('tanggal_produksi', 'desc')->orderBy('id', 'desc');

        if ($selectedDeliveryCategory) {
            $query->where('delivery_category_code', $selectedDeliveryCategory);
        }

        if ($selectedPlant && $selectedPlant !== 'ALL') {
            $query->where('factory_code', $selectedPlant);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('supplier_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tanggal_produksi', 'like', "%{$search}%")
                  ->orWhere('import_batch_id', 'like', "%{$search}%");
            });
        }

        if ($itemCode && $itemCode !== 'ALL') {
            $query->where('item_code', $itemCode);
        }

        // Summary KPI Metrics
        $totalLogsCount = ActualProduction::count();
        $totalUniqueItemsCount = ActualProduction::distinct('item_code')->count('item_code');
        $totalProductionQty = (int) ActualProduction::sum('qty');
        $totalZeroProductionCount = ActualProduction::where('qty', 0)->count();
        $totalPlantsCount = ActualProduction::whereNotNull('factory_code')->distinct('factory_code')->count('factory_code');

        $latestLog = ActualProduction::latest('created_at')->first();
        $latestBatchId = $latestLog ? $latestLog->import_batch_id : null;
        $latestImportDate = $latestLog ? $latestLog->created_at->format('d/m/Y H:i') : null;

        $logs = $query->paginate(25);

        // Ambil daftar unik item code dari master PO, master forecast, dan outstanding PO drawing
        $poItemCodes = MasterPo::whereNotNull('item_code')
            ->where('item_code', '!=', '')
            ->pluck('item_code');

        $availableItemCodes = PurchasingOutstanding::whereNotNull('part_number')
            ->where('part_number', '!=', '')
            ->pluck('part_number')
            ->concat(PurchasingOutstanding::whereNotNull('drawing')->where('drawing', '!=', '')->pluck('drawing'))
            ->concat($poItemCodes)
            ->concat(Forecasting::pluck('part_number'))
            ->concat(ActualProduction::pluck('item_code'))
            ->filter()
            ->map(fn($v) => strtoupper(trim((string)$v)))
            ->unique()
            ->sort()
            ->values();

        $availablePlants = ActualProduction::whereNotNull('factory_code')
            ->pluck('factory_code')
            ->concat(['KIP 1', 'KIP 2', 'KIP 4'])
            ->map(fn($v) => strtoupper(trim((string)$v)))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $forecastStartMonth = session('monitor_start_month', 'JAN');
        $forecastStartYear  = (int) session('monitor_start_year', (int) date('Y'));

        $monthNums = [
            'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'JULY' => '07',
            'AUG' => '08', 'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12'
        ];
        $startMonthNum = $monthNums[strtoupper($forecastStartMonth)] ?? '01';
        $activeForecastPeriodMonth = sprintf('%04d-%s', $forecastStartYear, $startMonthNum);

        $step1Items = collect();

        foreach (PurchasingOutstanding::with('category:id,category_name')->select(['id', 'part_number', 'drawing', 'description', 'category_id', 'price', 'currency'])->get() as $os) {
            $code = strtoupper(trim((string)($os->part_number ?: $os->drawing)));
            if (!$code) continue;
            $periodM = $os->getPeriodForMonth(1);
            $step1Items->put($code, [
                'item_code'        => $code,
                'part_number'      => $os->part_number,
                'drawing'          => $os->drawing,
                'description'      => $os->description ?: 'Material Item',
                'category_name'    => $os->category ? $os->category->category_name : 'General',
                'price'            => (float) ($os->price ?? 0),
                'currency'         => strtoupper(trim($os->currency ?? 'USD')),
                'period_month'     => $periodM ?: $activeForecastPeriodMonth,
                'formatted_period' => date('M Y', strtotime(($periodM ?: $activeForecastPeriodMonth) . '-01')),
            ]);
        }

        foreach (MasterPo::select(['id', 'item_code', 'name', 'price', 'currency', 'tanggal'])->get() as $mp) {
            $code = strtoupper(trim((string)$mp->item_code));
            if (!$code) continue;
            $periodM = !empty($mp->tanggal) ? date('Y-m', strtotime($mp->tanggal)) : $activeForecastPeriodMonth;
            if (!$step1Items->has($code)) {
                $step1Items->put($code, [
                    'item_code'        => $code,
                    'part_number'      => $code,
                    'drawing'          => $code,
                    'description'      => $mp->name ?: 'Material Item',
                    'category_name'    => 'Master PO',
                    'price'            => (float) ($mp->price ?? 0),
                    'currency'         => strtoupper(trim($mp->currency ?? 'USD')),
                    'period_month'     => $periodM,
                    'formatted_period' => date('M Y', strtotime($periodM . '-01')),
                ]);
            }
        }

        foreach (Forecasting::select(['id', 'part_number', 'description', 'price', 'currency', 'periode'])->get() as $fc) {
            $code = strtoupper(trim((string)$fc->part_number));
            if (!$code) continue;
            $periodM = !empty($fc->periode) && strlen($fc->periode) === 7 ? $fc->periode : $activeForecastPeriodMonth;
            if (!$step1Items->has($code)) {
                $step1Items->put($code, [
                    'item_code'        => $code,
                    'part_number'      => $code,
                    'drawing'          => $code,
                    'description'      => $fc->description ?: 'Material Item',
                    'category_name'    => 'Master Forecast',
                    'price'            => (float) ($fc->price ?? 0),
                    'currency'         => strtoupper(trim($fc->currency ?? 'USD')),
                    'period_month'     => $periodM,
                    'formatted_period' => date('M Y', strtotime($periodM . '-01')),
                ]);
            }
        }

        $itemsWithForecastDetails = $step1Items->values();
        $deliveryCategories = DeliveryCategory::all();

        return view('purchasing.actual_production', compact(
            'logs',
            'search',
            'itemCode',
            'availableItemCodes',
            'availablePlants',
            'selectedDeliveryCategory',
            'selectedPlant',
            'deliveryCategories',
            'itemsWithForecastDetails',
            'totalLogsCount',
            'totalUniqueItemsCount',
            'totalProductionQty',
            'totalZeroProductionCount',
            'totalPlantsCount',
            'latestBatchId',
            'latestImportDate'
        ));
    }

    /**
     * Simpan input manual actual produksi.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'tanggal_produksi' => 'required|date',
                'item_code'        => 'required|string',
                'qty'              => 'required|integer|min:0',
            ]);

            $code = strtoupper(trim((string)$request->item_code));
            $qty  = (int)$request->qty;
            $plant = strtoupper(trim((string)$request->input('factory_code', 'KIP 1')));

            $desc     = trim((string)$request->input('description', ''));
            $suppName = trim((string)$request->input('supplier_name', ''));
            $suppCode = strtoupper(trim((string)$request->input('supplier_code', '')));

            if (empty($desc) || empty($suppName)) {
                $os = PurchasingOutstanding::where('part_number', $code)->orWhere('drawing', $code)->first();
                if ($os) {
                    if (empty($desc)) $desc = $os->description ?: 'Material Item';
                    if (empty($suppName)) $suppName = $os->supplier_name ?: '';
                    if (empty($suppCode) && !empty($os->supplier_code)) $suppCode = $os->supplier_code;
                }
                if (empty($desc)) {
                    $mp = MasterPo::where('item_code', $code)->first();
                    if ($mp) {
                        $desc = $mp->name ?: 'Material Item';
                        if (empty($suppName)) $suppName = $mp->supplier ?: '';
                    }
                }
                if (empty($desc)) {
                    $fc = Forecasting::where('part_number', $code)->first();
                    if ($fc) {
                        $desc = $fc->description ?: 'Material Item';
                    }
                }
            }

            $actual = ActualProduction::create([
                'tanggal_produksi'       => $request->tanggal_produksi,
                'item_code'              => $code,
                'supplier_code'          => $suppCode,
                'supplier_name'          => $suppName,
                'description'            => $desc ?: 'Material Item',
                'factory_code'           => $plant,
                'qty'                    => $qty,
                'currency'               => strtoupper(trim((string)$request->input('currency', 'USD'))),
                'delivery_category_code' => $request->input('delivery_category_code', 'LOC'),
                'user_id'                => Auth::id(),
            ]);

            $msg = "Data aktual produksi item <strong>{$code}</strong> sebanyak <strong>" . number_format($qty) . " unit</strong> berhasil disimpan ke sistem.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'data'    => $actual
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Perbarui data log actual produksi.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'tanggal_produksi' => 'required|date',
                'item_code'        => 'required|string',
                'qty'              => 'required|integer|min:0',
            ]);

            $code = strtoupper(trim((string)$request->item_code));
            $qty  = (int)$request->qty;

            $log = ActualProduction::findOrFail($id);
            $log->update([
                'tanggal_produksi'       => $request->tanggal_produksi,
                'item_code'              => $code,
                'supplier_code'          => strtoupper(trim((string)$request->input('supplier_code', $log->supplier_code ?? ''))),
                'supplier_name'          => trim((string)$request->input('supplier_name', $log->supplier_name ?? '')),
                'description'            => trim((string)$request->input('description', $log->description ?? '')),
                'factory_code'           => strtoupper(trim((string)$request->input('factory_code', $log->factory_code ?? 'KIP 1'))),
                'qty'                    => $qty,
                'currency'               => strtoupper(trim((string)$request->input('currency', $log->currency ?? 'USD'))),
                'delivery_category_code' => $request->input('delivery_category_code', $log->delivery_category_code ?? 'LOC'),
            ]);

            $msg = "Data aktual produksi item <strong>{$code}</strong> berhasil diperbarui menjadi <strong>" . number_format($qty) . " unit</strong>.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'data'    => $log
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus single data log actual produksi.
     */
    public function destroy($id, Request $request)
    {
        try {
            $log = ActualProduction::findOrFail($id);
            $log->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data aktual produksi berhasil dihapus.'
                ]);
            }

            return redirect()->back()->with('success', 'Data aktual produksi berhasil dihapus.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus beberapa data log actual produksi sekaligus (Delete Selection).
     */
    public function destroyBulk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data terpilih untuk dihapus.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Tidak ada data terpilih untuk dihapus.');
            }

            $count = ActualProduction::whereIn('id', $ids)->delete();

            $msg = "Sebanyak {$count} data log aktual produksi terpilih berhasil dihapus.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus semua data log actual produksi (Delete Massal).
     */
    public function destroyAll(Request $request)
    {
        try {
            $count = ActualProduction::count();
            ActualProduction::truncate();

            $msg = "Seluruh data log aktual produksi ({$count} baris) berhasil dikosongkan/dihapus.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unduh template CSV untuk import Actual Production.
     */
    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=template_aktual_produksi.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header template sesuai standar
            fputcsv($file, ['Supplier Code', 'Supplier Name', 'Plant', 'Material Code', 'Description', 'Produksi', 'Tanggal Produksi']);
            
            // Contoh baris (termasuk contoh Produksi = 0 yang valid)
            fputcsv($file, ['V001', 'PT KAWAI LOGISTIK', 'KIP 1', '1312006', 'MAIN BODY PIANO ASSEMBLY', '216', '2026-08-01']);
            fputcsv($file, ['V002', 'PT INDO SPRING',     'KIP 1', '1311024', 'PEDAL SPRING LEVER',        '10',  '2026-08-01']);
            fputcsv($file, ['V002', 'PT INDO SPRING',     'KIP 4', '1311023', 'SIDE SPRING BRACKET',       '0',   '2026-08-01']);
            fputcsv($file, ['V003', 'PT METAL INDONESIA', 'KIP 1', '1311025', 'CAST IRON FRAME PIN',       '105', '2026-08-01']);
            fputcsv($file, ['V003', 'PT METAL INDONESIA', 'KIP 4', '1311009', 'TUNING PIN BUSHING',        '20',  '2026-08-01']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Unggah / Import data aktual produksi dari Excel/CSV atau Payload JSON SheetJS.
     * Menerapkan prinsip: 1 baris Excel valid = 1 log produksi (Zero production Qty = 0 TETAP VALID).
     */
    public function import(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        try {
            $parsedRows = [];

            // 1. Jika dikirim dari client-side preview (SheetJS AJAX JSON)
            if ($request->isJson() || $request->has('rows')) {
                $rawRows = $request->input('rows', []);
                if (is_string($rawRows)) {
                    $rawRows = json_decode($rawRows, true) ?: [];
                }
                $parsedRows = $this->parseClientPayloadRows($rawRows);
            } 
            // 2. Jika diupload via file Excel/CSV konvensional
            else {
                $file = $request->file('csv_file') ?: $request->file('file');
                if (!$file) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Silakan pilih berkas Excel atau CSV untuk diunggah.'], 400);
                    }
                    return redirect()->back()->with('error', 'Silakan pilih berkas Excel atau CSV untuk diunggah.');
                }
                $parsedRows = $this->parseUploadedFileRows($file);
            }

            if (empty($parsedRows)) {
                $msg = 'Tidak ada baris data produksi yang valid untuk diimport dari file.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            // Generate unique import batch identifier
            $importBatchId = 'BATCH-' . date('Ymd-His') . '-' . rand(100, 999);
            $currentUserId = Auth::id();
            $nowTimestamp  = now();

            $toInsert = [];
            $zeroCount = 0;
            $uniqueMaterials = [];

            foreach ($parsedRows as $row) {
                $qty = (int)$row['qty'];
                if ($qty === 0) {
                    $zeroCount++;
                }
                $itemCode = strtoupper(trim((string)$row['item_code']));
                $uniqueMaterials[$itemCode] = true;

                $toInsert[] = [
                    'tanggal_produksi'       => $row['tanggal_produksi'],
                    'item_code'              => $itemCode,
                    'supplier_code'          => $row['supplier_code'] ?? null,
                    'supplier_name'          => $row['supplier_name'] ?? null,
                    'description'            => $row['description'] ?? null,
                    'factory_code'           => $row['factory_code'] ?? 'KIP 1',
                    'qty'                    => $qty,
                    'currency'               => $row['currency'] ?? 'USD',
                    'delivery_category_code' => $row['delivery_category_code'] ?? 'LOC',
                    'import_batch_id'        => $importBatchId,
                    'excel_row_number'       => $row['excel_row_number'] ?? null,
                    'user_id'                => $currentUserId,
                    'created_at'             => $nowTimestamp,
                    'updated_at'             => $nowTimestamp,
                ];
            }

            // Transactional commit: seluruh 170 baris dijamin masuk utuh tanpa lost rows
            DB::beginTransaction();
            foreach (array_chunk($toInsert, 500) as $chunk) {
                ActualProduction::insert($chunk);
            }
            DB::commit();

            $totalInserted = count($toInsert);
            $uniqueCount   = count($uniqueMaterials);

            $msg = "✓ Berhasil mengimpor <strong>{$totalInserted} baris log produksi</strong> (termasuk <strong>{$zeroCount} baris produksi 0</strong>) mencakup <strong>{$uniqueCount} material unik</strong> [Batch ID: {$importBatchId}].";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'        => true,
                    'message'        => $msg,
                    'total_inserted' => $totalInserted,
                    'zero_count'     => $zeroCount,
                    'unique_count'   => $uniqueCount,
                    'batch_id'       => $importBatchId
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            $err = 'Gagal mengimpor file Excel/CSV: ' . $e->getMessage();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return redirect()->back()->with('error', $err);
        }
    }

    /**
     * Parser baris dari payload JSON Client-Side (SheetJS).
     */
    private function parseClientPayloadRows(array $rawRows): array
    {
        $parsed = [];
        foreach ($rawRows as $index => $row) {
            $item = $this->resolveField($row, ['material_code', 'item_code', 'part_number', 'part_no', 'drawing', 'material', 'kode_barang', 'kode_material', 'kode_item', 'kode_part', 'komponen', 'pn', 'sku', 'code', 'barang', 'item', 'part', 'mat_no', 'mat_code', 'drawing_no']);
            $itemStr = trim((string)$item);
            if ($itemStr === '' || strtoupper($itemStr) === 'ITEM CODE' || strtoupper($itemStr) === 'MATERIAL CODE' || strtoupper($itemStr) === 'TOTAL') {
                continue;
            }

            $qtyRaw = $this->resolveField($row, ['production_qty', 'produksi', 'qty', 'quantity', 'actual_production', 'actual', 'aktual', 'jumlah', 'kuantitas', 'realisasi', 'vol', 'volume', 'total', 'output', 'pcs', 'juli', 'jul']);
            $parsedQty = $this->parseStockNumeric($qtyRaw);

            // Validasi: Qty harus numeric (0 adalah VALID). Jika bernilai null / tidak bisa diparse, skip atau tandai error.
            if ($parsedQty === null) {
                continue;
            }

            $dateRaw = $this->resolveField($row, ['tanggal_produksi', 'tanggal', 'date', 'tgl', 'periode', 'prod_date']);
            $date = $this->parseExcelDate($dateRaw);

            $plant = $this->resolveField($row, ['plant', 'factory_code', 'pabrik', 'lokasi', 'factory', 'kode_pabrik', 'site', 'gedung', 'line', 'unit']) ?: 'KIP 1';
            $suppCode = $this->resolveField($row, ['supplier_code', 'vendor_code', 'kode_supplier', 'kode_vendor', 'kd_supp', 'kd_vendor', 'supp_code', 'kd_sp']) ?: '';
            $suppName = $this->resolveField($row, ['supplier_name', 'vendor_name', 'nama_supplier', 'nama_vendor', 'supplier', 'vendor', 'pemasok', 'nama_pemasok']) ?: '';
            $desc = $this->resolveField($row, ['description', 'deskripsi', 'nama_barang', 'nama_material', 'item_name', 'material_name', 'part_name', 'nama_part', 'keterangan', 'spec', 'spesifikasi', 'desc']) ?: '';

            $parsed[] = [
                'excel_row_number'       => (int)($row['excel_row_number'] ?? ($index + 2)),
                'tanggal_produksi'       => $date,
                'item_code'              => strtoupper($itemStr),
                'supplier_code'          => strtoupper(trim((string)$suppCode)),
                'supplier_name'          => trim((string)$suppName),
                'description'            => trim((string)$desc),
                'factory_code'           => strtoupper(trim((string)$plant)),
                'qty'                    => $parsedQty,
                'currency'               => strtoupper(trim((string)($row['currency'] ?? 'USD'))),
                'delivery_category_code' => strtoupper(trim((string)($row['delivery_category_code'] ?? 'LOC'))),
            ];
        }
        return $parsed;
    }

    /**
     * Parser baris dari file fisik (.xlsx, .xls, .csv).
     */
    private function parseUploadedFileRows($file): array
    {
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
        $fileRows = $sheet->toArray(null, false, true, true);

        if (empty($fileRows)) {
            return [];
        }

        // Cari baris header
        $headerIdx = null;
        foreach ($fileRows as $idx => $r) {
            foreach ($r as $col => $val) {
                $cleanVal = strtoupper(trim((string)$val));
                if (str_contains($cleanVal, 'MATERIAL') || str_contains($cleanVal, 'ITEM CODE') || str_contains($cleanVal, 'PRODUKSI') || str_contains($cleanVal, 'QTY') || str_contains($cleanVal, 'PART NUMBER')) {
                    $headerIdx = $idx;
                    break 2;
                }
            }
        }

        $headerMap = [];
        if ($headerIdx) {
            foreach ($fileRows[$headerIdx] as $col => $val) {
                $headerMap[$col] = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$val));
            }
            $dataRows = array_filter($fileRows, fn($key) => $key > $headerIdx, ARRAY_FILTER_USE_KEY);
        } else {
            $dataRows = $fileRows;
        }

        $parsed = [];
        foreach ($dataRows as $rowIdx => $r) {
            // Build associative row
            $assoc = [];
            foreach ($r as $col => $val) {
                $key = $headerMap[$col] ?? $col;
                $assoc[$key] = $val;
            }

            $item = $this->resolveField($assoc, ['materialcode', 'itemcode', 'partnumber', 'partno', 'drawing', 'material', 'kodebarang', 'kodematerial', 'pn', 'sku', 'b']);
            $itemStr = trim((string)$item);
            if ($itemStr === '' || strtoupper($itemStr) === 'ITEM CODE' || strtoupper($itemStr) === 'MATERIAL CODE') {
                continue;
            }

            $qtyRaw = $this->resolveField($assoc, ['productionqty', 'produksi', 'qty', 'actualproduction', 'jumlah', 'kuantitas', 'realisasi', 'vol', 'f', 'c']);
            $parsedQty = $this->parseStockNumeric($qtyRaw);

            // Validasi: Qty harus bernilai numeric (0 tetap VALID)
            if ($parsedQty === null) {
                continue;
            }

            $dateRaw = $this->resolveField($assoc, ['tanggalproduksi', 'tanggal', 'date', 'tgl', 'periode', 'proddate', 'g', 'a']);
            $date = $this->parseExcelDate($dateRaw);

            $plant = $this->resolveField($assoc, ['plant', 'factorycode', 'pabrik', 'lokasi', 'factory', 'kodepabrik', 'c']) ?: 'KIP 1';
            $suppCode = $this->resolveField($assoc, ['suppliercode', 'vendorcode', 'kodesupplier', 'supplier', 'vendor', 'kdsupp', 'kodevendor', 'a']) ?: '';
            $suppName = $this->resolveField($assoc, ['suppliername', 'vendorname', 'namasupplier', 'namavendor', 'b']) ?: '';
            $desc = $this->resolveField($assoc, ['description', 'deskripsi', 'namabarang', 'itemname', 'namamaterial', 'keterangan', 'e', 'd']) ?: '';

            $parsed[] = [
                'excel_row_number'       => (int)$rowIdx,
                'tanggal_produksi'       => $date,
                'item_code'              => strtoupper($itemStr),
                'supplier_code'          => strtoupper(trim((string)$suppCode)),
                'supplier_name'          => trim((string)$suppName),
                'description'            => trim((string)$desc),
                'factory_code'           => strtoupper(trim((string)$plant)),
                'qty'                    => $parsedQty,
                'currency'               => 'USD',
                'delivery_category_code' => 'LOC',
            ];
        }

        return $parsed;
    }

    /**
     * Resolves field flexibly from associative array with alias keys.
     */
    private function resolveField(array $data, array $candidates)
    {
        $normalizedData = [];
        foreach ($data as $k => $v) {
            $normalizedKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$k));
            $normalizedData[$normalizedKey] = $v;
        }

        foreach ($candidates as $candidate) {
            $cleanCand = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $candidate));
            if (array_key_exists($cleanCand, $normalizedData) && $normalizedData[$cleanCand] !== null && $normalizedData[$cleanCand] !== '') {
                return $normalizedData[$cleanCand];
            }
        }
        return null;
    }

    /**
     * Parse numeric qty safely handling thousands separators and 0 values.
     * Mengembalikan integer (termasuk 0) atau null jika bukan angka.
     */
    private function parseStockNumeric($val): ?int
    {
        if ($val === null || $val === '') {
            return null;
        }

        if (is_int($val) || is_float($val)) {
            return (int) round($val);
        }

        $str = trim((string)$val);
        // Hapus unit/teks non-numerik di akhir seperti " PCS", " UNIT", dll.
        $str = preg_replace('/[^\d\.\,\-]/', '', $str);

        if ($str === '' || $str === '-') {
            return null;
        }

        // Format Indonesia ribuan dengan titik: 1.100 atau 10.000
        if (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $str)) {
            $str = str_replace('.', '', $str);
        }
        // Format US ribuan dengan koma: 1,100 atau 10,000
        elseif (preg_match('/^-?\d{1,3}(,\d{3})+$/', $str)) {
            $str = str_replace(',', '', $str);
        }
        // Format desimal dengan koma: 10,50
        elseif (str_contains($str, ',') && !str_contains($str, '.')) {
            $str = str_replace(',', '.', $str);
        }

        if (is_numeric($str)) {
            return (int) round((float)$str);
        }

        return null;
    }

    /**
     * Helper privat untuk mengonversi berbagai format tanggal dari Excel/CSV menjadi format YYYY-MM-DD yang valid.
     */
    private function parseExcelDate($val): string
    {
        if (empty($val)) {
            return date('Y-m-d');
        }

        $val = trim((string)$val);

        // Strip time if present (e.g. "10/01/2026 00:00:00" -> "10/01/2026")
        if (preg_match('/^(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{4})/', $val, $mTime)) {
            $val = $mTime[1];
        } elseif (preg_match('/^(\d{4}[\/\.\-]\d{1,2}[\/\.\-]\d{1,2})/', $val, $mTime)) {
            $val = $mTime[1];
        }

        if (is_numeric($val)) {
            $num = (float)$val;
            if ($num > 1000) {
                $unixDate = ($num - 25569) * 86400;
                return date('Y-m-d', (int)$unixDate);
            }
        }

        if (preg_match('/^(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{4})$/', $val, $matches)) {
            $first = (int)$matches[1];
            $second = (int)$matches[2];
            $year = (int)$matches[3];

            if ($second > 12 && checkdate($first, $second, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $first, $second);
            }
            if ($first > 12 && checkdate($second, $first, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $second, $first);
            }

            if (checkdate($second, $first, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $second, $first);
            }
            if (checkdate($first, $second, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $first, $second);
            }
        }

        if (preg_match('/^(\d{4})[\/\.\-](\d{1,2})[\/\.\-](\d{1,2})$/', $val, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        if (preg_match('/^(\d{4})[\/\.\-](\d{1,2})$/', $val, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            if ($month >= 1 && $month <= 12) {
                return sprintf('%04d-%02d-01', $year, $month);
            }
        }

        $cleanDate = str_replace(['/', '.'], '-', $val);
        $ts = strtotime($cleanDate);
        if ($ts !== false && $ts > 0) {
            return date('Y-m-d', $ts);
        }

        return date('Y-m-d');
    }
}

