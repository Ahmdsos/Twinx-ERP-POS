<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>فاتورة مبيعات #{{ $salesInvoice->invoice_number }}</title>
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
            <button onclick="window.print()" class="btn btn-primary">طباعة الفاتورة</button>
        </div>

        <div class="invoice-header">
            <div>
                @if(\App\Models\Setting::getValue('company_logo'))
                    <img src="{{ Storage::url(\App\Models\Setting::getValue('company_logo')) }}" alt="Logo"
                        style="max-height: 80px; margin-bottom: 10px;">
                @else
                    <h1 class="invoice-title">فاتورة مبيعات</h1>
                @endif
                <h4 class="invoice-title mt-2">فاتورة مبيعات</h4>
                <p><strong>رقم الفاتورة:</strong> {{ $salesInvoice->invoice_number }}</p>
                <p><strong>التاريخ:</strong> {{ $salesInvoice->invoice_date->format('Y-m-d') }}</p>
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
                <h5 class="fw-bold border-bottom pb-2">بيانات العميل</h5>
                <p class="mb-1"><strong>الاسم:</strong> {{ optional($salesInvoice->customer)->name ?? 'عابر' }}</p>
                <p class="mb-1"><strong>الهاتف:</strong> {{ optional($salesInvoice->customer)->phone ?? '-' }}</p>
                <p class="mb-1"><strong>العنوان:</strong>
                    {{ optional($salesInvoice->customer)->billing_address ?? '-' }}</p>
            </div>
            <div class="col-6">
                <!-- Additional Info -->
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
                @foreach($salesInvoice->lines as $index => $line)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $line->product->name }} <br> <small class="text-muted">{{ $line->product->code }}</small>
                        </td>
                        <td class="text-center">{{ $line->quantity }}</td>
                        <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row">
            <div class="col-6">
                <!-- Terms -->
                <p class="text-muted small">
                    <strong>ملاحظات وشروط:</strong><br>
                    {{ \App\Models\Setting::getValue('invoice_footer', 'البضاعة المباعة ترد وتستبدل خلال 14 يوم من تاريخ الفاتورة.') }}
                </p>
            </div>
            <div class="col-6">
                <table class="table table-sm">
                    <tr>
                        <td>المجموع الفرعي:</td>
                        <td class="text-end">{{ number_format($salesInvoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>الضريبة ({{ number_format(\App\Models\Setting::getValue('default_tax_rate', 14), 0) }}%):
                        </td>
                        <td class="text-end">{{ number_format($salesInvoice->tax_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>الخصم:</td>
                        <td class="text-end text-danger">{{ number_format($salesInvoice->discount_amount, 2) }}</td>
                    </tr>
                    <tr class="table-dark text-body fw-bold">
                        <td class="fs-5">الإجمالي المستحق:</td>
                        <td class="text-end fs-5">{{ number_format($salesInvoice->total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>