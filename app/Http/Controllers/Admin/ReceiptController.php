<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\AdmissionPayment;
use App\Models\ExtraIncome;
use App\Exports\Receipts\ReceiptsExport;
use App\Models\ActivityLog;
use App\Models\StudentClassEnrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $receipts = $this->getReceipts($request);

        $totalAmount = $receipts->sum('amount');
        $totalReceipts = $receipts->count();


        return view(
            'admin.receipts.index',
            compact(
                'receipts',
                'totalAmount',
                'totalReceipts'
            )
        );
    }

    public function exportExcel(Request $request)
    {
        $receipts = $this->getReceipts($request);

        return Excel::download(
            new ReceiptsExport($receipts),
            'receipts.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $receipts = $this->getReceipts($request);

        $totalAmount = $receipts
            ->where('status', 'Active')
            ->sum('amount');

        $totalReceipts = $receipts->count();

        $activeReceipts = $receipts->where('status', 'Active')->count();
        $deletedReceipts = $receipts->where('status', 'Deleted')->count();

        $pdf = Pdf::loadView(
            'admin.pdf.receipts.receipts_pdf',
            compact(
                'receipts',
                'totalAmount',
                'totalReceipts',
                'activeReceipts',
                'deletedReceipts'
            )
        );

        return $pdf->download('receipts.pdf');
    }

    private function getReceipts(Request $request)
    {
        $payments = Payment::withTrashed()
            ->with([
                'student:id,custom_id,full_name,initial_name,permanent_qr_active,temporary_qr_code', // permanent_qr_active සහ temporary_qr_code add කළා

                'enrollment:id,student_class_id,class_category_fee_id',

                'enrollment.classCategoryFee:id,class_category_id',

                'enrollment.classCategoryFee.category:id,category_name',

                'enrollment.studentClass:id,class_name,grade_id,teacher_id',

                'enrollment.studentClass.grade:id,grade_name',

                'enrollment.studentClass.teacher:id,full_name',
            ])
            ->select([
                'id',
                'student_id',
                'student_class_enrollment_id',
                'receipt_number',
                'amount',
                'payment_month',
                'paid_at',
                'created_at',
                'deleted_at',
            ])
            ->get()
            ->map(function ($item) {
                // student ID එක determine කරනවා
                $studentId = 'N/A';
                if ($item->student) {
                    if ($item->student->permanent_qr_active) {
                        $studentId = $item->student->custom_id;
                    } else {
                        $studentId = $item->student->temporary_qr_code;
                    }
                }

                return [
                    'id' => $item->id,

                    'receipt_number' => $item->receipt_number,

                    'type' => 'Student Payment',

                    'student_id' => $studentId,

                    'student_name' => $item->student?->initial_name ?? 'N/A',

                    'grade' => $item->enrollment?->studentClass?->grade?->grade_name,

                    'category' => $item->enrollment?->classCategoryFee?->category?->category_name,

                    'class_name' => $item->enrollment?->studentClass?->class_name,

                    'teacher_name' => $item->enrollment?->studentClass?->teacher?->full_name,

                    'payment_month' => $item->payment_month ? Carbon::parse($item->payment_month) : null,

                    'paid_at' => $item->paid_at ? Carbon::parse($item->paid_at) : null,

                    'amount' => $item->amount,

                    'date' => $item->created_at,

                    'status' => $item->deleted_at ? 'Deleted' : 'Active',

                    'url' => route(
                        'admin.students-payments.show',
                        $item->id
                    ),
                ];
            });

        $admissions = AdmissionPayment::withTrashed()
            ->with([
                'student:id,custom_id,full_name,initial_name,permanent_qr_active,temporary_qr_code', // permanent_qr_active සහ temporary_qr_code add කළා
            ])
            ->select([
                'id',
                'student_id',
                'receipt_number',
                'amount',
                'paid_at',
                'created_at',
                'deleted_at',
            ])
            ->get()
            ->map(function ($item) {
                // student ID එක determine කරනවා
                $studentId = 'N/A';
                if ($item->student) {
                    if ($item->student->permanent_qr_active) {
                        $studentId = $item->student->custom_id;
                    } else {
                        $studentId = $item->student->temporary_qr_code;
                    }
                }

                return [
                    'id' => $item->id,

                    'receipt_number' => $item->receipt_number,

                    'type' => 'Admission Payment',

                    'student_id' => $studentId,

                    'student_name' => $item->student?->initial_name ?? 'N/A',

                    'grade' => 'N/A',

                    'category' => 'N/A',

                    'class_name' => 'N/A',

                    'teacher_name' => 'N/A',

                    'payment_month' => null,

                    'paid_at' => $item->paid_at ? Carbon::parse($item->paid_at) : null,

                    'amount' => $item->amount,

                    'date' => $item->created_at,

                    'status' => $item->deleted_at ? 'Deleted' : 'Active',

                    'url' => route(
                        'admin.admission-payments.show',
                        $item->id
                    ),
                ];
            });

        $extraIncomes = ExtraIncome::withTrashed()
            ->select([
                'id',
                'receipt_number',
                'amount',
                'created_at',
                'deleted_at',
            ])
            ->get()
            ->map(function ($item) {

                return [
                    'id' => $item->id,

                    'receipt_number' => $item->receipt_number,

                    'type' => 'Extra Income',

                    'student_id' => 'N/A',

                    'student_name' => 'N/A',

                    'grade' => 'N/A',

                    'category' => 'N/A',

                    'class_name' => 'N/A',

                    'teacher_name' => 'N/A',

                    'payment_month' => null,

                    'paid_at' => null,

                    'amount' => $item->amount,

                    'date' => $item->created_at,

                    'status' => $item->deleted_at ? 'Deleted' : 'Active',

                    'url' => route(
                        'admin.extra-incomes.show',
                        $item->id
                    ),
                ];
            });

        $deletedLogs = ActivityLog::query()
            ->where('table_name', 'payments')
            ->where('action', 'force_deleted')
            ->get();

        $enrollmentIds = $deletedLogs
            ->pluck('old_values.payment.student_class_enrollment_id')
            ->filter()
            ->unique()
            ->values();

        $enrollments = StudentClassEnrollment::withTrashed()
            ->with([
                'student:id,custom_id,full_name,initial_name,permanent_qr_active,temporary_qr_code', // permanent_qr_active සහ temporary_qr_code add කළා
                'studentClass:id,class_name,grade_id,teacher_id',
                'studentClass.grade:id,grade_name',
                'studentClass.teacher:id,full_name',
                'classCategoryFee:id,class_category_id',
                'classCategoryFee.category:id,category_name',
            ])
            ->whereIn('id', $enrollmentIds)
            ->get()
            ->keyBy('id');

        $deletedPayments = $deletedLogs->map(function ($log) use ($enrollments) {

            $payment = data_get($log->old_values, 'payment', []);

            $enrollment = $enrollments->get(
                $payment['student_class_enrollment_id'] ?? null
            );

            // student ID එක determine කරනවා
            $studentId = 'N/A';
            if ($enrollment?->student) {
                if ($enrollment->student->permanent_qr_active) {
                    $studentId = $enrollment->student->custom_id;
                } else {
                    $studentId = $enrollment->student->temporary_qr_code;
                }
            }

            return [

                'id' => $log->record_id,

                'receipt_number' => $payment['receipt_number'] ?? 'N/A',

                'type' => 'Student Payment',

                'student_id' => $studentId,

                'student_name' => $enrollment?->student?->initial_name ?? 'N/A',

                'grade' => $enrollment?->studentClass?->grade?->grade_name ?? 'N/A',

                'category' => $enrollment?->classCategoryFee?->category?->category_name ?? 'N/A',

                'class_name' => $enrollment?->studentClass?->class_name ?? 'N/A',

                'teacher_name' => $enrollment?->studentClass?->teacher?->full_name ?? 'N/A',

                'payment_month' => !empty($payment['payment_month'])
                    ? Carbon::parse($payment['payment_month'])
                    : null,

                'paid_at' => !empty($payment['paid_at'])
                    ? Carbon::parse($payment['paid_at'])
                    : null,

                'amount' => $payment['amount'] ?? 0,

                'date' => $log->created_at,

                'status' => 'Force Deleted',

                'url' => null,
            ];
        });

        $receipts = $payments
            ->merge($admissions)
            ->merge($extraIncomes)
            ->merge($deletedPayments);

        if ($request->filled('receipt_number')) {
            $receipts = $receipts->filter(function ($item) use ($request) {
                return str_contains(
                    strtolower($item['receipt_number'] ?? ''),
                    strtolower($request->receipt_number)
                );
            });
        }

        if ($request->filled('type')) {
            $receipts = $receipts->where(
                'type',
                $request->type
            );
        }

        if ($request->filled('from_date')) {
            $receipts = $receipts->filter(function ($item) use ($request) {
                return $item['date']->format('Y-m-d')
                    >= $request->from_date;
            });
        }

        if ($request->filled('to_date')) {
            $receipts = $receipts->filter(function ($item) use ($request) {
                return $item['date']->format('Y-m-d')
                    <= $request->to_date;
            });
        }

        return $receipts
            ->sortByDesc('date')
            ->values();
    }
}