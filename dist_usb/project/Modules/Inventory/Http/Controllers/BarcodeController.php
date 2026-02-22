<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;

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
        // Auto-generate numeric barcode if product doesn't have one
        $barcodeData = $product->barcode;
        if (empty($barcodeData)) {
            $barcodeData = $this->barcodeService->generateAutoBarcode($product);
            $product->update(['barcode' => $barcodeData]);
        }

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
     * Batch print labels for multiple products (supports per-product copies + design settings)
     */
    public function batch(Request $request)
    {
        $request->validate([
            'products' => 'required|array|min:1|max:50',
            'products.*' => 'required|integer|exists:products,id',
        ]);

        $products = Product::with(['category', 'brand'])->whereIn('id', $request->products)->get();
        $copies = $request->input('copies', []);
        $barcodes = $request->input('barcodes', []);

        // Design settings
        $design = [
            // Visibility
            'show_name' => $request->input('show_name', '1') === '1',
            'show_price' => $request->input('show_price', '1') === '1',
            'show_sku' => $request->input('show_sku', '0') === '1',
            'show_barcode_num' => $request->input('show_barcode_num', '1') === '1',
            'show_category' => $request->input('show_category', '0') === '1',
            'show_brand' => $request->input('show_brand', '0') === '1',
            'header_text' => $request->input('header_text', ''),
            // Font sizes
            'header_font_size' => (int) $request->input('header_font_size', 8),
            'name_font_size' => (int) $request->input('name_font_size', 10),
            'price_font_size' => (int) $request->input('price_font_size', 12),
            'barcode_font_size' => (int) $request->input('barcode_font_size', 10),
            'sku_font_size' => (int) $request->input('sku_font_size', 8),
            'cat_font_size' => (int) $request->input('cat_font_size', 7),
            // Bold
            'name_bold' => $request->input('name_bold', '1') === '1',
            'price_bold' => $request->input('price_bold', '1') === '1',
            // Per-element alignment
            'name_align' => $request->input('name_align', 'center'),
            'price_align' => $request->input('price_align', 'center'),
            'header_align' => $request->input('header_align', 'center'),
            // Per-element position (above/below barcode)
            'name_position' => $request->input('name_position', 'above'),
            'price_position' => $request->input('price_position', 'below'),
            'sku_position' => $request->input('sku_position', 'above'),
            'cat_position' => $request->input('cat_position', 'above'),
            'brand_position' => $request->input('brand_position', 'above'),
            // Price format
            'price_decimals' => (int) $request->input('price_decimals', 2),
            'currency' => $request->input('currency', 'EGP'),
            'currency_position' => $request->input('currency_position', 'after'), // before | after | none
            // Sticker dimensions (mm)
            'sticker_w' => (int) $request->input('sticker_w', 50),
            'sticker_h' => (int) $request->input('sticker_h', 30),
            // Layout
            'layout' => $request->input('layout', 'vertical'),
            // Barcode dimensions
            'bar_width' => (int) $request->input('bar_width', 2),
            'bar_height' => (int) $request->input('bar_height', 60),
            // Font family
            'font_family' => $request->input('font_family', 'Arial'),
            // Name truncation
            'name_max_lines' => (int) $request->input('name_max_lines', 1),
        ];

        $stickers = [];
        foreach ($products as $product) {
            $barcodeData = $barcodes[$product->id] ?? $product->barcode;

            // Auto-generate if still empty
            if (empty($barcodeData)) {
                $barcodeData = $this->barcodeService->generateAutoBarcode($product);
                $product->update(['barcode' => $barcodeData]);
            }

            // If user edited the barcode, save it
            if (!empty($barcodes[$product->id]) && $barcodes[$product->id] !== $product->barcode) {
                $product->update(['barcode' => $barcodes[$product->id]]);
            }

            $productCopies = min((int) ($copies[$product->id] ?? 1), 100);

            $stickers[] = [
                'product' => $product,
                'barcode_svg' => $this->barcodeService->generateBarcodeSvg(
                    $barcodeData,
                    'C128',
                    $design['bar_width'],
                    $design['bar_height']
                ),
                'barcode_data' => $barcodeData,
                'copies' => $productCopies,
            ];
        }

        return view('inventory.products.barcode-batch', [
            'stickers' => $stickers,
            'design' => $design,
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
        // Auto-generate numeric barcode if product doesn't have one
        $barcodeData = $product->barcode;
        if (empty($barcodeData)) {
            $barcodeData = $this->barcodeService->generateAutoBarcode($product);
            $product->update(['barcode' => $barcodeData]);
        }

        $copies = min($request->get('copies', 1), 100);
        $showPrice = !$request->has('no_price');

        return view('inventory.products.barcode-print', [
            'product' => $product,
            'barcode_svg' => $this->barcodeService->generateBarcodeSvg($barcodeData),
            'copies' => $copies,
            'showPrice' => $showPrice,
        ]);
    }

    /**
     * Barcode Management page
     */
    public function manager()
    {
        return view('inventory.products.barcode-manager');
    }

    /**
     * Search products (JSON API for AJAX)
     */
    public function searchProducts(Request $request)
    {
        $products = Product::with(['category:id,name', 'brand:id,name'])
            ->select('id', 'name', 'sku', 'barcode', 'selling_price', 'category_id', 'brand_id', 'created_at', 'updated_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'barcode' => $p->barcode,
                    'selling_price' => $p->selling_price,
                    'category_name' => $p->category ? $p->category->name : null,
                    'brand_name' => $p->brand ? $p->brand->name : null,
                    'created_at' => $p->created_at ? $p->created_at->toDateString() : null,
                    'updated_at' => $p->updated_at ? $p->updated_at->toDateString() : null,
                ];
            });

        return response()->json(['products' => $products]);
    }
}
