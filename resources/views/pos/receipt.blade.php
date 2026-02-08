<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm 297mm;
            /* Auto height usually handled by printer drivers */
        }

        body {
            font-family: 'Tahoma', 'Arial', sans-serif;
            margin: 0;
            padding: 5mm;
            width: 70mm;
            /* 80mm paper - margins */
            font-size: 12px;
            color: #000;
            line-height: 1.4;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: left;
        }

        /* RTL flip */
        .fw-bold {
            font-weight: bold;
        }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 5px 0;
            width: 100%;
        }

        .header {
            margin-bottom: 10px;
        }

        .logo {
            max-width: 80%;
            height: auto;
            margin-bottom: 5px;
            filter: grayscale(100%);
            /* Thermal printers normally are B&W */
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .store-info {
            font-size: 10px;
            margin-bottom: 2px;
        }

        .invoice-details {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .customer-info {
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 10px;
            border-radius: 4px;
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

        .col-item {
            width: 55%;
        }

        .col-price {
            width: 30%;
            text-align: left;
        }

        .totals {
            margin-top: 10px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .total-final {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
        }

        .barcode {
            margin-top: 10px;
        }
    </style>
</head>

<body onload="window.print()">
    <!-- Header -->
    <div class="header text-center">
        <!-- Optional Logo -->
        @if(\App\Models\Setting::getValue('printer_show_logo', true) && \App\Models\Setting::getValue('company_logo'))
            <img src="{{ Storage::url(\App\Models\Setting::getValue('company_logo')) }}" class="logo">
        @endif

        <div class="store-name">{{ \App\Models\Setting::getValue('company_name', config('app.name')) }}</div>
        <div class="store-info">{{ \App\Models\Setting::getValue('company_address', 'العنوان غير محدد') }}</div>
        <div class="store-info">هاتف: {{ \App\Models\Setting::getValue('company_phone', '-') }}</div>
    </div>

    <div class="dashed-line"></div>

    <!-- Invoice Details -->
    <div class="invoice-details">
        <div style="display: flex; justify-content: space-between;">
            <span>رقم الفاتورة:</span>
            <span class="fw-bold">{{ $invoice->invoice_number }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>التاريخ:</span>
            <span>{{ $invoice->created_at->format('Y-m-d H:i') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>الكاشير:</span>
            <span>{{ auth()->user()->name ?? 'Admin' }}</span>
        </div>
    </div>

    <!-- Customer Info -->
    @if($invoice->customer)
        <div class="customer-info">
            <div style="display: flex; justify-content: space-between;">
                <span>العميل:</span>
                <span class="fw-bold">{{ $invoice->customer->name }}</span>
            </div>
            @if($invoice->customer->code !== 'WALK-IN')
                <div style="display: flex; justify-content: space-between; font-size: 10px; margin-top: 2px;">
                    <span>النوع:</span>
                    <span>
                        @php
                            $labels = [
                                'individual' => 'فرد',
                                'company' => 'شركة',
                                'distributor' => 'موزع',
                                'wholesale' => 'جملة',
                                'half_wholesale' => 'نص جملة',
                                'quarter_wholesale' => 'ربع جملة',
                                'vip' => 'VIP'
                            ];
                            echo $labels[$invoice->customer->type] ?? $invoice->customer->type;
                        @endphp
                    </span>
                </div>
            @endif
        </div>
    @endif

    @if($invoice->is_delivery)
        <div class="customer-info" style="margin-top: 5px;">
            <div style="font-weight: bold; margin-bottom: 2px; border-bottom: 1px dashed #ccc;">بيانات التوصيل:</div>

            @if($invoice->shipping_address)
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span>العنوان:</span>
                    <span style="max-width: 70%; text-align: left; font-size: 11px;">{{ $invoice->shipping_address }}</span>
                </div>
            @endif

            @if($invoice->driver)
                <div style="display: flex; justify-content: space-between;">
                    <span>السائق:</span>
                    <span class="fw-bold">{{ $invoice->driver->name }}</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th class="col-item">الصنف</th>
                <th class="col-qty">الكمية</th>
                <th class="col-price">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
                <tr>
                    <td class="col-item">
                        {{ $line->product->name }}
                        @if($line->discount_amount > 0)
                            <div style="font-size: 10px; color: #555;">
                                سعر: {{ number_format($line->unit_price, 2) }}
                                <br>
                                خصم: <span
                                    style="text-decoration: line-through">{{ number_format($line->discount_amount, 2) }}</span>
                            </div>
                        @elseif($line->unit_price != $line->product->selling_price)
                            <div style="font-size: 10px; color: #555;">@ {{ number_format($line->unit_price, 2) }}</div>
                        @endif
                    </td>
                    <td class="col-qty">{{ $line->quantity + 0 }}</td>
                    <td class="col-price">
                        {{ number_format($line->line_total, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="dashed-line"></div>

    <!-- Totals -->
    <div class="totals">
        <div class="totals-row">
            <span>المجموع:</span>
            <span>{{ number_format($invoice->subtotal, 2) }}</span>
        </div>

        @if($invoice->discount_amount > 0)
            <div class="totals-row">
                <span>الخصم:</span>
                <span>-{{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
        @endif

        @if($invoice->is_delivery && $invoice->delivery_fee > 0)
            <div class="totals-row">
                <span>توصيل:</span>
                <span>{{ number_format($invoice->delivery_fee, 2) }}</span>
            </div>
        @endif

        <div class="totals-row">
            <span>ضريبة
                ({{ $invoice->lines->first()->tax_percent ?? \App\Models\Setting::getValue('default_tax_rate', 0) }}%):</span>
            <span>{{ number_format($invoice->tax_amount, 2) }}</span>
        </div>

        <div class="totals-row total-final">
            <span>الإجمالي:</span>
            <span>{{ number_format($invoice->total, 2) }}</span>
        </div>

        @if($invoice->paymentAllocations->isNotEmpty())
            @foreach($invoice->paymentAllocations as $allocation)
                <div class="totals-row" style="margin-top: 2px;">
                    <span>مدفوع ({{ 
                                match ($allocation->payment->payment_method) {
                        'cash' => 'نقد',
                        'card' => 'شبكة',
                        'credit' => 'آجل',
                        default => $allocation->payment->payment_method
                    }
                            }}):</span>
                    <span>{{ number_format($allocation->amount, 2) }}</span>
                </div>
            @endforeach
        @else
            <div class="totals-row" style="margin-top: 5px;">
                <span>المدفوع:</span>
                <span>{{ number_format($invoice->paid_amount, 2) }}</span>
            </div>
        @endif

        @php
            $change = max(0, $invoice->paid_amount - $invoice->total);
            $balance = max(0, $invoice->total - $invoice->paid_amount);
        @endphp

        @if($change > 0)
            <div class="totals-row" style="margin-top:5px; border-top: 1px dotted #000; padding-top:2px;">
                <span>الباقي للعميل:</span>
                <span style="font-weight: bold; font-size: 14px;">{{ number_format($change, 2) }}</span>
            </div>
        @endif

        @if($balance > 0)
            <div class="totals-row">
                <span>المتبقي عليه (آجل):</span>
                <span style="font-weight: bold;">{{ number_format($balance, 2) }}</span>
            </div>
        @endif
    </div>

    <div class="dashed-line"></div>

    <!-- Footer -->
    <div class="footer">
        <p>{{ \App\Models\Setting::getValue('invoice_footer', 'شكراً لتعاملكم معنا!') }}</p>

        <!-- Simplified Barcode Representation -->
        <div class="barcode">
            <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $invoice->invoice_number }}&code=Code128&translate-esc=true&imagetype=Png&hidehrt=false&eclevel=L&dmsize=Default"
                alt="Barcode" style="max-width: 100%; height: 40px;">
        </div>
    </div>
</body>

</html>