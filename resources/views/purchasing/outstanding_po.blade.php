<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Outstanding PO | PT Kawai Indonesia</title>
    <meta name="description" content="Status Outstanding dan Overstanding Purchase Order PT Kawai Indonesia.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
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
            --text-main: #f3f4f6;
            --text-muted: #cbd5e1;
        }
        * { box-spacing: border-box; }
        body { background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%); color: var(--text-main); font-family: 'Inter', sans-serif; min-height: 100vh; }
        h1,h2,h3,h4,h5,.brand-font { font-family: 'Outfit', sans-serif; }

        /* NAVBAR */
        .top-navbar { background: rgba(18, 24, 38, 0.88); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid var(--card-border); padding: 0.85rem 1.75rem; position: sticky; top: 0; z-index: 1000; }
        .text-muted, .text-secondary { color: #cbd5e1 !important; }

        /* GLASS CARD */
        .glass-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.3); backdrop-filter: blur(12px); }

        /* PAGE HEADER */
        .page-header-title { font-size: 1.7rem; font-weight: 800; background: linear-gradient(135deg, #fff 0%, var(--accent-blue) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* FILTER */
        .filter-select { background: #111827; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; padding: 0.5rem 1rem; font-size: 0.88rem; }
        .filter-select:focus { border-color: var(--accent-blue); outline: none; }

        /* TABLE */
        .table-container { overflow-x: auto; border-radius: 14px; border: 1px solid var(--card-border); background: var(--card-bg); }
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; color: var(--text-main); font-size: 0.88rem; }
        .table-custom thead th { background: rgba(18, 24, 38, 0.95) !important; color: #cbd5e1 !important; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; padding: 0.85rem 1rem; border-bottom: 2px solid rgba(255, 255, 255, 0.12) !important; font-weight: 700; white-space: nowrap; }
        .table-custom tbody tr { border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important; transition: background 0.15s; }
        .table-custom tbody tr:hover td { background: rgba(255, 255, 255, 0.08) !important; color: #ffffff !important; }
        .table-custom td { padding: 0.85rem 1rem; vertical-align: middle; background: rgba(18, 24, 38, 0.45) !important; color: #F3F4F6 !important; }
        
        .badge-status { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .badge-overstanding { background: rgba(16,185,129,0.18); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .badge-outstanding { background: rgba(239,68,68,0.18); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .badge-complete { background: rgba(59,130,246,0.18); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }

        .search-input { background: #111827; border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; border-radius: 8px; font-size: 0.88rem; }
        .search-input:focus { background: #1f2937; border-color: var(--accent-blue); color: #fff; outline: none; }
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
    </div>
    <div>
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.outstanding-po', 'hasFaqModal' => true])
    </div>
</nav>

<div class="container-dashboard py-4">

    <!-- 7-STEP UNIFIED WORKFLOW STEPPER -->
    @include('partials.workflow-stepper', ['currentStep' => 4])

    <!-- STANDARDIZED PAGE HEADER & ACTION HIERARCHY -->
    <div class="kawai-page-header">
        <div class="kawai-page-header-left">
            <div class="page-icon-box" style="background: rgba(0, 210, 255, 0.15); border: 1px solid rgba(0, 210, 255, 0.35);">
                <i class="bi bi-clock-history text-info"></i>
            </div>
            <div>
                <h1 class="page-title-text">Dashboard Outstanding PO</h1>
                <p class="page-subtitle-text">Monitoring sisa pesanan PO tertunda dan realisasi pemenuhan vendor PT Kawai Indonesia.</p>
            </div>
        </div>
        <div class="kawai-page-actions">
            <button type="button" class="btn-kawai-secondary" onclick="window.location.reload()" title="Muat Ulang &amp; Segarkan Kalkulasi">
                <i class="bi bi-arrow-clockwise text-info"></i> Refresh Data
            </button>
            <div class="dropdown">
                <button class="btn-kawai-more dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Opsi Navigasi">
                    <i class="bi bi-three-dots"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark-custom dropdown-menu-end">
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.actual-production') }}">
                            <i class="bi bi-gear-wide-connected text-warning"></i> Ke Aktual Produksi (05)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item-custom" href="{{ route('purchasing.analysis') }}">
                            <i class="bi bi-pie-chart-fill text-danger"></i> Ke Hasil Akhir (07)
                        </a>
                    </li>
                </ul>
            </div>
            @include('partials.kurs-kpi-banner')
        </div>
    </div>

    <!-- FILTER SECTION (VENDOR, PIC BUYER, RENTANG BULAN, NO PO) -->
    <div class="glass-card mb-4 p-3">
        <form method="GET" action="{{ route('purchasing.outstanding-po') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h6 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-funnel-fill text-warning"></i>
                <span>Filter Outstanding PO</span>
            </h6>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small fw-bold mb-0">Vendor:</label>
                    <select name="vendor" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL">-- Semua Vendor --</option>
                        @foreach($availableVendors as $v)
                            <option value="{{ $v }}" {{ ($selectedVendor ?? 'ALL') === $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small fw-bold mb-0">User (PIC):</label>
                    <select name="pic" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL">-- Semua PIC --</option>
                        @foreach($availablePics as $p)
                            <option value="{{ $p }}" {{ ($selectedPic ?? 'ALL') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small fw-bold mb-0">No. PO:</label>
                    <select name="po" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL">-- Semua No. PO --</option>
                        @foreach($availablePoNumbers as $poNum)
                            <option value="{{ $poNum }}" {{ ($selectedPo ?? 'ALL') === $poNum ? 'selected' : '' }}>{{ $poNum }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small fw-bold mb-0">Rentang Bulan:</label>
                    <select name="months_range" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL" {{ ($monthsRange ?? 'ALL') === 'ALL' ? 'selected' : '' }}>-- Semua Bulan --</option>
                        <option value="3" {{ ($monthsRange ?? 'ALL') === '3' ? 'selected' : '' }}>3 Bulan Terakhir</option>
                        <option value="6" {{ ($monthsRange ?? 'ALL') === '6' ? 'selected' : '' }}>6 Bulan Terakhir</option>
                        <option value="12" {{ ($monthsRange ?? 'ALL') === '12' ? 'selected' : '' }}>12 Bulan (1 Tahun)</option>
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small fw-bold mb-0">Pengantaran:</label>
                    <select name="delivery_category" class="filter-select" onchange="this.form.submit()">
                        <option value="ALL">-- Semua Pengantaran --</option>
                        @foreach($deliveryCategories ?? \App\Models\DeliveryCategory::all() as $dc)
                            <option value="{{ $dc->code }}" {{ ($selectedDeliveryCategory ?? 'ALL') === $dc->code ? 'selected' : '' }}>
                                {{ $dc->code }} - {{ $dc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(($selectedVendor ?? 'ALL') !== 'ALL' || ($selectedPic ?? 'ALL') !== 'ALL' || ($selectedPo ?? 'ALL') !== 'ALL' || ($selectedDeliveryCategory ?? 'ALL') !== 'ALL' || ($monthsRange ?? 'ALL') !== 'ALL' || !empty($search))
                    <a href="{{ route('purchasing.outstanding-po') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-x-circle me-1"></i> Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

<!-- ═══ MAIN KPI METRICS ═══ -->
<div class="row g-3 g-xl-4 mb-4">
    {{-- Card 1: Total Line PO --}}
    <div class="col-6 col-lg">
        <div class="kpi-card kpi-card-blue">
            <div class="kpi-header">
                <span class="kpi-title">TOTAL LINE PO DIPANTAU</span>
                <div class="kpi-icon-box icon-blue">
                    <i class="bi bi-list-check"></i>
                </div>
            </div>
            <div class="kpi-value text-white">
                {{ number_format($outstandingData->count()) }} <span class="kpi-unit">Line</span>
            </div>
            <div class="kpi-footer">
                <span class="text-muted small">Baris PO yang sedang dipantau</span>
            </div>
        </div>
    </div>

    {{-- Card 2: Total Qty Dipesan --}}
    <div class="col-6 col-lg">
        <div class="kpi-card kpi-card-gold">
            <div class="kpi-header">
                <span class="kpi-title">TOTAL QTY DIPESAN (PO)</span>
                <div class="kpi-icon-box icon-gold">
                    <i class="bi bi-cart-check"></i>
                </div>
            </div>
            <div class="kpi-value text-white">
                {{ number_format($outstandingData->sum('qty_po'), 0, ',', '.') }} <span class="kpi-unit">Unit</span>
            </div>
            <div class="kpi-footer">
                <span class="text-muted small">Total kuantitas pesanan PO</span>
            </div>
        </div>
    </div>

    {{-- Card 3: Total Qty Diterima --}}
    <div class="col-6 col-lg">
        <div class="kpi-card kpi-card-emerald">
            <div class="kpi-header">
                <span class="kpi-title">TOTAL QTY DITERIMA</span>
                <div class="kpi-icon-box icon-emerald">
                    <i class="bi bi-box-arrow-in-down"></i>
                </div>
            </div>
            <div class="kpi-value text-white">
                {{ number_format($outstandingData->sum('qty_receipt'), 0, ',', '.') }} <span class="kpi-unit">Unit</span>
            </div>
            <div class="kpi-footer">
                <span class="text-muted small">Total penerimaan gudang aktual</span>
            </div>
        </div>
    </div>

    {{-- Card 4: Total Sisa Outstanding --}}
    <div class="col-6 col-lg">
        <div class="kpi-card kpi-card-amber">
            <div class="kpi-header">
                <span class="kpi-title">TOTAL SISA OUTSTANDING</span>
                <div class="kpi-icon-box icon-amber">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="kpi-value text-white">
                {{ number_format($outstandingData->sum('outstanding_qty'), 0, ',', '.') }} <span class="kpi-unit">Unit</span>
            </div>
            <div class="kpi-footer">
                <span class="text-muted small">Sisa belum diterima (clamped ≥ 0)</span>
            </div>
        </div>
    </div>

    {{-- Card 5: Total Over Incoming --}}
    <div class="col-6 col-lg">
        <div class="kpi-card kpi-card-rose">
            <div class="kpi-header">
                <span class="kpi-title">TOTAL OVER INCOMING</span>
                <div class="kpi-icon-box icon-rose">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
            <div class="kpi-value text-white">
                {{ number_format($outstandingData->sum('over_delivery_qty'), 0, ',', '.') }} <span class="kpi-unit">Unit</span>
            </div>
            <div class="kpi-footer">
                <span class="text-muted small">Kelebihan penerimaan melebihi PO</span>
            </div>
        </div>
    </div>
</div>

    <div class="row g-4">
        <!-- CHART PANEL (LEFT) -->
        <div class="col-lg-6">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-bar-chart-fill text-info me-2"></i>Grafik Perbandingan Qty PO vs Qty Receipt</h5>
                    <div>
                        <select id="itemFilter" class="filter-select" onchange="updateOutstandingChart()">
                            <option value="ALL">-- Filter Semua Item --</option>
                            @foreach($availableItemCodes as $itemCode)
                                <option value="{{ $itemCode }}">{{ $itemCode }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="position-relative" style="height: 380px;">
                    <canvas id="outstandingChart"></canvas>
                </div>
            </div>
        </div>

        <!-- OUTSTANDING PO TABLE (RIGHT) -->
        <div class="col-lg-6">
            <div class="glass-card h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-list-task text-info me-2"></i>Katalog Data Incoming PO</h5>
                    <form action="{{ route('purchasing.outstanding-po') }}" method="GET" style="width: 200px;">
                        <input type="text" name="search" class="form-control search-input" placeholder="Cari PO / Item Code..." value="{{ $search }}">
                    </form>
                </div>
                
                <div class="table-container flex-grow-1" style="max-height: 380px; overflow-y: auto;">
                    <table class="table-custom">
                        <thead class="sticky-top">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>No. PO</th>
                                <th>Item Code</th>
                                <th>Supplier & Deskripsi</th>
                                <th class="text-end">Qty PO</th>
                                <th class="text-end">Qty Receipt</th>
                                <th class="text-end">Sisa</th>
                                <th class="text-center">Status / Deviasi</th>
                                <th class="text-center text-nowrap" style="background: rgba(59,130,246,0.15); color: #60a5fa;">Kategori Pengantaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingData as $idx => $row)
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                    <td class="fw-bold text-info">{{ $row->po }}</td>
                                    <td><span class="badge bg-dark border border-secondary px-2 py-1 font-monospace">{{ $row->item_code }}</span></td>
                                    <td>
                                        <div class="fw-semibold text-white fs-7">{{ $row->description }}</div>
                                        <div class="fs-8 text-muted"><i class="bi bi-building me-1"></i>{{ $row->supplier }}</div>
                                        @if(!empty($row->pic_buyer) && $row->pic_buyer !== '-')
                                            <div class="fs-8 text-info mt-0.5"><i class="bi bi-person-fill me-1"></i>PIC: {{ $row->pic_buyer }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-warning">{{ number_format($row->qty_po, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($row->qty_receipt, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold {{ $row->outstanding_qty > 0 ? 'text-danger' : ($row->outstanding_qty < 0 ? 'text-gold' : 'text-muted') }}">
                                        {{ number_format($row->outstanding_qty, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($row->diff > 0)
                                            <span class="badge-status badge-overstanding" title="Muatan berlebih dari item code {{ $row->item_code }}">
                                                +{{ $row->diff }} Overstanding
                                            </span>
                                        @elseif($row->diff < 0)
                                            <span class="badge-status badge-outstanding" title="Kekurangan penerimaan dari item code {{ $row->item_code }}">
                                                {{ $row->diff }} Outstanding
                                            </span>
                                        @else
                                            <span class="badge-status badge-complete">
                                                <i class="bi bi-check-circle-fill"></i> Terpenuhi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">{!! $row->delivery_category_badge ?? '' !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                        Tidak ditemukan data outstanding PO.
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

@include('partials.faq-modal')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- CHART RENDERING AND INTERACTIVE FILTER -->
<script>
    const rawData = @json($outstandingData);
    let chartInstance = null;

    function renderChart(labels, qtyPoData, qtyReceiptData) {
        const ctx = document.getElementById('outstandingChart').getContext('2d');
        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Qty PO (Target)',
                        data: qtyPoData,
                        backgroundColor: 'rgba(245, 158, 11, 0.65)',
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        borderRadius: 6
                    },
                    {
                        label: 'Qty Receipt (Aktual)',
                        data: qtyReceiptData,
                        backgroundColor: 'rgba(16, 185, 129, 0.65)',
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#f3f4f6', font: { family: 'Inter', weight: 600 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(18, 24, 38, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#9ca3af', font: { family: 'Inter' } },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    },
                    y: {
                        ticks: { color: '#9ca3af', font: { family: 'Inter' } },
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function updateOutstandingChart() {
        const selectedItem = document.getElementById('itemFilter').value;
        const dataList = Array.isArray(rawData) ? rawData : Object.values(rawData);
        let labels = [];
        let qtyPoData = [];
        let qtyReceiptData = [];

        if (selectedItem === 'ALL') {
            // Group by Item Code
            const groups = {};
            dataList.forEach(row => {
                if (!groups[row.item_code]) {
                    groups[row.item_code] = { qty_po: 0, qty_receipt: 0 };
                }
                groups[row.item_code].qty_po += row.qty_po;
                groups[row.item_code].qty_receipt += row.qty_receipt;
            });

            labels = Object.keys(groups);
            qtyPoData = labels.map(key => groups[key].qty_po);
            qtyReceiptData = labels.map(key => groups[key].qty_receipt);
        } else {
            // Filter and show PO details for selected Item Code
            const filtered = dataList.filter(row => row.item_code === selectedItem);
            labels = filtered.map(row => row.po);
            qtyPoData = filtered.map(row => row.qty_po);
            qtyReceiptData = filtered.map(row => row.qty_receipt);
        }

        renderChart(labels, qtyPoData, qtyReceiptData);
    }

    // Initialize chart
    document.addEventListener('DOMContentLoaded', () => {
        updateOutstandingChart();
    });
</script>
@include('partials.confirm-modal')
<script src="{{ asset('js/kawai-notify.js') }}"></script>
<script src="{{ asset('js/kawai-ui.js') }}"></script>
</body>
</html>
