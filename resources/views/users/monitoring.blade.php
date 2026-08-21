<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Performa Staff & Analytics | PT Kawai Indonesia</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-dark: #0A0E1A;
            --card-bg: rgba(18, 24, 38, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --accent-emerald: #10B981;
            --accent-gold: #F59E0B;
            --accent-blue: #3B82F6;
            --accent-red: #EF4444;
            --accent-cyan: #06B6D4;
            --text-main: #F3F4F6;
            --text-muted: #9CA3AF;
        }

        body {
            background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-dark) 60%);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding-bottom: 3rem;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: 'Outfit', sans-serif;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            padding: 1.5rem;
        }

        .metric-card {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 1.25rem;
            transition: all 0.25s ease;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            border-color: rgba(245, 158, 11, 0.4);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-custom th {
            background: rgba(15, 23, 42, 0.95);
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            white-space: nowrap;
            vertical-align: middle;
        }

        .table-custom td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
        }

        .table-custom tr:hover td {
            background: rgba(255, 255, 255, 0.03);
        }

        .search-input {
            background-color: rgba(15, 23, 42, 0.8) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 10px;
            padding: 0.5rem 0.85rem;
            font-size: 0.88rem;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
</head>
<body>

    <!-- TOP NAVBAR HEADER -->
    <nav class="top-navbar">
        <div class="top-navbar-row1 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
            <a href="{{ route('dashboard.overview') }}" class="text-decoration-none" style="color: inherit !important; text-decoration: none !important;">
                <div class="d-flex align-items-center gap-2 mb-0.5">
                    <i class="bi bi-music-note-beamed text-warning fs-4" style="line-height:1; vertical-align:middle;"></i>
                    <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
                </div>
                <div class="text-muted" style="font-size:0.72rem; margin-left:2px; color:#9ca3af !important;">Monitoring Performa Staff &amp; Inter-Division Analytics</div>
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
            @include('partials.pill-nav', ['activeRoute' => 'users.monitoring', 'hasFaqModal' => true])
        </div>
    </nav>

    @include('partials.faq-modal')


    <!-- MAIN CONTAINER -->
    <div class="container-fluid px-4">

        <!-- ALERTS -->
        @if(session('success'))
            <div class="alert alert-success bg-success bg-opacity-25 text-white border border-success border-opacity-50 rounded-3 mb-4 py-2.5 px-3 small d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>{!! session('success') !!}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger bg-danger bg-opacity-25 text-white border border-danger border-opacity-50 rounded-3 mb-4 py-2.5 px-3 small d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- HERO BANNER HEADER -->
        <div class="exchange-hero mb-4">
            <div class="row align-items-center">
                <div class="col-12 col-md-7">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:rgba(6,182,212,0.18);border:1px solid rgba(6,182,212,0.45);color:#06b6d4;">
                            <i class="bi bi-graph-up-arrow fs-3"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="hero-rate-label">TEAM MONITORING &amp; ANALYTICS</span>
                                <span class="badge rounded-pill bg-info text-dark fw-bold" style="font-size:0.68rem; padding: 2px 8px;">REAL-TIME EVALUATION</span>
                            </div>
                            <h2 class="fw-bold text-white mb-0 brand-font" style="font-size:1.65rem;">Monitoring Performa Staff &amp; Tren Produksi PO</h2>
                        </div>
                    </div>
                    <p class="text-muted mb-0 mt-2" style="font-size:0.88rem;">
                        Fitur evaluasi hasil pengisian data antar user, tren kenaikan/penurunan produksi PO, serta catatan koordinasi antar divisi Purchasing.
                    </p>
                </div>
                <div class="col-12 col-md-5 mt-3 mt-md-0">
                    <div class="d-flex gap-3 justify-content-md-end">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-warning fw-bold px-4 py-2 d-flex align-items-center gap-2 rounded-pill" style="font-size:0.88rem;">
                            <i class="bi bi-people-fill"></i> Kelola User &amp; Hak Akses
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- METRIC CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="metric-card h-100 p-3.5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Staff Aktif Penginput</span>
                            <h3 class="fw-bold text-white mt-1 mb-0 font-monospace">{{ number_format($activeStaffCount) }} <span class="fs-6 fw-normal text-muted">User</span></h3>
                        </div>
                        <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.35); color: #06b6d4;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="text-muted small pt-2 border-top border-secondary border-opacity-25" style="font-size: 0.78rem;">
                        <i class="bi bi-person-check-fill text-info me-1"></i> Dari total <strong class="text-white">{{ count($staffAnalytics) }}</strong> pengguna terdaftar
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="metric-card h-100 p-3.5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Entri Transaksi</span>
                            <h3 class="fw-bold text-warning mt-1 mb-0 font-monospace">{{ number_format($totalLogsCount) }} <span class="fs-6 fw-normal text-muted">Baris</span></h3>
                        </div>
                        <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #f59e0b;">
                            <i class="bi bi-journal-text fs-4"></i>
                        </div>
                    </div>
                    <div class="text-muted small pt-2 border-top border-secondary border-opacity-25" style="font-size: 0.78rem;">
                        <i class="bi bi-clock-history text-warning me-1"></i> Incoming penerimaan yang di-input
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="metric-card h-100 p-3.5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Material Diterima</span>
                            <h3 class="fw-bold text-success mt-1 mb-0 font-monospace">{{ number_format($totalReceivedQtyOverall, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">Unit</span></h3>
                        </div>
                        <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #10b981;">
                            <i class="bi bi-box-seam-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="text-muted small pt-2 border-top border-secondary border-opacity-25" style="font-size: 0.78rem;">
                        <i class="bi bi-box-arrow-in-down text-success me-1"></i> Akumulasi penerimaan seluruh buyer
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="metric-card h-100 p-3.5">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Periode Analisis</span>
                            <h3 class="fw-bold text-primary mt-1 mb-0 font-monospace">{{ date('M Y', strtotime($selectedPeriod . '-01')) }}</h3>
                        </div>
                        <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.35); color: #3b82f6;">
                            <i class="bi bi-calendar3 fs-4"></i>
                        </div>
                    </div>
                    <div class="text-muted small pt-2 border-top border-secondary border-opacity-25" style="font-size: 0.78rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <i class="bi bi-arrow-left-right text-primary me-1"></i> Dibandingkan bulan sebelumnya (MoM)
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER BAR -->
        <div class="glass-card mb-4 p-3.5">
            <form method="GET" action="{{ route('users.monitoring') }}" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control search-input" placeholder="Cari nama staff, username, atau email..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-calendar-month"></i></span>
                        <input type="month" name="period" class="form-control search-input" value="{{ $selectedPeriod }}" onchange="this.form.submit()">
                    </div>
                </div>
                <div class="col-md-5 d-flex gap-2 justify-content-md-end">
                    <button type="submit" class="btn btn-info text-dark rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-funnel-fill"></i> Terapkan Filter
                    </button>
                    @if($search || $selectedPeriod !== date('Y-m'))
                        <a href="{{ route('users.monitoring') }}" class="btn btn-outline-secondary rounded-pill px-3.5 d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- ANALYTICS TABLE CONTAINER -->
        <div class="glass-card mb-4 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold text-white mb-0 brand-font">
                    <i class="bi bi-bar-chart-line-fill text-warning me-2"></i>Tabel Rekapitulasi Performa &amp; Tren Produksi PO Per Staff
                </h5>
                <span class="badge bg-dark border border-secondary text-info font-monospace">
                    {{ count($staffAnalytics) }} User Teranalisis
                </span>
            </div>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th style="min-width: 180px;">Nama Staff / Buyer</th>
                            <th style="min-width: 150px;">Role &amp; Departemen</th>
                            <th class="text-center" style="min-width: 140px;">Akses Monitoring</th>
                            <th class="text-end" style="min-width: 130px;">Total Entri (Baris)</th>
                            <th class="text-end" style="min-width: 150px;">Aktual Diterima (Unit)</th>
                            <th class="text-center" style="min-width: 180px;">Tren Produksi PO (MoM)</th>
                            <th class="text-center" style="min-width: 160px;">Under / Over Incoming</th>
                            <th style="min-width: 220px;">Catatan Komunikasi Inter-Divisi</th>
                            <th class="text-center" style="min-width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffAnalytics as $idx => $st)
                            @php
                                $u = $st['user'];
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-white fs-7">{{ $u->name }}</div>
                                    <div class="fs-8 text-warning font-monospace"><i class="bi bi-person-badge me-1"></i>{{ $u->username ?: '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-warning px-2.5 py-1 text-uppercase fw-bold" style="font-size:0.7rem;">
                                        {{ $u->role }}
                                    </span>
                                    <div class="fs-8 text-white-50 mt-1">{{ $u->department ?: 'Purchasing' }}</div>
                                </td>
                                <td class="text-center">
                                    @if($u->canViewUserMonitoring())
                                        <span class="badge bg-success text-dark fw-bold border border-success px-2.5 py-1 font-monospace" style="font-size:0.7rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i>Akses Diberikan
                                        </span>
                                    @else
                                        <span class="badge bg-dark border border-secondary text-white-50 px-2.5 py-1 font-monospace" style="font-size:0.7rem;">
                                            <i class="bi bi-shield-fill me-1"></i>Normal
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace fw-bold text-white">
                                    {{ number_format($st['total_logs']) }}
                                </td>
                                <td class="text-end font-monospace fw-bold text-emerald" style="color:#34d399;">
                                    {{ number_format($st['total_received'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($st['prod_diff'] > 0)
                                        <span class="badge bg-success text-dark px-2.5 py-1.5 rounded-pill fw-bold shadow-sm" style="font-size:0.75rem; background-color:#10b981 !important; color:#0a0e17 !important;">
                                            <i class="bi bi-graph-up-arrow me-1"></i> +{{ number_format($st['prod_diff'], 0, ',', '.') }} unit (+{{ $st['prod_trend_pct'] }}% Kenaikan PO)
                                        </span>
                                    @elseif($st['prod_diff'] < 0)
                                        <span class="badge bg-danger text-white px-2.5 py-1.5 rounded-pill fw-bold shadow-sm" style="font-size:0.75rem;">
                                            <i class="bi bi-graph-down-arrow me-1"></i> {{ number_format($st['prod_diff'], 0, ',', '.') }} unit ({{ $st['prod_trend_pct'] }}% Penurunan PO)
                                        </span>
                                    @else
                                        <span class="badge bg-dark border border-secondary text-white-50 px-2.5 py-1.5 rounded-pill" style="font-size:0.75rem;">
                                            <i class="bi bi-dash-lg me-1"></i> Stabil / N/A
                                        </span>
                                    @endif
                                    <div class="fs-8 text-white-50 mt-1 font-monospace">
                                        {{ date('M Y', strtotime($selectedPeriod . '-01')) }}: <strong class="text-white">{{ number_format($st['current_month_prod']) }}</strong> vs {{ date('M Y', strtotime('-1 month', strtotime($selectedPeriod . '-01'))) }}: <span class="text-white-50">{{ number_format($st['last_month_prod']) }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($st['under_delivery_count'] > 0)
                                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill me-1" title="Kurang muatan">
                                            <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $st['under_delivery_count'] }} Under
                                        </span>
                                    @endif
                                    @if($st['over_delivery_count'] > 0)
                                        <span class="badge bg-danger text-white fw-bold px-2.5 py-1 rounded-pill" title="Kelebihan muatan">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>{{ $st['over_delivery_count'] }} Over
                                        </span>
                                    @endif
                                    @if($st['under_delivery_count'] == 0 && $st['over_delivery_count'] == 0)
                                        <span class="badge bg-success text-dark fw-bold px-2.5 py-1 rounded-pill" style="background-color:#10b981 !important; color:#0a0e17 !important;">
                                            <i class="bi bi-check-all me-1"></i> Sesuai Target
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($u->admin_note))
                                        <div class="p-2 rounded bg-dark border border-warning border-opacity-40 text-warning small font-monospace">
                                            <i class="bi bi-chat-left-text-fill me-1 text-warning"></i>{{ $u->admin_note }}
                                        </div>
                                    @else
                                        <span class="text-white-50 fs-8 font-italic"><i class="bi bi-chat-left me-1"></i>Belum ada catatan evaluasi</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('users.inspect', $u->id) }}" class="btn btn-xs rounded-pill px-2.5 py-1 fw-bold shadow-sm" style="background:#06b6d4; color:#0a0e17 !important;" title="Monitoring Alur & Hasil Dashboard User Ini">
                                            <i class="bi bi-display me-1"></i> Dashboard
                                        </a>
                                        @if(Auth::user() && Auth::user()->isAdmin())
                                            <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2.5 py-1 fw-semibold" onclick="openNoteModal({{ json_encode($u) }})" title="Edit Catatan Evaluasi Admin">
                                                <i class="bi bi-pencil me-1"></i> Note
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada data pengisian staff yang dapat dianalisis.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL EDIT NOTE EVALUASI ADMIN -->
    @if(Auth::user() && Auth::user()->isAdmin())
        <div class="modal fade" id="modalEditNote" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-card border-secondary text-white" style="background: #111827; border: 1px solid rgba(255,255,255,0.15);">
                    <div class="modal-header border-bottom border-secondary">
                        <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                            <i class="bi bi-chat-right-quote-fill text-warning"></i>
                            <span>Catatan Evaluasi Inter-Divisi</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="noteForm" method="POST">
                        @csrf
                        <div class="modal-body py-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">Nama Staff / Buyer</label>
                                <input type="text" id="noteUserName" class="form-control search-input" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">Catatan Evaluasi / Pesan Koordinasi Inter-Divisi</label>
                                <textarea name="admin_note" id="noteAdminText" class="form-control search-input" rows="4" placeholder="Tuliskan catatan evaluasi misal: Perlu dorong vendor terkait penurunan prod PO, atau koordinasikan kendala under-incoming..."></textarea>
                                <div class="form-text fs-8 text-muted">Catatan ini dapat dibaca oleh Admin &amp; Staff terkait untuk mempermudah komunikasi tim.</div>
                            </div>
                        </div>
                        <div class="modal-footer border-top border-secondary">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Simpan Catatan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function openNoteModal(user) {
            const form = document.getElementById('noteForm');
            if (form) {
                form.action = '/users/' + user.id + '/note';
                document.getElementById('noteUserName').value = user.name + ' (' + (user.username || '-') + ')';
                document.getElementById('noteAdminText').value = user.admin_note || '';

                const modal = new bootstrap.Modal(document.getElementById('modalEditNote'));
                modal.show();
            }
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
</body>
</html>
