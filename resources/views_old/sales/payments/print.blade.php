<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال {{ $customerPayment->receipt_number }} - Twinx ERP</title>
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

        .receipt {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            border: 2px solid #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed #ccc;
        }

        .header h1 {
            font-size: 24px;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            margin-bottom: 15px;
        }

        .receipt-title {
            font-size: 28px;
            font-weight: bold;
            color: #059669;
            margin-top: 15px;
        }

        .receipt-number {
            font-size: 18px;
            color: #333;
            margin-top: 5px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .info-box {
            width: 48%;
        }

        .info-box .label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-box .value {
            font-size: 16px;
            font-weight: bold;
        }

        .amount-box {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .amount-box .label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .amount-box .amount {
            font-size: 48px;
            font-weight: bold;
        }

        .amount-box .currency {
            font-size: 20px;
        }

        .details-table {
            width: 100%;
            margin-bottom: 30px;
        }

        .details-table td {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .details-table td:first-child {
            color: #666;
            width: 40%;
        }

        .details-table td:last-child {
            font-weight: bold;
            text-align: left;
        }

        .invoices-section {
            margin-bottom: 30px;
        }

        .invoices-section h3 {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .invoices-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoices-table th,
        .invoices-table td {
            padding: 10px;
            text-align: right;
            border: 1px solid #ddd;
        }

        .invoices-table th {
            background: #f5f5f5;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px dashed #ccc;
            font-size: 12px;
            color: #888;
        }

        .footer .thank-you {
            font-size: 16px;
            color: #059669;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin: 40px 0 30px;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 12px;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .receipt {
                border: none;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <!-- Print Button (no-print) -->
        <div class="no-print" style="text-align: center; margin-bottom: 20px;">
            <button onclick="window.print()"
                style="padding: 10px 30px; font-size: 16px; cursor: pointer; background: #059669; color: white; border: none; border-radius: 5px;">
                🖨️ طباعة الإيصال
            </button>
            <button onclick="window.close()"
                style="padding: 10px 30px; font-size: 16px; cursor: pointer; background: #6b7280; color: white; border: none; border-radius: 5px; margin-right: 10px;">
                ✖ إغلاق
            </button>
        </div>

        <!-- Header -->
        <div class="header">
            <h1>Twinx ERP</h1>
            <p>نظام إدارة موارد المؤسسات</p>
            <div class="receipt-title">إيصال استلام</div>
            <div class="receipt-number">{{ $customerPayment->receipt_number }}</div>
        </div>

        <!-- Amount Box -->
        <div class="amount-box">
            <div class="label">المبلغ المستلم</div>
            <div class="amount">
                {{ number_format($customerPayment->amount, 2) }}
                <span class="currency">ج.م</span>
            </div>
        </div>

        <!-- Customer & Date Info -->
        <div class="info-section">
            <div class="info-box">
                <div class="label">استلمنا من السيد/السادة</div>
                <div class="value">{{ $customerPayment->customer?->name }}</div>
            </div>
            <div class="info-box" style="text-align: left;">
                <div class="label">التاريخ</div>
                <div class="value">{{ $customerPayment->payment_date?->format('Y-m-d') }}</div>
            </div>
        </div>

        <!-- Payment Details -->
        <table class="details-table">
            <tr>
                <td>طريقة الدفع</td>
                <td>
                    @php
                        $methodLabels = [
                            'cash' => 'نقداً',
                            'bank_transfer' => 'تحويل بنكي',
                            'check' => 'شيك',
                            'credit_card' => 'بطاقة ائتمان',
                        ];
                    @endphp
                    {{ $methodLabels[$customerPayment->payment_method] ?? $customerPayment->payment_method }}
                </td>
            </tr>
            @if($customerPayment->reference)
                <tr>
                    <td>رقم المرجع</td>
                    <td>{{ $customerPayment->reference }}</td>
                </tr>
            @endif
            <tr>
                <td>كود العميل</td>
                <td>{{ $customerPayment->customer?->code }}</td>
            </tr>
        </table>

        <!-- Allocated Invoices -->
        @if($customerPayment->allocations->count() > 0)
            <div class="invoices-section">
                <h3>مخصص للفواتير التالية</h3>
                <table class="invoices-table">
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customerPayment->allocations as $allocation)
                            <tr>
                                <td>{{ $allocation->invoice?->invoice_number }}</td>
                                <td>{{ number_format($allocation->amount, 2) }} ج.م</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">توقيع المستلم</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">توقيع العميل</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">شكراً لتعاملكم معنا</div>
            <p>Twinx ERP - نظام إدارة موارد المؤسسات</p>
            <p>تم الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>
</body>

</html>