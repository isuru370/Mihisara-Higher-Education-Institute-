<?php

namespace App\Services;

use App\Models\StudentClassEnrollment;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class StudentClassEnrollmentService
{

    public function classCategoryWisePaymentStudent(
        int $classId,
        int $classCategoryFeeId,
        int $year,
        int $month,
        int $perPage = 50  // පිටුවකට students 50 බැගින්
    ): array {

        try {

            $enrollments = StudentClassEnrollment::query()

                ->where('student_class_id', $classId)

                ->where('class_category_fee_id', $classCategoryFeeId)

                ->where('is_active', true)

                ->with([
                    'student:id,initial_name,custom_id,temporary_qr_code,mobile',

                    'payments' => function ($query) use ($year, $month) {
                        $query
                            ->where('status', 'completed')
                            ->whereYear('paid_at', $year)
                            ->whereMonth('paid_at', $month)
                            ->orderBy('paid_at', 'desc');
                    }
                ])

                ->withSum([
                    'payments as monthly_paid_amount' => function ($query) use ($year, $month) {
                        $query
                            ->where('status', 'completed')
                            ->whereYear('paid_at', $year)
                            ->whereMonth('paid_at', $month);
                    }
                ], 'amount')

                // Pagination එක එකතු කිරීම
                ->paginate($perPage);

            // Return paginated data with formatted students
            return [
                'students' => $this->formatStudents($enrollments->items()),
                'pagination' => [
                    'current_page' => $enrollments->currentPage(),
                    'last_page' => $enrollments->lastPage(),
                    'per_page' => $enrollments->perPage(),
                    'total' => $enrollments->total(),
                    'from' => $enrollments->firstItem(),
                    'to' => $enrollments->lastItem(),
                    'has_more_pages' => $enrollments->hasMorePages(),
                    'next_page_url' => $enrollments->nextPageUrl(),
                    'previous_page_url' => $enrollments->previousPageUrl(),
                ]
            ];
        } catch (Throwable $e) {

            Log::error('Class Category Wise Payment Student Service Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'class_id' => $classId,
                'class_category_fee_id' => $classCategoryFeeId,
                'year' => $year,
                'month' => $month,
            ]);

            return [
                'students' => [],
                'pagination' => []
            ];
        }
    }

    // Format students method
    private function formatStudents($enrollments)
    {
        return collect($enrollments)->map(function ($enrollment) {

            $payments = $enrollment->payments->map(function ($payment) {
                return [
                    'payment_id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'paid_at' => optional($payment->paid_at)->format('Y-m-d h:i A'),
                    'payment_month' => optional($payment->payment_month)->format('Y-m'),
                    'payment_method' => $payment->payment_method,
                    'receipt_number' => $payment->receipt_number,
                    'reference_number' => $payment->reference_number,
                ];
            });

            return [
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student?->id,
                'student_custom_id' => $enrollment->student?->permanent_qr_active == 1
                    ? $enrollment->student?->custom_id
                    : $enrollment->student?->temporary_qr_code,
                'student_name' => $enrollment->student?->initial_name,
                'mobile' => $enrollment->student?->mobile,

                'is_free_card' => (bool) $enrollment->is_free_card,
                'custom_fee' => $enrollment->custom_fee ? (float) $enrollment->custom_fee : null,
                'custom_fee_reason' => $enrollment->custom_fee_reason,
                'discount_percentage' => $enrollment->discount_percentage ? (float) $enrollment->discount_percentage : 0,
                'discount_reason' => $enrollment->discount_reason,

                'final_fee' => (float) $enrollment->final_fee,

                'monthly_paid_amount' => (float) ($enrollment->monthly_paid_amount ?? 0),
                'payment_status' => $enrollment->payment_status,

                'payments' => $payments,
            ];
        })->toArray();
    }
}
