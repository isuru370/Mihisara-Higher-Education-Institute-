<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Grade;
use App\Models\Student;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Exports\StudentsExport;
use App\Models\Admission;
use App\Models\AdmissionPayment;
use App\Models\StudentIdCard;
use App\Models\StudentPortalLogin;
use App\Models\TemporaryIdCard;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Throwable;

class StudentController extends Controller
{


    public function index(Request $request)
    {
        $query = Student::with('grade')->latest();

        // 🔍 Search
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

        $admissions = Admission::active()
            ->orderBy('name')
            ->get();

        return view('admin.students.create', compact(
            'grades',
            'admissions'
        ));
    }

    public function store(StoreStudentRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Upload image if exists
            if ($request->hasFile('image')) {
                $data['img_url'] = $request->file('image')
                    ->store('uploads/students/original', 'public');
            } else {
                // Default image by gender
                if (($data['gender'] ?? null) === 'female') {
                    $data['img_url'] = 'uploads/female.png';
                } else {
                    $data['img_url'] = 'uploads/male.png';
                }
            }

            $data['custom_id'] = $this->generateCustomId($data['grade_id']);

            $temporaryCard = TemporaryIdCard::where('temporary_id_number', $data['temporary_qr_code'])
                ->lockForUpdate()
                ->first();

            if (! $temporaryCard) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'Temporary QR card not found.');
            }

            if ($temporaryCard->status !== 'issued') {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'This TMP card must be in ISSUED status before assigning to a student.');
            }

            $student = Student::create($data);

            $temporaryCard->update([
                'student_id'   => $student->id,
                'status'       => 'active',
                'activated_at' => now(),
            ]);

            // Create student ID card record
            StudentIdCard::create([
                'student_id'          => $student->id,
                'status'              => 'pending',
                'registration_status' => 'completed',
                'student_fee'         => 350,
                'print_cost'          => 90,
                'profit'              => 260,
                'is_reissue'          => false,
            ]);

            /**
             * Admission payment auto create
             * Checkbox tick + admission_id select කරලා තිබ්බොත් payment create වෙනවා
             */
            if ($request->boolean('admission') && $request->filled('admission_id')) {

                $admission = Admission::active()
                    ->where('id', $request->admission_id)
                    ->first();

                if (! $admission) {
                    DB::rollBack();
                    return back()
                        ->withInput()
                        ->with('error', 'Selected admission not found or inactive.');
                }

                AdmissionPayment::create([
                    'student_id'      => $student->id,
                    'admission_id'    => $admission->id,
                    'amount'          => $admission->amount,
                    'payment_method'  => 'cash',
                    'status'          => AdmissionPayment::STATUS_PAID,
                    'note'            => null,
                    'user_id'         => auth()->id(),
                    'paid_at'         => now(),
                ]);
            }

            $plainPassword = $this->createStudentPortalLogin($student);

            DB::commit();

            return back()
                ->with('success', 'Student created successfully.')
                ->with('portal_username', $student->custom_id)
                ->with('portal_password', $plainPassword);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Student create failed', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Student create failed. Please try again.');
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

        /*
    |--------------------------------------------------------------------------
    | Debug Logs
    |--------------------------------------------------------------------------
    */

        Log::info('Student Edit Debug', [

            'student_id' => $student->id,

            'latest_admission_payment' => $latestAdmissionPayment
                ? $latestAdmissionPayment->toArray()
                : null,

            'selected_admission_id' => $latestAdmissionPayment?->admission_id,

            'admission_ids' => $admissions->pluck('id')->toArray(),

            'admissions' => $admissions->map(function ($admission) {
                return [
                    'id' => $admission->id,
                    'name' => $admission->name,
                    'is_active' => $admission->is_active,
                    'deleted_at' => $admission->deleted_at,
                ];
            })->toArray(),
        ]);

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

            if ($request->hasFile('image')) {
                $data['img_url'] = $request->file('image')
                    ->store('uploads/students/original', 'public');
            }

            $student->update($data);

            // Update only if student_id_cards status is pending
            $studentCard = StudentIdCard::where('student_id', $student->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($studentCard) {
                $studentCard->update([
                    'registration_status' => 'completed',
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Admission Payment Sync
        |--------------------------------------------------------------------------
        | If checkbox is ticked + admission selected:
        |   - create payment if not exists
        |   - otherwise update latest payment
        | If checkbox is unticked:
        |   - mark latest payment as cancelled
        */
            $latestPayment = AdmissionPayment::where('student_id', $student->id)
                ->latest('id')
                ->first();

            if ($request->boolean('admission') && $request->filled('admission_id')) {

                $admission = Admission::active()
                    ->findOrFail($request->admission_id);

                $paymentData = [
                    'student_id'      => $student->id,
                    'admission_id'    => $admission->id,
                    'amount'          => $admission->amount,
                    'payment_method'  => 'cash',
                    'status'          => AdmissionPayment::STATUS_PAID,
                    'note'            => null,
                    'user_id'         => auth()->id(),
                    'paid_at'         => now(),
                ];

                if ($latestPayment) {
                    $latestPayment->update($paymentData);
                } else {
                    AdmissionPayment::create($paymentData);
                }
            } else {

                if ($latestPayment && $latestPayment->status !== AdmissionPayment::STATUS_CANCELLED) {
                    $latestPayment->update([
                        'status' => AdmissionPayment::STATUS_CANCELLED,
                    ]);
                }
            }

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

            return back()
                ->withInput()
                ->with('error', 'Student update failed. Please try again.');
        }
    }

    public function toggleActive(Student $student)
    {
        try {
            $student->update([
                'is_active' => !$student->is_active,
            ]);

            return back()->with(
                'success',
                $student->is_active ? 'Student activated successfully.' : 'Student deactivated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Student active toggle failed', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Student status update failed.');
        }
    }

    public function destroy(Student $student)
    {
        try {
            $student->delete();

            return redirect()
                ->route('students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (Exception $e) {
            Log::error('Student delete failed', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Student delete failed.');
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

    private function generateCustomId(int $gradeId): string
    {
        $grade = Grade::findOrFail($gradeId);

        $gradeName = trim($grade->grade_name);
        $gradeCode = '';

        if (preg_match('/^Grade\s+(\d+)$/i', $gradeName, $matches)) {
            $gradeCode = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/^(\d{4})\s+(A\/L|O\/L)$/i', $gradeName, $matches)) {
            $gradeCode = substr($matches[1], -2);
        } elseif (preg_match('/^\d{4}$/', $gradeName)) {
            $gradeCode = substr($gradeName, -2);
        } elseif (preg_match('/(\d+)/', $gradeName, $matches)) {
            $num = $matches[1];

            $gradeCode = strlen($num) === 4
                ? substr($num, -2)
                : str_pad($num, 2, '0', STR_PAD_LEFT);
        } else {
            $gradeCode = str_pad($gradeId, 2, '0', STR_PAD_LEFT);
        }

        $lastStudent = Student::where('grade_id', $gradeId)
            ->where('custom_id', 'like', 'SA' . $gradeCode . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $lastNumber = 0;

        if ($lastStudent && preg_match('/^SA' . $gradeCode . '(\d+)$/', $lastStudent->custom_id, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        do {
            $lastNumber++;
            $customId = 'SA' . $gradeCode . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
        } while (Student::where('custom_id', $customId)->exists());

        return $customId;
    }

    private function createStudentPortalLogin(Student $student): string
    {
        $plainPassword = $this->generateStudentPassword(
            $student->full_name,
            $student->mobile
        );

        StudentPortalLogin::create([
            'student_id' => $student->id,
            'username' => $student->custom_id,
            'password' => Hash::make($plainPassword),

            'is_verified' => true,
            'is_active' => true,

            'otp' => null,
            'otp_expires_at' => null,
            'last_login_at' => null,
        ]);

        return $plainPassword;
    }

    private function generateStudentPassword(string $name, string $mobile): string
    {
        $source = strtolower(trim($name)) . preg_replace('/\D/', '', $mobile);

        $number = abs(crc32($source));

        return str_pad(substr((string) $number, 0, 8), 8, '0', STR_PAD_LEFT);
    }

    // StudentController

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
                ->whereDate(
                    'temporary_qr_code_expire_date',
                    '<=',
                    $nextTenDays
                )
                ->orderBy('temporary_qr_code_expire_date')
                ->get();

            $pdf = Pdf::loadView(
                'admin.pdf.student.temporary_card_expired_soon',
                [
                    'students' => $students,
                    'today' => $today,
                ]
            );

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

            $pdf = Pdf::loadView(
                'admin.pdf.student.all_students',
                [
                    'students' => $students,
                    'permanentQrCount' => $permanentQrCount,
                    'temporaryQrCount' => $temporaryQrCount,
                ]
            );

            return $pdf->download('all-students-report.pdf');
        } catch (Throwable $e) {

            Log::error('Student PDF Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with(
                'error',
                'Failed to generate student report.'
            );
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
