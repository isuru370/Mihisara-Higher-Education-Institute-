<?php

namespace App\Http\Controllers\API\Parent\Exam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\Exam\ExamRequest;
use App\Services\Parent\Exam\ExamService;
use Illuminate\Http\JsonResponse;

class ExamController extends Controller
{
    public function __construct(
        private  ExamService $examService
    ) {}

    public function index(
        ExamRequest $request
    ): JsonResponse {

        $response = $this->examService->fetchExams(
            $request->validated()['student_id']
        );

        return response()->json(
            $response,
            $response['status'] ? 200 : 404
        );
    }
}