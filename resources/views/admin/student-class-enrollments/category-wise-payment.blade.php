@extends('layouts.app')

@section('title', 'Category Wise Student Payment Details')
@section('page-title', 'Category Wise Student Payment Details')

@section('content')
    <div class="category-payment-page">

        @php
            $selectedYear = $year ?? request()->year;
            $selectedMonth = $month ?? request()->month;
            $reportMonth = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y');

            $studentsArray = $students ?? [];
            $totalStudents = count($studentsArray);

            $totalPaid = collect($studentsArray)->filter(function ($student) {
                return !($student['is_free_card'] ?? false) && ($student['monthly_paid_amount'] ?? 0) >= ($student['final_fee'] ?? 0);
            })->count();

            $totalPartial = collect($studentsArray)->filter(function ($student) {
                return !($student['is_free_card'] ?? false) && ($student['monthly_paid_amount'] ?? 0) > 0 && ($student['monthly_paid_amount'] ?? 0) < ($student['final_fee'] ?? 0);
            })->count();

            $totalUnpaid = collect($studentsArray)->filter(function ($student) {
                return !($student['is_free_card'] ?? false) && ($student['monthly_paid_amount'] ?? 0) <= 0;
            })->count();

            $totalFreeCard = collect($studentsArray)->filter(function ($student) {
                return ($student['is_free_card'] ?? false) == true;
            })->count();

            $totalCollected = collect($studentsArray)->sum('monthly_paid_amount');
            $totalExpected = collect($studentsArray)->sum('final_fee');
            $collectionPercent = $totalExpected > 0 ? ($totalCollected / $totalExpected) * 100 : 0;
        @endphp

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">

                <div>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary custom-btn mb-3">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Summary
                    </a>

                    <h3 class="fw-bold mb-1">Category Wise Student Payment Details</h3>
                    <p class="text-muted mb-0">
                        {{ $reportMonth }} Payment Details
                    </p>
                </div>

                <div class="hero-actions">
                    <button onclick="window.print()" class="btn btn-primary custom-btn">
                        <i class="bi bi-printer me-1"></i>
                        Print Report
                    </button>

                    <button class="btn btn-outline-success custom-btn" disabled>
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        Export Excel
                    </button>

                    <button class="btn btn-outline-danger custom-btn" disabled>
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        Export PDF
                    </button>

                    <button class="btn btn-outline-dark custom-btn" disabled>
                        <i class="bi bi-download me-1"></i>
                        Download
                    </button>
                </div>

            </div>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="row g-3 mb-4">

            <div class="col-6 col-md-2">
                <div class="summary-card summary-blue">
                    <div class="card-body text-center">
                        <small class="summary-label">Total Students</small>
                        <h4 class="summary-value">{{ number_format($totalStudents) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="summary-card summary-green">
                    <div class="card-body text-center">
                        <small class="summary-label">Paid</small>
                        <h4 class="summary-value">{{ number_format($totalPaid) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="summary-card summary-warning">
                    <div class="card-body text-center">
                        <small class="summary-label">Partial</small>
                        <h4 class="summary-value">{{ number_format($totalPartial) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="summary-card summary-red">
                    <div class="card-body text-center">
                        <small class="summary-label">Unpaid</small>
                        <h4 class="summary-value">{{ number_format($totalUnpaid) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="summary-card summary-info">
                    <div class="card-body text-center">
                        <small class="summary-label">Free Card</small>
                        <h4 class="summary-value">{{ number_format($totalFreeCard) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="summary-card summary-dark">
                    <div class="card-body text-center">
                        <small class="summary-label">Collection</small>
                        <h4 class="summary-value">{{ number_format($totalCollected, 0) }}</h4>
                        <small class="summary-sub">/ {{ number_format($totalExpected, 0) }}</small>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $collectionPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- MAIN CARD -->
        <div class="main-card">

            <div class="main-card-header">
                <div>
                    <h4 class="mb-1 fw-bold">Student Payment Details</h4>
                    <p class="mb-0 text-muted">Monthly collection breakdown by student</p>
                </div>

                <div class="header-actions">
                    <button class="btn btn-outline-primary custom-btn" disabled>
                        <i class="bi bi-funnel me-1"></i>
                        Advanced Filter
                    </button>
                    <button class="btn btn-outline-warning custom-btn" disabled>
                        <i class="bi bi-bell me-1"></i>
                        Send Reminder
                    </button>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Student ID</th>
                            <th width="15%">Student Name</th>
                            <th width="10%">Mobile</th>
                            <th width="10%">Final Fee</th>
                            <th width="10%">Paid Amount</th>
                            <th width="10%">Status</th>
                            <th width="8%">Free Card</th>
                            <th width="10%">Custom Fee</th>
                            <th width="10%">Discount</th>
                            <th width="7%">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($studentsArray as $student)
                            @php
                                $paidAmount = $student['monthly_paid_amount'] ?? 0;
                                $finalFee = $student['final_fee'] ?? 0;
                            @endphp

                            <tr>
                                <td>{{ $loop->iteration + ((request('page', 1) - 1) * request('per_page', 50)) }}</td>
                                <td>{{ $student['student_custom_id'] ?? 'N/A' }}</td>
                                <td>
                                    <strong>{{ $student['student_name'] ?? 'N/A' }}</strong>
                                </td>
                                <td>{{ $student['mobile'] ?? 'N/A' }}</td>
                                <td class="text-end">LKR {{ number_format($finalFee, 2) }}</td>
                                <td class="text-end">
                                    @if($paidAmount > 0)
                                        <span class="text-success fw-bold">LKR {{ number_format($paidAmount, 2) }}</span>
                                    @else
                                        <span class="text-danger">LKR 0.00</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($student['is_free_card'] ?? false)
                                        <span class="badge bg-info custom-badge">🎫 Free Card</span>
                                    @elseif($paidAmount >= $finalFee)
                                        <span class="badge bg-success custom-badge">✓ Paid</span>
                                    @elseif($paidAmount > 0)
                                        <span class="badge bg-warning text-dark custom-badge">⚠ Partial</span>
                                    @else
                                        <span class="badge bg-danger custom-badge">✗ Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($student['is_free_card'] ?? false)
                                        <span class="badge bg-info custom-badge">Yes</span>
                                    @else
                                        <span class="badge bg-secondary custom-badge">No</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!empty($student['custom_fee']))
                                        <span class="badge bg-info custom-badge">
                                            LKR {{ number_format($student['custom_fee'], 2) }}
                                        </span>
                                        @if(!empty($student['custom_fee_reason']))
                                            <br>
                                            <small class="text-muted">
                                                {{ \Illuminate\Support\Str::limit($student['custom_fee_reason'], 20) }}
                                            </small>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(($student['discount_percentage'] ?? 0) > 0)
                                        <span class="badge bg-success custom-badge">
                                            {{ $student['discount_percentage'] }}%
                                        </span>
                                        @if(!empty($student['discount_reason']))
                                            <br>
                                            <small class="text-muted">
                                                {{ \Illuminate\Support\Str::limit($student['discount_reason'], 20) }}
                                            </small>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-success action-btn view-payments-btn"
                                            data-student-name="{{ $student['student_name'] }}"
                                            data-payments='@json($student['payments'] ?? [])' title="View Payment History">
                                            <i class="bi bi-receipt"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-primary action-btn" disabled
                                            title="Future Feature">
                                            <i class="bi bi-person"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-warning action-btn" disabled
                                            title="SMS Reminder - Future Feature">
                                            <i class="bi bi-chat-dots"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-dark action-btn" disabled
                                            title="Receipt Download - Future Feature">
                                            <i class="bi bi-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No students found for this category.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($totalStudents > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="fw-bold text-end">Total:</td>
                                <td class="fw-bold text-end">LKR {{ number_format($totalExpected, 2) }}</td>
                                <td class="fw-bold text-end">LKR {{ number_format($totalCollected, 2) }}</td>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Pagination --}}
            @if(isset($pagination) && $pagination['total'] > $pagination['per_page'])
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="text-muted small">
                            Showing {{ $pagination['from'] }} to {{ $pagination['to'] }} of {{ $pagination['total'] }} students
                        </div>

                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item {{ $pagination['current_page'] == 1 ? 'disabled' : '' }}">
                                    <a class="page-link"
                                        href="{{ $pagination['current_page'] > 1 ? url()->current() . '?page=' . ($pagination['current_page'] - 1) . '&per_page=' . $pagination['per_page'] : '#' }}">
                                        <i class="bi bi-chevron-left"></i> Previous
                                    </a>
                                </li>

                                @php
                                    $start = max(1, $pagination['current_page'] - 2);
                                    $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                                @endphp

                                @if($start > 1)
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="{{ url()->current() }}?page=1&per_page={{ $pagination['per_page'] }}">1</a>
                                    </li>
                                    @if($start > 2)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                @endif

                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $pagination['current_page'] == $i ? 'active' : '' }}">
                                        <a class="page-link"
                                            href="{{ url()->current() }}?page={{ $i }}&per_page={{ $pagination['per_page'] }}">{{ $i }}</a>
                                    </li>
                                @endfor

                                @if($end < $pagination['last_page'])
                                    @if($end < $pagination['last_page'] - 1)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="{{ url()->current() }}?page={{ $pagination['last_page'] }}&per_page={{ $pagination['per_page'] }}">{{ $pagination['last_page'] }}</a>
                                    </li>
                                @endif

                                <li
                                    class="page-item {{ $pagination['current_page'] == $pagination['last_page'] ? 'disabled' : '' }}">
                                    <a class="page-link"
                                        href="{{ $pagination['current_page'] < $pagination['last_page'] ? url()->current() . '?page=' . ($pagination['current_page'] + 1) . '&per_page=' . $pagination['per_page'] : '#' }}">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            @endif

        </div>

        <!-- PAYMENT HISTORY MODAL -->
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-receipt me-2"></i>
                            Payment History - <span id="paymentStudentName"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Amount (LKR)</th>
                                        <th>Payment Month</th>
                                        <th>Method</th>
                                        <th>Receipt Number</th>
                                        <th>Reference Number</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentModalBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Select a student to view payments
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary custom-btn" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAYMENT GUIDE -->
        <div class="mt-4">
            <div class="alert alert-info custom-alert mb-0">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Payment Status Guide:</strong>
                <div class="row mt-2 g-2">
                    <div class="col-md-3">
                        <span class="badge bg-success custom-badge">✓ Paid</span>
                        <small class="d-block text-muted mt-1">Full payment completed</small>
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-warning text-dark custom-badge">⚠ Partial</span>
                        <small class="d-block text-muted mt-1">Partial payment received</small>
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-info custom-badge">🎫 Free Card</span>
                        <small class="d-block text-muted mt-1">Free student (no fee)</small>
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-danger custom-badge">✗ Unpaid</span>
                        <small class="d-block text-muted mt-1">No payment received</small>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .category-payment-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .main-card,
        .summary-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
            overflow: hidden;
        }

        .hero-card {
            padding: 1.35rem 1.5rem;
            margin-bottom: 1.5rem;
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

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .custom-btn:hover:not(:disabled) {
            transform: translateY(-2px);
        }

        .custom-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .summary-card {
            color: #fff;
            min-height: 130px;
        }

        .summary-card .card-body {
            padding: 1rem;
        }

        .summary-label {
            display: block;
            opacity: .9;
            margin-bottom: .35rem;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .summary-value {
            margin: 0;
            font-weight: 800;
        }

        .summary-sub {
            opacity: .85;
            font-size: .8rem;
        }

        .summary-blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .summary-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .summary-warning {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: #111827;
        }

        .summary-red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .summary-info {
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
        }

        .summary-dark {
            background: linear-gradient(135deg, #0f172a, #334155);
        }

        .main-card {
            padding: 1.5rem;
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .main-card-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .main-card-header p {
            margin: 0;
            color: #64748b;
        }

        .header-actions {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
        }

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            text-transform: uppercase;
            padding: 1rem;
            vertical-align: middle;
            white-space: nowrap;
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
            font-weight: 600;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .45rem .65rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .05);
        }

        .action-btn:hover:not(:disabled) {
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
        }

        .custom-alert {
            border-radius: 18px;
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .table-responsive {
            border-radius: 20px;
            overflow: auto;
        }

        .pagination {
            gap: .35rem;
        }

        .page-link {
            border: none;
            min-width: 42px;
            height: 42px;
            border-radius: 12px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .04);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .page-link:hover {
            background: #eff6ff;
            color: #2563eb;
        }

        @media (max-width: 768px) {

            .hero-content,
            .main-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-actions,
            .header-actions {
                width: 100%;
            }

            .hero-actions .btn,
            .header-actions .btn {
                flex: 1;
            }
        }

        @media print {

            .btn,
            .badge,
            .alert,
            .pagination,
            .custom-alert,
            .sidebar,
            .navbar {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .hero-card,
            .main-card,
            .summary-card {
                box-shadow: none !important;
            }

            .hero-card,
            .main-card {
                border: 1px solid #ddd !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentModalEl = document.getElementById('paymentModal');
            const paymentModal = new bootstrap.Modal(paymentModalEl);

            document.querySelectorAll('.view-payments-btn').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();

                    const studentName = this.getAttribute('data-student-name') || 'Student';
                    const payments = JSON.parse(this.getAttribute('data-payments') || '[]');

                    document.getElementById('paymentStudentName').textContent = studentName;

                    const modalBody = document.getElementById('paymentModalBody');
                    modalBody.innerHTML = '';

                    if (payments.length > 0) {
                        payments.forEach(payment => {
                            const row = modalBody.insertRow();

                            const paymentDate = payment.paid_at || payment.payment_date || 'N/A';
                            const amount = Number(payment.amount || 0).toFixed(2);

                            row.insertCell(0).textContent = paymentDate;
                            row.insertCell(1).className = 'text-end';
                            row.insertCell(1).textContent = 'LKR ' + amount;
                            row.insertCell(2).textContent = payment.payment_month || 'N/A';
                            row.insertCell(3).textContent = payment.payment_method || 'N/A';
                            row.insertCell(4).textContent = payment.receipt_number || 'N/A';
                            row.insertCell(5).textContent = payment.reference_number || 'N/A';
                        });
                    } else {
                        const row = modalBody.insertRow();
                        const cell = row.insertCell(0);
                        cell.colSpan = 6;
                        cell.className = 'text-center py-4 text-muted';
                        cell.innerHTML = '<i class="bi bi-inbox fs-4 d-block mb-2"></i>No payment records found.';
                    }

                    paymentModal.show();
                });
            });

            const perPageSelect = document.getElementById('perPageSelect');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', this.value);
                    url.searchParams.set('page', 1);
                    window.location.href = url.toString();
                });
            }
        });
    </script>
@endpush