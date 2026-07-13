<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentClass;
use App\Models\ClassCategoryFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentClassManagementController extends Controller
{
    /**
     * Student Class Management Main Page
     */
    public function index()
    {
        return view('admin.student-class-management.index');
    }

    /**
     * Student එකක TMP ID එකෙන් search කරලා ඒ student ගේ classes display කරනවා
     */
    public function searchStudentClasses(Request $request)
    {
        try {
            // Get tmp_id from request (GET or POST)
            $tmpId = $request->input('tmp_id');

            // If GET request with no tmp_id, redirect to index
            if ($request->isMethod('get') && !$tmpId) {
                return redirect()->route('admin.student-class-management.index')
                    ->with('info', 'Please enter a TMP ID to search');
            }

            // If no tmp_id, redirect to index with message
            if (!$tmpId) {
                return redirect()->route('admin.student-class-management.index')
                    ->with('info', 'Please enter a TMP ID to search');
            }

            $validator = Validator::make(['tmp_id' => $tmpId], [
                'tmp_id' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->route('admin.student-class-management.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            // Search student - ✅ With grade relationship
            $student = Student::where('temporary_qr_code', $tmpId)
                ->with([
                    'enrollments' => function ($query) {
                        $query->with(['studentClass.grade', 'classCategoryFee.category']);
                    }
                ])
                ->first();

            if (!$student) {
                return redirect()->route('admin.student-class-management.index')
                    ->with('error', 'Student not found with TMP ID: ' . $tmpId);
            }

            $classes = $student->enrollments->map(function ($enrollment) {
                return [
                    'enrollment_id' => $enrollment->id,
                    'class_id' => $enrollment->student_class_id,
                    'class_name' => $enrollment->studentClass?->class_name ?? 'N/A',
                    'class_type' => $enrollment->studentClass?->class_type ?? 'N/A',
                    'medium' => $enrollment->studentClass?->medium ?? 'N/A',
                    'grade_name' => $enrollment->studentClass?->grade?->grade_name ?? 'N/A', // ✅ Added
                    'category_name' => $enrollment->classCategoryFee?->category?->category_name ?? 'N/A',
                    'fee' => $enrollment->classCategoryFee?->fee ?? 0,
                    'is_active' => $enrollment->is_active,
                    'custom_fee' => $enrollment->custom_fee,
                    'discount_percentage' => $enrollment->discount_percentage,
                    'final_fee' => $enrollment->final_fee,
                    'payment_status' => $enrollment->payment_status,
                    'enrolled_at' => $enrollment->enrolled_at?->format('Y-m-d'),
                    'left_at' => $enrollment->left_at?->format('Y-m-d'),
                    'note' => $enrollment->note,
                ];
            });

            return view('admin.student-class-management.show', compact('student', 'classes'));
        } catch (\Exception $e) {
            return redirect()->route('admin.student-class-management.index')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Student එකේ specific class එකක් active/inactive කරනවා (Modal සමඟ)
     */
    public function toggleClassStatus(Request $request, $enrollmentId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_active' => 'required|boolean',
                'left_at' => 'nullable|date',
                'note' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $enrollment = StudentClassEnrollment::with(['student', 'studentClass'])
                ->find($enrollmentId);

            if (!$enrollment) {
                return redirect()->back()
                    ->with('error', 'Class enrollment not found');
            }

            $studentId = $enrollment->student_id;

            $enrollment->is_active = $request->is_active;

            if (!$request->is_active) {
                $enrollment->left_at = $request->left_at ?? now();
            } else {
                $enrollment->left_at = null;
            }

            if ($request->has('note')) {
                $enrollment->note = $request->note;
            }

            $enrollment->save();

            $message = $request->is_active
                ? 'Class activated successfully'
                : 'Class deactivated successfully';

            return redirect()->route('admin.student-class-management.show', $studentId)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating class status: ' . $e->getMessage());
        }
    }

    /**
     * Student එකේ specific class එකක් deactivate කරනවා
     */
    public function deactivateClass($enrollmentId)
    {
        $enrollment = StudentClassEnrollment::find($enrollmentId);
        if (!$enrollment) {
            return redirect()->back()->with('error', 'Enrollment not found');
        }

        $studentId = $enrollment->student_id;

        $enrollment->is_active = false;
        $enrollment->left_at = now();
        $enrollment->save();

        return redirect()->route('admin.student-class-management.show', $studentId)
            ->with('success', 'Class deactivated successfully');
    }

    /**
     * Student එකේ specific class එකක් activate කරනවා
     */
    public function activateClass($enrollmentId)
    {
        $enrollment = StudentClassEnrollment::find($enrollmentId);
        if (!$enrollment) {
            return redirect()->back()->with('error', 'Enrollment not found');
        }

        $studentId = $enrollment->student_id;

        $enrollment->is_active = true;
        $enrollment->left_at = null;
        $enrollment->save();

        return redirect()->route('admin.student-class-management.show', $studentId)
            ->with('success', 'Class activated successfully');
    }

    /**
     * Student එකක සියලුම classes එකවර active/inactive කරනවා
     */
    public function toggleAllClassesStatus(Request $request, $studentId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_active' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->route('admin.student-class-management.show', $studentId)
                    ->withErrors($validator);
            }

            $student = Student::find($studentId);
            if (!$student) {
                return redirect()->route('admin.student-class-management.show', $studentId)
                    ->with('error', 'Student not found');
            }

            $updated = StudentClassEnrollment::where('student_id', $studentId)
                ->update([
                    'is_active' => $request->is_active,
                    'left_at' => $request->is_active ? null : now(),
                ]);

            $message = $request->is_active
                ? 'All classes activated successfully'
                : 'All classes deactivated successfully';

            return redirect()->route('admin.student-class-management.show', $studentId)
                ->with('success', $message . ' (' . $updated . ' classes updated)');
        } catch (\Exception $e) {
            return redirect()->route('admin.student-class-management.show', $studentId)
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Student එකට නව class එකක් assign කරනවා (Form Display)
     */
    public function showAssignClassForm($studentId = null)
    {
        $student = null;
        if ($studentId) {
            $student = Student::find($studentId);
        }

        $classes = StudentClass::where('is_active', true)->get();
        $students = Student::where('is_active', true)->get();

        return view('admin.student-class-management.assign', compact('student', 'classes', 'students'));
    }

    /**
     * Student එකට නව class එකක් assign කරනවා (Store)
     */
    public function assignClassToStudent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'student_class_id' => 'required|exists:student_classes,id',
                'class_category_fee_id' => 'nullable|exists:class_category_fees,id',
                'is_active' => 'boolean',
                'is_free_card' => 'boolean',
                'custom_fee' => 'nullable|numeric|min:0',
                'custom_fee_reason' => 'nullable|string|max:255',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'discount_reason' => 'nullable|string|max:255',
                'note' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $existing = StudentClassEnrollment::where('student_id', $request->student_id)
                ->where('student_class_id', $request->student_class_id)
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->with('error', 'This class is already assigned to the student')
                    ->withInput();
            }

            if ($request->class_category_fee_id) {
                $classCategoryFee = ClassCategoryFee::find($request->class_category_fee_id);
                if (!$classCategoryFee || $classCategoryFee->student_class_id != $request->student_class_id) {
                    return redirect()->back()
                        ->with('error', 'Invalid class category fee for this class')
                        ->withInput();
                }
            }

            $enrollment = StudentClassEnrollment::create([
                'student_id' => $request->student_id,
                'student_class_id' => $request->student_class_id,
                'class_category_fee_id' => $request->class_category_fee_id,
                'is_active' => $request->is_active ?? true,
                'is_free_card' => $request->is_free_card ?? false,
                'custom_fee' => $request->custom_fee,
                'custom_fee_reason' => $request->custom_fee_reason,
                'discount_percentage' => $request->discount_percentage,
                'discount_reason' => $request->discount_reason,
                'enrolled_at' => now(),
                'note' => $request->note,
            ]);

            return redirect()->route('admin.student-class-management.show', $request->student_id)
                ->with('success', 'Class assigned to student successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Student එකගේ classes display කරනවා (student ID එකෙන්)
     */
    public function showStudentClasses($studentId)
    {
        $student = Student::with([
            'enrollments' => function ($query) {
                $query->with(['studentClass.grade', 'classCategoryFee.category']);
            }
        ])->find($studentId);

        if (!$student) {
            return redirect()->route('admin.student-class-management.index')
                ->with('error', 'Student not found');
        }

        $classes = $student->enrollments->map(function ($enrollment) {
            return [
                'enrollment_id' => $enrollment->id,
                'class_id' => $enrollment->student_class_id,
                'class_name' => $enrollment->studentClass?->class_name ?? 'N/A',
                'class_type' => $enrollment->studentClass?->class_type ?? 'N/A',
                'medium' => $enrollment->studentClass?->medium ?? 'N/A',
                'grade_name' => $enrollment->studentClass?->grade?->grade_name ?? 'N/A',
                'category_name' => $enrollment->classCategoryFee?->category?->category_name ?? 'N/A',
                'fee' => $enrollment->classCategoryFee?->fee ?? 0,
                'is_active' => $enrollment->is_active,
                'custom_fee' => $enrollment->custom_fee,
                'discount_percentage' => $enrollment->discount_percentage,
                'final_fee' => $enrollment->final_fee,
                'payment_status' => $enrollment->payment_status,
                'enrolled_at' => $enrollment->enrolled_at?->format('Y-m-d'),
                'left_at' => $enrollment->left_at?->format('Y-m-d'),
                'note' => $enrollment->note,
            ];
        });

        return view('admin.student-class-management.show', compact('student', 'classes'));
    }

    /**
     * Get category fees for a class (AJAX)
     */
    /**
     * Get category fees for a class (AJAX)
     */
    public function getCategoryFees($classId)
    {
        try {
            $fees = ClassCategoryFee::where('student_class_id', $classId)
                ->where('is_active', true)
                ->with('category')
                ->get()
                ->map(function ($fee) {
                    return [
                        'id' => $fee->id,
                        'category_name' => $fee->category->category_name ?? 'N/A',
                        'fee' => $fee->fee,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $fees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading category fees',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
