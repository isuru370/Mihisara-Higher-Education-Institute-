@extends('layouts.app')

@section('title', 'View Class Students')
@section('page-title', 'View Class Students')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
    <li class="breadcrumb-item active">View Students</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Class Information Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Class Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="classInfo">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 mb-0">Loading class information...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Class Categories Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tags me-2"></i>Available Categories
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="classCategories">
                            <div class="text-center py-3">
                                <div class="spinner-border text-info" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 mb-0">Loading class categories...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Enrolled Students
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Selected Category Info -->
                        <div id="selectedCategoryInfo" class="alert alert-warning mb-4" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Selected Category:</strong>
                                    <span id="selectedCategoryName" class="fw-bold"></span> -
                                    Fee: Rs. <span id="selectedCategoryFee" class="fw-bold"></span>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" onclick="clearSelection()">
                                    <i class="fas fa-times me-1"></i>Clear Selection
                                </button>
                            </div>
                        </div>

                        <!-- Bulk Actions Bar -->
                        <div id="bulkActions" class="alert alert-info mb-3" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Bulk Actions:</strong>
                                    <span id="selectedStudentsCount" class="badge bg-primary ms-2">0 students
                                        selected</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning" id="bulkDeactivateBtn"
                                        onclick="bulkDeactivateStudents()" disabled>
                                        <i class="fas fa-user-minus me-1"></i>Deactivate Selected
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="clearAllSelections()">
                                        <i class="fas fa-times me-1"></i>Clear All
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Search and Filters -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Search Students</label>
                                <div class="input-group">
                                    <input type="text" id="studentSearch" class="form-control" placeholder="Name or ID...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filter by Grade</label>
                                <select class="form-select" id="gradeFilter">
                                    <option value="">All Grades</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filter by Status</label>
                                <!-- In the status filter dropdown -->
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="true">Active</option>
                                    <option value="false">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Records Per Page</label>
                                <select class="form-select" id="recordsPerPage">
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-success">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllStudents" class="form-check-input">
                                        </th>
                                        <th width="60">#</th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Grade</th>
                                        <th>Guardian Mobile</th>
                                        <th>Enrollment Status</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTableBody">
                                    <!-- Students will be loaded here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Loading State -->
                        <div id="studentsLoading" class="text-center py-4">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading students...</p>
                        </div>

                        <!-- Empty State -->
                        <div id="studentsEmpty" class="text-center py-5 d-none">
                            <div class="empty-state-icon mb-3">
                                <i class="fas fa-users fa-3x text-muted"></i>
                            </div>
                            <h4 class="text-muted">No Students Found</h4>
                            <p class="text-muted">No enrolled students found for this category.</p>
                        </div>

                        <!-- Pagination -->
                        <div id="studentsPagination" class="d-none">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="text-muted" id="paginationInfo">
                                        Showing 0 to 0 of 0 entries
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <nav aria-label="Students pagination">
                                        <ul class="pagination justify-content-end mb-0" id="paginationControls">
                                            <!-- Pagination controls will be inserted here -->
                                        </ul>
                                    </nav>
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
        .category-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .category-card.selected {
            border-color: #198754;
            background-color: #f8fff9;
        }

        .student-row {
            transition: all 0.2s ease;
        }

        .student-row.selected {
            background-color: #e8f5e8;
        }

        .table th {
            background: linear-gradient(135deg, #198754, #157347);
            color: white;
            font-weight: 600;
        }

        .badge-fee {
            font-size: 0.9rem;
            padding: 0.5rem 0.8rem;
        }

        .page-link {
            color: #198754;
            border-color: #dee2e6;
        }

        .page-item.active .page-link {
            background-color: #198754;
            border-color: #198754;
        }

        .page-link:hover {
            color: #146c43;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Helper functions
        const api = function (endpoint) {
            return `/api/${endpoint}`;
        };

        const getCsrfToken = function () {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        };

        const classId = {{ $id }};
        let selectedCategoryId = null;
        let selectedCategoryName = '';
        let selectedCategoryFee = 0;
        let selectedStudents = new Set();
        let allEnrolledStudents = [];
        let filteredStudents = [];

        // Pagination variables
        let studentsCurrentPage = 1;
        let studentsRecordsPerPage = 10;

        document.addEventListener('DOMContentLoaded', function () {
            loadClassInfo();
            loadGradesDropdown();

            // Event listeners
            document.getElementById('studentSearch').addEventListener('input', filterStudents);
            document.getElementById('gradeFilter').addEventListener('change', filterStudents);
            document.getElementById('statusFilter').addEventListener('change', filterStudents);
            document.getElementById('selectAllStudents').addEventListener('change', toggleSelectAll);
            document.getElementById('clearSearch').addEventListener('click', clearSearch);
            document.getElementById('recordsPerPage').addEventListener('change', function () {
                studentsRecordsPerPage = parseInt(this.value);
                studentsCurrentPage = 1;
                renderStudentsTable();
            });
        });

        // Load Class Information
        async function loadClassInfo() {
            try {
                const response = await fetch(api(`class-has-category-classes/class-category-class/${classId}`));
                if (!response.ok) throw new Error('Failed to load class information');

                const data = await response.json();
                const classData = data.data && data.data[0] ? data.data[0].student_class : null;

                if (classData) {
                    document.getElementById('classInfo').innerHTML = `
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Class Name:</strong><br>
                                <span class="fs-5 fw-bold text-primary">${classData.class_name}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Teacher:</strong><br>
                                <span class="fs-6">${classData.teacher ? classData.teacher.fname + ' ' + classData.teacher.lname : 'N/A'}</span>
                                <br><small class="text-muted">${classData.teacher ? classData.teacher.custom_id : ''}</small>
                            </div>
                            <div class="col-md-2">
                                <strong>Subject:</strong><br>
                                <span class="badge bg-light text-dark border">${classData.subject ? classData.subject.subject_name : 'N/A'}</span>
                            </div>
                            <div class="col-md-2">
                                <strong>Grade:</strong><br>
                                <span class="badge bg-primary">${classData.grade ? 'Grade ' + classData.grade.grade_name : 'N/A'}</span>
                            </div>
                            <div class="col-md-2">
                                <strong>Status:</strong><br>
                                <span class="badge ${classData.is_active ? 'bg-success' : 'bg-secondary'}">
                                    ${classData.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        </div>
                    `;
                }

                // Load categories
                renderClassCategories(data.data || []);
            } catch (error) {
                console.error('Error loading class info:', error);
                document.getElementById('classInfo').innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load class information: ${error.message}
                    </div>
                `;
            }
        }

        // Render Class Categories
        function renderClassCategories(categories) {
            const container = document.getElementById('classCategories');

            if (!categories || categories.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No categories available for this class.
                    </div>
                `;
                return;
            }

            container.innerHTML = categories.map((category, index) => {
                const categoryName = category.class_category ? category.class_category.category_name : 'Unknown Category';
                const fee = category.fees || 0;

                return `
                    <div class="col-md-4 mb-3">
                        <div class="card category-card" onclick="selectCategory(${category.id}, '${categoryName}', ${fee})">
                            <div class="card-body">
                                <h6 class="card-title">${categoryName}</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success badge-fee">
                                        <i class="fas fa-rupee-sign me-1"></i>${fee.toFixed(2)}
                                    </span>
                                    <small class="text-muted">Click to view students</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            // Wrap in row
            container.innerHTML = `<div class="row">${container.innerHTML}</div>`;

            // Automatically select first category
            if (categories.length > 0) {
                const firstCategory = categories[0];
                const categoryName = firstCategory.class_category ? firstCategory.class_category.category_name : 'Unknown';
                const fee = firstCategory.fees || 0;

                // Auto-select first category
                setTimeout(() => {
                    selectCategory(firstCategory.id, categoryName, fee, true);
                }, 500);
            }
        }

        // Select Category
        async function selectCategory(categoryId, categoryName, fee, isAutoSelect = false) {
            selectedCategoryId = categoryId;
            selectedCategoryName = categoryName;
            selectedCategoryFee = fee;

            // Update UI - Only if not auto-selecting
            if (!isAutoSelect && event && event.currentTarget) {
                document.querySelectorAll('.category-card').forEach(card => {
                    card.classList.remove('selected');
                });
                event.currentTarget.classList.add('selected');
            } else if (isAutoSelect) {
                // For auto-select, find and select the first category card
                document.querySelectorAll('.category-card').forEach(card => {
                    card.classList.remove('selected');
                });
                // Select the first category card
                const firstCategoryCard = document.querySelector('.category-card');
                if (firstCategoryCard) {
                    firstCategoryCard.classList.add('selected');
                }
            }

            // Show selected category info
            const selectedCategoryInfo = document.getElementById('selectedCategoryInfo');
            if (selectedCategoryInfo) {
                selectedCategoryInfo.style.display = 'block';
                document.getElementById('selectedCategoryName').textContent = categoryName;
                document.getElementById('selectedCategoryFee').textContent = fee.toFixed(2);
            }

            // Load enrolled students for this category
            await loadEnrolledStudents(categoryId);
        }

        // Load Enrolled Students
        async function loadEnrolledStudents(categoryId) {
            showStudentsLoading();

            try {
                console.log('Loading students for class:', classId, 'category:', categoryId);

                // Use the correct endpoint from your example
                const response = await fetch(`/api/student-classes/${classId}/category/${categoryId}`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Raw Students API Response:', data);

                // Handle different response structures
                if (data.status === "empty") {
                    allEnrolledStudents = [];
                } else if (Array.isArray(data)) {
                    // If the response is already an array
                    allEnrolledStudents = data;
                } else if (data.data && Array.isArray(data.data)) {
                    // If response has data property with array
                    allEnrolledStudents = data.data;
                } else if (data.id) {
                    // If the response is a single object (like your example)
                    allEnrolledStudents = [data];
                } else {
                    allEnrolledStudents = [];
                }

                console.log('Processed students count:', allEnrolledStudents.length);

                // Since your API response doesn't include student data directly,
                // we need to fetch student details for each enrollment
                if (allEnrolledStudents.length > 0) {
                    allEnrolledStudents = await Promise.all(
                        allEnrolledStudents.map(async (enrollment) => {
                            try {
                                // Fetch student details
                                const studentResponse = await fetch(`/api/students/${enrollment.student_id}`);
                                if (studentResponse.ok) {
                                    const studentData = await studentResponse.json();
                                    return {
                                        ...enrollment,
                                        student: studentData.data || studentData,
                                        // Map grade_id from student data
                                        grade_id: studentData.data?.grade_id || studentData.grade_id
                                    };
                                }
                            } catch (error) {
                                console.error('Error fetching student details:', error);
                            }
                            return {
                                ...enrollment,
                                student: {
                                    id: enrollment.student_id,
                                    custom_id: `SID-${enrollment.student_id}`,
                                    fname: 'Unknown',
                                    lname: 'Student'
                                }
                            };
                        })
                    );
                }

                filteredStudents = [...allEnrolledStudents];
                studentsCurrentPage = 1;

                renderStudentsTable();
                hideStudentsLoading();

                const bulkActions = document.getElementById('bulkActions');
                if (bulkActions && allEnrolledStudents.length > 0) {
                    bulkActions.style.display = 'block';
                }

            } catch (error) {
                console.error('Error loading enrolled students:', error);
                hideStudentsLoading();
                showStudentsEmptyState();
                showAlert('Failed to load students: ' + error.message, 'danger');
            }
        }

        // Load Grades Dropdown
        async function loadGradesDropdown() {
            try {
                const gradeSelect = document.getElementById('gradeFilter');
                if (!gradeSelect) return;

                // Show loading state
                gradeSelect.innerHTML = '<option value="">Loading grades...</option>';

                const response = await fetch(api('grades/dropdown'));
                if (!response.ok) throw new Error('Failed to load grades');

                const data = await response.json();
                console.log('Grades API Response:', data);

                // Clear existing options
                gradeSelect.innerHTML = '<option value="">All Grades</option>';

                // Check different possible response structures
                let grades = [];
                if (data.data && Array.isArray(data.data)) {
                    grades = data.data;
                } else if (Array.isArray(data)) {
                    grades = data;
                } else if (data.grades && Array.isArray(data.grades)) {
                    grades = data.grades;
                }

                console.log('Loaded grades:', grades);

                if (grades.length === 0) {
                    console.warn('No grades returned from API');
                    return;
                }

                // Populate dropdown
                grades.forEach(grade => {
                    if (grade && grade.id && grade.grade_name) {
                        const option = document.createElement('option');
                        option.value = grade.id;
                        option.textContent = `Grade ${grade.grade_name}`;
                        gradeSelect.appendChild(option);
                    }
                });

            } catch (error) {
                console.error('Error loading grades:', error);
                const gradeSelect = document.getElementById('gradeFilter');
                if (gradeSelect) {
                    gradeSelect.innerHTML = '<option value="">Error loading grades</option>';
                }
            }
        }

        // Render Students Table with Pagination
        function renderStudentsTable() {
            const tbody = document.getElementById('studentsTableBody');
            const emptyState = document.getElementById('studentsEmpty');
            const paginationContainer = document.getElementById('studentsPagination');
            const tableResponsive = document.querySelector('.table-responsive');
            const bulkActions = document.getElementById('bulkActions');
            const filterSummary = document.getElementById('filterSummary');

            if (!tbody) return;

            tbody.innerHTML = '';

            if (filteredStudents.length === 0) {
                if (emptyState) emptyState.classList.remove('d-none');
                if (paginationContainer) paginationContainer.classList.add('d-none');
                if (bulkActions) bulkActions.style.display = 'none';
                if (filterSummary) filterSummary.classList.add('d-none');
                return;
            }

            if (emptyState) emptyState.classList.add('d-none');
            if (paginationContainer) paginationContainer.classList.remove('d-none');
            if (tableResponsive) tableResponsive.classList.remove('d-none');

            // Calculate pagination
            const totalPages = Math.ceil(filteredStudents.length / studentsRecordsPerPage);
            const startIndex = (studentsCurrentPage - 1) * studentsRecordsPerPage;
            const endIndex = Math.min(startIndex + studentsRecordsPerPage, filteredStudents.length);
            const paginatedStudents = filteredStudents.slice(startIndex, endIndex);

            // Get the grade dropdown options to map grade IDs to names
            const gradeDropdown = document.getElementById('gradeFilter');
            const gradeOptions = gradeDropdown ? Array.from(gradeDropdown.options) : [];

            // Create a mapping of grade ID to grade name
            const gradeMap = {};
            gradeOptions.forEach(option => {
                if (option.value && option.value !== '') {
                    // Extract grade name from the option text (remove "Grade " prefix)
                    const gradeName = option.textContent.replace('Grade ', '');
                    gradeMap[option.value] = gradeName;
                }
            });

            // Render table rows
            paginatedStudents.forEach((enrollment, index) => {
                const actualIndex = startIndex + index;
                const student = enrollment.student || {};
                const isSelected = selectedStudents.has(enrollment.id);

                // FIXED: Handle status as boolean (true/false) not 1/0
                const isActiveBoolean = enrollment.status === true || enrollment.status === 'true';

                // Get grade name properly
                let gradeDisplay = 'N/A';
                let gradeId = null;

                // Try to get grade ID from different sources
                if (enrollment.grade_id) {
                    gradeId = enrollment.grade_id;
                } else if (student.grade_id) {
                    gradeId = student.grade_id;
                } else if (student.grade && student.grade.id) {
                    gradeId = student.grade.id;
                }

                // Get grade name from grade map or directly from student data
                if (gradeId && gradeMap[gradeId]) {
                    gradeDisplay = gradeMap[gradeId];
                } else if (student.grade && student.grade.grade_name) {
                    gradeDisplay = student.grade.grade_name;
                } else if (gradeId) {
                    gradeDisplay = `ID: ${gradeId}`;
                }

                const row = `
                    <tr class="student-row ${isSelected ? 'selected' : ''}">
                        <td>
                            <input type="checkbox" class="form-check-input student-checkbox" 
                                   value="${enrollment.id}" ${isSelected ? 'checked' : ''}
                                   onchange="toggleStudentSelection(${enrollment.id}, this.checked)">
                        </td>
                        <td class="fw-bold text-muted">${actualIndex + 1}</td>
                        <td>
                            <span class="badge bg-secondary">${student.custom_id || 'N/A'}</span>
                        </td>
                        <td>
                            <strong>${student.fname || ''} ${student.lname || ''}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info">${gradeDisplay}</span>
                        </td>
                        <td>${student.guardian_mobile || student.mobile || 'N/A'}</td>
                        <td>
                            <span class="badge ${isActiveBoolean ? 'bg-success' : 'bg-warning text-dark'}">
                                ${isActiveBoolean ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                ${isActiveBoolean ?
                        `<button class="btn btn-warning" onclick="deactivateStudent(${enrollment.id})" title="Deactivate">
                                        <i class="fas fa-user-minus"></i>
                                    </button>` :
                        `<button class="btn btn-success" onclick="activateStudent(${enrollment.id})" title="Activate">
                                        <i class="fas fa-user-check"></i>
                                    </button>`
                    }
                            </div>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Update pagination info
            updatePaginationInfo(startIndex, endIndex, filteredStudents.length);

            // Update pagination controls
            updatePaginationControls(totalPages);

            updateSelectedCount();
            updateSelectAllCheckbox();
            updateBulkActionButtons();
            updateFilterSummary();
        }

        // Update Select All Checkbox State
        function updateSelectAllCheckbox() {
            const selectAllCheckbox = document.getElementById('selectAllStudents');
            const allCheckboxes = document.querySelectorAll('.student-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.student-checkbox:checked');

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = checkedCheckboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
                selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
            }
        }

        // Update Bulk Action Buttons
        function updateBulkActionButtons() {
            const bulkDeactivateBtn = document.getElementById('bulkDeactivateBtn');
            if (bulkDeactivateBtn) {
                bulkDeactivateBtn.disabled = selectedStudents.size === 0;
            }
        }

        // Update Pagination Information
        function updatePaginationInfo(startIndex, endIndex, total) {
            const infoElement = document.getElementById('paginationInfo');
            if (infoElement) {
                if (total === 0) {
                    infoElement.textContent = 'Showing 0 to 0 of 0 entries';
                } else {
                    infoElement.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${total} entries`;
                }
            }
        }

        // Update Pagination Controls
        function updatePaginationControls(totalPages) {
            const paginationContainer = document.getElementById('paginationControls');
            if (!paginationContainer) return;

            paginationContainer.innerHTML = '';

            // Previous button
            const prevButton = `
                <li class="page-item ${studentsCurrentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${studentsCurrentPage - 1})" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            `;
            paginationContainer.innerHTML += prevButton;

            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, studentsCurrentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageItem = `
                    <li class="page-item ${i === studentsCurrentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                    </li>
                `;
                paginationContainer.innerHTML += pageItem;
            }

            // Next button
            const nextButton = `
                <li class="page-item ${studentsCurrentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${studentsCurrentPage + 1})" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            `;
            paginationContainer.innerHTML += nextButton;
        }

        // Change Page
        function changePage(page) {
            if (page < 1 || page > Math.ceil(filteredStudents.length / studentsRecordsPerPage)) {
                return;
            }
            studentsCurrentPage = page;
            renderStudentsTable();
        }

        // Student Selection Functions
        function toggleStudentSelection(enrollmentId, isSelected) {
            if (isSelected) {
                selectedStudents.add(enrollmentId);
            } else {
                selectedStudents.delete(enrollmentId);
            }

            // Update row appearance
            const row = event.target.closest('tr');
            if (row) {
                row.classList.toggle('selected', isSelected);
            }

            updateSelectedCount();
            updateSelectAllCheckbox();
            updateBulkActionButtons();
        }

        function toggleSelectAll(event) {
            const isChecked = event.target.checked;
            const checkboxes = document.querySelectorAll('.student-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const enrollmentId = parseInt(checkbox.value);

                if (isChecked) {
                    selectedStudents.add(enrollmentId);
                } else {
                    selectedStudents.delete(enrollmentId);
                }
            });

            // Update all rows
            document.querySelectorAll('.student-row').forEach(row => {
                row.classList.toggle('selected', isChecked);
            });

            updateSelectedCount();
            updateBulkActionButtons();
        }

        function clearAllSelections() {
            selectedStudents.clear();
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.querySelectorAll('.student-row').forEach(row => {
                row.classList.remove('selected');
            });

            const selectAllStudents = document.getElementById('selectAllStudents');
            if (selectAllStudents) {
                selectAllStudents.checked = false;
            }

            updateSelectedCount();
            updateBulkActionButtons();
        }

        // Filter Students
        function filterStudents() {
            const searchTerm = document.getElementById('studentSearch')?.value.toLowerCase() || '';
            const gradeFilter = document.getElementById('gradeFilter')?.value || '';
            const statusFilter = document.getElementById('statusFilter')?.value || '';

            filteredStudents = allEnrolledStudents.filter(enrollment => {
                const student = enrollment.student || {};

                // Check if student object exists
                if (!student) {
                    return false;
                }

                // Search filter
                const matchesSearch = !searchTerm ||
                    (student.fname && student.fname.toLowerCase().includes(searchTerm)) ||
                    (student.lname && student.lname.toLowerCase().includes(searchTerm)) ||
                    (student.custom_id && student.custom_id.toLowerCase().includes(searchTerm));

                // Grade filter
                let matchesGrade = true;
                if (gradeFilter) {
                    // Try multiple possible locations for grade_id
                    const studentGradeId = enrollment.grade_id ||
                        student.grade_id ||
                        student.grade?.id;

                    matchesGrade = studentGradeId && studentGradeId.toString() === gradeFilter.toString();
                }

                // Status filter - FIXED: Handle boolean status
                let matchesStatus = true;
                if (statusFilter) {
                    // Convert enrollment status to boolean
                    const isActive = enrollment.status === true || enrollment.status === 'true';
                    // Convert filter value to boolean
                    const filterActive = statusFilter === 'true';
                    // Compare the boolean values
                    matchesStatus = isActive === filterActive;
                }

                return matchesSearch && matchesGrade && matchesStatus;
            });

            studentsCurrentPage = 1;
            renderStudentsTable();
        }

        // Update Filter Summary
        function updateFilterSummary() {
            const searchTerm = document.getElementById('studentSearch')?.value || '';
            const gradeFilter = document.getElementById('gradeFilter');
            const statusFilter = document.getElementById('statusFilter');
            const summaryElement = document.getElementById('filterSummary');
            const activeFiltersElement = document.getElementById('activeFilters');

            if (!summaryElement || !activeFiltersElement) return;

            let filters = [];

            if (searchTerm) {
                filters.push(`Search: "${searchTerm}"`);
            }

            if (gradeFilter && gradeFilter.value) {
                const selectedGrade = gradeFilter.options[gradeFilter.selectedIndex].text;
                filters.push(`Grade: ${selectedGrade}`);
            }

            if (statusFilter && statusFilter.value) {
                const selectedStatus = statusFilter.options[statusFilter.selectedIndex].text;
                filters.push(`Status: ${selectedStatus}`);
            }

            if (filters.length > 0) {
                activeFiltersElement.textContent = filters.join(' • ');
                summaryElement.classList.remove('d-none');
            } else {
                summaryElement.classList.add('d-none');
            }
        }

        // Bulk Deactivate Students
        async function bulkDeactivateStudents() {
            if (selectedStudents.size === 0) {
                showAlert('Please select at least one student to deactivate', 'warning');
                return;
            }

            if (!confirm(`Are you sure you want to deactivate ${selectedStudents.size} students?`)) {
                return;
            }

            const bulkDeactivateBtn = document.getElementById('bulkDeactivateBtn');
            const originalText = bulkDeactivateBtn ? bulkDeactivateBtn.innerHTML : '';

            try {
                if (bulkDeactivateBtn) {
                    bulkDeactivateBtn.disabled = true;
                    bulkDeactivateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deactivating...';
                }

                const enrollmentIds = Array.from(selectedStudents);
                const requestData = {
                    student_class_ids: enrollmentIds
                };

                const response = await fetch(api('student-classes/bulk-deactivate'), {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showAlert(`Successfully deactivated ${result.deactivated_count} students`, 'success');

                    // Reload enrolled students to update the table
                    if (selectedCategoryId) {
                        await loadEnrolledStudents(selectedCategoryId);
                    }

                    // Clear selections
                    clearAllSelections();

                } else {
                    throw new Error(result.message || 'Failed to deactivate students');
                }

            } catch (error) {
                console.error('Error bulk deactivating students:', error);
                showAlert('Failed to deactivate students: ' + error.message, 'danger');
            } finally {
                if (bulkDeactivateBtn) {
                    bulkDeactivateBtn.disabled = false;
                    bulkDeactivateBtn.innerHTML = '<i class="fas fa-user-minus me-1"></i>Deactivate Selected';
                }
            }
        }

        // Activate Single Student
        async function activateStudent(enrollmentId) {
            if (!confirm('Are you sure you want to activate this student?')) {
                return;
            }

            try {
                const response = await fetch(api(`student-classes/${enrollmentId}/activate`), {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showAlert('Student activated successfully', 'success');

                    // Reload enrolled students to update the table
                    if (selectedCategoryId) {
                        await loadEnrolledStudents(selectedCategoryId);
                    }

                } else {
                    throw new Error(result.message || 'Failed to activate student');
                }

            } catch (error) {
                console.error('Error activating student:', error);
                showAlert('Failed to activate student: ' + error.message, 'danger');
            }
        }

        // Deactivate Single Student
        async function deactivateStudent(enrollmentId) {
            if (!confirm('Are you sure you want to deactivate this student?')) {
                return;
            }

            try {
                const response = await fetch(api(`student-classes/${enrollmentId}/deactivate`), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showAlert('Student deactivated successfully', 'success');

                    // Reload enrolled students to update the table
                    if (selectedCategoryId) {
                        await loadEnrolledStudents(selectedCategoryId);
                    }

                } else {
                    throw new Error(result.message || 'Failed to deactivate student');
                }

            } catch (error) {
                console.error('Error deactivating student:', error);
                showAlert('Failed to deactivate student: ' + error.message, 'danger');
            }
        }

        // Utility Functions
        function clearSelection() {
            selectedCategoryId = null;
            selectedCategoryName = '';
            selectedCategoryFee = 0;
            selectedStudents.clear();
            allEnrolledStudents = [];
            filteredStudents = [];

            document.querySelectorAll('.category-card').forEach(card => {
                card.classList.remove('selected');
            });

            const selectedCategoryInfo = document.getElementById('selectedCategoryInfo');
            if (selectedCategoryInfo) selectedCategoryInfo.style.display = 'none';

            const bulkActions = document.getElementById('bulkActions');
            if (bulkActions) bulkActions.style.display = 'none';

            const filterSummary = document.getElementById('filterSummary');
            if (filterSummary) filterSummary.classList.add('d-none');

            // Clear table
            const tbody = document.getElementById('studentsTableBody');
            if (tbody) tbody.innerHTML = '';

            const studentsEmpty = document.getElementById('studentsEmpty');
            if (studentsEmpty) studentsEmpty.classList.remove('d-none');

            const studentsPagination = document.getElementById('studentsPagination');
            if (studentsPagination) studentsPagination.classList.add('d-none');

            updateSelectedCount();
        }

        function clearSearch() {
            const studentSearch = document.getElementById('studentSearch');
            const gradeFilter = document.getElementById('gradeFilter');
            const statusFilter = document.getElementById('statusFilter');

            if (studentSearch) studentSearch.value = '';
            if (gradeFilter) gradeFilter.value = '';
            if (statusFilter) statusFilter.value = '';

            filterStudents();
        }

        function updateSelectedCount() {
            const count = selectedStudents.size;
            const selectedStudentsCount = document.getElementById('selectedStudentsCount');
            if (selectedStudentsCount) {
                selectedStudentsCount.textContent = `${count} student${count !== 1 ? 's' : ''} selected`;
            }
        }

        function showStudentsLoading() {
            const studentsLoading = document.getElementById('studentsLoading');
            const tableResponsive = document.querySelector('.table-responsive');
            const paginationContainer = document.getElementById('studentsPagination');
            const filterSummary = document.getElementById('filterSummary');

            if (studentsLoading) studentsLoading.classList.remove('d-none');
            if (tableResponsive) tableResponsive.classList.add('d-none');
            if (paginationContainer) paginationContainer.classList.add('d-none');
            if (filterSummary) filterSummary.classList.add('d-none');
        }

        function hideStudentsLoading() {
            const studentsLoading = document.getElementById('studentsLoading');
            if (studentsLoading) studentsLoading.classList.add('d-none');
        }

        function showStudentsEmptyState() {
            const studentsEmpty = document.getElementById('studentsEmpty');
            const tableResponsive = document.querySelector('.table-responsive');
            const paginationContainer = document.getElementById('studentsPagination');
            const filterSummary = document.getElementById('filterSummary');
            const bulkActions = document.getElementById('bulkActions');

            if (studentsEmpty) studentsEmpty.classList.remove('d-none');
            if (tableResponsive) tableResponsive.classList.add('d-none');
            if (paginationContainer) paginationContainer.classList.add('d-none');
            if (filterSummary) filterSummary.classList.add('d-none');
            if (bulkActions) bulkActions.style.display = 'none';
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                <strong>${type === 'success' ? 'Success!' : type === 'warning' ? 'Warning!' : 'Error!'}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
@endpush