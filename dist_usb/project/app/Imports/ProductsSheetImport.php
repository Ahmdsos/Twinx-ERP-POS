<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Enums\ProductType;
use Modules\Inventory\Enums\MovementType;
use Illuminate\Support\Str;

class ProductsSheetImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $warehouses = Warehouse::all();
        $inventoryService = app(\Modules\Inventory\Services\InventoryService::class);
        $accounts = class_exists(\Modules\Accounting\Models\Account::class)
            ? \Modules\Accounting\Models\Account::all()
            : collect();

        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row['sku']) && empty($row['id'])) {
                continue;
            }

            $id = $row['id'] ?? null;
            $sku = $row['sku'] ?? null;

            $product = null;
            if ($id) {
                $product = Product::find($id);
            }
            if (!$product && $sku) {
                $product = Product::where('sku', $sku)->first();
            }

            // Accounting lookup
            $salesAcc = $accounts->where('code', $row['sales_account_code'] ?? null)->first();
            $purchAcc = $accounts->where('code', $row['purchase_account_code'] ?? null)->first();
            $invAcc = $accounts->where('code', $row['inventory_account_code'] ?? null)->first();

            $data = [
                'sku' => $sku ?: ($product->sku ?? 'SKU-' . time()),
                'barcode' => $row['barcode'] ?? ($product->barcode ?? null),
                'name' => $row['name'] ?? ($product->name ?? 'Unnamed Product'),
                'description' => $row['description'] ?? ($product->description ?? null),
                'type' => ProductType::tryFrom($row['type'] ?? '') ?? ($product->type ?? ProductType::GOODS),

                'cost_price' => $row['cost_price'] ?? ($product->cost_price ?? 0),
                'selling_price' => $row['selling_price'] ?? ($product->selling_price ?? 0),
                'min_selling_price' => $row['min_selling_price'] ?? ($product->min_selling_price ?? 0),

                'category_id' => $row['category_id'] ?? ($product->category_id ?? null),
                'brand_id' => $row['brand_id'] ?? ($product->brand_id ?? null),
                'unit_id' => $row['unit_id'] ?? ($product->unit_id ?? null),
                'purchase_unit_id' => $row['purchase_unit_id'] ?? ($product->purchase_unit_id ?? null),

                'reorder_level' => $row['reorder_level'] ?? ($product->reorder_level ?? 0),
                'reorder_quantity' => $row['reorder_quantity'] ?? ($product->reorder_quantity ?? 0),
                'min_stock' => $row['min_stock'] ?? ($product->min_stock ?? 0),
                'max_stock' => $row['max_stock'] ?? ($product->max_stock ?? 0),

                'is_active' => strtolower($row['is_active'] ?? 'yes') === 'yes',
                'is_sellable' => strtolower($row['is_sellable'] ?? 'yes') === 'yes',
                'is_purchasable' => strtolower($row['is_purchasable'] ?? 'yes') === 'yes',

                'weight' => $row['weight'] ?? ($product->weight ?? 0),
                'weight_unit' => $row['weight_unit'] ?? ($product->weight_unit ?? 'kg'),
                'length' => $row['length'] ?? ($product->length ?? 0),
                'width' => $row['width'] ?? ($product->width ?? 0),
                'height' => $row['height'] ?? ($product->height ?? 0),
                'dimension_unit' => $row['dimension_unit'] ?? ($product->dimension_unit ?? 'cm'),

                'manufacturer' => $row['manufacturer'] ?? ($product->manufacturer ?? null),
                'manufacturer_part_number' => $row['manufacturer_part_number'] ?? ($product->manufacturer_part_number ?? null),
                'country_of_origin' => $row['country_of_origin'] ?? ($product->country_of_origin ?? null),
                'hs_code' => $row['hs_code'] ?? ($product->hs_code ?? null),
                'lead_time_days' => $row['lead_time_days'] ?? ($product->lead_time_days ?? 0),
                'is_returnable' => strtolower($row['is_returnable'] ?? 'yes') === 'yes',

                'color' => $row['color'] ?? ($product->color ?? null),
                'size' => $row['size'] ?? ($product->size ?? null),
                'tags' => !empty($row['tags']) ? (is_array($row['tags']) ? $row['tags'] : explode(',', $row['tags'])) : ($product->tags ?? null),

                'price_distributor' => $row['price_distributor'] ?? ($product->price_distributor ?? 0),
                'price_wholesale' => $row['price_wholesale'] ?? ($product->price_wholesale ?? 0),
                'price_half_wholesale' => $row['price_half_wholesale'] ?? ($product->price_half_wholesale ?? 0),
                'price_quarter_wholesale' => $row['price_quarter_wholesale'] ?? ($product->price_quarter_wholesale ?? 0),
                'price_special' => $row['price_special'] ?? ($product->price_special ?? 0),

                'warranty_months' => $row['warranty_months'] ?? ($product->warranty_months ?? 0),
                'warranty_type' => $row['warranty_type'] ?? ($product->warranty_type ?? null),
                'expiry_date' => $row['expiry_date'] ?? ($product->expiry_date ?? null),
                'shelf_life_days' => $row['shelf_life_days'] ?? ($product->shelf_life_days ?? 0),
                'track_batches' => strtolower($row['track_batches'] ?? 'no') === 'yes',
                'track_serials' => strtolower($row['track_serials'] ?? 'no') === 'yes',

                'seo_title' => $row['seo_title'] ?? ($product->seo_title ?? null),
                'seo_description' => $row['seo_description'] ?? ($product->seo_description ?? null),

                'sales_account_id' => $salesAcc?->id ?? ($product->sales_account_id ?? null),
                'purchase_account_id' => $purchAcc?->id ?? ($product->purchase_account_id ?? null),
                'inventory_account_id' => $invAcc?->id ?? ($product->inventory_account_id ?? null),
            ];

            if ($product) {
                $product->update($data);
            } else {
                $product = Product::create($data);
            }

            // TRUTH: Stock Handling
            // We iterate through all headers to find "stock_" patterns that contain warehouse IDs
            foreach ($row as $key => $targetQty) {
                if (str_starts_with($key, 'stock_') && is_numeric($targetQty)) {
                    // Try to extract ID from slug: "stock_main_id_1" -> 1
                    // The slug format from "Stock: Main (ID: 1)" is "stock_main_id_1"
                    if (preg_match('/_id_(\d+)$/', $key, $matches)) {
                        $whId = $matches[1];
                        $warehouse = $warehouses->find($whId);

                        if ($warehouse) {
                            $targetQty = (float) $targetQty;
                            $currentQty = $product->getStockInWarehouse($warehouse->id)?->quantity ?? 0;
                            $diff = $targetQty - $currentQty;

                            if ($diff != 0) {
                                // Use INITIAL for first-time stock (currentQty == 0), ADJUSTMENT for corrections
                                $type = ($currentQty == 0 && $diff > 0)
                                    ? MovementType::INITIAL
                                    : ($diff > 0 ? MovementType::ADJUSTMENT_IN : MovementType::ADJUSTMENT_OUT);

                                $inventoryService->addStock(
                                    $product,
                                    $warehouse,
                                    abs($diff),
                                    $product->cost_price ?? 0,
                                    $type,
                                    null,
                                    $currentQty == 0 ? 'Initial stock from import' : 'Super-Excel Unified Adjustment'
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
