<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\StudentCardAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentCardAssignmentController extends Controller
{
    public function __construct(
        protected StudentCardAssignmentService $studentCardAssignmentService
    ) {}

    /**
     * Search Student
     */
    public function searchStudent(
        Request $request
    ): JsonResponse {

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {

            $result = $this->studentCardAssignmentService
                ->searchStudent(
                    $request->code
                );

            return response()->json([

                'success' => true,

                'message' => 'Student retrieved successfully.',

                'data' => $result,

            ]);
        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

                'data' => null,

            ], 422);
        }
    }

    /**
     * Search Available Card
     */
    public function searchAvailableCard(
        Request $request
    ): JsonResponse {

        $request->validate([
            'qr_code' => ['required', 'string'],
        ]);

        try {

            $card = $this->studentCardAssignmentService
                ->searchAvailableCard(
                    $request->qr_code
                );

            return response()->json([

                'success' => true,

                'message' => 'Available student card found.',

                'data' => $card,

            ]);
        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

                'data' => null,

            ], 422);
        }
    }

    /**
     * Assign Card
     */
    public function assignCard(
        Request $request
    ): JsonResponse {

        $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'card_qr_code' => [
                'required',
                'exists:student_cards,qr_code',
            ],
        ]);

        try {

            $card = $this->studentCardAssignmentService
                ->assignCard(
                    $request->student_id,
                    $request->card_qr_code
                );

            return response()->json([

                'success' => true,

                'message' => 'Student card assigned successfully.',

                'data' => $card,

            ]);
        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

                'data' => null,

            ], 422);
        }
    }
}
