<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\AdmissionPayment;
use App\Models\ExtraIncome;
use App\Exports\Receipts\ReceiptsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $receipts = $this->getReceipts($request);

        $totalAmount = $receipts->sum('amount');
        $totalReceipts = $receipts->count();

        return view(
            'admin.receipts.index',
            compact(
                'receipts',
                'totalAmount',
                'totalReceipts'
            )
        );
    }

    public function exportExcel(Request $request)
    {
        $receipts = $this->getReceipts($request);

        return Excel::download(
            new ReceiptsExport($receipts),
            'receipts.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $receipts = $this->getReceipts($request);

        $totalAmount = $receipts->sum('amount');
        $totalReceipts = $receipts->count();

        $pdf = Pdf::loadView(
            'admin.pdf.receipts.receipts_pdf',
            compact(
                'receipts',
                'totalAmount',
                'totalReceipts'
            )
        );

        return $pdf->download('receipts.pdf');
    }

    private function getReceipts(Request $request)
    {
        $payments = Payment::query()
            ->select([
                'id',
                'receipt_number',
                'amount',
                'created_at',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'receipt_number' => $item->receipt_number,
                    'type' => 'Student Payment',
                    'amount' => $item->amount,
                    'date' => $item->created_at,
                    'url' => route(
                        'admin.students-payments.show',
                        $item->id
                    ),
                ];
            });

        $admissions = AdmissionPayment::query()
            ->select([
                'id',
                'receipt_number',
                'amount',
                'created_at',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'receipt_number' => $item->receipt_number,
                    'type' => 'Admission Payment',
                    'amount' => $item->amount,
                    'date' => $item->created_at,
                    'url' => route(
                        'admin.admission-payments.show',
                        $item->id
                    ),
                ];
            });

        $extraIncomes = ExtraIncome::query()
            ->select([
                'id',
                'receipt_number',
                'amount',
                'created_at',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'receipt_number' => $item->receipt_number,
                    'type' => 'Extra Income',
                    'amount' => $item->amount,
                    'date' => $item->created_at,
                    'url' => route(
                        'admin.extra-incomes.show',
                        $item->id
                    ),
                ];
            });

        $receipts = $payments
            ->merge($admissions)
            ->merge($extraIncomes);

        if ($request->filled('receipt_number')) {
            $receipts = $receipts->filter(function ($item) use ($request) {
                return str_contains(
                    strtolower($item['receipt_number'] ?? ''),
                    strtolower($request->receipt_number)
                );
            });
        }

        if ($request->filled('type')) {
            $receipts = $receipts->where(
                'type',
                $request->type
            );
        }

        if ($request->filled('from_date')) {
            $receipts = $receipts->filter(function ($item) use ($request) {
                return $item['date']->format('Y-m-d')
                    >= $request->from_date;
            });
        }

        if ($request->filled('to_date')) {
            $receipts = $receipts->filter(function ($item) use ($request) {
                return $item['date']->format('Y-m-d')
                    <= $request->to_date;
            });
        }

        return $receipts
            ->sortByDesc('date')
            ->values();
    }
}
