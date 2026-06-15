@php
    $user = auth()->user();
    $isAdmin = ($user && $user->email === 'admin@nexorait.lk');
    $studentRow = $student->student;
    $studentId  = $studentRow?->custom_id;
    $name       = $studentRow?->initial_name ?? 'Student Name';
    
    $address1 = $studentRow?->address1 ?? '';
    $address2 = $studentRow?->address2 ?? '';
    $address3 = $studentRow?->address3 ?? '';
    
    $address1 = trim(str_replace(['"', "'", '&quot;'], '', $address1));
    $address2 = trim(str_replace(['"', "'", '&quot;'], '', $address2));
    $address3 = trim(str_replace(['"', "'", '&quot;'], '', $address3));
    
    $addressParts = array_filter([$address1, $address2, $address3]);
    $address = implode(' ', $addressParts);
    $address = str_replace(['"', "'"], '', $address);
    
    $studentData = [
        'id'         => $student->id,
        'student_id' => $student->student_id,
        'card_no'    => $student->card_no,
        'custom_id'  => $studentId ?? 'SA09001',
        'name'       => $name,
        'address'    => $address,
        'img_url'    => $studentRow?->img_url,
        'status'     => $student->status
    ];

    $studentKey   = $studentId ?? $student->id;
    $defaultImage = asset('storage/logo/black_logo3.png');
    $studentImage = $studentData['img_url'] ?? $defaultImage;

    if ($studentImage && !str_starts_with($studentImage, 'http')) {
        $studentImage = ltrim($studentImage, '/');
        $studentImage = str_starts_with($studentImage, 'storage/')
            ? asset($studentImage)
            : asset('storage/' . $studentImage);
    }

    $qrData = $studentData['custom_id'] ?? 'SA09001';
    
    // Only pending status AND admin can download
    $canDownload = ($student->status === 'pending' && $isAdmin);
    
    $statusColors = [
        'pending' => 'warning',
        'downloaded' => 'success',
        'active' => 'info',
        'deleted' => 'danger'
    ];
    $statusColor = $statusColors[$student->status] ?? 'secondary';
    
    $statusLabels = [
        'pending' => 'Pending',
        'downloaded' => 'Downloaded',
        'active' => 'Active',
        'deleted' => 'Deleted'
    ];
    $statusLabel = $statusLabels[$student->status] ?? ucfirst($student->status);
@endphp

<div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4 student-card"
     data-id="{{ $studentKey }}"
     data-student='@json($studentData)'
     data-card-id="{{ $student->id }}">
    
    <div class="premium-student-card h-100">
        <div class="student-top d-flex justify-content-between align-items-center">
            <div>
                <div class="student-id-badge mb-1">
                    <i class="fas fa-id-card me-1"></i>
                    {{ $studentData['custom_id'] }}
                </div>
                <div class="student-card-badge">
                    <small class="text-muted">
                        <i class="fas fa-credit-card me-1"></i>
                        Card No: {{ $student->card_no ?? 'N/A' }}
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-{{ $statusColor }} rounded-pill px-3 py-2">
                    <i class="fas {{ $student->status === 'pending' ? 'fa-clock' : ($student->status === 'downloaded' ? 'fa-check-circle' : ($student->status === 'active' ? 'fa-check' : 'fa-trash')) }} me-1"></i>
                    {{ $statusLabel }}
                </span>
                {{-- Checkbox only visible for admin --}}
                @if($isAdmin)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input student-select"
                           value="{{ $studentKey }}" id="student_{{ $studentKey }}"
                           {{ $canDownload ? '' : 'disabled' }}>
                </div>
                @endif
            </div>
        </div>

        <div class="id-card-preview-wrap">
            <div class="id-scale-box">
                @include('admin.student-id-cards.components.id-card-preview', [
                    'studentImage' => $studentImage,
                    'qrData' => $qrData,
                    'studentData' => $studentData,
                    'defaultImage' => $defaultImage
                ])
            </div>
        </div>

        <div class="student-action-area px-3 pb-3 pt-2">
            <div class="d-flex justify-content-between mb-2">
                <small class="text-muted">
                    <i class="fas fa-user me-1"></i>
                    {{ Str::limit($studentData['name'], 24) }}
                </small>
                <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    {{ $student->created_at->format('d/m/Y') }}
                </small>
            </div>
            
            {{-- Download button only visible for admin with pending status --}}
            @if($isAdmin && $canDownload)
                <button type="button" 
                        class="btn download-btn text-white w-100 download-single-btn"
                        data-card-id="{{ $student->id }}"
                        data-student-key="{{ $studentKey }}">
                    <i class="fas fa-download me-1"></i> Download ID Card
                </button>
            @elseif($isAdmin && !$canDownload && $student->status !== 'pending')
                <button type="button" 
                        class="btn btn-secondary w-100"
                        disabled>
                    <i class="fas fa-check-circle me-1"></i> {{ $statusLabel }}
                </button>
            @endif
            {{-- Non-admin users see nothing --}}
        </div>
    </div>
</div>