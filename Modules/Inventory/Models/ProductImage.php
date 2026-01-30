<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * ProductImage Model
 * Handles product image storage and retrieval
 */
class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'is_primary',
        'sort_order',
        'alt_text',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'size' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    /**
     * Product relationship
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get full URL for the image
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        $disk = $this->disk ?? 'public';

        try {
            if (!Storage::disk($disk)->exists($this->path)) {
                return asset('images/no-image.svg');
            }
            return Storage::disk($disk)->url($this->path);
        } catch (\Exception $e) {
            return asset('images/no-image.svg');

        }
    }

    /**
     * Get thumbnail URL (using same path for now, can be enhanced)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        $disk = $this->disk ?? 'public';
        $thumbnailPath = str_replace('products/', 'products/thumbnails/', $this->path);

        try {
            if (Storage::disk($disk)->exists($thumbnailPath)) {
                return Storage::disk($disk)->url($thumbnailPath);
            }
            return $this->url;
        } catch (\Exception $e) {
            return $this->url;
        }
    }


    /**
     * Scope to get primary image
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for ordering
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Delete the physical file when model is deleted
     */
    protected static function booted(): void
    {
        static::deleting(function (ProductImage $image) {
            Storage::disk($image->disk)->delete($image->path);

            // Also delete thumbnail if exists
            $thumbnailPath = str_replace('products/', 'products/thumbnails/', $image->path);
            if (Storage::disk($image->disk)->exists($thumbnailPath)) {
                Storage::disk($image->disk)->delete($thumbnailPath);
            }
        });
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
