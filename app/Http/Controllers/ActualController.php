<?php

namespace App\Http\Controllers;

use App\Models\Actual;
use App\Models\Forecasting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActualController extends Controller
{
    /**
     * Tampilkan halaman Master Actual.
     */
    public function masterIndex(Request $request)
    {
        $periode = $this->normalizePeriodString($request->get('periode', now()->format('Y-m')));

        $actuals = \App\Models\Actual::when($periode, function ($q) use ($periode) {
            $variants = $this->getPeriodVariantsString($periode);
            $q->where(function ($q2) use ($variants) {
                $q2->whereIn('periode', $variants)->orWhereIn('period_month', $variants);
            });
        })->orderBy('part_number')->get();

        $availablePeriodes = \App\Models\Actual::whereNotNull('periode')
            ->pluck('periode')
            ->merge(\App\Models\Actual::whereNotNull('period_month')->pluck('period_month'))
            ->filter()
            ->map(fn ($p) => $this->normalizePeriodString((string) $p))
            ->unique()->sortDesc()->values();

        return view('purchasing.master_actual', compact('actuals', 'periode', 'availablePeriodes'));
    }

    public function store(Request $request)
    {
        try {
            $partNumber = strtoupper(trim($request->input('part_number', '')));
            $periode = trim($request->input('periode', $request->input('period_month', $request->input('custom_month', ''))));
            $actualPo = (int) $request->input('actual_po', 0);
            $actualProduction = (int) $request->input('actual_production', 0);
            // New master actual_qty field
            $actualQtyInput = $request->has('actual_qty') ? (int) $request->input('actual_qty') : null;
            $description = trim($request->input('description', ''));

            if (empty($partNumber) || empty($periode)) {
                throw new \Exception('Part Number dan Periode wajib diisi dengan format yang benar.');
            }

            // Ambil inputan master data jika disertakan (agar sama seperti input outstanding)
            $poNumber     = $request->input('po_number', null);
            $poDate       = $request->input('po_date', date('Y-m-d'));
            $description  = $request->input('description', null);
            $drawing      = $request->input('drawing', null);
            $supplierName = $request->input('supplier_name', null);
            $etaDate      = $request->input('eta_date', null);
            $orderQty     = $request->has('order_qty') && $request->input('order_qty') !== '' ? (int) $request->input('order_qty') : ($request->has('po') && $request->input('po') !== '' ? (int) $request->input('po') : null);
            $price        = $request->has('price') && $request->input('price') !== '' ? (int) $request->input('price') : null;
            $planStock    = $request->has('plan_stock') && $request->input('plan_stock') !== '' ? (int) $request->input('plan_stock') : null;
            $complete     = $request->has('complete') && $request->input('complete') !== '' ? (int) $request->input('complete') : null;

            // 1. Simpan atau update PurchasingOutstanding (Master PO)
            // NOTE: Prevent accidental auto-creation of PO from Monitoring/Actual input.
            // Require explicit `create_po` flag in the request to create a new PurchasingOutstanding.
            $master = \App\Models\PurchasingOutstanding::where('part_number', $partNumber)->first();
            $shouldCreatePo = $request->boolean('create_po', false);
            if (!$master && $shouldCreatePo) {
                // If order quantity not provided, prefer actual_po; otherwise skip creating PO
                $orderQtyVal = $orderQty ?? ($actualPo > 0 ? $actualPo : null);
                if ($orderQtyVal !== null) {
                    $priceVal    = $price ?? 100000;
                    \App\Models\PurchasingOutstanding::create([
                        'po_number'      => strtoupper($poNumber ?? ('PO-KI-' . date('Ym') . '-' . rand(100, 999))),
                        'po_date'        => $poDate ?? date('Y-m-d'),
                        'part_number'    => $partNumber,
                        'description'    => $description,
                        'order_qty'      => $orderQtyVal,
                        'drawing'        => strtoupper($drawing ?? '-'),
                        'price'          => $priceVal,
                        'amount'         => $orderQtyVal * $priceVal,
                        'complete'       => $complete ?? $actualPo,
                        'status'         => ($complete ?? $actualPo) >= $orderQtyVal && $orderQtyVal > 0 ? 'Complete' : 'On Progress',
                        'workflow_stage' => 'waiting_manager',
                        'approval_notes' => 'Draft PO Baru Dibuat dari Input Forecasting (Actual)',
                        'supplier_name'  => !empty($supplierName) ? $supplierName : null,
                        'eta_date'       => $etaDate ?? null,
                        'plan_stock'     => $planStock ?? 0,
                    ]);
                }
            } elseif ($master) {
                $updateData = [];
                if ($poNumber !== null && $poNumber !== '') $updateData['po_number'] = strtoupper($poNumber);
                if ($poDate !== null && $poDate !== '') $updateData['po_date'] = $poDate;
                if ($description !== null && $description !== '') $updateData['description'] = $description;
                if ($orderQty !== null) {
                    $updateData['order_qty'] = $orderQty;
                    $updateData['amount']    = $orderQty * ($price !== null ? $price : ($master->price ?? 0));
                }
                if ($drawing !== null && $drawing !== '') $updateData['drawing'] = strtoupper($drawing);
                if ($price !== null) {
                    $updateData['price']  = $price;
                    $updateData['amount'] = ($orderQty !== null ? $orderQty : ($master->order_qty ?? 0)) * $price;
                }
                if ($supplierName !== null && $supplierName !== '') $updateData['supplier_name'] = $supplierName;
                if ($etaDate !== null && $etaDate !== '') $updateData['eta_date'] = $etaDate;
                if ($planStock !== null) $updateData['plan_stock'] = $planStock;
                if ($complete !== null) $updateData['complete'] = $complete;
                if (!empty($updateData)) {
                    $master->update($updateData);
                }
            }

            // 2. Jika order_qty ada, update juga tabel outstandings untuk periode ini
            if ($orderQty !== null) {
                \App\Models\Outstanding::updateOrCreate(
                    [
                        'part_number' => $partNumber,
                        'periode'     => $periode,
                    ],
                    [
                        'outstanding_qty' => $orderQty,
                        'period_month'    => $periode,
                    ]
                );
            }

            // Hitung Forecast Stock bulan sebelumnya (atau Actual Stock bulan sebelumnya jika ada)
            $prevStock = 0;
            try {
                $prevMonth = Carbon::createFromFormat('Y-m', $periode)->subMonth()->format('Y-m');
                $prevForecast = Forecasting::where('part_number', $partNumber)
                    ->where(function ($q) use ($prevMonth) {
                        $q->where('periode', $prevMonth)->orWhere('period_month', $prevMonth);
                    })->first();

                if ($prevForecast) {
                    $prevStock = (int) ($prevForecast->stock ?? $prevForecast->stock_qty ?? 0);
                } else {
                    $prevActual = Actual::where('part_number', $partNumber)
                        ->where(function ($q) use ($prevMonth) {
                            $q->where('periode', $prevMonth)->orWhere('period_month', $prevMonth);
                        })->first();
                    if ($prevActual) {
                        $prevStock = (int) $prevActual->actual_stock;
                    }
                }
            } catch (\Exception $ex) {
                $prevStock = 0;
            }

            // Rumus Actual Stock: Forecast Stock bulan sebelumnya + Actual PO - Actual Production
            $actualStock = $prevStock + $actualPo - $actualProduction;

            // 3. Simpan data Actual
            $actualData = [
                'actual_po'         => max($actualPo, $actualQtyInput ?? 0),
                'actual_production' => $actualProduction,
                'actual_stock'      => $actualStock,
                'period_month'      => $periode,
                'actual_qty'        => $actualQtyInput ?? max($actualStock, $actualPo),
            ];
            if (!empty($description)) {
                $actualData['description'] = $description;
            }
            $actual = Actual::updateOrCreate(
                [
                    'part_number' => $partNumber,
                    'periode'     => $periode,
                ],
                $actualData
            );

            // 4. Update/Create ForecastActual agar rumus PT Kawai tersinkronisasi
            $masterPoQty = $orderQty !== null ? $orderQty : ($master->order_qty ?? ($actualPo > 0 ? $actualPo : 0));
            $outQty = \App\Models\Outstanding::where('part_number', $partNumber)
                ->where(function ($q) use ($periode) {
                    $q->where('periode', $periode)->orWhere('period_month', $periode);
                })->value('outstanding_qty') ?? 0;
            $faVal = max(0, $masterPoQty - $outQty);

            \App\Models\ForecastActual::updateOrCreate(
                [
                    'part_number' => $partNumber,
                    'periode'     => $periode,
                ],
                [
                    'description'     => $description ?? ($master->description ?? '-'),
                    'po'              => $masterPoQty,
                    'forecast_actual' => $faVal > 0 ? $faVal : $actualPo,
                ]
            );

            // 5. Trigger sync ke ComparisonMaster
            \App\Models\ComparisonMaster::sync($partNumber, $periode);

            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Forecasting (Actual) berhasil disimpan & tersinkronisasi',
                    'data'    => $actual,
                    'period'  => $periode,
                    'periode' => $periode,
                ], 200);
            }

            return redirect()->route('purchasing.outstanding', ['comparison_period' => $periode])
                ->with('success', 'Data Forecasting (Actual) berhasil disimpan & tersinkronisasi');
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
     * Hapus data Realisasi Actual bulanan
     */
    public function destroy(Request $request)
    {
        $partNumber = strtoupper(trim($request->input('part_number', '')));
        $periode    = trim($request->input('periode', ''));

        if (!empty($partNumber) && !empty($periode)) {
            Actual::where('part_number', $partNumber)
                ->where(function($q) use ($periode) {
                    $q->where('periode', $periode)->orWhere('period_month', $periode);
                })->delete();

            \App\Models\ComparisonMaster::syncDelete($partNumber, $periode);
        }

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => "Data Realisasi Actual part {$partNumber} periode {$periode} berhasil dihapus.",
                'period'  => $periode,
                'periode' => $periode,
            ], 200);
        }

        return redirect()->back()
            ->with('success', "Data Realisasi Actual part {$partNumber} periode {$periode} berhasil dihapus.");
    }

    /**
     * Update data Actual berdasarkan ID (untuk Master Actual page).
     */
    public function update(Request $request, $id)
    {
        try {
            $actual = Actual::findOrFail($id);

            $updateData = [];
            if ($request->has('actual_qty')) {
                $updateData['actual_qty'] = (int) $request->input('actual_qty');
                $updateData['actual_po']  = (int) $request->input('actual_qty');
            }
            if ($request->filled('description'))  $updateData['description']     = trim($request->input('description'));
            if ($request->filled('periode'))      $updateData['periode']         = trim($request->input('periode'));

            $actual->update($updateData);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data berhasil diupdate', 'data' => $actual]);
            }
            return redirect()->back()->with('success', 'Data Actual berhasil diupdate');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus data Actual berdasarkan ID (untuk Master Actual page).
     */
    public function destroyById($id, Request $request)
    {
        try {
            $actual = Actual::findOrFail($id);
            $actual->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data Actual berhasil dihapus']);
            }
            return redirect()->back()->with('success', 'Data Actual berhasil dihapus');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus banyak data Actual sekaligus (Bulk Delete).
     */
    public function destroyBulk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return redirect()->back()->with('error', 'Tidak ada data terpilih untuk dihapus.');
            }

            Actual::whereIn('id', $ids)->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data Actual terpilih berhasil dihapus massal.']);
            }
            return redirect()->back()->with('success', 'Data Actual terpilih berhasil dihapus massal.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
