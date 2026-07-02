<?php

namespace App\Services\Parent\ClassSchedule;

use App\Models\ClassSchedule;
use App\Models\StudentClassEnrollment;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClassScheduleService
{
    public function getSchedules(
        int $studentId
    ) {

        try {

            $categoryFeeIds = StudentClassEnrollment::query()
                ->where('student_id', $studentId)
                ->where('is_active', true)
                ->pluck('class_category_fee_id');

            return ClassSchedule::query()
                ->select([
                    'id',
                    'student_class_id',
                    'class_category_fee_id',
                    'class_hall_id',
                    'class_date',
                    'start_time',
                    'end_time',
                    'day_of_week',
                    'status',
                    'note',
                ])
                ->whereIn(
                    'class_category_fee_id',
                    $categoryFeeIds
                )
                ->with([

                    'studentClass:id,class_name,teacher_id,subject_id,grade_id',

                    'studentClass.teacher:id,full_name,mobile',

                    'studentClass.subject:id,subject_name',

                    'studentClass.grade:id,grade_name',

                    'classCategoryFee:id,class_category_id',

                    'classCategoryFee.category:id,category_name',

                    'hall:id,hall_name',

                ])
                ->orderBy('class_date')
                ->orderBy('start_time')
                ->get();

        } catch (Throwable $e) {

            Log::error('Schedule fetch failed.', [
                'student_id' => $studentId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}