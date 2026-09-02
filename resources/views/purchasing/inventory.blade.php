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

        /* ═══ MODERN NON-MONOTONE BUTTON SUITE ═══ */
        .btn-kawai-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            color: #ffffff !important;
            border: 1px solid rgba(139, 92, 246, 0.4);
            box-shadow: 0 4px 14px rgba(139, 92, 246, 0.3);
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.22s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-kawai-primary:hover {
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
            transform: translateY(-1.5px);
            color: #ffffff !important;
        }

        .btn-kawai-filter {
            background: linear-gradient(135deg, #8b5cf6 0%, #4f46e5 100%);
            color: #ffffff !important;
            border: 1px solid rgba(139, 92, 246, 0.45);
            box-shadow: 0 3px 12px rgba(139, 92, 246, 0.28);
            border-radius: 20px;
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.22s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-kawai-filter:hover {
            background: linear-gradient(135deg, #9333ea 0%, #4338ca 100%);
            box-shadow: 0 6px 18px rgba(139, 92, 246, 0.45);
            transform: translateY(-1.5px);
            color: #ffffff !important;
        }

        .btn-kawai-sum {
            background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%);
            color: #ffffff !important;
            border: 1px solid rgba(6, 182, 212, 0.45);
            box-shadow: 0 3px 12px rgba(6, 182, 212, 0.3);
            border-radius: 20px;
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.22s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-kawai-sum:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0369a1 100%);
            box-shadow: 0 6px 18px rgba(6, 182, 212, 0.5);
            transform: translateY(-1.5px);
            color: #ffffff !important;
        }

        .btn-kawai-reset {
            background: rgba(239, 68, 68, 0.12);
            color: #fca5a5 !important;
            border: 1px solid rgba(239, 68, 68, 0.35);
            border-radius: 20px;
            font-weight: 600;
            padding: 0.45rem 1.15rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.22s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-kawai-reset:hover {
            background: rgba(239, 68, 68, 0.28);
            color: #ffffff !important;
            border-color: rgba(239, 68, 68, 0.65);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
            transform: translateY(-1.5px);
        }
        .btn-kawai-reset.has-active-filters {
            border-color: rgba(239, 68, 68, 0.6);
            background: rgba(239, 68, 68, 0.2);
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.25);
        }

        .btn-kawai-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #e2e8f0 !important;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.22s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-kawai-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.3);
            color: #ffffff !important;
            transform: translateY(-1.5px);
        }

        .btn-kawai-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff !important;
            border: 1px solid rgba(239, 68, 68, 0.4);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.22s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-kawai-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
            transform: translateY(-1.5px);
            color: #ffffff !important;
        }

        .btn-kawai-more {
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .btn-kawai-more:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
        }

        /* Floating Selection SUM & Action Bar */
        .floating-selection-bar {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(120%);
            z-index: 1050;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(139, 92, 246, 0.4);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.5), 0 0 20px rgba(139, 92, 246, 0.25);
            border-radius: 50px;
            padding: 0.6rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
            opacity: 0;
            pointer-events: none;
        }
        .floating-selection-bar.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
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

        {{-- Card 4: Net Supply Gap & Status Pasokan --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card {{ $kpiNetSupplyGap >= 0 ? 'kpi-card-emerald' : 'kpi-card-rose' }}">
                <div class="kpi-header">
                    <span class="kpi-title">NET SUPPLY GAP (STATUS)</span>
                    <div class="kpi-icon-box {{ $kpiNetSupplyGap >= 0 ? 'icon-emerald' : 'icon-rose' }}">
                        <i class="bi {{ $kpiNetSupplyGap >= 0 ? 'bi-shield-check' : 'bi-exclamation-triangle-fill' }}"></i>
                    </div>
                </div>
                <div class="kpi-value {{ $kpiNetSupplyGap >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $kpiNetSupplyGap >= 0 ? '+' : '' }}{{ number_format($kpiNetSupplyGap) }} <span class="kpi-unit">PCS</span>
                </div>
                <div class="kpi-footer">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <span class="text-muted small font-monospace">Coverage: {{ $kpiCoveragePercentage }}%</span>
                        @if(($kpiAdditionalRequirement ?? 0) > 0)
                            <span class="badge bg-danger bg-opacity-25 text-white" title="Kekurangan Pasokan Total">
                                Defisit: -{{ number_format($kpiAdditionalRequirement) }}
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-25 text-white">
                                Surplus Pasokan
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

    <!-- ═══ ENHANCED FILTER BAR (WITH SUM & RESET BUTTONS) ═══ -->
    <div class="glass-card mb-4 p-3">
        <form method="GET" action="{{ route('purchasing.actual-inventory') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3" id="actualInventoryFilterForm">
            <div class="d-flex align-items-center gap-2 flex-wrap flex-grow-1">
                <div class="d-flex align-items-center gap-2 me-1">
                    <i class="bi bi-funnel-fill text-purple fs-6" style="color: #a78bfa;"></i>
                    <span class="fw-bold text-white small">Filter Data:</span>
                </div>

                {{-- Search Box --}}
                <div class="input-group input-group-sm" style="width: 220px;">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark border-secondary text-white" placeholder="Cari Part / Desc / Supplier..." value="{{ $search }}">
                </div>

                {{-- Period / Month Filter --}}
                <div>
                    <select name="period" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL">-- Semua Periode --</option>
                        @foreach($availablePeriods ?? [] as $per)
                            <option value="{{ $per->key }}" {{ ($periodFilter === $per->key) ? 'selected' : '' }}>
                                Periode: {{ $per->label }}
                            </option>
                        @endforeach
                    </select>
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

                {{-- Item Code Filter (Default ALL per semua Item Code) --}}
                <div>
                    <select name="item_code" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL">-- Semua Item Code (All) --</option>
                        @foreach($availableItemCodes as $code)
                            <option value="{{ $code }}" {{ ($itemCode === $code) ? 'selected' : '' }}>Item: {{ $code }}</option>
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

            {{-- Action Buttons: Terapkan Filter, SUM Calculator, Reset Filter --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="submit" class="btn btn-sm btn-kawai-filter" title="Terapkan Parameter Pencarian &amp; Filter">
                    <i class="bi bi-funnel-fill"></i> Terapkan
                </button>

                <button type="button" class="btn btn-sm btn-kawai-sum" data-bs-toggle="modal" data-bs-target="#modalSummarySum" title="Kalkulator &amp; Ringkasan Akumulasi (SUM Total Data)">
                    <i class="bi bi-calculator-fill"></i> ∑ SUM Total
                </button>

                <a href="{{ route('purchasing.actual-inventory') }}" 
                   class="btn btn-sm btn-kawai-reset {{ ($search || $plantFilter !== 'ALL' || $supplierFilter !== 'ALL' || $periodFilter !== 'ALL' || $statusFilter !== 'ALL' || ($itemCode && $itemCode !== 'ALL') || $perPageParam != '50') ? 'has-active-filters' : '' }}" 
                   title="Reset Semua Parameter Filter ke Default">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                    @if($search || $plantFilter !== 'ALL' || $supplierFilter !== 'ALL' || $periodFilter !== 'ALL' || $statusFilter !== 'ALL' || ($itemCode && $itemCode !== 'ALL'))
                        <span class="badge bg-danger rounded-pill ms-1 px-1.5 py-0.2" style="font-size: 0.65rem;">Aktif</span>
                    @endif
                </a>
            </div>
        </form>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ HIERARCHICAL DRILL-DOWN DASHBOARD (VENDOR -> ITEM CODE -> 3D) ═══ -->
    <!-- ═════════════════════════════════════════════════════════════════════ -->
    <div class="glass-card mb-4 p-4" style="border: 1px solid rgba(139, 92, 246, 0.2); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);">
        
        {{-- BREADCRUMB & DRILLDOWN STATE NAVIGATOR --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-3 border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge text-white px-2.5 py-1.5 rounded-pill font-monospace" style="background: #8b5cf6;">
                    <i class="bi bi-diagram-3-fill me-1"></i>Analisis Data
                </span>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 font-monospace small">
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0)" onclick="resetToVendorOverview()" class="text-info text-decoration-none fw-bold">
                                <i class="bi bi-buildings me-1"></i>Vendor Overview ({{ count($vendorOverviewList) }} Vendor)
                            </a>
                        </li>
                        <li class="breadcrumb-item text-warning fw-bold d-none" id="bcVendorItem">
                            <a href="javascript:void(0)" onclick="backToVendorItems()" class="text-warning text-decoration-none" id="bcVendorName">
                                Vendor Name
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-white fw-bold d-none" id="bcItemCode">
                            <span id="bcItemCodeText">ITEM-001</span>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark border border-secondary text-success font-monospace" id="validationBadgeCompleteness">
                    <i class="bi bi-shield-check me-1"></i>{{ count($vendorOverviewList) }} Vendor
                </span>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="resetToVendorOverview()" title="Kembali ke Ringkasan Semua Vendor">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Level
                </button>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- ── LEVEL 1: VENDOR OVERVIEW TABLE (ALL DISTINCT VENDORS) ────── --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- ── LEVEL 1: VENDOR OVERVIEW & DIAGNOSTIC AREA CHART ──────────── --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div id="drilldownLevel1">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-white mb-0 brand-font">
                        <i class="bi bi-building me-2 text-warning"></i>Monitoring Pasokan Vendor
                    </h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-xs btn-outline-secondary active" id="btnViewModeAll" onclick="setVendorViewMode('all')">
                            <i class="bi bi-grid-fill me-1"></i>Grafik &amp; Tabel
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" id="btnViewModeChart" onclick="setVendorViewMode('chart')">
                            <i class="bi bi-graph-up me-1"></i>Grafik Saja
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" id="btnViewModeTable" onclick="setVendorViewMode('table')">
                            <i class="bi bi-table me-1"></i>Tabel Saja
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── 1. DIAGRAM AREA STATUS PASOKAN SELURUH VENDOR ── --}}
            <div class="p-3 rounded-3 bg-dark bg-opacity-75 border border-secondary border-opacity-30 mb-3" id="wrapperVendorAreaChart">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2 pb-2 border-bottom border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-1.5 rounded-3 bg-opacity-20 border border-opacity-40" style="background: rgba(139, 92, 246, 0.2); border-color: rgba(139, 92, 246, 0.4); color: #a78bfa;">
                            <i class="bi bi-graph-up-arrow fs-6"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-white mb-0 brand-font">
                                Kebutuhan vs Stok vs PO Per Vendor
                            </h6>
                        </div>
                    </div>
                    
                    {{-- Status Quick Filters for Chart --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-xs btn-outline-secondary active px-2.5 py-1" id="vChartBtnAll" onclick="filterVendorAreaChart('ALL')">
                                Semua Vendor ({{ count($vendorOverviewList) }})
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-danger px-2.5 py-1" id="vChartBtnCritical" onclick="filterVendorAreaChart('Critical')">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Critical ({{ $vendorOverviewList->where('status', 'Critical')->count() }})
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-warning px-2.5 py-1" id="vChartBtnAttention" onclick="filterVendorAreaChart('Attention')">
                                <i class="bi bi-shield-fill-check me-1"></i>Attention ({{ $vendorOverviewList->where('status', 'Attention')->count() }})
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-success px-2.5 py-1" id="vChartBtnHealthy" onclick="filterVendorAreaChart('Healthy')">
                                <i class="bi bi-check-circle-fill me-1"></i>Healthy ({{ $vendorOverviewList->where('status', 'Healthy')->count() }})
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Chart Canvas --}}
                <div style="position: relative; height: 270px; width: 100%; cursor: pointer;" title="Klik pada area atau titik grafik untuk diagnosa vendor">
                    <canvas id="chartVendorSupplyArea"></canvas>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary border-opacity-20 flex-wrap gap-2 text-muted small">
                    <span class="fs-8 text-info"><i class="bi bi-info-circle me-1"></i>Klik titik grafik atau baris vendor pada tabel di bawah untuk membuka popup diagnosa item.</span>
                    <div class="d-flex align-items-center gap-3 fs-8 font-monospace">
                        <span><span class="badge rounded-circle p-1 me-1" style="background: #38bdf8;"></span>Target Kebutuhan</span>
                        <span><span class="badge rounded-circle p-1 me-1" style="background: #a855f7;"></span>Stok Fisik</span>
                        <span><span class="badge rounded-circle p-1 me-1" style="background: #f59e0b;"></span>Outstanding PO</span>
                    </div>
                </div>
            </div>

            {{-- ── 2. TABEL DAFTAR VENDOR LENGKAP ── --}}
            <div id="wrapperVendorTable">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-list-columns-reverse text-info"></i>
                        <span class="text-white fw-bold small">Daftar Vendor</span>
                    </div>
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-dark border-secondary text-white" id="searchVendorInput" placeholder="Cari Nama Vendor / Kode..." oninput="filterVendorOverviewTable(this.value)">
                    </div>
                </div>

                <div class="table-responsive rounded-3 border border-secondary border-opacity-30 style-scrollbar mb-2" style="max-height: 360px;">
                    <table class="table table-dark table-hover table-sm align-middle mb-0" id="tableVendorOverview">
                        <thead class="table-dark text-muted font-monospace small sticky-top" style="background: #0f172a; border-bottom: 2px solid rgba(139, 92, 246, 0.4);">
                            <tr>
                                <th class="ps-3">Nama Vendor &amp; Kode</th>
                                <th class="text-center">Komposisi Item</th>
                                <th class="text-end text-info">Total In Demand</th>
                                <th class="text-end" style="color: #a78bfa;">Total Actual Stock</th>
                                <th class="text-end text-warning">Total Outstanding PO</th>
                                <th class="text-end">Inventory Gap</th>
                                <th class="text-center">Status Vendor</th>
                                <th class="text-center pe-3" style="width: 220px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="font-monospace" id="tbodyVendorOverview">
                            @forelse($vendorOverviewList as $idx => $v)
                            <tr class="vendor-overview-row" data-vendor-name="{{ $v['supplier_name'] }}" data-vendor-code="{{ $v['supplier_code'] }}" data-vendor-status="{{ $v['status'] }}" style="cursor: pointer;" onclick="openVendorDiagnosticModal('{{ addslashes($v['supplier_name']) }}')">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="p-1.5 rounded bg-dark border border-secondary border-opacity-30 text-warning">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div>
                                            <strong class="text-white font-sans d-block">{{ $v['supplier_name'] }}</strong>
                                            <small class="text-muted fs-8">Kode: {{ $v['supplier_code'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge text-white rounded-pill px-2.5 py-0.5" style="background: #8b5cf6; font-size: 0.75rem;">
                                            {{ $v['total_item_codes'] }} Item
                                        </span>
                                        <div class="d-flex gap-1" style="font-size: 0.65rem;">
                                            @if($v['critical_items_count'] > 0)
                                                <span class="badge bg-danger bg-opacity-25 text-danger px-1" title="{{ $v['critical_items_count'] }} Item Defisit Kritis">{{ $v['critical_items_count'] }} Defisit</span>
                                            @endif
                                            @if($v['healthy_items_count'] > 0)
                                                <span class="badge bg-success bg-opacity-25 text-success px-1" title="{{ $v['healthy_items_count'] }} Item Aman">{{ $v['healthy_items_count'] }} Aman</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end text-info fw-bold">{{ number_format($v['total_in_demand']) }} PCS</td>
                                <td class="text-end fw-bold" style="color: #a78bfa;">{{ number_format($v['total_actual_inventory']) }} PCS</td>
                                <td class="text-end text-warning fw-bold">{{ number_format($v['total_outstanding']) }} PCS</td>
                                <td class="text-end fw-bold {{ $v['total_inventory_gap'] <= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $v['total_inventory_gap'] > 0 ? '+' : '' }}{{ number_format($v['total_inventory_gap']) }} PCS
                                </td>
                                <td class="text-center">
                                    @if($v['status'] === 'Healthy')
                                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-40 px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Healthy</span>
                                    @elseif($v['status'] === 'Attention')
                                        <span class="badge bg-primary bg-opacity-20 text-info border border-info border-opacity-40 px-2 py-1"><i class="bi bi-shield-fill-check me-1"></i>Attention</span>
                                    @elseif($v['status'] === 'Critical')
                                        <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-40 px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Critical</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-20 text-light border border-secondary border-opacity-40 px-2 py-1"><i class="bi bi-info-circle me-1"></i>Check Data</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex align-items-center justify-content-center gap-1.5" onclick="event.stopPropagation();">
                                        <button type="button" class="btn btn-xs btn-outline-purple rounded-pill px-2.5 py-1 fw-semibold" onclick="openVendorDiagnosticModal('{{ addslashes($v['supplier_name']) }}')" title="Diagnosa">
                                            <i class="bi bi-search me-1"></i>Diagnosa
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2.5 py-1 fw-semibold" onclick="drillDownToVendor('{{ addslashes($v['supplier_name']) }}')" title="Lihat Item">
                                            <i class="bi bi-arrow-right-circle me-1"></i>Pilih Item
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block opacity-50 mb-2"></i>
                                    Tidak ada data vendor pada filter saat ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center text-muted small font-monospace px-1">
                    <span>Menampilkan <strong>{{ count($vendorOverviewList) }}</strong> vendor</span>
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- ── LEVEL 2: VENDOR ITEM CODE DRILL-DOWN TABLE ────────────────── --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div id="drilldownLevel2" class="d-none">
            <div class="p-3 rounded-3 bg-dark bg-opacity-60 border border-secondary border-opacity-25 mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle px-2" onclick="resetToVendorOverview()" title="Kembali ke Daftar Vendor">
                            <i class="bi bi-arrow-left"></i>
                        </button>
                        <div>
                            <h6 class="fw-bold text-white mb-0">
                                <i class="bi bi-box-seam text-info me-1.5"></i>Item Codes: <span class="text-warning" id="activeVendorTitle">-</span>
                            </h6>
                            <small class="text-muted" id="activeVendorSubtitle"></small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-dark border-secondary text-white" id="searchItemTableInput" placeholder="Cari Item / Deskripsi..." oninput="filterVendorItemsTable(this.value)">
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive rounded-3 border border-secondary border-opacity-30 style-scrollbar mb-2" style="max-height: 320px;">
                <table class="table table-dark table-hover table-sm align-middle mb-0" id="tableVendorItems">
                    <thead class="table-dark text-muted font-monospace small sticky-top" style="background: #0f172a; border-bottom: 2px solid rgba(6, 182, 212, 0.4);">
                        <tr>
                            <th class="ps-3">Item Code</th>
                            <th>Deskripsi Material</th>
                            <th class="text-center">Plant</th>
                            <th class="text-end text-info">In Demand</th>
                            <th class="text-end" style="color: #a78bfa;">Actual Inventory</th>
                            <th class="text-end text-warning">Outstanding PO</th>
                            <th class="text-end">Inventory Gap</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-3" style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="font-monospace" id="tbodyVendorItems">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- ── LEVEL 3: FOCUSED ITEM CODE COMPARISON (AREA + BULLET CHART) ─ --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div id="drilldownLevel3" class="d-none mt-3">
            
            {{-- SUMMARY METRICS STRIP --}}
            <div class="p-3 rounded-3 bg-dark bg-opacity-80 border border-secondary border-opacity-30 mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2 pb-2 border-bottom border-secondary border-opacity-20">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded-3 bg-opacity-20 border border-opacity-40" style="background: rgba(139, 92, 246, 0.2); border-color: rgba(139, 92, 246, 0.4); color: #a78bfa;">
                            <i class="bi bi-search fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold text-white mb-0 font-monospace" id="l3PartNumber">MAT-001</h5>
                                <span class="badge bg-secondary" id="l3PlantBadge">KIP1</span>
                                <span class="badge-status" id="l3StatusBadge">Healthy</span>
                            </div>
                            <small class="text-muted font-sans" id="l3Description">Material Description</small>
                        </div>
                    </div>
                    <div class="text-end font-monospace">
                        <small class="text-muted d-block fs-8" id="l3VendorText">VENDOR: -</small>
                        <span class="text-white fw-bold fs-7" id="l3ValuationText">Valuasi: $0.00</span>
                    </div>
                </div>

                {{-- 4 Metric Counter Cards --}}
                <div class="row g-2 text-center font-monospace">
                    <div class="col-6 col-md-3">
                        <div class="p-2.5 rounded bg-dark border border-info border-opacity-30">
                            <small class="text-info d-block fs-8">IN DEMAND (TARGET)</small>
                            <strong class="fs-5 text-info" id="l3InDemand">0</strong>
                            <small class="text-muted d-block fs-8">PCS</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2.5 rounded bg-dark border border-secondary border-opacity-30" style="border-color: rgba(139, 92, 246, 0.4) !important;">
                            <small class="d-block fs-8" style="color: #a78bfa;">ACTUAL INVENTORY</small>
                            <strong class="fs-5" style="color: #a78bfa;" id="l3ActualInventory">0</strong>
                            <small class="text-muted d-block fs-8">PCS</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2.5 rounded bg-dark border border-warning border-opacity-30">
                            <small class="text-warning d-block fs-8">OUTSTANDING PO</small>
                            <strong class="fs-5 text-warning" id="l3Outstanding">0</strong>
                            <small class="text-muted d-block fs-8">PCS</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2.5 rounded bg-dark border border-secondary border-opacity-30">
                            <small class="text-muted d-block fs-8">INVENTORY GAP (DEMAND - ACTUAL)</small>
                            <strong class="fs-5" id="l3InventoryGap">0</strong>
                            <small class="text-muted d-block fs-8">PCS</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- EXACTLY TWO PRIMARY CHARTS: AREA CHART & BULLET CHART --}}
            <div class="row g-3">
                {{-- Chart 1: Area Chart (Historical / Monthly Trend) --}}
                <div class="col-12 col-lg-7">
                    <div class="p-3 rounded-3 bg-dark bg-opacity-75 border border-secondary border-opacity-30 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="fw-bold text-white mb-0 brand-font">
                                    <i class="bi bi-graph-up text-purple me-1.5" style="color: #a78bfa;"></i>1. Area Chart: Tren Pasokan Per Periode Bulan
                                </h6>
                                <small class="text-muted">In Demand vs Actual Inventory vs Outstanding per snapshot bulan</small>
                            </div>
                            <span class="badge bg-dark border border-secondary text-info font-monospace fs-8" id="areaPeriodCountBadge">
                                Multi-Period Timeline
                            </span>
                        </div>
                        <div style="position: relative; height: 260px; width: 100%;">
                            <canvas id="chartL3Area"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Chart 2: Bullet Chart (Current Condition & Coverage Target Gauge) --}}
                <div class="col-12 col-lg-5">
                    <div class="p-3 rounded-3 bg-dark bg-opacity-75 border border-secondary border-opacity-30 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="fw-bold text-white mb-0 brand-font">
                                        <i class="bi bi-bullseye text-info me-1.5"></i>2. Bullet Chart: Kecukupan Pasokan
                                    </h6>
                                    <small class="text-muted">Target: In Demand | Aktual: Stok Fisik | Indikator: PO</small>
                                </div>
                            </div>
                            <div style="position: relative; height: 180px; width: 100%;">
                                <canvas id="chartL3Bullet"></canvas>
                            </div>
                        </div>

                        {{-- Bullet Chart Quick Legend & Insight --}}
                        <div class="p-2 rounded bg-dark border border-secondary border-opacity-20 font-monospace small mt-2">
                            <div class="d-flex justify-content-between align-items-center fs-8 mb-1">
                                <span><span class="badge bg-danger rounded-circle p-1 me-1"></span>Target Kebutuhan (In Demand):</span>
                                <strong class="text-info" id="bulletTargetText">0 PCS</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center fs-8 mb-1">
                                <span><span class="badge rounded-circle p-1 me-1" style="background: #8b5cf6;"></span>Stok Fisik Aktual (Actual):</span>
                                <strong style="color: #a78bfa;" id="bulletActualText">0 PCS</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center fs-8">
                                <span><span class="badge bg-warning rounded-circle p-1 me-1"></span>Pesanan Berjalan (Outstanding PO):</span>
                                <strong class="text-warning" id="bulletPoText">0 PCS</strong>
                            </div>
                        </div>
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
                    <span class="fw-bold text-white small d-block">Rekonsiliasi KPI</span>
                </div>
            </div>
            <span class="badge bg-success bg-opacity-25 text-success px-2 py-1 font-monospace" style="font-size: 0.75rem;"><i class="bi bi-shield-check me-1"></i>Konsisten</span>
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
                    <small class="text-muted d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">NET SUPPLY GAP</small>
                    <span class="fw-bold fs-7 {{ $kpiNetSupplyGap >= 0 ? 'text-success' : 'text-danger' }}">{{ $kpiNetSupplyGap >= 0 ? '+' : '' }}{{ number_format($kpiNetSupplyGap) }} PCS</span>
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
                        <th class="text-center">Periode &amp; Tgl Snapshot</th>
                        <th class="text-end text-info">Inventory Demand</th>
                        <th class="text-end text-warning">Outstanding PO</th>
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
                                   data-stock="{{ $row->actual_stock }}"
                                   data-demand="{{ $row->inventory_demand }}"
                                   data-po="{{ $row->outstanding_po_qty }}"
                                   data-gap="{{ $row->net_supply_gap }}"
                                   data-valusd="{{ $row->inventory_val_usd }}"
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
                        <td class="text-center font-monospace">
                            @if(!empty($row->period_label) && $row->period_label !== '-')
                                <span class="badge bg-dark border border-secondary text-info fs-8 px-2 py-0.5" style="font-size: 0.73rem;">
                                    <i class="bi bi-calendar3 me-0.5"></i>{{ $row->period_label }}
                                </span>
                            @endif
                            <div class="text-muted fs-8 mt-0.5">{{ $row->last_stock_date }}</div>
                        </td>
                        <td class="text-end font-monospace text-info">
                            <span class="fw-semibold">{{ number_format($row->inventory_demand) }}</span>
                            <br><small class="text-muted font-monospace" style="font-size: 0.72rem;" title="Nilai Kebutuhan Target (${{ number_format($row->demand_val_usd, 2) }})">${{ number_format($row->demand_val_usd, 2) }}</small>
                        </td>
                        <td class="text-end font-monospace text-warning fw-semibold">
                            {{ number_format($row->outstanding_po_qty) }}
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
                        <td colspan="15" class="text-center py-5">
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
                Menampilkan <strong>{{ $paginatedMatrix->firstItem() }}</strong> - <strong>{{ $paginatedMatrix->lastItem() }}</strong> dari <strong>{{ $paginatedMatrix->total() }}</strong> total posisi part material
            </div>
            <div>
                {{ $paginatedMatrix->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>

    <!-- ═══ FLOATING LIVE SELECTION SUM BAR ═══ -->
    <div class="floating-selection-bar" id="floatingSelectionBar">
        <div class="d-flex align-items-center gap-2">
            <span class="badge text-white px-2.5 py-1 rounded-pill fw-bold" style="background: #8b5cf6;">
                <i class="bi bi-check2-square me-1"></i><span id="floatSelectedCount">0</span> Item Terpilih
            </span>
        </div>
        <div class="d-flex align-items-center gap-3 font-monospace small text-white border-start border-secondary border-opacity-50 ps-3">
            <div>
                <small class="text-muted d-block" style="font-size: 0.65rem;">∑ STOK FISIK</small>
                <span class="fw-bold" style="color: #a78bfa;" id="floatSumStock">0</span> PCS
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.65rem;">∑ DEMAND</small>
                <span class="fw-bold text-info" id="floatSumDemand">0</span> PCS
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.65rem;">∑ OUTSTANDING PO</small>
                <span class="fw-bold text-warning" id="floatSumPo">0</span> PCS
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.65rem;">∑ VALUASI ($)</small>
                <span class="fw-bold text-success" id="floatSumValUsd">$0.00</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 border-start border-secondary border-opacity-50 ps-3">
            <button type="button" class="btn btn-sm btn-kawai-sum px-3 py-1 rounded-pill" onclick="openSumModalWithSelection()" title="Buka Detail Kalkulasi Item Terpilih">
                <i class="bi bi-calculator me-1"></i>Detail SUM
            </button>
            <button type="button" class="btn btn-sm btn-kawai-danger px-3 py-1 rounded-pill" onclick="triggerDeleteSelectionModal()" title="Hapus Data Terpilih">
                <i class="bi bi-trash3-fill me-1"></i>Hapus (<span id="floatDeleteCount">0</span>)
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle px-2 py-1" onclick="clearAllSelection()" title="Batalkan Pilihan">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

</div>

<!-- ═══ MODAL KALKULATOR & RINGKASAN AKUMULASI (SUM TOTAL) ═══ -->
<div class="modal fade" id="modalSummarySum" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border: 1px solid rgba(6, 182, 212, 0.4); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);">
            <div class="modal-header pb-2" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-3" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.35);">
                        <i class="bi bi-calculator-fill text-info fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title brand-font text-white mb-0">Ringkasan &amp; Kalkulator Akumulasi (SUM Total)</h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Nav Tabs --}}
                <ul class="nav nav-pills mb-3 gap-2" id="sumModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-3 py-1.5 fw-semibold fs-7" id="tab-filtered-sum" data-bs-toggle="pill" data-bs-target="#pane-filtered-sum" type="button" role="tab">
                            <i class="bi bi-funnel-fill me-1"></i>∑ Total Data Terfilter ({{ $filteredMatrix->count() }} Data)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-1.5 fw-semibold fs-7" id="tab-selection-sum" data-bs-toggle="pill" data-bs-target="#pane-selection-sum" type="button" role="tab">
                            <i class="bi bi-check2-square me-1"></i>∑ Item Terpilih (<span id="modalTabSelectedCount">0</span>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="sumModalTabContent">
                    {{-- Tab 1: Total Filtered --}}
                    <div class="tab-pane fade show active" id="pane-filtered-sum" role="tabpanel">
                        <div class="row g-3 text-center font-monospace mb-3">
                            <div class="col-6 col-md-4">
                                <div class="p-3 rounded bg-dark bg-opacity-75 border border-secondary border-opacity-30">
                                    <small class="text-muted d-block fs-8">TOTAL ACTUAL STOCK (FISIK)</small>
                                    <span class="fw-bold fs-5" style="color: #a78bfa;">{{ number_format($kpiTotalInventoryQty) }}</span>
                                    <span class="text-muted small">PCS</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-3 rounded bg-dark bg-opacity-75 border border-secondary border-opacity-30">
                                    <small class="text-muted d-block fs-8">TOTAL INVENTORY DEMAND</small>
                                    <span class="fw-bold text-info fs-5">{{ number_format($kpiTotalInventoryDemand) }}</span>
                                    <span class="text-muted small">PCS</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-3 rounded bg-dark bg-opacity-75 border border-secondary border-opacity-30">
                                    <small class="text-muted d-block fs-8">TOTAL OUTSTANDING PO</small>
                                    <span class="fw-bold text-warning fs-5">{{ number_format($kpiTotalOutstandingPo) }}</span>
                                    <span class="text-muted small">PCS</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-3 rounded bg-dark bg-opacity-75 border border-secondary border-opacity-30">
                                    <small class="text-muted d-block fs-8">NET SUPPLY GAP</small>
                                    <span class="fw-bold fs-5 {{ $kpiNetSupplyGap >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $kpiNetSupplyGap >= 0 ? '+' : '' }}{{ number_format($kpiNetSupplyGap) }}
                                    </span>
                                    <span class="text-muted small">PCS</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-3 rounded bg-dark bg-opacity-75 border border-secondary border-opacity-30">
                                    <small class="text-muted d-block fs-8">VALUASI INVENTORY ($ USD)</small>
                                    <span class="fw-bold text-white fs-5">${{ number_format($kpiTotalInventoryValUsd, 2) }}</span>
                                    <div class="text-muted fs-8">≈ Rp {{ number_format($kpiTotalInventoryValIdr, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-3 rounded bg-dark bg-opacity-75 border border-secondary border-opacity-30">
                                    <small class="text-muted d-block fs-8">RATA-RATA COVERAGE</small>
                                    <span class="fw-bold text-primary fs-5">{{ $kpiCoveragePercentage }}%</span>
                                    <div class="text-muted fs-8">Tingkat Kecukupan</div>
                                </div>
                            </div>
                        </div>

                        {{-- Status Distribution pills --}}
                        <div class="p-3 rounded bg-dark bg-opacity-50 border border-secondary border-opacity-25 d-flex justify-content-around text-center flex-wrap gap-2">
                            <div>
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 px-2.5 py-1 mb-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>Surplus
                                </span>
                                <strong class="d-block text-white font-monospace">{{ $kpiSurplusCount }} Item</strong>
                            </div>
                            <div>
                                <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30 px-2.5 py-1 mb-1">
                                    <i class="bi bi-shield-fill-check me-1"></i>Covered by PO
                                </span>
                                <strong class="d-block text-white font-monospace">{{ $kpiCoveredByPoCount }} Item</strong>
                            </div>
                            <div>
                                <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30 px-2.5 py-1 mb-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Defisit / Perlu PO
                                </span>
                                <strong class="d-block text-white font-monospace">{{ $kpiCriticalDeficitCount }} Item</strong>
                            </div>
                            <div>
                                <span class="badge bg-secondary bg-opacity-20 text-light border border-secondary border-opacity-30 px-2.5 py-1 mb-1">
                                    <i class="bi bi-check-all me-1"></i>Optimal
                                </span>
                                <strong class="d-block text-white font-monospace">{{ $kpiOptimalCount }} Item</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Selected Checkboxes --}}
                    <div class="tab-pane fade" id="pane-selection-sum" role="tabpanel">
                        <div id="selectionSumEmptyState" class="text-center py-4 text-muted">
                            <i class="bi bi-check2-square fs-1 text-secondary mb-2 d-block opacity-50"></i>
                            <h6 class="text-white fw-bold">Belum Ada Baris Data yang Dipilih</h6>
                            <p class="small mb-3">Centang kotak checkbox pada baris tabel untuk melihat kalkulasi penjumlahan instan item-item spesifik.</p>
                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="document.getElementById('checkAllInventory').click(); updateSelectedInventoryCount();">
                                <i class="bi bi-check-all me-1"></i>Pilih Semua di Halaman Ini
                            </button>
                        </div>

                        <div id="selectionSumContent" class="d-none">
                            <div class="row g-3 text-center font-monospace mb-3">
                                <div class="col-6 col-md-4">
                                    <div class="p-3 rounded bg-dark bg-opacity-75 border border-purple border-opacity-30" style="border-color: rgba(139, 92, 246, 0.4) !important;">
                                        <small class="text-muted d-block fs-8">∑ STOK FISIK TERPILIH</small>
                                        <span class="fw-bold fs-5" style="color: #a78bfa;" id="modalSumSelStock">0</span>
                                        <span class="text-muted small">PCS</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="p-3 rounded bg-dark bg-opacity-75 border border-info border-opacity-30">
                                        <small class="text-muted d-block fs-8">∑ DEMAND TERPILIH</small>
                                        <span class="fw-bold text-info fs-5" id="modalSumSelDemand">0</span>
                                        <span class="text-muted small">PCS</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="p-3 rounded bg-dark bg-opacity-75 border border-warning border-opacity-30">
                                        <small class="text-muted d-block fs-8">∑ OUTSTANDING PO TERPILIH</small>
                                        <span class="fw-bold text-warning fs-5" id="modalSumSelPo">0</span>
                                        <span class="text-muted small">PCS</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="p-3 rounded bg-dark bg-opacity-75 border border-secondary border-opacity-30">
                                        <small class="text-muted d-block fs-8">∑ NET GAP TERPILIH</small>
                                        <span class="fw-bold fs-5" id="modalSumSelGap">0</span>
                                        <span class="text-muted small">PCS</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="p-3 rounded bg-dark bg-opacity-75 border border-success border-opacity-30">
                                        <small class="text-muted d-block fs-8">∑ VALUASI ($ USD)</small>
                                        <span class="fw-bold text-success fs-5" id="modalSumSelValUsd">$0.00</span>
                                        <div class="text-muted fs-8">Total Nilai Finansial</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="p-3 rounded bg-dark bg-opacity-75 border border-secondary border-opacity-30">
                                        <small class="text-muted d-block fs-8">TOTAL BARIS DIPILIH</small>
                                        <span class="fw-bold text-white fs-5" id="modalSumSelCount">0</span>
                                        <div class="text-muted fs-8">Posisi Material</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i>Kalkulasi Terintegrasi</span>
                <button type="button" class="btn btn-sm btn-kawai-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
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
                <div class="dropzone-box mb-3" id="dropzoneInventory" onclick="document.getElementById('fileInputInventory').click()">
                    <i class="bi bi-cloud-arrow-up-fill text-purple fs-1 mb-2 d-block" style="color: #a78bfa;"></i>
                    <h6 class="text-white fw-bold mb-1">Pilih atau Tarik File Excel / CSV ke Sini</h6>
                    <p class="text-muted small mb-0">Mendukung format: <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code> (Contoh: <em>inventory juni syahrul.xlsx</em>)</p>
                    <input type="file" id="fileInputInventory" accept=".xlsx,.xls,.csv" style="display:none;" onchange="handleExcelFileSelect(this)">
                </div>

                {{-- Tanggal / Periode Snapshot Selector --}}
                <div class="row g-3 mb-3 align-items-center p-3 rounded-3" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="col-12">
                        <label class="form-label text-white small fw-bold mb-1">
                            <i class="bi bi-calendar3 text-purple me-1"></i> Tanggal / Periode Snapshot Inventory:
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="date" id="importSnapshotDate" class="form-control bg-dark border-secondary text-white" value="{{ date('Y-m-d') }}" onchange="updatePreviewSnapshotDate(this.value)">
                            <span class="input-group-text bg-secondary bg-opacity-25 border-secondary text-info small" id="detectedPeriodBadge">
                                <i class="bi bi-magic me-1"></i> Auto-detect
                            </span>
                        </div>
                    </div>
                </div>
                {{-- Pemetaan Kolom Excel (Column Mapping) --}}
                <div id="columnMappingBox" class="p-3 mb-3 rounded-3 d-none" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(59, 130, 246, 0.3);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white small fw-bold">
                            <i class="bi bi-layout-three-columns text-info me-1"></i> Pemetaan Kolom Excel (Otomatis Terdeteksi):
                        </span>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-2 col-6">
                            <label class="form-label text-muted fs-8 mb-0.5">Supplier Code</label>
                            <select id="mapColSuppCode" class="form-select form-select-sm bg-dark text-white border-secondary fs-8" onchange="reapplyColumnMapping()">
                                <option value="-1">-- Tidak Ada --</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label text-muted fs-8 mb-0.5">Supplier Name</label>
                            <select id="mapColSuppName" class="form-select form-select-sm bg-dark text-white border-secondary fs-8" onchange="reapplyColumnMapping()">
                                <option value="-1">-- Tidak Ada --</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label text-muted fs-8 mb-0.5">Plant / Pabrik</label>
                            <select id="mapColPlant" class="form-select form-select-sm bg-dark text-white border-secondary fs-8" onchange="reapplyColumnMapping()">
                                <option value="-1">-- Default (KIP1) --</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label text-warning fs-8 mb-0.5 fw-bold"><i class="bi bi-star-fill text-warning me-0.5" style="font-size:0.65rem;"></i>Material Code *</label>
                            <select id="mapColMatCode" class="form-select form-select-sm bg-dark text-warning border-warning fs-8 fw-bold" onchange="reapplyColumnMapping()">
                                <option value="-1">-- Wajib Dipilih --</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label text-muted fs-8 mb-0.5">Description</label>
                            <select id="mapColDesc" class="form-select form-select-sm bg-dark text-white border-secondary fs-8" onchange="reapplyColumnMapping()">
                                <option value="-1">-- Otomatis Master --</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label class="form-label text-success fs-8 mb-0.5 fw-bold"><i class="bi bi-star-fill text-success me-0.5" style="font-size:0.65rem;"></i>Actual Stock *</label>
                            <select id="mapColInvQty" class="form-select form-select-sm bg-dark text-success border-success fs-8 fw-bold" onchange="reapplyColumnMapping()">
                                <option value="-1">-- Wajib Dipilih --</option>
                            </select>
                        </div>
                    </div>
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

{{-- ═════════════════════════════════════════════════════════════════════ --}}
{{-- ── MODAL DIAGNOSA KESEHATAN VENDOR & ITEM CODE BERMASALAH (POPUP) ── --}}
{{-- ═════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalVendorDiagnostic" tabindex="-1" aria-labelledby="diagVendorName" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border: 1px solid rgba(139, 92, 246, 0.4); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);">
            {{-- Modal Header --}}
            <div class="modal-header pb-3" style="background: rgba(15, 23, 42, 0.95); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="p-2.5 rounded-3 bg-dark border border-secondary border-opacity-30 text-warning fs-4">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title brand-font text-white mb-0" id="diagVendorName">NAMA VENDOR</h5>
                            <span class="badge bg-secondary font-monospace" id="diagVendorCode">KODE: -</span>
                            <span id="diagVendorStatusBadge" class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-40">CRITICAL</span>
                        </div>
                        <p class="text-muted small mb-0 mt-1" id="diagVendorSubtitle"></p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4 style-scrollbar" style="background: #111827;">
                
                {{-- Status Diagnostic Reason Banner --}}
                <div class="p-3 rounded-3 mb-3 d-flex align-items-center gap-3" id="diagReasonBanner" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35);">
                    <i class="bi bi-exclamation-triangle-fill fs-3 text-danger" id="diagReasonIcon"></i>
                    <div>
                        <strong class="d-block text-white" id="diagReasonTitle">Ringkasan Diagnosa Status Vendor</strong>
                        <span class="small text-light" id="diagReasonDesc">Deskripsi detail diagnosa...</span>
                    </div>
                </div>

                {{-- 5 Metric Cards --}}
                <div class="row g-2 text-center font-monospace mb-4">
                    <div class="col-6 col-md">
                        <div class="p-2.5 rounded bg-dark border border-secondary border-opacity-30">
                            <small class="text-muted d-block fs-8">TOTAL ITEM CODES</small>
                            <strong class="fs-5 text-white" id="diagTotalItems">0</strong>
                            <small class="text-muted d-block fs-8">SKU Terdaftar</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2.5 rounded bg-dark border border-danger border-opacity-40" style="background: rgba(239, 68, 68, 0.08) !important;">
                            <small class="text-danger d-block fs-8">ITEM DEFISIT (CRITICAL)</small>
                            <strong class="fs-5 text-danger" id="diagCriticalCount">0</strong>
                            <small class="text-muted d-block fs-8">Perlu PO Tambahan</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2.5 rounded bg-dark border border-warning border-opacity-40" style="background: rgba(245, 158, 11, 0.08) !important;">
                            <small class="text-warning d-block fs-8">ITEM TERCOVER PO</small>
                            <strong class="fs-5 text-warning" id="diagAttentionCount">0</strong>
                            <small class="text-muted d-block fs-8">PO On Schedule</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2.5 rounded bg-dark border border-success border-opacity-40" style="background: rgba(16, 185, 129, 0.08) !important;">
                            <small class="text-success d-block fs-8">ITEM AMAN (HEALTHY)</small>
                            <strong class="fs-5 text-success" id="diagHealthyCount">0</strong>
                            <small class="text-muted d-block fs-8">Stok Mencukupi</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-2.5 rounded bg-dark border border-info border-opacity-40" style="background: rgba(6, 182, 212, 0.08) !important;">
                            <small class="text-info d-block fs-8">TOTAL DEFISIT PASOKAN</small>
                            <strong class="fs-5 text-info" id="diagTotalDeficit">0 PCS</strong>
                            <small class="text-muted d-block fs-8">Kekurangan Fisik</small>
                        </div>
                    </div>
                </div>

                {{-- Nav Tabs & Search Filter --}}
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2 border-bottom border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold fs-7 active" id="tabDiagCritical" type="button" onclick="filterDiagModalCategory('Critical')">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Item Bermasalah / Kritis (<span id="tabCountCritical">0</span>)
                        </button>
                        <button class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 fw-semibold fs-7" id="tabDiagAttention" type="button" onclick="filterDiagModalCategory('Attention')">
                            <i class="bi bi-shield-fill-check me-1"></i>Tercover PO (<span id="tabCountAttention">0</span>)
                        </button>
                        <button class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold fs-7" id="tabDiagHealthy" type="button" onclick="filterDiagModalCategory('Healthy')">
                            <i class="bi bi-check-circle-fill me-1"></i>Item Aman (<span id="tabCountHealthy">0</span>)
                        </button>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold fs-7" id="tabDiagAll" type="button" onclick="filterDiagModalCategory('ALL')">
                            Semua Item (<span id="tabCountAll">0</span>)
                        </button>
                    </div>

                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-dark border-secondary text-white" id="searchDiagModalInput" placeholder="Cari Item / Deskripsi..." oninput="filterDiagModalSearch(this.value)">
                    </div>
                </div>

                {{-- Table of Diagnostic Items --}}
                <div class="table-responsive rounded-3 border border-secondary border-opacity-30 style-scrollbar" style="max-height: 340px;">
                    <table class="table table-dark table-hover table-sm align-middle mb-0" id="tableDiagItems">
                        <thead class="table-dark text-muted font-monospace small sticky-top" style="background: #0f172a; border-bottom: 2px solid rgba(139, 92, 246, 0.4);">
                            <tr>
                                <th class="ps-3">Item Code</th>
                                <th>Deskripsi Material</th>
                                <th class="text-center">Plant</th>
                                <th class="text-end text-info">In Demand</th>
                                <th class="text-end" style="color: #a78bfa;">Actual Stock</th>
                                <th class="text-end text-warning">Outstanding PO</th>
                                <th class="text-end">Supply Gap</th>
                                <th class="text-center">Coverage</th>
                                <th class="text-center">Status</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="font-monospace" id="tbodyDiagItems">
                            {{-- Populated dynamically via JS --}}
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.95); border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <div>
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" id="btnModalDrilldownL2" onclick="proceedFromModalToL2()">
                        <i class="bi bi-arrow-right-circle me-1"></i>Lihat Semua Item
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-kawai-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Tutup
                </button>
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
        const floatingBar = document.getElementById('floatingSelectionBar');

        let sumStock = 0;
        let sumDemand = 0;
        let sumPo = 0;
        let sumGap = 0;
        let sumValUsd = 0;

        checkedBoxes.forEach(cb => {
            sumStock += parseFloat(cb.getAttribute('data-stock') || 0);
            sumDemand += parseFloat(cb.getAttribute('data-demand') || 0);
            sumPo += parseFloat(cb.getAttribute('data-po') || 0);
            sumGap += parseFloat(cb.getAttribute('data-gap') || 0);
            sumValUsd += parseFloat(cb.getAttribute('data-valusd') || 0);
        });

        if (countSpan) countSpan.innerText = count;
        
        // Update Floating Live Selection Bar
        if (floatingBar) {
            if (count > 0) {
                floatingBar.classList.add('show');
                const floatSel = document.getElementById('floatSelectedCount');
                const floatDel = document.getElementById('floatDeleteCount');
                const floatStk = document.getElementById('floatSumStock');
                const floatDem = document.getElementById('floatSumDemand');
                const floatPo  = document.getElementById('floatSumPo');
                const floatVal = document.getElementById('floatSumValUsd');

                if (floatSel) floatSel.innerText = count;
                if (floatDel) floatDel.innerText = count;
                if (floatStk) floatStk.innerText = Math.round(sumStock).toLocaleString('id-ID');
                if (floatDem) floatDem.innerText = Math.round(sumDemand).toLocaleString('id-ID');
                if (floatPo) floatPo.innerText = Math.round(sumPo).toLocaleString('id-ID');
                if (floatVal) floatVal.innerText = '$' + sumValUsd.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                floatingBar.classList.remove('show');
            }
        }

        // Update Modal Selection Tab
        const modalTabSelectedCount = document.getElementById('modalTabSelectedCount');
        if (modalTabSelectedCount) modalTabSelectedCount.innerText = count;

        const selEmptyState = document.getElementById('selectionSumEmptyState');
        const selContent = document.getElementById('selectionSumContent');
        if (selEmptyState && selContent) {
            if (count > 0) {
                selEmptyState.classList.add('d-none');
                selContent.classList.remove('d-none');
                document.getElementById('modalSumSelStock').innerText = Math.round(sumStock).toLocaleString('id-ID');
                document.getElementById('modalSumSelDemand').innerText = Math.round(sumDemand).toLocaleString('id-ID');
                document.getElementById('modalSumSelPo').innerText = Math.round(sumPo).toLocaleString('id-ID');
                document.getElementById('modalSumSelGap').innerText = (sumGap >= 0 ? '+' : '') + Math.round(sumGap).toLocaleString('id-ID');
                document.getElementById('modalSumSelValUsd').innerText = '$' + sumValUsd.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('modalSumSelCount').innerText = count;
            } else {
                selEmptyState.classList.remove('d-none');
                selContent.classList.add('d-none');
            }
        }

        if (count > 0) {
            btnDeleteSelection?.classList.remove('d-none');
        } else {
            btnDeleteSelection?.classList.add('d-none');
        }

        if (masterCheck) {
            masterCheck.checked = (total > 0 && count === total);
            masterCheck.indeterminate = (count > 0 && count < total);
        }
    }

    function clearAllSelection() {
        document.querySelectorAll('.row-checkbox-inventory').forEach(cb => cb.checked = false);
        const masterCheck = document.getElementById('checkAllInventory');
        if (masterCheck) {
            masterCheck.checked = false;
            masterCheck.indeterminate = false;
        }
        updateSelectedInventoryCount();
    }

    function openSumModalWithSelection() {
        const modal = new bootstrap.Modal(document.getElementById('modalSummarySum'));
        modal.show();
        const tabEl = document.getElementById('tab-selection-sum');
        if (tabEl) {
            const tabTrigger = new bootstrap.Tab(tabEl);
            tabTrigger.show();
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
    let rawSheetDataCache = [];

    function detectPeriodFromText(text) {
        if (!text) return null;
        const lower = String(text).toLowerCase();
        
        // Daftar Bulan ID & EN
        const monthDefs = [
            { name: 'januari', short: 'jan', num: '01' },
            { name: 'februari', short: 'feb', num: '02' },
            { name: 'maret', short: 'mar', num: '03' },
            { name: 'april', short: 'apr', num: '04' },
            { name: 'mei', short: 'may', num: '05' },
            { name: 'juni', short: 'jun', num: '06' },
            { name: 'juli', short: 'jul', num: '07' },
            { name: 'agustus', short: 'aug', num: '08' },
            { name: 'september', short: 'sep', num: '09' },
            { name: 'oktober', short: 'oct', num: '10' },
            { name: 'november', short: 'nov', num: '11' },
            { name: 'desember', short: 'dec', num: '12' }
        ];

        let foundMonth = null;
        for (let m of monthDefs) {
            const regex = new RegExp(`\\b(${m.name}|${m.short})\\b|(${m.name}|${m.short})\\d+`, 'i');
            if (regex.test(lower) || lower.includes(m.name) || (m.short.length >= 3 && lower.includes(m.short))) {
                foundMonth = m.num;
                break;
            }
        }

        // Cari Tahun 4 digit (2020 - 2035)
        const yearMatch = lower.match(/\b(202[0-9]|203[0-5])\b/) || lower.match(/(202[0-9]|203[0-5])/);
        const foundYear = yearMatch ? yearMatch[1] : (new Date().getFullYear().toString());

        if (foundMonth) {
            const lastDay = new Date(parseInt(foundYear), parseInt(foundMonth), 0).getDate();
            return `${foundYear}-${foundMonth}-${String(lastDay).padStart(2, '0')}`;
        }
        return null;
    }

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
                rawSheetDataCache = sheetData;

                // Auto-detect period from filename, sheet name, and top rows
                let detectedDate = detectPeriodFromText(file.name) 
                    || detectPeriodFromText(firstSheetName);

                if (!detectedDate && sheetData.length > 0) {
                    for (let r = 0; r < Math.min(5, sheetData.length); r++) {
                        const rowStr = (sheetData[r] || []).join(' ');
                        const d = detectPeriodFromText(rowStr);
                        if (d) {
                            detectedDate = d;
                            break;
                        }
                    }
                }

                if (detectedDate) {
                    const dateInput = document.getElementById('importSnapshotDate');
                    if (dateInput) {
                        dateInput.value = detectedDate;
                        const badge = document.getElementById('detectedPeriodBadge');
                        if (badge) {
                            badge.innerHTML = `<i class="bi bi-check2-circle text-success me-1"></i> Terdeteksi: ${detectedDate}`;
                        }
                    }
                }

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

    function updatePreviewSnapshotDate(newDate) {
        if (!newDate) return;
        const badge = document.getElementById('detectedPeriodBadge');
        if (badge) {
            badge.innerHTML = `<i class="bi bi-pencil-fill text-warning me-1"></i> Manual: ${newDate}`;
        }
        if (rawSheetDataCache && rawSheetDataCache.length > 0) {
            renderExcelPreview2D(rawSheetDataCache);
        }
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

    function classifyExcelColumn(headerText) {
        const raw = String(headerText || '').trim().toLowerCase();
        const clean = raw.replace(/[^a-z0-9]/g, '');
        if (!clean) return null;

        // 1. Supplier Code (MUST check before mat_code)
        if (
            clean.includes('suppliercode') || clean.includes('vendorcode') ||
            clean.includes('kodesupplier') || clean.includes('kodevendor') ||
            clean.includes('kdsupp') || clean.includes('kdvendor') ||
            clean.includes('suppcode') || clean.includes('vendorcode') ||
            (clean.includes('supplier') && clean.includes('code')) ||
            (clean.includes('vendor') && clean.includes('code')) ||
            clean === 'kdpemasok' || clean === 'kdsp'
        ) {
            return 'supp_code';
        }

        // 2. Supplier Name
        if (
            clean.includes('suppliername') || clean.includes('vendorname') ||
            clean.includes('namasupplier') || clean.includes('namavendor') ||
            clean.includes('namapemasok') || clean === 'supplier' || clean === 'vendor' ||
            clean === 'pemasok' || clean === 'pt' || clean === 'namasupp'
        ) {
            return 'supp_name';
        }

        // 3. Plant / Factory
        if (
            clean === 'plant' || clean === 'factory' || clean === 'pabrik' ||
            clean === 'lokasi' || clean === 'site' || clean.includes('plant') ||
            clean.includes('factorycode') || clean.includes('kdpabrik')
        ) {
            return 'plant';
        }

        // 4. Actual Inventory / Stock Qty (MUST check before material/item)
        if (
            clean.includes('actualinventory') || clean.includes('aktualinventory') ||
            clean.includes('actualstock') || clean.includes('aktualstok') ||
            clean.includes('currentstock') || clean.includes('stokfisik') ||
            clean.includes('physicalstock') || clean.includes('endingstock') ||
            clean.includes('saldoakhir') || clean === 'inventory' || clean === 'stock' ||
            clean === 'stok' || clean === 'saldo' || clean === 'qty' || clean === 'quantity' ||
            clean === 'jumlah' || clean === 'kuantitas' || clean === 'pcs' ||
            clean.includes('m0inventory') || clean.includes('m0stock')
        ) {
            return 'inv_qty';
        }

        // 5. Material Code / Part Number (STRICT: Never match if supplier/vendor/desc/plant present)
        if (
            clean.includes('materialcode') || clean.includes('partnumber') ||
            clean.includes('itemcode') || clean.includes('kodematerial') ||
            clean.includes('kodebarang') || clean.includes('kodeitem') ||
            clean.includes('kodepart') || clean.includes('partno') ||
            clean.includes('drawingno') || clean === 'drawing' || clean === 'pn' || clean === 'sku' ||
            clean.includes('matcode') || clean.includes('matno') ||
            clean.includes('partcode') || clean === 'komponen' ||
            (clean.includes('material') && !clean.includes('name') && !clean.includes('desc') && !clean.includes('supplier')) ||
            (clean.includes('part') && !clean.includes('name') && !clean.includes('desc') && !clean.includes('supplier') && !clean.includes('vendor')) ||
            (clean.includes('item') && !clean.includes('name') && !clean.includes('desc') && !clean.includes('description') && !clean.includes('supplier'))
        ) {
            return 'mat_code';
        }

        // 6. Description
        if (
            clean.includes('description') || clean.includes('deskripsi') ||
            clean.includes('namabarang') || clean.includes('namamaterial') ||
            clean.includes('itemdescription') || clean.includes('keterangan') ||
            clean.includes('itemname') || clean.includes('partname') ||
            clean.includes('spec') || clean.includes('spesifikasi') || clean === 'desc'
        ) {
            return 'desc';
        }

        // 7. Snapshot Date / Periode
        if (
            clean.includes('snapshotdate') || clean.includes('tanggalinventory') ||
            clean.includes('tanggal') || clean === 'date' || clean.includes('periode') ||
            clean.includes('period') || clean === 'tgl'
        ) {
            return 'date';
        }

        return null;
    }

    let activeColMap = {
        mat_code: -1,
        inv_qty: -1,
        plant: -1,
        supp_code: -1,
        supp_name: -1,
        desc: -1,
        date: -1
    };
    let activeHeaderRow = [];
    let activeBestHeaderIdx = 0;

    function populateColumnDropdowns(headerRow) {
        const fields = [
            { id: 'mapColSuppCode', key: 'supp_code', defaultLabel: '-- Tidak Ada --' },
            { id: 'mapColSuppName', key: 'supp_name', defaultLabel: '-- Tidak Ada --' },
            { id: 'mapColPlant',    key: 'plant',     defaultLabel: '-- Default (KIP1) --' },
            { id: 'mapColMatCode',  key: 'mat_code',  defaultLabel: '-- Wajib Dipilih --' },
            { id: 'mapColDesc',     key: 'desc',      defaultLabel: '-- Otomatis Master --' },
            { id: 'mapColInvQty',   key: 'inv_qty',   defaultLabel: '-- Wajib Dipilih --' }
        ];

        fields.forEach(f => {
            const el = document.getElementById(f.id);
            if (!el) return;
            el.innerHTML = `<option value="-1">${f.defaultLabel}</option>`;
            for (let c = 0; c < headerRow.length; c++) {
                const colName = String(headerRow[c] || `Kolom ${c + 1}`).trim();
                const opt = document.createElement('option');
                opt.value = c;
                opt.text = `[Kolom ${String.fromCharCode(65 + c)}] ${colName}`;
                if (activeColMap[f.key] === c) {
                    opt.selected = true;
                }
                el.appendChild(opt);
            }
        });

        const box = document.getElementById('columnMappingBox');
        if (box) box.classList.remove('d-none');
    }

    function reapplyColumnMapping() {
        activeColMap.supp_code = parseInt(document.getElementById('mapColSuppCode')?.value ?? -1);
        activeColMap.supp_name = parseInt(document.getElementById('mapColSuppName')?.value ?? -1);
        activeColMap.plant     = parseInt(document.getElementById('mapColPlant')?.value ?? -1);
        activeColMap.mat_code  = parseInt(document.getElementById('mapColMatCode')?.value ?? -1);
        activeColMap.desc      = parseInt(document.getElementById('mapColDesc')?.value ?? -1);
        activeColMap.inv_qty   = parseInt(document.getElementById('mapColInvQty')?.value ?? -1);

        if (rawSheetDataCache && rawSheetDataCache.length > 0) {
            renderRowsWithActiveMap(rawSheetDataCache);
        }
    }

    function renderExcelPreview2D(sheetData) {
        if (!sheetData || sheetData.length === 0) {
            document.getElementById('statTotalRows').innerText = 'Total: 0 Baris';
            document.getElementById('btnConfirmImport').disabled = true;
            return;
        }

        // 1. Detect Header Row Index (Scan first 25 rows)
        let bestHeaderIdx = -1;
        let maxHeaderScore = 0;

        for (let r = 0; r < Math.min(25, sheetData.length); r++) {
            const row = sheetData[r];
            if (!Array.isArray(row)) continue;
            let score = 0;
            for (let cell of row) {
                const cType = classifyExcelColumn(cell);
                if (cType) {
                    score += (cType === 'mat_code' || cType === 'inv_qty' ? 3 : 1);
                }
            }
            if (score > maxHeaderScore) {
                maxHeaderScore = score;
                bestHeaderIdx = r;
            }
        }

        if (bestHeaderIdx === -1) bestHeaderIdx = 0;
        activeBestHeaderIdx = bestHeaderIdx;
        const headerRow = sheetData[bestHeaderIdx] || [];
        activeHeaderRow = headerRow;

        // 2. Map Column Indices Unambiguously
        activeColMap = {
            mat_code: -1,
            inv_qty: -1,
            plant: -1,
            supp_code: -1,
            supp_name: -1,
            desc: -1,
            date: -1
        };

        for (let c = 0; c < headerRow.length; c++) {
            const cell = headerRow[c];
            const cType = classifyExcelColumn(cell);
            if (cType && activeColMap[cType] === -1) {
                activeColMap[cType] = c;
            }
        }

        // 3. Fallback for Standard Kawai 6-column format (Col A=Supplier Code, B=Supplier Name, C=Plant, D=Material Code, E=Description, F=Actual Inventory)
        if (activeColMap.mat_code === -1 && headerRow.length >= 4) {
            if (activeColMap.supp_code === 0 && headerRow[3]) activeColMap.mat_code = 3;
            if (activeColMap.supp_code === 0 && headerRow[1] && activeColMap.supp_name === -1) activeColMap.supp_name = 1;
            if (activeColMap.supp_code === 0 && headerRow[2] && activeColMap.plant === -1) activeColMap.plant = 2;
            if (activeColMap.supp_code === 0 && headerRow[4] && activeColMap.desc === -1) activeColMap.desc = 4;
            if (activeColMap.supp_code === 0 && headerRow[5] && activeColMap.inv_qty === -1) activeColMap.inv_qty = 5;
        }

        // Heuristic fallback for mat_code & inv_qty
        if (activeColMap.mat_code === -1 || activeColMap.inv_qty === -1) {
            for (let c = 0; c < headerRow.length; c++) {
                if (c === activeColMap.supp_code || c === activeColMap.supp_name || c === activeColMap.plant) continue;
                let numCount = 0;
                let codeCount = 0;
                for (let r = bestHeaderIdx + 1; r < Math.min(bestHeaderIdx + 20, sheetData.length); r++) {
                    const val = String((sheetData[r] && sheetData[r][c]) || '').trim();
                    if (/^-?\d+(\.\d+)?$/.test(val)) numCount++;
                    if (/^[A-Za-z0-9\-\.]{3,20}$/.test(val) && isNaN(Number(val))) codeCount++;
                }
                if (activeColMap.inv_qty === -1 && numCount > 5) activeColMap.inv_qty = c;
                if (activeColMap.mat_code === -1 && (codeCount > 3 || (numCount > 5 && activeColMap.inv_qty !== c))) activeColMap.mat_code = c;
            }
        }

        populateColumnDropdowns(headerRow);
        renderRowsWithActiveMap(sheetData);
    }

    function renderRowsWithActiveMap(sheetData) {
        parsedExcelRows = [];
        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';

        let validCount = 0;
        let duplicateCount = 0;
        let deficitCount = 0;
        const seenCombinations = new Set();
        const activeSnapshotDate = document.getElementById('importSnapshotDate')?.value || (new Date().toISOString().split('T')[0]);
        let totalRawRows = 0;

        for (let r = activeBestHeaderIdx + 1; r < sheetData.length; r++) {
            const row = sheetData[r];
            if (!Array.isArray(row) || row.every(cell => String(cell || '').trim() === '')) continue;
            totalRawRows++;

            const matCode = activeColMap.mat_code !== -1 ? String(row[activeColMap.mat_code] || '').trim() : '';
            if (!matCode || matCode.toUpperCase() === 'ITEM CODE' || matCode.toUpperCase() === 'MATERIAL CODE' || matCode.toUpperCase() === 'PART NUMBER' || matCode.toUpperCase().startsWith('TOTAL')) {
                continue;
            }

            const rawInv = activeColMap.inv_qty !== -1 ? row[activeColMap.inv_qty] : 0;
            const cleanInv = parseNumericStockJS(rawInv);

            const plant   = (activeColMap.plant !== -1 && row[activeColMap.plant]) ? String(row[activeColMap.plant]).trim().toUpperCase() : 'KIP 1';
            const supCode = (activeColMap.supp_code !== -1 && row[activeColMap.supp_code]) ? String(row[activeColMap.supp_code]).trim().toUpperCase() : '';
            const supName = (activeColMap.supp_name !== -1 && row[activeColMap.supp_name]) ? String(row[activeColMap.supp_name]).trim() : '';
            const desc    = (activeColMap.desc !== -1 && row[activeColMap.desc]) ? String(row[activeColMap.desc]).trim() : '';
            const snapDate = (activeColMap.date !== -1 && row[activeColMap.date]) ? String(row[activeColMap.date]).trim() : activeSnapshotDate;

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
                    <td class="font-monospace text-info">${supCode || '-'}</td>
                    <td class="text-truncate" style="max-width: 140px;">${supName || '-'}</td>
                    <td><span class="badge-plant">${plant}</span></td>
                    <td><strong class="text-warning font-monospace fs-7">${matCode}</strong></td>
                    <td class="text-truncate" style="max-width: 170px;" title="${desc}">${desc || '-'}</td>
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
            btn.disabled = (parsedExcelRows.length === 0 || activeColMap.mat_code === -1 || activeColMap.inv_qty === -1);
            if (parsedExcelRows.length > 0 && activeColMap.mat_code !== -1) {
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

    // ── DATASETS & DRILL-DOWN INITIALIZATION ──
    const vendorOverviewList = @json($vendorOverviewList ?? []);
    const vendorChartData    = @json($vendorChartData ?? []);
    const statusDist         = @json($chartStatusDistribution ?? []);
    
    // Explicit Drill-Down & Diagnostic States
    let selectedVendor       = null;
    let selectedItemCode     = null;
    let selectedVendorDiag   = null;
    let currentDiagCategory  = 'ALL';
    let currentDiagSearch    = '';
    let chartVendorAreaInst  = null;
    let chartL3AreaInstance  = null;
    let chartL3BulletInstance= null;

    document.addEventListener('DOMContentLoaded', function() {
        // Automatically verify completeness badge
        const badge = document.getElementById('validationBadgeCompleteness');
        if (badge) {
            badge.innerHTML = `<i class="bi bi-shield-check me-1"></i>${vendorOverviewList.length} Vendor`;
        }

        // Initialize Vendor Supply & Health Area Chart
        renderVendorSupplyAreaChart('ALL');
    });

    // ═══════════════════════════════════════════════════════════
    // ── LEVEL 1: VENDOR AREA CHART & VIEW TOGGLE FUNCTIONS ────
    // ═══════════════════════════════════════════════════════════

    function setVendorViewMode(mode) {
        const chartBox = document.getElementById('wrapperVendorAreaChart');
        const tableBox = document.getElementById('wrapperVendorTable');
        const btnAll   = document.getElementById('btnViewModeAll');
        const btnChart = document.getElementById('btnViewModeChart');
        const btnTable = document.getElementById('btnViewModeTable');

        [btnAll, btnChart, btnTable].forEach(b => b?.classList.remove('active', 'btn-secondary', 'btn-outline-secondary'));
        [btnAll, btnChart, btnTable].forEach(b => b?.classList.add('btn-outline-secondary'));

        if (mode === 'chart') {
            chartBox?.classList.remove('d-none');
            tableBox?.classList.add('d-none');
            btnChart?.classList.add('active', 'btn-secondary');
            btnChart?.classList.remove('btn-outline-secondary');
        } else if (mode === 'table') {
            chartBox?.classList.add('d-none');
            tableBox?.classList.remove('d-none');
            btnTable?.classList.add('active', 'btn-secondary');
            btnTable?.classList.remove('btn-outline-secondary');
        } else {
            chartBox?.classList.remove('d-none');
            tableBox?.classList.remove('d-none');
            btnAll?.classList.add('active', 'btn-secondary');
            btnAll?.classList.remove('btn-outline-secondary');
        }

        if (chartVendorAreaInst && mode !== 'table') {
            setTimeout(() => chartVendorAreaInst.resize(), 100);
        }
    }

    function filterVendorAreaChart(statusFilter) {
        ['vChartBtnAll', 'vChartBtnCritical', 'vChartBtnAttention', 'vChartBtnHealthy'].forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.classList.remove('active');
        });

        if (statusFilter === 'Critical') document.getElementById('vChartBtnCritical')?.classList.add('active');
        else if (statusFilter === 'Attention') document.getElementById('vChartBtnAttention')?.classList.add('active');
        else if (statusFilter === 'Healthy') document.getElementById('vChartBtnHealthy')?.classList.add('active');
        else document.getElementById('vChartBtnAll')?.classList.add('active');

        renderVendorSupplyAreaChart(statusFilter);
    }

    function renderVendorSupplyAreaChart(statusFilter = 'ALL') {
        const canvas = document.getElementById('chartVendorSupplyArea');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let filteredVendors = vendorOverviewList;
        if (statusFilter !== 'ALL') {
            filteredVendors = vendorOverviewList.filter(v => v.status === statusFilter);
        }

        if (filteredVendors.length === 0) {
            filteredVendors = vendorOverviewList; // Fallback if filter empty
        }

        const labels = filteredVendors.map(v => {
            const name = v.supplier_name || 'Vendor';
            return name.length > 20 ? name.substring(0, 18) + '...' : name;
        });
        const fullNames = filteredVendors.map(v => v.supplier_name);
        const inDemandData = filteredVendors.map(v => v.total_in_demand || 0);
        const actualData   = filteredVendors.map(v => v.total_actual_inventory || 0);
        const poData       = filteredVendors.map(v => v.total_outstanding || 0);
        const statuses     = filteredVendors.map(v => v.status);
        const criticalCnts = filteredVendors.map(v => v.critical_items_count || 0);
        const healthyCnts  = filteredVendors.map(v => v.healthy_items_count || 0);

        // Linear Gradients with Soft Glow
        const gDemand = ctx.createLinearGradient(0, 0, 0, 240);
        gDemand.addColorStop(0, 'rgba(59, 130, 246, 0.42)');
        gDemand.addColorStop(1, 'rgba(59, 130, 246, 0.02)');

        const gActual = ctx.createLinearGradient(0, 0, 0, 240);
        gActual.addColorStop(0, 'rgba(168, 85, 247, 0.45)');
        gActual.addColorStop(1, 'rgba(168, 85, 247, 0.02)');

        const gPo = ctx.createLinearGradient(0, 0, 0, 240);
        gPo.addColorStop(0, 'rgba(245, 158, 11, 0.40)');
        gPo.addColorStop(1, 'rgba(245, 158, 11, 0.02)');

        if (chartVendorAreaInst) {
            chartVendorAreaInst.destroy();
        }

        chartVendorAreaInst = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'In Demand (Target Kebutuhan)',
                        data: inDemandData,
                        backgroundColor: gDemand,
                        borderColor: '#38bdf8',
                        borderWidth: 2.5,
                        fill: 'origin',
                        tension: 0.35,
                        pointRadius: 6,
                        pointHoverRadius: 10,
                        hitRadius: 30,
                        pointBackgroundColor: '#38bdf8',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5
                    },
                    {
                        label: 'Actual Inventory (Stok Fisik)',
                        data: actualData,
                        backgroundColor: gActual,
                        borderColor: '#a855f7',
                        borderWidth: 2.5,
                        fill: 'origin',
                        tension: 0.35,
                        pointRadius: 6,
                        pointHoverRadius: 10,
                        hitRadius: 30,
                        pointBackgroundColor: '#a855f7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5
                    },
                    {
                        label: 'Outstanding PO (Pesanan Berjalan)',
                        data: poData,
                        backgroundColor: gPo,
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        borderDash: [4, 4],
                        fill: 'origin',
                        tension: 0.35,
                        pointRadius: 6,
                        pointHoverRadius: 10,
                        hitRadius: 30,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                onHover: (event, chartElement) => {
                    const canvasEl = event.native ? event.native.target : (event.chart ? event.chart.canvas : event.target);
                    if (canvasEl) {
                        canvasEl.style.cursor = 'pointer';
                    }
                },
                onClick: (event, elements, chart) => {
                    const evt = event.native || event;
                    let targetIndex = -1;

                    if (elements && elements.length > 0) {
                        targetIndex = elements[0].index;
                    } else if (chart) {
                        const nearest = chart.getElementsAtEventForMode(evt, 'index', { intersect: false }, false);
                        if (nearest && nearest.length > 0) {
                            targetIndex = nearest[0].index;
                        } else {
                            const nearestX = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: false, axis: 'x' }, false);
                            if (nearestX && nearestX.length > 0) {
                                targetIndex = nearestX[0].index;
                            }
                        }
                    }

                    if (targetIndex >= 0 && fullNames[targetIndex]) {
                        openVendorDiagnosticModal(fullNames[targetIndex]);
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#e2e8f0',
                            font: { family: 'Outfit', weight: 600, size: 12 },
                            usePointStyle: true,
                            padding: 16
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.96)',
                        borderColor: 'rgba(139, 92, 246, 0.6)',
                        borderWidth: 1.5,
                        padding: 14,
                        cornerRadius: 8,
                        titleFont: { family: 'Outfit', weight: 'bold', size: 13 },
                        titleColor: '#ffffff',
                        bodyFont: { family: 'Outfit', size: 12 },
                        bodyColor: '#e2e8f0',
                        footerFont: { family: 'Outfit', size: 11 },
                        footerColor: '#94a3b8',
                        displayColors: true,
                        usePointStyle: true,
                        callbacks: {
                            title: (tooltipItems) => {
                                const idx = tooltipItems[0].dataIndex;
                                const v = filteredVendors[idx];
                                const tag = v.status === 'Critical' ? '🔴 DEFISIT KRITIS' : (v.status === 'Attention' ? '🟡 TERCOVER PO' : '🟢 HEALTHY (AMAN)');
                                return `${fullNames[idx]} [${tag}]`;
                            },
                            afterTitle: (tooltipItems) => {
                                const idx = tooltipItems[0].dataIndex;
                                const v = filteredVendors[idx];
                                return `Status: ${v.status} (${v.critical_items_count || 0} Defisit • ${v.healthy_items_count || 0} Aman)\n─────────────────────────────`;
                            },
                            label: (ctx) => `  ${ctx.dataset.label}: ${Number(ctx.raw || 0).toLocaleString('id-ID')} PCS`,
                            footer: (tooltipItems) => {
                                const idx = tooltipItems[0].dataIndex;
                                const v = filteredVendors[idx];
                                const pot = (v.total_actual_inventory || 0) + (v.total_outstanding || 0);
                                const dem = v.total_in_demand || 0;
                                const gap = pot - dem;
                                const gapText = gap >= 0 ? `+${Number(gap).toLocaleString('id-ID')} PCS (Surplus)` : `${Number(gap).toLocaleString('id-ID')} PCS (Defisit)`;
                                return `\nTotal Potensi Pasokan: ${Number(pot).toLocaleString('id-ID')} PCS (${gapText})\n💡 Klik grafik untuk diagnosa detail SKU vendor ini`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8', font: { family: 'Outfit', weight: 'bold', size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.07)' },
                        ticks: {
                            color: '#94a3b8',
                            font: { family: 'Outfit' },
                            callback: val => Number(val).toLocaleString('id-ID') + ' PCS'
                        }
                    }
                }
            }
        });

        // Direct Native Canvas Click Listener Failsafe
        canvas.onclick = function(e) {
            if (!chartVendorAreaInst) return;
            const elements = chartVendorAreaInst.getElementsAtEventForMode(e, 'index', { intersect: false }, false);
            if (elements && elements.length > 0) {
                const idx = elements[0].index;
                if (fullNames[idx]) {
                    openVendorDiagnosticModal(fullNames[idx]);
                }
            } else {
                const elementsX = chartVendorAreaInst.getElementsAtEventForMode(e, 'nearest', { intersect: false, axis: 'x' }, false);
                if (elementsX && elementsX.length > 0) {
                    const idx = elementsX[0].index;
                    if (fullNames[idx]) {
                        openVendorDiagnosticModal(fullNames[idx]);
                    }
                }
            }
        };
    }

    // ═══════════════════════════════════════════════════════════
    // ── POPUP MODAL DIAGNOSA KESEHATAN VENDOR & ITEM CODE ─────
    // ═══════════════════════════════════════════════════════════

    function openVendorDiagnosticModal(vendorName) {
        if (!vendorName) return;
        const vendorObj = vendorOverviewList.find(v => v.supplier_name === vendorName);
        if (!vendorObj) return;

        selectedVendorDiag = vendorName;
        currentDiagSearch = '';
        const searchInput = document.getElementById('searchDiagModalInput');
        if (searchInput) searchInput.value = '';

        // 1. Header Information
        const nameEl = document.getElementById('diagVendorName');
        const codeEl = document.getElementById('diagVendorCode');
        if (nameEl) nameEl.innerText = vendorObj.supplier_name;
        if (codeEl) codeEl.innerText = `KODE: ${vendorObj.supplier_code || '-'}`;

        const statusBadge = document.getElementById('diagVendorStatusBadge');
        if (statusBadge) {
            if (vendorObj.status === 'Healthy') {
                statusBadge.className = 'badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2.5 py-1 font-monospace';
                statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>HEALTHY (AMAN)';
            } else if (vendorObj.status === 'Attention') {
                statusBadge.className = 'badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 px-2.5 py-1 font-monospace';
                statusBadge.innerHTML = '<i class="bi bi-shield-fill-check me-1"></i>ATTENTION (TERCOVER PO)';
            } else if (vendorObj.status === 'Critical') {
                statusBadge.className = 'badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-2.5 py-1 font-monospace';
                statusBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>CRITICAL (DEFISIT)';
            } else {
                statusBadge.className = 'badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-50 px-2.5 py-1 font-monospace';
                statusBadge.innerHTML = '<i class="bi bi-info-circle me-1"></i>CHECK DATA';
            }
        }

        // 2. Reason Banner
        const banner = document.getElementById('diagReasonBanner');
        const bannerIcon = document.getElementById('diagReasonIcon');
        const bannerTitle = document.getElementById('diagReasonTitle');
        const bannerDesc = document.getElementById('diagReasonDesc');

        if (vendorObj.status === 'Critical') {
            banner.style.background = 'rgba(239, 68, 68, 0.12)';
            banner.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            bannerIcon.className = 'bi bi-exclamation-triangle-fill fs-3 text-danger';
            bannerTitle.innerText = `Status: Critical`;
            bannerDesc.innerText = vendorObj.status_reason || `Terdapat ${vendorObj.critical_items_count || 0} item code yang kekurangan stok fisik dan belum mencukupi kebutuhan produksi.`;
        } else if (vendorObj.status === 'Attention') {
            banner.style.background = 'rgba(245, 158, 11, 0.12)';
            banner.style.borderColor = 'rgba(245, 158, 11, 0.4)';
            bannerIcon.className = 'bi bi-shield-fill-check fs-3 text-warning';
            bannerTitle.innerText = `Status: Attention`;
            bannerDesc.innerText = vendorObj.status_reason || `Stok fisik di bawah kebutuhan, namun aman tercover pesanan PO berjalan.`;
        } else {
            banner.style.background = 'rgba(16, 185, 129, 0.12)';
            banner.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            bannerIcon.className = 'bi bi-check-circle-fill fs-3 text-success';
            bannerTitle.innerText = `Status: Healthy`;
            bannerDesc.innerText = vendorObj.status_reason || `Seluruh item code memiliki stok fisik yang mencukupi rencana kebutuhan produksi.`;
        }

        // 3. Metric Counters
        document.getElementById('diagTotalItems').innerText = vendorObj.items.length;
        document.getElementById('diagCriticalCount').innerText = vendorObj.critical_items_count || 0;
        document.getElementById('diagAttentionCount').innerText = vendorObj.attention_items_count || 0;
        document.getElementById('diagHealthyCount').innerText = vendorObj.healthy_items_count || 0;
        document.getElementById('diagTotalDeficit').innerText = `${Number(vendorObj.total_additional_req || 0).toLocaleString('id-ID')} PCS`;

        // 4. Tab Counters
        document.getElementById('tabCountCritical').innerText = vendorObj.critical_items_count || 0;
        document.getElementById('tabCountAttention').innerText = vendorObj.attention_items_count || 0;
        document.getElementById('tabCountHealthy').innerText = vendorObj.healthy_items_count || 0;
        document.getElementById('tabCountAll').innerText = vendorObj.items.length;

        // 5. Default Active Tab: Critical if critical items exist, otherwise ALL
        if ((vendorObj.critical_items_count || 0) > 0) {
            filterDiagModalCategory('Critical');
        } else if ((vendorObj.attention_items_count || 0) > 0) {
            filterDiagModalCategory('Attention');
        } else {
            filterDiagModalCategory('ALL');
        }

        // 6. Show Modal
        const modalEl = document.getElementById('modalVendorDiagnostic');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function filterDiagModalCategory(cat) {
        currentDiagCategory = cat;

        const btnCrit = document.getElementById('tabDiagCritical');
        const btnAtt  = document.getElementById('tabDiagAttention');
        const btnHlth = document.getElementById('tabDiagHealthy');
        const btnAll  = document.getElementById('tabDiagAll');

        if (btnCrit) {
            if (cat === 'Critical') {
                btnCrit.classList.add('active', 'btn-danger', 'text-white');
                btnCrit.classList.remove('btn-outline-danger');
            } else {
                btnCrit.classList.remove('active', 'btn-danger', 'text-white');
                btnCrit.classList.add('btn-outline-danger');
            }
        }

        if (btnAtt) {
            if (cat === 'Attention') {
                btnAtt.classList.add('active', 'btn-warning', 'text-dark');
                btnAtt.classList.remove('btn-outline-warning');
            } else {
                btnAtt.classList.remove('active', 'btn-warning', 'text-dark');
                btnAtt.classList.add('btn-outline-warning');
            }
        }

        if (btnHlth) {
            if (cat === 'Healthy') {
                btnHlth.classList.add('active', 'btn-success', 'text-white');
                btnHlth.classList.remove('btn-outline-success');
            } else {
                btnHlth.classList.remove('active', 'btn-success', 'text-white');
                btnHlth.classList.add('btn-outline-success');
            }
        }

        if (btnAll) {
            if (cat === 'ALL') {
                btnAll.classList.add('active', 'btn-secondary', 'text-white');
                btnAll.classList.remove('btn-outline-secondary');
            } else {
                btnAll.classList.remove('active', 'btn-secondary', 'text-white');
                btnAll.classList.add('btn-outline-secondary');
            }
        }

        renderDiagModalTableItems();
    }

    function filterDiagModalSearch(query) {
        currentDiagSearch = String(query || '').toLowerCase().trim();
        renderDiagModalTableItems();
    }

    function renderDiagModalTableItems() {
        const tbody = document.getElementById('tbodyDiagItems');
        if (!tbody || !selectedVendorDiag) return;
        tbody.innerHTML = '';

        const vendorObj = vendorOverviewList.find(v => v.supplier_name === selectedVendorDiag);
        if (!vendorObj || !vendorObj.items) return;

        let filteredItems = vendorObj.items;

        // Apply Category Filter
        if (currentDiagCategory !== 'ALL') {
            filteredItems = filteredItems.filter(item => item.status === currentDiagCategory);
        }

        // Apply Search Filter
        if (currentDiagSearch) {
            filteredItems = filteredItems.filter(item => {
                const p = String(item.part_number || '').toLowerCase();
                const d = String(item.description || '').toLowerCase();
                return p.includes(currentDiagSearch) || d.includes(currentDiagSearch);
            });
        }

        if (filteredItems.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block opacity-40 mb-1"></i>
                Tidak ada item code pada kriteria filter ini.
            </td></tr>`;
            return;
        }

        filteredItems.forEach(item => {
            const tr = document.createElement('tr');
            const gap = Number(item.inventory_gap || 0);
            const gapClass = (gap <= 0) ? 'text-success' : 'text-danger';
            const gapSign = (gap > 0) ? '+' : '';

            let statusBadge = '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-40 px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Healthy</span>';
            if (item.status === 'Critical') {
                statusBadge = '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-40 px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Critical</span>';
            } else if (item.status === 'Attention') {
                statusBadge = '<span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-40 px-2 py-1"><i class="bi bi-shield-fill-check me-1"></i>Attention</span>';
            } else if (item.status === 'Check Data') {
                statusBadge = '<span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-40 px-2 py-1"><i class="bi bi-info-circle me-1"></i>Check Data</span>';
            }

            const covPct = Number(item.coverage_pct || 100);
            const covBadgeClass = covPct >= 100 ? 'bg-success' : (covPct >= 50 ? 'bg-primary' : 'bg-danger');

            tr.innerHTML = `
                <td class="ps-3"><strong class="text-warning font-monospace fs-7">${item.part_number}</strong></td>
                <td class="text-light text-truncate" style="max-width: 200px;" title="${item.description || '-'}">${item.description || '-'}</td>
                <td class="text-center"><span class="badge-plant">${item.factory_code || 'KIP1'}</span></td>
                <td class="text-end text-info fw-bold">${Number(item.in_demand || 0).toLocaleString('id-ID')} PCS</td>
                <td class="text-end fw-bold" style="color: #a78bfa;">${Number(item.actual_inventory || 0).toLocaleString('id-ID')} PCS</td>
                <td class="text-end text-warning fw-bold">${Number(item.outstanding || 0).toLocaleString('id-ID')} PCS</td>
                <td class="text-end fw-bold ${gapClass}">${gapSign}${gap.toLocaleString('id-ID')} PCS</td>
                <td class="text-center">
                    <span class="badge ${covBadgeClass} bg-opacity-25 text-light border border-secondary border-opacity-40 px-2 py-0.5">
                        ${covPct}%
                    </span>
                </td>
                <td class="text-center">${statusBadge}</td>
                <td style="font-size: 0.78rem;" class="text-muted text-truncate" style="max-width: 260px;" title="${item.issue_reason || item.action_note || '-'}">
                    <span class="${item.status === 'Critical' ? 'text-danger fw-semibold' : (item.status === 'Attention' ? 'text-info' : 'text-light')}">
                        ${item.issue_reason || item.action_note || '-'}
                    </span>
                </td>
                <td class="text-center pe-3">
                    <button type="button" class="btn btn-xs btn-outline-purple rounded-pill px-2.5 py-1 fw-bold" onclick="jumpToItemCode3DFromModal('${item.part_number}')" title="Buka grafik analisis 3D untuk item ini">
                        <i class="bi bi-graph-up me-0.5"></i>3D
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function jumpToItemCode3DFromModal(partNumber) {
        if (!selectedVendorDiag) return;
        const targetVendor = selectedVendorDiag;

        // Hide Modal
        const modalEl = document.getElementById('modalVendorDiagnostic');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        // Trigger Drill-down to Vendor & Item
        setTimeout(() => {
            drillDownToVendor(targetVendor);
            setTimeout(() => {
                drillDownToItemCode(partNumber);
                // Smooth scroll to Level 3
                document.getElementById('drilldownLevel3')?.scrollIntoView({ behavior: 'smooth' });
            }, 150);
        }, 300);
    }

    function proceedFromModalToL2() {
        if (!selectedVendorDiag) return;
        const targetVendor = selectedVendorDiag;

        const modalEl = document.getElementById('modalVendorDiagnostic');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        setTimeout(() => {
            drillDownToVendor(targetVendor);
            document.getElementById('drilldownLevel2')?.scrollIntoView({ behavior: 'smooth' });
        }, 300);
    }

    // ═══════════════════════════════════════════════════════════
    // ── LEVEL 1: TABLE SEARCH & DRILL-DOWN TO LEVEL 2 ─────────
    // ═══════════════════════════════════════════════════════════

    function filterVendorOverviewTable(query) {
        const q = String(query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#tbodyVendorOverview tr.vendor-overview-row');
        rows.forEach(r => {
            const vName = String(r.getAttribute('data-vendor-name') || '').toLowerCase();
            const vCode = String(r.getAttribute('data-vendor-code') || '').toLowerCase();
            if (!q || vName.includes(q) || vCode.includes(q)) {
                r.classList.remove('d-none');
            } else {
                r.classList.add('d-none');
            }
        });
    }

    function drillDownToVendor(vendorName) {
        const vendorObj = vendorOverviewList.find(v => v.supplier_name === vendorName);
        if (!vendorObj) return;

        selectedVendor = vendorName;
        selectedItemCode = null;

        // 1. Update Breadcrumb
        const bcVendor = document.getElementById('bcVendorItem');
        const bcVendorName = document.getElementById('bcVendorName');
        const bcItemCode = document.getElementById('bcItemCode');

        if (bcVendor && bcVendorName) {
            bcVendor.classList.remove('d-none');
            bcVendorName.innerText = vendorName;
        }
        if (bcItemCode) bcItemCode.classList.add('d-none');

        // 2. Switch View Panels: Hide L1, Show L2, Hide L3 until item clicked
        document.getElementById('drilldownLevel1')?.classList.add('d-none');
        document.getElementById('drilldownLevel2')?.classList.remove('d-none');
        document.getElementById('drilldownLevel3')?.classList.add('d-none');

        // 3. Update Level 2 Headers
        const titleEl = document.getElementById('activeVendorTitle');
        const subEl = document.getElementById('activeVendorSubtitle');
        if (titleEl) titleEl.innerText = `${vendorName} (${vendorObj.supplier_code || '-'})`;
        if (subEl) subEl.innerText = `${vendorObj.items.length} item`;

        // 4. Populate Table of Item Codes for this vendor
        populateVendorItemsTable(vendorObj.items);

        // 5. If vendor has items, automatically select first item to populate Level 3
        if (vendorObj.items && vendorObj.items.length > 0) {
            drillDownToItemCode(vendorObj.items[0].part_number);
        }
    }

    function populateVendorItemsTable(items) {
        const tbody = document.getElementById('tbodyVendorItems');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!items || items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada item code untuk vendor ini.</td></tr>`;
            return;
        }

        items.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'vendor-item-row';
            tr.id = `itemRow_${item.part_number}`;
            tr.setAttribute('data-part', item.part_number);
            tr.setAttribute('data-desc', item.description || '');
            tr.style.cursor = 'pointer';
            tr.onclick = () => drillDownToItemCode(item.part_number);

            const gap = Number(item.inventory_gap || 0);
            const gapClass = (gap <= 0) ? 'text-success' : 'text-danger';
            const gapSign = (gap > 0) ? '+' : '';

            let statusBadge = '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-40 px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Healthy</span>';
            if (item.status === 'Critical') {
                statusBadge = '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-40 px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Critical</span>';
            } else if (item.status === 'Attention') {
                statusBadge = '<span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-40 px-2 py-1"><i class="bi bi-shield-fill-check me-1"></i>Attention</span>';
            } else if (item.status === 'Check Data') {
                statusBadge = '<span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-40 px-2 py-1"><i class="bi bi-info-circle me-1"></i>Check Data</span>';
            }

            tr.innerHTML = `
                <td class="ps-3"><strong class="text-warning font-monospace">${item.part_number}</strong></td>
                <td class="text-light text-truncate" style="max-width: 220px;" title="${item.description || '-'}">${item.description || '-'}</td>
                <td class="text-center"><span class="badge-plant">${item.factory_code || 'KIP1'}</span></td>
                <td class="text-end text-info fw-bold">${Number(item.in_demand || 0).toLocaleString('id-ID')} PCS</td>
                <td class="text-end fw-bold" style="color: #a78bfa;">${Number(item.actual_inventory || 0).toLocaleString('id-ID')} PCS</td>
                <td class="text-end text-warning fw-bold">${Number(item.outstanding || 0).toLocaleString('id-ID')} PCS</td>
                <td class="text-end fw-bold ${gapClass}">${gapSign}${gap.toLocaleString('id-ID')} PCS</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center pe-3">
                    <button type="button" class="btn btn-xs btn-outline-purple rounded-pill px-2.5 py-1 fw-bold" onclick="event.stopPropagation(); drillDownToItemCode('${item.part_number}')">
                        <i class="bi bi-bar-chart-fill me-1"></i>Analisis 3D
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterVendorItemsTable(query) {
        const q = String(query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#tbodyVendorItems tr.vendor-item-row');
        rows.forEach(r => {
            const part = String(r.getAttribute('data-part') || '').toLowerCase();
            const desc = String(r.getAttribute('data-desc') || '').toLowerCase();
            if (!q || part.includes(q) || desc.includes(q)) {
                r.classList.remove('d-none');
            } else {
                r.classList.add('d-none');
            }
        });
    }

    // ═══════════════════════════════════════════════════════════
    // ── LEVEL 3: FOCUSED ITEM CODE COMPARISON FUNCTIONS ───────
    // ═══════════════════════════════════════════════════════════

    function drillDownToItemCode(partNumber) {
        if (!selectedVendor) return;
        const vendorObj = vendorOverviewList.find(v => v.supplier_name === selectedVendor);
        if (!vendorObj) return;

        const itemObj = vendorObj.items.find(i => i.part_number === partNumber);
        if (!itemObj) return;

        selectedItemCode = partNumber;

        // 1. Update Breadcrumb
        const bcItem = document.getElementById('bcItemCode');
        const bcItemText = document.getElementById('bcItemCodeText');
        if (bcItem && bcItemText) {
            bcItem.classList.remove('d-none');
            bcItemText.innerText = partNumber;
        }

        // 2. Highlight Row in Table
        document.querySelectorAll('#tbodyVendorItems tr.vendor-item-row').forEach(tr => {
            tr.classList.remove('table-active', 'border-purple');
            tr.style.backgroundColor = '';
        });
        const activeRow = document.getElementById(`itemRow_${partNumber}`);
        if (activeRow) {
            activeRow.style.backgroundColor = 'rgba(139, 92, 246, 0.15)';
        }

        // 3. Reveal Level 3 Panel
        const l3Panel = document.getElementById('drilldownLevel3');
        if (l3Panel) l3Panel.classList.remove('d-none');

        // 4. Update Level 3 Header & Metrics Strip
        document.getElementById('l3PartNumber').innerText = itemObj.part_number;
        document.getElementById('l3Description').innerText = itemObj.description || 'Material Item';
        document.getElementById('l3PlantBadge').innerText = itemObj.factory_code || 'KIP1';
        document.getElementById('l3VendorText').innerText = `VENDOR: ${selectedVendor} (${vendorObj.supplier_code || '-'})`;
        document.getElementById('l3ValuationText').innerText = `Valuasi: $${Number(itemObj.val_usd || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

        // Numbers
        document.getElementById('l3InDemand').innerText = Number(itemObj.in_demand || 0).toLocaleString('id-ID');
        document.getElementById('l3ActualInventory').innerText = Number(itemObj.actual_inventory || 0).toLocaleString('id-ID');
        document.getElementById('l3Outstanding').innerText = Number(itemObj.outstanding || 0).toLocaleString('id-ID');

        const gap = Number(itemObj.inventory_gap || (itemObj.in_demand - itemObj.actual_inventory));
        const gapEl = document.getElementById('l3InventoryGap');
        if (gapEl) {
            gapEl.innerText = (gap > 0 ? '+' : '') + gap.toLocaleString('id-ID');
            gapEl.className = 'fs-5 fw-bold ' + (gap <= 0 ? 'text-success' : 'text-danger');
        }

        // Status Badge
        const statusBadgeEl = document.getElementById('l3StatusBadge');
        if (statusBadgeEl) {
            if (itemObj.status === 'Healthy') {
                statusBadgeEl.className = 'badge-status badge-surplus';
                statusBadgeEl.innerHTML = '<i class="bi bi-check-circle-fill"></i> Healthy';
            } else if (itemObj.status === 'Attention') {
                statusBadgeEl.className = 'badge-status badge-covered';
                statusBadgeEl.innerHTML = '<i class="bi bi-shield-fill-check"></i> Attention';
            } else if (itemObj.status === 'Critical') {
                statusBadgeEl.className = 'badge-status badge-deficit';
                statusBadgeEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Critical';
            } else {
                statusBadgeEl.className = 'badge-status badge-optimal';
                statusBadgeEl.innerHTML = '<i class="bi bi-info-circle"></i> Check Data';
            }
        }

        // 5. Render Primary Chart 1: Area Chart
        renderL3AreaChart(itemObj);

        // 6. Render Primary Chart 2: Bullet Chart
        renderL3BulletChart(itemObj);
    }

    // ── CHART 1: AREA CHART (MONTHLY / HISTORICAL TREND) ──
    function renderL3AreaChart(item) {
        const canvas = document.getElementById('chartL3Area');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let labels = [];
        let demandData = [];
        let actualData = [];
        let poData = [];

        if (item.periods && item.periods.length > 0) {
            labels     = item.periods.map(p => p.period_label || p.last_stock_date || 'Snapshot');
            demandData = item.periods.map(p => p.in_demand || 0);
            actualData = item.periods.map(p => p.actual_inventory || 0);
            poData     = item.periods.map(p => p.outstanding || 0);

            const countBadge = document.getElementById('areaPeriodCountBadge');
            if (countBadge) countBadge.innerText = `${item.periods.length} Periode Snapshot`;
        } else {
            labels     = ['Periode Saat Ini'];
            demandData = [item.in_demand || 0];
            actualData = [item.actual_inventory || 0];
            poData     = [item.outstanding || 0];
        }

        // Gradients
        const g1 = ctx.createLinearGradient(0, 0, 0, 240);
        g1.addColorStop(0, 'rgba(59, 130, 246, 0.45)');
        g1.addColorStop(1, 'rgba(59, 130, 246, 0.02)');

        const g2 = ctx.createLinearGradient(0, 0, 0, 240);
        g2.addColorStop(0, 'rgba(168, 85, 247, 0.45)');
        g2.addColorStop(1, 'rgba(168, 85, 247, 0.02)');

        const g3 = ctx.createLinearGradient(0, 0, 0, 240);
        g3.addColorStop(0, 'rgba(245, 158, 11, 0.45)');
        g3.addColorStop(1, 'rgba(245, 158, 11, 0.02)');

        if (chartL3AreaInstance) {
            chartL3AreaInstance.destroy();
        }

        chartL3AreaInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'In Demand (Kebutuhan)',
                        data: demandData,
                        backgroundColor: g1,
                        borderColor: '#3b82f6',
                        borderWidth: 2.5,
                        fill: 'origin',
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#3b82f6'
                    },
                    {
                        label: 'Actual Inventory (Fisik)',
                        data: actualData,
                        backgroundColor: g2,
                        borderColor: '#a855f7',
                        borderWidth: 2.5,
                        fill: 'origin',
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#a855f7'
                    },
                    {
                        label: 'Outstanding PO',
                        data: poData,
                        backgroundColor: g3,
                        borderColor: '#f59e0b',
                        borderWidth: 2.5,
                        fill: 'origin',
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#f59e0b'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { color: '#cbd5e1', font: { family: 'Outfit', weight: 600, size: 11 } } },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        borderColor: 'rgba(139, 92, 246, 0.4)',
                        borderWidth: 1,
                        callbacks: {
                            label: ctx => `  ${ctx.dataset.label}: ${Number(ctx.raw || 0).toLocaleString('id-ID')} PCS`
                        }
                    }
                },
                scales: {
                    x: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#94a3b8', font: { family: 'Outfit', weight: 'bold' } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.07)' }, ticks: { color: '#94a3b8', font: { family: 'Outfit' }, callback: val => Number(val).toLocaleString('id-ID') + ' PCS' } }
                }
            }
        });
    }

    // ── CHART 2: BULLET CHART (TARGET VS ACTUAL VS OUTSTANDING GAUGE) ──
    function renderL3BulletChart(item) {
        const canvas = document.getElementById('chartL3Bullet');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const inDemand = Number(item.in_demand || 0);
        const actualInv = Number(item.actual_inventory || 0);
        const outstPo = Number(item.outstanding || 0);

        // Update Text Indicators
        document.getElementById('bulletTargetText').innerText = inDemand.toLocaleString('id-ID') + ' PCS';
        document.getElementById('bulletActualText').innerText = actualInv.toLocaleString('id-ID') + ' PCS';
        document.getElementById('bulletPoText').innerText = outstPo.toLocaleString('id-ID') + ' PCS';

        if (chartL3BulletInstance) {
            chartL3BulletInstance.destroy();
        }

        // Render Horizontal Bullet Comparison Bars
        chartL3BulletInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Target vs Realisasi Pasokan'],
                datasets: [
                    {
                        label: 'Target: In Demand',
                        data: [inDemand],
                        backgroundColor: 'rgba(59, 130, 246, 0.85)',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Realisasi: Actual Stock',
                        data: [actualInv],
                        backgroundColor: 'rgba(168, 85, 247, 0.85)',
                        borderColor: '#a855f7',
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Indikator: Outstanding PO',
                        data: [outstPo],
                        backgroundColor: 'rgba(245, 158, 11, 0.85)',
                        borderColor: '#f59e0b',
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#cbd5e1', font: { family: 'Outfit', size: 10, weight: 600 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        borderColor: 'rgba(6, 182, 212, 0.4)',
                        borderWidth: 1,
                        callbacks: {
                            label: ctx => `  ${ctx.dataset.label}: ${Number(ctx.raw || 0).toLocaleString('id-ID')} PCS`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.08)' },
                        ticks: { color: '#94a3b8', font: { family: 'Outfit', size: 10 }, callback: val => Number(val).toLocaleString('id-ID') }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { display: false }
                    }
                }
            }
        });
    }

    // ── NAVIGATION HELPERS ──
    function resetToVendorOverview() {
        selectedVendor = null;
        selectedItemCode = null;

        document.getElementById('bcVendorItem')?.classList.add('d-none');
        document.getElementById('bcItemCode')?.classList.add('d-none');

        document.getElementById('drilldownLevel1')?.classList.remove('d-none');
        document.getElementById('drilldownLevel2')?.classList.add('d-none');
        document.getElementById('drilldownLevel3')?.classList.add('d-none');
    }

    function backToVendorItems() {
        if (!selectedVendor) return;
        selectedItemCode = null;

        document.getElementById('bcItemCode')?.classList.add('d-none');
        document.getElementById('drilldownLevel2')?.classList.remove('d-none');
        document.getElementById('drilldownLevel3')?.classList.add('d-none');
    }
</script>

@include('partials.confirm-modal')
@include('partials.import-preview-modal')
<script src="{{ asset('js/kawai-notify.js') }}"></script>
<script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
