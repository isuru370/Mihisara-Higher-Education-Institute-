@extends('layouts.app')

@section('title', 'Quick Images')
@section('page-title', 'Quick Images Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Quick Images</li>
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="row">
        <!-- LEFT: Upload Section -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <strong><i class="fas fa-upload me-2"></i>Upload Quick Image</strong>
                </div>
                <div class="card-body">

                    <!-- Preview Section -->
                    <div class="text-center mb-4">
                        <div class="preview-container position-relative">
                            <img id="previewImg" class="img-thumbnail rounded shadow-sm"
                                style="width: 100%; max-width: 300px; display: none;">
                            <div id="previewPlaceholder" class="text-muted p-4 border rounded">
                                <i class="fas fa-image fa-3x mb-3"></i>
                                <p class="mb-0">Image preview will appear here</p>
                            </div>
                        </div>
                    </div>

                    <!-- Camera Section -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Camera Capture</label>
                        <div id="cameraWrapper" class="border rounded p-3 bg-light" style="display: none">
                            <video id="cameraView" width="100%" autoplay muted class="rounded border"
                                style="max-height: 200px;"></video>
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn btn-success flex-fill" id="captureBtn">
                                    <i class="fas fa-camera me-2"></i>Capture
                                </button>
                                <button class="btn btn-secondary" id="closeCameraBtn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary w-100" id="openCameraBtn">
                            <i class="fas fa-camera me-2"></i>Enable Camera
                        </button>
                        <p id="cameraError" class="text-danger mt-2 small" style="display: none"></p>
                    </div>

                    <!-- File Upload Section -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Upload from Device</label>
                        <div class="file-upload-area border rounded p-4 text-center bg-light">
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-2">Click to browse or drag & drop</p>
                            <input type="file" id="imageInput" accept="image/*" class="d-none">
                            <button class="btn btn-outline-secondary btn-sm"
                                onclick="document.getElementById('imageInput').click()">
                                <i class="fas fa-folder-open me-2"></i>Browse Files
                            </button>
                        </div>
                    </div>

                    <!-- Grade Dropdown -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Select Grade</label>
                        <select id="gradeSelect" class="form-select">
                            <option value="">Loading grades...</option>
                        </select>
                    </div>

                    <!-- Save Button -->
                    <button class="btn btn-success w-100 py-2 fw-semibold" id="saveBtn" disabled>
                        <i class="fas fa-save me-2"></i>Save Quick Image
                    </button>
                </div>
            </div>
        </div>

        <!-- RIGHT: Quick Images List -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <strong><i class="fas fa-images me-2"></i>Quick Images List</strong>
                    <span class="badge bg-light text-primary" id="imageCount">0 images</span>
                </div>
                <div class="card-body">

                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Filter by Grade</label>
                            <select id="filterGrade" class="form-select">
                                <option value="">All Grades</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Search by Custom ID</label>
                            <div class="input-group">
                                <input type="text" id="searchCustomId" class="form-control"
                                    placeholder="Enter custom ID...">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Images Grid -->
                    <div id="loadingState" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading quick images...</p>
                    </div>

                    <div id="imagesContainer" class="row g-3" style="display: none;">
                        <!-- Images will be loaded here -->
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-5" style="display: none;">
                        <i class="fas fa-images fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Quick Images Found</h5>
                        <p class="text-muted">Upload your first quick image to get started.</p>
                    </div>

                    <!-- Error State -->
                    <div id="errorState" class="alert alert-danger text-center" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="errorText">Failed to load quick images</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .file-upload-area {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px dashed #dee2e6;
        }

        .file-upload-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .preview-container {
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .alert.position-fixed {
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        #imagesContainer {
            display: flex;
            flex-wrap: wrap;
        }

        .quick-image-card {
            position: relative;
        }

        .quick-image-card .custom-id-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let uploadedImgUrl = null;
        let cameraStream = null;
        let allQuickPhotos = []; // Store all photos for client-side filtering
        let currentAlert = null;

        // ================= LOAD GRADES =================
        async function loadGrades() {
            try {
                const response = await fetch('/api/grades/dropdown');
                if (!response.ok) {
                    throw new Error('Failed to fetch grades');
                }

                const res = await response.json();
                const data = res.data || res;

                const gradeSelect = document.getElementById('gradeSelect');
                const filterGrade = document.getElementById('filterGrade');

                gradeSelect.innerHTML = '<option value="">Select Grade</option>';
                filterGrade.innerHTML = '<option value="">All Grades</option>';

                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(g => {
                        gradeSelect.innerHTML += `<option value="${g.id}">Grade ${g.grade_name}</option>`;
                        filterGrade.innerHTML += `<option value="${g.id}">Grade ${g.grade_name}</option>`;
                    });
                } else {
                    gradeSelect.innerHTML = '<option value="">No grades available</option>';
                }
            } catch (e) {
                console.error('Error loading grades:', e);
                const gradeSelect = document.getElementById('gradeSelect');
                gradeSelect.innerHTML = '<option value="">Failed to load grades</option>';
            }
        }

        // ================= CAMERA FUNCTIONS =================
        document.getElementById('openCameraBtn').addEventListener('click', async () => {
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'environment'
                    }
                });

                const cameraView = document.getElementById('cameraView');
                cameraView.srcObject = cameraStream;

                document.getElementById('cameraWrapper').style.display = 'block';
                document.getElementById('openCameraBtn').style.display = 'none';
                document.getElementById('cameraError').style.display = 'none';

            } catch (e) {
                const cameraError = document.getElementById('cameraError');
                cameraError.innerText = 'Camera access denied or not available. Please check permissions.';
                cameraError.style.display = 'block';
                console.error('Camera error:', e);
            }
        });

        document.getElementById('closeCameraBtn').addEventListener('click', () => {
            closeCamera();
            document.getElementById('cameraWrapper').style.display = 'none';
            document.getElementById('openCameraBtn').style.display = 'block';
        });

        function closeCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
        }

        document.getElementById('captureBtn').addEventListener('click', () => {
            const video = document.getElementById('cameraView');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(blob => {
                const file = new File([blob], "camera_capture_" + Date.now() + ".jpg", {
                    type: "image/jpeg",
                    lastModified: Date.now()
                });
                uploadImage(file);
                closeCamera();
                document.getElementById('cameraWrapper').style.display = 'none';
                document.getElementById('openCameraBtn').style.display = 'block';
            }, "image/jpeg", 0.8);
        });

        // ================= FILE UPLOAD =================
        document.getElementById('imageInput').addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    showAlert('Please select a valid image file', 'danger');
                    return;
                }
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showAlert('Image size should be less than 5MB', 'danger');
                    return;
                }
                uploadImage(file);
            }
            // Reset input for same file upload
            e.target.value = '';
        });

        // Drag and drop functionality
        const fileUploadArea = document.querySelector('.file-upload-area');
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            fileUploadArea.style.borderColor = '#0d6efd';
            fileUploadArea.style.backgroundColor = '#e7f1ff';
        });

        fileUploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            fileUploadArea.style.borderColor = '#dee2e6';
            fileUploadArea.style.backgroundColor = '#f8f9fa';
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            fileUploadArea.style.borderColor = '#dee2e6';
            fileUploadArea.style.backgroundColor = '#f8f9fa';

            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                if (file.size > 5 * 1024 * 1024) {
                    showAlert('Image size should be less than 5MB', 'danger');
                    return;
                }
                uploadImage(file);
            } else {
                showAlert('Please drop a valid image file', 'warning');
            }
        });

        async function uploadImage(file) {
            try {
                showAlert('Uploading image...', 'info');

                const fd = new FormData();
                fd.append('image', file);

                const res = await fetch('/api/image-upload/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: fd
                });

                const data = await res.json();
                console.log('Upload response:', data);

                if (data.status === 'success') {
                    // FIX: Use image_url directly from response
                    uploadedImgUrl = data.image_url;

                    const preview = document.getElementById('previewImg');
                    const placeholder = document.getElementById('previewPlaceholder');

                    // Add cache busting to prevent cached images
                    const cacheBuster = '?t=' + Date.now();
                    preview.src = uploadedImgUrl + cacheBuster;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';

                    // Handle image load error
                    preview.onerror = function () {
                        console.error('Failed to load image:', uploadedImgUrl);
                        showAlert('Failed to load image preview. The image may have been moved or deleted.', 'warning');
                        preview.style.display = 'none';
                        placeholder.style.display = 'block';
                        uploadedImgUrl = null;
                        document.getElementById('saveBtn').disabled = true;
                    };

                    // Enable save button if grade is selected
                    const gradeSelect = document.getElementById('gradeSelect');
                    if (gradeSelect.value) {
                        document.getElementById('saveBtn').disabled = false;
                    }

                    showAlert('Image uploaded successfully!', 'success');
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            } catch (e) {
                console.error('Upload error:', e);
                showAlert('Failed to upload image: ' + e.message, 'danger');
            }
        }

        // ================= SAVE QUICK IMAGE =================
        document.getElementById('saveBtn').addEventListener('click', async () => {
            if (!uploadedImgUrl) {
                showAlert("Please upload an image first", 'warning');
                return;
            }

            const grade = document.getElementById('gradeSelect').value;
            if (!grade) {
                showAlert("Please select a grade", 'warning');
                return;
            }

            try {
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

                const res = await fetch('/api/quick-photos', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        quick_img: uploadedImgUrl,
                        grade_id: parseInt(grade)
                    })
                });

                const data = await res.json();
                console.log('Save response:', data);

                if (data.status === 'success' || res.ok) {
                    // Reset form
                    uploadedImgUrl = null;
                    document.getElementById('previewImg').style.display = 'none';
                    document.getElementById('previewPlaceholder').style.display = 'block';
                    document.getElementById('gradeSelect').value = '';
                    document.getElementById('saveBtn').disabled = true;

                    showAlert('Quick image saved successfully!', 'success');
                    loadQuickPhotos();
                } else {
                    throw new Error(data.message || 'Save failed');
                }
            } catch (e) {
                console.error('Save error:', e);
                showAlert('Failed to save quick image: ' + e.message, 'danger');
            } finally {
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Quick Image';
            }
        });

        // ================= LOAD QUICK PHOTOS =================
        async function loadQuickPhotos() {
            try {
                showLoadingState();

                const res = await fetch('/api/quick-photos/active');
                if (!res.ok) {
                    throw new Error('Failed to fetch quick photos');
                }

                const dataRes = await res.json();
                allQuickPhotos = dataRes.data || dataRes;

                // Apply filters and render
                applyFiltersAndRender();

            } catch (e) {
                console.error('Load quick photos error:', e);
                showErrorState('Failed to load quick images: ' + e.message);
            }
        }

        // ================= FILTER AND SEARCH FUNCTIONALITY =================
        function applyFiltersAndRender() {
            const gradeFilter = document.getElementById('filterGrade').value;
            const searchTerm = document.getElementById('searchCustomId').value.toLowerCase().trim();

            const filtered = allQuickPhotos.filter(item => {
                const gradeMatch = gradeFilter ? item.grade?.id == gradeFilter : true;

                // Search by custom_id - if search term exists, check if custom_id contains it
                const customIdMatch = searchTerm ?
                    (item.custom_id && item.custom_id.toLowerCase().includes(searchTerm)) :
                    true;

                return gradeMatch && customIdMatch;
            });

            renderQuickPhotos(filtered);
        }

        function renderQuickPhotos(photos) {
            const container = document.getElementById('imagesContainer');
            container.innerHTML = '';

            // Update image count
            document.getElementById('imageCount').textContent = `${photos.length} images`;

            if (photos.length === 0) {
                showEmptyState();
                return;
            }

            photos.forEach(item => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';

                // FIX: Use image URL directly from API response
                let imageUrl = item.quick_img || item.image_url || '';

                if (!imageUrl) {
                    console.warn('No image URL for item:', item);
                    return; // Skip if no image URL
                }

                // Add cache busting
                const cacheBuster = '?t=' + Date.now();
                const fullImageUrl = imageUrl + cacheBuster;

                col.innerHTML = `
                        <div class="card h-100 shadow-sm quick-image-card">
                            <img src="${fullImageUrl}" class="card-img-top" 
                                 style="height: 200px; object-fit: cover; width: 100%;" 
                                 alt="Quick image for ${item.grade?.grade_name || 'Unknown'}"
                                 onerror="this.src='https://via.placeholder.com/300x200?text=Image+Not+Found'">
                            ${item.custom_id ? `
                                <div class="custom-id-badge">
                                    <small>ID: ${item.custom_id}</small>
                                </div>
                            ` : ''}
                            <div class="card-body">
                                <h6 class="card-title">Grade ${item.grade?.grade_name || 'N/A'}</h6>
                                <span class="badge ${item.is_active ? 'bg-success' : 'bg-secondary'}">
                                    ${item.is_active ? 'Active' : 'Inactive'}
                                </span>
                                ${item.custom_id ? `
                                    <div class="mt-2">
                                        <small class="text-muted">Custom ID: ${item.custom_id}</small>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                container.appendChild(col);
            });

            showContentState();
        }

        // ================= SEARCH AND FILTER EVENT LISTENERS =================
        document.getElementById('filterGrade').addEventListener('change', applyFiltersAndRender);

        // Search with debounce
        let searchTimeout;
        document.getElementById('searchCustomId').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFiltersAndRender();
            }, 300);
        });

        // Clear search
        document.getElementById('clearSearch').addEventListener('click', () => {
            document.getElementById('searchCustomId').value = '';
            applyFiltersAndRender();
        });

        // ================= UI STATE MANAGEMENT =================
        function showLoadingState() {
            document.getElementById('loadingState').style.display = 'block';
            document.getElementById('imagesContainer').style.display = 'none';
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('errorState').style.display = 'none';
        }

        function showContentState() {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('imagesContainer').style.display = 'flex';
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('errorState').style.display = 'none';
        }

        function showEmptyState() {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('imagesContainer').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
            document.getElementById('errorState').style.display = 'none';
        }

        function showErrorState(message) {
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('imagesContainer').style.display = 'none';
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
            document.getElementById('errorText').textContent = message;
        }

        function showAlert(message, type) {
            // Remove existing alert
            if (currentAlert) {
                currentAlert.remove();
            }

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';

            const icon = type === 'success' ? 'fa-check-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' :
                    type === 'info' ? 'fa-info-circle' : 'fa-times-circle';

            alertDiv.innerHTML = `
                    <div class="d-flex align-items-start">
                        <i class="fas ${icon} fa-lg me-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <strong>${type === 'success' ? 'Success!' : type === 'warning' ? 'Warning!' : type === 'info' ? 'Info:' : 'Error!'}</strong> 
                            <span>${message}</span>
                        </div>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
                    </div>
                `;

            document.body.appendChild(alertDiv);
            currentAlert = alertDiv;

            // Auto remove after delay
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                    currentAlert = null;
                }
            }, type === 'success' ? 3000 : 5000);
        }

        // ================= INITIALIZATION =================
        document.addEventListener('DOMContentLoaded', function () {
            loadGrades();
            loadQuickPhotos();
        });

        // Enable save button when grade is selected
        document.getElementById('gradeSelect').addEventListener('change', function () {
            if (uploadedImgUrl && this.value) {
                document.getElementById('saveBtn').disabled = false;
            }
        });
    </script>
@endpush