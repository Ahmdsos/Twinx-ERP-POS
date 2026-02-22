<?php

namespace App\Exports;

use Modules\Inventory\Models\Warehouse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use Maatwebsite\Excel\Concerns\WithTitle;

class WarehousesExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Warehouses';
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Warehouse::with('manager')->get();
    }

    public function map($warehouse): array
    {
        return [
            $warehouse->id,
            $warehouse->code,
            $warehouse->name,
            $warehouse->address,
            $warehouse->phone,
            $warehouse->email,
            $warehouse->manager ? $warehouse->manager->name : '',
            $warehouse->is_default ? 'Yes' : 'No',
            $warehouse->is_active ? 'Yes' : 'No',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Name',
            'Address',
            'Phone',
            'Email',
            'Manager',
            'Default',
            'Active',
        ];
    }
}
