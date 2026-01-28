@extends('layouts.app')

@section('title', 'مدفوعات الموردين - Twinx ERP')
@section('page-title', 'مدفوعات الموردين')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">مدفوعات الموردين</li>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">مدفوعات اليوم</h6>
                            <h3 class="mb-0 text-success">{{ number_format($stats['today'], 2) }} ج.م</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cash-stack text-success fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">مدفوعات الشهر</h6>
                            <h3 class="mb-0">{{ number_format($stats['this_month'], 2) }} ج.م</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-range text-primary fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('supplier-payments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>دفعة جديدة
            </a>
        </div>
        <div class="text-muted">
            إجمالي: <strong>{{ $payments->total() }}</strong> دفعة
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('supplier-payments.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                        placeholder="رقم الدفعة">
                </div>
                <div class="col-md-3">
                    <label class="form-label">المورد</label>
                    <select class="form-select" name="supplier_id">
                        <option value="">الكل</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">طريقة الدفع</label>
                    <select class="form-select" name="payment_method">
                        <option value="">الكل</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>نقدي</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                            تحويل بنكي</option>
                        <option value="cheque" {{ request('payment_method') == 'cheque' ? 'selected' : '' }}>شيك</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>بحث
                    </button>
                    <a href="{{ route('supplier-payments.index') }}" class="btn btn-secondary">
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
                            <th>رقم الدفعة</th>
                            <th>المورد</th>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>المرجع</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            @php
                                $methodIcons = [
                                    'cash' => 'bi-cash',
                                    'bank_transfer' => 'bi-bank',
                                    'cheque' => 'bi-credit-card',
                                ];
                                $methodLabels = [
                                    'cash' => 'نقدي',
                                    'bank_transfer' => 'تحويل بنكي',
                                    'cheque' => 'شيك',
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('supplier-payments.show', $payment) }}"
                                        class="fw-bold text-decoration-none">
                                        {{ $payment->payment_number }}
                                    </a>
                                </td>
                                <td>{{ $payment->supplier?->name ?? '-' }}</td>
                                <td>{{ $payment->payment_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="fw-bold text-success">{{ number_format($payment->amount, 2) }} ج.م</td>
                                <td>
                                    <i class="bi {{ $methodIcons[$payment->payment_method] ?? 'bi-question' }} me-1"></i>
                                    {{ $methodLabels[$payment->payment_method] ?? $payment->payment_method }}
                                </td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('supplier-payments.show', $payment) }}"
                                            class="btn btn-outline-primary" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('supplier-payments.print', $payment) }}"
                                            class="btn btn-outline-secondary" title="طباعة" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
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