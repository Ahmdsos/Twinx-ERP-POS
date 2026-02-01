<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>سند صرف رقم {{ $supplierPayment->payment_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');

        body {
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            border-bottom: 1px dotted #ccc;
            padding-bottom: 5px;
        }

        .label {
            font-weight: bold;
            font-size: 14px;
            color: #555;
        }

        .value {
            font-weight: bold;
            font-size: 16px;
            margin-right: 10px;
        }

        .amount-box {
            text-align: center;
            border: 2px solid #000;
            padding: 10px;
            margin: 20px 0;
            border-radius: 8px;
            background: #f9f9f9;
        }

        .amount-box h2 {
            margin: 0;
            font-size: 28px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }

        .table th {
            background: #eee;
            font-weight: 700;
        }

        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        .signature {
            border-top: 1px solid #000;
            width: 40%;
            padding-top: 10px;
            font-weight: bold;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .btn {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        <h1>سند صرف نقدية / بنك</h1>
        <p>رقم السند: {{ $supplierPayment->payment_number }}</p>
        <p>التاريخ: {{ $supplierPayment->payment_date->format('Y-m-d h:i A') }}</p>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="label">اصرفوا إلى السيد/السادة:</span>
            <span class="value">{{ $supplierPayment->supplier->name }}</span>
        </div>
        <div class="info-item">
            <span class="label">طريقة الدفع:</span>
            <span class="value">
                @if($supplierPayment->payment_method == 'cash') نقدي
                @elseif($supplierPayment->payment_method == 'bank_transfer') تحويل بنكي @else شيك @endif
            </span>
        </div>
        <div class="info-item">
            <span class="label">المرجع:</span>
            <span class="value">{{ $supplierPayment->reference ?? '-' }}</span>
        </div>
        <div class="info-item">
            <span class="label">الخزينة/البنك:</span>
            <span class="value">{{ $supplierPayment->paymentAccount->name ?? '-' }}</span>
        </div>
    </div>

    <div class="amount-box">
        <span class="label">مبلغ وقدره</span>
        <h2>{{ number_format($supplierPayment->amount, 2) }} ج.م</h2>
    </div>

    @if($supplierPayment->notes)
        <div class="info-item" style="margin-bottom: 20px;">
            <span class="label">وذلك عن:</span>
            <span class="value">{{ $supplierPayment->notes }}</span>
        </div>
    @endif

    @if($supplierPayment->allocations->count() > 0)
        <h4>تفاصيل التخصيص (الفواتير):</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>القيمة المخصومة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supplierPayment->allocations as $allocation)
                    <tr>
                        <td>{{ $allocation->invoice->invoice_number ?? '-' }}</td>
                        <td>{{ number_format($allocation->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div class="signature">
            المستلم
        </div>
        <div class="signature">
            أمين الخزينة / المحاسب
            <br>
            <small>{{ $supplierPayment->creator->name ?? '' }}</small>
        </div>
    </div>

</body>

</html>