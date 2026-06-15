<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TeachersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Models\BankBranch;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ClassPaymentConfig;
use App\Models\StudentClass;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::with('bankBranch.bank')->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('custom_id', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('initials', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        if ($request->filled('bank_branch_id')) {
            $query->where('bank_branch_id', $request->bank_branch_id);
        }

        $teachers = $query
            ->paginate(10)
            ->appends($request->query());

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        $bankBranches = BankBranch::with('bank')
            ->orderBy('branch_name')
            ->get();

        return view('admin.teachers.create', compact('bankBranches'));
    }

    public function store(StoreTeacherRequest $request)
    {
        $data = $request->validated();

        $data['is_active'] = $request->boolean('is_active', true);
        $data['custom_id'] = $this->generateCustomId();

        Teacher::create($data);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load([
            'bankBranch.bank',
            'classes.subject',
            'classes.grade',
        ]);

        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $bankBranches = BankBranch::with('bank')
            ->orderBy('branch_name')
            ->get();

        return view('admin.teachers.edit', compact('teacher', 'bankBranches'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $data = $request->validated();

        $data['is_active'] = $request->boolean('is_active', true);

        $teacher->update($data);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    public function toggleActive(Teacher $teacher)
{
    try {
        DB::transaction(function () use ($teacher) {
            $newStatus = !$teacher->is_active;

            $teacher->update([
                'is_active' => $newStatus,
            ]);

            StudentClass::where('teacher_id', $teacher->id)
                ->update([
                    'is_active' => $newStatus,
                ]);

            ClassPaymentConfig::where('teacher_id', $teacher->id)
                ->update([
                    'is_active' => $newStatus,
                ]);
        });

        return back()->with(
            'success',
            $teacher->is_active
                ? 'Teacher activated successfully.'
                : 'Teacher deactivated successfully.'
        );
    } catch (Exception $e) {
        Log::error('Teacher active toggle failed', [
            'teacher_id' => $teacher->id,
            'error' => $e->getMessage(),
        ]);

        return back()->with('error', 'Teacher status update failed.');
    }
}



    public function destroy(Teacher $teacher)
{
    try {
        DB::transaction(function () use ($teacher) {
            StudentClass::where('teacher_id', $teacher->id)
                ->update([
                    'is_active' => false,
                ]);

            ClassPaymentConfig::where('teacher_id', $teacher->id)
                ->update([
                    'is_active' => false,
                ]);

            StudentClass::where('teacher_id', $teacher->id)->delete();

            ClassPaymentConfig::where('teacher_id', $teacher->id)->delete();

            $teacher->delete();
        });

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Teacher deleted successfully.');
    } catch (Exception $e) {
        Log::error('Teacher delete failed', [
            'teacher_id' => $teacher->id,
            'error' => $e->getMessage(),
        ]);

        return back()->with('error', 'Teacher delete failed.');
    }
}

    private function generateCustomId()
    {
        $lastTeacher = Teacher::orderBy('id', 'desc')->first();

        if (!$lastTeacher) {
            return 'SAT001';
        }

        $lastCustomId = $lastTeacher->custom_id;
        $lastNumber = (int) substr($lastCustomId, 3);
        $nextNumber = $lastNumber + 1;

        return 'SAT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function exportExcel()
    {
        return Excel::download(new TeachersExport, 'teachers.xlsx');
    }

    public function exportPdf()
    {
        $teachers = Teacher::with('bankBranch.bank')
            ->orderBy('id')
            ->get();

        $pdf = PDF::loadView('admin.teachers.pdf', compact('teachers'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('teachers.pdf');
    }
}
