<?php

namespace App\Services\Attendance;

use App\Models\ClassSchedule;
use Carbon\Carbon;

class AttendanceScheduleService
{
    /**
     * Get Attendance Schedules
     */
    public function getSchedules(
        int $studentClassId,
        int $classCategoryFeeId
    ): array {

        $schedules = $this->getClassSchedules(
            $studentClassId,
            $classCategoryFeeId
        );

        return $this->buildResponse(
            $schedules
        );
    }

    /**
     * Get Class Schedules
     */
    protected function getClassSchedules(
        int $studentClassId,
        int $classCategoryFeeId
    ) {

        return ClassSchedule::query()

            ->select([
                'id',
                'class_date',
                'start_time',
                'end_time',
                'status',
            ])

            ->where(
                'student_class_id',
                $studentClassId
            )

            ->where(
                'class_category_fee_id',
                $classCategoryFeeId
            )

            ->whereIn(
                'status',
                [
                    'ongoing',
                    'completed',
                ]
            )

            ->where(
                'is_active',
                true
            )

            ->orderByDesc('class_date')

            ->orderByDesc('start_time')

            ->get();
    }

    /**
     * Build Response
     */
    protected function buildResponse(
        $schedules
    ): array {

        return $schedules->map(function ($schedule) {

            return [

                'schedule_id' => $schedule->id,

                'class_date' => Carbon::parse(
                    $schedule->class_date
                )->format('Y-m-d'),

                'day' => Carbon::parse(
                    $schedule->class_date
                )->format('l'),

                'start_time' => $schedule->start_time,

                'end_time' => $schedule->end_time,

                'status' => $schedule->status,

            ];

        })->values()->toArray();
    }
}