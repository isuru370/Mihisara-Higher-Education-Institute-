@extends('layouts.app')

@section('title', 'Student Images')
@section('page-title', 'Student Images')

@push('styles')
    <style>
        .student-images-page {
            animation: fadeIn .4s ease;
        }

        /* Hero Card */
        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 32px;
            padding: 2rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8, #1e40af);
            box-shadow: 0 20px 45px rgba(37, 99, 235, .25);
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
            top: -100px;
            right: -80px;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
            bottom: -60px;
            left: -40px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .7rem 1rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .08);
            backdrop-filter: blur(10px);
            font-size: .85rem;
            font-weight: 600;
        }

        .text-light-soft {
            color: rgba(255, 255, 255, .78);
        }

        /* Stats Cards */
        .stats-row {
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 1.25rem;
            border: 1px solid #eef2f7;
            transition: all 0.2s ease;
            height: 100%;
        }

        .stat-card:hover {
            border-color: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.08);
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0;
        }

        .stat-value.text-primary {
            color: #2563eb;
        }

        .stat-value.text-success {
            color: #10b981;
        }

        .stat-value.text-warning {
            color: #f59e0b;
        }

        /* Filter Card */
        .filter-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .filter-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .filter-input {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .filter-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .btn-search {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
        }

        .btn-clear {
            background: #64748b;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-clear:hover {
            background: #475569;
            transform: translateY(-2px);
            color: white;
        }

        /* Table Card */
        .table-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
        }

        .table-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .table-title i {
            color: #2563eb;
            margin-right: 0.5rem;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table thead th {
            background: #f8fafc;
            padding: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .student-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .student-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Student Image */
        .student-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .student-img:hover {
            transform: scale(1.05);
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        /* Badges */
        .badge-assigned {
            background: #dcfce7;
            color: #166534;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-not-assigned {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Buttons */
        .btn-assign {
            background: #ef4444;
            border: none;
            border-radius: 10px;
            padding: 0.4rem 0.9rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-assign:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
            color: #64748b;
        }

        /* Modal Styles */
        .modal-content-custom {
            border-radius: 24px;
            border: none;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1);
        }

        .modal-header-custom {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
            padding: 1.25rem 1.5rem;
        }

        .modal-title-custom {
            font-weight: 800;
            color: #0f172a;
        }

        .modal-body-custom {
            padding: 1.5rem;
        }

        .modal-footer-custom {
            border-top: 1px solid #eef2f7;
            padding: 1rem 1.5rem;
        }

        .photo-preview {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .photo-preview-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 16px;
            border: 3px solid #2563eb;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        /* Pagination */
        .pagination-container {
            padding: 1rem 1.5rem;
            border-top: 1px solid #eef2f7;
        }

        .pagination {
            margin: 0;
            gap: 0.25rem;
        }

        .pagination .page-link {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .pagination .page-link:hover {
            background: #eff6ff;
            border-color: #2563eb;
            color: #2563eb;
        }

        .pagination .active .page-link {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-color: #2563eb;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-card {
                padding: 1.5rem;
            }

            .stat-value {
                font-size: 1.1rem;
            }

            .student-table thead th {
                font-size: 0.65rem;
                padding: 0.75rem;
            }

            .student-table tbody td {
                padding: 0.75rem;
                font-size: 0.75rem;
            }

            .student-img {
                width: 45px;
                height: 45px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="student-images-page">

        {{-- HERO CARD --}}
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="mb-3">
                        <span class="hero-badge">
                            <i class="bi bi-images"></i>
                            Student Images Gallery
                        </span>
                    </div>
                    <h2 class="fw-bold mb-2">Student Image Management</h2>
                    <p class="mb-0 text-light-soft">
                        Manage and assign student images for identification cards and records.
                    </p>
                </div>
                <div class="text-end">
                    <div class="hero-badge mb-2">
                        <i class="bi bi-calendar-event-fill"></i>
                        {{ now()->format('d M Y') }}
                    </div>
                    <div class="hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS CARDS --}}
        @php
            $totalImages = $quickPhotos->count();
            $assignedImages = $quickPhotos->where('is_active', 0)->count();
            $pendingImages = $quickPhotos->where('is_active', 1)->count();
        @endphp

        <div class="stats-row">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Total Images</div>
                        <div class="stat-value text-primary">{{ $totalImages }}</div>
                        <small class="text-muted">All student images</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Assigned Images</div>
                        <div class="stat-value text-success">{{ $assignedImages }}</div>
                        <small class="text-muted">Linked to students</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Pending Assignment</div>
                        <div class="stat-value text-warning">{{ $pendingImages }}</div>
                        <small class="text-muted">Awaiting student link</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER CARD --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.student-images.index') }}" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <div class="filter-label">
                        <i class="bi bi-search me-1"></i> Search Student Images
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="filter-input"
                        placeholder="Search by name, mobile number, or QR code...">
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-search w-100">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        <a href="{{ route('admin.student-images.index') }}" class="btn-clear w-100">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- TABLE CARD --}}
        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title">
                    <i class="bi bi-images"></i> Student Images List
                </h5>
            </div>

            <div class="table-responsive">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 10%;">Image</th>
                            <th style="width: 15%;">Photo ID</th>
                            <th style="width: 20%;">Student Name</th>
                            <th style="width: 15%;">Guardian Mobile</th>
                            <th style="width: 20%;">QR Code</th>
                            <th style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quickPhotos as $key => $photo)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <img src="{{ asset('storage/' . $photo->image_path) }}" alt="Student Image"
                                        class="student-img" onerror="this.src='{{ asset('storage/uploads/male.png') }}'">
                                </td>

                                <td>
                                    <code class="bg-light px-2 py-1 rounded">{{ $photo->custom_id }}</code>
                                </td>

                                <td>
                                    @if($photo->is_active == 0)
                                        <span class="fw-semibold">{{ $photo->initial_name ?? 'N/A' }}</span>
                                    @else
                                        <span class="text-muted">Not Assigned</span>
                                    @endif
                                </td>

                                <td>
                                    @if($photo->is_active == 0)
                                        {{ $photo->guardian_mobile ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if($photo->is_active == 0)
                                        <code class="bg-light px-2 py-1 rounded">{{ $photo->student_qr ?? 'N/A' }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if($photo->is_active == 0)
                                        <span class="badge-assigned">
                                            <i class="bi bi-check-circle me-1"></i> Assigned
                                        </span>
                                    @else
                                        <button type="button" class="btn-assign assign-btn" data-bs-toggle="modal"
                                            data-bs-target="#assignModal" data-photo-id="{{ $photo->id }}"
                                            data-photo-code="{{ $photo->custom_id }}"
                                            data-photo-url="{{ asset('storage/' . $photo->image_path) }}">
                                            <i class="bi bi-link me-1"></i> Assign
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h5>No Records Found</h5>
                                    <p class="text-muted">No student images available in the system.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($quickPhotos, 'links') && $quickPhotos->hasPages())
                <div class="pagination-container">
                    {{ $quickPhotos->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ASSIGN MODAL --}}
    <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <form method="POST" id="assignForm" action="">
                    @csrf

                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title modal-title-custom" id="assignModalLabel">
                            <i class="bi bi-person-badge-fill me-2"></i>Assign Image to Student
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body modal-body-custom">
                        <div class="photo-preview">
                            <img id="modalPhotoPreview" src="{{ asset('storage/logo/black_logo.png') }}" alt="Photo Preview"
                                class="photo-preview-img">
                            <div class="mt-2">
                                <code id="modalPhotoCode" class="bg-light px-2 py-1 rounded">-</code>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-qr-code me-1"></i> Student QR Code
                            </label>
                            <input type="text" name="qr_code" value="{{ old('qr_code') }}"
                                class="form-control form-control-lg @error('qr_code') is-invalid @enderror"
                                placeholder="Enter student custom_id or temporary QR code" style="border-radius: 12px;"
                                required>
                            @error('qr_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-info-circle"></i>
                                Enter the student's custom ID or temporary QR code from their ID card.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer modal-footer-custom">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Assign Image
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const assignButtons = document.querySelectorAll('.assign-btn');
            const assignForm = document.getElementById('assignForm');
            const modalPhotoCode = document.getElementById('modalPhotoCode');
            const modalPhotoPreview = document.getElementById('modalPhotoPreview');

            assignButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const photoId = this.getAttribute('data-photo-id');
                    const photoCode = this.getAttribute('data-photo-code');
                    const photoUrl = this.getAttribute('data-photo-url');

                    assignForm.action = `/admin/student-images/${photoId}/assign`;
                    modalPhotoCode.textContent = photoCode;
                    if (photoUrl) {
                        modalPhotoPreview.src = photoUrl;
                    }
                });
            });

            // Auto-show modal if there are validation errors
            @if($errors->has('qr_code'))
                const assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
                assignModal.show();
            @endif

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
@endpush