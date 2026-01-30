<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Sales\Models\Customer;
use Modules\Purchasing\Models\Supplier;

/**
 * ExportController - Handles Excel and PDF exports for all entities
 * 
 * Provides export functionality for Products, Customers, Suppliers
 * Supports both Excel (CSV) and PDF formats
 */
class ExportController extends Controller
{
    // ===========================
    // Products Export
    // ===========================

    /**
     * Export products to Excel (CSV)
     */
    public function productsExcel(Request $request)
    {
        $products = Product::with(['category', 'unit'])
            ->orderBy('name')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel Arabic support
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($file, [
                'الكود (SKU)',
                'الباركود',
                'اسم المنتج',
                'التصنيف',
                'الوحدة',
                'سعر التكلفة',
                'سعر البيع',
                'نسبة الضريبة',
                'الحد الأدنى',
                'العلامة التجارية',
                'الضمان (شهور)',
                'الوزن',
                'الحالة',
            ]);

            // Data rows
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->sku,
                    $product->barcode ?? '',
                    $product->name,
                    $product->category?->name ?? '',
                    $product->unit?->name ?? '',
                    $product->cost_price,
                    $product->selling_price,
                    $product->tax_rate . '%',
                    $product->min_stock,
                    $product->brand ?? '',
                    $product->warranty_months ?? 0,
                    $product->weight ? $product->weight . ' ' . $product->weight_unit : '',
                    $product->is_active ? 'نشط' : 'غير نشط',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export products to PDF
     */
    public function productsPdf(Request $request)
    {
        $products = Product::with(['category', 'unit'])
            ->orderBy('name')
            ->get();

        // Generate simple HTML for PDF (using browser print)
        $html = $this->generateProductsHtml($products);

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Generate HTML table for products (printable)
     */
    private function generateProductsHtml($products)
    {
        $html = '<!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>قائمة المنتجات - Twinx ERP</title>
            <style>
                body { font-family: "Cairo", Arial, sans-serif; direction: rtl; margin: 20px; }
                h1 { text-align: center; color: #333; }
                .meta { text-align: center; color: #666; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: right; font-size: 12px; }
                th { background-color: #4f46e5; color: white; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .number { text-align: center; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <h1>قائمة المنتجات</h1>
            <p class="meta">تاريخ التصدير: ' . date('Y-m-d H:i') . ' | عدد المنتجات: ' . $products->count() . '</p>
            <button class="no-print" onclick="window.print()">طباعة</button>
            <table>
                <thead>
                    <tr>
                        <th class="number">#</th>
                        <th>الكود</th>
                        <th>اسم المنتج</th>
                        <th>التصنيف</th>
                        <th>سعر التكلفة</th>
                        <th>سعر البيع</th>
                        <th>الضريبة</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($products as $index => $product) {
            $status = $product->is_active ? 'نشط' : 'غير نشط';
            $html .= '<tr>
                <td class="number">' . ($index + 1) . '</td>
                <td>' . htmlspecialchars($product->sku) . '</td>
                <td>' . htmlspecialchars($product->name) . '</td>
                <td>' . htmlspecialchars($product->category?->name ?? '-') . '</td>
                <td>' . number_format($product->cost_price, 2) . '</td>
                <td>' . number_format($product->selling_price, 2) . '</td>
                <td>' . $product->tax_rate . '%</td>
                <td>' . $status . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return $html;
    }

    // ===========================
    // Customers Export
    // ===========================

    /**
     * Export customers to Excel (CSV)
     */
    public function customersExcel(Request $request)
    {
        $customers = Customer::orderBy('name')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="customers_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'الكود',
                'اسم العميل',
                'البريد الإلكتروني',
                'الهاتف',
                'العنوان',
                'الحالة',
            ]);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->code ?? '',
                    $customer->name,
                    $customer->email ?? '',
                    $customer->phone ?? '',
                    $customer->address ?? '',
                    $customer->is_active ? 'نشط' : 'غير نشط',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export customers to PDF (printable HTML)
     */
    public function customersPdf(Request $request)
    {
        $customers = Customer::orderBy('name')->get();

        $html = '<!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>قائمة العملاء - Twinx ERP</title>
            <style>
                body { font-family: "Cairo", Arial, sans-serif; direction: rtl; margin: 20px; }
                h1 { text-align: center; color: #333; }
                .meta { text-align: center; color: #666; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: right; font-size: 12px; }
                th { background-color: #10b981; color: white; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .number { text-align: center; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <h1>قائمة العملاء</h1>
            <p class="meta">تاريخ التصدير: ' . date('Y-m-d H:i') . ' | عدد العملاء: ' . $customers->count() . '</p>
            <button class="no-print" onclick="window.print()">طباعة</button>
            <table>
                <thead>
                    <tr>
                        <th class="number">#</th>
                        <th>اسم العميل</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>العنوان</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($customers as $index => $customer) {
            $html .= '<tr>
                <td class="number">' . ($index + 1) . '</td>
                <td>' . htmlspecialchars($customer->name) . '</td>
                <td>' . htmlspecialchars($customer->email ?? '-') . '</td>
                <td>' . htmlspecialchars($customer->phone ?? '-') . '</td>
                <td>' . htmlspecialchars($customer->address ?? '-') . '</td>
                <td>' . ($customer->is_active ? 'نشط' : 'غير نشط') . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    // ===========================
    // Suppliers Export
    // ===========================

    /**
     * Export suppliers to Excel (CSV)
     */
    public function suppliersExcel(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="suppliers_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($suppliers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'الكود',
                'اسم المورد',
                'البريد الإلكتروني',
                'الهاتف',
                'العنوان',
                'الحالة',
            ]);

            foreach ($suppliers as $supplier) {
                fputcsv($file, [
                    $supplier->code ?? '',
                    $supplier->name,
                    $supplier->email ?? '',
                    $supplier->phone ?? '',
                    $supplier->address ?? '',
                    $supplier->is_active ? 'نشط' : 'غير نشط',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export suppliers to PDF (printable HTML)
     */
    public function suppliersPdf(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();

        $html = '<!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>قائمة الموردين - Twinx ERP</title>
            <style>
                body { font-family: "Cairo", Arial, sans-serif; direction: rtl; margin: 20px; }
                h1 { text-align: center; color: #333; }
                .meta { text-align: center; color: #666; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: right; font-size: 12px; }
                th { background-color: #3b82f6; color: white; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .number { text-align: center; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <h1>قائمة الموردين</h1>
            <p class="meta">تاريخ التصدير: ' . date('Y-m-d H:i') . ' | عدد الموردين: ' . $suppliers->count() . '</p>
            <button class="no-print" onclick="window.print()">طباعة</button>
            <table>
                <thead>
                    <tr>
                        <th class="number">#</th>
                        <th>اسم المورد</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>العنوان</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($suppliers as $index => $supplier) {
            $html .= '<tr>
                <td class="number">' . ($index + 1) . '</td>
                <td>' . htmlspecialchars($supplier->name) . '</td>
                <td>' . htmlspecialchars($supplier->email ?? '-') . '</td>
                <td>' . htmlspecialchars($supplier->phone ?? '-') . '</td>
                <td>' . htmlspecialchars($supplier->address ?? '-') . '</td>
                <td>' . ($supplier->is_active ? 'نشط' : 'غير نشط') . '</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
