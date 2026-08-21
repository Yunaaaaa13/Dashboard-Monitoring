<style>
/* ══════════════════════════════════════════════════════════════════════════
   HIGH-END ENTERPRISE MODAL & PANDUAN STYLING — PT KAWAI INDONESIA
   ══════════════════════════════════════════════════════════════════════════ */
#modalFaqPurchasing {
    z-index: 10850 !important;
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
    background: rgba(3, 7, 18, 0.82) !important;
}

#modalFaqPurchasing .modal-dialog {
    z-index: 10855 !important;
    max-width: 1260px;
    margin: 1.5rem auto;
    height: calc(100% - 3rem);
    display: flex;
    align-items: center;
}

#modalFaqPurchasing .modal-content {
    background: radial-gradient(120% 120% at 50% 0%, #162035 0%, #0d1527 50%, #070b14 100%) !important;
    border: 1px solid rgba(226, 179, 74, 0.4) !important;
    border-radius: 24px !important;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.9), 0 0 45px rgba(226, 179, 74, 0.18) !important;
    overflow: hidden;
    color: #f3f4f6;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

/* Modal Header Luxury Glass */
#modalFaqPurchasing .modal-header {
    background: linear-gradient(135deg, rgba(20, 28, 48, 0.98) 0%, rgba(11, 17, 32, 0.99) 100%) !important;
    border-bottom: 2px solid rgba(226, 179, 74, 0.35) !important;
    padding: 1.25rem 2rem;
    position: relative;
    flex-shrink: 0;
}

#modalFaqPurchasing .modal-header::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 8%;
    right: 8%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #e2b34a, #00d2ff, #a855f7, transparent);
}

/* Modal Body Scroll Area */
#modalFaqPurchasing .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    padding: 1.5rem 2rem 2.5rem 2rem !important;
    background: rgba(10, 15, 28, 0.95);
}

#modalFaqPurchasing .modal-body::-webkit-scrollbar {
    width: 8px;
}
#modalFaqPurchasing .modal-body::-webkit-scrollbar-track {
    background: rgba(7, 11, 20, 0.8);
}
#modalFaqPurchasing .modal-body::-webkit-scrollbar-thumb {
    background: rgba(226, 179, 74, 0.4);
    border-radius: 4px;
}
#modalFaqPurchasing .modal-body::-webkit-scrollbar-thumb:hover {
    background: rgba(226, 179, 74, 0.75);
}

/* ── BULLETPROOF HIGH-CONTRAST BADGE SYSTEM ── */
.faq-pill-badge {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0.3rem 0.75rem !important;
    border-radius: 999px !important;
    font-family: 'JetBrains Mono', 'Fira Code', monospace !important;
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.05em !important;
    text-transform: uppercase !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35) !important;
}

.faq-pill-gold {
    background: rgba(245, 158, 11, 0.16) !important;
    color: #fcd34d !important;
    border: 1.5px solid rgba(245, 158, 11, 0.55) !important;
    text-shadow: 0 0 8px rgba(245, 158, 11, 0.3);
}

.faq-pill-blue {
    background: rgba(59, 130, 246, 0.16) !important;
    color: #93c5fd !important;
    border: 1.5px solid rgba(59, 130, 246, 0.55) !important;
    text-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
}

.faq-pill-emerald {
    background: rgba(16, 185, 129, 0.16) !important;
    color: #6ee7b7 !important;
    border: 1.5px solid rgba(16, 185, 129, 0.55) !important;
    text-shadow: 0 0 8px rgba(16, 185, 129, 0.3);
}

.faq-pill-cyan {
    background: rgba(6, 182, 212, 0.16) !important;
    color: #67e8f9 !important;
    border: 1.5px solid rgba(6, 182, 212, 0.55) !important;
    text-shadow: 0 0 8px rgba(6, 182, 212, 0.3);
}

.faq-pill-purple {
    background: rgba(168, 85, 247, 0.16) !important;
    color: #e9d5ff !important;
    border: 1.5px solid rgba(168, 85, 247, 0.55) !important;
    text-shadow: 0 0 8px rgba(168, 85, 247, 0.3);
}

.faq-pill-rose {
    background: rgba(239, 68, 68, 0.16) !important;
    color: #fca5a5 !important;
    border: 1.5px solid rgba(239, 68, 68, 0.55) !important;
    text-shadow: 0 0 8px rgba(239, 68, 68, 0.3);
}

.faq-pill-neutral {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #e2e8f0 !important;
    border: 1.5px solid rgba(255, 255, 255, 0.2) !important;
}

/* ── STEP NUMBER BADGE ICONS ── */
.sop-step-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.86rem;
    font-weight: 800;
    font-family: 'Outfit', sans-serif;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

