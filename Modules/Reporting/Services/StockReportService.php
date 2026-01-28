<?php

namespace Modules\Reporting\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Warehouse;

/**
 * StockReportService - Generates inventory reports
 */
class StockReportService
{
    /**
     * Get Stock Valuation Report
     */
    public function stockValuation(?int $warehouseId = null): array
    {
        $query = ProductStock::query()
            ->with(['product', 'warehouse'])
            ->where('quantity', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $stocks = $query->get();

        $items = [];
        $totalQty = 0;
        $totalValue = 0;

        foreach ($stocks as $stock) {
            $value = $stock->quantity * $stock->average_cost;

            $items[] = [
                'product_id' => $stock->product_id,
                'sku' => $stock->product->sku,
                'product_name' => $stock->product->name,
                'warehouse_id' => $stock->warehouse_id,
                'warehouse_name' => $stock->warehouse->name,
                'quantity' => (float) $stock->quantity,
                'average_cost' => round($stock->average_cost, 4),
                'total_value' => round($value, 2),
            ];

            $totalQty += $stock->quantity;
            $totalValue += $value;
        }

        return [
            'report_type' => 'Stock Valuation',
            'as_of_date' => now()->toDateString(),
            'generated_at' => now()->toIso8601String(),
            'warehouse_filter' => $warehouseId,
            'items' => $items,
            'summary' => [
                'total_items' => count($items),
                'total_quantity' => round($totalQty, 4),
                'total_value' => round($totalValue, 2),
            ],
        ];
    }

    /**
     * Get Low Stock Alert Report
     */
    public function lowStock(?int $warehouseId = null): array
    {
        $query = Product::query()
            ->with(['stocks.warehouse'])
            ->where('is_active', true)
            ->where('reorder_level', '>', 0);

        $products = $query->get();
        $alerts = [];

        foreach ($products as $product) {
            $stocks = $warehouseId
                ? $product->stocks->where('warehouse_id', $warehouseId)
                : $product->stocks;

            foreach ($stocks as $stock) {
                if ($stock->quantity < $product->reorder_level) {
                    $alerts[] = [
                        'product_id' => $product->id,
                        'sku' => $product->sku,
                        'product_name' => $product->name,
                        'warehouse_id' => $stock->warehouse_id,
                        'warehouse_name' => $stock->warehouse->name,
                        'current_stock' => (float) $stock->quantity,
                        'min_level' => (float) $product->reorder_level,
                        'reorder_qty' => (float) $product->reorder_quantity,
                        'shortage' => round($product->reorder_level - $stock->quantity, 4),
                    ];
                }
            }
        }

        // Sort by shortage descending
        usort($alerts, fn($a, $b) => $b['shortage'] <=> $a['shortage']);

        return [
            'report_type' => 'Low Stock Alert',
            'as_of_date' => now()->toDateString(),
            'generated_at' => now()->toIso8601String(),
            'items' => $alerts,
            'summary' => [
                'total_alerts' => count($alerts),
            ],
        ];
    }

    /**
     * Get Stock Movement History
     */
    public function movementHistory(
        ?int $productId = null,
        ?int $warehouseId = null,
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null,
        int $limit = 100
    ): array {
        $query = StockMovement::query()
            ->with(['product', 'warehouse'])
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->limit($limit);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($fromDate) {
            $query->whereDate('movement_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('movement_date', '<=', $toDate);
        }

        $movements = $query->get();

        $items = $movements->map(fn($m) => [
            'id' => $m->id,
            'movement_number' => $m->movement_number,
            'movement_date' => $m->movement_date->toDateString(),
            'type' => $m->type->value,
            'product_id' => $m->product_id,
            'sku' => $m->product->sku,
            'product_name' => $m->product->name,
            'warehouse_id' => $m->warehouse_id,
            'warehouse_name' => $m->warehouse->name,
            'quantity' => (float) $m->quantity,
            'unit_cost' => round($m->unit_cost, 4),
            'total_cost' => round($m->total_cost, 2),
            'reference' => $m->reference,
        ])->toArray();

        return [
            'report_type' => 'Stock Movement History',
            'generated_at' => now()->toIso8601String(),
            'filters' => [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'from_date' => $fromDate?->toDateString(),
                'to_date' => $toDate?->toDateString(),
            ],
            'items' => $items,
            'count' => count($items),
        ];
    }

    /**
     * Get Stock Summary by Warehouse
     */
    public function stockByWarehouse(): array
    {
        $warehouses = Warehouse::with('stocks.product')->get();

        $summary = [];

        foreach ($warehouses as $warehouse) {
            $totalQty = $warehouse->stocks->sum('quantity');
            $totalValue = $warehouse->stocks->sum(fn($s) => $s->quantity * $s->average_cost);
            $productCount = $warehouse->stocks->where('quantity', '>', 0)->count();

            $summary[] = [
                'warehouse_id' => $warehouse->id,
                'warehouse_code' => $warehouse->code,
                'warehouse_name' => $warehouse->name,
                'product_count' => $productCount,
                'total_quantity' => round($totalQty, 4),
                'total_value' => round($totalValue, 2),
            ];
        }

        return [
            'report_type' => 'Stock by Warehouse',
            'as_of_date' => now()->toDateString(),
            'generated_at' => now()->toIso8601String(),
            'warehouses' => $summary,
            'totals' => [
                'total_warehouses' => count($summary),
                'total_value' => round(array_sum(array_column($summary, 'total_value')), 2),
            ],
        ];
    }
}
