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
        $isEAN13 = ctype_digit($code) && strlen($code) === 13;
        $isEAN8 = ctype_digit($code) && strlen($code) === 8;

        if ($isEAN13) {
            return $this->renderEAN13SVG($code, $w, $h);
        }
        if ($isEAN8) {
            return $this->renderEAN8SVG($code, $w, $h);
        }

        // Default: Code 128 rendering
        return $this->renderCode128SVG($code, $type, $w, $h);
    }

    /**
     * Render Code 128 barcode SVG (original style)
     */
    protected function renderCode128SVG(string $code, string $type, int $w, int $h): string
    {
        $bars = $this->encode($code, $type);
        $quietZone = 10 * $w;
        $barcodeWidth = strlen($bars) * $w;
        $totalWidth = $barcodeWidth + ($quietZone * 2);
        $totalHeight = $h + 18;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg"'
            . ' width="' . $totalWidth . '" height="' . $totalHeight . '"'
            . ' viewBox="0 0 ' . $totalWidth . ' ' . $totalHeight . '"'
            . ' shape-rendering="crispEdges">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        for ($i = 0; $i < strlen($bars); $i++) {
            if ($bars[$i] === '1') {
                $x = $quietZone + ($i * $w);
                $svg .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . $h . '" fill="black"/>';
            }
        }

        $svg .= '<text x="' . ($totalWidth / 2) . '" y="' . ($h + 14) . '"'
            . ' font-family="monospace" font-size="12" text-anchor="middle" fill="black"'
            . ' shape-rendering="auto">'
            . htmlspecialchars($code) . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Render EAN-13 barcode SVG with authentic visual
     * - Guard bars (start/center/end) extend 5 modules below normal bars
     * - First digit displayed to the left of the barcode
     * - Left 6 digits and right 6 digits displayed below their halves
     */
    protected function renderEAN13SVG(string $code, int $w, int $h): string
    {
        $bars = $this->encode($code);
        $guardExt = 5 * $w; // Guard bars extend below
        $quietZone = 11 * $w; // Extra for first digit
        $barcodeWidth = strlen($bars) * $w;
        $totalWidth = $barcodeWidth + ($quietZone * 2);
        $textY = $h + $guardExt + 14;
        $totalHeight = $h + $guardExt + 18;
        $fontSize = max(10, $w * 7);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg"'
            . ' width="' . $totalWidth . '" height="' . $totalHeight . '"'
            . ' viewBox="0 0 ' . $totalWidth . ' ' . $totalHeight . '"'
            . ' shape-rendering="crispEdges">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        // EAN-13 structure: 3(start) + 42(left 6 digits) + 5(center) + 42(right 6 digits) + 3(end) = 95 modules
        // Guard positions (module indices within the bars string)
        $startGuard = [0, 1, 2]; // 101
        $centerGuard = [45, 46, 47, 48, 49]; // 01010
        $endGuard = [92, 93, 94]; // 101

        $guardPositions = array_merge($startGuard, $centerGuard, $endGuard);

        for ($i = 0; $i < strlen($bars); $i++) {
            if ($bars[$i] === '1') {
                $x = $quietZone + ($i * $w);
                $barH = in_array($i, $guardPositions) ? ($h + $guardExt) : $h;
                $svg .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . $barH . '" fill="black"/>';
            }
        }

        // First digit — to the left of the barcode
        $svg .= '<text x="' . ($quietZone - $w * 2) . '" y="' . $textY . '"'
            . ' font-family="monospace" font-size="' . $fontSize . '" text-anchor="end" fill="black"'
            . ' shape-rendering="auto">' . $code[0] . '</text>';

        // Left 6 digits — centered under left half (modules 3-44)
        $leftCenterX = $quietZone + (3 + 21) * $w;
        $svg .= '<text x="' . $leftCenterX . '" y="' . $textY . '"'
            . ' font-family="monospace" font-size="' . $fontSize . '" text-anchor="middle" fill="black"'
            . ' shape-rendering="auto" letter-spacing="' . ($w * 1) . '">'
            . substr($code, 1, 6) . '</text>';

        // Right 6 digits — centered under right half (modules 50-91)
        $rightCenterX = $quietZone + (50 + 21) * $w;
        $svg .= '<text x="' . $rightCenterX . '" y="' . $textY . '"'
            . ' font-family="monospace" font-size="' . $fontSize . '" text-anchor="middle" fill="black"'
            . ' shape-rendering="auto" letter-spacing="' . ($w * 1) . '">'
            . substr($code, 7, 6) . '</text>';

        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Render EAN-8 barcode SVG with authentic visual
     */
    protected function renderEAN8SVG(string $code, int $w, int $h): string
    {
        $bars = $this->encode($code);
        $guardExt = 5 * $w;
        $quietZone = 9 * $w;
        $barcodeWidth = strlen($bars) * $w;
        $totalWidth = $barcodeWidth + ($quietZone * 2);
        $textY = $h + $guardExt + 14;
        $totalHeight = $h + $guardExt + 18;
        $fontSize = max(10, $w * 7);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg"'
            . ' width="' . $totalWidth . '" height="' . $totalHeight . '"'
            . ' viewBox="0 0 ' . $totalWidth . ' ' . $totalHeight . '"'
            . ' shape-rendering="crispEdges">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        // EAN-8: 3(start) + 28(left 4 digits) + 5(center) + 28(right 4 digits) + 3(end) = 67 modules
        $startGuard = [0, 1, 2];
        $centerGuard = [31, 32, 33, 34, 35];
        $endGuard = [64, 65, 66];
        $guardPositions = array_merge($startGuard, $centerGuard, $endGuard);

        for ($i = 0; $i < strlen($bars); $i++) {
            if ($bars[$i] === '1') {
                $x = $quietZone + ($i * $w);
                $barH = in_array($i, $guardPositions) ? ($h + $guardExt) : $h;
                $svg .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . $barH . '" fill="black"/>';
            }
        }

        // Left 4 digits
        $leftCenterX = $quietZone + (3 + 14) * $w;
        $svg .= '<text x="' . $leftCenterX . '" y="' . $textY . '"'
            . ' font-family="monospace" font-size="' . $fontSize . '" text-anchor="middle" fill="black"'
            . ' shape-rendering="auto" letter-spacing="' . ($w * 1) . '">'
            . substr($code, 0, 4) . '</text>';

        // Right 4 digits
        $rightCenterX = $quietZone + (36 + 14) * $w;
        $svg .= '<text x="' . $rightCenterX . '" y="' . $textY . '"'
            . ' font-family="monospace" font-size="' . $fontSize . '" text-anchor="middle" fill="black"'
            . ' shape-rendering="auto" letter-spacing="' . ($w * 1) . '">'
            . substr($code, 4, 4) . '</text>';

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
     * Encode string to barcode bar pattern
     *
     * Smart detection:
     *   - 13 digits (EAN-13) → proper EAN-13 encoding
     *   - 8 digits (EAN-8)   → proper EAN-8 encoding
     *   - Even-count digits   → Code 128C (compact)
     *   - Everything else     → Code 128B (alphanumeric)
     */
    protected function encode(string $code, string $type = 'C128'): string
    {
        // EAN-13: exactly 13 numeric digits
        if (ctype_digit($code) && strlen($code) === 13) {
            return $this->encodeEAN13($code);
        }

        // EAN-8: exactly 8 numeric digits
        if (ctype_digit($code) && strlen($code) === 8) {
            return $this->encodeEAN8($code);
        }

        // Code 128C for numeric-only with even length (compact)
        if (ctype_digit($code) && strlen($code) % 2 === 0 && strlen($code) >= 2) {
            return $this->encodeC128C($code);
        }

        return $this->encodeC128B($code);
    }

    /**
     * EAN-13 L-code patterns (left odd parity)
     */
    protected array $eanL = [
        '0001101',
        '0011001',
        '0010011',
        '0111101',
        '0100011',
        '0110001',
        '0101111',
        '0111011',
        '0110111',
        '0001011',
    ];

    /**
     * EAN-13 G-code patterns (left even parity)
     */
    protected array $eanG = [
        '0100111',
        '0110011',
        '0011011',
        '0100001',
        '0011101',
        '0111001',
        '0000101',
        '0010001',
        '0001001',
        '0010111',
    ];

    /**
     * EAN-13 R-code patterns (right)
     */
    protected array $eanR = [
        '1110010',
        '1100110',
        '1101100',
        '1000010',
        '1011100',
        '1001110',
        '1010000',
        '1000100',
        '1001000',
        '1110100',
    ];

    /**
     * EAN-13 first digit parity encoding
     * Determines which left digits use L vs G patterns
     */
    protected array $eanParity = [
        ['L', 'L', 'L', 'L', 'L', 'L'], // 0
        ['L', 'L', 'G', 'L', 'G', 'G'], // 1
        ['L', 'L', 'G', 'G', 'L', 'G'], // 2
        ['L', 'L', 'G', 'G', 'G', 'L'], // 3
        ['L', 'G', 'L', 'L', 'G', 'G'], // 4
        ['L', 'G', 'G', 'L', 'L', 'G'], // 5
        ['L', 'G', 'G', 'G', 'L', 'L'], // 6
        ['L', 'G', 'L', 'G', 'L', 'G'], // 7
        ['L', 'G', 'L', 'G', 'G', 'L'], // 8
        ['L', 'G', 'G', 'L', 'G', 'L'], // 9
    ];

    /**
     * Encode EAN-13 barcode
     */
    protected function encodeEAN13(string $code): string
    {
        $firstDigit = (int) $code[0];
        $parity = $this->eanParity[$firstDigit];

        // Start guard: 101
        $bars = '101';

        // Left side: 6 digits (digits 2-7, using parity from first digit)
        for ($i = 0; $i < 6; $i++) {
            $digit = (int) $code[$i + 1];
            if ($parity[$i] === 'L') {
                $bars .= $this->eanL[$digit];
            } else {
                $bars .= $this->eanG[$digit];
            }
        }

        // Center guard: 01010
        $bars .= '01010';

        // Right side: 6 digits (digits 8-13)
        for ($i = 7; $i < 13; $i++) {
            $digit = (int) $code[$i];
            $bars .= $this->eanR[$digit];
        }

        // End guard: 101
        $bars .= '101';

        return $bars;
    }

    /**
     * Encode EAN-8 barcode
     */
    protected function encodeEAN8(string $code): string
    {
        // Start guard: 101
        $bars = '101';

        // Left side: 4 digits using L patterns
        for ($i = 0; $i < 4; $i++) {
            $digit = (int) $code[$i];
            $bars .= $this->eanL[$digit];
        }

        // Center guard: 01010
        $bars .= '01010';

        // Right side: 4 digits using R patterns
        for ($i = 4; $i < 8; $i++) {
            $digit = (int) $code[$i];
            $bars .= $this->eanR[$digit];
        }

        // End guard: 101
        $bars .= '101';

        return $bars;
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
