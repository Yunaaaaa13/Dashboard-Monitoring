<!-- ════════════ MODAL POP-UP SELECTOR ITEM CODE ════════════ -->
<div class="modal fade" id="modalSelectItemCode" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border border-info border-opacity-50 shadow-lg" style="background: #0f172a !important; border-radius: 20px; color: #ffffff;">
            <div class="modal-header px-4 py-3 border-bottom border-secondary border-opacity-25" style="background: #1e293b !important;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam-fill text-info fs-4"></i>
                    <span>Pilih Item Code Material (Terdaftar)</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: #0b1120 !important;">
                <!-- Live Search Box -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-dark border-info text-info"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchItemCodeModalInput" class="form-control bg-dark text-white border-info" placeholder="Ketik untuk mencari Item Code atau Nama Material..." onkeyup="filterItemCodeModalTable()">
                </div>

                <!-- Table Item Codes -->
                <div class="table-responsive rounded-3 border border-secondary border-opacity-25 style-scrollbar" style="max-height: 380px;">
                    <table class="table table-dark table-hover align-middle mb-0" id="tableModalItemCodes" style="font-size: 0.9rem;">
                        <thead class="bg-dark text-info fw-bold sticky-top" style="background: #1e293b !important;">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="width: 180px;">Item Code</th>
                                <th>Deskripsi Material</th>
                                <th style="width: 110px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="trCustomItemCodeOption" style="display:none;" class="table-primary bg-primary bg-opacity-25 border border-info">
                                <td class="text-center text-info"><i class="bi bi-plus-circle-fill fs-5"></i></td>
                                <td colspan="2">
                                    <span class="text-white fw-bold">Gunakan Item Code Baru: </span>
                                    <span id="customItemCodeVal" class="badge bg-warning text-dark font-monospace fs-7 ms-2"></span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-sm" onclick="selectCustomItemCodeFromModal()">
                                        <i class="bi bi-check-circle-fill me-1"></i> Gunakan
                                    </button>
                                </td>
                            </tr>
                            @if(isset($registeredItems) && is_array($registeredItems) && count($registeredItems) > 0)
                                @foreach($registeredItems as $idx => $item)
                                    <tr>
                                        <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                        <td>
                                            <span class="badge bg-dark border border-info text-info px-2.5 py-1.5 font-monospace fs-7">
                                                <i class="bi bi-barcode me-1"></i>{{ $item['item_code'] }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-light">{{ $item['name'] }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info text-dark fw-bold rounded-pill px-3 shadow-sm" onclick="selectItemCodeFromModal('{{ addslashes($item['item_code']) }}', '{{ addslashes($item['name']) }}')">
                                                <i class="bi bi-check-lg me-1"></i> Pilih
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data Item Code yang terdaftar dalam sistem. Ketik kode baru pada pencarian untuk menggunakan.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer px-4 py-3 border-top border-secondary border-opacity-25" style="background: #1e293b !important;">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof window.itemCodeSelectorState === 'undefined') {
    window.itemCodeSelectorState = {
        codeTargetId: null,
        descTargetId: null
    };
}

function openItemCodeSelectorModal(codeTargetId, descTargetId) {
    window.itemCodeSelectorState.codeTargetId = codeTargetId;
    window.itemCodeSelectorState.descTargetId = descTargetId;
    
    const searchInput = document.getElementById('searchItemCodeModalInput');
    if (searchInput) {
        searchInput.value = '';
        filterItemCodeModalTable();
    }

    const modalElem = document.getElementById('modalSelectItemCode');
    if (modalElem) {
        const bsModal = new bootstrap.Modal(modalElem);
        bsModal.show();
        setTimeout(() => {
            if (searchInput) searchInput.focus();
        }, 300);
    }
}

function selectItemCodeFromModal(itemCode, itemName) {
    const codeId = window.itemCodeSelectorState.codeTargetId;
    const descId = window.itemCodeSelectorState.descTargetId;

    if (codeId) {
        const codeElem = document.getElementById(codeId);
        if (codeElem) {
            codeElem.value = itemCode;
            codeElem.dispatchEvent(new Event('change', { bubbles: true }));
            codeElem.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    if (descId) {
        const descElem = document.getElementById(descId);
        if (descElem) {
            descElem.value = itemName;
            descElem.dispatchEvent(new Event('change', { bubbles: true }));
            descElem.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    const modalElem = document.getElementById('modalSelectItemCode');
    if (modalElem) {
        const bsModal = bootstrap.Modal.getInstance(modalElem);
        if (bsModal) bsModal.hide();
    }
}

function filterItemCodeModalTable() {
    const q = (document.getElementById('searchItemCodeModalInput')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#tableModalItemCodes tbody tr:not(#trCustomItemCodeOption)');
    rows.forEach(tr => {
        const text = tr.innerText.toLowerCase();
        if (!q || text.includes(q)) {
            tr.style.display = '';
        } else {
            tr.style.display = 'none';
        }
    });

    const customOpt = document.getElementById('trCustomItemCodeOption');
    const customValSpan = document.getElementById('customItemCodeVal');
    if (customOpt && customValSpan) {
        if (q.length > 0) {
            customValSpan.innerText = q.toUpperCase();
            customOpt.style.display = '';
        } else {
            customOpt.style.display = 'none';
        }
    }
}

function selectCustomItemCodeFromModal() {
    const q = (document.getElementById('searchItemCodeModalInput')?.value || '').trim().toUpperCase();
    if (q) {
        selectItemCodeFromModal(q, '');
    }
}
</script>
