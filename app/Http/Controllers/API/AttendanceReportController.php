<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttendanceReportController extends Controller
{
    /**
     * @var AttendanceReportService
     */
    protected $attendanceReportService;

    /**
     * Constructor
     */
    public function __construct(
        AttendanceReportService $attendanceReportService
    ) {
        $this->attendanceReportService = $attendanceReportService;
    }

    /**
     * Attendance Report
     */
    public function index(Request $request)
    {
        $request->validate([

            'schedule_id' => [
                'required',
                'integer',
                'exists:class_schedules,id',
            ],

        ]);

        try {

            Log::info('Attendance Report Request', [

                'schedule_id' => $request->schedule_id,

            ]);

            $result = $this->attendanceReportService->generate(

                scheduleId: (int) $request->schedule_id,

            );

            Log::info('Attendance Report Response', [

                'students' => count($result['students']),

            ]);

            return response()->json([

                'success' => true,

                'message' => 'Attendance report retrieved successfully.',

                'data' => $result,

            ]);

        } catch (Throwable $e) {

            Log::error('Attendance Report Failed', [

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