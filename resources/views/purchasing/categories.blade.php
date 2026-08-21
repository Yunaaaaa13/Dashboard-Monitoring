<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Kategori Material Purchasing | PT Kawai Indonesia</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-dark: #0A0E1A;
            --bg-primary: #0A0E1A;
            --bg-secondary: #121826;
            --card-bg: rgba(18, 24, 38, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --accent-gold: #e2b34a;
            --text-main: #f3f4f6;
            --text-muted: #cbd5e1;
        }

        body {
            background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Outfit', sans-serif;
        }

        .text-muted, .text-secondary {
            color: #cbd5e1 !important;
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

        .brand-logo-text {
            font-weight: 800;
            font-size: 1.25rem;
            background: linear-gradient(135deg, #fff 0%, var(--accent-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .form-control-dark, .form-select-dark {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            border-radius: 10px;
            padding: 0.65rem 1rem;
        }

        .form-control-dark:focus, .form-select-dark:focus {
            background: #111827;
            border-color: var(--accent-gold);
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(226, 179, 74, 0.2);
            outline: none;
        }

        .table-container {
            border-radius: 14px;
            border: 1px solid var(--card-border);
            overflow: hidden;
            background: var(--card-bg);
        }

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--text-main);
            margin-bottom: 0;
        }

        .table-custom thead th {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--card-border);
            padding: 1rem;
            font-weight: 700;
        }

        .table-custom tbody td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }

        .table-custom tbody tr:hover td {
            background-color: rgba(226, 179, 74, 0.08) !important;
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
                <div class="text-muted" style="font-size:0.72rem; margin-left:2px; color:#9ca3af !important;">Master Kategori Material &amp; Kode SKU — Pengadaan Bahan Baku Piano</div>
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
            @include('partials.pill-nav', ['activeRoute' => 'purchasing.categories'])
        </div>
    </nav>

    @include('partials.faq-modal')


    <div class="container-fluid px-4 py-4">

        @include('partials.toast-and-notification-popup')

        <!-- HERO BANNER HEADER -->
        <div class="exchange-hero mb-4">
            <div class="row align-items-center">
                <div class="col-12 col-md-7">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:rgba(226,179,74,0.18);border:1px solid rgba(226,179,74,0.45);color:#e2b34a;">
                            <i class="bi bi-tags-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="hero-rate-label">MASTER DATA &amp; CATEGORIES</span>
                                <span class="badge rounded-pill bg-warning text-dark fw-bold" style="font-size:0.68rem; padding: 2px 8px;">SKU MASTER</span>
                            </div>
                            <h2 class="fw-bold text-white mb-0 brand-font" style="font-size:1.65rem;">Master Kategori Material &amp; Kode SKU</h2>
                        </div>
                    </div>
                    <p class="text-muted mb-0 mt-2" style="font-size:0.88rem;">
                        Kelola klasifikasi kelompok bahan baku, kode kategori, serta pencapaian realisasi unit pengadaan PT Kawai Indonesia.
                    </p>
                </div>
                <div class="col-12 col-md-5 mt-3 mt-md-0">
                    <div class="d-flex gap-2 justify-content-md-end align-items-center flex-wrap">
                        <span class="px-3.5 py-2 rounded-pill fw-bold" style="font-size:0.85rem; background:rgba(226,179,74,0.18); border:1px solid rgba(226,179,74,0.45); color:#fbbf24;">
                            <i class="fa-solid fa-boxes-stacked me-1.5 text-warning"></i> Total Kategori: <strong>{{ $categories->count() }}</strong>
                        </span>
                        @if(Auth::check() && Auth::user()->isAdmin())
                        <button type="button" class="btn btn-warning rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" onclick="document.querySelector('input[name=category_code]')?.focus(); document.querySelector('input[name=category_code]')?.scrollIntoView({behavior: 'smooth', block: 'center'});" style="font-size:0.88rem;">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Kategori Baru
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @php
            $totalTargetUnits = $categories->sum('monthly_target_units');
            $totalActualUnits = $categories->sum('logs_sum_actual_received');
            $overallPct = $totalTargetUnits > 0 ? round(($totalActualUnits / $totalTargetUnits) * 100, 1) : 0;
            $activeCount = $categories->where('status', 'Active')->count();
        @endphp

        <!-- SUMMARY KPI CARDS FOR CATEGORIES -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">TARGET BULANAN</div>
                        <div class="h3 fw-bold text-info mb-0 font-monospace">{{ number_format($totalTargetUnits) }} <small class="fs-6 text-muted">unit</small></div>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(6, 182, 212, 0.15); color: #06b6d4;">
                        <i class="fa-solid fa-bullseye fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">UNIT TERCAPAI (ACTUAL)</div>
                        <div class="h3 fw-bold text-warning mb-0 font-monospace">{{ number_format($totalActualUnits) }} <small class="fs-6 text-muted">unit</small></div>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="fa-solid fa-box-open fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">PERSENTASE PEMENUHAN</div>
                        <div class="h3 fw-bold text-emerald mb-0 font-monospace text-success">{{ $overallPct }}%</div>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="fa-solid fa-chart-line fs-4"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">KATEGORI AKTIF</div>
                        <div class="h3 fw-bold text-white mb-0 font-monospace">{{ $activeCount }} <small class="fs-6 text-muted">divisi</small></div>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                        <i class="fa-solid fa-layer-group fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Form Tambah Kategori Material -->
            <div class="col-12 col-lg-4">
                <div class="glass-card">
                    <h4 class="fw-bold mb-1">Tambah Kategori Material</h4>
                    <p class="text-muted mb-4" style="font-size: 0.85rem;">Kelompok pengadaan bahan baku manufaktur piano</p>

                    <form action="{{ route('purchasing.categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode Kategori</label>
                            <input type="text" name="category_code" class="form-control-dark w-100" placeholder="Contoh: PUR-05" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Kategori Material</label>
                            <input type="text" name="category_name" class="form-control-dark w-100" placeholder="Contoh: Aksesoris & Hardware Brass" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">PIC Procurement / Buyer</label>
                            <select name="buyer_user_id" class="form-select-dark w-100" required>
                                <option value="">-- Pilih akun buyer purchasing --</option>
                                @foreach($buyers as $buyer)
                                    <option value="{{ $buyer->id }}">
                                        {{ $buyer->name }} — {{ strtoupper($buyer->role) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">PIC terhubung langsung ke akun pengguna.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Target Pengadaan Bulanan (unit)</label>
                            <input type="number" name="monthly_target_units" class="form-control-dark w-100" value="3000" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status Kategori</label>
                            <select name="status" class="form-select-dark w-100" required>
                                <option value="Active">Active (Aktif Dibeli)</option>
                                <option value="Review">Review (Evaluasi Vendor)</option>
                                <option value="Hold">Hold (Sementara Ditahan)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2">
                            <i class="fa-solid fa-plus me-2"></i> Tambah Kategori Baru
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar Kategori -->
            <div class="col-12 col-lg-8">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h4 class="fw-bold mb-1">Daftar Divisi &amp; Kategori Pembelian Aktif</h4>
                            <p class="text-muted mb-0" style="font-size: 0.85rem;">Divisi Procurement PT Kawai Indonesia (KIIC Karawang)</p>
                        </div>
                        <button type="button" id="btnBulkDeleteCategory" class="btn btn-danger btn-sm rounded-pill px-3 d-none" onclick="confirmBulkDeleteCategory()">
                            <i class="fa-solid fa-trash me-1"></i> Hapus Terpilih (<span id="bulkDeleteCountCategory">0</span>)
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">
                                        <input type="checkbox" id="checkAllCategory" class="form-check-input">
                                    </th>
                                    <th>Kode</th>
                                    <th>Nama Kategori Material</th>
                                    <th>PIC Procurement</th>
                                    <th>Target Bulanan</th>
                                    <th>Unit Tercapai (Actual)</th>
                                    <th>Pencapaian (%)</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $cat)
                                    @php
                                        $actual = $cat->logs_sum_actual_received ?? 0;
                                        $target = $cat->monthly_target_units ?: 1;
                                        $pct = round(($actual / $target) * 100, 1);
                                        $badgeColor = $pct >= 100 ? 'bg-success' : ($pct > 0 ? 'bg-warning text-dark' : 'bg-secondary');
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="row-checkbox-category form-check-input" value="{{ $cat->id }}">
                                        </td>
                                        <td>
                                            <span class="badge bg-dark border text-light font-monospace">
                                                {{ $cat->category_code }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-white">{{ $cat->category_name }}</td>
                                        <td class="text-muted">
                                            @if($cat->buyer)
                                                <div class="fw-semibold text-white">{{ $cat->buyer->name }}</div>
                                                <small>{{ $cat->buyer->email }} · {{ strtoupper($cat->buyer->role) }}</small>
                                            @else
                                                {{ $cat->pic_buyer }}
                                            @endif
                                        </td>
                                        <td class="font-monospace text-info">{{ number_format($cat->monthly_target_units) }} unit</td>
                                        <td class="font-monospace text-warning fw-bold">{{ number_format($actual) }} unit</td>
                                        <td style="min-width: 140px;">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="badge {{ $badgeColor }} font-monospace" style="font-size: 0.75rem;">
                                                    {{ $pct }}%
                                                </span>
                                                <small class="text-muted" style="font-size:0.7rem;">{{ number_format($actual) }}/{{ number_format($cat->monthly_target_units) }}</small>
                                            </div>
                                            <div class="progress" style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 4px;">
                                                <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : ($pct > 0 ? 'bg-warning' : 'bg-secondary') }}"
                                                     role="progressbar"
                                                     style="width: {{ min(100, $pct) }}%;"
                                                     aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $cat->status == 'Active' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $cat->status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $cat->id }}" title="Edit Kategori">
                                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                                </button>
                                                <form id="deleteCategoryForm{{ $cat->id }}" action="{{ route('purchasing.categories.destroy', $cat->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" title="Hapus Kategori" onclick="KawaiConfirm.delete('Hapus Kategori', 'Kategori {{ $cat->category_code }} akan dihapus.', () => document.getElementById('deleteCategoryForm{{ $cat->id }}').submit())">
                                                        <i class="fa-solid fa-trash me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
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

    <!-- Modals Edit Kategori (placed outside any container to prevent viewport/backdrop clipping) -->
    @foreach($categories as $cat)
        <div class="modal fade text-start" id="editModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="background: #121826; border: 1px solid rgba(255,255,255,0.15); color: #f3f4f6; border-radius: 16px;">
                    <div class="modal-header border-bottom border-secondary border-opacity-25 py-3 px-4">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square text-warning me-2"></i>Edit Kategori Material ({{ $cat->category_code }})</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('purchasing.categories.update', $cat->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body py-3 px-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kode Kategori</label>
                                <input type="text" name="category_code" class="form-control-dark w-100" value="{{ $cat->category_code }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Kategori Material</label>
                                <input type="text" name="category_name" class="form-control-dark w-100" value="{{ $cat->category_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">PIC Procurement / Buyer</label>
                                <select name="buyer_user_id" class="form-select-dark w-100" required>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->id }}" {{ $cat->buyer_user_id == $buyer->id ? 'selected' : '' }}>
                                            {{ $buyer->name }} — {{ strtoupper($buyer->role) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Target Pengadaan Bulanan (unit)</label>
                                <input type="number" name="monthly_target_units" class="form-control-dark w-100" value="{{ $cat->monthly_target_units }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status Kategori</label>
                                <select name="status" class="form-select-dark w-100" required>
                                    <option value="Active" {{ $cat->status == 'Active' ? 'selected' : '' }}>Active (Aktif Dibeli)</option>
                                    <option value="Review" {{ $cat->status == 'Review' ? 'selected' : '' }}>Review (Evaluasi Vendor)</option>
                                    <option value="Hold" {{ $cat->status == 'Hold' ? 'selected' : '' }}>Hold (Sementara Ditahan)</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-top border-secondary border-opacity-25 py-3 px-4">
                            <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 py-2 fw-bold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal Bulk Delete Confirmation Kategori -->
    <div class="modal fade" id="modalBulkDeleteCategoryConfirm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-danger text-white" style="background: #111827;">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i> Konfirmasi Hapus Massal Kategori</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('purchasing.categories.destroy-bulk') }}" method="POST" id="formBulkDeleteCategory">
                    @csrf
                    <div class="modal-body">
                        <div id="bulkDeleteCategoryIdsContainer"></div>
                        Apakah Anda yakin ingin menghapus <strong id="bulkDeleteCategoryCountText" class="text-danger">0</strong> Kategori terpilih?
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

    <!-- Bootstrap 5.3 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAllCategory = document.getElementById('checkAllCategory');
            const rowCheckboxesCategory = document.querySelectorAll('.row-checkbox-category');
            const btnBulkDeleteCategory = document.getElementById('btnBulkDeleteCategory');
            const countSpanCategory = document.getElementById('bulkDeleteCountCategory');

            function updateCategoryBulkBtn() {
                const checked = document.querySelectorAll('.row-checkbox-category:checked');
                if (btnBulkDeleteCategory) {
                    if (checked.length > 0) {
                        btnBulkDeleteCategory.classList.remove('d-none');
                        countSpanCategory.innerText = checked.length;
                    } else {
                        btnBulkDeleteCategory.classList.add('d-none');
                    }
                }
            }

            if (checkAllCategory) {
                checkAllCategory.addEventListener('change', function() {
                    rowCheckboxesCategory.forEach(cb => cb.checked = this.checked);
                    updateCategoryBulkBtn();
                });
            }

            rowCheckboxesCategory.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (checkAllCategory) {
                        checkAllCategory.checked = (document.querySelectorAll('.row-checkbox-category:checked').length === rowCheckboxesCategory.length);
                    }
                    updateCategoryBulkBtn();
                });
            });
        });

        function confirmBulkDeleteCategory() {
            const checked = document.querySelectorAll('.row-checkbox-category:checked');
            if (checked.length === 0) return;
            
            const container = document.getElementById('bulkDeleteCategoryIdsContainer');
            container.innerHTML = '';
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            document.getElementById('bulkDeleteCategoryCountText').innerText = checked.length;
            new bootstrap.Modal(document.getElementById('modalBulkDeleteCategoryConfirm')).show();
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
