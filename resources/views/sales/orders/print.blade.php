<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أمر بيع #{{ $salesOrder->so_number }}</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #fff;
            color: #000;
            padding: 40px;
            font-size: 14px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-info h1 {
            color: #2563eb;
            margin: 0 0 5px;
        }

        .invoice-details {
            text-align: left;
        }

        .invoice-details h2 {
            margin: 0 0 10px;
            color: #333;
        }

        .client-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th {
            background: #2563eb;
            color: white;
            padding: 12px;
            text-align: right;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .totals {
            width: 300px;
            margin-right: auto;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .totals-row.final {
            border-bottom: 2px solid #000;
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 10px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        @media print {
            body {
                padding: 0;
            }

            button {
                display: none;
            }
        }
    </style>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <button onclick="window.print()"
        style="position: fixed; top: 20px; left: 20px; padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: 'Cairo';">
        🖨️ طباعة
    </button>

    <div class="header">
        <div class="company-info">
            <h1>Twinx ERP</h1>
            <p>123 شارع الشركات، القاهرة</p>
            <p>سجل تجاري: 123456 | بطاقة ضريبية: 789-456-123</p>
        </div>
        <div class="invoice-details">
            <h2>أمر بيع (Sales Order)</h2>
            <p><strong>رقم الأمر:</strong> {{ $salesOrder->so_number }}</p>
            <p><strong>التاريخ:</strong> {{ $salesOrder->order_date->format('Y-m-d') }}</p>
            <p><strong>تاريخ التسليم:</strong>
                {{ $salesOrder->expected_date ? $salesOrder->expected_date->format('Y-m-d') : 'غير محدد' }}</p>
        </div>
    </div>

    <div class="client-info">
        <div>
            <h3 style="margin-top: 0">بيانات العميل</h3>
            <p><strong>الاسم:</strong> {{ $salesOrder->customer->name }}</p>
            <p><strong>العنوان:</strong> {{ $salesOrder->customer->address ?? '-' }}</p>
            <p><strong>الهاتف:</strong> {{ $salesOrder->customer->phone ?? '-' }}</p>
        </div>
        <div>
            <h3 style="margin-top: 0">بيانات الشحن</h3>
            <p><strong>العنوان:</strong> {{ $salesOrder->shipping_address ?? 'استلام من المقر' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 40%">البيان</th>
                <th style="width: 10%; text-align: center">الكمية</th>
                <th style="width: 15%; text-align: center">السعر</th>
                <th style="width: 15%; text-align: center">الخصم</th>
                <th style="width: 15%; text-align: right">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesOrder->lines as $index => $line)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <b>{{ $line->description }}</b>
                        @if($line->product->code)
                            <br><small style="color: #666">{{ $line->product->code }}</small>
                        @endif
                    </td>
                    <td style="text-align: center">{{ $line->quantity + 0 }} {{ $line->unit->name ?? '' }}</td>
                    <td style="text-align: center">{{ number_format($line->unit_price, 2) }}</td>
                    <td style="text-align: center">{{ $line->discount_percent + 0 }}%</td>
                    <td style="text-align: right">{{ number_format($line->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>المجموع الفرعي:</span>
            <span>{{ number_format($salesOrder->subtotal, 2) }}</span>
        </div>
        @if($salesOrder->discount_amount > 0)
            <div class="totals-row">
                <span>خصم إضافي:</span>
                <span>-{{ number_format($salesOrder->discount_amount, 2) }}</span>
            </div>
        @endif
        <div class="totals-row">
            <span>الضريبة ({{ number_format(\App\Models\Setting::getValue('default_tax_rate', 14), 0) }}%):</span>
            <span>{{ number_format($salesOrder->tax_amount, 2) }}</span>
        </div>
        <div class="totals-row final">
            <span>الإجمالي:</span>
            <span>{{ number_format($salesOrder->total, 2) }} EGP</span>
        </div>
    </div>

    @if($salesOrder->notes)
        <div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
            <div style="margin-bottom: 20px;">
                <strong>ملاحظات:</strong>
                <p style="margin: 5px 0; color: #555;">{{ $salesOrder->notes }}</p>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>مستند داخلي - قسم المبيعات</p>
    </div>

</body>

</html>