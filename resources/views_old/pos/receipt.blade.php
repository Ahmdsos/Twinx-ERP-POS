<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة #{{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: #fff;
            color: #000;
        }
        
        .receipt {
            width: 100%;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .logo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 10px;
        }
        
        .invoice-info {
            margin-bottom: 10px;
        }
        
        .row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        
        .items-header {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        
        .item {
            margin: 5px 0;
            padding-bottom: 5px;
            border-bottom: 1px dotted #ccc;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #333;
        }
        
        .totals {
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .payment-info {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .barcode {
            text-align: center;
            margin: 10px 0;
        }
        
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 2mm;
            }
            
            .no-print {
                display: none;
            }
        }
        
        .print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ طباعة</button>
    
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="logo">{{ config('app.name', 'Twinx ERP') }}</div>
            <div class="company-info">
                {{ config('erp.company.address', 'العنوان') }}<br>
                هاتف: {{ config('erp.company.phone', '0000000000') }}
            </div>
        </div>
        
        <!-- Invoice Info -->
        <div class="invoice-info">
            <div class="row">
                <span>رقم الفاتورة:</span>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            <div class="row">
                <span>التاريخ:</span>
                <span>{{ $invoice->invoice_date->format('Y-m-d H:i') }}</span>
            </div>
            @if($invoice->customer)
            <div class="row">
                <span>العميل:</span>
                <span>{{ $invoice->customer->name }}</span>
            </div>
            @endif
            <div class="row">
                <span>الكاشير:</span>
                <span>{{ auth()->user()?->name ?? 'النظام' }}</span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Items -->
        <div class="items-header">
            <div class="row">
                <span>المنتج</span>
                <span>المبلغ</span>
            </div>
        </div>
        
        @foreach($invoice->lines as $line)
        <div class="item">
            <div class="item-name">{{ $line->product?->name ?? 'منتج' }}</div>
            <div class="item-details">
                <span>{{ number_format($line->unit_price, 2) }} × {{ $line->quantity }}</span>
                <span>{{ number_format($line->total, 2) }}</span>
            </div>
            @if($line->discount > 0)
            <div class="item-details" style="color: #c00;">
                <span>خصم</span>
                <span>-{{ number_format($line->discount, 2) }}</span>
            </div>
            @endif
        </div>
        @endforeach
        
        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>المجموع الفرعي:</span>
                <span>{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount > 0)
            <div class="total-row">
                <span>الخصم:</span>
                <span>-{{ number_format($invoice->discount, 2) }}</span>
            </div>
            @endif
            @if($invoice->tax > 0)
            <div class="total-row">
                <span>الضريبة:</span>
                <span>{{ number_format($invoice->tax, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>الإجمالي:</span>
                <span>{{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
        
        <!-- Payment Info -->
        <div class="payment-info">
            <div class="total-row">
                <span>المبلغ المدفوع:</span>
                <span>{{ number_format($invoice->amount_paid, 2) }}</span>
            </div>
            @if($invoice->amount_paid > $invoice->total)
            <div class="total-row" style="font-weight: bold;">
                <span>الباقي:</span>
                <span>{{ number_format($invoice->amount_paid - $invoice->total, 2) }}</span>
            </div>
            @endif
            @if($invoice->balance_due > 0)
            <div class="total-row" style="color: #c00; font-weight: bold;">
                <span>المتبقي على العميل:</span>
                <span>{{ number_format($invoice->balance_due, 2) }}</span>
            </div>
            @endif
            <div class="row">
                <span>طريقة الدفع:</span>
                <span>
                    @switch($invoice->payment_method)
                        @case('cash') نقدي @break
                        @case('card') بطاقة @break
                        @case('credit') آجل @break
                        @default {{ $invoice->payment_method }}
                    @endswitch
                </span>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>شكراً لتسوقكم معنا</p>
            <p>نتمنى لكم يوماً سعيداً</p>
            <br>
            <small>{{ now()->format('Y-m-d H:i:s') }}</small>
        </div>
    </div>
    
    <script>
        // Auto print on load (optional)
        // window.onload = () => window.print();
    </script>
</body>
</html>
