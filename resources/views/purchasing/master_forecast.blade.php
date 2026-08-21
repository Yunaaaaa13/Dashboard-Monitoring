<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Master Forecast | Purchasing PT Kawai Indonesia</title>
    <meta name="description" content="Master data perencanaan kebutuhan material (Forecast) berdasarkan Part Number dan Periode.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-secondary: #121826;
            --card-bg: rgba(23, 31, 48, 0.85);
            --card-border: rgba(255,255,255,0.08);
            --accent-gold: #e2b34a;
            --accent-blue: #3b82f6;
            --accent-emerald: #10b981;
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
            background: rgba(18,24,38,0.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 1.75rem;
            position: sticky; top: 0; z-index: 1000;
        }
        .brand-logo-text {
            font-weight: 800; font-size: 1.25rem; letter-spacing: 0.8px;
            background: linear-gradient(135deg, #fff 0%, var(--accent-gold) 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .nav-link-pill {
            color: var(--text-muted); font-size: 0.82rem; font-weight: 500;
            padding: 0.4rem 0.9rem; border-radius: 20px; transition: all 0.2s;
            text-decoration: none;
        }
        .nav-link-pill:hover, .nav-link-pill.active {
            background: rgba(59,130,246,0.18); color: #93c5fd;
        }
        .nav-link-pill.active-gold { background: rgba(226,179,74,0.18); color: var(--accent-gold); }
        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px; padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            backdrop-filter: blur(12px);
        }
        .page-header-title {
            font-size: 1.7rem; font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, var(--accent-blue) 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .btn-add {
            background: linear-gradient(135deg, var(--accent-blue), #6366f1);
            border: none; color: #fff; font-weight: 600;
            padding: 0.55rem 1.25rem; border-radius: 10px;
            transition: all 0.25s; font-size: 0.88rem;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(59,130,246,0.4); color:#fff; }
        .filter-bar {
            display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;
        }
        .filter-select {
            background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
            color: #fff; border-radius: 10px; padding: 0.45rem 1rem; font-size: 0.88rem;
        }
        .filter-select option { background: #1e2a3a; color: #fff; }
        .table-custom { color: var(--text-main); font-size: 0.88rem; }
        .table-custom thead th {
            background: rgba(59,130,246,0.15); color: #93c5fd;
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 0.85rem 1rem; white-space: nowrap;
        }
        .table-custom tbody tr {
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: background 0.15s;
        }
        .table-custom tbody tr:hover { background: rgba(255,255,255,0.04); }
        .table-custom tbody td { padding: 0.8rem 1rem; vertical-align: middle; }
        .badge-periode {
            background: rgba(59,130,246,0.2); color: #93c5fd;
            border: 1px solid rgba(59,130,246,0.35); border-radius: 20px;
            font-size: 0.75rem; padding: 0.25rem 0.75rem; font-weight: 600;
        }
        .badge-qty {
            font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem;
            color: #34d399;
        }
        .btn-icon {
            width: 32px; height: 32px; border-radius: 8px; border: none;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.78rem; transition: all 0.2s; cursor: pointer;
        }
        .btn-icon-edit { background: rgba(59,130,246,0.2); color: #93c5fd; }
        .btn-icon-edit:hover { background: rgba(59,130,246,0.4); }
        .btn-icon-del { background: rgba(239,68,68,0.2); color: #f87171; }
        .btn-icon-del:hover { background: rgba(239,68,68,0.4); }
        .empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }
        .kpi-mini { display: flex; align-items: center; gap: 0.6rem; }
        .kpi-mini .kpi-num { font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 800; color: #60a5fa; }
        .modal-content { background: #1a2236; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; color: var(--text-main); }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,0.08); }
        .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); }
        .form-control, .form-select {
            background: #1e293b !important; border: 1px solid rgba(255,255,255,0.12);
            color: #fff; border-radius: 10px;
        }
        .form-control:focus, .form-select:focus {
            background: #1e293b !important; border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.2); color: #fff;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.35); }
        .form-label { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .toast-alert {
            position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
            min-width: 280px; border-radius: 12px; padding: 1rem 1.25rem;
            font-weight: 500; font-size: 0.88rem; display: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }
        .toast-success { background: rgba(16,185,129,0.95); color: #fff; }
        .toast-error { background: rgba(239,68,68,0.95); color: #fff; }
    </style>
</head>
<body>

{{-- NAVBAR --}}
<nav class="top-navbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard.overview') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-music-note-beamed text-warning fs-4"></i>
            <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
        </a>
        <span class="badge bg-primary text-white px-3 py-1 rounded-pill fw-bold" style="font-size:0.72rem;">
            <i class="bi bi-chart-line me-1"></i> STEP 1: MASTER FORECAST
        </span>
    </div>
    <div>
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.master.forecast', 'hasFaqModal' => true])
    </div>
</nav>

@include('partials.faq-modal')

<div class="container-fluid px-4 py-4">

    @include('partials.toast-and-notification-popup')

    {{-- 4-STEP WORKFLOW STEPPER BANNER --}}
    <div class="glass-card p-3 mb-4" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.08) 100%); border: 1px solid rgba(16, 185, 129, 0.3);">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success text-white fw-bold px-3 py-1 rounded-pill" style="font-size: 0.75rem;">STEP 1: ACTIVE</span>
                <h5 class="fw-bold text-white mb-0 brand-font"><i class="bi bi-1-circle-fill text-success me-2"></i>Master Forecast</h5>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('purchasing.outstanding') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-success bg-opacity-25 border border-success border-opacity-50 text-white small fw-bold text-decoration-none">
                    <span class="badge bg-success rounded-circle">1</span> Forecast
                </a>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <a href="{{ route('purchasing.master-po') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">2</span> Master PO
                </a>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <a href="{{ route('purchasing.input') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">3</span> Incoming
                </a>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <a href="{{ route('purchasing.outstanding-po') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">4</span> Outstanding
                </a>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <a href="{{ route('purchasing.actual-production') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">5</span> Aktual Prod
                </a>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <a href="{{ route('purchasing.analysis') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">6</span> Hasil Akhir
                </a>
                {{-- KPI Kurs USD/IDR Terkini --}}
                @include('partials.kurs-kpi-banner')
            </div>
        </div>
    </div>

    {{-- HEADER --}}
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-header-title mb-1">Step 1: Master Forecast</h1>
            <p class="mb-0" style="color:var(--text-muted);font-size:0.88rem;">
                <i class="fa fa-info-circle me-1"></i>
                Data perencanaan kebutuhan material per Part Number dan Periode. Terhubung langsung untuk komparasi dengan penerimaan aktual PO.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btnBulkDeleteMasterForecast" class="btn btn-danger btn-sm rounded-pill px-3 d-none" onclick="confirmBulkDeleteMasterForecast()">
                <i class="fa fa-trash me-1"></i> Hapus Terpilih (<span id="bulkDeleteCountMasterForecast">0</span>)
            </button>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalAddForecast" id="btn-add-forecast">
                <i class="fa fa-plus me-1"></i> Tambah Forecast
            </button>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="glass-card mb-4">
        <form method="GET" action="{{ route('purchasing.master.forecast') }}" class="filter-bar">
            <label class="form-label mb-0" style="font-size:0.8rem;color:var(--text-muted);">Filter Periode:</label>
            <select name="periode" id="filter-periode" class="filter-select" onchange="this.form.submit()">
                <option value="">-- Semua Periode --</option>
                @foreach($availablePeriodes as $p)
                    <option value="{{ $p }}" {{ $periode == $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>

            <label class="form-label mb-0 ms-2" style="font-size:0.8rem;color:var(--text-muted);">Pengantaran:</label>
            <select name="delivery_category" id="filter-delivery-category" class="filter-select" onchange="this.form.submit()">
                <option value="">-- Semua Kategori Pengantaran --</option>
                @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                    <option value="{{ $dc->code }}" {{ ($selectedDeliveryCategory ?? '') == $dc->code ? 'selected' : '' }}>
                        {{ $dc->code }} - {{ $dc->name }}
                    </option>
                @endforeach
            </select>

            @if($periode || ($selectedDeliveryCategory ?? ''))
                <a href="{{ route('purchasing.master.forecast') }}" class="nav-link-pill" style="font-size:0.8rem;">
                    <i class="fa fa-times me-1"></i>Reset
                </a>
            @endif
            <div class="ms-auto kpi-mini">
                <span class="kpi-num">{{ $forecasts->count() }}</span>
                <span style="color:var(--text-muted);font-size:0.82rem;">No. PO / Ref</span>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-custom table-borderless align-middle" id="tbl-forecast">
                <thead>
                    <tr>
                        <th class="text-center" rowspan="2" style="width: 40px; vertical-align: middle;">
                            <input type="checkbox" id="checkAllMasterForecast" class="form-check-input">
                        </th>
                        <th rowspan="2" style="vertical-align: middle;">#</th>
                        <th rowspan="2" style="vertical-align: middle;">Part Number (Item Code)</th>
                        <th rowspan="2" style="vertical-align: middle;">Description</th>
                        <th rowspan="2" style="vertical-align: middle;">Periode</th>
                        <th rowspan="2" class="text-end text-nowrap" style="color:#e2b34a; min-width: 100px; white-space: nowrap; vertical-align: middle;">Price</th>
                        <th rowspan="2" class="text-center text-nowrap" style="color:#38bdf8; min-width: 80px; white-space: nowrap; vertical-align: middle;">Currency</th>
                        <th colspan="2" class="text-center text-nowrap" style="background: rgba(239,68,68,0.15); color: #f87171; border-bottom: 1px solid rgba(239,68,68,0.3); white-space: nowrap;">OUTSTANDING</th>
                        <th rowspan="2" class="text-end" style="color:#f59e0b; vertical-align: middle;">PO (Step 2)</th>
                        <th rowspan="2" class="text-end" style="color:#60a5fa; vertical-align: middle;">Forecast Qty</th>
                        <th rowspan="2" class="text-end" style="color:#10b981; vertical-align: middle;">Incoming (Step 3)</th>
                        <th rowspan="2" class="text-end" style="vertical-align: middle;">PROD</th>
                        <th colspan="2" class="text-center text-nowrap" style="background: rgba(16,185,129,0.15); color: #34d399; border-bottom: 1px solid rgba(16,185,129,0.3); white-space: nowrap;">STOCK AKHIR</th>
                        <th class="text-center" rowspan="2" style="vertical-align: middle;">Aksi</th>
                        <th class="text-center text-nowrap" rowspan="2" style="vertical-align: middle; background: rgba(59,130,246,0.15); color: #60a5fa;">Kategori Pengantaran</th>
                    </tr>
                    <tr>
                        <th class="text-end text-nowrap" style="background: rgba(239,68,68,0.1); color: #fca5a5; font-size: 0.7rem; white-space: nowrap; min-width: 90px;">QTY</th>
                        <th class="text-end text-nowrap" style="background: rgba(239,68,68,0.1); color: #fca5a5; font-size: 0.7rem; white-space: nowrap; min-width: 140px;">AMOUNT</th>
                        <th class="text-end text-nowrap" style="background: rgba(16,185,129,0.1); color: #6ee7b7; font-size: 0.7rem; white-space: nowrap; min-width: 90px;">QTY</th>
                        <th class="text-end text-nowrap" style="background: rgba(16,185,129,0.1); color: #6ee7b7; font-size: 0.7rem; white-space: nowrap; min-width: 140px;">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forecasts as $i => $row)
                    @php
                        $po = $row->calculated_po;
                        $delivery = $row->calculated_delivery;
                        $outPre = (int) ($row->outstanding_pre ?? 0);
                        $stockPre = (int) ($row->stock_pre ?? $row->stock_qty ?? 0);
                        $prod = (int) ($row->production_qty ?? $row->production ?? 0);
                        $price = (float) $row->price;

                        $forecastQty = $row->calculated_forecast;
                        $outstandingQty = $row->calculated_outstanding;
                        $stockAkhir = $row->calculated_stock;

                        $outstandingAmount = $row->calculated_outstanding_amount;
                        $stockAmount = $row->calculated_stock_amount;
                    @endphp
                    <tr data-id="{{ $row->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="row-checkbox-masterforecast form-check-input" value="{{ $row->id }}">
                        </td>
                        <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                        <td><strong style="color:#e2e8f0;font-family:'Outfit',sans-serif;">{{ $row->part_number }}</strong></td>
                        <td style="color:var(--text-muted);">{{ $row->description ?? '-' }}</td>
                        <td><span class="badge-periode">{{ $row->periode ?? $row->period_month }}</span></td>
                        <td class="text-end font-monospace fw-semibold text-nowrap" style="color:#e2b34a; white-space: nowrap; font-size: 0.82rem;">
                            {{ $price > 0 ? number_format($price, 2, ',', '.') : '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ strtoupper($row->currency ?? 'USD') === 'IDR' ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-info bg-opacity-25 text-info border border-info' }} px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                {{ strtoupper($row->currency ?? 'USD') }}
                            </span>
                        </td>
                        <td class="text-end font-monospace fw-bold text-nowrap {{ $outstandingQty < 0 ? 'text-danger' : 'text-light' }}" style="white-space: nowrap; font-size: 0.82rem;">
                            {{ number_format($outstandingQty) }}
                        </td>
                        <td class="text-end font-monospace fw-bold text-nowrap {{ $outstandingAmount < 0 ? 'text-danger' : 'text-light' }}" style="white-space: nowrap; font-size: 0.82rem;">
                            {{ $row->formatAmount($outstandingAmount) }}
                        </td>
                        <td class="text-end font-monospace fw-bold text-warning">
                            @if($po > 0)
                                {{ number_format($po) }}
                            @else
                                <span class="badge bg-dark border border-secondary text-muted">0 (Auto)</span>
                            @endif
                        </td>
                        <td class="text-end"><span class="badge-qty">{{ number_format($forecastQty) }}</span></td>
                        <td class="text-end font-monospace fw-bold text-success">
                            @if($delivery > 0)
                                {{ number_format($delivery) }}
                            @else
                                <span class="badge bg-dark border border-secondary text-muted">0 (Auto)</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace text-light">{{ number_format($prod) }}</td>
                        <td class="text-end font-monospace text-info fw-bold text-nowrap" style="white-space: nowrap; font-size: 0.82rem;">{{ number_format($stockAkhir) }}</td>
                        <td class="text-end font-monospace text-info fw-bold text-nowrap {{ $stockAmount < 0 ? 'text-danger' : '' }}" style="white-space: nowrap; font-size: 0.82rem;">
                            {{ $row->formatAmount($stockAmount) }}
                        </td>
                        <td class="text-center">
                            <button class="btn-icon btn-icon-edit me-1"
                                onclick="openEditModal({{ $row->id }},'{{ $row->part_number }}','{{ addslashes($row->description ?? '') }}','{{ $row->periode ?? $row->period_month }}',{{ $outPre }},{{ $stockPre }},{{ $prod }},{{ $forecastQty }},{{ $price }},'{{ $row->delivery_category_code ?? 'LOC' }}')"
                                title="Edit">
                                <i class="fa fa-pen"></i>
                            </button>
                            <button class="btn-icon btn-icon-del"
                                onclick="confirmDelete({{ $row->id }},'{{ $row->part_number }}')"
                                title="Hapus">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                        <td class="text-center text-nowrap">{!! $row->delivery_category_badge ?? '' !!}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="16">
                            <div class="empty-state">
                                <i class="fa fa-chart-line fa-2x mb-2" style="color:rgba(255,255,255,0.15)"></i>
                                <p class="mb-0">Belum ada data Forecast untuk periode ini.</p>
                                <p style="font-size:0.8rem;">Klik <strong>Tambah Forecast</strong> untuk menambahkan data.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL ADD --}}
<div class="modal fade" id="modalAddForecast" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font"><i class="fa fa-plus-circle me-2" style="color:#60a5fa"></i>Tambah Data Master Forecast & Formula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddForecast" method="POST" action="{{ route('purchasing.master.forecast.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info border-info border-opacity-25 bg-info bg-opacity-10 d-flex align-items-center gap-2 rounded-3 small p-2 mb-3">
                        <i class="fa fa-info-circle text-info"></i>
                        <span><strong>Otomasi Excel:</strong> Qty <strong>PO</strong> &amp; <strong>Incoming</strong> otomatis bernilai 0 / NULL hingga Anda mengisi data di <strong>Step 2 (Master PO)</strong> dan <strong>Step 3 (Incoming)</strong>.</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>Part Number / Item Code <span style="color:#f87171">*</span></span>
                                <button type="button" class="btn p-0 text-info text-decoration-none small fw-bold" onclick="openItemCodeSelectorModal('add_part_number', 'add_description')">
                                    <i class="bi bi-window-stack me-1"></i> Pilih dari Pop-up
                                </button>
                            </label>
                            <div class="input-group">
                                <input type="text" name="part_number" id="add_part_number" class="form-control" list="registeredItemCodesList" onchange="autoFillItemDescription(this, 'add_description')" oninput="autoFillItemDescription(this, 'add_description')" placeholder="Ketik Item Code Baru atau Cari..." required>
                                <button type="button" class="btn btn-outline-info" onclick="openItemCodeSelectorModal('add_part_number', 'add_description')">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Periode (YYYY-MM) <span style="color:#f87171">*</span></label>
                            <input type="month" name="periode" id="add_periode" class="form-control"
                                value="{{ $periode ?: now()->format('Y-m') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" id="add_description" class="form-control" placeholder="Nama / deskripsi material">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price & Mata Uang</label>
                            <div class="input-group">
                                <select name="currency" id="add_currency" class="form-select bg-dark text-warning border-secondary fw-bold px-1" style="max-width: 85px;">
                                    <option value="USD" selected>USD ($)</option>
                                    <option value="IDR">IDR (Rp)</option>
                                </select>
                                <input type="text" inputmode="decimal" name="price" id="add_price" class="form-control" placeholder="0.00 (cth: 227,05 atau 227.05)">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Outstanding Pre-Month</label>
                            <input type="number" name="outstanding_pre" id="add_outstanding_pre" class="form-control" placeholder="0 (cth: -40)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock Pre-Month (Awal)</label>
                            <input type="number" name="stock_pre" id="add_stock_pre" class="form-control" placeholder="0 (cth: 326)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">PROD (Rencana Produksi)</label>
                            <input type="number" name="production_qty" id="add_production_qty" class="form-control" placeholder="0 (cth: 165)">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold" style="color:#60a5fa;"><i class="bi bi-truck me-1"></i> Kategori Pengantaran <span style="color:#f87171">*</span></label>
                            <select name="delivery_category_code" id="add_delivery_category_code" class="form-select bg-dark border-info text-white fw-bold">
                                @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                    <option value="{{ $dc->code }}" {{ $dc->code === 'LOC' ? 'selected' : '' }}>
                                        {{ $dc->code }} - {{ $dc->name }} ({{ $dc->currency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-add" id="btn-submit-add"><i class="fa fa-save me-1"></i>Simpan &amp; Hitung Otomatis</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEditForecast" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font"><i class="fa fa-pen me-2" style="color:#60a5fa"></i>Edit Data Forecast</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditForecast" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Part Number / Item Code</label>
                            <input type="text" id="edit_part_number" class="form-control" readonly style="opacity:0.6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Periode (YYYY-MM)</label>
                            <input type="month" name="periode" id="edit_periode" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" id="edit_description" class="form-control" placeholder="Nama / deskripsi material">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price & Mata Uang</label>
                            <div class="input-group">
                                <select name="currency" id="edit_currency" class="form-select bg-dark text-warning border-secondary fw-bold px-1" style="max-width: 85px;">
                                    <option value="USD">USD ($)</option>
                                    <option value="IDR">IDR (Rp)</option>
                                </select>
                                <input type="text" inputmode="decimal" name="price" id="edit_price" class="form-control" placeholder="0.00 (cth: 227,05 atau 227.05)">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Outstanding Pre-Month</label>
                            <input type="number" name="outstanding_pre" id="edit_outstanding_pre" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock Pre-Month (Awal)</label>
                            <input type="number" name="stock_pre" id="edit_stock_pre" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">PROD (Rencana Produksi)</label>
                            <input type="number" name="production_qty" id="edit_production_qty" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold" style="color:#60a5fa;"><i class="bi bi-truck me-1"></i> Kategori Pengantaran <span style="color:#f87171">*</span></label>
                            <select name="delivery_category_code" id="edit_delivery_category_code" class="form-select bg-dark border-info text-white fw-bold">
                                @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                    <option value="{{ $dc->code }}">
                                        {{ $dc->code }} - {{ $dc->name }} ({{ $dc->currency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-add"><i class="fa fa-save me-1"></i>Update &amp; Recalculate</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DELETE --}}
<div class="modal fade" id="modalDeleteForecast" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" style="color:#f87171"><i class="fa fa-trash me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Hapus data forecast untuk <strong id="del_part_name"></strong>? Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="formDeleteForecast" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash me-1"></i>Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- TOAST --}}
<div class="toast-alert toast-success" id="toastSuccess"><i class="fa fa-check-circle me-2"></i><span id="toastSuccessMsg"></span></div>
<div class="toast-alert toast-error" id="toastError"><i class="fa fa-times-circle me-2"></i><span id="toastErrorMsg"></span></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showToast(type, msg) {
    const el = type === 'success' ? document.getElementById('toastSuccess') : document.getElementById('toastError');
    const span = type === 'success' ? document.getElementById('toastSuccessMsg') : document.getElementById('toastErrorMsg');
    span.textContent = msg;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 3500);
}

function openEditModal(id, partNumber, description, periode, outPre, stockPre, prod, forecastQty, price, deliveryCategoryCode) {
    document.getElementById('edit_part_number').value = partNumber;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_periode').value = periode;
    document.getElementById('edit_outstanding_pre').value = outPre || 0;
    document.getElementById('edit_stock_pre').value = stockPre || 0;
    document.getElementById('edit_production_qty').value = prod || 0;
    document.getElementById('edit_price').value = price || 0;
    if (document.getElementById('edit_delivery_category_code')) {
        document.getElementById('edit_delivery_category_code').value = deliveryCategoryCode || 'LOC';
    }
    document.getElementById('formEditForecast').action = '/purchasing/master/forecast/' + id;
    new bootstrap.Modal(document.getElementById('modalEditForecast')).show();
}

function confirmDelete(id, partNumber) {
    document.getElementById('del_part_name').textContent = partNumber;
    document.getElementById('formDeleteForecast').action = '/purchasing/master/forecast/' + id;
    new bootstrap.Modal(document.getElementById('modalDeleteForecast')).show();
}

// AJAX submit Add
document.getElementById('formAddForecast').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-add');
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Menyimpan...';
    btn.disabled = true;
    const fd = new FormData(this);
    fetch(this.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json()).then(res => {
            btn.innerHTML = '<i class="fa fa-save me-1"></i>Simpan'; btn.disabled = false;
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalAddForecast')).hide();
                showToast('success', res.message || 'Data berhasil disimpan');
                setTimeout(() => location.reload(), 900);
            } else { showToast('error', res.message || 'Gagal menyimpan data'); }
        }).catch(() => { btn.innerHTML = '<i class="fa fa-save me-1"></i>Simpan'; btn.disabled = false; showToast('error', 'Terjadi kesalahan jaringan'); });
});

</script>


<!-- Modal Bulk Delete Confirmation Master Forecast -->
<div class="modal fade" id="modalBulkDeleteMasterForecastConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-danger text-white" style="background: #111827;">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-danger fw-bold"><i class="fa fa-exclamation-triangle me-2"></i> Konfirmasi Hapus Massal Master Forecast</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchasing.master.forecast.destroy-bulk') }}" method="POST" id="formBulkDeleteMasterForecast">
                @csrf
                <div class="modal-body">
                    <div id="bulkDeleteMasterForecastIdsContainer"></div>
                    Apakah Anda yakin ingin menghapus <strong id="bulkDeleteMasterForecastCountText" class="text-danger">0</strong> data Forecast terpilih?
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
        const checkAllMasterForecast = document.getElementById('checkAllMasterForecast');
        const rowCheckboxesMasterForecast = document.querySelectorAll('.row-checkbox-masterforecast');
        const btnBulkDeleteMasterForecast = document.getElementById('btnBulkDeleteMasterForecast');
        const countSpanMasterForecast = document.getElementById('bulkDeleteCountMasterForecast');

        function updateMasterForecastBulkBtn() {
            const checked = document.querySelectorAll('.row-checkbox-masterforecast:checked');
            if (btnBulkDeleteMasterForecast) {
                if (checked.length > 0) {
                    btnBulkDeleteMasterForecast.classList.remove('d-none');
                    countSpanMasterForecast.innerText = checked.length;
                } else {
                    btnBulkDeleteMasterForecast.classList.add('d-none');
                }
            }
        }

        if (checkAllMasterForecast) {
            checkAllMasterForecast.addEventListener('change', function() {
                rowCheckboxesMasterForecast.forEach(cb => cb.checked = this.checked);
                updateMasterForecastBulkBtn();
            });
        }

        rowCheckboxesMasterForecast.forEach(cb => {
            cb.addEventListener('change', function() {
                if (checkAllMasterForecast) {
                    checkAllMasterForecast.checked = (document.querySelectorAll('.row-checkbox-masterforecast:checked').length === rowCheckboxesMasterForecast.length);
                }
                updateMasterForecastBulkBtn();
            });
        });
    });

    function confirmBulkDeleteMasterForecast() {
        const checked = document.querySelectorAll('.row-checkbox-masterforecast:checked');
        if (checked.length === 0) return;
        
        const container = document.getElementById('bulkDeleteMasterForecastIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('bulkDeleteMasterForecastCountText').innerText = checked.length;
        new bootstrap.Modal(document.getElementById('modalBulkDeleteMasterForecastConfirm')).show();
    }
</script>
@include('partials.registered-item-codes-datalist')
@include('partials.modal-select-item-code')
</body>
</html>
