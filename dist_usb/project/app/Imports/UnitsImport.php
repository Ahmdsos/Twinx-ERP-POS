<?php

namespace App\Imports;

use Modules\Inventory\Models\Unit;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class UnitsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['name']) && empty($row['id'])) {
                continue;
            }

            $id = $row['id'] ?? null;
            $name = $row['name'] ?? '';

            $unit = null;
            if ($id) {
                $unit = Unit::find($id);
            }

            if (!$unit && $name) {
                $unit = Unit::where('name', $name)->first();
            }

            $data = [
                'name' => $name ?: ($unit->name ?? 'Unknown'),
                'abbreviation' => $row['abbreviation'] ?? ($unit->abbreviation ?? ''),
                'is_base' => strtolower($row['is_base'] ?? 'no') === 'yes',
                'conversion_factor' => $row['conversion_factor'] ?? ($unit->conversion_factor ?? 1),
                'is_active' => strtolower($row['active'] ?? 'yes') === 'yes',
            ];

            // Handle Base Unit by Name or ID if needed, but for now name is fine for UI
            if (!empty($row['base_unit'])) {
                $base = Unit::where('name', $row['base_unit'])->first();
                if ($base) {
                    $data['base_unit_id'] = $base->id;
                }
            }

            if ($unit) {
                $unit->update($data);
            } else {
                Unit::create($data);
            }
        }
    }
}
