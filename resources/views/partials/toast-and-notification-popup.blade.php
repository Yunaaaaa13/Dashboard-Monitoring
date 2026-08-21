@php
    // Query untuk mengambil peringatan aktif secara real-time dari database agar tidak menumpuk di UI utama
    $pendingOutstandingsCount = \App\Models\PurchasingOutstanding::whereColumn('complete', '<', 'order_qty')->count();
    $pendingOutstandingsList = \App\Models\PurchasingOutstanding::whereColumn('complete', '<', 'order_qty')
                                ->orderBy('updated_at', 'desc')
                                ->take(20)
                                ->get();
    
    // Agregasi Log Realisasi berdasarkan (po_reference + purchasing_category_id + period_month)
    $allRawLogsNotif = \App\Models\PurchasingLog::with('category')
                        ->orderBy('updated_at', 'desc')
                        ->get();

    $groupedDiscrepancies = [];

    $groupedLogsNotif = $allRawLogsNotif->groupBy(function ($l) {
        $poRef  = trim($l->po_reference ?: ($l->item_code ?: 'NO_REF'));
        $item   = trim($l->item_code ?: 'NO_ITEM');
        $period = $l->period_month ?: 'NO_PER';
        return $poRef . '_' . $item . '_' . $l->purchasing_category_id . '_' . $period;
    });

    foreach ($groupedLogsNotif as $gKey => $lGroup) {
        $first = $lGroup->first();
        $poRef = $first->po_reference ?: ($first->item_code ?: 'No Ref');
        
        $targetOrder = $lGroup->max('target_order');
        if ($targetOrder <= 0 && $first->category) {
            $targetOrder = $first->category->monthly_target_units;
        }

        $totalReceived = $lGroup->sum('actual_received');

        // Masukkan ke notifikasi hanya jika ada selisih kumulatif vs Target PO
        if ($targetOrder > 0 && $totalReceived != $targetOrder) {
            $diffVal = abs($targetOrder - $totalReceived);
            $isUnder = $totalReceived < $targetOrder;

            $groupedDiscrepancies[] = (object) [
                'po_reference'    => $poRef,
                'category_code'   => $first->category->category_code ?? '-',
                'category_name'   => $first->category->category_name ?? 'Material',
                'period_month'    => $first->period_month,
                'target_order'    => $targetOrder,
                'actual_received' => $totalReceived,
                'diff_val'        => $diffVal,
                'is_under'        => $isUnder,
                'trans_count'     => $lGroup->count(),
                'updated_at'      => $lGroup->max('updated_at'),
            ];
        }
    }

    $discrepancyLogsCount = count($groupedDiscrepancies);
    $discrepancyLogsList  = collect($groupedDiscrepancies)->take(20);
    
    $totalNotifCount = $pendingOutstandingsCount + $discrepancyLogsCount + (session('warning') ? 1 : 0);
@endphp

<!-- TOMBOL FLOATING NOTIFIKASI COMPACT (FAB) DI POJOK KANAN BAWAH -->
<div style="position: fixed; bottom: 24px; right: 20px; z-index: 10700;">
    <button type="button"
        class="fab-notif-btn"
        data-bs-toggle="modal"
        data-bs-target="#modalNotificationCenter"
        title="Pusat Notifikasi Peringatan PO">
        <i class="fa-solid fa-bell"></i>
        @if($totalNotifCount > 0)
            <span class="fab-notif-badge">{{ $totalNotifCount }}</span>
        @endif
    </button>
</div>

<style>
/* Floating Toast Container - Di pojok kanan atas, tidak menggeser layout halaman */
.floating-toast-container {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 10800;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 440px;
    width: calc(100vw - 48px);
    pointer-events: none;
}

/* Glassmorphism Toast Card */
.floating-toast {
    pointer-events: auto;
    background: rgba(20, 27, 45, 0.95);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-radius: 14px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.65), 0 0 18px rgba(255, 255, 255, 0.06);
    overflow: hidden;
    transform: translateX(120%);
    opacity: 0;
    transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.5s ease;
}

.floating-toast.show-toast {
    transform: translateX(0);
    opacity: 1;
}

.floating-toast.hide-toast {
    transform: translateX(120%);
    opacity: 0;
}

.floating-toast-success {
    border: 1px solid rgba(16, 185, 129, 0.5);
    border-left: 5px solid #10b981;
}

.floating-toast-warning {
    border: 1px solid rgba(245, 158, 11, 0.5);
    border-left: 5px solid #f59e0b;
}

.floating-toast-error {
    border: 1px solid rgba(239, 68, 68, 0.5);
    border-left: 5px solid #ef4444;
}

/* Toast Progress Bar */
.toast-progress {
    height: 3px;
    width: 100%;
    background: rgba(255, 255, 255, 0.1);
}

.toast-progress-bar {
    height: 100%;
    width: 100%;
    transition: width linear;
}

.toast-progress-success { background: #10b981; }
.toast-progress-warning { background: #f59e0b; }
.toast-progress-error { background: #ef4444; }

/* Notification Modal Customization & Absolute Z-Index Priority */
#modalNotificationCenter {
    z-index: 10900 !important;
}
body.modal-open #modalNotificationCenter {
    z-index: 10900 !important;
}
#modalNotificationCenter + .modal-backdrop {
    z-index: 10890 !important;
}

#modalNotificationCenter .modal-content {
    background: #0f1623 !important;
    border: 1px solid rgba(226, 179, 74, 0.45);
    border-radius: 18px;
    color: #f1f5f9 !important;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9);
}

#modalNotificationCenter .modal-header {
    background: rgba(226, 179, 74, 0.06);
    border-bottom: 1px solid rgba(226, 179, 74, 0.2) !important;
}

#modalNotificationCenter .modal-footer {
    background: rgba(10, 14, 23, 0.5);
    border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
}

#modalNotificationCenter .modal-body {
    background: transparent;
    color: #f1f5f9 !important;
}

#modalNotificationCenter .text-muted {
    color: #94a3b8 !important;
}

#modalNotificationCenter h5.modal-title {
    color: #fbbf24 !important;
    font-weight: 700 !important;
}

.notif-item-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 12px 14px;
    transition: all 0.2s ease;
}

.notif-item-card:hover {
    background: rgba(226, 179, 74, 0.1);
    border-color: rgba(226, 179, 74, 0.35);
}

.notif-item-card .fw-bold {
    color: #f8fafc !important;
}

.notif-item-card .text-white {
    color: #f8fafc !important;
}

.notif-item-card small, .notif-item-card .small {
    color: #94a3b8 !important;
}

/* Nav pills custom untuk dark modal */
#modalNotificationCenter .nav-pills .nav-link {
    color: #cbd5e1;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
}

#modalNotificationCenter .nav-pills .nav-link.active {
    background: #2563eb;
    color: #fff;
    border-color: transparent;
}

/* ===== FLOATING FAB NOTIFICATION BUTTON ===== */
.fab-notif-btn {
    position: relative;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border: 2px solid rgba(255,255,255,0.2);
    color: #1a1a1a;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 18px rgba(245, 158, 11, 0.55);
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    animation: fabPulse 2.5s ease-in-out infinite;
}

.fab-notif-btn:hover {
    transform: scale(1.12);
    box-shadow: 0 6px 24px rgba(245, 158, 11, 0.75);
}

.fab-notif-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    border: 2px solid #0f1623;
    line-height: 1;
}

@keyframes fabPulse {
    0%, 100% { box-shadow: 0 4px 18px rgba(245, 158, 11, 0.55); }
    50%       { box-shadow: 0 4px 28px rgba(245, 158, 11, 0.9); }
}

/* Scrollbar di dalam modal notifikasi */
.notif-scroll-area {
    max-height: 480px;
    overflow-y: auto;
}
.notif-scroll-area::-webkit-scrollbar {
    width: 6px;
}
.notif-scroll-area::-webkit-scrollbar-track {
    background: rgba(10, 14, 23, 0.5);
    border-radius: 4px;
}
.notif-scroll-area::-webkit-scrollbar-thumb {
    background: rgba(226, 179, 74, 0.4);
    border-radius: 4px;
}

/* Custom Tab Button for Notification Modal */
.notif-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #94a3b8;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.notif-tab-btn:hover {
    background: rgba(255,255,255,0.08);
    color: #e2e8f0;
    border-color: rgba(255,255,255,0.18);
}

.notif-tab-btn.active {
    background: rgba(251,191,36,0.15);
    color: #fbbf24;
    border-color: rgba(251,191,36,0.4);
}
</style>

