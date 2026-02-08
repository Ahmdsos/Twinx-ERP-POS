<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال توصيل #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm 297mm;
        }

        body {
            font-family: 'Tahoma', 'Arial', sans-serif;
            margin: 0;
            padding: 5mm;
            width: 70mm;
            font-size: 12px;
            color: #000;
            line-height: 1.4;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 10px 0;
            width: 100%;
        }

        .header {
            margin-bottom: 10px;
            text-align: center;
        }

        .logo {
            max-width: 80%;
            height: auto;
            margin-bottom: 5px;
            filter: grayscale(100%);
        }

        .delivery-badge {
            border: 2px solid #000;
            padding: 5px;
            margin: 10px 0;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .customer-box {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            border-bottom: 1px solid #000;
            text-align: right;
            padding: 2px 0;
            font-size: 11px;
        }

        td {
            padding: 3px 0;
            vertical-align: top;
        }

        .col-qty {
            width: 15%;
            text-align: center;
        }

        .col-price {
            width: 25%;
            text-align: left;
        }

        .totals {
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .grand-total {
            font-weight: bold;
            font-size: 16px;
            border-top: 1px dashed #000;
            margin-top: 5px;
            padding-top: 5px;
        }

        .signatures {
            margin-top: 30px;
        }

        .sig-line {
            border-top: 1px solid #000;
            width: 100%;
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        @if(\App\Models\Setting::getValue('company_logo'))
            <img src="{{ asset('storage/' . \App\Models\Setting::getValue('company_logo')) }}" class="logo" alt="Logo">
        @endif
        <div style="font-size: 16px; font-weight: bold;">
            {{ \App\Models\Setting::getValue('company_name', config('app.name')) }}</div>
        <div style="font-size: 10px;">{{ \App\Models\Setting::getValue('company_address', '') }}</div>
        <div style="font-size: 10px;">{{ \App\Models\Setting::getValue('company_phone', '') }}</div>
    </div>

    <div class="delivery-badge">DELIVERY ORDER<br>توصيل</div>

    <div class="info-row">
        <span>رقم الفاتورة:</span> <span class="fw-bold">{{ $invoice->invoice_number }}</span>
    </div>
    <div class="info-row">
        <span>التاريخ:</span> <span>{{ $invoice->created_at->format('Y-m-d H:i') }}</span>
    </div>
    <div class="info-row">
        <span>الكاشير:</span> <span>{{ $invoice->creator->name ?? 'Admin' }}</span>
    </div>

    <div class="dashed-line"></div>

    <!-- Enhanced Customer Info -->
    <div class="customer-box">
        <div style="font-weight: bold; border-bottom: 1px dashed #ccc; margin-bottom: 5px;">بيانات العميل</div>
        <div class="info-row">
            <span>الاسم:</span> <span class="fw-bold">{{ $invoice->customer->name }}</span>
        </div>
        @if($invoice->customer->phone && $invoice->customer->phone !== 'N/A')
            <div class="info-row">
                <span>الهاتف:</span> <span>{{ $invoice->customer->phone }}</span>
            </div>
        @endif

        <div style="margin-top: 5px; font-weight: bold; font-size: 11px;">العنوان:</div>
        <div style="font-size: 11px; margin-bottom: 5px;">
            {{ $invoice->shipping_address ?? $invoice->customer->address ?? 'لا يوجد عنوان مسجل' }}
        </div>

        @if($invoice->driver)
            <div style="border-top: 1px dashed #ccc; margin-top: 5px; padding-top: 5px;">
                <div class="info-row">
                    <span>السائق:</span> <span class="fw-bold">{{ $invoice->driver->name }}</span>
                </div>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>الصنف</th>
                <th class="col-qty">الكمية</th>
                <th class="col-price">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
                <tr>
                    <td>{{ $line->product->name }}</td>
                    <td class="col-qty">{{ $line->quantity + 0 }}</td>
                    <td class="col-price">{{ number_format($line->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <span>المجموع الفرعي:</span>
            <span>{{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        @if($invoice->discount_amount > 0)
            <div class="total-row">
                <span>الخصم:</span>
                <span>-{{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
        @endif
        @if($invoice->tax_amount > 0)
            <div class="total-row">
                <span>الضريبة:</span>
                <span>{{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
        @endif
        @if($invoice->delivery_fee > 0)
            <div class="total-row" style="font-weight: bold;">
                <span>رسوم التوصيل:</span>
                <span>{{ number_format($invoice->delivery_fee, 2) }}</span>
            </div>
        @endif

        <div class="total-row grand-total">
            <span>الإجمالي:</span>
            <span>{{ number_format($invoice->total, 2) }}</span>
        </div>
    </div>

    <div class="signatures">
        <div style="float: right; width: 45%;">
            <div class="sig-line">توقيع المستلم</div>
        </div>
        <div style="float: left; width: 45%;">
            <div class="sig-line">توقيع السائق</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 10px;">
        *** شكراً لتعاملكم معنا ***
    </div>

</body>

</html>