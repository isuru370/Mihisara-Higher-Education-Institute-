<div class="premium-card search-card mb-4">
    <form method="GET" action="{{ url()->current() }}" class="row g-3 align-items-end">
        <div class="col-lg-8">
            <label for="search" class="form-label fw-semibold">Search Students</label>
            <input type="text" class="form-control custom-input" id="search" name="search"
                value="{{ request('search') }}" placeholder="Search by student ID or name">
        </div>
        <div class="col-lg-4">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary custom-btn flex-fill">
                    <i class="fas fa-search me-1"></i> Search
                </button>
                @if(request('search'))
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary custom-btn">Clear</a>
                @endif
            </div>
        </div>
    </form>
</div>