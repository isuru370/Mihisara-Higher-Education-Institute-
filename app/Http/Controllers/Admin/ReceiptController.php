<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\AdmissionPayment;
use App\Models\ExtraIncome;
use App\Exports\Receipts\ReceiptsExport;
use App\Models\ActivityLog;
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

        $totalAmount = $receipts
            ->where('status', 'Active')
            ->sum('amount');

        $totalReceipts = $receipts->count();

        $activeReceipts = $receipts->where('status', 'Active')->count();
        $deletedReceipts = $receipts->where('status', 'Deleted')->count();

        $pdf = Pdf::loadView(
            'admin.pdf.receipts.receipts_pdf',
            compact(
                'receipts',
                'totalAmount',
                'totalReceipts',
                'activeReceipts',
                'deletedReceipts'
            )
        );

        return $pdf->download('receipts.pdf');
    }

    private function getReceipts(Request $request)
    {
        $payments = Payment::withTrashed()
            ->select([
                'id',
                'receipt_number',
                'amount',
                'created_at',
                'deleted_at',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'receipt_number' => $item->receipt_number,
                    'type' => 'Student Payment',
                    'amount' => $item->amount,
                    'date' => $item->created_at,
                    'status' => $item->deleted_at ? 'Deleted' : 'Active',
                    'url' => route(
                        'admin.students-payments.show',
                        $item->id
                    ),
                ];
            });

        $admissions = AdmissionPayment::withTrashed()
            ->select([
                'id',
                'receipt_number',
                'amount',
                'created_at',
                'deleted_at',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'receipt_number' => $item->receipt_number,
                    'type' => 'Admission Payment',
                    'amount' => $item->amount,
                    'date' => $item->created_at,
                    'status' => $item->deleted_at ? 'Deleted' : 'Active',
                    'url' => route(
                        'admin.admission-payments.show',
                        $item->id
                    ),
                ];
            });

        $extraIncomes = ExtraIncome::withTrashed()
            ->select([
                'id',
                'receipt_number',
                'amount',
                'created_at',
                'deleted_at',
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'receipt_number' => $item->receipt_number,
                    'type' => 'Extra Income',
                    'amount' => $item->amount,
                    'date' => $item->created_at,
                    'status' => $item->deleted_at ? 'Deleted' : 'Active',
                    'url' => route(
                        'admin.extra-incomes.show',
                        $item->id
                    ),
                ];
            });

        $deletedPayments = ActivityLog::query()
            ->where('table_name', 'payments')
            ->where('action', 'force_deleted')
            ->get()
            ->map(function ($log) {

                $payment = data_get(
                    $log->old_values,
                    'payment',
                    []
                );

                return [
                    'id' => $log->record_id,
                    'receipt_number' => $payment['receipt_number'] ?? 'N/A',
                    'type' => 'Student Payment',
                    'amount' => $payment['amount'] ?? 0,
                    'date' => $log->created_at,
                    'status' => 'Force Deleted',
                    'url' => null,
                ];
            });

        $receipts = $payments
            ->merge($admissions)
            ->merge($extraIncomes)
            ->merge($deletedPayments);

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
