@extends('layouts.app')

@section('title', 'Organizer Salary Payment')
@section('page-title', 'Organizer Salary Payment')

@section('content')

    <div class="salary-payment-page">

        <!-- HERO -->
        <div class="hero-card mb-4">

            <div class="hero-content">

                <div class="d-flex align-items-center gap-3">

                    <div class="profile-avatar">
                        {{ strtoupper(substr($organizer->name, 0, 1)) }}
                    </div>

                    <div>

                        <h4 class="mb-1 fw-bold">
                            Organizer Salary Payment
                        </h4>

                        <p class="mb-0 text-muted">
                            {{ $organizer->name }}
                            —
                            {{ $organizer->code }}
                            |
                            {{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
                        </p>

                    </div>

                </div>

                <div class="hero-actions">

                    <a href="{{ route('admin.organizer-payments.index', [
        'year' => $year,
        'month' => $month,
    ]) }}" class="btn btn-outline-secondary custom-btn">

                        <i class="bi bi-arrow-left me-1"></i>
                        Back

                    </a>

                    @if($salaryRecord)

                        <button type="button" class="btn btn-dark custom-btn" disabled>

                            <i class="bi bi-printer me-1"></i>
                            Print Receipt

                        </button>

                    @endif

                </div>

            </div>

        </div>

        <!-- SUMMARY -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-blue">

                    <div class="card-body">

                        <i class="bi bi-cash-stack summary-icon"></i>

                        <h6>Total Income</h6>

                        <h3>
                            {{ number_format($totalIncome, 2) }}
                        </h3>

                    </div>

                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-red">

                    <div class="card-body">

                        <i class="bi bi-arrow-down-circle summary-icon"></i>

                        <h6>Advance</h6>

                        <h3>
                            {{ number_format($advanceAmount, 2) }}
                        </h3>

                    </div>

                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-orange">

                    <div class="card-body">

                        <i class="bi bi-dash-circle summary-icon"></i>

                        <h6>Deduction</h6>

                        <h3>
                            {{ number_format($deductionAmount, 2) }}
                        </h3>

                    </div>

                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-gold">

                    <div class="card-body">

                        <i class="bi bi-three-dots summary-icon"></i>

                        <h6>Other</h6>

                        <h3>
                            {{ number_format($otherAmount, 2) }}
                        </h3>

                    </div>

                </div>
            </div>

        </div>

        <!-- SECOND ROW -->
        <div class="row g-4 mb-4">

            <div class="col-lg-6">

                <div class="summary-card summary-purple">

                    <div class="card-body">

                        <i class="bi bi-wallet2 summary-icon"></i>

                        <h6>Salary Paid</h6>

                        <h3>
                            {{ number_format($salaryPaid, 2) }}
                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="summary-card summary-green">

                    <div class="card-body">

                        <i class="bi bi-check2-circle summary-icon"></i>

                        <h6>Net Payable</h6>

                        <h2 class="mb-0 fw-bold">
                            {{ number_format($netTotal, 2) }}
                        </h2>

                    </div>

                </div>

            </div>

        </div>

        <!-- MAIN CARD -->
        <div class="main-card">

            <div class="main-card-header">

                <div>

                    <h4>Salary Payment Status</h4>

                    <p>
                        Review organizer payment details before salary processing
                    </p>

                </div>

                <div class="header-badge">

                    {{ $year }} / {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}

                </div>

            </div>

            <div class="card-body-custom">

                @if ($salaryRecord)

                    <div class="status-alert success-alert">

                        <div class="alert-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>

                        <div>

                            <h5>
                                Salary Already Paid
                            </h5>

                            <p class="mb-0">
                                Salary payment record already exists for this organizer and selected month.
                            </p>

                        </div>

                    </div>

                @elseif ($netTotal <= 0)

                    <div class="status-alert warning-alert">

                        <div class="alert-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>

                        <div>

                            <h5>
                                No Payable Balance
                            </h5>

                            <p class="mb-0">
                                There is no payable salary balance available for this organizer.
                            </p>

                        </div>

                    </div>

                @else

                    <div class="payment-box">

                        <div class="payment-box-top">

                            <div>

                                <h5 class="fw-bold mb-1">
                                    Ready for Salary Payment
                                </h5>

                                <p class="text-muted mb-0">
                                    Click the button below to process organizer salary payment.
                                </p>

                            </div>

                            <div class="payable-amount">

                                <span>
                                    Net Payable
                                </span>

                                <h2>
                                    Rs. {{ number_format($netTotal, 2) }}
                                </h2>

                            </div>

                        </div>

                        <hr>

                        <form method="POST" action="{{ route('admin.organizer-payments.store', $organizer->id) }}">

                            @csrf

                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="payment_type" value="salary">

                            <button type="submit" class="btn btn-success payment-btn">

                                <i class="bi bi-cash-coin me-2"></i>

                                Pay Organizer Salary

                            </button>

                        </form>

                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection

@push('styles')

    <style>
        .salary-payment-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .main-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
        }

        .hero-card {
            padding: 1.4rem 1.5rem;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
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

        .summary-card {
            border-radius: 24px;
            overflow: hidden;
            color: #fff;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
            border: none;
            min-height: 145px;
        }

        .summary-card .card-body {
            padding: 1.4rem;
            position: relative;
        }

        .summary-card h6 {
            opacity: .92;
            margin-bottom: .6rem;
        }

        .summary-card h3,
        .summary-card h2 {
            margin: 0;
            font-weight: 800;
        }

        .summary-icon {
            position: absolute;
            right: 18px;
            top: 18px;
            font-size: 2rem;
            opacity: .18;
        }

        .summary-blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .summary-red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .summary-orange {
            background: linear-gradient(135deg, #ea580c, #f97316);
        }

        .summary-gold {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .summary-purple {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        }

        .summary-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .main-card {
            padding: 1.5rem;
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

        .card-body-custom {
            padding-top: .5rem;
        }

        .status-alert {
            border-radius: 22px;
            padding: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .alert-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .success-alert {
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
        }

        .success-alert .alert-icon {
            background: #10b981;
            color: #fff;
        }

        .warning-alert {
            background: #fffbeb;
            border: 1px solid #fde68a;
        }

        .warning-alert .alert-icon {
            background: #f59e0b;
            color: #fff;
        }

        .payment-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 1.5rem;
        }

        .payment-box-top {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .payable-amount {
            text-align: right;
        }

        .payable-amount span {
            color: #64748b;
            font-size: .9rem;
        }

        .payable-amount h2 {
            margin: 0;
            color: #16a34a;
            font-weight: 800;
        }

        .payment-btn {
            border-radius: 16px;
            padding: .9rem 1.4rem;
            font-weight: 700;
            min-width: 240px;
            transition: .2s ease;
        }

        .payment-btn:hover {
            transform: translateY(-2px);
        }

        @media(max-width:768px) {

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

            .payment-box-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .payable-amount {
                text-align: left;
            }

            .payment-btn {
                width: 100%;
            }
        }
    </style>

@endpush