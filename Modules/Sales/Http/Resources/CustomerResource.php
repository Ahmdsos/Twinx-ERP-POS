<?php

namespace Modules\Sales\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'billing_address' => $this->billing_address,
            'billing_city' => $this->billing_city,
            'billing_country' => $this->billing_country,
            'shipping_address' => $this->shipping_address,
            'shipping_city' => $this->shipping_city,
            'tax_number' => $this->tax_number,
            'payment_terms' => $this->payment_terms,
            'credit_limit' => (float) $this->credit_limit,
            'contact_person' => $this->contact_person,
            'is_active' => $this->is_active,
            'sales_rep' => $this->whenLoaded('salesRep', fn() => [
                'id' => $this->salesRep->id,
                'name' => $this->salesRep->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
