@extends('layouts.app')

@section('title', 'Student Payments')
@section('page-title', 'Student Payments')

@section('content')
    <div class="payments-page">

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <h4 class="mb-1 fw-bold">Payment Management</h4>
                    <p class="mb-0 text-muted">
                        Scan QR codes, search students, and process payments quickly.
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.payments.today-receipt') }}" class="btn btn-light border custom-btn">
                        <i class="bi bi-receipt me-1"></i> Today's Receipts
                    </a>
                </div>
            </div>
        </div>

        <!-- ALERT -->
        <div id="paymentAlert" class="alert alert-info d-none shadow-sm custom-alert" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-info-circle-fill fs-5 mt-1" id="alertIcon"></i>
                <div class="flex-grow-1">
                    <strong id="alertTitle">Information</strong>
                    <div id="alertMessage" class="mt-1"></div>
                </div>
                <button type="button" class="btn-close"
                    onclick="document.getElementById('paymentAlert').classList.add('d-none')"></button>
            </div>
        </div>

        <div class="row g-4">
            <!-- LEFT SIDE - QR Scanner & Manual Entry -->
            <div class="col-lg-4">
                <div class="panel-card h-100">
                    <div class="panel-header">
                        <h6 class="mb-0 fw-bold">QR Reader</h6>
                        <span class="badge bg-primary-subtle text-primary">Live</span>
                    </div>

                    <div class="panel-body">
                        <ul class="nav nav-pills custom-pills mb-3" role="tablist">
                            <li class="nav-item me-2">
                                <button class="nav-link active" id="scanTabBtn" data-bs-toggle="tab"
                                    data-bs-target="#scanTab" type="button" role="tab">
                                    <i class="bi bi-camera"></i> Scan QR
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="manualTabBtn" data-bs-toggle="tab" data-bs-target="#manualTab"
                                    type="button" role="tab">
                                    <i class="bi bi-keyboard"></i> Manual Entry
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- QR SCAN -->
                            <div class="tab-pane fade show active" id="scanTab" role="tabpanel">
                                <div id="qr-reader" class="qr-reader-box"></div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="button" class="btn btn-primary custom-btn" id="startScannerBtn">
                                        <i class="bi bi-camera-fill me-1"></i> Start Scanner
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary custom-btn d-none"
                                        id="stopScannerBtn">
                                        <i class="bi bi-stop-circle me-1"></i> Stop Scanner
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-3">Camera scanning requires HTTPS or localhost.</small>
                            </div>

                            <!-- MANUAL ENTRY -->
                            <div class="tab-pane fade" id="manualTab" role="tabpanel">
                                <form id="manualQrForm" autocomplete="off" novalidate>
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Student QR / Code</label>
                                        <div class="input-group custom-input-group">
                                            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" class="form-control custom-input" id="manualQrCode"
                                                name="qr_code" placeholder="Enter QR code" maxlength="150"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success custom-btn" id="manualReadBtn">
                                            <i class="bi bi-person-check-fill me-1"></i> Read Student
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE - Student Details & Classes -->
            <div class="col-lg-8">
                <!-- STUDENT DETAILS -->
                <div id="studentDetailsCard" class="panel-card d-none mb-4">
                    <div class="panel-header">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge"></i> Student Details</h6>
                        <span class="badge bg-secondary" id="qrTypeBadge">-</span>
                    </div>
                    <div class="panel-body">
                        <div class="row align-items-center g-4">
                            <div class="col-md-3 text-center">
                                <img id="studentImage" src="{{ asset('images/default-student.png') }}" class="student-image"
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
                                            <small class="text-muted">Mobile Number</small>
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

                <!-- CLASS DETAILS TABLE -->
                <div id="classDetailsCard" class="panel-card d-none mb-4">
                    <div class="panel-header">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-book"></i> Class Details & Payments</h6>
                        <span class="badge bg-primary-subtle text-primary">Ready for Payment</span>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table custom-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Category</th>
                                        <th>Teacher</th>
                                        <th>Grade</th>
                                        <th>Subject</th>
                                        <th>Final Fee</th>
                                        <th>Attendance</th>
                                        <th>Last Payment</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="classTableBody">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">No class details found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- EMPTY STATE -->
                <div id="emptyStateCard" class="panel-card">
                    <div class="panel-body text-center py-5">
                        <div class="empty-icon"><i class="bi bi-qr-code-scan"></i></div>
                        <h5 class="fw-bold mb-2">No Student Selected</h5>
                        <p class="text-muted mb-0">Scan a QR code or enter a student code manually to view payment details.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYMENT MODAL -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="paymentForm" autocomplete="off" novalidate>
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-cash-stack"></i> Make Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <input type="hidden" id="payStudentId">
                        <input type="hidden" id="payEnrollmentId">

                        <div class="detail-box mb-4">
                            <small class="text-muted">Class</small>
                            <div class="fw-bold" id="payClassName">-</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Month</label>
                                <input type="month" class="form-control custom-input" id="payMonth" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Amount</label>
                                <input type="number" class="form-control custom-input" id="payAmount" min="0" step="0.01"
                                    required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-semibold">Discount Amount</label>
                            <input type="number" class="form-control custom-input" id="payDiscount" min="0" step="0.01"
                                value="0">
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-semibold">Note (Optional)</label>
                            <textarea class="form-control custom-input" id="payNote" rows="2"
                                placeholder="Enter any additional notes..."></textarea>
                        </div>

                        <div class="alert alert-light border mt-4 mb-0">
                            <div class="small text-muted">Payment Method</div>
                            <div class="fw-bold"><i class="bi bi-cash"></i> Cash</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary custom-btn"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success custom-btn" id="savePaymentBtn">
                            <i class="bi bi-check-circle"></i> Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .payments-page {
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
            background: #fff;
        }

        .panel-body {
            padding: 1.25rem;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            transition: .2s ease;
            border: none;
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
            margin-right: 8px;
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
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            overflow: hidden;
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
        }

        .custom-input {
            border: none;
            min-height: 48px;
            border-radius: 14px !important;
        }

        .custom-input:focus {
            box-shadow: none;
            outline: none;
            border-color: #2563eb;
        }

        .student-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 18px;
            border: 3px solid #e2e8f0;
            background: #f8fafc;
        }

        .detail-box {
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 18px;
            padding: .9rem 1rem;
        }

        .custom-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: .75rem;
            text-transform: uppercase;
            padding: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .custom-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
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
                width: 100px;
                height: 100px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // API URLs
            const readApiUrl = "{{ route('api.payments.read') }}";
            const storePaymentApiUrl = "{{ route('api.student-payments.store') }}";
            const defaultStudentImage = "{{ asset('storage/uploads/male.png') }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            let html5QrCodeScanner = null;
            let scannerRunning = false;
            let processingRequest = false;
            let currentStudent = null;
            let currentClasses = [];
            let currentPaymentSource = 'manual'; // Track if payment came from scanner or manual

            // Helper Functions
            function showAlert(type, message, errors = null) {
                const alertBox = document.getElementById('paymentAlert');
                if (!alertBox) return;

                alertBox.className = `alert alert-${type} shadow-sm custom-alert`;
                alertBox.classList.remove('d-none');

                let title = 'Information';
                if (type === 'success') title = 'Success';
                else if (type === 'danger') title = 'Error';
                else if (type === 'warning') title = 'Warning';

                document.getElementById('alertTitle').textContent = title;
                document.getElementById('alertMessage').innerHTML = message;

                if (errors) {
                    console.error('Errors:', errors);
                }

                if (type === 'success') {
                    setTimeout(() => alertBox.classList.add('d-none'), 5000);
                }

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            async function readStudent(qrCode, source = 'manual') {
                const cleanedCode = String(qrCode || '').trim();

                if (!cleanedCode) {
                    showAlert('warning', 'Please enter or scan a valid QR code.');
                    return;
                }

                if (processingRequest) return;

                try {
                    processingRequest = true;

                    // Set payment source for mark_method
                    currentPaymentSource = source;

                    const response = await fetch(readApiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ qr_code: cleanedCode })
                    });

                    const result = await response.json();

                    if (response.ok && result.status === 'success') {
                        currentStudent = result.data.student;
                        currentClasses = result.data.classes || [];
                        renderStudent(currentStudent);
                        renderClasses(currentClasses);

                        document.getElementById('emptyStateCard').classList.add('d-none');
                        document.getElementById('studentDetailsCard').classList.remove('d-none');
                        document.getElementById('classDetailsCard').classList.remove('d-none');

                        showAlert('success', result.message || 'Student loaded successfully.');

                        if (source === 'manual' && document.getElementById('manualQrCode')) {
                            document.getElementById('manualQrCode').value = cleanedCode;
                        }
                    } else {
                        resetView();
                        showAlert('danger', result.message || 'Student not found. Please check the QR code.');
                    }
                } catch (error) {
                    console.error('Read student error:', error);
                    resetView();
                    showAlert('danger', 'Network error. Please check your connection and try again.');
                } finally {
                    processingRequest = false;
                }
            }

            function renderStudent(student) {
                const studentImg = document.getElementById('studentImage');
                studentImg.src = student.img_url || defaultStudentImage;
                studentImg.onerror = function () { this.src = defaultStudentImage; };

                document.getElementById('studentId').textContent = student.custom_id || student.id || '-';
                document.getElementById('studentName').textContent = student.initial_name || student.name || '-';
                document.getElementById('studentMobile').textContent = student.mobile || '-';
                document.getElementById('guardianMobile').textContent = student.guardian_mobile || '-';

                const qrTypeBadge = document.getElementById('qrTypeBadge');
                const isTemporary = student.qr_type === 'temporary';
                qrTypeBadge.textContent = isTemporary ? 'Temporary QR' : 'Permanent QR';
                qrTypeBadge.className = isTemporary ? 'badge bg-warning text-dark' : 'badge bg-success';
            }

            function renderClasses(classes) {
                const tbody = document.getElementById('classTableBody');

                if (!classes || !classes.length) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No active classes found for this student.</td></tr>';
                    return;
                }

                tbody.innerHTML = classes.map(cls => `
                        <tr>
                            <td class="fw-semibold">${escapeHtml(cls.class_name || '-')}</td>
                            <td>${escapeHtml(cls.category_name || '-')}</td>
                            <td>${escapeHtml(cls.teacher_initials || '-')}</td>
                            <td>${escapeHtml(cls.grade || '-')}</td>
                            <td>${escapeHtml(cls.subject || '-')}</td>
                            <td class="fw-bold text-primary">Rs. ${formatMoney(cls.final_fee || 0)}</td>
                            <td>
                                ${cls.attendance ?
                        `<div class="fw-bold">${cls.attendance.attended_classes || 0}/${cls.attendance.total_classes || 0}</div>
                                     <small class="text-muted">${cls.attendance.attendance_percentage || 0}%</small>` :
                        '<span class="text-muted">No data</span>'}
                              </td>
                            <td>
                                ${cls.last_payment ?
                        `<div class="fw-bold">Rs. ${formatMoney(cls.last_payment.amount)}</div>
                                     <small class="text-muted">${escapeHtml(cls.last_payment.payment_month || '-')}</small>
                                     <small class="text-info d-block">Receipt: ${escapeHtml(cls.last_payment.receipt_number || '-')}</small>` :
                        '<span class="badge bg-danger">No Payment Yet</span>'}
                              </td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="openPaymentModal(${cls.enrollment_id}, ${cls.final_fee || 0}, '${escapeHtml(cls.class_name)} - ${escapeHtml(cls.category_name)}')">
                                    <i class="bi bi-cash-stack"></i> Pay
                                </button>
                              </td>
                        </tr>
                    `).join('');
            }

            window.openPaymentModal = function (enrollmentId, amount, className) {
                if (!currentStudent) {
                    showAlert('warning', 'Please load a student first.');
                    return;
                }

                document.getElementById('payStudentId').value = currentStudent.id;
                document.getElementById('payEnrollmentId').value = enrollmentId;
                document.getElementById('payClassName').textContent = className;
                document.getElementById('payAmount').value = amount;
                document.getElementById('payDiscount').value = 0;
                document.getElementById('payNote').value = '';

                // Set current month as default
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                document.getElementById('payMonth').value = `${year}-${month}`;

                const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();
            };

            // Save Payment - FIXED with correct enum values
            document.getElementById('paymentForm')?.addEventListener('submit', async function (e) {
                e.preventDefault();

                const saveBtn = document.getElementById('savePaymentBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

                try {
                    // Get current date in YYYY-MM-DD format
                    const today = new Date();
                    const paidAt = today.toISOString().split('T')[0];

                    // Determine mark_method based on source
                    // Valid values: 'qr_mobile', 'qr_web', 'manual_mobile', 'manual_web'
                    let markMethod = 'manual_mobile'; // default

                    if (currentPaymentSource === 'scanner') {
                        // Check if it's mobile or web (you can detect based on screen size or user agent)
                        const isMobile = window.innerWidth <= 768;
                        markMethod = isMobile ? 'qr_mobile' : 'qr_web';
                    } else {
                        const isMobile = window.innerWidth <= 768;
                        markMethod = isMobile ? 'manual_mobile' : 'manual_web';
                    }

                    // Build the payments array
                    const payments = [{
                        student_id: parseInt(document.getElementById('payStudentId').value),
                        student_class_enrollment_id: parseInt(document.getElementById('payEnrollmentId').value),
                        amount: parseFloat(document.getElementById('payAmount').value),
                        discount_amount: parseFloat(document.getElementById('payDiscount').value) || 0,
                        payment_month: document.getElementById('payMonth').value,
                        paid_at: paidAt,
                        mark_method: markMethod, // Now using correct enum values
                        note: document.getElementById('payNote').value || null
                    }];

                    console.log('Sending payment data:', payments);
                    console.log('Mark Method used:', markMethod);

                    const response = await fetch(storePaymentApiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ payments: payments })
                    });

                    const result = await response.json();

                    if (response.ok && result.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                        showAlert('success', result.message || 'Payment saved successfully!');

                        // Refresh student data
                        if (currentStudent && currentStudent.custom_id) {
                            await readStudent(currentStudent.custom_id, 'refresh');
                        }
                    } else {
                        // Display validation errors
                        let errorMessage = result.message || 'Failed to save payment.';
                        if (result.errors) {
                            const errors = Object.values(result.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                        showAlert('danger', errorMessage);
                    }
                } catch (error) {
                    console.error('Save payment error:', error);
                    showAlert('danger', 'Network error. Please check your connection and try again.');
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Payment';
                }
            });

            function resetView() {
                currentStudent = null;
                currentClasses = [];

                document.getElementById('studentDetailsCard').classList.add('d-none');
                document.getElementById('classDetailsCard').classList.add('d-none');
                document.getElementById('emptyStateCard').classList.remove('d-none');

                document.getElementById('classTableBody').innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No class details found.</td></tr>';
                document.getElementById('studentImage').src = defaultStudentImage;
                document.getElementById('studentId').textContent = '-';
                document.getElementById('studentName').textContent = '-';
                document.getElementById('studentMobile').textContent = '-';
                document.getElementById('guardianMobile').textContent = '-';
            }

            function formatMoney(value) {
                if (!value && value !== 0) return '0.00';
                return Number(value).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function escapeHtml(str) {
                if (!str) return '-';
                return String(str).replace(/[&<>]/g, function (m) {
                    if (m === '&') return '&amp;';
                    if (m === '<') return '&lt;';
                    if (m === '>') return '&gt;';
                    return m;
                });
            }

            // QR Scanner
            document.getElementById('startScannerBtn')?.addEventListener('click', async function () {
                if (scannerRunning) return;

                try {
                    html5QrCodeScanner = new Html5Qrcode('qr-reader');

                    await html5QrCodeScanner.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => {
                            readStudent(decodedText, 'scanner');
                            if (html5QrCodeScanner) {
                                html5QrCodeScanner.stop();
                                scannerRunning = false;
                                document.getElementById('startScannerBtn').classList.remove('d-none');
                                document.getElementById('stopScannerBtn').classList.add('d-none');
                            }
                        },
                        (error) => { console.debug('Scan error:', error); }
                    );

                    scannerRunning = true;
                    this.classList.add('d-none');
                    document.getElementById('stopScannerBtn').classList.remove('d-none');
                    showAlert('info', 'Scanner started. Point camera at QR code.');
                } catch (err) {
                    console.error('Scanner error:', err);
                    showAlert('danger', 'Could not start camera. Please check permissions.');
                }
            });

            document.getElementById('stopScannerBtn')?.addEventListener('click', async function () {
                if (html5QrCodeScanner && scannerRunning) {
                    await html5QrCodeScanner.stop();
                    scannerRunning = false;
                    document.getElementById('startScannerBtn').classList.remove('d-none');
                    this.classList.add('d-none');
                    showAlert('info', 'Scanner stopped.');
                }
            });

            // Manual Entry Form
            document.getElementById('manualQrForm')?.addEventListener('submit', function (e) {
                e.preventDefault();
                const code = document.getElementById('manualQrCode').value.trim();
                if (code) {
                    readStudent(code, 'manual');
                } else {
                    showAlert('warning', 'Please enter a QR code.');
                }
            });
        });
    </script>
@endpush