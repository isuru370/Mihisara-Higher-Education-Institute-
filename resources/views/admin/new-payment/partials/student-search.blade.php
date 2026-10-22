<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-8">
                <form id="manual-search-form" class="h-100 d-flex flex-column justify-content-center" autocomplete="off">
                    <label for="manual-code" class="form-label fw-semibold fs-5 mb-2">
                        <i class="bi bi-person-vcard me-1 text-primary"></i>Student ID / QR Code
                        <kbd class="ms-1 small">F2</kbd>
                    </label>
                    <div class="input-group input-group-lg np-search-group">
                        <input type="text" id="manual-code" class="form-control np-search-input"
                            placeholder="Type Student ID and press Enter" autocomplete="off" autofocus>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold" id="manual-search-btn">
                            <i class="bi bi-search me-1"></i>Search <kbd class="np-kbd-light ms-1">Enter</kbd>
                        </button>
                    </div>
                    <div class="form-text mt-2">A scanned QR code is searched automatically.</div>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100 text-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold"><i class="bi bi-qr-code-scan me-1 text-primary"></i>QR Scanner</span>
                        <span class="badge bg-primary">QR</span>
                    </div>
                    <div id="qr-reader" class="mx-auto"></div>
                    <div id="qr-reader-status" class="small text-muted mt-2">Starting camera...</div>
                </div>
            </div>
        </div>
    </div>
</div>
