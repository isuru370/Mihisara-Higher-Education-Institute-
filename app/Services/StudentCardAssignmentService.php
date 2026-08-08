<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentCard;
use Illuminate\Support\Facades\Log;

class StudentCardAssignmentService
{
    public function __construct(
        protected StudentCardService $studentCardService
    ) {}

    /**
     * Search student by Temporary QR or Current QR.
     */
    public function searchStudent(string $code): array
    {
        try {
            $student = Student::query()
                ->with([
                    'grade:id,grade_name',
                    'currentCard:id,student_id,card_number,qr_code,status,is_current,issued_at',
                ])
                ->where(function ($query) use ($code) {
                    $query->where('temporary_qr_code', trim($code))
                        ->orWhere('custom_id', trim($code));
                })
                ->where('is_active', true)
                ->where('student_disable', false)
                ->first();

            if (!$student) {
                throw new \Exception('Student not found.');
            }

            return [
                'student' => [
                    'id' => $student->id,
                    'custom_id' => $student->custom_id,
                    'initial_name' => $student->initial_name,
                    'img_url' => $student->img_url,
                    'grade' => [
                        'id' => optional($student->grade)->id,
                        'grade_name' => optional($student->grade)->grade_name,
                    ],
                ],
                'current_card' => $student->currentCard
                    ? [
                        'id' => $student->currentCard->id,
                        'card_number' => $student->currentCard->card_number,
                        'qr_code' => $student->currentCard->qr_code,
                        'status' => $student->currentCard->status,
                    ]
                    : null,

                'has_card' => $student->currentCard !== null,

                'message' => $student->currentCard
                    ? 'Student already has an active card.'
                    : 'Student is ready for card assignment.',
            ];
        } catch (\Throwable $e) {
            Log::error('Student search for card assignment failed.', [
                'search_code' => $code,
                'message'     => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
                'trace'       => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Search available student card.
     */
    public function searchAvailableCard(string $qrCode): StudentCard
    {
        try {
            $card = StudentCard::query()
                ->where('qr_code', strtoupper(trim($qrCode)))
                ->first();

            if (!$card) {
                throw new \Exception('Student card not found.');
            }

            if ($card->status !== 'available') {
                throw new \Exception('This student card is not available.');
            }

            if ($card->student_id !== null) {
                throw new \Exception('This student card is already assigned.');
            }

            return $card;
        } catch (\Throwable $e) {
            Log::error('Available student card search failed.', [
                'qr_code' => $qrCode,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Assign available card to student.
     */
    public function assignCard(
        int $studentId,
        string $cardQrCode
    ): StudentCard {
        try {

            $card = StudentCard::query()
                ->where('qr_code', strtoupper(trim($cardQrCode)))
                ->first();

            if (!$card) {
                throw new \Exception('Student card not found.');
            }

            return $this->studentCardService->assignCard(
                $studentId,
                $card->id,
            );
        } catch (\Throwable $e) {

            Log::error('Student card assignment failed.', [
                'student_id' => $studentId,
                'card_qr_code' => $cardQrCode,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
