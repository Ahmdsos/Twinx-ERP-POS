<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductsImport implements WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            // Order is crucial for dependencies
            'Categories' => new CategoriesImport(),
            'Brands' => new BrandsImport(),
            'Units' => new UnitsImport(),
            'Warehouses' => new WarehousesImport(),
            'Products' => new ProductsSheetImport(),
        ];
    }
}
