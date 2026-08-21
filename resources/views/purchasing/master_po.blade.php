<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Master PO & Jadwal Kedatangan - PT Kawai Indonesia</title>
    <meta name="description" content="Dashboard Master PO Standalone PT Kawai Indonesia">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
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
        .modal-dialog-scrollable form.modal-content {
            display: flex;
            flex-direction: column;
            max-height: 88vh;
            overflow: hidden;
        }
        .modal-dialog-scrollable form.modal-content .modal-body {
            flex: 1 1 auto;
            overflow-y: auto !important;
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
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
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

        .btn-glow {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }
        .btn-glow:hover {
            color: #fff;
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.6);
        }
        .btn-success-glow {
            background: linear-gradient(135deg, #059669, #047857);
            color: #fff;
            border: none;
            box-shadow: 0 0 15px rgba(5, 150, 105, 0.4);
        }
        .btn-success-glow:hover {
            color: #fff;
            box-shadow: 0 0 25px rgba(5, 150, 105, 0.6);
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
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25) !important;
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
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.master-po', 'hasFaqModal' => true])
    </div>
</nav>


@include('partials.faq-modal')

<div class="container-dashboard py-4">

    <!-- 7-STEP UNIFIED WORKFLOW STEPPER -->
    @include('partials.workflow-stepper', ['currentStep' => 2])

    <!-- STANDARDIZED PAGE HEADER & ACTION HIERARCHY -->
    <div class="kawai-page-header">
        <div class="kawai-page-header-left">
            <div class="page-icon-box" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35);">
                <i class="bi bi-box-seam text-success"></i>
            </div>
            <div>
                <h1 class="page-title-text">Master Purchase Order</h1>
                <p class="page-subtitle-text">Mengelola dan memonitor data PO vendor serta jadwal kedatangan PT Kawai Indonesia.</p>
            </div>
        </div>
        <div class="kawai-page-actions">
            <button type="button" class="btn-kawai-secondary" data-bs-toggle="modal" data-bs-target="#modalImportMasterPo" title="Import data Master PO dari Excel (hanya membaca kolom Plan / Pesanan)">
                <i class="bi bi-file-earmark-excel-fill text-success"></i> Import Master PO
            </button>
            <button type="button" class="btn-kawai-primary" onclick="switchToBulkTab()">
                <i class="bi bi-plus-circle-fill"></i> Tambah PO
            </button>
            <div class="dropdown">
                <button class="btn-kawai-more dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Opsi Tambahan">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark-custom dropdown-menu-end">
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.master-po.template') }}">
                            <i class="bi bi-download text-info"></i> Unduh Template Master PO (.xlsx)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.integrated-import.template') }}">
                            <i class="bi bi-file-earmark-spreadsheet text-muted"></i> Unduh Template Lengkap (PO + Incoming)
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

    <!-- FILTER BAR -->
    <div class="kawai-filter-bar">
        <form method="GET" action="{{ route('purchasing.master-po') }}" class="filter-inputs-group">
            <div class="d-flex align-items-center gap-2">
                <label class="text-muted small fw-bold mb-0">Periode:</label>
                <select name="periode" class="form-select form-select-sm form-select-dark" style="min-width: 140px;" onchange="this.form.submit()">
                    <option value="All" {{ ($periode == 'All' || empty($periode)) ? 'selected' : '' }}>-- Semua Periode --</option>
                    @foreach($availableMonths ?? $periodes ?? [] as $month)
                        <option value="{{ $month }}" {{ $periode == $month ? 'selected' : '' }}>{{ $month }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex align-items-center gap-2">
                <label class="text-muted small fw-bold mb-0">Pengantaran:</label>
                <select name="delivery_category" class="form-select form-select-sm form-select-dark" style="min-width: 150px;" onchange="this.form.submit()">
                    <option value="">-- Semua Pengantaran --</option>
                    @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                        <option value="{{ $dc->code }}" {{ ($selectedDeliveryCategory ?? '') == $dc->code ? 'selected' : '' }}>
                            {{ $dc->code }} - {{ $dc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(($periode && $periode !== 'All') || $search || ($selectedDeliveryCategory ?? ''))
                <a href="{{ route('purchasing.master-po') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Navigation Pills for Sub-sections -->
    <div class="mb-4">
        <ul class="nav nav-pills nav-pills-custom" id="masterPoPills" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-table-btn" data-bs-toggle="pill" data-bs-target="#tab-table" type="button" role="tab" aria-controls="tab-table" aria-selected="true">
                    <i class="bi bi-table text-success"></i> Tabel Utama Master PO
                    <span class="badge bg-success ms-1">{{ $masterPoList->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-bulk-btn" data-bs-toggle="pill" data-bs-target="#tab-bulk" type="button" role="tab" aria-controls="tab-bulk" aria-selected="false">
                    <i class="bi bi-journal-text text-warning"></i> Form Input &amp; Bulk PO
                </button>
            </li>
            <li class="nav-item ms-auto" role="presentation">
                <button class="nav-link" id="tab-matrix-btn" data-bs-toggle="pill" data-bs-target="#tab-matrix" type="button" role="tab" aria-controls="tab-matrix" aria-selected="false" style="border-color: rgba(255,255,255,0.15);">
                    <i class="bi bi-intersect text-info me-1"></i> Matriks Korelasi Sinergi
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="masterPoPillsContent">
        
        <!-- ════════════ TAB 1: TABEL UTAMA MASTER PO ════════════ -->
        <div class="tab-pane fade show active" id="tab-table" role="tabpanel" aria-labelledby="tab-table-btn">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <div>
                        <h4 class="fw-bold text-white mb-1">
                            <i class="bi bi-list-columns-reverse text-success me-2"></i> Daftar Master Purchase Order (PO)
                        </h4>
                        <p class="text-muted small mb-0">
                            Seluruh data PO yang terdaftar dalam sistem beserta status korelasi terhadap aktual penerimaan gudang.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="btnBulkDeleteMasterPo" class="btn btn-danger btn-sm rounded-pill px-3 d-none" onclick="confirmBulkDeleteMasterPo()">
                            <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih (<span id="bulkDeleteCountMasterPo">0</span>)
                        </button>
                        <span class="badge bg-dark border border-secondary text-light font-monospace px-3 py-2">
                            Total: {{ $masterPoList->count() }} Data PO
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-secondary text-uppercase text-muted" style="font-size: 0.78rem;">
                            <tr>
                                <th class="text-center py-3" style="width: 35px;">
                                    <input type="checkbox" id="checkAllMasterPo" class="form-check-input">
                                </th>
                                <th class="text-center py-3" style="width: 40px;">#</th>
                                <th class="py-3">Supplier Name</th>
                                <th class="py-3">Delivery Date</th>
                                <th class="py-3">Material Code</th>
                                <th class="py-3">Description</th>
                                <th class="py-3">PO No.</th>
                                <th class="py-3 text-center">Currency</th>
                                <th class="py-3 text-end">Price</th>
                                <th class="py-3 text-end">Plan</th>
                                <th class="py-3 text-end text-info">Plan Amount</th>
                                <th class="py-3 text-center">Status Penerimaan</th>
                                <th class="py-3 text-center text-warning">Kode Pabrik</th>
                                <th class="py-3 text-center">Aksi</th>
                                <th class="py-3 text-center text-nowrap" style="background: rgba(59,130,246,0.15); color: #60a5fa;">Kategori Pengantaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($masterPoList as $idx => $mp)
                                @php
                                    $currSymbol = strtoupper($mp->currency ?? 'USD') === 'IDR' ? 'Rp ' : '$ ';
                                    $isIdr = strtoupper($mp->currency ?? 'USD') === 'IDR';
                                    $dec = $isIdr ? 0 : 2;
                                    
                                    $price = (float)($mp->price ?? 0);
                                    $planQty = (int)($mp->qty ?? 0);
                                    $planAmt = $price * $planQty;
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="row-checkbox-masterpo form-check-input" value="{{ $mp->id }}">
                                    </td>
                                    <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                    <td class="fw-semibold text-white">{{ $mp->supplier ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-dark border border-secondary text-light px-2 py-1 font-monospace">
                                            <i class="bi bi-calendar-event me-1 text-info"></i>{{ $mp->tanggal ? \Carbon\Carbon::parse($mp->tanggal)->format('d-M-y') : '-' }}
                                        </span>
                                    </td>
                                    <td><code class="text-info fs-6 fw-bold">{{ $mp->item_code }}</code></td>
                                    <td class="text-light fw-medium">{{ $mp->name }}</td>
                                    <td><span class="text-gold fw-bold">{{ $mp->po }}</span></td>
                                    <td class="text-center">
                                        <span class="badge {{ $isIdr ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-info bg-opacity-25 text-info border border-info' }} px-2 py-1 fw-bold">
                                            {{ strtoupper($mp->currency ?? 'USD') }}
                                        </span>
                                    </td>
                                    <td class="text-end font-monospace fw-semibold text-warning">
                                        {{ $currSymbol }}{{ number_format($price, $dec, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-bold text-white fs-6">
                                        {{ number_format($planQty, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace fw-semibold text-info">
                                        {{ $currSymbol }}{{ number_format($planAmt, $dec, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($mp->receipt_status === 'Diterima Lengkap')
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1" title="Terkoneksi dengan data penerimaan Aktual">
                                                <i class="bi bi-check-circle-fill me-1"></i> Diterima Lengkap
                                            </span>
                                        @elseif($mp->receipt_status === 'Diterima Sebagian')
                                            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-2 py-1" title="Item ada di Master Data namun belum terealisasi penuh">
                                                <i class="bi bi-clock me-1"></i> Diterima {{ $mp->receipt_percentage }}%
                                            </span>
                                        @elseif($mp->receipt_status === 'Over Receipt')
                                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-2 py-1">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Over Receipt
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-50 px-2 py-1">
                                                <i class="bi bi-hourglass me-1"></i> Belum Diterima
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2 py-1 font-monospace">{{ $mp->factory_code ?: 'KIP 1' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalEditPo{{ $mp->id }}" title="Edit PO">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form id="deletePoForm{{ $mp->id }}" action="{{ route('purchasing.master-po.destroy', $mp->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus PO" onclick="KawaiConfirm.delete('Hapus Master PO #{{ $mp->po }}', 'Baris PO {{ $mp->item_code }} akan dihapus.', () => document.getElementById('deletePoForm{{ $mp->id }}').submit())">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="text-center text-nowrap">{!! $mp->delivery_category_badge ?? '' !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                        Belum ada data Master PO terdaftar. Gunakan tab <b>Kelola & Input Bulk PO</b> untuk menambahkan data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


            </div>
        </div>

        <!-- ════════════ TAB 2: KELOLA & INPUT BULK PO ════════════ -->
        <div class="tab-pane fade" id="tab-bulk" role="tabpanel" aria-labelledby="tab-bulk-btn">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <div>
                        <h4 class="fw-bold text-white mb-1">
                            <i class="bi bi-file-earmark-spreadsheet text-warning me-2"></i> Editor Bulk Master Purchase Order
                        </h4>
                        <p class="text-muted small mb-0">
                            Masukkan atau edit data Purchase Order secara langsung dan simpan secara kolektif ke server.
                        </p>
                    </div>
                </div>

                <div class="row g-3 mb-4 bg-dark bg-opacity-50 p-3 rounded-3 border border-secondary border-opacity-25">
                    <div class="col-md-2">
                        <label class="form-label small text-muted text-uppercase fw-semibold">Tanggal PO (DD/MM/YYYY)</label>
                        <input type="date" id="m_tanggal" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted text-uppercase fw-semibold">Supplier</label>
                        <input type="text" id="m_supplier" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="PT Kawai Supplier...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted text-uppercase fw-semibold">PO Number</label>
                        <input type="text" id="m_po" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="No. PO (KI-0001)">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted text-uppercase fw-semibold d-flex justify-content-between align-items-center mb-1">
                            <span>Item Code</span>
                            <button type="button" class="btn p-0 text-info text-decoration-none small fw-bold" onclick="openItemCodeSelectorModal('m_itemcode', 'm_name')" style="font-size:0.75rem;">
                                <i class="bi bi-window-stack"></i> Pop-up
                            </button>
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="m_itemcode" class="form-control bg-dark text-white border-secondary" list="registeredItemCodesList" onchange="autoFillItemDescription(this, 'm_name')" oninput="autoFillItemDescription(this, 'm_name')" placeholder="Ketik Item Code Baru atau Cari...">
                            <button type="button" class="btn btn-outline-info" onclick="openItemCodeSelectorModal('m_itemcode', 'm_name')" title="Pilih Item Code dari Pop-up">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted text-uppercase fw-semibold">Name</label>
                        <input type="text" id="m_name" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Deskripsi material">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small text-muted text-uppercase fw-semibold">Qty</label>
                        <input type="number" id="m_qty" class="form-control form-control-sm bg-dark text-white border-secondary" value="0">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small text-warning text-uppercase fw-semibold">Price</label>
                        <input type="text" id="m_price" class="form-control form-control-sm bg-dark text-warning border-secondary fw-bold" placeholder="0.00">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small text-info text-uppercase fw-semibold">Currency</label>
                        <select id="m_currency" class="form-select form-select-sm bg-dark text-info border-secondary fw-bold">
                            <option value="USD">USD</option>
                            <option value="IDR">IDR</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-info fw-bold text-uppercase"><i class="bi bi-truck me-1"></i> Pengantaran</label>
                        <select id="m_delivery_category_code" class="form-select form-select-sm bg-dark text-white border-info fw-bold">
                            @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                <option value="{{ $dc->code }}" {{ $dc->code === 'LOC' ? 'selected' : '' }}>
                                    {{ $dc->code }} - {{ $dc->name }} ({{ $dc->currency }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" id="btnAddMaster" class="btn btn-sm btn-info text-dark fw-bold w-100">Tambah</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" id="masterPoTable" style="font-size:0.9rem;">
                        <thead class="table-secondary text-uppercase text-muted" style="font-size:0.8rem;">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Tanggal PO</th>
                                <th>Supplier</th>
                                <th>PO Number</th>
                                <th>Item Code</th>
                                <th>Name</th>
                                <th class="text-end">Qty Order</th>
                                <th class="text-end text-warning">Price</th>
                                <th class="text-center text-info">Currency</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="masterPoTbody">
                            @forelse($masterPoList as $idx => $mp)
                                <tr data-id="{{ $mp->id }}">
                                    <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                    <td>{{ $mp->tanggal ? \Carbon\Carbon::parse($mp->tanggal)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $mp->supplier }}</td>
                                    <td>{{ $mp->po }}</td>
                                    <td>{{ $mp->item_code }}</td>
                                    <td>{{ $mp->name }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($mp->qty) }}</td>
                                    <td class="text-end font-monospace text-warning fw-semibold">{{ $mp->price > 0 ? number_format($mp->price, 2, ',', '.') : '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ strtoupper($mp->currency ?? 'USD') === 'IDR' ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-info bg-opacity-25 text-info border border-info' }} px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                            {{ strtoupper($mp->currency ?? 'USD') }}
                                        </span>
                                    </td>
                                    <td class="text-center"><button class="btn btn-sm btn-outline-danger btnRemove">Hapus</button></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data Master PO. Tambah baris menggunakan form di atas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════════════ TAB 3: MATRIKS KORELASI SINERGI ════════════ -->
        <div class="tab-pane fade" id="tab-matrix" role="tabpanel" aria-labelledby="tab-matrix-btn">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <div>
                        <h4 class="fw-bold text-white mb-1">
                            <i class="bi bi-intersect text-info me-2"></i> Matriks Korelasi Sinergi Inter-Modul
                        </h4>
                        <p class="text-muted small mb-0">
                            Menghubungkan hasil dari <b>Dashboard Realisasi Aktual</b>, <b>Master PO</b>, dan <b>Master Data (Forecast & Outstanding)</b>.
                        </p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="p-4 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(16, 185, 129, 0.08);">
                            <h6 class="text-success fw-bold mb-3"><i class="bi bi-arrow-left-right me-2"></i> Sinergi ke Realisasi Aktual</h6>
                            <p class="text-muted small mb-3">
                                Saat Anda menginput surat jalan penerimaan pada <b>Dashboard Realisasi (Input Aktual)</b>, sistem akan mencocokkan `Item Code` atau `No. PO` secara live dengan tabel Master PO ini.
                            </p>
                            <div class="d-flex align-items-center justify-content-between border-top border-secondary border-opacity-25 pt-2 small text-muted">
                                <span>Status Sinkronisasi:</span>
                                <span class="badge bg-success">Aktif Terhubung</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="p-4 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(245, 158, 11, 0.08);">
                            <h6 class="text-warning fw-bold mb-3"><i class="bi bi-box-seam me-2"></i> Sinergi ke Master Outstanding</h6>
                            <p class="text-muted small mb-3">
                                Item SKU yang terdaftar dalam Master PO akan dibandingkan dengan sisa order (`Outstanding PO`) untuk menghitung status pemenuhan pasokan gudang PT Kawai Indonesia.
                            </p>
                            <div class="d-flex align-items-center justify-content-between border-top border-secondary border-opacity-25 pt-2 small text-muted">
                                <span>Status Sinkronisasi:</span>
                                <span class="badge bg-warning text-dark">Aktif Terhubung</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="p-4 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(59, 130, 246, 0.08);">
                            <h6 class="text-primary fw-bold mb-3"><i class="bi bi-graph-up me-2"></i> Sinergi ke Forecasting</h6>
                            <p class="text-muted small mb-3">
                                Jadwal kedatangan dan kuantitas pesanan Master PO dijadikan patokan dalam menyusun rasio stok bulanan terhadap rencana kebutuhan produksi (`PROD / Forecast`) bulan berikutnya.
                            </p>
                            <div class="d-flex align-items-center justify-content-between border-top border-secondary border-opacity-25 pt-2 small text-muted">
                                <span>Status Sinkronisasi:</span>
                                <span class="badge bg-primary">Aktif Terhubung</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    return "{{ csrf_token() }}";
}

function switchToBulkTab() {
    const tabBtn = document.getElementById('tab-bulk-btn');
    if (tabBtn) {
        const tab = bootstrap.Tab.getOrCreateInstance(tabBtn);
        tab.show();
        setTimeout(() => {
            const poInput = document.getElementById('m_po');
            if (poInput) {
                poInput.focus();
                poInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 150);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const btnAddMaster = document.getElementById('btnAddMaster');
    const masterTbody = document.getElementById('masterPoTbody');

    function renderEmptyRow() {
        if (!masterTbody) return;
        masterTbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">Belum ada data Master PO. Tambah baris menggunakan form di atas.</td></tr>';
    }

    if (btnAddMaster && masterTbody) {
        btnAddMaster.addEventListener('click', function() {
            const tanggal = document.getElementById('m_tanggal').value;
            const supplier = document.getElementById('m_supplier').value;
            const po = document.getElementById('m_po').value;
            const itemcode = document.getElementById('m_itemcode').value;
            const name = document.getElementById('m_name').value;
            const qty = document.getElementById('m_qty').value;
            const price = document.getElementById('m_price') ? document.getElementById('m_price').value : '0';
            const currency = document.getElementById('m_currency') ? document.getElementById('m_currency').value : 'USD';
            const deliveryCategoryCode = document.getElementById('m_delivery_category_code') ? document.getElementById('m_delivery_category_code').value : 'LOC';

            if (!itemcode && !po) {
                if (window.notify) {
                    window.notify.warning('Input Kurang', 'Mohon isi minimal Item Code atau PO Number.');
                }
                return;
            }

            const originalBtnText = btnAddMaster.innerHTML;
            btnAddMaster.disabled = true;
            btnAddMaster.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';

            const token = getCsrfToken();

            fetch("{{ route('purchasing.master-po.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    _token: token,
                    tanggal: tanggal,
                    supplier: supplier,
                    po: po,
                    item_code: itemcode,
                    name: name,
                    qty: qty || 0,
                    price: price || 0,
                    currency: currency || 'USD',
                    delivery_category_code: deliveryCategoryCode
                })
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (res.ok && (data.success || data.data || data.message)) {
                    if (window.notify) {
                        window.notify.success('Berhasil Disimpan', 'Data Master PO berhasil ditambahkan.');
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Gagal menyimpan data Master PO.');
                    if (window.notify) {
                        window.notify.error('Gagal Simpan', errorMsg);
                    }
                    btnAddMaster.disabled = false;
                    btnAddMaster.innerHTML = originalBtnText;
                }
            })
            .catch(err => {
                console.error(err);
                if (window.notify) {
                    window.notify.error('Kesalahan Jaringan', 'Terjadi kesalahan koneksi saat menyimpan ke server.');
                }
                btnAddMaster.disabled = false;
                btnAddMaster.innerHTML = originalBtnText;
            });
        });

        masterTbody.addEventListener('click', function(e) {
            const btnRemove = e.target.closest('.btnRemove');
            if (btnRemove) {
                const tr = btnRemove.closest('tr');
                const id = tr.getAttribute('data-id');
                if (id) {
                    KawaiConfirm.delete('Hapus Master PO', 'Data Master PO ini akan dihapus dari database.', function() {
                        fetch(`/purchasing/master-po/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(async res => {
                            if (res.ok) {
                                window.location.reload();
                            } else {
                                const data = await res.json().catch(() => ({}));
                                if (window.notify) {
                                    window.notify.error('Gagal Hapus', data.message || 'Gagal menghapus data dari database.');
                                }
                                setTimeout(() => window.location.reload(), 1500);
                            }
                        })
                        .catch(err => {
                            if (window.notify) {
                                window.notify.error('Koneksi Gagal', 'Terjadi kesalahan jaringan.');
                            }
                        });
                    });
                } else {
                    tr.remove();
                    if (masterTbody.children.length === 0) {
                        renderEmptyRow();
                    } else {
                        Array.from(masterTbody.children).forEach((row, index) => {
                            if (row.children[0]) row.children[0].textContent = index + 1;
                        });
                    }
                }
            }
        });
    }
});
</script>
<!-- Modals Edit PO (placed outside any container to prevent viewport/backdrop clipping) -->
@foreach($masterPoList as $mp)
    <div class="modal fade text-start" id="modalEditPo{{ $mp->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <form action="{{ route('purchasing.master-po.update', $mp->id) }}" method="POST" class="modal-content modal-content-dark border-secondary text-white">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <h5 class="modal-title"><i class="bi bi-pencil-square text-info me-2"></i> Edit Master PO #{{ $mp->po }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Tanggal PO</label>
                        <input type="date" name="tanggal" class="form-control bg-dark text-white border-secondary" value="{{ $mp->tanggal }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Supplier / Vendor</label>
                        <input type="text" name="supplier" class="form-control bg-dark text-white border-secondary" value="{{ $mp->supplier }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Nomor PO</label>
                            <input type="text" name="po" class="form-control bg-dark text-white border-secondary" value="{{ $mp->po }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Item Code</label>
                            <input type="text" name="item_code" class="form-control bg-dark text-white border-secondary" value="{{ $mp->item_code }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Nama Material / Deskripsi</label>
                        <input type="text" name="name" class="form-control bg-dark text-white border-secondary" value="{{ $mp->name }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label text-muted small">Quantity Order</label>
                            <input type="number" name="qty" class="form-control bg-dark text-white border-secondary" value="{{ $mp->qty }}" required min="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-warning small fw-bold">Price (Harga)</label>
                            <input type="text" name="price" class="form-control bg-dark text-warning border-secondary fw-bold" value="{{ $mp->price > 0 ? number_format($mp->price, 2, '.', '') : '' }}" placeholder="0.00">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-warning small fw-bold">Mata Uang</label>
                            <select name="currency" class="form-select bg-dark text-warning border-secondary fw-bold">
                                <option value="USD" {{ ($mp->currency ?? 'USD') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                <option value="IDR" {{ ($mp->currency ?? 'USD') == 'IDR' ? 'selected' : '' }}>IDR (Rp)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-info small fw-bold"><i class="bi bi-truck me-1"></i> Kategori Pengantaran</label>
                        <select name="delivery_category_code" class="form-select bg-dark text-white border-secondary">
                            @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                <option value="{{ $dc->code }}" {{ ($mp->delivery_category_code ?? 'LOC') == $dc->code ? 'selected' : '' }}>
                                    {{ $dc->code }} - {{ $dc->name }} ({{ $dc->currency }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 bg-dark">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-dark btn-sm rounded-pill px-4 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<div class="modal fade" id="modalImportMasterPo" tabindex="-1" aria-labelledby="modalImportMasterPoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-dark">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-white" id="modalImportMasterPoLabel"><i class="bi bi-file-earmark-arrow-up-fill text-success me-2"></i>Import Master PO (Plan / Pesanan)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchasing.master-po.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info bg-info bg-opacity-10 border border-info border-opacity-25 text-info py-2 px-3 mb-3 small rounded-3">
                        <i class="bi bi-info-circle-fill me-1"></i> <b>Step 2 — Master PO</b>: Hanya kolom <b>Plan / Plan Amount</b> yang akan diproses dan disimpan ke Master PO. Kolom Result (jika ada pada file EZRunner) akan diabaikan dan tidak otomatis membuat Incoming.
                    </div>
                    
                    <div class="mb-3">
                        <a href="{{ route('purchasing.master-po.template') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
                            <i class="bi bi-download me-1"></i> Unduh Template Master PO (.xlsx)
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
                <div class="modal-footer border-secondary border-opacity-25 py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-dark btn-sm rounded-pill px-4 fw-bold"><i class="bi bi-cloud-upload-fill me-1"></i> Mulai Impor Master PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const importForm = document.getElementById('importMasterPoForm');
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

                const reader = new FileReader();
                reader.onload = function(evt) {
                    try {
                        const data = new Uint8Array(evt.target.result);
                        const workbook = XLSX.read(data, { type: 'array' });
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

                        const headerKeywords = {
                            po: ['ponumber', 'nomorpo', 'kodepo', 'nopo', 'poref', 'poreference', 'noorder', 'purchaseorder', 'po'],
                            itemcode: ['materialcode', 'itemcode', 'partnumber', 'partno', 'drawing', 'material', 'kodebarang', 'kodematerial', 'kodeitem', 'kodepart', 'komponen', 'mat', 'pn', 'sku', 'code', 'barang', 'item', 'part'],
                            supplier: ['suppliername', 'vendorname', 'namasupplier', 'namavendor', 'kodesupplier', 'kodevendor', 'supplier', 'vendor', 'pemasok', 'kdsupp', 'kdvendor', 'pt'],
                            name: ['description', 'deskripsibarang', 'deskripsi', 'namabarang', 'namamaterial', 'keterangan', 'itemname', 'materialname', 'partname', 'namapart', 'spec', 'desc'],
                            tanggal: ['tanggalpo', 'podate', 'orderdate', 'tanggal', 'date', 'tgl', 'periode'],
                            qty: ['qtypo', 'orderqty', 'planqty', 'targetpo', 'target', 'plan', 'qty', 'quantity', 'jumlah', 'kuantitas', 'vol', 'total', 'pcs'],
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
                            po: -1,
                            itemcode: -1,
                            supplier: -1,
                            name: -1,
                            tanggal: -1,
                            qty: -1,
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
                            if (window.notify) {
                                window.notify.warning('Format Tidak Sesuai', 'Format Excel tidak sesuai. Pastikan kolom "PO Number" atau "Item Code" tersedia.');
                            }
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            return;
                        }

                        const rows = [];
                        for (let i = bestHeaderIdx + 1; i < jsonData.length; i++) {
                            const row = jsonData[i];
                            if (!row || !Array.isArray(row) || row.every(cell => String(cell || '').trim() === '')) continue;

                            const poVal = colMap.po !== -1 ? String(row[colMap.po] || '').trim() : '';
                            const itemVal = colMap.itemcode !== -1 ? String(row[colMap.itemcode] || '').trim() : '';
                            
                            if (!poVal && !itemVal) continue;
                            if (itemVal.toUpperCase() === 'ITEM CODE' || itemVal.toUpperCase() === 'MATERIAL CODE' || itemVal.toUpperCase().startsWith('TOTAL')) continue;
                            if (poVal.toUpperCase() === 'PO NUMBER' || poVal.toUpperCase() === 'NO PO') continue;

                            const parsedPrice = colMap.price !== -1 ? parseExcelNumber(row[colMap.price]) : 0;
                            let rawCurr = colMap.currency !== -1 ? String(row[colMap.currency] || '').trim().toUpperCase() : '';
                            let finalCurr = (parsedPrice > 300 || rawCurr === 'IDR' || rawCurr.includes('RP')) ? 'IDR' : (rawCurr || 'USD');

                            rows.push({
                                tanggal: colMap.tanggal !== -1 ? row[colMap.tanggal] : null,
                                supplier: colMap.supplier !== -1 ? row[colMap.supplier] : null,
                                po: poVal || ('PO-' + (itemVal || (i + 1))),
                                itemcode: itemVal || poVal,
                                name: colMap.name !== -1 ? row[colMap.name] : null,
                                qty: colMap.qty !== -1 ? parseExcelNumber(row[colMap.qty]) : 0,
                                price: parsedPrice,
                                currency: finalCurr
                            });
                        }

                        if (rows.length === 0) {
                            if (window.notify) {
                                window.notify.warning('Tidak Ada Data', 'Tidak ada baris data valid yang terdeteksi untuk diimport.');
                            }
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            return;
                        }

                        const token = getCsrfToken();
                        fetch('{{ route("purchasing.master.bulk") }}', {
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
                                    window.notify.success('Import Berhasil', res.body.message || 'Data Master PO berhasil diimpor!');
                                }
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                let errMsg = res.body.message || 'Gagal menyimpan data.';
                                if (res.body.errors && Array.isArray(res.body.errors)) {
                                    errMsg += '\n' + res.body.errors.join('\n');
                                }
                                if (window.notify) {
                                    window.notify.error('Gagal Import', errMsg);
                                }
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

<!-- Modal Bulk Delete Confirmation Master PO -->
<div class="modal fade" id="modalBulkDeleteMasterPoConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-danger text-white" style="background: #111827;">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Hapus Massal Master PO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchasing.master-po.destroy-bulk') }}" method="POST" id="formBulkDeleteMasterPo">
                @csrf
                <div class="modal-body">
                    <div id="bulkDeleteMasterPoIdsContainer"></div>
                    Apakah Anda yakin ingin menghapus <strong id="bulkDeleteMasterPoCountText" class="text-danger">0</strong> data Master PO terpilih?
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
        const checkAllMasterPo = document.getElementById('checkAllMasterPo');
        const rowCheckboxesMasterPo = document.querySelectorAll('.row-checkbox-masterpo');
        const btnBulkDeleteMasterPo = document.getElementById('btnBulkDeleteMasterPo');
        const countSpanMasterPo = document.getElementById('bulkDeleteCountMasterPo');

        function updateMasterPoBulkBtn() {
            const checked = document.querySelectorAll('.row-checkbox-masterpo:checked');
            if (btnBulkDeleteMasterPo) {
                if (checked.length > 0) {
                    btnBulkDeleteMasterPo.classList.remove('d-none');
                    countSpanMasterPo.innerText = checked.length;
                } else {
                    btnBulkDeleteMasterPo.classList.add('d-none');
                }
            }
        }

        if (checkAllMasterPo) {
            checkAllMasterPo.addEventListener('change', function() {
                rowCheckboxesMasterPo.forEach(cb => cb.checked = this.checked);
                updateMasterPoBulkBtn();
            });
        }

        rowCheckboxesMasterPo.forEach(cb => {
            cb.addEventListener('change', function() {
                if (checkAllMasterPo) {
                    checkAllMasterPo.checked = (document.querySelectorAll('.row-checkbox-masterpo:checked').length === rowCheckboxesMasterPo.length);
                }
                updateMasterPoBulkBtn();
            });
        });
    });

    function confirmBulkDeleteMasterPo() {
        const checked = document.querySelectorAll('.row-checkbox-masterpo:checked');
        if (checked.length === 0) return;
        
        const container = document.getElementById('bulkDeleteMasterPoIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('bulkDeleteMasterPoCountText').innerText = checked.length;
        new bootstrap.Modal(document.getElementById('modalBulkDeleteMasterPoConfirm')).show();
    }
</script>
@include('partials.registered-item-codes-datalist')
@include('partials.modal-select-item-code')
@include('partials.confirm-modal')
@include('partials.import-preview-modal')
<script src="{{ asset('js/kawai-notify.js') }}"></script>
<script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
