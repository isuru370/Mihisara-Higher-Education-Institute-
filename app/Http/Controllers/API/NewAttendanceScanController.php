<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceMarkService;
use App\Services\Attendance\AttendanceScanService;
use App\Services\Attendance\AttendanceStudentInfoService;
use App\Services\Notification\AttendanceNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Mobile app attendance: scan a student's QR / code and mark attendance in
 * one call.
 *
 * If more than one class is open for the student, nothing is marked and the
 * response has requires_selection = true with the open classes. The app
 * then calls again with class_schedule_id.
 */
class NewAttendanceScanController extends Controller
{
    protected AttendanceScanService $scanService;

    protected AttendanceMarkService $markService;

    protected AttendanceStudentInfoService $studentInfoService;

    protected AttendanceNotificationService $notificationService;

    public function __construct(
        AttendanceScanService $scanService,
        AttendanceMarkService $markService,
        AttendanceStudentInfoService $studentInfoService,
        AttendanceNotificationService $notificationService
    ) {
        $this->scanService = $scanService;
        $this->markService = $markService;
        $this->studentInfoService = $studentInfoService;
        $this->notificationService = $notificationService;
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'mark_method' => [
                'nullable',
                'string',
                Rule::in(['qr_mobile', 'qr_web', 'manual_mobile', 'manual_web']),
            ],
            'class_schedule_id' => ['nullable', 'integer'],
        ]);

        $markMethod = $validated['mark_method'] ?? 'qr_mobile';

        $classScheduleId = isset($validated['class_schedule_id'])
            ? (int) $validated['class_schedule_id']
            : null;

        try {

            $result = $this->scanService->scan(
                trim($validated['code']),
                $classScheduleId
            );

            /*
            |--------------------------------------------------------------------------
            | Error, or more than one class open (nothing marked)
            |--------------------------------------------------------------------------
            */

            if (!$result['success'] || !empty($result['requires_selection'])) {

                return response()->json([
                    'success' => $result['success'],
                    'requires_selection' => $result['requires_selection'] ?? false,
                    'message' => $result['message'],
                    'data' => $result['data'],
                ], $result['status_code']);
            }

            $context = $result['context'];

            if (!$this->markService->isStudentActive($context->student)) {

                return response()->json([
                    'success' => false,
                    'requires_selection' => false,
                    'message' => 'This student is inactive or disabled. Attendance cannot be marked.',
                    'data' => null,
                ], 403);
            }

            /*
            |--------------------------------------------------------------------------
            | Mark Attendance
            |--------------------------------------------------------------------------
            */

            $marked = $this->markService->mark($context, $markMethod);

            $attendance = $marked['attendance'];

            if (!$marked['already_marked']) {

                try {
                    $this->notificationService->sendSuccess(
                        $context->student,
                        $attendance,
                        $context->enrollment
                    );
                } catch (Throwable $e) {
                    // Attendance is saved; a failed SMS/push must not undo it.
                    Log::warning('Attendance notification failed', [
                        'attendance_id' => $attendance->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Response (summary counts include this attendance)
            |--------------------------------------------------------------------------
            */

            $studentInfo = $this->studentInfoService->build($context);

            $data = $result['data'];

            $data['enrollment'] = $studentInfo['enrollment'];
            $data['last_payment'] = $studentInfo['last_payment'];
            $data['current_month_payment'] = $studentInfo['current_month_payment'];
            $data['tute'] = $studentInfo['tute'];

            $data['attendance'] = array_merge(
                $studentInfo['attendance'] ?? [],
                [
                    'id' => $attendance->id,
                    'status' => 'present',
                    'marked_at' => optional($attendance->attended_at)->format('Y-m-d H:i:s'),
                    'mark_method' => $attendance->mark_method,
                    'already_marked' => $marked['already_marked'],
                ]
            );

            return response()->json([
                'success' => true,
                'requires_selection' => false,
                'message' => $marked['already_marked']
                    ? 'Attendance has already been marked.'
                    : 'Attendance marked successfully.',
                'data' => $data,
            ], 200);
        } catch (Throwable $e) {

            Log::error('New Attendance Scan Failed', [
                'code' => $validated['code'] ?? null,
                'class_schedule_id' => $classScheduleId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'requires_selection' => false,
                'message' => 'Something went wrong while marking attendance.',
                'data' => null,
            ], 500);
        }
    }
}
