<div class="row g-4 mb-4">

    <!-- Total Exams -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon blue">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Total Exams</p>
            </div>
        </div>
    </div>

    <!-- Scheduled -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon green">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <h3>{{ $stats['scheduled'] ?? 0 }}</h3>
                <p>Scheduled</p>
            </div>
        </div>
    </div>

    <!-- Ongoing -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon orange">
                <i class="bi bi-play-circle"></i>
            </div>
            <div>
                <h3>{{ $stats['ongoing'] ?? 0 }}</h3>
                <p>Ongoing</p>
            </div>
        </div>
    </div>

    <!-- Completed -->
    <div class="col-xl-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon purple">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <h3>{{ $stats['completed'] ?? 0 }}</h3>
                <p>Completed</p>
            </div>
        </div>
    </div>

    <!-- Cancelled -->
    <div class="col-xl-4 col-md-6">
        <div class="stats-card">
            <div class="stats-icon red">
                <i class="bi bi-x-circle"></i>
            </div>
            <div>
                <h3>{{ $stats['cancelled'] ?? 0 }}</h3>
                <p>Cancelled</p>
            </div>
        </div>
    </div>

    <!-- Upcoming -->
    <div class="col-xl-4 col-md-6">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div>
                <h3>{{ $stats['upcoming'] ?? 0 }}</h3>
                <p>Upcoming</p>
            </div>
        </div>
    </div>

</div>