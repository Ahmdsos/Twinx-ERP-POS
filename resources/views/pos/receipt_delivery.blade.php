<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال توصيل #{{ $invoice->invoice_number }}</title>
    <!-- Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }

        body {
            font-family: 'Cairo', 'Tahoma', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            background: #fff;
            display: flex;
            justify-content: center;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
        }

        .container {
            width: 76mm;
            padding: 2mm 3mm;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: left;
        }

        .fw-bold {
            font-weight: 700;
        }

        .divider {
            height: 1px;
            background: repeating-linear-gradient(to right, #000 0, #000 3px, transparent 3px, transparent 6px);
            margin: 8px 0;
        }

        .header {
            margin-bottom: 12px;
        }

        .logo {
            max-width: 50mm;
            height: auto;
            margin-bottom: 8px;
            filter: grayscale(100%) contrast(1.2);
        }

        .badge-delivery {
            display: inline-block;
            padding: 5px 20px;
            border: 2px solid #000;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 900;
            margin: 10px 0;
            background: #fdfdfd;
        }

        .customer-card {
            border: 1.5px solid #000;
            padding: 8px;
            margin: 10px 0;
            border-radius: 6px;
            background: #fff;
        }

        .card-title {
            font-weight: 700;
            font-size: 11px;
            border-bottom: 1px dashed #000;
            margin-bottom: 6px;
            padding-bottom: 4px;
            text-align: center;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 10.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            border-bottom: 2px solid #000;
            text-align: right;
            padding: 5px 0;
        }

        td {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
        }

        .grand-total-box {
            background: #000;
            color: #fff;
            padding: 8px;
            margin-top: 10px;
            border-radius: 4px;
        }

        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 10px;
        }

        #qrcode canvas,
        #qrcode img {
            width: 100px !important;
            height: 100px !important;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9.5px;
            opacity: 0.7;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body onload="processReceipt()">
    <div class="container">
        <!-- Header -->
        <div class="header">
            @if(\App\Models\Setting::getValue('printer_show_logo', true) && \App\Models\Setting::getValue('company_logo'))
                <div class="text-center">
                    <img src="{{ Storage::url(\App\Models\Setting::getValue('company_logo')) }}" class="logo">
                </div>
            @endif

            <div class="text-center">
                <div style="font-size: 18px; font-weight: 700;">
                    {{ \App\Models\Setting::getValue('company_name', 'Twinx ERP') }}
                </div>

                @php
                    $address = \App\Models\Setting::getValue('company_address');
                    $phone = \App\Models\Setting::getValue('company_phone');
                    $email = \App\Models\Setting::getValue('company_email');
                    $taxNumber = \App\Models\Setting::getValue('company_tax_number');
                @endphp

                <div style="font-size: 10px; margin-top: 2px; opacity: 0.8;">
                    @if($address)
                    <div>{{ $address }}</div> @endif
                    @if($phone || $email)
                        <div>
                            @if($phone) <span>الهاتف: {{ $phone }}</span> @endif
                            @if($phone && $email) <span> | </span> @endif
                            @if($email) <span>{{ $email }}</span> @endif
                        </div>
                    @endif
                    @if($taxNumber)
                    <div>الرقم الضريبي: {{ $taxNumber }}</div> @endif
                </div>
            </div>
        </div>

        <div class="text-center">
            <div class="badge-delivery">
                🛵 إيصال توصيل منزلي
            </div>
        </div>

        <div class="info-row">
            <span>رقم الفاتورة:</span> <span class="fw-bold">{{ $invoice->invoice_number }}</span>
        </div>
        <div class="info-row">
            <span>التاريخ والوقت:</span> <span>{{ $invoice->created_at->format('Y-m-d H:i') }}</span>
        </div>

        <div class="divider"></div>

        <!-- Recipient Card -->
        <div class="customer-card">
            <div class="card-title">بيانات المستسلم والوجهة</div>
            <div class="info-row">
                <span>اسم العميل:</span>
                <span class="fw-bold">{{ $invoice->deliveryOrder->recipient_name ?? $invoice->customer->name }}</span>
            </div>
            @if(($invoice->deliveryOrder->recipient_phone ?? $invoice->customer->phone))
                <div class="info-row">
                    <span>رقم الهاتف:</span>
                    <span class="fw-bold">{{ $invoice->deliveryOrder->recipient_phone ?? $invoice->customer->phone }}</span>
                </div>
            @endif
            <div style="margin-top: 6px;">
                <span class="fw-bold" style="font-size: 10px;">العنوان:</span>
                <div style="font-size: 10.5px; line-height: 1.2; margin-top: 2px;">
                    {{ $invoice->shipping_address ?? $invoice->customer->address ?? 'لا يوجد عنوان مسجل' }}
                </div>
            </div>
            @if($invoice->driver)
                <div style="border-top: 1px dashed #ccc; margin-top: 8px; padding-top: 6px;">
                    <div class="info-row">
                        <span>السائق المسؤول:</span>
                        <span class="fw-bold">{{ $invoice->driver->name }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 60%;">الصنف</th>
                    <th style="width: 15%; text-align: center;">الكمية</th>
                    <th style="width: 25%; text-align: left;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lines as $line)
                    <tr>
                        <td class="fw-bold">{{ $line->product->name }}</td>
                        <td class="text-center">{{ $line->quantity + 0 }}</td>
                        <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div style="margin-top: 10px;">
            @php
                $grossItemsTotal = $invoice->lines->sum(fn($l) => $l->quantity * $l->unit_price);
                $lineDiscounts = $invoice->lines->sum('discount_amount');
                $globalDiscount = $invoice->discount_amount ?? 0;
                $totalDiscount = $lineDiscounts + $globalDiscount;
            @endphp

            <div class="info-row">
                <span>إجمالي الأصناف:</span>
                <span>{{ number_format($grossItemsTotal, 2) }}</span>
            </div>

            @if($totalDiscount > 0)
                <div class="info-row" style="color: #c00;">
                    <span>إجمالي الخصومات (-):</span>
                    <span>{{ number_format($totalDiscount, 2) }}</span>
                </div>
            @endif

            @if($invoice->tax_amount > 0)
                <div class="info-row">
                    <span>الضريبة ({{ \App\Models\Setting::getValue('default_tax_rate', 14) }}%):</span>
                    <span>{{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
            @endif

            @if($invoice->delivery_fee > 0)
                <div class="info-row">
                    <span>رسوم التوصيل (+):</span>
                    <span>{{ number_format($invoice->delivery_fee, 2) }}</span>
                </div>
            @endif

            <div class="grand-total-box">
                <div class="info-row" style="margin-bottom: 0; color: #fff;">
                    <span style="font-size: 14px; font-weight: 700;">المطلوب سداده</span>
                    <span style="font-size: 18px; font-weight: 700;">{{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>

            @php
                $balance = max(0, $invoice->total - $invoice->paid_amount);
            @endphp
            @if($balance > 0)
                <div class="info-row"
                    style="margin-top: 8px; color: #d00; border: 1px dashed #d00; padding: 5px; border-radius: 4px;">
                    <span class="fw-bold">المتبقي (تحصيل نقدي):</span>
                    <span class="fw-bold" style="font-size: 14px;">{{ number_format($balance, 2) }}</span>
                </div>
            @endif
        </div>

        <div class="footer">
            <div class="divider"></div>

            @php
                $footerCustom = \App\Models\Setting::getValue('pos_receipt_footer_custom');
                $invoiceFooter = \App\Models\Setting::getValue('invoice_footer');
            @endphp

            @if($footerCustom)
                <div style="font-weight: 700; margin-bottom: 5px;">{{ $footerCustom }}</div>
            @endif

            @if($invoiceFooter)
                <div>{{ $invoiceFooter }}</div>
            @endif

            @if(!$footerCustom && !$invoiceFooter)
                *** نشكركم على طلبكم ونأمل أن تحوز الخدمة رضاكم ***
            @endif

            @if(\App\Models\Setting::getValue('pos_receipt_qr_enabled', true))
                <div class="qr-section">
                    <div id="qrcode"></div>
                </div>
            @endif

            <div style="margin-top: 10px; opacity: 0.5;">Twinx Delivery System</div>
        </div>
    </div>

    <script>
        function processReceipt() {
            @if(\App\Models\Setting::getValue('pos_receipt_qr_enabled', true))
                @php
                    $customQrLink = \App\Models\Setting::getValue('pos_receipt_qr_link');
                    if (!$customQrLink) {
                        try {
                            $customQrLink = route('sales-invoices.show', $invoice->id);
                        } catch (\Exception $e) {
                            $customQrLink = url("/sales-invoices/{$invoice->id}");
                        }
                    }
                @endphp

                new QRCode(document.getElementById("qrcode"), {
                    text: "{!! $customQrLink !!}",
                    width: 100,
                    height: 100,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            @endif

            setTimeout(() => { window.print(); }, 500);
        }
    </script>
</body>

</html>