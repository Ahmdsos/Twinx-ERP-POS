<?php

namespace App\Imports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class BrandsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['name']) && empty($row['id'])) {
                continue;
            }

            $id = $row['id'] ?? null;
            $name = $row['name'] ?? '';

            $brand = null;
            if ($id) {
                $brand = Brand::find($id);
            }

            if (!$brand && $name) {
                $brand = Brand::where('name', $name)->first();
            }

            $data = [
                'name' => $name ?: ($brand->name ?? 'Unknown'),
                'description' => $row['description'] ?? ($brand->description ?? null),
                'website' => $row['website'] ?? ($brand->website ?? null),
                'is_active' => strtolower($row['active'] ?? 'yes') === 'yes',
            ];

            if ($brand) {
                $brand->update($data);
            } else {
                Brand::create($data);
            }
        }
    }
}
