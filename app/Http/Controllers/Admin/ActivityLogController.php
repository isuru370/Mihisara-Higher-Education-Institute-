<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ActivityLog::with('user');

            // Search by user name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('table_name', 'like', "%{$search}%")
                        ->orWhere('record_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            // Filter by action
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            // Filter by table name
            if ($request->filled('table_name')) {
                $query->where('table_name', $request->table_name);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Filter by user
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $logs = $query->latest()->paginate(20)->appends($request->query());

            // Get unique table names for filter dropdown
            $tables = ActivityLog::select('table_name')->distinct()->pluck('table_name');

            // Get users for filter dropdown
            $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

            // Get actions for filter dropdown
            $actions = ['created', 'updated', 'deleted', 'force_deleted'];

            return view('admin.activity-logs.index', compact('logs', 'tables', 'users', 'actions'));
        } catch (Throwable $e) {
            Log::error('Activity logs load failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Unable to load activity logs.');
        }
    }

    /**
     * Export activity logs to CSV/Excel
     */
    public function export(Request $request)
    {
        try {
            $query = ActivityLog::with('user');

            // Apply same filters as index
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('table_name', 'like', "%{$search}%")
                        ->orWhere('record_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            if ($request->filled('table_name')) {
                $query->where('table_name', $request->table_name);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $logs = $query->latest()->get();

            // Generate CSV
            $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.csv';
            $handle = fopen('php://temp', 'w');

            // Add headers
            fputcsv($handle, ['ID', 'Date', 'Time', 'User', 'Action', 'Table Name', 'Record ID', 'IP Address', 'Old Values', 'New Values']);

            // Add data
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->created_at?->format('Y-m-d'),
                    $log->created_at?->format('h:i A'),
                    $log->user?->name ?? 'System',
                    strtoupper($log->action),
                    $log->table_name,
                    $log->record_id,
                    $log->ip_address ?? '-',
                    json_encode($log->old_values, JSON_UNESCAPED_UNICODE),
                    json_encode($log->new_values, JSON_UNESCAPED_UNICODE),
                ]);
            }

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            return response($csvContent, 200)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename={$filename}");
        } catch (Throwable $e) {
            Log::error('Activity logs export failed', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to export activity logs.');
        }
    }

    /**
     * Clear old activity logs
     */
    public function clearOld(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

            return back()->with('success', "Deleted {$deleted} old activity logs older than {$days} days.");
        } catch (Throwable $e) {
            Log::error('Clear old logs failed', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to clear old activity logs.');
        }
    }
}
