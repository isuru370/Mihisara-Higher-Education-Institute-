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
            const bulkButton = document.getElementById('bulk-payment-btn');
            const defaultStudentImage = @json(asset('images/default-student.png'));

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
            let isLoadingStudent = false;
            let lastReceipt = null;

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

                    if (/^\d{4}-\d{2}$/.test(normalized)) {
                        normalized = normalized + '-01';
                    }

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
                    if (/^\d{2}:\d{2}(:\d{2})?$/.test(String(value))) {
                        return String(value);
                    }

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
            | Month Parser
            |--------------------------------------------------------------------------
            |
            | Accepts:
            |   "2026-06"
            |   "2026-06-01"
            |   "2026-06-15T00:00:00.000000Z"
            |   "June 2026"
            |
            | Returns a Date object set to first day of that month
            | or null if invalid.
            |
            */

            function parseMonthToDate(value) {

                if (!value) return null;

                try {
                    let normalized = String(value).trim();

                    /*
                    | "June 2026" → "2026-06-01"
                    */
                    const monthNames = [
                        'january','february','march','april','may','june',
                        'july','august','september','october','november','december'
                    ];

                    const nameMatch = normalized
                        .toLowerCase()
                        .match(/^([a-z]+)\s+(\d{4})$/);

                    if (nameMatch) {
                        const idx = monthNames.indexOf(nameMatch[1]);

                        if (idx !== -1) {
                            return new Date(
                                parseInt(nameMatch[2]),
                                idx,
                                1
                            );
                        }
                    }

                    /*
                    | Strip time part
                    */
                    if (/T\d{2}:\d{2}/.test(normalized)) {
                        normalized = normalized.split('T')[0];
                    }

                    /*
                    | "2026-06" → "2026-06-01"
                    */
                    if (/^\d{4}-\d{2}$/.test(normalized)) {
                        normalized = normalized + '-01';
                    }

                    const parts = normalized.split('-');

                    if (parts.length === 3) {
                        const d = new Date(
                            parseInt(parts[0]),
                            parseInt(parts[1]) - 1,
                            parseInt(parts[2])
                        );

                        if (!isNaN(d.getTime())) {
                            return d;
                        }
                    }

                    return null;

                } catch (e) {
                    return null;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Next Payment Month Resolver
            |--------------------------------------------------------------------------
            |
            | Rules:
            |   - If any selected enrollment has a previous payment
            |       → use (latest paid month + 1)
            |   - Otherwise → use current month
            |
            */

            function resolveNextPaymentMonth(enrollmentIds) {

                let latestPaid = null;

                (enrollmentIds || []).forEach(function(id) {

                    const item = currentClasses.find(function(classItem) {
                        return Number(classItem.enrollment_id) === Number(id);
                    });

                    if (!item || !item.last_payment) {
                        return;
                    }

                    const lastMonth =
                        item.last_payment.payment_month ||
                        item.last_payment.payment_month_name ||
                        null;

                    if (!lastMonth) {
                        return;
                    }

                    const parsed = parseMonthToDate(lastMonth);

                    if (!parsed) {
                        return;
                    }

                    if (!latestPaid || parsed > latestPaid) {
                        latestPaid = parsed;
                    }
                });

                /*
                |--------------------------------------------------------------------------
                | Compute target month
                |--------------------------------------------------------------------------
                */

                if (latestPaid) {
                    return new Date(
                        latestPaid.getFullYear(),
                        latestPaid.getMonth() + 1,
                        1
                    );
                }

                const now = new Date();

                return new Date(now.getFullYear(), now.getMonth(), 1);
            }

            /*
            |--------------------------------------------------------------------------
            | Set Payment Month (default = next month after last payment)
            |--------------------------------------------------------------------------
            */

            function setNextPaymentMonth(enrollmentIds) {

                const monthInput =
                    document.getElementById('payment-month');

                if (!monthInput) {
                    console.error('Payment month input not found.');
                    return;
                }

                const target = resolveNextPaymentMonth(enrollmentIds);

                const year =
                    target.getFullYear();

                const month =
                    String(
                        target.getMonth() + 1
                    ).padStart(2, '0');

                monthInput.value = `${year}-${month}`;
            }
            /*
            |--------------------------------------------------------------------------
            | Read Student
            |--------------------------------------------------------------------------
            */

            async function readStudent(code, markMethod, options) {

                options = options || {};
                code = String(code || '').trim();

                if (!code) {
                    showError('Please enter Student ID or scan QR.');
                    focusSearch();
                    return;
                }

                if (isLoadingStudent) return;

                isLoadingStudent = true;
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

                    let result = {};
                    try {
                        result = await response.json();
                    } catch (e) {
                        throw new Error('Unexpected server response (' + response.status + ').');
                    }

                    if (!response.ok || result.status !== 'success') {
                        const firstError = result.errors ? Object.values(result.errors).flat()[0] : null;
                        throw new Error(firstError || result.message || 'Student not found.');
                    }

                    currentStudent = result.data.student;
                    currentClasses = result.data.classes || [];

                    const input = document.getElementById('manual-code');
                    if (input && currentStudent?.custom_id) input.value = currentStudent.custom_id;

                    renderClasses();
                    renderStudent();

                    // QR scan with exactly one unpaid class: open the payment straight away.
                    if (markMethod === 'qr_web' && options.autoOpen !== false) {
                        const unpaid = currentClasses.filter(function(item) {
                            return isPayable(item) && item.payment_status !== 'paid';
                        });
                        if (unpaid.length === 1) {
                            selectedEnrollmentIds = [Number(unpaid[0].enrollment_id)];
                            syncCardSelection();
                            setTimeout(function() {
                                openPaymentModal(selectedEnrollmentIds.slice());
                            }, 200);
                        } else if (unpaid.length === 0 && currentClasses.length) {
                            toast('All classes are paid for this month.', 'success');
                        }
                    }

                } catch (error) {
                    currentStudent = null;
                    currentClasses = [];
                    selectedEnrollmentIds = [];
                    updateBulkButton();
                    if (studentSection) studentSection.classList.add('d-none');
                    showError(error.message);
                    focusSearch();
                } finally {
                    isLoadingStudent = false;
                    hideLoading();
                }

            }

            /*
            |--------------------------------------------------------------------------
            | Small UI helpers
            |--------------------------------------------------------------------------
            */

            function toast(message, type) {
                const el = document.getElementById('np-toast');
                const body = document.getElementById('np-toast-body');
                if (!el || !body) return;

                el.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-dark');
                el.classList.add('text-bg-' + (type || 'dark'));
                body.textContent = message;
                bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 }).show();
            }

            function isModalOpen(id) {
                const el = document.getElementById(id);
                return !!(el && el.classList.contains('show'));
            }

            function anyModalOpen() {
                return !!document.querySelector('.modal.show');
            }

            function focusSearch(selectText) {
                const input = document.getElementById('manual-code');
                if (!input) return;
                input.focus();
                if (selectText !== false) input.select();
            }

            function isFreeCard(item) {
                return item && (item.is_free_card === true || item.is_free_card === 1 || item.is_free_card === '1');
            }

            function isPayable(item) {
                return !!item && !isFreeCard(item) && resolveClassFee(item) > 0;
            }

            function findClass(id) {
                return currentClasses.find(function(item) {
                    return Number(item.enrollment_id) === Number(id);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Render Student
            |--------------------------------------------------------------------------
            */

            function renderStudent() {

                if (!currentStudent) return;

                const student = currentStudent;

                const set = function(id, value) {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value;
                };

                set('student-name', student.initial_name || student.full_name || '-');
                set('student-custom-id', student.custom_id || '-');
                set('student-mobile', student.mobile || '-');
                set('student-guardian', student.guardian_name || student.guardian_mobile || '-');

                const imageEl = document.getElementById('student-image');
                if (imageEl) {
                    imageEl.src = student.image_url || defaultStudentImage;
                }

                const unpaid = currentClasses.filter(function(item) {
                    return isPayable(item) && item.payment_status !== 'paid';
                });

                set('student-unpaid-count', String(unpaid.length));
                set('student-due-total', formatMoney(unpaid.reduce(function(sum, item) {
                    return sum + resolveClassFee(item);
                }, 0)));

                if (studentSection) studentSection.classList.remove('d-none');
            }

            /*
            |--------------------------------------------------------------------------
            | Render Classes (cards with number keys)
            |--------------------------------------------------------------------------
            */

            function renderClasses() {

                const container = document.getElementById('classes-card-container');
                if (!container) return;

                container.innerHTML = '';
                selectedEnrollmentIds = [];
                updateBulkButton();

                if (!currentClasses.length) {
                    container.innerHTML = `
                        <div class="col-12">
                            <div class="classes-empty-state">
                                <div class="classes-empty-icon"><i class="bi bi-journal-x"></i></div>
                                <div class="fw-semibold text-muted">No active classes found</div>
                                <small class="text-muted">This student has no active class enrollments.</small>
                            </div>
                        </div>`;
                    return;
                }

                currentClasses.forEach(function(item, index) {

                    const number = index + 1;
                    const freeCard = isFreeCard(item);
                    const payable = isPayable(item);
                    const status = freeCard ? 'free card' : (item.payment_status || 'unpaid');

                    const statusClass = freeCard ? 'payment-status-free' :
                        (status === 'paid' ? 'payment-status-paid' : 'payment-status-unpaid');

                    const last = item.last_payment;

                    const lastPaymentHtml = last ? `
                        <div class="last-payment-box">
                            <div class="last-payment-header">
                                <span class="last-payment-label"><i class="bi bi-receipt me-1"></i>Last Payment</span>
                            </div>
                            <div class="last-payment-content">
                                <div>
                                    <div class="small text-muted">Month</div>
                                    <strong>${escapeHtml(last.payment_month_name || last.payment_month || '-')}</strong>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">Amount</div>
                                    <strong class="text-success">Rs. ${formatMoney(last.amount)}</strong>
                                </div>
                            </div>
                            <div class="last-payment-receipt"><i class="bi bi-upc-scan me-1"></i>Receipt: ${escapeHtml(last.receipt_number || '-')}</div>
                        </div>` : `
                        <div class="last-payment-box last-payment-empty">
                            <span class="last-payment-label"><i class="bi bi-receipt me-1"></i>Last Payment</span>
                            <div class="small text-muted mt-1">No payment recorded yet</div>
                        </div>`;

                    const feeOption = item.fee_option && item.fee_option.label ?
                        `<div class="small text-muted">${escapeHtml(item.fee_option.label)}</div>` : '';

                    container.insertAdjacentHTML('beforeend', `
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="class-payment-card np-card ${payable ? '' : 'np-card-disabled'}"
                                data-enrollment-id="${item.enrollment_id}" ${payable ? 'tabindex="0" role="button"' : ''}>

                                <div class="class-payment-card-header">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="np-card-number">${number <= 9 ? number : ''}</div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <h6 class="class-payment-title mb-1">${escapeHtml(item.class_name || '-')}</h6>
                                                    <div class="class-payment-subject"><i class="bi bi-book me-1"></i>${escapeHtml(item.subject || '-')}</div>
                                                </div>
                                                <span class="${statusClass}">${escapeHtml(status.toUpperCase())}</span>
                                            </div>
                                        </div>
                                        <input type="checkbox" class="form-check-input class-checkbox np-card-check"
                                            value="${item.enrollment_id}" ${payable ? '' : 'disabled'} tabindex="-1"
                                            aria-label="Select ${escapeHtml(item.class_name || 'class')}">
                                    </div>
                                </div>

                                <div class="class-payment-details">
                                    <div class="class-payment-detail">
                                        <div class="class-payment-detail-label"><i class="bi bi-tag me-1"></i>Category</div>
                                        <div class="class-payment-detail-value">${escapeHtml(item.category_name || '-')}</div>
                                    </div>
                                    <div class="class-payment-detail">
                                        <div class="class-payment-detail-label"><i class="bi bi-mortarboard me-1"></i>Grade</div>
                                        <div class="class-payment-detail-value">${escapeHtml(item.grade || '-')}</div>
                                    </div>
                                    <div class="class-payment-detail">
                                        <div class="class-payment-detail-label"><i class="bi bi-person-badge me-1"></i>Teacher</div>
                                        <div class="class-payment-detail-value">${escapeHtml(item.teacher_initials || '-')}</div>
                                    </div>
                                    <div class="class-payment-detail">
                                        <div class="class-payment-detail-label"><i class="bi bi-cash-stack me-1"></i>Monthly Fee</div>
                                        <div>
                                            <div class="class-payment-fee">${freeCard ? 'Free card' : 'Rs. ' + formatMoney(resolveClassFee(item))}</div>
                                            ${feeOption}
                                        </div>
                                    </div>
                                </div>

                                ${lastPaymentHtml}

                                <div class="class-payment-card-footer">
                                    ${payable ? `
                                        <button type="button" class="btn btn-primary btn-lg w-100 fw-semibold pay-single-btn"
                                            data-enrollment-id="${item.enrollment_id}">
                                            <i class="bi bi-credit-card me-1"></i>Pay this class
                                            ${number <= 9 ? `<kbd class="np-kbd-light ms-1">${number}</kbd> + <kbd class="np-kbd-light">F8</kbd>` : ''}
                                        </button>` : `
                                        <div class="w-100 text-center small text-muted py-2">
                                            ${freeCard ? '<i class="bi bi-gift me-1"></i>Free card &ndash; no payment needed' : 'No fee set for this class'}
                                        </div>`}
                                </div>
                            </div>
                        </div>`);
                });

                syncCardSelection();
            }

            /*
            |--------------------------------------------------------------------------
            | Selection
            |--------------------------------------------------------------------------
            */

            function toggleClass(enrollmentId, force) {
                const item = findClass(enrollmentId);
                if (!isPayable(item)) return;

                const id = Number(enrollmentId);
                const selected = selectedEnrollmentIds.includes(id);
                const shouldSelect = typeof force === 'boolean' ? force : !selected;

                if (shouldSelect && !selected) selectedEnrollmentIds.push(id);
                if (!shouldSelect && selected) {
                    selectedEnrollmentIds = selectedEnrollmentIds.filter(function(x) {
                        return x !== id;
                    });
                }

                syncCardSelection();
            }

            function selectAllUnpaid() {
                const unpaid = currentClasses.filter(function(item) {
                    return isPayable(item) && item.payment_status !== 'paid';
                }).map(function(item) {
                    return Number(item.enrollment_id);
                });

                const allSelected = unpaid.length > 0 && unpaid.every(function(id) {
                    return selectedEnrollmentIds.includes(id);
                });

                selectedEnrollmentIds = allSelected ? [] : unpaid;

                if (!unpaid.length) {
                    toast('All classes are already paid for this month.', 'success');
                }

                syncCardSelection();
            }

            function clearSelection() {
                selectedEnrollmentIds = [];
                syncCardSelection();
            }

            function syncCardSelection() {
                document.querySelectorAll('.np-card').forEach(function(card) {
                    const id = Number(card.dataset.enrollmentId);
                    const selected = selectedEnrollmentIds.includes(id);
                    card.classList.toggle('np-card-selected', selected);
                    const checkbox = card.querySelector('.class-checkbox');
                    if (checkbox) checkbox.checked = selected;
                });
                updateBulkButton();
            }

            function updateBulkButton() {

                const bar = document.getElementById('np-pay-bar');
                const count = document.getElementById('np-selected-count');
                const total = document.getElementById('np-selected-total');

                const sum = selectedEnrollmentIds.reduce(function(acc, id) {
                    return acc + resolveClassFee(findClass(id));
                }, 0);

                if (count) count.textContent = String(selectedEnrollmentIds.length);
                if (total) total.textContent = formatMoney(sum);
                if (bar) bar.classList.toggle('d-none', selectedEnrollmentIds.length === 0);
                if (bulkButton) bulkButton.disabled = selectedEnrollmentIds.length === 0;

                document.body.classList.toggle('np-has-pay-bar', selectedEnrollmentIds.length > 0);
            }

            function paySelected() {
                if (!currentStudent) {
                    focusSearch();
                    return;
                }

                // Nothing selected but exactly one unpaid class: pay that one.
                if (!selectedEnrollmentIds.length) {
                    const unpaid = currentClasses.filter(function(item) {
                        return isPayable(item) && item.payment_status !== 'paid';
                    });

                    if (unpaid.length === 1) {
                        selectedEnrollmentIds = [Number(unpaid[0].enrollment_id)];
                        syncCardSelection();
                    } else {
                        toast('Select a class first (press its number, or A for all unpaid).', 'warning');
                        return;
                    }
                }

                openPaymentModal(selectedEnrollmentIds.slice());
            }

            // Card click toggles selection; the Pay button pays only that class.
            document.addEventListener('click', function(event) {

                const payButton = event.target.closest('.pay-single-btn');
                if (payButton) {
                    const id = Number(payButton.dataset.enrollmentId);
                    selectedEnrollmentIds = [id];
                    syncCardSelection();
                    openPaymentModal([id]);
                    return;
                }

                const card = event.target.closest('.np-card');
                if (card && !card.classList.contains('np-card-disabled')) {
                    toggleClass(card.dataset.enrollmentId);
                }
            });

            document.addEventListener('keydown', function(event) {
                const card = event.target.closest && event.target.closest('.np-card');
                if (card && (event.key === ' ' || event.key === 'Enter') && !event.target.closest('.pay-single-btn')) {
                    event.preventDefault();
                    toggleClass(card.dataset.enrollmentId);
                }
            });

            const selectUnpaidBtn = document.getElementById('select-unpaid-btn');
            if (selectUnpaidBtn) selectUnpaidBtn.addEventListener('click', selectAllUnpaid);

            const clearSelectionBtn = document.getElementById('clear-selection-btn');
            if (clearSelectionBtn) clearSelectionBtn.addEventListener('click', clearSelection);

            if (bulkButton) bulkButton.addEventListener('click', paySelected);

            /*
            |--------------------------------------------------------------------------
            | Next student
            |--------------------------------------------------------------------------
            */

            function resetForNextStudent() {
                currentStudent = null;
                currentClasses = [];
                selectedEnrollmentIds = [];
                updateBulkButton();
                clearError();

                if (studentSection) studentSection.classList.add('d-none');

                const input = document.getElementById('manual-code');
                if (input) input.value = '';

                focusSearch();
            }

            const nextStudentBtn = document.getElementById('next-student-btn');
            if (nextStudentBtn) nextStudentBtn.addEventListener('click', resetForNextStudent);

            /*
            |--------------------------------------------------------------------------
            | Payment Modal
            |--------------------------------------------------------------------------
            */

            function setPaymentMethod(method) {
                const hidden = document.getElementById('payment-method');
                if (hidden) hidden.value = method;

                document.querySelectorAll('#payment-method-buttons .np-method').forEach(function(btn) {
                    btn.classList.toggle('active', btn.dataset.method === method);
                });
            }

            document.querySelectorAll('#payment-method-buttons .np-method').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    setPaymentMethod(btn.dataset.method);
                });
            });

            function modalError(message) {
                const box = document.getElementById('payment-modal-error');
                if (!box) return;
                box.textContent = message || '';
                box.classList.toggle('d-none', !message);
            }

            function recalcAmount() {
                const feeInput = document.getElementById('payment-final-fee');
                const discountInput = document.getElementById('payment-discount');
                const amountInput = document.getElementById('payment-amount');
                const display = document.getElementById('modal-amount-display');

                const fee = Number(String(feeInput?.value || '0').replace(/,/g, '')) || 0;
                let discount = Number(discountInput?.value || 0) || 0;

                if (discount < 0) discount = 0;
                if (discount > fee) discount = fee;

                const amount = Math.max(fee - discount, 0);

                if (amountInput) amountInput.value = amount.toFixed(2);
                if (display) display.textContent = formatMoney(amount);
            }

            const discountInputEl = document.getElementById('payment-discount');
            if (discountInputEl) discountInputEl.addEventListener('input', recalcAmount);

            function openPaymentModal(enrollmentIds) {

                const ids = (enrollmentIds || []).filter(function(id) {
                    return isPayable(findClass(id));
                });

                if (!ids.length) {
                    toast('Nothing to pay for the selected class.', 'warning');
                    return;
                }

                const modalElement = document.getElementById('paymentModal');
                if (!modalElement) return;

                const first = findClass(ids[0]);
                const multiple = ids.length > 1;

                document.getElementById('payment-code').value = currentStudent?.custom_id || '';
                document.getElementById('payment-enrollment-id').value = ids.join(',');
                document.getElementById('payment-mark-method').value = currentMarkMethod || 'manual_web';

                document.getElementById('modal-class-label').textContent = multiple ? 'Classes' : 'Class';
                document.getElementById('modal-class-name').textContent = multiple ?
                    ids.length + ' classes selected' : (first.class_name || '-');
                document.getElementById('modal-category-name').textContent = multiple ?
                    ids.map(function(id) {
                        return findClass(id)?.class_name || '';
                    }).join(', ') : (first.category_name || '-');

                const total = ids.reduce(function(sum, id) {
                    return sum + resolveClassFee(findClass(id));
                }, 0);

                document.getElementById('payment-final-fee').value = formatMoney(total);

                // Discount is only for a single class (bulk pays the class fees).
                const discountGroup = document.getElementById('payment-discount-group');
                const discountInput = document.getElementById('payment-discount');
                if (discountInput) discountInput.value = '0';
                if (discountGroup) discountGroup.classList.toggle('d-none', multiple);

                document.getElementById('payment-note').value = '';

                setPaymentMethod('cash');
                setNextPaymentMonth(ids);
                recalcAmount();
                modalError('');

                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }

            const paymentModalEl = document.getElementById('paymentModal');
            if (paymentModalEl) {
                paymentModalEl.addEventListener('shown.bs.modal', function() {
                    document.getElementById('confirm-payment-btn')?.focus();
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Confirm Payment
            |--------------------------------------------------------------------------
            */

            let paymentInProgress = false;

            async function postJson(url, data) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(data)
                });

                let result = {};
                try {
                    result = await response.json();
                } catch (e) {
                    throw new Error('Unexpected server response (' + response.status + ').');
                }

                if (!response.ok || result.status !== 'success') {
                    // Laravel validation: show the first field error.
                    const firstError = result.errors ? Object.values(result.errors).flat()[0] : null;
                    throw new Error(firstError || result.message || 'Payment failed.');
                }

                return result;
            }

            async function confirmPayment() {

                if (paymentInProgress) return;

                const ids = String(document.getElementById('payment-enrollment-id')?.value || '')
                    .split(',').map(Number).filter(Boolean);

                if (!ids.length) {
                    modalError('Please select a class.');
                    return;
                }

                const month = document.getElementById('payment-month')?.value || '';
                if (!month) {
                    modalError('Please select the payment month.');
                    document.getElementById('payment-month')?.focus();
                    return;
                }

                const paymentMethod = document.getElementById('payment-method')?.value || 'cash';
                const note = document.getElementById('payment-note')?.value || '';
                const markMethod = document.getElementById('payment-mark-method')?.value || 'manual_web';

                const button = document.getElementById('confirm-payment-btn');
                const originalHtml = button.innerHTML;

                paymentInProgress = true;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                modalError('');

                try {
                    let result;

                    if (ids.length === 1) {
                        const amount = Number(document.getElementById('payment-amount')?.value || 0);
                        const discount = Number(document.getElementById('payment-discount')?.value || 0);

                        if (amount <= 0) {
                            throw new Error('Amount must be more than 0.');
                        }

                        result = await postJson(payUrl, {
                            code: currentStudent?.custom_id || '',
                            enrollment_id: ids[0],
                            amount: amount,
                            discount_amount: discount > 0 ? discount : 0,
                            payment_month: month + '-01',
                            mark_method: markMethod,
                            payment_method: paymentMethod,
                            note: note
                        });
                    } else {
                        result = await postJson(bulkPayUrl, {
                            payments: ids.map(function(id) {
                                return {
                                    code: currentStudent?.custom_id || '',
                                    enrollment_id: id,
                                    amount: resolveClassFee(findClass(id)),
                                    payment_month: month + '-01',
                                    mark_method: markMethod,
                                    payment_method: paymentMethod,
                                    note: note
                                };
                            })
                        });
                    }

                    bootstrap.Modal.getInstance(document.getElementById('paymentModal'))?.hide();

                    lastReceipt = result.data?.receipt || null;

                    showReceipt(lastReceipt);
                    setTimeout(function() {
                        printReceipt(lastReceipt);
                    }, 350);

                    toast('Payment saved.', 'success');

                    await readStudent(currentStudent?.custom_id, currentMarkMethod, { autoOpen: false });

                } catch (error) {
                    modalError(error.message);
                } finally {
                    paymentInProgress = false;
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            }

            const confirmBtn = document.getElementById('confirm-payment-btn');
            if (confirmBtn) confirmBtn.addEventListener('click', confirmPayment);

            const receiptNextBtn = document.getElementById('receipt-next-btn');
            if (receiptNextBtn) {
                receiptNextBtn.addEventListener('click', function() {
                    bootstrap.Modal.getInstance(document.getElementById('receiptModal'))?.hide();
                    resetForNextStudent();
                });
            }

            const receiptModalEl = document.getElementById('receiptModal');
            if (receiptModalEl) {
                receiptModalEl.addEventListener('shown.bs.modal', function() {
                    receiptNextBtn?.focus();
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

                const rows =
                    receipt.rows ||
                    receipt.payments || [];

                const student =
                    receipt.student ||
                    rows[0]?.student || {};

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

                if (rows.length) {

                    rows.forEach(function(row) {

                        const classFee = Number(
                            row.class_fee ??
                            row.final_fee ??
                            row.total_fee ??
                            0
                        );

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

                        const paymentStatus =
                            row.payment_status ||
                            row.status ||
                            '-';

                        html += `
    <div class="receipt-item">

        <strong>${escapeHtml(className)}</strong><br>

        Category: ${escapeHtml(category)}<br>

        Grade: ${escapeHtml(grade)}<br>

        Teacher: ${escapeHtml(teacher)}<br>

        Month: ${escapeHtml(
            formatReceiptMonth(month)
        )}<br>

        Receipt: ${escapeHtml(receiptNumber)}<br>

        Class Fee: Rs. ${formatMoney(classFee)}<br>

        Payment Status: ${escapeHtml(
            String(paymentStatus).toUpperCase()
        )}

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

                const totalClassFee = Number(
                    receipt.summary?.total_class_fee || 0
                );

                html += `
    <div class="receipt-summary">

        <strong>
            TOTAL FEE: Rs. ${formatMoney(totalClassFee)}
        </strong>

    </div>

    <div class="receipt-line"></div>

    Payment: ${escapeHtml(paymentMethod || '-')}

    <br><br>

    <div class="text-center">
        Thank You!
    </div>

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
                    toast('Please allow popups to print the receipt automatically.', 'warning');
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
                        toast('Please allow popups to print the receipt.', 'warning');
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
            | F1 help, F2 or / search, 1-9 select class, A all unpaid, F8 pay,
            | Esc next student, F9 print again. Inside the payment modal: Enter
            | confirm, Alt+1..6 method, Alt+M month.
            */

            const methodKeys = ['cash', 'card', 'bank_transfer', 'online', 'cheque', 'other'];

            function isTyping(target) {
                if (!target) return false;
                const tag = target.tagName;
                return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || target.isContentEditable;
            }

            document.addEventListener('keydown', function(event) {

                const key = event.key;
                const paymentOpen = isModalOpen('paymentModal');
                const receiptOpen = isModalOpen('receiptModal');
                const helpOpen = isModalOpen('shortcutsModal');

                // Always available
                if (key === 'F1') {
                    event.preventDefault();
                    const help = document.getElementById('shortcutsModal');
                    if (help) bootstrap.Modal.getOrCreateInstance(help).toggle();
                    return;
                }

                if (key === 'F9') {
                    event.preventDefault();
                    if (lastReceipt) {
                        printReceipt(lastReceipt);
                    } else {
                        toast('No receipt to print yet.', 'warning');
                    }
                    return;
                }

                if (helpOpen) return;

                // Payment modal
                if (paymentOpen) {
                    if (event.altKey && /^[1-6]$/.test(key)) {
                        event.preventDefault();
                        setPaymentMethod(methodKeys[Number(key) - 1]);
                        return;
                    }
                    if (event.altKey && key.toLowerCase() === 'm') {
                        event.preventDefault();
                        document.getElementById('payment-month')?.focus();
                        return;
                    }
                    if (key === 'Enter' && event.target?.tagName !== 'TEXTAREA') {
                        event.preventDefault();
                        confirmPayment();
                    }
                    return;
                }

                // Receipt modal
                if (receiptOpen) {
                    if (key === 'Enter') {
                        event.preventDefault();
                        receiptNextBtn?.click();
                    }
                    return;
                }

                if (anyModalOpen()) return;

                if (key === 'F2') {
                    event.preventDefault();
                    focusSearch();
                    return;
                }

                if (key === 'F8' || (key === 'Enter' && event.ctrlKey)) {
                    event.preventDefault();
                    paySelected();
                    return;
                }

                if (key === 'Escape') {
                    event.preventDefault();
                    resetForNextStudent();
                    return;
                }

                // Single-key shortcuts only when not typing in a field
                if (isTyping(event.target) || event.ctrlKey || event.altKey || event.metaKey) return;

                if (key === '/') {
                    event.preventDefault();
                    focusSearch();
                    return;
                }

                if (!currentStudent) return;

                if (/^[1-9]$/.test(key)) {
                    const item = currentClasses[Number(key) - 1];
                    if (item) {
                        event.preventDefault();
                        if (isPayable(item)) {
                            toggleClass(item.enrollment_id);
                        } else {
                            toast(isFreeCard(item) ? 'Free card class - no payment needed.' : 'No fee set for this class.', 'warning');
                        }
                    }
                    return;
                }

                if (key.toLowerCase() === 'a') {
                    event.preventDefault();
                    selectAllUnpaid();
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

            // Leaving the search box with a student loaded: let number keys work.
            const manualInput = document.getElementById('manual-code');
            if (manualInput) {
                manualInput.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && currentStudent) {
                        event.preventDefault();
                        event.stopPropagation();
                        manualInput.blur();
                    }
                });
            }

            /*
            |--------------------------------------------------------------------------
            | QR Scanner
            |--------------------------------------------------------------------------
            | The camera decodes the same QR many times a second. Only use a
            | decode when nothing is loading, no dialog is open, and it is not the
            | same code we just read.
            */

            let lastQrCode = null;
            let lastQrAt = 0;

            function handleQrDecode(decodedText) {
                const code = String(decodedText || '').trim();
                if (!code) return;
                if (isLoadingStudent || paymentInProgress || anyModalOpen()) return;

                const now = Date.now();
                if (code === lastQrCode && now - lastQrAt < 4000) return;
                if (currentStudent && code === currentStudent.custom_id && now - lastQrAt < 15000) return;

                lastQrCode = code;
                lastQrAt = now;

                const statusEl = document.getElementById('qr-reader-status');
                if (statusEl) statusEl.innerHTML = '<i class="bi bi-check2-circle text-success me-1"></i>Scanned: ' + escapeHtml(code);

                readStudent(code, 'qr_web');
            }

            function startQrScanner() {

                const readerEl = document.getElementById('qr-reader');
                const statusEl = document.getElementById('qr-reader-status');

                if (!readerEl || typeof Html5Qrcode === 'undefined') {
                    if (statusEl) statusEl.textContent = 'QR scanner could not load. Type the Student ID instead.';
                    return;
                }

                html5QrCode = new Html5Qrcode('qr-reader');

                Html5Qrcode.getCameras()
                    .then(function(devices) {

                        if (!devices.length) {
                            if (statusEl) statusEl.textContent = 'No camera found. Type the Student ID instead.';
                            return;
                        }

                        const cameraId = devices[0].id;

                        html5QrCode.start(
                            cameraId, {
                                fps: 10,
                                qrbox: {
                                    width: 200,
                                    height: 200
                                }
                            },
                            handleQrDecode,
                            function() {
                                // Ignore scan errors
                            }
                        );

                        if (statusEl) statusEl.innerHTML = '<i class="bi bi-camera-video text-success me-1"></i>Camera ready &ndash; show the student QR';

                    })
                    .catch(function(error) {
                        if (statusEl) statusEl.textContent = 'Camera permission required. Type the Student ID instead.';
                        console.error(error);
                    });

            }

            startQrScanner();
            focusSearch();

        });
    </script>
@endpush
