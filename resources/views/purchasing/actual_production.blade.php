<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Step 5: Aktual Produksi | Purchasing PT Kawai Indonesia</title>
    <meta name="description" content="Pencatatan realisasi produksi actual harian berdasarkan item code dan tanggal produksi.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        :root {
            --bg-primary: #0a0e17;
            --card-bg: rgba(23,31,48,0.85);
            --card-border: rgba(255,255,255,0.08);
            --accent-gold: #F59E0B;
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --accent-purple: #8b5cf6;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
        }
        body {
            background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }
        h1,h2,h3,h4,h5,.brand-font { font-family: 'Outfit', sans-serif; }
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
        .nav-link-pill { color: var(--text-muted); font-size: 0.82rem; font-weight: 500; padding: 0.4rem 0.9rem; border-radius: 20px; transition: all 0.2s; text-decoration: none; }
        .nav-link-pill:hover { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            backdrop-filter: blur(12px);
        }
        .page-header-title {
            font-size: 1.7rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, var(--accent-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-add {
            background: linear-gradient(135deg, var(--accent-gold), #d97706);
            border: none;
            color: #0A0E1A;
            font-weight: 700;
            padding: 0.55rem 1.25rem;
            border-radius: 10px;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(245,158,11,0.4);
            color:#0A0E1A;
        }
        .btn-import {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 0.55rem 1.25rem;
            border-radius: 10px;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        .btn-import:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(16,185,129,0.4);
            color: #fff;
        }
        .btn-danger-custom {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 0.55rem 1.1rem;
            border-radius: 10px;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
        }
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(239,68,68,0.4);
            color: #fff;
        }
        .filter-select {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            color: #fff;
            border-radius: 10px;
            padding: 0.45rem 1rem;
            font-size: 0.88rem;
        }
        .filter-select option { background: #1e2a3a; color: #fff; }
        
        /* ═══ CANONICAL KPI CARDS ═══ */
        .kpi-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.35);
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
        }
        .kpi-card-gold::before    { background: linear-gradient(90deg, #f59e0b, #d97706); }
        .kpi-card-blue::before    { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
        .kpi-card-emerald::before { background: linear-gradient(90deg, #10b981, #047857); }
        .kpi-card-purple::before  { background: linear-gradient(90deg, #8b5cf6, #6d28d9); }

        .kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .kpi-title {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
        }
        .kpi-icon-box {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }
        .icon-gold    { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .icon-blue    { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .icon-emerald { background: rgba(16,185,129,0.15); color: #34d399; }
        .icon-purple  { background: rgba(139,92,246,0.15); color: #a78bfa; }

        .kpi-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 0.4rem;
        }
        .kpi-unit { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); }
        .kpi-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.76rem;
            color: var(--text-muted);
            border-top: 1px solid rgba(255,255,255,0.06);
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }

        .table-custom { color: var(--text-main); font-size: 0.88rem; }
        .table-custom thead th {
            background: rgba(245,158,11,0.13);
            color: #fbbf24;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 0.85rem 1rem;
            white-space: nowrap;
        }
        .table-custom tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.15s; }
        .table-custom tbody tr:hover { background: rgba(255,255,255,0.04); }
        .table-custom tbody td { padding: 0.8rem 1rem; vertical-align: middle; }
        
        .badge-plant {
            background: rgba(59,130,246,0.15);
            color: #93c5fd;
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 6px;
            font-size: 0.75rem;
            padding: 0.2rem 0.55rem;
            font-family: monospace;
            font-weight: 700;
        }
        .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.78rem; transition: all 0.2s; cursor: pointer; }
        .btn-icon-edit { background: rgba(59,130,246,0.2); color: #93c5fd; }
        .btn-icon-edit:hover { background: rgba(59,130,246,0.4); }
        .btn-icon-del { background: rgba(239,68,68,0.2); color: #f87171; }
        .btn-icon-del:hover { background: rgba(239,68,68,0.4); }
        .empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }
        
        .modal-content { background: #1a2236; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; color: var(--text-main); }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,0.08); }
        .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); }
        .form-control, .form-select { background: #1e293b !important; border: 1px solid rgba(255,255,255,0.12); color: #fff !important; border-radius: 10px; }
        .form-control:focus, .form-select:focus { background: #1e293b !important; border-color: var(--accent-gold); box-shadow: 0 0 0 3px rgba(245,158,11,0.2); color: #fff !important; }
        .form-select option, select option, option { background-color: #1e293b !important; color: #ffffff !important; font-weight: 500; }
        .form-control::placeholder { color: rgba(255,255,255,0.35); }
        .form-label { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .style-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .style-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); }
        .style-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
    </style>
</head>
<body>
<nav class="top-navbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard.overview') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-music-note-beamed text-warning fs-4"></i>
            <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
        </a>
    </div>
    <div>
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.actual-production', 'hasFaqModal' => true])
    </div>
</nav>

@include('partials.faq-modal')

<div class="container-dashboard py-4">

    <!-- 7-STEP UNIFIED WORKFLOW STEPPER -->
    @include('partials.workflow-stepper', ['currentStep' => 5])

    <!-- STANDARDIZED PAGE HEADER & ACTION HIERARCHY -->
    <div class="kawai-page-header">
        <div class="kawai-page-header-left">
            <div class="page-icon-box" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35);">
                <i class="bi bi-gear-wide-connected text-warning"></i>
            </div>
            <div>
                <h1 class="page-title-text">Aktual Produksi</h1>
                <p class="page-subtitle-text">Pencatatan realisasi produksi aktual harian &amp; impor data shopfloor.</p>
            </div>
        </div>
        <div class="kawai-page-actions">
            <button type="button" class="btn-kawai-secondary" data-bs-toggle="modal" data-bs-target="#modalImportActual">
                <i class="bi bi-file-earmark-excel-fill text-success"></i> Import Excel
            </button>
            <button type="button" class="btn-kawai-primary" data-bs-toggle="modal" data-bs-target="#modalAddActual">
                <i class="bi bi-plus-circle-fill"></i> + Tambah Log
            </button>
            <div class="dropdown">
                <button class="btn-kawai-more dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Opsi Tambahan">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark-custom dropdown-menu-end">
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.actual-production.template') }}">
                            <i class="bi bi-download text-info"></i> Unduh Template Excel
                        </a>
                    </li>
                    @if($totalLogsCount > 0)
                        <li><hr class="dropdown-divider border-secondary border-opacity-25 my-1"></li>
                        <li>
                            <a class="dropdown-item-custom text-danger" href="javascript:void(0)" onclick="triggerDeleteAllModal()">
                                <i class="bi bi-trash3-fill"></i> Hapus Semua Data
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
            @include('partials.kurs-kpi-banner')
        </div>
    </div>

    <!-- ═══ 4 CANONICAL KPI CARDS (TOTAL LOGS, MATERIAL UNIK, TOTAL QTY, ZERO PRODUCTION) ═══ -->
    <div class="row g-3 g-xl-4 mb-4">
        {{-- Card 1: Total Baris Log --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card kpi-card-gold">
                <div class="kpi-header">
                    <span class="kpi-title">TOTAL BARIS LOG</span>
                    <div class="kpi-icon-box icon-gold">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
                <div class="kpi-value text-warning">
                    {{ number_format($totalLogsCount) }} <span class="kpi-unit">Baris</span>
                </div>
                <div class="kpi-footer">
                    <span class="text-muted small">Total Transaksi Produksi</span>
                    <span class="badge bg-warning bg-opacity-25 text-warning font-monospace">1 Row = 1 Log</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Material Unik --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card kpi-card-blue">
                <div class="kpi-header">
                    <span class="kpi-title">TOTAL MATERIAL UNIK</span>
                    <div class="kpi-icon-box icon-blue">
                        <i class="bi bi-boxes"></i>
                    </div>
                </div>
                <div class="kpi-value text-info">
                    {{ number_format($totalUniqueItemsCount) }} <span class="kpi-unit">SKU</span>
                </div>
                <div class="kpi-footer">
                    <span class="text-muted small">Material Code Terdaftar</span>
                    <span class="badge bg-primary bg-opacity-25 text-info">Distinct Materials</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Realisasi Produksi --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card kpi-card-emerald">
                <div class="kpi-header">
                    <span class="kpi-title">TOTAL REALISASI PRODUKSI</span>
                    <div class="kpi-icon-box icon-emerald">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                </div>
                <div class="kpi-value text-success">
                    {{ number_format($totalProductionQty) }} <span class="kpi-unit">PCS</span>
                </div>
                <div class="kpi-footer">
                    <span class="text-muted small">Akumulasi Qty Aktual</span>
                    <span class="badge bg-success bg-opacity-25 text-success">Output Fisik</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Zero Production Logs --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card kpi-card-purple">
                <div class="kpi-header">
                    <span class="kpi-title">ZERO PRODUCTION (QTY = 0)</span>
                    <div class="kpi-icon-box icon-purple">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                </div>
                <div class="kpi-value" style="color: #a78bfa;">
                    {{ number_format($totalZeroProductionCount) }} <span class="kpi-unit">Baris</span>
                </div>
                <div class="kpi-footer">
                    <span class="text-muted small">Tetap Tercatat Valid</span>
                    <span class="badge bg-opacity-25" style="background: rgba(139,92,246,0.2); color: #a78bfa;">0 Qty Included</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ DATA QUALITY & IMPORT AUDIT SUMMARY WIDGET ═══ -->
    <div class="glass-card mb-4 p-3" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3);">
                    <i class="bi bi-file-earmark-check text-warning fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold text-white mb-0">Status Integritas &amp; Batch Import Actual Production</h6>
                        <span class="badge-health-success px-2 py-0.5" style="font-size: 0.72rem; border-radius: 999px;">
                            <i class="bi bi-shield-fill-check me-1"></i>1 Baris Excel = 1 Log Terarsip
                        </span>
                    </div>
                    <small class="text-muted">
                        Batch ID Terakhir: <strong class="text-warning font-monospace">{{ $latestBatchId ?: 'Manual Input / Belum Ada Batch' }}</strong>
                        @if($latestImportDate)
                            &bull; Diperbarui pada: <span class="text-light">{{ $latestImportDate }}</span>
                        @endif
                        &bull; Total Plant: <span class="text-info">{{ $totalPlantsCount }} Plant Terdaftar</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ FILTER & ACTION TOOLBAR ═══ -->
    <div class="glass-card mb-4">
        <form method="GET" action="{{ route('purchasing.actual-production') }}" class="d-flex gap-3 align-items-center flex-wrap">
            {{-- Filter Plant --}}
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" style="font-size:0.8rem;color:var(--text-muted);">Plant:</label>
                <select name="plant" class="filter-select" onchange="this.form.submit()">
                    <option value="ALL">-- Semua Plant --</option>
                    @foreach($availablePlants as $p)
                        <option value="{{ $p }}" {{ ($selectedPlant ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Item Code --}}
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" style="font-size:0.8rem;color:var(--text-muted);">Material Code:</label>
                <select name="item_code" class="filter-select" onchange="this.form.submit()">
                    <option value="ALL">-- Semua Item --</option>
                    @foreach($availableItemCodes as $code)
                        <option value="{{ $code }}" {{ $itemCode === $code ? 'selected' : '' }}>{{ $code }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Pengantaran --}}
            <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" style="font-size:0.8rem;color:var(--text-muted);">Kategori:</label>
                <select name="delivery_category" class="filter-select" onchange="this.form.submit()">
                    <option value="">-- Semua Pengantaran --</option>
                    @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                        <option value="{{ $dc->code }}" {{ ($selectedDeliveryCategory ?? '') === $dc->code ? 'selected' : '' }}>
                            {{ $dc->code }} - {{ $dc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Search Box --}}
            <div class="d-flex align-items-center gap-2">
                <input type="text" name="search" class="form-control form-control-sm bg-dark border-secondary text-white" style="width: 220px;" placeholder="Cari Item / Supplier / Batch..." value="{{ $search }}">
            </div>

            <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3"><i class="fa fa-search me-1"></i>Cari</button>
            
            @if($search || ($itemCode && $itemCode !== 'ALL') || ($selectedPlant && $selectedPlant !== 'ALL') || ($selectedDeliveryCategory ?? ''))
                <a href="{{ route('purchasing.actual-production') }}" class="nav-link-pill" style="font-size:0.8rem;"><i class="fa fa-times me-1"></i>Reset Filter</a>
            @endif

            {{-- Selection & Mass Actions --}}
            <div class="ms-auto d-flex align-items-center gap-2">
                <button type="button" id="btnBulkDelete" class="btn-danger-custom d-none" onclick="confirmBulkDelete()">
                    <i class="bi bi-trash-fill"></i> Delete Selection (<span id="bulkDeleteCount">0</span>)
                </button>
                @if($totalLogsCount > 0)
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold" onclick="triggerDeleteAllModal()">
                        <i class="bi bi-trash3 me-1"></i> Hapus Semua Data
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- ═══ DATA TABLE: ACTUAL PRODUCTION LOGS ═══ -->
    <div class="glass-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-white mb-0 brand-font">
                    <i class="bi bi-table text-warning me-2"></i>Daftar Log Aktual Produksi
                </h5>
                <small class="text-muted">Menampilkan seluruh baris transaksi aktual produksi hasil import Excel atau input manual.</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark border border-secondary text-light font-monospace">
                    Total: {{ $logs->total() }} Baris Log Terfilter
                </span>
            </div>
        </div>

        <div class="table-responsive style-scrollbar">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" id="checkAll" class="form-check-input" title="Pilih Semua">
                        </th>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Tanggal Produksi</th>
                        <th>Plant</th>
                        <th>Supplier</th>
                        <th>Material Code</th>
                        <th>Deskripsi Barang</th>
                        <th class="text-end" style="color: #34d399;">Qty Produksi</th>
                        <th class="text-center">Status Qty</th>
                        <th class="text-center">Kategori</th>
                        <th class="text-center">Batch ID</th>
                        <th class="text-center" style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $i => $row)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="row-checkbox form-check-input" value="{{ $row->id }}">
                        </td>
                        <td class="text-center font-monospace text-muted">{{ ($logs->currentPage() - 1) * $logs->perPage() + $i + 1 }}</td>
                        <td>
                            <span class="badge bg-dark border border-secondary text-warning font-monospace px-2.5 py-1">
                                <i class="bi bi-calendar-event me-1"></i>{{ Carbon\Carbon::parse($row->tanggal_produksi)->format('d/m/Y') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-plant">{{ $row->factory_code ?: 'KIP 1' }}</span>
                        </td>
                        <td>
                            @if($row->supplier_name)
                                <div class="text-white small fw-bold">{{ $row->supplier_name }}</div>
                                <span class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $row->supplier_code }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong class="text-white font-monospace fs-7">{{ $row->item_code }}</strong>
                        </td>
                        <td>
                            <div class="text-light fw-medium">{{ $row->description ?: 'Material Item' }}</div>
                        </td>
                        <td class="text-end font-monospace">
                            @if($row->qty > 0)
                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-3 py-1 rounded-pill fw-bold" style="font-size: 0.95rem;">
                                    {{ number_format($row->qty) }} <small class="fw-normal text-muted" style="font-size: 0.75rem;">PCS</small>
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary px-3 py-1 rounded-pill fw-bold" style="font-size: 0.95rem;">
                                    0 <small class="fw-normal text-muted" style="font-size: 0.75rem;">PCS</small>
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row->qty > 0)
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-40 px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-25 text-white-50 border border-secondary border-opacity-40 px-2 py-1"><i class="bi bi-pause-circle me-1"></i>Zero Prod</span>
                            @endif
                        </td>
                        <td class="text-center text-nowrap">
                            {!! $row->delivery_category_badge ?? '' !!}
                        </td>
                        <td class="text-center">
                            @if($row->import_batch_id)
                                <span class="badge bg-dark text-muted font-monospace border border-secondary border-opacity-25" style="font-size: 0.68rem;" title="Excel Row #{{ $row->excel_row_number }}">
                                    {{ Str::limit($row->import_batch_id, 14) }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 0.72rem;">Manual</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <button class="btn-icon btn-icon-edit"
                                    onclick="openEditModal({{ $row->id }},'{{ $row->tanggal_produksi }}','{{ $row->item_code }}',{{ $row->qty }},'{{ $row->delivery_category_code ?? 'LOC' }}','{{ $row->factory_code ?? 'KIP 1' }}','{{ addslashes($row->supplier_code ?? '') }}','{{ addslashes($row->supplier_name ?? '') }}','{{ addslashes($row->description ?? '') }}')"
                                    title="Edit Log"><i class="fa fa-pen"></i></button>
                                <button class="btn-icon btn-icon-del"
                                    onclick="confirmDelete({{ $row->id }},'{{ $row->item_code }}')"
                                    title="Hapus Log"><i class="fa fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5">
                            <div class="empty-state">
                                <i class="bi bi-gear-wide-connected" style="font-size: 3rem; color: var(--text-muted); opacity: 0.5;"></i>
                                <h6 class="text-white mt-3 mb-1">Belum Ada Data Aktual Produksi</h6>
                                <p class="text-muted small mb-3">Tabel ini kosong karena data belum diinput atau telah dihapus. Silakan unggah file Excel Actual Production.</p>
                                <button type="button" class="btn-import" data-bs-toggle="modal" data-bs-target="#modalImportActual">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Upload Excel Sekarang
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary border-opacity-25 flex-wrap gap-2">
                <div class="text-muted small">Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }} log</div>
                <div>{!! $logs->withQueryString()->links('pagination::bootstrap-5') !!}</div>
            </div>
        @endif
    </div>
</div>

<!-- ════════════ MODAL IMPORT EXCEL (WITH SHEETJS LIVE PREVIEW) ════════════ -->
<div class="modal fade" id="modalImportActual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom border-secondary border-opacity-25 py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-excel-fill text-success fs-4"></i>
                    <div>
                        <h5 class="modal-title brand-font text-white mb-0">Import Data Aktual Produksi dari Excel</h5>
                        <small class="text-muted">Prinsip: 1 baris Excel = 1 log produksi (Produksi Qty 0 tetap sah &amp; diimport)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 style-scrollbar">
                {{-- STEP 1: DOWNLOAD TEMPLATE & FILE UPLOAD --}}
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.15);">
                            <label class="form-label text-info d-block fw-bold"><i class="bi bi-file-earmark-arrow-down me-1"></i>1. File Template</label>
                            <p class="text-muted small mb-3">Unduh template CSV/Excel dengan struktur kolom standar.</p>
                            <a href="{{ route('purchasing.actual-production.template') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold w-100">
                                <i class="fa fa-download me-1"></i> Unduh Template
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="p-3 rounded-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(16,185,129,0.3);">
                            <label class="form-label text-success d-block fw-bold"><i class="bi bi-cloud-arrow-up-fill me-1"></i>2. Pilih File Excel (.xlsx, .xls, .csv)</label>
                            <input type="file" id="excelFileInputProd" class="form-control bg-dark text-white border-secondary" accept=".xlsx, .xls, .csv" onchange="handleProductionExcelFile(event)">
                            <div class="form-text text-muted small mt-1">Sistem membaca kolom: Supplier Code, Supplier Name, Plant, Material Code, Description, Produksi.</div>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: LIVE AUDIT STATS BOX --}}
                <div id="previewStatsBoxProd" class="d-none mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2.5 rounded-3 bg-dark border border-secondary border-opacity-50">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-primary px-3 py-2 fs-7" id="statTotalRowsProd">Total: 0 Baris</span>
                            <span class="badge bg-success px-3 py-2 fs-7" id="statValidRowsProd">✓ 0 Siap Import</span>
                            <span class="badge bg-info bg-opacity-25 text-info px-3 py-2 fs-7 border border-info border-opacity-25" id="statZeroRowsProd">0 Zero Production</span>
                            <span class="badge bg-danger px-3 py-2 fs-7 d-none" id="statInvalidRowsProd">✕ 0 Baris Rusak</span>
                        </div>
                        <small class="text-muted" id="previewFileMetaProd"></small>
                    </div>
                </div>

                {{-- STEP 3: PREVIEW TABLE CONTAINER --}}
                <div id="previewTableContainerProd" class="d-none border border-secondary border-opacity-25 rounded-3 overflow-hidden">
                    <div class="table-responsive style-scrollbar" style="max-height: 380px;">
                        <table class="table table-dark table-hover table-striped align-middle mb-0" style="font-size: 0.83rem;">
                            <thead class="sticky-top bg-dark border-bottom border-secondary">
                                <tr>
                                    <th class="text-center" style="width: 40px;">Row</th>
                                    <th>Plant</th>
                                    <th>Supplier Code</th>
                                    <th>Supplier Name</th>
                                    <th>Material Code</th>
                                    <th>Deskripsi Barang</th>
                                    <th class="text-end text-warning">Qty Produksi</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBodyProd">
                                {{-- Rows populated dynamically by SheetJS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25 py-3 px-4 justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" id="btnConfirmImportProd" class="btn-import px-4" onclick="submitParsedProductionData()" disabled>
                    <i class="bi bi-check2-circle me-1"></i> Konfirmasi &amp; Import Data
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL ADD MANUAL --}}
<div class="modal fade" id="modalAddActual" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font"><i class="fa fa-plus-circle me-2 text-warning"></i>Tambah Log Aktual Produksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddActual" method="POST" action="{{ route('purchasing.actual-production.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Produksi <span style="color:#f87171">*</span></label>
                        <input type="date" name="tanggal_produksi" id="add_tanggal_produksi" class="form-control text-white fw-bold" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Item Code / Material <span style="color:#f87171">*</span></label>
                            <button type="button" class="btn p-0 text-info text-decoration-none small fw-bold" onclick="openSearchModalActualProd('add')">
                                <i class="bi bi-window-stack"></i> Cari Item
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" name="item_code" id="add_item_code" class="form-control text-white fw-bold font-monospace" placeholder="Cth: 1312006 / 645090" required autocomplete="off">
                            <button type="button" class="btn btn-outline-info fw-bold" onclick="openSearchModalActualProd('add')">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Plant / Pabrik</label>
                            <select name="factory_code" id="add_factory_code" class="form-select bg-dark border-secondary text-white fw-bold">
                                <option value="KIP 1" selected>KIP 1</option>
                                <option value="KIP 2">KIP 2</option>
                                <option value="KIP 4">KIP 4</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Qty Produksi <span style="color:#f87171">*</span></label>
                            <input type="number" name="qty" class="form-control text-white fw-bold font-monospace" placeholder="0" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-info fw-bold"><i class="bi bi-truck me-1"></i> Kategori Pengantaran</label>
                        <select name="delivery_category_code" id="add_delivery_category_code" class="form-select bg-dark border-info text-white fw-bold">
                            @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                <option value="{{ $dc->code }}" {{ $dc->code === 'LOC' ? 'selected' : '' }}>
                                    {{ $dc->code }} - {{ $dc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold px-3 py-2 rounded-3" id="btn-submit-add"><i class="fa fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEditActual" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font"><i class="fa fa-pen me-2 text-warning"></i>Edit Log Produksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditActual" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Produksi</label>
                        <input type="date" name="tanggal_produksi" id="edit_tanggal_produksi" class="form-control text-white fw-bold" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-0">Material Code</label>
                        <input type="text" name="item_code" id="edit_item_code" class="form-control text-white fw-bold font-monospace" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Plant</label>
                            <input type="text" name="factory_code" id="edit_factory_code" class="form-control text-white">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Qty Produksi</label>
                            <input type="number" name="qty" id="edit_qty" class="form-control text-white fw-bold font-monospace" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-info fw-bold"><i class="bi bi-truck me-1"></i> Kategori Pengantaran</label>
                        <select name="delivery_category_code" id="edit_delivery_category_code" class="form-select bg-dark border-info text-white fw-bold">
                            @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                <option value="{{ $dc->code }}">
                                    {{ $dc->code }} - {{ $dc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold px-3 py-2 rounded-3"><i class="fa fa-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL SEARCH ITEM STEP 5 --}}
<div class="modal fade" id="modalSearchItemCodeActualProd" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-secondary">
            <div class="modal-header border-bottom border-secondary border-opacity-25 bg-dark">
                <h5 class="modal-title brand-font text-white d-flex align-items-center gap-2">
                    <i class="bi bi-window-stack text-info fs-4"></i>
                    <span>Pilih Item Code dari Master Forecast</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-dark">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-muted border-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" id="inputSearchItemCodeModalActual" class="form-control bg-dark text-white border-secondary" placeholder="Cari Item Code atau Deskripsi..." onkeyup="filterItemCodeModalActualTable()">
                    </div>
                </div>
                <div class="table-responsive rounded-3 border border-secondary border-opacity-25" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-dark table-hover table-striped align-middle mb-0 text-start" id="tableModalSearchActualProd" style="font-size: 0.85rem;">
                        <thead class="bg-dark text-info fw-bold sticky-top">
                            <tr>
                                <th class="text-center" style="width: 45px;">#</th>
                                <th>Item Code / Part No.</th>
                                <th>Deskripsi Material</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itemsWithForecastDetails ?? [] as $idx => $item)
                                <tr class="item-search-row-actual">
                                    <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                    <td><strong class="text-info font-monospace">{{ $item['item_code'] }}</strong></td>
                                    <td class="text-light">{{ $item['description'] }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" onclick="selectItemCodeFromModalActual('{{ $item['item_code'] }}')">
                                            <i class="bi bi-check-circle-fill me-1"></i> Pilih
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
</div>

{{-- MODAL DELETE SINGLE --}}
<div class="modal fade" id="modalDeleteActual" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font text-danger"><i class="fa fa-trash me-2"></i>Konfirmasi Hapus Log</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Apakah Anda yakin ingin menghapus data produksi aktual untuk item <strong id="del_item_code"></strong>?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="formDeleteActual" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash me-1"></i>Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL BULK DELETE CONFIRM --}}
<div class="modal fade" id="modalBulkDeleteConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Terpilih</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-start pt-3">
                Apakah Anda yakin ingin menghapus <strong id="bulkConfirmCount" class="text-warning">0</strong> data log aktual produksi yang dipilih? Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="formBulkDelete" method="POST" action="{{ route('purchasing.actual-production.destroy-bulk') }}">
                    @csrf
                    <div id="bulkDeleteIdsContainer"></div>
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash me-1"></i>Hapus Terpilih</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DELETE ALL CONFIRM --}}
<div class="modal fade" id="modalConfirmDeleteAllActualProd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-bottom border-danger border-opacity-25 bg-danger bg-opacity-10 py-3">
                <h5 class="modal-title brand-font text-danger fw-bold"><i class="bi bi-radioactive me-2"></i>Konfirmasi Hapus Semua Data Log</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-start">
                <div class="alert alert-danger bg-danger bg-opacity-20 border-danger mb-3">
                    <i class="bi bi-exclamation-octagon-fill me-1"></i> <strong>Perhatian: Tindakan Destruktif!</strong>
                </div>
                <p class="text-white mb-1">Apakah Anda yakin ingin mengosongkan <strong>seluruh data aktual produksi ({{ number_format($totalLogsCount) }} baris)</strong>?</p>
                <p class="text-muted small mb-0">Tindakan ini akan mereset seluruh log actual production dari database.</p>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25 py-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('purchasing.actual-production.destroy-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Semua Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let parsedExcelRowsProd = [];

// ── SHEETJS CLIENT-SIDE PARSER & LIVE AUDIT PREVIEW ──
function resolveFieldJS(row, candidates) {
    const keys = Object.keys(row);
    for (let cand of candidates) {
        const cleanCand = cand.toLowerCase().replace(/[^a-z0-9]/g, '');
        for (let k of keys) {
            const cleanK = k.toLowerCase().replace(/[^a-z0-9]/g, '');
            if (cleanK === cleanCand && row[k] !== undefined && row[k] !== null && String(row[k]).trim() !== '') {
                return String(row[k]).trim();
            }
        }
    }
    return '';
}

function parseNumericStockJS(val) {
    if (val === undefined || val === null) return null;
    let s = String(val).trim();
    s = s.replace(/[^\d\.\,\-]/g, '');
    if (s === '' || s === '-') return null;

    if (/^-?\d{1,3}(\.\d{3})+$/.test(s)) {
        s = s.replace(/\./g, '');
    } else if (/^-?\d{1,3}(,\d{3})+$/.test(s)) {
        s = s.replace(/,/g, '');
    } else if (s.includes(',') && !s.includes('.')) {
        s = s.replace(',', '.');
    }

    const n = parseFloat(s);
    return isNaN(n) ? null : Math.round(n);
}

function handleProductionExcelFile(e) {
    const file = e.target.files[0];
    if (!file) return;

    document.getElementById('previewFileMetaProd').innerText = `${file.name} (${(file.size/1024).toFixed(1)} KB)`;

    const reader = new FileReader();
    reader.onload = function(evt) {
        try {
            const data = new Uint8Array(evt.target.result);
            const workbook = XLSX.read(data, { type: 'array', cellDates: true });
            const firstSheet = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheet];
            const rawJson = XLSX.utils.sheet_to_json(worksheet, { defval: '', raw: false });

            processProductionPreviewRows(rawJson);
        } catch (err) {
            if (window.notify) {
                window.notify.error('Gagal Baca File', 'Gagal membaca file Excel: ' + err.message);
            } else {
                console.error(err);
            }
        }
    };
    reader.readAsArrayBuffer(file);
}

function processProductionPreviewRows(rawJson) {
    parsedExcelRowsProd = [];
    let validCount = 0;
    let zeroCount = 0;
    let invalidCount = 0;

    const tbody = document.getElementById('previewTableBodyProd');
    tbody.innerHTML = '';

    rawJson.forEach((row, idx) => {
        const itemCode = resolveFieldJS(row, ['material_code', 'item_code', 'part_number', 'part_no', 'drawing', 'material', 'kode_barang', 'kode_material', 'pn', 'sku']);
        if (!itemCode || itemCode.toUpperCase() === 'ITEM CODE' || itemCode.toUpperCase() === 'MATERIAL CODE') {
            return;
        }

        const plant = resolveFieldJS(row, ['plant', 'factory_code', 'pabrik', 'lokasi', 'factory', 'kode_pabrik']) || 'KIP 1';
        const suppCode = resolveFieldJS(row, ['supplier_code', 'vendor_code', 'kode_supplier', 'supplier', 'vendor', 'kd_supp', 'kode_vendor']);
        const suppName = resolveFieldJS(row, ['supplier_name', 'vendor_name', 'nama_supplier', 'nama_vendor']);
        const desc = resolveFieldJS(row, ['description', 'deskripsi', 'nama_barang', 'item_name', 'nama_material', 'keterangan']);
        
        const qtyRaw = resolveFieldJS(row, ['production_qty', 'produksi', 'qty', 'actual_production', 'jumlah', 'kuantitas', 'realisasi', 'vol']);
        const parsedQty = parseNumericStockJS(qtyRaw);

        const isInvalid = (parsedQty === null);
        const isZero = (parsedQty === 0);

        if (isInvalid) {
            invalidCount++;
        } else {
            validCount++;
            if (isZero) zeroCount++;

            parsedExcelRowsProd.push({
                excel_row_number: idx + 2,
                plant: plant.toUpperCase(),
                supplier_code: suppCode.toUpperCase(),
                supplier_name: suppName,
                material_code: itemCode.toUpperCase(),
                description: desc,
                production_qty: parsedQty,
                tanggal_produksi: new Date().toISOString().slice(0, 10),
            });
        }

        if (idx < 50) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center font-monospace text-muted">${idx + 2}</td>
                <td><span class="badge-plant">${plant.toUpperCase()}</span></td>
                <td class="font-monospace text-muted">${suppCode || '-'}</td>
                <td class="text-light">${suppName || '-'}</td>
                <td><strong class="text-white font-monospace">${itemCode.toUpperCase()}</strong></td>
                <td class="text-muted small">${desc || '-'}</td>
                <td class="text-end font-monospace fw-bold ${isZero ? 'text-muted' : 'text-success'}">
                    ${isInvalid ? '<span class="text-danger">Invalid</span>' : Number(parsedQty).toLocaleString('id-ID')} PCS
                </td>
                <td class="text-center">
                    ${isInvalid 
                        ? '<span class="badge bg-danger">✕ Error</span>' 
                        : (isZero ? '<span class="badge bg-secondary text-white-50">Zero Prod</span>' : '<span class="badge bg-success">✓ Ready</span>')}
                </td>
            `;
            tbody.appendChild(tr);
        }
    });

    document.getElementById('statTotalRowsProd').innerText = `Total: ${rawJson.length} Baris`;
    document.getElementById('statValidRowsProd').innerText = `✓ ${validCount} Siap Import`;
    document.getElementById('statZeroRowsProd').innerText = `0 ${zeroCount} Zero Production`;

    const statInv = document.getElementById('statInvalidRowsProd');
    if (invalidCount > 0) {
        statInv.innerText = `✕ ${invalidCount} Baris Rusak`;
        statInv.classList.remove('d-none');
    } else {
        statInv.classList.add('d-none');
    }

    document.getElementById('previewStatsBoxProd').classList.remove('d-none');
    document.getElementById('previewTableContainerProd').classList.remove('d-none');
    document.getElementById('btnConfirmImportProd').disabled = (parsedExcelRowsProd.length === 0);
}

function submitParsedProductionData() {
    if (parsedExcelRowsProd.length === 0) return;

    const btn = document.getElementById('btnConfirmImportProd');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengimport seluruh baris data...';

    fetch("{{ route('purchasing.actual-production.import') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ rows: parsedExcelRowsProd })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: '<span style="color: #10b981; font-family: Outfit, sans-serif; font-weight: 700;">Import Berhasil &amp; Lengkap!</span>',
                html: `<div style="color: #f8fafc; font-size: 0.95rem; line-height: 1.6;">${data.message}</div>`,
                icon: 'success',
                background: '#0f1623',
                iconColor: '#10b981',
                confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Selesai &amp; Muat Ulang',
                confirmButtonColor: '#10b981',
            }).then(() => {
                window.location.reload();
            });
        } else {
            if (window.notify) {
                window.notify.error('Gagal Import', data.message || 'Gagal mengimpor data.');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Konfirmasi & Import Data';
        }
    })
    .catch(err => {
        if (window.notify) {
            window.notify.error('Kesalahan Jaringan', err.message || 'Terjadi kesalahan sistem.');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Konfirmasi & Import Data';
    });
}

// ── MODAL & BULK DELETE HANDLERS ──
function triggerDeleteAllModal() {
    new bootstrap.Modal(document.getElementById('modalConfirmDeleteAllActualProd')).show();
}

function openEditModal(id, tanggal, itemCode, qty, deliveryCategoryCode, plant, suppCode, suppName, desc) {
    document.getElementById('edit_tanggal_produksi').value = tanggal;
    document.getElementById('edit_item_code').value = itemCode;
    document.getElementById('edit_qty').value = qty;
    if (document.getElementById('edit_factory_code')) document.getElementById('edit_factory_code').value = plant || 'KIP 1';
    if (document.getElementById('edit_delivery_category_code')) document.getElementById('edit_delivery_category_code').value = deliveryCategoryCode || 'LOC';
    document.getElementById('formEditActual').action = '/purchasing/actual-production/' + id;
    new bootstrap.Modal(document.getElementById('modalEditActual')).show();
}

function confirmDelete(id, itemCode) {
    document.getElementById('del_item_code').textContent = itemCode;
    document.getElementById('formDeleteActual').action = '/purchasing/actual-production/' + id;
    new bootstrap.Modal(document.getElementById('modalDeleteActual')).show();
}

// Checkbox bulk handlers
const checkAll = document.getElementById('checkAll');
const rowCheckboxes = document.querySelectorAll('.row-checkbox');
const btnBulkDelete = document.getElementById('btnBulkDelete');
const bulkDeleteCountSpan = document.getElementById('bulkDeleteCount');

function updateBulkDeleteState() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    const count = checked.length;
    if (count > 0) {
        btnBulkDelete.classList.remove('d-none');
        bulkDeleteCountSpan.textContent = count;
    } else {
        btnBulkDelete.classList.add('d-none');
        bulkDeleteCountSpan.textContent = 0;
    }
}

if (checkAll) {
    checkAll.addEventListener('change', function() {
        rowCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkDeleteState();
    });
}

rowCheckboxes.forEach(cb => {
    cb.addEventListener('change', function() {
        updateBulkDeleteState();
        if (!this.checked && checkAll) checkAll.checked = false;
    });
});

function confirmBulkDelete() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    if (checked.length === 0) return;
    
    document.getElementById('bulkConfirmCount').textContent = checked.length;
    const container = document.getElementById('bulkDeleteIdsContainer');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    new bootstrap.Modal(document.getElementById('modalBulkDeleteConfirm')).show();
}

// Search Modal helper
let currentTargetFormType = 'add';
function openSearchModalActualProd(formType) {
    currentTargetFormType = formType;
    new bootstrap.Modal(document.getElementById('modalSearchItemCodeActualProd')).show();
}

function selectItemCodeFromModalActual(itemCode) {
    const prefix = currentTargetFormType === 'edit' ? 'edit_' : 'add_';
    const itemInput = document.getElementById(prefix + 'item_code');
    if (itemInput) itemInput.value = itemCode;
    const modalEl = document.getElementById('modalSearchItemCodeActualProd');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
}

function filterItemCodeModalActualTable() {
    const q = (document.getElementById('inputSearchItemCodeModalActual').value || '').toUpperCase();
    const rows = document.querySelectorAll('#tableModalSearchActualProd tbody tr.item-search-row-actual');
    rows.forEach(r => {
        r.style.display = r.innerText.toUpperCase().includes(q) ? '' : 'none';
    });
}
</script>

@include('partials.confirm-modal')
@include('partials.import-preview-modal')
<script src="{{ asset('js/kawai-notify.js') }}"></script>
<script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
