<?php

namespace App\Services;

use App\Models\AdmissionPayment;
use App\Models\ExtraIncome;
use App\Models\InstitutePayment;
use App\Models\OrganizerPayment;
use App\Models\Payment;
use App\Models\PaymentSplitSnapshot;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentClassEnrollment;
use App\Models\TeacherPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DailyReportService
{
    protected function parseDate($date)
    {
        return $date
            ? Carbon::parse($date)->toDateString()
            : Carbon::today()->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | Main Daily Summary Report
    |--------------------------------------------------------------------------
    */
    public function generateDailyReport($date = null)
    {
        $date = $this->parseDate($date);

        $paymentTotal = Payment::whereDate('paid_at', $date)
            ->where('status', 'completed')
            ->sum('amount');

        $admissionTotal = AdmissionPayment::whereDate('created_at', $date)
            ->where('status', 'paid')
            ->sum('amount');

        $extraIncomeTotal = ExtraIncome::whereDate('created_at', $date)
            ->where('status', 'paid')
            ->sum('amount');

        $teacherPaymentTotal = TeacherPayment::whereDate('created_at', $date)
            ->where('status', 'paid')
            ->sum('amount');

        $instituteExpencesTotal = InstitutePayment::whereDate('created_at', $date)
            ->where('status', 'paid')
            ->sum('amount');

        $organizerExpenseTotal = OrganizerPayment::whereDate('created_at', $date)
            ->whereIn('payment_type', ['advance', 'deduction', 'other'])
            ->where('status', 'paid')
            ->sum('amount');

        return [
            'date' => $date,
            'payment_total' => $paymentTotal,
            'admission_total' => $admissionTotal,
            'extra_income_total' => $extraIncomeTotal,
            'teacher_expense_total' => $teacherPaymentTotal,
            'organizer_expense_total' => $organizerExpenseTotal,
            'instituteExpencesTotal' => $instituteExpencesTotal,
            'net_total' => (
                $paymentTotal +
                $admissionTotal +
                $extraIncomeTotal
            ) - (
                $teacherPaymentTotal +
                $organizerExpenseTotal +
                $instituteExpencesTotal
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get Students by Day of Week
    |--------------------------------------------------------------------------
    | 
    | @param string|null $date - Selected date (Y-m-d format)
    | @param string|null $dayOfWeek - Selected day (Monday, Tuesday, etc.)
    | 
    | If date is provided, get students for that specific date's day of week
    | If dayOfWeek is provided directly, get students for that day of week
    |
    */
    public function getStudentsByDayOfWeek($date = null, $dayOfWeek = null)
    {
        // Determine the target day of week
        $targetDayOfWeek = $dayOfWeek;

        if ($date && !$dayOfWeek) {
            $targetDayOfWeek = Carbon::parse($date)->format('l');
        }

        if (!$targetDayOfWeek) {
            $targetDayOfWeek = Carbon::today()->format('l');
        }

        // Get all active enrollments with their class schedules
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

        // Filter only enrollments that have schedules on the selected day
        $filteredEnrollments = $enrollments->filter(function ($enrollment) {
            return $enrollment->studentClass->classSchedule->isNotEmpty();
        });

        // Group by class and time
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

        // Re-index and sort by time
        $groupedStudents = array_values($groupedStudents);
        usort($groupedStudents, function ($a, $b) {
            return strcmp($a['start_time'] ?? '', $b['start_time'] ?? '');
        });

        // Calculate summary statistics
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

    /*
    |--------------------------------------------------------------------------
    | Get Students by Specific Date (using the date's day of week)
    |--------------------------------------------------------------------------
    */
    public function getStudentsByDate($date = null)
    {
        return $this->getStudentsByDayOfWeek($date, null);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Students for Today
    |--------------------------------------------------------------------------
    */
    public function getStudentsForToday()
    {
        return $this->getStudentsByDayOfWeek(Carbon::today()->toDateString(), null);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Students for Tomorrow
    |--------------------------------------------------------------------------
    */
    public function getStudentsForTomorrow()
    {
        return $this->getStudentsByDayOfWeek(Carbon::tomorrow()->toDateString(), null);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Students for a Specific Day Name
    |--------------------------------------------------------------------------
    */
    public function getStudentsByDayName($dayName)
    {
        return $this->getStudentsByDayOfWeek(null, ucfirst(strtolower($dayName)));
    }

    /*
    |--------------------------------------------------------------------------
    | Student Payment Report
    |--------------------------------------------------------------------------
    */
    public function studentPaymentReport($date = null)
    {
        $date = $this->parseDate($date);

        return Payment::with([
            'student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
            'collectedBy:id,name',
            'enrollment:id,student_id,student_class_id,class_category_fee_id,custom_fee,discount_percentage,is_free_card',
            'enrollment.studentClass:id,class_name,grade_id',
            'enrollment.studentClass.grade:id,grade_name',
            'enrollment.classCategoryFee:id,student_class_id,class_category_id,fee',
            'enrollment.classCategoryFee.category:id,category_name',
        ])
            ->whereDate('paid_at', $date)
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
    public function teacherCollectionReport($date = null)
    {
        $date = $this->parseDate($date);

        return PaymentSplitSnapshot::with([
            'teacher:id,full_name,initials',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'enrollment.student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
        ])
            ->whereDate('payment_date', $date)
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
    | Institution Daily Report
    |--------------------------------------------------------------------------
    */
    public function institutionDailyReport($date = null)
    {
        $date = $this->parseDate($date);

        $snapshots = PaymentSplitSnapshot::with([
            'payment:id,amount,paid_at,status',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'enrollment.student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
            'paymentConfig:id,student_class_id,teacher_percentage,organizer_percentage,institution_percentage',
        ])
            ->whereDate('payment_date', $date)
            ->get();

        return [
            'date' => $date,
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
    | Organizer Daily Report
    |--------------------------------------------------------------------------
    */
    public function organizerDailyReport($date = null)
    {
        $date = $this->parseDate($date);

        $snapshots = PaymentSplitSnapshot::with([
            'organizer:id,code,name,mobile,email',
            'studentClass:id,class_name,grade_id',
            'studentClass.grade:id,grade_name',
            'enrollment.student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
        ])
            ->whereDate('payment_date', $date)
            ->whereNotNull('organizer_id')
            ->get();

        return [
            'date' => $date,
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

    public function todayAdmission($date = null)
    {
        $date = $this->parseDate($date);

        return AdmissionPayment::with([
            'student:id,custom_id,permanent_qr_active,temporary_qr_code,initial_name,guardian_mobile',
            'collectedBy:id,name',
        ])
            ->whereDate('created_at', $date)
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

    public function teacherWithStudentPayments(int $teacherId, ?string $date = null): array
    {
        try {
            $reportDate = $this->normalizeReportDate($date);

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
                    'payments' => function ($query) use ($reportDate) {
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
                            ->whereDate('paid_at', $reportDate)
                            ->where('status', 'completed')
                            ->orderBy('paid_at', 'asc');
                    },
                ])
                ->whereIn('student_class_id', $classes->pluck('id'))
                ->whereHas('payments', function ($query) use ($reportDate) {
                    $query->whereDate('paid_at', $reportDate)
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
                'date' => $date,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'date' => 'Invalid date or unable to generate report.',
            ]);
        }
    }

    private function normalizeReportDate(?string $date): string
    {
        if (empty($date)) {
            return now()->toDateString();
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'date' => 'Invalid date format.',
            ]);
        }
    }
}
