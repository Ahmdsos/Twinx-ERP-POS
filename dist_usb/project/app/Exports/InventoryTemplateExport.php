<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InventoryTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProductsTemplateSheet(),
            new CategoriesTemplateSheet(),
            new BrandsTemplateSheet(),
            new UnitsTemplateSheet(),
            new WarehousesTemplateSheet(),
        ];
    }
}
