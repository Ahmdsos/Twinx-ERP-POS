<?php

namespace App\Services;

use Modules\Inventory\Models\Product;

/**
 * BarcodeService
 * Generates barcodes and QR codes for products
 */
class BarcodeService
{
    /**
     * Generate barcode as SVG
     */
    public function generateBarcodeSvg(string $code, string $type = 'C128', int $width = 2, int $height = 60): string
    {
        // Using DNS1D compatible format
        $generator = new \App\Helpers\BarcodeGenerator();
        return $generator->getBarcodeSVG($code, $type, $width, $height);
    }

    /**
     * Generate barcode as PNG (base64)
     */
    public function generateBarcodePng(string $code, string $type = 'C128'): string
    {
        $generator = new \App\Helpers\BarcodeGenerator();
        return $generator->getBarcodePNG($code, $type, 2, 60);
    }

    /**
     * Generate barcode as HTML
     */
    public function generateBarcodeHtml(string $code, string $type = 'C128'): string
    {
        $generator = new \App\Helpers\BarcodeGenerator();
        return $generator->getBarcodeHTML($code, $type, 2, 60);
    }

    /**
     * Generate QR code as SVG
     */
    public function generateQrCodeSvg(string $data, int $size = 200): string
    {
        $generator = new \App\Helpers\QrCodeGenerator();
        return $generator->generateSvg($data, $size);
    }

    /**
     * Generate product label with barcode
     */
    public function generateProductLabel(Product $product, string $labelSize = 'standard'): array
    {
        // Auto-generate numeric barcode if product doesn't have one
        $barcodeData = $product->barcode;
        if (empty($barcodeData)) {
            $barcodeData = $this->generateAutoBarcode($product);
            $product->update(['barcode' => $barcodeData]);
        }

        return [
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $barcodeData,
            'barcode_svg' => $this->generateBarcodeSvg($barcodeData, 'C128', 2, 60),
            'price' => number_format($product->selling_price, 2),
            'label_size' => $labelSize,
        ];
    }

    /**
     * Generate multiple labels for batch printing
     */
    public function generateBatchLabels(array $products, int $copiesEach = 1): array
    {
        $labels = [];

        foreach ($products as $product) {
            for ($i = 0; $i < $copiesEach; $i++) {
                $labels[] = $this->generateProductLabel($product);
            }
        }

        return $labels;
    }

    /**
     * Generate auto barcode for product if not set
     * Format: 8 digits — prefix (20) + product ID (5 digits) + check digit (1)
     * Short enough for small labels, scannable by any laser scanner
     */
    public function generateAutoBarcode(Product $product): string
    {
        if ($product->barcode) {
            return $product->barcode;
        }

        // 8-digit format: "20" prefix + 5-digit product ID + 1 check digit
        $prefix = '20';
        $productCode = str_pad($product->id, 5, '0', STR_PAD_LEFT);
        $barcodeWithoutCheck = $prefix . $productCode; // 7 digits

        // Simple check digit (EAN-8 algorithm)
        $checkDigit = $this->calculateCheckDigit($barcodeWithoutCheck);

        return $barcodeWithoutCheck . $checkDigit; // 8 digits total
    }

    /**
     * Calculate EAN-8 compatible check digit
     */
    protected function calculateCheckDigit(string $code): int
    {
        $sum = 0;
        $len = strlen($code);
        for ($i = 0; $i < $len; $i++) {
            $digit = (int) $code[$i];
            // EAN-8: odd positions (1,3,5,7) × 3, even positions (2,4,6) × 1
            $sum += ($i % 2 === 0) ? $digit * 3 : $digit;
        }

        return (10 - ($sum % 10)) % 10;
    }
}
