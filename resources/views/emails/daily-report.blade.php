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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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

        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }

        .stat-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .stat-card h3 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }

        .stat-card p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }

        .stat-card.success h3 {
            color: #28a745;
        }

        .stat-card.primary h3 {
            color: #007bff;
        }

        .stat-card.warning h3 {
            color: #ffc107;
        }

        .stat-card.danger h3 {
            color: #dc3545;
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
            background: #11998e;
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
            <h1>📊 التقرير اليومي</h1>
            <p>{{ $date }} - Twinx ERP</p>
        </div>

        <div class="content">
            <h3>ملخص المبيعات</h3>
            <div class="stat-grid">
                <div class="stat-card success">
                    <h3>{{ number_format($stats['sales_total'] ?? 0, 2) }}</h3>
                    <p>إجمالي المبيعات (ج.م)</p>
                </div>
                <div class="stat-card primary">
                    <h3>{{ $stats['sales_count'] ?? 0 }}</h3>
                    <p>عدد الفواتير</p>
                </div>
                <div class="stat-card warning">
                    <h3>{{ number_format($stats['cash_collected'] ?? 0, 2) }}</h3>
                    <p>النقد المحصل (ج.م)</p>
                </div>
                <div class="stat-card danger">
                    <h3>{{ $stats['new_customers'] ?? 0 }}</h3>
                    <p>عملاء جدد</p>
                </div>
            </div>

            @if(isset($stats['top_products']) && count($stats['top_products']) > 0)
                <h3>أفضل المنتجات مبيعاً</h3>
                <table>
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>الإيرادات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['top_products'] as $product)
                            <tr>
                                <td>{{ $product['name'] }}</td>
                                <td>{{ $product['quantity'] }}</td>
                                <td>{{ number_format($product['revenue'], 2) }} ج.م</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if(isset($stats['low_stock_count']) && $stats['low_stock_count'] > 0)
                <div style="background: #fff3cd; border-radius: 8px; padding: 15px; margin: 20px 0;">
                    <strong>⚠️ تنبيه:</strong> يوجد {{ $stats['low_stock_count'] }} منتج منخفض المخزون
                </div>
            @endif

            <p style="text-align: center;">
                <a href="{{ url('/dashboard') }}" class="btn">عرض لوحة التحكم</a>
            </p>
        </div>

        <div class="footer">
            <p>تقرير يومي تلقائي من نظام Twinx ERP</p>
            <p>يتم إرسال هذا التقرير يومياً في نهاية اليوم</p>
        </div>
    </div>
</body>

</html>