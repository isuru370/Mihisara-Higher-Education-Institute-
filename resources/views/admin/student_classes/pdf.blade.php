<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Student Classes Report - {{ config('app.name', 'EDU NEXORA') }}</title>

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
            width: 80%;
            margin: 0 auto 25px auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 12px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            width: 25%;
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
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 14px;
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

        .summary-value.ongoing {
            color: #0ea5e9;
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

        .text-center {
            text-align: center;
        }

        /* Badges */
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

        .badge-ongoing {
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-not-ongoing {
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            display: inline-block;
        }

        .small {
            font-size: 7px;
            color: #64748b;
            margin-top: 2px;
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
        <h1>Student Classes Report</h1>
        <div class="date">Generated on: {{ now()->format('d F Y, h:i A') }}</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalClasses = $classes->count();
        $activeClasses = $classes->where('is_active', true)->count();
        $inactiveClasses = $classes->where('is_active', false)->count();
        $ongoingClasses = $classes->where('is_ongoing', true)->count();
    @endphp

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">TOTAL CLASSES</div>
                <div class="summary-value total">{{ $totalClasses }}</div>
            </td>
            <td>
                <div class="summary-label">ACTIVE</div>
                <div class="summary-value active">{{ $activeClasses }}</div>
            </td>
            <td>
                <div class="summary-label">INACTIVE</div>
                <div class="summary-value inactive">{{ $inactiveClasses }}</div>
            </td>
            <td>
                <div class="summary-label">ONGOING</div>
                <div class="summary-value ongoing">{{ $ongoingClasses }}</div>
            </td>
        </tr>
    </table>

    {{-- Classes Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 18%;">Class Information</th>
                <th style="width: 12%;">Teacher</th>
                <th style="width: 12%;">Grade / Subject</th>
                <th style="width: 15%;">Payment Split (%)</th>
                <th style="width: 12%;">Organizer</th>
                <th style="width: 14%;">Effective Dates</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($classes as $class)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>
                        <strong>{{ $class->class_name }}</strong>
                        <div class="small">{{ ucfirst($class->class_type ?? 'N/A') }} / {{ $class->medium ?? 'N/A' }}</div>
                        @if($class->class_fee)
                            <div class="small">Fee: Rs. {{ number_format($class->class_fee, 2) }}</div>
                        @endif
                    </td>

                    <td>
                        <strong>{{ $class->teacher->full_name ?? 'N/A' }}</strong>
                        @if($class->teacher)
                            <div class="small">ID: {{ $class->teacher->custom_id ?? '#' . $class->teacher->id }}</div>
                        @endif
                    </td>

                    <td>
                        <strong>{{ $class->grade->grade_name ?? 'N/A' }}</strong>
                        <div class="small">{{ $class->subject->subject_name ?? 'N/A' }}</div>
                    </td>

                    <td>
                        <div class="small">👨‍🏫 Teacher:
                            <strong>{{ $class->paymentConfig->teacher_percentage ?? '0' }}%</strong></div>
                        <div class="small">📋 Organizer:
                            <strong>{{ $class->paymentConfig->organizer_percentage ?? '0' }}%</strong></div>
                        <div class="small">🏛️ Institution:
                            <strong>{{ $class->paymentConfig->institution_percentage ?? '0' }}%</strong></div>
                    </td>

                    <td>
                        {{ $class->paymentConfig->organizer->name ?? 'None' }}
                        @if($class->paymentConfig->organizer)
                            <div class="small">Code: {{ $class->paymentConfig->organizer->code ?? '-' }}</div>
                        @endif
                    </td>

                    <td>
                        <div class="small">From:
                            {{ optional($class->paymentConfig?->effective_from)->format('d M Y') ?? 'N/A' }}</div>
                        <div class="small">To:
                            {{ optional($class->paymentConfig?->effective_to)->format('d M Y') ?? 'N/A' }}</div>
                        @if($class->created_at)
                            <div class="small">Created: {{ $class->created_at->format('d M Y') }}</div>
                        @endif
                    </td>

                    <td>
                        <div class="text-center">
                            @if($class->is_active)
                                <span class="badge-active">✓ Active</span>
                            @else
                                <span class="badge-inactive">✗ Inactive</span>
                            @endif
                        </div>
                        <div class="text-center small" style="margin-top: 4px;">
                            @if($class->is_ongoing)
                                <span class="badge-ongoing">⟳ Ongoing</span>
                            @else
                                <span class="badge-not-ongoing">⏸ Completed</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Generated by {{ config('app.name', 'EDU NEXORA') }} System on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>