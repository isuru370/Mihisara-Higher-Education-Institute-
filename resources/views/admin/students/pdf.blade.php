<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Students Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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

        /* Summary Cards - Compact Professional Table */
        .summary-table {
            width: 80%;
            margin: 0 auto 25px auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 10px;
            text-align: center;
            width: 25%;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
        }

        .summary-table td:last-child {
            border-right: none;
        }

        .card-value {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .total-card .card-value {
            color: #2563eb;
        }

        .active-card .card-value {
            color: #10b981;
        }

        .inactive-card .card-value {
            color: #ef4444;
        }

        .admission-card .card-value {
            color: #8b5cf6;
        }

        .card-label {
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .card-sub {
            font-size: 7px;
            color: #94a3b8;
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
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
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

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .small {
            font-size: 7px;
            color: #64748b;
        }

        .text-center {
            text-align: center;
        }

        .text-danger {
            color: #ef4444;
        }

        .text-warning {
            color: #f59e0b;
        }

        .text-info {
            color: #0ea5e9;
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
        <h1>Students All Report</h1>
        <div class="date">Generated on: {{ now()->format('d F Y, h:i A') }}</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Cards - Compact Professional Table --}}
    @php
        $totalStudents = $students->count();
        $activeStudents = $students->where('is_active', true)->count();
        $inactiveStudents = $students->where('is_active', false)->count();
        $paidAdmissions = $students->where('admission', true)->count();
        $pendingAdmissions = $totalStudents - $paidAdmissions;
    @endphp

    <table class="summary-table">
        <tr>
            {{-- TOTAL STUDENTS --}}
            <td class="total-card">
                <div class="card-value">{{ $totalStudents }}</div>
                <div class="card-label">Total Students</div>
                <div class="card-sub">All registered</div>
            <td>

                {{-- ACTIVE --}}
            <td class="active-card">
                <div class="card-value">{{ $activeStudents }}</div>
                <div class="card-label">Active</div>
                <div class="card-sub">Currently active</div>
            <td>

                {{-- INACTIVE --}}
            <td class="inactive-card">
                <div class="card-value">{{ $inactiveStudents }}</div>
                <div class="card-label">Inactive</div>
                <div class="card-sub">Not active</div>
            <td>

                {{-- ADMISSION PAID --}}
            <td class="admission-card">
                <div class="card-value">{{ $paidAdmissions }}</div>
                <div class="card-label">Admission Paid</div>
                <div class="card-sub">Fee completed</div>
            <td>
        </tr>
    </table>

    {{-- Students Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 10%;">ID</th>
                <th style="width: 12%;">QR Code</th>
                <th style="width: 15%;">Student Name</th>
                <th style="width: 15%;">Contact Info</th>
                <th style="width: 8%;">Grade</th>
                <th style="width: 8%;">Class Type</th>
                <th style="width: 12%;">Guardian</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>

                    <td>
                        <strong>{{ $student->custom_id ?? '-' }}</strong>
                        <div class="small">#{{ $student->id }}</div>
                    </td>

                    <td>
                        @if($student->permanent_qr_active)
                            <span class="badge badge-success">✓ {{ $student->custom_id }}</span>
                            <div class="small">Permanent QR</div>
                        @elseif($student->temporary_qr_code)
                            <span class="badge badge-warning">⏱ {{ $student->temporary_qr_code }}</span>
                            <div class="small">
                                @php
                                    $expire = $student->temporary_qr_code_expire_date;
                                    $days = $expire ? now()->diffInDays($expire, false) : null;
                                @endphp

                                @if($days === null)
                                    No expiry
                                @elseif($days < 0)
                                    <span class="text-danger">Expired {{ abs($days) }} days ago</span>
                                @elseif($days == 0)
                                    <span class="text-warning">Expires today</span>
                                @else
                                    <span class="text-info">{{ $days }} days left</span>
                                @endif
                            </div>
                        @else
                            <span class="small">No QR</span>
                        @endif
                    </td>

                    <td>
                        <strong>{{ $student->full_name ?? $student->initial_name }}</strong>
                        @if($student->initial_name)
                            <div class="small">{{ $student->initial_name }}</div>
                        @endif
                        @if($student->nic)
                            <div class="small">NIC: {{ $student->nic }}</div>
                        @endif
                    </td>

                    <td>
                        <div>{{ $student->mobile ?? '-' }}</div>
                        @if($student->whatsapp_mobile)
                            <div class="small">WhatsApp: {{ $student->whatsapp_mobile }}</div>
                        @endif
                        @if($student->email)
                            <div class="small">{{ $student->email }}</div>
                        @endif
                    </td>

                    <td>
                        {{ $student->grade->grade_name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ ucfirst($student->class_type ?? 'N/A') }}
                    </td>

                    <td>
                        <div>{{ $student->guardian_mobile ?? '-' }}</div>
                        @if($student->guardian_fname)
                            <div class="small">{{ $student->guardian_fname }} {{ $student->guardian_lname }}</div>
                        @endif
                    </td>

                    <td>
                        @if($student->trashed())
                            <span class="badge badge-danger">Deleted</span>
                        @elseif($student->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif

                        <div class="small">
                            @if($student->admission)
                                <span class="badge badge-info">✓ Admission Paid</span>
                            @else
                                <span class="badge badge-warning">⏳ Pending</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System
    </div>

</body>

</html>