<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspeksi Dashboard Staff | PT Kawai Indonesia</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">

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

        h1, h2, h3, h4, h5, h6, .brand-font {
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

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(12px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-2px);
            border-color: rgba(0, 210, 255, 0.3);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45), 0 0 15px rgba(0, 210, 255, 0.1);
        }

        .inspect-banner {
            background: linear-gradient(135deg, rgba(18, 24, 38, 0.95) 0%, rgba(10, 14, 23, 0.98) 100%);
            border: 1px solid rgba(0, 210, 255, 0.35);
            border-radius: 20px;
            padding: 1.75rem 2.25rem;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5), 0 0 20px rgba(0, 210, 255, 0.12);
            position: relative;
            overflow: hidden;
        }

        .inspect-banner::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background: radial-gradient(circle at top right, rgba(0, 210, 255, 0.12) 0%, transparent 60%);
            pointer-events: none;
        }

        .user-avatar-circle {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00d2ff 0%, #3b82f6 100%);
            color: #0a0e17;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.85rem;
            box-shadow: 0 0 22px rgba(0, 210, 255, 0.45);
            border: 3px solid rgba(255, 255, 255, 0.9);
            flex-shrink: 0;
            margin-right: 1.35rem !important;
        }

        .kpi-title {
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.4rem;
        }

        .kpi-value {
            font-family: 'Outfit', sans-serif;
            font-size: 2.15rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.3rem;
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .icon-gold { background: rgba(226, 179, 74, 0.18); color: var(--accent-gold); border: 1px solid rgba(226, 179, 74, 0.35); }
        .icon-blue { background: rgba(0, 210, 255, 0.18); color: var(--accent-cyan); border: 1px solid rgba(0, 210, 255, 0.35); }
        .icon-emerald { background: rgba(16, 185, 129, 0.18); color: var(--accent-emerald); border: 1px solid rgba(16, 185, 129, 0.35); }
        .icon-purple { background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35); }

        .nav-tabs-step {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            gap: 8px;
            display: flex;
            flex-wrap: wrap;
        }

        .nav-tabs-step .nav-link {
            color: var(--text-muted);
            background: rgba(18, 24, 38, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.7rem 1.15rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-tabs-step .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .nav-tabs-step .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, rgba(0, 210, 255, 0.22) 0%, rgba(59, 130, 246, 0.22) 100%);
            border: 1px solid rgba(0, 210, 255, 0.55);
            box-shadow: 0 4px 15px rgba(0, 210, 255, 0.18);
            font-weight: 700;
        }

        .step-badge {
            font-size: 0.7rem;
            padding: 2px 7px;
            border-radius: 999px;
            font-family: monospace;
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
        }

        .nav-tabs-step .nav-link.active .step-badge {
            background: #00d2ff;
            color: #0a0e17;
            font-weight: 800;
        }

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        .table-custom th {
            background: rgba(15, 23, 42, 0.95) !important;
            color: #d1d5db !important;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 1rem;
            border-bottom: 1px solid var(--card-border) !important;
            vertical-align: middle;
        }

        .table-custom td {
            background-color: transparent !important;
            color: var(--text-main) !important;
            padding: 0.95rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .table-custom tbody tr:hover td {
            background: rgba(255, 255, 255, 0.04) !important;
        }

        .style-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .style-scrollbar::-webkit-scrollbar-track {
            background: rgba(10, 14, 23, 0.5);
            border-radius: 4px;
        }
        .style-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 4px;
        }
        .style-scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--accent-gold);
        }
    </style>
