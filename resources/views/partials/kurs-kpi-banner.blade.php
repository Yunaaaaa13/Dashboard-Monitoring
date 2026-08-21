@php
    /**
     * Partial: KPI Kurs USD/IDR Terkini (Real-Time Active Rate)
     *
     * Otomatis mendeteksi Kurs Pajak (KMK/Tax Exchange Rate) USD/IDR yang AKTIF HARI INI
     * berdasarkan range tanggal (start_date <= hari_ini <= end_date).
     * Jika hari Rabu depan ada perubahan kurs mingguan baru, KPI ini akan otomatis
     * berpindah dan menampilkan kurs Rupiah yang baru secara real-time.
     */

    $now          = \Carbon\Carbon::now('Asia/Jakarta');
    $todayDate    = $now->format('Y-m-d');
    $currentMonth = (int) $now->month;
    $currentYear  = (int) $now->year;
    $currencyCode = 2; // USD/IDR

    // 1. Cek Kurs Mingguan yang AKTIF HARI INI (sesuai range tanggal start_date s/d end_date)
    $activeWeeklyRate = \App\Models\TaxExchangeRate::where('currency_code', $currencyCode)
        ->whereDate('start_date', '<=', $todayDate)
        ->whereDate('end_date', '>=', $todayDate)
        ->first();

    // 2. Fallback: jika range tanggal belum diisi, ambil record minggu terbaru di bulan/tahun ini
    if (!$activeWeeklyRate) {
        $activeWeeklyRate = \App\Models\TaxExchangeRate::where('currency_code', $currencyCode)
            ->where('exch_year', $currentYear)
            ->where('exch_month', $currentMonth)
            ->orderByDesc('week_code')
            ->first();
    }

    // 3. Fallback kedua: ambil record paling akhir berdasarkan ID/end_date
    if (!$activeWeeklyRate) {
        $activeWeeklyRate = \App\Models\TaxExchangeRate::where('currency_code', $currencyCode)
            ->orderByDesc('end_date')
            ->orderByDesc('id')
            ->first();
    }

    // ── Kurs Budget Forecast Bulan Ini ──
    $budgetForecastRate = \App\Models\TaxBudgetForecastRate::where('currency_code', $currencyCode)
        ->where('exch_year', $currentYear)
        ->where('exch_month', $currentMonth)
        ->first();

    if (!$budgetForecastRate) {
        $budgetForecastRate = \App\Models\TaxBudgetForecastRate::where('currency_code', $currencyCode)
            ->where('exch_year', $currentYear)
            ->orderByDesc('exch_month')
            ->first();
    }

    $currentRate   = $activeWeeklyRate ? (int) $activeWeeklyRate->tax_exchange_rate : 16500;
    $budgetRate    = $budgetForecastRate ? (int) $budgetForecastRate->budget_rate : 16500;
    $weekCode      = $activeWeeklyRate ? $activeWeeklyRate->week_code : null;
    $rateMonthName = $activeWeeklyRate
        ? (\App\Models\TaxExchangeRate::$monthNames[$activeWeeklyRate->exch_month] ?? 'Bulan ?')
        : 'N/A';
    
    // Format rentang tanggal aktif jika tersedia
    $dateRangeText = '';
    if ($activeWeeklyRate && $activeWeeklyRate->start_date && $activeWeeklyRate->end_date) {
        $dateRangeText = $activeWeeklyRate->start_date->format('d/m') . ' - ' . $activeWeeklyRate->end_date->format('d/m/Y');
    }

    $diffRate      = $currentRate - $budgetRate;
    $diffPct       = $budgetRate > 0 ? round(($diffRate / $budgetRate) * 100, 2) : 0;
    $isOverBudget  = $diffRate > 0;
    $isUnderBudget = $diffRate < 0;

    // Warna indikator
    $rateColorClass  = $isOverBudget ? 'text-warning' : ($isUnderBudget ? 'text-success' : 'text-info');
    $rateBadgeColor  = $isOverBudget ? 'rgba(245,158,11,0.18)' : ($isUnderBudget ? 'rgba(16,185,129,0.18)' : 'rgba(59,130,246,0.18)');
    $rateBorderColor = $isOverBudget ? 'rgba(245,158,11,0.4)' : ($isUnderBudget ? 'rgba(16,185,129,0.4)' : 'rgba(59,130,246,0.4)');
    $rateArrow       = $isOverBudget ? '↑' : ($isUnderBudget ? '↓' : '→');
    $diffLabel       = $isOverBudget ? 'Di Atas Budget' : ($isUnderBudget ? 'Di Bawah Budget' : 'Sesuai Budget');
