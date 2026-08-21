<?php

namespace App\Http\Controllers;

use App\Models\PurchasingCategory;
use App\Models\PurchasingLog;
use App\Models\PurchasingOutstanding;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * Tampilkan Halaman History Gabungan (Hasil Input Realisasi & Outstanding Order)
     */
    public function index(Request $request)
    {
        $activeTab   = $request->get('tab', 'input');
        $searchQuery = $request->get('search');
        $selectedDeliveryCategory = $request->get('delivery_category');

        $categories  = PurchasingCategory::all();

        // 1. Query Hasil Input Realisasi (Purchasing Logs)
        $inputLogsQuery = PurchasingLog::with(['category', 'user']);
        if ($selectedDeliveryCategory) {
            $inputLogsQuery->where('delivery_category_code', $selectedDeliveryCategory);
        }
        if ($searchQuery) {
            $inputLogsQuery->where(function ($q) use ($searchQuery) {
                $q->where('po_reference', 'like', "%{$searchQuery}%")
                  ->orWhere('period_month', 'like', "%{$searchQuery}%")
                  ->orWhere('status_note', 'like', "%{$searchQuery}%")
                  ->orWhereHas('category', function ($catQ) use ($searchQuery) {
                      $catQ->where('category_name', 'like', "%{$searchQuery}%")
                           ->orWhere('category_code', 'like', "%{$searchQuery}%");
                  });
            });
        }
        $inputLogs = $inputLogsQuery->orderBy('period_month', 'desc')->orderBy('updated_at', 'desc')->get();

        // 2. Query Data Outstanding Order & Sync with Step 3 Realisasi Data
        $outstandingQuery = PurchasingOutstanding::query();
        if ($selectedDeliveryCategory) {
            $outstandingQuery->where('delivery_category_code', $selectedDeliveryCategory);
        }
        if ($searchQuery) {
            $outstandingQuery->where(function ($q) use ($searchQuery) {
                $q->where('po_number', 'like', "%{$searchQuery}%")
                  ->orWhere('part_number', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%")
                  ->orWhere('drawing', 'like', "%{$searchQuery}%")
                  ->orWhere('supplier_name', 'like', "%{$searchQuery}%")
                  ->orWhere('status', 'like', "%{$searchQuery}%");
            });
        }
        $rawOutstandings = $outstandingQuery->orderBy('updated_at', 'desc')->get();

        $outstandings = $rawOutstandings->map(function($o) {
            $partKey = strtoupper(trim($o->part_number ?: $o->drawing));
            $poKey   = strtoupper(trim($o->po_number));

            // 1. Target Order Qty
            $orderQty = (int) $o->order_qty;
            if ($orderQty <= 0) {
                $orderQty = (int) $o->computed_order_qty;
            }
            if ($orderQty <= 0) {
                for ($m = 0; $m <= 36; $m++) {
                    $orderQty += (int) ($o->{"m{$m}_po"} ?? 0);
                }
            }
            if ($orderQty <= 0) {
                $orderQty = (int) \App\Models\MasterPo::where('item_code', $partKey)->orWhere('po', $poKey)->sum('qty');
            }
            if ($orderQty <= 0) {
                $orderQty = 100;
            }

            // 2. Realisasi Completed Qty from Step 3 Logs
            $actualReceived = (int) PurchasingLog::where(function($q) use ($partKey, $poKey) {
                if ($partKey) $q->where('item_code', $partKey)->orWhere('po_reference', $partKey);
                if ($poKey) $q->orWhere('po_reference', $poKey)->orWhere('item_code', $poKey);
            })->sum('actual_received');

            if ($actualReceived <= 0 && $o->complete > 0) {
                $actualReceived = (int) $o->complete;
            }

            // 3. Price & Amount
            $price = (float) $o->price;
            if ($price <= 0) {
                $fc = \App\Models\Forecasting::where('part_number', $partKey)->first();
                if ($fc) $price = (float) $fc->price;
            }
            $amount = $orderQty * $price;

            // 4. Progress %
            $progressPct = $orderQty > 0 ? min(100, round(($actualReceived / $orderQty) * 100, 1)) : 0;

            $o->display_order_qty = $orderQty;
            $o->display_complete  = $actualReceived;
            $o->display_price     = $price;
            $o->display_amount    = $amount;
            $o->display_progress  = $progressPct;

            return $o;
        });

        // KPI Ringkasan
        $allInputLogs       = PurchasingLog::all();
        $totalInputLogs     = $allInputLogs->count();
        $totalInputReceived = $allInputLogs->sum('actual_received');
        $totalInputTarget   = $allInputLogs->sum('target_order');

        $totalOutstandingItems  = $outstandings->count();
        $totalOutstandingAmount = $outstandings->sum('display_amount');
        $totalCompleteUnits     = max($totalInputReceived, (int) $outstandings->sum('display_complete'));

        return view('purchasing.history', [
            'activeTab'             => $activeTab,
            'searchQuery'           => $searchQuery,
            'categories'            => $categories,
            'inputLogs'             => $inputLogs,
            'outstandings'          => $outstandings,
            'totalInputLogs'        => $totalInputLogs,
            'totalInputReceived'    => $totalInputReceived,
            'totalInputTarget'      => $totalInputTarget,
            'totalOutstandingItems' => $totalOutstandingItems,
            'totalOutstandingAmount'=> $totalOutstandingAmount,
            'totalCompleteUnits'    => $totalCompleteUnits,
            'selectedDeliveryCategory' => $selectedDeliveryCategory,
            'deliveryCategories'     => \App\Models\DeliveryCategory::all(),
        ]);
    }

    /**
     * Update data Hasil Input Realisasi Pembelian (Purchasing Log)
     */
    public function updateInputLog(Request $request, $id)
    {
        $log = PurchasingLog::findOrFail($id);

        $validated = $request->validate([
            'purchasing_category_id' => 'required|exists:purchasing_categories,id',
            'period_month'           => 'required|string',
            'po_reference'           => 'nullable|string',
            'target_order'           => 'required|integer|min:0',
            'actual_received'        => 'required|integer|min:0',
            'verification_status'    => 'nullable|string|in:pending,approved,conditional,rejected',
            'status_note'            => 'nullable|string|max:255',
        ]);

        $target   = (int) $validated['target_order'];
        $received = (int) $validated['actual_received'];
        $pending  = max(0, $target - $received);

        $userRole = auth()->user() ? auth()->user()->role : 'staff';
        $userName = auth()->user() ? auth()->user()->name : 'System';
        $vStatus  = $request->input('verification_status', 'pending');
        $userNote = trim($validated['status_note'] ?? 'Diperbarui via History');

        // Jika role adalah staff, tidak boleh langsung memilih status Disetujui Diterima
        if ($userRole === 'staff' && $vStatus === 'approved') {
            $vStatus = 'pending';
        }

        $roleTitle = ucfirst($userRole);
        if ($vStatus === 'approved') {
            $statusNote = "✅ Disetujui Diterima ($roleTitle: $userName) - $userNote";
        } elseif ($vStatus === 'conditional') {
            $statusNote = "⚠️ Diterima Bersyarat ($roleTitle: $userName) - $userNote";
        } elseif ($vStatus === 'rejected') {
            $statusNote = "❌ Ditolak / Revisi ($roleTitle: $userName) - $userNote";
        } else {
            if ($userRole === 'staff') {
                $statusNote = "⏳ Menunggu Approval (Staff: $userName) - $userNote";
            } else {
                $statusNote = "⏳ Menunggu Approval ($roleTitle: $userName) - $userNote";
            }
        }

        $log->update([
            'purchasing_category_id' => $validated['purchasing_category_id'],
            'period_month'           => $validated['period_month'],
            'po_reference'           => $validated['po_reference'] ?: $log->po_reference,
            'target_order'           => $target,
            'actual_received'        => $received,
            'pending_order'          => $pending,
            'status_note'            => $statusNote,
        ]);

        if ($received < $target) {
            $diff = number_format($target - $received, 0, ',', '.');
            session()->flash('warning', "⚠️ <strong>Peringatan Selisih Target (Under-Delivery):</strong> Aktual Diterima (" . number_format($received, 0, ',', '.') . " unit) <strong>kurang dari Target PO</strong> (" . number_format($target, 0, ',', '.') . " unit). Terdapat selisih kekurangan sebesar <strong>$diff unit</strong> yang dicatat sebagai Pending Order.");
        } elseif ($received > $target) {
            $diff = number_format($received - $target, 0, ',', '.');
            session()->flash('warning', "⚠️ <strong>Peringatan Kelebihan Target (Over-Delivery):</strong> Aktual Diterima (" . number_format($received, 0, ',', '.') . " unit) <strong>melebihi Target PO</strong> (" . number_format($target, 0, ',', '.') . " unit). Terdapat kelebihan penerimaan sebesar <strong>+$diff unit</strong> dari target awal.");
        }

        return redirect()->route('purchasing.history', ['tab' => 'input'])
            ->with('success', 'Riwayat Input Realisasi PO (' . $log->po_reference . ') berhasil diperbarui.');
    }

    /**
     * Setujui resmi catatan realisasi sebagai Diterima (Khusus Supervisor & Leader)
     */
    public function approveInputLog($id)
    {
        $log = PurchasingLog::findOrFail($id);

        // Bersihkan prefix status lama jika ada
        $cleanNote = preg_replace('/^(⏳ Menunggu (Verifikasi|Approval)[^-\)]*[-\)]*\s*|⚠️[^\:]*\:\s*[^-\)]*[-\)]*\s*|❌[^\:]*\:\s*[^-\)]*[-\)]*\s*|✅[^\:]*\:\s*[^-\)]*[-\)]*\s*)/i', '', $log->status_note);
        $cleanNote = trim(preg_replace('/^(\-\s*|\:\s*)/', '', $cleanNote));
        if (empty($cleanNote) || $cleanNote === 'Order Active' || $cleanNote === 'Diperbarui via History') {
            $cleanNote = 'Diterima lengkap & sesuai spesifikasi';
        }

        $approverRole = ucfirst(auth()->user()->role);
        $approverName = auth()->user()->name;

        $log->update([
            'status_note' => "✅ Disetujui Diterima ($approverRole: $approverName) - $cleanNote"
        ]);

        // Auto-sync ke Master Data setelah disetujui (Tahap Akhir Realisasi)
        $poClean = strtoupper(trim($log->po_reference ?? ''));
        $itemCodeClean = strtoupper(trim($log->item_code ?? ''));
        if (!empty($poClean) || !empty($itemCodeClean)) {
            $masterQuery = \App\Models\PurchasingOutstanding::query();
            if (!empty($itemCodeClean)) {
                $masterQuery->where('part_number', $itemCodeClean)->orWhere('drawing', $itemCodeClean);
            }
            if (!empty($poClean)) {
                $masterQuery->orWhere('part_number', $poClean)->orWhere('po_number', $poClean);
            }
            $masterItem = $masterQuery->first();

            $keyToSync = $itemCodeClean ?: ($masterItem ? ($masterItem->part_number ?: $masterItem->drawing) : $poClean);

            if ($masterItem) {
                $masterItem->update([
                    'complete' => (int) $log->actual_received,
                    'status' => ((int) $log->actual_received >= (int) $masterItem->order_qty && (int) $masterItem->order_qty > 0) ? 'Complete' : (((int) $log->actual_received > 0) ? 'On Progress' : 'Pending')
                ]);
                $keyToSync = strtoupper(trim($masterItem->part_number ?: $masterItem->drawing));
            }

            \App\Models\Actual::updateOrCreate(
                [
                    'part_number' => $keyToSync,
                    'periode'     => $log->period_month
                ],
                [
                    'description'       => $log->item_name ?? ($masterItem->description ?? 'Material Item'),
                    'actual_qty'        => (int) $log->actual_received,
                    'actual_po'         => (int) $log->actual_received,
                    'actual_production' => (int) ($log->production_qty ?? 0),
                    'period_month'      => $log->period_month
                ]
            );
            if (class_exists(\App\Models\ComparisonMaster::class)) {
                \App\Models\ComparisonMaster::sync($keyToSync, $log->period_month);
            }
        }

        return redirect()->back()->with('success', "Catatan realisasi PO (" . ($log->po_reference ?: ('ID #' . $log->id)) . ") resmi disetujui & diverifikasi sebagai Diterima oleh $approverRole ($approverName). Data Master & Analisis otomatis ter-update!");
    }

    /**
     * Tolak / Minta revisi catatan realisasi (Khusus Supervisor & Leader)
     */
    public function rejectInputLog(Request $request, $id)
    {
        $log = PurchasingLog::findOrFail($id);
        $reason = trim($request->input('reason', 'Perlu revisi format / angka realisasi'));

        // Bersihkan prefix status lama jika ada
        $cleanNote = preg_replace('/^(⏳ Menunggu (Verifikasi|Approval)[^-\)]*[-\)]*\s*|⚠️[^\:]*\:\s*[^-\)]*[-\)]*\s*|❌[^\:]*\:\s*[^-\)]*[-\)]*\s*|✅[^\:]*\:\s*[^-\)]*[-\)]*\s*)/i', '', $log->status_note);
        $cleanNote = trim(preg_replace('/^(\-\s*|\:\s*)/', '', $cleanNote));
        if (empty($cleanNote) || $cleanNote === 'Order Active' || $cleanNote === 'Diperbarui via History') {
            $cleanNote = 'Perlu perbaikan data input';
        }

        $rejectorRole = ucfirst(auth()->user()->role);
        $rejectorName = auth()->user()->name;

        $log->update([
            'status_note' => "❌ Ditolak / Revisi ($rejectorRole: $rejectorName) - Alasan: $reason"
        ]);

        return redirect()->back()->with('error', "Catatan realisasi PO (" . ($log->po_reference ?: ('ID #' . $log->id)) . ") ditolak & dikembalikan ke Staff untuk direvisi. Alasan: $reason");
    }

    /**
     * Hapus data Hasil Input Realisasi Pembelian (Purchasing Log)
     */
    public function destroyInputLog($id)
    {
        $log = PurchasingLog::findOrFail($id);
        $poRef = $log->po_reference ?: ('ID #' . $log->id);
        $log->delete();

        return redirect()->route('purchasing.history', ['tab' => 'input'])
            ->with('success', 'Riwayat Hasil Input Realisasi (' . $poRef . ') berhasil dihapus.');
    }

    /**
     * Update data Outstanding Order (Secara Lengkap)
     */
    public function updateOutstanding(Request $request, $id)
    {
        $item = PurchasingOutstanding::findOrFail($id);

        $validated = $request->validate([
            'po_number'     => 'nullable|string',
            'po_date'       => 'nullable|date',
            'part_number'   => 'required|string|unique:purchasing_outstandings,part_number,' . $id,
            'description'   => 'required|string',
            'drawing'       => 'nullable|string',
            'supplier_name' => 'nullable|string',
            'eta_date'      => 'nullable|date',
            'order_qty'     => 'required|integer|min:1',
            'price'         => 'required|integer|min:0',
            'complete'      => 'required|integer|min:0',
        ]);

        $orderQty = (int) $validated['order_qty'];
        $price    = (int) $validated['price'];
        $complete = min($orderQty, (int) $validated['complete']);
        $amount   = $orderQty * $price;

        $status = 'Pending';
        if ($complete >= $orderQty) {
            $status = 'Complete';
        } elseif ($complete > 0) {
            $status = 'On Progress';
        }

        $item->update([
            'po_number'     => strtoupper($validated['po_number'] ?? $item->po_number),
            'po_date'       => $validated['po_date'] ?? $item->po_date,
            'part_number'   => strtoupper($validated['part_number']),
            'description'   => $validated['description'],
            'drawing'       => strtoupper($validated['drawing'] ?? '-'),
            'supplier_name' => !empty($validated['supplier_name']) ? $validated['supplier_name'] : null,
            'eta_date'      => $validated['eta_date'] ?: null,
            'order_qty'     => $orderQty,
            'price'         => $price,
            'amount'        => $amount,
            'complete'      => $complete,
            'status'        => $status,
            'delivery_category_code' => $request->input('delivery_category_code', $item->delivery_category_code ?? 'LOC'),
        ]);

        if ($complete < $orderQty) {
            $diff = number_format($orderQty - $complete, 0, ',', '.');
            session()->flash('warning', "⚠️ <strong>Peringatan Outstanding (Pending Qty):</strong> Unit Diterima (" . number_format($complete, 0, ',', '.') . " unit) <strong>belum memenuhi Target Order PO</strong> (" . number_format($orderQty, 0, ',', '.') . " unit). Terdapat sisa kekurangan <strong>$diff unit</strong> yang masih dalam status On Progress / Pending.");
        }

        return redirect()->route('purchasing.history', ['tab' => 'outstanding'])
            ->with('success', 'Data Outstanding Part ' . $item->part_number . ' berhasil diperbarui.');
    }

    /**
     * Hapus data Outstanding Order
     */
    public function destroyOutstanding($id)
    {
        $item = PurchasingOutstanding::findOrFail($id);
        $partNo = $item->part_number;
        $item->delete();

        return redirect()->route('purchasing.history', ['tab' => 'outstanding'])
            ->with('success', 'Data Outstanding Part ' . $partNo . ' berhasil dihapus dari history.');
    }

    /**
     * Export Riwayat Data ke CSV
     */
    public function export(Request $request)
    {
        $tab = $request->get('tab', 'outstanding');
        $filename = "History_" . ucfirst($tab) . "_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        if ($tab === 'input') {
            $data = PurchasingLog::with('category')->orderBy('period_month', 'desc')->get();
            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['No', 'Item Code', 'PO Reference', 'Periode', 'Receipt Date', 'Description', 'Supplier', 'Target PO', 'Actual Received', 'Selisih', 'Kategori Pengantaran']);
                foreach ($data as $i => $row) {
                    fputcsv($file, [
                        $i + 1,
                        $row->item_code,
                        $row->po_reference,
                        $row->period_month,
                        $row->receipt_date,
                        $row->description,
                        $row->supplier_name,
                        $row->target_order,
                        $row->actual_received,
                        $row->actual_received - $row->target_order,
                        $row->delivery_category_code ?? 'LOC'
                    ]);
                }
                fclose($file);
            };
        } else {
            $data = PurchasingOutstanding::orderBy('updated_at', 'desc')->get();
            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['No', 'PO Number', 'PO Date', 'Drawing', 'Part Number', 'Description', 'Supplier', 'Order Qty', 'Price', 'Currency', 'Amount', 'Complete Qty', 'Status', 'Kategori Pengantaran']);
                foreach ($data as $i => $row) {
                    fputcsv($file, [
                        $i + 1,
                        $row->po_number,
                        $row->po_date,
                        $row->drawing,
                        $row->part_number,
                        $row->description,
                        $row->supplier_name,
                        $row->order_qty,
                        $row->price,
                        $row->currency ?? 'USD',
                        $row->amount,
                        $row->complete,
                        $row->status,
                        $row->delivery_category_code ?? 'LOC'
                    ]);
                }
                fclose($file);
            };
        }

        return response()->stream($callback, 200, $headers);
    }
}
