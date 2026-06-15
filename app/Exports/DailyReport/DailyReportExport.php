<?php

namespace App\Exports\DailyReport;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DailyReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected string $title,
        protected array $headings,
        protected array $columns,
        protected array $rows
    ) {
    }

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return array_map(
            fn ($column) => data_get($row, $column),
            $this->columns
        );
    }

    public function title(): string
    {
        return substr($this->title, 0, 31);
    }
}