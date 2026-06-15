@extends('layouts.app')

@section('title', 'Today Receipt')
@section('page-title', 'Today Receipt')

@section('content')
    <div class="receipt-page">

        <div class="page-toolbar card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="mb-1 fw-bold">Today Receipts</h4>
                        <div class="text-muted" id="selectedDateLabel">{{ now()->format('Y-m-d') }}</div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary toolbar-btn">
                            <i class="bi bi-arrow-left me-1"></i>
                            Back
                        </a>

                        <button type="button" onclick="window.print()" class="btn btn-primary toolbar-btn">
                            <i class="bi bi-printer me-1"></i>
                            Print
                        </button>
                    </div>
                </div>

                <div class="row g-3 mt-3 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" id="searchInput" class="form-control custom-filter-input"
                            placeholder="Search student / QR / class / teacher / category" value="">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label fw-semibold">Payment Date</label>
                        <input type="date" id="dateInput" class="form-control custom-filter-input"
                            value="{{ now()->format('Y-m-d') }}">
                    </div>

                    <div class="col-lg-2">
                        <label class="form-label fw-semibold">Per Page</label>
                        <select id="perPageInput" class="form-select custom-filter-input">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <div class="col-lg-2 d-grid">
                        <label class="form-label fw-semibold invisible">Search</label>
                        <button type="button" id="filterBtn" class="btn btn-primary toolbar-btn">
                            <i class="bi bi-search me-1"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="card summary-card summary-blue">
                    <div class="card-body">
                        <i class="bi bi-people-fill summary-icon"></i>
                        <h6>Enrollments with Today Payments</h6>
                        <h2 id="summaryEnrollments">0</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card summary-card summary-green">
                    <div class="card-body">
                        <i class="bi bi-receipt summary-icon"></i>
                        <h6>Today Payment Count</h6>
                        <h2 id="summaryPaymentCount">0</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card summary-card summary-orange">
                    <div class="card-body">
                        <i class="bi bi-cash-stack summary-icon"></i>
                        <h6>Today Total Amount</h6>
                        <h2 id="summaryTotalAmount">Rs. 0.00</h2>
                    </div>
                </div>
            </div>

        </div>

        <div class="card receipt-main-card">
            <div
                class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">Receipt List</h5>
                    <small class="text-muted">Payments collected for the selected date</small>
                </div>

                <span class="badge bg-primary-subtle text-primary custom-badge" id="resultBadge">
                    Loading...
                </span>
            </div>

            <div class="card-body">

                <div id="loadingState" class="loading-state text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <div class="text-muted">Loading receipts...</div>
                </div>

                <div id="errorState" class="alert alert-danger d-none"></div>

                <div id="emptyState" class="empty-state text-center d-none">
                    <i class="bi bi-receipt"></i>
                    <h5 class="mt-3 mb-2">No payments found</h5>
                    <p class="text-muted mb-0">There are no receipt records for this date.</p>
                </div>

                <div id="tableState" class="table-responsive d-none">
                    <table class="table align-middle receipt-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Student</th>
                                <th>QR Code</th>
                                <th>Class</th>
                                <th>Teacher</th>
                                <th>Grade</th>
                                <th>Category</th>
                                <th>Fee</th>
                                <th>Today Payments</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="receiptTableBody">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="paginationWrapper"
                    class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 d-none">
                    <div class="text-muted small" id="paginationInfo">Showing results</div>

                    <div class="d-flex gap-2">
                        <button type="button" id="prevPageBtn" class="btn btn-outline-secondary toolbar-btn btn-sm">
                            <i class="bi bi-arrow-left"></i>
                        </button>

                        <button type="button" id="nextPageBtn" class="btn btn-outline-secondary toolbar-btn btn-sm">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .receipt-page {
            animation: fadeIn .4s ease;
        }

        .page-toolbar,
        .receipt-main-card {
            border: none;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        }

        .toolbar-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            border: none;
            transition: .2s ease;
        }

        .toolbar-btn:hover {
            transform: translateY(-2px);
        }

        .custom-filter-input {
            border-radius: 14px !important;
            min-height: 48px;
            border: 1px solid #dbe3ec;
            box-shadow: none;
        }

        .custom-filter-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .summary-card {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            position: relative;
            color: white;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
        }

        .summary-card .card-body {
            padding: 1.5rem;
            position: relative;
        }

        .summary-card h6 {
            opacity: .9;
            margin-bottom: .5rem;
            font-size: .9rem;
        }

        .summary-card h2 {
            margin: 0;
            font-weight: 800;
            font-size: 1.8rem;
        }

        .summary-blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .summary-green {
            background: linear-gradient(135deg, #059669, #10b981);
        }

        .summary-orange {
            background: linear-gradient(135deg, #ea580c, #f97316);
        }

        .summary-icon {
            position: absolute;
            right: 18px;
            top: 18px;
            font-size: 2rem;
            opacity: .18;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-weight: 600;
        }

        .receipt-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc !important;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .receipt-table td {
            vertical-align: middle;
        }

        .receipt-list {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .receipt-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: .8rem;
            transition: .2s ease;
        }

        .receipt-item:hover {
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .receipt-item .top-line {
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .receipt-item .sub-line {
            font-size: .82rem;
            color: #64748b;
            margin-top: .45rem;
            line-height: 1.6;
        }

        .delete-payment-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 0;
        }

        .empty-state {
            padding: 4rem 1rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
        }

        .loading-state {
            padding: 4rem 1rem;
        }

        .loading-state .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        @media (max-width: 768px) {
            .summary-card h2 {
                font-size: 1.4rem;
            }

            .receipt-item .top-line {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media print {

            .btn,
            .badge,
            .sidebar,
            .navbar,
            .card-header,
            .page-toolbar {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            .receipt-item {
                border: 1px solid #ddd !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            'use strict';

            const todayReceiptApiUrl = @json(route('api.student-payments.today-receipt'));
            const deletePaymentApiUrlTemplate = @json(url('/api/payments/__PAYMENT_ID__'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const loadingState = document.getElementById('loadingState');
            const errorState = document.getElementById('errorState');
            const emptyState = document.getElementById('emptyState');
            const tableState = document.getElementById('tableState');
            const receiptTableBody = document.getElementById('receiptTableBody');
            const resultBadge = document.getElementById('resultBadge');

            const summaryEnrollments = document.getElementById('summaryEnrollments');
            const summaryPaymentCount = document.getElementById('summaryPaymentCount');
            const summaryTotalAmount = document.getElementById('summaryTotalAmount');

            const searchInput = document.getElementById('searchInput');
            const dateInput = document.getElementById('dateInput');
            const perPageInput = document.getElementById('perPageInput');
            const filterBtn = document.getElementById('filterBtn');
            const prevPageBtn = document.getElementById('prevPageBtn');
            const nextPageBtn = document.getElementById('nextPageBtn');
            const paginationWrapper = document.getElementById('paginationWrapper');
            const paginationInfo = document.getElementById('paginationInfo');
            const selectedDateLabel = document.getElementById('selectedDateLabel');

            let currentPage = 1;

            loadTodayReceipts();

            filterBtn.addEventListener('click', function () {
                currentPage = 1;
                loadTodayReceipts();
            });

            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    currentPage = 1;
                    loadTodayReceipts();
                }
            });

            dateInput.addEventListener('change', function () {
                currentPage = 1;
                loadTodayReceipts();
            });

            perPageInput.addEventListener('change', function () {
                currentPage = 1;
                loadTodayReceipts();
            });

            prevPageBtn.addEventListener('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    loadTodayReceipts();
                }
            });

            nextPageBtn.addEventListener('click', function () {
                currentPage++;
                loadTodayReceipts();
            });

            async function loadTodayReceipts() {
                hideAllStates();
                loadingState.classList.remove('d-none');
                resultBadge.textContent = 'Loading...';

                const search = searchInput.value.trim();
                const date = dateInput.value || '';
                const perPage = perPageInput.value || 10;

                try {
                    const url = new URL(todayReceiptApiUrl, window.location.origin);
                    url.searchParams.set('page', currentPage);
                    url.searchParams.set('search', search);
                    url.searchParams.set('date', date);
                    url.searchParams.set('per_page', perPage);

                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Failed to load today receipts.');
                    }

                    renderSummary(result.summary || {});
                    renderTable(result.data || []);
                    renderPagination(result.pagination || {});

                    selectedDateLabel.textContent = result.filters?.date || date || '{{ now()->format('Y-m-d') }}';

                    if ((result.data || []).length > 0) {
                        tableState.classList.remove('d-none');
                        resultBadge.textContent = `${result.pagination?.total || result.data.length} Records`;
                    } else {
                        emptyState.classList.remove('d-none');
                        resultBadge.textContent = 'No Data';
                    }
                } catch (error) {
                    errorState.textContent = error.message || 'Something went wrong while loading receipts.';
                    errorState.classList.remove('d-none');
                    resultBadge.textContent = 'Error';
                } finally {
                    loadingState.classList.add('d-none');
                }
            }

            function renderSummary(summary) {
                summaryEnrollments.textContent = summary.enrollment_count || 0;
                summaryPaymentCount.textContent = summary.payment_count || 0;
                summaryTotalAmount.textContent = formatMoney(summary.total_amount || 0);
            }

            function renderTable(data) {
                if (!data.length) {
                    receiptTableBody.innerHTML = `
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            No payments found for this date.
                                        </td>
                                    </tr>
                                `;
                    return;
                }

                receiptTableBody.innerHTML = data.map((item, index) => {
                    const payments = Array.isArray(item.today_payments) ? item.today_payments : [];

                    return `
                                    <tr>
                                        <td>${index + 1 + ((currentPage - 1) * parseInt(perPageInput.value, 10))}</td>

                                        <td>
                                            <div class="fw-bold">${escapeHtml(item.initial_name || '-')}${item.full_name ? ` <small class="text-muted">(${escapeHtml(item.full_name)})</small>` : ''}</div>
                                            <small class="text-muted">
                                                ${escapeHtml(item.mobile || '-')}
                                                ${item.guardian_mobile ? `<br>Guardian: ${escapeHtml(item.guardian_mobile)}` : ''}
                                            </small>
                                        </td>

                                        <td>${escapeHtml(item.qr_code || '-')}</td>

                                        <td>
                                            <div class="fw-bold">${escapeHtml(item.class_name || '-')}</div>
                                            <small class="text-muted">ID: ${escapeHtml(item.student_class_id || '-')}</small>
                                        </td>

                                        <td>
                                            <div class="fw-bold">${escapeHtml(item.teacher_name || '-')}</div>
                                            <small class="text-muted">ID: ${escapeHtml(item.teacher_custom_id || '-')}</small>
                                        </td>

                                        <td>${escapeHtml(item.grade_name || '-')}</td>
                                        <td>${escapeHtml(item.category_name || '-')}</td>

                                        <td>
                                            ${Number(item.is_free_card) === 1 || item.is_free_card === true
                            ? '<span class="badge bg-success">FREE CARD</span>'
                            : formatMoney(item.final_fee || 0)
                        }
                                        </td>

                                        <td>
                                            <div class="receipt-list">
                                                ${payments.map(payment => `
                                                    <div class="receipt-item">
                                                        <div class="top-line">
                                                            <span class="fw-bold">${escapeHtml(payment.receipt_number || 'N/A')}</span>

                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge bg-primary-subtle text-primary custom-badge">
                                                                    ${formatMoney(payment.amount || 0)}
                                                                </span>

                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-danger delete-payment-btn"
                                                                    data-payment-id="${payment.id}">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="sub-line">
                                                        Paid Date: ${payment.paid_at
                                ? new Date(payment.paid_at)
                                    .toLocaleString('sv-SE', {
                                        hour12: false
                                    })
                                    .slice(0, 16)
                                : '-'
                            }
                                                            <br>
                                                            Method: ${escapeHtml(payment.payment_method || '-')}
                                                            ${payment.note ? `<br>Note: ${escapeHtml(payment.note)}` : ''}
                                                        </div>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        </td>

                                        <td class="fw-bold text-success">
                                            ${formatMoney(item.today_payment_total || 0)}
                                        </td>
                                    </tr>
                                `;
                }).join('');
            }

            function renderPagination(pagination) {
                const total = Number(pagination.total || 0);
                const current = Number(pagination.current_page || 1);
                const last = Number(pagination.last_page || 1);
                const perPage = Number(pagination.per_page || perPageInput.value || 10);

                if (total <= perPage) {
                    paginationWrapper.classList.add('d-none');
                    return;
                }

                paginationWrapper.classList.remove('d-none');

                paginationInfo.textContent = `Page ${current} of ${last} · ${total} total records`;

                prevPageBtn.disabled = current <= 1;
                nextPageBtn.disabled = current >= last;

                currentPage = current;
            }

            async function deletePayment(paymentId) {
                if (!confirm('Are you sure you want to delete this payment?')) {
                    return;
                }

                try {
                    const deleteUrl = deletePaymentApiUrlTemplate.replace('__PAYMENT_ID__', paymentId);

                    const response = await fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken || ''
                        }
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Failed to delete payment.');
                    }

                    await loadTodayReceipts();
                } catch (error) {
                    alert(error.message || 'Something went wrong while deleting payment.');
                }
            }

            document.addEventListener('click', function (event) {
                const deleteBtn = event.target.closest('.delete-payment-btn');

                if (!deleteBtn) return;

                const paymentId = deleteBtn.getAttribute('data-payment-id');

                if (!paymentId) return;

                deletePayment(paymentId);
            });

            function hideAllStates() {
                loadingState.classList.add('d-none');
                errorState.classList.add('d-none');
                emptyState.classList.add('d-none');
                tableState.classList.add('d-none');
                paginationWrapper.classList.add('d-none');
            }

            function formatMoney(value) {
                const num = Number(value || 0);
                return 'Rs. ' + num.toLocaleString('en-LK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function escapeHtml(value) {
                if (value === null || value === undefined) return '-';

                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }
        });
    </script>
@endpush