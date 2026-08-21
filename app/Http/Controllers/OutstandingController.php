<?php

namespace App\Http\Controllers;

use App\Models\Outstanding;
use Illuminate\Http\Request;

class OutstandingController extends Controller
{
    /**
     * Tampilkan halaman Master Outstanding.
     */
    public function masterIndex(Request $request)
    {
        $periode = $this->normalizePeriodString($request->get('periode', now()->format('Y-m')));

        $outstandings = \App\Models\Outstanding::when($periode, function ($q) use ($periode) {
            $variants = $this->getPeriodVariantsString($periode);
            $q->where(function ($q2) use ($variants) {
                $q2->whereIn('periode', $variants)->orWhereIn('period_month', $variants);
            });
        })->orderBy('part_number')->get();

        $availablePeriodes = \App\Models\Outstanding::whereNotNull('periode')
            ->pluck('periode')
            ->merge(\App\Models\Outstanding::whereNotNull('period_month')->pluck('period_month'))
            ->filter()
            ->map(fn ($p) => $this->normalizePeriodString((string) $p))
            ->unique()->sortDesc()->values();

        return view('purchasing.master_outstanding', compact('outstandings', 'periode', 'availablePeriodes'));
    }

    /**
     * Simpan / Update Master Outstanding.
     */
    public function store(Request $request)
    {
        try {
            $partNumber     = strtoupper(trim($request->input('part_number', '')));
            $periode        = trim($request->input('periode', $request->input('period_month', '')));
            $outstandingQty = (int) $request->input('outstanding_qty', 0);
            $description    = trim($request->input('description', ''));
            $po             = trim($request->input('po', $request->input('drawing', '')));

            if (empty($partNumber) || empty($periode)) {
                throw new \Exception('Part Number dan Periode wajib diisi dengan format yang benar.');
            }

            $data = [
                'outstanding_qty' => $outstandingQty,
                'period_month'    => $periode,
            ];

            if (!empty($description)) $data['description'] = $description;
            if (!empty($po))          $data['po']          = strtoupper($po);

            $outstanding = Outstanding::updateOrCreate(
                [
                    'part_number' => $partNumber,
                    'periode'     => $periode,
                ],
                $data
            );

            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Outstanding berhasil disimpan',
                    'data'    => $outstanding,
                    'period'  => $periode,
                    'periode' => $periode,
                ], 200);
            }

            return redirect()->back()->with('success', 'Data Outstanding berhasil disimpan');
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
     * Update data Outstanding berdasarkan ID.
     */
    public function update(Request $request, $id)
    {
        try {
            $outstanding = Outstanding::findOrFail($id);

            $updateData = [];
            if ($request->has('outstanding_qty'))  $updateData['outstanding_qty'] = (int) $request->input('outstanding_qty');
            if ($request->filled('description'))   $updateData['description']     = trim($request->input('description'));
            if ($request->filled('po') || $request->filled('drawing')) {
                $updateData['po'] = strtoupper(trim($request->input('drawing', $request->input('po', ''))));
            }
            if ($request->filled('periode'))       $updateData['periode']         = trim($request->input('periode'));

            $outstanding->update($updateData);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data berhasil diupdate', 'data' => $outstanding]);
            }
            return redirect()->back()->with('success', 'Data Outstanding berhasil diupdate');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus data Outstanding berdasarkan ID atau Part Number + Periode.
     */
    public function destroy($id, Request $request = null)
    {
        // Support both route-param-id and query-string part_number+periode
        if (is_numeric($id)) {
            $record = Outstanding::find($id);
        } else {
            $partNumber = strtoupper(trim($request?->input('part_number', '')));
            $periode    = trim($request?->input('periode', ''));
            $record = Outstanding::where('part_number', $partNumber)
                ->where(function ($q) use ($periode) {
                    $q->where('periode', $periode)->orWhere('period_month', $periode);
                })->first();
        }

        if ($record) {
            $record->delete();
        }

        if ($request && ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest')) {
            return response()->json([
                'success' => true,
                'message' => 'Data Outstanding berhasil dihapus',
            ], 200);
        }

        return redirect()->back()->with('success', 'Data Outstanding berhasil dihapus');
    }

    /**
     * Hapus banyak data Outstanding sekaligus (Bulk Delete).
     */
    public function destroyBulk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return redirect()->back()->with('error', 'Tidak ada data terpilih untuk dihapus.');
            }

            Outstanding::whereIn('id', $ids)->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data Outstanding terpilih berhasil dihapus massal.']);
            }
            return redirect()->back()->with('success', 'Data Outstanding terpilih berhasil dihapus massal.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
