<?php

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use Maatwebsite\Excel\Concerns\WithTitle;

class BrandsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Brands';
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Brand::all();
    }

    public function map($brand): array
    {
        return [
            $brand->id,
            $brand->name,
            $brand->description,
            $brand->website,
            $brand->is_active ? 'Yes' : 'No',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Website',
            'Active',
        ];
    }
}
