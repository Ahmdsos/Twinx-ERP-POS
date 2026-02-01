@extends('layouts.app')

@section('title', $purchaseInvoice->invoice_number . ' - Twinx ERP')
@section('page-title', 'تفاصيل فاتورة الشراء')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase-invoices.index') }}">فواتير الشراء</a></li>
    <li class="breadcrumb-item active">{{ $purchaseInvoice->invoice_number }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Invoice Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark me-2"></i>
                    {{ $purchaseInvoice->invoice_number }}
                </h5>
                @php
                    $statusColors = [
                        'draft' => 'secondary',
                        'pending' => 'warning',
                        'partial' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                    ];
                @endphp
                <span class="badge bg-{{ $statusColors[$purchaseInvoice->status->value] ?? 'secondary' }} fs-6">
                    {{ $purchaseInvoice->status->label() }}
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 40%;">المورد</td>
                                <td><strong>{{ $purchaseInvoice->supplier?->name ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">رقم فاتورة المورد</td>
                                <td>{{ $purchaseInvoice->supplier_invoice_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">سند الاستلام</td>
                                <td>
                                    @if($purchaseInvoice->grn)
                                        <a href="{{ route('grns.show', $purchaseInvoice->grn) }}">
                                            {{ $purchaseInvoice->grn->grn_number }}
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
                                <td>{{ $purchaseInvoice->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">تاريخ الاستحقاق</td>
                                <td>
                                    {{ $purchaseInvoice->due_date?->format('Y-m-d') ?? '-' }}
                                    @if($purchaseInvoice->isOverdue())
                                        <span class="badge bg-danger ms-1">
                                            متأخر {{ $purchaseInvoice->getDaysOverdue() }} يوم
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">أمر الشراء</td>
                                <td>
                                    @if($purchaseInvoice->purchaseOrder)
                                        <a href="{{ route('purchase-orders.show', $purchaseInvoice->purchaseOrder) }}">
                                            {{ $purchaseInvoice->purchaseOrder->po_number }}
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
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>بنود الفاتورة</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>السعر</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseInvoice->lines as $index => $line)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $line->product?->name ?? '-' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $line->product?->sku }}</small>
                                    </td>
                                    <td>{{ number_format($line->quantity, 2) }} {{ $line->product?->unit?->name }}</td>
                                    <td>{{ number_format($line->unit_price ?? 0, 2) }}</td>
                                    <td class="fw-bold">{{ number_format($line->line_total ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-start">الإجمالي الفرعي</td>
                                <td class="fw-bold">{{ number_format($purchaseInvoice->subtotal, 2) }} ج.م</td>
                            </tr>
                            @if($purchaseInvoice->tax_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-start">الضريبة</td>
                                    <td>{{ number_format($purchaseInvoice->tax_amount, 2) }} ج.م</td>
                                </tr>
                            @endif
                            <tr class="table-primary">
                                <td colspan="4" class="text-start"><strong>الإجمالي</strong></td>
                                <td><strong>{{ number_format($purchaseInvoice->total, 2) }} ج.م</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        @if($purchaseInvoice->paymentAllocations->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-cash me-2"></i>سجل المدفوعات</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الدفعة</th>
                                    <th>التاريخ</th>
                                    <th>المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseInvoice->paymentAllocations as $allocation)
                                    <tr>
                                        <td>
                                            <a href="{{ route('supplier-payments.show', $allocation->payment) }}">
                                                {{ $allocation->payment?->payment_number }}
                                            </a>
                                        </td>
                                        <td>{{ $allocation->payment?->payment_date?->format('Y-m-d') }}</td>
                                        <td class="text-success fw-bold">{{ number_format($allocation->amount, 2) }} ج.م</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($purchaseInvoice->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted">{{ $purchaseInvoice->notes }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Payment Summary -->
        <div class="card mb-4 {{ $purchaseInvoice->isOverdue() ? 'border-danger' : '' }}">
            <div class="card-header {{ $purchaseInvoice->isOverdue() ? 'bg-danger text-white' : '' }}">
                <h6 class="mb-0"><i class="bi bi-wallet me-2"></i>حالة الدفع</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>إجمالي الفاتورة</span>
                    <strong>{{ number_format($purchaseInvoice->total, 2) }} ج.م</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>المدفوع</span>
                    <strong>{{ number_format($purchaseInvoice->paid_amount, 2) }} ج.م</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between {{ $purchaseInvoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                    <span><strong>المتبقي</strong></span>
                    <strong>{{ number_format($purchaseInvoice->balance_due, 2) }} ج.م</strong>
                </div>
                
                @if($purchaseInvoice->status->canPay())
                    <!-- Payment Progress -->
                    @php 
                        $paidPercent = $purchaseInvoice->total > 0 
                            ? round(($purchaseInvoice->paid_amount / $purchaseInvoice->total) * 100) 
                            : 0; 
                    @endphp
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $paidPercent }}%"></div>
                    </div>
                    <small class="text-muted">مدفوع {{ $paidPercent }}%</small>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>الإجراءات</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($purchaseInvoice->status->canPay())
                        <a href="{{ route('supplier-payments.create', ['invoice_id' => $purchaseInvoice->id]) }}" 
                           class="btn btn-success">
                            <i class="bi bi-cash me-2"></i>تسجيل دفعة
                        </a>
                    @endif
                    
                    <a href="{{ route('purchase-invoices.print', $purchaseInvoice) }}" 
                       class="btn btn-outline-secondary" target="_blank">
                        <i class="bi bi-printer me-2"></i>طباعة الفاتورة
                    </a>
                    
                    @if($purchaseInvoice->status !== \Modules\Purchasing\Enums\PurchaseInvoiceStatus::PAID && $purchaseInvoice->paid_amount == 0)
                        <form action="{{ route('purchase-invoices.cancel', $purchaseInvoice) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100" 
                                    onclick="return confirm('هل أنت متأكد من إلغاء هذه الفاتورة؟')">
                                <i class="bi bi-x-lg me-2"></i>إلغاء الفاتورة
                            </button>
                        </form>
                    @endif
                    
                    <hr>
                    <a href="{{ route('purchase-invoices.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-right me-2"></i>العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
