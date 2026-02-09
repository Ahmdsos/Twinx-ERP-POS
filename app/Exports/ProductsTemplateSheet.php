<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductsTemplateSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $warehouses;

    public function __construct()
    {
        $this->warehouses = \Modules\Inventory\Models\Warehouse::where('is_active', true)->get();
    }

    public function collection()
    {
        // Return an empty collection for a blank template
        return collect([]);
    }

    public function headings(): array
    {
        $headers = [
            'ID',
            'SKU',
            'Barcode',
            'Name',
            'Description',
            'Type',

            // Core Associations (IDs for Precision)
            'Category ID',
            'Brand ID',
            'Unit ID',
            'Purchase Unit ID',

            // Pricing & Core
            'Cost Price',
            'Selling Price',
            'Min Selling Price',
            'Is Active',
            'Is Sellable',
            'Is Purchasable',

            // Stock Control
            'Reorder Level',
            'Reorder Quantity',
            'Min Stock',
            'Max Stock',

            // Dimensions
            'Weight',
            'Weight Unit',
            'Length',
            'Width',
            'Height',
            'Dimension Unit',

            // Logistics
            'Manufacturer',
            'Manufacturer Part Number',
            'Country of Origin',
            'HS Code',
            'Lead Time Days',
            'Is Returnable',

            // Attributes
            'Color',
            'Size',
            'Tags',

            // Pricing Tiers
            'Price Distributor',
            'Price Wholesale',
            'Price Half Wholesale',
            'Price Quarter Wholesale',
            'Price Special',

            // Warranty
            'Warranty Months',
            'Warranty Type',
            'Expiry Date',
            'Shelf Life Days',
            'Track Batches',
            'Track Serials',

            // SEO
            'SEO Title',
            'SEO Description',

            // Accounting
            'Sales Account Code',
            'Purchase Account Code',
            'Inventory Account Code',
        ];

        // Dynamic Warehouse Stock Columns
        foreach ($this->warehouses as $warehouse) {
            $headers[] = "Stock: {$warehouse->name} (ID: {$warehouse->id})";
        }

        return $headers;
    }

    public function map($product): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Products';
    }
}
