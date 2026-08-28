<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Kategori Material Purchasing | PT Kawai Indonesia</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-dark: #0A0E1A;
            --bg-primary: #0A0E1A;
            --bg-secondary: #121826;
            --card-bg: rgba(18, 24, 38, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --accent-gold: #e2b34a;
            --text-main: #f3f4f6;
            --text-muted: #cbd5e1;
        }

        body {
            background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Outfit', sans-serif;
        }

        .text-muted, .text-secondary {
            color: #cbd5e1 !important;
        }

        .top-navbar {
            background: rgba(18, 24, 38, 0.88);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--card-border);
            padding: 0.85rem 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand-logo-text {
            font-weight: 800;
            font-size: 1.25rem;
            background: linear-gradient(135deg, #fff 0%, var(--accent-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .form-control-dark, .form-select-dark {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            border-radius: 10px;
            padding: 0.65rem 1rem;
        }

        .form-control-dark:focus, .form-select-dark:focus {
            background: #111827;
            border-color: var(--accent-gold);
            color: #fff;
            box-shadow: 0 0 0 0.15rem rgba(226, 179, 74, 0.12);
            outline: none;
        }

        .table-container {
            border-radius: 14px;
            border: 1px solid var(--card-border);
            overflow: hidden;
            background: var(--card-bg);
        }

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--text-main);
            margin-bottom: 0;
        }

        .table-custom thead th {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--card-border);
            padding: 1rem;
            font-weight: 700;
        }

        .table-custom tbody td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }

        .table-custom tbody tr:hover td {
            background-color: rgba(226, 179, 74, 0.08) !important;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
</head>
<body>

    <!-- TOP NAVBAR -->
    <nav class="top-navbar">
        <div class="top-navbar-row1 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
            <a href="{{ route('dashboard.overview') }}" class="text-decoration-none" style="color: inherit !important; text-decoration: none !important;">
                <div class="d-flex align-items-center gap-2 mb-0.5">
                    <i class="bi bi-music-note-beamed text-warning fs-4" style="line-height:1; vertical-align:middle;"></i>
                    <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
                </div>
                <div class="text-muted" style="font-size:0.72rem; margin-left:2px; color:#9ca3af !important;">Master Kategori Material</div>
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

                <div class="px-3 py-1.5 rounded-pill d-flex align-items-center gap-2" style="background:rgba(255,255,255,0.05); border:1px solid var(--card-border); font-size:0.84rem;">
                    <span class="live-indicator"></span>
                    <span id="live-clock" class="fw-bold font-monospace" style="letter-spacing:0.04em;">00:00:00</span>
                </div>
            </div>
        </div>
        <div class="top-navbar-row2">
            @include('partials.pill-nav', ['activeRoute' => 'purchasing.categories'])
        </div>
    </nav>

    @include('partials.faq-modal')


    <div class="container-fluid px-4 py-4">

        @include('partials.toast-and-notification-popup')

        <!-- HERO BANNER HEADER -->
        <div class="exchange-hero mb-4">
            <div class="row align-items-center">
                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:rgba(226,179,74,0.18);border:1px solid rgba(226,179,74,0.45);color:#e2b34a;">
                            <i class="bi bi-tags-fill fs-3"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold text-white mb-0 brand-font" style="font-size:1.65rem;">Master Kategori Material &amp; Target Purchasing (USD)</h2>
                            <div class="text-muted small mt-1">
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 me-1">
                                    <i class="fa-solid fa-dollar-sign me-1"></i>Basis Mata Uang: USD
                                </span>
                                Monitoring target anggaran, realisasi penerimaan aktual, dan rasio pencapaian per kategori material.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6 mt-3 mt-lg-0">
                    <div class="d-flex gap-2 justify-content-lg-end align-items-center flex-wrap">
                        <!-- Filter Periode Bulan / All Time -->
                        <form method="GET" action="{{ route('purchasing.categories') }}" id="periodFilterForm" class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="min-width: 220px;">
                                <span class="input-group-text bg-dark border-secondary border-opacity-50 text-warning">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </span>
                                <select name="period" class="form-select form-select-sm form-select-dark fw-semibold" onchange="document.getElementById('periodFilterForm').submit();" style="border-radius: 0 10px 10px 0;">
                                    <option value="all" {{ ($selectedPeriod ?? 'all') === 'all' ? 'selected' : '' }}>Semua Periode (Akumulasi)</option>
                                    @if(isset($availablePeriods) && count($availablePeriods) > 0)
                                        @foreach($availablePeriods as $p)
                                            @php
                                                try {
                                                    $pObj = \Carbon\Carbon::createFromFormat('Y-m', $p);
                                                    $pLabel = $pObj ? $pObj->translatedFormat('F Y') : $p;
                                                } catch (\Throwable $e) {
                                                    $pLabel = $p;
                                                }
                                            @endphp
                                            <option value="{{ $p }}" {{ ($selectedPeriod ?? '') === $p ? 'selected' : '' }}>
                                                {{ $pLabel }} ({{ $p }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </form>

                        <span class="px-3.5 py-2 rounded-pill fw-bold" style="font-size:0.85rem; background: rgba(226,179,74,0.15); border: none; color:#fbbf24;">
                            <i class="fa-solid fa-boxes-stacked me-1.5 text-warning"></i> Total Kategori: <strong>{{ $totalCategoriesCount ?? $categories->count() }}</strong>
                        </span>
                        
                        <a href="{{ route('purchasing.categories.template') }}" class="btn btn-outline-info rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1.5 shadow-sm" style="font-size:0.85rem;" title="Unduh format template Excel untuk kategori material">
                            <i class="fa-solid fa-file-excel text-info"></i> Template Excel
                        </a>

                        @if(Auth::check() && Auth::user()->isAdmin())
                        <button type="button" class="btn btn-outline-success rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#importCategoryModal" style="font-size:0.85rem;">
                            <i class="fa-solid fa-file-import text-success"></i> Import Excel
                        </button>
                        <button type="button" class="btn btn-warning rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" onclick="document.querySelector('input[name=category_code]')?.focus(); document.querySelector('input[name=category_code]')?.scrollIntoView({behavior: 'smooth', block: 'center'});" style="font-size:0.88rem;">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Kategori
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @php
            $kpiTargetUsd = $totalTargetUsd ?? $categories->sum('target_usd');
            $kpiActualUsd = $totalActualUsd ?? $categories->sum('actual_usd');
            $kpiTargetUnits = $totalTargetUnits ?? $categories->sum('target_units');
            $kpiActualUnits = $totalActualUnits ?? $categories->sum('actual_units');
            $kpiPendingUnits = $totalPendingUnits ?? max(0, $kpiTargetUnits - $kpiActualUnits);
            $kpiPendingUsd = $totalPendingUsd ?? max(0, $kpiTargetUsd - $kpiActualUsd);
            $kpiActualRows = $totalActualRows ?? $categories->sum('actual_rows');
            $kpiOverallUsdPct = $overallPct ?? ($kpiTargetUsd > 0 ? round(($kpiActualUsd / $kpiTargetUsd) * 100, 1) : 0);
            $kpiOverallQtyPct = $overallQtyPct ?? ($kpiTargetUnits > 0 ? round(($kpiActualUnits / $kpiTargetUnits) * 100, 1) : 0);
            $kpiActiveCount = $activeCount ?? $categories->where('status', 'Active')->count();
            $kpiTotalCount = $totalCategoriesCount ?? $categories->count();
            $isPeriodFiltered = isset($selectedPeriod) && $selectedPeriod !== 'all';
        @endphp

        <!-- SUMMARY KPI CARDS FOR CATEGORIES -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
                            TARGET PENGADAAN (PLAN)
                        </div>
                        <div class="h3 fw-bold text-info mb-0 font-monospace">${{ number_format($kpiTargetUsd, 2) }}</div>
                        <div class="text-muted small mt-1" style="font-size:0.72rem;">
                            Total Plan: <strong class="text-white">{{ number_format($kpiTargetUnits) }}</strong> unit
                            @if($isPeriodFiltered)
                                <span class="badge bg-secondary font-monospace ms-1">{{ $selectedPeriod }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(6, 182, 212, 0.15); color: #06b6d4;">
                        <i class="fa-solid fa-bullseye fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
                            ACTUAL TERCAPAI (USD)
                        </div>
                        <div class="h3 fw-bold text-warning mb-0 font-monospace">${{ number_format($kpiActualUsd, 2) }}</div>
                        <div class="text-muted small mt-1" style="font-size:0.72rem;">
                            Diterima: <strong class="text-white">{{ number_format($kpiActualUnits) }}</strong> unit
                            <span class="text-secondary mx-1">•</span>
                            <strong class="text-info">{{ number_format($kpiActualRows) }}</strong> baris
                        </div>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="fa-solid fa-hand-holding-dollar fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
                    <div class="w-100 me-2">
                        <div class="text-muted small fw-bold text-uppercase mb-1.5" style="font-size:0.75rem; letter-spacing:0.5px;">
                            PENCAPAIAN PENGADAAN
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-0.5">
                            <span class="small text-muted" style="font-size:0.72rem;"><i class="fa-solid fa-boxes-stacked me-1 text-warning"></i>Unit (Qty):</span>
                            <span class="fw-bold font-monospace text-warning" style="font-size:0.82rem;">{{ $kpiOverallQtyPct }}%</span>
                        </div>
                        <div class="progress mb-1.5" style="height: 4px; background: rgba(255,255,255,0.08); border-radius: 3px;">
                            <div class="progress-bar bg-warning" style="width: {{ min(100, $kpiOverallQtyPct) }}%;"></div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-0.5">
                            <span class="small text-muted" style="font-size:0.72rem;"><i class="fa-solid fa-dollar-sign me-1 text-success"></i>Amount (USD):</span>
                            <span class="fw-bold font-monospace text-success" style="font-size:0.82rem;">{{ $kpiOverallUsdPct }}%</span>
                        </div>
                        <div class="progress" style="height: 4px; background: rgba(255,255,255,0.08); border-radius: 3px;">
                            <div class="progress-bar bg-success" style="width: {{ min(100, $kpiOverallUsdPct) }}%;"></div>
                        </div>
                    </div>
                    <div class="p-3 rounded-circle flex-shrink-0" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="fa-solid fa-chart-pie fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between h-100">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">STATUS PENGADAAN</div>
                        <div class="mt-1">
                            @if($overallStatus === 'Order Fulfilled')
                                <span class="badge bg-success text-white px-2.5 py-1.5 font-monospace fw-bold" style="font-size:0.8rem;">
                                    <i class="fa-solid fa-check-double me-1"></i> Fulfilled
                                </span>
                            @elseif($overallStatus === 'Partial Fulfilled')
                                <span class="badge bg-info text-dark px-2.5 py-1.5 font-monospace fw-bold" style="font-size:0.8rem;">
                                    <i class="fa-solid fa-circle-check me-1"></i> Partial Fulfilled
                                </span>
                            @elseif($overallStatus === 'On Track')
                                <span class="badge bg-info text-dark px-2.5 py-1.5 font-monospace fw-bold" style="font-size:0.8rem;">
                                    <i class="fa-solid fa-gauge-high me-1"></i> On Track
                                </span>
                            @elseif($overallStatus === 'In Progress')
                                <span class="badge bg-warning text-dark px-2.5 py-1.5 font-monospace fw-bold" style="font-size:0.8rem;">
                                    <i class="fa-solid fa-spinner fa-spin me-1"></i> In Progress
                                </span>
                            @else
                                <span class="badge bg-secondary text-white px-2.5 py-1.5 font-monospace fw-bold" style="font-size:0.8rem;">
                                    <i class="fa-solid fa-clock me-1"></i> Pending
                                </span>
                            @endif
                        </div>
                        <div class="text-muted small mt-1" style="font-size:0.72rem;">
                            Sisa: <strong class="text-warning">{{ number_format($kpiPendingUnits) }}</strong> unit (${{ number_format($kpiPendingUsd, 0) }})
                        </div>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Form Tambah Kategori Material -->
            <div class="col-12 col-lg-4">
                <div class="glass-card">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-solid fa-plus-circle text-warning fs-5"></i>
                        <h4 class="fw-bold mb-0">Tambah Kategori Material</h4>
                    </div>

                    <form action="{{ route('purchasing.categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode Kategori</label>
                            <input type="text" name="category_code" class="form-control-dark w-100" placeholder="Contoh: PUR-06" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Kategori Material</label>
                            <input type="text" name="category_name" class="form-control-dark w-100" placeholder="Contoh: Wood & Timber Parts" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">PIC Procurement / Buyer</label>
                            <select name="buyer_user_id" class="form-select-dark w-100" required>
                                <option value="">-- Pilih akun buyer purchasing --</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->id }}">
                                        {{ $buyer->name }} — {{ strtoupper($buyer->role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                                <span>Target Kuantitas (Unit / Pcs)</span>
                                <span class="badge bg-secondary font-monospace" style="font-size:0.68rem;">Unit/Pcs</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="any" name="target_qty" class="form-control form-control-dark font-monospace" placeholder="Contoh: 126017" value="126017" min="0">
                                <span class="input-group-text bg-dark border-secondary border-opacity-50 text-muted px-2.5" style="font-size:0.75rem;">Unit</span>
                            </div>
                            <div class="form-text text-muted" style="font-size:0.75rem;">
                                <i class="fa-solid fa-boxes-stacked me-1 text-warning"></i>Kuantitas fisik target pengadaan barang bulanan.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                                <span>Target Pengadaan Bulanan (USD)</span>
                                <span class="badge bg-warning bg-opacity-25 text-warning font-monospace" style="font-size:0.7rem;">$ USD</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary border-opacity-50 text-warning fw-bold px-3">$</span>
                                <input type="number" step="any" name="monthly_target_units" class="form-control form-control-dark font-monospace" placeholder="Contoh: 20000" value="20000" min="0.01" required>
                            </div>
                            <div class="form-text text-muted" style="font-size:0.75rem;">
                                <i class="fa-solid fa-circle-info me-1 text-info"></i>Total nominal target anggaran pengadaan bulanan secara global dalam USD ($).
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status Kategori</label>
                            <select name="status" class="form-select-dark w-100" required>
                                <option value="Active">Active</option>
                                <option value="Review">Review</option>
                                <option value="Hold">Hold</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2.5 shadow-sm">
                            <i class="fa-solid fa-plus me-2"></i> Tambah Kategori Baru
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar Kategori -->
            <div class="col-12 col-lg-8">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h4 class="fw-bold mb-1">Daftar Kategori</h4>
                            <p class="text-muted small mb-0">Master kategori material, target Plan PO (USD & Unit), kedatangan unit aktual, dan persentase pencapaian.</p>
                        </div>
                        <button type="button" id="btnBulkDeleteCategory" class="btn btn-danger btn-sm rounded-pill px-3 d-none" onclick="confirmBulkDeleteCategory()">
                            <i class="fa-solid fa-trash me-1"></i> Hapus Terpilih (<span id="bulkDeleteCountCategory">0</span>)
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 35px;">
                                        <input type="checkbox" id="checkAllCategory" class="form-check-input">
                                    </th>
                                    <th>Kode</th>
                                    <th>Nama Kategori Material</th>
                                    <th>PIC Procurement</th>
                                    <th class="text-end">Target Plan Unit</th>
                                    <th class="text-end">Kedatangan Unit</th>
                                    <th class="text-end">Sisa Pending</th>
                                    <th class="text-end">Target Plan (USD)</th>
                                    <th class="text-end">Realisasi (USD)</th>
                                    <th>Capaian Unit</th>
                                    <th>Capaian Amount (USD)</th>
                                    <th>Status</th>
                                    <th class="text-center" style="min-width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $cat)
                                    @php
                                        $targetUsd = $cat->target_usd ?? (float)($cat->monthly_target_units ?: 1);
                                        $targetUnits = $cat->target_units ?? ($cat->target_qty ?: ($cat->logs_sum_target_order ?? 0));
                                        $actualUsd = $cat->actual_usd ?? 0.0;
                                        $actualUnits = $cat->actual_units ?? ($cat->logs_sum_actual_received ?? 0);
                                        $pendingUnits = $cat->pending_units ?? max(0, $targetUnits - $actualUnits);
                                        $actualRows = $cat->actual_rows ?? ($cat->logs_count ?? 0);
                                        
                                        $pctUsd = $cat->achievement_usd_pct ?? ($targetUsd > 0 ? round(($actualUsd / $targetUsd) * 100, 1) : 0);
                                        $pctQty = $cat->achievement_qty_pct ?? ($targetUnits > 0 ? round(($actualUnits / $targetUnits) * 100, 1) : 0);

                                        $badgeUsdColor = $pctUsd >= 100 ? 'bg-success text-white' : ($pctUsd >= 85 ? 'bg-info text-dark' : ($pctUsd > 0 ? 'bg-warning text-dark' : 'bg-secondary text-white'));
                                        $barUsdColor = $pctUsd >= 100 ? 'bg-success' : ($pctUsd >= 85 ? 'bg-info' : ($pctUsd > 0 ? 'bg-warning' : 'bg-secondary'));

                                        $badgeQtyColor = $pctQty >= 100 ? 'bg-success text-white' : ($pctQty >= 85 ? 'bg-info text-dark' : ($pctQty > 0 ? 'bg-warning text-dark' : 'bg-secondary text-white'));
                                        $barQtyColor = $pctQty >= 100 ? 'bg-success' : ($pctQty >= 85 ? 'bg-info' : ($pctQty > 0 ? 'bg-warning' : 'bg-secondary'));
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="row-checkbox-category form-check-input" value="{{ $cat->id }}">
                                        </td>
                                        <td>
                                            <span class="badge bg-dark border border-secondary text-warning font-monospace px-2 py-1">
                                                {{ $cat->category_code }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-white">{{ $cat->category_name }}</td>
                                        <td>
                                            @if($cat->buyer)
                                                <div class="fw-semibold text-white">{{ $cat->buyer->name }}</div>
                                                <small class="text-muted" style="font-size:0.75rem;">{{ $cat->buyer->email }}</small>
                                            @else
                                                <span class="text-muted">{{ $cat->pic_buyer ?: 'Belum Ditugaskan' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="font-monospace text-info fw-bold">{{ number_format($targetUnits) }}</div>
                                            <small class="text-muted" style="font-size:0.7rem;">unit target</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="font-monospace text-warning fw-bold">{{ number_format($actualUnits) }}</div>
                                            <small class="text-info" style="font-size:0.7rem;"><i class="bi bi-receipt me-0.5"></i>{{ number_format($actualRows) }} baris</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="font-monospace {{ $pendingUnits > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                                {{ number_format($pendingUnits) }}
                                            </div>
                                            <small class="text-muted" style="font-size:0.7rem;">unit sisa</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="font-monospace text-info fw-semibold">${{ number_format($targetUsd, 2) }}</div>
                                        </td>
                                        <td class="text-end">
                                            <div class="font-monospace text-success fw-bold">${{ number_format($actualUsd, 2) }}</div>
                                        </td>
                                        <!-- Capaian Unit (Qty) -->
                                        <td style="min-width: 130px;">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="badge {{ $badgeQtyColor }} font-monospace" style="font-size: 0.72rem;">
                                                    {{ $pctQty }}%
                                                </span>
                                                <small class="text-muted font-monospace" style="font-size:0.65rem;">{{ number_format($actualUnits) }}/{{ number_format($targetUnits) }}</small>
                                            </div>
                                            <div class="progress" style="height: 5px; background: rgba(255,255,255,0.08); border-radius: 3px;">
                                                <div class="progress-bar {{ $barQtyColor }}" role="progressbar" style="width: {{ min(100, $pctQty) }}%;"></div>
                                            </div>
                                        </td>
                                        <!-- Capaian Amount (USD) -->
                                        <td style="min-width: 130px;">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="badge {{ $badgeUsdColor }} font-monospace" style="font-size: 0.72rem;">
                                                    {{ $pctUsd }}%
                                                </span>
                                                <small class="text-muted font-monospace" style="font-size:0.65rem;">${{ number_format($actualUsd, 0) }}/${{ number_format($targetUsd, 0) }}</small>
                                            </div>
                                            <div class="progress" style="height: 5px; background: rgba(255,255,255,0.08); border-radius: 3px;">
                                                <div class="progress-bar {{ $barUsdColor }}" role="progressbar" style="width: {{ min(100, $pctUsd) }}%;"></div>
                                            </div>
                                        </td>
                                        <!-- Status Pengadaan Dinamis -->
                                        <td>
                                            @if($cat->status_label === 'Fulfilled')
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1" title="Target Unit ({{ $pctQty }}%) dan Amount ({{ $pctUsd }}%) telah 100% tuntas">
                                                    <i class="fa-solid fa-check-double me-1"></i>Fulfilled
                                                </span>
                                            @elseif($cat->status_label === 'Partial Fulfilled')
                                                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-1" title="Salah satu parameter (Unit: {{ $pctQty }}% | USD: {{ $pctUsd }}%) telah tuntas">
                                                    <i class="fa-solid fa-circle-check me-1"></i>Partial Fulfilled
                                                </span>
                                            @elseif($cat->status_label === 'On Track')
                                                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-1" title="Rata-rata pemenuhan di atas 85% (Unit: {{ $pctQty }}% • USD: {{ $pctUsd }}%)">
                                                    <i class="fa-solid fa-gauge-high me-1"></i>On Track
                                                </span>
                                            @elseif($cat->status_label === 'In Progress')
                                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2 py-1" title="Sedang berjalan (Unit: {{ $pctQty }}% • USD: {{ $pctUsd }}%)">
                                                    <i class="fa-solid fa-spinner fa-spin me-1"></i>In Progress
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-50 px-2 py-1">
                                                    <i class="fa-solid fa-clock me-1"></i>Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-1.5 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#detailModal{{ $cat->id }}" title="Lihat Rincian Transaksi & Kontribusi Item" style="font-size:0.78rem;">
                                                    <i class="fa-solid fa-chart-pie me-1"></i> Rincian
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $cat->id }}" title="Edit Kategori" style="font-size:0.78rem;">
                                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                                </button>
                                                <form id="deleteCategoryForm{{ $cat->id }}" action="{{ route('purchasing.categories.destroy', $cat->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" title="Hapus Kategori" onclick="KawaiConfirm.delete('Hapus Kategori', 'Kategori {{ $cat->category_code }} akan dihapus.', () => document.getElementById('deleteCategoryForm{{ $cat->id }}').submit())" style="font-size:0.78rem;">
                                                        <i class="fa-solid fa-trash me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                                            Belum ada kategori material yang terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals Edit & Detail Kategori (placed outside any container to prevent viewport/backdrop clipping) -->
    @foreach($categories as $cat)
        <!-- Modal Rincian Transaksi & Kontribusi Item -->
        <div class="modal fade text-start" id="detailModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content" style="background: #121826; border: 1px solid rgba(255,255,255,0.15); color: #f3f4f6; border-radius: 16px;">
                    <div class="modal-header border-bottom border-secondary border-opacity-25 py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-dark border border-secondary text-warning font-monospace px-2.5 py-1">
                                {{ $cat->category_code }}
                            </span>
                            <h5 class="modal-title fw-bold mb-0 text-white">
                                Rincian Transaksi: {{ $cat->category_name }}
                            </h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3 px-4">
                        @php
                            $catTotalLogsCount = $cat->logs->count();
                            $catTotalPlanUnits = (int)$cat->logs->sum('target_order');
                            $catTotalLogsUnits = (int)$cat->logs->sum('actual_received');
                            $catTotalPendingUnits = max(0, $catTotalPlanUnits - $catTotalLogsUnits);
                            $catQtyPct = $catTotalPlanUnits > 0 ? round(($catTotalLogsUnits / $catTotalPlanUnits) * 100, 1) : 0;
                            $catUsdPct = $cat->target_usd > 0 ? round(($cat->actual_usd / $cat->target_usd) * 100, 1) : 0;
                            $catAvgUnitsPerRow = $catTotalLogsCount > 0 ? round($catTotalLogsUnits / $catTotalLogsCount, 1) : 0;
                        @endphp

                        <!-- Mini Summary Cards -->
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3" style="background: rgba(6, 182, 212, 0.08); border: 1px solid rgba(6, 182, 212, 0.2);">
                                    <div class="text-muted small fw-bold" style="font-size:0.68rem;">TARGET PLAN PO</div>
                                    <div class="h5 fw-bold text-info mb-0 font-monospace">{{ number_format($catTotalPlanUnits) }} <small class="fs-6 text-muted">unit</small></div>
                                    <div class="text-muted font-monospace" style="font-size:0.7rem;">${{ number_format($cat->target_usd, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2);">
                                    <div class="text-muted small fw-bold" style="font-size:0.68rem;">ACTUAL DITERIMA</div>
                                    <div class="h5 fw-bold text-warning mb-0 font-monospace">{{ number_format($catTotalLogsUnits) }} <small class="fs-6 text-muted">unit</small></div>
                                    <div class="text-muted font-monospace" style="font-size:0.7rem;">${{ number_format($cat->actual_usd, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2);">
                                    <div class="text-muted small fw-bold" style="font-size:0.68rem;">SISA PENDING / OUTSTANDING</div>
                                    <div class="h5 fw-bold text-danger mb-0 font-monospace">{{ number_format($catTotalPendingUnits) }} <small class="fs-6 text-muted">unit</small></div>
                                    <div class="text-muted font-monospace" style="font-size:0.7rem;">${{ number_format($cat->pending_usd ?? max(0, $cat->target_usd - $cat->actual_usd), 2) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2);">
                                    <div class="text-muted small fw-bold" style="font-size:0.68rem;">PENCAPAIAN (UNIT & USD)</div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="small text-muted" style="font-size:0.7rem;">Unit (Qty):</span>
                                        <span class="fw-bold font-monospace text-warning" style="font-size:0.8rem;">{{ $catQtyPct }}%</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="small text-muted" style="font-size:0.7rem;">Amount (USD):</span>
                                        <span class="fw-bold font-monospace text-success" style="font-size:0.8rem;">{{ $catUsdPct }}%</span>
                                    </div>
                                    <div class="mt-0.5 text-center">
                                        <span class="badge {{ $cat->status_label === 'Fulfilled' ? 'bg-success text-white' : ($cat->status_label === 'On Track' || $cat->status_label === 'Partial Fulfilled' ? 'bg-info text-dark' : ($cat->status_label === 'In Progress' ? 'bg-warning text-dark' : 'bg-secondary text-white')) }}" style="font-size:0.65rem;">
                                            {{ $cat->status_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Penjelasan Transparansi Metrik -->
                        <div class="p-2.5 rounded-3 mb-3 d-flex align-items-start gap-2" style="background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.25); font-size: 0.78rem;">
                            <i class="fa-solid fa-circle-info text-info mt-0.5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-info">Analisis Rekonsiliasi:</strong> Dari total target pesanan (Plan PO) sebanyak <strong>{{ number_format($catTotalPlanUnits) }} unit</strong> (${{ number_format($cat->target_usd, 0) }}), barang fisik yang sudah masuk dan diterima adalah <strong>{{ number_format($catTotalLogsUnits) }} unit</strong> (${{ number_format($cat->actual_usd, 0) }}). Masih terdapat sisa outstanding sebanyak <strong>{{ number_format($catTotalPendingUnits) }} unit</strong> (${{ number_format($cat->pending_usd ?? max(0, $cat->target_usd - $cat->actual_usd), 0) }}) yang berstatus <em>In Progress / Belum Tuntas</em>.
                            </div>
                        </div>

                        <!-- Nav Tabs -->
                        <ul class="nav nav-pills mb-3 gap-1" id="catDetailTabs{{ $cat->id }}" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active btn-sm rounded-pill px-3 py-1.5 fw-semibold" id="tab-monthly-{{ $cat->id }}" data-bs-toggle="pill" data-bs-target="#content-monthly-{{ $cat->id }}" type="button" role="tab" style="font-size:0.8rem;">
                                    <i class="fa-solid fa-calendar-days me-1.5"></i> Distribusi Plan vs Actual Bulanan ({{ count($cat->monthly_breakdown ?? []) }} Periode)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link btn-sm rounded-pill px-3 py-1.5 fw-semibold" id="tab-items-{{ $cat->id }}" data-bs-toggle="pill" data-bs-target="#content-items-{{ $cat->id }}" type="button" role="tab" style="font-size:0.8rem;">
                                    <i class="fa-solid fa-boxes-stacked me-1.5"></i> Top Item Kontributor ({{ count($cat->top_items ?? []) }})
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="catDetailTabContent{{ $cat->id }}">
                            <!-- Tab Distribusi Bulanan -->
                            <div class="tab-pane fade show active" id="content-monthly-{{ $cat->id }}" role="tabpanel">
                                <div class="table-responsive" style="max-height: 280px;">
                                    <table class="table table-dark table-hover table-sm align-middle mb-0" style="font-size: 0.78rem;">
                                        <thead style="position: sticky; top: 0; background: #1f293d; z-index: 1;">
                                            <tr>
                                                <th>Periode</th>
                                                <th class="text-center">Baris</th>
                                                <th class="text-end">Plan Qty</th>
                                                <th class="text-end">Actual Qty</th>
                                                <th class="text-end">Sisa Pending</th>
                                                <th class="text-end">Plan USD</th>
                                                <th class="text-end">Actual USD</th>
                                                <th class="text-end">Capaian</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($cat->monthly_breakdown ?? [] as $mRow)
                                                @php
                                                    $mPlanUnits = (int)($mRow['plan_units'] ?? 0);
                                                    $mActualUnits = (int)($mRow['units'] ?? 0);
                                                    $mPendingUnits = max(0, $mPlanUnits - $mActualUnits);
                                                    $mPct = $mPlanUnits > 0 ? round(($mActualUnits / $mPlanUnits) * 100, 1) : ($mActualUnits > 0 ? 100 : 0);
                                                    $mBadge = $mPct >= 100 ? 'bg-success text-white' : ($mPct >= 85 ? 'bg-info text-dark' : ($mPct > 0 ? 'bg-warning text-dark' : 'bg-secondary text-white'));
                                                    try {
                                                        $mDateObj = \Carbon\Carbon::createFromFormat('Y-m', $mRow['period']);
                                                        $mName = $mDateObj ? $mDateObj->translatedFormat('M Y') : $mRow['period'];
                                                    } catch (\Throwable $e) {
                                                        $mName = $mRow['period'];
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary font-monospace">{{ $mRow['period'] }}</span>
                                                        <span class="ms-1 fw-semibold text-white">{{ $mName }}</span>
                                                    </td>
                                                    <td class="text-center font-monospace text-muted">{{ number_format($mRow['rows']) }}</td>
                                                    <td class="text-end font-monospace text-info">{{ number_format($mPlanUnits) }}</td>
                                                    <td class="text-end font-monospace text-warning fw-bold">{{ number_format($mActualUnits) }}</td>
                                                    <td class="text-end font-monospace text-danger">{{ number_format($mPendingUnits) }}</td>
                                                    <td class="text-end font-monospace text-muted">${{ number_format($mRow['plan_usd'] ?? 0, 0) }}</td>
                                                    <td class="text-end font-monospace text-success fw-semibold">${{ number_format($mRow['usd'] ?? 0, 0) }}</td>
                                                    <td class="text-end font-monospace">
                                                        <span class="badge {{ $mBadge }} px-2 py-0.5">
                                                            {{ $mPct }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-3 text-muted">Tidak ada data bulanan.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab Top Item Kontributor -->
                            <div class="tab-pane fade" id="content-items-{{ $cat->id }}" role="tabpanel">
                                <div class="table-responsive" style="max-height: 280px;">
                                    <table class="table table-dark table-hover table-sm align-middle mb-0" style="font-size: 0.78rem;">
                                        <thead style="position: sticky; top: 0; background: #1f293d; z-index: 1;">
                                            <tr>
                                                <th>Kode Item</th>
                                                <th>Deskripsi Material</th>
                                                <th>Supplier</th>
                                                <th class="text-end">Plan Qty</th>
                                                <th class="text-end">Actual Qty</th>
                                                <th class="text-end">Sisa Pending</th>
                                                <th class="text-end">Actual USD</th>
                                                <th class="text-end">Capaian</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($cat->top_items ?? [] as $it)
                                                @php
                                                    $itPlan = (int)($it['plan_units'] ?? 0);
                                                    $itAct = (int)($it['units'] ?? 0);
                                                    $itPending = max(0, $itPlan - $itAct);
                                                    $itPct = $itPlan > 0 ? round(($itAct / $itPlan) * 100, 1) : ($itAct > 0 ? 100 : 0);
                                                    $itBadge = $itPct >= 100 ? 'bg-success text-white' : ($itPct >= 85 ? 'bg-info text-dark' : ($itPct > 0 ? 'bg-warning text-dark' : 'bg-secondary text-white'));
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-dark border border-secondary text-warning font-monospace">
                                                            {{ $it['item_code'] }}
                                                        </span>
                                                    </td>
                                                    <td class="text-white text-truncate" style="max-width: 170px;" title="{{ $it['item_name'] }}">
                                                        {{ $it['item_name'] }}
                                                    </td>
                                                    <td class="text-muted text-truncate" style="max-width: 130px;" title="{{ $it['supplier'] }}">
                                                        {{ $it['supplier'] }}
                                                    </td>
                                                    <td class="text-end font-monospace text-info">{{ number_format($itPlan) }}</td>
                                                    <td class="text-end font-monospace text-warning fw-bold">{{ number_format($itAct) }}</td>
                                                    <td class="text-end font-monospace text-danger">{{ number_format($itPending) }}</td>
                                                    <td class="text-end font-monospace text-success">${{ number_format($it['usd'], 2) }}</td>
                                                    <td class="text-end font-monospace">
                                                        <span class="badge {{ $itBadge }} px-2 py-0.5">
                                                            {{ $itPct }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-3 text-muted">Tidak ada item transaksi.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-top border-secondary border-opacity-25 py-2.5 px-4">
                        <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Kategori -->
        <div class="modal fade text-start" id="editModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="background: #121826; border: 1px solid rgba(255,255,255,0.15); color: #f3f4f6; border-radius: 16px;">
                    <div class="modal-header border-bottom border-secondary border-opacity-25 py-3 px-4">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Edit Kategori Material ({{ $cat->category_code }})</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('purchasing.categories.update', $cat->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body py-3 px-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kode Kategori</label>
                                <input type="text" name="category_code" class="form-control-dark w-100" value="{{ $cat->category_code }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Kategori Material</label>
                                <input type="text" name="category_name" class="form-control-dark w-100" value="{{ $cat->category_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">PIC Procurement / Buyer</label>
                                <select name="buyer_user_id" class="form-select-dark w-100" required>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->id }}" {{ $cat->buyer_user_id == $buyer->id ? 'selected' : '' }}>
                                            {{ $buyer->name }} — {{ strtoupper($buyer->role) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                                    <span>Target Kuantitas (Unit / Pcs)</span>
                                    <span class="badge bg-secondary font-monospace" style="font-size:0.68rem;">Unit/Pcs</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="any" name="target_qty" class="form-control form-control-dark font-monospace" placeholder="Contoh: 126017" value="{{ $cat->target_qty ?? ($cat->target_units ?: '') }}" min="0">
                                    <span class="input-group-text bg-dark border-secondary border-opacity-50 text-muted px-2.5" style="font-size:0.75rem;">Unit</span>
                                </div>
                                <div class="form-text text-muted" style="font-size:0.75rem;">
                                    <i class="fa-solid fa-boxes-stacked me-1 text-warning"></i>Kuantitas fisik target pengadaan barang bulanan.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                                    <span>Target Pengadaan Bulanan (USD)</span>
                                    <span class="badge bg-warning bg-opacity-25 text-warning font-monospace" style="font-size:0.7rem;">$ USD</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary border-opacity-50 text-warning fw-bold px-3">$</span>
                                    <input type="number" step="any" name="monthly_target_units" class="form-control form-control-dark font-monospace" value="{{ $cat->monthly_target_units }}" min="0.01" required>
                                </div>
                                <div class="form-text text-muted" style="font-size:0.75rem;">
                                    <i class="fa-solid fa-circle-info me-1 text-info"></i>Total nominal target anggaran pengadaan bulanan secara global dalam USD ($).
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status Kategori</label>
                                <select name="status" class="form-select-dark w-100" required>
                                    <option value="Active" {{ $cat->status == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Review" {{ $cat->status == 'Review' ? 'selected' : '' }}>Review</option>
                                    <option value="Hold" {{ $cat->status == 'Hold' ? 'selected' : '' }}>Hold</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-top border-secondary border-opacity-25 py-3 px-4">
                            <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 py-2 fw-bold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal Import Excel Kategori Material -->
    <div class="modal fade text-start" id="importCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: #121826; border: 1px solid rgba(255,255,255,0.15); color: #f3f4f6; border-radius: 16px;">
                <div class="modal-header border-bottom border-secondary border-opacity-25 py-3 px-4">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="fa-solid fa-file-excel text-success me-2"></i> Import Kategori Material via Excel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('purchasing.categories.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body py-3 px-4">
                        <div class="p-3 rounded-3 mb-3" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); font-size: 0.8rem;">
                            <div class="fw-bold text-success mb-1"><i class="fa-solid fa-circle-check me-1"></i> Petunjuk Import:</div>
                            <ul class="mb-2 ps-3 text-muted" style="line-height: 1.5;">
                                <li>Gunakan format file <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong>.</li>
                                <li>Sistem akan menambahkan kategori baru atau memperbarui data yang sudah ada berdasarkan <strong>Kode Kategori</strong>.</li>
                                <li>Target bulanan (USD) dimuat secara global per kategori.</li>
                            </ul>
                            <a href="{{ route('purchasing.categories.template') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1 fw-semibold">
                                <i class="fa-solid fa-download me-1"></i> Unduh Format Template Excel
                            </a>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih File Excel / CSV</label>
                            <input type="file" name="file" class="form-control form-control-dark" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text text-muted" style="font-size:0.75rem;">
                                Format kolom: No, Kode Kategori, Nama Kategori Material, PIC Procurement, Target Bulanan (USD), Status.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary border-opacity-25 py-3 px-4">
                        <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 py-2 fw-bold">
                            <i class="fa-solid fa-cloud-arrow-up me-1.5"></i> Mulai Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Bulk Delete Confirmation Kategori -->
    <div class="modal fade" id="modalBulkDeleteCategoryConfirm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-danger text-white" style="background: #111827;">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i> Konfirmasi Hapus Massal Kategori</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('purchasing.categories.destroy-bulk') }}" method="POST" id="formBulkDeleteCategory">
                    @csrf
                    <div class="modal-body">
                        <div id="bulkDeleteCategoryIdsContainer"></div>
                        Apakah Anda yakin ingin menghapus <strong id="bulkDeleteCategoryCountText" class="text-danger">0</strong> Kategori terpilih?
                        <p class="text-muted small mt-2 mb-0">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer border-secondary border-opacity-25">
                        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">Ya, Hapus Terpilih</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAllCategory = document.getElementById('checkAllCategory');
            const rowCheckboxesCategory = document.querySelectorAll('.row-checkbox-category');
            const btnBulkDeleteCategory = document.getElementById('btnBulkDeleteCategory');
            const countSpanCategory = document.getElementById('bulkDeleteCountCategory');

            function updateCategoryBulkBtn() {
                const checked = document.querySelectorAll('.row-checkbox-category:checked');
                if (btnBulkDeleteCategory) {
                    if (checked.length > 0) {
                        btnBulkDeleteCategory.classList.remove('d-none');
                        countSpanCategory.innerText = checked.length;
                    } else {
                        btnBulkDeleteCategory.classList.add('d-none');
                    }
                }
            }

            if (checkAllCategory) {
                checkAllCategory.addEventListener('change', function() {
                    rowCheckboxesCategory.forEach(cb => cb.checked = this.checked);
                    updateCategoryBulkBtn();
                });
            }

            rowCheckboxesCategory.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (checkAllCategory) {
                        checkAllCategory.checked = (document.querySelectorAll('.row-checkbox-category:checked').length === rowCheckboxesCategory.length);
                    }
                    updateCategoryBulkBtn();
                });
            });
        });

        function confirmBulkDeleteCategory() {
            const checked = document.querySelectorAll('.row-checkbox-category:checked');
            if (checked.length === 0) return;
            
            const container = document.getElementById('bulkDeleteCategoryIdsContainer');
            container.innerHTML = '';
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            document.getElementById('bulkDeleteCategoryCountText').innerText = checked.length;
            new bootstrap.Modal(document.getElementById('modalBulkDeleteCategoryConfirm')).show();
        }
    </script>
    <script>
    (function() {
        function updateClock() {
            var el = document.getElementById('live-clock');
            if (el) el.textContent = new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
        }
        updateClock();
        setInterval(updateClock, 1000);
    })();
    </script>
    @include('partials.confirm-modal')
    <script src="{{ asset('js/kawai-notify.js') }}"></script>
    <script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
