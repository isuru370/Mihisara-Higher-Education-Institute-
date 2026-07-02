<?php

namespace App\Services\Admin;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Log;

class FcmTokenService
{
    /**
     * Get all tokens with filters.
     */
    public function getTokens(array $filters = [], int $perPage = 20)
    {
        $query = FcmToken::with('student')->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->active();
            } elseif ($filters['status'] === 'inactive') {
                $query->inactive();
            }
        }

        if (!empty($filters['device_type'])) {
            if ($filters['device_type'] === 'android') {
                $query->android();
            } elseif ($filters['device_type'] === 'ios') {
                $query->ios();
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('token', 'like', "%{$search}%")
                    ->orWhere('device_name', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('initial_name', 'like', "%{$search}%")
                            ->orWhere('custom_id', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get token statistics.
     */
    public function getStats(): array
    {
        return [
            'total' => FcmToken::count(),
            'active' => FcmToken::active()->count(),
            'inactive' => FcmToken::inactive()->count(),
            'android' => FcmToken::android()->count(),
            'ios' => FcmToken::ios()->count(),
            'unique_students' => FcmToken::distinct('student_id')->count('student_id'),
        ];
    }

    /**
     * Deactivate token.
     */
    public function deactivate(FcmToken $token): bool
    {
        if (!$token->is_active) {
            return false;
        }

        $token->update(['is_active' => false]);

        Log::info('FCM token deactivated', [
            'token_id' => $token->id,
            'student_id' => $token->student_id,
        ]);

        return true;
    }

    /**
     * Activate token.
     */
    public function activate(FcmToken $token): bool
    {
        if ($token->is_active) {
            return false;
        }

        $token->update(['is_active' => true]);

        Log::info('FCM token activated', [
            'token_id' => $token->id,
            'student_id' => $token->student_id,
        ]);

        return true;
    }

    /**
     * Delete token.
     */
    public function delete(FcmToken $token): bool
    {
        Log::info('FCM token deleted', [
            'token_id' => $token->id,
            'student_id' => $token->student_id,
        ]);

        return (bool) $token->delete();
    }

    /**
     * Delete all inactive tokens.
     */
    public function deleteInactive(): int
    {
        $count = FcmToken::inactive()->count();

        if ($count > 0) {
            FcmToken::inactive()->delete();

            Log::info('All inactive FCM tokens deleted', [
                'count' => $count,
            ]);
        }

        return $count;
    }

    /**
     * Get tokens for a specific student.
     */
    public function getStudentTokens(int $studentId)
    {
        return FcmToken::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
