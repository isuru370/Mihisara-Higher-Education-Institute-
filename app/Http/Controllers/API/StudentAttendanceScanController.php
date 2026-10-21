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
            'class_schedule_id' => 'nullable|integer',
        ]);

        try {

            $classScheduleId = $request->filled('class_schedule_id')
                ? (int) $request->class_schedule_id
                : null;

            Log::info('Attendance Scan Request', [
                'qr_code' => $request->qr_code,
                'class_schedule_id' => $classScheduleId,
            ]);

            $result = $this->attendanceScanService
                ->scan($request->qr_code, $classScheduleId);

            Log::info('Attendance Scan Response', [
                'success' => $result['success'],
                'message' => $result['message'],
                'requires_selection' => $result['requires_selection'] ?? false,
            ]);

            return response()->json(
                [
                    'success' => $result['success'],
                    'requires_selection' => $result['requires_selection'] ?? false,
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