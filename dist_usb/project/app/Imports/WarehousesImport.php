<?php

namespace App\Imports;

use Modules\Inventory\Models\Warehouse;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class WarehousesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['name']) && empty($row['id']) && empty($row['code'])) {
                continue;
            }

            $id = $row['id'] ?? null;
            $name = $row['name'] ?? '';
            $code = $row['code'] ?? '';

            $warehouse = null;
            if ($id) {
                $warehouse = Warehouse::find($id);
            }

            if (!$warehouse && $code) {
                $warehouse = Warehouse::where('code', $code)->first();
            }

            if (!$warehouse && $name) {
                $warehouse = Warehouse::where('name', $name)->first();
            }

            $data = [
                'code' => $code ?: ($warehouse->code ?? \Illuminate\Support\Str::slug($name)),
                'name' => $name ?: ($warehouse->name ?? 'Unknown'),
                'address' => $row['address'] ?? ($warehouse->address ?? null),
                'phone' => $row['phone'] ?? ($warehouse->phone ?? null),
                'email' => $row['email'] ?? ($warehouse->email ?? null),
                'is_default' => strtolower($row['default'] ?? 'no') === 'yes',
                'is_active' => strtolower($row['active'] ?? 'yes') === 'yes',
            ];

            if ($warehouse) {
                $warehouse->update($data);
            } else {
                Warehouse::create($data);
            }
        }
    }
}
