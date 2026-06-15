<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Temporary Card Expiry Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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

        .summary-value.total { color: #2563eb; }
        .summary-value.expired { color: #991b1b; }
        .summary-value.warning { color: #f59e0b; }

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

        /* Row Status Colors */
        .row-expired {
            background-color: #fee2e2 !important;
        }

        .row-expiring {
            background-color: #fef3c7 !important;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Status Badges */
        .badge-expired {
            background: #dc2626;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-warning {
            background: #f59e0b;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-critical {
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
            animation: blink 1s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Days Left Colors */
        .days-critical {
            color: #dc2626;
            font-weight: 800;
        }

        .days-warning {
            color: #f59e0b;
            font-weight: 700;
        }

        .days-normal {
            color: #64748b;
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
        <h1>Temporary QR Card Expiry Report</h1>
        <div class="subtitle">Expired Cards + Cards Expiring Within Next 10 Days</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Cards --}}
    @php
        $expiredCount = 0;
        $expiringSoonCount = 0;
        $criticalCount = 0;

        foreach ($students as $student) {
            $daysLeft = now()->diffInDays(
                \Carbon\Carbon::parse($student->temporary_qr_code_expire_date),
                false
            );

            if ($daysLeft < 0) {
                $expiredCount++;
            } elseif ($daysLeft <= 3) {
                $criticalCount++;
                $expiringSoonCount++;
            } else {
                $expiringSoonCount++;
            }
        }
    @endphp

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">TOTAL RECORDS</div>
                <div class="summary-value total">{{ $students->count() }}</div>
            </td>
            <td>
                <div class="summary-label">EXPIRED CARDS</div>
                <div class="summary-value expired">{{ $expiredCount }}</div>
            </td>
            <td>
                <div class="summary-label">EXPIRING SOON</div>
                <div class="summary-value warning">{{ $expiringSoonCount }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="summary-label">CRITICAL (≤3 DAYS)</div>
                <div class="summary-value expired">{{ $criticalCount }}</div>
            </td>
            <td colspan="2"></td>
        </tr>
    </table>

    {{-- Students Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Student ID</th>
                <th style="width: 18%;">Student Name</th>
                <th style="width: 12%;">Grade</th>
                <th style="width: 15%;">Guardian Mobile</th>
                <th style="width: 12%;">Expire Date</th>
                <th style="width: 12%;">Days Left</th>
                <th style="width: 14%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                @php
                    $expireDate = \Carbon\Carbon::parse($student->temporary_qr_code_expire_date);
                    $daysLeft = now()->diffInDays($expireDate, false);
                    $isExpired = $daysLeft < 0;
                    $isCritical = !$isExpired && $daysLeft <= 3;
                    $rowClass = $isExpired ? 'row-expired' : ($isCritical ? 'row-expired' : 'row-expiring');
                @endphp

                <tr class="{{ $rowClass }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $student->custom_id ?? $student->id }}</strong></td>
                    <td><strong>{{ $student->full_name ?? $student->initial_name }}</strong></td>
                    <td>{{ $student->grade->grade_name ?? $student->grade->name ?? '-' }}</td>
                    <td>{{ $student->guardian_mobile ?? '-' }}</td>
                    <td class="text-center">{{ $expireDate->format('d M Y') }}</td>
                    <td class="text-center">
                        @if($daysLeft < 0)
                            <span class="days-critical">Expired {{ abs($daysLeft) }} days ago</span>
                        @elseif($daysLeft == 0)
                            <span class="days-critical">Expires Today</span>
                        @elseif($daysLeft <= 3)
                            <span class="days-critical">{{ $daysLeft }} day(s) left</span>
                        @else
                            <span class="days-warning">{{ $daysLeft }} day(s) left</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($daysLeft < 0)
                            <span class="badge-expired">✗ EXPIRED</span>
                        @elseif($daysLeft <= 3)
                            <span class="badge-critical">⚠️ CRITICAL</span>
                        @else
                            <span class="badge-warning">⏳ EXPIRING SOON</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No expired or expiring temporary cards found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer Note --}}
    @if($expiredCount > 0 || $expiringSoonCount > 0)
    <div style="margin-top: 15px; padding: 8px 12px; background: #fef3c7; border-radius: 8px;">
        <span style="font-size: 7px; color: #92400e;">
            ⚠️ <strong>Note:</strong> 
            @if($expiredCount > 0) {{ $expiredCount }} card(s) have already expired. @endif
            @if($criticalCount > 0) {{ $criticalCount }} card(s) will expire within 3 days. @endif
            Please renew these cards immediately to avoid service disruption.
        </span>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>