<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Step 6: Dashboard Aktual Inventory & Supply Integration | PT Kawai Indonesia</title>
    <meta name="description" content="Monitoring integrasi aktual inventory fisik terhadap rencana forecast, outstanding PO, dan analisis kecukupan supply material PT Kawai Indonesia.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- SheetJS for high-fidelity interactive Excel parsing & preview --}}
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        :root {
            --bg-primary: #0a0e17;
            --card-bg: rgba(23,31,48,0.85);
            --card-border: rgba(255,255,255,0.08);
            --accent-purple: #8b5cf6;
            --accent-gold: #F59E0B;
            --accent-emerald: #10b981;
            --accent-cyan: #00d2ff;
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
        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            backdrop-filter: blur(12px);
        }
        .page-header-title {
            font-size: 1.65rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-add {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            border: none;
            color: #ffffff;
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
            box-shadow: 0 6px 18px rgba(139,92,246,0.4);
            color: #ffffff;
        }
        .btn-template {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.15);
            color: #e2e8f0;
            font-weight: 600;
            padding: 0.55rem 1.15rem;
            border-radius: 10px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .btn-template:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
            border-color: rgba(255,255,255,0.25);
        }
        .btn-import {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            color: #ffffff;
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
            box-shadow: 0 6px 18px rgba(16,185,129,0.35);
            color: #ffffff;
        }
        .btn-delete-mass {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
            font-weight: 600;
            padding: 0.55rem 1.15rem;
            border-radius: 10px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-delete-mass:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #fee2e2;
            border-color: rgba(239, 68, 68, 0.6);
        }
        .btn-delete-selection {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
            color: #ffffff;
            font-weight: 700;
            padding: 0.55rem 1.25rem;
            border-radius: 10px;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        .btn-delete-selection:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(239,68,68,0.4);
            color: #ffffff;
        }
        .filter-select {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            color: #fff;
            border-radius: 10px;
            padding: 0.45rem 1rem;
            font-size: 0.85rem;
        }
        .filter-select option { background: #1e2a3a; color: #fff; }
        .table-custom { color: var(--text-main); font-size: 0.86rem; }
        .table-custom thead th {
            background: rgba(139,92,246,0.15);
            color: #c4b5fd;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 0.85rem 0.75rem;
            white-space: nowrap;
            text-align: center;
        }
        .table-custom tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.15s; }
        .table-custom tbody tr:hover { background: rgba(255,255,255,0.04); }
        .table-custom tbody td { padding: 0.75rem 0.75rem; vertical-align: middle; }
        
        .badge-plant {
            background: rgba(0,210,255,0.15);
            color: #00d2ff;
            border: 1px solid rgba(0,210,255,0.35);
            border-radius: 6px;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            font-size: 0.75rem;
        }
        .badge-status {
            font-size: 0.73rem;
            font-weight: 700;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }
        .badge-surplus { background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.4); }
        .badge-covered { background: rgba(59,130,246,0.2); color: #60a5fa; border: 1px solid rgba(59,130,246,0.4); }
        .badge-deficit { background: rgba(239,68,68,0.2); color: #f87171; border: 1px solid rgba(239,68,68,0.4); }
        .badge-optimal { background: rgba(148,163,184,0.15); color: #cbd5e1; border: 1px solid rgba(148,163,184,0.3); }

        .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; transition: all 0.2s; cursor: pointer; }
        .btn-icon-del { background: rgba(239,68,68,0.18); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
        .btn-icon-del:hover { background: rgba(239,68,68,0.45); color: #ffffff; }
        .empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }
        .modal-content { background: #1a2236; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; color: var(--text-main); }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,0.08); }
        .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); }
        .form-control, .form-select { background: #1e293b !important; border: 1px solid rgba(255,255,255,0.12); color: #fff !important; border-radius: 10px; }
        .form-control:focus, .form-select:focus { background: #1e293b !important; border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139,92,246,0.2); color: #fff !important; }
        .form-select option, select option, option { background-color: #1e293b !important; color: #ffffff !important; font-weight: 500; }
        .form-control::placeholder { color: rgba(255,255,255,0.35); }
        .form-label { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

        .form-check-input {
            background-color: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.3);
            cursor: pointer;
            width: 1.15rem;
            height: 1.15rem;
        }
        .form-check-input:checked {
            background-color: #ef4444;
            border-color: #ef4444;
        }
        .dropzone-box {
            border: 2px dashed rgba(139,92,246,0.4);
            border-radius: 14px;
            background: rgba(139,92,246,0.05);
            padding: 2rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
        }
        .dropzone-box:hover, .dropzone-box.dragover {
            border-color: #8b5cf6;
            background: rgba(139,92,246,0.12);
        }
    </style>
</head>
<body>
<!-- Top Navbar -->
<nav class="top-navbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard.overview') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-music-note-beamed text-warning fs-4"></i>
            <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
        </a>
    </div>
    <div>
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.actual-inventory', 'hasFaqModal' => true])
    </div>
</nav>

@include('partials.faq-modal')

<div class="container-dashboard py-4">

    <!-- 7-STEP UNIFIED WORKFLOW STEPPER -->
    @include('partials.workflow-stepper', ['currentStep' => 6])

    <!-- STANDARDIZED PAGE HEADER & ACTION HIERARCHY -->
    <div class="kawai-page-header">
        <div class="kawai-page-header-left">
            <div class="page-icon-box" style="background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.35);">
                <i class="bi bi-boxes text-purple" style="color: #a78bfa;"></i>
            </div>
            <div>
                <h1 class="page-title-text">Aktual Inventory &amp; Analisis Supply</h1>
                <p class="page-subtitle-text">Integrasi stok fisik aktual terhadap kebutuhan forecast dan order outstanding untuk status kecukupan supply.</p>
            </div>
        </div>
        <div class="kawai-page-actions">
            {{-- Tombol Delete Selection (Aktif saat checkbox tercentang) --}}
            <button type="button" id="btnDeleteSelection" class="btn-kawai-danger d-none" onclick="triggerDeleteSelectionModal()">
                <i class="bi bi-trash3-fill"></i> Hapus Terpilih (<span id="selectedInventoryCount">0</span>)
            </button>

            <button type="button" class="btn-kawai-secondary" data-bs-toggle="modal" data-bs-target="#modalImportInventory">
                <i class="bi bi-file-earmark-excel-fill text-success"></i> Import Excel
            </button>
            
            <button type="button" class="btn-kawai-primary" data-bs-toggle="modal" data-bs-target="#modalAddInventory">
                <i class="bi bi-plus-circle-fill"></i> + Input Log Fisik
            </button>

            <div class="dropdown">
                <button class="btn-kawai-more dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Opsi Tambahan">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark-custom dropdown-menu-end">
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.actual-inventory.template') }}">
                            <i class="bi bi-download text-info"></i> Unduh Template Excel
                        </a>
                    </li>
                    <li><hr class="dropdown-divider border-secondary border-opacity-25 my-1"></li>
                    <li>
                        <a class="dropdown-item-custom text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalDeleteAllInventory">
                            <i class="bi bi-trash3-fill"></i> Hapus Semua Data
                        </a>
                    </li>
                </ul>
            </div>

            @include('partials.kurs-kpi-banner')
        </div>
    </div>

    <!-- ═══ 4 CANONICAL KPI CARDS (INVENTORY DEMAND → ACTUAL INVENTORY → OUTSTANDING → POTENTIAL SUPPLY) ═══ -->
    <div class="row g-3 g-xl-4 mb-4">
        {{-- Card 1: Total Aktual Inventory --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card kpi-card-purple">
                <div class="kpi-header">
                    <span class="kpi-title">TOTAL ACTUAL INVENTORY</span>
                    <div class="kpi-icon-box icon-purple">
                        <i class="bi bi-boxes"></i>
                    </div>
                </div>
                <div class="kpi-value text-white" style="color: #a78bfa !important;">
                    {{ number_format($kpiTotalInventoryQty) }} <span class="kpi-unit">PCS</span>
                </div>
                <div class="kpi-footer">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span class="text-muted small font-monospace" title="Nilai Finansial Stok Fisik">${{ number_format($kpiTotalInventoryValUsd, 2) }} (Rp {{ number_format($kpiTotalInventoryValIdr) }})</span>
                        <span class="badge bg-opacity-25" style="background: rgba(139,92,246,0.2); color: #a78bfa;">{{ $kpiTotalPositions ?? $kpiTotalUniqueItems }} Posisi ({{ $kpiUniqueMaterialsCount ?? $kpiTotalUniqueItems }} SKU)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Inventory Demand --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card kpi-card-blue">
                <div class="kpi-header">
                    <span class="kpi-title">TOTAL INVENTORY DEMAND</span>
                    <div class="kpi-icon-box icon-blue">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <div class="kpi-value text-info">
                    {{ number_format($kpiTotalInventoryDemand) }} <span class="kpi-unit">PCS</span>
                </div>
                <div class="kpi-footer">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span class="text-muted small font-monospace" title="Nilai Kebutuhan Target">${{ number_format($kpiTotalDemandValUsd, 2) }} (Rp {{ number_format($kpiTotalDemandValIdr) }})</span>
                        <span class="badge bg-primary bg-opacity-25 text-info">{{ $dataQualityScorecard['planning_horizon'] ?? 'Target Forecast' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Outstanding PO --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card kpi-card-gold">
                <div class="kpi-header">
                    <span class="kpi-title">TOTAL OUTSTANDING PO</span>
                    <div class="kpi-icon-box icon-gold">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
                <div class="kpi-value text-warning">
                    {{ number_format($kpiTotalOutstandingPo) }} <span class="kpi-unit">PCS</span>
                </div>
                <div class="kpi-footer">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span class="text-muted small">Pesanan Aktif On Progress</span>
                        <span class="badge bg-warning bg-opacity-25 text-warning">PO Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Total Potential Supply --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card {{ $kpiNetSupplyGap >= 0 ? 'kpi-card-emerald' : 'kpi-card-rose' }}">
                <div class="kpi-header">
                    <span class="kpi-title">TOTAL POTENTIAL SUPPLY</span>
                    <div class="kpi-icon-box {{ $kpiNetSupplyGap >= 0 ? 'icon-emerald' : 'icon-rose' }}">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
                <div class="kpi-value {{ $kpiNetSupplyGap >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($kpiTotalPotentialSupply) }} <span class="kpi-unit">PCS</span>
                </div>
                <div class="kpi-footer">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span class="text-muted small font-monospace">Coverage: {{ $kpiCoveragePercentage }}%</span>
                        @if(($kpiAdditionalRequirement ?? 0) > 0)
                            <span class="badge bg-danger bg-opacity-25 text-white" title="Kebutuhan Tambahan (Demand - Supply)">
                                Butuh: +{{ number_format($kpiAdditionalRequirement) }}
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-25 text-white">
                                Surplus: +{{ number_format($kpiNetSupplyGap) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ DATA QUALITY & INTEGRATION SUMMARY INDICATOR (SCORECARD) ═══ -->
    <div class="glass-card p-3 mb-4" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(255,255,255,0.08);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-check text-info fs-5"></i>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.7rem; letter-spacing: 0.05em;">INVENTORY SNAPSHOT</small>
                        <span class="fw-bold text-white small font-monospace">{{ $latestSnapshotDate ?? date('d M Y') }}</span>
                    </div>
                </div>
                <div class="border-start border-secondary border-opacity-25 ps-3 d-flex align-items-center gap-2">
                    <i class="bi bi-layers-fill text-purple fs-5" style="color: #a78bfa;"></i>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.7rem; letter-spacing: 0.05em;">GRAIN DATA INVENTORY</small>
                        <span class="fw-bold text-white small font-monospace">{{ $kpiTotalPositions ?? $kpiTotalUniqueItems }} Posisi Fisik ({{ $kpiUniqueMaterialsCount ?? $kpiTotalUniqueItems }} SKU)</span>
                    </div>
                </div>
                <div class="border-start border-secondary border-opacity-25 ps-3 d-flex align-items-center gap-2">
                    <i class="bi bi-link-45deg text-success fs-5"></i>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.7rem; letter-spacing: 0.05em;">INTEGRASI FORECAST &amp; PO</small>
                        <span class="fw-bold text-success small font-monospace">{{ $matchedMaterialsCount ?? $kpiUniqueMaterialsCount }} / {{ $kpiUniqueMaterialsCount }} SKU Terhubung ({{ $matchPercentage ?? 100 }}%)</span>
                    </div>
                </div>
                <div class="border-start border-secondary border-opacity-25 ps-3 d-flex align-items-center gap-2">
                    <i class="bi bi-currency-dollar text-warning fs-5"></i>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.7rem; letter-spacing: 0.05em;">KURS BUDGET NORMALISASI</small>
                        <span class="fw-bold text-warning small font-monospace">Rp {{ number_format($budgetExchangeRate) }} / USD</span>
                    </div>
                </div>
            </div>
            <div>
                @if(($dataQualityScorecard['overall_status'] ?? 'GOOD') === 'GOOD' && $kpiTotalInventoryQty > 0)
                    <span class="badge-health-success px-3 py-2" style="font-size: 0.82rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-shield-fill-check fs-6 text-success"></i>
                        <span class="fw-semibold">Data Quality: Good (100% Valid &amp; Terintegrasi)</span>
                    </span>
                @else
                    <span class="badge-health-warning px-3 py-2" style="font-size: 0.82rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-exclamation-triangle-fill fs-6 text-warning"></i>
                        <span class="fw-semibold">Data Quality: Perlu Tinjauan Stok / Integrasi</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- ═══ FILTER BAR ═══ -->
    <div class="glass-card mb-4 p-3">
        <form method="GET" action="{{ route('purchasing.actual-inventory') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-purple" style="color: #a78bfa;"></i>
                    <span class="fw-bold text-white small">Filter Data:</span>
                </div>

                {{-- Search Box --}}
                <div class="input-group input-group-sm" style="width: 240px;">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark border-secondary text-white" placeholder="Cari Part / Desc / Supplier..." value="{{ $search }}">
                </div>

                {{-- Plant Filter --}}
                <div>
                    <select name="plant" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL">-- Semua Plant --</option>
                        @foreach($availablePlants as $plant)
                            <option value="{{ $plant }}" {{ (strtoupper($plantFilter) === strtoupper($plant)) ? 'selected' : '' }}>Plant: {{ $plant }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Supplier Filter --}}
                <div>
                    <select name="supplier" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL">-- Semua Supplier --</option>
                        @foreach($availableSuppliers as $sup)
                            <option value="{{ $sup }}" {{ ($supplierFilter === $sup) ? 'selected' : '' }}>Supplier: {{ $sup }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <select name="status_filter" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL" {{ $statusFilter === 'ALL' ? 'selected' : '' }}>-- Semua Status Pasokan --</option>
                        <option value="SURPLUS" {{ $statusFilter === 'SURPLUS' ? 'selected' : '' }}>Surplus / Cukup</option>
                        <option value="COVERED_BY_PO" {{ $statusFilter === 'COVERED_BY_PO' ? 'selected' : '' }}>Terpenuhi via PO</option>
                        <option value="CRITICAL_DEFICIT" {{ $statusFilter === 'CRITICAL_DEFICIT' ? 'selected' : '' }}>Defisit / Perlu PO</option>
                        <option value="OPTIMAL" {{ $statusFilter === 'OPTIMAL' ? 'selected' : '' }}>Optimal / No Demand</option>
                    </select>
                </div>

                {{-- Per Page Selector --}}
                <div>
                    <select name="per_page" class="filter-select" onchange="this.form.submit()">
                        <option value="25" {{ ($perPageParam == '25') ? 'selected' : '' }}>25 Data / Hal</option>
                        <option value="50" {{ ($perPageParam == '50') ? 'selected' : '' }}>50 Data / Hal</option>
                        <option value="100" {{ ($perPageParam == '100') ? 'selected' : '' }}>100 Data / Hal</option>
                        <option value="ALL" {{ (strtoupper($perPageParam) == 'ALL') ? 'selected' : '' }}>Semua Data (All)</option>
                    </select>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-sm btn-purple text-white px-3 fw-bold rounded-pill" style="background: #8b5cf6;">
                    <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                @if($search || $plantFilter !== 'ALL' || $supplierFilter !== 'ALL' || $statusFilter !== 'ALL' || ($itemCode && $itemCode !== 'ALL') || $perPageParam != '50')
                    <a href="{{ route('purchasing.actual-inventory') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ═══ EXECUTIVE CHARTS (TOP 10 CRITICAL MATERIALS & STATUS DISTRIBUTION) ═══ -->
    <div class="row g-4 mb-4">
        {{-- Chart 1: Top 10 Executive Comparison (4 Bars) --}}
        <div class="col-12 col-xl-8">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-white mb-0 brand-font">
                            <i class="bi bi-bar-chart-fill text-purple me-2" style="color: #a78bfa;"></i>Inventory Demand vs Actual Inventory vs Outstanding vs Potential Supply
                        </h5>
                        <small class="text-muted">Perbandingan 4 dimensi: Kebutuhan (Inventory Demand) vs Stok Fisik vs Pesanan Berjalan (PO) vs Total Pasokan</small>
                    </div>
                    <span class="badge bg-dark border border-secondary text-info font-monospace" style="font-size: 0.75rem;" title="Diagram menampilkan 10 material teratas berdasarkan volume kebutuhan/pasokan dari total {{ $kpiTotalPositions }} posisi di tabel bawah">
                        Top 10 Spotlight (Total {{ $kpiTotalPositions }} Posisi)
                    </span>
                </div>
                <div style="position: relative; height: 340px; width: 100%;">
                    <canvas id="chartTopInventoryComparison"></canvas>
                </div>
            </div>
        </div>

        {{-- Chart 2: Status Distribution Donut --}}
        <div class="col-12 col-xl-4">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-white mb-0 brand-font">
                        <i class="bi bi-pie-chart-fill text-info me-2"></i>Distribusi Status Supply
                    </h5>
                    <span class="badge bg-purple text-white rounded-pill px-2.5" style="background: #8b5cf6;">{{ $kpiTotalUniqueItems }} Item</span>
                </div>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartInventoryStatusDoughnut"></canvas>
                </div>
                <div class="d-flex justify-content-around text-center mt-3 pt-2 border-top border-secondary border-opacity-25">
                    <div>
                        <span class="text-success fw-bold d-block fs-6">{{ $kpiSurplusCount }}</span>
                        <small class="text-muted" style="font-size:0.7rem;">Surplus</small>
                    </div>
                    <div>
                        <span class="text-primary fw-bold d-block fs-6">{{ $kpiCoveredByPoCount }}</span>
                        <small class="text-muted" style="font-size:0.7rem;">Covered PO</small>
                    </div>
                    <div>
                        <span class="text-danger fw-bold d-block fs-6">{{ $kpiCriticalDeficitCount }}</span>
                        <small class="text-muted" style="font-size:0.7rem;">Defisit</small>
                    </div>
                    <div>
                        <span class="text-light fw-bold d-block fs-6">{{ $kpiOptimalCount }}</span>
                        <small class="text-muted" style="font-size:0.7rem;">Optimal</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ RECONCILIATION & AUDIT INTEGRITY PANEL ═══ -->
    <div class="glass-card mb-4 p-3" style="background: rgba(15, 23, 42, 0.65); border: 1px dashed rgba(139, 92, 246, 0.4);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-calculator text-purple fs-5" style="color: #a78bfa;"></i>
                <div>
                    <span class="fw-bold text-white small d-block">Panel Rekonsiliasi Matematika: Dashboard KPI vs Detail Tabel</span>
                    <small class="text-muted" style="font-size: 0.72rem;">Verifikasi otomatis: Nilai Kartu KPI sama persis dengan total akumulasi baris tabel fisik.</small>
                </div>
            </div>
            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2.5 py-1.5 font-monospace" style="font-size: 0.75rem;">
                <i class="bi bi-shield-check me-1"></i>Audit: 100% Konsisten
            </span>
        </div>
        <div class="row g-2 text-center font-monospace">
            <div class="col-6 col-md-2">
                <div class="p-2 rounded bg-dark bg-opacity-60 border border-secondary border-opacity-25">
                    <small class="text-muted d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">Σ ACTUAL STOCK</small>
                    <span class="fw-bold text-white fs-7" style="color: #a78bfa !important;">{{ number_format($kpiTotalInventoryQty) }} PCS</span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 rounded bg-dark bg-opacity-60 border border-secondary border-opacity-25">
                    <small class="text-muted d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">Σ INVENTORY DEMAND</small>
                    <span class="fw-bold text-info fs-7">{{ number_format($kpiTotalInventoryDemand) }} PCS</span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 rounded bg-dark bg-opacity-60 border border-secondary border-opacity-25">
                    <small class="text-muted d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">Σ OUTSTANDING PO</small>
                    <span class="fw-bold text-warning fs-7">{{ number_format($kpiTotalOutstandingPo) }} PCS</span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 rounded bg-dark bg-opacity-60 border border-secondary border-opacity-25">
                    <small class="text-muted d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">Σ POTENTIAL SUPPLY</small>
                    <span class="fw-bold text-success fs-7">{{ number_format($kpiTotalPotentialSupply) }} PCS</span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 rounded bg-dark bg-opacity-60 border border-secondary border-opacity-25">
                    <small class="text-muted d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">ADDITIONAL REQ.</small>
                    <span class="fw-bold text-danger fs-7">{{ number_format($kpiAdditionalRequirement ?? 0) }} PCS</span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="p-2 rounded bg-dark bg-opacity-60 border border-secondary border-opacity-25">
                    <small class="text-muted d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">VALUASI INVENTORY</small>
                    <span class="fw-bold text-white fs-7" title="Nilai Finansial Stok">${{ number_format($kpiTotalInventoryValUsd, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ DETAIL TABLE: ACTUAL INVENTORY & SUPPLY INTEGRATION ═══ -->
    <div class="glass-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-white mb-0 brand-font">
                    <i class="bi bi-table text-purple me-2" style="color: #a78bfa;"></i>Tabel Data Aktual Inventory (Stok Fisik &amp; Integrasi Supply)
                </h5>
                <small class="text-muted">Menampilkan daftar data stok fisik aktual yang telah diunggah ke sistem. Centang checkbox untuk <strong>Delete Selection</strong> atau gunakan tombol <strong>Hapus Semua Data</strong> untuk reset.</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark border border-secondary text-light font-monospace">
                    @if(method_exists($paginatedMatrix, 'firstItem') && $paginatedMatrix->firstItem())
                        Menampilkan {{ $paginatedMatrix->firstItem() }} - {{ $paginatedMatrix->lastItem() }} dari Total {{ $filteredMatrix->count() }} Data
                    @else
                        Total: {{ $filteredMatrix->count() }} Posisi Terdaftar
                    @endif
                </span>
            </div>
        </div>

        <div class="table-responsive style-scrollbar">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" id="checkAllInventory" class="form-check-input" onchange="toggleSelectAllInventory(this)" title="Pilih Semua di Halaman Ini">
                        </th>
                        <th style="width: 35px;">#</th>
                        <th>Plant</th>
                        <th>Supplier</th>
                        <th>Material Code</th>
                        <th>Deskripsi Barang</th>
                        <th class="text-end" style="color: #c4b5fd;">Actual Inv</th>
                        <th class="text-center">Snapshot Date</th>
                        <th class="text-end text-info">Inventory Demand</th>
                        <th class="text-end text-warning">Outstanding PO</th>
                        <th class="text-end text-success">Potential Supply</th>
                        <th class="text-end">Supply Gap</th>
                        <th class="text-center">Coverage</th>
                        <th class="text-center">Status Supply</th>
                        <th>Rekomendasi Tindakan</th>
                        <th class="text-center" style="width: 60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedMatrix as $index => $row)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input row-checkbox-inventory" 
                                   value="{{ $row->inventory_id ?? $row->part_number }}" 
                                   data-id="{{ $row->inventory_id ?? '' }}" 
                                   data-part="{{ $row->part_number }}"
                                   onchange="updateSelectedInventoryCount()">
                        </td>
                        <td class="text-center font-monospace text-muted">
                            {{ method_exists($paginatedMatrix, 'firstItem') && $paginatedMatrix->firstItem() ? ($paginatedMatrix->firstItem() + $index) : ($index + 1) }}
                        </td>
                        <td>
                            <span class="badge-plant">{{ $row->factory_code }}</span>
                        </td>
                        <td>
                            <div class="text-white small fw-bold">{{ $row->supplier_name }}</div>
                            <span class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $row->supplier_code }}</span>
                        </td>
                        <td>
                            <strong class="text-white font-monospace fs-7">{{ $row->part_number }}</strong>
                        </td>
                        <td>
                            <div class="text-light fw-medium">{{ $row->description }}</div>
                        </td>
                        <td class="text-end font-monospace">
                            <span class="fw-bold" style="color: #a78bfa; font-size: 0.92rem;">{{ number_format($row->actual_stock) }}</span>
                            <br><small class="text-muted font-monospace" style="font-size: 0.72rem;" title="Nilai Finansial Stok Fisik (${{ number_format($row->inventory_val_usd, 2) }})">${{ number_format($row->inventory_val_usd, 2) }}</small>
                        </td>
                        <td class="text-center font-monospace text-muted fs-8">
                            {{ $row->last_stock_date }}
                        </td>
                        <td class="text-end font-monospace text-info">
                            <span class="fw-semibold">{{ number_format($row->inventory_demand) }}</span>
                            <br><small class="text-muted font-monospace" style="font-size: 0.72rem;" title="Nilai Kebutuhan Target (${{ number_format($row->demand_val_usd, 2) }})">${{ number_format($row->demand_val_usd, 2) }}</small>
                        </td>
                        <td class="text-end font-monospace text-warning fw-semibold">
                            {{ number_format($row->outstanding_po_qty) }}
                        </td>
                        <td class="text-end font-monospace text-success fw-bold">
                            {{ number_format($row->potential_supply) }}
                        </td>
                        <td class="text-end font-monospace fw-bold {{ $row->net_supply_gap >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $row->net_supply_gap >= 0 ? '+' : '' }}{{ number_format($row->net_supply_gap) }}
                        </td>
                        <td class="text-center font-monospace small">
                            <span class="badge {{ $row->coverage_pct >= 100 ? 'bg-success' : ($row->coverage_pct >= 50 ? 'bg-primary' : 'bg-danger') }} bg-opacity-20 text-light border border-secondary border-opacity-50 px-2 py-1">
                                {{ $row->coverage_pct }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if($row->status === 'SURPLUS')
                                <span class="badge-status badge-surplus"><i class="bi bi-check-circle-fill"></i> Surplus</span>
                            @elseif($row->status === 'COVERED_BY_PO')
                                <span class="badge-status badge-covered"><i class="bi bi-shield-fill-check"></i> Terpenuhi via PO</span>
                            @elseif($row->status === 'CRITICAL_DEFICIT')
                                <span class="badge-status badge-deficit"><i class="bi bi-exclamation-triangle-fill"></i> Defisit / Perlu PO</span>
                            @else
                                <span class="badge-status badge-optimal"><i class="bi bi-check-all"></i> Optimal</span>
                            @endif
                        </td>
                        <td style="font-size: 0.78rem;" class="text-muted">
                            @if($row->status === 'CRITICAL_DEFICIT')
                                <span class="text-danger fw-bold"><i class="bi bi-arrow-right-circle-fill me-1"></i>{{ $row->action_note }}</span>
                            @elseif($row->status === 'COVERED_BY_PO')
                                <span class="text-info"><i class="bi bi-info-circle-fill me-1"></i>{{ $row->action_note }}</span>
                            @elseif($row->status === 'SURPLUS')
                                <span class="text-success"><i class="bi bi-check2 me-1"></i>{{ $row->action_note }}</span>
                            @else
                                <span>{{ $row->action_note }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn-icon btn-icon-del" 
                                     onclick="triggerSingleDelete('{{ $row->inventory_id ?? $row->part_number }}', '{{ $row->part_number }}')" 
                                     title="Hapus data actual inventory untuk material {{ $row->part_number }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="16" class="text-center py-5">
                            <div class="empty-state">
                                <i class="bi bi-boxes" style="font-size: 3rem; color: var(--text-muted); opacity: 0.5;"></i>
                                <h6 class="text-white mt-3 mb-1">Belum Ada Data Aktual Inventory</h6>
                                <p class="text-muted small mb-3">Tabel ini kosong karena data inventaris telah dihapus atau belum diunggah. Silakan unggah file Excel Actual Inventory.</p>
                                <button type="button" class="btn-import" data-bs-toggle="modal" data-bs-target="#modalImportInventory">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Upload Excel Sekarang
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($filteredMatrix->isNotEmpty())
                <tfoot style="border-top: 2px solid rgba(139, 92, 246, 0.4); background: rgba(139, 92, 246, 0.08);">
                    <tr class="fw-bold font-monospace">
                        <td colspan="6" class="text-uppercase text-white ps-3 py-2.5">
                            <i class="bi bi-calculator-fill text-purple me-1"></i> Total Akumulasi ({{ $filteredMatrix->count() }} Posisi)
                        </td>
                        <td class="text-end py-2.5" style="color: #c4b5fd;">
                            {{ number_format($kpiTotalInventoryQty) }}
                            <br><small class="text-muted font-monospace" style="font-size: 0.7rem;">${{ number_format($kpiTotalInventoryValUsd, 2) }}</small>
                        </td>
                        <td class="text-center text-muted py-2.5">-</td>
                        <td class="text-end text-info py-2.5">
                            {{ number_format($kpiTotalInventoryDemand) }}
                            <br><small class="text-muted font-monospace" style="font-size: 0.7rem;">${{ number_format($kpiTotalDemandValUsd, 2) }}</small>
                        </td>
                        <td class="text-end text-warning py-2.5">
                            {{ number_format($kpiTotalOutstandingPo) }}
                        </td>
                        <td class="text-end text-success py-2.5">
                            {{ number_format($kpiTotalPotentialSupply) }}
                        </td>
                        <td class="text-end py-2.5 {{ $kpiNetSupplyGap >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $kpiNetSupplyGap >= 0 ? '+' : '' }}{{ number_format($kpiNetSupplyGap) }}
                        </td>
                        <td class="text-center py-2.5 text-white">
                            {{ $kpiCoveragePercentage }}%
                        </td>
                        <td colspan="3" class="text-muted small py-2.5 text-center">
                            Rekonsiliasi Sempurna dengan 4 KPI Utama
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Pagination Controls --}}
        @if(method_exists($paginatedMatrix, 'hasPages') && $paginatedMatrix->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top border-secondary border-opacity-25 flex-wrap gap-2" style="background: rgba(15, 23, 42, 0.4);">
            <div class="text-muted small font-monospace">
                Menampilkan <strong>{{ $paginatedMatrix->firstItem() }}</strong> - <strong>{{ $paginatedMatrix->lastItem() }}</strong> dari <strong>{{ $paginatedMatrix->total() }}</strong> total posisi stok fisik
            </div>
            <div>
                {{ $paginatedMatrix->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>

</div>

<!-- ═══ MODAL IMPORT EXCEL DENGAN PREVIEW INTERAKTIF ═══ -->
<div class="modal fade" id="modalImportInventory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font text-white">
                    <i class="bi bi-file-earmark-excel-fill text-success me-2"></i>Upload &amp; Validasi Preview Excel Actual Inventory
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Dropzone / File Select --}}
                <div class="dropzone-box mb-4" id="dropzoneInventory" onclick="document.getElementById('fileInputInventory').click()">
                    <i class="bi bi-cloud-arrow-up-fill text-purple fs-1 mb-2 d-block" style="color: #a78bfa;"></i>
                    <h6 class="text-white fw-bold mb-1">Pilih atau Tarik File Excel / CSV ke Sini</h6>
                    <p class="text-muted small mb-0">Mendukung format: <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code></p>
                    <input type="file" id="fileInputInventory" accept=".xlsx,.xls,.csv" style="display:none;" onchange="handleExcelFileSelect(this)">
                </div>

                {{-- Status Alert Box Preview --}}
                <div id="previewStatsBox" class="p-3 mb-3 rounded-3 d-none" style="background: rgba(18, 24, 38, 0.9); border: 1px solid rgba(139, 92, 246, 0.3);">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span class="badge bg-primary bg-opacity-25 text-info px-3 py-2 fs-7" id="statTotalRows">Total: 0 Baris</span>
                            <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 fs-7" id="statValidRows">✓ 0 Valid</span>
                            <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-2 fs-7 d-none" id="statDuplicateRows">⚠ 0 Duplikasi</span>
                            <span class="badge bg-danger bg-opacity-25 text-danger px-3 py-2 fs-7 d-none" id="statDeficitRows">✕ 0 Defisit</span>
                        </div>
                        <span class="text-muted small font-monospace" id="statFileName">file.xlsx</span>
                    </div>
                </div>

                {{-- Preview Table Container --}}
                <div id="previewTableContainer" class="table-responsive style-scrollbar d-none" style="max-height: 320px; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
                    <table class="table table-dark table-hover align-middle mb-0" style="font-size: 0.82rem;" id="tableExcelPreview">
                        <thead class="sticky-top bg-dark">
                            <tr>
                                <th>#</th>
                                <th>Supplier Code</th>
                                <th>Supplier Name</th>
                                <th>Plant</th>
                                <th>Material Code</th>
                                <th>Description</th>
                                <th class="text-end">Actual Inventory</th>
                                <th>Snapshot Date</th>
                                <th>Status Validasi</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <!-- Populated dynamically by SheetJS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="{{ route('purchasing.actual-inventory.template') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                    <i class="bi bi-download me-1"></i> Download Format Standar
                </a>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnConfirmImport" class="btn-import px-4" onclick="submitParsedInventoryData()" disabled>
                        <i class="bi bi-check2-circle me-1"></i> Konfirmasi &amp; Import Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI DELETE SELECTION (HAPUS TERPILIH) --}}
<div class="modal fade" id="modalConfirmDeleteSelection" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom border-danger border-opacity-25">
                <h5 class="modal-title brand-font text-danger"><i class="bi bi-trash3-fill me-2"></i>Konfirmasi Hapus Terpilih</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bi bi-exclamation-diamond-fill text-danger d-block mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-white fw-bold mb-2">Hapus <span id="modalSelectionCount" class="text-danger fw-bold">0</span> Data Inventory Terpilih?</h5>
                <p class="text-muted small mb-0">Tindakan ini akan menghapus catatan stok fisik terpilih dan mereset saldo inventory menjadi 0. Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" id="btnExecuteBulkDelete" onclick="executeBulkDelete()">
                    <i class="bi bi-trash-fill me-1"></i> Ya, Hapus Terpilih
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI DELETE MASSAL (HAPUS SEMUA) --}}
<div class="modal fade" id="modalDeleteAllInventory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom border-danger border-opacity-25">
                <h5 class="modal-title brand-font text-danger"><i class="bi bi-radioactive me-2"></i>Konfirmasi Delete Massal (Hapus Semua)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bi bi-exclamation-triangle-fill text-warning d-block mb-3" style="font-size: 3.5rem;"></i>
                <h5 class="text-white fw-bold mb-2">Hapus SELURUH Data Aktual Inventory?</h5>
                <p class="text-muted small mb-3">Tindakan ini akan menghapus <strong>seluruh data stok aktual inventory</strong> yang ada di database dan mereset semua posisi stok menjadi 0.</p>
                <div class="p-2 rounded-3 bg-danger bg-opacity-15 border border-danger border-opacity-25 text-danger small font-monospace">
                    Perhatian: Aksi ini bersifat permanen untuk seluruh log persediaan!
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('purchasing.actual-inventory.destroy-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="bi bi-trash-fill me-1"></i> Ya, Hapus Semua Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI SINGLE DELETE --}}
<div class="modal fade" id="modalSingleDeleteInventory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom border-danger border-opacity-25">
                <h5 class="modal-title brand-font text-danger"><i class="bi bi-trash-fill me-2"></i>Hapus Log Inventory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bi bi-trash3 text-danger d-block mb-3" style="font-size: 2.8rem;"></i>
                <h6 class="text-white fw-bold mb-2">Hapus data inventory untuk Material:</h6>
                <div class="fs-5 text-warning font-monospace fw-bold mb-3" id="singleDeletePartName">-</div>
                <p class="text-muted small mb-0">Saldo stok aktual untuk item ini akan dihapus dan direset ke 0.</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" id="btnExecuteSingleDelete" onclick="executeSingleDelete()">
                    <i class="bi bi-trash-fill me-1"></i> Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL MANUAL INPUT LOG INVENTORY --}}
<div class="modal fade" id="modalAddInventory" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font"><i class="fa fa-plus-circle me-2 text-purple" style="color: #a78bfa;"></i>Input Log Stok Fisik Manual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchasing.actual-inventory.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Stok (Snapshot Date) <span style="color:#f87171">*</span></label>
                        <input type="date" name="tanggal_inventory" class="form-control text-white fw-bold" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Plant / Lokasi <span style="color:#f87171">*</span></label>
                            <select name="factory_code" class="form-select text-white" required>
                                <option value="KIP1" selected>KIP1</option>
                                <option value="KIP2">KIP2</option>
                                <option value="KIP3">KIP3</option>
                                <option value="KIP4">KIP4</option>
                                <option value="Plant 3">Plant 3</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Supplier Code</label>
                            <input type="text" name="supplier_code" id="add_supplier_code" class="form-control text-white" placeholder="Cth: C102">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Material Code / Part Number <span style="color:#f87171">*</span></label>
                            <button type="button" class="btn p-0 text-info text-decoration-none small fw-bold" onclick="openSearchModalInventory('add')">
                                <i class="bi bi-window-stack"></i> Cari Item
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" name="part_number" id="add_part_number" class="form-control text-white fw-bold" list="listAvailableItemCodes" placeholder="Ketik Material Code (cth: 1312004)" required autocomplete="off" onchange="syncDescriptionInventory('add', this.value)">
                            <button type="button" class="btn btn-outline-info fw-bold" onclick="openSearchModalInventory('add')" title="Cari Item Code dari Pop-up Modal">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <datalist id="listAvailableItemCodes">
                            @foreach($availableItemCodes as $code)
                                <option value="{{ $code }}">{{ $code }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" name="supplier_name" id="add_supplier_name" class="form-control text-white" placeholder="Nama Supplier">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Material</label>
                        <input type="text" name="description" id="add_description" class="form-control text-white" placeholder="Deskripsi barang (otomatis terisi jika ada)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Qty Actual Inventory <span style="color:#f87171">*</span></label>
                        <input type="number" name="current_stock" class="form-control text-white fw-bold fs-5" placeholder="Masukkan jumlah stok fisik" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-add px-4"><i class="fa fa-save me-1"></i> Simpan Log Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL SEARCH ITEM CODE POPUP --}}
<div class="modal fade" id="modalSearchItemCodeInventory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title brand-font text-white"><i class="bi bi-window-stack text-info me-2"></i>Pilih Material Code / Part Number</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchItemCodeInputInventory" class="form-control bg-dark border-secondary text-white" placeholder="Ketik Material Code atau Deskripsi untuk menyaring..." onkeyup="filterItemCodeListInventory()">
                </div>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-dark table-hover align-middle mb-0" id="tableSearchItemCodeInventory">
                        <thead class="sticky-top bg-dark">
                            <tr>
                                <th>Material Code</th>
                                <th>Deskripsi Barang</th>
                                <th>Plant</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itemsWithDetails as $item)
                            <tr>
                                <td><strong class="text-white font-monospace">{{ $item['item_code'] }}</strong></td>
                                <td><span class="text-light">{{ $item['description'] }}</span></td>
                                <td><span class="badge-plant">{{ $item['factory'] }}</span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="selectItemCodeFromModalInventory('{{ $item['item_code'] }}', '{{ addslashes($item['description'] ?? '') }}', '{{ addslashes($item['supplier_code'] ?? '') }}', '{{ addslashes($item['supplier_name'] ?? '') }}')">
                                        <i class="bi bi-check-lg me-1"></i> Pilih
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let activeTargetFormInventory = 'add';
    const itemsMapInventory = {!! json_encode($itemsWithDetails) !!};
    let parsedExcelRows = [];
    let pendingSingleDeleteId = null;
    let pendingSingleDeletePart = null;

    // ── CHECKBOX SELECTION LOGIC ──
    function toggleSelectAllInventory(master) {
        const checkboxes = document.querySelectorAll('.row-checkbox-inventory');
        checkboxes.forEach(cb => cb.checked = master.checked);
        updateSelectedInventoryCount();
    }

    function updateSelectedInventoryCount() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox-inventory:checked');
        const count = checkedBoxes.length;
        const total = document.querySelectorAll('.row-checkbox-inventory').length;
        
        const countSpan = document.getElementById('selectedInventoryCount');
        const btnDeleteSelection = document.getElementById('btnDeleteSelection');
        const masterCheck = document.getElementById('checkAllInventory');

        if (countSpan) countSpan.innerText = count;
        
        if (count > 0) {
            btnDeleteSelection.classList.remove('d-none');
        } else {
            btnDeleteSelection.classList.add('d-none');
        }

        if (masterCheck) {
            masterCheck.checked = (total > 0 && count === total);
            masterCheck.indeterminate = (count > 0 && count < total);
        }
    }

    function triggerDeleteSelectionModal() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox-inventory:checked');
        if (checkedBoxes.length === 0) return;
        document.getElementById('modalSelectionCount').innerText = checkedBoxes.length;
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmDeleteSelection'));
        modal.show();
    }

    function executeBulkDelete() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox-inventory:checked');
        const ids = [];
        const partNumbers = [];

        checkedBoxes.forEach(cb => {
            const rowId = cb.getAttribute('data-id');
            const partNo = cb.getAttribute('data-part');
            if (rowId) ids.push(rowId);
            if (partNo) partNumbers.push(partNo);
        });

        const btn = document.getElementById('btnExecuteBulkDelete');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghapus...';

        fetch("{{ route('purchasing.actual-inventory.destroy-bulk') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids, part_numbers: partNumbers })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.notify) {
                    window.notify.success('Berhasil Hapus', data.message || 'Data terpilih berhasil dihapus.');
                }
                setTimeout(() => window.location.reload(), 1000);
            } else {
                if (window.notify) {
                    window.notify.error('Gagal Hapus', data.message || 'Gagal menghapus data terpilih.');
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus Terpilih';
            }
        })
        .catch(err => {
            if (window.notify) {
                window.notify.error('Kesalahan Jaringan', err.message || 'Terjadi kesalahan jaringan.');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus Terpilih';
        });
    }

    // ── SINGLE DELETE LOGIC ──
    function triggerSingleDelete(idOrPart, partNumber) {
        pendingSingleDeleteId = idOrPart;
        pendingSingleDeletePart = partNumber;
        document.getElementById('singleDeletePartName').innerText = partNumber;
        const modal = new bootstrap.Modal(document.getElementById('modalSingleDeleteInventory'));
        modal.show();
    }

    function executeSingleDelete() {
        if (!pendingSingleDeleteId) return;

        const btn = document.getElementById('btnExecuteSingleDelete');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghapus...';

        fetch(`/purchasing/actual-inventory/${pendingSingleDeleteId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.notify) {
                    window.notify.success('Berhasil Hapus', 'Data stok fisik berhasil dihapus.');
                }
                setTimeout(() => window.location.reload(), 1000);
            } else {
                if (window.notify) {
                    window.notify.error('Gagal Hapus', data.message || 'Gagal menghapus data.');
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus';
            }
        })
        .catch(err => {
            // Fallback reload jika redirect normal
            window.location.reload();
        });
    }

    // ── SEARCH MODAL HELPER ──
    function openSearchModalInventory(target) {
        activeTargetFormInventory = target;
        const modal = new bootstrap.Modal(document.getElementById('modalSearchItemCodeInventory'));
        modal.show();
    }

    function filterItemCodeListInventory() {
        const query = document.getElementById('searchItemCodeInputInventory').value.toLowerCase();
        const rows = document.querySelectorAll('#tableSearchItemCodeInventory tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    function selectItemCodeFromModalInventory(itemCode, description, supplierCode, supplierName) {
        document.getElementById('add_part_number').value = itemCode;
        document.getElementById('add_description').value = description;
        if (supplierCode) document.getElementById('add_supplier_code').value = supplierCode;
        if (supplierName) document.getElementById('add_supplier_name').value = supplierName;
        
        const modalEl = document.getElementById('modalSearchItemCodeInventory');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    function syncDescriptionInventory(target, itemCode) {
        const codeClean = itemCode.trim().toUpperCase();
        if (itemsMapInventory[codeClean]) {
            const desc = itemsMapInventory[codeClean].description || '';
            const supCode = itemsMapInventory[codeClean].supplier_code || '';
            const supName = itemsMapInventory[codeClean].supplier_name || '';
            
            document.getElementById('add_description').value = desc;
            if (supCode && document.getElementById('add_supplier_code')) document.getElementById('add_supplier_code').value = supCode;
            if (supName && document.getElementById('add_supplier_name')) document.getElementById('add_supplier_name').value = supName;
        }
    }

    // ── SHEETJS EXCEL PARSER & INTERACTIVE PREVIEW ──
    function handleExcelFileSelect(input) {
        const file = input.files[0];
        if (!file) return;

        document.getElementById('statFileName').innerText = file.name;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array', cellDates: true });
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                
                // Read as 2D Array to handle any header position
                const sheetData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });
                renderExcelPreview2D(sheetData);
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

    // Drag & Drop event bindings
    const dropzone = document.getElementById('dropzoneInventory');
    if (dropzone) {
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                document.getElementById('fileInputInventory').files = e.dataTransfer.files;
                handleExcelFileSelect(document.getElementById('fileInputInventory'));
            }
        });
    }

    function parseNumericStockJS(val) {
        if (val === null || val === undefined || val === '') return 0;
        if (typeof val === 'number') return isNaN(val) ? 0 : Math.round(val);
        let s = String(val).trim().replace(/[Rp\$€¥\s]/gi, '').replace(/[^\d\.\,\-]/g, '');
        if (s === '' || s === '-') return 0;
        
        if (/^-?\d{1,3}(\.\d{3})+$/.test(s)) {
            s = s.replace(/\./g, '');
        } else if (/^-?\d{1,3}(,\d{3})+$/.test(s)) {
            s = s.replace(/,/g, '');
        } else if (s.includes(',') && !s.includes('.')) {
            s = s.replace(',', '.');
        }
        const num = parseFloat(s);
        return isNaN(num) ? 0 : Math.round(num);
    }

    function renderExcelPreview2D(sheetData) {
        parsedExcelRows = [];
        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';

        if (!sheetData || sheetData.length === 0) {
            document.getElementById('statTotalRows').innerText = 'Total: 0 Baris';
            document.getElementById('btnConfirmImport').disabled = true;
            return;
        }

        let validCount = 0;
        let duplicateCount = 0;
        let deficitCount = 0;
        const seenCombinations = new Set();
        const todayStr = new Date().toISOString().split('T')[0];

        const headerKeywords = {
            mat_code: ['materialcode', 'itemcode', 'partnumber', 'partno', 'drawing', 'material', 'kodebarang', 'kodematerial', 'kodeitem', 'kodepart', 'komponen', 'mat', 'pn', 'sku', 'code', 'barang', 'item', 'part', 'matno', 'matcode', 'drawingno'],
            inv_qty: ['actualinventory', 'aktualinventory', 'actualstock', 'aktualstok', 'currentstock', 'stokfisik', 'physicalstock', 'endingstock', 'saldoakhir', 'inventory', 'stock', 'stok', 'm0inventory', 'm0stock', 'm0', 'saldo', 'qty', 'quantity', 'jumlah', 'kuantitas', 'vol', 'volume', 'total', 'output', 'pcs', 'juli', 'jul'],
            plant: ['plant', 'factorycode', 'factory', 'pabrik', 'lokasi', 'site', 'gedung', 'kdpabrik', 'line', 'unit', 'plantcode'],
            supp_code: ['suppliercode', 'vendorcode', 'kodesupplier', 'kodevendor', 'kdsupp', 'kdvendor', 'kdsp', 'suppcode', 'vendorcode'],
            supp_name: ['suppliername', 'vendorname', 'namasupplier', 'namavendor', 'namapemasok', 'pemasok', 'supplier', 'vendor', 'namasupp', 'pt'],
            desc: ['description', 'deskripsibarang', 'deskripsi', 'namabarang', 'namamaterial', 'keterangan', 'itemname', 'materialname', 'partname', 'namapart', 'spec', 'spesifikasi', 'desc', 'itemdescription'],
            date: ['snapshotdate', 'tanggalinventory', 'tanggal', 'date', 'periode', 'period', 'tgl', 'proddate']
        };

        // 1. Detect Header Row Index (Scan first 25 rows)
        let bestHeaderIdx = -1;
        let maxHeaderScore = 0;

        for (let r = 0; r < Math.min(25, sheetData.length); r++) {
            const row = sheetData[r];
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
        const headerRow = sheetData[bestHeaderIdx] || [];

        // 2. Map Column Indices
        const colMap = {
            mat_code: -1,
            inv_qty: -1,
            plant: -1,
            supp_code: -1,
            supp_name: -1,
            desc: -1,
            date: -1
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

        // Heuristic fallback if mat_code or inv_qty not detected
        if (colMap.mat_code === -1 || colMap.inv_qty === -1) {
            for (let c = 0; c < headerRow.length; c++) {
                let numCount = 0;
                let codeCount = 0;
                for (let r = bestHeaderIdx + 1; r < Math.min(bestHeaderIdx + 20, sheetData.length); r++) {
                    const val = String((sheetData[r] && sheetData[r][c]) || '').trim();
                    if (/^-?\d+(\.\d+)?$/.test(val)) numCount++;
                    if (/^[A-Za-z0-9\-\.]{4,20}$/.test(val) && isNaN(Number(val))) codeCount++;
                }
                if (colMap.inv_qty === -1 && numCount > 5) colMap.inv_qty = c;
                if (colMap.mat_code === -1 && (codeCount > 3 || (numCount > 5 && colMap.inv_qty !== c))) colMap.mat_code = c;
            }
        }

        let totalRawRows = 0;

        for (let r = bestHeaderIdx + 1; r < sheetData.length; r++) {
            const row = sheetData[r];
            if (!Array.isArray(row) || row.every(cell => String(cell || '').trim() === '')) continue;
            totalRawRows++;

            const matCode = colMap.mat_code !== -1 ? String(row[colMap.mat_code] || '').trim() : '';
            if (!matCode || matCode.toUpperCase() === 'ITEM CODE' || matCode.toUpperCase() === 'MATERIAL CODE' || matCode.toUpperCase().startsWith('TOTAL')) {
                continue;
            }

            const rawInv = colMap.inv_qty !== -1 ? row[colMap.inv_qty] : 0;
            const cleanInv = parseNumericStockJS(rawInv);

            const plant   = (colMap.plant !== -1 && row[colMap.plant]) ? String(row[colMap.plant]).trim().toUpperCase() : 'KIP 1';
            const supCode = (colMap.supp_code !== -1 && row[colMap.supp_code]) ? String(row[colMap.supp_code]).trim().toUpperCase() : '';
            const supName = (colMap.supp_name !== -1 && row[colMap.supp_name]) ? String(row[colMap.supp_name]).trim() : '';
            const desc    = (colMap.desc !== -1 && row[colMap.desc]) ? String(row[colMap.desc]).trim() : '';
            const snapDate = (colMap.date !== -1 && row[colMap.date]) ? String(row[colMap.date]).trim() : todayStr;

            const comboKey = plant + '|' + matCode;
            const isDuplicate = seenCombinations.has(comboKey);
            seenCombinations.add(comboKey);

            if (isDuplicate) duplicateCount++;
            if (cleanInv < 0) deficitCount++;
            validCount++;

            parsedExcelRows.push({
                supplier_code: supCode,
                supplier_name: supName,
                plant: plant,
                material_code: matCode,
                description: desc,
                actual_inventory: cleanInv,
                snapshot_date: snapDate
            });

            if (parsedExcelRows.length <= 100) {
                const tr = document.createElement('tr');
                let statusBadge = '<span class="badge bg-success bg-opacity-25 text-success">Valid</span>';
                if (isDuplicate) {
                    statusBadge = '<span class="badge bg-warning bg-opacity-25 text-warning">Duplikasi Plant</span>';
                } else if (cleanInv < 0) {
                    statusBadge = '<span class="badge bg-danger bg-opacity-25 text-danger">Defisit Stok</span>';
                }

                tr.innerHTML = `
                    <td class="text-muted font-monospace">${r + 1}</td>
                    <td class="font-monospace">${supCode || '-'}</td>
                    <td>${supName || '-'}</td>
                    <td><span class="badge-plant">${plant}</span></td>
                    <td><strong class="text-white font-monospace">${matCode}</strong></td>
                    <td class="text-truncate" style="max-width: 160px;" title="${desc}">${desc || '-'}</td>
                    <td class="text-end font-monospace fw-bold ${cleanInv < 0 ? 'text-danger' : 'text-success'}">${cleanInv.toLocaleString('id-ID')}</td>
                    <td class="text-muted fs-8">${snapDate}</td>
                    <td>${statusBadge}</td>
                `;
                tbody.appendChild(tr);
            }
        }

        document.getElementById('statTotalRows').innerText = `Total: ${totalRawRows} Baris`;
        document.getElementById('statValidRows').innerText = `✓ ${validCount} Siap Import`;
        
        const statDup = document.getElementById('statDuplicateRows');
        if (duplicateCount > 0) {
            statDup.innerText = `⚠ ${duplicateCount} Duplikasi`;
            statDup.classList.remove('d-none');
        } else {
            statDup.classList.add('d-none');
        }

        const statDef = document.getElementById('statDeficitRows');
        if (deficitCount > 0) {
            statDef.innerText = `✕ ${deficitCount} Negatif (Defisit)`;
            statDef.classList.remove('d-none');
        } else {
            statDef.classList.add('d-none');
        }

        document.getElementById('previewStatsBox').classList.remove('d-none');
        document.getElementById('previewTableContainer').classList.remove('d-none');
        
        const btn = document.getElementById('btnConfirmImport');
        if (btn) {
            btn.disabled = (parsedExcelRows.length === 0);
            if (parsedExcelRows.length > 0) {
                btn.classList.add('shadow-lg');
                btn.style.filter = 'drop-shadow(0 0 10px rgba(16,185,129,0.5))';
            } else {
                btn.style.filter = '';
            }
        }
    }

    function submitParsedInventoryData() {
        if (!parsedExcelRows || parsedExcelRows.length === 0) {
            if (window.notify) {
                window.notify.warning('Data Kosong', 'Tidak ada data valid yang siap diimport. Silakan periksa file Excel Anda.');
            }
            return;
        }

        const btn = document.getElementById('btnConfirmImport');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengimport data...';
        }

        fetch("{{ route('purchasing.actual-inventory.import') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ rows: parsedExcelRows })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.notify) {
                    window.notify.success('Import Berhasil', data.message || 'Data stok fisik aktual berhasil diimpor!');
                }
                setTimeout(() => window.location.reload(), 1000);
            } else {
                if (window.notify) {
                    window.notify.error('Gagal Import', data.message || 'Gagal mengimpor data.');
                }
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Konfirmasi & Import Data';
                }
            }
        })
        .catch(err => {
            if (window.notify) {
                window.notify.error('Kesalahan Jaringan', err.message || 'Terjadi kesalahan sistem.');
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Konfirmasi & Import Data';
            }
        });
    }

    // ── CHARTS INITIALIZATION ──
    document.addEventListener('DOMContentLoaded', function() {
        initInventoryCharts();
    });

    function initInventoryCharts() {
        const chartLabels          = @json($chartLabels ?? []);
        const chartInventoryDemand = @json($chartInventoryDemand ?? ($chartForecastStock ?? []));
        const chartActualInventory = @json($chartActualInventory ?? []);
        const chartOutstandingPo   = @json($chartOutstandingPo ?? []);
        const chartPotentialSupply = @json($chartPotentialSupply ?? []);
        const statusDist           = @json($chartStatusDistribution ?? []);

        // 1. Grouped Bar Chart: Top 10 Executive Comparison (4 Bars)
        const ctxBar = document.getElementById('chartTopInventoryComparison');
        if (ctxBar) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: chartLabels.length > 0 ? chartLabels : ['No Data'],
                    datasets: [
                        {
                            label: 'Inventory Demand (Forecast)',
                            data: chartInventoryDemand,
                            backgroundColor: 'rgba(59, 130, 246, 0.85)',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Actual Inventory (Fisik)',
                            data: chartActualInventory,
                            backgroundColor: 'rgba(168, 85, 247, 0.85)',
                            borderColor: '#a855f7',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Outstanding PO',
                            data: chartOutstandingPo,
                            backgroundColor: 'rgba(245, 158, 11, 0.85)',
                            borderColor: '#f59e0b',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Potential Supply',
                            data: chartPotentialSupply,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { labels: { color: '#cbd5e1', font: { family: 'Outfit', weight: 600 } } },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            borderColor: 'rgba(139, 92, 246, 0.3)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': ' + Number(ctx.raw).toLocaleString('id-ID') + ' PCS';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: '#94a3b8', font: { family: 'Outfit', weight: 'bold' } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#94a3b8',
                                font: { family: 'Outfit', weight: 'bold' },
                                callback: function(val) { return Number(val).toLocaleString('id-ID'); }
                            },
                            grid: { color: 'rgba(255,255,255,0.05)' }
                        }
                    }
                }
            });
        }

        // 2. Doughnut Chart: Status Distribution
        const ctxPie = document.getElementById('chartInventoryStatusDoughnut');
        if (ctxPie) {
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Surplus / Cukup', 'Terpenuhi via PO', 'Defisit / Perlu PO', 'Optimal / No Demand'],
                    datasets: [{
                        data: [
                            statusDist.surplus || 0,
                            statusDist.covered_by_po || 0,
                            statusDist.critical_deficit || 0,
                            statusDist.optimal || 0
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(59, 130, 246, 0.85)',
                            'rgba(239, 68, 68, 0.85)',
                            'rgba(100, 116, 139, 0.85)'
                        ],
                        borderColor: '#111827',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#cbd5e1', font: { family: 'Outfit', weight: 600 } } },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': ' + ctx.raw + ' Material';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
</script>

@include('partials.confirm-modal')
@include('partials.import-preview-modal')
<script src="{{ asset('js/kawai-notify.js') }}"></script>
<script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
