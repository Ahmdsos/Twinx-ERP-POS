@extends('layouts.app')

@section('title', $salesInvoice->invoice_number . ' - Twinx ERP')
@section('page-title', 'تفاصيل الفاتورة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales-invoices.index') }}">فواتير البيع</a></li>
    <li class="breadcrumb-item active">{{ $salesInvoice->invoice_number }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Invoice Header Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>
                        {{ $salesInvoice->invoice_number }}
                    </h5>
                    @php
                        $statusClass = match ($salesInvoice->status->value) {
                            'draft' => 'secondary',
                            'pending' => 'warning',
                            'partial' => 'info',
                            'paid' => 'success',
                            'cancelled' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge bg-{{ $statusClass }} fs-6">
                        {{ $salesInvoice->status->label() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">العميل</td>
                                    <td>
                                        <a href="{{ route('customers.show', $salesInvoice->customer_id) }}">
                                            <strong>{{ $salesInvoice->customer?->name ?? '-' }}</strong>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">كود العميل</td>
                                    <td>{{ $salesInvoice->customer?->code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">أمر البيع</td>
                                    <td>
                                        @if($salesInvoice->salesOrder)
                                            <a href="{{ route('sales-orders.show', $salesInvoice->sales_order_id) }}">
                                                {{ $salesInvoice->salesOrder?->so_number }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">تاريخ الفاتورة</td>
                                    <td>{{ $salesInvoice->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">تاريخ الاستحقاق</td>
                                    <td>
                                        {{ $salesInvoice->due_date?->format('Y-m-d') ?? '-' }}
                                        @if($salesInvoice->isOverdue())
                                            <br>
                                            <span class="badge bg-danger">
                                                متأخرة {{ $salesInvoice->getDaysOverdue() }} يوم
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">أمر التسليم</td>
                                    <td>
                                        @if($salesInvoice->deliveryOrder)
                                            <a href="{{ route('deliveries.show', $salesInvoice->delivery_order_id) }}">
                                                {{ $salesInvoice->deliveryOrder?->do_number }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Lines -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>أصناف الفاتورة</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>سعر الوحدة</th>
                                    <th>خصم</th>
                                    <th>ضريبة</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesInvoice->lines as $index => $line)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $line->product?->name ?? '-' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $line->product?->sku ?? '' }}</small>
                                        </td>
                                        <td>{{ number_format($line->quantity, 2) }}
                                            {{ $line->product?->unit?->abbreviation ?? '' }}</td>
                                        <td>{{ number_format($line->unit_price, 2) }}</td>
                                        <td>
                                            @if($line->discount_amount > 0)
                                                <span class="text-danger">-{{ number_format($line->discount_amount, 2) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($line->tax_amount > 0)
                                                {{ number_format($line->tax_amount, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><strong>{{ number_format($line->line_total, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-start">الإجمالي الفرعي</td>
                                    <td><strong>{{ number_format($salesInvoice->subtotal, 2) }}</strong></td>
                                </tr>
                                @if($salesInvoice->discount_amount > 0)
                                    <tr>
                                        <td colspan="6" class="text-start text-danger">الخصم</td>
                                        <td class="text-danger">
                                            <strong>-{{ number_format($salesInvoice->discount_amount, 2) }}</strong></td>
                                    </tr>
                                @endif
                                @if($salesInvoice->tax_amount > 0)
                                    <tr>
                                        <td colspan="6" class="text-start">الضريبة</td>
                                        <td><strong>{{ number_format($salesInvoice->tax_amount, 2) }}</strong></td>
                                    </tr>
                                @endif
                                <tr class="fs-5">
                                    <td colspan="6" class="text-start"><strong>الإجمالي النهائي</strong></td>
                                    <td class="text-primary"><strong>{{ number_format($salesInvoice->total, 2) }}
                                            ج.م</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>سجل المدفوعات</h5>
                </div>
                <div class="card-body p-0">
                    @if($salesInvoice->paymentAllocations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الإيصال</th>
                                        <th>التاريخ</th>
                                        <th>المبلغ</th>
                                        <th>طريقة الدفع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesInvoice->paymentAllocations as $allocation)
                                        <tr>
                                            <td>{{ $allocation->payment?->receipt_number ?? '-' }}</td>
                                            <td>{{ $allocation->payment?->payment_date?->format('Y-m-d') ?? '-' }}</td>
                                            <td class="text-success">{{ number_format($allocation->amount, 2) }} ج.م</td>
                                            <td>{{ $allocation->payment?->payment_method ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-cash-coin fs-1 d-block mb-2"></i>
                            لا توجد مدفوعات مسجلة
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($salesInvoice->notes || $salesInvoice->terms)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                    </div>
                    <div class="card-body">
                        @if($salesInvoice->terms)
                            <div class="mb-3">
                                <strong class="text-muted">شروط الدفع:</strong>
                                <p class="mb-0">{{ $salesInvoice->terms }}</p>
                            </div>
                        @endif
                        @if($salesInvoice->notes)
                            <div>
                                <strong class="text-muted">ملاحظات:</strong>
                                <p class="mb-0">{{ $salesInvoice->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>الإجراءات</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sales-invoices.print', $salesInvoice) }}" class="btn btn-secondary"
                            target="_blank">
                            <i class="bi bi-printer me-2"></i>طباعة الفاتورة
                        </a>

                        @if($salesInvoice->status->canReceivePayment())
                            <a href="{{ route('customer-payments.create', ['invoice_id' => $salesInvoice->id]) }}"
                                class="btn btn-success">
                                <i class="bi bi-cash me-2"></i>تسجيل دفعة
                            </a>
                        @endif

                        @if($salesInvoice->paid_amount == 0 && $salesInvoice->status->value !== 'cancelled')
                            <form action="{{ route('sales-invoices.cancel', $salesInvoice) }}" method="POST"
                                onsubmit="return confirm('هل أنت متأكد من إلغاء هذه الفاتورة؟')">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-lg me-2"></i>إلغاء الفاتورة
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('sales-invoices.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>ملخص الدفعات</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>إجمالي الفاتورة:</span>
                        <strong>{{ number_format($salesInvoice->total, 2) }} ج.م</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-success">
                        <span>المدفوع:</span>
                        <strong>{{ number_format($salesInvoice->paid_amount, 2) }} ج.م</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5">
                        <span><strong>المتبقي:</strong></span>
                        <strong class="{{ $salesInvoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($salesInvoice->balance_due, 2) }} ج.م
                        </strong>
                    </div>

                    <!-- Payment Progress -->
                    @if($salesInvoice->total > 0)
                        @php $paymentPercent = min(100, ($salesInvoice->paid_amount / $salesInvoice->total) * 100); @endphp
                        <div class="progress mt-3" style="height: 20px;">
                            <div class="progress-bar bg-success" style="width: {{ $paymentPercent }}%">
                                {{ number_format($paymentPercent, 0) }}%
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection