<!-- Universal High-End Import Preview & Validation Modal -->
<div class="modal fade" id="modalImportPreview" tabindex="-1" aria-labelledby="modalImportPreviewLabel" aria-hidden="true" style="z-index: 10900;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: radial-gradient(120% 120% at 50% 0%, #151d30 0%, #0b1120 70%, #060913 100%); border: 1px solid rgba(226, 179, 74, 0.4); border-radius: 20px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9);">
            
            <!-- Modal Header -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3" style="background: rgba(18, 24, 38, 0.98); border-bottom: 2px solid rgba(226, 179, 74, 0.35);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="background: rgba(226, 179, 74, 0.15); border: 1.5px solid rgba(226, 179, 74, 0.5);">
                        <i class="bi bi-file-earmark-spreadsheet-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span id="previewEngineBadge" class="badge bg-primary bg-opacity-20 text-info border border-primary border-opacity-40 font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                NATIVE TABULAR ENGINE
                            </span>
                            <span id="previewVersionBadge" class="badge bg-dark text-warning border border-secondary font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                SCHEMA v2.0
                            </span>
                        </div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalImportPreviewLabel" style="font-family: 'Outfit', sans-serif;">
                            Pratinjau &amp; Validasi Impor Batch Data
                        </h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 text-light" style="background: rgba(10, 15, 28, 0.95);">
                
                <!-- Batch Summary Metric Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 border border-secondary border-opacity-25 text-center" style="background: rgba(15, 23, 42, 0.8);">
                            <small class="text-muted text-uppercase fw-bold font-monospace" style="font-size:0.7rem;">Total Baris</small>
                            <div id="previewTotalRows" class="fs-4 fw-bold text-white font-monospace mt-1">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 border border-success border-opacity-30 text-center" style="background: rgba(16, 185, 129, 0.1);">
                            <small class="text-success text-uppercase fw-bold font-monospace" style="font-size:0.7rem;">Siap Impor (Valid)</small>
                            <div id="previewValidRows" class="fs-4 fw-bold text-success font-monospace mt-1">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 border border-warning border-opacity-30 text-center" style="background: rgba(245, 158, 11, 0.1);">
                            <small class="text-warning text-uppercase fw-bold font-monospace" style="font-size:0.7rem;">Peringatan (Warnings)</small>
                            <div id="previewWarningRows" class="fs-4 fw-bold text-warning font-monospace mt-1">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded-4 border border-danger border-opacity-30 text-center" style="background: rgba(239, 68, 68, 0.1);">
                            <small class="text-danger text-uppercase fw-bold font-monospace" style="font-size:0.7rem;">Ditolak (Errors)</small>
                            <div id="previewInvalidRows" class="fs-4 fw-bold text-danger font-monospace mt-1">0</div>
                        </div>
                    </div>
                </div>

                <!-- Alert Information Box -->
                <div id="previewAlertBox" class="alert alert-info border-info border-opacity-30 bg-info bg-opacity-10 rounded-3 d-flex align-items-center gap-2.5 py-2.5 px-3 mb-3 small">
                    <i class="bi bi-shield-check text-info fs-5 flex-shrink-0"></i>
                    <span id="previewAlertMessage">Periksa kembali data di bawah ini sebelum menyimpan permanen ke sistem database.</span>
                </div>

                <!-- Table Preview -->
                <div class="table-responsive rounded-3 border border-secondary border-opacity-25" style="max-height: 380px;">
                    <table class="table table-dark table-hover mb-0 align-middle small" style="background: transparent;">
                        <thead class="sticky-top" style="background: #111827;">
                            <tr id="previewTableHead">
                                <th class="text-muted px-3 py-2 font-monospace" style="width: 50px;">#</th>
                                <th class="text-muted px-3 py-2">Part Number</th>
                                <th class="text-muted px-3 py-2">Quantity</th>
                                <th class="text-muted px-3 py-2">Supplier / Vendor</th>
                                <th class="text-muted px-3 py-2">Plant / Lokasi</th>
                                <th class="text-muted px-3 py-2 text-center" style="width: 110px;">Status</th>
                                <th class="text-muted px-3 py-2">Keterangan / Diagnostik</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-1 opacity-50"></i>
                                    Belum ada data file yang dimuat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer px-4 py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0b1120 0%, #060913 100%); border-top: 1px solid rgba(226, 179, 74, 0.25);">
                <div class="text-muted small font-monospace">
                    <i class="bi bi-cpu me-1 text-warning"></i> Atomic Import Engine PT Kawai Indonesia
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 text-light" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" id="btnConfirmImport" class="btn btn-warning text-dark rounded-pill px-4 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5" onclick="submitConfirmedImport()">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Konfirmasi &amp; Simpan Data
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
