<?php

namespace App\Http\Controllers\Admin;

use App\Models\TemporaryIdCard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;

class TemporaryIDCardController extends Controller
{

    /**
     * Show form
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));
        $status = trim($request->get('status'));

        $baseQuery = TemporaryIdCard::query();

        $temporaryIdCards = TemporaryIdCard::with(['student.grade'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('temporary_id_number', 'like', '%' . $search . '%')
                        ->orWhere('card_number', 'like', '%' . $search . '%');
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('temporary_id_number', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $counts = TemporaryIdCard::selectRaw("
            COUNT(*) as total_count,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'downloaded' THEN 1 ELSE 0 END) as downloaded_count,
            SUM(CASE WHEN status = 'issued' THEN 1 ELSE 0 END) as issued_count,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_count
        ")
            ->first();

        return view('admin.temporary_id_cards.index', compact(
            'temporaryIdCards',
            'search',
            'status',
            'counts'
        ));
    }
    public function create()
    {
        return view('admin.temporary_id_cards.create');
    }
    /**
     * Save cards to DB and show preview
     */
    public function store(Request $request)
    {
        $request->validate([
            'start' => 'required|string',
            'end'   => 'required|string',
        ]);

        $startInput = strtoupper(trim($request->start));
        $endInput   = strtoupper(trim($request->end));

        $startNum = intval(preg_replace('/^TMP/i', '', $startInput));
        $endNum   = intval(preg_replace('/^TMP/i', '', $endInput));

        if ($startNum <= 0 || $endNum <= 0) {
            return back()
                ->with('error', 'Please enter valid numbers (e.g., 001 or TMP001)')
                ->withInput();
        }

        if ($startNum > $endNum) {
            return back()
                ->with('error', 'Start code must be smaller than End code')
                ->withInput();
        }

        /*
    |--------------------------------------------------------------------------
    | CHECK EXISTING DATA HAS NO GAPS
    |--------------------------------------------------------------------------
    */

        $existingNumbers = TemporaryIdCard::orderBy('temporary_id_number')
            ->pluck('temporary_id_number')
            ->map(function ($value) {
                return intval(preg_replace('/^TMP/i', '', $value));
            })
            ->sort()
            ->values();

        if ($existingNumbers->count() > 0) {
            $expected = 1;

            foreach ($existingNumbers as $num) {
                if ($num !== $expected) {
                    return back()
                        ->with('error', 'Existing temporary ID numbers have a gap at TMP' . str_pad($expected, 3, '0', STR_PAD_LEFT))
                        ->withInput();
                }
                $expected++;
            }
        }

        /*
    |--------------------------------------------------------------------------
    | CHECK NEW RANGE MUST CONTINUE SEQUENTIALLY
    |--------------------------------------------------------------------------
    */

        $nextExpected = $existingNumbers->count() > 0
            ? ($existingNumbers->last() + 1)
            : 1;

        if ($startNum !== $nextExpected) {
            return back()
                ->with(
                    'error',
                    'Next available number is TMP' . str_pad($nextExpected, 3, '0', STR_PAD_LEFT) . '. You cannot skip numbers.'
                )
                ->withInput();
        }

        /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATES INSIDE REQUESTED RANGE
    |--------------------------------------------------------------------------
    */

        for ($i = $startNum; $i <= $endNum; $i++) {
            $temporaryIdNumber = 'TMP' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $cardNumber        = 'CARD' . str_pad($i, 3, '0', STR_PAD_LEFT);

            $exists = TemporaryIdCard::where('temporary_id_number', $temporaryIdNumber)
                ->orWhere('card_number', $cardNumber)
                ->exists();

            if ($exists) {
                return back()
                    ->with('error', "Duplicate found: {$temporaryIdNumber} or {$cardNumber} already exists.")
                    ->withInput();
            }
        }

        /*
    |--------------------------------------------------------------------------
    | SAVE DATA
    |--------------------------------------------------------------------------
    */

        $savedIds = [];

        DB::transaction(function () use ($startNum, $endNum, &$savedIds) {
            for ($i = $startNum; $i <= $endNum; $i++) {
                $temporaryIdNumber = 'TMP' . str_pad($i, 3, '0', STR_PAD_LEFT);
                $cardNumber        = 'CARD' . str_pad($i, 3, '0', STR_PAD_LEFT);

                $card = TemporaryIdCard::create([
                    'temporary_id_number' => $temporaryIdNumber,
                    'card_number'         => $cardNumber,
                    'status'              => 'pending',
                ]);

                $savedIds[] = $card->id;
            }
        });

        return redirect()
            ->route('admin.temporary-id-cards.index')
            ->with('success', 'Temporary ID cards saved successfully.');
    }

