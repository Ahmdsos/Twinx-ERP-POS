<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Sales\Models\Customer;
use Modules\Purchasing\Models\Supplier;
use App\Exports\InventoryTemplateExport;
use App\Exports\ProductsExport;
use App\Exports\ProductsSheet;
use App\Exports\CategoriesExport;
use App\Exports\BrandsExport;
use App\Exports\UnitsExport;
use App\Exports\WarehousesExport;
use App\Exports\CustomersExport;
use App\Exports\SuppliersExport;
use App\Services\ImportExportService;

/**
 * ExportController - Handles Excel, CSV and PDF exports for all entities
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
     * Export products to Excel or CSV
     */
    public function products(Request $request, ImportExportService $service)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'products_' . date('Y-m-d_H-i') . '.' . $format;

        $export = ($format === 'xlsx') ? new ProductsExport : new ProductsSheet();

        return $service->export($export, $filename);
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
     * Export customers to Excel or CSV
     */
    public function customers(Request $request, ImportExportService $service)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'customers_' . date('Y-m-d_H-i') . '.' . $format;

        return $service->export(new CustomersExport, $filename);
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
     * Export suppliers to Excel or CSV
     */
    public function suppliers(Request $request, ImportExportService $service)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'suppliers_' . date('Y-m-d_H-i') . '.' . $format;

        return $service->export(new SuppliersExport, $filename);
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

    /**
     * Export blank inventory template (multi-sheet)
     */
    public function inventoryTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new InventoryTemplateExport, 'twinx_inventory_template.xlsx');
    }

    /**
     * Export a sample JSON for unified inventory import
     */
    public function inventoryJsonSample()
    {
        $filename = 'twinx_inventory_comprehensive_sample.json';
        $sampleData = [
            'system_info' => [
                'exported_at' => now()->toDateTimeString(),
                'version' => '1.2.0',
                'description' => 'Comprehensive Inventory Schema Snapshot'
            ],
            'categories' => [
                [
                    'id' => 1,
                    'name' => 'Electronics',
                    'slug' => 'electronics',
                    'parent_id' => null,
                    'description' => 'Gadgets and hardware',
                    'sort_order' => 1,
                    'is_active' => true
                ]
            ],
            'brands' => [
                [
                    'id' => 1,
                    'name' => 'Samsung',
                    'description' => 'Global electronics brand',
                    'website' => 'https://samsung.com',
                    'is_active' => true
                ]
            ],
            'units' => [
                [
                    'id' => 1,
                    'name' => 'Piece',
                    'abbreviation' => 'pcs',
                    'is_base' => true,
                    'base_unit_id' => null,
                    'conversion_factor' => 1.0,
                    'is_active' => true
                ]
            ],
            'warehouses' => [
                [
                    'id' => 1,
                    'code' => 'WH-MAIN',
                    'name' => 'Main Warehouse',
                    'address' => '123 Industrial Rd',
                    'phone' => '01000000000',
                    'email' => 'wh@example.com',
                    'manager_id' => 1,
                    'is_default' => true,
                    'is_active' => true
                ]
            ],
            'products' => [
                [
                    'id' => 1,
                    'sku' => 'PROD-COMP-001',
                    'barcode' => '6281000000001',
                    'name' => 'Sample Master Product',
                    'description' => 'Full detail sample product',
                    'type' => 'goods', // goods or service
                    'category_id' => 1,
                    'brand_id' => 1,
                    'unit_id' => 1,
                    'purchase_unit_id' => 1,
                    'cost_price' => 1000.00,
                    'selling_price' => 1500.00,
                    'min_selling_price' => 1400.00,
                    'reorder_level' => 10,
                    'reorder_quantity' => 20,
                    'min_stock' => 5,
                    'max_stock' => 100,
                    'sales_account_id' => null,
                    'purchase_account_id' => null,
                    'inventory_account_id' => null,
                    'is_active' => true,
                    'is_sellable' => true,
                    'is_purchasable' => true,
                    // Logistics
                    'weight' => 1.5,
                    'weight_unit' => 'kg',
                    'length' => 20.0,
                    'width' => 10.0,
                    'height' => 5.0,
                    'dimension_unit' => 'cm',
                    'manufacturer' => 'Samsung Corp',
                    'manufacturer_part_number' => 'MPN-X1',
                    'country_of_origin' => 'Vietnam',
                    'hs_code' => '851712',
                    'lead_time_days' => 7,
                    'is_returnable' => true,
                    // Pricing Tiers
                    'price_distributor' => 1200.00,
                    'price_wholesale' => 1300.00,
                    'price_half_wholesale' => 1350.00,
                    'price_quarter_wholesale' => 1400.00,
                    'price_special' => 1100.00,
                    // Attributes & Meta
                    'color' => 'Black',
                    'size' => 'Large',
                    'tags' => ['hardware', 'new', 'premium'],
                    'seo_title' => 'Sample Master Product - Buy Now',
                    'seo_description' => 'High quality sample product for testing.',
                    // Warranty & Expiry
                    'warranty_months' => 12,
                    'warranty_type' => 'Manufacturer',
                    'expiry_date' => '2027-12-31',
                    'shelf_life_days' => 365,
                    'track_batches' => false,
                    'track_serials' => true,
                    // Complex Mappings
                    'stocks' => [
                        '1' => 75, // [Warehouse ID] => [Quantity]
                    ],
                    'images_list' => [
                        ['path' => 'products/sample_1.jpg', 'is_primary' => true, 'sort_order' => 0],
                        ['path' => 'products/sample_2.jpg', 'is_primary' => false, 'sort_order' => 1]
                    ]
                ]
            ],
        ];

        return response()->json($sampleData)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ===========================
    // Inventory Sub-entities Export
    // ===========================

    public function categories(Request $request, ImportExportService $service)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'categories_' . date('Y-m-d_H-i') . '.' . $format;
        return $service->export(new CategoriesExport, $filename);
    }

    public function brands(Request $request, ImportExportService $service)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'brands_' . date('Y-m-d_H-i') . '.' . $format;
        return $service->export(new BrandsExport, $filename);
    }

    public function units(Request $request, ImportExportService $service)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'units_' . date('Y-m-d_H-i') . '.' . $format;
        return $service->export(new UnitsExport, $filename);
    }

    public function warehouses(Request $request, ImportExportService $service)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'warehouses_' . date('Y-m-d_H-i') . '.' . $format;
        return $service->export(new WarehousesExport, $filename);
    }
}
