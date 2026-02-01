<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $salesInvoice->invoice_number }} - Twinx ERP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #fff;
            direction: rtl;
        }

        .invoice {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }

        .company-info h1 {
            font-size: 28px;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .company-info p {
            color: #666;
        }

        .invoice-info {
            text-align: left;
        }

        .invoice-info h2 {
            font-size: 24px;
            color: #333;
        }

        .invoice-info .invoice-number {
            font-size: 18px;
            color: #2563eb;
            margin: 5px 0;
        }

        .invoice-info .dates {
            font-size: 13px;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-partial {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .customer-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .customer-box,
        .invoice-to {
            width: 48%;
        }

        .section-title {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .customer-box h3,
        .invoice-to h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background: #f3f4f6;
            padding: 12px;
            text-align: right;
            border-bottom: 2px solid #ddd;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .items-table .text-left {
            text-align: left;
        }

        .items-table .total-row td {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 16px;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .totals-box {
            width: 300px;
        }

        .totals-box .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .totals-box .total-final {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
            padding: 12px 0;
        }

        .payment-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .payment-section h4 {
            margin-bottom: 15px;
        }

        .payment-info {
            display: flex;
            justify-content: space-between;
        }

        .payment-box {
            text-align: center;
        }

        .payment-box .amount {
            font-size: 24px;
            font-weight: bold;
        }

        .payment-box .label {
            font-size: 12px;
            color: #666;
        }

        .amount-paid {
            color: #059669;
        }

        .amount-due {
            color: #dc2626;
        }

        .terms-section {
            border-top: 1px solid #eee;
            padding-top: 20px;
            font-size: 12px;
            color: #666;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #888;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .invoice {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="invoice">
        <!-- Print Button (no-print) -->
        <div class="no-print" style="text-align: center; margin-bottom: 20px;">
            <button onclick="window.print()"
                style="padding: 10px 30px; font-size: 16px; cursor: pointer; background: #2563eb; color: white; border: none; border-radius: 5px;">
                🖨️ طباعة الفاتورة
            </button>
            <button onclick="window.close()"
                style="padding: 10px 30px; font-size: 16px; cursor: pointer; background: #6b7280; color: white; border: none; border-radius: 5px; margin-right: 10px;">
                ✖ إغلاق
            </button>
        </div>

        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>Twinx ERP</h1>
                <p>نظام إدارة موارد المؤسسات</p>
                <p>القاهرة، مصر</p>
            </div>
            <div class="invoice-info">
                <h2>فـاتـورة</h2>
                <div class="invoice-number">{{ $salesInvoice->invoice_number }}</div>
                <div class="dates">
                    <div>تاريخ الفاتورة: {{ $salesInvoice->invoice_date?->format('Y-m-d') }}</div>
                    <div>تاريخ الاستحقاق: {{ $salesInvoice->due_date?->format('Y-m-d') }}</div>
                </div>
                @php
                    $statusClass = match ($salesInvoice->status->value) {
                        'pending' => 'status-pending',
                        'partial' => 'status-partial',
                        'paid' => 'status-paid',
                        default => ''
                    };
                @endphp
                <span class="status-badge {{ $statusClass }}">
                    {{ $salesInvoice->status->label() }}
                </span>
            </div>
        </div>

        <!-- Customer Section -->
        <div class="customer-section">
            <div class="customer-box">
                <div class="section-title">فاتورة إلى</div>
                <h3>{{ $salesInvoice->customer?->name }}</h3>
                <p>{{ $salesInvoice->customer?->code }}</p>
                @if($salesInvoice->customer?->billing_address)
                    <p>{{ $salesInvoice->customer?->billing_address }}</p>
                @endif
                @if($salesInvoice->customer?->phone)
                    <p>هاتف: {{ $salesInvoice->customer?->phone }}</p>
                @endif
            </div>
            <div class="invoice-to">
                <div class="section-title">مستندات مرتبطة</div>
                @if($salesInvoice->salesOrder)
                    <p>أمر البيع: {{ $salesInvoice->salesOrder?->so_number }}</p>
                @endif
                @if($salesInvoice->deliveryOrder)
                    <p>أمر التسليم: {{ $salesInvoice->deliveryOrder?->do_number }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th class="text-left">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesInvoice->lines as $index => $line)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $line->product?->name }}</strong>
                            <br>
                            <small>{{ $line->product?->sku }}</small>
                        </td>
                        <td>{{ number_format($line->quantity, 2) }} {{ $line->product?->unit?->abbreviation ?? '' }}</td>
                        <td>{{ number_format($line->unit_price, 2) }} ج.م</td>
                        <td class="text-left">{{ number_format($line->line_total, 2) }} ج.م</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="row">
                    <span>الإجمالي الفرعي:</span>
                    <span>{{ number_format($salesInvoice->subtotal, 2) }} ج.م</span>
                </div>
                @if($salesInvoice->discount_amount > 0)
                    <div class="row">
                        <span>الخصم:</span>
                        <span>-{{ number_format($salesInvoice->discount_amount, 2) }} ج.م</span>
                    </div>
                @endif
                @if($salesInvoice->tax_amount > 0)
                    <div class="row">
                        <span>الضريبة:</span>
                        <span>{{ number_format($salesInvoice->tax_amount, 2) }} ج.م</span>
                    </div>
                @endif
                <div class="row total-final">
                    <span>الإجمالي:</span>
                    <span>{{ number_format($salesInvoice->total, 2) }} ج.م</span>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="payment-section">
            <h4>حالة الدفع</h4>
            <div class="payment-info">
                <div class="payment-box">
                    <div class="amount">{{ number_format($salesInvoice->total, 2) }}</div>
                    <div class="label">إجمالي الفاتورة</div>
                </div>
                <div class="payment-box">
                    <div class="amount amount-paid">{{ number_format($salesInvoice->paid_amount, 2) }}</div>
                    <div class="label">المدفوع</div>
                </div>
                <div class="payment-box">
                    <div class="amount amount-due">{{ number_format($salesInvoice->balance_due, 2) }}</div>
                    <div class="label">المتبقي</div>
                </div>
            </div>
        </div>

        <!-- Terms -->
        @if($salesInvoice->terms)
            <div class="terms-section">
                <strong>شروط الدفع:</strong>
                <p>{{ $salesInvoice->terms }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>شكراً لتعاملكم معنا</p>
            <p>Twinx ERP - نظام إدارة موارد المؤسسات</p>
        </div>
    </div>
</body>

</html>