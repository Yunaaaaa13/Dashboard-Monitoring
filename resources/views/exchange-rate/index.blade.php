<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Exchange Rate | PT Kawai Indonesia</title>
    <meta name="description" content="Dashboard monitoring kurs pajak rupiah mingguan dan bulanan PT Kawai Indonesia">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">

    <style>
        :root {
            --bg-primary:        #0a0e17;
            --bg-secondary:      #121826;
            --card-bg:           rgba(23, 31, 48, 0.75);
            --card-border:       rgba(255, 255, 255, 0.08);
            --accent-gold:       #e2b34a;
            --accent-gold-glow:  rgba(226, 179, 74, 0.25);
            --accent-cyan:       #00d2ff;
            --accent-emerald:    #10b981;
            --accent-amber:      #f59e0b;
            --accent-purple:     #a855f7;
            --text-main:         #f3f4f6;
            --text-muted:        #9ca3af;
            --kurs-up:           #34d399;
            --kurs-down:         #f87171;
        }
        body {
            background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }
        h1,h2,h3,h4,h5,.brand-font { font-family: 'Outfit', sans-serif; }

        /* ── TOP NAVBAR ── */
        .top-navbar {
            background: rgba(18, 24, 38, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--card-border);
            padding: 0.75rem 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .top-navbar-row1 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .top-navbar-row2 {
            border-top: 1px solid rgba(255,255,255,0.06);
            padding-top: 0.5rem;
        }
        .brand-logo-text {
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 0.04em;
            background: linear-gradient(135deg, #ffffff 0%, var(--accent-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .live-indicator {
            display: inline-block;
            width: 8px; height: 8px;
            background-color: var(--accent-emerald);
            border-radius: 50%;
            margin-right: 6px;
        }

        /* ── GLASS CARD ── */
        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            backdrop-filter: blur(12px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }
        .glass-card-static {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            backdrop-filter: blur(12px);
        }

        /* ── KPI CARDS ── */
        .kpi-title {
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.4rem;
        }
        .kpi-value {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.25rem;
        }
        .kpi-icon {
            width: 50px; height: 50px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        /* ── HERO BANNER ── */
        .exchange-hero {
            background: rgba(226,179,74,0.08);
            border: 1px solid rgba(226,179,74,0.25);
            border-radius: 20px;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .hero-rate-display {
            font-family: 'Outfit', monospace;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: #fbbf24;
            line-height: 1;
        }
        .hero-rate-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* ── TREND BADGE ── */
        .trend-up   { background: rgba(52,211,153,0.18); color: #34d399; border: 1px solid rgba(52,211,153,0.45); }
        .trend-down { background: rgba(248,113,113,0.18); color: #f87171; border: 1px solid rgba(248,113,113,0.45); }
        .trend-flat { background: rgba(156,163,175,0.18); color: #9ca3af; border: 1px solid rgba(156,163,175,0.35); }

        /* ── TABLE ── */
        .table-dark-custom {
            --bs-table-bg: transparent !important;
            --bs-table-color: var(--text-main) !important;
            --bs-table-border-color: rgba(255,255,255,0.08) !important;
            --bs-table-hover-bg: rgba(255,255,255,0.04) !important;
            color: var(--text-main) !important;
            background-color: transparent !important;
            margin-bottom: 0;
        }
        .table-dark-custom thead th {
            background: rgba(18,24,38,0.85) !important;
            color: #d1d5db !important;
            font-size: 0.73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            border-bottom: 1px solid var(--card-border) !important;
            padding: 0.85rem 1rem;
            white-space: nowrap;
        }
        .table-dark-custom tbody tr td {
            background-color: transparent !important;
            color: var(--text-main) !important;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
            vertical-align: middle;
            font-size: 0.88rem;
        }
        .table-dark-custom tbody tr:hover td {
            background: rgba(255,255,255,0.04) !important;
        }

        /* ── FORM ── */
        .form-control-dark, .form-select-dark {
            background: rgba(10,14,23,0.88);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 10px;
            font-size: 0.88rem;
        }
        .form-control-dark:focus, .form-select-dark:focus {
            background: rgba(10,14,23,0.95);
            color: #fff;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.15rem rgba(226,179,74,0.1);
            outline: none;
        }
        .form-control-dark::placeholder { color: #6b7280; }
        .form-label { color: #b0b8c4; font-size: 0.83rem; font-weight: 600; margin-bottom: 0.35rem; }

        /* ── BADGE WEEK ── */
        .week-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 50%;
            font-weight: 800; font-size: 0.82rem;
        }

        /* ── CHART WRAPPER ── */
        .chart-wrapper {
            position: relative;
            height: 260px;
        }

        /* ── TABS ── */
        .er-tab-btn {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            color: #9ca3af;
            border-radius: 10px;
            padding: 0.5rem 1.25rem;
            font-size: 0.83rem;
            font-weight: 600;
            transition: all 0.2s;
            cursor: pointer;
        }
        .er-tab-btn.active, .er-tab-btn:hover {
            background: rgba(226,179,74,0.15);
            border-color: rgba(226,179,74,0.5);
            color: var(--accent-gold);
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,0.04); }
        ::-webkit-scrollbar-thumb { background: rgba(226,179,74,0.3); border-radius: 3px; }

        /* ── RESPONSIVE TABLE WRAP ── */
        .table-responsive { border-radius: 12px; overflow: hidden; }

        /* ── MONTH FILTER PILLS ── */
        .month-pill {
            padding: 4px 14px; border-radius: 999px; font-size: 0.78rem;
            font-weight: 600; border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.04); color: #9ca3af;
            cursor: pointer; transition: all 0.2s; white-space: nowrap;
            text-decoration: none;
        }
        .month-pill:hover, .month-pill.active {
            background: rgba(226,179,74,0.18);
            border-color: rgba(226,179,74,0.5);
            color: var(--accent-gold);
        }

        .text-muted { color: #b0b8c4 !important; }

        /* SweetAlert dark */
        .swal2-popup {
            background: #121826 !important;
            color: #f3f4f6 !important;
            border: 1px solid rgba(255,255,255,0.12) !important;
            border-radius: 16px !important;
        }
        .swal2-title { color: #f3f4f6 !important; }
        .swal2-html-container { color: #9ca3af !important; }
    </style>
</head>
<body>

{{-- ══ TOP NAVBAR ══ --}}
<nav class="top-navbar">
    <div class="top-navbar-row1 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
        <a href="{{ route('dashboard.overview') }}" class="text-decoration-none" style="color: inherit !important; text-decoration: none !important;">
            <div class="d-flex align-items-center gap-2 mb-0.5">
                <i class="bi bi-music-note-beamed text-warning fs-4" style="line-height: 1; vertical-align: middle;"></i>
                <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
            </div>
            <div class="text-muted" style="font-size:0.72rem; margin-left:2px; color: #9ca3af !important;">Dashboard Kurs Pajak</div>
        </a>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{-- Filter Tahun --}}
            <form action="{{ route('exchange-rate.index') }}" method="GET" class="d-flex align-items-center gap-2" id="filterForm">
                <select name="year" class="form-select-dark form-select-sm" onchange="this.form.submit()" style="border-radius:8px; padding:0.38rem 1.8rem 0.38rem 0.7rem;">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endforeach
                </select>
                <select name="currency" class="form-select-dark form-select-sm" onchange="this.form.submit()" style="border-radius:8px; padding:0.38rem 1.8rem 0.38rem 0.7rem;">
                    @foreach(\App\Models\TaxExchangeRate::$currencyMap as $code => $curr)
                        <option value="{{ $code }}" {{ $selectedCurrency == $code ? 'selected' : '' }}>{{ $curr['label'] }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
            </form>

            @auth
            <div class="user-profile-pill">
                <i class="fa-solid fa-user-shield user-pill-icon"></i>
                <span class="user-pill-name">{{ Auth::user()->name }}</span>
                @php
                    $roleColors = [
                        'supervisor' => 'bg-danger text-white',
                        'leader'     => 'bg-info text-dark',
                        'staff'      => 'bg-success text-white'
                    ];
                @endphp
                <span class="badge {{ $roleColors[Auth::user()->role] ?? 'bg-secondary text-white' }} user-pill-role">
                    {{ Auth::user()->role }}
                </span>
                <form action="{{ route('logout') }}" method="POST" class="user-pill-logout-form">
                    @csrf
                    <button type="submit" class="user-pill-logout-btn" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
            @endauth

            <div class="px-3 py-1.5 rounded-pill d-flex align-items-center gap-2" style="background:rgba(255,255,255,0.05); border:1px solid var(--card-border); font-size:0.84rem;">
                <span class="live-indicator"></span>
                <span id="live-clock" class="fw-bold font-monospace" style="letter-spacing:0.04em;">00:00:00</span>
            </div>
        </div>
    </div>
    <div class="top-navbar-row2">
        @include('partials.pill-nav', ['activeRoute' => 'exchange-rate.index', 'hasFaqModal' => true])
    </div>
</nav>

@include('partials.faq-modal')

{{-- ══ NOTIFICATION ══ --}}
<div class="container-fluid px-4 pt-3">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show glass-card-static p-3 mb-3 border-success border-opacity-50" role="alert">
            <i class="bi bi-check-circle-fill text-success me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show glass-card-static p-3 mb-3 border-danger border-opacity-50" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>

{{-- ══ MAIN CONTENT ══ --}}
<div class="container-fluid px-4 pb-5">

    {{-- ── HERO BANNER ── --}}
    <div class="exchange-hero">
        <div class="row align-items-center">
            <div class="col-12 col-xl-6">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:rgba(226,179,74,0.18);border:1px solid rgba(226,179,74,0.45);color:#e2b34a;">
                        <i class="bi bi-currency-exchange fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="hero-rate-label">KURS PAJAK TERKINI (LAST UPLOAD)</span>
                            <span class="badge rounded-pill bg-warning text-dark fw-bold" style="font-size:0.68rem; padding: 2px 8px;">LATEST</span>
                        </div>
                        @if($latestOverallRecord)
                        <div class="hero-rate-display">
                            Rp {{ number_format($latestOverallRecord->tax_exchange_rate, 0, ',', '.') }}
                        </div>
                        @else
                        <div class="hero-rate-display" style="font-size:2rem; opacity:0.5;">— Belum ada data —</div>
                        @endif
                    </div>
                </div>

                @if($latestOverallRecord)
                <div class="d-flex align-items-center gap-2.5 flex-wrap mt-2.5">
                    {{-- Periode --}}
                    <span class="px-3 py-1 rounded-pill fw-semibold" style="font-size:0.8rem; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15);">
                        <i class="bi bi-calendar3 me-1 text-warning"></i>
                        Minggu {{ $latestOverallRecord->week_code }} — {{ \App\Models\TaxExchangeRate::$monthNames[$latestOverallRecord->exch_month] ?? '-' }} {{ $latestOverallRecord->exch_year }}
                    </span>

                    {{-- Tanggal --}}
                    @if($latestOverallRecord->start_date && $latestOverallRecord->end_date)
                    <span class="px-3 py-1 rounded-pill" style="font-size:0.8rem; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:#9ca3af;">
                        <i class="bi bi-clock me-1"></i>
                        {{ $latestOverallRecord->start_date->format('d M') }} – {{ $latestOverallRecord->end_date->format('d M Y') }}
                    </span>
                    @endif

                    {{-- Pengupload / Last User --}}
                    <span class="px-3 py-1 rounded-pill fw-bold" style="font-size:0.8rem; background:rgba(226,179,74,0.18); border:1px solid rgba(226,179,74,0.45); color:#fbbf24;">
                        <i class="bi bi-person-circle me-1 text-warning"></i>
                        Uploaded by: <strong>{{ $latestOverallRecord->last_user ?? 'System' }}</strong>
                    </span>

                    @if($trend)
                    <span class="px-3 py-1 rounded-pill fw-bold d-inline-flex align-items-center gap-1" style="font-size:0.8rem; {{ $trend['up'] ? 'background:rgba(52,211,153,0.18);color:#34d399;border:1px solid rgba(52,211,153,0.45);' : 'background:rgba(248,113,113,0.18);color:#f87171;border:1px solid rgba(248,113,113,0.45);' }}">
                        <i class="bi {{ $trend['up'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ $trend['up'] ? '+' : '' }}{{ number_format($trend['diff'], 0, ',', '.') }}
                        ({{ $trend['up'] ? '+' : '' }}{{ $trend['pct'] }}%)
                    </span>
                    @endif
                </div>
                @endif
            </div>
            <div class="col-12 col-xl-6 mt-3 mt-xl-0">
                <div class="d-flex align-items-center justify-content-start justify-content-xl-end gap-2 flex-wrap">
                    {{-- Quick action buttons --}}
                    <button class="btn btn-warning fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalInputManual" style="font-size:0.85rem; white-space:nowrap;">
                        <i class="bi bi-plus-lg"></i> Input Kurs Mingguan
                    </button>
                    <button class="btn btn-outline-info fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBulkBudgetForecast" style="font-size:0.85rem; white-space:nowrap;">
                        <i class="bi bi-pie-chart-fill"></i> Budget Forecast
                    </button>
                    <button class="btn btn-outline-light fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportExcel" style="font-size:0.85rem; white-space:nowrap;">
                        <i class="bi bi-file-earmark-excel"></i> Import
                    </button>
                    <a href="{{ route('exchange-rate.template') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 d-inline-flex align-items-center gap-2 rounded-pill shadow-sm" style="font-size:0.85rem; white-space:nowrap;" title="Download Template CSV">
                        <i class="bi bi-download"></i> Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 4 KPI CARDS ── --}}
    <div class="row g-3 mb-4">
        {{-- 1. Kurs Terkini --}}
        <div class="col-6 col-xl-3">
            <div class="glass-card h-100 p-3 d-flex justify-content-between align-items-start">
                <div style="min-width:0; flex:1;">
                    <div class="kpi-title">KURS MINGGU INI</div>
                    <div class="kpi-value text-warning">
                        {{ $latestRecord ? number_format($latestRecord->tax_exchange_rate, 0, ',', '.') : '—' }}
                    </div>
                    <div class="text-muted" style="font-size:0.78rem;">Rp / 1 USD &nbsp;•&nbsp; Minggu {{ $latestRecord->week_code ?? '-' }}</div>
                </div>
                <div class="kpi-icon flex-shrink-0" style="background:rgba(226,179,74,0.18);border:1px solid rgba(226,179,74,0.4);color:#e2b34a;">
                    <i class="bi bi-currency-exchange"></i>
                </div>
            </div>
        </div>

        {{-- 2. Rata-rata Bulanan --}}
        <div class="col-6 col-xl-3">
            <div class="glass-card h-100 p-3 d-flex justify-content-between align-items-start">
                <div style="min-width:0; flex:1;">
                    <div class="kpi-title">RATA-RATA BULAN INI</div>
                    <div class="kpi-value text-info">
                        {{ $monthlyAvg ? number_format(round($monthlyAvg->avg_rate), 0, ',', '.') : '—' }}
                    </div>
                    <div class="text-muted" style="font-size:0.78rem;">{{ \App\Models\TaxExchangeRate::$monthNames[$selectedMonth] ?? 'Bln '.$selectedMonth }} {{ $selectedYear }}</div>
                </div>
                <div class="kpi-icon flex-shrink-0" style="background:rgba(0,210,255,0.15);border:1px solid rgba(0,210,255,0.4);color:#00d2ff;">
                    <i class="bi bi-bar-chart-line-fill"></i>
                </div>
            </div>
        </div>

        {{-- 3. Tertinggi Tahun Ini --}}
        <div class="col-6 col-xl-3">
            <div class="glass-card h-100 p-3 d-flex justify-content-between align-items-start">
                <div style="min-width:0; flex:1;">
                    <div class="kpi-title">TERTINGGI {{ $selectedYear }}</div>
                    <div class="kpi-value" style="color:#f87171;">
                        {{ $yearHighest ? number_format($yearHighest, 0, ',', '.') : '—' }}
                    </div>

                </div>
                <div class="kpi-icon flex-shrink-0" style="background:rgba(248,113,113,0.15);border:1px solid rgba(248,113,113,0.4);color:#f87171;">
                    <i class="bi bi-arrow-up-circle-fill"></i>
                </div>
            </div>
        </div>

        {{-- 4. Terendah Tahun Ini --}}
        <div class="col-6 col-xl-3">
            <div class="glass-card h-100 p-3 d-flex justify-content-between align-items-start">
                <div style="min-width:0; flex:1;">
                    <div class="kpi-title">TERENDAH {{ $selectedYear }}</div>
                    <div class="kpi-value" style="color:#34d399;">
                        {{ $yearLowest ? number_format($yearLowest, 0, ',', '.') : '—' }}
                    </div>

                </div>
                <div class="kpi-icon flex-shrink-0" style="background:rgba(52,211,153,0.15);border:1px solid rgba(52,211,153,0.4);color:#34d399;">
                    <i class="bi bi-arrow-down-circle-fill"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ── CHARTS ROW ── --}}
    <div class="row g-4 mb-4">

        {{-- Chart Bulanan (kiri) --}}
        <div class="col-12 col-lg-7">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0" style="font-size:1rem;">
                            <i class="bi bi-graph-up text-warning me-2"></i>Tren Kurs Bulanan — {{ $selectedYear }}
                        </h5>

                    </div>
                    <span class="badge rounded-pill px-3 py-1.5" style="background:rgba(226,179,74,0.18);color:#e2b34a;border:1px solid rgba(226,179,74,0.4); font-size:0.75rem;">
                        {{ \App\Models\TaxExchangeRate::$currencyMap[$selectedCurrency]['label'] ?? 'USD/IDR' }}
                    </span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="chartMonthly"></canvas>
                </div>
            </div>
        </div>

        {{-- Chart Mingguan (kanan) --}}
        <div class="col-12 col-lg-5">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0" style="font-size:1rem;">
                            <i class="bi bi-calendar-week text-info me-2"></i>Kurs Mingguan
                        </h5>
                        <p class="text-muted mb-0" style="font-size:0.78rem;">
                            {{ $selectedMonth === 'all' ? 'Semua Bulan (Jan – Des)' : ('Per minggu — ' . (\App\Models\TaxExchangeRate::$monthNames[$selectedMonth] ?? '') . ' ' . $selectedYear) }}
                        </p>
                    </div>
                </div>
                {{-- Filter Bulan Pills --}}
                <div class="d-flex flex-wrap gap-1 mb-3 align-items-center">
                    {{-- ALL Pill --}}
                    <a href="{{ route('exchange-rate.index', ['year'=>$selectedYear, 'month'=>'all', 'currency'=>$selectedCurrency]) }}"
                       class="month-pill {{ $selectedMonth === 'all' ? 'active' : '' }}"
                       style="{{ $selectedMonth === 'all' ? 'background:#10b981; color:#000; border-color:#10b981; font-weight:800;' : 'border-color:rgba(16,185,129,0.5); color:#34d399;' }}"
                       title="Semua bulan">
                        <i class="bi bi-grid-fill me-1"></i> ALL (Semua)
                    </a>
                    <span class="text-secondary mx-1">|</span>

                    @if($latestOverallRecord)
                    <a href="{{ route('exchange-rate.index', ['year'=>$latestOverallRecord->exch_year, 'month'=>$latestOverallRecord->exch_month, 'currency'=>$selectedCurrency]) }}"
                       class="month-pill {{ $selectedMonth == $latestOverallRecord->exch_month ? 'active' : '' }}"
                       style="border-color:rgba(251,191,36,0.6); color:#fbbf24; background:rgba(251,191,36,0.12);"
                       title="Bulan terakhir">
                        <i class="bi bi-star-fill text-warning me-1"></i> Terkini ({{ substr(\App\Models\TaxExchangeRate::$monthNames[$latestOverallRecord->exch_month] ?? '', 0, 3) }})
                    </a>
                    <span class="text-secondary mx-1">|</span>
                    @endif

                    @php $mn = \App\Models\TaxExchangeRate::$monthNames; @endphp
                    @foreach($mn as $num => $name)
                    <a href="{{ route('exchange-rate.index', ['year'=>$selectedYear,'month'=>$num,'currency'=>$selectedCurrency]) }}"
                       class="month-pill {{ $selectedMonth==$num ? 'active' : '' }}">
                        {{ substr($name,0,3) }}
                    </a>
                    @endforeach
                </div>
                <div class="chart-wrapper" style="height:200px;">
                    <canvas id="chartWeekly"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TAB NAVIGATION: INCOMING (MINGGUAN) vs BUDGET FORECAST (BULANAN) ── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <ul class="nav nav-pills custom-er-tabs gap-2" id="erPillsTab" role="tablist" style="background:rgba(18,24,38,0.7); padding:6px; border-radius:999px; border:1px solid var(--card-border);">
            <li class="nav-item" role="presentation">
                <button class="nav-link active er-tab-btn px-4 py-2 fw-bold rounded-pill" id="tab-realisasi-btn" data-bs-toggle="pill" data-bs-target="#pills-realisasi" type="button" role="tab">
                    <i class="bi bi-calendar-week text-warning me-1.5"></i> Kurs Incoming
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link er-tab-btn px-4 py-2 fw-bold rounded-pill" id="tab-budget-btn" data-bs-toggle="pill" data-bs-target="#pills-budget" type="button" role="tab">
                    <i class="bi bi-pie-chart-fill me-1.5" style="color:#00d2ff;"></i> Budget Forecast
                </button>
            </li>
        </ul>

        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-cyan fw-bold px-3.5 py-1.5 rounded-pill d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalBulkBudgetForecast" style="font-size:0.82rem; border-color:rgba(0,210,255,0.4); color:#00d2ff; background:rgba(0,210,255,0.1);">
                <i class="bi bi-lightning-charge-fill text-info"></i> Edit Massal
            </button>
        </div>
    </div>

    <div class="tab-content" id="erPillsTabContent">
        {{-- ══ TAB 1: KURS INCOMING MINGGUAN ══ --}}
        <div class="tab-pane fade show active" id="pills-realisasi" role="tabpanel">
            <div class="glass-card-static p-0 overflow-hidden" style="border-radius:16px; border:1px solid var(--card-border);">
                {{-- Table Header Realisasi --}}
                <div class="d-flex justify-content-between align-items-center px-4 py-3 flex-wrap gap-2" style="background:rgba(18,24,38,0.7); border-bottom:1px solid var(--card-border);">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <h5 class="fw-bold mb-0" style="font-size:0.95rem;">
                            <i class="bi bi-table text-warning me-2"></i>Data Kurs Incoming Mingguan — {{ $selectedYear }}
                            <span class="badge ms-2 rounded-pill" style="background:rgba(226,179,74,0.2);color:#e2b34a;font-size:0.72rem;">{{ $allRecords->count() }} Records</span>
                        </h5>
                        {{-- Dropdown Filter Bulan Tabel --}}
                        <select onchange="window.location.href=this.value" class="form-select-dark form-select-sm" style="border-radius:20px; font-weight:600; padding:0.3rem 1.8rem 0.3rem 0.75rem; font-size:0.8rem; border-color:rgba(226,179,74,0.4);">
                            <option value="{{ route('exchange-rate.index', ['year'=>$selectedYear, 'month'=>'all', 'currency'=>$selectedCurrency]) }}" {{ $selectedMonth === 'all' ? 'selected' : '' }}>
                                Filter: Semua Bulan
                            </option>
                            @foreach(\App\Models\TaxExchangeRate::$monthNames as $num => $name)
                            <option value="{{ route('exchange-rate.index', ['year'=>$selectedYear, 'month'=>$num, 'currency'=>$selectedCurrency]) }}" {{ $selectedMonth == $num ? 'selected' : '' }}>
                                Filter: Bulan {{ $num }} — {{ $name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-warning fw-bold px-3 rounded-pill d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalInputManual" style="font-size:0.82rem;">
                            <i class="bi bi-plus-lg"></i> Input Kurs Mingguan
                        </button>
                        <button class="btn btn-sm btn-outline-danger fw-bold px-3 rounded-pill d-flex align-items-center gap-1" id="btnBulkDelete" onclick="bulkDelete()" style="font-size:0.82rem; display:none!important;">
                            <i class="bi bi-trash"></i> Hapus Terpilih
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark-custom" id="tableExchangeRate">
                        <thead>
                            <tr>
                                <th style="width:40px;">
                                    <input type="checkbox" class="form-check-input" id="checkAll" onchange="toggleAllChecks(this)">
                                </th>
                                <th>Tahun</th>
                                <th>Bulan</th>
                                <th>Minggu Ke-</th>
                                <th>Kurs Incoming Mingguan (Rp)</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Diperbarui</th>
                                <th>User (Pengupload)</th>
                                <th style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allRecords as $rec)
                            @php
                                $isCurrentMonth = ($rec->exch_month == $selectedMonth);
                                $isLatest = ($latestOverallRecord && $rec->id === $latestOverallRecord->id);
                            @endphp
                            <tr class="{{ $isCurrentMonth ? '' : '' }}" id="row-{{ $rec->id }}">
                                <td>
                                    <input type="checkbox" class="form-check-input row-check" value="{{ $rec->id }}">
                                </td>
                                <td class="fw-semibold text-white">{{ $rec->exch_year }}</td>
                                <td>
                                    <span class="badge rounded-pill" style="background:rgba(6,182,212,0.15);color:#06b6d4;border:1px solid rgba(6,182,212,0.35);font-size:0.78rem;">
                                        {{ \App\Models\TaxExchangeRate::$monthNames[$rec->exch_month] ?? 'Bln '.$rec->exch_month }}
                                    </span>
                                </td>
                                <td>
                                    <span class="week-badge" style="background:rgba(245,158,11,0.18);color:#f59e0b;border:1px solid rgba(245,158,11,0.4);">
                                        {{ $rec->week_code }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold font-monospace" style="font-size:0.95rem;{{ $isLatest ? 'color:#fbbf24;' : 'color:#f3f4f6;' }}">
                                        Rp {{ number_format($rec->tax_exchange_rate, 0, ',', '.') }}
                                    </span>
                                    @if($isLatest)
                                    <span class="badge ms-1" style="background:rgba(251,191,36,0.2);color:#fbbf24;border:1px solid rgba(251,191,36,0.4);font-size:0.68rem;">TERKINI</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:0.82rem;">{{ $rec->start_date ? $rec->start_date->format('d M Y') : '—' }}</td>
                                <td class="text-muted" style="font-size:0.82rem;">{{ $rec->end_date ? $rec->end_date->format('d M Y') : '—' }}</td>
                                <td class="text-muted" style="font-size:0.82rem;">{{ $rec->last_update ? $rec->last_update->format('d M Y') : '—' }}</td>
                                <td style="font-size:0.84rem;">
                                    <span class="px-2 py-1 rounded" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:#e2b34a;">
                                        <i class="bi bi-person-circle me-1 text-warning"></i>{{ $rec->last_user ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-info px-2 py-1 rounded" title="Edit Data & User" onclick="openEdit({{ $rec->id }}, {{ $rec->exch_year }}, {{ $rec->exch_month }}, {{ $rec->week_code }}, {{ $rec->currency_code }}, {{ $rec->tax_exchange_rate }}, '{{ $rec->start_date?->format('Y-m-d') ?? '' }}', '{{ $rec->end_date?->format('Y-m-d') ?? '' }}', '{{ addslashes($rec->last_user ?? '') }}')">
                                            <i class="bi bi-pencil-fill" style="font-size:0.78rem;"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger px-2 py-1 rounded" title="Hapus" onclick="confirmDelete({{ $rec->id }}, '{{ addslashes(\App\Models\TaxExchangeRate::$monthNames[$rec->exch_month] ?? '') }} {{ $rec->exch_year }} Minggu {{ $rec->week_code }}')">
                                            <i class="bi bi-trash-fill" style="font-size:0.78rem;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div style="opacity:0.45;">
                                        <i class="bi bi-currency-exchange" style="font-size:3rem; display:block; margin-bottom:0.75rem;"></i>
                                        <p class="fw-semibold mb-1">Belum ada data kurs incoming mingguan</p>

                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ══ TAB 2: KURS BUDGET FORECAST BULANAN (1 - 12) ══ --}}
        <div class="tab-pane fade" id="pills-budget" role="tabpanel">
            <div class="glass-card-static p-0 overflow-hidden" style="border-radius:16px; border:1px solid var(--card-border);">
                {{-- Table Header Budget Forecast --}}
                <div class="d-flex justify-content-between align-items-center px-4 py-3 flex-wrap gap-2" style="background:rgba(18,24,38,0.7); border-bottom:1px solid var(--card-border);">
                    <div>
                        <h5 class="fw-bold mb-0" style="font-size:0.95rem;">
                            <i class="bi bi-pie-chart-fill text-info me-2"></i>Tabel Budget Forecast Kurs — Tahun {{ $selectedYear }}
                            <span class="badge ms-2 rounded-pill" style="background:rgba(0,210,255,0.2);color:#00d2ff;font-size:0.72rem;">12 Periode Bulanan</span>
                        </h5>

                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-info fw-bold px-3 rounded-pill d-flex align-items-center gap-1.5 text-dark" data-bs-toggle="modal" data-bs-target="#modalBulkBudgetForecast" style="font-size:0.82rem;">
                            <i class="bi bi-lightning-charge-fill"></i> Edit Massal 12 Bulan
                        </button>
                        <button class="btn btn-sm btn-outline-info fw-bold px-3 rounded-pill d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalInputBudgetForecast" style="font-size:0.82rem;">
                            <i class="bi bi-plus-lg"></i> Input Single Bulan
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark-custom" id="tableBudgetForecast">
                        <thead>
                            <tr>
                                <th>Periode Bulan</th>
                                <th>Kurs Budget Forecast (Rp)</th>
                                <th>Incoming Rata-Rata (Rp)</th>
                                <th>Keterangan / Remarks</th>
                                <th>Diperbarui Pada</th>
                                <th>User (PIC)</th>
                                <th style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($budgetForecastRecords as $bf)
                            <tr>
                                <td class="fw-bold text-white">
                                    <span class="px-2.5 py-1 rounded-pill me-2" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); font-size:0.78rem;">
                                        Bulan {{ sprintf('%02d', $bf->exch_month) }}
                                    </span>
                                    {{ $bf->month_name }}
                                </td>
                                <td>
                                    @if($bf->budget_rate > 0)
                                    <span class="fw-bold font-monospace" style="font-size:0.98rem; color:#00d2ff;">
                                        Rp {{ $bf->budget_rate_fmt }}
                                    </span>
                                    @else
                                    <span class="text-muted font-monospace" style="font-size:0.85rem; opacity:0.5;">— Belum di-set —</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bf->actual_avg_rate > 0)
                                    <span class="fw-bold font-monospace text-warning" style="font-size:0.92rem;">
                                        Rp {{ $bf->actual_avg_fmt }}
                                    </span>
                                    @else
                                    <span class="text-muted" style="font-size:0.82rem;">—</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:0.84rem;">
                                    {{ $bf->remarks ?? 'Budget Forecast ' . $selectedYear }}
                                </td>
                                <td class="text-muted" style="font-size:0.82rem;">{{ $bf->last_update }}</td>
                                <td style="font-size:0.84rem;">
                                    <span class="px-2 py-1 rounded" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:#e2b34a;">
                                        <i class="bi bi-person-circle me-1 text-warning"></i>{{ $bf->last_user }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info px-2 py-1 rounded d-flex align-items-center gap-1" title="Edit Budget Forecast" onclick="openEditBudgetForecast({{ $bf->exch_month }}, {{ $bf->budget_rate }}, '{{ addslashes($bf->remarks ?? '') }}', '{{ addslashes($bf->last_user ?? '') }}')">
                                        <i class="bi bi-pencil-fill" style="font-size:0.78rem;"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /container --}}


{{-- ══════════════════════════════════════════════════════
     MODAL: INPUT MANUAL
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalInputManual" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:#121826;border:1px solid rgba(255,255,255,0.12);border-radius:20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(226,179,74,0.18);border:1px solid rgba(226,179,74,0.4);color:#e2b34a;">
                        <i class="bi bi-plus-square-dotted fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Input Kurs Manual</h5>

                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form action="{{ route('exchange-rate.store') }}" method="POST" id="formInputManual">
                    @csrf
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label"><i class="bi bi-calendar text-warning me-1"></i> Tahun</label>
                            <input type="number" name="exch_year" class="form-control form-control-dark"
                                   value="{{ old('exch_year', $selectedYear) }}" min="2000" max="2099" placeholder="2026" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label"><i class="bi bi-calendar3 text-info me-1"></i> Bulan</label>
                            <select name="exch_month" class="form-select form-select-dark" required>
                                @foreach(\App\Models\TaxExchangeRate::$monthNames as $num => $name)
                                <option value="{{ $num }}" {{ old('exch_month', $selectedMonth) == $num ? 'selected' : '' }}>
                                    {{ $num }} — {{ $name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label"><i class="bi bi-calendar-week text-warning me-1"></i> Minggu Ke-</label>
                            <select name="week_code" class="form-select form-select-dark" required>
                                @for($w=1; $w<=5; $w++)
                                <option value="{{ $w }}" {{ old('week_code',1)==$w ? 'selected' : '' }}>Minggu {{ $w }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label"><i class="bi bi-globe text-info me-1"></i> Mata Uang</label>
                            <select name="currency_code" class="form-select form-select-dark" required>
                                @foreach(\App\Models\TaxExchangeRate::$currencyMap as $code => $curr)
                                <option value="{{ $code }}" {{ $selectedCurrency==$code ? 'selected' : '' }}>{{ $curr['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label"><i class="bi bi-currency-exchange text-warning me-1"></i> Nilai Kurs (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(226,179,74,0.15);border:1px solid rgba(255,255,255,0.14);color:#e2b34a;border-right:none;">Rp</span>
                                <input type="number" name="tax_exchange_rate" class="form-control form-control-dark"
                                       placeholder="contoh: 16777" min="1" required value="{{ old('tax_exchange_rate') }}"
                                       style="border-left:none;">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label"><i class="bi bi-calendar-event me-1 text-info"></i> Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control form-control-dark" value="{{ old('start_date') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label"><i class="bi bi-calendar-check me-1 text-success"></i> Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-control form-control-dark" value="{{ old('end_date') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-person-fill me-1 text-warning"></i> User / Pengupload (Last User)</label>
                            <input type="text" name="last_user" class="form-control form-control-dark" value="{{ old('last_user', Auth::user()->name ?? 'Administrator') }}" placeholder="Ketik nama pengupload (contoh: Bella / Admin)" required>

                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning fw-bold rounded-pill px-4 d-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i> Simpan Kurs
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: EDIT
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditRate" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:#121826;border:1px solid rgba(255,255,255,0.12);border-radius:20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(0,210,255,0.15);border:1px solid rgba(0,210,255,0.4);color:#00d2ff;">
                        <i class="bi bi-pencil-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Edit Data Kurs & User</h5>

                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="formEditRate" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Tahun</label>
                            <select name="exch_year" id="edit_exch_year" class="form-select form-select-dark" required>
                                @foreach($availableYears as $y)
                                <option value="{{ $y }}">Tahun {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Bulan</label>
                            <select name="exch_month" id="edit_exch_month" class="form-select form-select-dark" required>
                                @foreach(\App\Models\TaxExchangeRate::$monthNames as $num => $name)
                                <option value="{{ $num }}">{{ $num }} — {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Minggu Ke-</label>
                            <select name="week_code" id="edit_week_code" class="form-select form-select-dark" required>
                                @for($w=1;$w<=5;$w++)
                                <option value="{{ $w }}">Minggu {{ $w }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Mata Uang</label>
                            <select name="currency_code" id="edit_currency_code" class="form-select form-select-dark" required>
                                @foreach(\App\Models\TaxExchangeRate::$currencyMap as $code => $curr)
                                <option value="{{ $code }}">{{ $curr['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nilai Kurs (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(226,179,74,0.15);border:1px solid rgba(255,255,255,0.14);color:#e2b34a;border-right:none;">Rp</span>
                                <input type="number" name="tax_exchange_rate" id="edit_rate" class="form-control form-control-dark" min="1" required style="border-left:none;">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="edit_start_date" class="form-control form-control-dark">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="edit_end_date" class="form-control form-control-dark">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-person-fill text-warning me-1"></i> User / Pengupload (Last User)</label>
                            <input type="text" name="last_user" id="edit_last_user" class="form-control form-control-dark" placeholder="Ketik nama user pengupload" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info fw-bold rounded-pill px-4 d-flex align-items-center gap-2">
                            <i class="bi bi-check-lg"></i> Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: IMPORT EXCEL
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalImportExcel" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:#121826;border:1px solid rgba(255,255,255,0.12);border-radius:20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.4);color:#10b981;">
                        <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Import Data Kurs dari Excel / CSV</h5>

                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                {{-- Format Info --}}
                <div class="p-3 mb-3 rounded-3" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.3);">
                    <div class="fw-bold text-success mb-2" style="font-size:0.85rem;"><i class="bi bi-info-circle me-1"></i> Format Kolom Excel (sesuai urutan)</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(['Exch_Year','Exch_Month','Week_Code','Currency_Code','Tax_ExchangeRate','Start_Date','End_Date','Last_Update','Last_User','Register_Date'] as $col)
                        <span class="badge" style="background:rgba(16,185,129,0.2);color:#34d399;border:1px solid rgba(52,211,153,0.4);font-size:0.75rem;">{{ $col }}</span>
                        @endforeach
                    </div>
                    <div class="mt-2 text-muted" style="font-size:0.78rem;">
                        <i class="bi bi-lightbulb text-warning me-1"></i>
                        Format tanggal: <strong>YYYYMMDD</strong> (cth: 20260101) &nbsp;•&nbsp;
                        Currency_Code: <strong>2 = USD/IDR</strong> &nbsp;•&nbsp;
                        Baris pertama boleh berupa header (akan dilewati otomatis)
                    </div>
                </div>

                <form action="{{ route('exchange-rate.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File</label>
                        <input type="file" name="excel_file" class="form-control form-control-dark" accept=".csv,.xlsx,.xls,.txt" required
                               style="padding:0.6rem;">
                        <div class="text-muted mt-1" style="font-size:0.78rem;">Format yang diterima: CSV, XLSX, XLS &nbsp;•&nbsp; Maks 5 MB</div>
                    </div>

                    @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="p-3 rounded-3 mb-3" style="background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.3);">
                        <div class="fw-bold text-danger mb-2" style="font-size:0.83rem;"><i class="bi bi-exclamation-triangle me-1"></i> Error Baris:</div>
                        <ul class="mb-0" style="font-size:0.78rem; color:#f87171;">
                            @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center gap-2 mt-4">
                        <a href="{{ route('exchange-rate.template') }}" class="btn btn-outline-success rounded-pill px-4 d-flex align-items-center gap-2" style="font-size:0.83rem;">
                            <i class="bi bi-download"></i> Download Template
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success fw-bold rounded-pill px-4 d-flex align-items-center gap-2">
                                <i class="bi bi-upload"></i> Import Sekarang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: INPUT SINGLE BUDGET FORECAST
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalInputBudgetForecast" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#121826;border:1px solid rgba(255,255,255,0.12);border-radius:20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(0,210,255,0.18);border:1px solid rgba(0,210,255,0.4);color:#00d2ff;">
                        <i class="bi bi-pie-chart-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Input Budget Forecast Kurs</h5>

                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form action="{{ route('exchange-rate.budget-forecast.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="currency_code" value="{{ $selectedCurrency }}">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label"><i class="bi bi-calendar text-warning me-1"></i> Tahun Target</label>
                            <input type="number" name="exch_year" class="form-control form-control-dark fw-bold"
                                   value="{{ old('exch_year', $selectedYear) }}" min="2000" max="2099" placeholder="contoh: 2027" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label"><i class="bi bi-calendar3 text-info me-1"></i> Pilih Bulan</label>
                            <select name="exch_month" class="form-select form-select-dark" required>
                                @foreach(\App\Models\TaxBudgetForecastRate::$monthNames as $num => $name)
                                <option value="{{ $num }}">Bulan {{ sprintf('%02d', $num) }} — {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-currency-exchange text-warning me-1"></i> Nilai Budget Forecast Kurs (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:rgba(0,210,255,0.15);border:1px solid rgba(255,255,255,0.14);color:#00d2ff;border-right:none;">Rp</span>
                            <input type="number" name="budget_rate" class="form-control form-control-dark" placeholder="contoh: 16500" min="1" required style="border-left:none;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-chat-left-text text-muted me-1"></i> Keterangan / Remarks</label>
                        <input type="text" name="remarks" class="form-control form-control-dark" placeholder="Contoh: Budget Target Q1 {{ $selectedYear }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-person-circle text-warning me-1"></i> User Pengisi / PIC</label>
                        <input type="text" name="last_user" class="form-control form-control-dark" value="{{ Auth::user()->name ?? 'System' }}">
                    </div>
                    <div class="modal-footer border-0 px-0 pb-0">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info fw-bold rounded-pill px-4 text-dark"><i class="bi bi-check-circle-fill me-1"></i> Simpan Forecast</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: EDIT SINGLE BUDGET FORECAST
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditBudgetForecast" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#121826;border:1px solid rgba(255,255,255,0.12);border-radius:20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(0,210,255,0.18);border:1px solid rgba(0,210,255,0.4);color:#00d2ff;">
                        <i class="bi bi-pencil-square fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Edit Budget Forecast Kurs</h5>

                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form action="{{ route('exchange-rate.budget-forecast.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="exch_year" value="{{ $selectedYear }}">
                    <input type="hidden" name="currency_code" value="{{ $selectedCurrency }}">
                    <input type="hidden" name="exch_month" id="bf_edit_month">

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-calendar3 text-info me-1"></i> Periode Bulan</label>
                        <input type="text" id="bf_edit_month_label" class="form-control form-control-dark" readonly style="opacity:0.8;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-currency-exchange text-warning me-1"></i> Nilai Budget Forecast Kurs (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:rgba(0,210,255,0.15);border:1px solid rgba(255,255,255,0.14);color:#00d2ff;border-right:none;">Rp</span>
                            <input type="number" name="budget_rate" id="bf_edit_rate" class="form-control form-control-dark" required style="border-left:none;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-chat-left-text text-muted me-1"></i> Keterangan / Remarks</label>
                        <input type="text" name="remarks" id="bf_edit_remarks" class="form-control form-control-dark">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-person-circle text-warning me-1"></i> User Pengisi / PIC</label>
                        <input type="text" name="last_user" id="bf_edit_user" class="form-control form-control-dark" value="{{ Auth::user()->name ?? 'System' }}">
                    </div>
                    <div class="modal-footer border-0 px-0 pb-0">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info fw-bold rounded-pill px-4 text-dark"><i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODAL: BULK EDIT 12 MONTHS BUDGET FORECAST
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalBulkBudgetForecast" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background:#121826;border:1px solid rgba(255,255,255,0.12);border-radius:20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(0,210,255,0.18);border:1px solid rgba(0,210,255,0.4);color:#00d2ff;">
                        <i class="bi bi-lightning-charge-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Edit Massal Budget Forecast (12 Bulan)</h5>

                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <form action="{{ route('exchange-rate.budget-forecast.bulk') }}" method="POST">
                    @csrf
                    <input type="hidden" name="currency_code" value="{{ $selectedCurrency }}">
                    <div class="mb-3 p-3 rounded-3" style="background:rgba(0,210,255,0.08); border:1px solid rgba(0,210,255,0.25);">
                        <div class="row align-items-center g-2">
                            <div class="col-md-5">
                                <label class="form-label mb-0 fw-bold text-info"><i class="bi bi-calendar-event me-1"></i> Target Tahun Budget Forecast:</label>
                            </div>
                            <div class="col-md-7">
                                <input type="number" name="exch_year" class="form-control form-control-dark fw-bold"
                                       style="border-color: rgba(0,210,255,0.4);" value="{{ old('exch_year', $selectedYear) }}"
                                       min="2000" max="2099" placeholder="contoh: 2027" required>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        @foreach($budgetForecastRecords as $bf)
                        <div class="col-6 col-md-4">
                            <label class="form-label" style="font-size:0.8rem;">
                                <i class="bi bi-calendar3 text-info me-1"></i> Bulan {{ sprintf('%02d', $bf->exch_month) }} ({{ substr($bf->month_name,0,3) }})
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" style="background:rgba(0,210,255,0.12);border:1px solid rgba(255,255,255,0.14);color:#00d2ff;border-right:none;font-size:0.75rem;">Rp</span>
                                <input type="number" name="rates[{{ $bf->exch_month }}]" class="form-control form-control-dark" value="{{ $bf->budget_rate > 0 ? $bf->budget_rate : '' }}" placeholder="0" min="0" style="border-left:none;">
                            </div>
                        </div>
                        @endforeach
                        <div class="col-12 mt-3">
                            <label class="form-label"><i class="bi bi-person-circle text-warning me-1"></i> User Pengisi / PIC</label>
                            <input type="text" name="last_user" class="form-control form-control-dark" value="{{ Auth::user()->name ?? 'System' }}">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-0 pb-0 mt-3">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info fw-bold rounded-pill px-4 text-dark"><i class="bi bi-save-fill me-1"></i> Simpan Semuanya</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Hidden delete forms --}}
<form id="formDeleteSingle" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
<form id="formDeleteBulk" action="{{ route('exchange-rate.destroy-bulk') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── Live Clock ──
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    const el = document.getElementById('live-clock');
    if (el) el.textContent = `${h}:${m}:${s} WIB`;
}
setInterval(updateClock, 1000);
updateClock();

// ── Chart: Monthly ──
const ctxMonthlyEl = document.getElementById('chartMonthly');
if (ctxMonthlyEl) {
    const ctxMonthly = ctxMonthlyEl.getContext('2d');
    const monthlyLabels = @json($monthlyChartLabels);
    const monthlyValues = @json($monthlyChartValues);
    const monthlyMax    = @json($monthlyChartMax);
    const monthlyMin    = @json($monthlyChartMin);
    const budgetValues  = @json($budgetChartValues);

    const allMonthlyNums = [...monthlyValues, ...monthlyMax, ...monthlyMin, ...budgetValues].map(v => Number(v)).filter(v => v > 0);
    const monthlyMinVal = allMonthlyNums.length ? Math.min(...allMonthlyNums) : 15000;
    const monthlyMaxVal = allMonthlyNums.length ? Math.max(...allMonthlyNums) : 18000;
    const monthlyScaleMin = Math.max(0, Math.floor((monthlyMinVal - 300) / 100) * 100);
    const monthlyScaleMax = Math.ceil((monthlyMaxVal + 300) / 100) * 100;

    new Chart(ctxMonthly, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [
                {
                    label: 'Rata-rata Incoming (Rp)',
                    data: monthlyValues,
                    backgroundColor: 'rgba(226,179,74,0.45)',
                    borderColor: '#e2b34a',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Budget Forecast (Rp)',
                    data: budgetValues,
                    type: 'line',
                    borderColor: '#00d2ff',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#00d2ff',
                    fill: false,
                    tension: 0.35,
                },
                {
                    label: 'Tertinggi',
                    data: monthlyMax,
                    type: 'line',
                    borderColor: 'rgba(248,113,113,0.85)',
                    borderWidth: 2,
                    borderDash: [4,4],
                    pointRadius: 3,
                    pointBackgroundColor: '#f87171',
                    fill: false,
                    tension: 0.35,
                },
                {
                    label: 'Terendah',
                    data: monthlyMin,
                    type: 'line',
                    borderColor: 'rgba(52,211,153,0.85)',
                    borderWidth: 2,
                    borderDash: [4,4],
                    pointRadius: 3,
                    pointBackgroundColor: '#34d399',
                    fill: false,
                    tension: 0.35,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: '#cbd5e1', font: { family: 'Outfit', weight: '600', size: 11 } } },
                tooltip: {
                    backgroundColor: 'rgba(18,24,38,0.95)',
                    borderColor: 'rgba(226,179,74,0.4)',
                    borderWidth: 1,
                    titleColor: '#f3f4f6',
                    bodyColor: '#cbd5e1',
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.raw === null || ctx.raw === undefined) return '';
                            return ` ${ctx.dataset.label}: Rp ${parseInt(ctx.raw).toLocaleString('id-ID')}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#94a3b8', font: { family: 'Outfit', weight: '600', size: 11 } },
                    grid: { color: 'rgba(255,255,255,0.05)' },
                },
                y: {
                    min: monthlyScaleMin,
                    max: monthlyScaleMax,
                    ticks: {
                        color: '#94a3b8',
                        font: { family: 'Outfit', weight: '600', size: 11 },
                        callback: (v) => 'Rp ' + parseInt(v).toLocaleString('id-ID')
                    },
                    grid: { color: 'rgba(255,255,255,0.06)' }
                }
            }
        }
    });
}

// ── Chart: Weekly ──
const ctxWeeklyEl = document.getElementById('chartWeekly');
if (ctxWeeklyEl) {
    const ctxWeekly = ctxWeeklyEl.getContext('2d');
    const weeklyLabels = @json($weeklyChartLabels);
    const weeklyValues = @json($weeklyChartValues);

    const allWeeklyNums = weeklyValues.map(v => Number(v)).filter(v => v > 0);
    const weeklyMinVal = allWeeklyNums.length ? Math.min(...allWeeklyNums) : 15000;
    const weeklyMaxVal = allWeeklyNums.length ? Math.max(...allWeeklyNums) : 18000;
    const weeklyScaleMin = Math.max(0, Math.floor((weeklyMinVal - 200) / 100) * 100);
    const weeklyScaleMax = Math.ceil((weeklyMaxVal + 200) / 100) * 100;

    const weeklyGradient = ctxWeekly.createLinearGradient(0, 0, 0, 220);
    weeklyGradient.addColorStop(0, 'rgba(0,210,255,0.35)');
    weeklyGradient.addColorStop(1, 'rgba(0,210,255,0.02)');

    new Chart(ctxWeekly, {
        type: 'line',
        data: {
            labels: weeklyLabels,
            datasets: [{
                label: 'Kurs Mingguan (Rp)',
                data: weeklyValues,
                borderColor: '#00d2ff',
                borderWidth: 2.5,
                pointBackgroundColor: '#00d2ff',
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                backgroundColor: weeklyGradient,
                tension: 0.35,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(18,24,38,0.95)',
                    borderColor: 'rgba(0,210,255,0.4)',
                    borderWidth: 1,
                    titleColor: '#f3f4f6',
                    bodyColor: '#cbd5e1',
                    callbacks: {
                        label: (ctx) => ` Kurs Pajak: Rp ${parseInt(ctx.raw).toLocaleString('id-ID')}`
                    }
                }
            },
            scales: {
                x: { ticks: { color: '#94a3b8', font: { family: 'Outfit', weight: '600', size: 11 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: {
                    min: weeklyScaleMin,
                    max: weeklyScaleMax,
                    ticks: { color: '#94a3b8', font: { family: 'Outfit', weight: '600', size: 10 }, callback: (v) => 'Rp ' + parseInt(v).toLocaleString('id-ID') },
                    grid: { color: 'rgba(255,255,255,0.06)' }
                }
            }
        }
    });
}
// ── Edit Modal ──
function openEdit(id, year, month, week, currency, rate, startDate, endDate, lastUser) {
    document.getElementById('edit_exch_year').value   = year;
    document.getElementById('edit_exch_month').value  = month;
    document.getElementById('edit_week_code').value   = week;
    document.getElementById('edit_currency_code').value = currency;
    document.getElementById('edit_rate').value         = rate;
    document.getElementById('edit_start_date').value   = startDate;
    document.getElementById('edit_end_date').value     = endDate;
    if (document.getElementById('edit_last_user')) {
        document.getElementById('edit_last_user').value = lastUser || '';
    }
    document.getElementById('formEditRate').action     = `/exchange-rate/${id}`;
    new bootstrap.Modal(document.getElementById('modalEditRate')).show();
}

// ── Delete Confirm (Single) ──
function confirmDelete(id, label) {
    Swal.fire({
        title: '<span style="color: #fbbf24; font-family: Outfit, sans-serif; font-weight: 700;">Hapus Data Kurs?</span>',
        html: `<div style="color: #f8fafc; font-size: 0.95rem; line-height: 1.5;">Apakah Anda yakin ingin menghapus data kurs <strong>${label}</strong>?<br><span class="text-danger" style="font-size:0.83rem;">Data yang dihapus tidak dapat dikembalikan.</span></div>`,
        icon: 'warning',
        iconColor: '#f59e0b',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus Data!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        background: '#0f1623',
        customClass: {
            popup: 'border border-warning border-opacity-50 rounded-4 shadow-lg py-4 px-3',
            confirmButton: 'btn btn-danger rounded-pill px-4 py-2 fw-bold text-white me-2',
            cancelButton: 'btn btn-outline-light rounded-pill px-4 py-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('formDeleteSingle');
            form.action = `/exchange-rate/${id}`;
            form.submit();
        }
    });
}

// ── Bulk Delete Confirm ──
function toggleAllChecks(master) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
    updateBulkBtn();
}
document.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateBulkBtn));
function updateBulkBtn() {
    const checked = document.querySelectorAll('.row-check:checked').length;
    const btn = document.getElementById('btnBulkDelete');
    if (btn) btn.style.display = checked > 0 ? 'inline-flex' : 'none';
}
function bulkDelete() {
    const ids = [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    if (!ids.length) return;
    Swal.fire({
        title: '<span style="color: #ef4444; font-family: Outfit, sans-serif; font-weight: 700;">Hapus Terpilih?</span>',
        html: `<div style="color: #f8fafc; font-size: 0.95rem; line-height: 1.5;">Apakah Anda yakin ingin menghapus <strong>${ids.length} data kurs</strong> yang dipilih?<br><span class="text-danger" style="font-size:0.83rem;">Semua record terpilih akan dihapus dari sistem.</span></div>`,
        icon: 'warning',
        iconColor: '#ef4444',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        background: '#0f1623',
        customClass: {
            popup: 'border border-danger border-opacity-50 rounded-4 shadow-lg py-4 px-3',
            confirmButton: 'btn btn-danger rounded-pill px-4 py-2 fw-bold text-white me-2',
            cancelButton: 'btn btn-outline-light rounded-pill px-4 py-2'
        },
        buttonsStyling: false
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
            document.getElementById('formDeleteBulk').submit();
        }
    });
}

// ── Open Edit Modal for Budget Forecast ──
function openEditBudgetForecast(month, rate, remarks, user) {
    const monthNames = {1:'Januari', 2:'Februari', 3:'Maret', 4:'April', 5:'Mei', 6:'Juni', 7:'Juli', 8:'Agustus', 9:'September', 10:'Oktober', 11:'November', 12:'Desember'};
    document.getElementById('bf_edit_month').value = month;
    document.getElementById('bf_edit_month_label').value = `Bulan ${String(month).padStart(2, '0')} — ${monthNames[month] || ''}`;
    document.getElementById('bf_edit_rate').value = rate > 0 ? rate : '';
    document.getElementById('bf_edit_remarks').value = remarks || '';
    document.getElementById('bf_edit_user').value = user || '{{ Auth::user()->name ?? "System" }}';

    const modalEl = document.getElementById('modalEditBudgetForecast');
    const m = new bootstrap.Modal(modalEl);
    m.show();
}

// ── Auto-open modal bila ada validation error ──
@if($errors->any())
const m = new bootstrap.Modal(document.getElementById('modalInputManual'));
m.show();
@endif

// ── SweetAlert Pop-Up Notifikasi Selesai (Hapus / Simpan / Edit) ──
@if(session('success'))
Swal.fire({
    title: '<span style="color: #fbbf24; font-family: Outfit, sans-serif; font-weight: 700;">Berhasil!</span>',
    html: '<div style="color: #f8fafc; font-size: 0.95rem; line-height: 1.5;">' + @json(session('success')) + '</div>',
    icon: 'success',
    background: '#0f1623',
    iconColor: '#10b981',
    confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Tutup & Lanjutkan',
    confirmButtonColor: '#e2b34a',
    customClass: {
        popup: 'border border-warning border-opacity-50 rounded-4 shadow-lg py-4 px-3',
        confirmButton: 'btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark'
    },
    buttonsStyling: false
});
@endif

@if(session('error'))
Swal.fire({
    title: '<span style="color: #ef4444; font-family: Outfit, sans-serif; font-weight: 700;">Terjadi Kesalahan!</span>',
    html: '<div style="color: #f8fafc; font-size: 0.95rem; line-height: 1.5;">' + @json(session('error')) + '</div>',
    icon: 'error',
    background: '#0f1623',
    iconColor: '#ef4444',
    confirmButtonText: '<i class="bi bi-x-lg me-1"></i> Tutup',
    confirmButtonColor: '#ef4444',
    customClass: {
        popup: 'border border-danger border-opacity-50 rounded-4 shadow-lg py-4 px-3',
        confirmButton: 'btn btn-danger rounded-pill px-4 py-2 fw-bold text-white'
    },
    buttonsStyling: false
});
@endif
</script>
</body>
</html>
