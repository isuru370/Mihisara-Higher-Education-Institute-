<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Salary Slip - {{ $data['teacher_name'] ?? 'Teacher' }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #f1f5f9;
            padding: 30px;
            line-height: 1.4;
        }

        /* Salary Slip Container */
        .slip-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Header */
        .slip-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            padding: 20px 25px;
            text-align: center;
            color: white;
        }

        .slip-header h1 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .slip-header .institution {
            font-size: 11px;
            opacity: 0.9;
            margin-bottom: 3px;
        }

        .slip-header .address {
            font-size: 9px;
            opacity: 0.8;
        }

        .slip-header .slip-title {
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            display: inline-block;
        }

        /* Body */
        .slip-body {
            padding: 25px;
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

        .info-value.paid {
            color: #10b981;
        }

        .info-value.unpaid {
            color: #ef4444;
        }

        /* Earnings & Deductions Table */
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .salary-table th {
            background: #0f172a;
            color: white;
            padding: 10px 12px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #334155;
        }

        .salary-table td {
            padding: 10px 12px;
            font-size: 9px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .salary-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .salary-table .total-row {
            background: #eff6ff !important;
            font-weight: 800;
        }

        .salary-table .total-row td {
            font-size: 10px;
            font-weight: 800;
            color: #1e40af;
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

        .small-text {
            font-size: 7px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Summary Table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .summary-table th {
            background: #f1f5f9;
            padding: 8px 10px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #475569;
            border: 1px solid #e2e8f0;
            width: 25%;
        }

        .summary-table td {
            padding: 8px 10px;
            font-size: 9px;
            border: 1px solid #e2e8f0;
        }

        /* Signature Section */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
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
            padding: 10px 20px;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }

        /* Error Message */
        .error-message {
            text-align: center;
            padding: 50px;
            background: #fff;
            border-radius: 16px;
            max-width: 500px;
            margin: 50px auto;
        }

        .error-message h2 {
            color: #ef4444;
            margin-bottom: 10px;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .slip-container {
                box-shadow: none;
                border-radius: 0;
            }

            .slip-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .salary-table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    @php
        $data = $data ?? [];
        $isSuccess = ($data['status'] ?? '') === 'success';
        $teacherId = $data['teacher_id'] ?? '';
        $teacherName = $data['teacher_name'] ?? '';
        $monthYearDisplay = $data['month_year_display'] ?? '';
        $dateGenerated = $data['date_generated'] ?? now()->format('d M Y, h:i A');
        $isSalaryPaid = $data['is_salary_paid'] ?? false;
        $earnings = $data['earnings'] ?? [];
        $deductions = $data['deductions'] ?? [];
        $totalClassAmount = $data['total_class_amount'] ?? 0;
        $totalTeacherEarnings = $data['total_teacher_earnings'] ?? 0;
        $totalOrganizeCut = $data['total_organize_cut'] ?? 0;
        $totalInstitutionCut = $data['total_institution_cut'] ?? 0;
        $totalAddition = $data['total_addition'] ?? 0;
        $totalDeductions = $data['total_deductions'] ?? 0;
        $netSalary = $data['net_salary'] ?? 0;
        $paymentMethod = $data['payment_method'] ?? 'Cash';
    @endphp

    @if(!$isSuccess)

        <div class="error-message">
            <h2>⚠️ Salary Slip Load Failed</h2>
            <p>{{ $data['message'] ?? 'Unknown error occurred.' }}</p>
        </div>

    @else

        <div class="slip-container">

            {{-- Header --}}
            <div class="slip-header">
                <h1>MINIPALASA HIGHER EDUCATION</h1>
                <div class="institution">Mirigama</div>
                <div class="address">📞 +94 XX XXX XXXX | ✉ info@minipalasa.edu</div>
                <div class="slip-title">TEACHER SALARY SLIP</div>
            </div>

            {{-- Body --}}
            <div class="slip-body">

                {{-- Teacher Information Grid --}}
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Teacher ID</div>
                        <div class="info-value">{{ $teacherId }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Teacher Name</div>
                        <div class="info-value">{{ $teacherName }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Salary Period</div>
                        <div class="info-value">{{ $monthYearDisplay }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Payment Status</div>
                        <div class="info-value {{ $isSalaryPaid ? 'paid' : 'unpaid' }}">
                            {{ $isSalaryPaid ? '✓ PAID' : '✗ UNPAID' }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Payment Method</div>
                        <div class="info-value">{{ $paymentMethod }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Generated Date</div>
                        <div class="info-value">{{ $dateGenerated }}</div>
                    </div>
                </div>

                {{-- Earnings & Deductions Table --}}
                <table class="salary-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Earnings</th>
                            <th style="width: 15%;" class="text-right">Amount (Rs.)</th>
                            <th style="width: 30%;">Deductions</th>
                            <th style="width: 15%;" class="text-right">Amount (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $maxRows = max(count($earnings), count($deductions));
                        @endphp

                        @for($i = 0; $i < $maxRows; $i++)
                            <tr>
                                {{-- Earnings --}}
                                @if(isset($earnings[$i]))
                                    <td>
                                        <strong>{{ $earnings[$i]['description'] ?? '' }}</strong>
                                        @if(isset($earnings[$i]['class_total']))
                                            <div class="small-text">
                                                Class Total: Rs. {{ number_format($earnings[$i]['class_total'], 2) }}<br>
                                                Teacher ({{ $earnings[$i]['teacher_percentage'] ?? 0 }}%): Rs.
                                                {{ number_format($earnings[$i]['teacher_share'] ?? 0, 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-right amount amount-income">Rs.
                                        {{ number_format($earnings[$i]['amount'] ?? 0, 2) }}</td>
                                @else
                                    <td></td>
                                    <td class="text-right"></td>
                                @endif

                                {{-- Deductions --}}
                                @if(isset($deductions[$i]))
                                    <td>{{ $deductions[$i]['description'] ?? '' }}</td>
                                    <td class="text-right amount amount-deduction">- Rs.
                                        {{ number_format($deductions[$i]['amount'] ?? 0, 2) }}</td>
                                @else
                                    <td></td>
                                    <td class="text-right"></td>
                                @endif
                            </tr>
                        @endfor

                        {{-- Totals Row --}}
                        <tr class="total-row">
                            <td><strong>Total Earnings</strong></td>
                            <td class="text-right amount amount-income"><strong>Rs.
                                    {{ number_format($totalAddition, 2) }}</strong></td>
                            <td><strong>Total Deductions</strong></td>
                            <td class="text-right amount amount-deduction"><strong>- Rs.
                                    {{ number_format($totalDeductions, 2) }}</strong></td>
                        </tr>

                        {{-- Net Salary Row --}}
                        <tr class="total-row">
                            <td colspan="3"><strong>NET SALARY</strong></td>
                            <td class="text-right"><strong>Rs. {{ number_format($netSalary, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                {{-- Summary Table --}}
                <table class="summary-table">
                    <tr>
                        <th>Total Class Income</th>
                        <td>Rs. {{ number_format($totalClassAmount, 2) }}</td>
                        <th>Total Teacher Earnings</th>
                        <td>Rs. {{ number_format($totalTeacherEarnings, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total Organizer Cut</th>
                        <td>Rs. {{ number_format($totalOrganizeCut, 2) }}</td>
                        <th>Total Institute Cut</th>
                        <td>Rs. {{ number_format($totalInstitutionCut, 2) }}</td>
                    </tr>
                </table>

                {{-- Amount in Words --}}
                <div style="margin-bottom: 20px; padding: 8px 12px; background: #f8fafc; border-radius: 8px;">
                    <span style="font-size: 8px; color: #64748b;">AMOUNT IN WORDS:</span>
                    <span style="font-size: 9px; font-weight: 600; margin-left: 8px;">
                        {{ ucwords(convertNumberToWords($netSalary)) }} Rupees Only
                    </span>
                </div>

                {{-- Signature Section --}}
                <div class="signature-section">
                    <div class="signature-box">
                        <div class="signature-line">Teacher Signature</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line">Authorized Signature</div>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="slip-footer">
                This is a system-generated salary slip and does not require a physical signature.
                <br>For any discrepancies, please contact the accounts department.
            </div>

        </div>

    @endif

    <script>
        window.addEventListener('load', function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('autoPrint') === 'true') {
                setTimeout(() => {
                    window.print();
                }, 800);
            }
        });

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
    </script>

</body>

</html>