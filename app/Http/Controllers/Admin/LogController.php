<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogController extends Controller
{
    public function index()
    {
        try {
            $path = storage_path('logs/laravel.log');

            if (!File::exists($path)) {
                return view('admin.logs.index', [
                    'content' => '',
                    'logLines' => [],
                    'error' => 'Log file not found.'
                ]);
            }

            // Get last 5000 lines for better performance
            $lines = collect(file($path))
                ->filter()
                ->reverse()
                ->take(5000)
                ->reverse()
                ->values()
                ->toArray();

            return view('admin.logs.index', [
                'content' => implode('', $lines),
                'logLines' => $lines,
                'error' => null
            ]);
        } catch (Throwable $e) {
            Log::error('Laravel log viewer failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return view('admin.logs.index', [
                'content' => '',
                'logLines' => [],
                'error' => 'Unable to load log file.'
            ]);
        }
    }

    public function clear(Request $request)
    {
        try {
            $path = storage_path('logs/laravel.log');

            if (File::exists($path)) {
                // Clear the log file
                File::put($path, '');

                // Log the action
                Log::warning('Laravel log file cleared by user', [
                    'user_id' => auth()->id(),
                    'user_name' => auth()->user()->name,
                    'ip' => $request->ip(),
                ]);
            }

            return redirect()
                ->route('admin.logs.laravel.index')
                ->with('success', 'Laravel log file cleared successfully.');
        } catch (Throwable $e) {
            Log::error('Laravel log clear failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('admin.logs.laravel.index')
                ->with('error', 'Unable to clear log file.');
        }
    }

    public function download()
    {
        try {
            $path = storage_path('logs/laravel.log');

            if (!File::exists($path)) {
                return redirect()
                    ->route('admin.logs.laravel.index')
                    ->with('error', 'Laravel log file not found.');
            }

            return response()->download(
                $path,
                'laravel-log-' . now()->format('Y-m-d-His') . '.log'
            );
        } catch (Throwable $e) {
            Log::error('Laravel log download failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('admin.logs.laravel.index')
                ->with('error', 'Unable to download log file.');
        }
    }

    /**
     * Get log stats
     */
    public function stats()
    {
        try {
            $path = storage_path('logs/laravel.log');

            if (!File::exists($path)) {
                return response()->json([
                    'size' => 0,
                    'lines' => 0,
                    'errors' => 0,
                    'warnings' => 0,
                    'exceptions' => 0,
                ]);
            }

            $content = File::get($path);
            $lines = explode("\n", $content);

            $stats = [
                'size' => round(File::size($path) / 1024, 2), // KB
                'lines' => count($lines),
                'errors' => substr_count(strtolower($content), 'error'),
                'warnings' => substr_count(strtolower($content), 'warning'),
                'exceptions' => substr_count($content, 'exception'),
            ];

            return response()->json($stats);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Unable to get stats'], 500);
        }
    }
}
