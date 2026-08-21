<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management & Privileges | PT Kawai Indonesia</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS CDN -->
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
            --text-main: #F3F4F6;
            --text-muted: #9CA3AF;
        }

        * { box-sizing: border-box; }

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

        .filter-select, .search-input {
            background-color: rgba(15, 23, 42, 0.8) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 10px;
            padding: 0.5rem 0.85rem;
            font-size: 0.88rem;
        }

        .filter-select:focus, .search-input:focus {
            border-color: var(--accent-gold) !important;
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25) !important;
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
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table-custom td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
        }

        .table-custom tr:hover td {
            background: rgba(255, 255, 255, 0.03);
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
                <div class="text-muted" style="font-size:0.72rem; margin-left:2px; color:#9ca3af !important;">User Management &amp; Privileges Control</div>
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
            @include('partials.pill-nav', ['activeRoute' => 'users.index', 'hasFaqModal' => true])
        </div>
    </nav>

    @include('partials.faq-modal')


    <!-- MAIN CONTENT CONTAINER -->
    <div class="container-fluid px-4">

        <!-- HERO BANNER HEADER -->
        <div class="exchange-hero mb-4">
            <div class="row align-items-center">
                <div class="col-12 col-md-7">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:rgba(226,179,74,0.18);border:1px solid rgba(226,179,74,0.45);color:#e2b34a;">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="hero-rate-label">USER ADMINISTRATION &amp; ACCESS CONTROL</span>
                                <span class="badge rounded-pill bg-warning text-dark fw-bold" style="font-size:0.68rem; padding: 2px 8px;">ADMIN ONLY</span>
                            </div>
                            <h2 class="fw-bold text-white mb-0 brand-font" style="font-size:1.65rem;">User Management &amp; Privileges Control</h2>
                        </div>
                    </div>
                    <p class="text-muted mb-0 mt-2" style="font-size:0.88rem;">
                        Modul khusus Admin untuk mengelola akun pengguna, User Group (Role), dan rincian Hak Akses (Privileges).
                    </p>
                </div>
                <div class="col-12 col-md-5 mt-3 mt-md-0">
                    <div class="d-flex gap-3 justify-content-md-end flex-wrap">
                        <a href="{{ route('users.monitoring') }}" class="btn btn-outline-info fw-bold px-4 py-2 d-flex align-items-center gap-2 rounded-pill" style="font-size:0.88rem;">
                            <i class="bi bi-graph-up-arrow"></i> Monitoring Tim
                        </a>
                        <button type="button" class="btn btn-warning fw-bold px-4 py-2 d-flex align-items-center gap-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalAddUser" style="font-size:0.88rem;">
                            <i class="bi bi-person-plus-fill"></i> Tambah User Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ALERTS -->
        @if(session('success'))
            <div class="alert alert-success bg-success bg-opacity-25 text-white border border-success border-opacity-50 rounded-3 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div>{!! session('success') !!}</div>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger bg-danger bg-opacity-25 text-white border border-danger border-opacity-50 rounded-3 mb-4">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat kesalahan:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPI METRICS CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="glass-card p-3 h-100" style="border-left: 4px solid var(--accent-blue);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total User Terdaftar</span>
                            <h3 class="fw-bold text-white mb-0 mt-1">{{ number_format($totalUsers) }}</h3>
                        </div>
                        <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle">
                            <i class="bi bi-people fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-3 h-100" style="border-left: 4px solid var(--accent-gold);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">User Group: Admin</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1">{{ number_format($adminCount) }}</h3>
                        </div>
                        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-3 h-100" style="border-left: 4px solid var(--accent-red);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">User Group: Supervisor</span>
                            <h3 class="fw-bold text-danger mb-0 mt-1">{{ number_format($supervisorCount) }}</h3>
                        </div>
                        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle">
                            <i class="bi bi-person-badge fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-3 h-100" style="border-left: 4px solid var(--accent-emerald);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Staff &amp; Leader</span>
                            <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($staffCount) }}</h3>
                        </div>
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                            <i class="bi bi-person-workspace fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER & SEARCH BAR -->
        <div class="glass-card mb-4 p-3">
            <form method="GET" action="{{ route('users.index') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-warning fs-5"></i>
                    <h6 class="fw-bold text-white mb-0">Filter Data User</h6>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted small fw-bold mb-0">Role Group:</label>
                        <select name="role" class="filter-select" onchange="this.form.submit()">
                            <option value="ALL" {{ ($roleFilter ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua Role --</option>
                            <option value="admin" {{ ($roleFilter ?? '') === 'admin' ? 'selected' : '' }}>Admin (Administrator)</option>
                            <option value="supervisor" {{ ($roleFilter ?? '') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="leader" {{ ($roleFilter ?? '') === 'leader' ? 'selected' : '' }}>Leader</option>
                            <option value="staff" {{ ($roleFilter ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="viewer" {{ ($roleFilter ?? '') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                        </select>
                    </div>
                    <div style="min-width: 250px;">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control search-input" placeholder="Cari Username / Nama..." value="{{ $search }}">
                        </div>
                    </div>
                    @if(($roleFilter ?? 'ALL') !== 'ALL' || !empty($search))
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i> Reset Filter
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- USERS TABLE CONTAINER -->
        <div class="glass-card mb-4 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0">
                    <i class="bi bi-table text-info me-2"></i>Daftar Pengguna &amp; Hak Akses
                </h5>
                <span class="badge bg-dark border border-secondary text-muted font-monospace">{{ $users->count() }} User Terdaftar</span>
            </div>

            <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                <table class="table-custom">
                    <thead class="sticky-top">
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th>User Name</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>User Group (Role)</th>
                            <th>Hak Akses (Privileges)</th>
                            <th class="text-center" style="width: 170px;">Izin Monitoring User Lain</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $idx => $u)
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="badge rounded-pill px-2.5 py-1.5 font-monospace fs-7 fw-bold" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24 !important; border: 1px solid rgba(245, 158, 11, 0.4);">
                                        <i class="bi bi-person-fill me-1"></i>{{ $u->username ?: '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-white fs-7">{{ $u->name }}</div>
                                    <div class="fs-8 font-monospace mt-0.5" style="color: #38bdf8 !important;"><i class="bi bi-building me-1 opacity-75"></i>{{ $u->department ?: '(Tanpa Departemen)' }}</div>
                                </td>
                                <td class="fs-7 font-monospace" style="color: #cbd5e1 !important;">{{ $u->email }}</td>
                                <td>
                                    @if($u->isAdmin())
                                        <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24 !important; border: 1px solid rgba(245, 158, 11, 0.45);">
                                            <i class="bi bi-shield-lock-fill me-1"></i> Administrator
                                        </span>
                                    @elseif($u->role === 'supervisor')
                                        <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background: rgba(239, 68, 68, 0.2); color: #f87171 !important; border: 1px solid rgba(239, 68, 68, 0.45);">
                                            <i class="bi bi-person-badge-fill me-1"></i> Supervisor
                                        </span>
                                    @elseif($u->role === 'leader')
                                        <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.45);">
                                            <i class="bi bi-person-gear me-1"></i> Leader
                                        </span>
                                    @elseif($u->role === 'staff')
                                        <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background: rgba(16, 185, 129, 0.2); color: #34d399 !important; border: 1px solid rgba(16, 185, 129, 0.45);">
                                            <i class="bi bi-person-workspace me-1"></i> Staff Buyer
                                        </span>
                                    @else
                                        <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background: rgba(148, 163, 184, 0.2); color: #e2e8f0 !important; border: 1px solid rgba(148, 163, 184, 0.45);">
                                            Viewer
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($u->isAdmin())
                                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm" style="font-size: 0.78rem;">
                                            <i class="bi bi-shield-lock-fill me-1"></i> Full Access (Semua Akses Admin)
                                        </span>
                                    @else
                                        @php
                                            $perms = $u->permissions ?? [];
                                        @endphp
                                        @if(empty($perms))
                                            <span class="badge rounded-pill px-2.5 py-1 fw-medium" style="font-size: 0.75rem; background: rgba(148, 163, 184, 0.15); color: #cbd5e1 !important; border: 1px solid rgba(148, 163, 184, 0.3);">
                                                <i class="bi bi-shield-lock me-1 opacity-75"></i>Akses Standar
                                            </span>
                                        @else
                                            <div class="d-flex flex-wrap gap-1.5">
                                                @foreach($perms as $pKey)
                                                    @if(isset($allPermissions[$pKey]))
                                                        <span class="badge rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center shadow-sm" style="font-size: 0.76rem; background: rgba(14, 165, 233, 0.18); color: #e0f2fe !important; border: 1px solid rgba(56, 189, 248, 0.45); font-family: 'Inter', sans-serif;">
                                                            <i class="bi bi-shield-check me-1.5" style="color: #38bdf8; font-size: 0.8rem;"></i>{{ $allPermissions[$pKey] }}
                                                        </span>
                                                    @elseif($pKey === '*')
                                                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1.5 fw-bold shadow-sm" style="font-size: 0.76rem;">
                                                            <i class="bi bi-star-fill me-1"></i>Semua Akses
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($u->canViewUserMonitoring())
                                        <div class="d-flex align-items-center justify-content-center gap-1.5 flex-wrap">
                                            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-40 px-2 py-1 font-monospace" style="font-size:0.68rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>Akses Diberikan
                                            </span>
                                            @if(!$u->isAdmin())
                                                <form action="{{ route('users.toggle-monitoring', $u->id) }}" method="POST" class="d-inline mb-0">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-outline-warning rounded-pill py-0.5 px-2 text-warning" title="Kembalikan Hak Akses ke Normal" style="font-size:0.68rem;">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Kembalikan ke Normal
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center gap-1.5 flex-wrap">
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-40 px-2 py-1 font-monospace" style="font-size:0.68rem;">
                                                <i class="bi bi-shield-fill me-1"></i>Normal
                                            </span>
                                            <form action="{{ route('users.toggle-monitoring', $u->id) }}" method="POST" class="d-inline mb-0">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-success rounded-pill py-0.5 px-2 text-success fw-bold" title="Berikan Hak Akses Monitoring User Lain" style="font-size:0.68rem;">
                                                    <i class="bi bi-key-fill me-1"></i>Berikan Hak Akses
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2 py-1" onclick="openEditUserModal({{ json_encode($u) }})" title="Edit User &amp; Privileges">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        @if($u->id !== Auth::id())
                                            <form id="deleteUserForm{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1" title="Hapus User" onclick="KawaiConfirm.delete('Hapus User', 'Pengguna {{ $u->name }} ({{ $u->username }}) akan dihapus permanen.', () => document.getElementById('deleteUserForm{{ $u->id }}').submit())">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                    Tidak ditemukan data pengguna.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL 1: TAMBAH USER BARU -->
    <div class="modal fade" id="modalAddUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content glass-card border-secondary text-white" style="background: #111827; border: 1px solid rgba(255,255,255,0.15);">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                        <i class="bi bi-person-plus-fill text-warning"></i>
                        <span>Tambah User Baru &amp; Tentukan Privileges</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body py-4">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">User Name (Username) <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control search-input" placeholder="contoh: admin_bambang" required>
                                <div class="form-text fs-8 text-muted">Digunakan untuk login ke dalam sistem</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Full Name (Nama Lengkap) <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control search-input" placeholder="contoh: Bambang Widjanarko" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control search-input" placeholder="bambang@kawai.co.id" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">User Group / Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select search-input" required id="addRoleSelect" onchange="togglePermissionsChecklist('add')">
                                    <option value="staff">Staff Buyer / Procurement</option>
                                    <option value="leader">Leader</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="admin">Admin (Administrator - Full Access)</option>
                                    <option value="viewer">Viewer (Read Only)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Departemen / Sub-Role / Keterangan Jabatan</label>
                            <input type="text" name="department" class="form-control search-input" placeholder="contoh: Buyer Wood & Acoustic Spruce atau Purchasing Management">
                            <div class="form-text fs-8 text-muted">Keterangan ini akan ditampilkan di bawah Full Name pada tabel user</div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control search-input" placeholder="Minimal 6 karakter" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control search-input" placeholder="Ulangi password" required>
                            </div>
                        </div>

                        <!-- PRIVILEGES / HAK AKSES CHECKLIST -->
                        <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-50 mb-3" id="addPermissionsSection">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-warning mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Penetapan Privileges &amp; Hak Akses Fitur (Khusus Admin)</span>
                                </h6>
                                <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2.5 py-1" onclick="selectAllPermissions('add')">
                                    Pilih Semua (Select All)
                                </button>
                            </div>
                            <div class="row g-2">
                                @foreach($allPermissions as $permKey => $permLabel)
                                    <div class="col-md-6">
                                        <div class="form-check form-switch p-2 rounded bg-secondary bg-opacity-10 border border-secondary border-opacity-25">
                                            <input class="form-check-input ms-0 me-2 perm-add-check" type="checkbox" name="permissions[]" value="{{ $permKey }}" id="perm_add_{{ $permKey }}" checked>
                                            <label class="form-check-label text-white small fw-semibold" for="perm_add_{{ $permKey }}">
                                                {{ $permLabel }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Simpan User Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: EDIT USER & HAK AKSES -->
    <div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content glass-card border-secondary text-white" style="background: #111827; border: 1px solid rgba(255,255,255,0.15);">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-info"></i>
                        <span>Edit User &amp; Perbarui Hak Akses (Privileges)</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body py-4">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">User Name (Username) <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="editUsername" class="form-control search-input" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Full Name (Nama Lengkap) <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="editName" class="form-control search-input" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="editEmail" class="form-control search-input" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">User Group / Role <span class="text-danger">*</span></label>
                                <select name="role" id="editRole" class="form-select search-input" required onchange="togglePermissionsChecklist('edit')">
                                    <option value="staff">Staff Buyer / Procurement</option>
                                    <option value="leader">Leader</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="admin">Admin (Administrator - Full Access)</option>
                                    <option value="viewer">Viewer (Read Only)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Departemen / Sub-Role / Keterangan Jabatan</label>
                            <input type="text" name="department" id="editDepartment" class="form-control search-input" placeholder="contoh: Buyer Wood & Acoustic Spruce (atau kosongkan untuk menghapus)">
                            <div class="form-text fs-8 text-muted">Bisa diubah atau dikosongkan jika ingin menghapus keterangan di bawah Full Name</div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Ubah Password Baru (Opsional)</label>
                                <input type="password" name="password" class="form-control search-input" placeholder="Kosongkan jika tidak ingin diubah">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-control search-input" placeholder="Ulangi password baru">
                            </div>
                        </div>

                        <!-- PRIVILEGES / HAK AKSES CHECKLIST EDIT -->
                        <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-50 mb-3" id="editPermissionsSection">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-warning mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Privileges &amp; Hak Akses Fitur (Khusus Admin)</span>
                                </h6>
                                <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2.5 py-1" onclick="selectAllPermissions('edit')">
                                    Pilih Semua (Select All)
                                </button>
                            </div>
                            <div class="row g-2">
                                @foreach($allPermissions as $permKey => $permLabel)
                                    <div class="col-md-6">
                                        <div class="form-check form-switch p-2 rounded bg-secondary bg-opacity-10 border border-secondary border-opacity-25">
                                            <input class="form-check-input ms-0 me-2 perm-edit-check" type="checkbox" name="permissions[]" value="{{ $permKey }}" id="perm_edit_{{ $permKey }}">
                                            <label class="form-check-label text-white small fw-semibold" for="perm_edit_{{ $permKey }}">
                                                {{ $permLabel }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info text-dark rounded-pill px-4 fw-bold">Update Data User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function openEditUserModal(user) {
            document.getElementById('editUserForm').action = '/users/' + user.id;
            document.getElementById('editUsername').value = user.username || '';
            document.getElementById('editName').value = user.name || '';
            document.getElementById('editEmail').value = user.email || '';
            document.getElementById('editRole').value = user.role || 'staff';
            document.getElementById('editDepartment').value = user.department || '';

            const perms = Array.isArray(user.permissions) ? user.permissions : [];
            const isFullAccess = user.role === 'admin' || perms.includes('*');

            document.querySelectorAll('.perm-edit-check').forEach(chk => {
                chk.checked = isFullAccess || perms.includes(chk.value);
            });

            togglePermissionsChecklist('edit');

            const modal = new bootstrap.Modal(document.getElementById('modalEditUser'));
            modal.show();
        }

        function togglePermissionsChecklist(mode) {
            const role = document.getElementById(mode === 'add' ? 'addRoleSelect' : 'editRole').value;
            const section = document.getElementById(mode === 'add' ? 'addPermissionsSection' : 'editPermissionsSection');
            
            if (role === 'admin') {
                section.style.opacity = '0.5';
                document.querySelectorAll(`.perm-${mode}-check`).forEach(chk => chk.checked = true);
            } else {
                section.style.opacity = '1';
            }
        }

        function selectAllPermissions(mode) {
            document.querySelectorAll(`.perm-${mode}-check`).forEach(chk => chk.checked = true);
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
    @include('partials.confirm-modal')
    <script src="{{ asset('js/kawai-notify.js') }}"></script>
    <script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
