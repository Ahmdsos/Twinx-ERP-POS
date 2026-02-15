@extends('layouts.app')

@section('title', 'تقرير الوردية (Z-Report) #' . $shift->id)

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm border-0 glass-card p-4"
            style="background: rgba(30, 30, 40, 0.95); border: 1px solid var(--btn-glass-border);">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <h2 class="mb-0 text-heading">
                    <i class="bi bi-file-earmark-bar-graph me-2 text-info"></i>
                    تقرير الوردية #{{ $shift->id }}
                </h2>
                <div class="text-end">
                    <button onclick="window.print()" class="btn btn-secondary no-print me-2">
                        <i class="bi bi-printer me-1"></i> طباعة التقرير
                    </button>
                    <a href="{{ route('pos.index') }}" class="btn btn-primary no-print">
                        <i class="bi bi-arrow-right me-1"></i> العودة للبيع
                    </a>
                </div>
            </div>

            <div class="row g-4 m-0">
                <!-- Header Info -->
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border h-100"
                        style="background: var(--btn-glass-bg); border-color: rgba(255, 255, 255, 0.1) !important;">
                        <p class="text-secondary small mb-1">الكاشير المسئول</p>
                        <h5 class="mb-3 text-heading">{{ $shift->user->name }}</h5>

                        <p class="text-secondary small mb-1">توقيت الفتح</p>
                        <p class="fw-bold mb-3 text-body">{{ $shift->opened_at->format('Y-m-d H:i') }}</p>

                        @if($shift->closed_at)
                            <p class="text-secondary small mb-1">توقيت الإغلاق</p>
                            <p class="fw-bold text-body">{{ $shift->closed_at->format('Y-m-d H:i') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="col-md-8">
                    <div class="table-responsive border rounded-3 overflow-hidden">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-primary text-secondary text-center">
                                <tr>
                                    <th colspan="2">ملخص الحسابات</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tbody>
                                <tr>
                                    <td class="ps-3 bg-transparent text-body">النقدية الافتتاحية (العهدة)</td>
                                    <td class="text-end pe-3 fw-bold text-body">
                                        {{ number_format($stats['opening_cash'], 2) }} EGP
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 bg-transparent text-body">إجمالي المبيعات (نقداً)</td>
                                    <td class="text-end pe-3 text-success fw-bold">+
                                        {{ number_format($stats['total_cash'], 2) }} EGP
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 bg-transparent text-body">إجمالي المصروفات</td>
                                    <td class="text-end pe-3 text-danger fw-bold">-
                                        {{ number_format($stats['total_expenses'], 2) }} EGP
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3 bg-transparent text-body">إجمالي المرتجعات (نقداً)</td>
                                    <td class="text-end pe-3 text-danger fw-bold">-
                                        {{ number_format($stats['total_returns'], 2) }} EGP
                                    </td>
                                </tr>
                                <tr class="table-primary border-top border-primary border-2"
                                    style="background-color: rgba(13, 110, 253, 0.2) !important;">
                                    <td class="ps-3 fw-bold text-body" style="background: transparent;">النقدية المتوقعة في
                                        الدرج</td>
                                    <td class="text-end pe-3 fs-5 fw-black text-info" style="background: transparent;">
                                        {{ number_format($stats['expected_cash'], 2) }} EGP
                                    </td>
                                </tr>
                                <tr class="table-secondary" style="background-color: rgba(108, 117, 125, 0.2) !important;">
                                    <td class="ps-3 text-body" style="background: transparent;">النقدية الفعلية (المسلمة)
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-body" style="background: transparent;">
                                        {{ number_format($stats['closing_cash'], 2) }} EGP
                                    </td>
                                </tr>
                                <tr class="{{ $stats['difference'] < 0 ? 'table-danger' : ($stats['difference'] > 0 ? 'table-warning' : 'table-success') }}"
                                    style="background-color: transparent;">
                                    <td class="ps-3 fw-bold text-body" style="background: transparent;">الفارق / العجز</td>
                                    <td class="text-end pe-3 fw-black {{ $stats['difference'] < 0 ? 'text-danger' : ($stats['difference'] > 0 ? 'text-warning' : 'text-success') }}"
                                        style="background: transparent;">{{ number_format($stats['difference'], 2) }} EGP
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Other Stats -->
                <div class="col-md-12 mt-4">
                    <div class="row row-cols-1 row-cols-md-3 g-3">
                        <div class="col">
                            <div class="p-3 border rounded shadow-sm text-center"
                                style="background: var(--btn-glass-bg); border-color: rgba(255, 255, 255, 0.1) !important;">
                                <h6 class="text-secondary border-bottom border-secondary pb-2">مبيعات الشبكة (Card)</h6>
                                <h4 class="mb-0 text-info">{{ number_format($stats['total_card'], 2) }}</h4>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 border rounded shadow-sm text-center"
                                style="background: var(--btn-glass-bg); border-color: rgba(255, 255, 255, 0.1) !important;">
                                <h6 class="text-secondary border-bottom border-secondary pb-2">مبيعات الآجل (Credit)</h6>
                                <h4 class="mb-0 text-warning">{{ number_format($stats['total_credit'], 2) }}</h4>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-3 border rounded shadow-sm text-center"
                                style="background: var(--btn-glass-bg); border-color: rgba(255, 255, 255, 0.1) !important;">
                                <h6 class="text-secondary border-bottom border-secondary pb-2">إجمالي التحصيل</h6>
                                <h4 class="mb-0 text-heading">{{ number_format($stats['total_sales'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Transactions Table -->
            <div class="col-12 mt-4">
                <div class="border rounded-3 overflow-hidden"
                    style="background: var(--btn-glass-bg); border-color: rgba(255, 255, 255, 0.1) !important;">
                    <h5 class="p-3 mb-0 text-heading border-bottom border-secondary">
                        <i class="bi bi-list-check me-2 text-primary"></i> تفاصيل المبيعات
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle text-body">
                            <thead class="bg-secondary text-secondary small">
                                <tr>
                                    <th># الفاتورة</th>
                                    <th>الوقت</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Total') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>البائع (Cashier)</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shift->invoices as $invoice)
                                    <tr>
                                        <td class="font-monospace text-info fw-bold">{{ $invoice->invoice_number }}
                                        </td>
                                        <td>{{ $invoice->created_at->format('H:i') }}</td>
                                        <td>{{ $invoice->customer->name ?? 'Walk-in' }}</td>
                                        <td class="fw-bold">{{ number_format($invoice->total, 2) }}</td>
                                        <td>
                                            @foreach ($invoice->paymentAllocations as $payment)
                                                <span class="badge bg-dark border border-secondary">{{ $payment->method }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-warning">
                                            <i class="bi bi-person-badge me-1"></i>
                                            {{ $invoice->creator->name ?? 'System' }}
                                        </td>
                                        <td class="d-flex align-items-center gap-2">
                                            <span
                                                class="{{ $invoice->status == \Modules\Sales\Enums\SalesInvoiceStatus::PAID ? 'bg-success' : 'bg-warning' }} badge">
                                                {{ $invoice->status->label() }}
                                            </span>
                                            <a href="{{ route('sales-invoices.show', $invoice) }}"
                                                class="btn btn-sm btn-outline-info border-0" title="عرض الفاتورة">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $invoice->status == \Modules\Sales\Enums\SalesInvoiceStatus::PAID ? 'bg-success' : 'bg-warning' }}">
                                                {{ $invoice->status->label() }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            لا توجد مبيعات مسجلة في هذه الوردية
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($shift->closing_notes)
                <div class="col-12 mt-4">
                    <div class="p-3 bg-surface-secondary rounded border">
                        <h6 class="text-muted">ملاحظات الإغلاق:</h6>
                        <p class="mb-0 fst-italic">{{ $shift->closing_notes }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .card {
                border: 0 !important;
                shadow: none !important;
            }

            body {
                background: white !important;
            }

            
        }

        .fw-black {
            font-weight: 900;
        }
    </style>
@endsection