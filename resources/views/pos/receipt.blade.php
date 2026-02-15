<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة #{{ $invoice->invoice_number }}</title>
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

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            margin-bottom: 10px;
            font-size: 9.5px;
        }

        .meta-item {
            padding: 3px 5px;
            background: #f9f9f9;
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th {
            border-bottom: 2px solid #000;
            text-align: right;
            padding: 5px 0;
            font-size: 11px;
        }

        td {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }

        .grand-total-box {
            background: #000;
            color: #fff;
            padding: 8px;
            margin-top: 10px;
            border-radius: 4px;
        }

        .grand-total-box span {
            font-size: 18px;
            font-weight: 700;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
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
    </style>
    <!-- QR Code Library -->
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
                    {{ \App\Models\Setting::getValue('company_name', 'Twinx ERP') }}</div>

                @php
                    $headerCustom = \App\Models\Setting::getValue('pos_receipt_header_custom');
                    $address = \App\Models\Setting::getValue('company_address');
                    $phone = \App\Models\Setting::getValue('company_phone');
                    $email = \App\Models\Setting::getValue('company_email');
                    $taxNumber = \App\Models\Setting::getValue('company_tax_number');
                @endphp

                @if($headerCustom)
                    <div style="font-size: 12px; font-weight: 700; margin-top: 2px;">{{ $headerCustom }}</div>
                @endif

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

        <div class="divider"></div>

        <!-- Order Information Meta -->
        <div class="meta-grid">
            <div class="meta-item"><b>الرقم:</b> {{ $invoice->invoice_number }}</div>
            <div class="meta-item"><b>الوقت:</b> {{ $invoice->created_at->format('Y-m-d H:i') }}</div>
            <div class="meta-item"><b>الكاشير:</b> {{ $invoice->creator->name ?? 'Admin' }}</div>
        </div>

        @if($invoice->is_delivery)
            <div class="text-center">
                <div
                    style="display: inline-block; padding: 2px 12px; border: 2px solid #000; border-radius: 30px; font-size: 11px; font-weight: 700; margin-bottom: 10px;">
                    🛵 فاتورة توصيل
                </div>
            </div>
        @endif

        <div class="divider"></div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">الصنف</th>
                    <th style="width: 15%; text-align: center;">الكمية</th>
                    <th style="width: 15%; text-align: center;">السعر</th>
                    <th style="width: 20%; text-align: left;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lines as $line)
                    <tr>
                        <td class="fw-bold">{{ $line->product->name }}</td>
                        <td class="text-center">{{ $line->quantity + 0 }}</td>
                        <td class="text-center">{{ number_format($line->unit_price, 2) }}</td>
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

            <div class="summary-row">
                <span>إجمالي الأصناف:</span>
                <span>{{ number_format($grossItemsTotal, 2) }}</span>
            </div>

            @if($totalDiscount > 0)
                <div class="summary-row" style="color: #c00;">
                    <span>إجمالي الخصومات (-):</span>
                    <span>{{ number_format($totalDiscount, 2) }}</span>
                </div>
            @endif

            @if($invoice->tax_amount > 0)
                <div class="summary-row">
                    <span>الضريبة ({{ \App\Models\Setting::getValue('default_tax_rate', 14) }}%):</span>
                    <span>{{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
            @endif

            @if($invoice->delivery_fee > 0)
                <div class="summary-row">
                    <span>رسوم التوصيل (+):</span>
                    <span>{{ number_format($invoice->delivery_fee, 2) }}</span>
                </div>
            @endif

            <div class="grand-total-box">
                <div class="summary-row" style="margin-bottom: 0;">
                    <span style="font-size: 14px;">الإجمالي النهائي</span>
                    <span>{{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>

            <div style="margin-top: 8px;">
                @if($invoice->paid_amount > 0)
                    <div class="summary-row">
                        <span>المبلغ المدفوع:</span>
                        <span>{{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                @endif

                @php
                    $change = max(0, $invoice->paid_amount - $invoice->total);
                    $balance = max(0, $invoice->total - $invoice->paid_amount);
                @endphp

                @if($change > 0)
                    <div class="summary-row" style="font-weight: 700;">
                        <span>المتبقي للعميل:</span>
                        <span>{{ number_format($change, 2) }}</span>
                    </div>
                @endif

                @if($balance > 0)
                    <div class="summary-row"
                        style="color: #d00; font-weight: 700; border: 1px dashed #d00; padding: 4px; border-radius: 4px; margin-top: 5px;">
                        <span>المتبقي (تحصيل):</span>
                        <span class="fs-lg">{{ number_format($balance, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="divider"></div>

            @php
                $footerCustom = \App\Models\Setting::getValue('pos_receipt_footer_custom');
                $invoiceFooter = \App\Models\Setting::getValue('invoice_footer');
            @endphp

            @if($footerCustom)
                <div style="font-weight: 700; margin-bottom: 5px; font-size: 12px;">{{ $footerCustom }}</div>
            @endif

            @if($invoiceFooter)
                <div style="font-size: 9.5px; opacity: 0.8; font-style: italic;">{{ $invoiceFooter }}</div>
            @endif

            @if(!$footerCustom && !$invoiceFooter)
                <div style="font-size: 10px; opacity: 0.8;">*** شكراً لزيارتكم ***</div>
            @endif

            <div class="text-center" style="margin: 15px 0;">
                @php
                    $barcodeService = app(\App\Services\BarcodeService::class);
                    $barcodeSvg = $barcodeService->generateBarcodeSvg($invoice->invoice_number, 'C128', 1, 25);
                @endphp
                {!! $barcodeSvg !!}
            </div>

            @if(\App\Models\Setting::getValue('pos_receipt_qr_enabled', true))
                <div class="qr-section">
                    <div id="qrcode"></div>
                </div>
            @endif

            <div style="font-size: 8px; margin-top: 15px; opacity: 0.4;">
                Twinx ERP v1.0 • {{ now()->format('Y') }}
            </div>
        </div>
    </div>

    <script>
        function processReceipt() {
            // Generate QR Code if enabled
            @if(\App\Models\Setting::getValue('pos_receipt_qr_enabled', true))
                @php
                    $customQrLink = \App\Models\Setting::getValue('pos_receipt_qr_link');
                    // Primary goal: use user-defined link. Fallback: Invoice URL
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

            // Trigger Print
            setTimeout(() => {
                window.print();
                // Close window after print if it was a popup (optional)
                // window.close();
            }, 500);
        }
    </script>
</body>

</html>