@extends('layouts.app')

@section('title', 'Add Students to Class')
@section('page-title', 'Add Students to Class')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('class_rooms.index') }}">Classes</a></li>
    <li class="breadcrumb-item active">Add Students</li>
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

        <!-- Students Selection Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-plus me-2"></i>Select Students
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filters -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Search Students</label>
                                <div class="input-group">
                                    <input type="text" id="studentSearch" class="form-control"
                                        placeholder="Search by name or ID...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Filter by Grade</label>
                                <select class="form-select" id="gradeFilter">
                                    <option value="">All Grades</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Records Per Page</label>
                                <select class="form-select" id="recordsPerPage">
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>

                        <!-- Selected Category Info -->
                        <div id="selectedCategoryInfo" class="alert alert-warning mb-4" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Selected Category:</strong>
                                    <span id="selectedCategoryName" class="fw-bold"></span> -
                                    Fee: Rs. <span id="selectedCategoryFee" class="fw-bold"></span>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" onclick="clearSelection()">
                                    <i class="fas fa-times me-1"></i>Clear
                                </button>
                            </div>
                        </div>

                        <!-- Enrolled Students Action Bar -->
                        <div id="enrolledActions" class="alert alert-info mb-3" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Enrolled Students Management:</strong>
                                    <span id="selectedEnrolledCount" class="badge bg-primary ms-2">0 selected</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning" id="deactivateEnrolledBtn"
                                        onclick="bulkDeactivateStudents()" disabled>
                                        <i class="fas fa-user-minus me-1"></i>Deactivate Selected
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="clearEnrolledSelections()">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </button>
                                </div>
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
                                        <th width="80">#</th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Grade</th>
                                        <th>Mobile</th>
                                        <th>Enrollment Status</th>
                                        <th width="100">Actions</th>
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
                            <p class="text-muted">No active students match your search criteria.</p>
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

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span id="selectedCount" class="badge bg-primary fs-6">0 students selected</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-primary" onclick="loadStudents()">
                                            <i class="fas fa-sync-alt me-2"></i>Refresh
                                        </button>
                                        <button class="btn btn-secondary" onclick="clearAllSelections()">
                                            <i class="fas fa-times me-2"></i>Clear All
                                        </button>
                                        <button class="btn btn-success" id="addStudentsBtn" onclick="addStudentsToClass()"
                                            disabled>
                                            <i class="fas fa-user-plus me-2"></i>Add Selected Students
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Helper functions
        const api = function (endpoint) {
            return `/api/${endpoint}`;
        };

        const getCsrfToken = function () {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        };

        const classId = {{ $class_id }};
        let selectedCategoryId = null;
        let selectedCategoryName = '';
        let selectedCategoryFee = 0;
        let selectedStudents = new Set();
        let selectedEnrolledStudents = new Set(); // For enrolled student selection
        let allStudents = [];
        let enrolledStudents = {}; // Store enrolled students data

        // Pagination variables
        let studentsCurrentPage = 1;
        let studentsRecordsPerPage = 10;
        let filteredStudents = [];

        document.addEventListener('DOMContentLoaded', function () {
            loadClassInfo();
            loadGradesDropdown();
            loadStudents();

            // Event listeners
            document.getElementById('studentSearch').addEventListener('input', filterStudents);
            document.getElementById('gradeFilter').addEventListener('change', filterStudents);
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

                const responseData = await response.json();

                // ✅ only active
                const activeData = (responseData.data || []).filter(item => item.status == 1);

                const classData = activeData[0] ? activeData[0].student_class : null;

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

                renderClassCategories(activeData);

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

            container.innerHTML = categories.map(category => {
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
                                        <small class="text-muted">Click to select</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
            }).join('');

            // Wrap in row
            container.innerHTML = `<div class="row">${container.innerHTML}</div>`;
        }

        // Select Category
        function selectCategory(categoryId, categoryName, fee) {
            selectedCategoryId = categoryId;
            selectedCategoryName = categoryName;
            selectedCategoryFee = fee;

            // Update UI
            document.querySelectorAll('.category-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');

            // Show selected category info
            document.getElementById('selectedCategoryInfo').style.display = 'block';
            document.getElementById('selectedCategoryName').textContent = categoryName;
            document.getElementById('selectedCategoryFee').textContent = fee.toFixed(2);

            // Load enrolled students for this category
            loadEnrolledStudents(categoryId);

            // Enable add button if students are selected
            updateAddButtonState();
        }

        // Load Enrolled Students
        async function loadEnrolledStudents(categoryId) {
            try {
                const response = await fetch(api(`student-classes/${classId}/category/${categoryId}`));

                if (!response.ok) {
                    throw new Error('Failed to load enrolled students');
                }

                const data = await response.json();

                // Reset enrolled students
                enrolledStudents = {};
                selectedEnrolledStudents.clear(); // Clear previous selections

                if (data.status !== "empty") {
                    // Process enrolled students array
                    const enrollments = Array.isArray(data) ? data : (data.data || []);

                    enrollments.forEach(enrollment => {
                        enrolledStudents[enrollment.student_id] = {
                            status: enrollment.status,
                            enrollmentId: enrollment.id,
                            is_free_card: enrollment.is_free_card || 0
                        };
                    });
                }

                // Show/hide enrolled actions bar
                toggleEnrolledActionsBar();

                // Re-render table with enrollment status colors
                renderStudentsTable();

            } catch (error) {
                console.error('Error loading enrolled students:', error);
                // Continue without enrollment data
                enrolledStudents = {};
                selectedEnrolledStudents.clear();
                toggleEnrolledActionsBar();
                renderStudentsTable();
            }
        }

        // Toggle Enrolled Actions Bar
        function toggleEnrolledActionsBar() {
            const enrolledActions = document.getElementById('enrolledActions');
            const hasEnrolledStudents = Object.keys(enrolledStudents).length > 0;

            if (hasEnrolledStudents) {
                enrolledActions.style.display = 'block';
            } else {
                enrolledActions.style.display = 'none';
                selectedEnrolledStudents.clear();
                updateEnrolledActionButtons();
            }
        }

        // Load Grades Dropdown
        async function loadGradesDropdown() {
            try {
                const response = await fetch(api('grades/dropdown'));
                if (!response.ok) throw new Error('Failed to load grades');

                const data = await response.json();
                const grades = data.data || data;
                const gradeSelect = document.getElementById('gradeFilter');

                grades.forEach(grade => {
                    const option = document.createElement('option');
                    option.value = grade.id;
                    option.textContent = `Grade ${grade.grade_name}`;
                    gradeSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading grades:', error);
            }
        }

        // Load Students
        async function loadStudents() {
            showStudentsLoading();

            try {
                const response = await fetch(api('students/active'));
                if (!response.ok) throw new Error('Failed to load students');

                const data = await response.json();
                allStudents = data.data || data;
                filteredStudents = [...allStudents];

                studentsCurrentPage = 1;
                renderStudentsTable();
                hideStudentsLoading();
            } catch (error) {
                console.error('Error loading students:', error);
                hideStudentsLoading();
                showStudentsEmptyState();
            }
        }

        // Render Students Table with Pagination
        function renderStudentsTable() {
            const tbody = document.getElementById('studentsTableBody');
            const emptyState = document.getElementById('studentsEmpty');
            const paginationContainer = document.getElementById('studentsPagination');

            tbody.innerHTML = '';

            if (filteredStudents.length === 0) {
                emptyState.classList.remove('d-none');
                paginationContainer.classList.add('d-none');
                return;
            }

            emptyState.classList.add('d-none');
            paginationContainer.classList.remove('d-none');

            // Calculate pagination
            const totalPages = Math.ceil(filteredStudents.length / studentsRecordsPerPage);
            const startIndex = (studentsCurrentPage - 1) * studentsRecordsPerPage;
            const endIndex = Math.min(startIndex + studentsRecordsPerPage, filteredStudents.length);
            const paginatedStudents = filteredStudents.slice(startIndex, endIndex);

            // Render table rows with enrollment status
            paginatedStudents.forEach((student, index) => {
                const actualIndex = startIndex + index;
                const isSelected = selectedStudents.has(student.id);
                const isEnrolledSelected = selectedEnrolledStudents.has(student.id);
                const enrollment = enrolledStudents[student.id];

                let rowClass = 'enrollment-not-enrolled';
                let statusBadge = '';
                let enrollmentCheckbox = '';
                let actionButtons = '';
                let disabled = '';
                let tooltip = '';

                if (enrollment) {
                    const isEnrolledChecked = isEnrolledSelected ? 'checked' : '';

                    if (enrollment.status === 1) {
                        rowClass = 'enrollment-active';
                        statusBadge = '<span class="badge bg-success">Enrolled (Active)</span>';
                        disabled = 'disabled';
                        tooltip = 'title="Already enrolled and active"';
                        enrollmentCheckbox = `
                                <input type="checkbox" class="form-check-input enrollment-checkbox" 
                                       value="${student.id}" ${isEnrolledChecked}
                                       onchange="toggleEnrolledStudentSelection(${student.id}, this.checked)">
                            `;
                        actionButtons = `
                                <button class="btn btn-sm btn-warning" onclick="deactivateSingleStudent(${enrollment.enrollmentId}, ${student.id})" title="Deactivate Student">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            `;
                    } else {
                        rowClass = 'enrollment-inactive';
                        statusBadge = '<span class="badge bg-warning text-dark">Enrolled (Inactive)</span>';
                        disabled = 'disabled';
                        tooltip = 'title="Already enrolled but inactive"';
                        actionButtons = `
                                <span class="text-muted">Inactive</span>
                            `;
                    }
                } else {
                    statusBadge = '<span class="badge bg-secondary">Not Enrolled</span>';
                    tooltip = 'title="Available for enrollment"';
                    actionButtons = '';
                }

                const row = `
                        <tr class="student-row ${rowClass} ${isSelected ? 'selected' : ''}" ${tooltip}>
                            <td>
                                ${enrollmentCheckbox}
                                <input type="checkbox" class="form-check-input student-checkbox" 
                                       value="${student.id}" ${isSelected ? 'checked' : ''} ${disabled}
                                       onchange="toggleStudentSelection(${student.id}, this.checked, ${enrollment ? 'true' : 'false'})">
                            </td>
                            <td class="fw-bold text-muted">${actualIndex + 1}</td>
                            <td>
                                <span class="badge bg-secondary">${student.custom_id}</span>
                            </td>
                            <td>
                                <strong>${student.initial_name}</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">${student.grade ? 'Grade ' + student.grade.grade_name : 'N/A'}</span>
                            </td>
                            <td>${student.mobile || 'N/A'}</td>
                            <td>${statusBadge}</td>
                            <td>${actionButtons}</td>
                        </tr>
                    `;
                tbody.innerHTML += row;
            });

            // Update pagination info
            updatePaginationInfo(startIndex, endIndex, filteredStudents.length);

            // Update pagination controls
            updatePaginationControls(totalPages);

            updateSelectedCount();
            updateEnrolledSelectedCount();
            updateSelectAllCheckbox();
            updateEnrolledActionButtons();
        }

        // Toggle Enrolled Student Selection
        function toggleEnrolledStudentSelection(studentId, isSelected) {
            if (isSelected) {
                selectedEnrolledStudents.add(studentId);
            } else {
                selectedEnrolledStudents.delete(studentId);
            }

            updateEnrolledSelectedCount();
            updateEnrolledActionButtons();
        }

        // Update Enrolled Selected Count
        function updateEnrolledSelectedCount() {
            const count = selectedEnrolledStudents.size;
            document.getElementById('selectedEnrolledCount').textContent = `${count} selected`;
        }

        // Update Enrolled Action Buttons
        function updateEnrolledActionButtons() {
            const deactivateBtn = document.getElementById('deactivateEnrolledBtn');
            deactivateBtn.disabled = selectedEnrolledStudents.size === 0;
        }

        // Clear Enrolled Selections
        function clearEnrolledSelections() {
            selectedEnrolledStudents.clear();
            document.querySelectorAll('.enrollment-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateEnrolledSelectedCount();
            updateEnrolledActionButtons();
        }

        // Update Select All Checkbox State
        function updateSelectAllCheckbox() {
            const selectAllCheckbox = document.getElementById('selectAllStudents');
            const allCheckboxes = document.querySelectorAll('.student-checkbox:not(:disabled)');
            const checkedCheckboxes = document.querySelectorAll('.student-checkbox:not(:disabled):checked');

            if (allCheckboxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.disabled = true;
            } else {
                selectAllCheckbox.disabled = false;
                selectAllCheckbox.checked = checkedCheckboxes.length === allCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
            }
        }

        // Update Pagination Information
        function updatePaginationInfo(startIndex, endIndex, total) {
            const infoElement = document.getElementById('paginationInfo');
            if (total === 0) {
                infoElement.textContent = 'Showing 0 to 0 of 0 entries';
            } else {
                infoElement.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${total} entries`;
            }
        }

        // Update Pagination Controls
        function updatePaginationControls(totalPages) {
            const paginationContainer = document.getElementById('paginationControls');
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

            // Scroll to top of table
            document.getElementById('studentsTableBody').closest('.table-responsive').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // Student Selection Functions
        function toggleStudentSelection(studentId, isSelected, isEnrolled = false) {
            // Prevent selection if student is already enrolled
            if (isEnrolled) {
                event.target.checked = false;
                showAlert('This student is already enrolled in this class category', 'warning');
                return;
            }

            if (isSelected) {
                selectedStudents.add(studentId);
            } else {
                selectedStudents.delete(studentId);
            }

            // Update row appearance
            const row = event.target.closest('tr');
            if (row) {
                row.classList.toggle('selected', isSelected);
            }

            updateSelectedCount();
            updateAddButtonState();
            updateSelectAllCheckbox();
        }

        function toggleSelectAll() {
            const isChecked = event.target.checked;
            const checkboxes = document.querySelectorAll('.student-checkbox:not(:disabled)');

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const studentId = parseInt(checkbox.value);

                if (isChecked) {
                    selectedStudents.add(studentId);
                } else {
                    selectedStudents.delete(studentId);
                }
            });

            // Update all enabled rows
            document.querySelectorAll('.student-row').forEach(row => {
                if (!row.querySelector('.student-checkbox').disabled) {
                    row.classList.toggle('selected', isChecked);
                }
            });

            updateSelectedCount();
            updateAddButtonState();
        }

        function clearAllSelections() {
            selectedStudents.clear();
            selectedEnrolledStudents.clear();
            document.querySelectorAll('.student-checkbox').forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = false;
                }
            });
            document.querySelectorAll('.enrollment-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.querySelectorAll('.student-row').forEach(row => {
                row.classList.remove('selected');
            });
            document.getElementById('selectAllStudents').checked = false;

            updateSelectedCount();
            updateEnrolledSelectedCount();
            updateAddButtonState();
            updateEnrolledActionButtons();
        }

        // Filter Students
        function filterStudents() {
            const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
            const gradeFilter = document.getElementById('gradeFilter').value;

            filteredStudents = allStudents.filter(student => {
                const matchesSearch = !searchTerm ||
                    student.fname.toLowerCase().includes(searchTerm) ||
                    student.lname.toLowerCase().includes(searchTerm) ||
                    student.custom_id.toLowerCase().includes(searchTerm);

                const matchesGrade = !gradeFilter ||
                    (student.grade && student.grade.id == gradeFilter);

                return matchesSearch && matchesGrade;
            });

            studentsCurrentPage = 1;
            renderStudentsTable();
        }

        // Bulk Deactivate Students - FIXED
        async function bulkDeactivateStudents() {
            if (selectedEnrolledStudents.size === 0) {
                showAlert('Please select at least one enrolled student to deactivate', 'warning');
                return;
            }

            // Get enrollment IDs for selected students
            const enrollmentIds = [];
            selectedEnrolledStudents.forEach(studentId => {
                const enrollment = enrolledStudents[studentId];
                if (enrollment && enrollment.status === 1) { // Only active students
                    enrollmentIds.push(enrollment.enrollmentId);
                }
            });

            if (enrollmentIds.length === 0) {
                showAlert('No active enrolled students selected for deactivation', 'warning');
                return;
            }

            const deactivateBtn = document.getElementById('deactivateEnrolledBtn');
            const originalText = deactivateBtn.innerHTML;

            try {
                deactivateBtn.disabled = true;
                deactivateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deactivating...';

                const requestData = {
                    student_class_ids: enrollmentIds
                };

                console.log('Sending deactivation data:', requestData);

                // FIXED: Changed to PUT method
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

                    // Clear selections on success
                    clearEnrolledSelections();

                } else {
                    throw new Error(result.message || 'Failed to deactivate students');
                }

            } catch (error) {
                console.error('Error deactivating students:', error);
                showAlert('Failed to deactivate students: ' + error.message, 'danger');
            } finally {
                deactivateBtn.disabled = false;
                deactivateBtn.innerHTML = originalText;
                updateEnrolledActionButtons();
            }
        }

        // Deactivate Single Student - FIXED
        async function deactivateSingleStudent(enrollmentId, studentId) {
            if (!confirm('Are you sure you want to deactivate this student?')) {
                return;
            }

            try {
                const requestData = {
                    student_class_ids: [enrollmentId]
                };

                // FIXED: Changed to PUT method
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

        // Add Students to Class
        async function addStudentsToClass() {
            if (!selectedCategoryId) {
                showAlert('Please select a category first', 'warning');
                return;
            }

            if (selectedStudents.size === 0) {
                showAlert('Please select at least one student', 'warning');
                return;
            }

            const addBtn = document.getElementById('addStudentsBtn');
            const originalText = addBtn.innerHTML;

            try {
                addBtn.disabled = true;
                addBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding Students...';

                const studentsArray = Array.from(selectedStudents).map(studentId => ({
                    student_id: studentId
                }));

                const requestData = {
                    student_classes_id: classId,
                    class_category_has_student_class_id: selectedCategoryId,
                    students: studentsArray
                };

                console.log('Sending data:', requestData);

                const response = await fetch(api('student-classes/bulk'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    let message = `Successfully processed ${result.created_count} students`;

                    if (result.skipped && result.skipped.length > 0) {
                        message += `. ${result.skipped.length} students skipped (already enrolled or other issues)`;
                    }

                    showAlert(message, 'success');

                    // Reload enrolled students to update the table
                    if (selectedCategoryId) {
                        await loadEnrolledStudents(selectedCategoryId);
                    }

                    // Clear selections on success
                    clearAllSelections();

                } else {
                    throw new Error(result.message || 'Failed to add students');
                }

            } catch (error) {
                console.error('Error adding students:', error);
                showAlert('Failed to add students: ' + error.message, 'danger');
            } finally {
                addBtn.disabled = false;
                addBtn.innerHTML = originalText;
                updateAddButtonState();
            }
        }

        // Utility Functions
        function clearSelection() {
            selectedCategoryId = null;
            selectedCategoryName = '';
            selectedCategoryFee = 0;
            enrolledStudents = {};
            selectedEnrolledStudents.clear();

            document.querySelectorAll('.category-card').forEach(card => {
                card.classList.remove('selected');
            });

            document.getElementById('selectedCategoryInfo').style.display = 'none';
            document.getElementById('enrolledActions').style.display = 'none';

            // Re-render table without enrollment colors
            renderStudentsTable();
            updateAddButtonState();
        }

        function clearSearch() {
            document.getElementById('studentSearch').value = '';
            filterStudents();
        }

        function updateSelectedCount() {
            const count = selectedStudents.size;
            document.getElementById('selectedCount').textContent = `${count} student${count !== 1 ? 's' : ''} selected`;
        }

        function updateAddButtonState() {
            const addBtn = document.getElementById('addStudentsBtn');
            addBtn.disabled = !selectedCategoryId || selectedStudents.size === 0;
        }

        function showStudentsLoading() {
            document.getElementById('studentsLoading').classList.remove('d-none');
            document.getElementById('studentsTableBody').closest('.table-responsive').classList.add('d-none');
            document.getElementById('studentsPagination').classList.add('d-none');
        }

        function hideStudentsLoading() {
            document.getElementById('studentsLoading').classList.add('d-none');
            document.getElementById('studentsTableBody').closest('.table-responsive').classList.remove('d-none');
        }

        function showStudentsEmptyState() {
            document.getElementById('studentsEmpty').classList.remove('d-none');
            document.getElementById('studentsTableBody').closest('.table-responsive').classList.add('d-none');
            document.getElementById('studentsPagination').classList.add('d-none');
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