    /**
     * Preview saved cards from DB
     */
    public function preview()
    {
        return view('admin.temporary_id_cards.preview', [
            'codes' => collect(),
            'start' => '',
            'end'   => '',
        ]);
    }

    public function generatePreview(Request $request)
    {
        $validated = $request->validate([
            'start' => ['required', 'string', 'regex:/^TMP\d+$/i'],
            'end'   => ['required', 'string', 'regex:/^TMP\d+$/i'],
        ]);

        $startInput = strtoupper(trim($validated['start']));
        $endInput   = strtoupper(trim($validated['end']));

        $startNum = (int) preg_replace('/^TMP/i', '', $startInput);
        $endNum   = (int) preg_replace('/^TMP/i', '', $endInput);

        if ($startNum < 1 || $endNum < 1) {
            return back()->with('error', 'Please enter valid TMP numbers like TMP001 and TMP120.');
        }

        if ($startNum > $endNum) {
            return back()->with('error', 'Start TMP number must be smaller than End TMP number.');
        }

        $totalCards = ($endNum - $startNum) + 1;

        if ($totalCards > 120) {
            return back()->with('error', 'You can preview maximum 120 cards at once.');
        }

        $tmpNumbers = collect(range($startNum, $endNum))->map(function ($num) {
            return 'TMP' . str_pad($num, 3, '0', STR_PAD_LEFT);
        });

        $cards = TemporaryIdCard::with(['student.grade'])
            ->whereIn('temporary_id_number', $tmpNumbers)
            ->where('status', 'pending')
            ->orderByRaw("CAST(SUBSTRING(temporary_id_number, 4) AS UNSIGNED)")
            ->get();

        if ($cards->count() !== $tmpNumbers->count()) {
            $existingCount = TemporaryIdCard::whereIn('temporary_id_number', $tmpNumbers)->count();

            if ($existingCount !== $tmpNumbers->count()) {
                return back()->with('error', 'Some TMP numbers are missing in the database.');
            }

            return back()->with('error', 'Some TMP numbers are not pending, so they cannot be previewed.');
        }

        $codes = $cards->map(function ($card) {
            return [
                'id'          => $card->id,
                'code'        => $card->temporary_id_number,
                'card_number' => $card->card_number,
                'status'      => $card->status,
                'student'     => $card->student,
                'qr_base64'   => $card->qr_base64,
            ];
        });

        return view('admin.temporary_id_cards.preview', [
            'codes' => $codes,
            'start' => $startInput,
            'end'   => $endInput,
        ]);
    }

    public function updateStatusPage()
    {
        return view('admin.temporary_id_cards.update-status', [
            'updatedCards'   => collect(),
            'start'          => '',
            'end'            => '',
            'selectedStatus' => 'downloaded',
        ]);
    }

