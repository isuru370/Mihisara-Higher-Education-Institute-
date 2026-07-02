<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{
    // Grade List
    public function index()
    {
        $grades = Grade::withCount('students')
            ->latest()
            ->get();

        return view('admin.grade.index', compact('grades'));
    }

    // Store Grade
    public function store(Request $request)
    {
        $request->validate([
            'grade_name' => [
                'required',
                'max:100',
                Rule::unique('grades', 'grade_name')
                    ->whereNull('deleted_at'),
            ],
            'is_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {
            Grade::create([
                'grade_name' => trim($request->grade_name),
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Grade created successfully.');
    }

    // Update Grade
    public function update(Request $request, Grade $grade)
    {
        $request->validate([
            'grade_name' => [
                'required',
                'max:100',
                Rule::unique('grades', 'grade_name')
                    ->ignore($grade->id)
                    ->whereNull('deleted_at'),
            ],
            'is_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $grade) {
            $grade->update([
                'grade_name' => trim($request->grade_name),
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Grade updated successfully.');
    }

    // Delete Grade
    public function destroy(Grade $grade)
    {
        if ($grade->students()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'This grade has students. Cannot delete.');
        }

        $grade->delete();

        return redirect()
            ->back()
            ->with('success', 'Grade deleted successfully.');
    }
}