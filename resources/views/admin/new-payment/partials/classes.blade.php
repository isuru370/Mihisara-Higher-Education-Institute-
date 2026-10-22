<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-semibold mb-1"><i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>Enrolled Classes</h5>
                <small class="text-muted">Click a card or press its number to select it</small>
            </div>
            <button type="button" id="select-unpaid-btn" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-check2-all me-1"></i>Select all unpaid <kbd class="ms-1">A</kbd>
            </button>
        </div>
    </div>
    <div class="card-body px-4 pb-4">
        <div id="classes-card-container" class="row g-3">
            <div class="col-12">
                <div class="classes-empty-state">
                    <div class="classes-empty-icon"><i class="bi bi-inbox"></i></div>
                    <div class="fw-semibold text-muted">Search a student to view classes</div>
                    <small class="text-muted">Enrolled classes will appear here</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sticky pay bar --}}
<div id="np-pay-bar" class="np-pay-bar d-none">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="small text-white-50">Selected</div>
                <div class="fs-5 fw-semibold text-white"><span id="np-selected-count">0</span> class(es)
                    &middot; Rs. <span id="np-selected-total">0.00</span></div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="clear-selection-btn" class="btn btn-outline-light btn-lg">Clear</button>
                <button type="button" id="bulk-payment-btn" class="btn btn-success btn-lg px-5 fw-bold np-pay-btn" disabled>
                    <i class="bi bi-cash-coin me-2"></i>PAY NOW <kbd class="np-kbd-light ms-2">F8</kbd>
                </button>
            </div>
        </div>
    </div>
</div>
