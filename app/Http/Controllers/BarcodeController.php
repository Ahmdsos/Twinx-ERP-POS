<?php

namespace App\Http\Controllers;

use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;

/**
 * BarcodeController
 * Handles barcode generation and printing
 */
class BarcodeController extends Controller
{
    public function __construct(protected BarcodeService $barcodeService)
    {
    }

    /**
     * Show barcode for a single product
     */
    public function show(Product $product)
    {
        $barcodeData = $product->barcode ?: $product->sku;

        return response()->json([
            'barcode_svg' => $this->barcodeService->generateBarcodeSvg($barcodeData),
            'barcode_html' => $this->barcodeService->generateBarcodeHtml($barcodeData),
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $barcodeData,
                'price' => $product->selling_price,
            ],
        ]);
    }

    /**
     * Generate printable label for a product
     */
    public function label(Product $product, Request $request)
    {
        $copies = $request->get('copies', 1);
        $labelSize = $request->get('size', 'standard'); // standard, small, large

        $label = $this->barcodeService->generateProductLabel($product, $labelSize);

        return view('inventory.products.barcode-label', [
            'product' => $product,
            'label' => $label,
            'copies' => min($copies, 100), // Max 100 copies
            'labelSize' => $labelSize,
        ]);
    }

    /**
     * Batch print labels for multiple products
     */
    public function batch(Request $request)
    {
        $request->validate([
            'products' => 'required|array|min:1|max:50',
            'products.*' => 'required|integer|exists:products,id',
            'copies' => 'integer|min:1|max:100',
            'size' => 'in:small,standard,large',
        ]);

        $products = Product::whereIn('id', $request->products)->get();
        $copies = $request->get('copies', 1);
        $labelSize = $request->get('size', 'standard');

        $labels = $this->barcodeService->generateBatchLabels($products->all(), $copies);

        return view('inventory.products.barcode-batch', [
            'labels' => $labels,
            'labelSize' => $labelSize,
            'totalLabels' => count($labels),
        ]);
    }

    /**
     * Generate auto barcode for product
     */
    public function generate(Product $product)
    {
        $barcode = $this->barcodeService->generateAutoBarcode($product);

        $product->update(['barcode' => $barcode]);

        return response()->json([
            'success' => true,
            'barcode' => $barcode,
            'message' => 'تم توليد الباركود بنجاح',
        ]);
    }

    /**
     * Print barcode preview page
     */
    public function printPreview(Product $product, Request $request)
    {
        $barcodeData = $product->barcode ?: $product->sku;
        $copies = min($request->get('copies', 1), 100);

        return view('inventory.products.barcode-print', [
            'product' => $product,
            'barcode_svg' => $this->barcodeService->generateBarcodeSvg($barcodeData),
            'copies' => $copies,
        ]);
    }
}
