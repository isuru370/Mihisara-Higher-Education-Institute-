<?php

namespace App\Exports;

use App\Models\Organizer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrganizerExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Organizer::query();

        if ($this->request && $this->request->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->request->search . '%')
                    ->orWhere('mobile', 'like', '%' . $this->request->search . '%')
                    ->orWhere('email', 'like', '%' . $this->request->search . '%')
                    ->orWhere('nic', 'like', '%' . $this->request->search . '%')
                    ->orWhere('code', 'like', '%' . $this->request->search . '%');
            });
        }

        if ($this->request && $this->request->is_active !== null && $this->request->is_active !== '') {
            $query->where('is_active', $this->request->is_active === 'true');
        }

        return $query->latest()->get()->map(function ($organizer) {
            return [
                'Code'       => $organizer->code,
                'Name'       => $organizer->name,
                'Mobile'     => $organizer->mobile,
                'Email'      => $organizer->email,
                'NIC'        => $organizer->nic,
                'Status'     => $organizer->is_active ? 'Active' : 'Inactive',
                'Note'       => $organizer->note,
                'Created By'  => optional($organizer->createdBy)->name,
                'Created At'  => optional($organizer->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Mobile',
            'Email',
            'NIC',
            'Status',
            'Note',
            'Created By',
            'Created At',
        ];
    }
}
