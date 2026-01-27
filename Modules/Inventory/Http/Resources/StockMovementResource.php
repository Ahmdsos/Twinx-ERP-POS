<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movement_number' => $this->movement_number,
            'movement_date' => $this->movement_date->toDateString(),
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'is_inward' => $this->isInward(),

            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->id,
                'sku' => $this->product->sku,
                'name' => $this->product->name,
            ]),

            'warehouse' => $this->whenLoaded('warehouse', fn() => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ]),

            'to_warehouse' => $this->whenLoaded('toWarehouse', fn() => $this->toWarehouse ? [
                'id' => $this->toWarehouse->id,
                'name' => $this->toWarehouse->name,
            ] : null),

            'quantity' => (float) $this->quantity,
            'unit_cost' => (float) $this->unit_cost,
            'total_cost' => (float) $this->total_cost,
            'remaining_quantity' => (float) $this->remaining_quantity,

            'reference' => $this->reference,
            'notes' => $this->notes,

            'created_by' => $this->whenLoaded('creator', fn() => $this->creator?->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
