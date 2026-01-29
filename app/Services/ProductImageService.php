<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductImage;

/**
 * ProductImageService
 * Handles image upload, resize, and management
 */
class ProductImageService
{
    protected string $disk = 'public';
    protected string $basePath = 'products';
    protected int $thumbnailWidth = 150;
    protected int $thumbnailHeight = 150;
    protected int $maxWidth = 1200;
    protected int $maxHeight = 1200;

    /**
     * Upload a single image for a product
     */
    public function upload(Product $product, UploadedFile $file, bool $isPrimary = false): ProductImage
    {
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $this->basePath . '/' . $product->id . '/' . $filename;

        // Store original (resized if too large)
        $storedPath = $this->storeAndResize($file, $path);

        // Create thumbnail
        $this->createThumbnail($file, $product->id, $filename);

        // If this is primary, unset other primaries
        if ($isPrimary) {
            $product->images()->update(['is_primary' => false]);
        }

        // Get next sort order
        $maxSort = $product->images()->max('sort_order') ?? 0;

        return ProductImage::create([
            'product_id' => $product->id,
            'filename' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'disk' => $this->disk,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'is_primary' => $isPrimary || $product->images()->count() === 0,
            'sort_order' => $maxSort + 1,
        ]);
    }

    /**
     * Upload multiple images
     */
    public function uploadMultiple(Product $product, array $files): array
    {
        $images = [];

        foreach ($files as $index => $file) {
            $images[] = $this->upload($product, $file, $index === 0 && $product->images()->count() === 0);
        }

        return $images;
    }

    /**
     * Set an image as primary
     */
    public function setPrimary(ProductImage $image): void
    {
        // Unset all other primaries for this product
        ProductImage::where('product_id', $image->product_id)
            ->where('id', '!=', $image->id)
            ->update(['is_primary' => false]);

        $image->update(['is_primary' => true]);
    }

    /**
     * Update sort order
     */
    public function updateOrder(Product $product, array $imageIds): void
    {
        foreach ($imageIds as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }
    }

    /**
     * Delete an image
     */
    public function delete(ProductImage $image): void
    {
        $wasPrimary = $image->is_primary;
        $productId = $image->product_id;

        $image->delete();

        // If deleted image was primary, set next one as primary
        if ($wasPrimary) {
            $nextImage = ProductImage::where('product_id', $productId)
                ->ordered()
                ->first();

            if ($nextImage) {
                $nextImage->update(['is_primary' => true]);
            }
        }
    }

    /**
     * Store and resize image if too large
     */
    protected function storeAndResize(UploadedFile $file, string $path): string
    {
        // For now, just store directly
        // If Intervention Image is installed, resize
        if (class_exists(ImageManager::class)) {
            try {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getPathname());

                // Resize if too large
                if ($image->width() > $this->maxWidth || $image->height() > $this->maxHeight) {
                    $image->scaleDown($this->maxWidth, $this->maxHeight);
                }

                $encoded = $image->toJpeg(85);
                Storage::disk($this->disk)->put($path, $encoded);

                return $path;
            } catch (\Exception $e) {
                // Fall through to basic storage
            }
        }

        return $file->storeAs(dirname($path), basename($path), $this->disk);
    }

    /**
     * Create thumbnail
     */
    protected function createThumbnail(UploadedFile $file, int $productId, string $filename): void
    {
        $thumbnailPath = $this->basePath . '/thumbnails/' . $productId . '/' . $filename;

        if (class_exists(ImageManager::class)) {
            try {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getPathname());
                $image->cover($this->thumbnailWidth, $this->thumbnailHeight);

                $encoded = $image->toJpeg(80);
                Storage::disk($this->disk)->put($thumbnailPath, $encoded);
            } catch (\Exception $e) {
                // Silently fail, use original as thumbnail
            }
        }
    }
}
