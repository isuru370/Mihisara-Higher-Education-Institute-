@extends('layouts.app')

@section('title', 'Institute Monthly Income Report')
@section('page-title', 'Institute Monthly Income Report')

@section('content')
    <div class="institute-income-page">

        @php
            $reportMonth = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
            $currentYear = now()->year;
            $currentMonth = now()->month;
            $isCurrentMonth = ($year == $currentYear && $month == $currentMonth);
        @endphp

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <h3 class="fw-bold mb-1">Institute Monthly Income Report</h3>
                    <p class="text-muted mb-0">{{ $reportMonth }}</p>
                    @if(!$isCurrentMonth)
                        <span class="badge bg-secondary mt-2">
                            <i class="bi bi-calendar-lock me-1"></i> Read Only Mode (Past Month)
                        </span>
                    @else
                        <span class="badge bg-success mt-2">
                            <i class="bi bi-calendar-check me-1"></i> Current Month (Editable)
                        </span>
                    @endif
                </div>

                <div class="hero-actions">
                    @if($isCurrentMonth)
                        <button type="button" class="btn btn-danger custom-btn" data-bs-toggle="modal" data-bs-target="#expenseModal">
                            <i class="bi bi-cash-coin me-1"></i> Add Expense
                        </button>
                    @endif

                    <button onclick="window.print()" class="btn btn-primary custom-btn">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <!-- FILTER -->
        <div class="filter-card mb-4">
            <form method="GET" action="{{ route('admin.institute-income.monthly-report') }}"
                class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Year</label>
                    <input type="number" name="year" class="form-control custom-input" value="{{ $year }}" min="2000"
                        max="2100">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Month</label>
                    <select name="month" class="form-select custom-input">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <button class="btn btn-success w-100 custom-btn">
                        <i class="bi bi-search me-1"></i> Generate Report
                    </button>
                </div>

                <div class="col-lg-3 col-md-6">
                    <button type="button" onclick="window.print()" class="btn btn-outline-secondary w-100 custom-btn">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </form>
        </div>

        <!-- SUMMARY CARDS -->
        @include('admin.institute-income.components.summary-cards', [
            'summary' => $summary ?? []
        ])

        @include('admin.institute-income.components.admission-table', [
            'summary' => $summary ?? []
        ])

        <!-- TEACHER TABLE -->
        @include('admin.institute-income.components.teacher-table', [
            'teacher_summaries' => $teacher_summaries ?? []
        ])

        <!-- ORGANIZER TABLE -->
        @include('admin.institute-income.components.organizer-table', [
            'organizer_summaries' => $organizer_summaries ?? []
        ])

        <!-- CLASS TABLE -->
        @include('admin.institute-income.components.class-table', [
            'class_summaries' => $class_summaries ?? []
        ])

        @php
            $netIncome = $summary['net_total'] ?? 0;
        @endphp

        <!-- EXPENSE MODAL - Only shown for current month -->
        @if($isCurrentMonth)
            <div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="expenseModalLabel">
                                Add Institute Expense
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form method="POST" action="{{ route('admin.institute-expenses.store') }}" id="expenseForm">
                            @csrf

                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Current Net Income Before Expense:
                                    <strong>Rs. {{ number_format($netIncome, 2) }}</strong>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Note:</strong> Adding an expense will reduce the net income. 
                                    You can add any amount. If expense exceeds net income, the net will become negative.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Expense Amount <span class="text-danger">*</span></label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="amount"
                                        id="expenseAmount"
                                        class="form-control custom-input @error('amount') is-invalid @enderror"
                                        placeholder="Enter expense amount"
                                        required
                                    >
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Enter the expense amount (positive number only). No upper limit restriction.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                    <input
                                        type="date"
                                        name="payment_date"
                                        class="form-control custom-input @error('payment_date') is-invalid @enderror"
                                        value="{{ now()->format('Y-m-d') }}"
                                        required
                                    >
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Reason / Description</label>
                                    <textarea
                                        name="reason"
                                        rows="3"
                                        class="form-control custom-input @error('reason') is-invalid @enderror"
                                        placeholder="Enter reason for expense (optional)"
                                    ></textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Hidden fields to pass month and year -->
                                <input type="hidden" name="month" value="{{ $month }}">
                                <input type="hidden" name="year" value="{{ $year }}">
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary custom-btn" data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-danger custom-btn" id="expenseSubmitBtn">
                                    <i class="bi bi-save me-1"></i> Save Expense
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

@push('styles')
    <style>
        .institute-income-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .filter-card,
        .main-card,
        .summary-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,.05);
            border: 1px solid #eef2f7;
        }

        .hero-card,
        .filter-card,
        .main-card {
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
            box-shadow: 0 0 0 4px rgba(37,99,235,.10);
        }

        textarea.custom-input {
            min-height: 80px;
        }

        .summary-card {
            color: #fff;
            min-height: 145px;
            overflow: hidden;
            position: relative;
        }

        .summary-card .card-body {
            padding: 1.35rem;
            position: relative;
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

        .summary-blue { background: linear-gradient(135deg,#2563eb,#3b82f6); }
        .summary-green { background: linear-gradient(135deg,#10b981,#34d399); }
        .summary-warning { background: linear-gradient(135deg,#f59e0b,#fbbf24); color: #111827; }
        .summary-red { background: linear-gradient(135deg,#ef4444,#f87171); }
        .summary-info { background: linear-gradient(135deg,#0ea5e9,#38bdf8); }
        .summary-dark { background: linear-gradient(135deg,#0f172a,#334155); }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
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

        .custom-alert {
            border-radius: 18px;
        }

        .table-responsive {
            border-radius: 20px;
            overflow: auto;
        }

        .table-sticky thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #f8fafc !important;
            white-space: nowrap;
        }

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            text-transform: uppercase;
            padding: 1rem;
            vertical-align: middle;
        }

        .custom-table tbody td {
            padding: 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .hero-content,
            .main-card-header {
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

        @media print {
            .btn,
            .alert,
            .sidebar,
            .navbar {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .hero-card,
            .filter-card,
            .main-card,
            .summary-card {
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('expenseForm');
        const amountInput = document.getElementById('expenseAmount');
        
        if (form && amountInput) {
            // Remove any validation that prevents saving
            form.addEventListener('submit', function (e) {
                const amount = parseFloat(amountInput.value || 0);
                
                if (isNaN(amount) || amount <= 0) {
                    e.preventDefault();
                    amountInput.classList.add('is-invalid');
                    
                    let feedback = amountInput.parentNode.querySelector('.custom-error-message');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'text-danger mt-1 custom-error-message';
                        amountInput.parentNode.appendChild(feedback);
                    }
                    feedback.textContent = 'Please enter a valid expense amount greater than 0.';
                    amountInput.focus();
                }
            });
            
            // Clear validation on input
            amountInput.addEventListener('input', function () {
                const amount = parseFloat(amountInput.value || 0);
                let feedback = amountInput.parentNode.querySelector('.custom-error-message');
                
                if (amount > 0) {
                    amountInput.classList.remove('is-invalid');
                    if (feedback) feedback.remove();
                }
            });
        }
    });
</script>
@endpush