<?php

namespace App\Http\Controllers;

use App\Models\Forecasting;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    /**
     * Tampilkan halaman Master Forecast.
     */
    public function masterIndex(Request $request)
    {
        $periode = $this->normalizePeriodString($request->get('periode', now()->format('Y-m')));
        $selectedDeliveryCategory = $request->get('delivery_category');
        $targetUserId = $request->get('user_id');
        $user = \Illuminate\Support\Facades\Auth::user();

        $forecasts = \App\Models\Forecasting::when($periode, function ($q) use ($periode) {
            $variants = $this->getPeriodVariantsString($periode);
            $q->where(function ($q2) use ($variants) {
                $q2->whereIn('periode', $variants)->orWhereIn('period_month', $variants);
            });
        })->when($selectedDeliveryCategory, function ($q) use ($selectedDeliveryCategory) {
            $q->where('delivery_category_code', $selectedDeliveryCategory);
        })->when(!$user?->isAdmin() && !$targetUserId, function ($q) use ($user) {
            $q->where('user_id', $user?->id);
        })->when($targetUserId, function ($q) use ($targetUserId) {
            $q->where('user_id', $targetUserId);
        })->orderBy('part_number')->get();

        $availablePeriodes = \App\Models\Forecasting::whereNotNull('periode')
            ->pluck('periode')
            ->merge(\App\Models\Forecasting::whereNotNull('period_month')->pluck('period_month'))
            ->filter()
            ->map(fn ($p) => $this->normalizePeriodString((string) $p))
            ->unique()->sortDesc()->values();

        $deliveryCategories = \App\Models\DeliveryCategory::all();

        return view('purchasing.master_forecast', compact('forecasts', 'periode', 'availablePeriodes', 'selectedDeliveryCategory', 'deliveryCategories'));
    }

    /**
     * Simpan / Update Master Forecast (Part Number + Periode + Forecast Qty).
     */
    public function store(Request $request)
    {
        try {
            $partNumber  = strtoupper(trim($request->input('part_number', '')));
            $periode     = trim($request->input('periode', $request->input('period_month', '')));
            $description = trim($request->input('description', ''));
            $outstandingPre = (int) $request->input('outstanding_pre', 0);
            $stockPre       = (int) $request->input('stock_pre', 0);
            $production     = (int) $request->input('production', $request->input('production_qty', 0));
            $forecastQty    = (int) $request->input('forecast_qty', 0);
            $rawPrice       = $request->input('price');
            $price          = ($rawPrice !== null && $rawPrice !== '') ? (float) str_replace(',', '.', (string) $rawPrice) : 0.0;

            if (empty($partNumber) || empty($periode)) {
                throw new \Exception('Part Number dan Periode wajib diisi dengan format yang benar.');
            }

            if ($price <= 0) {
                $existingPo = \App\Models\PurchasingOutstanding::where('part_number', $partNumber)
                    ->orWhere('drawing', $partNumber)
                    ->where('price', '>', 0)
                    ->first();
                if ($existingPo) {
                    $price = (float) $existingPo->price;
                }
            }

            // Hitung PO & Delivery secara dinamis
            $poSum = (int) \App\Models\MasterPo::where('item_code', $partNumber)->orWhere('po', $partNumber)->sum('qty');
            $logSum = (int) \App\Models\PurchasingLog::where('item_code', $partNumber)->orWhere('po_reference', $partNumber)->sum('actual_received');
            $delSum = $logSum;
            if ($delSum <= 0) {
                $delSum = (int) \App\Models\ForecastActual::where('part_number', $partNumber)->where('periode', $periode)->sum('forecast_actual');
            }
            if ($delSum <= 0) {
                $delSum = (int) \App\Models\Actual::where('part_number', $partNumber)->where(function ($q) use ($periode) {
                    $q->where('periode', $periode)->orWhere('period_month', $periode);
                })->sum('actual_qty');
            }
            if ($delSum <= 0 && $poSum > 0) {
                $delSum = $poSum;
            }

            // Rumus Excel
            // Forecast = PO - Outstanding (pre month)
            $calcForecast = max(0, $poSum - $outstandingPre);
            // Outstanding = Outstanding (pre month) + PO - Delivery
            $calcOutstanding = $outstandingPre + $poSum - $delSum;
            // Stock = Stock (pre month) + Delivery - PROD
            $calcStock = $stockPre + $delSum - $production;

            $forecast = Forecasting::updateOrCreate(
                [
                    'part_number' => $partNumber,
                    'periode'     => $periode,
                ],
                [
                    'user_id'         => \Illuminate\Support\Facades\Auth::id(),
                    'description'     => !empty($description) ? $description : '-',
                    'price'           => $price,
                    'outstanding_pre' => $outstandingPre,
                    'stock_pre'       => $stockPre,
                    'po'              => $poSum,
                    'po_qty'          => $poSum,
                    'delivery'        => $delSum,
                    'forecast_qty'    => $forecastQty > 0 ? $forecastQty : max(0, $calcForecast),
                    'outstanding'     => $calcOutstanding,
                    'production'      => $production,
                    'production_qty'  => $production,
                    'stock'           => $calcStock,
                    'stock_qty'       => $calcStock,
                    'period_month'    => $periode,
                    'delivery_category_code' => $request->input('delivery_category_code', 'LOC'),
                ]
            );

            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Forecast berhasil disimpan dengan kalkulasi otomatis',
                    'data'    => $forecast,
                    'period'  => $periode,
                    'periode' => $periode,
                ], 200);
            }

            return redirect()->back()->with('success', 'Data Forecast berhasil disimpan dengan kalkulasi otomatis');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update data Forecast berdasarkan ID.
     */
    public function update(Request $request, $id)
    {
        try {
            $forecast = Forecasting::findOrFail($id);

            $description    = trim($request->input('description', $forecast->description ?? ''));
            $outstandingPre = (int) $request->input('outstanding_pre', $forecast->outstanding_pre ?? 0);
            $stockPre       = (int) $request->input('stock_pre', $forecast->stock_pre ?? 0);
            $production     = (int) $request->input('production', $request->input('production_qty', $forecast->production_qty ?? 0));
            $forecastQty    = (int) $request->input('forecast_qty', $forecast->forecast_qty ?? 0);
            $rawPrice       = $request->input('price');
            $price          = ($rawPrice !== null && $rawPrice !== '') ? (float) str_replace(',', '.', (string) $rawPrice) : (float) ($forecast->price ?? 0);
            $periode        = trim($request->input('periode', $forecast->periode ?? ''));

            $partNumber = $forecast->part_number;
            $poSum      = (int) \App\Models\MasterPo::where('item_code', $partNumber)->orWhere('po', $partNumber)->sum('qty');
            $logSum     = (int) \App\Models\PurchasingLog::where('item_code', $partNumber)->orWhere('po_reference', $partNumber)->sum('actual_received');
            $delSum     = $logSum;
            if ($delSum <= 0) {
                $delSum = (int) \App\Models\ForecastActual::where('part_number', $partNumber)->where('periode', $periode)->sum('forecast_actual');
            }
            if ($delSum <= 0) {
                $delSum = (int) \App\Models\Actual::where('part_number', $partNumber)->where(function ($q) use ($periode) {
                    $q->where('periode', $periode)->orWhere('period_month', $periode);
                })->sum('actual_qty');
            }
            if ($delSum <= 0 && $poSum > 0) {
                $delSum = $poSum;
            }

            $calcForecast    = max(0, $poSum - $outstandingPre);
            $calcOutstanding = $outstandingPre + $poSum - $delSum;
            $calcStock       = $stockPre + $delSum - $production;

            $forecast->update([
                'description'     => !empty($description) ? $description : ($forecast->description ?? '-'),
                'price'           => $price,
                'outstanding_pre' => $outstandingPre,
                'stock_pre'       => $stockPre,
                'po'              => $poSum,
                'po_qty'          => $poSum,
                'delivery'        => $delSum,
                'forecast_qty'    => $forecastQty > 0 ? $forecastQty : max(0, $calcForecast),
                'outstanding'     => $calcOutstanding,
                'production'      => $production,
                'production_qty'  => $production,
                'stock'           => $calcStock,
                'stock_qty'       => $calcStock,
                'periode'         => $periode ?: $forecast->periode,
                'period_month'    => $periode ?: $forecast->period_month,
                'delivery_category_code' => $request->input('delivery_category_code', $forecast->delivery_category_code ?? 'LOC'),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data berhasil diupdate', 'data' => $forecast]);
            }
            return redirect()->back()->with('success', 'Data Forecast berhasil diupdate');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus data Forecast berdasarkan ID.
     */
    public function destroy($id, Request $request)
    {
        try {
            $forecast   = Forecasting::findOrFail($id);
            $partNumber = strtoupper(trim($forecast->part_number ?? ''));
            $forecast->delete();

            if (!empty($partNumber)) {
                \App\Models\PurchasingOutstanding::where('part_number', $partNumber)
                    ->orWhere('drawing', $partNumber)
                    ->delete();
                \App\Models\Outstanding::where('part_number', $partNumber)->delete();
                if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_forecast_actuals')) {
                    \App\Models\ForecastActual::where('part_number', $partNumber)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_comparison_master')) {
                    \App\Models\ComparisonMaster::where('part_number', $partNumber)->delete();
                }
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data Forecast berhasil dihapus']);
            }
            return redirect()->back()->with('success', 'Data Forecast berhasil dihapus');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus banyak data Forecast sekaligus (Bulk Delete).
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
                $totalCount = Forecasting::count();
                if ($deleteAll || (!empty($ids) && count($ids) >= $totalCount)) {
                    Forecasting::query()->delete();
                    \App\Models\PurchasingOutstanding::query()->delete();
                    \App\Models\Outstanding::query()->delete();
                    if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_forecast_actuals')) {
                        \App\Models\ForecastActual::query()->delete();
                    }
                    if (\Illuminate\Support\Facades\Schema::hasTable('purchasing_comparison_master')) {
                        \App\Models\ComparisonMaster::query()->delete();
                    }
                } else {
                    $forecasts   = Forecasting::whereIn('id', $ids)->get(['id', 'part_number']);
                    $partNumbers = $forecasts->pluck('part_number')->filter()->unique()->toArray();

                    foreach (array_chunk($ids, 500) as $chunkIds) {
                        Forecasting::whereIn('id', $chunkIds)->delete();
                    }

                    if (!empty($partNumbers)) {
                        foreach (array_chunk($partNumbers, 500) as $chunkKeys) {
                            \App\Models\PurchasingOutstanding::whereIn('part_number', $chunkKeys)
                                ->orWhereIn('drawing', $chunkKeys)
                                ->delete();
                            \App\Models\Outstanding::whereIn('part_number', $chunkKeys)->delete();
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

            $msg = $deleteAll ? 'Seluruh data Forecast berhasil dibersihkan.' : 'Data Forecast terpilih berhasil dihapus massal.';

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
     * Unduh template Excel / CSV untuk Master Forecast.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_master_forecast.csv"',
        ];

        $columns = ['Part Number', 'Description', 'Supplier', 'Periode', 'Forecast Qty', 'Price', 'Currency', 'Delivery Category'];
        $sample  = ['001234', 'Soundboard Spruce AAA', 'PT KAWAI INDONESIA', now()->format('Y-m'), '1500', '125.50', 'USD', 'IMP-REG'];

        $callback = function () use ($columns, $sample) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, $columns);
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
