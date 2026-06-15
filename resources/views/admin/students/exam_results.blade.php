@extends('layouts.app')

@section('title', 'Exam Results')
@section('page-title', 'Student Exam Results')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
    <li class="breadcrumb-item"><a href="{{ url()->previous() }}">Student Analytics</a></li>
    <li class="breadcrumb-item active">Exam Results</li>
@endsection

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header Card -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="fas fa-chart-line text-primary" style="font-size: 1.2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-semibold" id="studentName">Loading...</h5>
                                    <small class="text-muted" id="className">Please wait</small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>Print
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="exportChart()">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Card -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold">
                                <i class="fas fa-chart-bar me-2 text-primary"></i>
                                Performance Trend
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="changeChartType('line')">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                <button class="btn btn-outline-secondary active" onclick="changeChartType('bar')">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <!-- Loading State -->
                        <div id="chartLoading" class="text-center py-4">
                            <div class="spinner-border text-primary mb-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mb-0">Loading exam data...</p>
                        </div>

                        <!-- Chart Container -->
                        <div id="chartContainer" style="height: 300px; position: relative;" class="d-none">
                            <canvas id="examChart"></canvas>
                        </div>

                        <!-- No Data State -->
                        <div id="noDataMessage" class="text-center py-4 d-none">
                            <div class="bg-light rounded-circle d-inline-flex p-3 mb-2">
                                <i class="fas fa-chart-bar text-muted" style="font-size: 2rem;"></i>
                            </div>
                            <h6 class="text-muted mb-1">No Exam Results Found</h6>
                            <p class="text-muted mb-0">No exam records available for this student.</p>
                        </div>

                        <!-- Stats Summary -->
                        <div id="statsSummary" class="row g-2 mt-3 d-none">
                            <div class="col-4">
                                <div class="bg-light rounded p-2 text-center">
                                    <small class="text-muted d-block">Average</small>
                                    <span class="fw-bold fs-5 text-primary" id="avgMarks">0</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-2 text-center">
                                    <small class="text-muted d-block">Highest</small>
                                    <span class="fw-bold fs-5 text-success" id="highestMarks">0</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-2 text-center">
                                    <small class="text-muted d-block">Lowest</small>
                                    <span class="fw-bold fs-5 text-danger" id="lowestMarks">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exam Results Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold">
                                <i class="fas fa-table me-2 text-primary"></i>
                                Exam History
                            </h5>
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" 
                                       placeholder="Search exams..." id="searchExams">
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Table Loading -->
                        <div id="tableLoading" class="text-center py-4">
                            <div class="spinner-border text-primary mb-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mb-0">Loading exam list...</p>
                        </div>

                        <!-- Table Container -->
                        <div id="tableContainer" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="py-2 ps-3">#</th>
                                            <th class="py-2">Exam Title</th>
                                            <th class="py-2">Date</th>
                                            <th class="py-2 text-center">Marks</th>
                                            <th class="py-2 text-center">Status</th>
                                            <th class="py-2 text-end pe-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="examTableBody">
                                        <!-- Data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Table Footer with Pagination -->
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light border-top">
                                <small class="text-muted" id="tableInfo">Showing 0 exams</small>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary" onclick="prevPage()" id="prevBtn" disabled>
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <span class="btn btn-outline-secondary disabled" id="pageInfo">Page 1 of 1</span>
                                    <button class="btn btn-outline-secondary" onclick="nextPage()" id="nextBtn" disabled>
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Custom styles */
        .card {
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        
        .btn-group .btn {
            padding: 0.25rem 0.6rem;
        }
        
        .table th {
            font-weight: 600;
            color: #495057;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }
        
        /* Status badge colors */
        .badge.bg-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; }
        .badge.bg-info { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important; }
        .badge.bg-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; }
        .badge.bg-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important; }
        
        /* Print styles */
        @media print {
            .btn, .btn-group, .input-group, footer, nav, .breadcrumb {
                display: none !important;
            }
            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
        }
        
        /* Chart container */
        #chartContainer {
            min-height: 250px;
        }
        
        /* Font size adjustments */
        body {
            font-size: 0.9rem;
        }
        
        h5 {
            font-size: 1.1rem;
        }
        
        .table td {
            font-size: 0.9rem;
            padding: 0.75rem 0.5rem;
        }
        
        .table th {
            font-size: 0.85rem;
        }
        
        .fs-5 {
            font-size: 1.1rem !important;
        }
        
        small {
            font-size: 0.8rem;
        }
    </style>
@endpush

