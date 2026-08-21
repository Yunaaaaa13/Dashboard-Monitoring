<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Master Outstanding | Purchasing PT Kawai Indonesia</title>
    <meta name="description" content="Master data outstanding material yang belum diterima dari supplier, per Part Number dan Periode.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/kawai-theme.css') }}">
    <style>
        :root {
            --bg-primary: #0a0e17;
            --card-bg: rgba(23,31,48,0.85);
            --card-border: rgba(255,255,255,0.08);
            --accent-gold: #e2b34a;
            --accent-amber: #f59e0b;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
        }
        body { background: radial-gradient(circle at top right, #1a2236 0%, var(--bg-primary) 60%); color: var(--text-main); font-family: 'Inter', sans-serif; min-height: 100vh; }
        h1,h2,h3,h4,h5,.brand-font { font-family: 'Outfit', sans-serif; }
        .top-navbar { background: rgba(18,24,38,0.92); backdrop-filter: blur(14px); border-bottom: 1px solid var(--card-border); padding: 1rem 1.75rem; position: sticky; top: 0; z-index: 1000; }
        .brand-logo-text { font-weight: 800; font-size: 1.25rem; background: linear-gradient(135deg, #fff 0%, var(--accent-gold) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-link-pill { color: var(--text-muted); font-size: 0.82rem; font-weight: 500; padding: 0.4rem 0.9rem; border-radius: 20px; transition: all 0.2s; text-decoration: none; }
        .nav-link-pill:hover { background: rgba(245,158,11,0.15); color: #fcd34d; }
        .nav-link-pill.active-amber { background: rgba(245,158,11,0.18); color: #fbbf24; }
        .glass-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.3); backdrop-filter: blur(12px); }
        .page-header-title { font-size: 1.7rem; font-weight: 800; background: linear-gradient(135deg, #fff 0%, var(--accent-amber) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .btn-add { background: linear-gradient(135deg, var(--accent-amber), #d97706); border: none; color: #fff; font-weight: 600; padding: 0.55rem 1.25rem; border-radius: 10px; transition: all 0.25s; font-size: 0.88rem; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(245,158,11,0.4); color:#fff; }
        .filter-select { background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); color: #fff; border-radius: 10px; padding: 0.45rem 1rem; font-size: 0.88rem; }
        .filter-select option { background: #1e2a3a; color: #fff; }
        .table-custom { color: var(--text-main); font-size: 0.88rem; }
        .table-custom thead th { background: rgba(245,158,11,0.12); color: #fbbf24; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid rgba(255,255,255,0.08); padding: 0.85rem 1rem; white-space: nowrap; }
        .table-custom tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.15s; }
        .table-custom tbody tr:hover { background: rgba(255,255,255,0.04); }
        .table-custom tbody td { padding: 0.8rem 1rem; vertical-align: middle; }
        .badge-periode { background: rgba(245,158,11,0.18); color: #fbbf24; border: 1px solid rgba(245,158,11,0.35); border-radius: 20px; font-size: 0.75rem; padding: 0.25rem 0.75rem; font-weight: 600; }
        .badge-qty { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem; color: #fbbf24; }
        .badge-po { background: rgba(99,102,241,0.18); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); border-radius: 6px; font-size: 0.75rem; padding: 0.2rem 0.6rem; font-weight: 600; }
        .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.78rem; transition: all 0.2s; cursor: pointer; }
        .btn-icon-edit { background: rgba(59,130,246,0.2); color: #93c5fd; }
        .btn-icon-edit:hover { background: rgba(59,130,246,0.4); }
        .btn-icon-del { background: rgba(239,68,68,0.2); color: #f87171; }
        .btn-icon-del:hover { background: rgba(239,68,68,0.4); }
        .empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }
        .kpi-mini { display: flex; align-items: center; gap: 0.6rem; }
        .kpi-mini .kpi-num { font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 800; color: #fbbf24; }
        .modal-content { background: #1a2236; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; color: var(--text-main); }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,0.08); }
        .modal-footer { border-top: 1px solid rgba(255,255,255,0.08); }
        .form-control, .form-select { background: #1e293b !important; border: 1px solid rgba(255,255,255,0.12); color: #fff; border-radius: 10px; }
        .form-control:focus { background: #1e293b !important; border-color: var(--accent-amber); box-shadow: 0 0 0 3px rgba(245,158,11,0.2); color: #fff; }
        .form-control::placeholder { color: rgba(255,255,255,0.35); }
        .form-label { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .toast-alert { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; min-width: 280px; border-radius: 12px; padding: 1rem 1.25rem; font-weight: 500; font-size: 0.88rem; display: none; box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
        .toast-success { background: rgba(16,185,129,0.95); color: #fff; }
        .toast-error { background: rgba(239,68,68,0.95); color: #fff; }
    </style>
</head>
<body>
<nav class="top-navbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard.overview') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-music-note-beamed text-warning fs-4"></i>
            <span class="brand-logo-text" style="font-weight: 800; font-size: 1.25rem; letter-spacing: 0.04em; background: linear-gradient(135deg, #ffffff 0%, #e2b34a 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;">PT KAWAI INDONESIA</span>
        </a>
        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold" style="font-size:0.72rem;">
            <i class="bi bi-4-circle-fill me-1"></i> MASTER OUTSTANDING
        </span>
    </div>
    <div>
        @include('partials.pill-nav', ['activeRoute' => 'purchasing.master.outstanding', 'hasFaqModal' => true])
    </div>
</nav>

@include('partials.faq-modal')

<div class="container-fluid px-4 py-4">

    @include('partials.toast-and-notification-popup')

    <!-- 6-STEP WORKFLOW STEPPER BANNER -->
    <div class="glass-card p-3 mb-4" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.08) 100%); border: 1px solid rgba(245, 158, 11, 0.3);">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill" style="font-size: 0.75rem;">STEP 4: ACTIVE</span>
                <h5 class="fw-bold text-white mb-0 brand-font"><i class="bi bi-4-circle-fill text-warning me-2"></i>Master Outstanding</h5>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('purchasing.outstanding') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">1</span> Forecast
                </a>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <a href="{{ route('purchasing.master-po') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">2</span> Master PO
                </a>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <a href="{{ route('purchasing.input') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">3</span> Realisasi
                </a>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <div class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-warning bg-opacity-25 border border-warning border-opacity-50 text-white small fw-bold text-dark">
                    <span class="badge bg-warning text-dark rounded-circle">4</span> Outstanding
                </div>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <a href="{{ route('purchasing.actual-production') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">5</span> Aktual Prod
                </a>
                <i class="bi bi-chevron-right text-muted d-none d-sm-inline"></i>
                <a href="{{ route('purchasing.analysis') }}" class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-muted small text-decoration-none hover-white">
                    <span class="badge bg-secondary rounded-circle">6</span> Hasil Akhir
                </a>
            </div>
        </div>
    </div>
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-header-title mb-1">Master Outstanding</h1>
            <p class="mb-0" style="color:var(--text-muted);font-size:0.88rem;"><i class="fa fa-info-circle me-1"></i>Material yang belum diterima dari supplier.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btnBulkDeleteMasterOutstanding" class="btn btn-danger btn-sm rounded-pill px-3 d-none" onclick="confirmBulkDeleteMasterOutstanding()">
                <i class="fa fa-trash me-1"></i> Hapus Terpilih (<span id="bulkDeleteCountMasterOutstanding">0</span>)
            </button>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#modalAddOutstanding">
                <i class="fa fa-plus me-1"></i> Tambah Outstanding
            </button>
        </div>
    </div>

    <div class="glass-card mb-4">
        <form method="GET" action="{{ route('purchasing.master.outstanding') }}" class="d-flex gap-3 align-items-center flex-wrap">
            <label class="form-label mb-0" style="font-size:0.8rem;color:var(--text-muted);">Filter Periode:</label>
            <select name="periode" class="filter-select" onchange="this.form.submit()">
                <option value="">-- Semua Periode --</option>
                @foreach($availablePeriodes as $p)
                    <option value="{{ $p }}" {{ $periode == $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
            @if($periode)<a href="{{ route('purchasing.master.outstanding') }}" class="nav-link-pill" style="font-size:0.8rem;"><i class="fa fa-times me-1"></i>Reset</a>@endif
            <div class="ms-auto kpi-mini">
                <span class="kpi-num">{{ $outstandings->count() }}</span>
                <span style="color:var(--text-muted);font-size:0.82rem;">No. PO / Ref</span>
            </div>
        </form>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-custom table-borderless">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <input type="checkbox" id="checkAllMasterOutstanding" class="form-check-input">
                        </th>
                        <th>#</th>
                        <th>No. PO / Ref</th>
                        <th>Description</th>
                        <th>Periode</th>
                        <th>Item Code / PO</th>
                        <th>Outstanding Qty</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outstandings as $i => $row)
                    <tr data-id="{{ $row->id }}">
                        <td class="text-center">
                            <input type="checkbox" class="row-checkbox-masteroutstanding form-check-input" value="{{ $row->id }}">
                        </td>
                        <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                        <td><strong style="color:#e2e8f0;font-family:'Outfit',sans-serif;">{{ $row->part_number }}</strong></td>
                        <td style="color:var(--text-muted);">{{ $row->description ?? '-' }}</td>
                        <td><span class="badge-periode">{{ $row->periode ?? $row->period_month }}</span></td>
                        <td>
                            @if($row->po)
                                <span class="badge-po">{{ $row->po }}</span>
                            @else
                                <span style="color:rgba(255,255,255,0.25)">-</span>
                            @endif
                        </td>
                        <td><span class="badge-qty">{{ number_format($row->outstanding_qty) }}</span></td>
                        <td>
                            <button class="btn-icon btn-icon-edit me-1"
                                onclick="openEditModal({{ $row->id }},'{{ $row->part_number }}','{{ addslashes($row->description ?? '') }}','{{ $row->periode ?? $row->period_month }}','{{ $row->po ?? '' }}',{{ $row->outstanding_qty }})"
                                title="Edit"><i class="fa fa-pen"></i></button>
                            <button class="btn-icon btn-icon-del"
                                onclick="confirmDelete({{ $row->id }},'{{ $row->part_number }}')"
                                title="Hapus"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8">
                        <div class="empty-state">
                            <i class="fa fa-box-open fa-2x mb-2" style="color:rgba(255,255,255,0.15)"></i>
                            <p class="mb-0">Belum ada data Outstanding untuk periode ini.</p>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL ADD --}}
<div class="modal fade" id="modalAddOutstanding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font"><i class="fa fa-plus-circle me-2" style="color:#fbbf24"></i>Tambah Data Outstanding</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddOutstanding" method="POST" action="{{ route('purchasing.master.outstanding.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">No. PO / Ref (Part Number) <span style="color:#f87171">*</span></label>
                            <input type="text" name="part_number" class="form-control" placeholder="cth: PO-KW-0726" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Nama / deskripsi material">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Periode (YYYY-MM) <span style="color:#f87171">*</span></label>
                            <input type="month" name="periode" class="form-control" value="{{ $periode ?: now()->format('Y-m') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Item Code / PO</label>
                            <input type="text" name="po" class="form-control" placeholder="cth: ITM-001">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Outstanding Qty <span style="color:#f87171">*</span></label>
                            <input type="number" name="outstanding_qty" class="form-control" placeholder="0" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-add" id="btn-submit-add"><i class="fa fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEditOutstanding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font"><i class="fa fa-pen me-2" style="color:#fbbf24"></i>Edit Data Outstanding</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditOutstanding" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">No. PO / Ref (Part Number)</label>
                            <input type="text" id="edit_part_number" class="form-control" readonly style="opacity:0.6">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" id="edit_description" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Periode</label>
                            <input type="month" name="periode" id="edit_periode" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. PO</label>
                            <input type="text" name="po" id="edit_po" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Outstanding Qty</label>
                            <input type="number" name="outstanding_qty" id="edit_outstanding_qty" class="form-control" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-add"><i class="fa fa-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DELETE --}}
<div class="modal fade" id="modalDeleteOutstanding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title brand-font" style="color:#f87171"><i class="fa fa-trash me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Hapus data outstanding untuk <strong id="del_part_name"></strong>?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="formDeleteOutstanding" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash me-1"></i>Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="toast-alert toast-success" id="toastSuccess"><i class="fa fa-check-circle me-2"></i><span id="toastSuccessMsg"></span></div>
<div class="toast-alert toast-error" id="toastError"><i class="fa fa-times-circle me-2"></i><span id="toastErrorMsg"></span></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showToast(type, msg) {
    const el = document.getElementById(type === 'success' ? 'toastSuccess' : 'toastError');
    document.getElementById(type === 'success' ? 'toastSuccessMsg' : 'toastErrorMsg').textContent = msg;
    el.style.display = 'block'; setTimeout(() => el.style.display = 'none', 3500);
}
function openEditModal(id, partNumber, description, periode, po, outstandingQty) {
    document.getElementById('edit_part_number').value = partNumber;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_periode').value = periode;
    document.getElementById('edit_po').value = po;
    document.getElementById('edit_outstanding_qty').value = outstandingQty;
    document.getElementById('formEditOutstanding').action = '/purchasing/master/outstanding/' + id;
    new bootstrap.Modal(document.getElementById('modalEditOutstanding')).show();
}
function confirmDelete(id, partNumber) {
    document.getElementById('del_part_name').textContent = partNumber;
    document.getElementById('formDeleteOutstanding').action = '/purchasing/master/outstanding/' + id;
    new bootstrap.Modal(document.getElementById('modalDeleteOutstanding')).show();
}
document.getElementById('formAddOutstanding').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-add');
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Menyimpan...'; btn.disabled = true;
    fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json()).then(res => {
            btn.innerHTML = '<i class="fa fa-save me-1"></i>Simpan'; btn.disabled = false;
            if (res.success) { bootstrap.Modal.getInstance(document.getElementById('modalAddOutstanding')).hide(); showToast('success', res.message || 'Data berhasil disimpan'); setTimeout(() => location.reload(), 900); }
            else showToast('error', res.message || 'Gagal menyimpan');
        }).catch(() => { btn.innerHTML = '<i class="fa fa-save me-1"></i>Simpan'; btn.disabled = false; showToast('error', 'Kesalahan jaringan'); });
});
</script>


<!-- Modal Bulk Delete Confirmation Master Outstanding -->
<div class="modal fade" id="modalBulkDeleteMasterOutstandingConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-danger text-white" style="background: #111827;">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-danger fw-bold"><i class="fa fa-exclamation-triangle me-2"></i> Konfirmasi Hapus Massal Master Outstanding</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchasing.master.outstanding.destroy-bulk') }}" method="POST" id="formBulkDeleteMasterOutstanding">
                @csrf
                <div class="modal-body">
                    <div id="bulkDeleteMasterOutstandingIdsContainer"></div>
                    Apakah Anda yakin ingin menghapus <strong id="bulkDeleteMasterOutstandingCountText" class="text-danger">0</strong> data Outstanding terpilih?
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
        const checkAllMasterOutstanding = document.getElementById('checkAllMasterOutstanding');
        const rowCheckboxesMasterOutstanding = document.querySelectorAll('.row-checkbox-masteroutstanding');
        const btnBulkDeleteMasterOutstanding = document.getElementById('btnBulkDeleteMasterOutstanding');
        const countSpanMasterOutstanding = document.getElementById('bulkDeleteCountMasterOutstanding');

        function updateMasterOutstandingBulkBtn() {
            const checked = document.querySelectorAll('.row-checkbox-masteroutstanding:checked');
            if (btnBulkDeleteMasterOutstanding) {
                if (checked.length > 0) {
                    btnBulkDeleteMasterOutstanding.classList.remove('d-none');
                    countSpanMasterOutstanding.innerText = checked.length;
                } else {
                    btnBulkDeleteMasterOutstanding.classList.add('d-none');
                }
            }
        }

        if (checkAllMasterOutstanding) {
            checkAllMasterOutstanding.addEventListener('change', function() {
                rowCheckboxesMasterOutstanding.forEach(cb => cb.checked = this.checked);
                updateMasterOutstandingBulkBtn();
            });
        }

        rowCheckboxesMasterOutstanding.forEach(cb => {
            cb.addEventListener('change', function() {
                if (checkAllMasterOutstanding) {
                    checkAllMasterOutstanding.checked = (document.querySelectorAll('.row-checkbox-masteroutstanding:checked').length === rowCheckboxesMasterOutstanding.length);
                }
                updateMasterOutstandingBulkBtn();
            });
        });
    });

    function confirmBulkDeleteMasterOutstanding() {
        const checked = document.querySelectorAll('.row-checkbox-masteroutstanding:checked');
        if (checked.length === 0) return;
        
        const container = document.getElementById('bulkDeleteMasterOutstandingIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('bulkDeleteMasterOutstandingCountText').innerText = checked.length;
        new bootstrap.Modal(document.getElementById('modalBulkDeleteMasterOutstandingConfirm')).show();
    }
</script>
</body>
</html>
