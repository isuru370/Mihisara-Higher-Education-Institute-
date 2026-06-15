<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DailyReport\DailyReportExport;
use App\Exports\Teacher\TeacherExpenseExport;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherPayment;
use App\Services\DailyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class TeacherReportController extends Controller
{

    public function index(Request $request, DailyReportService $service)
    {
        $date = $this->normalizeDate($request->query('date'));
        $teacherId = $request->query('teacher_id');

        $teachers = Teacher::query()
            ->select(['id', 'custom_id', 'initials'])
            ->orderBy('initials')
            ->get();

        $data = [];
        if ($teacherId) {
            $data = $service->teacherWithStudentPayments((int) $teacherId, $date);
        }

        return view('admin.teacher-report.index', [
            'date' => $date,
            'teacherId' => $teacherId,
            'teachers' => $teachers,
            'data' => $data,
        ]);
    }
    public function teacherWithStudentPayments(Request $request, int $teacherId, DailyReportService $service): JsonResponse
    {
        $date = $this->normalizeDate($request->query('date'));
        $report = $service->teacherWithStudentPayments($teacherId, $date);

        return response()->json([
            'success' => true,
            'message' => 'Teacher student payments report fetched successfully.',
            'date' => $date,
            'teacher_id' => $teacherId,
            'data' => $report,
        ]);
    }

    public function downloadTeacherWithStudentPaymentsPdf(Request $request, DailyReportService $service)
    {
        $date = $this->normalizeDate($request->query('date'));
        $teacherId = (int) $request->query('teacher_id');

        abort_if(!$teacherId, 422, 'teacher_id is required');

        $nestedReport = $service->teacherWithStudentPayments($teacherId, $date);
        $rows = $this->flattenTeacherStudentPaymentsRows($nestedReport);

        $report = [
            'type' => 'teacher_students',
            'title' => 'Teacher Student Payments Report',
            'date' => $date,
            'teacher_id' => $teacherId,
            'summary_label' => 'Total Paid',
            'summary_value' => collect($nestedReport)->sum('class_total_paid'),
            'count' => count($rows),
            'headings' => [
                'Class Name',
                'Grade Name',
                'Category Name',
                'Student Code',
                'Student Name',
                'Guardian Mobile',
                'Payment ID',
                'Paid At',
                'Amount',
                'Payment Method',
                'Receipt Number',
                'Reference Number',
                'Note',
            ],
            'columns' => [
                'class_name',
                'grade_name',
                'category_name',
                'student_code',
                'student_name',
                'guardian_mobile',
                'payment_id',
                'paid_at',
                'amount',
                'payment_method',
                'receipt_number',
                'reference_number',
                'note',
            ],
            'rows' => $rows,
            'nested_report' => $nestedReport,
        ];

        $pdf = Pdf::loadView('admin.teacher-report.pdf', $report);

        return $pdf->download(Str::slug($report['title']) . '-' . $report['date'] . '.pdf');
    }

    public function downloadTeacherWithStudentPaymentsExcel(Request $request, DailyReportService $service)
    {
        $date = $this->normalizeDate($request->query('date'));
        $teacherId = (int) $request->query('teacher_id');

        abort_if(!$teacherId, 422, 'teacher_id is required');

        $nestedReport = $service->teacherWithStudentPayments($teacherId, $date);
        $rows = $this->flattenTeacherStudentPaymentsRows($nestedReport);

        $title = 'Teacher Student Payments Report';

        return Excel::download(
            new DailyReportExport(
                title: $title,
                headings: [
                    'Class Name',
                    'Grade Name',
                    'Category Name',
                    'Student Code',
                    'Student Name',
                    'Guardian Mobile',
                    'Payment ID',
                    'Paid At',
                    'Amount',
                    'Payment Method',
                    'Receipt Number',
                    'Reference Number',
                    'Note',
                ],
                columns: [
                    'class_name',
                    'grade_name',
                    'category_name',
                    'student_code',
                    'student_name',
                    'guardian_mobile',
                    'payment_id',
                    'paid_at',
                    'amount',
                    'payment_method',
                    'receipt_number',
                    'reference_number',
                    'note',
                ],
                rows: $rows
            ),
            Str::slug($title) . '-' . $date . '.xlsx'
        );
    }

    private function normalizeDate(?string $date): string
    {
        if (empty($date)) {
            return Carbon::today()->toDateString();
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            abort(422, 'Invalid date format.');
        }
    }

    private function flattenTeacherStudentPaymentsRows(array $nestedReport): array
    {
        $rows = [];

        foreach ($nestedReport as $class) {
            foreach ($class['categories'] ?? [] as $category) {
                foreach ($category['students'] ?? [] as $student) {
                    foreach ($student['payments'] ?? [] as $payment) {
                        $rows[] = [
                            'class_name' => $class['class_name'] ?? null,
                            'grade_name' => $class['grade_name'] ?? null,
                            'category_name' => $category['category_name'] ?? null,
                            'student_code' => $student['student_code'] ?? null,
                            'student_name' => $student['student_name'] ?? null,
                            'guardian_mobile' => $student['guardian_mobile'] ?? null,
                            'payment_id' => $payment['payment_id'] ?? null,
                            'paid_at' => $payment['paid_at'] ?? null,
                            'amount' => $payment['amount'] ?? 0,
                            'payment_method' => $payment['payment_method'] ?? null,
                            'receipt_number' => $payment['receipt_number'] ?? null,
                            'reference_number' => $payment['reference_number'] ?? null,
                            'note' => $payment['note'] ?? null,
                        ];
                    }
                }
            }
        }

        return $rows;
    }

    public function teacherExpenseReport(Request $request): JsonResponse
    {
        try {
            $data = $this->buildTeacherExpenseReport($request);

            return response()->json([
                'success' => true,
                'message' => 'Teacher expense report fetched successfully.',
                'date' => $data['date'],
                'teacher_id' => $data['teacherId'],
                'data' => $data['report'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Teacher expense report error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching teacher expense report.',
            ], 500);
        }
    }

    public function teacherExpenseReportExcel(Request $request)
    {
        try {
            $data = $this->buildTeacherExpenseReport($request);

            return Excel::download(
                new TeacherExpenseExport($data['payments'], $data['teacherId'], $data['year'], $data['month']),
                "teacher_expense_{$data['teacherId']}_{$data['year']}_{$data['month']}.xlsx"
            );
        } catch (\Throwable $e) {
            Log::error('Teacher expense excel error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while downloading Excel report.',
            ], 500);
        }
    }

    public function teacherExpenseReportPdf(Request $request)
{
    try {
        $data = $this->buildTeacherExpenseReport($request);

        if (!view()->exists('admin.pdf.teacher.teacher_expense')) {
            throw new \Exception('PDF view not found: admin.pdf.teacher.teacher_expense');
        }

        $pdf = Pdf::loadView('admin.pdf.teacher.teacher_expense', [
            'payments' => $data['report'],
            'date' => $data['date'],
            'teacher_id' => $data['teacherId'],
            'teacher_name' => $data['teacherName'],
            'year' => $data['year'],
            'month' => $data['month'],
        ]);

        return $pdf->download("teacher_expense_{$data['teacherId']}_{$data['year']}_{$data['month']}.pdf");
    } catch (\Throwable $e) {
        Log::error('Teacher expense pdf error', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    private function buildTeacherExpenseReport(Request $request): array
    {
        $year = $request->query('year', Carbon::now()->year);
        $month = $request->query('month', Carbon::now()->month);
        $teacherId = $request->query('teacher_id');

        if (!$teacherId) {
            throw new \InvalidArgumentException('teacher_id is required');
        }

        $teacher = Teacher::select('id', 'custom_id', 'initials')
            ->find($teacherId);

        if (!$teacher) {
            throw new \Exception('Teacher not found');
        }

        $teacherName = trim(($teacher->custom_id ?? '') . ' - ' . ($teacher->initials ?? ''));

        $date = Carbon::createFromDate($year, $month, 1)->toDateString();

        $payments = TeacherPayment::with('createdBy:id,name')
            ->where('teacher_id', $teacherId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->whereIn('status', ['paid', 'cancelled'])
            ->get();

        $report = $payments->map(function ($payment) use ($teacherName) {
            return [
                'payment_id' => $payment->id,
                'payment_type' => $payment->payment_type,
                'amount' => $payment->amount,
                'payment_date' => optional($payment->payment_date)->toDateString(),
                'reason' => $payment->reason,
                'note' => $payment->note,
                'created_by' => optional($payment->createdBy)->name,
                'status' => $payment->status,
                'teacher_name' => $teacherName,
            ];
        });

        return [
            'year' => $year,
            'month' => $month,
            'date' => $date,
            'teacherId' => $teacherId,
            'teacherName' => $teacherName,
            'payments' => $payments,
            'report' => $report,
        ];
    }
}
