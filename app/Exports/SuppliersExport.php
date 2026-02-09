<?php

namespace App\Exports;

use Modules\Purchasing\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuppliersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Supplier::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Name',
            'Email',
            'Phone',
            'Mobile',
            'Fax',
            'Address',
            'City',
            'Country',
            'Postal Code',
            'Tax Number',
            'Commercial Register',
            'Payment Terms (Days)',
            'Credit Limit',
            'Contact Person',
            'Notes',
            'Is Active',
            'Total Purchases',
            'Outstanding Balance',
        ];
    }

    public function map($supplier): array
    {
        return [
            $supplier->id,
            $supplier->code,
            $supplier->name,
            $supplier->email,
            $supplier->phone,
            $supplier->mobile,
            $supplier->fax,
            $supplier->address,
            $supplier->city,
            $supplier->country,
            $supplier->postal_code,
            $supplier->tax_number,
            $supplier->commercial_register,
            $supplier->payment_terms,
            $supplier->credit_limit,
            $supplier->contact_person,
            $supplier->notes,
            $supplier->is_active ? 'Yes' : 'No',
            $supplier->getTotalPurchases(),
            $supplier->getOutstandingBalance(),
        ];
    }
}