.sop-step-1 { background: #2563eb; color: #ffffff; box-shadow: 0 0 12px rgba(37, 99, 235, 0.5); }
.sop-step-2 { background: #059669; color: #ffffff; box-shadow: 0 0 12px rgba(5, 150, 105, 0.5); }
.sop-step-3 { background: #0891b2; color: #ffffff; box-shadow: 0 0 12px rgba(8, 145, 178, 0.5); }
.sop-step-4 { background: #7c3aed; color: #ffffff; box-shadow: 0 0 12px rgba(124, 58, 237, 0.5); }
.sop-step-5 { background: #d97706; color: #ffffff; box-shadow: 0 0 12px rgba(217, 119, 6, 0.5); }
.sop-step-6 { background: #9333ea; color: #ffffff; box-shadow: 0 0 12px rgba(147, 51, 234, 0.5); }
.sop-step-7 { background: #dc2626; color: #ffffff; box-shadow: 0 0 12px rgba(220, 38, 38, 0.5); }

/* Tab Navigation Pills */
.faq-pnav-wrap {
    background: rgba(14, 21, 37, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 18px;
    padding: 6px;
    gap: 6px;
    backdrop-filter: blur(12px);
}

.faq-tab-btn {
    border: 1px solid transparent !important;
    background: rgba(255, 255, 255, 0.04) !important;
    color: #94a3b8 !important;
    font-family: 'Outfit', 'Inter', sans-serif;
    font-size: 0.88rem;
    font-weight: 600;
    padding: 0.65rem 1.15rem;
    border-radius: 12px !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    text-decoration: none;
}

.faq-tab-btn:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.09) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    transform: translateY(-1px);
}

.faq-tab-btn.active {
    background: linear-gradient(135deg, rgba(226, 179, 74, 0.28) 0%, rgba(217, 119, 6, 0.4) 100%) !important;
    color: #fbbf24 !important;
    border-color: rgba(245, 158, 11, 0.7) !important;
    box-shadow: 0 4px 18px rgba(245, 158, 11, 0.25) !important;
    font-weight: 700 !important;
}

.faq-tab-btn.active i {
    color: #fbbf24 !important;
}

/* Glass Card Content Modules */
.faq-card {
    background: rgba(18, 27, 46, 0.78);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 18px;
    padding: 1.45rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(12px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    position: relative;
    overflow: hidden;
}

.faq-card:hover {
    border-color: rgba(226, 179, 74, 0.45);
    transform: translateY(-2px);
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.45), 0 0 18px rgba(226, 179, 74, 0.15);
}

.faq-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3.5px;
    background: rgba(255, 255, 255, 0.08);
}

.faq-card.border-accent-blue::before    { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
.faq-card.border-accent-emerald::before { background: linear-gradient(90deg, #10b981, #34d399); }
.faq-card.border-accent-cyan::before    { background: linear-gradient(90deg, #06b6d4, #22d3ee); }
.faq-card.border-accent-purple::before  { background: linear-gradient(90deg, #8b5cf6, #c084fc); }
.faq-card.border-accent-gold::before    { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.faq-card.border-accent-rose::before    { background: linear-gradient(90deg, #ef4444, #f87171); }

/* Quick Filter Chips */
.faq-filter-chip {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    border-radius: 999px;
    padding: 0.38rem 0.95rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    display: inline-flex;
    align-items: center;
}

.faq-filter-chip:hover, .faq-filter-chip.active {
    background: rgba(226, 179, 74, 0.22);
    border-color: rgba(226, 179, 74, 0.6);
    color: #fbbf24;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(226, 179, 74, 0.2);
}

/* Formula Box */
.formula-display-box {
    background: #070b14;
    border: 1px solid rgba(0, 210, 255, 0.3);
    border-left: 4px solid #00d2ff;
    border-radius: 12px;
    padding: 1.1rem 1.35rem;
    font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
    font-size: 0.95rem;
    color: #e0f2fe;
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.6);
}

/* Search input glass styling */
.faq-search-box {
    background: rgba(12, 18, 32, 0.92) !important;
    border: 1.5px solid rgba(255, 255, 255, 0.18) !important;
    color: #ffffff !important;
    border-radius: 14px !important;
    padding: 0.75rem 1.15rem !important;
    font-size: 0.92rem !important;
    transition: all 0.25s ease;
}
.faq-search-box:focus {
    border-color: #fbbf24 !important;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25) !important;
    background: rgba(16, 24, 44, 0.98) !important;
}

/* Action Link Pills */
.faq-action-pill {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fbbf24 !important;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none !important;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.faq-action-pill:hover {
    background: rgba(245, 158, 11, 0.2);
    border-color: rgba(245, 158, 11, 0.5);
    color: #ffffff !important;
    transform: translateX(2px);
}

/* Interactive Calculator Input */
.calc-input {
    background: #0a0f1d !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
    border-radius: 10px !important;
    padding: 0.45rem 0.75rem !important;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.9rem;
}
.calc-input:focus {
    border-color: #38bdf8 !important;
    outline: none;
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.3);
}

/* Print styling for SOP logbook */
@media print {
    body * { visibility: hidden; }
    #modalFaqPurchasing, #modalFaqPurchasing * { visibility: visible; }
    #modalFaqPurchasing { position: absolute; left: 0; top: 0; width: 100%; }
    .btn-close, .modal-footer button, .faq-filter-chip, .input-group { display: none !important; }
}
</style>

<script>
// ── FAQ Modal Utilities ──
function closeFaqModal() {
    const el = document.getElementById('modalFaqPurchasing');
    if (el) {
        try {
            const bsModal = bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
            if (bsModal) bsModal.hide();
        } catch (err) {
            el.classList.remove('show');
            el.style.display = 'none';
        }
        if (document.querySelectorAll('.modal.show').length <= 1) {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
    }
}

// ── Search & Filter Logic ──
let activeFaqFilterCategory = 'all';

function filterFaqContent() {
    const q = (document.getElementById('searchFaqInput')?.value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('#modalFaqPurchasing .faq-card, #modalFaqPurchasing .accordion-item');
    let visibleCount = 0;

    cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        const category = card.getAttribute('data-category') || 'all';
        
        const matchCategory = (activeFaqFilterCategory === 'all' || category.includes(activeFaqFilterCategory));
        const matchText = (q === '' || text.includes(q));

        if (matchCategory && matchText) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const countBadge = document.getElementById('faqSearchResultCount');
    if (countBadge) {
        if (q !== '' || activeFaqFilterCategory !== 'all') {
            countBadge.innerText = `Menampilkan ${visibleCount} topik terkait`;
            countBadge.classList.remove('d-none');
        } else {
            countBadge.classList.add('d-none');
        }
    }
}

function setFaqFilterCategory(cat, btn) {
    activeFaqFilterCategory = cat;
    document.querySelectorAll('.faq-filter-chip').forEach(c => c.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filterFaqContent();
}

function clearFaqSearch() {
    const input = document.getElementById('searchFaqInput');
    if (input) {
        input.value = '';
        setFaqFilterCategory('all', document.querySelector('.faq-filter-chip[data-cat="all"]'));
        filterFaqContent();
        input.focus();
    }
}

// ── Copy SOP Snippet ──
function copySopText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2 text-success"></i> Tersalin!';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    }).catch(err => {
        console.error('Gagal menyalin:', err);
    });
}

// ── Live Interactive Calculation Engine ──
function runFaqCalculator() {
    const prevStock = parseFloat(document.getElementById('calcPrevStock')?.value) || 0;
    const poQty     = parseFloat(document.getElementById('calcPoQty')?.value) || 0;
    const prodQty   = parseFloat(document.getElementById('calcProdQty')?.value) || 0;
    const nextProd  = parseFloat(document.getElementById('calcNextProd')?.value) || 0;

    // Formula 1: Stock Akhir
    const finalStock = prevStock + poQty - prodQty;
    const resStockEl = document.getElementById('calcResultStock');
    if (resStockEl) {
        resStockEl.innerText = finalStock.toLocaleString('id-ID') + ' unit';
        resStockEl.className = finalStock < 0 ? 'fw-bold text-danger' : 'fw-bold text-warning';
    }

    // Formula 2: Live Ratio %
    const ratioEl = document.getElementById('calcResultRatio');
    const badgeEl = document.getElementById('calcResultRatioBadge');
    if (ratioEl && nextProd > 0) {
        const ratio = (finalStock / nextProd) * 100;
        ratioEl.innerText = ratio.toFixed(1) + '%';
        if (ratio < 100) {
            badgeEl.className = 'faq-pill-badge faq-pill-rose ms-2';
            badgeEl.innerText = 'Kritis / Kurang (< 100%)';
        } else if (ratio <= 200) {
            badgeEl.className = 'faq-pill-badge faq-pill-gold ms-2';
            badgeEl.innerText = 'Normal & Ideal (100% - 200%)';
        } else {
            badgeEl.className = 'faq-pill-badge faq-pill-emerald ms-2';
            badgeEl.innerText = 'Overstock (> 200%)';
        }
    } else if (ratioEl) {
        ratioEl.innerText = '— (PROD M+1 Kosong)';
        badgeEl.className = 'faq-pill-badge faq-pill-neutral ms-2';
        badgeEl.innerText = 'No Demand Data';
    }
}

// Keyboard shortcuts for quick accessibility
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('modalFaqPurchasing');
    if (modal && modal.classList.contains('show')) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            document.getElementById('searchFaqInput')?.focus();
        }
    }
});

// Deep link & scroll to specific FAQ section
function showFaqSection(sectionId) {
    const modalEl = document.getElementById('modalFaqPurchasing');
    if (modalEl) {
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();
        
        // Ensure Tab 1 is active
        const tabBtn = document.getElementById('faq-dashboards-tab');
        if (tabBtn) {
            const bsTab = bootstrap.Tab.getOrCreateInstance(tabBtn);
            bsTab.show();
        }
        
        setTimeout(() => {
            const targetEl = document.getElementById(sectionId);
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetEl.style.transition = 'box-shadow 0.4s ease';
                targetEl.style.boxShadow = '0 0 30px rgba(0, 210, 255, 0.4)';
                setTimeout(() => { targetEl.style.boxShadow = ''; }, 2200);
            }
        }, 350);
    }
}
</script>

<!-- Pusat Panduan & FAQ Modal - PT Kawai Indonesia -->
<div class="modal fade" id="modalFaqPurchasing" tabindex="-1" aria-labelledby="modalFaqPurchasingLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg">
            
            <!-- Modal Header -->
            <div class="modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" 
                         style="width: 52px; height: 52px; background: rgba(226, 179, 74, 0.16); border: 1.5px solid rgba(226, 179, 74, 0.55); box-shadow: 0 0 20px rgba(226, 179, 74, 0.25);">
                        <i class="bi bi-book-half text-warning fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="faq-pill-badge faq-pill-gold">
                                ENTERPRISE PROCUREMENT ECOSYSTEM
                            </span>
                            <span class="faq-pill-badge faq-pill-blue">
                                ISO-9001 COMPLIANT
                            </span>
                            <span class="faq-pill-badge faq-pill-emerald">
                                7-STEP INTEGRATED
                            </span>
                        </div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalFaqPurchasingLabel" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.01em;">
                            Pusat Panduan, Rumus Resmi &amp; Logbook Digital SOP — PT Kawai Indonesia
                        </h5>
                        <small class="text-muted" style="font-size: 0.82rem; color: #94a3b8 !important;">
                            Dokumentasi komprehensif alur kerja 7 langkah pengadaan, rumus perhitungan running stock, linking kurs pajak, dan manual book operasional user.
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 text-light d-none d-md-inline-flex align-items-center gap-1.5" onclick="window.print()" title="Cetak atau Simpan PDF Logbook">
                        <i class="bi bi-printer"></i> Cetak / PDF
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" onclick="closeFaqModal()" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                
                <!-- Live Search & Category Chips Toolbar -->
                <div class="p-3 mb-4 rounded-4" style="background: rgba(15, 23, 42, 0.88); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-warning px-3" style="border-radius: 12px 0 0 12px;"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchFaqInput" class="form-control faq-search-box border-start-0" 
                                       placeholder="Cari topik panduan... (misal: Forecast, PO, ETA, IAD, Kurs Pajak, Selisih, Import Excel, Live Ratio)" 
                                       onkeyup="filterFaqContent()"
                                       style="border-radius: 0 12px 12px 0;">
                                <button class="btn btn-outline-secondary text-muted px-3" type="button" onclick="clearFaqSearch()" title="Bersihkan Pencarian">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 text-md-end">
                            <span id="faqSearchResultCount" class="faq-pill-badge faq-pill-gold d-none">
                                Menampilkan hasil...
                            </span>
                            <span class="text-muted small d-none d-lg-inline-block font-monospace ms-2" style="font-size: 0.75rem;">
                                <kbd class="bg-dark text-warning border border-secondary px-2 py-1">Ctrl + K</kbd>
                            </span>
                        </div>
                    </div>

                    <!-- Quick Filter Chips -->
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-3 pt-2.5 border-top border-secondary border-opacity-25">
                        <span class="text-muted small fw-bold me-1" style="font-size: 0.78rem;"><i class="bi bi-funnel me-1"></i>Filter Cepat:</span>
                        <span class="faq-filter-chip active" data-cat="all" onclick="setFaqFilterCategory('all', this)">Semua Topik</span>
                        <span class="faq-filter-chip" data-cat="workflow" onclick="setFaqFilterCategory('workflow', this)"><i class="bi bi-bezier2 text-primary me-1.5"></i>7 Alur Workflow</span>
                        <span class="faq-filter-chip" data-cat="formula" onclick="setFaqFilterCategory('formula', this)"><i class="bi bi-calculator text-warning me-1.5"></i>Rumus &amp; Formula</span>
                        <span class="faq-filter-chip" data-cat="sop" onclick="setFaqFilterCategory('sop', this)"><i class="bi bi-journal-bookmark text-danger me-1.5"></i>Logbook SOP</span>
                        <span class="faq-filter-chip" data-cat="exchange" onclick="setFaqFilterCategory('exchange', this)"><i class="bi bi-currency-exchange text-info me-1.5"></i>Kurs &amp; Finansial</span>
                        <span class="faq-filter-chip" data-cat="roles" onclick="setFaqFilterCategory('roles', this)"><i class="bi bi-people text-success me-1.5"></i>Role &amp; Otorisasi</span>
                        <span class="faq-filter-chip" data-cat="tips" onclick="setFaqFilterCategory('tips', this)"><i class="bi bi-lightbulb text-warning me-1.5"></i>Tips &amp; Best Practices</span>
                    </div>
                </div>
                
                <!-- Nav Tabs / Kategori FAQ High-End Segmented Bar -->
                <ul class="nav nav-pills faq-pnav-wrap mb-4 flex-nowrap overflow-x-auto style-scrollbar" id="faqTabs" role="tablist">
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link active faq-tab-btn w-100 justify-content-center" id="faq-dashboards-tab" data-bs-toggle="tab" data-bs-target="#faq-dashboards" type="button" role="tab" aria-selected="true">
                            <i class="bi bi-grid-1x2-fill text-primary"></i> <span>1. Alur 7-Step &amp; Dashboard</span>
                        </button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link faq-tab-btn w-100 justify-content-center" id="faq-formulas-tab" data-bs-toggle="tab" data-bs-target="#faq-formulas" type="button" role="tab" aria-selected="false">
                            <i class="bi bi-calculator-fill text-warning"></i> <span>2. Rumus &amp; Kalkulasi Resmi</span>
                        </button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link faq-tab-btn w-100 justify-content-center" id="faq-workflow-tab" data-bs-toggle="tab" data-bs-target="#faq-workflow" type="button" role="tab" aria-selected="false">
                            <i class="bi bi-lightning-charge-fill text-info"></i> <span>3. Tips &amp; Best Practices</span>
                        </button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link faq-tab-btn w-100 justify-content-center" id="faq-roles-tab" data-bs-toggle="tab" data-bs-target="#faq-roles" type="button" role="tab" aria-selected="false">
                            <i class="bi bi-people-fill text-success"></i> <span>4. Role &amp; Otorisasi PIC</span>
                        </button>
                    </li>
                    <li class="nav-item flex-fill text-center" role="presentation">
                        <button class="nav-link faq-tab-btn w-100 justify-content-center" id="faq-logbook-tab" data-bs-toggle="tab" data-bs-target="#faq-logbook" type="button" role="tab" aria-selected="false">
                            <i class="bi bi-journal-check text-danger"></i> <span>5. Logbook &amp; SOP Digital</span>
                        </button>
                    </li>
                </ul>

                <!-- Tab Contents -->
                <div class="tab-content" id="faqTabsContent">
                    
                    <!-- ════════════════════════════════════════════════════════════════
                         TAB 1: PENGERTIAN SEMUA DASHBOARD & 7 ALUR WORKFLOW TERINTEGRASI
                         ════════════════════════════════════════════════════════════════ -->
                    <div class="tab-pane fade show active" id="faq-dashboards" role="tabpanel" aria-labelledby="faq-dashboards-tab">
                        
                        <!-- Header Banner Alur -->
                        <div class="alert alert-primary border-primary border-opacity-30 bg-primary bg-opacity-10 d-flex align-items-center justify-content-between flex-wrap gap-3 rounded-4 p-3 mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle p-2 bg-primary bg-opacity-25 text-primary fs-4 flex-shrink-0">
                                    <i class="bi bi-diagram-3-fill"></i>
                                </div>
                                <div class="text-white">
                                    <h6 class="fw-bold mb-0 brand-font">Struktur 7-Langkah Terintegrasi Purchasing PT Kawai Indonesia</h6>
                                    <small class="text-light opacity-90">Sistem alur rantai pasok (Supply Chain Pipeline) dirancang dari perencanaan kebutuhan (Forecast), pemesanan vendor (PO), kontrol kedatangan (Incoming &amp; Outstanding), realisasi pabrik, audit fisik gudang, hingga analisis komparasi kurs.</small>
                                </div>
                            </div>
                            <span class="faq-pill-badge faq-pill-gold">
                                7 STEPS PIPELINE
                            </span>
                        </div>

                        <!-- 7 Step Cards Grid -->
                        <div class="row g-3 g-xl-4 mb-4">
                            
                            <!-- Step 1 -->
                            <div class="col-md-6 col-xl-4">
                                <div class="faq-card border-accent-blue" data-category="workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                                        <span class="sop-step-badge sop-step-1">1</span>
                                        <span class="faq-pill-badge faq-pill-blue">DEMAND PLANNING</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #60a5fa !important;"><i class="bi bi-graph-up-arrow me-1.5"></i>Step 1: Master Data (Forecast)</h6>
                                    <p class="small mb-3" style="line-height: 1.65; color: #cbd5e1 !important;">
                                        Menentukan rencana kebutuhan material (Forecast) untuk 12 bulan ke depan per item code. Rencana ini menjadi acuan dasar perhitungan modal perputaran stok roll-forward.
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-2.5 border-top border-secondary border-opacity-25 small">
                                        <span class="text-info font-monospace small"><i class="bi bi-database me-1"></i>Master Forecast</span>
                                        <a href="{{ route('purchasing.outstanding') }}" class="faq-action-pill">Buka Step 1 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2 -->
                            <div class="col-md-6 col-xl-4">
                                <div class="faq-card border-accent-emerald" data-category="workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                                        <span class="sop-step-badge sop-step-2">2</span>
                                        <span class="faq-pill-badge faq-pill-emerald">VENDOR ORDER</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #34d399 !important;"><i class="bi bi-file-earmark-text me-1.5"></i>Step 2: Master PO (Purchase Orders)</h6>
                                    <p class="small mb-3" style="line-height: 1.65; color: #cbd5e1 !important;">
                                        Mencatat dan mengelola Purchase Order (PO) resmi ke supplier vendor, termasuk harga per unit ($), kuantitas order, dan estimasi waktu kedatangan kontainer / truk (ETA).
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-2.5 border-top border-secondary border-opacity-25 small">
                                        <span class="text-success font-monospace small"><i class="bi bi-receipt me-1"></i>Order Tracking</span>
                                        <a href="{{ route('purchasing.master-po') }}" class="faq-action-pill">Buka Step 2 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3 -->
                            <div class="col-md-6 col-xl-4">
                                <div class="faq-card border-accent-cyan" data-category="workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                                        <span class="sop-step-badge sop-step-3">3</span>
                                        <span class="faq-pill-badge faq-pill-cyan">RECEIPT &amp; IAD</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #22d3ee !important;"><i class="bi bi-truck-flatbed me-1.5"></i>Step 3: Incoming Penerimaan PO</h6>
                                    <p class="small mb-3" style="line-height: 1.65; color: #cbd5e1 !important;">
                                        Pencatatan aktual kedatangan barang fisik di gudang penerimaan pabrik. Mengumpulkan data penerimaan fisik, verifikasi surat jalan, dan approval mutu tim IAD.
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-2.5 border-top border-secondary border-opacity-25 small">
                                        <span class="text-info font-monospace small"><i class="bi bi-box-seam me-1"></i>Actual Receipts</span>
                                        <a href="{{ route('purchasing.input') }}" class="faq-action-pill">Buka Step 3 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4 -->
                            <div class="col-md-6 col-xl-4">
                                <div class="faq-card border-accent-purple" data-category="workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                                        <span class="sop-step-badge sop-step-4">4</span>
                                        <span class="faq-pill-badge faq-pill-purple">PENDING SUPPLY</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #c084fc !important;"><i class="bi bi-boxes me-1.5"></i>Step 4: Dashboard Outstanding PO</h6>
                                    <p class="small mb-3" style="line-height: 1.65; color: #cbd5e1 !important;">
                                        Memantau secara visual barang outstanding (sisa PO yang belum dikirim supplier) dengan membandingkan Kuantitas PO (Step 2) terhadap Kuantitas Diterima (Step 3).
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-2.5 border-top border-secondary border-opacity-25 small">
                                        <span class="text-purple font-monospace small" style="color:#d8b4fe !important;"><i class="bi bi-hourglass-split me-1"></i>Outstanding Tracker</span>
                                        <a href="{{ route('purchasing.outstanding-po') }}" class="faq-action-pill">Buka Step 4 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 5 -->
                            <div class="col-md-6 col-xl-4">
                                <div class="faq-card border-accent-gold" data-category="workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                                        <span class="sop-step-badge sop-step-5">5</span>
                                        <span class="faq-pill-badge faq-pill-gold">SHOPFLOOR OUTPUT</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #fbbf24 !important;"><i class="bi bi-gear-wide-connected me-1.5"></i>Step 5: Aktual Produksi Pabrik</h6>
                                    <p class="small mb-3" style="line-height: 1.65; color: #cbd5e1 !important;">
                                        Pencatatan realisasi pemakaian material harian berdasarkan hasil output produksi perakitan piano di lini pabrik. Angka pemakaian ini mengurangi modal stok berjalan.
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-2.5 border-top border-secondary border-opacity-25 small">
                                        <span class="text-warning font-monospace small"><i class="bi bi-cpu me-1"></i>Production Realization</span>
                                        <a href="{{ route('purchasing.actual-production') }}" class="faq-action-pill">Buka Step 5 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 6 -->
                            <div class="col-md-6 col-xl-4">
                                <div class="faq-card border-accent-purple" data-category="workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                                        <span class="sop-step-badge sop-step-6">6</span>
                                        <span class="faq-pill-badge faq-pill-purple">PHYSICAL AUDIT</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #e879f9 !important;"><i class="bi bi-box-seam-fill me-1.5"></i>Step 6: Aktual Inventory &amp; Supply Integration</h6>
                                    <p class="small mb-3" style="line-height: 1.65; color: #cbd5e1 !important;">
                                        Dashboard pemantauan stok fisik gudang (Stock Opname) yang terintegrasi langsung dengan Forecast dan Outstanding PO untuk mengukur <em>Potential Supply, Coverage %,</em> dan <em>Supply Gap</em>.
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-2.5 border-top border-secondary border-opacity-25 small">
                                        <span class="font-monospace small" style="color:#f0abfc !important;"><i class="bi bi-layers-half me-1"></i>Supply Integration</span>
                                        <a href="{{ route('purchasing.actual-inventory') }}" class="faq-action-pill">Buka Step 6 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 7 -->
                            <div class="col-12 col-md-12 col-xl-12">
                                <div class="faq-card border-accent-rose" data-category="workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2.5 flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="sop-step-badge sop-step-7">7</span>
                                            <span class="faq-pill-badge faq-pill-rose">EXECUTIVE ANALYSIS (3-SLIDES)</span>
                                        </div>
                                        <a href="{{ route('purchasing.analysis') }}" class="faq-action-pill px-3 py-1.5" style="border-color: rgba(239, 68, 68, 0.5); color: #fca5a5 !important;">
                                            Buka Dashboard Komparasi Step 7 &rarr;
                                        </a>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #f87171 !important;"><i class="bi bi-sliders me-1.5"></i>Step 7: Hasil Akhir &amp; Komparasi Multi-Dimensi (3 Slide Terpadu)</h6>
                                    <div class="row g-3 pt-2">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(15, 23, 42, 0.65);">
                                                <div class="fw-bold text-info small mb-1"><i class="bi bi-currency-dollar me-1"></i>Slide 1: Kurs &amp; Financial Analysis</div>
                                                <p class="small mb-0" style="font-size: 0.82rem; color: #cbd5e1 !important; line-height: 1.6;">Linking otomatis Kurs Budget Bulanan vs Kurs Pajak Mingguan Kemenkeu, switcher format Dollar/Rupiah, dan pop-up variansi harga per item code.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(15, 23, 42, 0.65);">
                                                <div class="fw-bold text-warning small mb-1"><i class="bi bi-graph-up me-1"></i>Slide 2: Infografis Tren &amp; Outstanding</div>
                                                <p class="small mb-0" style="font-size: 0.82rem; color: #cbd5e1 !important; line-height: 1.6;">Visualisasi pergerakan volume incoming, target PO, grafik deviasi pemenuhan part number, serta tracking sisa outstanding.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(15, 23, 42, 0.65);">
                                                <div class="fw-bold small mb-1" style="color:#c084fc !important;"><i class="bi bi-boxes me-1"></i>Slide 3: Stock Forecast vs Actual Inv</div>
                                                <p class="small mb-0" style="font-size: 0.82rem; color: #cbd5e1 !important; line-height: 1.6;">Komparasi langsung antara model perhitungan stok roll-forward di sistem dengan hasil opname fisik inventaris aktual di gudang.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Supporting Core Modules -->
                        <h6 class="fw-bold text-warning mb-3 d-flex align-items-center gap-2" style="font-family:'Outfit', sans-serif;">
                            <i class="bi bi-puzzle-fill text-warning"></i>
                            <span>Modul Pendukung &amp; Manajemen Terpadu</span>
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="faq-card" data-category="exchange workflow">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-currency-exchange text-warning fs-5"></i>
                                        <h6 class="fw-bold text-white mb-0 small">Master Kurs Pajak (KMK)</h6>
                                    </div>
                                    <p class="small mb-0" style="font-size:0.82rem; color:#cbd5e1 !important; line-height: 1.6;">
                                        Manajemen Kurs Budget Forecast &amp; Kurs Mingguan Pajak Kemenkeu untuk konversi dollar ($) ke rupiah (Rp).
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="faq-card" data-category="workflow">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-tags-fill text-danger fs-5"></i>
                                        <h6 class="fw-bold text-white mb-0 small">Master Kategori Material</h6>
                                    </div>
                                    <p class="small mb-0" style="font-size:0.82rem; color:#cbd5e1 !important; line-height: 1.6;">
                                        Klasifikasi part number (Spruce Wood, Cast Iron Plate, Strings, Action Parts, dsb) per divisi pembelian.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="faq-card" data-category="roles workflow">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-clock-history text-info fs-5"></i>
                                        <h6 class="fw-bold text-white mb-0 small">Riwayat &amp; Audit Trail</h6>
                                    </div>
                                    <p class="small mb-0" style="font-size:0.82rem; color:#cbd5e1 !important; line-height: 1.6;">
                                        Pencatatan otomatis seluruh log penambahan, modifikasi, penghapusan, serta approval PO oleh pimpinan.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="faq-card" data-category="roles workflow">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-people-fill text-success fs-5"></i>
                                        <h6 class="fw-bold text-white mb-0 small">Users &amp; Monitoring Tim</h6>
                                    </div>
                                    <p class="small mb-0" style="font-size:0.82rem; color:#cbd5e1 !important; line-height: 1.6;">
                                        Manajemen otorisasi wewenang, hak akses spesifik user group, serta pemantauan produktivitas staff buyer.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- KPI Dashboard Utama Standards -->
                        <div class="p-4 rounded-4 border border-secondary border-opacity-30" style="background: rgba(15, 23, 42, 0.75);">
                            <h6 class="fw-bold text-warning mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-patch-check-fill text-warning fs-5"></i>
                                <span>Standar Indikator KPI Dashboard Utama &amp; Status Kesehatan Pengadaan</span>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(10, 15, 29, 0.85);">
                                        <div class="fw-bold text-info mb-2"><i class="bi bi-heart-pulse-fill me-1 text-danger"></i> KPI Card: Status Kesehatan Pengadaan</div>
                                        <p class="small mb-2.5" style="color:#cbd5e1 !important; font-size:0.82rem; line-height: 1.65;">
                                            Mengukur persentase pemenuhan penerimaan material (Deliveries) terhadap total target order PO dari pengisian seluruh buyer pada tahun berjalan.
                                        </p>
                                        <div class="d-flex flex-column gap-2" style="font-size: 0.8rem;">
                                            <div><span class="faq-pill-badge faq-pill-emerald me-1.5">🟢 AMAN &ge; 85%</span> : Pemenuhan kebutuhan material aman sesuai target lini produksi.</div>
                                            <div><span class="faq-pill-badge faq-pill-gold me-1.5">🟡 ON PROCESS 50%-84%</span> : Pengiriman bertahap (partial delivery) dari supplier sedang berlangsung.</div>
                                            <div><span class="faq-pill-badge faq-pill-rose me-1.5">🔴 ALERT &lt; 50%</span> : Kuantitas fisik di gudang masih di bawah 50%, risiko hambatan line assembly.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(10, 15, 29, 0.85);">
                                        <div class="fw-bold text-warning mb-2"><i class="bi bi-boxes me-1"></i> Rekapitulasi Stok Bulanan &amp; Kategori Aktif</div>
                                        <p class="small mb-0" style="color:#cbd5e1 !important; font-size:0.82rem; line-height: 1.75;">
                                            Menyajikan akumulasi hasil pengadaan dari <strong>seluruh user / buyer</strong> per bulannya. Mengompilasikan total barang masuk (Step 3), pemakaian produksi nyata (Step 5), estimasi saldo stok akhir berjalan, serta kategori material yang aktif digunakan tanpa menyebabkan tumpang tindih data.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── SINKRONISASI ALUR WAKTU & 3-LAYER ARCHITECTURE ── -->
                        <div class="mt-4 p-4 rounded-4 border border-info border-opacity-30" id="faq-period-alignment" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(10, 18, 35, 0.95) 100%);">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2 border-bottom border-secondary border-opacity-25">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="faq-pill-badge faq-pill-cyan">
                                        <i class="bi bi-clock-history me-1"></i> 3-LAYER ARCHITECTURE
                                    </span>
                                    <h6 class="fw-bold text-white mb-0 brand-font fs-6">
                                        Sinkronisasi Alur Waktu &amp; Quality Control (QC) Analisis Purchasing
                                    </h6>
                                </div>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success px-3 py-1 rounded-pill font-monospace" style="font-size:0.75rem;">
                                    <i class="bi bi-shield-check me-1"></i> Zero Division-by-Zero Protection
                                </span>
                            </div>

                            <p class="small mb-3" style="color: #cbd5e1 !important; line-height: 1.7; font-size: 0.84rem;">
                                Modul Analisis Komparasi Purchasing (Step 7) menerapkan <strong>3-Layer Architecture</strong> untuk menyelaraskan 4 dimensi periode waktu yang berbeda agar komparasi data finansial, volume unit, dan pergerakan stok tetap konsisten, adil, dan bebas dari bias interpretasi:
                            </p>

                            <div class="row g-3 mb-3">
                                <!-- 1. Forecast Period -->
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="p-3 rounded-3 border border-info border-opacity-30 h-100" style="background: rgba(10, 15, 29, 0.85);">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <span class="text-info small fw-bold font-monospace" style="font-size:0.72rem;">1. RENCANA ANGGARAN</span>
                                            <span class="badge bg-info bg-opacity-20 text-info rounded-pill" style="font-size:0.65rem;">12 Bulan</span>
                                        </div>
                                        <h6 class="fw-bold text-white mb-1" style="font-size:0.88rem;"><i class="bi bi-calendar-check text-info me-1"></i>Forecast Period</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem; line-height:1.6;">
                                            Horizon 12 bulan perencanaan (Januari &ndash; Desember atau Juli &ndash; Juni) sebagai baseline kebutuhan produksi dan plafon alokasi anggaran belanja pabrik.
                                        </p>
                                    </div>
                                </div>

                                <!-- 2. Actual Period -->
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="p-3 rounded-3 border border-success border-opacity-30 h-100" style="background: rgba(10, 15, 29, 0.85);">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <span class="text-success small fw-bold font-monospace" style="font-size:0.72rem;">2. REALISASI FISIK</span>
                                            <span class="badge bg-success bg-opacity-20 text-success rounded-pill" style="font-size:0.65rem;">Tervalidasi</span>
                                        </div>
                                        <h6 class="fw-bold text-white mb-1" style="font-size:0.88rem;"><i class="bi bi-box-seam text-success me-1"></i>Actual Period</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem; line-height:1.6;">
                                            Hanya mencakup bulan-bulan yang telah memiliki transaksi fisik penerimaan barang incoming yang telah lolos verifikasi surat jalan dan inspeksi mutu IAD.
                                        </p>
                                    </div>
                                </div>

                                <!-- 3. Current Running Period -->
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="p-3 rounded-3 border border-warning border-opacity-30 h-100" style="background: rgba(10, 15, 29, 0.85);">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <span class="text-warning small fw-bold font-monospace" style="font-size:0.72rem;">3. BULAN BERJALAN</span>
                                            <span class="badge bg-warning bg-opacity-20 text-warning rounded-pill" style="font-size:0.65rem;">Operasional</span>
                                        </div>
                                        <h6 class="fw-bold text-white mb-1" style="font-size:0.88rem;"><i class="bi bi-lightning-charge text-warning me-1"></i>Running Period</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem; line-height:1.6;">
                                            Posisi operasional bulan aktif yang sedang berjalan saat ini, menjadi titik tolak evaluasi pencapaian aktual terhadap target pengadaan berjalan.
                                        </p>
                                    </div>
                                </div>

                                <!-- 4. Outstanding PO Backlog -->
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="p-3 rounded-3 border border-purple border-opacity-30 h-100" style="background: rgba(10, 15, 29, 0.85); border-color: rgba(168, 85, 247, 0.35) !important;">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <span class="small fw-bold font-monospace" style="font-size:0.72rem; color:#c084fc;">4. PIPELINE PASOKAN</span>
                                            <span class="badge rounded-pill" style="background: rgba(168,85,247,0.2); color:#e9d5ff; font-size:0.65rem;">PO Berjalan</span>
                                        </div>
                                        <h6 class="fw-bold text-white mb-1" style="font-size:0.88rem;"><i class="bi bi-file-earmark-text text-purple me-1" style="color:#c084fc;"></i>Outstanding PO</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem; line-height:1.6;">
                                            Pasokan penopang berupa pesanan PO yang sudah dirilis ke vendor namun masih dalam proses perkapalan / pengiriman darat menuju pabrik.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 rounded-3 border border-secondary border-opacity-25" style="background: rgba(7, 11, 20, 0.75);">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-info-circle-fill text-info"></i>
                                    <strong class="text-white small">Prinsip Komparasi yang Adil (Fair Comparison Rule):</strong>
                                </div>
                                <p class="text-muted small mb-0" style="font-size:0.8rem; line-height:1.65;">
                                    Evaluasi ketercapaian dan variansi biaya (Slide 1) dihitung dengan membandingkan <em>Total Realisasi Incoming</em> hanya terhadap <em>Forecast pada periode yang telah berjalan (Comparable Period)</em>, bukan terhadap total 12 bulan penuh. Hal ini mencegah kesalahan penilaian yang menganggap bulan masa depan bernilai nol (0) atau gagal kirim.
                                </p>
                            </div>
                        </div>

                    </div>

                    <!-- ════════════════════════════════════════════════════════════════
                         TAB 2: RUMUS, FORMULA & KALKULASI RESMI PT KAWAI INDONESIA
                         ════════════════════════════════════════════════════════════════ -->
                    <div class="tab-pane fade" id="faq-formulas" role="tabpanel" aria-labelledby="faq-formulas-tab">
                        
                        <div class="alert alert-warning border-warning border-opacity-30 bg-warning bg-opacity-10 rounded-4 p-3 mb-4 text-white d-flex align-items-center gap-3">
                            <i class="bi bi-award-fill text-warning fs-3 flex-shrink-0"></i>
                            <div>
                                <strong class="text-white fs-6">Standar Rumus Perhitungan Resmi Purchasing PT Kawai Indonesia</strong>
                                <div class="small text-light opacity-90">Seluruh rumus matematika di bawah ini telah divalidasi dan dihitung secara otomatis oleh sistem backend &amp; frontend secara presisi.</div>
                            </div>
                        </div>

                        <div class="row g-4">
                            
                            <!-- Rumus 1: Stock Roll-Forward -->
                            <div class="col-12">
                                <div class="faq-card border-accent-blue" data-category="formula">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="faq-pill-badge faq-pill-blue">RUMUS 1</span>
                                            <h6 class="fw-bold mb-0 text-white fs-5">Kalkulasi Stok Berjalan Bulanan (<code class="text-primary font-monospace">Stock Bulan ke-i</code>)</h6>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 text-light small" onclick="copySopText('Stock Bulan ke-i = Stock Bulan Sebelumnya + PO Bulan Ini - PROD Bulan Ini', this)">
                                            <i class="bi bi-clipboard"></i> Salin Rumus
                                        </button>
                                    </div>
                                    
                                    <div class="formula-display-box my-3">
                                        Stock Bulan ke-i = Stock Bulan Sebelumnya + PO (Order) Bulan Ini - PROD (Produksi) Bulan Ini
                                    </div>

                                    <p class="small mb-2" style="line-height: 1.7; color: #cbd5e1 !important;">
                                        <strong>Logika Roll-Forward:</strong> Stok akhir dihitung secara berantai progresif (running balance). Saldo akhir dari bulan sebelumnya (misal: Juni) menjadi modal persediaan awal, kemudian ditambah dengan rencana/realisasi kedatangan pesanan PO pada bulan berjalan (Juli), dan dikurangi kebutuhan penggunaan material di lini perakitan pabrik pada bulan berjalan (Juli).
                                    </p>
                                    <div class="p-3 rounded-3 border border-secondary border-opacity-25 small" style="background: rgba(15, 23, 42, 0.75); color: #cbd5e1 !important;">
                                        💡 <strong>Simulasi Kasus Nyata:</strong> Jika Stok akhir Juni = <strong>50 unit</strong>, lalu pada bulan Juli masuk pesanan PO = <strong>30 unit</strong> dan digunakan untuk produksi PROD = <strong>20 unit</strong>, maka: <br>
                                        <span class="text-warning fw-bold font-monospace">Stock Juli = 50 + 30 - 20 = 60 unit.</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Rumus 2: Live Ratio % -->
                            <div class="col-12">
                                <div class="faq-card border-accent-gold" data-category="formula">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="faq-pill-badge faq-pill-gold">RUMUS 2</span>
                                            <h6 class="fw-bold mb-0 text-white fs-5">Kalkulasi Live Ratio Kecukupan Stok (<code class="text-warning font-monospace">Ratio % Bulan ke-i</code>)</h6>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 text-light small" onclick="copySopText('Ratio (%) Bulan ke-i = ( Stock Bulan ke-i / PROD Bulan Berikutnya [M+1] ) × 100%', this)">
                                            <i class="bi bi-clipboard"></i> Salin Rumus
                                        </button>
                                    </div>
                                    
                                    <div class="formula-display-box my-3" style="border-left-color: #f59e0b;">
                                        Ratio (%) Bulan ke-i = ( Stock Bulan ke-i / PROD Bulan Berikutnya [M+1] ) × 100%
                                    </div>

                                    <p class="small mb-3" style="line-height: 1.7; color: #cbd5e1 !important;">
                                        <strong>Fungsi Rasio:</strong> Mengukur sejauh mana stok yang tersedia di akhir bulan berjalan sanggup menutup atau mengamankan jalannya kebutuhan produksi pada <strong>bulan berikutnya (M+1)</strong> agar tidak terjadi <em>line-stop</em> perakitan piano.
                                    </p>
                                    
                                    <h6 class="fw-bold text-white small mb-2">🎯 Klasifikasi &amp; Ambang Batas Warna Live Ratio PT Kawai:</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-danger bg-danger bg-opacity-10 h-100">
                                                <div class="fw-bold text-danger mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> &lt; 100% (Kritis / Supply Alert)</div>
                                                <p class="small mb-0" style="color: #cbd5e1 !important; font-size: 0.82rem; line-height: 1.6;">Stok akhir bulan ini <strong>tidak mencukupi kebutuhan produksi bulan depan</strong>. Perlu percepatan kedatangan PO vendor segera.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-warning bg-warning bg-opacity-10 h-100">
                                                <div class="fw-bold text-warning mb-1"><i class="bi bi-check-circle-fill me-1"></i> 100% &ndash; 200% (Normal &amp; Ideal)</div>
                                                <p class="small mb-0" style="color: #cbd5e1 !important; font-size: 0.82rem; line-height: 1.6;">Stok berada pada rentang aman standar Kawai, cukup untuk mengamankan jalannya perakitan 1 hingga 2 bulan ke depan.</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-success bg-success bg-opacity-10 h-100">
                                                <div class="fw-bold text-success mb-1"><i class="bi bi-arrow-up-circle-fill me-1"></i> &gt; 200% (Overstock / Berlebih)</div>
                                                <p class="small mb-0" style="color: #cbd5e1 !important; font-size: 0.82rem; line-height: 1.6;">Stok melebihi kebutuhan 2 bulan produksi ke depan. Evaluasi jadwal kedatangan agar gudang tidak menumpuk.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rumus 3: Selisih Rekonsiliasi -->
                            <div class="col-12">
                                <div class="faq-card border-accent-cyan" data-category="formula">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="faq-pill-badge faq-pill-cyan">RUMUS 3</span>
                                            <h6 class="fw-bold mb-0 text-white fs-5">Kalkulasi Selisih &amp; Akurasi Rekonsiliasi (<code class="text-info font-monospace">Outstanding vs Actual</code>)</h6>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 text-light small" onclick="copySopText('Selisih = Outstanding Qty (Barang Tertunda) - Actual Production (Pemakaian Nyata)', this)">
                                            <i class="bi bi-clipboard"></i> Salin Rumus
                                        </button>
                                    </div>

                                    <div class="formula-display-box my-3" style="border-left-color: #06b6d4;">
                                        Selisih = Outstanding Qty (Barang Tertunda) - Actual Production (Pemakaian Nyata)
                                    </div>

                                    <div class="row g-3 small">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(15, 23, 42, 0.75); color: #cbd5e1 !important;">
                                                <strong class="text-primary d-block mb-1">📦 Selisih &gt; 0 (Surplus Outstanding):</strong>
                                                Barang yang dipesan dari vendor masih dalam proses pengiriman via laut/darat. Sisa pasokan aman dalam perjalanan.
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(15, 23, 42, 0.75); color: #cbd5e1 !important;">
                                                <strong class="text-success d-block mb-1">✅ Selisih = 0 (Seimbang Sempurna):</strong>
                                                Kedatangan barang dari supplier pas dan setara dengan pemakaian perakitan di pabrik. Rekonsiliasi data 100% klop.
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(15, 23, 42, 0.75); color: #cbd5e1 !important;">
                                                <strong class="text-danger d-block mb-1">⚠️ Selisih &lt; 0 (Perlu Verifikasi):</strong>
                                                Pemakaian produksi di pabrik melebihi catatan sisa barang tertunda. Periksa apakah ada penerimaan fisik belum di-input ke PO.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rumus 4 & 5: Konversi Rupiah Kurs Pajak -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-emerald" data-category="formula exchange">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="faq-pill-badge faq-pill-emerald">RUMUS 4</span>
                                        <h6 class="fw-bold mb-0 text-white">Konversi Rupiah Forecast (<code class="text-success font-monospace">Forecast IDR</code>)</h6>
                                    </div>
                                    <div class="formula-display-box my-2" style="font-size: 0.88rem; border-left-color: #10b981;">
                                        Forecast Amount (Rp) = Forecast Amount ($) × Kurs Budget Bulanan
                                    </div>
                                    <p class="small mb-0" style="line-height: 1.6; font-size: 0.82rem; color: #cbd5e1 !important;">
                                        Mengonversi perkiraan anggaran pengadaan dari dollar ($) ke rupiah (Rp) berdasarkan kurs penetapan anggaran tahunan yang dikunci pada Master Kurs.
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="faq-card border-accent-rose" data-category="formula exchange">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="faq-pill-badge faq-pill-rose">RUMUS 5</span>
                                        <h6 class="fw-bold mb-0 text-white">Konversi Rupiah Incoming (<code class="text-danger font-monospace">Actual IDR</code>)</h6>
                                    </div>
                                    <div class="formula-display-box my-2" style="font-size: 0.88rem; border-left-color: #ef4444;">
                                        Incoming Amount (Rp) = Actual Qty Received × Price ($) × Kurs Pajak Mingguan KMK
                                    </div>
                                    <p class="small mb-0" style="line-height: 1.6; font-size: 0.82rem; color: #cbd5e1 !important;">
                                        Penerimaan barang fisik dihitung presisi berdasarkan tanggal transaksi penerimaan gudang yang otomatis mencocokkan Kurs Pajak Kemenkeu minggu ke-1 s/d 5.
                                    </p>
                                </div>
                            </div>

                            <!-- Rumus 6: Step 6 Inventory Supply Gap & Potential Supply -->
                            <div class="col-12">
                                <div class="faq-card border-accent-purple" data-category="formula">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="faq-pill-badge faq-pill-purple">RUMUS 6</span>
                                            <h6 class="fw-bold mb-0 text-white fs-5">Kalkulasi Potential Supply, Gap &amp; Coverage Inventory (Step 6)</h6>
                                        </div>
                                    </div>

                                    <div class="formula-display-box my-3" style="border-left-color: #8b5cf6;">
                                        Potential Supply = Actual Stock Fisik + Outstanding PO Qty<br>
                                        Net Supply Gap   = Potential Supply - Inventory Demand (Kebutuhan Target)<br>
                                        Coverage Ratio   = ( Potential Supply / Inventory Demand ) × 100%
                                    </div>

                                    <p class="small mb-0" style="line-height: 1.7; color: #cbd5e1 !important;">
                                        <strong>Logika Evaluasi Pasokan Step 6:</strong> Menggabungkan stok riil di gudang dengan pesanan PO yang masih dalam perjalanan untuk mengetahui apakah total potensi pasokan mampu meng-<em>cover</em> target kebutuhan material. Jika <code>Net Supply Gap &lt; 0</code>, sistem menetapkan status <strong>CRITICAL DEFICIT</strong> dan merekomendasikan penerbitan PO baru.
                                    </p>
                                </div>
                            </div>

                            <!-- Interactive Live Calculator Widget -->
                            <div class="col-12">
                                <div class="p-4 rounded-4 border border-warning border-opacity-40" style="background: rgba(226, 179, 74, 0.06);">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-calculator-fill text-warning fs-4"></i>
                                            <div>
                                                <h6 class="fw-bold text-white mb-0">Simulasi Interaktif: Uji Rumus Stok &amp; Live Ratio %</h6>
                                                <small class="text-muted">Ketik angka di bawah untuk melihat kalkulasi otomatis rumus secara instan</small>
                                            </div>
                                        </div>
                                        <span class="faq-pill-badge faq-pill-gold">LIVE TEST ENGINE</span>
                                    </div>

                                    <div class="row g-3 align-items-end">
                                        <div class="col-6 col-md-3">
                                            <label class="form-label text-muted small fw-semibold">Stok Bulan Lalu (M-1):</label>
                                            <input type="number" id="calcPrevStock" class="form-control calc-input" value="50" oninput="runFaqCalculator()">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label text-muted small fw-semibold">+ PO Masuk (Bulan Ini):</label>
                                            <input type="number" id="calcPoQty" class="form-control calc-input" value="30" oninput="runFaqCalculator()">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label text-muted small fw-semibold">- PROD (Bulan Ini):</label>
                                            <input type="number" id="calcProdQty" class="form-control calc-input" value="20" oninput="runFaqCalculator()">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label text-muted small fw-semibold">PROD Bulan Depan (M+1):</label>
                                            <input type="number" id="calcNextProd" class="form-control calc-input" value="40" oninput="runFaqCalculator()">
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-3 pt-3 border-top border-secondary border-opacity-25">
                                        <div>
                                            <span class="text-muted small">Hasil Estimasi Stok Akhir:</span>
                                            <span id="calcResultStock" class="fw-bold text-warning fs-5 ms-2 font-monospace">60 unit</span>
                                        </div>
                                        <div>
                                            <span class="text-muted small">Live Ratio % Kecukupan:</span>
                                            <span id="calcResultRatio" class="fw-bold text-success fs-5 ms-2 font-monospace">150.0%</span>
                                            <span id="calcResultRatioBadge" class="faq-pill-badge faq-pill-gold ms-2">Normal &amp; Ideal</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ════════════════════════════════════════════════════════════════
                         TAB 3: TIPS, BEST PRACTICES & SINKRONISASI SISTEM
                         ════════════════════════════════════════════════════════════════ -->
                    <div class="tab-pane fade" id="faq-workflow" role="tabpanel" aria-labelledby="faq-workflow-tab">
                        <div class="row g-4">
                            
                            <div class="col-md-6">
                                <div class="faq-card border-accent-gold" data-category="tips">
                                    <h6 class="fw-bold text-white mb-2"><i class="bi bi-arrow-repeat text-warning me-2"></i> Sinkronisasi Otomatis Terpadu (One-Time Input)</h6>
                                    <p class="small mb-2" style="line-height: 1.7; color: #cbd5e1 !important;">
                                        Sistem didesain untuk memangkas duplikasi kerja penginputan data:
                                    </p>
                                    <ul class="text-light small ps-3 mb-0" style="line-height: 1.8;">
                                        <li><strong>Linking Master Outstanding &amp; Actual:</strong> Saat Anda menginput atau mengubah kedatangan barang pada Step 3, kuantitas outstanding pada Step 4 dan matriks komparasi Step 7 langsung ter-update otomatis secara real-time.</li>
                                        <li><strong>Audit Otomatis:</strong> Setiap aktivitas penambahan, edit, atau hapus otomatis mencatat timestamp dan nama user pada modul <strong>Riwayat &amp; Audit</strong>.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="faq-card border-accent-blue" data-category="tips">
                                    <h6 class="fw-bold text-white mb-2"><i class="bi bi-calendar-range text-info me-2"></i> Fleksibilitas Durasi &amp; Bulan Mulai (1 s/d 36 Bulan)</h6>
                                    <p class="small mb-2" style="line-height: 1.7; color: #cbd5e1 !important;">
                                        Pada dashboard Step 7 Komparasi dan pop-up monitoring:
                                    </p>
                                    <ul class="text-light small ps-3 mb-0" style="line-height: 1.8;">
                                        <li><strong>Start Month:</strong> Pilih bulan mulai (misal: <em>JUNE</em>) sebagai jangkar perhitungan awal modal stok.</li>
                                        <li><strong>Duration Dropdown:</strong> Sesuaikan rentang tampilan (1 s/d 36 bulan). Tabel menyaring baris secara presisi tanpa perlu melakukan scroll manual yang melelahkan.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="faq-card border-accent-emerald" data-category="tips">
                                    <h6 class="fw-bold text-white mb-2"><i class="bi bi-file-earmark-excel text-success me-2"></i> Standar Impor Bulk Excel / CSV (SheetJS Validator)</h6>
                                    <p class="small mb-2" style="line-height: 1.7; color: #cbd5e1 !important;">
                                        Untuk mempercepat input puluhan s/d ratusan data sekaligus:
                                    </p>
                                    <ul class="text-light small ps-3 mb-0" style="line-height: 1.8;">
                                        <li>Unduh <strong>Template Excel Resmi</strong> yang disediakan di header masing-masing halaman.</li>
                                        <li>Pastikan kolom <em>Part Number, Factory Code, Target Qty,</em> dan <em>Price ($)</em> terisi sesuai format tanpa mengubah header tabel.</li>
                                        <li>Sistem dilengkapi <strong>SheetJS Live Preview</strong> yang memeriksa data duplikat dan validitas sebelum disimpan permanen ke database.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="faq-card border-accent-cyan" data-category="tips">
                                    <h6 class="fw-bold text-white mb-2"><i class="bi bi-currency-dollar text-info me-2"></i> Kebijakan Multi-Currency &amp; Variansi Harga</h6>
                                    <p class="small mb-2" style="line-height: 1.7; color: #cbd5e1 !important;">
                                        Evaluasi transaksi ekspor/impor bahan baku piano:
                                    </p>
                                    <ul class="text-light small ps-3 mb-0" style="line-height: 1.8;">
                                        <li>Gunakan tombol toggle <strong>Dollar Only ($)</strong> atau <strong>Rupiah Only (Rp)</strong> untuk menyesuaikan format laporan meeting divisi.</li>
                                        <li>Buka pop-up <strong>Item Variansi Harga</strong> untuk mendeteksi part number mana yang harganya naik/turun di atas toleransi standar anggaran.</li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ════════════════════════════════════════════════════════════════
                         TAB 4: ROLE, MATRIKS OTORISASI & PEMBAGIAN PIC PURCHASING
                         ════════════════════════════════════════════════════════════════ -->
                    <div class="tab-pane fade" id="faq-roles" role="tabpanel" aria-labelledby="faq-roles-tab">
                        <div class="alert alert-info border-info border-opacity-30 bg-info bg-opacity-10 d-flex align-items-center gap-3 rounded-4 p-3 mb-4">
                            <i class="bi bi-shield-lock-fill text-info fs-3 flex-shrink-0"></i>
                            <div class="text-white">
                                <strong class="fs-6">Matriks Pembagian Peran (Role-Based Access Control) PT Kawai Indonesia</strong>
                                <div class="small text-light opacity-90">Penjelasan wewenang otorisasi modul, hak approval, dan tanggung jawab kerja seluruh personel tim Procurement.</div>
                            </div>
                        </div>

                        <div class="row g-4">
                            
                            <!-- Role 1: Supervisor / Manager -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-rose" data-category="roles">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-rose">
                                            <i class="bi bi-shield-fill-check me-1"></i> SUPERVISOR / MANAGER
                                        </span>
                                        <span class="faq-pill-badge faq-pill-neutral">EXECUTIVE</span>
                                    </div>
                                    <div class="text-muted small mb-2"><i class="bi bi-building me-1"></i> Executive Procurement &amp; Supply Chain Management</div>
                                    <hr class="border-secondary opacity-25 my-2">
                                    <strong class="text-warning small d-block mb-1"><i class="bi bi-check2-square me-1"></i> Wewenang &amp; Tanggung Jawab Utama:</strong>
                                    <ul class="text-light small ps-3 mb-3" style="line-height: 1.6;">
                                        <li>Otorisasi &amp; Approval Resmi Penerbitan Draft Purchase Order (PO) ke Supplier.</li>
                                        <li>Keputusan final penerimaan / reject material hasil inspeksi mutu tim IAD.</li>
                                        <li>Pengawasan eksekutif terhadap kesehatan pengadaan dan evaluasi variansi kurs anggaran.</li>
                                    </ul>
                                    <strong class="text-info small d-block mb-1"><i class="bi bi-key me-1"></i> Modul Otorisasi:</strong>
                                    <div class="d-flex flex-wrap gap-1.5">
                                        <span class="faq-pill-badge faq-pill-neutral">Approval PO</span>
                                        <span class="faq-pill-badge faq-pill-neutral">Riwayat &amp; Audit Log</span>
                                        <span class="faq-pill-badge faq-pill-neutral">Executive Dashboard</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Role 2: Leader Procurement -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-cyan" data-category="roles">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-cyan">
                                            <i class="bi bi-person-gear me-1"></i> LEADER PROCUREMENT
                                        </span>
                                        <span class="faq-pill-badge faq-pill-neutral">SUPERVISORY</span>
                                    </div>
                                    <div class="text-muted small mb-2"><i class="bi bi-building me-1"></i> Procurement Control &amp; ETA Schedule Group</div>
                                    <hr class="border-secondary opacity-25 my-2">
                                    <strong class="text-warning small d-block mb-1"><i class="bi bi-check2-square me-1"></i> Wewenang &amp; Tanggung Jawab Utama:</strong>
                                    <ul class="text-light small ps-3 mb-3" style="line-height: 1.6;">
                                        <li>Koordinasi jadwal pengiriman supplier vendor dan pemantauan ETA kontainer.</li>
                                        <li>Supervisi pemenuhan kuantitas sisa PO outstanding bersama staff buyer.</li>
                                        <li>Monitoring kesehatan rasio kecukupan stok terhadap rencana pemakaian produksi.</li>
                                    </ul>
                                    <strong class="text-info small d-block mb-1"><i class="bi bi-key me-1"></i> Modul Otorisasi:</strong>
                                    <div class="d-flex flex-wrap gap-1.5">
                                        <span class="faq-pill-badge faq-pill-neutral">Monitoring Outstanding</span>
                                        <span class="faq-pill-badge faq-pill-neutral">ETA Tracking</span>
                                        <span class="faq-pill-badge faq-pill-neutral">Monitoring Performa Tim</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Role 3: Staff Buyer Material Akustik & Kayu -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-emerald" data-category="roles">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-emerald">
                                            <i class="bi bi-person-workspace me-1"></i> BUYER AKUSTIK &amp; KAYU
                                        </span>
                                        <span class="faq-pill-badge faq-pill-neutral">OPERATIONAL</span>
                                    </div>
                                    <div class="text-muted small mb-2"><i class="bi bi-building me-1"></i> Purchasing Acoustic Division (Piano Bench, Spruce Wood &amp; Soundboard)</div>
                                    <hr class="border-secondary opacity-25 my-2">
                                    <strong class="text-warning small d-block mb-1"><i class="bi bi-check2-square me-1"></i> Wewenang &amp; Tanggung Jawab Utama:</strong>
                                    <ul class="text-light small ps-3 mb-3" style="line-height: 1.6;">
                                        <li>Input &amp; penyusunan Draft Order PO komponen kayu akustik, soundboard, dan piano bench.</li>
                                        <li>Input incoming penerimaan fisik material dan konfirmasi surat jalan penerimaan gudang.</li>
                                        <li>Pembaruan rencana kebutuhan forecast bulanan per part number.</li>
                                    </ul>
                                    <strong class="text-info small d-block mb-1"><i class="bi bi-key me-1"></i> Modul Otorisasi:</strong>
                                    <div class="d-flex flex-wrap gap-1.5">
                                        <span class="faq-pill-badge faq-pill-neutral">Draft Order PO</span>
                                        <span class="faq-pill-badge faq-pill-neutral">Input Incoming</span>
                                        <span class="faq-pill-badge faq-pill-neutral">Forecast Master</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Role 4: Staff Buyer Hardware & Metal -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-purple" data-category="roles">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-purple">
                                            <i class="bi bi-gear-fill me-1"></i> BUYER HARDWARE &amp; METAL
                                        </span>
                                        <span class="faq-pill-badge faq-pill-neutral">OPERATIONAL</span>
                                    </div>
                                    <div class="text-muted small mb-2"><i class="bi bi-building me-1"></i> Purchasing Metal Division (Cast Iron Plate, Senar Baja, Screws &amp; Pedals)</div>
                                    <hr class="border-secondary opacity-25 my-2">
                                    <strong class="text-warning small d-block mb-1"><i class="bi bi-check2-square me-1"></i> Wewenang &amp; Tanggung Jawab Utama:</strong>
                                    <ul class="text-light small ps-3 mb-3" style="line-height: 1.6;">
                                        <li>Input draft order PO komponen besi cor (iron plate), senar musik, pedal, dan aksesoris metal.</li>
                                        <li>Pembaruan progress kuantitas terkirim (shipped status) dari vendor luar negeri.</li>
                                        <li>Verifikasi drawing teknis dan kelengkapan sertifikat material supplier.</li>
                                    </ul>
                                    <strong class="text-info small d-block mb-1"><i class="bi bi-key me-1"></i> Modul Otorisasi:</strong>
                                    <div class="d-flex flex-wrap gap-1.5">
                                        <span class="faq-pill-badge faq-pill-neutral">Draft Order PO</span>
                                        <span class="faq-pill-badge faq-pill-neutral">Update Shipped Qty</span>
                                        <span class="faq-pill-badge faq-pill-neutral">Master Kategori</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Role 5: Admin Sistem & IT -->
                            <div class="col-12">
                                <div class="faq-card border-accent-gold" data-category="roles">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-gold">
                                            <i class="bi bi-shield-lock-fill me-1"></i> ADMINISTRATOR SISTEM &amp; IT
                                        </span>
                                        <span class="faq-pill-badge faq-pill-neutral">FULL CONTROL (ALL PRIVILEGES)</span>
                                    </div>
                                    <p class="small mb-2" style="color: #cbd5e1 !important; line-height: 1.65;">
                                        Memiliki hak akses penuh untuk membuat/mengedit akun user, mengatur hak izin akses per modul, melakukan reset database, pengelolaan master kurs pajak, backup data, serta auditing berkala terhadap keamanan sistem.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ════════════════════════════════════════════════════════════════
                         TAB 5: LOGBOOK & SOP CARA PENGGUNAAN SISTEM (STEP-BY-STEP RUNBOOK)
                         ════════════════════════════════════════════════════════════════ -->
                    <div class="tab-pane fade" id="faq-logbook" role="tabpanel" aria-labelledby="faq-logbook-tab">
                        
                        <div class="alert alert-danger border-danger border-opacity-30 bg-danger bg-opacity-10 d-flex align-items-center justify-content-between flex-wrap gap-3 rounded-4 p-3 mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle p-2 bg-danger bg-opacity-25 text-danger fs-3 flex-shrink-0">
                                    <i class="bi bi-journal-bookmark-fill"></i>
                                </div>
                                <div class="text-white">
                                    <strong class="fs-6">📖 Logbook &amp; Standar Operasional Prosedur (SOP) Digital PT Kawai Indonesia</strong>
                                    <div class="small text-light opacity-90 mt-0.5">Panduan resmi langkah-demi-langkah (Standard Operating Procedures &amp; Runbook) untuk seluruh modul operasional sistem procurement.</div>
                                </div>
                            </div>
                            <span class="faq-pill-badge faq-pill-rose">
                                10 RESMI SOP PROSEDUR
                            </span>
                        </div>

                        <!-- 10 Detailed SOP Runbook Cards -->
                        <div class="row g-4">

                            <!-- SOP 01: Step 1 Forecast -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-blue" data-category="sop workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-blue">SOP-PUR-01</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Buyer &amp; PPIC</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #60a5fa !important;"><i class="bi bi-graph-up text-primary me-1.5"></i>Prosedur Input Forecast Master Data (Step 1)</h6>
                                    <ol class="text-light small ps-3 mb-3" style="line-height: 1.8;">
                                        <li>Buka menu <strong>Alur Purchasing &rarr; Step 1: Master Data (Forecast)</strong>.</li>
                                        <li>Klik tombol <strong class="text-warning">+ Tambah Forecast Baru</strong> atau gunakan fitur <em>Impor Excel Bulk</em> untuk unggah massal.</li>
                                        <li>Lengkapi parameter: <strong>Part Number, Description, Supplier, Factory Code</strong>, dan rencana <strong>Target Kebutuhan Bulanan (M0 s/d M12)</strong>.</li>
                                        <li>Simpan data. Nilai target otomatis menjadi basis penghitungan modal perputaran stok roll-forward.</li>
                                    </ol>
                                    <div class="pt-2.5 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                        <span class="faq-pill-badge faq-pill-neutral">Siklus: Bulanan / Tahunan</span>
                                        <a href="{{ route('purchasing.outstanding') }}" class="faq-action-pill">Buka Step 1 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP 02: Step 2 Master PO -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-emerald" data-category="sop workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-emerald">SOP-PUR-02</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Buyer / Staff Purchasing</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #34d399 !important;"><i class="bi bi-file-earmark-text text-success me-1.5"></i>Prosedur Pembuatan PO &amp; Jadwal ETA (Step 2)</h6>
                                    <ol class="text-light small ps-3 mb-3" style="line-height: 1.8;">
                                        <li>Akses modul <strong>Step 2: Master PO</strong>.</li>
                                        <li>Klik <strong class="text-white">+ Buat Draft PO Baru</strong>. Masukkan Nomor PO resmi, Nama Supplier, Harga Satuan ($), dan Kuantitas Order.</li>
                                        <li>Isi parameter <strong class="text-warning">ETA (Estimated Time of Arrival)</strong> agar jadwal kedatangan kontainer terlacak di kalender.</li>
                                        <li>Status PO awal berstatus <span class="faq-pill-badge faq-pill-gold">⏳ Menunggu Approval</span>, kemudian diajukan ke Leader / Manager.</li>
                                    </ol>
                                    <div class="pt-2.5 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                        <span class="faq-pill-badge faq-pill-neutral">Siklus: Transaksional PO</span>
                                        <a href="{{ route('purchasing.master-po') }}" class="faq-action-pill">Buka Step 2 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP 03: Step 3 Incoming Penerimaan & Mutu IAD -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-cyan" data-category="sop workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-cyan">SOP-PUR-03</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Gudang &amp; Tim IAD</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #22d3ee !important;"><i class="bi bi-truck-flatbed text-info me-1.5"></i>Prosedur Penerimaan Fisik (Incoming PO &amp; Mutu IAD) (Step 3)</h6>
                                    <ol class="text-light small ps-3 mb-3" style="line-height: 1.8;">
                                        <li>Masuk ke menu <strong>Step 3: Incoming Penerimaan PO</strong>.</li>
                                        <li>Pilih <strong>Nomor PO</strong> atau cari berdasarkan Part Number material yang tiba.</li>
                                        <li>Input jumlah <strong class="text-warning">Aktual Diterima (Unit)</strong> fisik di gudang pabrik dan tanggal surat jalan.</li>
                                        <li>Pilih status <strong>Pemeriksaan IAD Mutu</strong> (Lolos Mutu / Karantina / Reject). Sistem secara otomatis menghitung selisih pending sisa order.</li>
                                    </ol>
                                    <div class="pt-2.5 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                        <span class="faq-pill-badge faq-pill-neutral">Siklus: Harian Kedatangan</span>
                                        <a href="{{ route('purchasing.input') }}" class="faq-action-pill">Buka Step 3 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP 04: Step 4 Outstanding Tracking -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-purple" data-category="sop workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-purple">SOP-PUR-04</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Leader &amp; Buyer</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #c084fc !important;"><i class="bi bi-boxes text-purple me-1.5"></i>Prosedur Monitoring &amp; Update Outstanding PO (Step 4)</h6>
                                    <ol class="text-light small ps-3 mb-3" style="line-height: 1.8;">
                                        <li>Buka dashboard <strong>Step 4: Monitoring Outstanding PO</strong>.</li>
                                        <li>Pantau progress bar pemenuhan (<span class="faq-pill-badge faq-pill-emerald">Complete</span> atau <span class="faq-pill-badge faq-pill-gold">On Progress</span>).</li>
                                        <li>Klik tombol <strong class="text-warning">Edit</strong> jika ada revisi jadwal ETA atau penyesuaian kuantitas yang disepakati bersama supplier vendor.</li>
                                        <li>Jika seluruh barang telah tiba 100%, sistem otomatis menandai PO tuntas.</li>
                                    </ol>
                                    <div class="pt-2.5 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                        <span class="faq-pill-badge faq-pill-neutral">Siklus: Mingguan / Bulanan</span>
                                        <a href="{{ route('purchasing.outstanding-po') }}" class="faq-action-pill">Buka Step 4 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP 05: Step 5 Output Produksi -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-gold" data-category="sop workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-gold">SOP-PUR-05</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Produksi &amp; PPIC</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #fbbf24 !important;"><i class="bi bi-gear-fill text-warning me-1.5"></i>Prosedur Pencatatan Aktual Produksi Pabrik (Step 5)</h6>
                                    <ol class="text-light small ps-3 mb-3" style="line-height: 1.8;">
                                        <li>Akses modul <strong>Step 5: Aktual Produksi</strong>.</li>
                                        <li>Pilih Periode Bulan &amp; Part Number komponen yang dipakai pada lini perakitan piano.</li>
                                        <li>Input angka <strong class="text-info">Actual Production (Output Unit)</strong> riil pabrik. Angka pemakaian ini otomatis mengurangi modal stok akhir berjalan.</li>
                                    </ol>
                                    <div class="pt-2.5 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                        <span class="faq-pill-badge faq-pill-neutral">Siklus: Harian / Bulanan</span>
                                        <a href="{{ route('purchasing.actual-production') }}" class="faq-action-pill">Buka Step 5 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP 06: Step 6 Aktual Inventory -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-purple" data-category="sop workflow">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-purple">SOP-PUR-06</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Gudang &amp; Inventory Auditor</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #e879f9 !important;"><i class="bi bi-box-seam text-purple me-1.5"></i>Prosedur Stock Opname &amp; Supply Integration (Step 6)</h6>
                                    <ol class="text-light small ps-3 mb-3" style="line-height: 1.8;">
                                        <li>Buka menu <strong>Step 6: Aktual Inventory &amp; Supply Integration</strong>.</li>
                                        <li>Unggah hasil stock opname fisik melalui <strong>Upload Excel (Preview)</strong> atau klik <strong>Input Log Fisik</strong>.</li>
                                        <li>Periksa status supply: <span class="faq-pill-badge faq-pill-emerald">SURPLUS</span>, <span class="faq-pill-badge faq-pill-blue">COVERED VIA PO</span>, atau <span class="faq-pill-badge faq-pill-rose">CRITICAL DEFICIT</span>.</li>
                                        <li>Centang checkbox untuk <strong>Hapus Terpilih</strong> jika ada baris keliru, atau gunakan tombol <strong>Hapus Semua Data</strong> saat reset tahunan.</li>
                                    </ol>
                                    <div class="pt-2.5 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                        <span class="faq-pill-badge faq-pill-neutral">Siklus: Bulanan Opname</span>
                                        <a href="{{ route('purchasing.actual-inventory') }}" class="faq-action-pill">Buka Step 6 &rarr;</a>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP 07: Step 7 Komparasi Finansial & Kurs Linking -->
                            <div class="col-12">
                                <div class="faq-card border-accent-rose" data-category="sop workflow exchange">
                                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                        <span class="faq-pill-badge faq-pill-rose">SOP-PUR-07</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Manager, Leader &amp; Accounting</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #f87171 !important;"><i class="bi bi-sliders text-danger me-1.5"></i>Prosedur Evaluasi Finansial 3-Slide, Kurs Pajak &amp; Variansi Harga (Step 7)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(15, 23, 42, 0.75);">
                                                <strong class="text-info d-block mb-1">1. Slide 1 (Kurs &amp; Finansial):</strong>
                                                <p class="small mb-0" style="color:#cbd5e1 !important; line-height: 1.6;">
                                                    Pilih mode mata uang <strong>[Dollar Only ($)]</strong> atau <strong>[Rupiah Only (Rp)]</strong>. Klik tombol <strong>Item Kenaikan / Penurunan Harga</strong> untuk menganalisis material dengan variansi signifikan.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(15, 23, 42, 0.75);">
                                                <strong class="text-warning d-block mb-1">2. Slide 2 (Infografis Tren):</strong>
                                                <p class="small mb-0" style="color:#cbd5e1 !important; line-height: 1.6;">
                                                    Gunakan filter Item Code, Vendor, dan No. PO untuk mengevaluasi konsistensi pengiriman serta tren deviasi pemenuhan target pesanan.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100" style="background: rgba(15, 23, 42, 0.75);">
                                                <strong class="d-block mb-1" style="color:#c084fc !important;">3. Slide 3 (Stok vs Inv):</strong>
                                                <p class="small mb-0" style="color:#cbd5e1 !important; line-height: 1.6;">
                                                    Bandingkan model estimasi persediaan roll-forward di sistem terhadap stok fisik riil hasil audit opname gudang (Step 6) untuk menguji akurasi saldo.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-3 mt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <span class="faq-pill-badge faq-pill-neutral">Siklus: Review Mingguan / Bulanan</span>
                                        <a href="{{ route('purchasing.analysis') }}" class="faq-action-pill px-3 py-1.5" style="border-color: rgba(239, 68, 68, 0.5); color: #fca5a5 !important;">
                                            Buka Dashboard Step 7 &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- SOP 08: Riwayat & Audit Trail -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-gold" data-category="sop">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-gold">SOP-PUR-08</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Supervisor, Leader &amp; Admin</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #fbbf24 !important;"><i class="bi bi-clock-history text-warning me-1.5"></i>Prosedur Riwayat, Approval, Reject &amp; Hapus Data</h6>
                                    <ol class="text-light small ps-3 mb-0" style="line-height: 1.8;">
                                        <li>Buka menu <strong>Riwayat</strong> di navigasi atas.</li>
                                        <li><strong>Approval:</strong> Supervisor / Leader mengklik tombol centang hijau untuk menyetujui log pesanan.</li>
                                        <li><strong>Reject / Revisi:</strong> Klik tombol putar kuning untuk menolak dan meminta revisi perbaikan kepada staff buyer.</li>
                                        <li><strong>Hapus Permanen:</strong> Khusus Admin / Supervisor dapat menghapus baris riwayat data keliru.</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- SOP 09: Master Kurs Pajak -->
                            <div class="col-md-6">
                                <div class="faq-card border-accent-cyan" data-category="sop exchange">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-cyan">SOP-PUR-09</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Admin Kurs &amp; Finance</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #22d3ee !important;"><i class="bi bi-currency-exchange text-info me-1.5"></i>Prosedur Pengelolaan Master Kurs Pajak Kemenkeu</h6>
                                    <ol class="text-light small ps-3 mb-0" style="line-height: 1.8;">
                                        <li>Masuk ke menu <strong>KURS</strong> di navigasi atas (URL: <code>/exchange-rate</code>).</li>
                                        <li><strong>Kurs Budget Bulanan:</strong> Masukkan estimasi kurs acuan rupiah (Jan s/d Des) pada panel <em>Forecast Kurs Budget</em>.</li>
                                        <li><strong>Kurs Pajak Mingguan:</strong> Klik <strong>+ Input Kurs Mingguan</strong> atau gunakan fitur <strong>Import Excel</strong> setiap rilis Keputusan Menteri Keuangan (KMK).</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- SOP 10: Manajemen User & Hak Akses -->
                            <div class="col-12">
                                <div class="faq-card border-accent-emerald" data-category="sop roles">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="faq-pill-badge faq-pill-emerald">SOP-PUR-10</span>
                                        <span class="text-muted small"><i class="bi bi-person me-1"></i>Administrator Sistem</span>
                                    </div>
                                    <h6 class="fw-bold text-white mb-2" style="color: #34d399 !important;"><i class="bi bi-people-fill text-success me-1.5"></i>Prosedur Manajemen Akun Pengguna, Hak Akses &amp; Monitoring Tim</h6>
                                    <ol class="text-light small ps-3 mb-0" style="line-height: 1.8;">
                                        <li>Akses menu <strong>USERS</strong> (khusus Admin) atau <strong>Monitoring Tim</strong> untuk melihat performa penginputan staf.</li>
                                        <li>Klik <strong>+ Tambah User Baru</strong> atau tombol <strong>Edit</strong> pada akun yang ingin disesuaikan.</li>
                                        <li>Tentukan <strong>Role Group</strong> (Admin, Supervisor, Leader, Staff, Viewer) dan centang <strong>Privileges Fitur</strong> yang diizinkan.</li>
                                        <li>Gunakan tombol <em>"Berikan Hak Akses Monitoring"</em> untuk mengizinkan staf senior memeriksa input rekan kerjanya.</li>
                                    </ol>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Footer Luxury -->
            <div class="modal-footer px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: linear-gradient(135deg, #0b1120 0%, #060913 100%) !important; border-top: 1px solid rgba(226, 179, 74, 0.3) !important; flex-shrink: 0;">
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <i class="bi bi-shield-fill-check text-warning fs-5"></i>
                    <span style="color:#cbd5e1 !important;">Sistem Purchasing &amp; Logistik Terpadu &bull; <strong>PT Kawai Indonesia</strong> &bull; v2.6 Enterprise</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-dismiss="modal" onclick="closeFaqModal()">
                        <i class="bi bi-check-lg fs-6"></i> Tutup Panduan
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
