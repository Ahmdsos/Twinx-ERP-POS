<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Print Barcodes') }}</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700&family=Inter:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 2mm;
        }

        body {
            background: #fff;
            color: #000;
        }

        .ctrl {
            padding: 10px 16px;
            background: #1a1625;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: Arial, sans-serif;
            font-size: 0.85rem;
        }

        .ctrl button {
            padding: 8px 20px;
            background: #a855f7;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 13px;
        }

        .ctrl button:hover {
            background: #7e22ce;
        }

        .ctrl .close-btn {
            background: #555;
        }

        .ctrl .info {
            color: #999;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5mm;
            padding: 1mm;
            justify-content: flex-start;
        }

        .sticker {
            overflow: hidden;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
            font-family: {{ $design['font_family'] }}, sans-serif;
            width: {{ $design['sticker_w'] }}mm;
            height: {{ $design['sticker_h'] }}mm;
            padding: 1mm;
            position: relative;
            border: 0.3px solid #eee;
        }

        .sticker .above {
            display: flex;
            flex-direction: column;
            gap: 0.2mm;
        }

        .sticker .bar-area {
            display: flex;
            justify-content: center;
            flex-shrink: 0;
        }

        .sticker .bar-area svg {
            height: auto;
            display: block;
            image-rendering: pixelated;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .sticker .below {
            display: flex;
            flex-direction: column;
            gap: 0.2mm;
        }

        .el-header {
            color: #444;
        }

        .el-name {
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            line-height: 1.15;
        }

        .el-name.lines1 {
            white-space: nowrap;
        }

        .el-name.lines2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            white-space: normal;
        }

        .el-price {
            line-height: 1.15;
        }

        .el-sku {
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .el-cat,
        .el-brand {
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .el-bcnum {
            font-family: 'Courier New', monospace;
            color: #222;
            letter-spacing: 0.5px;
        }

        @media print {
            .ctrl {
                display: none !important;
            }

            .sticker {
                border: none;
            }
        }
    </style>
</head>

<body>
    <div class="ctrl">
        <button onclick="window.print()">🖨️ {{ __('Print') }}</button>
        <span class="info">{{ collect($stickers)->sum('copies') }} {{ __('stickers') }} ·
            {{ $design['sticker_w'] }}×{{ $design['sticker_h'] }}mm</span>
        <button class="close-btn" onclick="window.close()">✕</button>
    </div>
    <div class="grid">
        @foreach($stickers as $sticker)
            @for($i = 0; $i < $sticker['copies']; $i++)
                @php
                    $p = $sticker['product'];
                    $d = $design;
                    // Price formatting
                    $priceNum = number_format($p->selling_price, $d['price_decimals']);
                    $curr = $d['currency'] ?? '';
                    $currPos = $d['currency_position'] ?? 'after';
                    if ($currPos === 'before' && $curr)
                        $priceStr = $curr . ' ' . $priceNum;
                    elseif ($currPos === 'after' && $curr)
                        $priceStr = $priceNum . ' ' . $curr;
                    else
                        $priceStr = $priceNum;

                    // Collect elements into above/below
                    $above = [];
                    $below = [];

                    // Header always above
                    if (!empty($d['header_text'])) {
                        $above[] = ['type' => 'header', 'text' => $d['header_text'], 'fs' => $d['header_font_size'], 'bold' => false, 'align' => $d['header_align'] ?? 'center'];
                    }

                    // Category
                    if ($d['show_category'] && $p->category) {
                        $pos = $d['cat_position'] ?? 'above';
                        $el = ['type' => 'cat', 'text' => $p->category->name, 'fs' => $d['cat_font_size'] ?? 7, 'bold' => false, 'align' => $d['header_align'] ?? 'center'];
                        if ($pos === 'above')
                            $above[] = $el;
                        else
                            $below[] = $el;
                    }

                    // Brand
                    if ($d['show_brand'] && $p->brand) {
                        $pos = $d['brand_position'] ?? 'above';
                        $el = ['type' => 'brand', 'text' => $p->brand->name, 'fs' => $d['cat_font_size'] ?? 7, 'bold' => false, 'align' => $d['header_align'] ?? 'center'];
                        if ($pos === 'above')
                            $above[] = $el;
                        else
                            $below[] = $el;
                    }

                    // Name
                    if ($d['show_name']) {
                        $pos = $d['name_position'] ?? 'above';
                        $el = ['type' => 'name', 'text' => $p->name, 'fs' => $d['name_font_size'], 'bold' => $d['name_bold'], 'align' => $d['name_align'] ?? 'center'];
                        if ($pos === 'above')
                            $above[] = $el;
                        else
                            $below[] = $el;
                    }

                    // SKU
                    if ($d['show_sku']) {
                        $pos = $d['sku_position'] ?? 'above';
                        $el = ['type' => 'sku', 'text' => $p->sku, 'fs' => $d['sku_font_size'] ?? 8, 'bold' => false, 'align' => $d['name_align'] ?? 'center'];
                        if ($pos === 'above')
                            $above[] = $el;
                        else
                            $below[] = $el;
                    }

                    // Price
                    if ($d['show_price']) {
                        $pos = $d['price_position'] ?? 'below';
                        $el = ['type' => 'price', 'text' => $priceStr, 'fs' => $d['price_font_size'], 'bold' => $d['price_bold'], 'align' => $d['price_align'] ?? 'center'];
                        if ($pos === 'above')
                            $above[] = $el;
                        else
                            $below[] = $el;
                    }

                    // Barcode number always below barcode
                    if ($d['show_barcode_num']) {
                        $below[] = ['type' => 'bcnum', 'text' => $sticker['barcode_data'], 'fs' => $d['barcode_font_size'], 'bold' => false, 'align' => 'center'];
                    }

                    $nameLines = $d['name_max_lines'] ?? 1;
                @endphp

                <div class="sticker">
                    <div class="above">
                        @foreach($above as $el)
                            <div class="el-{{ $el['type'] }} {{ $el['type'] === 'name' ? 'lines' . $nameLines : '' }}"
                                style="font-size:{{ $el['fs'] }}px;font-weight:{{ $el['bold'] ? '700' : '400' }};text-align:{{ $el['align'] }};">
                                {{ $el['text'] }}
                            </div>
                        @endforeach
                    </div>
                    <div class="bar-area">{!! $sticker['barcode_svg'] !!}</div>
                    <div class="below">
                        @foreach($below as $el)
                            <div class="el-{{ $el['type'] }} {{ $el['type'] === 'name' ? 'lines' . $nameLines : '' }}"
                                style="font-size:{{ $el['fs'] }}px;font-weight:{{ $el['bold'] ? '700' : '400' }};text-align:{{ $el['align'] }};">
                                {{ $el['text'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endfor
        @endforeach
    </div>
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 600));</script>
</body>

</html>