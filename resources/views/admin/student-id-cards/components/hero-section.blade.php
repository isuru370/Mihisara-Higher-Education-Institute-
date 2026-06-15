<div class="hero-card mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
        <div>
            <div class="hero-badge mb-3">
                <i class="fas fa-id-card"></i> Student ID Management
            </div>
            <h2 class="fw-bold mb-2">Generate Student ID Cards</h2>
            <p class="mb-0 text-white-50">Preview, manage and bulk download student ID cards (CR80: 3.375" × 2.125")</p>
        </div>
        <div class="text-end">
            <div class="hero-badge mb-2">
                <i class="fas fa-users"></i> {{ $totalStudents }} Students
            </div>
            <div class="hero-badge">
                <i class="fas fa-calendar"></i> {{ now()->format('d M Y') }}
            </div>
        </div>
    </div>
</div>