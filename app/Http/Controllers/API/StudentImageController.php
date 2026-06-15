<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QuickPhoto;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StudentImageController extends Controller
{
    public function fetchStudentImage(): JsonResponse
    {
        $students = Student::select(
            'id',
            'custom_id',
            'temporary_qr_code',
            'initial_name',
            'guardian_mobile',
            'img_url'
        )
            ->selectRaw("
        CASE
            WHEN permanent_qr_active = 1
            THEN custom_id
            ELSE temporary_qr_code
        END as qr_code
    ")
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }


    public function updateStudentImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'quick_image_id' => 'required|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $student = Student::lockForUpdate()->findOrFail($validated['student_id']);

            $quickPhoto = QuickPhoto::where('custom_id', $validated['quick_image_id'])
                ->lockForUpdate()
                ->first();

            if (!$quickPhoto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quick image was not uploaded.',
                ], 404);
            }

            if (empty($quickPhoto->image_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quick image file not found.',
                ], 404);
            }

            if (!$quickPhoto->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quick image was already used or deactivated.',
                ], 422);
            }

            $student->img_url = $quickPhoto->image_path;
            $student->last_image_update_at = now();
            $student->save();

            $quickPhoto->is_active = false;
            $quickPhoto->save();

            return response()->json([
                'success' => true,
                'message' => 'Student image updated successfully.',
                'data' => [
                    'student_id' => $student->id,
                    'custom_id' => $student->custom_id,
                    'initial_name' => $student->initial_name,
                    'img_url' => $student->img_url,
                    'updated_at' => $student->last_image_update_at,
                    'quick_image_id' => $quickPhoto->custom_id,
                    'quick_photo_active' => $quickPhoto->is_active,
                ],
            ]);
        });
    }
}
