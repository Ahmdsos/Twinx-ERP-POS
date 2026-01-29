<?php

namespace App\Helpers;

/**
 * QrCodeGenerator
 * Simple QR code generator using HTML/CSS approach
 */
class QrCodeGenerator
{
    /**
     * Generate QR code as SVG
     * Note: For production, use chillerlan/php-qrcode or similar
     */
    public function generateSvg(string $data, int $size = 200): string
    {
        // Generate a simple placeholder QR code
        // In production, integrate with proper QR library
        $moduleCount = 21; // Version 1 QR code
        $moduleSize = $size / $moduleCount;

        // Create a deterministic pattern based on data (simplified)
        $hash = md5($data);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        // Draw finder patterns (corners)
        $svg .= $this->drawFinderPattern(0, 0, $moduleSize);
        $svg .= $this->drawFinderPattern($size - (7 * $moduleSize), 0, $moduleSize);
        $svg .= $this->drawFinderPattern(0, $size - (7 * $moduleSize), $moduleSize);

        // Draw data modules (simplified)
        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                // Skip finder pattern areas
                if ($this->isFinderArea($row, $col, $moduleCount)) {
                    continue;
                }

                // Use hash to determine if module is dark
                $hashIndex = ($row * $moduleCount + $col) % 32;
                $isDark = hexdec($hash[$hashIndex]) > 7;

                if ($isDark) {
                    $x = $col * $moduleSize;
                    $y = $row * $moduleSize;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $moduleSize . '" height="' . $moduleSize . '" fill="black"/>';
                }
            }
        }

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Draw finder pattern (the 3 corner squares)
     */
    protected function drawFinderPattern(float $x, float $y, float $moduleSize): string
    {
        $svg = '';

        // Outer black square (7x7)
        $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . (7 * $moduleSize) . '" height="' . (7 * $moduleSize) . '" fill="black"/>';

        // Inner white square (5x5)
        $svg .= '<rect x="' . ($x + $moduleSize) . '" y="' . ($y + $moduleSize) . '" width="' . (5 * $moduleSize) . '" height="' . (5 * $moduleSize) . '" fill="white"/>';

        // Center black square (3x3)
        $svg .= '<rect x="' . ($x + 2 * $moduleSize) . '" y="' . ($y + 2 * $moduleSize) . '" width="' . (3 * $moduleSize) . '" height="' . (3 * $moduleSize) . '" fill="black"/>';

        return $svg;
    }

    /**
     * Check if position is in finder pattern area
     */
    protected function isFinderArea(int $row, int $col, int $moduleCount): bool
    {
        // Top-left finder
        if ($row < 8 && $col < 8) {
            return true;
        }

        // Top-right finder
        if ($row < 8 && $col >= $moduleCount - 8) {
            return true;
        }

        // Bottom-left finder
        if ($row >= $moduleCount - 8 && $col < 8) {
            return true;
        }

        return false;
    }

    /**
     * Generate as PNG (base64)
     */
    public function generatePng(string $data, int $size = 200): string
    {
        // Create image from SVG or use GD
        if (!function_exists('imagecreate')) {
            return '';
        }

        $moduleCount = 21;
        $moduleSize = $size / $moduleCount;
        $hash = md5($data);

        $img = imagecreate($size, $size);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        imagefill($img, 0, 0, $white);

        // Draw finder patterns
        $this->drawFinderPatternGd($img, 0, 0, $moduleSize, $black, $white);
        $this->drawFinderPatternGd($img, $size - (7 * $moduleSize), 0, $moduleSize, $black, $white);
        $this->drawFinderPatternGd($img, 0, $size - (7 * $moduleSize), $moduleSize, $black, $white);

        // Draw data
        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                if ($this->isFinderArea($row, $col, $moduleCount)) {
                    continue;
                }

                $hashIndex = ($row * $moduleCount + $col) % 32;
                $isDark = hexdec($hash[$hashIndex]) > 7;

                if ($isDark) {
                    $x = (int) ($col * $moduleSize);
                    $y = (int) ($row * $moduleSize);
                    imagefilledrectangle($img, $x, $y, $x + $moduleSize - 1, $y + $moduleSize - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($data);
    }

    /**
     * Draw finder pattern using GD
     */
    protected function drawFinderPatternGd($img, float $x, float $y, float $moduleSize, $black, $white): void
    {
        // Outer black
        imagefilledrectangle($img, (int) $x, (int) $y, (int) ($x + 7 * $moduleSize - 1), (int) ($y + 7 * $moduleSize - 1), $black);
        // Inner white
        imagefilledrectangle($img, (int) ($x + $moduleSize), (int) ($y + $moduleSize), (int) ($x + 6 * $moduleSize - 1), (int) ($y + 6 * $moduleSize - 1), $white);
        // Center black
        imagefilledrectangle($img, (int) ($x + 2 * $moduleSize), (int) ($y + 2 * $moduleSize), (int) ($x + 5 * $moduleSize - 1), (int) ($y + 5 * $moduleSize - 1), $black);
    }
}
