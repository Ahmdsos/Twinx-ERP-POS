<?php

namespace Modules\Reporting\Services;

use Illuminate\Support\Facades\DB;
use Modules\Purchasing\Models\PurchaseInvoice;
use Carbon\Carbon;

class PurchaseReportService
{
    /**
     * Get Purchase Analysis by Supplier
     */
    public function purchasesBySupplier(Carbon $startDate, Carbon $toDate): array
    {
        return PurchaseInvoice::whereBetween('invoice_date', [$startDate, $toDate])
            ->whereIn('status', ['paid', 'partial', 'pending'])
            ->with('supplier')
            ->selectRaw('supplier_id, COUNT(id) as invoice_count, SUM(total) as total_purchases, SUM(balance_due) as total_due')
            ->groupBy('supplier_id')
            ->orderByDesc('total_purchases')
            ->get()
            ->map(fn($inv) => [
                'supplier_name' => $inv->supplier?->name ?? 'Unknown',
                'invoice_count' => $inv->invoice_count,
                'total_purchases' => (float) $inv->total_purchases,
                'total_due' => (float) $inv->total_due,
            ])
            ->toArray();
    }
}
