<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ClassCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassCategory::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('category_name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        if ($request->filled('is_schedulable')) {
            $query->where('is_schedulable', $request->is_schedulable === 'true');
        }

        $categories = $query
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return view('admin.class_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.class_categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:class_categories,category_name',
            'code' => 'required|string|max:50|unique:class_categories,code',
            'is_schedulable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        ClassCategory::create([
            'category_name' => trim($validated['category_name']),
            'code' => strtoupper(trim($validated['code'])),
            'is_schedulable' => $request->boolean('is_schedulable', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.class-categories.index')
            ->with('success', 'Class category created successfully.');
    }

    public function show(ClassCategory $classCategory)
    {
        return view('admin.class_categories.show', compact('classCategory'));
    }

    public function edit(ClassCategory $classCategory)
    {
        return view('admin.class_categories.edit', compact('classCategory'));
    }

    public function update(Request $request, ClassCategory $classCategory)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:class_categories,category_name,' . $classCategory->id,
            'code' => 'required|string|max:50|unique:class_categories,code,' . $classCategory->id,
            'is_schedulable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $classCategory->update([
            'category_name' => trim($validated['category_name']),
            'code' => strtoupper(trim($validated['code'])),
            'is_schedulable' => $request->boolean('is_schedulable'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.class-categories.index')
            ->with('success', 'Class category updated successfully.');
    }

    public function destroy(ClassCategory $classCategory)
    {
        try {
            if ($classCategory->classCategoryFees()->exists()) {
                return back()->with('error', 'Cannot delete category. It is used in class category fees.');
            }

            $classCategory->delete();

            return redirect()
                ->route('admin.class-categories.index')
                ->with('success', 'Class category deleted successfully.');
        } catch (Exception $e) {
            Log::error('Class category delete failed', [
                'category_id' => $classCategory->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Class category delete failed.');
        }
    }

    public function toggleActive(ClassCategory $classCategory)
    {
        // 🔥 if trying to deactivate
        if ($classCategory->is_active) {

            $hasActiveFees = $classCategory->classCategoryFees()
                ->where('is_active', true)
                ->exists();

            if ($hasActiveFees) {
                return back()->with(
                    'error',
                    'Cannot deactivate category. It is used in class category fees.'
                );
            }
        }

        $classCategory->update([
            'is_active' => !$classCategory->is_active,
        ]);

        return back()->with('success', 'Category status updated.');
    }
}
