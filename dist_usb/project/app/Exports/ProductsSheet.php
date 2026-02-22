<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Inventory\Models\Product;

class ProductsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $warehouses;

    public function __construct()
    {
        $this->warehouses = \Modules\Inventory\Models\Warehouse::where('is_active', true)->get();
    }

    public function collection()
    {
        return Product::with([
            'category',
            'brand',
            'unit',
            'purchaseUnit',
            'stock',
            'salesAccount',
            'purchaseAccount',
            'inventoryAccount'
        ])->get();
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
        $row = [
            $product->id,
            $product->sku,
            $product->barcode,
            $product->name,
            $product->description,
            $product->type->value ?? $product->type,

            // Associations
            $product->category_id,
            $product->brand_id,
            $product->unit_id,
            $product->purchase_unit_id,

            // Pricing
            $product->cost_price,
            $product->selling_price,
            $product->min_selling_price,
            $product->is_active ? 'Yes' : 'No',
            $product->is_sellable ? 'Yes' : 'No',
            $product->is_purchasable ? 'Yes' : 'No',

            // Stock
            $product->reorder_level,
            $product->reorder_quantity,
            $product->min_stock,
            $product->max_stock,

            // Dimensions
            $product->weight,
            $product->weight_unit,
            $product->length,
            $product->width,
            $product->height,
            $product->dimension_unit,

            // Logistics
            $product->manufacturer,
            $product->manufacturer_part_number,
            $product->country_of_origin,
            $product->hs_code,
            $product->lead_time_days,
            $product->is_returnable ? 'Yes' : 'No',

            // Attributes
            $product->color,
            $product->size,
            is_array($product->tags) ? implode(',', $product->tags) : $product->tags,

            // Tiers
            $product->price_distributor,
            $product->price_wholesale,
            $product->price_half_wholesale,
            $product->price_quarter_wholesale,
            $product->price_special,

            // Warranty
            $product->warranty_months,
            $product->warranty_type,
            $product->expiry_date ? $product->expiry_date->format('Y-m-d') : null,
            $product->shelf_life_days,
            $product->track_batches ? 'Yes' : 'No',
            $product->track_serials ? 'Yes' : 'No',

            // SEO
            $product->seo_title,
            $product->seo_description,

            // Accounting
            $product->salesAccount?->code,
            $product->purchaseAccount?->code,
            $product->inventoryAccount?->code,
        ];

        foreach ($this->warehouses as $warehouse) {
            $stock = $product->stock->where('warehouse_id', $warehouse->id)->sum('quantity');
            $row[] = $stock;
        }

        return $row;
    }

    public function title(): string
    {
        return 'Products';
    }
}
