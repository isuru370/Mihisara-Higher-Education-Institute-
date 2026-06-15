<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WeeklyTimeTableExport;
use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ClassTimeTableController extends Controller
{
    public function weeklyTimeTable(Request $request)
    {
        try {
            $validated = $request->validate([
                'week_date' => ['nullable', 'date'],
            ]);

            $selectedDate = $validated['week_date'] ?? now()->toDateString();
            $date = Carbon::parse($selectedDate);

            $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endOfWeek   = $date->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

            $schedules = ClassSchedule::query()
                ->select([
                    'id',
                    'student_class_id',
                    'class_category_fee_id',
                    'class_date',
                    'start_time',
                    'end_time',
                    'status',
                    'class_hall_id'
                ])
                ->with([
                    'Hall:id,hall_name',
                    'studentClass:id,class_name,grade_id',
                    'studentClass.grade:id,grade_name',
                    'classCategoryFee:id,class_category_id,fee',
                    'classCategoryFee.category:id,category_name',
                ])
                ->whereBetween('class_date', [
                    $startOfWeek->toDateString(),
                    $endOfWeek->toDateString(),
                ])
                ->orderBy('class_date')
                ->orderBy('start_time')
                ->get();

            return view('admin.class-time-table.weekly', compact(
                'schedules',
                'selectedDate',
                'startOfWeek',
                'endOfWeek'
            ));
        } catch (\Throwable $e) {
            Log::error('Weekly timetable load failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to load weekly timetable. Please try again later.');
        }
    }

    /**
     * Download Weekly Timetable as PDF
     */
    public function downloadPdf(Request $request)
    {
        try {
            $selectedDate = $request->get('week_date', now()->toDateString());
            $date = Carbon::parse($selectedDate);

            $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endOfWeek   = $date->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

            $schedules = ClassSchedule::query()
                ->select([
                    'id',
                    'student_class_id',
                    'class_category_fee_id',
                    'class_date',
                    'start_time',
                    'end_time',
                    'status',
                    'class_hall_id',
                ])
                ->with([
                    'Hall:id,hall_name',
                    'studentClass:id,class_name,grade_id',
                    'studentClass.grade:id,grade_name',
                    'classCategoryFee:id,class_category_id,fee',
                    'classCategoryFee.category:id,category_name',
                ])
                ->whereBetween('class_date', [
                    $startOfWeek->toDateString(),
                    $endOfWeek->toDateString(),
                ])
                ->orderBy('class_date')
                ->orderBy('start_time')
                ->get();

            $weekNumber = $date->weekOfYear;
            $year = $date->year;

            $data = [
                'schedules' => $schedules,
                'startOfWeek' => $startOfWeek,
                'endOfWeek' => $endOfWeek,
                'selectedDate' => $selectedDate,
                'weekNumber' => $weekNumber,
                'year' => $year,
                'generatedAt' => now(),
            ];

            $pdf = Pdf::loadView('admin.class-time-table.pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download("weekly_timetable_week_{$weekNumber}_{$year}.pdf");
        } catch (\Throwable $e) {
            Log::error('PDF download failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Failed to download PDF. Please try again later.');
        }
    }

    /**
     * Download Weekly Timetable as Excel
     */
    public function downloadExcel(Request $request)
    {
        try {
            $selectedDate = $request->get('week_date', now()->toDateString());
            $date = Carbon::parse($selectedDate);

            $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endOfWeek   = $date->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

            $schedules = ClassSchedule::query()
                ->select([
                    'id',
                    'student_class_id',
                    'class_category_fee_id',
                    'class_date',
                    'start_time',
                    'end_time',
                    'status',
                    'class_hall_id',
                ])
                ->with([
                    'Hall:id,hall_name',
                    'studentClass:id,class_name,grade_id',
                    'studentClass.grade:id,grade_name',
                    'classCategoryFee:id,class_category_id,fee',
                    'classCategoryFee.category:id,category_name',
                    'classHall:id,hall_name',
                ])
                ->whereBetween('class_date', [
                    $startOfWeek->toDateString(),
                    $endOfWeek->toDateString(),
                ])
                ->orderBy('class_date')
                ->orderBy('start_time')
                ->get();

            $weekNumber = $date->weekOfYear;
            $year = $date->year;

            $export = new WeeklyTimeTableExport($schedules, $startOfWeek, $endOfWeek);

            return Excel::download($export, "weekly_timetable_week_{$weekNumber}_{$year}.xlsx");
        } catch (\Throwable $e) {
            Log::error('Excel download failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Failed to download Excel. Please try again later.');
        }
    }
}
