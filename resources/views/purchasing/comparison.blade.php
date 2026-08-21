<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparison Outstanding vs Actual | PT Kawai Indonesia</title>
    <meta name="description" content="Modul Komparasi Pasokan Outstanding PO vs Konsumsi Produksi Actual per Part Number & Periode.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg-dark: #0A0E1A;
            --bg-primary: #0A0E1A;
            --bg-secondary: #121826;
            --card-bg: rgba(18, 24, 38, 0.75);
            --card-border: rgba(255,255,255,0.08);
            --accent-gold: #e2b34a;
            --accent-blue: #3b82f6;
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --accent-purple: #8b5cf6;
            --accent-cyan: #00d2ff;
            --accent-danger: #ef4444;
            --text-main: #f3f4f6;
            --text-muted: #cbd5e1;
        }
        * { box-sizing: border-box; }
        body { background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%); color: var(--text-main); font-family: 'Inter', sans-serif; min-height: 100vh; }
        h1,h2,h3,h4,h5,.brand-font { font-family: 'Outfit', sans-serif; }

        /* NAVBAR */
        .top-navbar { background: rgba(18, 24, 38, 0.88); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid var(--card-border); padding: 0.85rem 1.75rem; position: sticky; top: 0; z-index: 1000; }
        .text-muted, .text-secondary { color: #cbd5e1 !important; }

        /* GLASS CARD */
        .glass-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.3); backdrop-filter: blur(12px); }

        /* PAGE HEADER */
        .page-header-title { font-size: 1.7rem; font-weight: 800; background: linear-gradient(135deg, #fff 0%, var(--accent-cyan) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* FILTER */
        .filter-select { background: #111827; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; padding: 0.5rem 1rem; font-size: 0.9rem; }
        .filter-select:focus { border-color: var(--accent-cyan); outline: none; }
        .btn-filter { background: var(--accent-cyan); color: #0A0E1A; border: none; border-radius: 8px; padding: 0.5rem 1.25rem; font-weight: 700; font-size: 0.9rem; transition: background 0.2s; }
        .btn-filter:hover { background: #00b8e6; color: #0A0E1A; }

        /* KPI CARDS & SPACING */
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
        .kpi-card { border-radius: 16px; padding: 1.5rem 1.65rem; border: 1px solid rgba(255,255,255,0.08); position: relative; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; background: var(--card-bg); }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,0.35); }
        .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; }
        .kpi-out::before { background: var(--accent-amber); }
        .kpi-act::before { background: var(--accent-emerald); }
        .kpi-sel::before { background: var(--accent-cyan); }
        .kpi-def::before { background: var(--accent-danger); }
        .kpi-card-title { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-muted); margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.6rem; }
        .kpi-card-value { font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; line-height: 1.1; margin-bottom: 0.35rem; }
        .kpi-card-sub { font-size: 0.82rem; color: var(--text-muted); }

        /* TABLE */
        .table-container { overflow-x: auto; border-radius: 14px; border: 1px solid var(--card-border); background: var(--card-bg); }
        .table-custom, .table { width: 100%; border-collapse: separate; border-spacing: 0; color: var(--text-main); font-size: 0.88rem; --bs-table-bg: transparent !important; --bs-table-color: var(--text-main) !important; --bs-table-border-color: rgba(255, 255, 255, 0.08) !important; --bs-table-hover-bg: rgba(255, 255, 255, 0.06) !important; --bs-table-hover-color: #ffffff !important; background: transparent !important; }
        .table-custom thead th, .table thead th { background: rgba(18, 24, 38, 0.95) !important; color: #cbd5e1 !important; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px; padding: 0.9rem 1rem; border-bottom: 2px solid rgba(255, 255, 255, 0.12) !important; font-weight: 700; white-space: nowrap; }
        .table-custom tbody tr, .table tbody tr { border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important; transition: background 0.15s; }
        .table-custom tbody tr:last-child, .table tbody tr:last-child { border-bottom: none; }
        .table-custom tbody tr:hover td, .table tbody tr:hover td { background: rgba(255, 255, 255, 0.08) !important; color: #ffffff !important; }
        .table-custom td, .table tbody td { padding: 0.9rem 1rem; vertical-align: middle; background: rgba(18, 24, 38, 0.45) !important; color: #F3F4F6 !important; }
        .table-custom td.part-number { font-family: 'Outfit', sans-serif; font-weight: 700; color: #fbbf24 !important; }
        .form-control::placeholder { color: #cbd5e1 !important; opacity: 0.85 !important; }

        /* STATUS BADGE */
        .badge-status { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.85rem; border-radius: 20px; font-size: 0.78rem; font-weight: 700; }
        .badge-cukup { background: rgba(16,185,129,0.18); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .badge-pas { background: rgba(245,158,11,0.18); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
        .badge-kurang { background: rgba(239,68,68,0.18); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }

        /* CHART CONTAINER */
        .chart-container { position: relative; height: 320px; }
        .chart-section-title { font-size: 1.05rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem; }
        .chart-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }

        /* EMPTY STATE */
        .empty-state { text-align: center; padding: 3.5rem; color: var(--text-muted); }
        .empty-state i { font-size: 2.8rem; opacity: 0.3; }

        /* Responsive */
        @media (max-width: 992px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .chart-container { height: 260px; }
        }
        @media (max-width: 576px) {
            .kpi-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

{{-- NAVBAR --}}
<nav class="top-navbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard.overview') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-music-note-beamed text-warning fs-4"></i>
            <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
        </a>
        <span class="badge bg-info text-dark px-3 py-1 rounded-pill fw-bold" style="font-size:0.72rem;">
            <i class="bi bi-intersect me-1"></i> COMPARISON OUTSTANDING VS ACTUAL
        </span>
    </div>
    <div>
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.comparison', 'hasFaqModal' => true])
    </div>
</nav>


@include('partials.faq-modal')

<main class="container-fluid py-4 px-4">
    @include('partials.toast-and-notification-popup')

    {{-- PAGE HEADER & FILTER --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-header-title mb-1">
                <i class="bi bi-sliders me-2" style="color:var(--accent-cyan)"></i>
                Comparison Outstanding vs Actual
            </h1>
            <p class="mb-0" style="color:var(--text-muted);font-size:0.88rem;">
                Komparasi rencana kebutuhan (Forecast) dengan incoming penerimaan PO per SKU dan periode.
            </p>
        </div>
        <form method="GET" action="{{ route('purchasing.comparison') }}" class="d-flex align-items-center gap-2">
            <label for="periode" class="form-label mb-0 fw-semibold" style="font-size:0.85rem;color:var(--text-muted);">
                <i class="fa fa-calendar-days me-1"></i> Filter Bulan:
            </label>
            <select name="periode" id="periode" class="filter-select" onchange="this.form.submit()">
                <option value="{{ now()->format('Y-m') }}" {{ $periode === now()->format('Y-m') ? 'selected' : '' }}>
                    {{ now()->format('F Y') }} (Bulan Ini)
                </option>
                @foreach($availablePeriodes as $p)
                    @if($p !== now()->format('Y-m'))
                        <option value="{{ $p }}" {{ $periode === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endif
                @endforeach
            </select>
            <button type="submit" class="btn-filter">
                <i class="fa fa-filter me-1"></i> Filter
            </button>
        </form>
    </div>

    {{-- ZONA ATAS: 4 KPI CARDS (Prompt 3 & 5) --}}
    <div class="kpi-grid mb-4">
        <div class="kpi-card kpi-out">
            <div class="kpi-card-title"><i class="fa fa-box me-1" style="color:var(--accent-amber)"></i>Total Outstanding Qty</div>
            <div class="kpi-card-value" style="color:#fbbf24;">{{ number_format($totalOutstanding) }}</div>
            <div class="kpi-card-sub">Barang PO belum diterimakan</div>
        </div>
        <div class="kpi-card kpi-act">
            <div class="kpi-card-title"><i class="fa fa-box-arrow-in-down me-1" style="color:var(--accent-emerald)"></i>Total Incoming Penerimaan</div>
            <div class="kpi-card-value" style="color:#34d399;">{{ number_format($totalActual) }}</div>
            <div class="kpi-card-sub">Total barang fisik diterima dari PO</div>
        </div>
        <div class="kpi-card kpi-sel">
            <div class="kpi-card-title"><i class="fa fa-scale-balanced me-1" style="color:var(--accent-cyan)"></i>Total Selisih Pasokan</div>
            <div class="kpi-card-value" style="color:{{ $totalSelisih >= 0 ? '#00d2ff' : '#f87171' }};">
                {{ $totalSelisih >= 0 ? '+' : '' }}{{ number_format($totalSelisih) }}
            </div>
            <div class="kpi-card-sub">Rumus: Outstanding - Actual</div>
        </div>
        <div class="kpi-card kpi-def">
            <div class="kpi-card-title"><i class="fa fa-triangle-exclamation me-1" style="color:var(--accent-danger)"></i>Total Material Kurang</div>
            <div class="kpi-card-value" style="color:#f87171;">{{ number_format($totalKurang) }}</div>
            <div class="kpi-card-sub">SKU defisit (Selisih &lt; 0)</div>
        </div>
    </div>

    {{-- ZONA TENGAH: GRAFIK BAR & DONUT (Prompt 3 & 5) --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="glass-card h-100">
                <div class="chart-section-title">
                    <span class="chart-dot" style="background:#fbbf24"></span>
                    <span class="chart-dot" style="background:#34d399"></span>
                    Ringkasan Outstanding vs Actual per Kategori (Bulan: {{ $periode }})
                </div>
                <div class="chart-container">
                    <canvas id="chartComparisonBar"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="glass-card h-100">
                <div class="chart-section-title">
                    <span class="chart-dot" style="background:#10b981"></span>
                    <span class="chart-dot" style="background:#f59e0b"></span>
                    <span class="chart-dot" style="background:#ef4444"></span>
                    Distribusi Status Pasokan
                </div>
                <div class="chart-container">
                    <canvas id="chartDonutStatus"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="chart-section-title mb-1">
                    <span class="chart-dot" style="background:#fbbf24"></span>
                    <span class="chart-dot" style="background:#34d399"></span>
                    Detail per Kode Item
                </div>
                <small style="color:var(--text-muted)">Pilih kategori untuk melihat perubahan outstanding dan actual setiap kode item.</small>
            </div>
            <select id="chartCategoryFilter" class="filter-select" aria-label="Pilih kategori untuk grafik detail"></select>
        </div>
        <div class="chart-container">
            <canvas id="chartCategoryItems"></canvas>
        </div>
    </div>

    {{-- ZONA BAWAH: TABEL KOMPARASI & EXPORT TOOLS (Prompt 3 & 5) --}}
    <div class="glass-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h2 style="font-size:1.15rem;font-weight:700;margin:0;">
                    <i class="fa fa-table me-2" style="color:var(--accent-cyan)"></i>
                    Tabel Komparasi Pasokan — Periode: <span style="color:#00d2ff">{{ $periode }}</span>
                </h2>
                <div class="input-group input-group-sm" style="width:280px;">
                    <span class="input-group-text bg-dark border-secondary text-light"><i class="bi bi-search"></i></span>
                    <input type="text" id="tableSearch" class="form-control bg-dark border-secondary text-light" placeholder="Cari Part Number / Description / PO...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="exportToCSV('Comparison_Outstanding_vs_Actual_{{ $periode }}.csv')" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </button>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="table table-custom table-borderless" id="tableComparison">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Part Number</th>
                        <th>Description</th>
                        <th>PO / Referensi</th>
                        <th style="color:#fbbf24">Outstanding Qty</th>
                        <th style="color:#34d399">Incoming Penerimaan</th>
                        <th style="color:#94a3b8">Stok Gudang</th>
                        <th style="color:#00d2ff">Selisih</th>
                        <th>Status</th>
                        <th>Rekomendasi Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($analysisData as $i => $row)
                    <tr>
                        <td style="color:#cbd5e1; font-weight:bold;">{{ $i + 1 }}</td>
                        <td class="part-number">{{ $row->part_number }}</td>
                        <td style="color:#ffffff; font-weight:500; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $row->description }}">{{ $row->description }}</td>
                        <td><span class="badge bg-secondary bg-opacity-50 text-light border border-secondary px-2 py-1">{{ $row->po }}</span></td>
                        <td><span class="fw-bold" style="color:#fbbf24;font-size:0.95rem;">{{ number_format($row->outstanding_qty) }}</span> unit</td>
                        <td><span class="fw-bold" style="color:#34d399;font-size:0.95rem;">{{ number_format($row->actual_qty) }}</span> unit</td>
                        <td><span style="color:#cbd5e1;">{{ number_format($row->stock_qty) }}</span> unit</td>
                        <td>
                            @php $sel = $row->selisih_out_actual; @endphp
                            <span class="fw-bold fs-6" style="color:{{ $sel > 0 ? '#10b981' : ($sel < 0 ? '#ef4444' : '#f59e0b') }}">
                                {{ $sel >= 0 ? '+' : '' }}{{ number_format($sel) }}
                            </span>
                        </td>
                        <td>
                            @if($row->status_pasokan === 'Cukup')
                                <span class="badge-status badge-cukup"><i class="bi bi-check-circle-fill"></i> Cukup</span>
                            @elseif($row->status_pasokan === 'Pas')
                                <span class="badge-status badge-pas"><i class="bi bi-exclamation-circle-fill"></i> Pas</span>
                            @elseif($row->status_pasokan === 'Kurang')
                                <span class="badge-status badge-kurang"><i class="bi bi-x-circle-fill"></i> Kurang</span>
                            @else
                                <span class="badge bg-secondary text-light px-2 py-1">N/A</span>
                            @endif
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);">
                            @if($row->status_pasokan === 'Kurang')
                                <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle me-1"></i> Expedite PO / Follow-up Supplier!</span>
                            @elseif($row->status_pasokan === 'Pas')
                                <span class="text-warning"><i class="bi bi-eye me-1"></i> Monitoring ketat ketepatan waktu JIT</span>
                            @else
                                <span class="text-success"><i class="bi bi-check me-1"></i> Pasokan aman & terjamin</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p class="mt-2 mb-0 fw-semibold">Tidak ada data komparasi untuk periode <strong>{{ $periode }}</strong>.</p>
                                <p style="font-size:0.82rem;">Pastikan Anda telah mengisi data di tab Master Outstanding dan Master Actual.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
// Real-time table search
document.getElementById('tableSearch')?.addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#tableComparison tbody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// CSV Export function
function exportToCSV(filename) {
    let csv = [];
    let rows = document.querySelectorAll('#tableComparison tr');
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/,/g, '');
            row.push('"' + data + '"');
        }
        csv.push(row.join(','));
    }
    let csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
    let downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
}

const chartGroups = @json($chartGroups);

// Chart 1: ringkasan per kategori, sehingga grafik tetap ringkas walaupun
// jumlah item hasil upload bertambah banyak.
const ctxBar = document.getElementById('chartComparisonBar')?.getContext('2d');
if (ctxBar) {
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'Outstanding Qty',
                    data: {!! json_encode($chartOutstanding) !!},
                    backgroundColor: '#fbbf24',
                    borderRadius: 6,
                },
                {
                    label: 'Incoming Penerimaan PO',
                    data: {!! json_encode($chartActual) !!},
                    backgroundColor: '#34d399',
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#cbd5e1', font: { family: 'Inter', size: 12 } } },
                tooltip: {
                    callbacks: {
                        afterBody: function(ctx) {
                            let idx = ctx[0].dataIndex;
                            let out = ctx[0].chart.data.datasets[0].data[idx] || 0;
                            let act = ctx[0].chart.data.datasets[1].data[idx] || 0;
                            let sel = out - act;
                            let st = sel > 0 ? 'Cukup' : (sel === 0 ? 'Pas' : 'Kurang');
                            return `Selisih kategori: ${sel >= 0 ? '+' : ''}${sel} unit (${st})`;
                        }
                    }
                }
            },
            scales: {
                x: { ticks: { color: '#cbd5e1', font: { family: 'Outfit', weight: 'bold' } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            }
        }
    });
}

// Chart 2: detail kode item di dalam satu kategori yang dipilih.
const categoryFilter = document.getElementById('chartCategoryFilter');
const ctxCategoryItems = document.getElementById('chartCategoryItems')?.getContext('2d');
let categoryItemsChart;

function renderCategoryItems(group) {
    if (!ctxCategoryItems || !group) return;
    const items = group.items || [];
    if (categoryItemsChart) categoryItemsChart.destroy();

    categoryItemsChart = new Chart(ctxCategoryItems, {
        type: 'bar',
        data: {
            labels: items.map(item => item.part_number),
            datasets: [
                { label: 'Outstanding Qty', data: items.map(item => item.outstanding_qty), backgroundColor: '#fbbf24', borderRadius: 6 },
                { label: 'Incoming Penerimaan PO', data: items.map(item => item.actual_qty), backgroundColor: '#34d399', borderRadius: 6 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#cbd5e1', font: { family: 'Inter', size: 12 } } },
                tooltip: {
                    callbacks: {
                        afterBody(context) {
                            const item = items[context[0].dataIndex];
                            return `Selisih: ${item.selisih >= 0 ? '+' : ''}${item.selisih} unit (${item.status})`;
                        }
                    }
                }
            },
            scales: {
                x: { ticks: { color: '#cbd5e1', font: { family: 'Outfit', weight: 'bold' }, maxRotation: 45, minRotation: 0 }, grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { beginAtZero: true, ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            }
        }
    });
}

if (categoryFilter) {
    if (chartGroups.length === 0) {
        categoryFilter.innerHTML = '<option>Tidak ada data kategori</option>';
        categoryFilter.disabled = true;
    } else {
        chartGroups.forEach((group, index) => {
            const option = document.createElement('option');
            option.value = group.key;
            option.textContent = `${group.label} (${group.item_count} item)`;
            option.selected = index === 0;
            categoryFilter.appendChild(option);
        });
        renderCategoryItems(chartGroups[0]);
        categoryFilter.addEventListener('change', function () {
            renderCategoryItems(chartGroups.find(group => group.key === this.value));
        });
    }
}

// Chart 3: Donut Chart Status Pasokan
const ctxDonut = document.getElementById('chartDonutStatus')?.getContext('2d');
if (ctxDonut) {
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: ['Cukup (> Actual)', 'Pas (= Actual)', 'Kurang (< Actual)'],
            datasets: [{
                data: [{{ $totalCukup }}, {{ $totalPas }}, {{ $totalKurang }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 2,
                borderColor: '#121826'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#cbd5e1', font: { family: 'Inter', size: 11 } } }
            },
            cutout: '68%'
        }
    });
}
</script>
</body>
</html>
