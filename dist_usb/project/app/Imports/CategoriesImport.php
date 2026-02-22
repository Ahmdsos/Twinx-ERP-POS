<?php

namespace App\Imports;

use Modules\Inventory\Models\Category;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoriesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['name']) && empty($row['id'])) {
                continue;
            }

            $id = $row['id'] ?? null;
            $name = $row['name'] ?? '';

            $category = null;
            if ($id) {
                $category = Category::find($id);
            }

            if (!$category && $name) {
                $category = Category::where('name', $name)->first();
            }

            // Find parent if specified (by name for now, or could add parent_id)
            $parentId = null;
            if (!empty($row['parent_category'])) {
                $parent = Category::where('name', $row['parent_category'])->first();
                $parentId = $parent?->id;
            }

            $data = [
                'name' => $name ?: ($category->name ?? 'Unknown'),
                'slug' => $row['slug'] ?? ($category->slug ?? Str::slug($name)),
                'parent_id' => $parentId ?? ($category->parent_id ?? null),
                'description' => $row['description'] ?? ($category->description ?? null),
                'sort_order' => $row['sort_order'] ?? ($category->sort_order ?? 0),
                'is_active' => strtolower($row['active'] ?? 'yes') === 'yes',
            ];

            if ($category) {
                $category->update($data);
            } else {
                Category::create($data);
            }
        }
    }
}
