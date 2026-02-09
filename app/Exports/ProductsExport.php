<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductsExport implements WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new ProductsSheet(),
            new CategoriesExport(),
            new BrandsExport(),
            new UnitsExport(),
            new WarehousesExport(),
        ];
    }
}
