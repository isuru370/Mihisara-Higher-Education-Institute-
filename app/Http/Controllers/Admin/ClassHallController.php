<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassHall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ClassHallController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassHall::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('hall_name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        $halls = $query->latest()->paginate(10)->appends($request->query());

        return view('admin.class_halls.index', compact('halls'));
    }

    public function create()
    {
        return view('admin.class_halls.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:class_halls,code',
            'hall_name' => 'required|string|max:150',
            'hall_type' => 'nullable|string|max:50',
            'hall_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        ClassHall::create([
            'code' => strtoupper($validated['code']),
            'hall_name' => $validated['hall_name'],
            'hall_type' => $validated['hall_type'] ?? null,
            'hall_price' => $validated['hall_price'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.class-halls.index')
            ->with('success', 'Class hall created successfully.');
    }

    public function show(ClassHall $classHall)
    {
        return view('admin.class_halls.show', compact('classHall'));
    }

    public function edit(ClassHall $classHall)
    {
        return view('admin.class_halls.edit', compact('classHall'));
    }

    public function update(Request $request, ClassHall $classHall)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:class_halls,code,' . $classHall->id,
            'hall_name' => 'required|string|max:150',
            'hall_type' => 'nullable|string|max:50',
            'hall_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $classHall->update([
            'code' => strtoupper($validated['code']),
            'hall_name' => $validated['hall_name'],
            'hall_type' => $validated['hall_type'] ?? null,
            'hall_price' => $validated['hall_price'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.class-halls.index')
            ->with('success', 'Class hall updated successfully.');
    }

    public function destroy(ClassHall $classHall)
    {
        try {
            if ($classHall->schedules()->exists()) {
                return back()->with('error', 'Cannot delete hall. It is used in schedules.');
            }

            $classHall->delete();

            return redirect()
                ->route('admin.class-halls.index')
                ->with('success', 'Class hall deleted successfully.');
        } catch (Exception $e) {
            Log::error('Class hall delete failed', [
                'hall_id' => $classHall->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Class hall delete failed.');
        }
    }

    public function toggleActive(ClassHall $classHall)
    {
        $classHall->update([
            'is_active' => !$classHall->is_active,
        ]);

        return back()->with('success', 'Hall status updated.');
    }
}
