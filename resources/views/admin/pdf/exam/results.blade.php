<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 11px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .info {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>{{ $exam->title }} - Results Report</h2>

    <div class="info">
        <strong>Class :</strong>
        {{ $exam->studentClass->class_name ?? 'N/A' }}
        <br>
        <strong>Grade :</strong>
        {{  $exam->studentClass?->grade?->grade_name ?? 'N/A'  }}
        <br>

        <strong>Category :</strong>
        {{ $exam->category->category_name ?? 'N/A' }}
        <br>

        <strong>Date :</strong>
        {{ $exam->exam_date->format('d M Y') }}
    </div>

    <table>

        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Student ID</th>
                <th>Marks</th>
                <th>Max</th>
                <th>%</th>
                <th>Grade</th>
                <th>Rank</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($results as $result)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>
                        {{ $result->student->initial_name ?? 'N/A' }}
                    </td>

                     <td>
                        {{ $result->student->custom_id ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $result->marks }}
                    </td>

                    <td>
                        {{ $result->max_marks }}
                    </td>

                    <td>
                        {{ $result->percentage }}
                    </td>

                    <td>
                        {{ $result->grade }}
                    </td>

                    <td>
                        {{ $result->rank }}
                    </td>

                    <td>
                        {{ ucfirst($result->status) }}
                    </td>
                </tr>
            @endforeach

        </tbody>

    </table>

</body>

</html>
