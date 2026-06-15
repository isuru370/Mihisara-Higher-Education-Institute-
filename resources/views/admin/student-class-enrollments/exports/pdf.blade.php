<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Class Enrollment Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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

        /* Class Info Card */
        .class-info-card {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .class-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2563eb;
            display: inline-block;
        }

        .info-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .info-item {
            flex: 1;
            min-width: 150px;
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

        .info-value.category {
            color: #2563eb;
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
            font-size: 16px;
            font-weight: 800;
        }

        .summary-value.total {
            color: #2563eb;
        }

        .summary-value.active {
            color: #166534;
        }

        .summary-value.inactive {
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
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .amount {
            font-weight: 700;
            font-family: monospace;
        }

        /* Student Code */
        .student-code {
            font-family: monospace;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 600;
            display: inline-block;
        }

        /* Status Badges */
        .badge-active {
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
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
        <h1>Class Enrollment Report</h1>
        <div class="date">Generated on: {{ now()->format('d F Y, h:i A') }}</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Class Information Card --}}
    <div class="class-info-card">
        <div class="class-title">{{ $studentClass->class_name }}</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Grade</div>
                <div class="info-value">{{ $studentClass->grade?->grade_name ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Subject</div>
                <div class="info-value">{{ $studentClass->subject?->subject_name ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Teacher</div>
                <div class="info-value">
                    {{ $studentClass->teacher?->initials ?? $studentClass->teacher?->full_name ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Category</div>
                <div class="info-value category">{{ $classCategory->category_name }}</div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalStudents = $enrollments->count();
        $activeStudents = $enrollments->where('is_active', true)->count();
        $inactiveStudents = $enrollments->where('is_active', false)->count();
        $totalFee = $enrollments->sum('final_fee');
    @endphp

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">TOTAL STUDENTS</div>
                <div class="summary-value total">{{ $totalStudents }}</div>
            </td>
            <td>
                <div class="summary-label">ACTIVE</div>
                <div class="summary-value active">{{ $activeStudents }}</div>
            </td>
            <td>
                <div class="summary-label">INACTIVE</div>
                <div class="summary-value inactive">{{ $inactiveStudents }}</div>
            </td>
        </tr>
    </table>

    {{-- Enrollment Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Student ID / QR</th>
                <th style="width: 18%;">Initial Name</th>
                <th style="width: 20%;">Full Name</th>
                <th style="width: 12%;">Mobile</th>
                <th style="width: 15%;" class="text-right">Final Fee</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $key => $enrollment)
                @php
                    $student = $enrollment->student;
                    $studentCode = '-';

                    if ($student && $student->permanent_qr_active && !empty($student->custom_id) && $student->custom_id != '0') {
                        $studentCode = $student->custom_id;
                    } else {
                        $studentCode = $student->temporary_qr_code ?? '-';
                    }
                @endphp

                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    <td class="text-center"><span class="student-code">{{ $studentCode }}</span></td>
                    <td><strong>{{ $student->initial_name ?? '-' }}</strong></td>
                    <td>{{ $student->full_name ?? '-' }}</td>
                    <td>{{ $student->mobile ?? '-' }}</td>
                    <td class="text-right amount">Rs. {{ number_format($enrollment->final_fee, 2) }}</td>
                    <td class="text-center">
                        @if($enrollment->is_active)
                            <span class="badge-active">✓ Active</span>
                        @else
                            <span class="badge-inactive">✗ Inactive</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #eff6ff; font-weight: 800;">
                <td colspan="5" class="text-right"><strong>Total</strong></td>
                <td class="text-right amount"><strong>Rs. {{ number_format($totalFee, 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Footer Note --}}
    @if($inactiveStudents > 0)
        <div style="margin-top: 15px; padding: 8px 12px; background: #fef3c7; border-radius: 8px;">
            <span style="font-size: 7px; color: #92400e;">
                ⚠️ <strong>Note:</strong> {{ $inactiveStudents }} student(s) have inactive enrollment status.
            </span>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>