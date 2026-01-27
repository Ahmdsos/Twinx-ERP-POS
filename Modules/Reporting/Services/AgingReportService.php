<?php

namespace Modules\Reporting\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\Customer;
use Modules\Purchasing\Models\PurchaseInvoice;
use Modules\Purchasing\Models\Supplier;

/**
 * AgingReportService - Generates AR/AP aging reports
 */
class AgingReportService
{
    // Standard aging buckets
    protected array $buckets = [
        'current' => [0, 0],
        '1_30' => [1, 30],
        '31_60' => [31, 60],
        '61_90' => [61, 90],
        'over_90' => [91, 9999],
    ];

    /**
     * Get AR Aging Report (Customer Receivables)
     */
    public function arAging(?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();

        $invoices = SalesInvoice::query()
            ->with('customer')
            ->whereIn('status', ['pending', 'partial'])
            ->where('balance_due', '>', 0)
            ->whereDate('invoice_date', '<=', $asOfDate)
            ->get();

        $customerAging = [];
        $totals = $this->initBucketTotals();

        foreach ($invoices as $invoice) {
            $daysOld = $invoice->due_date->diffInDays($asOfDate, false);
            $bucket = $this->getBucket($daysOld);
            $customerId = $invoice->customer_id;

            if (!isset($customerAging[$customerId])) {
                $customerAging[$customerId] = [
                    'customer_id' => $customerId,
                    'customer_code' => $invoice->customer->code,
                    'customer_name' => $invoice->customer->name,
                    'buckets' => $this->initBucketTotals(),
                    'total' => 0,
                ];
            }

            $customerAging[$customerId]['buckets'][$bucket] += $invoice->balance_due;
            $customerAging[$customerId]['total'] += $invoice->balance_due;
            $totals[$bucket] += $invoice->balance_due;
        }

        return [
            'report_type' => 'AR Aging',
            'as_of_date' => $asOfDate->toDateString(),
            'generated_at' => now()->toIso8601String(),
            'customers' => array_values($customerAging),
            'totals' => [
                'current' => round($totals['current'], 2),
                '1_30_days' => round($totals['1_30'], 2),
                '31_60_days' => round($totals['31_60'], 2),
                '61_90_days' => round($totals['61_90'], 2),
                'over_90_days' => round($totals['over_90'], 2),
                'grand_total' => round(array_sum($totals), 2),
            ],
            'summary' => [
                'total_customers' => count($customerAging),
                'total_outstanding' => round(array_sum($totals), 2),
                'overdue_amount' => round($totals['1_30'] + $totals['31_60'] + $totals['61_90'] + $totals['over_90'], 2),
            ],
        ];
    }

    /**
     * Get AP Aging Report (Supplier Payables)
     */
    public function apAging(?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();

        $invoices = PurchaseInvoice::query()
            ->with('supplier')
            ->whereIn('status', ['pending', 'partial'])
            ->where('balance_due', '>', 0)
            ->whereDate('invoice_date', '<=', $asOfDate)
            ->get();

        $supplierAging = [];
        $totals = $this->initBucketTotals();

        foreach ($invoices as $invoice) {
            $daysOld = $invoice->due_date->diffInDays($asOfDate, false);
            $bucket = $this->getBucket($daysOld);
            $supplierId = $invoice->supplier_id;

            if (!isset($supplierAging[$supplierId])) {
                $supplierAging[$supplierId] = [
                    'supplier_id' => $supplierId,
                    'supplier_code' => $invoice->supplier->code,
                    'supplier_name' => $invoice->supplier->name,
                    'buckets' => $this->initBucketTotals(),
                    'total' => 0,
                ];
            }

            $supplierAging[$supplierId]['buckets'][$bucket] += $invoice->balance_due;
            $supplierAging[$supplierId]['total'] += $invoice->balance_due;
            $totals[$bucket] += $invoice->balance_due;
        }

        return [
            'report_type' => 'AP Aging',
            'as_of_date' => $asOfDate->toDateString(),
            'generated_at' => now()->toIso8601String(),
            'suppliers' => array_values($supplierAging),
            'totals' => [
                'current' => round($totals['current'], 2),
                '1_30_days' => round($totals['1_30'], 2),
                '31_60_days' => round($totals['31_60'], 2),
                '61_90_days' => round($totals['61_90'], 2),
                'over_90_days' => round($totals['over_90'], 2),
                'grand_total' => round(array_sum($totals), 2),
            ],
            'summary' => [
                'total_suppliers' => count($supplierAging),
                'total_payable' => round(array_sum($totals), 2),
                'overdue_amount' => round($totals['1_30'] + $totals['31_60'] + $totals['61_90'] + $totals['over_90'], 2),
            ],
        ];
    }

    /**
     * Get aging bucket for days
     */
    protected function getBucket(int $daysOld): string
    {
        if ($daysOld <= 0)
            return 'current';
        if ($daysOld <= 30)
            return '1_30';
        if ($daysOld <= 60)
            return '31_60';
        if ($daysOld <= 90)
            return '61_90';
        return 'over_90';
    }

    /**
     * Initialize bucket totals
     */
    protected function initBucketTotals(): array
    {
        return [
            'current' => 0,
            '1_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            'over_90' => 0,
        ];
    }
}
