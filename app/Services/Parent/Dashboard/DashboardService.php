<?php

namespace App\Services\Parent\Dashboard;

use App\Models\ClassSchedule;
use App\Models\Exam;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Carbon\Carbon;

class DashboardService
{
    public function fetchDashboardData(
        int $studentId
    ): array {

        $student = Student::query()
            ->select([
                'id',
                'custom_id',
                'temporary_qr_code',
                'initial_name',
                'img_url',
                'grade_id',
            ])
            ->with([
                'grade:id,grade_name',
            ])
            ->find($studentId);

        if (!$student) {
            return [
                'status' => false,
                'message' => 'Student not found',
            ];
        }

        $totalClasses = StudentClassEnrollment::query()
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->count();

        $categoryFeeIds = StudentClassEnrollment::query()
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->pluck('class_category_fee_id');

        $todayClasses = ClassSchedule::query()
            ->select([
                'id',
                'student_class_id',
                'class_category_fee_id',
                'class_date',
                'start_time',
                'end_time',
                'status',
            ])
            ->with([
                'studentClass:id,class_name,subject_id,teacher_id',
                'studentClass.subject:id,subject_name',
                'studentClass.teacher:id,full_name',
            ])
            ->whereIn('class_category_fee_id', $categoryFeeIds)
            ->whereDate('class_date', today())
            ->orderBy('start_time')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'class_date' => $schedule->class_date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'status' => $schedule->status,
                    'live_status' => $this->getClassStatus($schedule),

                    'student_class' => [
                        'id' => $schedule->studentClass?->id,
                        'class_name' => $schedule->studentClass?->class_name,

                        'subject' => [
                            'id' => $schedule->studentClass?->subject?->id,
                            'subject_name' => $schedule->studentClass?->subject?->subject_name,
                        ],

                        'teacher' => [
                            'id' => $schedule->studentClass?->teacher?->id,
                            'full_name' => $schedule->studentClass?->teacher?->full_name,
                        ],
                    ],
                ];
            })
            ->values();

        $thisWeekClasses = ClassSchedule::query()
            ->select([
                'id',
                'student_class_id',
                'class_category_fee_id',
                'class_date',
                'start_time',
                'end_time',
                'status',
            ])
            ->with([
                'studentClass:id,class_name,subject_id,teacher_id',
                'studentClass.subject:id,subject_name',
                'studentClass.teacher:id,full_name',
            ])
            ->whereIn('class_category_fee_id', $categoryFeeIds)
            ->whereBetween(
                'class_date',
                [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]
            )
            ->orderBy('class_date')
            ->orderBy('start_time')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'class_date' => $schedule->class_date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'status' => $schedule->status,
                    'live_status' => $this->getClassStatus($schedule),

                    'student_class' => [
                        'id' => $schedule->studentClass?->id,
                        'class_name' => $schedule->studentClass?->class_name,

                        'subject' => [
                            'id' => $schedule->studentClass?->subject?->id,
                            'subject_name' => $schedule->studentClass?->subject?->subject_name,
                        ],

                        'teacher' => [
                            'id' => $schedule->studentClass?->teacher?->id,
                            'full_name' => $schedule->studentClass?->teacher?->full_name,
                        ],
                    ],
                ];
            })
            ->values();

        $classIds = StudentClassEnrollment::query()
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->pluck('student_class_id');

        $upcomingExams = Exam::query()
            ->select([
                'id',
                'title',
                'student_class_id',
                'class_category_id',
                'exam_date',
                'start_time',
                'end_time',
                'status',
            ])
            ->with([
                'studentClass:id,class_name,subject_id',
                'studentClass.subject:id,subject_name',

                // Category
                'category:id,category_name',
            ])
            ->whereIn('student_class_id', $classIds)
            ->whereBetween('exam_date', [
                today(),
                today()->copy()->addMonth(),
            ])
            ->where('status', '!=', 'cancelled')
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'exam_date' => $exam->exam_date,
                    'start_time' => $exam->start_time,
                    'end_time' => $exam->end_time,
                    'status' => $exam->status,

                    'student_class' => [
                        'id' => $exam->studentClass?->id,
                        'class_name' => $exam->studentClass?->class_name,

                        'subject' => [
                            'id' => $exam->studentClass?->subject?->id,
                            'subject_name' => $exam->studentClass?->subject?->subject_name,
                        ],
                    ],

                    'category' => [
                        'id' => $exam->category?->id,
                        'category_name' => $exam->category?->category_name,
                    ],
                ];
            })
            ->values();

        $recentPayments = Payment::query()
            ->select([
                'id',
                'student_id',
                'student_class_enrollment_id',
                'user_id',
                'amount',
                'payment_month',
                'paid_at',
                'mark_method',
                'status',
            ])
            ->where('student_id', $studentId)
            ->with([
                'enrollment:id,student_class_id',
                'enrollment.studentClass:id,class_name',
                'collectedBy:id,name',
            ])
            ->latest('paid_at')
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'payment_month' => $payment->payment_month?->format('Y-m-d'),
                    'paid_at' => $payment->paid_at,
                    'mark_method' => $payment->mark_method,
                    'status' => $payment->status,

                    'class_name' => $payment->enrollment?->studentClass?->class_name,

                    'collected_by' => [
                        'id' => $payment->collectedBy?->id,
                        'name' => $payment->collectedBy?->name,
                    ],
                ];
            })
            ->values();



        return [
            'status' => true,
            'message' => 'Dashboard data fetched successfully',
            'data' => [
                'banner' => [
                    'title' => config('app.name') . ' වෙත සාදරයෙන් පිළිගනිමු',
                    'subtitle' => 'ඔබගේ අධ්‍යාපනික කටයුතු පහසුවෙන් කළමනාකරණය කරන්න.',
                    'description' => 'පන්ති කාලසටහන්, ඉදිරි විභාග, පැමිණීම, ගෙවීම්, ප්‍රතිඵල සහ ආයතනයේ වැදගත් නිවේදන මෙම යෙදුම හරහා පහසුවෙන් නරඹන්න.',
                ],
                'student' => [
                    'id' => $student->id,
                    'custom_id' => $student->custom_id,
                    'temporary_qr_code' => $student->temporary_qr_code,
                    'initial_name' => $student->initial_name,
                    'grade' => $student->grade?->grade_name,
                    'img_url' => $student->img_url,
                ],

                'total_classes' => $totalClasses,

                'today_classes' => $todayClasses,

                'this_week_classes' => $thisWeekClasses,

                'upcoming_exams' => $upcomingExams,

                'recent_payments' => $recentPayments,
            ],
        ];
    }

    private function getClassStatus(
        ClassSchedule $schedule
    ): string {

        $now = now();

        $start = Carbon::parse(
            $schedule->class_date->format('Y-m-d') .
                ' ' .
                $schedule->start_time
        );

        $end = Carbon::parse(
            $schedule->class_date->format('Y-m-d') .
                ' ' .
                $schedule->end_time
        );

        if ($schedule->status === 'cancelled') {
            return 'cancelled';
        }

        if ($now->between($start, $end)) {
            return 'ongoing';
        }

        if ($now->greaterThan($end)) {
            return 'completed';
        }

        return 'scheduled';
    }
}
