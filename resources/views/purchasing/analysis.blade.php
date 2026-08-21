<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komparasi Aktual vs Forecast | PT Kawai Indonesia</title>
    <meta name="description" content="Perbandingan rencana Forecast vs Realisasi Aktual Penerimaan PT Kawai Indonesia.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg-dark: #0A0E1A;
            --bg-primary: #0A0E1A;
            --bg-secondary: #121826;
            --card-bg: rgba(18, 24, 38, 0.75);
            --card-border: rgba(255,255,255,0.08);
            --accent-gold: #e2b34a;
            --accent-blue: #3b82f6;
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --accent-purple: #8b5cf6;
            --text-main: #f3f4f6;
            --text-muted: #cbd5e1;
        }
        * { box-sizing: border-box; }
        body { background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%); color: var(--text-main); font-family: 'Inter', sans-serif; min-height: 100vh; }
        h1,h2,h3,h4,h5,.brand-font { font-family: 'Outfit', sans-serif; }

        /* NAVBAR */
        .top-navbar { background: rgba(18, 24, 38, 0.88); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid var(--card-border); padding: 0.85rem 1.75rem; position: sticky; top: 0; z-index: 1000; }
        .text-muted, .text-secondary { color: #cbd5e1 !important; }

        /* GLASS CARD */
        .glass-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 1.65rem; box-shadow: 0 10px 30px rgba(0,0,0,0.3); backdrop-filter: blur(12px); margin-bottom: 1.75rem; }

        /* FILTER */
        .filter-select { background: #111827; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; padding: 0.5rem 1rem; font-size: 0.88rem; }
        .filter-select:focus { border-color: var(--accent-blue); outline: none; }

        /* TABLE */
        .table-container { overflow-x: auto; border-radius: 14px; border: 1px solid var(--card-border); background: var(--card-bg); }
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; color: var(--text-main); font-size: 0.85rem; }
        .table-custom thead th { background: rgba(18, 24, 38, 0.95) !important; color: #cbd5e1 !important; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; padding: 0.85rem 1rem; border-bottom: 2px solid rgba(255, 255, 255, 0.12) !important; font-weight: 700; white-space: nowrap; text-align: center; }
        /* TABLE CURRENCY MODE VISIBILITY */
        table.currency-mode-usd .val-idr { display: none !important; }
        table.currency-mode-usd .val-usd { display: block !important; }
        table.currency-mode-idr .val-usd { display: none !important; }
        table.currency-mode-idr .val-idr { display: block !important; }
        
        .table-custom tbody tr { border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important; transition: background 0.15s; }
        .table-custom tbody tr:hover td { background: rgba(255, 255, 255, 0.08) !important; color: #ffffff !important; }
        .table-custom td { padding: 0.85rem 1rem; vertical-align: middle; background: rgba(18, 24, 38, 0.45) !important; color: #F3F4F6 !important; text-align: center; }

        /* RATIO BADGE */
        .ratio-red { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
        .ratio-yellow { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
        .ratio-green { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
        .ratio-muted { background: rgba(107, 114, 128, 0.2); color: #9ca3af; border: 1px solid rgba(107, 114, 128, 0.4); }

        /* MINIMALIST SEGMENTED CONTROL BUTTONS */
        .segmented-control {
            display: inline-flex;
            align-items: center;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 9999px;
            padding: 5px 8px;
            gap: 6px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.35);
        }
        .segmented-btn {
            border: 1px solid transparent !important;
            background: transparent !important;
            color: #94a3b8 !important;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 7px 16px;
            border-radius: 9999px !important;
            transition: all 0.2s ease-in-out;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            text-decoration: none;
            line-height: 1.3;
        }
        .segmented-btn:hover {
            color: #f1f5f9 !important;
            background: rgba(255, 255, 255, 0.08) !important;
        }
        .segmented-btn.active {
            background: rgba(245, 158, 11, 0.22) !important;
            color: #fbbf24 !important;
            font-weight: 700 !important;
            border-color: rgba(245, 158, 11, 0.45) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        .segmented-btn-xs {
            font-size: 0.72rem;
            padding: 4px 10px;
        }

        /* Keep the dense analysis controls comfortable on smaller screens. */
        @media (max-width: 991.98px) {
            .top-navbar { padding: 0.85rem 1rem; }
            .analysis-shell { padding: 1rem !important; }
            .analysis-stepper-links {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                padding: 0.2rem 0 0.45rem;
                width: 100%;
                scrollbar-width: thin;
            }
            .analysis-stepper-links > * { flex: 0 0 auto; }
        }

        @media (max-width: 575.98px) {
            .glass-card { padding: 1rem; border-radius: 13px; }
            .analysis-filter-form { width: 100%; }
            .analysis-filter-form > div,
            .analysis-filter-form .filter-select { width: 100%; }
            .analysis-tab-switcher .nav-link { width: 100%; text-align: left; }
            .fx-toolbar, .fx-chart-meta { align-items: stretch !important; }
            .segmented-control { width: 100%; overflow-x: auto; justify-content: flex-start; }
            .segmented-btn { flex: 0 0 auto; white-space: nowrap; }
            .fx-chart-meta { gap: 0.65rem !important; }
        }

        /* ─── CUSTOM DARK GLASS PAGINATION ─── */
        .pagination {
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 5px;
            list-style: none;
            padding-left: 0;
        }
        .pagination .page-item .page-link {
            background: rgba(18, 24, 38, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 0.38rem 0.75rem !important;
            font-size: 0.82rem !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            transition: all 0.2s ease-in-out !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
        }
        .pagination .page-item .page-link:hover {
            background: rgba(0, 210, 255, 0.18) !important;
            border-color: rgba(0, 210, 255, 0.5) !important;
            color: #00d2ff !important;
            box-shadow: 0 0 10px rgba(0, 210, 255, 0.25);
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #00d2ff 0%, #0088cc 100%) !important;
            border-color: #00d2ff !important;
            color: #0a0e1a !important;
            font-weight: 800 !important;
            box-shadow: 0 0 12px rgba(0, 210, 255, 0.45);
        }
        .pagination .page-item.disabled .page-link {
            background: rgba(18, 24, 38, 0.4) !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
            color: #475569 !important;
            cursor: not-allowed;
            pointer-events: none;
        }
        .pagination svg, nav svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
    </style>
</head>
<body>

@php
    if (!function_exists('localFormatRatioDisplay')) {
        function localFormatRatioDisplay($ratio) {
            if (!$ratio || $ratio === '-' || $ratio === '#DIV/0!' || $ratio === 'DIV/0' || $ratio === 'No Demand') return '-';
            if ($ratio === 'Unplanned') return 'Unplanned';
            return $ratio;
        }
    }
    if (!function_exists('localGetRatioClass')) {
        function localGetRatioClass($ratio) {
            if (!$ratio || $ratio === '-' || $ratio === '#DIV/0!' || $ratio === 'DIV/0' || $ratio === 'No Demand') return 'ratio-muted';
            if ($ratio === 'Unplanned') return 'ratio-yellow';
            $num = (int)str_replace('%', '', $ratio);
            if ($num < 100) return 'ratio-red';
            if ($num > 200) return 'ratio-green';
            return 'ratio-yellow';
        }
    }
    if (!function_exists('localGetAchievementClass')) {
        function localGetAchievementClass($val) {
            if (!$val || $val === '-' || $val === 'No Demand') return 'badge bg-secondary bg-opacity-25 text-muted border border-secondary border-opacity-50';
            if ($val === 'Unplanned') return 'badge bg-purple bg-opacity-25 text-warning border border-warning border-opacity-50';
            $num = (float)str_replace(['%', ','], ['', '.'], $val);
            if ($num >= 95 && $num <= 105) return 'badge bg-success bg-opacity-25 text-success border border-success border-opacity-50';
            if ($num < 95) return 'badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50';
            return 'badge bg-info bg-opacity-25 text-info border border-info border-opacity-50';
        }
    }
    if (!function_exists('localGetStatusBadgeClass')) {
        function localGetStatusBadgeClass($status) {
            return match($status) {
                'Sesuai' => 'badge bg-success bg-opacity-25 text-success border border-success border-opacity-50',
                'Under Forecast' => 'badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50',
                'Over Forecast' => 'badge bg-info bg-opacity-25 text-info border border-info border-opacity-50',
                'Unplanned', 'Unplanned Actual' => 'badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50',
                default => 'badge bg-secondary bg-opacity-25 text-muted border border-secondary border-opacity-50',
            };
        }
    }
    if (!function_exists('formatAmountCustom')) {
        function formatAmountCustom($val) {
            if ($val === null || $val === '' || $val == 0) return '$ -';
            if ($val < 0) return '$ (' . number_format(abs($val), 2, ',', '.') . ')';
            return '$ ' . number_format($val, 2, ',', '.');
        }
    }
@endphp

{{-- NAVBAR --}}
<nav class="top-navbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard.overview') }}" class="text-decoration-none">
            <div class="d-flex align-items-center gap-2 mb-0.5">
                <i class="bi bi-music-note-beamed text-warning fs-4" style="line-height: 1; vertical-align: middle;"></i>
                <span class="brand-logo-text" style="font-size: 1.25rem; font-weight: 800; letter-spacing: 0.04em;">PT KAWAI INDONESIA</span>
            </div>
            <div class="text-muted" style="font-size: 0.73rem; letter-spacing: 0.02em; margin-left: 2px;">Dashboard Monitoring Purchasing &amp; Pengadaan Bahan Baku Piano</div>
        </a>
    </div>
    <div>
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.analysis', 'hasFaqModal' => true])
    </div>
</nav>

<div class="container-dashboard py-4 analysis-shell">

    <!-- 7-STEP UNIFIED WORKFLOW STEPPER -->
    @include('partials.workflow-stepper', ['currentStep' => 7])

    <!-- STANDARDIZED PAGE HEADER & ACTION HIERARCHY -->
    <div class="kawai-page-header">
        <div class="kawai-page-header-left">
            <div class="page-icon-box" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35);">
                <i class="bi bi-pie-chart-fill text-danger"></i>
            </div>
            <div>
                <h1 class="page-title-text">Hasil Akhir &amp; Komparasi 3-Slide</h1>
                <p class="page-subtitle-text">Komparasi komprehensif rencana forecast vs realisasi, roll-forward stok, dan analisis ketercukupan supply PT Kawai Indonesia.</p>
            </div>
        </div>
        <div class="kawai-page-actions">
            <a href="{{ route('purchasing.outstanding') }}" class="btn-kawai-secondary">
                <i class="bi bi-arrow-clockwise text-info"></i> Kembali ke Forecast
            </a>
            @include('partials.kurs-kpi-banner')
        </div>
    </div>

    {{-- ── STEP 6 SLIDE SWITCHER TABS ── --}}
    <ul class="nav nav-tabs nav-tabs-custom mb-4 flex-wrap gap-2 analysis-tab-switcher" id="step6SlideTabs" role="tablist" style="border-bottom:2px solid rgba(0,210,255,0.25) !important;">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeSlide ?? 'slide1') === 'slide1' ? 'active' : '' }} rounded-top-3 px-4 py-2.5 fw-bold" id="tab-slide1-btn" data-bs-toggle="tab" data-bs-target="#slide1-content" type="button" role="tab" aria-controls="slide1-content" aria-selected="{{ ($activeSlide ?? 'slide1') === 'slide1' ? 'true' : 'false' }}" onclick="window.switchFxChartMode(window.currentFxChartMode || 'amount_usd')" style="font-size:0.95rem;">
                <i class="bi bi-scale-balanced me-2 text-info fs-5"></i>
                <span>Slide 1: Komparasi Kurs &amp; Financial Analysis</span>
                @if(($activeFilterCountS1 ?? 0) > 0)
                    <span class="badge bg-info text-dark rounded-pill px-2 py-0.5 ms-2" style="font-size: 0.72rem;">{{ $activeFilterCountS1 }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeSlide ?? 'slide1') === 'slide2' ? 'active' : '' }} rounded-top-3 px-4 py-2.5 fw-bold" id="tab-slide2-btn" data-bs-toggle="tab" data-bs-target="#slide2-content" type="button" role="tab" aria-controls="slide2-content" aria-selected="{{ ($activeSlide ?? 'slide1') === 'slide2' ? 'true' : 'false' }}" onclick="resizeSlide2Charts()" style="font-size:0.95rem;">
                <i class="bi bi-graph-up-arrow me-2 text-warning fs-5"></i>
                <span>Slide 2: Infografis Tren &amp; Komparasi Outstanding</span>
                @if(($activeFilterCountS2 ?? 0) > 0)
                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 ms-2" style="font-size: 0.72rem;">{{ $activeFilterCountS2 }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeSlide ?? 'slide1') === 'slide3' ? 'active' : '' }} rounded-top-3 px-4 py-2.5 fw-bold" id="tab-slide3-btn" data-bs-toggle="tab" data-bs-target="#slide3-content" type="button" role="tab" aria-controls="slide3-content" aria-selected="{{ ($activeSlide ?? 'slide1') === 'slide3' ? 'true' : 'false' }}" onclick="resizeSlide3Charts()" style="font-size:0.95rem;">
                <i class="bi bi-boxes me-2 text-purple fs-5" style="color: #a78bfa;"></i>
                <span>Slide 3: Analisis Stock Forecast vs Stock Actual</span>
                @if(($activeFilterCountS3 ?? 0) > 0)
                    <span class="badge bg-purple text-white rounded-pill px-2 py-0.5 ms-2" style="font-size: 0.72rem; background: #8b5cf6;">{{ $activeFilterCountS3 }}</span>
                @endif
            </button>
        </li>
    </ul>

    <div class="tab-content" id="step6SlideTabContent">
        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- SLIDE 1: KOMPARASI KURS & FINANCIAL ANALYSIS                     --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade {{ ($activeSlide ?? 'slide1') === 'slide1' ? 'show active' : '' }}" id="slide1-content" role="tabpanel" aria-labelledby="tab-slide1-btn">

            <!-- 🎛️ INDEPENDENT FILTER PANEL: SLIDE 1 -->
            <div class="glass-card mb-4 p-3 border border-info border-opacity-30" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.85) 100%);">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-funnel-fill text-info fs-5"></i>
                        <h6 class="fw-bold text-white mb-0">Filter Khusus Slide 1 (Kurs &amp; Financial)</h6>
                        @if(($activeFilterCountS1 ?? 0) > 0)
                            <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 fw-bold">{{ $activeFilterCountS1 }} Filter Aktif</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-25 text-muted rounded-pill px-2.5 py-1">Semua Data</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-info rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilterSlide1" aria-expanded="{{ ($activeFilterCountS1 ?? 0) > 0 ? 'true' : 'false' }}">
                            <i class="bi bi-sliders me-1"></i> Buka / Tutup Filter <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                    </div>
                </div>

                @if(($activeFilterCountS1 ?? 0) > 0)
                    <div class="d-flex flex-wrap gap-1.5 align-items-center mt-2.5 pt-2 border-top border-secondary border-opacity-25">
                        <small class="text-muted me-1 fw-semibold">Filter Aktif:</small>
                        @foreach($activeFiltersListS1 as $af)
                            <span class="badge bg-dark border border-info text-info rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1">
                                {{ $af['label'] }}
                                <a href="{{ route('purchasing.analysis', array_merge(request()->query(), ['s1_'.$af['key'] => ($af['key'] === 'year' ? '2026' : ($af['key'] === 'duration' ? 8 : 'ALL')), 'active_slide' => 'slide1'])) }}" class="text-info text-decoration-none ms-1" title="Hapus filter">&times;</a>
                            </span>
                        @endforeach
                        <a href="{{ route('purchasing.analysis', ['reset_slide' => 'slide1', 'active_slide' => 'slide1']) }}" class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill px-2.5 py-1.5 text-decoration-none ms-auto">
                            <i class="bi bi-x-circle me-1"></i> Reset Slide 1
                        </a>
                    </div>
                @endif

                <div class="collapse {{ ($activeFilterCountS1 ?? 0) > 0 ? 'show' : '' }}" id="collapseFilterSlide1">
                    <form method="GET" action="{{ route('purchasing.analysis') }}" class="pt-3 mt-2 border-top border-secondary border-opacity-25">
                        <input type="hidden" name="active_slide" value="slide1">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">Item Code</label>
                                <select name="s1_item_code" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s1_item_code ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua Item --</option>
                                    @foreach($availableItemCodes as $code)
                                        <option value="{{ $code }}" {{ ($s1_item_code ?? 'ALL') === $code ? 'selected' : '' }}>{{ $code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">Vendor</label>
                                <select name="s1_vendor" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s1_vendor ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua Vendor --</option>
                                    @foreach($availableVendors as $v)
                                        <option value="{{ $v }}" {{ ($s1_vendor ?? 'ALL') === $v ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">PIC / Buyer</label>
                                <select name="s1_pic" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s1_pic ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua PIC --</option>
                                    @foreach($availablePics as $p)
                                        <option value="{{ $p }}" {{ ($s1_pic ?? 'ALL') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">No. PO</label>
                                <select name="s1_po" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s1_po ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua PO --</option>
                                    @foreach($availablePoNumbers as $poNum)
                                        <option value="{{ $poNum }}" {{ ($s1_po ?? 'ALL') === $poNum ? 'selected' : '' }}>{{ $poNum }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">Pengantaran</label>
                                <select name="s1_delivery_category" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s1_delivery_category ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua --</option>
                                    @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                        <option value="{{ $dc->code }}" {{ ($s1_delivery_category ?? 'ALL') === $dc->code ? 'selected' : '' }}>{{ $dc->code }} - {{ $dc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-1">
                                <label class="form-label text-muted small mb-1 fw-semibold">Tahun</label>
                                <select name="s1_year" class="form-select form-select-sm bg-dark text-warning border-secondary fw-bold">
                                    <option value="ALL" {{ (string)($s1_year ?? '2026') === 'ALL' ? 'selected' : '' }}>All</option>
                                    @foreach(($availableYears ?? [2025, 2026, 2027, 2028]) as $yr)
                                        <option value="{{ $yr }}" {{ (string)($s1_year ?? '2026') === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-1">
                                <label class="form-label text-muted small mb-1 fw-semibold">Durasi</label>
                                <select name="s1_duration" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    @for($d = 1; $d <= 36; $d++)
                                        <option value="{{ $d }}" {{ ($s1_duration ?? 8) == $d ? 'selected' : '' }}>{{ $d }} Bln</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-12 d-flex justify-content-end gap-2 mt-2">
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                                    <i class="bi bi-check2-circle me-1"></i> Terapkan Filter Slide 1
                                </button>
                                <a href="{{ route('purchasing.analysis', ['reset_slide' => 'slide1', 'active_slide' => 'slide1']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-x-circle me-1"></i> Reset Slide 1
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(empty($displayGridS1) || $displayGridS1->isEmpty())
                <div class="alert alert-warning text-center my-4 py-4 rounded-4 shadow-sm border border-warning border-opacity-50">
                    <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2 text-warning"></i>
                    <h6 class="fw-bold text-white">Tidak ada data yang sesuai dengan filter Slide 1</h6>
                    <p class="text-muted small mb-3">Silakan sesuaikan filter atau reset untuk melihat semua data.</p>
                    <a href="{{ route('purchasing.analysis', ['reset_slide' => 'slide1', 'active_slide' => 'slide1']) }}" class="btn btn-sm btn-outline-warning rounded-pill px-4">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter Slide 1
                    </a>
                </div>
            @endif

    {{-- ── SLIDE 1 EXECUTIVE DATA STATUS & SCORECARD BAR ── --}}
    <div class="glass-card mb-4 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: linear-gradient(135deg, rgba(18,24,38,0.92) 0%, rgba(10,14,26,0.95) 100%); border:1px solid rgba(0,210,255,0.35);">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="badge bg-dark border border-info text-info px-3 py-1.5 rounded-pill fw-bold" style="font-size:0.78rem;">
                <i class="bi bi-shield-check me-1"></i> Data Completeness &amp; QC
            </span>
            <div class="d-flex align-items-center gap-2.5 flex-wrap text-white small" style="font-size:0.82rem;">
                <span class="d-inline-flex align-items-center gap-1 text-success fw-semibold">
                    <i class="bi bi-check-circle-fill"></i> Forecast: {{ $slide1ExecutiveSummary->total_months_count }} Bulan
                </span>
                <span class="text-muted">•</span>
                <span class="d-inline-flex align-items-center gap-1 text-success fw-semibold">
                    <i class="bi bi-check-circle-fill"></i> Incoming: {{ $slide1ExecutiveSummary->validated_months_count }} Bulan Valid
                </span>
                <span class="text-muted">•</span>
                <span class="d-inline-flex align-items-center gap-1 text-info fw-semibold font-monospace" style="font-size:0.78rem;" title="QC Audit: {{ $analysisPeriodMetadata->qc_audit->forecast_records ?? 0 }} Records • {{ $analysisPeriodMetadata->qc_audit->actual_records ?? 0 }} Receipts Valid • 0 Division-by-Zero">
                    <i class="bi bi-shield-lock-fill text-info"></i> QC Audit: {{ $analysisPeriodMetadata->qc_audit->forecast_records ?? 0 }} Records • {{ $analysisPeriodMetadata->qc_audit->actual_records ?? 0 }} Valid
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success bg-opacity-25 text-success border border-success px-3 py-1.5 rounded-pill fw-bold font-monospace" style="font-size:0.8rem;">
                <i class="bi bi-check2-circle me-1"></i> Ketercapaian: {{ $slide1ExecutiveSummary->completion_pct }}%
            </span>
            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFaqPurchasing" onclick="showFaqSection('faq-period-alignment')" style="font-size:0.78rem;" title="Buka Panduan Sinkronisasi Alur 3-Layer & QC Audit">
                <i class="bi bi-clock-history"></i> Panduan Alur &amp; QC
            </button>
        </div>
    </div>

    {{-- SUMMARY KPI CARDS (RECONCILED WITH COMPARISON ANALYSIS SERVICE) --}}
    <div class="row g-3 g-xl-4 mb-4">
        {{-- Card 1: Forecast Amount (Total Spending Summary) --}}
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-card-blue">
                <div class="kpi-header">
                    <span class="kpi-title" title="Total Nilai / Biaya Pengadaan yang Direncanakan (12 Bulan)">TOTAL FORECAST AMOUNT</span>
                    <div class="kpi-icon-box icon-blue">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="kpi-value text-info" style="font-size:1.55rem;">
                    $ {{ number_format($slide1ExecutiveSummary->total_forecast_amount_usd, 2, '.', ',') }}
                </div>
                <div class="kpi-footer">
                    <span class="text-muted small font-monospace">Rp {{ number_format($slide1ExecutiveSummary->total_forecast_amount_idr, 0, ',', '.') }} ({{ number_format($slide1ExecutiveSummary->total_forecast_qty, 0, ',', '.') }} Unit)</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Forecast Unit Price (Weighted Average per PCS) --}}
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-card-cyan">
                <div class="kpi-header">
                    <span class="kpi-title" title="Rata-rata Tertimbang Harga Satuan per 1 PCS Material (Total Amount / Total Qty)">AVG FORECAST UNIT PRICE</span>
                    <div class="kpi-icon-box icon-cyan">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                </div>
                <div class="kpi-value text-white" style="font-size:1.55rem;">
                    $ {{ number_format($slide1ExecutiveSummary->avg_forecast_price_usd, 2, '.', ',') }} <span class="text-muted fs-6 fw-normal">/ PCS</span>
                </div>
                <div class="kpi-footer">
                    <span class="text-muted small font-monospace">Rp {{ number_format($slide1ExecutiveSummary->avg_forecast_price_idr, 0, ',', '.') }} / PCS (Weighted)</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Incoming Amount (Total Realization Summary) --}}
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-card-emerald">
                <div class="kpi-header">
                    <span class="kpi-title" title="Total Realisasi Penerimaan Fisik Barang Valid">TOTAL INCOMING AMOUNT</span>
                    <div class="kpi-icon-box icon-emerald">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
                <div class="kpi-value" style="font-size:1.55rem; color:#34d399;">
                    $ {{ number_format($slide1ExecutiveSummary->total_incoming_amount_usd, 2, '.', ',') }}
                </div>
                <div class="kpi-footer">
                    <span class="text-success small font-monospace">Rp {{ number_format($slide1ExecutiveSummary->total_incoming_amount_idr, 0, ',', '.') }} ({{ number_format($slide1ExecutiveSummary->total_incoming_qty, 0, ',', '.') }} Unit)</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Incoming Unit Price (Weighted Average per PCS) --}}
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-card-amber">
                <div class="kpi-header">
                    <span class="kpi-title" title="Rata-rata Tertimbang Harga Satuan Aktual per 1 PCS Material">AVG INCOMING UNIT PRICE</span>
                    <div class="kpi-icon-box icon-amber">
                        <i class="bi bi-tag"></i>
                    </div>
                </div>
                <div class="kpi-value text-warning" style="font-size:1.55rem;">
                    $ {{ number_format($slide1ExecutiveSummary->avg_incoming_price_usd, 2, '.', ',') }} <span class="text-muted fs-6 fw-normal">/ PCS</span>
                </div>
                <div class="kpi-footer">
                    <span class="text-muted small font-monospace">Rp {{ number_format($slide1ExecutiveSummary->avg_incoming_price_idr, 0, ',', '.') }} / PCS (Weighted)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- EXECUTIVE PERIOD SUMMARY MATRIX (SEMESTER 1, SEMESTER 2 & ANNUAL SNAPSHOT) --}}
    <div class="glass-card mb-4 p-3.5" style="background: rgba(15, 23, 42, 0.88); border: 1px solid rgba(0, 210, 255, 0.28); border-radius: 16px;">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2.5 border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(0,210,255,0.15);border:1px solid rgba(0,210,255,0.35);color:#00d2ff;">
                    <i class="bi bi-calendar3-range fs-6"></i>
                </div>
                <div>
                    <h6 class="text-white fw-bold mb-0 brand-font" style="letter-spacing:0.03em; font-size:0.95rem;">EXECUTIVE SUMMARY MATRIX</h6>
                    <small class="text-muted" style="font-size:0.76rem;">Ringkasan Komparasi Semester 1, Semester 2 &amp; Total Fiskal Tahunan</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark border border-secondary text-info px-2.5 py-1 rounded-pill font-monospace" style="font-size:0.75rem;">
                    <i class="bi bi-currency-dollar me-1"></i> Multi-Period Snapshot
                </span>
            </div>
        </div>

        <div class="row g-3 align-items-stretch">
            {{-- Semester 1 --}}
            <div class="col-12 col-lg-4">
                <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(180deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(56, 189, 248, 0.25); box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <!-- Header -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-25 text-info border border-primary border-opacity-40 px-2.5 py-1 rounded-pill font-monospace fw-bold" style="font-size:0.75rem;">
                                    <i class="bi bi-calendar-event me-1"></i>Semester 1 (Jul &ndash; Des)
                                </span>
                            </div>
                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-40 rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.72rem;">
                                <i class="bi bi-check-circle me-1"></i>2 Bulan Valid
                            </span>
                        </div>

                        <!-- Forecast Box -->
                        <div class="p-2.5 rounded-3 mb-2.5" style="background: rgba(10, 15, 29, 0.7); border: 1px solid rgba(255, 255, 255, 0.06);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small text-uppercase" style="font-size:0.7rem; letter-spacing:0.04em;"><i class="bi bi-graph-up text-info me-1"></i>Forecast Spending</span>
                                <span class="badge bg-info bg-opacity-15 text-info font-monospace" style="font-size:0.68rem;">Rencana</span>
                            </div>
                            <div class="fw-bold text-white font-monospace" style="font-size:1.15rem;">
                                $ {{ number_format($slide1ExecutiveSummary->sem1->forecast_amount_usd, 2, '.', ',') }}
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1.5 pt-1.5 border-top border-secondary border-opacity-20 font-monospace text-muted" style="font-size:0.75rem;">
                                <span><i class="bi bi-tag text-info me-1"></i>$ {{ number_format($slide1ExecutiveSummary->sem1->avg_forecast_price_usd, 2, '.', ',') }} / PCS</span>
                                <span><i class="bi bi-boxes text-muted me-1"></i>{{ number_format($slide1ExecutiveSummary->sem1->forecast_qty ?? 0, 0, ',', '.') }} Unit</span>
                            </div>
                        </div>

                        <!-- Incoming Box -->
                        <div class="p-2.5 rounded-3" style="background: rgba(10, 15, 29, 0.7); border: 1px solid rgba(16, 185, 129, 0.18);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small text-uppercase" style="font-size:0.7rem; letter-spacing:0.04em;"><i class="bi bi-wallet2 text-success me-1"></i>Realisasi Incoming</span>
                                <span class="badge bg-success bg-opacity-15 text-success font-monospace" style="font-size:0.68rem;">Tervalidasi</span>
                            </div>
                            <div class="fw-bold text-success font-monospace" style="font-size:1.15rem;">
                                $ {{ number_format($slide1ExecutiveSummary->sem1->incoming_amount_usd, 2, '.', ',') }}
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1.5 pt-1.5 border-top border-secondary border-opacity-20 font-monospace" style="font-size:0.75rem;">
                                <span class="text-warning"><i class="bi bi-tag text-warning me-1"></i>$ {{ number_format($slide1ExecutiveSummary->sem1->avg_incoming_price_usd, 2, '.', ',') }} / PCS</span>
                                <span class="text-muted"><i class="bi bi-box-seam text-success me-1"></i>{{ number_format($slide1ExecutiveSummary->sem1->incoming_qty ?? 0, 0, ',', '.') }} Unit</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div class="mt-2.5 pt-2 border-top border-secondary border-opacity-20 d-flex justify-content-between align-items-center" style="font-size:0.73rem;">
                        <span class="text-muted">Status: <strong class="text-success">2 Bln Incoming Valid</strong></span>
                        <span class="text-info font-monospace">H1 Performance</span>
                    </div>
                </div>
            </div>

            {{-- Semester 2 --}}
            <div class="col-12 col-lg-4">
                <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(180deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(245, 158, 11, 0.25); box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <!-- Header -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-40 px-2.5 py-1 rounded-pill font-monospace fw-bold" style="font-size:0.75rem;">
                                    <i class="bi bi-calendar-event me-1"></i>Semester 2 (Jan &ndash; Jun)
                                </span>
                            </div>
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-40 rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.72rem;">
                                <i class="bi bi-hourglass-split me-1"></i>Planned Horizon
                            </span>
                        </div>

                        <!-- Forecast Box -->
                        <div class="p-2.5 rounded-3 mb-2.5" style="background: rgba(10, 15, 29, 0.7); border: 1px solid rgba(255, 255, 255, 0.06);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small text-uppercase" style="font-size:0.7rem; letter-spacing:0.04em;"><i class="bi bi-graph-up text-info me-1"></i>Forecast Spending</span>
                                <span class="badge bg-info bg-opacity-15 text-info font-monospace" style="font-size:0.68rem;">Rencana</span>
                            </div>
                            <div class="fw-bold text-white font-monospace" style="font-size:1.15rem;">
                                $ {{ number_format($slide1ExecutiveSummary->sem2->forecast_amount_usd, 2, '.', ',') }}
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1.5 pt-1.5 border-top border-secondary border-opacity-20 font-monospace text-muted" style="font-size:0.75rem;">
                                <span><i class="bi bi-tag text-info me-1"></i>$ {{ number_format($slide1ExecutiveSummary->sem2->avg_forecast_price_usd, 2, '.', ',') }} / PCS</span>
                                <span><i class="bi bi-boxes text-muted me-1"></i>{{ number_format($slide1ExecutiveSummary->sem2->forecast_qty ?? 0, 0, ',', '.') }} Unit</span>
                            </div>
                        </div>

                        <!-- Incoming Box (Planned / No Incoming Yet) -->
                        <div class="p-2.5 rounded-3" style="background: rgba(10, 15, 29, 0.7); border: 1px dashed rgba(245, 158, 11, 0.3);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small text-uppercase" style="font-size:0.7rem; letter-spacing:0.04em;"><i class="bi bi-wallet2 text-muted me-1"></i>Realisasi Incoming</span>
                                <span class="badge bg-warning bg-opacity-15 text-warning font-monospace" style="font-size:0.68rem;">Future</span>
                            </div>
                            <div class="text-muted font-monospace" style="font-size:1.15rem;">
                                — <span style="font-size:0.8rem; font-family:'Inter',sans-serif;">(Belum Ada Transaksi)</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1.5 pt-1.5 border-top border-secondary border-opacity-20 font-monospace text-muted" style="font-size:0.75rem;">
                                <span><i class="bi bi-clock-history text-warning me-1"></i>Status: Future Planned</span>
                                <span><i class="bi bi-box-seam text-muted me-1"></i>{{ number_format($slide1ExecutiveSummary->sem2->forecast_qty ?? 0, 0, ',', '.') }} Unit Demand</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div class="mt-2.5 pt-2 border-top border-secondary border-opacity-20 d-flex justify-content-between align-items-center" style="font-size:0.73rem;">
                        <span class="text-muted">Status: <strong class="text-warning">Menunggu Eksekusi PO</strong></span>
                        <span class="text-warning font-monospace">H2 Pipeline</span>
                    </div>
                </div>
            </div>

            {{-- Annual Summary Total --}}
            <div class="col-12 col-lg-4">
                <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(180deg, rgba(14, 38, 59, 0.8) 0%, rgba(10, 22, 38, 0.95) 100%); border: 1.5px solid rgba(0, 210, 255, 0.45); box-shadow: 0 4px 25px rgba(0, 210, 255, 0.12);">
                    <!-- Header -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-info border-opacity-30">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info text-dark px-2.5 py-1 rounded-pill font-monospace fw-bold" style="font-size:0.75rem;">
                                    <i class="bi bi-award-fill me-1"></i>Full Fiscal Year 2026/2027
                                </span>
                            </div>
                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-40 rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size:0.72rem;">
                                <i class="bi bi-pie-chart-fill me-1"></i>Ketercapaian: {{ $slide1ExecutiveSummary->completion_pct }}%
                            </span>
                        </div>

                        <!-- Annual Totals Grid -->
                        <div class="p-2.5 rounded-3 mb-2.5" style="background: rgba(10, 15, 29, 0.85); border: 1px solid rgba(0, 210, 255, 0.2);">
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="text-muted small" style="font-size:0.78rem;">Total Forecast (12 Bln):</span>
                                <span class="fw-bold text-info font-monospace" style="font-size:0.95rem;">$ {{ number_format($slide1ExecutiveSummary->total_forecast_amount_usd, 2, '.', ',') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="text-muted small" style="font-size:0.78rem;">Total Realisasi Incoming:</span>
                                <span class="fw-bold text-success font-monospace" style="font-size:0.95rem;">$ {{ number_format($slide1ExecutiveSummary->total_incoming_amount_usd, 2, '.', ',') }}</span>
                            </div>
                        </div>

                        <!-- Performance Highlights -->
                        <div class="p-2.5 rounded-3" style="background: rgba(10, 15, 29, 0.85); border: 1px solid rgba(16, 185, 129, 0.25);">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <span class="text-muted small" style="font-size:0.76rem;">Efisiensi Biaya (Variance):</span>
                                @if(($slide1ExecutiveSummary->variance_amount_usd ?? 0) <= 0)
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-40 font-monospace fw-bold" style="font-size:0.78rem;">
                                        <i class="bi bi-arrow-down-right me-0.5"></i>Hemat $ {{ number_format(abs($slide1ExecutiveSummary->variance_amount_usd), 2, '.', ',') }} ({{ abs($slide1ExecutiveSummary->variance_amount_pct) }}%)
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-40 font-monospace fw-bold" style="font-size:0.78rem;">
                                        <i class="bi bi-arrow-up-right me-0.5"></i>Over +$ {{ number_format($slide1ExecutiveSummary->variance_amount_usd, 2, '.', ',') }} (+{{ $slide1ExecutiveSummary->variance_amount_pct }}%)
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-1.5 border-top border-secondary border-opacity-20">
                                <span class="text-muted small" style="font-size:0.76rem;">Harga Rata-rata Unit:</span>
                                <span class="text-white font-monospace fw-semibold" style="font-size:0.82rem;">
                                    $ {{ number_format($slide1ExecutiveSummary->avg_forecast_price_usd, 2, '.', ',') }} &rarr; <strong class="text-warning">$ {{ number_format($slide1ExecutiveSummary->avg_incoming_price_usd, 2, '.', ',') }}</strong> / PCS
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div class="mt-2.5 pt-2 border-top border-info border-opacity-25 d-flex justify-content-between align-items-center" style="font-size:0.73rem;">
                        <span class="text-muted">Total Volume: <strong class="text-light">{{ number_format($slide1ExecutiveSummary->total_forecast_qty, 0, ',', '.') }} Unit</strong></span>
                        <span class="text-info font-monospace">Annual Consolidated</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $countIncrease = $itemPriceVariances->where('is_increase', true)->count();
        $countDecrease = $itemPriceVariances->where('is_decrease', true)->count();
    @endphp

    {{-- ACTION BUTTON FILTER POP-UP MODAL UNTUK KENAIKAN & PENURUNAN ITEM CODE --}}
    <div class="glass-card mb-4 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background:rgba(18,24,38,0.85); border:1px solid rgba(245,158,11,0.3);">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-funnel-fill text-warning fs-5"></i>
            <div>
                <h6 class="fw-bold text-white mb-0" style="font-size:0.92rem;">Filter Variansi Harga &amp; Pop-Up Info Item Code</h6>
                <small class="text-muted">Analisis detil item code mana yang mengalami kenaikan / penurunan harga aktual incoming vs forecast</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-2" onclick="openItemVarianceModal('increase')">
                <i class="bi bi-arrow-up-right-circle-fill text-danger"></i>
                Item Kenaikan Harga <span class="badge bg-danger text-white rounded-pill ms-1">{{ $countIncrease }} Item</span>
            </button>
            
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-2" onclick="openItemVarianceModal('decrease')">
                <i class="bi bi-arrow-down-right-circle-fill text-success"></i>
                Item Penurunan Harga <span class="badge bg-success text-white rounded-pill ms-1">{{ $countDecrease }} Item</span>
            </button>

            <button type="button" class="btn btn-sm btn-info rounded-pill px-3 py-1.5 fw-bold text-dark d-flex align-items-center gap-2" onclick="openItemVarianceModal('all')">
                <i class="bi bi-list-check"></i>
                Semua Item Code Variansi
            </button>
        </div>
    </div>

    {{-- ── 2. DIAGRAM GARIS/AREA & TABEL KOMPARASI KONVERSI RUPIAH ── --}}
    <div class="glass-card mb-4 p-4" style="border:1px solid rgba(0,210,255,0.3); background: linear-gradient(135deg, rgba(18,24,38,0.92) 0%, rgba(10,14,26,0.95) 100%);">
        
        {{-- Header Card --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-3 border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:46px;height:46px;background:rgba(0,210,255,0.18);border:1px solid rgba(0,210,255,0.45);color:#00d2ff;">
                    <i class="bi bi-graph-up-arrow fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-white mb-0 brand-font" style="font-size:1.05rem;">
                        <i class="bi bi-scale-balanced text-info me-2"></i>Komparasi Finansial: Forecast (Kurs Budget) vs Incoming PO (Kurs Mingguan)
                    </h5>
                    <p class="text-muted mb-0" style="font-size:0.78rem;">
                        Output default adalah <strong>Dollar (USD)</strong>: forecast IDR dikonversi dengan <strong>Kurs Budget Bulanan</strong>, sedangkan incoming PO IDR dikonversi dengan <strong>Kurs Incoming Mingguan</strong> sesuai tanggal transaksi.
                    </p>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3 flex-wrap fx-toolbar">
                {{-- Group 1: Focus Dimension (Amount vs Price) --}}
                <div class="d-flex align-items-center gap-1.5">
                    <span class="text-muted small fw-bold text-uppercase d-none d-md-inline" style="font-size:0.72rem; letter-spacing:0.04em;">Metrik:</span>
                    <div class="segmented-control" id="fxMetricFocusGroup">
                        <button type="button" class="segmented-btn active" id="btnFxFocusAmount" onclick="setFxMetricFocus('amount')" title="Fokus Evaluasi Nilai Finansial / Total Belanja Pengadaan Material">
                            <i class="bi bi-wallet2"></i> <strong>Amount</strong> (Belanja)
                        </button>
                        <button type="button" class="segmented-btn" id="btnFxFocusPrice" onclick="setFxMetricFocus('price')" title="Fokus Evaluasi Harga Satuan per Material / Piece">
                            <i class="bi bi-tag-fill"></i> <strong>Price</strong> (Harga Satuan)
                        </button>
                    </div>
                </div>

                {{-- Group 2: Aggregation (SUM vs AVG) --}}
                <div class="d-flex align-items-center gap-1.5">
                    <span class="text-muted small fw-bold text-uppercase d-none d-md-inline" style="font-size:0.72rem; letter-spacing:0.04em;">Agregasi:</span>
                    <div class="segmented-control" id="fxAggregationGroup">
                        <button type="button" class="segmented-btn active" id="btnFxAggSum" onclick="setFxAggregation('sum')" title="Agregasi Penjumlahan Total (SUM)">
                            <i class="bi bi-plus-slash-minus"></i> SUM
                        </button>
                        <button type="button" class="segmented-btn" id="btnFxAggAvg" onclick="setFxAggregation('avg')" title="Agregasi Rata-rata / Weighted Average (AVG)">
                            <i class="bi bi-calculator"></i> AVG
                        </button>
                    </div>
                </div>

                {{-- Group 3: Currency (USD vs IDR) --}}
                <div class="d-flex align-items-center gap-1.5">
                    <span class="text-muted small fw-bold text-uppercase d-none d-md-inline" style="font-size:0.72rem; letter-spacing:0.04em;">Mata Uang:</span>
                    <div class="segmented-control" id="fxCurrencyGroup">
                        <button type="button" class="segmented-btn active" id="btnFxCurrUsd" onclick="setFxCurrencyMode('usd')" title="Mata Uang Dollar Amerika (USD)">
                            <i class="bi bi-currency-dollar"></i> USD ($)
                        </button>
                        <button type="button" class="segmented-btn" id="btnFxCurrIdr" onclick="setFxCurrencyMode('idr')" title="Mata Uang Rupiah Indonesia (IDR)">
                            <i class="bi bi-cash-stack"></i> IDR (Rp)
                        </button>
                    </div>
                </div>

                <a href="{{ route('exchange-rate.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5 ms-auto" style="font-size:0.8rem;">
                    <i class="bi bi-sliders"></i> Master Kurs
                </a>
            </div>
        </div>

        {{-- DYNAMIC CHART MODE INFO BANNER (EXPLAINS ACTIVE PERSPECTIVE) --}}
        <div id="fxChartModeInfoBanner" class="p-2.5 px-3 rounded-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:rgba(0,210,255,0.06); border:1px solid rgba(0,210,255,0.25); font-size:0.82rem;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill text-info fs-5" id="fxModeInfoIcon"></i>
                <span class="text-light" id="fxModeInfoText">
                    <strong>Mode Aktif: SUM (Total Belanja - USD)</strong> — Menampilkan <em>Summary Total Biaya Pengadaan</em> ($25.2k di Juli, $18.2k di Agustus).
                </span>
            </div>
            <span class="text-muted" style="font-size:0.77rem;" id="fxModeInfoSub">
                Ganti ke <strong>AVG (Harga Satuan)</strong> untuk mengevaluasi harga per piece material piano.
            </span>
        </div>

        {{-- TABLE DISPLAY CURRENCY SWITCHER BUTTONS --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 p-2 px-3 rounded-3" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-table text-info"></i>
                <span class="text-white small fw-bold">Tampilan Kolom Tabel:</span>
                <span class="text-muted small d-none d-sm-inline">(Pilih satuan nilai yang ingin difokuskan pada tabel di bawah)</span>
            </div>
            <div class="btn-group btn-group-sm" role="group" aria-label="Table Currency Mode">
                <button type="button" class="btn btn-outline-info active btn-tbl-currency" id="btnTblMode_usd" onclick="setTableCurrencyDisplay('usd')">
                    <i class="bi bi-currency-dollar me-1"></i> Dollar Only ($)
                </button>
                <button type="button" class="btn btn-outline-info btn-tbl-currency" id="btnTblMode_idr" onclick="setTableCurrencyDisplay('idr')">
                    <i class="bi bi-flag-fill me-1"></i> Rupiah Only (Rp)
                </button>
            </div>
        </div>

        {{-- DIAGRAM GARIS / AREA CHART --}}
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap fx-chart-meta">
                <span class="text-muted small fw-bold uppercase" style="letter-spacing:0.05em;" id="fxChartTitleLabel">
                    <i class="bi bi-currency-dollar text-success me-1"></i> DIAGRAM AREA KOMPARASI TOTAL AMOUNT (DOLLAR / USD)
                </span>
                <div class="d-flex align-items-center gap-3" style="font-size:0.78rem;">
                    <span class="d-inline-flex align-items-center gap-1">
                        <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#00d2ff;"></span>
                        <span class="text-info fw-semibold">Forecast</span>
                        <span class="text-muted" id="fxBudgetRateLegend">(Kurs Budget)</span>
                    </span>
                    <span class="d-inline-flex align-items-center gap-1">
                        <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#10b981;"></span>
                        <span class="text-success fw-semibold">Realisasi PO</span>
                        <span class="text-muted" id="fxActualRateLegend">(Kurs Mingguan)</span>
                    </span>
                    <span class="text-warning" title="Nilai kurs per bulan juga ditampilkan pada tooltip setiap titik grafik.">
                        <i class="bi bi-info-circle me-1"></i>Legend kurs tersedia per titik
                    </span>
                    <span class="badge bg-dark bg-opacity-75 text-info border border-info border-opacity-30 px-2.5 py-1 rounded-pill" style="font-size:0.75rem;" title="Nominal pengadaan mencerminkan jadwal pemesanan batch material utama (seperti PE Foam laminasi rolls) yang dipesan secara 2-bulanan (Juli & Sept) untuk efisiensi kontainer">
                        <i class="bi bi-diagram-3-fill text-info me-1"></i>Siklus Pemesanan 2-Bulanan (Batching)
                    </span>
                </div>
            </div>

            <div style="position:relative; height:360px; width:100%;">
                <canvas id="chartExchangeRateComparisonArea"></canvas>
            </div>

            <!-- INTERACTIVE MONTH-BY-MONTH INSIGHT QUICK PILLS -->
            <div class="p-3 mt-3 rounded-3" style="background: rgba(18, 24, 38, 0.85); border: 1px solid rgba(0, 210, 255, 0.2);">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <span class="text-white small fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-lightbulb-fill text-warning"></i>
                        <span>Interactive Financial Insight per Bulan:</span>
                        <span class="text-muted fw-normal d-none d-md-inline" style="font-size:0.78rem;">(Klik titik grafik atau tombol bulan di bawah untuk melihat analisis penyebab naik/turun &amp; material contributor)</span>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-1.5 flex-wrap" id="cmiPillsContainer">
                    @foreach($comparisonMonthlyInsights as $cmi)
                        <button type="button" class="btn btn-sm btn-outline-dark text-white rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 cmi-pill-btn"
                                id="cmiPillBtn_{{ $cmi->month_index }}"
                                style="font-size:0.75rem; background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.12);"
                                onclick="openMonthlyInsightModal({{ $cmi->month_index }})"
                                title="Klik untuk melihat insight lengkap {{ $cmi->month_name }}">
                            <span class="fw-bold font-monospace">{{ $cmi->short_label }}</span>
                            <span class="badge {{ $cmi->badge_color ? 'bg-'.$cmi->badge_color.' bg-opacity-25 text-'.$cmi->badge_color : 'bg-secondary bg-opacity-25 text-muted' }}" 
                                  id="cmiPillBadge_{{ $cmi->month_index }}" 
                                  style="font-size:0.65rem;">
                                @if($cmi->is_first_month)
                                    Base
                                @elseif($cmi->data_status === 'FORECAST_ONLY')
                                    <i class="bi bi-exclamation-triangle"></i> {{ $cmi->mom_fc_amount_pct >= 0 ? '+' : '' }}{{ $cmi->mom_fc_amount_pct }}%
                                @elseif($cmi->mom_fc_amount_pct > 0)
                                    <i class="bi bi-arrow-up-short"></i>+{{ $cmi->mom_fc_amount_pct }}%
                                @elseif($cmi->mom_fc_amount_pct < 0)
                                    <i class="bi bi-arrow-down-short"></i>{{ $cmi->mom_fc_amount_pct }}%
                                @else
                                    0%
                                @endif
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- TABEL DETIL KONVERSI KURS (DOLLAR & RUPIAH) --}}
        <div class="table-container border-0 rounded-3">
            <table class="table-custom currency-mode-usd" id="tableFxComparison">
                <thead>
                    <tr>
                        <th style="text-align:left; padding-left:1.5rem;">Periode Bulan</th>
                        <th>Status Data</th>
                        <th>Kurs Budget (IDR)</th>
                        <th>Kurs Realisasi (IDR Avg)</th>
                        <th>Forecast Price ($ / Rp)</th>
                        <th>Forecast Amount ($ / Rp)</th>
                        <th>Realisasi Price ($ / Rp)</th>
                        <th>Realisasi Amount ($ / Rp)</th>
                        <th>Selisih Amount ($ &amp; Rp)</th>
                        <th>Evaluasi Dampak Kurs</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exchangeRateComparisonGrid as $erc)
                    <tr>
                        <td class="fw-bold text-white" style="text-align:left; padding-left:1.5rem;">
                            <span class="px-2.5 py-1 rounded-pill me-2" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); font-size:0.75rem;">
                                {{ sprintf('%02d', $erc->month) }}
                            </span>
                            {{ $erc->month_name }}
                        </td>
                        <td>
                            @if($erc->data_status === 'COMPLETE')
                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-0.5 rounded-pill" style="font-size:0.72rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Complete
                                </span>
                            @elseif($erc->data_status === 'FORECAST_ONLY')
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-2 py-0.5 rounded-pill" style="font-size:0.72rem;" title="Data Forecast tersedia, incoming fisik belum tercatat">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Forecast Only
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-25 text-muted border border-secondary px-2 py-0.5 rounded-pill" style="font-size:0.72rem;">
                                    <i class="bi bi-dash-circle me-1"></i>No Data
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold font-monospace" style="color:#00d2ff; font-size:0.88rem;">
                                Rp {{ $erc->budget_rate_fmt }}
                            </span>
                        </td>
                        <td>
                            @if($erc->actual_avg_rate && $erc->actual_avg_rate > 0)
                                <span class="fw-semibold font-monospace text-warning" style="font-size:0.88rem;">
                                    {{ $erc->actual_avg_fmt }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size:0.88rem;">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="val-usd fw-bold text-white font-monospace" style="font-size:0.88rem;">$ {{ $erc->forecast_price_usd_fmt }}</div>
                            <div class="val-idr text-muted font-monospace" style="font-size:0.76rem;">Rp {{ $erc->forecast_price_idr_fmt }}</div>
                        </td>
                        <td>
                            <div class="val-usd fw-bold font-monospace text-info" style="font-size:0.9rem;">$ {{ $erc->forecast_amount_usd_fmt }}</div>
                            <div class="val-idr text-muted font-monospace" style="font-size:0.76rem;">Rp {{ $erc->forecast_amount_idr_fmt }}</div>
                        </td>
                        <td>
                            @if($erc->incoming_amount_usd !== null)
                                <div class="val-usd fw-bold text-white font-monospace" style="font-size:0.88rem;">$ {{ $erc->incoming_price_usd_fmt }}</div>
                                <div class="val-idr text-muted font-monospace" style="font-size:0.76rem;">Rp {{ $erc->incoming_price_idr_fmt }}</div>
                            @else
                                <span class="text-muted font-monospace" style="font-size:0.88rem;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($erc->incoming_amount_usd !== null)
                                <div class="val-usd fw-bold font-monospace text-emerald" style="color:#34d399; font-size:0.9rem;">$ {{ $erc->incoming_amount_usd_fmt }}</div>
                                <div class="val-idr text-muted font-monospace" style="font-size:0.76rem;">Rp {{ $erc->incoming_amount_idr_fmt }}</div>
                            @else
                                <span class="text-muted font-monospace" style="font-size:0.88rem;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($erc->incoming_amount_usd !== null && $erc->forecast_amount_usd > 0)
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="{{ $erc->is_favorable ? 'background:rgba(52,211,153,0.18);color:#34d399;border:1px solid rgba(52,211,153,0.4);' : 'background:rgba(248,113,113,0.18);color:#f87171;border:1px solid rgba(248,113,113,0.4);' }}">
                                    <i class="bi {{ $erc->is_favorable ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }} me-1"></i>
                                    <span class="val-usd">$ {{ $erc->variance_amt_usd_fmt }}</span>
                                    <span class="val-idr">Rp {{ $erc->variance_amt_idr_fmt }}</span>
                                    ({{ $erc->variance_amt_pct }}%)
                                </span>
                            @else
                                <span class="text-muted" style="font-size:0.82rem;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($erc->incoming_amount_usd !== null && $erc->forecast_amount_usd > 0)
                                @if($erc->is_favorable)
                                    <span class="text-success small fw-semibold"><i class="bi bi-shield-check me-1"></i>Efisiensi Biaya (Favorable)</span>
                                @else
                                    <span class="text-danger small fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>Peningkatan Biaya (Unfavorable)</span>
                                @endif
                            @elseif($erc->data_status === 'FORECAST_ONLY')
                                <span class="text-warning small"><i class="bi bi-hourglass-split me-1"></i>Planned (Future)</span>
                            @else
                                <span class="text-muted small">— N/A —</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold" style="background:rgba(18,24,38,0.95); border-top:2px solid rgba(0,210,255,0.3);">
                        <td class="text-white text-uppercase" style="text-align:left; padding-left:1.5rem;">
                            <i class="bi bi-calculator-fill text-warning me-1"></i> Total (12 Bulan)
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-25 text-info border border-info px-2 py-0.5 rounded-pill" style="font-size:0.72rem;">
                                {{ $slide1ExecutiveSummary->validated_months_count }}/{{ $slide1ExecutiveSummary->total_months_count }} Validated
                            </span>
                        </td>
                        <td class="text-muted">—</td>
                        <td class="text-muted">—</td>
                        <td>
                            <div class="val-usd text-muted font-monospace" style="font-size:0.82rem;">$ {{ number_format($slide1ExecutiveSummary->avg_forecast_price_usd, 2, '.', ',') }}</div>
                            <div class="val-idr text-muted font-monospace" style="font-size:0.72rem;">Rp {{ number_format($slide1ExecutiveSummary->avg_forecast_price_idr, 0, ',', '.') }}</div>
                        </td>
                        <td>
                            <div class="val-usd fw-bold font-monospace text-info" style="font-size:0.92rem;">$ {{ number_format($slide1ExecutiveSummary->total_forecast_amount_usd, 2, '.', ',') }}</div>
                            <div class="val-idr text-muted font-monospace" style="font-size:0.78rem;">Rp {{ number_format($slide1ExecutiveSummary->total_forecast_amount_idr, 0, ',', '.') }}</div>
                        </td>
                        <td>
                            <div class="val-usd text-muted font-monospace" style="font-size:0.82rem;">$ {{ number_format($slide1ExecutiveSummary->avg_incoming_price_usd, 2, '.', ',') }}</div>
                            <div class="val-idr text-muted font-monospace" style="font-size:0.72rem;">Rp {{ number_format($slide1ExecutiveSummary->avg_incoming_price_idr, 0, ',', '.') }}</div>
                        </td>
                        <td>
                            <div class="val-usd fw-bold font-monospace text-emerald" style="color:#34d399; font-size:0.92rem;">$ {{ number_format($slide1ExecutiveSummary->total_incoming_amount_usd, 2, '.', ',') }}</div>
                            <div class="val-idr text-muted font-monospace" style="font-size:0.78rem;">Rp {{ number_format($slide1ExecutiveSummary->total_incoming_amount_idr, 0, ',', '.') }}</div>
                        </td>
                        <td>
                            <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="{{ $slide1ExecutiveSummary->variance_amount_usd <= 0 ? 'background:rgba(52,211,153,0.2);color:#34d399;border:1px solid rgba(52,211,153,0.5);' : 'background:rgba(248,113,113,0.2);color:#f87171;border:1px solid rgba(248,113,113,0.5);' }}">
                                <span class="val-usd">{{ $slide1ExecutiveSummary->variance_amount_usd > 0 ? '+' : '' }}$ {{ number_format($slide1ExecutiveSummary->variance_amount_usd, 2, '.', ',') }}</span>
                                <span class="val-idr">{{ $slide1ExecutiveSummary->variance_amount_idr > 0 ? '+' : '' }}Rp {{ number_format($slide1ExecutiveSummary->variance_amount_idr, 0, ',', '.') }}</span>
                                ({{ $slide1ExecutiveSummary->variance_pct }}%)
                            </span>
                        </td>
                        <td>
                            @if($slide1ExecutiveSummary->variance_amount_usd <= 0)
                                <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Validated Favorable</span>
                            @else
                                <span class="text-danger small fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Validated Unfavorable</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if(($selectedItemCode ?? 'ALL') === 'ALL')
        <!-- SUMMARY CATALOG GRID (WHEN ALL IS SELECTED) -->
        <div class="glass-card mb-4">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-grid-3x3-gap-fill text-info me-2"></i>Katalog Item Forecast & Aktual</h5>
            <div class="table-container">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Item Code</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Supplier / Vendor</th>
                            <th>User (PIC)</th>
                            <th>Plan Stock (Awal)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visibleDisplayGridS1 as $idx => $row)
                            <tr id="item-row-{{ $row->item_code }}" class="item-grid-row">
                                <td>{{ $visibleDisplayGridS1->firstItem() + $idx }}</td>
                                <td><span class="badge bg-dark border border-secondary font-monospace">{{ $row->item_code }}</span></td>
                                <td>
                                    <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50">
                                        {{ $row->category_code ? $row->category_code . ' - ' : '' }}{{ $row->category_name }}
                                    </span>
                                </td>
                                <td class="text-start">{{ $row->description }}</td>
                                <td>{{ $row->supplier }}</td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-info">
                                        <i class="bi bi-person-fill me-1"></i>{{ $row->pic_buyer }}
                                    </span>
                                </td>
                                <td class="fw-bold text-warning">{{ number_format($row->forecast_grid[0]->stock) }}</td>
                                <td>
                                    <a href="{{ route('purchasing.analysis', array_merge(request()->except(['page', 's1_item_code']), ['s1_item_code' => $row->item_code, 'active_slide' => 'slide1'])) }}" class="btn btn-xs btn-outline-info rounded-pill px-3 py-1 text-decoration-none">
                                        <i class="bi bi-eye"></i> Detail Grid
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted text-center py-4">Tidak ada data item code yang sesuai dengan filter Slide 1.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($visibleDisplayGridS1->hasPages())
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top border-secondary border-opacity-25">
                    <span class="text-muted small">Menampilkan {{ $visibleDisplayGridS1->firstItem() }}–{{ $visibleDisplayGridS1->lastItem() }} dari {{ number_format($visibleDisplayGridS1->total()) }} item.</span>
                    <div class="pagination-container">
                        {{ $visibleDisplayGridS1->onEachSide(1)->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    @else
        <!-- DETAILED ROLL-FORWARD GRIDS (WHEN SPECIFIC ITEM CODE IS SELECTED) -->
        @foreach($visibleDisplayGridS1 as $item)
            <div class="glass-card mb-4" id="item-detail-{{ $item->item_code }}">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                    <div>
                        <h4 class="fw-bold text-white mb-0 brand-font">Item: {{ $item->item_code }}</h4>
                        <p class="text-muted small mb-0">{{ $item->description }} | Supplier: {{ $item->supplier }} | Kategori: {{ $item->category_name }}</p>
                    </div>
                    <a href="{{ route('purchasing.analysis', array_merge(request()->except(['s1_item_code']), ['s1_item_code' => 'ALL', 'active_slide' => 'slide1'])) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Semua Item
                    </a>
                </div>

                <div class="table-container mb-3">
                    <table class="table-custom table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" style="min-width: 140px;">Tipe Data &amp; Komparasi</th>
                                <th rowspan="2" style="background: rgba(18, 24, 38, 0.98) !important; color: #f87171;">Pre Outstand ({{ $startMonth }})</th>
                                <th rowspan="2" style="background: rgba(18, 24, 38, 0.98) !important;">Plan Stock ({{ $startMonth }})</th>
                                <th rowspan="2" style="background: rgba(18, 24, 38, 0.98) !important;">Pre Ratio</th>
                                @for($i = 1; $i <= $s1_duration; $i++)
                                    <th colspan="9" style="background: rgba(18, 24, 38, 0.95) !important; text-align: center;">{{ $months[$i] }}</th>
                                @endfor
                            </tr>
                            <tr>
                                @for($i = 1; $i <= $s1_duration; $i++)
                                    <th style="color:#60a5fa;" title="Target Order PO">PO</th>
                                    <th style="color:#c084fc;" title="Rencana Kebutuhan Material (Forecast Demand)">FORECAST</th>
                                    <th style="color:#34d399;" title="Realisasi Penerimaan PO (Incoming)">INCOMING</th>
                                    <th style="color:#f87171;" title="Sisa Pasokan (Outstanding)">OUTSTAND</th>
                                    <th style="color:#fbbf24;" title="Realisasi Output Produksi">PROD</th>
                                    <th style="color:#38bdf8;" title="Ending Inventory (Stok Akhir)">ENDING INV</th>
                                    <th style="color:#a78bfa;" title="Ketercapaian Realisasi vs Rencana (Achievement %)">ACHIEVEMENT</th>
                                    <th style="color:#fbbf24;" title="Harga Satuan ($)">PRICE</th>
                                    <th style="color:#34d399;" title="Valuasi Finansial ($)">AMOUNT</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 1. FORECAST ROW (TARGET RENCANA) -->
                            <tr style="background: rgba(14, 165, 233, 0.04);">
                                <td class="fw-bold text-info text-start text-nowrap">
                                    <i class="bi bi-graph-up me-1 text-info"></i>Forecast (Target)
                                </td>
                                <td class="font-monospace fw-bold bg-dark bg-opacity-25 {{ ($item->forecast_grid[0]->outstanding ?? 0) < 0 ? 'text-danger' : 'text-light' }}">{{ number_format($item->forecast_grid[0]->outstanding ?? 0) }}</td>
                                <td class="fw-bold text-warning bg-dark bg-opacity-25">{{ number_format($item->forecast_grid[0]->stock ?? 0) }}</td>
                                <td class="bg-dark bg-opacity-25">
                                    <span class="badge {{ localGetRatioClass($item->forecast_grid[0]->ratio ?? '-') }}">{{ localFormatRatioDisplay($item->forecast_grid[0]->ratio ?? '-') }}</span>
                                </td>
                                @for($i = 1; $i <= $s1_duration; $i++)
                                    @php
                                        $fg = $item->forecast_grid[$i] ?? null;
                                        $fPo  = $fg->po ?? 0;
                                        $fFc  = $fg->forecast ?? 0;
                                        $fDel = $fg->delivery ?? 0;
                                        $fOut = $fg->outstanding ?? 0;
                                        $fPrd = $fg->prod ?? 0;
                                        $fStk = $fg->stock ?? 0;
                                        $fCov = $fg->coverage_ratio ?? ($fg->ratio ?? '-');
                                        $fPrc = $fg->price_usd ?? ($fg->price ?? 0);
                                        $fAmt = $fg->forecast_amount_usd ?? ($fFc * $fPrc);
                                    @endphp
                                    <td class="font-monospace">{{ number_format($fPo) }}</td>
                                    <td class="font-monospace text-primary fw-bold">{{ number_format($fFc) }}</td>
                                    <td class="font-monospace text-muted">{{ number_format($fDel) }}</td>
                                    <td class="font-monospace {{ $fOut < 0 ? 'text-danger fw-bold' : 'text-light' }}">{{ number_format($fOut) }}</td>
                                    <td class="font-monospace text-warning">{{ number_format($fPrd) }}</td>
                                    <td class="fw-bold text-info bg-info bg-opacity-10">{{ number_format($fStk) }}</td>
                                    <td>
                                        <span class="badge {{ localGetRatioClass($fCov) }}" title="Stock Coverage Ratio">{{ localFormatRatioDisplay($fCov) }}</span>
                                    </td>
                                    <td class="font-monospace text-warning fw-bold text-nowrap" style="white-space:nowrap;">$ {{ number_format($fPrc, 2, ',', '.') }}</td>
                                    <td class="font-monospace fw-bold text-nowrap text-info" style="white-space:nowrap;" title="Forecast Demand Amount = {{ number_format($fFc) }} × ${{ number_format($fPrc, 2) }}">{{ formatAmountCustom($fAmt) }}</td>
                                @endfor
                            </tr>

                            <!-- 2. ACTUAL ROW (REALISASI & AKTUAL ENDING INVENTORY) -->
                            <tr style="background: rgba(16, 185, 129, 0.04);">
                                <td class="fw-bold text-success text-start text-nowrap">
                                    <i class="bi bi-check-circle-fill me-1 text-success"></i>Actual (Realisasi)
                                </td>
                                <td class="font-monospace fw-bold bg-dark bg-opacity-25 {{ ($item->actual_grid[0]->outstanding ?? 0) < 0 ? 'text-danger' : 'text-light' }}">{{ number_format($item->actual_grid[0]->outstanding ?? 0) }}</td>
                                <td class="fw-bold text-warning bg-dark bg-opacity-25">{{ number_format($item->actual_grid[0]->stock ?? 0) }}</td>
                                <td class="bg-dark bg-opacity-25">
                                    <span class="badge {{ localGetRatioClass($item->actual_grid[0]->ratio ?? '-') }}">{{ localFormatRatioDisplay($item->actual_grid[0]->ratio ?? '-') }}</span>
                                </td>
                                @for($i = 1; $i <= $s1_duration; $i++)
                                    @php
                                        $ag = $item->actual_grid[$i] ?? null;
                                        $aPo  = $ag->po ?? 0;
                                        $aFc  = $ag->forecast ?? 0;
                                        $aDel = $ag->delivery ?? 0;
                                        $aOut = $ag->outstanding ?? 0;
                                        $aPrd = $ag->prod ?? 0;
                                        $aStk = $ag->stock ?? 0;
                                        $aAch = $ag->achievement_pct ?? ($ag->ratio ?? '-');
                                        $aPrc = $ag->price_usd ?? ($ag->price ?? 0);
                                        $aInvAmt = $ag->inventory_amount_usd ?? ($aStk * $aPrc);
                                        $aIncAmt = $ag->incoming_amount_usd ?? ($aDel * $aPrc);
                                        $fPrc = $item->forecast_grid[$i]->price_usd ?? $aPrc;
                                    @endphp
                                    <td class="text-info fw-bold font-monospace">{{ number_format($aPo) }}</td>
                                    <td class="text-primary fw-bold font-monospace">{{ number_format($aFc) }}</td>
                                    <td class="text-success fw-bold font-monospace">
                                        @if($aDel > 0 && !empty($ag->delivery_details))
                                            <button type="button" 
                                                    class="btn btn-link p-0 text-success fw-bold font-monospace text-decoration-underline" 
                                                    onclick="openDeliveryDetailModal('{{ $months[$i] }}', '{{ $item->item_code }}', '{{ addslashes($item->description) }}', {{ json_encode($ag->delivery_details) }})" 
                                                    title="Klik untuk melihat rincian transaksi penerimaan PO bulan {{ $months[$i] }}">
                                                {{ number_format($aDel) }}
                                                <i class="bi bi-info-circle-fill text-info ms-0.5" style="font-size:0.7rem;"></i>
                                            </button>
                                        @else
                                            {{ number_format($aDel) }}
                                        @endif
                                    </td>
                                    <td class="font-monospace {{ $aOut > 0 ? 'text-danger fw-bold' : 'text-success' }}">{{ number_format($aOut) }}</td>
                                    <td class="font-monospace text-warning">{{ number_format($aPrd) }}</td>
                                    <td class="fw-bold text-success bg-success bg-opacity-10 font-monospace">{{ number_format($aStk) }}</td>
                                    <td>
                                        <span class="{{ localGetAchievementClass($aAch) }}" title="Achievement = {{ number_format($aDel) }} Incoming ÷ {{ number_format($aFc) }} Demand">{{ localFormatRatioDisplay($aAch) }}</span>
                                    </td>
                                    <td class="font-monospace text-warning fw-bold text-nowrap" style="white-space:nowrap;">
                                        $ {{ number_format($aPrc, 2, ',', '.') }}
                                        @php
                                            $b64Details    = base64_encode(json_encode($ag->delivery_details ?? []));
                                            $b64AllDetails = base64_encode(json_encode($item->all_delivery_details ?? []));
                                        @endphp
                                        @if($aPrc != $fPrc)
                                            <button type="button" class="btn btn-xs p-0 border-0 ms-1" data-details="{{ $b64Details }}" data-all-details="{{ $b64AllDetails }}" onclick="showPriceReasonModal(this, '{{ $item->item_code }}', '{{ $item->id }}', '{{ $aPrc }}', '{{ $fPrc }}', '{{ addslashes($item->price_deviation_reason ?? '') }}')" title="Penyimpangan Harga! Klik untuk lihat rincian per PO & tambah catatan">
                                                <i class="bi bi-exclamation-triangle-fill text-warning fs-7"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-xs p-0 border-0 ms-1" data-details="{{ $b64Details }}" data-all-details="{{ $b64AllDetails }}" onclick="showPriceReasonModal(this, '{{ $item->item_code }}', '{{ $item->id }}', '{{ $aPrc }}', '{{ $fPrc }}', '{{ addslashes($item->price_deviation_reason ?? '') }}')" title="Klik untuk lihat rincian per PO & tambah catatan">
                                                <i class="bi bi-chat-left-text text-muted fs-7"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td class="font-monospace fw-bold text-nowrap" style="color:#34d399; white-space:nowrap;" title="Actual Ending Inventory Amount = {{ number_format($aStk) }} PCS × ${{ number_format($aPrc, 2) }}">
                                        {{ formatAmountCustom($aInvAmt) }}
                                        <small class="text-muted d-block" style="font-size:0.68rem;" title="Incoming Amount = {{ number_format($aDel) }} × ${{ number_format($aPrc, 2) }}">
                                            Inc: {{ formatAmountCustom($aIncAmt) }}
                                        </small>
                                    </td>
                                @endfor
                            </tr>

                            <!-- 3. VARIANCE ROW (SELISIH & STATUS MONITORING) -->
                            <tr style="background: rgba(255, 255, 255, 0.02); border-top: 1px dashed rgba(255, 255, 255, 0.15);">
                                <td class="fw-semibold text-muted text-start text-nowrap">
                                    <i class="bi bi-arrow-left-right me-1 text-warning"></i>Selisih (Variance)
                                </td>
                                <td class="font-monospace text-muted bg-dark bg-opacity-25">0</td>
                                <td class="font-monospace text-muted bg-dark bg-opacity-25">0</td>
                                <td class="bg-dark bg-opacity-25 text-muted">-</td>
                                @for($i = 1; $i <= $s1_duration; $i++)
                                    @php
                                        $fg = $item->forecast_grid[$i] ?? null;
                                        $ag = $item->actual_grid[$i] ?? null;
                                        $fFc = $fg->forecast ?? 0;
                                        $aDel = $ag->delivery ?? 0;
                                        $fStk = $fg->stock ?? 0;
                                        $aStk = $ag->stock ?? 0;
                                        $fAmt = $fg->forecast_amount_usd ?? ($fFc * ($fg->price_usd ?? 0));
                                        $aInvAmt = $ag->inventory_amount_usd ?? ($aStk * ($ag->price_usd ?? 0));
                                        $diffDemand = $aDel - $fFc;
                                        $diffStock  = $aStk - $fStk;
                                        $diffAmt    = $aInvAmt - $fAmt;
                                        $varPct     = $ag->variance_pct ?? 0;
                                        $statusMon  = $ag->status ?? 'Sesuai';
                                    @endphp
                                    <td class="font-monospace text-muted">{{ number_format(($ag->po ?? 0) - ($fg->po ?? 0)) }}</td>
                                    <td class="font-monospace fw-bold {{ $diffDemand >= 0 ? 'text-success' : 'text-danger' }}" title="Selisih Incoming vs Target Demand">
                                        {{ $diffDemand >= 0 ? '+' : '' }}{{ number_format($diffDemand) }}
                                    </td>
                                    <td class="font-monospace text-muted">{{ number_format($aDel - ($fg->delivery ?? 0)) }}</td>
                                    <td class="font-monospace text-muted">{{ number_format(($ag->outstanding ?? 0) - ($fg->outstanding ?? 0)) }}</td>
                                    <td class="font-monospace text-muted">{{ number_format(($ag->prod ?? 0) - ($fg->prod ?? 0)) }}</td>
                                    <td class="font-monospace fw-bold {{ $diffStock >= 0 ? 'text-success' : 'text-danger' }}" title="Selisih Stok Fisik vs Rencana Stok">
                                        {{ $diffStock >= 0 ? '+' : '' }}{{ number_format($diffStock) }}
                                    </td>
                                    <td>
                                        <span class="{{ localGetStatusBadgeClass($statusMon) }}">{{ $statusMon }}</span>
                                    </td>
                                    <td class="font-monospace text-muted" style="font-size:0.75rem;">
                                        @php
                                            $diffPrc = ($ag->price_usd ?? 0) - ($fg->price_usd ?? 0);
                                        @endphp
                                        {{ $diffPrc != 0 ? (($diffPrc > 0 ? '+' : '') . '$ ' . number_format($diffPrc, 2)) : '$ 0.00' }}
                                    </td>
                                     <td class="font-monospace fw-bold text-nowrap {{ $diffAmt >= 0 ? 'text-success' : 'text-danger' }}" style="white-space:nowrap;" title="Selisih Nilai Finansial Inventory vs Demand Target">
                                        {{ $diffAmt >= 0 ? '+' : '' }}{{ formatAmountCustom($diffAmt) }}
                                        <small class="d-block {{ $diffAmt >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:0.68rem;">
                                            {{ $varPct >= 0 ? '+' : '' }}{{ $varPct }}%
                                        </small>
                                    </td>
                                @endfor
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif

        </div> {{-- END SLIDE 1 TAB PANE --}}

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- SLIDE 2: INFOGRAFIS TREN & KOMPARASI OUTSTANDING                 --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade {{ ($activeSlide ?? 'slide1') === 'slide2' ? 'show active' : '' }}" id="slide2-content" role="tabpanel" aria-labelledby="tab-slide2-btn">

            <!-- 🎛️ INDEPENDENT FILTER PANEL: SLIDE 2 -->
            <div class="glass-card mb-4 p-3 border border-warning border-opacity-30" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.85) 100%);">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-funnel-fill text-warning fs-5"></i>
                        <h6 class="fw-bold text-white mb-0">Filter Khusus Slide 2 (Infografis &amp; Tren Vendor)</h6>
                        @if(($activeFilterCountS2 ?? 0) > 0)
                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold">{{ $activeFilterCountS2 }} Filter Aktif</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-25 text-muted rounded-pill px-2.5 py-1">Semua Data</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-warning rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilterSlide2" aria-expanded="{{ ($activeFilterCountS2 ?? 0) > 0 ? 'true' : 'false' }}">
                            <i class="bi bi-sliders me-1"></i> Buka / Tutup Filter <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                    </div>
                </div>

                @if(($activeFilterCountS2 ?? 0) > 0)
                    <div class="d-flex flex-wrap gap-1.5 align-items-center mt-2.5 pt-2 border-top border-secondary border-opacity-25">
                        <small class="text-muted me-1 fw-semibold">Filter Aktif:</small>
                        @foreach($activeFiltersListS2 as $af)
                            <span class="badge bg-dark border border-warning text-warning rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1">
                                {{ $af['label'] }}
                                <a href="{{ route('purchasing.analysis', array_merge(request()->query(), ['s2_'.$af['key'] => ($af['key'] === 'year' ? '2026' : ($af['key'] === 'duration' ? 8 : 'ALL')), 'active_slide' => 'slide2'])) }}" class="text-warning text-decoration-none ms-1" title="Hapus filter">&times;</a>
                            </span>
                        @endforeach
                        <a href="{{ route('purchasing.analysis', ['reset_slide' => 'slide2', 'active_slide' => 'slide2']) }}" class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill px-2.5 py-1.5 text-decoration-none ms-auto">
                            <i class="bi bi-x-circle me-1"></i> Reset Slide 2
                        </a>
                    </div>
                @endif

                <div class="collapse {{ ($activeFilterCountS2 ?? 0) > 0 ? 'show' : '' }}" id="collapseFilterSlide2">
                    <form method="GET" action="{{ route('purchasing.analysis') }}" class="pt-3 mt-2 border-top border-secondary border-opacity-25">
                        <input type="hidden" name="active_slide" value="slide2">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">Item Code</label>
                                <select name="s2_item_code" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s2_item_code ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua Item --</option>
                                    @foreach($availableItemCodes as $code)
                                        <option value="{{ $code }}" {{ ($s2_item_code ?? 'ALL') === $code ? 'selected' : '' }}>{{ $code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">Vendor</label>
                                <select name="s2_vendor" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s2_vendor ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua Vendor --</option>
                                    @foreach($availableVendors as $v)
                                        <option value="{{ $v }}" {{ ($s2_vendor ?? 'ALL') === $v ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">PIC / Buyer</label>
                                <select name="s2_pic" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s2_pic ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua PIC --</option>
                                    @foreach($availablePics as $p)
                                        <option value="{{ $p }}" {{ ($s2_pic ?? 'ALL') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">No. PO</label>
                                <select name="s2_po" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s2_po ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua PO --</option>
                                    @foreach($availablePoNumbers as $poNum)
                                        <option value="{{ $poNum }}" {{ ($s2_po ?? 'ALL') === $poNum ? 'selected' : '' }}>{{ $poNum }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                <label class="form-label text-muted small mb-1 fw-semibold">Pengantaran</label>
                                <select name="s2_delivery_category" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option value="ALL" {{ ($s2_delivery_category ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua --</option>
                                    @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                        <option value="{{ $dc->code }}" {{ ($s2_delivery_category ?? 'ALL') === $dc->code ? 'selected' : '' }}>{{ $dc->code }} - {{ $dc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-1">
                                <label class="form-label text-muted small mb-1 fw-semibold">Tahun</label>
                                <select name="s2_year" class="form-select form-select-sm bg-dark text-warning border-secondary fw-bold">
                                    <option value="ALL" {{ (string)($s2_year ?? '2026') === 'ALL' ? 'selected' : '' }}>All</option>
                                    @foreach(($availableYears ?? [2025, 2026, 2027, 2028]) as $yr)
                                        <option value="{{ $yr }}" {{ (string)($s2_year ?? '2026') === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 col-lg-1">
                                <label class="form-label text-muted small mb-1 fw-semibold">Durasi</label>
                                <select name="s2_duration" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    @for($d = 1; $d <= ($maxForecastPeriods ?? 8); $d++)
                                        <option value="{{ $d }}" {{ ($s2_duration ?? 8) == $d ? 'selected' : '' }}>{{ $d }} Bln</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-12 d-flex justify-content-end gap-2 mt-2">
                                <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold text-dark">
                                    <i class="bi bi-check2-circle me-1"></i> Terapkan Filter Slide 2
                                </button>
                                <a href="{{ route('purchasing.analysis', ['reset_slide' => 'slide2', 'active_slide' => 'slide2']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-x-circle me-1"></i> Reset Slide 2
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(empty($displayGridS2) || $displayGridS2->isEmpty())
                <div class="alert alert-warning text-center my-4 py-4 rounded-4 shadow-sm border border-warning border-opacity-50">
                    <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2 text-warning"></i>
                    <h6 class="fw-bold text-white">Tidak ada data yang sesuai dengan filter Slide 2</h6>
                    <p class="text-muted small mb-3">Silakan sesuaikan filter atau reset untuk melihat seluruh vendor dan pergerakan tren.</p>
                    <a href="{{ route('purchasing.analysis', ['reset_slide' => 'slide2', 'active_slide' => 'slide2']) }}" class="btn btn-sm btn-outline-warning rounded-pill px-4">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter Slide 2
                    </a>
                </div>
            @endif

    <!-- 📊 INFOGRAPHIC MONTHLY TREND ANALYTICS PANEL -->
    @if(isset($monthlyInsights) && count($monthlyInsights) > 0)
        <div class="glass-card mb-4 p-4 border border-info border-opacity-30 shadow-lg" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.9) 100%);">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-bottom border-secondary border-opacity-25 pb-3 mb-4">
                <div>
                    <h4 class="fw-bold text-white mb-1 brand-font d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-warning fs-4"></i>
                        <span>Infografis Analisis Tren Bulanan</span>
                    </h4>
                    <p class="text-muted small mb-0">Rincian pergerakan penerimaan PO & level stok (Item mana yang naik/turun per bulan). Klik badge item untuk meluncur langsung ke tabel Detail Grid.</p>
                </div>
                
                <!-- Minimalist Month Selector Segmented Pill Bar -->
                <div class="segmented-control style-scrollbar flex-nowrap" style="overflow-x: auto; max-width: 100%; padding: 3px;" id="infographicMonthTabs" role="tablist">
                    @foreach($monthlyInsights as $mName => $insight)
                        <button class="segmented-btn segmented-btn-xs {{ $loop->first ? 'active' : '' }}" 
                                id="tab-info-{{ $mName }}" 
                                data-bs-toggle="pill" 
                                data-bs-target="#content-info-{{ $mName }}" 
                                type="button" role="tab">
                            {{ $mName }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="tab-content" id="infographicMonthTabContent">
                @foreach($monthlyInsights as $mName => $insight)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="content-info-{{ $mName }}" role="tabpanel">
                        
                        <!-- Top Summary Metric Cards -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 border border-success border-opacity-30 bg-success bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-success text-white fw-bold"><i class="bi bi-graph-up-arrow me-1"></i> TREN NAIK / SURPLUS</span>
                                        <span class="fs-4 fw-bold text-success">{{ $insight['total_up'] }} Item</span>
                                    </div>
                                    <div class="text-muted fs-7">Penerimaan PO atau stok melebihi rencana forecast</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 border border-danger border-opacity-30 bg-danger bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-danger text-white fw-bold"><i class="bi bi-graph-down-arrow me-1"></i> TREN TURUN / DEFISIT</span>
                                        <span class="fs-4 fw-bold text-danger">{{ $insight['total_down'] }} Item</span>
                                    </div>
                                    <div class="text-muted fs-7">Penerimaan PO atau stok di bawah target forecast</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 border border-info border-opacity-30 bg-info bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-info text-white fw-bold"><i class="bi bi-check-all me-1"></i> STABIL / ON TRACK</span>
                                        <span class="fs-4 fw-bold text-info">{{ $insight['total_stable'] }} Item</span>
                                    </div>
                                    <div class="text-muted fs-7">Pergerakan stok selaras 100% dengan rencana</div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Badges per Month -->
                        <div class="row g-4">
                            <!-- Left: Items UP -->
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 h-100">
                                    <h6 class="fw-bold text-success mb-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-arrow-up-right-circle-fill fs-5"></i>
                                        <span>Item dengan Tren Naik ({{ $mName }})</span>
                                    </h6>
                                    @if(count($insight['items_up']) > 0)
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($insight['items_up'] as $item)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 d-flex align-items-center gap-2 shadow-sm text-start"
                                                        onclick="jumpToItemGrid('{{ $item['item_code'] }}')"
                                                        title="Klik untuk meluncur ke item {{ $item['item_code'] }}">
                                                    <span class="fw-bold text-white">{{ $item['item_code'] }}</span>
                                                    <span class="badge bg-success text-dark fw-bold">
                                                        +{{ number_format(max(abs($item['diff_po']), abs($item['diff_stock']))) }} unit
                                                    </span>
                                                    <i class="bi bi-box-arrow-in-down-right fs-7 text-success"></i>
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-muted small italic">Tidak ada item dengan lonjakan kenaikan pada bulan ini.</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Right: Items DOWN -->
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 h-100">
                                    <h6 class="fw-bold text-danger mb-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-arrow-down-right-circle-fill fs-5"></i>
                                        <span>Item dengan Tren Turun / Penurunan ({{ $mName }})</span>
                                    </h6>
                                    @if(count($insight['items_down']) > 0)
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($insight['items_down'] as $item)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 d-flex align-items-center gap-2 shadow-sm text-start"
                                                        onclick="jumpToItemGrid('{{ $item['item_code'] }}')"
                                                        title="Klik untuk meluncur ke item {{ $item['item_code'] }}">
                                                    <span class="fw-bold text-white">{{ $item['item_code'] }}</span>
                                                    <span class="badge bg-danger text-white fw-bold">
                                                        -{{ number_format(max(abs($item['diff_po']), abs($item['diff_stock']))) }} unit
                                                    </span>
                                                    <i class="bi bi-box-arrow-in-down-right fs-7 text-danger"></i>
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-muted small italic">Tidak ada item dengan defisit/penurunan pada bulan ini.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 📈 MULTI-DIMENSIONAL CHART SELECTOR HEADER -->
    <div class="glass-card mb-4 p-3 d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.9) 100%);">
        <div>
            <h5 class="fw-bold text-white mb-1 brand-font"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Pilihan Tren Grafik & Analisis Komparasi</h5>
            <small class="text-muted">Pilih dimensi untuk melihat grafik tren: <strong>QTY Order</strong>, <strong>Level Stok</strong>, <strong>Amount ($)</strong>, atau <strong>Harga Unit ($)</strong></small>
        </div>
        <div class="segmented-control" role="group" id="chartDimensionGroup">
            <button type="button" class="segmented-btn active" id="btnChartQty" onclick="switchChartDimension('qty')">
                <i class="bi bi-box-seam me-1"></i> QTY Order
            </button>
            <button type="button" class="segmented-btn" id="btnChartStock" onclick="switchChartDimension('stock')">
                <i class="bi bi-layers me-1"></i> Level Stok
            </button>
            <button type="button" class="segmented-btn" id="btnChartAmount" onclick="switchChartDimension('amount')">
                <i class="bi bi-currency-dollar me-1"></i> Amount ($)
            </button>
            <button type="button" class="segmented-btn" id="btnChartPrice" onclick="switchChartDimension('price')">
                <i class="bi bi-tag-fill me-1"></i> Harga Unit ($)
            </button>
        </div>
    </div>

    <!-- CHARTS SECTION -->
    <div class="row g-4 mt-3">
        <!-- PO ORDERS / DIMENSION COMPARISON CHART -->
        <div class="col-lg-6">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-white mb-1" id="chartMainTitle"><i class="bi bi-bar-chart-line text-warning me-2"></i>Komparasi Order PO: Forecast vs Aktual</h5>
                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2.5 py-1 rounded-pill font-monospace" style="font-size:0.75rem;">
                                <i class="bi bi-calendar3 me-1"></i>Periode Aktif: {{ $startMonth }} {{ $selectedYear }} &bull; Horizon {{ $duration }} Bulan
                            </span>
                            <span class="badge bg-dark border border-secondary text-info px-2.5 py-1 rounded-pill font-monospace" style="font-size:0.75rem;" id="chartActiveFilterBadge">
                                Filter: {{ $selectedItemCode !== 'ALL' ? 'Item ' . $selectedItemCode : ($selectedPo !== 'ALL' ? 'PO ' . $selectedPo : 'Semua Item & PO') }}
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <!-- Chart Filter by Item Code -->
                        <div>
                            <select id="chartItemCodeSelect" class="filter-select py-1 px-2.5 font-monospace fs-8" onchange="updateChartFilter(this.value, 'item')">
                                <option value="ALL">-- semua item code --</option>
                                @foreach($availableItemCodes as $code)
                                    <option value="{{ $code }}" {{ ($selectedItemCode ?? 'ALL') === $code ? 'selected' : '' }}>Item: {{ $code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Chart Filter by PO Number -->
                        <div>
                            <select id="chartPoSelect" class="filter-select py-1 px-2.5 font-monospace fs-8" onchange="updateChartFilter(this.value, 'po')">
                                <option value="ALL">-- semua no. po --</option>
                                @foreach($availablePoNumbers as $poNum)
                                    <option value="{{ $poNum }}" {{ ($selectedPo ?? 'ALL') === $poNum ? 'selected' : '' }}>PO: {{ $poNum }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Chart Visual Type Selector -->
                        <div class="segmented-control" role="group" id="chartTypeGroup">
                            <button type="button" class="segmented-btn segmented-btn-xs active" id="btnTypeBar" onclick="switchChartType('bar')" title="Grafik Batang (Bar)">
                                <i class="bi bi-bar-chart-fill me-1"></i> Batang
                            </button>
                            <button type="button" class="segmented-btn segmented-btn-xs" id="btnTypeLine" onclick="switchChartType('line')" title="Grafik Garis (Line)">
                                <i class="bi bi-graph-up me-1"></i> Garis
                            </button>
                            <button type="button" class="segmented-btn segmented-btn-xs" id="btnTypeArea" onclick="switchChartType('area')" title="Grafik Area (Filled Line)">
                                <i class="bi bi-water me-1"></i> Area
                            </button>
                            <button type="button" class="segmented-btn segmented-btn-xs" id="btnTypeDoughnut" onclick="switchChartType('doughnut')" title="Grafik Donut (Doughnut)">
                                <i class="bi bi-pie-chart-fill me-1"></i> Donut
                            </button>
                            <button type="button" class="segmented-btn segmented-btn-xs" id="btnTypePie" onclick="switchChartType('pie')" title="Grafik Pai (Pie)">
                                <i class="bi bi-pie-chart me-1"></i> Pai
                            </button>
                        </div>
                    </div>
                </div>
                <div class="position-relative" style="height: 360px;">
                    <canvas id="chartPoComparison"></canvas>
                </div>
            </div>
        </div>

        <!-- RIGHT CARD: TABEL TOP 10 ITEM CODE -->
        <div class="col-lg-6">
            <div class="glass-card h-100 p-3">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-white mb-0" id="top10CardTitle">
                            <i class="bi bi-trophy-fill text-warning me-2"></i>Peringkat Top 10 Item Code
                        </h5>
                        <small class="text-muted fs-7" id="top10CardSubtitle">Urutan 10 item code teratas berdasarkan kriteria terpilih</small>
                    </div>
                    <!-- Top 10 Criteria Selector Pills -->
                    <div class="segmented-control" role="group" id="top10CriteriaGroup">
                        <button type="button" class="segmented-btn segmented-btn-xs active" id="btnTopAmount" onclick="switchTop10Criteria('amount')" title="Top 10 Amount ($) Total">
                            <i class="bi bi-currency-dollar me-1"></i> Amount ($)
                        </button>
                        <button type="button" class="segmented-btn segmented-btn-xs" id="btnTopPo" onclick="switchTop10Criteria('po')" title="Top 10 Target Qty PO">
                            <i class="bi bi-box-seam me-1"></i> Qty PO
                        </button>
                        <button type="button" class="segmented-btn segmented-btn-xs" id="btnTopActual" onclick="switchTop10Criteria('actual')" title="Top 10 Qty Penerimaan Realisasi">
                            <i class="bi bi-truck me-1"></i> Qty Aktual
                        </button>
                        <button type="button" class="segmented-btn segmented-btn-xs" id="btnTopPrice" onclick="switchTop10Criteria('price')" title="Top 10 Harga Unit ($)">
                            <i class="bi bi-tag-fill me-1"></i> Harga Unit ($)
                        </button>
                    </div>
                </div>

                <div class="table-responsive style-scrollbar" style="max-height: 350px;">
                    <table class="table table-dark table-hover table-bordered align-middle mb-0 text-nowrap" style="font-size: 0.82rem; border-color: rgba(255,255,255,0.08);">
                        <thead class="sticky-top bg-dark text-muted uppercase fs-8">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>Item Code / Part</th>
                                <th>Deskripsi & Supplier</th>
                                <th class="text-end text-primary">Qty PO</th>
                                <th class="text-end text-info">Qty Aktual</th>
                                <th class="text-end text-warning">Harga ($)</th>
                                <th class="text-end text-success">Amount ($)</th>
                            </tr>
                        </thead>
                        <tbody id="top10TableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- OUTSTANDING PO QTY VS RECEIPT COMPARISON CHART -->
        <div class="col-12 mt-4">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-bar-chart-fill text-success me-2"></i>Komparasi Outstanding PO: Qty PO vs Qty Receipt</h5>
                    @if(($selectedPic ?? 'ALL') !== 'ALL' || ($selectedVendor ?? 'ALL') !== 'ALL' || ($selectedItemCode ?? 'ALL') !== 'ALL' || ($selectedPo ?? 'ALL') !== 'ALL')
                        <span class="badge bg-success text-white font-monospace" style="font-size: 0.75rem;">
                            <i class="bi bi-funnel-fill me-1"></i>
                            {{ ($selectedPic ?? 'ALL') !== 'ALL' ? 'PIC: ' . $selectedPic : '' }}
                            {{ ($selectedVendor ?? 'ALL') !== 'ALL' ? ' | Vendor: ' . $selectedVendor : '' }}
                            {{ ($selectedItemCode ?? 'ALL') !== 'ALL' ? ' | Item: ' . $selectedItemCode : '' }}
                            {{ ($selectedPo ?? 'ALL') !== 'ALL' ? ' | PO: ' . $selectedPo : '' }}
                        </span>
                    @endif
                </div>
                <div class="position-relative" style="height: 380px;">
                    <canvas id="chartOutstandingPoComparison"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════ --}}
    {{-- SUPPLIER MONTHLY SUMMARY & MULTI-CURRENCY ANALYTICAL LAYER     --}}
    {{-- ════════════════════════════════════════════════════════════════ --}}
    @php
        $activeSupplier = ($selectedVendor ?? 'ALL') !== 'ALL' ? $selectedVendor : null;
        $supplierSummaryTitle = $activeSupplier ? $activeSupplier : 'Semua Supplier';

        // Compute KPI totals for supplier summary section (both USD and IDR)
        $supKpiForecastQty    = 0; $supKpiActualQty    = 0;
        $supKpiForecastAmtUsd = 0.0; $supKpiActualAmtUsd = 0.0;
        $supKpiForecastAmtIdr = 0.0; $supKpiActualAmtIdr = 0.0;
        $supKpiItemCount      = 0;

        if ($activeSupplier && isset($supplierTotals[$activeSupplier])) {
            $st = $supplierTotals[$activeSupplier];
            $supKpiForecastQty    = $st['total_forecast_qty'];
            $supKpiActualQty      = $st['total_actual_qty'];
            $supKpiForecastAmtUsd = $st['total_forecast_amount_usd'];
            $supKpiForecastAmtIdr = $st['total_forecast_amount_idr'];
            $supKpiActualAmtUsd   = $st['total_incoming_amount_usd'];
            $supKpiActualAmtIdr   = $st['total_incoming_amount_idr'];
            $supKpiItemCount      = $st['item_count'];
        } else {
            foreach ($supplierTotals as $st) {
                $supKpiForecastQty    += $st['total_forecast_qty'];
                $supKpiActualQty      += $st['total_actual_qty'];
                $supKpiForecastAmtUsd += $st['total_forecast_amount_usd'];
                $supKpiForecastAmtIdr += $st['total_forecast_amount_idr'];
                $supKpiActualAmtUsd   += $st['total_incoming_amount_usd'];
                $supKpiActualAmtIdr   += $st['total_incoming_amount_idr'];
                $supKpiItemCount      += $st['item_count'];
            }
        }
        $supKpiVarianceAmtUsd = $supKpiActualAmtUsd - $supKpiForecastAmtUsd;
        $supKpiVarianceAmtIdr = $supKpiActualAmtIdr - $supKpiForecastAmtIdr;
        $supKpiAchievement    = $supKpiForecastQty > 0
            ? round(($supKpiActualQty / $supKpiForecastQty) * 100, 1)
            : ($supKpiActualQty > 0 ? 'Unplanned' : 0);
    @endphp

    <div class="glass-card mb-4 p-4 border border-warning border-opacity-20 shadow-lg" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.96) 0%, rgba(30, 41, 59, 0.92) 100%);">
        {{-- Header Bar: Title, Currency Toggle, MoM Button & Filter Reset --}}
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 border-bottom border-secondary border-opacity-25 pb-3 mb-4">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h3 class="fw-bold text-white mb-0" style="font-size:1.2rem;">
                        <i class="bi bi-building me-2 text-warning"></i>
                        Supplier Analysis: <span class="text-warning">{{ $supplierSummaryTitle }}</span>
                    </h3>
                    @if($supplierReconciliationPassed ?? true)
                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill font-monospace" style="font-size:0.7rem;" title="Integritas data terverifikasi: SUM(Item Code) = SUM(Supplier) = Global Monthly Total">
                            <i class="bi bi-shield-check me-1"></i> 100% Reconciled
                        </span>
                    @endif
                </div>
                <p class="text-muted small mb-0">
                    @if($activeSupplier)
                        Komparasi rencana pembelian (<strong class="text-info">Forecast</strong>) vs realisasi fisik (<strong class="text-success">Incoming</strong>) untuk seluruh item code milik <strong class="text-warning">{{ $activeSupplier }}</strong>.
                    @else
                        Layer analitik global agregasi Supplier × Item Code × Periode Bulan. Normalisasi multi-currency akurat & bebas double counting.
                    @endif
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- MoM Quantity Change Analysis Modal Trigger --}}
                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalSupplierMoMAnalytics" title="Lihat detail kontributor kenaikan / penurunan kuantitas antarbulan">
                    <i class="bi bi-bar-chart-steps me-1"></i> Analisis Perubahan MoM
                </button>

                {{-- Currency Segmented Control for Supplier Analytics --}}
                <div class="segmented-control" role="group">
                    <button type="button" class="segmented-btn active" id="btnSupCurrUsd" onclick="switchSupplierCurrency('USD')">
                        <i class="bi bi-currency-dollar me-1"></i> USD ($)
                    </button>
                    <button type="button" class="segmented-btn" id="btnSupCurrIdr" onclick="switchSupplierCurrency('IDR')">
                        <i class="bi bi-cash-stack me-1"></i> IDR (Rp)
                    </button>
                </div>

                @if($activeSupplier)
                    <a href="{{ route('purchasing.analysis', array_merge(request()->except(['vendor','page']), ['vendor' => 'ALL'])) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Semua Supplier
                    </a>
                @endif
            </div>
        </div>

        {{-- ── SUPPLIER KPI SUMMARY CARDS (Multi-Currency Dual View) ── --}}
        <div class="row g-3 g-xl-4 mb-4">
            {{-- Card 1: Forecast Amount --}}
            <div class="col-6 col-lg-3">
                <div class="kpi-card kpi-card-cyan">
                    <div class="kpi-header">
                        <span class="kpi-title">FORECAST AMOUNT</span>
                        <div class="kpi-icon-box icon-cyan"><i class="bi bi-graph-up"></i></div>
                    </div>
                    <div class="kpi-value text-info sup-val-usd" style="font-size:1.35rem;">
                        $ {{ number_format($supKpiForecastAmtUsd, 2, '.', ',') }}
                    </div>
                    <div class="kpi-value text-info sup-val-idr d-none" style="font-size:1.35rem;">
                        Rp {{ number_format($supKpiForecastAmtIdr, 0, ',', '.') }}
                    </div>
                    <div class="kpi-sub text-muted" style="font-size:0.72rem;">
                        Rencana demand {{ $supKpiItemCount }} item (Budget Rate)
                    </div>
                </div>
            </div>

            {{-- Card 2: Actual / Incoming Amount --}}
            <div class="col-6 col-lg-3">
                <div class="kpi-card kpi-card-emerald">
                    <div class="kpi-header">
                        <span class="kpi-title">ACTUAL / INCOMING</span>
                        <div class="kpi-icon-box icon-emerald"><i class="bi bi-box-arrow-in-down"></i></div>
                    </div>
                    <div class="kpi-value text-success sup-val-usd" style="font-size:1.35rem;">
                        $ {{ number_format($supKpiActualAmtUsd, 2, '.', ',') }}
                    </div>
                    <div class="kpi-value text-success sup-val-idr d-none" style="font-size:1.35rem;">
                        Rp {{ number_format($supKpiActualAmtIdr, 0, ',', '.') }}
                    </div>
                    <div class="kpi-sub text-muted" style="font-size:0.72rem;">
                        Realisasi penerimaan (Actual Rate)
                    </div>
                </div>
            </div>

            {{-- Card 3: Variance Amount --}}
            <div class="col-6 col-lg-3">
                <div class="kpi-card kpi-card-amber">
                    <div class="kpi-header">
                        <span class="kpi-title">VARIANCE AMOUNT</span>
                        <div class="kpi-icon-box icon-amber"><i class="bi bi-arrow-left-right"></i></div>
                    </div>
                    <div class="kpi-value {{ $supKpiVarianceAmtUsd >= 0 ? 'text-success' : 'text-danger' }} sup-val-usd" style="font-size:1.35rem;">
                        {{ $supKpiVarianceAmtUsd >= 0 ? '+' : '' }}$ {{ number_format($supKpiVarianceAmtUsd, 2, '.', ',') }}
                    </div>
                    <div class="kpi-value {{ $supKpiVarianceAmtIdr >= 0 ? 'text-success' : 'text-danger' }} sup-val-idr d-none" style="font-size:1.35rem;">
                        {{ $supKpiVarianceAmtIdr >= 0 ? '+' : '' }}Rp {{ number_format($supKpiVarianceAmtIdr, 0, ',', '.') }}
                    </div>
                    <div class="kpi-sub text-muted" style="font-size:0.72rem;">
                        Selisih Incoming vs Forecast
                    </div>
                </div>
            </div>

            {{-- Card 4: Achievement & Qty --}}
            <div class="col-6 col-lg-3">
                <div class="kpi-card kpi-card-blue">
                    <div class="kpi-header">
                        <span class="kpi-title">ACHIEVEMENT & QTY</span>
                        <div class="kpi-icon-box icon-blue"><i class="bi bi-trophy"></i></div>
                    </div>
                    <div class="kpi-value text-warning" style="font-size:1.35rem;">
                        {{ is_numeric($supKpiAchievement) ? $supKpiAchievement . '%' : $supKpiAchievement }}
                    </div>
                    <div class="kpi-sub text-muted" style="font-size:0.72rem;">
                        {{ number_format($supKpiActualQty) }} PCS incoming / {{ number_format($supKpiForecastQty) }} PCS target
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SUPPLIER CHARTS: Line Amount (USD/IDR) + Bar Qty (PCS) ── --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-7">
                <div class="glass-card h-100 p-3" style="background:rgba(10,15,30,0.5);">
                    <div class="chart-section-title mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="chart-dot" style="background:#00d2ff"></span>
                            <span class="chart-dot" style="background:#34d399"></span>
                            <span id="labelSupplierAmountTrendTitle">Tren Amount: Forecast vs Actual/Incoming ($ USD)</span>
                        </div>
                        <span class="badge bg-secondary bg-opacity-20 text-muted font-monospace" style="font-size:0.7rem;">Normalisasi Rate Aktif</span>
                    </div>
                    <div class="position-relative" style="height:300px;">
                        <canvas id="chartSupplierAmountTrend"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="glass-card h-100 p-3" style="background:rgba(10,15,30,0.5);">
                    <div class="chart-section-title mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="chart-dot" style="background:#c084fc"></span>
                            <span class="chart-dot" style="background:#fbbf24"></span>
                            Quantity: Forecast vs Actual (PCS)
                        </div>
                        <span class="badge bg-secondary bg-opacity-20 text-muted font-monospace" style="font-size:0.7rem;">Volume Fisik</span>
                    </div>
                    <div class="position-relative" style="height:300px;">
                        <canvas id="chartSupplierQtyCompare"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SUPPLIER MONTHLY SUMMARY TABLE (When Specific Supplier Selected) ── --}}
        @if($activeSupplier && isset($supplierMonthlySummary[$activeSupplier]))
            <div class="glass-card p-3 mb-4" style="background:rgba(10,15,30,0.5);">
                <div class="chart-section-title mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="chart-dot" style="background:#fbbf24"></span>
                        <span class="chart-dot" style="background:#34d399"></span>
                        Monthly Summary: <strong class="text-white">{{ $activeSupplier }}</strong>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary bg-opacity-20 text-info border border-info border-opacity-30" style="font-size:0.7rem;">
                            <i class="bi bi-calendar2-check me-1"></i> Rencana = Forecast
                        </span>
                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30" style="font-size:0.7rem;">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Realisasi = Incoming
                        </span>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table-custom table-bordered" id="tableSupplierMonthly">
                        <thead>
                            <tr>
                                <th style="min-width:100px;">Bulan</th>
                                <th style="color:#c084fc;">Forecast Qty</th>
                                <th style="color:#34d399;">Incoming Qty</th>
                                <th style="color:#00d2ff;">
                                    <span class="sup-val-usd">Forecast ($)</span>
                                    <span class="sup-val-idr d-none">Forecast (Rp)</span>
                                </th>
                                <th style="color:#34d399;">
                                    <span class="sup-val-usd">Incoming ($)</span>
                                    <span class="sup-val-idr d-none">Incoming (Rp)</span>
                                </th>
                                <th>Variance Amount</th>
                                <th>Achievement</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplierMonthlySummary[$activeSupplier] as $mIdx => $mRow)
                                @php
                                    $smVarAmtUsd = $mRow['incoming_amount_usd'] - $mRow['forecast_amount_usd'];
                                    $smVarAmtIdr = $mRow['incoming_amount_idr'] - $mRow['forecast_amount_idr'];
                                @endphp
                                <tr>
                                    <td class="fw-bold text-white">{{ $mRow['month_name'] }}</td>
                                    <td class="font-monospace text-primary fw-bold">{{ number_format($mRow['forecast_qty']) }}</td>
                                    <td class="font-monospace text-success fw-bold">{{ number_format($mRow['actual_qty']) }}</td>
                                    
                                    {{-- Forecast Amount USD & IDR --}}
                                    <td class="font-monospace text-info text-nowrap">
                                        <span class="sup-val-usd">$ {{ number_format($mRow['forecast_amount_usd'], 2, '.', ',') }}</span>
                                        <span class="sup-val-idr d-none">Rp {{ number_format($mRow['forecast_amount_idr'], 0, ',', '.') }}</span>
                                    </td>

                                    {{-- Incoming Amount USD & IDR --}}
                                    <td class="font-monospace text-success text-nowrap">
                                        <span class="sup-val-usd">$ {{ number_format($mRow['incoming_amount_usd'], 2, '.', ',') }}</span>
                                        <span class="sup-val-idr d-none">Rp {{ number_format($mRow['incoming_amount_idr'], 0, ',', '.') }}</span>
                                    </td>

                                    {{-- Variance Amount USD & IDR --}}
                                    <td class="font-monospace fw-bold text-nowrap">
                                        <span class="sup-val-usd {{ $smVarAmtUsd >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $smVarAmtUsd >= 0 ? '+' : '' }}$ {{ number_format($smVarAmtUsd, 2, '.', ',') }}
                                        </span>
                                        <span class="sup-val-idr d-none {{ $smVarAmtIdr >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $smVarAmtIdr >= 0 ? '+' : '' }}Rp {{ number_format($smVarAmtIdr, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="{{ localGetAchievementClass($mRow['achievement_pct']) }}">{{ $mRow['achievement_pct'] }}</span>
                                    </td>
                                    <td>
                                        <span class="{{ localGetStatusBadgeClass($mRow['status']) }}">{{ $mRow['status'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @php
                                $smTotalFcQty    = 0; $smTotalAcQty    = 0;
                                $smTotalFcAmtUsd = 0.0; $smTotalAcAmtUsd = 0.0;
                                $smTotalFcAmtIdr = 0.0; $smTotalAcAmtIdr = 0.0;
                                foreach ($supplierMonthlySummary[$activeSupplier] as $mr) {
                                    $smTotalFcQty    += $mr['forecast_qty'];
                                    $smTotalAcQty    += $mr['actual_qty'];
                                    $smTotalFcAmtUsd += $mr['forecast_amount_usd'];
                                    $smTotalFcAmtIdr += $mr['forecast_amount_idr'];
                                    $smTotalAcAmtUsd += $mr['incoming_amount_usd'];
                                    $smTotalAcAmtIdr += $mr['incoming_amount_idr'];
                                }
                                $smTotalVarAmtUsd = $smTotalAcAmtUsd - $smTotalFcAmtUsd;
                                $smTotalVarAmtIdr = $smTotalAcAmtIdr - $smTotalFcAmtIdr;
                                $smTotalAchPct    = $smTotalFcQty > 0 ? round(($smTotalAcQty / $smTotalFcQty) * 100, 1) . '%' : '-';
                            @endphp
                            <tr style="background:rgba(255,255,255,0.04); border-top:2px solid rgba(255,255,255,0.15);">
                                <td class="fw-bold text-warning">TOTAL</td>
                                <td class="font-monospace text-primary fw-bold">{{ number_format($smTotalFcQty) }}</td>
                                <td class="font-monospace text-success fw-bold">{{ number_format($smTotalAcQty) }}</td>
                                
                                <td class="font-monospace text-info text-nowrap fw-bold">
                                    <span class="sup-val-usd">$ {{ number_format($smTotalFcAmtUsd, 2, '.', ',') }}</span>
                                    <span class="sup-val-idr d-none">Rp {{ number_format($smTotalFcAmtIdr, 0, ',', '.') }}</span>
                                </td>
                                <td class="font-monospace text-success text-nowrap fw-bold">
                                    <span class="sup-val-usd">$ {{ number_format($smTotalAcAmtUsd, 2, '.', ',') }}</span>
                                    <span class="sup-val-idr d-none">Rp {{ number_format($smTotalAcAmtIdr, 0, ',', '.') }}</span>
                                </td>
                                
                                <td class="font-monospace fw-bold text-nowrap">
                                    <span class="sup-val-usd {{ $smTotalVarAmtUsd >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $smTotalVarAmtUsd >= 0 ? '+' : '' }}$ {{ number_format($smTotalVarAmtUsd, 2, '.', ',') }}
                                    </span>
                                    <span class="sup-val-idr d-none {{ $smTotalVarAmtIdr >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $smTotalVarAmtIdr >= 0 ? '+' : '' }}Rp {{ number_format($smTotalVarAmtIdr, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td><span class="{{ localGetAchievementClass($smTotalAchPct) }}">{{ $smTotalAchPct }}</span></td>
                                <td class="text-muted">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- ── TOP SUPPLIER RANKING TABLE (Global Mode) ── --}}
        @if(!$activeSupplier && $supplierRanking->count() > 0)
            <div class="glass-card p-3" style="background:rgba(10,15,30,0.5);">
                <div class="chart-section-title mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="chart-dot" style="background:#fbbf24"></span>
                        <span class="chart-dot" style="background:#f87171"></span>
                        Top Supplier Ranking (Berdasarkan Forecast Purchasing)
                    </div>
                    <span class="text-muted small">
                        Klik <strong>Detail</strong> pada salah satu supplier untuk melihat perincian item code dan bulanan.
                    </span>
                </div>
                <div class="table-container">
                    <table class="table-custom table-bordered" id="tableSupplierRanking">
                        <thead>
                            <tr>
                                <th style="width:50px;">Rank</th>
                                <th style="min-width:180px;">Supplier</th>
                                <th>Items</th>
                                <th>Currency</th>
                                <th style="color:#c084fc;">
                                    <span class="sup-val-usd">Forecast Amount ($)</span>
                                    <span class="sup-val-idr d-none">Forecast Amount (Rp)</span>
                                </th>
                                <th style="color:#34d399;">
                                    <span class="sup-val-usd">Incoming Amount ($)</span>
                                    <span class="sup-val-idr d-none">Incoming Amount (Rp)</span>
                                </th>
                                <th>Variance</th>
                                <th>Achievement</th>
                                <th style="color:#fbbf24;">Kontribusi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supplierRanking as $rank => $sr)
                                @php
                                    $srVarAmtUsd = $sr->total_incoming_amount_usd - $sr->total_forecast_amount_usd;
                                    $srVarAmtIdr = $sr->total_incoming_amount_idr - $sr->total_forecast_amount_idr;
                                @endphp
                                <tr>
                                    <td class="fw-bold text-warning text-center">{{ $rank + 1 }}</td>
                                    <td class="text-start fw-bold text-white">{{ $sr->supplier }}</td>
                                    <td class="font-monospace text-center">{{ $sr->item_count }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary bg-opacity-25 font-monospace text-light" style="font-size:0.7rem;">
                                            {{ $sr->currency_badge }}
                                        </span>
                                    </td>
                                    
                                    {{-- Forecast Amount USD/IDR --}}
                                    <td class="font-monospace text-info text-nowrap">
                                        <span class="sup-val-usd">$ {{ number_format($sr->total_forecast_amount_usd, 2, '.', ',') }}</span>
                                        <span class="sup-val-idr d-none">Rp {{ number_format($sr->total_forecast_amount_idr, 0, ',', '.') }}</span>
                                    </td>

                                    {{-- Incoming Amount USD/IDR --}}
                                    <td class="font-monospace text-success text-nowrap">
                                        <span class="sup-val-usd">$ {{ number_format($sr->total_incoming_amount_usd, 2, '.', ',') }}</span>
                                        <span class="sup-val-idr d-none">Rp {{ number_format($sr->total_incoming_amount_idr, 0, ',', '.') }}</span>
                                    </td>

                                    {{-- Variance Amount USD/IDR --}}
                                    <td class="font-monospace fw-bold text-nowrap">
                                        <span class="sup-val-usd {{ $srVarAmtUsd >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $srVarAmtUsd >= 0 ? '+' : '' }}$ {{ number_format($srVarAmtUsd, 2, '.', ',') }}
                                        </span>
                                        <span class="sup-val-idr d-none {{ $srVarAmtIdr >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $srVarAmtIdr >= 0 ? '+' : '' }}Rp {{ number_format($srVarAmtIdr, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="{{ localGetAchievementClass($sr->achievement_pct) }}">{{ $sr->achievement_pct }}</span>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px; background:rgba(255,255,255,0.08);">
                                                <div class="progress-bar" style="width:{{ min(100, $sr->contribution_pct) }}%; background:linear-gradient(90deg,#fbbf24,#f59e0b);"></div>
                                            </div>
                                            <span class="font-monospace text-warning fw-bold" style="font-size:0.75rem; min-width:40px;">{{ $sr->contribution_pct }}%</span>
                                        </div>
                                    </td>

                                    <td>
                                        <a href="{{ route('purchasing.analysis', array_merge(request()->except(['vendor','page']), ['vendor' => $sr->supplier])) }}" class="btn btn-xs btn-outline-warning rounded-pill px-2 py-0 text-decoration-none" title="Lihat detail supplier {{ $sr->supplier }}">
                                            <i class="bi bi-arrow-right-circle"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Month-over-Month (MoM) Material & Supplier Analytics    --}}
    {{-- ════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalSupplierMoMAnalytics" tabindex="-1" aria-labelledby="modalSupplierMoMTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content glass-card border border-info border-opacity-30" style="background:#0f172a; color:#f8fafc;">
                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-steps fs-4 text-info"></i>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalSupplierMoMTitle">
                                Analisis Perubahan Kuantitas & Kontributor Material (MoM Change)
                            </h5>
                            <span class="text-muted small">Menjelaskan penyebab matematis kenaikan / penurunan kuantitas dari bulan ke bulan</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if(isset($supplierMoMAnalytics) && $supplierMoMAnalytics->count() > 1)
                        <div class="alert alert-dark border border-info border-opacity-25 rounded-3 mb-4 p-3" style="background:rgba(15,23,42,0.6);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-info-circle-fill text-info"></i>
                                <span class="fw-bold text-white">Prinsip Rekonsiliasi Perubahan Kuantitas:</span>
                            </div>
                            <p class="small text-muted mb-0">
                                Perubahan total Forecast dari satu bulan ke bulan berikutnya merupakan hasil penjumlahan bersih selisih seluruh Item Code & Supplier (<span class="text-warning font-monospace">Δ Net = ∑ Δ Supplier = ∑ Δ Item Code</span>).
                            </p>
                        </div>

                        <div class="accordion" id="accordionMoMChanges">
                            @foreach($supplierMoMAnalytics as $mIndex => $mom)
                                @if($mIndex > 1)
                                    <div class="accordion-item glass-card mb-3 border border-secondary border-opacity-25" style="background:rgba(30,41,59,0.7);">
                                        <h2 class="accordion-header" id="headingMoM{{ $mIndex }}">
                                            <button class="accordion-button {{ $mIndex === 2 ? '' : 'collapsed' }} text-white fw-bold d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMoM{{ $mIndex }}" aria-expanded="{{ $mIndex === 2 ? 'true' : 'false' }}" aria-controls="collapseMoM{{ $mIndex }}" style="background:rgba(15,23,42,0.85);">
                                                <div class="d-flex flex-wrap align-items-center gap-3 w-100 pe-3">
                                                    <div>
                                                        <i class="bi bi-calendar-event me-2 text-warning"></i>
                                                        <span>{{ $mom->prev_month_name }} &rarr; {{ $mom->month_name }}</span>
                                                    </div>
                                                    <div class="ms-auto d-flex align-items-center gap-2">
                                                        <span class="badge bg-secondary bg-opacity-30 font-monospace text-light">
                                                            {{ number_format($mom->prev_forecast_qty) }} &rarr; {{ number_format($mom->curr_forecast_qty) }} PCS
                                                        </span>
                                                        <span class="badge {{ $mom->diff_forecast_qty >= 0 ? 'bg-success' : 'bg-danger' }} font-monospace">
                                                            {{ $mom->diff_forecast_qty >= 0 ? '+' : '' }}{{ number_format($mom->diff_forecast_qty) }} PCS ({{ $mom->diff_forecast_pct >= 0 ? '+' : '' }}{{ $mom->diff_forecast_pct }}%)
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapseMoM{{ $mIndex }}" class="accordion-collapse collapse {{ $mIndex === 2 ? 'show' : '' }}" aria-labelledby="headingMoM{{ $mIndex }}" data-bs-parent="#accordionMoMChanges">
                                            <div class="accordion-body p-3">
                                                <div class="row g-3">
                                                    {{-- Top Supplier Contributors --}}
                                                    <div class="col-12 col-lg-5">
                                                        <div class="p-3 rounded-3 h-100" style="background:rgba(10,15,30,0.6); border:1px solid rgba(255,255,255,0.06);">
                                                            <h6 class="fw-bold text-warning mb-2" style="font-size:0.85rem;">
                                                                <i class="bi bi-building me-1"></i> Top Supplier Penyumbang Perubahan
                                                            </h6>
                                                            @if(!empty($mom->top_supplier_drivers))
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-dark table-borderless align-middle mb-0 font-monospace" style="font-size:0.75rem;">
                                                                        <thead>
                                                                            <tr class="text-muted border-bottom border-secondary border-opacity-25">
                                                                                <th>Supplier</th>
                                                                                <th class="text-end">Δ Qty</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($mom->top_supplier_drivers as $sd)
                                                                                <tr>
                                                                                    <td class="text-white text-truncate" style="max-width:140px;">{{ $sd['supplier'] }}</td>
                                                                                    <td class="text-end fw-bold {{ $sd['delta_qty'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                                        {{ $sd['delta_qty'] >= 0 ? '+' : '' }}{{ number_format($sd['delta_qty']) }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <p class="text-muted small mb-0">Tidak ada perubahan kuantitas signifikan.</p>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Top Item Code Contributors --}}
                                                    <div class="col-12 col-lg-7">
                                                        <div class="p-3 rounded-3 h-100" style="background:rgba(10,15,30,0.6); border:1px solid rgba(255,255,255,0.06);">
                                                            <h6 class="fw-bold text-info mb-2" style="font-size:0.85rem;">
                                                                <i class="bi bi-cpu me-1"></i> Top Material (Item Code) Penyumbang Perubahan
                                                            </h6>
                                                            @if(!empty($mom->top_item_drivers))
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-dark table-borderless align-middle mb-0 font-monospace" style="font-size:0.75rem;">
                                                                        <thead>
                                                                            <tr class="text-muted border-bottom border-secondary border-opacity-25">
                                                                                <th>Item Code</th>
                                                                                <th>Deskripsi</th>
                                                                                <th class="text-end">Δ Qty</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($mom->top_item_drivers as $idrv)
                                                                                <tr>
                                                                                    <td class="text-warning fw-bold">{{ $idrv['item_code'] }}</td>
                                                                                    <td class="text-white text-truncate" style="max-width:180px;">{{ $idrv['description'] }}</td>
                                                                                    <td class="text-end fw-bold {{ $idrv['delta_qty'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                                        {{ $idrv['delta_qty'] >= 0 ? '+' : '' }}{{ number_format($idrv['delta_qty']) }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <p class="text-muted small mb-0">Tidak ada item code yang berubah.</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">Memerlukan durasi minimal 2 bulan untuk analisis perbandingan MoM.</p>
                    @endif
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div> {{-- END SLIDE 2 TAB PANE --}}



        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- SLIDE 3: ANALISIS STOCK FORECAST VS STOCK ACTUAL                  --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade {{ ($activeSlide ?? 'slide1') === 'slide3' ? 'show active' : '' }}" id="slide3-content" role="tabpanel" aria-labelledby="tab-slide3-btn">

            @php
                $sumSlide3FcQty  = $slide3TotalForecastStockQty ?? (isset($displayGridS3) && $displayGridS3->isNotEmpty() ? $displayGridS3->sum(fn($x) => $x->inventory_grid[0]->forecast_stock_qty ?? 0) : 0);
                $sumSlide3FcUsd  = $slide3TotalForecastStockUsd ?? (isset($displayGridS3) && $displayGridS3->isNotEmpty() ? $displayGridS3->sum(fn($x) => $x->inventory_grid[0]->forecast_stock_usd ?? 0) : 0);
                $sumSlide3FcIdr  = $slide3TotalForecastStockIdr ?? (isset($displayGridS3) && $displayGridS3->isNotEmpty() ? $displayGridS3->sum(fn($x) => $x->inventory_grid[0]->forecast_stock_idr ?? 0) : 0);

                $sumSlide3ActQty = $slide3TotalActualStockQty ?? (isset($displayGridS3) && $displayGridS3->isNotEmpty() ? $displayGridS3->sum(fn($x) => $x->inventory_grid[0]->stock_qty ?? 0) : 0);
                $sumSlide3ActUsd = $slide3TotalActualStockUsd ?? (isset($displayGridS3) && $displayGridS3->isNotEmpty() ? $displayGridS3->sum(fn($x) => $x->inventory_grid[0]->stock_amount_usd ?? 0) : 0);
                $sumSlide3ActIdr = $slide3TotalActualStockIdr ?? (isset($displayGridS3) && $displayGridS3->isNotEmpty() ? $displayGridS3->sum(fn($x) => $x->inventory_grid[0]->stock_amount_idr ?? 0) : 0);

                $sumSlide3VarQty = $sumSlide3ActQty - $sumSlide3FcQty;
                $sumSlide3VarUsd = $sumSlide3ActUsd - $sumSlide3FcUsd;
                $sumSlide3VarIdr = $sumSlide3ActIdr - $sumSlide3FcIdr;

                $countMatching  = (isset($displayGridS3) && $displayGridS3->isNotEmpty()) ? $displayGridS3->filter(fn($x) => ($x->inventory_grid[0]->variance_qty ?? 0) == 0)->count() : 0;
                $countSurplus   = (isset($displayGridS3) && $displayGridS3->isNotEmpty()) ? $displayGridS3->filter(fn($x) => ($x->inventory_grid[0]->variance_qty ?? 0) > 0)->count() : 0;
                $countShortage  = (isset($displayGridS3) && $displayGridS3->isNotEmpty()) ? $displayGridS3->filter(fn($x) => ($x->inventory_grid[0]->variance_qty ?? 0) < 0)->count() : 0;
            @endphp

            {{-- ═══════════════════════════════════════════════════════════════════════ --}}
            {{-- 1. DEDICATED VENDOR DASHBOARD VIEW (Aktif saat melihat rincian Vendor) --}}
            {{-- ═══════════════════════════════════════════════════════════════════════ --}}
            <div id="slide3DedicatedVendorView" class="{{ (($s3_vendor ?? 'ALL') === 'ALL') ? 'd-none' : '' }}">
                
                {{-- ── Top Navigation & Vendor Profile Header ── --}}
                <div class="glass-card mb-4 p-3 p-md-4" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.98) 100%); border-left: 5px solid #a78bfa; overflow: visible !important; position: relative; z-index: 1050;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- Large Prominent Back Button --}}
                            <button type="button" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold font-monospace shadow" onclick="closeVendorDashboard()">
                                <i class="bi bi-arrow-left-circle-fill me-2 fs-6"></i> Kembali ke Ringkasan Semua Vendor
                            </button>
                            <span class="badge bg-purple bg-opacity-25 text-purple border border-purple border-opacity-50 px-3 py-2 rounded-pill font-monospace" style="color:#c4b5fd;">
                                <i class="bi bi-building-fill-check me-1"></i> Mode Detail Vendor &bull; Dashboard Fokus
                            </span>
                        </div>

                        {{-- Quick Switcher Dropdown (Fixed Z-Index & No Overflow Clipping) --}}
                        <div class="dropdown" style="position: relative; z-index: 1060;">
                            <button class="btn btn-dark border-secondary dropdown-toggle text-light rounded-pill px-3 py-2 font-monospace fw-semibold shadow-sm" type="button" id="dropdownSwitchVendorBtn" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true">
                                <i class="bi bi-arrow-left-right me-1 text-warning"></i> Ganti Vendor
                            </button>
                            <div class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg p-2" aria-labelledby="dropdownSwitchVendorBtn" style="max-height: 340px; overflow-y: auto; width: 300px; z-index: 1099; border: 1px solid rgba(255,255,255,0.2);">
                                <div class="p-1 mb-2">
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary rounded-pill" placeholder="Cari nama vendor..." onkeyup="filterQuickSwitchVendors(this.value)" onclick="event.stopPropagation()">
                                </div>
                                <ul class="list-unstyled mb-0" id="quickSwitchVendorList">
                                    <li>
                                        <a class="dropdown-item rounded-2 py-1.5 fw-bold text-warning d-flex align-items-center" href="javascript:void(0)" onclick="closeVendorDashboard()">
                                            <i class="bi bi-grid-fill me-2 text-warning"></i> Ringkasan Semua Vendor
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider border-secondary my-1"></li>
                                    @foreach($slide3VendorSummaries ?? [] as $vsItem)
                                        <li class="quick-vendor-item" data-vendor-name="{{ strtoupper($vsItem->supplier) }}">
                                            <a class="dropdown-item rounded-2 py-1.5 d-flex justify-content-between align-items-center {{ ($s3_vendor ?? '') === $vsItem->supplier ? 'active fw-bold' : '' }}" href="javascript:void(0)" onclick="openVendorDashboard('{{ addslashes($vsItem->supplier) }}')">
                                                <span class="text-truncate me-2"><i class="bi bi-building me-1.5 text-muted"></i> {{ $vsItem->supplier }}</span>
                                                <span class="badge bg-secondary bg-opacity-40 text-light font-monospace">{{ $vsItem->item_count }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-25 my-3">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h2 class="fw-bold text-white mb-1 brand-font d-flex align-items-center gap-2">
                                <i class="bi bi-building text-warning fs-3"></i> <span id="vDashTitle">{{ ($s3_vendor ?? 'ALL') !== 'ALL' ? $s3_vendor : '-' }}</span>
                            </h2>
                            <p class="text-muted small mb-0">
                                Menampilkan dashboard komparasi stok bahan baku piano yang dipasok oleh vendor ini secara menyeluruh tanpa perlu scroll berlebih.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-info bg-opacity-25 text-info border border-info px-3 py-2 rounded-pill font-monospace" id="vDashBadgeCount">
                                <i class="bi bi-boxes me-1"></i> {{ count($displayGridS3) }} Item Material
                            </span>
                            <span class="badge bg-secondary bg-opacity-30 text-light border border-secondary px-3 py-2 rounded-pill font-monospace" id="vDashBadgePic">
                                <i class="bi bi-person-badge me-1 text-warning"></i> PIC: {{ $displayGridS3->first()->pic_buyer ?? '-' }}
                            </span>
                            <span class="badge bg-secondary bg-opacity-30 text-light border border-secondary px-3 py-2 rounded-pill font-monospace" id="vDashBadgeDel">
                                <i class="bi bi-truck me-1 text-info"></i> Delivery: {{ $displayGridS3->first()->delivery_category_code ?? 'LOC' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- ── Dedicated Vendor KPI Summary Cards (4 Cards) ── --}}
                @php
                    $vActiveSummary = (($s3_vendor ?? 'ALL') !== 'ALL') ? collect($slide3VendorSummaries)->firstWhere('supplier', $s3_vendor) : null;
                    $vKpiFcQty  = $vActiveSummary ? $vActiveSummary->m0['forecast_stock_qty'] : $sumSlide3FcQty;
                    $vKpiFcUsd  = $vActiveSummary ? $vActiveSummary->m0['forecast_stock_usd'] : $sumSlide3FcUsd;
                    $vKpiFcIdr  = $vActiveSummary ? $vActiveSummary->m0['forecast_stock_idr'] : $sumSlide3FcIdr;
                    $vKpiActQty = $vActiveSummary ? $vActiveSummary->m0['actual_stock_qty'] : $sumSlide3ActQty;
                    $vKpiActUsd = $vActiveSummary ? $vActiveSummary->m0['actual_stock_usd'] : $sumSlide3ActUsd;
                    $vKpiActIdr = $vActiveSummary ? $vActiveSummary->m0['actual_stock_idr'] : $sumSlide3ActIdr;
                    $vKpiVarQty = $vActiveSummary ? $vActiveSummary->m0['variance_qty'] : $sumSlide3VarQty;
                    $vKpiVarUsd = $vActiveSummary ? $vActiveSummary->m0['variance_amount_usd'] : $sumSlide3VarUsd;
                    $vKpiVarIdr = $vActiveSummary ? $vActiveSummary->m0['variance_amount_idr'] : $sumSlide3VarIdr;
                    $vKpiStatus = $vActiveSummary ? $vActiveSummary->status : 'Optimal';
                @endphp
                <div class="row g-3 g-xl-4 mb-4">
                    {{-- Vendor KPI 1: Total Part Number --}}
                    <div class="col-6 col-lg-3">
                        <div class="kpi-card kpi-card-amber">
                            <div class="kpi-header">
                                <span class="kpi-title">TOTAL PART NUMBER</span>
                                <div class="kpi-icon-box icon-amber"><i class="bi bi-box-seam"></i></div>
                            </div>
                            <div class="kpi-value text-warning" id="vDashKpiItemCount" style="font-size:1.55rem;">
                                {{ $vActiveSummary ? $vActiveSummary->item_count : count($displayGridS3) }} Item
                            </div>
                            <div class="kpi-footer">
                                <span class="text-muted small">Komponen Material Vendor</span>
                            </div>
                        </div>
                    </div>

                    {{-- Vendor KPI 2: Planned Stock Forecast --}}
                    <div class="col-6 col-lg-3">
                        <div class="kpi-card kpi-card-blue">
                            <div class="kpi-header">
                                <span class="kpi-title">PLANNED STOCK FORECAST</span>
                                <div class="kpi-icon-box icon-blue"><i class="bi bi-box-seam"></i></div>
                            </div>
                            <div class="kpi-value text-info slide3-val-usd" id="vDashKpiFcUsd" style="font-size:1.45rem;">
                                $ {{ number_format($vKpiFcUsd, 2, '.', ',') }}
                            </div>
                            <div class="kpi-value text-info slide3-val-idr d-none" id="vDashKpiFcIdr" style="font-size:1.45rem;">
                                Rp {{ number_format($vKpiFcIdr, 0, ',', '.') }}
                            </div>
                            <div class="kpi-footer">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <span class="badge bg-primary bg-opacity-25 text-primary" id="vDashKpiFcQty">{{ number_format($vKpiFcQty) }} PCS</span>
                                    <small class="text-muted">Target Rencana</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vendor KPI 3: Realisasi Stock Actual --}}
                    <div class="col-6 col-lg-3">
                        <div class="kpi-card kpi-card-purple">
                            <div class="kpi-header">
                                <span class="kpi-title">REALISASI STOCK ACTUAL</span>
                                <div class="kpi-icon-box icon-purple"><i class="bi bi-boxes"></i></div>
                            </div>
                            <div class="kpi-value slide3-val-usd" id="vDashKpiActUsd" style="font-size:1.45rem; color: #a78bfa;">
                                $ {{ number_format($vKpiActUsd, 2, '.', ',') }}
                            </div>
                            <div class="kpi-value slide3-val-idr d-none" id="vDashKpiActIdr" style="font-size:1.45rem; color: #a78bfa;">
                                Rp {{ number_format($vKpiActIdr, 0, ',', '.') }}
                            </div>
                            <div class="kpi-footer">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <span class="badge bg-opacity-25" style="background: rgba(139,92,246,0.2); color: #a78bfa;" id="vDashKpiActQty">{{ number_format($vKpiActQty) }} PCS</span>
                                    <small class="text-muted">Stok Fisik Gudang</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Vendor KPI 4: Selisih & Status --}}
                    <div class="col-6 col-lg-3">
                        <div class="kpi-card {{ $vKpiVarUsd >= 0 ? 'kpi-card-emerald' : 'kpi-card-rose' }}">
                            <div class="kpi-header">
                                <span class="kpi-title">TOTAL ESTIMASI SELISIH</span>
                                <div class="kpi-icon-box {{ $vKpiVarUsd >= 0 ? 'icon-emerald' : 'icon-rose' }}"><i class="bi bi-arrow-left-right"></i></div>
                            </div>
                            <div class="kpi-value slide3-val-usd {{ $vKpiVarUsd >= 0 ? 'text-success' : 'text-danger' }}" id="vDashKpiVarUsd" style="font-size:1.45rem;">
                                {{ $vKpiVarUsd >= 0 ? '+' : '' }}$ {{ number_format($vKpiVarUsd, 2, '.', ',') }}
                            </div>
                            <div class="kpi-value slide3-val-idr d-none {{ $vKpiVarIdr >= 0 ? 'text-success' : 'text-danger' }}" id="vDashKpiVarIdr" style="font-size:1.45rem;">
                                {{ $vKpiVarIdr >= 0 ? '+' : '' }}Rp {{ number_format($vKpiVarIdr, 0, ',', '.') }}
                            </div>
                            <div class="kpi-footer">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <span class="badge {{ $vKpiVarQty >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-25 text-white" id="vDashKpiVarQty">{{ $vKpiVarQty >= 0 ? '+' : '' }}{{ number_format($vKpiVarQty) }} PCS</span>
                                    <span id="vDashKpiStatus">
                                        @if($vKpiStatus === 'Surplus')
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-1 font-monospace"><i class="bi bi-arrow-up-circle me-1"></i>Surplus</span>
                                        @elseif($vKpiStatus === 'Deficit')
                                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-2 py-1 font-monospace"><i class="bi bi-arrow-down-circle me-1"></i>Defisit</span>
                                        @else
                                            <span class="badge bg-info bg-opacity-25 text-info border border-info px-2 py-1 font-monospace"><i class="bi bi-check-circle me-1"></i>Optimal</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Dedicated Part Number Comparison Table ── --}}
                <div class="glass-card p-0 mb-4">
                    <div class="p-3 d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-table text-purple fs-5" style="color: #a78bfa;"></i>
                            <div>
                                <h5 class="fw-bold text-white mb-0 brand-font">Rincian Komparasi Part Number Material</h5>
                                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Perbandingan saldo Stock Forecast vs Actual per Item Material</small>
                            </div>
                            <span class="badge bg-secondary bg-opacity-50 text-light rounded-pill ms-2 font-monospace" id="vDashTableItemBadge">{{ count($displayGridS3) }} Item Material</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchSlide3Item" class="form-control bg-dark text-white border-secondary" placeholder="Cari Part Number / Deskripsi..." onkeyup="filterSlide3ItemTable(this.value)">
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 font-monospace fw-bold" onclick="closeVendorDashboard()">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Ringkasan
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0" id="tableSlide3InventoryComp">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">NO</th>
                                    <th>PART NUMBER / DRAWING</th>
                                    <th>DESKRIPSI &amp; VENDOR</th>
                                    <th class="text-center">METRIK / ELEMEN</th>
                                    <th class="text-end">PRE-MONTH (M0)</th>
                                    @for($i = 1; $i <= $s3_duration; $i++)
                                        <th class="text-end">{{ $monthsLabels[$i] ?? ('M' . $i) }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allDisplayGridS3 ?? $displayGridS3 as $idx => $g)
                                    @php
                                        $invGrid   = $g->inventory_grid;
                                        $vQty0     = $invGrid[0]->variance_qty ?? 0;
                                        $statusKey = $vQty0 > 0 ? 'SURPLUS' : ($vQty0 < 0 ? 'DEFICIT' : 'OPTIMAL');

                                        $itemCodeClean   = strtoupper(trim($g->item_code));
                                        $reasonObj       = $inventoryVarianceReasons[$itemCodeClean] ?? null;
                                        $gVendorUpper    = strtoupper(trim($g->supplier ?? ''));
                                        $isInitialActive = (($s3_vendor ?? 'ALL') !== 'ALL') && ($gVendorUpper === strtoupper(trim($s3_vendor)));
                                    @endphp

                                    {{-- Sub-Row 1: Stock Forecast --}}
                                    <tr class="slide3-row-group" data-status="{{ $statusKey }}" data-vendor="{{ $gVendorUpper }}" data-item-code="{{ $itemCodeClean }}" data-desc="{{ strtoupper($g->description ?? '') }}" style="{{ (($s3_vendor ?? 'ALL') !== 'ALL' && !$isInitialActive) ? 'display:none;' : '' }} background: rgba(30, 41, 59, 0.5); border-top: 2px solid rgba(139, 92, 246, 0.3);">
                                        <td rowspan="3" class="text-center text-muted fw-bold align-middle">{{ $idx + 1 }}</td>
                                        <td rowspan="3" class="align-middle">
                                            <div class="fw-bold text-white font-monospace item-code-text">{{ $g->item_code }}</div>
                                            @if($g->drawing && $g->drawing !== $g->item_code)
                                                <small class="text-muted font-monospace"><i class="bi bi-diagram-2 me-1"></i>{{ $g->drawing }}</small>
                                            @endif
                                        </td>
                                        <td rowspan="3" class="align-middle">
                                            <div class="fw-semibold text-light item-desc-text" style="max-width: 220px; white-space: normal;">{{ $g->description }}</div>
                                            <small class="text-info d-block"><i class="bi bi-building me-1"></i>{{ $g->supplier }}</small>
                                            {{-- Variance Reason Badge / Button --}}
                                            <div class="mt-1">
                                                @if($reasonObj)
                                                    <button type="button" 
                                                            class="btn btn-xs btn-outline-warning text-warning border-warning border-opacity-50 px-2 py-0.5 rounded-pill font-monospace" 
                                                            style="font-size:0.73rem;" 
                                                            data-part-no="{{ $g->item_code }}"
                                                            data-desc="{{ htmlspecialchars($g->description ?? '', ENT_QUOTES) }}"
                                                            data-status="{{ $statusKey }}"
                                                            data-category="{{ htmlspecialchars($reasonObj->reason_category ?? '', ENT_QUOTES) }}"
                                                            data-notes="{{ htmlspecialchars($reasonObj->reason_notes ?? '', ENT_QUOTES) }}"
                                                            onclick="triggerInvReasonModal(this)" 
                                                            title="Klik untuk edit alasan variansi stok">
                                                        <i class="bi bi-chat-left-text-fill me-1"></i> {{ $reasonObj->reason_category }}
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                            class="btn btn-xs btn-outline-secondary text-muted border-secondary border-opacity-50 px-2 py-0.5 rounded-pill font-monospace" 
                                                            style="font-size:0.73rem;" 
                                                            data-part-no="{{ $g->item_code }}"
                                                            data-desc="{{ htmlspecialchars($g->description ?? '', ENT_QUOTES) }}"
                                                            data-status="{{ $statusKey }}"
                                                            data-category=""
                                                            data-notes=""
                                                            onclick="triggerInvReasonModal(this)" 
                                                            title="Tambah alasan kenapa variansi stok naik/turun">
                                                        <i class="bi bi-chat-plus me-1"></i> + Alasan Variansi
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2 py-1"><i class="bi bi-box-seam me-1"></i>Stock Forecast</span>
                                        </td>
                                        {{-- Pre-month --}}
                                        <td class="text-end font-monospace">
                                            <div class="text-info fw-bold">{{ number_format($invGrid[0]->forecast_stock_qty ?? 0) }} PCS</div>
                                            <small class="text-muted slide3-val-usd">$ {{ number_format($invGrid[0]->forecast_stock_usd ?? 0, 2, '.', ',') }}</small>
                                            <small class="text-muted slide3-val-idr d-none">Rp {{ number_format($invGrid[0]->forecast_stock_idr ?? 0, 0, ',', '.') }}</small>
                                        </td>
                                        {{-- Months 1..duration --}}
                                        @for($i = 1; $i <= $s3_duration; $i++)
                                            <td class="text-end font-monospace">
                                                <div class="text-info fw-bold">{{ number_format($invGrid[$i]->forecast_stock_qty ?? 0) }} PCS</div>
                                                <small class="text-muted slide3-val-usd">$ {{ number_format($invGrid[$i]->forecast_stock_usd ?? 0, 2, '.', ',') }}</small>
                                                <small class="text-muted slide3-val-idr d-none">Rp {{ number_format($invGrid[$i]->forecast_stock_idr ?? 0, 0, ',', '.') }}</small>
                                            </td>
                                        @endfor
                                    </tr>

                                    {{-- Sub-Row 2: Stock Actual --}}
                                    <tr class="slide3-row-group" data-status="{{ $statusKey }}" data-vendor="{{ $gVendorUpper }}" data-item-code="{{ $itemCodeClean }}" data-desc="{{ strtoupper($g->description ?? '') }}" style="{{ (($s3_vendor ?? 'ALL') !== 'ALL' && !$isInitialActive) ? 'display:none;' : '' }} background: rgba(30, 41, 59, 0.3);">
                                        <td class="text-center">
                                            <span class="badge bg-purple bg-opacity-25 text-purple border border-purple border-opacity-50 px-2 py-1" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa;"><i class="bi bi-boxes me-1"></i>Stock Actual</span>
                                        </td>
                                        {{-- Pre-month --}}
                                        <td class="text-end font-monospace">
                                            <div class="text-purple fw-bold" style="color: #c4b5fd;">{{ number_format($invGrid[0]->stock_qty ?? 0) }} PCS</div>
                                            <small class="text-muted slide3-val-usd">$ {{ number_format($invGrid[0]->stock_amount_usd ?? 0, 2, '.', ',') }}</small>
                                            <small class="text-muted slide3-val-idr d-none">Rp {{ number_format($invGrid[0]->stock_amount_idr ?? 0, 0, ',', '.') }}</small>
                                        </td>
                                        {{-- Months 1..duration --}}
                                        @for($i = 1; $i <= $s3_duration; $i++)
                                            @php
                                                $hasActM = !empty($invGrid[$i]->has_actual_data) && ($invGrid[$i]->stock_qty !== null);
                                            @endphp
                                            <td class="text-end font-monospace">
                                                @if($hasActM)
                                                    <div class="text-purple fw-bold" style="color: #c4b5fd;">{{ number_format($invGrid[$i]->stock_qty) }} PCS</div>
                                                    <small class="text-muted slide3-val-usd">$ {{ number_format($invGrid[$i]->stock_amount_usd ?? 0, 2, '.', ',') }}</small>
                                                    <small class="text-muted slide3-val-idr d-none">Rp {{ number_format($invGrid[$i]->stock_amount_idr ?? 0, 0, ',', '.') }}</small>
                                                @else
                                                    <div class="text-muted font-monospace">—</div>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>

                                    {{-- Sub-Row 3: Selisih / Variance --}}
                                    <tr class="slide3-row-group" data-status="{{ $statusKey }}" data-vendor="{{ $gVendorUpper }}" data-item-code="{{ $itemCodeClean }}" data-desc="{{ strtoupper($g->description ?? '') }}" style="{{ (($s3_vendor ?? 'ALL') !== 'ALL' && !$isInitialActive) ? 'display:none;' : '' }} background: rgba(15, 23, 42, 0.6); border-bottom: 2px solid rgba(255, 255, 255, 0.08);">
                                        <td class="text-center">
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary px-2 py-1"><i class="bi bi-arrow-left-right me-1"></i>Selisih (Variance)</span>
                                        </td>
                                        {{-- Pre-month --}}
                                        @php
                                            $vAmtUsd0 = $invGrid[0]->variance_amount_usd ?? 0;
                                            $vAmtIdr0 = $invGrid[0]->variance_amount_idr ?? 0;
                                        @endphp
                                        <td class="text-end font-monospace">
                                            <div class="fw-bold {{ $vQty0 >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $vQty0 >= 0 ? '+' : '' }}{{ number_format($vQty0) }} PCS
                                            </div>
                                            <small class="slide3-val-usd {{ $vAmtUsd0 >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $vAmtUsd0 >= 0 ? '+' : '' }}$ {{ number_format($vAmtUsd0, 2, '.', ',') }}
                                            </small>
                                            <small class="slide3-val-idr d-none {{ $vAmtIdr0 >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $vAmtIdr0 >= 0 ? '+' : '' }}Rp {{ number_format($vAmtIdr0, 0, ',', '.') }}
                                            </small>
                                        </td>
                                        {{-- Months 1..duration --}}
                                        @for($i = 1; $i <= $s3_duration; $i++)
                                            @php
                                                $hasActM  = !empty($invGrid[$i]->has_actual_data) && ($invGrid[$i]->variance_qty !== null);
                                                $vQtyM    = $invGrid[$i]->variance_qty ?? 0;
                                                $vAmtUsdM = $invGrid[$i]->variance_amount_usd ?? 0;
                                                $vAmtIdrM = $invGrid[$i]->variance_amount_idr ?? 0;
                                            @endphp
                                            <td class="text-end font-monospace">
                                                @if($hasActM)
                                                    <div class="fw-bold {{ $vQtyM >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $vQtyM >= 0 ? '+' : '' }}{{ number_format($vQtyM) }} PCS
                                                    </div>
                                                    <small class="slide3-val-usd {{ $vAmtUsdM >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $vAmtUsdM >= 0 ? '+' : '' }}$ {{ number_format($vAmtUsdM, 2, '.', ',') }}
                                                    </small>
                                                    <small class="slide3-val-idr d-none {{ $vAmtIdrM >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $vAmtIdrM >= 0 ? '+' : '' }}Rp {{ number_format($vAmtIdrM, 0, ',', '.') }}
                                                    </small>
                                                @else
                                                    <div class="text-muted font-monospace">—</div>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 4 + $s3_duration + 1 }}" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 text-warning d-block mb-2"></i>
                                            Belum ada data komparasi stok yang memenuhi filter pencarian.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Bottom Back Button --}}
                    <div class="p-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-warning rounded-pill px-4 py-1.5 font-monospace fw-bold shadow-sm" onclick="closeVendorDashboard()">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Ringkasan Semua Vendor
                        </button>
                        <small class="text-muted">PT Kawai Indonesia — Dashboard Monitoring Stok Bahan Baku</small>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════════ --}}
            {{-- 2. SLIDE 3 OVERVIEW SECTION (Aktif saat melihat Ringkasan Semua Vendor) --}}
            {{-- ═══════════════════════════════════════════════════════════════════════ --}}
            <div id="slide3OverviewSection" class="{{ (($s3_vendor ?? 'ALL') !== 'ALL') ? 'd-none' : '' }}">

                <!-- 🎛️ INDEPENDENT FILTER PANEL: SLIDE 3 -->
                <div class="glass-card mb-4 p-3 border border-purple border-opacity-30" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.85) 100%); border-color: rgba(139, 92, 246, 0.4) !important;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-funnel-fill fs-5" style="color: #a78bfa;"></i>
                            <h6 class="fw-bold text-white mb-0">Filter Khusus Slide 3 (Stock Forecast vs Stock Actual)</h6>
                            @if(($activeFilterCountS3 ?? 0) > 0)
                                <span class="badge bg-purple text-white rounded-pill px-2.5 py-1 fw-bold" style="background: #8b5cf6;">{{ $activeFilterCountS3 }} Filter Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-25 text-muted rounded-pill px-2.5 py-1">Semua Data</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-light rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilterSlide3" aria-expanded="{{ ($activeFilterCountS3 ?? 0) > 0 ? 'true' : 'false' }}">
                                <i class="bi bi-sliders me-1"></i> Buka / Tutup Filter <i class="bi bi-chevron-down ms-1"></i>
                            </button>
                        </div>
                    </div>

                    @if(($activeFilterCountS3 ?? 0) > 0)
                        <div class="d-flex flex-wrap gap-1.5 align-items-center mt-2.5 pt-2 border-top border-secondary border-opacity-25">
                            <small class="text-muted me-1 fw-semibold">Filter Aktif:</small>
                            @foreach($activeFiltersListS3 as $af)
                                <span class="badge bg-dark rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1" style="border: 1px solid #a78bfa; color: #c4b5fd;">
                                    {{ $af['label'] }}
                                    <a href="{{ route('purchasing.analysis', array_merge(request()->query(), ['s3_'.$af['key'] => ($af['key'] === 'year' ? '2026' : ($af['key'] === 'duration' ? 8 : 'ALL')), 'active_slide' => 'slide3'])) }}" class="text-white text-decoration-none ms-1" title="Hapus filter">&times;</a>
                                </span>
                            @endforeach
                            <a href="{{ route('purchasing.analysis', ['reset_slide' => 'slide3', 'active_slide' => 'slide3']) }}" class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill px-2.5 py-1.5 text-decoration-none ms-auto">
                                <i class="bi bi-x-circle me-1"></i> Reset Slide 3
                            </a>
                        </div>
                    @endif

                    <div class="collapse {{ ($activeFilterCountS3 ?? 0) > 0 ? 'show' : '' }}" id="collapseFilterSlide3">
                        <form method="GET" action="{{ route('purchasing.analysis') }}" class="pt-3 mt-2 border-top border-secondary border-opacity-25">
                            <input type="hidden" name="active_slide" value="slide3">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                    <label class="form-label text-muted small mb-1 fw-semibold">Item Code</label>
                                    <select name="s3_item_code" class="form-select form-select-sm bg-dark text-white border-secondary">
                                        <option value="ALL" {{ ($s3_item_code ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua Item --</option>
                                        @foreach($availableItemCodes as $code)
                                            <option value="{{ $code }}" {{ ($s3_item_code ?? 'ALL') === $code ? 'selected' : '' }}>{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                    <label class="form-label text-muted small mb-1 fw-semibold">Vendor</label>
                                    <select name="s3_vendor" class="form-select form-select-sm bg-dark text-white border-secondary">
                                        <option value="ALL" {{ ($s3_vendor ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua Vendor --</option>
                                        @foreach($availableVendors as $v)
                                            <option value="{{ $v }}" {{ ($s3_vendor ?? 'ALL') === $v ? 'selected' : '' }}>{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                    <label class="form-label text-muted small mb-1 fw-semibold">PIC / Buyer</label>
                                    <select name="s3_pic" class="form-select form-select-sm bg-dark text-white border-secondary">
                                        <option value="ALL" {{ ($s3_pic ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua PIC --</option>
                                        @foreach($availablePics as $p)
                                            <option value="{{ $p }}" {{ ($s3_pic ?? 'ALL') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                    <label class="form-label text-muted small mb-1 fw-semibold">No. PO</label>
                                    <select name="s3_po" class="form-select form-select-sm bg-dark text-white border-secondary">
                                        <option value="ALL" {{ ($s3_po ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua PO --</option>
                                        @foreach($availablePoNumbers as $poNum)
                                            <option value="{{ $poNum }}" {{ ($s3_po ?? 'ALL') === $poNum ? 'selected' : '' }}>{{ $poNum }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                                    <label class="form-label text-muted small mb-1 fw-semibold">Pengantaran</label>
                                    <select name="s3_delivery_category" class="form-select form-select-sm bg-dark text-white border-secondary">
                                        <option value="ALL" {{ ($s3_delivery_category ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua --</option>
                                        @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                            <option value="{{ $dc->code }}" {{ ($s3_delivery_category ?? 'ALL') === $dc->code ? 'selected' : '' }}>{{ $dc->code }} - {{ $dc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 col-lg-1">
                                    <label class="form-label text-muted small mb-1 fw-semibold">Tahun</label>
                                    <select name="s3_year" class="form-select form-select-sm bg-dark text-warning border-secondary fw-bold">
                                        <option value="ALL" {{ (string)($s3_year ?? '2026') === 'ALL' ? 'selected' : '' }}>All</option>
                                        @foreach(($availableYears ?? [2025, 2026, 2027, 2028]) as $yr)
                                            <option value="{{ $yr }}" {{ (string)($s3_year ?? '2026') === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 col-lg-1">
                                    <label class="form-label text-muted small mb-1 fw-semibold">Durasi</label>
                                    <select name="s3_duration" class="form-select form-select-sm bg-dark text-white border-secondary">
                                        @for($d = 1; $d <= ($maxForecastPeriods ?? 8); $d++)
                                            <option value="{{ $d }}" {{ ($s3_duration ?? 8) == $d ? 'selected' : '' }}>{{ $d }} Bln</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" style="background: #8b5cf6; border-color: #8b5cf6;">
                                        <i class="bi bi-check2-circle me-1"></i> Terapkan Filter Slide 3
                                    </button>
                                    <a href="{{ route('purchasing.analysis', ['reset_slide' => 'slide3', 'active_slide' => 'slide3']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                        <i class="bi bi-x-circle me-1"></i> Reset Slide 3
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if(empty($displayGridS3) || $displayGridS3->isEmpty())
                    <div class="alert alert-warning text-center my-4 py-4 rounded-4 shadow-sm border border-warning border-opacity-50">
                        <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2 text-warning"></i>
                        <h6 class="fw-bold text-white">Tidak ada data yang sesuai dengan filter Slide 3</h6>
                        <p class="text-muted small mb-3">Silakan sesuaikan filter atau reset untuk melihat seluruh data stok material.</p>
                        <a href="{{ route('purchasing.analysis', ['reset_slide' => 'slide3', 'active_slide' => 'slide3']) }}" class="btn btn-sm btn-outline-warning rounded-pill px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter Slide 3
                        </a>
                    </div>
                @endif

                @php
                    $sumSlide3FcQty  = $slide3TotalForecastStockQty ?? ($displayGridS3->sum(fn($x) => $x->inventory_grid[0]->forecast_stock_qty ?? 0));
                    $sumSlide3FcUsd  = $slide3TotalForecastStockUsd ?? ($displayGridS3->sum(fn($x) => $x->inventory_grid[0]->forecast_stock_usd ?? 0));
                    $sumSlide3FcIdr  = $slide3TotalForecastStockIdr ?? ($displayGridS3->sum(fn($x) => $x->inventory_grid[0]->forecast_stock_idr ?? 0));

                    $sumSlide3ActQty = $slide3TotalActualStockQty ?? ($displayGridS3->sum(fn($x) => $x->inventory_grid[0]->stock_qty ?? 0));
                    $sumSlide3ActUsd = $slide3TotalActualStockUsd ?? ($displayGridS3->sum(fn($x) => $x->inventory_grid[0]->stock_amount_usd ?? 0));
                    $sumSlide3ActIdr = $slide3TotalActualStockIdr ?? ($displayGridS3->sum(fn($x) => $x->inventory_grid[0]->stock_amount_idr ?? 0));

                    $sumSlide3VarQty = $sumSlide3ActQty - $sumSlide3FcQty;
                    $sumSlide3VarUsd = $sumSlide3ActUsd - $sumSlide3FcUsd;
                    $sumSlide3VarIdr = $sumSlide3ActIdr - $sumSlide3FcIdr;

                    $countMatching  = $displayGridS3->filter(fn($x) => ($x->inventory_grid[0]->variance_qty ?? 0) == 0)->count();
                    $countSurplus   = $displayGridS3->filter(fn($x) => ($x->inventory_grid[0]->variance_qty ?? 0) > 0)->count();
                    $countShortage  = $displayGridS3->filter(fn($x) => ($x->inventory_grid[0]->variance_qty ?? 0) < 0)->count();
                @endphp

                {{-- ── SLIDE 3 CONTROL TOOLBAR: CURRENCY TOGGLE & STATUS FILTER ── --}}
                <div class="glass-card mb-4 p-3 d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.9) 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-sliders text-purple fs-4" style="color: #a78bfa;"></i>
                        <div>
                            <h5 class="fw-bold text-white mb-0 brand-font">Opsi Tampilan Komparasi Stock Forecast vs Stock Actual</h5>
                            <small class="text-muted">Pilih mata uang ($ USD / Rp IDR) dan filter status evaluasi saldo stok persediaan</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        {{-- Status Dropdown Filter --}}
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-muted small fw-bold"><i class="bi bi-funnel me-1"></i>Filter Status:</label>
                            <select id="slide3StatusFilter" class="form-select form-select-sm bg-dark text-white border-secondary rounded-pill font-monospace" style="width: auto;" onchange="filterSlide3TableByStatus(this.value)">
                                <option value="ALL">-- Semua Status Stok (All) --</option>
                                <option value="SURPLUS">Surplus Stok (+ Kenaikan)</option>
                                <option value="DEFICIT">Defisit Stok (- Penurunan)</option>
                                <option value="OPTIMAL">Terpenuhi / Sesuai (Optimal)</option>
                            </select>
                        </div>

                        {{-- Currency Segmented Control --}}
                        <div class="segmented-control" role="group">
                            <button type="button" class="segmented-btn active" id="btnSlide3CurrUsd" onclick="switchSlide3Currency('USD')">
                                <i class="bi bi-currency-dollar me-1"></i> Dollar ($ USD)
                            </button>
                            <button type="button" class="segmented-btn" id="btnSlide3CurrIdr" onclick="switchSlide3Currency('IDR')">
                                <i class="bi bi-cash-stack me-1"></i> Rupiah (Rp IDR)
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── SUMMARY KPI CARDS SLIDE 3 ── --}}
                <div class="row g-3 g-xl-4 mb-4">
                    {{-- Card 1: Stock Forecast --}}
                    <div class="col-6 col-lg-3">
                        <div class="kpi-card kpi-card-blue">
                            <div class="kpi-header">
                                <span class="kpi-title">PLANNED STOCK FORECAST</span>
                                <div class="kpi-icon-box icon-blue">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            </div>
                            <div class="kpi-value text-info slide3-val-usd" style="font-size:1.45rem;">
                                $ {{ number_format($sumSlide3FcUsd, 2, '.', ',') }}
                            </div>
                            <div class="kpi-value text-info slide3-val-idr d-none" style="font-size:1.45rem;">
                                Rp {{ number_format($sumSlide3FcIdr, 0, ',', '.') }}
                            </div>
                            <div class="kpi-footer">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <span class="text-muted small font-monospace slide3-val-usd">Rp {{ number_format($sumSlide3FcIdr, 0, ',', '.') }}</span>
                                    <span class="text-muted small font-monospace slide3-val-idr d-none">$ {{ number_format($sumSlide3FcUsd, 2, '.', ',') }}</span>
                                    <span class="badge bg-primary bg-opacity-25 text-primary">{{ number_format($sumSlide3FcQty) }} PCS</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Stock Actual --}}
                    <div class="col-6 col-lg-3">
                        <div class="kpi-card kpi-card-purple">
                            <div class="kpi-header">
                                <span class="kpi-title">REALISASI STOCK ACTUAL</span>
                                <div class="kpi-icon-box icon-purple">
                                    <i class="bi bi-boxes"></i>
                                </div>
                            </div>
                            <div class="kpi-value slide3-val-usd" style="font-size:1.45rem; color: #a78bfa;">
                                $ {{ number_format($sumSlide3ActUsd, 2, '.', ',') }}
                            </div>
                            <div class="kpi-value slide3-val-idr d-none" style="font-size:1.45rem; color: #a78bfa;">
                                Rp {{ number_format($sumSlide3ActIdr, 0, ',', '.') }}
                            </div>
                            <div class="kpi-footer">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <span class="badge bg-opacity-25" style="background: rgba(139,92,246,0.2); color: #a78bfa;">{{ number_format($sumSlide3ActQty) }} PCS</span>
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success">{{ $slide3AvailablePeriodsCount ?? 2 }} / {{ $slide3TotalPeriodsCount ?? 12 }} Periode Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card 3: Selisih / Variance --}}
                    <div class="col-6 col-lg-3">
                        <div class="kpi-card {{ $sumSlide3VarUsd >= 0 ? 'kpi-card-emerald' : 'kpi-card-rose' }}">
                            <div class="kpi-header">
                                <span class="kpi-title">TOTAL SELISIH STOCK</span>
                                <div class="kpi-icon-box {{ $sumSlide3VarUsd >= 0 ? 'icon-emerald' : 'icon-rose' }}">
                                    <i class="bi bi-arrow-left-right"></i>
                                </div>
                            </div>
                            <div class="kpi-value slide3-val-usd {{ $sumSlide3VarUsd >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:1.45rem;">
                                {{ $sumSlide3VarUsd >= 0 ? '+' : '' }}$ {{ number_format($sumSlide3VarUsd, 2, '.', ',') }}
                            </div>
                            <div class="kpi-value slide3-val-idr d-none {{ $sumSlide3VarIdr >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:1.45rem;">
                                {{ $sumSlide3VarIdr >= 0 ? '+' : '' }}Rp {{ number_format($sumSlide3VarIdr, 0, ',', '.') }}
                            </div>
                            <div class="kpi-footer">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <span class="font-monospace small slide3-val-usd {{ $sumSlide3VarIdr >= 0 ? 'text-success' : 'text-danger' }}">{{ $sumSlide3VarIdr >= 0 ? '+' : '' }}Rp {{ number_format($sumSlide3VarIdr, 0, ',', '.') }}</span>
                                    <span class="font-monospace small slide3-val-idr d-none {{ $sumSlide3VarUsd >= 0 ? 'text-success' : 'text-danger' }}">{{ $sumSlide3VarUsd >= 0 ? '+' : '' }}$ {{ number_format($sumSlide3VarUsd, 2, '.', ',') }}</span>
                                    <span class="badge {{ $sumSlide3VarQty >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-25 text-white">{{ $sumSlide3VarQty >= 0 ? '+' : '' }}{{ number_format($sumSlide3VarQty) }} PCS</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card 4: Health Status & Data Coverage --}}
                    <div class="col-6 col-lg-3">
                        <div class="kpi-card kpi-card-amber">
                            <div class="kpi-header">
                                <span class="kpi-title">KESEHATAN &amp; DATA COVERAGE</span>
                                <div class="kpi-icon-box icon-amber">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1" title="Surplus"><i class="bi bi-arrow-up-circle me-1"></i>{{ $countSurplus }} Surplus</span>
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-2 py-1" title="Defisit"><i class="bi bi-arrow-down-circle me-1"></i>{{ $countShortage }} Defisit</span>
                                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-1" title="Optimal"><i class="bi bi-check-circle me-1"></i>{{ $countMatching }} Sesuai</span>
                            </div>
                            <div class="kpi-footer">
                                <span class="text-muted small"><i class="bi bi-clock-history me-1"></i>Data Transaksi: {{ $slide3AvailablePeriodsCount ?? 2 }} Bulan Terverifikasi</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── CHART SECTION SLIDE 3 ── --}}
                <div class="row g-4 mb-5">
                    <div class="col-12 col-xl-7">
                        <div class="glass-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-white mb-0 brand-font" id="slide3AmtChartTitle">
                                    <i class="bi bi-graph-up text-purple me-2" style="color: #a78bfa;"></i>Perbandingan Nominal Stock Forecast vs Stock Actual
                                </h5>
                                <span class="badge bg-purple bg-opacity-25 text-purple border border-purple border-opacity-50 px-2 py-1" id="slide3AmtChartBadge" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa;">Mata Uang: USD ($)</span>
                            </div>
                            <div style="height: 360px; position: relative;">
                                <canvas id="chartSlide3InventoryAmount"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-5">
                        <div class="glass-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-white mb-0 brand-font">
                                    <i class="bi bi-bar-chart-line-fill text-info me-2"></i>Perbandingan Qty Stock Forecast vs Stock Actual (PCS)
                                </h5>
                                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-1">Stock Forecast vs Actual</span>
                            </div>
                            <div style="height: 360px; position: relative;">
                                <canvas id="chartSlide3InventoryQty"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── MONTHLY STOCK SUMMARY & RECONCILIATION TABLE (Slide 3) ── --}}
                @if(isset($stockMonthlySummary) && count($stockMonthlySummary) > 0)
                    <div class="glass-card p-3 mb-4" style="background:rgba(15,23,42,0.6);">
                        <div class="chart-section-title mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="chart-dot" style="background:#3b82f6"></span>
                                <span class="chart-dot" style="background:#8b5cf6"></span>
                                <div>
                                    <h6 class="fw-bold text-white mb-0">Tabel Rekonsiliasi &amp; Tren Saldo Stock Bulanan (Summary)</h6>
                                    <small class="text-muted">Perbandingan menyeluruh Stock Forecast vs Stock Actual per horizon bulan (M0..M{{ $s3_duration }})</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 font-monospace" data-bs-toggle="modal" data-bs-target="#modalStockMovementAnalytics">
                                    <i class="bi bi-speedometer2 me-1"></i> Analisis Pergerakan MoM &amp; Driver Material
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-custom align-middle mb-0 font-monospace" style="font-size:0.8rem;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 70px;">PERIODE</th>
                                        <th class="text-center" style="width: 100px;">BULAN</th>
                                        <th class="text-end text-info">STOCK FC (QTY)</th>
                                        <th class="text-end text-info">STOCK FC (NOMINAL)</th>
                                        <th class="text-end text-purple" style="color:#c4b5fd;">STOCK ACT (QTY)</th>
                                        <th class="text-end text-purple" style="color:#c4b5fd;">STOCK ACT (NOMINAL)</th>
                                        <th class="text-end">SELISIH QTY</th>
                                        <th class="text-end">SELISIH NOMINAL</th>
                                        <th class="text-center">VARIANSI %</th>
                                        <th class="text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockMonthlySummary as $mIdx => $mRow)
                                        @php
                                            $isM0 = ($mIdx === 0);
                                            $hasActual = !empty($mRow['has_actual_data']);
                                            $vQty = $mRow['variance_qty'];
                                            $vAmtUsd = $mRow['variance_amount_usd'];
                                            $vAmtIdr = $mRow['variance_amount_idr'];
                                            $stat = $mRow['status'];
                                        @endphp
                                        <tr style="{{ $isM0 ? 'background: rgba(59, 130, 246, 0.08);' : '' }}">
                                            <td class="text-center fw-bold {{ $isM0 ? 'text-warning' : 'text-light' }}">
                                                {{ $isM0 ? 'M0 (Pre)' : 'M' . $mIdx }}
                                            </td>
                                            <td class="text-center fw-semibold text-white">
                                                {{ $mRow['month_name'] }}
                                            </td>
                                            <td class="text-end text-info fw-bold">
                                                {{ number_format($mRow['forecast_stock_qty']) }} PCS
                                            </td>
                                            <td class="text-end text-info">
                                                <span class="slide3-val-usd">$ {{ number_format($mRow['forecast_stock_usd'], 2, '.', ',') }}</span>
                                                <span class="slide3-val-idr d-none">Rp {{ number_format($mRow['forecast_stock_idr'], 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-end text-purple fw-bold" style="color:#c4b5fd;">
                                                @if($hasActual)
                                                    {{ number_format($mRow['actual_stock_qty']) }} PCS
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-purple" style="color:#c4b5fd;">
                                                @if($hasActual)
                                                    <span class="slide3-val-usd">$ {{ number_format($mRow['actual_stock_usd'], 2, '.', ',') }}</span>
                                                    <span class="slide3-val-idr d-none">Rp {{ number_format($mRow['actual_stock_idr'], 0, ',', '.') }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold {{ $hasActual ? ($vQty >= 0 ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                                @if($hasActual)
                                                    {{ $vQty >= 0 ? '+' : '' }}{{ number_format($vQty) }} PCS
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-end {{ $hasActual ? ($vAmtUsd >= 0 ? 'text-success' : 'text-danger') : 'text-muted' }}">
                                                @if($hasActual)
                                                    <span class="slide3-val-usd">{{ $vAmtUsd >= 0 ? '+' : '' }}$ {{ number_format($vAmtUsd, 2, '.', ',') }}</span>
                                                    <span class="slide3-val-idr d-none">{{ $vAmtIdr >= 0 ? '+' : '' }}Rp {{ number_format($vAmtIdr, 0, ',', '.') }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center font-monospace">
                                                @if($hasActual)
                                                    <span class="badge {{ $vQty >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-25 text-white">
                                                        {{ $mRow['variance_pct'] }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!$hasActual)
                                                    <span class="badge bg-secondary bg-opacity-25 text-muted">Belum Ada Data</span>
                                                @elseif($stat === 'Surplus')
                                                    <span class="badge bg-success bg-opacity-25 text-success border border-success">Surplus</span>
                                                @elseif($stat === 'Deficit')
                                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Defisit</span>
                                                @else
                                                    <span class="badge bg-info bg-opacity-25 text-info border border-info">Sesuai</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ── TOP 10 KONTRIBUTOR VARIANSI STOK MATERIAL ── --}}
                @if(isset($slide3TopVarianceItems) && count($slide3TopVarianceItems) > 0)
                    <div class="glass-card p-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-trophy-fill text-warning fs-5"></i>
                                <div>
                                    <h6 class="fw-bold text-white mb-0">Top 10 Kontributor Variansi Stok Material (Pre-Month M0)</h6>
                                    <small class="text-muted">Item dengan selisih stock forecast vs actual terbesar yang memerlukan prioritas monitoring pengadaan</small>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-custom align-middle mb-0" style="font-size: 0.82rem;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">NO</th>
                                        <th>PART NUMBER</th>
                                        <th>DESKRIPSI &amp; VENDOR</th>
                                        <th class="text-end text-info">STOCK FORECAST (PCS)</th>
                                        <th class="text-end text-purple" style="color:#c4b5fd;">STOCK ACTUAL (PCS)</th>
                                        <th class="text-end">SELISIH STOK (PCS)</th>
                                        <th class="text-center">VARIANSI %</th>
                                        <th class="text-end">ESTIMASI SELISIH ($ USD)</th>
                                        <th class="text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($slide3TopVarianceItems as $topIdx => $topItem)
                                        @php
                                            $diffVal = $topItem['variance_qty'];
                                            $isSurplus = $diffVal > 0;
                                            $isMatch = $diffVal == 0;
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted fw-bold">{{ $topIdx + 1 }}</td>
                                            <td class="fw-bold text-white font-monospace">
                                                {{ $topItem['item_code'] }}
                                                @if($topItem['drawing'] && $topItem['drawing'] !== $topItem['item_code'])
                                                    <small class="text-muted d-block">{{ $topItem['drawing'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-light">{{ $topItem['description'] }}</div>
                                                <small class="text-info"><i class="bi bi-building me-1"></i>{{ $topItem['supplier'] ?: '-' }}</small>
                                            </td>
                                            <td class="text-end font-monospace text-info fw-bold">{{ number_format($topItem['forecast_stock']) }}</td>
                                            <td class="text-end font-monospace text-purple fw-bold" style="color:#c4b5fd;">{{ number_format($topItem['actual_stock']) }}</td>
                                            <td class="text-end font-monospace fw-bold {{ $isMatch ? 'text-info' : ($isSurplus ? 'text-success' : 'text-danger') }}">
                                                {{ $diffVal > 0 ? '+' : '' }}{{ number_format($diffVal) }}
                                            </td>
                                            <td class="text-center font-monospace {{ $isMatch ? 'text-info' : ($isSurplus ? 'text-success' : 'text-danger') }}">
                                                {{ $topItem['variance_pct'] > 0 ? '+' : '' }}{{ $topItem['variance_pct'] }}%
                                            </td>
                                            <td class="text-end font-monospace {{ $topItem['variance_usd'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $topItem['variance_usd'] >= 0 ? '+' : '' }}$ {{ number_format($topItem['variance_usd'], 2, '.', ',') }}
                                            </td>
                                            <td class="text-center">
                                                @if($isMatch)
                                                    <span class="badge bg-info bg-opacity-25 text-info border border-info">Sesuai</span>
                                                @elseif($isSurplus)
                                                    <span class="badge bg-success bg-opacity-25 text-success border border-success">Surplus</span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Defisit</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ── TABEL RINGKASAN PER VENDOR ── --}}
                <div id="slide3VendorSection" class="glass-card p-0 mb-4">
                    <div class="p-3 d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-building-check text-purple fs-4" style="color: #a78bfa;"></i>
                            <div>
                                <h5 class="fw-bold text-white mb-0 brand-font">Tabel Ringkasan Komparasi Stock Forecast vs Stock Actual per Vendor</h5>
                                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Disederhanakan berdasarkan vendor penyedia. Klik <strong>Detail Item</strong> untuk membuka dashboard per vendor secara instan.</small>
                            </div>
                            <span class="badge bg-secondary bg-opacity-50 text-light rounded-pill ms-2 font-monospace" id="slide3VendorCountBadge">{{ count($slide3VendorSummaries ?? []) }} Vendor</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- Live Search Vendor --}}
                            <div class="input-group input-group-sm" style="max-width: 240px;">
                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchSlide3Vendor" class="form-control bg-dark text-white border-secondary" placeholder="Cari nama vendor..." onkeyup="filterSlide3VendorTable(this.value)">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0" id="tableSlide3VendorComp">
                            <thead>
                                <tr>
                                    <th style="width: 45px;" class="text-center">NO</th>
                                    <th style="min-width: 200px;">NAMA VENDOR / SUPPLIER</th>
                                    <th class="text-center" style="width: 90px;">JML ITEM</th>
                                    <th class="text-center" style="min-width: 140px;">METRIK / ELEMEN</th>
                                    <th class="text-end font-monospace" style="min-width: 120px;">PRE-MONTH (M0)</th>
                                    @for($i = 1; $i <= $s3_duration; $i++)
                                        <th class="text-end font-monospace" style="min-width: 110px;">{{ $monthsLabels[$i] ?? ('M' . $i) }}</th>
                                    @endfor
                                    <th class="text-center" style="min-width: 100px;">STATUS</th>
                                    <th class="text-center" style="width: 140px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($slide3VendorSummaries ?? [] as $vIdx => $vs)
                                    @php
                                        $vStatusKey = $vs->status === 'Surplus' ? 'SURPLUS' : ($vs->status === 'Deficit' ? 'DEFICIT' : 'OPTIMAL');
                                        $vM0VarQty  = $vs->m0['variance_qty'] ?? 0;
                                        $vM0VarUsd  = $vs->m0['variance_amount_usd'] ?? 0;
                                        $vM0VarIdr  = $vs->m0['variance_amount_idr'] ?? 0;
                                    @endphp

                                    {{-- Sub-Row 1: Vendor Stock Forecast --}}
                                    <tr class="slide3-vendor-row-group" data-status="{{ $vStatusKey }}" data-vendor="{{ strtoupper($vs->supplier) }}" style="background: rgba(30, 41, 59, 0.5); border-top: 2px solid rgba(139, 92, 246, 0.3);">
                                        <td rowspan="3" class="text-center text-muted fw-bold align-middle">{{ $vIdx + 1 }}</td>
                                        <td rowspan="3" class="align-middle">
                                            <div class="fw-bold text-white fs-6 vendor-name-text">
                                                <i class="bi bi-building text-warning me-1"></i>{{ $vs->supplier }}
                                            </div>
                                            @if(!empty($vs->pics))
                                                <small class="text-muted d-block font-monospace"><i class="bi bi-person me-1"></i>PIC: {{ implode(', ', array_slice($vs->pics, 0, 2)) }}</small>
                                            @endif
                                            @if(!empty($vs->delivery_categories))
                                                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary font-monospace" style="font-size:0.68rem;">
                                                    {{ implode(', ', $vs->delivery_categories) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td rowspan="3" class="text-center align-middle">
                                            <span class="badge bg-purple bg-opacity-25 text-purple border border-purple border-opacity-50 px-2.5 py-1 font-monospace rounded-pill fs-7" style="color: #c4b5fd;">
                                                <i class="bi bi-boxes me-1"></i>{{ $vs->item_count }} Item
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2 py-1"><i class="bi bi-box-seam me-1"></i>Stock Forecast</span>
                                        </td>
                                        {{-- Pre-month Forecast --}}
                                        <td class="text-end font-monospace">
                                            <div class="text-info fw-bold">{{ number_format($vs->m0['forecast_stock_qty']) }} PCS</div>
                                            <small class="text-muted slide3-val-usd">$ {{ number_format($vs->m0['forecast_stock_usd'], 2, '.', ',') }}</small>
                                            <small class="text-muted slide3-val-idr d-none">Rp {{ number_format($vs->m0['forecast_stock_idr'], 0, ',', '.') }}</small>
                                        </td>
                                        {{-- Months Forecast --}}
                                        @for($i = 1; $i <= $s3_duration; $i++)
                                            @php $vm = $vs->monthly[$i]; @endphp
                                            <td class="text-end font-monospace">
                                                <div class="text-info fw-bold">{{ number_format($vm['forecast_stock_qty']) }} PCS</div>
                                                <small class="text-muted slide3-val-usd">$ {{ number_format($vm['forecast_stock_usd'], 2, '.', ',') }}</small>
                                                <small class="text-muted slide3-val-idr d-none">Rp {{ number_format($vm['forecast_stock_idr'], 0, ',', '.') }}</small>
                                            </td>
                                        @endfor
                                        {{-- Status --}}
                                        <td rowspan="3" class="text-center align-middle">
                                            @if($vs->status === 'Surplus')
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-1 font-monospace"><i class="bi bi-arrow-up-circle me-1"></i>Surplus</span>
                                            @elseif($vs->status === 'Deficit')
                                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-2 py-1 font-monospace"><i class="bi bi-arrow-down-circle me-1"></i>Defisit</span>
                                            @else
                                                <span class="badge bg-info bg-opacity-25 text-info border border-info px-2 py-1 font-monospace"><i class="bi bi-check-circle me-1"></i>Optimal</span>
                                            @endif
                                        </td>
                                        {{-- Action Detail --}}
                                        <td rowspan="3" class="text-center align-middle">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 font-monospace fw-bold text-nowrap shadow-sm" 
                                                    onclick="openVendorDashboard('{{ addslashes($vs->supplier) }}')"
                                                    title="Buka dashboard rincian part number material dari {{ $vs->supplier }}">
                                                <i class="bi bi-arrow-right-circle-fill me-1"></i> Detail Item
                                            </button>
                                        </td>
                                    </tr>

                                    {{-- Sub-Row 2: Vendor Stock Actual --}}
                                    <tr class="slide3-vendor-row-group" data-status="{{ $vStatusKey }}" data-vendor="{{ strtoupper($vs->supplier) }}" style="background: rgba(30, 41, 59, 0.3);">
                                        <td class="text-center">
                                            <span class="badge bg-purple bg-opacity-25 text-purple border border-purple border-opacity-50 px-2 py-1" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa;"><i class="bi bi-boxes me-1"></i>Stock Actual</span>
                                        </td>
                                        {{-- Pre-month Actual --}}
                                        <td class="text-end font-monospace">
                                            <div class="text-purple fw-bold" style="color: #c4b5fd;">{{ number_format($vs->m0['actual_stock_qty']) }} PCS</div>
                                            <small class="text-muted slide3-val-usd">$ {{ number_format($vs->m0['actual_stock_usd'], 2, '.', ',') }}</small>
                                            <small class="text-muted slide3-val-idr d-none">Rp {{ number_format($vs->m0['actual_stock_idr'], 0, ',', '.') }}</small>
                                        </td>
                                        {{-- Months Actual --}}
                                        @for($i = 1; $i <= $s3_duration; $i++)
                                            @php $vm = $vs->monthly[$i]; @endphp
                                            <td class="text-end font-monospace">
                                                @if(!empty($vm['has_actual_data']))
                                                    <div class="text-purple fw-bold" style="color: #c4b5fd;">{{ number_format($vm['actual_stock_qty']) }} PCS</div>
                                                    <small class="text-muted slide3-val-usd">$ {{ number_format($vm['actual_stock_usd'], 2, '.', ',') }}</small>
                                                    <small class="text-muted slide3-val-idr d-none">Rp {{ number_format($vm['actual_stock_idr'], 0, ',', '.') }}</small>
                                                @else
                                                    <div class="text-muted font-monospace">—</div>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>

                                    {{-- Sub-Row 3: Vendor Selisih / Variance --}}
                                    <tr class="slide3-vendor-row-group" data-status="{{ $vStatusKey }}" data-vendor="{{ strtoupper($vs->supplier) }}" style="background: rgba(15, 23, 42, 0.6); border-bottom: 2px solid rgba(255, 255, 255, 0.08);">
                                        <td class="text-center">
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary px-2 py-1"><i class="bi bi-arrow-left-right me-1"></i>Selisih (Variance)</span>
                                        </td>
                                        {{-- Pre-month Selisih --}}
                                        <td class="text-end font-monospace">
                                            <div class="fw-bold {{ $vM0VarQty >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $vM0VarQty >= 0 ? '+' : '' }}{{ number_format($vM0VarQty) }} PCS
                                            </div>
                                            <small class="slide3-val-usd {{ $vM0VarUsd >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $vM0VarUsd >= 0 ? '+' : '' }}$ {{ number_format($vM0VarUsd, 2, '.', ',') }}
                                            </small>
                                            <small class="slide3-val-idr d-none {{ $vM0VarIdr >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $vM0VarIdr >= 0 ? '+' : '' }}Rp {{ number_format($vM0VarIdr, 0, ',', '.') }}
                                            </small>
                                        </td>
                                        {{-- Months Selisih --}}
                                        @for($i = 1; $i <= $s3_duration; $i++)
                                            @php
                                                $vm = $vs->monthly[$i];
                                                $vQtyM = $vm['variance_qty'] ?? 0;
                                                $vAmtUsdM = $vm['variance_amount_usd'] ?? 0;
                                                $vAmtIdrM = $vm['variance_amount_idr'] ?? 0;
                                            @endphp
                                            <td class="text-end font-monospace">
                                                @if(!empty($vm['has_actual_data']))
                                                    <div class="fw-bold {{ $vQtyM >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $vQtyM >= 0 ? '+' : '' }}{{ number_format($vQtyM) }} PCS
                                                    </div>
                                                    <small class="slide3-val-usd {{ $vAmtUsdM >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $vAmtUsdM >= 0 ? '+' : '' }}$ {{ number_format($vAmtUsdM, 2, '.', ',') }}
                                                    </small>
                                                    <small class="slide3-val-idr d-none {{ $vAmtIdrM >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $vAmtIdrM >= 0 ? '+' : '' }}Rp {{ number_format($vAmtIdrM, 0, ',', '.') }}
                                                    </small>
                                                @else
                                                    <div class="text-muted font-monospace">—</div>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 4 + $s3_duration + 2 }}" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 text-warning d-block mb-2"></i>
                                            Belum ada data vendor yang memenuhi filter pencarian Slide 3.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> {{-- END SLIDE 3 OVERVIEW SECTION --}}

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- MODAL: Month-over-Month (MoM) Stock Movement & Material Drivers   --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div class="modal fade" id="modalStockMovementAnalytics" tabindex="-1" aria-labelledby="modalStockMoMTitle" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content glass-card border border-purple border-opacity-30" style="background:#0f172a; color:#f8fafc;">
                        <div class="modal-header border-bottom border-secondary border-opacity-25">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-boxes fs-4 text-purple" style="color:#a78bfa;"></i>
                                <div>
                                    <h5 class="modal-title fw-bold text-white mb-0" id="modalStockMoMTitle">
                                        Analisis Pergerakan Stok & Kontributor Material (Stock Movement MoM)
                                    </h5>
                                    <span class="text-muted small">Menjelaskan penyebab matematis kenaikan / penurunan saldo inventori fisik antarbulan</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            @if(isset($stockMoMAnalytics) && count($stockMoMAnalytics) > 0)
                                <div class="alert alert-dark border border-purple border-opacity-25 rounded-3 mb-4 p-3" style="background:rgba(15,23,42,0.6);">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bi bi-info-circle-fill text-info"></i>
                                        <span class="fw-bold text-white">Prinsip Rekonsiliasi Pergerakan Stok:</span>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        Perubahan total stok aktual dari bulan sebelumnya ke bulan berikutnya merupakan hasil penjumlahan bersih pergerakan seluruh item code material (<span class="text-warning font-monospace">Δ Stock = ∑ (Stok Bulan Ini - Stok Bulan Lalu)</span>).
                                    </p>
                                </div>

                                <div class="accordion" id="accordionStockMoMChanges">
                                    @php
                                        $validMoMAnalytics = collect($stockMoMAnalytics)->filter();
                                    @endphp
                                    @forelse($validMoMAnalytics as $mIndex => $smom)
                                        <div class="accordion-item glass-card mb-3 border border-secondary border-opacity-25" style="background:rgba(30,41,59,0.7);">
                                            <h2 class="accordion-header" id="headingStockMoM{{ $mIndex }}">
                                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} text-white fw-bold d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStockMoM{{ $mIndex }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapseStockMoM{{ $mIndex }}" style="background:rgba(15,23,42,0.85);">
                                                    <div class="d-flex flex-wrap align-items-center gap-3 w-100 pe-3">
                                                        <div>
                                                            <i class="bi bi-calendar-event me-2 text-warning"></i>
                                                            <span>{{ $smom->prev_month_name }} &rarr; {{ $smom->curr_month_name }}</span>
                                                        </div>
                                                        <div class="ms-auto d-flex align-items-center gap-2">
                                                            <span class="badge bg-secondary bg-opacity-30 font-monospace text-light">
                                                                {{ number_format($smom->prev_stock_qty) }} &rarr; {{ number_format($smom->curr_stock_qty) }} PCS
                                                            </span>
                                                            <span class="badge {{ $smom->diff_stock_qty >= 0 ? 'bg-success' : 'bg-danger' }} font-monospace">
                                                                {{ $smom->diff_stock_qty >= 0 ? '+' : '' }}{{ number_format($smom->diff_stock_qty) }} PCS ({{ $smom->diff_stock_pct >= 0 ? '+' : '' }}{{ $smom->diff_stock_pct }}%)
                                                            </span>
                                                        </div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapseStockMoM{{ $mIndex }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="headingStockMoM{{ $mIndex }}" data-bs-parent="#accordionStockMoMChanges">
                                                <div class="accordion-body p-3">
                                                    <div class="row g-3">
                                                        {{-- Top Supplier Movers --}}
                                                        <div class="col-12 col-lg-5">
                                                            <div class="p-3 rounded-3 h-100" style="background:rgba(10,15,30,0.6); border:1px solid rgba(255,255,255,0.06);">
                                                                <h6 class="fw-bold text-warning mb-2" style="font-size:0.85rem;">
                                                                    <i class="bi bi-building me-1"></i> Top Supplier Penyumbang Perubahan Stok
                                                                </h6>
                                                                @if(!empty($smom->top_supplier_drivers))
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-dark table-borderless align-middle mb-0 font-monospace" style="font-size:0.75rem;">
                                                                            <thead>
                                                                                <tr class="text-muted border-bottom border-secondary border-opacity-25">
                                                                                    <th>Supplier</th>
                                                                                    <th class="text-end">Δ Stok</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($smom->top_supplier_drivers as $ssd)
                                                                                    <tr>
                                                                                        <td class="text-white text-truncate" style="max-width:140px;">{{ $ssd['supplier'] }}</td>
                                                                                        <td class="text-end fw-bold {{ $ssd['delta_qty'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                                            {{ $ssd['delta_qty'] >= 0 ? '+' : '' }}{{ number_format($ssd['delta_qty']) }}
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @else
                                                                    <p class="text-muted small mb-0">Tidak ada perubahan stok signifikan.</p>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        {{-- Top Item Code Movers --}}
                                                        <div class="col-12 col-lg-7">
                                                            <div class="p-3 rounded-3 h-100" style="background:rgba(10,15,30,0.6); border:1px solid rgba(255,255,255,0.06);">
                                                                <h6 class="fw-bold text-info mb-2" style="font-size:0.85rem;">
                                                                    <i class="bi bi-cpu me-1"></i> Top Material (Item Code) Penyumbang Perubahan Stok
                                                                </h6>
                                                                @if(!empty($smom->top_material_drivers))
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-dark table-borderless align-middle mb-0 font-monospace" style="font-size:0.75rem;">
                                                                            <thead>
                                                                                <tr class="text-muted border-bottom border-secondary border-opacity-25">
                                                                                    <th>Item Code</th>
                                                                                    <th>Deskripsi</th>
                                                                                    <th class="text-end">Bulan Lalu</th>
                                                                                    <th class="text-end">Bulan Ini</th>
                                                                                    <th class="text-end">Δ Stok</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($smom->top_material_drivers as $sdrv)
                                                                                    <tr>
                                                                                        <td class="text-warning fw-bold">{{ $sdrv['item_code'] }}</td>
                                                                                        <td class="text-white text-truncate" style="max-width:140px;">{{ $sdrv['description'] }}</td>
                                                                                        <td class="text-end text-muted">{{ number_format($sdrv['prev_qty']) }}</td>
                                                                                        <td class="text-end text-info">{{ number_format($sdrv['curr_qty']) }}</td>
                                                                                        <td class="text-end fw-bold {{ $sdrv['delta_qty'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                                            {{ $sdrv['delta_qty'] >= 0 ? '+' : '' }}{{ number_format($sdrv['delta_qty']) }}
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @else
                                                                    <p class="text-muted small mb-0">Tidak ada material yang berubah.</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted text-center py-4 mb-0">Belum ada data pergerakan stok antarbulan yang tervalidasi.</p>
                                    @endforelse
                                </div>
                            @else
                                <p class="text-muted text-center py-4 mb-0">Memerlukan durasi minimal 2 bulan untuk analisis pergerakan stok MoM.</p>
                            @endif
                        </div>
                        <div class="modal-footer border-top border-secondary border-opacity-25">
                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

        </div> {{-- END SLIDE 3 TAB PANE --}}
    </div> {{-- END STEP 6 SLIDE TAB CONTENT --}}
</div> {{-- END CONTAINER DASHBOARD --}}

<!-- Modal Alasan Penyimpangan Harga Material -->
<div class="modal fade" id="modalPriceReason" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-content-dark" style="background:#131c2e; border:1px solid rgba(255,255,255,0.12); border-radius:16px; color:#fff;">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-warning brand-font"><i class="bi bi-exclamation-triangle-fill me-2"></i> Analisis Penyimpangan Harga & Breakdown Per PO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPriceReason" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="p-3 rounded-3 bg-dark border border-warning border-opacity-30 mb-3 small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Item Code / Part:</span>
                            <strong class="text-warning brand-font fs-6" id="modal_price_item_code">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Harga Forecast (Rencana):</span>
                            <span class="text-info font-monospace fw-bold" id="modal_price_forecast">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Rata-Rata Harga Incoming (Weighted Avg):</span>
                            <span class="text-warning font-monospace fw-bold" id="modal_price_actual">-</span>
                        </div>
                        <div class="d-flex justify-content-between border-top border-secondary border-opacity-25 pt-2 mt-2">
                            <span class="text-muted">Status Penyimpangan Kumulatif:</span>
                            <span class="badge bg-warning text-dark font-monospace" id="modal_price_diff">-</span>
                        </div>
                    </div>

                    <!-- TABEL RINCIAN FLUKTUASI HARGA PER PO -->
                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold mb-2">
                            <i class="bi bi-list-columns-reverse text-warning me-1"></i> Rincian Per No. PO (Kenaikan / Penurunan Harga):
                        </label>
                        <div class="table-responsive rounded-3 border border-secondary border-opacity-25 bg-dark">
                            <table class="table table-custom text-start align-middle mb-0 small" style="font-size:0.82rem;">
                                <thead>
                                    <tr class="text-muted fs-8 bg-black bg-opacity-40">
                                        <th>NO. PO</th>
                                        <th>TGL RECEIPT</th>
                                        <th class="text-end">QTY TERIMA</th>
                                        <th class="text-end">HARGA INCOMING</th>
                                        <th class="text-end">PERUBAHAN VS FORECAST</th>
                                    </tr>
                                </thead>
                                <tbody id="modal_price_po_breakdown">
                                    <!-- Populated dynamically via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Alasan / Catatan Penyimpangan Harga <span class="text-danger">*</span></label>
                        <textarea name="price_deviation_reason" id="modal_price_reason_input" class="form-control bg-dark border-secondary text-light" rows="3" placeholder="Contoh: PO #0210 mengalami kenaikan harga bahan baku impor +$0,80, sedangkan PO #0226 mendapatkan diskon volume -$0,20 dari supplier..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark rounded-pill px-4"><i class="bi bi-save me-1"></i> Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.faq-modal')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── GLOBAL SAFE ARRAY NORMALIZER (0 for missing periods to ensure unbroken line) ──
    window.adjustLineData = function(arr) {
        if (!Array.isArray(arr)) return [];
        return arr.map(v => (v === undefined || v === null ? 0 : Number(v)));
    };

    // ── GLOBAL SLIDE TAB RESIZE HELPERS & LIFECYCLE ──
    window.resizeSlide2Charts = function() {
        setTimeout(() => {
            if (typeof renderMainChart === 'function') {
                renderMainChart(currentDimension, currentChartType);
            } else if (window.mainChartInstance) {
                window.mainChartInstance.resize();
            }
            if (window.chartTop10Instance) window.chartTop10Instance.resize();
            if (window.chartOutstandingPoInstance) window.chartOutstandingPoInstance.resize();
            if (window.chartSupplierAmtInstance) {
                window.chartSupplierAmtInstance.resize();
                window.chartSupplierAmtInstance.update();
            }
            if (window.chartSupplierQtyInstance) {
                window.chartSupplierQtyInstance.resize();
                window.chartSupplierQtyInstance.update();
            }
        }, 60);
    };

    window.resizeSlide3Charts = function() {
        setTimeout(() => {
            if (typeof window.initOrUpdateSlide3Charts === 'function') {
                window.initOrUpdateSlide3Charts();
            } else {
                if (window.chartSlide3AmtInstance) {
                    window.chartSlide3AmtInstance.resize();
                    window.chartSlide3AmtInstance.update();
                }
                if (window.chartSlide3QtyInstance) {
                    window.chartSlide3QtyInstance.resize();
                    window.chartSlide3QtyInstance.update();
                }
            }
            if (typeof window.switchSlide3Currency === 'function') {
                window.switchSlide3Currency(window.currentSlide3Currency || 'USD');
            }
        }, 150);
    };

    // Auto-attach tab shown event listeners to guarantee chart renders on tab switch
    document.addEventListener('DOMContentLoaded', () => {
        const tabSlide1 = document.getElementById('tab-slide1-btn');
        if (tabSlide1) {
            tabSlide1.addEventListener('shown.bs.tab', () => {
                if (typeof window.switchFxChartMode === 'function') {
                    window.switchFxChartMode(window.currentFxChartMode || 'amount_sum_usd');
                }
            });
        }

        const tabSlide2 = document.getElementById('tab-slide2-btn');
        if (tabSlide2) {
            tabSlide2.addEventListener('shown.bs.tab', () => {
                window.resizeSlide2Charts();
            });
        }

        const tabSlide3 = document.getElementById('tab-slide3-btn');
        if (tabSlide3) {
            tabSlide3.addEventListener('shown.bs.tab', () => {
                window.resizeSlide3Charts();
            });
        }
    });

    // ── EXCHANGE RATE AREA CHART & MULTI-METRIC MATRIX SWITCHERS ──
    const chartFxLabels               = @json($chartFxLabels ?? []);
    const chartFxForecastAmountUsd    = @json($chartFxForecastAmountUsd ?? []);
    const chartFxActualAmountUsd      = @json($chartFxActualAmountUsd ?? []);
    const chartFxForecastAmountIdr    = @json($chartFxForecastAmountIdr ?? []);
    const chartFxActualAmountIdr      = @json($chartFxActualAmountIdr ?? []);
    const chartFxForecastPriceUsd     = @json($chartFxForecastPriceUsd ?? []);
    const chartFxActualPriceUsd       = @json($chartFxActualPriceUsd ?? []);
    const chartFxForecastPriceIdr     = @json($chartFxForecastPriceIdr ?? []);
    const chartFxActualPriceIdr       = @json($chartFxActualPriceIdr ?? []);

    const chartFxAvgForecastAmountUsd = @json($chartFxAvgForecastAmountUsd ?? []);
    const chartFxAvgActualAmountUsd   = @json($chartFxAvgActualAmountUsd ?? []);
    const chartFxAvgForecastAmountIdr = @json($chartFxAvgForecastAmountIdr ?? []);
    const chartFxAvgActualAmountIdr   = @json($chartFxAvgActualAmountIdr ?? []);

    const chartFxSumForecastPriceUsd  = @json($chartFxSumForecastPriceUsd ?? []);
    const chartFxSumActualPriceUsd    = @json($chartFxSumActualPriceUsd ?? []);
    const chartFxSumForecastPriceIdr  = @json($chartFxSumForecastPriceIdr ?? []);
    const chartFxSumActualPriceIdr    = @json($chartFxSumActualPriceIdr ?? []);

    const chartFxIncomingStatus       = @json($chartFxIncomingStatus ?? []);
    const chartFxBudgetRates          = @json($chartFxBudgetRates ?? []);
    const chartFxActualRates          = @json($chartFxActualRates ?? []);

    // ── FX CHART STATE & SWITCHERS (3-TIER MATRIX) ──
    window.currentFxFocus     = 'amount'; // 'amount' (Belanja) | 'price' (Harga Satuan)
    window.currentFxAgg       = 'sum';    // 'sum' | 'avg'
    window.currentFxCurr      = 'usd';    // 'usd' ($) | 'idr' (Rp)
    window.currentFxChartMode = 'amount_sum_usd';
    window.chartFxInstance    = null;

    window.setFxMetricFocus = function(focus) {
        window.currentFxFocus = focus || 'amount';
        document.getElementById('btnFxFocusAmount')?.classList.toggle('active', window.currentFxFocus === 'amount');
        document.getElementById('btnFxFocusPrice')?.classList.toggle('active', window.currentFxFocus === 'price');
        applyCombinedFxMode();
    };

    window.setFxAggregation = function(agg) {
        window.currentFxAgg = agg || 'sum';
        document.getElementById('btnFxAggSum')?.classList.toggle('active', window.currentFxAgg === 'sum');
        document.getElementById('btnFxAggAvg')?.classList.toggle('active', window.currentFxAgg === 'avg');
        applyCombinedFxMode();
    };

    window.setFxCurrencyMode = function(curr) {
        window.currentFxCurr = curr || 'usd';
        document.getElementById('btnFxCurrUsd')?.classList.toggle('active', window.currentFxCurr === 'usd');
        document.getElementById('btnFxCurrIdr')?.classList.toggle('active', window.currentFxCurr === 'idr');
        applyCombinedFxMode();
    };

    function applyCombinedFxMode() {
        const mode = `${window.currentFxFocus}_${window.currentFxAgg}_${window.currentFxCurr}`;
        window.switchFxChartMode(mode);
    }

    // Custom plugin to render data labels directly on chart points safely in Chart.js v4
    const valueLabelsPlugin = {
        id: 'valueLabelsPlugin',
        afterDatasetsDraw(chart) {
            if (!chart || !chart.canvas || chart.canvas.id !== 'chartExchangeRateComparisonArea') return;
            const { ctx } = chart;
            ctx.save();

            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                if (!meta || meta.hidden) return;

                meta.data.forEach((element, index) => {
                    const val = dataset.data[index];
                    if (val === null || val === undefined) return;
                    if (!element || typeof element.x !== 'number' || typeof element.y !== 'number') return;

                    const isForecast = datasetIndex === 0;
                    const mode = window.currentFxChartMode;
                    let text = '';
                    if (mode.includes('price')) {
                        if (mode.endsWith('usd')) {
                            text = '$' + Number(val || 0).toFixed(2) + '/u';
                        } else {
                            text = 'Rp ' + Math.round(val || 0).toLocaleString('id-ID') + '/u';
                        }
                    } else {
                        if (mode.endsWith('usd')) {
                            if (val >= 1000000) text = '$' + (val / 1000000).toFixed(2) + 'M';
                            else if (val >= 1000) text = '$' + (val / 1000).toFixed(1) + 'k';
                            else text = '$' + Number(val || 0).toFixed(0);
                        } else {
                            if (val >= 1000000000) text = 'Rp ' + (val / 1000000000).toFixed(2) + ' M';
                            else if (val >= 1000000) text = 'Rp ' + (val / 1000000).toFixed(1) + ' Jt';
                            else text = 'Rp ' + Math.round(val || 0).toLocaleString('id-ID');
                        }
                    }

                    const x = element.x;
                    const y = element.y;
                    const textY = isForecast ? (y - 14) : (y + 14);

                    ctx.font = '700 10px Outfit, sans-serif';
                    ctx.textAlign = 'center';

                    const textWidth = ctx.measureText(text).width;
                    const padX = 4;
                    const rectW = textWidth + (padX * 2);
                    const rectH = 14;
                    const rectX = x - (rectW / 2);
                    const rectY = textY - 10;

                    ctx.fillStyle = 'rgba(10, 14, 26, 0.90)';
                    ctx.strokeStyle = isForecast ? 'rgba(0, 210, 255, 0.85)' : 'rgba(16, 185, 129, 0.85)';
                    ctx.lineWidth = 1;

                    ctx.beginPath();
                    if (typeof ctx.roundRect === 'function') {
                        ctx.roundRect(rectX, rectY, rectW, rectH, 4);
                    } else {
                        ctx.rect(rectX, rectY, rectW, rectH);
                    }
                    ctx.fill();
                    ctx.stroke();

                    ctx.fillStyle = isForecast ? '#00d2ff' : '#34d399';
                    ctx.fillText(text, x, textY);
                });
            });

            ctx.restore();
        }
    };

    window.updateInsightPills = function(mode) {
        if (!window.comparisonMonthlyInsights || !Array.isArray(window.comparisonMonthlyInsights)) return;
        const isPrice = mode.includes('price');
        window.comparisonMonthlyInsights.forEach((cmi, idx) => {
            const badgeEl = document.getElementById(`cmiPillBadge_${idx}`);
            if (!badgeEl) return;
            if (cmi.is_first_month) {
                badgeEl.innerText = 'Base';
                badgeEl.className = 'badge bg-secondary bg-opacity-50 text-light';
                return;
            }
            if (isPrice) {
                const pPct = Number(cmi.mom_fc_price_pct || 0);
                if (cmi.data_status === 'NO_DATA') {
                    badgeEl.innerText = '0%';
                    badgeEl.className = 'badge bg-secondary bg-opacity-25 text-muted';
                } else if (pPct > 0) {
                    badgeEl.innerHTML = `<i class="bi bi-arrow-up-short"></i>+${pPct.toFixed(1)}%`;
                    badgeEl.className = 'badge bg-danger bg-opacity-25 text-danger';
                } else if (pPct < 0) {
                    badgeEl.innerHTML = `<i class="bi bi-arrow-down-short"></i>${pPct.toFixed(1)}%`;
                    badgeEl.className = 'badge bg-success bg-opacity-25 text-success';
                } else {
                    badgeEl.innerText = '0%';
                    badgeEl.className = 'badge bg-secondary bg-opacity-25 text-muted';
                }
            } else {
                const aPct = Number(cmi.mom_fc_amount_pct || 0);
                if (cmi.data_status === 'NO_DATA') {
                    badgeEl.innerText = '0%';
                    badgeEl.className = 'badge bg-secondary bg-opacity-25 text-muted';
                } else if (aPct > 0) {
                    badgeEl.innerHTML = `<i class="bi bi-arrow-up-short"></i>+${aPct.toFixed(1)}%`;
                    badgeEl.className = 'badge bg-success bg-opacity-25 text-success';
                } else if (aPct < 0) {
                    badgeEl.innerHTML = `<i class="bi bi-arrow-down-short"></i>${aPct.toFixed(1)}%`;
                    badgeEl.className = 'badge bg-danger bg-opacity-25 text-danger';
                } else {
                    badgeEl.innerText = '0%';
                    badgeEl.className = 'badge bg-secondary bg-opacity-25 text-muted';
                }
            }
        });
    };

    window.switchFxChartMode = function(mode) {
        window.currentFxChartMode = mode || 'amount_sum_usd';

        // Parse mode components
        const parts = mode.split('_');
        if (parts.length >= 3) {
            window.currentFxFocus = parts[0];
            window.currentFxAgg   = parts[1];
            window.currentFxCurr  = parts[2];
        }

        // Update button states
        document.getElementById('btnFxFocusAmount')?.classList.toggle('active', window.currentFxFocus === 'amount');
        document.getElementById('btnFxFocusPrice')?.classList.toggle('active', window.currentFxFocus === 'price');
        document.getElementById('btnFxAggSum')?.classList.toggle('active', window.currentFxAgg === 'sum');
        document.getElementById('btnFxAggAvg')?.classList.toggle('active', window.currentFxAgg === 'avg');
        document.getElementById('btnFxCurrUsd')?.classList.toggle('active', window.currentFxCurr === 'usd');
        document.getElementById('btnFxCurrIdr')?.classList.toggle('active', window.currentFxCurr === 'idr');

        // Select proper datasets based on the 8 combinations
        let dataA = [];
        let dataB = [];
        let titleHtml = '';
        let infoTextHtml = '';
        let infoSubHtml = '';
        const isDollar = window.currentFxCurr === 'usd';

        if (window.currentFxFocus === 'amount') {
            if (window.currentFxAgg === 'sum') {
                dataA = isDollar ? chartFxForecastAmountUsd : chartFxForecastAmountIdr;
                dataB = isDollar ? chartFxActualAmountUsd   : chartFxActualAmountIdr;
                titleHtml = `<i class="bi bi-wallet2 text-info me-1"></i> DIAGRAM KOMPARASI TOTAL BELANJA (SUM AMOUNT - ${isDollar ? 'USD $' : 'RUPIAH IDR'})`;
                infoTextHtml = `<strong>Mode Aktif: SUM Amount (${isDollar ? 'USD' : 'IDR'})</strong> — Menampilkan <em>Total Nilai Pengadaan</em> (Σ Qty × Price).`;
                infoSubHtml = `Evaluasi makro total pengeluaran belanja pengadaan material per bulan.`;
            } else {
                dataA = isDollar ? chartFxAvgForecastAmountUsd : chartFxAvgForecastAmountIdr;
                dataB = isDollar ? chartFxAvgActualAmountUsd   : chartFxAvgActualAmountIdr;
                titleHtml = `<i class="bi bi-calculator text-warning me-1"></i> DIAGRAM KOMPARASI RATA-RATA BELANJA PER MATERIAL (AVG AMOUNT / MATERIAL - ${isDollar ? 'USD $' : 'RUPIAH IDR'})`;
                infoTextHtml = `<strong>Mode Aktif: AVG Amount / Material (${isDollar ? 'USD' : 'IDR'})</strong> — Menampilkan <em>Rata-Rata Nilai Belanja per Material</em> (Total Belanja / Item Count).`;
                infoSubHtml = `Evaluasi intensitas pengeluaran per SKU/part number material.`;
            }
        } else {
            if (window.currentFxAgg === 'avg') {
                dataA = isDollar ? chartFxForecastPriceUsd : chartFxForecastPriceIdr;
                dataB = isDollar ? chartFxActualPriceUsd   : chartFxActualPriceIdr;
                titleHtml = `<i class="bi bi-tag-fill text-success me-1"></i> DIAGRAM KOMPARASI HARGA SATUAN TERTIMBANG (WEIGHTED AVG PRICE - ${isDollar ? 'USD $/PCS' : 'RUPIAH RP/PCS'})`;
                infoTextHtml = `<strong>Mode Aktif: Weighted AVG Price (${isDollar ? 'USD/PCS' : 'Rp/PCS'})</strong> — Menampilkan <em>Rata-Rata Harga Satuan Tertimbang</em> (Σ[Qty × Price] / ΣQty).`;
                infoSubHtml = `Indikator KPI utama efisiensi harga beli per satuan piece material piano.`;
            } else {
                dataA = isDollar ? chartFxSumForecastPriceUsd : chartFxSumForecastPriceIdr;
                dataB = isDollar ? chartFxSumActualPriceUsd   : chartFxSumActualPriceIdr;
                titleHtml = `<i class="bi bi-tags text-primary me-1"></i> DIAGRAM KOMPARASI AKUMULASI PRICE LIST (SUM PRICE - ${isDollar ? 'USD $' : 'RUPIAH IDR'})`;
                infoTextHtml = `<strong>Mode Aktif: SUM Price (${isDollar ? 'USD' : 'IDR'})</strong> — Menampilkan <em>Jumlah Akumulasi Harga Satuan Katalog</em> (Analytical Metric).`;
                infoSubHtml = `Menjumlahkan harga price list seluruh item aktif untuk analisis variasi katalog.`;
            }
        }

        dataA = window.adjustLineData(dataA);
        dataB = window.adjustLineData(dataB);

        // Update Info Banner & Title
        const titleEl = document.getElementById('fxChartTitleLabel');
        if (titleEl) titleEl.innerHTML = titleHtml;
        const infoTextEl = document.getElementById('fxModeInfoText');
        if (infoTextEl) infoTextEl.innerHTML = infoTextHtml;
        const infoSubEl  = document.getElementById('fxModeInfoSub');
        if (infoSubEl) infoSubEl.innerHTML = infoSubHtml;

        // Update pills dynamically to match current chart mode
        window.updateInsightPills(window.currentFxChartMode);

        const validNums = [...dataA, ...dataB].filter(v => v !== null && v !== undefined && !isNaN(v) && v > 0).map(v => Number(v));
        let yMax = undefined;
        if (validNums.length > 0) {
            const maxVal = Math.max(...validNums);
            yMax = Math.ceil(maxVal * 1.18);
        }

        const chartDatasets = [
            {
                label: 'Forecast (Kurs Budget)',
                data: dataA,
                borderColor: '#00d2ff',
                backgroundColor: 'rgba(0, 210, 255, 0.15)',
                fill: true,
                tension: 0.35,
                borderWidth: 2.5,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#00d2ff',
                spanGaps: true,
            },
            {
                label: 'Realisasi Incoming PO (Transaksi Berjalan Aktual)',
                data: dataB,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.10)',
                fill: true,
                tension: 0.35,
                borderWidth: 2.5,
                spanGaps: false,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
            }
        ];

        const isPrice = window.currentFxFocus === 'price';
        const chartScales = {
            x: { ticks: { color: '#94a3b8', font: { family: 'Outfit', weight: 'bold' } }, grid: { color: 'rgba(255,255,255,0.05)' } },
            y: {
                beginAtZero: true,
                ...(yMax !== undefined && { max: yMax }),
                ticks: {
                    color: '#94a3b8',
                    font: { family: 'Outfit', weight: 'bold' },
                    callback: function(val) {
                        if (isDollar) {
                            if (isPrice) return '$ ' + Number(val).toFixed(2);
                            if (val >= 1000000) return '$ ' + (val / 1000000).toFixed(1) + 'M';
                            if (val >= 1000) return '$ ' + (val / 1000).toFixed(0) + 'k';
                            return '$ ' + Number(val).toLocaleString('en-US');
                        } else {
                            if (isPrice) return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                            if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' M';
                            if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(0) + ' Jt';
                            return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                        }
                    }
                },
                grid: { color: 'rgba(255,255,255,0.05)' }
            }
        };

        const canvasEl = document.getElementById('chartExchangeRateComparisonArea');
        if (!canvasEl) return;
        const ctxFx = canvasEl.getContext('2d');
        if (!ctxFx) return;

        if (window.chartFxInstance) {
            window.chartFxInstance.destroy();
            window.chartFxInstance = null;
        }

        try {
            window.chartFxInstance = new Chart(ctxFx, {
                type: 'line',
                data: {
                    labels: chartFxLabels,
                    datasets: chartDatasets
                },
                plugins: [valueLabelsPlugin],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    onHover: function(event, chartElements) {
                        if (event.native && event.native.target) {
                            event.native.target.style.cursor = (chartElements && chartElements.length > 0) ? 'pointer' : 'default';
                        }
                    },
                    onClick: function(event, elements) {
                        if (!elements || elements.length === 0) return;
                        const index = elements[0].index;
                        if (typeof window.openMonthlyInsightModal === 'function') {
                            window.openMonthlyInsightModal(index);
                        }
                    },
                    plugins: {
                        legend: { labels: { color: '#cbd5e1', font: { family: 'Outfit', weight: 600 } } },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            borderColor: 'rgba(0, 210, 255, 0.3)',
                            borderWidth: 1,
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            callbacks: {
                                label: function(ctx) {
                                    const hasIncoming = chartFxIncomingStatus[ctx.dataIndex];
                                    const val = ctx.raw;
                                    const bRate = chartFxBudgetRates[ctx.dataIndex] || 16600;
                                    const aRate = chartFxActualRates[ctx.dataIndex] || bRate;
                                    const isForecast = ctx.datasetIndex === 0;

                                    if (!isForecast && (val === null || val === undefined || !hasIncoming)) {
                                        return ctx.dataset.label + ': Belum Ada Realisasi (Pending / Belum Ada Transaksi)';
                                    }

                                    if (val === null || val === undefined) return ctx.dataset.label + ': —';

                                    const rateText = isForecast 
                                        ? ` (Kurs Budget: Rp ${bRate.toLocaleString('id-ID')})` 
                                        : ` (Kurs Realisasi: Rp ${aRate.toLocaleString('id-ID')})`;

                                    const numVal = Number(val);
                                    if (isPrice) {
                                        if (isDollar) {
                                            return ctx.dataset.label + ': $ ' + numVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' / PCS' + rateText;
                                        } else {
                                            return ctx.dataset.label + ': Rp ' + Math.round(numVal).toLocaleString('id-ID') + ' / PCS' + rateText;
                                        }
                                    } else {
                                        if (isDollar) {
                                            return ctx.dataset.label + ': $ ' + numVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + rateText;
                                        } else {
                                            return ctx.dataset.label + ': Rp ' + Math.round(numVal).toLocaleString('id-ID') + rateText;
                                        }
                                    }
                                }
                            }
                        }
                    },
                    scales: chartScales
                }
            });
        } catch (e) {
            console.error('Error rendering FX Chart:', e);
        }
    };

    window.setTableCurrencyDisplay = function(mode) {
        const tbl = document.getElementById('tableFxComparison');
        const btnUsd = document.getElementById('btnTblMode_usd');
        const btnIdr = document.getElementById('btnTblMode_idr');

        if (mode === 'idr') {
            tbl?.classList.remove('currency-mode-usd');
            tbl?.classList.add('currency-mode-idr');
            btnIdr?.classList.add('active');
            btnUsd?.classList.remove('active');
        } else {
            tbl?.classList.remove('currency-mode-idr');
            tbl?.classList.add('currency-mode-usd');
            btnUsd?.classList.add('active');
            btnIdr?.classList.remove('active');
        }
    };

    // Auto-initialize FX Chart immediately and on events
    setTimeout(() => window.switchFxChartMode('amount_sum_usd'), 50);
    document.addEventListener('DOMContentLoaded', () => window.switchFxChartMode('amount_sum_usd'));
    window.addEventListener('load', () => window.switchFxChartMode('amount_sum_usd'));

    const monthsLabels        = @json($monthsLabels);
    const chartForecastPo     = @json($chartForecastPo);
    const chartActualPo       = @json($chartActualPo);
    const chartForecastStock  = @json($chartForecastStock);
    const chartActualStock    = @json($chartActualStock);
    const chartForecastAmount = @json($chartForecastAmount ?? []);
    const chartActualAmount   = @json($chartActualAmount ?? []);
    const chartForecastPrice  = @json($chartForecastPrice ?? []);
    const chartActualPrice    = @json($chartActualPrice ?? []);

    const chartPerItem        = @json($chartPerItem ?? []);
    const chartPerPo          = @json($chartPerPo ?? []);

    // Limit elements to the actual monitor duration + month 0
    const duration = {{ $duration }};
    const slicedLabels          = monthsLabels.slice(1, duration + 1);
    const slicedForecastPo      = chartForecastPo.slice(1, duration + 1);
    const slicedActualPo        = chartActualPo.slice(1, duration + 1);
    const slicedForecastStock   = chartForecastStock.slice(1, duration + 1);
    const slicedActualStock     = chartActualStock.slice(1, duration + 1);
    const slicedForecastAmount  = chartForecastAmount.slice(1, duration + 1);
    const slicedActualAmount    = chartActualAmount.slice(1, duration + 1);
    const slicedForecastPrice   = chartForecastPrice.slice(1, duration + 1);
    const slicedActualPrice     = chartActualPrice.slice(1, duration + 1);

    let mainChartInstance = null;
    let currentDimension = 'qty';
    let currentChartType = 'bar';

    let activeChartItemFilter = '{{ $selectedItemCode ?? 'ALL' }}';
    let activeChartPoFilter = '{{ $selectedPo ?? 'ALL' }}';

    function updateChartFilter(val, filterType) {
        if (filterType === 'item') {
            activeChartItemFilter = val;
            if (val !== 'ALL') {
                const poSelect = document.getElementById('chartPoSelect');
                if (poSelect) poSelect.value = 'ALL';
                activeChartPoFilter = 'ALL';
            }
        } else if (filterType === 'po') {
            activeChartPoFilter = val;
            if (val !== 'ALL') {
                const itemSelect = document.getElementById('chartItemCodeSelect');
                if (itemSelect) itemSelect.value = 'ALL';
                activeChartItemFilter = 'ALL';
            }
        }
        renderMainChart(currentDimension, currentChartType);
    }

    // Helper currency formatting
    function formatCurrencyVal(val) {
        if (val === null || val === undefined) return '$ -';
        if (val < 0) return '$ (' + Math.abs(val).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
        return '$ ' + val.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Chart 1: Main Dynamic Dimension Comparison Chart (QTY / Stock / Amount / Price)
    const ctxPo = document.getElementById('chartPoComparison')?.getContext('2d');

    function renderMainChart(dim, type) {
        currentDimension = dim || currentDimension;
        currentChartType = type || currentChartType;

        let chartTitle = 'Komparasi Order PO: Forecast vs Aktual';
        let labelA = 'Forecast PO';
        let labelB = 'Actual PO Received';
        let isCurrency = false;

        // Check if specific Item Code or PO is selected in chart filter
        let datasetGroup = null;
        if (activeChartItemFilter && activeChartItemFilter !== 'ALL' && chartPerItem[activeChartItemFilter]) {
            datasetGroup = chartPerItem[activeChartItemFilter];
            chartTitle = `[Item ${activeChartItemFilter}] Komparasi Order PO: Forecast vs Aktual`;
        } else if (activeChartPoFilter && activeChartPoFilter !== 'ALL' && chartPerPo[activeChartPoFilter]) {
            datasetGroup = chartPerPo[activeChartPoFilter];
            chartTitle = `[PO ${activeChartPoFilter}] Komparasi Order PO: Forecast vs Aktual`;
        }

        let dataA = [];
        let dataB = [];

        if (currentDimension === 'stock') {
            chartTitle = datasetGroup ? chartTitle.replace('Order PO', 'Level Stok') : 'Tren Level Stok: Forecast vs Aktual';
            labelA = 'Forecast Stock';
            labelB = 'Actual Stock';
            dataA = datasetGroup ? datasetGroup.forecast_stock.slice(1, duration + 1) : slicedForecastStock;
            dataB = datasetGroup ? datasetGroup.actual_stock.slice(1, duration + 1) : slicedActualStock;
        } else if (currentDimension === 'amount') {
            chartTitle = datasetGroup ? chartTitle.replace('Order PO', 'Total Amount ($)') : 'Komparasi Total Amount ($): Forecast vs Aktual';
            labelA = 'Forecast Amount ($)';
            labelB = 'Actual Amount ($)';
            dataA = datasetGroup ? datasetGroup.forecast_amount.slice(1, duration + 1) : slicedForecastAmount;
            dataB = datasetGroup ? datasetGroup.actual_amount.slice(1, duration + 1) : slicedActualAmount;
            isCurrency = true;
        } else if (currentDimension === 'price') {
            chartTitle = datasetGroup ? chartTitle.replace('Order PO', 'Harga Unit ($ / Unit)') : 'Tren Harga Unit ($ / Unit): Forecast vs Aktual';
            labelA = 'Forecast Price ($)';
            labelB = 'Actual Price ($)';
            dataA = datasetGroup ? datasetGroup.forecast_price.slice(1, duration + 1) : slicedForecastPrice;
            dataB = datasetGroup ? datasetGroup.actual_price.slice(1, duration + 1) : slicedActualPrice;
            isCurrency = true;
        } else {
            dataA = datasetGroup ? datasetGroup.forecast_po.slice(1, duration + 1) : slicedForecastPo;
            dataB = datasetGroup ? datasetGroup.actual_po.slice(1, duration + 1) : slicedActualPo;
        }

        const titleEl = document.getElementById('chartMainTitle');
        if (titleEl) {
            titleEl.innerHTML = `<i class="bi bi-bar-chart-line text-warning me-2"></i>${chartTitle}`;
        }

        if (mainChartInstance) {
            mainChartInstance.destroy();
            mainChartInstance = null;
        }

        const canvasPo = document.getElementById('chartPoComparison');
        if (!canvasPo) return;
        const ctxPo = canvasPo.getContext('2d');
        if (!ctxPo) return;

        let jsChartType = 'bar';
        let isFill = false;

        if (currentChartType === 'line') {
            jsChartType = 'line';
            isFill = false;
        } else if (currentChartType === 'area') {
            jsChartType = 'line';
            isFill = true;
        } else if (currentChartType === 'doughnut') {
            jsChartType = 'doughnut';
        } else if (currentChartType === 'pie') {
            jsChartType = 'pie';
        } else {
            jsChartType = 'bar';
        }

        if (jsChartType === 'line') {
            dataB = window.adjustLineData ? window.adjustLineData(dataB) : dataB;
        }

        let chartConfig = {};

        if (jsChartType === 'doughnut' || jsChartType === 'pie') {
            chartConfig = {
                type: jsChartType,
                data: {
                    labels: slicedLabels,
                    datasets: [
                        {
                            label: labelA,
                            data: dataA,
                            backgroundColor: [
                                '#3b82f6', '#60a5fa', '#93c5fd', '#1d4ed8', '#2563eb',
                                '#38bdf8', '#0284c7', '#0ea5e9', '#0284c7', '#0369a1',
                                '#6366f1', '#4f46e5', '#4338ca'
                            ],
                            borderWidth: 1.5,
                            borderColor: '#1e293b'
                        },
                        {
                            label: labelB,
                            data: dataB,
                            backgroundColor: [
                                '#10b981', '#34d399', '#6ee7b7', '#047857', '#059669',
                                '#14b8a6', '#0d9488', '#0f766e', '#10b981', '#34d399',
                                '#059669', '#047857', '#0f766e'
                            ],
                            borderWidth: 1.5,
                            borderColor: '#1e293b'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { color: '#f3f4f6', font: { family: 'Inter', size: 11, weight: 600 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let val = context.parsed;
                                    if (isCurrency) {
                                        return context.dataset.label + ' (' + context.label + '): ' + formatCurrencyVal(val);
                                    }
                                    return context.dataset.label + ' (' + context.label + '): ' + (val || 0).toLocaleString('de-DE') + ' unit';
                                }
                            }
                        }
                    }
                }
            };
        } else {
            // Dynamic Y-axis scale bounds for Price dimension to magnify small variations (e.g. $80,40 vs $80,20)
            let beginAtZeroVal = true;
            let yMinVal = undefined;
            let yMaxVal = undefined;

            if (currentDimension === 'price') {
                let validPrices = [...dataA, ...dataB].filter(v => v !== null && v !== undefined && v > 0);
                if (validPrices.length > 0) {
                    beginAtZeroVal = false;
                    let minP = Math.min(...validPrices);
                    let maxP = Math.max(...validPrices);
                    let margin = Math.max(5, (maxP - minP) * 0.3);
                    yMinVal = Math.max(0, Math.floor(minP - margin));
                    yMaxVal = Math.ceil(maxP + margin);
                }
            }

            chartConfig = {
                type: jsChartType,
                data: {
                    labels: slicedLabels,
                    datasets: [
                        {
                            label: labelA,
                            data: dataA,
                            backgroundColor: jsChartType === 'line' ? (isFill ? 'rgba(59, 130, 246, 0.18)' : 'transparent') : 'rgba(59, 130, 246, 0.75)',
                            borderColor: '#3b82f6',
                            borderDash: jsChartType === 'line' ? [6, 4] : [],
                            borderWidth: jsChartType === 'line' ? 3 : 1.5,
                            borderRadius: jsChartType === 'bar' ? 6 : 0,
                            pointStyle: 'circle',
                            pointRadius: jsChartType === 'line' ? 6 : 0,
                            pointHoverRadius: 8,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1.5,
                            tension: 0.25,
                            fill: isFill
                        },
                        {
                            label: labelB,
                            data: dataB,
                            backgroundColor: jsChartType === 'line' ? (isFill ? 'rgba(16, 185, 129, 0.35)' : 'transparent') : 'rgba(16, 185, 129, 0.85)',
                            borderColor: '#10b981',
                            borderDash: [],
                            borderWidth: jsChartType === 'line' ? 3.5 : 1.5,
                            borderRadius: jsChartType === 'bar' ? 6 : 0,
                            pointStyle: 'rectRot',
                            pointRadius: jsChartType === 'line' ? 7 : 0,
                            pointHoverRadius: 9,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1.5,
                            tension: 0.25,
                            fill: isFill,
                            spanGaps: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#f3f4f6', font: { family: 'Inter', weight: 600 } } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let val = context.parsed.y;
                                    if (isCurrency) {
                                        return context.dataset.label + ': ' + formatCurrencyVal(val);
                                    }
                                    return context.dataset.label + ': ' + (val || 0).toLocaleString('de-DE') + ' unit';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: '#9ca3af', font: { family: 'Outfit', weight: 'bold' } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        y: {
                            min: yMinVal,
                            max: yMaxVal,
                            beginAtZero: beginAtZeroVal,
                            ticks: {
                                color: '#9ca3af',
                                callback: function(value) {
                                    if (isCurrency) {
                                        return '$ ' + value.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    }
                                    return value.toLocaleString('de-DE');
                                }
                            },
                            grid: { color: 'rgba(255,255,255,0.05)' }
                        }
                    }
                }
            };
        }

        mainChartInstance = new Chart(ctxPo, chartConfig);
        window.mainChartInstance = mainChartInstance;
    }

    // Switch chart dimension dynamically
    function switchChartDimension(dim) {
        document.querySelectorAll('#chartDimensionGroup button').forEach(btn => btn.classList.remove('active'));
        if (dim === 'qty') document.getElementById('btnChartQty')?.classList.add('active');
        if (dim === 'stock') document.getElementById('btnChartStock')?.classList.add('active');
        if (dim === 'amount') document.getElementById('btnChartAmount')?.classList.add('active');
        if (dim === 'price') document.getElementById('btnChartPrice')?.classList.add('active');

        renderMainChart(dim, currentChartType);
    }

    // Switch chart visual type dynamically (Bar, Line, Area, Donut, Pie)
    function switchChartType(type) {
        document.querySelectorAll('#chartTypeGroup button').forEach(btn => btn.classList.remove('active'));
        if (type === 'bar') document.getElementById('btnTypeBar')?.classList.add('active');
        if (type === 'line') document.getElementById('btnTypeLine')?.classList.add('active');
        if (type === 'area') document.getElementById('btnTypeArea')?.classList.add('active');
        if (type === 'doughnut') document.getElementById('btnTypeDoughnut')?.classList.add('active');
        if (type === 'pie') document.getElementById('btnTypePie')?.classList.add('active');

        renderMainChart(currentDimension, type);
    }

    // Helper ESCAPE HTML
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Initialize main chart on load
    renderMainChart('qty', 'bar');

    // 🏆 TABEL TOP 10 RANKING LOGIC
    const top10ItemsData = @json($top10ItemsData ?? []);
    let currentTop10Criterion = 'amount';

    function switchTop10Criteria(criterion) {
        document.querySelectorAll('#top10CriteriaGroup button').forEach(btn => btn.classList.remove('active'));
        if (criterion === 'amount') document.getElementById('btnTopAmount')?.classList.add('active');
        if (criterion === 'po') document.getElementById('btnTopPo')?.classList.add('active');
        if (criterion === 'actual') document.getElementById('btnTopActual')?.classList.add('active');
        if (criterion === 'price') document.getElementById('btnTopPrice')?.classList.add('active');

        renderTop10Table(criterion);
    }

    function renderTop10Table(criterion) {
        currentTop10Criterion = criterion || 'amount';
        const tbody = document.getElementById('top10TableBody');
        const subtitleEl = document.getElementById('top10CardSubtitle');
        if (!tbody) return;

        let subtitleText = 'Urutan 10 item code teratas berdasarkan kriteria terpilih';
        if (criterion === 'amount') subtitleText = '10 Item Code dengan Total Amount ($) Tertinggi';
        if (criterion === 'po') subtitleText = '10 Item Code dengan Target Qty PO Tertinggi';
        if (criterion === 'actual') subtitleText = '10 Item Code dengan Realisasi Qty Penerimaan Tertinggi';
        if (criterion === 'price') subtitleText = '10 Item Code dengan Harga Unit ($) Tertinggi';

        if (subtitleEl) subtitleEl.innerText = subtitleText;

        let sorted = [...top10ItemsData];
        if (criterion === 'amount') {
            sorted.sort((a, b) => (b.total_amount || 0) - (a.total_amount || 0));
        } else if (criterion === 'po') {
            sorted.sort((a, b) => (b.sum_po_qty || 0) - (a.sum_po_qty || 0));
        } else if (criterion === 'actual') {
            sorted.sort((a, b) => (b.sum_actual_qty || 0) - (a.sum_actual_qty || 0));
        } else if (criterion === 'price') {
            sorted.sort((a, b) => (b.price || 0) - (a.price || 0));
        }

        const top10 = sorted.slice(0, 10);

        if (top10.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-3 d-block mb-1"></i>Tidak ada data item code.</td></tr>`;
            return;
        }

        let html = '';
        top10.forEach((item, idx) => {
            let rankBadge = '';
            if (idx === 0) rankBadge = `<span class="badge bg-warning text-dark font-monospace fw-bold px-2 py-1">🥇 1</span>`;
            else if (idx === 1) rankBadge = `<span class="badge bg-secondary text-white font-monospace fw-bold px-2 py-1">🥈 2</span>`;
            else if (idx === 2) rankBadge = `<span class="badge text-white font-monospace fw-bold px-2 py-1" style="background:#b45309;">🥉 3</span>`;
            else rankBadge = `<span class="badge bg-dark border border-secondary text-muted font-monospace px-2 py-1">#${idx + 1}</span>`;

            const poClass = criterion === 'po' ? 'bg-primary bg-opacity-25 text-primary fw-bold' : 'text-light';
            const actualClass = criterion === 'actual' ? 'bg-info bg-opacity-25 text-info fw-bold' : 'text-light';
            const priceClass = criterion === 'price' ? 'bg-warning bg-opacity-25 text-warning fw-bold' : 'text-warning';
            const amountClass = criterion === 'amount' ? 'bg-success bg-opacity-25 text-success fw-bold' : 'text-success';

            const itemCodeEsc = escapeHtml(item.item_code);
            const descEsc = escapeHtml(item.description);
            const suppEsc = escapeHtml(item.supplier);

            html += `<tr>
                <td class="text-center">${rankBadge}</td>
                <td>
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2.5 py-0.5 font-monospace fw-bold text-nowrap" onclick="jumpToItemGrid('${itemCodeEsc}')">
                        ${itemCodeEsc} <i class="bi bi-arrow-down-short"></i>
                    </button>
                </td>
                <td>
                    <div class="fw-bold text-white fs-7 text-truncate" style="max-width: 170px;" title="${descEsc}">${descEsc}</div>
                    <div class="text-muted fs-8 text-truncate" style="max-width: 170px;" title="${suppEsc}">${suppEsc}</div>
                </td>
                <td class="text-end font-monospace ${poClass}">${Number(item.sum_po_qty || 0).toLocaleString('de-DE')}</td>
                <td class="text-end font-monospace ${actualClass}">${Number(item.sum_actual_qty || 0).toLocaleString('de-DE')}</td>
                <td class="text-end font-monospace ${priceClass}">$ ${Number(item.price || 0).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-end font-monospace ${amountClass}">${formatCurrencyVal(Number(item.total_amount || 0))}</td>
            </tr>`;
        });

        tbody.innerHTML = html;
    }

    // Initialize Top 10 Table on load
    renderTop10Table('amount');

    // Chart 3: Outstanding PO Qty vs Receipt Comparison
    const outstandingPoChartData = @json($outstandingPoChartData);
    const poLabels = outstandingPoChartData.map(item => `${item.po} (${item.item_code})`);
    const poQtys = outstandingPoChartData.map(item => item.qty_po);
    const poReceipts = outstandingPoChartData.map(item => item.qty_receipt);

    const ctxOutPo = document.getElementById('chartOutstandingPoComparison')?.getContext('2d');
    if (ctxOutPo) {
        new Chart(ctxOutPo, {
            type: 'bar',
            data: {
                labels: poLabels,
                datasets: [
                    {
                        label: 'Qty PO (Order Qty)',
                        data: poQtys,
                        backgroundColor: 'rgba(245, 158, 11, 0.75)',
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        borderRadius: 6
                    },
                    {
                        label: 'Qty Receipt (Received Qty)',
                        data: poReceipts,
                        backgroundColor: 'rgba(16, 185, 129, 0.75)',
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#f3f4f6', font: { family: 'Inter', weight: 600 } } }
                },
                scales: {
                    x: { ticks: { color: '#9ca3af', font: { family: 'Outfit', weight: 'bold' } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
                }
            }
        });
    }

    /**
     * Interactive Price Deviation Modal Trigger with per-PO Breakdown
     */
    /**
     * Interactive Price Deviation Modal Trigger with per-PO Breakdown
     */
    function showPriceReasonModal(btnEl, itemCode, id, actualPrice, forecastPrice, currentReason) {
        const form = document.getElementById('formPriceReason');
        if (form) {
            form.action = `/purchasing/outstanding/${id}`;
        }
        document.getElementById('modal_price_item_code').innerText = itemCode;
        
        const aPrice = parseFloat(actualPrice) || 0;
        const fPrice = parseFloat(forecastPrice) || 0;

        document.getElementById('modal_price_forecast').innerText = '$ ' + fPrice.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('modal_price_actual').innerText = '$ ' + aPrice.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const diff = aPrice - fPrice;
        const pct = fPrice > 0 ? ((diff / fPrice) * 100).toFixed(1) : 0;
        const diffBadge = document.getElementById('modal_price_diff');
        if (diff > 0) {
            diffBadge.className = 'badge bg-danger text-white font-monospace';
            diffBadge.innerText = `Rata-Rata Naik +$ ${diff.toFixed(2)} (+${pct}%)`;
        } else if (diff < 0) {
            diffBadge.className = 'badge bg-success text-white font-monospace';
            diffBadge.innerText = `Rata-Rata Turun -$ ${Math.abs(diff).toFixed(2)} (${pct}%)`;
        } else {
            diffBadge.className = 'badge bg-secondary text-white font-monospace';
            diffBadge.innerText = 'Sama (Sesuai Forecast)';
        }

        document.getElementById('modal_price_reason_input').value = currentReason || '';

        // Render per-PO price breakdown safely from base64 data-details attribute (with data-all-details fallback)
        const tbody = document.getElementById('modal_price_po_breakdown');
        if (tbody) {
            let details = [];
            if (btnEl && typeof btnEl.getAttribute === 'function') {
                const rawB64 = btnEl.getAttribute('data-details') || '';
                if (rawB64) {
                    try {
                        const jsonStr = atob(rawB64);
                        details = JSON.parse(jsonStr);
                    } catch(e) {
                        console.error('Failed to decode data-details:', e);
                        details = [];
                    }
                }
                if (!Array.isArray(details) || details.length === 0) {
                    const rawAllB64 = btnEl.getAttribute('data-all-details') || '';
                    if (rawAllB64) {
                        try {
                            const jsonStr = atob(rawAllB64);
                            details = JSON.parse(jsonStr);
                        } catch(e) {
                            console.error('Failed to decode data-all-details:', e);
                            details = [];
                        }
                    }
                }
            }

            if (Array.isArray(details) && details.length > 0) {
                let html = '';
                details.forEach(det => {
                    const poRef = escapeHtml(det.po_reference || '-');
                    const tgl = escapeHtml(det.receipt_date || '-');
                    const qtyRec = Number(det.actual_received || 0);
                    const pVal = Number(det.price || 0);
                    const pDiff = pVal - fPrice;
                    const pPct = fPrice > 0 ? ((pDiff / fPrice) * 100).toFixed(1) : 0;

                    let badgeHtml = '';
                    if (pDiff > 0) {
                        badgeHtml = `<span class="badge bg-danger text-white font-monospace"><i class="bi bi-arrow-up-right me-1"></i>Naik +$ ${pDiff.toFixed(2)} (+${pPct}%)</span>`;
                    } else if (pDiff < 0) {
                        badgeHtml = `<span class="badge bg-success text-white font-monospace"><i class="bi bi-arrow-down-right me-1"></i>Turun -$ ${Math.abs(pDiff).toFixed(2)} (${pPct}%)</span>`;
                    } else {
                        badgeHtml = `<span class="badge bg-secondary text-white font-monospace">Sama ($ 0.00)</span>`;
                    }

                    html += `<tr>
                        <td class="fw-bold text-warning font-monospace">${poRef}</td>
                        <td class="text-white">${tgl}</td>
                        <td class="text-end font-monospace fw-bold text-info">${qtyRec.toLocaleString('de-DE')} unit</td>
                        <td class="text-end font-monospace text-warning">$ ${pVal.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="text-end">${badgeHtml}</td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-muted"><i class="bi bi-info-circle me-1"></i>Tidak ada rincian penerimaan PO terpisah untuk periode ini.</td></tr>`;
            }
        }

        const bsModal = new bootstrap.Modal(document.getElementById('modalPriceReason'));
        bsModal.show();
    }

    /**
     * Interactive Smooth Scroll & Highlight item in Item Grid
     */
    function jumpToItemGrid(itemCode) {
        let targetEl = document.getElementById('item-row-' + itemCode);
        if (!targetEl) {
            targetEl = document.getElementById('item-detail-' + itemCode);
        }

        if (targetEl) {
            targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            targetEl.classList.add('highlight-row-glow');
            setTimeout(() => {
                targetEl.classList.remove('highlight-row-glow');
            }, 3000);
        } else {
            window.location.href = "{{ route('purchasing.analysis') }}?item_code=" + encodeURIComponent(itemCode);
        }
    }

    /**
     * Open Interactive Delivery Detail Breakdown Modal (Includes Harga ($) & Amount ($))
     */
    function openDeliveryDetailModal(monthName, itemCode, description, details) {
        document.getElementById('modalMonthName').textContent = monthName;
        document.getElementById('modalItemDesc').textContent = 'Item: ' + itemCode + ' - ' + description;
        
        const tbody = document.getElementById('modalDeliveryTableBody');
        tbody.innerHTML = '';
        
        let totalQty = 0;
        let totalAmt = 0;
        if (details && details.length > 0) {
            details.forEach(item => {
                const qtyRec = Number(item.actual_received || 0);
                const price  = Number(item.price || 0);
                const amt    = qtyRec * price;
                totalQty    += qtyRec;
                totalAmt    += amt;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-monospace text-light">${item.receipt_date || '-'}</td>
                    <td class="font-monospace fw-bold text-info">${escapeHtml(item.po_reference || '-')}</td>
                    <td><span class="badge bg-dark border border-secondary text-light">${escapeHtml(item.supplier_name || '-')}</span></td>
                    <td class="text-end font-monospace text-warning fw-semibold">${Number(item.target_order || 0).toLocaleString('de-DE')} unit</td>
                    <td class="text-end font-monospace text-success fw-bold fs-6">${qtyRec.toLocaleString('de-DE')} unit</td>
                    <td class="text-end font-monospace text-warning">$ ${price.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-end font-monospace text-success fw-bold">$ ${amt.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td><span class="badge bg-secondary bg-opacity-25 text-light fs-8">${escapeHtml(item.user_name || 'System')}</span></td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-3 text-muted">Tidak ada transaksi penerimaan pada bulan ini.</td></tr>`;
        }
        
        document.getElementById('modalTotalDeliveryQty').textContent = totalQty.toLocaleString('de-DE') + ' unit';
        const elAmt = document.getElementById('modalTotalDeliveryAmount');
        if (elAmt) {
            elAmt.textContent = '$ ' + totalAmt.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        
        const modalEl = document.getElementById('modalDeliveryDetailBreakdown');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>

<!-- MODAL RINCIAN TRANSAKSI INCOMING BULANAN -->
<div class="modal fade" id="modalDeliveryDetailBreakdown" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content glass-card border-secondary shadow-lg">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <div>
                    <h5 class="modal-title fw-bold text-warning mb-0" id="modalDeliveryTitle">
                        <i class="bi bi-receipt-cutoff me-2"></i>Rincian Transaksi Incoming Penerimaan PO - Bulan <span id="modalMonthName" class="text-white">MEI</span>
                    </h5>
                    <p class="text-muted small mb-0 mt-1" id="modalItemDesc">Item: 67508601 - keybed MO GL-10 NEW</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr class="text-uppercase small">
                                <th>TANGGAL RECEIPT</th>
                                <th>NO. PO</th>
                                <th>SUPPLIER / VENDOR</th>
                                <th class="text-end text-warning">TARGET PO</th>
                                <th class="text-end text-success">QTY DITERIMA</th>
                                <th class="text-end text-warning">HARGA ($)</th>
                                <th class="text-end text-success">TOTAL AMOUNT ($)</th>
                                <th>INPUT BY</th>
                            </tr>
                        </thead>
                        <tbody id="modalDeliveryTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold bg-dark text-white">
                                <td colspan="4" class="text-end text-uppercase">Total Akumulasi Incoming Penerimaan:</td>
                                <td class="text-end text-success font-monospace fs-6" id="modalTotalDeliveryQty">0 unit</td>
                                <td class="text-end text-muted small">Total Amount:</td>
                                <td class="text-end text-success font-monospace fs-6" id="modalTotalDeliveryAmount">$ 0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25 pt-2">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ── POP-UP MODAL: RINCIAN ITEM CODE KENAIKAN & PENURUNAN HARGA / AMOUNT ── --}}
<div class="modal fade" id="modalItemPriceVariance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content glass-card-static border-secondary shadow-lg">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2 me-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:rgba(245,158,11,0.2);border:1px solid rgba(245,158,11,0.4);color:#f59e0b;">
                            <i class="bi bi-diagram-3-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalVarianceTitle">
                                Info Item Code: Analisis Kenaikan &amp; Penurunan
                            </h5>
                            <p class="text-muted small mb-0" id="modalVarianceSub">Perbandingan Forecast Price vs Incoming Price Aktual per Item Code</p>
                        </div>
                    </div>

                    {{-- TOGGLE BUTTON PRICE VS AMOUNT IN MODAL --}}
                    <div class="btn-group btn-group-sm" role="group" aria-label="Modal Target Switcher">
                        <button type="button" class="btn btn-outline-warning active btn-modal-target" id="btnModalTarget_price" onclick="switchModalAnalysisTarget('price')">
                            <i class="bi bi-tag-fill me-1"></i> Mode Price (Harga Satuan)
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-modal-target" id="btnModalTarget_amount" onclick="switchModalAnalysisTarget('amount')">
                            <i class="bi bi-cash-stack me-1"></i> Mode Amount (Total Biaya)
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body py-3">
                {{-- Modal Nav Tabs --}}
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <ul class="nav nav-pills gap-2" id="variancePillsTab">
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill px-3 py-1.5 text-danger border border-danger border-opacity-50" id="pill-var-increase" onclick="filterVarianceModalTab('increase')">
                                <i class="bi bi-arrow-up-right-circle-fill me-1"></i> Kenaikan (Over Budget)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-3 py-1.5 text-success border border-success border-opacity-50 ms-1" id="pill-var-decrease" onclick="filterVarianceModalTab('decrease')">
                                <i class="bi bi-arrow-down-right-circle-fill me-1"></i> Penurunan (Efisiensi)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill px-3 py-1.5 text-info border border-info border-opacity-50 ms-1" id="pill-var-all" onclick="filterVarianceModalTab('all')">
                                <i class="bi bi-grid-3x3-gap-fill me-1"></i> Semua Item Code
                            </button>
                        </li>
                    </ul>

                    <div style="width:250px;">
                        <input type="text" class="form-control form-control-sm search-input" id="searchVarianceModal" placeholder="Cari Item Code / Description..." onkeyup="searchVarianceModalTable()">
                    </div>
                </div>

                {{-- Table inside modal --}}
                <div class="table-responsive">
                    <table class="table table-dark-custom align-middle" id="tableVarianceModal">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item Code</th>
                                <th>Deskripsi &amp; Supplier</th>
                                <th>User PIC / Buyer</th>
                                <th id="thModalFc">Forecast Price ($ / Rp)</th>
                                <th id="thModalAct">Realisasi Price ($ / Rp)</th>
                                <th id="thModalDiff">Selisih Harga ($ &amp; Rp)</th>
                                <th>Evaluasi Dampak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visibleItemPriceVariances as $idx => $ipv)
                            <tr class="var-row var-type-{{ $ipv->is_increase ? 'increase' : ($ipv->is_decrease ? 'decrease' : 'stable') }}" data-search="{{ strtolower($ipv->item_code . ' ' . $ipv->description . ' ' . $ipv->supplier) }}">
                                <td>{{ $idx + 1 }}</td>
                                <td><span class="badge bg-dark border border-secondary font-monospace text-warning fs-6">{{ $ipv->item_code }}</span></td>
                                <td>
                                    <div class="fw-semibold text-white">{{ $ipv->description }}</div>
                                    <small class="text-muted"><i class="bi bi-building me-1"></i>{{ $ipv->supplier }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-info">
                                        <i class="bi bi-person-fill me-1"></i>{{ $ipv->pic_buyer }}
                                    </span>
                                </td>

                                {{-- MODE PRICE CELLS --}}
                                <td class="col-target-price">
                                    <div class="fw-bold text-white font-monospace">$ {{ number_format($ipv->forecast_price_usd, 2, '.', ',') }}</div>
                                    <small class="text-muted font-monospace">Rp {{ number_format($ipv->forecast_price_idr, 0, ',', '.') }}</small>
                                </td>
                                <td class="col-target-price">
                                    <div class="fw-bold font-monospace text-warning">$ {{ number_format($ipv->actual_price_usd, 2, '.', ',') }}</div>
                                    <small class="text-muted font-monospace">Rp {{ number_format($ipv->actual_price_idr, 0, ',', '.') }}</small>
                                </td>
                                <td class="col-target-price">
                                    @if($ipv->diff_price_usd > 0)
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 font-monospace">
                                            <i class="bi bi-arrow-up-right me-1"></i>+$ {{ number_format($ipv->diff_price_usd, 2, '.', ',') }} (+{{ $ipv->diff_price_pct }}%)
                                        </span>
                                        <div class="small font-monospace text-danger mt-0.5">+Rp {{ number_format($ipv->diff_price_idr, 0, ',', '.') }}</div>
                                    @elseif($ipv->diff_price_usd < 0)
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 font-monospace">
                                            <i class="bi bi-arrow-down-right me-1"></i>-$ {{ number_format(abs($ipv->diff_price_usd), 2, '.', ',') }} ({{ $ipv->diff_price_pct }}%)
                                        </span>
                                        <div class="small font-monospace text-success mt-0.5">-Rp {{ number_format(abs($ipv->diff_price_idr), 0, ',', '.') }}</div>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-25 text-muted border border-secondary font-monospace">Sama ($ 0.00)</span>
                                    @endif
                                </td>

                                {{-- MODE AMOUNT CELLS (INITIAL DISPLAY NONE) --}}
                                <td class="col-target-amount" style="display:none;">
                                    <div class="fw-bold text-info font-monospace">$ {{ number_format($ipv->forecast_amount_usd, 2, '.', ',') }}</div>
                                    <small class="text-muted font-monospace">Rp {{ number_format($ipv->forecast_amount_idr, 0, ',', '.') }}</small>
                                </td>
                                <td class="col-target-amount" style="display:none;">
                                    <div class="fw-bold font-monospace text-emerald" style="color:#34d399;">$ {{ number_format($ipv->actual_amount_usd, 2, '.', ',') }}</div>
                                    <small class="text-muted font-monospace">Rp {{ number_format($ipv->actual_amount_idr, 0, ',', '.') }}</small>
                                </td>
                                <td class="col-target-amount" style="display:none;">
                                    @if($ipv->diff_amount_usd > 0)
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 font-monospace">
                                            <i class="bi bi-arrow-up-right me-1"></i>+$ {{ number_format($ipv->diff_amount_usd, 2, '.', ',') }} (+{{ $ipv->diff_amount_pct }}%)
                                        </span>
                                        <div class="small font-monospace text-danger mt-0.5">+Rp {{ number_format($ipv->diff_amount_idr, 0, ',', '.') }}</div>
                                    @elseif($ipv->diff_amount_usd < 0)
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 font-monospace">
                                            <i class="bi bi-arrow-down-right me-1"></i>-$ {{ number_format(abs($ipv->diff_amount_usd), 2, '.', ',') }} ({{ $ipv->diff_amount_pct }}%)
                                        </span>
                                        <div class="small font-monospace text-success mt-0.5">-Rp {{ number_format(abs($ipv->diff_amount_idr), 0, ',', '.') }}</div>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-25 text-muted border border-secondary font-monospace">Sama ($ 0.00)</span>
                                    @endif
                                </td>

                                <td>
                                    @if($ipv->is_increase)
                                        <span class="text-danger small fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Kenaikan Biaya</span>
                                    @elseif($ipv->is_decrease)
                                        <span class="text-success small fw-bold"><i class="bi bi-shield-check me-1"></i>Efisiensi Penurunan</span>
                                    @else
                                        <span class="text-muted small">Harga &amp; Amount Stabil</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Tidak ada data variansi item code.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-top border-secondary border-opacity-25 pt-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ── POP-UP MODAL: DATA-DRIVEN MONTHLY FINANCIAL & FORECAST INSIGHT ── --}}
<div class="modal fade" id="modalMonthlyInsight" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content glass-card-static border-secondary shadow-lg" style="background:#0f172a; border:1px solid rgba(0, 210, 255, 0.4); border-radius:18px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2 me-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:rgba(0,210,255,0.18);border:1px solid rgba(0,210,255,0.4);color:#00d2ff;">
                            <i class="bi bi-lightbulb-fill fs-4"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold text-white mb-0 brand-font" id="mInsightTitle">Insight Finansial &amp; Komparasi</h5>
                                <span class="badge" id="mInsightBadgeTrend">Trend</span>
                            </div>
                            <p class="text-muted small mb-0 mt-0.5" id="mInsightSubtitle">Analisis Perubahan Month-over-Month (MoM), Faktor Penyebab, dan Kontributor Material</p>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body py-4">
                {{-- CONDITIONAL ALERT: INCOMPLETE DATA HORIZON --}}
                <div id="mInsightIncompleteAlert" class="alert alert-warning bg-warning bg-opacity-10 border border-warning border-opacity-40 text-warning d-none rounded-3 p-3 mb-4">
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi bi-exclamation-triangle-fill fs-5 mt-0.5"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Peringatan: Data Planning Horizon Terbatas</h6>
                            <p class="small mb-0 text-white">File Master Forecast yang diunggah saat ini baru mencakup rencana pengadaan sampai periode Januari 2027. Angka nominal pada bulan ini merupakan nilai baseline ($338.18) dan bukan representasi penuh penurunan kebutuhan pabrik.</p>
                        </div>
                    </div>
                </div>

                {{-- 4 KEY METRIC CARDS --}}
                <div class="row g-3 mb-4">
                    {{-- Card 1: Forecast Amount --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="p-3 rounded-3 h-100" style="background:rgba(0,210,255,0.08); border:1px solid rgba(0,210,255,0.25);">
                            <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-graph-up text-info me-1"></i>Forecast Amount</div>
                            <div class="fs-4 fw-bold text-white font-monospace" id="mInsightFcUsd">$ 0.00</div>
                            <div class="small font-monospace text-muted" id="mInsightFcIdr">Rp 0</div>
                            <div class="mt-2" id="mInsightFcDiffContainer">
                                <span class="badge" id="mInsightFcDiffBadge">MoM: 0%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Incoming Amount --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="p-3 rounded-3 h-100" style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.25);">
                            <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-wallet2 text-success me-1"></i>Incoming PO (Actual)</div>
                            <div class="fs-4 fw-bold text-success font-monospace" id="mInsightActUsd">$ 0.00</div>
                            <div class="small font-monospace text-muted" id="mInsightActIdr">Rp 0</div>
                            <div class="mt-2">
                                <span class="badge" id="mInsightActStatusBadge">Status</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card 3: Forecast Volume Qty --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="p-3 rounded-3 h-100" style="background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.25);">
                            <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-box-seam text-warning me-1"></i>Volume Forecast Qty</div>
                            <div class="fs-4 fw-bold text-warning font-monospace" id="mInsightFcQty">0 Unit</div>
                            <div class="small text-muted" id="mInsightFcPrice">Avg Price: $ 0.00/unit</div>
                            <div class="mt-2">
                                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Kuantitas Rencana</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card 4: Inventory & Outstanding Support --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="p-3 rounded-3 h-100" style="background:rgba(168,85,247,0.08); border:1px solid rgba(168,85,247,0.25);">
                            <div class="text-muted small fw-bold text-uppercase mb-1"><i class="bi bi-shield-check text-purple me-1" style="color:#a78bfa;"></i>Stok Fisik &amp; PO Penopang</div>
                            <div class="fs-4 fw-bold text-white font-monospace" id="mInsightInvStock">0 PCS</div>
                            <div class="small font-monospace" style="color:#c4b5fd;" id="mInsightOutstanding">PO Outstanding: 0 Unit</div>
                            <div class="mt-2">
                                <span class="text-muted small"><i class="bi bi-layers me-1"></i>Buffer pasokan berjalan</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: MENGAPA ANGKA INI BERUBAH? --}}
                <div class="card bg-dark border-secondary border-opacity-30 rounded-3 p-3.5 mb-4" style="background:rgba(255,255,255,0.02) !important;">
                    <h6 class="fw-bold text-info mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-chat-left-dots-fill"></i>
                        <span>Analisis Penyebab &amp; Faktor Pendukung (Data-Driven Insight):</span>
                    </h6>
                    <p class="text-light mb-3 fw-medium" id="mInsightNarrativeSummary" style="font-size:0.95rem;">-</p>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2" id="mInsightFactorsList">
                        <!-- Populated dynamically via JS -->
                    </ul>
                </div>

                {{-- SECTION 3: TOP MATERIAL CONTRIBUTORS --}}
                <div id="mInsightContributorsSection">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-diagram-3-fill text-warning"></i>
                            <span>Top Material Penyebab Perubahan Terbesar (Month-over-Month):</span>
                        </h6>
                        <span class="text-muted small" style="font-size:0.78rem;">Ranked by Absolute Amount Delta ($ USD)</span>
                    </div>
                    <div class="table-responsive rounded-3 border border-secondary border-opacity-25 style-scrollbar" style="max-height: 270px; overflow-y: auto;">
                        <table class="table table-dark table-hover align-middle mb-0" style="font-size:0.85rem;" id="mInsightContributorsTable">
                            <thead class="sticky-top bg-dark" style="z-index: 1;">
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Item Code</th>
                                    <th>Deskripsi &amp; Supplier</th>
                                    <th class="text-end">Forecast Bulan Lalu</th>
                                    <th class="text-end">Forecast Bulan Ini</th>
                                    <th class="text-end">Selisih Amount ($ USD)</th>
                                    <th class="text-center">Status Dampak</th>
                                </tr>
                            </thead>
                            <tbody id="mInsightContributorsBody">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-top border-secondary border-opacity-25 pt-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
.highlight-row-glow {
    animation: glowPulse 0.8s infinite alternate !important;
    outline: 2px solid #f59e0b !important;
    background-color: rgba(245, 158, 11, 0.25) !important;
}
@keyframes glowPulse {
    from { background-color: rgba(245, 158, 11, 0.35); }
    to { background-color: rgba(245, 158, 11, 0.08); }
}
.currency-mode-usd .val-idr { display: none !important; }
.currency-mode-idr .val-usd { display: none !important; }
.currency-mode-dual .val-usd, .currency-mode-dual .val-idr { display: block !important; }
</style>

<script>
window.comparisonMonthlyInsights = @json($comparisonMonthlyInsights ?? []);

window.openMonthlyInsightModal = function(index) {
    if (!window.comparisonMonthlyInsights || !window.comparisonMonthlyInsights[index]) {
        console.warn('No insight data found for month index:', index);
        return;
    }

    const data = window.comparisonMonthlyInsights[index];

    // 1. Header & Badges
    document.getElementById('mInsightTitle').innerHTML = `<i class="bi bi-calendar3 me-2 text-info"></i>Insight Finansial &amp; Operasional: ${data.month_name}`;

    const badgeTrend = document.getElementById('mInsightBadgeTrend');
    if (data.is_first_month) {
        badgeTrend.className = 'badge bg-secondary text-white';
        badgeTrend.innerText = 'Periode Patokan (Baseline)';
    } else if (data.data_status === 'NO_DATA') {
        badgeTrend.className = 'badge bg-secondary text-muted';
        badgeTrend.innerText = 'Tidak Ada Data Rencana';
    } else if (data.data_status === 'FORECAST_ONLY') {
        badgeTrend.className = 'badge bg-warning text-dark fw-bold';
        badgeTrend.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i>Forecast Only (MoM: ${data.mom_fc_amount_pct >= 0 ? '+' : ''}${data.mom_fc_amount_pct}%)`;
    } else if (data.mom_fc_amount_pct > 5.0) {
        badgeTrend.className = 'badge bg-success text-white';
        badgeTrend.innerHTML = `<i class="bi bi-arrow-up-circle-fill me-1"></i>Naik +${data.mom_fc_amount_pct}%`;
    } else if (data.mom_fc_amount_pct < -5.0) {
        badgeTrend.className = 'badge bg-danger text-white';
        badgeTrend.innerHTML = `<i class="bi bi-arrow-down-circle-fill me-1"></i>Turun ${data.mom_fc_amount_pct}%`;
    } else {
        badgeTrend.className = 'badge bg-secondary text-white';
        badgeTrend.innerText = 'Stabil (0%)';
    }

    // 2. Incomplete Alert
    const incompleteAlert = document.getElementById('mInsightIncompleteAlert');
    if (data.data_status === 'FORECAST_ONLY' || data.is_incomplete) {
        incompleteAlert?.classList.remove('d-none');
    } else {
        incompleteAlert?.classList.add('d-none');
    }

    // 3. Metric Cards
    document.getElementById('mInsightFcUsd').innerText = '$ ' + Number(data.forecast_amount_usd || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('mInsightFcIdr').innerText = 'Rp ' + Number(data.forecast_amount_idr || 0).toLocaleString('id-ID');

    const fcDiffBadge = document.getElementById('mInsightFcDiffBadge');
    const diffUsd = data.forecast_diff_usd !== undefined ? Number(data.forecast_diff_usd) : Number(data.mom_fc_amount_usd || 0);
    const diffPct = Number(data.mom_fc_amount_pct || 0);

    if (data.is_first_month) {
        fcDiffBadge.className = 'badge bg-secondary';
        fcDiffBadge.innerText = 'Bulan Awal (Baseline)';
    } else if (diffUsd > 0) {
        fcDiffBadge.className = 'badge bg-success font-monospace';
        fcDiffBadge.innerText = `MoM Amount: +$ ${diffUsd.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})} (+${diffPct}%)`;
    } else if (diffUsd < 0) {
        fcDiffBadge.className = 'badge bg-danger font-monospace';
        fcDiffBadge.innerText = `MoM Amount: -$ ${Math.abs(diffUsd).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})} (${diffPct}%)`;
    } else {
        fcDiffBadge.className = 'badge bg-secondary font-monospace';
        fcDiffBadge.innerText = 'MoM Amount: $ 0.00 (0.00%)';
    }

    if (data.incoming_amount_usd !== null) {
        document.getElementById('mInsightActUsd').innerText = '$ ' + Number(data.incoming_amount_usd).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('mInsightActIdr').innerText = 'Rp ' + Number(data.incoming_amount_idr).toLocaleString('id-ID');
    } else {
        document.getElementById('mInsightActUsd').innerText = '—';
        document.getElementById('mInsightActIdr').innerText = 'Belum Ada Transaksi';
    }

    const actStatusBadge = document.getElementById('mInsightActStatusBadge');
    if (data.data_status === 'COMPLETE' && data.incoming_qty !== null) {
        actStatusBadge.className = 'badge bg-success bg-opacity-25 text-success border border-success';
        actStatusBadge.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Incoming Validated (${Number(data.incoming_qty).toLocaleString('id-ID')} Unit @ $${Number(data.incoming_price_usd).toFixed(2)})`;
    } else {
        actStatusBadge.className = 'badge bg-warning bg-opacity-25 text-warning border border-warning';
        actStatusBadge.innerHTML = `<i class="bi bi-hourglass-split me-1"></i>Future Planned (No Incoming Data Yet)`;
    }

    const pMom = Number(data.mom_fc_price_pct || 0);
    const pMomSign = pMom >= 0 ? '+' : '';
    document.getElementById('mInsightFcQty').innerText = Number(data.forecast_qty || 0).toLocaleString('id-ID') + ' Unit';
    document.getElementById('mInsightFcPrice').innerHTML = `Avg Price: <strong>$ ${Number(data.forecast_price_usd || 0).toFixed(2)}/unit</strong> <span class="ms-1 font-monospace text-nowrap ${pMom > 0 ? 'text-danger' : (pMom < 0 ? 'text-success' : 'text-muted')}">(MoM: ${pMomSign}${pMom.toFixed(1)}%)</span>`;

    document.getElementById('mInsightInvStock').innerText = Number(data.month_inv_stock || 0).toLocaleString('id-ID') + ' PCS';
    document.getElementById('mInsightOutstanding').innerText = 'PO Outstanding: ' + Number(data.month_outstanding || 0).toLocaleString('id-ID') + ' Unit';

    // 4. Narrative & Key Factors
    document.getElementById('mInsightNarrativeSummary').innerText = data.narrative_summary;

    const factorsList = document.getElementById('mInsightFactorsList');
    if (factorsList) {
        factorsList.innerHTML = '';
        if (data.key_factors && data.key_factors.length > 0) {
            data.key_factors.forEach(f => {
                const li = document.createElement('li');
                li.className = 'd-flex align-items-start gap-2 text-muted small';
                li.innerHTML = `<i class="bi bi-check2-circle text-info mt-0.5"></i><span class="text-light">${escapeHtml(f)}</span>`;
                factorsList.appendChild(li);
            });
        }
    }

    // 5. Top Contributors Table
    const contribBody = document.getElementById('mInsightContributorsBody');
    const contribSection = document.getElementById('mInsightContributorsSection');

    if (contribBody) {
        contribBody.innerHTML = '';
        if (data.top_contributors && data.top_contributors.length > 0) {
            if (contribSection) contribSection.style.display = '';
            data.top_contributors.forEach((item, cIdx) => {
                const isDown = item.diff_amt < 0;
                const badgeClass = isDown ? 'badge bg-danger bg-opacity-25 text-danger border border-danger font-monospace' : 'badge bg-success bg-opacity-25 text-success border border-success font-monospace';
                const sign = item.diff_amt > 0 ? '+' : '-';
                const statusText = isDown ? '<span class="text-danger small fw-semibold"><i class="bi bi-arrow-down-right me-1"></i>Kebutuhan Turun</span>' : '<span class="text-success small fw-semibold"><i class="bi bi-arrow-up-right me-1"></i>Kebutuhan Naik</span>';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-muted text-center">${cIdx + 1}</td>
                    <td><strong class="text-white font-monospace">${escapeHtml(item.item_code)}</strong></td>
                    <td>
                        <div class="text-light fw-medium">${escapeHtml(item.description)}</div>
                        <div class="text-muted small" style="font-size:0.75rem;"><i class="bi bi-truck me-1"></i>${escapeHtml(item.supplier)}</div>
                    </td>
                    <td class="text-end font-monospace text-muted">$ ${Number(item.prev_amt).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td class="text-end font-monospace text-white fw-bold">$ ${Number(item.curr_amt).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    <td class="text-end">
                        <span class="${badgeClass}">${sign}$ ${Number(Math.abs(item.diff_amt)).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                    </td>
                    <td class="text-center">${statusText}</td>
                `;
                contribBody.appendChild(tr);
            });
        } else {
            if (data.is_first_month || data.data_status === 'NO_DATA') {
                if (contribSection) contribSection.style.display = 'none';
            } else {
                if (contribSection) contribSection.style.display = '';
                contribBody.innerHTML = `<tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada material dengan perubahan volume signifikan.</td></tr>`;
            }
        }
    }

    const modalEl = document.getElementById('modalMonthlyInsight');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
};

window.resizeSlide2Charts = function() {
    setTimeout(function() {
        if (window.chartPoInstance) window.chartPoInstance.resize();
        if (window.chartOutstandingPoInstance) window.chartOutstandingPoInstance.resize();
        if (window.chartSupplierAmtInstance) window.chartSupplierAmtInstance.resize();
        if (window.chartSupplierQtyInstance) window.chartSupplierQtyInstance.resize();
    }, 150);
};


window.switchModalAnalysisTarget = function(target) {
    const btnPrice  = document.getElementById('btnModalTarget_price');
    const btnAmount = document.getElementById('btnModalTarget_amount');
    const thFc     = document.getElementById('thModalFc');
    const thAct    = document.getElementById('thModalAct');
    const thDiff   = document.getElementById('thModalDiff');
    const subTitle = document.getElementById('modalVarianceSub');

    [btnPrice, btnAmount].forEach(b => b?.classList.remove('active'));

    const priceCols  = document.querySelectorAll('.col-target-price');
    const amountCols = document.querySelectorAll('.col-target-amount');

    if (target === 'amount') {
        btnAmount?.classList.add('active');
        if (thFc) thFc.innerHTML = 'Forecast Amount ($ / Rp)';
        if (thAct) thAct.innerHTML = 'Incoming Amount ($ / Rp)';
        if (thDiff) thDiff.innerHTML = 'Selisih Amount ($ & Rp)';
        if (subTitle) subTitle.innerHTML = 'Perbandingan Total Biaya Akumulasi Forecast Amount vs Incoming Amount Aktual per Item Code';

        priceCols.forEach(c => c.style.display = 'none');
        amountCols.forEach(c => c.style.display = '');
    } else {
        btnPrice?.classList.add('active');
        if (thFc) thFc.innerHTML = 'Forecast Price ($ / Rp)';
        if (thAct) thAct.innerHTML = 'Incoming Price ($ / Rp)';
        if (thDiff) thDiff.innerHTML = 'Selisih Harga ($ & Rp)';
        if (subTitle) subTitle.innerHTML = 'Perbandingan Harga Satuan Forecast Price vs Incoming Price Aktual per Item Code';

        priceCols.forEach(c => c.style.display = '');
        amountCols.forEach(c => c.style.display = 'none');
    }
};
window.openItemVarianceModal = function(type) {
    const modalEl = document.getElementById('modalItemPriceVariance');
    if (!modalEl) return;
    filterVarianceModalTab(type);
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
};

window.filterVarianceModalTab = function(type) {
    const btnInc = document.getElementById('pill-var-increase');
    const btnDec = document.getElementById('pill-var-decrease');
    const btnAll = document.getElementById('pill-var-all');

    [btnInc, btnDec, btnAll].forEach(b => b?.classList.remove('active'));

    const rows = document.querySelectorAll('#tableVarianceModal tbody tr.var-row');

    if (type === 'increase') {
        btnInc?.classList.add('active');
        rows.forEach(r => r.style.display = r.classList.contains('var-type-increase') ? '' : 'none');
    } else if (type === 'decrease') {
        btnDec?.classList.add('active');
        rows.forEach(r => r.style.display = r.classList.contains('var-type-decrease') ? '' : 'none');
    } else {
        btnAll?.classList.add('active');
        rows.forEach(r => r.style.display = '');
    }
};

window.searchVarianceModalTable = function() {
    const input = document.getElementById('searchVarianceModal');
    const filter = input ? input.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('#tableVarianceModal tbody tr.var-row');

    rows.forEach(r => {
        const text = r.getAttribute('data-search') || '';
        if (text.includes(filter)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
};

    window.setTableCurrencyDisplay = function(mode) {
        const tbl = document.getElementById('tableFxComparison');
        const btnUsd = document.getElementById('btnTblMode_usd');
        const btnIdr = document.getElementById('btnTblMode_idr');

        if (mode === 'idr') {
            tbl?.classList.remove('currency-mode-usd');
            tbl?.classList.add('currency-mode-idr');
            btnIdr?.classList.add('active');
            btnUsd?.classList.remove('active');
        } else {
            tbl?.classList.remove('currency-mode-idr');
            tbl?.classList.add('currency-mode-usd');
            btnUsd?.classList.add('active');
            btnIdr?.classList.remove('active');
        }
    };
</script>

<!-- MODAL INPUT ALASAN VARIANSI STOK INVENTORY -->
<div class="modal fade" id="modalInventoryReason" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card-static shadow-lg" style="background:#0f172a; border:1px solid rgba(139, 92, 246, 0.4); color:#fff; border-radius:16px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold text-white brand-font d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-purple fs-4" style="color:#a78bfa;"></i>
                    <span>Input Alasan Variansi Stok Inventory</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formInventoryReason" onsubmit="submitInventoryReason(event)">
                @csrf
                <input type="hidden" id="invReasonPartNumber" name="part_number">
                <input type="hidden" id="invReasonVarianceType" name="variance_type">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">ITEM CODE / PART NUMBER</label>
                        <input type="text" class="form-control font-monospace bg-dark text-info border-secondary fw-bold" id="invReasonPartDisplay" readonly style="font-size:0.95rem;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">DESKRIPSI BARANG</label>
                        <input type="text" class="form-control bg-dark text-light border-secondary" id="invReasonDescDisplay" readonly style="font-size:0.85rem;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold required">KATEGORI ALASAN VARIANSI</label>
                        <select class="form-select bg-dark text-white border-secondary" id="invReasonCategory" name="reason_category" required style="font-size:0.9rem;">
                            <option value="">-- Pilih Kategori Alasan --</option>
                            <option value="Delay Delivery Supplier">Delay Incoming Supplier (Penerimaan Terlambat)</option>
                            <option value="Overproduction Line Assembly">Overproduction Line Assembly (Produksi Melebihi Rencana)</option>
                            <option value="Penyesuaian Demand Piano">Penyesuaian Demand / Order Customer (Plan Production Berubah)</option>
                            <option value="Defect / Material Scrap">Material Defect / Reject / Out of Spec</option>
                            <option value="Buffer Safety Stock">Penambahan Safety Stock Tambahan</option>
                            <option value="Lain-lain">Lain-lain / Catatan Khusus</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">CATATAN &amp; PENJELASAN DETIL</label>
                        <textarea class="form-control bg-dark text-white border-secondary" id="invReasonNotes" name="reason_notes" rows="3" placeholder="Tuliskan catatan penjelasan penyebab kenaikan/penurunan stok..." style="font-size:0.9rem;"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn rounded-pill px-4 fw-bold text-white" style="background:#8b5cf6;" id="btnSaveInvReason">
                        <i class="bi bi-check-circle me-1"></i> Simpan Alasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

// ── SLIDE 3 INTERACTIVE FUNCTIONS ──
window.currentSlide3Currency = 'USD';

window.triggerInvReasonModal = function(btn) {
    if (!btn) return;
    const partNo   = btn.getAttribute('data-part-no') || '';
    const desc     = btn.getAttribute('data-desc') || '';
    const status   = btn.getAttribute('data-status') || '';
    const category = btn.getAttribute('data-category') || '';
    const notes    = btn.getAttribute('data-notes') || '';
    window.openInventoryReasonModal(partNo, desc, status, category, notes);
};

window.switchSlide3Currency = function(mode) {
    window.currentSlide3Currency = mode;
    const btnUsd = document.getElementById('btnSlide3CurrUsd');
    const btnIdr = document.getElementById('btnSlide3CurrIdr');
    const badge  = document.getElementById('slide3AmtChartBadge');

    if (mode === 'USD') {
        btnUsd?.classList.add('active');
        btnIdr?.classList.remove('active');
        if (badge) badge.textContent = 'Mata Uang: USD ($)';

        document.querySelectorAll('.slide3-val-usd').forEach(el => el.classList.remove('d-none'));
        document.querySelectorAll('.slide3-val-idr').forEach(el => el.classList.add('d-none'));

        if (window.chartSlide3AmtInstance) {
            window.chartSlide3AmtInstance.data.datasets[0].label = 'Stock Forecast Amount ($ USD)';
            window.chartSlide3AmtInstance.data.datasets[0].data  = window.invFcAmtUsdData;
            window.chartSlide3AmtInstance.data.datasets[1].label = 'Stock Actual Amount ($ USD)';
            window.chartSlide3AmtInstance.data.datasets[1].data  = window.invActAmtUsdData;
            window.chartSlide3AmtInstance.update();
        }
    } else {
        btnIdr?.classList.add('active');
        btnUsd?.classList.remove('active');
        if (badge) badge.textContent = 'Mata Uang: IDR (Rp)';

        document.querySelectorAll('.slide3-val-idr').forEach(el => el.classList.remove('d-none'));
        document.querySelectorAll('.slide3-val-usd').forEach(el => el.classList.add('d-none'));

        if (window.chartSlide3AmtInstance) {
            window.chartSlide3AmtInstance.data.datasets[0].label = 'Stock Forecast Amount (Rp IDR)';
            window.chartSlide3AmtInstance.data.datasets[0].data  = window.invFcAmtIdrData;
            window.chartSlide3AmtInstance.data.datasets[1].label = 'Stock Actual Amount (Rp IDR)';
            window.chartSlide3AmtInstance.data.datasets[1].data  = window.invActAmtIdrData;
            window.chartSlide3AmtInstance.update();
        }
    }
};

// ── SLIDE 3 INTERACTIVE FUNCTIONS ──
window.currentSlide3Currency = 'USD';
window.slide3VendorSummariesData = @json($slide3VendorSummaries ?? []);
window.currentActiveVendor = '{{ $s3_vendor ?? "ALL" }}';

window.openVendorDashboard = function(vendorName) {
    if (!vendorName) return;
    window.currentActiveVendor = vendorName;

    // 1. Hide overview, show dedicated vendor view
    const overviewSec = document.getElementById('slide3OverviewSection');
    const vendorView  = document.getElementById('slide3DedicatedVendorView');
    if (overviewSec) overviewSec.classList.add('d-none');
    if (vendorView)  vendorView.classList.remove('d-none');

    // 2. Find vendor summary object
    const upperVendor = vendorName.toUpperCase().trim();
    const vsData = (window.slide3VendorSummariesData || []).find(v => (v.supplier || '').toUpperCase().trim() === upperVendor);

    // 3. Update Vendor Header & Badges
    const titleEl = document.getElementById('vDashTitle');
    if (titleEl) titleEl.textContent = vendorName;

    const countBadge = document.getElementById('vDashBadgeCount');
    const picBadge   = document.getElementById('vDashBadgePic');
    const delBadge   = document.getElementById('vDashBadgeDel');
    const tableBadge = document.getElementById('vDashTableItemBadge');

    const itemCount = vsData ? vsData.item_count : 0;
    if (countBadge) countBadge.innerHTML = '<i class="bi bi-boxes me-1"></i> ' + itemCount + ' Item Material';
    if (tableBadge) tableBadge.textContent = itemCount + ' Item Material';

    if (picBadge) {
        const pics = (vsData && vsData.pics && vsData.pics.length > 0) ? vsData.pics.join(', ') : '-';
        picBadge.innerHTML = '<i class="bi bi-person-badge me-1 text-warning"></i> PIC: ' + pics;
    }
    if (delBadge) {
        const dels = (vsData && vsData.delivery_categories && vsData.delivery_categories.length > 0) ? vsData.delivery_categories.join(', ') : 'LOC';
        delBadge.innerHTML = '<i class="bi bi-truck me-1 text-info"></i> Delivery: ' + dels;
    }

    // 4. Update KPI Cards for this vendor
    const kpiItemCount = document.getElementById('vDashKpiItemCount');
    if (kpiItemCount) kpiItemCount.textContent = itemCount + ' Item';

    if (vsData && vsData.m0) {
        const fcQty = vsData.m0.forecast_stock_qty || 0;
        const fcUsd = vsData.m0.forecast_stock_usd || 0;
        const fcIdr = vsData.m0.forecast_stock_idr || 0;

        const actQty = vsData.m0.actual_stock_qty || 0;
        const actUsd = vsData.m0.actual_stock_usd || 0;
        const actIdr = vsData.m0.actual_stock_idr || 0;

        const varQty = vsData.m0.variance_qty || 0;
        const varUsd = vsData.m0.variance_amount_usd || 0;
        const varIdr = vsData.m0.variance_amount_idr || 0;

        const elFcQty = document.getElementById('vDashKpiFcQty');
        const elFcUsd = document.getElementById('vDashKpiFcUsd');
        const elFcIdr = document.getElementById('vDashKpiFcIdr');
        if (elFcQty) elFcQty.textContent = Number(fcQty).toLocaleString('id-ID') + ' PCS';
        if (elFcUsd) elFcUsd.textContent = '$ ' + Number(fcUsd).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elFcIdr) elFcIdr.textContent = 'Rp ' + Number(fcIdr).toLocaleString('id-ID');

        const elActQty = document.getElementById('vDashKpiActQty');
        const elActUsd = document.getElementById('vDashKpiActUsd');
        const elActIdr = document.getElementById('vDashKpiActIdr');
        if (elActQty) elActQty.textContent = Number(actQty).toLocaleString('id-ID') + ' PCS';
        if (elActUsd) elActUsd.textContent = '$ ' + Number(actUsd).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elActIdr) elActIdr.textContent = 'Rp ' + Number(actIdr).toLocaleString('id-ID');

        const elVarQty = document.getElementById('vDashKpiVarQty');
        const elVarUsd = document.getElementById('vDashKpiVarUsd');
        const elVarIdr = document.getElementById('vDashKpiVarIdr');

        const signQty = varQty >= 0 ? '+' : '';
        const signUsd = varUsd >= 0 ? '+' : '';
        const signIdr = varIdr >= 0 ? '+' : '';
        const colorClass = varUsd >= 0 ? 'text-success' : 'text-danger';

        if (elVarQty) {
            elVarQty.className = 'badge ' + (varQty >= 0 ? 'bg-success' : 'bg-danger') + ' bg-opacity-25 text-white';
            elVarQty.textContent = signQty + Number(varQty).toLocaleString('id-ID') + ' PCS';
        }
        if (elVarUsd) {
            elVarUsd.className = 'kpi-value slide3-val-usd ' + colorClass + (window.currentSlide3Currency === 'IDR' ? ' d-none' : '');
            elVarUsd.textContent = signUsd + '$ ' + Number(varUsd).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        if (elVarIdr) {
            elVarIdr.className = 'kpi-value slide3-val-idr ' + colorClass + (window.currentSlide3Currency === 'USD' ? ' d-none' : '');
            elVarIdr.textContent = signIdr + 'Rp ' + Number(varIdr).toLocaleString('id-ID');
        }

        const elStatus = document.getElementById('vDashKpiStatus');
        if (elStatus) {
            if (vsData.status === 'Surplus') {
                elStatus.innerHTML = '<span class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-1 font-monospace"><i class="bi bi-arrow-up-circle me-1"></i>Surplus</span>';
            } else if (vsData.status === 'Deficit') {
                elStatus.innerHTML = '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-2 py-1 font-monospace"><i class="bi bi-arrow-down-circle me-1"></i>Defisit</span>';
            } else {
                elStatus.innerHTML = '<span class="badge bg-info bg-opacity-25 text-info border border-info px-2 py-1 font-monospace"><i class="bi bi-check-circle me-1"></i>Optimal</span>';
            }
        }
    }

    // 5. Filter Item Table Rows for this vendor
    const itemRows = document.querySelectorAll('.slide3-row-group');
    let matchCount = 0;

    itemRows.forEach(row => {
        const rowVendor = (row.getAttribute('data-vendor') || '').toUpperCase().trim();
        if (rowVendor === upperVendor) {
            row.style.display = '';
            matchCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const searchInput = document.getElementById('searchSlide3Item');
    if (searchInput) searchInput.value = '';

    // 6. Update URL in address bar without reload
    const url = new URL(window.location);
    url.searchParams.set('active_slide', 'slide3');
    url.searchParams.set('s3_vendor', vendorName);
    window.history.pushState({ vendor: vendorName }, '', url);

    // 7. Smooth Scroll to top of Vendor Dashboard
    if (vendorView) {
        vendorView.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

window.closeVendorDashboard = function() {
    window.currentActiveVendor = 'ALL';

    const overviewSec = document.getElementById('slide3OverviewSection');
    const vendorView  = document.getElementById('slide3DedicatedVendorView');
    if (vendorView)  vendorView.classList.add('d-none');
    if (overviewSec) overviewSec.classList.remove('d-none');

    // Restore all item rows and vendor summary rows
    document.querySelectorAll('.slide3-row-group').forEach(row => row.style.display = '');
    document.querySelectorAll('.slide3-vendor-row-group').forEach(row => row.style.display = '');

    // Reset filter status and search inputs
    const searchItem = document.getElementById('searchSlide3Item');
    if (searchItem) searchItem.value = '';
    const searchVendor = document.getElementById('searchSlide3Vendor');
    if (searchVendor) searchVendor.value = '';
    const statusFilter = document.getElementById('slide3StatusFilter');
    if (statusFilter) statusFilter.value = 'ALL';

    // Update URL to s3_vendor=ALL
    const url = new URL(window.location);
    url.searchParams.set('active_slide', 'slide3');
    url.searchParams.set('s3_vendor', 'ALL');
    window.history.pushState({ vendor: 'ALL' }, '', url);

    // Refresh overview charts
    if (typeof window.resizeSlide3Charts === 'function') {
        window.resizeSlide3Charts();
    }

    // Scroll smoothly to overview vendor table
    const tableVendor = document.getElementById('slide3VendorSection');
    if (tableVendor) {
        tableVendor.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

window.filterQuickSwitchVendors = function(query) {
    const q = (query || '').trim().toUpperCase();
    const items = document.querySelectorAll('.quick-vendor-item');
    items.forEach(it => {
        const vName = (it.getAttribute('data-vendor-name') || '').toUpperCase();
        if (!q || vName.includes(q)) {
            it.style.display = '';
        } else {
            it.style.display = 'none';
        }
    });
};

window.filterSlide3VendorTable = function(query) {
    const q = (query || '').trim().toUpperCase();
    const rows = document.querySelectorAll('.slide3-vendor-row-group');
    const seenVendors = new Set();

    rows.forEach(row => {
        const vName = (row.getAttribute('data-vendor') || '').toUpperCase();
        if (!q || vName.includes(q)) {
            row.style.display = '';
            seenVendors.add(vName);
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('slide3VendorCountBadge');
    if (badge) {
        badge.textContent = seenVendors.size + ' Vendor';
    }
};

window.filterSlide3ItemTable = function(query) {
    const q = (query || '').trim().toUpperCase();
    const rows = document.querySelectorAll('.slide3-row-group');
    const activeVendor = (window.currentActiveVendor || 'ALL').toUpperCase().trim();
    const isVendorFocus = (activeVendor !== 'ALL');
    const seenItems = new Set();

    rows.forEach(row => {
        const rowVendor = (row.getAttribute('data-vendor') || '').toUpperCase().trim();
        const itemCode  = (row.getAttribute('data-item-code') || '').toUpperCase();
        const desc      = (row.getAttribute('data-desc') || '').toUpperCase();

        const matchesVendor = !isVendorFocus || (rowVendor === activeVendor);
        const matchesQuery  = !q || itemCode.includes(q) || desc.includes(q);

        if (matchesVendor && matchesQuery) {
            row.style.display = '';
            seenItems.add(itemCode);
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('vDashTableItemBadge') || document.getElementById('slide3ItemCountBadge');
    if (badge) {
        badge.textContent = seenItems.size + ' Item Material';
    }
};

window.filterSlide3TableByStatus = function(status) {
    // 1. Filter Part Number Rows
    const itemRows = document.querySelectorAll('.slide3-row-group');
    const activeVendor = (window.currentActiveVendor || 'ALL').toUpperCase().trim();
    const isVendorFocus = (activeVendor !== 'ALL');
    const seenItems = new Set();

    itemRows.forEach(row => {
        const rowVendor = (row.getAttribute('data-vendor') || '').toUpperCase().trim();
        const rowStatus = row.getAttribute('data-status');
        const matchesVendor = !isVendorFocus || (rowVendor === activeVendor);
        const matchesStatus = (status === 'ALL' || rowStatus === status);

        if (matchesVendor && matchesStatus) {
            row.style.display = '';
            const code = row.getAttribute('data-item-code');
            if (code) seenItems.add(code);
        } else {
            row.style.display = 'none';
        }
    });

    const itemBadge = document.getElementById('vDashTableItemBadge') || document.getElementById('slide3ItemCountBadge');
    if (itemBadge) {
        itemBadge.textContent = seenItems.size + ' Item Material';
    }

    // 2. Filter Vendor Rows in Overview
    const vendorRows = document.querySelectorAll('.slide3-vendor-row-group');
    const seenVendors = new Set();
    vendorRows.forEach(row => {
        const vStatus = row.getAttribute('data-status');
        if (status === 'ALL' || vStatus === status) {
            row.style.display = '';
            const vName = row.getAttribute('data-vendor');
            if (vName) seenVendors.add(vName);
        } else {
            row.style.display = 'none';
        }
    });
    const vendorBadge = document.getElementById('slide3VendorCountBadge');
    if (vendorBadge) {
        vendorBadge.textContent = seenVendors.size + ' Vendor';
    }
};

window.openInventoryReasonModal = function(partNo, desc, vType, category, notes) {
    document.getElementById('invReasonPartNumber').value = partNo;
    document.getElementById('invReasonVarianceType').value = vType;
    document.getElementById('invReasonPartDisplay').value = partNo;
    document.getElementById('invReasonDescDisplay').value = desc;
    document.getElementById('invReasonCategory').value = category || '';
    document.getElementById('invReasonNotes').value = notes || '';

    const modalEl = document.getElementById('modalInventoryReason');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
};

window.submitInventoryReason = function(event) {
    event.preventDefault();
    const btn = document.getElementById('btnSaveInvReason');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    const formData = new FormData(document.getElementById('formInventoryReason'));

    fetch('{{ route("purchasing.analysis.inventory-reason") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan Alasan';

        if (data.success) {
            const modalEl = document.getElementById('modalInventoryReason');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Refresh page so table badge updates cleanly
            window.location.reload();
        } else {
            if (window.notify) {
                window.notify.error('Gagal Simpan', data.message || 'Gagal menyimpan alasan.');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan Alasan';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan Alasan';
        if (window.notify) {
            window.notify.error('Kesalahan Jaringan', 'Terjadi kesalahan jaringan/server.');
        }
    });
};
</script>

<script>
        // ── SLIDE 3 CHARTS INITIALIZATION & LIFECYCLE MANAGEMENT ──
        const invLabels       = @json(array_slice($monthsLabels, 1, $s3_duration));
        const invFcQty        = @json(array_slice($chartInvForecastStock, 1, $s3_duration));
        const invActQty       = @json(array_slice($chartInvActualStock, 1, $s3_duration));

        window.invFcAmtUsdData = @json(array_slice($chartInvForecastAmountUsd, 1, $s3_duration));
        window.invActAmtUsdData = @json(array_slice($chartInvActualAmountUsd, 1, $s3_duration));
        window.invFcAmtIdrData = @json(array_slice($chartInvForecastAmountIdr, 1, $s3_duration));
        window.invActAmtIdrData = @json(array_slice($chartInvActualAmountIdr, 1, $s3_duration));

        // Datalabels plugin for Slide 3
        const slide3ValueLabelsPlugin = {
            id: 'slide3ValueLabelsPlugin',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                chart.data.datasets.forEach((dataset, datasetIndex) => {
                    const meta = chart.getDatasetMeta(datasetIndex);
                    if (meta.hidden) return;

                    meta.data.forEach((element, index) => {
                        const val = dataset.data[index];
                        if (val === undefined || val === null || val <= 0) return;

                        const isUsd = (window.currentSlide3Currency || 'USD') === 'USD';
                        let text = '';
                        if (chart.canvas.id === 'chartSlide3InventoryAmount') {
                            if (isUsd) {
                                if (val >= 1000000) text = '$ ' + (val / 1000000).toFixed(2) + 'M';
                                else if (val >= 1000) text = '$ ' + (val / 1000).toFixed(1) + 'k';
                                else text = '$ ' + Math.round(val).toLocaleString('en-US');
                            } else {
                                if (val >= 1000000000) text = 'Rp ' + (val / 1000000000).toFixed(2) + ' M';
                                else if (val >= 1000000) text = 'Rp ' + (val / 1000000).toFixed(1) + ' Jt';
                                else if (val >= 1000) text = 'Rp ' + (val / 1000).toFixed(0) + 'k';
                                else text = 'Rp ' + Math.round(val).toLocaleString('id-ID');
                            }
                        } else {
                            if (val >= 1000000) text = (val / 1000000).toFixed(2) + 'M';
                            else if (val >= 1000) text = (val / 1000).toFixed(1) + 'k';
                            else text = val.toLocaleString('id-ID');
                        }

                        ctx.save();
                        ctx.font = 'bold 9px Inter, sans-serif';
                        const textWidth = ctx.measureText(text).width;
                        const paddingX = 4;
                        const boxWidth = textWidth + paddingX * 2;
                        const boxHeight = 13;

                        const isFirstDataset = datasetIndex === 0;
                        const yPos = isFirstDataset ? element.y - 14 : element.y + 14;

                        ctx.fillStyle = isFirstDataset ? 'rgba(15, 23, 42, 0.88)' : 'rgba(30, 27, 75, 0.88)';
                        ctx.strokeStyle = isFirstDataset ? '#3b82f6' : '#8b5cf6';
                        ctx.lineWidth = 1;
                        
                        ctx.beginPath();
                        if (typeof ctx.roundRect === 'function') {
                            ctx.roundRect(element.x - boxWidth / 2, yPos - boxHeight / 2, boxWidth, boxHeight, 4);
                        } else {
                            ctx.rect(element.x - boxWidth / 2, yPos - boxHeight / 2, boxWidth, boxHeight);
                        }
                        ctx.fill();
                        ctx.stroke();

                        ctx.fillStyle = isFirstDataset ? '#60a5fa' : '#c4b5fd';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(text, element.x, yPos);
                        ctx.restore();
                    });
                });
            }
        };

        window.initOrUpdateSlide3Charts = function() {
            const elAmt = document.getElementById('chartSlide3InventoryAmount');
            const elQty = document.getElementById('chartSlide3InventoryQty');
            if (!elAmt || !elQty) return;

            // 1. Amount Chart (Line)
            if (window.chartSlide3AmtInstance) {
                window.chartSlide3AmtInstance.resize();
                window.chartSlide3AmtInstance.update();
            } else {
                const ctxSlide3Amt = elAmt.getContext('2d');
                if (ctxSlide3Amt) {
                    window.chartSlide3AmtInstance = new Chart(ctxSlide3Amt, {
                        type: 'line',
                        data: {
                            labels: invLabels,
                            datasets: [
                                {
                                    label: 'Stock Forecast Amount ($ USD)',
                                    data: window.invFcAmtUsdData,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: 4,
                                    spanGaps: true,
                                },
                                {
                                    label: 'Stock Actual Amount ($ USD)',
                                    data: window.invActAmtUsdData,
                                    borderColor: '#8b5cf6',
                                    backgroundColor: 'rgba(139, 92, 246, 0.15)',
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: 4,
                                    spanGaps: true,
                                }
                            ]
                        },
                        plugins: [slide3ValueLabelsPlugin],
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 12 } } },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            const isUsd = (window.currentSlide3Currency || 'USD') === 'USD';
                                            const val = Number(ctx.raw || 0);
                                            if (isUsd) {
                                                return ctx.dataset.label + ': $ ' + val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                            } else {
                                                return ctx.dataset.label + ': Rp ' + Math.round(val).toLocaleString('id-ID');
                                            }
                                        },
                                        afterBody: function(tooltipItems) {
                                            if (!tooltipItems || tooltipItems.length === 0) return [];
                                            const idx = tooltipItems[0].dataIndex;
                                            const isUsd = (window.currentSlide3Currency || 'USD') === 'USD';
                                            const fcVal = isUsd ? Number(window.invFcAmtUsdData[idx] || 0) : Number(window.invFcAmtIdrData[idx] || 0);
                                            const actVal = isUsd ? Number(window.invActAmtUsdData[idx] || 0) : Number(window.invActAmtIdrData[idx] || 0);
                                            const diff = actVal - fcVal;
                                            const pct = fcVal > 0 ? ((diff / fcVal) * 100).toFixed(1) : '0.0';
                                            const sign = diff >= 0 ? '+' : '';
                                            const diffStr = isUsd ? ('$ ' + Number(diff).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})) : ('Rp ' + Math.round(Number(diff)).toLocaleString('id-ID'));
                                            const statusStr = actVal <= 0 ? 'Belum Ada Transaksi' : (Math.abs(diff) <= (fcVal * 0.05) ? 'Sesuai / Optimal' : (diff > 0 ? 'Surplus Stock' : 'Defisit Stock'));
                                            return [
                                                '------------------------------------',
                                                'Selisih Nominal : ' + sign + diffStr,
                                                'Variansi %     : ' + sign + pct + '%',
                                                'Evaluasi       : ' + statusStr
                                            ];
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#94a3b8',
                                        callback: function(val) {
                                            if ((window.currentSlide3Currency || 'USD') === 'USD') {
                                                if (val >= 1000000) return '$ ' + (val / 1000000).toFixed(1) + 'M';
                                                if (val >= 1000) return '$ ' + (val / 1000).toFixed(0) + 'k';
                                                return '$ ' + Number(val).toLocaleString('en-US', {maximumFractionDigits: 0});
                                            } else {
                                                if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' Miliar';
                                                if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(0) + 'Jt';
                                                return 'Rp ' + Math.round(Number(val)).toLocaleString('id-ID');
                                            }
                                        }
                                    },
                                    grid: { color: 'rgba(255,255,255,0.05)' }
                                }
                            }
                        }
                    });
                }
            }

            // 2. Quantity Chart (Bar)
            if (window.chartSlide3QtyInstance) {
                window.chartSlide3QtyInstance.resize();
                window.chartSlide3QtyInstance.update();
            } else {
                const ctxSlide3Qty = elQty.getContext('2d');
                if (ctxSlide3Qty) {
                    window.chartSlide3QtyInstance = new Chart(ctxSlide3Qty, {
                        type: 'bar',
                        data: {
                            labels: invLabels,
                            datasets: [
                                {
                                    label: 'Stock Forecast QTY (PCS)',
                                    data: invFcQty,
                                    backgroundColor: 'rgba(59, 130, 246, 0.75)',
                                    borderRadius: 6,
                                },
                                {
                                    label: 'Stock Actual QTY (PCS)',
                                    data: invActQty,
                                    backgroundColor: 'rgba(139, 92, 246, 0.75)',
                                    borderRadius: 6,
                                }
                            ]
                        },
                        plugins: [slide3ValueLabelsPlugin],
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 12 } } },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            const val = Number(ctx.raw || 0);
                                            return ctx.dataset.label + ': ' + val.toLocaleString('id-ID') + ' PCS';
                                        },
                                        afterBody: function(tooltipItems) {
                                            if (!tooltipItems || tooltipItems.length === 0) return [];
                                            const idx = tooltipItems[0].dataIndex;
                                            const fcQty = Number(invFcQty[idx] || 0);
                                            const actQty = Number(invActQty[idx] || 0);
                                            const diff = actQty - fcQty;
                                            const pct = fcQty > 0 ? ((diff / fcQty) * 100).toFixed(1) : '0.0';
                                            const sign = diff >= 0 ? '+' : '';
                                            const statusStr = actQty <= 0 ? 'Belum Ada Transaksi' : (Math.abs(diff) <= (fcQty * 0.05) ? 'Sesuai / Optimal' : (diff > 0 ? 'Surplus Stock' : 'Defisit Stock'));
                                            return [
                                                '------------------------------------',
                                                'Selisih QTY    : ' + sign + Number(diff).toLocaleString('id-ID') + ' PCS',
                                                'Variansi %     : ' + sign + pct + '%',
                                                'Evaluasi       : ' + statusStr
                                            ];
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#94a3b8',
                                        callback: function(val) {
                                            if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M PCS';
                                            if (val >= 1000) return (val / 1000).toFixed(0) + 'k PCS';
                                            return Number(val).toLocaleString('id-ID') + ' PCS';
                                        }
                                    },
                                    grid: { color: 'rgba(255,255,255,0.05)' }
                                }
                            }
                        }
                    });
                }
            }
        };

        // Initialize immediately or on load
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            window.initOrUpdateSlide3Charts();
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                window.initOrUpdateSlide3Charts();
            });
        }

// ═══════════════════════════════════════════════════════════════════════════
// SUPPLIER CHARTS — Multi-Currency Amount Trend (Line) & Quantity Comparison (Bar)
// ═══════════════════════════════════════════════════════════════════════════
(function() {
    window.currentSupplierCurrency = 'USD';

    const supLabels      = @json($chartSupplierLabels ?? []);
    const supFcAmountUsd = @json($chartSupplierForecastAmountUsd ?? $chartSupplierForecastAmount ?? []);
    const supAcAmountUsd = @json($chartSupplierActualAmountUsd ?? $chartSupplierActualAmount ?? []);
    const supFcAmountIdr = @json($chartSupplierForecastAmountIdr ?? []);
    const supAcAmountIdr = @json($chartSupplierActualAmountIdr ?? []);
    const supFcQty       = @json($chartSupplierForecastQty ?? []);
    const supAcQty       = @json($chartSupplierActualQty ?? []);

    // ── LINE CHART: Supplier Amount Trend (USD & IDR) ──
    const ctxSupAmt = document.getElementById('chartSupplierAmountTrend');
    if (ctxSupAmt) {
        window.chartSupplierAmtInstance = new Chart(ctxSupAmt.getContext('2d'), {
            type: 'line',
            data: {
                labels: supLabels,
                datasets: [
                    {
                        label: 'Forecast Amount ($ USD)',
                        data: supFcAmountUsd,
                        borderColor: '#00d2ff',
                        backgroundColor: 'rgba(0,210,255,0.08)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#00d2ff',
                        pointBorderColor: '#0f172a',
                        pointBorderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        order: 2
                    },
                    {
                        label: 'Actual/Incoming Amount ($ USD)',
                        data: supAcAmountUsd,
                        borderColor: '#34d399',
                        backgroundColor: 'rgba(52,211,153,0.08)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#34d399',
                        pointBorderColor: '#0f172a',
                        pointBorderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { color: '#cbd5e1', usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.95)',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed.y || 0;
                                if (window.currentSupplierCurrency === 'IDR') {
                                    if (val >= 1000000000) return ctx.dataset.label + ': Rp ' + (val / 1000000000).toFixed(2) + ' Miliar';
                                    if (val >= 1000000) return ctx.dataset.label + ': Rp ' + (val / 1000000).toFixed(1) + ' Jt';
                                    return ctx.dataset.label + ': Rp ' + Math.round(val).toLocaleString('id-ID');
                                } else {
                                    if (val >= 1000000) return ctx.dataset.label + ': $ ' + (val / 1000000).toFixed(2) + 'M';
                                    if (val >= 1000) return ctx.dataset.label + ': $ ' + (val / 1000).toFixed(1) + 'K';
                                    return ctx.dataset.label + ': $ ' + val.toLocaleString('en-US', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#94a3b8', font: { size: 10 } },
                        grid: { color: 'rgba(255,255,255,0.04)' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#94a3b8',
                            callback: function(val) {
                                if (window.currentSupplierCurrency === 'IDR') {
                                    if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' M';
                                    if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(0) + ' Jt';
                                    return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                                } else {
                                    if (val >= 1000000) return '$ ' + (val / 1000000).toFixed(1) + 'M';
                                    if (val >= 1000) return '$ ' + (val / 1000).toFixed(0) + 'K';
                                    return '$ ' + val.toLocaleString('en-US');
                                }
                            }
                        },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    }
                }
            }
        });
    }

    // ── GROUPED BAR CHART: Supplier Quantity Comparison ──
    const ctxSupQty = document.getElementById('chartSupplierQtyCompare');
    if (ctxSupQty) {
        window.chartSupplierQtyInstance = new Chart(ctxSupQty.getContext('2d'), {
            type: 'bar',
            data: {
                labels: supLabels,
                datasets: [
                    {
                        label: 'Forecast Qty (PCS)',
                        data: supFcQty,
                        backgroundColor: 'rgba(192,132,252,0.65)',
                        borderColor: '#c084fc',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.55,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Actual Qty (PCS)',
                        data: supAcQty,
                        backgroundColor: 'rgba(251,191,36,0.65)',
                        borderColor: '#fbbf24',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.55,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { color: '#cbd5e1', usePointStyle: true, pointStyle: 'rectRounded', padding: 16, font: { size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.95)',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': ' + Number(ctx.parsed.y || 0).toLocaleString('id-ID') + ' PCS';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#94a3b8', font: { size: 10 } },
                        grid: { color: 'rgba(255,255,255,0.04)' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#94a3b8',
                            callback: function(val) {
                                if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                if (val >= 1000) return (val / 1000).toFixed(0) + 'K';
                                return Number(val).toLocaleString('id-ID') + ' PCS';
                            }
                        },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    }
                }
            }
        });
    }

    // ── CURRENCY SWITCHER FUNCTION FOR SUPPLIER SECTION ──
    window.switchSupplierCurrency = function(mode) {
        window.currentSupplierCurrency = mode;

        const btnUsd = document.getElementById('btnSupCurrUsd');
        const btnIdr = document.getElementById('btnSupCurrIdr');
        const titleEl = document.getElementById('labelSupplierAmountTrendTitle');

        if (mode === 'IDR') {
            if (btnUsd) btnUsd.classList.remove('active');
            if (btnIdr) btnIdr.classList.add('active');
            if (titleEl) titleEl.innerText = 'Tren Amount: Forecast vs Actual/Incoming (Rp IDR)';

            // Toggle HTML values
            document.querySelectorAll('.sup-val-usd').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.sup-val-idr').forEach(el => el.classList.remove('d-none'));

            // Update Chart.js datasets
            if (window.chartSupplierAmtInstance) {
                window.chartSupplierAmtInstance.data.datasets[0].label = 'Forecast Amount (Rp IDR)';
                window.chartSupplierAmtInstance.data.datasets[0].data  = supFcAmountIdr;
                window.chartSupplierAmtInstance.data.datasets[1].label = 'Actual/Incoming Amount (Rp IDR)';
                window.chartSupplierAmtInstance.data.datasets[1].data  = supAcAmountIdr;
                window.chartSupplierAmtInstance.update();
            }
        } else {
            if (btnIdr) btnIdr.classList.remove('active');
            if (btnUsd) btnUsd.classList.add('active');
            if (titleEl) titleEl.innerText = 'Tren Amount: Forecast vs Actual/Incoming ($ USD)';

            // Toggle HTML values
            document.querySelectorAll('.sup-val-idr').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.sup-val-usd').forEach(el => el.classList.remove('d-none'));

            // Update Chart.js datasets
            if (window.chartSupplierAmtInstance) {
                window.chartSupplierAmtInstance.data.datasets[0].label = 'Forecast Amount ($ USD)';
                window.chartSupplierAmtInstance.data.datasets[0].data  = supFcAmountUsd;
                window.chartSupplierAmtInstance.data.datasets[1].label = 'Actual/Incoming Amount ($ USD)';
                window.chartSupplierAmtInstance.data.datasets[1].data  = supAcAmountUsd;
                window.chartSupplierAmtInstance.update();
            }
        }
    };

    // ── PERSIST ACTIVE SLIDE TAB IN URL HISTORY ──
    document.querySelectorAll('#step6SlideTabs button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const targetId = e.target.getAttribute('data-bs-target');
            let slideKey = 'slide1';
            if (targetId === '#slide2-content') slideKey = 'slide2';
            else if (targetId === '#slide3-content') slideKey = 'slide3';

            const url = new URL(window.location);
            url.searchParams.set('active_slide', slideKey);
            window.history.replaceState({}, '', url);
        });
    });
})();

</script>
@include('partials.confirm-modal')
<script src="{{ asset('js/kawai-notify.js') }}"></script>
<script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
