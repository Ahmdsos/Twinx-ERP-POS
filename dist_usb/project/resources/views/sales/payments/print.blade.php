<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>إيصال تحصيل #{{ $customerPayment->payment_number }}</title>
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            padding: 40px;
        }

        .receipt-box {
            border: 2px solid #333;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .row {
            display: flex;
            margin-bottom: 15px;
        }

        .label {
            width: 150px;
            font-weight: bold;
            color: #555;
        }

        .value {
            flex: 1;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 5px;
        }

        .amount-box {
            border: 2px solid #000;
            padding: 10px 30px;
            font-size: 24px;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        .signature {
            border-top: 1px solid #000;
            padding-top: 10px;
            width: 200px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="receipt-box">
        <div class="header">
            <h1>إيصال استلام نقدية</h1>
            <h3>{{ config('app.name') }}</h3>
            <p>التاريخ: {{ \Carbon\Carbon::parse($customerPayment->payment_date)->format('Y-m-d') }}</p>
        </div>

        <div class="content">
            <div class="row">
                <span class="label">رقم الإيصال:</span>
                <span class="value">{{ $customerPayment->payment_number }}</span>
            </div>
            <div class="row">
                <span class="label">استلمنا من السيد/ة:</span>
                <span class="value">{{ $customerPayment->customer->name }}</span>
            </div>
            <div class="row">
                <span class="label">مبلغ وقدره:</span>
                <span class="value">{{ number_format($customerPayment->amount, 2) }} جنيه مصري</span>
            </div>
            <div class="row">
                <span class="label">وذلك قيمة:</span>
                <span class="value">{{ $customerPayment->notes ?? 'دفعة من الحساب' }}</span>
            </div>
            <div class="row">
                <span class="label">طريقة الدفع:</span>
                <span class="value">
                    {{ $customerPayment->payment_method == 'cash' ? 'نقدي' :
    ($customerPayment->payment_method == 'cheque' ? 'شيك رقم ' . $customerPayment->reference_number : $customerPayment->payment_method) }}
                </span>
            </div>

            <div style="text-align: center">
                <div class="amount-box">
                    EGP {{ number_format($customerPayment->amount, 2) }}
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="signature">
                المحاسب
            </div>
            <div class="signature">
                المستلم
            </div>
        </div>
    </div>
</body>

</html>