@endphp

{{-- KPI Kurs Mini Banner Terkini --}}
<div class="d-flex align-items-center gap-2 flex-wrap ms-auto"
     style="background: rgba(10,14,23,0.75); border: 1px solid {{ $rateBorderColor }}; border-radius: 12px; padding: 6px 14px; backdrop-filter: blur(10px); box-shadow: 0 4px 15px rgba(0,0,0,0.3);"
     title="Kurs Pajak USD/IDR Terkini (Otomatis terkonversi & ter-update tiap minggu di hari Rabu)">

    {{-- Icon & Label --}}
    <div class="d-flex align-items-center gap-1.5" style="color: #94a3b8; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
        <i class="bi bi-currency-exchange text-warning" style="font-size: 1rem;"></i>
        <div class="d-flex flex-column" style="line-height:1.2;">
            <span class="text-white fw-bold">KURS USD/IDR</span>
            <span style="font-size: 0.62rem; color: #64748b;">
                {{ $dateRangeText ? $dateRangeText : 'Aktual Hari Ini' }}
            </span>
        </div>
    </div>

    {{-- Divider --}}
    <div style="width: 1px; height: 24px; background: rgba(255,255,255,0.14);"></div>

    {{-- Budget Forecast Rate --}}
    <div class="d-flex flex-column align-items-center" style="min-width: 75px;">
        <span style="font-size: 0.62rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px;">BUDGET</span>
        <span style="font-family: 'Outfit', sans-serif; font-size: 0.92rem; font-weight: 800; color: #60a5fa;">
            Rp {{ number_format($budgetRate, 0, ',', '.') }}
        </span>
    </div>

    {{-- Arrow Indicator --}}
    <div class="{{ $rateColorClass }}" style="font-size: 1.1rem; font-weight: 900; line-height: 1;">
        {{ $rateArrow }}
    </div>

    {{-- Actual / Realisasi Rate Terkini --}}
    <div class="d-flex flex-column align-items-center" style="min-width: 95px;">
        <span style="font-size: 0.62rem; color: #34d399; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px;">
            <i class="bi bi-clock-history me-0.5"></i> TERKINI {{ $weekCode ? 'WK-'.$weekCode : '' }}
        </span>
        <span class="{{ $rateColorClass }}" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 800;">
            Rp {{ number_format($currentRate, 0, ',', '.') }}
        </span>
    </div>

    {{-- Divider --}}
    <div style="width: 1px; height: 24px; background: rgba(255,255,255,0.14);"></div>

    {{-- Selisih --}}
    <div class="d-flex flex-column align-items-center">
        <span style="font-size: 0.62rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px;">SELISIH</span>
        <span class="{{ $rateColorClass }}" style="font-family: 'Outfit', sans-serif; font-size: 0.82rem; font-weight: 700;">
            {{ $diffRate > 0 ? '+' : '' }}{{ number_format($diffRate, 0, ',', '.') }}
            <span style="font-size: 0.65rem; opacity: 0.85;">({{ $diffPct > 0 ? '+' : '' }}{{ $diffPct }}%)</span>
        </span>
    </div>

    {{-- Status Badge --}}
    <span style="background: {{ $rateBadgeColor }}; border: 1px solid {{ $rateBorderColor }}; border-radius: 20px; padding: 3px 10px; font-size: 0.68rem; font-weight: 700; color: {{ $isOverBudget ? '#fbbf24' : ($isUnderBudget ? '#34d399' : '#60a5fa') }};">
        {{ $diffLabel }}
    </span>

    {{-- Link ke Manajemen Kurs --}}
    @if(Route::has('tax.exchange-rates'))
    <a href="{{ route('tax.exchange-rates') }}" class="d-flex align-items-center ms-1"
       style="color: #94a3b8; font-size: 0.75rem; text-decoration: none; padding: 3px 7px; border-radius: 6px; background: rgba(255,255,255,0.05); transition: all 0.2s;"
       title="Kelola & Update Kurs Pajak Mingguan"
       onmouseover="this.style.color='#fff'; this.style.background='rgba(59,130,246,0.3)'"
       onmouseout="this.style.color='#94a3b8'; this.style.background='rgba(255,255,255,0.05)'">
        <i class="bi bi-box-arrow-up-right me-1"></i> Kurs
    </a>
    @endif
</div>
