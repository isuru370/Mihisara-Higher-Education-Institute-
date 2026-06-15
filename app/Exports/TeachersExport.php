<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeachersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Teacher::with('bankBranch.bank')
            ->get()
            ->map(function ($teacher) {
                return [
                    'Custom ID' => $teacher->custom_id,
                    'Full Name' => $teacher->full_name,
                    'Initials' => $teacher->initials,
                    'Email' => $teacher->email,
                    'Mobile' => $teacher->mobile,
                    'NIC' => $teacher->nic,
                    'Birthday' => optional($teacher->bday)->format('Y-m-d'),
                    'Gender' => ucfirst($teacher->gender),

                    'Bank' => $teacher->bankBranch->bank->bank_name ?? 'N/A',
                    'Branch' => $teacher->bankBranch->branch_name ?? 'N/A',
                    'Account Number' => $teacher->account_number ?? 'N/A',

                    'Graduation' => $teacher->graduation_details ?? '-',
                    'Experience' => $teacher->experience ?? '-',

                    'Status' => $teacher->is_active ? 'Active' : 'Inactive',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Custom ID',
            'Full Name',
            'Initials',
            'Email',
            'Mobile',
            'NIC',
            'Birthday',
            'Gender',
            'Bank',
            'Branch',
            'Account Number',
            'Graduation Details',
            'Experience',
            'Status',
        ];
    }
}