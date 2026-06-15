<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Teacher Payment Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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

        /* Teacher Info Card */
        .teacher-info {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .teacher-info-item {
            flex: 1;
            min-width: 120px;
        }

        .teacher-info-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 4px;
        }

        .teacher-info-value {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Summary Table - Horizontal */
        .summary-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 10px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            width: 12.5%;
        }

        .summary-table td:first-child {
            border-radius: 8px 0 0 8px;
        }

        .summary-table td:last-child {
            border-radius: 0 8px 8px 0;
        }

        .summary-label {
            font-size: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 12px;
            font-weight: 800;
        }

        .summary-value.total {
            color: #2563eb;
        }

        .summary-value.paid {
            color: #166534;
        }

        .summary-value.partial {
            color: #92400e;
        }

        .summary-value.unpaid {
            color: #991b1b;
        }

        .summary-value.free {
            color: #1d4ed8;
        }

        /* Class Section */
        .class-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .class-header {
            background: #0f172a;
            color: white;
            padding: 8px 12px;
            border-radius: 8px 8px 0 0;
            font-size: 11px;
            font-weight: 700;
        }

        .category-header {
            background: #f1f5f9;
            padding: 6px 12px;
            margin-top: 8px;
            font-size: 9px;
            font-weight: 700;
            color: #1e40af;
            border-left: 3px solid #2563eb;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }

        .data-table th {
            background: #e2e8f0;
            padding: 6px 6px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #cbd5e1;
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

        /* Status Colors */
        .status-paid {
            color: #166534;
            font-weight: 700;
        }

        .status-partial {
            color: #92400e;
            font-weight: 700;
        }

        .status-unpaid {
            color: #991b1b;
            font-weight: 700;
        }

        .status-freecard {
            color: #1d4ed8;
            font-weight: 700;
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
        <h1>Teacher With Student Payment Report</h1>
        <div class="period">
            {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
        </div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Teacher Information --}}
    <div class="teacher-info">
        <div class="teacher-info-item">
            <div class="teacher-info-label">Teacher ID</div>
            <div class="teacher-info-value">{{ $report['teacher']['id'] }}</div>
        </div>
        <div class="teacher-info-item">
            <div class="teacher-info-label">Custom ID</div>
            <div class="teacher-info-value">{{ $report['teacher']['custom_id'] }}</div>
        </div>
        <div class="teacher-info-item">
            <div class="teacher-info-label">Initials</div>
            <div class="teacher-info-value">{{ $report['teacher']['initials'] }}</div>
        </div>
        <div class="teacher-info-item">
            <div class="teacher-info-label">Generated At</div>
            <div class="teacher-info-value">{{ now()->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    {{-- Summary Table (8 Columns) --}}
    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">YEAR</div>
                <div class="summary-value total">{{ $year }}</div>
            </td>
            <td>
                <div class="summary-label">MONTH</div>
                <div class="summary-value total">{{ \Carbon\Carbon::create()->month($month)->format('M') }}</div>
            </td>
            <td>
                <div class="summary-label">TOTAL CLASSES</div>
                <div class="summary-value total">{{ $report['summary']['total_classes'] }}</div>
            </td>
            <td>
                <div class="summary-label">TOTAL STUDENTS</div>
                <div class="summary-value total">{{ $report['summary']['total_students'] }}</div>
            </td>
            <td>
                <div class="summary-label">PAID</div>
                <div class="summary-value paid">{{ $report['summary']['paid_students'] }}</div>
            </td>
            <td>
                <div class="summary-label">PARTIAL</div>
                <div class="summary-value partial">{{ $report['summary']['partial_students'] }}</div>
            </td>
            <td>
                <div class="summary-label">UNPAID</div>
                <div class="summary-value unpaid">{{ $report['summary']['unpaid_students'] }}</div>
            </td>
            <td>
                <div class="summary-label">FREE CARD</div>
                <div class="summary-value free">{{ $report['summary']['freecard_students'] }}</div>
            </td>
        </tr>
    </table>

    {{-- Classes Section --}}
    @foreach($report['classes'] as $class)
        <div class="class-section">
            <div class="class-header">
                📚 Class: {{ $class['class_name'] }} | Grade: {{ $class['grade_name'] }}
            </div>

            @foreach($class['categories'] as $category)
                <div class="category-header">
                    🏷️ Category: {{ $category['category_name'] }} |
                    Fee: Rs. {{ number_format($category['fee'], 2) }} |
                    Total: {{ $category['total_students'] }} |
                    Paid: {{ $category['paid_count'] }} |
                    Partial: {{ $category['partial_count'] }} |
                    Unpaid: {{ $category['unpaid_count'] }} |
                    Free: {{ $category['freecard_count'] }}
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Student Code</th>
                            <th style="width: 20%;">Student Name</th>
                            <th style="width: 15%;">Guardian Mobile</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 10%;" class="text-right">Final Fee</th>
                            <th style="width: 10%;" class="text-right">Paid</th>
                            <th style="width: 10%;" class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Paid Students --}}
                        @foreach($category['students']['paid'] as $student)
                            <tr>
                                <td>{{ $student['student_code'] }}</td>
                                <td><strong>{{ $student['initial_name'] }}</strong></td>
                                <td>{{ $student['guardian_mobile'] }}</td>
                                <td class="status-paid text-center">✓ Paid</td>
                                <td class="text-right amount">Rs. {{ number_format($student['final_fee'], 2) }}</td>
                                <td class="text-right amount">Rs. {{ number_format($student['paid_amount'], 2) }}</td>
                                <td class="text-right amount">Rs. {{ number_format($student['balance'], 2) }}</td>
                            </tr>
                        @endforeach

                        {{-- Partial Students --}}
                        @foreach($category['students']['partial'] as $student)
                            <tr>
                                <td>{{ $student['student_code'] }}</td>
                                <td><strong>{{ $student['initial_name'] }}</strong></td>
                                <td>{{ $student['guardian_mobile'] }}</td>
                                <td class="status-partial text-center">⏳ Partial</td>
                                <td class="text-right amount">Rs. {{ number_format($student['final_fee'], 2) }}</td>
                                <td class="text-right amount">Rs. {{ number_format($student['paid_amount'], 2) }}</td>
                                <td class="text-right amount">Rs. {{ number_format($student['balance'], 2) }}</td>
                            </tr>
                        @endforeach

                        {{-- Unpaid Students --}}
                        @foreach($category['students']['unpaid'] as $student)
                            <tr>
                                <td>{{ $student['student_code'] }}</td>
                                <td><strong>{{ $student['initial_name'] }}</strong></td>
                                <td>{{ $student['guardian_mobile'] }}</td>
                                <td class="status-unpaid text-center">✗ Unpaid</td>
                                <td class="text-right amount">Rs. {{ number_format($student['final_fee'], 2) }}</td>
                                <td class="text-right amount">Rs. {{ number_format($student['paid_amount'], 2) }}</td>
                                <td class="text-right amount">Rs. {{ number_format($student['balance'], 2) }}</td>
                            </tr>
                        @endforeach

                        {{-- Free Card Students --}}
                        @foreach($category['students']['freecard'] as $student)
                            <tr>
                                <td>{{ $student['student_code'] }}</td>
                                <td><strong>{{ $student['initial_name'] }}</strong></td>
                                <td>{{ $student['guardian_mobile'] }}</td>
                                <td class="status-freecard text-center">🎫 Free Card</td>
                                <td class="text-right amount">Rs. {{ number_format($student['final_fee'], 2) }}</td>
                                <td class="text-right amount">Rs. {{ number_format($student['paid_amount'], 2) }}</td>
                                <td class="text-right amount">Rs. {{ number_format($student['balance'], 2) }}</td>
                            </tr>
                        @endforeach

                        @if(
                                count($category['students']['paid']) === 0 &&
                                count($category['students']['partial']) === 0 &&
                                count($category['students']['unpaid']) === 0 &&
                                count($category['students']['freecard']) === 0
                            )
                            <tr>
                                <td colspan="7" class="text-center">No students found in this category.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            @endforeach
        </div>
    @endforeach

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>