<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentCardRegistrationRequest;
use App\Services\StudentCardRegistrationService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentCardRegistrationController extends Controller
{
    protected StudentCardRegistrationService $registrationService;

    public function __construct(
        StudentCardRegistrationService $registrationService
    ) {
        $this->registrationService = $registrationService;
    }

    /**
     * Show Student Card Registration Form.
     */
    public function create()
    {
        return $this->registrationService->createForm();
    }

    /**
     * Register Student using Physical Student Card.
     */
    public function store(StoreStudentCardRegistrationRequest $request)
    {
        DB::beginTransaction();

        try {

            $result = $this->registrationService->register($request);

            DB::commit();

            return redirect()
                ->route('admin.student-card-registration.create')
                ->with($result);

        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Student Card Registration Failed', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}