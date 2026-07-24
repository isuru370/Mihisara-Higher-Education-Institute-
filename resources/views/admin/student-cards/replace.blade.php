@extends('layouts.app')

@section('title', 'Student Card Details')
@section('page-title', 'Student Card Details')

@section('content')

<div class="halls-page">

    <!-- MAIN CARD -->
    <div class="main-card">

        <!-- HEADER -->
        <div class="main-card-header">
            <div>
                <h4>
                    <i class="bi bi-person-vcard-fill text-primary me-2"></i>
                    Student Card Details
                </h4>
                <p>View student card information</p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.student-cards.index') }}" class="btn btn-light border custom-btn">
                    <i class="bi bi-arrow-left"></i>
                    Back to Inventory
                </a>
                @if($card->status == 'assigned' && $card->student)
                    <a href="#" class="btn btn-primary custom-btn" target="_blank">
                        <i class="bi bi-printer"></i>
                        Print Card
                    </a>
                @endif
            </div>
        </div>

        <!-- ALERT MESSAGES -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show custom-alert">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show custom-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- CONTENT -->
        <div class="row g-4">
            {{-- Student Profile Card --}}
            <div class="col-lg-4">
                <div class="profile-card">
                    {{-- Card Header with Gradient --}}
                    <div class="profile-header">
                        <div class="text-center">
                            <div class="position-relative d-inline-block">
                                {{-- Student Photo --}}
                                @if($card->student && $card->student->img_url)
                                    @php
                                        $imageUrl = $card->student->img_url;
                                        if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                                            $fullImageUrl = $imageUrl;
                                        } else {
                                            $imageUrl = ltrim($imageUrl, '/');
                                            if (strpos($imageUrl, 'storage/') === 0) {
                                                $fullImageUrl = asset($imageUrl);
                                            } else {
                                                $fullImageUrl = asset('storage/' . $imageUrl);
                                            }
                                        }
                                    @endphp
                                    <img src="{{ $fullImageUrl }}" 
                                         alt="{{ $card->student->full_name }}" 
                                         class="profile-image"
                                         onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
                                @else
                                    <div class="profile-image-placeholder">
                                        <i class="bi bi-person fs-1"></i>
                                    </div>
                                @endif
                                
                                {{-- Status Badge on Photo --}}
                                <span class="position-absolute bottom-0 end-0">
                                    @if($card->status == 'assigned')
                                        <span class="badge bg-success custom-badge border border-white">
                                            <i class="bi bi-check-circle-fill me-1"></i> Active
                                        </span>
                                    @elseif($card->status == 'lost')
                                        <span class="badge bg-danger custom-badge border border-white">
                                            <i class="bi bi-x-circle-fill me-1"></i> Lost
                                        </span>
                                    @elseif($card->status == 'damaged')
                                        <span class="badge bg-warning text-dark custom-badge border border-white">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Damaged
                                        </span>
                                    @else
                                        <span class="badge bg-secondary custom-badge border border-white">
                                            {{ ucfirst($card->status) }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                            
                            <h4 class="fw-bold mt-3 mb-1 text-white">
                                {{ $card->student ? $card->student->full_name : 'Not Assigned' }}
                            </h4>
                            @if($card->student)
                                <p class="text-white-50 mb-0">
                                    <i class="bi bi-person-badge me-1"></i>
                                    {{ $card->student->custom_id ?? 'ID: ' . $card->student->id }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="profile-body">
                        @if($card->student)
                            {{-- QR Code Preview --}}
                            <div class="text-center mb-4">
                                <div class="qr-wrapper">
                                    @if($card->qr_code)
                                        <div class="qr-code-preview">
                                            @if(class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode'))
                                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($card->qr_code) !!}
                                            @else
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($card->qr_code) }}" 
                                                     alt="QR Code" 
                                                     style="width: 120px; height: 120px;">
                                            @endif
                                        </div>
                                    @else
                                        <div class="qr-placeholder">
                                            <i class="bi bi-qr-code fs-1 text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-muted small mt-2 mb-0">
                                    <i class="bi bi-qr-code me-1"></i>
                                    {{ $card->qr_code ?? 'No QR Code' }}
                                </p>
                            </div>

                            {{-- Student Information Grid --}}
                            <div class="info-grid">
                                <div class="info-item">
                                    <label class="info-label">Student ID</label>
                                    <p class="info-value">{{ $card->student->custom_id ?? '#' . $card->student->id }}</p>
                                </div>
                                <div class="info-item">
                                    <label class="info-label">Grade</label>
                                    <p class="info-value">
                                        @if($card->student->grade)
                                            <span class="badge bg-info custom-badge">{{ $card->student->grade->grade_name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="info-item full-width">
                                    <label class="info-label">
                                        <i class="bi bi-telephone me-1"></i> Mobile
                                    </label>
                                    <p class="info-value">
                                        {{ $card->student->mobile ?? 'Not provided' }}
                                    </p>
                                </div>
                                <div class="info-item full-width">
                                    <label class="info-label">
                                        <i class="bi bi-envelope me-1"></i> Email
                                    </label>
                                    <p class="info-value">
                                        {{ $card->student->email ?? 'Not provided' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Active Classes --}}
                            <div class="section-divider">
                                <label class="info-label">
                                    <i class="bi bi-book me-1"></i> Active Classes
                                </label>
                                @php
                                    $activeEnrollments = $card->student->enrollments->where('is_active', true);
                                @endphp
                                @if($activeEnrollments->isEmpty())
                                    <p class="text-muted mb-0">No active classes</p>
                                @else
                                    <div class="class-badges">
                                        @foreach($activeEnrollments as $enrollment)
                                            <span class="class-badge-item">
                                                <i class="bi bi-bookmark-fill me-1"></i>
                                                {{ $enrollment->studentClass->class_name }}
                                                @if($enrollment->category)
                                                    <span class="badge bg-secondary custom-badge ms-1">
                                                        {{ $enrollment->category->category_name }}
                                                    </span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Guardian Information --}}
                            <div class="section-divider">
                                <label class="info-label">
                                    <i class="bi bi-person-lines-fill me-1"></i> Guardian
                                </label>
                                <div class="guardian-info">
                                    <p class="guardian-name">
                                        {{ $card->student->guardian_fname ?? '' }} {{ $card->student->guardian_lname ?? '' }}
                                    </p>
                                    <p class="guardian-detail">
                                        <i class="bi bi-telephone me-1"></i>
                                        {{ $card->student->guardian_mobile ?? 'Not provided' }}
                                    </p>
                                    @if($card->student->guardian_nic)
                                        <p class="guardian-detail">
                                            <i class="bi bi-card-text me-1"></i>
                                            NIC: {{ $card->student->guardian_nic }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="bi bi-person-x"></i>
                                <h5>No Student Assigned</h5>
                                <p>This card has not been assigned to a student.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Card Information & Details --}}
            <div class="col-lg-8">
                <div class="row g-4">
                    {{-- Card Details --}}
                    <div class="col-12">
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-credit-card-2-front text-primary me-2"></i>
                                    Card Information
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="detail-info-item">
                                            <label class="detail-info-label">Card Number</label>
                                            <p class="detail-info-value text-primary">
                                                <i class="bi bi-hash me-1"></i>
                                                {{ $card->card_number }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-info-item">
                                            <label class="detail-info-label">Status</label>
                                            @if ($card->status == 'available')
                                                <span class="badge bg-success custom-badge-lg">
                                                    <i class="bi bi-check-circle me-1"></i> Available
                                                </span>
                                            @elseif($card->status == 'assigned')
                                                <span class="badge bg-primary custom-badge-lg">
                                                    <i class="bi bi-person-check me-1"></i> Assigned
                                                </span>
                                            @elseif($card->status == 'lost')
                                                <span class="badge bg-danger custom-badge-lg">
                                                    <i class="bi bi-x-circle me-1"></i> Lost
                                                </span>
                                            @elseif($card->status == 'damaged')
                                                <span class="badge bg-warning text-dark custom-badge-lg">
                                                    <i class="bi bi-exclamation-triangle me-1"></i> Damaged
                                                </span>
                                            @else
                                                <span class="badge bg-secondary custom-badge-lg">
                                                    {{ ucfirst($card->status) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-info-item">
                                            <label class="detail-info-label">Current Card</label>
                                            @if ($card->is_current)
                                                <span class="badge bg-success custom-badge-lg">
                                                    <i class="bi bi-star-fill me-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary custom-badge-lg">
                                                    <i class="bi bi-clock me-1"></i> Inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-info-item">
                                            <label class="detail-info-label">Issued Date</label>
                                            <p class="detail-info-value">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                {{ optional($card->issued_at)->format('d M Y') }}
                                                <span class="text-muted d-block small">
                                                    {{ optional($card->issued_at)->format('h:i A') }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-info-item">
                                            <label class="detail-info-label">QR Code</label>
                                            <p class="detail-info-value">
                                                <code class="qr-code-text">{{ $card->qr_code ?? 'N/A' }}</code>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Enrollment Summary --}}
                    @if($card->student)
                    <div class="col-12">
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-clock-history text-primary me-2"></i>
                                    Enrollment Summary
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                @php
                                    $enrollments = $card->student->enrollments;
                                    $activeCount = $enrollments->where('is_active', true)->count();
                                    $totalEnrollments = $enrollments->count();
                                    $inactiveCount = $enrollments->where('is_active', false)->count();
                                @endphp
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="stat-card-primary">
                                            <h3 class="stat-number text-primary">{{ $activeCount }}</h3>
                                            <p class="stat-label">Active Classes</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card-success">
                                            <h3 class="stat-number text-success">{{ $totalEnrollments }}</h3>
                                            <p class="stat-label">Total Enrollments</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card-info">
                                            <h3 class="stat-number text-info">{{ $inactiveCount }}</h3>
                                            <p class="stat-label">Completed/Inactive</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Recent Enrollments List --}}
                                @if($enrollments->isNotEmpty())
                                <div class="enrollment-table-wrapper">
                                    <label class="info-label mb-2">Recent Enrollments</label>
                                    <div class="table-responsive">
                                        <table class="table custom-table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Class</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                    <th>Enrolled Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($enrollments->take(5) as $enrollment)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $enrollment->studentClass->class_name ?? 'N/A' }}</td>
                                                        <td>
                                                            @if($enrollment->category)
                                                                <span class="badge bg-secondary custom-badge">{{ $enrollment->category->category_name }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($enrollment->is_active)
                                                                <span class="badge bg-success custom-badge">Active</span>
                                                            @else
                                                                <span class="badge bg-secondary custom-badge">Inactive</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ optional($enrollment->enrolled_at)->format('d M Y') ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Actions --}}
                    <div class="col-12">
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-tools text-primary me-2"></i>
                                    Actions
                                </h5>
                            </div>
                            <div class="detail-card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    @if ($card->status == 'assigned' && $card->student)
                                        <a href="{{ route('admin.student-cards.replace', $card->id) }}" class="btn btn-warning custom-btn">
                                            <i class="bi bi-arrow-repeat"></i>
                                            Replace Card
                                        </a>

                                        <a href="{{ route('admin.student-cards.lost', $card->id) }}" 
                                           class="btn btn-danger custom-btn"
                                           onclick="return confirm('Are you sure you want to mark this card as lost?')">
                                            <i class="bi bi-x-circle"></i>
                                            Mark Lost
                                        </a>

                                        <a href="{{ route('admin.student-cards.damaged', $card->id) }}" 
                                           class="btn btn-dark custom-btn"
                                           onclick="return confirm('Are you sure you want to mark this card as damaged?')">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Mark Damaged
                                        </a>

                                        <a href="{{ route('admin.student-cards.deactivate', $card->id) }}" 
                                           class="btn btn-secondary custom-btn"
                                           onclick="return confirm('Are you sure you want to deactivate this card?')">
                                            <i class="bi bi-slash-circle"></i>
                                            Deactivate
                                        </a>

                                        @if($card->student)
                                            <a href="{{ route('admin.students.show', $card->student->id) }}" class="btn btn-info custom-btn text-white">
                                                <i class="bi bi-person"></i>
                                                View Student Profile
                                            </a>
                                        @endif
                                    @elseif($card->status == 'available')
                                        <a href="{{ route('admin.student-cards.assign', $card->id) }}" class="btn btn-primary custom-btn">
                                            <i class="bi bi-person-plus"></i>
                                            Assign to Student
                                        </a>
                                    @endif
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

