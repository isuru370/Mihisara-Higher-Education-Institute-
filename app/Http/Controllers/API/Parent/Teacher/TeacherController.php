<?php

namespace App\Http\Controllers\API\Parent\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\Teacher\GetTeachersRequest;
use App\Services\Parent\Teacher\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class TeacherController extends Controller
{
    public function __construct(
        private  TeacherService $teacherService
    ) {}

    public function index(GetTeachersRequest $request): JsonResponse
    {
        try {

            $teachers = $this->teacherService->getAllTeachers(
                $request->validated()['student_id'],
            );

            return response()->json([
                'success' => true,
                'message' => 'Teachers retrieved successfully.',
                'data'    => $teachers,
            ]);
        } catch (Throwable $e) {

            Log::error('Unable to retrieve teachers.', [
                'student_id' => $request->student_id,
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve teachers.',
            ], 500);
        }
    }
}
