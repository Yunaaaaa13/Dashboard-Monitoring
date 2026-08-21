/**
 * PT Kawai Indonesia — Unified Global Toast Notification Engine
 * Standardized across all 7 purchasing workflow steps.
 */
(function() {
    'use strict';

    function ensureContainer() {
        let container = document.getElementById('globalToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'globalToastContainer';
            document.body.appendChild(container);
        }
        return container;
    }

    const icons = {
        success: { icon: 'bi-check-lg', bg: 'rgba(16, 185, 129, 0.2)', color: '#34d399' },
        warning: { icon: 'bi-exclamation-triangle', bg: 'rgba(245, 158, 11, 0.2)', color: '#fbbf24' },
        danger:  { icon: 'bi-x-lg', bg: 'rgba(239, 68, 68, 0.2)', color: '#f87171' },
        info:    { icon: 'bi-info-lg', bg: 'rgba(59, 130, 246, 0.2)', color: '#60a5fa' },
    };

    function showToast(type, title, message, duration = 5000) {
        const container = ensureContainer();
        const conf = icons[type] || icons.info;
        const toastTypeClass = 'kawai-toast-' + (type === 'error' ? 'danger' : type);

        const toast = document.createElement('div');
        toast.className = `kawai-toast ${toastTypeClass}`;
        toast.innerHTML = `
            <div class="toast-icon-circle" style="background: ${conf.bg}; color: ${conf.color};">
                <i class="bi ${conf.icon}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button type="button" class="toast-close-btn" aria-label="Close">
                <i class="bi bi-x"></i>
            </button>
        `;

        const closeBtn = toast.querySelector('.toast-close-btn');
        const dismiss = () => {
            toast.classList.add('fade-out');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        };

        closeBtn.addEventListener('click', dismiss);
        container.appendChild(toast);

        if (duration > 0) {
            setTimeout(dismiss, duration);
        }
    }

    window.notify = {
        success: function(title, message, duration = 3500) {
            showToast('success', title || 'Berhasil', message || '', duration);
        },
        warning: function(title, message, duration = 5000) {
            showToast('warning', title || 'Peringatan', message || '', duration);
        },
        error: function(title, message, duration = 0) {
            showToast('danger', title || 'Terjadi Kesalahan', message || '', duration);
        },
        info: function(title, message, duration = 3500) {
            showToast('info', title || 'Informasi', message || '', duration);
        }
    };

    // Auto-detect Laravel Flash Session Messages in DOM on Page Load
    document.addEventListener('DOMContentLoaded', function() {
        // Flash Success
        const flashSuccess = document.querySelector('[data-flash-success]');
        if (flashSuccess && flashSuccess.dataset.flashSuccess) {
            window.notify.success('Berhasil', flashSuccess.dataset.flashSuccess);
        }

        // Flash Error
        const flashError = document.querySelector('[data-flash-error]');
        if (flashError && flashError.dataset.flashError) {
            window.notify.error('Gagal', flashError.dataset.flashError);
        }

        // Flash Warning
        const flashWarning = document.querySelector('[data-flash-warning]');
        if (flashWarning && flashWarning.dataset.flashWarning) {
            window.notify.warning('Peringatan', flashWarning.dataset.flashWarning);
        }
    });
})();
