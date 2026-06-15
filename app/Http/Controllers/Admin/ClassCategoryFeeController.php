<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassCategory;
use App\Models\ClassCategoryFee;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class ClassCategoryFeeController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassCategoryFee::with([
            'studentClass.grade',
            'studentClass.subject',
            'studentClass.teacher',
            'category',
        ]);

        if ($request->filled('student_class_id')) {
            $query->where('student_class_id', $request->student_class_id);
        }

        if ($request->filled('class_category_id')) {
            $query->where('class_category_id', $request->class_category_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $fees = $query->latest()
            ->paginate(10)
            ->appends($request->query());

        $classes = StudentClass::with(['grade', 'subject'])
            ->where('is_active', true)
            ->orderBy('class_name')
            ->get();

        $categories = ClassCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('admin.class_category_fees.index', compact(
            'fees',
            'classes',
            'categories'
        ));
    }

    public function create(Request $request)
    {
        $selectedClassId = $request->student_class_id;

        if ($selectedClassId) {
            $selectedClass = StudentClass::with(['grade', 'subject', 'teacher'])
                ->findOrFail($selectedClassId);

            if (! $selectedClass->is_active) {
                return redirect()
                    ->route('admin.student-classes.index')
                    ->with('error', 'Inactive class එකකට category fee add කරන්න බැහැ.');
            }
        }

        $classes = StudentClass::with(['grade', 'subject', 'teacher'])
            ->where('is_active', true)
            ->orderBy('class_name')
            ->get();

        $categories = ClassCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('admin.class_category_fees.create', compact(
            'classes',
            'categories',
            'selectedClassId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_class_id' => [
                'required',
                Rule::exists('student_classes', 'id')->where('is_active', true),
            ],

            'class_category_id' => [
                'required',
                'exists:class_categories,id',

                Rule::unique('class_category_fees', 'class_category_id')
                    ->where(function ($query) use ($request) {
                        return $query->where('student_class_id', $request->student_class_id)
                            ->whereNull('deleted_at');
                    }),
            ],

            'fee' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);

        DB::transaction(function () use ($request, $validated) {
            ClassCategoryFee::create([
                'student_class_id' => $validated['student_class_id'],
                'class_category_id' => $validated['class_category_id'],
                'fee' => $validated['fee'],
                'is_active' => $request->boolean('is_active', true),
                'note' => isset($validated['note']) ? $validated['note'] : null,
            ]);

            StudentClass::where('id', $validated['student_class_id'])
                ->update([
                    'is_ongoing' => true,
                ]);
        });

        return redirect()
            ->route('admin.class-category-fees.index')
            ->with('success', 'Class category fee created successfully.');
    }

    public function show(ClassCategoryFee $classCategoryFee)
    {
        $classCategoryFee->load([
            'studentClass.grade',
            'studentClass.subject',
            'studentClass.teacher',
            'category'
        ]);

        return view(
            'admin.class_category_fees.show',
            compact('classCategoryFee')
        );
    }

    public function edit(ClassCategoryFee $classCategoryFee)
    {
        // inactive class fee edit block
        if (! $classCategoryFee->studentClass?->is_active) {

            return redirect()
                ->route('admin.class-category-fees.index')
                ->with(
                    'error',
                    'Inactive class fee edit කරන්න බැහැ.'
                );
        }

        $classes = StudentClass::where('is_active', true)
            ->orderBy('class_name')
            ->get();

        $categories = ClassCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('admin.class_category_fees.edit', compact(
            'classCategoryFee',
            'classes',
            'categories'
        ));
    }

    public function update(
        Request $request,
        ClassCategoryFee $classCategoryFee
    ) {

        // prevent inactive class update
        if (! $classCategoryFee->studentClass?->is_active) {

            return redirect()
                ->route('admin.class-category-fees.index')
                ->with(
                    'error',
                    'Inactive class fee update කරන්න බැහැ.'
                );
        }

        $validated = $request->validate([

            'student_class_id' => [
                'required',

                Rule::exists('student_classes', 'id')
                    ->where('is_active', true),
            ],

            'class_category_id' => [
                'required',
                'exists:class_categories,id',

                Rule::unique(
                    'class_category_fees',
                    'class_category_id'
                )
                    ->ignore($classCategoryFee->id)
                    ->where(function ($query) use ($request) {

                        return $query->where(
                            'student_class_id',
                            $request->student_class_id
                        )->whereNull('deleted_at');
                    }),
            ],

            'fee' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);

        $classCategoryFee->update([
            'student_class_id' => $validated['student_class_id'],
            'class_category_id' => $validated['class_category_id'],
            'fee' => $validated['fee'],
            'is_active' => $request->boolean('is_active', false),
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()
            ->route('admin.class-category-fees.index')
            ->with(
                'success',
                'Class category fee updated successfully.'
            );
    }

    public function destroy(ClassCategoryFee $classCategoryFee)
    {
        try {

            // prevent inactive class delete
            if (! $classCategoryFee->studentClass?->is_active) {

                return back()->with(
                    'error',
                    'Inactive class fee delete කරන්න බැහැ.'
                );
            }

            $classCategoryFee->delete();

            return redirect()
                ->route('admin.class-category-fees.index')
                ->with(
                    'success',
                    'Class category fee deleted successfully.'
                );
        } catch (Exception $e) {

            Log::error('Class category fee delete failed', [
                'id' => $classCategoryFee->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Class category fee delete failed.'
            );
        }
    }

    public function toggleActive(ClassCategoryFee $classCategoryFee)
    {
        // prevent inactive class fee toggle
        if (! $classCategoryFee->studentClass?->is_active) {

            return back()->with(
                'error',
                'Inactive class fee status change කරන්න බැහැ.'
            );
        }

        $classCategoryFee->update([
            'is_active' => ! $classCategoryFee->is_active,
        ]);

        return back()->with(
            'success',
            'Class category fee status updated.'
        );
    }

    // ClassCategoryFeeController
    public function byClass(StudentClass $studentClass)
    {
        return ClassCategoryFee::query()
            ->with('category')
            ->where('student_class_id', $studentClass->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn($fee) => [
                'id' => $fee->id,
                'category_name' => $fee->category?->category_name ?? '-',
                'fee' => number_format($fee->fee, 2, '.', ''),
            ]);
    }
}
