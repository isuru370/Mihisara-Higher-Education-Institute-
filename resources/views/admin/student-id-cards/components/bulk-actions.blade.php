@php
    $user = auth()->user();
    $showBulkActions = ($user && $user->email === 'admin@nexorait.lk');
@endphp

@if($showBulkActions)
    <div class="bulk-actions mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-id-card me-2"></i>
                    <span id="selectedCount">0</span> students selected
                </h5>
                <small class="text-muted" id="selectedInfo"></small>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <button type="button" class="btn btn-outline-primary me-2" id="selectAllBtn">
                    <i class="fas fa-check-double me-1"></i> Select All Pending
                </button>
                <button type="button" class="btn btn-outline-secondary me-2" id="deselectAllBtn">
                    <i class="fas fa-times me-1"></i> Deselect All
                </button>
                <button type="button" class="btn btn-success" id="bulkDownloadBtn">
                    <i class="fas fa-download me-1"></i>
                    Download Selected (<span id="downloadCount">0</span>)
                </button>
            </div>
        </div>
    </div>
@else
    {{-- Optional: Show a message for other users --}}
    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
        <i class="fas fa-info-circle me-2"></i>
        Bulk download actions are available for administrators only.
    </div>
@endif