<?php

namespace App\Http\Controllers\API;

use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use App\Jobs\SendPaymentSms;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\StudentClassEnrollment;
use App\Services\Notification\PaymentNotificationService;
use App\Services\ReceiptNumberService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StudentPaymentController extends Controller
{
     protected PaymentNotificationService $paymentNotification;

     public function __construct(PaymentNotificationService $paymentNotification)
    {
        $this->paymentNotification = $paymentNotification;
    }
    public function todayReceipt(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', 'in:5,10,15,20,50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = $validated['search'] ?? null;
        $date = $validated['date'] ?? today()->toDateString();
        $perPage = (int) ($validated['per_page'] ?? 10);

        $baseQuery = StudentClassEnrollment::with([
            'student',
            'studentClass.teacher',
            'studentClass.grade',
            'classCategoryFee.category',
            'payments' => function ($query) use ($date) {
                $query->whereDate('created_at', $date)->latest();
            },
        ])
            ->whereHas('payments', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('initial_name', 'like', "%{$search}%")
                            ->orWhere('custom_id', 'like', "%{$search}%")
                            ->orWhere('temporary_qr_code', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('guardian_mobile', 'like', "%{$search}%");
                    })
                        ->orWhereHas('studentClass', function ($classQuery) use ($search) {
                            $classQuery->where('class_name', 'like', "%{$search}%")
                                ->orWhereHas('teacher', function ($teacherQuery) use ($search) {
                                    $teacherQuery->where('initials', 'like', "%{$search}%")
                                        ->orWhere('custom_id', 'like', "%{$search}%");
                                })
                                ->orWhereHas('grade', function ($gradeQuery) use ($search) {
                                    $gradeQuery->where('grade_name', 'like', "%{$search}%");
                                });
                        })
                        ->orWhereHas('classCategoryFee.category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('category_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();

        $summaryRows = (clone $baseQuery)->get();

        $summary = [
            'enrollments' => $summaryRows->count(),
            'payment_count' => $summaryRows->sum(fn($enrollment) => $enrollment->payments->count()),
            'total_amount' => $summaryRows->sum(fn($enrollment) => $enrollment->payments->sum('amount')),
        ];

        $paginated = (clone $baseQuery)->paginate($perPage)->appends($request->query());

        $data = collect($paginated->items())->map(function ($enrollment) {
            $student = $enrollment->student;
            $todayPayments = $enrollment->payments;

            return [
                'enrollment_id' => $enrollment->id,
                'student_id' => $student?->id,
                'initial_name' => $student?->initial_name,
                'mobile' => $student?->mobile,
                'guardian_mobile' => $student?->guardian_mobile,
                'qr_code' => $student?->permanent_qr_active == 1
                    ? $student?->custom_id
                    : $student?->temporary_qr_code,
                'student_class_id' => $enrollment->studentClass?->id,
                'class_name' => $enrollment->studentClass?->class_name,
                'teacher_custom_id' => $enrollment->studentClass?->teacher?->custom_id,
                'teacher_name' => $enrollment->studentClass?->teacher?->initials,
                'grade_name' => $enrollment->studentClass?->grade?->grade_name,
                'category_name' => $enrollment->classCategoryFee?->category?->category_name,
                'is_free_card' => $enrollment->is_free_card,
                'final_fee' => $enrollment->final_fee,
                'payment_status' => $enrollment->payment_status,
                'balance' => $enrollment->balance,
                'today_payment_count' => $todayPayments->count(),
                'today_payment_total' => $todayPayments->sum('amount'),
                'today_payments' => $todayPayments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'paid_at' => $payment->paid_at,
                        'payment_month' => $payment->payment_month,
                        'payment_method' => $payment->payment_method,
                        'status' => $payment->status,
                        'receipt_number' => $payment->receipt_number,
                        'note' => $payment->note,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'summary' => $summary,
            'filters' => [
                'date' => $date,
                'search' => $search,
                'per_page' => $perPage,
            ],
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
        ]);
    }
     public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.student_id' => ['required', 'exists:students,id'],
            'payments.*.student_class_enrollment_id' => ['required', 'exists:student_class_enrollments,id'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payments.*.payment_month' => ['required', 'date_format:Y-m'],
            'payments.*.paid_at' => ['nullable', 'date'],
            'payments.*.mark_method' => ['required', 'string'],
            'payments.*.note' => ['nullable', 'string'],
        ]);

        try {
            $createdPayments = DB::transaction(function () use ($validated) {
                $results = [];

                foreach ($validated['payments'] as $item) {
                    $enrollment = StudentClassEnrollment::query()
                        ->where('id', $item['student_class_enrollment_id'])
                        ->where('student_id', $item['student_id'])
                        ->where('is_active', true)
                        ->firstOrFail();

                    $paymentMonth = Carbon::createFromFormat('Y-m-d', $item['payment_month'] . '-01');

                    $payment = Payment::create([
                        'student_id' => $item['student_id'],
                        'student_class_enrollment_id' => $enrollment->id,
                        'user_id' => auth()->id(),
                        'mark_method' => $item['mark_method'],
                        'amount' => $item['amount'],
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'paid_at' => $item['paid_at'] ?? now(),
                        'payment_month' => $paymentMonth->toDateString(),
                        'payment_method' => 'cash',
                        'status' => 'completed',
                        'receipt_number' => ReceiptNumberService::generate(),
                        'reference_number' => $this->generateReferenceNumber(),
                        'is_synced' => true,
                        'note' => $item['note'] ?? $paymentMonth->format('F Y') . ' month paid',
                    ]);

                    $payment->loadMissing([
                        'student',
                        'enrollment.studentClass.grade',
                        'enrollment.classCategoryFee.category',
                    ]);

                    $results[] = $payment;
                }

                return $results;
            });

            // ============================================
            // 🚀 SEND NOTIFICATION FOR EACH PAYMENT
            // ============================================
            foreach ($createdPayments as $payment) {
                $this->paymentNotification->sendSuccess($payment);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payments saved successfully',
                'data' => [
                    'student' => [
                        'id' => $createdPayments[0]->student?->id,
                        'name' => $createdPayments[0]->student?->initial_name,
                        'guardian_mobile' => $createdPayments[0]->student?->guardian_mobile,
                    ],
                    'receipt' => [
                        'payment_month' => Carbon::parse($createdPayments[0]->payment_month)->format('Y-m'),
                        'total_fee' => (float) collect($createdPayments)->sum('amount'),
                        'discount_amount' => (float) collect($createdPayments)->sum('discount_amount'),
                        'payable_total' => (float) collect($createdPayments)->sum('amount'),
                        'items' => collect($createdPayments)->map(function ($payment) {
                            return [
                                'payment_id' => $payment->id,
                                'receipt_number' => $payment->receipt_number,
                                'reference_number' => $payment->reference_number,
                                'class_name' => $payment->enrollment?->studentClass?->class_name,
                                'grade' => $payment->enrollment?->studentClass?->grade?->grade_name,
                                'category_name' => $payment->enrollment?->classCategoryFee?->category?->category_name,
                                'amount' => (float) $payment->amount,
                                'discount_amount' => (float) $payment->discount_amount,
                                'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
                            ];
                        })->values(),
                    ],
                    'count' => count($createdPayments),
                ],
            ], 201);

        } catch (Throwable $e) {
            Log::error('Bulk payment store failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while saving payments',
                'data' => [],
            ], 500);
        }
    }

    public function destroy(int $paymentId): JsonResponse
    {
        DB::beginTransaction();

        try {
            $payment = Payment::with('splitSnapshot')
                ->findOrFail($paymentId);

            // allow delete only within 14 days
            if ($payment->created_at->lt(now()->subDays(14))) {

                return response()->json([
                    'success' => false,
                    'message' => 'This payment can only be deleted within 14 days.'
                ], 403);
            }
            // delete split snapshot
            $payment->splitSnapshot()?->forceDelete();
            $payment->forceDelete();

            // laravel log
            Log::info('Payment deleted successfully.', [
                'payment_id' => $paymentId,
                'deleted_by' =>  auth()->id(),
                'deleted_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully.'
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            // error log
            Log::error('Payment delete failed.', [
                'payment_id' => $paymentId,
                'user_id' =>  auth()->id(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting payment.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'student_class_enrollment_id' => ['required', 'exists:student_class_enrollments,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_month' => ['required', 'date_format:Y-m'],
            'paid_at' => ['nullable', 'date'],
        ]);
    }

    private function createPayment(array $validated): Payment
    {
        $enrollment = $this->findEnrollment($validated);

        $paymentMonth = $this->makePaymentMonth(
            $validated['payment_month']
        );

        return Payment::create([
            'student_id' => $validated['student_id'],
            'student_class_enrollment_id' => $enrollment->id,
            'user_id' => auth()->id(),
            'amount' => $validated['amount'],
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'paid_at' => $validated['paid_at'] ?? now(),
            'payment_month' => $paymentMonth->toDateString(),
            'payment_method' => 'cash',
            'status' => 'completed',
            'receipt_number' => $this->generateReceiptNumber(),
            'reference_number' => $this->generateReferenceNumber(),
            'is_synced' => true,
            'note' => $this->generatePaymentNote($paymentMonth),
        ]);
    }

    private function findEnrollment(array $validated): StudentClassEnrollment
    {
        return StudentClassEnrollment::query()
            ->where('id', $validated['student_class_enrollment_id'])
            ->where('student_id', $validated['student_id'])
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function makePaymentMonth(string $month): Carbon
    {
        return Carbon::createFromFormat(
            'Y-m-d',
            $month . '-01'
        );
    }

    private function generatePaymentNote(Carbon $paymentMonth): string
    {
        return $paymentMonth->format('F Y') . ' month paid';
    }

    private function sendPaymentSmsIfAvailable(Payment $payment): void
    {
        $guardianMobile = $payment->student?->guardian_mobile;

        if (! $guardianMobile) {
            return;
        }

        $smsMessage = $this->buildSmsMessage($payment);

        SendPaymentSms::dispatch(
            $guardianMobile,
            $smsMessage
        );
    }

    private function buildSmsMessage(Payment $payment): string
    {
        return sprintf(
            'Payment received. Student: %s, Class: %s, Category: %s, Grade: %s, Amount: Rs. %s, Month: %s, Receipt: %s. Thank you.',
            $payment->student?->initial_name ?? 'Student',
            $payment->enrollment?->studentClass?->class_name ?? 'N/A',
            $payment->enrollment?->classCategoryFee?->category?->category_name ?? 'N/A',
            $payment->enrollment?->studentClass?->grade?->grade_name ?? 'N/A',
            number_format((float) $payment->amount, 2),
            Carbon::parse($payment->payment_month)->format('Y-m') ?? '-',
            $payment->receipt_number ?? '-'
        );
    }

    private function successResponse(Payment $payment): JsonResponse
    {
        $guardianMobile = $payment->student?->guardian_mobile;

        return response()->json([
            'status' => 'success',
            'message' => 'Payment saved successfully',
            'data' => [
                'payment_id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'reference_number' => $payment->reference_number,
                'payment_method' => $payment->payment_method,
                'payment_month' => Carbon::parse($payment->payment_month)->format('Y-m'),
                'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
                'note' => $payment->note,
                'guardian_mobile' => $guardianMobile,
                'sms_queued' => (bool) $guardianMobile,
            ],
        ], 201);
    }

    private function errorResponse(string $message): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => [],
        ], 500);
    }

    private function logStoreError(Throwable $e): void
    {
        Log::error('Payment store failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    private function logDeleteError(
        Payment $payment,
        Throwable $e
    ): void {
        Log::error('Payment delete failed', [
            'payment_id' => $payment->id,
            'message' => $e->getMessage(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Reference Number
    |--------------------------------------------------------------------------
    */
    private function generateReferenceNumber(): string
    {
        do {

            $number = 'PAY-' .
                now()->format('YmdHis') .
                '-' .
                random_int(1000, 9999);
        } while (
            Payment::where('reference_number', $number)->exists()
        );

        return $number;
    }

    public function todayPayments(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
            ]);

            $date = Carbon::parse($request->date);

            $payments = Payment::query()
                ->select([
                    'id',
                    'student_id',
                    'student_class_enrollment_id',
                    'mark_method',
                    'amount',
                    'discount_amount',
                    'paid_at',
                    'payment_month',
                    'receipt_number',
                ])
                ->with([
                    'student:id,custom_id,temporary_qr_code,initial_name,guardian_mobile,img_url,permanent_qr_active',
                    'enrollment:id,student_class_id,class_category_fee_id', // Add class_category_fee_id
                    'enrollment.studentClass:id,class_name,grade_id,subject_id,teacher_id',
                    'enrollment.studentClass.grade:id,grade_name',
                    'enrollment.studentClass.subject:id,subject_name',
                    'enrollment.studentClass.teacher:id,initials',
                    'enrollment.classCategoryFee:id,class_category_id,fee', // Load through enrollment
                    'enrollment.classCategoryFee.category:id,category_name,code', // Load category
                ])
                ->where('status', 'completed')
                ->whereDate('paid_at', $date->toDateString())
                ->orderByDesc('paid_at')
                ->get()
                ->map(function ($payment) {
                    // Get category from enrollment's classCategoryFee
                    $category = $payment->enrollment?->classCategoryFee?->category;

                    return [
                        'payment' => [
                            'id' => $payment->id,
                            'mark_method' => $payment->mark_method,
                            'amount' => $payment->amount,
                            'discount_amount' => $payment->discount_amount,
                            'paid_at' => optional($payment->paid_at)?->toDateTimeString(),
                            'payment_month' => optional($payment->payment_month)?->toDateString(),
                            'receipt_number' => $payment->receipt_number,
                        ],
                        'student' => [
                            'id' => $payment->student?->id,
                            'custom_id' => $payment->student?->permanent_qr_active == 1
                                ? $payment->student?->custom_id
                                : $payment->student?->temporary_qr_code,
                            'initial_name' => $payment->student?->initial_name,
                            'guardian_mobile' => $payment->student?->guardian_mobile,
                            'img_url' => $payment->student?->img_url,
                        ],
                        'student_class_enrollment' => [
                            'id' => $payment->enrollment?->id,
                        ],
                        'student_class' => [
                            'id' => $payment->enrollment?->studentClass?->id,
                            'class_name' => $payment->enrollment?->studentClass?->class_name,
                            'grade' => [
                                'grade_name' => $payment->enrollment?->studentClass?->grade?->grade_name,
                            ],
                            'subject' => [
                                'subject_name' => $payment->enrollment?->studentClass?->subject?->subject_name,
                            ],
                            'teacher' => [
                                'id' => $payment->enrollment?->studentClass?->teacher?->id,
                                'initials' => $payment->enrollment?->studentClass?->teacher?->initials,
                            ],
                            'category' => [
                                'id' => $category?->id,
                                'category_name' => $category?->category_name,
                                'code' => $category?->code,
                            ],
                        ],
                    ];
                });

            return response()->json([
                'success' => true,
                'date' => $date->toDateString(),
                'count' => $payments->count(),
                'data' => $payments,
            ]);
        } catch (Throwable $e) {
            Log::error('Today Payments Fetch Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching today payments.',
            ], 500);
        }
    }


    public function fetchStudentAllPayment(int $studentId, int $enrolledId)
    {
        try {
            $payments = Payment::query()
                ->select([
                    'id',
                    'student_id',
                    'student_class_enrollment_id',
                    'mark_method',
                    'amount',
                    'note',
                    'discount_amount',
                    'paid_at',
                    'payment_month',
                    'receipt_number',
                    'status',
                ])
                ->where('student_id', $studentId)
                ->where('student_class_enrollment_id', $enrolledId)
                ->where('status', 'completed')
                ->orderByDesc('paid_at')
                ->get();

            $monthWiseSummary = $payments
                ->groupBy(function ($payment) {
                    return $payment->payment_month
                        ? Carbon::parse($payment->payment_month)->format('Y-m')
                        : 'unknown';
                })
                ->map(function ($items, $monthKey) {
                    return [
                        'month' => $monthKey,
                        'month_name' => $monthKey !== 'unknown'
                            ? Carbon::createFromFormat('Y-m', $monthKey)->format('F Y')
                            : 'Unknown',
                        'count' => $items->count(),
                        'total_amount' => (float) $items->sum('amount'),
                        'total_discount_amount' => (float) $items->sum('discount_amount'),
                        'payments' => $items->map(function ($payment) {
                            return [
                                'id' => $payment->id,
                                'mark_method' => $payment->mark_method,
                                'amount' => (float) $payment->amount,
                                'discount_amount' => (float) $payment->discount_amount,
                                'note' => $payment->note,
                                'paid_at' => optional($payment->paid_at)->toDateTimeString(),
                                'payment_month' => optional($payment->payment_month)->toDateString(),
                                'receipt_number' => $payment->receipt_number,
                                'status' => $payment->status,
                            ];
                        })->values(),
                    ];
                })
                ->sortByDesc('month')
                ->values();

            return response()->json([
                'success' => true,
                'count' => $payments->count(),
                'data' => $monthWiseSummary,
            ]);
        } catch (Throwable $e) {
            Log::error('Fetch Student All Payment Error', [
                'student_id' => $studentId,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching student payments.',
            ], 500);
        }
    }
}
