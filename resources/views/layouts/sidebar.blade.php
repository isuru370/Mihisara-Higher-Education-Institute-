<aside class="sidebar" id="sidebar">

    <!-- SIDEBAR HEADER -->
    <div class="sidebar-header">

        <button type="button" class="brand text-decoration-none" id="brandButton"
            style="background: none; border: none; width: 100%; cursor: pointer; padding: 0;">

            <div class="brand-icon">
                N
            </div>

            <div class="brand-text">

                <h4>Nexora</h4>

                <small>
                    Education System
                </small>

            </div>

        </button>

    </div>

    <!-- SIDEBAR BODY -->
    <div class="sidebar-body">

        <!-- MAIN SECTION -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">MAIN MENU</div>

            @if(hasPermission('dashboard'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        data-route="admin.dashboard" data-href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </button>
                </div>
            @endif
            @if(hasPermission('weekly-timetable'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.weekly-timetable') ? 'active' : '' }}"
                        data-route="weekly-timetable" data-href="{{ route('admin.weekly-timetable') }}">
                        <i class="bi bi-calendar3"></i>
                        <span>Timetable</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- MANAGEMENT -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">MANAGEMENT</div>

            @if(hasPermission('system-users.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.system-users.*') ? 'active' : '' }}"
                        data-route="admin.system-users.index" data-href="{{ route('admin.system-users.index') }}">
                        <i class="bi bi-people-fill"></i>
                        <span>System User</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('students.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.students.*') ? 'active' : '' }}"
                        data-route="admin.students.index" data-href="{{ route('admin.students.index') }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Students</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('teachers.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"
                        data-route="admin.teachers.index" data-href="{{ route('admin.teachers.index') }}">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>Teachers</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('organizers.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.organizers.*') ? 'active' : '' }}"
                        data-route="admin.organizers.index" data-href="{{ route('admin.organizers.index') }}">
                        <i class="bi bi-calendar-check-fill"></i>
                        <span>Organizers</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">STUDENT SERVICES</div>

            @if(hasPermission('student-images.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.student-images.*') ? 'active' : '' }}"
                        data-route="admin.student-images.index" data-href="{{ route('admin.student-images.index') }}">
                        <i class="bi bi-images"></i>
                        <span>Student Images</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('student-id-cards.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.student-id-cards.index') ? 'active' : '' }}"
                        data-route="admin.student-id-cards.index" data-href="{{ route('admin.student-id-cards.index') }}">
                        <i class="bi bi-card-heading"></i>
                        <span>Student ID Cards</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('admin.temporary-id-cards.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.temporary-id-cards.*') ? 'active' : '' }}"
                        data-route="admin.temporary-id-cards.index"
                        data-href="{{ route('admin.temporary-id-cards.index') }}">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>Temporary ID</span>
                    </button>
                </div>
            @endif
        </div>


        <!-- ACADEMIC -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">ACADEMIC</div>

            @if(hasPermission('student-classes.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.student-classes.*') ? 'active' : '' }}"
                        data-route="admin.student-classes.index" data-href="{{ route('admin.student-classes.index') }}">
                        <i class="bi bi-book-fill"></i>
                        <span>Classes</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('class-schedules.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.class-schedules.*') ? 'active' : '' }}"
                        data-route="admin.class-schedules.index" data-href="{{ route('admin.class-schedules.index') }}">
                        <i class="bi bi-calendar-event-fill"></i>
                        <span>Class Schedule</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('student-class-enrollments.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.student-class-enrollments.*') ? 'active' : '' }}"
                        data-route="admin.student-class-enrollments.index"
                        data-href="{{ route('admin.student-class-enrollments.index') }}">
                        <i class="bi bi-pencil-square"></i>
                        <span>Enrollments</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('attendance.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.class-schedules.todayClasses') ? 'active' : '' }}"
                        data-route="admin.class-schedules.todayClasses"
                        data-href="{{ route('admin.class-schedules.todayClasses') }}">
                        <i class="bi bi-calendar2-check-fill"></i>
                        <span>Attendance</span>
                    </button>
                </div>
            @endif


        </div>

        <!-- STUDENT FINANCE -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">STUDENT FINANCE</div>

            @if(hasPermission('payments.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.admissions.*') ? 'active' : '' }}"
                        data-route="admin.admissions.index" data-href="{{ route('admin.admissions.index') }}">
                        <i class="bi bi-credit-card-fill"></i>
                        <span>Admissions</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('payments.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.admission-payments.*') ? 'active' : '' }}"
                        data-route="admin.admission-payments.index"
                        data-href="{{ route('admin.admission-payments.index') }}">
                        <i class="bi bi-credit-card-fill"></i>
                        <span>Admissions Payments</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('payments.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"
                        data-route="admin.payments.index" data-href="{{ route('admin.payments.index') }}">
                        <i class="bi bi-credit-card-fill"></i>
                        <span>Payments</span>
                    </button>
                </div>
            @endif

        </div>

        <!-- INSTITUTE FINANCE -->
        <div class="sidebar-section">
            <div class="sidebar-section-title"> INSTITUTE FINANCE</div>

            @if(hasPermission('teacher-salaries.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.teacher-salaries.*') ? 'active' : '' }}"
                        data-route="admin.teacher-salaries.index" data-href="{{ route('admin.teacher-salaries.index') }}">
                        <i class="bi bi-cash-stack"></i>
                        <span>Teacher Salaries</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('organizer-payments.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.organizer-payments.index') ? 'active' : '' }}"
                        data-route="admin.organizer-payments.index"
                        data-href="{{ route('admin.organizer-payments.index') }}">
                        <i class="bi bi-wallet2"></i>
                        <span>Organizer Payments</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('extra-incomes.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.extra-incomes.*') ? 'active' : '' }}"
                        data-route="admin.extra-incomes.index" data-href="{{ route('admin.extra-incomes.index') }}">
                        <i class="bi bi-cash-coin"></i>
                        <span>Extra Incomes</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('institute-expenses.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.institute-expenses.*') ? 'active' : '' }}"
                        data-route="admin.institute-expenses.index"
                        data-href="{{ route('admin.institute-expenses.index') }}">
                        <i class="bi bi-cash-coin"></i>
                        <span>Institute Expenses</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('institute-income.monthly-report'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.institute-income.monthly-report') ? 'active' : '' }}"
                        data-route="admin.institute-income.monthly-report"
                        data-href="{{ route('admin.institute-income.monthly-report') }}">
                        <i class="bi bi-bar-chart-line-fill"></i>
                        <span>Income Reports</span>
                    </button>
                </div>
            @endif


        </div>

        <!-- REPORTS -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">RECEIPTS</div>

            @if(hasPermission('receipts.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.receipts.*') ? 'active' : '' }}"
                        data-route="admin.receipts.index" data-href="{{ route('admin.receipts.index') }}">
                        <i class="bi bi-file-earmark-person"></i>
                        <span>Receipts</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- REPORTS -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">REPORTS</div>

            @if(hasPermission('daily-report.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.daily-report.*') ? 'active' : '' }}"
                        data-route="admin.daily-report.index" data-href="{{ route('admin.daily-report.index') }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Daily Report</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('monthly-report.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.monthly-report.*') ? 'active' : '' }}"
                        data-route="admin.monthly-report.index" data-href="{{ route('admin.monthly-report.index') }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Monthly Report</span>
                    </button>
                </div>
            @endif


            @if(hasPermission('teacher-report.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.teacher-report.*') ? 'active' : '' }}"
                        data-route="admin.teacher-report.index" data-href="{{ route('admin.teacher-report.index') }}">
                        <i class="bi bi-file-earmark-person"></i>
                        <span>Teacher Daily Report</span>
                    </button>
                </div>
            @endif

            @if(hasPermission('institute-reports.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.institute-reports.*') ? 'active' : '' }}"
                        data-route="admin.institute-reports.index" data-href="{{ route('admin.institute-reports.index') }}">
                        <i class="bi bi-file-earmark-person"></i>
                        <span>Institute Payment Report</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-title">RECENT ACTIVITIES</div>

            @if(hasPermission('activity-logs.index'))
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}"
                        data-route="admin.activity-logs.index" data-href="{{ route('admin.activity-logs.index') }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Activities</span>
                    </button>
                </div>
            @endif
            @if(
                    hasPermission('logs.laravel.index') &&
                    auth()->check() &&
                    auth()->user()->email === 'admin@nexorait.lk'
                )
                <div class="nav-item">
                    <button type="button"
                        class="nav-link-custom {{ request()->routeIs('admin.logs.laravel.*') ? 'active' : '' }}"
                        data-route="admin.logs.laravel.index" data-href="{{ route('admin.logs.laravel.index') }}">

                        <i class="bi bi-file-earmark-text"></i>

                        <span>Laravel Logs</span>

                    </button>
                </div>
            @endif
        </div>

    </div>

