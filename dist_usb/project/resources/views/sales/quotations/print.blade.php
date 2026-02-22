<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض سعر #{{ $quotation->quotation_number }}</title>
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
            <h1>{{ \App\Models\Setting::getValue('company_name', config('app.name')) }}</h1>
            <p>{{ \App\Models\Setting::getValue('company_address', 'العنوان غير محدد') }}</p>
            <p>سجل تجاري: - | بطاقة ضريبية: {{ \App\Models\Setting::getValue('company_tax_number', '-') }}</p>
        </div>
        <div class="invoice-details">
            <h2>عرض سعر</h2>
            <p><strong>رقم العرض:</strong> {{ $quotation->quotation_number }}</p>
            <p><strong>التاريخ:</strong> {{ $quotation->quotation_date->format('Y-m-d') }}</p>
            <p><strong>صالح حتى:</strong>
                {{ $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : 'غير محدد' }}</p>
        </div>
    </div>

    <div class="client-info">
        <div>
            <h3 style="margin-top: 0">بيانات العميل</h3>
            @if($quotation->customer)
                <p><strong>الاسم:</strong> {{ $quotation->customer->name }}</p>
                <p><strong>العنوان:</strong> {{ $quotation->customer->address ?? '-' }}</p>
                <p><strong>الهاتف:</strong> {{ $quotation->customer->phone ?? '-' }}</p>
            @else
                <p><strong>الفئة المستهدفة:</strong> {{ $quotation->target_customer_type_label }}</p>
                <p style="color: #666; font-size: 0.9em;">(هذا العرض ساري لجميع عملاء هذه الفئة)</p>
            @endif
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
            @foreach($quotation->lines as $index => $line)
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
            <span>{{ number_format($quotation->subtotal, 2) }}</span>
        </div>
        @if($quotation->discount_amount > 0)
            <div class="totals-row">
                <span>خصم إضافي:</span>
                <span>-{{ number_format($quotation->discount_amount, 2) }}</span>
            </div>
        @endif
        <div class="totals-row">
            <span>الضريبة ({{ number_format(\App\Models\Setting::getValue('default_tax_rate', 14), 0) }}%):</span>
            <span>{{ number_format($quotation->tax_amount, 2) }}</span>
        </div>
        <div class="totals-row final">
            <span>الإجمالي:</span>
            <span>{{ number_format($quotation->total, 2) }} EGP</span>
        </div>
    </div>

    @if($quotation->notes || $quotation->terms)
        <div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
            @if($quotation->notes)
                <div style="margin-bottom: 20px;">
                    <strong>ملاحظات:</strong>
                    <p style="margin: 5px 0; color: #555;">{{ $quotation->notes }}</p>
                </div>
            @endif

            @if($quotation->terms)
                <div>
                    <strong>الشروط والأحكام:</strong>
                    <p style="margin: 5px 0; color: #555;">{{ $quotation->terms }}</p>
                </div>
            @endif
        </div>
    @endif

    <div class="footer">
        <p>تم إصدار هذا العرض إلكترونياً ولا يحتاج إلى توقيع في حالة عدم وجود أختام.</p>
        <p>شكراً لتعاملكم معنا!</p>
    </div>

</body>

</html>