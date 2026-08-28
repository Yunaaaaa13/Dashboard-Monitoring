<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitoring Purchasing | PT Kawai Indonesia</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-secondary: #121826;
            --card-bg: rgba(23, 31, 48, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --accent-gold: #e2b34a;
            --accent-gold-glow: rgba(226, 179, 74, 0.25);
            --accent-cyan: #00d2ff;
            --accent-emerald: #10b981;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
        }

        body {
            background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Outfit', sans-serif;
        }

        .top-navbar {
            background: rgba(18, 24, 38, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--card-border);
            padding: 0.75rem 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 0;
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
            font-size: 1.4rem;
            letter-spacing: 1px;
            color: #ffffff;
        }

        .system-badge {
            font-size: 0.75rem;
            background: rgba(226, 179, 74, 0.15);
            color: var(--accent-gold);
            
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .live-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: var(--accent-emerald);
            border-radius: 50%;
            margin-right: 6px;
            
        }

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            
            backdrop-filter: blur(4px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        

        .kpi-title {
            color: var(--text-muted);
            font-size: 0.825rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.5rem;
        }

        .kpi-value {
            font-family: 'Outfit', sans-serif;
            font-size: 2.35rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.4rem;
        }

        .kpi-unit {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .kpi-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .icon-gold {
            background: rgba(226, 179, 74, 0.15);
            color: var(--accent-gold);
        }

        .icon-blue {
            background: rgba(0, 210, 255, 0.15);
            color: var(--accent-cyan);
        }

        .icon-emerald {
            background: rgba(16, 185, 129, 0.15);
            color: var(--accent-emerald);
        }

        .icon-purple {
            background: rgba(168, 85, 247, 0.15);
            color: #c084fc;
        }

        .text-muted {
            color: #b0b8c4 !important;
        }

        .table, .table-custom {
            --bs-table-bg: transparent !important;
            --bs-table-color: var(--text-main) !important;
            --bs-table-border-color: rgba(255, 255, 255, 0.1) !important;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.05) !important;
            --bs-table-hover-color: #ffffff !important;
            color: var(--text-main) !important;
            background-color: transparent !important;
            margin-bottom: 0;
        }

        .table thead th, .table-custom thead th {
            background: rgba(18, 24, 38, 0.85) !important;
            color: #d1d5db !important;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            border-bottom: 1px solid var(--card-border) !important;
            padding: 1rem;
        }

        .table tbody tr, .table tbody td, .table-custom tbody tr, .table-custom tbody td {
            background-color: transparent !important;
            color: var(--text-main) !important;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            vertical-align: middle;
            font-size: 0.92rem;
        }

        .table tbody tr:hover, .table-custom tbody tr:hover {
            background: rgba(255, 255, 255, 0.04) !important;
        }

        .progress-thin {
            height: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .form-select-dark {
            background-color: rgba(18, 24, 38, 0.9);
            color: var(--text-main);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            background-color: rgba(10, 14, 23, 0.88);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            padding: 0.4rem 2rem 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .form-select-dark:focus {
            background-color: rgba(10, 14, 23, 0.95);
            color: #fff;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.2rem rgba(226, 179, 74, 0.25);
            outline: none;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
</head>
<body>

    <!-- TOP NAVBAR (STICKY) -->
    <nav class="top-navbar">
        <!-- Row 1: Brand + Controls -->
        <div class="top-navbar-row1 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
            <a href="{{ route('dashboard.overview') }}" class="text-decoration-none" style="color: inherit !important; text-decoration: none !important;">
                <div class="d-flex align-items-center gap-2 mb-0.5">
                    <i class="bi bi-music-note-beamed text-warning fs-4" style="line-height: 1; vertical-align: middle;"></i>
                    <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; color: #ffffff; display: inline-block;">PT KAWAI INDONESIA</span>
                </div>
                <div class="text-muted" style="font-size: 0.73rem; letter-spacing: 0.02em; margin-left: 2px; color: #9ca3af !important;">Dashboard Monitoring Purchasing</div>
            </a>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Filter Tahun, Kategori & PIC Buyer (Konsolidasi Multi-User) -->
                <form action="{{ route('dashboard.overview') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                    <select name="year" class="form-select-dark form-select-sm" onchange="this.form.submit()">
                        @for($y = 2024; $y <= 2028; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                    <select name="category_id" class="form-select-dark form-select-sm" onchange="this.form.submit()" style="max-width: 175px;">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_code }} - {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="user_id" class="form-select-dark form-select-sm" onchange="this.form.submit()" style="max-width: 185px;">
                        <option value="">-- Semua PIC (Global) --</option>
                        @foreach(($buyerUsers ?? []) as $bu)
                            <option value="{{ $bu->id }}" {{ (($selectedUserId ?? null) == $bu->id) ? 'selected' : '' }}>
                                {{ $bu->name }} ({{ ucfirst($bu->role) }})
                            </option>
                        @endforeach
                    </select>
                    <select name="supplier" class="form-select-dark form-select-sm" onchange="this.form.submit()" style="max-width: 190px;">
                        <option value="">-- Semua Vendor --</option>
                        @foreach(($suppliers ?? []) as $sup)
                            <option value="{{ $sup }}" {{ (($selectedSupplier ?? null) == $sup) ? 'selected' : '' }}>
                                {{ strlen($sup) > 22 ? substr($sup, 0, 20) . '...' : $sup }}
                            </option>
                        @endforeach
                    </select>
                    @if(!empty($selectedCategoryId) || !empty($selectedUserId) || !empty($selectedSupplier) || ($selectedYear ?? date('Y')) != date('Y'))
                        <a href="{{ route('dashboard.overview') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </form>

                <!-- User Auth & Role Info -->
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
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-warning btn-sm rounded-pill px-3.5 py-1.5 fw-bold d-flex align-items-center gap-2" style="font-size:0.84rem;">
                        <i class="fa-solid fa-user-lock me-1"></i> Login
                    </a>
                @endauth



                <!-- Jam Realtime -->
                <div class="px-3 py-1.5 rounded-pill d-flex align-items-center gap-2" style="background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); font-size:0.84rem;">
                    <i class="fa-regular fa-clock text-warning me-1"></i>
                    <span id="live-clock" class="fw-bold font-monospace" style="letter-spacing: 0.04em;">00:00:00 WIB</span>
                </div>
            </div>
        </div>

        <!-- Row 2: PillNav Navigation -->
        <div class="top-navbar-row2">
            @include('partials.pill-nav', ['activeRoute' => 'dashboard.overview', 'hasFaqModal' => true])
        </div>
    </nav>

    @include('partials.faq-modal')

    <!-- MAIN CONTAINER -->
    <div class="container-fluid px-3 px-md-4 py-3 py-md-4">
        @include('partials.toast-and-notification-popup')

        <!-- HERO BANNER HEADER -->
        <div class="exchange-hero mb-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;background:rgba(226,179,74,0.18);border:1px solid rgba(226,179,74,0.45);color:#e2b34a;">
                            <i class="bi bi-house-door-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="hero-rate-label" style="font-size:0.75rem;">PURCHASING OVERVIEW &amp; PERFORMANCE</span>
                                @if(!empty($selectedUserId))
                                    @php $activeBuyer = ($buyerUsers ?? collect())->where('id', $selectedUserId)->first(); @endphp
                                    <span class="badge rounded-pill bg-info text-dark fw-bold" style="font-size:0.65rem; padding: 2px 8px;">
                                        <i class="bi bi-person-fill me-1"></i> Filter PIC: {{ $activeBuyer->name ?? 'User '.$selectedUserId }}
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-success bg-opacity-25 text-success border border-success fw-bold" style="font-size:0.65rem; padding: 2px 8px;">
                                        <i class="bi bi-globe me-1"></i> Global
                                    </span>
                                @endif
                            </div>
                            <h2 class="fw-bold text-white mb-0 brand-font" style="font-size:1.55rem; letter-spacing: -0.01em;">Ringkasan Kinerja Purchasing</h2>
                            <p class="text-muted mb-0 mt-1" style="font-size:0.84rem;">
                                Tahun <strong class="text-warning">{{ $selectedYear }}</strong> &bull; Update: <span class="text-light">{{ $lastUpdated }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <button onclick="window.location.reload()" class="btn btn-warning fw-bold px-4 py-2 d-inline-flex align-items-center gap-2 rounded-pill shadow-sm" style="font-size:0.86rem;">
                        <i class="fa-solid fa-rotate-right"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- 4 COMPONENT KPI OVERVIEW CARDS (CONSISTENT & BALANCED) -->
        <div class="row g-3 g-xl-4 mb-4 align-items-stretch">
            <!-- 1. Total Material Terealisasi Diterima -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card kpi-card-gold">
                    <div class="kpi-header">
                        <span class="kpi-title">TOTAL DITERIMA (INCOMING)</span>
                        <div class="kpi-icon-box icon-gold">
                            <i class="fa-solid fa-cubes"></i>
                        </div>
                    </div>
                    <div class="kpi-value text-white">
                        {{ number_format($totalReceived, 0, ',', '.') }} <span class="kpi-unit">unit</span>
                    </div>
                    <div class="kpi-footer">
                        <div class="d-flex align-items-center justify-content-between w-100 flex-nowrap gap-1">
                            <span class="kpi-sub-badge badge-realisasi text-truncate" title="Total Nilai Incoming Material: ${{ number_format($totalAmountReceived ?? 0, 2, '.', ',') }}">
                                <i class="bi bi-currency-dollar"></i>{{ number_format($totalAmountReceived ?? 0, 2, '.', ',') }}
                                <span class="kpi-sub-label">Incoming</span>
                            </span>
                            <span class="text-muted small font-monospace flex-shrink-0" style="font-size: 0.73rem;">
                                <i class="fa-solid fa-circle-check text-success me-1"></i>Masuk
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Target Kebutuhan Order -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card kpi-card-blue">
                    <div class="kpi-header">
                        <span class="kpi-title">TARGET KEBUTUHAN ORDER</span>
                        <div class="kpi-icon-box icon-blue">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <div class="kpi-value text-info">
                        {{ number_format($targetOrder, 0, ',', '.') }} <span class="kpi-unit">unit</span>
                    </div>
                    <div class="kpi-footer">
                        <div class="d-flex align-items-center justify-content-between w-100 flex-nowrap gap-1">
                            <span class="kpi-sub-badge badge-target text-truncate" title="Total Nilai Target Order: ${{ number_format($totalAmountTarget ?? 0, 2, '.', ',') }}">
                                <i class="bi bi-currency-dollar"></i>{{ number_format($totalAmountTarget ?? 0, 2, '.', ',') }}
                                <span class="kpi-sub-label">Target</span>
                            </span>
                            @if(($totalPending ?? 0) > 0)
                                <span class="kpi-sub-badge badge-pending flex-shrink-0" title="Sisa material yang belum diterima: {{ number_format($totalPending) }} unit">
                                    <i class="fa-solid fa-hourglass-half"></i>{{ number_format($totalPending) }} <span class="kpi-sub-label">sisa</span>
                                </span>
                            @else
                                <span class="kpi-sub-badge badge-success-sub flex-shrink-0">
                                    <i class="fa-solid fa-check"></i> <span class="kpi-sub-label">Tuntas</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Persentase Fulfillment -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card kpi-card-emerald">
                    <div class="kpi-header">
                        <span class="kpi-title">PERSENTASE PEMENUHAN</span>
                        <div class="kpi-icon-box icon-emerald">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="kpi-value {{ $fulfillmentPercentage >= 90 ? 'text-success' : 'text-warning' }}">
                        {{ $fulfillmentPercentage }}% <span class="kpi-unit {{ $fulfillmentPercentage >= 90 ? 'text-success' : 'text-warning' }}" style="font-size:0.88rem; font-weight:600;">{{ $fulfillmentPercentage >= 90 ? 'Optimal' : 'In Progress' }}</span>
                    </div>
                    <div class="kpi-footer">
                        <div class="w-100">
                            <div class="d-flex justify-content-between align-items-center mb-1 font-monospace" style="font-size: 0.73rem;">
                                <span class="text-muted">Target: 100%</span>
                                <span class="fw-bold {{ $fulfillmentPercentage >= 90 ? 'text-success' : 'text-warning' }}">
                                    {{ $fulfillmentPercentage >= 90 ? 'Memenuhi Standar' : 'Perlu Pemenuhan' }}
                                </span>
                            </div>
                            <div class="progress progress-thin w-100" style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 4px;">
                                <div class="progress-bar {{ $fulfillmentPercentage >= 90 ? 'bg-success' : 'bg-warning' }}" 
                                     role="progressbar" 
                                     style="width: {{ min($fulfillmentPercentage, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Status Kesehatan Pengadaan & Stok -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card kpi-card-purple">
                    <div class="kpi-header">
                        <span class="kpi-title">STATUS KATEGORI</span>
                        <div class="kpi-icon-box icon-purple">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                    </div>
                    <div class="kpi-value text-white">
                        {{ $activeCategoriesCount }}/{{ $totalCategoriesCount }} <span class="kpi-unit">Kategori Aktif</span>
                    </div>
                    <div class="kpi-footer">
                        <div class="d-flex align-items-center justify-content-between w-100 flex-nowrap gap-1">
                            @if($fulfillmentPercentage >= 85)
                                <span class="kpi-sub-badge badge-health-success text-truncate" title="Status Pengadaan: Aman & Terpenuhi">
                                    <i class="fa-solid fa-circle-check text-success"></i> Terpenuhi
                                </span>
                            @elseif($fulfillmentPercentage >= 50)
                                <span class="kpi-sub-badge badge-health-warning text-truncate" title="Status Pengadaan: Proses">
                                    <i class="fa-solid fa-clock text-warning"></i> Proses
                                </span>
                            @else
                                <span class="kpi-sub-badge badge-health-danger text-truncate" title="Status Pengadaan: Perlu Perhatian">
                                    <i class="fa-solid fa-triangle-exclamation text-danger"></i> Perlu Perhatian
                                </span>
                            @endif

                            <button type="button" class="btn btn-link text-warning p-0 border-0 ms-1 opacity-75 hover-opacity-100 text-decoration-none flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modalFaqPurchasing" title="Panduan & FAQ Indikator">
                                <i class="bi bi-question-circle-fill" style="font-size: 0.95rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ANALYTICS SECTION: Charts & Category Insights -->
        <div class="row g-3 g-xl-4 mb-4">
            <!-- Panel A: Monthly Trend Chart (Target vs Aktual vs Pending) -->
            <div class="col-12 col-lg-7">
                <div class="glass-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">Incoming vs Target vs Pending Material (Per Bulan)</h5>
                            <span class="text-muted" style="font-size: 0.82rem;">Rekapitulasi Jan &ndash; Des {{ $selectedYear }}</span>
                        </div>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <span class="d-flex align-items-center gap-1" style="font-size:0.78rem; color:#38bdf8;"><span style="display:inline-block;width:18px;height:3px;background:#38bdf8;border-radius:2px;"></span> Target</span>
                            <span class="d-flex align-items-center gap-1" style="font-size:0.78rem; color:#f59e0b;"><span style="display:inline-block;width:18px;height:10px;background:#f59e0b;border-radius:3px;"></span> Incoming</span>
                            <span class="d-flex align-items-center gap-1" style="font-size:0.78rem; color:#f87171;"><span style="display:inline-block;width:18px;height:3px;background:#f87171;border-radius:2px; border-top: 2px dashed #f87171;"></span> Pending</span>
                        </div>
                    </div>
                    <div style="height: 310px;">
                        <canvas id="monthlyPurchasingChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Panel B: Pie / Donut Chart (Proporsi Pembelian per Kategori) -->
            <div class="col-12 col-lg-5">
                <div class="glass-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="fa-solid fa-chart-pie text-warning me-1"></i> Proporsi Kategori</h5>
                        </div>
                    </div>
                    <div style="height: 310px;" class="d-flex justify-content-center align-items-center my-auto">
                        <canvas id="categoryContributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Panel C: Category Achievement Breakdown -->
            <div class="col-12">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-layer-group text-warning me-2"></i>Status Per Kategori</h5>
                        </div>
                        <a href="{{ route('purchasing.categories') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3" style="font-size:0.78rem;">
                            <i class="fa-solid fa-sliders me-1"></i> Kelola Master Kategori
                        </a>
                    </div>

                    <div class="row g-3">
                        @forelse($categoryPerformances as $cp)
                            @php
                                $ach   = $cp['achievement'];
                                $color = $ach >= 100 ? '#10b981' : ($ach >= 75 ? '#f59e0b' : '#f87171');
                                $badge = $ach >= 100 ? 'bg-success bg-opacity-25 text-success border-success' : ($ach >= 75 ? 'bg-warning bg-opacity-25 text-warning border-warning' : 'bg-danger bg-opacity-25 text-danger border-danger');
                                $label = $ach >= 100 ? 'Fulfilled' : ($ach >= 75 ? 'On Track' : 'Supply Alert');
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-dark border border-secondary text-warning font-monospace me-1" style="font-size:0.72rem;">{{ $cp['code'] }}</span>
                                                <span class="fw-bold text-white" style="font-size:0.9rem;">{{ $cp['name'] }}</span>
                                                <div class="text-muted mt-1" style="font-size:0.76rem;">
                                                    <i class="fa-solid fa-user-tie me-1"></i>{{ $cp['buyer'] ?? '-' }}
                                                    <span class="badge ms-1" style="background:rgba(255,255,255,0.1); font-size:0.65rem;">{{ $cp['buyer_role'] }}</span>
                                                </div>
                                            </div>
                                            <span class="badge border {{ $badge }} px-2 py-1 rounded-pill" style="font-size:0.7rem;">
                                                {{ $label }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1 mt-3" style="font-size:0.8rem;">
                                            <span class="text-muted">Aktual: <strong class="text-warning">{{ number_format($cp['received']) }}</strong> unit</span>
                                            <span class="text-muted">Target: <strong class="text-info">{{ number_format($cp['target']) }}</strong> unit</span>
                                            <span style="color: {{ $color }}; font-weight:700;">{{ $ach }}%</span>
                                        </div>
                                        <div class="progress" style="height:8px; background:rgba(255,255,255,0.08); border-radius:4px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: {{ min($ach,100) }}%; background: {{ $color }}; border-radius:4px;"
                                                 aria-valuenow="{{ $ach }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center" style="font-size:0.75rem;">
                                        @if($cp['pending'] > 0)
                                            <span class="text-danger fw-bold">
                                                <i class="fa-solid fa-circle-exclamation me-1"></i>Sisa Pending: {{ number_format($cp['pending']) }} unit
                                            </span>
                                        @else
                                            <span class="text-success fw-bold">
                                                <i class="fa-solid fa-circle-check me-1"></i>Tidak ada sisa pending
                                            </span>
                                        @endif
                                        <span class="text-muted">
                                            <i class="fa-solid fa-list-check me-1"></i>{{ $cp['log_count'] }}x Log Input
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4 text-muted">
                                <i class="fa-solid fa-layer-group fs-3 d-block mb-2 opacity-50"></i>
                                Belum ada data kategori material.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION: REKAPITULASI KONTRIBUSI INPUT PER PIC BUYER (KOLABORASI MULTI-USER) -->
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12">
                <div class="glass-card border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold mb-0 brand-font text-white">Kontribusi Per PIC Buyer</h5>
                            </div>
                        </div>
                        <span class="badge bg-dark border border-secondary text-warning font-monospace">
                            {{ count($buyerContributions ?? []) }} User Terdaftar
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>NAMA PERSONEL / PIC BUYER</th>
                                    <th>ROLE &amp; TUGAS</th>
                                    <th>KATEGORI MATERIAL DIKELOLA</th>
                                    <th class="text-center">AKTIVITAS INPUT</th>
                                    <th class="text-end">TARGET ORDER</th>
                                    <th class="text-end">REALISASI DITERIMA</th>
                                    <th class="text-end">SISA PENDING</th>
                                    <th class="text-center">CAPAIAN (%)</th>
                                    <th class="text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($buyerContributions ?? []) as $bc)
                                    @php
                                        $bAch = $bc['achievement'];
                                        $bColor = $bAch >= 90 ? 'text-success' : ($bc['log_count'] > 0 ? 'text-warning' : 'text-muted');
                                        $bBadge = $bAch >= 90 ? 'bg-success bg-opacity-25 text-success border-success' : ($bc['log_count'] > 0 ? 'bg-warning bg-opacity-25 text-warning border-warning' : 'bg-secondary bg-opacity-25 text-muted border-secondary');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-dark border border-secondary text-warning fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($bc['name'], 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-white">{{ $bc['name'] }}</span>
                                                    <div class="text-muted small" style="font-size: 0.72rem;">{{ $bc['email'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark border border-secondary text-light font-monospace" style="font-size:0.75rem;">
                                                {{ $bc['role'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-light" style="font-size: 0.82rem;">{{ $bc['category_name'] }}</span>
                                        </td>
                                        <td class="text-center font-monospace">
                                            <span class="badge bg-dark border border-secondary text-info">
                                                {{ $bc['log_count'] }}x Transaksi
                                            </span>
                                        </td>
                                        <td class="text-end font-monospace text-info fw-bold">{{ number_format($bc['target']) }} unit</td>
                                        <td class="text-end font-monospace text-emerald fw-bold">{{ number_format($bc['received']) }} unit</td>
                                        <td class="text-end font-monospace {{ $bc['pending'] > 0 ? 'text-danger' : 'text-muted' }} fw-bold">
                                            {{ number_format($bc['pending']) }} unit
                                        </td>
                                        <td class="text-center font-monospace fw-bold {{ $bColor }}">
                                            {{ $bAch }}%
                                        </td>
                                        <td class="text-center">
                                            <span class="badge border {{ $bBadge }} rounded-pill px-2.5 py-1" style="font-size:0.72rem;">
                                                {{ $bc['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-3 text-muted">Belum ada data kontribusi user.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION NEW: REKAPITULASI STOK BULANAN & PENGGUNAAN KATEGORI (VALUASI FINANSIAL USD) -->
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12">
                <div class="glass-card border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold mb-0 brand-font text-white"><i class="bi bi-cash-stack text-warning me-2"></i>Rekapitulasi Stok &amp; Valuasi Finansial Bulanan (USD)</h5>
                            </div>
                            <div class="text-muted small mt-1">Monitoring arus penerimaan (incoming), pemakaian produksi, dan akumulasi saldo stok berbasis valuasi mata uang USD.</div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-dark border border-success border-opacity-50 text-emerald font-monospace py-1.5 px-2.5">
                                <i class="bi bi-arrow-down-left-circle me-1"></i> Incoming: +${{ number_format($totalStockReceivedUsd ?? 0, 2) }}
                            </span>
                            <span class="badge bg-dark border border-info border-opacity-50 text-info font-monospace py-1.5 px-2.5">
                                <i class="bi bi-arrow-up-right-circle me-1"></i> Pemakaian: -${{ number_format($totalStockProductionUsd ?? 0, 2) }}
                            </span>
                            <span class="badge bg-dark border border-warning border-opacity-50 text-warning font-monospace py-1.5 px-2.5">
                                <i class="bi bi-safe2-fill me-1"></i> Stok Akhir: ${{ number_format($latestStockEndUsd ?? 0, 2) }}
                            </span>
                            <span class="badge bg-dark border border-secondary text-light font-monospace py-1.5 px-2.5">
                                Jan &ndash; Des {{ $selectedYear }}
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th>PERIODE BULAN</th>
                                    <th class="text-end">PENERIMAAN (INCOMING USD)</th>
                                    <th class="text-end">PEMAKAIAN (PRODUKSI USD)</th>
                                    <th class="text-end">ESTIMASI STOK AKHIR (USD)</th>
                                    <th>KATEGORI MATERIAL AKTIF TERPAKAI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sumRecQty = 0;
                                    $sumProdQty = 0;
                                    $sumRecUsd = 0;
                                    $sumProdUsd = 0;
                                @endphp
                                @forelse($monthlyStockBreakdown as $mRow)
                                    @php
                                        $sumRecQty += (float)($mRow['received_qty'] ?? 0);
                                        $sumProdQty += (float)($mRow['production_qty'] ?? 0);
                                        $sumRecUsd += (float)($mRow['received_amount_usd'] ?? 0);
                                        $sumProdUsd += (float)($mRow['production_amount_usd'] ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted font-monospace fw-bold">{{ $mRow['num'] }}</td>
                                        <td class="fw-bold text-white font-monospace">
                                             <i class="fa-regular fa-calendar text-warning me-1"></i> {{ $mRow['label'] }} {{ $selectedYear }}
                                        </td>
                                        <td class="text-end">
                                            <div class="font-monospace text-emerald fw-bold" style="font-size: 0.95rem;">+${{ number_format($mRow['received_amount_usd'] ?? 0, 2) }}</div>
                                            <div class="text-muted small font-monospace" style="font-size: 0.75rem;">+{{ number_format($mRow['received_qty']) }} unit</div>
                                        </td>
                                        <td class="text-end">
                                            <div class="font-monospace text-info fw-bold" style="font-size: 0.95rem;">-${{ number_format($mRow['production_amount_usd'] ?? 0, 2) }}</div>
                                            <div class="text-muted small font-monospace" style="font-size: 0.75rem;">-{{ number_format($mRow['production_qty']) }} unit</div>
                                        </td>
                                        <td class="text-end">
                                            <div class="font-monospace text-warning fw-bold fs-6">${{ number_format($mRow['stock_end_usd'] ?? 0, 2) }}</div>
                                            <div class="text-muted small font-monospace" style="font-size: 0.75rem;">{{ number_format($mRow['stock_end']) }} unit</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse($mRow['categories_used'] as $cName)
                                                    <span class="badge bg-dark border border-secondary text-info font-monospace" style="font-size:0.72rem;">
                                                        <i class="bi bi-tag-fill text-warning me-1"></i>{{ $cName }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted fs-8">-</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">Belum ada data stok bulanan untuk tahun {{ $selectedYear }}.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($monthlyStockBreakdown) > 0)
                                <tfoot class="border-top border-2 border-secondary border-opacity-50 bg-dark bg-opacity-25">
                                    <tr class="fw-bold">
                                        <td colspan="2" class="text-end text-warning font-monospace py-3">TOTAL TAHUNAN {{ $selectedYear }}:</td>
                                        <td class="text-end py-3">
                                            <div class="font-monospace text-emerald fw-bold fs-6">+${{ number_format($sumRecUsd, 2) }}</div>
                                            <div class="text-muted small font-monospace" style="font-size: 0.75rem;">+{{ number_format($sumRecQty) }} unit</div>
                                        </td>
                                        <td class="text-end py-3">
                                            <div class="font-monospace text-info fw-bold fs-6">-${{ number_format($sumProdUsd, 2) }}</div>
                                            <div class="text-muted small font-monospace" style="font-size: 0.75rem;">-{{ number_format($sumProdQty) }} unit</div>
                                        </td>
                                        <td class="text-end py-3">
                                            <div class="font-monospace text-warning fw-bold fs-6">${{ number_format(end($monthlyStockBreakdown)['stock_end_usd'] ?? 0, 2) }}</div>
                                            <div class="text-muted small font-monospace" style="font-size: 0.75rem;">{{ number_format(end($monthlyStockBreakdown)['stock_end'] ?? 0) }} unit</div>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-40 px-2.5 py-1 font-monospace">
                                                <i class="bi bi-check2-circle me-1"></i> Rekap Finansial Terverifikasi
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- DETAILED USER ROLES & ANDIL KERJA TABLE -->
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="fa-solid fa-users-gear text-warning me-2"></i>Tim Purchasing</h5>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-bold">
                                <i class="bi bi-people-fill me-1"></i> Kelola User &amp; Hak Akses
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>NIP / ID</th>
                                    <th>Nama Personel &amp; Divisi</th>
                                    <th>Role Sistem</th>
                                    <th class="text-center">Status Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($userRolesAndAndil ?? []) as $user)
                                    @php
                                        $isUserLoggedIn   = Auth::check();
                                        $currentUser      = Auth::user();
                                        $isThisUserOnline = false;

                                        // Hanya user yang sedang login (berdasarkan email atau ID) yang ditandai ONLINE
                                        if ($isUserLoggedIn && $currentUser) {
                                            $uEmail = strtolower($user['email'] ?? '');
                                            $uId    = (int) ($user['id'] ?? 0);
                                            $cEmail = strtolower($currentUser->email ?? '');
                                            $cId    = (int) ($currentUser->id ?? 0);

                                            if (
                                                ($uEmail && $cEmail && $uEmail === $cEmail) ||
                                                ($uId > 0 && $cId > 0 && $uId === $cId)
                                            ) {
                                                $isThisUserOnline = true;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-dark border border-secondary text-light font-monospace px-2 py-1">
                                                {{ $user['nip'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                                    {{ substr($user['name'], 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-white fs-6">{{ $user['name'] }}</div>
                                                    <div class="text-muted small">{{ $user['department'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $user['role_badge'] }} px-3 py-2 fw-semibold" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-user-shield me-1"></i> {{ strtoupper($user['role']) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($isThisUserOnline)
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-3 py-1">
                                                    <i class="fa-solid fa-circle-check me-1 text-success"></i> ONLINE
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-25 text-muted border border-secondary px-3 py-1" style="background: rgba(148, 163, 184, 0.12) !important; color: #94a3b8 !important;">
                                                    <i class="fa-solid fa-power-off me-1"></i> Offline
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION SINERGI MASTER PO: LIVE MONITORING DASHBOARD MASTER PO (CLIENT-SIDE / BULK PO) -->
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12">
                <div class="glass-card border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold mb-0 brand-font text-success">Master PO</h5>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span class="badge bg-dark border border-secondary text-light px-3 py-2 font-monospace">
                                <i class="bi bi-list-check me-1"></i> Total PO: <strong class="text-success">{{ number_format($masterPoTotalCount ?? 0) }} Baris</strong>
                            </span>
                            <span class="badge bg-dark border border-secondary text-light px-3 py-2 font-monospace">
                                <i class="bi bi-box-seam me-1"></i> Total Qty: <strong class="text-warning">{{ number_format($masterPoTotalQty ?? 0) }} Unit</strong>
                            </span>
                            <a href="{{ route('purchasing.master-po') }}" class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                                <span>Kelola & Input Master PO</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>TANGGAL PO</th>
                                    <th>SUPPLIER</th>
                                    <th>PO NUMBER</th>
                                    <th>ITEM CODE</th>
                                    <th>NAME / DESCRIPTION</th>
                                    <th class="text-end">QTY ORDER</th>
                                    <th class="text-center">AKSI CEPAT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($latestMasterPos ?? []) as $mp)
                                    <tr>
                                        <td class="font-monospace fw-semibold text-light">{{ $mp->tanggal ? \Carbon\Carbon::parse($mp->tanggal)->format('d/m/Y') : '-' }}</td>
                                        <td>
                                            <span class="badge bg-dark border border-secondary text-info font-monospace px-2 py-1">{{ $mp->supplier ?? '-' }}</span>
                                        </td>
                                        <td class="font-monospace fw-bold text-success">{{ $mp->po ?? '-' }}</td>
                                        <td class="font-monospace fw-bold text-warning">{{ $mp->item_code ?? '-' }}</td>
                                        <td class="text-white fw-semibold">{{ $mp->name ?? '-' }}</td>
                                        <td class="text-end font-monospace text-warning fw-bold fs-6">{{ number_format($mp->qty ?? 0) }} <span class="fs-7 fw-normal text-muted">unit</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('purchasing.master-po') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                Edit PO <i class="fa-solid fa-pen-to-square ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                            Belum ada data Master PO terdaftar. Klik tombol <b>Kelola & Input Master PO</b> untuk menambahkan data.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION SINERGI 1: LIVE MONITORING OUTSTANDING PO & ALUR IAD -->
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12">
                <div class="glass-card border-start border-4 border-warning shadow-lg">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold mb-0 brand-font text-warning">Outstanding PO</h5>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-dark border border-warning border-opacity-30 text-warning font-monospace fs-7">
                                <i class="bi bi-currency-dollar me-1"></i> Total Nilai Outstandings: <strong class="text-white">$ {{ number_format($outstandingsTotalAmount ?? 0, 2, '.', ',') }}</strong>
                            </div>
                            <a href="{{ route('purchasing.outstanding') }}" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                                <span>Buka Halaman Outstanding PO</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Live Table 5 Outstanding Terbaru -->
                    <div class="table-responsive style-scrollbar">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>PO NUMBER</th>
                                    <th>PART NUMBER</th>
                                    <th>DESCRIPTION &amp; SUPPLIER</th>
                                    <th class="text-end text-info">TARGET QTY PO</th>
                                    <th class="text-end text-success">AMOUNT ($)</th>
                                    <th class="text-center">AKSI CEPAT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestOutstandings as $lpo)
                                    <tr>
                                        <td class="font-monospace fw-bold text-info">{{ $lpo->po_number }}</td>
                                        <td class="font-monospace fw-bold text-warning">{{ $lpo->part_number }}</td>
                                        <td>
                                            <div class="text-white fw-semibold">{{ $lpo->description }}</div>
                                            <div class="text-muted small">{{ $lpo->supplier_name }}</div>
                                        </td>
                                        <td class="text-end font-monospace fw-bold text-info fs-7">{{ number_format($lpo->computed_order_qty) }} unit</td>
                                        <td class="text-end font-monospace">
                                             <span class="badge font-monospace px-3 py-1.5 fs-7 rounded-pill" style="background: rgba(16, 185, 129, 0.22); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.5); font-weight: 700; letter-spacing: 0.3px;">
                                                <i class="bi bi-currency-dollar me-0.5"></i>{{ number_format($lpo->computed_amount, 2, '.', ',') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('purchasing.outstanding') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                                Detail PO <i class="fa-solid fa-arrow-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">Belum ada data Outstanding PO.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION SINERGI 2: LIVE MONITORING INPUT INCOMING BULANAN -->
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12">
                <div class="glass-card border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold mb-0 brand-font text-info">Incoming Material</h5>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('purchasing.input') }}" class="btn btn-info btn-sm rounded-pill px-4 fw-bold shadow-sm text-dark d-flex align-items-center gap-2">
                                <span>Buka Halaman Input Incoming Bulanan</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 38px;"></th>
                                    <th>PERIODE BULAN</th>
                                    <th>KATEGORI MATERIAL</th>
                                    <th class="text-end">TARGET ORDER</th>
                                    <th class="text-end">AKTUAL DITERIMA</th>
                                    <th class="text-end">PENDING ORDER</th>
                                    <th style="min-width: 130px;">ACHIEVEMENT %</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">RINCIAN TANGGAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestInputLogs as $log)
                                    @php
                                        $isArr = is_array($log);
                                        $grpKey = $isArr ? $log['group_key'] : ('grp_' . $log->id);
                                        $periodMonth = $isArr ? $log['period_month'] : $log->period_month;
                                        $catCode = $isArr ? $log['category_code'] : ($log->category->category_code ?? '-');
                                        $catName = $isArr ? $log['category_name'] : ($log->category->category_name ?? 'Material Umum');
                                        $targetOrder = $isArr ? $log['target_order'] : $log->target_order;
                                        $actualReceived = $isArr ? $log['actual_received'] : $log->actual_received;
                                        $pendingOrder = $isArr ? $log['pending_order'] : max(0, $targetOrder - $actualReceived);
                                        $ach = $isArr ? $log['achievement'] : ($targetOrder > 0 ? round(($actualReceived / $targetOrder) * 100, 1) : 0);
                                        $transCount = $isArr ? $log['trans_count'] : 1;
                                        $details = $isArr ? $log['details'] : [];
                                    @endphp
                                    
                                    <!-- MAIN BARIS REKAPITULASI BULANAN -->
                                    <tr class="main-log-row" style="cursor: pointer;" onclick="toggleLogDetail('{{ $grpKey }}')">
                                        <td class="text-center">
                                            <i class="fa-solid fa-chevron-right text-warning" id="icon-{{ $grpKey }}" style="transition: transform 0.25s;"></i>
                                        </td>
                                        <td class="fw-bold text-white font-monospace">
                                            <i class="fa-regular fa-calendar-days text-info me-1"></i>
                                            {{ \Carbon\Carbon::parse($periodMonth)->translatedFormat('F Y') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-dark border border-secondary text-info font-monospace me-1">{{ $catCode }}</span>
                                            <span class="text-white fw-semibold">{{ $catName }}</span>
                                        </td>
                                        <td class="text-end font-monospace text-info fw-bold">{{ number_format($targetOrder) }} unit</td>
                                        <td class="text-end font-monospace text-warning fw-bold fs-6">{{ number_format($actualReceived) }} unit</td>
                                        <td class="text-end font-monospace {{ $pendingOrder > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ number_format($pendingOrder) }} unit</td>
                                        <td>
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="fw-bold {{ $ach >= 90 ? 'text-success' : 'text-warning' }}">{{ $ach }}%</span>
                                            </div>
                                            <div class="progress progress-thin" style="height: 6px; background: rgba(255,255,255,0.08);">
                                                <div class="progress-bar {{ $ach >= 90 ? 'bg-success' : 'bg-warning' }}" style="width: {{ min($ach, 100) }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($actualReceived < $targetOrder)
                                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-3 py-1 rounded-pill mb-1 d-block" style="font-size: 0.72rem;">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Kurang {{ number_format($targetOrder - $actualReceived, 0, ',', '.') }}
                                                </span>
                                            @elseif($actualReceived > $targetOrder)
                                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-3 py-1 rounded-pill mb-1 d-block" style="font-size: 0.72rem;">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Lebih +{{ number_format($actualReceived - $targetOrder, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-3 py-1 rounded-pill" style="font-size: 0.72rem;">
                                                    <i class="fa-solid fa-check-circle me-1"></i> Sesuai Target
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-bold" onclick="event.stopPropagation(); toggleLogDetail('{{ $grpKey }}')" style="font-size:0.75rem;">
                                                <i class="fa-solid fa-list-ul me-1"></i> Rincian ({{ $transCount }})
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- COLLAPSIBLE RINCIAN SUB-TRANSAKSI TANGGAL -->
                                    <tr id="detail-{{ $grpKey }}" class="d-none bg-dark bg-opacity-50">
                                        <td colspan="9" class="p-0 border-0">
                                            <div class="p-3 m-2 rounded-3 border border-secondary border-opacity-25" style="background: rgba(15, 23, 42, 0.95);">
                                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-25">
                                                    <div class="fw-bold text-warning small">
                                                        <i class="fa-solid fa-receipt me-1"></i> Rincian Transaksi Penerimaan {{ \Carbon\Carbon::parse($periodMonth)->translatedFormat('F Y') }}
                                                    </div>
                                                    <span class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-1 font-monospace" style="font-size:0.75rem;">
                                                        Total Aktual: {{ number_format($actualReceived) }} / {{ number_format($targetOrder) }} unit
                                                    </span>
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-sm table-dark table-hover mb-0 align-middle" style="font-size: 0.82rem; background: transparent;">
                                                        <thead>
                                                            <tr class="text-muted border-bottom border-secondary border-opacity-25">
                                                                <th>#</th>
                                                                <th>TANGGAL PENGIRIMAN / RECEIPT</th>
                                                                <th>NO. PO / REF</th>
                                                                <th>SUPPLIER</th>
                                                                <th class="text-end">QTY DITERIMA</th>
                                                                <th class="text-end">PENDING LOG</th>
                                                                <th>PETUGAS INPUT</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($details as $dIdx => $dt)
                                                                <tr>
                                                                    <td class="text-muted font-monospace">{{ $dIdx + 1 }}</td>
                                                                    <td class="fw-bold text-info font-monospace">
                                                                        <i class="fa-regular fa-calendar text-warning me-1"></i>
                                                                        {{ $dt['receipt_date'] }}
                                                                    </td>
                                                                    <td class="font-monospace fw-bold">
                                                                        <span class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-1">
                                                                            <i class="bi bi-file-earmark-text me-1"></i>{{ $dt['po_number'] ?? '-' }}
                                                                        </span>
                                                                        @if(!empty($dt['item_code']) && $dt['item_code'] !== '-')
                                                                            <span class="badge bg-primary bg-opacity-25 text-info border border-info px-2 py-1 ms-1" style="font-size:0.7rem;">
                                                                                Item: {{ $dt['item_code'] }}
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td><span class="badge bg-dark border border-secondary text-light">{{ $dt['supplier_name'] }}</span></td>
                                                                    <td class="text-end font-monospace text-warning fw-bold">+{{ number_format($dt['actual_received']) }} unit</td>
                                                                    <td class="text-end font-monospace text-muted">{{ number_format($dt['pending_diff']) }} unit</td>
                                                                    <td class="text-light small"><i class="fa-solid fa-user-check text-success me-1"></i>{{ $dt['user_name'] }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="mt-2 pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center small text-muted">
                                                    <div><i class="fa-solid fa-calculator text-warning me-1"></i> Penjumlahan Aktual: @foreach($details as $dIdx => $dt) {{ number_format($dt['actual_received']) }} @if(!$loop->last) + @endif @endforeach = <strong class="text-warning">{{ number_format($actualReceived) }} unit</strong></div>
                                                    <div>Sisa Kebutuhan Bulan {{ \Carbon\Carbon::parse($periodMonth)->translatedFormat('F Y') }}: <strong class="{{ $pendingOrder > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($pendingOrder) }} unit</strong></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-3 text-muted">Belum ada data incoming bulanan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <script>
                    function toggleLogDetail(grpKey) {
                        const detailRow = document.getElementById('detail-' + grpKey);
                        const icon = document.getElementById('icon-' + grpKey);
                        if (detailRow) {
                            if (detailRow.classList.contains('d-none')) {
                                detailRow.classList.remove('d-none');
                                if (icon) icon.style.transform = 'rotate(90deg)';
                            } else {
                                detailRow.classList.add('d-none');
                                if (icon) icon.style.transform = 'rotate(0deg)';
                            }
                        }
                    }
                    </script>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <footer class="mt-5 pt-4 border-top border-secondary border-opacity-25 text-center text-muted" style="font-size: 0.82rem;">
            <p class="mb-1">
                &copy; {{ date('Y') }} PT KAWAI INDONESIA &bull; Dashboard Monitoring Purchasing
            </p>

        </footer>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- CHART.JS BULANAN LOGIC -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
            const el = document.getElementById('live-clock');
            if (el) el.innerText = timeStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // ─── Enhanced Monthly Chart: Target vs Aktual vs Pending ───
        const canvasMonthly = document.getElementById('monthlyPurchasingChart');
        if (canvasMonthly) {
            const ctxMonthly = canvasMonthly.getContext('2d');
            const gradReceived = ctxMonthly.createLinearGradient(0, 0, 0, 280);
            gradReceived.addColorStop(0, 'rgba(245, 158, 11, 0.90)');
            gradReceived.addColorStop(1, 'rgba(245, 158, 11, 0.20)');

            window.chartMonthlyPurchasing = new Chart(ctxMonthly, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlyLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']) !!},
                    datasets: [
                        {
                            label: 'Aktual Diterima',
                            data: {!! json_encode($monthlyReceived ?? array_fill(0, 12, 0)) !!},
                            backgroundColor: gradReceived,
                            borderColor: '#f59e0b',
                            borderWidth: 1.5,
                            borderRadius: 5,
                            order: 3
                        },
                        {
                            label: 'Target Order',
                            data: {!! json_encode($monthlyTarget ?? array_fill(0, 12, 0)) !!},
                            type: 'line',
                            borderColor: '#38bdf8',
                            backgroundColor: 'rgba(56,189,248,0.08)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#38bdf8',
                            pointBorderColor: '#ffffff',
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            tension: 0.4,
                            fill: false,
                            order: 1
                        },
                        {
                            label: 'Pending Order',
                            data: {!! json_encode($monthlyPending ?? array_fill(0, 12, 0)) !!},
                            type: 'line',
                            borderColor: '#f87171',
                            backgroundColor: 'rgba(248,113,113,0.06)',
                            borderWidth: 2,
                            borderDash: [6, 4],
                            pointBackgroundColor: '#f87171',
                            pointBorderColor: '#ffffff',
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            tension: 0.3,
                            fill: false,
                            order: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#e2b34a',
                            bodyColor: '#f1f5f9',
                            borderColor: 'rgba(226,179,74,0.4)',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    return ' ' + ctx.dataset.label + ': ' + (ctx.parsed.y || 0).toLocaleString('id-ID') + ' unit';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255,255,255,0.06)' },
                            ticks: { color: '#94a3b8', font: { family: 'Inter', size: 11, weight: '600' } }
                        },
                        y: {
                            grid: { color: 'rgba(255,255,255,0.06)' },
                            ticks: {
                                color: '#94a3b8',
                                font: { family: 'Inter', size: 11 },
                                callback: function(v) { return Number(v).toLocaleString('id-ID'); }
                            },
                            title: { display: true, text: 'Jumlah Unit', color: '#64748b', font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // ─── Chart 2: Category Contribution & Volume Pie/Doughnut Chart ───
        const canvasCategory = document.getElementById('categoryContributionChart');
        if (canvasCategory) {
            const ctxCategory = canvasCategory.getContext('2d');
            const catLabels = {!! json_encode($categoryNames ?? []) !!};
            const catData = {!! json_encode($categoryReceiveds ?? $categoryReceived ?? []) !!};
            const catLogCounts = {!! json_encode($categoryLogCounts ?? []) !!};

            window.chartCategoryContribution = new Chart(ctxCategory, {
                type: 'doughnut',
                data: {
                    labels: catLabels.length > 0 ? catLabels : ['Belum Ada Kategori'],
                    datasets: [{
                        data: catData.length > 0 ? catData : [0],
                        backgroundColor: [
                            '#38bdf8',
                            '#f59e0b',
                            '#10b981',
                            '#a855f7',
                            '#ec4899',
                            '#f97316'
                        ],
                        borderColor: '#0f172a',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#cbd5e1',
                                font: { family: 'Inter', size: 11, weight: '500' },
                                boxWidth: 12,
                                padding: 12
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#e2b34a',
                            bodyColor: '#f1f5f9',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const idx = ctx.dataIndex;
                                    const val = (ctx.parsed || 0).toLocaleString('id-ID');
                                    const cnt = catLogCounts[idx] || 0;
                                    return [' Incoming: ' + val + ' unit', ' Frekuensi Input: ' + cnt + 'x transaksi'];
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
