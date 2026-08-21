<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Realisasi & Input Aktual | PT Kawai Indonesia</title>
    <meta name="description" content="Dashboard Realisasi & Input Aktual Penerimaan Material PT Kawai Indonesia">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-dark: #0A0E1A;
            --card-bg: rgba(18, 24, 38, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --accent-emerald: #10B981;
            --accent-gold: #F59E0B;
            --accent-blue: #3B82F6;
            --accent-purple: #8B5CF6;
            --text-main: #F3F4F6;
            --text-muted: #9CA3AF;
            --font-mono: 'JetBrains Mono', monospace;
        }
        * { box-sizing: border-box; }
        body {
            background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-dark) 60%);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        h1, h2, h3, h4, h5, .brand-font { font-family: 'Outfit', sans-serif; }
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            border-color: rgba(16, 185, 129, 0.3);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.1);
        }
        .nav-pills-custom .nav-link {
            color: var(--text-muted);
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.25s ease;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .nav-pills-custom .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
        }
        .nav-pills-custom .nav-link.active {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #2563eb 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.35);
            border-color: transparent;
        }
        .table-custom, .table-dark-custom, .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            --bs-table-bg: transparent !important;
            --bs-table-color: var(--text-main) !important;
            --bs-table-border-color: rgba(255, 255, 255, 0.08) !important;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.06) !important;
            --bs-table-hover-color: #ffffff !important;
            background: transparent !important;
            color: var(--text-main) !important;
        }
        .table-custom thead th, .table-dark-custom thead th, .table thead th {
            background: rgba(18, 24, 38, 0.95) !important;
            color: #cbd5e1 !important;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 14px 16px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.12) !important;
            font-weight: 700;
        }
        .table-custom tbody td, .table-dark-custom tbody td, .table tbody td {
            background: rgba(18, 24, 38, 0.45) !important;
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            color: #F3F4F6 !important;
            vertical-align: middle;
            font-size: 0.9rem;
        }
        .table-custom tbody tr, .table-dark-custom tbody tr, .table tbody tr {
            transition: background 0.2s ease;
        }
        .table-custom tbody tr:hover td, .table-dark-custom tbody tr:hover td, .table tbody tr:hover td {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }
        .kpi-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        .kpi-emerald::before { background: var(--accent-emerald); }
        .kpi-gold::before { background: var(--accent-gold); }
        .kpi-blue::before { background: var(--accent-blue); }
        .btn-glow {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            border: none;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
        }
        .btn-glow:hover {
            color: #fff;
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.6);
        }
        .btn-success-glow {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }
        .btn-success-glow:hover {
            color: #fff;
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.6);
        }
        .modal-content-dark {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            color: #F3F4F6;
        }
        .modal-content-dark .text-muted {
            color: #cbd5e1 !important;
        }
        .modal-content-dark .form-label {
            color: #ffffff !important;
            font-weight: 600 !important;
        }
        .form-control, .form-select, .form-control-dark, .form-select-dark {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-radius: 8px;
        }
        .form-select option, .form-select-dark option {
            background-color: #1a2236 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            padding: 8px !important;
        }
        .form-control::placeholder, .form-control-dark::placeholder {
            color: #cbd5e1 !important;
            opacity: 0.85 !important;
        }
        .form-control:focus, .form-select:focus, .form-control-dark:focus, .form-select-dark:focus {
            background-color: rgba(255, 255, 255, 0.12) !important;
            border-color: var(--accent-blue) !important;
            color: #fff !important;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
        }
        .top-navbar {
            background: rgba(18, 24, 38, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--card-border);
            padding: 0.85rem 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .text-muted, .text-secondary {
            color: #cbd5e1 !important;
        }
        .badge-key-primary {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.25) 0%, rgba(37, 99, 235, 0.15) 100%);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.4);
            font-family: var(--font-mono);
            font-weight: 700;
            padding: 0.35rem 0.65rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            display: inline-block;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.15);
        }
        .badge-key-secondary {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.22) 0%, rgba(5, 150, 105, 0.15) 100%);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.35);
            font-family: var(--font-mono);
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 0.45rem;
            font-size: 0.82rem;
            display: inline-block;
        }
        .badge-status {
            padding: 0.4rem 0.8rem;
            border-radius: 50rem;
            font-weight: 600;
            font-size: 0.76rem;
            letter-spacing: 0.03em;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .badge-over {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.4);
            box-shadow: 0 0 12px rgba(245, 158, 11, 0.25);
            animation: pulse-glow 2s infinite;
        }
        .badge-under {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }
        .badge-fit {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 8px rgba(245, 158, 11, 0.3); }
            50% { box-shadow: 0 0 18px rgba(245, 158, 11, 0.6); }
        }
        .info-pill {
            font-size: 0.78rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.25rem 0.65rem;
            border-radius: 0.4rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="top-navbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard.overview') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-music-note-beamed text-warning fs-4"></i>
            <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
        </a>
    </div>
    <div>
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.input', 'hasFaqModal' => true])
    </div>
</nav>


@include('partials.faq-modal')

<div class="container-dashboard py-4">

    <!-- 7-STEP UNIFIED WORKFLOW STEPPER -->
    @include('partials.workflow-stepper', ['currentStep' => 3])

    <!-- STANDARDIZED PAGE HEADER & ACTION HIERARCHY -->
    <div class="kawai-page-header">
        <div class="kawai-page-header-left">
            <div class="page-icon-box" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.35);">
                <i class="bi bi-truck text-info"></i>
            </div>
            <div>
                <h1 class="page-title-text">Incoming Penerimaan PO</h1>
                <p class="page-subtitle-text">Pencatatan realisasi fisik penerimaan barang dan status pemeriksaan IAD Quality PT Kawai Indonesia.</p>
            </div>
        </div>
        <div class="kawai-page-actions">
            <button type="button" class="btn-kawai-secondary" data-bs-toggle="modal" data-bs-target="#modalImportRealisasiPo" title="Import data realisasi penerimaan barang dari Excel (hanya membaca kolom Result / Penerimaan)">
                <i class="bi bi-file-earmark-excel-fill text-success"></i> Import Incoming
            </button>
            <button type="button" class="btn-kawai-primary" onclick="switchToFormTab()">
                <i class="bi bi-plus-circle-fill"></i> Input Penerimaan
            </button>
            <div class="dropdown">
                <button class="btn-kawai-more dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Opsi Tambahan">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark-custom dropdown-menu-end">
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.input.template') }}">
                            <i class="bi bi-download text-info"></i> Unduh Template Incoming (.xlsx)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.master-po') }}">
                            <i class="bi bi-box-seam text-success"></i> Ke Master PO (Step 2)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.outstanding-po') }}">
                            <i class="bi bi-clock-history text-warning"></i> Ke Dashboard Outstanding PO
                        </a>
                    </li>
                </ul>
            </div>
            @include('partials.kurs-kpi-banner')
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success bg-opacity-10 border border-success border-opacity-25 text-success alert-dismissible fade show mb-4 rounded-3 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{!! session('success') !!}</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger alert-dismissible fade show mb-4 rounded-3 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>{!! session('error') !!}</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 4 KPI Totals Summary (Matching Dashboard Master PO style) -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Total Realisasi Masuk -->
        <div class="col-md-3">
            <div class="glass-card kpi-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1 mb-2">TOTAL REALISASI QTY</span>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Aktual Penerimaan Unit</h6>
                        <h3 class="fw-bold text-white mb-0">{{ number_format($poMonitoringList->sum('actual_received'), 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">unit</span></h3>
                    </div>
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #10b981;">
                        <i class="bi bi-box-seam-fill fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-25 small text-muted">
                    <span>Total Baris / Transaksi:</span>
                    <span class="text-white fw-bold">{{ number_format($poMonitoringList->count()) }} Baris</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Amount Realisasi ($) -->
        <div class="col-md-3">
            <div class="glass-card kpi-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2 py-1 mb-2">TOTAL AMOUNT ($)</span>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Nilai Transaksi Realisasi</h6>
                        @php 
                            $totAmt = $poMonitoringList->sum('amount'); 
                            $todayDate = date('Y-m-d');
                            $currentActiveRate = \App\Models\TaxExchangeRate::where('currency_code', 2)
                                ->whereDate('start_date', '<=', $todayDate)
                                ->whereDate('end_date', '>=', $todayDate)
                                ->value('tax_exchange_rate') 
                                ?? \App\Models\TaxExchangeRate::where('currency_code', 2)->orderByDesc('id')->value('tax_exchange_rate') 
                                ?? 16500;
                            $totAmtIdr = $totAmt * $currentActiveRate;
                        @endphp
                        <h3 class="fw-bold text-warning mb-0 font-monospace" style="font-size:1.35rem;">$ {{ number_format($totAmt, 2, ',', '.') }}</h3>
                        <div class="text-success small fw-bold font-monospace mt-1" style="font-size:0.8rem;" title="Konversi Rupiah dengan Kurs Pajak Terkini Hari Ini (Rp {{ number_format($currentActiveRate, 0, ',', '.') }}/$)">
                            <i class="bi bi-arrow-repeat me-1"></i>≈ Rp {{ number_format($totAmtIdr, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #f59e0b;">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-25 small text-muted">
                    <span>Kurs Terkini:</span>
                    <span class="text-success fw-bold">Rp {{ number_format($currentActiveRate, 0, ',', '.') }} / $</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Target Order Terpantau -->
        <div class="col-md-3">
            <div class="glass-card kpi-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2 py-1 mb-2">TARGET ORDER TERPANTAU</span>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Sinergi Terhadap Master Data</h6>
                        <h3 class="fw-bold text-white mb-0">{{ number_format($poMonitoringList->sum('target_order'), 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">unit</span></h3>
                    </div>
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.35); color: #3b82f6;">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-25 small text-muted">
                    <span>Master Item Terdaftar:</span>
                    <span class="text-white fw-bold">{{ number_format(count($masterList)) }} Part</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Rata-rata Achievement -->
        <div class="col-md-3">
            <div class="glass-card kpi-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-1 mb-2">STATUS ACHIEVEMENT</span>
                        <h6 class="text-muted mb-1 small text-uppercase fw-semibold">Tingkat Pemenuhan PO</h6>
                        @php
                            $avgAchievement = $rekapTable->count() > 0 ? round($rekapTable->avg('fulfillment_pct'), 1) : ($poMonitoringList->sum('target_order') > 0 ? round(($poMonitoringList->sum('actual_received') / $poMonitoringList->sum('target_order')) * 100, 1) : 100);
                        @endphp
                        <h3 class="fw-bold text-white mb-0">{{ $avgAchievement }}% <span class="fs-6 fw-normal text-muted">fulfilled</span></h3>
                    </div>
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.35); color: #06b6d4;">
                        <i class="bi bi-pie-chart-fill fs-4"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-25 small text-muted">
                    <span>Kategori Terpantau:</span>
                    <span class="text-white fw-bold">{{ number_format($categories->count()) }} Kategori</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Pills for Sub-sections (Matching Dashboard Master PO) -->
    <div class="mb-4">
        <ul class="nav nav-pills nav-pills-custom" id="realisasiPills" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-monitoring-btn" data-bs-toggle="pill" data-bs-target="#tab-monitoring" type="button" role="tab" aria-controls="tab-monitoring" aria-selected="true">
                    <i class="bi bi-table text-success"></i> Tabel Incoming Penerimaan PO
                    <span class="badge bg-success ms-1">{{ $poMonitoringList->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-input-btn" data-bs-toggle="pill" data-bs-target="#tab-input" type="button" role="tab" aria-controls="tab-input" aria-selected="false">
                    <i class="bi bi-journal-text text-warning"></i> Form Input Penerimaan (Gudang)
                </button>
            </li>
            <li class="nav-item ms-auto" role="presentation">
                <button class="nav-link" id="tab-rekap-btn" data-bs-toggle="pill" data-bs-target="#tab-rekap" type="button" role="tab" aria-controls="tab-rekap" aria-selected="false" style="border-color: rgba(255,255,255,0.15);">
                    <i class="bi bi-bar-chart-steps text-info me-1"></i> Rekapitulasi per Kategori & Bulan
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="realisasiPillsContent">
        
        <!-- ════════════ TAB 1: TABEL AKTUAL PENERIMAAN & MONITORING MUATAN ════════════ -->
        <div class="tab-pane fade show active" id="tab-monitoring" role="tabpanel" aria-labelledby="tab-monitoring-btn">
            <div class="glass-card p-4 mb-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-bottom border-secondary border-opacity-25 pb-3 mb-4">
                    <div>
                        <h5 class="fw-bold text-white mb-1 brand-font">
                            <i class="bi bi-list-check text-warning me-2"></i>Daftar Riwayat Hasil Input Incoming Pembelian
                        </h5>
                        <p class="text-muted small mb-0">Rincian data transaksi penerimaan fisik material yang telah dimasukkan oleh tim Gudang / Purchasing.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <form method="GET" action="{{ route('purchasing.input') }}" class="d-inline-flex align-items-center gap-2">
                            <select name="delivery_category" class="form-select form-select-sm bg-dark border-secondary text-white" style="min-width: 160px;" onchange="this.form.submit()">
                                <option value="">-- Semua Pengantaran --</option>
                                @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                    <option value="{{ $dc->code }}" {{ ($selectedDeliveryCategory ?? '') == $dc->code ? 'selected' : '' }}>
                                        {{ $dc->code }} - {{ $dc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($selectedDeliveryCategory ?? '')
                                <a href="{{ route('purchasing.input') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                            @endif
                        </form>
                        <button type="button" id="btnBulkDeleteLog" class="btn btn-danger btn-sm rounded-pill px-3 py-2 d-none" onclick="confirmBulkDeleteLog()">
                            <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span id="bulkDeleteCountLog">0</span>)
                        </button>
                        <span class="badge bg-dark border border-secondary p-2 px-3 text-muted">
                            <i class="bi bi-list-check me-1"></i> Total Data: <strong class="text-white">{{ $poMonitoringList->count() }}</strong>
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table-custom" style="font-size: 0.85rem;">
                        <thead class="table-secondary text-uppercase text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="text-center" style="width: 35px;">
                                    <input type="checkbox" id="checkAllLogs" class="form-check-input">
                                </th>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>Supplier Name</th>
                                <th>Incoming Date</th>
                                <th>Material Code</th>
                                <th>Description</th>
                                <th>PO No.</th>
                                <th class="text-center">Currency</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Plan</th>
                                <th class="text-end text-info">Plan Amount</th>
                                <th class="text-end text-success">Result</th>
                                <th class="text-end text-success">Result Amount</th>
                                <th class="text-end text-warning">Remaining</th>
                                <th class="text-end text-warning">Rem Amount</th>
                                <th class="text-center">Persen</th>
                                <th class="text-center">Completed</th>
                                <th class="text-center text-warning">Kode Pabrik</th>
                                <th>Pencatat / Verifikasi</th>
                                <th class="text-center">Aksi</th>
                                <th class="text-center text-nowrap" style="background: rgba(59,130,246,0.15); color: #60a5fa;">Kategori Pengantaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($poMonitoringList as $index => $row)
                                @php
                                    $price = (float)($row->price ?? 0);
                                    $isIdr = strtoupper($row->currency ?? 'USD') === 'IDR' || $price > 300;
                                    $currSymbol = $isIdr ? 'Rp ' : '$ ';
                                    $dec = $isIdr ? 0 : 2;
                                    
                                    $targetQty = (int)($row->target_order ?? 0);
                                    $planAmt = $price * $targetQty;
                                    
                                    $actualQty = (int)($row->actual_received ?? 0);
                                    $resultAmt = $price * $actualQty;
                                    
                                    $remQty = max(0, $targetQty - $actualQty);
                                    $remAmt = $price * $remQty;
                                    
                                    $persen = $targetQty > 0 ? round(($actualQty / $targetQty) * 100, 1) : 0;
                                    $isCompleted = ($actualQty >= $targetQty && $targetQty > 0);
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="row-checkbox-log form-check-input" value="{{ $row->id }}">
                                    </td>
                                    <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-medium text-info">{{ $row->supplier_name ?: '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark border border-secondary text-light px-2 py-1 font-monospace">
                                             <i class="bi bi-calendar-event me-1 text-info"></i>{{ $row->receipt_date ? date('d-M-y', strtotime($row->receipt_date)) : $row->period_month }}
                                        </span>
                                    </td>
                                    <td>
                                        <code class="text-info fs-6 fw-bold">{{ $row->item_code ?: '-' }}</code>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white">{{ $row->description }}</div>
                                        @if(!empty($row->status_note))
                                            <div class="fs-8 text-muted text-truncate max-w-xs">{{ $row->status_note }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-gold fw-bold">{{ $row->po_reference ?: '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $isIdr ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-info bg-opacity-25 text-info border border-info' }} px-2 py-1 fw-bold">
                                            {{ $isIdr ? 'IDR' : strtoupper($row->currency ?? 'USD') }}
                                        </span>
                                    </td>
                                    <td class="text-end font-monospace fw-semibold text-warning">
                                        {{ $currSymbol }}{{ number_format($price, $dec, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-semibold text-white fs-6">
                                        {{ number_format($targetQty, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace fw-semibold text-info">
                                        {{ $currSymbol }}{{ number_format($planAmt, $dec, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-bold text-success fs-6">
                                        {{ number_format($actualQty, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-success">
                                        {{ $currSymbol }}{{ number_format($resultAmt, $dec, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-bold {{ $remQty > 0 ? 'text-warning' : 'text-muted' }}">
                                        {{ number_format($remQty, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace fw-semibold {{ $remAmt > 0 ? 'text-warning' : 'text-muted' }}">
                                        {{ $currSymbol }}{{ number_format($remAmt, $dec, ',', '.') }}
                                    </td>
                                    <td class="text-center font-monospace fw-bold {{ $persen >= 100 ? 'text-success' : ($persen > 0 ? 'text-info' : 'text-muted') }}">
                                        {{ $persen }}%
                                    </td>
                                    <td class="text-center">
                                        @if($row->diff > 0)
                                            <span class="badge badge-status badge-over" title="Over Receipt">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Over (+{{ number_format($row->diff) }})
                                            </span>
                                        @elseif($row->diff < 0)
                                            <span class="badge badge-status badge-under" title="Kurang / Pending">
                                                <i class="bi bi-arrow-down-circle-fill me-1"></i> No ({{ $persen }}%)
                                            </span>
                                        @else
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i> Yes
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2 py-1 font-monospace">{{ $row->factory_code ?? 'KIP 1' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @php $roleVal = $row->role ?? 'User'; @endphp
                                            <span class="badge {{ $roleVal === 'Manager' || $roleVal === 'Supervisor' ? 'bg-danger' : ($roleVal === 'Leader' ? 'bg-info text-dark' : 'bg-secondary') }} rounded-pill" style="font-size: 0.65rem;">
                                                {{ $roleVal }}
                                            </span>
                                            <span class="fs-7 text-light">{{ $row->created_by ?? 'System' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-info rounded-2" data-bs-toggle="modal" data-bs-target="#modalEditLog{{ $row->id }}" title="Edit catatan">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @if($row->role === 'Admin Staff' || $row->role === 'Leader')
                                                <form action="{{ route('purchasing.log.verify', $row->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-2" title="Approve & Verifikasi">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form id="deleteLogForm{{ $row->id }}" action="{{ route('purchasing.log.destroy', $row->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-2" title="Hapus catatan" onclick="KawaiConfirm.delete('Hapus Catatan Incoming', 'Catatan penerimaan Item {{ $row->item_code }} (PO {{ $row->po_reference }}) akan dihapus.', () => document.getElementById('deleteLogForm{{ $row->id }}').submit())">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="text-center text-nowrap">{!! $row->delivery_category_badge ?? '' !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                        Belum ada data incoming/aktual penerimaan. Silakan isi form pada tab <strong>Form Input Incoming</strong>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- NOTIFIKASI HASIL RINGKASAN PEMENUHAN PO & RINCIAN PENGIRIMAN PARSIAL -->
                @if(isset($poGroupSummaries) && count($poGroupSummaries) > 0)
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                            <h6 class="fw-bold text-white mb-0 brand-font">📌 Notifikasi Status Pemenuhan PO & Rincian Penerimaan Parsial</h6>
                        </div>
                        <div class="row g-3">
                            @foreach($poGroupSummaries as $summary)
                                <div class="col-md-6 col-xl-4">
                                    <div class="p-3 rounded-3" style="background: rgba(18, 24, 38, 0.85); border: 1px solid rgba(255, 255, 255, 0.1);">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary px-2 py-0.5 rounded" style="font-size: 0.7rem;">Item: {{ $summary->item_code }}</span>
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-0.5 rounded" style="font-size: 0.7rem;">PO: {{ $summary->po_reference }}</span>
                                            </div>
                                            @if($summary->is_completed)
                                                <span class="badge bg-success text-white rounded-pill px-2 py-1" style="font-size: 0.68rem;"><i class="bi bi-check-circle-fill me-1"></i>COMPLETE (LUNAS)</span>
                                            @elseif($summary->diff_cumulative > 0)
                                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 0.68rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>OVER +{{ number_format($summary->diff_cumulative) }}</span>
                                            @else
                                                <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 0.68rem;"><i class="bi bi-clock-history me-1"></i>SISA {{ number_format(abs($summary->diff_cumulative)) }} UNIT</span>
                                            @endif
                                        </div>
                                        <div class="fw-bold text-white mb-2" style="font-size: 0.88rem;">{{ $summary->description }}</div>
                                        <div class="d-flex justify-content-between align-items-center small text-muted mb-2 pb-2 border-bottom border-secondary border-opacity-25">
                                            <span>Target PO: <strong class="text-warning">{{ number_format($summary->target_order) }} unit</strong></span>
                                            <span>Total Diterima: <strong class="text-success">{{ number_format($summary->total_received) }} unit</strong></span>
                                        </div>
                                        
                                        <!-- Rincian Tahap Pengiriman -->
                                        <div class="pt-1">
                                            <small class="text-muted fw-semibold d-block mb-1" style="font-size: 0.72rem;">Rincian {{ $summary->shipment_count }}x Tanggal Pengiriman:</small>
                                            @foreach($summary->shipments as $shipIndex => $sh)
                                                <div class="d-flex justify-content-between align-items-center py-1 px-2 mb-1 rounded" style="background: rgba(255,255,255,0.03); font-size: 0.75rem;">
                                                    <span class="text-light">
                                                        <i class="bi bi-calendar-check text-info me-1"></i> {{ date('d/m/Y', strtotime($sh->receipt_date)) }}
                                                    </span>
                                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 fw-bold">
                                                        +{{ number_format($sh->actual_received) }} unit
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ════════════ TAB 2: FORM INPUT INCOMING MATERIAL ════════════ -->
        <div class="tab-pane fade" id="tab-input" role="tabpanel" aria-labelledby="tab-input-btn">
            <div class="glass-card p-4 mb-5">
                <div class="d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25 pb-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.35); color: #06b6d4;">
                            <i class="bi bi-box-arrow-in-down fs-3"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-white">Input Incoming Penerimaan Material</h4>
                            <p class="text-muted mb-0 fs-7">
                                Pilih <strong>Item Code</strong> atau <strong>No. PO</strong>, sistem akan otomatis mendeteksi spesifikasi & target order dari Master Data.
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('purchasing.store') }}" method="POST" id="formRealisasi">
                    @csrf
                    <div class="row g-4">
                        
                        <!-- 1. ITEM CODE -->
                        <div class="col-12 col-md-4">
                            <label class="form-label d-flex justify-content-between">
                                <span><i class="bi bi-qr-code text-info me-1"></i> 1. ITEM CODE</span>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="item_code" id="inputItemCode" list="listMasterItemCode" placeholder="Pilih atau ketik Item Code (cth: 00001 / DWG-A)" required autocomplete="off">
                                <button class="btn btn-outline-info d-flex align-items-center gap-1 fw-bold" type="button" data-bs-toggle="modal" data-bs-target="#modalSearchItemCode" title="Cari & Pilih dari Pop-up">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                            <datalist id="listMasterItemCode">
                                @foreach($masterListObjects as $master)
                                    @if(!empty($master->part_number) || !empty($master->drawing))
                                        <option value="{{ $master->part_number ?: $master->drawing }}" 
                                                data-po="{{ $master->po_number ?: $master->part_number }}"
                                                data-supplier="{{ $master->supplier_name ?: '' }}"
                                                data-name="{{ $master->description }}"
                                                data-target="{{ $master->order_qty }}"
                                                data-price="{{ $master->price ?? 0 }}"
                                                data-currency="{{ $master->currency ?? 'USD' }}"
                                                data-date="{{ $master->po_date }}"
                                                data-category="{{ $master->purchasing_category_id }}">
                                            {{ $master->part_number ?: $master->drawing }} | Spec: {{ $master->drawing ?: '-' }} | {{ $master->description }}
                                        </option>
                                    @endif
                                @endforeach
                            </datalist>
                            <div class="form-text text-muted fs-7">Kode material / part number produk.</div>
                        </div>

                        <!-- 2. NO. PO -->
                        <div class="col-12 col-md-4">
                            <label class="form-label d-flex justify-content-between">
                                <span><i class="bi bi-file-earmark-text text-success me-1"></i> 2. NO. PO</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="po_reference" id="inputPoReference" list="listMasterPo" placeholder="Pilih atau ketik No. PO (cth: KI-0001)" required autocomplete="off">
                            <datalist id="listMasterPo">
                                @foreach($masterListObjects as $master)
                                    @if(!empty($master->part_number) || !empty($master->po_number))
                                        <option value="{{ $master->po_number ?: $master->part_number }}" 
                                                data-itemcode="{{ $master->part_number ?: $master->drawing }}"
                                                data-supplier="{{ $master->supplier_name ?: '' }}"
                                                data-name="{{ $master->description }}"
                                                data-target="{{ $master->order_qty }}"
                                                data-price="{{ $master->price ?? 0 }}"
                                                data-currency="{{ $master->currency ?? 'USD' }}"
                                                data-date="{{ $master->po_date }}"
                                                data-category="{{ $master->purchasing_category_id }}">
                                            PO: {{ $master->po_number ?: $master->part_number }} | Item Code: {{ $master->part_number ?: $master->drawing }} | {{ $master->description }}
                                        </option>
                                    @endif
                                @endforeach
                            </datalist>
                            <div class="form-text text-muted fs-7">Nomor referensi Purchase Order (PO).</div>
                        </div>

                        <!-- 3. TANGGAL RECEIPT & PERIODE BULAN -->
                        <div class="col-12 col-md-4">
                            <div class="row g-2">
                                <div class="col-7">
                                    <label class="form-label"><i class="bi bi-calendar-event me-1"></i> Tanggal Receipt <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="receipt_date" id="inputReceiptDate" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-5">
                                    <label class="form-label"><i class="bi bi-calendar-month me-1"></i> Periode</label>
                                    <input type="text" class="form-control text-center fw-bold text-info" name="period_month" id="inputPeriodMonth" value="{{ date('Y-m') }}" required readonly>
                                </div>
                            </div>
                            <div class="form-text text-muted fs-7">Tanggal surat jalan penerimaan di gudang.</div>
                        </div>

                        <!-- 4. SUPPLIER -->
                        <div class="col-12 col-md-4">
                            <label class="form-label"><i class="bi bi-building me-1"></i> Supplier / Vendor</label>
                            <input type="text" class="form-control" name="supplier_name" id="inputSupplier" placeholder="Nama Supplier (opsional)" value="">
                        </div>

                        <!-- 5. NAME / DESCRIPTION -->
                        <div class="col-12 col-md-5">
                            <label class="form-label"><i class="bi bi-box me-1"></i> Name / Description</label>
                            <input type="text" class="form-control" name="item_name" id="inputItemName" placeholder="Deskripsi material (cth: Chair aluminium)" required>
                        </div>

                        <!-- 6. KATEGORI MATERIAL -->
                        <div class="col-12 col-md-3">
                            <label class="form-label"><i class="bi bi-tags me-1"></i> Kategori Material <span class="text-muted">(opsional)</span></label>
                            <select class="form-select" name="purchasing_category_id" id="selectCategory">
                                <option value="" selected>-- Tidak ada kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_code }} - {{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- KATEGORI PENGANTARAN -->
                        <div class="col-12 col-md-3">
                            <label class="form-label text-info fw-bold"><i class="bi bi-truck me-1"></i> Kategori Pengantaran <span class="text-danger">*</span></label>
                            <select class="form-select bg-dark text-white border-info fw-bold" name="delivery_category_code" id="selectDeliveryCategory">
                                @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                    <option value="{{ $dc->code }}" {{ $dc->code === 'LOC' ? 'selected' : '' }}>
                                        {{ $dc->code }} - {{ $dc->name }} ({{ $dc->currency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 7. TARGET ORDER (RENCANA PO / KEBUTUHAN) -->
                        <div class="col-12 col-md-3">
                            <label class="form-label text-warning"><i class="bi bi-bullseye me-1"></i> 7. Target Order (Rencana) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control fw-bold text-warning fs-5" name="target_order" id="inputTargetOrder" placeholder="0" min="0" readonly value="0">
                                <span class="input-group-text bg-dark border-secondary text-muted">Unit</span>
                            </div>
                            <div class="form-text text-warning fs-7">Terkunci dan diambil dari Master PO (Step 2).</div>
                        </div>

                        <!-- 8. AKTUAL PENERIMAAN (QTY) -->
                        <div class="col-12 col-md-3">
                            <label class="form-label text-success"><i class="bi bi-box-arrow-in-down me-1"></i> 8. Aktual Penerimaan (qty) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control fw-bold text-success fs-5" name="actual_received" id="inputActualReceived" placeholder="0" min="0" required>
                                <span class="input-group-text bg-dark border-secondary text-muted">Unit</span>
                            </div>
                            <div class="form-text text-success fs-7">Jumlah fisik masuk sesuai surat jalan.</div>
                        </div>

                        <!-- 9. PRICE / HARGA UNIT & MATA UANG -->
                        <div class="col-12 col-md-3">
                            <label class="form-label text-warning"><i class="bi bi-tag-fill me-1"></i> 9. Price & Mata Uang</label>
                            <div class="input-group">
                                <select name="currency" id="inputCurrency" class="form-select bg-dark text-warning border-secondary fw-bold" style="max-width: 105px;">
                                    <option value="USD" selected>USD ($)</option>
                                    <option value="IDR">IDR (Rp)</option>
                                </select>
                                <input type="text" inputmode="decimal" class="form-control fw-bold text-warning fs-5" name="price" id="inputPrice" placeholder="0.00 (cth: 227,05)">
                            </div>
                            <div class="form-text text-warning fs-7">Harga & Satuan (USD/IDR).</div>
                        </div>

                        <!-- 10. CATATAN / STATUS NOTE -->
                        <div class="col-12 col-md-3">
                            <label class="form-label"><i class="bi bi-chat-left-text me-1"></i> Catatan Penerimaan</label>
                            <input type="text" class="form-control" name="status_note" placeholder="Cth: Diterima lengkap dari surat jalan #SJ-998">
                        </div>

                    </div>

                    <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <button type="reset" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-success-glow px-5 py-2 fs-6">
                            <i class="bi bi-cloud-check-fill me-2"></i> Simpan Incoming & Update Master Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ════════════ TAB 3: REKAPITULASI TARGET vs AKTUAL PER KATEGORI ════════════ -->
        <div class="tab-pane fade" id="tab-rekap" role="tabpanel" aria-labelledby="tab-rekap-btn">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <div>
                        <h4 class="mb-1 fw-bold text-white"><i class="bi bi-bar-chart-steps me-2 text-info"></i> Rekapitulasi Target vs Aktual per Kategori & Bulan</h4>
                        <p class="text-muted mb-0 fs-7">Perbandingan agregat persentase pemenuhan material terhadap rencana kebutuhan setiap kategori.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Kategori Material</th>
                                <th>Periode</th>
                                <th class="text-end">Total Target Order</th>
                                <th class="text-end">Total Aktual Masuk</th>
                                <th class="text-end">Pending / Selisih</th>
                                <th class="text-center">Achievement (%)</th>
                                <th class="text-center">Status Keseluruhan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapTable as $rekap)
                                <tr>
                                    <td class="fw-bold text-light">{{ $rekap->category_name }}</td>
                                    <td><span class="badge bg-secondary bg-opacity-25 text-light">{{ $rekap->period_month }}</span></td>
                                    <td class="text-end text-warning fw-semibold">{{ number_format($rekap->target_order, 0, ',', '.') }} Unit</td>
                                    <td class="text-end text-success fw-bold">{{ number_format($rekap->actual_received, 0, ',', '.') }} Unit</td>
                                    <td class="text-end {{ $rekap->pending_order > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ number_format($rekap->pending_order, 0, ',', '.') }} Unit
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <div class="progress bg-dark" style="width: 70px; height: 6px;">
                                                <div class="progress-bar {{ $rekap->fulfillment_pct >= 100 ? 'bg-success' : 'bg-warning' }}" style="width: {{ min(100, $rekap->fulfillment_pct) }}%;"></div>
                                            </div>
                                            <span class="fw-bold fs-7">{{ $rekap->fulfillment_pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $rekap->badge }} px-3 py-1">{{ $rekap->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada rekapitulasi per kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modals Edit Realisasi (placed outside any container to prevent viewport/backdrop clipping) -->
@foreach($poMonitoringList as $row)
    <div class="modal fade text-start" id="modalEditLog{{ $row->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <form action="{{ route('purchasing.log.update', $row->id) }}" method="POST" class="w-100">
                @csrf
                @method('PUT')
                <div class="modal-content modal-content-dark border-secondary text-white">
                    <div class="modal-header border-bottom border-secondary border-opacity-25">
                        <h5 class="modal-title"><i class="bi bi-pencil-square text-info me-2"></i> Edit Realisasi Penerimaan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 style-scrollbar" style="max-height: 65vh; overflow-y: auto;">
                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="form-label text-muted small">Tanggal Receipt</label>
                                <input type="date" name="receipt_date" class="form-control bg-dark text-white border-secondary" value="{{ date('Y-m-d', strtotime($row->receipt_date)) }}" required id="editReceiptDate{{ $row->id }}">
                            </div>
                            <div class="col-5">
                                <label class="form-label text-muted small">Periode</label>
                                <input type="text" name="period_month" class="form-control bg-dark text-white border-secondary text-center fw-bold text-info" value="{{ $row->period_month }}" required readonly id="editPeriodMonth{{ $row->id }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Supplier / Vendor</label>
                            <input type="text" name="supplier_name" class="form-control bg-dark text-white border-secondary" value="{{ $row->supplier_name }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small">Item Code (Primary Key)</label>
                                <input type="text" name="item_code" class="form-control bg-dark text-white border-secondary" value="{{ $row->item_code }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">Nomor PO (Secondary Key)</label>
                                <input type="text" name="po_reference" class="form-control bg-dark text-white border-secondary" value="{{ $row->po_reference }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Nama Material / Deskripsi</label>
                            <input type="text" name="item_name" class="form-control bg-dark text-white border-secondary" value="{{ $row->description }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Kategori Material</label>
                            <select class="form-select bg-dark text-white border-secondary" name="purchasing_category_id" required>
                                <option value="" disabled>-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $row->purchasing_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->category_code }} - {{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-info small fw-bold"><i class="bi bi-truck me-1"></i> Kategori Pengantaran</label>
                            <select class="form-select bg-dark text-white border-secondary fw-bold" name="delivery_category_code">
                                @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                    <option value="{{ $dc->code }}" {{ ($row->delivery_category_code ?? 'LOC') == $dc->code ? 'selected' : '' }}>
                                        {{ $dc->code }} - {{ $dc->name }} ({{ $dc->currency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label text-warning small">Target Order (Rencana)</label>
                                <input type="number" name="target_order" class="form-control bg-dark text-white border-secondary text-warning fw-bold" value="{{ $row->target_order }}" required min="0">
                            </div>
                            <div class="col-4">
                                <label class="form-label text-success small">Aktual Penerimaan (Qty)</label>
                                <input type="number" name="actual_received" class="form-control bg-dark text-white border-secondary text-success fw-bold" value="{{ $row->actual_received }}" required min="0">
                            </div>
                            <div class="col-4">
                                <label class="form-label text-warning small">Price & Mata Uang</label>
                                <div class="input-group input-group-sm">
                                    <select name="currency" class="form-select bg-dark text-warning border-secondary fw-bold px-1" style="max-width: 80px;">
                                        <option value="USD" {{ ($row->currency ?? 'USD') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                        <option value="IDR" {{ ($row->currency ?? 'USD') == 'IDR' ? 'selected' : '' }}>IDR (Rp)</option>
                                    </select>
                                    <input type="text" inputmode="decimal" name="price" class="form-control bg-dark text-white border-secondary text-warning fw-bold" value="{{ ($row->currency ?? 'USD') === 'IDR' ? number_format($row->price ?? 0, 0, ',', '.') : number_format($row->price ?? 0, 2, ',', '.') }}" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Catatan Penerimaan</label>
                            @php
                                $cleanNote = preg_replace('/^(⏳ Menunggu Approval .*? - |✅ Disetujui Diterima .*? - )/', '', $row->status_note);
                            @endphp
                            <input type="text" name="status_note" class="form-control bg-dark text-white border-secondary" value="{{ $cleanNote }}">
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary border-opacity-25">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('editReceiptDate{{ $row->id }}').addEventListener('change', function() {
            if (this.value) {
                const parts = this.value.split('-');
                if (parts.length >= 2) {
                    document.getElementById('editPeriodMonth{{ $row->id }}').value = parts[0] + '-' + parts[1];
                }
            }
        });
    </script>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function switchToFormTab() {
    const inputTabBtn = document.getElementById('tab-input-btn');
    if (inputTabBtn) {
        const tabInstance = bootstrap.Tab.getOrCreateInstance(inputTabBtn);
        tabInstance.show();
        setTimeout(() => {
            const inputItem = document.getElementById('inputItemCode');
            if (inputItem) {
                inputItem.focus();
                inputItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 150);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const inputItemCode = document.getElementById('inputItemCode');
    const inputPoReference = document.getElementById('inputPoReference');
    const inputReceiptDate = document.getElementById('inputReceiptDate');
    const inputPeriodMonth = document.getElementById('inputPeriodMonth');
    const inputSupplier = document.getElementById('inputSupplier');
    const inputItemName = document.getElementById('inputItemName');
    const selectCategory = document.getElementById('selectCategory');

    // Auto update period_month ketika tanggal receipt diubah
    inputReceiptDate.addEventListener('change', function() {
        if (this.value) {
            const dateParts = this.value.split('-');
            if (dateParts.length >= 2) {
                inputPeriodMonth.value = dateParts[0] + '-' + dateParts[1];
            }
        }
    });

    const inputTargetOrder = document.getElementById('inputTargetOrder');

    // Smart Fill saat Item Code (Primary Key) dipilih dari Datalist
    if (inputItemCode) {
        inputItemCode.addEventListener('input', function() {
            const val = this.value.trim();
            const option = document.querySelector(`#listMasterItemCode option[value="${val}"]`);
            if (option) {
                if (option.dataset.po) inputPoReference.value = option.dataset.po;
                if (option.dataset.supplier) inputSupplier.value = option.dataset.supplier;
                if (option.dataset.name) inputItemName.value = option.dataset.name;
                if (option.dataset.category) selectCategory.value = option.dataset.category;
                if (option.dataset.target && inputTargetOrder) inputTargetOrder.value = option.dataset.target;
                if (option.dataset.price && document.getElementById('inputPrice')) document.getElementById('inputPrice').value = option.dataset.price;
                if (option.dataset.currency && document.getElementById('inputCurrency')) document.getElementById('inputCurrency').value = option.dataset.currency;
                if (option.dataset.date && inputReceiptDate) {
                    inputReceiptDate.value = option.dataset.date;
                    const parts = option.dataset.date.split('-');
                    if (parts.length >= 2 && inputPeriodMonth) {
                        inputPeriodMonth.value = parts[0] + '-' + parts[1];
                    }
                }
            }
        });
    }

    // Smart Fill saat No. PO (Secondary Key) dipilih dari Datalist
    if (inputPoReference) {
        inputPoReference.addEventListener('input', function() {
            const val = this.value.trim();
            const option = document.querySelector(`#listMasterPo option[value="${val}"]`);
            if (option) {
                if (option.dataset.itemcode && !inputItemCode.value) inputItemCode.value = option.dataset.itemcode;
                if (option.dataset.supplier) inputSupplier.value = option.dataset.supplier;
                if (option.dataset.name) inputItemName.value = option.dataset.name;
                if (option.dataset.category) selectCategory.value = option.dataset.category;
                if (option.dataset.target && inputTargetOrder) inputTargetOrder.value = option.dataset.target;
                if (option.dataset.price && document.getElementById('inputPrice')) document.getElementById('inputPrice').value = option.dataset.price;
                if (option.dataset.currency && document.getElementById('inputCurrency')) document.getElementById('inputCurrency').value = option.dataset.currency;
                if (option.dataset.date && inputReceiptDate) {
                    inputReceiptDate.value = option.dataset.date;
                    const parts = option.dataset.date.split('-');
                    if (parts.length >= 2 && inputPeriodMonth) {
                        inputPeriodMonth.value = parts[0] + '-' + parts[1];
                    }
                }
            }
        });
    }
});

// ESCAPE HTML helper
function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Select item code from popup modal
function selectItemCodeFromPopup(itemCode, poReference, supplier, name, categoryId, targetOrder, poDate) {
    const inputItemCode = document.getElementById('inputItemCode');
    const inputPoReference = document.getElementById('inputPoReference');
    const inputSupplier = document.getElementById('inputSupplier');
    const inputItemName = document.getElementById('inputItemName');
    const selectCategory = document.getElementById('selectCategory');
    const inputTargetOrder = document.getElementById('inputTargetOrder');
    const inputReceiptDate = document.getElementById('inputReceiptDate');
    const inputPeriodMonth = document.getElementById('inputPeriodMonth');

    if (inputItemCode) inputItemCode.value = itemCode;
    if (inputPoReference) inputPoReference.value = poReference;
    if (inputSupplier) inputSupplier.value = supplier;
    if (inputItemName) inputItemName.value = name;
    if (selectCategory) selectCategory.value = categoryId || '';
    if (inputTargetOrder) inputTargetOrder.value = targetOrder || 0;
    if (poDate && inputReceiptDate) {
        inputReceiptDate.value = poDate;
        const parts = poDate.split('-');
        if (parts.length >= 2 && inputPeriodMonth) {
            inputPeriodMonth.value = parts[0] + '-' + parts[1];
        }
    }

    // Close modal
    const modalEl = document.getElementById('modalSearchItemCode');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance.hide();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // UNIFIED LIST OF ITEMS (Master PO + Realisasi PO)
    // Using *ForJs variables which are guaranteed plain PHP arrays for correct JSON serialization
    const masterItems = @json($masterListForJs);
    const realisasiItems = @json($poMonitoringListForJs);
    const combinedItemsMap = new Map();

    // 1. Process Master PO items
    masterItems.forEach(item => {
        const itemCodeKey = item.item_code || item.part_number || item.drawing || '';
        const poKey = item.po || item.po_number || item.part_number || '';
        const drawingVal = item.drawing || item.item_code || '';
        const descVal = item.description || item.name || '';
        const supplierVal = item.supplier_name || item.supplier || '';
        const targetVal = item.order_qty !== undefined ? item.order_qty : (item.qty || 0);
        const dateVal = item.po_date || item.tanggal || '';
        const key = `${itemCodeKey}___${poKey}`;
        combinedItemsMap.set(key, {
            item_code: itemCodeKey,
            po_reference: poKey,
            drawing: drawingVal,
            description: descVal,
            supplier_name: supplierVal,
            target_order: targetVal,
            po_date: dateVal,
            category_id: item.purchasing_category_id || null,
            source: 'Master PO (Step 2)'
        });
    });

    // 2. Process Realisasi PO items
    realisasiItems.forEach(item => {
        const key = `${item.item_code}___${item.po_reference}`;
        if (!combinedItemsMap.has(key)) {
            combinedItemsMap.set(key, {
                item_code: item.item_code,
                po_reference: item.po_reference,
                drawing: item.drawing || '',
                description: item.description || '',
                supplier_name: item.supplier_name || '',
                target_order: item.target_order || 0,
                po_date: item.receipt_date || '',
                category_id: item.purchasing_category_id,
                source: 'Realisasi PO (Step 3)'
            });
        } else {
            const existing = combinedItemsMap.get(key);
            existing.source = 'Master PO & Realisasi';
            if (!existing.drawing && item.drawing) {
                existing.drawing = item.drawing;
            }
            if (!existing.po_date && item.receipt_date) {
                existing.po_date = item.receipt_date;
            }
        }
    });

    const unifiedItems = Array.from(combinedItemsMap.values());

    const searchInput = document.getElementById('popupSearchInput');
    const tableBody = document.getElementById('popupItemTableBody');

    function renderPopupItems(filterText = '') {
        if (!tableBody) return;
        const lowerFilter = String(filterText || '').toLowerCase().trim();
        let html = '';

        const filtered = unifiedItems.filter(item => {
            const itemCodeStr = String(item.item_code || '').toLowerCase();
            const drawingStr = String(item.drawing || '').toLowerCase();
            const poStr = String(item.po_reference || '').toLowerCase();
            const supplierStr = String(item.supplier_name || '').toLowerCase();
            const descStr = String(item.description || '').toLowerCase();
            const sourceStr = String(item.source || '').toLowerCase();

            return itemCodeStr.includes(lowerFilter) ||
                   drawingStr.includes(lowerFilter) ||
                   poStr.includes(lowerFilter) ||
                   supplierStr.includes(lowerFilter) ||
                   descStr.includes(lowerFilter) ||
                   sourceStr.includes(lowerFilter);
        });

        if (filtered.length === 0) {
            html = `<tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    Tidak ada Item Code yang cocok dengan kata kunci "${escapeHtml(filterText)}"
                </td>
            </tr>`;
        } else {
            filtered.forEach(item => {
                const itemCodeRaw = String(item.item_code || '');
                const poRaw = String(item.po_reference || '');
                const supplierRaw = String(item.supplier_name || '');
                const descRaw = String(item.description || '');
                const poDateRaw = String(item.po_date || '');
                const targetQty = Number(item.target_order || 0);

                const escapedItemCode = escapeHtml(itemCodeRaw);
                const escapedPo = escapeHtml(poRaw);
                const escapedSupplier = escapeHtml(supplierRaw);
                const escapedDesc = escapeHtml(descRaw);
                const escapedPoDate = escapeHtml(poDateRaw);
                
                let sourceBadge = '';
                const itemSource = String(item.source || '');
                if (itemSource.includes('Master PO (Step 2)')) {
                    sourceBadge = `<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">Master PO</span>`;
                } else if (itemSource.includes('Realisasi')) {
                    sourceBadge = `<span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50">Realisasi</span>`;
                } else {
                    sourceBadge = `<span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50">Keduanya</span>`;
                }

                html += `<tr>
                    <td><span class="badge-key-primary">${escapedItemCode}</span></td>
                    <td><span class="badge-key-secondary">${escapedPo}</span></td>
                    <td><span class="small text-muted">${escapedSupplier}</span></td>
                    <td><div class="fw-bold text-white small">${escapedDesc}</div></td>
                    <td class="text-end fw-semibold text-warning">${targetQty.toLocaleString('id-ID')}</td>
                    <td>${sourceBadge}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-info px-2 py-1 fw-bold text-dark rounded" 
                            onclick="selectItemCodeFromPopup('${escapedItemCode}', '${escapedPo}', '${escapedSupplier}', '${escapedDesc}', ${item.category_id || 'null'}, ${targetQty}, '${escapedPoDate}')">
                            Pilih
                        </button>
                    </td>
                </tr>`;
            });
        }

        tableBody.innerHTML = html;
    }

    // Render immediately on page load
    renderPopupItems('');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            renderPopupItems(this.value);
        });
    }

    const modalSearchEl = document.getElementById('modalSearchItemCode');
    if (modalSearchEl) {
        modalSearchEl.addEventListener('show.bs.modal', function () {
            if (searchInput) searchInput.value = '';
            renderPopupItems('');
        });
        modalSearchEl.addEventListener('shown.bs.modal', function () {
            renderPopupItems('');
            if (searchInput) searchInput.focus();
        });
    }

    // BULK UPLOAD SHEETJS LOGIC
    const importForm = document.getElementById('importRealisasiPoForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('importFile');
            const file = fileInput.files[0];
            if (!file) return;

            const submitBtn = importForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengimpor...';

            const errorAlert = document.getElementById('importErrorAlert');
            const errorList = document.getElementById('importErrorList');
            errorAlert.classList.add('d-none');
            errorList.innerHTML = '';

            const reader = new FileReader();
            reader.onload = function(evt) {
                try {
                    const data = new Uint8Array(evt.target.result);
                    const workbook = XLSX.read(data, { type: 'array', cellDates: true });
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });
                    if (!jsonData || jsonData.length === 0) {
                        if (window.notify) {
                            window.notify.warning('File Kosong', 'Berkas Excel kosong atau tidak memiliki baris data.');
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        return;
                    }

                    const headerKeywords = {
                        itemcode: ['materialcode', 'itemcode', 'partnumber', 'partno', 'drawing', 'material', 'kodebarang', 'kodematerial', 'kodeitem', 'kodepart', 'komponen', 'mat', 'pn', 'sku', 'code', 'barang', 'item', 'part', 'drawingno'],
                        po: ['ponumber', 'nomorpo', 'kodepo', 'nopo', 'poref', 'poreference', 'noorder', 'purchaseorder', 'po', 'pono'],
                        supplier: ['suppliername', 'vendorname', 'namasupplier', 'namavendor', 'namapemasok', 'kodesupplier', 'kodevendor', 'supplier', 'vendor', 'pemasok', 'kdsupp', 'kdvendor', 'pt'],
                        tanggal: ['deliverydate', 'tanggalreceipt', 'tanggalterima', 'tanggalpo', 'tanggal', 'date', 'tgl', 'receiptdate', 'periode'],
                        name: ['description', 'deskripsibarang', 'deskripsi', 'namabarang', 'namamaterial', 'keterangan', 'itemname', 'materialname', 'partname', 'spec', 'desc'],
                        target: ['targetpo', 'targetqty', 'planqty', 'orderqty', 'plan', 'target', 'order', 'qtyplan', 'qtytarget'],
                        actual: ['actualqty', 'resultqty', 'qtyreceived', 'receivedqty', 'aktualqty', 'realisasi', 'result', 'actual', 'aktual', 'received', 'terima', 'masuk', 'qty'],
                        price: ['unitprice', 'hargasatuan', 'price', 'harga', 'rate', 'unitcost'],
                        currency: ['currency', 'kurs', 'matauang', 'curr', 'valuta']
                    };

                    // 1. Detect Header Row Index (Score first 25 rows)
                    let bestHeaderIdx = -1;
                    let maxHeaderScore = 0;

                    for (let r = 0; r < Math.min(25, jsonData.length); r++) {
                        const row = jsonData[r];
                        if (!Array.isArray(row)) continue;
                        let score = 0;
                        for (let cell of row) {
                            const clean = String(cell || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                            if (!clean) continue;
                            for (let type in headerKeywords) {
                                for (let kw of headerKeywords[type]) {
                                    if (clean === kw || clean.includes(kw) || (kw.length >= 5 && kw.includes(clean))) {
                                        score += (kw.length >= 4 ? 2 : 1);
                                        break;
                                    }
                                }
                            }
                        }
                        if (score > maxHeaderScore) {
                            maxHeaderScore = score;
                            bestHeaderIdx = r;
                        }
                    }

                    if (bestHeaderIdx === -1) bestHeaderIdx = 0;
                    const headerRow = jsonData[bestHeaderIdx] || [];

                    // 2. Map Column Indices
                    const colMap = {
                        itemcode: -1,
                        po: -1,
                        supplier: -1,
                        tanggal: -1,
                        name: -1,
                        target: -1,
                        actual: -1,
                        price: -1,
                        currency: -1
                    };

                    for (let c = 0; c < headerRow.length; c++) {
                        const clean = String(headerRow[c] || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                        if (!clean) continue;

                        for (let field in colMap) {
                            if (colMap[field] === -1) {
                                for (let kw of headerKeywords[field]) {
                                    if (clean === kw || clean.includes(kw) || (clean.length >= 4 && kw.includes(clean))) {
                                        colMap[field] = c;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    // Heuristic Fallback
                    if (colMap.itemcode === -1 || colMap.po === -1) {
                        for (let c = 0; c < headerRow.length; c++) {
                            let codeCount = 0;
                            let poCount = 0;
                            for (let r = bestHeaderIdx + 1; r < Math.min(bestHeaderIdx + 20, jsonData.length); r++) {
                                const val = String((jsonData[r] && jsonData[r][c]) || '').trim();
                                if (/^PO[\-\s0-9A-Z]+/i.test(val)) poCount++;
                                if (/^[A-Za-z0-9\-\.]{4,20}$/.test(val) && isNaN(Number(val))) codeCount++;
                            }
                            if (colMap.po === -1 && poCount > 3) colMap.po = c;
                            if (colMap.itemcode === -1 && (codeCount > 3 || (poCount > 3 && colMap.po !== c))) colMap.itemcode = c;
                        }
                    }

                    if (colMap.itemcode === -1 && colMap.po === -1) {
                        // Fallback ke Server-side Direct FormData Processing jika header client-side tidak terdeteksi
                        const formData = new FormData();
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}');
                        formData.append('file', file);

                        fetch('{{ route("purchasing.input.bulk") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => response.json().then(data => ({ status: response.status, body: data })))
                        .then(res => {
                            if (res.status === 200) {
                                if (window.notify) {
                                    window.notify.success('Import Berhasil', res.body.message || 'Data Realisasi PO berhasil diimpor!');
                                }
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalBtnText;
                                errorAlert.classList.remove('d-none');
                                let errorHtml = '';
                                if (res.body.errors && Array.isArray(res.body.errors)) {
                                    res.body.errors.forEach(err => { errorHtml += `<li>${escapeHtml(err)}</li>`; });
                                } else {
                                    errorHtml = `<li>${escapeHtml(res.body.message || 'Gagal mengimpor file.')}</li>`;
                                }
                                errorList.innerHTML = errorHtml;
                            }
                        })
                        .catch(err => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            if (window.notify) {
                                window.notify.error('Gagal Import', 'Gagal mengimpor file: ' + err.message);
                            }
                        });
                        return;
                    }

                    function parseExcelNumber(val) {
                        if (val === null || val === undefined || val === '') return 0;
                        if (typeof val === 'number') return isNaN(val) ? 0 : val;
                        let str = String(val).trim();
                        str = str.replace(/[Rp\$€¥\s]/gi, '').replace(/(USD|IDR|JPY|EUR|SGD|RP|RUPIAH)/gi, '');
                        let clean = str.replace(/[^0-9.,-]/g, '');
                        if (!clean || clean === '-') return 0;
                        
                        let lastDot = clean.lastIndexOf('.');
                        let lastComma = clean.lastIndexOf(',');
                        if (lastDot !== -1 && lastComma !== -1) {
                            if (lastComma > lastDot) {
                                clean = clean.replace(/\./g, '').replace(',', '.');
                            } else {
                                clean = clean.replace(/,/g, '');
                            }
                        } else if (lastComma !== -1) {
                            if ((clean.match(/,/g) || []).length > 1 || /,\d{3}$/.test(clean)) {
                                clean = clean.replace(/,/g, '');
                            } else {
                                clean = clean.replace(',', '.');
                            }
                        } else if (lastDot !== -1) {
                            if ((clean.match(/\./g) || []).length > 1) {
                                clean = clean.replace(/\./g, '');
                            }
                        }
                        let num = parseFloat(clean);
                        return isNaN(num) ? 0 : num;
                    }

                    const rows = [];
                    const skipList = ['ITEM CODE','ITEM_CODE','PART NUMBER','PART_NUMBER','TOTAL','GRAND TOTAL','NO','SUPPLIER NAME','MATERIAL CODE','DELIVERY DATE','PO NO.','DESCRIPTION'];

                    for (let i = bestHeaderIdx + 1; i < jsonData.length; i++) {
                        const r = jsonData[i];
                        if (!r || !Array.isArray(r) || r.every(cell => String(cell || '').trim() === '')) continue;

                        const itemCode = String(colMap.itemcode !== -1 && r[colMap.itemcode] !== undefined ? r[colMap.itemcode] : '').trim();
                        const poNum = String(colMap.po !== -1 && r[colMap.po] !== undefined ? r[colMap.po] : '').trim();

                        if (!itemCode && !poNum) continue;
                        if (skipList.includes(itemCode.toUpperCase()) || skipList.includes(poNum.toUpperCase()) || itemCode.toUpperCase().startsWith('TOTAL')) continue;

                        let tgl = r[colMap.tanggal];
                        let formattedDate = '';
                        if (typeof tgl === 'number') {
                            const dateObj = new Date(Math.round((tgl - 25569) * 86400 * 1000));
                            if (!isNaN(dateObj.getTime())) {
                                formattedDate = dateObj.toISOString().split('T')[0];
                            }
                        } else if (typeof tgl === 'string' && tgl.trim() !== '') {
                            const parts = tgl.trim().split(/[\/\-\.]/);
                            if (parts.length === 3) {
                                if (parts[2].length === 4) {
                                    formattedDate = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                                } else if (parts[0].length === 4) {
                                    formattedDate = `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
                                }
                            } else {
                                const parsedDate = new Date(tgl);
                                if (!isNaN(parsedDate.getTime())) {
                                    formattedDate = parsedDate.toISOString().split('T')[0];
                                }
                            }
                        }

                        const parsedPrice = colMap.price !== -1 ? parseExcelNumber(r[colMap.price]) : 0;
                        let rawCurr = colMap.currency !== -1 ? String(r[colMap.currency] || '').trim().toUpperCase() : '';
                        let finalCurr = (parsedPrice > 300 || rawCurr === 'IDR' || rawCurr.includes('RP')) ? 'IDR' : (rawCurr || 'USD');

                        rows.push({
                            supplier_name: colMap.supplier !== -1 ? String(r[colMap.supplier] || '').trim() : null,
                            tanggal: formattedDate || new Date().toISOString().split('T')[0],
                            item_code: itemCode,
                            description: colMap.name !== -1 ? String(r[colMap.name] || '').trim() : null,
                            po_reference: poNum,
                            currency: finalCurr,
                            price: parsedPrice,
                            target_qty: colMap.target !== -1 ? parseExcelNumber(r[colMap.target]) : 0,
                            actual_qty: colMap.actual !== -1 ? parseExcelNumber(r[colMap.actual]) : 0
                        });
                    }

                    if (rows.length === 0) {
                        if (window.notify) {
                            window.notify.warning('Tidak Ada Data', 'Tidak ada data valid yang dapat diproses dari berkas ini.');
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        return;
                    }

                    const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                    fetch('{{ route("purchasing.input.bulk") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _token: token, rows: rows })
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(res => {
                        if (res.status === 200) {
                            if (window.notify) {
                                window.notify.success('Import Berhasil', res.body.message || 'Data Incoming PO berhasil diimpor!');
                            }
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            errorAlert.classList.remove('d-none');
                            let errorHtml = '';
                            if (res.body.errors && Array.isArray(res.body.errors)) {
                                res.body.errors.forEach(err => {
                                    errorHtml += `<li>${escapeHtml(err)}</li>`;
                                });
                            } else {
                                errorHtml = `<li>${escapeHtml(res.body.message || 'Gagal menyimpan data.')}</li>`;
                            }
                            errorList.innerHTML = errorHtml;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (window.notify) {
                            window.notify.error('Kesalahan Jaringan', 'Terjadi kesalahan saat menghubungi server.');
                        }
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    });

                } catch (error) {
                    console.error(error);
                    if (window.notify) {
                        window.notify.error('Gagal Baca File', 'Gagal membaca berkas Excel: ' + error.message);
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            };
            
            reader.readAsArrayBuffer(file);
        });
    }
});
</script>

<!-- MODAL SEARCH ITEM CODE POPUP -->
<div class="modal fade" id="modalSearchItemCode" tabindex="-1" aria-labelledby="modalSearchItemCodeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-info" id="modalSearchItemCodeLabel"><i class="bi bi-window-stack me-2"></i>Cari & Pilih Item Code</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-dark text-secondary border-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" id="popupSearchInput" class="form-control bg-dark text-white border-secondary" placeholder="Ketik untuk mencari Item Code, No PO, Supplier, Deskripsi...">
                </div>
                
                <div class="table-responsive border border-secondary border-opacity-25 rounded shadow-sm" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-dark table-hover align-middle mb-0" id="popupItemTable">
                        <thead class="sticky-top bg-dark" style="z-index: 10;">
                            <tr>
                                <th>Item Code</th>
                                <th>No PO</th>
                                <th>Supplier</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Target PO</th>
                                <th>Sumber</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="popupItemTableBody">
                            <!-- Populated dynamically by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import Realisasi Incoming -->
<div class="modal fade" id="modalImportRealisasiPo" tabindex="-1" aria-labelledby="modalImportRealisasiPoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-dark border-secondary text-white">
            <div class="modal-header border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="modal-title fw-bold" id="modalImportRealisasiPoLabel">
                    <i class="bi bi-file-earmark-arrow-up-fill text-success me-2 fs-5"></i>Import Realisasi Incoming (Penerimaan Barang)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchasing.input.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-info bg-opacity-10 border border-info border-opacity-25 text-info py-2 px-3 mb-3 small rounded-3">
                        <i class="bi bi-info-circle-fill me-1"></i> <b>Step 3 — Incoming</b>: Hanya kolom <b>Result / Result Amount</b> yang akan diproses dan disimpan ke Catatan Penerimaan. Kolom Plan (jika ada pada berkas Excel EZRunner) akan diabaikan dan tidak akan mengubah Master PO.
                    </div>
                    
                    <div class="mb-3">
                        <a href="{{ route('purchasing.input.template') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
                            <i class="bi bi-download me-1"></i> Unduh Template Incoming (.xlsx)
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-warning small fw-bold">Mata Uang Default (Harga Unit)</label>
                        <select name="currency" class="form-select bg-dark text-warning border-secondary fw-bold">
                            <option value="ALL" selected>ALL / Otomatis (Multi-Currency Sesuai File Excel)</option>
                            <option value="USD">USD ($ - Dollar)</option>
                            <option value="IDR">IDR (Rp - Rupiah)</option>
                        </select>
                        <div class="form-text text-muted fs-7">Jika Excel tidak memiliki kolom 'Currency', pilihan ini yang akan dipakai.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Pilih Berkas Excel / CSV (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control form-control-dark" accept=".xlsx, .xls, .csv" required>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 py-3 px-4">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-dark fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-cloud-upload-fill me-1"></i> Mulai Impor Incoming
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bulk Delete Confirmation Incoming Log -->
<div class="modal fade" id="modalBulkDeleteLogConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-danger text-white" style="background: #111827;">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Hapus Massal Incoming Penerimaan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchasing.log.destroy-bulk') }}" method="POST" id="formBulkDeleteLog">
                @csrf
                <div class="modal-body">
                    <div id="bulkDeleteLogIdsContainer"></div>
                    Apakah Anda yakin ingin menghapus <strong id="bulkDeleteLogCountText" class="text-danger">0</strong> catatan penerimaan terpilih?
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAllLogs = document.getElementById('checkAllLogs');
        const rowCheckboxesLog = document.querySelectorAll('.row-checkbox-log');
        const btnBulkDeleteLog = document.getElementById('btnBulkDeleteLog');
        const countSpanLog = document.getElementById('bulkDeleteCountLog');

        function updateLogBulkBtn() {
            const checked = document.querySelectorAll('.row-checkbox-log:checked');
            if (btnBulkDeleteLog) {
                if (checked.length > 0) {
                    btnBulkDeleteLog.classList.remove('d-none');
                    countSpanLog.innerText = checked.length;
                } else {
                    btnBulkDeleteLog.classList.add('d-none');
                }
            }
        }

        if (checkAllLogs) {
            checkAllLogs.addEventListener('change', function() {
                rowCheckboxesLog.forEach(cb => cb.checked = this.checked);
                updateLogBulkBtn();
            });
        }

        rowCheckboxesLog.forEach(cb => {
            cb.addEventListener('change', function() {
                if (checkAllLogs) {
                    checkAllLogs.checked = (document.querySelectorAll('.row-checkbox-log:checked').length === rowCheckboxesLog.length);
                }
                updateLogBulkBtn();
            });
        });
    });

    function confirmBulkDeleteLog() {
        const checked = document.querySelectorAll('.row-checkbox-log:checked');
        if (checked.length === 0) return;
        
        const container = document.getElementById('bulkDeleteLogIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('bulkDeleteLogCountText').innerText = checked.length;
        new bootstrap.Modal(document.getElementById('modalBulkDeleteLogConfirm')).show();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<style>
.modal-dialog-scrollable .modal-content {
    max-height: 85vh !important;
    display: flex;
    flex-direction: column;
}
.modal-dialog-scrollable form {
    display: flex;
    flex-direction: column;
    max-height: 100%;
    overflow: hidden;
}
.modal-dialog-scrollable .modal-body {
    overflow-y: auto !important;
    max-height: calc(85vh - 130px) !important;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.2) transparent;
}
.modal-dialog-scrollable .modal-body::-webkit-scrollbar {
    width: 6px;
}
.modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
</style>
@include('partials.confirm-modal')
@include('partials.import-preview-modal')
<script src="{{ asset('js/kawai-notify.js') }}"></script>
<script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
