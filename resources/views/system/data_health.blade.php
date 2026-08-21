<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Integration Health & Traceability - PT Kawai Indonesia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-dark: #0A0E1A;
            --card-bg: rgba(18, 24, 38, 0.85);
            --card-border: rgba(255, 255, 255, 0.08);
            --accent-emerald: #10B981;
            --accent-gold: #F59E0B;
            --accent-blue: #3B82F6;
            --text-main: #F3F4F6;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }
        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            backdrop-filter: blur(10px);
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
        @include('partials.pill-nav', ['activeRoute' => 'system.data-health', 'hasFaqModal' => false])
    </div>
</nav>

<div class="container-fluid py-4 px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-2 py-1">
                    <i class="bi bi-shield-check me-1"></i> Data Trace Debugger
                </span>
                <span class="text-muted small">Updated: {{ $healthData['timestamp'] }}</span>
            </div>
            <h1 class="h3 fw-bold mt-1 text-white">Data Integration Health & Traceability Matrix</h1>
            <p class="text-muted small mb-0">Audit korelasi data antar dashboard Purchasing PT Kawai Indonesia.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchasing.master-po') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Ke Master PO
            </a>
            <button onclick="window.location.reload()" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Status
            </button>
        </div>
    </div>

    <!-- Health Score & Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="glass-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Overall Health Score</div>
                        <div class="h2 fw-bold text-success mb-0">{{ $healthData['health_score'] }}%</div>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                        <i class="bi bi-heart-pulse-fill fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <i class="bi bi-check-circle-fill text-success me-1"></i> Integrasi modul tersinkronisasi
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="glass-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Master PO Plan Qty</div>
                        <div class="h2 fw-bold text-info mb-0">{{ number_format($healthData['reconciliation']['sum_master_po_qty']) }}</div>
                    </div>
                    <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    Total records: {{ number_format($healthData['modules']['master_po']['record_count']) }} baris
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="glass-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Incoming Result Qty</div>
                        <div class="h2 fw-bold text-success mb-0">{{ number_format($healthData['reconciliation']['sum_incoming_qty']) }}</div>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                        <i class="bi bi-truck fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    Total receipts: {{ number_format($healthData['modules']['incoming']['record_count']) }} baris
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="glass-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold">Total Outstanding Qty</div>
                        <div class="h2 fw-bold text-warning mb-0">{{ number_format($healthData['reconciliation']['sum_outstanding_qty']) }}</div>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    Matched PO: {{ number_format($healthData['modules']['outstanding']['matched_po']) }} nomor
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Module Health Matrix Cards -->
    <div class="row g-3 mb-4">
        @foreach($healthData['modules'] as $key => $mod)
            <div class="col">
                <div class="glass-card p-3 h-100 border-{{ $mod['status'] === 'HEALTHY' ? 'success' : 'secondary' }} border-opacity-50">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge {{ $mod['status'] === 'HEALTHY' ? 'bg-success' : 'bg-secondary' }} px-2 py-1">
                            {{ $mod['status'] }}
                        </span>
                        <i class="bi bi-diagram-3 text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-white mb-1">{{ $mod['title'] }}</h6>
                    <div class="small text-muted">
                        @if(isset($mod['record_count']))
                            <span>Data: <b>{{ number_format($mod['record_count']) }}</b> baris</span><br>
                        @endif
                        @if(isset($mod['items_count']))
                            <span>Items: <b>{{ number_format($mod['items_count']) }}</b> SKU</span>
                        @endif
                        @if(isset($mod['actuals_count']))
                            <span>Actuals: <b>{{ number_format($mod['actuals_count']) }}</b></span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Data Trace Table & Reconciliation Checklist -->
    <div class="row g-4">
        <div class="col-md-7">
            <div class="glass-card p-4">
                <h5 class="fw-bold text-white mb-3">
                    <i class="bi bi-search text-info me-2"></i>Sample Unmatched / Pending PO Tracing
                </h5>
                @if(count($healthData['unmatched_samples']) > 0)
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0 small">
                            <thead>
                                <tr class="text-secondary border-secondary">
                                    <th>PO Number</th>
                                    <th>Material Code</th>
                                    <th>Supplier</th>
                                    <th class="text-end">Plan Qty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($healthData['unmatched_samples'] as $sample)
                                    <tr>
                                        <td class="fw-bold text-warning">{{ $sample['po'] }}</td>
                                        <td><code>{{ $sample['item_code'] }}</code></td>
                                        <td>{{ $sample['supplier'] }}</td>
                                        <td class="text-end fw-bold">{{ number_format($sample['plan_qty']) }}</td>
                                        <td><span class="badge bg-secondary">{{ $sample['status'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-success bg-success bg-opacity-10 border border-success border-opacity-25 text-success mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i> Seluruh Master PO telah terhubung dengan realisasi penerimaan (atau belum ada data yang dimuat).
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-5">
            <div class="glass-card p-4">
                <h5 class="fw-bold text-white mb-3">
                    <i class="bi bi-clipboard2-check-fill text-success me-2"></i>Reconciliation Checklist
                </h5>
                <ul class="list-group list-group-flush bg-transparent small">
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-check2-circle text-success me-2"></i>Canonical Period Format (`YYYY-MM`)</span>
                        <span class="badge bg-success">Standard</span>
                    </li>
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-check2-circle text-success me-2"></i>Preserve Leading Zero (`Material Code`)</span>
                        <span class="badge bg-success">Active</span>
                    </li>
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-check2-circle text-success me-2"></i>Canonical PO Number Matching</span>
                        <span class="badge bg-success">Active</span>
                    </li>
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-check2-circle text-success me-2"></i>Formula: `Outstanding = max(Plan - Result, 0)`</span>
                        <span class="badge bg-success">Verified</span>
                    </li>
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-check2-circle text-success me-2"></i>Multi-Currency Normalization (`USD/IDR`)</span>
                        <span class="badge bg-success">Active</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
