/**
 * PT Kawai Indonesia — Unified UI Interaction & Confirmation Service
 * Handles debouncing, loading states, and modal confirmations.
 */
(function() {
    'use strict';

    // 1. Global Confirmation Service
    window.KawaiConfirm = {
        ask: function(options) {
            const {
                title = 'Konfirmasi Tindakan',
                message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
                confirmText = 'Ya, Lanjutkan',
                confirmType = 'danger', // danger, warning, primary
                onConfirm = () => {}
            } = options;

            const modalEl = document.getElementById('modalGlobalConfirm');
            if (!modalEl) {
                if (window.confirm(message)) {
                    onConfirm();
                }
                return;
            }

            const titleEl = document.getElementById('confirmTitle');
            const msgEl = document.getElementById('confirmMessage');
            const btnConfirm = document.getElementById('btnExecuteConfirm');
            const iconBox = document.getElementById('confirmIconBox');
            const iconEl = document.getElementById('confirmIcon');

            if (titleEl) titleEl.innerText = title;
            if (msgEl) msgEl.innerText = message;

            if (btnConfirm) {
                btnConfirm.innerText = confirmText;
                btnConfirm.className = 'btn rounded-pill px-4 fw-bold ' + 
                    (confirmType === 'danger' ? 'btn-kawai-danger' : 
                    (confirmType === 'warning' ? 'btn-kawai-primary' : 'btn-kawai-primary'));

                // Replace onclick listener
                const newBtn = btnConfirm.cloneNode(true);
                btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);

                newBtn.addEventListener('click', function() {
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();
                    onConfirm();
                });
            }

            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        },

        delete: function(targetName, onConfirm) {
            this.ask({
                title: 'Hapus Data?',
                message: `Data "${targetName}" akan dihapus permanen dari sistem dan tidak dapat dikembalikan.`,
                confirmText: 'Ya, Hapus',
                confirmType: 'danger',
                onConfirm: onConfirm
            });
        },

        deleteAll: function(count, onConfirm) {
            this.ask({
                title: 'Hapus Seluruh Data?',
                message: `PERINGATAN KRITIS: Sebanyak ${count || 'seluruh'} data akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.`,
                confirmText: 'Hapus Semua Data',
                confirmType: 'danger',
                onConfirm: onConfirm
            });
        }
    };

    // 2. Button Debounce & Loading Spinner Engine
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                // If form is valid
                if (form.checkValidity && !form.checkValidity()) {
                    return;
                }

                const submitBtn = form.querySelector('button[type="submit"]:not([data-no-debounce])');
                if (submitBtn && !submitBtn.disabled) {
                    const originalHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span> Memproses...`;

                    // Safety timeout to re-enable after 15s in case of aborted navigation
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                    }, 15000);
                }
            });
        });
    });
})();
