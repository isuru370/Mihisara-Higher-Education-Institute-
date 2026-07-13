@extends('layouts.app')

@section('title', 'Student Classes - ' . $student->full_name)
@section('page-title', 'Student Classes')

@section('content')

    <div class="student-class-show-page">

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- STUDENT INFO CARD -->
        <div class="student-info-card mb-4">

            <div class="student-avatar">
                @if ($student->img_url)
                    <img src="{{ asset('storage/' . $student->img_url) }}" alt="{{ $student->full_name }}">
                @else
                    <div class="avatar-placeholder">
                        {{ strtoupper(substr($student->full_name, 0, 2)) }}
                    </div>
                @endif
            </div>

            <div class="student-details">
                <h4 class="mb-1 fw-bold">{{ $student->full_name }}</h4>
                <div class="student-meta">
                    <span><i class="bi bi-id-card"></i> {{ $student->custom_id }}</span>
                    <span><i class="bi bi-phone"></i> {{ $student->mobile }}</span>
                    <span><i class="bi bi-envelope"></i> {{ $student->email ?? 'N/A' }}</span>
                    <span><i class="bi bi-graduation-cap"></i> {{ optional($student->grade)->grade_name ?? 'N/A' }}</span>
                    <span>
                        <i class="bi bi-circle-fill {{ $student->is_active ? 'text-success' : 'text-danger' }}"></i>
                        {{ $student->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <div class="student-actions">
                <a href="{{ route('admin.student-class-management.assign-form', $student->id) }}"
                    class="btn btn-primary custom-btn">
                    <i class="bi bi-plus-lg"></i> Assign Class
                </a>
                <a href="{{ route('admin.student-class-management.index') }}" class="btn btn-light border custom-btn">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

        </div>

        <!-- CLASSES CARD -->
        <div class="main-card">

            <div class="main-card-header">

                <div>
                    <h4>Enrolled Classes</h4>
                    <p>Manage class enrollments for this student</p>
                </div>

                <div class="header-buttons">
                    @if (count($classes) > 0)
                        <button type="button" class="btn btn-warning custom-btn" onclick="confirmToggleAll()">
                            <i class="bi bi-arrow-repeat"></i> Toggle All
                        </button>
                        <form action="{{ route('admin.student-class-management.toggle-all', $student->id) }}"
                            method="POST" class="d-inline" id="toggleAllForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_active" id="toggleAllValue" value="">
                        </form>
                    @endif
                    <span class="badge bg-primary class-count-badge">{{ count($classes) }} Classes</span>
                </div>

            </div>

            @if (count($classes) > 0)
                <div class="table-responsive">
                    <table class="table custom-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Class Details</th>
                                <th>Grade</th> <!-- ✅ New Column -->
                                <th>Category</th>
                                <th>Fee (LKR)</th>
                                <th>Payment Status</th>
                                <th>Status</th>
                                <th>Enrolled</th>
                                <th width="130" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classes as $index => $class)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $class['class_name'] }}</div>
                                        <div class="text-muted small">
                                            <i class="bi bi-tag"></i> {{ $class['class_type'] }} &middot;
                                            <i class="bi bi-globe"></i> {{ $class['medium'] }}
                                        </div>
                                        @if ($class['note'])
                                            <div class="text-muted small mt-1">
                                                <i class="bi bi-info-circle"></i> {{ $class['note'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $class['grade_name'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $class['category_name'] }}</span>
                                    </td>
                                    <td>
                                        @if ($class['final_fee'] != $class['fee'])
                                            <div>
                                                <span class="text-muted text-decoration-line-through">
                                                    {{ number_format($class['fee'], 2) }}
                                                </span>
                                                <br>
                                                <span class="text-success fw-bold">
                                                    {{ number_format($class['final_fee'], 2) }}
                                                </span>
                                                @if ($class['discount_percentage'])
                                                    <br>
                                                    <span class="badge bg-warning text-dark">
                                                        {{ $class['discount_percentage'] }}% off
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="fw-bold">{{ number_format($class['final_fee'], 2) }}</span>
                                        @endif
                                        @if ($class['custom_fee'])
                                            <br><span class="badge bg-warning text-dark">Custom</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($class['payment_status'] == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($class['payment_status'] == 'partial')
                                            <span class="badge bg-warning text-dark">Partial</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($class['is_active'])
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                            @if ($class['left_at'])
                                                <br><small class="text-muted">Left: {{ $class['left_at'] }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $class['enrolled_at'] ?? 'N/A' }}</small>
                                    </td>
                                    <td class="text-end">
                                        @if ($class['is_active'])
                                            <button type="button" class="action-btn deactivate-btn"
                                                onclick="toggleStatus({{ $class['enrollment_id'] }}, 0, '{{ $class['class_name'] }}')"
                                                title="Deactivate">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @else
                                            <button type="button" class="action-btn activate-btn"
                                                onclick="toggleStatus({{ $class['enrollment_id'] }}, 1, '{{ $class['class_name'] }}')"
                                                title="Activate">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-journal-bookmark"></i>
                    <h5>No Classes Enrolled</h5>
                    <p class="text-muted">This student is not enrolled in any class yet.</p>

                </div>
            @endif

        </div>
    </div>

    <!-- Toggle Status Modal -->
    <div class="modal fade" id="toggleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Confirm Status Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="toggleForm" method="POST">
                    @csrf
                    @method('PUT') <!-- ✅ This will be overwritten by JavaScript -->
                    <div class="modal-body">
                        <p id="toggleMessage"></p>
                        <div id="leftAtField" style="display: none;">
                            <div class="mb-3">
                                <label for="left_at" class="form-label fw-semibold">Left Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="left_at" id="left_at" class="form-control custom-input"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label fw-semibold">Note</label>
                            <textarea name="note" id="note" class="form-control custom-input" rows="2"
                                placeholder="Add a note about this change..."></textarea>
                        </div>
                        <input type="hidden" name="is_active" id="modalIsActive">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border custom-btn"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary custom-btn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .student-class-show-page {
            animation: fadeIn .4s ease;
        }

        .student-info-card {
            background: #fff;
            border-radius: 24px;
            padding: 1.5rem;
            display: flex;
            gap: 1.5rem;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .04);
            border: 1px solid #eef2f7;
            flex-wrap: wrap;
        }

        .student-avatar {
            flex-shrink: 0;
        }

        .student-avatar img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #eef2f7;
        }

        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .student-details {
            flex: 1;
            min-width: 200px;
        }

        .student-details h4 {
            margin: 0;
        }

        .student-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: .5rem;
        }

        .student-meta span {
            font-size: .9rem;
            color: #64748b;
        }

        .student-meta span i {
            margin-right: .3rem;
        }

        .student-actions {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
        }

        .main-card {
            background: #fff;
            border-radius: 28px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .main-card-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .main-card-header p {
            margin: 0;
            color: #64748b;
        }

        .header-buttons {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .class-count-badge {
            font-size: 1rem;
            padding: .5rem 1rem;
            border-radius: 12px;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.2rem;
            font-weight: 600;
            border: none;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .custom-input {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            min-height: 48px;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            text-transform: uppercase;
            padding: 1rem;
        }

        .custom-table tbody tr {
            transition: .2s ease;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .custom-table tbody td {
            padding: 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: .2s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .deactivate-btn {
            background: #fef2f2;
            color: #ef4444;
        }

        .deactivate-btn:hover {
            background: #ef4444;
            color: #fff;
        }

        .activate-btn {
            background: #ecfdf5;
            color: #10b981;
        }

        .activate-btn:hover {
            background: #10b981;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 3.5rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
        }

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

        @media(max-width:768px) {
            .student-info-card {
                flex-direction: column;
                text-align: center;
            }

            .student-meta {
                justify-content: center;
            }

            .student-actions {
                justify-content: center;
                width: 100%;
            }

            .main-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-buttons {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function toggleStatus(enrollmentId, isActive, className) {
            const action = isActive ? 'activate' : 'deactivate';
            const message = isActive ?
                `Are you sure you want to <strong>activate</strong> the class "<strong>${className}</strong>"?` :
                `Are you sure you want to <strong>deactivate</strong> the class "<strong>${className}</strong>"?`;

            document.getElementById('toggleMessage').innerHTML = message;
            document.getElementById('modalIsActive').value = isActive;
            document.getElementById('leftAtField').style.display = isActive ? 'none' : 'block';

            // ✅ Fix: Use the toggle-status route with PUT method
            const form = document.getElementById('toggleForm');
            form.action = `/admin/student-class-management/${enrollmentId}/toggle-status`;
            form.method = 'POST'; // Keep as POST for Laravel form
            form.querySelector('input[name="_method"]').value = 'PUT'; // Set PUT method

            const modal = new bootstrap.Modal(document.getElementById('toggleModal'));
            modal.show();
        }

        function confirmToggleAll() {
            if (confirm('Are you sure you want to toggle all classes status?')) {
                const currentStatus = {{ count($classes->where('is_active', true)) > 0 ? 'true' : 'false' }};
                document.getElementById('toggleAllValue').value = currentStatus ? 0 : 1;

                // ✅ Fix: Use correct method
                const form = document.getElementById('toggleAllForm');
                form.querySelector('input[name="_method"]').value = 'PUT';
                form.submit();
            }
        }

        // ✅ Auto-close alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@endpush
