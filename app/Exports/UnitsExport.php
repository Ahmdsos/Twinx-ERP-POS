<?php

namespace App\Exports;

use Modules\Inventory\Models\Unit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class UnitsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function collection()
    {
        return Unit::with('baseUnit')->get();
    }

    public function map($unit): array
    {
        return [
            $unit->id,
            $unit->name,
            $unit->abbreviation,
            $unit->is_base ? 'Yes' : 'No',
            $unit->baseUnit ? $unit->baseUnit->name : '',
            $unit->conversion_factor,
            $unit->is_active ? 'Yes' : 'No',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Abbreviation',
            'Is Base',
            'Base Unit',
            'Conversion Factor',
            'Active',
        ];
    }

    public function title(): string
    {
        return 'Units';
    }
}
