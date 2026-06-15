<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Organizer Salary Slip - {{ $organizer->name }}</title>

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
            background: #f1f5f9;
            padding: 30px;
            line-height: 1.4;
        }

        /* Salary Slip Container */
        .slip-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Header */
        .slip-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            padding: 25px 30px;
            text-align: center;
            color: white;
        }

        .slip-header h1 {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .slip-header .month {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 5px;
        }

        /* Body */
        .slip-body {
            padding: 25px 30px;
        }

        /* Info Grid */
        .info-grid {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 25px;
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            border: 1px solid #e2e8f0;
        }

        .info-item {
            flex: 1;
            min-width: 150px;
            padding: 5px 10px;
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

        /* Amount Table */
        .amount-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .amount-table th {
            background: #0f172a;
            color: white;
            padding: 10px 12px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #334155;
        }

        .amount-table td {
            padding: 10px 12px;
            font-size: 10px;
            border: 1px solid #e2e8f0;
        }

        .amount-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .amount-table .total-row {
            background: #eff6ff !important;
            font-weight: 800;
        }

        .amount-table .total-row td {
            font-size: 12px;
            font-weight: 800;
            color: #1e40af;
        }

        .text-right {
            text-align: right;
        }

        .amount-income {
            color: #166534;
            font-weight: 700;
        }

        .amount-deduction {
            color: #991b1b;
            font-weight: 700;
        }

        /* Signature Section */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            margin-top: 30px;
            padding-top: 5px;
            border-top: 1px solid #94a3b8;
            width: 100%;
            font-size: 8px;
            color: #64748b;
        }

        /* Footer */
        .slip-footer {
            background: #f8fafc;
            padding: 12px 20px;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }

        /* Print Button */
        .print-btn {
            text-align: center;
            margin-top: 20px;
        }

        .print-btn button {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .print-btn button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .print-btn {
                display: none;
            }

            .slip-container {
                box-shadow: none;
                border-radius: 0;
            }

            .slip-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .amount-table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="slip-container">

        {{-- Header --}}
        <div class="slip-header">
            <h1>ORGANIZER SALARY SLIP</h1>
            <div class="month">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</div>
        </div>

        {{-- Body --}}
        <div class="slip-body">

            {{-- Organizer Information --}}
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Organizer Code</div>
                    <div class="info-value">{{ $organizer->code }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Organizer Name</div>
                    <div class="info-value">{{ $organizer->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mobile</div>
                    <div class="info-value">{{ $organizer->mobile ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Status</div>
                    <div class="info-value">
                        @if($salaryRecord->status == 'paid')
                            <span style="color: #10b981;">✓ Paid</span>
                        @else
                            <span style="color: #ef4444;">Pending</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Date</div>
                    <div class="info-value">{{ $salaryRecord->payment_date?->format('d M Y') ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Generated On</div>
                    <div class="info-value">{{ now()->format('d M Y, h:i A') }}</div>
                </div>
            </div>

            {{-- Salary Breakdown Table --}}
            <table class="amount-table">
                <thead>
                    <tr>
                        <th style="width: 70%;">Description</th>
                        <th style="width: 30%;" class="text-right">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Total Income --}}
                    <tr>
                        <td><strong>Total Income</strong></td>
                        <td class="text-right amount-income">Rs. {{ number_format($totalIncome, 2) }}</td>
                    </tr>

                    {{-- Advance Deduction --}}
                    @if($advanceAmount > 0)
                        <tr>
                            <td>Advance Deduction</td>
                            <td class="text-right amount-deduction">- Rs. {{ number_format($advanceAmount, 2) }}</td>
                        </tr>
                    @endif

                    {{-- Deduction --}}
                    @if($deductionAmount > 0)
                        <tr>
                            <td>Deduction</td>
                            <td class="text-right amount-deduction">- Rs. {{ number_format($deductionAmount, 2) }}</td>
                        </tr>
                    @endif

                    {{-- Other --}}
                    @if($otherAmount > 0)
                        <tr>
                            <td>Other</td>
                            <td class="text-right amount-deduction">- Rs. {{ number_format($otherAmount, 2) }}</td>
                        </tr>
                    @endif

                    {{-- Net Salary --}}
                    <tr class="total-row">
                        <td><strong>NET SALARY PAID</strong></td>
                        <td class="text-right"><strong>Rs. {{ number_format($salaryRecord->amount, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            {{-- Amount in Words --}}
            <div style="margin-bottom: 20px; padding: 8px 12px; background: #f8fafc; border-radius: 8px;">
                <span style="font-size: 8px; color: #64748b;">AMOUNT IN WORDS:</span>
                <span style="font-size: 9px; font-weight: 600; margin-left: 8px;">
                    {{ ucwords(convertNumberToWords($salaryRecord->amount)) }} Rupees Only
                </span>
            </div>

            {{-- Signature Section --}}
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Prepared By</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Organizer Signature</div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="slip-footer">
            This is a system-generated salary slip and does not require a physical signature.
            <br>For any discrepancies, please contact the accounts department.
        </div>

    </div>

    {{-- Print Button --}}
    <div class="print-btn no-print">
        <button onclick="window.print()">
            🖨️ Print Salary Slip
        </button>
    </div>

</body>

</html>

@php
    function convertNumberToWords($number)
    {
        $words = [
            0 => 'Zero',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        ];

        if ($number < 20) {
            return $words[$number] ?? '';
        } elseif ($number < 100) {
            $tens = floor($number / 10) * 10;
            $units = $number % 10;
            return $words[$tens] . ($units ? ' ' . $words[$units] : '');
        } elseif ($number < 1000) {
            $hundreds = floor($number / 100);
            $remainder = $number % 100;
            return $words[$hundreds] . ' Hundred' . ($remainder ? ' and ' . convertNumberToWords($remainder) : '');
        } elseif ($number < 100000) {
            $thousands = floor($number / 1000);
            $remainder = $number % 1000;
            return convertNumberToWords($thousands) . ' Thousand' . ($remainder ? ' ' . convertNumberToWords($remainder) : '');
        } elseif ($number < 10000000) {
            $lakhs = floor($number / 100000);
            $remainder = $number % 100000;
            return convertNumberToWords($lakhs) . ' Lakh' . ($remainder ? ' ' . convertNumberToWords($remainder) : '');
        } else {
            $crores = floor($number / 10000000);
            $remainder = $number % 10000000;
            return convertNumberToWords($crores) . ' Crore' . ($remainder ? ' ' . convertNumberToWords($remainder) : '');
        }
    }
@endphp