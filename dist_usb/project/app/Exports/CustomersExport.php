<?php

namespace App\Exports;

use Modules\Sales\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Customer::with('salesRep')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Name',
            'Type',
            'Email',
            'Phone',
            'Mobile',
            'Billing Address',
            'Billing City',
            'Billing Country',
            'Billing Postal',
            'Shipping Address',
            'Shipping City',
            'Shipping Country',
            'Shipping Postal',
            'Tax Number',
            'Payment Terms (Days)',
            'Credit Limit',
            'Credit Grace Days',
            'Contact Person',
            'Notes',
            'Is Active',
            'Is Blocked',
            'Block Reason',
            'Total Sales',
            'Outstanding Balance',
            'Available Credit',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->code,
            $customer->name,
            $customer->type,
            $customer->email,
            $customer->phone,
            $customer->mobile,
            $customer->billing_address,
            $customer->billing_city,
            $customer->billing_country,
            $customer->billing_postal,
            $customer->shipping_address,
            $customer->shipping_city,
            $customer->shipping_country,
            $customer->shipping_postal,
            $customer->tax_number,
            $customer->payment_terms,
            $customer->credit_limit,
            $customer->credit_grace_days,
            $customer->contact_person,
            $customer->notes,
            $customer->is_active ? 'Yes' : 'No',
            $customer->is_blocked ? 'Yes' : 'No',
            $customer->block_reason,
            $customer->getTotalSales(),
            $customer->getOutstandingBalance(),
            $customer->getAvailableCredit(),
        ];
    }
}
