<?php

namespace Modules\Sales\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesInvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer->id,
                'code' => $this->customer->code,
                'name' => $this->customer->name,
            ]),
            'sales_order' => $this->whenLoaded('salesOrder', fn() => [
                'id' => $this->salesOrder?->id,
                'so_number' => $this->salesOrder?->so_number,
            ]),
            'delivery_order' => $this->whenLoaded('deliveryOrder', fn() => [
                'id' => $this->deliveryOrder?->id,
                'do_number' => $this->deliveryOrder?->do_number,
            ]),
            'amounts' => [
                'subtotal' => (float) $this->subtotal,
                'discount' => (float) $this->discount_amount,
                'tax' => (float) $this->tax_amount,
                'total' => (float) $this->total,
            ],
            'payment' => [
                'paid_amount' => (float) $this->paid_amount,
                'balance_due' => (float) $this->balance_due,
            ],
            'currency' => $this->currency,
            'lines_count' => $this->lines_count ?? null,
            'lines' => SalesInvoiceLineResource::collection($this->whenLoaded('lines')),
            'notes' => $this->notes,
            'terms' => $this->terms,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
