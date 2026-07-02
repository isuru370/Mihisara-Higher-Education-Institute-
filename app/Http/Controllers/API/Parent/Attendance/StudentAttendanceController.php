<?php

namespace App\Http\Controllers\API\Parent\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\Attendance\StudentAttendanceRequest;
use App\Services\Parent\Attendance\StudentAttendanceService;
use Illuminate\Http\JsonResponse;

class StudentAttendanceController extends Controller
{
    public function __construct(
        protected StudentAttendanceService $attendanceService
    ) {}

    public function index(
        StudentAttendanceRequest $request
    ): JsonResponse {

        $response = $this->attendanceService->fetchAttendance(
            $request->validated()['student_id']
        );

        return response()->json($response);
    }
}