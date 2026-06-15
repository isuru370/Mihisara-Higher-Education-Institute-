@extends('layouts.app')

@section('title', 'Attendance')
@section('page-title', 'Attendance')

@section('content')
    <div class="attendance-page">
        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <h4 class="mb-1 fw-bold">Attendance Management</h4>
                    <p class="mb-0 text-muted">
                        Scan a QR code or manually enter a student code to mark attendance.
                    </p>
                </div>

                <div class="hero-actions">
                    <button class="btn btn-light border custom-btn" disabled>
                        <i class="bi bi-printer me-1"></i>
                        Print
                    </button>

                    <button class="btn btn-light border custom-btn" disabled>
                        <i class="bi bi-file-earmark-excel-fill me-1"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- ALERT -->
        <div id="attendanceAlert" class="alert alert-info d-none shadow-sm custom-alert" data-persist-alert="true"
            role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-info-circle-fill fs-5 mt-1" id="alertIcon"></i>

                <div class="flex-grow-1">
                    <strong id="alertTitle">Info</strong>
                    <div id="alertMessage" class="mt-1"></div>
                </div>

                <button type="button" class="btn-close" aria-label="Close"
                    onclick="document.getElementById('attendanceAlert').classList.add('d-none')">
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- LEFT SIDE -->
            <div class="col-lg-4">
                <div class="panel-card h-100">
                    <div class="panel-header">
                        <h6 class="mb-0 fw-bold">QR Reader</h6>
                        <span class="badge bg-primary-subtle text-primary">Live</span>
                    </div>

                    <div class="panel-body">
                        <ul class="nav nav-pills custom-pills mb-3" role="tablist">
                            <li class="nav-item me-2">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#scanTab"
                                    type="button">
                                    Scan QR
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#manualTab" type="button">
                                    Manual Entry
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- SCAN -->
                            <div class="tab-pane fade show active" id="scanTab">
                                <div id="qr-reader" class="qr-reader-box"></div>

                                <div class="d-grid gap-2 mt-3">
                                    <button type="button" class="btn btn-primary custom-btn" id="startScannerBtn">
                                        <i class="bi bi-camera-fill me-1"></i>
                                        Start Scanner
                                    </button>

                                    <button type="button" class="btn btn-outline-secondary custom-btn d-none"
                                        id="stopScannerBtn">
                                        <i class="bi bi-stop-circle me-1"></i>
                                        Stop Scanner
                                    </button>
                                </div>

                                <small class="text-muted d-block mt-3">
                                    Camera scanning requires HTTPS or localhost.
                                </small>
                            </div>

                            <!-- MANUAL -->
                            <div class="tab-pane fade" id="manualTab">
                                <form id="manualQrForm" autocomplete="off">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="manualQrCode" class="form-label fw-semibold">
                                            Student QR / Code
                                        </label>

                                        <div class="input-group custom-input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-upc-scan"></i>
                                            </span>

                                            <input type="text" class="form-control custom-input" id="manualQrCode"
                                                placeholder="Enter QR code" maxlength="150">
                                        </div>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success custom-btn" id="manualReadBtn">
                                            <i class="bi bi-person-check-fill me-1"></i>
                                            Read Student
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="col-lg-8">
                <!-- EMPTY STATE -->
                <div id="emptyStateCard" class="panel-card">
                    <div class="panel-body text-center py-5">
                        <div class="empty-icon">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <h5 class="fw-bold mb-2">No Student Selected</h5>
                        <p class="text-muted mb-0">
                            Scan a QR code or enter a student code manually.
                        </p>
                    </div>
                </div>

                <!-- STUDENT DETAILS -->
                <div id="studentDetailsCard" class="panel-card d-none mb-4">
                    <div class="panel-header">
                        <h6 class="mb-0 fw-bold">Student Details</h6>
                        <span class="badge bg-secondary" id="qrTypeBadge">-</span>
                    </div>

                    <div class="panel-body">
                        <div class="row align-items-center g-4">
                            <div class="col-md-3 text-center">
                                <img id="studentImage" src="{{ asset('storage/uploads/male.png') }}" class="student-image"
                                    alt="Student Image">
                            </div>

                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="detail-box">
                                            <small class="text-muted">Student ID</small>
                                            <div class="fw-bold" id="studentId">-</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="detail-box">
                                            <small class="text-muted">Name</small>
                                            <div class="fw-bold" id="studentName">-</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="detail-box">
                                            <small class="text-muted">Mobile</small>
                                            <div class="fw-bold" id="studentMobile">-</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="detail-box">
                                            <small class="text-muted">Guardian Mobile</small>
                                            <div class="fw-bold" id="guardianMobile">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ATTENDANCE DETAILS -->
                <div id="attendanceDetailsCard" class="panel-card d-none">
                    <div class="panel-header">
                        <h6 class="mb-0 fw-bold">Attendance Details</h6>
                        <span class="badge bg-success-subtle text-success">Ready</span>
                    </div>

                    <div class="panel-body">
                        <div id="attendanceDetailsContent"></div>
                    </div>
                </div>

                <!-- ERROR DETAILS -->
                <div id="errorDetailsCard" class="panel-card mt-4 d-none">
                    <div class="panel-header bg-danger text-white rounded-top-4">
                        <h6 class="mb-0 fw-bold">Error Details</h6>
                    </div>
                    <div class="panel-body">
                        <div id="errorDetails"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ADD CLASS MODAL -->
    <div class="modal fade" id="addClassModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="addClassForm" autocomplete="off">
                    @csrf

                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Add Student To Class</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body pt-0">
                        <div class="alert alert-light border">
                            <div><strong>Student:</strong> <span id="modalStudentName">-</span></div>
                            <div><strong>Class:</strong> <span id="modalClassName">-</span></div>
                            <div><strong>Category:</strong> <span id="modalCategoryName">-</span></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Free Card</label>
                                <select id="modalIsFreeCard" class="form-select custom-input">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Custom Fee</label>
                                <input type="number" step="0.01" min="0" id="modalCustomFee"
                                    class="form-control custom-input">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Discount %</label>
                                <input type="number" step="0.01" min="0" max="100" id="modalDiscountPercentage"
                                    class="form-control custom-input">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Custom Fee Reason</label>
                                <select id="modalCustomFeeReason" class="form-select custom-input">
                                    <option value="">Select Reason</option>
                                    <option value="Student enrolled in multiple categories">Student enrolled in multiple
                                        categories</option>
                                    <option value="Special Discount">Special Discount</option>
                                    <option value="Scholarship">Scholarship</option>
                                    <option value="Sibling Discount">Sibling Discount</option>
                                    <option value="Staff Child">Staff Child</option>
                                    <option value="Promotional">Promotional</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Discount Reason</label>
                                <select id="modalDiscountReason" class="form-select custom-input">
                                    <option value="">Select Reason</option>
                                    <option value="Half Card">Half Card</option>
                                    <option value="Special Discount">Special Discount</option>
                                    <option value="Scholarship">Scholarship</option>
                                    <option value="Sibling Discount">Sibling Discount</option>
                                    <option value="Staff Child">Staff Child</option>
                                    <option value="Promotional">Promotional</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Enrolled At</label>
                                <input type="date" id="modalEnrolledAt" class="form-control custom-input"
                                    value="{{ now()->toDateString() }}">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary custom-btn" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary custom-btn" id="saveClassBtn">
                            Save Class
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .attendance-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .panel-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
            overflow: hidden;
        }

        .hero-card {
            padding: 1.35rem 1.5rem;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-actions {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
        }

        .panel-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: #fff;
        }

        .panel-body {
            padding: 1.25rem;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            border: none;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .custom-pills .nav-link {
            border-radius: 12px;
            font-weight: 600;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .custom-pills .nav-link.active {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            border-color: transparent;
        }

        .qr-reader-box {
            width: 100%;
            min-height: 320px;
            border-radius: 18px;
            overflow: hidden;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
        }

        .custom-input-group {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #fff;
        }

        .custom-input-group .input-group-text {
            background: #f8fafc;
            border: none;
            color: #64748b;
        }

        .custom-input,
        .custom-input:focus {
            border: none;
            box-shadow: none;
            min-height: 48px;
        }

        .student-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 18px;
            border: 3px solid #e2e8f0;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .06);
            background: #f8fafc;
        }

        .detail-box {
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 18px;
            padding: .9rem 1rem;
        }

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            text-transform: uppercase;
            padding: 1rem;
        }

        .attendance-detail-card {
            background: #fff;
            border-radius: 24px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 16px;
            height: 100%;
            transition: .2s ease;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
        }

        .info-card small {
            color: #64748b;
            display: block;
            margin-bottom: 8px;
        }

        .info-card h6 {
            margin: 0;
            font-weight: 700;
            color: #0f172a;
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

        .empty-icon {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .custom-alert {
            border-radius: 18px;
        }

        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-actions {
                width: 100%;
            }

            .hero-actions .btn {
                flex: 1;
            }

            .student-image {
                width: 130px;
                height: 130px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const readApiUrl = @json(route('api.attendance.read'));
            const storeAttendanceApiUrl = @json(route('api.attendance.store'));
            const storeEnrollmentApiUrl = @json(route('api.student-class-enrollments.store'));
            const defaultStudentImage = @json(asset('storage/uploads/male.png'));

            const classScheduleId = @json(request('class_schedule_id'));
            const studentClassId = @json(request('student_class_id'));
            const classCategoryFeeId = @json(request('class_category_fee_id'));

            const alertBox = document.getElementById('attendanceAlert');
            const alertIcon = document.getElementById('alertIcon');
            const alertTitle = document.getElementById('alertTitle');
            const alertMessage = document.getElementById('alertMessage');
            const errorDetailsCard = document.getElementById('errorDetailsCard');
            const errorDetails = document.getElementById('errorDetails');

            const emptyStateCard = document.getElementById('emptyStateCard');
            const studentDetailsCard = document.getElementById('studentDetailsCard');
            const attendanceDetailsCard = document.getElementById('attendanceDetailsCard');

            const studentImage = document.getElementById('studentImage');
            const studentId = document.getElementById('studentId');
            const studentName = document.getElementById('studentName');
            const studentMobile = document.getElementById('studentMobile');
            const guardianMobile = document.getElementById('guardianMobile');
            const qrTypeBadge = document.getElementById('qrTypeBadge');
            const attendanceDetailsContent = document.getElementById('attendanceDetailsContent');

            const manualQrForm = document.getElementById('manualQrForm');
            const manualQrCode = document.getElementById('manualQrCode');
            const manualReadBtn = document.getElementById('manualReadBtn');

            const startScannerBtn = document.getElementById('startScannerBtn');
            const stopScannerBtn = document.getElementById('stopScannerBtn');

            const addClassModalEl = document.getElementById('addClassModal');
            const addClassModal = addClassModalEl ? new bootstrap.Modal(addClassModalEl) : null;
            const addClassForm = document.getElementById('addClassForm');
            const saveClassBtn = document.getElementById('saveClassBtn');

            const modalStudentName = document.getElementById('modalStudentName');
            const modalClassName = document.getElementById('modalClassName');
            const modalCategoryName = document.getElementById('modalCategoryName');
            const modalIsFreeCard = document.getElementById('modalIsFreeCard');
            const modalCustomFee = document.getElementById('modalCustomFee');
            const modalCustomFeeReason = document.getElementById('modalCustomFeeReason');
            const modalDiscountPercentage = document.getElementById('modalDiscountPercentage');
            const modalDiscountReason = document.getElementById('modalDiscountReason');
            const modalEnrolledAt = document.getElementById('modalEnrolledAt');

            let html5QrCode = null;
            let scannerRunning = false;
            let processing = false;
            let lastScannedCode = null;
            let lastScanTime = 0;
            let currentQrCode = null;
            let currentStudent = null;
            let currentEnrollment = null;
            let currentMarkMethod = 'qr_web';

            const SCAN_COOLDOWN_MS = 1200;

            function showAlert(type, message) {
                const safeType = ['success', 'danger', 'warning', 'info'].includes(type) ? type : 'info';

                alertBox.className = `alert alert-${safeType} shadow-sm custom-alert`;
                alertBox.classList.remove('d-none');
                alertBox.style.display = 'block';

                if (alertIcon) {
                    alertIcon.className =
                        safeType === 'success' ? 'bi bi-check-circle-fill fs-5 mt-1' :
                            safeType === 'danger' ? 'bi bi-exclamation-triangle-fill fs-5 mt-1' :
                                safeType === 'warning' ? 'bi bi-exclamation-circle-fill fs-5 mt-1' :
                                    'bi bi-info-circle-fill fs-5 mt-1';
                }

                alertTitle.textContent =
                    safeType === 'success' ? 'Success' :
                        safeType === 'danger' ? 'Error' :
                            safeType === 'warning' ? 'Warning' : 'Info';

                alertMessage.textContent = message || '';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function hideAlert() {
                alertBox.classList.add('d-none');
                alertMessage.textContent = '';
            }

            function hideErrorDetails() {
                if (errorDetailsCard) errorDetailsCard.classList.add('d-none');
                if (errorDetails) errorDetails.innerHTML = '';
            }

            function showErrorDetails(message) {
                if (!errorDetailsCard || !errorDetails) return;

                errorDetails.innerHTML = `
                                                    <div class="alert alert-danger mb-0">
                                                        ${escapeHtml(message || 'Unknown error')}
                                                    </div>
                                                `;
                errorDetailsCard.classList.remove('d-none');
            }

            function setReadButtonLoading(isLoading) {
                if (!manualReadBtn) return;

                manualReadBtn.disabled = isLoading;
                manualReadBtn.innerHTML = isLoading
                    ? '<span class="spinner-border spinner-border-sm me-1"></span>Processing...'
                    : '<i class="bi bi-person-check-fill me-1"></i>Read Student';
            }

            function setMarkButtonLoading(isLoading) {
                const button = document.getElementById('markAttendanceBtn');
                if (!button) return;

                button.disabled = isLoading;
                button.innerHTML = isLoading
                    ? '<span class="spinner-border spinner-border-sm me-1"></span>Marking...'
                    : 'Mark Attendance';
            }

            async function postJson(url, payload) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                let result = {};
                try {
                    result = await response.json();
                } catch (error) {
                    result = { message: 'Invalid server response.' };
                }

                return { ok: response.ok, status: response.status, result };
            }

            function hasEnrollment(enrollment) {
                return Boolean(
                    enrollment && (
                        enrollment.is_enrolled === true ||
                        String(enrollment.status || '').toLowerCase() === 'enrolled' ||
                        enrollment.enrollment_id !== null
                    )
                );
            }

            function normalizeEnrollment(enrollment) {
                const e = enrollment || {};

                return {
                    status: String(e.status || e.enrollment_status || '').toLowerCase(),
                    is_enrolled: e.is_enrolled === true || e.is_enrolled === 1 || String(e.status || '').toLowerCase() === 'enrolled' || !!e.enrollment_id,
                    enrollment_id: e.enrollment_id ?? e.id ?? null,
                    student_class_id: e.student_class_id ?? e.studentClass?.id ?? null,
                    class_category_fee_id: e.class_category_fee_id ?? e.classCategoryFee?.id ?? null,
                    class_name: e.class_name ?? e.studentClass?.class_name ?? e.studentClass?.name ?? '-',
                    grade: e.grade ?? e.grade_name ?? e.studentClass?.grade?.grade_name ?? e.studentClass?.grade?.name ?? '-',
                    teacher: e.teacher ?? e.teacher_name ?? e.studentClass?.teacher?.name ?? '-',
                    category_name: e.category_name ?? e.classCategoryFee?.category?.category_name ?? e.classCategoryFee?.category?.name ?? '-',
                    default_fee: e.default_fee ?? e.classCategoryFee?.default_fee ?? '-',
                    final_fee: e.final_fee ?? e.classCategoryFee?.final_fee ?? e.default_fee ?? '-',
                };
            }

            function getIssueTuteValue() {
                const checkbox = document.getElementById('issueTuteCheckbox');
                return checkbox && checkbox.checked ? 1 : 0;
            }

            async function readStudent(qrCode, method = 'qr_web') {
                const code = String(qrCode || '').trim();

                currentMarkMethod = method;
                hideAlert();
                hideErrorDetails();

                if (!code) {
                    showAlert('warning', 'Please enter or scan QR code.');
                    return;
                }

                if (!classScheduleId || !studentClassId || !classCategoryFeeId) {
                    showAlert('danger', 'Class schedule, class ID or category fee ID missing.');
                    return;
                }

                if (processing) return;

                try {
                    processing = true;
                    setReadButtonLoading(true);

                    const response = await postJson(readApiUrl, {
                        qr_code: code,
                        student_class_id: studentClassId,
                        class_category_fee_id: classCategoryFeeId,
                    });

                    const result = response.result || {};
                    const isSuccess = response.ok && (result.status === 'success' || result.success === true);

                    if (!isSuccess) {
                        resetView();
                        showAlert('danger', result.message || 'Student read failed.');
                        showErrorDetails(result.message || 'Student read failed.');
                        return;
                    }

                    const payload = result.data || {};

                    currentQrCode = code;
                    currentStudent = payload.student || null;
                    currentEnrollment = pickEnrollmentFromResponse(payload);

                    console.log('SELECTED ENROLLMENT =>', currentEnrollment);

                    renderStudent(currentStudent);
                    renderAttendanceDetails(payload);

                    hideErrorDetails();
                    showAlert('success', result.message || 'Student attendance details loaded successfully.');
                    manualQrCode.value = code;
                } catch (error) {
                    console.error(error);
                    resetView();
                    showAlert('danger', 'Something went wrong while reading student.');
                    showErrorDetails('Something went wrong while reading student.');
                } finally {
                    processing = false;
                    setReadButtonLoading(false);
                }
            }

            function renderStudent(student) {
                if (!student) {
                    resetView();
                    return;
                }

                emptyStateCard.classList.add('d-none');
                studentDetailsCard.classList.remove('d-none');
                attendanceDetailsCard.classList.remove('d-none');

                studentImage.src = student.img_url || defaultStudentImage;
                studentImage.onerror = function () {
                    this.src = defaultStudentImage;
                    this.onerror = null;
                };

                studentId.textContent = student.custom_id || student.id || '-';
                studentName.textContent = student.initial_name || '-';
                studentMobile.textContent = student.mobile || '-';
                guardianMobile.textContent = student.guardian_mobile || '-';

                qrTypeBadge.textContent = student.qr_type === 'temporary' ? 'Temporary QR' : 'Permanent QR';
                qrTypeBadge.className = student.qr_type === 'temporary' ? 'badge bg-warning text-dark' : 'badge bg-success';
            }

            function renderAttendanceDetails(data) {
                const selectedEnrollmentRaw = pickEnrollmentFromResponse(data);
                const enrollment = normalizeEnrollment(selectedEnrollmentRaw);

                const attendance = data?.attendance || {};
                const lastPayment = data?.last_payment || null;
                const tute = data?.tute || null;

                const enrollmentExists = hasEnrollment(enrollment);
                const statusLabel = enrollmentExists ? 'Enrolled' : 'New Student';
                const statusBadgeClass = enrollmentExists ? 'badge bg-success' : 'badge bg-warning text-dark';

                const studentClassIdValue = enrollment.student_class_id ?? studentClassId ?? '-';
                const feeIdValue = enrollment.class_category_fee_id ?? classCategoryFeeId ?? '-';

                const className = enrollment.class_name;
                const grade = enrollment.grade;
                const teacher = enrollment.teacher;
                const categoryName = enrollment.category_name;
                const finalFeeValue = enrollment.final_fee;
                const defaultFeeValue = enrollment.default_fee;

                const paymentHtml = lastPayment
                    ? `
                <div class="fw-bold">${formatMoney(lastPayment.amount)}</div>
                <small class="text-muted d-block">Month: ${escapeHtml(lastPayment.payment_month)}</small>
                <small class="text-info d-block">Paid: ${escapeHtml(lastPayment.paid_at)}</small>
            `
                    : '<span class="badge bg-danger">No Payment Yet</span>';

                const tuteHtml = tute?.is_issued
                    ? '<span class="badge bg-info text-dark">Tute issued</span>'
                    : '';

                attendanceDetailsContent.innerHTML = `
            <div class="attendance-detail-card">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="info-card">
                            <small>Status</small>
                            <div class="mt-2">
                                <span class="${statusBadgeClass}">${statusLabel}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-card">
                            <small>Class</small>
                            <h6>${escapeHtml(className)}</h6>
                            <span class="text-muted">Class ID: ${escapeHtml(studentClassIdValue)}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="info-card">
                            <small>Grade</small>
                            <h6>${escapeHtml(grade)}</h6>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-card">
                            <small>Teacher</small>
                            <h6>${escapeHtml(teacher)}</h6>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-card">
                            <small>Category</small>
                            <h6>${escapeHtml(categoryName)}</h6>
                            <span class="text-muted">Fee ID: ${escapeHtml(feeIdValue)}</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-card">
                            <small>Final Fee</small>
                            <h6>${formatMoney(finalFeeValue)}</h6>
                            <span class="text-muted">Default: ${formatMoney(defaultFeeValue)}</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-card">
                            <small>Last Payment</small>
                            ${paymentHtml}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-card">
                            <small>Attendance</small>
                            <h6>${attendance.attended_classes ?? 0} / ${attendance.total_classes ?? 0}</h6>
                            <span class="text-muted">
                                ${attendance.attendance_percentage ?? 0}%
                                ${attendance.month ? '(' + escapeHtml(attendance.month) + ')' : ''}
                            </span>
                            ${tuteHtml ? `<div class="mt-2">${tuteHtml}</div>` : ''}
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                    ${enrollmentExists ? `
                        <div class="form-check d-flex align-items-center gap-2 me-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="issueTuteCheckbox"
                                ${tute?.is_issued ? 'checked' : ''}
                            >
                            <label class="form-check-label" for="issueTuteCheckbox">
                                Issue Tute
                            </label>
                        </div>
                    ` : ''}

                    <button type="button" class="btn btn-primary custom-btn" id="addClassBtn">
                        <i class="bi bi-plus-circle me-1"></i>
                        Add Class
                    </button>

                    <button type="button" class="btn btn-success custom-btn" id="markAttendanceBtn">
                        <i class="bi bi-check-circle me-1"></i>
                        Mark Attendance
                    </button>
                </div>
            </div>
        `;
            }

            function pickEnrollmentFromResponse(data) {
                const enrollments = Array.isArray(data?.enrollments) ? data.enrollments : [];

                if (!enrollments.length) {
                    return {};
                }

                const matched = enrollments.find((item) => {
                    const itemStudentClassId = Number(item?.student_class_id ?? item?.studentClass?.id ?? 0);
                    const itemFeeId = Number(item?.class_category_fee_id ?? item?.classCategoryFee?.id ?? 0);

                    return (
                        itemStudentClassId === Number(studentClassId) &&
                        itemFeeId === Number(classCategoryFeeId)
                    );
                });

                return matched || enrollments[0] || {};
            }

            function normalizeEnrollment(enrollment) {
                const e = enrollment || {};

                return {
                    status: String(e.status || e.enrollment_status || '').toLowerCase(),
                    is_enrolled:
                        e.is_enrolled === true ||
                        e.is_enrolled === 1 ||
                        String(e.status || '').toLowerCase() === 'enrolled' ||
                        !!e.enrollment_id,
                    enrollment_id: e.enrollment_id ?? e.id ?? null,
                    student_class_id: e.student_class_id ?? e.studentClass?.id ?? null,
                    class_category_fee_id: e.class_category_fee_id ?? e.classCategoryFee?.id ?? null,
                    class_name: e.class_name ?? e.studentClass?.class_name ?? e.studentClass?.name ?? '-',
                    grade: e.grade ?? e.grade_name ?? e.studentClass?.grade?.grade_name ?? e.studentClass?.grade?.name ?? '-',
                    teacher: e.teacher ?? e.teacher_name ?? e.studentClass?.teacher?.name ?? '-',
                    category_name: e.category_name ?? e.classCategoryFee?.category?.category_name ?? e.classCategoryFee?.category?.name ?? '-',
                    default_fee: e.default_fee ?? e.classCategoryFee?.default_fee ?? '-',
                    final_fee: e.final_fee ?? e.classCategoryFee?.final_fee ?? e.default_fee ?? '-',
                };
            }

            function renderLastPayment(payment) {
                if (!payment) {
                    return '<span class="badge bg-danger">No Payment Yet</span>';
                }

                return `
                                                    <div class="fw-bold">${formatMoney(payment.amount)}</div>
                                                    <small class="text-muted">Month: ${escapeHtml(payment.payment_month)}</small><br>
                                                    <small class="text-info">Paid: ${escapeHtml(payment.paid_at)}</small>
                                                `;
            }

            async function markAttendance() {
                if (!currentQrCode) {
                    showAlert('warning', 'Please read a student first.');
                    return;
                }

                if (!classScheduleId) {
                    showAlert('danger', 'Class schedule ID is missing.');
                    return;
                }

                if (!currentStudent?.id) {
                    showAlert('danger', 'Student ID is missing.');
                    return;
                }

                const issueTute = getIssueTuteValue();
                const enrollmentExists = hasEnrollment(currentEnrollment);

                if (issueTute && !enrollmentExists) {
                    showAlert('warning', 'Student must be enrolled before issuing a tute.');
                    return;
                }

                try {
                    setMarkButtonLoading(true);

                    const response = await postJson(storeAttendanceApiUrl, {
                        student_id: currentStudent.id,
                        qr_code: currentQrCode,
                        class_schedule_id: classScheduleId,
                        student_class_id: studentClassId,
                        class_category_fee_id: classCategoryFeeId,
                        mark_method: currentMarkMethod,
                        mark_tute: issueTute,
                        note: null,
                    });

                    const result = response.result || {};
                    const isSuccess = response.ok && (result.status === 'success' || result.success === true);

                    if (!isSuccess) {
                        const validationMessage =
                            result.message ||
                            (result.errors ? Object.values(result.errors).flat().join(' ') : 'Attendance mark failed.');

                        showAlert('danger', validationMessage);
                        return;
                    }

                    showAlert('success', result.message || 'Attendance marked successfully.');
                    await readStudent(currentQrCode, currentMarkMethod);
                } catch (error) {
                    console.error(error);
                    showAlert('danger', 'Something went wrong while marking attendance.');
                } finally {
                    setMarkButtonLoading(false);
                }
            }

            function openAddClassModal() {
                if (!currentStudent) {
                    showAlert('warning', 'Please read a student first.');
                    return;
                }

                modalStudentName.textContent = currentStudent.initial_name || currentStudent.custom_id || '-';
                modalClassName.textContent = currentEnrollment?.class_name || '-';
                modalCategoryName.textContent = currentEnrollment?.category_name || '-';

                modalIsFreeCard.value = '0';
                modalCustomFee.value = '';
                modalCustomFeeReason.value = '';
                modalDiscountPercentage.value = '';
                modalDiscountReason.value = '';
                modalEnrolledAt.value = new Date().toISOString().slice(0, 10);

                addClassModal?.show();
            }

            async function saveClassEnrollment() {
                if (!currentStudent) {
                    showAlert('warning', 'Please read a student first.');
                    return;
                }

                try {
                    saveClassBtn.disabled = true;
                    saveClassBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

                    const payload = {
                        student_id: currentStudent.id,
                        student_class_id: studentClassId,
                        class_category_fee_id: classCategoryFeeId,
                        is_free_card: modalIsFreeCard.value === '1' ? 1 : 0,
                        custom_fee: modalCustomFee.value || null,
                        custom_fee_reason: modalCustomFeeReason.value || null,
                        discount_percentage: modalDiscountPercentage.value || null,
                        discount_reason: modalDiscountReason.value || null,
                        enrolled_at: modalEnrolledAt.value || null,
                        note: null,
                    };

                    const response = await postJson(storeEnrollmentApiUrl, payload);
                    const result = response.result || {};
                    const isSuccess = response.ok && (result.status === 'success' || result.success === true);

                    if (!isSuccess) {
                        showAlert('danger', result.message || 'Student class enrollment failed.');
                        return;
                    }

                    addClassModal?.hide();
                    showAlert('success', 'Student enrolled successfully.');

                    setTimeout(async () => {
                        if (currentQrCode) {
                            await readStudent(currentQrCode, currentMarkMethod);
                        }
                    }, 350);
                } catch (error) {
                    console.error(error);
                    showAlert('danger', 'Something went wrong while saving class.');
                } finally {
                    saveClassBtn.disabled = false;
                    saveClassBtn.innerHTML = 'Save Class';
                }
            }

            function resetView() {
                currentQrCode = null;
                currentStudent = null;
                currentEnrollment = null;

                emptyStateCard.classList.remove('d-none');
                studentDetailsCard.classList.add('d-none');
                attendanceDetailsCard.classList.add('d-none');

                if (attendanceDetailsContent) {
                    attendanceDetailsContent.innerHTML = '';
                }

                hideErrorDetails();
            }

            function formatMoney(value) {
                if (value === null || value === undefined || value === '') return '-';

                const amount = Number(value);
                if (Number.isNaN(amount)) return '-';

                return 'Rs. ' + amount.toLocaleString('en-LK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function escapeHtml(value) {
                if (value === null || value === undefined || value === '') return '-';

                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            async function startScanner() {
                if (scannerRunning) return;

                if (typeof Html5Qrcode === 'undefined') {
                    showAlert('danger', 'QR scanner library failed to load.');
                    return;
                }

                try {
                    html5QrCode = new Html5Qrcode('qr-reader');

                    startScannerBtn.disabled = true;
                    startScannerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Starting Scanner...';

                    await html5QrCode.start(
                        { facingMode: 'environment' },
                        {
                            fps: 10,
                            qrbox: 250
                        },
                        function (decodedText) {
                            const code = String(decodedText || '').trim();
                            const now = Date.now();

                            if (!code) return;

                            if (code === lastScannedCode && now - lastScanTime < SCAN_COOLDOWN_MS) {
                                return;
                            }

                            lastScannedCode = code;
                            lastScanTime = now;

                            readStudent(code, 'qr_web');
                        }
                    );

                    scannerRunning = true;
                    startScannerBtn.innerHTML = '<i class="bi bi-camera-video-fill me-1"></i>Scanner Running';
                    startScannerBtn.disabled = true;
                    stopScannerBtn.classList.remove('d-none');
                } catch (error) {
                    console.error(error);
                    showAlert('danger', 'Could not start scanner.');
                    startScannerBtn.disabled = false;
                    startScannerBtn.innerHTML = '<i class="bi bi-camera-fill me-1"></i>Start Scanner';
                }
            }

            async function stopScanner() {
                if (html5QrCode && scannerRunning) {
                    await html5QrCode.stop();
                    await html5QrCode.clear();
                }

                scannerRunning = false;
                html5QrCode = null;
                lastScannedCode = null;
                lastScanTime = 0;

                startScannerBtn.disabled = false;
                startScannerBtn.innerHTML = '<i class="bi bi-camera-fill me-1"></i>Start Scanner';
                stopScannerBtn.classList.add('d-none');
            }

            manualQrForm.addEventListener('submit', function (event) {
                event.preventDefault();
                readStudent(manualQrCode.value, 'manual_web');
            });

            addClassForm.addEventListener('submit', function (event) {
                event.preventDefault();
                saveClassEnrollment();
            });

            startScannerBtn.addEventListener('click', startScanner);
            stopScannerBtn.addEventListener('click', stopScanner);

            attendanceDetailsContent.addEventListener('click', function (event) {
                if (event.target.closest('#addClassBtn')) {
                    openAddClassModal();
                }

                if (event.target.closest('#markAttendanceBtn')) {
                    markAttendance();
                }
            });

            window.addEventListener('beforeunload', function () {
                if (html5QrCode && scannerRunning) {
                    html5QrCode.stop().catch(function () { });
                }
            });
        });
    </script>
@endpush