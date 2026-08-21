<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Master Line Produksi | PT Kawai Indonesia</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --bg-primary: #0a0e17;
            --bg-secondary: #121826;
            --card-bg: rgba(23, 31, 48, 0.8);
            --card-border: rgba(255, 255, 255, 0.08);
            --accent-gold: #e2b34a;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
        }

        body {
            background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Outfit', sans-serif;
        }

        .top-navbar {
            background: rgba(18, 24, 38, 0.85);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 1.75rem;
        }

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(12px);
        }

        .form-control-dark, .form-select-dark {
            background-color: rgba(10, 14, 23, 0.85);
            color: var(--text-main);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 0.65rem 1rem;
        }

        .table-custom {
            color: var(--text-main);
        }

        .table-custom thead th {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            border-bottom: 1px solid var(--card-border);
            padding: 1rem;
        }

        .table-custom tbody td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
</head>
<body>

    <nav class="top-navbar d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('production.input') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Form Input
            </a>
            <span class="fw-bold fs-5 text-warning">PT KAWAI INDONESIA | MASTER DATA LINE PRODUKSI</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('production.input') }}" class="btn btn-info btn-sm rounded-pill px-3 fw-bold text-dark">
                <i class="fa-solid fa-pen-to-square me-1"></i> Data Input
            </a>
            <a href="{{ route('dashboard.overview') }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-chart-line me-1"></i> Lihat Dashboard
            </a>
        </div>
    </nav>

    <div class="container py-4">

        @include('partials.toast-and-notification-popup')

        <div class="row g-4">
            <!-- Form Tambah Line -->
            <div class="col-12 col-lg-4">
                <div class="glass-card">
                    <h4 class="fw-bold mb-1">Tambah Line Produksi</h4>
                    <p class="text-muted mb-4" style="font-size: 0.85rem;">Daftarkan jalur perakitan / divisi manufaktur baru</p>

                    <form action="{{ route('production.lines.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode Line</label>
                            <input type="text" name="line_code" class="form-control-dark w-100" placeholder="Contoh: LINE-09" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Line Produksi</label>
                            <input type="text" name="line_name" class="form-control-dark w-100" placeholder="Contoh: Grand Piano Action Mechanism" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori Produk</label>
                            <input type="text" name="product_category" class="form-control-dark w-100" placeholder="Contoh: Shigeru Kawai & GX Series" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Supervisor</label>
                            <input type="text" name="supervisor" class="form-control-dark w-100" placeholder="Contoh: Hendra Saputra" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kapasitas Target Harian (pcs)</label>
                            <input type="number" name="daily_target_capacity" class="form-control-dark w-100" value="1500" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status Line</label>
                            <select name="status" class="form-select-dark w-100" required>
                                <option value="Running">Running (Aktif Berjalan)</option>
                                <option value="Maintenance">Maintenance (Perawatan)</option>
                                <option value="Idle">Idle (Istirahat)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2">
                            <i class="fa-solid fa-plus me-2"></i> Tambah Line Baru
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar Master Line -->
            <div class="col-12 col-lg-8">
                <div class="glass-card">
                    <h4 class="fw-bold mb-1">Daftar Divisi / Line Produksi Aktif</h4>
                    <p class="text-muted mb-4" style="font-size: 0.85rem;">Pabrik Manufaktur Piano PT Kawai Indonesia (KIIC Karawang)</p>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Line</th>
                                    <th>Kategori</th>
                                    <th>Supervisor</th>
                                    <th>Kapasitas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lines as $line)
                                    <tr>
                                        <td>
                                            <span class="badge bg-dark border text-light font-monospace">
                                                {{ $line->line_code }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-white">{{ $line->line_name }}</td>
                                        <td class="text-muted">{{ $line->product_category }}</td>
                                        <td>{{ $line->supervisor }}</td>
                                        <td class="font-monospace text-info">{{ number_format($line->daily_target_capacity) }} pcs</td>
                                        <td>
                                            <span class="badge {{ $line->status == 'Running' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $line->status }}
                                            </span>
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

</body>
</html>
