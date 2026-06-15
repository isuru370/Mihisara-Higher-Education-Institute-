<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionPayment;
use App\Models\Grade;
use App\Models\QuickPhoto;
use App\Models\Student;
use App\Models\StudentIdCard;
use App\Models\TemporaryIdCard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StudentRegisterController extends Controller
{
    public function QuickStudentStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'temporary_qr_code' => [
                'required',
                'string',
                'regex:/^TMP\d{3,}$/',
                'unique:students,temporary_qr_code',
                'exists:temporary_id_cards,temporary_id_number',
                function ($attribute, $value, $fail) {
                    if ((int) substr($value, 3) < 1) {
                        $fail('The temporary QR code must start from TMP001.');
                    }

                    $card = TemporaryIdCard::where('temporary_id_number', $value)->first();

                    if (!$card) {
                        $fail('Temporary QR card not found.');
                        return;
                    }

                    if ($card->status === 'active') {
                        $fail('This temporary QR code is already active.');
                    }

                    if ($card->status === 'expired') {
                        $fail('This temporary QR code is expired.');
                    }

                    if ($card->status !== 'issued') {
                        $fail('This temporary QR code must be in ISSUED status before assigning to a student.');
                    }
                },
            ],
            'quick_image_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {

                    $quickPhoto = QuickPhoto::where('custom_id', $value)
                        ->where('is_active', true)
                        ->first();

                    if (!$quickPhoto) {
                        $fail('Quick image is invalid or already used.');
                    }
                }
            ],
            'initial_name'    => 'required|string|max:100',
            'guardian_mobile' => 'required|string|max:20',
            'grade_id'        => 'required|exists:grades,id',
            'gender'          => 'required|in:male,female,other',
            'admission_id' => 'nullable|exists:admissions,id',
        ]);

        $studentData = DB::transaction(function () use ($validated) {

            $customId = $this->generateCustomId((int) $validated['grade_id']);

            $imgUrl = match ($validated['gender']) {
                'female' => 'uploads/female.png',
                default  => 'uploads/male.png',
            };

            $quickPhoto = null;

            if (!empty($validated['quick_image_id'])) {

                $quickPhoto = QuickPhoto::where(
                    'custom_id',
                    $validated['quick_image_id']
                )
                    ->lockForUpdate()
                    ->first();

                if ($quickPhoto && !empty($quickPhoto->image_path)) {
                    $imgUrl = $quickPhoto->image_path;
                }
            }

            $student = Student::create([
                'custom_id'                     => $customId,
                'temporary_qr_code'             => $validated['temporary_qr_code'],
                'temporary_qr_code_expire_date' => Carbon::now('Asia/Colombo')->addMonths(2),
                'initial_name'                  => $validated['initial_name'],
                'guardian_mobile'               => $validated['guardian_mobile'],
                'grade_id'                      => $validated['grade_id'],
                'gender'                        => $validated['gender'],
                'img_url'                       => $imgUrl,
            ]);

            $temporaryCard = TemporaryIdCard::where(
                'temporary_id_number',
                $validated['temporary_qr_code']
            )
                ->lockForUpdate()
                ->first();

            if ($temporaryCard) {

                $temporaryCard->update([
                    'student_id'   => $student->id,
                    'status'       => 'active',
                    'activated_at' => now(),
                ]);
            }

            if ($quickPhoto) {

                $quickPhoto->update([
                    'is_active' => false,
                ]);
            }

            StudentIdCard::create([
                'student_id'          => $student->id,
                'status'              => 'pending',
                'registration_status' => 'incomplete',
                'student_fee'         => 350,
                'print_cost'          => 90,
                'profit'              => 260,
                'is_reissue'          => false,
            ]);

            $admissionPayment = null;

            if (!empty($validated['admission_id'])) {

                $admission = Admission::findOrFail(
                    $validated['admission_id']
                );

                $admissionPayment = AdmissionPayment::create([
                    'student_id'   => $student->id,
                    'admission_id' => $admission->id,
                    'amount'       => $admission->amount,
                ]);

                $admissionPayment->load('admission');

                $student->update([
                    'admission' => true,
                ]);
            } else {

                $student->update([
                    'admission' => false,
                ]);
            }

            $student->load('grade');

            return [
                'student'           => $student,
                'admissionPayment'  => $admissionPayment,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Student registered successfully',

            'student' => $studentData['student'],

            'admission_payment' => $studentData['admissionPayment']
                ? [
                    'id' => $studentData['admissionPayment']->id,
                    'receipt_number' => $studentData['admissionPayment']->receipt_number,
                    'amount' => $studentData['admissionPayment']->amount,
                    'status' => $studentData['admissionPayment']->status,
                    'paid_at' => $studentData['admissionPayment']->paid_at,
                    'payment_method' => $studentData['admissionPayment']->payment_method,

                    'admission_name' =>
                    $studentData['admissionPayment']
                        ->admission?->name,
                ]
                : null,

        ], 201);
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

        if ($lastStudent && preg_match('/^SA' . preg_quote($gradeCode, '/') . '(\d+)$/', $lastStudent->custom_id, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        do {
            $lastNumber++;
            $customId = 'SA' . $gradeCode . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
        } while (Student::where('custom_id', $customId)->exists());

        return $customId;
    }
}
