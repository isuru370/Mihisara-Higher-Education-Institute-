<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <h6 class="fw-semibold mb-1"><i class="bi bi-qr-code-scan me-1 text-primary"></i>QR Scanner
                            </h6><small class="text-muted">Scan student QR code</small>
                        </div><span class="badge bg-primary">QR</span>
                    </div>
                    <div class="text-center">
                        <div id="qr-reader" class="mx-auto"></div>
                        <div id="qr-reader-status" class="small text-muted mt-2">Camera ready</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <h6 class="fw-semibold mb-1"><i class="bi bi-keyboard me-1"></i>Manual Entry</h6><small
                                class="text-muted">F2 to focus</small>
                        </div><span class="badge bg-secondary">F2</span>
                    </div>
                    <form id="manual-search-form"><label class="form-label fw-semibold">Student ID / QR Code</label>
                        <div class="input-group"><input type="text" id="manual-code" class="form-control"
                                placeholder="Enter Student ID" autocomplete="off"><button type="submit"
                                class="btn btn-primary">Search</button></div>
                        <div class="form-text">Press Enter to search</div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
