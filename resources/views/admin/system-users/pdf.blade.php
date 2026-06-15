<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>System Users Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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
            width: 70%;
            margin: 0 auto 25px auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 10px;
            text-align: center;
            width: 33.33%;
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
        <h1>System Users Report</h1>
        <div class="date">Generated on: {{ now()->format('d F Y, h:i A') }}</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalUsers = $systemUsers->count();
        $activeUsers = $systemUsers->where('is_active', true)->count();
        $inactiveUsers = $systemUsers->where('is_active', false)->count();
    @endphp

    <table class="summary-table">
        <tr>
            {{-- TOTAL USERS --}}
            <td class="total-card">
                <div class="card-value">{{ $totalUsers }}</div>
                <div class="card-label">Total Users</div>
                <div class="card-sub">All registered</div>
            </td>

            {{-- ACTIVE --}}
            <td class="active-card">
                <div class="card-value">{{ $activeUsers }}</div>
                <div class="card-label">Active</div>
                <div class="card-sub">Currently active</div>
            </td>

            {{-- INACTIVE --}}
            <td class="inactive-card">
                <div class="card-value">{{ $inactiveUsers }}</div>
                <div class="card-label">Inactive</div>
                <div class="card-sub">Not active</div>
            </td>
        </tr>
    </table>

    {{-- System Users Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Custom ID</th>
                <th style="width: 12%;">Full Name</th>
                <th style="width: 8%;">Mobile</th>
                <th style="width: 10%;">NIC</th>
                <th style="width: 8%;">Birthday</th>
                <th style="width: 6%;">Gender</th>
                <th style="width: 12%;">Address</th>
                <th style="width: 10%;">Email</th>
                <th style="width: 6%;">Status</th>
                <th style="width: 10%;">Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($systemUsers as $systemUser)
                <tr>
                    <td>
                        <strong>{{ $systemUser->custom_id ?? '-' }}</strong>
                        <div class="small">#{{ $systemUser->id }}</div>
                    </td>

                    <td>
                        <strong>{{ $systemUser->full_name }}</strong>
                    </td>

                    <td>
                        {{ $systemUser->mobile ?? '-' }}
                    </td>

                    <td>
                        <div class="small">{{ $systemUser->nic ?? '-' }}</div>
                    </td>

                    <td>
                        <div class="small">{{ optional($systemUser->bday)->format('Y-m-d') ?? '-' }}</div>
                    </td>

                    <td>
                        <div class="small">{{ ucfirst($systemUser->gender ?? '-') }}</div>
                    </td>

                    <td>
                        <div class="small">
                            {{ $systemUser->address1 ?? '' }}
                            @if($systemUser->address2)
                                , {{ $systemUser->address2 }}
                            @endif
                            @if($systemUser->address3)
                                , {{ $systemUser->address3 }}
                            @endif
                            @if(!$systemUser->address1 && !$systemUser->address2 && !$systemUser->address3)
                                -
                            @endif
                        </div>
                    </td>

                    <td>
                        <div class="small">{{ optional($systemUser->user)->email ?? '-' }}</div>
                    </td>

                    <td>
                        @if($systemUser->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>

                    <td>
                        <div class="small">{{ \Illuminate\Support\Str::limit($systemUser->note ?? '-', 30) }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">
                        <div class="small" style="padding: 20px;">No system users found.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System
    </div>

</body>

</html>