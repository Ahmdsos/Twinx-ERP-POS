<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض سعر - {{ $quotation->quotation_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            padding: 20px;
            direction: rtl;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .company-info h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
        }

        .company-info p {
            color: #666;
            margin: 2px 0;
        }

        .document-title {
            text-align: left;
        }

        .document-title h2 {
            font-size: 24px;
            color: #2c5aa0;
            margin-bottom: 10px;
        }

        .document-title .number {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 30px;
        }

        .info-box {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .info-box h4 {
            color: #2c5aa0;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .info-box p {
            margin: 5px 0;
        }

        .info-box label {
            color: #666;
            display: inline-block;
            width: 80px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px 8px;
            text-align: right;
        }

        th {
            background: #2c5aa0;
            color: white;
            font-weight: 600;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 300px;
        }

        .totals-table td {
            padding: 8px 15px;
        }

        .totals-table tr:last-child {
            background: #2c5aa0;
            color: white;
            font-size: 14px;
        }

        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .notes-section h4 {
            color: #2c5aa0;
            margin-bottom: 10px;
        }

        .terms-section {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .terms-section h4 {
            color: #2c5aa0;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }

        .validity-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .validity-notice strong {
            color: #856404;
        }

        @media print {
            body {
                padding: 10px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; cursor: pointer;">
            🖨️ طباعة
        </button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>{{ config('app.name', 'Twinx ERP') }}</h1>
                <p>العنوان: القاهرة، مصر</p>
                <p>هاتف: 01234567890</p>
                <p>البريد: info@twinxerp.com</p>
            </div>
            <div class="document-title">
                <h2>عرض سعر</h2>
                <div class="number">{{ $quotation->quotation_number }}</div>
            </div>
        </div>

        <!-- Validity Notice -->
        <div class="validity-notice">
            <strong>⏰ هذا العرض صالح حتى: {{ $quotation->valid_until?->format('Y-m-d') }}</strong>
            @if($quotation->isExpired())
                <span style="color: #dc3545;"> (منتهي الصلاحية)</span>
            @endif
        </div>

        <!-- Customer & Quotation Info -->
        <div class="info-section">
            <div class="info-box">
                <h4>بيانات العميل</h4>
                <p><label>الاسم:</label> <strong>{{ $quotation->customer?->name }}</strong></p>
                <p><label>الكود:</label> {{ $quotation->customer?->code }}</p>
                <p><label>الهاتف:</label> {{ $quotation->customer?->phone ?? '-' }}</p>
                <p><label>العنوان:</label> {{ $quotation->customer?->address ?? '-' }}</p>
            </div>
            <div class="info-box">
                <h4>بيانات العرض</h4>
                <p><label>رقم العرض:</label> <strong>{{ $quotation->quotation_number }}</strong></p>
                <p><label>التاريخ:</label> {{ $quotation->quotation_date?->format('Y-m-d') }}</p>
                <p><label>صالح حتى:</label> {{ $quotation->valid_until?->format('Y-m-d') }}</p>
                <p><label>الحالة:</label> {{ $quotation->status->label() }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف</th>
                    <th>الوحدة</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>الخصم %</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->lines as $index => $line)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $line->product?->name }}</td>
                        <td>{{ $line->product?->unit?->name ?? '-' }}</td>
                        <td>{{ number_format($line->quantity, 2) }}</td>
                        <td>{{ number_format($line->unit_price ?? 0, 2) }}</td>
                        <td>{{ $line->discount_percent }}%</td>
                        <td>{{ number_format($line->line_total ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>الإجمالي الفرعي:</td>
                    <td>{{ number_format($quotation->subtotal, 2) }} ج.م</td>
                </tr>
                @if($quotation->tax_amount > 0)
                    <tr>
                        <td>الضريبة:</td>
                        <td>{{ number_format($quotation->tax_amount, 2) }} ج.م</td>
                    </tr>
                @endif
                @if($quotation->discount_amount > 0)
                    <tr>
                        <td>الخصم:</td>
                        <td style="color: #dc3545;">-{{ number_format($quotation->discount_amount, 2) }} ج.م</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>الإجمالي:</strong></td>
                    <td><strong>{{ number_format($quotation->total, 2) }} ج.م</strong></td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($quotation->notes)
            <div class="notes-section">
                <h4>ملاحظات</h4>
                <p>{{ $quotation->notes }}</p>
            </div>
        @endif

        <!-- Terms & Conditions -->
        @if($quotation->terms)
            <div class="terms-section">
                <h4>الشروط والأحكام</h4>
                <p>{{ $quotation->terms }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>شكراً لثقتكم بنا - نتطلع للتعامل معكم</p>
            <p>{{ config('app.name', 'Twinx ERP') }} © {{ date('Y') }}</p>
        </div>
    </div>
</body>

</html>