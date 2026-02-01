@extends('layouts.app')

@section('title', $customerPayment->receipt_number . ' - Twinx ERP')
@section('page-title', 'تفاصيل الدفعة')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customer-payments.index') }}">المدفوعات</a></li>
    <li class="breadcrumb-item active">{{ $customerPayment->receipt_number }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Payment Header Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-cash me-2"></i>
                        {{ $customerPayment->receipt_number }}
                    </h5>
                    <span class="badge bg-success fs-6">
                        {{ number_format($customerPayment->amount, 2) }} ج.م
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">العميل</td>
                                    <td>
                                        <a href="{{ route('customers.show', $customerPayment->customer_id) }}">
                                            <strong>{{ $customerPayment->customer?->name ?? '-' }}</strong>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">كود العميل</td>
                                    <td>{{ $customerPayment->customer?->code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">طريقة الدفع</td>
                                    <td>
                                        @php
                                            $methodLabels = [
                                                'cash' => 'نقداً',
                                                'bank_transfer' => 'تحويل بنكي',
                                                'check' => 'شيك',
                                                'credit_card' => 'بطاقة ائتمان',
                                            ];
                                        @endphp
                                        {{ $methodLabels[$customerPayment->payment_method] ?? $customerPayment->payment_method }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">تاريخ الدفع</td>
                                    <td>{{ $customerPayment->payment_date?->format('Y-m-d') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">حساب الاستلام</td>
                                    <td>{{ $customerPayment->paymentAccount?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">المرجع</td>
                                    <td>{{ $customerPayment->reference ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Allocations -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>الفواتير المخصص لها</h5>
                </div>
                <div class="card-body p-0">
                    @if($customerPayment->allocations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الفاتورة</th>
                                        <th>تاريخ الفاتورة</th>
                                        <th>إجمالي الفاتورة</th>
                                        <th>المبلغ المخصص</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customerPayment->allocations as $allocation)
                                        <tr>
                                            <td>
                                                <a href="{{ route('sales-invoices.show', $allocation->sales_invoice_id) }}">
                                                    {{ $allocation->invoice?->invoice_number ?? '-' }}
                                                </a>
                                            </td>
                                            <td>{{ $allocation->invoice?->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                                            <td>{{ number_format($allocation->invoice?->total ?? 0, 2) }} ج.م</td>
                                            <td class="text-success fw-bold">{{ number_format($allocation->amount, 2) }} ج.م</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3"><strong>إجمالي المخصص</strong></td>
                                        <td class="text-success fw-bold">
                                            {{ number_format($customerPayment->getAllocatedAmount(), 2) }} ج.م
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                            لم يتم تخصيص هذه الدفعة لأي فواتير
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($customerPayment->notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>ملاحظات</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $customerPayment->notes }}</p>
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
                        <a href="{{ route('customer-payments.print', $customerPayment) }}" class="btn btn-primary"
                            target="_blank">
                            <i class="bi bi-printer me-2"></i>طباعة الإيصال
                        </a>

                        <a href="{{ route('customer-payments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-right me-2"></i>العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i>ملخص الدفعة</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>المبلغ الإجمالي:</span>
                        <strong>{{ number_format($customerPayment->amount, 2) }} ج.م</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>المخصص للفواتير:</span>
                        <strong>{{ number_format($customerPayment->getAllocatedAmount(), 2) }} ج.م</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5">
                        <span><strong>غير مخصص:</strong></span>
                        <strong
                            class="{{ $customerPayment->getUnallocatedAmount() > 0 ? 'text-warning' : 'text-success' }}">
                            {{ number_format($customerPayment->getUnallocatedAmount(), 2) }} ج.م
                        </strong>
                    </div>

                    @if($customerPayment->getUnallocatedAmount() > 0)
                        <div class="alert alert-warning mt-3 mb-0 small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            يوجد مبلغ غير مخصص سيظل كرصيد دائن للعميل
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection