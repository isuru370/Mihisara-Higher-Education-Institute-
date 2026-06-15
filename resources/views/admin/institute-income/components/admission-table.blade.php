@php
    $admission_payment_list = $admission_payment_list ?? [];
@endphp

<div class="main-card mb-4">
    <div class="main-card-header">
        <div>
            <h4>Admission Payments</h4>
            <p>Monthly admission income details</p>
        </div>
        <span class="header-badge">
            {{ count($admission_payment_list) }} Records
        </span>
    </div>

    @if(count($admission_payment_list) > 0)
        <div class="table-responsive">
            <table class="table table-hover custom-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt No</th>
                        <th>Student</th>
                        <th>Admission</th>
                        <th>Amount</th>
                        <th>Paid Date</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admission_payment_list as $index => $payment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $payment['receipt_number'] ?? '-' }}</td>
                            <td>{{ $payment['student_name'] ?? '-' }}</td>
                            <td>{{ $payment['admission_name'] ?? '-' }}</td>
                            <td>Rs. {{ number_format($payment['amount'] ?? 0, 2) }}</td>
                            <td>{{ $payment['paid_at'] ?? '-' }}</td>
                            <td>{{ ucfirst($payment['payment_method'] ?? '-') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-receipt"></i>
            <h5 class="mb-2">No admission payments found</h5>
            <p class="text-muted mb-0">There are no admission payments for this month.</p>
        </div>
    @endif
</div>