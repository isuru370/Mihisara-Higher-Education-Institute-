<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h4 class="modal-title fw-bold"><i class="bi bi-credit-card-2-front-fill me-2 text-primary"></i>Confirm Payment</h4>
                    <small class="text-muted"><kbd>Enter</kbd> confirm &middot; <kbd>Esc</kbd> cancel</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="payment-form" autocomplete="off">
                    <input type="hidden" id="payment-enrollment-id" name="enrollment_id">
                    <input type="hidden" id="payment-code" name="code">
                    <input type="hidden" id="payment-mark-method" name="mark_method">
                    <input type="hidden" id="payment-method" value="cash">

                    <div class="np-modal-total mb-4">
                        <div>
                            <div class="small text-muted" id="modal-class-label">Class</div>
                            <div class="fw-bold fs-5" id="modal-class-name">-</div>
                            <div class="small text-muted" id="modal-category-name">-</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">Amount to pay</div>
                            <div class="np-modal-amount">Rs. <span id="modal-amount-display">0.00</span></div>
                        </div>
                    </div>

                    <label class="form-label fw-semibold">Payment method <small class="text-muted">(Alt+1&hellip;6)</small></label>
                    <div class="np-method-grid mb-4" id="payment-method-buttons">
                        <button type="button" class="btn btn-outline-primary np-method active" data-method="cash"><span class="np-method-icon">💵</span>Cash<kbd>Alt 1</kbd></button>
                        <button type="button" class="btn btn-outline-primary np-method" data-method="card"><span class="np-method-icon">💳</span>Card<kbd>Alt 2</kbd></button>
                        <button type="button" class="btn btn-outline-primary np-method" data-method="bank_transfer"><span class="np-method-icon">🏦</span>Bank<kbd>Alt 3</kbd></button>
                        <button type="button" class="btn btn-outline-primary np-method" data-method="online"><span class="np-method-icon">🌐</span>Online<kbd>Alt 4</kbd></button>
                        <button type="button" class="btn btn-outline-primary np-method" data-method="cheque"><span class="np-method-icon">📄</span>Cheque<kbd>Alt 5</kbd></button>
                        <button type="button" class="btn btn-outline-primary np-method" data-method="other"><span class="np-method-icon">📌</span>Other<kbd>Alt 6</kbd></button>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="payment-month" class="form-label fw-semibold">Payment month <small class="text-muted">(Alt+M)</small></label>
                            <input type="month" id="payment-month" name="payment_month" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Class fee</label>
                            <input type="text" id="payment-final-fee" class="form-control form-control-lg bg-light fw-bold" readonly>
                        </div>
                        <div class="col-md-4" id="payment-discount-group">
                            <label for="payment-discount" class="form-label fw-semibold">Discount</label>
                            <input type="number" id="payment-discount" class="form-control form-control-lg" value="0" min="0" step="0.01">
                        </div>
                        <input type="hidden" id="payment-amount">
                        <div class="col-12">
                            <label for="payment-note" class="form-label fw-semibold">Note <small class="text-muted">(optional)</small></label>
                            <textarea id="payment-note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div id="payment-modal-error" class="alert alert-danger mt-3 mb-0 d-none"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border btn-lg" data-bs-dismiss="modal">Cancel <kbd class="ms-1">Esc</kbd></button>
                <button type="button" id="confirm-payment-btn" class="btn btn-success btn-lg px-5 fw-bold">
                    <i class="bi bi-check-circle me-1"></i>CONFIRM &amp; PRINT <kbd class="np-kbd-light ms-1">Enter</kbd>
                </button>
            </div>
        </div>
    </div>
</div>
