<?php

namespace Modules\Purchasing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'supplier_invoice_number' => $this->supplier_invoice_number,
            'invoice_date' => $this->invoice_date->toDateString(),
            'due_date' => $this->due_date->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),

            'supplier' => $this->whenLoaded('supplier', fn() => [
                'id' => $this->supplier->id,
                'code' => $this->supplier->code,
                'name' => $this->supplier->name,
            ]),

            'amounts' => [
                'subtotal' => (float) $this->subtotal,
                'discount' => (float) $this->discount_amount,
                'tax' => (float) $this->tax_amount,
                'total' => (float) $this->total,
                'paid' => (float) $this->paid_amount,
                'balance' => (float) $this->balance_due,
            ],

            'is_overdue' => $this->isOverdue(),
            'days_overdue' => $this->getDaysOverdue(),

            'lines' => $this->whenLoaded('lines', fn() => $this->lines->map(fn($l) => [
                'id' => $l->id,
                'product_id' => $l->product_id,
                'description' => $l->description,
                'quantity' => (float) $l->quantity,
                'unit_price' => (float) $l->unit_price,
                'line_total' => (float) $l->line_total,
            ])),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
