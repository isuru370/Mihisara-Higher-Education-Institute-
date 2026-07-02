<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Exam Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-date {
            text-align: right;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        table th {
            background: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>Exam Report</h2>

    <div class="report-date">
        Generated : {{ now()->format('d M Y h:i A') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Exam Title</th>
                <th>Class</th>
                <th>Category</th>
                <th>Hall</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Results</th>
            </tr>
        </thead>

        <tbody>

            @forelse($exams as $exam)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>
                        {{ $exam->title }}
                    </td>

                    <td>
                        {{ $exam->studentClass->class_name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $exam->category->category_name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $exam->hall->hall_name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $exam->exam_date?->format('d M Y') }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }}
                        -
                        {{ \Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}
                    </td>

                    <td>
                        {{ ucfirst($exam->status) }}
                    </td>

                    <td class="text-center">
                        {{ $exam->results->count() }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">
                        No Exams Found
                    </td>
                </tr>
            @endforelse

        </tbody>
    </table>

</body>

</html>
