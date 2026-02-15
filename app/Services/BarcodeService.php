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
        $barcodeData = $product->barcode ?: $product->sku;

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
     */
    public function generateAutoBarcode(Product $product): string
    {
        if ($product->barcode) {
            return $product->barcode;
        }

        // Generate EAN-13 like barcode: prefix + product ID padded
        $prefix = '200'; // Internal use prefix
        $productCode = str_pad($product->id, 9, '0', STR_PAD_LEFT);
        $barcodeWithoutCheck = $prefix . $productCode;

        // Calculate check digit (EAN-13 algorithm)
        $checkDigit = $this->calculateEan13CheckDigit($barcodeWithoutCheck);

        return $barcodeWithoutCheck . $checkDigit;
    }

    /**
     * Calculate EAN-13 check digit
     */
    protected function calculateEan13CheckDigit(string $code): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $code[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit;
    }
}
