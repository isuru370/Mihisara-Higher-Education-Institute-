<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class FcmTokenController extends Controller
{
    /**
     * Display a listing of FCM tokens.
     */
    public function index(Request $request): View
    {
        $query = FcmToken::with('student')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        // Filter by device type
        if ($request->filled('device_type')) {
            if ($request->device_type === 'android') {
                $query->android();
            } elseif ($request->device_type === 'ios') {
                $query->ios();
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('token', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($studentQuery) use ($search) {
                      $studentQuery->where('initial_name', 'like', "%{$search}%")
                          ->orWhere('custom_id', 'like', "%{$search}%");
                  });
            });
        }

        $tokens = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total' => FcmToken::count(),
            'active' => FcmToken::active()->count(),
            'inactive' => FcmToken::inactive()->count(),
            'android' => FcmToken::android()->count(),
            'ios' => FcmToken::ios()->count(),
            'unique_students' => FcmToken::distinct('student_id')->count('student_id'),
        ];

        return view('admin.fcm-tokens.index', compact('tokens', 'stats'));
    }

    /**
     * Show a single FCM token.
     */
    public function show(int $id): View
    {
        $token = FcmToken::with('student')->findOrFail($id);

        return view('admin.fcm-tokens.show', compact('token'));
    }

    /**
     * Show tokens for a specific student.
     */
    public function studentTokens(int $studentId): View
    {
        $student = Student::findOrFail($studentId);
        $tokens = FcmToken::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.fcm-tokens.student', compact('student', 'tokens'));
    }

    /**
     * Deactivate a token.
     */
    public function deactivate(int $id): RedirectResponse
    {
        $token = FcmToken::findOrFail($id);

        if (!$token->is_active) {
            return redirect()
                ->back()
                ->with('warning', 'Token is already inactive.');
        }

        $token->update([
            'is_active' => false,
        ]);

        Log::info('FCM token deactivated by admin', [
            'token_id' => $token->id,
            'student_id' => $token->student_id,
            'admin_id' => auth()->id(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'FCM token deactivated successfully.');
    }

    /**
     * Activate a token.
     */
    public function activate(int $id): RedirectResponse
    {
        $token = FcmToken::findOrFail($id);

        if ($token->is_active) {
            return redirect()
                ->back()
                ->with('warning', 'Token is already active.');
        }

        $token->update([
            'is_active' => true,
        ]);

        Log::info('FCM token activated by admin', [
            'token_id' => $token->id,
            'student_id' => $token->student_id,
            'admin_id' => auth()->id(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'FCM token activated successfully.');
    }

    /**
     * Delete a token.
     */
    public function destroy(int $id): RedirectResponse
    {
        $token = FcmToken::findOrFail($id);

        Log::info('FCM token deleted by admin', [
            'token_id' => $token->id,
            'student_id' => $token->student_id,
            'admin_id' => auth()->id(),
        ]);

        $token->delete();

        return redirect()
            ->route('admin.fcm-tokens.index')
            ->with('success', 'FCM token deleted successfully.');
    }

    /**
     * Delete all inactive tokens.
     */
    public function deleteInactive(): RedirectResponse
    {
        $count = FcmToken::inactive()->count();

        if ($count === 0) {
            return redirect()
                ->back()
                ->with('warning', 'No inactive tokens to delete.');
        }

        FcmToken::inactive()->delete();

        Log::info('All inactive FCM tokens deleted by admin', [
            'count' => $count,
            'admin_id' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.fcm-tokens.index')
            ->with('success', "{$count} inactive tokens deleted successfully.");
    }

    /**
     * Export tokens as CSV.
     */
    public function export(Request $request)
    {
        $query = FcmToken::with('student');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        $tokens = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fcm_tokens_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($tokens) {
            $handle = fopen('php://output', 'w');

            // Headers
            fputcsv($handle, [
                'ID',
                'Student',
                'Student ID',
                'Token',
                'Device Name',
                'Device Type',
                'App Version',
                'Status',
                'Last Login',
                'Created At',
            ]);

            // Data
            foreach ($tokens as $token) {
                fputcsv($handle, [
                    $token->id,
                    $token->student?->initial_name ?? 'N/A',
                    $token->student?->custom_id ?? 'N/A',
                    $token->masked_token,
                    $token->device_name ?? 'N/A',
                    $token->device_type_label,
                    $token->app_version ?? 'N/A',
                    $token->status_label,
                    $token->last_login_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $token->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get token statistics (AJAX).
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $stats = [
            'total' => FcmToken::count(),
            'active' => FcmToken::active()->count(),
            'inactive' => FcmToken::inactive()->count(),
            'android' => FcmToken::android()->count(),
            'ios' => FcmToken::ios()->count(),
            'unique_students' => FcmToken::distinct('student_id')->count('student_id'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}