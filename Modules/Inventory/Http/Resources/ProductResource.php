<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'tracks_inventory' => $this->tracksInventory(),

            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'path' => $this->category->path,
            ]),

            'unit' => $this->whenLoaded('unit', fn() => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'abbreviation' => $this->unit->abbreviation,
            ]),

            'purchase_unit' => $this->whenLoaded('purchaseUnit', fn() => $this->purchaseUnit ? [
                'id' => $this->purchaseUnit->id,
                'name' => $this->purchaseUnit->name,
            ] : null),

            'pricing' => [
                'cost_price' => (float) $this->cost_price,
                'selling_price' => (float) $this->selling_price,
                'min_selling_price' => $this->min_selling_price ? (float) $this->min_selling_price : null,
                'tax_rate' => (float) $this->tax_rate,
                'is_tax_inclusive' => $this->is_tax_inclusive,
            ],

            'inventory' => [
                'reorder_level' => $this->reorder_level,
                'reorder_quantity' => $this->reorder_quantity,
                'min_stock' => $this->min_stock,
                'max_stock' => $this->max_stock,
                'total_stock' => $this->when($this->relationLoaded('stock'), fn() => (float) $this->total_stock),
                'is_low_stock' => $this->when($this->relationLoaded('stock'), fn() => $this->isLowStock()),
            ],

            'stock' => $this->when(
                $this->relationLoaded('stock'),
                fn() =>
                $this->stock->map(fn($s) => [
                    'warehouse_id' => $s->warehouse_id,
                    'warehouse_name' => $s->warehouse?->name,
                    'quantity' => (float) $s->quantity,
                    'available' => (float) $s->available_quantity,
                    'average_cost' => (float) $s->average_cost,
                ])
            ),

            'status' => [
                'is_active' => $this->is_active,
                'is_sellable' => $this->is_sellable,
                'is_purchasable' => $this->is_purchasable,
            ],

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
