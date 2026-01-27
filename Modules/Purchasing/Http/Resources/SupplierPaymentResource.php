<?php

namespace Modules\Purchasing\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'payment_date' => $this->payment_date->toDateString(),
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,

            'supplier' => $this->whenLoaded('supplier', fn() => [
                'id' => $this->supplier->id,
                'code' => $this->supplier->code,
                'name' => $this->supplier->name,
            ]),

            'payment_account' => $this->whenLoaded('paymentAccount', fn() => [
                'id' => $this->paymentAccount->id,
                'code' => $this->paymentAccount->code,
                'name' => $this->paymentAccount->name,
            ]),

            'reference' => $this->reference,
            'notes' => $this->notes,

            'allocated_amount' => $this->getAllocatedAmount(),
            'unallocated_amount' => $this->getUnallocatedAmount(),

            'allocations' => $this->whenLoaded('allocations', fn() => $this->allocations->map(fn($a) => [
                'invoice_id' => $a->purchase_invoice_id,
                'invoice_number' => $a->invoice?->invoice_number,
                'amount' => (float) $a->amount,
            ])),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
