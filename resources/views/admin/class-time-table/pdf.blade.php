<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Weekly Timetable - Week {{ $weekNumber }}, {{ $year }} - {{ config('app.name', 'EDU NEXORA') }}</title>

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
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }

        .header .date {
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

        /* Week Info Card */
        .week-info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .week-info {
            font-size: 9px;
        }

        .week-info strong {
            color: #2563eb;
            font-size: 10px;
        }

        .generated-info {
            font-size: 8px;
            color: #64748b;
        }

        /* Summary Table - Horizontal Table */
        .summary-table {
            width: 80%;
            margin: 0 auto 20px auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 10px 15px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
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

        .summary-value.records {
            color: #8b5cf6;
        }

        /* Data Table */
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
            padding: 6px;
            font-size: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .data-table tr:nth-child(even) {
            background: #f8fafc;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: 700;
            text-align: center;
        }

        .badge-scheduled {
            background-color: #3b82f6;
            color: white;
        }

        .badge-pending {
            background-color: #f59e0b;
            color: white;
        }

        .badge-ongoing {
            background-color: #8b5cf6;
            color: white;
        }

        .badge-completed {
            background-color: #10b981;
            color: white;
        }

        .badge-cancelled {
            background-color: #ef4444;
            color: white;
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

        .hall-name {
            font-weight: 600;
        }

        .class-name {
            font-weight: 600;
            color: #1e293b;
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
        <h1>Weekly Class Timetable</h1>
        <div class="date">Week {{ $weekNumber }}, {{ $year }}</div>
    </div>

    {{-- Company --}}
    <div class="company-info">
        <div class="company-name">{{ config('app.name', 'EDU NEXORA') }}</div>
    </div>

    {{-- Week Info --}}
    <div class="week-info-card">
        <div class="week-info">
            <strong>Week Period:</strong> {{ \Carbon\Carbon::parse($startOfWeek)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endOfWeek)->format('d M Y') }}
        </div>
        <div class="generated-info">
            Generated: {{ $generatedAt->format('d M Y, h:i A') }}
        </div>
    </div>

    {{-- Summary Table --}}
    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">TOTAL CLASSES</div>
                <div class="summary-value total">{{ $schedules->count() }}</div>
            </td>
            <td>
                <div class="summary-label">SCHEDULED</div>
                <div class="summary-value records">{{ $schedules->where('status', 'scheduled')->count() }}</div>
            </td>
            <td>
                <div class="summary-label">ONGOING</div>
                <div class="summary-value records">{{ $schedules->where('status', 'ongoing')->count() }}</div>
            </td>
            <td>
                <div class="summary-label">COMPLETED</div>
                <div class="summary-value records">{{ $schedules->where('status', 'completed')->count() }}</div>
            </td>
            <td>
                <div class="summary-label">CANCELLED</div>
                <div class="summary-value records">{{ $schedules->where('status', 'cancelled')->count() }}</div>
            </td>
        </tr>
    </table>

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Day</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Hall</th>
                <th>Class Name</th>
                <th>Grade</th>
                <th>Category</th>
                <th>Fee</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $index => $schedule)
                @php
                    $statusText = [
                        'scheduled' => 'Scheduled',
                        'pending' => 'Pending',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled'
                    ];
                    $currentStatus = strtolower($schedule->status ?? 'pending');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($schedule->class_date)->format('Y-m-d') }}</td>
                    <td>{{ \Carbon\Carbon::parse($schedule->class_date)->format('l') }}</td>
                    <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</td>
                    <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                    <td>
                        <span
                            class="hall-name">{{ $schedule->hall->hall_name ?? $schedule->classHall->hall_name ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="class-name">{{ $schedule->studentClass->class_name ?? '-' }}</span>
                    </td>
                    <td>{{ $schedule->studentClass->grade->grade_name ?? '-' }}</td>
                    <td>{{ $schedule->classCategoryFee->category->category_name ?? '-' }}</td>
                    <td class="text-right amount">
                        @php
                            $fee = $schedule->classCategoryFee->fee ?? 0;
                        @endphp
                        @if($fee > 0)
                            Rs. {{ number_format($fee, 2) }}
                        @else
                            Free
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ $currentStatus }}">
                            {{ $statusText[$currentStatus] ?? ucfirst($currentStatus) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">
                        <span style="color: #94a3b8;">No class schedules found for this week.</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        This is a system generated document. | {{ config('app.name', 'EDU NEXORA') }} | All Rights Reserved.<br>
        Generated on {{ now()->format('d M Y, h:i A') }}
    </div>

</body>

</html>