<!-- FLOATING TOAST CONTAINER -->
<div class="floating-toast-container" id="floatingToastContainer">
    @if(session('success'))
        <div class="floating-toast floating-toast-success" id="toastSuccess">
            <div class="p-3 d-flex align-items-start gap-3">
                <div class="fs-4 text-success mt-1">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold mb-1" style="font-size: 0.92rem; color: #f8fafc;">Berhasil Disimpan</div>
                    <div style="font-size: 0.84rem; line-height: 1.4; color: #e2e8f0;">{!! session('success') !!}</div>
                </div>
                <button type="button" class="btn-close btn-close-white small ms-1" onclick="closeToast('toastSuccess')" aria-label="Close"></button>
            </div>
            <div class="toast-progress">
                <div class="toast-progress-bar toast-progress-success"></div>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="floating-toast floating-toast-warning" id="toastWarning">
            <div class="p-3 d-flex align-items-start gap-3">
                <div class="fs-4 text-warning mt-1">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold mb-1" style="font-size: 0.92rem; color: #fbbf24;">Peringatan Selisih / Outstanding</div>
                    <div class="mb-2" style="font-size: 0.84rem; line-height: 1.4; color: #e2e8f0;">{!! session('warning') !!}</div>
                    <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 py-1 fw-bold" style="font-size: 0.76rem;" data-bs-toggle="modal" data-bs-target="#modalNotificationCenter" onclick="closeToast('toastWarning')">
                        <i class="fa-solid fa-bell me-1"></i> Lihat Semua Peringatan PO ({{ $totalNotifCount }})
                    </button>
                </div>
                <button type="button" class="btn-close btn-close-white small ms-1" onclick="closeToast('toastWarning')" aria-label="Close"></button>
            </div>
            <div class="toast-progress">
                <div class="toast-progress-bar toast-progress-warning"></div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="floating-toast floating-toast-error" id="toastError">
            <div class="p-3 d-flex align-items-start gap-3">
                <div class="fs-4 text-danger mt-1">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold text-danger mb-1" style="font-size: 0.92rem;">Terjadi Kesalahan</div>
                    <div class="text-light" style="font-size: 0.84rem; line-height: 1.4;">{!! session('error') !!}</div>
                </div>
                <button type="button" class="btn-close btn-close-white small ms-1" onclick="closeToast('toastError')" aria-label="Close"></button>
            </div>
            <div class="toast-progress">
                <div class="toast-progress-bar toast-progress-error"></div>
            </div>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="floating-toast floating-toast-error" id="toastValidationErrors">
            <div class="p-3 d-flex align-items-start gap-3">
                <div class="fs-4 text-danger mt-1">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold text-danger mb-1" style="font-size: 0.92rem;">Validasi Data Gagal</div>
                    <ul class="mb-0 ps-3 text-light" style="font-size: 0.82rem; line-height: 1.4;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white small ms-1" onclick="closeToast('toastValidationErrors')" aria-label="Close"></button>
            </div>
            <div class="toast-progress">
                <div class="toast-progress-bar toast-progress-error"></div>
            </div>
        </div>
    @endif
</div>

