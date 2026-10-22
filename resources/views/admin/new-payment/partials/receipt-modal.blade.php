<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i>Payment successful</h5>
                    <small class="text-muted">The receipt was sent to the printer</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="receipt-preview"></div>
            </div>
            <div class="modal-footer border-0 d-grid gap-2">
                <button type="button" id="receipt-next-btn" class="btn btn-success btn-lg fw-bold">
                    <i class="bi bi-person-plus me-1"></i>Next student <kbd class="np-kbd-light ms-1">Enter</kbd>
                </button>
                <button type="button" id="print-receipt-btn" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-printer me-1"></i>Print again <kbd class="ms-1">F9</kbd>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Small notifications instead of alert() --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1090">
    <div id="np-toast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="np-toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
