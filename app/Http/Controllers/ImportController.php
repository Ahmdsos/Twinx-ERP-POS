<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Unit;
use Modules\Sales\Models\Customer;
use Modules\Purchasing\Models\Supplier;

/**
 * ImportController - Handles CSV data imports for all entities
 */
class ImportController extends Controller
{
    /**
     * Show import page with options
     */
    public function index()
    {
        $demoFiles = [
            'products' => storage_path('demo/products_demo.csv'),
            'customers' => storage_path('demo/customers_demo.csv'),
            'suppliers' => storage_path('demo/suppliers_demo.csv'),
            'categories' => storage_path('demo/categories_demo.csv'),
            'warehouses' => storage_path('demo/warehouses_demo.csv'),
            'units' => storage_path('demo/units_demo.csv'),
        ];

        return view('imports.index', compact('demoFiles'));
    }

    /**
     * Import products from CSV
     */
    public function importProducts(Request $request)
    {
        $file = $request->file('file') ?? storage_path('demo/products_demo.csv');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
        }

        $data = $this->parseCSV($file);
        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                Product::updateOrCreate(
                    ['sku' => $row['sku'] ?? $row['code'] ?? 'SKU-' . ($index + 1)],
                    [
                        'name' => $row['name'],
                        'code' => $row['code'] ?? null,
                        'barcode' => $row['barcode'] ?? null,
                        'category_id' => $row['category_id'] ?? 1,
                        'unit_id' => $row['unit_id'] ?? 1,
                        'cost_price' => $row['cost_price'] ?? 0,
                        'selling_price' => $row['selling_price'] ?? 0,
                        'min_stock' => $row['min_stock'] ?? 0,
                        'max_stock' => $row['max_stock'] ?? 999,
                        'description' => $row['description'] ?? '',
                        'is_active' => $row['is_active'] ?? 1,
                        'brand' => $row['brand'] ?? null,
                        'weight' => $row['weight'] ?? null,
                        'weight_unit' => $row['weight_unit'] ?? 'kg',
                        'track_batches' => $row['track_batches'] ?? 0,
                        'track_serials' => $row['track_serials'] ?? 0,
                        'warranty_months' => $row['warranty_months'] ?? 0,
                        'country_of_origin' => $row['country_of_origin'] ?? null,
                        'is_returnable' => $row['is_returnable'] ?? 1,
                        'color' => $row['color'] ?? null,
                        'size' => $row['size'] ?? null,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return back()->with('success', "تم استيراد {$imported} منتج بنجاح")
            ->with('import_errors', $errors);
    }

    /**
     * Import customers from CSV
     */
    public function importCustomers(Request $request)
    {
        $file = $request->file('file') ?? storage_path('demo/customers_demo.csv');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
        }

        $data = $this->parseCSV($file);
        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                Customer::updateOrCreate(
                    ['email' => $row['email']],
                    [
                        'name' => $row['name'],
                        'phone' => $row['phone'] ?? null,
                        'mobile' => $row['mobile'] ?? null,
                        'address' => $row['address'] ?? null,
                        'city' => $row['city'] ?? null,
                        'country' => $row['country'] ?? 'مصر',
                        'tax_number' => $row['tax_number'] ?? null,
                        'credit_limit' => $row['credit_limit'] ?? 0,
                        'payment_terms' => $row['payment_terms'] ?? 30,
                        'notes' => $row['notes'] ?? null,
                        'is_active' => $row['is_active'] ?? 1,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return back()->with('success', "تم استيراد {$imported} عميل بنجاح")
            ->with('import_errors', $errors);
    }

    /**
     * Import suppliers from CSV
     */
    public function importSuppliers(Request $request)
    {
        $file = $request->file('file') ?? storage_path('demo/suppliers_demo.csv');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
        }

        $data = $this->parseCSV($file);
        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                Supplier::updateOrCreate(
                    ['email' => $row['email']],
                    [
                        'name' => $row['name'],
                        'phone' => $row['phone'] ?? null,
                        'mobile' => $row['mobile'] ?? null,
                        'address' => $row['address'] ?? null,
                        'city' => $row['city'] ?? null,
                        'country' => $row['country'] ?? 'مصر',
                        'tax_number' => $row['tax_number'] ?? null,
                        'payment_terms' => $row['payment_terms'] ?? 30,
                        'contact_person' => $row['contact_person'] ?? null,
                        'notes' => $row['notes'] ?? null,
                        'is_active' => $row['is_active'] ?? 1,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return back()->with('success', "تم استيراد {$imported} مورد بنجاح")
            ->with('import_errors', $errors);
    }

    /**
     * Import categories from CSV
     */
    public function importCategories(Request $request)
    {
        $file = $request->file('file') ?? storage_path('demo/categories_demo.csv');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
        }

        $data = $this->parseCSV($file);
        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                Category::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'description' => $row['description'] ?? null,
                        'parent_id' => !empty($row['parent_id']) ? $row['parent_id'] : null,
                        'is_active' => $row['is_active'] ?? 1,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return back()->with('success', "تم استيراد {$imported} فئة بنجاح")
            ->with('import_errors', $errors);
    }

    /**
     * Import warehouses from CSV
     */
    public function importWarehouses(Request $request)
    {
        $file = $request->file('file') ?? storage_path('demo/warehouses_demo.csv');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
        }

        $data = $this->parseCSV($file);
        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                Warehouse::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'address' => $row['address'] ?? null,
                        'city' => $row['city'] ?? null,
                        'phone' => $row['phone'] ?? null,
                        'manager' => $row['manager'] ?? null,
                        'is_active' => $row['is_active'] ?? 1,
                        'is_default' => $row['is_default'] ?? 0,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return back()->with('success', "تم استيراد {$imported} مستودع بنجاح")
            ->with('import_errors', $errors);
    }

    /**
     * Import units from CSV
     */
    public function importUnits(Request $request)
    {
        $file = $request->file('file') ?? storage_path('demo/units_demo.csv');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
        }

        $data = $this->parseCSV($file);
        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                Unit::updateOrCreate(
                    ['symbol' => $row['symbol']],
                    [
                        'name' => $row['name'],
                        'abbreviation' => $row['abbreviation'] ?? $row['symbol'],
                        'is_active' => $row['is_active'] ?? 1,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return back()->with('success', "تم استيراد {$imported} وحدة بنجاح")
            ->with('import_errors', $errors);
    }

    /**
     * Download sample CSV template
     */
    public function downloadTemplate($type)
    {
        $templates = [
            'products' => storage_path('demo/products_demo.csv'),
            'customers' => storage_path('demo/customers_demo.csv'),
            'suppliers' => storage_path('demo/suppliers_demo.csv'),
            'categories' => storage_path('demo/categories_demo.csv'),
            'warehouses' => storage_path('demo/warehouses_demo.csv'),
            'units' => storage_path('demo/units_demo.csv'),
        ];

        if (!isset($templates[$type]) || !file_exists($templates[$type])) {
            return back()->with('error', 'القالب غير موجود');
        }

        return response()->download($templates[$type], "{$type}_template.csv");
    }

    /**
     * Parse CSV file to array
     */
    private function parseCSV($file): array
    {
        $path = is_string($file) ? $file : $file->getRealPath();

        if (!file_exists($path)) {
            throw new \Exception('ملف CSV غير موجود');
        }

        $data = [];
        $handle = fopen($path, 'r');

        // Read UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            throw new \Exception('ملف CSV فارغ أو غير صالح');
        }

        // Clean headers
        $headers = array_map('trim', $headers);
        $headers = array_map(function ($h) {
            return preg_replace('/^\xEF\xBB\xBF/', '', $h);
        }, $headers);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, array_map('trim', $row));
            }
        }

        fclose($handle);
        return $data;
    }
}
