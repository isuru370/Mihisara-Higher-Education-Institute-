<?php

namespace App\Services;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StudentQRService
{
    private const QR_TYPE_TEMPORARY = 'temporary';
    private const QR_TYPE_PERMANENT = 'permanent';

    public static function read(string $qrCode): array
    {
        try {
            $qrCode = trim($qrCode);

            if ($qrCode === '') {
                return self::error('QR code is required', 422);
            }

            return DB::transaction(function () use ($qrCode) {
                $student = self::findStudentByQrCode($qrCode);

                if (!$student) {
                    return self::error('QR code invalid', 404);
                }

                if ((bool) $student->student_disable) {
                    return self::error('Student is disabled', 403);
                }

                if (! (bool) $student->is_active) {
                    return self::error('Student is inactive', 403);
                }

                $qrType = self::getQrType($student, $qrCode);

                if (
                    $qrType === self::QR_TYPE_TEMPORARY &&
                    self::isTemporaryQrExpired($student)
                ) {
                    return self::error('QR code expired', 403);
                }

                if (
                    $qrType === self::QR_TYPE_PERMANENT &&
                    ! (bool) $student->permanent_qr_active
                ) {
                    $student->forceFill([
                        'permanent_qr_active' => true,
                    ])->save();
                }

                return self::success(
                    studentId: (int) $student->id,
                    studentCode: $qrCode,
                    qrType: $qrType
                );
            });
        } catch (Throwable $e) {
            Log::error('Student QR read failed', [
                'qr_code' => $qrCode ?? null,
                'error' => $e->getMessage(),
            ]);

            return self::error('Something went wrong while reading QR code', 500);
        }
    }

    private static function findStudentByQrCode(string $qrCode): ?Student
    {
        return Student::query()
            ->select([
                'id',
                'custom_id',
                'temporary_qr_code',
                'temporary_qr_code_expire_date',
                'is_active',
                'student_disable',
                'permanent_qr_active',
            ])
            ->where(function ($query) use ($qrCode) {
                $query->where('temporary_qr_code', $qrCode)
                    ->orWhere('custom_id', $qrCode);
            })
            ->lockForUpdate()
            ->first();
    }

    private static function getQrType(Student $student, string $qrCode): string
    {
        return self::isTemporaryQr($student, $qrCode)
            ? self::QR_TYPE_TEMPORARY
            : self::QR_TYPE_PERMANENT;
    }

    private static function isTemporaryQr(Student $student, string $qrCode): bool
    {
        return hash_equals((string) $student->temporary_qr_code, $qrCode);
    }

    private static function isTemporaryQrExpired(Student $student): bool
    {
        if (!$student->temporary_qr_code_expire_date) {
            return false;
        }

        return Carbon::now()->gt(Carbon::parse($student->temporary_qr_code_expire_date));
    }

    private static function success(int $studentId, string $studentCode, string $qrType): array
    {
        return [
            'success' => true,
            'status_code' => 200,
            'message' => 'QR code valid',
            'student_id' => $studentId,
            'student_code' => $studentCode,
            'qr_type' => $qrType,
        ];
    }

    private static function error(string $message, int $statusCode): array
    {
        return [
            'success' => false,
            'status_code' => $statusCode,
            'message' => $message,
            'student_id' => null,
            'student_code' => null,
            'qr_type' => null,
        ];
    }
}
