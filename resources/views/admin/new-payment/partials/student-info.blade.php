<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <img id="student-image" src="{{ asset('images/default-student.png') }}" class="rounded-circle border"
                    width="90" height="90" style="object-fit:cover" alt="Student">
            </div>
            <div class="col">
                <h4 id="student-name" class="fw-bold mb-1">-</h4>
                <div class="text-muted mb-2 fs-6">Student ID: <strong id="student-custom-id">-</strong></div>
                <div class="small"><span class="me-3"><i class="bi bi-telephone me-1"></i><span id="student-mobile">-</span></span>
                    <span>Guardian: <span id="student-guardian">-</span></span></div>
            </div>
            <div class="col-md-auto">
                <div class="d-flex flex-wrap gap-2">
                    <div class="np-stat np-stat-danger">
                        <div class="np-stat-label">Unpaid classes</div>
                        <div class="np-stat-value" id="student-unpaid-count">0</div>
                    </div>
                    <div class="np-stat np-stat-primary">
                        <div class="np-stat-label">Due this month</div>
                        <div class="np-stat-value">Rs. <span id="student-due-total">0.00</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-auto">
                <button type="button" id="next-student-btn" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-arrow-repeat me-1"></i>Next student <kbd class="ms-1">Esc</kbd>
                </button>
            </div>
        </div>
    </div>
</div>
