<?php

namespace App\Http\Controllers\API\Parent\ClassSchedule;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\ClassSchedule\GetClassSchedulesRequest;
use App\Services\Parent\ClassSchedule\ClassScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClassScheduleController extends Controller
{
    public function __construct(
        private  ClassScheduleService $service
    ) {}

    public function index(
        GetClassSchedulesRequest $request
    ): JsonResponse {

        try {

            $data = $this->service->getSchedules(
                $request->validated()['student_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Class schedules fetched successfully.',
                'data' => $data,
            ]);

        } catch (Throwable $e) {

            Log::error('Class schedule fetch failed.', [
                'student_id' => $request->input('student_id'),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch schedules.',
            ], 500);
        }
    }
}