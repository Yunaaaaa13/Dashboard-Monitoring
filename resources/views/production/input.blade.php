<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Produksi Real | PT Kawai Indonesia</title>
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

        .form-control-dark:focus, .form-select-dark:focus {
            background-color: rgba(10, 14, 23, 1);
            color: var(--text-main);
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.25rem rgba(226, 179, 74, 0.2);
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
            <a href="{{ route('dashboard.overview') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <span class="fw-bold fs-5 text-warning">PT KAWAI INDONESIA | ENTRY DATA PRODUKSI NYATA</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('production.lines') }}" class="btn btn-outline-info btn-sm rounded-pill px-3">
                <i class="fa-solid fa-gear me-1"></i> Kelola Master Line Produksi
            </a>
            <a href="{{ route('production.input') }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark">
                <i class="fa-solid fa-pen-to-square me-1"></i> Data Input
            </a>
        </div>
    </nav>

    <div class="container py-4">

        @include('partials.toast-and-notification-popup')

        <div class="row g-4">
            <!-- Form Input Real Data -->
            <div class="col-12 col-lg-5">
                <div class="glass-card">
                    <h4 class="fw-bold mb-1">Input Real Data Produksi</h4>
                    <p class="text-muted mb-4" style="font-size: 0.85rem;">Catat output aktual per Line produksi (secara manual atau verifikasi EZRunner)</p>

                    <form action="{{ route('production.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-light">Pilih Line Produksi</label>
                            <select name="production_line_id" class="form-select-dark w-100" required>
                                <option value="">-- Pilih Line Perakitan --</option>
                                @foreach($lines as $line)
                                    <option value="{{ $line->id }}">{{ $line->line_code }} - {{ $line->line_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-light">Tanggal</label>
                                <input type="date" name="log_date" class="form-control-dark w-100" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-light">Jam Shift</label>
                                <select name="log_hour" class="form-select-dark w-100" required>
                                    @for($h = 7; $h <= 22; $h++)
                                        <option value="{{ sprintf('%02d:00', $h) }}" {{ date('H') == $h ? 'selected' : '' }}>
                                            {{ sprintf('%02d:00 WIB', $h) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-light">Target Output (pcs)</label>
                                <input type="number" name="target_output" class="form-control-dark w-100" placeholder="Contoh: 200" required min="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-light">Aktual Output (pcs)</label>
                                <input type="number" name="actual_output" class="form-control-dark w-100" placeholder="Contoh: 195" required min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-light">Jumlah Defect / Cacat (pcs)</label>
                            <input type="number" name="defect_count" class="form-control-dark w-100" value="0" min="0">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-light">Catatan / Status Opsional</label>
                            <input type="text" name="status_note" class="form-control-dark w-100" placeholder="Contoh: Normal / Penggantian mata bor CNC">
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Data Real Produksi
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabel Riwayat Real Log -->
            <div class="col-12 col-lg-7">
                <div class="glass-card h-100">
                    <h4 class="fw-bold mb-1">Riwayat Pencatatan Aktual Terakhir</h4>
                    <p class="text-muted mb-3" style="font-size: 0.85rem;">Data yang tampil langsung disinkronkan ke Modul Dashboard Overview</p>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Line Produksi</th>
                                    <th class="text-end">Target</th>
                                    <th class="text-end">Aktual</th>
                                    <th class="text-center">Achievement</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                    <tr>
                                        <td class="font-monospace text-muted" style="font-size: 0.85rem;">
                                            {{ $log->log_time->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="fw-semibold">
                                            {{ $log->line->line_code ?? '-' }} <br>
                                            <small class="text-muted">{{ $log->line->line_name ?? '' }}</small>
                                        </td>
                                        <td class="text-end font-monospace text-info">{{ number_format($log->target_output) }}</td>
                                        <td class="text-end font-monospace text-warning fw-bold">{{ number_format($log->actual_output) }}</td>
                                        <td class="text-center">
                                            @php
                                                $ach = $log->target_output > 0 ? round(($log->actual_output / $log->target_output)*100, 1) : 0;
                                            @endphp
                                            <span class="badge {{ $ach >= 90 ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $ach }}%
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <form id="deleteProdLog{{ $log->id }}" action="{{ route('production.log.destroy', $log->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus log" onclick="KawaiConfirm.delete('Hapus Log Produksi', 'Data log produksi ini akan dihapus.', () => document.getElementById('deleteProdLog{{ $log->id }}').submit())">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Belum ada log produksi real. Silakan input pada form di samping.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.confirm-modal')
    <script src="{{ asset('js/kawai-notify.js') }}"></script>
    <script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
