<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentCard;
use App\Services\StudentCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentCardController extends Controller
{
    protected StudentCardService $studentCardService;

    public function __construct(StudentCardService $studentCardService)
    {
        $this->studentCardService = $studentCardService;
    }

    /**
     * Card inventory.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['status', 'search', 'assigned', 'per_page']);
            $cards = $this->studentCardService->getCards($filters);

            return view('admin.student-cards.index', compact('cards'));
        } catch (\Throwable $e) {
            Log::error('Failed to load student card inventory.', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return redirect()->back()->with(
                'error',
                'Unable to load student card inventory.'
            );
        }
    }

    /**
     * Show generate card form.
     */
    public function generate()
    {
        return view('admin.student-cards.generate');
    }

    /**
     * Generate new student cards.
     */
    public function generateCards(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        try {
            $this->studentCardService->generateCards($request->quantity);

            return redirect()
                ->route('admin.student-cards.index')
                ->with('success', $request->quantity . ' cards generated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display card details.
     */
    public function show(StudentCard $card)
    {
        $card->load([
            'student.grade',
            'student.enrollments' => function ($query) {
                $query->where('is_active', true);
            },
            'student.enrollments.studentClass',
            'student.enrollments.category',
        ]);

        return view('admin.student-cards.show', compact('card'));
    }

    /**
     * Show assign card form.
     */
    public function assignForm(Request $request)
    {
        $cardId = $request->query('card_id');
        $studentId = $request->query('student_id');

        $card = null;
        $student = null;

        if ($cardId) {
            $card = StudentCard::with('student')->find($cardId);
        }

        if ($studentId) {
            $student = Student::with('currentCard')->find($studentId);
        }

        $availableCards = StudentCard::where('status', 'available')
            ->whereNull('student_id')
            ->orderBy('card_sequence')
            ->get();

        $students = Student::where('student_disable', false)
            ->whereDoesntHave('currentCard')
            ->orderBy('full_name')
            ->get();

        return view('admin.student-cards.assign', compact(
            'card',
            'student',
            'availableCards',
            'students'
        ));
    }

    /**
     * Assign card to student.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'card_id'    => 'required|exists:student_cards,id',
        ]);

        try {
            $this->studentCardService->assignCard(
                $request->student_id,
                $request->card_id
            );

            return redirect()
                ->route('admin.student-cards.available')
                ->with('success', 'Student card assigned successfully. Student account has been activated.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show replace card form.
     */
    public function replaceForm(StudentCard $card)
    {
        if ($card->status !== 'assigned' || $card->student_id === null) {
            return redirect()
                ->route('admin.student-cards.show', $card->id)
                ->with('error', 'This card is not assigned to any student.');
        }

        $availableCards = StudentCard::where('status', 'available')
            ->whereNull('student_id')
            ->orderBy('card_sequence')
            ->get();

        if ($availableCards->isEmpty()) {
            return redirect()
                ->route('admin.student-cards.show', $card->id)
                ->with('warning', 'No available cards to replace with.');
        }

        return view('admin.student-cards.replace', compact('card', 'availableCards'));
    }

    /**
     * Replace student's current card.
     */
    public function replace(Request $request, StudentCard $card)
    {
        $request->validate([
            'new_qr_code' => 'required|string|exists:student_cards,qr_code',
            'reason' => 'required|in:lost,damaged,worn_out,other',
            'remarks' => 'nullable|string|max:255',
        ]);

        try {
            $newCard = $this->studentCardService->replaceCard(
                $card->qr_code,
                $request->new_qr_code,
                $request->reason,
                $request->remarks
            );

            return redirect()
                ->route('admin.student-cards.show', $newCard)
                ->with('success', 'Student card replaced successfully.');
        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mark card as lost.
     */
    public function markLost(StudentCard $card)
    {
        try {
            $this->studentCardService->markLost($card);

            return redirect()
                ->route('admin.student-cards.show', $card->id)
                ->with('success', 'Student card marked as lost successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to mark student card as lost.', [
                'card_id' => $card->id,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('admin.student-cards.show', $card->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mark card as damaged.
     */
    public function markDamaged(StudentCard $card)
    {
        try {
            $this->studentCardService->markDamaged($card);

            return redirect()
                ->route('admin.student-cards.show', $card->id)
                ->with('success', 'Student card marked as damaged successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to mark student card as damaged.', [
                'card_id' => $card->id,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('admin.student-cards.show', $card->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Deactivate card.
     */
    public function deactivate(StudentCard $card)
    {
        try {
            $this->studentCardService->deactivateCard($card);

            return redirect()
                ->route('admin.student-cards.show', $card->id)
                ->with('success', 'Student card deactivated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to deactivate student card.', [
                'card_id' => $card->id,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('admin.student-cards.show', $card->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Available cards.
     */
    public function available(Request $request)
    {
        $cards = $this->studentCardService->getAvailableCards($request->keyword);

        $students = Student::with('currentCard')
            ->where('student_disable', false)
            ->orderBy('full_name')
            ->get();

        return view('admin.student-cards.available', compact('cards', 'students'));
    }
    /**
     * Student card history.
     */
    public function history(Student $student)
    {
        try {
            $cards = $this->studentCardService->getCardHistory($student);

            if ($cards->isEmpty()) {
                return redirect()
                    ->route('admin.students.show', $student->id)
                    ->with('info', 'This student has no card history.');
            }

            return view('admin.student-cards.history', compact('student', 'cards'));
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve student card history.', [
                'student_id' => $student->id,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            return redirect()
                ->route('admin.students.show', $student->id)
                ->with('error', 'Unable to retrieve student card history.');
        }
    }

    /**
     * Search card by number or QR.
     */
    public function search(Request $request)
    {
        $request->validate([
            'keyword' => ['required', 'string', 'min:1'],
        ]);

        try {
            $keyword = trim($request->keyword);

            $card = $this->studentCardService->findByCardNumber($keyword);

            if (!$card) {
                $card = $this->studentCardService->findByQrCode($keyword);
            }

            if (!$card) {
                return redirect()
                    ->route('admin.student-cards.index')
                    ->with('warning', 'Student card not found.');
            }

            return redirect()->route('admin.student-cards.show', $card->id);
        } catch (\Throwable $e) {
            Log::error('Failed to search student card.', [
                'keyword' => $request->keyword,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('admin.student-cards.index')
                ->withInput()
                ->with('error', 'Unable to search student card.');
        }
    }

    public function checkCurrentCard(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        $student = Student::with('currentCard')->find($request->student_id);

        if (!$student) {
            return response()->json([
                'has_current_card' => false
            ]);
        }

        $currentCard = $student->currentCard;

        return response()->json([
            'has_current_card' => $currentCard ? true : false,
            'student_disabled' => $student->student_disable,
            'card_number' => $currentCard?->card_number,
            'card_id' => $currentCard?->id,
        ]);
    }

    public function preview(Request $request)
    {
        try {

            $cards = $this->studentCardService->idCardDetails(
                $request->get('per_page', 12)
            );

            return view('admin.student-cards.preview', compact('cards'));
        } catch (\Throwable $e) {

            Log::error('Failed to load ID Card Preview.', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('admin.student-cards.index')
                ->with('error', 'Unable to load ID Card Preview.');
        }
    }
}
