<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-semibold mb-1"><i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>Enrolled Classes
                </h5><small class="text-muted">Select classes to make payment</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="form-check mb-0"><input type="checkbox" id="select-all" class="form-check-input"><label
                        for="select-all" class="form-check-label small text-muted">Select All</label></div><button
                    type="button" id="bulk-payment-btn" class="btn btn-primary btn-sm" disabled>Pay Selected</button>
            </div>
        </div>
    </div>
    <div class="card-body px-4 pb-4">
        <div id="classes-card-container" class="row g-3">
            <div class="col-12">
                <div class="classes-empty-state">
                    <div class="classes-empty-icon"><i class="bi bi-inbox"></i></div>
                    <div class="fw-semibold text-muted">Search a student to view classes</div><small
                        class="text-muted">Enrolled classes will appear here</small>
                </div>
            </div>
        </div>
    </div>
</div>
