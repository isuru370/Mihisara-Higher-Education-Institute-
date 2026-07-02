<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Admission;
use App\Models\AdmissionPayment;
use App\Models\StudentIdCard;
use App\Models\TemporaryIdCard;
use App\Services\StudentService;
use App\Exports\StudentsExport;
use App\Models\FcmToken;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Throwable;

class StudentController extends Controller
{
    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index(Request $request)
    {
        $query = Student::with('grade')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('temporary_qr_code', 'like', "%{$search}%")
                    ->orWhere('custom_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        $students = $query->paginate(10)->appends($request->query());

        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        $grades = Grade::orderBy('grade_name')->get();
        $admissions = Admission::active()->orderBy('name')->get();

        return view('admin.students.create', compact('grades', 'admissions'));
    }

    public function store(StoreStudentRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle image upload
            $data['img_url'] = $this->studentService->handleStudentImage(
                $request->file('image'),
                $data['gender'] ?? null
            );

            // Generate custom ID
            $data['custom_id'] = $this->studentService->generateCustomId($data['grade_id']);

            // Validate temporary QR code
            $error = $this->studentService->validateTemporaryQrCode($data['temporary_qr_code']);
            if ($error) {
                DB::rollBack();
                return back()->withInput()->with('error', $error);
            }

            // Create student
            $student = Student::create($data);

            // Assign temporary card
            $this->studentService->assignTemporaryCard($student, $data['temporary_qr_code']);

            // Create student ID card
            $this->studentService->createStudentIdCard($student, 'completed');

            // Create admission payment if applicable
            if ($request->boolean('admission') && $request->filled('admission_id')) {
                $this->studentService->createAdmissionPayment($student, $request->admission_id);
            }

            // Create portal login
            $plainPassword = $this->studentService->createStudentPortalLogin($student);

            DB::commit();

            return back()
                ->with('success', 'Student created successfully.')
                ->with('portal_username', $student->custom_id)
                ->with('portal_password', $plainPassword);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Student create failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Student create failed. Please try again.');
        }
    }

    public function show(Student $student)
    {
        $student->load('grade');
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $grades = Grade::orderBy('grade_name')->get();

        $latestAdmissionPayment = $student->admissionPayments()
            ->where('status', 'paid')
            ->latest()
            ->first();

        $admissions = Admission::withTrashed()
            ->where(function ($query) use ($latestAdmissionPayment) {
                $query->active();
                if ($latestAdmissionPayment?->admission_id) {
                    $query->orWhere('id', $latestAdmissionPayment->admission_id);
                }
            })
            ->orderBy('name')
            ->get();

        return view('admin.students.edit', compact(
            'student',
            'grades',
            'admissions',
            'latestAdmissionPayment'
        ));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        try {
            DB::beginTransaction();

            $data = collect($request->validated())
                ->except(['temporary_qr_code', 'temporary_qr_code_expire_date', 'admission', 'admission_id'])
                ->toArray();

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['img_url'] = $this->studentService->handleStudentImage($request->file('image'));
            }

            $student->update($data);

            // Update student card registration status
            $this->studentService->updateStudentCardRegistrationStatus($student, 'completed');

            // Sync admission payment
            $this->studentService->syncAdmissionPayment(
                $student,
                $request->boolean('admission'),
                $request->filled('admission_id') ? $request->admission_id : null
            );

            DB::commit();

            return redirect()
                ->route('admin.students.show', $student)
                ->with('success', 'Student updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Student update failed', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Student update failed. Please try again.');
        }
    }

    public function toggleActive(Student $student)
    {
        DB::beginTransaction();

        try {

            $isActive = !$student->is_active;

            $student->update([
                'is_active' => $isActive,
            ]);

            // Student inactive කරන විට FCM tokens deactivate කරන්න
            if (!$isActive) {
                FcmToken::where('student_id', $student->id)
                    ->update([
                        'is_active' => false,
                    ]);
            }

            DB::commit();

            return back()->with(
                'success',
                $isActive
                    ? 'Student activated successfully.'
                    : 'Student deactivated successfully.'
            );
        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Student active toggle failed', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Student status update failed.'
            );
        }
    }

    public function destroy(Student $student)
    {
        DB::beginTransaction();

        try {

            // Deactivate all FCM tokens
            FcmToken::where('student_id', $student->id)
                ->update([
                    'is_active' => false,
                ]);

            // Soft delete student
            $student->delete();

            DB::commit();

            return redirect()
                ->route('students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Student delete failed', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Student delete failed.'
            );
        }
    }

    public function exportExcel()
    {
        return Excel::download(new StudentsExport, 'students.xlsx');
    }

    public function exportPdf()
    {
        $students = Student::withTrashed()
            ->with('grade')
            ->orderBy('id')
            ->get();

        $pdf = PDF::loadView('admin.students.pdf', compact('students'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('students.pdf');
    }

    public function studentTemporaryCardExpiredSoon()
    {
        try {
            $today = Carbon::today();
            $nextTenDays = Carbon::today()->addDays(10);

            $students = Student::with('grade')
                ->select(
                    'custom_id',
                    'temporary_qr_code',
                    'temporary_qr_code_expire_date',
                    'full_name',
                    'guardian_mobile',
                    'grade_id',
                    'permanent_qr_active'
                )
                ->where('permanent_qr_active', 0)
                ->whereNotNull('temporary_qr_code_expire_date')
                ->whereDate('temporary_qr_code_expire_date', '<=', $nextTenDays)
                ->orderBy('temporary_qr_code_expire_date')
                ->get();

            $pdf = Pdf::loadView('admin.pdf.student.temporary_card_expired_soon', [
                'students' => $students,
                'today' => $today,
            ]);

            return $pdf->download('temporary-card-expired-soon.pdf');
        } catch (Throwable $e) {
            Log::error('Temporary card report error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->with('error', 'Failed to generate report.');
        }
    }

    public function allStudentDetailsPdf()
    {
        try {
            $students = Student::select(
                'id',
                'custom_id',
                'temporary_qr_code',
                'full_name',
                'guardian_mobile',
                'grade_id',
                'permanent_qr_active'
            )
                ->orderBy('id')
                ->get();

            $permanentQrCount = Student::where('permanent_qr_active', 1)->count();
            $temporaryQrCount = Student::where('permanent_qr_active', 0)->count();

            $pdf = Pdf::loadView('admin.pdf.student.all_students', [
                'students' => $students,
                'permanentQrCount' => $permanentQrCount,
                'temporaryQrCount' => $temporaryQrCount,
            ]);

            return $pdf->download('all-students-report.pdf');
        } catch (Throwable $e) {
            Log::error('Student PDF Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Failed to generate student report.');
        }
    }

    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        return Student::query()
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('custom_id', 'like', "%{$q}%")
                        ->orWhere('temporary_qr_code', 'like', "%{$q}%")
                        ->orWhere('full_name', 'like', "%{$q}%")
                        ->orWhere('initial_name', 'like', "%{$q}%");
                });
            })
            ->orderBy('full_name')
            ->limit(20)
            ->get()
            ->map(fn($student) => [
                'id' => $student->id,
                'text' => "{$student->custom_id} - {$student->full_name}",
            ]);
    }
}
