<?php

namespace Modules\Reporting\Services;

use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\SalesInvoiceLine;
use Carbon\Carbon;

class SalesReportService
{
    /**
     * Get Sales Analysis by Product
     */
    public function salesByProduct(Carbon $startDate, Carbon $toDate): array
    {
        return SalesInvoiceLine::whereHas('invoice', function ($query) use ($startDate, $toDate) {
            $query->whereBetween('invoice_date', [$startDate, $toDate])
                ->whereIn('status', ['paid', 'partial', 'pending']);
        })
            ->with('product')
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(line_total) as total_sales')
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->get()
            ->map(fn($line) => [
                'product_name' => $line->product?->name ?? 'Deleted Product',
                'sku' => $line->product?->sku ?? 'N/A',
                'total_qty' => (float) $line->total_qty,
                'total_sales' => (float) $line->total_sales,
            ])
            ->toArray();
    }

    /**
     * Get Sales Analysis by Customer
     */
    public function salesByCustomer(Carbon $startDate, Carbon $toDate): array
    {
        return SalesInvoice::whereBetween('invoice_date', [$startDate, $toDate])
            ->whereIn('status', ['paid', 'partial', 'pending'])
            ->with('customer')
            ->selectRaw('customer_id, COUNT(id) as invoice_count, SUM(total) as total_sales, SUM(balance_due) as total_due')
            ->groupBy('customer_id')
            ->orderByDesc('total_sales')
            ->get()
            ->map(fn($inv) => [
                'customer_name' => $inv->customer?->name ?? 'Guest',
                'phone' => $inv->customer?->phone ?? 'N/A',
                'invoice_count' => $inv->invoice_count,
                'total_sales' => (float) $inv->total_sales,
                'total_due' => (float) $inv->total_due,
            ])
            ->toArray();
    }
}
