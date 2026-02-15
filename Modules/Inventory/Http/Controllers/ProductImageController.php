<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Services\ProductImageService;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductImage;

/**
 * ProductImageController
 * Handles product image upload and management
 */
class ProductImageController extends Controller
{
    public function __construct(protected ProductImageService $imageService)
    {
    }

    /**
     * Upload images for a product
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        $images = $this->imageService->uploadMultiple($product, $request->file('images'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم رفع الصور بنجاح',
                'images' => $images,
            ]);
        }

        return back()->with('success', 'تم رفع الصور بنجاح');
    }

    /**
     * Set an image as primary
     */
    public function setPrimary(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        $this->imageService->setPrimary($image);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تعيين الصورة كصورة رئيسية',
            ]);
        }

        return back()->with('success', 'تم تعيين الصورة كصورة رئيسية');
    }

    /**
     * Update image order
     */
    public function updateOrder(Request $request, Product $product)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:product_images,id',
        ]);

        $this->imageService->updateOrder($product, $request->order);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث ترتيب الصور',
        ]);
    }

    /**
     * Delete an image
     */
    public function destroy(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        $this->imageService->delete($image);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الصورة',
            ]);
        }

        return back()->with('success', 'تم حذف الصورة');
    }
}
