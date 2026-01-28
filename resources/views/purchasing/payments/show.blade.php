@extends('layouts.app')

@section('title', $supplierPayment->payment_number . ' - Twinx ERP')
@section('page-title', 'تفاصيل الدفعة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supplier-payments.index') }}">مدفوعات الموردين</a></li>
    <li class="breadcrumb-item active">{{ $supplierPayment->payment_number }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Payment Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-cash me-2"></i>
                        {{ $supplierPayment->payment_number }}
                    </h5>
                    <span class="badge bg-success fs-6">
                        {{ number_format($supplierPayment->amount, 2) }} ج.م
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">المورد</td>
                                    <td><strong>{{ $supplierPayment->supplier?->name ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">تاريخ الدفع</td>
                                    <td>{{ $supplierPayment->payment_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">طريقة الدفع</td>
                                    <td>
                                        @php
                                            $methodLabels = [
                                                'cash' => 'نقدي',
                                                'bank_transfer' => 'تحويل بنكي',
                                                'cheque' => 'شيك',
                                            ];
                                        @endphp
                                        {{ $methodLabels[$supplierPayment->payment_method] ?? $supplierPayment->payment_method }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">حساب الدفع</td>
                                    <td>{{ $supplierPayment->paymentAccount?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">المرجع</td>
                                    <td>{{ $supplierPayment->reference ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">بواسطة</td>
                                    <td>{{ $supplierPayment->creator?->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Allocation Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>تخصيص الدفعة</h5>
                </div>
                <div class="card-body p-0">
                    @if($supplierPayment->allocations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الفاتورة</th>
                                        <th>تاريخ الفاتورة</th>
                                        <th>المبلغ المخصص</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplierPayment->allocations as $allocation)
                                        <tr>
                                            <td>
                                                <a href="{{ route('purchase-invoices.show', $allocation->invoice) }}">
                                                    {{ $allocation->invoice?->invoice_number }}
                                                </a>
                                            </td>
                                            <td>{{ $allocation->invoice?->invoice_date?->format('Y-m-d') }}</td>
                                            <td class="text-success fw-bold">{{ number_format($allocation->amount, 2) }} ج.م</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="2"><strong>إجمالي التخصيص</strong></td>
                                        <td class="text-success">
                                            <strong>{{ number_format($supplierPayment->getAllocatedAmount(), 2) }} ج.م</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                            لم يتم تخصيص هذه الدفعة على أي فواتير بعد
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($supplierPayment->notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ $supplierPayment->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Payment Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>ملخص الدفعة</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>إجمالي الدفعة</span>
                        <strong>{{ number_format($supplierPayment->amount, 2) }} ج.م</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>المخصص</span>
                        <strong>{{ number_format($supplierPayment->getAllocatedAmount(), 2) }} ج.م</strong>
                    </div>
                    <hr>
                    @php $unallocated = $supplierPayment->getUnallocatedAmount(); @endphp
                    <div class="d-flex justify-content-between {{ $unallocated > 0 ? 'text-warning' : 'text-success' }}">
                        <span><strong>غير مخصص</strong></span>
                        <strong>{{ number_format($unallocated, 2) }} ج.م</strong>
                    </div>
                    @if($unallocated > 0)
                        <div class="alert alert-warning mt-3 mb-0">
                            <small><i class="bi bi-exclamation-triangle me-1"></i>
                                هناك مبلغ غير مخصص يظهر كرصيد دائن للمورد
                            </small>
                        </div>
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
                        <a href="{{ route('supplier-payments.print', $supplierPayment) }}" class="btn btn-primary"
                            target="_blank">
                            <i class="bi bi-printer me-2"></i>طباعة الإيصال
                        </a>
                        <hr>
                        <a href="{{ route('supplier-payments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-right me-2"></i>العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

            <!-- Document Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>معلومات المستند</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">تاريخ الإنشاء</td>
                            <td>{{ $supplierPayment->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection