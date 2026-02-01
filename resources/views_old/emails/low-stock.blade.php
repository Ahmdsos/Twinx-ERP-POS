<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            direction: rtl;
            text-align: right;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 30px;
        }

        .alert-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .critical {
            background: #ffebee;
            color: #c62828;
        }

        .warning {
            background: #fff3e0;
            color: #e65100;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #666;
            font-size: 12px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #ff7e5f;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ تنبيه انخفاض المخزون</h1>
            <p>Twinx ERP</p>
        </div>

        <div class="content">
            <div class="alert-box">
                <strong>تنبيه:</strong> يوجد <strong>{{ $count }}</strong> منتج وصل إلى الحد الأدنى للمخزون ويحتاج إلى
                إعادة طلب.
            </div>

            <h3>المنتجات منخفضة المخزون</h3>
            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>المنتج</th>
                        <th>المخزون الحالي</th>
                        <th>الحد الأدنى</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        @php
                            $stock = $product->getTotalStock();
                            $isCritical = $stock == 0;
                        @endphp
                        <tr class="{{ $isCritical ? 'critical' : 'warning' }}">
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $stock }}</td>
                            <td>{{ $product->min_stock }}</td>
                            <td>{{ $isCritical ? '🚨 نفد' : '⚠️ منخفض' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p style="text-align: center;">
                <a href="{{ url('/products') }}" class="btn">عرض المنتجات</a>
            </p>
        </div>

        <div class="footer">
            <p>هذا التنبيه تلقائي من نظام Twinx ERP</p>
            <p>{{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>
</body>

</html>