<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesTemplateSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return collect([]);
    }
    public function title(): string
    {
        return 'Categories';
    }
    public function headings(): array
    {
        return ['ID', 'Name', 'Slug', 'Parent Category', 'Description', 'Sort Order', 'Active'];
    }
}