    public function changeStatus(Request $request)
    {
        $validated = $request->validate([
            'start'  => ['required', 'string', 'regex:/^TMP\d+$/i'],
            'end'    => ['nullable', 'string', 'regex:/^TMP\d+$/i'],
            'status' => ['required', 'in:pending,downloaded,issued,active,expired'],
        ]);

        $startInput = strtoupper(trim($validated['start']));
        $endInput   = $validated['end'] ? strtoupper(trim($validated['end'])) : $startInput;

        $startNum = (int) preg_replace('/^TMP/i', '', $startInput);
        $endNum   = (int) preg_replace('/^TMP/i', '', $endInput);

        if ($startNum < 1 || $endNum < 1) {
            return back()->with('error', 'Please enter valid TMP numbers like TMP001.');
        }

        if ($startNum > $endNum) {
            return back()->with('error', 'Start TMP number must be smaller than End TMP number.');
        }

        $tmpNumbers = collect(range($startNum, $endNum))->map(function ($num) {
            return 'TMP' . str_pad($num, 3, '0', STR_PAD_LEFT);
        });

        $cards = TemporaryIdCard::whereIn('temporary_id_number', $tmpNumbers)
            ->get()
            ->keyBy('temporary_id_number');

        $updatedCards = collect();
        $updatedCount = 0;
        $skippedCount = 0;
        $missingNumbers = [];

        foreach ($tmpNumbers as $tmpNumber) {
            $card = $cards->get($tmpNumber);

            if (!$card) {
                $missingNumbers[] = $tmpNumber;
                continue;
            }

            if ($validated['status'] === 'issued' && $card->status !== 'downloaded') {
                $skippedCount++;
                continue;
            }

            $card->status = $validated['status'];
            $card->updated_at = now();
            $card->save();

            $updatedCards->push($card->fresh());
            $updatedCount++;
        }

        $message = "{$updatedCount} Temporary ID cards updated successfully.";

        if ($skippedCount > 0) {
            $message .= " {$skippedCount} cards were skipped because they were not downloaded.";
        }

        if (!empty($missingNumbers)) {
            $message .= " Missing cards: " . implode(', ', $missingNumbers);
        }

        return view('admin.temporary_id_cards.update-status', [
            'updatedCards'   => $updatedCards,
            'start'          => $startInput,
            'end'            => $endInput,
            'selectedStatus' => $validated['status'],
            'message'        => $message,
        ])->with('success', $message);
    }

    /**
     * Download PDF from DB records
     */
    public function downloadPdf(Request $request)
    {
        $validated = $request->validate([
            'start' => ['required', 'string', 'regex:/^TMP\d+$/i'],
            'end'   => ['required', 'string', 'regex:/^TMP\d+$/i'],
        ]);

        $startNum = (int) preg_replace('/^TMP/i', '', strtoupper(trim($validated['start'])));
        $endNum   = (int) preg_replace('/^TMP/i', '', strtoupper(trim($validated['end'])));

        if ($startNum > $endNum) {
            return back()->with('error', 'Start TMP number must be smaller than End TMP number.');
        }

        if (($endNum - $startNum + 1) > 120) {
            return back()->with('error', 'You can download maximum 120 cards at once.');
        }

        $tmpNumbers = collect(range($startNum, $endNum))->map(function ($num) {
            return 'TMP' . str_pad($num, 3, '0', STR_PAD_LEFT);
        });

        $cards = TemporaryIdCard::whereIn('temporary_id_number', $tmpNumbers)
            ->orderByRaw("CAST(SUBSTRING(temporary_id_number, 4) AS UNSIGNED)")
            ->get();

        $codes = $cards->map(function ($card, $index) {
            return [
                'id'        => $card->id,
                'number'    => $index + 1,
                'code'      => $card->temporary_id_number,
                'qr_base64' => $card->qr_base64,
            ];
        });


        $pdf = Pdf::loadView('admin.temporary_id_cards.pdf', [
            'codes' => $codes,
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('encoding', 'UTF-8')
            ->setOption('enable-local-file-access', true)
            ->setOption('print-media-type', true)
            ->setOption('background', true)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('dpi', 300)
            ->setOption('zoom', 1)
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0);

        TemporaryIdCard::whereIn('id', $cards->pluck('id'))
            ->update([
                'status' => 'downloaded',
                'updated_at' => now(),
            ]);

        return $pdf->download('temporary-id-cards.pdf');
    }
}
