<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Teacher;
use App\Models\TemporaryIdCard;
use App\Models\StudentIdCard;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $studentsCount = Student::count();
        $teachersCount = Teacher::count();
        $classesCount = StudentClass::count();

        $todayIncome = Payment::whereDate('paid_at', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $monthlyIncome = Payment::whereMonth('paid_at', $currentMonth)
            ->whereYear('paid_at', $currentYear)
            ->where('status', 'completed')
            ->sum('amount');

        $temporaryIdCardPendingCount = TemporaryIdCard::issued()->count();
        $showTemporaryIdCardWarning = $temporaryIdCardPendingCount < 10;

        $incompleteRegistrationCount = StudentIdCard::where('registration_status', 'incomplete')->count();

        $incompleteRegistrations = StudentIdCard::with([
            'student:id,custom_id,temporary_qr_code,initial_name,guardian_mobile'
        ])
            ->where('registration_status', 'incomplete')
            ->latest()
            ->take(5)
            ->get();

        $latestStudents = Student::latest()
            ->take(5)
            ->get(['id', 'custom_id', 'initial_name', 'guardian_mobile', 'created_at']);

        $expiringStudents = Student::select(
            'id',
            'temporary_qr_code',
            'initial_name',
            'guardian_mobile',
            'temporary_qr_code_expire_date'
        )
            ->where('permanent_qr_active', 0)
            ->whereNotNull('temporary_qr_code_expire_date')
            ->whereBetween('temporary_qr_code_expire_date', [
                Carbon::today(),
                Carbon::today()->addDays(10),
            ])
            ->orderBy('temporary_qr_code_expire_date')
            ->get();

        $expiringStudentsCount = $expiringStudents->count();

        return view('admin.dashboard.index', compact(
            'studentsCount',
            'teachersCount',
            'classesCount',
            'todayIncome',
            'monthlyIncome',
            'temporaryIdCardPendingCount',
            'showTemporaryIdCardWarning',
            'incompleteRegistrationCount',
            'incompleteRegistrations',
            'latestStudents',
            'expiringStudents',
            'expiringStudentsCount'
        ));
    }
}
