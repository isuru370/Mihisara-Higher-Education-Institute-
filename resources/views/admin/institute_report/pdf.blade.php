<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Institute Payment Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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

        .header .date-range {
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

        /* Summary Table - Horizontal Table */
        .summary-table {
            width: 100%;
            margin: 0 auto 25px auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 10px 12px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            width: 25%;
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

        .summary-value.income {
            color: #166534;
        }

        .summary-value.expense {
            color: #991b1b;
        }

        .summary-value.net {
            color: #1e40af;
        }

        .summary-value.records {
            color: #8b5cf6;
        }

        /* Section Title */
        .section-title {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #2563eb;
            display: inline-block;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
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

        /* Badges */
        .badge-income {
            background: #dcfce7;
            color: #166534;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-expense {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 10px;
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
        <h1>Institute Payment Report</h1>
        <div class="date-range">
            @if(!empty($filters['start_date']) && !empty($filters['end_date']))
                Period: {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
            @elseif(!empty($filters['date']))
                Date: {{ \Carbon\Carbon::parse($filters['date'])->format('d M Y') }}
            @elseif(!empty($filters['month']))
                Month: {{ \Carbon\Carbon::parse($filters['month'])->format('F Y') }}
            @else
                All Records
            @endif
        </div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Table - Horizontal Table (4 Columns) --}}
    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">CLASS INCOME</div>
                <div class="summary-value income">Rs. {{ number_format($summary['snapshot_income_total'] ?? 0, 2) }}
                </div>
            </td>
            <td>
                <div class="summary-label">EXTRA INCOME</div>
                <div class="summary-value income">Rs. {{ number_format($summary['extra_income_total'] ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">TOTAL INCOME</div>
                <div class="summary-value income">Rs. {{ number_format($summary['total_income'] ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">TOTAL EXPENSE</div>
                <div class="summary-value expense">Rs. {{ number_format($summary['total_expense'] ?? 0, 2) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="summary-label">NET TOTAL</div>
                <div class="summary-value net">Rs. {{ number_format($summary['net_total'] ?? 0, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">TOTAL RECORDS</div>
                <div class="summary-value records">{{ $summary['total_records'] ?? 0 }}</div>
            </td>
            <td colspan="2"></td>
        </tr>
    </table>

    {{-- 1. Payment Split Snapshots --}}
    <div class="section-title">1. Payment Split Snapshots</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 18%;">Class</th>
                <th style="width: 14%;">Teacher</th>
                <th style="width: 14%;">Organizer</th>
                <th style="width: 12%;" class="text-right">Payment Amount</th>
                <th style="width: 12%;" class="text-right">Institute Amount</th>
                <th style="width: 20%;">Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($snapshotPayments as $row)
                <tr>
                    <td class="text-center">
                        {{ $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('Y-m-d') : '-' }}</td>
                    <td>{{ $row->studentClass->name ?? $row->studentClass->class_name ?? '-' }}</td>
                    <td>{{ $row->teacher->name ?? $row->teacher->full_name ?? '-' }}</td>
                    <td>{{ $row->organizer->name ?? '-' }}</td>
                    <td class="text-right amount">Rs. {{ number_format($row->payment_amount ?? 0, 2) }}</td>
                    <td class="text-right amount">Rs. {{ number_format($row->institution_amount ?? 0, 2) }}</td>
                    <td>{{ $row->createdBy->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 2. Extra Incomes --}}
    <div class="section-title">2. Extra Incomes</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 28%;">Reason</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 16%;" class="text-right">Amount</th>
                <th style="width: 20%;">Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($extraIncomes as $income)
                <tr>
                    <td class="text-center">
                        {{ $income->income_date ? \Carbon\Carbon::parse($income->income_date)->format('Y-m-d') : '-' }}</td>
                    <td>{{ $income->reason ?? '-' }}</td>
                    <td class="text-center"><span class="badge-income">{{ ucfirst($income->income_type ?? '-') }}</span>
                    </td>
                    <td class="text-center">{{ ucfirst($income->status ?? '-') }}</td>
                    <td class="text-right amount">Rs. {{ number_format($income->amount ?? 0, 2) }}</td>
                    <td>{{ $income->createdBy->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 3. Expenses --}}
    <div class="section-title">3. Expenses</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 30%;">Reason</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 16%;" class="text-right">Amount</th>
                <th style="width: 18%;">Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                <tr>
                    <td class="text-center">
                        {{ $expense->payment_date ? \Carbon\Carbon::parse($expense->payment_date)->format('Y-m-d') : '-' }}
                    </td>
                    <td>{{ $expense->reason ?? '-' }}</td>
                    <td class="text-center">
                        <span class="{{ $expense->payment_type === 'income' ? 'badge-income' : 'badge-expense' }}">
                            {{ ucfirst($expense->payment_type ?? '-') }}
                        </span>
                    </td>
                    <td class="text-center">{{ ucfirst($expense->status ?? '-') }}</td>
                    <td class="text-right amount">Rs. {{ number_format($expense->amount ?? 0, 2) }}</td>
                    <td>{{ $expense->createdBy->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No records found.</td>
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