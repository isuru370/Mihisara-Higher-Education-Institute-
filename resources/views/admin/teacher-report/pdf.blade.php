<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
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

        /* Teacher Info Card */
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

        /* Summary Card */
        .summary-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 10px 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
        }

        .summary-value {
            font-size: 16px;
            font-weight: 800;
            color: #1e40af;
        }

        .summary-count {
            background: #dbeafe;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
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

        .amount-income {
            color: #166534;
        }

        /* Payment Method Badges */
        .method-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: 600;
        }

        .method-cash {
            background: #dcfce7;
            color: #166534;
        }

        .method-card {
            background: #dbeafe;
            color: #1e40af;
        }

        .method-bank {
            background: #fef3c7;
            color: #92400e;
        }

        .method-online {
            background: #e0e7ff;
            color: #3730a3;
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
        <div class="date">Generated on: {{ now()->format('d F Y, h:i A') }}</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Teacher Information --}}
    <div class="info-card">
        <div class="info-item">
            <div class="info-label">Teacher ID</div>
            <div class="info-value">{{ $teacher_id ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Report Date</div>
            <div class="info-value">{{ $date ?? now()->format('d M Y') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Records</div>
            <div class="info-value">{{ count($rows) }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Paid</div>
            <div class="info-value" style="color: #166534;">Rs. {{ number_format($summary_value, 2) }}</div>
        </div>
    </div>

    {{-- Summary Card --}}
    <div class="summary-card">
        <div>
            <div class="summary-label">TOTAL PAYMENT AMOUNT</div>
            <div class="summary-value">Rs. {{ number_format($summary_value, 2) }}</div>
        </div>
        <div>
            <span class="summary-count">📊 {{ count($rows) }} Transactions</span>
        </div>
    </div>

    {{-- Payments Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Class</th>
                <th style="width: 6%;">Grade</th>
                <th style="width: 10%;">Category</th>
                <th style="width: 8%;">Student Code</th>
                <th style="width: 12%;">Student Name</th>
                <th style="width: 10%;">Guardian Mobile</th>
                <th style="width: 6%;">Payment ID</th>
                <th style="width: 8%;">Paid At</th>
                <th style="width: 8%;" class="text-right">Amount</th>
                <th style="width: 8%;">Method</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($row['class_name'] ?? '-', 12) }}</td>
                    <td>{{ $row['grade_name'] ?? '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($row['category_name'] ?? '-', 12) }}</td>
                    <td><strong>{{ $row['student_code'] ?? '-' }}</strong></td>
                    <td>{{ \Illuminate\Support\Str::limit($row['student_name'] ?? '-', 15) }}</td>
                    <td>{{ $row['guardian_mobile'] ?? '-' }}</td>
                    <td><code>{{ $row['payment_id'] ?? '-' }}</code></td>
                    <td>{{ \Carbon\Carbon::parse($row['paid_at'])->format('d M Y') }}</td>
                    <td class="text-right amount amount-income">Rs. {{ number_format($row['amount'] ?? 0, 2) }}</td>
                    <td>
                        @php
                            $method = strtolower($row['payment_method'] ?? 'cash');
                            $methodClass = match ($method) {
                                'cash' => 'method-cash',
                                'card' => 'method-card',
                                'bank_transfer', 'bank' => 'method-bank',
                                'online' => 'method-online',
                                default => 'method-cash'
                            };
                        @endphp
                        <span class="method-badge {{ $methodClass }}">{{ ucfirst($row['payment_method'] ?? 'Cash') }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #eff6ff; font-weight: 800;">
                <td colspan="8" class="text-right"><strong>Total</strong></td>
                <td class="text-right amount"><strong>Rs. {{ number_format($summary_value, 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>