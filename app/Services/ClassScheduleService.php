<?php

namespace App\Services;

use App\Jobs\SendClassScheduleSms;
use App\Models\ClassHall;
use App\Models\ClassSchedule;
use App\Models\ClassSchedulePattern;
use App\Models\StudentClassEnrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClassScheduleService
{
    public function getActivePattern(int $studentClassId)
    {
        return ClassSchedulePattern::active()
            ->where('student_class_id', $studentClassId)
            ->first();
    }

    public function validatePatternDateRange($pattern, string $classDate): bool
    {
        $date = Carbon::parse($classDate);

        return !(
            $date->lt(Carbon::parse($pattern->start_date)) ||
            $date->gt(Carbon::parse($pattern->end_date))
        );
    }

    public function hasDuplicateSchedule(
        int $studentClassId,
        string $classDate,
        ?int $excludeId = null
    ): bool {

        $query = ClassSchedule::where(
            'student_class_id',
            $studentClassId
        )
            ->whereDate('class_date', $classDate)
            ->whereNull('deleted_at');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function isLockedStatus(string $status): bool
    {
        return in_array($status, [
            'ongoing',
            'completed',
            'cancelled'
        ]);
    }

    public function isPastSchedule($schedule): bool
    {
        return Carbon::parse(
            $schedule->class_date
        )->lt(today());
    }

    public function sendClassScheduleSms(
        array $validated,
        string $action
    ): void {

        $hall = !empty($validated['class_hall_id'])
            ? ClassHall::find($validated['class_hall_id'])
            : null;

        $students = StudentClassEnrollment::with([
            'student:id,guardian_mobile,is_active',
            'studentClass:id,class_name',
            'classCategoryFee.category:id,category_name'
        ])
            ->where('class_category_fee_id', $validated['class_category_fee_id'])
            ->where('is_active', true)
            ->whereHas('student', function ($query) {
                $query->where('is_active', true);
            })
            ->get();

        $title = match ($action) {
            'created'  => 'New class scheduled.',
            'updated'  => 'Class schedule has been updated.',
            'cancelled' => 'Class has been cancelled.',
            default    => 'Class schedule notification.',
        };

        foreach ($students as $enrollment) {

            if (empty($enrollment->student?->guardian_mobile)) {
                continue;
            }

            $message =
                "Dear Parent,\n\n" .
                "{$title}\n\n" .
                "Class : {$enrollment->studentClass->class_name}\n" .
                "Category : {$enrollment->classCategoryFee->category->category_name}\n";

            if ($hall) {
                $message .= "Hall : {$hall->hall_name}\n";
            }

            if (!empty($validated['class_date'])) {
                $message .= "Date : {$validated['class_date']}\n";
            }

            if (
                !empty($validated['start_time']) &&
                !empty($validated['end_time'])
            ) {
                $message .=
                    "Time : {$validated['start_time']} - {$validated['end_time']}\n";
            }

            if (
                $action === 'cancelled' &&
                !empty($validated['cancel_reason'])
            ) {
                $message .=
                    "Reason : {$validated['cancel_reason']}\n";
            }

            $message .= "\nThank You.";

            SendClassScheduleSms::dispatch(
                $enrollment->student->guardian_mobile,
                $message
            );
        }
    }

    public function generateRecurringSchedules(ClassSchedulePattern $pattern)
    {
        $start = Carbon::parse($pattern->start_date);
        $end = Carbon::parse($pattern->end_date);

        $date = $start->copy();

        while ($date->lte($end)) {

            $dayOfWeek = strtolower($date->format('l'));

            if ($dayOfWeek === strtolower($pattern->class_day)) {

                $exists = ClassSchedule::where('student_class_id', $pattern->student_class_id)
                    ->where('class_category_fee_id', $pattern->class_category_fee_id)
                    ->whereDate('class_date', $date->toDateString())
                    ->where('start_time', $pattern->start_time)
                    ->where('end_time', $pattern->end_time)
                    ->whereNull('deleted_at')
                    ->exists();

                if (!$exists) {

                    ClassSchedule::create([
                        'class_schedule_pattern_id' => $pattern->id,
                        'student_class_id'          => $pattern->student_class_id,
                        'class_category_fee_id'     => $pattern->class_category_fee_id,
                        'class_hall_id'             => $pattern->class_hall_id,
                        'class_date'                => $date->toDateString(),
                        'start_time'                => $pattern->start_time,
                        'end_time'                  => $pattern->end_time,
                        'day_of_week'               => $dayOfWeek,
                        'status'                    => 'scheduled',
                        'is_active'                 => $pattern->is_active,
                        'note'                      => $pattern->note,
                    ]);
                }
            }

            $date->addDay();
        }
    }

    /**
     * Delete future schedules and regenerate.
     */
    public function regenerateFutureSchedules(ClassSchedulePattern $pattern)
    {
        DB::transaction(function () use ($pattern) {

            ClassSchedule::where('class_schedule_pattern_id', $pattern->id)
                ->whereDate('class_date', '>=', today())
                ->where('status', 'scheduled')
                ->delete();

            $this->generateRecurringSchedules($pattern);
        });
    }
}
