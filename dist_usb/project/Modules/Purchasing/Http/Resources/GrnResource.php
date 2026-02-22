<?php

namespace Modules\Purchasing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grn_number' => $this->grn_number,
            'received_date' => $this->received_date->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),

            'supplier' => $this->whenLoaded('supplier', fn() => [
                'id' => $this->supplier->id,
                'code' => $this->supplier->code,
                'name' => $this->supplier->name,
            ]),

            'warehouse' => $this->whenLoaded('warehouse', fn() => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ]),

            'purchase_order' => $this->whenLoaded('purchaseOrder', fn() => [
                'id' => $this->purchaseOrder->id,
                'po_number' => $this->purchaseOrder->po_number,
            ]),

            'supplier_delivery_note' => $this->supplier_delivery_note,
            'notes' => $this->notes,
            'total_value' => $this->getTotalValue(),

            'receiver' => $this->whenLoaded('receiver', fn() => $this->receiver?->name),

            'lines' => GrnLineResource::collection($this->whenLoaded('lines')),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
