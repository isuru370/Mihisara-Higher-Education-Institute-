<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class StudentPaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::query()
            ->with([

                'student:id,initial_name,permanent_qr_active,custom_id,temporary_qr_code',
                'enrollment:id,student_class_id,class_category_fee_id',
                'enrollment.studentClass:id,class_name',
                'enrollment.classCategoryFee:id,class_category_id',
                'enrollment.classCategoryFee.category:id,category_name',
                'collectedBy:id,name',
            ])
            ->select([
                'id',
                'student_id',
                'enrollment_id',
                'user_id',
                'mark_method',
                'amount',
                'receipt_number',
                'created_at',
            ])
            ->latest()
            ->paginate(15);

        return view(
            'admin.students-payments.index',
            compact('payments')
        );
    }

    public function show($id)
    {
        $payment = Payment::query()
            ->with([
                'student:id,initial_name,permanent_qr_active,custom_id,temporary_qr_code',
                'enrollment:id,student_class_id,class_category_fee_id',
                'enrollment.studentClass:id,class_name',
                'enrollment.classCategoryFee:id,class_category_id',
                'enrollment.classCategoryFee.category:id,category_name',
                'collectedBy:id,name',
            ])
            ->findOrFail($id);

        return view(
            'admin.students-payments.show',
            compact('payment')
        );
    }
}
