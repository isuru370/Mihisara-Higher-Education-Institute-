<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttendanceScheduleController extends Controller
{
    /**
     * @var AttendanceScheduleService
     */
    protected $attendanceScheduleService;

    /**
     * Constructor
     */
    public function __construct(
        AttendanceScheduleService $attendanceScheduleService
    ) {
        $this->attendanceScheduleService = $attendanceScheduleService;
    }

    /**
     * Get Available Attendance Schedules
     */
    public function index(Request $request)
    {
        $request->validate([

            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'class_category_fee_id' => [
                'required',
                'integer',
                'exists:class_category_fees,id',
            ],

        ]);

        try {

            Log::info('Attendance Schedule Request', [

                'student_class_id'      => $request->student_class_id,

                'class_category_fee_id' => $request->class_category_fee_id,

            ]);

            $result = $this->attendanceScheduleService->getSchedules(

                studentClassId: (int) $request->student_class_id,

                classCategoryFeeId: (int) $request->class_category_fee_id,

            );

            Log::info('Attendance Schedule Response', [

                'count' => count($result),

            ]);

            return response()->json([

                'success' => true,

                'message' => 'Schedules retrieved successfully.',

                'data' => $result,

            ]);

        } catch (Throwable $e) {

            Log::error('Attendance Schedule Failed', [

                'message' => $e->getMessage(),

                'file' => $e->getFile(),

                'line' => $e->getLine(),

            ]);

            return response()->json([

                'success' => false,

                'message' => 'Internal server error.',

                'data' => null,

            ], 500);
        }
    }
}