<?php

namespace Modules\Sales\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'do_number' => $this->do_number,
            'delivery_date' => $this->delivery_date?->toDateString(),
            'shipped_date' => $this->shipped_date?->toDateString(),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'sales_order' => $this->whenLoaded('salesOrder', fn() => [
                'id' => $this->salesOrder->id,
                'so_number' => $this->salesOrder->so_number,
            ]),
            'warehouse' => $this->whenLoaded('warehouse', fn() => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ]),
            'shipping' => [
                'address' => $this->shipping_address,
                'method' => $this->shipping_method,
                'tracking_number' => $this->tracking_number,
                'driver_name' => $this->driver_name,
                'vehicle_number' => $this->vehicle_number,
            ],
            'lines_count' => $this->lines_count ?? null,
            'lines' => DeliveryOrderLineResource::collection($this->whenLoaded('lines')),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
