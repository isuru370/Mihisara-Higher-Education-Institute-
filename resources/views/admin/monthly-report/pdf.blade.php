@php
    $report = $summary['summary'] ?? $summary ?? [];
    $generatedAt = now()->format('Y-m-d H:i:s');
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title ?? 'Monthly Report' }} - {{ config('app.name', 'EDU NEXORA') }}</title>

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

        /* Summary Table - Horizontal Table (6 Columns) */
        .summary-table {
            width: 100%;
            margin: 0 auto 20px auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 10px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            width: 16.66%;
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
            font-size: 12px;
            font-weight: 800;
        }

        .summary-value.income {
            color: #166534;
        }

        .summary-value.expense {
            color: #991b1b;
        }

        .summary-value.net {
            color: #1e40af;
        }

        /* Net Box */
        .net-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .net-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 5px;
        }

        .net-value {
            font-size: 16px;
            font-weight: 800;
            color: #1e40af;
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
            padding: 6px;
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
        <h1>{{ $title ?? 'Monthly Financial Report' }}</h1>
        <div class="period">
            @if(isset($filters['report_type']) && $filters['report_type'] === 'range')
                Period: {{ \Carbon\Carbon::parse($filters['from_date'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['to_date'])->format('d M Y') }}
            @else
                Month: {{ $summary['month'] ?? \Carbon\Carbon::now()->format('F Y') }}
            @endif
        </div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Table - Horizontal Table (6 Columns) --}}
    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">PAYMENT TOTAL</div>
                <div class="summary-value income">Rs. {{ number_format($report['payment_total'] ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">ADMISSION TOTAL</div>
                <div class="summary-value income">Rs. {{ number_format($report['admission_total'] ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">EXTRA INCOME</div>
                <div class="summary-value income">Rs. {{ number_format($report['extra_income_total'] ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">TEACHER EXPENSE</div>
                <div class="summary-value expense">Rs. {{ number_format($report['teacher_expense_total'] ?? 0, 2) }}
                </div>
            </td>
            <td>
                <div class="summary-label">ORGANIZER EXPENSE</div>
                <div class="summary-value expense">Rs. {{ number_format($report['organizer_expense_total'] ?? 0, 2) }}
                </div>
            </td>
            <td>
                <div class="summary-label">INSTITUTE EXPENSE</div>
                <div class="summary-value expense">Rs.
                    {{ number_format($report['instituteExpencesTotal'] ?? $report['institute_expense_total'] ?? 0, 2) }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Net Box --}}
    <div class="net-box">
        <div class="net-label">NET TOTAL</div>
        <div class="net-value">Rs. {{ number_format($report['net_total'] ?? 0, 2) }}</div>
    </div>

    {{-- Daily Breakdown Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 11%;" class="text-right">Payment</th>
                <th style="width: 11%;" class="text-right">Admission</th>
                <th style="width: 11%;" class="text-right">Extra Income</th>
                <th style="width: 11%;" class="text-right">Teacher Expense</th>
                <th style="width: 11%;" class="text-right">Organizer Expense</th>
                <th style="width: 11%;" class="text-right">Institute Expense</th>
                <th style="width: 11%;" class="text-right">Net Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dailyRows ?? [] as $row)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                    <td class="text-right amount">Rs. {{ number_format($row['payment_total'] ?? 0, 2) }}</td>
                    <td class="text-right amount">Rs. {{ number_format($row['admission_total'] ?? 0, 2) }}</td>
                    <td class="text-right amount">Rs. {{ number_format($row['extra_income_total'] ?? 0, 2) }}</td>
                    <td class="text-right amount">Rs. {{ number_format($row['teacher_expense_total'] ?? 0, 2) }}</td>
                    <td class="text-right amount">Rs. {{ number_format($row['organizer_expense_total'] ?? 0, 2) }}</td>
                    <td class="text-right amount">Rs. {{ number_format($row['institute_expense_total'] ?? 0, 2) }}</td>
                    <td
                        class="text-right amount @if(($row['net_total'] ?? 0) >= 0) amount-income @else amount-expense @endif">
                        Rs. {{ number_format($row['net_total'] ?? 0, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No daily records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>