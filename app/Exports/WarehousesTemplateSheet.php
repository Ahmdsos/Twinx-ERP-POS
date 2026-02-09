<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class WarehousesTemplateSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return collect([]);
    }
    public function title(): string
    {
        return 'Warehouses';
    }
    public function headings(): array
    {
        return ['ID', 'Code', 'Name', 'Address', 'Phone', 'Email', 'Active'];
    }
}
