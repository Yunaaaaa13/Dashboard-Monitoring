<!-- Global Dark Glassmorphism Confirmation Modal -->
<div class="modal fade" id="modalGlobalConfirm" tabindex="-1" aria-labelledby="modalGlobalConfirmLabel" aria-hidden="true" style="z-index: 10950;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.16); border-radius: 20px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.85); overflow: hidden;">
            
            <div class="modal-body p-4 text-center">
                <!-- Icon Alert -->
                <div id="confirmIconBox" class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: rgba(239, 68, 68, 0.15); border: 2px solid rgba(239, 68, 68, 0.4); color: #f87171;">
                    <i id="confirmIcon" class="bi bi-exclamation-triangle-fill fs-3"></i>
                </div>

                <!-- Title & Text -->
                <h5 class="modal-title fw-bold text-white mb-2" id="confirmTitle" style="font-family: 'Outfit', sans-serif;">
                    Konfirmasi Tindakan
                </h5>
                <p id="confirmMessage" class="text-muted small mb-4" style="line-height: 1.5; color: #cbd5e1 !important;">
                    Apakah Anda yakin ingin melanjutkan tindakan ini? Data yang telah diproses tidak dapat dikembalikan.
                </p>

                <!-- Actions -->
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <button type="button" class="btn btn-kawai-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" id="btnExecuteConfirm" class="btn btn-kawai-danger rounded-pill px-4 fw-bold">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
