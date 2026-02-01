@extends('layouts.app')

@section('title', $purchaseOrder->po_number . ' - Twinx ERP')
@section('page-title', 'تفاصيل أمر الشراء')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">أوامر الشراء</a></li>
    <li class="breadcrumb-item active">{{ $purchaseOrder->po_number }}</li>
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
                        {{ $purchaseOrder->po_number }}
                    </h5>
                    @php
                        $statusColors = [
                            'draft' => 'secondary',
                            'pending' => 'warning',
                            'approved' => 'info',
                            'sent' => 'primary',
                            'partial' => 'warning',
                            'received' => 'success',
                            'cancelled' => 'danger',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$purchaseOrder->status->value] ?? 'secondary' }} fs-6">
                        {{ $purchaseOrder->status->label() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">المورد</td>
                                    <td>
                                        <a href="{{ route('suppliers.show', $purchaseOrder->supplier_id) }}">
                                            <strong>{{ $purchaseOrder->supplier?->name ?? '-' }}</strong>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">كود المورد</td>
                                    <td>{{ $purchaseOrder->supplier?->code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">المستودع</td>
                                    <td>{{ $purchaseOrder->warehouse?->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">تاريخ الطلب</td>
                                    <td>{{ $purchaseOrder->order_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">التسليم المتوقع</td>
                                    <td>{{ $purchaseOrder->expected_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">المرجع</td>
                                    <td>{{ $purchaseOrder->reference ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($purchaseOrder->approved_at)
                        <div class="alert alert-info mb-0 mt-3">
                            <i class="bi bi-check-circle me-1"></i>
                            تم اعتماد الأمر بواسطة <strong>{{ $purchaseOrder->approver?->name }}</strong>
                            في {{ $purchaseOrder->approved_at->format('Y-m-d H:i') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Lines -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>بنود أمر الشراء</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>المنتج</th>
                                    <th>الكمية</th>
                                    <th>المستلم</th>
                                    <th>السعر</th>
                                    <th>الخصم</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->lines as $index => $line)
                                    @php
                                        $receivedPercent = $line->quantity > 0
                                            ? round(($line->received_quantity / $line->quantity) * 100)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $line->product?->name ?? '-' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $line->product?->sku }}</small>
                                        </td>
                                        <td>{{ number_format($line->quantity, 2) }} {{ $line->product?->unit?->name }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px; width: 60px;">
                                                    <div class="progress-bar {{ $receivedPercent >= 100 ? 'bg-success' : 'bg-warning' }}"
                                                        style="width: {{ $receivedPercent }}%"></div>
                                                </div>
                                                <small>{{ number_format($line->received_quantity, 2) }}</small>
                                            </div>
                                        </td>
                                        <td>{{ number_format($line->unit_price, 2) }}</td>
                                        <td>{{ $line->discount_percent > 0 ? $line->discount_percent . '%' : '-' }}</td>
                                        <td class="fw-bold">{{ number_format($line->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-start">الإجمالي الفرعي</td>
                                    <td class="fw-bold">{{ number_format($purchaseOrder->subtotal, 2) }} ج.م</td>
                                </tr>
                                @if($purchaseOrder->discount_amount > 0)
                                    <tr>
                                        <td colspan="6" class="text-start">الخصم</td>
                                        <td class="text-danger">-{{ number_format($purchaseOrder->discount_amount, 2) }} ج.م
                                        </td>
                                    </tr>
                                @endif
                                @if($purchaseOrder->tax_amount > 0)
                                    <tr>
                                        <td colspan="6" class="text-start">الضريبة</td>
                                        <td>{{ number_format($purchaseOrder->tax_amount, 2) }} ج.م</td>
                                    </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="6" class="text-start"><strong>الإجمالي</strong></td>
                                    <td><strong>{{ number_format($purchaseOrder->total, 2) }} ج.م</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($purchaseOrder->notes || $purchaseOrder->terms)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات وشروط</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($purchaseOrder->notes)
                                <div class="col-md-6">
                                    <h6>ملاحظات</h6>
                                    <p class="text-muted">{{ $purchaseOrder->notes }}</p>
                                </div>
                            @endif
                            @if($purchaseOrder->terms)
                                <div class="col-md-6">
                                    <h6>الشروط</h6>
                                    <p class="text-muted">{{ $purchaseOrder->terms }}</p>
                                </div>
                            @endif
                        </div>
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
                        <!-- GRNs -->
                        <div class="col-md-6">
                            <h6>سندات استلام البضاعة (GRN)</h6>
                            @if($purchaseOrder->grns->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($purchaseOrder->grns as $grn)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <a href="{{ route('grns.show', $grn) }}">{{ $grn->grn_number }}</a>
                                            <span class="badge bg-{{ $grn->status->value == 'completed' ? 'success' : 'warning' }}">
                                                {{ $grn->status->label() }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small">لا توجد سندات استلام</p>
                            @endif
                        </div>

                        <!-- Invoices -->
                        <div class="col-md-6">
                            <h6>فواتير الشراء</h6>
                            @if($purchaseOrder->invoices->count() > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($purchaseOrder->invoices as $invoice)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <a
                                                href="{{ route('purchase-invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                            <span class="badge bg-{{ $invoice->status->value == 'paid' ? 'success' : 'warning' }}">
                                                {{ $invoice->status->label() }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small">لا توجد فواتير</p>
                            @endif
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
                        @if($purchaseOrder->canEdit())
                            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-2"></i>تعديل الأمر
                            </a>

                            <form action="{{ route('purchase-orders.approve', $purchaseOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-2"></i>اعتماد الأمر
                                </button>
                            </form>
                        @endif

                        @if($purchaseOrder->canReceive())
                            <a href="{{ route('grns.create', ['purchase_order_id' => $purchaseOrder->id]) }}"
                                class="btn btn-primary">
                                <i class="bi bi-box-seam me-2"></i>استلام البضاعة
                            </a>
                        @endif

                        @if($purchaseOrder->canCancel())
                            <form action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100"
                                    onclick="return confirm('هل أنت متأكد من إلغاء هذا الأمر؟')">
                                    <i class="bi bi-x-lg me-2"></i>إلغاء الأمر
                                </button>
                            </form>
                        @endif

                        <hr>
                        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-right me-2"></i>العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

            <!-- Receipt Status -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-truck me-2"></i>حالة الاستلام</h6>
                </div>
                <div class="card-body">
                    @php $percentage = $purchaseOrder->getReceivedPercentage(); @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>نسبة الاستلام</span>
                            <strong>{{ $percentage }}%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar {{ $percentage >= 100 ? 'bg-success' : 'bg-warning' }}"
                                style="width: {{ $percentage }}%">
                                {{ $percentage }}%
                            </div>
                        </div>
                    </div>

                    @if($percentage >= 100)
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            تم استلام جميع الأصناف
                        </div>
                    @elseif($percentage > 0)
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-clock me-1"></i>
                            تم استلام جزء من الأصناف
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-hourglass me-1"></i>
                            في انتظار الاستلام
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection