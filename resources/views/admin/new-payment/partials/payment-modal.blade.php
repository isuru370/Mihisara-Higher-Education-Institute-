<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold"><i class="bi bi-credit-card-2-front-fill me-2 text-primary"></i>Make
                        Payment</h5><small class="text-muted">Enter = Confirm Payment</small>
                </div><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="payment-form"><input type="hidden" id="payment-enrollment-id" name="enrollment_id"><input
                        type="hidden" id="payment-code" name="code"><input type="hidden" id="payment-mark-method"
                        name="mark_method">
                    <div class="alert alert-light border mb-4">
                        <div class="row g-2">
                            <div class="col-md-6"><small class="text-muted d-block">Class</small>
                                <div id="modal-class-name" class="fw-semibold">-</div>
                            </div>
                            <div class="col-md-6"><small class="text-muted d-block">Category</small>
                                <div id="modal-category-name" class="fw-semibold">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><label for="payment-month" class="form-label fw-semibold">Payment
                                Month</label><input type="month" id="payment-month" name="payment_month"
                                class="form-control" required></div>
                        <div class="col-md-6"><label for="payment-method" class="form-label fw-semibold">Payment
                                Method</label><select id="payment-method" class="form-select">
                                <option value="cash">💵 Cash</option>
                                <option value="card">💳 Card</option>
                                <option value="bank_transfer">🏦 Bank Transfer</option>
                                <option value="online">🌐 Online</option>
                                <option value="cheque">📄 Cheque</option>
                                <option value="other">📌 Other</option>
                            </select></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Class Fee</label><input
                                type="number" id="payment-class-fee" class="form-control bg-light" readonly></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Hall Fee</label><input
                                type="number" id="payment-hall-fee" class="form-control bg-light" readonly></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Total Fee</label><input
                                type="number" id="payment-final-fee" class="form-control bg-light fw-bold" readonly>
                        </div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Discount</label><input
                                type="number" id="payment-discount" class="form-control" value="0" min="0"
                                step="0.01"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Amount</label><input type="number"
                                id="payment-amount" class="form-control" min="0" step="0.01" required></div>
                        <div class="col-12"><label class="form-label fw-semibold">Note</label>
                            <textarea id="payment-note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0"><button type="button" class="btn btn-light border"
                    data-bs-dismiss="modal">Cancel</button><button type="button" id="confirm-payment-btn"
                    class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Confirm Payment</button></div>
        </div>
    </div>
</div>
