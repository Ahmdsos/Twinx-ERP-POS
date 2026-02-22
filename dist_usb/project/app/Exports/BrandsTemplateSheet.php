<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BrandsTemplateSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return collect([]);
    }
    public function title(): string
    {
        return 'Brands';
    }
    public function headings(): array
    {
        return ['ID', 'Name', 'Description', 'Website', 'Active'];
    }
}
