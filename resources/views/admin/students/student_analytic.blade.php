@extends('layouts.app')

@section('title', 'Student Analytics')
@section('page-title', 'Student Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
    <li class="breadcrumb-item active">Student Analytics</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div class="mb-3 mb-md-0">
                        <h2 class="h3 mb-1 text-gray-800">Student Analytics Dashboard</h2>
                        <p class="text-muted mb-0">Comprehensive overview of student performance and activities</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshData()" id="refreshBtn">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-download me-2"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                        <i class="fas fa-file-pdf me-2 text-danger"></i>PDF Report
                                    </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">
                                        <i class="fas fa-file-excel me-2 text-success"></i>Excel Report
                                    </a></li>
                            </ul>
                        </div>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Profile Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-3 border-bottom">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                            <h5 class="card-title mb-2 mb-md-0 text-white">
                                <i class="fas fa-user-graduate me-2"></i>Student Profile
                            </h5>
                            <span class="badge bg-white text-primary fs-6 px-3 py-2" id="studentStatusBadge">
                                <i class="fas fa-circle fa-xs me-1"></i>Loading...
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="studentInfo">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 mb-0 text-muted">Loading student information...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Dashboard -->
        <div class="row mb-4" id="statsSection">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-3 border-bottom">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-chart-line me-2"></i>Performance Overview
                            </h5>
                            <div class="mt-2 mt-md-0">
                                <span class="badge bg-white text-primary">All Time Data</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Loading State -->
                        <div class="text-center py-5" id="statsLoading">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading statistics...</span>
                            </div>
                            <p class="text-muted mb-0">Loading performance metrics...</p>
                        </div>

                        <!-- Stats Content -->
                        <div id="statsContent" class="d-none">
                            <!-- Main Stats Row -->
                            <div class="row g-4 mb-4">
                                <!-- Total Enrollments -->
                                <div class="col-xl-3 col-lg-6">
                                    <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h6 class="text-muted mb-1">Total Classes</h6>
                                                    <h2 class="mb-0 text-primary" id="totalEnrollments">0</h2>
                                                </div>
                                                <div class="bg-primary bg-opacity-25 p-3 rounded-circle">
                                                    <i class="fas fa-chalkboard-teacher fa-lg text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Active</small>
                                                    <div class="fw-bold text-success" id="activeEnrollments">0</div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Inactive</small>
                                                    <div class="fw-bold text-secondary" id="inactiveEnrollments">0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial Overview -->
                                <div class="col-xl-3 col-lg-6">
                                    <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h6 class="text-muted mb-1">Financial Summary</h6>
                                                    <h2 class="mb-0 text-success" id="totalFees">Rs. 0</h2>
                                                </div>
                                                <div class="bg-success bg-opacity-25 p-3 rounded-circle">
                                                    <i class="fas fa-money-bill-wave fa-lg text-success"></i>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Paid</small>
                                                    <div class="fw-bold text-success" id="totalPaid">Rs. 0</div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Due</small>
                                                    <div class="fw-bold text-danger" id="totalDue">Rs. 0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Overview -->
                                <div class="col-xl-3 col-lg-6">
                                    <div class="card border-0 shadow-sm h-100 bg-info bg-opacity-10">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h6 class="text-muted mb-1">Attendance Rate</h6>
                                                    <h2 class="mb-0 text-info" id="attendanceRate">0%</h2>
                                                </div>
                                                <div class="bg-info bg-opacity-25 p-3 rounded-circle">
                                                    <i class="fas fa-user-check fa-lg text-info"></i>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Present</small>
                                                    <div class="fw-bold text-success" id="totalPresent">0</div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Absent</small>
                                                    <div class="fw-bold text-warning" id="totalAbsent">0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Class Sessions -->
                                <div class="col-xl-3 col-lg-6">
                                    <div class="card border-0 shadow-sm h-100 bg-warning bg-opacity-10">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h6 class="text-muted mb-1">Class Sessions</h6>
                                                    <h2 class="mb-0 text-warning" id="totalSessions">0</h2>
                                                </div>
                                                <div class="bg-warning bg-opacity-25 p-3 rounded-circle">
                                                    <i class="fas fa-calendar-alt fa-lg text-warning"></i>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Avg/Class</small>
                                                    <div class="fw-bold text-warning" id="avgSessions">0</div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Free Cards</small>
                                                    <div class="fw-bold text-primary" id="freeCards">0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bars Row -->
                            <div class="row g-4">
                                <!-- Payment Progress -->
                                <div class="col-xl-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white py-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><i class="fas fa-credit-card me-2 text-primary"></i>Payment
                                                    Progress</h6>
                                                <span class="badge bg-primary bg-opacity-10 text-primary"
                                                    id="paymentCompletion">0%</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-success" id="paymentProgress" style="width: 0%"
                                                    role="progressbar"></div>
                                            </div>
                                            <div class="mt-2 d-flex justify-content-between">
                                                <small class="text-muted">Paid: <span class="fw-semibold"
                                                        id="paidAmount">Rs. 0</span></small>
                                                <small class="text-muted">Total: <span class="fw-semibold"
                                                        id="totalFeesAmount">Rs. 0</span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Progress -->
                                <div class="col-xl-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white py-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><i class="fas fa-chart-bar me-2 text-info"></i>Attendance
                                                    Progress</h6>
                                                <span class="badge bg-info bg-opacity-10 text-info"
                                                    id="attendanceCompletion">0%</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-info" id="attendanceProgress" style="width: 0%"
                                                    role="progressbar"></div>
                                            </div>
                                            <div class="mt-2 d-flex justify-content-between">
                                                <small class="text-muted">Attended: <span class="fw-semibold"
                                                        id="attendedCount">0</span></small>
                                                <small class="text-muted">Total Sessions: <span class="fw-semibold"
                                                        id="totalSessionsCount">0</span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Class Enrollments Section -->
        <div class="row mb-4" id="enrollmentsSection">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-3 border-bottom">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                            <div class="mb-2 mb-md-0">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>Class Enrollments
                                </h5>
                                <p class="text-white-50 small mb-0 mt-1">Detailed analytics for each enrolled class</p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i
                                            class="fas fa-search text-primary"></i></span>
                                    <input type="text" class="form-control" placeholder="Search classes..." id="classSearch"
                                        style="width: 150px;">
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-light active"
                                        onclick="filterEnrollments('all')">All</button>
                                    <button class="btn btn-sm btn-outline-light"
                                        onclick="filterEnrollments('active')">Active</button>
                                    <button class="btn btn-sm btn-outline-light"
                                        onclick="filterEnrollments('free')">Free</button>
                                </div>
                                <button class="btn btn-sm btn-outline-light" onclick="toggleSplitCategories()"
                                    id="toggleSplitBtn">
                                    <i class="fas fa-layer-group me-1"></i>Show Split
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Loading State -->
                        <div id="enrollmentsLoading" class="p-5">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mb-0">Loading class analytics...</p>
                            </div>
                        </div>

                        <!-- Enrollments Grid -->
                        <div class="row g-4 p-4 d-none" id="enrollmentsList">
                            <!-- Enrollments will be loaded here -->
                        </div>

                        <!-- No Enrollments State -->
                        <div id="noEnrollments" class="text-center py-5 d-none">
                            <div class="mb-4">
                                <i class="fas fa-chalkboard-teacher fa-4x text-muted opacity-25"></i>
                            </div>
                            <h5 class="text-muted fw-semibold mb-2">No Class Enrollments Found</h5>
                            <p class="text-muted mb-4">This student is not enrolled in any classes yet.</p>
                            <a href="{{ url('/students') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Enroll in Classes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Custom Bootstrap enhancements */
        .card {
            border-radius: 10px;
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        /* Custom badge styles */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Progress bar customization */
        .progress {
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 10px;
        }

        /* Split category styling */
        .split-category-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
        }

        .combined-category {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        /* Metric cards styling */
        .metric-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Custom shadows */
        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-header h5 {
                font-size: 1.1rem;
            }

            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }
        }

        /* Animation for loading */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let studentCode = null; // custom_id
        let studentId = null;   // student_id from URL

        // Production Configuration
        const config = {
            apiBaseUrl: '/api',
            debounceTime: 300,
            alertTimeout: 5000,
            refreshInterval: null
        };

        // Get student ID from URL
        const getStudentIdFromUrl = () => {
            const pathArray = window.location.pathname.split('/');
            return pathArray[pathArray.length - 1];
        };

        studentId = getStudentIdFromUrl();

        // State Management
        let currentStudent = null;
        let enrollmentsData = [];
        let currentFilter = 'all';
        let showSplitCategories = false;

        // DOM Elements Cache
        const elements = {
            refreshBtn: null,
            studentInfo: null,
            studentStatusBadge: null,
            statsContent: null,
            statsLoading: null,
            enrollmentsList: null,
            enrollmentsLoading: null,
            noEnrollments: null,
            classSearch: null,
            toggleSplitBtn: null
        };

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function () {
            initializeElements();
            initializeEventListeners();
            loadStudentAnalytics();
        });

        // Initialize DOM elements
        function initializeElements() {
            elements.refreshBtn = document.getElementById('refreshBtn');
            elements.studentInfo = document.getElementById('studentInfo');
            elements.studentStatusBadge = document.getElementById('studentStatusBadge');
            elements.statsContent = document.getElementById('statsContent');
            elements.statsLoading = document.getElementById('statsLoading');
            elements.enrollmentsList = document.getElementById('enrollmentsList');
            elements.enrollmentsLoading = document.getElementById('enrollmentsLoading');
            elements.noEnrollments = document.getElementById('noEnrollments');
            elements.classSearch = document.getElementById('classSearch');
            elements.toggleSplitBtn = document.getElementById('toggleSplitBtn');
        }

        // Initialize event listeners
        function initializeEventListeners() {
            // Search with debounce
            let searchTimeout;
            elements.classSearch.addEventListener('keyup', function (e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterEnrollmentsBySearch(e.target.value.toLowerCase());
                }, config.debounceTime);
            });

            // Network status
            window.addEventListener('online', handleOnlineStatus);
            window.addEventListener('offline', handleOfflineStatus);

            // Initialize Bootstrap components
            initializeBootstrapComponents();
        }

        // Initialize Bootstrap components
        function initializeBootstrapComponents() {
            // Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Popovers if any
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        }

        // Network status handlers
        function handleOnlineStatus() {
            showAlert('Connection restored. Data will be refreshed.', 'success');
            loadStudentAnalytics();
        }

        function handleOfflineStatus() {
            showAlert('You are offline. Some features may not work.', 'warning');
        }

        // API Helper
        async function apiFetch(endpoint, options = {}) {
            const url = `${config.apiBaseUrl}/${endpoint}`;

            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...options.headers
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                return await response.json();
            } catch (error) {
                console.error(`API Error (${endpoint}):`, error);
                throw error;
            }
        }

        // Load Student Analytics
        async function loadStudentAnalytics() {
            try {
                setLoadingState(true);

                // Load student info and analytics in parallel
                await Promise.all([
                    loadStudentInfo(),
                    loadAnalyticsData()
                ]);

                showAlert('Data loaded successfully', 'success');
            } catch (error) {
                console.error('Error loading analytics:', error);
                showAlert('Failed to load analytics data. Please try again.', 'danger');
                showNoEnrollments();
            } finally {
                setLoadingState(false);
            }
        }

        // Set loading state
        function setLoadingState(isLoading) {
            if (elements.refreshBtn) {
                elements.refreshBtn.innerHTML = isLoading ?
                    '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing' :
                    '<i class="fas fa-sync-alt me-2"></i>Refresh';
                elements.refreshBtn.disabled = isLoading;
            }
        }

        // Load Student Information
        async function loadStudentInfo() {
            try {
                const studentData = await apiFetch(`students/${studentId}`);
                currentStudent = studentData.data || studentData;

                // Save custom_id
                studentCode = currentStudent.custom_id;

                displayStudentInfo(currentStudent);
            } catch (error) {
                console.error('Error loading student info:', error);
                displayStudentInfo(null);
            }
        }

        // Load Analytics Data
        async function loadAnalyticsData() {
            try {
                const analyticsData = await apiFetch(`students/analytics/${studentId}`);

                // Handle different response structures
                let processedData = analyticsData;
                if (analyticsData.data) {
                    processedData = analyticsData.data;
                } else if (analyticsData.classes) {
                    processedData = analyticsData.classes;
                }

                // Check if it's an array - our new API returns array directly
                if (Array.isArray(processedData)) {
                    enrollmentsData = processedData;
                    displayStudentEnrollments(enrollmentsData);
                    updateStatistics(enrollmentsData);
                } else {
                    throw new Error('Invalid data format received from server');
                }
            } catch (error) {
                throw error;
            }
        }

        // Display Student Info
        function displayStudentInfo(student) {
            if (!student) {
                elements.studentInfo.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Unable to load student information
                    </div>
                `;
                return;
            }

            const studentName = `${student.fname || ''} ${student.lname || ''}`.trim();
            const grade = student.grade?.grade_name || 'Not Assigned';
            const studentCode = student.custom_id || student.code || 'N/A';

            // Check student status from API response (is_active field)
            const isStudentActive = student.is_active == 1;

            elements.studentInfo.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-user-graduate fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h4 class="mb-1">${studentName}</h4>
                                <div class="d-flex flex-wrap gap-3">
                                    <div>
                                        <small class="text-muted">Student ID</small>
                                        <div class="fw-bold">${studentCode}</div>
                                    </div>
                                    <div>
                                        <small class="text-muted">Grade</small>
                                        <div class="fw-bold">${grade}</div>
                                    </div>
                                    <div>
                                        <small class="text-muted">Status</small>
                                        <div class="fw-bold">
                                            <span class="badge ${isStudentActive ? 'bg-success' : 'bg-secondary'}">
                                                <i class="fas fa-circle fa-xs me-1"></i>
                                                ${isStudentActive ? 'Active Student' : 'Inactive Student'}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="d-flex flex-column flex-sm-row flex-md-column flex-lg-row gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="viewStudentProfile()">
                                <i class="fas fa-eye me-2"></i>View Profile
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="editStudentDetails()">
                                <i class="fas fa-edit me-2"></i>Edit Details
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Update student status badge
            updateStudentStatusBadge(isStudentActive);
        }

        // Update Student Status Badge
        function updateStudentStatusBadge(isStudentActive) {
            if (!elements.studentStatusBadge) return;

            if (isStudentActive) {
                elements.studentStatusBadge.innerHTML = '<i class="fas fa-circle fa-xs me-1 text-success"></i>Active Student';
                elements.studentStatusBadge.className = 'badge bg-success bg-opacity-10 text-success fs-6 px-3 py-2';
            } else {
                elements.studentStatusBadge.innerHTML = '<i class="fas fa-circle fa-xs me-1 text-secondary"></i>Inactive Student';
                elements.studentStatusBadge.className = 'badge bg-secondary bg-opacity-10 text-secondary fs-6 px-3 py-2';
            }
        }

        // Update Statistics based on new API structure
        function updateStatistics(enrollments) {
            if (!Array.isArray(enrollments)) {
                enrollments = [];
            }

            // Calculate statistics
            const totalClasses = enrollments.length;
            const activeClasses = enrollments.filter(e => e.status === true).length;  // status is boolean true/false
            const inactiveClasses = totalClasses - activeClasses;
            
            // Free cards - based on is_free_card field
            const freeCards = enrollments.filter(e => e.is_free_card === 1).length;

            // Financial calculations
            const totalFees = enrollments.reduce((sum, e) => sum + (e.category_info?.fees || 0), 0);
            const totalPaid = enrollments.reduce((sum, e) => sum + (e.payments?.summary?.total_paid || 0), 0);
            const totalDue = Math.max(0, totalFees - totalPaid);

            // Attendance calculations
            const totalSessions = enrollments.reduce((sum, e) => sum + (e.class_attendance?.total_sessions || 0), 0);
            const totalPresent = enrollments.reduce((sum, e) => sum + (e.student_attendance?.present_count || 0), 0);
            const totalAbsent = enrollments.reduce((sum, e) => sum + (e.student_attendance?.absent_count || 0), 0);

            const attendanceRate = totalSessions > 0 ? Math.round((totalPresent / totalSessions) * 100) : 0;
            const avgSessions = totalClasses > 0 ? Math.round(totalSessions / totalClasses) : 0;
            const paymentCompletion = totalFees > 0 ? Math.round((totalPaid / totalFees) * 100) : 100;

            // Hide loading, show content with animation
            if (elements.statsLoading && elements.statsContent) {
                elements.statsLoading.style.display = 'none';
                elements.statsContent.classList.remove('d-none');
                elements.statsContent.classList.add('fade-in');
            }

            // Update main stats
            updateElementText('totalEnrollments', totalClasses);
            updateElementText('activeEnrollments', activeClasses);
            updateElementText('inactiveEnrollments', inactiveClasses);
            updateElementText('freeCards', freeCards);
            updateElementText('totalFees', `Rs. ${totalFees.toLocaleString()}`);
            updateElementText('totalPaid', `Rs. ${totalPaid.toLocaleString()}`);
            updateElementText('totalDue', `Rs. ${totalDue.toLocaleString()}`);
            updateElementText('totalSessions', totalSessions);
            updateElementText('avgSessions', avgSessions);
            updateElementText('totalPresent', totalPresent);
            updateElementText('totalAbsent', totalAbsent);
            updateElementText('attendanceRate', `${attendanceRate}%`);

            // Update progress bars
            updateProgressBar('paymentProgress', paymentCompletion);
            updateProgressBar('attendanceProgress', attendanceRate);
            updateElementText('paymentCompletion', `${paymentCompletion}%`);
            updateElementText('attendanceCompletion', `${attendanceRate}%`);
            updateElementText('paidAmount', `Rs. ${totalPaid.toLocaleString()}`);
            updateElementText('totalFeesAmount', `Rs. ${totalFees.toLocaleString()}`);
            updateElementText('attendedCount', totalPresent);
            updateElementText('totalSessionsCount', totalSessions);
        }

        // Helper function to update element text
        function updateElementText(elementId, text) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = text;
            }
        }

        // Helper function to update progress bar
        function updateProgressBar(elementId, percentage) {
            const element = document.getElementById(elementId);
            if (element) {
                element.style.width = `${percentage}%`;
            }
        }

        // Display Student Enrollments with Exam Results button
        function displayStudentEnrollments(enrollments) {
            if (!elements.enrollmentsLoading || !elements.enrollmentsList || !elements.noEnrollments) return;

            // Hide loading, show list with animation
            elements.enrollmentsLoading.style.display = 'none';
            elements.enrollmentsList.classList.remove('d-none');
            elements.enrollmentsList.classList.add('fade-in');
            elements.enrollmentsList.innerHTML = '';

            if (!Array.isArray(enrollments) || enrollments.length === 0) {
                showNoEnrollments();
                return;
            }

            let enrollmentsHTML = '';

            enrollments.forEach((enrollment) => {
                // Get enrollment_id and classCategoryHasStudentClassId
                const enrollmentId = enrollment.enrollment_id;
                const classCategoryHasStudentClassId = enrollment.category_info?.class_category_has_student_class_id;
                const classInfo = enrollment.class_info || {};
                const categoryInfo = enrollment.category_info || {};
                const payments = enrollment.payments?.summary || {};
                const classAttendance = enrollment.class_attendance || {};
                const studentAttendance = enrollment.student_attendance || {};

                // Class information
                const className = classInfo.class_name || 'No Class Name';
                const teacherName = classInfo.teacher ?
                    `${classInfo.teacher.first_name || ''} ${classInfo.teacher.last_name || ''}`.trim() :
                    'N/A';
                const subjectName = classInfo.subject?.subject_name || 'N/A';
                const gradeName = classInfo.grade?.grade_name || 'N/A';

                // Category information
                const categoryName = categoryInfo.category_name || 'General';
                const fees = categoryInfo.fees || 0;
                const isSplitCategory = categoryInfo.is_split_category || false;

                // Status and badges
                const isActive = enrollment.status === true;  // status is boolean
                const isFreeCard = enrollment.is_free_card === 1;
                const enrollmentDate = formatDate(enrollment.enrollment_date);

                // Payment information
                const paymentCount = payments.payment_count || 0;
                const totalPaid = payments.total_paid || 0;
                const isFullyPaid = payments.is_fully_paid || false;
                const paymentPercentage = fees > 0 ? Math.round((totalPaid / fees) * 100) : 100;

                // Attendance information
                const totalSessions = classAttendance.total_sessions || 0;
                const presentCount = studentAttendance.present_count || 0;
                const absentCount = studentAttendance.absent_count || 0;
                const attendancePercentage = studentAttendance.attendance_rate || 0;

                // Determine if we should show this enrollment based on split category toggle
                if (!showSplitCategories && isSplitCategory) {
                    // Don't show individual split categories when toggle is off
                    return;
                }

                enrollmentsHTML += `
                    <div class="col-xl-6 col-lg-12" 
                         data-enrollment-id="${enrollmentId}"
                         data-class-category-id="${classCategoryHasStudentClassId}"
                         data-status="${isActive ? 'active' : 'inactive'}" 
                         data-type="${isFreeCard ? 'free' : 'paid'}" 
                         data-category-type="${isSplitCategory ? 'split' : 'regular'}">
                        <div class="card border h-100 ${isSplitCategory ? 'combined-category' : ''}">
                            <div class="card-header ${isSplitCategory ? 'text-white' : 'bg-white'}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 fw-bold ${isSplitCategory ? 'text-white' : 'text-dark'}">${className}</h6>
                                        ${isSplitCategory ?
                                            '<small class="opacity-75"><i class="fas fa-layer-group fa-xs me-1"></i>Part of Combined Category</small>' :
                                            ''
                                        }
                                    </div>
                                    <div class="d-flex gap-2">
                                        ${isSplitCategory ?
                                            '<span class="split-category-badge"><i class="fas fa-link fa-xs me-1"></i>Split</span>' :
                                            ''
                                        }
                                        <span class="badge ${isActive ? 'bg-success' : 'bg-secondary'}">
                                            ${isActive ? 'Active' : 'Inactive'}
                                        </span>
                                        ${isFreeCard ?
                                            '<span class="badge bg-warning text-dark"><i class="fas fa-crown me-1"></i>Free Card</span>' :
                                            ''
                                        }
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Class Info -->
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Teacher</small>
                                        <div class="fw-bold">${teacherName}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Category</small>
                                        <div class="fw-bold ${isSplitCategory ? 'text-primary' : ''}">${categoryName}</div>
                                        ${isSplitCategory ?
                                            '<small class="text-muted">(Individual Category)</small>' :
                                            ''
                                        }
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Subject</small>
                                        <span class="badge bg-info">${subjectName}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Grade</small>
                                        <span class="badge bg-primary">${gradeName}</span>
                                    </div>
                                </div>

                                <!-- Financial Status -->
                                <div class="mb-4">
                                    <small class="text-muted d-block mb-2">Financial Status</small>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Rs. ${totalPaid.toLocaleString()}</span>
                                        <span class="text-muted">/ Rs. ${fees.toLocaleString()}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: ${paymentPercentage}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">${paymentPercentage}% Paid</small>
                                        <small class="text-muted">${paymentCount} payment${paymentCount !== 1 ? 's' : ''}</small>
                                    </div>
                                </div>

                                <!-- Attendance Status -->
                                <div class="mb-4">
                                    <small class="text-muted d-block mb-2">Attendance Status</small>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>${presentCount} present</span>
                                        <span class="text-muted">/ ${totalSessions} session${totalSessions !== 1 ? 's' : ''}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: ${attendancePercentage}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">${attendancePercentage}% Attendance</small>
                                        <small class="text-muted">${absentCount} absent</small>
                                    </div>
                                </div>

                                <!-- Exam Results Button -->
                                <div class="d-grid">
                                    <button class="btn btn-primary" onclick="goToExamResults(${classCategoryHasStudentClassId}, ${studentId})">
                                        <i class="fas fa-chart-bar me-2"></i>View Exam Results
                                    </button>
                                </div>
                            </div>
                            <div class="card-footer ${isSplitCategory ? 'bg-transparent text-white-50 border-top-0' : 'bg-white text-muted'}">
                                <small><i class="fas fa-calendar me-1"></i>Enrolled on: ${enrollmentDate}</small>
                            </div>
                        </div>
                    </div>
                `;
            });

            elements.enrollmentsList.innerHTML = enrollmentsHTML;

            // Apply current filters
            filterEnrollmentsBySearch(elements.classSearch.value);
        }

        // Navigate to Exam Results page
        function goToExamResults(classCategoryHasStudentClassId, studentId) {
            if (classCategoryHasStudentClassId && studentId) {
                window.location.href = `/students/${classCategoryHasStudentClassId}/${studentId}/exam-results`;
            } else {
                showAlert('Cannot navigate to exam results: Missing parameters', 'danger');
            }
        }

        // Toggle Split Categories
        function toggleSplitCategories() {
            showSplitCategories = !showSplitCategories;

            const button = elements.toggleSplitBtn;
            if (button) {
                button.innerHTML = showSplitCategories ?
                    '<i class="fas fa-eye-slash me-1"></i>Hide Split' :
                    '<i class="fas fa-layer-group me-1"></i>Show Split';
                button.classList.toggle('active', showSplitCategories);
            }

            displayStudentEnrollments(enrollmentsData);
        }

        // Filter Enrollments
        function filterEnrollments(filterType) {
            currentFilter = filterType;

            // Update button states
            const buttons = document.querySelectorAll('#enrollmentsSection .btn-group .btn');
            buttons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Find which button was clicked and set it active
            const activeBtn = Array.from(buttons).find(btn => btn.textContent.toLowerCase().includes(filterType));
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            filterEnrollmentsBySearch(elements.classSearch.value);
        }

        // Filter by search term
        function filterEnrollmentsBySearch(searchTerm) {
            const enrollmentCards = document.querySelectorAll('#enrollmentsList .col-xl-6');
            let visibleCount = 0;

            enrollmentCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                const status = card.getAttribute('data-status');
                const type = card.getAttribute('data-type');

                const matchesSearch = searchTerm === '' || cardText.includes(searchTerm);
                const matchesStatus = currentFilter === 'all' ||
                    (currentFilter === 'active' && status === 'active') ||
                    (currentFilter === 'free' && type === 'free');

                if (matchesSearch && matchesStatus) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount === 0 && elements.noEnrollments && elements.enrollmentsList) {
                elements.noEnrollments.classList.remove('d-none');
                elements.enrollmentsList.classList.add('d-none');
            } else if (elements.noEnrollments && elements.enrollmentsList) {
                elements.noEnrollments.classList.add('d-none');
                elements.enrollmentsList.classList.remove('d-none');
            }
        }

        // Show No Enrollments
        function showNoEnrollments() {
            if (elements.enrollmentsLoading) {
                elements.enrollmentsLoading.style.display = 'none';
            }
            if (elements.enrollmentsList) {
                elements.enrollmentsList.classList.add('d-none');
            }
            if (elements.noEnrollments) {
                elements.noEnrollments.classList.remove('d-none');
            }
        }

        // Refresh Data
        function refreshData() {
            loadStudentAnalytics();
        }

        // Export Report
        function exportReport(format) {
            showAlert(`Exporting report as ${format.toUpperCase()}...`, 'info');
            // Implement export functionality here
        }

        // View Student Profile
        function viewStudentProfile() {
            if (currentStudent && studentCode) {
                window.location.href = `/students/${studentCode}`;
            }
        }

        // Edit Student Details
        function editStudentDetails() {
            if (currentStudent && studentCode) {
                window.location.href = `/students/${studentCode}/edit`;
            }
        }

        // Format Date
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (error) {
                return 'Invalid Date';
            }
        }

        // Show Alert (Production-ready)
        function showAlert(message, type = 'info') {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert-toast');
            existingAlerts.forEach(alert => alert.remove());

            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-toast position-fixed top-0 end-0 m-3 shadow`;
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            alertDiv.style.maxWidth = '400px';
            alertDiv.setAttribute('role', 'alert');

            const icon = type === 'success' ? 'check-circle' :
                type === 'danger' ? 'exclamation-circle' :
                    type === 'warning' ? 'exclamation-triangle' : 'info-circle';

            alertDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${icon} me-2"></i>
                    <span class="flex-grow-1">${message}</span>
                    <button type="button" class="btn-close ms-2" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;

            document.body.appendChild(alertDiv);

            // Auto-remove after timeout
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, config.alertTimeout);
        }

        // Error handling for unhandled promises
        window.addEventListener('unhandledrejection', function (event) {
            console.error('Unhandled promise rejection:', event.reason);
            showAlert('An unexpected error occurred. Please refresh the page.', 'danger');
        });
    </script>
@endpush