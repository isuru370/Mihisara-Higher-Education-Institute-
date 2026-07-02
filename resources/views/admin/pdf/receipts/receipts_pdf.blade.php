<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Receipts Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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
            width: 50%;
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
            font-size: 16px;
            font-weight: 800;
        }

        .summary-value.total {
            color: #2563eb;
        }

        .summary-value.amount {
            color: #166534;
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

        /* Receipt Type Badges */
        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
        }

        .type-payment {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-admission {
            background: #dcfce7;
            color: #166534;
        }

        .type-extra {
            background: #fef3c7;
            color: #92400e;
        }

        .type-refund {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Receipt Number */
        .receipt-code {
            font-family: monospace;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 600;
        }

        /* Footer Total */
        .footer-total {
            margin-top: 20px;
            padding: 10px 15px;
            background: #eff6ff;
            border-radius: 8px;
            text-align: right;
            border: 1px solid #bfdbfe;
        }

        .footer-total .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
            margin-right: 10px;
        }

        .footer-total .value {
            font-size: 16px;
            font-weight: 800;
            color: #1e40af;
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
        <h1>Receipts Report</h1>
        <div class="date">Generated on: {{ now()->format('d F Y, h:i A') }}</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Cards --}}
    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">TOTAL RECEIPTS</div>
                <div class="summary-value total">{{ $totalReceipts }}</div>
            </td>
            <td>
                <div class="summary-label">TOTAL AMOUNT</div>
                <div class="summary-value amount">Rs. {{ number_format($totalAmount, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Receipts Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Receipt Number</th>
                <th style="width: 30%;">Type</th>
                <th style="width: 20%;" class="text-right">Amount</th>
                <th style="width: 25%;">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receipts as $receipt)
                <tr>
                    <td>
                        <span class="receipt-code">{{ $receipt['receipt_number'] }}</span>
                    </td>
                    <td>
                        @php
                            $type = strtolower($receipt['type']);
                            $typeClass = match ($type) {
                                'payment', 'student payment' => 'type-payment',
                                'admission' => 'type-admission',
                                'extra', 'extra income' => 'type-extra',
                                'refund' => 'type-refund',
                                default => 'type-payment'
                            };
                        @endphp
                        <span class="type-badge {{ $typeClass }}">{{ $receipt['type'] }}</span>
                    </td>
                    <td class="text-right amount amount-income">Rs. {{ number_format($receipt['amount'], 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($receipt['date'])->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No receipts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer Total --}}
    <div class="footer-total">
        <span class="label">GRAND TOTAL :</span>
        <span class="value">Rs. {{ number_format($totalAmount, 2) }}</span>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>