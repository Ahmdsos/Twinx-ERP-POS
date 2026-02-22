<?php

namespace Modules\Sales\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryOrderLineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->id,
                'sku' => $this->product->sku,
                'name' => $this->product->name,
            ]),
            'quantity' => (float) $this->quantity,
            'unit_cost' => (float) $this->unit_cost,
            'line_cost' => (float) $this->line_cost,
            'notes' => $this->notes,
        ];
    }
}
