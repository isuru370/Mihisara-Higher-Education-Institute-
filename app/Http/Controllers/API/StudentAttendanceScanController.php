<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class StudentAttendanceScanController extends Controller
{
    /**
     * @var AttendanceScanService
     */
    protected $attendanceScanService;

    /**
     * Constructor
     */
    public function __construct(AttendanceScanService $attendanceScanService)
    {
        $this->attendanceScanService = $attendanceScanService;
    }

    /**
     * Scan Student QR
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        try {

            Log::info('Attendance Scan Request', [
                'qr_code' => $request->qr_code,
            ]);

            $result = $this->attendanceScanService
                ->scan($request->qr_code);

            Log::info('Attendance Scan Response', [
                'success' => $result['success'],
                'message' => $result['message'],
            ]);

            return response()->json(
                [
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'data'    => $result['data'],
                ],
                $result['status_code']
            );

        } catch (Throwable $e) {

            Log::error('Attendance Scan Controller Failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error.',
                'data'    => null,
            ], 500);
        }
    }
}