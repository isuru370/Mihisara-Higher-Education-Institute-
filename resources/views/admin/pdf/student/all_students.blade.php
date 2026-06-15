<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Student QR Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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

        .header .subtitle {
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

        .summary-value.permanent {
            color: #166534;
        }

        .summary-value.temporary {
            color: #f59e0b;
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

        /* Row Colors */
        .row-permanent {
            background-color: #dcfce7;
        }

        .row-temporary {
            background-color: #fef3c7;
        }

        .text-center {
            text-align: center;
        }

        /* Status Badges */
        .badge-permanent {
            background: #166534;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-temporary {
            background: #f59e0b;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .small-text {
            font-size: 7px;
            color: #64748b;
            margin-top: 2px;
        }

        /* QR Code Display */
        .qr-code {
            font-family: monospace;
            background: #f1f5f9;
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 8px;
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
        <h1>Student QR Report</h1>
        <div class="subtitle">All Student Details with QR Code Status</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalStudents = $students->count();
        $permanentQrCount = $students->where('permanent_qr_active', 1)->count();
        $temporaryQrCount = $students->where('permanent_qr_active', 0)->count();
        $noQrCount = $students->whereNull('temporary_qr_code')->where('permanent_qr_active', 0)->count();
    @endphp

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">TOTAL STUDENTS</div>
                <div class="summary-value total">{{ $totalStudents }}</div>
        </tr>
        <td>
            <div class="summary-label">PERMANENT QR</div>
            <div class="summary-value permanent">{{ $permanentQrCount }}</div>
        </td>
        <td>
            <div class="summary-label">TEMPORARY QR</div>
            <div class="summary-value temporary">{{ $temporaryQrCount }}</div>
            </tr>
            </tr>
    </table>

    {{-- Students Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Student ID</th>
                <th style="width: 20%;">Full Name</th>
                <th style="width: 12%;">Grade</th>
                <th style="width: 15%;">Guardian Mobile</th>
                <th style="width: 12%;">QR Type</th>
                <th style="width: 24%;">QR Code / Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                @php
                    $isPermanent = $student->permanent_qr_active == 1;
                    $rowClass = $isPermanent ? 'row-permanent' : 'row-temporary';
                    $expireDate = $student->temporary_qr_code_expire_date;
                    $daysLeft = $expireDate ? now()->diffInDays($expireDate, false) : null;
                @endphp

                <tr class="{{ $rowClass }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $student->custom_id ?? $student->id }}</strong></td>
                    <td>
                        <strong>{{ $student->full_name ?? $student->initial_name }}</strong>
                        @if($student->initial_name && $student->full_name != $student->initial_name)
                            <div class="small-text">({{ $student->initial_name }})</div>
                        @endif
                    </td>
                    <td>{{ $student->grade_name ?? $student->grade->grade_name ?? '-' }}</td>
                    <td>{{ $student->guardian_mobile ?? '-' }}</td>
                    <td class="text-center">
                        @if($isPermanent)
                            <span class="badge-permanent">✓ PERMANENT</span>
                        @else
                            <span class="badge-temporary">⏱ TEMPORARY</span>
                        @endif
                    </td>
                    <td>
                        @if($isPermanent)
                            <span class="qr-code">{{ $student->custom_id }}</span>
                            <div class="small-text">Permanent QR Code</div>
                        @elseif($student->temporary_qr_code)
                            <span class="qr-code">{{ $student->temporary_qr_code }}</span>
                            @if($daysLeft !== null)
                                @if($daysLeft < 0)
                                    <div class="small-text" style="color: #dc2626;">Expired {{ abs($daysLeft) }} days ago</div>
                                @elseif($daysLeft == 0)
                                    <div class="small-text" style="color: #dc2626;">Expires Today!</div>
                                @elseif($daysLeft <= 3)
                                    <div class="small-text" style="color: #dc2626;">⚠️ Expires in {{ $daysLeft }} days</div>
                                @elseif($daysLeft <= 10)
                                    <div class="small-text" style="color: #f59e0b;">Expires in {{ $daysLeft }} days</div>
                                @else
                                    <div class="small-text">Expires in {{ $daysLeft }} days</div>
                                @endif
                            @endif
                        @else
                            <span class="small-text">No QR Code Assigned</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer Note --}}
    @if($temporaryQrCount > 0)
        <div style="margin-top: 15px; padding: 8px 12px; background: #fef3c7; border-radius: 8px;">
            <span style="font-size: 7px; color: #92400e;">
                ⚠️ <strong>Note:</strong> {{ $temporaryQrCount }} student(s) are using temporary QR codes.
                Please remind them to upgrade to permanent QR codes before expiry.
            </span>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>