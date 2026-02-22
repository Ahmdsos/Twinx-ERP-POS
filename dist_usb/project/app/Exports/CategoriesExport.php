<?php

namespace App\Exports;

use Modules\Inventory\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Categories';
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Category::with('parent')->get();
    }

    public function map($category): array
    {
        return [
            $category->id,
            $category->parent ? $category->parent->name : '',
            $category->name,
            $category->slug,
            $category->description,
            $category->sort_order,
            $category->is_active ? 'Yes' : 'No',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Parent Category',
            'Name',
            'Slug',
            'Description',
            'Sort Order',
            'Active',
        ];
    }
}
