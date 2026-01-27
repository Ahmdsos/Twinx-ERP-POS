<?php

namespace Modules\Sales\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerPaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'receipt_number' => $this->receipt_number,
            'payment_date' => $this->payment_date?->toDateString(),
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer->id,
                'code' => $this->customer->code,
                'name' => $this->customer->name,
            ]),
            'payment_account' => $this->whenLoaded('paymentAccount', fn() => [
                'id' => $this->paymentAccount->id,
                'code' => $this->paymentAccount->code,
                'name' => $this->paymentAccount->name,
            ]),
            'reference' => $this->reference,
            'notes' => $this->notes,
            'allocations' => $this->whenLoaded(
                'allocations',
                fn() =>
                $this->allocations->map(fn($a) => [
                    'invoice_id' => $a->sales_invoice_id,
                    'invoice_number' => $a->invoice?->invoice_number,
                    'amount' => (float) $a->amount,
                ])
            ),
            'allocated_amount' => $this->getAllocatedAmount(),
            'unallocated_amount' => $this->getUnallocatedAmount(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
