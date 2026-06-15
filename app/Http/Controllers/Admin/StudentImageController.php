<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickPhoto;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentImageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $quickPhotos = QuickPhoto::query()
            ->leftJoin('students', function ($join) {
                $join->on('quick_photos.image_path', '=', 'students.img_url');
            })
            ->select(
                'quick_photos.*',
                'students.id as student_id',
                'students.initial_name',
                'students.guardian_mobile',
                'students.permanent_qr_active',
                'students.custom_id as student_custom_id',
                'students.temporary_qr_code'
            )
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('quick_photos.custom_id', 'like', "%{$search}%")
                        ->orWhere('students.initial_name', 'like', "%{$search}%")
                        ->orWhere('students.guardian_mobile', 'like', "%{$search}%")
                        ->orWhere('students.custom_id', 'like', "%{$search}%")
                        ->orWhere('students.temporary_qr_code', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('quick_photos.id')
            ->get()
            ->map(function ($item) {
                $item->student_qr = null;

                if ($item->student_id) {
                    $item->student_qr = $item->permanent_qr_active
                        ? $item->student_custom_id
                        : $item->temporary_qr_code;
                }

                return $item;
            });

        return view('admin.student-image.index', compact('quickPhotos', 'search'));
    }

    public function assign(Request $request, QuickPhoto $quickPhoto)
    {
        $request->validate([
            'qr_code' => ['required', 'string', 'max:255'],
        ], [
            'qr_code.required' => 'Please enter a QR code.',
            'qr_code.string'   => 'QR code must be a valid text value.',
            'qr_code.max'      => 'QR code is too long.',
        ]);

        $qrCode = trim($request->qr_code);

        if ($quickPhoto->is_active == 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'qr_code' => 'This image is already assigned.',
                ]);
        }

        $student = Student::where('custom_id', $qrCode)
            ->orWhere('temporary_qr_code', $qrCode)
            ->first();

        if (! $student) {
            return back()
                ->withInput()
                ->withErrors([
                    'qr_code' => 'No student was found for the entered QR code.',
                ]);
        }

        $anotherStudentUsingSameImage = Student::where('img_url', $quickPhoto->image_path)
            ->where('id', '!=', $student->id)
            ->first();

        if ($anotherStudentUsingSameImage) {
            return back()
                ->withInput()
                ->withErrors([
                    'qr_code' => 'This image is already assigned to another student.',
                ]);
        }

        DB::transaction(function () use ($student, $quickPhoto) {
            $student->update([
                'img_url' => $quickPhoto->image_path,
                'last_image_update_at' => now(),
            ]);

            $quickPhoto->update([
                'is_active' => 0,
            ]);
        });

        return redirect()
            ->route('admin.student-images.index')
            ->with('success', 'Image assigned successfully.');
    }
}
