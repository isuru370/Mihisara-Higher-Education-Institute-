<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Salary Report - {{ $month }}/{{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 8px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .summary .label {
            font-weight: bold;
            width: 25%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-success {
            color: #28a745;
        }
        .text-primary {
            color: #007bff;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Teacher Salary Report</h1>
        <p>{{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</p>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Teachers:</td>
                <td><strong>{{ $summary['total_teachers'] ?? 0 }}</strong></td>
                <td class="label">Gross Earnings:</td>
                <td><strong>LKR {{ number_format($summary['gross_earnings'] ?? 0, 2) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Total Deductions:</td>
                <td><strong>LKR {{ number_format(($summary['advance_deducted'] ?? 0) + ($summary['other_deduction'] ?? 0), 2) }}</strong></td>
                <td class="label">Net Payable:</td>
                <td><strong>LKR {{ number_format($summary['net_payable'] ?? 0, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Teacher ID</th>
                <th>Teacher Name</th>
                <th class="text-right">Gross (LKR)</th>
                <th class="text-right">Advance (LKR)</th>
                <th class="text-right">Deduction (LKR)</th>
                <th class="text-right">Other (LKR)</th>
                <th class="text-right">Net Payable (LKR)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salaryRows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['teacher_custom_id'] ?? 'N/A' }}</td>
                <td>{{ $row['teacher_name'] }}</td>
                <td class="text-right">{{ number_format($row['earnings'], 2) }}</td>
                <td class="text-right text-danger">{{ number_format($row['advance'], 2) }}</td>
                <td class="text-right text-danger">{{ number_format($row['deduction'], 2) }}</td>
                <td class="text-right text-danger">{{ number_format($row['other'], 2) }}</td>
                <td class="text-right text-success"><strong>{{ number_format($row['net_payable'], 2) }}</strong></td>
                <td class="text-center">
                    @if($row['status'] == 'paid')
                        <span class="badge badge-success">Paid</span>
                    @else
                        <span class="badge badge-warning">Pending</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-right">{{ number_format($summary['gross_earnings'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($summary['advance_deducted'] ?? 0, 2) }}</td>
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($summary['other_deduction'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($summary['net_payable'] ?? 0, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature required.</p>
    </div>

</body>
</html>