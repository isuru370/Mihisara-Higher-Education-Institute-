<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Teacher Salary Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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

        /* Summary Cards - Horizontal Table */
        .summary-table {
            width: 70%;
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

        .summary-value.income {
            color: #166534;
        }

        .summary-value.deduction {
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
            padding: 8px 8px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #334155;
        }

        .data-table td {
            padding: 8px 8px;
            font-size: 9px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
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

        .amount-income {
            color: #166534;
        }

        .amount-deduction {
            color: #991b1b;
        }

        /* Badges */
        .badge-paid {
            background: #dcfce7;
            color: #166534;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-unpaid {
            background: #fee2e2;
            color: #991b1b;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
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
        <h1>Teacher Salary Report</h1>
        <div class="period">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalGrossIncome = collect($report)->sum('gross_income');
        $totalAdvanceDeduction = collect($report)->sum('advance_deduction');
        $totalNetSalary = $totalGrossIncome - $totalAdvanceDeduction;
        $paidCount = collect($report)->where('salary_paid_status', 'paid')->count();
        $pendingCount = collect($report)->where('salary_paid_status', 'pending')->count();
        $unpaidCount = collect($report)->where('salary_paid_status', 'unpaid')->count();
    @endphp

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">TOTAL TEACHERS</div>
                <div class="summary-value total">{{ count($report) }}</div>
            </td>
            <td>
                <div class="summary-label">GROSS INCOME</div>
                <div class="summary-value income">Rs. {{ number_format($totalGrossIncome, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">NET SALARY</div>
                <div class="summary-value income">Rs. {{ number_format($totalNetSalary, 2) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="summary-label">PAID</div>
                <div class="summary-value income">{{ $paidCount }}</div>
            </td>
            <td>
                <div class="summary-label">PENDING</div>
                <div class="summary-value deduction">{{ $pendingCount }}</div>
            </td>
            <td>
                <div class="summary-label">UNPAID</div>
                <div class="summary-value deduction">{{ $unpaidCount }}</div>
            </td>
        </tr>
    </table>

    {{-- Teacher Salary Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Custom ID</th>
                <th style="width: 25%;">Teacher Name</th>
                <th style="width: 15%;" class="text-right">Gross Income</th>
                <th style="width: 15%;" class="text-right">Advance Deduction</th>
                <th style="width: 10%;" class="text-right">Net Salary</th>
                <th style="width: 15%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $row['custom_id'] }}</strong></td>
                    <td>{{ $row['initials'] }} / {{ $row['full_name'] ?? '' }}</td>
                    <td class="text-right amount amount-income">Rs. {{ number_format($row['gross_income'], 2) }}</td>
                    <td class="text-right amount amount-deduction">Rs. {{ number_format($row['advance_deduction'], 2) }}
                    </td>
                    <td class="text-right amount amount-income">Rs.
                        {{ number_format($row['net_salary'] ?? ($row['gross_income'] - $row['advance_deduction']), 2) }}
                    </td>
                    <td class="text-center">
                        @if($row['salary_paid_status'] == 'paid')
                            <span class="badge-paid">✓ Paid</span>
                        @elseif($row['salary_paid_status'] == 'pending')
                            <span class="badge-pending">⏳ Pending</span>
                        @else
                            <span class="badge-unpaid">✗ Unpaid</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer Note --}}
    @if(count($report) > 0)
        <div class="footer">
            <div>Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}</div>
            <div class="small" style="margin-top: 4px;">* Net Salary = Gross Income - Advance Deduction</div>
        </div>
    @else
        <div class="footer">
            Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
        </div>
    @endif

</body>

</html>