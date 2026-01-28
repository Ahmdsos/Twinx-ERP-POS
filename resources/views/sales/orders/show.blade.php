@extends('layouts.app')

@section('title', $salesOrder->so_number . ' - Twinx ERP')
@section('page-title', 'تفاصيل أمر البيع')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales-orders.index') }}">أوامر البيع</a></li>
    <li class="breadcrumb-item active">{{ $salesOrder->so_number }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Order Header Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-cart me-2"></i>
                        {{ $salesOrder->so_number }}
                    </h5>
                    @php
                        $statusClass = match ($salesOrder->status->value) {
                            'draft' => 'secondary',
                            'confirmed' => 'primary',
                            'processing' => 'info',
                            'partial' => 'warning',
                            'delivered' => 'success',
                            'invoiced' => 'success',
                            'cancelled' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge bg-{{ $statusClass }} fs-6">
                        {{ $salesOrder->status->label() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">العميل</td>
                                    <td>
                                        <a href="{{ route('customers.show', $salesOrder->customer_id) }}">
                                            <strong>{{ $salesOrder->customer?->name ?? '-' }}</strong>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">كود العميل</td>
                                    <td>{{ $salesOrder->customer?->code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">المستودع</td>
                                    <td>{{ $salesOrder->warehouse?->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">تاريخ الأمر</td>
                                    <td>{{ $salesOrder->order_date->format('Y-m-d') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">تاريخ التسليم المتوقع</td>
                                    <td>{{ $salesOrder->expected_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">طريقة الشحن</td>
                                    <td>{{ $salesOrder->shipping_method ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($salesOrder->shipping_address)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <strong class="text-muted">عنوان الشحن:</strong>
                                <p class="mb-0">{{ $salesOrder->shipping_address }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Lines -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>أصناف الأمر</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>تم التسليم</th>
                                    <th>سعر الوحدة</th>
                                    <th>خصم</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesOrder->lines as $index => $line)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $line->product?->name ?? '-' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $line->product?->sku ?? '' }}</small>
                                        </td>
                                        <td>{{ number_format($line->quantity, 2) }}
                                            {{ $line->product?->unit?->abbreviation ?? '' }}</td>
                                        <td>
                                            @if($line->delivered_quantity > 0)
                                                <span class="text-success">{{ number_format($line->delivered_quantity, 2) }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($line->unit_price, 2) }}</td>
                                        <td>
                                            @if($line->discount_percent > 0)
                                                {{ $line->discount_percent }}%
                                                <br>
                                                <small class="text-danger">-{{ number_format($line->discount_amount, 2) }}</small>
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
                                    <td><strong>{{ number_format($salesOrder->subtotal, 2) }}</strong></td>
                                </tr>
                                @if($salesOrder->discount_amount > 0)
                                    <tr>
                                        <td colspan="6" class="text-start text-danger">الخصم</td>
                                        <td class="text-danger">
                                            <strong>-{{ number_format($salesOrder->discount_amount, 2) }}</strong></td>
                                    </tr>
                                @endif
                                @if($salesOrder->tax_amount > 0)
                                    <tr>
                                        <td colspan="6" class="text-start">الضريبة</td>
                                        <td><strong>{{ number_format($salesOrder->tax_amount, 2) }}</strong></td>
                                    </tr>
                                @endif
                                <tr class="fs-5">
                                    <td colspan="6" class="text-start"><strong>الإجمالي النهائي</strong></td>
                                    <td class="text-primary"><strong>{{ number_format($salesOrder->total, 2) }} ج.م</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($salesOrder->notes || $salesOrder->customer_notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                    </div>
                    <div class="card-body">
                        @if($salesOrder->notes)
                            <div class="mb-3">
                                <strong class="text-muted">ملاحظات داخلية:</strong>
                                <p class="mb-0">{{ $salesOrder->notes }}</p>
                            </div>
                        @endif
                        @if($salesOrder->customer_notes)
                            <div>
                                <strong class="text-muted">ملاحظات للعميل:</strong>
                                <p class="mb-0">{{ $salesOrder->customer_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Related Documents -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>المستندات المرتبطة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">أوامر التسليم</h6>
                            @forelse($salesOrder->deliveryOrders as $do)
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <div>
                                        <strong>{{ $do->do_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $do->delivery_date?->format('Y-m-d') ?? '-' }}</small>
                                    </div>
                                    <span class="badge bg-{{ $do->status === 'shipped' ? 'success' : 'warning' }}">
                                        {{ $do->status === 'shipped' ? 'تم الشحن' : 'قيد التجهيز' }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">لا توجد أوامر تسليم</p>
                            @endforelse
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">الفواتير</h6>
                            @forelse($salesOrder->invoices as $invoice)
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <div>
                                        <strong>{{ $invoice->invoice_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ number_format($invoice->total, 2) }} ج.م</small>
                                    </div>
                                    <span
                                        class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : 'danger') }}">
                                        {{ $invoice->status === 'paid' ? 'مدفوعة' : ($invoice->status === 'partial' ? 'جزئي' : 'معلقة') }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">لا توجد فواتير</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
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
                        @if($salesOrder->canEdit())
                            <a href="{{ route('sales-orders.edit', $salesOrder) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-2"></i>تعديل
                            </a>
                        @endif

                        @if($salesOrder->status->value === 'draft')
                            <form action="{{ route('sales-orders.confirm', $salesOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-2"></i>تأكيد الأمر
                                </button>
                            </form>
                        @endif

                        @if($salesOrder->canDeliver())
                        <a href="{{ route('deliveries.create', ['sales_order_id' => $salesOrder->id]) }}" class="btn btn-info">
                            <i class="bi bi-truck me-2"></i>إنشاء أمر تسليم
                        </a>
                    @endif

                        @if($salesOrder->canInvoice())
                            <a href="#" class="btn btn-primary">
                                <i class="bi bi-receipt me-2"></i>إنشاء فاتورة
                            </a>
                        @endif

                        @if($salesOrder->canCancel())
                            <form action="{{ route('sales-orders.cancel', $salesOrder) }}" method="POST"
                                onsubmit="return confirm('هل أنت متأكد من إلغاء هذا الأمر؟')">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-lg me-2"></i>إلغاء الأمر
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-right me-2"></i>العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

            <!-- Delivery Progress -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-truck me-2"></i>نسبة التسليم</h6>
                </div>
                <div class="card-body">
                    @php $deliveryPercentage = $salesOrder->getDeliveredPercentage(); @endphp
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: {{ $deliveryPercentage }}%">
                            {{ $deliveryPercentage }}%
                        </div>
                    </div>
                    <small class="text-muted">
                        @if($deliveryPercentage == 0)
                            لم يتم التسليم بعد
                        @elseif($deliveryPercentage < 100)
                            تسليم جزئي
                        @else
                            تم التسليم بالكامل
                        @endif
                    </small>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>ملخص الأمر</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>عدد الأصناف:</span>
                        <strong>{{ $salesOrder->lines->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>إجمالي الكميات:</span>
                        <strong>{{ number_format($salesOrder->lines->sum('quantity'), 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>الإجمالي الفرعي:</span>
                        <strong>{{ number_format($salesOrder->subtotal, 2) }}</strong>
                    </div>
                    @if($salesOrder->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>الخصم:</span>
                            <strong>-{{ number_format($salesOrder->discount_amount, 2) }}</strong>
                        </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between fs-5">
                        <span><strong>الإجمالي:</strong></span>
                        <strong class="text-primary">{{ number_format($salesOrder->total, 2) }} ج.م</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection