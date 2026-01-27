<?php

namespace Modules\Purchasing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'po_number' => $this->po_number,
            'order_date' => $this->order_date->toDateString(),
            'expected_date' => $this->expected_date?->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),

            'supplier' => $this->whenLoaded('supplier', fn() => [
                'id' => $this->supplier->id,
                'code' => $this->supplier->code,
                'name' => $this->supplier->name,
            ]),

            'warehouse' => $this->whenLoaded('warehouse', fn() => $this->warehouse ? [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ] : null),

            'amounts' => [
                'subtotal' => (float) $this->subtotal,
                'discount' => (float) $this->discount_amount,
                'tax' => (float) $this->tax_amount,
                'total' => (float) $this->total,
            ],

            'received_percentage' => $this->getReceivedPercentage(),

            'reference' => $this->reference,
            'notes' => $this->notes,

            'approver' => $this->whenLoaded('approver', fn() => $this->approver?->name),
            'approved_at' => $this->approved_at?->toIso8601String(),

            'lines' => PurchaseOrderLineResource::collection($this->whenLoaded('lines')),

            'can_edit' => $this->canEdit(),
            'can_receive' => $this->canReceive(),
            'can_cancel' => $this->canCancel(),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
