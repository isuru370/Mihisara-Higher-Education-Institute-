<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Grade;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StudentsImport implements ToModel, WithHeadingRow
{
    use Importable;

    public $errors = [];
    public $rowNumber = 1;

    public function model(array $row)
    {
        $this->rowNumber++;

        // ✅ Required fields validation
        if (empty($row['fname']) || empty($row['mobile']) || empty($row['grade_id'])) {
            $this->logError("Row {$this->rowNumber}: missing required fields");
            return null;
        }

        // ✅ Generate custom_id if missing
        $customId = $row['custom_id'] ?? $this->generateCustomId($row['grade_id']);

        // ✅ Duplicate check
        $exists = Student::where('custom_id', $customId)
            ->orWhere('mobile', $row['mobile'])
            ->first();

        if ($exists) {
            $this->logError("Row {$this->rowNumber}: duplicate found");
            return null;
        }

        // ✅ Birthday validation
        $bday = null;
        if (!empty($row['bday'])) {
            try {
                $bday = Carbon::parse($row['bday'])->format('Y-m-d');
            } catch (\Exception $e) {
                $this->logError("Row {$this->rowNumber}: invalid birthday format");
            }
        }

        // ✅ Handle image upload
        $imageURL = null;
        if (!empty($row['image_path'])) {
            if (file_exists($row['image_path'])) {
                $extension = strtolower(pathinfo($row['image_path'], PATHINFO_EXTENSION));

                // Allow only safe extensions
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($extension, $allowed)) {
                    $this->logError("Row {$this->rowNumber}: invalid image type");
                } else {
                    $imageName = Str::uuid() . '.' . $extension;
                    $uploadPath = public_path('uploads/students');

                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    try {
                        copy($row['image_path'], $uploadPath . '/' . $imageName);
                        $imageURL = asset('uploads/students/' . $imageName);
                    } catch (\Exception $e) {
                        $this->logError("Row {$this->rowNumber}: image copy failed - " . $e->getMessage());
                    }
                }
            } else {
                $this->logError("Row {$this->rowNumber}: image file not found");
            }
        }

        // ✅ Return Student model
        return new Student([
            'custom_id'       => $customId,
            'fname'           => $row['fname'],
            'lname'           => $row['lname'] ?? null,
            'mobile'          => $row['mobile'],
            'email'           => $row['email'] ?? null,
            'whatsapp_mobile' => $row['whatsapp_mobile'] ?? $row['mobile'],
            'nic'             => $row['nic'] ?? null,
            'bday'            => $bday,
            'gender'          => $row['gender'] ?? 'Not specified',
            'address1'        => $row['address1'] ?? null,
            'address2'        => $row['address2'] ?? null,
            'address3'        => $row['address3'] ?? null,
            'guardian_fname'  => $row['guardian_fname'] ?? null,
            'guardian_lname'  => $row['guardian_lname'] ?? null,
            'guardian_nic'    => $row['guardian_nic'] ?? null,
            'guardian_mobile' => $row['guardian_mobile'] ?? null,
            'grade_id'        => $row['grade_id'],
            'class_type'      => $row['class_type'] ?? 'OFFLINE',
            'student_school'  => $row['student_school'] ?? null,
            'img_url'         => $imageURL,
        ]);
    }

    private function generateCustomId($gradeId)
    {
        $grade = Grade::find($gradeId);
        if (!$grade) return 'CS00000';

        if (is_numeric($grade->grade_name)) {
            $gradeCode = str_pad($grade->grade_name, 2, '0', STR_PAD_LEFT);
        } else {
            preg_match('/\d{4}/', $grade->grade_name, $matches);
            $year = $matches ? substr($matches[0], 2) : "00";
            $gradeCode = $year;
        }

        $studentCount = Student::where('grade_id', $gradeId)->count() + 1;
        return "CS{$gradeCode}" . str_pad($studentCount, 3, '0', STR_PAD_LEFT);
    }

    private function logError($message)
    {
        $this->errors[] = $message;
        Log::error($message);
    }
}
