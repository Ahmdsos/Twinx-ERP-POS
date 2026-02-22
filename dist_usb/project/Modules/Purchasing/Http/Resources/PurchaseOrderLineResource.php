<?php

namespace Modules\Purchasing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->id,
                'sku' => $this->product->sku,
                'name' => $this->product->name,
            ]),
            'quantity' => (float) $this->quantity,
            'received_quantity' => (float) $this->received_quantity,
            'remaining_quantity' => (float) $this->getRemainingQuantity(),
            'unit_price' => (float) $this->unit_price,
            'discount_percent' => (float) $this->discount_percent,
            'tax_percent' => (float) $this->tax_percent,
            'line_total' => (float) $this->line_total,
            'is_fully_received' => $this->isFullyReceived(),
        ];
    }
}
