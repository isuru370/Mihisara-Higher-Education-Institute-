<?php

namespace App\Http\Controllers\API\Parent\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\Payment\StudentPaymentRequest;
use App\Services\Parent\Payment\StudentPaymentService;
use Illuminate\Http\JsonResponse;

class StudentPaymentController extends Controller
{
    public function __construct(
        private StudentPaymentService $studentPaymentService
    ) {}

    public function index(
        StudentPaymentRequest $request
    ): JsonResponse {

        $response = $this->studentPaymentService->fetchPaymentHistory(
            $request->validated()['student_id']
        );

        return response()->json(
            $response,
            $response['status'] ? 200 : 404
        );
    }
}
