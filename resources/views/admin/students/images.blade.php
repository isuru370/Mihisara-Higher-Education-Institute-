@extends('layouts.app')

@section('title', 'Student Images')
@section('page-title', 'Student Images')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
    <li class="breadcrumb-item active">Student Images</li>
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div>
                        <strong><i class="fas fa-images me-2"></i>Student Images Management</strong>
                        <span class="badge bg-light text-primary ms-2">{{ $students->total() }} Students</span>
                    </div>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#uploadImageModal">
                        <i class="fas fa-camera me-1"></i>Update Image
                    </button>
                </div>
                
                <div class="card-body">
                    <!-- Search Form -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('students.images') }}" class="d-flex">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Search by ID, first name, or last name..."
                                           value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('students.images') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Student Images Grid -->
                    @if($students->count() > 0)
                        <div class="row">
                            @foreach($students as $student)
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                    <div class="card student-card h-100 shadow-sm">
                                        <div class="card-body text-center p-3">
                                            <!-- Status Badge -->
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <span class="badge {{ $student->img_url ? 'bg-success' : 'bg-warning' }}">
                                                    <i class="fas {{ $student->img_url ? 'fa-check' : 'fa-times' }} me-1"></i>
                                                    {{ $student->img_url ? 'Has Image' : 'No Image' }}
                                                </span>
                                            </div>
                                            
                                            <!-- Student Image -->
                                            <div class="mb-3">
                                                @php
                                                    $imageUrl = $student->img_url ?? '/uploads/logo/logo.png';
                                                    if ($student->img_url && !str_starts_with($student->img_url, 'http')) {
                                                        if (str_starts_with($student->img_url, 'uploads/')) {
                                                            $imageUrl = asset($student->img_url);
                                                        } else {
                                                            $imageUrl = asset('uploads/' . $student->img_url);
                                                        }
                                                    }
                                                @endphp
                                                <img src="{{ $imageUrl }}" 
                                                     class="rounded-circle border"
                                                     style="width: 120px; height: 120px; object-fit: cover;"
                                                     onerror="this.src='/uploads/logo/logo.png'"
                                                     alt="{{ $student->fname }} {{ $student->lname }}">
                                            </div>
                                            
                                            <!-- Student Info -->
                                            <h6 class="card-title mb-1">{{ $student->fname }} {{ $student->lname }}</h6>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-id-card me-1"></i> {{ $student->custom_id }}
                                            </p>
                                            @if($student->grade)
                                                <p class="text-muted small mb-3">
                                                    <i class="fas fa-graduation-cap me-1"></i> {{ $student->grade->grade_name }}
                                                </p>
                                            @endif
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-grid gap-2 mt-3">
                                                <button class="btn btn-outline-primary btn-sm" 
                                                        onclick="openUpdateModal('{{ $student->custom_id }}', '{{ $student->fname }} {{ $student->lname }}', '{{ $student->img_url ?? '' }}')">
                                                    <i class="fas fa-camera me-1"></i> Update Image
                                                </button>
                                                <button class="btn btn-outline-info btn-sm" 
                                                        onclick="viewStudentImage('{{ $student->custom_id }}', '{{ $student->fname }} {{ $student->lname }}', '{{ $student->img_url ?? '' }}')">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination with Bootstrap Styling -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <!-- Bootstrap Pagination -->
                                @if($students->hasPages())
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mb-0">
                                        {{-- Previous Page Link --}}
                                        @if($students->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="fas fa-chevron-left"></i>
                                                </span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $students->previousPageUrl() }}" rel="prev">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @php
                                            $current = $students->currentPage();
                                            $last = $students->lastPage();
                                            $start = max(1, $current - 2);
                                            $end = min($last, $current + 2);
                                            
                                            if ($current <= 3) {
                                                $end = min(5, $last);
                                            }
                                            if ($current >= $last - 2) {
                                                $start = max(1, $last - 4);
                                            }
                                        @endphp

                                        {{-- First Page Link --}}
                                        @if($start > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $students->url(1) }}">1</a>
                                            </li>
                                            @if($start > 2)
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            @endif
                                        @endif

                                        {{-- Page Number Links --}}
                                        @for($i = $start; $i <= $end; $i++)
                                            @if($i == $current)
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $i }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $students->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        {{-- Last Page Link --}}
                                        @if($end < $last)
                                            @if($end < $last - 1)
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            @endif
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $students->url($last) }}">{{ $last }}</a>
                                            </li>
                                        @endif

                                        {{-- Next Page Link --}}
                                        @if($students->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $students->nextPageUrl() }}" rel="next">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="fas fa-chevron-right"></i>
                                                </span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                                @endif
                                
                                <div class="text-center text-muted mt-3">
                                    <small>
                                        Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} 
                                        of {{ $students->total() }} entries
                                        @if(request('search'))
                                            <span class="ms-2">(Filtered)</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- No Students Found -->
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Students Found</h4>
                            @if(request('search'))
                                <p class="text-muted">No students match your search criteria</p>
                                <a href="{{ route('students.images') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to All Students
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Image Modal -->
    <div class="modal fade" id="uploadImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-camera me-2"></i>Update Student Image
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Student Selection -->
                    <div class="mb-4">
                        <label class="form-label">Select Student <span class="text-danger">*</span></label>
                        <select class="form-select" id="studentSelect" name="student_id" required>
                            <option value="">Choose a student...</option>
                            @foreach($allStudents as $student)
                                <option value="{{ $student->custom_id }}" data-img="{{ $student->img_url ?? '' }}">
                                    {{ $student->custom_id }} - {{ $student->fname }} {{ $student->lname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Current Image Preview -->
                    <div class="mb-4" id="currentImageSection" style="display: none;">
                        <label class="form-label">Current Image</label>
                        <div class="text-center">
                            <img id="currentStudentImage" class="img-thumbnail rounded"
                                style="width: 150px; height: 150px; object-fit: cover;"
                                onerror="this.src='/uploads/logo/logo.png'">
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <strong>Select New Image Method</strong>
                        </div>
                        <div class="card-body">
                            <!-- New Image Preview -->
                            <div class="text-center mb-3">
                                <img id="imagePreview" class="img-thumbnail rounded"
                                    style="width: 200px; height: 200px; object-fit: cover; display: none;"
                                    onerror="this.src='/uploads/logo/logo.png'">
                                <div id="imagePlaceholder" class="text-muted p-4 border rounded">
                                    <i class="fas fa-user fa-3x mb-3"></i>
                                    <p class="mb-0">New image will appear here</p>
                                </div>
                            </div>

                            <!-- Image Upload Tabs -->
                            <ul class="nav nav-tabs" id="imageUploadTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="camera-tab" data-bs-toggle="tab"
                                        data-bs-target="#camera" type="button" role="tab">
                                        <i class="fas fa-camera me-1"></i>Camera
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="upload-tab" data-bs-toggle="tab"
                                        data-bs-target="#upload" type="button" role="tab">
                                        <i class="fas fa-upload me-1"></i>Browse
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="quick-image-tab" data-bs-toggle="tab"
                                        data-bs-target="#quick-image" type="button" role="tab">
                                        <i class="fas fa-bolt me-1"></i>Quick Image
                                    </button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content p-3 border border-top-0" id="imageUploadTabsContent">
                                <!-- Camera Tab -->
                                <div class="tab-pane fade show active" id="camera" role="tabpanel">
                                    <div id="cameraWrapper" style="display: none">
                                        <video id="cameraView" width="100%" autoplay muted class="rounded border"
                                            style="max-height: 200px;"></video>
                                        <div class="d-flex gap-2 mt-2">
                                            <button class="btn btn-success flex-fill" type="button" id="captureBtn">
                                                <i class="fas fa-camera me-2"></i>Capture
                                            </button>
                                            <button class="btn btn-secondary" type="button" id="closeCameraBtn">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-primary w-100" type="button" id="openCameraBtn">
                                        <i class="fas fa-camera me-2"></i>Enable Camera
                                    </button>
                                    <p id="cameraError" class="text-danger mt-2 small" style="display: none"></p>
                                </div>

                                <!-- File Upload Tab -->
                                <div class="tab-pane fade" id="upload" role="tabpanel">
                                    <div class="file-upload-area border rounded p-3 text-center bg-light"
                                         onclick="document.getElementById('fileInput').click()"
                                         style="cursor: pointer;">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-3"></i>
                                        <p class="text-muted mb-2">Click to browse or drag & drop</p>
                                        <p class="text-muted small">Maximum file size: 5MB</p>
                                        <input type="file" id="fileInput" accept="image/*" class="d-none">
                                    </div>
                                </div>

                                <!-- Quick Image Tab -->
                                <div class="tab-pane fade" id="quick-image" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">Search Quick Image by Custom ID</label>
                                        <div class="input-group">
                                            <input type="text" id="quickImageSearch" class="form-control"
                                                placeholder="Enter custom ID...">
                                            <button class="btn btn-outline-primary" type="button" id="searchQuickImage">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="quickImageResults" class="mt-3">
                                        <p class="text-muted text-center">Search for quick images above</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Image Info -->
                            <div id="selectedImageInfo" class="mt-3 p-2 bg-light rounded" style="display: none">
                                <small class="text-muted" id="imageSource"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="uploadImageBtn" disabled>
                        <i class="fas fa-save me-2"></i>Update Image
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Image Modal -->
    <div class="modal fade" id="viewImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="viewImageTitle">
                        <i class="fas fa-image me-2"></i>Student Image
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 text-center mb-3">
                            <h6>Current Image</h6>
                            <img id="viewImage" src="" class="img-fluid rounded border" 
                                 style="max-height: 300px; object-fit: cover;"
                                 onerror="this.src='/uploads/logo/logo.png'">
                        </div>
                        <div class="col-md-6">
                            <h6>Student Details</h6>
                            <div class="mb-3">
                                <strong>Student ID:</strong> 
                                <span id="viewStudentId" class="badge bg-primary ms-2"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Name:</strong> 
                                <span id="viewStudentName" class="fw-bold ms-2"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Image Status:</strong> 
                                <span id="viewImageStatus" class="badge ms-2"></span>
                            </div>
                            <div class="mt-4">
                                <button type="button" class="btn btn-warning btn-sm w-100"
                                        onclick="updateCurrentStudentImage()">
                                    <i class="fas fa-camera me-2"></i>Update This Student's Image
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .student-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
            border: 1px solid #e9ecef;
            height: 100%;
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-color: #0d6efd;
        }

        .file-upload-area {
            transition: all 0.3s ease;
            border: 2px dashed #dee2e6;
        }

        .file-upload-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .quick-image-item {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .quick-image-item:hover {
            border-color: #0d6efd;
            transform: scale(1.02);
        }

        .quick-image-item.selected {
            border-color: #198754;
            background-color: #f8fff9;
        }

        .student-card img {
            border: 3px solid #f8f9fa;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Bootstrap Pagination Custom Styles */
        .pagination {
            margin-bottom: 0;
        }

        .page-item .page-link {
            color: #495057;
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 0.375rem 0.75rem;
            margin: 0 2px;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }

        .page-item .page-link:hover {
            color: #0d6efd;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        .page-item.active .page-link {
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            opacity: 0.6;
        }

        .page-link i {
            font-size: 0.875rem;
        }
    </style>
@endpush

@push('scripts')
<script>
    // Global variables
    let selectedStudentId = '';
    let selectedImageUrl = '';
    let cameraStream = null;
    let selectedQuickImageId = null;
    let currentViewStudentId = '';
    let currentViewStudentName = '';
    let currentViewImageUrl = '';

    // Open update modal for specific student
    function openUpdateModal(customId, name, currentImageUrl) {
        // Set selected student
        selectedStudentId = customId;
        
        // Update student select dropdown
        const studentSelect = document.getElementById('studentSelect');
        studentSelect.value = customId;
        
        // Trigger change event to show current image
        if (studentSelect.value) {
            const event = new Event('change');
            studentSelect.dispatchEvent(event);
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('uploadImageModal'));
        modal.show();
    }

    // View student image in modal
    function viewStudentImage(customId, name, imageUrl) {
        currentViewStudentId = customId;
        currentViewStudentName = name;
        currentViewImageUrl = imageUrl;
        
        const viewImage = document.getElementById('viewImage');
        const viewStudentId = document.getElementById('viewStudentId');
        const viewStudentName = document.getElementById('viewStudentName');
        const viewImageStatus = document.getElementById('viewImageStatus');
        const viewImageTitle = document.getElementById('viewImageTitle');
        
        // Set image URL
        viewImage.src = getImageUrl(imageUrl);
        
        // Set student info
        viewStudentId.textContent = customId;
        viewStudentName.textContent = name;
        viewImageTitle.textContent = `${name}'s Image`;
        
        // Set status badge
        if (imageUrl && imageUrl !== '') {
            viewImageStatus.textContent = 'Has Image';
            viewImageStatus.className = 'badge bg-success';
        } else {
            viewImageStatus.textContent = 'No Image';
            viewImageStatus.className = 'badge bg-warning';
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('viewImageModal'));
        modal.show();
    }

    // Update current student's image from view modal
    function updateCurrentStudentImage() {
        // Close view modal
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewImageModal'));
        if (viewModal) viewModal.hide();
        
        // Open update modal
        openUpdateModal(currentViewStudentId, currentViewStudentName, currentViewImageUrl);
    }

    // Helper function to get proper image URL
    function getImageUrl(imageUrl) {
        if (!imageUrl || imageUrl === 'null' || imageUrl === '') {
            return '/uploads/logo/logo.png';
        }
        
        if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) {
            return imageUrl;
        }
        
        if (imageUrl.startsWith('uploads/')) {
            return `{{ asset('') }}${imageUrl}`;
        }
        
        return `{{ asset('uploads/') }}/${imageUrl}`;
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Student select change event
        document.getElementById('studentSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const currentImageUrl = selectedOption.getAttribute('data-img');
            const currentImageSection = document.getElementById('currentImageSection');
            const currentStudentImage = document.getElementById('currentStudentImage');
            
            selectedStudentId = this.value;
            
            // Show/hide current image
            if (currentImageUrl && currentImageUrl !== '') {
                currentStudentImage.src = getImageUrl(currentImageUrl);
                currentImageSection.style.display = 'block';
            } else {
                currentImageSection.style.display = 'none';
            }
            
            updateUploadButton();
        });
        
        // Camera functionality
        document.getElementById('openCameraBtn').addEventListener('click', openCamera);
        document.getElementById('closeCameraBtn').addEventListener('click', closeCamera);
        document.getElementById('captureBtn').addEventListener('click', captureImage);

        // File upload
        document.getElementById('fileInput').addEventListener('change', handleFileUpload);

        // Quick image search
        document.getElementById('searchQuickImage').addEventListener('click', searchQuickImages);

        // Upload image button
        document.getElementById('uploadImageBtn').addEventListener('click', updateStudentImage);
    });

    // ================= CAMERA FUNCTIONS =================
    async function openCamera() {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { width: 1280, height: 720, facingMode: 'environment' }
            });

            const cameraView = document.getElementById('cameraView');
            cameraView.srcObject = cameraStream;

            document.getElementById('cameraWrapper').style.display = 'block';
            document.getElementById('openCameraBtn').style.display = 'none';
            document.getElementById('cameraError').style.display = 'none';

        } catch (e) {
            document.getElementById('cameraError').innerText = 'Camera access denied or not available.';
            document.getElementById('cameraError').style.display = 'block';
        }
    }

    function closeCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        document.getElementById('cameraWrapper').style.display = 'none';
        document.getElementById('openCameraBtn').style.display = 'block';
    }

    function captureImage() {
        try {
            const video = document.getElementById('cameraView');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(async (blob) => {
                if (!blob) {
                    showAlert('Failed to capture image', 'danger');
                    return;
                }
                
                const file = new File([blob], "student_capture.jpg", { type: "image/jpeg" });
                const success = await uploadImage(file, 'camera');
                
                if (success) {
                    closeCamera();
                }
            }, "image/jpeg", 0.8);
        } catch (error) {
            console.error('Error capturing image:', error);
            showAlert('Failed to capture image: ' + error.message, 'danger');
        }
    }

    // ================= FILE UPLOAD =================
    function handleFileUpload(e) {
        const file = e.target.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                showAlert('Please select a valid image file', 'danger');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showAlert('Image size should be less than 5MB', 'danger');
                return;
            }
            uploadImage(file, 'file');
        }
    }

    // ================= QUICK IMAGE FUNCTIONS =================
    async function searchQuickImages() {
        const searchTerm = document.getElementById('quickImageSearch').value.trim();
        if (!searchTerm) {
            showAlert('Please enter a custom ID to search', 'warning');
            return;
        }

        try {
            const response = await fetch('/api/quick-photos/active');
            if (!response.ok) throw new Error('Failed to fetch quick images');

            const res = await response.json();
            const quickImages = res.data || res;

            const filteredImages = quickImages.filter(img =>
                img.custom_id && img.custom_id.toLowerCase().includes(searchTerm.toLowerCase())
            );

            displayQuickImages(filteredImages);
        } catch (e) {
            console.error('Error searching quick images:', e);
            showAlert('Failed to search quick images', 'danger');
        }
    }

    function displayQuickImages(images) {
        const resultsContainer = document.getElementById('quickImageResults');

        if (images.length === 0) {
            resultsContainer.innerHTML = '<p class="text-muted text-center">No quick images found</p>';
            return;
        }

        resultsContainer.innerHTML = images.map(img => {
            let imageUrl = img.quick_img;
            if (!imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
                imageUrl = '/uploads/' + imageUrl;
            }

            return `
                <div class="quick-image-item card mb-2 p-2" onclick="selectQuickImage(${img.id}, '${imageUrl}', '${img.custom_id || 'No ID'}')">
                    <div class="row g-2 align-items-center">
                        <div class="col-3">
                            <img src="${imageUrl}" class="img-fluid rounded" style="height: 60px; object-fit: cover;"
                                 onerror="this.src='/uploads/logo/logo.png'">
                        </div>
                        <div class="col-9">
                            <small class="fw-bold">ID: ${img.custom_id || 'No ID'}</small><br>
                            <small class="text-muted">Grade: ${img.grade?.grade_name || 'N/A'}</small>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function selectQuickImage(id, imageUrl, customId) {
        document.querySelectorAll('.quick-image-item').forEach(item => {
            item.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');

        selectedImageUrl = imageUrl;
        selectedQuickImageId = id;
        updateImagePreview(imageUrl, `Quick Image: ${customId}`);
        showAlert(`Quick image "${customId}" selected`, 'success');
    }

    // ================= IMAGE UPLOAD =================
    async function uploadImage(file, source) {
        try {
            showAlert('Uploading image...', 'info');

            const formData = new FormData();
            formData.append('image', file);

            const res = await fetch('/api/image-upload/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await res.json();

            if (data.status === 'success') {
                selectedImageUrl = data.image_url;
                updateImagePreview(selectedImageUrl, `Uploaded via ${source}`);
                showAlert('Image uploaded successfully!', 'success');
                return true;
            } else {
                throw new Error(data.message || 'Upload failed');
            }
        } catch (e) {
            console.error('Upload error:', e);
            showAlert('Failed to upload image: ' + e.message, 'danger');
            return false;
        }
    }

    function updateImagePreview(imageUrl, source) {
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');
        const imageInfo = document.getElementById('selectedImageInfo');

        preview.src = getImageUrl(imageUrl);
        preview.style.display = 'block';
        placeholder.style.display = 'none';
        imageInfo.style.display = 'block';
        document.getElementById('imageSource').textContent = source;

        updateUploadButton();
    }

    function updateUploadButton() {
        const uploadBtn = document.getElementById('uploadImageBtn');
        uploadBtn.disabled = !(selectedStudentId && selectedImageUrl);
    }

    // ================= UPDATE STUDENT IMAGE =================
    async function updateStudentImage() {
        if (!selectedStudentId) {
            showAlert('Please select a student first', 'warning');
            return;
        }

        if (!selectedImageUrl) {
            showAlert('Please capture or upload an image first', 'warning');
            return;
        }

        try {
            const uploadBtn = document.getElementById('uploadImageBtn');
            const originalText = uploadBtn.innerHTML;
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

            const response = await fetch(`/api/students/update_image/${selectedStudentId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    img_url: selectedImageUrl
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                // Deactivate quick image if used
                if (selectedQuickImageId) {
                    await deactivateQuickImage(selectedQuickImageId);
                }
                
                showAlert('Student image updated successfully!', 'success');

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('uploadImageModal'));
                if (modal) modal.hide();

                // Refresh page to show updated image
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(result.message || 'Update failed');
            }
        } catch (error) {
            console.error('Error updating student image:', error);
            showAlert('Failed to update student image: ' + error.message, 'danger');
        } finally {
            const uploadBtn = document.getElementById('uploadImageBtn');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = originalText;
        }
    }

    // ================= DEACTIVATE QUICK IMAGE =================
    async function deactivateQuickImage(quickImageId) {
        try {
            const response = await fetch(`/api/quick-photos/${quickImageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                console.warn('Failed to deactivate quick image, but student was updated');
            }
        } catch (e) {
            console.error('Error deactivating quick image:', e);
        }
    }

    // ================= UTILITY FUNCTIONS =================
    function showAlert(message, type) {
        // Remove existing alerts
        document.querySelectorAll('.alert:not(.alert-info)').forEach(alert => {
            alert.remove();
        });

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.querySelector('.card-body').insertBefore(alertDiv, document.querySelector('.card-body').firstChild);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
</script>
@endpush