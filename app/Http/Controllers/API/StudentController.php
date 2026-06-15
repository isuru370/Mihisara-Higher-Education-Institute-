<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QuickPhoto;
use App\Models\Student;
use App\Services\StudentQRService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class StudentController extends Controller
{
    public function fetchAllStudent(): JsonResponse
    {
        $students = Student::with(['grade'])->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }

    public function studentDetailsSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => ['required', 'string', 'max:150'],
        ]);

        $qrResult = StudentQRService::read($validated['qr_code']);

        if (! $qrResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $qrResult['message'] ?? 'Failed to read QR code',
            ], $qrResult['status_code'] ?? 400);
        }

        $student = Student::query()
            ->with(['grade'])
            ->find($qrResult['student_id']);

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student details loaded successfully',
            'student' => $student,
        ], 200);
    }

    public function searchStudent(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'search' => 'required|string|min:1',
            ]);

            $search = trim($request->search);

            $students = Student::with(['grade'])
                ->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('initial_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('custom_id', 'like', "%{$search}%")
                        ->orWhere('temporary_qr_code', 'like', "%{$search}%")
                        ->orWhere('guardian_mobile', 'like', "%{$search}%");
                })
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => $students->count() > 0
                    ? 'Students found successfully'
                    : 'No students found',
                'data' => $students,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStudentImage(Request $request): JsonResponse
    {
        try {

            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'custom_id' => 'required|exists:quick_photos,custom_id',
            ]);

            $responseData = DB::transaction(function () use ($validated) {

                // QuickPhoto eka ganna
                $quickPhoto = QuickPhoto::where(
                    'custom_id',
                    $validated['custom_id']
                )->lockForUpdate()->firstOrFail();

                // already used da check karanna
                if (!$quickPhoto->is_active) {
                    throw new \Exception('This quick photo is already used.');
                }

                // Student eka ganna
                $student = Student::findOrFail(
                    $validated['student_id']
                );

                // student image update
                $student->update([
                    'img_url' => $quickPhoto->image_path,
                    'last_image_update_at' => now(),
                ]);

                // QuickPhoto inactive karanna
                $quickPhoto->update([
                    'is_active' => false,
                ]);

                return [
                    'student_id' => $student->id,
                    'student_custom_id' => $student->custom_id,
                    'quick_photo_custom_id' => $quickPhoto->custom_id,
                    'img_url' => $student->img_url,
                    'image_url' => asset('storage/' . $student->img_url),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Student image updated successfully',
                'data' => $responseData,
            ], 200);
        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update student image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
