<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;
use Modules\Sales\Models\Customer;

/**
 * Bulk Actions Controller
 * Handles bulk delete, update, export for various entities
 */
class BulkActionsController extends Controller
{
    /**
     * Bulk delete products
     */
    public function deleteProducts(Request $request)
    {
        // SECURITY FIX: Require authorization for bulk delete
        if (!auth()->user()->can('products.delete')) {
            abort(403, 'غير مصرح لك بحذف المنتجات');
        }

        $request->validate(['ids' => 'required|array']);

        $count = Product::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "تم حذف {$count} منتج بنجاح",
        ]);
    }

    /**
     * Bulk update products (activate/deactivate)
     */
    public function updateProducts(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        $count = Product::whereIn('id', $request->ids)->update([
            'is_active' => $request->is_active
        ]);

        $status = $request->is_active ? 'تفعيل' : 'تعطيل';

        return response()->json([
            'success' => true,
            'message' => "تم {$status} {$count} منتج بنجاح",
        ]);
    }

    /**
     * Bulk move products to category
     */
    public function moveProductsCategory(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'category_id' => 'required|exists:categories,id',
        ]);

        $count = Product::whereIn('id', $request->ids)->update([
            'category_id' => $request->category_id
        ]);

        return response()->json([
            'success' => true,
            'message' => "تم نقل {$count} منتج بنجاح",
        ]);
    }

    /**
     * Bulk export products
     */
    public function exportProducts(Request $request)
    {
        $ids = explode(',', $request->ids);
        $format = $request->get('format', 'excel');

        $products = Product::with(['category', 'unit'])
            ->whereIn('id', $ids)
            ->get();

        // Use existing export service
        $exportService = new \App\Services\ExportService();

        $headers = ['SKU', 'الاسم', 'التصنيف', 'الوحدة', 'سعر التكلفة', 'سعر البيع', 'المخزون'];
        $rows = $products->map(fn(Product $p) => [
            $p->sku,
            $p->name,
            $p->category?->name ?? '-',
            $p->unit?->name ?? '-',
            $p->cost_price,
            $p->selling_price,
            $p->getTotalStock(),
        ]);

        return $exportService->toExcelCsv($headers, $rows, 'products-export-' . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Bulk delete customers
     */
    public function deleteCustomers(Request $request)
    {
        // SECURITY FIX: Require authorization for bulk delete
        if (!auth()->user()->can('customers.delete')) {
            abort(403, 'غير مصرح لك بحذف العملاء');
        }

        $request->validate(['ids' => 'required|array']);

        $count = Customer::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "تم حذف {$count} عميل بنجاح",
        ]);
    }

    /**
     * Bulk update customers (activate/deactivate)
     */
    public function updateCustomers(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'is_active' => 'required|boolean',
        ]);

        $count = Customer::whereIn('id', $request->ids)->update([
            'is_active' => $request->is_active
        ]);

        $status = $request->is_active ? 'تفعيل' : 'تعطيل';

        return response()->json([
            'success' => true,
            'message' => "تم {$status} {$count} عميل بنجاح",
        ]);
    }
}
