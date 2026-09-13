@push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /*
            |--------------------------------------------------------------------------
            | Elements
            |--------------------------------------------------------------------------
            */

            const loading = document.getElementById('payment-loading');
            const errorBox = document.getElementById('payment-error');
            const studentSection = document.getElementById('student-section');
            const classesBody = document.getElementById('classes-table-body');
            const bulkButton = document.getElementById('bulk-payment-btn');
            const selectAll = document.getElementById('select-all');

            /*
            |--------------------------------------------------------------------------
            | State
            |--------------------------------------------------------------------------
            */

            let currentStudent = null;
            let currentMarkMethod = null;
            let currentClasses = [];
            let selectedEnrollmentIds = [];
            let html5QrCode = null;

            /*
            |--------------------------------------------------------------------------
            | URLs
            |--------------------------------------------------------------------------
            */

            const readUrl = @json(route('admin.new-payment.read'));
            const payUrl = @json(route('admin.new-payment.pay'));
            const bulkPayUrl = @json(route('admin.new-payment.bulk-pay'));

            /*
            |--------------------------------------------------------------------------
            | Helpers
            |--------------------------------------------------------------------------
            */

            function showLoading() {
                if (loading) loading.classList.remove('d-none');
                if (errorBox) errorBox.classList.add('d-none');
            }

            function hideLoading() {
                if (loading) loading.classList.add('d-none');
            }

            function showError(message) {
                hideLoading();
                if (errorBox) {
                    errorBox.textContent = message;
                    errorBox.classList.remove('d-none');
                }
            }

            function clearError() {
                if (errorBox) {
                    errorBox.classList.add('d-none');
                    errorBox.textContent = '';
                }
            }

            function formatMoney(value) {
                return Number(value || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            /*
            |--------------------------------------------------------------------------
            | Class Fee Resolver
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | PaymentDataBuilder returns:
            |   class_fee
            |   total_fee
            |   final_fee
            |
            | For safety we also support:
            |   fee.class_fee (legacy)
            |
            */

            function resolveClassFee(item) {
                if (!item) return 0;

                const candidates = [
                    item.class_fee,
                    item.final_fee,
                    item.total_fee,
                    item.fee?.class_fee,
                    item.fee?.final_fee
                ];

                for (const candidate of candidates) {
                    const value = Number(candidate ?? 0);

                    if (value > 0) {
                        return value;
                    }
                }

                return 0;
            }

            /*
            |--------------------------------------------------------------------------
            | Receipt Format Helpers
            |--------------------------------------------------------------------------
            */

            function formatReceiptMonth(value) {
                if (!value) return '-';

                try {
                    let normalized = String(value);

                    // "2026-08" -> "2026-08-01"
                    if (/^\d{4}-\d{2}$/.test(normalized)) {
                        normalized = normalized + '-01';
                    }

                    // ISO string like "2026-08-31T18:30:00.000000Z"
                    // -> strip timezone, treat as local date
                    if (/T\d{2}:\d{2}/.test(normalized)) {
                        normalized = normalized.split('T')[0];
                    }

                    // Now "2026-08-01" -> parse as local date
                    const parts = normalized.split('-');

                    if (parts.length === 3) {
                        const d = new Date(
                            parseInt(parts[0]),
                            parseInt(parts[1]) - 1,
                            parseInt(parts[2])
                        );

                        if (!isNaN(d.getTime())) {
                            return d.toLocaleDateString('en-US', {
                                month: 'long',
                                year: 'numeric'
                            });
                        }
                    }

                    return String(value);
                } catch (e) {
                    return String(value);
                }
            }

            function formatReceiptDate(value) {
                if (!value) return '-';

                try {
                    let normalized = String(value);

                    // Strip time part if present
                    if (/T\d{2}:\d{2}/.test(normalized)) {
                        normalized = normalized.split('T')[0];
                    }

                    const parts = normalized.split('-');

                    if (parts.length === 3) {
                        const d = new Date(
                            parseInt(parts[0]),
                            parseInt(parts[1]) - 1,
                            parseInt(parts[2])
                        );

                        if (!isNaN(d.getTime())) {
                            return d.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric'
                            });
                        }
                    }

                    return String(value);
                } catch (e) {
                    return String(value);
                }
            }

            function formatReceiptTime(value) {
                if (!value) return '-';

                try {
                    // Already HH:MM:SS
                    if (/^\d{2}:\d{2}(:\d{2})?$/.test(String(value))) {
                        return String(value);
                    }

                    // Extract time from ISO string
                    if (/T\d{2}:\d{2}/.test(String(value))) {
                        const timePart = String(value).split('T')[1] || '';
                        const clean = timePart.replace(/Z$/, '').split('.')[0];

                        if (/^\d{2}:\d{2}:\d{2}$/.test(clean)) {
                            return clean;
                        }
                    }

                    const d = new Date(value);

                    if (isNaN(d.getTime())) {
                        return String(value);
                    }

                    return d.toLocaleTimeString('en-GB', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    });
                } catch (e) {
                    return String(value);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Read Student
            |--------------------------------------------------------------------------
            */

            async function readStudent(code, markMethod) {

                code = String(code || '').trim();

                if (!code) {
                    showError('Please enter Student ID or scan QR.');
                    return;
                }

                showLoading();
                clearError();
                currentMarkMethod = markMethod;

                try {

                    const response = await fetch(readUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            code: code
                        })
                    });

                    const result = await response.json();

                    if (!response.ok || result.status !== 'success') {
                        throw new Error(result.message || 'Student not found.');
                    }

                    currentStudent = result.data.student;
                    currentClasses = result.data.classes || [];

                    renderStudent();
                    renderClasses();

                    if (markMethod === 'qr_web') {
                        const firstUnpaid = currentClasses.find(function(item) {
                            return item.payment_status !== 'paid';
                        });
                        if (firstUnpaid) {
                            setTimeout(function() {
                                openPaymentModal([Number(firstUnpaid.enrollment_id)]);
                            }, 200);
                        }
                    }

                } catch (error) {
                    if (studentSection) studentSection.classList.add('d-none');
                    showError(error.message);
                } finally {
                    hideLoading();
                }

            }

            /*
            |--------------------------------------------------------------------------
            | Render Student
            |--------------------------------------------------------------------------
            */

            function renderStudent() {

                if (!currentStudent) return;

                const student = currentStudent;

                const nameEl = document.getElementById('student-name');
                const idEl = document.getElementById('student-custom-id');
                const mobileEl = document.getElementById('student-mobile');
                const guardianEl = document.getElementById('student-guardian');
                const imageEl = document.getElementById('student-image');

                if (nameEl) nameEl.textContent = student.initial_name || student.full_name || '-';
                if (idEl) idEl.textContent = student.custom_id || '-';
                if (mobileEl) mobileEl.textContent = student.mobile || '-';
                if (guardianEl) guardianEl.textContent = student.guardian_name || student.guardian_mobile || '-';
                if (imageEl) {
                    imageEl.src = student.image_url || '{{ asset('images/default-student.png') }}';
                }

                if (studentSection) studentSection.classList.remove('d-none');

            }

            /*
            |--------------------------------------------------------------------------
            | Render Classes
            |--------------------------------------------------------------------------
            */

            function renderClasses() {

                const container =
                    document.getElementById(
                        'classes-card-container'
                    );

                if (!container) {

                    console.error(
                        'Classes card container not found.'
                    );

                    return;
                }


                container.innerHTML = '';

                selectedEnrollmentIds = [];

                if (selectAll) {
                    selectAll.checked = false;
                }

                updateBulkButton();


                /*
                |--------------------------------------------------------------------------
                | No Classes
                |--------------------------------------------------------------------------
                */

                if (!currentClasses.length) {

                    container.innerHTML = `

            <div class="col-12">

                <div class="classes-empty-state">

                    <div class="classes-empty-icon">

                        <i class="bi bi-journal-x"></i>

                    </div>

                    <div class="fw-semibold text-muted">
                        No active classes found
                    </div>

                    <small class="text-muted">
                        This student has no active class enrollments.
                    </small>

                </div>

            </div>

        `;

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Render Cards
                |--------------------------------------------------------------------------
                */

                currentClasses.forEach(function(item) {

                    const status =
                        item.payment_status || 'unpaid';


                    const statusClass =
                        status === 'paid' ?
                        'payment-status-paid' :
                        'payment-status-unpaid';


                    /*
                    |--------------------------------------------------------------------------
                    | Last Payment
                    |--------------------------------------------------------------------------
                    */

                    let lastPaymentHtml = '';


                    if (item.last_payment) {

                        const last =
                            item.last_payment;


                        lastPaymentHtml = `

                <div class="last-payment-box">

                    <div class="last-payment-header">

                        <span class="last-payment-label">

                            <i class="bi bi-receipt me-1"></i>

                            Last Payment

                        </span>

                    </div>


                    <div class="last-payment-content">

                        <div>

                            <div class="small text-muted">
                                Month
                            </div>

                            <strong>
                                ${escapeHtml(
                                    last.payment_month_name ||
                                    last.payment_month ||
                                    '-'
                                )}
                            </strong>

                        </div>


                        <div class="text-end">

                            <div class="small text-muted">
                                Amount
                            </div>

                            <strong class="text-success">

                                Rs.
                                ${formatMoney(
                                    last.amount
                                )}

                            </strong>

                        </div>

                    </div>


                    <div class="last-payment-receipt">

                        <i class="bi bi-upc-scan me-1"></i>

                        Receipt:
                        ${escapeHtml(
                            last.receipt_number || '-'
                        )}

                    </div>

                </div>

            `;

                    } else {

                        lastPaymentHtml = `

                <div class="last-payment-box last-payment-empty">

                    <div>

                        <span class="last-payment-label">

                            <i class="bi bi-receipt me-1"></i>

                            Last Payment

                        </span>

                    </div>


                    <div class="small text-muted mt-1">

                        No payment recorded yet

                    </div>

                </div>

            `;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Card
                    |--------------------------------------------------------------------------
                    */

                    container.insertAdjacentHTML(
                        'beforeend',
                        `

            <div class="col-12 col-md-6 col-xl-4">

                <div class="class-payment-card">


                    {{-- =====================================================
                         Card Header
                         ===================================================== --}}
                    <div class="class-payment-card-header">

                        <div class="d-flex align-items-start gap-3">

                            {{-- Checkbox --}}
                            <div class="pt-1">

                                <input
                                    type="checkbox"
                                    class="form-check-input class-checkbox"
                                    value="${item.enrollment_id}"
                                >

                            </div>


                            {{-- Class Info --}}
                            <div class="flex-grow-1">

                                <div class="d-flex justify-content-between align-items-start gap-2">

                                    <div>

                                        <h6 class="class-payment-title mb-1">

                                            ${escapeHtml(
                                                item.class_name || '-'
                                            )}

                                        </h6>


                                        <div class="class-payment-subject">

                                            <i class="bi bi-book me-1"></i>

                                            ${escapeHtml(
                                                item.subject || '-'
                                            )}

                                        </div>

                                    </div>


                                    {{-- Status --}}
                                    <span class="${statusClass}">

                                        ${escapeHtml(
                                            status.toUpperCase()
                                        )}

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =====================================================
                         Class Details
                         ===================================================== --}}
                    <div class="class-payment-details">


                        {{-- Category --}}
                        <div class="class-payment-detail">

                            <div class="class-payment-detail-label">

                                <i class="bi bi-tag me-1"></i>

                                Category

                            </div>

                            <div class="class-payment-detail-value">

                                ${escapeHtml(
                                    item.category_name || '-'
                                )}

                            </div>

                        </div>


                        {{-- Grade --}}
                        <div class="class-payment-detail">

                            <div class="class-payment-detail-label">

                                <i class="bi bi-mortarboard me-1"></i>

                                Grade

                            </div>

                            <div class="class-payment-detail-value">

                                ${escapeHtml(
                                    item.grade || '-'
                                )}

                            </div>

                        </div>


                        {{-- Teacher --}}
                        <div class="class-payment-detail">

                            <div class="class-payment-detail-label">

                                <i class="bi bi-person-badge me-1"></i>

                                Teacher

                            </div>

                            <div class="class-payment-detail-value">

                                ${escapeHtml(
                                    item.teacher_initials || '-'
                                )}

                            </div>

                        </div>


                        {{-- Fee (Class Fee only - Hall Fee ignored) --}}
                        <div class="class-payment-detail">

                            <div class="class-payment-detail-label">

                                <i class="bi bi-cash-stack me-1"></i>

                                Monthly Fee

                            </div>

                            <div class="class-payment-fee">
                                Rs. ${formatMoney(resolveClassFee(item))}
                            </div>

                        </div>

                    </div>


                    {{-- =====================================================
                         Last Payment
                         ===================================================== --}}
                    ${lastPaymentHtml}


                    {{-- =====================================================
                         Footer
                         ===================================================== --}}
                    <div class="class-payment-card-footer">

                        <div class="small text-muted">

                            ${status === 'paid'
                                ? '<i class="bi bi-check-circle-fill text-success me-1"></i> Payment completed'
                                : '<i class="bi bi-exclamation-circle text-warning me-1"></i> Payment pending'
                            }

                        </div>


                        <button
                            type="button"
                            class="btn btn-primary btn-sm pay-single-btn"
                            data-enrollment-id="${item.enrollment_id}"
                        >

                            <i class="bi bi-credit-card me-1"></i>

                            Pay

                        </button>

                    </div>

                </div>

            </div>

            `
                    );

                });

            }

            /*
            |--------------------------------------------------------------------------
            | Single Pay
            |--------------------------------------------------------------------------
            */

            document.addEventListener('click', function(event) {

                const button = event.target.closest('.pay-single-btn');
                if (!button) return;

                const enrollmentId = Number(button.dataset.enrollmentId);
                openPaymentModal([enrollmentId]);

            });

            /*
            |--------------------------------------------------------------------------
            | Checkbox
            |--------------------------------------------------------------------------
            */

            document.addEventListener('change', function(event) {

                if (!event.target.classList.contains('class-checkbox')) return;
                updateSelectedEnrollments();

            });

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    document.querySelectorAll('.class-checkbox').forEach(function(checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                    updateSelectedEnrollments();
                });
            }

            function updateSelectedEnrollments() {

                selectedEnrollmentIds = Array.from(
                    document.querySelectorAll('.class-checkbox:checked')
                ).map(function(checkbox) {
                    return Number(checkbox.value);
                });

                updateBulkButton();

            }

            function updateBulkButton() {

                if (!bulkButton) return;

                bulkButton.disabled = selectedEnrollmentIds.length === 0;

                bulkButton.innerHTML = selectedEnrollmentIds.length > 1 ?
                    `<i class="bi bi-credit-card me-1"></i> Pay Selected (${selectedEnrollmentIds.length})` :
                    `<i class="bi bi-credit-card me-1"></i> Pay Selected`;

            }

            /*
            |--------------------------------------------------------------------------
            | Bulk Button
            |--------------------------------------------------------------------------
            */

            if (bulkButton) {
                bulkButton.addEventListener('click', function() {
                    if (!selectedEnrollmentIds.length) return;
                    openPaymentModal(selectedEnrollmentIds);
                });
            }

            function openPaymentModal(enrollmentIds) {

                if (!enrollmentIds || !enrollmentIds.length) {
                    return;
                }

                const firstEnrollment = currentClasses.find(function(item) {
                    return Number(item.enrollment_id) === Number(enrollmentIds[0]);
                });

                if (!firstEnrollment) {
                    return;
                }

                const modalElement = document.getElementById('paymentModal');

                if (!modalElement) {
                    console.error('Payment modal not found.');
                    return;
                }

                const form = modalElement.querySelector('#payment-form');

                if (!form) {
                    console.error('Payment form not found.');
                    return;
                }

                // Hidden fields
                const paymentCode =
                    form.querySelector('#payment-code');

                const paymentEnrollmentId =
                    form.querySelector('#payment-enrollment-id');

                const paymentMarkMethod =
                    form.querySelector('#payment-mark-method');

                if (!paymentCode || !paymentEnrollmentId || !paymentMarkMethod) {
                    console.error('Payment hidden fields are missing.');
                    return;
                }

                paymentCode.value =
                    currentStudent?.custom_id || '';

                paymentEnrollmentId.value =
                    enrollmentIds.join(',');

                paymentMarkMethod.value =
                    currentMarkMethod || 'manual_web';


                // =====================================================
                // Class / Category
                // =====================================================

                let infoBox =
                    form.querySelector('[data-payment-class-info]');

                if (!infoBox) {

                    infoBox = document.createElement('div');

                    infoBox.setAttribute(
                        'data-payment-class-info',
                        'true'
                    );

                    infoBox.className =
                        'alert alert-light border mb-3';

                    infoBox.innerHTML = `
            <div class="row">

                <div class="col-md-6">

                    <small class="text-muted d-block">
                        Class
                    </small>

                    <div
                        data-payment-class-name
                        class="fw-semibold"
                    >
                        -
                    </div>

                </div>

                <div class="col-md-6">

                    <small class="text-muted d-block">
                        Category
                    </small>

                    <div
                        data-payment-category-name
                        class="fw-semibold"
                    >
                        -
                    </div>

                </div>

            </div>
        `;

                    const firstRow =
                        form.querySelector('.row.g-3');

                    if (firstRow) {
                        form.insertBefore(infoBox, firstRow);
                    } else {
                        form.prepend(infoBox);
                    }
                }


                const classNameElement =
                    infoBox.querySelector(
                        '[data-payment-class-name]'
                    );

                const categoryNameElement =
                    infoBox.querySelector(
                        '[data-payment-category-name]'
                    );

                if (classNameElement) {
                    classNameElement.textContent =
                        enrollmentIds.length > 1 ?
                        `${enrollmentIds.length} Classes Selected` :
                        firstEnrollment.class_name || '-';
                }

                if (categoryNameElement) {
                    categoryNameElement.textContent =
                        enrollmentIds.length > 1 ?
                        'Bulk Payment' :
                        firstEnrollment.category_name || '-';
                }


                // =====================================================
                // IMPORTANT: Calculate Class Fee only
                // (Hall Fee intentionally ignored on this page)
                // =====================================================

                const feeSummary = enrollmentIds.reduce(
                    function(summary, id) {

                        const item = currentClasses.find(function(classItem) {
                            return Number(classItem.enrollment_id) === Number(id);
                        });

                        if (!item) {
                            return summary;
                        }

                        const classFee = resolveClassFee(item);

                        summary.classFee += classFee;
                        summary.totalFee += classFee;

                        return summary;

                    },
                    {
                        classFee: 0,
                        totalFee: 0
                    }
                );


                // =====================================================
                // Payment Inputs
                // =====================================================

                const classFeeInput =
                    form.querySelector('#payment-class-fee');

                const hallFeeInput =
                    form.querySelector('#payment-hall-fee');

                const finalFeeInput =
                    form.querySelector('#payment-final-fee');

                const discountInput =
                    form.querySelector('#payment-discount');

                const amountInput =
                    form.querySelector('#payment-amount');

                const noteInput =
                    form.querySelector('#payment-note');


                if (!finalFeeInput ||
                    !discountInput ||
                    !amountInput ||
                    !noteInput) {

                    console.error(
                        'Payment input fields are missing.'
                    );

                    return;
                }


                // =====================================================
                // Set Fee Values (Class Fee only)
                // =====================================================

                if (classFeeInput) {
                    classFeeInput.value =
                        feeSummary.classFee.toFixed(2);
                }

                // Hall Fee is intentionally ignored on New Payment page.
                if (hallFeeInput) {
                    hallFeeInput.value = '0.00';
                }

                finalFeeInput.value =
                    feeSummary.totalFee.toFixed(2);

                discountInput.value = '0';

                amountInput.value =
                    feeSummary.totalFee.toFixed(2);

                noteInput.value = '';


                // =====================================================
                // Current Month
                // =====================================================

                setCurrentMonth();


                // =====================================================
                // Show Modal
                // =====================================================

                bootstrap.Modal
                    .getOrCreateInstance(modalElement)
                    .show();
            }

            function setCurrentMonth() {

                const monthInput =
                    document.getElementById('payment-month');

                if (!monthInput) {
                    console.error(
                        'Payment month input not found.'
                    );
                    return;
                }

                const now = new Date();

                const year =
                    now.getFullYear();

                const month =
                    String(
                        now.getMonth() + 1
                    ).padStart(2, '0');

                monthInput.value =
                    `${year}-${month}`;
            }

            /*
            |--------------------------------------------------------------------------
            | Discount
            |--------------------------------------------------------------------------
            */

            const discountInput = document.getElementById('payment-discount');
            if (discountInput) {
                discountInput.addEventListener('input', function() {
                    const finalFee = Number(document.getElementById('payment-final-fee')?.value || 0);
                    const discount = Number(this.value || 0);
                    const payable = Math.max(finalFee - discount, 0);
                    const amountEl = document.getElementById('payment-amount');
                    if (amountEl) amountEl.value = payable.toFixed(2);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Confirm Payment
            |--------------------------------------------------------------------------
            */

            const confirmBtn = document.getElementById('confirm-payment-btn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', async function() {

                    const enrollmentInput = document.getElementById('payment-enrollment-id');
                    if (!enrollmentInput) {
                        alert('Payment form not loaded properly.');
                        return;
                    }

                    const enrollmentIds = enrollmentInput.value
                        .split(',')
                        .map(Number)
                        .filter(Boolean);

                    if (!enrollmentIds.length) {
                        alert('Please select a class.');
                        return;
                    }

                    const monthEl = document.getElementById('payment-month');
                    const month = monthEl?.value || '';

                    if (!month) {
                        alert('Please select payment month.');
                        return;
                    }

                    const paymentMethod = document.getElementById('payment-method')?.value || 'cash';
                    const discount = Number(document.getElementById('payment-discount')?.value || 0);
                    const note = document.getElementById('payment-note')?.value || '';
                    const markMethod = document.getElementById('payment-mark-method')?.value ||
                        'manual_web';

                    const button = this;
                    button.disabled = true;
                    button.innerHTML =
                        `<span class="spinner-border spinner-border-sm me-2"></span> Processing...`;

                    try {

                        if (enrollmentIds.length === 1) {

                            const enrollment = currentClasses.find(function(item) {
                                return Number(item.enrollment_id) === Number(enrollmentIds[0]);
                            });

                            if (!enrollment) throw new Error('Class not found');

                            const amount = Number(document.getElementById('payment-amount')?.value ||
                                0);

                            const data = {
                                code: currentStudent?.custom_id || '',
                                enrollment_id: enrollmentIds[0],
                                amount: amount,
                                payment_month: month + '-01',
                                mark_method: markMethod,
                                payment_method: paymentMethod,
                                discount_amount: discount,
                                note: note
                            };

                            const response = await fetch(payUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')?.getAttribute(
                                        'content') || ''
                                },
                                body: JSON.stringify(data)
                            });

                            const result = await response.json();

                            if (!response.ok || result.status !== 'success') {
                                throw new Error(result.message || 'Payment failed.');
                            }

                            // Close payment modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'paymentModal'));
                            if (modal) modal.hide();

                            // Show receipt
                            showReceipt(result.data.receipt);

                            setTimeout(function() {
                                printReceipt(result.data.receipt);
                            }, 350);

                            // Refresh student data
                            await readStudent(currentStudent?.custom_id, currentMarkMethod);

                        } else {

                            // Bulk Payment (Class Fee only)
                            const payments = enrollmentIds.map(function(id) {
                                const enrollment = currentClasses.find(function(item) {
                                    return Number(item.enrollment_id) === Number(id);
                                });

                                const totalFee = resolveClassFee(enrollment);

                                return {
                                    code: currentStudent?.custom_id || '',
                                    enrollment_id: id,
                                    amount: totalFee,
                                    payment_month: month + '-01',
                                    mark_method: markMethod,
                                    payment_method: paymentMethod,
                                    discount_amount: 0,
                                    note: note
                                };
                            });

                            const response = await fetch(bulkPayUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')?.getAttribute(
                                        'content') || ''
                                },
                                body: JSON.stringify({
                                    payments: payments
                                })
                            });

                            const result = await response.json();

                            if (!response.ok || result.status !== 'success') {
                                throw new Error(result.message || 'Bulk payment failed.');
                            }

                            // Close payment modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'paymentModal'));
                            if (modal) modal.hide();

                            // Show receipt
                            showReceipt(result.data.receipt);

                            setTimeout(function() {
                                printReceipt(result.data.receipt);
                            }, 350);

                            // Refresh student data
                            await readStudent(currentStudent?.custom_id, currentMarkMethod);

                        }

                    } catch (error) {
                        alert(error.message);
                    } finally {
                        button.disabled = false;
                        button.innerHTML = `<i class="bi bi-check-circle me-1"></i> Confirm Payment`;
                    }

                });
            }

            /*
            |--------------------------------------------------------------------------
            | Build Receipt HTML
            |--------------------------------------------------------------------------
            */

            function buildReceiptHtml(receipt) {

                if (!receipt) {
                    return '';
                }

                /*
                |--------------------------------------------------------------------------
                | Rows
                |--------------------------------------------------------------------------
                */

                const rows =
                    receipt.rows ||
                    receipt.payments ||
                    [];

                /*
                |--------------------------------------------------------------------------
                | Student (fallback to first row's student)
                |--------------------------------------------------------------------------
                */

                const student =
                    receipt.student ||
                    rows[0]?.student ||
                    {};

                /*
                |--------------------------------------------------------------------------
                | Payment Date / Time
                |--------------------------------------------------------------------------
                */

                const paymentDate =
                    receipt.payment_date ||
                    rows[0]?.payment_date ||
                    new Date();

                const paymentTime =
                    receipt.payment_time ||
                    rows[0]?.payment_time ||
                    new Date();

                const paymentMethod =
                    receipt.payment_method ||
                    rows[0]?.payment_method ||
                    '-';

                /*
                |--------------------------------------------------------------------------
                | Header
                |--------------------------------------------------------------------------
                */

                let html = `
                    <div id="receipt-print-area" class="receipt-paper">
                        <div class="text-center">
                            <strong>{{ config('app.name') }}</strong><br>
                            PAYMENT RECEIPT
                        </div>
                        <div class="receipt-line"></div>

                        Student ID: ${escapeHtml(student.custom_id || '-')}<br>
                        Student: ${escapeHtml(student.initial_name || student.full_name || '-')}<br>
                        Date: ${escapeHtml(formatReceiptDate(paymentDate))}<br>
                        Time: ${escapeHtml(formatReceiptTime(paymentTime))}
                        <div class="receipt-line"></div>
                `;

                /*
                |--------------------------------------------------------------------------
                | Rows
                |--------------------------------------------------------------------------
                */

                if (rows.length) {

                    rows.forEach(function(row) {

                        /*
                        |--------------------------------------------------------------------------
                        | Fee (Class Fee only)
                        |--------------------------------------------------------------------------
                        */

                        const classFee = Number(
                            row.class_fee ??
                            row.final_fee ??
                            row.total_fee ??
                            0
                        );

                        const totalFee = Number(
                            row.total_fee ??
                            classFee
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Row Fields (with nested fallback)
                        |--------------------------------------------------------------------------
                        */

                        const className =
                            row.class_name ||
                            row.class?.class_name ||
                            '-';

                        const category =
                            row.category ||
                            row.class?.category_name ||
                            row.class?.category?.name ||
                            '-';

                        const grade =
                            row.grade ||
                            row.class?.grade ||
                            '-';

                        const teacher =
                            row.teacher ||
                            row.class?.teacher_initials ||
                            '-';

                        const month =
                            row.payment_month ||
                            row.class?.payment_month ||
                            '-';

                        const receiptNumber =
                            row.receipt_number ||
                            row.class?.receipt_number ||
                            '-';

                        const paidAmount = Number(
                            row.paid_amount ??
                            row.paid ??
                            0
                        );

                        html += `
                            <div class="receipt-item">
                                <strong>${escapeHtml(className)}</strong><br>
                                Category: ${escapeHtml(category)}<br>
                                Grade: ${escapeHtml(grade)}<br>
                                Teacher: ${escapeHtml(teacher)}<br>
                                Month: ${escapeHtml(formatReceiptMonth(month))}<br>
                                Receipt: ${escapeHtml(receiptNumber)}<br>
                                Class Fee: Rs. ${formatMoney(classFee)}<br>
                                Total Fee: Rs. ${formatMoney(totalFee)}<br>
                                Paid: Rs. ${formatMoney(paidAmount)}
                            </div>
                            <div class="receipt-line"></div>
                        `;
                    });

                } else {

                    html += `
                        <div class="receipt-item text-center text-muted">
                            No payment rows available.
                        </div>
                        <div class="receipt-line"></div>
                    `;
                }

                /*
                |--------------------------------------------------------------------------
                | Summary (Class Fee only)
                |--------------------------------------------------------------------------
                */

                const totalClassFee = Number(
                    receipt.summary?.total_class_fee || 0
                );

                const totalFee = totalClassFee;

                const totalPaid = Number(
                    receipt.summary?.total_paid || 0
                );

                html += `
                        <div class="receipt-summary">
                            Class Fee: Rs. ${formatMoney(totalClassFee)}<br>
                            <strong>TOTAL FEE: Rs. ${formatMoney(totalFee)}</strong><br>
                            <strong>TOTAL PAID: Rs. ${formatMoney(totalPaid)}</strong>
                        </div>
                        <div class="receipt-line"></div>

                        Payment: ${escapeHtml(paymentMethod)}<br><br>

                        <div class="text-center">Thank You!</div>
                    </div>
                `;

                return html;
            }

            /*
            |--------------------------------------------------------------------------
            | Show / Print Receipt
            |--------------------------------------------------------------------------
            */

            function showReceipt(receipt) {
                if (!receipt) return;

                const previewEl = document.getElementById('receipt-preview');
                if (previewEl) previewEl.innerHTML = buildReceiptHtml(receipt);

                const modalElement = document.getElementById('receiptModal');
                if (modalElement) {
                    bootstrap.Modal.getOrCreateInstance(modalElement).show();
                }
            }

            function printReceipt(receipt) {
                if (!receipt) return;

                const printWindow = window.open('', '_blank', 'width=400,height=700');
                if (!printWindow) {
                    alert('Please allow popups to automatically print the receipt.');
                    return;
                }

                printWindow.document.open();
                printWindow.document.write(`<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Payment Receipt</title><style>
                    @page{size:58mm auto;margin:0}html,body{margin:0;padding:0;width:58mm}body{font-family:monospace;font-size:11px;color:#000;background:#fff}.receipt-paper{width:58mm;box-sizing:border-box;padding:3mm}.receipt-line{border-top:1px dashed #000;margin:6px 0}.receipt-summary{line-height:1.6}.receipt-item{line-height:1.45;word-break:break-word}.text-center{text-align:center}
                </style></head><body>${buildReceiptHtml(receipt)}</body></html>`);
                printWindow.document.close();
                printWindow.focus();

                setTimeout(function() {
                    printWindow.print();
                    setTimeout(function() {
                        printWindow.close();
                    }, 500);
                }, 300);
            }

            const printBtn = document.getElementById('print-receipt-btn');
            if (printBtn) {
                printBtn.addEventListener('click', function() {
                    const receiptElement = document.getElementById('receipt-print-area');
                    if (!receiptElement) return;

                    const printWindow = window.open('', '_blank', 'width=400,height=700');
                    if (!printWindow) {
                        alert('Please allow popups to print the receipt.');
                        return;
                    }

                    printWindow.document.write(
                        `<!DOCTYPE html><html><head><title>Payment Receipt</title><style>@page{size:58mm auto;margin:0}html,body{margin:0;padding:0;width:58mm}body{font-family:monospace;font-size:11px}.receipt-paper{width:58mm;box-sizing:border-box;padding:3mm}.receipt-line{border-top:1px dashed #000;margin:6px 0}.receipt-summary{line-height:1.6}.receipt-item{line-height:1.45}.text-center{text-align:center}</style></head><body>${receiptElement.outerHTML}</body></html>`
                    );
                    printWindow.document.close();
                    printWindow.focus();

                    setTimeout(function() {
                        printWindow.print();
                        setTimeout(function() {
                            printWindow.close();
                        }, 500);
                    }, 300);
                });
            }

            /*
             |--------------------------------------------------------------------------
             | Keyboard Shortcuts
             |--------------------------------------------------------------------------
             */
            document.addEventListener('keydown', function(event) {
                if (event.key === 'F2') {
                    event.preventDefault();
                    const input = document.getElementById('manual-code');
                    if (input) {
                        input.focus();
                        input.select();
                    }
                    return;
                }
                if (event.key === 'Enter') {
                    const modal = document.getElementById('paymentModal');
                    if (!modal || !modal.classList.contains('show')) return;
                    if (event.target?.tagName === 'TEXTAREA') return;
                    event.preventDefault();
                    const confirmBtn = document.getElementById('confirm-payment-btn');
                    if (confirmBtn && !confirmBtn.disabled) confirmBtn.click();
                }
            });

            /*
             |--------------------------------------------------------------------------
             | Manual Search
            |--------------------------------------------------------------------------
            */

            const searchForm = document.getElementById('manual-search-form');
            if (searchForm) {
                searchForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const code = document.getElementById('manual-code')?.value || '';
                    readStudent(code, 'manual_web');
                });
            }

            /*
            |--------------------------------------------------------------------------
            | QR Scanner
            |--------------------------------------------------------------------------
            */

            function startQrScanner() {

                const readerEl = document.getElementById('qr-reader');
                const statusEl = document.getElementById('qr-reader-status');

                if (!readerEl) return;

                html5QrCode = new Html5Qrcode('qr-reader');

                Html5Qrcode.getCameras()
                    .then(function(devices) {

                        if (!devices.length) {
                            if (statusEl) statusEl.textContent = '❌ No camera found.';
                            return;
                        }

                        const cameraId = devices[0].id;

                        html5QrCode.start(
                            cameraId, {
                                fps: 10,
                                qrbox: {
                                    width: 180,
                                    height: 180
                                }
                            },
                            function(decodedText) {
                                readStudent(decodedText, 'qr_web');
                            },
                            function() {
                                // Ignore scan errors
                            }
                        );

                        if (statusEl) statusEl.innerHTML = '✅ Camera ready';

                    })
                    .catch(function(error) {
                        if (statusEl) statusEl.textContent = '❌ Camera permission required.';
                        console.error(error);
                    });

            }

            startQrScanner();

        });
    </script>
@endpush