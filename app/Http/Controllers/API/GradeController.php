<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Throwable;

class GradeController extends Controller
{
    public function fetchGrade(): JsonResponse
    {
        try {

            $grades = Grade::where('is_active', true)
                ->orderBy('id', 'desc')
                ->select('id', 'grade_name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Grades fetched successfully',
                'data' => $grades,
            ], 200);
        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch grades',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
