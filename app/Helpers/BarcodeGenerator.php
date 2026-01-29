<?php

namespace App\Helpers;

/**
 * BarcodeGenerator
 * Simple barcode generator using HTML/CSS
 */
class BarcodeGenerator
{
    protected array $codes128 = [];

    public function __construct()
    {
        // Code 128B character set
        $this->initCode128();
    }

    /**
     * Generate barcode as SVG
     */
    public function getBarcodeSVG(string $code, string $type = 'C128', int $w = 2, int $h = 60): string
    {
        $bars = $this->encode($code, $type);
        $width = strlen($bars) * $w;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . ($h + 20) . '" viewBox="0 0 ' . $width . ' ' . ($h + 20) . '">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        $x = 0;
        for ($i = 0; $i < strlen($bars); $i++) {
            if ($bars[$i] === '1') {
                $svg .= '<rect x="' . ($x * $w) . '" y="0" width="' . $w . '" height="' . $h . '" fill="black"/>';
            }
            $x++;
        }

        // Add text below barcode
        $svg .= '<text x="' . ($width / 2) . '" y="' . ($h + 15) . '" font-family="monospace" font-size="12" text-anchor="middle">' . htmlspecialchars($code) . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Generate barcode as PNG (base64)
     */
    public function getBarcodePNG(string $code, string $type = 'C128', int $w = 2, int $h = 60): string
    {
        $bars = $this->encode($code, $type);
        $width = strlen($bars) * $w;
        $height = $h + 20;

        if (!function_exists('imagecreate')) {
            return '';
        }

        $img = imagecreate($width, $height);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        imagefill($img, 0, 0, $white);

        $x = 0;
        for ($i = 0; $i < strlen($bars); $i++) {
            if ($bars[$i] === '1') {
                imagefilledrectangle($img, $x * $w, 0, ($x * $w) + $w - 1, $h - 1, $black);
            }
            $x++;
        }

        // Add text
        $textWidth = strlen($code) * 7;
        $textX = ($width - $textWidth) / 2;
        imagestring($img, 3, $textX, $h + 2, $code, $black);

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($data);
    }

    /**
     * Generate barcode as HTML table
     */
    public function getBarcodeHTML(string $code, string $type = 'C128', int $w = 2, int $h = 60): string
    {
        $bars = $this->encode($code, $type);

        $html = '<div style="display:inline-block;text-align:center;">';
        $html .= '<div style="display:flex;">';

        for ($i = 0; $i < strlen($bars); $i++) {
            $color = $bars[$i] === '1' ? 'black' : 'white';
            $html .= '<div style="width:' . $w . 'px;height:' . $h . 'px;background:' . $color . ';"></div>';
        }

        $html .= '</div>';
        $html .= '<div style="font-family:monospace;font-size:12px;margin-top:4px;">' . htmlspecialchars($code) . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Encode string to barcode pattern
     */
    protected function encode(string $code, string $type = 'C128'): string
    {
        // Start code B
        $bars = '11010010000';

        $checksum = 104; // Start B value

        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $value = ord($char) - 32;

            if ($value >= 0 && $value < 96) {
                $bars .= $this->getCode128Pattern($value);
                $checksum += $value * ($i + 1);
            }
        }

        // Add checksum
        $checksumValue = $checksum % 103;
        $bars .= $this->getCode128Pattern($checksumValue);

        // Stop code
        $bars .= '1100011101011';

        return $bars;
    }

    /**
     * Get Code 128 pattern for a value
     */
    protected function getCode128Pattern(int $value): string
    {
        $patterns = [
            '11011001100',
            '11001101100',
            '11001100110',
            '10010011000',
            '10010001100',
            '10001001100',
            '10011001000',
            '10011000100',
            '10001100100',
            '11001001000',
            '11001000100',
            '11000100100',
            '10110011100',
            '10011011100',
            '10011001110',
            '10111001100',
            '10011101100',
            '10011100110',
            '11001110010',
            '11001011100',
            '11001001110',
            '11011100100',
            '11001110100',
            '11101101110',
            '11101001100',
            '11100101100',
            '11100100110',
            '11101100100',
            '11100110100',
            '11100110010',
            '11011011000',
            '11011000110',
            '11000110110',
            '10100011000',
            '10001011000',
            '10001000110',
            '10110001000',
            '10001101000',
            '10001100010',
            '11010001000',
            '11000101000',
            '11000100010',
            '10110111000',
            '10110001110',
            '10001101110',
            '10111011000',
            '10111000110',
            '10001110110',
            '11101110110',
            '11010001110',
            '11000101110',
            '11011101000',
            '11011100010',
            '11011101110',
            '11101011000',
            '11101000110',
            '11100010110',
            '11101101000',
            '11101100010',
            '11100011010',
            '11101111010',
            '11001000010',
            '11110001010',
            '10100110000',
            '10100001100',
            '10010110000',
            '10010000110',
            '10000101100',
            '10000100110',
            '10110010000',
            '10110000100',
            '10011010000',
            '10011000010',
            '10000110100',
            '10000110010',
            '11000010010',
            '11001010000',
            '11110111010',
            '11000010100',
            '10001111010',
            '10100111100',
            '10010111100',
            '10010011110',
            '10111100100',
            '10011110100',
            '10011110010',
            '11110100100',
            '11110010100',
            '11110010010',
            '11011011110',
            '11011110110',
            '11110110110',
            '10101111000',
            '10100011110',
            '10001011110',
            '10111101000',
            '10111100010',
            '11110101000',
            '11110100010',
            '10111011110',
            '10111101110',
            '11101011110',
            '11110101110',
        ];

        return $patterns[$value] ?? '11011001100';
    }

    protected function initCode128(): void
    {
        // Initialization if needed
    }
}