@push('styles')
<style>
    /* ===== HALLS PAGE STYLES ===== */
    .halls-page {
        animation: fadeIn 0.4s ease;
    }

    /* ===== MAIN CARD ===== */
    .main-card {
        background: #fff;
        border-radius: 28px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
    }

    /* ===== HEADER ===== */
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
        color: #0f172a;
        font-size: 1.3rem;
    }
    .main-card-header p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    .header-buttons {
        display: flex;
        gap: .7rem;
        flex-wrap: wrap;
    }

    /* ===== CUSTOM BUTTONS ===== */
    .custom-btn {
        border-radius: 14px;
        padding: .7rem 1.2rem;
        font-weight: 600;
        border: none;
        transition: all .2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }
    .custom-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* ===== ALERTS ===== */
    .custom-alert {
        border-radius: 16px;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .custom-alert i {
        font-size: 1.3rem;
    }

    /* ===== PROFILE CARD ===== */
    .profile-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #eef2f7;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .04);
    }

    .profile-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 2rem 1.5rem 1.5rem;
    }

    .profile-image {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
        object-fit: cover;
    }

    .profile-image-placeholder {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid #fff;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        margin: 0 auto;
    }

    .profile-body {
        padding: 1.5rem;
    }

    /* ===== BADGES ===== */
    .custom-badge {
        border-radius: 10px;
        padding: .4rem .7rem;
        font-size: .75rem;
        font-weight: 600;
    }

    .custom-badge-lg {
        border-radius: 12px;
        padding: .5rem 1rem;
        font-size: .9rem;
        font-weight: 600;
    }

    /* ===== QR CODE ===== */
    .qr-wrapper {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 16px;
        display: inline-block;
        border: 2px dashed #e2e8f0;
    }

    .qr-code-preview {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 8px;
    }

    .qr-placeholder {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 8px;
    }

    .qr-code-text {
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #1e293b;
        font-weight: 600;
    }

    /* ===== INFO GRID ===== */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem 1rem;
        margin-bottom: 1rem;
    }

    .info-item {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-item.full-width {
        grid-column: 1 / -1;
    }

    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        display: block;
        margin-bottom: 2px;
    }

    .info-value {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 0;
        font-size: 0.95rem;
    }

    /* ===== SECTION DIVIDER ===== */
    .section-divider {
        padding-top: 1rem;
        margin-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    /* ===== CLASS BADGES ===== */
    .class-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .class-badge-item {
        background: #eff6ff;
        color: #2563eb;
        padding: 0.4rem 0.8rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }

    /* ===== GUARDIAN INFO ===== */
    .guardian-info {
        margin-top: 0.5rem;
    }

    .guardian-name {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 2px;
    }

    .guardian-detail {
        color: #64748b;
        margin-bottom: 2px;
        font-size: 0.9rem;
    }

    /* ===== DETAIL CARD ===== */
    .detail-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #eef2f7;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .04);
    }

    .detail-card-header {
        padding: 1.25rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f7;
    }

    .detail-card-body {
        padding: 1.5rem;
    }

    /* ===== DETAIL INFO ===== */
    .detail-info-item {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 14px;
        border: 1px solid #eef2f7;
        height: 100%;
        transition: all .2s ease;
    }
    .detail-info-item:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .08);
    }

    .detail-info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        display: block;
        margin-bottom: 4px;
    }

    .detail-info-value {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 0;
        font-size: 1rem;
    }

    /* ===== STAT CARDS ===== */
    .stat-card-primary,
    .stat-card-success,
    .stat-card-info {
        padding: 1rem;
        border-radius: 14px;
        text-align: center;
        border: 1px solid #eef2f7;
        transition: all .2s ease;
    }
    .stat-card-primary:hover,
    .stat-card-success:hover,
    .stat-card-info:hover {
        transform: translateY(-2px);
    }

    .stat-card-primary {
        background: #eff6ff;
        border-color: #bfdbfe;
    }
    .stat-card-success {
        background: #ecfdf5;
        border-color: #a7f3d0;
    }
    .stat-card-info {
        background: #f0f9ff;
        border-color: #bae6fd;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0;
        font-weight: 500;
    }

    /* ===== CUSTOM TABLE ===== */
    .custom-table-sm thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 0.6rem 0.8rem;
        border: none;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .custom-table-sm tbody td {
        padding: 0.6rem 0.8rem;
        border-color: #f1f5f9;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .custom-table-sm tbody tr:hover {
        background: #f8fafc;
    }

    .enrollment-table-wrapper {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    .empty-state i {
        font-size: 3.5rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
        display: block;
    }
    .empty-state h5 {
        font-weight: 700;
        color: #1e293b;
    }
    .empty-state p {
        color: #64748b;
    }

    /* ===== RESPONSIVE ===== */
    @media(max-width: 768px) {
        .main-card-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-buttons {
            width: 100%;
        }

        .header-buttons a,
        .header-buttons button {
            flex: 1;
        }

        .main-card {
            padding: 1rem;
        }

        .profile-header {
            padding: 1.5rem 1rem;
        }

        .profile-body {
            padding: 1rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .detail-card-body {
            padding: 1rem;
        }

        .detail-card-header {
            padding: 1rem;
        }

        .stat-number {
            font-size: 1.5rem;
        }
    }

    @media(max-width: 576px) {
        .profile-image,
        .profile-image-placeholder {
            width: 80px;
            height: 80px;
        }

        .qr-code-preview,
        .qr-placeholder {
            width: 100px;
            height: 100px;
        }

        .custom-btn {
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
        }

        .detail-info-item {
            padding: 0.8rem;
        }
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentImages = document.querySelectorAll('.profile-image');
        studentImages.forEach(img => {
            img.addEventListener('error', function() {
                this.onerror = null;
                this.style.display = 'none';
                const placeholder = document.createElement('div');
                placeholder.className = 'profile-image-placeholder';
                placeholder.innerHTML = '<i class="bi bi-person fs-1"></i>';
                this.parentNode.appendChild(placeholder);
            });
        });
    });
</script>
@endpush