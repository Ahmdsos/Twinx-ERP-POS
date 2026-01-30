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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .invoice-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .invoice-info p {
            margin: 8px 0;
        }

        .invoice-info strong {
            color: #333;
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

        .total-row {
            background: #e8f5e9;
            font-weight: bold;
            font-size: 18px;
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
            background: #667eea;
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
            <h1>🧾 فاتورة مبيعات</h1>
            <p>Twinx ERP</p>
        </div>

        <div class="content">
            <div class="invoice-info">
                <p><strong>رقم الفاتورة:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>التاريخ:</strong> {{ $invoice->invoice_date }}</p>
                <p><strong>العميل:</strong> {{ $customer->name ?? 'عميل نقدي' }}</p>
                @if($customer?->phone)
                    <p><strong>الهاتف:</strong> {{ $customer->phone }}</p>
                @endif
            </div>

            <h3>تفاصيل الفاتورة</h3>
            <table>
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lines as $line)
                        <tr>
                            <td>{{ $line->product?->name ?? 'منتج' }}</td>
                            <td>{{ $line->quantity }}</td>
                            <td>{{ number_format($line->unit_price, 2) }}</td>
                            <td>{{ number_format($line->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3">الإجمالي</td>
                        <td>{{ number_format($invoice->total_amount, 2) }} ج.م</td>
                    </tr>
                </tbody>
            </table>

            @if($invoice->paid_amount > 0)
                <p><strong>المدفوع:</strong> {{ number_format($invoice->paid_amount, 2) }} ج.م</p>
                <p><strong>المتبقي:</strong> {{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }} ج.م</p>
            @endif
        </div>

        <div class="footer">
            <p>شكراً لتعاملكم معنا - Twinx ERP System</p>
            <p>هذا البريد الإلكتروني تم إرساله تلقائياً</p>
        </div>
    </div>
</body>

</html>