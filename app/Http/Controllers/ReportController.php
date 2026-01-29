<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesInvoice;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Models\PurchaseInvoice;
use Carbon\Carbon;

/**
 * ReportController - تقارير ملخصة
 */
class ReportController extends Controller
{
    /**
     * Customer Sales Summary Report
     * ملخص مبيعات العملاء
     */
    public function customerSalesSummary(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $customerId = $request->customer_id;

        // Build query
        $query = DB::table('sales_invoices')
            ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
            ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
            ->whereNull('sales_invoices.deleted_at')
            ->where('sales_invoices.status', '!=', 'cancelled');

        if ($customerId) {
            $query->where('sales_invoices.customer_id', $customerId);
        }

        // Get summary data
        $data = $query
            ->select([
                'customers.id as customer_id',
                'customers.code as customer_code',
                'customers.name as customer_name',
                DB::raw('COUNT(sales_invoices.id) as invoice_count'),
                DB::raw('SUM(sales_invoices.subtotal) as total_subtotal'),
                DB::raw('SUM(sales_invoices.tax_amount) as total_tax'),
                DB::raw('SUM(sales_invoices.discount_amount) as total_discount'),
                DB::raw('SUM(sales_invoices.total) as total_sales'),
                DB::raw('SUM(sales_invoices.amount_paid) as total_paid'),
                DB::raw('SUM(sales_invoices.balance_due) as total_due'),
            ])
            ->groupBy('customers.id', 'customers.code', 'customers.name')
            ->orderByDesc('total_sales')
            ->get();

        // Overall totals
        $totals = [
            'invoice_count' => $data->sum('invoice_count'),
            'total_subtotal' => $data->sum('total_subtotal'),
            'total_tax' => $data->sum('total_tax'),
            'total_discount' => $data->sum('total_discount'),
            'total_sales' => $data->sum('total_sales'),
            'total_paid' => $data->sum('total_paid'),
            'total_due' => $data->sum('total_due'),
        ];

        // Get all customers for filter
        $customers = Customer::active()->orderBy('name')->get(['id', 'code', 'name']);

        return view('reports.customer-sales', compact(
            'data',
            'totals',
            'customers',
            'startDate',
            'endDate',
            'customerId'
        ));
    }

    /**
     * Supplier Purchase Summary Report
     * ملخص مشتريات الموردين
     */
    public function supplierPurchaseSummary(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $supplierId = $request->supplier_id;

        // Build query
        $query = DB::table('purchase_invoices')
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->whereBetween('purchase_invoices.invoice_date', [$startDate, $endDate])
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoices.status', '!=', 'cancelled');

        if ($supplierId) {
            $query->where('purchase_invoices.supplier_id', $supplierId);
        }

        // Get summary data
        $data = $query
            ->select([
                'suppliers.id as supplier_id',
                'suppliers.code as supplier_code',
                'suppliers.name as supplier_name',
                DB::raw('COUNT(purchase_invoices.id) as invoice_count'),
                DB::raw('SUM(purchase_invoices.subtotal) as total_subtotal'),
                DB::raw('SUM(purchase_invoices.tax_amount) as total_tax'),
                DB::raw('SUM(purchase_invoices.total) as total_purchases'),
                DB::raw('SUM(purchase_invoices.amount_paid) as total_paid'),
                DB::raw('SUM(purchase_invoices.balance_due) as total_due'),
            ])
            ->groupBy('suppliers.id', 'suppliers.code', 'suppliers.name')
            ->orderByDesc('total_purchases')
            ->get();

        // Overall totals
        $totals = [
            'invoice_count' => $data->sum('invoice_count'),
            'total_subtotal' => $data->sum('total_subtotal'),
            'total_tax' => $data->sum('total_tax'),
            'total_purchases' => $data->sum('total_purchases'),
            'total_paid' => $data->sum('total_paid'),
            'total_due' => $data->sum('total_due'),
        ];

        // Get all suppliers for filter
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('reports.supplier-purchases', compact(
            'data',
            'totals',
            'suppliers',
            'startDate',
            'endDate',
            'supplierId'
        ));
    }
}
