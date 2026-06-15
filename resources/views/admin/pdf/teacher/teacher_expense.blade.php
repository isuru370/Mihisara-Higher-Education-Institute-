<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Teacher Expense Report - {{ config('app.name', 'EDU NEXORA') }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            padding: 15px;
            line-height: 1.4;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #2563eb;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .header .period {
            font-size: 9px;
            color: #64748b;
            margin-top: 5px;
        }

        /* Company Info */
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 12px;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: 0.5px;
        }

        /* Info Card */
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-item {
            flex: 1;
            min-width: 120px;
        }

        .info-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Summary Cards */
        .summary-table {
            width: 80%;
            margin: 0 auto 25px auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 10px 12px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            width: 33.33%;
        }

        .summary-table td:first-child {
            border-radius: 8px 0 0 8px;
        }

        .summary-table td:last-child {
            border-radius: 0 8px 8px 0;
        }

        .summary-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: 800;
        }

        .summary-value.total {
            color: #2563eb;
        }

        .summary-value.amount {
            color: #991b1b;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th {
            background: #0f172a;
            color: white;
            padding: 8px 6px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #334155;
        }

        .data-table td {
            padding: 8px 6px;
            font-size: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .data-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount {
            font-weight: 700;
            font-family: monospace;
        }

        .amount-expense {
            color: #991b1b;
        }

        /* Status Badges */
        .badge-paid {
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-cancelled {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        /* Payment Type Badges */
        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
        }

        .type-salary {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-advance {
            background: #fef3c7;
            color: #92400e;
        }

        .type-bonus {
            background: #dcfce7;
            color: #166534;
        }

        .type-other {
            background: #f1f5f9;
            color: #475569;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <div class="header">
        <h1>Teacher Expense Report</h1>
        <div class="period">
            {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
        </div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Teacher Information --}}
    <div class="info-card">
        <div class="info-item">
            <div class="info-label">Teacher Name</div>
            <div class="info-value">{{ $teacher_name ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Report Date</div>
            <div class="info-value">{{ $date ?? now()->format('d M Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Expenses</div>
            <div class="info-value">{{ count($payments) }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Amount</div>
            <div class="info-value" style="color: #991b1b;">Rs.
                {{ number_format(collect($payments)->sum('amount'), 2) }}</div>
        </div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalExpenses = collect($payments)->sum('amount');
        $paidExpenses = collect($payments)->where('status', 'paid')->count();
        $cancelledExpenses = collect($payments)->where('status', 'cancelled')->count();
    @endphp

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">TOTAL EXPENSES</div>
                <div class="summary-value total">{{ count($payments) }}</div>
            </td>
            <td>
                <div class="summary-label">TOTAL AMOUNT</div>
                <div class="summary-value amount">Rs. {{ number_format($totalExpenses, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">PAID / CANCELLED</div>
                <div class="summary-value total">{{ $paidExpenses }} / {{ $cancelledExpenses }}</div>
            </td>
        </tr>
    </table>

    {{-- Expenses Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">Payment ID</th>
                
                <th style="width: 10%;">Type</th>
                <th style="width: 8%;" class="text-right">Amount</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 18%;">Reason</th>
                <th style="width: 18%;">Note</th>
                <th style="width: 15%;">Created By</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td><code>{{ $payment['payment_id'] }}</code></td>
                    
                    <td>
                        @php
                            $type = strtolower($payment['payment_type'] ?? 'other');
                            $typeClass = match ($type) {
                                'salary' => 'type-salary',
                                'advance' => 'type-advance',
                                'bonus' => 'type-bonus',
                                default => 'type-other'
                            };
                        @endphp
                        <span class="type-badge {{ $typeClass }}">{{ ucfirst($payment['payment_type'] ?? 'Other') }}</span>
                    </td>
                    <td class="text-right amount amount-expense">Rs. {{ number_format($payment['amount'] ?? 0, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('d M Y') }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($payment['reason'] ?? '-', 25) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($payment['note'] ?? '-', 25) }}</td>
                    <td><strong>{{ $payment['teacher_name'] ?? '-' }}</strong></td>
                    <td>
                        @if(($payment['status'] ?? '') == 'paid')
                            <span class="badge-paid">✓ Paid</span>
                        @elseif(($payment['status'] ?? '') == 'pending')
                            <span class="badge-pending">⏳ Pending</span>
                        @else
                            <span class="badge-cancelled">✗ Cancelled</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #fef2f2; font-weight: 800;">
                <td colspan="3" class="text-right"><strong>Total</strong></td>
                <td class="text-right amount"><strong>Rs. {{ number_format($totalExpenses, 2) }}</strong></td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>