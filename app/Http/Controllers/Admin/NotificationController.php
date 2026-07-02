<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Student;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Services\Notification\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    protected AdminNotificationService $notificationService;

    public function __construct(AdminNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of notifications.
     */
    public function index(Request $request): View
    {
        $query = Notification::query()
            ->with(['student', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('initial_name', 'like', "%{$search}%")
                            ->orWhere('custom_id', 'like', "%{$search}%");
                    });
            });
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total' => Notification::count(),
            'pending' => Notification::pending()->count(),
            'sent' => Notification::sent()->count(),
            'failed' => Notification::failed()->count(),
            'unread' => Notification::unread()->count(),
            'today' => Notification::whereDate('created_at', today())->count(),
        ];

        // Statuses and types for filters
        $statuses = NotificationStatus::all();
        $types = NotificationType::all();

        return view('admin.notifications.index', compact(
            'notifications',
            'stats',
            'statuses',
            'types'
        ));
    }

    /**
     * Show a single notification.
     */
    public function show(int $id): View
    {
        $notification = Notification::with(['student', 'creator'])
            ->findOrFail($id);


        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create(): View
    {
        $students = Student::where('is_active', true)
            ->orderBy('initial_name')
            ->get(['id', 'initial_name', 'custom_id']);

        $types = NotificationType::all();
        $statuses = NotificationStatus::all();

        return view('admin.notifications.create', compact('students', 'types', 'statuses'));
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:150',
            'body' => 'required|string|max:1000',
            'type' => 'required|string|in:' . implode(',', NotificationType::all()),
            'scheduled_at' => 'nullable|date|after:now',
            'data' => 'nullable|array',
        ]);

        try {
            $notification = $this->notificationService->create($validated);

            return redirect()
                ->route('admin.notifications.show', $notification->id)
                ->with('success', 'Notification created and queued successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to multiple students.
     */
    public function sendBulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'title' => 'required|string|max:150',
            'body' => 'required|string|max:1000',
            'type' => 'required|string|in:' . implode(',', NotificationType::all()),
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        try {
            $count = $this->notificationService->sendBulk($validated);

            return redirect()
                ->route('admin.notifications.index')
                ->with('success', "{$count} notifications queued successfully!");
        } catch (\Exception $e) {
            Log::error('Failed to send bulk notifications', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to send notifications: ' . $e->getMessage());
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $id): RedirectResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsRead();

        return redirect()
            ->back()
            ->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        Notification::unread()->update(['read_at' => now()]);

        Log::info('All notifications marked as read', [
            'admin_id' => auth()->id(),
            'count' => Notification::unread()->count(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'All notifications marked as read.');
    }

    /**
     * Retry a failed notification.
     */
    public function retry(int $id): RedirectResponse
    {
        try {
            $notification = Notification::findOrFail($id);

            if (!$notification->canRetry()) {
                return redirect()
                    ->back()
                    ->with('error', 'This notification cannot be retried.');
            }

            $this->notificationService->retry($notification);

            return redirect()
                ->back()
                ->with('success', 'Notification retry queued successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to retry notification', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to retry notification: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a pending notification.
     */
    public function cancel(int $id): RedirectResponse
    {
        $notification = Notification::findOrFail($id);

        if (!$notification->isPending()) {
            return redirect()
                ->back()
                ->with('error', 'Only pending notifications can be cancelled.');
        }

        $notification->markAsCancelled();

        return redirect()
            ->back()
            ->with('success', 'Notification cancelled successfully.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(int $id): RedirectResponse
    {
        $notification = Notification::findOrFail($id);

        // Prevent deleting sent notifications (optional)
        if ($notification->wasSent()) {
            return redirect()
                ->back()
                ->with('error', 'Sent notifications cannot be deleted.');
        }

        $notification->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }

    /**
     * Delete old notifications.
     */
    public function deleteOld(Request $request): RedirectResponse
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $days = $request->days;
        $deleted = Notification::where('created_at', '<', now()->subDays($days))
            ->whereIn('status', [NotificationStatus::SENT, NotificationStatus::FAILED, NotificationStatus::CANCELLED])
            ->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', "{$deleted} notifications older than {$days} days deleted.");
    }

    /**
     * Export notifications (CSV).
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Notification::with(['student', 'creator'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="notifications_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($notifications) {
            $handle = fopen('php://output', 'w');

            // Headers
            fputcsv($handle, [
                'ID',
                'Student',
                'Title',
                'Body',
                'Type',
                'Status',
                'Sent At',
                'Read At',
                'Created At',
                'Created By',
            ]);

            // Data
            foreach ($notifications as $notification) {
                fputcsv($handle, [
                    $notification->id,
                    $notification->student?->initial_name ?? 'N/A',
                    $notification->title,
                    $notification->body,
                    $notification->type,
                    $notification->status,
                    $notification->sent_at?->format('Y-m-d H:i:s'),
                    $notification->read_at?->format('Y-m-d H:i:s'),
                    $notification->created_at->format('Y-m-d H:i:s'),
                    $notification->creator?->name ?? 'System',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
