<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DailyReport\DailyReportExport;
use App\Http\Controllers\Controller;
use App\Services\DailyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DailyReportController extends Controller
{
    public function index(Request $request, DailyReportService $service)
    {
        $date = $request->date
            ? Carbon::parse($request->date)->toDateString()
            : Carbon::today()->toDateString();

        $type = $request->type ?? 'student';

        $summary = $service->generateDailyReport($date);
        $report = $this->resolveReport($type, $date, $service);

        return view('admin.daily-report.index', compact('date', 'type', 'summary', 'report'));
    }

    protected function resolveReport(string $type, ?string $date, DailyReportService $service): array
    {
        $date = $date
            ? Carbon::parse($date)->toDateString()
            : Carbon::today()->toDateString();

        return match ($type) {
            'student' => $this->buildStudentReport($date, $service),
            'teacher' => $this->buildTeacherReport($date, $service),
            'institution' => $this->buildInstitutionReport($date, $service),
            'organizer' => $this->buildOrganizerReport($date, $service),
            'admission' => $this->buildAdmissionReport($date, $service),
            'summary' => $this->buildSummaryReport($date, $service),
            default => abort(404, 'Invalid report type'),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Summary Report (generateDailyReport)
    |--------------------------------------------------------------------------
    */
    protected function buildSummaryReport(string $date, DailyReportService $service): array
    {
        $summary = $service->generateDailyReport($date);

        // Prepare summary data as rows for table display
        $rows = [
            [
                'category' => 'Income',
                'sub_category' => 'Student Payments',
                'amount' => $summary['payment_total'] ?? 0,
                'type' => 'income',
            ],
            [
                'category' => 'Income',
                'sub_category' => 'Admission Fees',
                'amount' => $summary['admission_total'] ?? 0,
                'type' => 'income',
            ],
            [
                'category' => 'Income',
                'sub_category' => 'Extra Income',
                'amount' => $summary['extra_income_total'] ?? 0,
                'type' => 'income',
            ],
            [
                'category' => 'Expense',
                'sub_category' => 'Teacher Payments',
                'amount' => $summary['teacher_expense_total'] ?? 0,
                'type' => 'expense',
            ],
            [
                'category' => 'Expense',
                'sub_category' => 'Organizer Payments',
                'amount' => $summary['organizer_expense_total'] ?? 0,
                'type' => 'expense',
            ],
            [
                'category' => 'Expense',
                'sub_category' => 'Institute Expenses',
                'amount' => $summary['instituteExpencesTotal'] ?? 0,
                'type' => 'expense',
            ],
            [
                'category' => 'Net Total',
                'sub_category' => 'Net Balance',
                'amount' => $summary['net_total'] ?? 0,
                'type' => 'net',
            ],
        ];

        return [
            'type' => 'summary',
            'title' => 'Daily Financial Summary Report',
            'date' => $date,
            'summary_label' => 'Net Balance',
            'summary_value' => $summary['net_total'] ?? 0,
            'count' => count($rows),
            'headings' => [
                'Category',
                'Sub Category',
                'Amount (Rs.)',
            ],
            'columns' => [
                'category',
                'sub_category',
                'amount',
            ],
            'rows' => $rows,
            'summary_data' => $summary, // Full summary data for additional details
        ];
    }

    protected function buildStudentReport(string $date, DailyReportService $service): array
    {
        $rows = collect($service->studentPaymentReport($date))->values()->all();

        return [
            'type' => 'student',
            'title' => 'Student Daily Report',
            'date' => $date,
            'summary_label' => 'Total Amount',
            'summary_value' => collect($rows)->sum('amount'),
            'count' => count($rows),
            'headings' => [
                'Payment ID',
                'Paid At',
                'Amount',
                'Discount Amount',
                'Student Code',
                'Student Name',
                'Guardian Mobile',
                'Custom Fee',
                'Discount %',
                'Final Fee',
                'Payment Status',
                'Class Name',
                'Grade Name',
                'Category Name',
                'Collected By',
            ],
            'columns' => [
                'payment_id',
                'paid_at',
                'amount',
                'discount_amount',
                'student_code',
                'student_name',
                'guardian_mobile',
                'custom_fee',
                'discount_percentage',
                'final_fee',
                'payment_status',
                'class_name',
                'grade_name',
                'category_name',
                'collected_by',
            ],
            'rows' => $rows,
        ];
    }

    protected function buildTeacherReport(string $date, DailyReportService $service): array
    {
        $rows = collect($service->teacherCollectionReport($date))->values()->all();

        return [
            'type' => 'teacher',
            'title' => 'Teacher Daily Report',
            'date' => $date,
            'summary_label' => 'Total Teacher Amount',
            'summary_value' => collect($rows)->sum('teacher_amount'),
            'count' => count($rows),
            'headings' => [
                'Payment Date',
                'Teacher Name',
                'Student Code',
                'Student Name',
                'Guardian Mobile',
                'Class Name',
                'Grade Name',
                'Payment Amount',
                'Teacher Amount',
            ],
            'columns' => [
                'payment_date',
                'teacher_name',
                'student_code',
                'student_name',
                'guardian_mobile',
                'class_name',
                'grade_name',
                'payment_amount',
                'teacher_amount',
            ],
            'rows' => $rows,
        ];
    }

    protected function buildInstitutionReport(string $date, DailyReportService $service): array
    {
        $report = $service->institutionDailyReport($date);
        $rows = $report['details'] ?? [];

        return [
            'type' => 'institution',
            'title' => 'Institution Daily Report',
            'date' => $date,
            'summary_label' => 'Total Institution Amount',
            'summary_value' => $report['total_institution_amount'] ?? 0,
            'count' => $report['count'] ?? count($rows),
            'headings' => [
                'Payment ID',
                'Payment Date',
                'Student Code',
                'Student Name',
                'Guardian Mobile',
                'Class Name',
                'Grade Name',
                'Payment Amount',
                'Institution Amount',
            ],
            'columns' => [
                'payment_id',
                'payment_date',
                'student_code',
                'student_name',
                'guardian_mobile',
                'class_name',
                'grade_name',
                'payment_amount',
                'institution_amount',
            ],
            'rows' => $rows,
        ];
    }

    protected function buildOrganizerReport(string $date, DailyReportService $service): array
    {
        $report = $service->organizerDailyReport($date);
        $rows = $report['details'] ?? [];

        return [
            'type' => 'organizer',
            'title' => 'Organizer Daily Report',
            'date' => $date,
            'summary_label' => 'Total Organizer Amount',
            'summary_value' => $report['total_organizer_amount'] ?? 0,
            'count' => $report['count'] ?? count($rows),
            'headings' => [
                'Payment ID',
                'Payment Date',
                'Organizer ID',
                'Organizer Name',
                'Organizer Mobile',
                'Student Code',
                'Student Name',
                'Guardian Mobile',
                'Class Name',
                'Grade Name',
                'Payment Amount',
                'Organizer Amount',
            ],
            'columns' => [
                'payment_id',
                'payment_date',
                'organizer_id',
                'organizer_name',
                'organizer_mobile',
                'student_code',
                'student_name',
                'guardian_mobile',
                'class_name',
                'grade_name',
                'payment_amount',
                'organizer_amount',
            ],
            'rows' => $rows,
        ];
    }

    protected function buildAdmissionReport(string $date, DailyReportService $service): array
    {
        $rows = collect($service->todayAdmission($date))->values()->all();

        return [
            'type' => 'admission',
            'title' => 'Admission Daily Report',
            'date' => $date,
            'summary_label' => 'Total Amount',
            'summary_value' => collect($rows)->sum('amount'),
            'count' => count($rows),
            'headings' => [
                'Payment ID',
                'Created At',
                'Student Code',
                'Student Name',
                'Guardian Mobile',
                'Amount',
                'Collected By',
            ],
            'columns' => [
                'payment_id',
                'created_at',
                'student_code',
                'student_name',
                'guardian_mobile',
                'amount',
                'collected_by',
            ],
            'rows' => $rows,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Download PDF for Summary Report
    |--------------------------------------------------------------------------
    */
    public function downloadSummaryPdf(Request $request, DailyReportService $service)
    {
        $date = $request->date
            ? Carbon::parse($request->date)->toDateString()
            : Carbon::today()->toDateString();

        $report = $this->buildSummaryReport($date, $service);

        $pdf = Pdf::loadView('admin.daily-report.pdf-summary', $report);

        return $pdf->download(Str::slug($report['title']) . '-' . $report['date'] . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Download Excel for Summary Report
    |--------------------------------------------------------------------------
    */
    public function downloadSummaryExcel(Request $request, DailyReportService $service)
    {
        $date = $request->date
            ? Carbon::parse($request->date)->toDateString()
            : Carbon::today()->toDateString();

        $report = $this->buildSummaryReport($date, $service);

        return Excel::download(
            new DailyReportExport(
                title: $report['title'],
                headings: $report['headings'],
                columns: $report['columns'],
                rows: $report['rows']
            ),
            Str::slug($report['title']) . '-' . $report['date'] . '.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Download PDF for any report type
    |--------------------------------------------------------------------------
    */
    public function downloadPdf(Request $request, string $type, DailyReportService $service)
    {
        $report = $this->resolveReport($type, $request->date, $service);

        $report['headings'] = $this->pdfHeadings($type);
        $report['columns'] = $this->pdfColumns($type);
        $report['rows'] = $this->pdfRows($report['rows'], $report['columns']);

        $pdf = Pdf::loadView('admin.daily-report.pdf', $report);

        return $pdf->download(Str::slug($report['title']) . '-' . $report['date'] . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Download Excel for any report type
    |--------------------------------------------------------------------------
    */
    public function downloadExcel(Request $request, string $type, DailyReportService $service)
    {
        $report = $this->resolveReport($type, $request->date, $service);

        return Excel::download(
            new DailyReportExport(
                title: $report['title'],
                headings: $report['headings'],
                columns: $report['columns'],
                rows: $report['rows']
            ),
            Str::slug($report['title']) . '-' . $report['date'] . '.xlsx'
        );
    }

    public function teacherWithStudentPayments($teacherId, $date, DailyReportService $service)
    {
        $date = $date
            ? Carbon::parse($date)->toDateString()
            : Carbon::today()->toDateString();

        $rows = collect($service->teacherWithStudentPayments((int) $teacherId, $date))->values()->all();

        return [
            'type' => 'teacher_students',
            'title' => 'Teacher Student Payments Report',
            'date' => $date,
            'teacher_id' => (int) $teacherId,
            'summary_label' => 'Total Paid',
            'summary_value' => collect($rows)->sum('class_total_paid'),
            'count' => collect($rows)->flatMap(function ($class) {
                return $class['categories'] ?? [];
            })->flatMap(function ($category) {
                return $category['students'] ?? [];
            })->count(),
            'rows' => $rows,
        ];
    }

    protected function pdfColumns(string $type): array
    {
        return match ($type) {
            'student' => [
                'payment_id',
                'paid_at',
                'student_code',
                'student_name',
                'amount',
                'payment_status',
                'collected_by',
            ],
            'teacher' => [
                'payment_date',
                'teacher_name',
                'student_name',
                'teacher_amount',
            ],
            'institution' => [
                'payment_id',
                'payment_date',
                'student_name',
                'institution_amount',
            ],
            'organizer' => [
                'payment_id',
                'payment_date',
                'organizer_name',
                'student_name',
                'organizer_amount',
            ],
            'admission' => [
                'payment_id',
                'created_at',
                'student_name',
                'amount',
                'collected_by',
            ],
            'summary' => [
                'category',
                'sub_category',
                'amount',
            ],
            default => [],
        };
    }

    protected function pdfHeadings(string $type): array
    {
        return match ($type) {
            'student' => ['Payment ID', 'Paid At', 'Qr Code', 'Student Name', 'Amount', 'Status', 'Collected By'],
            'teacher' => ['Payment Date', 'Teacher Name', 'Student Name', 'Teacher Amount'],
            'institution' => ['Payment ID', 'Payment Date', 'Student Name', 'Institution Amount'],
            'organizer' => ['Payment ID', 'Payment Date', 'Organizer Name', 'Student Name', 'Organizer Amount'],
            'admission' => ['Payment ID', 'Created At', 'Student Name', 'Amount', 'Collected By'],
            'summary' => ['Category', 'Sub Category', 'Amount (Rs.)'],
            default => [],
        };
    }

    protected function pdfRows(array $rows, array $columns): array
    {
        return collect($rows)->map(function ($row) use ($columns) {
            return collect($columns)->mapWithKeys(function ($column) use ($row) {
                return [$column => data_get($row, $column)];
            })->all();
        })->values()->all();
    }
}
