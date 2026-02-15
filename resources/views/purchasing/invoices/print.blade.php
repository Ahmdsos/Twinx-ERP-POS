<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>فاتورة شراء #{{ $purchaseInvoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            background: #fff;
            color: #000;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .amounts-box {
            float: left;
            width: 300px;
        }

        @media print {
            body {
                padding: 0;
            }

            .invoice-box {
                border: none;
                padding: 0;
                max-width: 100%;
            }

            .btn-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-box mt-4">
        <div class="text-center mb-4 d-print-none">
            <button onclick="window.print()" class="btn btn-primary btn-print">طباعة الفاتورة</button>
            <a href="{{ route('purchase-invoices.show', $purchaseInvoice) }}" class="btn btn-secondary btn-print">العودة
                للفاتورة</a>
        </div>

        <div class="invoice-header">
            <div>
                @php
                    $logo = \App\Models\Setting::getValue('company_logo');
                @endphp
                @if($logo)
                    <img src="{{ Storage::url($logo) }}" alt="Logo" style="max-height: 80px; margin-bottom: 10px;">
                @endif
                <h4 class="invoice-title mt-2">فاتورة شراء</h4>
                <p><strong>رقم الفاتورة:</strong> {{ $purchaseInvoice->invoice_number }}</p>
                <p><strong>رقم فاتورة المورد:</strong> {{ $purchaseInvoice->supplier_invoice_number }}</p>
                <p><strong>التاريخ:</strong> {{ $purchaseInvoice->invoice_date->format('Y-m-d') }}</p>
            </div>
            <div class="text-end">
                <h3>{{ \App\Models\Setting::getValue('company_name', config('app.name')) }}</h3>
                <p>
                    {{ \App\Models\Setting::getValue('company_address', 'العنوان غير محدد') }}<br>
                    <strong>الرقم الضريبي:</strong> {{ \App\Models\Setting::getValue('company_tax_number', '-') }}<br>
                    <strong>الهاتف:</strong> {{ \App\Models\Setting::getValue('company_phone', '-') }}
                </p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-6">
                <h5 class="fw-bold border-bottom pb-2">بيانات المورد</h5>
                <p class="mb-1"><strong>الاسم:</strong> {{ $purchaseInvoice->supplier->name ?? '-' }}</p>
                <p class="mb-1"><strong>الهاتف:</strong> {{ $purchaseInvoice->supplier->phone ?? '-' }}</p>
                <p class="mb-1"><strong>العنوان:</strong> {{ $purchaseInvoice->supplier->address ?? '-' }}</p>
                <p class="mb-1"><strong>الرقم الضريبي للمورد:</strong>
                    {{ $purchaseInvoice->supplier->tax_number ?? '-' }}</p>
            </div>
            <div class="col-6 text-end">
                <h5 class="fw-bold border-bottom pb-2">حالة الفاتورة</h5>
                <p class="mb-1"><strong>الحالة:</strong> {{ $purchaseInvoice->status->label() }}</p>
                <p class="mb-1"><strong>تاريخ الاستحقاق:</strong> {{ $purchaseInvoice->due_date->format('Y-m-d') }}</p>
            </div>
        </div>

        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th>الصنف</th>
                    <th class="text-center" style="width: 10%">الكمية</th>
                    <th class="text-end" style="width: 15%">سعر الوحدة</th>
                    <th class="text-end" style="width: 15%">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseInvoice->lines as $index => $line)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $line->product->name }} <br> <small class="text-muted">{{ $line->product->sku }}</small>
                        </td>
                        <td class="text-center">{{ $line->quantity }} {{ $line->product->unit->name ?? '' }}</td>
                        <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row">
            <div class="col-6">
                @if($purchaseInvoice->notes)
                    <div class="border p-3 rounded">
                        <strong>ملاحظات:</strong><br>
                        {{ $purchaseInvoice->notes }}
                    </div>
                @endif
            </div>
            <div class="col-6">
                <table class="table table-sm">
                    <tr>
                        <td>المجموع الفرعي:</td>
                        <td class="text-end">{{ number_format($purchaseInvoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>الضريبة:</td>
                        <td class="text-end">{{ number_format($purchaseInvoice->tax_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>الخصم:</td>
                        <td class="text-end text-danger">{{ number_format($purchaseInvoice->discount_amount, 2) }}</td>
                    </tr>
                    <tr class="table-dark text-body fw-bold">
                        <td class="fs-5">الإجمالي المستحق:</td>
                        <td class="text-end fs-5">{{ number_format($purchaseInvoice->total, 2) }}</td>
                    </tr>
                    @if($purchaseInvoice->paid_amount > 0)
                        <tr>
                            <td>المبلغ المدفوع:</td>
                            <td class="text-end text-success">{{ number_format($purchaseInvoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>الرصيد المتبقي:</td>
                            <td class="text-end text-danger">{{ number_format($purchaseInvoice->balance_due, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="mt-5 pt-5 text-center">
            <div class="row">
                <div class="col-4">
                    <p class="border-top pt-2">توقيع المستلم</p>
                </div>
                <div class="col-4"></div>
                <div class="col-4">
                    <p class="border-top pt-2">ختم الشركة</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>