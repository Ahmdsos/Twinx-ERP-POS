<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>طباعة باركود - دفعة</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .controls {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 5px;
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff;
        }

        .btn-secondary {
            background: #6b7280;
            color: #fff;
        }

        .labels-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .label {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            width: 50mm;
            height: 30mm;
        }

        .label.small {
            width: 40mm;
            height: 25mm;
        }

        .label.large {
            width: 60mm;
            height: 40mm;
        }

        .label .product-name {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label .barcode {
            margin: 3px 0;
        }

        .label .barcode svg {
            max-width: 100%;
            height: 30px;
        }

        .label .price {
            font-size: 12px;
            font-weight: bold;
            color: #2563eb;
        }

        .label .sku {
            font-size: 7px;
            color: #666;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .controls {
                display: none;
            }

            .labels-container {
                gap: 2mm;
            }

            .label {
                border: none;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="controls">
        <h2>🏷️ طباعة باركود - {{ $totalLabels }} ملصق</h2>
        <button class="btn btn-primary" onclick="window.print()">🖨️ طباعة الكل</button>
        <button class="btn btn-secondary" onclick="history.back()">رجوع</button>
    </div>

    <div class="labels-container">
        @foreach($labels as $label)
            <div class="label {{ $labelSize }}">
                <div class="product-name">{{ $label['product_name'] }}</div>
                <div class="barcode">{!! $label['barcode_svg'] !!}</div>
                <div class="price">{{ $label['price'] }} ج.م</div>
                <div class="sku">{{ $label['sku'] }}</div>
            </div>
        @endforeach
    </div>
</body>

</html>