@extends('layouts.app')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h4 class="mb-0">Teacher Salary Payment</h4>

                <small class="text-muted">
                    {{ $teacher->full_name }}
                    —
                    {{ $teacher->custom_id }}
                    |
                    {{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
                </small>
            </div>

            <a href="{{ route('admin.teacher-salaries.index', [
        'year' => $year,
        'month' => $month,
    ]) }}" class="btn btn-secondary">
                Back
            </a>

        </div>

        @php
            $hasPaidSalary = $salary && $salary->status === 'paid';
        @endphp

        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <small>Gross Amount</small>
                        <h4>{{ number_format($grossAmount, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <small>Advance Deduction</small>
                        <h4 class="text-danger">{{ number_format($advanceDeduction, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <small>Other Deduction</small>
                        <h4 class="text-danger">{{ number_format($otherDeduction ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <small>Salary Paid</small>
                        <h4 class="text-primary">{{ number_format($salaryPaid ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>

        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 bg-success-subtle">
                    <div class="card-body">
                        <small>Net Payable</small>
                        <h3 class="mb-0">{{ number_format($netAmount, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        @if ($hasPaidSalary)
            <div class="alert alert-success">
                This teacher salary has already been paid.
            </div>
        @else
            <div class="alert alert-info">
                <strong>Calculation:</strong>
                Gross Amount
                - Advance Deduction
                - Other Deduction
                - Salary Paid
                =
                <strong>{{ number_format($netAmount, 2) }}</strong>
            </div>
        @endif

        <div class="card shadow-sm border-0">

            <div class="card-header">
                <h5 class="mb-0">
                    {{ $hasPaidSalary ? 'Salary Already Paid' : 'Pay Salary' }}
                </h5>
            </div>

            <div class="card-body">

                @if ($hasPaidSalary)

                    <div class="alert alert-success mb-0">
                        Salary payment record already exists for this teacher and selected month.
                    </div>

                @elseif ($netAmount <= 0)

                    <div class="alert alert-warning mb-0">
                        No payable salary balance for this teacher.
                    </div>

                @else

                    <form method="POST" action="{{ route('admin.teacher-salary-payments.store', $teacher->id) }}">
                        @csrf

                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">

                        <div class="mb-3">
                            <label class="form-label">Gross Amount</label>
                            <input type="text" class="form-control" value="{{ number_format($grossAmount, 2) }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Advance Deduction</label>
                            <input type="text" class="form-control" value="{{ number_format($advanceDeduction, 2) }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Other Deduction</label>
                            <input type="text" class="form-control" value="{{ number_format($otherDeduction ?? 0, 2) }}"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Salary Paid</label>
                            <input type="text" class="form-control" value="{{ number_format($salaryPaid ?? 0, 2) }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Net Payable</label>
                            <input type="text" class="form-control fw-bold text-success"
                                value="{{ number_format($netAmount, 2) }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-cash"></i>
                            Pay Salary
                        </button>
                    </form>

                @endif

            </div>

        </div>

        @if ($salary)

            <div class="card shadow-sm border-0 mt-4">

                <div class="card-header">
                    <h5 class="mb-0">Latest Salary Payment Record</h5>
                </div>

                <div class="card-body table-responsive">

                    <table class="table table-bordered align-middle mb-0">
                        <tr>
                            <th style="width: 220px">Gross Amount</th>
                            <td>{{ number_format($salary->gross_amount, 2) }}</td>
                        </tr>

                        <tr>
                            <th>Advance Deduction</th>
                            <td>{{ number_format($salary->advance_deduction, 2) }}</td>
                        </tr>

                        <tr>
                            <th>Other Deduction</th>
                            <td>{{ number_format($salary->other_deduction, 2) }}</td>
                        </tr>

                        <tr>
                            <th>Net Amount</th>
                            <td class="fw-bold text-success">{{ number_format($salary->net_amount, 2) }}</td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-success">
                                    {{ ucfirst($salary->status) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Paid At</th>
                            <td>{{ $salary->paid_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Note</th>
                            <td>{{ $salary->note ?? '-' }}</td>
                        </tr>
                    </table>

                </div>

            </div>

        @endif

    </div>

@endsection