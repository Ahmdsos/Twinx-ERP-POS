@extends('layouts.app')

@section('title', 'فواتير الشراء - Twinx ERP')
@section('page-title', 'فواتير الشراء')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">فواتير الشراء</li>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">إجمالي المستحقات</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_pending'], 2) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cash-stack text-warning fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-4 border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">المتأخرات</h6>
                            <h3 class="mb-0 text-danger">{{ number_format($stats['total_overdue'], 2) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-exclamation-triangle text-danger fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">فواتير متأخرة</h6>
                            <h3 class="mb-0">{{ $stats['overdue_count'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock-history text-info fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('purchase-invoices.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>فاتورة جديدة
            </a>
        </div>
        <div class="text-muted">
            إجمالي: <strong>{{ $invoices->total() }}</strong> فاتورة
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('purchase-invoices.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                           placeholder="رقم الفاتورة">
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
                    <label class="form-label">الحالة</label>
                    <select class="form-select" name="status">
                        <option value="">الكل</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">المتأخرات فقط</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" name="overdue_only" value="1" 
                               {{ request('overdue_only') ? 'checked' : '' }}>
                        <label class="form-check-label">عرض المتأخرات</label>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>بحث
                    </button>
                    <a href="{{ route('purchase-invoices.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>رقم فاتورة المورد</th>
                            <th>المورد</th>
                            <th>التاريخ</th>
                            <th>تاريخ الاستحقاق</th>
                            <th>الإجمالي</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'pending' => 'warning',
                                    'partial' => 'info',
                                    'paid' => 'success',
                                    'cancelled' => 'danger',
                                ];
                                $isOverdue = $invoice->isOverdue();
                            @endphp
                            <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('purchase-invoices.show', $invoice) }}" 
                                       class="fw-bold text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->supplier_invoice_number ?? '-' }}</td>
                                <td>{{ $invoice->supplier?->name ?? '-' }}</td>
                                <td>{{ $invoice->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                                <td>
                                    {{ $invoice->due_date?->format('Y-m-d') ?? '-' }}
                                    @if($isOverdue)
                                        <br>
                                        <small class="text-danger">
                                            <i class="bi bi-clock me-1"></i>
                                            متأخر {{ $invoice->getDaysOverdue() }} يوم
                                        </small>
                                    @endif
                                </td>
                                <td>{{ number_format($invoice->total, 2) }} ج.م</td>
                                <td class="fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($invoice->balance_due, 2) }} ج.م
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$invoice->status->value] ?? 'secondary' }}">
                                        {{ $invoice->status->label() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('purchase-invoices.show', $invoice) }}" 
                                           class="btn btn-outline-primary" title="عرض">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('purchase-invoices.print', $invoice) }}" 
                                           class="btn btn-outline-secondary" title="طباعة" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        @if($invoice->status->canPay())
                                            <a href="{{ route('supplier-payments.create', ['invoice_id' => $invoice->id]) }}" 
                                               class="btn btn-outline-success" title="سداد">
                                                <i class="bi bi-cash"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark fs-1 d-block mb-2"></i>
                                    لا توجد فواتير
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
