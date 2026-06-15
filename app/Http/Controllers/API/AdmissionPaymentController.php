<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionPayment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdmissionPaymentController extends Controller
{
    public function fetchAdmission(Request $request)
    {
        try {

            $admissions = Admission::active()
                ->select('id', 'name', 'amount')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $admissions,
            ]);
        } catch (\Throwable $e) {

            Log::error('Failed to fetch admissions.', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admissions.',
            ], 500);
        }
    }

    public function fetchAdmissionPayment(Request $request)
    {
        try {

            $paidStudents = AdmissionPayment::with([
                'admission:id,name,amount',
                'student:id,custom_id,initial_name,grade_id,guardian_mobile',
                'student.grade:id,grade_name',
                'collectedBy:id,name',
            ])
                ->latest()
                ->get()
                ->map(function ($payment) {

                    return [
                        'payment_id' => $payment->id,
                        'receipt_number' => $payment->receipt_number,

                        'student' => [
                            'id' => $payment->student?->id,
                            'custom_id' => $payment->student?->custom_id,
                            'name' => $payment->student?->initial_name,
                            'grade' => $payment->student?->grade?->grade_name,
                            'guardian_mobile' => $payment->student?->guardian_mobile,
                        ],

                        'admission' => [
                            'id' => $payment->admission?->id,
                            'name' => $payment->admission?->name,
                            'amount' => $payment->admission?->amount,
                        ],

                        'payment' => [
                            'amount' => $payment->amount,
                            'payment_method' => $payment->payment_method,
                            'status' => $payment->status,
                            'paid_at' => $payment->paid_at,
                            'collected_by' => $payment->collectedBy?->name,
                            'note' => $payment->note,
                        ],
                    ];
                });

            $unpaidStudents = Student::with([
                'grade:id,grade_name'
            ])
                ->where('admission', false)
                ->latest()
                ->get()
                ->map(function ($student) {

                    return [
                        'id' => $student->id,
                        'custom_id' => $student->custom_id,
                        'name' => $student->initial_name,
                        'grade' => $student->grade?->grade_name,
                        'guardian_mobile' => $student->guardian_mobile,
                        'temporary_qr_code' => $student->temporary_qr_code,
                        'admission' => false,
                    ];
                });

            return response()->json([
                'success' => true,

                'summary' => [
                    'paid_count' => $paidStudents->count(),
                    'unpaid_count' => $unpaidStudents->count(),
                    'total_count' => $paidStudents->count() + $unpaidStudents->count(),
                ],

                'paid_students' => $paidStudents,

                'unpaid_students' => $unpaidStudents,

            ]);
        } catch (\Throwable $e) {

            Log::error('Failed to fetch admission details.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admission details.',
            ], 500);
        }
    }

    public function storeAdmissionPayment(Request $request)
    {
        try {

            $request->validate([
                'student_id'   => 'required|exists:students,id',
                'admission_id' => 'required|exists:admissions,id',
            ]);

            $admission = Admission::active()
                ->findOrFail($request->admission_id);

            $payment = AdmissionPayment::create([
                'student_id'   => $request->student_id,
                'admission_id' => $admission->id,
                'amount'       => $admission->amount,
            ]);

            Student::where('id', $request->student_id)
                ->update([
                    'admission' => true,
                ]);

            $student = Student::with('grade')
                ->findOrFail($request->student_id);

            $payment->load('admission');

            return response()->json([
                'success' => true,
                'message' => 'Admission payment recorded successfully.',

                'student' => $student,

                'admission_payment' => [
                    'id' => $payment->id,
                    'receipt_number' => $payment->receipt_number,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'paid_at' => optional($payment->paid_at)
                        ->toDateTimeString(),
                    'payment_method' => $payment->payment_method,
                    'admission_name' => optional($payment->admission)->name,
                    'note' => $payment->note,
                ],
            ]);
        } catch (\Throwable $e) {

            Log::error('Failed to store admission payment.', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record admission payment.',
            ], 500);
        }
    }
}
