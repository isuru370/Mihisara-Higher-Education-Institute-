@extends('layouts.app')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">
                    Student Payment List
                </h4>

                <small class="text-muted">
                    Teacher: {{ $teacher->full_name }}
                    |
                    Class: {{ $studentClass->class_name }}
                    |
                    Grade: {{ $studentClass->grade?->grade_name }}
                    |
                    Category: {{ $categoryFee->category?->category_name }}
                    |
                    {{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
                </small>
            </div>

            <a href="{{ route('admin.teacher-salaries.show', [
                'teacher' => $teacher->id,
                'year' => $year,
                'month' => $month,
            ]) }}" class="btn btn-secondary">
                Back
            </a>
        </div>

        @php
            $totalFinalFee = $enrollments->sum('final_fee');
            $totalPaid = $enrollments->sum(function ($enrollment) {
                return $enrollment->payments->sum('amount');
            });
            $totalPayments = $enrollments->sum(function ($enrollment) {
                return $enrollment->payments->count();
            });
        @endphp

        <div class="row mb-4">

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <small>Total Students</small>
                        <h4>{{ $enrollments->total() }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <small>Total Payments</small>
                        <h4>{{ $totalPayments }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <small>Paid Total</small>
                        <h4>{{ number_format($totalPaid, 2) }}</h4>
                    </div>
                </div>
            </div>

        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Payment Type</th>
                            <th class="text-end">Final Fee</th>
                            <th class="text-end">Payment Count</th>
                            <th class="text-end">Paid Total</th>
                            <th>Payments</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($enrollments as $enrollment)

                            @php
                                $paymentType = 'Default Payment';

                                if ($enrollment->is_free_card) {
                                    $paymentType = 'Free Card';
                                } elseif (!is_null($enrollment->custom_fee)) {
                                    $paymentType = 'Custom Payment';
                                }

                                $student = $enrollment->student;
                                $payments = $enrollment->payments;
                            @endphp

                            <tr>
                                <td>
                                    {{ $student?->custom_id ?? $student?->id }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $student?->initial_name ?? $student?->full_name ?? '-' }}
                                    </strong>
                                </td>

                                <td>
                                    @if ($paymentType === 'Free Card')
                                        <span class="badge bg-success">Free Card</span>
                                    @elseif ($paymentType === 'Custom Payment')
                                        <span class="badge bg-warning text-dark">Custom Payment</span>
                                    @else
                                        <span class="badge bg-primary">Default Payment</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    {{ number_format($enrollment->final_fee, 2) }}
                                </td>

                                <td class="text-end">
                                    {{ $payments->count() }}
                                </td>

                                <td class="text-end fw-bold text-success">
                                    {{ number_format($payments->sum('amount'), 2) }}
                                </td>

                                <td>
                                    @forelse ($payments as $payment)

                                        <div class="border rounded p-2 mb-1 bg-light">
                                            <strong>
                                                {{ $payment->receipt_number }}
                                            </strong>

                                            <br>

                                            Amount:
                                            <strong>{{ number_format($payment->amount, 2) }}</strong>

                                            <br>

                                            Paid Month:
                                            {{ \Carbon\Carbon::parse($payment->payment_month)->format('F Y') }}

                                            <br>

                                            Paid At:
                                            {{ $payment->paid_at?->format('Y-m-d H:i:s') }}

                                            <br>

                                            Method:
                                            {{ ucfirst($payment->payment_method) }}
                                        </div>

                                    @empty

                                        <span class="text-muted">
                                            No payment for selected month
                                        </span>

                                    @endforelse
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No enrolled students found
                                </td>
                            </tr>

                        @endforelse
                    </tbody>

                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">
                                Page Total
                            </th>

                            <th class="text-end">
                                {{ number_format($totalFinalFee, 2) }}
                            </th>

                            <th class="text-end">
                                {{ $totalPayments }}
                            </th>

                            <th class="text-end">
                                {{ number_format($totalPaid, 2) }}
                            </th>

                            <th></th>
                        </tr>
                    </tfoot>
                </table>

                {{ $enrollments->links() }}

            </div>
        </div>

    </div>

@endsection