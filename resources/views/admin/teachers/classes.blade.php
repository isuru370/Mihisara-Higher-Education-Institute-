@extends('layouts.app')

@section('title', 'Teacher Classes')
@section('page-title', 'Teacher Classes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">Teachers</a></li>
    <li class="breadcrumb-item active">Teacher Classes</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h3 mb-1">Teacher Classes</h2>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-light text-dark fs-6" id="teacherName">Loading...</span>
                            <span class="badge bg-secondary fs-6" id="teacherId">ID: Loading...</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="loadTeacherClasses()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <a href="{{ route('class_rooms.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>New Class
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title fs-2 fw-bold mb-1" id="totalClasses">0</h4>
                                <p class="card-text mb-0">Total Classes</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-layer-group fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title fs-2 fw-bold mb-1" id="activeClasses">0</h4>
                                <p class="card-text mb-0">Active Classes</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-play-circle fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title fs-2 fw-bold mb-1" id="ongoingClasses">0</h4>
                                <p class="card-text mb-0">Ongoing</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-running fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title fs-2 fw-bold mb-1" id="subjectsCount">0</h4>
                                <p class="card-text mb-0">Subjects</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-book fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control border-start-0"
                                placeholder="Search classes by name, subject, or grade...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes Grid -->
        <div class="row">
            <div class="col-12">
                <!-- Loading State -->
                <div id="loadingState" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <h4 class="text-muted">Loading Classes</h4>
                    <p class="text-muted">Please wait while we fetch the class information</p>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5" style="display: none;">
                    <div class="mb-4">
                        <i class="fas fa-chalkboard-teacher fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No Classes Assigned</h4>
                    <p class="text-muted mb-4">This teacher doesn't have any classes assigned yet</p>
                    <a href="{{ route('class_rooms.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Assign First Class
                    </a>
                </div>

                <!-- Error State -->
                <div id="errorState" class="text-center py-5" style="display: none;">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
                    </div>
                    <h4 class="text-danger">Unable to Load Classes</h4>
                    <p class="text-muted mb-4">There was an error loading the classes</p>
                    <button class="btn btn-outline-primary" onclick="loadTeacherClasses()">
                        <i class="fas fa-redo me-2"></i>Try Again
                    </button>
                </div>

                <!-- Classes Grid -->
                <div id="classesGrid" class="row g-3" style="display: none;">
                    <!-- Classes will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    const teacherId = {{ $id }};
    let allClasses = [];

    document.addEventListener('DOMContentLoaded', function () {
        loadTeacherClasses();

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');

        searchInput.addEventListener('input', function () {
            filterClasses(this.value);
        });

        clearSearch.addEventListener('click', function () {
            searchInput.value = '';
            filterClasses('');
        });
    });

    function loadTeacherClasses() {
        showLoadingState();

        fetch(`/api/class-rooms/teacher/${teacherId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);

                // Check if the response has the expected structure
                if (data.status === 'success' && data.data && data.data.length > 0) {
                    // Response structure: {status: "success", data: [...]}
                    allClasses = data.data;
                    displayClasses(allClasses);
                    updateStats(allClasses);
                    updateTeacherInfo(allClasses[0]?.teacher);
                    showClassesGrid();
                } else if (data.status === 'error' || !data.data || data.data.length === 0) {
                    // No classes found or error response
                    showEmptyState();
                    updateTeacherInfoFromAPI();
                } else {
                    // Fallback for unexpected response structure
                    showEmptyState();
                    updateTeacherInfoFromAPI();
                }
            })
            .catch(error => {
                console.error('Error loading classes:', error);
                showErrorState();
                updateTeacherInfoFromAPI();
            });
    }

    function updateTeacherInfoFromAPI() {
        fetch(`/api/teachers/${teacherId}`)
            .then(response => {
                if (!response.ok) throw new Error('Teacher not found');
                return response.json(); // FIXED: removed line break
            })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('teacherName').textContent =
                        `${data.data.fname} ${data.data.lname}`;
                    document.getElementById('teacherId').textContent =
                        `ID: ${data.data.custom_id}`;
                }
            })
            .catch(error => {
                console.error('Error loading teacher info:', error);
                document.getElementById('teacherName').textContent = 'Teacher Not Found';
            });
    }

    function displayClasses(classes) {
        const classesGrid = document.getElementById('classesGrid');

        if (!classes || classes.length === 0) {
            classesGrid.innerHTML = '';
            return;
        }

        classesGrid.innerHTML = classes.map(classRoom => {
            // Safely handle potentially undefined properties
            const className = classRoom.class_name || 'Unnamed Class';
            const subjectName = classRoom.subject?.subject_name || 'N/A';
            const gradeName = classRoom.grade?.grade_name || 'N/A';
            const createdDate = classRoom.created_at ?
                new Date(classRoom.created_at).toLocaleDateString() :
                'N/A';

            return `
            <div class="col-xl-4 col-lg-6 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-0 text-dark">${className}</h5>
                            <div class="d-flex gap-1">
                                <span class="badge ${classRoom.is_active ? 'bg-success' : 'bg-secondary'}">
                                    ${classRoom.is_active ? 'Active' : 'Inactive'}
                                </span>
                                ${classRoom.is_ongoing ? '<span class="badge bg-info">Ongoing</span>' : ''}
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-book text-primary me-2"></i>
                                    <div>
                                        <small class="text-muted">Subject</small>
                                        <p class="mb-0 fw-semibold">${subjectName}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-graduation-cap text-warning me-2"></i>
                                    <div>
                                        <small class="text-muted">Grade</small>
                                        <p class="mb-0 fw-semibold">${gradeName}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar text-success me-2"></i>
                                    <div>
                                        <small class="text-muted">Created</small>
                                        <p class="mb-0 fw-semibold">${createdDate}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex">
                            <a href="/teachers/view_student/${classRoom.id}" class="btn btn-outline-warning btn-sm flex-fill">
                                <i class="fas fa-users me-1"></i> View Students
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        }).join('');
    }

    function filterClasses(searchTerm) {
        const filteredClasses = allClasses.filter(classRoom => {
            const searchLower = searchTerm.toLowerCase();
            return (
                classRoom.class_name.toLowerCase().includes(searchLower) ||
                (classRoom.subject?.subject_name || '').toLowerCase().includes(searchLower) ||
                (classRoom.grade?.grade_name || '').toLowerCase().includes(searchLower)
            );
        });

        displayClasses(filteredClasses);
        updateStats(filteredClasses);
    }

    function updateStats(classes) {
        const totalClasses = classes.length;
        const activeClasses = classes.filter(c => c.is_active).length;
        const ongoingClasses = classes.filter(c => c.is_ongoing).length;
        const subjectsCount = new Set(classes.map(c => c.subject?.subject_name).filter(Boolean)).size;

        document.getElementById('totalClasses').textContent = totalClasses;
        document.getElementById('activeClasses').textContent = activeClasses;
        document.getElementById('ongoingClasses').textContent = ongoingClasses;
        document.getElementById('subjectsCount').textContent = subjectsCount;
    }

    function updateTeacherInfo(teacher) {
        if (teacher) {
            document.getElementById('teacherName').textContent = `${teacher.fname} ${teacher.lname}`;
            document.getElementById('teacherId').textContent = `ID: ${teacher.custom_id}`;
        }
    }

    function showLoadingState() {
        document.getElementById('loadingState').style.display = 'block';
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('errorState').style.display = 'none';
        document.getElementById('classesGrid').style.display = 'none';
    }

    function showClassesGrid() {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('errorState').style.display = 'none';
        document.getElementById('classesGrid').style.display = 'flex';
    }

    function showEmptyState() {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('errorState').style.display = 'none';
        document.getElementById('classesGrid').style.display = 'none';
    }

    function showErrorState() {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('errorState').style.display = 'block';
        document.getElementById('classesGrid').style.display = 'none';
    }
</script>