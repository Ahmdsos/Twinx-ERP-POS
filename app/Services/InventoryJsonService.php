<?php

namespace App\Services;

use Modules\Inventory\Models\Category;
use App\Models\Brand;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Enums\MovementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InventoryJsonService
{
    /**
     * Export all inventory data to an array
     */
    public function getData(): array
    {
        return [
            'system_info' => [
                'exported_at' => now()->toDateTimeString(),
                'version' => '1.2.0',
                'description' => 'Twinx ERP Unified Inventory Export'
            ],
            'categories' => Category::all()->map(fn($item) => $item->toArray())->toArray(),
            'brands' => Brand::all()->map(fn($item) => $item->toArray())->toArray(),
            'units' => Unit::all()->map(fn($item) => $item->toArray())->toArray(),
            'warehouses' => Warehouse::all()->map(fn($item) => $item->toArray())->toArray(),
            'products' => Product::with(['stock', 'images', 'category', 'brand', 'unit'])->get()->map(function ($item) {
                // Map stocks for easier editing
                $stocks = [];
                foreach ($item->stock as $s) {
                    $stocks[$s->warehouse_id] = $s->quantity;
                }

                // Map images
                $images = $item->images->map(fn($img) => [
                    'path' => $img->path,
                    'is_primary' => (bool) $img->is_primary,
                    'sort_order' => $img->sort_order
                ])->toArray();

                $data = $item->toArray();

                // Add descriptive fields for readability
                $data['descriptive_info'] = [
                    'category_name' => $item->category?->name,
                    'brand_name' => $item->brand?->name,
                    'unit_name' => $item->unit?->name,
                ];

                $data['stocks'] = $stocks;
                $data['images_list'] = $images;
                return $data;
            })->toArray(),
        ];
    }

    /**
     * Recursive function to clean data (convert 'nan' strings to null)
     */
    private function sanitize(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value) && strtolower($value) === 'nan') {
                $value = null;
            }
        });
        return $data;
    }

    /**
     * Import inventory data from an array/JSON
     */
    public function importData(array $data): void
    {
        // Sanitize Data (Remove 'nan' values)
        $data = $this->sanitize($data);

        DB::transaction(function () use ($data) {
            // Allow mass assignment of 'id'
            \Illuminate\Database\Eloquent\Model::unguard();

            // Helper to clean data (keep only relevant columns)
            $cleaner = function ($item, $exclude = []) {
                return collect($item)->except(array_merge([
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'stock_qty',
                    'total_stock',
                    'available_stock',
                    'primary_image_url', // Appended attributes
                    'descriptive_info', // Exclude the intuitive help labels
                    'system_info' // Exclude system info
                ], $exclude))->toArray();
            };

            // Import Dependencies
            foreach ($data['categories'] ?? [] as $item) {
                Category::updateOrCreate(['id' => $item['id']], $cleaner($item, ['parent', 'children']));
            }

            foreach ($data['brands'] ?? [] as $item) {
                Brand::updateOrCreate(['id' => $item['id']], $cleaner($item, []));
            }

            foreach ($data['units'] ?? [] as $item) {
                Unit::updateOrCreate(['id' => $item['id']], $cleaner($item, []));
            }

            foreach ($data['warehouses'] ?? [] as $item) {
                Warehouse::updateOrCreate(['id' => $item['id']], $cleaner($item, []));
            }

            // Import Products
            $warehouses = Warehouse::all();
            $inventoryService = app(\Modules\Inventory\Services\InventoryService::class);

            foreach ($data['products'] ?? [] as $item) {
                $stocks = $item['stocks'] ?? [];
                $imageList = $item['images_list'] ?? [];

                // Deep cleaning for Products
                $cleanProduct = $cleaner($item, [
                    'stocks',
                    'images_list',
                    'stock',
                    'images',
                    'category',
                    'brand',
                    'unit',
                    'purchase_unit',
                    'sales_account',
                    'purchase_account',
                    'inventory_account'
                ]);

                // Defend against corruption (NaN -> null -> 0)
                $cleanProduct['cost_price'] = $cleanProduct['cost_price'] ?? 0;
                $cleanProduct['selling_price'] = $cleanProduct['selling_price'] ?? 0;
                $cleanProduct['name'] = $cleanProduct['name'] ?? 'Unknown Product ' . ($item['id'] ?? uniqid());
                $cleanProduct['warranty_months'] = $cleanProduct['warranty_months'] ?? 0;

                // Debugging Import
                if (is_null($cleanProduct['warranty_months'])) {
                    \Illuminate\Support\Facades\Log::error("Warranty Months is NULL for ID " . ($item['id'] ?? 'unknown'));
                    $cleanProduct['warranty_months'] = 0; // Force 0 again
                }

                $product = Product::updateOrCreate(['id' => $item['id'] ?? null], $cleanProduct);

                // Update Stocks (Target Mode)
                foreach ($stocks as $warehouseId => $targetQty) {
                    $warehouse = $warehouses->find($warehouseId);
                    if ($warehouse) {
                        $currentQty = $product->getStockInWarehouse($warehouseId)?->quantity ?? 0;
                        $diff = $targetQty - $currentQty;

                        if ($diff != 0) {
                            $inventoryService->addStock(
                                $product,
                                $warehouse,
                                abs($diff),
                                $product->cost_price ?? 0,
                                $diff > 0 ? MovementType::ADJUSTMENT_IN : MovementType::ADJUSTMENT_OUT,
                                null,
                                'JSON-UI Unified Adjustment'
                            );
                        }
                    }
                }

                // Update Images (Upsert by path)
                if (!empty($imageList)) {
                    foreach ($imageList as $imgData) {
                        \Modules\Inventory\Models\ProductImage::updateOrCreate([
                            'product_id' => $product->id,
                            'path' => $imgData['path']
                        ], [
                            'filename' => basename($imgData['path']),
                            'is_primary' => $imgData['is_primary'] ?? false,
                            'sort_order' => $imgData['sort_order'] ?? 0,
                            'disk' => 'public'
                        ]);
                    }
                }
            }
            \Illuminate\Database\Eloquent\Model::reguard();
        });
    }
}
