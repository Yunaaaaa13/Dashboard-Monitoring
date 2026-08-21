<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use App\Models\ProductionLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductionController extends Controller
{
    /**
     * Tampilkan form input data produksi real (Entry Operator / Supervisor)
     */
    public function createLog()
    {
        $lines = ProductionLine::where('status', 'Running')->get();
        $recentLogs = ProductionLog::with('line')
            ->orderBy('log_time', 'desc')
            ->take(15)
            ->get();

        return view('production.input', [
            'lines' => $lines,
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * Simpan data log produksi nyata ke database
     */
    public function storeLog(Request $request)
    {
        $validated = $request->validate([
            'production_line_id' => 'required|exists:production_lines,id',
            'log_date' => 'required|date',
            'log_hour' => 'required|string',
            'target_output' => 'required|integer|min:0',
            'actual_output' => 'required|integer|min:0',
            'defect_count' => 'nullable|integer|min:0',
            'status_note' => 'nullable|string|max:255',
        ]);

        $logDateTime = Carbon::parse($validated['log_date'] . ' ' . $validated['log_hour']);

        // Tentukan status otomatis berdasarkan achievement jam tersebut
        $target = $validated['target_output'];
        $actual = $validated['actual_output'];
        $achievement = $target > 0 ? ($actual / $target) * 100 : 0;

        $statusNote = $validated['status_note'] ?? 'Normal';
        if (empty($validated['status_note'])) {
            if ($achievement >= 100) {
                $statusNote = 'Target Achieved';
            } elseif ($achievement >= 90) {
                $statusNote = 'Normal On Track';
            } else {
                $statusNote = 'Under Target Alert';
            }
        }

        ProductionLog::create([
            'production_line_id' => $validated['production_line_id'],
            'ezrunner_batch_id' => 'EZR-MANUAL-' . $logDateTime->format('YmdHi'),
            'log_time' => $logDateTime,
            'target_output' => $target,
            'actual_output' => $actual,
            'defect_count' => $validated['defect_count'] ?? 0,
            'status_note' => $statusNote,
        ]);

        return redirect()->route('production.input')
            ->with('success', 'Data produksi aktual berhasil disimpan ke sistem monitoring!');
    }

    /**
     * Hapus log produksi
     */
    public function destroyLog($id)
    {
        $log = ProductionLog::findOrFail($id);
        $log->delete();

        return redirect()->back()->with('success', 'Data log produksi berhasil dihapus.');
    }

    /**
     * Halaman Manajemen Master Line Produksi PT Kawai Indonesia
     */
    public function lines()
    {
        $lines = ProductionLine::withCount('logs')->get();
        return view('production.lines', ['lines' => $lines]);
    }

    /**
     * Tambah Line Produksi Baru
     */
    public function storeLine(Request $request)
    {
        $validated = $request->validate([
            'line_code' => 'required|string|unique:production_lines,line_code',
            'line_name' => 'required|string',
            'product_category' => 'required|string',
            'supervisor' => 'required|string',
            'daily_target_capacity' => 'required|integer|min:1',
            'status' => 'required|in:Running,Idle,Maintenance,Alert',
        ]);

        ProductionLine::create($validated);

        return redirect()->route('production.lines')
            ->with('success', 'Line produksi baru berhasil ditambahkan.');
    }

    /**
     * Update Line Produksi
     */
    public function updateLine(Request $request, $id)
    {
        $line = ProductionLine::findOrFail($id);

        $validated = $request->validate([
            'line_name' => 'required|string',
            'product_category' => 'required|string',
            'supervisor' => 'required|string',
            'daily_target_capacity' => 'required|integer|min:1',
            'status' => 'required|in:Running,Idle,Maintenance,Alert',
        ]);

        $line->update($validated);

        return redirect()->route('production.lines')
            ->with('success', 'Data Line Produksi berhasil diperbarui.');
    }

    /**
     * REST API / Webhook Real untuk Menerima Data Otomatis dari Sistem Mesin EZRunner
     * POST /api/ezrunner/sync
     */
    public function ezrunnerSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'line_code' => 'required|string|exists:production_lines,line_code',
            'log_time' => 'required|date',
            'target_output' => 'required|integer|min:0',
            'actual_output' => 'required|integer|min:0',
            'defect_count' => 'nullable|integer|min:0',
            'batch_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $line = ProductionLine::where('line_code', $request->line_code)->first();

        $log = ProductionLog::create([
            'production_line_id' => $line->id,
            'ezrunner_batch_id' => $request->batch_id ?? ('EZR-AUTO-' . time()),
            'log_time' => Carbon::parse($request->log_time),
            'target_output' => $request->target_output,
            'actual_output' => $request->actual_output,
            'defect_count' => $request->defect_count ?? 0,
            'status_note' => 'EZRunner Auto-Sync',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data EZRunner berhasil disinkronkan ke Dashboard Monitoring Produksi',
            'data' => $log
        ], 201);
    }

    /**
     * Bersihkan seluruh log dummy/testing agar sistem 100% menggunakan data real
     */
    public function clearLogs()
    {
        ProductionLog::truncate();
        return redirect()->route('dashboard.overview')
            ->with('success', 'Seluruh data log testing telah dibersihkan. Sistem siap mencatat data real produksi!');
    }
}
