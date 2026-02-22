<?php

namespace Modules\Sales\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'so_number' => $this->so_number,
            'order_date' => $this->order_date?->toDateString(),
            'expected_date' => $this->expected_date?->toDateString(),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer->id,
                'code' => $this->customer->code,
                'name' => $this->customer->name,
            ]),
            'warehouse' => $this->whenLoaded('warehouse', fn() => [
                'id' => $this->warehouse?->id,
                'name' => $this->warehouse?->name,
            ]),
            'amounts' => [
                'subtotal' => (float) $this->subtotal,
                'discount' => (float) $this->discount_amount,
                'tax' => (float) $this->tax_amount,
                'total' => (float) $this->total,
            ],
            'currency' => $this->currency,
            'reference' => $this->reference,
            'shipping_address' => $this->shipping_address,
            'shipping_method' => $this->shipping_method,
            'notes' => $this->notes,
            'lines_count' => $this->lines_count ?? null,
            'lines' => SalesOrderLineResource::collection($this->whenLoaded('lines')),
            'delivery_orders' => DeliveryOrderResource::collection($this->whenLoaded('deliveryOrders')),
            'can_edit' => $this->canEdit(),
            'can_deliver' => $this->canDeliver(),
            'can_invoice' => $this->canInvoice(),
            'can_cancel' => $this->canCancel(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
