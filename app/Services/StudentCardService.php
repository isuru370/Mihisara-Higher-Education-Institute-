<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentCard;
use App\Models\StudentCardHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentCardService
{
    /**
     * Generate new student cards.
     */
    public function generateCards(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        try {

            DB::transaction(function () use ($quantity) {

                $lastSequence = StudentCard::max('card_sequence') ?? 0;

                $cards = [];

                for ($i = 1; $i <= $quantity; $i++) {

                    $sequence = $lastSequence + $i;

                    $cards[] = [
                        'student_id'    => null,
                        'card_sequence' => $sequence,
                        'card_number'   => 'MIHISARA' . str_pad($sequence, 6, '0', STR_PAD_LEFT),
                        'qr_code'       => 'ST' . str_pad($sequence, 3, '0', STR_PAD_LEFT),
                        'status'        => 'available',
                        'is_current'    => false,
                        'issued_at'     => null,
                        'deactivated_at' => null,
                        'remarks'       => null,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }

                StudentCard::insert($cards);
            });

            Log::info('Student cards generated successfully.', [
                'quantity' => $quantity,
            ]);
        } catch (\Throwable $e) {

            Log::error('Student card generation failed.', [
                'quantity' => $quantity,
                'error'    => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Get card inventory.
     */
    public function getCards(array $filters = [])
    {
        try {

            $query = StudentCard::query()->with('student');

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['search'])) {

                $search = trim($filters['search']);

                $query->where(function ($q) use ($search) {
                    $q->where('card_number', 'like', "%{$search}%")
                        ->orWhere('qr_code', 'like', "%{$search}%");
                });
            }

            if (isset($filters['assigned'])) {

                if ($filters['assigned']) {
                    $query->whereNotNull('student_id');
                } else {
                    $query->whereNull('student_id');
                }
            }

            $perPage = $filters['per_page'] ?? 20;

            return $query
                ->orderBy('card_sequence')
                ->paginate($perPage);
        } catch (\Throwable $e) {

            Log::error('Failed to retrieve student cards.', [
                'filters' => $filters,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get available cards.
     */
    public function getAvailableCards($keyword = null)
    {
        return StudentCard::where('status', 'available')
            ->whereNull('student_id')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('card_number', 'like', "%{$keyword}%")
                        ->orWhere('qr_code', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('card_sequence')
            ->paginate(20);
    }

    /**
     * Find card by ID.
     */
    public function findCard(int $id): ?StudentCard
    {
        try {

            return StudentCard::with('student')->find($id);
        } catch (\Throwable $e) {

            Log::error('Failed to retrieve student card.', [
                'card_id' => $id,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Find card by card number.
     */
    public function findByCardNumber(string $cardNumber): ?StudentCard
    {
        try {

            return StudentCard::query()
                ->with('student')
                ->where('card_number', trim($cardNumber))
                ->first();
        } catch (\Throwable $e) {

            Log::error('Failed to retrieve student card by card number.', [
                'card_number' => $cardNumber,
                'error'       => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Find card by QR code.
     */
    public function findByQrCode(string $qrCode): ?StudentCard
    {
        try {

            return StudentCard::query()
                ->with('student')
                ->where('qr_code', trim($qrCode))
                ->first();
        } catch (\Throwable $e) {

            Log::error('Failed to retrieve student card by QR code.', [
                'qr_code' => $qrCode,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Assign a card to a student.
     */
    public function assignCard(int $studentId, int $cardId): StudentCard
    {
        return DB::transaction(function () use ($studentId, $cardId) {

            // Find card with lock
            $card = StudentCard::lockForUpdate()->findOrFail($cardId);

            // Check card availability
            if ($card->status !== 'available') {
                throw new \Exception('This card is not available for assignment.');
            }

            if ($card->student_id !== null) {
                throw new \Exception('This card is already assigned to a student.');
            }

            // Check student
            $student = Student::lockForUpdate()->findOrFail($studentId);

            // Check current active card
            $currentCard = StudentCard::where('student_id', $studentId)
                ->where('is_current', true)
                ->first();

            if ($currentCard) {
                throw new \Exception(
                    'This student already has an active student card.'
                );
            }

            // Assign card
            $card->update([
                'student_id'      => $studentId,
                'status'          => 'assigned',
                'is_current'      => true,
                'issued_at'       => Carbon::now(),
                'deactivated_at'  => null,
            ]);

            // UPDATE STUDENT'S CUSTOM_ID WITH THE NEW CARD NUMBER
            $student->update([
                'custom_id'           => $card->qr_code,
                'permanent_qr_active' => true,
                'student_disable'     => false,
            ]);

            // Log history
            $this->logHistory(
                $studentId,
                $card->id,
                'assigned',
                null,
                null,
                null,
                'Card assigned to student'
            );

            Log::info('Student card assigned successfully.', [
                'student_id' => $studentId,
                'card_id'    => $card->id,
                'card_number' => $card->card_number,
                'custom_id_updated' => $card->qr_code,
                'performed_by' => auth()->id(),
            ]);

            return $card->fresh();
        });
    }

    /**
     * Replace student's current card.
     */
    /**
     * Replace student's current card.
     */
    public function replaceCard(
        string $oldQrCode,
        string $newQrCode,
        ?string $reason = null,
        ?string $remarks = null
    ): StudentCard {

        return DB::transaction(function () use (
            $oldQrCode,
            $newQrCode,
            $reason,
            $remarks
        ) {

            $oldCard = StudentCard::lockForUpdate()
                ->where('qr_code', strtoupper(trim($oldQrCode)))
                ->firstOrFail();

            $newCard = StudentCard::lockForUpdate()
                ->where('qr_code', strtoupper(trim($newQrCode)))
                ->firstOrFail();

            // Old card must be Assigned or Lost
            if (!in_array($oldCard->status, ['assigned', 'lost'])) {
                throw new \Exception('Only assigned or lost cards can be replaced.');
            }

            if ($oldCard->student_id === null) {
                throw new \Exception('Old card is not assigned to any student.');
            }

            // Validate new card
            if ($newCard->status !== 'available') {
                throw new \Exception('Selected card is not available.');
            }

            if ($newCard->student_id !== null) {
                throw new \Exception('Selected card is already assigned.');
            }

            // Lock student
            $student = Student::lockForUpdate()->findOrFail($oldCard->student_id);

            // Deactivate old card
            if ($oldCard->status === 'assigned') {
                $oldCard->update([
                    'status' => 'inactive',
                    'is_current' => false,
                    'deactivated_at' => now(),
                ]);
            } else {
                $oldCard->update([
                    'is_current' => false,
                    'deactivated_at' => now(),
                ]);
            }

            // Assign new card
            $newCard->update([
                'student_id' => $student->id,
                'status' => 'assigned',
                'is_current' => true,
                'issued_at' => now(),
                'deactivated_at' => null,
            ]);

            // Update student
            $student->update([
                'custom_id' => $newCard->qr_code,
                'permanent_qr_active' => true,
                'student_disable' => false,
            ]);

            // History
            $this->logHistory(
                $student->id,
                $newCard->id,
                'replaced',
                $oldCard->id,
                $newCard->id,
                $reason,
                $remarks ?? 'Card replaced'
            );

            Log::info('Student card replaced successfully.', [
                'student_id' => $student->id,
                'old_qr_code' => $oldCard->qr_code,
                'old_card_no' => $oldCard->card_number,
                'new_qr_code' => $newCard->qr_code,
                'new_card_no' => $newCard->card_number,
                'custom_id_updated' => $newCard->qr_code,
                'reason' => $reason,
                'performed_by' => auth()->id(),
            ]);

            return $newCard->fresh();
        });
    }

    /**
     * Mark a card as lost.
     */
    public function markLost(StudentCard $card): StudentCard
    {
        try {

            if ($card->student_id === null) {
                throw new \Exception('Cannot mark an unassigned card as lost.');
            }

            DB::transaction(function () use (&$card) {

                // Lock card
                $card = StudentCard::lockForUpdate()->findOrFail($card->id);

                // Lock student
                $student = Student::lockForUpdate()->findOrFail($card->student_id);

                // Update card
                $card->update([
                    'status'         => 'lost',
                    'is_current'     => false,
                    'deactivated_at' => now(),
                ]);

                // Disable student
                $student->update([
                    'student_disable'     => true,
                    'permanent_qr_active' => false,
                    'custom_id'           => null,
                ]);

                // Save history
                $this->logHistory(
                    $student->id,
                    $card->id,
                    'lost',
                    null,
                    null,
                    'lost',
                    'Card marked as lost'
                );

                Log::info('Student card marked as lost.', [
                    'student_id'   => $student->id,
                    'card_id'      => $card->id,
                    'card_number'  => $card->card_number,
                    'performed_by' => auth()->id(),
                ]);
            });

            return $card->fresh();
        } catch (\Throwable $e) {

            Log::error('Failed to mark student card as lost.', [
                'card_id' => $card->id,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            throw $e;
        }
    }
    /**
     * Mark a card as damaged.
     */
    public function markDamaged(StudentCard $card): StudentCard
    {
        try {

            if ($card->student_id === null) {
                throw new \Exception('Cannot mark an unassigned card as damaged.');
            }

            $card->update([
                'status' => 'damaged',
                'is_current' => false,
                'deactivated_at' => now(),
            ]);

            $this->logHistory(
                $card->student_id,
                $card->id,
                'damaged',
                null,
                null,
                'damaged',
                'Card marked as damaged'
            );

            Log::info('Student card marked as damaged.', [
                'card_id' => $card->id,
                'card_number' => $card->card_number,
                'student_id' => $card->student_id,
                'performed_by' => auth()->id(),
            ]);

            return $card->fresh();
        } catch (\Throwable $e) {

            Log::error('Failed to mark student card as damaged.', [
                'card_id' => $card->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Deactivate a card.
     */
    public function deactivateCard(StudentCard $card): StudentCard
    {
        try {

            if ($card->student_id === null) {
                throw new \Exception('Cannot deactivate an unassigned card.');
            }

            $card->update([
                'status' => 'inactive',
                'is_current' => false,
                'deactivated_at' => now(),
            ]);

            $this->logHistory(
                $card->student_id,
                $card->id,
                'deactivated',
                null,
                null,
                null,
                'Card deactivated'
            );

            Log::info('Student card deactivated.', [
                'card_id' => $card->id,
                'card_number' => $card->card_number,
                'student_id' => $card->student_id,
                'performed_by' => auth()->id(),
            ]);

            return $card->fresh();
        } catch (\Throwable $e) {

            Log::error('Failed to deactivate student card.', [
                'card_id' => $card->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Get student's current card.
     */
    public function getCurrentCard(Student $student): ?StudentCard
    {
        try {

            return $student->currentCard()->with('student')->first();
        } catch (\Throwable $e) {

            Log::error('Failed to retrieve current student card.', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Get student's card history with pagination.
     */
    public function getCardHistory(Student $student, int $perPage = 20)
    {
        try {

            return $student->cardHistories()
                ->with([
                    'card:id,card_number,qr_code,status',
                    'oldCard:id,card_number,qr_code,status',
                    'newCard:id,card_number,qr_code,status',
                    'performedBy:id,name',
                ])
                ->orderByDesc('performed_at')
                ->paginate($perPage);
        } catch (\Throwable $e) {

            Log::error('Failed to retrieve student card history.', [
                'student_id' => $student->id,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Check whether card is available.
     */
    public function isCardAvailable(StudentCard $card): bool
    {
        return $card->status === 'available'
            && $card->student_id === null;
    }

    /**
     * Get next card sequence.
     */
    public function getNextSequence(): int
    {
        try {

            return (int) StudentCard::max('card_sequence') + 1;
        } catch (\Throwable $e) {

            Log::error('Failed to retrieve next student card sequence.', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Log card history.
     */
    private function logHistory(
        int $studentId,
        int $cardId,
        string $action,
        ?int $oldCardId = null,
        ?int $newCardId = null,
        ?string $reason = null,
        ?string $remarks = null
    ): void {

        StudentCardHistory::create([
            'student_id'   => $studentId,
            'card_id'      => $cardId,
            'old_card_id'  => $oldCardId,
            'new_card_id'  => $newCardId,
            'action'       => $action,
            'reason'       => $reason,
            'remarks'      => $remarks,
            'performed_by' => auth()->id(),
            'performed_at' => now(),
        ]);
    }

    /**
     * Find an available card by QR Code.
     */
    public function findAvailableCardByQr(string $qrCode): StudentCard
    {
        $card = StudentCard::query()
            ->where('qr_code', trim($qrCode))
            ->lockForUpdate()
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
    }

    public function searchCard(string $qrCode): array
    {
        $card = StudentCard::with([
            'student.grade:id,grade_name',
        ])->where('qr_code', strtoupper(trim($qrCode)))
            ->first();

        if (!$card) {
            return [
                'status' => false,
                'message' => 'Card not found',
            ];
        }

        if ($card->status === 'available') {
            return [
                'status' => true,
                'message' => 'Card is available',
            ];
        }

        $student = Student::with([
            'grade:id,grade_name',
            'studentCards' => function ($q) {
                $q->orderBy('issued_at')
                    ->orderBy('id');
            }
        ])->find($card->student_id);

        if (!$student) {
            return [
                'status' => false,
                'message' => 'Student not found',
            ];
        }

        $currentCard = $student->studentCards
            ->firstWhere('is_current', true);

        return [

            'status' => false,

            'message' => $card->is_current
                ? 'Card already assigned'
                : 'This card is no longer active.',

            'student' => [

                'id' => $student->id,

                'initial_name' => $student->initial_name,

                'img_url' => $student->img_url,

                'grade' => $student->grade?->grade_name,

                'guardian_mobile' => $student->guardian_mobile,
            ],

            'current_card' => $currentCard ? [

                'id' => $currentCard->id,

                'card_number' => $currentCard->card_number,

                'qr_code' => $currentCard->qr_code,

                'status' => $currentCard->status,

                'is_current' => $currentCard->is_current,

                'issued_at' => optional($currentCard->issued_at)
                    ->format('Y-m-d H:i:s'),

            ] : null,

            'cards' => $student->studentCards
                ->map(function ($c) {

                    return [

                        'id' => $c->id,

                        'card_number' => $c->card_number,

                        'qr_code' => $c->qr_code,

                        'status' => $c->status,

                        'is_current' => $c->is_current,

                        'issued_at' => optional($c->issued_at)
                            ->format('Y-m-d H:i:s'),

                        'deactivated_at' => optional($c->deactivated_at)
                            ->format('Y-m-d H:i:s'),
                    ];
                })
                ->values(),
        ];
    }

    public function idCardDetails($perPage = 12)
    {
        return StudentCard::select([
            'id',
            'card_number',
            'qr_code',
            'status'
        ])
            ->orderBy('id', 'asc')
            ->paginate($perPage);
    }
}
