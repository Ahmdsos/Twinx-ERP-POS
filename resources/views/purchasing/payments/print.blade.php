<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال دفع - {{ $supplierPayment->payment_number }}</title>
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

        .receipt {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: #e74c3c;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 20px;
            color: #666;
        }

        .receipt-number {
            background: #f8f9fa;
            padding: 15px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 5px;
        }

        .receipt-number h3 {
            font-size: 24px;
            color: #e74c3c;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-section h4 {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .info-table {
            width: 100%;
        }

        .info-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-table td:first-child {
            color: #999;
            width: 40%;
        }

        .amount-box {
            background: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .amount-box h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }

        .amount-box p {
            font-size: 14px;
            opacity: 0.8;
        }

        .allocations {
            margin-bottom: 30px;
        }

        .allocations table {
            width: 100%;
            border-collapse: collapse;
        }

        .allocations th {
            background: #f8f9fa;
            padding: 10px;
            text-align: right;
            border-bottom: 2px solid #dee2e6;
        }

        .allocations td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 12px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 20px;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-box .line {
            border-top: 1px solid #333;
            margin-bottom: 5px;
            margin-top: 40px;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .receipt {
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

    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <h1>Twinx ERP</h1>
            <h2>إيصال دفع للمورد</h2>
        </div>

        <!-- Receipt Number -->
        <div class="receipt-number">
            <h3>{{ $supplierPayment->payment_number }}</h3>
            <p>{{ $supplierPayment->payment_date?->format('Y-m-d') }}</p>
        </div>

        <!-- Amount Box -->
        <div class="amount-box">
            <h3>{{ number_format($supplierPayment->amount, 2) }} ج.م</h3>
            <p>قيمة الدفعة</p>
        </div>

        <!-- Supplier Info -->
        <div class="info-section">
            <h4>بيانات المورد</h4>
            <table class="info-table">
                <tr>
                    <td>اسم المورد</td>
                    <td><strong>{{ $supplierPayment->supplier?->name }}</strong></td>
                </tr>
                <tr>
                    <td>كود المورد</td>
                    <td>{{ $supplierPayment->supplier?->code }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment Details -->
        <div class="info-section">
            <h4>تفاصيل الدفع</h4>
            <table class="info-table">
                <tr>
                    <td>طريقة الدفع</td>
                    <td>
                        @php
                            $methodLabels = [
                                'cash' => 'نقدي',
                                'bank_transfer' => 'تحويل بنكي',
                                'cheque' => 'شيك',
                            ];
                        @endphp
                        {{ $methodLabels[$supplierPayment->payment_method] ?? $supplierPayment->payment_method }}
                    </td>
                </tr>
                @if($supplierPayment->reference)
                    <tr>
                        <td>رقم المرجع</td>
                        <td>{{ $supplierPayment->reference }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Allocations -->
        @if($supplierPayment->allocations->count() > 0)
            <div class="allocations">
                <h4
                    style="font-size: 12px; color: #999; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                    الفواتير المسددة
                </h4>
                <table>
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplierPayment->allocations as $allocation)
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
        <div class="signatures">
            <div class="signature-box">
                <div class="line"></div>
                <p>المستلم</p>
            </div>
            <div class="signature-box">
                <div class="line"></div>
                <p>أمين الخزينة</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>تم إنشاء هذا الإيصال بواسطة Twinx ERP</p>
            <p>{{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>
</body>

</html>