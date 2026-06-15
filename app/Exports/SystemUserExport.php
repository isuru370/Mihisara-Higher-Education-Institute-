<?php

namespace App\Exports;

use App\Models\SystemUser;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SystemUserExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected ?Request $request;

    public function __construct(?Request $request = null)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = SystemUser::with('user')
            ->whereHas('user', function ($q) {
                $q->where('email', '!=', 'admin@nexorait.lk');
            });

        if ($this->request && $this->request->search) {
            $search = $this->request->search;

            $query->where(function ($q) use ($search) {
                $q->where('custom_id', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->request && $this->request->has('is_active') && $this->request->is_active !== '') {
            $query->where('is_active', $this->request->is_active === 'true');
        }

        return $query->latest()->get()->map(function ($systemUser) {
            return [
                'Custom ID'   => $systemUser->custom_id,
                'Full Name'   => $systemUser->full_name,
                'Mobile'      => $systemUser->mobile,
                'NIC'         => $systemUser->nic,
                'Birthday'    => optional($systemUser->bday)->format('Y-m-d'),
                'Gender'      => $systemUser->gender,
                'Address 1'   => $systemUser->address1,
                'Address 2'   => $systemUser->address2,
                'Address 3'   => $systemUser->address3,
                'Email'       => optional($systemUser->user)->email,
                'Status'      => $systemUser->is_active ? 'Active' : 'Inactive',
                'Note'        => $systemUser->note,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Custom ID',
            'Full Name',
            'Mobile',
            'NIC',
            'Birthday',
            'Gender',
            'Address 1',
            'Address 2',
            'Address 3',
            'Email',
            'Status',
            'Note',
        ];
    }
}
