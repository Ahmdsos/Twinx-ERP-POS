<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة شراء - {{ $purchaseInvoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', 'Tahoma', sans-serif;
        }

        body {
            background: #fff;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }

        .invoice {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-info h1 {
            font-size: 28px;
            color: #e74c3c;
            margin-bottom: 5px;
        }

        .company-info p {
            color: #666;
        }

        .invoice-title {
            text-align: left;
        }

        .invoice-title h2 {
            font-size: 32px;
            color: #e74c3c;
            text-transform: uppercase;
        }

        .invoice-title .invoice-number {
            font-size: 18px;
            color: #666;
            margin-top: 10px;
        }

        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .party {
            width: 45%;
        }

        .party h3 {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .party p {
            margin-bottom: 5px;
        }

        .party strong {
            font-size: 16px;
        }

        .details-table {
            width: 100%;
            margin-bottom: 30px;
        }

        .details-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .details-table td:first-child {
            color: #999;
            width: 40%;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: right;
            border-bottom: 2px solid #dee2e6;
            font-weight: bold;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .items-table tfoot td {
            background: #f8f9fa;
            font-weight: bold;
        }

        .items-table .total-row td {
            background: #e74c3c;
            color: white;
            font-size: 16px;
        }

        .text-left {
            text-align: left;
        }

        .notes {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .notes h4 {
            color: #999;
            font-size: 12px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-partial {
            background: #17a2b8;
            color: #fff;
        }

        .status-paid {
            background: #28a745;
            color: #fff;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 12px;
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
    <div class="no-print" style="text-align: center; padding: 10px; background: #f8f9fa;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">
            🖨️ طباعة
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; margin-right: 10px;">
            ✖️ إغلاق
        </button>
    </div>

    <div class="invoice">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>Twinx ERP</h1>
                <p>نظام إدارة الموارد المؤسسية</p>
            </div>
            <div class="invoice-title">
                <h2>فاتورة شراء</h2>
                <div class="invoice-number">{{ $purchaseInvoice->invoice_number }}</div>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties">
            <div class="party">
                <h3>المورد</h3>
                <p><strong>{{ $purchaseInvoice->supplier?->name }}</strong></p>
                <p>{{ $purchaseInvoice->supplier?->code }}</p>
                @if($purchaseInvoice->supplier?->phone)
                    <p>📞 {{ $purchaseInvoice->supplier->phone }}</p>
                @endif
                @if($purchaseInvoice->supplier?->email)
                    <p>✉️ {{ $purchaseInvoice->supplier->email }}</p>
                @endif
            </div>
            <div class="party" style="text-align: left;">
                <h3>بيانات الفاتورة</h3>
                <table class="details-table">
                    <tr>
                        <td>رقم فاتورة المورد</td>
                        <td>{{ $purchaseInvoice->supplier_invoice_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>التاريخ</td>
                        <td>{{ $purchaseInvoice->invoice_date?->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td>تاريخ الاستحقاق</td>
                        <td>{{ $purchaseInvoice->due_date?->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td>الحالة</td>
                        <td>
                            @php
                                $statusClass = match ($purchaseInvoice->status->value) {
                                    'paid' => 'status-paid',
                                    'partial' => 'status-partial',
                                    default => 'status-pending'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $purchaseInvoice->status->label() }}
                            </span>
                        </td>
                    </tr>
                </table>
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
                @foreach($purchaseInvoice->lines as $index => $line)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $line->product?->name }}
                            <br>
                            <small style="color: #999;">{{ $line->product?->sku }}</small>
                        </td>
                        <td>{{ number_format($line->quantity, 2) }} {{ $line->product?->unit?->name }}</td>
                        <td>{{ number_format($line->unit_price ?? 0, 2) }}</td>
                        <td class="text-left">{{ number_format($line->line_total ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">الإجمالي الفرعي</td>
                    <td class="text-left">{{ number_format($purchaseInvoice->subtotal, 2) }} ج.م</td>
                </tr>
                @if($purchaseInvoice->tax_amount > 0)
                    <tr>
                        <td colspan="4">الضريبة</td>
                        <td class="text-left">{{ number_format($purchaseInvoice->tax_amount, 2) }} ج.م</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td colspan="4">الإجمالي المستحق</td>
                    <td class="text-left">{{ number_format($purchaseInvoice->total, 2) }} ج.م</td>
                </tr>
                @if($purchaseInvoice->paid_amount > 0)
                    <tr>
                        <td colspan="4">المدفوع</td>
                        <td class="text-left" style="color: green;">{{ number_format($purchaseInvoice->paid_amount, 2) }}
                            ج.م</td>
                    </tr>
                    <tr>
                        <td colspan="4"><strong>المتبقي</strong></td>
                        <td class="text-left" style="color: red;">
                            <strong>{{ number_format($purchaseInvoice->balance_due, 2) }} ج.م</strong></td>
                    </tr>
                @endif
            </tfoot>
        </table>

        <!-- Notes -->
        @if($purchaseInvoice->notes)
            <div class="notes">
                <h4>ملاحظات</h4>
                <p>{{ $purchaseInvoice->notes }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>تم إنشاء هذه الفاتورة بواسطة Twinx ERP</p>
            <p>{{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>
</body>

</html>