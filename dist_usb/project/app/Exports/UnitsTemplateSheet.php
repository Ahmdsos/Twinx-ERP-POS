<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class UnitsTemplateSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return collect([]);
    }
    public function title(): string
    {
        return 'Units';
    }
    public function headings(): array
    {
        return ['ID', 'Name', 'Abbreviation', 'Is Base', 'Base Unit', 'Conversion Factor', 'Active'];
    }
}
