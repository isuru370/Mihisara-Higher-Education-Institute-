@extends('layouts.app')

@section('title', 'Monthly Payment Report')
@section('page-title', 'Monthly Payment Report')

@push('styles')
    <style>
        .monthly-report-page {
            animation: fadeIn .4s ease;
        }

        /* Hero Card */
        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 32px;
            padding: 2rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8, #1e40af);
            box-shadow: 0 20px 45px rgba(37, 99, 235, .25);
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
            top: -100px;
            right: -80px;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
            bottom: -60px;
            left: -40px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .7rem 1rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .08);
            backdrop-filter: blur(10px);
            font-size: .85rem;
            font-weight: 600;
        }

        .text-light-soft {
            color: rgba(255, 255, 255, .78);
        }

        /* Report Cards */
        .report-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
            transition: all .25s ease;
            margin-bottom: 1.5rem;
        }

        .report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px rgba(15, 23, 42, .1);
        }

        .report-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
        }

        .report-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .report-title i {
            color: #2563eb;
            font-size: 1.2rem;
        }

        .report-subtitle {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .report-body {
            padding: 1.5rem;
        }

        /* Form Elements */
        .form-label-custom {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control-custom,
        .form-select-custom {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
        }

        .btn-excel {
            background: #059669;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-excel:hover {
            background: #047857;
            transform: translateY(-2px);
            color: white;
        }

        .btn-pdf {
            background: #dc2626;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-pdf:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            color: white;
        }

        .btn-print {
            background: #0ea5e9;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-print:hover {
            background: #0284c7;
            transform: translateY(-2px);
            color: white;
        }

        .divider {
            border-top: 1px solid #eef2f7;
            margin: 1.25rem 0;
        }

        /* Info Box */
        .info-box {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-box i {
            font-size: 1.2rem;
            color: #2563eb;
        }

        .info-box-text {
            font-size: 0.8rem;
            color: #475569;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-card {
                padding: 1.5rem;
            }

            .report-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-excel,
            .btn-pdf,
            .btn-print {
                width: 100%;
                justify-content: center;
            }

            .report-body .d-flex {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <div class="monthly-report-page">

        {{-- HERO CARD --}}
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="hero-badge mb-3">
                        <i class="bi bi-graph-up"></i>
                        Financial Reports
                    </div>
                    <h2 class="fw-bold mb-2">Monthly Payment Reports</h2>
                    <p class="mb-0 text-light-soft">
                        Generate and download teacher salary reports, salary slips, and student payment reports.
                    </p>
                </div>
                <div class="text-end">
                    <div class="hero-badge mb-2">
                        <i class="bi bi-calendar-event-fill"></i>
                        {{ now()->format('d M Y') }}
                    </div>
                    <div class="hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================= --}}
        {{-- TEACHER SALARY REPORT SECTION --}}
        {{-- ========================================= --}}
        <div class="report-card">
            <div class="report-header">
                <div class="report-title">
                    <i class="bi bi-cash-stack"></i>
                    Teacher Salary Report
                </div>
                <div class="report-subtitle">Filter and download monthly teacher salary reports</div>
            </div>
            <div class="report-body">
                <form method="GET" action="{{ route('admin.monthly-report.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label-custom">Year</label>
                            <select name="year" class="form-select-custom">
                                @for ($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Month</label>
                            <select name="month" class="form-select-custom">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn-primary-custom w-100">
                                <i class="bi bi-funnel-fill me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <div class="divider"></div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.teacher.salary.report.excel', [
        'year' => request('year', now()->year),
        'month' => request('month', now()->month),
    ]) }}" class="btn-excel">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Download Excel
                    </a>
                    <a href="{{ route('admin.teacher.salary.report.pdf', [
        'year' => request('year', now()->year),
        'month' => request('month', now()->month),
    ]) }}" class="btn-pdf">
                        <i class="bi bi-filetype-pdf"></i> Download PDF
                    </a>
                </div>

                <div class="info-box">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="info-box-text">
                        This report includes all teacher salary details for the selected month and year.
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================= --}}
        {{-- TEACHER SALARY SLIP SECTION --}}
        {{-- ========================================= --}}
        <div class="report-card">
            <div class="report-header">
                <div class="report-title">
                    <i class="bi bi-receipt"></i>
                    Teacher Salary Slip
                </div>
                <div class="report-subtitle">Generate and print individual teacher salary slips</div>
            </div>
            <div class="report-body">
                <form method="GET" action="{{ route('admin.monthly-report.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label-custom">Year</label>
                            <select name="year" class="form-select-custom">
                                @for ($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Month</label>
                            <select name="month" class="form-select-custom">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Select Teacher</label>
                            <select name="teacher_id" class="form-select-custom">
                                <option value="">-- Select Teacher --</option>
                                @foreach ($teachers as $teacherItem)
                                    <option value="{{ $teacherItem->id }}" {{ request('teacher_id') == $teacherItem->id ? 'selected' : '' }}>
                                        {{ $teacherItem->custom_id }} - {{ $teacherItem->initials }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn-primary-custom w-100">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <div class="divider"></div>

                <div class="d-flex gap-2 flex-wrap">
                    @php
                        $teacherId = request('teacher_id');
                        $year = request('year', now()->year);
                        $month = request('month', now()->month);
                    @endphp

                    @if($teacherId)
                                    <a href="{{ route('admin.teacher-salaries.slip', [
                            'teacher' => $teacherId,
                            'year' => $year,
                            'month' => $month,
                        ]) }}?autoPrint=true" target="_blank" class="btn-print">
                                        <i class="bi bi-printer"></i> Print Salary Slip
                                    </a>
                    @else
                        <div class="info-box" style="background: #fef3c7;">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #f59e0b;"></i>
                            <div class="info-box-text">
                                Please select a teacher to generate the salary slip.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ========================================= --}}
        {{-- TEACHER WITH STUDENT PAYMENT MONTH REPORT SECTION --}}
        {{-- ========================================= --}}
        <div class="report-card">
            <div class="report-header">
                <div class="report-title">
                    <i class="bi bi-people-fill"></i>
                    Teacher Student Payment Month Report
                </div>
                <div class="report-subtitle">Shows students who paid for the selected fee month.</div>
            </div>
            <div class="report-body">
                <form method="GET" action="{{ route('admin.monthly-report.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label-custom">Year</label>
                            <select name="year" class="form-select-custom">
                                @for ($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Month</label>
                            <select name="month" class="form-select-custom">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Select Teacher</label>
                            <select name="teacher_id" class="form-select-custom">
                                <option value="">-- Select Teacher --</option>
                                @foreach ($teachers as $teacherItem)
                                    <option value="{{ $teacherItem->id }}" {{ request('teacher_id') == $teacherItem->id ? 'selected' : '' }}>
                                        {{ $teacherItem->custom_id }} - {{ $teacherItem->initials }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn-primary-custom w-100">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <div class="divider"></div>

                <div class="d-flex gap-2 flex-wrap">
                    @php
                        $teacherId = request('teacher_id');
                        $year = request('year', now()->year);
                        $month = request('month', now()->month);
                    @endphp

                    @if($teacherId)
                        <a href="{{ url('/admin/reports/teacher-student-payment-excel?teacher_id=' . $teacherId . '&year=' . $year . '&month=' . $month) }}"
                            class="btn-excel">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Download Excel
                        </a>
                        <a href="{{ url('/admin/reports/teacher-student-payment-pdf?teacher_id=' . $teacherId . '&year=' . $year . '&month=' . $month) }}"
                            target="_blank" class="btn-pdf">
                            <i class="bi bi-filetype-pdf"></i> Download PDF
                        </a>
                    @else
                        <div class="info-box" style="background: #fef3c7;">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #f59e0b;"></i>
                            <div class="info-box-text">
                                Please select a teacher to generate the student payment report.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="info-box mt-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="info-box-text">
                        <strong>Payment Month Report:</strong>
                        This report shows students who have paid for the selected payment month.
                        The payment can be made on any date, but students will be included if their
                        payment month matches the selected year and month.
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================= --}}
        {{-- TEACHER WITH STUDENT PAYMENT DATE REPORT SECTION --}}
        {{-- ========================================= --}}
        <div class="report-card">
            <div class="report-header">
                <div class="report-title">
                    <i class="bi bi-people-fill"></i>
                    Teacher Student Collection Report
                </div>
                <div class="report-subtitle">Shows students who made payments during the selected month.</div>
            </div>
            <div class="report-body">
                <form method="GET" action="{{ route('admin.monthly-report.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label-custom">Year</label>
                            <select name="year" class="form-select-custom">
                                @for ($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Month</label>
                            <select name="month" class="form-select-custom">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Select Teacher</label>
                            <select name="teacher_id" class="form-select-custom">
                                <option value="">-- Select Teacher --</option>
                                @foreach ($teachers as $teacherItem)
                                    <option value="{{ $teacherItem->id }}" {{ request('teacher_id') == $teacherItem->id ? 'selected' : '' }}>
                                        {{ $teacherItem->custom_id }} - {{ $teacherItem->initials }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn-primary-custom w-100">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <div class="divider"></div>

                <div class="d-flex gap-2 flex-wrap">
                    @php
                        $teacherId = request('teacher_id');
                        $year = request('year', now()->year);
                        $month = request('month', now()->month);
                    @endphp

                    @if($teacherId)
                        <a href="{{ url('/admin/reports/teacher-student-payment-date-excel?teacher_id=' . $teacherId . '&year=' . $year . '&month=' . $month) }}"
                            class="btn-excel">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Download Excel
                        </a>
                        <a href="{{ url('/admin/reports/teacher-student-payment-date-pdf?teacher_id=' . $teacherId . '&year=' . $year . '&month=' . $month) }}"
                            target="_blank" class="btn-pdf">
                            <i class="bi bi-filetype-pdf"></i> Download PDF
                        </a>
                    @else
                        <div class="info-box" style="background: #fef3c7;">
                            <i class="bi bi-exclamation-triangle-fill" style="color: #f59e0b;"></i>
                            <div class="info-box-text">
                                Please select a teacher to generate the student payment report.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="info-box mt-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="info-box-text">
                        <strong>Payment Date Report:</strong>
                        This report shows students who made payments during the selected year and month,
                        regardless of which payment month the payment was assigned to.
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================= --}}
        {{-- TEACHER EXPENSE REPORT SECTION --}}
        {{-- ========================================= --}}
        <div class="report-card">
            <div class="report-header">
                <div class="report-title">
                    <i class="bi bi-wallet2"></i>
                    Teacher Expense Report
                </div>
                <div class="report-subtitle">
                    Filter teacher, year and month to download expense reports
                </div>
            </div>

            <div class="report-body">
                <form method="GET" action="{{ route('admin.monthly-report.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label-custom">Year</label>
                            <select name="year" class="form-select-custom">
                                @for ($y = now()->year; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label-custom">Month</label>
                            <select name="month" class="form-select-custom">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-custom">Select Teacher</label>
                            <select name="teacher_id" class="form-select-custom">
                                <option value="">-- Select Teacher --</option>
                                @foreach ($teachers as $teacherItem)
                                    <option value="{{ $teacherItem->id }}" {{ request('teacher_id') == $teacherItem->id ? 'selected' : '' }}>
                                        {{ $teacherItem->custom_id }} - {{ $teacherItem->initials }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn-primary-custom w-100">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                <div class="divider"></div>

                @php
                    $teacherId = request('teacher_id');
                    $year = request('year', now()->year);
                    $month = request('month', now()->month);
                @endphp

                @if($teacherId)
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('admin.teacher-expense-report.excel', [
                        'teacher_id' => $teacherId,
                        'year' => $year,
                        'month' => $month,
                    ]) }}" class="btn-excel">
                                    <i class="bi bi-file-earmark-spreadsheet"></i>
                                    Download Excel
                                </a>

                                <a href="{{ route('admin.teacher-expense-report.pdf', [
                        'teacher_id' => $teacherId,
                        'year' => $year,
                        'month' => $month,
                    ]) }}" target="_blank" class="btn-pdf">
                                    <i class="bi bi-filetype-pdf"></i>
                                    Download PDF
                                </a>
                            </div>
                @else
                    <div class="info-box" style="background: #fef3c7;">
                        <i class="bi bi-exclamation-triangle-fill" style="color: #f59e0b;"></i>
                        <div class="info-box-text">
                            Please select a teacher to generate the expense report.
                        </div>
                    </div>
                @endif

                <div class="info-box mt-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="info-box-text">
                        This report shows all teacher expenses for the selected
                        month and year.
                    </div>
                </div>
            </div>
        </div>

        {{-- FOOTER NOTE --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="bi bi-printer"></i> Reports generated on {{ now()->format('d M Y, h:i A') }}
            </small>
        </div>

    </div>
@endsection