<!-- MODAL POP-UP NOTIFIKASI PUSAT PERINGATAN -->
<div class="modal fade" id="modalNotificationCenter" tabindex="-1" aria-labelledby="modalNotificationCenterLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #0d1526; border: 1px solid rgba(251,191,36,0.3); border-radius: 16px; overflow: hidden; box-shadow: 0 30px 80px rgba(0,0,0,0.85);">

            <!-- Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, rgba(251,191,36,0.12), rgba(251,191,36,0.04)); border-bottom: 1px solid rgba(251,191,36,0.2); padding: 16px 20px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:38px; height:38px; border-radius:50%; background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.35); display:flex; align-items:center; justify-content:center;">
                        <i class="fa-solid fa-bell" style="color:#fbbf24; font-size:1rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:1rem; font-weight:700; color:#fbbf24; letter-spacing:0.01em;" id="modalNotificationCenterLabel">Pusat Notifikasi Peringatan PO</div>
                        <div style="font-size:0.76rem; color:#64748b; margin-top:1px;">Ringkasan selisih Target vs Incoming antar nomor PO</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0.75);"></button>
            </div>

            <!-- Body -->
            <div class="modal-body notif-scroll-area" style="padding: 16px 20px; background: transparent;">
                @if(session('warning'))
                    <div style="background: rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.35); border-radius:10px; padding: 12px 14px; margin-bottom: 16px;">
                        <div style="font-size:0.82rem; font-weight:700; color:#fbbf24; margin-bottom:4px;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Peringatan Aktivitas Baru Saja
                        </div>
                        <div style="font-size:0.8rem; color:#e2e8f0; line-height:1.5;">{!! session('warning') !!}</div>
                    </div>
                @endif

                <!-- Tab Navigation -->
                <div class="d-flex gap-2 mb-3">
                    <button class="notif-tab-btn active" id="tab-notif-outstanding-tab" type="button" onclick="switchNotifTab('tab-notif-outstanding', this)">
                        <i class="fa-solid fa-list-check me-1"></i> Outstanding Pending
                        <span style="background:rgba(245,158,11,0.25); color:#fbbf24; border-radius:20px; padding:1px 7px; font-size:0.72rem; font-weight:700; margin-left:4px;">{{ $pendingOutstandingsCount }}</span>
                    </button>
                    <button class="notif-tab-btn" id="tab-notif-discrepancy-tab" type="button" onclick="switchNotifTab('tab-notif-discrepancy', this)">
                        <i class="fa-solid fa-scale-unbalanced me-1"></i> Selisih Incoming
                        <span style="background:rgba(59,130,246,0.2); color:#93c5fd; border-radius:20px; padding:1px 7px; font-size:0.72rem; font-weight:700; margin-left:4px;">{{ $discrepancyLogsCount }}</span>
                    </button>
                </div>

                <div id="notifTabContent">
                    <!-- TAB 1: OUTSTANDING PENDING -->
                    <div id="tab-notif-outstanding" style="display:block;">
                        @forelse($pendingOutstandingsList as $out)
                            <div class="notif-item-card mb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-warning text-dark font-monospace fw-bold">{{ $out->po_number ?: 'PO-KP3' }}</span>
                                        <span class="badge bg-secondary text-white font-monospace">{{ $out->part_number }}</span>
                                        <span style="font-size: 0.72rem; color: #94a3b8;">Diperbarui {{ $out->updated_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="fw-semibold mb-1" style="color: #f8fafc; font-size: 0.9rem;">{{ $out->description }}</div>
                                    <div style="font-size: 0.78rem; color: #94a3b8;">
                                        Target Order: <strong style="color: #e2e8f0;">{{ number_format($out->order_qty) }} unit</strong> | 
                                        Diterima/Complete: <strong style="color: #fbbf24;">{{ number_format($out->complete) }} unit</strong>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @php $diffOut = $out->order_qty - $out->complete; @endphp
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-3 py-1 mb-1 d-block" style="font-size: 0.76rem;">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Pending {{ number_format($diffOut) }} unit
                                    </span>
                                    <a href="{{ route('purchasing.outstanding') }}" class="btn btn-outline-light btn-sm rounded-pill px-3 py-0" style="font-size: 0.72rem;">
                                        Pantau <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-check fs-3 text-success mb-2 d-block"></i>
                                Semua order outstanding telah terpenuhi 100% (Complete).
                            </div>
                        @endforelse
                    </div>

                    <!-- TAB 2: DISCREPANCY INCOMING BULANAN -->
                    <div id="tab-notif-discrepancy" style="display:none;">
                        @forelse($discrepancyLogsList as $log)
                            <div class="notif-item-card mb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <span class="badge bg-info text-dark font-monospace fw-bold">{{ $log->po_reference }}</span>
                                        <span class="badge bg-secondary text-white font-monospace">{{ $log->category_code }}</span>
                                        <span class="badge bg-secondary bg-opacity-50" style="color: #f1f5f9;">{{ \Carbon\Carbon::parse($log->period_month)->translatedFormat('F Y') }}</span>
                                        @if($log->trans_count > 1)
                                            <span class="badge bg-dark border border-secondary text-warning font-monospace" style="font-size: 0.68rem;">
                                                <i class="fa-solid fa-layer-group me-1"></i>{{ $log->trans_count }}x Influx Date
                                            </span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.78rem; color: #94a3b8;">
                                        Target PO: <strong style="color: #e2e8f0;">{{ number_format($log->target_order) }} unit</strong> | 
                                        Total Masuk: <strong style="color: #67e8f9;">{{ number_format($log->actual_received) }} unit</strong>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($log->is_under)
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-3 py-1 mb-1 d-block" style="font-size: 0.76rem;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Kurang {{ number_format($log->diff_val) }} unit
                                        </span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-3 py-1 mb-1 d-block" style="font-size: 0.76rem;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Surplus +{{ number_format($log->diff_val) }} unit
                                        </span>
                                    @endif
                                    <a href="{{ route('purchasing.input') }}" class="btn btn-outline-light btn-sm rounded-pill px-3 py-0" style="font-size: 0.72rem;">
                                        Lihat Audit <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-check fs-3 text-success mb-2 d-block"></i>
                                Tidak ada catatan selisih incoming penerimaan bulanan.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.08); padding: 12px 20px; background: rgba(10,14,23,0.4);">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" style="font-size:0.82rem;">Tutup</button>
                <a href="{{ route('purchasing.outstanding') }}" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold" style="font-size:0.82rem;">
                    <i class="fa-solid fa-list-check me-1"></i> Kelola Outstanding Order
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== SWEETALERT2 POP-UP NOTIFIKASI SELESAI DI SETIAP DASHBOARD =====
    @if(session('success') && session('warning'))
        Swal.fire({
            title: '<span style="color: #fbbf24; font-family: Outfit, sans-serif; font-weight: 700;">Berhasil Disimpan & Catatan PO</span>',
            html: '<div style="color: #f8fafc; font-size: 0.95rem; line-height: 1.5;" class="text-start">' +
                  '<div class="p-3 mb-3 rounded-3" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7;"><i class="fa-solid fa-circle-check me-2"></i>' + @json(session('success')) + '</div>' +
                  '<div class="p-3 rounded-3" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #fcd34d;">' + @json(session('warning')) + '</div>' +
                  '</div>',
            icon: 'success',
            background: '#0f1623',
            iconColor: '#10b981',
            confirmButtonText: '<i class="fa-solid fa-check me-2"></i>Tutup & Lanjutkan',
            confirmButtonColor: '#e2b34a',
            customClass: {
                popup: 'border border-warning border-opacity-50 rounded-4 shadow-lg py-4 px-3',
                confirmButton: 'btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark'
            },
            buttonsStyling: false
        });
    @elseif(session('success'))
        Swal.fire({
            title: '<span style="color: #fbbf24; font-family: Outfit, sans-serif; font-weight: 700;">Berhasil & Selesai!</span>',
            html: '<div style="color: #f8fafc; font-size: 0.95rem; line-height: 1.5;">' + @json(session('success')) + '</div>',
            icon: 'success',
            background: '#0f1623',
            iconColor: '#10b981',
            confirmButtonText: '<i class="fa-solid fa-check me-2"></i>Tutup & Lanjutkan',
            confirmButtonColor: '#e2b34a',
            customClass: {
                popup: 'border border-warning border-opacity-50 rounded-4 shadow-lg py-4 px-3',
                confirmButton: 'btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark'
            },
            buttonsStyling: false
        });
    @elseif(session('warning'))
        Swal.fire({
            title: '<span style="color: #fbbf24; font-family: Outfit, sans-serif; font-weight: 700;">Peringatan PO / Outstanding!</span>',
            html: '<div style="color: #f8fafc; font-size: 0.95rem; line-height: 1.5;">' + @json(session('warning')) + '</div>',
            icon: 'warning',
            background: '#0f1623',
            iconColor: '#f59e0b',
            confirmButtonText: '<i class="fa-solid fa-bell me-2"></i>Lihat Detail di Pusat Peringatan',
            showCancelButton: true,
            cancelButtonText: 'Tutup',
            customClass: {
                popup: 'border border-warning border-opacity-50 rounded-4 shadow-lg py-4 px-3',
                confirmButton: 'btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark me-2',
                cancelButton: 'btn btn-outline-light rounded-pill px-4 py-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const modalNotif = new bootstrap.Modal(document.getElementById('modalNotificationCenter'));
                modalNotif.show();
            }
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: '<span style="color: #ef4444; font-family: Outfit, sans-serif; font-weight: 700;">Terjadi Kesalahan!</span>',
            html: '<div style="color: #f8fafc; font-size: 0.95rem; line-height: 1.5;">' + @json(session('error')) + '</div>',
            icon: 'error',
            background: '#0f1623',
            iconColor: '#ef4444',
            confirmButtonText: '<i class="fa-solid fa-xmark me-2"></i>Tutup',
            confirmButtonColor: '#ef4444',
            customClass: {
                popup: 'border border-danger border-opacity-50 rounded-4 shadow-lg py-4 px-3',
                confirmButton: 'btn btn-danger rounded-pill px-4 py-2 fw-bold text-white'
            },
            buttonsStyling: false
        });
    @endif

    @if(isset($errors) && $errors->any())
        Swal.fire({
            title: '<span style="color: #ef4444; font-family: Outfit, sans-serif; font-weight: 700;">Validasi Data Gagal!</span>',
            html: '<div style="color: #f8fafc; font-size: 0.9rem; line-height: 1.5; text-align: left;">' +
                  '<ul class="mb-0 ps-3">' +
                  @json(implode('', array_map(fn($e) => "<li>" . e($e) . "</li>", $errors->all()))) +
                  '</ul></div>',
            icon: 'error',
            background: '#0f1623',
            iconColor: '#ef4444',
            confirmButtonText: '<i class="fa-solid fa-xmark me-2"></i>Perbaiki Data',
            confirmButtonColor: '#ef4444',
            customClass: {
                popup: 'border border-danger border-opacity-50 rounded-4 shadow-lg py-4 px-3',
                confirmButton: 'btn btn-danger rounded-pill px-4 py-2 fw-bold text-white'
            },
            buttonsStyling: false
        });
    @endif
    // 1. Toast Success - auto hide perlahan (fade out) setelah 4.8 detik
    const toastSuccess = document.getElementById('toastSuccess');
    if (toastSuccess) {
        setTimeout(() => toastSuccess.classList.add('show-toast'), 100);
        const progressBar = toastSuccess.querySelector('.toast-progress-bar');
        if (progressBar) {
            progressBar.style.transition = 'width 4800ms linear';
            setTimeout(() => progressBar.style.width = '0%', 150);
        }
        setTimeout(() => {
            if (document.body.contains(toastSuccess)) {
                toastSuccess.classList.remove('show-toast');
                toastSuccess.classList.add('hide-toast');
                setTimeout(() => toastSuccess.remove(), 600);
            }
        }, 4800);
    }

    // 2. Toast Warning - auto hide perlahan setelah 7.5 detik (agar sempat dibaca atau diklik ke modal)
    const toastWarning = document.getElementById('toastWarning');
    if (toastWarning) {
        setTimeout(() => toastWarning.classList.add('show-toast'), 200);
        const progressBar = toastWarning.querySelector('.toast-progress-bar');
        if (progressBar) {
            progressBar.style.transition = 'width 7500ms linear';
            setTimeout(() => progressBar.style.width = '0%', 250);
        }
        setTimeout(() => {
            if (document.body.contains(toastWarning)) {
                toastWarning.classList.remove('show-toast');
                toastWarning.classList.add('hide-toast');
                setTimeout(() => toastWarning.remove(), 600);
            }
        }, 7500);
    }

    // 3. Toast Error - auto hide perlahan setelah 6 detik
    const toastError = document.getElementById('toastError');
    if (toastError) {
        setTimeout(() => toastError.classList.add('show-toast'), 150);
        const progressBar = toastError.querySelector('.toast-progress-bar');
        if (progressBar) {
            progressBar.style.transition = 'width 6000ms linear';
            setTimeout(() => progressBar.style.width = '0%', 200);
        }
        setTimeout(() => {
            if (document.body.contains(toastError)) {
                toastError.classList.remove('show-toast');
                toastError.classList.add('hide-toast');
                setTimeout(() => toastError.remove(), 600);
            }
        }, 6000);
    }

    // 4. Toast Validation Errors - auto hide perlahan setelah 6.5 detik
    const toastVal = document.getElementById('toastValidationErrors');
    if (toastVal) {
        setTimeout(() => toastVal.classList.add('show-toast'), 180);
        const progressBar = toastVal.querySelector('.toast-progress-bar');
        if (progressBar) {
            progressBar.style.transition = 'width 6500ms linear';
            setTimeout(() => progressBar.style.width = '0%', 220);
        }
        setTimeout(() => {
            if (document.body.contains(toastVal)) {
                toastVal.classList.remove('show-toast');
                toastVal.classList.add('hide-toast');
                setTimeout(() => toastVal.remove(), 600);
            }
        }, 6500);
    }
});

function closeToast(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.remove('show-toast');
        el.classList.add('hide-toast');
        setTimeout(() => el.remove(), 500);
    }
}

function switchNotifTab(tabId, clickedBtn) {
    // Hide all tab panels
    const allPanels = ['tab-notif-outstanding', 'tab-notif-discrepancy'];
    allPanels.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    // Show selected panel
    const target = document.getElementById(tabId);
    if (target) target.style.display = 'block';
    // Update active button style
    document.querySelectorAll('.notif-tab-btn').forEach(btn => btn.classList.remove('active'));
    if (clickedBtn) clickedBtn.classList.add('active');
}
</script>
