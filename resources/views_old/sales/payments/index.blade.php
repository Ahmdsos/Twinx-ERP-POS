@extends('layouts.app')

@section('title', 'المدفوعات - Twinx ERP')
@section('page-title', 'مدفوعات العملاء')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المدفوعات</li>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ number_format($totalToday, 2) }} ج.م</h3>
                        <small>تحصيلات اليوم</small>
                    </div>
                    <i class="bi bi-cash-coin fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ number_format($totalMonth, 2) }} ج.م</h3>
                        <small>تحصيلات الشهر</small>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('customer-payments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>تسجيل دفعة جديدة
            </a>
        </div>
        <div class="text-muted">
            إجمالي: <strong>{{ $payments->total() }}</strong> دفعة
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('customer-payments.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                        placeholder="رقم الإيصال">
                </div>
                <div class="col-md-3">
                    <label class="form-label">العميل</label>
                    <select class="form-select" name="customer_id">
                        <option value="">الكل</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">طريقة الدفع</label>
                    <select class="form-select" name="payment_method">
                        <option value="">الكل</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>نقداً</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                            تحويل بنكي</option>
                        <option value="check" {{ request('payment_method') == 'check' ? 'selected' : '' }}>شيك</option>
                        <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>بطاقة
                            ائتمان</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('customer-payments.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الإيصال</th>
                            <th>العميل</th>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>الحساب</th>
                            <th>المرجع</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <a href="{{ route('customer-payments.show', $payment) }}"
                                        class="fw-bold text-decoration-none">
                                        {{ $payment->receipt_number }}
                                    </a>
                                </td>
                                <td>{{ $payment->customer?->name ?? '-' }}</td>
                                <td>{{ $payment->payment_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="text-success fw-bold">{{ number_format($payment->amount, 2) }} ج.م</td>
                                <td>
                                    @php
                                        $methodLabels = [
                                            'cash' => 'نقداً',
                                            'bank_transfer' => 'تحويل بنكي',
                                            'check' => 'شيك',
                                            'credit_card' => 'بطاقة ائتمان',
                                        ];
                                        $methodIcons = [
                                            'cash' => 'bi-cash',
                                            'bank_transfer' => 'bi-bank',
                                            'check' => 'bi-file-text',
                                            'credit_card' => 'bi-credit-card',
                                        ];
                                    @endphp
                                    <i class="bi {{ $methodIcons[$payment->payment_method] ?? 'bi-cash' }} me-1"></i>
                                    {{ $methodLabels[$payment->payment_method] ?? $payment->payment_method }}
                                </td>
                                <td>{{ $payment->paymentAccount?->name ?? '-' }}</td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('customer-payments.show', $payment) }}"
                                            class="btn btn-outline-primary" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('customer-payments.print', $payment) }}"
                                            class="btn btn-outline-secondary" title="طباعة" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
                                    لا توجد مدفوعات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
            <div class="card-footer">
                {{ $payments->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection