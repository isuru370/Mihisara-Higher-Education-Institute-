<?php

namespace App\Services\Attendance;

use App\Models\ClassSchedule;
use App\Models\StudentAttendance;
use App\Models\StudentTute;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttendanceStudentInfoService
{
    /**
     * Build Student Attendance Information
     *
     * @param AttendanceContext $context
     * @return array
     */
    public function build(
        AttendanceContext $context
    ): array {

        try {

            return [

                /*
                |--------------------------------------------------------------------------
                | Enrollment
                |--------------------------------------------------------------------------
                */

                'enrollment' => [

                    'id' => $context->enrollment->id,

                    'student_class_id' =>
                    $context->enrollment->student_class_id,

                    'class_category_fee_id' =>
                    $context->enrollment->class_category_fee_id,

                    'final_fee' =>
                    $context->enrollment->final_fee,

                    'is_free_card' =>
                    (bool) $context->enrollment->is_free_card,

                ],

                /*
                |--------------------------------------------------------------------------
                | Last Payment
                |--------------------------------------------------------------------------
                */

                'last_payment' => $this->getLastPayment(
                    $context
                ),

                /*
                |--------------------------------------------------------------------------
                | Current Month Payment
                |--------------------------------------------------------------------------
                */

                'current_month_payment' => $this->getCurrentMonthPayment(
                    $context
                ),

                /*
                |--------------------------------------------------------------------------
                | Attendance Summary
                |--------------------------------------------------------------------------
                */

                'attendance' => $this->getAttendanceSummary(
                    $context
                ),

                /*
                |--------------------------------------------------------------------------
                | Tute Summary
                |--------------------------------------------------------------------------
                */

                'tute' => $this->getTuteSummary(
                    $context
                ),

            ];
        } catch (Throwable $e) {

            Log::error(

                'Attendance Student Information Failed',

                [

                    'student_id' => optional(
                        $context->student
                    )->id,

                    'message' => $e->getMessage(),

                    'file' => $e->getFile(),

                    'line' => $e->getLine(),

                ]

            );

            return [

                'enrollment' => null,

                'last_payment' => null,

                'current_month_payment' => null,

                'attendance' => null,

                'tute' => null,

            ];
        }
    }

    /**
     * Has the student paid for the current month for this enrollment?
     *
     * Looks at payment_month (the month the fee is for), not at when the
     * last payment was made, so an old or advance payment is not counted.
     */
    protected function getCurrentMonthPayment(
        AttendanceContext $context
    ): array {

        $now = Carbon::now();

        $currentMonth = $now->format('Y-m');

        $payment = $context->enrollment
            ->payments
            ->first(function ($payment) use ($currentMonth) {

                return optional($payment->payment_month)->format('Y-m') === $currentMonth;
            });

        return [

            'month' => $currentMonth,

            'month_label' => $now->format('F Y'),

            'is_paid' => (bool) $payment,

            'is_free_card' => (bool) $context->enrollment->is_free_card,

            'payment_id' => optional($payment)->id,

            'receipt_number' => optional($payment)->receipt_number,

            'paid_at' => optional(optional($payment)->paid_at)->format('Y-m-d H:i:s'),

        ];
    }

    /**
     * Get Last Completed Payment
     *
     * @param AttendanceContext $context
     * @return array|null
     */
    protected function getLastPayment(
        AttendanceContext $context
    ): ?array {

        /*
        |--------------------------------------------------------------------------
        | Payments already eager loaded
        |--------------------------------------------------------------------------
        */

        $payment = $context->enrollment
            ->payments
            ->first();

        if (!$payment) {

            return null;
        }

        return [

            'payment_id' => $payment->id,

            'amount' => $payment->amount,

            'discount_amount' => $payment->discount_amount,

            'payment_month' => optional(
                $payment->payment_month
            )->format('Y-m'),

            'payment_method' => $payment->payment_method,

            'receipt_number' => $payment->receipt_number,

            'paid_at' => optional(
                $payment->paid_at
            )->format('Y-m-d H:i:s'),

            'status' => $payment->status,

        ];
    }

    /**
     * Get Attendance Summary
     *
     * @param AttendanceContext $context
     * @return array
     */
    protected function getAttendanceSummary(
        AttendanceContext $context
    ): array {

        $now = Carbon::now();

        /*
    |--------------------------------------------------------------------------
    | Conducted Classes
    |--------------------------------------------------------------------------
    */

        $scheduleIds = ClassSchedule::query()

            ->where(
                'student_class_id',
                $context->schedule->student_class_id
            )

            ->where(
                'class_category_fee_id',
                $context->schedule->class_category_fee_id
            )

            ->whereYear(
                'class_date',
                $now->year
            )

            ->whereMonth(
                'class_date',
                $now->month
            )

            ->whereIn('status', [

                'ongoing',

                'completed',

            ])

            ->where(
                'is_active',
                true
            )

            ->pluck('id');

        /*
    |--------------------------------------------------------------------------
    | Total Conducted Classes
    |--------------------------------------------------------------------------
    */

        $totalCount = $scheduleIds->count();

        $presentCount = StudentAttendance::query()

            ->where('student_id', $context->student->id)

            ->where('student_class_enrollment_id', $context->enrollment->id)

            ->whereIn('class_schedule_id', $scheduleIds)

            ->count();

        $absentCount = max($totalCount - $presentCount, 0);

        $attendancePercentage = $totalCount > 0
            ? round(($presentCount / $totalCount) * 100)
            : 0;

        return [

            'month' => $now->format('Y-m'),

            // මේ මාසේ මේ class/category එකට පැවැත්වුණු sessions ගණන
            'total_count' => $totalCount,

            // Student attendance mark වුණු ගණන
            'present_count' => $presentCount,

            // Student attendance mark නොවුණු ගණන
            'absent_count' => $absentCount,

            // Attendance %
            'attendance_percentage' => $attendancePercentage,

        ];
    }

    /**
     * Get Current Month Tute Summary
     *
     * @param AttendanceContext $context
     * @return array|null
     */
    protected function getTuteSummary(
        AttendanceContext $context
    ): ?array {

        try {

            $now = Carbon::now();

            $tute = StudentTute::query()

                ->select([
                    'id',
                    'issued_at',
                    'note',
                    'is_issued',
                ])

                ->where(
                    'student_id',
                    $context->student->id
                )

                ->where(
                    'student_class_enrollment_id',
                    $context->enrollment->id
                )

                ->where(
                    'is_issued',
                    true
                )

                ->whereYear(
                    'issued_at',
                    $now->year
                )

                ->whereMonth(
                    'issued_at',
                    $now->month
                )

                ->latest('issued_at')

                ->first();

            if (!$tute) {

                return [

                    'month' => $now->format('Y-m'),

                    'is_issued' => false,

                    'issued_at' => null,

                    'note' => null,

                ];
            }

            return [

                'month' => $now->format('Y-m'),

                'is_issued' => true,

                'issued_at' => optional(
                    $tute->issued_at
                )->format('Y-m-d H:i:s'),

                'note' => $tute->note,

            ];
        } catch (Throwable $e) {

            Log::error('Tute Summary Failed', [

                'student_id' => $context->student->id,

                'enrollment_id' => $context->enrollment->id,

                'message' => $e->getMessage(),

                'file' => $e->getFile(),

                'line' => $e->getLine(),

            ]);

            return [

                'month' => Carbon::now()->format('Y-m'),

                'is_issued' => false,

                'issued_at' => null,

                'note' => null,

            ];
        }
    }
}
