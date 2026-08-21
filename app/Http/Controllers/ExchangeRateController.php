<?php

namespace App\Http\Controllers;

use App\Models\TaxExchangeRate;
use App\Models\TaxBudgetForecastRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExchangeRateController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  DASHBOARD INDEX
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        if (Auth::check() && !Auth::user()->hasPermission('exchange_rate')) {
            return redirect()->route('dashboard.overview')
                ->with('error', 'Akses Ditolak. Anda tidak memiliki hak akses untuk fitur Manajemen & Input Kurs Pajak.');
        }

        $selectedYear     = (int) ($request->get('year',  date('Y')));
        $selectedCurrency = (int) ($request->get('currency', 2)); // default USD/IDR

        // ── Daftar Tahun Tersedia (Dinamis DB + 10 Tahun Kedepan) ──
        $yearsInDb = TaxExchangeRate::pluck('exch_year')
            ->concat(TaxBudgetForecastRate::pluck('exch_year'))
            ->filter()
            ->map(fn($y) => (int)$y)
            ->unique()
            ->toArray();

        $currentY = (int) date('Y');
        $futureYears = range($currentY - 2, $currentY + 10);
        $availableYears = array_unique(array_merge($futureYears, $yearsInDb));
        sort($availableYears);

        // ── Record Terkini / Terakhir Di-upload di Seluruh Sistem ──
        $latestOverallRecord = TaxExchangeRate::ofCurrency($selectedCurrency)
            ->orderBy('exch_year', 'desc')
            ->orderBy('exch_month', 'desc')
            ->orderBy('week_code', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // Jika bulan tidak secara eksplisit dipilih di URL, gunakan bulan dari data terakhir diupload
        $rawMonth = $request->get('month');
        if ($rawMonth === 'all') {
            $selectedMonth = 'all';
        } elseif ($rawMonth !== null && is_numeric($rawMonth)) {
            $selectedMonth = (int) $rawMonth;
        } else {
            $selectedMonth = $latestOverallRecord ? $latestOverallRecord->exch_month : (int) date('n');
        }

        // ── Data untuk Chart Mingguan (bulan & tahun terpilih atau ALL) ──
        $weeklyData = TaxExchangeRate::ofYear($selectedYear)
            ->ofCurrency($selectedCurrency)
            ->when($selectedMonth !== 'all', fn($q) => $q->ofMonth((int)$selectedMonth))
            ->orderBy('exch_month')
            ->orderBy('week_code')
            ->get();

        // ── Data untuk Chart Bulanan (rata-rata per bulan, tahun terpilih) ──
        $monthlyData = TaxExchangeRate::ofYear($selectedYear)
            ->ofCurrency($selectedCurrency)
            ->select('exch_month', DB::raw('AVG(tax_exchange_rate) as avg_rate'), DB::raw('MAX(tax_exchange_rate) as max_rate'), DB::raw('MIN(tax_exchange_rate) as min_rate'))
            ->groupBy('exch_month')
            ->orderBy('exch_month')
            ->get();

        // ── Data Budget Forecast Rate (Bulanan 1-12) ──
        $rawBudgetForecasts = TaxBudgetForecastRate::ofYear($selectedYear)
            ->ofCurrency($selectedCurrency)
            ->get()
            ->keyBy('exch_month');

        $budgetForecastRecords = collect();
        $budgetChartValues = [];

        for ($m = 1; $m <= 12; $m++) {
            $bf = $rawBudgetForecasts->get($m);
            $budgetRate = $bf ? (int) $bf->budget_rate : 0;
            
            $mAvgObj = $monthlyData->firstWhere('exch_month', $m);
            $actualAvg = $mAvgObj ? (int) round($mAvgObj->avg_rate) : 0;
            
            $variance = ($actualAvg > 0 && $budgetRate > 0) ? ($actualAvg - $budgetRate) : 0;
            $variancePct = ($actualAvg > 0 && $budgetRate > 0) ? round(($variance / $budgetRate) * 100, 2) : 0;

            $item = (object) [
                'id'                => $bf?->id,
                'exch_year'         => $selectedYear,
                'exch_month'        => $m,
                'month_name'        => TaxBudgetForecastRate::$monthNames[$m] ?? "Bulan {$m}",
                'currency_code'     => $selectedCurrency,
                'budget_rate'       => $budgetRate,
                'budget_rate_fmt'   => number_format($budgetRate, 0, ',', '.'),
                'actual_avg_rate'   => $actualAvg,
                'actual_avg_fmt'    => number_format($actualAvg, 0, ',', '.'),
                'variance'          => $variance,
                'variance_fmt'      => ($variance > 0 ? '+' : '') . number_format($variance, 0, ',', '.'),
                'variance_pct'      => $variancePct,
                'remarks'           => $bf?->remarks,
                'last_update'       => $bf?->last_update?->format('d/m/Y') ?? '-',
                'last_user'         => $bf?->last_user ?? '-',
            ];

            $budgetForecastRecords->push($item);
            $budgetChartValues[] = $budgetRate;
        }

        $budgetAvgRate = round($budgetForecastRecords->where('budget_rate', '>', 0)->avg('budget_rate') ?? 0);
        $budgetHighest = $budgetForecastRecords->max('budget_rate') ?? 0;
        $budgetLowest  = $budgetForecastRecords->where('budget_rate', '>', 0)->min('budget_rate') ?? 0;

        // ── KPI Cards ──
        $latestRecord = ($selectedMonth !== 'all')
            ? (TaxExchangeRate::ofYear($selectedYear)
                ->ofMonth((int)$selectedMonth)
                ->ofCurrency($selectedCurrency)
                ->orderBy('week_code', 'desc')
                ->first() ?? $latestOverallRecord)
            : $latestOverallRecord;

        $monthlyAvg = ($selectedMonth !== 'all')
            ? $monthlyData->firstWhere('exch_month', (int)$selectedMonth)
            : (object)['avg_rate' => $monthlyData->avg('avg_rate')];

        $yearHighest = TaxExchangeRate::ofYear($selectedYear)
            ->ofCurrency($selectedCurrency)
            ->max('tax_exchange_rate');

        $yearLowest  = TaxExchangeRate::ofYear($selectedYear)
            ->ofCurrency($selectedCurrency)
            ->min('tax_exchange_rate');

        $monthlyHigh = ($selectedMonth !== 'all')
            ? TaxExchangeRate::ofYear($selectedYear)->ofMonth((int)$selectedMonth)->ofCurrency($selectedCurrency)->max('tax_exchange_rate')
            : $yearHighest;

        $monthlyLow = ($selectedMonth !== 'all')
            ? TaxExchangeRate::ofYear($selectedYear)->ofMonth((int)$selectedMonth)->ofCurrency($selectedCurrency)->min('tax_exchange_rate')
            : $yearLowest;

        // ── Semua Record Realisasi (tabel) dengan filter ──
        $allRecords = TaxExchangeRate::ofYear($selectedYear)
            ->ofCurrency($selectedCurrency)
            ->when($selectedMonth !== 'all', fn($q) => $q->ofMonth((int)$selectedMonth))
            ->orderBy('exch_month')
            ->orderBy('week_code')
            ->get();

        // ── Hitung trend minggu ini vs minggu lalu ──
        $trend = null;
        if ($weeklyData->count() >= 2) {
            $last   = $weeklyData->last()->tax_exchange_rate;
            $prev   = $weeklyData[$weeklyData->count() - 2]->tax_exchange_rate;
            $diff   = $last - $prev;
            $trend  = ['diff' => $diff, 'pct' => $prev > 0 ? round(($diff / $prev) * 100, 2) : 0, 'up' => $diff >= 0];
        }

        // ── Data Chart JSON ──
        $weeklyChartLabels = $weeklyData->map(function($r) use ($selectedMonth) {
            $monthShort = substr(TaxExchangeRate::$monthNames[$r->exch_month] ?? '', 0, 3);
            return $selectedMonth === 'all' ? "{$monthShort} W{$r->week_code}" : "Minggu {$r->week_code}";
        });
        $weeklyChartValues = $weeklyData->pluck('tax_exchange_rate');

        // Semua dataset chart harus memakai 12 label yang sama. Sebelumnya label
        // hanya berisi bulan yang sudah memiliki realisasi, sedangkan budget
        // selalu berisi 12 nilai. Ketidaksamaan indeks ini membuat garis
        // komparasi bergeser atau tidak dirender pada kondisi data parsial.
        $monthlyStatsByMonth = $monthlyData->keyBy('exch_month');
        $monthlyChartLabels = collect();
        $monthlyChartValues = collect();
        $monthlyChartMax    = collect();
        $monthlyChartMin    = collect();

        for ($month = 1; $month <= 12; $month++) {
            $stats = $monthlyStatsByMonth->get($month);
            $monthlyChartLabels->push(TaxExchangeRate::$monthNames[$month] ?? "Bln {$month}");
            $monthlyChartValues->push($stats ? (int) round($stats->avg_rate) : null);
            $monthlyChartMax->push($stats ? (int) $stats->max_rate : null);
            $monthlyChartMin->push($stats ? (int) $stats->min_rate : null);
        }

        return view('exchange-rate.index', compact(
            'selectedYear',
            'selectedMonth',
            'selectedCurrency',
            'weeklyData',
            'monthlyData',
            'allRecords',
            'latestRecord',
            'latestOverallRecord',
            'monthlyAvg',
            'yearHighest',
            'yearLowest',
            'monthlyHigh',
            'monthlyLow',
            'trend',
            'weeklyChartLabels',
            'weeklyChartValues',
            'monthlyChartLabels',
            'monthlyChartValues',
            'monthlyChartMax',
            'monthlyChartMin',
            'budgetForecastRecords',
            'budgetAvgRate',
            'budgetHighest',
            'budgetLowest',
            'budgetChartValues',
            'availableYears'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    //  STORE (Input Manual)
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exch_year'         => 'required|integer|min:2000|max:2099',
            'exch_month'        => 'required|integer|min:1|max:12',
            'week_code'         => 'required|integer|min:1|max:5',
            'currency_code'     => 'required|integer|in:1,2,3',
            'tax_exchange_rate' => 'required|integer|min:1',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'last_user'         => 'nullable|string|max:100',
        ]);

        // Cek duplikat
        $exists = TaxExchangeRate::where('exch_year',     $validated['exch_year'])
            ->where('exch_month',    $validated['exch_month'])
            ->where('week_code',     $validated['week_code'])
            ->where('currency_code', $validated['currency_code'])
            ->first();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', "Data kurs untuk Tahun {$validated['exch_year']}, Bulan {$validated['exch_month']}, Minggu {$validated['week_code']} sudah ada! Gunakan fitur Edit untuk memperbarui.");
        }

        $userLabel = trim($request->input('last_user')) ?: (Auth::user()->name ?? Auth::user()->username ?? 'System');

        TaxExchangeRate::create(array_merge($validated, [
            'last_update'   => now()->toDateString(),
            'last_user'     => $userLabel,
            'register_date' => now()->toDateString(),
            'user_id'       => Auth::id(),
        ]));

        return back()->with('success', "Kurs minggu {$validated['week_code']} bulan {$validated['exch_month']}/{$validated['exch_year']} berhasil disimpan oleh {$userLabel}!");
    }

    // ─────────────────────────────────────────────────────────────
    //  UPDATE
    // ─────────────────────────────────────────────────────────────

    public function update(Request $request, int $id)
    {
        $rate = TaxExchangeRate::findOrFail($id);

        $validated = $request->validate([
            'exch_year'         => 'required|integer|min:2000|max:2099',
            'exch_month'        => 'required|integer|min:1|max:12',
            'week_code'         => 'required|integer|min:1|max:5',
            'currency_code'     => 'required|integer|in:1,2,3',
            'tax_exchange_rate' => 'required|integer|min:1',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'last_user'         => 'nullable|string|max:100',
        ]);

        $duplicate = TaxExchangeRate::where('exch_year', $validated['exch_year'])
            ->where('exch_month', $validated['exch_month'])
            ->where('week_code', $validated['week_code'])
            ->where('currency_code', $validated['currency_code'])
            ->whereKeyNot($rate->getKey())
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with('error', 'Periode kurs tersebut sudah digunakan oleh record lain. Pilih tahun, bulan, minggu, atau mata uang yang berbeda.');
        }

        $userLabel = trim($request->input('last_user')) ?: (Auth::user()->name ?? Auth::user()->username ?? 'System');

        $rate->update(array_merge($validated, [
            'last_update' => now()->toDateString(),
            'last_user'   => $userLabel,
        ]));

        return back()->with('success', "Data kurs berhasil diperbarui (User: {$userLabel})!");
    }

    // ─────────────────────────────────────────────────────────────
    //  DESTROY
    // ─────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        $rate = TaxExchangeRate::findOrFail($id);
        $rate->delete();
        return back()->with('success', "Data kurs berhasil dihapus.");
    }

    public function destroyBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $decoded = json_decode($ids, true);
            if (is_array($decoded)) {
                $ids = $decoded;
            } else {
                $ids = array_filter(explode(',', $ids));
            }
        }

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $ids = collect($ids)
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(1000)
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Pilihan data kurs tidak valid.');
        }

        $count = TaxExchangeRate::whereIn('id', $ids)->delete();
        return back()->with('success', "{$count} data kurs berhasil dihapus.");
    }

    // ─────────────────────────────────────────────────────────────
    //  IMPORT EXCEL
    // ─────────────────────────────────────────────────────────────

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,xlsx,xls,txt|max:51200',
        ]);

        $file      = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = in_array($extension, ['xlsx', 'xls'])
            ? $this->parseExcelFile($file->getPathname())
            : $this->iterateCsvFile($file->getPathname());

        $saved   = 0;
        $skipped = 0;
        $errors  = [];

        $batch = [];
        $hasRows = false;

        foreach ($rows as $i => $row) {
            try {
                $hasRows = true;
                // Skip header row
                if (isset($row[0]) && strtolower(trim(ltrim((string) $row[0], "\xEF\xBB\xBF"))) === 'exch_year') continue;
                if (count($row) < 5) continue;

                $exchYear   = (int) trim($row[0] ?? 0);
                $exchMonth  = (int) trim($row[1] ?? 0);
                $weekCode   = (int) trim($row[2] ?? 0);
                $currCode   = (int) trim($row[3] ?? 2);
                $rate       = (int) trim($row[4] ?? 0);
                $startRaw   = trim($row[5] ?? '');
                $endRaw     = trim($row[6] ?? '');
                $lastUpd    = trim($row[7] ?? '');
                $lastUsr    = trim($row[8] ?? (Auth::user()->name ?? 'Import'));
                $regDate    = trim($row[9] ?? '');

                if ($exchYear < 2000 || $exchMonth < 1 || $exchMonth > 12 || $weekCode < 1 || $weekCode > 5 || !in_array($currCode, [1, 2, 3], true) || $rate < 1) {
                    $errors[] = "Baris " . ($i + 2) . ": nilai tidak valid (year={$exchYear}, month={$exchMonth}, week={$weekCode}, rate={$rate})";
                    $skipped++;
                    continue;
                }

                // Parse tanggal format YYYYMMDD
                $startDate = $this->parseYMDDate($startRaw);
                $endDate   = $this->parseYMDDate($endRaw);
                $lastUpdate  = $this->parseYMDDate($lastUpd) ?? now()->toDateString();
                $registerDate = $this->parseYMDDate($regDate) ?? now()->toDateString();

                if (($startRaw !== '' && !$startDate) || ($endRaw !== '' && !$endDate) || ($startDate && $endDate && $endDate < $startDate)) {
                    $errors[] = "Baris " . ($i + 2) . ': tanggal tidak valid atau tanggal selesai lebih awal dari tanggal mulai.';
                    $skipped++;
                    continue;
                }

                $now = now();
                $batch[] = [
                    'exch_year'         => $exchYear,
                    'exch_month'        => $exchMonth,
                    'week_code'         => $weekCode,
                    'currency_code'     => $currCode,
                    'tax_exchange_rate' => $rate,
                    'start_date'        => $startDate,
                    'end_date'          => $endDate,
                    'last_update'       => $lastUpdate,
                    'last_user'         => $lastUsr,
                    'register_date'     => $registerDate,
                    'user_id'           => Auth::id(),
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
                $saved++;

                if (count($batch) >= 1000) {
                    $this->upsertExchangeRateBatch($batch);
                    $batch = [];
                }
            } catch (\Throwable $e) {
                Log::warning("ExchangeRate import error baris " . ($i + 2) . ": " . $e->getMessage());
                $errors[] = "Baris " . ($i + 2) . ": " . $e->getMessage();
                $skipped++;
            }
        }

        if (!$hasRows) {
            return back()->with('error', 'File kosong atau format tidak dikenali.');
        }

        if ($batch) {
            $this->upsertExchangeRateBatch($batch);
        }

        $msg = "{$saved} data kurs berhasil diimpor.";
        if ($skipped > 0) $msg .= " {$skipped} baris dilewati.";

        return back()->with($skipped > 0 && $saved === 0 ? 'error' : 'success', $msg)
                     ->with('import_errors', $errors);
    }

    // ─────────────────────────────────────────────────────────────
    //  DOWNLOAD TEMPLATE
    // ─────────────────────────────────────────────────────────────

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_tax_exchange_rate.csv"',
        ];

        $columns = ['Exch_Year','Exch_Month','Week_Code','Currency_Code','Tax_ExchangeRate','Start_Date','End_Date','Last_Update','Last_User','Register_Date'];
        $example = [date('Y'), date('n'), 1, 2, 16777, date('Ymd'), date('Ymd', strtotime('+6 days')), date('Ymd'), Auth::user()->name ?? 'User', date('Ymd')];

        $callback = function () use ($columns, $example) {
            $out = fopen('php://output', 'w');
            // BOM untuk Excel agar UTF-8 terbaca benar
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, $columns);
            fputcsv($out, $example);
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────
    //  BUDGET FORECAST RATE (Single Month & Bulk Update)
    // ─────────────────────────────────────────────────────────────

    public function storeBudgetForecast(Request $request)
    {
        $validated = $request->validate([
            'exch_year'     => 'required|integer|min:2000|max:2099',
            'exch_month'    => 'required|integer|min:1|max:12',
            'currency_code' => 'required|integer|min:1',
            'budget_rate'   => 'required|integer|min:1',
            'remarks'       => 'nullable|string|max:255',
            'last_user'     => 'nullable|string|max:100',
        ]);

        $userLabel = trim($request->input('last_user')) ?: (Auth::user()->name ?? Auth::user()->username ?? 'System');

        TaxBudgetForecastRate::updateOrCreate(
            [
                'exch_year'     => $validated['exch_year'],
                'exch_month'    => $validated['exch_month'],
                'currency_code' => $validated['currency_code'],
            ],
            [
                'budget_rate' => $validated['budget_rate'],
                'remarks'     => $validated['remarks'] ?? null,
                'last_update' => now()->toDateString(),
                'last_user'   => $userLabel,
                'user_id'     => Auth::id(),
            ]
        );

        $monthName = TaxBudgetForecastRate::$monthNames[$validated['exch_month']] ?? "Bulan {$validated['exch_month']}";

        return back()->with('success', "Budget Forecast Kurs untuk {$monthName} {$validated['exch_year']} berhasil disimpan (Rp " . number_format($validated['budget_rate'], 0, ',', '.') . ")!");
    }

    public function updateBudgetForecastBulk(Request $request)
    {
        $validated = $request->validate([
            'exch_year'     => 'required|integer|min:2000|max:2099',
            'currency_code' => 'required|integer|min:1',
            'rates'         => 'required|array',
            'rates.*'       => 'nullable|numeric|min:0',
            'last_user'     => 'nullable|string|max:100',
        ]);

        $userLabel = trim($request->input('last_user')) ?: (Auth::user()->name ?? Auth::user()->username ?? 'System');
        $savedCount = 0;

        foreach ($validated['rates'] as $month => $rateVal) {
            if (!filter_var($month, FILTER_VALIDATE_INT) || (int) $month < 1 || (int) $month > 12) {
                continue;
            }
            $rate = (int) $rateVal;
            if ($rate <= 0) continue;

            TaxBudgetForecastRate::updateOrCreate(
                [
                    'exch_year'     => $validated['exch_year'],
                    'exch_month'    => (int) $month,
                    'currency_code' => $validated['currency_code'],
                ],
                [
                    'budget_rate' => $rate,
                    'last_update' => now()->toDateString(),
                    'last_user'   => $userLabel,
                    'user_id'     => Auth::id(),
                ]
            );
            $savedCount++;
        }

        return back()->with('success', "{$savedCount} Bulan Budget Forecast Kurs Tahun {$validated['exch_year']} berhasil diperbarui oleh {$userLabel}!");
    }

    // ─────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function parseCsvFile(string $path): array
    {
        $rows = [];
        foreach ($this->iterateCsvFile($path) as $row) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Membaca CSV secara streaming agar import puluhan hingga ratusan ribu
     * baris tidak menyimpan seluruh file di memori PHP sekaligus.
     */
    private function iterateCsvFile(string $path): \Generator
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return;
        }

        try {
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                // Template lama kadang menggunakan pemisah titik koma.
                if (count($data) < 5) {
                    $data = str_getcsv(implode(',', $data), ';');
                }
                yield $data;
            }
        } finally {
            fclose($handle);
        }
    }

    /** Jalankan upsert dalam batch agar import tetap cepat pada dataset besar. */
    private function upsertExchangeRateBatch(array $rows): void
    {
        TaxExchangeRate::upsert(
            $rows,
            ['exch_year', 'exch_month', 'week_code', 'currency_code'],
            [
                'tax_exchange_rate',
                'start_date',
                'end_date',
                'last_update',
                'last_user',
                'register_date',
                'user_id',
                'updated_at',
            ]
        );
    }

    private function parseExcelFile(string $path): array
    {
        // Gunakan PhpSpreadsheet bila terinstall, fallback ke CSV parse
        if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $sheet       = $spreadsheet->getActiveSheet();
                $rows        = [];
                foreach ($sheet->toArray() as $row) {
                    $rows[] = array_values($row);
                }
                return $rows;
            } catch (\Throwable $e) {
                Log::warning('PhpSpreadsheet failed: ' . $e->getMessage());
            }
        }
        // Fallback: coba baca sebagai CSV
        return $this->parseCsvFile($path);
    }

    /**
     * Konversi string YYYYMMDD atau Y-m-d ke format Y-m-d
     */
    private function parseYMDDate(?string $raw): ?string
    {
        if (empty($raw)) return null;
        $raw = trim($raw);

        // Format YYYYMMDD (tanpa strip), dengan validasi kalender yang ketat.
        if (preg_match('/^\d{8}$/', $raw)) {
            $year = (int) substr($raw, 0, 4);
            $month = (int) substr($raw, 4, 2);
            $day = (int) substr($raw, 6, 2);
            return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : null;
        }

        foreach (['Y-m-d', 'Y/m/d', 'd/m/Y', 'd-m-Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $raw);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }

        // Serial date Excel (mis. 45500), umum terjadi pada XLSX yang belum
        // diformat sebagai teks oleh pengguna.
        if (preg_match('/^\d{5}(?:\.0+)?$/', $raw) && class_exists('\PhpOffice\PhpSpreadsheet\Shared\Date')) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            $timestamp = strtotime($raw);
            return $timestamp === false ? null : date('Y-m-d', $timestamp);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
