<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>طباعة الباركود - {{ $product->name }}</title>
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

        .controls h2 {
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 200px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
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
            justify-content: flex-start;
        }

        .label {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .label.small {
            width: 40mm;
            height: 25mm;
        }

        .label.standard {
            width: 50mm;
            height: 30mm;
        }

        .label.large {
            width: 60mm;
            height: 40mm;
        }

        .label .product-name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label .barcode {
            margin: 5px 0;
        }

        .label .barcode svg {
            max-width: 100%;
            height: auto;
        }

        .label .price {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
        }

        .label .sku {
            font-size: 8px;
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
        <h2>🏷️ طباعة باركود: {{ $product->name }}</h2>

        <div style="display: flex; gap: 20px; align-items: end;">
            <div class="form-group">
                <label>عدد النسخ:</label>
                <input type="number" id="copies" value="{{ $copies }}" min="1" max="100">
            </div>

            <div class="form-group">
                <label>حجم الملصق:</label>
                <select id="labelSize">
                    <option value="small">صغير (40mm × 25mm)</option>
                    <option value="standard" selected>عادي (50mm × 30mm)</option>
                    <option value="large">كبير (60mm × 40mm)</option>
                </select>
            </div>

            <div>
                <button class="btn btn-primary" onclick="updateLabels()">تحديث</button>
                <button class="btn btn-primary" onclick="window.print()">🖨️ طباعة</button>
                <button class="btn btn-secondary" onclick="history.back()">رجوع</button>
            </div>
        </div>
    </div>

    <div class="labels-container" id="labelsContainer">
        @for($i = 0; $i < $copies; $i++)
            <div class="label standard">
                <div class="product-name">{{ $product->name }}</div>
                <div class="barcode">
                    {!! $barcode_svg !!}
                </div>
                <div class="price">{{ number_format($product->selling_price, 2) }} ج.م</div>
                <div class="sku">{{ $product->sku }}</div>
            </div>
        @endfor
    </div>

    <script>
        function updateLabels() {
            const copies = document.getElementById('copies').value;
            const size = document.getElementById('labelSize').value;
            window.location.href = `?copies=${copies}&size=${size}`;
        }
    </script>
</body>

</html>