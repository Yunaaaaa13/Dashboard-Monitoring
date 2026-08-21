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
            if (!modalEl || typeof bootstrap === 'undefined') {
                if (window.confirm(message)) {
                    if (typeof onConfirm === 'function') onConfirm();
                }
                return;
            }

            const titleEl = document.getElementById('confirmTitle');
            const msgEl = document.getElementById('confirmMessage');
            const btnConfirm = document.getElementById('btnExecuteConfirm');

            if (titleEl) titleEl.innerText = title;
            if (msgEl) msgEl.innerText = message;

            if (btnConfirm) {
                btnConfirm.innerText = confirmText;
                btnConfirm.className = 'btn rounded-pill px-4 fw-bold ' + 
                    (confirmType === 'danger' ? 'btn-kawai-danger' : 
                    (confirmType === 'warning' ? 'btn-kawai-primary' : 'btn-kawai-primary'));

                // Replace onclick listener cleanly
                const newBtn = btnConfirm.cloneNode(true);
                btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);

                newBtn.addEventListener('click', function() {
                    let bsModal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                    if (bsModal) bsModal.hide();
                    if (typeof onConfirm === 'function') {
                        onConfirm();
                    }
                });
            }

            let bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            bsModal.show();
        },

        delete: function(arg1, arg2, arg3) {
            let title = 'Hapus Data?';
            let message = 'Data ini akan dihapus permanen dari sistem dan tidak dapat dikembalikan.';
            let onConfirm = () => {};

            if (typeof arg1 === 'string' && typeof arg2 === 'string' && typeof arg3 === 'function') {
                title = arg1;
                message = arg2;
                onConfirm = arg3;
            } else if (typeof arg1 === 'string' && typeof arg2 === 'function') {
                title = 'Hapus Data?';
                message = `Data "${arg1}" akan dihapus permanen dari sistem dan tidak dapat dikembalikan.`;
                onConfirm = arg2;
            } else if (typeof arg1 === 'function') {
                onConfirm = arg1;
            }

            this.ask({
                title: title,
                message: message,
                confirmText: 'Ya, Hapus',
                confirmType: 'danger',
                onConfirm: onConfirm
            });
        },

        deleteAll: function(arg1, arg2, arg3) {
            let title = 'Hapus Seluruh Data?';
            let message = 'PERINGATAN KRITIS: Seluruh data akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.';
            let onConfirm = () => {};

            if (typeof arg1 === 'string' && typeof arg2 === 'string' && typeof arg3 === 'function') {
                title = arg1;
                message = arg2;
                onConfirm = arg3;
            } else if (typeof arg1 === 'function') {
                onConfirm = arg1;
            } else if (typeof arg2 === 'function') {
                message = `PERINGATAN KRITIS: Sebanyak ${arg1} data akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.`;
                onConfirm = arg2;
            }

            this.ask({
                title: title,
                message: message,
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