@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!-- html2canvas for export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <script>
        // Get parameters from URL
        const pathParts = window.location.pathname.split('/');
        const classCategoryHasStudentClassId = pathParts[2];  // /students/{id}/{studentId}/exam-results
        const studentId = pathParts[3];
        
        // State management
        let examData = [];
        let filteredData = [];
        let chartInstance = null;
        let currentChartType = 'bar'; // Default to bar chart
        let currentPage = 1;
        const itemsPerPage = 10;
        
        // DOM Elements
        const elements = {
            studentName: document.getElementById('studentName'),
            className: document.getElementById('className'),
            chartLoading: document.getElementById('chartLoading'),
            chartContainer: document.getElementById('chartContainer'),
            noDataMessage: document.getElementById('noDataMessage'),
            statsSummary: document.getElementById('statsSummary'),
            examTableBody: document.getElementById('examTableBody'),
            tableLoading: document.getElementById('tableLoading'),
            tableContainer: document.getElementById('tableContainer'),
            avgMarks: document.getElementById('avgMarks'),
            highestMarks: document.getElementById('highestMarks'),
            lowestMarks: document.getElementById('lowestMarks'),
            searchExams: document.getElementById('searchExams'),
            prevBtn: document.getElementById('prevBtn'),
            nextBtn: document.getElementById('nextBtn'),
            pageInfo: document.getElementById('pageInfo'),
            tableInfo: document.getElementById('tableInfo')
        };
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadExamResults();
            setupEventListeners();
        });
        
        // Setup event listeners
        function setupEventListeners() {
            if (elements.searchExams) {
                let timeout;
                elements.searchExams.addEventListener('keyup', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        filterExams(this.value.toLowerCase());
                    }, 300);
                });
            }
        }
        
        // Load exam results from API
        async function loadExamResults() {
            try {
                // Show loading states
                if (elements.chartLoading) elements.chartLoading.classList.remove('d-none');
                if (elements.tableLoading) elements.tableLoading.classList.remove('d-none');
                if (elements.chartContainer) elements.chartContainer.classList.add('d-none');
                if (elements.tableContainer) elements.tableContainer.classList.add('d-none');
                if (elements.noDataMessage) elements.noDataMessage.classList.add('d-none');
                
                const response = await fetch(`/api/exams/results/${classCategoryHasStudentClassId}/${studentId}`);
                const result = await response.json();
                
                if (result.status === 'success' && result.data && result.data.length > 0) {
                    examData = result.data;
                    filteredData = [...examData];
                    
                    // Update header info
                    updateHeaderInfo();
                    
                    // Render chart (default bar chart)
                    renderChart(examData);
                    
                    // Update statistics
                    updateStatistics(examData);
                    
                    // Render table
                    renderTable();
                    
                    // Hide loading, show content
                    if (elements.chartLoading) elements.chartLoading.classList.add('d-none');
                    if (elements.tableLoading) elements.tableLoading.classList.add('d-none');
                    if (elements.chartContainer) elements.chartContainer.classList.remove('d-none');
                    if (elements.tableContainer) elements.tableContainer.classList.remove('d-none');
                    if (elements.statsSummary) elements.statsSummary.classList.remove('d-none');
                } else {
                    // No data
                    if (elements.chartLoading) elements.chartLoading.classList.add('d-none');
                    if (elements.tableLoading) elements.tableLoading.classList.add('d-none');
                    if (elements.noDataMessage) elements.noDataMessage.classList.remove('d-none');
                    
                    // Show default message in header
                    if (elements.studentName) elements.studentName.textContent = 'No Exam Data';
                    if (elements.className) elements.className.textContent = 'No exams found for this student';
                }
            } catch (error) {
                console.error('Error loading exam results:', error);
                showError('Failed to load exam results. Please try again.');
                
                if (elements.chartLoading) elements.chartLoading.classList.add('d-none');
                if (elements.tableLoading) elements.tableLoading.classList.add('d-none');
                if (elements.noDataMessage) elements.noDataMessage.classList.remove('d-none');
            }
        }
        
        // Update header with student/class info
        function updateHeaderInfo() {
            if (elements.studentName) {
                elements.studentName.textContent = 'Exam Performance';
            }
            if (elements.className) {
                elements.className.textContent = `${examData.length} exams found`;
            }
        }
        
        // Generate gradient colors for bar chart
        function getGradient(ctx, color1, color2) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, color1);
            gradient.addColorStop(1, color2);
            return gradient;
        }
        
        // Render chart
        function renderChart(data) {
            const ctx = document.getElementById('examChart').getContext('2d');
            
            // Sort data by date
            const sortedData = [...data].sort((a, b) => new Date(a.date) - new Date(b.date));
            
            const labels = sortedData.map(exam => {
                const date = new Date(exam.date);
                return `${date.getDate()}/${date.getMonth() + 1}`;
            });
            
            const marks = sortedData.map(exam => parseFloat(exam.marks));
            
            // Destroy existing chart
            if (chartInstance) {
                chartInstance.destroy();
            }
            
            // Chart options
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#333',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: function(context) {
                                return `Marks: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            font: { size: 11 },
                            stepSize: 20,
                            callback: function(value) {
                                return value + '';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11 },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            };
            
            // Dataset configuration based on chart type
            let dataset;
            
            if (currentChartType === 'bar') {
                // Create gradient for bars
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, '#4f46e5');
                gradient.addColorStop(0.5, '#7c3aed');
                gradient.addColorStop(1, '#9333ea');
                
                dataset = {
                    label: 'Marks',
                    data: marks,
                    backgroundColor: gradient,
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                };
            } else {
                // Line chart
                dataset = {
                    label: 'Marks',
                    data: marks,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true
                };
            }
            
            // Create new chart
            chartInstance = new Chart(ctx, {
                type: currentChartType,
                data: {
                    labels: labels,
                    datasets: [dataset]
                },
                options: chartOptions
            });
        }
        
        // Change chart type
        function changeChartType(type) {
            currentChartType = type;
            
            // Update button states
            document.querySelectorAll('[onclick^="changeChartType"]').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('button').classList.add('active');
            
            if (examData.length > 0) {
                renderChart(examData);
            }
        }
        
        // Update statistics
        function updateStatistics(data) {
            const marks = data.map(exam => parseFloat(exam.marks));
            const avg = marks.reduce((a, b) => a + b, 0) / marks.length;
            const highest = Math.max(...marks);
            const lowest = Math.min(...marks);
            
            if (elements.avgMarks) elements.avgMarks.textContent = avg.toFixed(1);
            if (elements.highestMarks) elements.highestMarks.textContent = highest.toFixed(1);
            if (elements.lowestMarks) elements.lowestMarks.textContent = lowest.toFixed(1);
        }
        
        // Render table with pagination
        function renderTable() {
            if (!elements.examTableBody) return;
            
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageData = filteredData.slice(start, end);
            
            let html = '';
            
            pageData.forEach((exam, index) => {
                const marks = parseFloat(exam.marks);
                const date = new Date(exam.date);
                const formattedDate = date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
                
                // Status based on marks
                let statusClass = 'bg-success';
                let statusText = 'Excellent';
                if (marks < 40) {
                    statusClass = 'bg-danger';
                    statusText = 'Needs Improvement';
                } else if (marks < 60) {
                    statusClass = 'bg-warning';
                    statusText = 'Average';
                } else if (marks < 75) {
                    statusClass = 'bg-info';
                    statusText = 'Good';
                }
                
                html += `
                    <tr>
                        <td class="ps-3 fw-medium">${start + index + 1}</td>
                        <td>
                            <span class="fw-medium">${exam.exam_title}</span>
                            ${exam.is_updated ? '<br><small class="text-muted"><i class="fas fa-edit me-1"></i>Updated</small>' : ''}
                        </td>
                        <td>${formattedDate}</td>
                        <td class="text-center fw-bold fs-5 ${marks >= 75 ? 'text-success' : (marks >= 60 ? 'text-info' : (marks >= 40 ? 'text-warning' : 'text-danger'))}">${marks}</td>
                        <td class="text-center">
                            <span class="badge ${statusClass}">${statusText}</span>
                        </td>
                        <td class="text-end pe-3">
                            <button class="btn btn-sm btn-link text-primary p-0 me-2" onclick="viewExamDetails(${exam.exam_id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-link text-success p-0" onclick="printExamResult(${exam.exam_id})" title="Print Result">
                                <i class="fas fa-print"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            elements.examTableBody.innerHTML = html;
            
            // Update pagination
            updatePagination();
        }
        
        // Filter exams by search term
        function filterExams(searchTerm) {
            if (!searchTerm) {
                filteredData = [...examData];
            } else {
                filteredData = examData.filter(exam => 
                    exam.exam_title.toLowerCase().includes(searchTerm)
                );
            }
            
            currentPage = 1;
            renderTable();
            
            // Update table info
            if (elements.tableInfo) {
                elements.tableInfo.textContent = `Showing ${filteredData.length} exams`;
            }
        }
        
        // Update pagination controls
        function updatePagination() {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            
            if (elements.pageInfo) {
                elements.pageInfo.textContent = `Page ${currentPage} of ${totalPages || 1}`;
            }
            
            if (elements.prevBtn) {
                elements.prevBtn.disabled = currentPage === 1;
            }
            
            if (elements.nextBtn) {
                elements.nextBtn.disabled = currentPage === totalPages || totalPages === 0;
            }
            
            if (elements.tableInfo) {
                const start = (currentPage - 1) * itemsPerPage + 1;
                const end = Math.min(currentPage * itemsPerPage, filteredData.length);
                elements.tableInfo.textContent = `Showing ${filteredData.length ? start : 0}-${end} of ${filteredData.length} exams`;
            }
        }
        
        // Pagination functions
        function prevPage() {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        }
        
        function nextPage() {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        }
        
        // Export chart as image
        function exportChart() {
            const chartContainer = document.getElementById('chartContainer');
            if (!chartContainer) return;
            
            html2canvas(chartContainer).then(canvas => {
                const link = document.createElement('a');
                link.download = 'exam-results-chart.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        }
        
        // View exam details
        function viewExamDetails(examId) {
            // You can implement modal or redirect to exam details page
            alert(`View details for exam ID: ${examId} (To be implemented)`);
        }
        
        // Print exam result
        function printExamResult(examId) {
            // You can implement print functionality
            alert(`Print exam ID: ${examId} (To be implemented)`);
        }
        
        // Show error message
        function showError(message) {
            // Simple alert for now - you can enhance with toast
            alert(message);
        }
        
        // Refresh data
        function refreshData() {
            loadExamResults();
        }
    </script>
@endpush