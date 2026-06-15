<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentSplitSnapshot;

class InstitutePaymentReportController extends Controller
{

    public function yearlyPaymentReport(Request $request)
    {
        try {

            $year = $request->get('year', now()->year);

            $monthlyTotals = PaymentSplitSnapshot::query()
                ->selectRaw('
                MONTH(payment_date) as month,
                SUM(payment_amount) as total_payment,
                SUM(institution_amount) as institute_total
            ')
                ->whereYear('payment_date', $year)
                ->groupByRaw('MONTH(payment_date)')
                ->orderByRaw('MONTH(payment_date)')
                ->get()
                ->keyBy('month');

            $labels = [
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec'
            ];

            $totalPayments = [];
            $institutionPayments = [];

            for ($i = 1; $i <= 12; $i++) {

                $totalPayments[] = isset($monthlyTotals[$i])
                    ? (float) $monthlyTotals[$i]->total_payment
                    : 0;

                $institutionPayments[] = isset($monthlyTotals[$i])
                    ? (float) $monthlyTotals[$i]->institute_total
                    : 0;
            }

            return response()->json([
                'success' => true,
                'year'    => (int) $year,
                'labels'  => $labels,

                'total_payments' => $totalPayments,

                'institution_payments' => $institutionPayments,
            ]);
        } catch (\Exception $e) {

            Log::error('Yearly Payment Report Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
}
