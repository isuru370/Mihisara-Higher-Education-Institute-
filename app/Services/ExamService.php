<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\StudentClass;
use App\Models\ClassCategory;
use App\Models\ClassCategoryFee;
use App\Models\ClassHall;
use App\Models\StudentClassEnrollment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ExamService
{
    /**
     * Get all exams with filters and pagination
     */
    public function getAllExams(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Exam::query()
            ->with(['studentClass', 'category', 'hall', 'cancelledBy']);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply class filter
        if (!empty($filters['student_class_id'])) {
            $query->where('student_class_id', $filters['student_class_id']);
        }

        // Apply date range filters
        if (!empty($filters['exam_date_from'])) {
            $query->whereDate('exam_date', '>=', $filters['exam_date_from']);
        }

        if (!empty($filters['exam_date_to'])) {
            $query->whereDate('exam_date', '<=', $filters['exam_date_to']);
        }

        // Default ordering
        $query->orderBy('exam_date', 'desc')
            ->orderBy('start_time', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get single exam by ID with relationships
     */
    public function getExamById(int $id): ?Exam
    {
        return Exam::with([
            'studentClass',
            'studentClass.grade',
            'studentClass.subject',
            'category',
            'hall',
            'cancelledBy',
            'results.student'
        ])->find($id);
    }

    /**
     * Create a new exam
     */
    public function createExam(array $data): Exam
    {
        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'scheduled';
        }

        return Exam::create($data);
    }

    /**
     * Update an existing exam
     */
    public function updateExam(Exam $exam, array $data): bool
    {
        return $exam->update($data);
    }

    /**
     * Cancel an exam with reason
     */
    public function cancelExam(Exam $exam, int $userId, string $reason): bool
    {
        return $exam->cancel($userId, $reason);
    }

    /**
     * Delete an exam (soft delete)
     */
    public function deleteExam(Exam $exam): bool
    {
        return $exam->delete();
    }

    /**
     * Check if a hall is available for a given time slot
     */
    public function checkHallAvailability(
        int $hallId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeExamId = null
    ): bool {
        $query = Exam::where('class_hall_id', $hallId)
            ->whereDate('exam_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($sub) use ($startTime, $endTime) {
                    $sub->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeExamId) {
            $query->where('id', '!=', $excludeExamId);
        }

        return !$query->exists();
    }

    /**
     * Get categories by class (only those with active fees)
     */
    public function getCategoriesByClass(int $classId): Collection
    {
        $classCategoryFees = ClassCategoryFee::with('category')
            ->where('student_class_id', $classId)
            ->where('is_active', true)
            ->get();

        // Extract unique categories
        $categories = $classCategoryFees->map(function ($fee) {
            return $fee->category;
        })->filter(function ($category) {
            return $category !== null && $category->is_active;
        })->unique('id')->values();

        Log::info('Categories loaded for class: ' . $classId, [
            'count' => $categories->count(),
            'categories' => $categories->pluck('category_name')->toArray()
        ]);

        return $categories;
    }

    /**
     * Get all active classes with relationships
     */
    public function getClasses(): Collection
    {
        return StudentClass::with(['grade', 'subject', 'teacher'])
            ->where('is_active', true)
            ->orderBy('class_name')
            ->get();
    }

    /**
     * Get all active halls
     */
    public function getHalls(): Collection
    {
        return ClassHall::where('is_active', true)
            ->orderBy('hall_name')
            ->get();
    }

    /**
     * Get all active categories
     */
    public function getAllCategories(): Collection
    {
        return ClassCategory::where('is_active', true)
            ->where('is_schedulable', true)
            ->orderBy('category_name')
            ->get();
    }

    /**
     * Get categories (alias for getAllCategories)
     */
    public function getCategories(): Collection
    {
        return $this->getAllCategories();
    }

    /**
     * Get exam statistics for dashboard
     */
    public function getExamStats(): array
    {
        return [
            'total' => Exam::count(),
            'scheduled' => Exam::where('status', 'scheduled')->count(),
            'ongoing' => Exam::where('status', 'ongoing')->count(),
            'completed' => Exam::where('status', 'completed')->count(),
            'cancelled' => Exam::where('status', 'cancelled')->count(),
            'upcoming' => Exam::where('status', 'scheduled')
                ->whereDate('exam_date', '>=', now()->toDateString())
                ->count(),
        ];
    }

    /**
     * Update exam status
     */
    public function updateExamStatus(Exam $exam, string $status): bool
    {
        $validStatuses = ['scheduled', 'ongoing', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            Log::warning('Invalid exam status attempted: ' . $status);
            return false;
        }

        return $exam->update(['status' => $status]);
    }

    /**
     * Get exams by class
     */
    public function getExamsByClass(int $classId): Collection
    {
        return Exam::with(['category', 'hall'])
            ->where('student_class_id', $classId)
            ->orderBy('exam_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();
    }

    /**
     * Get upcoming exams
     */
    public function getUpcomingExams(int $limit = 5): Collection
    {
        return Exam::with(['studentClass', 'category'])
            ->where('status', 'scheduled')
            ->whereDate('exam_date', '>=', now()->toDateString())
            ->orderBy('exam_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get today's exams
     */
    public function getTodayExams(): Collection
    {
        return Exam::with(['studentClass', 'category', 'hall'])
            ->whereDate('exam_date', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Get exams by date range
     */
    public function getExamsByDateRange(string $from, string $to): Collection
    {
        return Exam::with(['studentClass', 'category', 'hall'])
            ->whereDate('exam_date', '>=', $from)
            ->whereDate('exam_date', '<=', $to)
            ->where('status', '!=', 'cancelled')
            ->orderBy('exam_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'total_today' => $this->getTodayExams()->count(),
            'total_upcoming' => Exam::where('status', 'scheduled')
                ->whereDate('exam_date', '>=', now()->toDateString())
                ->count(),
            'total_completed' => Exam::where('status', 'completed')->count(),
            'total_cancelled' => Exam::where('status', 'cancelled')->count(),
            'total_scheduled' => Exam::where('status', 'scheduled')->count(),
            'total_ongoing' => Exam::where('status', 'ongoing')->count(),
        ];
    }

    /**
     * Get exams by status
     */
    public function getExamsByStatus(string $status): Collection
    {
        return Exam::with(['studentClass', 'category', 'hall'])
            ->where('status', $status)
            ->orderBy('exam_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();
    }

    /**
     * Count exams by status
     */
    public function countExamsByStatus(string $status): int
    {
        return Exam::where('status', $status)->count();
    }

    /**
     * Get exams scheduled for a specific date
     */
    public function getExamsByDate(string $date): Collection
    {
        return Exam::with(['studentClass', 'category', 'hall'])
            ->whereDate('exam_date', $date)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Check if exam exists with conflicts
     */
    public function hasConflicts(Exam $exam, array $data): bool
    {
        // Check hall availability
        $hallId = $data['class_hall_id'] ?? $exam->class_hall_id;
        $date = $data['exam_date'] ?? $exam->exam_date->format('Y-m-d');
        $startTime = $data['start_time'] ?? $exam->start_time;
        $endTime = $data['end_time'] ?? $exam->end_time;

        return !$this->checkHallAvailability($hallId, $date, $startTime, $endTime, $exam->id);
    }

    /**
     * Get upcoming exams count
     */
    public function getUpcomingExamsCount(): int
    {
        return Exam::where('status', 'scheduled')
            ->whereDate('exam_date', '>=', now()->toDateString())
            ->count();
    }

    /**
     * Get exams with results
     */
    public function getExamsWithResults(): Collection
    {
        return Exam::with(['results', 'studentClass'])
            ->whereHas('results')
            ->orderBy('exam_date', 'desc')
            ->get();
    }

    /**
     * Get exams without results
     */
    public function getExamsWithoutResults(): Collection
    {
        return Exam::with(['studentClass'])
            ->whereDoesntHave('results')
            ->where('status', 'completed')
            ->orderBy('exam_date', 'desc')
            ->get();
    }

    /**
     * Get exam by title (for search)
     */
    public function getExamByTitle(string $title): ?Exam
    {
        return Exam::with(['studentClass', 'category', 'hall'])
            ->where('title', 'like', '%' . $title . '%')
            ->first();
    }

    /**
     * Get recent exams
     */
    public function getRecentExams(int $limit = 10): Collection
    {
        return Exam::with(['studentClass', 'category'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Bulk update exam status
     */
    public function bulkUpdateStatus(array $examIds, string $status): int
    {
        $validStatuses = ['scheduled', 'ongoing', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            return 0;
        }

        return Exam::whereIn('id', $examIds)
            ->where('status', '!=', 'cancelled')
            ->update(['status' => $status]);
    }

    /**
     * Get exam schedule for a specific class
     */
    public function getClassExamSchedule(int $classId, ?string $date = null): Collection
    {
        $query = Exam::with(['category', 'hall'])
            ->where('student_class_id', $classId)
            ->where('status', '!=', 'cancelled');

        if ($date) {
            $query->whereDate('exam_date', $date);
        }

        return $query->orderBy('exam_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Check if hall is booked for any exam
     */
    public function isHallBooked(int $hallId, string $date): bool
    {
        return Exam::where('class_hall_id', $hallId)
            ->whereDate('exam_date', $date)
            ->where('status', '!=', 'cancelled')
            ->exists();
    }

    /**
     * Get students for exam mark entry
     */
    public function getExamStudents(int $examId)
    {
        $exam = Exam::findOrFail($examId);

        return StudentClassEnrollment::query()
            ->where('student_class_id', $exam->student_class_id)
            ->where('is_active', true)
            ->whereHas('classCategoryFee', function ($q) use ($exam) {
                $q->where(
                    'class_category_id',
                    $exam->class_category_id
                );
            })
            ->with([
                'student' => function ($q) {
                    $q->select(
                        'id',
                        'custom_id',
                        'initial_name'
                    );
                },

                'student.results' => function ($q) use ($exam) {
                    $q->where(
                        'exam_id',
                        $exam->id
                    );
                }
            ])
            ->get();
    }

    /**
     * Calculate grade from percentage
     */
    public function calculateGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 75 => 'A',
            $percentage >= 65 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 35 => 'S',
            default => 'F',
        };
    }
}
