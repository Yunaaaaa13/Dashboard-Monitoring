<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Master Data Purchasing - PT Kawai Indonesia</title>
    <meta name="description" content="Dashboard Master Forecast PT Kawai Indonesia">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
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
            background: linear-gradient(135deg, var(--accent-emerald) 0%, #059669 100%);
            color: #fff;
            box-shadow: none;
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

        .btn-custom-add {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: all 0.2s ease;
        }
        .btn-custom-add:hover {
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
            color: white;
        }
        .filter-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        .filter-select:focus {
            outline: none;
            border-color: var(--accent-emerald);
        }
        .filter-select option {
            background-color: #111827;
            color: #fff;
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
        .form-control-dark, .form-select-dark {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-radius: 8px;
        }
        .form-select-dark option {
            background-color: #1a2236 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            padding: 8px !important;
        }
        .form-control-dark::placeholder {
            color: #cbd5e1 !important;
            opacity: 0.85 !important;
        }
        .form-control-dark:focus, .form-select-dark:focus {
            background-color: rgba(255, 255, 255, 0.12) !important;
            border-color: var(--accent-gold) !important;
            color: #fff !important;
            box-shadow: 0 0 0 0.15rem rgba(245, 158, 11, 0.12) !important;
        }
        .badge-period {
            background: rgba(245, 158, 11, 0.15);
            color: #FBBF24;
            border: 1px solid rgba(245, 158, 11, 0.3);
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
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
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.outstanding', 'hasFaqModal' => true])
    </div>
</nav>

@include('partials.faq-modal')



<div class="container-dashboard py-4">

    <!-- 7-STEP UNIFIED WORKFLOW STEPPER -->
    @include('partials.workflow-stepper', ['currentStep' => 1])

    <!-- STANDARDIZED PAGE HEADER & ACTION HIERARCHY -->
    <div class="kawai-page-header">
        <div class="kawai-page-header-left">
            <div class="page-icon-box" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.35);">
                <i class="bi bi-graph-up-arrow text-info"></i>
            </div>
            <div>
                <h1 class="page-title-text">Master Forecast &amp; Live Ratio</h1>
                </div>
        </div>
        <div class="kawai-page-actions">
            <button type="button" class="btn-kawai-secondary" data-bs-toggle="modal" data-bs-target="#modalImportPlant3">
                <i class="bi bi-file-earmark-excel-fill text-success"></i> Import Excel
            </button>
            <button type="button" class="btn-kawai-primary" onclick="openAddPlant3Modal()">
                <i class="bi bi-plus-circle-fill"></i> Tambah Item
            </button>
            <div class="dropdown">
                <button class="btn-kawai-more dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Opsi Tambahan">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark-custom dropdown-menu-end">
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.template') }}">
                            <i class="bi bi-download text-info"></i> Unduh Template Excel
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.export') }}">
                            <i class="bi bi-box-arrow-up-right text-warning"></i> Export Data CSV/Excel
                        </a>
                    </li>
                </ul>
            </div>
            @include('partials.kurs-kpi-banner')
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="kawai-filter-bar">
        <form method="GET" action="{{ route('purchasing.outstanding') }}" class="filter-inputs-group">
            <div class="d-flex align-items-center gap-2">
                <label class="text-muted small fw-semibold text-nowrap"><i class="bi bi-funnel me-1"></i> Periode:</label>
                <select name="periode" class="form-select form-select-sm form-select-dark" style="min-width: 150px;" onchange="this.form.submit()">
                    <option value="All">Semua Periode</option>
                    @foreach($periodes as $p)
                        <option value="{{ $p }}" {{ $periode == $p ? 'selected' : '' }}>Periode {{ $p }}</option>
                    @endforeach
                </select>
            </div>
            @if($periode && $periode !== 'All')
                <a href="{{ route('purchasing.outstanding') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Reset Filter">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ═══ KPI MASTER FORECAST ═══ --}}
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12">
            <div class="kpi-card kpi-card-blue">
                <div class="kpi-header">
                    <span class="kpi-title">MASTER FORECAST</span>
                    <div class="kpi-icon-box icon-blue">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <div class="kpi-value text-white">
                    {{ number_format($masterForecastTotalQty) }} <span class="kpi-unit">unit</span>
                </div>
                <div class="kpi-footer">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span class="text-muted small">Total Item SKU Terdaftar</span>
                        <span class="text-white fw-bold small">{{ number_format($masterForecastCount) }} Part</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Pills for Master Forecast + Monitoring PLANT-3 -->
    <div class="mb-4">
        <ul class="nav nav-pills nav-pills-custom" id="masterPills" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-plant3-btn" data-bs-toggle="pill" data-bs-target="#tab-plant3" type="button" role="tab" aria-controls="tab-plant3" aria-selected="true">
                    <i class="bi bi-calendar-range text-gold"></i> Monitoring & Ratio KAWAI
                    <span class="badge bg-warning text-dark ms-1">{{ $items->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-forecast-btn" data-bs-toggle="pill" data-bs-target="#tab-forecast" type="button" role="tab" aria-controls="tab-forecast" aria-selected="false">
                    <i class="bi bi-bar-chart-fill text-primary me-1"></i> Master Forecast (Perencanaan)
                    <span class="badge bg-primary ms-1">{{ $masterForecastCount }}</span>
                </button>
            </li>
            <li class="nav-item ms-auto" role="presentation">
                <button class="nav-link" id="tab-legacy-btn" data-bs-toggle="pill" data-bs-target="#tab-legacy" type="button" role="tab" aria-controls="tab-legacy" aria-selected="false" style="border-color: rgba(255,255,255,0.15);">
                    <i class="bi bi-clipboard-check me-1"></i> Status Pemenuhan Material
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="masterPillsContent">
        
        <!-- ════════════ TAB 0: MONITORING & RATIO PT KAWAI (MULTI-BULAN) ════════════ -->
        <div class="tab-pane fade show active" id="tab-plant3" role="tabpanel" aria-labelledby="tab-plant3-btn">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <div>
                        <h4 class="fw-bold text-white mb-1">
                            <i class="bi bi-calendar-range text-gold me-2"></i> Monitoring PT KAWAI & Live Ratio
                        </h4>
                        </div>
                    
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" id="btnBulkDeletePlant3" class="btn btn-danger btn-sm rounded-pill px-3 d-none" onclick="confirmBulkDeletePlant3()">
                            <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span id="bulkDeleteCountPlant3">0</span>)
                        </button>
                        @if(isset($items) && count($items) > 0)
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="confirmDeleteAllPlant3()" title="Kosongkan seluruh data monitoring & outstanding">
                            <i class="bi bi-trash3 me-1"></i> Hapus Semua Data
                        </button>
                        @endif
                        <form action="{{ route('purchasing.outstanding.months') }}" method="POST" class="d-flex align-items-center gap-2 bg-dark bg-opacity-50 p-2 rounded-4 border border-secondary border-opacity-25">
                            @csrf
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-dark bg-opacity-75 text-warning border border-warning border-opacity-40 px-2.5 py-1.5 rounded-3 fw-bold ms-1" style="font-size:0.8rem;" title="Bulan sebelumnya">
                                    <i class="bi bi-lock-fill me-1 text-warning"></i> Pre-Month: {{ $startMonth }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-1 border-start border-secondary border-opacity-50 ps-2">
                                <label class="text-muted small fw-bold mb-0">Tahun:</label>
                                <select name="start_year" class="form-select form-select-sm form-select-dark border-0 py-1 text-warning fw-bold" style="width: 95px;" onchange="this.form.submit()">
                                    @for($y = date('Y') - 2; $y <= date('Y') + 4; $y++)
                                        <option value="{{ $y }}" {{ (int)($startYear ?? date('Y')) === $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="d-flex align-items-center gap-1 border-start border-secondary border-opacity-50 ps-2">
                                <label class="text-muted small fw-bold mb-0">Jarak:</label>
                                <select name="duration" class="form-select form-select-sm form-select-dark border-0 py-1 text-gold fw-bold" style="width: 150px;" onchange="this.form.submit()">
                                    @for($d = 1; $d <= 36; $d++)
                                        <option value="{{ $d }}" {{ (int)$duration === $d ? 'selected' : '' }}>
                                            {{ $d }} Bulan {{ $d == 12 ? '(1 Thn)' : ($d == 18 ? '(1.5 Thn)' : ($d == 24 ? '(2 Thn)' : ($d == 30 ? '(2.5 Thn)' : ($d == 36 ? '(3 Thn)' : '')))) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row g-3 mb-3 align-items-center">
                    <div class="col-md-5 col-lg-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-dark border-secondary border-opacity-50 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchPlant3" class="form-control form-control-dark border-secondary border-opacity-50" placeholder="Cari Part Number, Description, Supplier..." onkeyup="filterPlant3Table()">
                        </div>
                    </div>
                    <div class="col-md-7 col-lg-8 d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                        <form method="GET" action="{{ route('purchasing.outstanding') }}" class="d-flex align-items-center gap-2 flex-wrap">
                            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                            
                            <label class="text-muted small fw-bold text-nowrap mb-0"><i class="bi bi-building me-1"></i> Vendor:</label>
                            <select name="supplier" class="form-select form-select-sm form-select-dark border-secondary border-opacity-50" style="min-width: 170px; max-width: 220px;" onchange="this.form.submit()">
                                <option value="All">-- Semua Vendor --</option>
                                @foreach($suppliers ?? [] as $sup)
                                    <option value="{{ $sup }}" {{ (($supplierFilter ?? 'All') === $sup) ? 'selected' : '' }}>
                                        {{ strlen($sup) > 22 ? substr($sup, 0, 20) . '...' : $sup }}
                                    </option>
                                @endforeach
                            </select>

                            <label class="text-muted small fw-bold text-nowrap mb-0 ms-1"><i class="bi bi-list-nested me-1"></i> Tampilkan:</label>
                            <select name="per_page" class="form-select form-select-sm form-select-dark border-secondary border-opacity-50" style="width: 110px;" onchange="this.form.submit()">
                                <option value="25" {{ ($perPageParam ?? '50') == '25' ? 'selected' : '' }}>25 baris</option>
                                <option value="50" {{ ($perPageParam ?? '50') == '50' ? 'selected' : '' }}>50 baris</option>
                                <option value="100" {{ ($perPageParam ?? '50') == '100' ? 'selected' : '' }}>100 baris</option>
                                <option value="ALL" {{ ($perPageParam ?? '50') == 'ALL' ? 'selected' : '' }}>Semua Data</option>
                            </select>
                            @if(($supplierFilter ?? 'All') !== 'All')
                                <a href="{{ route('purchasing.outstanding') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 680px; overflow-y: auto;">
                    <table class="table table-custom table-bordered table-hover align-middle mb-0" id="tbl-plant3">
                        <thead class="sticky-top bg-dark" style="z-index: 10;">
                            <tr class="text-center">
                                <th rowspan="3" class="bg-dark text-center align-middle" style="width: 40px;">
                                    <input type="checkbox" id="checkAllPlant3" class="form-check-input cursor-pointer" onchange="toggleSelectAllPlant3(this)" title="Pilih Semua Item">
                                </th>
                                <th rowspan="3" class="bg-dark text-muted align-middle" style="width: 40px;">NO</th>
                                <th rowspan="3" class="bg-dark text-white text-start align-middle" style="min-width: 140px;">ITEM CODE (PK)</th>
                                <th rowspan="3" class="bg-dark text-warning text-center align-middle" style="min-width: 100px;">KODE PABRIK</th>
                                <th rowspan="3" class="bg-dark text-muted text-start align-middle" style="min-width: 150px;">KATEGORI</th>
                                <th rowspan="3" class="bg-dark text-muted text-start align-middle" style="min-width: 180px;">DESCRIPTION</th>
                                <th rowspan="3" class="bg-dark text-info text-start align-middle" style="min-width: 180px;">SUPPLIER / VENDOR</th>
                                <th rowspan="3" class="bg-dark text-warning text-end text-nowrap align-middle" style="min-width: 100px; color: #fbbf24; white-space: nowrap;">PRICE</th>
                                <th rowspan="3" class="bg-dark text-info text-center text-nowrap align-middle" style="min-width: 80px; color: #38bdf8; white-space: nowrap;">CURRENCY</th>
                                
                                <!-- Month 0 Header -->
                                <th colspan="5" class="bg-primary bg-opacity-25 text-warning border-end border-secondary py-2" style="font-size: 0.8rem;">
                                    <i class="bi bi-calendar-event me-1"></i> {{ $startMonth }} (MULAI)
                                </th>

                                <!-- Subsequent Months -->
                                @for($i = 1; $i <= $duration; $i++)
                                    <th colspan="11" class="bg-secondary bg-opacity-50 text-light border-secondary py-2 text-nowrap" style="white-space: nowrap;">
                                        <i class="bi bi-calendar-check me-1"></i> {{ $months[$i] ?? 'M'.$i }}
                                    </th>
                                @endfor

                                <th rowspan="3" class="bg-dark text-end align-middle" style="width: 110px;">AKSI</th>
                            </tr>
                            <tr class="text-center" style="font-size: 0.72rem;">
                                <!-- Month 0 Groups -->
                                <th colspan="2" class="bg-primary bg-opacity-10 text-danger border-end border-secondary border-opacity-25">OUTSTANDING</th>
                                <th colspan="2" class="bg-primary bg-opacity-10 text-primary border-end border-secondary border-opacity-25">STOCK</th>
                                <th rowspan="2" class="bg-primary bg-opacity-10 text-primary align-middle" style="min-width: 70px;">%</th>

                                <!-- Subsequent Month Groups -->
                                @for($i = 1; $i <= $duration; $i++)
                                    <th colspan="2" class="bg-dark text-info border-end border-secondary border-opacity-25">PO</th>
                                    <th colspan="2" class="bg-dark text-primary border-end border-secondary border-opacity-25">FORECAST</th>
                                    <th colspan="2" class="bg-dark text-success border-end border-secondary border-opacity-25">INCOMING</th>
                                    <th rowspan="2" class="bg-dark text-danger align-middle text-nowrap" style="white-space: nowrap; min-width: 90px;">OUTSTANDING</th>
                                    <th rowspan="2" class="bg-dark text-warning align-middle text-nowrap" style="white-space: nowrap; min-width: 70px;">PROD</th>
                                    <th colspan="2" class="bg-dark text-success border-end border-secondary border-opacity-25">STOCK</th>
                                    <th rowspan="2" class="bg-dark text-gold align-middle text-nowrap" style="white-space: nowrap; min-width: 70px;">%</th>
                                @endfor
                            </tr>
                            <tr class="text-center" style="font-size: 0.68rem;">
                                <!-- Month 0 Subheaders -->
                                <th class="bg-primary bg-opacity-10 text-danger text-nowrap" style="white-space: nowrap; min-width: 80px;">QTY</th>
                                <th class="bg-primary bg-opacity-10 text-danger text-nowrap" style="white-space: nowrap; min-width: 130px;">AMOUNT</th>
                                <th class="bg-primary bg-opacity-10 text-primary text-nowrap" style="white-space: nowrap; min-width: 80px;">QTY</th>
                                <th class="bg-primary bg-opacity-10 text-primary text-nowrap" style="white-space: nowrap; min-width: 130px;">AMOUNT</th>

                                <!-- Subsequent Month Subheaders -->
                                @for($i = 1; $i <= $duration; $i++)
                                    <th class="bg-dark text-info text-nowrap" style="white-space: nowrap; min-width: 75px;">QTY</th>
                                    <th class="bg-dark text-info text-nowrap" style="white-space: nowrap; min-width: 120px;">AMOUNT</th>
                                    <th class="bg-dark text-primary text-nowrap" style="white-space: nowrap; min-width: 75px;">QTY</th>
                                    <th class="bg-dark text-primary text-nowrap" style="white-space: nowrap; min-width: 120px;">AMOUNT</th>
                                    <th class="bg-dark text-success text-nowrap" style="white-space: nowrap; min-width: 75px;">QTY</th>
                                    <th class="bg-dark text-success text-nowrap" style="white-space: nowrap; min-width: 120px;">AMOUNT</th>
                                    <th class="bg-dark text-success text-nowrap" style="white-space: nowrap; min-width: 75px;">QTY</th>
                                    <th class="bg-dark text-success text-nowrap" style="white-space: nowrap; min-width: 120px;">AMOUNT</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $idx => $item)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="row-checkbox-plant3 form-check-input cursor-pointer" value="{{ $item->id }}" onchange="updatePlant3BulkBtn()">
                                    </td>
                                    <td class="text-center text-light fw-bold">{{ $idx + 1 }}</td>
                                    <td class="text-start">
                                         <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2 py-1 fs-6 font-monospace"><i class="bi bi-upc-scan me-1"></i>{{ $item->part_number ?: ($item->drawing ?: '-') }}</span>
                                    </td>
                                    <td class="text-center">
                                         <span class="badge bg-warning bg-opacity-25 text-warning border border-secondary border-opacity-25 px-2 py-1 font-monospace"><i class="bi bi-building-gear me-1"></i>{{ $item->factory_code ?: 'KIP 1' }}</span>
                                    </td>
                                    <td>
                                        @if($item->category)
                                            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 text-wrap text-start">{{ $item->category->category_code }}<br>{{ $item->category->category_name }}</span>
                                        @else
                                            <span class="text-muted small">Tanpa Kategori</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-white">{{ $item->description }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-info small"><i class="bi bi-building me-1"></i>{{ $item->supplier_name ?: '-' }}</span>
                                    </td>
                                    <td class="text-end font-monospace fw-semibold text-nowrap" style="color:#e2b34a; white-space: nowrap; font-size: 0.82rem;">
                                        {{ $item->price > 0 ? number_format($item->price, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ strtoupper($item->currency ?? 'USD') === 'IDR' ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-info bg-opacity-25 text-info border border-info' }} px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                            {{ strtoupper($item->currency ?? 'USD') }}
                                        </span>
                                    </td>

                                    <!-- Month 0 -->
                                    <td class="text-end font-monospace text-danger bg-primary bg-opacity-10 fw-bold text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($item->plan_outstand ?? 0) }}</td>
                                    <td class="text-end font-monospace fw-bold bg-primary bg-opacity-10 text-nowrap {{ $item->plan_outstand_amount < 0 ? 'text-danger' : 'text-light' }}" style="white-space: nowrap; font-size: 0.82rem;">
                                        {{ $item->formatAmount($item->plan_outstand_amount) }}
                                    </td>
                                    <td class="text-end fw-bold text-primary bg-primary bg-opacity-10 text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($item->plan_stock) }}</td>
                                    <td class="text-end font-monospace fw-bold bg-primary bg-opacity-10 text-nowrap {{ $item->plan_stock_amount < 0 ? 'text-danger' : 'text-info' }}" style="white-space: nowrap; font-size: 0.82rem;">
                                        {{ $item->formatAmount($item->plan_stock_amount) }}
                                    </td>
                                    <td class="text-center fw-bold bg-primary bg-opacity-10 text-nowrap" style="white-space: nowrap;">
                                        @php $r0 = $item->getRatioForMonth(0); @endphp
                                        <span class="badge {{ $item->getRatioBadgeClass($r0) }}">{{ $r0 ?? '-' }}</span>
                                    </td>

                                    <!-- Subsequent Months -->
                                    @for($i = 1; $i <= $duration; $i++)
                                        @php
                                            $poI      = $item->getPoForMonth($i);
                                            $poAmtI   = $poI * $item->price;
                                            $fcI      = $item->getForecastForMonth($i);
                                            $fcAmtI   = $fcI * $item->price;
                                            $delI     = $item->getDeliveryForMonth($i);
                                            $delAmtI  = $delI * $item->price;
                                            $outI     = $item->getOutstandingForMonth($i);
                                            $stkI     = $item->getStockForMonth($i);
                                            $stkAmtI  = $stkI * $item->price;
                                            $ratioI   = $item->getRatioForMonth($i);
                                        @endphp
                                        <td class="text-end font-monospace text-info fw-bold text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($poI) }}</td>
                                        <td class="text-end font-monospace text-info fw-bold text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ $item->formatAmount($poAmtI) }}</td>
                                        <td class="text-end font-monospace text-primary fw-bold text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($fcI) }}</td>
                                        <td class="text-end font-monospace text-primary fw-bold text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ $item->formatAmount($fcAmtI) }}</td>
                                        <td class="text-end font-monospace text-success fw-bold text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($delI) }}</td>
                                        <td class="text-end font-monospace text-success fw-bold text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ $item->formatAmount($delAmtI) }}</td>
                                        <td class="text-end font-monospace fw-bold text-nowrap {{ $outI < 0 ? 'text-danger' : 'text-light' }}" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($outI) }}</td>
                                        <td class="text-end font-monospace text-warning text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($item->getProdForMonth($i)) }}</td>
                                        <td class="text-end font-monospace fw-bold text-success text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($stkI) }}</td>
                                        <td class="text-end font-monospace fw-bold text-success text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ $item->formatAmount($stkAmtI) }}</td>
                                        <td class="text-center fw-bold text-nowrap" style="white-space: nowrap;">
                                            <span class="badge {{ $item->getRatioBadgeClass($ratioI) }}">{{ $ratioI ?? '-' }}</span>
                                        </td>
                                    @endfor

                                    <!-- Actions -->
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1 me-1" 
                                            onclick="openEditPlant3Modal({{ $item->id }}, '{{ addslashes($item->part_number) }}', '{{ addslashes($item->description) }}', '{{ addslashes($item->supplier_name ?? '') }}', '{{ addslashes($item->drawing ?? '-') }}', {{ $item->plan_stock }}, {{ $item->plan_outstand ?? 0 }}, {{ json_encode([
                                                1 => ['po' => $item->getPoForMonth(1), 'prod' => $item->m1_prod ?? 0],
                                                2 => ['po' => $item->getPoForMonth(2), 'prod' => $item->m2_prod ?? 0],
                                                3 => ['po' => $item->getPoForMonth(3), 'prod' => $item->m3_prod ?? 0],
                                                4 => ['po' => $item->getPoForMonth(4), 'prod' => $item->m4_prod ?? 0],
                                                5 => ['po' => $item->getPoForMonth(5), 'prod' => $item->m5_prod ?? 0],
                                                6 => ['po' => $item->getPoForMonth(6), 'prod' => $item->m6_prod ?? 0],
                                                7 => ['po' => $item->getPoForMonth(7), 'prod' => $item->m7_prod ?? 0],
                                                8 => ['po' => $item->getPoForMonth(8), 'prod' => $item->m8_prod ?? 0],
                                                9 => ['po' => $item->getPoForMonth(9), 'prod' => $item->m9_prod ?? 0],
                                                10 => ['po' => $item->getPoForMonth(10), 'prod' => $item->m10_prod ?? 0],
                                                11 => ['po' => $item->getPoForMonth(11), 'prod' => $item->m11_prod ?? 0],
                                                12 => ['po' => $item->getPoForMonth(12), 'prod' => $item->m12_prod ?? 0],
                                            ]) }}, {{ $item->category_id ?? 'null' }}, {{ $item->user_id ?? 'null' }}, '{{ addslashes($item->pic_buyer ?? '') }}', {{ $item->price }})" 
                                            title="Edit PO, PROD & Stock Multi-Bulan">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form id="deleteMonitoringForm{{ $item->id }}" action="{{ route('purchasing.outstanding.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Hapus" onclick="KawaiConfirm.delete('Hapus Data Monitoring', 'Data monitoring item {{ $item->item_code }} akan dihapus.', () => document.getElementById('deleteMonitoringForm{{ $item->id }}').submit())">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 15 + ($duration * 11) }}" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-warning"></i>
                                        Belum ada data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasPages())
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top border-secondary border-opacity-25">
                        <div class="text-muted small">
                            Menampilkan <b>{{ $items->firstItem() ?? 0 }}</b> - <b>{{ $items->lastItem() ?? 0 }}</b> dari total <b>{{ $items->total() }}</b> Part Number
                        </div>
                        <div class="d-flex align-items-center">
                            {{ $items->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ════════════ TAB 3: MASTER FORECAST ════════════ -->
        <div class="tab-pane fade" id="tab-forecast" role="tabpanel" aria-labelledby="tab-forecast-btn">
            <div class="glass-card p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25 gap-2">
                    <div>
                        <h4 class="fw-bold text-white mb-1"><i class="bi bi-bar-chart-fill text-primary me-2"></i> Tabel Master Forecast (Perencanaan Kebutuhan)</h4>
                        <p class="text-muted small mb-0">Daftar perencanaan kebutuhan material yang diestimasi untuk proses produksi PT Kawai Indonesia.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="btnBulkDeleteForecast" class="btn btn-danger btn-sm rounded-pill px-3 d-none" onclick="confirmBulkDeleteForecast()">
                            <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span id="bulkDeleteCountForecast">0</span>)
                        </button>
                        <span class="badge bg-dark border border-secondary text-light font-monospace px-3 py-2">
                            Total: {{ $masterForecastCount }} Data Forecast
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark-custom align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="text-center align-middle" rowspan="2">
                                    <input type="checkbox" id="checkAllForecast" class="form-check-input cursor-pointer" onchange="toggleSelectAllForecast(this)" title="Pilih Semua Forecast">
                                </th>
                                <th style="width: 50px;" rowspan="2" class="align-middle">No</th>
                                <th rowspan="2" class="align-middle">Part Number (Item Code)</th>
                                <th rowspan="2" class="align-middle text-center text-warning">Kode Pabrik</th>
                                <th rowspan="2" class="align-middle">Description</th>
                                <th rowspan="2" class="align-middle text-info">Supplier / Vendor</th>
                                <th rowspan="2" class="align-middle">Periode</th>
                                <th rowspan="2" class="text-end text-nowrap" style="color:#e2b34a; min-width: 100px; white-space: nowrap; vertical-align: middle;">Price</th>
                                <th rowspan="2" class="text-center text-nowrap" style="color:#38bdf8; min-width: 80px; white-space: nowrap; vertical-align: middle;">Currency</th>
                                <th colspan="2" class="text-center bg-danger bg-opacity-10 text-danger border-bottom-0 text-nowrap" style="white-space: nowrap;">OUTSTANDING</th>
                                <th rowspan="2" class="text-end align-middle" style="color:#f59e0b;">PO (Step 2)</th>
                                <th rowspan="2" class="text-end align-middle" style="color:#60a5fa;">Forecast Qty</th>
                                <th rowspan="2" class="text-end align-middle" style="color:#10b981;">Incoming (Step 3)</th>
                                <th colspan="2" class="text-center bg-success bg-opacity-10 text-success border-bottom-0 text-nowrap" style="white-space: nowrap;">STOCK AKHIR</th>
                                <th class="text-center align-middle" rowspan="2" style="width: 120px;">Aksi</th>
                            </tr>
                            <tr>
                                <th class="text-end bg-danger bg-opacity-10 text-danger small text-nowrap" style="white-space: nowrap; min-width: 90px;">QTY</th>
                                <th class="text-end bg-danger bg-opacity-10 text-danger small text-nowrap" style="white-space: nowrap; min-width: 140px;">AMOUNT</th>
                                <th class="text-end bg-success bg-opacity-10 text-success small text-nowrap" style="white-space: nowrap; min-width: 90px;">QTY</th>
                                <th class="text-end bg-success bg-opacity-10 text-success small text-nowrap" style="white-space: nowrap; min-width: 140px;">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($forecastList as $index => $item)
                            @php
                                $po = $item->calculated_po;
                                $delivery = $item->calculated_delivery;
                                $price = (float) $item->price;
                                $outQty = $item->calculated_outstanding;
                                $outAmt = $item->calculated_outstanding_amount;
                                $stkQty = $item->calculated_stock;
                                $stkAmt = $item->calculated_stock_amount;
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="row-checkbox-forecast form-check-input cursor-pointer" value="{{ $item->id }}" onchange="updateForecastBulkBtn()">
                                </td>
                                <td class="text-muted fw-semibold">{{ $index + 1 }}</td>
                                <td class="fw-bold text-white">{{ $item->part_number }}</td>
                                <td class="text-center"><span class="badge bg-warning bg-opacity-25 text-warning border border-secondary border-opacity-25 px-2 py-1 font-monospace">{{ $item->factory_code ?: 'KIP 1' }}</span></td>
                                <td>{{ $item->description ?? '-' }}</td>
                                <td><span class="fw-medium text-info small">{{ $item->supplier_name ?? '-' }}</span></td>
                                <td><span class="badge-period">{{ $item->periode }}</span></td>
                                <td class="text-end font-monospace fw-semibold text-nowrap" style="color:#e2b34a; white-space: nowrap; font-size: 0.82rem;">
                                    {{ $price > 0 ? number_format($price, 2, ',', '.') : '-' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ strtoupper($item->currency ?? 'USD') === 'IDR' ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-info bg-opacity-25 text-info border border-info' }} px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                        {{ strtoupper($item->currency ?? 'USD') }}
                                    </span>
                                </td>
                                <td class="text-end font-monospace fw-bold text-nowrap {{ $outQty < 0 ? 'text-danger' : 'text-light' }}" style="white-space: nowrap; font-size: 0.82rem;">
                                    {{ number_format($outQty) }}
                                </td>
                                <td class="text-end font-monospace fw-bold text-nowrap {{ $outAmt < 0 ? 'text-danger' : 'text-light' }}" style="white-space: nowrap; font-size: 0.82rem;">
                                    {{ $item->formatAmount($outAmt) }}
                                </td>
                                <td class="text-end font-monospace fw-bold text-warning">
                                    @if($po > 0)
                                        {{ number_format($po) }}
                                    @else
                                        <span class="badge bg-dark border border-secondary text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-primary fs-6">{{ number_format($item->forecast_qty) }}</td>
                                <td class="text-end font-monospace fw-bold text-success">
                                    @if($delivery > 0)
                                        {{ number_format($delivery) }}
                                    @else
                                        <span class="badge bg-dark border border-secondary text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace text-info fw-bold text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($stkQty) }}</td>
                                <td class="text-end font-monospace text-info fw-bold text-nowrap {{ $stkAmt < 0 ? 'text-danger' : '' }}" style="white-space: nowrap; font-size: 0.82rem;">
                                    {{ $item->formatAmount($stkAmt) }}
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info btn-action me-1" 
                                            onclick="editForecast({{ $item->id }}, '{{ $item->part_number }}', '{{ addslashes($item->description ?? '') }}', '{{ $item->periode }}', {{ $item->forecast_qty }}, {{ $price }})"
                                            title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form id="deleteForecastForm{{ $item->id }}" action="{{ route('purchasing.master.forecast.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Hapus Data" onclick="KawaiConfirm.delete('Hapus Data Forecast', 'Data forecast part {{ $item->part_number }} ({{ $item->periode }}) akan dihapus.', () => document.getElementById('deleteForecastForm{{ $item->id }}').submit())">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="14" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    Belum ada data Master Forecast @if($periode && $periode !== 'All') pada periode <b>{{ $periode }}</b> @endif.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($forecastList instanceof \Illuminate\Pagination\LengthAwarePaginator && $forecastList->hasPages())
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top border-secondary border-opacity-25">
                        <div class="text-muted small">
                            Menampilkan <b>{{ $forecastList->firstItem() ?? 0 }}</b> - <b>{{ $forecastList->lastItem() ?? 0 }}</b> dari total <b>{{ $forecastList->total() }}</b> Data Forecast
                        </div>
                        <div class="d-flex align-items-center">
                            {{ $forecastList->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ════════════ TAB 4: MONITORING STATUS PEMENUHAN MATERIAL (REVISI OPERASIONAL) ════════════ -->
        <div class="tab-pane fade" id="tab-legacy" role="tabpanel" aria-labelledby="tab-legacy-btn">
            <div class="glass-card p-4">
                <!-- Header -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25 gap-2">
                    <div>
                        <h4 class="fw-bold text-white mb-1"><i class="bi bi-clipboard-check text-info me-2"></i> Monitoring Status Pemenuhan Material</h4>
                        </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary px-3 py-2 rounded-pill small">
                            <i class="bi bi-calendar-check me-1"></i> Periode: {{ $periode && $periode !== 'All' ? $periode : 'Semua Periode' }}
                        </span>
                    </div>
                </div>

                <!-- KPI Section (4 Cards) -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background: rgba(96, 165, 250, 0.08); border: 1px solid rgba(96, 165, 250, 0.25);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-semibold d-block">FORECAST QTY</span>
                                <i class="bi bi-graph-up text-primary fs-5"></i>
                            </div>
                            <h3 class="fw-bold text-info mb-0 mt-2">{{ number_format($monitoringTotalForecast ?? 0) }} <small class="fs-6 text-muted">unit</small></h3>
                            <div class="small text-muted mt-1">Target kebutuhan material</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background: rgba(52, 211, 153, 0.08); border: 1px solid rgba(52, 211, 153, 0.25);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-semibold d-block">ACTUAL QTY</span>
                                <i class="bi bi-box-seam text-success fs-5"></i>
                            </div>
                            <h3 class="fw-bold text-success mb-0 mt-2">{{ number_format($monitoringTotalActual ?? 0) }} <small class="fs-6 text-muted">unit</small></h3>
                            <div class="small text-muted mt-1">Total kedatangan material</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background: rgba(251, 191, 36, 0.08); border: 1px solid rgba(251, 191, 36, 0.25);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-semibold d-block">OUTSTANDING QTY</span>
                                <i class="bi bi-clock-history text-warning fs-5"></i>
                            </div>
                            <h3 class="fw-bold text-warning mb-0 mt-2">{{ number_format($monitoringTotalOutstanding ?? 0) }} <small class="fs-6 text-muted">unit</small></h3>
                            <div class="small text-muted mt-1">Total pesanan belum dipenuhi</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded-3" style="background: rgba(168, 85, 247, 0.08); border: 1px solid rgba(168, 85, 247, 0.25);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-semibold d-block">FULFILLMENT (%)</span>
                                <i class="bi bi-pie-chart fs-5" style="color: #c084fc;"></i>
                            </div>
                            <h3 class="fw-bold mb-0 mt-2" style="color: #c084fc;">{{ $monitoringFulfillmentPct ?? 0 }}%</h3>
                            </div>
                    </div>
                </div>

                <!-- Tabel Operasional 8 Kolom -->
                <div class="table-responsive mb-5">
                    <table class="table table-dark-custom align-middle table-hover">
                        <thead>
                            <tr>
                                <th style="min-width: 140px;">No. PO / Ref</th>
                                <th style="min-width: 220px;">Description & Item</th>
                                <th class="text-end" style="min-width: 110px;">Forecast</th>
                                <th class="text-end" style="min-width: 110px;">Actual</th>
                                <th class="text-end" style="min-width: 110px;">Outstanding</th>
                                <th class="text-center" style="min-width: 120px;">Fulfillment</th>
                                <th class="text-center" style="min-width: 150px;">Status</th>
                                <th class="text-center" style="min-width: 190px;">Recommendation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fulfillmentMonitoringList ?? [] as $row)
                            <tr>
                                <td>
                                    <span class="fw-bold text-warning font-monospace">{{ $row->part_number }}</span>
                                    @if(!$periode || $periode === 'All')
                                        <div class="small text-muted"><i class="bi bi-calendar2 me-1"></i>{{ $row->periode }}</div>
                                    @endif
                                </td>
                                <td class="text-white fw-semibold">{{ $row->description }}</td>
                                <td class="text-end fw-bold text-info">{{ number_format($row->forecast_qty) }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($row->actual_qty) }}</td>
                                <td class="text-end fw-bold text-warning">{{ number_format($row->outstanding_qty) }}</td>
                                <td class="text-center">
                                    @if($row->fulfillment_pct !== null)
                                        <span class="badge {{ $row->fulfillment_pct >= 100 ? 'bg-success' : ($row->fulfillment_pct >= 80 ? 'bg-info text-dark' : 'bg-warning text-dark') }} px-2 py-1">
                                            {{ $row->fulfillment_pct }}%
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-50 text-muted px-2 py-1">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="{{ $row->status_badge }}">{{ $row->status }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="{{ $row->recommendation_badge }}">{{ $row->recommendation }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Belum ada data pemenuhan material untuk filter ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Collapsible Arsip Data PO Legacy -->
                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <button class="btn btn-sm btn-outline-secondary w-100 d-flex justify-content-between align-items-center p-2 rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLegacyArchive" aria-expanded="false" aria-controls="collapseLegacyArchive">
                        <span class="small fw-semibold text-light"><i class="bi bi-archive me-2"></i> Lihat Arsip Data Monitoring PO Legacy (12 Bulan)</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse mt-3" id="collapseLegacyArchive">
                        <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.25); border: 1px dashed rgba(255,255,255,0.15);">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small text-muted">Data historis monitoring progres pemenuhan per Nomor PO:</span>
                                <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2 py-1 small" data-bs-toggle="modal" data-bs-target="#modalDuration">
                                    <i class="bi bi-calendar-range me-1"></i> Atur Bulan Monitoring ({{ $duration }} Bulan)
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-dark-custom align-middle">
                                    <thead>
                                        <tr>
                                            <th>No. PO / Ref</th>
                                            <th>Supplier</th>
                                            <th>No. PO & Description</th>
                                            <th>Item Code</th>
                                            <th class="text-end">Harga Satuan</th>
                                            <th class="text-end">Order Qty</th>
                                            <th class="text-end">Complete</th>
                                            <th class="text-end">Pending</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($items as $i => $item)
                                        <tr>
                                            <td class="fw-bold text-warning">{{ $item->po_number ?? '-' }}</td>
                                            <td>{{ $item->supplier_name ?? '-' }}</td>
                                            <td>
                                                <div class="fw-bold text-white">{{ $item->part_number }}</div>
                                                <div class="small text-muted">{{ $item->description }}</div>
                                            </td>
                                            <td>{{ $item->drawing ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold">{{ number_format($item->order_qty) }}</td>
                                            <td class="text-end text-success fw-bold">{{ number_format($item->complete) }}</td>
                                            <td class="text-end text-danger fw-bold">{{ number_format(max(0, $item->order_qty - $item->complete)) }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ $item->status }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">Belum ada data monitoring legacy.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ════════════ MODALS TAMBAH & EDIT OUTSTANDING ════════════ -->
<div class="modal fade" id="modalAddOutstanding" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-dark">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-warning"><i class="bi bi-box-seam me-2"></i> Tambah Master Outstanding</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchasing.master.outstanding.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-light small fw-semibold">1. Item Code (Drawing) <span class="text-danger">*</span></label>
                        <input type="text" name="drawing" class="form-control form-control-dark border-primary" placeholder="Contoh: ITM-0001 / DWG-SPEC">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-semibold">2. No. PO / Ref (Part Number) <span class="text-danger">*</span></label>
                        <input type="text" name="part_number" class="form-control form-control-dark border-success" required placeholder="Contoh: PO-KW-0726">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control form-control-dark" placeholder="Nama part / spesifikasi material">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Periode <span class="text-danger">*</span></label>
                            <input type="text" name="periode" class="form-control form-control-dark" required placeholder="YYYY-MM (e.g. 2026-07)" value="{{ date('Y-m') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Nomor PO Alternatif</label>
                            <input type="text" name="po" class="form-control form-control-dark" placeholder="Contoh: PO-2026-001">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Outstanding Qty <span class="text-danger">*</span></label>
                        <input type="number" name="outstanding_qty" class="form-control form-control-dark" required min="0" placeholder="0">
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark rounded-pill px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditOutstanding" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-dark">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-info"><i class="bi bi-pencil-square me-2"></i> Edit Master Outstanding</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditOutstanding" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-light small fw-semibold">1. Item Code (Drawing)</label>
                        <input type="text" name="drawing" id="edit_out_drawing" class="form-control form-control-dark border-primary">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-semibold">2. No. PO / Ref (Part Number) <span class="text-danger">*</span></label>
                        <input type="text" name="part_number" id="edit_out_part_number" class="form-control form-control-dark border-success" required readonly style="opacity: 0.7;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Description</label>
                        <input type="text" name="description" id="edit_out_description" class="form-control form-control-dark">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Periode <span class="text-danger">*</span></label>
                            <input type="text" name="periode" id="edit_out_periode" class="form-control form-control-dark" required readonly style="opacity: 0.7;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Nomor PO Alternatif</label>
                            <input type="text" name="po" id="edit_out_po" class="form-control form-control-dark">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Outstanding Qty <span class="text-danger">*</span></label>
                        <input type="number" name="outstanding_qty" id="edit_out_qty" class="form-control form-control-dark" required min="0">
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info fw-bold rounded-pill px-4">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════ MODALS TAMBAH & EDIT ACTUAL ════════════ -->
<div class="modal fade" id="modalAddActual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-dark">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle me-2"></i> Tambah Master Actual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchasing.master.actual.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">No. PO / Ref (Part Number) <span class="text-danger">*</span></label>
                        <input type="text" name="part_number" class="form-control form-control-dark" required placeholder="Contoh: PO-KW-0726">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control form-control-dark" placeholder="Nama part / spesifikasi material">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Periode <span class="text-danger">*</span></label>
                            <input type="text" name="periode" class="form-control form-control-dark" required placeholder="YYYY-MM (e.g. 2026-07)" value="{{ date('Y-m') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Actual Qty <span class="text-danger">*</span></label>
                            <input type="number" name="actual_qty" class="form-control form-control-dark" required min="0" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditActual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-dark">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-info"><i class="bi bi-pencil-square me-2"></i> Edit Master Actual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditActual" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">No. PO / Ref (Part Number) <span class="text-danger">*</span></label>
                        <input type="text" name="part_number" id="edit_act_part_number" class="form-control form-control-dark" required readonly style="opacity: 0.7;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Description</label>
                        <input type="text" name="description" id="edit_act_description" class="form-control form-control-dark">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Periode <span class="text-danger">*</span></label>
                            <input type="text" name="periode" id="edit_act_periode" class="form-control form-control-dark" required readonly style="opacity: 0.7;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Actual Qty <span class="text-danger">*</span></label>
                            <input type="number" name="actual_qty" id="edit_act_qty" class="form-control form-control-dark" required min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info fw-bold rounded-pill px-4">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════ MODALS TAMBAH & EDIT FORECAST ════════════ -->
<div class="modal fade" id="modalAddForecast" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-dark">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-graph-up me-2"></i> Tambah Master Forecast</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchasing.master.forecast.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">No. PO / Ref (Part Number) <span class="text-danger">*</span></label>
                            <input type="text" name="part_number" class="form-control form-control-dark" required placeholder="Contoh: PO-KW-0726">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-warning small fw-semibold">Kode Pabrik (Factory Code) <span class="text-danger">*</span></label>
                            <select name="factory_code" class="form-select form-select-dark text-warning border-secondary fw-bold">
                                <option value="KIP 1" selected>KIP 1</option>
                                <option value="KIP 2">KIP 2</option>
                                <option value="KIP 3">KIP 3</option>
                                <option value="KIP 4">KIP 4</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control form-control-dark" placeholder="Nama part / spesifikasi material">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Periode <span class="text-danger">*</span></label>
                            <input type="text" name="periode" class="form-control form-control-dark" required placeholder="YYYY-MM (e.g. 2026-07)" value="{{ date('Y-m') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Forecast Qty <span class="text-danger">*</span></label>
                            <input type="number" name="forecast_qty" class="form-control form-control-dark" required min="0" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-warning small fw-semibold">Price & Currency</label>
                            <div class="input-group">
                                <select name="currency" class="form-select form-select-dark text-warning border-secondary fw-bold px-1" style="max-width: 80px;">
                                    <option value="USD" selected>USD</option>
                                    <option value="IDR">IDR</option>
                                </select>
                                <input type="text" inputmode="decimal" name="price" class="form-control form-control-dark" placeholder="0.00 (cth: 227,05)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditForecast" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-dark">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-info"><i class="bi bi-pencil-square me-2"></i> Edit Master Forecast</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditForecast" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">No. PO / Ref (Part Number) <span class="text-danger">*</span></label>
                        <input type="text" name="part_number" id="edit_for_part_number" class="form-control form-control-dark" required readonly style="opacity: 0.7;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Description</label>
                        <input type="text" name="description" id="edit_for_description" class="form-control form-control-dark">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Periode <span class="text-danger">*</span></label>
                            <input type="text" name="periode" id="edit_for_periode" class="form-control form-control-dark" required readonly style="opacity: 0.7;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Forecast Qty <span class="text-danger">*</span></label>
                            <input type="number" name="forecast_qty" id="edit_for_qty" class="form-control form-control-dark" required min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-warning small fw-semibold">Price & Currency</label>
                            <div class="input-group">
                                <select name="currency" id="edit_for_currency" class="form-select form-select-dark text-warning border-secondary fw-bold px-1" style="max-width: 80px;">
                                    <option value="USD">USD</option>
                                    <option value="IDR">IDR</option>
                                </select>
                                <input type="text" inputmode="decimal" name="price" id="edit_for_price" class="form-control form-control-dark" placeholder="0.00 (cth: 227,05)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info fw-bold rounded-pill px-4">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Atur Durasi Monitoring Legacy -->
<div class="modal fade" id="modalDuration" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-dark">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-calendar-range me-2"></i> Atur Bulan Monitoring Legacy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchasing.outstanding.months') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Bulan Mulai (Start Month)</label>
                        <select name="start_month" class="form-select form-select-dark">
                            @foreach(['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $m)
                                <option value="{{ $m }}" {{ $startMonth == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Durasi Tampilan</label>
                        <select name="duration" class="form-select form-select-dark">
                            @foreach([3, 4, 6, 12] as $d)
                                <option value="{{ $d }}" {{ $duration == $d ? 'selected' : '' }}>{{ $d }} Bulan</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark rounded-pill px-4">Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- ════════════ MODAL ADD DATA & LIVE RATIO PT KAWAI ════════════ -->
<div class="modal fade" id="modalAddPlant3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border border-secondary border-opacity-25 shadow-lg" style="background: #0f172a !important; border-radius: 20px; color: #ffffff;">
            <div class="modal-header px-4 py-3 border-bottom border-secondary border-opacity-25" style="background: #1e293b !important;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill text-warning fs-4"></i>
                    <span>Input Incoming & Kalkulasi Ratio Multi-Bulan (PT KAWAI)</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: #0b1120 !important;">
                <form action="{{ route('purchasing.outstanding.store') }}" method="POST" id="formAddPlant3">
                    @csrf
                    <!-- Pilih Durasi & Bulan Mulai Monitoring -->
                    <div class="row g-3 mb-4 p-3 rounded-3 border border-secondary border-opacity-25 align-items-center shadow-sm" style="background: rgba(245, 158, 11, 0.08);">
                        <div class="col-md-4">
                            <label class="form-label text-warning small fw-bold mb-1"><i class="bi bi-calendar-check me-1"></i> Bulan Mulai (Start Month)</label>
                            <select name="start_month" id="add_start_month" class="form-select border-warning text-warning fw-bold" onchange="updateModalMonthsDisplay('add')" style="background-color: #1a2234 !important; color: #fbbf24 !important;">
                                @foreach(['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $m)
                                    <option value="{{ $m }}" {{ ($startMonth ?? 'JAN') == $m ? 'selected' : '' }} style="background-color: #1a2234; color: #fbbf24;">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-warning small fw-bold mb-1"><i class="bi bi-calendar3 me-1"></i> Tahun (Start Year)</label>
                            <select name="start_year" id="add_start_year" class="form-select border-warning text-warning fw-bold" onchange="updateModalMonthsDisplay('add')" style="background-color: #1a2234 !important; color: #fbbf24 !important;">
                                @for($y = date('Y') - 2; $y <= date('Y') + 4; $y++)
                                    <option value="{{ $y }}" {{ ($startYear ?? date('Y')) == $y ? 'selected' : '' }} style="background-color: #1a2234; color: #fbbf24;">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-warning small fw-bold mb-1"><i class="bi bi-clock-history me-1"></i> Pilih Jumlah Bulan Tampilan</label>
                            <select name="duration" id="add_duration_months" class="form-select border-warning text-warning fw-bold" onchange="updateModalMonthsDisplay('add')" style="background-color: #1a2234 !important; color: #fbbf24 !important;">
                                @for($d = 1; $d <= 36; $d++)
                                    <option value="{{ $d }}" {{ ($duration ?? 12) == $d ? 'selected' : '' }} style="background-color: #1a2234; color: #fbbf24;">
                                        {{ $d }} Bulan {{ $d == 12 ? '(1 Tahun)' : ($d == 18 ? '(1.5 Tahun)' : ($d == 24 ? '(2 Tahun)' : ($d == 30 ? '(2.5 Tahun)' : ($d == 36 ? '(3 Tahun)' : '')))) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="badge bg-warning text-dark px-3 py-2 fw-bold d-block mt-3 mt-md-0 shadow-sm" id="add_duration_badge" style="font-size: 0.85rem;">
                                Tampil: {{ $duration ?? 4 }} Bulan
                            </span>
                        </div>
                    </div>

                    <!-- Header Information -->
                    <div class="row g-3 mb-4 p-3 rounded-3 border border-secondary border-opacity-25" style="background: rgba(30, 41, 59, 0.6);">
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold d-flex justify-content-between align-items-center mb-1">
                                <span>Item Code (Drawing)</span>
                                <button type="button" class="btn p-0 text-info text-decoration-none small fw-bold" onclick="openItemCodeSelectorModal('add_drawing', 'add_description_input')">
                                    <i class="bi bi-window-stack"></i> Pop-up
                                </button>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="drawing" id="add_drawing" class="form-control text-white fw-bold" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid #3b82f6 !important;" list="registeredItemCodesList" onchange="autoFillItemDescription(this, 'add_description_input'); document.getElementById('add_part_number_hidden').value = this.value;" oninput="autoFillItemDescription(this, 'add_description_input'); document.getElementById('add_part_number_hidden').value = this.value;" placeholder="Ketik Item Code Baru atau Cari...">
                                <button type="button" class="btn btn-outline-info" onclick="openItemCodeSelectorModal('add_drawing', 'add_description_input')" title="Pilih Item Code dari Pop-up">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-warning small fw-bold mb-1">
                                <i class="bi bi-building-gear me-1"></i> Kode Pabrik (Factory Code)
                            </label>
                            <select name="factory_code" class="form-select border-warning text-warning fw-bold" style="background-color: #1a2234 !important; border: 1px solid #f59e0b !important;">
                                <option value="KIP 1" selected>KIP 1</option>
                                <option value="KIP 2">KIP 2</option>
                                <option value="KIP 3">KIP 3</option>
                                <option value="KIP 4">KIP 4</option>
                            </select>
                        </div>
                        <div class="col-md-3" style="display:none;">
                            <input type="hidden" name="part_number" id="add_part_number_hidden" value="-">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" id="add_description_input" class="form-control text-white" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;" required placeholder="Nama part / material">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold">Supplier Name (Vendor)</label>
                            <input type="text" name="supplier_name" class="form-control text-white" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;" placeholder="Nama Supplier / Vendor">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold">User (PIC Buyer)</label>
                            <select name="user_id" class="form-select text-white" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;" onchange="if(this.value) { document.getElementById('add_pic_buyer_hidden').value = this.options[this.selectedIndex].text; }">
                                <option value="">Pilih User / PIC</option>
                                @foreach($buyers as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="pic_buyer" id="add_pic_buyer_hidden" value="">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold">Kategori Item</label>
                            <select name="category_id" class="form-select text-white" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;">
                                <option value="">Pilih kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_code }} - {{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-danger small fw-bold" id="add_plan_outstand_label">Outstand Mulai ({{ $months[0] ?? 'DEC' }})</label>
                            <input type="number" name="plan_outstand" id="add_plan_outstand" class="form-control text-danger fw-bold" style="background-color: #1a2234 !important; color: #f87171 !important; border: 1px solid #f87171 !important;" value="0" oninput="calculateLivePlant3Ratios('add')">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-info small fw-bold" id="add_plan_stock_label">Plan Stock Mulai ({{ $months[0] ?? 'DEC' }})</label>
                            <input type="number" name="plan_stock" id="add_plan_stock" class="form-control text-info fw-bold" style="background-color: #1a2234 !important; color: #38bdf8 !important; border: 1px solid #38bdf8 !important;" value="0" min="0" oninput="calculateLivePlant3Ratios('add')">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-warning small fw-bold">Price & Mata Uang</label>
                            <div class="input-group">
                                <select name="currency" id="add_plant3_currency" class="form-select bg-dark text-warning border-secondary fw-bold px-1" style="max-width: 85px;">
                                    <option value="USD" selected>USD ($)</option>
                                    <option value="IDR">IDR (Rp)</option>
                                </select>
                                <input type="text" inputmode="decimal" name="price" id="add_plant3_price" class="form-control text-warning fw-bold" style="background-color: #1a2234 !important; color: #fbbf24 !important; border: 1px solid #fbbf24 !important;" placeholder="0.00">
                            </div>
                        </div>
                        <input type="hidden" name="order_qty" value="0">
                    </div>

                    <!-- Multi-Month Input Grid -->
                    <h6 class="fw-bold text-warning mb-2"><i class="bi bi-table me-2"></i> Table Incoming PO, PROD & Live Ratio Per Bulan</h6>
                    <div class="table-responsive rounded-3 border border-secondary border-opacity-25 mb-3">
                        <table class="table table-dark table-bordered align-middle mb-0 text-center" style="font-size: 0.82rem; background: transparent;">
                            <thead class="bg-dark text-warning fw-bold">
                                <tr>
                                    <th style="width: 120px;">Bulan Periode</th>
                                    <th style="width: 110px;">PO (Step 2)</th>
                                    <th style="width: 100px;">Forecast</th>
                                    <th style="width: 100px;">Incoming (Step 3)</th>
                                    <th style="width: 110px;">Outstanding</th>
                                    <th style="width: 110px;">Input PROD</th>
                                    <th style="width: 110px;">Kalkulasi STOCK</th>
                                    <th style="width: 100px;">Live RATIO (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Month 0 Display -->
                                <tr style="background: rgba(59, 130, 246, 0.12);">
                                    <td class="fw-bold text-info" id="add_m0_label">{{ $months[0] ?? 'DEC' }} (Mulai)</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td><span id="add_outstand_0" class="fw-bold text-danger fs-6">0</span></td>
                                    <td class="text-muted">-</td>
                                    <td><span id="add_stock_0" class="fw-bold text-info fs-6">0</span></td>
                                    <td><span id="add_ratio_0" class="badge bg-secondary bg-opacity-25 text-muted px-2 py-1">-</span></td>
                                </tr>
                                <!-- Months 1 to 36 -->
                                @for($i = 1; $i <= 36; $i++)
                                    <tr id="add_row_{{ $i }}">
                                        <td class="fw-semibold text-light" id="add_month_label_{{ $i }}">{{ $months[$i] ?? ('Bulan ' . $i) }}</td>
                                        <td class="align-middle">
                                            <span id="add_po_{{ $i }}" class="fw-bold text-info fs-6">0</span>
                                            <input type="hidden" name="m{{ $i }}_po" id="add_po_hidden_{{ $i }}" value="0">
                                        </td>
                                        <td><span id="add_forecast_{{ $i }}" class="fw-bold text-primary">0</span></td>
                                        <td><span id="add_delivery_{{ $i }}" class="fw-bold text-success">0</span></td>
                                        <td><span id="add_outstanding_{{ $i }}" class="fw-bold text-danger">0</span></td>
                                        <td>
                                            <input type="number" name="m{{ $i }}_prod" id="add_prod_{{ $i }}" class="form-control form-control-sm text-center text-warning fw-bold mx-auto" style="max-width: 100px; background-color: #0f172a !important; color: #fbbf24 !important; border: 1px solid #fbbf24 !important;" value="0" min="0" oninput="calculateLivePlant3Ratios('add')">
                                        </td>
                                        <td><span id="add_stock_{{ $i }}" class="fw-bold text-success fs-6">0</span></td>
                                        <td><span id="add_ratio_{{ $i }}" class="badge bg-secondary bg-opacity-25 text-muted px-2 py-1">-</span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25 px-4 py-3" style="background: #1e293b !important;">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formAddPlant3" class="btn btn-warning fw-bold text-dark rounded-pill px-5 shadow">
                    <i class="bi bi-save-fill me-1"></i> Simpan Data & Live Ratio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════ MODAL EDIT DATA & LIVE RATIO PT KAWAI ════════════ -->
<div class="modal fade" id="modalEditPlant3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border border-secondary border-opacity-25 shadow-lg" style="background: #0f172a !important; border-radius: 20px; color: #ffffff;">
            <div class="modal-header px-4 py-3 border-bottom border-secondary border-opacity-25" style="background: #1e293b !important;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-warning fs-4"></i>
                    <span>Edit Incoming & Kalkulasi Ratio Multi-Bulan (PT KAWAI)</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: #0b1120 !important;">
                <form action="" method="POST" id="formEditPlant3">
                    @csrf
                    @method('PUT')
                    <!-- Pilih Durasi & Bulan Mulai Monitoring -->
                    <div class="row g-3 mb-4 p-3 rounded-3 border border-secondary border-opacity-25 align-items-center shadow-sm" style="background: rgba(245, 158, 11, 0.08);">
                        <div class="col-md-4">
                            <label class="form-label text-warning small fw-bold mb-1"><i class="bi bi-calendar-check me-1"></i> Bulan Mulai (Start Month)</label>
                            <select name="start_month" id="edit_plant3_start_month" class="form-select border-warning text-warning fw-bold" onchange="updateModalMonthsDisplay('edit')" style="background-color: #1a2234 !important; color: #fbbf24 !important;">
                                @foreach(['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $m)
                                    <option value="{{ $m }}" {{ ($startMonth ?? 'JAN') == $m ? 'selected' : '' }} style="background-color: #1a2234; color: #fbbf24;">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-warning small fw-bold mb-1"><i class="bi bi-calendar3 me-1"></i> Tahun (Start Year)</label>
                            <select name="start_year" id="edit_plant3_start_year" class="form-select border-warning text-warning fw-bold" onchange="updateModalMonthsDisplay('edit')" style="background-color: #1a2234 !important; color: #fbbf24 !important;">
                                @for($y = date('Y') - 2; $y <= date('Y') + 4; $y++)
                                    <option value="{{ $y }}" {{ ($startYear ?? date('Y')) == $y ? 'selected' : '' }} style="background-color: #1a2234; color: #fbbf24;">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-warning small fw-bold mb-1"><i class="bi bi-clock-history me-1"></i> Pilih Jumlah Bulan Tampilan</label>
                            <select name="duration" id="edit_plant3_duration_months" class="form-select border-warning text-warning fw-bold" onchange="updateModalMonthsDisplay('edit')" style="background-color: #1a2234 !important; color: #fbbf24 !important;">
                                @for($d = 1; $d <= 36; $d++)
                                    <option value="{{ $d }}" {{ ($duration ?? 12) == $d ? 'selected' : '' }} style="background-color: #1a2234; color: #fbbf24;">
                                        {{ $d }} Bulan {{ $d == 12 ? '(1 Tahun)' : ($d == 18 ? '(1.5 Tahun)' : ($d == 24 ? '(2 Tahun)' : ($d == 30 ? '(2.5 Tahun)' : ($d == 36 ? '(3 Tahun)' : '')))) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="badge bg-warning text-dark px-3 py-2 fw-bold d-block mt-3 mt-md-0 shadow-sm" id="edit_plant3_duration_badge" style="font-size: 0.85rem;">
                                Tampil: {{ $duration ?? 4 }} Bulan
                            </span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4 p-3 rounded-3 border border-secondary border-opacity-25" style="background: rgba(30, 41, 59, 0.6);">
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold">Item Code (Drawing)</label>
                            <input type="text" name="drawing" id="edit_plant3_drawing" class="form-control text-white fw-bold" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid #3b82f6 !important;" list="registeredItemCodesList" onchange="autoFillItemDescription(this, 'edit_plant3_description'); document.getElementById('edit_plant3_part_number').value = this.value;" oninput="autoFillItemDescription(this, 'edit_plant3_description'); document.getElementById('edit_plant3_part_number').value = this.value;">
                        </div>
                        <div class="col-md-3" style="display:none;">
                            <input type="hidden" name="part_number" id="edit_plant3_part_number" value="-">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" id="edit_plant3_description" class="form-control text-white" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold">Supplier Name (Vendor)</label>
                            <input type="text" name="supplier_name" id="edit_plant3_supplier_name" class="form-control text-white" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold">User (PIC Buyer)</label>
                            <select name="user_id" id="edit_plant3_user_id" class="form-select text-white" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;" onchange="if(this.value) { document.getElementById('edit_pic_buyer_hidden').value = this.options[this.selectedIndex].text; }">
                                <option value="">Pilih User / PIC</option>
                                @foreach($buyers as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="pic_buyer" id="edit_pic_buyer_hidden" value="">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-bold">Kategori Item</label>
                            <select name="category_id" id="edit_plant3_category_id" class="form-select text-white" style="background-color: #1a2234 !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;">
                                <option value="">Pilih kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_code }} - {{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-danger small fw-bold" id="edit_plant3_plan_outstand_label">Outstand Mulai ({{ $months[0] ?? 'DEC' }})</label>
                            <input type="number" name="plan_outstand" id="edit_plant3_plan_outstand" class="form-control text-danger fw-bold" style="background-color: #1a2234 !important; color: #f87171 !important; border: 1px solid #f87171 !important;" value="0" oninput="calculateLivePlant3Ratios('edit')">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-info small fw-bold" id="edit_plant3_plan_stock_label">Plan Stock Mulai ({{ $months[0] ?? 'DEC' }})</label>
                            <input type="number" name="plan_stock" id="edit_plant3_plan_stock" class="form-control text-info fw-bold" style="background-color: #1a2234 !important; color: #38bdf8 !important; border: 1px solid #38bdf8 !important;" min="0" oninput="calculateLivePlant3Ratios('edit')">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-warning small fw-bold">Price & Mata Uang</label>
                            <div class="input-group">
                                <select name="currency" id="edit_plant3_currency" class="form-select bg-dark text-warning border-secondary fw-bold px-1" style="max-width: 85px;">
                                    <option value="USD">USD ($)</option>
                                    <option value="IDR">IDR (Rp)</option>
                                </select>
                                <input type="text" inputmode="decimal" name="price" id="edit_plant3_price" class="form-control text-warning fw-bold" style="background-color: #1a2234 !important; color: #fbbf24 !important; border: 1px solid #fbbf24 !important;" placeholder="0.00">
                            </div>
                        </div>
                        <input type="hidden" name="order_qty" id="edit_plant3_order_qty" value="0">
                    </div>

                    <h6 class="fw-bold text-warning mb-2"><i class="bi bi-table me-2"></i> Table Incoming PO, PROD & Live Ratio Per Bulan</h6>
                    <div class="table-responsive rounded-3 border border-secondary border-opacity-25 mb-3">
                        <table class="table table-dark table-bordered align-middle mb-0 text-center" style="font-size: 0.82rem; background: transparent;">
                            <thead class="bg-dark text-warning fw-bold">
                                <tr>
                                    <th style="width: 120px;">Bulan Periode</th>
                                    <th style="width: 110px;">PO (Step 2)</th>
                                    <th style="width: 100px;">Forecast</th>
                                    <th style="width: 100px;">Incoming (Step 3)</th>
                                    <th style="width: 110px;">Outstanding</th>
                                    <th style="width: 110px;">Input PROD</th>
                                    <th style="width: 110px;">Kalkulasi STOCK</th>
                                    <th style="width: 100px;">Live RATIO (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Month 0 Display -->
                                <tr style="background: rgba(59, 130, 246, 0.12);">
                                    <td class="fw-bold text-info" id="edit_plant3_m0_label">{{ $months[0] ?? 'DEC' }} (Mulai)</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td class="text-muted">-</td>
                                    <td><span id="edit_plant3_outstand_0" class="fw-bold text-danger fs-6">0</span></td>
                                    <td class="text-muted">-</td>
                                    <td><span id="edit_plant3_stock_0" class="fw-bold text-info fs-6">0</span></td>
                                    <td><span id="edit_plant3_ratio_0" class="badge bg-secondary bg-opacity-25 text-muted px-2 py-1">-</span></td>
                                </tr>
                                <!-- Months 1 to 36 -->
                                @for($i = 1; $i <= 36; $i++)
                                    <tr id="edit_plant3_row_{{ $i }}">
                                        <td class="fw-semibold text-light" id="edit_plant3_month_label_{{ $i }}">{{ $months[$i] ?? ('Bulan ' . $i) }}</td>
                                        <td class="align-middle">
                                            <span id="edit_plant3_po_{{ $i }}" class="fw-bold text-info fs-6">0</span>
                                            <input type="hidden" name="m{{ $i }}_po" id="edit_plant3_po_hidden_{{ $i }}" value="0">
                                        </td>
                                        <td><span id="edit_plant3_forecast_{{ $i }}" class="fw-bold text-primary">0</span></td>
                                        <td><span id="edit_plant3_delivery_{{ $i }}" class="fw-bold text-success">0</span></td>
                                        <td><span id="edit_plant3_outstanding_{{ $i }}" class="fw-bold text-danger">0</span></td>
                                        <td>
                                            <input type="number" name="m{{ $i }}_prod" id="edit_plant3_prod_{{ $i }}" class="form-control form-control-sm text-center text-warning fw-bold mx-auto" style="max-width: 100px; background-color: #0f172a !important; color: #fbbf24 !important; border: 1px solid #fbbf24 !important;" value="0" min="0" oninput="calculateLivePlant3Ratios('edit')">
                                        </td>
                                        <td><span id="edit_plant3_stock_{{ $i }}" class="fw-bold text-success fs-6">0</span></td>
                                        <td><span id="edit_plant3_ratio_{{ $i }}" class="badge bg-secondary bg-opacity-25 text-muted px-2 py-1">-</span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer px-4 py-3 border-top border-secondary border-opacity-25" style="background: #1e293b !important;">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formEditPlant3" class="btn btn-warning fw-bold text-dark rounded-pill px-5 shadow-sm">
                    <i class="bi bi-save-fill me-1"></i> Update Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════ MODAL SMART IMPORT EXCEL (ENHANCED v2) ════════════ -->
<div class="modal fade" id="modalImportPlant3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border border-secondary border-opacity-25 shadow-lg" style="background: #0f172a !important; border-radius: 20px; color: #ffffff;">
            <div class="modal-header px-4 py-3 border-bottom border-secondary border-opacity-25" style="background: #1e293b !important;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-4"></i>
                    <span>Smart Import Excel (Pemetaan Otomatis v2)</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchasing.outstanding.import') }}" method="POST" enctype="multipart/form-data" id="formImportExcel" onsubmit="showImportProgress()">
                @csrf
                <div class="modal-body p-4" style="background: #0b1120 !important;">
                    <!-- Template Format Reference -->
                    <div class="alert border border-info border-opacity-50 text-light p-3 mb-3 rounded-3 small" style="background: rgba(56, 189, 248, 0.08);">
                        <div class="d-flex align-items-center gap-2 mb-2 fw-bold text-info fs-6">
                            <i class="bi bi-table"></i> Format Template Excel (3-Baris Header):
                        </div>
                        <div class="table-responsive" style="max-height: 120px;">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 0.68rem; background: rgba(0,0,0,0.3);">
                                <tbody>
                                    <tr style="background: rgba(56, 189, 248, 0.15);">
                                        <td class="text-white fw-bold">ITEM CODE</td>
                                        <td class="text-white fw-bold">DESCRIPTION</td>
                                        <td class="text-white fw-bold">SUPPLIER</td>
                                        <td class="text-white fw-bold">PRICE</td>
                                        <td colspan="2" class="text-center text-primary fw-bold">Jun-26</td>
                                        <td colspan="2" class="text-center text-success fw-bold">Jul-26</td>
                                    </tr>
                                    <tr style="background: rgba(56, 189, 248, 0.08);">
                                        <td></td><td></td><td></td><td></td>
                                        <td class="text-danger text-center">OUTSTANDING</td>
                                        <td class="text-info text-center">STOCK</td>
                                        <td class="text-info text-center">PO</td>
                                        <td class="text-warning text-center">PROD</td>
                                    </tr>
                                    <tr style="background: rgba(56, 189, 248, 0.05);">
                                        <td></td><td></td><td></td><td></td>
                                        <td class="text-center text-muted">QTY | AMOUNT</td>
                                        <td class="text-center text-muted">QTY | AMOUNT</td>
                                        <td class="text-center text-muted">QTY | AMOUNT</td>
                                        <td class="text-center text-muted">QTY</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2 text-muted" style="font-size: 0.72rem;">
                            <i class="bi bi-info-circle me-1"></i>Header bisa 1-3 baris. Bulan, QTY, AMOUNT terdeteksi otomatis. Data dimulai setelah header terakhir.
                        </div>
                    </div>

                    <!-- Currency Selector -->
                    <div class="mb-3">
                        <label class="form-label text-warning small fw-bold"><i class="bi bi-currency-exchange me-1"></i>Mata Uang Default</label>
                        <select name="import_currency" class="form-select bg-dark text-warning border-secondary fw-bold" style="font-size: 0.85rem;">
                            <option value="ALL" selected>ALL / Otomatis (Multi-Currency Sesuai File Excel)</option>
                            <option value="USD">USD ($ - Dollar)</option>
                            <option value="IDR">IDR (Rp - Rupiah)</option>
                        </select>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Jika Excel tidak memiliki kolom Currency, sistem menggunakan pilihan ini.</div>
                    </div>

                    <!-- File Input -->
                    <div class="mb-3">
                        <label class="form-label text-light fw-bold"><i class="bi bi-file-earmark-arrow-up me-1"></i>Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" id="importExcelFileInput" class="form-control text-white" accept=".xlsx,.xls,.csv" style="background-color: #1a2234 !important; border: 1px solid #10b981 !important;" required>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Mendukung semua format template PT Kawai Indonesia. Max 5 MB.</div>
                    </div>

                    <!-- Preview Panel (populated by JavaScript) -->
                    <div id="excelPreviewPanel" class="d-none">
                        <div class="border border-success border-opacity-25 rounded-3 p-3 mb-2" style="background: rgba(16, 185, 129, 0.05);">
                            <div class="d-flex align-items-center gap-2 mb-2 fw-bold text-success small">
                                <i class="bi bi-eye-fill"></i> Preview Analisis File Excel:
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background: rgba(0,0,0,0.2);">
                                        <i class="bi bi-list-ol text-info"></i>
                                        <div>
                                            <div class="text-muted" style="font-size: 0.68rem;">Baris Data</div>
                                            <div class="fw-bold text-white" id="previewRowCount">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background: rgba(0,0,0,0.2);">
                                        <i class="bi bi-columns-gap text-warning"></i>
                                        <div>
                                            <div class="text-muted" style="font-size: 0.68rem;">Total Kolom</div>
                                            <div class="fw-bold text-white" id="previewColCount">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="text-muted small mb-1"><i class="bi bi-calendar3 me-1"></i>Bulan Terdeteksi:</div>
                                <div id="previewMonths" class="d-flex flex-wrap gap-1">-</div>
                            </div>
                            <div class="mt-2">
                                <div class="text-muted small mb-1"><i class="bi bi-columns me-1"></i>Kolom Terdeteksi:</div>
                                <div id="previewColumns" class="d-flex flex-wrap gap-1">-</div>
                            </div>
                            <div id="previewWarnings" class="mt-2 d-none">
                                <div class="alert alert-warning border-warning border-opacity-50 py-2 px-3 mb-0 small" style="background: rgba(245, 158, 11, 0.1);">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <span id="previewWarningText"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div id="importProgressBar" class="d-none mt-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                            <span class="text-success small fw-bold">Memproses file Excel...</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 100%"></div>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.7rem;">Memproses data...</div>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 border-top border-secondary border-opacity-25" style="background: #1e293b !important;">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitImport" class="btn btn-success fw-bold rounded-pill px-5 shadow">
                        <i class="bi bi-upload me-1"></i> Upload &amp; Petakan Otomatis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════ MODAL POPUP DUPLICATE ITEM CODE NOTIFICATION ════════════ -->
<!-- ════════════ MODAL POPUP DUPLICATE ITEM CODE NOTIFICATION ════════════ -->
@php
    $dupWarnings = session('import_duplicates_found') ?? $importDuplicatesFound ?? null;
@endphp
@if(!empty($dupWarnings) && is_array($dupWarnings))
<div class="modal fade show" id="modalDuplicatesNotification" tabindex="-1" aria-labelledby="modalDuplicatesNotificationLabel" aria-hidden="true" style="display: block; background: rgba(0,0,0,0.85); z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border border-warning border-opacity-75 shadow-lg" style="background: #0f172a !important; border-radius: 20px; color: #ffffff;">
            <div class="modal-header px-4 py-3 border-bottom border-warning border-opacity-25" style="background: rgba(245, 158, 11, 0.15) !important;">
                <h5 class="modal-title fw-bold text-warning d-flex align-items-center gap-2" id="modalDuplicatesNotificationLabel">
                    <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                    <span>Peringatan: Item Code Duplikat Terdeteksi</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeDuplicateModal()"></button>
            </div>
            <div class="modal-body p-4" style="background: #0b1120 !important;">
                <div class="alert alert-warning border border-secondary border-opacity-25 text-light p-3 mb-3 rounded-3 small" style="background: rgba(245, 158, 11, 0.1);">
                    <div class="d-flex align-items-center gap-2 mb-2 fw-bold text-warning fs-6">
                        <i class="bi bi-info-circle-fill me-1"></i> Informasi Pengunggahan File Excel:
                    </div>
                    <div>
                        Sistem mendeteksi ada <strong>{{ count($dupWarnings) }} Item Code</strong> yang di-input <strong>lebih dari 1 kali</strong> di dalam file Excel yang baru Anda unggah.
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.76rem;">
                        <i class="bi bi-check2-circle text-success me-1"></i> <strong>Semua baris di Excel tetap berhasil di-import (100% tersimpan).</strong> Gunakan rincian baris di bawah ini untuk mengecek dan mengoreksi file Excel Anda jika ada kesalahan input.
                    </div>
                </div>

                <div class="table-responsive rounded-3 border border-secondary border-opacity-25" style="max-height: 320px;">
                    <table class="table table-dark table-hover table-bordered mb-0 align-middle small" style="font-size: 0.8rem; background: rgba(0,0,0,0.3);">
                        <thead style="background: #1e293b;" class="text-warning fw-bold sticky-top">
                            <tr>
                                <th style="width: 40px;" class="text-center">No</th>
                                <th>Item Code / Part Number</th>
                                <th class="text-center" style="width: 110px;">Frekuensi</th>
                                <th>Posisi Baris di Excel</th>
                                <th>Deskripsi Barang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dupWarnings as $idx => $dup)
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                <td class="fw-bold text-warning">
                                    <code class="px-2 py-1 bg-dark text-warning border border-warning border-opacity-25 rounded">{{ $dup['code'] }}</code>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-2 py-1">
                                        <i class="bi bi-layers-fill me-1"></i>{{ $dup['count'] }}x muncul
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($dup['rows'] as $rNum)
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-secondary border-opacity-25 px-2 py-1">Baris {{ $rNum }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-light">{{ implode(', ', $dup['descriptions']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer px-4 py-3 border-top border-secondary border-opacity-25 d-flex justify-content-between" style="background: #1e293b !important;">
                
                <button type="button" class="btn btn-warning fw-bold rounded-pill px-5 shadow" onclick="closeDuplicateModal()">
                    <i class="bi bi-check-circle-fill me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function closeDuplicateModal() {
    const el = document.getElementById('modalDuplicatesNotification');
    if (el) el.remove();
}
</script>
@endif

<!-- SheetJS for client-side Excel preview -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('importExcelFileInput');
    if (!fileInput) return;
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) {
            document.getElementById('excelPreviewPanel').classList.add('d-none');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(evt) {
            try {
                const data = new Uint8Array(evt.target.result);
                const wb = XLSX.read(data, { type: 'array', cellDates: true, dateNF: 'MMM-YY' });
                const ws = wb.Sheets[wb.SheetNames[0]];
                const rows = XLSX.utils.sheet_to_json(ws, { header: 'A', raw: true, defval: '' });
                
                if (!rows || rows.length === 0) {
                    showPreviewWarning('File Excel kosong atau tidak terbaca.');
                    return;
                }
                
                // Detect header row with scoring
                let headerRowIdx = -1;
                let maxHScore = 0;
                const headerKeywords = [
                    'ITEM CODE', 'ITEM_CODE', 'ITEM', 'PART NUMBER', 'PART_NUMBER', 'PART NO', 'PN', 
                    'DRAWING', 'NO. BARANG', 'ITEM CODE (PK)', 'MATERIAL CODE', 'MATERIAL_CODE', 'MATERIAL',
                    'KODE BARANG', 'KODE MATERIAL', 'KODE ITEM', 'KODE PART', 'KOMPONEN', 'SKU', 'CODE'
                ];
                for (let i = 0; i < Math.min(rows.length, 30); i++) {
                    const rowVals = Object.values(rows[i]).map(v => String(v ?? '').toUpperCase().trim());
                    let score = 0;
                    rowVals.forEach(v => {
                        if (!v) return;
                        if (headerKeywords.some(kw => v === kw || v.startsWith(kw) || v.includes(kw))) score += 3;
                        if (v.includes('SUPPLIER') || v.includes('VENDOR') || v.includes('DESCRIPTION') || v.includes('PRICE') || v.includes('PO') || v.includes('STOCK')) score += 1;
                    });
                    if (score > maxHScore) {
                        maxHScore = score;
                        headerRowIdx = i;
                    }
                }
                if (headerRowIdx === -1) headerRowIdx = 0;
                
                // Detect months
                const monthPattern = /(JAN|FEB|MAR|APR|MAY|MEI|JUN|JUL|AUG|AGS|SEP|OCT|OKT|NOV|DEC|DES)[\s\-]?(\d{2,4})/i;
                const months = new Set();
                const detectedCols = new Set();
                
                // Scan header rows (headerRowIdx ± 2)
                for (let i = Math.max(0, headerRowIdx - 2); i < Math.min(rows.length, headerRowIdx + 5); i++) {
                    Object.entries(rows[i]).forEach(([col, val]) => {
                        const str = String(val ?? '').trim();
                        const mMatch = str.match(monthPattern);
                        if (mMatch) {
                            let mShort = mMatch[1].toUpperCase();
                            if (mShort === 'MEI') mShort = 'MAY';
                            if (mShort === 'AGS') mShort = 'AUG';
                            if (mShort === 'DES') mShort = 'DEC';
                            if (mShort === 'OKT') mShort = 'OCT';
                            const yr = mMatch[2].length === 2 ? '20' + mMatch[2] : mMatch[2];
                            months.add(mShort + '-' + yr.slice(-2));
                        }
                        // Also detect serial dates
                        if (typeof val === 'number' && val >= 40000 && val <= 50000) {
                            const d = new Date((val - 25569) * 86400000);
                            const mNames = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
                            months.add(mNames[d.getMonth()] + '-' + String(d.getFullYear()).slice(-2));
                        }
                    });
                }
                
                // Detect columns
                const colKeywords = {
                    'ITEM CODE': 'Item Code', 'PART NUMBER': 'Part Number',
                    'DESCRIPTION': 'Description', 'DECRIPTION': 'Description',
                    'SUPPLIER': 'Supplier', 'PRICE': 'Price',
                    'CURRENCY': 'Currency', 'OUTSTANDING': 'Outstanding',
                    'STOCK': 'Stock', 'PO': 'PO', 'PROD': 'Produksi',
                    'FORECAST': 'Forecast', 'INCOMING': 'Incoming', 'DELIVERY': 'Incoming',
                    'QTY': 'QTY', 'AMOUNT': 'Amount'
                };
                
                for (let i = Math.max(0, headerRowIdx); i < Math.min(rows.length, headerRowIdx + 5); i++) {
                    Object.values(rows[i]).forEach(val => {
                        const str = String(val ?? '').toUpperCase().trim();
                        Object.entries(colKeywords).forEach(([key, label]) => {
                            if (str.includes(key)) detectedCols.add(label);
                        });
                    });
                }
                
                // Count data rows (skip header + sub-headers)
                let dataStart = headerRowIdx + 1;
                const subKw = ['PO', 'PROD', 'STOCK', 'OUTSTANDING', 'QTY', 'AMOUNT', 'FORECAST', 'INCOMING', 'DELIVERY', '%', 'BQTY'];
                for (let i = headerRowIdx + 1; i < Math.min(rows.length, headerRowIdx + 6); i++) {
                    const vals = Object.values(rows[i]).map(v => String(v ?? '').toUpperCase().trim()).filter(v => v);
                    const textVals = vals.filter(v => isNaN(v));
                    const kwCount = textVals.filter(v => subKw.includes(v)).length;
                    if (kwCount >= 2 || vals.some(v => v.match(monthPattern))) {
                        dataStart = i + 1;
                    } else if (textVals.length > 0 && kwCount === 0) {
                        break;
                    }
                }
                
                const dataRowCount = Math.max(0, rows.length - dataStart);
                
                // Scan for client-side duplicate Item Codes + Factory Code + Supplier
                let factoryColKey = null;
                let suppColKey = null;
                const headerRow = rows[headerRowIdx] || {};
                for (let colKey in headerRow) {
                    const val = String(headerRow[colKey] || '').toUpperCase().trim();
                    if (!factoryColKey && (val.includes('FACTORY') || val.includes('PABRIK') || val.includes('KIP') || val.includes('PLANT'))) {
                        factoryColKey = colKey;
                    }
                    if (!suppColKey && (val.includes('SUPPLIER') || val.includes('VENDOR'))) {
                        suppColKey = colKey;
                    }
                }

                const codeMap = {};
                const skipList = ['ITEM CODE','ITEM_CODE','PART NUMBER','PART_NUMBER','TOTAL','GRAND TOTAL','NO'];
                for (let i = dataStart; i < rows.length; i++) {
                    const row = rows[i];
                    const code = String(row['B'] || row['C'] || '').trim().toUpperCase();
                    const factory = factoryColKey ? String(row[factoryColKey] || 'KIP 1').trim().toUpperCase() : 'KIP 1';
                    const supp = suppColKey ? String(row[suppColKey] || '').trim().toUpperCase() : '';
                    const compKey = code + ' [' + factory + (supp ? ' - ' + supp : '') + ']';
                    if (code && !skipList.includes(code) && !code.match(/^(PO|PROD|STOCK|QTY|AMOUNT|FORECAST)$/i)) {
                        if (!codeMap[compKey]) codeMap[compKey] = [];
                        codeMap[compKey].push(i + 1); // 1-based row number
                    }
                }
                const dupEntries = Object.entries(codeMap).filter(([k, v]) => v.length > 1);

                // Update UI
                document.getElementById('previewRowCount').textContent = dataRowCount.toLocaleString() + ' baris';
                document.getElementById('previewColCount').textContent = Object.keys(rows[0] || {}).length + ' kolom';
                
                const monthsEl = document.getElementById('previewMonths');
                if (months.size > 0) {
                    monthsEl.innerHTML = [...months].map(m => 
                        `<span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2 py-1" style="font-size: 0.72rem;"><i class="bi bi-calendar3 me-1"></i>${m}</span>`
                    ).join('');
                } else {
                    monthsEl.innerHTML = '<span class="text-warning small"><i class="bi bi-exclamation-triangle me-1"></i>Tidak ada bulan terdeteksi</span>';
                }
                
                const colsEl = document.getElementById('previewColumns');
                if (detectedCols.size > 0) {
                    colsEl.innerHTML = [...detectedCols].map(c => 
                        `<span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 0.7rem;">${c}</span>`
                    ).join('');
                } else {
                    colsEl.innerHTML = '<span class="text-warning small">Kolom belum terdeteksi</span>';
                }
                
                // Warnings
                const warnings = [];
                if (months.size === 0) warnings.push('Tidak ada bulan terdeteksi di header. Pastikan format bulan seperti JUN-26 atau serial date ada di baris header.');
                if (!detectedCols.has('Item Code') && !detectedCols.has('Part Number')) warnings.push('Kolom ITEM CODE / PART NUMBER tidak ditemukan.');
                if (dataRowCount === 0) warnings.push('Tidak ada baris data terdeteksi setelah header.');
                if (dupEntries.length > 0) {
                    const dupDetail = dupEntries.slice(0, 4).map(([k, v]) => `<code>${k}</code> (${v.length}x: Baris ${v.join(', ')})`).join(', ');
                    warnings.push('⚠️ <strong>Item Code Duplikat:</strong> Terdeteksi <strong>' + dupEntries.length + ' Item Code duplikat</strong> di Excel: ' + dupDetail + (dupEntries.length > 4 ? '...' : ''));
                }
                if (dataRowCount > 2000) warnings.push('File sangat besar (' + dataRowCount + ' baris). Proses mungkin memakan waktu lama.');
                
                if (warnings.length > 0) {
                    document.getElementById('previewWarnings').classList.remove('d-none');
                    document.getElementById('previewWarningText').innerHTML = warnings.join('<br>');
                } else {
                    document.getElementById('previewWarnings').classList.add('d-none');
                }
                
                document.getElementById('excelPreviewPanel').classList.remove('d-none');
                
            } catch (err) {
                console.error('Preview error:', err);
                showPreviewWarning('Gagal membaca file: ' + err.message);
            }
        };
        reader.readAsArrayBuffer(file);
    });
    
    function showPreviewWarning(msg) {
        document.getElementById('excelPreviewPanel').classList.remove('d-none');
        document.getElementById('previewWarnings').classList.remove('d-none');
        document.getElementById('previewWarningText').textContent = msg;
        document.getElementById('previewMonths').innerHTML = '-';
        document.getElementById('previewColumns').innerHTML = '-';
        document.getElementById('previewRowCount').textContent = '-';
        document.getElementById('previewColCount').textContent = '-';
    }
});

function showImportProgress() {
    document.getElementById('importProgressBar').classList.remove('d-none');
    document.getElementById('btnSubmitImport').disabled = true;
    document.getElementById('btnSubmitImport').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
}
</script>


@push('scripts')
<script>
    function calculateLivePlant3Ratios(mode) {
        let prefix = mode === 'add' ? 'add_' : 'edit_plant3_';
        let planStockInput = document.getElementById(prefix + 'plan_stock');
        let planOutstandInput = document.getElementById(prefix + 'plan_outstand');

        let currentStock = parseInt(planStockInput ? planStockInput.value : 0) || 0;
        let prevOutstand = parseInt(planOutstandInput ? planOutstandInput.value : 0) || 0;

        let stocks = [];
        stocks[0] = currentStock;

        let stockSpan0 = document.getElementById(prefix + 'stock_0');
        if (stockSpan0) stockSpan0.innerText = currentStock.toLocaleString();
        let outSpan0 = document.getElementById(prefix + 'outstand_0');
        if (outSpan0) outSpan0.innerText = prevOutstand.toLocaleString();

        for (let i = 1; i <= 36; i++) {
            let poSpan = document.getElementById(prefix + 'po_' + i);
            let poInput = document.getElementById(prefix + 'po_hidden_' + i);
            let prodInput = document.getElementById(prefix + 'prod_' + i);

            let po = poInput ? (parseInt(poInput.value) || 0) : (poSpan ? (parseInt(poSpan.innerText.replace(/,/g, '')) || 0) : 0);
            let prod = prodInput ? (parseInt(prodInput.value) || 0) : 0;
            
            let delSpan = document.getElementById(prefix + 'delivery_' + i);
            let rawDel = delSpan ? (parseInt(delSpan.innerText.replace(/,/g, '')) || 0) : 0;
            let del = rawDel > 0 ? rawDel : po; // Delivery mengikuti Master PO (PO) jika belum ada realisasi aktual

            let forecast = po - prevOutstand;
            let outstanding = prevOutstand + po - del;
            currentStock = currentStock + del - prod;

            stocks[i] = currentStock;
            prevOutstand = outstanding;

            let fcSpan = document.getElementById(prefix + 'forecast_' + i);
            if (fcSpan) fcSpan.innerText = forecast.toLocaleString();
            if (delSpan) delSpan.innerText = del.toLocaleString();
            let outSpan = document.getElementById(prefix + 'outstanding_' + i);
            if (outSpan) outSpan.innerText = outstanding.toLocaleString();
            let stockSpan = document.getElementById(prefix + 'stock_' + i);
            if (stockSpan) stockSpan.innerText = currentStock.toLocaleString();
        }

        // Compute ratio: Ratio[i] = Stock[i] / Prod[i+1]
        for (let i = 0; i <= 35; i++) {
            let nextProdInput = document.getElementById(prefix + 'prod_' + (i + 1));
            let nextProd = nextProdInput ? (parseInt(nextProdInput.value) || 0) : 0;

            if (nextProd <= 0) {
                let nextPoSpan = document.getElementById(prefix + 'po_' + (i + 1));
                nextProd = nextPoSpan ? (parseInt(nextPoSpan.innerText.replace(/,/g, '')) || 0) : 0;
            }

            let ratioSpan = document.getElementById(prefix + 'ratio_' + i);
            if (ratioSpan) {
                if (nextProd > 0) {
                    let r = Math.round((stocks[i] / nextProd) * 100);
                    ratioSpan.innerText = r + '%';
                    ratioSpan.className = 'badge ' + (r < 100 ? 'bg-danger text-white' : (r > 200 ? 'bg-success text-white' : 'bg-warning text-dark')) + ' px-2 py-1';
                } else {
                    ratioSpan.innerText = '-';
                    ratioSpan.className = 'badge bg-secondary bg-opacity-25 text-muted px-2 py-1';
                }
            }
        }
    }

    function updateModalMonthsDisplay(mode) {
        let prefix = mode === 'add' ? 'add' : 'edit_plant3';
        let startMonthSelect = document.getElementById(prefix + '_start_month');
        let durationSelect = document.getElementById(prefix + '_duration_months');
        if (!startMonthSelect || !durationSelect) return;

        let startMonth = startMonthSelect.value;
        let duration = parseInt(durationSelect.value) || 4;

        const allMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JULY', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        let startIndex = allMonths.indexOf(startMonth);
        if (startIndex === -1) startIndex = 6; // default JULY

        let planOutstandLabel = document.getElementById(prefix + '_plan_outstand_label');
        if (planOutstandLabel) {
            planOutstandLabel.innerText = 'Outstand Mulai (' + startMonth + ')';
        }

        let planStockLabel = document.getElementById(prefix + '_plan_stock_label');
        if (planStockLabel) {
            planStockLabel.innerText = 'Plan Stock Mulai (' + startMonth + ')';
        }

        let m0Label = document.getElementById(prefix + '_m0_label');
        if (m0Label) {
            m0Label.innerText = startMonth + ' (Mulai)';
        }

        let durationBadge = document.getElementById(prefix + '_duration_badge');
        if (durationBadge) {
            durationBadge.innerText = 'Tampil: ' + duration + ' Bulan';
        }

        for (let i = 1; i <= 36; i++) {
            let monthIdx = (startIndex + i) % 12;
            let monthName = allMonths[monthIdx];
            
            let rowLabel = document.getElementById(prefix + '_month_label_' + i);
            if (rowLabel) {
                rowLabel.innerText = monthName;
            }

            let rowElem = document.getElementById(prefix + '_row_' + i);
            if (rowElem) {
                if (i <= duration) {
                    rowElem.style.display = '';
                } else {
                    rowElem.style.display = 'none';
                }
            }
        }

        calculateLivePlant3Ratios(mode);
    }

    function openAddPlant3Modal() {
        const form = document.getElementById('formAddPlant3');
        if (form) form.reset();
        const stock = document.getElementById('add_plan_stock');
        if (stock) stock.value = 0;
        const outstand = document.getElementById('add_plan_outstand');
        if (outstand) outstand.value = 0;
        for (let i = 1; i <= 36; i++) {
            let poSpan = document.getElementById('add_po_' + i);
            let poInput = document.getElementById('add_po_hidden_' + i);
            let prodInput = document.getElementById('add_prod_' + i);
            if (poSpan) poSpan.innerText = '0';
            if (poInput) poInput.value = 0;
            if (prodInput) prodInput.value = 0;
        }
        if (typeof updateModalMonthsDisplay === 'function') {
            updateModalMonthsDisplay('add');
        }
        const modalEl = document.getElementById('modalAddPlant3');
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function openEditPlant3Modal(id, part_number, description, supplier_name, drawing, plan_stock, plan_outstand, monthData, categoryId, userId, picBuyer, price) {
        document.getElementById('formEditPlant3').action = "/purchasing/outstanding/" + id;
        document.getElementById('edit_plant3_part_number').value = part_number;
        document.getElementById('edit_plant3_description').value = description;
        document.getElementById('edit_plant3_supplier_name').value = supplier_name || '';
        document.getElementById('edit_plant3_drawing').value = drawing || '';
        document.getElementById('edit_plant3_plan_stock').value = plan_stock || 0;
        if (document.getElementById('edit_plant3_plan_outstand')) {
            document.getElementById('edit_plant3_plan_outstand').value = plan_outstand || 0;
        }
        if (document.getElementById('edit_plant3_price')) {
            document.getElementById('edit_plant3_price').value = price || 0;
        }
        document.getElementById('edit_plant3_category_id').value = categoryId || '';
        if (document.getElementById('edit_plant3_user_id')) {
            document.getElementById('edit_plant3_user_id').value = userId || '';
        }
        if (document.getElementById('edit_pic_buyer_hidden')) {
            document.getElementById('edit_pic_buyer_hidden').value = picBuyer || '';
        }

        for (let i = 1; i <= 36; i++) {
            let poSpan = document.getElementById('edit_plant3_po_' + i);
            let poInput = document.getElementById('edit_plant3_po_hidden_' + i);
            let prodInput = document.getElementById('edit_plant3_prod_' + i);

            let poVal = (monthData && monthData[i]) ? (monthData[i].po || 0) : 0;
            if (poSpan) poSpan.innerText = poVal.toLocaleString();
            if (poInput) poInput.value = poVal;
            if (prodInput) prodInput.value = (monthData && monthData[i]) ? (monthData[i].prod || 0) : 0;
        }
        updateModalMonthsDisplay('edit');
        const modalEl = document.getElementById('modalEditPlant3');
        if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function filterPlant3Table() {
        let input = document.getElementById('searchPlant3').value.toLowerCase();
        let rows = document.querySelectorAll('#tbl-plant3 tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }
</script>
<script>
    function editOutstanding(id, drawing, part_number, description, periode, po, qty) {
        document.getElementById('formEditOutstanding').action = "/purchasing/master/outstanding/" + id;
        document.getElementById('edit_out_drawing').value = drawing || '';
        document.getElementById('edit_out_part_number').value = part_number;
        document.getElementById('edit_out_description').value = description;
        document.getElementById('edit_out_periode').value = periode;
        document.getElementById('edit_out_po').value = po;
        document.getElementById('edit_out_qty').value = qty;
        const modalEl = document.getElementById('modalEditOutstanding');
        if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function editActual(id, part_number, description, periode, qty) {
        document.getElementById('formEditActual').action = "/purchasing/master/actual/" + id;
        document.getElementById('edit_act_part_number').value = part_number;
        document.getElementById('edit_act_description').value = description;
        document.getElementById('edit_act_periode').value = periode;
        document.getElementById('edit_act_qty').value = qty;
        const modalEl = document.getElementById('modalEditActual');
        if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function editForecast(id, part_number, description, periode, qty, price) {
        document.getElementById('formEditForecast').action = "/purchasing/master/forecast/" + id;
        document.getElementById('edit_for_part_number').value = part_number;
        document.getElementById('edit_for_description').value = description;
        document.getElementById('edit_for_periode').value = periode;
        document.getElementById('edit_for_qty').value = qty;
        if (document.getElementById('edit_for_price')) {
            document.getElementById('edit_for_price').value = price || 0;
        }
        const modalEl = document.getElementById('modalEditForecast');
        if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
</script>

<!-- Modal Bulk Delete Confirmation Monitoring -->
<div class="modal fade" id="modalBulkDeletePlant3Confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-danger text-white" style="background: #111827;">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Hapus Terpilih Data Monitoring</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchasing.outstanding.destroy-bulk') }}" method="POST" id="formBulkDeletePlant3">
                @csrf
                <div class="modal-body">
                    <div id="bulkDeletePlant3IdsContainer"></div>
                    Apakah Anda yakin ingin menghapus <strong id="bulkDeletePlant3CountText" class="text-danger">0</strong> data monitoring terpilih?
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

<!-- Modal Reset / Hapus Semua Data Monitoring (Step 1) -->
<div class="modal fade" id="modalDeleteAllPlant3Confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-danger text-white" style="background: #111827;">
            <div class="modal-header border-danger border-opacity-50">
                <h5 class="modal-title text-danger fw-bold"><i class="bi bi-exclamation-octagon-fill me-2"></i> Hapus SEMUA Data Step 1 (Reset Total)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchasing.outstanding.destroy-all') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger bg-danger bg-opacity-20 border border-danger border-opacity-50 text-white small mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i>
                        <strong>PERINGATAN:</strong> Tindakan ini akan menghapus seluruh data secara permanen.
                    </div>
                    <p class="text-warning small mb-0">Apakah Anda yakin ingin melanjutkan?</p>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold"><i class="bi bi-trash3-fill me-1"></i> Ya, Kosongkan Semua Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bulk Delete Confirmation Forecast -->
<div class="modal fade" id="modalBulkDeleteForecastConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-danger text-white" style="background: #111827;">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Hapus Massal Master Forecast</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchasing.master.forecast.destroy-bulk') }}" method="POST" id="formBulkDeleteForecast">
                @csrf
                <div class="modal-body">
                    <div id="bulkDeleteForecastIdsContainer"></div>
                    Apakah Anda yakin ingin menghapus <strong id="bulkDeleteForecastCountText" class="text-danger">0</strong> data forecast terpilih?
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
    // Global Helper Functions for Step 1 Checkbox Interactions
    window.toggleSelectAllPlant3 = function(masterCb) {
        const isChecked = masterCb ? masterCb.checked : false;
        const checkboxes = document.querySelectorAll('.row-checkbox-plant3');
        checkboxes.forEach(cb => { cb.checked = isChecked; });
        window.updatePlant3BulkBtn();
    };

    window.updatePlant3BulkBtn = function() {
        const checked = document.querySelectorAll('.row-checkbox-plant3:checked');
        const allCheckboxes = document.querySelectorAll('.row-checkbox-plant3');
        const checkAllPlant3 = document.getElementById('checkAllPlant3');
        const btnBulkDeletePlant3 = document.getElementById('btnBulkDeletePlant3');
        const countSpanPlant3 = document.getElementById('bulkDeleteCountPlant3');

        if (checkAllPlant3 && allCheckboxes.length > 0) {
            checkAllPlant3.checked = (checked.length === allCheckboxes.length);
        }

        if (btnBulkDeletePlant3) {
            if (checked.length > 0) {
                btnBulkDeletePlant3.classList.remove('d-none');
                if (countSpanPlant3) countSpanPlant3.innerText = checked.length;
            } else {
                btnBulkDeletePlant3.classList.add('d-none');
            }
        }
    };

    window.confirmBulkDeletePlant3 = function() {
        const checked = document.querySelectorAll('.row-checkbox-plant3:checked');
        const total = document.querySelectorAll('.row-checkbox-plant3').length;
        if (checked.length === 0) return;
        
        const container = document.getElementById('bulkDeletePlant3IdsContainer');
        if (container) {
            container.innerHTML = '';
            const frag = document.createDocumentFragment();
            if (checked.length === total && total > 0) {
                const inputAll = document.createElement('input');
                inputAll.type = 'hidden';
                inputAll.name = 'delete_all';
                inputAll.value = '1';
                frag.appendChild(inputAll);
            } else {
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    frag.appendChild(input);
                });
            }
            container.appendChild(frag);
        }

        const countText = document.getElementById('bulkDeletePlant3CountText');
        if (countText) countText.innerText = checked.length;

        const modalEl = document.getElementById('modalBulkDeletePlant3Confirm');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            if (window.confirm(`Hapus ${checked.length} data terpilih?`)) {
                const form = document.getElementById('formBulkDeletePlant3');
                if (form) form.submit();
            }
        }
    };

    window.confirmDeleteAllPlant3 = function() {
        const modalEl = document.getElementById('modalDeleteAllPlant3Confirm');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            if (window.confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS SEMUA DATA Step 1?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('purchasing.outstanding.destroy-all') }}";
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        }
    };

    window.toggleSelectAllForecast = function(masterCb) {
        const isChecked = masterCb ? masterCb.checked : false;
        const checkboxes = document.querySelectorAll('.row-checkbox-forecast');
        checkboxes.forEach(cb => { cb.checked = isChecked; });
        window.updateForecastBulkBtn();
    };

    window.updateForecastBulkBtn = function() {
        const checked = document.querySelectorAll('.row-checkbox-forecast:checked');
        const allCheckboxes = document.querySelectorAll('.row-checkbox-forecast');
        const checkAllForecast = document.getElementById('checkAllForecast');
        const btnBulkDeleteForecast = document.getElementById('btnBulkDeleteForecast');
        const countSpanForecast = document.getElementById('bulkDeleteCountForecast');

        if (checkAllForecast && allCheckboxes.length > 0) {
            checkAllForecast.checked = (checked.length === allCheckboxes.length);
        }

        if (btnBulkDeleteForecast) {
            if (checked.length > 0) {
                btnBulkDeleteForecast.classList.remove('d-none');
                if (countSpanForecast) countSpanForecast.innerText = checked.length;
            } else {
                btnBulkDeleteForecast.classList.add('d-none');
            }
        }
    };

    window.confirmBulkDeleteForecast = function() {
        const checked = document.querySelectorAll('.row-checkbox-forecast:checked');
        const total = document.querySelectorAll('.row-checkbox-forecast').length;
        if (checked.length === 0) return;
        
        const container = document.getElementById('bulkDeleteForecastIdsContainer');
        if (container) {
            container.innerHTML = '';
            const frag = document.createDocumentFragment();
            if (checked.length === total && total > 0) {
                const inputAll = document.createElement('input');
                inputAll.type = 'hidden';
                inputAll.name = 'delete_all';
                inputAll.value = '1';
                frag.appendChild(inputAll);
            } else {
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = cb.value;
                    frag.appendChild(input);
                });
            }
            container.appendChild(frag);
        }

        const countText = document.getElementById('bulkDeleteForecastCountText');
        if (countText) countText.innerText = checked.length;

        const modalEl = document.getElementById('modalBulkDeleteForecastConfirm');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            if (window.confirm(`Hapus ${checked.length} data forecast terpilih?`)) {
                const form = document.getElementById('formBulkDeleteForecast');
                if (form) form.submit();
            }
        }
    };
</script>
@endpush
@include('partials.registered-item-codes-datalist')
@include('partials.modal-select-item-code')
@include('partials.confirm-modal')
@include('partials.import-preview-modal')
<script src="{{ asset('js/kawai-notify.js') }}"></script>
<script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
