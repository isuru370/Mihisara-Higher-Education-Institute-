<?php

namespace App\Services;

use App\Models\AdmissionPayment;
use App\Models\ExtraIncome;
use App\Models\InstitutePayment;
use App\Models\OrganizerPayment;
use App\Models\Payment;
use App\Models\PaymentSplitSnapshot;
use App\Models\StudentClass;
use App\Models\StudentClassEnrollment;
use App\Models\TeacherPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MonthlyReportService
{
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function parseDate($date): string
    {
        return $date
            ? Carbon::parse($date)->toDateString()
            : Carbon::today()->toDateString();
    }

    protected function normalizeMonthRange(?string $month = null, ?int $year = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ? (int) $month : now()->month;

        if ($month < 1 || $month > 12) {
            throw ValidationException::withMessages([
                'month' => 'Invalid month. Use 1-12.',
            ]);
        }

        try {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();

            return [$start, $end];
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'month' => 'Invalid month or year.',
            ]);
        }
    }

    protected function normalizeDateRange(string $fromDate, string $toDate): array
    {
        try {
            $start = Carbon::parse($fromDate)->startOfDay();
            $end = Carbon::parse($toDate)->endOfDay();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'date' => 'Invalid date format.',
            ]);
        }

        if ($start->greaterThan($end)) {
            throw ValidationException::withMessages([
                'date' => 'From date must be before to date.',
            ]);
        }

        return [$start, $end];
    }

    protected function buildSummaryByRange(Carbon $start, Carbon $end): array
    {
        $paymentTotal = Payment::whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->sum('amount');

        $admissionTotal = AdmissionPayment::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('amount');

        $extraIncomeTotal = ExtraIncome::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('amount');

        $teacherPaymentTotal = TeacherPayment::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('amount');

        $instituteExpensesTotal = InstitutePayment::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('amount');

        $organizerExpenseTotal = OrganizerPayment::whereBetween('created_at', [$start, $end])
            ->whereIn('payment_type', ['advance', 'deduction', 'other'])
            ->where('status', 'paid')
            ->sum('amount');

        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'payment_total' => (float) $paymentTotal,
            'admission_total' => (float) $admissionTotal,
            'extra_income_total' => (float) $extraIncomeTotal,
            'teacher_expense_total' => (float) $teacherPaymentTotal,
            'organizer_expense_total' => (float) $organizerExpenseTotal,
            'instituteExpencesTotal' => (float) $instituteExpensesTotal,
            'net_total' => (
                $paymentTotal +
                $admissionTotal +
                $extraIncomeTotal
            ) - (
                $teacherPaymentTotal +
                $organizerExpenseTotal +
                $instituteExpensesTotal
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Main Monthly Summary Report
    |--------------------------------------------------------------------------
    */
    public function generateMonthlyReport(?string $month = null, ?int $year = null): array
    {
        [$start, $end] = $this->normalizeMonthRange($month, $year);

        return [
            'month' => $start->format('Y-m'),
            'year' => $start->year,
            'summary' => $this->buildSummaryByRange($start, $end),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Date Range Summary Report
    |--------------------------------------------------------------------------
    */
    public function generateDateRangeReport(string $fromDate, string $toDate): array
    {
        [$start, $end] = $this->normalizeDateRange($fromDate, $toDate);

        return [
            'summary' => $this->buildSummaryByRange($start, $end),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get Students by Day of Week
    |--------------------------------------------------------------------------
    */
    public function getStudentsByDayOfWeek($date = null, $dayOfWeek = null)
    {
        $targetDayOfWeek = $dayOfWeek;

        if ($date && !$dayOfWeek) {
            $targetDayOfWeek = Carbon::parse($date)->format('l');
        }

        if (!$targetDayOfWeek) {
            $targetDayOfWeek = Carbon::today()->format('l');
        }

        $enrollments = StudentClassEnrollment::with([
            'student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile,mobile,email,address',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'studentClass.classSchedule' => function ($query) use ($targetDayOfWeek) {
                $query->where('day_of_week', $targetDayOfWeek)
                    ->where('is_active', true);
            },
            'studentClass.classSchedule.classTime',
        ])
            ->where('is_active', true)
            ->whereHas('studentClass.classSchedule', function ($query) use ($targetDayOfWeek) {
                $query->where('day_of_week', $targetDayOfWeek)
                    ->where('is_active', true);
            })
            ->get();

        $filteredEnrollments = $enrollments->filter(function ($enrollment) {
            return $enrollment->studentClass->classSchedule->isNotEmpty();
        });

        $groupedStudents = [];

        foreach ($filteredEnrollments as $enrollment) {
            $student = $enrollment->student;
            $studentClass = $enrollment->studentClass;

            foreach ($studentClass->classSchedule as $schedule) {
                $classTime = $schedule->classTime;
                $timeSlot = $classTime ? $classTime->start_time . ' - ' . $classTime->end_time : 'Time not set';

                $key = $studentClass->id . '_' . $schedule->id;

                if (!isset($groupedStudents[$key])) {
                    $groupedStudents[$key] = [
                        'class_id' => $studentClass->id,
                        'class_name' => $studentClass->class_name,
                        'grade_name' => $studentClass->grade->grade_name ?? '-',
                        'day_of_week' => $targetDayOfWeek,
                        'start_time' => $classTime?->start_time,
                        'end_time' => $classTime?->end_time,
                        'time_slot' => $timeSlot,
                        'schedule_id' => $schedule->id,
                        'students' => []
                    ];
                }

                $groupedStudents[$key]['students'][] = [
                    'student_id' => $student->id,
                    'student_code' => $student->permanent_qr_active
                        ? $student->custom_id
                        : $student->temporary_qr_code,
                    'student_name' => $student->initial_name,
                    'guardian_mobile' => $student->guardian_mobile,
                    'mobile' => $student->mobile,
                    'email' => $student->email,
                    'address' => $student->address,
                    'enrollment_id' => $enrollment->id,
                    'payment_status' => $enrollment->payment_status,
                    'balance' => $enrollment->balance,
                ];
            }
        }

        $groupedStudents = array_values($groupedStudents);

        usort($groupedStudents, function ($a, $b) {
            return strcmp($a['start_time'] ?? '', $b['start_time'] ?? '');
        });

        $totalStudents = collect($groupedStudents)->sum(function ($group) {
            return count($group['students']);
        });

        return [
            'selected_date' => $date ?? Carbon::today()->toDateString(),
            'selected_day' => $targetDayOfWeek,
            'total_classes' => count($groupedStudents),
            'total_students' => $totalStudents,
            'classes' => $groupedStudents,
        ];
    }

    public function getStudentsByDate($date = null)
    {
        return $this->getStudentsByDayOfWeek($date, null);
    }

    public function getStudentsForToday()
    {
        return $this->getStudentsByDayOfWeek(Carbon::today()->toDateString(), null);
    }

    public function getStudentsForTomorrow()
    {
        return $this->getStudentsByDayOfWeek(Carbon::tomorrow()->toDateString(), null);
    }

    public function getStudentsByDayName($dayName)
    {
        return $this->getStudentsByDayOfWeek(null, ucfirst(strtolower($dayName)));
    }

    /*
    |--------------------------------------------------------------------------
    | Student Payment Report
    |--------------------------------------------------------------------------
    */
    public function studentPaymentReportByMonth(?string $month = null, ?int $year = null)
    {
        [$start, $end] = $this->normalizeMonthRange($month, $year);

        return Payment::with([
            'student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
            'collectedBy:id,name',
            'enrollment:id,student_id,student_class_id,class_category_fee_id,custom_fee,discount_percentage,is_free_card',
            'enrollment.studentClass:id,class_name,grade_id',
            'enrollment.studentClass.grade:id,grade_name',
            'enrollment.classCategoryFee:id,student_class_id,class_category_id,fee',
            'enrollment.classCategoryFee.category:id,category_name',
        ])
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->get()
            ->map(function ($payment) {
                $student = $payment->student;
                $enrollment = $payment->enrollment;
                $studentClass = $enrollment?->studentClass;
                $categoryFee = $enrollment?->classCategoryFee;

                return [
                    'payment_id' => $payment->id,
                    'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'class_name' => $studentClass?->class_name,
                    'grade_name' => $studentClass?->grade?->grade_name,
                    'category_name' => $categoryFee?->category?->category_name,
                    'amount' => $payment->amount,
                    'discount_amount' => $payment->discount_amount,
                    'custom_fee' => $enrollment?->custom_fee,
                    'discount_percentage' => $enrollment?->discount_percentage,
                    'final_fee' => $enrollment?->final_fee,
                    'balance' => $enrollment?->balance,
                    'payment_status' => $enrollment?->payment_status,
                    'collected_by' => $payment->collectedBy?->name,
                ];
            });
    }

    public function studentPaymentReportByRange(string $fromDate, string $toDate)
    {
        [$start, $end] = $this->normalizeDateRange($fromDate, $toDate);

        return Payment::with([
            'student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
            'collectedBy:id,name',
            'enrollment:id,student_id,student_class_id,class_category_fee_id,custom_fee,discount_percentage,is_free_card',
            'enrollment.studentClass:id,class_name,grade_id',
            'enrollment.studentClass.grade:id,grade_name',
            'enrollment.classCategoryFee:id,student_class_id,class_category_id,fee',
            'enrollment.classCategoryFee.category:id,category_name',
        ])
            ->whereBetween('paid_at', [$start, $end])
            ->where('status', 'completed')
            ->get()
            ->map(function ($payment) {
                $student = $payment->student;
                $enrollment = $payment->enrollment;
                $studentClass = $enrollment?->studentClass;
                $categoryFee = $enrollment?->classCategoryFee;

                return [
                    'payment_id' => $payment->id,
                    'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'class_name' => $studentClass?->class_name,
                    'grade_name' => $studentClass?->grade?->grade_name,
                    'category_name' => $categoryFee?->category?->category_name,
                    'amount' => $payment->amount,
                    'discount_amount' => $payment->discount_amount,
                    'custom_fee' => $enrollment?->custom_fee,
                    'discount_percentage' => $enrollment?->discount_percentage,
                    'final_fee' => $enrollment?->final_fee,
                    'balance' => $enrollment?->balance,
                    'payment_status' => $enrollment?->payment_status,
                    'collected_by' => $payment->collectedBy?->name,
                ];
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Teacher Collection Report
    |--------------------------------------------------------------------------
    */
    public function teacherCollectionReportByMonth(?string $month = null, ?int $year = null)
    {
        [$start, $end] = $this->normalizeMonthRange($month, $year);

        return PaymentSplitSnapshot::with([
            'teacher:id,full_name,initials',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'enrollment.student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
        ])
            ->whereBetween('payment_date', [$start, $end])
            ->get()
            ->map(function ($row) {
                $student = $row->enrollment?->student;

                return [
                    'payment_date' => $row->payment_date?->format('Y-m-d H:i:s'),
                    'teacher_name' => $row->teacher?->full_name,
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'class_name' => $row->studentClass?->class_name,
                    'grade_name' => $row->studentClass?->grade?->grade_name,
                    'payment_amount' => $row->payment_amount,
                    'teacher_amount' => $row->teacher_amount,
                    'organizer_amount' => $row->organizer_amount,
                    'institution_amount' => $row->institution_amount,
                ];
            });
    }

    public function teacherCollectionReportByRange(string $fromDate, string $toDate)
    {
        [$start, $end] = $this->normalizeDateRange($fromDate, $toDate);

        return PaymentSplitSnapshot::with([
            'teacher:id,full_name,initials',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'enrollment.student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
        ])
            ->whereBetween('payment_date', [$start, $end])
            ->get()
            ->map(function ($row) {
                $student = $row->enrollment?->student;

                return [
                    'payment_date' => $row->payment_date?->format('Y-m-d H:i:s'),
                    'teacher_name' => $row->teacher?->full_name,
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'class_name' => $row->studentClass?->class_name,
                    'grade_name' => $row->studentClass?->grade?->grade_name,
                    'payment_amount' => $row->payment_amount,
                    'teacher_amount' => $row->teacher_amount,
                    'organizer_amount' => $row->organizer_amount,
                    'institution_amount' => $row->institution_amount,
                ];
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Institution Report
    |--------------------------------------------------------------------------
    */
    public function institutionMonthlyReport(?string $month = null, ?int $year = null)
    {
        [$start, $end] = $this->normalizeMonthRange($month, $year);

        $snapshots = PaymentSplitSnapshot::with([
            'payment:id,amount,paid_at,status',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'enrollment.student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
            'paymentConfig:id,student_class_id,teacher_percentage,organizer_percentage,institution_percentage',
        ])
            ->whereBetween('payment_date', [$start, $end])
            ->get();

        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'total_payment_amount' => $snapshots->sum('payment_amount'),
            'total_institution_amount' => $snapshots->sum('institution_amount'),
            'count' => $snapshots->count(),
            'details' => $snapshots->map(function ($row) {
                $student = $row->enrollment?->student;

                return [
                    'payment_id' => $row->payment_id,
                    'payment_date' => $row->payment_date?->format('Y-m-d H:i:s'),
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'class_name' => $row->studentClass?->class_name,
                    'grade_name' => $row->studentClass?->grade?->grade_name,
                    'payment_amount' => $row->payment_amount,
                    'institution_amount' => $row->institution_amount,
                ];
            }),
        ];
    }

    public function institutionReportByRange(string $fromDate, string $toDate)
    {
        [$start, $end] = $this->normalizeDateRange($fromDate, $toDate);

        $snapshots = PaymentSplitSnapshot::with([
            'payment:id,amount,paid_at,status',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'enrollment.student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
            'paymentConfig:id,student_class_id,teacher_percentage,organizer_percentage,institution_percentage',
        ])
            ->whereBetween('payment_date', [$start, $end])
            ->get();

        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'total_payment_amount' => $snapshots->sum('payment_amount'),
            'total_institution_amount' => $snapshots->sum('institution_amount'),
            'count' => $snapshots->count(),
            'details' => $snapshots->map(function ($row) {
                $student = $row->enrollment?->student;

                return [
                    'payment_id' => $row->payment_id,
                    'payment_date' => $row->payment_date?->format('Y-m-d H:i:s'),
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'class_name' => $row->studentClass?->class_name,
                    'grade_name' => $row->studentClass?->grade?->grade_name,
                    'payment_amount' => $row->payment_amount,
                    'institution_amount' => $row->institution_amount,
                ];
            }),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Organizer Report
    |--------------------------------------------------------------------------
    */
    public function organizerMonthlyReport(?string $month = null, ?int $year = null)
    {
        [$start, $end] = $this->normalizeMonthRange($month, $year);

        $snapshots = PaymentSplitSnapshot::with([
            'organizer:id,code,name,mobile,email',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'enrollment.student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
        ])
            ->whereBetween('payment_date', [$start, $end])
            ->whereNotNull('organizer_id')
            ->get();

        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'total_payment_amount' => $snapshots->sum('payment_amount'),
            'total_organizer_amount' => $snapshots->sum('organizer_amount'),
            'count' => $snapshots->count(),
            'details' => $snapshots->map(function ($row) {
                $student = $row->enrollment?->student;

                return [
                    'payment_id' => $row->payment_id,
                    'payment_date' => $row->payment_date?->format('Y-m-d H:i:s'),
                    'organizer_id' => $row->organizer_id,
                    'organizer_name' => $row->organizer?->name,
                    'organizer_mobile' => $row->organizer?->mobile,
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'class_name' => $row->studentClass?->class_name,
                    'grade_name' => $row->studentClass?->grade?->grade_name,
                    'payment_amount' => $row->payment_amount,
                    'organizer_amount' => $row->organizer_amount,
                ];
            }),
        ];
    }

    public function organizerReportByRange(string $fromDate, string $toDate)
    {
        [$start, $end] = $this->normalizeDateRange($fromDate, $toDate);

        $snapshots = PaymentSplitSnapshot::with([
            'organizer:id,code,name,mobile,email',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'enrollment.student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
        ])
            ->whereBetween('payment_date', [$start, $end])
            ->whereNotNull('organizer_id')
            ->get();

        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'total_payment_amount' => $snapshots->sum('payment_amount'),
            'total_organizer_amount' => $snapshots->sum('organizer_amount'),
            'count' => $snapshots->count(),
            'details' => $snapshots->map(function ($row) {
                $student = $row->enrollment?->student;

                return [
                    'payment_id' => $row->payment_id,
                    'payment_date' => $row->payment_date?->format('Y-m-d H:i:s'),
                    'organizer_id' => $row->organizer_id,
                    'organizer_name' => $row->organizer?->name,
                    'organizer_mobile' => $row->organizer?->mobile,
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'class_name' => $row->studentClass?->class_name,
                    'grade_name' => $row->studentClass?->grade?->grade_name,
                    'payment_amount' => $row->payment_amount,
                    'organizer_amount' => $row->organizer_amount,
                ];
            }),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Admission Report
    |--------------------------------------------------------------------------
    */
    public function todayAdmissionByMonth(?string $month = null, ?int $year = null)
    {
        [$start, $end] = $this->normalizeMonthRange($month, $year);

        return AdmissionPayment::with([
            'student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
            'collectedBy:id,name',
        ])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->get()
            ->map(function ($payment) {
                $student = $payment->student;

                return [
                    'payment_id' => $payment->id,
                    'created_at' => $payment->created_at?->format('Y-m-d H:i:s'),
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'amount' => $payment->amount,
                    'collected_by' => $payment->collectedBy?->name,
                ];
            });
    }

    public function todayAdmissionByRange(string $fromDate, string $toDate)
    {
        [$start, $end] = $this->normalizeDateRange($fromDate, $toDate);

        return AdmissionPayment::with([
            'student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
            'collectedBy:id,name',
        ])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->get()
            ->map(function ($payment) {
                $student = $payment->student;

                return [
                    'payment_id' => $payment->id,
                    'created_at' => $payment->created_at?->format('Y-m-d H:i:s'),
                    'student_code' => $student?->permanent_qr_active
                        ? $student?->custom_id
                        : $student?->temporary_qr_code,
                    'student_name' => $student?->initial_name,
                    'guardian_mobile' => $student?->guardian_mobile,
                    'amount' => $payment->amount,
                    'collected_by' => $payment->collectedBy?->name,
                ];
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Teacher with Student Payments
    |--------------------------------------------------------------------------
    */
    public function teacherWithStudentPayments(int $teacherId, ?string $month = null, ?int $year = null): array
    {
        try {
            [$start, $end] = $this->normalizeMonthRange($month, $year);

            $classes = StudentClass::query()
                ->select(['id', 'class_name', 'grade_id', 'teacher_id'])
                ->where('teacher_id', $teacherId)
                ->with([
                    'grade:id,grade_name',
                    'categoryFees:id,student_class_id,class_category_id,fee',
                    'categoryFees.category:id,category_name,code',
                ])
                ->orderBy('class_name')
                ->get();

            if ($classes->isEmpty()) {
                return [];
            }

            $enrollments = StudentClassEnrollment::query()
                ->select([
                    'id',
                    'student_id',
                    'student_class_id',
                    'class_category_fee_id',
                ])
                ->with([
                    'student:id,initial_name,guardian_mobile,custom_id,temporary_qr_code,permanent_qr_active',
                    'classCategoryFee:id,student_class_id,class_category_id,fee',
                    'classCategoryFee.category:id,category_name,code',
                    'payments' => function ($query) use ($start, $end) {
                        $query->select([
                            'id',
                            'student_class_enrollment_id',
                            'amount',
                            'paid_at',
                            'payment_method',
                            'receipt_number',
                            'reference_number',
                            'note',
                            'status',
                        ])
                            ->whereBetween('paid_at', [$start, $end])
                            ->where('status', 'completed')
                            ->orderBy('paid_at', 'asc');
                    },
                ])
                ->whereIn('student_class_id', $classes->pluck('id'))
                ->whereHas('payments', function ($query) use ($start, $end) {
                    $query->whereBetween('paid_at', [$start, $end])
                        ->where('status', 'completed');
                })
                ->get()
                ->groupBy('student_class_id');

            return $classes->map(function (StudentClass $class) use ($enrollments) {
                $classEnrollments = $enrollments->get($class->id, collect());

                $categories = $class->categoryFees
                    ->map(function ($fee) use ($classEnrollments) {
                        $feeEnrollments = $classEnrollments->where('class_category_fee_id', $fee->id);

                        $students = $feeEnrollments->map(function (StudentClassEnrollment $enrollment) {
                            $student = $enrollment->student;

                            $payments = $enrollment->payments->map(function (Payment $payment) {
                                return [
                                    'payment_id' => $payment->id,
                                    'amount' => (float) $payment->amount,
                                    'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
                                    'payment_method' => $payment->payment_method,
                                    'receipt_number' => $payment->receipt_number,
                                    'reference_number' => $payment->reference_number,
                                    'note' => $payment->note,
                                ];
                            })->values();

                            return [
                                'enrollment_id' => $enrollment->id,
                                'student_code' => $student?->permanent_qr_active
                                    ? $student?->custom_id
                                    : $student?->temporary_qr_code,
                                'student_name' => $student?->initial_name,
                                'guardian_mobile' => $student?->guardian_mobile,
                                'payments' => $payments,
                                'total_paid' => (float) $payments->sum('amount'),
                            ];
                        })->values();

                        return [
                            'class_category_fee_id' => $fee->id,
                            'category_id' => $fee->class_category_id,
                            'category_name' => $fee->category?->category_name,
                            'fee' => (float) $fee->fee,
                            'students' => $students,
                            'category_total_paid' => (float) $students->sum('total_paid'),
                        ];
                    })
                    ->filter(fn($category) => $category['students']->isNotEmpty())
                    ->values();

                return [
                    'class_id' => $class->id,
                    'class_name' => $class->class_name,
                    'grade_name' => $class->grade?->grade_name,
                    'categories' => $categories,
                    'class_total_paid' => (float) $categories->sum('category_total_paid'),
                ];
            })
                ->filter(fn($class) => $class['categories']->isNotEmpty())
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::error('Teacher report generation failed', [
                'teacher_id' => $teacherId,
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'date' => 'Invalid month or unable to generate report.',
            ]);
        }
    }
}
