<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }} - {{ config('app.name', 'EDU NEXORA') }}</title>

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

        .header .date {
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

        .summary-value.income {
            color: #166534;
        }

        .summary-value.expense {
            color: #991b1b;
        }

        .summary-value.net {
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
            padding: 8px 10px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #334155;
        }

        .data-table td {
            padding: 8px 10px;
            font-size: 9px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .data-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .amount-income {
            color: #166534;
            font-weight: 700;
        }

        .amount-expense {
            color: #991b1b;
            font-weight: 700;
        }

        .amount-net {
            color: #1e40af;
            font-weight: 800;
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
        <h1>{{ $title }}</h1>
        <div class="date">{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Table - Horizontal Table (3 Columns) --}}
    @php
        $totalIncome = ($summary_data['payment_total'] ?? 0) + ($summary_data['admission_total'] ?? 0) + ($summary_data['extra_income_total'] ?? 0);
        $totalExpense = ($summary_data['teacher_expense_total'] ?? 0) + ($summary_data['organizer_expense_total'] ?? 0) + ($summary_data['instituteExpencesTotal'] ?? 0);
        $netBalance = $summary_data['net_total'] ?? 0;
    @endphp

    <table class="summary-table">
        <tr>
            {{-- TOTAL INCOME --}}
            <td class="income-card">
                <div class="summary-label">TOTAL INCOME</div>
                <div class="summary-value income">Rs. {{ number_format($totalIncome, 2) }}</div>
            </td>

            {{-- TOTAL EXPENSES --}}
            <td class="expense-card">
                <div class="summary-label">TOTAL EXPENSES</div>
                <div class="summary-value expense">Rs. {{ number_format($totalExpense, 2) }}</div>
            </td>

            {{-- NET BALANCE --}}
            <td class="net-card">
                <div class="summary-label">NET BALANCE</div>
                <div class="summary-value net">Rs. {{ number_format($netBalance, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Category</th>
                <th style="width: 45%;">Sub Category</th>
                <th style="width: 25%;" class="text-right">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td><strong>{{ $row['category'] }}</strong></td>
                    <td>{{ $row['sub_category'] }}</td>
                    <td class="text-right
                                @if($row['type'] == 'income') amount-income
                                @elseif($row['type'] == 'expense') amount-expense
                                @elseif($row['type'] == 'net') amount-net
                                @endif">
                        @if($row['type'] == 'expense') - @endif
                        Rs. {{ number_format($row['amount'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>