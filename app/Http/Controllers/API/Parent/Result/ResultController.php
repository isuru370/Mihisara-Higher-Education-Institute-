<?php

namespace App\Http\Controllers\API\Parent\Result;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\Result\ResultRequest;
use App\Services\Parent\Result\ResultService;
use Illuminate\Http\JsonResponse;

class ResultController extends Controller
{
    public function __construct(
        private ResultService $resultService
    ) {}

    public function index(
        ResultRequest $request
    ): JsonResponse {

        $response = $this->resultService->fetchResult(
            $request->validated()['student_id'],
            $request->validated()['exam_id']
        );

        return response()->json(
            $response,
            $response['status'] ? 200 : 404
        );
    }
}