</head>
<body>

    <!-- TOP NAVBAR (STICKY) -->
    <nav class="top-navbar">
        <div class="top-navbar-row1 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
            <a href="{{ route('dashboard.overview') }}" class="text-decoration-none" style="color: inherit !important; text-decoration: none !important;">
                <div class="d-flex align-items-center gap-2.5 mb-0.5">
                    <i class="bi bi-music-note-beamed text-warning fs-4" style="line-height: 1; vertical-align: middle;"></i>
                    <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
                </div>
                <div class="text-muted" style="font-size: 0.73rem; letter-spacing: 0.02em; margin-left: 2px; color: #9ca3af !important;">USER DASHBOARD INSPECTION MODE &bull; AUDIT ALUR WORKFLOW</div>
            </a>

            <div class="d-flex align-items-center gap-2 flex-wrap">
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

                <!-- Status Badge -->
                <div class="px-3 py-1.5 rounded-pill d-flex align-items-center gap-2" style="background: rgba(0, 210, 255, 0.12); border: 1px solid rgba(0, 210, 255, 0.4); color: #00d2ff; font-size:0.8rem; font-weight:600;">
                    <i class="bi bi-display me-1.5"></i> Mode Inspeksi User
                </div>

                <!-- Live Clock -->
                <div class="px-3 py-1.5 rounded-pill d-flex align-items-center gap-2" style="background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); font-size:0.84rem;">
                    <i class="fa-regular fa-clock text-warning me-1.5"></i>
                    <span id="live-clock" class="fw-bold font-monospace" style="letter-spacing: 0.04em;">00:00:00 WIB</span>
                </div>
            </div>
        </div>

        <div class="top-navbar-row2">
            @include('partials.pill-nav', ['activeRoute' => 'users.monitoring'])
        </div>
    </nav>

    <!-- MAIN CONTAINER -->
    <div class="container-fluid px-4 py-4">

        <!-- INSPECTION BANNER & STAFF PROFILE -->
        <div class="inspect-banner mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-4">
                <div class="d-flex align-items-center">
                    <div class="user-avatar-circle">
                        {{ strtoupper(substr($targetUser->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                            @php
                                $tRoleBadge = match(strtolower($targetUser->role)) {
                                    'supervisor' => 'bg-danger text-white',
                                    'leader'     => 'bg-info text-dark',
                                    default      => 'bg-success text-white'
                                };
                            @endphp
                            <span class="badge {{ $tRoleBadge }} px-3 py-1 fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.03em;">
                                <i class="fa-solid fa-user-shield me-1.5"></i>{{ $targetUser->role }}
                            </span>
                        </div>
                        <h2 class="fw-bold text-white mb-1.5 brand-font" style="font-size: 1.85rem; line-height:1.2;">{{ $targetUser->name }}</h2>
                        <div class="text-muted small d-flex align-items-center gap-3 flex-wrap" style="font-size:0.88rem;">
                            <span><i class="bi bi-person-badge text-warning me-1.5"></i>Username: <strong class="text-warning font-monospace">{{ $targetUser->username ?: '-' }}</strong></span>
                            <span class="opacity-40">&bull;</span>
                            <span><i class="bi bi-envelope text-info me-1.5"></i>Email: <strong class="text-white">{{ $targetUser->email }}</strong></span>
                            <span class="opacity-40">&bull;</span>
                            <span><i class="bi bi-building text-success me-1.5"></i>Departemen: <strong class="text-white">{{ $targetUser->department ?: 'Group Purchasing' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('users.monitoring') }}" class="btn btn-outline-warning rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" style="font-size:0.88rem;">
                        <i class="bi bi-arrow-left me-1"></i>
                        <span>Kembali ke User Monitoring</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 METRICS SUMMARY CARDS -->
        <div class="row g-4 mb-4">
            <!-- 1. Total Target Order PO -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card h-100 d-flex justify-content-between align-items-start p-3.5">
                    <div style="min-width: 0; flex: 1; margin-right: 0.5rem;">
                        <div class="kpi-title text-truncate">TOTAL TARGET ORDER PO</div>
                        <div class="kpi-value text-info text-nowrap" style="font-size: 1.9rem;">
                            {{ number_format($totalPoTarget, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">unit</span>
                        </div>
                        <div class="text-muted small mt-2 d-flex align-items-center gap-1.5" style="font-size:0.8rem;">
                            <i class="bi bi-file-earmark-text text-info me-1"></i>Total pesanan PO oleh user ini
                        </div>
                    </div>
                    <div class="kpi-icon icon-blue">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Total Incoming Diterima -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card h-100 d-flex justify-content-between align-items-start p-3.5">
                    <div style="min-width: 0; flex: 1; margin-right: 0.5rem;">
                        <div class="kpi-title text-truncate">TOTAL INCOMING DITERIMA</div>
                        <div class="kpi-value text-white text-nowrap" style="font-size: 1.9rem;">
                            {{ number_format($totalActualReceived, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">unit</span>
                        </div>
                        <div class="text-muted small mt-2 d-flex align-items-center gap-1.5" style="font-size:0.8rem;">
                            <i class="bi bi-box-arrow-in-down text-warning me-1"></i>Material di-input terima
                        </div>
                    </div>
                    <div class="kpi-icon icon-gold">
                        <i class="fa-solid fa-cubes"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Persentase Pemenuhan -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card h-100 d-flex justify-content-between align-items-start p-3.5">
                    <div style="min-width: 0; flex: 1; margin-right: 0.5rem;">
                        <div class="kpi-title text-truncate">PERSENTASE PEMENUHAN</div>
                        <div class="kpi-value {{ $fulfillmentPct >= 90 ? 'text-success' : ($fulfillmentPct >= 50 ? 'text-warning' : 'text-danger') }}" style="font-size: 1.9rem;">
                            {{ $fulfillmentPct }}%
                        </div>
                        <div class="progress progress-thin mt-2" style="height: 6px; background: rgba(255,255,255,0.1); border-radius: 4px;">
                            <div class="progress-bar {{ $fulfillmentPct >= 90 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ min($fulfillmentPct, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="kpi-icon icon-emerald">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                </div>
            </div>

            <!-- 4. Status Selisih Penerimaan -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card h-100 d-flex justify-content-between align-items-start p-3.5">
                    <div style="min-width: 0; flex: 1; margin-right: 0.5rem;">
                        <div class="kpi-title text-truncate">STATUS SELISIH PENERIMAAN</div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-warning text-dark px-2.5 py-1.5 fw-bold" style="font-size:0.78rem;">
                                <i class="bi bi-exclamation-circle me-1.5"></i>{{ $underDeliveryLogsCount }} Under
                            </span>
                            <span class="badge bg-danger text-white px-2.5 py-1.5 fw-bold" style="font-size:0.78rem;">
                                <i class="bi bi-box-arrow-up-right me-1.5"></i>{{ $overDeliveryLogsCount }} Over
                            </span>
                        </div>
                        <div class="text-muted small mt-2" style="font-size:0.78rem;">
                            Selisih penerimaan vs target PO
                        </div>
                    </div>
                    <div class="kpi-icon icon-purple">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABS WORKFLOW STEP PER USER -->
        <div class="glass-card mb-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-white mb-1 brand-font"><i class="fa-solid fa-layer-group text-warning me-2.5"></i>Inspeksi Alur 6 Step Pengadaan Material</h5>
                    <p class="text-muted mb-0 small">Audit rincian data per tahapan workflow Step 1 s/d Step 6 khusus milik {{ $targetUser->name }}</p>
                </div>
            </div>

            <ul class="nav nav-tabs-step mb-4" id="inspectTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="forecast-tab" data-bs-toggle="tab" data-bs-target="#step-forecast" type="button" role="tab">
                        <i class="bi bi-chart-line text-primary me-2"></i>Step 1: Master Forecast
                        <span class="step-badge ms-1.5">{{ $forecasts->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="masterpo-tab" data-bs-toggle="tab" data-bs-target="#step-masterpo" type="button" role="tab">
                        <i class="bi bi-file-earmark-text text-success me-2"></i>Step 2: Master PO
                        <span class="step-badge ms-1.5">{{ $masterPos->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="input-tab" data-bs-toggle="tab" data-bs-target="#step-input" type="button" role="tab">
                        <i class="bi bi-box-arrow-in-down text-info me-2"></i>Step 3: Incoming Penerimaan
                        <span class="step-badge ms-1.5">{{ $purchasingLogs->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="outstandingpo-tab" data-bs-toggle="tab" data-bs-target="#step-outstandingpo" type="button" role="tab">
                        <i class="bi bi-clock-history text-danger me-2"></i>Step 4: Outstanding PO
                        <span class="step-badge ms-1.5">{{ count($outstandingPos) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="production-tab" data-bs-toggle="tab" data-bs-target="#step-production" type="button" role="tab">
                        <i class="bi bi-gear-wide-connected text-warning me-2"></i>Step 5: Aktual Produksi
                        <span class="step-badge ms-1.5">{{ $actualProductions->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="comparison-tab" data-bs-toggle="tab" data-bs-target="#step-comparison" type="button" role="tab">
                        <i class="bi bi-award-fill text-info me-2"></i>Step 6: Hasil Akhir &amp; Komparasi
                        <span class="step-badge ms-1.5">{{ count($comparisonList) }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="inspectTabContent">

                <!-- TAB 1: STEP 1 MASTER FORECAST -->
                <div class="tab-pane fade show active" id="step-forecast" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0 brand-font"><i class="bi bi-bar-chart-fill text-primary me-2.5"></i>Data Master Forecast &amp; Rencana Stok (Pengisian {{ $targetUser->name }})</h6>
                    </div>
                    <div class="table-responsive style-scrollbar" style="max-height: 480px; overflow-y: auto;">
                        <table class="table-custom align-middle">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th>PART NUMBER / MATERIAL</th>
                                    <th>DESKRIPSI ITEM</th>
                                    <th class="text-center">PERIODE</th>
                                    <th class="text-end">FORECAST QTY</th>
                                    <th class="text-end">CALCULATED PO</th>
                                    <th class="text-end">INCOMING</th>
                                    <th class="text-end">OUTSTANDING</th>
                                    <th class="text-end">STOCK AKHIR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($forecasts as $idx => $fc)
                                    <tr>
                                        <td class="text-center text-muted font-monospace fw-bold">{{ $idx + 1 }}</td>
                                        <td class="fw-bold text-warning font-monospace">{{ $fc->part_number }}</td>
                                        <td class="text-white small fw-semibold">{{ $fc->description ?: '-' }}</td>
                                        <td class="text-center font-monospace text-info small">{{ $fc->periode }}</td>
                                        <td class="text-end font-monospace text-info fw-bold">{{ number_format($fc->forecast_qty) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                        <td class="text-end font-monospace text-emerald fw-bold">{{ number_format($fc->calculated_po) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                        <td class="text-end font-monospace text-white">{{ number_format($fc->calculated_delivery) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                        <td class="text-end font-monospace text-warning fw-bold">{{ number_format($fc->calculated_outstanding) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                        <td class="text-end font-monospace text-white fw-bold">{{ number_format($fc->calculated_stock) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                            Belum ada data Master Forecast yang dibuat oleh {{ $targetUser->name }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: STEP 2 MASTER PO -->
                <div class="tab-pane fade" id="step-masterpo" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0 brand-font"><i class="bi bi-file-earmark-spreadsheet text-success me-2.5"></i>Daftar Master PO (Dibuat oleh {{ $targetUser->name }})</h6>
                    </div>
                    <div class="table-responsive style-scrollbar" style="max-height: 480px; overflow-y: auto;">
                        <table class="table-custom align-middle">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th>TANGGAL ORDER</th>
                                    <th>SUPPLIER</th>
                                    <th>NOMOR PO</th>
                                    <th>ITEM CODE</th>
                                    <th>NAMA MATERIAL</th>
                                    <th class="text-end">QTY ORDER</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($masterPos as $idx => $mp)
                                    <tr>
                                        <td class="text-center text-muted font-monospace fw-bold">{{ $idx + 1 }}</td>
                                        <td class="font-monospace text-light small">{{ date('d/m/Y', strtotime($mp->tanggal)) }}</td>
                                        <td><span class="badge bg-dark border border-secondary text-info font-monospace px-2 py-1">{{ $mp->supplier ?: '-' }}</span></td>
                                        <td class="fw-bold text-success font-monospace">{{ $mp->po }}</td>
                                        <td class="fw-bold text-warning font-monospace">{{ $mp->item_code }}</td>
                                        <td class="text-white small fw-semibold">{{ $mp->name ?: '-' }}</td>
                                        <td class="text-end font-monospace text-warning fw-bold fs-6">{{ number_format($mp->qty) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                            Belum ada Master PO yang di-input oleh {{ $targetUser->name }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: STEP 3 INCOMING PENERIMAAN PO -->
                <div class="tab-pane fade" id="step-input" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0 brand-font"><i class="bi bi-box-arrow-in-down text-info me-2.5"></i>Log Incoming Penerimaan PO (Di-entry oleh {{ $targetUser->name }})</h6>
                    </div>
                    <div class="table-responsive style-scrollbar" style="max-height: 480px; overflow-y: auto;">
                        <table class="table-custom align-middle">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th>TANGGAL TERIMA</th>
                                    <th>ITEM CODE</th>
                                    <th>PO REFERENCE</th>
                                    <th>MATERIAL / SUPPLIER</th>
                                    <th class="text-end">TARGET PO</th>
                                    <th class="text-end">AKTUAL DITERIMA</th>
                                    <th class="text-center">STATUS SELISIH</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchasingLogs as $idx => $lg)
                                    <tr>
                                        <td class="text-center text-muted font-monospace fw-bold">{{ $idx + 1 }}</td>
                                        <td class="font-monospace text-info small">{{ date('d/m/Y', strtotime($lg->receipt_date)) }}</td>
                                        <td class="fw-bold text-warning font-monospace">{{ $lg->item_code }}</td>
                                        <td class="fw-bold text-success font-monospace">{{ $lg->po_reference }}</td>
                                        <td>
                                            <div class="text-white small fw-semibold">{{ $lg->item_name }}</div>
                                            <div class="fs-8 text-muted">{{ $lg->supplier_name }}</div>
                                        </td>
                                        <td class="text-end font-monospace text-info fw-bold">{{ number_format($lg->target_order) }} unit</td>
                                        <td class="text-end font-monospace text-warning fw-bold fs-6">{{ number_format($lg->actual_received) }} unit</td>
                                        <td class="text-center">
                                            @if((int)$lg->actual_received < (int)$lg->target_order)
                                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-2.5 py-1 rounded-pill" style="font-size:0.72rem;">Under-Incoming</span>
                                            @elseif((int)$lg->actual_received > (int)$lg->target_order)
                                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-2.5 py-1 rounded-pill" style="font-size:0.72rem;">Over-Incoming</span>
                                            @else
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-2.5 py-1 rounded-pill" style="font-size:0.72rem;">Lengkap</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                            Belum ada log incoming penerimaan yang di-input oleh {{ $targetUser->name }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 4: STEP 4 OUTSTANDING PO (WITH DIAGRAM) -->
                <div class="tab-pane fade" id="step-outstandingpo" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0 brand-font"><i class="bi bi-clock-history text-danger me-2.5"></i>Daftar Outstanding PO Belum Terpenuhi (Ditangani {{ $targetUser->name }})</h6>
                    </div>

                    <!-- DIAGRAM CHART STEP 4 -->
                    @if(count($outstandingPos) > 0)
                        <div class="row g-4 mb-4">
                            <div class="col-12 col-lg-8">
                                <div class="p-3.5 rounded-3" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(255, 255, 255, 0.1);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-white mb-0 brand-font fs-7"><i class="bi bi-bar-chart-fill text-danger me-2"></i>Diagram Rasio Outstanding PO vs Incoming Penerimaan</h6>
                                        <span class="badge bg-dark border border-secondary text-danger font-monospace fs-8">Step 4 Analytics</span>
                                    </div>
                                    <div style="height: 230px;">
                                        <canvas id="chartStep4Outstanding"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="p-3.5 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(255, 255, 255, 0.1);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-white mb-0 brand-font fs-7"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Proporsi Status Qty PO</h6>
                                    </div>
                                    <div style="height: 180px;" class="d-flex justify-content-center align-items-center my-auto">
                                        <canvas id="chartStep4Doughnut"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive style-scrollbar" style="max-height: 440px; overflow-y: auto;">
                        <table class="table-custom align-middle">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th>TANGGAL ORDER</th>
                                    <th>NOMOR PO</th>
                                    <th>ITEM CODE / PART NUMBER</th>
                                    <th>NAMA MATERIAL</th>
                                    <th class="text-end">TARGET PO</th>
                                    <th class="text-end">AKTUAL DITERIMA</th>
                                    <th class="text-end">SISA OUTSTANDING</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($outstandingPos as $idx => $op)
                                    <tr>
                                        <td class="text-center text-muted font-monospace fw-bold">{{ $idx + 1 }}</td>
                                        <td class="font-monospace text-light small">{{ date('d/m/Y', strtotime($op['tanggal'])) }}</td>
                                        <td class="fw-bold text-info font-monospace">{{ $op['po'] }}</td>
                                        <td class="fw-bold text-warning font-monospace">{{ $op['item_code'] }}</td>
                                        <td class="text-white small fw-semibold">{{ $op['name'] }}</td>
                                        <td class="text-end font-monospace text-info">{{ number_format($op['target_qty']) }} unit</td>
                                        <td class="text-end font-monospace text-emerald">{{ number_format($op['received_qty']) }} unit</td>
                                        <td class="text-end font-monospace text-danger fw-bold fs-6">{{ number_format($op['outstanding_qty']) }} unit</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-check-circle fs-3 d-block mb-1 text-success opacity-75"></i>
                                            Tidak ada sisa outstanding PO yang belum terpenuhi untuk {{ $targetUser->name }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 5: STEP 5 AKTUAL PRODUKSI -->
                <div class="tab-pane fade" id="step-production" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0 brand-font"><i class="bi bi-gear-wide-connected text-warning me-2.5"></i>Log Catatan Produksi (Terkait Pengadaan {{ $targetUser->name }})</h6>
                    </div>
                    <div class="table-responsive style-scrollbar" style="max-height: 480px; overflow-y: auto;">
                        <table class="table-custom align-middle">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th>TANGGAL PRODUKSI</th>
                                    <th>ITEM CODE</th>
                                    <th class="text-end">JUMLAH PRODUKSI (QTY)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($actualProductions as $idx => $ap)
                                    <tr>
                                        <td class="text-center text-muted font-monospace fw-bold">{{ $idx + 1 }}</td>
                                        <td class="font-monospace text-warning small">
                                            <i class="fa-regular fa-calendar me-1.5"></i> {{ date('d/m/Y', strtotime($ap->tanggal_produksi)) }}
                                        </td>
                                        <td class="fw-bold text-warning font-monospace">{{ $ap->item_code }}</td>
                                        <td class="text-end font-monospace text-primary fw-bold fs-6">-{{ number_format($ap->qty) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                            Belum ada catatan produksi yang terdaftar untuk {{ $targetUser->name }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 6: STEP 6 HASIL AKHIR & KOMPARASI (WITH DIAGRAM) -->
                <div class="tab-pane fade" id="step-comparison" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0 brand-font"><i class="bi bi-award-fill text-info me-2.5"></i>Ringkasan Hasil Akhir &amp; Komparasi Pengadaan (Analisis {{ $targetUser->name }})</h6>
                    </div>

                    <!-- DIAGRAM CHART STEP 6 -->
                    @if(count($comparisonList) > 0)
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <div class="p-3.5 rounded-3" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(255, 255, 255, 0.1);">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        <div>
                                            <h6 class="fw-bold text-white mb-0 brand-font fs-7"><i class="bi bi-diagram-3-fill text-info me-2"></i>Diagram Komparasi Alur 5 Tahap Pengadaan Material (Per Item Material)</h6>
                                            <span class="text-muted fs-8">Visualisasi perbandingan Forecast (Step 1) vs Target PO (Step 2) vs Incoming Diterima (Step 3) vs Sisa Outstanding (Step 4) vs Production (Step 5)</span>
                                        </div>
                                    </div>
                                    <div style="height: 250px;">
                                        <canvas id="chartStep6Comparison"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive style-scrollbar" style="max-height: 440px; overflow-y: auto;">
                        <table class="table-custom align-middle">
                            <thead class="sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th>PART NUMBER / ITEM CODE</th>
                                    <th class="text-end">FORECAST QTY (STEP 1)</th>
                                    <th class="text-end">TARGET PO QTY (STEP 2)</th>
                                    <th class="text-end">ACTUAL DITERIMA (STEP 3)</th>
                                    <th class="text-end">SISA OUTSTANDING (STEP 4)</th>
                                    <th class="text-end">AKTUAL PRODUKSI (STEP 5)</th>
                                    <th class="text-center">STATUS AUDIT &amp; FULFILLMENT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($comparisonList as $idx => $cp)
                                    <tr>
                                        <td class="text-center text-muted font-monospace fw-bold">{{ $idx + 1 }}</td>
                                        <td class="fw-bold text-warning font-monospace">{{ $cp['item_code'] }}</td>
                                        <td class="text-end font-monospace text-info fw-bold">{{ number_format($cp['forecast_qty']) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                        <td class="text-end font-monospace text-emerald fw-bold">{{ number_format($cp['po_qty']) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                        <td class="text-end font-monospace text-warning fw-bold">{{ number_format($cp['received_qty']) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                        <td class="text-end font-monospace {{ $cp['outstanding_qty'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ number_format($cp['outstanding_qty']) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                        <td class="text-end font-monospace text-primary fw-bold">{{ number_format($cp['production_qty']) }} <span class="fs-8 text-muted fw-normal">unit</span></td>
                                        <td class="text-center">
                                            @if($cp['status'] === 'Lengkap')
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-2.5 py-1 rounded-pill" style="font-size:0.72rem;">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Lengkap Terpenuhi
                                                </span>
                                            @elseif($cp['status'] === 'Diterima Sebagian')
                                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-2.5 py-1 rounded-pill" style="font-size:0.72rem;">
                                                    <i class="bi bi-clock-history me-1"></i>Diterima Sebagian
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary px-2.5 py-1 rounded-pill" style="font-size:0.72rem;">
                                                    <i class="bi bi-hourglass-split me-1"></i>Pending PO
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                            Belum ada item material yang dapat dikomparasi untuk {{ $targetUser->name }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- FOOTER -->
        <footer class="mt-5 pt-4 border-top border-secondary border-opacity-25 text-center text-muted" style="font-size: 0.82rem;">
            <p class="mb-1">
                &copy; {{ date('Y') }} PT KAWAI INDONESIA &bull; Dashboard Monitoring Purchasing &amp; Pengadaan Bahan Baku Piano
            </p>
            <p class="mb-0">
                Sistem Terintegrasi Divisi Procurement KIIC Karawang &bull; Multi-Role RBAC (Supervisor, Leader, Staff)
            </p>
        </footer>

    </div>

    <!-- BOOTSTRAP 5 JS BUNDLE -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- LIVE CLOCK SCRIPT -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
            const el = document.getElementById('live-clock');
            if (el) el.innerText = timeStr;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

    <!-- CHARTS JS SCRIPTS FOR STEP 4 & STEP 6 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ─── Step 4 Chart: Outstanding PO (Bar Chart) ───
            const step4Data = {!! json_encode($outstandingPos) !!};
            if (step4Data && step4Data.length > 0) {
                const ctx4 = document.getElementById('chartStep4Outstanding');
                if (ctx4) {
                    const labels4 = step4Data.map(d => d.po + ' (' + d.item_code + ')');
                    const target4 = step4Data.map(d => d.target_qty);
                    const rec4    = step4Data.map(d => d.received_qty);
                    const out4    = step4Data.map(d => d.outstanding_qty);

                    new Chart(ctx4, {
                        type: 'bar',
                        data: {
                            labels: labels4,
                            datasets: [
                                { label: 'Target PO', data: target4, backgroundColor: '#38bdf8', borderRadius: 4 },
                                { label: 'Diterima (Real)', data: rec4, backgroundColor: '#10b981', borderRadius: 4 },
                                { label: 'Sisa Outstanding', data: out4, backgroundColor: '#f87171', borderRadius: 4 }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top', labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 } } },
                                tooltip: { backgroundColor: '#0f172a', borderColor: 'rgba(255,255,255,0.2)', borderWidth: 1 }
                            },
                            scales: {
                                x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                                y: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#94a3b8', font: { size: 11 } } }
                            }
                        }
                    });
                }

                const ctx4Doughnut = document.getElementById('chartStep4Doughnut');
                if (ctx4Doughnut) {
                    const totRec = step4Data.reduce((acc, curr) => acc + curr.received_qty, 0);
                    const totOut = step4Data.reduce((acc, curr) => acc + curr.outstanding_qty, 0);

                    new Chart(ctx4Doughnut, {
                        type: 'doughnut',
                        data: {
                            labels: ['Sudah Diterima', 'Sisa Outstanding'],
                            datasets: [{
                                data: [totRec, totOut],
                                backgroundColor: ['#10b981', '#f87171'],
                                borderColor: '#0f172a',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: { position: 'bottom', labels: { color: '#cbd5e1', font: { family: 'Inter', size: 11 } } }
                            }
                        }
                    });
                }
            }

            // ─── Step 6 Chart: Hasil Komparasi 5 Tahap Pengadaan ───
            const step6Data = {!! json_encode($comparisonList) !!};
            if (step6Data && step6Data.length > 0) {
                const ctx6 = document.getElementById('chartStep6Comparison');
                if (ctx6) {
                    const labels6 = step6Data.map(d => d.item_code);
                    const fc6     = step6Data.map(d => d.forecast_qty);
                    const po6     = step6Data.map(d => d.po_qty);
                    const rec6    = step6Data.map(d => d.received_qty);
                    const out6    = step6Data.map(d => d.outstanding_qty);
                    const prod6   = step6Data.map(d => d.production_qty);

                    new Chart(ctx6, {
                        type: 'bar',
                        data: {
                            labels: labels6,
                            datasets: [
                                { label: 'Step 1 Forecast', data: fc6, backgroundColor: '#38bdf8', borderRadius: 4 },
                                { label: 'Step 2 Target PO', data: po6, backgroundColor: '#10b981', borderRadius: 4 },
                                { label: 'Step 3 Diterima', data: rec6, backgroundColor: '#f59e0b', borderRadius: 4 },
                                { label: 'Step 4 Outstanding', data: out6, backgroundColor: '#f87171', borderRadius: 4 },
                                { label: 'Step 5 Production', data: prod6, backgroundColor: '#a855f7', borderRadius: 4 }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top', labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 } } },
                                tooltip: { backgroundColor: '#0f172a', borderColor: 'rgba(255,255,255,0.2)', borderWidth: 1 }
                            },
                            scales: {
                                x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                                y: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#94a3b8', font: { size: 11 } } }
                            }
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>
