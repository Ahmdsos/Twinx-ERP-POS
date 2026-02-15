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
        // Consolidated query: fetch both qty and sales in one DB call
        $salesData = SalesInvoiceLine::whereHas('invoice', function ($query) use ($startDate, $toDate) {
            $query->whereBetween('invoice_date', [$startDate, $toDate])
                ->whereIn('status', ['paid', 'partial', 'pending']);
        })
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(line_total) as total_sales')
            ->groupBy('product_id')
            ->get();

        $sales = $salesData->pluck('total_sales', 'product_id');
        $quantities = $salesData->pluck('total_qty', 'product_id');

        // Subtract Returns
        $returns = \Modules\Sales\Models\SalesReturnLine::whereHas('salesReturn', function ($query) use ($startDate, $toDate) {
            $query->whereBetween('return_date', [$startDate, $toDate])
                ->where('status', \Modules\Sales\Enums\SalesReturnStatus::APPROVED);
        })
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(line_total) as total_returns')
            ->groupBy('product_id')
            ->get();

        $data = \Modules\Inventory\Models\Product::whereIn('id', $quantities->keys()->merge($returns->pluck('product_id')))
            ->get()
            ->map(function ($product) use ($sales, $quantities, $returns) {
                $saleAmount = $sales[$product->id] ?? 0;
                $saleQty = $quantities[$product->id] ?? 0;

                $return = $returns->where('product_id', $product->id)->first();
                $returnAmount = $return ? $return->total_returns : 0;
                $returnQty = $return ? $return->total_qty : 0;

                return [
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'total_qty' => (float) ($saleQty - $returnQty),
                    'total_sales' => (float) ($saleAmount - $returnAmount),
                ];
            })
            ->filter(fn($item) => $item['total_qty'] != 0 || $item['total_sales'] != 0)
            ->sortByDesc('total_sales')
            ->values()
            ->toArray();

        return $data;
    }

    /**
     * Get Sales Analysis by Customer
     */
    public function salesByCustomer(Carbon $startDate, Carbon $toDate): array
    {
        $invoices = SalesInvoice::whereBetween('invoice_date', [$startDate, $toDate])
            ->whereIn('status', ['paid', 'partial', 'pending'])
            ->with([
                'customer',
                'returns' => function ($q) use ($startDate, $toDate) {
                    $q->whereBetween('return_date', [$startDate, $toDate])
                        ->where('status', \Modules\Sales\Enums\SalesReturnStatus::APPROVED);
                }
            ])
            ->get()
            ->groupBy('customer_id');

        return $invoices->map(function ($customerInvoices, $customerId) {
            $customer = $customerInvoices->first()->customer;
            $totalSales = $customerInvoices->sum('total');
            $totalDue = $customerInvoices->sum('balance_due');

            // Subtract Returns for this customer in this period
            $totalReturns = 0;
            foreach ($customerInvoices as $invoice) {
                $totalReturns += $invoice->returns->sum('total_amount');
            }

            return [
                'customer_name' => $customer?->name ?? 'Guest',
                'phone' => $customer?->phone ?? 'N/A',
                'invoice_count' => $customerInvoices->count(),
                'total_sales' => (float) ($totalSales - $totalReturns),
                'total_due' => (float) $totalDue,
            ];
        })
            ->sortByDesc('total_sales')
            ->values()
            ->toArray();
    }
}
