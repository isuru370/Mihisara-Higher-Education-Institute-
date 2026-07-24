@extends('layouts.app')

@section('title', 'Student Card Details')

@section('content')

<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">
                <i class="bi bi-person-vcard-fill text-primary"></i>
                Student Card Details
            </h2>
            <p class="text-muted mb-0">
                View student card information.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.student-cards.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Back
            </a>
            @if($card->status == 'assigned' && $card->student)
                <a href="#" class="btn btn-primary" target="_blank">
                    <i class="bi bi-printer"></i>
                    Print Card
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Student Profile Card --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                {{-- Card Header with Gradient --}}
                <div class="card-header bg-gradient-primary text-white py-4" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                    <div class="text-center">
                        <div class="position-relative d-inline-block">
                            {{-- Student Photo --}}
                            @if($card->student && $card->student->img_url)
                                @php
                                    // Handle different image URL formats
                                    $imageUrl = $card->student->img_url;
                                    
                                    // Check if it's already a full URL
                                    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                                        $fullImageUrl = $imageUrl;
                                    } else {
                                        // Remove leading slash if exists
                                        $imageUrl = ltrim($imageUrl, '/');
                                        // Check if it already contains 'storage/'
                                        if (strpos($imageUrl, 'storage/') === 0) {
                                            $fullImageUrl = asset($imageUrl);
                                        } else {
                                            $fullImageUrl = asset('storage/' . $imageUrl);
                                        }
                                    }
                                @endphp
                                <img src="{{ $fullImageUrl }}" 
                                     alt="{{ $card->student->full_name }}" 
                                     class="rounded-circle border border-3 border-white shadow-sm"
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
                            @else
                                <div class="rounded-circle border border-3 border-white bg-light d-flex align-items-center justify-content-center mx-auto"
                                     style="width: 100px; height: 100px;">
                                    <i class="bi bi-person fs-1 text-secondary"></i>
                                </div>
                            @endif
                            
                            {{-- Status Badge on Photo --}}
                            <span class="position-absolute bottom-0 end-0">
                                @if($card->status == 'assigned')
                                    <span class="badge bg-success rounded-pill px-3 py-2 border border-white">
                                        <i class="bi bi-check-circle-fill me-1"></i> Active
                                    </span>
                                @elseif($card->status == 'lost')
                                    <span class="badge bg-danger rounded-pill px-3 py-2 border border-white">
                                        <i class="bi bi-x-circle-fill me-1"></i> Lost
                                    </span>
                                @elseif($card->status == 'damaged')
                                    <span class="badge bg-warning rounded-pill px-3 py-2 border border-white">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Damaged
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-2 border border-white">
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

                <div class="card-body p-4">
                    @if($card->student)
                        {{-- QR Code Preview --}}
                        <div class="text-center mb-4">
                            <div class="bg-light p-3 rounded-3 d-inline-block">
                                @if($card->qr_code)
                                    <div class="qr-code-preview" style="width: 120px; height: 120px; background: white; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        @if(class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode'))
                                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($card->qr_code) !!}
                                        @else
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($card->qr_code) }}" 
                                                 alt="QR Code" 
                                                 style="width: 120px; height: 120px;">
                                        @endif
                                    </div>
                                @else
                                    <div class="bg-white d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
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
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-item">
                                    <label class="text-muted small text-uppercase fw-semibold d-block">Student ID</label>
                                    <p class="fw-semibold mb-0">{{ $card->student->custom_id ?? '#' . $card->student->id }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-item">
                                    <label class="text-muted small text-uppercase fw-semibold d-block">Grade</label>
                                    <p class="fw-semibold mb-0">
                                        @if($card->student->grade)
                                            <span class="badge bg-info text-white">{{ $card->student->grade->grade_name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <label class="text-muted small text-uppercase fw-semibold d-block">
                                        <i class="bi bi-telephone me-1"></i> Mobile
                                    </label>
                                    <p class="fw-semibold mb-0">
                                        {{ $card->student->mobile ?? 'Not provided' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <label class="text-muted small text-uppercase fw-semibold d-block">
                                        <i class="bi bi-envelope me-1"></i> Email
                                    </label>
                                    <p class="fw-semibold mb-0">
                                        {{ $card->student->email ?? 'Not provided' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Active Classes --}}
                        <div class="mt-4">
                            <label class="text-muted small text-uppercase fw-semibold d-block mb-2">
                                <i class="bi bi-book me-1"></i> Active Classes
                            </label>
                            @php
                                $activeEnrollments = $card->student->enrollments->where('is_active', true);
                            @endphp
                            @if($activeEnrollments->isEmpty())
                                <p class="text-muted mb-0">No active classes</p>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($activeEnrollments as $enrollment)
                                        <div class="class-badge">
                                            <span class="badge bg-primary bg-gradient px-3 py-2 rounded-pill">
                                                <i class="bi bi-bookmark-fill me-1"></i>
                                                {{ $enrollment->studentClass->class_name }}
                                            </span>
                                            @if($enrollment->category)
                                                <span class="badge bg-secondary bg-gradient px-3 py-2 rounded-pill ms-1">
                                                    {{ $enrollment->category->category_name }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Guardian Information --}}
                        <div class="mt-4 pt-3 border-top">
                            <label class="text-muted small text-uppercase fw-semibold d-block mb-2">
                                <i class="bi bi-person-lines-fill me-1"></i> Guardian
                            </label>
                            <div class="row">
                                <div class="col-12">
                                    <p class="mb-1">
                                        <strong>{{ $card->student->guardian_fname ?? '' }} {{ $card->student->guardian_lname ?? '' }}</strong>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-telephone me-1"></i>
                                        {{ $card->student->guardian_mobile ?? 'Not provided' }}
                                    </p>
                                    @if($card->student->guardian_nic)
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-card-text me-1"></i>
                                            NIC: {{ $card->student->guardian_nic }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-person-x fs-1 text-muted"></i>
                            <p class="text-muted mt-2">This card has not been assigned to a student.</p>
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
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white border-0 pt-4">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-credit-card-2-front text-primary me-2"></i>
                                Card Information
                            </h5>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="info-card p-3 bg-light rounded-3">
                                        <label class="text-muted small text-uppercase fw-semibold d-block">Card Number</label>
                                        <p class="fs-5 fw-bold mb-0 text-primary">
                                            <i class="bi bi-hash me-1"></i>
                                            {{ $card->card_number }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-card p-3 bg-light rounded-3">
                                        <label class="text-muted small text-uppercase fw-semibold d-block">Status</label>
                                        @if ($card->status == 'available')
                                            <span class="badge bg-success bg-gradient fs-6 px-4 py-2">
                                                <i class="bi bi-check-circle me-1"></i> Available
                                            </span>
                                        @elseif($card->status == 'assigned')
                                            <span class="badge bg-primary bg-gradient fs-6 px-4 py-2">
                                                <i class="bi bi-person-check me-1"></i> Assigned
                                            </span>
                                        @elseif($card->status == 'lost')
                                            <span class="badge bg-danger bg-gradient fs-6 px-4 py-2">
                                                <i class="bi bi-x-circle me-1"></i> Lost
                                            </span>
                                        @elseif($card->status == 'damaged')
                                            <span class="badge bg-warning bg-gradient fs-6 px-4 py-2">
                                                <i class="bi bi-exclamation-triangle me-1"></i> Damaged
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-gradient fs-6 px-4 py-2">
                                                {{ ucfirst($card->status) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-card p-3 bg-light rounded-3">
                                        <label class="text-muted small text-uppercase fw-semibold d-block">Current Card</label>
                                        @if ($card->is_current)
                                            <span class="badge bg-success bg-gradient fs-6 px-4 py-2">
                                                <i class="bi bi-star-fill me-1"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-gradient fs-6 px-4 py-2">
                                                <i class="bi bi-clock me-1"></i> Inactive
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-card p-3 bg-light rounded-3">
                                        <label class="text-muted small text-uppercase fw-semibold d-block">Issued Date</label>
                                        <p class="fw-semibold mb-0">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            {{ optional($card->issued_at)->format('d M Y') }}
                                            <span class="text-muted small d-block">
                                                {{ optional($card->issued_at)->format('h:i A') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-card p-3 bg-light rounded-3">
                                        <label class="text-muted small text-uppercase fw-semibold d-block">QR Code</label>
                                        <p class="fw-semibold mb-0">
                                            <code class="bg-white p-1 rounded">{{ $card->qr_code ?? 'N/A' }}</code>
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
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white border-0 pt-4">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-clock-history text-primary me-2"></i>
                                Enrollment Summary
                            </h5>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                @php
                                    $enrollments = $card->student->enrollments;
                                    $activeCount = $enrollments->where('is_active', true)->count();
                                    $totalEnrollments = $enrollments->count();
                                @endphp
                                <div class="col-md-4">
                                    <div class="stat-card text-center p-3 bg-primary bg-opacity-10 rounded-3">
                                        <h3 class="mb-0 text-primary">{{ $activeCount }}</h3>
                                        <p class="text-muted small mb-0">Active Classes</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card text-center p-3 bg-success bg-opacity-10 rounded-3">
                                        <h3 class="mb-0 text-success">{{ $totalEnrollments }}</h3>
                                        <p class="text-muted small mb-0">Total Enrollments</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card text-center p-3 bg-info bg-opacity-10 rounded-3">
                                        <h3 class="mb-0 text-info">{{ $enrollments->where('is_active', false)->count() }}</h3>
                                        <p class="text-muted small mb-0">Completed/Inactive</p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Recent Enrollments List --}}
                            @if($enrollments->isNotEmpty())
                            <div class="mt-3">
                                <label class="text-muted small text-uppercase fw-semibold d-block mb-2">Recent Enrollments</label>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
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
                                                    <td>{{ $enrollment->studentClass->class_name ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($enrollment->category)
                                                            <span class="badge bg-secondary">{{ $enrollment->category->category_name }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($enrollment->is_active)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-secondary">Inactive</span>
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
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white border-0 pt-4">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-tools text-primary me-2"></i>
                                Actions
                            </h5>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-wrap gap-2">
                                @if ($card->status == 'assigned' && $card->student)
                                    <a href="{{ route('admin.student-cards.replace', $card->id) }}" class="btn btn-warning">
                                        <i class="bi bi-arrow-repeat"></i>
                                        Replace Card
                                    </a>

                                    <a href="{{ route('admin.student-cards.lost', $card->id) }}" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Are you sure you want to mark this card as lost?')">
                                        <i class="bi bi-x-circle"></i>
                                        Mark Lost
                                    </a>

                                    <a href="{{ route('admin.student-cards.damaged', $card->id) }}" 
                                       class="btn btn-dark"
                                       onclick="return confirm('Are you sure you want to mark this card as damaged?')">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Mark Damaged
                                    </a>

                                    <a href="{{ route('admin.student-cards.deactivate', $card->id) }}" 
                                       class="btn btn-secondary"
                                       onclick="return confirm('Are you sure you want to deactivate this card?')">
                                        <i class="bi bi-slash-circle"></i>
                                        Deactivate
                                    </a>

                                    @if($card->student)
                                        <a href="{{ route('admin.students.show', $card->student->id) }}" class="btn btn-info text-white">
                                            <i class="bi bi-person"></i>
                                            View Student Profile
                                        </a>
                                    @endif
                                @elseif($card->status == 'available')
                                    <a href="{{ route('admin.student-cards.assign', $card->id) }}" class="btn btn-primary">
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

@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .info-item {
        padding: 6px 0;
    }
    
    .info-item label {
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        color: #6c757d;
    }
    
    .info-item p {
        font-size: 0.95rem;
        margin-bottom: 2px;
    }
    
    .class-badge {
        display: inline-block;
        margin-bottom: 4px;
    }
    
    .stat-card {
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    .info-card {
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .rounded-4 {
        border-radius: 1rem;
    }
    
    .qr-code-preview {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .qr-code-preview svg {
        max-width: 100%;
        height: auto;
    }
    
    /* Image error fallback */
    img[onerror] {
        background: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
    // Optional: Add image loading handling
    document.addEventListener('DOMContentLoaded', function() {
        const studentImages = document.querySelectorAll('img[alt]');
        studentImages.forEach(img => {
            img.addEventListener('error', function() {
                this.onerror = null;
                this.src = '{{ asset("images/default-avatar.png") }}';
            });
        });
    });
</script>
@endpush