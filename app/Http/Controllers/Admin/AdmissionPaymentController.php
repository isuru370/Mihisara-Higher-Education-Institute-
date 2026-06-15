<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionPayment;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdmissionPayment::with(['student', 'admission', 'collectedBy'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('initial_name', 'like', "%{$search}%")
                            ->orWhere('custom_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('admission', function ($admissionQuery) use ($search) {
                        $admissionQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(15)->appends($request->query());

        return view('admin.admission_payments.index', compact('payments'));
    }

    public function create(): View
    {
        $students = Student::select('id', 'custom_id', 'initial_name')
            ->orderBy('initial_name')
            ->get();

        $admissions = Admission::active()
            ->select('id', 'name', 'amount')
            ->orderBy('name')
            ->get();

        return view('admin.admission_payments.create', compact('students', 'admissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id'     => ['required', 'exists:students,id'],
            'admission_id'   => ['required', 'exists:admissions,id'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'status'         => ['nullable', 'in:pending,paid,cancelled,refunded'],
            'note'           => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['payment_method'] = $request->input('payment_method', 'cash');
        $validated['status'] = $request->input('status', 'paid');
        $validated['note'] = $request->filled('note') ? $request->note : null;


        $payment = AdmissionPayment::create($validated);

        return redirect()
            ->route('admin.admission-payments.show', $payment)
            ->with('success', 'Admission payment created successfully.');
    }

    public function show(AdmissionPayment $admissionPayment): View
    {
        $admissionPayment->load(['student', 'admission', 'collectedBy']);

        return view('admin.admission_payments.show', compact('admissionPayment'));
    }

    public function edit(AdmissionPayment $admissionPayment): View
    {
        $students = Student::select('id', 'custom_id', 'initial_name')
            ->orderBy('initial_name')
            ->get();

        $admissions = Admission::active()
            ->select('id', 'name', 'amount')
            ->orderBy('name')
            ->get();

        return view('admin.admission_payments.edit', compact('admissionPayment', 'students', 'admissions'));
    }

    public function update(Request $request, AdmissionPayment $admissionPayment): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'admission_id' => ['required', 'exists:admissions,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['payment_method'] = $request->input('payment_method', 'cash');
        $validated['status'] = $request->boolean('status', true);
        $validated['note'] = $request->filled('note') ? $request->note : null;

        $admissionPayment->update($validated);

        return redirect()
            ->route('admin.admission-payments.show', $admissionPayment)
            ->with('success', 'Admission payment updated successfully.');
    }

    public function destroy(AdmissionPayment $admissionPayment): RedirectResponse
    {
        $admissionPayment->delete();

        return redirect()
            ->route('admin.admission-payments.index')
            ->with('success', 'Admission payment deleted successfully.');
    }
}
