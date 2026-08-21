<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Hasil Input & Outstanding Order | PT Kawai Indonesia</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-secondary: #121826;
            --card-bg: rgba(23, 31, 48, 0.82);
            --card-border: rgba(255, 255, 255, 0.09);
            --accent-gold: #e2b34a;
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

        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Outfit', sans-serif;
        }

        .top-navbar {
            background: rgba(18, 24, 38, 0.88);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand-logo-text {
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: 0.8px;
            background: linear-gradient(135deg, #ffffff 0%, var(--accent-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(12px);
        }

        .kpi-title {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.1px;
            color: var(--text-muted);
            margin-bottom: 0.45rem;
        }

        .kpi-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
        }

        .nav-tabs-custom {
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .nav-tabs-custom .nav-link {
            color: var(--text-muted);
            border: none;
            padding: 0.85rem 1.75rem;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            border-radius: 12px 12px 0 0;
            transition: all 0.25s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-tabs-custom .nav-link.active {
            background: var(--accent-gold);
            color: #000 !important;
            box-shadow: 0 -4px 15px rgba(226, 179, 74, 0.25);
        }

        .table-custom {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-main);
            --bs-table-border-color: rgba(255, 255, 255, 0.08);
            --bs-table-hover-bg: rgba(226, 179, 74, 0.08);
            --bs-table-hover-color: #ffffff;
            color: var(--text-main) !important;
            background: rgba(10, 14, 23, 0.58);
            margin-bottom: 0;
        }

        .table-custom thead th {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-muted);
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0.85rem;
        }

        .table-custom tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .table-custom tbody tr:hover {
            background: rgba(226, 179, 74, 0.06);
        }

        .table-custom td {
            background-color: rgba(10, 14, 23, 0.38) !important;
            color: var(--text-main) !important;
            background: transparent;
            color: var(--accent-gold);
            border-bottom-color: var(--accent-gold);
        }

        .badge-status {
            padding: 0.35em 0.8em;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: 6px;
        }

        .modal-content-dark {
            background: #141b2d;
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: var(--text-main);
            border-radius: 18px;
        }

        .form-control-dark, .form-select-dark {
            background: #111827;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 0.65rem 1rem;
        }

        .form-control-dark:focus, .form-select-dark:focus {
            background: #111827;
            color: #fff;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.25rem rgba(226, 179, 74, 0.2);
            outline: none;
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
                <div class="text-muted" style="font-size:0.72rem; margin-left:2px; color:#9ca3af !important;">Dashboard Riwayat &amp; Audit Log — Pengadaan Bahan Baku Piano</div>
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
            @include('partials.pill-nav', ['activeRoute' => 'purchasing.history', 'hasFaqModal' => true])
        </div>
    </nav>

    @include('partials.faq-modal')

    <!-- MAIN CONTENT -->
    <div class="container-fluid px-4 py-4">

        @include('partials.toast-and-notification-popup')

        <!-- HERO BANNER HEADER -->
        <div class="exchange-hero mb-4">
            <div class="row align-items-center">
                <div class="col-12 col-lg-7">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:rgba(226,179,74,0.18);border:1px solid rgba(226,179,74,0.45);color:#e2b34a;">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="hero-rate-label">TRANSACTIONS &amp; AUDIT LOG</span>
                                <span class="badge rounded-pill bg-warning text-dark fw-bold" style="font-size:0.68rem; padding: 2px 8px;">HISTORI</span>
                            </div>
                            <h2 class="fw-bold text-white mb-0 brand-font" style="font-size:1.65rem;">Riwayat Transaksi Hasil Input &amp; Outstanding Order</h2>
                        </div>
                    </div>
                    <p class="text-muted mb-0 mt-2" style="font-size:0.88rem;">
                        Pusat pemantauan histori transaksi pengadaan material PT Kawai Indonesia yang dapat <strong class="text-warning">Di-update (Edit)</strong> atau <strong class="text-danger">Dihapus</strong>.
                    </p>
                </div>
                <div class="col-12 col-lg-5 mt-3 mt-lg-0">
                    <form action="{{ route('purchasing.history') }}" method="GET" class="d-flex gap-2 justify-content-lg-end flex-wrap">
                        <input type="hidden" name="tab" id="current_tab_input" value="{{ $activeTab }}">
                        <select name="delivery_category" class="form-select bg-dark border-secondary text-white rounded-pill px-3" style="max-width: 170px; font-size:0.85rem;" onchange="this.form.submit()">
                            <option value="">-- Semua Pengantaran --</option>
                            @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                <option value="{{ $dc->code }}" {{ ($selectedDeliveryCategory ?? '') === $dc->code ? 'selected' : '' }}>
                                    {{ $dc->code }} - {{ $dc->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group" style="max-width: 280px;">
                            <span class="input-group-text border-secondary text-muted" style="background:rgba(10,14,23,0.92); border-color:rgba(255,255,255,0.18);"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" class="form-control form-control-dark" placeholder="Cari Referensi PO / Part..." value="{{ $searchQuery }}">
                        </div>
                        <button type="submit" class="btn btn-warning fw-bold px-4 rounded-pill">Cari</button>
                        @if($searchQuery || ($selectedDeliveryCategory ?? ''))
                            <a href="{{ route('purchasing.history', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary rounded-pill">Reset</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- 4 KPI SUMMARY CARDS -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card h-100">
                    <div class="kpi-title">TOTAL RIWAYAT INPUT INCOMING</div>
                    <div class="kpi-value text-info">{{ number_format($totalInputLogs, 0, ',', '.') }} <span class="fs-6 text-muted">catatan</span></div>
                    <div class="mt-2 text-muted small">
                        <i class="fa-solid fa-cubes text-info me-1"></i> Total Aktual Masuk: {{ number_format($totalInputReceived, 0, ',', '.') }} unit
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card h-100">
                    <div class="kpi-title">TOTAL RIWAYAT OUTSTANDING PO</div>
                    @php
                        $todayDate = date('Y-m-d');
                        $currentActiveRate = \App\Models\TaxExchangeRate::where('currency_code', 2)
                            ->whereDate('start_date', '<=', $todayDate)
                            ->whereDate('end_date', '>=', $todayDate)
                            ->value('tax_exchange_rate') 
                            ?? \App\Models\TaxExchangeRate::where('currency_code', 2)->orderByDesc('id')->value('tax_exchange_rate') 
                            ?? 16500;
                        $totalOutstandingAmountIdr = $totalOutstandingAmount * $currentActiveRate;
                    @endphp
                    <div class="mt-2 text-muted small">
                        <i class="fa-solid fa-coins text-warning me-1"></i> Nilai Total: $ {{ number_format($totalOutstandingAmount, 2, ',', '.') }}
                        <div class="text-success fw-bold font-monospace mt-0.5" style="font-size:0.78rem;" title="Konversi Rupiah dengan Kurs Pajak Terkini Hari Ini (Rp {{ number_format($currentActiveRate, 0, ',', '.') }}/$)">
                            <i class="bi bi-arrow-repeat me-1"></i>≈ Rp {{ number_format($totalOutstandingAmountIdr, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card h-100">
                    <div class="kpi-title">TOTAL UNIT TERKIRIM (COMPLETE)</div>
                    <div class="kpi-value text-success">{{ number_format($totalCompleteUnits, 0, ',', '.') }} <span class="fs-6 text-muted">unit</span></div>
                    <div class="mt-2 text-muted small">
                        <i class="fa-solid fa-check-double text-success me-1"></i> Akumulasi dari seluruh PO Outstanding
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card h-100">
                    <div class="kpi-title">KONTROL MANAJEMEN RIWAYAT</div>
                    <div class="kpi-value text-white fs-4">Full Edit &amp; Delete</div>
                    <div class="mt-2 text-success small">
                        <i class="fa-solid fa-shield-halved me-1"></i> Akses perubahan data aktif
                    </div>
                </div>
            </div>
        </div>

        <!-- TABS SWITCHER -->
        <ul class="nav nav-tabs nav-tabs-custom mb-3 gap-2" id="historyTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab == 'input' ? 'active' : '' }} d-flex align-items-center gap-2" 
                   href="{{ route('purchasing.history', ['tab' => 'input', 'search' => $searchQuery]) }}">
                    <i class="fa-solid fa-clipboard-list"></i>
                    Riwayat Hasil Input Incoming Bulanan ({{ $inputLogs->count() }})
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab == 'outstanding' ? 'active' : '' }} d-flex align-items-center gap-2" 
                   href="{{ route('purchasing.history', ['tab' => 'outstanding', 'search' => $searchQuery]) }}">
                    <i class="fa-solid fa-boxes-packing"></i>
                    Riwayat Data Outstanding Order Material ({{ $outstandings->count() }})
                </a>
            </li>
        </ul>

        <!-- TAB CONTENT 1: RIWAYAT HASIL INPUT INCOMING -->
        @if($activeTab == 'input')
            <div class="glass-card p-0 overflow-hidden">
                <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold brand-font text-info">
                        <i class="fa-solid fa-file-invoice me-2"></i> Daftar Riwayat Hasil Input Incoming Pembelian
                    </h5>
                    <a href="{{ route('purchasing.input') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                        + Tambah Input Incoming Baru
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">NO</th>
                                <th>ITEM CODE (PK)</th>
                                <th>NO. PO / REF (SK)</th>
                                <th>PERIODE</th>
                                <th>TANGGAL RECEIPT</th>
                                <th>DESKRIPSI</th>
                                <th>SUPPLIER</th>
                                <th class="text-end">TARGET PO</th>
                                <th class="text-end">AKTUAL MASUK</th>
                                <th class="text-end">SELISIH</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center" style="width: 140px;">AKSI</th>
                                <th class="text-center text-nowrap" style="background: rgba(59,130,246,0.15); color: #60a5fa;">KATEGORI PENGANTARAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inputLogs as $index => $log)
                                @php
                                    $diff = $log->actual_received - $log->target_order;
                                @endphp
                                <tr>
                                    <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2.5 py-1 font-monospace" style="font-size: 0.88rem;">
                                            <i class="fa-solid fa-tag me-1"></i>{{ $log->item_code }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-white font-monospace">
                                            <i class="fa-solid fa-file-invoice text-info me-1"></i>{{ $log->po_reference }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark border border-secondary text-info px-2.5 py-1 font-monospace">{{ $log->period_month }}</span>
                                    </td>
                                    <td>
                                        <div class="text-white"><i class="fa-regular fa-calendar-check text-success me-1"></i>{{ \Carbon\Carbon::parse($log->receipt_date)->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="text-muted small">{{ $log->description ?: '-' }}</td>
                                    <td>
                                        <span class="text-light small"><i class="fa-solid fa-building text-warning me-1"></i>{{ $log->supplier_name ?: '-' }}</span>
                                    </td>
                                    <td class="text-end fw-bold font-monospace">{{ number_format($log->target_order, 0, ',', '.') }} unit</td>
                                    <td class="text-end fw-bold font-monospace text-success">{{ number_format($log->actual_received, 0, ',', '.') }} unit</td>
                                    <td class="text-end fw-bold font-monospace {{ $diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-warning' : 'text-success') }}">
                                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($diff == 0)
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-1">PAS (100%)</span>
                                        @elseif($diff > 0)
                                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-2 py-1">SURPLUS (+{{ number_format($diff) }})</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-2 py-1">KURANG ({{ number_format($diff) }})</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-warning rounded-pill px-2.5 py-1 fw-bold"
                                                    onclick="openEditInputLogModal({{ $log->id }}, '{{ addslashes($log->item_code) }}', '{{ addslashes($log->po_reference) }}', '{{ $log->period_month }}', '{{ $log->receipt_date }}', {{ $log->target_order }}, {{ $log->actual_received }}, '{{ addslashes($log->supplier_name) }}', '{{ addslashes($log->description) }}', '{{ $log->delivery_category_code ?? 'LOC' }}')"
                                                    title="Edit Data Incoming">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                            </button>
                                            
                                            <!-- Approval Workflow Action Buttons -->
                                            @if($log->is_approved)
                                                <span class="badge bg-success text-dark fw-bold px-2 py-1 align-self-center"><i class="fa-solid fa-circle-check me-1"></i>Approved</span>
                                            @else
                                                 <form id="approveInputForm{{ $log->id }}" action="{{ route('purchasing.history.input.approve', $log->id) }}" method="POST" class="d-inline">
                                                     @csrf
                                                     <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" title="Approve Data" onclick="KawaiConfirm.ask('Verifikasi Penerimaan PO', 'Setujui laporan incoming PO {{ $log->po_reference }} ini?', () => document.getElementById('approveInputForm{{ $log->id }}').submit())">
                                                         <i class="fa-solid fa-check"></i>
                                                     </button>
                                                 </form>
                                                 <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1" title="Reject / Kembalikan Catatan" onclick="rejectInputLog({{ $log->id }}, '{{ addslashes($log->po_reference) }}')">
                                                     <i class="fa-solid fa-xmark"></i>
                                                 </button>
                                             @endif

                                             <form id="deleteInputHistoryForm{{ $log->id }}" action="{{ route('purchasing.history.input.destroy', $log->id) }}" method="POST" class="d-inline">
                                                 @csrf
                                                 @method('DELETE')
                                                 <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Hapus Riwayat" onclick="KawaiConfirm.delete('Hapus Riwayat Input', 'Riwayat input PO {{ $log->po_reference }} akan dihapus.', () => document.getElementById('deleteInputHistoryForm{{ $log->id }}').submit())">
                                                     <i class="fa-solid fa-trash-can"></i>
                                                 </button>
                                             </form>
                                        </div>
                                    </td>
                                    <td class="text-center text-nowrap">{!! $log->delivery_category_badge ?? '' !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-5 text-muted">
                                        Belum ada riwayat hasil input incoming pembelian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB CONTENT 2: RIWAYAT OUTSTANDING ORDER -->
        @if($activeTab == 'outstanding')
            <div class="glass-card p-0 overflow-hidden">
                <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold brand-font text-warning">
                        <i class="fa-solid fa-boxes-packing me-2"></i> Daftar Riwayat Data Outstanding Order Material
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('purchasing.history.export') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('purchasing.outstanding-po') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                            + Lihat di Step 4 Outstanding
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">NO</th>
                                <th>NO. PO (PURCHASE ORDER)</th>
                                <th>DRAWING NO</th>
                                <th>PART NUMBER</th>
                                <th>DESCRIPTION &amp; SUPPLIER</th>
                                <th class="text-end">ORDER QTY</th>
                                <th class="text-end">PRICE</th>
                                <th class="text-center text-info">CURRENCY</th>
                                <th class="text-end">AMOUNT</th>
                                <th>PROGRESS INCOMING (STEP 3)</th>
                                <th>STATUS &amp; WORKFLOW PO</th>
                                <th class="text-center" style="width: 240px;">AKSI (INCOMING / EDIT / HAPUS)</th>
                                <th class="text-center text-nowrap" style="background: rgba(59,130,246,0.15); color: #60a5fa;">KATEGORI PENGANTARAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandings as $index => $item)
                                <tr>
                                    <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-info font-monospace" style="font-size: 0.92rem;">
                                            <i class="fa-solid fa-file-invoice me-1"></i>{{ $item->po_number ?: 'PO-KI-202607' }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fa-regular fa-calendar me-1"></i>{{ $item->po_date ? \Carbon\Carbon::parse($item->po_date)->format('d/m/Y') : '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2 py-1 font-monospace"><i class="fa-solid fa-compass-drafting me-1"></i>{{ $item->drawing ?: '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-warning font-monospace"><i class="fa-solid fa-receipt me-1"></i>{{ $item->part_number }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-white">{{ $item->description }}</div>
                                        @if(!empty($item->supplier_name))
                                            <div class="text-muted small">
                                                <i class="fa-solid fa-truck me-1"></i>{{ $item->supplier_name }}
                                                @if($item->eta_date)
                                                    &bull; <span class="text-info">ETA: {{ \Carbon\Carbon::parse($item->eta_date)->format('d/m/Y') }}</span>
                                                @endif
                                            </div>
                                        @elseif($item->eta_date)
                                            <div class="text-muted small">
                                                <span class="text-info">ETA: {{ \Carbon\Carbon::parse($item->eta_date)->format('d/m/Y') }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold font-monospace">{{ number_format($item->display_order_qty, 0, ',', '.') }} unit</td>
                                    <td class="text-end font-monospace text-light">{{ number_format($item->display_price, 2, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ strtoupper($item->currency ?? 'USD') === 'IDR' ? 'bg-success bg-opacity-25 text-success border border-success' : 'bg-info bg-opacity-25 text-info border border-info' }} px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                            {{ strtoupper($item->currency ?? 'USD') }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold font-monospace text-warning">{{ $item->formatAmount($item->display_amount) }}</td>
                                    <td>
                                        <div class="d-flex justify-content-between small mb-1 font-monospace">
                                            <span class="fw-bold text-white">{{ number_format($item->display_complete, 0, ',', '.') }} / {{ number_format($item->display_order_qty, 0, ',', '.') }} unit</span>
                                            <span class="text-warning fw-bold">{{ $item->display_progress }}%</span>
                                        </div>
                                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.08);">
                                            <div class="progress-bar {{ $item->display_progress >= 100 ? 'bg-success' : 'bg-warning' }}" 
                                                 style="width: {{ min(100, $item->display_progress) }}%;"></div>
                                        </div>
                                        @if($item->display_complete < $item->display_order_qty && $item->display_complete > 0)
                                            <span class="badge bg-info text-dark fw-bold d-block mt-1" style="font-size: 0.68rem;" title="Penerimaan Parsial dari Step 3 Incoming">
                                                <i class="fa-solid fa-truck-ramp-box me-1"></i>On Progress ({{ number_format($item->display_complete, 0, ',', '.') }} unit masuk)
                                            </span>
                                        @elseif($item->display_complete >= $item->display_order_qty && $item->display_order_qty > 0)
                                            <span class="badge bg-success text-dark fw-bold d-block mt-1" style="font-size: 0.68rem;">
                                                <i class="fa-solid fa-check me-1"></i>Sesuai Target (Complete)
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark fw-bold d-block mt-1" style="font-size: 0.68rem;" title="Belum ada penerimaan incoming di Step 3">
                                                <i class="fa-solid fa-clock me-1"></i>Pending {{ number_format($item->display_order_qty, 0, ',', '.') }} unit
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->workflow_stage_badge }} rounded-pill px-3 py-1 mb-1 d-inline-block">
                                            {{ $item->workflow_stage_label }}
                                        </span>
                                        @if($item->approval_notes)
                                            <div class="text-muted small" style="font-size: 0.74rem;">
                                                <i class="fa-solid fa-circle-info me-1 text-info"></i>{{ $item->approval_notes }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1.5 flex-wrap">
                                            <!-- Tombol Alur Ke Incoming Data (Step 3) -->
                                            <a href="{{ route('purchasing.input') }}?search={{ urlencode($item->part_number ?: $item->po_number) }}" 
                                               class="btn btn-sm btn-success text-dark rounded-pill px-2.5 py-1 fw-bold" 
                                               title="Input / Tambah Incoming Penerimaan PO Ini di Step 3">
                                                <i class="fa-solid fa-truck-arrow-right me-1"></i> Incoming
                                            </a>

                                            <!-- Tombol Edit Lengkap Outstanding -->
                                            <button type="button" class="btn btn-sm btn-warning rounded-pill px-2.5 py-1 fw-bold"
                                                    onclick="openEditOutstandingModal(
                                                        {{ $item->id }},
                                                        '{{ addslashes($item->po_number) }}',
                                                        '{{ $item->po_date }}',
                                                        '{{ addslashes($item->part_number) }}',
                                                        '{{ addslashes($item->drawing) }}',
                                                        '{{ addslashes($item->description) }}',
                                                        '{{ addslashes($item->supplier_name) }}',
                                                        '{{ $item->eta_date }}',
                                                        {{ $item->display_order_qty }},
                                                        {{ $item->display_price }},
                                                        {{ $item->display_complete }},
                                                        '{{ $item->delivery_category_code ?? 'LOC' }}'
                                                    )">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <form id="deleteOutHistoryForm{{ $item->id }}" action="{{ route('purchasing.history.outstanding.destroy', $item->id) }}" method="POST" class="d-inline mb-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Hapus Data" onclick="KawaiConfirm.delete('Hapus Histori Outstanding', 'Histori part {{ $item->part_number }} akan dihapus.', () => document.getElementById('deleteOutHistoryForm{{ $item->id }}').submit())">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="text-center text-nowrap">{!! $item->delivery_category_badge ?? '' !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        Belum ada riwayat data Outstanding Order.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

    <!-- MODAL UPDATE HASIL INPUT INCOMING -->
    <div class="modal fade" id="modalEditInputLog" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-dark p-3">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold brand-font text-info">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Update Riwayat Hasil Input Incoming
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditInputLog" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Nomor Referensi PO</label>
                            <input type="text" id="edit_input_po" name="po_reference" class="form-control form-control-dark font-monospace" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Periode Bulan (YYYY-MM)</label>
                            <input type="text" id="edit_input_period" name="period_month" class="form-control form-control-dark font-monospace" placeholder="2026-07" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Kategori Material</label>
                            <select id="edit_input_category" name="purchasing_category_id" class="form-select form-select-dark" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_code }} - {{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Target Order (Unit) <span class="text-danger">*</span></label>
                            <input type="number" id="edit_input_target" name="target_order" class="form-control form-control-dark" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-warning fw-bold small">Aktual Diterima (Unit) <span class="text-danger">*</span></label>
                            <input type="number" id="edit_input_received" name="actual_received" class="form-control form-control-dark fs-5 fw-bold text-warning" min="0" required>
                        </div>
                        <div id="edit_input_warning" class="alert alert-warning bg-warning bg-opacity-25 text-warning border border-warning rounded-3 mb-1 d-none col-12 p-2" style="font-size: 0.82rem;"></div>
                        <div class="col-md-6">
                            <label class="form-label text-warning fw-bold small">Status Verifikasi Penerimaan <span class="text-danger">*</span></label>
                            <select id="edit_input_verification_status" name="verification_status" class="form-select form-select-dark" required>
                                <option value="pending">⏳ Menunggu Approval (Staff Entry)</option>
                                <option value="approved">✅ Disetujui Diterima (Verified by Leader/Supervisor)</option>
                                <option value="conditional">⚠️ Diterima Bersyarat / Catatan Khusus</option>
                                <option value="rejected">❌ Ditolak / Perlu Revisi Surat Jalan</option>
                            </select>
                            @if((auth()->user()->role ?? 'staff') === 'staff')
                                <div class="small text-warning mt-1" style="font-size:0.75rem;">
                                    <i class="fa-solid fa-lock me-1"></i> Role Staff hanya dapat mengajukan status <strong>Menunggu Approval</strong>. Persetujuan status <strong>Disetujui Diterima</strong> memerlukan otorisasi Supervisor / Leader.
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Catatan Status / Keterangan Tambahan</label>
                            <input type="text" id="edit_input_note" name="status_note" class="form-control form-control-dark" placeholder="e.g. Diterima lengkap di Gudang KIIC">
                        </div>
                    </div>
                    <div class="modal-footer border-secondary mt-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL UPDATE HASIL OUTSTANDING ORDER LENGKAP -->
    <div class="modal fade" id="modalEditOutstanding" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-dark p-3">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold brand-font text-warning">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Update Data Outstanding Order Material
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditOutstanding" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-info fw-bold small">Nomor PO</label>
                            <input type="text" id="edit_out_po_num" name="po_number" class="form-control form-control-dark font-monospace">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-info fw-bold small">Tanggal PO</label>
                            <input type="date" id="edit_out_po_date" name="po_date" class="form-control form-control-dark">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold">1. Item Code (Drawing) <span class="badge bg-primary bg-opacity-25 text-primary ms-1">PRIMARY KEY</span></label>
                            <input type="text" id="edit_out_drawing" name="drawing" class="form-control form-control-dark border-primary font-monospace text-white fw-bold">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold">2. No. PO / Ref <span class="badge bg-success bg-opacity-25 text-success ms-1">SECONDARY KEY</span> <span class="text-danger">*</span></label>
                            <input type="text" id="edit_out_part" name="part_number" class="form-control form-control-dark border-success font-monospace fw-bold text-warning" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Description (Nama Part/Material) <span class="text-danger">*</span></label>
                            <input type="text" id="edit_out_desc" name="description" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Nama Supplier</label>
                            <input type="text" id="edit_out_supplier" name="supplier_name" class="form-control form-control-dark">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">ETA Date</label>
                            <input type="date" id="edit_out_eta" name="eta_date" class="form-control form-control-dark">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Order Qty (Unit) <span class="text-danger">*</span></label>
                            <input type="number" id="edit_out_order_qty" name="order_qty" class="form-control form-control-dark" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Price Satuan (IDR) <span class="text-danger">*</span></label>
                            <input type="number" id="edit_out_price" name="price" class="form-control form-control-dark" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-warning fw-bold small">Complete Qty (Selesai) <span class="text-danger">*</span></label>
                            <input type="number" id="edit_out_complete" name="complete" class="form-control form-control-dark fs-5 fw-bold text-warning" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-info small fw-bold"><i class="bi bi-truck me-1"></i> Kategori Pengantaran</label>
                            <select id="edit_out_delivery_category" name="delivery_category_code" class="form-select form-select-dark text-white fw-bold">
                                @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                                    <option value="{{ $dc->code }}">
                                        {{ $dc->code }} - {{ $dc->name }} ({{ $dc->currency }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="edit_out_warning" class="alert alert-warning bg-warning bg-opacity-25 text-warning border border-warning rounded-3 mb-1 d-none col-12 p-2" style="font-size: 0.82rem;"></div>
                    </div>
                    <div class="modal-footer border-secondary mt-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function promptInputReject(formEl, poRef) {
            const reason = prompt(`Masukkan alasan penolakan yang harus direvisi oleh Staff pada riwayat input ${poRef}:`, "Perlu revisi format/angka incoming");
            if (reason === null) return false;
            const reasonInput = formEl.querySelector('.input-reject-reason');
            if (reasonInput) reasonInput.value = reason || "Perlu revisi format input";
            return true;
        }

        function checkEditInputDiscrepancy() {
            const tEl = document.getElementById('edit_input_target');
            const rEl = document.getElementById('edit_input_received');
            const wEl = document.getElementById('edit_input_warning');
            if (!tEl || !rEl || !wEl) return;
            const target = parseInt(tEl.value, 10) || 0;
            const actual = parseInt(rEl.value, 10) || 0;
            if (actual < target) {
                const diff = target - actual;
                wEl.className = 'alert alert-danger bg-danger bg-opacity-25 text-danger border border-danger rounded-3 mb-1 col-12 p-2';
                wEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Under-Incoming:</strong> Diterima (${actual.toLocaleString('id-ID')}) kurang dari Target PO (${target.toLocaleString('id-ID')}). Kekurangan ${diff.toLocaleString('id-ID')} unit.`;
                wEl.classList.remove('d-none');
            } else if (actual > target) {
                const diff = actual - target;
                wEl.className = 'alert alert-warning bg-warning bg-opacity-25 text-warning border border-warning rounded-3 mb-1 col-12 p-2';
                wEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Over-Incoming:</strong> Diterima (${actual.toLocaleString('id-ID')}) melebihi Target PO (${target.toLocaleString('id-ID')}). Surplus +${diff.toLocaleString('id-ID')} unit.`;
                wEl.classList.remove('d-none');
            } else if (target > 0 && actual === target) {
                wEl.className = 'alert alert-success bg-success bg-opacity-25 text-success border border-success rounded-3 mb-1 col-12 p-2';
                wEl.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Sesuai Target PO (100%).`;
                wEl.classList.remove('d-none');
            } else {
                wEl.classList.add('d-none');
            }
        }

        function checkEditOutDiscrepancy() {
            const oEl = document.getElementById('edit_out_order_qty');
            const cEl = document.getElementById('edit_out_complete');
            const wEl = document.getElementById('edit_out_warning');
            if (!oEl || !cEl || !wEl) return;
            const orderQty = parseInt(oEl.value, 10) || 0;
            const complete = parseInt(cEl.value, 10) || 0;
            if (complete < orderQty) {
                const diff = orderQty - complete;
                wEl.className = 'alert alert-warning bg-warning bg-opacity-25 text-warning border border-warning rounded-3 mb-1 col-12 p-2';
                wEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Pending Qty:</strong> Complete (${complete.toLocaleString('id-ID')}) belum memenuhi Target Order PO (${orderQty.toLocaleString('id-ID')}). Sisa ${diff.toLocaleString('id-ID')} unit.`;
                wEl.classList.remove('d-none');
            } else if (orderQty > 0 && complete >= orderQty) {
                wEl.className = 'alert alert-success bg-success bg-opacity-25 text-success border border-success rounded-3 mb-1 col-12 p-2';
                wEl.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Sesuai Target PO / Complete.`;
                wEl.classList.remove('d-none');
            } else {
                wEl.classList.add('d-none');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tEl = document.getElementById('edit_input_target');
            const rEl = document.getElementById('edit_input_received');
            if (tEl && rEl) {
                tEl.addEventListener('input', checkEditInputDiscrepancy);
                rEl.addEventListener('input', checkEditInputDiscrepancy);
            }
            const oEl = document.getElementById('edit_out_order_qty');
            const cEl = document.getElementById('edit_out_complete');
            if (oEl && cEl) {
                oEl.addEventListener('input', checkEditOutDiscrepancy);
                cEl.addEventListener('input', checkEditOutDiscrepancy);
            }
        });

        function openEditInputModal(id, categoryId, poRef, periodMonth, targetOrder, actualReceived, statusNote) {
            const modal = new bootstrap.Modal(document.getElementById('modalEditInputLog'));
            document.getElementById('formEditInputLog').action = '/purchasing/history/input/' + id;
            document.getElementById('edit_input_category').value = categoryId;
            document.getElementById('edit_input_po').value = poRef;
            document.getElementById('edit_input_period').value = periodMonth;
            document.getElementById('edit_input_target').value = targetOrder;
            document.getElementById('edit_input_received').value = actualReceived;

            const statusSelect = document.getElementById('edit_input_verification_status');
            let cleanNote = statusNote || '';
            if (/^✅ Disetujui/i.test(statusNote) || /^Lolos IAD/i.test(statusNote)) {
                if (statusSelect) statusSelect.value = 'approved';
                cleanNote = cleanNote.replace(/^✅ Disetujui[^-\)]*[-\)]*\s*/i, '').replace(/^(\-\s*|\:\s*)/, '');
            } else if (/^❌ Ditolak/i.test(statusNote)) {
                if (statusSelect) statusSelect.value = 'rejected';
                cleanNote = cleanNote.replace(/^❌ Ditolak[^-\)]*[-\)]*\s*/i, '').replace(/^(\-\s*|\:\s*)/, '');
            } else if (/^⚠️ Diterima Bersyarat/i.test(statusNote)) {
                if (statusSelect) statusSelect.value = 'conditional';
                cleanNote = cleanNote.replace(/^⚠️ Diterima Bersyarat[^-\)]*[-\)]*\s*/i, '').replace(/^(\-\s*|\:\s*)/, '');
            } else {
                if (statusSelect) statusSelect.value = 'pending';
                cleanNote = cleanNote.replace(/^⏳ Menunggu[^-\)]*[-\)]*\s*/i, '').replace(/^(\-\s*|\:\s*)/, '');
            }
            document.getElementById('edit_input_note').value = cleanNote.trim();

            checkEditInputDiscrepancy();
            modal.show();
        }

        function openEditOutstandingModal(id, poNumber, poDate, partNumber, drawing, description, supplier, etaDate, orderQty, price, completeQty, deliveryCategoryCode) {
            const modal = new bootstrap.Modal(document.getElementById('modalEditOutstanding'));
            document.getElementById('formEditOutstanding').action = '/purchasing/history/outstanding/' + id;
            document.getElementById('edit_out_po_num').value = poNumber || '';
            document.getElementById('edit_out_po_date').value = poDate || '';
            document.getElementById('edit_out_part').value = partNumber;
            document.getElementById('edit_out_drawing').value = drawing;
            document.getElementById('edit_out_desc').value = description;
            document.getElementById('edit_out_supplier').value = supplier;
            document.getElementById('edit_out_eta').value = etaDate || '';
            document.getElementById('edit_out_order_qty').value = orderQty;
            document.getElementById('edit_out_price').value = price;
            document.getElementById('edit_out_complete').value = completeQty;
            if (document.getElementById('edit_out_delivery_category')) {
                document.getElementById('edit_out_delivery_category').value = deliveryCategoryCode || 'LOC';
            }
            checkEditOutDiscrepancy();
            modal.show();
        }
    </script>
    <script>
    // Live Clock
    (function() {
        function updateClock() {
            var el = document.getElementById('live-clock');
            if (el) {
                var now = new Date();
                el.textContent = now.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
            }
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

