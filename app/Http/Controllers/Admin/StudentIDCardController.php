<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentIdCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class StudentIDCardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->input('search', ''));

            // Get all records with pagination (not filtered by status)
            $students = StudentIdCard::query()
                ->with(['student:id,initial_name,custom_id,address1,address2,address3,img_url'])
                ->select(['id', 'student_id', 'card_no', 'status', 'registration_status', 'created_at'])
                ->where('registration_status', 'completed')  // Only show completed registrations
                // Removed status filter - show all statuses
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($mainQuery) use ($search) {
                        $mainQuery->where('card_no', 'like', "%{$search}%")
                            ->orWhereHas('student', function ($studentQuery) use ($search) {
                                $studentQuery->where('initial_name', 'like', "%{$search}%")
                                    ->orWhere('custom_id', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest('created_at')
                ->paginate(12)
                ->appends($request->query());

            return view('admin.student-id-cards.index', compact('students'));
        } catch (Throwable $e) {
            Log::error('Student ID Card Fetch Error', ['message' => $e->getMessage()]);
            return back()->with('error', 'Something went wrong.');
        }
    }

    // Status update single - FETCH use karanna
    public function updateStatus(Request $request, StudentIdCard $studentIdCard)
    {
        try {
            $request->validate([
                'status' => ['required', 'string', 'in:pending,downloaded,active,deleted']
            ]);

            $studentIdCard->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'id' => $studentIdCard->id,
                    'status' => $studentIdCard->status
                ]
            ]);
        } catch (Throwable $e) {
            Log::error('Status update failed', [
                'message' => $e->getMessage(),
                'status' => $request->status ?? null
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Status update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpdateStatus(Request $request)
    {
        try {
            $data = $request->validate([
                'student_ids' => ['required', 'array', 'min:1'],
                'student_ids.*' => ['integer', 'exists:student_id_cards,id'],
                'status' => ['required', 'string', 'in:pending,downloaded,active,deleted']
            ]);

            $updatedCount = StudentIdCard::whereIn('id', $data['student_ids'])
                ->update(['status' => $data['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Bulk status updated successfully',
                'data' => [
                    'updated_count' => $updatedCount,
                    'status' => $data['status']
                ]
            ]);
        } catch (Throwable $e) {
            Log::error('Bulk status update failed', [
                'message' => $e->getMessage(),
                'status' => $request->status ?? null
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Bulk status update failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
