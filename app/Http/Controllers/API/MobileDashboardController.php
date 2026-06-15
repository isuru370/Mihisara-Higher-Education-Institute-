<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Teacher;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Throwable;

class MobileDashboardController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function mobileDashboardDetails(): JsonResponse
    {
        try {
            $now = now();
            $today = $now->toDateString();
            $currentMonth = $now->month;
            $currentYear = $now->year;

            $smsBalance = $this->smsService->getBalance();

            $totalStudent = Student::count();
            $totalTeacher = Teacher::count();
            $totalClasses = StudentClass::count();

            $todayClassCount = ClassSchedule::whereDate('class_date', $today)->count();

            $todayPaymentCollection = Payment::whereBetween('created_at', [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ])->sum('amount');

            $currentMonthAdmissionCount = Student::whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count();

            $currentMonthAdmissions = Student::whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->latest()
                ->take(5)
                ->get([
                    'id',
                    'custom_id',
                    'temporary_qr_code',
                    'initial_name',
                    'gender',
                    'guardian_mobile',
                ]);

            $temporaryStudentCount = Student::where('permanent_qr_active', false)->count();
            $permanentStudentCount = Student::where('permanent_qr_active', true)->count();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard details fetched successfully',
                'data' => [
                    'sms_balance' => $smsBalance,
                    'total_student' => $totalStudent,
                    'total_teacher' => $totalTeacher,
                    'total_classes' => $totalClasses,
                    'today_class_count' => $todayClassCount,
                    'today_payment_collection' => $todayPaymentCollection,
                    'current_month_admission_count' => $currentMonthAdmissionCount,
                    'current_month_admissions' => $currentMonthAdmissions,
                    'temporary_student_count' => $temporaryStudentCount,
                    'permanent_student_count' => $permanentStudentCount,
                ]
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}