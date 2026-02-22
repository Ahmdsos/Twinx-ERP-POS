<?php

namespace App\Helpers;

/**
 * BarcodeGenerator
 * Generates Code 128B barcodes as SVG/PNG/HTML
 * Optimized for laser scanner readability with proper quiet zones
 */
class BarcodeGenerator
{
    /**
     * Code 128 bar patterns (values 0-102)
     * Each pattern is an 11-module sequence of bars (1) and spaces (0)
     */
    protected array $patterns = [
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

    // Start codes
    const START_B = '11010010000';
    const START_C = '11010011100';
    // Stop code (13 modules)
    const STOP = '1100011101011';

    /**
     * Generate barcode as SVG - optimized for print & laser scanners
     *
     * @param string $code    The data to encode
     * @param string $type    Barcode type (C128 = Code 128)
     * @param int    $w       Width of each module (bar unit) in pixels
     * @param int    $h       Height of bars in pixels
     */
    public function getBarcodeSVG(string $code, string $type = 'C128', int $w = 2, int $h = 60): string
    {
        $bars = $this->encode($code, $type);

        // Quiet zone: 10 modules minimum on each side (Code 128 spec)
        $quietZone = 10 * $w;
        $barcodeWidth = strlen($bars) * $w;
        $totalWidth = $barcodeWidth + ($quietZone * 2);
        $totalHeight = $h + 18; // bars + text

        $svg = '<svg xmlns="http://www.w3.org/2000/svg"'
            . ' width="' . $totalWidth . '" height="' . $totalHeight . '"'
            . ' viewBox="0 0 ' . $totalWidth . ' ' . $totalHeight . '"'
            . ' shape-rendering="crispEdges">';

        // White background (includes quiet zones)
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        // Draw bars (offset by quiet zone)
        for ($i = 0; $i < strlen($bars); $i++) {
            if ($bars[$i] === '1') {
                $x = $quietZone + ($i * $w);
                $svg .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . $h . '" fill="black"/>';
            }
        }

        // Text below barcode (centered)
        $svg .= '<text x="' . ($totalWidth / 2) . '" y="' . ($h + 14) . '"'
            . ' font-family="monospace" font-size="12" text-anchor="middle" fill="black"'
            . ' shape-rendering="auto">'
            . htmlspecialchars($code) . '</text>';

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Generate barcode as PNG (base64)
     */
    public function getBarcodePNG(string $code, string $type = 'C128', int $w = 2, int $h = 60): string
    {
        $bars = $this->encode($code, $type);

        $quietZone = 10 * $w;
        $barcodeWidth = strlen($bars) * $w;
        $totalWidth = $barcodeWidth + ($quietZone * 2);
        $totalHeight = $h + 20;

        if (!function_exists('imagecreate')) {
            return '';
        }

        $img = imagecreate($totalWidth, $totalHeight);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        imagefill($img, 0, 0, $white);

        for ($i = 0; $i < strlen($bars); $i++) {
            if ($bars[$i] === '1') {
                $x = $quietZone + ($i * $w);
                imagefilledrectangle($img, $x, 0, $x + $w - 1, $h - 1, $black);
            }
        }

        // Add text
        $textWidth = strlen($code) * 7;
        $textX = ($totalWidth - $textWidth) / 2;
        imagestring($img, 3, (int) $textX, $h + 2, $code, $black);

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($data);
    }

    /**
     * Generate barcode as HTML
     */
    public function getBarcodeHTML(string $code, string $type = 'C128', int $w = 2, int $h = 60): string
    {
        $bars = $this->encode($code, $type);

        $html = '<div style="display:inline-block;text-align:center;background:white;padding:0 ' . (10 * $w) . 'px;">';
        $html .= '<div style="display:flex;">';

        for ($i = 0; $i < strlen($bars); $i++) {
            $color = $bars[$i] === '1' ? 'black' : 'white';
            $html .= '<div style="width:' . $w . 'px;height:' . $h . 'px;background:' . $color . ';"></div>';
        }

        $html .= '</div>';
        $html .= '<div style="font-family:monospace;font-size:12px;margin-top:4px;color:black;">' . htmlspecialchars($code) . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Encode string to Code 128 bar pattern
     *
     * Detects whether to use Code 128B (alphanumeric) or Code 128C (digits only, compact)
     */
    protected function encode(string $code, string $type = 'C128'): string
    {
        // Use Code 128C for all-numeric strings with even length (more compact, better for laser scanners)
        if (ctype_digit($code) && strlen($code) % 2 === 0 && strlen($code) >= 2) {
            return $this->encodeC128C($code);
        }

        return $this->encodeC128B($code);
    }

    /**
     * Encode using Code 128B (alphanumeric characters)
     */
    protected function encodeC128B(string $code): string
    {
        $bars = self::START_B; // Start Code B
        $checksum = 104;       // Start B value

        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $value = ord($char) - 32;

            if ($value >= 0 && $value < 96) {
                $bars .= $this->patterns[$value];
                $checksum += $value * ($i + 1);
            }
        }

        // Add checksum character
        $checksumValue = $checksum % 103;
        $bars .= $this->patterns[$checksumValue];

        // Stop code
        $bars .= self::STOP;

        return $bars;
    }

    /**
     * Encode using Code 128C (digit pairs — more compact for numbers)
     * This produces shorter barcodes that are easier for laser scanners to read
     */
    protected function encodeC128C(string $code): string
    {
        $bars = self::START_C; // Start Code C
        $checksum = 105;       // Start C value
        $position = 1;

        // Encode two digits at a time
        for ($i = 0; $i < strlen($code); $i += 2) {
            $pair = (int) substr($code, $i, 2);
            $bars .= $this->patterns[$pair];
            $checksum += $pair * $position;
            $position++;
        }

        // Add checksum character
        $checksumValue = $checksum % 103;
        $bars .= $this->patterns[$checksumValue];

        // Stop code
        $bars .= self::STOP;

        return $bars;
    }
}
