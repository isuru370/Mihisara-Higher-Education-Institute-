<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Issued Temporary ID Cards - {{ config('app.name', 'EDU NEXORA') }}</title>

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
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 12px;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: 0.5px;
        }

        /* Date Range */
        .date-range {
            text-align: center;
            font-size: 8px;
            color: #64748b;
            margin-bottom: 20px;
            padding: 5px 10px;
            background: #f8fafc;
            border-radius: 6px;
            display: inline-block;
        }

        /* Summary Cards - 6 columns */
        .summary-table {
            width: 100%;
            margin: 0 auto 20px auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 10px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            width: 16.66%;
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
            font-size: 14px;
            font-weight: 800;
        }

        .summary-value.total { color: #2563eb; }
        .summary-value.pending { color: #f59e0b; }
        .summary-value.downloaded { color: #8b5cf6; }
        .summary-value.issued { color: #166534; }
        .summary-value.active { color: #10b981; }
        .summary-value.expired { color: #991b1b; }

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

        .text-left {
            text-align: left;
        }

        /* Badge */
        .badge-issued {
            background: #dcfce7;
            color: #166534;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-not-assigned {
            background: #fef3c7;
            color: #92400e;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .temp-id {
            font-family: monospace;
            font-weight: 700;
            color: #2563eb;
            font-size: 9px;
        }

        .card-number {
            font-family: monospace;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
        }

        .student-name {
            font-weight: 600;
            color: #0f172a;
        }

        .student-mobile {
            font-size: 7px;
            color: #64748b;
        }

        /* Footer Note */
        .footer-note {
            margin-top: 15px;
            padding: 8px 12px;
            background: #eff6ff;
            border-radius: 8px;
            border-left: 3px solid #2563eb;
            font-size: 7px;
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

        @media print {
            .data-table th {
                background: #0f172a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <div class="header">
        <h1>Issued Temporary ID Cards</h1>
        <div class="subtitle">Temporary ID cards issued to the institute</div>
        <div class="date-range">
            📅 Issued: {{ $firstIssued ? $firstIssued->format('d M Y') : 'N/A' }} 
            @if($lastIssued && $firstIssued != $lastIssued)
                - {{ $lastIssued->format('d M Y') }}
            @endif
        </div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Card Status Summary --}}
    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">TOTAL CARDS</div>
                <div class="summary-value total">{{ $totalCards }}</div>
            </td>
            <td>
                <div class="summary-label">PENDING</div>
                <div class="summary-value pending">{{ $pendingCount }}</div>
            </td>
            <td>
                <div class="summary-label">DOWNLOADED</div>
                <div class="summary-value downloaded">{{ $downloadedCount }}</div>
            </td>
            <td>
                <div class="summary-label">ISSUED</div>
                <div class="summary-value issued">{{ $issuedCount }}</div>
            </td>
            <td>
                <div class="summary-label">ACTIVE</div>
                <div class="summary-value active">{{ $activeCount }}</div>
            </td>
            <td>
                <div class="summary-label">EXPIRED</div>
                <div class="summary-value expired">{{ $expiredCount }}</div>
            </td>
        </tr>
    </table>

    {{-- Cards Table with All Details --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 13%;">Temporary ID</th>
                <th style="width: 11%;">Card Number</th>
                <th style="width: 14%;">Student Name</th>
                <th style="width: 10%;">Student ID</th>
                <th style="width: 12%;">Guardian Mobile</th>
                <th style="width: 10%;">Student Mobile</th>
                <th style="width: 8%;">Grade</th>
                <th style="width: 10%;">Activated At</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cardDetails as $index => $card)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        <span class="temp-id">{{ $card['temporary_id_number'] }}</span>
                    </td>
                    <td class="text-center">
                        @if($card['card_number'])
                            <span class="card-number">{{ $card['card_number'] }}</span>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($card['is_assigned'])
                            <span class="student-name">{{ $card['student_name'] ?? 'N/A' }}</span>
                        @else
                            <span style="color: #94a3b8;">Not assigned</span>
                        @endif
                    </td>
                    <td>
                        @if($card['is_assigned'] && $card['student_id'])
                            <span>{{ $card['student_id'] }}</span>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($card['is_assigned'] && $card['guardian_mobile'])
                            {{ $card['guardian_mobile'] }}
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($card['is_assigned'] && $card['student_mobile'])
                            {{ $card['student_mobile'] }}
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($card['is_assigned'] && $card['grade_name'])
                            {{ $card['grade_name'] }}
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($card['activated_at'])
                            {{ $card['activated_at']->format('d M Y') }}
                            <div style="font-size: 7px; color: #64748b;">{{ $card['activated_at']->format('h:i A') }}</div>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($card['is_assigned'])
                            <span class="badge-active" style="background: #dcfce7; color: #166534; padding: 3px 10px; border-radius: 12px; font-size: 7px; font-weight: 600; display: inline-block;">✓ Active</span>
                        @else
                            <span class="badge-issued">✓ Issued</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 40px; color: #94a3b8;">
                        <span style="font-size: 14px;">📭</span><br>
                        <span style="font-size: 12px; margin-top: 8px; display: block;">No issued temporary ID cards found.</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer Note --}}
    <div class="footer-note">
        📋 <strong>Summary:</strong> 
        {{ $totalIssued }} issued cards | 
        {{ $cardsWithNumbers }} have card numbers | 
        {{ $cardsWithoutNumbers }} without card numbers | 
        {{ $assignedToStudents }} assigned to students
    </div>

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>