</aside>

<style>
    /* SIDEBAR */

    .sidebar {

        width: 280px;

        background:
            linear-gradient(180deg,
                #0f172a 0%,
                #111827 50%,
                #1e293b 100%);

        position: fixed;

        top: 0;
        left: 0;
        bottom: 0;

        z-index: 1030;

        display: flex;
        flex-direction: column;

        border-right:
            1px solid rgba(255, 255, 255, 0.05);

        box-shadow:
            6px 0 25px rgba(0, 0, 0, 0.25);
    }

    /* HEADER */

    .sidebar-header {

        padding: 1.5rem 1.25rem;

        border-bottom:
            1px solid rgba(255, 255, 255, 0.08);

        flex-shrink: 0;
    }

    /* BODY */

    .sidebar-body {

        flex: 1;

        overflow-y: auto;

        padding: 1rem;

        scrollbar-width: thin;
    }

    /* SCROLLBAR */

    .sidebar-body::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-body::-webkit-scrollbar-thumb {

        background:
            rgba(255, 255, 255, 0.15);

        border-radius: 20px;
    }

    /* BRAND */

    .brand {

        display: flex;
        align-items: center;
        gap: 14px;
    }

    .brand-icon {

        width: 48px;
        height: 48px;

        border-radius: 14px;

        background:
            linear-gradient(135deg,
                #3b82f6,
                #2563eb);

        display: flex;
        align-items: center;
        justify-content: center;

        color: white;

        font-size: 1.4rem;
        font-weight: 800;

        box-shadow:
            0 10px 20px rgba(37, 99, 235, 0.35);
    }

    .brand-text h4 {

        margin: 0;

        color: white;

        font-size: 1.2rem;
        font-weight: 700;
    }

    .brand-text small {

        color: #94a3b8;

        font-size: 0.75rem;
    }

    /* SECTIONS */

    .sidebar-section {
        margin-bottom: 1.8rem;
    }

    .sidebar-section-title {

        color: #64748b;

        font-size: 0.72rem;

        font-weight: 700;

        letter-spacing: 1px;

        margin-bottom: 0.8rem;

        padding-left: 0.8rem;
    }

    /* NAV ITEMS */

    .nav-item {
        margin-bottom: 0.35rem;
    }

    .nav-link-custom {

        display: flex;
        align-items: center;
        gap: 14px;

        padding: 0.9rem 1rem;

        border-radius: 14px;

        color: #cbd5e1;

        text-decoration: none;

        font-size: 0.95rem;
        font-weight: 500;

        transition: all 0.25s ease;

        /* Button reset styles - keeps original look */
        background: none;
        border: none;
        width: 100%;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
    }

    .nav-link-custom i {

        font-size: 1.15rem;

        width: 22px;
    }

    .nav-link-custom:hover {

        background:
            rgba(255, 255, 255, 0.08);

        color: white;

        transform: translateX(5px);
    }

    .nav-link-custom.active {

        background:
            linear-gradient(135deg,
                #2563eb,
                #1d4ed8);

        color: white;

        box-shadow:
            0 10px 18px rgba(37, 99, 235, 0.30);
    }

    /* MOBILE */

    @media(max-width: 991px) {

        .sidebar {
            transform: translateX(-100%);
            transition: 0.3s ease;
        }

        .sidebar.show {
            transform: translateX(0);
        }
    }
</style>

<script>
    (function () {
        // Convert all sidebar navigation buttons to actual navigation handlers
        const navButtons = document.querySelectorAll('#sidebar .nav-link-custom');
        const brandButton = document.getElementById('brandButton');

        // Function to handle navigation
        function navigateTo(url, routeName, buttonElement) {
            // Remove active class from all buttons
            navButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            if (buttonElement) {
                buttonElement.classList.add('active');
            }

            // Actual navigation - redirect to the route URL
            if (url && url !== '#') {
                window.location.href = url;
            }
        }

        // Add click handlers to all nav buttons
        navButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('data-href');
                const routeName = this.getAttribute('data-route');
                navigateTo(url, routeName, this);
            });
        });

        // Handle brand button click (navigate to dashboard)
        if (brandButton) {
            brandButton.addEventListener('click', function (e) {
                e.preventDefault();
                const dashboardUrl = "{{ route('admin.dashboard') }}";

                // Remove active from all nav buttons
                navButtons.forEach(btn => {
                    btn.classList.remove('active');
                });

                // Find and activate dashboard button if exists
                const dashboardBtn = Array.from(navButtons).find(btn =>
                    btn.getAttribute('data-route') === 'admin.dashboard'
                );
                if (dashboardBtn) {
                    dashboardBtn.classList.add('active');
                }

                // Navigate to dashboard
                if (dashboardUrl && dashboardUrl !== '#') {
                    window.location.href = dashboardUrl;
                }
            });
        }
    })();
</script>