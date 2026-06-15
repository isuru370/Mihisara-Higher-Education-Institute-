@extends('layouts.app')

@section('title', 'Teacher Salary Report')
@section('page-title', 'Teacher Salary Report')

@section('content')

    @php
        $teacher = $data['teacher'];
        $selectedYear = $year;
        $selectedMonth = $month;

        $isCurrentMonth = ($selectedYear == now()->year && $selectedMonth == now()->month);
        $isLastFiveDays = $isCurrentMonth && \Carbon\Carbon::now()->endOfMonth()->diffInDays(\Carbon\Carbon::now()) <= 5;
        $canShowPayButton = ($data['salary_status'] !== 'paid') && ($isLastFiveDays || !$isCurrentMonth);
        $deductionTotal = ($data['advance'] ?? 0) + ($data['deduction'] ?? 0) + ($data['other'] ?? 0);
    @endphp

    <div class="salary-details-page">

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">

                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($teacher->full_name, 0, 1)) }}
                    </div>

                    <div>
                        <h3 class="mb-1 fw-bold">
                            {{ $teacher->full_name }}
                        </h3>
                        <p class="mb-0 text-muted">
                            Salary Report • {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}
                        </p>
                    </div>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.teacher-salaries.index', ['year' => $selectedYear, 'month' => $selectedMonth]) }}"
                        class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </a>

                    <a href="{{ route('admin.teacher-salaries.index', ['year' => $selectedYear, 'month' => $selectedMonth]) }}"
                        class="btn btn-primary custom-btn">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Refresh
                    </a>
                </div>

            </div>
        </div>

        <!-- FILTER CARD -->
        <div class="filter-card mb-4">
            <form method="GET"
                action="{{ route('admin.teacher-salaries.show', ['teacher' => $teacher->id, 'year' => $selectedYear, 'month' => $selectedMonth]) }}"
                class="row g-3 align-items-end">

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Year</label>
                    <select name="year" class="form-select custom-input">
                        @for($y = now()->year; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Month</label>
                    <select name="month" class="form-select custom-input">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <button type="submit" class="btn btn-primary w-100 custom-btn">
                        <i class="bi bi-search me-1"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- SUMMARY -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-blue">
                    <div class="card-body">
                        <i class="bi bi-building summary-icon"></i>
                        <h6>Class Income</h6>
                        <h3>
                            LKR {{ number_format($data['total_class_income'] ?? 0, 2) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-green">
                    <div class="card-body">
                        <i class="bi bi-cash-stack summary-icon"></i>
                        <h6>Teacher Earnings</h6>
                        <h3>
                            LKR {{ number_format($data['teacher_earnings'] ?? 0, 2) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-red">
                    <div class="card-body">
                        <i class="bi bi-dash-circle summary-icon"></i>
                        <h6>Deductions</h6>
                        <h3>
                            LKR {{ number_format($deductionTotal, 2) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-primary">
                    <div class="card-body">
                        <i class="bi bi-wallet2 summary-icon"></i>
                        <h6>Net Payable</h6>
                        <h3>
                            LKR {{ number_format($data['net_payable'] ?? 0, 2) }}
                        </h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- STATUS -->
        <div class="status-card mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-1 fw-bold">Payment Status</h5>

                    @if($data['salary_status'] === 'paid')
                        <span class="badge bg-success custom-badge">Paid</span>
                        <div class="mt-2 text-success fw-semibold">
                            Paid Amount: LKR {{ number_format($data['salary_paid'] ?? 0, 2) }}
                        </div>
                    @else
                        <span class="badge bg-warning text-dark custom-badge">Pending</span>
                    @endif
                </div>

                <div class="small text-muted">
                    {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}
                </div>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="main-card mb-4">
            <div class="main-card-header">
                <div>
                    <h4>Salary Actions</h4>
                    <p>Process salary, print slip, or record advance and deduction</p>
                </div>

                <div class="header-badge">
                    @if($data['salary_status'] === 'paid')
                        PAID
                    @else
                        PENDING
                    @endif
                </div>
            </div>

            <div class="row g-3 align-items-stretch">

                <div class="col-lg-3 col-md-6">
                    @if($data['salary_status'] !== 'paid')
                        @if($canShowPayButton)
                            <form method="POST" action="{{ route('admin.teacher-salaries.pay', $teacher->id) }}">
                                @csrf
                                <input type="hidden" name="year" value="{{ $selectedYear }}">
                                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                                <input type="hidden" name="amount" value="{{ $data['net_payable'] }}">

                                <button type="submit" class="btn btn-success action-btn-full">
                                    <i class="bi bi-cash-coin me-1"></i>
                                    Pay Salary
                                    @if(($data['net_payable'] ?? 0) > 0)
                                        (LKR {{ number_format($data['net_payable'], 2) }})
                                    @else
                                        (LKR 0.00)
                                    @endif
                                </button>
                            </form>
                        @else
                            @if($isCurrentMonth && !$isLastFiveDays)
                                <button class="btn btn-secondary action-btn-full" disabled>
                                    <i class="bi bi-lock-fill me-1"></i>
                                    Pay Available in Last 5 Days
                                </button>
                            @else
                                <button class="btn btn-secondary action-btn-full" disabled>
                                    <i class="bi bi-lock-fill me-1"></i>
                                    Payment Unavailable
                                </button>
                            @endif
                        @endif
                    @else
                        <button class="btn btn-outline-success action-btn-full" disabled>
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Salary Paid
                        </button>
                    @endif
                </div>

                <div class="col-lg-3 col-md-6">
                    @if($data['salary_status'] === 'paid')
                        <a href="{{ route('admin.teacher-salaries.slip', [$teacher->id, $selectedYear, $selectedMonth]) }}?autoPrint=true"
                            target="_blank" class="btn btn-primary action-btn-full">
                            <i class="bi bi-printer-fill me-1"></i>
                            Print Salary Slip
                        </a>
                    @else
                        <button class="btn btn-secondary action-btn-full" disabled>
                            <i class="bi bi-printer-fill me-1"></i>
                            Print Slip (Pay first)
                        </button>
                    @endif
                </div>

                <div class="col-lg-3 col-md-6">
                    @if($isCurrentMonth)
                        <button class="btn btn-warning action-btn-full" data-bs-toggle="modal" data-bs-target="#advanceModal">
                            <i class="bi bi-wallet2 me-1"></i>
                            Advance / Deduction
                        </button>
                    @else
                        <button class="btn btn-outline-secondary action-btn-full" disabled
                            title="Advance/Deduction only available for current month">
                            <i class="bi bi-lock-fill me-1"></i>
                            Current Month Only
                        </button>
                    @endif
                </div>

                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('admin.teacher-salaries.payment-details', [
        'teacher' => $teacher->id,
        'year' => $selectedYear,
        'month' => $selectedMonth
    ]) }}" class="btn btn-outline-danger action-btn-full">
                        <i class="bi bi-graph-down me-1"></i>
                        Expenses
                    </a>
                </div>

            </div>

            <div class="row g-3 mt-1">
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('admin.teacher-salaries.payment-summary', [
        'teacher' => $teacher->id,
        'year' => $selectedYear,
        'month' => $selectedMonth
    ]) }}" class="btn btn-outline-info action-btn-full">
                        <i class="bi bi-eye me-1"></i>
                        Details
                    </a>
                </div>
            </div>

            <div class="mt-4">
                @if($data['salary_status'] === 'paid')
                    <div class="alert alert-success custom-alert mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Salary has been paid. Slip will open automatically.
                    </div>
                @else
                    @if($isCurrentMonth && !$isLastFiveDays)
                        <div class="alert alert-info custom-alert mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>Payment will be available in the last 5 days of
                                {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F') }}.</strong><br>
                            You can still add advances and deductions during this time.
                        </div>
                    @elseif($isCurrentMonth && $isLastFiveDays)
                        <div class="alert alert-success custom-alert mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Payment is now available. Please process the salary before month end.
                        </div>
                    @elseif(!$isCurrentMonth && $data['salary_status'] !== 'paid')
                        <div class="alert alert-warning custom-alert mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            This is a past month's pending salary. Payment can still be completed.
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- ADVANCE MODAL -->
        <div class="modal fade" id="advanceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="{{ route('admin.teacher-salaries.payment.store', $teacher->id) }}"
                        id="advanceForm">
                        @csrf

                        <div class="modal-header border-0">
                            <div>
                                <h5 class="modal-title fw-bold">Add Advance / Deduction</h5>
                                <small class="text-muted">
                                    {{ $teacher->full_name }}
                                </small>
                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body pt-0">

                            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Transaction Type</label>
                                <select name="payment_type" class="form-select custom-input" required>
                                    <option value="advance">Advance</option>
                                    <option value="deduction">Deduction</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Amount (LKR)</label>
                                <input type="number" name="amount" step="0.01" class="form-control custom-input" required
                                    id="advanceAmount" min="0.01" placeholder="Enter amount">

                                @if(($data['net_payable'] ?? 0) > 0)
                                    <small class="text-muted d-block mt-1">
                                        Maximum allowed: LKR {{ number_format($data['net_payable'], 2) }}
                                    </small>
                                @endif

                                <div class="text-danger small mt-2 d-none" id="amountWarning"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control custom-input"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Reason / Description</label>
                                <input type="text" name="reason" class="form-control custom-input"
                                    placeholder="Optional: Specify reason for this transaction">
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold">Additional Notes</label>
                                <textarea name="note" class="form-control custom-input" rows="3"
                                    placeholder="Optional: Any additional information"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary custom-btn" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit" class="btn btn-success custom-btn">
                                <i class="bi bi-save me-1"></i>
                                Save Transaction
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- AUTO SLIP OPEN --}}
        @if(session()->has('slip_open'))
            <script>
                window.addEventListener('load', function () {
                    const url = "{{ route('admin.teacher-salaries.slip', [session('slip_teacher'), session('slip_year'), session('slip_month')]) }}?autoPrint=true";
                    setTimeout(() => {
                        window.open(url, '_blank');
                    }, 500);
                });
            </script>
        @endif

    </div>
@endsection

@push('styles')
    <style>
        .salary-details-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .filter-card,
        .main-card,
        .status-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
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

        .profile-avatar {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.6rem;
            box-shadow: 0 10px 20px rgba(79, 70, 229, .20);
            flex-shrink: 0;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .custom-input {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            min-height: 48px;
            box-shadow: none;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .summary-card {
            border-radius: 24px;
            overflow: hidden;
            color: #fff;
            position: relative;
            min-height: 145px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
            border: none;
        }

        .summary-card .card-body {
            padding: 1.4rem;
            position: relative;
        }

        .summary-card h6 {
            opacity: .9;
            margin-bottom: .6rem;
        }

        .summary-card h3 {
            margin: 0;
            font-weight: 800;
            word-break: break-word;
        }

        .summary-icon {
            position: absolute;
            right: 18px;
            top: 18px;
            font-size: 2rem;
            opacity: .18;
        }

        .summary-blue {
            background: linear-gradient(135deg, #0f172a, #334155);
        }

        .summary-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .summary-red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .summary-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
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

        .header-badge {
            display: inline-flex;
            align-items: center;
            padding: .55rem .9rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
        }

        .action-btn-full {
            width: 100%;
            border-radius: 16px;
            padding: .9rem 1rem;
            font-weight: 700;
            transition: .2s ease;
        }

        .action-btn-full:hover {
            transform: translateY(-2px);
        }

        .custom-alert {
            border-radius: 18px;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
            font-weight: 600;
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
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const amountInput = document.getElementById('advanceAmount');
            const form = document.getElementById('advanceForm');
            const warningDiv = document.getElementById('amountWarning');
            const netPayable = {{ (float) ($data['net_payable'] ?? 0) }};

            if (amountInput && form && warningDiv && netPayable > 0) {
                amountInput.addEventListener('input', function () {
                    const value = parseFloat(this.value || '0');

                    if (value > netPayable) {
                        warningDiv.textContent = 'Amount cannot exceed Net Payable (LKR ' + netPayable.toFixed(2) + ')';
                        warningDiv.classList.remove('d-none');
                        this.classList.add('is-invalid');
                    } else {
                        warningDiv.classList.add('d-none');
                        warningDiv.textContent = '';
                        this.classList.remove('is-invalid');
                    }
                });

                form.addEventListener('submit', function (e) {
                    const value = parseFloat(amountInput.value || '0');

                    if (value > netPayable) {
                        e.preventDefault();
                        warningDiv.textContent = 'Amount cannot exceed Net Payable (LKR ' + netPayable.toFixed(2) + ')';
                        warningDiv.classList.remove('d-none');
                        amountInput.classList.add('is-invalid');
                    }
                });
            }
        });
    </script>
@endpush